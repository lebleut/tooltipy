<?php
namespace Tooltipy\Editor;

defined( 'ABSPATH' ) || exit;

/**
 * TinyMCE button for inserting [tooltip] shortcodes.
 */
class TinyMceButton {

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register' ] );
	}

	public function register(): void {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', [ $this, 'add_plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'add_button' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_css' ] );
	}

	/**
	 * @param array<string, string> $plugins
	 * @return array<string, string>
	 */
	public function add_plugin( array $plugins ): array {
		$plugins['bluetKFI'] = TOOLTIPY_PLUGIN_URL . 'assets/bluetkfi-plugin.js';
		return $plugins;
	}

	/**
	 * @param string[] $buttons
	 * @return string[]
	 */
	public function add_button( array $buttons ): array {
		$buttons[] = 'bluetKFI';
		return $buttons;
	}

	public function enqueue_css(): void {
		wp_enqueue_style(
			'tooltipy-mce',
			TOOLTIPY_PLUGIN_URL . 'assets/kttg-mce.css',
			[],
			TOOLTIPY_VERSION
		);
	}
}
