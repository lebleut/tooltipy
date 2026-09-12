<?php
namespace Tooltipy\Keyword;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Security\Sanitizer;

/**
 * Fetches keyword posts from the database, with transient cache.
 */
class KeywordRepository {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Returns all keywords as KeywordData[], using a 24-hour transient cache.
     * The cache is invalidated by Plugin::delete_keywords_transient() on save_post.
     *
     * @return KeywordData[]
     */
    public function get_all(): array {
        $cached = get_transient( 'tooltipy_keywords_titles_ids' );

        if ( false === $cached ) {
            $cached = new \WP_Query( [
                'post_type'      => $this->plugin->get_tooltip_post_types(),
                'posts_per_page' => -1,
            ] );
            set_transient( 'tooltipy_keywords_titles_ids', $cached, 24 * HOUR_IN_SECONDS );
        }

        return $this->query_to_keyword_data( $cached );
    }

    /**
     * Returns keywords related to a given post (by scanning content).
     *
     * @param int $post_id
     * @return int[]
     */
    public function get_related_ids( int $post_id ): array {
        $all_keywords = $this->get_all_raw_for_matching();
        $cat_name     = $this->plugin->get_cat_name();

        $post = get_post( $post_id );
        if ( ! $post ) {
            return [];
        }

        $content = ' ' . $post->post_content;
        $content = strip_tags( $content );

        $related = [];
        foreach ( $all_keywords as $kw_id => $pattern ) {
            if ( preg_match( $pattern, $content ) ) {
                $related[] = $kw_id;
            }
        }
        return $related;
    }

    /**
     * Returns all keywords as raw WP_Post objects (lightweight, for matching).
     *
     * @return array<int, string>  kw_id => regex pattern
     */
    private function get_all_raw_for_matching(): array {
        $posts = get_posts( [
            'post_type'      => $this->plugin->get_tooltip_post_types(),
            'posts_per_page' => -1,
        ] );

        $result = [];
        foreach ( $posts as $kw ) {
            $syn       = Sanitizer::synonyms( (string) get_post_meta( $kw->ID, 'bluet_synonyms_keywords', true ) );
            $is_prefix = (bool) get_post_meta( $kw->ID, 'bluet_prefix_keywords', true );
            $kw_after  = $is_prefix ? '\w*' : '';

            $title    = (string) $kw->post_title;
            $text_sep = '(\W)';

            $jc_re = "/[\x{3000}-\x{303F}]|[\x{3040}-\x{309F}]|[\x{30A0}-\x{30FF}]|[\x{FF00}-\x{FFEF}]|[\x{4E00}-\x{9FAF}]/u";
            if ( preg_match( $jc_re, $title ) ) {
                $text_sep = '';
            }

            $pattern = preg_quote( $title, '/' ) . $kw_after;
            if ( $syn !== '' ) {
                foreach ( array_filter( array_map( 'trim', explode( '|', $syn ) ) ) as $syn_term ) {
                    $pattern .= '|' . preg_quote( $syn_term, '/' ) . $kw_after;
                }
            }

            $result[ $kw->ID ] = '/' . $text_sep . '(' . $pattern . ')' . $text_sep . '/iu';
        }

        return $result;
    }

    /**
     * Converts a WP_Query into an array of KeywordData.
     */
    private function query_to_keyword_data( \WP_Query $query ): array {
        $result   = [];
        $cat_name = $this->plugin->get_cat_name();

        if ( ! $query->have_posts() ) {
            return $result;
        }

        while ( $query->have_posts() ) {
            $query->the_post();

            $kw_id = get_the_id();
            $title = get_the_title();

            if ( $title === '' ) {
                continue;
            }

            $title = preg_replace( '/\&#8217;/', '\u2019', $title );

            $families_arr = wp_get_post_terms( $kw_id, $cat_name, [ 'fields' => 'ids' ] );
            $families_cls = '';
            if ( is_array( $families_arr ) ) {
                $families_cls = implode( ' ', array_map(
                    fn( $fid ) => 'tooltipy-kw-cat-' . $fid,
                    $families_arr
                ) );
            }

            $icon_url        = '';
            $choose_icon     = get_post_meta( $kw_id, 'kttg_choose_icon_type', true );
            $kttg_icon_url   = (string) get_post_meta( $kw_id, 'kttg_icon_url', true );
            $kttg_icon_id    = (string) get_post_meta( $kw_id, 'kttg_icon_id', true );

            if ( ! empty( $choose_icon ) ) {
                if ( $choose_icon === 'url' ) {
                    $icon_url = esc_url_raw( $kttg_icon_url, [ 'http', 'https' ] );
                } else {
                    $tmp = wp_get_attachment_image_src( (int) $kttg_icon_id, 'full' );
                    $icon_url = esc_url_raw( (string) ( $tmp[0] ?? '' ), [ 'http', 'https' ] );
                }
            }

            $result[] = new KeywordData( [
                'kw_id'         => $kw_id,
                'term'          => $title,
                'syns'          => Sanitizer::synonyms( (string) get_post_meta( $kw_id, 'bluet_synonyms_keywords', true ) ),
                'case'          => get_post_meta( $kw_id, 'bluet_case_sensitive_word', true ) === 'on',
                'pref'          => get_post_meta( $kw_id, 'bluet_prefix_keywords', true ) === 'on',
                'families_class'=> $families_cls,
                'youtube'       => Sanitizer::youtube_id( (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true ) ),
                'icon'          => $icon_url,
                'img'           => get_the_post_thumbnail( $kw_id, 'medium' ),
                'dfn'           => Sanitizer::tooltip_html( (string) get_the_content() ),
            ] );
        }

        wp_reset_postdata();

        return $result;
    }

    /**
     * Fetch a specific set of keywords by IDs (for AJAX).
     *
     * @param int[] $ids
     * @return \WP_Query
     */
    public function query_by_ids( array $ids ): \WP_Query {
        $args = [
            'post_type'      => $this->plugin->get_tooltip_post_types(),
            'posts_per_page' => -1,
        ];

        $adv_options          = get_option( 'bluet_kw_advanced' );
        $fetch_all            = ! empty( $adv_options['kttg_fetch_all_keywords'] ) && $adv_options['kttg_fetch_all_keywords'] === 'on';

        if ( ! $fetch_all ) {
            $args['post__in'] = $ids;
        }

        return new \WP_Query( $args );
    }
}
