<?php
/**
 * Demos
 *
 * @package Ocean_Extra
 * @category Core
 * @author OceanWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Start Class
if ( ! class_exists( 'OceanWP_Demos' ) ) {

	class OceanWP_Demos {

		/**
		 * Start things up
		 */
		public function __construct() {

			// Return if not in admin
			if ( ! is_admin() || is_customize_preview() ) {
				return;
			}

			// Import demos page
			if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
				require_once( OE_PATH .'/includes/panel/classes/importers/class-helpers.php' );
				require_once( OE_PATH .'/includes/panel/classes/class-install-demos.php' );
			}

			// Disable Woo Wizard if the Pro Demos plugin is activated
			if ( class_exists( 'Ocean_Pro_Demos' ) ) {
				add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );
				add_filter( 'woocommerce_show_admin_notice', '__return_false' );
				add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_false' );
	        }

			// Start things
			add_action( 'admin_init', array( $this, 'init' ) );

			// Demos scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			// Allows xml uploads
			add_filter( 'upload_mimes', array( $this, 'allow_xml_uploads' ) );

			// Demos popup
			add_action( 'admin_footer', array( $this, 'popup' ) );

		}

		/**
		 * Register the AJAX methods
		 *
		 * @since 1.0.0
		 */
		public function init() {

			// Demos popup ajax
			add_action( 'wp_ajax_owp_ajax_get_demo_data', array( $this, 'ajax_demo_data' ) );
			add_action( 'wp_ajax_owp_ajax_required_plugins_activate', array( $this, 'ajax_required_plugins_activate' ) );

			// Get data to import
			add_action( 'wp_ajax_owp_ajax_get_import_data', array( $this, 'ajax_get_import_data' ) );

			// Import XML file
			add_action( 'wp_ajax_owp_ajax_import_xml', array( $this, 'ajax_import_xml' ) );

			// Import customizer settings
			add_action( 'wp_ajax_owp_ajax_import_theme_settings', array( $this, 'ajax_import_theme_settings' ) );

			// Import widgets
			add_action( 'wp_ajax_owp_ajax_import_widgets', array( $this, 'ajax_import_widgets' ) );

			// Import forms
			add_action( 'wp_ajax_owp_ajax_import_forms', array( $this, 'ajax_import_forms' ) );

			// After import
			add_action( 'wp_ajax_owp_after_import', array( $this, 'ajax_after_import' ) );

		}

		/**
		 * Load scripts
		 *
		 * @since 1.4.5
		 */
		public static function scripts( $hook_suffix ) {

			if ( 'theme-panel_page_oceanwp-panel-install-demos' == $hook_suffix ) {

				// CSS
				wp_enqueue_style( 'owp-demos-style', plugins_url( '/assets/css/demos.min.css', __FILE__ ) );

				// JS
				wp_enqueue_script( 'owp-demos-js', plugins_url( '/assets/js/demos.min.js', __FILE__ ), array( 'jquery', 'wp-util', 'updates' ), '1.0', true );

				wp_localize_script( 'owp-demos-js', 'owpDemos', array(
					'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
					'demo_data_nonce' 			=> wp_create_nonce( 'get-demo-data' ),
					'owp_import_data_nonce' 	=> wp_create_nonce( 'owp_import_data_nonce' ),
					'content_importing_error' 	=> esc_html__( 'There was a problem during the importing process resulting in the following error from your server:', 'ocean-extra' ),
					'button_activating' 		=> esc_html__( 'Activating', 'ocean-extra' ) . '&hellip;',
					'button_active' 			=> esc_html__( 'Active', 'ocean-extra' ),
				) );

			}

		}

		/**
		 * Allows xml uploads so we can import from github
		 *
		 * @since 1.0.0
		 */
		public function allow_xml_uploads( $mimes ) {
			$mimes = array_merge( $mimes, array(
				'xml' 	=> 'application/xml'
			) );
			return $mimes;
		}

		/**
		 * Get demos data to add them in the Demo Import and Pro Demos plugins
		 *
		 * @since 1.4.5
		 */
		public static function get_demos_data() {

			// Demos url
			$url = 'https://raw.githubusercontent.com/oceanwp/oceanwp-sample-data/master/';

			$data = array(

				'architect' => array(
					'categories'        => array( 'Business' ),
					'xml_file'     		=> $url . 'architect/sample-data.xml',
					'theme_settings' 	=> $url . 'architect/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'architect/widgets.wie',
					'form_file'  		=> $url . 'architect/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'Blog',
					'posts_to_show'  	=> '3',
					'elementor_width'  	=> '1220',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
							array(
								'slug' 		=> 'ocean-elementor-widgets',
								'init'  	=> 'ocean-elementor-widgets/ocean-elementor-widgets.php',
								'name' 		=> 'Ocean Elementor Widgets',
							),
						),
					),
				),
				
				'blogger' => array(
					'categories'        => array( 'Blog' ),
					'xml_file'     		=> $url . 'blogger/sample-data.xml',
					'theme_settings' 	=> $url . 'blogger/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'blogger/widgets.wie',
					'form_file'  		=> $url . 'blogger/form.json',
					'home_title'  		=> '',
					'blog_title'  		=> 'Home',
					'posts_to_show'  	=> '12',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
						),
					),
				),
				
				'coach' => array(
					'categories'        => array( 'Business', 'Sport', 'One Page' ),
					'xml_file'     		=> $url . 'coach/sample-data.xml',
					'theme_settings' 	=> $url . 'coach/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'coach/widgets.wie',
					'form_file'  		=> $url . 'coach/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'Blog',
					'posts_to_show'  	=> '3',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
						),
					),
				),
				
				'gym' => array(
					'categories'        => array( 'Business', 'Sport' ),
					'xml_file'     		=> $url . 'gym/sample-data.xml',
					'theme_settings' 	=> $url . 'gym/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'gym/widgets.wie',
					'form_file'  		=> $url . 'gym/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'News',
					'posts_to_show'  	=> '3',
					'elementor_width'  	=> '1100',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
							array(
								'slug' 		=> 'ocean-elementor-widgets',
								'init'  	=> 'ocean-elementor-widgets/ocean-elementor-widgets.php',
								'name' 		=> 'Ocean Elementor Widgets',
							),
						),
					),
				),
				
				'lawyer' => array(
					'categories'        => array( 'Business' ),
					'xml_file'     		=> $url . 'lawyer/sample-data.xml',
					'theme_settings' 	=> $url . 'lawyer/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'lawyer/widgets.wie',
					'form_file'  		=> $url . 'lawyer/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'Blog',
					'posts_to_show'  	=> '3',
					'elementor_width'  	=> '1220',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
							array(
								'slug' 		=> 'ocean-side-panel',
								'init'  	=> 'ocean-side-panel/ocean-side-panel.php',
								'name' 		=> 'Ocean Side Panel',
							),
							array(
								'slug' 		=> 'ocean-elementor-widgets',
								'init'  	=> 'ocean-elementor-widgets/ocean-elementor-widgets.php',
								'name' 		=> 'Ocean Elementor Widgets',
							),
						),
					),
				),
				
				'megagym' => array(
					'categories'        => array( 'Business', 'Sport', 'One Page' ),
					'xml_file'     		=> $url . 'megagym/sample-data.xml',
					'theme_settings' 	=> $url . 'megagym/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'megagym/widgets.wie',
					'form_file'  		=> $url . 'megagym/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'Blog',
					'posts_to_show'  	=> '3',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
							array(
								'slug' 		=> 'ocean-elementor-widgets',
								'init'  	=> 'ocean-elementor-widgets/ocean-elementor-widgets.php',
								'name' 		=> 'Ocean Elementor Widgets',
							),
						),
					),
				),
				
				'personal' => array(
					'categories'        => array( 'Blog' ),
					'xml_file'     		=> $url . 'personal/sample-data.xml',
					'theme_settings' 	=> $url . 'personal/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'personal/widgets.wie',
					'form_file'  		=> $url . 'personal/form.json',
					'home_title'  		=> '',
					'blog_title'  		=> 'Home',
					'posts_to_show'  	=> '3',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-posts-slider',
								'init'  	=> 'ocean-posts-slider/ocean-posts-slider.php',
								'name'  	=> 'Ocean Posts Slider',
							),
							array(
								'slug'  	=> 'ocean-social-sharing',
								'init'  	=> 'ocean-social-sharing/ocean-social-sharing.php',
								'name'  	=> 'Ocean Social Sharing',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
						),
					),
				),
				
				'simple' => array(
					'categories'        => array( 'eCommerce' ),
					'xml_file'     		=> $url . 'simple/sample-data.xml',
					'theme_settings' 	=> $url . 'simple/oceanwp-export.dat',
					'widgets_file'  	=> $url . 'simple/widgets.wie',
					'form_file'  		=> $url . 'simple/form.json',
					'home_title'  		=> 'Home',
					'blog_title'  		=> 'Blog',
					'posts_to_show'  	=> '3',
					'elementor_width'  	=> '1100',
					'is_shop'  			=> true,
					'woo_image_size'  	=> '454',
					'woo_thumb_size' 	=> '348',
					'woo_crop_width'  	=> '3',
					'woo_crop_height' 	=> '4',
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'ocean-extra',
								'init'  	=> 'ocean-extra/ocean-extra.php',
								'name'  	=> 'Ocean Extra',
							),
							array(
								'slug'  	=> 'ocean-modal-window',
								'init'  	=> 'ocean-modal-window/ocean-modal-window.php',
								'name'  	=> 'Ocean Modal Window',
							),
							array(
								'slug'  	=> 'ocean-product-sharing',
								'init'  	=> 'ocean-product-sharing/ocean-product-sharing.php',
								'name'  	=> 'Ocean Product Sharing',
							),
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'wpforms-lite',
								'init'  	=> 'wpforms-lite/wpforms.php',
								'name'  	=> 'WPForms',
							),
							array(
								'slug'  	=> 'woocommerce',
								'init'  	=> 'woocommerce/woocommerce.php',
								'name'  	=> 'WooCommerce',
							),
						),
						'premium' => array(
							array(
								'slug' 		=> 'ocean-sticky-header',
								'init'  	=> 'ocean-sticky-header/ocean-sticky-header.php',
								'name' 		=> 'Ocean Sticky Header',
							),
							array(
								'slug' 		=> 'ocean-elementor-widgets',
								'init'  	=> 'ocean-elementor-widgets/ocean-elementor-widgets.php',
								'name' 		=> 'Ocean Elementor Widgets',
							),
							array(
								'slug' 		=> 'ocean-footer-callout',
								'init'  	=> 'ocean-footer-callout/ocean-footer-callout.php',
					