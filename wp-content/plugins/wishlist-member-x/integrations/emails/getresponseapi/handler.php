<?php

namespace WishListMember\Autoresponders;

class GetResponseAPI {
	public static function user_registered( $user_id, $data ) {
		self::added_to_level( $user_id, array( $data['wpm_id'] ) );
	}

	public static function added_to_level( $user_id, $level_id ) {
		$level_id = wlm_remove_inactive_levels( $user_id, $level_id );
		self::process( $user_id, $level_id, 'added' );
	}

	public static function removed_from_level( $user_id, $level_id ) {
		self::process( $user_id, $level_id, 'removed' );
	}

	public static function uncancelled_from_level( $user_id, $levels ) {
		self::process( $user_id, $levels, 'uncancelled' );
	}

	public static function cancelled_from_level( $user_id, $levels ) {
		self::process( $user_id, $levels, 'cancelled' );
	}

	public static function process( $email_or_id, $levels, $action ) {
		static $ar;

		// get email address
		if ( is_numeric( $email_or_id ) ) {
			$userdata = get_userdata( $email_or_id );
			if ( ! $userdata ) {
				return; // invalid user_id
			}
		} elseif ( filter_var( $email_or_id, FILTER_VALIDATE_EMAIL ) ) {
			$userdata = get_user_by( 'email', $email_or_id );
			if ( ! $userdata ) {
				return; // invalid user_id
			}
		} else {
			return; // email_or_id is neither a valid ID or email address
		}

		// make sure email is not temp
		if ( ! wlm_trim( $userdata->user_email ) || preg_match( '/^temp_[0-9a-f]+/i', $userdata->user_email ) ) {
			return;
		}

		if ( empty( $ar ) ) {
			$ar = ( new \WishListMember\Autoresponder( 'getresponseAPI' ) )->settings;
		}

		// make sure levels is an array
		if ( ! is_array( $levels ) ) {
			$levels = array( $levels );
		}

		foreach ( $levels as $level_id ) {
			$add    = wlm_or( $ar['list_actions'][ $level_id ][ $action ]['add'], '' );
			$remove = wlm_or( $ar['list_actions'][ $level_id ][ $action ]['remove'], '' );
			if ( $add ) {
				self::send_data( $ar, $add, $userdata, false );
			}
			if ( $remove ) {
				self::send_data( $ar, $remove, $userdata, true );
			}
		}
	}

	public static function send_data( $ar, $campaign, $userdata, $unsub = false ) {
		global $wpdb;

		require_once wishlistmember_instance()->plugindir . '/extlib/jsonRPCClient.php';
		require_once wishlistmember_instance()->plugindir . '/extlib/wlm-getresponse-v3.php';

		$name    = wlm_trim( $userdata->first_name . ' ' . $userdata->last_name );
		$email   = wlm_trim( $userdata->user_email );
		$api_key = wlm_trim( $ar['apikey'] );
		$api_url = empty( $ar['api_url'] ) ? 'https://api.getresponse.com/v3' : wlm_trim( $ar['api_url'] );

		$uid = $wpdb->get_var( "SELECT ID FROM {$wpdb->users} WHERE `user_email`='" . esc_sql( $userdata->user_email ) . "'" );
		$ip  = trim( wishlistmember_instance()->Get_UserMeta( $uid, 'wpm_login_ip' ) );
		$ip  = ( $ip ) ? $ip : trim( wishlistmember_instance()->Get_UserMeta( $uid, 'wpm_registration_ip' ) );
		$ip  = ( $ip ) ? $ip : trim( wlm_server_data()['REMOTE_ADDR'] );

		try {
			if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'json' ) ) {
				// these extensions are a must
				throw new \Exception(
					'CURL and JSON are modules required to use'
					. ' the GetResponse Integration'
				);
			}

			if ( false === strpos( $api_url, 'api2' ) ) { // for V3 Users
				$api  = new \WLM_GETRESPONSE_V3( $api_key, $api_url );
				$resp = $api->getCampaigns();
				if ( isset( $resp->httpStatus ) ) {
					throw new \Exception( 'Unable to connect to API:' . $resp->message );
				}
				$cid = null;
				foreach ( $resp as $i => $item ) {
					if ( strtolower( $item->name ) == strtolower( $campaign ) ) {
						$cid = $item->campaignId;
					}
				}
				if ( empty( $cid ) ) {
					throw new \Exception( "Could not find campaign $campaign" );
				}

				if ( $unsub ) {
					// list contacts
					$params   = array(
						'query' => array(
							'campaignId' => $cid,
							'email'      => $email,
						),
					);
					$contacts = $api->getContacts( $params );
					$contacts = (array) $contacts;
					$contact  = is_array( $contacts ) && isset( $contacts[0] ) ? $contacts[0] : false;
					if ( ! $contact || ! isset( $contact->email ) || ! isset( $contact->contactId ) ) {
						return; // could not find the contact, nothing to remove
					}
					if ( $contact->email == $email ) {
						$params = array(
							'ipAddress' => $ip,
						);
						$resp   = $api->deleteContact( $contact->contactId, $params );
					}
				} else {
					// CHECK FOR DUPLICATE, remove it for now to save api call
					// $params = array(
					// 'query' => array('campaignId'=>$cid,'email'=>$email)
					// );
					// $contacts = $api->getContacts($params);
					// $contacts = (array) $contacts;
					// $contact = is_array($contacts) && isset($contacts[0]) ? $contacts[0] : false;
					// if ( $contact && isset($contact->email) && $contact->email == $email )  return; #duplicate
					$params = array(
						'name'       => $name,
						'email'      => $email,
						'campaign'   => array( 'campaignId' => $cid ),
						'dayOfCycle' => 0,
						'ipAddress'  => $ip,
					);
					$resp   = $api->addContact( $params );
				}
			} else { // for v2 Users
				$api = new \jsonRPCClient( $api_url );
				// get the campaign id
				$resp = $api->get_campaigns( $api_key );
				$cid  = null;
				if ( ! empty( $resp ) ) {
					foreach ( $resp as $i => $item ) {
						if ( strtolower( $item['name'] ) == strtolower( $campaign ) ) {
							$cid = $i;
						}
					}
				}
				if ( empty( $cid ) ) {
					throw new \Exception( "Could not find campaign $campaign" );
				}

				if ( $unsub ) {
					// list contacts
					$contacts = $api->get_contacts(
						$api_key,
						array(
							'campaigns' => array( $cid ),
							'email'     => array( 'EQUALS' => "$email" ),
						)
					);
					if ( empty( $contacts ) ) {
						// could not find the contact, nothing to remove
						return;
					}
					$pid = key( $contacts );
					$res = $api->delete_contact( $api_key, array( 'contact' => $pid ) );
					if ( empty( $res ) ) {
						throw new \Exception( 'Empty server response while deleting contact' );
					}
				} else {

					// prepare data
					$data = array(
						'campaign'  => wlm_trim( $cid ),
						'name'      => wlm_trim( $name ),
						'email'     => wlm_trim( $email ),
						'ip'        => wlm_trim( $ip ),
						'cycle_day' => 0,
					);

					// remove empty items - getResponse don't like it
					$data = array_diff( $data, array( '', null ) );

					$resp = $api->add_contact( $api_key, $data );
					if ( empty( $resp ) ) {
						throw new \Exception( 'Empty server response while sending' );
					}
				}
			}
		} catch ( \Exception $e ) {
			return;
		}
	}

}
