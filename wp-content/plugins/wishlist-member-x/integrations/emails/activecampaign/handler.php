<?php

namespace WishListMember\Autoresponders;

if ( ! class_exists( 'ActiveCampaign\SDK' ) ) {
	require_once __DIR__ . '/lib/sdk.php';
}

class ActiveCampaign {
	public static function sdk() {
		static $ac;
		if ( ! empty( $ac ) ) {
			return $ac;
		}
		if ( empty( $ar ) ) {
			$ar      = ( new \WishListMember\Autoresponder( 'activecampaign' ) )->settings;
			$api_url = wlm_trim( wlm_arrval( $ar, 'api_url' ) );
			$api_key = wlm_trim( wlm_arrval( $ar, 'api_key' ) );
		}
		// sdk
		$ac = new ActiveCampaign\SDK( $api_url, $api_key );
		return $ac;
	}

	/**
	 * Process incoming webhooks triggered
	 * by tag adds/removes in ActiveCampaign
	 */
	public static function process_webhooks() {
		if ( wlm_get_data()['wishlist-member-activecampaign-webhook'] ) {
			$data = wlm_post_data( true );

			// activecampaign settings
			$activecampaign_settings = new \WishListMember\Autoresponder( 'activecampaign' );

			// no actions for tag
			$tag_settings = wlm_arrval( $activecampaign_settings, 'settings', 'tag_actions', wlm_arrval( $data, 'tag' ) );
			if ( empty( $tag_settings ) ) {
				return;
			}

			$map = array(
				'contact_tag_added'   => 'add',
				'contact_tag_removed' => 'remove',
			);

			$action = wlm_arrval( $map, wlm_arrval( $data, 'type' ) );

			$user = get_user_by( 'email', wlm_arrval( $data, 'contact', 'email' ) );
			if ( false === $user ) {

				// If email is not a WP user then check if action is add (contact_tag_added).
				// We also only want to add the user account if the tag action has an Add to Level or Add to PPP configured.
				if ( ! empty( $tag_settings[ $action ]['add_level'][0] ) || ! empty( $tag_settings[ $action ]['add_ppp'][0] ) ) {
					$add_level_or_ppp = true;
				}

				if ( 'add' === $action && $add_level_or_ppp ) {
					// If user doesn't exist, pass an array containing new member's data which is needed for creating the user later.
					$user = array(
						'user_login' => $data['contact']['email'],
						'user_email' => $data['contact']['email'],
						'first_name' => $data['contact']['first_name'],
						'last_name'  => $data['contact']['last_name'],
						'user_pass'  => wishlistmember_instance()->pass_gen(),
					);
				} else {
					return;
				}
			}

			self::process_tag_actions( $user, $tag_settings[ $action ] );
		}
	}

	/**
	 * Return our webhook URL
	 *
	 * @return string URL
	 */
	public static function webhook_url() {
		static $url;
		if ( empty( $url ) ) {
			$url = admin_url( '?wishlist-member-activecampaign-webhook=1' );
		}
		return $url;
	}

	/**
	 * Add our webhook to ActiveCampaign
	 * if it is not yet there
	 */
	public static function add_tag_webhooks() {
		// get configured tag actions
		$activecampaign_settings = new \WishListMember\Autoresponder( 'activecampaign' );
		if ( empty( wlm_arrval( $activecampaign_settings, 'settings', 'tag_actions' ) ) ) {
			// no tag actions configured, return
			return;
		}

		// look for our webhook
		$webhooks = self::sdk()->get_webhooks();
		foreach ( $webhooks as $webhook ) {
			if ( wlm_arrval( $webhook, 'url' ) == self::webhook_url() ) {
				// webhook found, return
				return;
			}
		}

		// webhook not found, add it
		self::sdk()->add_webhook(
			self::webhook_url(), // webhook URL
			'WishList Member @ ' . site_url(), // webhook name
			array( 'contact_tag_added', 'contact_tag_removed' ) // actions to monitor
		);
	}

