<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/HalloftheGods/xophz-compass-bulletin-board
 * @since             1.0.0
 * @package           Xophz_Compass_Bulletin_Board
 *
 * @wordpress-plugin
 * Category:          Trajectory
 * Group:             Community
 * Plugin Name:       Xophz Bulletin Board
 * Plugin URI:        https://github.com/HalloftheGods/xophz-compass-bulletin-board
 * Description:       Enterprise-grade WordPress forum software, topic dispatcher, and Noosphere Observer engine.
 * Version:           26.9.4-171
 * Author:            Hall of the Gods, Inc.
 * Author URI:        http://www.hallofthegods.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xophz-compass-bulletin-board
 * Domain Path:       /languages
 * Update URI:        https://github.com/HalloftheGods/xophz-compass-bulletin-board
 * Color:             #ffb300
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'XOPHZ_COMPASS_BULLETIN_BOARD_VERSION', '26.9.4-171' );

/**
 * The code that runs during plugin activation.
 */
function activate_xophz_compass_bulletin_board() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xophz-compass-bulletin-board-activator.php';
	Xophz_Compass_Bulletin_Board_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_xophz_compass_bulletin_board() {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'activate_xophz_compass_bulletin_board' );
register_deactivation_hook( __FILE__, 'deactivate_xophz_compass_bulletin_board' );

/**
 * The core plugin class that is used to define internationalization,
 * CPTs, taxonomies, and REST API routes.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xophz-compass-bulletin-board.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_xophz_compass_bulletin_board() {
	$plugin = new Xophz_Compass_Bulletin_Board();
	$plugin->run();
}
add_action( 'plugins_loaded', 'run_xophz_compass_bulletin_board' );
