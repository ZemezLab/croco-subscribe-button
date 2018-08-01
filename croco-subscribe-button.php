<?php
/**
 * Plugin Name: CrocoBlock Subscribe Button
 * Description: CrocoBlock Subscribe Button
 * Version:     1.0.0
 * Author:      CrocoBlock
 * Author URI:  https://crocoblock.com/
 * Text Domain: croco-subscribe-button
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Croco_Subscribe_Button` doesn't exists yet.
if ( ! class_exists( 'Croco_Subscribe_Button' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Croco_Subscribe_Button {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.0.0';

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Settings.
		 *
		 * @var array
		 */
		private $settings = array();

		/**
		 * Remote data url.
		 *
		 * @var string
		 */
		private $remote_data_url = 'https://raw.githubusercontent.com/ZemezLab/croco-price-data/master/data.json';

		/**
		 * Transient key.
		 *
		 * @var string
		 */
		private $transient_key = 'croco_subscribe_button_settings';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );

			add_action( 'init', array( $this, 'init' ), -999 );

			// Enqueue public assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ));

			// Print button.
			add_action( 'wp_footer', array( $this, 'print_button' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Init.
		 */
		public function init() {
			if ( ! isset( $_GET['croco_subs_btn_clear_cache'] ) ) {
				return;
			}

			delete_transient( $this->transient_key );
		}

		/**
		 * Get settings.
		 *
		 * @return array
		 */
		public function get_settings() {
			if ( ! empty( $this->settings ) ) {
				return $this->settings;
			}

			$this->settings = array( 'price' => 49 ); // default settings

			$settings = get_transient( $this->transient_key );

			if ( ! $settings ) {

				$response = wp_remote_get( $this->remote_data_url, array( 'timeout' => 30 ) );

				if ( ! $response || is_wp_error( $response ) ) {
					return $this->settings;
				}

				$body = wp_remote_retrieve_body( $response );

				if ( ! $body || is_wp_error( $body ) ) {
					return $this->settings;
				}

				$settings = json_decode( $body, true );

				if ( empty( $settings ) ) {
					return $this->settings;
				}

				set_transient( $this->transient_key, $settings, DAY_IN_SECONDS );
			}

			$this->settings = wp_parse_args( $settings, $this->settings );

			return $this->settings;
		}

		/**
		 * Enqueue public assets.
		 */
		public function enqueue_assets() {
			wp_enqueue_style( 'croco-subscribe-button', $this->plugin_url( 'assets/css/frontend.css' ), false, $this->get_version() );
			wp_enqueue_style( 'croco-subscribe-button-fonts', $this->get_fonts_url(), false, null );
		}

		/**
		 * Print button.
		 */
		public function print_button() {
			require_once $this->plugin_path( 'templates/button.php' );
		}

		/***
		 * Get fonts url
		 */
		public function get_fonts_url() {
			$fonts_url = '';
			$fonts     = array();
			$subsets   = 'latin';

			$fonts[] = 'Roboto:400,700';

			if ( $fonts ) {
				$fonts_url = add_query_arg( array(
					'family' => urlencode( implode( '|', $fonts ) ),
					'subset' => urlencode( $subsets ),
				), 'https://fonts.googleapis.com/css' );
			}

			return $fonts_url;
		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}

		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function lang() {
			load_plugin_textdomain( 'croco-subscribe-button', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function activation() {}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function deactivation() {}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

if ( ! function_exists( 'croco_subscribe_button' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function croco_subscribe_button() {
		return Croco_Subscribe_Button::get_instance();
	}
}

croco_subscribe_button();
