<?php

/**
 * The core plugin class
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

if ( ! class_exists( 'Xophz_Compass_Plugin_Base' ) ) {
	$core_plugin_base = dirname( dirname( __DIR__ ) ) . '/xophz-compass/includes/core/class-compass-plugin-base.php';
	if ( file_exists( $core_plugin_base ) ) {
		require_once $core_plugin_base;
	}
}

class Xophz_Compass_Bulletin_Board extends Xophz_Compass_Plugin_Base {

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
	protected string $version;

	/**
	 * Initialize plugin properties and dependencies.
	 */
	public function __construct( ?string $param1 = null, ?string $version = null, string $param3 = '' ) {
		if ( null === $param1 ) {
			$file = dirname( __DIR__ ) . '/xophz-compass-bulletin-board.php';
			$ver  = defined( 'XOPHZ_COMPASS_BULLETIN_BOARD_VERSION' ) ? XOPHZ_COMPASS_BULLETIN_BOARD_VERSION : '1.0.0';
			parent::__construct( $file, $ver, 'xophz-compass-bulletin-board' );
		} else {
			parent::__construct( $param1, $version ?? '1.0.0', $param3 );
		}
		$this->plugin_name = $this->text_domain;
		$this->loader = $this;

		$this->load_dependencies();
		$this->set_locale();
		$this->init_components();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-cpt.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-bulletin-board-rest.php';

	}

	/**
	 * Set up internationalization.
	 */
	private function set_locale() {
		// Localization handled by Xophz_Compass_Plugin_Base on init priority 5
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
	public function run(): void {
		$this->run_hooks();
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
	public function get_loader(): self {
		return $this;
	}

	/**
	 * Get the plugin version.
	 */
	public function get_version(): string {
		return $this->version;
	}
}
