<?php
if( get_theme_mod( 'nikkon-social-email', false ) ) :
    echo '<a href="' . esc_url( 'mailto:' . antispambot( get_theme_mod( 'nikkon-social-email' ), 1 ) ) . '" title="' . __( 'Send Us an Email', 'nikkon' ) . '" class="social-icon social-email"><i class="fa fa-envelope-o"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-skype', false ) ) :
    echo '<a href="skype:' . esc_html( get_theme_mod( 'nikkon-social-skype' ) ) . '?userinfo" title="' . __( 'Contact Us on Skype', 'nikkon' ) . '" class="social-icon social-skype"><i class="fa fa-skype"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-facebook', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-facebook' ) ) . '" target="_blank" title="' . __( 'Find Us on Facebook', 'nikkon' ) . '" class="social-icon social-facebook"><i class="fa fa-facebook"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-twitter', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-twitter' ) ) . '" target="_blank" title="' . __( 'Follow Us on Twitter', 'nikkon' ) . '" class="social-icon social-twitter"><i class="fa fa-twitter"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-google-plus', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-google-plus' ) ) . '" target="_blank" title="' . __( 'Find Us on Google Plus', 'nikkon' ) . '" class="social-icon social-gplus"><i class="fa fa-google-plus"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-snapchat', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-snapchat' ) ) . '" target="_blank" title="' . __( 'Follow Us on SnapChat', 'nikkon' ) . '" class="social-icon social-snapchat"><i class="fa fa-snapchat"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-etsy', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-etsy' ) ) . '" target="_blank" title="' . __( 'Find Us on Etsy', 'nikkon' ) . '" class="social-icon social-etsy"><i class="fa fa-etsy"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-youtube', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-youtube' ) ) . '" target="_blank" title="' . __( 'View our YouTube Channel', 'nikkon' ) . '" class="social-icon social-youtube"><i class="fa fa-youtube-play"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-instagram', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-instagram' ) ) . '" target="_blank" title="' . __( 'Follow Us on Instagram', 'nikkon' ) . '" class="social-icon social-instagram"><i class="fa fa-instagram"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-pinterest', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-pinterest' ) ) . '" target="_blank" title="' . __( 'Pin Us on Pinterest', 'nikkon' ) . '" class="social-icon social-pinterest"><i class="fa fa-pinterest"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-medium', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-medium' ) ) . '" target="_blank" title="' . __( 'Find us on Medium', 'nikkon' ) . '" class="social-icon social-medium"><i class="fa fa-medium"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-behance', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-behance' ) ) . '" target="_blank" title="' . __( 'Find us on Behance', 'nikkon' ) . '" class="social-icon social-behance"><i class="fa fa-behance"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-product-hunt', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-product-hunt' ) ) . '" target="_blank" title="' . __( 'Find us on Product Hunt', 'nikkon' ) . '" class="social-icon social-product-hunt"><i class="fa fa-product-hunt"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-slack', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-slack' ) ) . '" target="_blank" title="' . __( 'Find us on Slack', 'nikkon' ) . '" class="social-icon social-slack"><i class="fa fa-slack"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-linkedin', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-linkedin' ) ) . '" target="_blank" title="' . __( 'Find Us on LinkedIn', 'nikkon' ) . '" class="social-icon social-linkedin"><i class="fa fa-linkedin"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-tumblr', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-tumblr' ) ) . '" target="_blank" title="' . __( 'Find Us on Tumblr', 'nikkon' ) . '" class="social-icon social-tumblr"><i class="fa fa-tumblr"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-flickr', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-flickr' ) ) . '" target="_blank" title="' . __( 'Find Us on Flickr', 'nikkon' ) . '" class="social-icon social-flickr"><i class="fa fa-flickr"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-houzz', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-houzz' ) ) . '" target="_blank" title="' . __( 'Find our profile on Houzz', 'nikkon' ) . '" class="social-icon social-houzz"><i class="fa fa-houzz"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-vk', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-vk' ) ) . '" target="_blank" title="' . __( 'Find Us on VK', 'nikkon' ) . '" class="social-icon social-vk"><i class="fa fa-vk"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-tripadvisor', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-tripadvisor' ) ) . '" target="_blank" title="' . __( 'Find Us on TripAdvisor', 'nikkon' ) . '" class="social-icon social-tripadvisor"><i class="fa fa-tripadvisor"></i></a>';
endif;

if( get_theme_mod( 'nikkon-social-github', false ) ) :
    echo '<a href="' . esc_url( get_theme_mod( 'nikkon-social-github' ) ) . '" target="_blank" title="' . __( 'Find Us on GitHub', 'nikkon' ) . '" class="social-icon social-github"><i class="fa fa-github"></i></a>';
endif;