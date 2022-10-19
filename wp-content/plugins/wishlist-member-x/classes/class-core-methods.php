<?php
/**
 * Core Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Core Methods trait
*/
trait Core_Methods {
	// -----------------------------------------
	// Init Hook
	public function init() {
		// $this->cache = new WishListMemberCache($this->PluginSlug, $this->get_option('custom_cache_folder'));

		// process ping from HQ
		if ( isset( wlm_get_data()['_wlping_'] ) && isset( wlm_get_data()['_hash_'] ) ) {
			$this->process_wlping( wlm_get_data()['_wlping_'], wlm_get_data()['_hash_'] );
		}

		if ( 'wp-login.php' === $GLOBALS['pagenow'] && ! isset( $_COOKIE['wlm_login_cookie'] ) ) {
			$_COOKIE['wlm_login_cookie'] = 'WLM Login check';
		}

		// check for access levels
		// do not allow wlm to run it's own access_protection
		// let's control it via another plugin. That is much cleane
		global $wpdb;
		if ( defined( 'WLM_ERROR_REPORTING' ) ) {
			set_error_handler( array( &$this, 'error_handler' ), WLM_ERROR_REPORTING );
		}

		$this->MigrateLevelData();

		// migrate data pertaining to each content's membership level
		// this prepares us for user level content
		$this->MigrateContentLevelData();

		/*
		 * Short Codes
		 */
		$this->wlmshortcode = new \WishListMember\Shortcodes();

		/*
		 * Generate Transient Hash Session
		 * and Javascript Code
		 */
		if ( isset( wlm_get_data()['wlm_th'] ) ) {
			list($field, $name) = explode( ':', wlm_get_data()['wlm_th'] );
			header( 'Content-type:text/javascript' );
			$ckname = md5( 'wlm_transient_hash' );
			$hash   = md5( wlm_server_data()['REMOTE_ADDR'] . microtime() );
			wlm_setcookie( "{$ckname}[{$hash}]", $hash, 0, '/' );
			echo "<!-- \n\n";
			if ( 'field' === $field && ! empty( $name ) ) {
				echo 'document.write("<input type=\'hidden\' name=\'' . esc_js( $name ) . '\' value=\'' . esc_js( $hash ) . '\' />");';
				echo 'document.write("<input type=\'hidden\' name=\'bn\' value=\'WishListProducts_SP\' />");';
			} else {
				echo 'var wlm_cookie_hash="' . esc_attr( $hash ) . '";';
			}
			echo "\n\n// -->";
			exit;
		}
		/*
		 * End Transient Hash Code
		 */

		$wpm_levels = (array) $this->get_option( 'wpm_levels' );

		// load $this->attachments with list of attachments including resized versions
		/*
		 * WP Cron Hooks
		 */
		// Sync Membership
		if ( ! wp_next_scheduled( 'wishlistmember_syncmembership_count' ) ) {
			wp_schedule_event( time(), 'daily', 'wishlistmember_syncmembership_count' );
		}

		// Send Queued Email
		if ( ! wp_next_scheduled( 'wishlistmember_email_queue' ) ) {
			wp_schedule_event( time(), 'wlm_minute', 'wishlistmember_email_queue' );
		}

		// Process Queued Import
		if ( ! wp_next_scheduled( 'wishlistmember_import_queue' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_import_queue' );
		}

		// Process Queued Import
		if ( ! wp_next_scheduled( 'wishlistmember_backup_queue' ) ) {
			wp_schedule_event( time(), 'wlm_minute', 'wishlistmember_backup_queue' );
		}

		// process api queue
		if ( ! wp_next_scheduled( 'wishlistmember_api_queue' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_api_queue' );
		}

		// Unsubscribe Expired Members
		if ( ! wp_next_scheduled( 'wishlistmember_unsubscribe_expired' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_unsubscribe_expired' );
		}

		// Schedule the cron to run the cancelling of memberships. Glen Barnhardt 4-16-2010
		if ( ! wp_next_scheduled( 'wishlistmember_check_scheduled_cancelations' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_check_scheduled_cancelations' );
		}

		// Schedule the cron to run the cancelling of waiting level cancellations. Glen Barnhardt 10-27-2010
		if ( ! wp_next_scheduled( 'wishlistmember_check_level_cancelations' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_check_level_cancelations' );
		}

		// Schedule the cron to run the notification of members with incomplete registration. Fel Jun 10-27-2010
		if ( ! wp_next_scheduled( 'wishlistmember_registration_notification' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_registration_notification' );
		}

		// Schedule the cron to run the notification of members with incomplete registration. Fel Jun 10-27-2010
		if ( ! wp_next_scheduled( 'wishlistmember_email_confirmation_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'wishlistmember_email_confirmation_reminders' );
		}

		// Schedule the cron to run the notification for expiring members. Peter 02-20-2013
		if ( ! wp_next_scheduled( 'wishlistmember_expring_members_notification' ) ) {
			wp_schedule_event( time(), 'daily', 'wishlistmember_expring_members_notification' );
		}

		// Schedule the cron to run User Level modifications
		if ( ! wp_next_scheduled( 'wishlistmember_run_scheduled_user_levels' ) ) {
			// schedule the event daily.
			wp_schedule_event( time(), 'hourly', 'wishlistmember_run_scheduled_user_levels' );
		}

		// Schedule the cron to run User Level Actions
		if ( ! wp_next_scheduled( 'wishlistmember_run_user_level_actions' ) ) {
			// schedule the event daily.
			wp_schedule_event( time(), 'hourly', 'wishlistmember_run_user_level_actions' );
		}

		// Schedule the cron to run file protection migration
		if ( ! wp_next_scheduled( 'wishlistmember_migrate_file_protection' ) ) {
			// schedule the event twice daily.
			wp_schedule_event( time(), 'twicedaily', 'wishlistmember_migrate_file_protection' );
		}

		if ( wlm_get_data()['wlmfile'] ) {
			$this->file_protect_load_attachments();
			$this->file_protect( wlm_get_data()['wlmfile'] );
		}
		if ( wlm_get_data()['wlmfolder'] ) {
			if ( 1 == $this->get_option( 'folder_protection' ) ) {
				$this->folder_protect( wlm_get_data()['wlmfolder'], wlm_get_data()['restoffolder'] );
			}
		}

		$wpm_current_user = wp_get_current_user();

		if ( ( isset( wlm_get_data()['wlmfolderinfo'] ) ) && ( $wpm_current_user->caps['administrator'] ) ) {

			wlm_print_style( get_bloginfo( 'wpurl' ) . '/wp-admin/css/wp-admin.css' );

			// security check. we dont want display list of all files on the  server right? we make it limited only to folder protection folder even for admin
			$needle = $this->get_option( 'rootOfFolders' );
			// echo "<br>needle->".$needle;
			$haystack = wlm_get_data()['wlmfolderinfo'];
			// echo "<br>haystack->".$haystack;
			$pos = strpos( $haystack, $needle );

			if ( false === $pos ) {
				die();
			}

			$handle = opendir( wlm_get_data()['wlmfolderinfo'] );
			if ( $handle ) {
				?>
				<div style="padding-top:5px;padding-left:20px;">
					<table>
						<tr>
							<th> URL</th>
						</tr>
						<?php
						while ( false !== ( $file = readdir( $handle ) ) ) {
							// do something with the file
							// note that '.' and '..' is returned even
							if ( ! ( ( '.' === $file ) || ( '..' === $file ) || ( '.htaccess' === $file ) ) ) {
								?>
								<tr>

									<td> <?php echo esc_html( wlm_get_data()['wlmfolderLinkinfo'] . '/' . $file ); ?></td>

								</tr>

								<?php
							}
						}
						?>
					</table>
				</div>
				<?php
				closedir( $handle );
			}

			die();
		}

		if ( wlm_get_data()['clearRecentPosts'] ) {
			if ( is_admin() ) {
				$this->delete_option( 'RecentPosts' );
			}
		}

		// email confirmation
		if ( wlm_get_data()['wlmconfirm'] ) {
			list($uid, $hash) = explode( '/', wlm_get_data()['wlmconfirm'], 2 );
			$user             = new \WishListMember\User( $uid, true );
			$levelID          = $user->ConfirmByHash( $hash );
			if ( $levelID ) {
				// send welcome email
				$userinfo = $user->user_info->data;

				// get first name and last name using get_user_meta as $userinfo only got the display name
				$usermeta = get_user_meta( $userinfo->ID, $key, $single );

				delete_user_meta( $userinfo->ID, 'wlm_email_confirmation_reminder' );

				if ( $this->get_option( 'auto_login_after_confirm' ) ) {
					$this->wpm_auto_login( $uid );
				}
				wp_safe_redirect( $this->get_after_reg_redirect( $levelID, null, 'after_registration' ) );
				exit;
			}
		}

		/* we just save the original post and get data just in case we need them later */
		$this->OrigPost = wlm_post_data( true );
		$this->OrigGet  = wlm_get_data( true );
		/* remove unsecure information */

		unset( $this->OrigPost['password'] );
		unset( $this->OrigGet['password'] );
		unset( $this->OrigPost['password1'] );
		unset( $this->OrigGet['password1'] );
		unset( $this->OrigPost['password2'] );
		unset( $this->OrigGet['password2'] );

		/* load extensions */
		foreach ( (array) $this->extensions as $extension ) {
			include_once $extension;
			$this->register_extension( $WLMExtension['Name'], $WLMExtension['URL'], $WLMExtension['Version'], $WLMExtension['Description'], $WLMExtension['Author'], $WLMExtension['AuthorURL'], $WLMExtension['File'] );
		}

		if ( false !== strpos( urldecode( wlm_server_data()['REQUEST_URI'] ), '/wlmapi/2.0/' ) ) {
			if ( file_exists( $this->plugindir . '/core/API2.php' ) ) {
				require_once WLM_PLUGIN_DIR . '/legacy/core/API2.php';
				preg_match( '/\/wlmapi\/2\.0\/(xml|json|php)?\//i', urldecode( wlm_server_data()['REQUEST_URI'] ), $return_type );
				$return_type = $return_type[1];
				$wlmapi      = new \WLMAPI2( 'EXTERNAL' );
				switch ( $wlmapi->return_type ) {
					case 'XML':
						header( 'Content-type: text/xml' );
						break;
					case 'JSON':
						header( 'Content-type: application/json' );
						break;
					default:
						header( 'Content-type: text/plain' );
						break;
				}

				// clean output buffering to make sure nothing gets sent over with our API response
				@ob_end_clean();
				fwrite( WLM_STDOUT, $wlmapi->result );

				// record API used
				$api_used = $this->get_option( 'WLMAPIUsed' );
				$date     = wlm_date( 'Y-m-d' );
				if ( $api_used ) {
					$api_used = (array) wlm_maybe_unserialize( $api_used );
					if ( isset( $api_used['api2'] ) && $api_used['api2']['date'] == $date ) {
						$request                     = (int) $api_used['api2']['request'];
						$api_used['api2']['request'] = $request + 1;
					} else {
						$arr              = array(
							'request' => 1,
							'date'    => $date,
						);
						$api_used['api2'] = $arr;
					}
				} else {
					$arr              = array(
						'request' => 1,
						'date'    => $date,
					);
					$api_used['api2'] = $arr;
				}
				$this->save_option( 'WLMAPIUsed', wlm_maybe_serialize( (array) $api_used ) );

				exit;
			}
		}

		if ( ! defined( 'WLMCANSPAM' ) ) {
			define( 'WLMCANSPAM', sprintf( __( "If you no longer wish to receive communication from us:\n%1\$s=%2\$s\n\nTo update your contact information:\n%3\$s", 'wishlist-member' ), get_bloginfo( 'url' ) . '/?wlmunsub', '%s', get_bloginfo( 'wpurl' ) . '/wp-admin/profile.php' ) );
		}

		$this->Permalink = (bool) get_option( 'permalink_structure' ); // we get permalink status

		if ( wlm_post_data()['cookiehash'] ) {
			@wlm_inject_cookie( 'wishlist_reg_cookie', stripslashes( wlm_post_data()['cookiehash'] ), 0, '/' );
		}

		if ( wlm_get_data()['wlmunsub'] ) {
			list($uid, $key) = explode( '/', wlm_get_data()['wlmunsub'] );
			$mykey           = substr( md5( $uid . AUTH_SALT ), 0, 10 );
			$user            = $this->get_user_data( $uid );
			if ( $user->ID && $mykey == $key ) {
				$this->Update_UserMeta( $user->ID, 'wlm_unsubscribe', 1 );
				if ( 1 == $this->get_option( 'unsub_notification' ) ) {
					$recipient_email = '' == wlm_trim( $this->get_option( 'unsubscribe_notice_email_recipient' ) ) ? get_bloginfo( 'admin_email' ) : $this->get_option( 'unsubscribe_notice_email_recipient' );
					$this->send_email_template( 'admin_unsubscribe_notice', $user->ID, array(), $recipient_email );
				}

				$this->send_unsubscribe_notification_to_user( $user );

				$url = $this->unsubscribe_url();
				if ( $url ) {
					header( 'Location:' . $url );
					exit;
				} else {
					add_action( 'wp_head', array( &$this, 'unsub_javascript' ) );
				}
			}
		}

		if ( wlm_get_data()['wlmresub'] ) {
			list($uid, $key) = explode( '/', wlm_get_data()['wlmresub'] );
			$mykey           = substr( md5( $uid . AUTH_SALT ), 0, 10 );
			$user            = $this->get_user_data( $uid );
			if ( $user->ID && $mykey == $key ) {
				$this->Delete_UserMeta( $user->ID, 'wlm_unsubscribe' );
			}
			$url = $this->resubscribe_url();
			if ( $url ) {
				header( 'Location:' . $url );
				exit;
			} else {
				add_action( 'wp_head', array( &$this, 'resub_javascript' ) );
			}
		}

		if ( wlm_get_data()['loginlimit'] ) {
			add_filter(
				'wp_login_errors',
				function( $errors ) {
					$errors->add( 'wlm_loginlimit', $this->get_option( 'login_limit_error' ) );
					return $errors;
				}
			);
		}

		// process registration URL...
		$scuri = $this->registration_url();

		if ( 1 == wlm_get_data()['wpm_download_sample_csv'] ) {
			$this->sample_import_csv();
		}

		if ( $scuri ) {
			// strip out trailing .php
			$scuri = preg_replace( '/\.php$/i', '', $scuri );

			// match the URL with an SC Method
			$scuris = array_keys( (array) $this->SCIntegrationURIs );
			foreach ( (array) $scuris as $x ) {
				if ( $this->get_option( $x ) == $scuri ) {
					$scuri = $x;
					break;
				}
			}

			// get the method name to call for the shoppingcart
			if ( isset( $this->SCIntegrationURIs[ $scuri ] ) ) {
				$scmethod                               = $this->SCIntegrationURIs[ $scuri ];
				wlm_post_data()['WishListMemberAction'] = 'WPMRegister';
			} else {
				do_action( 'wishlistmember_paymentprovider_handler', $scuri );
				// not a valid SC Integration URI - we terminate.
				$this->cart_integration_terminate( $scuri );
				// not a valid SC Integration URI - we redirect to homepage
				/*
					header("Location: ".get_bloginfo('url'));
					exit;
				 */
			}
		}

		switch ( wlm_post_data()['WishListMemberAction'] ) {
			case 'ResetPrivacyEmailTemplates':
				$this->reset_privacy_template();
				break;
			case 'SaveCustomRegForm':
				$this->save_custom_reg_form();
				break;
			case 'CloneCustomRegForm':
				$this->clone_custom_reg_form( wlm_post_data()['form_id'] );
				break;
			case 'DeleteCustomRegForm':
				$this->delete_custom_reg_form( wlm_post_data()['form_id'] );
				break;
			case 'SaveMembershipLevels':
				$this->save_membership_levels();
				break;
			case 'SaveMembershipContent':
				$this->save_membership_content();
				break;
			case 'SaveMembershipContentPayPerPost':
				$this->save_membership_content_pay_per_post();
				break;
			case 'EasyFolderProtection':
				$this->easy_folder_protection();
				break;
			case 'FolderProtectionParentFolder':
				$this->folder_protection_parent_folder();
				break;
			case 'SaveMembersData':
				$this->save_members_data();
				break;
			case 'MoveMembership':
				$this->move_membership();
				break;
			case 'ImportMembers':
				$this->queue_import_members();
				break;
			case 'ExportMembersChunked':
				$this->export_members_chunked();
				break;
			case 'ExportSettingsToFile':
				$this->export_settings_to_file();
				break;
			/* start - backup stuff */
			case 'BackupSettings':
				$this->backup_generate();
				break;
			case 'RestoreSettings':
				$this->backup_restore( wlm_post_data()['SettingsName'], false );
				break;
			case 'ImportSettings':
				$this->backup_import( 1 == wlm_post_data()['backup_first'] );
				break;
			case 'ExportSettings':
				$this->backup_download( wlm_post_data()['SettingsName'] );
				break;
			case 'DeleteSettings':
				$this->backup_delete( wlm_post_data()['SettingsName'] );
				break;
			case 'ResetSettings':
				$this->reset_settings();
				break;
			/* end - backup stuff */
			case 'SaveSequential':
				$this->save_sequential_upgrade_configuration();
				break;
			case 'WPMRegister':
				// Added by Admin
				if ( true === wlm_admin_in_admin() ) {
					$wpm_errmsg = '';
					$registered = $this->wpm_register( wlm_post_data( true ), $wpm_errmsg );
					if ( $registered ) {
						$_POST = array( 'msg' => __( '<b>New Member Added.</b>', 'wishlist-member' ) );
					} else {
						wlm_post_data()['notice'] = $wpm_errmsg;
					}
				} elseif ( wlm_post_data( true ) ) {
					$docart = true;
					/*
					 * this is an attempt to prevent duplicate shopping cart registration posts
					 * from being processed it will definitely have its side effects but let's
					 * give it a try and see if people will complain
					 */

					if ( $this->get_option( 'PreventDuplicatePosts' ) && $scmethod ) {

						// do not check for duplicate posts for PayPalPS short URL
						if ( ( 'WLM_INTEGRATION_PAYPAL' === $scmethod['class'] && ! empty( wlm_get_data()['pid'] ) ) ) {
							// do not check for duplicate posts on Stripe's action=sync
							null;
						} elseif ( ( 'WLM_INTEGRATION_STRIPE' === $scmethod['class'] && ( 'sync' == wlm_get_data()['stripe_action'] ) ) ) {
							null;
						} else {
							$now         = time();
							$recentposts = (array) $this->get_option( 'RecentPosts' );
							/*
							 * we now compute posthash from both $_GET and $_POST and not
							 * just from $_POST because some integrations don't send $_POST
							 * data but $_GET.
							 */
							$posthash = md5( serialize( wlm_get_data( true ) ) . serialize( wlm_post_data( true ) ) );

							asort( $recentposts );
							foreach ( (array) array_keys( (array) $recentposts ) as $k ) {
								if ( $recentposts[ $k ] < $now ) {
									unset( $recentposts[ $k ] );
								}
							}
							if ( $recentposts[ $posthash ] ) {
								$docart = false;
								$url    = $this->duplicate_post_url();
								if ( $url == $this->request_url() ) {
									$url = get_bloginfo( 'url' );
								}
								header( "Location: {$url}" );
								exit;
							} else {
								$recentposts[ $posthash ] = $now + WLM_DUPLICATE_POST_TIMEOUT;
							}
							$this->save_option( 'RecentPosts', $recentposts );
						}
					}
					if ( $docart ) {
						// we save original $_POST to see if it will change
						$op = serialize( wlm_post_data( true ) );
						if ( ! class_exists( $scmethod['class'] ) ) {
							include_once $this->plugindir . '/lib/' . $scmethod['file'];
						}
						$this->RegisterClass( $scmethod['class'] );
						call_user_func( array( &$this, $scmethod['method'] ) );

						// record shopping cart used
						$shoppingcart_used = $this->get_option( 'WLMShoppinCartUsed' );
						$date              = wlm_date( 'Y-m-d H:i:s' );
						if ( $shoppingcart_used ) {
							$shoppingcart_used                        = (array) wlm_maybe_unserialize( $shoppingcart_used );
							$shoppingcart_used[ $scmethod['method'] ] = $date;
						} else {
							$shoppingcart_used[ $scmethod['method'] ] = $date;
						}
						$this->save_option( 'WLMShoppinCartUsed', wlm_maybe_serialize( (array) $shoppingcart_used ) );
						/*
							// $_POST didn't changed - nothing happened, we redirect to homepage. This avoids 404 to be returned for the SC URIs
							if(serialize(wlm_post_data( true ) )==$op){
							header("Location: ".get_bloginfo('url'));
							exit;
							}
						 */
					}
					$this->cart_integration_terminate();
				}
				break;
			case 'EmailBroadcast':
				// email broadcast
				$this->email_broadcast();
				break;
			case 'DoMarketPlaceActions':
				// marketplace actions
				$this->do_market_place_actions();
		}

		// check that each level has a reg URL specified
		$changed = false;
		foreach ( (array) array_keys( (array) $wpm_levels ) as $k ) {
			if ( ! $wpm_levels[ $k ]['url'] ) {
				$wpm_levels[ $k ]['url'] = $this->pass_gen( 6 );
				$changed                 = true;
			}
		}
		if ( $changed
		) {
			$this->save_option( 'wpm_levels', $wpm_levels );
		}

		// check if all levels have expirations specified
		$unspecifiedexpiration = array();
		foreach ( (array) $wpm_levels as $level ) {
			if ( ! wlm_arrval( $level, 'expire' ) && ! wlm_arrval( $level, 'noexpire' ) && wlm_arrval( $level, 'name' ) ) {
				$unspecifiedexpiration[] = $level['name'];
			}
		}
		if ( count( $unspecifiedexpiration ) ) {
			$GLOBALS['unspecifiedexpiration'] = $unspecifiedexpiration;
		}

		$wpm_current_user = wp_get_current_user();
		// No profile editing for members
		if ( $wpm_current_user->ID && 'wp-admin' == basename( dirname( wlm_server_data()['PHP_SELF'] ) ) && 'profile.php' == basename( wlm_server_data()['PHP_SELF'] ) && ! $this->get_option( 'members_can_update_info' ) && ! $wpm_current_user->caps['administrator'] && ! $this->get_option( 'members_can_update_info' ) && ! current_user_can( 'level_8' ) ) {
			header( 'Location:' . get_bloginfo( 'url' ) );
			exit;
		}

		// Do not allow access to Dashboard for non-admins
		if ( $wpm_current_user->ID && 'wp-admin/index.php' == basename( dirname( wlm_server_data()['PHP_SELF'] ) ) . '/' . basename( wlm_server_data()['PHP_SELF'] ) && ! current_user_can( 'edit_posts' ) && ! current_user_can( 'level_8' ) ) {
			header( 'Location:profile.php' );
			exit;
		}

		if ( $wpm_current_user->ID ) {
			if ( empty( wlm_getcookie( 'wlm_user_sequential' ) ) ) {
				$this->do_sequential( $wpm_current_user->ID );
				$this->process_scheduled_level_actions( $wpm_current_user->ID );
				wlm_setcookie( 'wlm_user_sequential', 1, time() + 3600, home_url( '/', 'relative' ) );
				wlm_setcookie( 'wlm_user_sequential', 1, time() + 3600, site_url( '/', 'relative' ) );
			}
		}

		// spawn cron job if requested
		if ( 1 == wlm_get_data()['wlmcron'] ) {
			spawn_cron();
			exit;
		}

		// send registration notification by force without waiting for the cron
		if ( 1 == wlm_get_data()['regnotification'] ) {
			$this->notify_registration();
			exit;
		}

		// send registration notification by force without waiting for the cron
		if ( 1 == wlm_get_data()['emailconfirmationreminders'] ) {
			$this->email_confirmation_reminders();
			exit;
		}

		// send expiring members notification by force without waiting for the cron
		if ( 1 == wlm_get_data()['expnotification'] ) {
			$this->expiring_members_notification();
			exit;
		}

		if ( wlm_get_data()['wlmprocessapiqueues'] > 0 ) {
			do_action( 'wishlistmember_api_queue', wlm_get_data()['wlmprocessapiqueues'] );
			exit;
		}

		if ( wlm_get_data()['wlmprocessbroadcast'] > 0 ) {
			$x = $this->send_queued_mail();
			exit;
		}

		if ( wlm_get_data()['wlmprocessimport'] > 0 ) {
			$x = $this->process_import_members();
			exit;
		}

		if ( wlm_get_data()['wlmprocessbackup'] > 0 ) {
			$x = $this->process_backup_queue();
			exit;
		}

		if ( wlm_get_data()['syncmembership'] > 0 ) {
			$wpm_current_user = wp_get_current_user();
			if ( $wpm_current_user->caps['administrator'] ) {
				$this->sync_membership_count();
				echo 'Done!';
				exit;
			}
		}

		// temporary fix for wpm_useraddress
		$this->fix_user_address( 1 );

		// get term_ids for OnlyShowContentForLevel
		$this->taxonomyIds = array();

		$this->taxonomies = get_taxonomies(
			array(
				'_builtin'     => false,
				'hierarchical' => true,
			),
			'names'
		);
		array_unshift( $this->taxonomies, 'category' );
		foreach ( $this->taxonomies as $taxonomy ) {
			add_action( $taxonomy . '_edit_form_fields', array( &$this, 'category_form' ) );
			add_action( $taxonomy . '_add_form_fields', array( &$this, 'category_form' ) );
			add_action( 'create_' . $taxonomy, array( &$this, 'save_category' ) );
			add_action( 'edit_' . $taxonomy, array( &$this, 'save_category' ) );
		}
		$this->taxonomyIds = get_terms(
			$this->taxonomies,
			array(
				'fields'  => 'ids',
				'get'     => 'all',
				'orderby' => 'none',
			)
		);
		// Cateogry Protection
		// error_reporting($error_reporting);
	}
	/**
	 * Activation
	 */
	public function activate() {
		global $wpdb;

		$this->CoreActivate();

		/* create WishList Member DB Tables */
		$this->CreateWLMDBTables();

		/* This is where you place code that runs on plugin activation */

		/* load all initial values */
		require $this->plugindir . '/core/InitialValues.php';
		if ( is_array( $wishlist_member_initial_data ) ) {
			foreach ( $wishlist_member_initial_data as $key => $value ) {
				$this->add_option( $key, $value );
			}
		}
		include_once $this->plugindir . '/core/OldValues.php';
		if ( is_array( $wishlist_member_old_initial_values ) ) {
			foreach ( $wishlist_member_old_initial_values as $key => $values ) {
				foreach ( (array) $values as $value ) {
					if ( strtolower( preg_replace( '/\s/', '', $this->get_option( $key ) ) ) === strtolower( preg_replace( '/\s/', '', $value ) ) ) {
						$this->save_option( $key, $wishlist_member_initial_data[ $key ] );
					}
				}
			}
		}

		// update lostinfo email subject.
		if ( ! $this->get_option( 'lostinfo_email_subject_spam_fix_re' ) && 'RE: Your membership login info' === $this->get_option( 'lostinfo_email_subject' ) ) {
			$this->save_option( 'lostinfo_email_subject', $wishlist_member_initial_data['lostinfo_email_subject'] );
			$this->save_option( 'lostinfo_email_subject_spam_fix_re', 1 );
		}

		$apikey = $this->get_option( 'genericsecret' );
		if ( empty( $apikey ) ) {
			$apikey = wlm_generate_password( 50, false );
		}

		$this->add_option( 'WLMAPIKey', $apikey );

		$user = new \WP_User( 1 );
		if ( $user ) {
			$name = wlm_trim( $user->first_name . ' ' . $user->last_name );
			if ( ! $name ) {
				$name = $user->display_name;
			}
			if ( ! $name ) {
				$name = $user->user_nicename;
			}
			if ( ! $name ) {
				$name = $user->user_login;
			}
			$this->add_option( 'email_sender_name', $name );
			$this->add_option( 'email_sender_address', $user->user_email );
			$this->add_option( 'newmembernotice_email_recipient', $user->user_email );
		}

		/* add file protection htaccess */
		$this->file_protect_htaccess( ! ( 1 === (int) $this->get_option( 'file_protection' ) ) );

		$wpm_levels = $this->get_option( 'wpm_levels' );
		/* membership levels cleanup */
		if ( is_array( $wpm_levels ) && count( $wpm_levels ) ) {
			foreach ( $wpm_levels as $key => $level ) {
				/* add slugs to membership levels that don't have slugs */
				if ( empty( $level['slug'] ) ) {
					$level['slug'] = $this->sanitize_string( $level['name'] );
				}

				/*
				 * turn off sequential upgrade for levels that match any of the ff:
				 * - no upgrade method specified
				 * - no upgrade to specified and method is not remove
				 * - have 0-day moves
				 */
				if (
					// no upgrade method at all.
					empty( $level['upgradeMethod'] )
					// no upgrade destination and method is not REMOVE.
					|| ( empty( $level['upgradeTo'] ) && 'REMOVE' !== $level['upgradeMethod'] )
					// 0-Day Moves
					|| ( 'MOVE' === $level['upgradeMethod'] && ! ( (int) $level['upgradeAfter'] ) && empty( $level['upgradeSchedule'] ) )
				) {
					$level['upgradeMethod'] = '0';
					$level['upgradeTo']     = '0';
					$level['upgradeAfter']  = '0';
				}

				/* Migrate Add To Feature to Level Actions */
				if ( ( isset( $level['addToLevel'] ) && is_array( $level['addToLevel'] ) && count( $level['addToLevel'] ) > 0 ) ) {
					$data = array(
						'level_action_event'  => 'added',
						'level_action_method' => 'add',
						'action_levels'       => array_keys( $level['addToLevel'] ),
						'inheritparent'       => isset( $level['inheritparent'] ) ? $level['inheritparent'] : 0,
						'sched_toggle'        => 'after',
						'sched_after_term'    => '0',
						'sched_after_period'  => 'days',
					);
					$this->LevelOptions->save_option( $key, 'scheduled_action', $data );
					$this->save_option( 'addto_feature_moved', 1 );
				}
				if ( ( isset( $level['removeFromLevel'] ) && is_array( $level['removeFromLevel'] ) && count( $level['removeFromLevel'] ) > 0 ) ) {
					$data = array(
						'level_action_event'  => 'added',
						'level_action_method' => 'remove',
						'action_levels'       => array_keys( $level['removeFromLevel'] ),
						'sched_toggle'        => 'after',
						'sched_after_term'    => '0',
						'sched_after_period'  => 'days',
					);
					$this->LevelOptions->save_option( $key, 'scheduled_action', $data );
					$this->save_option( 'addto_feature_moved', 1 );
				}
				// lets remove Add To Level feature data.
				unset( $level['addToLevel'] );
				unset( $level['removeFromLevel'] );

				$wpm_levels[ $key ] = $level;
			}
		} else {
			$wpm_levels = array();
		}
		$this->save_option( 'wpm_levels', $wpm_levels );

		// default login limit error.
		if ( '' === wlm_trim( $this->get_option( 'login_limit_error' ) ) ) {
			$this->save_option( 'login_limit_error', $wishlist_member_initial_data['login_limit_error'] );
		}

		// default minimum password length.
		if ( '' === wlm_trim( $this->get_option( 'min_passlength' ) ) ) {
			$this->save_option( 'min_passlength', $wishlist_member_initial_data['min_passlength'] );
		}

		/* Sync Membership Content */
		$this->sync_content();

		/* migrate old cydec (qpp) stuff to new cydec. qpp is now a separate deal */
		if ( 1 !== (int) $this->get_option( 'cydec_migrated' ) ) {
			if ( $this->add_option( 'cydecthankyou', $this->get_option( 'qppthankyou' ) ) ) {
				$this->delete_option( 'qppthankyou' );
			}

			if ( $this->add_option( 'cydecsecret', $this->get_option( 'qppsecret' ) ) ) {
				$this->delete_option( 'qppsecret' );
			}

			if ( 'qpp' === $this->get_option( 'lastcartviewed' ) ) {
				$this->save_option( 'lastcartviewed', 'cydec' );
			}

			$wpdb->query( 'UPDATE `' . esc_sql( $this->table_names->userlevel_options ) . '` SET `option_value`=REPLACE(`option_value`,"QPP","CYDEC") WHERE `option_name`="transaction_id" AND `option_value` LIKE "QPP\_%"' );

			$this->save_option( 'cydec_migrated', 1 );
		}

		$this->remove_cron_hooks();
		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			if ( function_exists( 'apache_get_modules' ) ) {
				$GLOBALS['wp_rewrite']->flush_rules();
			}
		}

		/* migrate file protection settings to table */
		$this->migrate_file_protection();

		/* migrate folder protection settings */
		$this->folder_protection_migrate(); // really old to old migration.
		$this->migrate_folder_protection(); // old to new migration.

		// Migrate old widget if active to new one that uses Class.
		$this->migrate_widget();

		// migrate data for scheduled add, move and remove to new format.
		$this->MigrateScheduledLevelsMeta();

		/*
		 * we clear xxxssapxxx% entries in the database
		 * removed in WLM 2.8 to prevent security issues
		 */
		$wpdb->query( 'DELETE FROM `' . esc_sql( $this->table_names->options ) . '` WHERE `option_name` LIKE "xxxssapxxx%"' );
	}

		// -----------------------------------------
		// Plugin Deactivation
	public function deactivate() {
		// $this->backup_generate();
		// we delete magic page
		wp_delete_post( $this->magic_page( false ), true );
		// remove file protection htaccess
		$this->file_protect_htaccess( true );
		// remove the cron schedule. Glen Barnhardt 4/16/2010
		$this->remove_cron_hooks();
	}

	public function wlm_cron_schedules( $schedules ) {
		$schedules['wlm_minute']    = array(
			'interval' => 60,
			'display'  => __( 'Every Minute (added by WishList Member)', 'wishlist-member' ),
		);
		$schedules['wlm_15minutes'] = array(
			'interval' => 900,
			'display'  => __( 'Every 15 Minute (added by WishList Member)', 'wishlist-member' ),
		);
		// add other intervals here
		return $schedules;
	}

	public function remove_cron_hooks() {
		$hooks  = apply_filters(
			'wishlistmember_remove_cron_hooks',
			array(
				'wishlistmember_eway_sync',
				'wishlistmember_1shoppingcart_check_orders_status',
				'wishlistmember_1shoppingcart_get_new_orders_detail',
				'wishlistmember_1shoppingcart_process_orders',
				'wishlistmember_1shoppingcart_update_orders_id',
				'wishlistmember_api_queue',
				'wishlistmember_arb_sync',
				'wishlistmember_attachments_load',
				'wishlistmember_check_level_cancelations',
				'wishlistmember_check_scheduled_cancelations',
				'wishlistmember_email_queue',
				'wishlistmember_import_queue',
				'wishlistmember_backup_queue',
				'wishlistmember_expring_members_notification',
				'wishlistmember_ifs_sync',
				'wishlistmember_registration_notification',
				'wishlistmember_email_confirmation_reminders',
				'wishlistmember_run_scheduled_user_levels',
				'wishlistmember_run_user_level_actions',
				'wishlistmember_syncmembership_count',
				'wishlistmember_unsubscribe_expired',
				'wishlistmember_migrate_file_protection',
			)
		);
		$scheds = (array) get_option( 'cron' );
		foreach ( $scheds as $sched ) {
			if ( is_array( $sched ) ) {
				foreach ( array_keys( $sched ) as $hook ) {
					if ( 'wishlistmember_' == substr( $hook, 0, 15 ) ) {
						$hooks[] = $hook;
					}
				}
			}
		}
		$hooks = array_unique( $hooks );

		foreach ( $hooks as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
	}

	public function error_handler( $errno, $errmsg, $errfile, $errline ) {
		static $errcodes;

		if ( ! isset( $errcodes ) ) {
			$errcodes = array(
				E_ERROR             => 'Fatal run-time error',
				E_WARNING           => 'Run-time warning',
				E_PARSE             => 'Compile-time parse error',
				E_NOTICE            => 'Run-time notice',
				E_CORE_ERROR        => 'Fatal initial startup error',
				E_CORE_WARNING      => 'Initial startup warning',
				E_COMPILE_ERROR     => 'Fatal compile-time error',
				E_COMPILE_WARNING   => 'Compile-time warnings',
				E_USER_ERROR        => 'User-generated error',
				E_USER_WARNING      => 'User-generated warning',
				E_USER_NOTICE       => 'User-generated notice',
				E_STRICT            => 'E_STRICT error',
				E_RECOVERABLE_ERROR => 'Catchable fatal error',
				E_DEPRECATED        => 'E_DEPRECATED error',
				E_USER_DEPRECATED   => 'E_USER_DEPRECATED error',
			);
		}

		if ( substr( $errfile, 0, strlen( $this->plugindir ) ) == $this->plugindir ) {
			echo '<br />WishList Member Debug. [This is a notification for developers who are working in WordPress debug mode.]';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$code = $errcodes[ $errno ];
				printf( '<br />%s<br />%s<br />Location: %s line number %s<br />', esc_html( $code ), esc_html( $errmsg ), esc_html( $errfile ), esc_html( $errline ) );
			}
		}
		return false;
	}
	// -----------------------------------------
	// Admin Head
	public function admin_head() {
		if ( ! ( current_user_can( 'manage_posts' ) ) ) {
			echo "<style type=\"text/css\">\n\n/* WishList Member */\ndivul#dashmenu{ display:none; }\n#wphead{ border-top-width:2px; }\n#screen-meta a.show-settings{display:none;}\n</style>\n";
		}
	}

	public function plugin_update_notice( $transient ) {
		static $our_transient_response;

		if ( empty( $transient ) ) {
			$transient = new \stdClass();
		}

		$version = current_user_can( 'update_plugins' ) ? wlm_arrval( $_REQUEST, 'wlm3_rollback' ) : '';

		if ( $this->plugin_is_latest() && ! $version ) {
			return $transient;
		}

		if ( ! $our_transient_response ) {
			$package = $this->plugin_download_url();
			if ( false === $package ) {
				return $transient;
			}

			$file = $this->PluginFile;

			$our_transient_response = array(
				$file => (object) array(
					'id'           => 'wishlist-member-' . time(),
					'slug'         => $this->PluginSlug,
					'plugin'       => $file,
					'new_version'  => $version ? $version : $this->plugin_latest_version(),
					'url'          => 'http://member.wishlistproducts.com/',
					'package'      => $package,
					'requires_php' => WLM_MIN_PHP_VERSION,
					'icons'        => array(
						'svg' => plugins_url( 'ui/images/WishListMember-logomark-16px-wp.svg', WLM_PLUGIN_FILE ),
					),
				),
			);
		}
		if ( ! isset( $transient->response ) ) {
			$transient->response = array();
		}
		$transient->response = array_merge( (array) $transient->response, (array) $our_transient_response );

		return $transient;
	}

	public function plugin_info_hook( $res, $action, $args ) {
		if ( false === $res && 'plugin_information' === $action && $args->slug == $this->PluginSlug ) {
			$res                  = new \stdClass();
			$res->name            = 'WishList Member&trade;';
			$res->slug            = $this->PluginSlug;
			$res->version         = $this->plugin_latest_version();
			$res->author          = WLM_PLUGIN_AUTHOR;
			$res->author_profile  = WLM_PLUGIN_AUTHORURI;
			$res->homepage        = WLM_PLUGIN_URI;
			$res->active_installs = wlm_arrval( (array) wp_remote_get( 'http://wishlistactivation.com/counter.php' ), 'body' ) + 0;
			$res->requires        = WLM_MIN_WP_VERSION;
			$res->requires_php    = WLM_MIN_PHP_VERSION;
			$res->banners         = array(
				'high' => 'https://wishlist-member-images.s3.amazonaws.com/wp-update-banner-2x.png',
				'low'  => 'https://wishlist-member-images.s3.amazonaws.com/wp-update-banner.png',
			);
			$res->sections        = array(
				'description' => '<p><strong>WishList Member&trade;</strong> is a powerful, yet easy to use membership software solution that can turn any WordPress site into a full-blown membership site.</p>'
				. '<p>Simply install the plugin, and within minutes you’ll have your own membership site up and running… complete with protected, members-only content, integrated payments, member management, and so much more!</p>',

				'changelog'   => '<p>WishList Member&trade; Changelog can be viewed <a href="https://customers.wishlistproducts.com/changelogs/" target="_blank">HERE</a>.</p>',

				'support'     => '<p>WishList Member&trade; offers support using the following options:</p>'
				. '<ul>'
				. '<li><a href="https://help.wishlistproducts.com/article-categories/video-tutorials/" target="_blank" title="Video Tutorials">Tutorials</a></li>'
				. '<li><a href="https://help.wishlistproducts.com/" target="_blank" title="Help">Help Docs</a></li>'
				. '<li><a href="http://codex.wishlistproducts.com/" target="_blank" title="API Documents">API Docs</a></li>'
				. '<li><a href="https://customers.wishlistproducts.com/support/" target="_blank" title="Support">Support</a></li>'
				. '</ul>',
			);
		}
		return $res;
	}

	public function pre_upgrade( $return, $plugin ) {
		$plugin = ( isset( $plugin['plugin'] ) ) ? $plugin['plugin'] : '';
		if ( $plugin == $this->PluginFile ) {
			$dir = sys_get_temp_dir() . '/' . sanitize_title( 'wishlist-member-upgrade-' . get_bloginfo( 'url' ) );

			$this->recursive_delete( $dir );

			$this->recursive_copy( $this->plugindir . '/extensions', $dir . '/extensions' );
			$this->recursive_copy( $this->plugindir . '/lang', $dir . '/lang' );
		}
		return $return;
	}

	public function post_upgrade( $return, $plugin ) {
		$plugin = ( isset( $plugin['plugin'] ) ) ? $plugin['plugin'] : '';
		if ( $plugin == $this->PluginFile ) {
			$dir = sys_get_temp_dir() . '/' . sanitize_title( 'wishlist-member-upgrade-' . get_bloginfo( 'url' ) );

			$this->recursive_copy( $this->plugindir . '/extensions', $dir . '/extensions' );
			$this->recursive_copy( $this->plugindir . '/lang', $dir . '/lang' );

			$this->recursive_copy( $dir . '/extensions', $this->plugindir . '/extensions' );
			$this->recursive_copy( $dir . '/lang', $this->plugindir . '/lang' );

			$this->recursive_delete( $dir );
		}
		return $return;
	}

	public function update_nag() {
		$current_screen = get_current_screen();
		if ( preg_match( '/^update/', $current_screen->id ) ) {
			return;
		}
		if ( ! $this->plugin_is_latest() ) {
			$latest_wpm_ver = $this->plugin_latest_version();
			if ( ! $latest_wpm_ver ) {
				$latest_wpm_ver = $this->Version;
			}

			global $current_user;
			$user_id                      = $current_user->ID;
							$dismiss_meta = 'dismiss_wlm_update_notice_' . $latest_wpm_ver;
			if ( ! get_user_meta( $user_id, $dismiss_meta ) && current_user_can( 'update_plugins' ) ) {
				echo "<div class='update-nag'>";
				// Translators: 1: Latest WLM Version.
				printf( esc_html__( 'The most current version of WishList Member is v%s.', 'wishlist-member' ), esc_html( $latest_wpm_ver ) );
				echo ' ';
				echo "<a href='" . esc_url( $this->plugin_update_url() ) . "'>";
				esc_html_e( 'Please update now. ', 'wishlist-member' );
				echo '</a> | ';
				echo '<a href="' . esc_url( add_query_arg( 'dismiss_notice', '0' ) ) . '"> Dismiss </a>';
				echo '</div>';
			}
		}
	}

	public function dismiss_wlm_update_notice() {

		global $current_user;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add that to their user meta */
		if ( ! $this->plugin_is_latest() ) {
			$latest_wpm_ver = $this->plugin_latest_version();
			if ( ! $latest_wpm_ver ) {
						$latest_wpm_ver = $this->Version;
			}

			$dismiss_meta = 'dismiss_wlm_update_notice_' . $latest_wpm_ver;
			if ( isset( wlm_get_data()['dismiss_notice'] ) && '0' == wlm_get_data()['dismiss_notice'] ) {
				add_user_meta( $user_id, $dismiss_meta, 'true', true );
			}
		}
	}



	public function accept_hq_announcement() {

			global $current_user;
			$user_id      = $current_user->ID;
			$dismiss_meta = 'dismiss_hq_notice';
			$announcement = $this->get_announcement();
		if ( ! empty( $announcement ) && ! get_user_meta( $user_id, $dismiss_meta ) && current_user_can( 'update_plugins' ) ) {
			echo "<br/><div class='update-nag'>";
			echo wp_kses_post( $announcement );
			echo ' ';
			echo '<a href="' . esc_url( add_query_arg( 'dismiss_hq_notice', '0' ) ) . '"> Dismiss </a>';
			echo '</div>';
		}
	}

	public function dismiss_hq_announcement() {

		global $current_user;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset( wlm_get_data()['dismiss_hq_notice'] ) && '0' == wlm_get_data()['dismiss_hq_notice'] ) {
				$dismiss_meta = 'dismiss_hq_notice';
				add_user_meta( $user_id, $dismiss_meta, 'true', true );
		}

	}

	public function dismiss_wlm_nag() {
		if ( ! empty( wlm_post_data()['nag_name'] ) ) {
			$this->add_option( wlm_post_data()['nag_name'], time() );
		}
	}

	/**
	 * Pre-upgrade checking
	 */
	public function upgrade_check() {
		if ( ! empty( wlm_get_data()['wlm3_rollback'] ) ) {
			return;
		}
		if ( 'update.php' == basename( wlm_server_data()['SCRIPT_NAME'] ) && 'upgrade-plugin' === wlm_get_data()['action'] && wlm_get_data()['plugin'] == $this->PluginFile ) {
			$check_result = wlm_trim( $this->ReadURL( add_query_arg( 'check', '1', $this->plugin_download_url() ), 10, true, true ) );
			if ( 'allowed' !== $check_result ) {
				header( 'Location: ' . $check_result );
				exit;
			}
		}
	}


	public function frontend_scripts_and_styles() {
		$magicpage = is_page( $this->magic_page( false ) );
		$fallback  = $magicpage | $this->is_fallback_url( wlm_get_data()['reg'] );

		if ( true === wlm_arrval( $this, 'force_registrationform_scripts_and_styles' ) || $magicpage || $fallback ) {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'wishlist_member_regform_prefill', $this->pluginURL . '/js/regform_prefill.js', array(), $this->Version );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'tb_images', $this->pluginURL . '/js/thickbox_images.js', array(), $this->Version );

			switch ( $this->get_option( 'FormVersion' ) ) {
				case 'improved':
					wp_enqueue_script( 'wishlist_member_improved_registration_js', $this->pluginURL . '/js/improved_registration_form_frontend.js', 'jquery-ui', $this->Version );
					wp_enqueue_style( 'wishlist_member_improved_registration_css', $this->pluginURL . '/css/improved_registration_form_frontend.css', 'jquery-ui', $this->Version );
					break;
				case 'themestyled':
					// scripts are enqueued as needed by wlm_form_field()
					break;
				default:
					wp_enqueue_style( 'wishlist_member_custom_reg_form_css', $this->pluginURL . '/css/registration_form_frontend.css', array(), $this->Version );
			}

			add_action( 'wp_print_footer_scripts', array( $this, 'regpage_form_data' ) );
		}
	}
			// -----------------------------------------
			// Footer Hook
	public function footer() {
		// terms of service & privacy policy
		$privacy = array();
		if ( $this->get_option( 'privacy_display_tos_on_footer' ) && $this->get_option( 'privacy_tos_page' ) ) {
			$page      = get_page( $this->get_option( 'privacy_tos_page' ) );
			$privacy[] = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( get_permalink( $page->ID ) ), esc_html( $page->post_title ) );
		}
		if ( $this->get_option( 'privacy_display_pp_on_footer' ) && $this->get_option( 'privacy_pp_page' ) ) {
			$page      = get_page( $this->get_option( 'privacy_pp_page' ) );
			$privacy[] = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( get_permalink( $page->ID ) ), esc_html( $page->post_title ) );
		}
		if ( $privacy ) {
			printf( '<p align="center">%s</p>', wp_kses_post( implode( ' | ', $privacy ) ) );
		}

		// show affiliate link
		if ( $this->get_option( 'show_linkback' ) ) {
			$url = 'http://member.wishlistproducts.com/';
			$aff = $this->get_option( 'affiliate_id' );
			if ( $aff && ! empty( $aff ) ) {
				if ( wp_http_validate_url( $aff ) ) {
					$url = esc_url( $aff );
				} else {
					$url = 'https://member.wishlistproducts.com/wlp.php?af=' . $aff;
				}
			}
			// translators: 1: affiliate url
			echo '<p align="center">' . wp_kses_post( sprintf( __( 'Powered by WishList Member - <a href="%1$s" target="_blank" title="Membership Software">Membership Software</a>', 'wishlist-member' ), esc_url( $url ) ) ) . '</p>';
		}
	}


			// -----------------------------------------
			// WP Head Hook
	public function wp_head() {
		global $post;
		echo '<!-- Running WishList Member X v' . esc_html( $this->Version ) . " -->\n";
		$p_id = isset( $post->ID ) ? $post->ID : '';

		$wpmpage = $this->magic_page( false );
		if ( (int) $p_id === (int) $wpmpage ) {
			echo '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW" />';
			echo "\n";
			echo '<META NAME="GOOGLEBOT" CONTENT="NOARCHIVE"/ >';
			echo "\n";
			echo '<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE"/ >';
			echo "\n";
			echo '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"/ >';
			echo "\n";
			echo '<META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 02 Aug 1999 01:02:03 GMT">';
			echo "\n";
		}

		$wlm_css = $this->get_option( 'wlm_css' ); // wlm3
		wp_register_style( 'wishlistmember-frontend-styles', false, array(), WLM_PLUGIN_VERSION );
		if ( false === $wlm_css ) {
			wp_add_inline_style( 'wishlistmember-frontend-styles', $this->get_option( 'reg_form_css' ) );
			wp_add_inline_style( 'wishlistmember-frontend-styles', $this->get_option( 'sidebar_widget_css' ) );
			wp_add_inline_style( 'wishlistmember-frontend-styles', $this->get_option( 'login_mergecode_css' ) );
		} else {
			wp_add_inline_style( 'wishlistmember-frontend-styles', $wlm_css );
		}
		wp_print_styles( 'wishlistmember-frontend-styles' );
	}
	public function dashboard_feeds() {
		$maxitems = 2;
		$defaults = array(
			'url'     => 'http://feeds.feedburner.com/wishlistmembernews',
			'age'     => 7,
			'dismiss' => 'dashboard_feed_dismissed',
		);

		$args = wp_parse_args( wlm_post_data( true ), $defaults );
		$rss  = fetch_feed( $args['url'] );
		if ( ! is_wp_error( $rss ) ) {
			$maxitems  = $rss->get_item_quantity( 1 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}

		$dismiss_timestamp = $this->get_option( $args['dismiss'] ) + 0;

		$date_now    = strtotime( 'now' );
		$rss_content = '';
		$results     = array();
		if ( $maxitems > 0 ) {
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) {
				$timestamp = $item->get_date( 'U' );
				$item_date = wlm_date( get_option( 'date_format' ), $timestamp );
				$date_diff = $date_now - $timestamp;
				$date_diff = $date_diff / 86400;
				// only show feeds less than 7 days old
				if ( $date_diff >= $args['age'] ) {
					continue;
				}
				if ( $timestamp <= $dismiss_timestamp ) {
					continue;
				}

				$results[] = array(
					'title'       => $item->get_title(),
					'content'     => $item->get_content(),
					'description' => $item->get_description(),
					'permalink'   => $item->get_permalink(),
				);
			}
		}
		wp_send_json( $results );
	}
}

// register hooks.
add_action(
	'wishlistmember_register_hooks',
	function( $wlm ) {
		add_action( 'admin_head', array( $wlm, 'admin_head' ), 1 );
		add_action( 'admin_init', array( $wlm, 'dismiss_hq_announcement' ) );
		add_action( 'admin_init', array( $wlm, 'dismiss_wlm_update_notice' ) );
		add_action( 'admin_init', array( $wlm, 'upgrade_check' ) );
		add_action( 'init', array( $wlm, 'init' ) );
		add_action( 'wp_ajax_wlm_dismiss_nag', array( $wlm, 'dismiss_wlm_nag' ) );
		add_action( 'wp_ajax_wlm_feeds', array( $wlm, 'dashboard_feeds' ) );
		add_action( 'wp_enqueue_scripts', array( $wlm, 'frontend_scripts_and_styles' ), 9999999999 );
		add_action( 'wp_footer', array( $wlm, 'footer' ) );
		add_action( 'wp_head', array( $wlm, 'wp_head' ) );
		add_filter( 'cron_schedules', array( $wlm, 'wlm_cron_schedules' ) );
		add_filter( 'plugins_api', array( $wlm, 'plugin_info_hook' ), 10, 3 );
		add_filter( 'site_transient_update_plugins', array( $wlm, 'plugin_update_notice' ) );
		add_filter( 'upgrader_post_install', array( $wlm, 'post_upgrade' ), 10, 2 );
		add_filter( 'upgrader_pre_install', array( $wlm, 'pre_upgrade' ), 10, 2 );
		register_deactivation_hook( WLM_PLUGIN_FILE, array( $wlm, 'deactivate' ) );
	}
);
