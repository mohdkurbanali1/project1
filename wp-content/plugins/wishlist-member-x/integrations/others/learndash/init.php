<?php
/**
 * LearnDash init
 *
 * @package WishListMember/OtherProviders
 */

if ( ! class_exists( 'WLM3_LearnDash_Hooks' ) ) {
	/**
	 * WLM3_LearnDash_Hooks classs
	 */
	class WLM3_LearnDash_Hooks {
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_wlm3_learndash_get_data', array( $this, 'get_data' ) );
		}

		/**
		 * Get data.
		 */
		public function get_data() {
			$data = array(
				'status'  => false,
				'message' => '',
				'courses' => array(),
				'groups'  => array(),
			);
			// connect and get info.
			try {
				$data['status']  = true;
				$data['message'] = __( 'LearnDash plugin is isntalled and activated', 'wishlist-member' );
				$the_posts       = new WP_Query(
					array(
						'post_type' => 'sfwd-courses',
						'nopaging'  => true,
					)
				);
				$courses         = array();
				if ( count( $the_posts->posts ) ) {
					foreach ( $the_posts->posts as $c ) {
						$courses[ $c->ID ] = $c->post_title;
					}
					$data['courses'] = $courses;
				} else {
					$data['message'] = 'You need to create a LearnDash course in order proceed';
				}
				$the_groups = new WP_Query(
					array(
						'post_type' => 'groups',
						'nopaging'  => true,
					)
				);
				$groups     = array();
				if ( count( $the_groups->posts ) ) {
					foreach ( $the_groups->posts as $c ) {
						$groups[ $c->ID ] = $c->post_title;
					}
					$data['groups'] = $groups;
				}
			} catch ( \Exception $e ) {
				$data['message'] = $e->getMessage();
			}
			wp_die( wp_json_encode( $data ) );
		}
	}
	new WLM3_LearnDash_Hooks();
}
