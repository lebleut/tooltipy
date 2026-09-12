<?php
namespace Tooltipy\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize callbacks for register_setting() option groups.
 */
final class OptionSanitizer {

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function settings( $input ): array {
		$input = is_array( $input ) ? $input : [];
		$out   = [];

		if ( ! empty( $input['kttg_tooltip_post_types'] ) && is_array( $input['kttg_tooltip_post_types'] ) ) {
			$out['kttg_tooltip_post_types'] = array_values(
				array_filter(
					array_map( 'sanitize_key', $input['kttg_tooltip_post_types'] )
				)
			);
		} else {
			$out['kttg_tooltip_post_types'] = [];
		}

		$out['bt_kw_match_all']  = Sanitizer::on_off( $input['bt_kw_match_all'] ?? '' );
		$out['bt_kw_hide_title'] = Sanitizer::on_off( $input['bt_kw_hide_title'] ?? '' );

		$pos = sanitize_key( (string) ( $input['bt_kw_position'] ?? 'bottom' ) );
		$out['bt_kw_position'] = in_array( $pos, [ 'top', 'bottom', 'left', 'right' ], true ) ? $pos : 'bottom';

		$out['bt_kw_animation_type']  = sanitize_html_class( (string) ( $input['bt_kw_animation_type'] ?? 'none' ) );
		$out['bt_kw_animation_speed'] = sanitize_html_class( (string) ( $input['bt_kw_animation_speed'] ?? '' ) );

		return $out;
	}

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function style( $input ): array {
		$input = is_array( $input ) ? $input : [];
		$out   = [];

		$mode = sanitize_key( (string) ( $input['bt_kw_fetch_mode'] ?? 'highlight' ) );
		$out['bt_kw_fetch_mode'] = in_array( $mode, [ 'highlight', 'icon' ], true ) ? $mode : 'highlight';

		$out['bt_kw_tooltip_width']  = Sanitizer::positive_int_or_empty( $input['bt_kw_tooltip_width'] ?? '', 1, 5000 );
		$out['bt_kw_desc_font_size'] = Sanitizer::positive_int_or_empty( $input['bt_kw_desc_font_size'] ?? '', 1, 50 );
		$out['bt_kw_alt_img']        = Sanitizer::on_off( $input['bt_kw_alt_img'] ?? '' );
		$out['bt_kw_on_background']  = Sanitizer::on_off( $input['bt_kw_on_background'] ?? '' );

		$out['bt_kw_tt_color']      = Sanitizer::css_color( (string) ( $input['bt_kw_tt_color'] ?? '' ), 'inherit' );
		$out['bt_kw_tt_bg_color']   = Sanitizer::css_color( (string) ( $input['bt_kw_tt_bg_color'] ?? '' ), '#0D45AA' );
		$out['bt_kw_desc_color']    = Sanitizer::css_color( (string) ( $input['bt_kw_desc_color'] ?? '' ), '#ffffff' );
		$out['bt_kw_desc_bg_color'] = Sanitizer::css_color( (string) ( $input['bt_kw_desc_bg_color'] ?? '' ), '#5eaa0d' );

		$out['bt_kw_add_css_classes'] = [
			'keyword' => Sanitizer::css_classes( (string) ( $input['bt_kw_add_css_classes']['keyword'] ?? '' ) ),
			'popup'   => Sanitizer::css_classes( (string) ( $input['bt_kw_add_css_classes']['popup'] ?? '' ) ),
		];

		return $out;
	}

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function glossary( $input ): array {
		$input = is_array( $input ) ? $input : [];
		$out   = [];

		$out['kttg_kws_per_page']              = Sanitizer::positive_int_or_empty( $input['kttg_kws_per_page'] ?? '', 1, 900 );
		$out['tltpy_glossary_show_thumb']      = Sanitizer::on_off( $input['tltpy_glossary_show_thumb'] ?? '' );
		$out['bluet_kttg_show_glossary_link']  = Sanitizer::on_off( $input['bluet_kttg_show_glossary_link'] ?? '' );
		$out['link_titles']                    = Sanitizer::on_off( $input['link_titles'] ?? '' );
		$out['kttg_link_glossary_page_link']   = esc_url_raw( (string) ( $input['kttg_link_glossary_page_link'] ?? '' ), [ 'http', 'https' ] );
		$out['kttg_link_glossary_label']       = sanitize_text_field( (string) ( $input['kttg_link_glossary_label'] ?? '' ) );

		$out['kttg_glossary_text'] = [];
		$labels = is_array( $input['kttg_glossary_text'] ?? null ) ? $input['kttg_glossary_text'] : [];
		foreach ( $labels as $key => $val ) {
			$out['kttg_glossary_text'][ sanitize_key( (string) $key ) ] = sanitize_text_field( (string) $val );
		}

		return $out;
	}

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function advanced( $input ): array {
		$input = is_array( $input ) ? $input : [];
		$out   = [];

		$out['kttg_cover_areas']   = Sanitizer::css_classes( (string) ( $input['kttg_cover_areas'] ?? '' ) );
		$out['kttg_exclude_areas'] = Sanitizer::css_classes( (string) ( $input['kttg_exclude_areas'] ?? '' ) );
		$out['kttg_cover_tags']    = self::html_tag_list( (string) ( $input['kttg_cover_tags'] ?? '' ) );

		$out['kttg_exclude_anchor_tags'] = Sanitizer::on_off( $input['kttg_exclude_anchor_tags'] ?? '' );
		$out['kttg_fetch_all_keywords']  = Sanitizer::on_off( $input['kttg_fetch_all_keywords'] ?? '' );
		$out['prevent_plugins_filters'] = Sanitizer::on_off( $input['prevent_plugins_filters'] ?? '' );

		$out['kttg_exclude_heading_tags'] = [];
		$headings = is_array( $input['kttg_exclude_heading_tags'] ?? null ) ? $input['kttg_exclude_heading_tags'] : [];
		for ( $i = 1; $i < 7; $i++ ) {
			$key = 'h' . $i;
			if ( ! empty( $headings[ $key ] ) ) {
				$out['kttg_exclude_heading_tags'][ $key ] = 'on';
			}
		}

		$out['kttg_exclude_common_tags'] = [];
		$allowed_common = [ 'strong', 'b', 'abbr', 'button', 'dfn', 'em', 'i', 'label' ];
		$common         = is_array( $input['kttg_exclude_common_tags'] ?? null ) ? $input['kttg_exclude_common_tags'] : [];
		foreach ( $allowed_common as $tag ) {
			if ( ! empty( $common[ $tag ] ) ) {
				$out['kttg_exclude_common_tags'][ $tag ] = 'on';
			}
		}

		$out['kttg_custom_events'] = self::event_names( (string) ( $input['kttg_custom_events'] ?? '' ) );

		$style = is_array( $input['bt_kw_adv_style'] ?? null ) ? $input['bt_kw_adv_style'] : [];
		$out['bt_kw_adv_style'] = [
			'apply_custom_style_sheet' => Sanitizer::on_off( $style['apply_custom_style_sheet'] ?? '' ),
			'custom_style_sheet'       => Sanitizer::stylesheet_url( (string) ( $style['custom_style_sheet'] ?? '' ) ),
		];

		$concern = is_array( $input['bt_kw_in_concern_custom_posts'] ?? null ) ? $input['bt_kw_in_concern_custom_posts'] : [];
		$existing = get_option( 'bluet_kw_advanced', [] );
		$prev_concern = is_array( $existing['bt_kw_in_concern_custom_posts'] ?? null )
			? $existing['bt_kw_in_concern_custom_posts']
			: [];

		$out['bt_kw_in_concern_custom_posts'] = [
			'post_types'           => self::post_type_list( $concern['post_types'] ?? [] ),
			'custom_fields'        => sanitize_text_field( (string) ( $concern['custom_fields'] ?? ( $prev_concern['custom_fields'] ?? '' ) ) ),
			'custom_fields_hooks'  => is_array( $concern['custom_fields_hooks'] ?? null )
				? $concern['custom_fields_hooks']
				: ( $prev_concern['custom_fields_hooks'] ?? [] ),
			'is_acf_field'         => is_array( $concern['is_acf_field'] ?? null )
				? $concern['is_acf_field']
				: ( $prev_concern['is_acf_field'] ?? [] ),
		];

		$plugins = is_array( $input['bt_kw_supported_plugins'] ?? null ) ? $input['bt_kw_supported_plugins'] : [];
		$out['bt_kw_supported_plugins'] = [
			'bbpress' => Sanitizer::on_off( $plugins['bbpress'] ?? '' ),
			'wooc'    => Sanitizer::on_off( $plugins['wooc'] ?? '' ),
		];

		return $out;
	}

	private static function html_tag_list( string $raw ): string {
		$parts = preg_split( '/\s+/', strtolower( $raw ) ) ?: [];
		$clean = [];
		foreach ( $parts as $tag ) {
			$tag = preg_replace( '/[^a-z0-9]/', '', $tag ) ?? '';
			if ( $tag !== '' && strlen( $tag ) <= 20 ) {
				$clean[] = $tag;
			}
		}
		return implode( ' ', array_unique( $clean ) );
	}

	private static function event_names( string $raw ): string {
		$parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		$clean = [];
		foreach ( $parts as $event ) {
			if ( preg_match( '/^[A-Za-z][A-Za-z0-9_-]{0,63}$/', $event ) ) {
				$clean[] = $event;
			}
		}
		return implode( ',', array_unique( $clean ) );
	}

	/**
	 * @param mixed $types
	 * @return string[]
	 */
	private static function post_type_list( $types ): array {
		if ( ! is_array( $types ) ) {
			return [];
		}
		return array_values( array_filter( array_map( 'sanitize_key', $types ) ) );
	}
}
