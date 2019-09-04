<?php

class Advanced_Ads_Corner {

	/**
	 * holds plugin base class
	 *
	 * @var Advanced_Ads_Corner_Plugin
	 */
	protected $plugin;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
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
		// check if current placement can be displayed at all (after Sticky Ad plugin)
		add_filter( 'advanced-ads-can-display-placement', array( $this, 'placement_can_display' ), 11, 2 );
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

			$options['data-id'] = $args['previous_id'];
			$options['data-close_for'] = isset($args['corner_placement']['close']['for_how_long']) ? $args['corner_placement']['close']['for_how_long'] : '';

			$options['class'][] = $corner_class . '-onload';

			if ( $args['corner_placement']['how_to_show'] == 'rectangle' ) $options['class'][] = 'advads-corner-show-in-rectangle';

			if ( isset($args['corner_placement']['close']['when_to']) && $args['corner_placement']['close']['when_to'] == 'opened' ) $options['class'][] = 'advads-corner-close-opened';
			elseif ( isset($args['corner_placement']['close']['when_to']) && $args['corner_placement']['close']['when_to'] == 'clicked') $options['class'][] = 'advads-corner-close-clicked';
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
	 * @param arr $wrapper
	 * @param $ad
	 * @return arr
	 */
	public function corner_css($wrapper = array(), $ad) {

		if (!is_admin()) {

			$args = $ad->args;
			$start_width = isset($args['corner_placement']['start_width']) && $args['corner_placement']['start_width'] != '' ? $args['corner_placement']['start_width'] : 58;
			$start_height = isset($args['corner_placement']['start_height']) && $args['corner_placement']['start_height'] != '' ? $args['corner_placement']['start_height'] : 58;
			$full_width = isset($args['corner_placement']['full_width']) && $args['corner_placement']['full_width'] != '' && $args['corner_placement']['full_width'] != 0 ? $args['corner_placement']['full_width'] : $ad->width;
			$full_height = isset($args['corner_placement']['full_height']) && $args['corner_placement']['full_height'] != '' && $args['corner_placement']['full_height'] != 0 ? $args['corner_placement']['full_height'] : $ad->height;
			$placement_id = isset($args['previous_id']) && $args['previous_id'] != '' ? $args['previous_id'] : '';
			$peel_color = isset($args['corner_placement']['peel_color']) && $args['corner_placement']['peel_color'] != '' ? $args['corner_placement']['peel_color']: '#5d5d5d';
			$disable_when = isset($args['corner_placement']['disable_when']) && $args['corner_placement']['disable_when'] != '' ? $args['corner_placement']['disable_when']: 768;

			$style = '<style type="text/css">
					@media screen and (min-width: '.$disable_when.'px) {
					.corner-peel-'.$placement_id.', .corner-peel-'.$placement_id.' + .corner-shadow {
					  width: '.$start_width.'px;
					  height: '.$start_height.'px; }
					.corner-peel-'.$placement_id.'::before {
					  width: '.$start_width.'px;
				  	  height: '.$start_height.'px; }
					.corner-peel-'.$placement_id.':hover{
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
					.corner-peel-'.$placement_id.':hover + .corner-shadow {
					  width: '.($full_width + $full_width*0.05).'px;
					  height: '.($full_height + $full_height*0.05).'px; }
					.corner-peel-'.$placement_id.':hover::before {
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
				  	.corner-peel-'.$placement_id.'.corner-topright:before {
				  	  border-style: solid;
					  border-width: '.$start_height.'px 0 0 '.$start_width.'px;
					  border-color: transparent transparent transparent '.$peel_color.'; }
				  	.corner-peel-'.$placement_id.'.corner-topright:hover:before {
					  border-width: '.$full_height.'px 0 0 '.$full_width.'px; }
					.corner-peel-'.$placement_id.'.corner-topleft:before {
				  	  border-style: solid;
					  border-width: 0 0 '.$start_height.'px '.$start_width.'px;
					  border-color: transparent transparent '.$peel_color.' transparent; }
				  	.corner-peel-'.$placement_id.'.corner-topleft:hover:before {
					  border-width: 0 0 '.$full_height.'px '.$full_width.'px; }
					.corner-peel-'.$placement_id.'.corner-bottomright:before {
				  	  border-style: solid;
					  border-width: '.$start_height.'px '.$start_width.'px 0 0;
					  border-color: '.$peel_color.' transparent transparent transparent; }
				  	.corner-peel-'.$placement_id.'.corner-bottomright:hover:before {
					  border-width: '.$full_height.'px '.$full_width.'px 0 0; }
					.corner-peel-'.$placement_id.'.corner-bottomleft:before {
				  	  border-style: solid;
					  border-width: 0 '.$start_width.'px '.$start_height.'px 0;
					  border-color: transparent '.$peel_color.' transparent transparent; }
				  	.corner-peel-'.$placement_id.'.corner-bottomleft:hover:before {
					  border-width: 0 '.$full_width.'px '.$full_height.'px 0; }
					}
				  </style>';

			echo $style;
		}

		return $wrapper;
	}

	/**
	 * append js file in footer
	 *
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
		    'corner_class' => $this->get_corner_class(),
			'ajax_url' => admin_url( 'admin-ajax.php' )
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

	/**
	 * check if placement was closed with a cookie before
	 *
	 * @param int $id placement id
	 * @return bool whether placement can be displayed or not
	 * @return bool false if placement was closed for this user
	 */
	public function placement_can_display( $return, $id = 0 ) {
		// get all placements
		$placements = Advanced_Ads::get_ad_placements_array();

		if ( isset( $placements[ $id ]['options']['corner_placement']['close']['when_to'] ) && $placements[ $id ]['options']['corner_placement']['close']['when_to'] != 'never' ) {
			$slug = sanitize_title( $placements[ $id ]['name'] );
			if ( isset( $_COOKIE[ 'timeout_placement_' . $slug ] ) ) {
				return false;
			}
		}

		return $return;
	}
}
