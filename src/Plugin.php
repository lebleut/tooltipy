<?php
namespace Tooltipy;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\PostType\KeywordPostType;
use Tooltipy\Frontend\ScriptManager;
use Tooltipy\Frontend\FrontendMatcher;
use Tooltipy\ContentFilter\ContentFilter;
use Tooltipy\Ajax\AjaxHandler;
use Tooltipy\Glossary\GlossaryShortcode;
use Tooltipy\Widget\KeywordWidget;
use Tooltipy\Admin\MetaBoxes;
use Tooltipy\Admin\SettingsPage;
use Tooltipy\Admin\StyleSettings;
use Tooltipy\Admin\GlossarySettings;
use Tooltipy\Admin\AdvancedSettings;
use Tooltipy\Addon\AddonManager;
use Tooltipy\Shortcode\ManualTooltip;
use Tooltipy\Editor\TinyMceButton;

/**
 * Central orchestrator.  Replaces all former globals.
 */
final class Plugin {

    /** @var Plugin|null */
    private static ?Plugin $instance = null;

    /* -----------------------------------------------------------
     * Configuration resolved once on init
     * --------------------------------------------------------- */

    private string $post_type_name     = 'my_keywords';
    private string $cat_name           = 'keywords_family';
    private array  $tooltip_post_types = [];
    private string $capability         = 'manage_options';

    /* -----------------------------------------------------------
     * Sub-components
     * --------------------------------------------------------- */

    private AddonManager $addon_manager;

    private function __construct() {}

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* -----------------------------------------------------------
     * Bootstrap
     * --------------------------------------------------------- */

    public function run(): void {
        add_action( 'init', [ $this, 'on_init' ], 0 );
        add_action( 'init', [ $this, 'late_init' ], 5 );
    }

    public function on_init(): void {
        $this->resolve_post_type_name();
        $this->resolve_tooltip_post_types();
        $this->capability = apply_filters( 'bluet_kw_capability', 'manage_options' );

        $this->addon_manager = new AddonManager();
        $this->addon_manager->init();

        load_plugin_textdomain(
            'tooltipy-lang',
            false,
            dirname( plugin_basename( TOOLTIPY_PLUGIN_FILE ) ) . '/languages/'
        );
    }

    public function late_init(): void {
        ( new KeywordPostType( $this ) )->register();
        ( new ScriptManager( $this ) )->init();
        ( new FrontendMatcher( $this ) )->init();
        ( new ContentFilter( $this ) )->init();
        ( new AjaxHandler( $this ) )->init();
        ( new GlossaryShortcode( $this ) )->init();
        KeywordWidget::register();
        ( new MetaBoxes( $this ) )->init();
        ( new SettingsPage( $this ) )->init();
        ( new StyleSettings( $this ) )->init();
        ( new GlossarySettings( $this ) )->init();
        ( new AdvancedSettings( $this ) )->init();
        ( new ManualTooltip( $this ) )->init();
        ( new TinyMceButton() )->init();
        $this->run_data_migration();

        add_action( 'save_post', [ $this, 'delete_keywords_transient' ] );
    }

    /* -----------------------------------------------------------
     * Configuration resolution
     * --------------------------------------------------------- */

    private function resolve_post_type_name(): void {
        $default  = 'my_keywords';
        $from_opt = get_option( 'tooltipy_post_type_name' );

        if ( empty( $from_opt ) ) {
            update_option( 'tooltipy_post_type_name', $default );
            $from_opt = $default;
        }

        $this->post_type_name = $from_opt;
        $this->cat_name       = ( $from_opt === $default )
            ? 'keywords_family'
            : $from_opt . '_cat';
    }

    private function resolve_tooltip_post_types(): void {
        $options = get_option( 'bluet_kw_settings' );
        $types   = $options['kttg_tooltip_post_types'] ?? null;

        if ( empty( $types ) || ! is_array( $types ) ) {
            $types = [ $this->post_type_name ];
        }

        $this->tooltip_post_types = $types;
    }

    /* -----------------------------------------------------------
     * Accessors (used by sub-components instead of globals)
     * --------------------------------------------------------- */

