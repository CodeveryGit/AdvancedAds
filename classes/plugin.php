<?php

/*
 * load common and WordPress based resources
 */

class Advanced_Ads_Corner_Plugin {

	/**
	 * @var Advanced_Ads_Corner_Plugin
	 */
	protected static $instance;

	/**
	 * plugin options
	 *
	 * @var     array (if loaded)
	 */
	protected $options = false;

	/**
	* name of options in db
	*
	* @car     string
	*/
	public $options_slug;

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );
	}

	/**
	 * @return Advanced_Ads_Corner_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
		self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * load actions and filters
	 *
	 * @todo include more of the hooks used in public and admin class
	 */
	public function wp_plugins_loaded() {
		// stop, if main plugin doesn’t exist
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
		    return ;
		}
		
		$this->load_plugin_textdomain();

		$this->options_slug =  ADVADS_SLUG . '-corner';

		// force advanced js to be activated
		add_filter( 'advanced-ads-activate-advanced-js', array( $this, 'force_advanced_js' ) );
	}
	
	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
	       load_plugin_textdomain( 'advanced-ads-corner', false, AACPDS_BASE_DIR . '/languages' );
	}

	/**
	* load advanced ads settings
	*/
	public function options() {
		// don’t initiate if main plugin not loaded
		if ( ! class_exists( 'Advanced_Ads' ) ) {
			return array();
		}

		return Advanced_Ads::get_instance()->options();
	}

	/**
	 * force advanced js file from base plugin to be implemented
	 *
	 * @param bool $is_activated whether or not the file is enqueued
	 * @return true to enqueue file
	 */
	public function force_advanced_js( $is_activated ){
		return true;
	}

}