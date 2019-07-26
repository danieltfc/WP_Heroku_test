<?php
/**
 * Free Downloads - Database Update Functions
 * 
 * Various functions.
 * 
 * @version	3.1.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin database updating master function
 */
function somdn_update_plugin_db_master( $update_args = array() ) {

	if ( empty( $update_args ) ) {
		// Bail if no args passed with update information
		return;
	}

	$gen_options = get_option( 'somdn_gen_settings' );
	if ( empty( $gen_options ) ) {
		// No options have ever been set, we're definitely a fresh install of the plugin. No need to run update checks.
		return;
	}

	$_REQUEST['somdn_db_update_errors'] = array();

	$db_setting = isset( $update_args['db_setting'] ) ? esc_html( trim( $update_args['db_setting'] ) ) : '' ;
	$new_db_version = isset( $update_args['new_version'] ) ? esc_html( trim( $update_args['new_version'] ) ) : '' ;
	$old_db_version = ! empty( get_option( $db_setting ) ) ? esc_html( trim( get_option( $db_setting ) ) ) : '0.0.1' ;


	if ( empty( $db_setting ) || empty( $new_db_version ) ) {
		// Nothing set for the update args, just bail
		return;
	}

	// For good measure we create a new update_args array with cleaned variables
	$cleaned_args = array(
		'db_setting' => $db_setting,
		'new_version' => $new_db_version
	);

	if ( version_compare( $old_db_version, $new_db_version, '=' ) ) {
		// Version numbers are the same, just bail
		return;
	}

	// If we got here then we need to run some update functions

	// A filtered array of functions to run for updating depending on the current plugin version
	$updates = apply_filters( 'somdn_plugin_updates_array', array() );

	if ( empty( $updates ) ) {
		// No updates to do, just bail
		//echo '<p>No updates found</p>';
		return;
	}

	//echo '<p>old_db_version = ' . $old_db_version . '</p>';
	//echo '<p>new_db_version = ' . $new_db_version . '</p>';
	//$compare = version_compare( $old_db_version, $new_db_version, '<' );
	//echo '<p>version_compare = ' . $compare . '</p>';
	//exit;

	//somdn_debug_array($updates);

	// Now we run through each update index which contains a stack of version numbers and callback functions
	// For each of these stacks we check the version number and if it's lower than the current one we run the callback
	// function which will perform the necessary tasks to update the plugin.

	// Split out each stack of updates, which starts with 'default_updates' and then include ones returned from the filter 'somdn_plugin_updates_array'
	foreach ( $updates as $stack ) {

		//somdn_debug_array($stack);

		foreach ( $stack as $version => $update_callbacks ) {
			//echo '<p>Version ' . $version . '.</p>';
			if ( version_compare( $new_db_version, $version, '<' ) ) {
				// If for some reason the version number inside the update is greater than the current
				// plugin version, just skip to the next one in the list.
				continue;
			}
			if ( version_compare( $old_db_version, $version, '<' ) ) {
				//echo '<p>Update needed for version ' . $version . '.</p>';
				foreach ( $update_callbacks as $update_callback ) {
					if ( function_exists( $update_callback ) ) {

						// Call the function to process the update for each version
						$update_result = call_user_func( $update_callback, $cleaned_args );

						if ( is_array( $update_result ) ) {
							$new_error = array();
							// Something went wrong with the update, an error has been returned (array of values) instead of a true boolean
							$update_title = $update_result['title'];
							$update_error = $update_result['error'];
							$new_error['title'] = $update_title;
							$new_error['error'] = $update_error;
							array_push( $_REQUEST['somdn_db_update_errors'], $new_error);
						}

					}
				}
			}
		}

	}

	$update_errors = isset( $_REQUEST['somdn_db_update_errors'] ) ? $_REQUEST['somdn_db_update_errors'] : '' ;

	//somdn_debug_array($update_errors);

	//if ( ! empty( $update_errors ) ) {
	//	foreach( $update_errors as $error ) {
	//		echo '<p>title = ' . $error['title'] . '</p>';
	//		echo '<p>error = ' . $error['error'] . '</p>';
	//	}
	//}

	if ( ! empty( $update_errors ) ) {
		foreach( $update_errors as $error ) {
			somdn_write_log( '[UPDATE ERROR] ' . $error['title'] . ': ' . $error['error'] );
		}
	}

	if ( somdn_is_debug_on() ) {
		somdn_write_log( '[DEBUG] Plugin updated to version ' . $new_db_version . '.' );
	}

	// Updates completed, now save the new version number to the database
	delete_option( $db_setting );
	add_option( $db_setting, $new_db_version );

}

add_filter( 'somdn_plugin_updates_array', 'somdn_plugin_updates_array_default', 10, 1);
function somdn_plugin_updates_array_default( $updates ) {
	$default_updates = array(
		'default_updates' => array(
			'2.1.0' => array(
				'somdn_plugin_update_to_2_1_0'
			),
			'3.1.5' => array(
				'somdn_plugin_update_to_3_1_5'
			)
		)
	);
	$new_updates_array = array_merge( $updates, $default_updates );
	return $new_updates_array;
}

/*
Error template for update errors:
	$result = array(
		'title' => 'Update {version number} Error',
		'error' => 'A description of the error.'
	);
*/


function somdn_plugin_update_to_3_1_5( $update_args ) {

	$result = '';

	// Delete the old file download temp folder
	$upload_dir = wp_upload_dir();
	$old_zip_path = $upload_dir['basedir'] . '/download-now-uploads';
	if ( file_exists( $old_zip_path ) ) {
		array_map( 'unlink', glob( $upload_dir['basedir'] . '/download-now-uploads/*' ) );
		rmdir( $old_zip_path );
	}

	// No error to pass back for this update

	$result = true;

	return $result;

}