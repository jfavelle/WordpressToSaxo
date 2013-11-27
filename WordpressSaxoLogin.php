<?php

/*
Plugin Name: WordpressSaxoLogin
Description: Add custom fields to users for saxo login "contact" section
Version: 1.0
Author: Jesse Favelle
*/

$extra_fields =  array(
array( 'saxopassword', 'Saxo Password', true ));

add_filter( 'user_contactmethods', 'rc_add_user_custommethods');

function rc_add_user_custommethods ($user_contactmethods) {
    global $extra_fields; 
    
    $user_contactmethods['saxopassword'] = 'Saxo Password';

    return $user_contactmethods;
}
    
    function rc_register_form_display_extra_fields() {
     
    // Get fields
    global $extra_fields;
 
    // Display each field if 3th parameter set to "true"
    foreach( $extra_fields as $field ) {
        if( $field[2] == true ) {
            if( isset( $_POST[ $field[0] ] ) ) { $field_value = $_POST[ $field[0] ]; } else { $field_value = ''; }
     ?>
        <p>
            <label for="<?php echo $field[0]; ?>"><?php echo $field[1]; ?><br />
            <input type="text" name="<?php echo $field[0]; ?>" id="<?php echo $field[0]; ?>" class="input" value="<?php echo $field_value; ?>" size="20" /></label>
            </label>
        </p>
     <?php
        } // endif
    } // end foreach
}

function rc_user_register_save_extra_fields( $user_id, $password = '', $meta = array() )  {

    // Get fields
    global $extra_fields;
    
    $userdata       = array();
    $userdata['ID'] = $user_id;
    
    // Save each field
    foreach( $extra_fields as $field ) {
    	if( $field[2] == true ) { 
	    	$userdata[ $field[0] ] = $_POST[ $field[0] ];
	} // endif
    } // end foreach

    update_user_meta($user_id, 'saxo_password', $userdata[0]);
    $new_user_id = wp_update_user( $userdata );
}
   
// Add our fields to the registration process
add_action( 'register_form', 'rc_register_form_display_extra_fields' );
add_action( 'user_register', 'rc_user_register_save_extra_fields', 100 );

?>
