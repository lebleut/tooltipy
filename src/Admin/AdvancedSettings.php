<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Advanced settings section (cover areas, exclude areas, headings, MCE, etc.)
 * Replaces advanced/settings-page.php.
 */
class AdvancedSettings {

    private Plugin $plugin;

    private const COMMON_TAGS = [ 'strong', 'b', 'abbr', 'button', 'dfn', 'em', 'i', 'label' ];

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'admin_init',  [ $this, 'register_settings' ] );
        add_action( 'admin_init',  [ $this, 'register_mce_buttons' ] );
        add_action( 'init',        [ $this, 'register_advanced_init_hooks' ] );
    }

    public function register_settings(): void {
        add_settings_section( 'advanced_section', __( 'Advance settings for KTTG :', 'tooltipy-lang' ), [ $this, 'section_cb' ], 'my_keywords_advanced_page' );

        $fields = [
            'kttg_cover_areas'                 => [ __( 'Cover CSS classes', 'tooltipy-lang' ),              [ $this, 'field_cover_areas' ] ],
            'kttg_cover_tags'                  => [ __( 'Cover HTML TAGS', 'tooltipy-lang' ),                [ $this, 'field_cover_tags' ] ],
            'kttg_exclude_areas'               => [ __( 'Exclude CSS classes', 'tooltipy-lang' ),            [ $this, 'field_exclude_areas' ] ],
            'kttg_exclude_anchor_tags'         => [ __( 'Exclude links', 'tooltipy-lang' ) . ' ?',           [ $this, 'field_exclude_anchors' ] ],
            'kttg_exclude_heading_tags'        => [ __( 'Exclude Headings', 'tooltipy-lang' ) . ' ?',        [ $this, 'field_exclude_headings' ] ],
            'kttg_exclude_common_tags'         => [ __( 'Exclude Common Tags', 'tooltipy-lang' ) . ' ?',     [ $this, 'field_exclude_common_tags' ] ],
            'bt_kw_adv_style'                  => [ __( 'Advanced Style', 'tooltipy-lang' ),                 [ $this, 'field_adv_style' ] ],
            'kttg_fetch_all_keywords'          => [ __( 'Load all keywords', 'tooltipy-lang' ),              [ $this, 'field_fetch_all' ] ],
            'kttg_custom_events'               => [ __( 'Events to fetch', 'tooltipy-lang' ),                [ $this, 'field_custom_events' ] ],
            'tooltipy_prevent_plugins_filters' => [ __( 'Prevent other plugins filters', 'tooltipy-lang' ),  [ $this, 'field_prevent_filters' ] ],
        ];

        foreach ( $fields as $id => [ $label, $cb ] ) {
            add_settings_field( $id, $label, $cb, 'my_keywords_advanced_page', 'advanced_section' );
        }

        // Post types / custom fields configuration
        add_settings_field( 'bt_kw_in_concern_custom_posts', __( 'Post types to filter', 'tooltipy-lang' ), [ $this, 'field_post_types_filter' ], 'my_keywords_advanced_page', 'advanced_section' );

        register_setting( 'settings_group', 'bluet_kw_advanced' );
    }

    public function register_mce_buttons(): void {
        add_filter( 'mce_external_plugins', [ $this, 'mce_add_plugin' ] );
        add_filter( 'mce_buttons',          [ $this, 'mce_register_button' ] );
        add_editor_style( TOOLTIPY_PLUGIN_URL . 'assets/kttg-mce.css' );
    }

    public function mce_add_plugin( array $plugins ): array {
        $plugins['bluetKFI'] = TOOLTIPY_PLUGIN_URL . 'assets/bluetkfi-plugin.js';
        return $plugins;
    }

    public function mce_register_button( array $buttons ): array {
        $buttons[] = 'tltpy_kttg_img';
        return $buttons;
    }

    public function register_advanced_init_hooks(): void {
        // Filter: add CPT metaboxes from advanced settings
        add_action( 'do_meta_boxes', function (): void {
            foreach ( $this->get_post_types_to_filter() as $cpt ) {
                if ( post_type_exists( $cpt ) && ! in_array( $cpt, [ 'post', 'page' ], true ) ) {
                    add_meta_box(
                        'bluet_kw_posttypes_related_keywords_meta',
                        __( 'Keywords related', 'tooltipy-lang' ) . ' (KTTG)',
                        function () {
                            // Rendered by MetaBoxes::render_related_keywords via do_meta_boxes
                        },
                        $cpt,
                        'side',
                        'high'
                    );
                }
            }
        } );

        // Filter: post types to match
        add_filter( 'tltpy_posttypes_to_match', function ( array $types ): array {
            $from_adv = $this->get_post_types_to_filter();
            if ( ! empty( $from_adv ) ) {
                return $from_adv; // replaces defaults if advanced is configured
            }
            return $types;
        } );

        // Filter: custom fields hooks
        add_filter( 'tltpy_custom_fields_hooks', function ( array $hooks ): array {
            $from_adv = $this->get_custom_fields_to_filter();
            if ( ! empty( $from_adv ) ) {
                return $from_adv;
            }
            return $hooks;
        } );
    }

    /* ---- Section callback ---- */
    public function section_cb(): void {
        echo '<div id="keywords-advanced">' . esc_html__( 'Advanced space', 'tooltipy-lang' ) . '.</div>';
    }

    /* ---- Field callbacks ---- */

    public function field_cover_areas(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        $val     = $options['kttg_cover_areas'] ?? '';
        ?>
        <div class="easy_tags" data-easy-tags-delimiter=" ">
            <div class="easy_tags-content" onclick="jQuery('#bluet_cover_areas_id').focus()">
                <div class="easy_tags-list tagchecklist" id="cover_areas_list"></div>
                <input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_areas_id" placeholder="<?php esc_attr_e( 'class ...', 'tooltipy-lang' ); ?>">
                <input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_cover_areas]" value="<?php echo esc_attr( $val ); ?>">
            </div>
            <input class="easy_tags-add button tagadd" type="button" value="<?php esc_attr_e( 'Add' ); ?>" id="cover_class_add">
        </div>
        <p style="color:green;"><?php esc_html_e( 'Choose CSS classes to cover with tooltips', 'tooltipy-lang' ); ?></p>
        <?php
    }

    public function field_cover_tags(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        $val     = $options['kttg_cover_tags'] ?? '';
        ?>
        <div class="easy_tags" data-easy-tags-delimiter=" ">
            <div class="easy_tags-content" onclick="jQuery('#bluet_cover_tags_id').focus()">
                <div class="easy_tags-list tagchecklist" id="cover_tags_list"></div>
                <input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_tags_id" placeholder="<?php esc_attr_e( 'HTML tag ...', 'tooltipy-lang' ); ?>">
                <input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_cover_tags]" value="<?php echo esc_attr( $val ); ?>">
            </div>
            <input class="easy_tags-add button tagadd" type="button" value="<?php esc_attr_e( 'Add' ); ?>" id="cover_tag_add">
        </div>
        <p style="color:green;"><?php esc_html_e( 'Choose HTML TAGS (like h1, h2, strong, p, ...) to cover with tooltips', 'tooltipy-lang' ); ?></p>
        <?php
    }

    public function field_exclude_areas(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        $val     = $options['kttg_exclude_areas'] ?? '';
        ?>
        <div class="easy_tags" data-easy-tags-delimiter=" ">
            <div class="easy_tags-content" onclick="jQuery('#bluet_exclude_areas_id').focus()">
                <div class="easy_tags-list tagchecklist" id="exclude_areas_list"></div>
                <input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_exclude_areas_id" placeholder="<?php esc_attr_e( 'class ...', 'tooltipy-lang' ); ?>">
                <input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_exclude_areas]" value="<?php echo esc_attr( $val ); ?>">
            </div>
            <input class="easy_tags-add button tagadd" type="button" value="<?php esc_attr_e( 'Add' ); ?>" id="exclude_class_add">
        </div>
        <p style="color:red;"><?php esc_html_e( 'Choose CSS classes to exclude', 'tooltipy-lang' ); ?></p>
        <?php
    }

    public function field_exclude_anchors(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        ?>
        <input id="bluet_exclude_anchor_tags" type="checkbox" name="bluet_kw_advanced[kttg_exclude_anchor_tags]" <?php checked( $options['kttg_exclude_anchor_tags'] ?? '', 'on' ); ?>>
        <label for="bluet_exclude_anchor_tags"><?php esc_html_e( 'Links', 'tooltipy-lang' ); ?></label>
        <?php
    }

    public function field_exclude_headings(): void {
        $options  = get_option( 'bluet_kw_advanced', [] );
        $headings = $options['kttg_exclude_heading_tags'] ?? [];
        echo "<div id='kttg_exclude_headings_zone'>";
        for ( $i = 1; $i < 7; $i++ ) {
            $checked = checked( $headings[ 'h' . $i ] ?? '', 'on', false );
            echo "<label for='bluet_exclude_heading_H{$i}'><h{$i}><input id='bluet_exclude_heading_H{$i}' type='checkbox' name='bluet_kw_advanced[kttg_exclude_heading_tags][h{$i}]' {$checked}> H{$i}</h{$i}></label>";
        }
        echo "</div>";
    }

    public function field_exclude_common_tags(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        $exclude = $options['kttg_exclude_common_tags'] ?? [];
        echo "<div id='kttg_exclude_tags_zone'>";
        foreach ( self::COMMON_TAGS as $tag ) {
            $checked = checked( $exclude[ $tag ] ?? '', 'on', false );
            echo "<input id='bluet_exclude_tag_{$tag}' type='checkbox' name='bluet_kw_advanced[kttg_exclude_common_tags][{$tag}]' {$checked}> ";
            echo "<label for='bluet_exclude_tag_{$tag}'>&lt;{$tag}/&gt;</label><br>";
        }
        echo "</div>";
    }

    public function field_adv_style(): void {
        $options   = get_option( 'bluet_kw_advanced', [] );
        $url       = $options['bt_kw_adv_style']['custom_style_sheet']         ?? '';
        $apply     = ! empty( $options['bt_kw_adv_style']['apply_custom_style_sheet'] );
        ?>
        <input id="bluet_apply_custom_style_sheet" type="checkbox" name="bluet_kw_advanced[bt_kw_adv_style][apply_custom_style_sheet]" <?php checked( $apply ); ?>>
        <label for="bluet_apply_custom_style_sheet"><?php esc_html_e( 'Apply custom style sheet', 'tooltipy-lang' ); ?></label>
        <br><input style="min-width:250px;" type="text" name="bluet_kw_advanced[bt_kw_adv_style][custom_style_sheet]" value="<?php echo esc_attr( $url ); ?>" placeholder="CSS URL Here">
        <?php
    }

    public function field_fetch_all(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        ?>
        <input id="bluet_fetch_all_keywords" type="checkbox" name="bluet_kw_advanced[kttg_fetch_all_keywords]" <?php checked( $options['kttg_fetch_all_keywords'] ?? '', 'on' ); ?>>
        <label for="bluet_fetch_all_keywords">(<?php esc_html_e( 'use only if needed to load all keywords per page', 'tooltipy-lang' ); ?>)</label>
        <?php
    }

    public function field_custom_events(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        $val     = $options['kttg_custom_events'] ?? '';
        ?>
        <input type="text" style="min-width:300px;" placeholder="<?php esc_attr_e( "Events names saparated with ','", 'tooltipy-lang' ); ?>" name="bluet_kw_advanced[kttg_custom_events]" value="<?php echo esc_attr( $val ); ?>">
        <?php
    }

    public function field_prevent_filters(): void {
        $options = get_option( 'bluet_kw_advanced', [] );
        ?>
        <input type="checkbox" name="bluet_kw_advanced[prevent_plugins_filters]" <?php checked( $options['prevent_plugins_filters'] ?? '', 'on' ); ?> />
        <?php esc_html_e( 'Prevent any 3rd party plugin to filter or change the keywords content', 'tooltipy-lang' ); ?>
        <?php
    }

    public function field_post_types_filter(): void {
        $options    = get_option( 'bluet_kw_advanced', [] );
        $post_types = $options['bt_kw_in_concern_custom_posts']['post_types'] ?? [];
        $pt_name    = $this->plugin->get_post_type_name();

        echo '<p><b>' . esc_html__( 'Post types to apply tooltips to :', 'tooltipy-lang' ) . '</b></p>';
        echo '<select multiple name="bluet_kw_advanced[bt_kw_in_concern_custom_posts][post_types][]" size="8">';
        foreach ( get_post_types() as $pt ) {
            if ( $pt === $pt_name ) { continue; }
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $pt ),
                in_array( $pt, (array) $post_types, true ) ? ' selected' : '',
                esc_html( $pt )
            );
        }
        echo '</select>';

        // Supported plugins
        $supported = $options['bt_kw_supported_plugins'] ?? [];
        echo '<p><b>' . esc_html__( 'Supported plugins :', 'tooltipy-lang' ) . '</b></p>';
        echo '<label><input type="checkbox" name="bluet_kw_advanced[bt_kw_supported_plugins][bbpress]" ' . checked( $supported['bbpress'] ?? '', 'on', false ) . '> bbPress</label><br>';
        echo '<label><input type="checkbox" name="bluet_kw_advanced[bt_kw_supported_plugins][wooc]" '   . checked( $supported['wooc']   ?? '', 'on', false ) . '> WooCommerce</label>';
    }

    /* ---- Helpers (also used by MetaBoxes & ContentFilter) ---- */

    public function get_post_types_to_filter(): array {
        $options = get_option( 'bluet_kw_advanced', [] );
        $types   = [];

        if ( ! empty( $options['bt_kw_in_concern_custom_posts']['post_types'] ) ) {
            $types = (array) $options['bt_kw_in_concern_custom_posts']['post_types'];
        }

        if ( ! empty( $options['bt_kw_supported_plugins']['bbpress'] ) ) { $types[] = 'topic'; }
        if ( ! empty( $options['bt_kw_supported_plugins']['wooc'] ) )    { $types[] = 'product'; }

        return array_values( array_filter( array_unique( $types ) ) );
    }

    public function get_custom_fields_to_filter(): array {
        $options = get_option( 'bluet_kw_advanced', [] );
        $fields  = $options['bt_kw_in_concern_custom_posts']['custom_fields_hooks'] ?? [];
        $is_acf  = $options['bt_kw_in_concern_custom_posts']['is_acf_field']         ?? [];

        foreach ( $fields as $k => $val ) {
            if ( is_array( $val ) ) {
                foreach ( $val as $ind => $v ) {
                    if ( $v === '' ) {
                        unset( $fields[ $k ][ $ind ] );
                    } elseif ( ! empty( $is_acf[ $k ][ $ind ] ) ) {
                        $fields[ $k ][ $ind ] = 'acf/load_value/name=' . $v;
                    }
                }
            }
            if ( empty( $fields[ $k ] ) ) {
                unset( $fields[ $k ] );
            }
        }

        return $fields;
    }
}
