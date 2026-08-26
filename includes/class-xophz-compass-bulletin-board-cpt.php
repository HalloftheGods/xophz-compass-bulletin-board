<?php

/**
 * Bulletin Board Custom Post Types & Taxonomy Registration
 *
 * Defines the Topics CPT (`cafeteria_topic`) and Boards taxonomy (`cafeteria_board`)
 * with metadata, capabilities, and default community sphere seeders.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Xophz_Compass_Bulletin_Board_CPT {

	const POST_TYPE = 'cafeteria_topic';
	const TAXONOMY  = 'cafeteria_board';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type_and_taxonomy' ) );
		add_action( 'init', array( __CLASS__, 'register_term_meta' ) );
		add_action( 'init', array( __CLASS__, 'seed_default_boards' ), 20 );
	}

	/**
	 * Register Taxonomy and Custom Post Type
	 */
	public static function register_post_type_and_taxonomy() {
		// 1. Register Taxonomy: Boards
		$tax_labels = array(
			'name'              => _x( 'Boards', 'taxonomy general name', 'xophz-compass-bulletin-board' ),
			'singular_name'     => _x( 'Board', 'taxonomy singular name', 'xophz-compass-bulletin-board' ),
			'search_items'      => __( 'Search Boards', 'xophz-compass-bulletin-board' ),
			'all_items'         => __( 'All Boards', 'xophz-compass-bulletin-board' ),
			'parent_item'       => __( 'Parent Board', 'xophz-compass-bulletin-board' ),
			'parent_item_colon' => __( 'Parent Board:', 'xophz-compass-bulletin-board' ),
			'edit_item'         => __( 'Edit Board', 'xophz-compass-bulletin-board' ),
			'update_item'       => __( 'Update Board', 'xophz-compass-bulletin-board' ),
			'add_new_item'      => __( 'Add New Board', 'xophz-compass-bulletin-board' ),
			'new_item_name'     => __( 'New Board Name', 'xophz-compass-bulletin-board' ),
			'menu_name'         => __( 'Boards', 'xophz-compass-bulletin-board' ),
		);

		$tax_args = array(
			'hierarchical'      => true,
			'labels'            => $tax_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rest_base'         => 'cafeteria_board',
			'rewrite'           => array( 'slug' => 'board' ),
		);

		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), $tax_args );

		// 2. Register Post Type: Topics
		$cpt_labels = array(
			'name'               => _x( 'Topics', 'post type general name', 'xophz-compass-bulletin-board' ),
			'singular_name'      => _x( 'Topic', 'post type singular name', 'xophz-compass-bulletin-board' ),
			'menu_name'          => _x( 'Topics', 'admin menu', 'xophz-compass-bulletin-board' ),
			'name_admin_bar'     => _x( 'Topic', 'add new on admin bar', 'xophz-compass-bulletin-board' ),
			'add_new'            => _x( 'Add New', 'topic', 'xophz-compass-bulletin-board' ),
			'add_new_item'       => __( 'Add New Topic', 'xophz-compass-bulletin-board' ),
			'new_item'           => __( 'New Topic', 'xophz-compass-bulletin-board' ),
			'edit_item'          => __( 'Edit Topic', 'xophz-compass-bulletin-board' ),
			'view_item'          => __( 'View Topic', 'xophz-compass-bulletin-board' ),
			'all_items'          => __( 'All Topics', 'xophz-compass-bulletin-board' ),
			'search_items'       => __( 'Search Topics', 'xophz-compass-bulletin-board' ),
			'not_found'          => __( 'No topics found.', 'xophz-compass-bulletin-board' ),
			'not_found_in_trash' => __( 'No topics found in Trash.', 'xophz-compass-bulletin-board' ),
		);

		$cpt_args = array(
			'labels'             => $cpt_labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'topic' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-format-chat',
			'show_in_rest'       => true,
			'rest_base'          => 'cafeteria_topic',
			'supports'           => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'revisions', 'thumbnail' ),
			'taxonomies'         => array( self::TAXONOMY ),
		);

		register_post_type( self::POST_TYPE, $cpt_args );
	}

	/**
	 * Register term meta for icons and ordering
	 */
	public static function register_term_meta() {
		register_term_meta( self::TAXONOMY, 'board_icon', array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'description'   => 'FontAwesome icon string class',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		) );

		register_term_meta( self::TAXONOMY, 'board_order', array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'description'   => 'Display ordering number',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		) );
	}

	/**
	 * Seed default community boards (Noosphere, Suggestion Box, Lunch Room)
	 */
	public static function seed_default_boards() {
		if ( ! taxonomy_exists( self::TAXONOMY ) ) {
			return;
		}

		self::seed_noosphere_boards();
		self::seed_suggestion_box_boards();
		self::seed_lunch_room_boards();
	}

	/**
	 * Seed Noosphere Spheres (noo/*)
	 */
	private static function seed_noosphere_boards() {
		$parent_slug = 'noo';
		$parent_term = get_term_by( 'slug', $parent_slug, self::TAXONOMY );

		if ( ! $parent_term ) {
			$inserted = wp_insert_term( 'Noosphere', self::TAXONOMY, array(
				'slug'        => $parent_slug,
				'description' => 'Noosphere Observer Spheres and Global Pulse',
			) );

			if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
				$parent_id = $inserted['term_id'];
				update_term_meta( $parent_id, 'board_icon', 'fal fa-galaxy' );
				update_term_meta( $parent_id, 'board_order', '0' );
			} else {
				return;
			}
		} else {
			$parent_id = $parent_term->term_id;
		}

		$spheres = array(
			array(
				'name'        => 'u/noo/COMPASS',
				'slug'        => 'noo-compass',
				'description' => 'Navigational systems and compass updates',
				'icon'        => 'fal fa-compass',
				'order'       => '1',
			),
			array(
				'name'        => 'u/noo/Aesthetics',
				'slug'        => 'noo-aesthetics',
				'description' => 'Design systems, neon, glassmorphism, visual theory',
				'icon'        => 'fal fa-palette',
				'order'       => '2',
			),
			array(
				'name'        => 'u/noo/General',
				'slug'        => 'noo-general',
				'description' => 'General chatter across the federation',
				'icon'        => 'fal fa-comments',
				'order'       => '3',
			),
		);

		foreach ( $spheres as $sphere ) {
			$existing = get_term_by( 'slug', $sphere['slug'], self::TAXONOMY );
			if ( ! $existing ) {
				$inst = wp_insert_term( $sphere['name'], self::TAXONOMY, array(
					'slug'        => $sphere['slug'],
					'description' => $sphere['description'],
					'parent'      => $parent_id,
				) );

				if ( ! is_wp_error( $inst ) && isset( $inst['term_id'] ) ) {
					update_term_meta( $inst['term_id'], 'board_icon', $sphere['icon'] );
					update_term_meta( $inst['term_id'], 'board_order', $sphere['order'] );
				}
			}
		}
	}

	/**
	 * Seed Suggestion Box Boards
	 */
	private static function seed_suggestion_box_boards() {
		$sug_slug = 'suggestion-box';
		$sug_term = get_term_by( 'slug', $sug_slug, self::TAXONOMY );

		if ( ! $sug_term ) {
			$inserted = wp_insert_term( 'Suggestion Box', self::TAXONOMY, array(
				'slug'        => $sug_slug,
				'description' => 'A place for ideas, feedback, and feature requests.',
			) );

			if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
				$sug_id = $inserted['term_id'];
				update_term_meta( $sug_id, 'board_icon', 'fal fa-box-ballot' );
				update_term_meta( $sug_id, 'board_order', '10' );
			} else {
				return;
			}
		} else {
			$sug_id = $sug_term->term_id;
		}

		$suggestions = array(
			array(
				'name'        => 'Ideas, Suggestions, Nuances',
				'slug'        => 'ideas-suggestions',
				'description' => 'Share your ideas and suggestions for the platform.',
				'icon'        => 'fal fa-lightbulb-on',
				'order'       => '1',
			),
			array(
				'name'        => 'Comments, Feedback, Shouts',
				'slug'        => 'comments-feedback',
				'description' => 'General comments and feedback about the experience.',
				'icon'        => 'fal fa-comment-alt-dots',
				'order'       => '2',
			),
			array(
				'name'        => 'Feature Requests',
				'slug'        => 'feature-requests',
				'description' => 'Request new features and capabilities.',
				'icon'        => 'fal fa-flask-potion',
				'order'       => '3',
			),
		);

		foreach ( $suggestions as $child ) {
			$existing_child = get_term_by( 'slug', $child['slug'], self::TAXONOMY );
			if ( ! $existing_child ) {
				$inst = wp_insert_term( $child['name'], self::TAXONOMY, array(
					'slug'        => $child['slug'],
					'description' => $child['description'],
					'parent'      => $sug_id,
				) );

				if ( ! is_wp_error( $inst ) && isset( $inst['term_id'] ) ) {
					update_term_meta( $inst['term_id'], 'board_icon', $child['icon'] );
					update_term_meta( $inst['term_id'], 'board_order', $child['order'] );
				}
			}
		}
	}

	/**
	 * Seed Lunch Room Boards
	 */
	private static function seed_lunch_room_boards() {
		$lunch_slug = 'lunch-room';
		$lunch_term = get_term_by( 'slug', $lunch_slug, self::TAXONOMY );

		if ( ! $lunch_term ) {
			$inserted = wp_insert_term( 'Lunch Room', self::TAXONOMY, array(
				'slug'        => $lunch_slug,
				'description' => 'Staff and team discussions shaping the future.',
			) );

			if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
				$lunch_id = $inserted['term_id'];
				update_term_meta( $lunch_id, 'board_icon', 'fal fa-coffee-pot' );
				update_term_meta( $lunch_id, 'board_order', '20' );
			} else {
				return;
			}
		} else {
			$lunch_id = $lunch_term->term_id;
		}

		$lunch_channels = array(
			array(
				'name'        => 'The Office',
				'slug'        => 'the-office',
				'description' => 'General administrative and operations discussion.',
				'icon'        => 'fal fa-comments-alt',
				'order'       => '1',
			),
			array(
				'name'        => 'Water Cooler',
				'slug'        => 'water-cooler',
				'description' => 'Casual backchannel and team coffee breaks.',
				'icon'        => 'fal fa-mug-hot',
				'order'       => '2',
			),
		);

		foreach ( $lunch_channels as $child ) {
			$existing = get_term_by( 'slug', $child['slug'], self::TAXONOMY );
			if ( ! $existing ) {
				$inst = wp_insert_term( $child['name'], self::TAXONOMY, array(
					'slug'        => $child['slug'],
					'description' => $child['description'],
					'parent'      => $lunch_id,
				) );

				if ( ! is_wp_error( $inst ) && isset( $inst['term_id'] ) ) {
					update_term_meta( $inst['term_id'], 'board_icon', $child['icon'] );
					update_term_meta( $inst['term_id'], 'board_order', $child['order'] );
				}
			}
		}
	}
}
