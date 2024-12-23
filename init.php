<?php

/*
 * Plugin Name: Custom react module
 */

 function me_add_admin_menu() {

	add_menu_page(
		esc_html__( 'UT', 'react-settings-page' ),
		esc_html__( 'UI Test', 'react-settings-page' ),
		'manage_options',
		'react-settings-page-options',
		'me_display_menu_options'
	);

	global $screen_id_options;
	$screen_id_options = add_submenu_page(
		'react-settings-page-options',
		esc_html__( 'UT - Options', 'react-settings-page' ),
		esc_html__( 'Options', 'react-settings-page' ),
		'manage_options',
		'react-settings-page-options',
		'me_display_menu_options'
	);

}

add_action( 'admin_menu', 'me_add_admin_menu');

function me_display_menu_options() {
	include_once( 'options.php' );
}



function enqueue_admin_scripts(){

	global $screen_id_options;
	if ( $screen_id_options == $screen_id_options ) {

		$plugin_url  = plugin_dir_url( __FILE__ );

		wp_enqueue_script('react-settings-page-menu-options',
			$plugin_url . '/build/index.js',
			array('wp-element', 'wp-api-fetch', 'react-jsx-runtime'),
			'1.00',
			true);

	}

}

add_action( 'admin_enqueue_scripts', 'enqueue_admin_scripts' );




/*
 * Add custom routes to the Rest API
 */
function rest_api_register_route(){

	//Add the GET 'react-settings-page/v1/options' endpoint to the Rest API
	register_rest_route(
		'react-settings-page/v1', '/options', array(
			'methods'  => 'GET',
			'callback' => 'rest_api_react_settings_page_read_options_callback',
			'permission_callback' => '__return_true',
		)
	);
    register_rest_route(
        'react-settings-page/v1', '/options', array(
            'methods'             => 'POST',
            'callback'            => 'rest_api_react_settings_page_update_options_callback',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('init', 'rest_api_register_route');


 /*
 * Callback for the GET 'react-settings-page/v1/options' endpoint of the Rest API
 */
function rest_api_react_settings_page_read_options_callback( $data ) {

	//Check the capability
	if (!current_user_can('manage_options')) {
		return new WP_Error(
			'rest_read_error',
			'Sorry, you are not allowed to view the options.',
			array('status' => 403)
		);
	}

	//Generate the response
	$response = [];
	$response['plugin_option_1'] = get_option('plugin_option_1');
	$response['plugin_option_2'] = get_option('plugin_option_2');


	//Prepare the response
	$response = new WP_REST_Response($response);

	return $response;

}
function rest_api_react_settings_page_update_options_callback( $request ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error(
			'rest_update_error',
			'Sorry, you are not allowed to update the DAEXT UI Test options.',
			array( 'status' => 403 )
		);
	}

	//Get the data and sanitize
	//Note: In a real-world scenario, the sanitization function should be based on the option type.
	$plugin_option_1 = sanitize_text_field( $request->get_param( 'plugin_option_1' ) );
	$plugin_option_2 = sanitize_text_field( $request->get_param( 'plugin_option_2' ) );

	//Update the options
	update_option( 'plugin_option_1', $plugin_option_1 );
	update_option( 'plugin_option_2', $plugin_option_2 );

	$response = new WP_REST_Response( 'Data successfully added.', '200' );

	return $response;

}
