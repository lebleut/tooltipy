<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) || exit;

use Tooltipy\Plugin;

/**
 * Settings page UI + Options tab fields.
 */
class SettingsPage {

	private Plugin $plugin;

	private const ANIMATIONS = [
		'bounce', 'bounceIn', 'bounceInLeft', 'bounceInRight', 'bounceInDown', 'bounceInUp',
		'fadeIn', 'fadeInLeft', 'fadeInLeftBig', 'fadeInRight', 'fadeInRightBig', 'fadeInUp', 'fadeInUpBig',
		'flash', 'flip', 'flipInX', 'flipInY', 'lightSpeedIn', 'pulse', 'rollIn',
		'rotateIn', 'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight',
		'slideInDown', 'slideInLeft', 'slideInRight', 'slideInUp', 'swing', 'shake', 'tada',
		'wobble', 'zoomIn', 'zoomInDown', 'zoomInLeft', 'zoomInRight', 'zoomInUp',
	];

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_submenu' ] );
	}

	public function register_settings(): void {
		add_settings_section( 'concern_section', __( 'General', 'tooltipy-lang' ), [ $this, 'section_concern_cb' ], 'my_keywords_settings' );

		add_settings_field( 'kttg_tooltip_post_types', __( 'Get tooltips from', 'tooltipy-lang' ), [ $this, 'field_tooltip_post_types' ], 'my_keywords_settings', 'concern_section' );
		add_settings_field( 'bt_kw_match_all_field', __( 'Match once or all occurrences', 'tooltipy-lang' ), [ $this, 'field_match_all' ], 'my_keywords_settings', 'concern_section' );
		add_settings_field( 'bt_kw_hide_title', __( 'Tooltip title', 'tooltipy-lang' ), [ $this, 'field_hide_title' ], 'my_keywords_settings', 'concern_section' );
		add_settings_field( 'bt_kw_position', __( 'Tooltip position', 'tooltipy-lang' ), [ $this, 'field_position' ], 'my_keywords_settings', 'concern_section' );
		add_settings_field( 'bt_kw_animation_type', __( 'Animation', 'tooltipy-lang' ), [ $this, 'field_animation_type' ], 'my_keywords_settings', 'concern_section' );

		register_setting( 'settings_group', 'bluet_kw_settings' );
	}

	public function add_submenu(): void {
		$pt_name = $this->plugin->get_post_type_name();
		add_submenu_page(
			'edit.php?post_type=' . $pt_name,
			__( 'Tooltipy Settings', 'tooltipy-lang' ),
			__( 'Settings' ),
			'manage_options',
			'my_keywords_settings',
			[ $this, 'render_page' ]
		);
	}

	public function section_concern_cb(): void {
		echo '<p class="tooltipy-settings__lead">' . esc_html__( 'How tooltips behave across your site.', 'tooltipy-lang' ) . '</p>';
	}

	public function field_tooltip_post_types(): void {
		$pt_name  = $this->plugin->get_post_type_name();
		$options  = get_option( 'bluet_kw_settings', [] );
		$selected = $options['kttg_tooltip_post_types'] ?? [];

		echo '<select multiple name="bluet_kw_settings[kttg_tooltip_post_types][]" size="8" class="tooltipy-settings__select-multi">';
		foreach ( get_post_types() as $pt ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $pt ),
				in_array( $pt, (array) $selected, true ) ? ' selected' : '',
				esc_html( $pt )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Post types that provide keyword definitions.', 'tooltipy-lang' ) . ' (' . esc_html( $pt_name ) . ')</p>';

		if ( empty( $selected ) || ! in_array( $pt_name, (array) $selected, true ) ) {
			echo '<p class="tooltipy-settings__warn"><strong>' . esc_html( $pt_name ) . '</strong> ' . esc_html__( 'is not selected.', 'tooltipy-lang' ) . '</p>';
		}
	}

	public function field_match_all(): void {
		$options = get_option( 'bluet_kw_settings', [] );
		?>
		<label class="tooltipy-settings__check">
			<input type="checkbox" id="bt_kw_match_all_id" name="bluet_kw_settings[bt_kw_match_all]" <?php checked( $options['bt_kw_match_all'] ?? '', 'on' ); ?> />
			<span><?php esc_html_e( 'Match all occurrences in the content', 'tooltipy-lang' ); ?></span>
		</label>
		<?php
	}

	public function field_hide_title(): void {
		$options = get_option( 'bluet_kw_settings', [] );
		?>
		<label class="tooltipy-settings__check">
			<input type="checkbox" id="bt_kw_hide_title_id" name="bluet_kw_settings[bt_kw_hide_title]" <?php checked( $options['bt_kw_hide_title'] ?? '', 'on' ); ?> />
			<span><?php esc_html_e( 'Hide the tooltip title', 'tooltipy-lang' ); ?></span>
		</label>
		<?php
	}

	public function field_position(): void {
		$options  = get_option( 'bluet_kw_settings', [] );
		$position = $options['bt_kw_position'] ?? 'bottom';
		echo '<div class="tooltipy-settings__radios">';
		foreach ( [ 'top', 'bottom', 'right', 'left' ] as $pos ) {
			printf(
				'<label><input type="radio" name="bluet_kw_settings[bt_kw_position]" value="%1$s" %2$s /> %3$s</label>',
				esc_attr( $pos ),
				checked( $position, $pos, false ),
				esc_html( ucfirst( $pos ) )
			);
		}
		echo '</div>';
	}

	public function field_animation_type(): void {
		$options    = get_option( 'bluet_kw_settings', [] );
		$anim_type  = $options['bt_kw_animation_type'] ?? 'none';
		$anim_speed = $options['bt_kw_animation_speed'] ?? 'kttg_normal';

		echo '<select id="select_anim" name="bluet_kw_settings[bt_kw_animation_type]">';
		echo '<option value="none"' . selected( $anim_type, 'none', false ) . '>' . esc_html__( 'None', 'tooltipy-lang' ) . '</option>';
		foreach ( self::ANIMATIONS as $anim ) {
			echo '<option value="' . esc_attr( $anim ) . '"' . selected( $anim_type, $anim, false ) . '>' . esc_html( $anim ) . '</option>';
		}
		echo '</select>';

		echo '<div class="tooltipy-settings__radios tooltipy-settings__radios--inline">';
		foreach ( [ 'kttg_fast' => __( 'Fast', 'tooltipy-lang' ), 'kttg_normal' => __( 'Normal', 'tooltipy-lang' ), 'kttg_slow' => __( 'Slow', 'tooltipy-lang' ) ] as $val => $label ) {
			printf(
				'<label for="select_speed_%1$s"><input type="radio" id="select_speed_%1$s" name="bluet_kw_settings[bt_kw_animation_speed]" value="%1$s" %2$s /> %3$s</label>',
				esc_attr( $val ),
				checked( $anim_speed, $val, false ),
				esc_html( $label )
			);
		}
		echo '</div>';

		echo '<button type="button" id="demo_div" class="button tooltipy-settings__demo">' . esc_html__( 'Preview animation', 'tooltipy-lang' ) . '</button>';
		?>
		<script>
		(function($){
			function play(){
				var speed = $("input[name='bluet_kw_settings[bt_kw_animation_speed]']:checked").val() || "kttg_normal";
				var anim = $("#select_anim").val() || "none";
				$("#demo_div").removeClass().addClass("button tooltipy-settings__demo animated " + speed + " " + anim);
			}
			$(document).on("change", "#select_anim, input[name='bluet_kw_settings[bt_kw_animation_speed]']", play);
			$(document).on("click", "#demo_div", play);
		})(jQuery);
		</script>
		<?php
	}

	public function render_page(): void {
		$plugin_data = get_plugin_data( TOOLTIPY_PLUGIN_FILE );
		$panels      = [
			'style'    => [
				'label' => __( 'Style', 'tooltipy-lang' ),
				'desc'  => __( 'Colors, width, and highlight mode', 'tooltipy-lang' ),
				'icon'  => 'dashicons-art',
			],
			'options'  => [
				'label' => __( 'Options', 'tooltipy-lang' ),
				'desc'  => __( 'Matching, position, animation', 'tooltipy-lang' ),
				'icon'  => 'dashicons-admin-generic',
			],
			'glossary' => [
				'label' => __( 'Glossary', 'tooltipy-lang' ),
				'desc'  => __( 'Shortcode page and labels', 'tooltipy-lang' ),
				'icon'  => 'dashicons-book-alt',
			],
			'advanced' => [
				'label' => __( 'Advanced', 'tooltipy-lang' ),
				'desc'  => __( 'Cover areas, exclusions, filters', 'tooltipy-lang' ),
				'icon'  => 'dashicons-admin-tools',
			],
			'excluded' => [
				'label' => __( 'Excluded posts', 'tooltipy-lang' ),
				'desc'  => __( 'Posts skipped by matching', 'tooltipy-lang' ),
				'icon'  => 'dashicons-dismiss',
			],
		];
		?>
		<div class="wrap tooltipy-settings" id="tooltipy-settings">
			<header class="tooltipy-settings__header">
				<div>
					<h1><?php esc_html_e( 'Tooltipy Settings', 'tooltipy-lang' ); ?></h1>
					<p class="tooltipy-settings__version">
						<?php echo esc_html( $plugin_data['Name'] ); ?>
						<span><?php echo esc_html( $plugin_data['Version'] ); ?></span>
					</p>
				</div>
				<div class="tooltipy-settings__header-actions">
					<a class="button" href="https://wordpress.org/support/plugin/bluet-keywords-tooltip-generator" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help', 'tooltipy-lang' ); ?></a>
					<a class="button" href="https://wordpress.org/support/view/plugin-reviews/bluet-keywords-tooltip-generator" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Rate', 'tooltipy-lang' ); ?></a>
				</div>
			</header>

			<?php settings_errors(); ?>

			<form method="post" action="options.php" class="tooltipy-settings__form" id="tooltipy-settings-form">
				<?php settings_fields( 'settings_group' ); ?>

				<div class="tooltipy-settings__layout">
					<nav class="tooltipy-settings__nav" aria-label="<?php esc_attr_e( 'Settings sections', 'tooltipy-lang' ); ?>">
						<?php foreach ( $panels as $id => $panel ) : ?>
							<button
								type="button"
								class="tooltipy-settings__nav-item<?php echo $id === 'style' ? ' is-active' : ''; ?>"
								data-tooltipy-panel="<?php echo esc_attr( $id ); ?>"
								aria-controls="tooltipy-panel-<?php echo esc_attr( $id ); ?>"
							>
								<span class="dashicons <?php echo esc_attr( $panel['icon'] ); ?>" aria-hidden="true"></span>
								<span class="tooltipy-settings__nav-text">
									<strong><?php echo esc_html( $panel['label'] ); ?></strong>
									<small><?php echo esc_html( $panel['desc'] ); ?></small>
								</span>
							</button>
						<?php endforeach; ?>
						<?php do_action( 'tooltipy_settings_tabs' ); ?>
					</nav>

					<div class="tooltipy-settings__panels" id="tooltipy-settings-panels">
						<section class="tooltipy-settings__panel is-active" id="tooltipy-panel-style" data-panel="style">
							<div class="tooltipy-settings__card">
								<?php $this->load_template( 'admin/style' ); ?>
							</div>
						</section>

						<section class="tooltipy-settings__panel" id="tooltipy-panel-options" data-panel="options" hidden>
							<div class="tooltipy-settings__card">
								<?php do_settings_sections( 'my_keywords_settings' ); ?>
							</div>
						</section>

						<section class="tooltipy-settings__panel" id="tooltipy-panel-glossary" data-panel="glossary" hidden>
							<div class="tooltipy-settings__card">
								<?php do_settings_sections( 'my_keywords_glossary_settings' ); ?>
							</div>
						</section>

						<section class="tooltipy-settings__panel" id="tooltipy-panel-advanced" data-panel="advanced" hidden>
							<div class="tooltipy-settings__card">
								<?php do_settings_sections( 'my_keywords_advanced_page' ); ?>
							</div>
						</section>

						<section class="tooltipy-settings__panel" id="tooltipy-panel-excluded" data-panel="excluded" hidden>
							<div class="tooltipy-settings__card">
								<?php $this->load_template( 'admin/exclude' ); ?>
							</div>
						</section>

						<?php do_action( 'tooltipy_settings_tab_panels' ); ?>
					</div>
				</div>

				<footer class="tooltipy-settings__footer">
					<button type="submit" class="button button-primary button-hero" id="tooltipy-settings-save">
						<?php esc_html_e( 'Save settings', 'tooltipy-lang' ); ?>
					</button>
					<span class="tooltipy-settings__footer-hint"><?php esc_html_e( 'Saves all tabs at once.', 'tooltipy-lang' ); ?></span>
				</footer>
			</form>
		</div>
		<?php
	}

	private function load_template( string $name ): void {
		$file = TOOLTIPY_PLUGIN_DIR . 'templates/' . $name . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		}
	}

	/** @return array<int, array{id:int,title:string,slug:string}> */
	public static function get_excluded_posts(): array {
		$types = [ 'post', 'page' ];
		$adv   = get_option( 'bluet_kw_advanced', [] );
		if ( ! empty( $adv['bt_kw_in_concern_custom_posts']['post_types'] ) && is_array( $adv['bt_kw_in_concern_custom_posts']['post_types'] ) ) {
			$types = array_unique( array_merge( $types, $adv['bt_kw_in_concern_custom_posts']['post_types'] ) );
		}

		$query = new \WP_Query( [
			'post_type'      => $types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [ [
				'key'     => 'bluet_exclude_post_from_matching',
				'value'   => 'on',
				'compare' => '=',
			] ],
		] );

		$result = [];
		foreach ( $query->posts as $pid ) {
			$post = get_post( (int) $pid );
			if ( ! $post ) {
				continue;
			}
			$result[] = [
				'id'    => (int) $pid,
				'title' => get_the_title( $pid ),
				'slug'  => $post->post_name,
			];
		}
		wp_reset_postdata();
		return $result;
	}
}
