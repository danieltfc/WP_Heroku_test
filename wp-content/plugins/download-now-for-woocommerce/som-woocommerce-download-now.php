<?php
/**
 * Plugin Name: Free Downloads WooCommerce
 * Plugin URI: https://squareonemedia.co.uk
 * Description: Allow users to instantly download your free digital products without going through the checkout.
 * Version: 3.1.6
 * Author: Square One Media
 * Author URI: https://squareonemedia.co.uk
 * Requires at least: 4.4
 * Tested up to: 5.1
 * Requires PHP: 5.6
 *
 * Text Domain: download-now-for-woocommerce
 * Domain Path: /i18n/languages
 *
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.7
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
// Shared across versions
define( 'SOMDN_FILE', __FILE__ );
define( 'SOMDN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOMDN_PLUGIN_PATH', plugin_basename( dirname( SOMDN_FILE ) ) );
define( 'SOMDN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SOMDN_PLUGIN_VER', '3.1.5' );

// Base changeable
define( 'SOMDN_BASE', 'WooCommerce' );

// Version changeable
define( 'SOMDN_PLUGIN_NAME_FULL', 'Free Downloads WooCommerce' );
define( 'SOMDN_TEXTDOMAIN', 'download-now-for-woocommerce' );

// Basic Only
define( 'SOMDN_BASIC_FILE', __FILE__ );
// The setting name for the version number stored in the database, unique to this base plugin
define( 'SOMDN_DB_VER_SETTING', 'somdn_woo_plugin_db_version' );

register_activation_hook( SOMDN_BASIC_FILE, 'somdn_basic_activated' );
register_deactivation_hook( SOMDN_BASIC_FILE, 'somdn_basic_deactivated' );

// Deactivate the pro edition when basic edition is activated
function somdn_basic_activated() {
	if ( is_plugin_active( 'som-free-downloads-woocommerce-pro/free-downloads-woocommerce-pro.php' ) ) {
		deactivate_plugins( 'som-free-downloads-woocommerce-pro/free-downloads-woocommerce-pro.php' );
	}
	do_action( 'somdn_on_activate' );
	do_action( 'somdn_basic_activated' );
}

function somdn_basic_deactivated() {
	do_action( 'somdn_on_deactivate' );
	do_action( 'somdn_basic_deactivated' );
}

// Load dependency files (functions etc)
require_once( SOMDN_PATH . 'somdn-loader.php' );

/**
 * Basic function to run database updates for the plugin if needed.
 * This function is uniquely customised for each base plugin file due to the setting name differences
 */
add_action( 'plugins_loaded', 'somdn_update_plugin_db', 10 );
function somdn_update_plugin_db() {

	$args = array(
		'db_setting' => SOMDN_DB_VER_SETTING,
		'new_version' => SOMDN_PLUGIN_VER
	);

	// Only call the update function if it exists
	if ( function_exists( 'somdn_update_plugin_db_master' ) ) {
		somdn_update_plugin_db_master( $args );
	}

	//echo '<p>Updates Complete</p>';
	//exit;

}