<?php
class Advanced_Ads_Corner_Admin {

	/**
	 * stores the settings page hook
	 *
	 * @var     string
	 */
	protected $settings_page_hook = '';

	const PLUGIN_LINK = 'https://wpadvancedads.com/add-ons/corner-peel-ads/';

	/**
	 * holds base class
	 *
	 * @var Advanced_Ads_Corner_Plugin
	 * @since 1.2.0
	 */
	protected $plugin;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->plugin = Advanced_Ads_Corner_Plugin::get_instance();

		add_action( 'plugins_loaded', array( $this, 'wp_admin_plugins_loaded' ) );
	}

	/**
	 * load actions and filters
	 */
	public function wp_admin_plugins_loaded() {
		if ( ! class_exists( 'Advanced_Ads_Admin', false ) ) {
			// show admin notice
			add_action( 'admin_notices', array( $this, 'missing_plugin_notice' ) );

			return;
		}

		// register settings
		add_action( 'advanced-ads-settings-init', array( $this, 'settings_init' ) );
		// add our new options using the options filter before saving
		add_filter( 'advanced-ads-save-options', array( $this, 'save_options' ), 10, 2 );
		// add admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		// content of corner placement
		add_action( 'advanced-ads-placement-options-after-advanced', array( $this, 'corner_placement_content' ), 10, 2 );
		// add AdSense warning
		add_action( 'advanced-ads-placement-options-after', array( $this, 'add_adsense_warning' ), 5, 2 );
        // enqueue add media scripts
		add_action('admin_enqueue_scripts', array( $this, 'add_media_scripts'));
	}

	/**
	 * show warning if Advanced Ads js is not activated
	 */
	public function missing_plugin_notice() {
		$plugins = get_plugins();
		if( isset( $plugins['advanced-ads/advanced-ads.php'] ) ){ // is installed, but not active
			$link = '<a class="button button-primary" href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads/advanced-ads.php&amp', 'activate-plugin_advanced-ads/advanced-ads.php' ) . '">'. __('Activate Now', 'advanced-ads-corner') .'</a>';
		} else {
			$link = '<a class="button button-primary" href="' . wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . 'advanced-ads'), 'install-plugin_' . 'advanced-ads') . '">'. __('Install Now', 'advanced-ads-corner') .'</a>';
		}
		echo '<div class="error"><p>' . sprintf(__('<strong>%s</strong> requires the <strong><a href="https://wpadvancedads.com/#utm_source=advanced-ads&utm_medium=link&utm_campaign=activate-genesis" target="_blank">Advanced Ads</a></strong> plugin to be installed and activated on your site.', 'advanced-ads-corner'), 'Advanced Ads – Corner Peel Ads')
			. '&nbsp;' . $link . '</p></div>';
	}

	/**
	 * add corner placement styles
	 *
	 * @since 1.2.4
	 * @param type $hook_suffix
	 */
	function admin_scripts( $hook_suffix ) {
		if ( ! class_exists( 'Advanced_Ads_Admin' ) ) {
			return;
		};

		if ( Advanced_Ads_Admin::screen_belongs_to_advanced_ads() ) {
			wp_enqueue_style( 'advanced-ads-corner-admin-css', AACPDS_BASE_URL . 'admin/assets/css/admin.css', array(), AACPDS_VERSION );
			wp_add_inline_style( 'advanced-ads-corner-admin-css', self::get_custom_css() );
		}
	}
	
	/**
	 * creates the css containing the corner placement styles
	 *
	 * @since 1.6.3
	 */
	static final function get_custom_css(){
		$corner_class = Advanced_Ads_Corner::get_corner_class();
		$css = ".$corner_class-aa-position div.clear { content: ' '; display: block; float: none; clear: both; }\n";
		$css.= ".advads-placements-table .$corner_class-aa-position .advads-sticky-assistant table tbody tr td { width: 3em; height: 2em; text-align: center; vertical-align: middle; padding: 0; }\n";
		return $css;
	}

	/**
	 * add settings to settings page
	 *
	 * @since 1.2.0
	 */
	public function settings_init() {

		// don’t initiate if main plugin not loaded
		if ( ! class_exists( 'Advanced_Ads_Admin' ) ) {
			return;
		}

		// get settings page hook
		$admin = Advanced_Ads_Admin::get_instance();
		$hook = $admin->plugin_screen_hook_suffix;
		$this->settings_page_hook = $hook;

		// add license key field to license section
		add_settings_field(
			'corner-license',
			__( 'Corner Peel Ads', 'advanced-ads-corner' ),
			array( $this, 'render_settings_license_callback' ),
			'advanced-ads-settings-license-page',
			'advanced_ads_settings_license_section'
		);

	}

	/**
	 * render license key section
	 *
	 * @since 1.2.0
	 */
	public function render_settings_license_callback() {
		$licenses = get_option(ADVADS_SLUG . '-licenses', array());
		$license_key = isset( $licenses['corner'] ) ? $licenses['corner'] : '';
		$license_status = get_option( $this->plugin->options_slug . '-license-status', false );
		$index = 'corner';
		$plugin_name = AACPDS_PLUGIN_NAME;
		$options_slug = $this->plugin->options_slug;
		$plugin_url = self::PLUGIN_LINK;

		// template in main plugin
		include ADVADS_BASE_PATH . 'admin/views/setting-license.php';
	}

	/**
	 * save options
	 *
	 * @since 1.0.0
	 */
	public function save_options( $options = array(), $ad = 0 ) {
		// sanitize sticky options
		$positions = array();

		$options['corner']['enabled']                  = ( ! empty( $_POST['advanced_ad']['corner']['enabled'] ) ) ? absint( $_POST['advanced_ad']['corner']['enabled'] ) : 0;
		$options['corner']['trigger']                  = ( ! empty( $_POST['advanced_ad']['corner']['trigger'] ) ) ? $_POST['advanced_ad']['corner']['trigger'] : '';
		$options['corner']['offset']                   = ( ! empty( $_POST['advanced_ad']['corner']['offset'] ) ) ? absint( $_POST['advanced_ad']['corner']['offset'] ) : '';
		$options['corner']['background']               = ( ! empty( $_POST['advanced_ad']['corner']['background'] ) ) ? absint( $_POST['advanced_ad']['corner']['background']) : '';
		$options['corner']['close']['enabled']         = ( ! empty( $_POST['advanced_ad']['corner']['close']['enabled'] ) ) ? absint( $_POST['advanced_ad']['corner']['close']['enabled'] ) : '';
		$options['corner']['close']['where']           = ( ! empty( $_POST['advanced_ad']['corner']['close']['where'] ) ) ? $_POST['advanced_ad']['corner']['close']['where'] : '';
		$options['corner']['close']['side']            = ( ! empty( $_POST['advanced_ad']['corner']['close']['side'] ) ) ? $_POST['advanced_ad']['corner']['close']['side'] : '';
		$options['corner']['close']['timeout_enabled'] = ( ! empty( $_POST['advanced_ad']['corner']['close']['timeout_enabled'] ) ) ? $_POST['advanced_ad']['corner']['close']['timeout_enabled'] : false;
		$options['corner']['close']['timeout']         = ( ! empty( $_POST['advanced_ad']['corner']['close']['timeout'] ) ) ? absint( $_POST['advanced_ad']['corner']['close']['timeout'] ) : 0;
		$options['corner']['effect']                   = ( ! empty( $_POST['advanced_ad']['corner']['effect'] ) ) ? $_POST['advanced_ad']['corner']['effect'] : 'show';
		$options['corner']['duration']                 = ( ! empty( $_POST['advanced_ad']['corner']['duration'] ) ) ? absint( $_POST['advanced_ad']['corner']['duration'] ) : 0;

		return $options;
	}

	/**
	 * render corner placement content
	 *
	 * @since 1.2.4
	 * @param string $placement_slug id of the placement
	 *
	 */
	public function corner_placement_content( $placement_slug, $placement ) { // admin placement content
		switch ( $placement['type'] ) {
			case 'corner' :
			    
				if( ! class_exists( 'Advanced_Ads_Admin_Options' ) ){
					echo 'Please update to Advanced Ads 1.8';
					return;
				}
			    
				$options = isset( $placement['options']['corner_placement'] ) ? $placement['options']['corner_placement'] : array();
				$option_name = "advads[placements][$placement_slug][options][corner_placement]";
			    
				// trigger
				$trigger    = isset( $options['trigger'] ) ? $options['trigger'] : '';
				$offset     = isset( $options['offset'] ) ? absint( $options['offset'] ) : 0;
				$delay_sec  = isset( $options['delay_sec'] ) ? absint( $options['delay_sec'] ) : 0;

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/trigger.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-trigger',
					__( 'show the ad', 'advanced-ads-corner' ),
					$option_content );
				
				// effect
				$effect     = isset( $options['effect'] ) ? $options['effect'] : 'show';
				$duration   = isset( $options['duration'] ) ? absint( $options['duration'] ) : 0;

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/effects.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-effect',
					__( 'effect', 'advanced-ads-corner' ),
					$option_content );				

				// auto close
				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/auto_close.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-auto-close',
					__( 'auto close', 'advanced-ads-corner' ),
					$option_content );

				// close button
				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/close-button.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-trigger',
					__( 'close button', 'advanced-ads-corner' ),
					$option_content );
				
				// position on the screen
				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/position.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-trigger',
					__( 'Position', 'advanced-ads-corner' ),
					$option_content );

				// Ad start size
				$width = isset( $placement['options']['start_width'] ) ? absint( $placement['options']['start_width'] ) : 58;
				$height = isset( $placement['options']['start_height'] ) ? absint( $placement['options']['start_height'] ) : 58;

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/size.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-start-size',
					__( 'Start size', 'advanced-ads-corner' ),
					$option_content );

				// Cover background
				$cover_background = isset( $placement['options']['cover_background'] ) ? $placement['options']['cover_background'] : '';

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/cover-background.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-cover-background',
					__( 'Cover background', 'advanced-ads-corner' ),
					$option_content );
			break;
		}
	}

	/**
	 * Add a warning when an AdSense ad is assigned to the corner placement.
	 *
	 * @param string $_placement_slug
	 * @param array $_placement
	 */
	public function add_adsense_warning( $_placement_slug, $_placement ) {
		if ( 'corner' !== $_placement['type'] || empty( $_placement['item'] ) ) {
			return;
		}

		if ( ! class_exists( 'Advanced_Ads_Utils' ) || ! method_exists( 'Advanced_Ads_Utils', 'get_nested_ads' ) ) {
			return;
		}

		foreach ( Advanced_Ads_Utils::get_nested_ads( $_placement_slug, 'placement' ) as $ad ) {
			if ( $ad->type === 'adsense' ) { ?>
				<p class="advads-error-message"><?php
				_e( 'It is against the AdSense policy to use their ads in popups.', 'advanced-ads-corner' ); ?></p>
				<?php return;
			}
		}
	}

	// Enqueue add media scripts
	public function add_media_scripts() {
		if( is_admin() && isset($_GET['page']) && $_GET['page'] == 'advanced-ads-placements' ) {
			wp_enqueue_media('media-upload');
			wp_enqueue_media('thickbox');
			wp_register_script('add-media', AACPDS_BASE_URL.'admin/assets/js/upload-media.js', array('jquery','media-upload','thickbox'));
			wp_enqueue_script('add-media');
			wp_enqueue_style('thickbox');
        }
	}
}
