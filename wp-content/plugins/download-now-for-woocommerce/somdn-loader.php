<?php
/**
 * Free Downloads - File Loader
 * 
 * @version	3.1.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build the requirements array
 *
 * @return array Required plugins with relevant info
 */
function somdn_plugin_requirements() {

	$requirements = array();

	if ( defined( 'SOMDN_BASE' ) ) {

		$base = constant( 'SOMDN_BASE' );

	/**
	 * Check for WooCommerce
	 */
	if ( $base === 'WooCommerce' ) :
		$requirements = array(
			$woocommerce_plugin = array(
				'type' => 'WordPress Plugin',
				'name' => 'WooCommerce',
				'url'  => 'https://en-gb.wordpress.org/plugins/woocommerce/',
				'path' => 'woocommerce/woocommerce.php'
			)
		);
	endif;

	/**
	 * Check for Easy Digital Downloads
	 */
	if ( $base === 'EDD' ) :
		$requirements = array(
			$woocommerce_plugin = array(
				'type' => 'WordPress Plugin',
				'name' => 'Easy Digital Downloads',
				'url'  => 'https://en-gb.wordpress.org/plugins/easy-digital-downloads/',
				'path' => 'easy-digital-downloads/easy-digital-downloads.php'
			)
		);
	endif;

	}

	return apply_filters( 'somdn_plugin_requirements', $requirements );

}

/**
 * Checks if the plugin requirements are met
 *
 * @return bool True if plugin requirements are met, false if not
 */
function somdn_plugin_is_required_active() {

	$active = true;

	$requirements = somdn_plugin_requirements();
	
	if ( empty( $requirements ) ) {
		return $active;
	}

	// Require 'plugin.php' to get access to is_plugin_active() early
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

	foreach ( $requirements as $requirement ) {

		if ( $requirement['type'] === 'WordPress Plugin' ) {

			if ( ! is_plugin_active( $requirement['path'] ) ) {
				$active = false;
			}

		} else {
			// No support for non-WordPress plugins yet, default to true
			$active = true;
		}

	}
		
	return $active;
}

/**
 * Add a notice to the WP dashboard advising of any missing requirements
 */
add_action( 'admin_notices', 'somdn_plugin_no_req_plugins_notice' );
function somdn_plugin_no_req_plugins_notice(){
 
	if( ! somdn_plugin_is_required_active() ) {
		?>
		<div class="notice notice-error">
			<p><strong><?php echo SOMDN_PLUGIN_NAME_FULL; ?></strong> will not work without all of the following plugins:</p>
			
			<?php
			
			$requirements = somdn_plugin_requirements();

			if ( ! empty( $requirements ) ) {
				foreach ( $requirements as $requirement ) {

					printf( '<p><a rel="nofollow" href="%2$s" target="_blank">%1$s</a></p>', $requirement['name'], $requirement['url']);

				}
			}
			
			?>		

		</div>
		<?php
	}
}

/**
 * If plugin dependencies are active, load in the required plugin files
 */
if ( somdn_plugin_is_required_active() ) {

	require_once( SOMDN_PATH . 'includes/somdn-functions.php' );
	require_once( SOMDN_PATH . 'includes/somdn-file-functions.php' );
	require_once( SOMDN_PATH . 'includes/somdn-downloader.php' );
	require_once( SOMDN_PATH . 'includes/somdn-download-page.php' );
	require_once( SOMDN_PATH . 'includes/somdn-plugin-settings.php' );
	require_once( SOMDN_PATH . 'includes/somdn-compatibility.php' );
	require_once( SOMDN_PATH . 'includes/somdn-meta.php' );
	require_once( SOMDN_PATH . 'includes/somdn-doc-viewer-functions.php' );
	require_once( SOMDN_PATH . 'includes/somdn-shortcodes.php' );

	require_once( SOMDN_PATH . 'somdn-base-loader.php' );

	$pro_loader = SOMDN_PATH . 'pro/somdn-pro-loader.php';
	if ( file_exists( $pro_loader ) ) require_once( $pro_loader );

	// Load the update file to update the database where needed
	require_once( SOMDN_PATH . 'includes/somdn-updates-master.php' );

	do_action( 'somdn_after_file_loader' );

}