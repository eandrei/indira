<?php
/**
 * Implements styles set in the theme customizer
 *
 * @package Customizer Library Nikkon
 */

if ( ! function_exists( 'customizer_library_nikkon_build_styles' ) && class_exists( 'Customizer_Library_Styles' ) ) :
/**
 * Process user options to generate CSS needed to implement the choices.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function customizer_library_nikkon_build_styles() {
	
	// Primary Color
	$setting = 'nikkon-primary-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'#comments .form-submit #submit,
				.search-block .search-submit,
				.no-results-btn,
				button,
				input[type="button"],
				input[type="reset"],
				input[type="submit"],
				.woocommerce ul.products li.product a.add_to_cart_button, .woocommerce-page ul.products li.product a.add_to_cart_button,
				.woocommerce ul.products li.product .onsale, .woocommerce-page ul.products li.product .onsale,
				.woocommerce button.button.alt,
				.woocommerce-page button.button.alt,
				.woocommerce input.button.alt:hover,
				.woocommerce-page #content input.button.alt:hover,
				.woocommerce .cart-collaterals .shipping_calculator .button,
				.woocommerce-page .cart-collaterals .shipping_calculator .button,
				.woocommerce a.button,
				.woocommerce-page a.button,
				.woocommerce input.button,
				.woocommerce-page #content input.button,
				.woocommerce-page input.button,
				.woocommerce #review_form #respond .form-submit input,
				.woocommerce-page #review_form #respond .form-submit input,
				.woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover,
				.single-product span.onsale,
				.main-navigation ul ul a:hover,
				.main-navigation ul ul li.current-menu-item > a,
				.main-navigation ul ul li.current_page_item > a,
				.main-navigation ul ul li.current-menu-parent > a,
				.main-navigation ul ul li.current_page_parent > a,
				.main-navigation ul ul li.current-menu-ancestor > a,
				.main-navigation ul ul li.current_page_ancestor > a,
				.main-navigation button,
				.wpcf7-submit'
			),
			'declarations' => array(
				'background' => 'inherit',
                'background-color' => $color
			)
		) );
	}
	
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'a,
				.content-area .entry-content a,
				#comments a,
				.post-edit-link,
				.site-title a,
				.error-404.not-found .page-header .page-title span,
				.search-button .fa-search,
				.header-cart-checkout.cart-has-items .fa-shopping-cart,
				.main-navigation ul#primary-menu > li > a:hover,
				.main-navigation ul#primary-menu > li.current-menu-item > a,
				.main-navigation ul#primary-menu > li.current-menu-ancestor > a,
				.main-navigation ul#primary-menu > li.current-menu-parent > a,
				.main-navigation ul#primary-menu > li.current_page_parent > a,
				.main-navigation ul#primary-menu > li.current_page_ancestor > a'
			),
			'declarations' => array(
                'color' => $color
			)
		) );
	}
	
	

	// Secondary Color
	$setting = 'nikkon-secondary-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.main-navigation button:hover,
				#comments .form-submit #submit:hover,
				.search-block .search-submit:hover,
				.no-results-btn:hover,
				button,
				input[type="button"],
				input[type="reset"],
				input[type="submit"],
				.woocommerce input.button.alt,
				.woocommerce-page #content input.button.alt,
				.woocommerce .cart-collaterals .shipping_calculator .button,
				.woocommerce-page .cart-collaterals .shipping_calculator .button,
				.woocommerce a.button:hover,
				.woocommerce-page a.button:hover,
				.woocommerce input.button:hover,
				.woocommerce-page #content input.button:hover,
				.woocommerce-page input.button:hover,
				.woocommerce ul.products li.product a.add_to_cart_button:hover, .woocommerce-page ul.products li.product a.add_to_cart_button:hover,
				.woocommerce button.button.alt:hover,
				.woocommerce-page button.button.alt:hover,
				.woocommerce #review_form #respond .form-submit input:hover,
				.woocommerce-page #review_form #respond .form-submit input:hover,
				.woocommerce-cart .wc-proceed-to-checkout a.checkout-button,
				.wpcf7-submit:hover'
			),
			'declarations' => array(
				'background' => 'inherit',
                'background-color' => $color
			)
		) );
	}
	
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'a:hover,
				.nikkon-header-nav ul li a:hover,
				.content-area .entry-content a:hover,
				.header-social .social-icon:hover,
				.widget-area .widget a:hover,
				.site-footer-widgets .widget a:hover,
				.site-footer .widget a:hover,
				.search-btn:hover,
				.search-button .fa-search:hover,
				.woocommerce #content div.product p.price,
				.woocommerce-page #content div.product p.price,
				.woocommerce-page div.product p.price,
				.woocommerce #content div.product span.price,
				.woocommerce div.product span.price,
				.woocommerce-page #content div.product span.price,
				.woocommerce-page div.product span.price,
				.woocommerce #content div.product .woocommerce-tabs ul.tabs li.active,
				.woocommerce div.product .woocommerce-tabs ul.tabs li.active,
				.woocommerce-page #content div.product .woocommerce-tabs ul.tabs li.active,
				.woocommerce-page div.product .woocommerce-tabs ul.tabs li.active'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Body Font
	$setting = 'nikkon-body-font';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );
	$stack = customizer_library_get_font_stack( $mod );

	if ( $mod != customizer_library_get_default( $setting ) ) {

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'body,
				.widget-area .widget a'
			),
			'declarations' => array(
				'font-family' => $stack
			)
		) );

	}
	
	// Body Font Color
	$setting = 'nikkon-body-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'body,
                .widget-area .widget a'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}

	// Heading Font
	$setting = 'nikkon-heading-font';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );
	$stack = customizer_library_get_font_stack( $mod );

	if ( $mod != customizer_library_get_default( $setting ) ) {

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'h1, h2, h3, h4, h5, h6,
                h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
                .widget-area .widget-title,
                .main-navigation ul li a,
                .woocommerce table.cart th,
                .woocommerce-page #content table.cart th,
                .woocommerce-page table.cart th,
                .woocommerce input.button.alt,
                .woocommerce-page #content input.button.alt,
                .woocommerce table.cart input,
                .woocommerce-page #content table.cart input,
                .woocommerce-page table.cart input,
                button, input[type="button"],
                input[type="reset"],
                input[type="submit"]',
			),
			'declarations' => array(
				'font-family' => $stack
			)
		) );

	}
	
	// Heading Font Color
	$setting = 'nikkon-heading-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'h1, h2, h3, h4, h5, h6,
                h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
                .widget-area .widget-title'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	
	// Site Title Font
	$setting = 'nikkon-title-font';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );
	$stack = customizer_library_get_font_stack( $mod );

	if ( $mod != customizer_library_get_default( $setting ) ) {

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-title a'
			),
			'declarations' => array(
				'font-family' => $stack
			)
		) );

	}
	// Site Title Font Size
	$setting = 'nikkon-title-font-size';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$title_font_size = esc_attr( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-title'
			),
			'declarations' => array(
				'font-size' => $title_font_size . 'px'
			)
		) );
	}
	// Site Title Font Size
	$setting = 'nikkon-tagline-font-size';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$title_font_size = esc_attr( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-description'
			),
			'declarations' => array(
				'font-size' => $title_font_size . 'px'
			)
		) );
	}
	// Site Title Bottom Margin
	$setting = 'nikkon-title-bottom-margin';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$title_bottom_margin = esc_attr( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-title'
			),
			'declarations' => array(
				'margin-bottom' => $title_bottom_margin . 'px'
			)
		) );
	}
	
	// Site Logo Max Width
	$setting = 'nikkon-logo-max-width';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$logo_max_width = esc_attr( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-branding a.custom-logo-link'
			),
			'declarations' => array(
				'max-width' => $logo_max_width . 'px'
			)
		) );
	}
	
	// Site Boxed Background Color
	$setting = 'nikkon-boxed-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-boxed'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	
	// Header Background Color
	$setting = 'nikkon-header-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-header,
				.main-navigation ul ul'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Header Font Color
	$setting = 'nikkon-header-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-header,
				.header-cart,
				.main-navigation ul li a,
				.header-social .header-social-icon,
				.header-social .social-pinterest span'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	
	// Top Bar Background Color
	$setting = 'nikkon-topbar-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-top-bar,
				.site-top-bar-left,
				.site-top-bar-right,
				.search-block'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Top Bar Font Color
	$setting = 'nikkon-topbar-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-top-bar'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	
	// Navigation Background Color
	$setting = 'nikkon-nav-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.main-navigation,
				.main-navigation ul ul'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Navigation Font Color
	$setting = 'nikkon-nav-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.main-navigation ul li a,
				a.header-cart-contents,
				.header-cart,
				.header-menu-button'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	// Navigation Drop Down Background Color
	$setting = 'nikkon-nav-drop-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.main-navigation ul ul'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Navigation Drop Down Font Color
	$setting = 'nikkon-nav-drop-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.main-navigation ul ul li a'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	
	// Footer Background Color
	$setting = 'nikkon-footer-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer-standard,
				.site-footer.site-footer-social,
				.site-footer.site-footer-custom'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Footer Font Color
	$setting = 'nikkon-footer-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	// Footer Heading Font Color
	$setting = 'nikkon-footer-heading-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer .widgettitle,
				.site-footer .widget-title'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer .widgettitle,
				.site-footer .widget-title'
			),
			'declarations' => array(
				'border-bottom' => '1px dotted ' . $color
			)
		) );
	}
	// Footer Bottom Bar Background Color
	$setting = 'nikkon-footer-bottombar-bg-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer-bottom-bar'
			),
			'declarations' => array(
				'background-color' => $color
			)
		) );
	}
	// Footer Bottom Bar Font Color
	$setting = 'nikkon-footer-bottombar-font-color';
	$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

	if ( $mod !== customizer_library_get_default( $setting ) ) {

		$color = sanitize_hex_color( $mod );

		Customizer_Library_Styles()->add( array(
			'selectors' => array(
				'.site-footer-bottom-bar,
				.site-footer-bottom-bar .social-pinterest span'
			),
			'declarations' => array(
				'color' => $color
			)
		) );
	}
	
	// Footer Custom custom widths
	if ( get_theme_mod( 'nikkon-footer-customize' ) ) :
		
		// Site Footer Column Widths
		$setting = 'nikkon-footer-customize-col-1';
		$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

		if ( $mod !== customizer_library_get_default( $setting ) ) {

			$logo_max_width = esc_attr( $mod );

			Customizer_Library_Styles()->add( array(
				'selectors' => array(
					'.footer-custom-block.footer-custom-one'
				),
				'declarations' => array(
					'width' => $logo_max_width . '%'
				)
			) );
		}
		$setting = 'nikkon-footer-customize-col-2';
		$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

		if ( $mod !== customizer_library_get_default( $setting ) ) {

			$logo_max_width = esc_attr( $mod );

			Customizer_Library_Styles()->add( array(
				'selectors' => array(
					'.footer-custom-block.footer-custom-two'
				),
				'declarations' => array(
					'width' => $logo_max_width . '%'
				)
			) );
		}
		$setting = 'nikkon-footer-customize-col-3';
		$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

		if ( $mod !== customizer_library_get_default( $setting ) ) {

			$logo_max_width = esc_attr( $mod );

			Customizer_Library_Styles()->add( array(
				'selectors' => array(
					'.footer-custom-block.footer-custom-three'
				),
				'declarations' => array(
					'width' => $logo_max_width . '%'
				)
			) );
		}
		$setting = 'nikkon-footer-customize-col-4';
		$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

		if ( $mod !== customizer_library_get_default( $setting ) ) {

			$logo_max_width = esc_attr( $mod );

			Customizer_Library_Styles()->add( array(
				'selectors' => array(
					'.footer-custom-block.footer-custom-four'
				),
				'declarations' => array(
					'width' => $logo_max_width . '%'
				)
			) );
		}
		$setting = 'nikkon-footer-customize-col-5';
		$mod = get_theme_mod( $setting, customizer_library_get_default( $setting ) );

		if ( $mod !== customizer_library_get_default( $setting ) ) {

			$logo_max_width = esc_attr( $mod );

			Customizer_Library_Styles()->add( array(
				'selectors' => array(
					'.footer-custom-block.footer-custom-five'
				),
				'declarations' => array(
					'width' => $logo_max_width . '%'
				)
			) );
		}
	
	endif;

}
endif;

add_action( 'customizer_library_styles', 'customizer_library_nikkon_build_styles' );

if ( ! function_exists( 'customizer_library_nikkon_styles' ) ) :
/**
 * Generates the style tag and CSS needed for the theme options.
 *
 * By using the "Customizer_Library_Styles" filter, different components can print CSS in the header.
 * It is organized this way to ensure there is only one "style" tag.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function customizer_library_nikkon_styles() {

	do_action( 'customizer_library_styles' );

	// Echo the rules
	$css = Customizer_Library_Styles()->build();

	if ( ! empty( $css ) ) {
		echo "\n<!-- Begin Custom CSS -->\n<style type=\"text/css\" id=\"nikkon-custom-css\">\n";
		echo $css;
		echo "\n</style>\n<!-- End Custom CSS -->\n";
	}
}
endif;

add_action( 'wp_head', 'customizer_library_nikkon_styles', 11 );