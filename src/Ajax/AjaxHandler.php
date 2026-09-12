<?php
namespace Tooltipy\Ajax;

defined( 'ABSPATH' ) || exit;

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;
use Tooltipy\Security\Sanitizer;

/**
 * AJAX loading of tooltip popup HTML.
 */
class AjaxHandler {

	private Plugin $plugin;
	private KeywordRepository $repo;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->repo   = new KeywordRepository( $plugin );
	}

	public function init(): void {
		add_action( 'wp_ajax_tltpy_load_keywords', [ $this, 'load_keywords' ] );
		add_action( 'wp_ajax_nopriv_tltpy_load_keywords', [ $this, 'load_keywords' ] );
		add_action( 'wp_footer', [ $this, 'output_tooltip_container' ] );
		add_action( 'wp_footer', [ $this, 'output_ajax_script' ] );
	}

	public function output_tooltip_container(): void {
		if ( is_admin() ) {
			return;
		}
		echo '<div id="tooltip_blocks_to_show" style="display:none;"></div>' . "\n";
	}

	public function load_keywords(): void {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'tltpy_load_keywords_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'tooltipy-lang' ), 403 );
		}

		if ( empty( $_POST['keyword_ids'] ) ) {
			wp_die();
		}

		$keyword_ids = [];
		foreach ( (array) $_POST['keyword_ids'] as $id ) {
			$keyword_ids[] = absint( $id );
		}
		$keyword_ids = array_values( array_unique( array_filter( $keyword_ids ) ) );

		$query        = $this->repo->query_by_ids( $keyword_ids );
		$settings     = get_option( 'bluet_kw_settings', [] );
		$style_opt    = get_option( 'bluet_kw_style', [] );
		$glossary_opt = get_option( 'bluet_glossary_options', [] );
		$hide_title   = ! empty( $settings['bt_kw_hide_title'] ) && $settings['bt_kw_hide_title'] === 'on';
		$cat_name     = $this->plugin->get_cat_name();
		$extra_cls    = Sanitizer::css_classes( (string) ( $style_opt['bt_kw_add_css_classes']['popup'] ?? '' ) );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$kw_id    = get_the_ID();
				$youtube  = Sanitizer::youtube_id( (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true ) );
				$yt_class = $youtube !== '' ? 'tooltipy-pop-youtube' : '';

				$families_arr = wp_get_post_terms( $kw_id, $cat_name, [ 'fields' => 'ids' ] );
				$families_cls = '';
				if ( is_array( $families_arr ) ) {
					$families_cls = implode(
						' ',
						array_map(
							static fn( $fid ) => 'tooltipy-pop-cat-' . absint( $fid ),
							$families_arr
						)
					);
				}

				$class_attr = trim( "bluet_block_to_show tooltipy-pop tooltipy-pop-{$kw_id} {$families_cls} {$yt_class} {$extra_cls}" );
				$content    = Sanitizer::tooltip_html( (string) apply_filters( 'the_content', get_post_field( 'post_content', $kw_id ) ) );
				?>
				<span class="<?php echo esc_attr( $class_attr ); ?>" data-tooltip="<?php echo esc_attr( (string) $kw_id ); ?>">
					<div class="bluet_hide_tooltip_button">&times;</div>
					<div class="bluet_block_container">
						<?php if ( $youtube === '' ) : ?>
							<div class="bluet_img_in_tooltip"><?php echo get_the_post_thumbnail( $kw_id, 'medium' ); ?></div>
						<?php else : ?>
							<div class="bluet_img_in_tooltip">
								<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube ); ?>?rel=0&showinfo=0" frameborder="0" allowfullscreen width="100%" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
							</div>
						<?php endif; ?>
						<div class="bluet_text_content">
							<?php if ( ! $hide_title ) : ?>
								<span class="bluet_title_on_block"><?php echo esc_html( get_the_title() ); ?></span>
							<?php endif; ?>
							<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses_post applied ?>
						</div>
						<?php if ( ! empty( $glossary_opt['bluet_kttg_show_glossary_link'] ) && $glossary_opt['bluet_kttg_show_glossary_link'] === 'on' ) : ?>
							<div class="bluet_block_footer">
								<p class="bluet_block_glossary_link">
									<a href="<?php echo esc_url( $glossary_opt['kttg_link_glossary_page_link'] ?? '' ); ?>">
										<?php echo esc_html( ! empty( $glossary_opt['kttg_link_glossary_label'] ) ? $glossary_opt['kttg_link_glossary_label'] : __( 'View glossary', 'tooltipy-lang' ) ); ?>
									</a>
								</p>
							</div>
						<?php endif; ?>
					</div>
				</span>
				<?php
			}
		}

		wp_reset_postdata();
		wp_die();
	}

	public function output_ajax_script(): void {
		if ( is_admin() ) {
			return;
		}

		$ajax_url = admin_url( 'admin-ajax.php' );
		$nonce    = wp_create_nonce( 'tltpy_load_keywords_nonce' );
		$mq       = wp_script_is( 'wp-mediaelement', 'enqueued' );
		?>
		<script>
		var currentHoveredKeyword = false;

		jQuery(document).on("keywordsFetched", function() {
			var keyw = [];
			jQuery("body .bluet_tooltip").each(function(){
				keyw.push(jQuery(this).data('tooltip'));
			});

			jQuery.post(
				<?php echo wp_json_encode( $ajax_url ); ?>,
				{
					'action':      'tltpy_load_keywords',
					'keyword_ids': keyw,
					'nonce':       <?php echo wp_json_encode( $nonce ); ?>
				},
				function(response){
					jQuery('#tooltip_blocks_to_show .bluet_block_to_show').remove(':not(#loading_tooltip)');
					jQuery('#tooltip_blocks_to_show').append(response);
					jQuery.event.trigger("keywordsLoaded");
				}
			);
		});

		jQuery(document).on("keywordsLoaded", function() {
			jQuery('#loading_tooltip').remove();

			if( currentHoveredKeyword && currentHoveredKeyword?.trigger && typeof currentHoveredKeyword.trigger === 'function' ){
				currentHoveredKeyword.trigger('mouseover');
				currentHoveredKeyword = 'done';
			}

			<?php if ( $mq ) : ?>
			jQuery('.tooltipy-pop .wp-audio-shortcode[style*="visibility:hidden"], .tooltipy-pop .wp-video-shortcode[style*="visibility:hidden"]').mediaelementplayer();
			jQuery('.tooltipy-pop .wp-audio-shortcode[style*="visibility: hidden"], .tooltipy-pop .wp-video-shortcode[style*="visibility: hidden"]').mediaelementplayer();
			<?php endif; ?>
		});
		</script>
		<?php
	}
}
