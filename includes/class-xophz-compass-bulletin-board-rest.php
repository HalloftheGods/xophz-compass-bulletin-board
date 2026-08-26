<?php

/**
 * Bulletin Board REST API Endpoints & Field Extensions
 *
 * Provides REST routes for user board subscriptions, telemetry statistics,
 * and the Noosphere Global Activity Pulse aggregator.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Bulletin_Board
 * @subpackage Xophz_Compass_Bulletin_Board/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Xophz_Compass_Bulletin_Board_REST {

	const POST_TYPE = 'cafeteria_topic';
	const TAXONOMY  = 'cafeteria_board';

	/**
	 * Initialize REST hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_fields' ) );
	}

	/**
	 * Register REST routes
	 */
	public static function register_rest_routes() {
		// Subscriptions API (Compass v1 standard)
		register_rest_route( 'xophz-compass/v1', '/subscriptions', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_user_subscriptions' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_user_subscriptions' ),
				'permission_callback' => function() {
					return is_user_logged_in();
				},
			),
		) );

		// Global Pulse Telemetry API
		register_rest_route( 'xophz-bulletin-board/v1', '/pulse', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_global_pulse' ),
				'permission_callback' => '__return_true',
			),
		) );
	}

	/**
	 * Register custom REST fields for stats
	 */
	public static function register_rest_fields() {
		register_rest_field( self::TAXONOMY, 'stats', array(
			'get_callback' => array( __CLASS__, 'get_board_stats' ),
			'schema'       => null,
		) );

		register_rest_field( self::POST_TYPE, 'stats', array(
			'get_callback' => array( __CLASS__, 'get_topic_stats' ),
			'schema'       => null,
		) );
	}

	/**
	 * Compute board statistics for REST responses
	 */
	public static function get_board_stats( $term, $field_name, $request ) {
		$term_id = is_array( $term ) ? $term['id'] : $term->term_id;
		$term_count = is_array( $term ) ? $term['count'] : $term->count;

		$stats = array(
			'topics'        => $term_count,
			'replies'       => 0,
			'last_activity' => null,
		);

		$topics = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		) );

		$total_replies = 0;
		foreach ( $topics as $topic_id ) {
			$comments_count = wp_count_comments( $topic_id );
			$total_replies += $comments_count->total_comments;
		}
		$stats['replies'] = $total_replies;

		$last_activity = null;
		$latest_ts = 0;

		$latest_topic = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		) );

		if ( ! empty( $latest_topic ) ) {
			$t = $latest_topic[0];
			$latest_ts = strtotime( $t->post_date );
			$author_id = $t->post_author;
			$last_activity = array(
				'title'     => $t->post_title,
				'timestamp' => $t->post_date,
				'author'    => get_the_author_meta( 'display_name', $author_id ) ?: 'Unknown',
				'avatar'    => get_avatar_url( $author_id, array( 'size' => 48 ) ),
			);
		}

		if ( ! empty( $topics ) ) {
			$latest_comment = get_comments( array(
				'post_id__in' => $topics,
				'number'      => 1,
				'orderby'     => 'comment_date',
				'order'       => 'DESC',
				'status'      => 'all',
			) );

			if ( ! empty( $latest_comment ) ) {
				$c = $latest_comment[0];
				$c_ts = strtotime( $c->comment_date );
				if ( $c_ts > $latest_ts ) {
					$p = get_post( $c->comment_post_ID );
					$last_activity = array(
						'title'     => 'Re: ' . ( $p ? $p->post_title : '' ),
						'timestamp' => $c->comment_date,
						'author'    => $c->comment_author,
						'avatar'    => get_avatar_url( $c->comment_author_email, array( 'size' => 48 ) ),
					);
				}
			}
		}

		$stats['last_activity'] = $last_activity;

		if ( ! empty( $topics ) && $stats['topics'] == 0 ) {
			$stats['topics'] = count( $topics );
		}

		return $stats;
	}

	/**
	 * Compute topic statistics for REST responses
	 */
	public static function get_topic_stats( $post, $field_name, $request ) {
		$topic_id = is_array( $post ) ? $post['id'] : $post->ID;
		$stats = array(
			'replies'       => 0,
			'views'         => 0,
			'last_activity' => null,
		);

		$comments_count = wp_count_comments( $topic_id );
		$stats['replies'] = $comments_count->total_comments;

		$latest_comment = get_comments( array(
			'post_id' => $topic_id,
			'number'  => 1,
			'orderby' => 'comment_date',
			'order'   => 'DESC',
			'status'  => 'all',
		) );

		if ( ! empty( $latest_comment ) ) {
			$c = $latest_comment[0];
			$stats['last_activity'] = array(
				'timestamp' => $c->comment_date,
				'author'    => $c->comment_author,
				'avatar'    => get_avatar_url( $c->comment_author_email, array( 'size' => 48 ) ),
			);
		}

		return $stats;
	}

	/**
	 * GET user subscriptions
	 */
	public static function get_user_subscriptions( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return rest_ensure_response( array( 'subscribed_boards' => array() ) );
		}

		$subs = get_user_meta( $user_id, '_xophz_subscribed_spheres', true );
		if ( ! is_array( $subs ) ) {
			$subs = array();
		}

		return rest_ensure_response( array( 'subscribed_boards' => array_map( 'intval', $subs ) ) );
	}

	/**
	 * POST update user subscriptions
	 */
	public static function update_user_subscriptions( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'unauthorized', 'User must be logged in.', array( 'status' => 401 ) );
		}

		$boards = $request->get_param( 'subscribed_boards' );
		if ( ! is_array( $boards ) ) {
			$boards = array();
		}

		$clean_boards = array_map( 'intval', $boards );
		update_user_meta( $user_id, '_xophz_subscribed_spheres', $clean_boards );

		return rest_ensure_response( array(
			'success'           => true,
			'subscribed_boards' => $clean_boards,
		) );
	}

	/**
	 * GET Global Activity Pulse
	 */
	public static function get_global_pulse( WP_REST_Request $request ) {
		$per_page = min( 50, max( 1, intval( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
		$filter   = $request->get_param( 'filter' ) ?: 'global';

		$query_args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		// If filtering to subscribed boards only
		if ( 'local' === $filter ) {
			$user_id = get_current_user_id();
			$subs = $user_id ? get_user_meta( $user_id, '_xophz_subscribed_spheres', true ) : array();
			if ( empty( $subs ) || ! is_array( $subs ) ) {
				return rest_ensure_response( array(
					'items'       => array(),
					'total'       => 0,
					'total_pages' => 0,
				) );
			}
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $subs ),
				),
			);
		}

		$query = new WP_Query( $query_args );
		$items = array();

		foreach ( $query->posts as $post ) {
			$terms = wp_get_post_terms( $post->ID, self::TAXONOMY );
			$primary_term = ! empty( $terms ) ? $terms[0] : null;
			$comments_count = wp_count_comments( $post->ID );
			$upvotes = intval( get_post_meta( $post->ID, '_xophz_upvotes', true ) ?: 0 );

			$board_icon = $primary_term ? get_term_meta( $primary_term->term_id, 'board_icon', true ) : 'fal fa-comments';

			$items[] = array(
				'id'            => $post->ID,
				'title'         => $post->post_title,
				'detail'        => wp_trim_words( wp_strip_all_tags( $post->post_content ), 25 ),
				'time'          => human_time_diff( strtotime( $post->post_date ), current_time( 'timestamp' ) ) . ' ago',
				'timestamp'     => $post->post_date,
				'communitySlug' => $primary_term ? $primary_term->slug : 'general',
				'communityName' => $primary_term ? $primary_term->name : 'General',
				'icon'          => $board_icon ?: 'fal fa-comments',
				'color'         => 'primary',
				'authorId'      => $post->post_author,
				'authorName'    => get_the_author_meta( 'display_name', $post->post_author ) ?: 'Unknown',
				'upvotes'       => $upvotes,
				'comments'      => $comments_count->total_comments,
			);
		}

		return rest_ensure_response( array(
			'items'       => $items,
			'total'       => $query->found_posts,
			'total_pages' => $query->max_num_pages,
		) );
	}
}
