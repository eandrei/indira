<?php
/**
 * Template Name: Full Width
 *
 */

$page_blocks_enabled = get_post_meta( $post->ID, 'nikkon-meta-box-checkbox-blocks', true );
$page_title_disabled = get_post_meta( $post->ID, 'nikkon-meta-box-checkbox-title', true );

get_header(); ?>

	<div id="primary" class="content-area content-area-full">
		<main id="main" class="site-main" role="main">
			
			<?php if ( ! $page_title_disabled ) : ?>
				<?php get_template_part( '/templates/titlebar' ); ?>
			<?php endif; ?>
			
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'templates/contents/content', 'page' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
				?>

			<?php endwhile; // end of the loop. ?>
			
			<?php
			// Blocks layout on Page
			if ( $page_blocks_enabled ) : ?>
				
				<?php get_template_part( 'templates/contents/content-blocks-loop' ); ?>
				
			<?php endif; ?>
			
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>