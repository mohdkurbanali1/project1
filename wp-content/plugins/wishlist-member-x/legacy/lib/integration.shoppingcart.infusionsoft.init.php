<?php

/*
 * InfusionSoft Shopping Cart Integration Init
 * Original Author : Fel Jun Palawan
 */
if ( ! class_exists( 'WLM_Infusionsoft' ) ) {
	include_once $this->plugindir . '/extlib/wlm-infusionsoft.php';
}

if ( ! class_exists( 'WLM_INTEGRATION_INFUSIONSOFT_INIT' ) ) {

	class WLM_INTEGRATION_INFUSIONSOFT_INIT {
		private $wlm          = null;
		private $machine_name = '';
		private $api_key      = '';
		private $ifsdk        = null;
		private $log          = false;
		private $invmarker    = 'InfusionSoft';

		public function __construct() {
			global $WishListMemberInstance;

			// make sure that WLM active and infusiosnsoft connection is set
			if ( ! isset( $WishListMemberInstance ) || ! class_exists( 'WLM_Infusionsoft' ) ) {
				return;
			}

			$this->wlm          = $WishListMemberInstance;
			$this->machine_name = $this->wlm->get_option( 'ismachine' );
			$this->api_key      = $this->wlm->get_option( 'isapikey' );
			$this->log          = $this->wlm->get_option( 'isenable_log' );
			$this->machine_name = $this->machine_name ? $this->machine_name : '';
			$this->api_key      = $this->api_key ? $this->api_key : '';

			$apilogfile = false;
			if ( $this->log ) {
				$date_now   = wlm_date( 'm-d-Y' );
				$apilogfile = $this->wlm->plugindir . "/ifs_logs_{$date_now}.csv";
			}

			if ( $this->api_key && $this->machine_name ) {
				$this->ifsdk = new WLM_Infusionsoft( $this->machine_name, $this->api_key, $apilogfile );
			}
		}

		public function load_hooks() {
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return;
			}

			add_action( 'init', array( $this, 'wp_init' ) );

			add_action( 'wishlistmember_user_registered', array( $this, 'NewUserTagsQueue' ), 99, 2 );
			add_action( 'wishlistmember_add_user_levels', array( $this, 'AddUserTagsQueue' ), 10, 3 );
			add_action( 'wishlistmember_pre_remove_user_levels', array( $this, 'RemoveUserTagsQueue' ), 99, 2 );
			add_action( 'wishlistmember_cancel_user_levels', array( $this, 'CancelUserTagsQueue' ), 99, 2 );
			add_action( 'delete_user', array( $this, 'DeleteUserHookQueue' ), 9, 1 );

			add_action( 'wishlistmember_confirm_user_levels', array( $this, 'ConfirmApproveLevelsTags' ), 99, 2 );
			add_action( 'wishlistmember_approve_user_levels', array( $this, 'ConfirmApproveLevelsTags' ), 99, 2 );

			add_action( 'wishlistmember_addpp_posts_user', array( $this, 'PPAddUserTagsQueue' ), 99, 2 );
			add_action( 'wishlistmember_removepp_posts_user', array( $this, 'PPRemoveUserTagsQueue' ), 99, 2 );

			add_action( 'wishlistmember_addpp_pages_user', array( $this, 'PPAddUserTagsQueue' ), 99, 2 );
			add_action( 'wishlistmember_removepp_pages_user', array( $this, 'PPRemoveUserTagsQueue' ), 99, 2 );

			add_action( 'edit_user_profile', array( $this, 'ProfileForm' ) );
			add_action( 'show_user_profile', array( $this, 'ProfileForm' ) );
			add_action( 'profile_update', array( $this, 'UpdateProfile' ), 9, 2 );

			add_filter( 'wishlist_member_user_custom_fields', array( $this, 'add_ifs_field' ), 99, 2 );
			add_filter( 'wishlistmember_post_update_user', array( $this, 'save_ifs_field' ), 99, 1 );

			// syncing infusionsoft shoppingcart orders/subscription status to level status
			add_action( 'wishlistmember_ifs_sync', array( $this, 'sync_ifs' ) );

			// api queue processing.
			add_action( 'wishlistmember_api_queue', array( $this, 'ifscProcessQueue' ) );
		}

		public function wp_init() {
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return;
			}
			// cron for syncing arb
			$next_schedule = wp_next_scheduled( 'wishlistmember_ifs_sync' );
			if ( ! $next_schedule ) {
				wp_schedule_event( time(), 'twicedaily', 'wishlistmember_ifs_sync' );
				// this will be cleared on WLM cron clearing
			} else {
				if ( $next_schedule <= time() ) {
					spawn_cron( time() );
				}
			}
		}

		public function sync_ifs( $debug = false, $force = true ) {
			global $wpdb;
			// put an hour delay every another sync
			$logs = $this->wlm->get_option( 'ifs_sync_log' );
			if ( ! $force && $logs && is_array( $logs ) ) {
				$previous = isset( $logs['start'] ) ? $logs['start'] : '';
				$previous = strtotime( $previous );
				$now      = time();
				$diff     = $now - $previous;
				$delay    = 60 * 60;
				if ( $diff < $delay ) {
					$msg = 'Cannot sync now. ' . ( $delay - $diff ) . ' second/s left';
					return array(
						'end'     => wlm_date( 'Y-m-d H:i:s' ),
						'message' => $msg,
						'count'   => 0,
					);
				}
			}

			if ( ! $this->ifsdk || ! $this->ifsdk->is_api_connected() ) {
				$msg = 'Unable to establish Infusionsoft API connection. Please check your Infusionsoft App Name and API Key.';
				return array(
					'end'     => wlm_date( 'Y-m-d H:i:s' ),
					'message' => $msg,
					'count'   => 0,
				);
			}

			// initial the log since it was called
			$sync_start = wlm_date( 'Y-m-d H:i:s' );
			$log        = array(
				'count'   => 0,
				'message' => 'Infusionsoft Sync started.',
				'start'   => $sync_start,
				'end'     => '',
			);
			$this->wlm->save_option( 'ifs_sync_log', $log );

			wlm_set_time_limit( 0 ); // override max execution time
			$log  = 'Syncing Infusionsoft Transactions with WLM<br />';
			$log .= '<i>You should see a message below saying that all records were processed.<br />If not some records might not been processed due to lack of computer resources or an error occured.</i><br />';

			$istrans = $this->wlm->get_option( 'infusionsoft_transaction_ids' );
			if ( empty( $istrans ) || ! $istrans || ! is_array( $istrans ) ) {
				$log .= '<br />Retrieving transactions records...';
				// get all the infusionsoft txn_id
				$trans = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT ul.`level_id` as levelid, ul.`user_id` as uid, uo.`option_value` as option_value  FROM `' . esc_sql( $this->wlm->table_names->userlevel_options ) . '` AS uo LEFT JOIN `' . esc_sql( $this->wlm->table_names->userlevels ) . '` AS ul ON uo.`userlevel_id` = ul.`ID` WHERE uo.`option_value` LIKE %s',
						"{$this->invmarker}%"
					)
				);

				$istrans = array();
				foreach ( $trans as $t ) {
					$txn_id               = $t->option_value; // format {marker}-{invoice#}-{subcriptionid}
					list( $marker, $tid ) = explode( '-', $txn_id, 2 ); // seperate the marker from the others
					$istrans[ $tid ]      = array(
						'level' => $t->levelid,
						'uid'   => $t->uid,
					);
				}

				$this->wlm->save_option( 'infusionsoft_transaction_ids', $istrans );
			} else {
				$log .= '<br />Using previous transactions records that was not processed...';
			}

			$cnt = count( $istrans );
			if ( $cnt > 0 ) {
				$log .= "Found <strong>{$cnt}</strong> record/s<br />";
				$log .= 'Processing please wait...<br />';
			} else {
				$log .= 'No Records to Sync.';
			}

			// loop through the txn_ids
			$rec       = 1;
			$counter   = 0;
			$log_tbl   = '';
			$wlmlevels = $this->wlm->get_option( 'wpm_levels' );

			foreach ( (array) $istrans as $invid => $data ) {
				list( $iid, $sid ) = explode( '-', $invid, 2 );  // retrieve Invoice id and Sub id
				$uid               = $data['uid'];
				$invoice           = $this->ifsdk->get_invoice_details( $iid );
				$mstat             = 'Active';
				// do we have a valid invoice? if so, retrieve the status
				if ( $invoice ) {
					$invoice['SubscriptionId'] = $sid; // include the subscription id

					$invoice = $this->get_invoice_status( $invoice );

					// update level status based on invoice status
					wlm_post_data()['sctxnid'] = "{$this->invmarker}-" . $invid;
					switch ( $invoice['Status'] ) {
						case 'active':
							$this->wlm->shopping_cart_reactivate();

							// Add hook for Shoppingcart reactivate so that other plugins can hook into this
							wlm_post_data()['sc_type'] = 'Infusionsoft';
							do_action_deprecated( 'wlm_shoppingcart_rebill', array( wlm_post_data( true ) ), '3.10', 'wishlistmember_shoppingcart_rebill' );
							do_action( 'wishlistmember_shoppingcart_rebill', wlm_post_data( true ) );

							break;
						default:// 'inactive':
							$this->wlm->shopping_cart_deactivate();
					}
					$mstat = ucfirst( $invoice['Status'] );
				}

				// update the txnid list
				unset( $istrans[ $invid ] );
				$this->wlm->save_option( 'infusionsoft_transaction_ids', $istrans );

				$stat     = $invoice ? 'Processed' : 'Invalid invoice';
				$user_url = admin_url( "user-edit.php?user_id={$uid}&wp_http_referer=wlm" );
				$lvlname  = isset( $wlmlevels[ $data['level'] ] ) ? $wlmlevels[ $data['level'] ]['name'] : 'Unknown';
				$log_tbl .= "<tr><td><a target='_blank' href='{$user_url}'>{$uid}</a></td><td>{$lvlname}</td><td>{$this->invmarker}-{$invid}</td><td>{$iid}</td><td>{$stat}</td><td>{$mstat}</td></tr>";// $rec++ . ($invoice ? "(OK)" : "(Invalid)") . ", ";
				$rec++;
				$counter++;
			}

			$log .= "<table style='width:100%;' border='1'><tr><th>User ID</th><th>Level</th><th>Transaction Id</th><th>Invoice#</th><th>Result</th><th>Membership Status</th></tr>" . $log_tbl . '</table>';
			// lets end the cron job here
			$log .= "<br /><br /><b>FINISHED</b>.<i>All {$cnt} records were processed.</i>";

			// display logs for admin only
			$current_user = wp_get_current_user();
			if ( $debug && $current_user->caps['administrator'] ) {
				echo wp_kses_post( $log );
			} else {
				echo 'WLM Infusionsoft Integration syncing done. For more detailed output, login an admin account and refresh this page.';
			}
			$message = 'Synced successfully.';
			// update the log
			$log = array(
				'count'   => $counter,
				'message' => $message,
				'start'   => $sync_start,
				'end'     => wlm_date( 'Y-m-d H:i:s' ),
			);
			$this->wlm->save_option( 'ifs_sync_log', $log );
			return $log;
		}

		public function add_ifs_field( $custom_fields, $userid ) {
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return $custom_fields;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return $custom_fields; }

			$contactid = $this->wlm->Get_UserMeta( $userid, 'wlminfusionsoft_contactid' );
			if ( ! $contactid ) {
				$contactid = get_user_meta( $userid, 'wlifcon_contactid', true ); // wlmis contactid
			}
			$custom_fields['wlminfusionsoft_contactid'] = array(
				'type'       => 'text', // hidden, select, textarea, checkbox, etc
				'label'      => 'Infusionsoft Contact ID',
				// 'description' => 'Description',
				'attributes' => array(
					'type'  => 'text', // hidden, select, textarea, checkbox, etc
					'name'  => 'wlminfusionsoft_contactid', // same as index above
					// 'other attributes' => 'value',
					'value' => $contactid,
					// more attributes if needed
				),
			);
			return $custom_fields;
		}

		public function save_ifs_field( $data ) {
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return; }
			if ( ! isset( $data['userid'] ) ) {
				return;
			}

			$user_custom_fields = isset( $data['customfields'] ) ? $data['customfields'] : array();
			if ( ! isset( $user_custom_fields['wlminfusionsoft_contactid'] ) ) {
				return;
			}
			$wlminfusionsoft_contactid = $user_custom_fields['wlminfusionsoft_contactid'] ? (int) wlm_trim( $user_custom_fields['wlminfusionsoft_contactid'] ) : '';

			$this->wlm->Update_UserMeta( $data['userid'], 'wlminfusionsoft_contactid', $wlminfusionsoft_contactid );
		}

		public function ProfileForm( $user ) {
			global $pagenow;
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return; }

			$user_id = $user;
			if ( is_object( $user ) ) {
				$user_id = $user->ID;
			}
			if ( 'profile.php' !== $pagenow && 'user-edit.php' !== $pagenow ) {
				return;
			}

			$contactid = $this->wlm->Get_UserMeta( $user_id, 'wlminfusionsoft_contactid' );
			if ( ! $contactid ) {
				$contactid = get_user_meta( $user_id, 'wlifcon_contactid', true ); // wlmis contactid
			}
			echo '<h3>WishList Member Infusionsoft Integration</h3>';
			echo '<table class="form-table">';
			echo '<tbody>';
			echo '<tr>';
			echo '<th><label for="wlminfusionsoft_contactid">Infusionsoft Contact ID</label></th>';
			echo '<td>';
			echo '<input type="text" name="wlminfusionsoft_contactid" id="wlminfusionsoft_contactid" value="' . esc_attr( $contactid ) . '" class="regular-text" style="width:100px;" maxlength="10" maxlength="10">';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
		}

		public function UpdateProfile( $user ) {
			if ( empty( $this->ifsdk ) || empty( $this->wlm ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return; }
			$user_id = $user;
			if ( is_object( $user ) ) {
				$user_id = $user->ID;
			}

			if ( isset( wlm_post_data()['wlminfusionsoft_contactid'] ) ) {
				$this->wlm->Update_UserMeta( $user_id, 'wlminfusionsoft_contactid', (int) trim( wlm_post_data()['wlminfusionsoft_contactid'] ) );
			}
		}

		public function generateContactId( $uid, $data = null ) {
			if ( ! $this->ifsdk || ! $this->ifsdk->is_api_connected() ) {
				return null;
			}

			global $WishListMemberInstance;
			$contactid = get_user_meta( $uid, 'wlifcon_contactid', true ); // wlmis contactid

			// lets get contactid using transactionid
			if ( ! $contactid ) {
				// first we get txnids by Infusionsoft if he have
				$txnids    = $WishListMemberInstance->get_membership_levels_txn_ids( $uid );
				$txnids    = (array) $txnids;
				$wlm_txnid = '';
				foreach ( $txnids as $id => $txnid ) {
					if ( false !== strpos( $txnid, 'InfusionSoft' ) ) {
						$wlm_txnid = $txnid;
						break;
					} elseif ( false !== strpos( $txnid, 'IFContact' ) ) {
						$wlm_txnid = $txnid;
						break;
					}
				}
				// in case we dont find txnid, we use the one in the data for new users
				if ( empty( $wlm_txnid ) ) {
					$wlm_txnid = isset( $data['sctxnid'] ) ? $data['sctxnid'] : '';
				}

				if ( ! empty( $wlm_txnid ) ) {
					if ( false !== strpos( $wlm_txnid, 'IFContact' ) ) {
						list( $marker, $contactid ) = explode( '-', $wlm_txnid, 2 );
					} elseif ( false !== strpos( $wlm_txnid, 'InfusionSoft' ) ) {
						list( $marker, $tid ) = explode( '-', $wlm_txnid, 2 );
						list( $iid, $sid )    = explode( '-', $tid, 2 );  // retrieve Invoice id and Sub id
						$contactid            = $this->ifsdk->get_contactid_by_invoice( $iid );
					}
				}
			}

			// lets create contactid using email
			if ( ! $contactid ) {
				$user_info = get_userdata( $uid );
				if ( ! $user_info ) {
					return null;
				}

				$email = $user_info->user_email;
				if ( $email && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$contactid = $this->ifsdk->get_contactid_by_email( $email );
					if ( ! $contactid ) {
						$user      = array(
							'Email'     => $email,
							'FirstName' => $user_info->user_firstname,
							'LastName'  => $user_info->user_lastname,
						);
						$contactid = $this->ifsdk->create_contact( $user, 'Added Via WLM INF SC Integration API.' );
					}
				} else {
					return null;
				}
			}

			if ( $contactid ) {
				$WishListMemberInstance->Update_UserMeta( $uid, 'wlminfusionsoft_contactid', $contactid );
			} else {
				$contactid = false; // make sure that contactid is false
			}

			return $contactid;
		}

		public function processTags( $levels, $action, $contactid = null, $uid = null, $data = null ) {
			global $WishListMemberInstance;
			if ( ! isset( $WishListMemberInstance ) ) {
				return array(
					'errstr' => 'Theres a problem with wlm instance.',
					'errno'  => 1,
				);
			}
			if ( ! $this->ifsdk || ! $this->ifsdk->is_api_connected() ) {
				return array(
					'errstr' => 'Unable to process tags. No API Connection.',
					'errno'  => 1,
				);
			}

			$levels = (array) $levels;
			if ( count( $levels ) <= 0 ) {
				return array(
					'errstr' => 'No Levels Found',
					'errno'  => 1,
				);// no levels, no need to continue
			}

			if ( ! $contactid ) {
				$contactid = $WishListMemberInstance->Get_UserMeta( $uid, 'wlminfusionsoft_contactid' );
				// get the contactid if not set
				if ( ! $contactid ) {
					$contactid = $this->generateContactId( $uid, $data );
					if ( null === $contactid ) {
						 return array(
							 'errstr' => 'Theres a problem with userid, email or wlm instance.',
							 'errno'  => 1,
						 );
					}
				}
			}

			if ( $contactid ) {

				if ( 'new' === $action || 'add' === $action ) {
					$istags_app   = $WishListMemberInstance->get_option( 'istags_add_app' );
					$istags_rem   = $WishListMemberInstance->get_option( 'istags_add_rem' );
					$istagspp_app = $WishListMemberInstance->get_option( 'istagspp_add_app' );
					$istagspp_rem = $WishListMemberInstance->get_option( 'istagspp_add_rem' );
				} elseif ( 'remove' === $action ) {
					$istags_app   = $WishListMemberInstance->get_option( 'istags_remove_app' );
					$istags_rem   = $WishListMemberInstance->get_option( 'istags_remove_rem' );
					$istagspp_app = $WishListMemberInstance->get_option( 'istagspp_remove_app' );
					$istagspp_rem = $WishListMemberInstance->get_option( 'istagspp_remove_rem' );
				} elseif ( 'cancel' === $action ) {
					$istags_app   = $WishListMemberInstance->get_option( 'istags_cancelled_app' );
					$istags_rem   = $WishListMemberInstance->get_option( 'istags_cancelled_rem' );
					$istagspp_app = $WishListMemberInstance->get_option( 'istagspp_cancelled_app' );
					$istagspp_rem = $WishListMemberInstance->get_option( 'istagspp_cancelled_rem' );
				} elseif ( 'delete' === $action ) {
					$istags_app   = $WishListMemberInstance->get_option( 'istags_remove_app' );
					$istags_rem   = $WishListMemberInstance->get_option( 'istags_remove_rem' );
					$istagspp_app = $WishListMemberInstance->get_option( 'istagspp_remove_app' );
					$istagspp_rem = $WishListMemberInstance->get_option( 'istagspp_remove_rem' );
				}

				if ( $istags_app ) {
					$istags_app = wlm_maybe_unserialize( $istags_app );
				} else {
					$istags_app = array();
				}
				if ( $istags_rem ) {
					$istags_rem = wlm_maybe_unserialize( $istags_rem );
				} else {
					$istags_rem = array();
				}

				if ( $istagspp_app ) {
					$istagspp_app = wlm_maybe_unserialize( $istagspp_app );
				} else {
					$istagspp_app = array();
				}
				if ( $istagspp_rem ) {
					$istagspp_rem = wlm_maybe_unserialize( $istagspp_rem );
				} else {
					$istagspp_rem = array();
				}

				// add the tags for each level
				foreach ( (array) $levels as $level ) {

					if ( false === strpos( $level, 'payperpost' ) ) {
						$app_tags = $istags_app;
						$rem_tags = $istags_rem;
					} else {
						$app_tags = $istagspp_app;
						$rem_tags = $istagspp_rem;
					}

					// add the contact to a tag/group
					if ( isset( $app_tags[ $level ] ) ) {
						foreach ( $app_tags[ $level ] as $k => $val ) {
							$ret = $this->ifsdk->tag_contact( $contactid, $val );
							if ( isset( $ret['errno'] ) ) {
								return $ret;
							}
						}
					}

					// remove the contact from tag/group
					if ( isset( $rem_tags[ $level ] ) ) {
						foreach ( $rem_tags[ $level ] as $k => $val ) {
							$ret = $this->ifsdk->untag_contact( $contactid, $val );
							if ( isset( $ret['errno'] ) ) {
								return $ret;
							}
						}
					}
				}
			} else {
				return array(
					'errstr' => 'No Contact ID',
					'errno'  => 1,
				);
			}

			return true; // success
		}

		public function ifscAddQueue( $data, $process = true ) {
			$WishlistAPIQueueInstance = new \WishListMember\API_Queue();
			$qname                    = 'infusionsoftsc_' . time();
			$data                     = wlm_maybe_serialize( $data );
			$WishlistAPIQueueInstance->add_queue( $qname, $data, 'For Queueing' );
			if ( $process ) {
				$this->ifscProcessQueue();
			}
		}

		public function ifscProcessQueue( $recnum = 10, $tries = 3 ) {
			if ( ! $this->ifsdk || ! $this->ifsdk->is_api_connected() ) {
				return;
			}
			$WishlistAPIQueueInstance = new \WishListMember\API_Queue();
			$last_process             = get_option( 'WLM_InfusionsoftSCAPI_LastProcess' );
			$current_time             = time();
			$tries                    = $tries > 1 ? (int) $tries : 3;
			$error                    = false;
			// lets process every 10 seconds
			if ( ! $last_process || ( $current_time - $last_process ) > 10 ) {
				$queues = $WishlistAPIQueueInstance->get_queue( 'infusionsoftsc', $recnum, $tries, 'tries,name' );
				foreach ( $queues as $queue ) {
					$data = wlm_maybe_unserialize( $queue->value );
					if ( 'new' == $data['action'] ) {
						$res = $this->NewUserTagsHook( $data['uid'], $data['data'] );
					} elseif ( 'add' == $data['action'] ) {
						$res = $this->AddUserTagsHook( $data['uid'], $data['levels'] );
					} elseif ( 'remove' == $data['action'] ) {
						$res = $this->RemoveUserTagsHook( $data['uid'], $data['levels'] );
					} elseif ( 'cancel' == $data['action'] ) {
						$res = $this->CancelUserTagsHook( $data['uid'], $data['levels'] );
					} elseif ( 'delete' == $data['action'] ) {
						$res = $this->DeleteUserTagsHook( $data['contactid'], $data['levels'] );
					}

					if ( isset( $res['errstr'] ) ) {
						$res['error'] = strip_tags( $res['errstr'] );
						$res['error'] = str_replace( array( "\n", "\t", "\r" ), '', $res['error'] );
						$d            = array(
							'notes' => "{$res['errno']}:{$res['error']}",
							'tries' => $queue->tries + 1,
						);
						$WishlistAPIQueueInstance->update_queue( $queue->ID, $d );
						$error = true;
					} else {
						$WishlistAPIQueueInstance->delete_queue( $queue->ID );
						$error = false;
					}
				}
				// save the last processing time when error has occured on last transaction
				if ( $error ) {
					$current_time = time();
					if ( $last_process ) {
						update_option( 'WLM_InfusionsoftSCAPI_LastProcess', $current_time );
					} else {
						add_option( 'WLM_InfusionsoftSCAPI_LastProcess', $current_time );
					}
				}
			}
		}

		public function get_invoice_status( $invoice ) {
			$sid = isset( $invoice['SubscriptionId'] ) ? $invoice['SubscriptionId'] : '';
			$pid = $invoice['ProductSold'];

			if ( empty( $sid ) ) { // old transaction id, base the search from contact id and product id
				/*** THIS IS FOR OLD VERSION OF IF INTEGRATION */
				$invoice['Status'] = ( 1 == $invoice['PayStatus'] && 0 == $invoice['RefundStatus'] ) ? 'active' : 'inactive';
				// check if this is recurring
				$recur = $this->ifsdk->get_cidpid_recurringorder( $invoice['ContactId'], $pid );

				if ( $recur && ! empty( $recur['Status'] ) ) { // make sure that we're not processing an empty field. fixes issue with complete recurring subscriptions
					$invoice['Status'] = strtolower( $recur['Status'] );
				}
			} else {
				// NEW INFUSIONSOFT UPDATES AFTER THE SPRING RELEASE, we added subscription id for subscriptions
				// non-subscriptions have 00 values
				$invoice['Status'] = ( 1 == $invoice['PayStatus'] && 0 == $invoice['RefundStatus'] ) ? 'active' : 'inactive';

				// check if this is recurring
				if ( '00' != $sid ) { // subscriptions have number values
					$recur = $this->ifsdk->get_subscriptionid_recurringorder( $sid );
				} else { // if subscription is not available, use the contactid and job id
					$recur = $this->ifsdk->get_cidjobid_recurringorder( $invoice['ContactId'], $invoice['JobId'] );
				}

				// subscription
				if ( $recur && ! empty( $recur['Status'] ) ) { // make sure that we're not processing an empty field. fixes issue with complete recurring subscriptions

					// assign the subscription id
					$invoice['SubscriptionId'] = $recur['Id'];
					unset( $recur['Id'] );

					$invoice = array_merge( $invoice, $recur );

					if ( 'Active' != $recur['Status'] ) {
						$refund      = $this->get_invoice_refunds( $invoice['Id'] ); // get refunds of invoice
						$full_refund = $refund > 0 && $refund >= $invoice['TotalDue'] ? true : false;
						if ( $full_refund || 'refund' == strtolower( wlm_trim( $recur['ReasonStopped'] ) ) ) {
							$invoice['Status'] = 'inactive';
						} elseif ( $recur['NextBillDate'] > wlm_date( 'Ymd\TH:i:s', strtotime( 'EST' ) ) ) { // if no active, lets cancel them only when the next bill date has passed already
							if ( $this->is_last_invoice_paid( $invoice ) ) { // if last invoice is paid, wait for next bill date because he paid
								$invoice['Status'] = 'active';
							}
						} else {
							$invoice['Status'] = strtolower( $recur['Status'] );
						}
					} else {
						$invoice['Status'] = strtolower( $recur['Status'] );
					}
				} else { // one time payment
					if ( 'inactive' === $invoice['Status'] && 1 == $invoice['RefundStatus'] ) { // check if refunded
						$refund      = $this->get_invoice_refunds( $invoice['Id'] ); // get refunds of invoice
						$full_refund = $refund > 0 && $refund >= $invoice['TotalDue'] ? true : false;
						if ( ! $full_refund ) {
							$invoice['Status'] = 'active';
						}
					}
				}
			}

			// if invoice is inactive, lets check if its has payment plan
			if ( 'inactive' == $invoice['Status'] ) {

				$invstat = 'inactive';
				// lets get the payment plan for this invoice
				$pp = $this->get_payplan_status( $invoice['Id'] );

				if ( $pp ) {
					if ( $pp['OverDue'] ) { // if it has overdue payment plan
						// get the payment plan items
						$ppi = $this->ifsdk->get_payplan_items( $pp['PayPlanId'] );

						if ( $ppi ) {
							// get the payment plan items with unpaid status
							foreach ( (array) $ppi as $ppitems ) {
								if ( 1 == $ppitems['Status'] ) {
									// If it has an unpaid payment, check if it's more than 1 day and then set it to inactive
									$seconds_diff = strtotime( wlm_date( 'Ymd\TH:i:s', strtotime( 'EST' ) ) ) - strtotime( $ppitems['DateDue'] );
									if ( $seconds_diff >= 86400 ) {
										$invstat = 'inactive';
									} else {
										$invstat = 'active';
									}
									break;
								}
							}
						}
					} else {
						// if payment plan has number of days before charging and its not overdue
						$invstat = 'active';
					}
				}
				$invoice['Status'] = $invstat;
			}
			return $invoice;
		}

		private function get_invoice_refunds( $invoiceid ) {
			$inv_payments    = $this->ifsdk->get_invoice_payments( $invoiceid );
			$refunded_amount = 0;
			if ( $inv_payments ) {
				foreach ( $inv_payments as $inv_payment ) {
					if ( 'refunded' == strtolower( $inv_payment['PayStatus'] ) ) {
						$refunded_amount += abs( $inv_payment['Amt'] );
					}
				}
			}

			return $refunded_amount;
		}

		private function is_last_invoice_paid( $invoice ) {
			// lets get the jobs for the subscription
			$jobs = $this->ifsdk->get_subscriptionid_jobs( $invoice['SubscriptionId'] );
			if ( ! $jobs ) {
				return false; // no job then unpaid
			}
			$job_ids      = array_map(
				function( $arr ) {
					return $arr['Id'];
				},
				$jobs
			); // we only need the ids
			$latest_jobid = max( $job_ids ); // get the latest invoice of this subscription

			$latest_invoice = $this->ifsdk->get_jobid_invoice( $latest_jobid ); // get the invoice of this job
			if ( ! $latest_invoice ) {
				return false; // if invoice unpaid
			}
			return (bool) $latest_invoice['PayStatus'];
		}

		private function get_payplan_status( $invoiceid ) {
			$pp = $this->ifsdk->get_invoice_payplan( $invoiceid );
			if ( $pp ) {
				if ( ! empty( $pp['StartDate'] ) && $pp['StartDate'] > wlm_date( 'Ymd\TH:i:s', strtotime( 'EST' ) ) ) {
					$ret = array(
						'PayPlanId' => $pp['Id'],
						'OverDue'   => false,
					);
				} else {
					$ret = array(
						'PayPlanId' => $pp['Id'],
						'OverDue'   => true,
					);
				}
			} else {
				$ret = false;
			}
			return $ret;
		}

		public function ConfirmApproveLevelsTags( $uid = null, $level = null ) {
			global $WishListMemberInstance;

			$user = get_userdata( $uid );

			$udata = array(
				'username'  => $user->user_login,
				'firstname' => $user->user_firstname,
				'lastname'  => $user->user_lastname,
				'email'     => $user->user_email,
				'wpm_id'    => $level[0],
			);

			$level_unconfirmed = false;
			if ( $WishListMemberInstance->level_unconfirmed( $level[0], $uid ) ) {
				$level_unconfirmed = true;
			}

			$level_for_approval = false;
			if ( $WishListMemberInstance->level_for_approval( $level[0], $uid ) ) {
				$level_for_approval = true;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'new',
				'data'   => $udata,
			);

			if ( ! $level_unconfirmed && ! $level_for_approval ) {
				$this->ifscAddQueue( $data );
			}
		}

		public function NewUserTagsQueue( $uid = null, $udata = null ) {

			global $WishListMemberInstance;

			$level_unconfirmed = false;
			if ( $WishListMemberInstance->level_unconfirmed( $udata['wpm_id'], $uid ) ) {
				$level_unconfirmed = true;
			}

			$level_for_approval = false;
			if ( $WishListMemberInstance->level_for_approval( $udata['wpm_id'], $uid ) ) {
				$level_for_approval = true;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'new',
				'data'   => $udata,
			);

			// Part of the Fix for issue where Add To levels aren't being processed.
			$user = get_userdata( $uid );
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				// Don't add the data into the queue if it's from a temp account
				null;
			} elseif ( $level_unconfirmed || $level_for_approval ) {
				// Don't add the data into the queue if the level's status is not active
				null;
			} else {
				$this->ifscAddQueue( $data );
			}

		}

		public function AddUserTagsQueue( $uid, $addlevels = '' ) {

			global $WishListMemberInstance;

			$level_unconfirmed = false;
			if ( $WishListMemberInstance->level_unconfirmed( $addlevels[0], $uid ) ) {
				$level_unconfirmed = true;
			}

			$level_for_approval = false;
			if ( $WishListMemberInstance->level_for_approval( $addlevels[0], $uid ) ) {
				$level_for_approval = true;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'add',
				'levels' => $addlevels,
			);

			// If from registration then don't don't process if the $addlevels is
			// the same level the user registered to. This is already processed by NewUserTagsQueue func.
			if ( isset( wlm_post_data()['action'] ) && 'wpm_register' == wlm_post_data()['action'] ) {
				if ( wlm_post_data()['wpm_id'] == $addlevels[0] ) {
					return;
				}
			}

			// Fix for issue where Add To levels aren't being processed.
			// If the data is from a temp account then add it to the queue API and don't process it for now.
			$user = get_userdata( $uid );
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				$this->ifscAddQueue( $data, 0 );
			} elseif ( $level_unconfirmed || $level_for_approval ) {
				// Don't add the data into the queue if the level's status is not active
				null;
			} else {
				$this->ifscAddQueue( $data );
			}
		}

		public function RemoveUserTagsQueue( $uid, $removedlevels = '' ) {
			// lets check for PPPosts
			$levels = (array) $removedlevels;
			foreach ( $levels as $key => $level ) {
				if ( false !== strrpos( $level, 'U-' ) ) {
					unset( $levels[ $key ] );
				}
			}
			if ( count( $levels ) <= 0 ) {
				return;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'remove',
				'levels' => $removedlevels,
			);
			$this->ifscAddQueue( $data );
		}

		public function CancelUserTagsQueue( $uid, $cancellevels = '' ) {
			// lets check for PPPosts
			$levels = (array) $cancellevels;
			foreach ( $levels as $key => $level ) {
				if ( false !== strrpos( $level, 'U-' ) ) {
					unset( $levels[ $key ] );
				}
			}
			if ( count( $levels ) <= 0 ) {
				return;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'cancel',
				'levels' => $cancellevels,
			);
			$this->ifscAddQueue( $data );
		}

		public function DeleteUserHookQueue( $uid ) {
			if ( ! $this->ifsdk || ! $this->ifsdk->is_api_connected() ) {
				return;
			}

			global $WishListMemberInstance;
			$levels = $WishListMemberInstance->get_membership_levels( $uid );
			foreach ( $levels as $key => $lvl ) {
				if ( false !== strpos( $lvl, 'U-' ) ) {
					unset( $levels[ $key ] );
				}
			}
			if ( ! is_array( $levels ) || count( $levels ) <= 0 ) {
				return; // lets return if no level was found
			}

			$contactid = $WishListMemberInstance->Get_UserMeta( $uid, 'wlminfusionsoft_contactid' );
			if ( ! $contactid ) {
				$contactid = get_user_meta( $uid, 'wlifcon_contactid', true );
			}

			if ( ! $contactid ) { // if no contactid, lets get it from the txnid using invoicenumber
				$txnids    = $WishListMemberInstance->get_membership_levels_txn_ids( $uid );
				$txnids    = (array) $txnids;
				$wlm_txnid = '';
				foreach ( $txnids as $id => $txnid ) {
					if ( false !== strpos( $txnid, 'InfusionSoft' ) ) {
						$wlm_txnid = $txnid;
						break;
					} elseif ( false !== strpos( $txnid, 'IFContact' ) ) {
						$wlm_txnid = $txnid;
						break;
					}
				}

				if ( ! empty( $wlm_txnid ) ) {
					if ( false !== strpos( $wlm_txnid, 'IFContact' ) ) {
						list( $marker, $contactid ) = explode( '-', $wlm_txnid, 2 );
					} elseif ( false !== strpos( $wlm_txnid, 'InfusionSoft' ) ) {
						list( $marker, $tid ) = explode( '-', $wlm_txnid, 2 );
						list( $iid, $sid )    = explode( '-', $tid, 2 );  // retrieve Invoice id and Sub id
						$contactid            = $this->ifsdk->get_contactid_by_invoice( $iid );
					}
				}
			}
			if ( ! $contactid ) {
				return; // lets return if no record in infusionsoft
			}

			$data = array(
				'uid'       => $uid,
				'contactid' => $contactid,
				'action'    => 'delete',
				'levels'    => $levels,
			);
			$this->ifscAddQueue( $data );
			return;
		}

		public function PPAddUserTagsQueue( $contentid, $levelid ) {
			$uid  = substr( $levelid, 2 );
			$user = get_userdata( $uid );
			if ( ! $user ) {
				return;
			}
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				return;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'add',
				'levels' => "payperpost-{$contentid}",
			);
			$this->ifscAddQueue( $data );
		}

		public function PPRemoveUserTagsQueue( $contentid, $levelid ) {
			$uid  = substr( $levelid, 2 );
			$user = get_userdata( $uid );
			if ( ! $user ) {
				return;
			}
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				return;
			}

			$data = array(
				'uid'    => $uid,
				'action' => 'remove',
				'levels' => "payperpost-{$contentid}",
			);
			$this->ifscAddQueue( $data );
		}

		public function NewUserTagsHook( $uid = null, $data = null ) {
			$tempacct = 'temp_' . md5( $data['orig_email'] ) == $data['email'];
			if ( $tempacct ) {
				return; // if temp account used by sc, do not process
			}
			$levels = (array) $data['wpm_id'];

			return $this->processTags( $levels, 'new', null, $uid, $data );
		}

		public function AddUserTagsHook( $uid, $newlevels = '' ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				return array(
					'errstr' => 'Invalid User ID.',
					'errno'  => 1,
				);
			}
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				return;
			}

			$levels = (array) $newlevels;
			return $this->processTags( $levels, 'add', null, $uid );
		}

		public function RemoveUserTagsHook( $uid, $removedlevels = '' ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				return array(
					'errstr' => 'Invalid User ID.',
					'errno'  => 1,
				);
			}
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				return;
			}

			$levels = (array) $removedlevels;
			return $this->processTags( $levels, 'remove', null, $uid );
		}

		public function CancelUserTagsHook( $uid, $removedlevels = '' ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				return array(
					'errstr' => 'Invalid User ID.',
					'errno'  => 1,
				);
			}
			if ( false !== strpos( $user->user_email, 'temp_' ) && 37 == strlen( $user->user_email ) && false === strpos( $user->user_email, '@' ) ) {
				return;
			}

			$levels = (array) $removedlevels;
			return $this->processTags( $levels, 'cancel', null, $uid );
		}

		public function DeleteUserTagsHook( $contactid, $levels = array() ) {
			$levels = (array) $levels;
			return $this->processTags( $levels, 'remove', $contactid, null );
		}

	}
}

$sc = new WLM_INTEGRATION_INFUSIONSOFT_INIT();
$sc->load_hooks();
