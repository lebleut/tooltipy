<?php
namespace Tooltipy\Widget;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;

/**
 * Sidebar widget showing related keywords for the current post.
 * Replaces widget.php / bluet_keyword_widget.
 *
 * Note: WordPress instantiates widgets via new ClassName() with no args,
 * so we access the Plugin singleton through Plugin::get_instance().
 */
class KeywordWidget extends \WP_Widget {

    public function __construct() {
        parent::__construct(
            'my_keywords_widget',
            '',
            [
                'name'        => 'My keywords (KTTG)',
                'description' => 'Contains keywords used in the current single post.',
            ]
        );
    }

    /**
     * Called by Plugin to register the widget with WordPress.
     */
    public static function register(): void {
        add_action( 'widgets_init', function (): void {
            register_widget( self::class );
        } );
    }

    /** @param array $args @param array $instance */
    public function widget( $args, $instance ): void {
        global $is_kttg_glossary_page;

        if ( ! ( is_single() || is_page() ) ) {
            return;
        }

        $plugin     = Plugin::get_instance();
        $post_id    = get_the_id();
        $exclude_me = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );

        if ( $exclude_me || $is_kttg_glossary_page ) {
            return;
        }

        $style_opt = get_option( 'bluet_kw_style', [] );
        $css_kw    = $style_opt['bt_kw_add_css_classes']['keyword'] ?? '';

        $repo    = new KeywordRepository( $plugin );
        $kw_ids  = $repo->get_related_ids( $post_id );

        $manual = get_post_meta( $post_id, 'bluet_matching_keywords_field', true );
        if ( ! empty( $manual ) ) {
            $kw_ids = (array) $manual;
        }

        $cat_name = $plugin->get_cat_name();
        $types    = $plugin->get_tooltip_post_types();

        echo wp_kses_post( $args['before_widget'] );
        echo wp_kses_post( $args['before_title'] );
        echo esc_html( $instance['title'] ?? '' );
        echo wp_kses_post( $args['after_title'] );
        echo '<ul>';

        if ( ! empty( $kw_ids ) ) {
            foreach ( $kw_ids as $term_id ) {
                $kw_id       = 0;
                $title       = '';
                $families_cls = '';
                $video_class = '';

                $q = new \WP_Query( [ 'p' => (int) $term_id, 'post_type' => $types ] );

                if ( $q->have_posts() ) {
                    while ( $q->have_posts() ) {
                        $q->the_post();
                        $kw_id = get_the_id();
                        $title = str_replace( '&#8217;', "'", get_the_title() );

                        $families_arr = wp_get_post_terms( $kw_id, $cat_name, [ 'fields' => 'ids' ] );
                        $families_cls = '';
                        if ( is_array( $families_arr ) ) {
                            $families_cls = implode( ' ', array_map( fn( $fid ) => 'tooltipy-kw-cat-' . $fid, $families_arr ) );
                        }

                        $youtube     = (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true );
                        $video_class = strlen( $youtube ) > 5 ? 'tooltipy-kw-youtube' : '';
                    }
                }

                wp_reset_postdata();

                if ( $kw_id === 0 ) {
                    continue;
                }

                // Add zero-width non-joiner to prevent tooltip overlapping inside widget
                $title_display = esc_html( $title ) . '&zwnj;';

                echo '<li>';
                echo '<span class="bluet_tooltip tooltipy-kw tooltipy-kw-' . esc_attr( (string) $kw_id ) . ' ' . esc_attr( trim( $families_cls . ' ' . $video_class . ' ' . $css_kw ) ) . '" data-tooltip="' . esc_attr( (string) $kw_id ) . '">';
                echo $title_display;
                echo '</span></li>';
            }
        } else {
            esc_html_e( 'no terms found for this post', 'tooltipy-lang' );
        }

        echo '</ul>';
        echo wp_kses_post( $args['after_widget'] );
    }

    /** @param array $instance */
    public function form( $instance ): void {
        ?>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title : </label>
        <input
            class="widefat"
            id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
            name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
            value="<?php echo isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : ''; ?>"
        />
        <?php
    }

    /** @param array $new_instance @param array $old_instance */
    public function update( $new_instance, $old_instance ): array {
        return [ 'title' => sanitize_text_field( $new_instance['title'] ?? '' ) ];
    }
}
