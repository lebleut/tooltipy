<?php
/**
 * single page template for keywords post type
 */

get_header(); ?>

<style>
#bluet_post_meta{
	text-align: center;
}
</style>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<article class="post type-post status-publish has-post-thumbnail">
				<?php /* The loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>
					<div class="entry-thumbnail" style="max-width: 50%;"><?php the_post_thumbnail(); ?></div>
					<div id="bluet_the_article" class="entry-content">
						<?php the_content(); ?>
					</div>
					
				<?php endwhile; ?>
			</article>
		</div><!-- #content -->

	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