    public function get_post_type_name(): string   { return $this->post_type_name; }
    public function get_cat_name(): string          { return $this->cat_name; }
    public function get_tooltip_post_types(): array { return $this->tooltip_post_types; }
    public function get_capability(): string        { return $this->capability; }
    public function get_addon_manager(): AddonManager { return $this->addon_manager; }

    /* -----------------------------------------------------------
     * Transient management
     * --------------------------------------------------------- */

    public function delete_keywords_transient(): void {
        delete_transient( 'tooltipy_keywords_titles_ids' );
    }

    /* -----------------------------------------------------------
     * Activation / Deactivation hooks
     * --------------------------------------------------------- */

    public static function activate(): void {
        $style_defaults = [
            'bt_kw_tt_color'       => 'inherit',
            'bt_kw_tt_bg_color'    => '#0D45AA',
            'bt_kw_desc_color'     => '#ffffff',
            'bt_kw_desc_bg_color'  => '#5eaa0d',
            'bt_kw_desc_font_size' => '14',
            'bt_kw_on_background'  => 'on',
        ];

        if ( ! get_option( 'bluet_kw_style' ) ) {
            add_option( 'bluet_kw_style', $style_defaults );
        }

        $settings_defaults = [
            'bt_kw_for_posts' => 'on',
            'bt_kw_match_all' => 'on',
            'bt_kw_position'  => 'bottom',
        ];

        if ( ! get_option( 'bluet_kw_settings' ) ) {
            add_option( 'bluet_kw_settings', $settings_defaults );
        }

        $advanced_defaults = [
            'bt_kw_supported_plugins' => [ 'bbpress' => 'on', 'wooc' => 'on' ],
            'bt_kw_in_concern_custom_posts' => [
                'custom_fields_hooks' => [
                    'pt0' => [ 'the_content' ],
                    'pt1' => [ 'the_content' ],
                ],
                'post_types' => [ 'post', 'page' ],
            ],
        ];

        if ( ! get_option( 'bluet_kw_advanced' ) ) {
            add_option( 'bluet_kw_advanced', $advanced_defaults );
        }

        update_option( 'tooltipy_activated_just_now', true );
    }

    public static function deactivate(): void {}

    /* -----------------------------------------------------------
     * Data migration from 5.x (excluded posts: global option -> postmeta)
     * --------------------------------------------------------- */

    private function run_data_migration(): void {
        add_action( 'admin_init', function (): void {
            if ( get_option( 'tooltipy_excluded_posts_migrated' ) ) {
                return;
            }

            $old_data = get_option( 'tooltipy_excluded_posts_from_matching' );

            if ( empty( $old_data ) || ! is_array( $old_data ) ) {
                add_option( 'tooltipy_excluded_posts_migrated', true );
                return;
            }

            $migrated = 0;
            $failed   = 0;

            foreach ( $old_data as $item ) {
                $pid = (int) ( $item['id'] ?? 0 );
                if ( ! $pid || ! get_post( $pid ) ) {
                    $failed++;
                    continue;
                }
                if ( get_post_meta( $pid, 'bluet_exclude_post_from_matching', true ) !== 'on' ) {
                    update_post_meta( $pid, 'bluet_exclude_post_from_matching', 'on' );
                    $migrated++;
                }
            }

            if ( $migrated > 0 ) {
                delete_option( 'tooltipy_excluded_posts_from_matching' );
                add_option( 'tooltipy_migration_notice', [
                    'migrated'  => $migrated,
                    'failed'    => $failed,
                    'timestamp' => current_time( 'mysql' ),
                ] );
            }

            add_option( 'tooltipy_excluded_posts_migrated', true );
        } );

        add_action( 'admin_notices', function (): void {
            $notice = get_option( 'tooltipy_migration_notice' );
            if ( ! $notice ) {
                return;
            }
            echo '<div class="notice notice-success is-dismissible"><p><strong>Tooltipy:</strong> Migrated ' . (int) $notice['migrated'] . ' excluded posts to individual post settings.</p></div>';
            delete_option( 'tooltipy_migration_notice' );
        } );
    }
}
