<?php
namespace Tooltipy\Frontend;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Enqueues all front-end and admin scripts/styles.
 * Replaces bluet_kw_load_scripts_front() and ttpy_admin_load_scripts().
 */
class ScriptManager {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
        add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_advanced_scripts' ] );
    }

    public function enqueue_frontend(): void {
        $options   = get_option( 'bluet_kw_settings', [] );
        $anim_type = $options['bt_kw_animation_type'] ?? '';

        if ( ! empty( $anim_type ) && $anim_type !== 'none' ) {
            wp_enqueue_style(
                'kttg-tooltips-animations-styles',
                apply_filters( 'tooltipy_animate_css_url', TOOLTIPY_PLUGIN_URL . 'assets/animate.css' ),
                [],
                TOOLTIPY_VERSION
            );
        }

        wp_enqueue_style(
            'tooltipy-default-style',
            apply_filters( 'tooltipy_stylesheet_url', TOOLTIPY_PLUGIN_URL . 'assets/style.css' ),
            [],
            TOOLTIPY_VERSION
        );

        wp_enqueue_style(
            'tooltipy-tippy',
            TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/tippy.css',
            [],
            TOOLTIPY_VERSION
        );

        wp_enqueue_style(
            'tooltipy-tippy-theme',
            TOOLTIPY_PLUGIN_URL . 'assets/css/tooltipy-tippy-theme.css',
            [ 'tooltipy-tippy', 'tooltipy-default-style' ],
            TOOLTIPY_VERSION
        );

        wp_enqueue_script(
            'tooltipy-find-and-replace',
            TOOLTIPY_PLUGIN_URL . 'library/findandreplacedomtext.js',
            [],
            TOOLTIPY_VERSION,
            false
        );

        // Tippy UMD factory expects window.Popper (@popperjs/core).
        wp_enqueue_script(
            'tooltipy-popper',
            TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/popper.min.js',
            [],
            TOOLTIPY_VERSION,
            true
        );

        wp_enqueue_script(
            'tooltipy-tippy',
            TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/tippy-bundle.umd.min.js',
            [ 'tooltipy-popper' ],
            TOOLTIPY_VERSION,
            true
        );

        wp_enqueue_script(
            'tooltipy-tippy-bridge',
            TOOLTIPY_PLUGIN_URL . 'assets/js/tooltipy-tippy.js',
            [ 'jquery', 'tooltipy-tippy' ],
            TOOLTIPY_VERSION,
            true
        );

        $settings  = get_option( 'bluet_kw_settings', [] );
        $placement = $settings['bt_kw_position'] ?? 'bottom';
        wp_localize_script(
            'tooltipy-tippy-bridge',
            'tooltipyTippy',
            [
                'placement' => $placement,
            ]
        );

        wp_enqueue_script( 'wp-mediaelement' );
        wp_enqueue_style( 'wp-mediaelement' );

        $style_opt = get_option( 'bluet_kw_style', [] );
        if ( ! empty( $style_opt['bt_kw_alt_img'] ) && $style_opt['bt_kw_alt_img'] === 'on' ) {
            wp_enqueue_script(
                'kttg-functions-alt-img-script',
                TOOLTIPY_PLUGIN_URL . 'assets/img-alt-tooltip.js',
                [ 'jquery', 'tooltipy-tippy-bridge' ],
                TOOLTIPY_VERSION,
                true
            );
        }
    }

    public function enqueue_admin(): void {
        wp_enqueue_style(
            'tooltipy-admin-style',
            TOOLTIPY_PLUGIN_URL . 'assets/admin-style.css',
            [],
            TOOLTIPY_VERSION
        );

        wp_enqueue_script(
            'kttg-settings-functions-script',
            TOOLTIPY_PLUGIN_URL . 'assets/settings-functions.js',
            [ 'jquery' ],
            TOOLTIPY_VERSION,
            true
        );

        if ( $this->is_tooltipy_settings_page() ) {
            $options   = get_option( 'bluet_kw_settings', [] );
            $anim_type = $options['bt_kw_animation_type'] ?? '';

            if ( ! empty( $anim_type ) && $anim_type !== 'none' ) {
                wp_enqueue_style(
                    'kttg-tooltips-animations-styles',
                    TOOLTIPY_PLUGIN_URL . 'assets/animate.css',
                    [],
                    TOOLTIPY_VERSION
                );
            }

            wp_enqueue_script(
                'tooltipy-find-and-replace',
                TOOLTIPY_PLUGIN_URL . 'library/findandreplacedomtext.js',
                [],
                TOOLTIPY_VERSION,
                false
            );

            wp_enqueue_style(
                'tooltipy-tippy',
                TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/tippy.css',
                [],
                TOOLTIPY_VERSION
            );

            wp_enqueue_style(
                'tooltipy-tippy-theme',
                TOOLTIPY_PLUGIN_URL . 'assets/css/tooltipy-tippy-theme.css',
                [ 'tooltipy-tippy' ],
                TOOLTIPY_VERSION
            );

            wp_enqueue_script(
                'tooltipy-popper',
                TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/popper.min.js',
                [],
                TOOLTIPY_VERSION,
                true
            );

            wp_enqueue_script(
                'tooltipy-tippy',
                TOOLTIPY_PLUGIN_URL . 'assets/vendor/tippy/tippy-bundle.umd.min.js',
                [ 'tooltipy-popper' ],
                TOOLTIPY_VERSION,
                true
            );

            wp_enqueue_script(
                'tooltipy-tippy-bridge',
                TOOLTIPY_PLUGIN_URL . 'assets/js/tooltipy-tippy.js',
                [ 'jquery', 'tooltipy-tippy' ],
                TOOLTIPY_VERSION,
                true
            );

            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script(
                'kttg-colorpicker-custom-script',
                TOOLTIPY_PLUGIN_URL . 'assets/colorpicker-custom-script.js',
                [ 'wp-color-picker', 'jquery' ],
                TOOLTIPY_VERSION,
                true
            );
        }
    }

    /**
     * Enqueue advanced (Pro) scripts and custom style sheet.
     */
    public function enqueue_advanced_scripts(): void {
        wp_enqueue_script(
            'kttg-pro-tooltip-scripts',
            TOOLTIPY_PLUGIN_URL . 'assets/kttg-pro-functions.js',
            [ 'jquery' ],
            TOOLTIPY_VERSION,
            true
        );

        $adv = get_option( 'bluet_kw_advanced', [] );
        if (
            ! empty( $adv['bt_kw_adv_style']['apply_custom_style_sheet'] )
            && ! empty( $adv['bt_kw_adv_style']['custom_style_sheet'] )
        ) {
            wp_enqueue_style(
                'kttg-custom-style-sheet',
                esc_url_raw( $adv['bt_kw_adv_style']['custom_style_sheet'] ),
                [],
                TOOLTIPY_VERSION
            );
        }
    }

    private function is_tooltipy_settings_page(): bool {
        $pt = $this->plugin->get_post_type_name();
        return (
            ! empty( $_GET['post_type'] ) && $_GET['post_type'] === $pt
            && ! empty( $_GET['page'] )   && $_GET['page']      === 'my_keywords_settings'
        );
    }
}