	/**
	 * Delete tag action and remove the
	 * webhook in ActiveCampaign if there
	 * are no more more tag actions found
	 */
	public static function delete_tag_action() {
		$activecampaign_settings = new \WishListMember\Autoresponder( 'activecampaign' );
		$tag_id                  = wlm_post_data()['tag_id'];
		try {
			unset( $activecampaign_settings->settings['tag_actions'][ wlm_post_data()['tag_id'] ] );
			if ( empty( wlm_arrval( $activecampaign_settings, 'settings', 'tag_actions' ) ) ) {
				$webhooks = self::sdk()->get_webhooks();
				if ( $webhooks ) {
					foreach ( $webhooks as $webhook ) {
						if ( wlm_arrval( $webhook, 'url' ) == self::webhook_url() ) {
							self::sdk()->remove_webhook( wlm_arrval( $webhook, 'id' ) );
							break;
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			null;
		}
		$activecampaign_settings->save_settings();
		wp_send_json_success();
	}

	/**
	 * Process ActiveCampaign tag actions
	 *
	 * @param  \WP_User $user         WP_User object
	 * @param  array    $tag_settings Tag action settings
	 */
	public static function process_tag_actions( $user, $tag_settings ) {
		// go through each tag action
		foreach ( array( 'add_level', 'remove_level', 'cancel_level', 'uncancel_level', 'add_ppp', 'remove_ppp' ) as $action ) {
			$levels = array_diff( $tag_settings[ $action ], array( '' ) );
			if ( $levels ) {
				// prepend "payperpost-" to level IDs if we're dealing with payperpost
				if ( '_ppp' == substr( $action, -4 ) ) {
					array_walk(
						$levels,
						function( &$v, $k ) {
							$v = 'payperpost-' . $v;
						}
					);
				}

				switch ( $action ) {
					case 'add_ppp':
					case 'add_level':
						$add_args = array(
							'ObeyRegistrationRequirements' => 'add_level' == $action,
							'Levels'                       => $levels,
							'SendMailPerLevel'             => 1,
						);
						// Check if user ID is set and if not, create the member first using the passed user data.
						if ( isset( $user->ID ) ) {
							wlmapi_update_member( $user->ID, $add_args );
						} else {
							$member     = array_merge( $user, $add_args );
							$add_result = wlmapi_add_member( $member );
							// If adding is successful, update "$user" values to WP user object.
							if ( $add_result['success'] ) {
								$user = get_user_by( 'email', $member['user_email'] );
							}
						}
						unset( $add_args );
						break;
					case 'remove_ppp':
					case 'remove_level':
						wlmapi_update_member( $user->ID, array( 'RemoveLevels' => $levels ) );
						break;
					case 'cancel_level':
					case 'uncancel_level':
						foreach ( $levels as $level ) {
							wlmapi_update_level_member_data( $level, $user->ID, array( 'Cancelled' => 'cancel_level' === $action ) );
						}
						break;
				}
			}
		}
	}


	/**
	 * Triggers add to level action when user is registered
	 *
	 * @wp-hook wishlistmember_user_registered
	 * @param  integer $user_id User ID
	 * @param  array   $data    Registration data
	 */
	public static function user_registered( $user_id, $data ) {
		self::added_to_level( $user_id, array( $data['wpm_id'] ) );
	}

	/**
	 * Process ActiveCampaign actions when a user is added, confirmed or approved to a level
	 *
	 * @wp-hook wishlistmember_add_user_levels_shutdown
	 * @wp-hook wishlistmember_confirm_user_levels
	 * @wp-hook wishlistmember_approve_user_levels
	 *
	 * @param  int    $user_id  User ID
	 * @param  string $level_id Membership Level ID
	 */
	public static function added_to_level( $user_id, $level_id ) {
		$level_id = wlm_remove_inactive_levels( $user_id, $level_id );
		self::process_level_actions( $user_id, $level_id, 'added' );
	}

	/**
	 * Process ActiveCampaign actions when a user is removed from a level
	 *
	 * @wp-hook wishlistmember_remove_user_levels
	 *
	 * @param  int    $user_id  User ID
	 * @param  string $level_id Membership Level ID
	 */
	public static function removed_from_level( $user_id, $level_id ) {
		self::process_level_actions( $user_id, $level_id, 'removed' );
	}

	/**
	 * Process ActiveCampaign actions when a user is uncancelled from a level
	 *
	 * @wp-hook wishlistmember_uncancel_user_levels
	 * @param  int   $user_id User ID
	 * @param  array $levels  Membership Level IDs
	 */
	public static function uncancelled_from_level( $user_id, $levels ) {
		self::process_level_actions( $user_id, $levels, 'uncancelled' );
	}

	/**
	 * Process ActiveCampaign actions when a user is cancelled from a level
	 *
	 * @wp-hook wishlistmember_cancel_user_levels
	 * @param  int   $user_id User ID
	 * @param  array $levels  Membership Level IDs
	 */
	public static function cancelled_from_level( $user_id, $levels ) {
		self::process_level_actions( $user_id, $levels, 'cancelled' );
	}

	/**
	 * Process level actions
	 *
	 * @param  string|int   $email_or_id Email address or User ID
	 * @param  string|array $levels      Membership Level ID or array of Membership Level IDs
	 * @param  string       $action      Action to process
	 */
	public static function process_level_actions( $email_or_id, $levels, $action ) {
		// get email address
		if ( is_numeric( $email_or_id ) ) {
			$userdata = get_userdata( $email_or_id );
		} elseif ( filter_var( $email_or_id, FILTER_VALIDATE_EMAIL ) ) {
			$userdata = get_user_by( 'email', $email_or_id );
		} else {
			return; // email_or_id is neither a valid ID or email address
		}
		if ( ! $userdata ) {
			return;
		}
		$email = $userdata->user_email;
		$fname = $userdata->first_name;
		$lname = $userdata->last_name;

		// make sure email is not temp
		if ( ! wlm_trim( $email ) || preg_match( '/^temp_[0-9a-f]+/i', $email ) ) {
			return;
		}

		// make sure levels is an array
		if ( ! is_array( $levels ) ) {
			$levels = array( $levels );
		}

		foreach ( $levels as $level_id ) {
			self::process( $email, $fname, $lname, $level_id, $action );
		}
	}

	/**
	 * Subscribes or unsubscribes a user to or from a list
	 *
	 * @param  string $email    Email address
	 * @param  string $fname    First name
	 * @param  string $lname    Last name
	 * @param  string $level_id Membership Level ID
	 * @param  string $action   Level Action
	 */
	public static function process( $email, $first_name, $last_name, $level_id, $action ) {
		// settings
		$ar      = ( new \WishListMember\Autoresponder( 'activecampaign' ) )->settings;
		$api_url = isset( $ar['api_url'] ) ? wlm_trim( $ar['api_url'] ) : '';
		$api_key = isset( $ar['api_key'] ) ? wlm_trim( $ar['api_key'] ) : '';

		// subscribe to form
		$x = wlm_or( $ar['level_actions'][ $level_id ][ $action ]['add'], array() );
		if ( $x ) {
			try {
				self::sdk()->add_to_lists( $x, compact( 'email', 'first_name', 'last_name' ) );
			} catch ( \Exception $e ) {
				null;
			}
		}

		// unsubscribe from form
		$x = wlm_or( $ar['level_actions'][ $level_id ][ $action ]['remove'], array() );
		if ( $x ) {
			try {
				self::sdk()->remove_from_lists( $x, $email );
			} catch ( \Exception $e ) {
				null;
			}
		}

		// add tags
		$x = wlm_or( $ar['level_tag_actions'][ $level_id ][ $action ]['add'], array() );
		if ( $x ) {
			try {
				self::sdk()->add_tags(
					$email,
					$x
				);
			} catch ( \Exception $e ) {
				null;
			}
		}

		// remove tags
		$x = wlm_or( $ar['level_tag_actions'][ $level_id ][ $action ]['remove'], array() );
		if ( $x ) {
			try {
				self::sdk()->remove_tags( $email, $x );
			} catch ( \Exception $e ) {
				null;
			}
		}
	}
}
