<?php
/**
 * Mailerlite init
 *
 * @package WishListMember/Autoresponders
 */

if ( ! class_exists( 'WLM3_MailerLite_Hooks' ) ) {
	/**
	 * WLM3_MailerLite_Hooks class
	 */
	class WLM3_MailerLite_Hooks {
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_wlm3_mailerlite_test_keys', array( $this, 'test_keys' ) );
		}

		/**
		 * Test keys
		 */
		public function test_keys() {
			$data = array(
				'status'  => false,
				'message' => '',
			);

			$api_key = wlm_post_data()['data']['api_key'];
			$save    = wlm_post_data()['data']['save'];

			$transient_name = 'wlmmailerlite_' . md5( $api_key );
			if ( $save ) {
				$ar = wishlistmember_instance()->get_option( 'Autoresponders' );

				$ar['mailerlite']['api_key'] = $api_key;
				wishlistmember_instance()->save_option( 'Autoresponders', $ar );
			} else {
				$transient_result = get_transient( $transient_name );
				if ( $transient_result ) {
					$transient_result['cached'] = 1;
					wp_die( wp_json_encode( $transient_result ) );
				}
			}

			$response = wp_remote_get(
				sprintf( 'https://api.mailerlite.com/api/v2/groups' ),
				array(
					'headers'    => array(
						'X-MailerLite-ApiKey' => $api_key,
					),
					'user-agent' => 'WishList Member/' . wishlistmember_instance()->Version,
				)
			);

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! empty( $body ) && empty( $body->error ) ) {
				$data['status'] = true;
				$data['lists']  = $body;
			} else {
				$data['message'] = $body->error->message;
			}

			set_transient( $transient_name, $data, 60 * 15 );
			wp_die( wp_json_encode( $data ) );
		}
	}
	new WLM3_MailerLite_Hooks();
}
