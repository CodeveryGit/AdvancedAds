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
		    wp_enqueue_style( 'advanced-ads-corner-admin-css', AACPDS_BASE_URL . 'admin/assets/css/admin.css', array(), AAPLDS_VERSION );
		}
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

				// Ad start size
				$width = isset( $options['start_width'] ) ? absint( $options['start_width'] ) : 58;
				$height = isset( $options['start_height'] ) ? absint( $options['start_height'] ) : 58;

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/size.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-start-size',
					__( 'Start size', 'advanced-ads-corner' ),
					$option_content );

				// Ad full size
				$full_width = isset( $options['full_width'] ) ? absint( $options['full_width'] ) : '';
				$full_height = isset( $options['full_height'] ) ? absint( $options['full_height'] ) : '';

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/full-size.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-full-size',
					__( 'Full size', 'advanced-ads-corner' ),
					$option_content );

				// Peel color
				$peel_color = isset( $options['peel_color'] ) ? $options['peel_color'] : '';

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/peel-color.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-peel-color',
					__( 'Corner Peel color', 'advanced-ads-corner' ),
					$option_content );

				// how to show
				$how_to_show = isset( $options['how_to_show'] ) ? $options['how_to_show'] : 'triangle';

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/how-to-show.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-how-to-show',
					__( 'How to show', 'advanced-ads-corner' ),
					$option_content );

				// disable when
				$disable_when = isset( $options['disable_when'] ) ? $options['disable_when'] : 768;

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/disable-when.php';
				$option_content = ob_get_clean();

				Advanced_Ads_Admin_Options::render_option(
					'placement-corner-disable-when',
					__( 'Disable when', 'advanced-ads-corner' ),
					$option_content );

				// close
                $close = isset( $options['close'] ) ? $options['close'] : 'never';

				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/close.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-close',
					__( 'When to close', 'advanced-ads-corner' ),
					$option_content );
				
				// position on the screen
				ob_start();
				include AACPDS_BASE_PATH . '/admin/views/position.php';
				$option_content = ob_get_clean();
				
				Advanced_Ads_Admin_Options::render_option( 
					'placement-corner-position',
					__( 'Position', 'advanced-ads-corner' ),
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
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_register_script('corner-color-picker', AACPDS_BASE_URL.'admin/assets/js/colorpicker.js', array('jquery'));
			wp_enqueue_script('corner-color-picker');
        }
	}
}
