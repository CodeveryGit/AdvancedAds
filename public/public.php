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
			return ;
		}

		// add corner placement
		add_action( 'advanced-ads-placement-types', array( $this, 'add_corner_placement' ) );
		// add options to the wrapper
		add_filter( 'advanced-ads-set-wrapper', array( $this, 'set_wrapper' ), 21, 2 );
		add_filter( 'advanced-ads-set-wrapper', array( $this, 'corner_css' ), 22, 2 );
		// add wrapper options. Load after Sticky ad plugin
		add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper_options' ), 21, 2 );
		// add wrapper options, group
		add_filter( 'advanced-ads-output-wrapper-options-group', array( $this, 'add_wrapper_options_group' ), 10, 2 );
		// action after ad output is created; used for js injection
		add_filter( 'advanced-ads-ad-output', array( $this, 'add_content_after' ), 20, 2 );
		// Action after group output is created; used for js injection.
		add_filter( 'advanced-ads-group-output', array( $this, 'add_content_after_group' ), 10, 2 );
		// Action after group output (passive cache-busting) is created; used for js injection.
		add_filter( 'advanced-ads-pro-passive-cb-group-data', array( $this, 'after_group_output_passive' ), 11, 2 );

		// // add button to wrapper content
		add_filter( 'advanced-ads-output-wrapper-before-content', array( $this, 'add_button' ), 20, 2 );
		add_filter( 'advanced-ads-output-wrapper-after-content-group', array( $this, 'add_button_group' ), 20, 2 );

		// // add close js to wrapper content
		// check if current placement can be displayed at all (after Sticky Ad plugin)
		add_filter( 'advanced-ads-can-display-placement', array( $this, 'placement_can_display' ), 11, 2 );
		// check if current ad can be displayed at all
		add_filter( 'advanced-ads-can-display', array( $this, 'can_display' ), 11, 2 );
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

		if ( ! $width) {
			// obsolete settings from corner placement
			$width = ( ! empty( $args['corner_placement']['sticky']['position']['width'] ) ) ? absint( $args['corner_placement']['sticky']['position']['width']) : 0;
		}
		if ( ! $height) {
			// obsolete settings from corner placement
			$height = ( ! empty( $args['corner_placement']['sticky']['position']['height'] ) ) ? absint( $args['corner_placement']['sticky']['position']['height']) : 0;
		}


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
	 * @param bool $add_width Whether to add width to the wrapper.
	 * @return arr $options Modified attributes.
	 */
	private function get_wrapper_options( $options = array(), $args, $width, $height, $add_width = false ) {
		if ( isset ( $args['placement_type'] ) && $args['placement_type'] == 'corner' ) {

		    $corner_class = $this->get_corner_class();
			$options['class'][] = $corner_class;
			$options['class'][] = 'corner-peel-'.$args['previous_id'].' corner-peel-transition';

			$options['data-width'][] = $width;
			$options['data-height'][] = $height;

			if ( ! empty( $args['corner_placement']['effect'] ) && ! empty( $args['corner_placement']['duration'] ) ) {
				$options['class'][] = 'advads-effect';
				$options['class'][] = 'advads-effect-' . $args['corner_placement']['effect'];
				$options['class'][] = 'advads-duration-' . absint( $args['corner_placement']['duration'] );
			}

			if ( isset( $args['corner_placement']['trigger'] ) ) {
				// add trigger options depending on trigger
				switch ( $args['corner_placement']['trigger'] ) {
					case '' :
						$options['class'][] = $corner_class . '-onload';
					break;
					case 'stop' :
						$options['class'][] = $corner_class . '-stop';
					break;
					case 'half' :
						$options['class'][] = $corner_class . '-half';
					break;
					case 'custom' :
						$options['class'][] = $corner_class . '-offset';
						if ( isset( $args['corner_placement']['offset'] ) && $args['corner_placement']['offset'] > 0 ) {
							$options['class'][] = $corner_class . '-offset-' . absint( $args['corner_placement']['offset'] );
						}
					break;
					case 'exit' :
						$options['class'][] = $corner_class . '-exit';
					break;
					case 'delay' :
						$options['class'][] = $corner_class . '-delay';
						$options['data-advads-corner-delay'][] = isset( $args['corner_placement']['delay_sec'] ) ? absint( $args['corner_placement']['delay_sec'] ) * 1000 : 0;
					break;
				}
			} else {
				$options['class'][] = $corner_class . '-onload';
			}

			// set background arguments (in form of a class)
			if ( ! empty( $args['corner_placement']['background'] ) ) {
				$options['class'][] = 'advads-has-background';
				if ( ! empty( $args['corner_placement']['background_click_close'] ) ) {
					$options['class'][] = 'advads-background-click-close';
				}
			}


			if ( isset( $args['corner_placement']['close']['enabled'] ) && $args['corner_placement']['close']['enabled'] ) {
				$options['class'][] = 'advads-close';
			}

			if ( ! empty( $args['corner_placement']['auto_close']['trigger'] ) ) {
				$auto_close_delay = isset( $args['corner_placement']['auto_close']['delay'] ) ? absint ( $args['corner_placement']['auto_close']['delay'] ) * 1000 : 0;
				if ( $auto_close_delay )  {
					$options['data-auto-close-delay'] = $auto_close_delay;
				}
			}

			$is_assistant = ! empty( $args['corner_placement']['sticky']['assistant'] );
			if ( $is_assistant ) {
				$options['class'][] = 'is-sticky';
				$options['data-position'][] = $args['corner_placement']['sticky']['assistant'];
			}

			$options['style']['display'] = 'none';

			if ( $add_width ) {
				$options['style']['width'] = $width . 'px';
			}

			$options['style']['z-index'] = '9999';
			$options['style']['position'] = 'fixed';

			$top_pos = is_admin_bar_showing() ? '32px' : 0;

			if ( $is_assistant ) {
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
			} else {
				$options['style']['margin-left'] = '-' . $width / 2 . 'px';
				$options['style']['margin-bottom'] = '-' . $height / 2 . 'px';
				$options['style']['bottom'] = '50%';
				$options['style']['left'] = '50%';
			}


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
		$start_width = $args['start_width'];
		$start_height = $args['start_height'];
		$full_width = $ad->width;
		$full_height = $ad->height;

		$placement_id = $args['previous_id'];
		$cover_background = $args['cover_background'] ? 'url("'.$args['cover_background'] .'")': 'transparent';

		$style = '<style type="text/css">
					.corner-peel-'.$placement_id.' {
					  width: '.$start_width.'px;
					  height: '.$start_height.'px; }
					.corner-peel-'.$placement_id.'::before {
					  width: '.$start_width.'px;
				  	  height: '.$start_height.'px;
					  background: '.$cover_background.'; }
					.corner-peel-'.$placement_id.':hover {
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
					.corner-peel-'.$placement_id.':hover::before {
					  width: '.$full_width.'px;
				  	  height: '.$full_height.'px; }
				  </style>';
		echo $style;
		return $this->add_css_to_wrapper( $wrapper, $ad );
	}

	/**
	 * append js file in footer
	 *
	 * @since 1.0.0
	 */
	public function footer_scripts() {

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
		echo '<script>advads_corner_items = { conditions: {}, display_callbacks: {}, display_effect_callbacks: {}, hide_callbacks: {}, backgrounds: {}, effect_durations: {}, close_functions: {}, showed: [] };</script>';
		echo '<style type="text/css" id="' . self::get_corner_class() . '-custom-css"></style>';
	}

	/**
	 * set the ad wrapper options
	 *
	 * @since 1.0.0
	 * @param arr $wrapper wrapper options
	 * @param obj $ad ad object
	 * @return \arr
	 */
	public function set_wrapper( $wrapper = array(), $ad ) {
		return $this->add_css_to_wrapper( $wrapper, $ad );
	}

	/**
	 * set the ad wrapper options
	 *
	 * @since 1.2.4
	 * @param arr $wrapper wrapper options
	 * @param obj $ad ad object
	 * @return arr $wrapper with css classes/styles
	 * @deprecated since 1.3 (Oct 13 2015)
	 */
	public function add_css_to_wrapper( $wrapper = array(), $ad ) {
		$options = $ad->options();

		// define basic corner options
		if ( isset( $options['corner']['enabled'] ) && $options['corner']['enabled'] ) {
		    $corner_class = $this->get_corner_class();
			$wrapper['class'][] = $corner_class;
			$wrapper['style']['display'] = 'none';
			$wrapper['style']['z-index'] = '9999';

			$width = ( isset( $ad->width ) ) ? $ad->width : 0;
			$height = ( isset( $ad->height ) ) ? $ad->height : 0;
			$wrapper['data-width'][] = $width;
			$wrapper['data-height'][] = $height;

			if ( ! empty( $options['corner']['effect'] ) && ! empty( $options['corner']['duration'] ) ) {
				$wrapper['class'][] = 'advads-effect';
				$wrapper['class'][] = 'advads-effect-' . $options['corner']['effect'];

				if ( ! empty( $options['corner']['duration'] ) ) {
					$wrapper['class'][] = 'advads-duration-' . absint( $options['corner']['duration'] );
				}
			}

			// center the ad if position is not set by sticky plugin
			if ( empty( $options['sticky']['enabled'] ) || empty( $options['sticky']['type'] ) ) {
				$wrapper['style']['position'] = 'fixed';
				$wrapper['style']['margin-left'] = '-' . $width / 2 . 'px';
				$wrapper['style']['margin-bottom'] = '-' . $height / 2 . 'px';
				$wrapper['style']['bottom'] = '50%';
				$wrapper['style']['left'] = '50%';
			}

			// add trigger options depending on trigger
			switch( $options['corner']['trigger'] ) {
				case '' :
					$wrapper['class'][] = $corner_class . '-onload';
				break;
				case 'stop' :
					$wrapper['class'][] = $corner_class . '-stop';
				break;
				case 'half' :
					$wrapper['class'][] = $corner_class . '-half';
				break;
				case 'custom' :
					$wrapper['class'][] = $corner_class . '-offset';
					if ( isset( $options['corner']['offset'] ) && $options['corner']['offset'] > 0 ) {
						$wrapper['class'][] = $corner_class . '-offset-' . absint( $options['corner']['offset'] );
					}
				break;
				case 'exit' :
					$wrapper['class'][] = $corner_class . '-exit';
				break;
			}
			// set background arguments (in form of a class)
			if ( ! empty( $options['corner']['background'] ) ) {
				$wrapper['class'][] = 'advads-has-background';
			}
		}
		// set close button options
		if ( isset( $options['corner']['close']['enabled'] ) && $options['corner']['close']['enabled'] ) {
			$wrapper['class'][] = 'advads-close';
		}

		return $wrapper;
	}

	/**
	 * add the close button to the wrapper
	 * @since 1.0.0
	 * @param string $content additional content added
	 * @param obj $ad ad object
	 * @return string
	 */
	public function add_button( $content = '', $ad = '' ) {
		$options = $ad->options();
		$top_level = ! isset( $options['previous_method'] ) || 'placement' === $options['previous_method'];

		// for button, enabled in corner placement
		if ( isset( $options['corner_placement']['close']['enabled'] ) && $options['corner_placement']['close']['enabled']
			&& $top_level
		) {
			// build close button
			$content .= $this->build_close_button( $options['corner_placement']['close'] );
		}
		// for button, enabled in ad settings
		else if ( isset( $options['corner']['close']['enabled'] ) && $options['corner']['close']['enabled'] ) {
			// build close button
			$content .= $this->build_close_button( $options['corner']['close'] );
		}

		return $content;
	}


	/**
	 * add the close button to the group wrapper
	 *
	 * @param string $content additional content added
	 * @param Advanced_Ads_Group|obj $group Advanced_Ads_Group
	 * @return string
	 */
	public function add_button_group( $content = '', Advanced_Ads_Group $group ) {
		$top_level = ! isset( $group->ad_args['previous_method'] ) || 'placement' === $group->ad_args['previous_method'];

		// for button, enabled in corner placement
		if ( ! empty( $group->ad_args['corner_placement']['close']['enabled'] ) && $top_level ) {
			// build close button
			$content .= $this->build_close_button( $group->ad_args['corner_placement']['close'] );
		}

		return $content;
	}

	/**
	 * build the close button
	 *
	 * @since 1.0.0
	 * @param arr $options original [close] part of the ad options array
	 * @return string
	 */
	public function build_close_button( $options ) {
		$closebutton = '';
		if ( ! empty( $options['where'] ) && ! empty( $options['side'] ) ) {
			switch( $options['where'] ) {
				case 'inside' :
					$offset = '0';
				break;
				default : $offset = '-15px';
			}
			switch( $options['side'] ) {
				case 'left' :
					$side = 'left';
				break;
				default : $side = 'right';
			}
			$closebutton = '<span class="advads-close-button" title="' . __( 'close', 'advanced-ads-corner' )
					.'" style="width: 15px; height: 15px; background: #fff; position: absolute; top: 0; line-height: 15px; text-align: center; cursor: pointer; ' . $side. ':' . $offset .'">×</span>';
		}

		return $closebutton;
	}

	/**
	 * add content after the ad wrapper
	 *
	 * @since 1.0.0
	 * @param string $content Existing content.
	 * @param obj $ad ad object
	 * @return string
	 */
	public function add_content_after( $content = '', Advanced_Ads_Ad $ad ) {
		if ( ! isset( $ad->wrapper['id'] ) ) { return $content; }

		$options = $ad->options();
		$content .= $this->close_script( $ad->wrapper['id'], $options, $ad->id );

		return $content;
	}

	/**
	 * Add content after the group wrapper.
	 *
	 * @since untagged
	 * @param str $content Existing content.
	 * @param obj $group Advanced_Ads_Group.
	 * @return str $content Modified content.
	 */
	public function add_content_after_group( $content = '', Advanced_Ads_Group $group ) {
		if ( isset( $group->wrapper['id'] ) ) {
			$content .= $this->close_script( $group->wrapper['id'], $group->ad_args );
		}

	    return $content;
	}

	/**
	 * Add content after the group wrapper (passive cache-busting).
	 *
	 * @since untagged
	 * @param arr $group_data Data to inject after the group.
	 * @param obj $group Advanced_Ads_Group.
	 * @return arr $group_data Modified data to inject after the group.
	 */
	public function after_group_output_passive( $group_data, Advanced_Ads_Group $group ) {
		if ( isset( $group->wrapper['id'] ) ) {

			$close_script = $this->close_script( $group->wrapper['id'], $group->ad_args );

			if ( $close_script) {
				$group_data['group_wrap'][] = array( 'after' => $close_script );
			}
		}

		return $group_data;
	}


	/**
	 * add the javascript for close and timeout feature
	 *
	 * @since 1.2.4
	 * @param str $wrapper_id Id of the wrapper.
	 * @param arr $options Arguments passed to ads.
	 * @param int $ad_id Id of the ad.
	 * @return string
	 */
	public function close_script( $wrapper_id, $options, $ad_id = '' ) {

		// close button enabled for corner placement
		$content = '';
		$top_level = ! isset( $options['previous_method'] ) || 'placement' === $options['previous_method'];
		$is_corner_placement = isset ( $options['placement_type'] ) && $options['placement_type'] === 'corner';

		if ( $top_level && $is_corner_placement ) {
			$close_button_enabled = isset( $options['corner_placement']['close']['enabled'] ) && $options['corner_placement']['close']['enabled'];
			$set_cookie_string = '';
			// check if value exists; also 0 works, since it sets the cookie for the current session
			if ( isset( $options['corner_placement']['close']['timeout_enabled'] ) ) {
				$timeout = isset( $options['corner_placement']['close']['timeout'] ) ? absint( $options['corner_placement']['close']['timeout'] ) : 0;
				if ( ! $timeout ) {
					// Session cookie;
					$timeout = 'null';
				}

				$set_cookie_string .= 'advads.set_cookie("timeout_placement_' . $options['output']['placement_id'] . '", 1, '. $timeout .'); ';
			}

			$content .= $this->build_close_ad_js( $set_cookie_string, $wrapper_id, $close_button_enabled );
		}
		// close button for ad itself
		else if ( isset( $options['corner']['close']['enabled'] ) && $options['corner']['close']['enabled'] && $ad_id ) {
			$close_button_enabled = isset( $options['corner_placement']['close']['enabled'] ) && $options['corner_placement']['close']['enabled'];
			$set_cookie_string = '';
			if ( isset( $options['corner']['close']['timeout_enabled'] ) ) {
				$timeout = isset( $options['corner']['close']['timeout'] ) ? absint( $options['corner']['close']['timeout'] ) : 0;
				if ( ! $timeout ) {
					// Session cookie;
					$timeout = 'null';
				}
				$set_cookie_string .= 'advads.set_cookie("timeout_' . $ad_id . '", 1, '. $timeout .'); ';
			}

			$content .= $this->build_close_ad_js( $set_cookie_string, $wrapper_id, true );
		}

		return $content;
	}

	/**
	 * build js for ad close handling
	 *
	 * @param str $set_cookie_string for setup timeout cookie
	 * @param str $wrapper_id Id of the wrapper.
	 * @param bool $close_button_enabled Whether the close button is enabled.
	 * @return str js for ad close handling
	 */
	private function build_close_ad_js( $set_cookie_string, $wrapper_id, $close_button_enabled ) {
		$script = '<script>( window.advanced_ads_ready || jQuery( document ).ready ).call( null, function() {';
		$script .= "advads_corner_items.close_functions[ '{$wrapper_id}' ] = function() {"
			. "advads.close( '#{$wrapper_id}' ); ";

		if ( $set_cookie_string ) {
			$script .= $set_cookie_string;
		}
		$script .= '};';

		if ( $close_button_enabled ) {
			$script .= "jQuery( '#{$wrapper_id}' ).on( 'click', '.advads-close-button', function() { "
				. "var close_function = advads_corner_items.close_functions[ '{$wrapper_id}' ];"
				. "if ( typeof close_function === 'function' ) {"
				.     "close_function(); "
				. "}";
			$script .= '});';
		}
		$script .= '});</script>';
		return $script;
	}

	/**
	 * check if placement was closed with a cookie before
	 *
	 * @since 1.2.4
	 * @param int $id placement id
	 * @return bool whether placement can be displayed or not
	 * @return bool false if placement was closed for this user
	 */
	public function placement_can_display( $return, $id = 0 ) {

		// get all placements
		$placements = Advanced_Ads::get_ad_placements_array();

		if ( ! isset( $placements[ $id ]['options']['corner_placement']['close']['enabled'] ) || ! $placements[ $id ]['options']['corner_placement']['close']['enabled'] ) {
			return $return;
		}

		if ( isset( $placements[ $id ]['options']['corner_placement']['close']['timeout_enabled'] ) && $placements[ $id ]['options']['corner_placement']['close']['timeout_enabled'] ) {
			$slug = sanitize_title( $placements[ $id ]['name'] );
			if ( isset( $_COOKIE[ 'timeout_placement_' . $slug ] ) ) {
				return false;
			}
		}

		return $return;
	}

	/**
	 * check if the current ad can be displayed based on minimal and maximum browser width
	 *
	 * @since 1.2.4
	 * @param bool $can_display value as set so far
	 * @param obj $ad the ad object
	 * @return bool false if can’t be displayed, else return $can_display
	 */
	public function can_display( $can_display, $ad = 0 ) {

		$ad_options = $ad->options();

		if ( ! isset( $ad_options['corner']['close']['enabled'] ) || ! $ad_options['corner']['close']['enabled'] ) {
			return $can_display;
		}

		if ( isset( $ad_options['corner']['close']['timeout_enabled'] ) && $ad_options['corner']['close']['timeout_enabled'] ) {
			if ( isset( $_COOKIE[ 'timeout_' . $ad->id ] ) ) {
				return false;
			}
		}

		return $can_display;
	}

	/**
	 * returns the (css) class name for corner ads
	 */
	public static final function get_corner_class(){
	    return Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . "corner";
	}
}