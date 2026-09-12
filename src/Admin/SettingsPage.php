<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Registers the Settings page and all its sections/fields/tabs.
 * Replaces settings-page.php (excluding style & glossary which have their own classes).
 */
class SettingsPage {

    private Plugin $plugin;

    /** Animations available for the tooltip */
    private const ANIMATIONS = [
        'bounce','bounceIn','bounceInLeft','bounceInRight','bounceInDown','bounceInUp',
        'fadeIn','fadeInLeft','fadeInLeftBig','fadeInRight','fadeInRightBig','fadeInUp','fadeInUpBig',
        'flash','flip','flipInX','flipInY','lightSpeedIn','pulse','rollIn',
        'rotateIn','rotateInDownLeft','rotateInDownRight','rotateInUpLeft','rotateInUpRight',
        'slideInDown','slideInLeft','slideInRight','slideInUp','swing','shake','tada',
        'wobble','zoomIn','zoomInDown','zoomInLeft','zoomInRight','zoomInUp',
    ];

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'admin_init',  [ $this, 'register_settings' ] );
        add_action( 'admin_menu',  [ $this, 'add_submenu' ] );
    }

    public function register_settings(): void {
        // --- Sections ---
        add_settings_section( 'concern_section', __( 'Tooltips settings :', 'tooltipy-lang' ), [ $this, 'section_concern_cb' ], 'my_keywords_settings' );

        // --- Fields ---
        add_settings_field( 'kttg_tooltip_post_types',  __( 'Get tooltips from', 'tooltipy-lang' ),             [ $this, 'field_tooltip_post_types' ],  'my_keywords_settings', 'concern_section' );
        add_settings_field( 'bt_kw_match_all_field',    __( 'Match once or all occurrences', 'tooltipy-lang' ),  [ $this, 'field_match_all' ],            'my_keywords_settings', 'concern_section' );
        add_settings_field( 'bt_kw_hide_title',         __( 'Tooltip title', 'tooltipy-lang' ),                  [ $this, 'field_hide_title' ],           'my_keywords_settings', 'concern_section' );
        add_settings_field( 'bt_kw_position',           __( 'Tooltip position', 'tooltipy-lang' ),               [ $this, 'field_position' ],             'my_keywords_settings', 'concern_section' );
        add_settings_field( 'bt_kw_animation_type',     __( 'Animation', 'tooltipy-lang' ),                      [ $this, 'field_animation_type' ],       'my_keywords_settings', 'concern_section' );

        // --- Registration ---
        register_setting( 'settings_group', 'bluet_kw_settings' );
    }

    public function add_submenu(): void {
        $pt_name = $this->plugin->get_post_type_name();
        add_submenu_page(
            'edit.php?post_type=' . $pt_name,
            __( 'KeyWords Settings', 'tooltipy-lang' ),
            __( 'Settings' ),
            'manage_options',
            'my_keywords_settings',
            [ $this, 'render_page' ]
        );
    }

    /* ---- Section callbacks ---- */

    public function section_concern_cb(): void {
        echo '<div id="keywords-settings">' . esc_html__( 'General tooltips settings', 'tooltipy-lang' ) . '.</div>';
    }

    /* ---- Field callbacks ---- */

    public function field_tooltip_post_types(): void {
        $pt_name = $this->plugin->get_post_type_name();
        $options = get_option( 'bluet_kw_settings', [] );
        $selected = $options['kttg_tooltip_post_types'] ?? [];

        echo '<select multiple name="bluet_kw_settings[kttg_tooltip_post_types][]" size="10">';
        foreach ( get_post_types() as $pt ) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $pt ),
                in_array( $pt, (array) $selected, true ) ? ' selected' : '',
                esc_html( $pt )
            );
        }
        echo '</select>';
        echo '<div>' . esc_html__( 'Select post types from which you want to get tooltips (default post type : ', 'tooltipy-lang' ) . esc_html( $pt_name ) . ')</div>';

        if ( empty( $selected ) || ! in_array( $pt_name, (array) $selected, true ) ) {
            echo '<div style="color:red;"><b>' . esc_html( $pt_name ) . esc_html__( ' is not selected as a tooltip.', 'tooltipy-lang' ) . '</b></div>';
        }
    }

    public function field_match_all(): void {
        $options = get_option( 'bluet_kw_settings', [] );
        ?>
        <input type="checkbox" id="bt_kw_match_all_id" name="bluet_kw_settings[bt_kw_match_all]" <?php checked( $options['bt_kw_match_all'] ?? '', 'on' ); ?> />
        <label for="bt_kw_match_all_id"><?php esc_html_e( 'Match all occurrences', 'tooltipy-lang' ); ?></label>
        <?php
    }

    public function field_hide_title(): void {
        $options = get_option( 'bluet_kw_settings', [] );
        ?>
        <input type="checkbox" id="bt_kw_hide_title_id" name="bluet_kw_settings[bt_kw_hide_title]" <?php checked( $options['bt_kw_hide_title'] ?? '', 'on' ); ?> />
        <label for="bt_kw_hide_title_id"><?php esc_html_e( 'Hide the tooltips title', 'tooltipy-lang' ); ?></label>
        <?php
    }

    public function field_position(): void {
        $options  = get_option( 'bluet_kw_settings', [] );
        $position = $options['bt_kw_position'] ?? 'bottom';
        foreach ( [ 'top', 'bottom', 'right', 'left' ] as $pos ) {
            echo '<input type="radio" name="bluet_kw_settings[bt_kw_position]" value="' . esc_attr( $pos ) . '" ' . checked( $position, $pos, false ) . ' />' . esc_html( ucfirst( $pos ) ) . '<br>';
        }
    }

    public function field_animation_type(): void {
        $options    = get_option( 'bluet_kw_settings', [] );
        $anim_type  = $options['bt_kw_animation_type']  ?? 'none';
        $anim_speed = $options['bt_kw_animation_speed'] ?? 'kttg_normal';

        echo '<select id="select_anim" name="bluet_kw_settings[bt_kw_animation_type]">';
        echo '<optgroup label="Select an animation">';
        echo '<option value="none"' . selected( $anim_type, 'none', false ) . ' style="color:red;">' . esc_html__( 'None', 'tooltipy-lang' ) . '</option>';
        foreach ( self::ANIMATIONS as $anim ) {
            echo '<option value="' . esc_attr( $anim ) . '"' . selected( $anim_type, $anim, false ) . '>' . esc_html( $anim ) . '</option>';
        }
        echo '</optgroup></select>';

        foreach ( [ 'kttg_fast' => 'Fast', 'kttg_normal' => 'Normal', 'kttg_slow' => 'Slow' ] as $val => $label ) {
            echo '<label for="select_speed_' . esc_attr( $val ) . '">' . esc_html__( $label, 'tooltipy-lang' ) . '</label>';
            echo '<input type="radio" id="select_speed_' . esc_attr( $val ) . '" name="bluet_kw_settings[bt_kw_animation_speed]" value="' . esc_attr( $val ) . '" ' . checked( $anim_speed, $val, false ) . ' />';
        }

        echo '<div id="demo_div" style="width:200px;text-align:center;font-size:30px;">' . esc_html__( 'click to see a DEMO', 'tooltipy-lang' ) . '</div>';
        ?>
        <script>
        jQuery("#select_anim, #select_speed_kttg_fast, #select_speed_kttg_normal, #select_speed_kttg_slow").on("change", function(){
            jQuery("#demo_div").removeClass().addClass("animated " + jQuery("input[name='bluet_kw_settings[bt_kw_animation_speed]']:checked").val() + " " + jQuery("#select_anim").val());
        });
        jQuery("#demo_div").click(function(){
            jQuery(this).removeClass().addClass("animated " + jQuery("input[name='bluet_kw_settings[bt_kw_animation_speed]']:checked").val() + " " + jQuery("#select_anim").val());
        });
        </script>
        <?php
    }

    /* ---- Page render ---- */

    public function render_page(): void {
        $plugin_data = get_plugin_data( TOOLTIPY_PLUGIN_FILE );
        ?>
        <div id="bluet-general" class="wrap">
            <h2><?php esc_html_e( 'KeyWords Settings', 'tooltipy-lang' ); ?></h2>
            <span><b><?php echo esc_html( $plugin_data['Name'] ); ?></b> (v<?php echo esc_html( $plugin_data['Version'] ); ?>)</span>

            <?php settings_errors(); ?>

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab" id="bluet_style_tab"    data-tab="bluet-section-style"    href="#style_tab"><?php esc_html_e( 'Style', 'tooltipy-lang' ); ?></a>
                <a class="nav-tab" id="bluet_settings_tab" data-tab="bluet-section-settings" href="#options_tab"><?php esc_html_e( 'Options', 'tooltipy-lang' ); ?></a>
                <a class="nav-tab" id="bluet_glossary_tab" data-tab="bluet-section-glossary" href="#glossary_tab"><?php esc_html_e( 'Glossary', 'tooltipy-lang' ); ?></a>
                <a class="nav-tab" id="bluet_advanced_tab" data-tab="bluet-section-advanced" href="#advanced_tab"><?php esc_html_e( 'Advanced', 'tooltipy-lang' ); ?></a>
                <a class="nav-tab" id="bluet_excluded_tab" data-tab="bluet-section-excluded" href="#excluded_tab"><?php esc_html_e( 'Excluded posts', 'tooltipy-lang' ); ?></a>
                <?php do_action( 'tooltipy_settings_tabs' ); ?>
                <a class="nav-tab" target="_blank" style="background-color:antiquewhite;" href="https://wordpress.org/support/plugin/bluet-keywords-tooltip-generator"><?php esc_html_e( 'Help ?', 'tooltipy-lang' ); ?></a>
                <a class="nav-tab rate-tooltipy" target="_blank" style="background-color:aliceblue;" href="https://wordpress.org/support/view/plugin-reviews/bluet-keywords-tooltip-generator"><?php esc_html_e( 'Rate', 'tooltipy-lang' ); ?></a>
                <style>.rate-tooltipy:after{content:" \f155\f155\f155\f155\f155";font-family:"dashicons";color:#e6b800;}</style>
            </h2>

            <form method="post" action="options.php">
                <?php settings_fields( 'settings_group' ); ?>
                <div id="bluet-sections-div">

                    <div class="bluet-section" id="bluet-section-style" name="style_tab">
                        <?php $this->load_template( 'admin/style' ); ?>
                    </div>

                    <div class="bluet-section" id="bluet-section-settings" name="options_tab">
                        <?php do_settings_sections( 'my_keywords_settings' ); ?>
                    </div>

                    <div class="bluet-section" id="bluet-section-glossary" name="glossary_tab">
                        <?php do_settings_sections( 'my_keywords_glossary_settings' ); ?>
                    </div>

                    <div class="bluet-section" id="bluet-section-advanced" name="advanced_tab">
                        <?php do_settings_sections( 'my_keywords_advanced_page' ); ?>
                    </div>

                    <div class="bluet-section" id="bluet-section-excluded" name="excluded_tab">
                        <?php $this->load_template( 'admin/exclude' ); ?>
                    </div>

                    <?php do_action( 'tooltipy_settings_tab_panels' ); ?>

                </div>

                <?php submit_button( __( 'Save Settings', 'tooltipy-lang' ), 'primary' ); ?>
            </form>
        </div>
        <?php
    }

    private function load_template( string $name ): void {
        $file = TOOLTIPY_PLUGIN_DIR . 'templates/' . $name . '.php';
        if ( file_exists( $file ) ) {
            include $file;
        }
    }

    /** Returns excluded posts (for template). */
    public static function get_excluded_posts(): array {
        $types = [ 'post', 'page' ];

        if ( function_exists( 'bluet_get_post_types_to_filter' ) ) {
            $types = array_unique( array_merge( $types, (array) bluet_get_post_types_to_filter() ) );
        }

        $query = new \WP_Query( [
            'post_type'      => $types,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [ [
                'key'     => 'bluet_exclude_post_from_matching',
                'value'   => 'on',
                'compare' => '=',
            ] ],
        ] );

        $result = [];
        foreach ( $query->posts as $pid ) {
            $result[] = [ 'id' => $pid, 'title' => get_the_title( $pid ), 'slug' => get_post( $pid )->post_name ];
        }
        wp_reset_postdata();
        return $result;
    }
}
