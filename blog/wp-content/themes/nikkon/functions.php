<?php
/**
 * nikkon functions and definitions
 *
 * @package Nikkon
 */
define( 'NIKKON_THEME_VERSION' , '10.0.6' ); // PREMIUM ------------------------------------------ YEAH !!

// Include Nikkon Premium page
require get_template_directory() . '/premium/premium.php';
// Theme now uses our own update system
require get_template_directory() . '/update.php';

// Load WP included scripts
require get_template_directory() . '/includes/inc/template-tags.php';
require get_template_directory() . '/includes/inc/extras.php';
require get_template_directory() . '/includes/inc/jetpack.php';

// Load Customizer Library scripts
require get_template_directory() . '/customizer/customizer-options.php';
require get_template_directory() . '/customizer/customizer-library/customizer-library.php';
require get_template_directory() . '/customizer/styles.php';
require get_template_directory() . '/customizer/mods.php';

// Load TGM plugin class
require_once get_template_directory() . '/includes/inc/class-tgm-plugin-activation.php';

/*
 * Add the meta boxes to pages if selected in Customizer setting.
 */
if ( get_theme_mod( 'nikkon-pages-blocks-layout', false ) ) {
	require get_template_directory() . '/includes/inc/page-meta-box.php';
}

if ( ! function_exists( 'nikkon_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function nikkon_setup() {
	
	/**
	 * Set the content width based on the theme's design and stylesheet.
	 */
	global $content_width;
	if ( ! isset( $content_width ) ) {
		$content_width = 900; /* pixels */
	}

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on nikkon, use a find and replace
	 * to change 'nikkon' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'nikkon', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'nikkon_blog_img_side', 500, 380, true );
    add_image_size( 'nikkon_blog_img_top', 1200, 440, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
        'top-bar-menu' => esc_html__( 'Top Bar Menu', 'nikkon' ),
		'primary' => esc_html__( 'Main Menu', 'nikkon' ),
		'primary-left' => esc_html__( 'Split Header Left Menu', 'nikkon' ),
		'primary-right' => esc_html__( 'Split Header Right Menu', 'nikkon' ),
        'footer-bar' => esc_html__( 'Footer Bar Menu', 'nikkon' )
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'quote', 'link',
	) );
	
	// The custom logo
	add_theme_support( 'custom-logo', array(
		'width'       => 280,
		'height'      => 145,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'nikkon_custom_background_args', array(
		'default-color' => 'F9F9F9',
		'default-image' => '',
	) ) );
	
	add_theme_support( 'woocommerce' );
}
endif; // nikkon_setup
add_action( 'after_setup_theme', 'nikkon_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function nikkon_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'nikkon' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar(array(
		'name' => __( 'Nikkon Footer Standard', 'nikkon' ),
		'id' => 'nikkon-site-footer-standard',
        'description' => __( 'The footer will divide into however many widgets are placed here.', 'nikkon' )
	));
	
	register_sidebar( array(
		'name' => __( 'Nikkon Footer Custom 1', 'nikkon' ),
		'id' => 'nikkon-site-footer-custom-1',
        'description' => __( '', 'nikkon' )
	));
	register_sidebar( array(
		'name' => __( 'Nikkon Footer Custom 2', 'nikkon' ),
		'id' => 'nikkon-site-footer-custom-2',
        'description' => __( '', 'nikkon' )
	));
	register_sidebar( array(
		'name' => __( 'Nikkon Footer Custom 3', 'nikkon' ),
		'id' => 'nikkon-site-footer-custom-3',
        'description' => __( '', 'nikkon' )
	));
	register_sidebar( array(
		'name' => __( 'Nikkon Footer Custom 4', 'nikkon' ),
		'id' => 'nikkon-site-footer-custom-4',
        'description' => __( '', 'nikkon' )
	));
	register_sidebar( array(
		'name' => __( 'Nikkon Footer Custom 5', 'nikkon' ),
		'id' => 'nikkon-site-footer-custom-5',
        'description' => __( '', 'nikkon' )
	));
}
add_action( 'widgets_init', 'nikkon_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function nikkon_scripts() {
	wp_enqueue_style( 'nikkon-body-font-default', '//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic', array(), NIKKON_THEME_VERSION );
	wp_enqueue_style( 'nikkon-heading-font-default', '//fonts.googleapis.com/css?family=Dosis:400,300,500,600,700', array(), NIKKON_THEME_VERSION );
	
	wp_enqueue_style( 'font-awesome', get_template_directory_uri().'/includes/font-awesome/css/font-awesome.css', array(), '4.7.0' );
	wp_enqueue_style( 'nikkon-style', get_stylesheet_uri(), array(), NIKKON_THEME_VERSION );
	
	if ( nikkon_is_woocommerce_activated() ) :
		wp_enqueue_style( 'nikkon-woocommerce-style', get_template_directory_uri()."/includes/css/woocommerce.css", array(), NIKKON_THEME_VERSION );
	endif;
	
	wp_enqueue_script( 'caroufredsel-js', get_template_directory_uri() . "/js/caroufredsel/jquery.carouFredSel-6.2.1-packed.js", array('jquery'), NIKKON_THEME_VERSION, true );
	wp_enqueue_script( 'nikkon-custom-js', get_template_directory_uri() . "/js/custom.js", array('jquery'), NIKKON_THEME_VERSION, true );
	
	if ( get_theme_mod( 'nikkon-blog-layout', false ) == 'blog-blocks-layout' || get_theme_mod( 'nikkon-pages-blocks-layout', false ) == 1 || get_post_meta( get_the_ID(), 'nikkon-meta-box-checkbox-blocks', true ) ) :
		wp_enqueue_script( 'jquery-masonry' );
        wp_enqueue_script( 'nikkon-masonry-custom', get_template_directory_uri() . '/js/layout-blocks.js', array('jquery'), NIKKON_THEME_VERSION, true );
	endif;
	
	wp_enqueue_script( 'nikkon-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), NIKKON_THEME_VERSION, true );
	
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nikkon_scripts' );

/**
 * To maintain backwards compatibility with older versions of WordPress
 */
function nikkon_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}

/**
 * Add theme stying to the theme content editor
 */
function nikkon_add_editor_styles() {
    add_editor_style( 'style-theme-editor.css' );
}
add_action( 'admin_init', 'nikkon_add_editor_styles' );

/**
 * Enqueue admin styling.
 */
function nikkon_load_admin_script() {
	global $pagenow;
    
    wp_enqueue_style( 'nikkon-admin-css', get_template_directory_uri() . '/premium/css/admin-css.css' );
    // Only load if on themes.php page
    if ( $pagenow == 'themes.php' || $pagenow == 'widgets.php' ) {
    	wp_enqueue_script( 'nikkon-premium-js', get_template_directory_uri() . '/premium/js/upgrade-custom.js', array( 'jquery' ), NIKKON_THEME_VERSION, true );
    }
}
add_action( 'admin_enqueue_scripts', 'nikkon_load_admin_script' );

/**
 * Enqueue nikkon custom customizer styling.
 */
function nikkon_load_customizer_script() {
	wp_enqueue_script( 'nikkon-customizer-js', get_template_directory_uri() . "/customizer/customizer-library/js/customizer-custom.js", array('jquery'), NIKKON_THEME_VERSION, true );
    wp_enqueue_style( 'nikkon-customizer-css', get_template_directory_uri() . "/customizer/customizer-library/css/customizer.css" );
}
add_action( 'customize_controls_enqueue_scripts', 'nikkon_load_customizer_script' );

/**
 * Check if WooCommerce exists.
 */
if ( ! function_exists( 'nikkon_is_woocommerce_activated' ) ) :
	function nikkon_is_woocommerce_activated() {
	    if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
	}
endif; // nikkon_is_woocommerce_activated

// If WooCommerce exists include ajax cart
if ( nikkon_is_woocommerce_activated() ) {
	require get_template_directory() . '/includes/inc/woocommerce-header-inc.php';
}

/**
 * Add classed to the body tag from settings
 */
function nikkon_add_body_class( $classes ) {
	if ( get_theme_mod( 'nikkon-woocommerce-shop-fullwidth' ) ) {
		$classes[] = 'nikkon-shop-full-width';
	}
	if ( get_theme_mod( 'nikkon-woocommerce-shop-archive-fullwidth' ) ) {
		$classes[] = 'nikkon-shop-archives-full-width';
	}
	if ( get_theme_mod( 'nikkon-woocommerce-shop-single-fullwidth' ) ) {
		$classes[] = 'nikkon-shop-single-full-width';
	}
	
	if ( get_theme_mod( 'nikkon-page-remove-titlebar' ) ) {
		$classes[] = 'nikkon-shop-remove-titlebar';
	}
	
	return $classes;
}
add_filter( 'body_class', 'nikkon_add_body_class' );

/**
 * Add classes to the blog list for styling.
 */
function nikkon_add_blog_post_classes ( $classes ) {
	global $current_class;
	
	$nikkon_blog_layout = sanitize_html_class( 'blog-left-layout' );
	if ( get_theme_mod( 'nikkon-blog-layout' ) ) :
	    $nikkon_blog_layout = sanitize_html_class( get_theme_mod( 'nikkon-blog-layout' ) );
	    if ( is_archive() && get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' && !get_theme_mod( 'nikkon-blog-cats-blocks' ) ) :
	    	$nikkon_blog_layout = sanitize_html_class( 'blog-left-layout' );
	    endif;
	endif;
	$classes[] = $nikkon_blog_layout;
	
	$nikkon_blog_style = sanitize_html_class( 'blog-style-postblock' );
	if ( get_theme_mod( 'nikkon-blog-layout' ) == 'blog-blocks-layout' ) :
		if ( get_theme_mod( 'nikkon-blog-blocks-style' ) ) :
		    $nikkon_blog_style = sanitize_html_class( get_theme_mod( 'nikkon-blog-blocks-style' ) );
		endif;
	endif;
	$classes[] = $nikkon_blog_style;
	
	$nikkon_blog_greyscale = '';
	if ( get_theme_mod( 'nikkon-blog-blocks-greyscale' ) ) :
	    $nikkon_blog_greyscale = sanitize_html_class( 'blog-blocks-greyscale' );
	endif;
	$classes[] = $nikkon_blog_greyscale;
	
	$classes[] = $current_class;
	$current_class = ( $current_class == 'blog-alt-odd' ) ? sanitize_html_class( 'blog-alt-even' ) : sanitize_html_class( 'blog-alt-odd' );
	
	return $classes;
}
global $current_class;
$current_class = 'blog-alt-odd';
add_filter ( 'post_class' , 'nikkon_add_blog_post_classes' );

/**
 * Adjust is_home query if nikkon-blog-cats is set
 */
function nikkon_set_blog_queries( $query ) {
    $blog_query_set = '';
    if ( get_theme_mod( 'nikkon-blog-cats', false ) ) {
        $blog_query_set = get_theme_mod( 'nikkon-blog-cats' );
    }
    
    if ( $blog_query_set ) {
        // do not alter the query on wp-admin pages and only alter it if it's the main query
        if ( !is_admin() && $query->is_main_query() ){
            if ( is_home() ){
                $query->set( 'cat', $blog_query_set );
            }
        }
    }
}
add_action( 'pre_get_posts', 'nikkon_set_blog_queries' );

/**
 * Display recommended plugins with the TGM class
 */
function nikkon_register_required_plugins() {
	$plugins = array(
		// The recommended WordPress.org plugins.
		array(
			'name'      => 'Page Builder',
			'slug'      => 'siteorigin-panels',
			'required'  => false,
		),
		array(
			'name'      => 'WooCommerce',
			'slug'      => 'woocommerce',
			'required'  => false,
		),
		array(
			'name'      => 'Widgets Bundle',
			'slug'      => 'siteorigin-panels',
			'required'  => false,
		),
		array(
			'name'      => 'Contact Form 7',
			'slug'      => 'contact-form-7',
			'required'  => false,
		),
		array(
			'name'      => 'Breadcrumb NavXT',
			'slug'      => 'breadcrumb-navxt',
			'required'  => false,
		),
		array(
			'name'      => 'Meta Slider',
			'slug'      => 'ml-slider',
			'required'  => false,
		)
	);
	$config = array(
		'id'           => 'nikkon',
		'menu'         => 'tgmpa-install-plugins',
		'message'      => '',
	);

	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'nikkon_register_required_plugins' );

/**
 * Check if Meta Slider plugin is active then add Meta Slider hoplink if slider is enabled
 */
if ( ! function_exists( 'is_plugin_active' ) )
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
 
if ( is_plugin_active( 'ml-slider/ml-slider.php' ) ) {
    
    function nikkon_metaslider_hoplink( $link ) {
        return "https://getdpd.com/cart/hoplink/15318?referrer=9jtzbgs34v8k4c0gs";
    }
    add_filter('nikkon_metaslider_hoplink', 'nikkon_metaslider_hoplink', 10, 1);
    
}

/**
 * Add classes to the admin body class
 */
function nikkon_add_admin_body_class() {
	$nikkon_admin_class = '';
	
	if ( get_theme_mod( 'nikkon-footer-layout' ) == 'nikkon-footer-layout-custom' ) {
		if ( get_theme_mod( 'nikkon-footer-custom-cols' ) ) {
			$nikkon_admin_class = 'nikkon-footer-layout-custom ' . sanitize_html_class( get_theme_mod( 'nikkon-footer-custom-cols' ) );
		} else {
			$nikkon_admin_class = 'nikkon-footer-layout-custom nikkon-footer-custom-cols-3';
		}
	} elseif ( get_theme_mod( 'nikkon-footer-layout' ) ) {
		$nikkon_admin_class = sanitize_html_class( get_theme_mod( 'nikkon-footer-layout' ) );
	} else {
		$nikkon_admin_class = sanitize_html_class( 'nikkon-footer-layout-standard' );
	}
	
	return $nikkon_admin_class;
}
add_filter( 'admin_body_class', 'nikkon_add_admin_body_class' );

/**
 * Register a custom Post Categories ID column
 */
function nikkon_edit_cat_columns( $nikkon_cat_columns ) {
    $nikkon_cat_in = array( 'cat_id' => 'Category ID <span class="cat_id_note">For the Default Slider</span>' );
    $nikkon_cat_columns = nikkon_cat_columns_array_push_after( $nikkon_cat_columns, $nikkon_cat_in, 0 );
    return $nikkon_cat_columns;
}
add_filter( 'manage_edit-category_columns', 'nikkon_edit_cat_columns' );

/**
 * Print the ID column
 */
function nikkon_cat_custom_columns( $value, $name, $cat_id ) {
    if( 'cat_id' == $name ) 
        echo $cat_id;
}
add_filter( 'manage_category_custom_column', 'nikkon_cat_custom_columns', 10, 3 );

/**
 * Insert an element at the beggining of the array
 */
function nikkon_cat_columns_array_push_after( $src, $nikkon_cat_in, $pos ) {
    if ( is_int( $pos ) ) {
        $R = array_merge( array_slice( $src, 0, $pos + 1 ), $nikkon_cat_in, array_slice( $src, $pos + 1 ) );
    } else {
        foreach ( $src as $k => $v ) {
            $R[$k] = $v;
            if ( $k == $pos )
                $R = array_merge( $R, $nikkon_cat_in );
        }
    }
    return $R;
}
