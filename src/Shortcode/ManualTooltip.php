<?php
namespace Tooltipy\Shortcode;

defined( 'ABSPATH' ) || exit;

use Tooltipy\Plugin;

/**
 * Manual [tooltip] shortcode (legacy advanced feature).
 */
class ManualTooltip {

	private Plugin $plugin;

	/** @var array<int, string> */
	private array $pending_blocks = [];

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_shortcode( 'tooltip', [ $this, 'render' ] );
		add_action( 'wp_footer', [ $this, 'output_pending_blocks' ], 5 );
	}

	/**
	 * @param array<string, string>|string $atts
	 */
	public function render( $atts, ?string $content = null ): string {
		$atts = shortcode_atts(
			[
				'text'    => '',
				'image'   => '',
				'youtube' => '',
			],
			(array) $atts,
			'tooltip'
		);

		$post_id    = get_the_ID();
		$exclude_me = $post_id ? get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true ) : '';

		$inner = $content !== null && $content !== '' ? $content : '<b>tooltip</b>';

		if ( $exclude_me === 'on' ) {
			return $inner;
		}

		$tooltip_id = wp_rand( 11111, 55555 );

		$this->pending_blocks[ $tooltip_id ] = $this->build_block(
			(string) $atts['text'],
			(string) $atts['image'],
			(string) $atts['youtube'],
			$tooltip_id
		);

		add_filter(
			'kttg_another_tooltip_in_block',
			function ( string $cont ) use ( $tooltip_id ): string {
				if ( isset( $this->pending_blocks[ $tooltip_id ] ) ) {
					$cont .= $this->pending_blocks[ $tooltip_id ];
				}
				return $cont;
			}
		);

		return '<span class="bluet_tooltip" data-tooltip="' . esc_attr( (string) $tooltip_id ) . '">' . $inner . '</span>';
	}

	public function output_pending_blocks(): void {
		if ( empty( $this->pending_blocks ) ) {
			return;
		}

		$html = '';
		foreach ( $this->pending_blocks as $block ) {
			$html .= $block;
		}

		$html = apply_filters( 'kttg_another_tooltip_in_block', $html );

		echo '<div id="tooltipy_manual_tooltip_blocks" style="display:none;">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* below

		?>
		<script>
		jQuery(function($){
			var $manual = $("#tooltipy_manual_tooltip_blocks").children();
			if ($manual.length) {
				$("#tooltip_blocks_to_show").append($manual);
			}
		});
		</script>
		<?php
	}

	private function build_block( string $text, string $image, string $youtube, int $id ): string {
		$text_html  = '';
		$media_html = '';

		if ( $text !== '' ) {
			$text_html = '<div class="bluet_text_content">' . wp_kses_post( $text ) . '</div>';
		}

		if ( $image !== '' ) {
			$media_html = '<div class="bluet_img_in_tooltip"><img src="' . esc_url( $image ) . '" alt="" /></div>';
		}

		if ( $youtube !== '' ) {
			$iframe_id  = 'iframe_' . $id;
			$media_html = '<div class="bluet_img_in_tooltip" id="' . esc_attr( $iframe_id ) . '">'
				. '<iframe width="300" height="200" src="https://www.youtube.com/embed/' . esc_attr( $youtube ) . '?rel=0&amp;showinfo=0&amp;enablejsapi=1" frameborder="0" allowfullscreen></iframe>'
				. '</div>';
		}

		$close = '<img src="' . esc_url( TOOLTIPY_PLUGIN_URL . 'assets/close_button.png' ) . '" class="bluet_hide_tooltip_button" alt="" />';

		return '<span class="bluet_block_to_show" data-tooltip="' . esc_attr( (string) $id ) . '">'
			. $close
			. '<div class="bluet_block_container">'
			. $media_html
			. $text_html
			. '</div>'
			. '</span>';
	}
}
