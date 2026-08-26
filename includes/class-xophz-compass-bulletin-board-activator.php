<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

class Xophz_Compass_Bulletin_Board_Activator {

	/**
	 * Run activation tasks: register post types/taxonomies and seed defaults.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once plugin_dir_path( __FILE__ ) . 'class-xophz-compass-bulletin-board-cpt.php';
		Xophz_Compass_Bulletin_Board_CPT::register_post_type_and_taxonomy();
		Xophz_Compass_Bulletin_Board_CPT::register_term_meta();
		Xophz_Compass_Bulletin_Board_CPT::seed_default_boards();

		flush_rewrite_rules();
	}
}
