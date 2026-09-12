<?php
namespace Tooltipy\ContentFilter;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;
use Tooltipy\Keyword\KeywordData;
use Tooltipy\Frontend\TooltipRenderer;
use Tooltipy\Security\Sanitizer;

/**
 * Handles server-side keyword highlighting in post content (the_content filter).
 * Also handles WooCommerce Product Addons (array content).
 * Replaces tltpy_filter_posttype() and tltpy_filter_any_content().
 */
class ContentFilter {

    private Plugin            $plugin;
    private KeywordRepository $repo;
    private TooltipRenderer   $renderer;

    /** @var bool used to prevent tooltip loops inside glossary */
    private bool $is_glossary_page = false;

    public function __construct( Plugin $plugin ) {
        $this->plugin   = $plugin;
        $this->repo     = new KeywordRepository( $plugin );
        $this->renderer = new TooltipRenderer( $plugin );
    }

    public function init(): void {
        // Keyword highlighting is handled client-side (FrontendMatcher + Tippy).
        // Keep only image-alt tooltips and the optional "prevent plugins filters" guard.
        add_action( 'the_post',    [ $this, 'maybe_remove_plugin_filters' ] );
        add_filter( 'the_content', [ $this, 'filter_image_alts' ], 101 );
    }

    /**
     * Expose a setter so GlossaryShortcode can flag the current page.
     */
    public function set_glossary_page( bool $is ): void {
        $this->is_glossary_page = $is;
    }

    public function is_glossary_page(): bool {
        return $this->is_glossary_page;
    }

    /**
     * Register the_content filters for each post type to match.
     * Fires on wp_head so we know the current post type.
     */
    public function register_content_filters(): void {
        $settings = get_option( 'bluet_kw_settings', [] );

        $post_types = [];
        if ( ! empty( $settings['bt_kw_for_posts'] ) && $settings['bt_kw_for_posts'] === 'on' ) {
            $post_types[] = 'post';
        }
        if ( ! empty( $settings['bt_kw_for_pages'] ) && $settings['bt_kw_for_pages'] === 'on' ) {
            $post_types[] = 'page';
        }

        // Advanced: let addons/hooks extend which post types are matched
        $post_types = apply_filters( 'tltpy_posttypes_to_match', $post_types );

        // Custom field hooks (from advanced settings)
        $contents_to_filter = [
            [ 'the_content' ],
            [ 'the_content' ],
        ];
        $contents_to_filter = apply_filters( 'tltpy_custom_fields_hooks', $contents_to_filter );

        foreach ( $post_types as $k => $the_post_type ) {
            if ( ! empty( $contents_to_filter[ $k ] ) ) {
                $this->register_filter_for_post_type( $the_post_type, $contents_to_filter[ $k ] );
            }
        }
    }

    private function register_filter_for_post_type( string $post_type, array $hooks ): void {
        $post_id    = get_the_id();
        $exclude_me = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );

        if ( $exclude_me === 'on' ) {
            return;
        }

        if ( $post_type !== get_post_type( $post_id ) ) {
            return;
        }

        if ( $post_type === 'post' && ! is_single( $post_id ) ) {
            return;
        }

