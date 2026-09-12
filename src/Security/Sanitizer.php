<?php
namespace Tooltipy\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Shared sanitization / validation helpers.
 */
final class Sanitizer {

	/**
	 * Extract and validate a YouTube video ID.
	 */
	public static function youtube_id( string $raw ): string {
		$raw = trim( $raw );
		if ( $raw === '' ) {
			return '';
		}

		if ( preg_match( '/^[A-Za-z0-9_-]{6,32}$/', $raw ) ) {
			return $raw;
		}

		if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,32})/', $raw, $m ) ) {
			return $m[1];
		}

		return '';
	}

	/**
	 * Sanitize a CSS color (hex or inherit).
	 */
	public static function css_color( string $color, string $fallback = '' ): string {
		$color = trim( $color );
		if ( strtolower( $color ) === 'inherit' ) {
			return 'inherit';
		}
		$hex = sanitize_hex_color( $color );
		return $hex ?: $fallback;
	}

	/**
	 * Sanitize space-separated CSS class list.
	 */
	public static function css_classes( string $classes ): string {
		$parts = preg_split( '/\s+/', $classes ) ?: [];
		$clean = [];
		foreach ( $parts as $part ) {
			$class = sanitize_html_class( $part );
			if ( $class !== '' ) {
				$clean[] = $class;
			}
		}
		return implode( ' ', array_unique( $clean ) );
	}

	/**
	 * Bounded positive integer or empty string.
	 */
	public static function positive_int_or_empty( mixed $value, int $min, int $max ): string {
		if ( $value === '' || $value === null ) {
			return '';
		}
		$n = (int) $value;
		if ( $n < $min || $n > $max ) {
			return '';
		}
		return (string) $n;
	}

	/**
	 * Safe external stylesheet URL (http/https only).
	 */
	public static function stylesheet_url( string $url ): string {
		$url = esc_url_raw( trim( $url ), [ 'http', 'https' ] );
		return is_string( $url ) ? $url : '';
	}

	/**
	 * Synonyms pipe list without HTML / script tags.
	 */
	public static function synonyms( string $syns ): string {
		$syns = wp_strip_all_tags( $syns );
		$syns = sanitize_text_field( $syns );
		$syns = (string) preg_replace( '/\|{2,}/', '|', $syns );
		$syns = (string) preg_replace( '/^\||\|$/', '', $syns );
		return $syns;
	}

	/**
	 * Keyword/tooltip HTML body for popup rendering.
	 */
	public static function tooltip_html( string $html ): string {
		return wp_kses_post( $html );
	}

	/**
	 * Checkbox stored as "on" or empty.
	 */
	public static function on_off( mixed $value ): string {
		return ( ! empty( $value ) && $value !== '0' && $value !== 'off' ) ? 'on' : '';
	}
}
