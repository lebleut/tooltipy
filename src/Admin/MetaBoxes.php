<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) || exit;

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;

/**
 * Metaboxes for keyword posts and content posts.
 */
class MetaBoxes {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_action( 'edit_form_after_title', [ $this, 'render_after_title_metaboxes' ] );
		add_action( 'do_meta_boxes', [ $this, 'register_metaboxes' ] );
		add_action( 'save_post', [ $this, 'save' ] );
	}

	public function render_after_title_metaboxes(): void {
		global $post, $post_type;
		do_meta_boxes( $post_type, 'after_title', $post );
	}

	public function register_metaboxes(): void {
		$pt_name = $this->plugin->get_post_type_name();

		add_meta_box(
			'bluet_kw_settings_meta',
			__( 'Keyword options', 'tooltipy-lang' ),
			[ $this, 'render_keyword_settings' ],
			$pt_name,
			'after_title',
			'high'
		);

		$screens = array_unique(
			array_merge(
				array_diff( get_post_types( [ 'public' => true ], 'names' ), [ $pt_name ] ),
				$this->get_custom_post_types_to_filter()
			)
		);

		foreach ( $screens as $screen ) {
			if ( $screen === $pt_name || ! post_type_exists( $screen ) ) {
				continue;
			}
			add_meta_box(
				'bluet_kw_post_related_keywords_meta',
				__( 'Tooltipy', 'tooltipy-lang' ),
				[ $this, 'render_related_keywords' ],
				$screen,
				'side',
				'high'
			);
		}
	}

	public function render_keyword_settings(): void {
		$kw_id = get_the_ID();
		wp_nonce_field( 'tooltipy_save_metaboxes', 'tooltipy_metabox_nonce' );

		$synonyms = (string) get_post_meta( $kw_id, 'bluet_synonyms_keywords', true );
		$case     = get_post_meta( $kw_id, 'bluet_case_sensitive_word', true );
		$prefix   = get_post_meta( $kw_id, 'bluet_prefix_keywords', true );
		$youtube  = (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true );
		?>
		<div class="tooltipy-meta tooltipy-meta--keyword">
			<div class="tooltipy-meta__grid">
				<div class="tooltipy-meta__field tooltipy-meta__field--full">
					<label class="tooltipy-meta__label" for="bluet_synonyms_id"><?php esc_html_e( 'Synonyms', 'tooltipy-lang' ); ?></label>
					<input
						class="tooltipy-meta__input"
						type="text"
						id="bluet_synonyms_id"
						name="bluet_synonyms_name"
						value="<?php echo esc_attr( $synonyms ); ?>"
						placeholder="<?php esc_attr_e( "Separate synonyms with |  e.g. car|auto|vehicle", 'tooltipy-lang' ); ?>"
					/>
					<p class="tooltipy-meta__hint"><?php esc_html_e( 'These alternate spellings will also trigger this tooltip.', 'tooltipy-lang' ); ?></p>
				</div>

				<div class="tooltipy-meta__field">
					<span class="tooltipy-meta__label"><?php esc_html_e( 'Matching', 'tooltipy-lang' ); ?></span>
					<div class="tooltipy-meta__toggles">
						<label class="tooltipy-meta__toggle">
							<input type="checkbox" id="bluet_case_sensitive_id" name="bluet_case_sensitive_name" <?php checked( $case, 'on' ); ?> />
							<span><?php esc_html_e( 'Case sensitive', 'tooltipy-lang' ); ?></span>
						</label>
						<label class="tooltipy-meta__toggle">
							<input type="checkbox" id="bluet_prefix_id" name="bluet_prefix_name" <?php checked( $prefix, 'on' ); ?> />
							<span><?php esc_html_e( 'Prefix match', 'tooltipy-lang' ); ?></span>
						</label>
					</div>
					<p class="tooltipy-meta__hint"><?php esc_html_e( 'Prefix match: “photo” also matches “photography”.', 'tooltipy-lang' ); ?></p>
				</div>

				<div class="tooltipy-meta__field">
					<label class="tooltipy-meta__label" for="bluet_video_id"><?php esc_html_e( 'YouTube video', 'tooltipy-lang' ); ?></label>
					<div class="tooltipy-meta__youtube">
						<span class="tooltipy-meta__youtube-prefix">youtube.com/watch?v=</span>
						<input
							class="tooltipy-meta__input"
							id="bluet_video_id"
							name="bluet_video_id_name"
							type="text"
							value="<?php echo esc_attr( $youtube ); ?>"
							placeholder="dQw4w9WgXcQ"
						/>
					</div>
					<p class="tooltipy-meta__hint"><?php esc_html_e( 'Optional video shown inside the tooltip popup.', 'tooltipy-lang' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_related_keywords(): void {
		global $post;

		$pt_name            = $this->plugin->get_post_type_name();
		$post_id            = (int) $post->ID;
		$exclude_me         = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );
		$exclude_kws_string = (string) get_post_meta( $post_id, 'bluet_exclude_keywords_from_matching', true );
		$excluded_kws       = array_filter(
			array_map( 'strtolower', array_map( 'trim', explode( ',', $exclude_kws_string ) ) )
		);

		$repo   = new KeywordRepository( $this->plugin );
		$kw_ids = $repo->get_related_ids( $post_id );

		wp_nonce_field( 'tooltipy_save_metaboxes', 'tooltipy_metabox_nonce' );
		?>
		<div class="tooltipy-meta tooltipy-meta--post">
			<label class="tooltipy-meta__toggle tooltipy-meta__toggle--danger">
				<input
					type="checkbox"
					id="bluet_kw_admin_exclude_post_from_matching_id"
					name="bluet_exclude_post_from_matching_name"
					<?php checked( $exclude_me, 'on' ); ?>
					data-tooltipy-toggle-target="#bluet_kw_admin_div_terms"
				/>
				<span><?php esc_html_e( 'Exclude this post from matching', 'tooltipy-lang' ); ?></span>
			</label>

			<div id="bluet_kw_admin_div_terms" class="tooltipy-meta__body"<?php echo $exclude_me === 'on' ? ' hidden' : ''; ?>>
				<div class="tooltipy-meta__section">
					<span class="tooltipy-meta__label"><?php esc_html_e( 'Matched keywords', 'tooltipy-lang' ); ?></span>
					<?php if ( ! empty( $kw_ids ) ) : ?>
						<ul class="tooltipy-meta__chips">
							<?php foreach ( $kw_ids as $kw_id ) :
								$title     = get_the_title( $kw_id );
								$is_ex     = in_array( strtolower( trim( $title ) ), $excluded_kws, true );
								$edit_link = get_edit_post_link( $kw_id );
								?>
								<li class="tooltipy-meta__chip<?php echo $is_ex ? ' is-excluded' : ''; ?>">
									<?php if ( $edit_link ) : ?>
										<a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $title ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $title ); ?>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="tooltipy-meta__empty"><?php esc_html_e( 'No keywords matched in this content yet.', 'tooltipy-lang' ); ?></p>
					<?php endif; ?>
				</div>

				<div class="tooltipy-meta__section">
					<span class="tooltipy-meta__label"><?php esc_html_e( 'Keywords to exclude', 'tooltipy-lang' ); ?></span>
					<div class="easy_tags tooltipy-meta__tags" data-easy-tags-delimiter=",">
						<div class="easy_tags-content" onclick="document.getElementById('bluet_exclude_keywords_field_id').focus()">
							<div class="easy_tags-list tagchecklist" id="exclude_keywords_list"></div>
							<input class="easy_tags-field" type="text" id="bluet_exclude_keywords_field_id" placeholder="<?php esc_attr_e( 'Add keyword…', 'tooltipy-lang' ); ?>">
							<input class="easy_tags-to_send" type="hidden" name="bluet_exclude_keywords_from_matching_name" id="exclude-keywords-field" value="<?php echo esc_attr( $exclude_kws_string ); ?>">
						</div>
						<input class="easy_tags-add button" type="button" value="<?php esc_attr_e( 'Add' ); ?>" id="exclude_keywords_add">
					</div>
				</div>

				<p class="tooltipy-meta__footer">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $pt_name ) ); ?>">
						<?php esc_html_e( 'Manage keywords', 'tooltipy-lang' ); ?> →
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	public function save( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $_POST['post_type'] ) || ( $_POST['action'] ?? '' ) !== 'editpost' ) {
			return;
		}

		if ( ! isset( $_POST['tooltipy_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tooltipy_metabox_nonce'] ) ), 'tooltipy_save_metaboxes' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$pt_name = $this->plugin->get_post_type_name();

		if ( ( $_POST['post_type'] ?? '' ) === $pt_name ) {
			$syns = isset( $_POST['bluet_synonyms_name'] )
				? sanitize_text_field( wp_unslash( $_POST['bluet_synonyms_name'] ) )
				: '';
			$syns = (string) preg_replace( '(\|{2,100})', '|', $syns );
			$syns = (string) preg_replace( '(^\||\|$|[\s]{2,100})', '', $syns );

			$case    = isset( $_POST['bluet_case_sensitive_name'] ) ? 'on' : '';
			$prefix  = isset( $_POST['bluet_prefix_name'] ) ? 'on' : '';
			$youtube = isset( $_POST['bluet_video_id_name'] )
				? sanitize_text_field( wp_unslash( $_POST['bluet_video_id_name'] ) )
				: '';

			update_post_meta( $post_id, 'bluet_synonyms_keywords', $syns );
			update_post_meta( $post_id, 'bluet_case_sensitive_word', $case );
			update_post_meta( $post_id, 'bluet_prefix_keywords', $prefix );
			update_post_meta( $post_id, 'bluet_youtube_video_id', $youtube );
		} else {
			$exclude_me  = ! empty( $_POST['bluet_exclude_post_from_matching_name'] ) ? 'on' : '';
			$exclude_kws = sanitize_text_field( wp_unslash( $_POST['bluet_exclude_keywords_from_matching_name'] ?? '' ) );

			update_post_meta( $post_id, 'bluet_exclude_post_from_matching', $exclude_me );
			update_post_meta( $post_id, 'bluet_exclude_keywords_from_matching', $exclude_kws );

			$matchable = $_POST['matchable_keywords'] ?? [];
			$arr_match = [];
			if ( is_array( $matchable ) ) {
				foreach ( $matchable as $mid ) {
					$mid = (int) $mid;
					if ( $mid > 0 ) {
						$arr_match[ $mid ] = $mid;
					}
				}
			}
			update_post_meta( $post_id, 'bluet_matching_keywords_field', $arr_match );
		}
	}

	/** @return string[] */
	private function get_custom_post_types_to_filter(): array {
		$options = get_option( 'bluet_kw_advanced', [] );
		$types   = [];

		if ( ! empty( $options['bt_kw_in_concern_custom_posts']['post_types'] ) ) {
			$types = (array) $options['bt_kw_in_concern_custom_posts']['post_types'];
		}

		if ( ! empty( $options['bt_kw_supported_plugins']['bbpress'] ) ) {
			$types[] = 'topic';
		}
		if ( ! empty( $options['bt_kw_supported_plugins']['wooc'] ) ) {
			$types[] = 'product';
		}

		return array_values( array_filter( array_unique( $types ) ) );
	}
}
