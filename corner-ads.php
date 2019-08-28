<?php
/**
 * Advanced Ads – Corner Peel Ads
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Ads – Corner Peel Ads
 * Plugin URI:        https://wpadvancedads.com/add-ons/corner-peel-ads/
 * Description:       Create Corner Peel Ads
 * Version:           1.6.4
 * Author:
 * Author URI:
 * Text Domain:       advanced-ads-corner
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('Advanced_Ads_Corner')  ) {

	// load basic path and url to the plugin
	define( 'AACPDS_BASE_PATH', plugin_dir_path(__FILE__) );
	define( 'AACPDS_BASE_URL', plugin_dir_url(__FILE__) );
	define( 'AACPDS_BASE_DIR', dirname( plugin_basename( __FILE__ ) ) ); // directory of the plugin without any paths
	define( 'AACPDS_SLUG', 'advanced-ads-corner');

	define( 'AACPDS_VERSION', '1.0' );
	define( 'AACPDS_PLUGIN_URL', 'https://wpadvancedads.com' );
	define( 'AACPDS_PLUGIN_NAME', 'Corner Peel Ads' );

	include_once( plugin_dir_path( __FILE__ ) . 'classes/plugin.php' );

	/*----------------------------------------------------------------------------*
	 * Public-Facing Functionality
	 *----------------------------------------------------------------------------*/
	require_once( plugin_dir_path( __FILE__ ) . 'public/public.php' );

	$is_admin = is_admin();
	$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	$corner_ads = new Advanced_Ads_Corner( $is_admin, $is_ajax );


	/*----------------------------------------------------------------------------*
	 * Dashboard and Administrative Functionality
	 *----------------------------------------------------------------------------*/
	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	    require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );
	    $corner_ads_admin = new Advanced_Ads_Corner_Admin();
	}
}