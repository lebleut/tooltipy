<?php
namespace Tooltipy\PostType;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Registers the Keyword custom post type and its family taxonomy.
 * Replaces the former bluet_keyword class.
 */
class KeywordPostType {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function register(): void {
        $this->register_post_type();
        $this->register_taxonomy();
        $this->maybe_flush_rewrite_rules();
        $this->add_admin_columns();
        $this->add_family_filter();
    }

    private function register_post_type(): void {
        $pt_name    = $this->plugin->get_post_type_name();
        $capability = $this->plugin->get_capability();

        $args = [
            'labels'    => [
                'name'               => __( 'My KeyWords', 'tooltipy-lang' ),
                'singular_name'      => __( 'KeyWord', 'tooltipy-lang' ),
                'menu_name'          => __( 'Tooltipy', 'tooltipy-lang' ),
                'name_admin_bar'     => __( 'My KeyWords', 'tooltipy-lang' ),
                'all_items'          => __( 'My KeyWords', 'tooltipy-lang' ),
                'add_new'            => __( 'Add' ),
                'add_new_item'       => __( 'New', 'tooltipy-lang' ) . ' ' . __( 'KeyWord', 'tooltipy-lang' ),
                'edit_item'          => __( 'Edit', 'tooltipy-lang' ) . ' ' . __( 'KeyWord', 'tooltipy-lang' ),
                'new_item'           => __( 'New', 'tooltipy-lang' ) . ' ' . __( 'KeyWord', 'tooltipy-lang' ),
                'view_item'          => __( 'View', 'tooltipy-lang' ) . ' ' . __( 'KeyWord', 'tooltipy-lang' ),
                'search_items'       => __( 'Search for KeyWords', 'tooltipy-lang' ),
                'not_found'          => __( 'KeyWords not found', 'tooltipy-lang' ),
                'not_found_in_trash' => __( 'KeyWords not found in trash', 'tooltipy-lang' ),
                'parent_item_colon'  => __( 'Parent KeyWords colon', 'tooltipy-lang' ),
            ],
            'public'    => true,
            'supports'  => [ 'title', 'editor', 'thumbnail', 'author' ],
            'menu_icon' => TOOLTIPY_PLUGIN_URL . 'assets/ico_16x16.png',
        ];

        if ( $capability !== 'manage_options' ) {
            $args['capabilities'] = [
                'edit_post'     => $capability,
                'edit_posts'    => $capability,
                'publish_posts' => $capability,
                'delete_post'   => $capability,
            ];
        }

        $args = apply_filters( 'tltpy_post_type_args', $args );

        register_post_type( $pt_name, $args );
    }

    private function register_taxonomy(): void {
        register_taxonomy(
            $this->plugin->get_cat_name(),
            $this->plugin->get_post_type_name(),
            [
                'labels'             => [ 'name' => __( 'Families', 'tooltipy-lang' ) ],
                'hierarchical'       => true,
                'show_ui'            => 'radio',
                'show_admin_column'  => true,
            ]
        );
    }

    private function maybe_flush_rewrite_rules(): void {
        if ( get_option( 'tooltipy_activated_just_now', false ) ) {
            flush_rewrite_rules();
            delete_option( 'tooltipy_activated_just_now' );
        }
    }

    private function add_admin_columns(): void {
        $pt_name = $this->plugin->get_post_type_name();

        add_filter(
            "manage_{$pt_name}_posts_columns",
            static function ( array $defaults ): array {
                $cols = [
                    'cb'          => $defaults['cb'] ?? '',
                    'the_picture' => __( 'Picture', 'tooltipy-lang' ),
                    'title'       => $defaults['title'] ?? __( 'Title' ),
                    'is_prefix'   => __( 'Is Prefix ?', 'tooltipy-lang' ),
                    'is_video'    => __( 'Video tooltip', 'tooltipy-lang' ),
                    'date'        => $defaults['date'] ?? __( 'Date' ),
                ];
                return $cols;
            }
        );

        add_action(
            "manage_{$pt_name}_posts_custom_column",
            static function ( string $column_name, int $post_id ): void {
                if ( $column_name === 'the_picture' ) {
                    echo get_the_post_thumbnail( $post_id, [ 75, 75 ] );
                } elseif ( $column_name === 'is_prefix' ) {
                    echo get_post_meta( $post_id, 'bluet_prefix_keywords', true ) === 'on'
                        ? '✔'
                        : '';
                } elseif ( $column_name === 'is_video' ) {
                    $yt = (string) get_post_meta( $post_id, 'bluet_youtube_video_id', true );
                    echo strlen( $yt ) > 5 ? '🎬' : '';
                }
            },
            10,
            2
        );
    }

    private function add_family_filter(): void {
        $pt_name  = $this->plugin->get_post_type_name();
        $cat_name = $this->plugin->get_cat_name();

        add_action(
            'restrict_manage_posts',
            function () use ( $pt_name, $cat_name ): void {
                $type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

                if ( $type !== $pt_name ) {
                    return;
                }

                $families = get_terms( $cat_name );
                $current  = isset( $_GET['tooltipy_family'] ) ? esc_html( $_GET['tooltipy_family'] ) : '';

                echo '<select name="tooltipy_family" id="tooltipy_filter_by_family">';
                echo '<option value="">' . esc_html__( 'Filter by Family', 'tooltipy-lang' ) . '</option>';
                foreach ( (array) $families as $fam ) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr( $fam->slug ),
                        selected( $fam->slug, $current, false ),
                        esc_html( $fam->name )
                    );
                }
                echo '</select>';
            }
        );

        add_filter(
            'parse_query',
            function ( \WP_Query $query ) use ( $pt_name, $cat_name ): void {
                global $pagenow;
                $type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

                if (
                    $type === $pt_name
                    && is_admin()
                    && $pagenow === 'edit.php'
                    && isset( $_GET['tooltipy_family'] )
                    && $_GET['tooltipy_family'] !== ''
                ) {
                    $query->query_vars[ $cat_name ] = esc_html( $_GET['tooltipy_family'] );
                }
            }
        );
    }
}
