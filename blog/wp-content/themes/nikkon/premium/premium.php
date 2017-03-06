<?php
/**
 * Functions for users wanting to upgrade to premium
 *
 * @package Nikkon
 */

/**
 * Display the upgrade to Premium page & load styles.
 */
function nikkon_premium_admin_menu() {
    global $nikkon_upgrade_page;
    $nikkon_upgrade_page = add_theme_page( __( 'About Nikkon', 'nikkon' ), '<span class="premium-link">' . __( 'About Nikkon', 'nikkon' ) . '</span>', 'edit_theme_options', 'theme_info', 'nikkon_render_upgrade_page' );
}
add_action( 'admin_menu', 'nikkon_premium_admin_menu' );

/**
 * Enqueue admin stylesheet only on upgrade page.
 */
function nikkon_load_upgrade_page_scripts( $hook ) {
    global $nikkon_upgrade_page;
    if ( $hook != $nikkon_upgrade_page )
        return;
    
    wp_enqueue_style( 'nikkon-premium-css', get_template_directory_uri() . '/premium/css/upgrade-admin.css' );
}
add_action( 'admin_enqueue_scripts', 'nikkon_load_upgrade_page_scripts' );

/**
 * Render the premium upgraded page
 */
function nikkon_render_upgrade_page() {
	get_template_part( 'premium/tpl/premium-page' );
}

/**
 * Add Premium Name and Order Number on WP Dashboard (Home)
 */
function nikkon_premium_dashboard_note() {
	$theme = basename( get_template_directory() ); // = nikkon
	$option_name = $theme . '_user_order_number';
	$order_number = get_theme_mod( $option_name );
	
	if ( !empty( $order_number ) ) {
    	echo '<div class="premium-upgrade-info"><strong>' . ucfirst ( $theme ) . ' Premium</strong> - Order Number: <strong>' . $order_number . '</strong></div>';
	}
}
add_filter( 'rightnow_end', 'nikkon_premium_dashboard_note' );
