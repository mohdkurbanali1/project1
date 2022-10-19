<?php
/**
 * Level Action Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Level Action Methods trait
*/
trait Level_Action_Methods {
	/**
	 * Process scheduled level actions for user
	 *
	 * @param  int $uid User ID.
	 */
	public function process_scheduled_level_actions( $uid = null ) {
		ignore_user_abort( true );

		$wlm_is_doing_level_actions = 'wlm_is_doing_level_actions_' . wlm_server_data()['REMOTE_ADDR'];
		if ( is_null( $uid ) ) {
			if ( 'yes' === get_transient( $wlm_is_doing_level_actions ) ) {
				return;
			}
			set_transient( $wlm_is_doing_level_actions, 'yes', 60 * 60 * 24 );
		}
		wlm_set_time_limit( 60 * 60 * 12 );

		$level_actions = $this->get_user_scheduled_level_actions( $uid );
		foreach ( (array) $level_actions as $key => $action ) {
			$meta_value = isset( $action['meta_value'] ) ? wlm_maybe_unserialize( $action['meta_value'] ) : array();
			if ( isset( $meta_value['action_timestamp'] ) ) {
				if ( $meta_value['action_timestamp'] <= time() ) {
					$this->do_level_action( $action['user_id'], $meta_value['trigger_level'], $meta_value['action_id'], $meta_value['action_details'] );
					delete_user_meta( $action['user_id'], $action['meta_key'] );
				}
			} else {
				delete_user_meta( $action['user_id'], $action['meta_key'] );
			}
		}

		wlm_set_time_limit( ini_get( 'max_execution_time' ) );
		delete_transient( $wlm_is_doing_level_actions );
	}

	/**
	 * Process level actions
	 *
	 * @param  array  $levels Array of level IDs.
	 * @param  int    $uid    User ID.
	 * @param  string $event  Event.
	 */
	public function process_level_actions( $levels, $uid, $event ) {
		// Let's remove pay per post.
		foreach ( (array) $levels as $key => $lvl ) {
			if ( false !== strpos( $lvl, 'U-' ) ) {
				unset( $levels[ $key ] );
			}
		}
		if ( count( $levels ) <= 0 ) {
			return;
		}
		if ( ! is_array( $levels ) ) {
			return;
		}
		if ( ! in_array( $event, array( 'added', 'removed', 'cancelled' ), true ) ) {
			return;
		}

		// Let's get what actions are being processed.
		$user_level_action_record = get_transient( 'user_level_action_record_' . $uid );
		$user_level_action_record = is_array( $user_level_action_record ) ? $user_level_action_record : array();

		foreach ( $levels as $key => $lvlid ) {
			// Let's skip unconfirmed and pending levels.
			if ( 'added' === $event && ( $this->level_pending( $lvlid, $uid ) || $this->level_unconfirmed( $lvlid, $uid ) ) ) {
				continue;
			}
			$level_actions = $this->LevelOptions->get_options( $lvlid, 'scheduled_action' );
			foreach ( $level_actions as $key => $action ) {
				$action_value = wlm_maybe_unserialize( $action->option_value );

				/*
				 * let's check if this is the event and if this event hasnt been processes already.
				 * We do not allow the same event to be executed within the chain to prevent loop.
				 */
				if ( $action_value['level_action_event'] === $event && ! isset( $user_level_action_record[ $action->ID ] ) ) {
					// let's process schedule actions seperately.
					if ( ( 'after' === $action_value['sched_toggle'] && (int) $action_value['sched_after_term'] > 0 ) || 'ondate' === $action_value['sched_toggle'] ) {
						if ( 'ondate' === $action_value['sched_toggle'] ) {
							// Adjust schedule with timezone gmt offset.
							$gmt = get_option( 'gmt_offset' );
							if ( $gmt >= 0 ) {
								$gmt = '+' . $gmt;
							}
							$gmt          = ' ' . $gmt . ' GMT';
							$upgrade_date = strtotime( $action_value['sched_ondate'] . $gmt );
						} else {
							$period       = $action_value['sched_after_period'] ? $action_value['sched_after_period'] : 'days';
							$upgrade_date = strtotime( '+' . $action_value['sched_after_term'] . ' ' . $period, time() );
						}
						$meta_key   = 'incoming_level_actions_' . $action->ID;
						$meta_value = array(
							'action_id'        => $action->ID,
							'event'            => $action_value['level_action_event'],
							'trigger_level'    => $lvlid,
							'action_timestamp' => $upgrade_date,
							'timestamp'        => time(),
							'action_details'   => $action_value,
						);
						update_user_meta( $uid, $meta_key, $meta_value );
					} else {
						$this->do_level_action( $uid, $lvlid, $action->ID, $action_value );
					}
				}
			}
		}
	}

