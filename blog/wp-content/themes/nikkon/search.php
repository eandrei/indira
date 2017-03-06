<?php
/**
 * The template for displaying search results pages.
 *
 * @package Nikkon
 */
$blog_style = 'blog-style-postblock';
if ( get_theme_mod( 'nikkon-blog-blocks-style' ) )
	$blog_style = get_theme_mod( 'nikkon-blog-blocks-style' );

$blog_columns = 'blog-columns-three';
if ( get_theme_mod( 'nikkon-blog-column-layout' ) )
	$blog_columns = get_theme_mod( 'nikkon-blog-column-layout' );

get_header(); ?>

	<section id="primary" class="content-area <?php echo ( get_theme_mod( 'nikkon-blog-search-full-width', false ) ) ? sanitize_html_class( 'content-area-full' ) : ''; ?>">
		<main id="main" class="site-main" role="main">

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<h1 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'nikkon' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
				</header><!-- .page-header -->
				
				<?php echo ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) ? '<div class="blog-blocks-wrap blog-blocks-wrap-remove ' . sanitize_html_class( $blog_columns ) . ' ' . sanitize_html_class( $blog_style ) . '">' : ''; ?>
					<?php echo ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) ? '<div class="blog-blocks-wrap-inner">' : ''; ?>
					
						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>
							
							<?php
							/**
							 * Run the loop for the search to output the results.
							 * If you want to overload this in a child theme then include a file
							 * called content-search.php and that will be used instead.
							 */
							get_template_part( 'templates/contents/content', 'search' );
							?>

						<?php endwhile; ?>
						
					<?php echo ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) ? '<div class="clearboth"></div></div>' : ''; ?>
				<?php echo ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) ? '</div>' : ''; ?>

			<?php else : ?>

				<?php get_template_part( 'templates/contents/content', 'none' ); ?>

			<?php endif; ?>
			
			<?php the_posts_navigation(); ?>

		</main><!-- #main -->
	</section><!-- #primary -->

	<?php if ( get_theme_mod( 'nikkon-blog-search-full-width', false ) ) : ?>
        <!-- No Sidebar -->
    <?php else : ?>
        <?php get_sidebar(); ?>
    <?php endif; ?>
    
    <div class="clearboth"></div>

<?php get_footer(); ?>
