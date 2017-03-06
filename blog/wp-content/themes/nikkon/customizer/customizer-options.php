<?php
/**
 * Defines customizer options
 *
 * @package Customizer Library Nikkon
 */

function customizer_library_nikkon_options() {
	
    $site_boxed_color = '#FFFFFF';
	$primary_color = '#13C76D';
	$secondary_color = '#047b40';
    
    $header_bg_color = '#FFFFFF';
    $header_font_color = '#3C3C3C';
	
	$body_font_color = '#3C3C3C';
	$heading_font_color = '#000000';

	// Stores all the controls that will be added
	$options = array();

	// Stores all the sections to be added
	$sections = array();

	// Stores all the panels to be added
	$panels = array();

	// Adds the sections to the $options array
	$options['sections'] = $sections;
    
	
    // Header Image
    $section = 'title_tagline';
    
    $options['nikkon-logo-max-width'] = array(
        'id' => 'nikkon-logo-max-width',
        'label'   => __( 'Set a max-width for the logo', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( 'This only applies if a logo image is uploaded', 'nikkon' ),
        'default' => '',
    );
    
    $panel = 'nikkon-panel-layout';
    
    $panels[] = array(
        'id' => $panel,
        'title' => __( 'Layout Options', 'nikkon' ),
        'priority' => '30'
    );
    
	$section = 'nikkon-site-layout-section-site';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Site Layout', 'nikkon' ),
		'priority' => '30',
        'panel' => $panel
	);
	
    $choices = array(
        'nikkon-site-boxed' => __( 'Boxed Layout', 'nikkon' ),
        'nikkon-site-full-width' => __( 'Full Width Layout', 'nikkon' )
    );
    $options['nikkon-site-layout'] = array(
        'id' => 'nikkon-site-layout',
        'label'   => __( 'Site Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-site-full-width'
    );
	
	// WooCommerce style Layout
    if ( nikkon_is_woocommerce_activated() ) :
    	
        $options['nikkon-woocommerce-shop-fullwidth'] = array(
            'id' => 'nikkon-woocommerce-shop-fullwidth',
            'label'   => __( 'Make Shop page full width', 'nikkon' ),
            'section' => $section,
            'type'    => 'checkbox',
            'default' => 0,
        );
        $options['nikkon-woocommerce-shop-archive-fullwidth'] = array(
            'id' => 'nikkon-woocommerce-shop-archive-fullwidth',
            'label'   => __( 'Make Shop archive pages full width', 'nikkon' ),
            'section' => $section,
            'type'    => 'checkbox',
            'default' => 0,
        );
        $options['nikkon-woocommerce-shop-single-fullwidth'] = array(
            'id' => 'nikkon-woocommerce-shop-single-fullwidth',
            'label'   => __( 'Make Shop single pages full width', 'nikkon' ),
            'section' => $section,
            'type'    => 'checkbox',
            'default' => 0,
        );
        
    endif;
    
    $options['nikkon-pages-blocks-layout'] = array(
        'id' => 'nikkon-pages-blocks-layout',
        'label'   => __( 'Enable the Blocks layout on ALL PAGES', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
        'description' => __( 'This option will create a new options box on each page in the dashboard where you can specify which posts show on the page in the blocks layout.', 'nikkon' )
    );
    
    $section = 'nikkon-site-layout-section-page';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Pages', 'nikkon' ),
        'priority' => '30',
        'panel' => $panel
    );
    
    $options['nikkon-page-remove-titlebar'] = array(
        'id' => 'nikkon-page-remove-titlebar',
        'label'   => __( 'Remove Page Titles', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    $choices = array(
        'nikkon-page-fimage-layout-none' => __( 'None', 'nikkon' ),
        'nikkon-page-fimage-layout-standard' => __( 'Standard', 'nikkon' ),
        'nikkon-page-fimage-layout-banner' => __( 'Page Banner', 'nikkon' )
    );
    $options['nikkon-page-fimage-layout'] = array(
        'id' => 'nikkon-page-fimage-layout',
        'label'   => __( 'Featured Image Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-page-fimage-layout-none'
    );
    $choices = array(
        'nikkon-page-fimage-size-extra-small' => __( 'Extra Small Banner', 'nikkon' ),
        'nikkon-page-fimage-size-small' => __( 'Small Banner', 'nikkon' ),
        'nikkon-page-fimage-size-medium' => __( 'Medium Banner', 'nikkon' ),
        'nikkon-page-fimage-size-large' => __( 'Large Banner', 'nikkon' ),
        'nikkon-page-fimage-size-actual' => __( 'Use Proper Image', 'nikkon' )
    );
    $options['nikkon-page-fimage-size'] = array(
        'id' => 'nikkon-page-fimage-size',
        'label'   => __( 'Page Banner Size', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-page-fimage-size-medium'
    );
    $options['nikkon-page-fimage-fullwidth'] = array(
        'id' => 'nikkon-page-fimage-fullwidth',
        'label'   => __( 'Full Width Banner', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
	
	
	// Header Layout Options
	$section = 'nikkon-header-section';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Header Options', 'nikkon' ),
		'priority' => '30',
		'description' => __( '', 'nikkon' )
	);
    
    $options['nikkon-header-remove-topbar'] = array(
        'id' => 'nikkon-header-remove-topbar',
        'label'   => __( 'Remove Top Bar', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    $choices = array(
        'nikkon-header-layout-one' => __( 'Header Centered', 'nikkon' ),
        'nikkon-header-layout-two' => __( 'Header Centered Split', 'nikkon' ),
        'nikkon-header-layout-three' => __( 'Header Standard', 'nikkon' )
    );
    $options['nikkon-header-layout'] = array(
        'id' => 'nikkon-header-layout',
        'label'   => __( 'Header Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-header-layout-one'
    );
    
    $choices = array(
        'nikkon-header-layout-inward' => __( 'Center Out', 'nikkon' ),
        'nikkon-header-layout-outward' => __( 'Outwards In', 'nikkon' )
    );
    $options['nikkon-header-layout-type'] = array(
        'id' => 'nikkon-header-layout-type',
        'label'   => __( 'Header Layout Type', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-header-layout-inward'
    );
	
	$options['nikkon-header-menu-text'] = array(
		'id' => 'nikkon-header-menu-text',
		'label'   => __( 'Menu Button Text', 'nikkon' ),
		'section' => $section,
		'type'    => 'text',
		'default' => 'menu',
		'description' => __( 'This is the text for the mobile menu button', 'nikkon' )
	);
	
	$options['nikkon-header-search'] = array(
        'id' => 'nikkon-header-search',
        'label'   => __( 'Remove Search', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-header-remove-no'] = array(
        'id' => 'nikkon-header-remove-no',
        'label'   => __( 'Remove Phone Number', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    if ( nikkon_is_woocommerce_activated() ) :
        
        $options['nikkon-header-remove-cart'] = array(
            'id' => 'nikkon-header-remove-cart',
            'label'   => __( 'Remove WooCommerce Cart from Navigation', 'nikkon' ),
            'section' => $section,
            'type'    => 'checkbox',
            'default' => 0,
        );
        $options['nikkon-header-cartto-topbar'] = array(
            'id' => 'nikkon-header-cartto-topbar',
            'label'   => __( 'Add WooCommerce Cart to Top Bar', 'nikkon' ),
            'section' => $section,
            'type'    => 'checkbox',
            'default' => 0,
        );
    
    endif;
    
    // Slider Settings
    $section = 'nikkon-slider-section';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Slider Options', 'nikkon' ),
        'priority' => '35'
    );
    
    $choices = array(
        'nikkon-slider-default' => __( 'Default Slider', 'nikkon' ),
        'nikkon-meta-slider' => __( 'Meta Slider', 'nikkon' ),
        'nikkon-no-slider' => __( 'None', 'nikkon' )
    );
    $options['nikkon-slider-type'] = array(
        'id' => 'nikkon-slider-type',
        'label'   => __( 'Choose a Slider', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-slider-default'
    );
    $options['nikkon-slider-cats'] = array(
        'id' => 'nikkon-slider-cats',
        'label'   => __( 'Slider Categories', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'description' => __( 'Enter the ID\'s of the post categories you want to display in the slider. Eg: "13,17,19" (no spaces and only comma\'s)<br /><br />Get the ID at <b>Posts -> Categories</b>.<br /><br />Or <a href="https://kairaweb.com/documentation/setting-up-the-default-slider/" target="_blank"><b>See more instructions here</b></a>', 'nikkon' )
    );
    $options['nikkon-meta-slider-shortcode'] = array(
        'id' => 'nikkon-meta-slider-shortcode',
        'label'   => __( 'Slider Shortcode', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'description' => __( 'Enter the shortcode give by the slider.', 'nikkon' )
    );
    $options['nikkon-slider-full-width'] = array(
        'id' => 'nikkon-slider-full-width',
        'label'   => __( 'Set slider to full width', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $choices = array(
        'nikkon-slider-size-small' => __( 'Small Slider', 'nikkon' ),
        'nikkon-slider-size-medium' => __( 'Medium Slider', 'nikkon' ),
        'nikkon-slider-size-large' => __( 'Large Slider', 'nikkon' )
    );
    $options['nikkon-slider-size'] = array(
        'id' => 'nikkon-slider-size',
        'label'   => __( 'Slider Size', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-slider-size-medium'
    );
    $choices = array(
        'crossfade' => __( 'Fade', 'nikkon' ),
        'cover-fade' => __( 'Cover Fade', 'nikkon' ),
        'uncover-fade' => __( 'Uncover Fade', 'nikkon' ),
        'cover' => __( 'Cover', 'nikkon' ),
        'scroll' => __( 'Scroll', 'nikkon' )
    );
    $options['nikkon-slider-scroll-effect'] = array(
        'id' => 'nikkon-slider-scroll-effect',
        'label'   => __( 'Slider Scroll Effect', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'crossfade'
    );
    $options['nikkon-slider-linkto-post'] = array(
        'id' => 'nikkon-slider-linkto-post',
        'label'   => __( 'Link Slide to post', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-slider-remove-title'] = array(
        'id' => 'nikkon-slider-remove-title',
        'label'   => __( 'Remove Slider Title', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-slider-remove-pagination'] = array(
        'id' => 'nikkon-slider-remove-pagination',
        'label'   => __( 'Remove Slider Pagination', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-slider-auto-scroll'] = array(
        'id' => 'nikkon-slider-auto-scroll',
        'label'   => __( 'Stop Auto Scroll', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    

	// Colors
	$section = 'colors';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Colors', 'nikkon' ),
		'priority' => '80'
	);
    
    $options['nikkon-boxed-bg-color'] = array(
        'id' => 'nikkon-boxed-bg-color',
        'label'   => __( 'Site Boxed Background Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $site_boxed_color,
    );

	$options['nikkon-primary-color'] = array(
		'id' => 'nikkon-primary-color',
		'label'   => __( 'Primary Color', 'nikkon' ),
		'section' => $section,
		'type'    => 'color',
		'default' => $primary_color,
	);

	$options['nikkon-secondary-color'] = array(
		'id' => 'nikkon-secondary-color',
		'label'   => __( 'Secondary Color', 'nikkon' ),
		'section' => $section,
		'type'    => 'color',
		'default' => $secondary_color,
	);
    
    
    $panel = 'nikkon-panel-colors';
    
    $panels[] = array(
        'id' => $panel,
        'title' => __( 'Layout Colors', 'nikkon' ),
        'priority' => '80'
    );
    
    $section = 'nikkon-panel-colors-section-header';
    
    $sections[] = array(
        'id' => $section,
        'title' => __( 'Header', 'nikkon' ),
        'priority' => '10',
        'panel' => $panel
    );
    
    $options['nikkon-header-bg-color'] = array(
        'id' => 'nikkon-header-bg-color',
        'label'   => __( 'Background Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-header-font-color'] = array(
        'id' => 'nikkon-header-font-color',
        'label'   => __( 'Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    
    $options['nikkon-topbar-bg-color'] = array(
        'id' => 'nikkon-topbar-bg-color',
        'label'   => __( 'Top Bar Background Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-topbar-font-color'] = array(
        'id' => 'nikkon-topbar-font-color',
        'label'   => __( 'Top Bar Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    
    $section = 'nikkon-panel-colors-section-nav';
    
    $sections[] = array(
        'id' => $section,
        'title' => __( 'Navigation', 'nikkon' ),
        'priority' => '10',
        'panel' => $panel
    );
    
    $options['nikkon-nav-bg-color'] = array(
        'id' => 'nikkon-nav-bg-color',
        'label'   => __( 'Background Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-nav-font-color'] = array(
        'id' => 'nikkon-nav-font-color',
        'label'   => __( 'Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    
    $options['nikkon-nav-drop-bg-color'] = array(
        'id' => 'nikkon-nav-drop-bg-color',
        'label'   => __( 'Drop Down Bg Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-nav-drop-font-color'] = array(
        'id' => 'nikkon-nav-drop-font-color',
        'label'   => __( 'DropDown Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    
    $section = 'nikkon-panel-colors-section-footer';
    
    $sections[] = array(
        'id' => $section,
        'title' => __( 'Footer', 'nikkon' ),
        'priority' => '10',
        'panel' => $panel
    );
    
    $options['nikkon-footer-bg-color'] = array(
        'id' => 'nikkon-footer-bg-color',
        'label'   => __( 'Background Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-footer-heading-font-color'] = array(
        'id' => 'nikkon-footer-heading-font-color',
        'label'   => __( 'Heading Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    $options['nikkon-footer-font-color'] = array(
        'id' => 'nikkon-footer-font-color',
        'label'   => __( 'Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    
    $options['nikkon-footer-bottombar-bg-color'] = array(
        'id' => 'nikkon-footer-bottombar-bg-color',
        'label'   => __( 'Bottom Bar Bg Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_bg_color,
    );
    $options['nikkon-footer-bottombar-font-color'] = array(
        'id' => 'nikkon-footer-bottombar-font-color',
        'label'   => __( 'Bottom Bar Font Color', 'nikkon' ),
        'section' => $section,
        'type'    => 'color',
        'default' => $header_font_color,
    );
    

	// Font Options
	$section = 'nikkon-typography-section';
	$font_choices = customizer_library_get_font_choices();

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Font Options', 'nikkon' ),
		'priority' => '80'
	);
    
    $options['nikkon-title-font'] = array(
        'id' => 'nikkon-title-font',
        'label'   => __( 'Site Title Font', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $font_choices,
        'default' => 'Lato'
    );
    $options['nikkon-title-font-size'] = array(
        'id' => 'nikkon-title-font-size',
        'label'   => __( 'Site Title Size', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'default' => 64,
    );
    $options['nikkon-tagline-font-size'] = array(
        'id' => 'nikkon-tagline-font-size',
        'label'   => __( 'Site Tagline Size', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'default' => 14,
    );
    $options['nikkon-title-bottom-margin'] = array(
        'id' => 'nikkon-title-bottom-margin',
        'label'   => __( 'Site Title Bottom Margin', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( 'This will set the space between the site title and the site tagline', 'nikkon' ),
        'default' => 0,
    );
    
	$options['nikkon-body-font'] = array(
		'id' => 'nikkon-body-font',
		'label'   => __( 'Body Font', 'nikkon' ),
		'section' => $section,
		'type'    => 'select',
		'choices' => $font_choices,
		'default' => 'Open Sans'
	);
	$options['nikkon-body-font-color'] = array(
		'id' => 'nikkon-body-font-color',
		'label'   => __( 'Body Font Color', 'nikkon' ),
		'section' => $section,
		'type'    => 'color',
		'default' => $body_font_color,
	);

	$options['nikkon-heading-font'] = array(
		'id' => 'nikkon-heading-font',
		'label'   => __( 'Heading Font', 'nikkon' ),
		'section' => $section,
		'type'    => 'select',
		'choices' => $font_choices,
		'default' => 'Kaushan Script'
	);
	$options['nikkon-heading-font-color'] = array(
		'id' => 'nikkon-heading-font-color',
		'label'   => __( 'Heading Font Color', 'nikkon' ),
		'section' => $section,
		'type'    => 'color',
		'default' => $heading_font_color,
	);
	
	
    $panel = 'nikkon-panel-layout-blog';
    
    $panels[] = array(
        'id' => $panel,
        'title' => __( 'Blog Options', 'nikkon' ),
        'priority' => '50'
    );
    
    $section = 'nikkon-blog-section-blog';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Blog List', 'nikkon' ),
        'priority' => '50',
        'panel' => $panel
    );
    
    $choices = array(
        'blog-left-layout' => __( 'Left Layout', 'nikkon' ),
        'blog-right-layout' => __( 'Right Layout', 'nikkon' ),
        'blog-alt-layout' => __( 'Alternate Layout', 'nikkon' ),
        'blog-top-layout' => __( 'Top Layout', 'nikkon' ),
        'blog-blocks-layout' => __( 'Blocks Layout', 'nikkon' )
    );
    $options['nikkon-blog-layout'] = array(
        'id' => 'nikkon-blog-layout',
        'label'   => __( 'Blog Posts Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'blog-left-layout'
    );
    $options['nikkon-blog-title'] = array(
        'id' => 'nikkon-blog-title',
        'label'   => __( 'Blog Page Title', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'default' => 'Blog'
    );
    $options['nikkon-blog-cats'] = array(
        'id' => 'nikkon-blog-cats',
        'label'   => __( 'Exclude Blog Categories', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'description' => __( 'Enter the ID\'s of the post categories you\'d like to EXCLUDE from the Blog, enter only the ID\'s with a minus sign (-) before them, separated by a comma (,)<br />Eg: "-13, -17, -19"<br /><br />If you enter the ID\'s without the minus then it\'ll show ONLY posts in those categories.<br /><br />Get the ID at <b>Posts -> Categories</b>.', 'nikkon' )
    );
    $options['nikkon-blog-full-width'] = array(
        'id' => 'nikkon-blog-full-width',
        'label'   => __( 'Make Blog Full Width', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-cat-full-width'] = array(
        'id' => 'nikkon-blog-cat-full-width',
        'label'   => __( 'Make Archives/Categories full width', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-search-full-width'] = array(
        'id' => 'nikkon-blog-search-full-width',
        'label'   => __( 'Make Search Results Full Width', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-single-full-width'] = array(
        'id' => 'nikkon-blog-single-full-width',
        'label'   => __( 'Make Post Single Pages Full Width', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    $options['nikkon-blog-cats-blocks'] = array(
        'id' => 'nikkon-blog-cats-blocks',
        'label'   => __( 'Enable blocks on Archive pages', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    $section = 'nikkon-blog-section-post';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Single Posts', 'nikkon' ),
        'priority' => '50',
        'panel' => $panel
    );
    
    $choices = array(
        'nikkon-single-page-fimage-layout-none' => __( 'None', 'nikkon' ),
        'nikkon-single-page-fimage-layout-standard' => __( 'Standard', 'nikkon' ),
        'nikkon-single-page-fimage-layout-banner' => __( 'Page Banner', 'nikkon' )
    );
    $options['nikkon-single-page-fimage-layout'] = array(
        'id' => 'nikkon-single-page-fimage-layout',
        'label'   => __( 'Featured Image Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-single-page-fimage-layout-none'
    );
    $choices = array(
        'nikkon-single-page-fimage-size-extra-small' => __( 'Extra Small Banner', 'nikkon' ),
        'nikkon-single-page-fimage-size-small' => __( 'Small Banner', 'nikkon' ),
        'nikkon-single-page-fimage-size-medium' => __( 'Medium Banner', 'nikkon' ),
        'nikkon-single-page-fimage-size-large' => __( 'Large Banner', 'nikkon' ),
        'nikkon-single-page-fimage-size-actual' => __( 'Use Proper Image', 'nikkon' )
    );
    $options['nikkon-single-page-fimage-size'] = array(
        'id' => 'nikkon-single-page-fimage-size',
        'label'   => __( 'Page Banner Size', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-single-page-fimage-size-medium'
    );
    $options['nikkon-single-page-fimage-fullwidth'] = array(
        'id' => 'nikkon-single-page-fimage-fullwidth',
        'label'   => __( 'Full Width Banner', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
	
    // Blog Settings
    $section = 'nikkon-blocks-layout-section';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Blocks Layout Options', 'nikkon' ),
        'priority' => '50'
    );
    
    $choices = array(
        'blog-post-shape-square' => __( 'Square Blocks', 'nikkon' ),
        'blog-post-shape-img' => __( 'Image Shape Blocks', 'nikkon' )
    );
    $options['nikkon-blog-post-shape'] = array(
        'id' => 'nikkon-blog-post-shape',
        'label'   => __( 'Blog Post Shape', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'blog-post-shape-square'
    );
    $choices = array(
        'blog-columns-two' => __( '2', 'nikkon' ),
        'blog-columns-three' => __( '3', 'nikkon' ),
        'blog-columns-four' => __( '4', 'nikkon' ),
        'blog-columns-five' => __( '5', 'nikkon' )
    );
    $options['nikkon-blog-column-layout'] = array(
        'id' => 'nikkon-blog-column-layout',
        'label'   => __( 'Blog Columns', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'blog-columns-three'
    );
    $choices = array(
        'blog-style-imgblock' => __( 'Image/Block Style', 'nikkon' ),
        'blog-style-postblock' => __( 'Post/Block Style', 'nikkon' )
    );
    $options['nikkon-blog-blocks-style'] = array(
        'id' => 'nikkon-blog-blocks-style',
        'label'   => __( 'Blocks Styling', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'blog-style-postblock'
    );
    $options['nikkon-blog-blocks-remove-meta'] = array(
        'id' => 'nikkon-blog-blocks-remove-meta',
        'label'   => __( 'Remove Meta info', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-blocks-remove-content'] = array(
        'id' => 'nikkon-blog-blocks-remove-content',
        'label'   => __( 'Remove Content', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-blocks-remove-tagcats'] = array(
        'id' => 'nikkon-blog-blocks-remove-tagcats',
        'label'   => __( 'Remove Tags & Categories', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    $options['nikkon-blog-blocks-greyscale'] = array(
        'id' => 'nikkon-blog-blocks-greyscale',
        'label'   => __( 'Images Grey / Color on hover', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'description' => __( 'Select this if you\'d like the block images to show in greyscale and re-color on mouse hover', 'nikkon' ),
        'default' => 0,
    );
    
	
	// Footer Settings
    $section = 'nikkon-footer-section';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Footer Layout Options', 'nikkon' ),
        'priority' => '85'
    );
    
    $choices = array(
        'nikkon-footer-layout-standard' => __( 'Standard Layout', 'nikkon' ),
        'nikkon-footer-layout-social' => __( 'Social Layout', 'nikkon' ),
        'nikkon-footer-layout-custom' => __( 'Custom Layout', 'nikkon' ),
        'nikkon-footer-layout-none' => __( 'None', 'nikkon' )
    );
    $options['nikkon-footer-layout'] = array(
        'id' => 'nikkon-footer-layout',
        'label'   => __( 'Footer Layout', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-footer-layout-standard'
    );
    
    $options['nikkon-footer-bottombar'] = array(
        'id' => 'nikkon-footer-bottombar',
        'label'   => __( 'Remove the Bottom Bar', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'default' => 0,
    );
    
    $choices = array(
        'nikkon-footer-custom-cols-1' => __( '1 Column', 'nikkon' ),
        'nikkon-footer-custom-cols-2' => __( '2 Columns', 'nikkon' ),
        'nikkon-footer-custom-cols-3' => __( '3 Columns', 'nikkon' ),
        'nikkon-footer-custom-cols-4' => __( '4 Columns', 'nikkon' ),
        'nikkon-footer-custom-cols-5' => __( '5 Columns', 'nikkon' )
    );
    $options['nikkon-footer-custom-cols'] = array(
        'id' => 'nikkon-footer-custom-cols',
        'label'   => __( 'Columns', 'nikkon' ),
        'section' => $section,
        'type'    => 'select',
        'choices' => $choices,
        'default' => 'nikkon-footer-custom-cols-3'
    );
    
    $options['nikkon-footer-customize'] = array(
        'id' => 'nikkon-footer-customize',
        'label'   => __( 'Custom Widths', 'nikkon' ),
        'section' => $section,
        'type'    => 'checkbox',
        'description' => __( 'Select this box to manually adjust the columns widths by percentage ( % )', 'nikkon' ),
        'default' => 0,
    );
    
    $options['nikkon-footer-customize-col-1'] = array(
        'id' => 'nikkon-footer-customize-col-1',
        'label'   => __( 'Column 1 %', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( '', 'nikkon' ),
        'default' => '',
    );
    $options['nikkon-footer-customize-col-2'] = array(
        'id' => 'nikkon-footer-customize-col-2',
        'label'   => __( 'Column 2 %', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( '', 'nikkon' ),
        'default' => '',
    );
    $options['nikkon-footer-customize-col-3'] = array(
        'id' => 'nikkon-footer-customize-col-3',
        'label'   => __( 'Column 3 %', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( '', 'nikkon' ),
        'default' => '',
    );
    $options['nikkon-footer-customize-col-4'] = array(
        'id' => 'nikkon-footer-customize-col-4',
        'label'   => __( 'Column 4 %', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( '', 'nikkon' ),
        'default' => '',
    );
    $options['nikkon-footer-customize-col-5'] = array(
        'id' => 'nikkon-footer-customize-col-5',
        'label'   => __( 'Column 5 %', 'nikkon' ),
        'section' => $section,
        'type'    => 'number',
        'description' => __( '', 'nikkon' ),
        'default' => '',
    );
	
	
	// Site Text Settings
    $section = 'nikkon-website-section';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Website Text', 'nikkon' ),
        'priority' => '50'
    );
    
    $options['nikkon-website-site-add'] = array(
        'id' => 'nikkon-website-site-add',
        'label'   => __( 'Header Address', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'default' => __( 'Cape Town, South Africa', 'nikkon' ),
        'description' => __( 'This is the address in the social footer', 'nikkon' )
    );
    $options['nikkon-website-head-no'] = array(
        'id' => 'nikkon-website-head-no',
        'label'   => __( 'Header Phone Number', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'default' => __( 'Call Us: +2782 444 YEAH', 'nikkon' ),
        'description' => __( 'This is the phone number in the header top bar', 'nikkon' )
    );
    
    $options['nikkon-website-txt-copy'] = array(
        'id' => 'nikkon-website-txt-copy',
        'label'   => __( 'Site Copy Text', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'default' => __( 'Nikkon theme, by <a href="http://kairaweb.com">Kaira</a>', 'nikkon'),
        'description' => __( 'Enter the text in the bottom bar of the footer', 'nikkon' )
    );
    $options['nikkon-website-error-head'] = array(
        'id' => 'nikkon-website-error-head',
        'label'   => __( '404 Error Page Heading', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
        'default' => __( 'Oops! <span>404</span>', 'nikkon'),
        'description' => __( 'Enter the heading for the 404 Error page', 'nikkon' )
    );
    $options['nikkon-website-error-msg'] = array(
        'id' => 'nikkon-website-error-msg',
        'label'   => __( 'Error 404 Message', 'nikkon' ),
        'section' => $section,
        'type'    => 'textarea',
        'default' => __( 'It looks like that page does not exist. <br />Return home or try a search', 'nikkon'),
        'description' => __( 'Enter the default text on the 404 error page (Page not found)', 'nikkon' )
    );
    $options['nikkon-website-nosearch-msg'] = array(
        'id' => 'nikkon-website-nosearch-msg',
        'label'   => __( 'No Search Results', 'nikkon' ),
        'section' => $section,
        'type'    => 'textarea',
        'default' => __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'nikkon'),
        'description' => __( 'Enter the default text for when no search results are found', 'nikkon' )
    );
	
	
	// Social Settings
    $section = 'nikkon-social-section';

    $sections[] = array(
        'id' => $section,
        'title' => __( 'Social Links', 'nikkon' ),
        'priority' => '80'
    );
    
    $options['nikkon-social-email'] = array(
        'id' => 'nikkon-social-email',
        'label'   => __( 'Email Address', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-skype'] = array(
        'id' => 'nikkon-social-skype',
        'label'   => __( 'Skype Name', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-facebook'] = array(
        'id' => 'nikkon-social-facebook',
        'label'   => __( 'Facebook', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-twitter'] = array(
        'id' => 'nikkon-social-twitter',
        'label'   => __( 'Twitter', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-google-plus'] = array(
        'id' => 'nikkon-social-google-plus',
        'label'   => __( 'Google Plus', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-snapchat'] = array(
        'id' => 'nikkon-social-snapchat',
        'label'   => __( 'SnapChat', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-etsy'] = array(
        'id' => 'nikkon-social-etsy',
        'label'   => __( 'Etsy', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-youtube'] = array(
        'id' => 'nikkon-social-youtube',
        'label'   => __( 'YouTube', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-instagram'] = array(
        'id' => 'nikkon-social-instagram',
        'label'   => __( 'Instagram', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-pinterest'] = array(
        'id' => 'nikkon-social-pinterest',
        'label'   => __( 'Pinterest', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-medium'] = array(
        'id' => 'nikkon-social-medium',
        'label'   => __( 'Medium', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-behance'] = array(
        'id' => 'nikkon-social-behance',
        'label'   => __( 'Behance', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-product-hunt'] = array(
        'id' => 'nikkon-social-product-hunt',
        'label'   => __( 'Product Hunt', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-slack'] = array(
        'id' => 'nikkon-social-slack',
        'label'   => __( 'Slack', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-linkedin'] = array(
        'id' => 'nikkon-social-linkedin',
        'label'   => __( 'LinkedIn', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-tumblr'] = array(
        'id' => 'nikkon-social-tumblr',
        'label'   => __( 'Tumblr', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-flickr'] = array(
        'id' => 'nikkon-social-flickr',
        'label'   => __( 'Flickr', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-houzz'] = array(
        'id' => 'nikkon-social-houzz',
        'label'   => __( 'Houzz', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-vk'] = array(
        'id' => 'nikkon-social-vk',
        'label'   => __( 'VK', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-tripadvisor'] = array(
        'id' => 'nikkon-social-tripadvisor',
        'label'   => __( 'TripAdvisor', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
    $options['nikkon-social-github'] = array(
        'id' => 'nikkon-social-github',
        'label'   => __( 'GitHub', 'nikkon' ),
        'section' => $section,
        'type'    => 'text',
    );
	

	// Adds the sections to the $options array
	$options['sections'] = $sections;

	// Adds the panels to the $options array
	$options['panels'] = $panels;

	$customizer_library = Customizer_Library::Instance();
	$customizer_library->add_options( $options );

	// To delete custom mods use: customizer_library_remove_theme_mods();

}
add_action( 'init', 'customizer_library_nikkon_options' );