	/**
	 * Execute level action
	 *
	 * @param  int    $uid          User ID.
	 * @param  string $trigger_lvl  Level ID.
	 * @param  int    $action_id    Action ID.
	 * @param  array  $action_value Action.
	 */
	public function do_level_action( $uid, $trigger_lvl, $action_id, $action_value ) {
		$wpm_levels     = $this->get_option( 'wpm_levels' );
		$current_levels = $this->get_membership_levels( $uid, null, null, true );
		$action_levels  = isset( $action_value['action_levels'] ) && is_array( $action_value['action_levels'] ) ? $action_value['action_levels'] : array();

		$action_value = array_merge( $action_value, array( 'level_action_metaid' => $action_id ) );

		do_action( 'wishlistmember_process_level_actions', $uid, $trigger_lvl, $action_value );

		$level_email = isset( $action_value['level_email'] ) ? wlm_trim( $action_value['level_email'] ) : 'dontsend';
		$level_email = in_array( $level_email, array( 'send', 'sendlevel', 'dontsend' ), true ) ? $level_email : 'dontsend';

		if ( in_array( $action_value['level_action_method'], array( 'create-ppp', 'add-ppp', 'remove-ppp' ), true ) ) {
			$pid      = isset( $action_value['ppp_content'] ) ? $action_value['ppp_content'] : false;
			$the_post = get_post( $pid );
			if ( $the_post ) {

				$post_type = $the_post->post_type;
				$post_id   = $the_post->ID;

				if ( in_array( $action_value['level_action_method'], array( 'create-ppp', 'add-ppp' ), true ) ) {
					if ( 'create-ppp' === $action_value['level_action_method'] ) {
						$user_info = get_userdata( $uid );
						if ( $user_info ) {
							$username         = $user_info->user_login;
							$first_name       = $user_info->first_name;
							$last_name        = $user_info->last_name;
							$title_shortcodes = array(
								'{fname}'     => $user_info->first_name,
								'{lname}'     => $user_info->last_name,
								'{name}'      => wlm_trim( $user_info->first_name . ' ' . $user_info->last_name ),
								'{email}'     => $user_info->user_email,
								'{username}'  => $user_info->user_login,
								'{id}'        => $user_info->ID,
								'{date}'      => date_i18n( get_option( 'date_format' ) ),
								'{time}'      => date_i18n( get_option( 'time_format' ) ),
								'{timestamp}' => date_i18n( get_option( 'date_format' ) ),
							);

							$ptitle = isset( $action_value['ppp_title'] ) ? wlm_trim( $action_value['ppp_title'] ) : '{name}-' . $the_post->post_title;
							$ptitle = ! empty( $ptitle ) ? $ptitle : '{name}-' . $the_post->post_title;
							$ptitle = str_replace( array_keys( $title_shortcodes ), $title_shortcodes, $ptitle );
							// Check for duplicates.
							$dup       = get_page_by_title( $ptitle, ARRAY_A, $post_type );
							$dup_cnt   = 1;
							$t         = $ptitle;
							$ppp_users = array();
							$create_p  = true;
							while ( ! is_null( $dup ) ) {
								// If the post exist and user has access to it, dont create.
								$ppp_users = $this->get_post_users( $dup['post_type'], $dup['ID'] );
								if ( ! in_array( "U-{$uid}", $ppp_users, true ) ) {
										$t   = $ptitle . ' ' . $dup_cnt;
										$dup = get_page_by_title( $t, ARRAY_A, $post_type );
								} else {
									$post_id   = $dup['ID'];
									$post_type = $dup['post_type'];
									$dup       = null;
									$create_p  = false;
								}
								$dup_cnt++;
							}
							// Only create if post does not exist or user has no access to it.
							if ( $create_p ) {

								// Note the original id, we will use it for postmeta.
								$old_pid = $post_id;

								$ptitle                    = $t;
								$page_data                 = array();
								$page_data['post_title']   = $ptitle;
								$page_data['post_content'] = $the_post->post_content;
								$page_data['post_type']    = $post_type;
								$page_data['post_status']  = 'publish';
								$post_id                   = wp_insert_post( $page_data, true );
								$this->special_content_level( $post_id, 'Protection', 'Y', $post_type );
								$this->special_content_level( $post_id, 'Inherit', 'N', $post_type );
								$this->pay_per_post( $post_id, 'Y' );

								// let's update the meta.
								global $wpdb;
								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) SELECT %d,meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d",
										$post_id,
										$old_pid
									)
								);

							}
						}
					}
					$this->add_post_users( $post_type, $post_id, $uid );
				} else {
					$this->remove_post_users( $post_type, $post_id, $uid );
				}
			}
		} elseif ( 'cancel' === $action_value['level_action_method'] ) {
			foreach ( $action_levels as $key => $lvl ) {
				if ( isset( $wpm_levels[ $lvl ] ) ) { // Make sure that level is existing and active.
					if ( in_array( $lvl, $current_levels ) ) { // Only cancel levels that this user currently have.
						// Send email notification.
						// It has to be here because it resets every call of LevelCancelled.
						if ( 'dontsend' !== $level_email ) {
							if ( 'sendlevel' !== $level_email ) {
								add_filter(
									'wishlistmember_per_level_template_setting',
									function( $value, $setting, $user_id, $level_id ) {
										return 'cancel_notification' === $setting ? 1 : $value;
									},
									10,
									4
								);
							}
						} else {
							// Disable cancel / uncancel email notif.
							add_filter( 'wishlistmember_pre_email_template', '__return_false', 11, 2 );
						}
						$this->level_cancelled( $lvl, $uid, true );
						// Let's remove the filter.
						remove_filter( 'wishlistmember_pre_email_template', '__return_false', 11, 2 );
						remove_filter(
							'wishlistmember_per_level_template_setting',
							function( $value, $setting, $user_id, $level_id ) {
								return 'cancel_notification' === $setting ? 1 : $value;
							},
							10,
							4
						);
					}
				}
			}
		} else {
			$levels_to_remove = array();
			$levels_to_add    = array();
			if ( 'add' === $action_value['level_action_method'] ) {
				$levels_to_add = $action_levels;
			} elseif ( 'remove' === $action_value['level_action_method'] ) {
				$levels_to_remove = $action_levels;
			} elseif ( 'move' === $action_value['level_action_method'] ) {
				// move function will do add and remove of levels.
				$levels_to_remove[] = $trigger_lvl; // remove from event level.
				$levels_to_add      = $action_levels;
			}

			/*
			 * LET'S DO THE ADD AND REMOVE HERE
			 *
			 * we merge current levels with levels to be
			 * automatically added and then we remove the
			 * remainings levels that are to be automatically removed
			 */
			$levels_for_set = array_unique( array_diff( array_merge( $current_levels, $levels_to_add ), $levels_to_remove ) );
			// we update the levels.
			$x_levels = array(
				'Levels'            => array_unique( $levels_for_set ),
				'To_Removed_Levels' => array_unique( $levels_to_remove ),
				'Metas'             => array(),
			);
			if ( '1' == $action_value['inheritparent'] ) { // we only add parent for ADD action.
				foreach ( $levels_for_set as $key => $lvl ) {
					if ( in_array( $lvl, $levels_to_add ) ) { // if this level is newly added, we add parent meta.
						$x_levels['Metas'][ $lvl ] = array( array( 'parent_level', $trigger_lvl ) );
					}
				}
			}
			$res = $this->set_membership_levels( $uid, (object) $x_levels );
			// Fix the inaccurate time for level registration dates that was added/moved through level actions.
			if ( 'add' === $action_value['level_action_method'] || 'move' === $action_value['level_action_method'] ) {
				if ( ( 'after' === $action_value['sched_toggle'] && (int) $action_value['sched_after_term'] > 0 ) || 'ondate' === $action_value['sched_toggle'] ) {
					// update the registration date/time using the scheduled date/time for "scheduled on date".
					if ( 'ondate' === $action_value['sched_toggle'] ) {
						$gmt = get_option( 'gmt_offset' );
						if ( $gmt >= 0 ) {
							$gmt = '+' . $gmt;
						}
						$gmt                = ' ' . $gmt . ' GMT';
						$update_regdatetime = strtotime( $action_value['sched_ondate'] . $gmt );
					} else {
						/*
						 * update the registration date/time using the
						 * registration date/time of the level that triggered
						 * the action for "scheduled after period".
						 */
						$user_levels        = new \WishListMember\User( $uid );
						$user_levels        = $user_levels->Levels;
						$level_reg_datetime = wlm_arrval( $user_levels, $trigger_lvl, 'Timestamp' );
						$update_period      = $action_value['sched_after_period'] ? $action_value['sched_after_period'] : 'days';
						$update_regdatetime = strtotime( '+' . $action_value['sched_after_term'] . ' ' . $update_period, $level_reg_datetime );
					}
					foreach ( $action_levels as $key => $lvl ) {
						$this->user_level_timestamp( $uid, $lvl, $update_regdatetime );
					}
				}
			}
			// send email notification.
			if ( ( 'add' === $action_value['level_action_method'] || 'move' === $action_value['level_action_method'] ) && count( $levels_to_add ) > 0 && 'dontsend' !== $level_email ) {
				if ( 'send' === $level_email ) {
					add_filter(
						'wishlistmember_per_level_template_setting',
						function( $value, $setting, $user_id, $level_id ) {
							return in_array( $setting, array( 'newuser_notification_user', 'newuser_notification_admin' ), true ) ? 1 : $value;
						},
						10,
						4
					);
				}
				foreach ( $levels_to_add as $lvlid ) {
					$email_macros               = array(
						'[password]'    => '********',
						'[memberlevel]' => $wpm_levels[ $lvlid ]['name'],
					);
					$this->email_template_level = $lvlid;
					$this->send_email_template( 'registration', $uid, $email_macros );

					$this->email_template_level = $lvlid;
					$this->send_email_template( 'admin_new_member_notice', $uid, $email_macros, $this->get_option( 'email_sender_address' ) );
				}
				// let's remove the filter.
				if ( 'send' === $level_email ) {
					remove_filter(
						'wishlistmember_per_level_template_setting',
						function( $value, $setting, $user_id, $level_id ) {
							return in_array( $setting, array( 'newuser_notification_user', 'newuser_notification_admin' ), true ) ? 1 : $value;
						},
						10,
						4
					);
				}
			}
		}
	}

	/**
	 * Get scheduled level actions for a user
	 *
	 * @param  int $uid User ID.
	 * @return array    Array of scheduled level actions
	 */
	public function get_user_scheduled_level_actions( $uid = null ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->usermeta . '` WHERE `user_id` LIKE %s AND meta_key LIKE %s ORDER BY meta_key DESC',
				! is_null( $uid ) ? $uid : '%',
				'incoming_level_actions_%'
			),
			ARRAY_A
		);
	}

	/**
	 * Record user level actions
	 *
	 * @param  int    $uid          User ID.
	 * @param  string $lvlid        Level ID.
	 * @param  string $action_value Action Value.
	 */
	public function record_user_level_actions( $uid, $lvlid, $action_value ) {
		$wpm_levels               = $this->get_option( 'wpm_levels' );
		$user_level_action_record = get_transient( 'user_level_action_record_' . $uid );
		$user_level_action_record = is_array( $user_level_action_record ) ? $user_level_action_record : array();
		$lvls                     = array();
		foreach ( (array) $action_value['action_levels'] as $key => $lvl ) {
			$lvls[] = $wpm_levels[ $lvl ]['name'];
		}
		$user_level_action_record[ $action_value['level_action_metaid'] ] = array(
			'uid'         => $uid,
			'event'       => $action_value['level_action_event'],
			'event_level' => $wpm_levels[ $lvlid ]['name'],
			'method'      => $action_value['level_action_method'],
			'levels'      => implode( ', ', $lvls ),
		);

		/*
		 * lets save for a minute while processing
		 * aside from 1 minute lifetime, we also delete this after schedule_user_level in wishlist-member3-actions.php
		 */
		set_transient( 'user_level_action_record_' . $uid, $user_level_action_record, MINUTE_IN_SECONDS );
	}


			/**
			 * Auto Remove From Feature hook for Add Action
			 *
			 * @param integer $uid User ID
			 * @param array   $new_levels New Membership Levels
			 */
	public function do_auto_add_remove( $uid, $new_levels = '', $removed_levels = '' ) {
		$new_levels     = (array) $new_levels;
		$removed_levels = (array) $removed_levels;
		$wlmuser        = new \WishListMember\User( $uid );
		foreach ( $removed_levels as $key => $value ) {
			if ( false !== strpos( $value, 'U-' ) ) {
				unset( $removed_levels[ $key ] );
			}
		}
		foreach ( $new_levels as $key => $value ) {
			if ( false !== strpos( $value, 'U-' ) ) {
				unset( $new_levels[ $key ] );
			}
		}
		// prevent infinite loop, dont run this for levels with parent
		foreach ( $new_levels as $key => $lvl ) {
			if ( isset( $wlmuser->Levels[ $lvl ] ) ) {
				if ( $wlmuser->Levels[ $lvl ]->ParentLevel ) {
					unset( $new_levels[ $key ] ); // dont do add remove for child levels
				}
			} else {
				unset( $new_levels[ $key ] ); // only add levels that user has
			}
		}

		if ( count( $new_levels ) <= 0 && count( $removed_levels ) <= 0 ) {
			return;
		}

		$wlmuser->DoAddRemove( $new_levels, $removed_levels );
		$this->update_child_status( $uid, $new_levels );
	}

			/**
			 * Auto Remove From Feature hook for Remove action
			 *
			 * @param integer $uid User ID
			 * @param array   $new_levels New Membership Levels
			 */
	public function remove_do_auto_add_remove( $uid, $removed_levels = '' ) {
		$removed_levels = (array) $removed_levels;
		$wlmuser        = new \WishListMember\User( $uid );
		foreach ( $removed_levels as $key => $value ) {
			if ( false !== strpos( $value, 'U-' ) ) {
				unset( $removed_levels[ $key ] );
			}
		}
		if ( count( $removed_levels ) <= 0 ) {
			return;
		}

		$wlmuser->DoAddRemove( $removed_levels, array(), 'remove' );
	}

			/**
			 * Auto Remove From Feature hook for Cancel action
			 *
			 * @param integer $uid User ID
			 * @param array   $new_levels New Membership Levels
			 */
	public function cancel_do_auto_add_remove( $uid, $cancel_levels = '' ) {
		$cancel_levels = (array) $cancel_levels;
		$wlmuser       = new \WishListMember\User( $uid );
		$wlmuser->DoAddRemove( $cancel_levels, array(), 'cancel' );
	}

			/**
			 * Remove Child of Parent Levels hook
			 *
			 * @param integer $uid User ID
			 * @param array   $removed_levels Removed Membership Levels
			 * @param array   $new_levels New Membership Levels
			 */
	public function do_remove_child_levels( $uid, $removed_levels = array(), $new_levels = array() ) {
		// ** we remove this part then we change to Level Actions from Add To feature
		// ** because levels with parent automatically inherits the status of its parent
		// $wpm_levels = $this->get_option('wpm_levels');
		// foreach ($removed_levels as $key => $lvl) {
		// $inherit = isset( $wpm_levels[$lvl]['inheritparent'] ) && '1' === $wpm_levels[$lvl]['inheritparent'] ? true:false;
		// if ( ! $inherit ) {
		// unset( $removed_levels[$key] );
		// }
		// }
		$this->remove_child_levels( $uid, $removed_levels );
	}

			/**
			 * Update Status of Child when Parent Levels changed hook
			 *
			 * @param integer $uid User ID
			 * @param array   $parent_levels Removed Membership Levels
			 */
	public function do_update_child_status( $uid, $parent_levels ) {
		// ** we remove this part then we change to Level Actions from Add To feature
		// ** because levels with parent automatically inherits the status of its parent
		// $wpm_levels = $this->get_option('wpm_levels');
		// foreach ($parent_levels as $key => $lvl) {
		// $inherit = isset( $wpm_levels[$lvl]['inheritparent'] ) && '1' === $wpm_levels[$lvl]['inheritparent'] ? true:false;
		// if ( ! $inherit ) {
		// unset( $parent_levels[$key] );
		// }
		// }
		$this->update_child_status( $uid, $parent_levels );
	}

			/**
			 * Set Expire Status of Child with Parent Levels
			 *
			 * @param assorted $expire Expire Status
			 * @param integer  $uid User ID
			 * @param array    $parent_level Removed Membership Level
			 */
	public function do_expire_child_status( $expire_date, $uid, $level ) {
		$p          = $this->level_parent( $level, $uid );
		$wpm_levels = $this->get_option( 'wpm_levels' );

		// ** we remove this part then we change to Level Actions from Add To feature
		// ** because levels with parent automatically inherits the status of its parent
		// $inherit = isset( $wpm_levels[$p]['inheritparent'] ) && '1' === $wpm_levels[$p]['inheritparent'] ? true:false;
		// if ( ! $inherit ) {
		// return $expire_date;
		// }

		if ( $p ) {
			$p_expire_date = $this->level_expire_date( $p, $uid );
			if ( false === $expire_date ) {
				$expire_date = $p_expire_date;
			} elseif ( false !== $p_expire_date && $p_expire_date < $expire_date ) {
				$expire_date = $p_expire_date;
			}
		}
		return $expire_date;
	}

}

// register hooks.
add_action(
	'wishlistmember_register_hooks',
	function( $wlm ) {
		add_action( 'wishlistmember_approve_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_action( 'wishlistmember_cancel_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_action( 'wishlistmember_confirm_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_action( 'wishlistmember_process_level_actions', array( $wlm, 'record_user_level_actions' ), 1, 3 );
		add_action( 'wishlistmember_remove_user_levels', array( $wlm, 'do_remove_child_levels' ), 1, 3 );
		add_action( 'wishlistmember_run_user_level_actions', array( $wlm, 'process_scheduled_level_actions' ) );
		add_action( 'wishlistmember_unapprove_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_action( 'wishlistmember_uncancel_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_action( 'wishlistmember_unconfirm_user_levels', array( $wlm, 'do_update_child_status' ), 1, 2 );
		add_filter( 'wishlistmember_user_expire_date', array( $wlm, 'do_expire_child_status' ), 1, 3 );
	}
);
