<?php
/**
 * Handler for Evidence integration
 * Author: Mike Lopez <mike@wishlistproducts.com>
 */

if ( ! class_exists( 'WLM_OTHER_INTEGRATION_EVIDENCE' ) ) {
	class WLM_OTHER_INTEGRATION_EVIDENCE {
		public $levels = array();
		public $config = array();

		public function __construct() {
			// load membership levels
			$this->levels = wishlistmember_instance()->get_option( 'wpm_levels' );
			// load evidence settings
			$this->config = wishlistmember_instance()->get_option( 'evidence_settings' );

			// hooks
			add_action( 'wishlistmember_add_user_levels', array( $this, 'add_to_level' ), 10, 3 );
			add_action( 'wishlistmember_user_registered', array( $this, 'new_wishlist_member' ), 10, 3 );
		}

		public function new_wishlist_member( $user_id, $data = array(), $merge_with = '' ) {
			if ( ! $merge_with ) {
				$this->add_to_level( $user_id, array( $data['wpm_id'] ), array() );
			}
		}

		/**
		 * Called by `wishlistmember_add_user_levels` hook
		 */
		public function add_to_level( $user_id, $new_levels, $removed_levels ) {

			// go through each new level
			foreach ( $new_levels as $level ) {
				$webhook_url = $this->config['webhook_url'];

				$test = false;
				if ( 'wlm3-evidence-webhook-test' == $user_id ) {
					$user_id = get_current_user_id();
					$test    = true;
				}
				// skip if the level is not active in Evidence
				if ( empty( $this->config['active'][ $level ] ) && ! $test ) {
					continue;
				}

				// initialize data only if empty
				if ( empty( $data ) ) {
					$user            = get_userdata( $user_id );
					$data            = array(
						'first_name' => $user->first_name,
						'last_name'  => $user->last_name,
						'email'      => $user->user_email,
					);
					$address         = wishlistmember_instance()->Get_UserMeta( $user_id, 'wpm_useraddress' );
					$data['city']    = (string) wlm_arrval( $address, 'city' );
					$data['state']   = (string) wlm_arrval( $address, 'state' );
					$data['zip']     = (string) wlm_arrval( $address, 'zip' );
					$data['country'] = (string) wlm_arrval( $address, 'country' );
				}

				// do not process temp emails
				if ( preg_match( '/^temp_[0-9a-f]{32}$/', $data['email'] ) ) {
					break;
				}

				if ( $test && 'wlm3-evidence-webhook-test' == $level ) {
					// test data
					// level name
					$data['level_name'] = 'Webhook Test Level';
					// add configured custom texts to data
					$data['custom_text_1'] = 'Custom Text #1';
					$data['custom_text_2'] = 'Custom Text #2';

				} else {
					// add level name to data
					$data['level_name'] = (string) $this->levels[ $level ]['name'];
					// add configured custom texts to data
					$data['custom_text_1'] = (string) $this->config['custom_text_1'][ $level ];
					$data['custom_text_2'] = (string) $this->config['custom_text_2'][ $level ];

					if ( ! empty( $this->config['custom_webhook_enabled'][ $level ] ) && wlm_trim( $this->config['custom_webhook_url'][ $level ] ) ) {
						$webhook_url = $this->config['custom_webhook_url'][ $level ];
					}
				}
				// convert data to json
				$body = wp_json_encode( $data );

				// prepare post options
				$options = array(
					'body'        => $body,
					'headers'     => array( 'Content-Type' => 'application/json' ),
					'blocking'    => false,
					'data_format' => 'body',
				);
				// send post
				wp_remote_post( $webhook_url, $options );
			}
		}

	}
	new WLM_OTHER_INTEGRATION_EVIDENCE();
}
