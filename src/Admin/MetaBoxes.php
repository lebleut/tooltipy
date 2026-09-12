<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;

/**
 * Registers and renders admin metaboxes for keyword posts and regular posts.
 * Replaces meta-boxes.php.
 */
class MetaBoxes {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'edit_form_after_title', [ $this, 'render_after_title_metaboxes' ] );
        add_action( 'do_meta_boxes',         [ $this, 'register_metaboxes' ] );
        add_action( 'save_post',             [ $this, 'save' ] );
    }

    public function render_after_title_metaboxes(): void {
        global $post, $wp_meta_boxes, $post_type;
        do_meta_boxes( $post_type, 'after_title', $post );
    }

    public function register_metaboxes(): void {
        $pt_name = $this->plugin->get_post_type_name();

        // Keyword settings metabox (on keyword edit screen)
        add_meta_box(
            'bluet_kw_settings_meta',
            __( 'Keyword Settings', 'tooltipy-lang' ),
            [ $this, 'render_keyword_settings' ],
            $pt_name,
            'after_title',
            'high'
        );

        // Related keywords metabox (on all other post types)
        foreach ( get_post_types() as $screen ) {
            if ( $screen !== $pt_name ) {
                add_meta_box(
                    'bluet_kw_post_related_keywords_meta',
                    __( 'Keywords related', 'tooltipy-lang' ) . ' (KTTG)',
                    [ $this, 'render_related_keywords' ],
                    $screen,
                    'side',
                    'high'
                );
            }
        }

        // Advanced: CPT metaboxes registered by advanced settings
        foreach ( $this->get_custom_post_types_to_filter() as $cpt ) {
            if ( post_type_exists( $cpt ) && ! in_array( $cpt, [ 'post', 'page' ], true ) ) {
                add_meta_box(
                    'bluet_kw_posttypes_related_keywords_meta',
                    __( 'Keywords related', 'tooltipy-lang' ) . ' (KTTG)',
                    [ $this, 'render_related_keywords' ],
                    $cpt,
                    'side',
                    'high'
                );
            }
        }
    }

    public function render_keyword_settings(): void {
        $kw_id = get_the_id();
        wp_nonce_field( 'tooltipy_save_metaboxes', 'tooltipy_metabox_nonce' );
        ?>
        <p>
            <label for="bluet_synonyms_id"><?php esc_html_e( 'Synonyms', 'tooltipy-lang' ); ?></label>
            <input type="text"
                id="bluet_synonyms_id"
                name="bluet_synonyms_name"
                value="<?php echo esc_attr( (string) get_post_meta( $kw_id, 'bluet_synonyms_keywords', true ) ); ?>"
                placeholder="<?php esc_attr_e( "Type here the keyword's Synonyms separated with '|'", 'tooltipy-lang' ); ?>"
                style="width:100%;"
            />
        </p>
        <p>
            <label for="bluet_case_sensitive_id"><?php echo wp_kses( __( 'Make this keyword <b>Case Sensitive</b>', 'tooltipy-lang' ), [ 'b' => [] ] ); ?></label>
            <input type="checkbox"
                id="bluet_case_sensitive_id"
                name="bluet_case_sensitive_name"
                <?php checked( get_post_meta( $kw_id, 'bluet_case_sensitive_word', true ), 'on' ); ?>
            />
        </p>
        <p>
            <label for="bluet_prefix_id"><?php esc_html_e( 'Prefix', 'tooltipy-lang' ); ?></label>
            <input id="bluet_prefix_id" name="bluet_prefix_name" type="checkbox" <?php checked( get_post_meta( $kw_id, 'bluet_prefix_keywords', true ), 'on' ); ?> />
            <span class="description"><?php esc_html_e( 'Match words that start with this keyword (e.g. "photo" matches "photography").', 'tooltipy-lang' ); ?></span>
        </p>
        <p>
            <label for="bluet_video_id"><?php esc_html_e( 'YouTube video', 'tooltipy-lang' ); ?></label><br>
            WWW.Youtube.com/watch?v=
            <input id="bluet_video_id" name="bluet_video_id_name" type="text" value="<?php echo esc_attr( (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true ) ); ?>" />
            <?php if ( is_readable( TOOLTIPY_PLUGIN_DIR . 'assets/youtube_play.png' ) ) : ?>
                <img src="<?php echo esc_url( TOOLTIPY_PLUGIN_URL . 'assets/youtube_play.png' ); ?>" alt="" style="position: relative; top: 5px;" />
            <?php endif; ?>
        </p>
        <?php
    }

    public function render_related_keywords(): void {
        global $post;

        $pt_name              = $this->plugin->get_post_type_name();
        $post_id              = $post->ID;
        $exclude_me           = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );
        $exclude_kws_string   = (string) get_post_meta( $post_id, 'bluet_exclude_keywords_from_matching', true );
        $excluded_kws         = array_filter(
            array_map( 'strtolower', array_map( 'trim', explode( ',', $exclude_kws_string ) ) )
        );

        $repo     = new KeywordRepository( $this->plugin );
        $kw_ids   = $repo->get_related_ids( $post_id );

        wp_nonce_field( 'tooltipy_save_metaboxes', 'tooltipy_metabox_nonce' );
        ?>
        <div>
            <h3><?php esc_html_e( 'Exclude this post from being matched', 'tooltipy-lang' ); ?></h3>
            <input type="checkbox"
                id="bluet_kw_admin_exclude_post_from_matching_id"
                onclick="hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id','bluet_kw_admin_div_terms')"
                name="bluet_exclude_post_from_matching_name"
                <?php checked( $exclude_me, 'on' ); ?>
            />
            <label for="bluet_kw_admin_exclude_post_from_matching_id" style="color:red;">
                <?php esc_html_e( 'Exclude this post', 'tooltipy-lang' ); ?>
            </label>

            <div id="bluet_kw_admin_div_terms">
                <?php if ( ! empty( $kw_ids ) ) : ?>
                    <h3><?php esc_html_e( 'Keywords related', 'tooltipy-lang' ); ?></h3>
                    <ul style="list-style: initial; padding-left: 20px;">
                        <?php foreach ( $kw_ids as $kw_id ) :
                            $title = get_the_title( $kw_id );
                            $color = in_array( strtolower( trim( $title ) ), $excluded_kws, true ) ? 'red' : 'green';
                            ?>
                            <li style="color:<?php echo esc_attr( $color ); ?>;"><i><?php echo esc_html( $title ); ?></i></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e( 'No KeyWords found for this post', 'tooltipy-lang' ); ?></p>
                <?php endif; ?>

                <h3><?php esc_html_e( 'Keywords to exclude', 'tooltipy-lang' ); ?></h3>
                <div class="easy_tags">
                    <div class="easy_tags-content" onclick="jQuery('#bluet_cover_areas_id').focus()">
                        <div class="easy_tags-list tagchecklist" id="cover_areas_list"></div>
                        <input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_areas_id" placeholder="<?php esc_attr_e( 'keyword...', 'tooltipy-lang' ); ?>">
                        <input class="easy_tags-to_send" type="hidden" name="bluet_exclude_keywords_from_matching_name" id="exclude-keywords-field" value="<?php echo esc_attr( $exclude_kws_string ); ?>">
                    </div>
                    <input class="easy_tags-add button tagadd" type="button" value="<?php esc_attr_e( 'Add' ); ?>" id="cover_class_add">
                </div>
                <script>
                jQuery(document).ready(function(){
                    var field = easy_tags.construct(",");
                    field.init(".easy_tags");
                    field.fill_classes(".easy_tags");
                });
                </script>

                <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $pt_name ) ); ?>">
                    <?php echo esc_html__( 'Manage KeyWords', 'tooltipy-lang' ) . ' >>'; ?>
                </a></p>
            </div>
        </div>
        <?php
    }

    public function save( int $post_id ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( empty( $_POST['post_type'] ) || ( $_POST['action'] ?? '' ) !== 'editpost' ) {
            return;
        }

        if ( ! isset( $_POST['tooltipy_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tooltipy_metabox_nonce'] ) ), 'tooltipy_save_metaboxes' ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $pt_name = $this->plugin->get_post_type_name();

        if ( $_POST['post_type'] === $pt_name ) {
            $syns = isset( $_POST['bluet_synonyms_name'] )
                ? sanitize_text_field( wp_unslash( $_POST['bluet_synonyms_name'] ) )
                : '';
            $syns = (string) preg_replace( '(\|{2,100})', '|', $syns );
            $syns = (string) preg_replace( '(^\||\|$|[\s]{2,100})', '', $syns );

            $case = isset( $_POST['bluet_case_sensitive_name'] )
                ? sanitize_text_field( wp_unslash( $_POST['bluet_case_sensitive_name'] ) )
                : '';

            $prefix  = isset( $_POST['bluet_prefix_name'] ) ? 'on' : '';
            $youtube = isset( $_POST['bluet_video_id_name'] )
                ? sanitize_text_field( wp_unslash( $_POST['bluet_video_id_name'] ) )
                : '';

            update_post_meta( $post_id, 'bluet_synonyms_keywords', $syns );
            update_post_meta( $post_id, 'bluet_case_sensitive_word', $case );
            update_post_meta( $post_id, 'bluet_prefix_keywords', $prefix );
            update_post_meta( $post_id, 'bluet_youtube_video_id', $youtube );
        } else {
            $exclude_me = ! empty( $_POST['bluet_exclude_post_from_matching_name'] )
                ? sanitize_text_field( wp_unslash( $_POST['bluet_exclude_post_from_matching_name'] ) )
                : '';
            $exclude_kws = sanitize_text_field( wp_unslash( $_POST['bluet_exclude_keywords_from_matching_name'] ?? '' ) );

            update_post_meta( $post_id, 'bluet_exclude_post_from_matching', $exclude_me );
            update_post_meta( $post_id, 'bluet_exclude_keywords_from_matching', $exclude_kws );

            $matchable = $_POST['matchable_keywords'] ?? [];
            $arr_match = [];
            if ( is_array( $matchable ) ) {
                foreach ( $matchable as $mid ) {
                    $mid = (int) $mid;
                    if ( $mid > 0 ) {
                        $arr_match[ $mid ] = $mid;
                    }
                }
            }
            update_post_meta( $post_id, 'bluet_matching_keywords_field', $arr_match );
        }
    }

    /** Returns custom post types to filter (from advanced settings). */
    private function get_custom_post_types_to_filter(): array {
        $options = get_option( 'bluet_kw_advanced', [] );
        $types   = [];

        if ( ! empty( $options['bt_kw_in_concern_custom_posts']['post_types'] ) ) {
            $types = (array) $options['bt_kw_in_concern_custom_posts']['post_types'];
        }

        if ( ! empty( $options['bt_kw_supported_plugins']['bbpress'] ) ) {
            $types[] = 'topic';
        }
        if ( ! empty( $options['bt_kw_supported_plugins']['wooc'] ) ) {
            $types[] = 'product';
        }

        return array_filter( array_unique( $types ) );
    }
}
