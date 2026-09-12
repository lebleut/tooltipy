<?php
/**
 * Plugin Name: Tooltipy
 * Description: Automatically create tooltip boxes for your technical keywords to explain them for your visitors.
 * Author: Jamel Zarga
 * Version: 7.0.0-alpha
 * Author URI: https://www.wpjam.co
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: tooltipy-lang
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'TOOLTIPY_PLUGIN_FILE', __FILE__ );
define( 'TOOLTIPY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOOLTIPY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TOOLTIPY_VERSION', '7.0.0-alpha' );

/** Canonical glossary shortcode tag (legacy constant kept for BC). */
if ( ! defined( 'TLTPY_GLOSSARY_SHORTCODE' ) ) {
	define( 'TLTPY_GLOSSARY_SHORTCODE', 'tooltip_glossary' );
}

spl_autoload_register(
	static function ( string $class ): void {
		$prefix   = 'Tooltipy\\';
		$base_dir = TOOLTIPY_PLUGIN_DIR . 'src/';

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require_once TOOLTIPY_PLUGIN_DIR . 'src/Plugin.php';

register_activation_hook( __FILE__, [ Tooltipy\Plugin::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Tooltipy\Plugin::class, 'deactivate' ] );

Tooltipy\Plugin::get_instance()->run();
