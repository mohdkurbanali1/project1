<?php
/*
 * Zapier Autoresponder Integration Functions
 *
 * @package WishListMember
 */

if ( ! class_exists( 'WLM_OTHER_INTEGRATION_ZAPIER' ) ) {
	/**
	 * WLM_OTHER_INTEGRATION_ZAPIER class
	 */
	class WLM_OTHER_INTEGRATION_ZAPIER {
		/**
		 * Takes care of authentication checking and parsing requests
		 */
		public static function Zapier() {
			// no need to do anything if the our auth is not present.
			if ( ! isset( wlm_server_data()['HTTP_X_WLMZAPIERAUTH'] ) ) {
				return;
			}
			$qstring = urldecode( wlm_server_data()['QUERY_STRING'] );
			if ( false !== strpos( $qstring, '/zapier/' ) ) {
				$url_parts = explode( '/zapier/', $qstring );
				$url_parts = explode( '/', $url_parts[1] );
				if ( method_exists( __CLASS__, $url_parts[0] ) && '_' !== substr( $url_parts[0], 0, 1 ) ) {
					if ( ! self::_auth() ) {
						http_response_code( 401 );
						wp_send_json( array( 'message' => 'Invalid WishList Member Zapier Key' ) );
					}
					$data = array(
						'payload'   => file_get_contents( 'php://input' ),
						'url_parts' => $url_parts,
					);
					header( 'Content-type: application/json' );
					list($data, $response_code) = call_user_func( array( __CLASS__, $url_parts[0] ), $data );
					http_response_code( $response_code ? $response_code : 404 );
					if ( empty( $data ) ) {
						$data = new \stdClass();
					}
					if ( is_string( $data ) ) {
						$data = json_decode( $data );
					}
					wp_send_json( $data );
					exit;
				} else {
					http_response_code( 404 );
					wp_send_json( array( 'message' => 'Invalid request made to the WishList Member Zapier Integration' ) );
				}
			}
		}

		/**
		 * Checks if we request is authenticated
		 *
		 * @return boolean
		 */
		public static function _auth() {
			$zapier_settings = (array) wishlistmember_instance()->get_option( 'zapier_settings' );
			return wlm_server_data()['HTTP_X_WLMZAPIERAUTH'] === $zapier_settings['key'];
		}

		/**
		 * Gets webhook URLs
		 *
		 * @param  string $event Event.
		 * @return array
		 */
		public static function _get_zap_urls( $event = '' ) {
			$zapier_urls = wishlistmember_instance()->get_option( 'zapier_urls' );

			if ( empty( $zapier_urls ) ) {
				return array();
			}
			if ( empty( $event ) ) {
				return $zapier_urls;
			}
			if ( empty( $zapier_urls[ $event ] ) ) {
				return array();
			}
			return $zapier_urls[ $event ];
		}

		/**
		 * Adds a webhook URL
		 *
		 * @param  string $url   Zap URL.
		 * @param  string $event Event.
		 * @return boolean
		 */
		public static function _add_zap_url( $url, $event ) {
			$urls = self::_get_zap_urls();
			if ( empty( $urls ) ) {
				$urls = array();
			}

			if ( empty( $urls[ $event ] ) ) {
				$urls[ $event ] = array();
			}

			$u     = $urls[ $event ];
			$count = count( $u );
			$u[]   = $url;
			$u     = array_unique( $u );
			if ( count( $u ) > $count ) {
				$urls[ $event ] = $u;
				wishlistmember_instance()->save_option( 'zapier_urls', $urls );
				return true;
			}
			return false;
		}

		/**
		 * Deletes a webhook URL
		 *
		 * @param string $url Zap URL.
		 */
		public static function _delete_zap_url( $url ) {
			$urls = self::_get_zap_urls();

			foreach ( $urls as $event => $u ) {
				$u = array_diff( $u, array( $url ) );
				if ( empty( $u ) ) {
					unset( $urls[ $event ] );
				} else {
					$urls[ $event ] = $u;
				}
			}
			wishlistmember_instance()->save_option( 'zapier_urls', $urls );
		}

		/**
		 * Sends data to webhook URL
		 *
		 * @param  string $data  Data.
		 * @param  string $event Event.
		 */
		public static function _zap( $data, $event ) {
			static $_zaps = array();

			$urls = self::_get_zap_urls( $event );
			if ( empty( $urls ) ) {
				return;
			}

			$data = wp_json_encode( $data );

			foreach ( $urls as $url ) {
				// prevent duplicate zaps from being sent.
				$_zap_hash = md5( $url . $data );
				if ( in_array( $_zap_hash, $_zaps, true ) ) {
					continue;
				}
				$_zaps[] = $_zap_hash;

				$result = wp_remote_post(
					$url,
					array(
						'method'  => 'POST',
						'headers' => array( 'Content-type' => 'application/json' ),
						'body'    => $data,
					)
				);
				if ( ! is_wp_error( $result ) && 410 === (int) $result['response']['code'] ) {
					self::_delete_zap_url( $url );
				}
			}
		}

		/**
		 * Handles subscribe requests
		 *
		 * @param  array $data Data.
		 * @return array
		 */
		public static function subscribe( $data ) {
			extract( $data, EXTR_SKIP );
			$payload = json_decode( $payload );
			if ( empty( $payload->target_url ) ) {
				return array( array(), 409 );
			}
			$event = $payload->event;
			if ( isset( $payload->level_id ) ) {
				$event .= '|' . $payload->level_id;
			}
			if ( ! self::_add_zap_url( $payload->target_url, $event ) ) {
				return array( array(), 409 );
			}
			return array( array( $user ), 201 );
		}

		/**
		 * Handles unsubscribe requests
		 *
		 * @param  array $data Data.
		 */
		public static function unsubscribe( $data ) {
			extract( $data, EXTR_SKIP );
			$payload = json_decode( $payload );
			self::_delete_zap_url( $payload->target_url );
		}

		/**
		 * Handles poll requests for testing connectivity
		 * Note: This returns data from a randomly chosen
		 *
		 * @param  array $data Data.
		 * @return array
		 */
		public static function user_poll_test( $data ) {
			global $wpdb;

			$poll_id = $data['url_parts'][1];

			if ( ! empty( $data['payload'] ) ) {
				$payload = json_decode( $data['payload'] );
				if ( isset( $payload->level_id ) ) {
					$rand_id = $wpdb->get_var( $wpdb->prepare( 'select user_id from %0s where level_id = %s order by rand() limit 1', wishlistmember_instance()->table_names->userlevels, $payload->level_id ) );
				}
			}

			if ( empty( $rand_id ) ) {
				$rand_id = $wpdb->get_var( 'select ID from ' . $wpdb->users . ' order by rand() limit 1' );
			}

			if ( empty( $rand_id ) ) {
				return array( array(), 200 );
			}
			$user = self::_get_user( $rand_id );

			$data = array( 'id' => $poll_id . '-' . $rand_id ) + $user;

			if ( in_array(
				$poll_id,
				array(
					'member_added_to_level',
					'member_removed_from_level',
					'member_cancelled_from_level',
					'member_uncancelled_from_level',
				),
				true
			) ) {
				$data['levels'] = array_values( ( new \WishListMember\User( $rand_id, false ) )->Levels );
				if ( $data['levels'] ) {
					$data['level_id']   = $data['levels'][0]->Level_ID;
					$data['level_name'] = $data['levels'][0]->Name;
				} else {
					$data['level_id']   = '';
					$data['level_name'] = '';
				}
			}
			return array( array( $data ), 200 );
		}

		/**
		 * Poll URL for ping requests from Zapier
		 *
		 * @param  array $data Data.
		 * @return array
		 */
		public static function ping( $data ) {
			return array(
				array(
					'message' => 'pinged',
					'date'    => gmdate( 'r' ),
					'site'    => get_bloginfo( 'url' ),
					'version' => WLM_PLUGIN_VERSION,
				),
				200,
			);
		}

		/**
		 * Poll URL for levels requests from Zapier
		 *
		 * @param  array $data Data.
		 * @return array
		 */
		public static function levels( $data ) {
			$wpm_levels = self::_get_levels();
			$levels     = array();
			foreach ( $wpm_levels as $level_id => $level ) {
				$levels[] = array(
					'level_id'   => $level_id,
					'level_name' => $level['name'],
				);
			}
			return array( $levels, 200 );
		}

		/**
		 * Zapier Action : Add Member
		 *
		 * @param array $data Data.
		 * @return array
		 */
		public static function add_member( $data ) {
			extract( $data, EXTR_SKIP );
			$payload = json_decode( $payload );

			if ( ! filter_var( $payload->email, FILTER_VALIDATE_EMAIL ) ) {
				return array( array( 'message' => __( 'Invalid email address', 'wishlist-member' ) ), 400 );
			}

			// prepare user data for adding.
			$user_data = array(
				'user_login' => $payload->login,
				'user_email' => $payload->email,
				'user_pass'  => empty( $payload->password ) ? wishlistmember_instance()->pass_gen( 12 ) : $payload->password,
			);

			if ( ! empty( $payload->full_name ) ) {
				$full                    = explode( ' ', trim( preg_replace( '/[\s]+/', ' ', $payload->full_name ) ), 2 );
				$user_data['first_name'] = $full[0];
				if ( isset( $full[1] ) ) {
					$user_data['last_name'] = $full[1];
				}
			}
			// add first name and last name to user data if provided.
			if ( ! empty( $payload->first_name ) ) {
				$user_data['first_name'] = $payload->first_name;
			}
			if ( ! empty( $payload->last_name ) ) {
				$user_data['last_name'] = $payload->last_name;
			}

			// insert user.
			$user_data['Sequential'] = 1;

			if ( ! empty( $payload->level_id ) ) {
				$user_data['Levels'] = array( array( $payload->level_id, empty( $payload->transaction_id ) ? '' : $payload->transaction_id ) );
			}
			if ( ! empty( $payload->send_email ) ) {
				$user_data['SendMailPerLevel'] = 1;
			}

			$update_id = false;
			if ( ! empty( $payload->update_user_if_existing ) ) {
				$update_id = email_exists( $payload->email );
			}

			foreach ( array( 'company', 'address1', 'address2', 'city', 'state', 'zip', 'country' ) as $address_field ) {
				if ( ! empty( $payload->$address_field ) ) {
					$user_data[ $address_field ] = $payload->$address_field;
				}
			}

			foreach ( array( 'phone_home', 'phone_work', 'phone_mobile' ) as $address_field ) {
				if ( ! empty( $payload->$address_field ) ) {
					$user_data[ 'custom_' . $address_field ] = $payload->$address_field;
				}
			}

			if ( $update_id ) {
				$user_data2 = $user_data;
				unset( $user_data2['user_login'] );
				unset( $user_data2['user_email'] );
				unset( $user_data2['user_pass'] );
				$result = wlmapi_update_member( $update_id, $user_data2 );
			} else {
				$result = wlmapi_add_member( $user_data );
			}

			if ( ! $result['success'] ) {
				return array( array( 'message' => $result['ERROR'] ), 409 );
			}

			// return user info.
			$user = self::_get_user( $result['member'][0]['ID'] );

			// include levels to be returned.
			$user['levels'] = array_values( ( new \WishListMember\User( $user['user_id'], false ) )->Levels );

			return array( array( $user ), 200 );
		}

		/**
		 * Zapier Action : Add Member To Level
		 *
		 * @uses self::_level_management
		 * @param array $data Data.
		 * @return array
		 */
		public static function add_member_to_level( $data ) {
			return self::_level_management( $data, __FUNCTION__ );
		}

		/**
		 * Zapier Action : Remove Member From Level
		 *
		 * @uses self::_level_management
		 * @param array $data Data.
		 * @return array
		 */
		public static function remove_member_from_level( $data ) {
			return self::_level_management( $data, __FUNCTION__ );
		}

		/**
		 * Zapier Action : Cancel Member From Level
		 *
		 * @uses self::_level_management
		 * @param array $data Data.
		 * @return array
		 */
		public static function cancel_member_from_level( $data ) {
			return self::_level_management( $data, __FUNCTION__ );
		}

		/**
		 * Zapier Action : UnCancel Member From Level
		 *
		 * @uses self::_level_management
		 * @param array $data Data.
		 * @return array
		 */
		public static function uncancel_member_from_level( $data ) {
			return self::_level_management( $data, __FUNCTION__ );
		}

		/**
		 * Helper Function for the following Zapier Actions:
		 * - Add Member To Level
		 * - Remove Member From Level
		 * - Cancel Member From Level
		 * - UnCancel Member From Level
		 *
		 * @param array  $data Data.
		 * @param string $action Action.
		 * @return array
		 */
		public static function _level_management( $data, $action ) {
			extract( $data, EXTR_SKIP );
			$payload = json_decode( $payload );

			// grab user info from email.
			$user = self::_get_user( $payload->email, 'email' );
			if ( empty( $user ) ) {
				// return if email does not match a user.
				return array( array( 'message' => __( 'Email address not found in WishList Member', 'wishlist-member' ) ), 404 );
			}
			$user_id = $user['user_id'];

			// do requested action.
			switch ( $action ) {
				case 'add_member_to_level':
					$data = array(
						'Users' => $user_id,
						'TxnID' => empty( $payload->transaction_id ) ? '' : $payload->transaction_id,
					);
					wlmapi_add_member_to_level( $payload->level_id, $data );
					break;
				case 'remove_member_from_level':
					wlmapi_remove_member_from_level( $payload->level_id, $user_id );
					break;
				case 'cancel_member_from_level':
					wlmapi_update_level_member_data( $payload->level_id, $user_id, array( 'Cancelled' => '1' ) );
					break;
				case 'uncancel_member_from_level':
					wlmapi_update_level_member_data( $payload->level_id, $user_id, array( 'Cancelled' => '0' ) );
					break;
			}

			// include levels to be returned.
			$user['levels'] = array_values( ( new \WishListMember\User( $user_id, false ) )->Levels );

			return array( array( $user ), 200 );
		}

		/**
		 * Check if email is a temporary email
		 *
		 * @param  string $email Email address.
		 * @return boolean
		 */
		public static function _is_temp_email( $email ) {
			return preg_match( '/^temp_[0-9a-f]{32}$/', $email );
		}

		/**
		 * Get user and return info that we need
		 *
		 * @uses   get_user_by
		 * @param  integer $user_id User ID.
		 * @param  string  $field id, login or email.
		 * @return array
		 */
		public static function _get_user( $user_id, $field = 'id' ) {
			$user = get_user_by( $field, $user_id );
			if ( ! $user ) {
				return array();
			}

			$data = array(
				'user_id'    => $user->ID,
				'login'      => $user->user_login,
				'email'      => $user->user_email,
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
			);
			return $data;
		}


		/**
		 * Action: wishlistmember_user_registered
		 *
		 * Action called when a new member is registered to WishList Member
		 *
		 * @param integer $user_id User ID.
		 * @param array   $data Data.
		 * @param mixed   $merge_with Merge width ID.
		 */
		public static function _new_wishlist_member( $user_id, $data, $merge_with = '' ) {
			if ( ! $merge_with ) {
				self::_new_member( $user_id );
				self::_wlmhook_add_levels( $user_id, array( $data['wpm_id'] ) );
			}
		}

		/**
		 * Action called when a new user is registered to WordPress
		 *
		 * @param  integer $user_id User ID.
		 */
		public static function _new_member( $user_id ) {
			$event = 'new_member';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			self::_zap( array( $data ), $event );
		}

		/**
		 * Action called when a user is deleted from WordPress
		 *
		 * @param  integer $user_id User ID.
		 */
		public static function _remove_member( $user_id ) {
			$event = 'remove_member';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			self::_zap( array( $data ), $event );
		}

		/**
		 * Return membership levels
		 *
		 * @return array
		 */
		public static function _get_levels() {
			static $levels = null;

			if ( is_null( $levels ) ) {
				$levels = wishlistmember_instance()->get_option( 'wpm_levels' );
				if ( ! $levels ) {
					$levels = array();
				}
			}

			return $levels;
		}

		/**
		 * Action called when a user is added to one or more membership levels in WishList Member
		 *
		 * @param  integer $user_id User ID.
		 * @param  array   $levels  Array of membership levels.
		 */
		public static function _wlmhook_add_levels( $user_id, $levels ) {
			$event = 'member_added_to_level';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			$wpm_levels = self::_get_levels();
			foreach ( $levels as $level ) {
				$data['level_id']   = $level;
				$data['level_name'] = $wpm_levels[ $level ]['name'];
				$data['levels']     = array_values( ( new \WishListMember\User( $user_id, false ) )->Levels );
				self::_zap( array( $data ), $event );
				self::_zap( array( $data ), $event . '|' . $level );
			}
		}

		/**
		 * Action called when a user is removed from one or more membership levels in WishList Member
		 *
		 * @param  integer $user_id User ID.
		 * @param  array   $levels  array of membership levels.
		 */
		public static function _wlmhook_remove_levels( $user_id, $levels ) {
			$event = 'member_removed_from_level';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			$wpm_levels = self::_get_levels();
			foreach ( $levels as $level ) {
				$data['level_id']   = $level;
				$data['level_name'] = $wpm_levels[ $level ]['name'];
				$data['levels']     = array_values( ( new \WishListMember\User( $user_id, false ) )->Levels );
				self::_zap( array( $data ), $event );
				self::_zap( array( $data ), $event . '|' . $level );
			}
		}

		/**
		 * Action called when a user is cancelled from one or more membership levels in WishList Member
		 *
		 * @param  integer $user_id User ID.
		 * @param  array   $levels  Array of membership levels.
		 */
		public static function _wlmhook_cancel_levels( $user_id, $levels ) {
			$event = 'member_cancelled_from_level';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			$wpm_levels = self::_get_levels();
			foreach ( $levels as $level ) {
				$data['level_id']   = $level;
				$data['level_name'] = $wpm_levels[ $level ]['name'];
				$data['levels']     = array_values( ( new \WishListMember\User( $user_id, false ) )->Levels );
				self::_zap( array( $data ), $event );
				self::_zap( array( $data ), $event . '|' . $level );
			}
		}

		/**
		 * Action called when a user is uncancelled from one or more membership levels in WishList Member
		 *
		 * @param  integer $user_id User ID.
		 * @param  array   $levels  Array of membership levels.
		 */
		public static function _wlmhook_uncancel_levels( $user_id, $levels ) {
			$event = 'member_uncancelled_from_level';
			$data  = self::_get_user( $user_id );
			if ( self::_is_temp_email( $data['email'] ) ) {
				return;
			}
			$data['id'] = sprintf( '%s-%d', $event, $user_id );
			$wpm_levels = self::_get_levels();
			foreach ( $levels as $level ) {
				$data['level_id']   = $level;
				$data['level_name'] = $wpm_levels[ $level ]['name'];
				$data['levels']     = array_values( ( new \WishListMember\User( $user_id, false ) )->Levels );
				self::_zap( array( $data ), $event );
				self::_zap( array( $data ), $event . '|' . $level );
			}
		}

		/**
		 * Function to remove hooks that's been set.
		 */
		public static function remove_hooks() {
			// hook to take care requests from zapier.
			remove_action( 'plugins_loaded', array( 'WLM_OTHER_INTEGRATION_ZAPIER', 'Zapier' ) );

			// hooks when a user is registered or deleted.
			remove_action( 'user_register', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_new_member' ), 10 );
			remove_action( 'delete_user', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_remove_member' ), 10 );

			// hooks when a user is added to or removed from membership levels.
			remove_action( 'wishlistmember_user_registered', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_new_wishlist_member' ), 10 );
			remove_action( 'wishlistmember_add_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_add_levels' ), 10 );
			remove_action( 'wishlistmember_remove_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_remove_levels' ), 10 );

			// hooks when a user is cancelled or uncancelled from membership levels.
			remove_action( 'wishlistmember_cancel_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_cancel_levels' ), 10 );
			remove_action( 'wishlistmember_uncancel_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_uncancel_levels' ), 10 );
		}

		/**
		 * Function to set hooks.
		 */
		public static function set_hooks() {
			// hook to take care requests from zapier.
			add_action( 'plugins_loaded', array( 'WLM_OTHER_INTEGRATION_ZAPIER', 'Zapier' ) );

			// hooks when a user is registered or deleted.
			add_action( 'wishlistmember_user_registered', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_new_wishlist_member' ), 10, 3 );
			add_action( 'user_register', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_new_member' ), 10, 1 );
			add_action( 'delete_user', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_remove_member' ), 10, 1 );

			// hooks when a user is added to or removed from membership levels.
			add_action( 'wishlistmember_add_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_add_levels' ), 10, 2 );
			add_action( 'wishlistmember_remove_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_remove_levels' ), 10, 2 );

			// hooks when a user is cancelled or uncancelled from membership levels.
			add_action( 'wishlistmember_cancel_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_cancel_levels' ), 10, 2 );
			add_action( 'wishlistmember_uncancel_user_levels', array( 'WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_uncancel_levels' ), 10, 2 );

			add_action( 'wishlistmember_suppress_other_integrations', array( 'WLM_OTHER_INTEGRATION_ZAPIER', 'remove_hooks' ) );
		}
	}

	WLM_OTHER_INTEGRATION_ZAPIER::set_hooks();
}
