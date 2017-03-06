<footer id="colophon" class="site-footer site-footer-custom">
	
	<div class="site-footer-widgets <?php echo ( get_theme_mod( 'nikkon-footer-custom-cols' ) ) ? sanitize_html_class( get_theme_mod( 'nikkon-footer-custom-cols' ) ) : sanitize_html_class( 'nikkon-footer-custom-cols-3' ); ?>">
        <div class="site-container">
        	
        	<?php if ( get_theme_mod( 'nikkon-footer-custom-cols', false ) == 'nikkon-footer-custom-cols-5' ) : ?>
        	
	            <div class="footer-custom-block footer-custom-one">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-1' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-two">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-2' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-three">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-3' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-four">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-4' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-five">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-5' ); ?>
	            </div>
		        
		    <?php elseif ( get_theme_mod( 'nikkon-footer-custom-cols', false ) == 'nikkon-footer-custom-cols-4' ) : ?>
		    	
		    	<div class="footer-custom-block footer-custom-one">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-1' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-two">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-2' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-three">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-3' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-four">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-4' ); ?>
	            </div>
	        
	        <?php elseif ( get_theme_mod( 'nikkon-footer-custom-cols', false ) == 'nikkon-footer-custom-cols-1' ) : ?>
	        	
	        	<div class="footer-custom-block footer-custom-one">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-1' ); ?>
	            </div>
	        
	        <?php elseif ( get_theme_mod( 'nikkon-footer-custom-cols', false ) == 'nikkon-footer-custom-cols-2' ) : ?>
	        	
	        	<div class="footer-custom-block footer-custom-one">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-1' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-two">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-2' ); ?>
	            </div>
	            
	        <?php else : ?>
	        	
	        	<div class="footer-custom-block footer-custom-one">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-1' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-two">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-2' ); ?>
	            </div>
	        
	            <div class="footer-custom-block footer-custom-three">
	                <?php dynamic_sidebar( 'nikkon-site-footer-custom-3' ); ?>
	            </div>
	            
	        <?php endif; ?>
	        
        </div>
    </div>
    
    <?php if ( get_theme_mod( 'nikkon-footer-bottombar', false ) == 0 ) : ?>
		
		<div class="site-footer-bottom-bar <?php echo ( get_theme_mod( 'nikkon-header-layout' ) == 'nikkon-header-layout-two' ) ? sanitize_html_class( 'layout-circles' ) : sanitize_html_class( 'layout-plain' ); ?>">
		
			<div class="site-container">
				
				<div class="site-footer-bottom-bar-left">
	                
	                <?php echo wp_kses_post( get_theme_mod( 'nikkon-website-txt-copy', 'Vogue theme, by <a href="https://kairaweb.com/">Kaira</a>' ) ) ?>
	                
	                <?php do_action ( 'nikkon_footer_bottombar_left' ); ?>
	                
				</div>
		        
		        <div class="site-footer-bottom-bar-right">
		        	
		        	<?php do_action ( 'nikkon_footer_bottombar_right' ); ?>
	                
		            <?php wp_nav_menu( array( 'theme_location' => 'footer-bar','container' => false, 'fallback_cb' => false, 'depth'  => 1 ) ); ?>
		            
		            <?php if ( ! get_theme_mod( 'nikkon-footer-hide-social' ) ) : ?>
		            
		            	<?php get_template_part( '/templates/social-links' ); ?>
		            	
		            <?php endif; ?>
	                
		        </div>
		        
		        <div class="clearboth"></div>
		    </div>
			
		</div>
		
	<?php endif; ?>
	
</footer>