        foreach ( $hooks as $hook ) {
            add_filter( $hook, [ $this, 'apply_filter' ], 100000 );
        }
    }

    /**
     * Main content filter – replaces tltpy_filter_posttype().
     */
    public function apply_filter( $content ) {
        // WooCommerce Product Addons compatibility (array content)
        if ( is_array( $content ) ) {
            foreach ( $content as $k => $item ) {
                if ( isset( $item['description'] ) ) {
                    $content[ $k ]['description'] = $this->apply_filter( $item['description'] );
                }
            }
            return $content;
        }

        $post_id    = get_the_id();
        $exclude_me = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );

        if ( $exclude_me || $this->is_glossary_page ) {
            return $content;
        }

        $settings     = get_option( 'bluet_kw_settings', [] );
        $adv_options  = get_option( 'bluet_kw_advanced', [] );
        $match_all    = ! empty( $settings['bt_kw_match_all'] ) && $settings['bt_kw_match_all'] === 'on';
        $limit        = $match_all ? -1 : 1;

        $glossary_opt       = get_option( 'bluet_glossary_options', [] );
        $show_glossary_link = ! empty( $glossary_opt['bluet_kttg_show_glossary_link'] )
            && $glossary_opt['bluet_kttg_show_glossary_link'] === 'on'
            && esc_url( (string) ( $glossary_opt['kttg_link_glossary_page_link'] ?? '' ) ) !== '';

        // Determine which keywords to match
        $keyword_ids = $this->repo->get_related_ids( $post_id );

        $manual = get_post_meta( $post_id, 'bluet_matching_keywords_field', true );
        if ( ! empty( $manual ) ) {
            $keyword_ids = (array) $manual;
        }

        $fetch_all_kws = ! empty( $adv_options['kttg_fetch_all_keywords'] ) && $adv_options['kttg_fetch_all_keywords'] === 'on';

        if ( empty( $keyword_ids ) && ! $fetch_all_kws ) {
            return $content;
        }

        // Build WP_Query for these keywords
        $args = [
            'post_type'      => $this->plugin->get_tooltip_post_types(),
            'posts_per_page' => -1,
        ];
        if ( ! $fetch_all_kws ) {
            $args['post__in'] = $keyword_ids;
        }

        $query = new \WP_Query( $args );

        $keywords_terms = [];
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $title = get_the_title();
                if ( $title === '' ) {
                    continue;
                }

                $keywords_terms[] = new KeywordData( [
                    'kw_id'   => get_the_id(),
                    'term'    => $title,
                    'syns'    => Sanitizer::synonyms( (string) get_post_meta( get_the_id(), 'bluet_synonyms_keywords', true ) ),
                    'case'    => get_post_meta( get_the_id(), 'bluet_case_sensitive_word', true ) === 'on',
                    'pref'    => get_post_meta( get_the_id(), 'bluet_prefix_keywords', true ) === 'on',
                    'youtube' => Sanitizer::youtube_id( (string) get_post_meta( get_the_id(), 'bluet_youtube_video_id', true ) ),
                    'dfn'     => Sanitizer::tooltip_html( (string) get_the_content() ),
                    'img'     => get_the_post_thumbnail( get_the_id(), 'medium' ),
                ] );
            }
        }
        wp_reset_postdata();

        if ( empty( $keywords_terms ) ) {
            return $content;
        }

        // Extract existing HTML tags, replace with placeholder, do regex, reinsert
        $tag_regex = '<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>';
        $out       = [];
        preg_match_all( '#(' . $tag_regex . ')#iu', $content, $out );
        $content = preg_replace( '#(' . $tag_regex . ')#i', '**T_A_G**', $content );

        // First pass: mark matches with __word__
        foreach ( $keywords_terms as $kw ) {
            $case_flag = $kw->case_sensitive ? '' : 'i';
            $kw_after  = $kw->is_prefix ? '\w*' : '';
            $terms_arr = array_reverse( explode( '|', $kw->get_full_term() ) ); // longest first
            usort( $terms_arr, fn( $a, $b ) => strlen( $b ) - strlen( $a ) );

            foreach ( $terms_arr as $term_occ ) {
                $term_occ = $this->eliminate_apostrophes( $term_occ );
                $term_occ = preg_quote( $term_occ, '#' );
                $content  = $this->eliminate_apostrophes( $content );
                $content  = preg_replace(
                    '#((\W)(' . $term_occ . $kw_after . ')(\W))#u' . $case_flag,
                    '$2__$3__$4',
                    $content,
                    $limit
                );
            }
        }

        // Build tooltip blocks HTML & second pass: replace __word__ with span
        $html_blocks = '<div class="my_tooltips_in_block">';

        foreach ( $keywords_terms as $kw ) {
            $case_flag = $kw->case_sensitive ? '' : 'i';
            $kw_after  = $kw->is_prefix ? '\w*' : '';
            $terms_arr = explode( '|', $kw->get_full_term() );
            usort( $terms_arr, fn( $a, $b ) => strlen( $b ) - strlen( $a ) );

            $html_replace = '<span class="bluet_tooltip" data-tooltip="' . esc_attr( (string) $kw->id ) . '">$2</span>';

            if ( $kw->has_video() ) {
                $html_blocks .= $this->renderer->render_tooltip_block( $kw, $show_glossary_link );
            } else {
                $html_blocks .= $this->renderer->render_tooltip_block( $kw, $show_glossary_link );
            }

            foreach ( $terms_arr as $term_occ ) {
                $term_occ = $this->eliminate_apostrophes( $term_occ );
                $term_occ = preg_quote( $term_occ, '#' );
                $content  = $this->eliminate_apostrophes( $content );
                $content  = preg_replace(
                    '#(__(' . $term_occ . $kw_after . ')__)#u' . $case_flag,
                    $html_replace,
                    $content,
                    -1
                );
            }
        }

        // Re-insert HTML tags
        foreach ( $out[0] as $tag ) {
            $content = preg_replace( '#(\*\*T_A_G\*\*)#', $tag, $content, 1 );
        }

        // Backward-compat filter hook
        $html_blocks = apply_filters( 'kttg_another_tooltip_in_block', $html_blocks );
        $html_blocks .= '</div>';

        $content = $html_blocks . $content;

        return do_shortcode( $content );
    }

    /**
     * Filter images with alt="KTTG: keyword" (Pro image tooltip feature).
     */
    public function filter_image_alts( string $content ): string {
        $post_id    = get_the_id();
        $exclude_me = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );

        if ( $exclude_me ) {
            return $content;
        }

        $settings    = get_option( 'bluet_kw_settings', [] );
        $match_all   = ! empty( $settings['bt_kw_match_all'] ) && $settings['bt_kw_match_all'] === 'on';
        $limit       = $match_all ? -1 : 1;

        $is_single   = is_single() || is_page();
        $for_posts   = ! empty( $settings['bt_kw_for_posts'] ) && $settings['bt_kw_for_posts'];
        $for_pages   = ! empty( $settings['bt_kw_for_pages'] ) && $settings['bt_kw_for_pages'] === 'on';

        if ( ! ( ( $is_single && $for_posts ) || ( is_page() && $for_pages ) ) ) {
            return $content;
        }

        $kw_ids = $this->repo->get_related_ids( $post_id );

        $manual = get_post_meta( $post_id, 'bluet_matching_keywords_field', true );
        if ( ! empty( $manual ) ) {
            $kw_ids = (array) $manual;
        }

        if ( empty( $kw_ids ) ) {
            return $content;
        }

        $query = new \WP_Query( [
            'post__in'       => $kw_ids,
            'post_type'      => $this->plugin->get_tooltip_post_types(),
            'posts_per_page' => -1,
        ] );

        if ( ! $query->have_posts() ) {
            return $content;
        }

        while ( $query->have_posts() ) {
            $query->the_post();
            $kw_id   = get_the_id();
            $term    = get_the_title();
            $syns    = (string) get_post_meta( $kw_id, 'bluet_synonyms_keywords', true );
            $dfn     = get_the_content();
            $img     = get_the_post_thumbnail( $kw_id, 'medium' );
            $term_1  = explode( '|', $term . ( $syns ? '|' . $syns : '' ) )[0];

            $dfn_display = $dfn !== '' ? ' : ' . $dfn : '';

            $html_to_replace = '$1
                <span class="bluet_block_to_show" data-tooltip="' . $kw_id . '">
                    <span class="bluet_block_container">
                        ' . $img . '
                        <span class="bluet_title_on_block">' . esc_html( $term_1 ) . '</span>
                        ' . $dfn_display . '
                    </span>
                </span>';

            $content = preg_replace(
                '#(<img\s([^>]*\s)?alt="KTTG: ' . preg_quote( $term_1, '#' ) . '"(.*?)>)#i',
                $html_to_replace,
                $content,
                $limit
            );
            $content = preg_replace(
                '#((<img)(\s([^>]*\s)?alt="KTTG: ' . preg_quote( $term_1, '#' ) . '"(.*?)>))#i',
                '<img class="bluet_img_tooltip" data-tooltip="' . $kw_id . '" $3',
                $content,
                $limit
            );
        }
        wp_reset_postdata();

        return $content;
    }

    /**
     * Remove all filters on keyword post type content if setting is on.
     */
    public function maybe_remove_plugin_filters(): void {
        global $post;

        if ( ! $post ) {
            return;
        }

        $options                    = get_option( 'bluet_kw_advanced', [] );
        $prevent_plugins_filters    = ! empty( $options['prevent_plugins_filters'] ) && $options['prevent_plugins_filters'] === 'on';
        $pt_name                    = $this->plugin->get_post_type_name();

        if ( $prevent_plugins_filters && $pt_name === $post->post_type ) {
            remove_all_filters( 'the_content', 10 );
        }
    }

    private function eliminate_apostrophes( string $str ): string {
        return str_replace( '&#8217;', "'", $str );
    }
}
