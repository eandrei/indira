<footer id="colophon" class="site-footer site-footer-social">
	
	<div class="site-footer-icons">
        <div class="site-container">
            
            <?php
			if( get_theme_mod( 'nikkon-social-email', false ) ) :
			    echo '<a href="' . esc_url( 'mailto:' . antispambot( get_theme_mod( 'nikkon-social-email' ), 1 ) ) . '" title="' . __( 'Send Us an Email', 'nikkon' ) . '" class="footer-social-icon footer-social-email"><i class="fa fa-envelope-o"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-skype', false ) ) :
			    echo '<a href="skype:' . esc_html( get_theme_mod( 'nikkon-social-skype' ) ) . '?userinfo" title="' . __( 'Contact Us on Skype', 'nikkon' ) . '" class="footer-social-icon footer-social-skype"><i class="fa fa-skype"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-facebook', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-facebook' ) ) . '" target="_blank" title="' . __( 'Find Us on Facebook', 'nikkon' ) . '" class="footer-social-icon footer-social-facebook"><i class="fa fa-facebook"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-twitter', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-twitter' ) ) . '" target="_blank" title="' . __( 'Follow Us on Twitter', 'nikkon' ) . '" class="footer-social-icon footer-social-twitter"><i class="fa fa-twitter"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-google-plus', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-google-plus' ) ) . '" target="_blank" title="' . __( 'Find Us on Google Plus', 'nikkon' ) . '" class="footer-social-icon footer-social-gplus"><i class="fa fa-google-plus"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-snapchat', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-snapchat' ) ) . '" target="_blank" title="' . __( 'Follow Us on SnapChat', 'nikkon' ) . '" class="footer-social-icon footer-social-snapchat"><i class="fa fa-snapchat"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-etsy', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-etsy' ) ) . '" target="_blank" title="' . __( 'Find Us on Etsy', 'nikkon' ) . '" class="footer-social-icon footer-social-etsy"><i class="fa fa-etsy"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-youtube', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-youtube' ) ) . '" target="_blank" title="' . __( 'View our YouTube Channel', 'nikkon' ) . '" class="footer-social-icon footer-social-youtube"><i class="fa fa-youtube-play"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-vimeo', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-vimeo' ) ) . '" target="_blank" title="' . __( 'View our Vimeo Channel', 'nikkon' ) . '" class="footer-social-icon footer-social-vimeo"><i class="fa fa-vimeo"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-instagram', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-instagram' ) ) . '" target="_blank" title="' . __( 'Follow Us on Instagram', 'nikkon' ) . '" class="footer-social-icon footer-social-instagram"><i class="fa fa-instagram"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-pinterest', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-pinterest' ) ) . '" target="_blank" title="' . __( 'Pin Us on Pinterest', 'nikkon' ) . '" class="footer-social-icon footer-social-pinterest"><i class="fa fa-pinterest"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-medium', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-medium' ) ) . '" target="_blank" title="' . __( 'Find us on Medium', 'nikkon' ) . '" class="footer-social-icon social-medium"><i class="fa fa-medium"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-behance', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-behance' ) ) . '" target="_blank" title="' . __( 'Find us on Behance', 'nikkon' ) . '" class="footer-social-icon social-behance"><i class="fa fa-behance"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-product-hunt', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-product-hunt' ) ) . '" target="_blank" title="' . __( 'Find us on Product Hunt', 'nikkon' ) . '" class="footer-social-icon social-product-hunt"><i class="fa fa-product-hunt"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-slack', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-slack' ) ) . '" target="_blank" title="' . __( 'Find us on Slack', 'nikkon' ) . '" class="footer-social-icon social-slack"><i class="fa fa-slack"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-linkedin', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-linkedin' ) ) . '" target="_blank" title="' . __( 'Find Us on LinkedIn', 'nikkon' ) . '" class="footer-social-icon footer-social-linkedin"><i class="fa fa-linkedin"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-tumblr', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-tumblr' ) ) . '" target="_blank" title="' . __( 'Find Us on Tumblr', 'nikkon' ) . '" class="footer-social-icon footer-social-tumblr"><i class="fa fa-tumblr"></i></a>';
			endif;

			if( get_theme_mod( 'nikkon-social-flickr', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-flickr' ) ) . '" target="_blank" title="' . __( 'Find Us on Flickr', 'nikkon' ) . '" class="footer-social-icon footer-social-flickr"><i class="fa fa-flickr"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-houzz', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-houzz' ) ) . '" target="_blank" title="' . __( 'Find our profile on Houzz', 'nikkon' ) . '" class="footer-social-icon social-houzz"><i class="fa fa-houzz"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-vk', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-vk' ) ) . '" target="_blank" title="' . __( 'Find Us on VK', 'nikkon' ) . '" class="footer-social-icon social-vk"><i class="fa fa-vk"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-tripadvisor', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-tripadvisor' ) ) . '" target="_blank" title="' . __( 'Find Us on TripAdvisor', 'nikkon' ) . '" class="footer-social-icon footer-social-tripadvisor"><i class="fa fa-tripadvisor"></i></a>';
			endif;
			
			if( get_theme_mod( 'nikkon-social-github', false ) ) :
			    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-github' ) ) . '" target="_blank" title="' . __( 'Find Us on GitHub', 'nikkon' ) . '" class="footer-social-icon footer-social-github"><i class="fa fa-github"></i></a>';
			endif; ?>
			
        	<div class="site-footer-social-ad">
        		<i class="fa fa-map-marker"></i> <?php echo wp_kses_post( get_theme_mod( 'nikkon-website-site-add', __( 'Cape Town, South Africa', 'nikkon' ) ) ) ?>
        	</div>
			
			<div class="site-footer-social-copy">
				<?php echo wp_kses_post( get_theme_mod( 'nikkon-website-txt-copy', 'Nikkon theme, by <a href="https://kairaweb.com/">Kaira</a>' ) ) ?>
			</div>
            
            <div class="clearboth"></div>
        </div>
    </div>
    
</footer>

<?php if ( get_theme_mod( 'nikkon-footer-bottombar', false ) == 0 ) : ?>
	
	<div class="site-social-bottom-bar site-footer-bottom-bar">
	
		<div class="site-container">
			
	        <?php wp_nav_menu( array( 'theme_location' => 'footer-bar','container' => false, 'depth'  => 1 ) ); ?>
                
	    </div>
		
        <div class="clearboth"></div>
	</div>
	
<?php endif; ?>