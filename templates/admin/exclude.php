<?php
/**
 * Excluded posts list (settings panel).
 *
 * @package Tooltipy
 */

defined( 'ABSPATH' ) || exit;

$excluded_posts = \Tooltipy\Admin\SettingsPage::get_excluded_posts();
?>
<div id="bluet_kw_excluded_posts" class="tooltipy-excluded">
	<h2><?php esc_html_e( 'Excluded posts', 'tooltipy-lang' ); ?></h2>
	<p class="description"><?php esc_html_e( 'These posts are excluded from keyword matching. Change this from each post’s sidebar metabox.', 'tooltipy-lang' ); ?></p>

	<?php if ( empty( $excluded_posts ) ) : ?>
		<p class="tooltipy-settings__empty"><?php esc_html_e( 'No posts or pages are excluded.', 'tooltipy-lang' ); ?></p>
	<?php else : ?>
		<ul class="tooltipy-excluded__list">
			<?php foreach ( $excluded_posts as $excluded_post ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $excluded_post['id'] ) ); ?>">
						<?php echo esc_html( $excluded_post['title'] ); ?>
					</a>
					<a class="tooltipy-excluded__edit" href="<?php echo esc_url( get_edit_post_link( $excluded_post['id'] ) ); ?>">
						<?php esc_html_e( 'Edit', 'tooltipy-lang' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
