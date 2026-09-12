<?php
namespace Tooltipy\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Typed accessors for legacy option keys (bluet_*).
 * Keeps storage 100% backward-compatible with Tooltipy 5.x.
 */
final class OptionsRepository {

	public function get_settings(): array {
		$opts = get_option( 'bluet_kw_settings', [] );
		return is_array( $opts ) ? $opts : [];
	}

	public function get_style(): array {
		$opts = get_option( 'bluet_kw_style', [] );
		return is_array( $opts ) ? $opts : [];
	}

	public function get_glossary(): array {
		$opts = get_option( 'bluet_glossary_options', [] );
		return is_array( $opts ) ? $opts : [];
	}

	public function get_advanced(): array {
		$opts = get_option( 'bluet_kw_advanced', [] );
		return is_array( $opts ) ? $opts : [];
	}

	public function get( string $group, string $key, mixed $default = null ): mixed {
		$opts = match ( $group ) {
			'settings' => $this->get_settings(),
			'style'    => $this->get_style(),
			'glossary' => $this->get_glossary(),
			'advanced' => $this->get_advanced(),
			default    => [],
		};

		return $opts[ $key ] ?? $default;
	}

	/**
	 * Sanitize a color string (hex or "inherit").
	 */
	public static function sanitize_color( string $color ): string {
		$color = trim( $color );
		if ( strtolower( $color ) === 'inherit' ) {
			return 'inherit';
		}
		$sanitized = sanitize_hex_color( $color );
		return $sanitized ?: '';
	}
}
