<?php
namespace Tooltipy\Glossary;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Handles glossary shortcodes.
 * Canonical: [tooltip_glossary] (TLTPY_GLOSSARY_SHORTCODE).
 * Legacy aliases: [kttg_glossary], [tooltipy_glossary].
 */
class GlossaryShortcode {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        // Canonical + historical names — all kept for existing content.
        add_shortcode( TLTPY_GLOSSARY_SHORTCODE, [ $this, 'render' ] ); // tooltip_glossary
        add_shortcode( 'kttg_glossary',          [ $this, 'render' ] );
        add_shortcode( 'tooltipy_glossary',      [ $this, 'render' ] );
    }

    /**
     * Render the glossary HTML.
     *
     * @return string
     */
    public function render(): string {
        global $wpdb, $is_kttg_glossary_page;

        $pt_name  = $this->plugin->get_post_type_name();
        $cat_name = $this->plugin->get_cat_name();
        $types    = $this->plugin->get_tooltip_post_types();

        $glossary_options = get_option( 'bluet_glossary_options', [] );

        $label_select_family     = $this->label( $glossary_options, 'kttg_glossary_text_select_a_family', 'Select a family' );
        $label_all_families      = $this->label( $glossary_options, 'kttg_glossary_text_select_all_families', 'All families' );
        $text_all                = $this->label( $glossary_options, 'kttg_glossary_text_all', __( 'ALL', 'tooltipy-lang' ) );
        $text_next               = $this->label( $glossary_options, 'kttg_glossary_text_next', __( 'Next', 'tooltipy-lang' ) );
        $text_previous           = $this->label( $glossary_options, 'kttg_glossary_text_previous', __( 'Previous', 'tooltipy-lang' ) );
        $show_thumb              = ! empty( $glossary_options['tltpy_glossary_show_thumb'] ) && $glossary_options['tltpy_glossary_show_thumb'] === 'on';

        // Flag: suppress tooltips inside the glossary
        $is_kttg_glossary_page = true;

        // Save/update the glossary page permalink
        $permalink = get_the_permalink();
        if ( ! get_option( 'tltpy_glossary_page' ) ) {
            add_option( 'tltpy_glossary_page', $permalink );
        } elseif ( get_option( 'tltpy_glossary_page' ) !== $permalink ) {
            update_option( 'tltpy_glossary_page', $permalink );
        }

        $current_letter_class = empty( $_GET['letter'] ) ? 'bluet_glossary_current_letter' : '';
        $all_link             = get_permalink();
        if ( ! empty( $_GET['cat'] ) ) {
            $all_link = add_query_arg( 'cat', esc_html( $_GET['cat'] ), $all_link );
        }

        // --- Family dropdown ---
        $ret  = "<div class='kttg_glossary_div'>";
        $ret .= "<div class='kttg_glossary_families'><label>" . esc_html( $label_select_family ) . " : </label>";
        $ret .= "<select name='kttg-glossary-family' onchange='document.location.href=changeQueryStringParameter(\"" . esc_url( get_permalink() ) . "\",\"cat\",this.options[this.selectedIndex].value);'>";
        $ret .= "<option value='all_families'>" . esc_html( $label_all_families ) . "</option>";

        $families = get_categories( [ 'taxonomy' => $cat_name ] );
        foreach ( $families as $family ) {
            $selected = ( ! empty( $_GET['cat'] ) && esc_html( $_GET['cat'] ) === $family->category_nicename ) ? 'selected' : '';
            $ret .= '<option value="' . esc_attr( $family->category_nicename ) . '" ' . $selected . '>';
            $ret .= esc_html( $family->cat_name ) . ' (' . (int) $family->category_count . ')';
            $ret .= '</option>';
        }
        $ret .= "</select></div>";

        // --- Letter navigation ---
        $ret .= '<div class="kttg_glossary_header"><span class="bluet_glossary_all ' . esc_attr( $current_letter_class ) . '"><a href=\'' . esc_url( $all_link ) . '\'>' . esc_html( $text_all ) . '</a></span> - ';

        $chars_count = $this->get_chars_count( $types, $cat_name );

        $current_glossary_url = get_permalink();
        foreach ( $chars_count as $char => $count ) {
            $letter_link = add_query_arg( 'letter', $char, $current_glossary_url );
            if ( ! empty( $_GET['cat'] ) ) {
                $letter_link = add_query_arg( 'cat', esc_attr( $_GET['cat'] ), $letter_link );
            }

            $active_cls = ( ! empty( $_GET['letter'] ) && esc_html( $_GET['letter'] ) === $char ) ? 'bluet_glossary_current_letter' : '';
            $ret .= ' <span class="bluet_glossary_letter bluet_glossary_found_letter ' . esc_attr( $active_cls ) . '"><a href=\'' . esc_url( $letter_link ) . '\'>' . esc_html( $char ) . '<span class="bluet_glossary_letter_count">' . (int) $count . '</span></a></span>';
        }
        $ret .= '</div>';

        // --- Keyword listing ---
        $chosen_letter = null;
        $postids       = [];

        if ( ! empty( $_GET['letter'] ) ) {
            $chosen_letter = esc_html( $_GET['letter'] );
            $postids       = (array) $wpdb->get_col( $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE SUBSTR(post_title,1,1) = %s AND post_type = %s AND post_status = 'publish' ORDER BY post_title",
                $chosen_letter,
                $pt_name
            ) );
        }

        $paged     = max( 1, (int) get_query_var( 'paged' ) );
        $showposts = -1;
        if ( isset( $glossary_options['kttg_kws_per_page'] ) && trim( $glossary_options['kttg_kws_per_page'] ) !== '' ) {
            $showposts = (int) $glossary_options['kttg_kws_per_page'];
        }

        $args = [
            'post__in'       => $postids,
            'post_type'      => $types,
            'order'          => 'ASC',
            'orderby'        => 'title',
            'posts_per_page' => $showposts,
            'paged'          => $paged,
        ];

        $current_family = '';
        if ( ! empty( $_GET['cat'] ) && esc_html( $_GET['cat'] ) !== 'all_families' ) {
            $current_family      = esc_html( $_GET['cat'] );
            $args['tax_query']   = [ [
                'taxonomy' => $cat_name,
                'field'    => 'slug',
                'terms'    => $current_family,
            ] ];
        }

        $query = new \WP_Query( $args );

        if ( $query->have_posts() ) {
            $link_titles = ! empty( $glossary_options['link_titles'] ) && $glossary_options['link_titles'] === 'on';

            $ret .= '<div class="kttg_glossary_content"><ul>';

            while ( $query->have_posts() ) {
                $query->the_post();

                if ( $chosen_letter !== null && strtoupper( mb_substr( get_the_title(), 0, 1, 'utf-8' ) ) !== $chosen_letter ) {
                    continue;
                }

                $families_list   = wp_get_post_terms( get_the_id(), $cat_name, [ 'fields' => 'slugs' ] );
                $safe_title      = esc_html( get_the_title() );
                $title_wrap      = $link_titles
                    ? '<a href="' . esc_url( get_permalink() ) . '">' . $safe_title . '</a>'
                    : $safe_title;

                $thumb = '';
                if ( $show_thumb && has_post_thumbnail() ) {
                    $thumb = '<div class="kttg_glossary_element_thumbnail">' . get_the_post_thumbnail() . '</div>';
                }

                $ret .= '<li class="kttg_glossary_element" style="list-style-type:none;">';
                $ret .= '<h2 class="kttg_glossary_element_title">' . $title_wrap . ' ';

                if ( count( $families_list ) > 0 ) {
                    $ret .= '<sub>[';
                    foreach ( $families_list as $key => $fam_slug ) {
                        $fam_slug = sanitize_title( (string) $fam_slug );
                        $fam_link = add_query_arg( 'cat', $fam_slug, $current_glossary_url );
                        $ret .= ' <a href="' . esc_url( $fam_link ) . '">' . esc_html( $fam_slug ) . '</a>';
                        $ret .= ( $key + 1 === count( $families_list ) ) ? ' ' : ', ';
                    }
                    $ret .= ']</sub>';
                }

                $ret .= '</h2>';
                $content = wp_kses_post( (string) apply_filters( 'the_content', get_post_field( 'post_content', get_the_ID() ) ) );
                $ret .= '<div class="kttg_glossary_element_content">' . $thumb . $content . '</div>';
                $ret .= '</li>';
            }

            $ret .= '</ul></div>';

            $ret .= (string) get_previous_posts_link( '<span class="kttg_glossary_nav prev">' . esc_html( $text_previous ) . '</span>' );
            $ret .= ' ';
            $ret .= (string) get_next_posts_link( '<span class="kttg_glossary_nav next">' . esc_html( $text_next ) . '</span>', $query->max_num_pages );
            $ret .= '</div>';

            wp_reset_postdata();
        } else {
            $ret .= '<p>' . esc_html__( 'Sorry, no posts matched your criteria.' ) . '</p>';
        }

        return $ret;
    }

    /* ---- Helpers ---- */

    private function label( array $options, string $key, string $default ): string {
        $val = $options['kttg_glossary_text'][ $key ] ?? $default;
        return ( is_string( $val ) && trim( $val ) !== '' ) ? $val : $default;
    }

    private function get_chars_count( array $types, string $cat_name ): array {
        $args = [
            'post_type'      => $types,
            'order'          => 'ASC',
            'orderby'        => 'title',
            'posts_per_page' => -1,
        ];

        if ( ! empty( $_GET['cat'] ) && esc_html( $_GET['cat'] ) !== 'all_families' ) {
            $args['tax_query'] = [ [
                'taxonomy' => $cat_name,
                'field'    => 'slug',
                'terms'    => esc_html( $_GET['cat'] ),
            ] ];
        }

        $query  = new \WP_Query( $args );
        $counts = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $char = strtoupper( mb_substr( get_the_title(), 0, 1, 'utf-8' ) );
                $counts[ $char ] = ( $counts[ $char ] ?? 0 ) + 1;
            }
        }
        wp_reset_postdata();

        return $counts;
    }
}
