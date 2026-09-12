<?php
namespace Tooltipy\Addon;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Contract that every Tooltipy addon must implement.
 *
 * Usage (from an addon plugin):
 *
 *   add_action('tooltipy_register_addons', function(\Tooltipy\Addon\AddonManager $mgr) {
 *       $mgr->register(new MyAwesomeAddon());
 *   });
 *
 * Available extension hooks for addons:
 *   - tooltipy_register_addons          (AddonManager)   – Register the addon
 *   - tooltipy_settings_tabs            (void)           – Add tab <a> links in the settings page
 *   - tooltipy_settings_tab_panels      (void)           – Add tab panel <div> content
 *   - tooltipy_keyword_data             (KeywordData)    – Modify a keyword's data object
 *   - tltpy_posttypes_to_match          (array)          – Extend post types matched by content filter
 *   - tltpy_custom_fields_hooks         (array)          – Extend filter hooks for custom fields
 *   - kttg_another_tooltip_in_block     (string)         – Append extra HTML inside tooltip block container
 *   - tltpy_post_type_args              (array)          – Modify CPT registration args
 *   - bluet_kw_capability               (string)         – Change the capability required
 *   - tooltipy_stylesheet_url           (string)         – Replace or filter the front-end CSS URL
 */
interface AddonInterface {

    /**
     * A unique machine-readable identifier (e.g. 'my-addon').
     */
    public function get_id(): string;

    /**
     * Human-readable name shown in the admin UI.
     */
    public function get_name(): string;

    /**
     * Called by AddonManager once the addon is registered.
     * Hook into WordPress actions/filters here.
     */
    public function init(): void;

    /**
     * Called on plugin activation (optional setup).
     */
    public function activate(): void;

    /**
     * Called on plugin deactivation (optional cleanup).
     */
    public function deactivate(): void;
}
