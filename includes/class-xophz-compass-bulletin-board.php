<?php

/**
 * The core plugin class
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

class Xophz_Compass_Bulletin_Board {

	/**
	 * The loader that coordinates all actions and filters.
	 *
	 * @var Xophz_Compass_Bulletin_Board_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string $version
	 */
	protected $version;

	/**
	 * Initialize plugin properties and dependencies.
	 */
	public function __construct() {
		if ( defined( 'XOPHZ_COMPASS_BULLETIN_BOARD_VERSION' ) ) {
			$this->version = XOPHZ_COMPASS_BULLETIN_BOARD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'xophz-compass-bulletin-board';

		$this->load_dependencies();
		$this->set_locale();
		$this->init_components();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-cpt.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-rest.php';

		$this->loader = new Xophz_Compass_Bulletin_Board_Loader();
	}

	/**
	 * Set up internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new Xophz_Compass_Bulletin_Board_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Initialize CPTs and REST API components.
	 */
	private function init_components() {
		Xophz_Compass_Bulletin_Board_CPT::init();
		Xophz_Compass_Bulletin_Board_REST::init();
	}

	/**
	 * Run the loader to execute all registered hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin name.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the loader instance.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get the plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
