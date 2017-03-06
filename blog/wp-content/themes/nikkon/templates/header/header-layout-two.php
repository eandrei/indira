<?php
/**
 * @package Nikkon
 */
global $woocommerce; ?>

<?php if ( !get_theme_mod( 'nikkon-header-remove-topbar' ) ) : ?>
	<div class="site-top-bar site-header-layout-two">
		
		<div class="site-container">
			
			<div class="site-top-bar-left">
				
				<?php wp_nav_menu( array( 'theme_location' => 'top-bar-menu', 'container_class' => 'nikkon-header-nav', 'fallback_cb' => false ) ); ?>
				
				<?php get_template_part( '/templates/social-links' ); ?>

			</div>
			<div class="site-top-bar-right">
				
				<?php if ( !get_theme_mod( 'nikkon-header-search' ) ) : ?>
					<div class="menu-search">
				    	<i class="fa fa-search search-btn"></i>
				    </div>
				<?php endif; ?>
				
				<?php if ( !get_theme_mod( 'nikkon-header-remove-no' ) ) : ?>
					<span class="site-topbar-right-no"><i class="fa fa-phone"></i> <?php echo wp_kses_post( get_theme_mod( 'nikkon-website-head-no', __( 'Call Us: +2782 444 YEAH', 'nikkon' ) ) ) ?></span>
				<?php endif; ?>
				
				<?php if ( nikkon_is_woocommerce_activated() ) : ?>
					<?php if ( !get_theme_mod( 'nikkon-header-remove-cart' ) ) : ?>
						<div class="header-cart">
							
				            <a class="header-cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e( 'View your shopping cart', 'nikkon' ); ?>">
				                <span class="header-cart-amount">
				                    <?php echo sprintf( _n( '%d', '%d', $woocommerce->cart->cart_contents_count, 'nikkon' ), $woocommerce->cart->cart_contents_count ); ?><span> - <?php echo $woocommerce->cart->get_cart_total(); ?></span>
				                </span>
				                <span class="header-cart-checkout <?php echo ( $woocommerce->cart->cart_contents_count > 0 ) ? sanitize_html_class( 'cart-has-items' ) : ''; ?>">
				                    <i class="fa fa-shopping-cart"></i>
				                </span>
				            </a>
							
						</div>
					<?php endif; ?>
				<?php endif; ?>
				
			</div>
			
			<?php if ( !get_theme_mod( 'nikkon-header-search' ) ) : ?>
			    <div class="search-block">
			        <?php get_search_form(); ?>
			    </div>
			<?php endif; ?>
			
		</div>
		
		<div class="clearboth"></div>
	</div>
<?php endif; ?>

<header id="masthead" class="site-header site-header-layout-two <?php echo ( get_theme_mod( 'nikkon-header-layout-type' ) == 'nikkon-header-layout-outward' ) ? sanitize_html_class( 'header-nav-outward' ) : sanitize_html_class( 'header-nav-inward' ); ?>">
	
	<div class="site-container">
		
		<div class="site-branding">
			
			<?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
            <?php endif; ?>
			
		</div><!-- .site-branding -->
		
		<span class="header-menu-button"><i class="fa fa-bars"></i><span><?php echo esc_attr( get_theme_mod( 'nikkon-header-menu-text', __( 'menu', 'nikkon' ) ) ); ?></span></span>
		
		<div id="main-menu" class="main-menu-container site-navigation-wrap">
			<span class="main-menu-close"><i class="fa fa-angle-right"></i><i class="fa fa-angle-left"></i></span>
			
			<nav id="site-navigation-left" class="main-navigation main-navigation-left" role="navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'primary-left', 'menu_id' => 'primary-menu-left' ) ); ?>
			</nav><!-- #site-navigation left -->
			
			<nav id="site-navigation-right" class="main-navigation main-navigation-right" role="navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'primary-right', 'menu_id' => 'primary-menu-right' ) ); ?>
			</nav><!-- #site-navigation right -->
			
			<div class="clearboth"></div>
		</div>
				
	</div>
		
</header><!-- #masthead -->