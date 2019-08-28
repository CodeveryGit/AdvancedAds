<?php

class Advanced_Ads_Corner {

	/**
	 * holds plugin base class
	 *
	 * @var Advanced_Ads_Corner_Plugin
	 * @since 1.2.4
	 */
	protected $plugin;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $is_admin, $is_ajax ) {

		$this->plugin = Advanced_Ads_Corner_Plugin::get_instance();

		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded_ad_actions' ), 20 );

		if ( ! $is_admin ) {
			add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );
		}
	}

	/**
	 * load actions and filters needed only for ad rendering
	 * this will make sure options get loaded for ajax and non-ajax-calls
	 */
	public function wp_plugins_loaded_ad_actions() {
		// stop, if main plugin doesn’t exist
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
			return;
		}

		// add corner placement
		add_action( 'advanced-ads-placement-types', array( $this, 'add_corner_placement' ) );
		// add options to the wrapper
		add_filter( 'advanced-ads-set-wrapper', array( $this, 'corner_css' ), 22, 2 );
		// add wrapper options. Load after Sticky ad plugin
		add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper_options' ), 21, 2 );
		// add wrapper options, group
		add_filter( 'advanced-ads-output-wrapper-options-group', array( $this, 'add_wrapper_options_group' ), 10, 2 );
	}

	/**
	* load actions and filters
	*/
	public function wp_plugins_loaded() {
		// stop, if main plugin doesn’t exist
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
			return;
		}

		// append js file into footer
		add_action( 'wp_enqueue_scripts', array( $this, 'footer_scripts' ) );
		// frontend output
		add_action( 'wp_head', array( $this, 'header_output' ) );
		// inject ad content into footer
		add_action( 'wp_footer', array( $this, 'footer_injection' ), 10 );
	}

	/**
	 * add corner placement to list of placements (on placement page, but also for all AXAX calls)
	 *
	 * @since 1.2.4
	 * @param arr $types existing placements
	 * @return arr $types
	 */
	public function add_corner_placement( $types ) {

		// fixed header bar
		$types['corner'] = array(
			'title' => __( 'Corner Peel Ads', 'advanced-ads-corner' ),
			'description' => __( 'Create Corner Peel Ad', 'advanced-ads-corner' ),
			'image' => AACPDS_BASE_URL . 'admin/assets/img/corner.png',
		);

		return $types;
	}

	/**
	 * inject ad placement into footer
	 *
	 * @since 1.2.4
	 */
	public function footer_injection() {

		if( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			return;
		}
		$placements = get_option( 'advads-ads-placements', array() );
		if( is_array( $placements ) ){
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && in_array( $_placement['type'], array( 'corner' ) ) ) {
					echo Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT );
				}
			}
		}
	}

	/**
	 * add sticky attributes to wrapper
	 *
	 * @since 1.2.4
	 * @param arr $options
	 * @param obj $ad ad object
	 */
	public function add_wrapper_options( $options = array(), Advanced_Ads_Ad $ad ) {
		$top_level = ! isset( $ad->args['previous_method'] ) || 'placement' === $ad->args['previous_method'];
		if ( ! $top_level ) { return $options; }

		// new settings from the ad itself
		$width = ( isset( $ad->width ) ) ? $ad->width : 0;
		$height = ( isset( $ad->height ) ) ? $ad->height : 0;
		return $this->get_wrapper_options( $options, $ad->args, $width, $height );
	}


	/**
	 * Add sticky attributes to group wrapper.
	 *
	 * @since untagged
	 * @param arr $options Existing attributes.
	 * @param obj $group Advanced_Ads_Group.
	 */
	public function add_wrapper_options_group( $options = array(), Advanced_Ads_Group $group ) {
		$top_level = ! isset( $group->ad_args['previous_method'] ) || 'placement' === $group->ad_args['previous_method'];
		if ( ! $top_level ) { return $options; }

		$width = ! empty( $group->ad_args['placement_width'] ) ? absint( $group->ad_args['placement_width'] ) : 0;
		$height = ! empty( $group->ad_args['placement_height'] ) ? absint( $group->ad_args['placement_height'] ) : 0;
		$add_width = $group->type === 'slider' && $width;

		return $this->get_wrapper_options( $options, $group->ad_args, $width, $height, $add_width );
	}

	/**
	 * Get wrapper attributes.
	 *
	 * @since untagged
	 * @param arr $options Existing attributes.
	 * @param arr $args Arguments passed to ads.
	 * @param int $width Width of the wrapper.
	 * @param int $height Height of the wrapper.
	 * @return arr $options Modified attributes.
	 */
	private function get_wrapper_options( $options = array(), $args, $width, $height ) {

		if ( isset ( $args['placement_type'] ) && $args['placement_type'] == 'corner' ) {

		    $corner_class = $this->get_corner_class();
			$options['class'][] = $corner_class . ' corner-peel-'.$args['previous_id'].' corner-peel-transition';

			$full_width = isset($args['corner_placement']['full_width']) && $args['corner_placement']['full_width'] != '' ? $args['corner_placement']['full_width'] : $width;
			$full_height = isset($args['corner_placement']['full_height']) && $args['corner_placement']['full_height'] != '' ? $args['corner_placement']['full_height'] : $height;
			$options['data-width'][] = $full_width;
			$options['data-height'][] = $full_height;

			$options['class'][] = $corner_class . '-onload';

			if ( $args['corner_placement']['how_to_show'] == 'rectangle' ) $options['class'][] = 'advads-corner-show-in-rectangle';

			if ( $args['corner_placement']['close'] == 'opened' ) $options['class'][] = 'advads-corner-close-opened';
			elseif ($args['corner_placement']['close'] == 'clicked') $options['class'][] = 'advads-corner-close-clicked';
			else $options['class'][] = 'advads-corner-close-never';

			$is_assistant = ! empty( $args['corner_placement']['sticky']['assistant'] );
			if ( $is_assistant ) {
				$options['class'][] = 'is-sticky';
				$options['data-position'][] = $args['corner_placement']['sticky']['assistant'];
			}

			$options['style']['display'] = 'none';
			$options['style']['z-index'] = '9999';
			$options['style']['position'] = 'fixed';

			$top_pos = is_admin_bar_showing() ? '32px' : 0;

			switch ( $args['corner_placement']['sticky']['assistant'] ) {
				case 'topleft' :
					$options['style']['top'] = $top_pos;
					$options['style']['left'] = 0;
					break;
				case 'topright' :
					$options['style']['top'] = $top_pos;
					$options['style']['right'] = 0;
					break;
				case 'bottomleft' :
					$options['style']['bottom'] = 0;
					$options['style']['left'] = 0;
					break;
				case 'bottomright' :
					$options['style']['bottom'] = 0;
					$options['style']['right'] = 0;
					break;
			}

			$options['class'][] = 'corner-'.$args['corner_placement']['sticky']['assistant'];
		}

		return $options;
	}

	/**
	 * Add corner placement args to css
	 * 
	 * @param array $wrapper
	 * @param $ad
	 * @return arr
	 */
	public function corner_css($wrapper = array(), $ad) {

		$args = $ad->args;
		$start_width = isset($args['corner_placement']['start_width']) && $args['corner_placement']['start_width'] != '' ? $args['corner_placement']['start_width'] : 58;
		$start_height = isset($args['corner_placement']['start_height']) && $args['corner_placement']['start_height'] != '' ? $args['corner_placement']['start_height'] : 58;
		$full_width = isset($args['corner_placement']['full_width']) && $args['corner_placement']['full_width'] != '' ? $args['corner_placement']['full_width'] : $ad->width;
		$full_height = isset($args['corner_placement']['full_height']) && $args['corner_placement']['full_height'] != '' ? $args['corner_placement']['full_height'] : $ad->height;
		$placement_id = isset($args['previous_id']) && $args['previous_id'] != '' ? $args['previous_id'] : '';
		$peel_color = isset($args['corner_placement']['peel_color']) && $args['corner_placement']['peel_color'] != '' ? $args['corner_placement']['peel_color']: '#5d5d5d';
		$disable_when = isset($args['corner_placement']['disable_when']) && $args['corner_placement']['disable_when'] != '' ? $args['corner_placement']['disable_when']: 768;

		$style = '<style type="text/css">
					@media screen and (min-width: '.$disable_when.'px) {
					.corner-peel-'.$placement_id.' {
					  width: '.$start_width.'px;
					  height: '.$start_height.'px; }
					.corner-peel-'.$placement_id.'::before {
					  width: '.$start_width.'px;
				  	  height: '.$start_height.'px; }
					.corner-peel-'.$placement_id.':hover {
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
					.corner-peel-'.$placement_id.':hover::before {
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
				  	.corner-peel-'.$placement_id.'.corner-topright:before {
						background: linear-gradient(to bottom left, transparent 0%, transparent 50%, '.$peel_color.' 50%, '.$peel_color.' 100%); }
					.corner-peel-'.$placement_id.'.corner-topleft:before {
						background: linear-gradient(to bottom right, transparent 0%, transparent 50%, '.$peel_color.' 50%, '.$peel_color.' 100%); }
					.corner-peel-'.$placement_id.'.corner-bottomright:before {
						background: linear-gradient(to top left, transparent 0%, transparent 50%, '.$peel_color.' 50%, '.$peel_color.' 100%); }
					.corner-peel-'.$placement_id.'.corner-bottomleft:before {
						background: linear-gradient(to top right, transparent 0%, transparent 50%, '.$peel_color.' 50%, '.$peel_color.' 100%); }
					}
				  </style>';
		echo $style;
		return $wrapper;
	}

	/**
	 * append js file in footer
	 *
	 * @since 1.0.0
	 */
	public function footer_scripts() {

		if( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			return;
		}

		$deps = array( 'jquery' );

		if ( class_exists( 'Advanced_Ads_Pro' ) ) {
			$pro_options = Advanced_Ads_Pro::get_instance()->get_options();
			if ( ! empty( $pro_options['cache-busting']['enabled'] ) ) {
				$deps[] = 'advanced-ads-pro/cache_busting';
			}
		}

		wp_enqueue_style( 'corner-peel', AACPDS_BASE_URL . 'public/assets/css/corner-peel.css', array(), '1' );

		wp_enqueue_script( 'advanced-ads-corner-footer-js', AACPDS_BASE_URL . 'public/assets/js/corner.js', $deps, AACPDS_VERSION, true );
		wp_localize_script( 'advanced-ads-corner-footer-js', 'Advanced_Ads_Corner_settings', array(
		    'corner_class' => $this->get_corner_class()
		) );
	}

	/**
	 * content output in the header
	 */
	public function header_output() {
		// inject js array for banner conditions
		if( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			return;
		}
		echo '<script>advads_corner_items = { conditions: {}, showed: [] };</script>';
	}

	/**
	 * returns the (css) class name for corner ads
	 */
	public static final function get_corner_class(){
	    return Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . "corner";
	}
}
