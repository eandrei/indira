<?php
/**
 * @package Nikkon
 */

$page_blocks_enabled = get_post_meta( $post->ID, 'nikkon-meta-box-checkbox', true );
$page_cats = get_post_meta( $post->ID, 'nikkon-meta-box-cats', true );
$page_ppp = get_post_meta( $post->ID, 'nikkon-meta-box-ppp', true );

$blog_columns = 'blog-columns-three';
if ( get_theme_mod( 'nikkon-blog-column-layout' ) )
	$blog_columns = get_theme_mod( 'nikkon-blog-column-layout' );

$blog_style = 'blog-style-postblock';
if ( get_theme_mod( 'nikkon-blog-blocks-style' ) )
	$blog_style = get_theme_mod( 'nikkon-blog-blocks-style' );

// Set Posts Per Page to show all if on front/home page
$ppp = '';
if ( get_post_meta( $post->ID, 'nikkon-meta-box-ppp', true ) ) {
	if ( is_front_page() ) {
		$ppp = '&posts_per_page=-1';
	} else {
		$ppp = '&posts_per_page=' . $page_ppp;
	}
} else {
	$ppp = '';
}

// Set Pagination Paged values
if ( get_query_var( 'paged' ) ) {
	$paged = get_query_var( 'paged' );
} else if ( get_query_var( 'page' ) ) {
	$paged = get_query_var( 'page' );
} else {
	$paged = 1;
}

// Set Categories or if not set then show all posts
if ( $page_cats ) {
    $cats_set = 'cat=' . $page_cats . $ppp . '&paged=' . $paged;
} else {
    $cats_set = 'post_type=post' . $ppp . '&paged=' . $paged;
}

$page_cats_query = new WP_Query( $cats_set );

if ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) :
	echo '<div class="blog-blocks-wrap blog-blocks-wrap-remove ' . sanitize_html_class( $blog_columns ) . ' ' . sanitize_html_class( $blog_style ) . '">';
		echo '<div class="blog-blocks-wrap-inner blog-blocks-custom-loop">';
else :
	echo '<div class="blog-blocks-custom-loop">';
endif; ?>
	        
			<?php while ( $page_cats_query->have_posts() ) : $page_cats_query->the_post(); ?>
				
				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'templates/contents/content', get_post_format() );
				?>
				
			<?php endwhile; // end of the loop. ?>
		
		<?php
if ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) :
		// Close the divs for the blocks layout
		echo '</div>';
		echo '<div class="clearboth"></div>';
	echo '</div>';
else:
	echo '</div>';
endif;

// Custom Pagination
if ( $page_cats_query->max_num_pages > 1 ) : ?>
	<div class="clearboth"></div>
    <nav class="navigation posts-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'nikkon' ); ?></h2>
		<div class="nav-links">
        	
        	<div class="nav-next"><?php echo get_previous_posts_link( esc_html__( 'Newer posts', 'nikkon' ) );  ?></div>
			
            <div class="nav-previous"><?php echo get_next_posts_link( esc_html__( 'Older posts', 'nikkon' ), $page_cats_query->max_num_pages );  ?></div>
        	
        </div><!-- .nav-links -->
	</nav><!-- .navigation -->
<?php endif;
wp_reset_query(); ?>
