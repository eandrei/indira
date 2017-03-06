<?php
/*
 *
 * Create a Custom Meta Box only on Pages
 *
 */
function nikkon_add_custom_page_meta_box() {
    add_meta_box( 'nikkon-page-meta-box', __( 'Page Blocks Options', 'nikkon' ), 'nikkon_create_custom_page_meta_box', 'page', 'side', 'high', null );
}
add_action( 'add_meta_boxes', 'nikkon_add_custom_page_meta_box' );

/*
 *
 * Function to create the Page meta box
 *
 */
function nikkon_create_custom_page_meta_box( $object ) {
    
    wp_nonce_field( basename( __FILE__ ), 'nikkon-page-meta-box-nonce' ); ?>
    
    <p>
        <label for="nikkon-meta-box-checkbox-blocks"><strong><?php _e( 'Enable Blocks Layout', 'nikkon' ); ?></strong></label>
    </p>
    <?php
    $checkbox_blocks_value = get_post_meta( $object->ID, 'nikkon-meta-box-checkbox-blocks', true );
    
    if ( $checkbox_blocks_value == '' ) { ?>
        <input name="nikkon-meta-box-checkbox-blocks" type="checkbox" value="true">
    <?php
    } else if ( $checkbox_blocks_value == "true" ) { ?>  
        <input name="nikkon-meta-box-checkbox-blocks" type="checkbox" value="true" checked>
    <?php
    } ?>
    
    <p>
        <label for="nikkon-meta-box-checkbox-title"><strong><?php _e( 'Remove Page Title', 'nikkon' ); ?></strong></label>
    </p>
    <?php
    $checkbox_title_value = get_post_meta( $object->ID, 'nikkon-meta-box-checkbox-title', true );
    
    if ( $checkbox_title_value == '' ) { ?>
        <input name="nikkon-meta-box-checkbox-title" type="checkbox" value="true">
    <?php
    } else if ( $checkbox_title_value == 'true' ) { ?>  
        <input name="nikkon-meta-box-checkbox-title" type="checkbox" value="true" checked>
    <?php
    } ?>
    
    <p>
        <label for="nikkon-meta-box-cats"><strong><?php _e( 'Post Categories', 'nikkon' ); ?></strong></label>
    </p>
    <input name="nikkon-meta-box-cats" type="text" placeholder="Eg: 13, 17, 19" value="<?php echo get_post_meta( $object->ID, 'nikkon-meta-box-cats', true ); ?>">
    <div style="font-size: 11px; font-style: italic;">
        <?php printf( __( 'Enter the ID\'s of the post categories you\'d like to list on this page, separated by a comma (,)<br />OR enter the ID\'s of the post categories you\'d like to EXCLUDE, but place a minus (-) before them.<br />If you leave this blank then it will show all post categories. <a href="%s" target="_blank">See full explanations here</a>', 'nikkon' ), esc_url( 'https://kairaweb.com/' ) ); ?>
    </div>
    
    <p>
        <label for="nikkon-meta-box-ppp"><strong><?php _e( 'Posts Per Page', 'nikkon' ); ?></strong></label>
    </p>
    <input name="nikkon-meta-box-ppp" type="text" placeholder="'-1' to show all posts" value="<?php echo get_post_meta( $object->ID, 'nikkon-meta-box-ppp', true ); ?>">
    <div style="font-size: 11px; font-style: italic;">
        <?php printf( __( 'Set the number of posts you want to show per page. If left empty it will <a href="%s" target="_blank">show the default</a>.', 'nikkon' ), admin_url( 'options-reading.php' ) ); ?>
    </div>
    
<?php
}

/*
 *
 * Saving the data for the page meta box
 *
 */
function nikkon_save_custom_page_meta_box( $post_id, $post, $update ) {
	
    if ( !isset( $_POST['nikkon-page-meta-box-nonce'] ) || !wp_verify_nonce( $_POST['nikkon-page-meta-box-nonce'], basename( __FILE__ ) ) )
        return $post_id;

    if ( !current_user_can( "edit_post", $post_id ) )
        return $post_id;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

    $slug = 'page';
    if ( $slug != $post->post_type )
        return $post_id;

    $meta_box_cats_value = '';
    $meta_box_ppp_value = '';
    $meta_box_blocks_checkbox_value = '';
    $meta_box_title_checkbox_value = '';

    if ( isset( $_POST['nikkon-meta-box-cats'] ) ) {
        $meta_box_cats_value = $_POST['nikkon-meta-box-cats'];
    }
    update_post_meta( $post_id, 'nikkon-meta-box-cats', $meta_box_cats_value );
    
    if ( isset( $_POST['nikkon-meta-box-ppp'] ) ) {
        $meta_box_ppp_value = $_POST['nikkon-meta-box-ppp'];
    }
    update_post_meta( $post_id, 'nikkon-meta-box-ppp', $meta_box_ppp_value );
    
    if( isset( $_POST['nikkon-meta-box-checkbox-blocks'] ) ) {
        $meta_box_blocks_checkbox_value = $_POST['nikkon-meta-box-checkbox-blocks'];
    }   
    update_post_meta( $post_id, 'nikkon-meta-box-checkbox-blocks', $meta_box_blocks_checkbox_value );
    
    if( isset( $_POST['nikkon-meta-box-checkbox-title'] ) ) {
        $meta_box_title_checkbox_value = $_POST['nikkon-meta-box-checkbox-title'];
    }   
    update_post_meta( $post_id, 'nikkon-meta-box-checkbox-title', $meta_box_title_checkbox_value );
}
add_action( 'save_post', 'nikkon_save_custom_page_meta_box', 10, 3 );
