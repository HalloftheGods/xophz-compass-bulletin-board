<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

class Xophz_Compass_Bulletin_Board_Deactivator {

	/**
	 * Run deactivation cleanup.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
