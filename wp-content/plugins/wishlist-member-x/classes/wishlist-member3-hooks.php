<?php
if ( ! class_exists( 'WishListMember3_Hooks' ) ) {
	class WishListMember3_Hooks extends WishListMember3_Actions {

		public function hooks_init() {
			register_activation_hook( $this->PluginFile, array( $this, 'wlm3_activate' ) );

			add_action( 'plugins_loaded', array( $this, 'version_change_check' ), 0 );

			add_filter( 'submenu_file', array( $this, 'submenu_file' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 1 );
			add_action( 'admin_print_scripts', array( $this, 'admin_enqueue_metabox_scripts' ), 1 );

			add_action( 'admin_enqueue_scripts', array( $this, 'restore_wp_mediaelement' ), 999999999 );

			// attempt to remove theme and plugin scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'remove_theme_and_plugins_scripts_and_styles' ), 999999999 );
			add_action( 'admin_head', array( $this, 'remove_theme_and_plugins_scripts_and_styles' ), 999999999 );
			add_action( 'admin_footer', array( $this, 'remove_theme_and_plugins_scripts_and_styles' ), 999999999 );

			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ), 0 );
			add_action( 'admin_title', array( $this, 'admin_title' ) );

			add_action( 'wp_ajax_admin_actions', array( $this, 'admin_actions' ) ); // saving settings using ajax
			add_action( 'wp_ajax_wlm3_get_screen', array( $this, 'ajax_get_screen' ) );
			add_action( 'wp_ajax_toggle_payment_provider', array( $this, 'ajax_toggle_payment_provider' ) );
			add_action( 'wp_ajax_toggle_email_provider', array( $this, 'ajax_toggle_email_provider' ) );
			add_action( 'wp_ajax_toggle_other_provider', array( $this, 'ajax_toggle_other_provider' ) );
			add_action( 'wp_ajax_regurl_exists', array( $this, 'regurl_exists' ) );
			add_action( 'wp_ajax_wlm3_update_level_property', array( $this, 'update_level_property' ) );
			add_action( 'wp_ajax_wlm3_export_settings', array( $this, 'export_settings' ) );
			add_action( 'wp_ajax_wlm3_download_sysinfo', array( $this, 'download_sysinfo' ) );

			add_action( 'wp_ajax_wlm3_get_level_stats', array( $this, 'get_level_stats' ) );

			add_action( 'wp_ajax_wlm3_save_postpage_settings', array( $this, 'save_postpage_settings' ) );
			add_action( 'wp_ajax_wlm3_dismiss_news', array( $this, 'dismiss_news' ) );
			add_action( 'wp_ajax_wlm3_post_page_ppp_user_search', array( $this, 'post_page_ppp_user_search' ) );
			add_action( 'wp_ajax_wlm3_add_user_ppp', array( $this, 'add_user_ppp' ) );
			add_action( 'wp_ajax_wlm3_remove_user_ppp', array( $this, 'remove_user_ppp' ) );
			add_action( 'wp_ajax_wlm3_save_postpage_system_page', array( $this, 'save_postpage_system_page' ) );
			add_action( 'wp_ajax_wlm3_save_postpage_displayed_tab', array( $this, 'save_postpage_displayed_tab' ) );
			add_action( 'wp_ajax_wlm3_save_postpage_pass_protection', array( $this, 'save_postpage_pass_protection' ) );

			add_filter( 'the_posts', array( $this, 'custom_error_page' ), 1 );
			add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );

			add_action( 'wishlistmember_ui_footer_scripts', array( $this, 'footer_scripts' ) );
			add_action( 'wishlistmember_ui_header_scripts', array( $this, 'header_scripts' ) );

			add_action( 'wishlistmember_admin_screen_notices', array( $this, 'admin_screen_notice' ), 10, 2 );
			// add_action( 'wishlistmember_admin_screen_notices', array( $this, 'admin_screen_beta_notice' ), 10, 2 );
			add_action( 'wishlistmember_admin_screen', array( $this, 'admin_screen' ), 10, 2 );
			// add_action( 'wishlistmember_add_user_levels', array( $this, 'send_registration_email_when_added_by_admin' ), 10, 3 );
			// add_action( 'wishlistmember_do_sequential_upgrade', array( $this, 'send_registration_email_on_sequential_upgrade' ), 10, 3 );

			add_filter( 'wlm_after_registration_redirect', array( $this, 'after_registration_redirect' ), 10, 2 );
			add_filter( 'wlm_after_login_redirect', array( $this, 'after_login_redirect' ), 10, 2 );
			add_filter( 'wlm_after_logout_redirect', array( $this, 'after_logout_redirect' ), 10, 2 );

			add_filter( 'wlm_after_registration_redirect', array( $this, 'ppp_after_registration_redirect' ), 10, 2 );
			add_filter( 'wlm_after_login_redirect', array( $this, 'ppp_after_login_redirect' ), 11, 2 );

			add_filter( 'wlm_after_login_redirect', array( $this, 'payperpost_as_after_login_redirect' ), 12, 3 );

			// custom post types
			add_filter( 'wishlistmember_current_admin_screen', array( $this, 'display_customposttypes_screen' ), 1 );
			add_filter( 'wishlist_member_menu', array( $this, 'add_customposttypes_menu' ), 1 );

			// email template
			add_filter( 'wishlistmember_pre_email_template', array( $this, 'pre_email_template' ), 10, 2 );
			add_filter( 'wishlistmember_template_mail_from_email', array( $this, 'template_mail_from' ), 10, 3 );
			add_filter( 'wishlistmember_template_mail_from_name', array( $this, 'template_mail_from' ), 10, 3 );
			add_filter( 'wishlistmember_email_template_recipient', array( $this, 'email_template_recipient' ), 10, 3 );

			// cancel / uncancel notifications
			add_action( 'wishlistmember_cancel_user_levels', array( $this, 'send_cancel_uncancel_notification' ), 10, 2 );
			add_action( 'wishlistmember_uncancel_user_levels', array( $this, 'send_cancel_uncancel_notification' ), 10, 2 );

			// email confirmed notification
			add_action( 'wishlistmember_confirm_user_levels', array( $this, 'send_email_confirmed_notification' ), 10, 2 );

			// reg form before and after
			add_filter( 'wishlistmember_before_registration_form', array( $this, 'regform_before' ), 10, 2 );
			add_filter( 'wishlistmember_after_registration_form', array( $this, 'regform_after' ), 10, 2 );

			// sync membership
			add_action( 'wishlistmember_syncmembership', array( $this, 'sync_membership' ), 10, 2 );

			add_action( 'init', array( $this, 'load_integrations' ) );
			add_action( 'init', array( $this, 'recheck_license' ) );
			add_action( 'init', array( $this, 'frontend_init' ) );
			add_action( 'init', array( $this, 'import_and_load_images' ) );

			// frontend styleshet
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ), 9999999999 );

			// wp login form customization
			add_action( 'login_enqueue_scripts', array( $this, 'login_screen_customization' ) );
			add_action( 'login_headerurl', 'home_url', 10, 0 );

			// wp front end media uploader
			// add_action( 'parse_query', array( $this, 'filter_media_by_user' ) );
			// add_filter( 'user_has_cap', array( $this, 'frontend_give_upload_permissions' ), 0 );
			// add_filter( 'upload_mimes', array( $this, 'restrict_upload_mimetypes' ) );

			add_filter( 'wishlist_member_legacy_menu', array( $this, 'wishlist_member_legacy_menu' ), 10, 2 );

			add_filter( 'upgrader_pre_download', array( $this, 'wishlist_member_upgrader_pre_download' ), 10, 3 );

			// auto-create accounts
			add_action( 'wishlistmember_autocreate_account', array( $this, 'autocreate_account_for_integration' ), 10, 4 );
			add_action( 'wishlistmember_finish_incomplete_registration', array( $this, 'finish_incomplete_registration' ), 10, 3 );

			add_action( 'plugin_action_links_' . plugin_basename( $this->pluginPath ), array( $this, 'add_settings_link' ) );

			// auto-add non-WLM registrations to level
			add_action( 'user_register', array( $this, 'autoadd_other_registrations' ) );

			add_filter( 'wishlistmember_per_level_template_setting', array( $this, 'send_newuser_email_notification_to_new_members_only' ), 10, 4 );

			add_action( 'wp_ajax_wlm3_generate_new_user_rss', array( $this, 'generate_new_user_rss' ) );

			// this creates a proper html message with an alternate text body
			add_action( 'phpmailer_init', array( $this, 'send_multipart_email' ) );

			// one-time login
			if ( $this->get_option( 'show_onetime_login_option' ) ) {
				add_action( 'login_form_wishlistmember-otl', array( $this, 'onetime_login_form' ) );
				add_action( 'login_footer', array( $this, 'onetime_login_footer' ) );
			}
			if ( wlm_get_data()['wlmotl'] ) {
				add_action( 'init', array( $this, 'onetime_login' ) );
			}

			add_action( 'wp_ajax_wlm3_generate_api_key', array( $this, 'generate_api_key' ) );

			// update the post meta box
			// @since 3.7
			add_action(
				'wp_ajax_wlm3_update_postbox',
				function() {
					return wishlistmember_instance()->post_page_options( get_post( wlm_post_data()['post_id'] ) );
				}
			);

			if ( $this->get_option( 'enable_retrieve_password_override' ) ) {
				add_filter( 'retrieve_password_message', array( $this, 'retrieve_password_hook' ), 10, 4 );
				add_action( 'retrieve_password/wlminternal', array( $this, 'retrieve_password_hook' ) );
			}

			/**
			 * Attempt to fix corrupted wpm_levels data
			 * Note: called by wishlistmember_get_option filter
			 *
			 * @since 3.7
			 *
			 * @param mixed   $value      Value to check
			 * @param string  $option     Option name
			 * @param mixed   $raw_value  Raw value (unserialized)
			 */
			add_filter(
				'wishlistmember_get_option',
				function( $value, $option, $raw_value ) {
					global $wpdb;
					// we only check for wpm_levels
					if ( 'wpm_levels' !== $option ) {
						return $value;
					}
					// empty $value but non-empty $raw_value
					if ( ! $value && $raw_value ) {
						// attempt to fix serialized data and then unserialize it
						$value = unserialize(
							preg_replace_callback(
								'/s:([0-9]+):\"(.*?)\";/',
								function ( $matches ) {
										return 's:' . strlen( $matches[2] ) . ':"' . $matches[2] . '";';
								},
								str_replace( array( "\r", "\n" ), array( '', '<br>' ), $raw_value )
							)
						);
						// if we get an array then save it
						if ( is_array( $value ) ) {
							// first save a backup of the original (broken) $raw_value
							// Note: WordPress will double serialize this data for some reason
							wishlistmember_instance()->save_option( $option . '.backup', $raw_value );

							// prepare data to save
							$data  = array(
								'option_name'  => $option,
								'option_value' => serialize( $value ),
							);
							$where = array(
								'option_name' => $data['option_name'],
							);
							// update wpm_levels with new fixed data
							$wpdb->update( wishlistmember_instance()->options_table, $data, $where );
						}
					}
					// return the fixed data
					return $value;
				},
				10,
				3
			);

			add_filter(
				'wlm_mergecodes',
				function( $merge_codes ) {
					$merge_codes[] = array(
						'title'  => 'Member Action Button',
						'value'  => '',
						'jsfunc' => 'wlmtnmcelbox_vars.member_action_button_lightbox',
					);

					return $merge_codes;
				}
			);

			/**
			 * Remove "signature" content from Magic Page on version change.
			 */
			add_action(
				'wishlistmember_version_changed',
				function() {
					add_action(
						'shutdown',
						function() {
							$page = get_post( wishlistmember_instance()->magic_page( false ) );
							if ( $page ) {
								$page->post_content = str_replace(
									array(
										'<p>This page is auto-generated by the WishList Member Plugin.</p>',
										'<p>The status of this page must be set to Published. Do not delete this page or put it to Trash.</p>',
										'This page is auto-generated by the WishList Member Plugin.',
										'The status of this page must be set to Published. Do not delete this page or put it to Trash.',
									),
									'',
									$page->post_content
								);
								wp_update_post( $page );
							}
						}
					);
				}
			);

			/**
			 * Display notice on magic page edit screen.
			 */
			add_action(
				'admin_init',
				function() {
					global $pagenow;
					if ( 'post.php' === $pagenow ) {
						if ( wishlistmember_instance()->magic_page( false ) == wlm_get_data()['post'] ) {
							// we do not need WLM protection for the magic page.
							remove_meta_box( 'wlm_postpage_metabox', 'page', 'normal' );
							// The message to display.
							$msg = __( 'This page is auto-generated by the WishList Member Plugin. The status of this page must be set to Published. Do not delete this page or put it to Trash.', 'wishlist-member' );
							// display notice for non-visual editor.
							add_action(
								'admin_notices',
								function() use ( $msg ) {
									print(
										'<div class="notice notice-warning">'
										. '<p>' . esc_html( $msg ) . '</p>'
										. '</div>'
									);
								}
							);
							// display notice for visual editor.
							add_action(
								'admin_head',
								function() use ( $msg ) {
									printf(
										'<script>'
										. 'var wlmonload = window.onload;'
										. 'window.onload = function() {'
										. ' try {'
										. '		( function ( wp ) {'
										. '			wp.data.dispatch( "core/notices" ).createWarningNotice('
										. '				"%1$s",'
										. '				{ isDismissible: false }'
										. '			);'
										. '		} )( window.wp );'
										. ' } finally {'
										. '		if ( typeof wlmonload == "function" ) {'
										. '			wlmonload();'
										. '		}'
										. '	}'
										. '}'
										. '</script>',
										esc_html( $msg )
									);
								}
							);
						}
					}
				}
			);

			/**
			 * Purge Login IP from all user meta.
			 */
			add_action(
				'wp_ajax_wishlistmember_purge_login_ip',
				function() {
					global $wpdb;
					$wpdb->query( 'DELETE FROM `' . esc_sql( wishlistmember_instance()->table_names->userlevel_options ) . '` WHERE `option_name` IN ( "wpm_login_ip", "wpm_login_counter", "wpm_registration_ip" )' );
					wp_send_json_success();
					exit;
				}
			);
		}

		public function wlm3_activate() {
			$this->activate(); // must be called here
			$this->content_control->activate();
		}

		public function admin_init() {
			if ( 1 == wlm_get_data()['wpm_download_sample_csv'] ) {
				$this->sample_import_csv();
			}
			$this->process_admin_actions(); // process WishlistMemberActions via POST
		}

		public function admin_actions() {
			$data   = wlm_post_data( true );
			$action = $data['WishListMemberAction'];
			unset( $data['action'] ); // remove action used for ajax
			unset( $data['WishListMemberAction'] ); // remove action used by WLM
			if ( isset( $data['wlmdelay'] ) ) {
				sleep( $data['wlmdelay'] );
				unset( $data['wlmdelay'] );
			}
			$result = $this->process_admin_ajax_actions( $action, $data );
			ob_clean(); // lets clean
			if ( is_array( $result ) || is_object( $result ) ) {
				wp_send_json( $result );
			} else {
				fwrite( WLM_STDOUT, $result );
			}
			wp_die(); // stop executing script
		}
		/**
		 * Generates the top level admin menus
		 */
		public function admin_menus() {
			$menus = $this->get_menus( 0 );

			$acl = new WishListAcl();
			if ( $acl->current_user_can( 'allow_plugin_WishListMember' ) ) {
				add_menu_page( $this->Title, $this->Title, 'read', $this->MenuID, array( $this, 'admin_page' ), $this->pluginURL3 . '/ui/images/WishListMember-logomark-16px-wp.svg', ( 1 == $this->get_option( 'menu_on_top' ) ? '2.01' : 99.363317 ) );

				foreach ( $menus as $menu ) {
					if ( $menu['legacy'] ) {
						continue;
					}
					$wl = 'dashboard' == $menu['key'] ? '' : '&amp;wl=' . $menu['key'];
					add_submenu_page( $this->MenuID, $this->Title . ' | ' . $menu['title'], $menu['name'], 'read', ( $this->MenuID . $wl ), array( $this, 'admin_page' ) );
				}
			}
		}
		/**
		 * Returns submenu file for proper menu highlighting
		 */
		public function submenu_file( $submenu, $parent ) {
			$wl = wlm_get_data()['wl'];
			if ( $parent == $this->MenuID && $wl ) {
				$wl    = explode( '/', $wl );
				$menus = $this->get_menus( 0 );
				foreach ( $menus as $menu ) {
					$key = explode( '/', $menu['key'] );
					if ( $key[0] == $wl[0] ) {
						return $this->MenuID . '&amp;wl=' . $menu['key'];
					}
				}
			}
			return $submenu;
		}

		public function header_scripts() {
			include_once $this->plugindir3 . '/ui/templates/form-group.php';
			include_once $this->plugindir3 . '/ui/templates/toggle-switch.php';
			include_once $this->plugindir3 . '/ui/templates/modal.php';
		}

		public function footer_scripts() {
			// placeholder
		}

		public function ajax_get_screen() {
			$url     = wlm_post_data()['data']['url'];
			$section = wlm_post_data()['data']['section'];

			ob_clean();

			if ( empty( $url ) ) {
				die( 'no url' );
			}
			if ( ! in_array( $section, array( 'the-screen', 'the-content' ) ) ) {
				die( 'no section' );
			}

			$this->ajaxurl = $url;

			parse_str( parse_url( $url, PHP_URL_QUERY ), $res );
			foreach ( $res as $k => $v ) {
				wlm_get_data()[ $k ] = $v;
			}

			switch ( $section ) {
				case 'the-content':
					$this->show_admin_page();
					break;
				case 'the-screen':
					$this->show_screen();
					break;
			}

			echo wp_json_encode(
				array(
					'html' => ob_get_clean(),
					'get'  => wlm_get_data( true ),
					'post' => wlm_post_data( true ),
					'js'   => $this->get_screen_js(),
				)
			);
			exit;
		}

		public function ajax_toggle_payment_provider() {
			$state    = wlm_post_data()['data']['state'];
			$provider = wlm_post_data()['data']['provider'];

			$active_carts = $this->toggle_payment_provider( $provider, $state );
			wp_send_json( array( 'actives' => array_values( $active_carts ) ) );
		}

		public function ajax_toggle_email_provider() {
			$state    = wlm_post_data()['data']['state'];
			$provider = wlm_post_data()['data']['provider'];

			$active_carts = $this->toggle_email_provider( $provider, $state );
			wp_send_json( array( 'actives' => array_values( $active_carts ) ) );
		}

		public function ajax_toggle_other_provider() {
			$state    = wlm_post_data()['data']['state'];
			$provider = wlm_post_data()['data']['provider'];

			$active_carts = $this->toggle_other_provider( $provider, $state );
			wp_send_json( array( 'actives' => array_values( $active_carts ) ) );
		}

		public function regurl_exists() {
			$regurl = wlm_post_data()['regurl'];
			$name   = wlm_post_data()['name'];

			echo json_encode( $this->reg_url_exists( $regurl, null, $name ) );
			exit;
		}

		public function custom_error_page( $content ) {
			if ( ! isset( wlm_get_data()['sp'] ) ) {
				return $content;
			}

			$sp = wlm_get_data()['sp'];

			if ( ! empty( wlm_get_data()['l'] ) ) {
				$level = wlm_get_data()['l'];
				if ( $this->is_user_level( $level ) || $this->is_ppp_level( $level ) ) {
					$ppp = $this->get_option( 'payperpost' );
					$c   = stripslashes( $ppp[ $sp . '_message' ] );
				} else {
					$wpm_levels = $this->get_option( 'wpm_levels' );
					if ( ! empty( $wpm_levels[ $level ] ) ) {
						$c = wlm_arrval( $wpm_levels[ $level ], $sp . '_message' );
					}
				}
			} elseif ( ! empty( wlm_get_data()['pid'] ) ) {
				$c = $this->get_option( $sp . '_message_' . wlm_get_data()['pid'] );
			} else {
				$c = $this->get_option( $sp . '_text' );
			}

			// default values based on templates
			if ( false === $c && isset( $this->page_templates[ $sp . '_internal' ] ) ) {
				$c = $this->page_templates[ $sp . '_internal' ];
			}

			$posts = $content;
			if ( is_page() && count( $posts ) ) {
				$post = &$posts[0];
				if ( $post->ID == $this->magic_page( false ) ) {
					$post->post_title   = '';
					$post->post_content = $c ? do_shortcode( $c ) : '';
				}
			}

			unset( $post );
			return $posts;
		}

		public function hide_admin_bar( $value ) {
			if ( current_user_can( 'manage_options' ) || ! is_user_logged_in() ) {
				return $value;
			}
			return (bool) $this->get_option( 'show_wp_admin_bar' );
		}

		public function _level_redirect( $url, $level_id, $index ) {
			$wpm_levels = $this->get_option( 'wpm_levels' );

			if ( empty( $wpm_levels[ $level_id ] ) ) {
				return $url;
			}

			$level = $wpm_levels[ $level_id ];

			$_custom = sprintf( 'custom_%s_redirect', $index );
			if ( empty( $level[ $_custom ] ) ) {
				return $url;
			}

			$_type = sprintf( '%s_redirect_type', $index );
			$type  = wlm_arrval( $level, $_type );
			$_url  = '';
			switch ( $type ) {
				case 'url':
					$_url = $level[ $index . '_url' ];
					break;
				case 'page':
					$_url = $level[ $index . '_page' ] ? get_permalink( $level[ $index . '_page' ] ) : home_url();
					break;
				default:
					$_url = add_query_arg(
						array(
							'sp' => $index,
							'l'  => $level_id,
						),
						$this->magic_page()
					);
			}
			if ( ! empty( $_url ) ) {
				$url = $_url;
			}
			return $url;
		}
		public function after_registration_redirect( $url, $level ) {
			return $this->_level_redirect( $url, $level, 'afterreg' );
		}
		public function after_login_redirect( $url, $level ) {
			return $this->_level_redirect( $url, $level, 'login' );
		}
		public function after_logout_redirect( $url, $level ) {
			return $this->_level_redirect( $url, $level, 'logout' );
		}

		public function _ppp_redirect( $url, $level, $index ) {
			if ( ! $this->is_ppp_level( $level ) ) {
				if ( false === $this->is_user_level( $level, true ) || ! $this->get_user_pay_per_post( $level, false, null, true ) ) {
					return $url;
				}
			}

			$ppp_settings = $this->get_option( 'payperpost' );
			if ( ! is_array( $ppp_settings ) ) {
				return $url;
			}

			$_custom = sprintf( 'custom_%s_redirect', $index );
			if ( ! wlm_arrval( $ppp_settings, $_custom ) ) {
				return $url;
			}

			$_type = sprintf( '%s_redirect_type', $index );
			$type  = wlm_arrval( $ppp_settings, $_type );
			$_url  = '';
			switch ( $type ) {
				case 'url':
					$_url = $ppp_settings[ $index . '_url' ];
					break;
				case 'page':
					$pid = $ppp_settings[ $index . '_page' ];
					if ( 'backtopost' === $pid ) {
						$pid = (int) substr( $level, 11 );
					}
					$_url = $pid ? get_permalink( $pid ) : home_url();
					break;
				default:
					$_url = add_query_arg(
						array(
							'sp' => $index,
							'l'  => $level,
						),
						$this->magic_page()
					);
			}
			if ( ! empty( $_url ) ) {
				$url = $_url;
			}
			return $url;

		}
		public function ppp_after_registration_redirect( $url, $level ) {
			return $this->_ppp_redirect( $url, $level, 'afterreg' );
		}
		public function ppp_after_login_redirect( $url, $level ) {
			return $this->_ppp_redirect( $url, $level, 'login' );
		}

		public function add_customposttypes_menu( $menus ) {
			$args       = array(
				// 'public'                => true,
				// 'exclude_from_search'   => false,
				   '_builtin' => false,
			);
			$post_types = get_post_types( $args, 'objects' );

			foreach ( $menus as $key => $value ) {

				if ( isset( $value['key'] ) && ( 'content_protection' == $value['key'] ) && count( $post_types ) > 0 ) {
					foreach ( $post_types as $k => $v ) {
						if ( wlm_post_type_is_excluded( $k ) ) {
							continue;
						}
						$new_menu = array(
							'key'   => "{$k}",
							'name'  => $v->label,
							'title' => $v->label,
							'icon'  => 'description',
							'sub'   => array(
								array(
									'key'   => 'content',
									'name'  => 'Content',
									'title' => 'Content',
								),
								array(
									'key'   => 'comments',
									'name'  => 'Comments',
									'title' => 'Comments',
								),
							),
						);
						$old      = $menus[ $key ]['sub'];
						array_splice( $old, 2, 0, array( $new_menu ) );
						$menus[ $key ]['sub'] = $old;
					}
				}

				// add content control
				if ( isset( $value['key'] ) && ( 'contentcontrol' == $value['key'] ) ) {
					// settings
					$new_menu = array(
						'key'   => 'settings',
						'name'  => 'Settings',
						'title' => 'Settings',
						'icon'  => 'settings',
						'sub'   => array(),
					);
					$old      = $menus[ $key ]['sub'];
					array_splice( $old, 1, 0, array( $new_menu ) );
					$menus[ $key ]['sub'] = $old;

					// only show if content control plugin is inactive
					if ( ! $this->content_control->old_contentcontrol_active ) {
						if ( $this->content_control->scheduler || $this->content_control->archiver || $this->content_control->manager ) {
							// posts
							$new_menu = array(
								'key'   => 'post',
								'name'  => 'Posts',
								'title' => 'Posts',
								'icon'  => 'description',
								'sub'   => array(),
							);
							$old      = $menus[ $key ]['sub'];
							array_splice( $old, 2, 0, array( $new_menu ) );
							$menus[ $key ]['sub'] = $old;
							// pages
							$new_menu = array(
								'key'   => 'page',
								'name'  => 'Pages',
								'title' => 'Pages',
								'icon'  => 'description',
								'sub'   => array(),
							);
							$old      = $menus[ $key ]['sub'];
							array_splice( $old, 3, 0, array( $new_menu ) );
							$menus[ $key ]['sub'] = $old;

							if ( count( $post_types ) > 0 ) {
								foreach ( $post_types as $k => $v ) {
									$new_menu = array(
										'key'   => "{$k}",
										'name'  => $v->label,
										'title' => $v->label,
										'icon'  => 'description',
										'sub'   => array(),
									);
									$old      = $menus[ $key ]['sub'];
									array_splice( $old, 4, 0, array( $new_menu ) );
									$menus[ $key ]['sub'] = $old;
								}
							}
						}
					}
				}
			}
			return $menus;
		}

		public function display_customposttypes_screen( $wl ) {
			$args       = array(
				// 'public'                => true,
				// 'exclude_from_search'   => false,
				   '_builtin' => false,
			);
			$post_types = get_post_types( $args, 'objects' );

			$wl_list = explode( '/', $wl );
			if ( isset( $wl_list[0] ) && isset( $wl_list[1] ) && 'content_protection' == $wl_list[0] ) {
				if ( array_key_exists( $wl_list[1], $post_types ) ) {
					$wl_list[1] = 'custom';
					$wl         = implode( '/', $wl_list );
				}
			}
			// content protection
			if ( isset( $wl_list[0] ) && isset( $wl_list[1] ) && 'contentcontrol' == $wl_list[0] ) {
				$post_types['post'] = 'Posts';
				$post_types['page'] = 'Pages';
				if ( array_key_exists( $wl_list[1], $post_types ) ) {
					$wl_list[1] = 'content';
					$wl         = implode( '/', $wl_list );
				}
			}
			return $wl;
		}

		public function admin_screen( $wl, $base ) {
			$wl_path      = $wl;
			$virtual_path = array();
			while ( strlen( $wl_path ) > 1 && ! file_exists( $base . $wl_path . '.php' ) ) {
				$virtual_path[] = basename( $wl_path );
				$wl_path        = dirname( $wl_path );
			}

			if ( $wl_path && '.' !== $wl_path && file_exists( $base . $wl_path . '.php' ) ) {
				include_once $base . $wl_path . '.php';
			}
			printf( '<script type="text/javascript">document.cookie="wlm3url=%s"</script>', esc_js( $wl ) );
		}

		public function admin_screen_notice( $wl, $base ) {
			// for content control feature
			if ( $this->content_control->old_contentcontrol_active && 'contentcontrol/settings' !== $wl ) {
				echo "<div class='form-text text-warning help-block mb-1'>
					<p class='mb-0'><strong>WishList Member:</strong> Please deactivate WishList Content Control plugin in order to use WishList Member Content Control feature.</p>
				</div>";
			}

			// show notice if paused.
			if ( 1 != $this->get_option( 'import_member_pause' ) || 'members/import' === $wl ) {
				return;
			}
			$api_queue   = new \WishListMember\API_Queue();
			$queue_count = $api_queue->count_queue( 'import_member_queue', 0 );
			if ( ! $queue_count ) {
				return;
			}
			$import_link = admin_url( "?page={$this->MenuID}&wl=members/import" );
			printf(
				"<div class='form-text text-warning help-block mb-1'>
					<p class='mb-0'><strong>WishList Member:</strong> The import of %s member(s) is currently on hold, please <a href='%s'>click here</a> to continue.</p>
				</div>",
				(int) $queue_count,
				esc_url( $import_link )
			);
		}

		public function admin_screen_beta_notice( $wl, $base ) {
			if ( 'dashboard' !== $wl ) {
				return;
			}

			$betadone = get_transient( 'wlm3_betadone' );
			if ( false === $betadone ) {
				$x = wp_remote_get( 'https://wlm3beta.wpengine.com/betadone.txt' );
				if ( is_array( $x ) ) {
					$betadone = wlm_trim( $x['body'] );
				} else {
					$betadone = '';
				}
				set_transient( 'wlm3_betadone', $betadone, 12 * HOUR_IN_SECONDS );
			}
			if ( '1' == $betadone ) {
				echo '<div class="form-text text-danger help-block" style="background:#ffe0dd">';
				printf( '<p><strong>%s</strong></p>', esc_html__( 'WishList Member 3.1 is now officially released. This BETA will no longer be updated.', 'wishlist-member' ) );
				printf( '<p class="mb-0">%s<a href="%2$s" target="_blank">%2$s</a></p>', esc_html__( 'Please visit here for more information on WishList Member 3.1:', 'wishlist-member' ), 'https://wishlistproducts.com/wlm3-official-release' );
				echo '</div>';
			} else {
				echo '<div class="form-text text-danger help-block mb-1">';
				printf( '<p><strong>WARNING</strong>: %s</p>', esc_html__( 'This version of WishList Member 3.1 is intended for BETA testing purposes only. You will very likely encounter issues/bugs as it is still in development. Do not use this version of WishList Member on a live site.', 'wishlist-member' ) );
				printf( '<p class="mb-0">%s <a href="%2$s" target="_blank">%2$s</a></p>', esc_html__( 'Please report any issues you find in our BETA Test forum:', 'wishlist-member' ), 'http://wlm3beta.wpengine.com/forum/' );
				echo '</div>';
			}
		}

		public function admin_enqueue_metabox_scripts() {
			global $post;
			if ( 'post' != get_current_screen()->base || ! $post || wlm_post_type_is_excluded( $post->post_type ) ) {
				return;
			}
			wlm_enqueue_style( 'post-page', $this->pluginURL3 . '/ui/css/post-page.css' );

			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'wlm_moment', "{$this->pluginURL3}/assets/js/moment.min.js", array( 'jquery' ), WLM_PLUGIN_VERSION );

			if ( function_exists( 'wp_enqueue_editor' ) ) {
				wp_enqueue_editor();
			}

			// wlm_enqueue_script( 'admin_main', $this->pluginURL . '/js/admin_main.js' );
			// wp_localize_script( 'wishlistmember3-js-admin_main', 'admin_main_js', array( 'wlm_feed_url' => admin_url( 'admin-ajax.php' ) ) );

			// select2
			wlm_select2();

			wlm_enqueue_style( 'daterangepicker', 'daterangepicker.css' );
			wlm_enqueue_script( 'daterangerpicker', 'daterangepicker.js', '', '', true );

		}

		public function admin_enqueue_scripts( $hook ) {
			global $wp_styles;
			if ( 'toplevel_page_' . $this->MenuID == $hook ) {

				echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n";

				/* start: fonts */

				printf( '<link rel="preload" href="%s/assets/fonts/wlm-iconset.woff?build=8261" as="font" crossorigin="anonymous">' . "\n", esc_url( $this->pluginURL3 ) );

				/* end: fonts */

				/* start: styles */

				// remove WP styling for forms and revisions as it messes with our layout
				wp_deregister_style( 'forms' );
				wp_register_style( 'forms', '', array(), WLM_PLUGIN_VERSION );
				wp_deregister_style( 'revisions' );
				wp_register_style( 'revisions', '', array(), WLM_PLUGIN_VERSION );

				// load our styles
				wp_enqueue_style( 'wishlistmember3-combined-styles', $this->pluginURL3 . '/ui/css/main.css', array(), $this->Version );

				/* end: styles */

				/* start: scripts */

				// IE 9 stuff
				if ( function_exists( 'wp_script_add_data' ) ) {
					wp_enqueue_script( 'html5shiv', $this->pluginURL3 . '/assets/js/html5shiv.min.js', array(), WLM_PLUGIN_VERSION );
					wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );

					wp_enqueue_script( 'respond', $this->pluginURL3 . '/assets/js/respond.min.js', array(), WLM_PLUGIN_VERSION );
					wp_script_add_data( 'respond', 'conditional', 'lt IE 9' );
				}

				// head scripts
				wp_enqueue_script( 'wishlistmember3-combined-scripts', $this->pluginURL3 . '/ui/js/head.js', array(), $this->Version );

				// start: re-register wp scripts as aliases of `wishlistmember3-combined-scripts`
				$re_register_scripts = array( 'jquery', 'jquery-ui', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-accordion', 'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-menu', 'jquery-ui-mouse', 'jquery-ui-position', 'jquery-ui-progressbar', 'jquery-ui-selectable', 'jquery-ui-resizable', 'jquery-ui-selectmenu', 'jquery-ui-sortable', 'jquery-ui-slider', 'jquery-ui-spinner', 'jquery-ui-tooltip', 'jquery-ui-tabs', 'underscore', 'backbone', 'moment' );

				foreach ( $re_register_scripts as $script ) {
					wp_deregister_script( $script );
					wp_register_script( $script, false, array( 'wishlistmember3-combined-scripts' ), $this->Version );
				}
				// end: re-register wp scripts as aliases of `wishlistmember3-combined-scripts`

				wp_enqueue_script( 'wp-tinymce' );

				// footer scripts
				wp_enqueue_script( 'wishlistmember3-combined-scripts-footer', $this->pluginURL3 . '/ui/js/foot.js', array( 'wishlistmember3-combined-scripts' ), $this->Version, true );

				// per-screen js
				$screen_js = $this->get_screen_js();
				if ( $screen_js ) {
					wp_enqueue_script( md5( $screen_js ), $screen_js, array( 'wishlistmember3-combined-scripts-footer' ), $this->Version, true );
				}

				// wp media
				$wl = wlm_get_data()['wl'];
				if ( preg_match( '#((setup|advanced_settings)/)#', (string) $wl ) ) {
					wp_enqueue_media();

					$wlm_scripts                = wp_scripts();
					$this->orig_wp_mediaelement = $wlm_scripts->registered['wp-mediaelement'];
				}

				/* end: scripts */

				/* start: data */
				$wlm3vars = array(
					'sku'                       => WLM_SKU,
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'request_error'             => 'Something went wrong while processing your request. Please refresh your browser and try again.',
					'request_failed'            => 'An error occured while processing your request. Please try again.',
					'blogurl'                   => get_bloginfo( 'url' ),
					'pluginurl'                 => $this->pluginURL3,
					'plugin_version'            => $this->Version,
					'copy_command'              => $this->copy_command,
					'js_date_format'            => $this->js_date_format,
					'js_time_format'            => $this->js_time_format,
					'js_datetime_format'        => $this->js_datetime_format,
					'js_time_offset'            => get_option( 'gmt_offset' ) * 3600,
					'js_timezone_string_pretty' => wishlistmember_instance()->get_wp_tzstring( true ),
					'js_timezone_string'        => wishlistmember_instance()->get_wp_tzstring( false ),
					'custom_fields_merge_codes' => $this->custom_fields_merge_codes,
					'tinymce_external_plugins'  => array(
						'advlist'       => $this->pluginURL3 . '/assets/js/tinymce/plugins/advlist/plugin.min.js',
						'anchor'        => $this->pluginURL3 . '/assets/js/tinymce/plugins/anchor/plugin.min.js',
						'code'          => $this->pluginURL3 . '/assets/js/tinymce/plugins/code/plugin.min.js',
						'contextmenu'   => $this->pluginURL3 . '/assets/js/tinymce/plugins/contextmenu/plugin.min.js',
						'fullpage'      => $this->pluginURL3 . '/assets/js/tinymce/plugins/fullpage/plugin.min.js',
						'imagetools'    => $this->pluginURL3 . '/assets/js/tinymce/plugins/imagetools/plugin.min.js',
						'nonbreaking'   => $this->pluginURL3 . '/assets/js/tinymce/plugins/nonbreaking/plugin.min.js',
						'noneditable'   => $this->pluginURL3 . '/assets/js/tinymce/plugins/noneditable/plugin.min.js',
						'pagebreak'     => $this->pluginURL3 . '/assets/js/tinymce/plugins/pagebreak/plugin.min.js',
						'preview'       => $this->pluginURL3 . '/assets/js/tinymce/plugins/preview/plugin.min.js',
						'searchreplace' => $this->pluginURL3 . '/assets/js/tinymce/plugins/searchreplace/plugin.min.js',
						'table'         => $this->pluginURL3 . '/assets/js/tinymce/plugins/table/plugin.min.js',
						'textpattern'   => $this->pluginURL3 . '/assets/js/tinymce/plugins/textpattern/plugin.min.js',
					),
				);

				$wlm3vars['page_templates']       = $this->page_templates;
				$wlm3vars['level_email_defaults'] = $this->level_email_defaults;
				$wlm3vars['level_defaults']       = array_merge( $this->level_defaults, $this->level_email_defaults );

				$wlm3vars['ppp_email_defaults'] = $this->ppp_email_defaults;
				$wlm3vars['ppp_defaults']       = $this->ppp_defaults;

				$wlm3vars['custom_login_form_custom_css'] = "body.login {}\nbody.login div#login {}\nbody.login div#login h1 {}\nbody.login div#login h1 a {}\nbody.login div#login form#loginform {}\nbody.login div#login form#loginform p {}\nbody.login div#login form#loginform p label {}\nbody.login div#login form#loginform input {}\nbody.login div#login form#loginform input#user_login {}\nbody.login div#login form#loginform input#user_pass {}\nbody.login div#login form#loginform p.forgetmenot {}\nbody.login div#login form#loginform p.forgetmenot input#rememberme {}\nbody.login div#login form#loginform p.submit {}\nbody.login div#login form#loginform p.submit input#wp-submit {}\nbody.login div#login p#nav {}\nbody.login div#login p#nav a {}\nbody.login div#login p#backtoblog {}\nbody.login div#login p#backtoblog a {}";

				wp_localize_script( 'wishlistmember3-combined-scripts', 'WLM3VARS', $wlm3vars );
				include_once $this->plugindir3 . '/helpers/jslang.php';

				/* end: data */
			}
		}

		/**
		 * Send per level email
		 *
		 * Called by wishlistmember_pre_email_template hook
		 *
		 * @param  string $email_template Email template to send
		 * @param  int    $user_id        User ID of recipient
		 * @return string                 Filtered email template or false to abort email sending
		 */
		public function pre_email_template( $email_template, $user_id ) {
			static $_per_level_templates = array(
				'expiring_level'                    => array( 'expiring_user_subject', 'expiring_user_message', 'expire_option', 'expiring_notification_user' ),
				'expiring_level_admin'              => array( 'expiring_admin_subject', 'expiring_admin_message', 'expire_option', 'expiring_notification_admin' ),

				'require_admin_approval'            => array( 'require_admin_approval_free_user1_subject', 'require_admin_approval_free_user1_message', 'requireadminapproval', 'require_admin_approval_free_notification_user1' ),
				'registration_approved'             => array( 'require_admin_approval_free_user2_subject', 'require_admin_approval_free_user2_message', 'requireadminapproval', 'require_admin_approval_free_notification_user2' ),
				'require_admin_approval_admin'      => array( 'require_admin_approval_free_admin_subject', 'require_admin_approval_free_admin_message', 'requireadminapproval', 'require_admin_approval_free_notification_admin' ),

				'require_admin_approval_paid'       => array( 'require_admin_approval_paid_user1_subject', 'require_admin_approval_paid_user1_message', 'requireadminapproval_integrations', 'require_admin_approval_paid_notification_user1' ),
				'registration_approved_paid'        => array( 'require_admin_approval_paid_user2_subject', 'require_admin_approval_paid_user2_message', 'requireadminapproval_integrations', 'require_admin_approval_paid_notification_user2' ),
				'require_admin_approval_paid_admin' => array( 'require_admin_approval_paid_admin_subject', 'require_admin_approval_paid_admin_message', 'requireadminapproval_integrations', 'require_admin_approval_paid_notification_admin' ),

				'email_confirmation'                => array( 'require_email_confirmation_subject', 'require_email_confirmation_message', 'requireemailconfirmation' ),
				'email_confirmation_reminder'       => array( 'require_email_confirmation_reminder_subject', 'require_email_confirmation_reminder_message', 'requireemailconfirmation', 'require_email_confirmation_reminder' ),

				'email_confirmed'                   => array( 'email_confirmed_subject', 'email_confirmed_message', 'email_confirmed' ),

				'registration'                      => array( 'newuser_user_subject', 'newuser_user_message', 'newuser_notification_user' ),
				'admin_new_member_notice'           => array( 'newuser_admin_subject', 'newuser_admin_message', 'newuser_notification_admin' ),

				'incomplete_registration'           => array( 'incomplete_subject', 'incomplete_message', 'incomplete_notification' ),

				'membership_cancelled'              => array( 'cancel_subject', 'cancel_message', 'cancel_notification' ),
				'membership_uncancelled'            => array( 'uncancel_subject', 'uncancel_message', 'uncancel_notification' ),

			);

			$per_level_templates = apply_filters( 'wishlistmember_per_level_templates', $_per_level_templates );

			// return original if no per level email template is found
			if ( empty( $per_level_templates[ $email_template ] ) ) {
				return $email_template;
			}

			// get the level - wlm_post_data()['wpm_id'] or the latest membership level
			$level = wlm_arrval( $this, 'email_template_level' ) ? wlm_arrval( 'lastresult' ) : ( wlm_post_data()['wpm_id'] ? wlm_post_data()['wpm_id'] : $this->get_latest_membership_level( $user_id ) );

			$payperpost = preg_match( '/^payperpost-\d+$/', $level );

			// abort if no level is found
			if ( empty( $level ) ) {
				return false;
			}

			// if(!$payperpost) {
			// $this->get_latest_membership_level($user_id) has a flaw, if level has Add to feature, it returns the Add to level
			// this is the fix
			// $level_parent = $this->level_parent($level,$user_id); //check level if it has a parent and use it
			// $level = $level_parent ? $level_parent : $level;
			// }

			$settings = $per_level_templates[ $email_template ];

			if ( $payperpost ) {
				$level_data = array_merge( $this->ppp_email_defaults, (array) $this->get_option( $level ) );
			} else {
				$wpm_levels = $this->get_option( 'wpm_levels' );
				$level_data = $wpm_levels[ $level ];
			}

			// abort if any of the settings is off/empty
			foreach ( $settings as $setting ) {
				$d = apply_filters( 'wishlistmember_per_level_template_setting', $level_data[ $setting ], $setting, $user_id, $level );
				$d = apply_filters( 'wishlistmember_per_level_template_setting_' . $setting . '_' . $level, $d, $user_id );
				if ( empty( $d ) ) {
					return false;
				}
			}

			// return the template
			return array( $level_data[ $settings[0] ], $level_data[ $settings[1] ] );
		}

		/**
		 * Set from name & email address of the email that's being sent
		 * based on per level settings
		 *
		 * Called by wishlistmember_template_mail_from_email
		 * and wishlistmember_template_mail_from_name hooks
		 *
		 * @param  string $from           Email/Name
		 * @param  string $email_template Email template being sent
		 * @param  int    $user_id        User ID of recipient
		 * @return string                 Filtered Email/Name
		 */
		public function template_mail_from( $from, $email_template, $user_id ) {
			$per_level_senders = array(
				'expiring_level'              => array( 'expiring_user_sender_name', 'expiring_user_sender_email', 'expire_option', 'expiring_notification_user', 'expiring_level_default_sender' ),

				'require_admin_approval'      => array( 'require_admin_approval_free_user1_sender_name', 'require_admin_approval_free_user1_sender_email', 'requireadminapproval', 'require_admin_approval_free_notification_user1', 'require_admin_approval_default_sender' ),
				'registration_approved'       => array( 'require_admin_approval_free_user2_sender_name', 'require_admin_approval_free_user2_sender_email', 'requireadminapproval', 'require_admin_approval_free_notification_user2', 'registration_approved_default_sender' ),

				'require_admin_approval_paid' => array( 'require_admin_approval_paid_user1_sender_name', 'require_admin_approval_paid_user1_sender_email', 'requireadminapproval_integrations', 'require_admin_approval_paid_notification_user1', 'require_admin_approval_paid_default_sender' ),
				'registration_approved_paid'  => array( 'require_admin_approval_paid_user2_sender_name', 'require_admin_approval_paid_user2_sender_email', 'requireadminapproval_integrations', 'require_admin_approval_paid_notification_user2', 'registration_approved_paid_default_sender' ),

				'email_confirmation'          => array( 'require_email_confirmation_sender_name', 'require_email_confirmation_sender_email', 'requireemailconfirmation', 'email_confirmation_default_sender' ),

				'registration'                => array( 'newuser_user_sender_name', 'newuser_user_sender_email', 'newuser_notification_user', 'registration_default_sender' ),

				'incomplete_registration'     => array( 'incomplete_sender_name', 'incomplete_sender_email', 'incomplete_notification', 'incomplete_registration_default_sender' ),

				'membership_cancelled'        => array( 'cancel_sender_name', 'cancel_sender_email', 'cancel_notification', 'membership_cancelled_default_sender' ),
				'membership_uncancelled'      => array( 'uncancel_sender_name', 'uncancel_sender_email', 'uncancel_notification', 'membership_uncancelled_default_sender' ),
			);

			// abort if no template or user id specified
			if ( ! $email_template || ! $user_id ) {
				return $from;
			}

			// abort if no sender is found
			if ( empty( $per_level_senders[ $email_template ] ) ) {
				return $from;
			}

			// Use Global Sender Info
			$global_sender = wlm_arrval( $per_level_senders[ $email_template ], 3 );
			unset( $per_level_senders[ $email_template ][3] );

			$current_filter = current_filter();
			if ( 'wishlistmember_template_mail_from_name' === $current_filter ) {
				unset( $per_level_senders[ $email_template ][1] );
			} elseif ( 'wishlistmember_template_mail_from_email' === $current_filter ) {
				unset( $per_level_senders[ $email_template ][0] );
			} else {
				return $from;
			}

			$settings = array_values( $per_level_senders[ $email_template ] );

			// get the level - wlm_post_data()['wpm_id'] or the latest membership level
			$level = wlm_post_data()['wpm_id'] ? wlm_post_data()['wpm_id'] : ( $this->email_template_level ? $this->email_template_level : $this->get_latest_membership_level( $user_id ) );

			// abort if no level is found
			if ( empty( $level ) ) {
				return $from;
			}

			// $this->get_latest_membership_level($user_id) has a flaw, if level has Add to feature, it returns the Add to level
			// this is the fix
			// $level_parent = $this->level_parent($level,$user_id); //check level if it has a parent and use it
			// $level = $level_parent ? $level_parent : $level;

			$wpm_levels = $this->get_option( 'wpm_levels' );
			// abort if any of the setting is off/empty
			$level = $wpm_levels[ $level ];

			// check if we're using the global sender info
			if ( $global_sender && (bool) $level[ $global_sender ] ) {
				return $from;
			}

			foreach ( $settings as $setting ) {
				if ( empty( $level[ $setting ] ) ) {
					return $from;
				}
			}

			// return the sender
			return $level[ $settings[0] ];
		}

		/**
		 * Filter the reciipient of the email template
		 * Filter: wishlistmember_email_template_recipient
		 *
		 * @param  string  $recipient Recipient Email Address
		 * @param  string  $email_template
		 * @param  integer $user_id
		 * @return string  Filtered email address
		 */
		public function email_template_recipient( $recipient, $email_template, $user_id ) {

			// get the membership level
			$level = wlm_arrval( $this, 'email_template_level' ) ? wlm_arrval( 'lastresult' ) : ( wlm_post_data()['wpm_id'] ? wlm_post_data()['wpm_id'] : $this->get_latest_membership_level( $user_id ) );
			$level = new \WishListMember\Level( $level );

			switch ( $email_template ) {
				case 'admin_new_member_notice': // admin new member notification
					$new_recipient = wlm_arrval( $level, 'newuser_admin_recipient' ) ? wlm_arrval( 'lastresult' ) : wlm_or( $this->get_option( 'newmembernotice_email_recipient' ), $recipient );
					break;
				default:
					$new_recipient = $recipient;
			}

			// revert to $recipient if $new_recipient is not a valid email
			if ( ! is_email( $new_recipient ) ) {
				$new_recipient = $recipient;
			}

			return $new_recipient;
		}

		public function load_integrations() {
			// init active shopping carts
			$active_wlm_shopping_carts = (array) $this->get_option( 'ActiveShoppingCarts' );
			foreach ( $active_wlm_shopping_carts as $sc ) {
				$sc = $this->plugindir3 . '/integrations/payments/' . str_replace( array( 'integration.shoppingcart.', '.php' ), '', $sc ) . '/init.php';
				if ( file_exists( $sc ) ) {
					include_once $sc;
				}
			}

			// init active autoresponders
			$active_wlm_autoresponders = (array) $this->get_option( 'active_email_integrations' );
			foreach ( $active_wlm_autoresponders as $ar ) {
				$ar = $this->plugindir3 . '/integrations/emails/' . strtolower( $ar ) . '/init.php';
				if ( file_exists( $ar ) ) {
					include_once $ar;
				}
			}

			// init active other integrations
			$active_wlm_other = (array) $this->get_option( 'active_other_integrations' );
			foreach ( $active_wlm_other as $other ) {
				$other = $this->plugindir3 . '/integrations/others/' . $other . '/init.php';
				if ( file_exists( $other ) ) {
					include_once $other;
				}
			}
		}

		// called by wp-cron
		// sends incomplete registration notification emails
		public function incomplete_registration_notification() {
			$wpm_levels = $this->get_option( 'wpm_levels' );
			if ( 1 != $this->get_option( 'incomplete_notification' ) ) {
				return;
			}

			$incomplete_users = $this->get_incomplete_registrations(); // get users with incomplete registration
			foreach ( $incomplete_users as $id => $user ) {

				if ( empty( $user['wlm_incregnotification'] ) || empty( $user['wlm_incregnotification']['level'] ) ) {
					$user['wlm_incregnotification']['level'] = array_shift( $this->get_membership_levels( $id ) );
				}
				$incregnotification = $user['wlm_incregnotification'];

				if ( empty( $wpm_levels[ $incregnotification['level'] ] ) ) {
					continue;
				}
				$level = $wpm_levels[ $incregnotification['level'] ];

				if ( empty( $level['incomplete_notification'] ) ) {
					continue;
				}

				$first_notification     = $level['incomplete_start'] / $level['incomplete_start_type'];
				$add_notification_count = $level['incomplete_howmany'] + 1;
				$add_notification_freq  = (int) $level['incomplete_send_every'];
				$send                   = false;
				$count                  = isset( $incregnotification['count'] ) ? $incregnotification['count'] : 0;
				$lastsend               = isset( $incregnotification['lastsend'] ) ? $incregnotification['lastsend'] : time();
				$t_diff                 = ( time() - $lastsend ) / 3600;
				$t_diff                 = $t_diff < 0 ? 0 : round( $t_diff, 3 );
				if ( $count <= 0 && $t_diff >= $first_notification ) {
					$send = true;
				} elseif ( $count < $add_notification_count && $t_diff >= $add_notification_freq ) {
					$send = true;
				}

				if ( $send ) {
					$incregurl = $this->get_continue_registration_url( $user['email'] ); // get user's registration url

					$macros = array(
						'[incregurl]'   => $incregurl,
						'[memberlevel]' => $level['name'],
					);

					$this->send_email_template( 'incomplete_registration', $id, $macros );
					$incregnotification['count']    = $count + 1;
					$incregnotification['lastsend'] = time();
					update_user_meta( $id, 'wlm_incregnotification', $incregnotification );
				}
			}
		}

		/**
		 * Returns the text before or after the registration form for the level
		 *
		 * @used-by \WishListMember3_Hooks::regform_before
		 * @used-by \WishListMember3_Hooks::regform_after
		 * @param  integer $level     level ID
		 * @param  string  $position  before|after
		 * @param  string  $text      default text
		 * @return string
		 */
		private function regform_before_after( $level, $position, $text ) {
			$wpm_levels = $this->get_option( 'wpm_levels' );

			if ( empty( $wpm_levels[ $level ] ) || ! is_array( $wpm_levels[ $level ] ) ) {
				return $text;
			}

			$index = 'regform_' . $position;
			if ( empty( $wpm_levels[ $level ]['enable_header_footer'] ) || empty( $wpm_levels[ $level ][ $index ] ) ) {
				return '';
			}

			return $wpm_levels[ $level ][ $index ];

		}

		/**
		 * Filter for wishlistmember_before_registration_form
		 *
		 * @uses \WishListMember3_Hooks::regform_before_after
		 * @param  string  $text  text to filter
		 * @param  integer $level level ID
		 * @return string
		 */
		public function regform_before( $text, $level ) {
			return $this->regform_before_after( $level, 'before', $text );
		}
		/**
		 * Filter for wishlistmember_after_registration_form
		 *
		 * @uses \WishListMember3_Hooks::regform_before_after
		 * @param  string  $text  text to filter
		 * @param  integer $level level ID
		 * @return string
		 */
		public function regform_after( $text, $level ) {
			return $this->regform_before_after( $level, 'after', $text );
		}

		// ajax handler for exporting settings
		public function export_settings() {
			global $wpdb;
			$export = array(
				'levels'  => array(),
				'globals' => array(),
			);
			if ( ! empty( wlm_post_data()['export_levels'] ) && is_array( wlm_post_data()['levels'] ) && count( wlm_post_data()['levels'] ) ) {
				$levels     = wlm_post_data()['levels'];
				$wpm_levels = $this->get_option( 'wpm_levels' );
				foreach ( $wpm_levels as $key => $level ) {
					if ( in_array( $key, $levels ) ) {
						$level['id']        = $key;
						$export['levels'][] = $level;
					}
				}
			}
			if ( ! empty( wlm_post_data()['global_settings'] ) ) {
				$export['globals'] = $wpdb->get_results( 'SELECT `option_name`,`option_value` FROM `' . esc_sql( $this->table_names->options ) . '` WHERE `option_name` <> "wpm_levels"', ARRAY_A );
			}
			$export = base64_encode( json_encode( $export ) );
			$length = strlen( $export );
			$parts  = array(
				'WLM3EXPORTFILE',
				$this->Version,
				get_bloginfo( 'url' ),
				strlen( $export ),
				md5( $export ),
				$export,
			);
			$file   = implode( '|', $parts );
			wp_send_json(
				array(
					'name'    => sprintf( '%s_%s.wlm3settings', sanitize_title( preg_replace( '#^.+?://#', '', get_bloginfo( 'url' ) ) ), wlm_date( 'Ymd_His' ) ),
					'content' => $file,
				)
			);
		}

		public function download_sysinfo() {
			$system_info = new \WishListMember\System_Info();
			wp_send_json(
				array(
					'name'    => sprintf( 'system_information_%s_%s.txt', sanitize_title( preg_replace( '#^.+?://#', '', get_bloginfo( 'url' ) ) ), wlm_date( 'YmdHis' ) ),
					'content' => $system_info->get_raw(),
				)
			);
		}

		public function frontend_styles() {

			if ( 'improved' == $this->get_option( 'FormVersion' ) ) {
				wp_enqueue_style( 'wlm3_frontend_css', $this->pluginURL3 . '/ui/css/frontend.css', array(), WLM_PLUGIN_VERSION );
			}

		}

		public function admin_title( $admin_title ) {
			if ( wlm_get_data()['page'] != $this->MenuID ) {
				return $admin_title;
			}

			$menu = $this->get_current_menu_item();
			if ( empty( $menu['title'] ) ) {
				return $admin_title;
			}

			return sprintf( '%s | %s', $this->Title, $menu['title'] );
		}

		/**
		 * Send registration email when added by admin
		 *
		 * @uses WishListMember3_Hooks::__send_registration_email to send the registration email
		 */
		public function send_registration_email_when_added_by_admin( $id, $new_levels, $removed_levels ) {
			static $acl;

			// don't send when api is running
			if ( ! empty( $this->api2_running ) ) {
				return;
			}

			// don't send when adding new user
			if ( 'admin_actions' == wlm_post_data()['action'] && 'add_user' == wlm_post_data()['WishListMemberAction'] ) {
				return;
			}

			// don't send when importing members via import feature.
			if ( 'ImportMembers' == wlm_post_data()['WishListMemberAction'] ) {
				return;
			}

			// don't send if user can't manage options
			if ( is_null( $acl ) ) {
				$acl = new WishListAcl();
			}
			if ( ! $acl->current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( is_null( $wpm_levels ) ) {
				$wpm_levels = $this->get_option( 'wpm_levels' );
			}

			$this->__send_registration_email( $id, $new_levels );
		}

		/**
		 * Send registration email on sequential upgrade
		 *
		 * @uses WishListMember3_Hooks::__send_registration_email to send the registration email
		 */
		public function send_registration_email_on_sequential_upgrade( $id, $new_levels, $seqlevels ) {
			$this->__send_registration_email( $id, $new_levels );
		}

		/**
		 * Send registration email
		 *
		 * @used-by WishListMember3_Hooks::send_registration_email_when_added_by_admin
		 * @used-by WishListMember3_Hooks::send_registration_email_on_sequential_upgrade
		 */
		private function __send_registration_email( $id, $levels ) {
			static $wpm_levels;

			if ( empty( $levels ) ) {
				return;
			}

			if ( is_null( $wpm_levels ) ) {
				$wpm_levels = $this->get_option( 'wpm_levels' );
			}

			foreach ( $levels as &$level ) {
				$level = $wpm_levels[ $level ]['name'];
			}
			unset( $level );
			$macros = array(
				'[password]'    => '********',
				'[memberlevel]' => implode( ', ', $levels ),
			);
			$this->send_email_template( 'registration', $id, $macros );
		}

		public function recheck_license() {
			if ( ! wlm_post_data( true ) || ! current_user_can( 'manage_options' ) || empty( wlm_post_data()['_wlm_recheck_license_'] ) ) {
				return;
			}

			$license = $this->get_option( 'LicenseKey' );
			if ( ! wlm_trim( $license ) ) {
				exit;
			}

			list( $key, $hash ) = explode( '/', wlm_post_data()['_wlm_recheck_license_'] );
			if ( md5( $key . $license ) != $hash ) {
				exit;
			}

			$this->delete_option( 'LicenseLastCheck' );
			$this->WPWLKeyProcess();
			exit;
		}

		public function save_postpage_settings() {
			$this->save_post_page();
			wp_send_json( array( 'success' => true ) );
		}

		public function dismiss_news() {
			$dismiss = wlm_post_data()['dismiss'];
			if ( ! in_array( $dismiss, array( 'dashboard_warningfeed_dismissed', 'dashboard_feed_dismissed' ) ) ) {
				return;
			}
			$this->save_option( $dismiss, time() );
		}

		public function post_page_ppp_user_search() {
			$search     = wlm_post_data()['search'];
			$search_by  = wlm_post_data()['search_by'];
			$page       = wlm_post_data()['page'];
			$number     = wlm_post_data()['number'];
			$ppp_access = wlm_post_data()['ppp_access'];
			$ppp_id     = wlm_post_data()['ppp_id'];

			$incomplete = new \WP_User_Query(
				array(
					'fields' => array( 'ID' ),
					'search' => 'temp_*',
				)
			);
			$incomplete = $incomplete->get_results();
			foreach ( $incomplete as &$i ) {
				$i = $i->ID;
			}
			unset( $i );

			$number = (int) $number;
			if ( empty( $number ) ) {
				$number = 10;
			}
			$page--;
			if ( $page < 0 ) {
				$page = 0;
			}
			$offset = $page * $number;

			$args = array(
				'number'  => $number,
				'offset'  => $offset,
				'exclude' => $incomplete,
				'fields'  => array( 'ID', 'display_name', 'user_login', 'user_email' ),
			);

			$contentLevels = $this->get_content_levels( 'posts', $ppp_id );

			switch ( $search_by ) {
				case 'by_level':
					if ( ! is_array( $search ) || empty( $search ) ) {
						$search = null;
					}
					$args['include'] = $this->active_member_ids( $search );
					if ( empty( $args['include'] ) ) {
						wp_send_json(
							array(
								'users'         => 0,
								'total_users'   => 0,
								'contentlevels' => $contentLevels,
							)
						);
					}
					break;
				default: // by_user
					$search = wlm_trim( $search );
					$search = esc_attr( wlm_trim( $search ) ) . '*';
					if ( strlen( $search ) > 1 ) {
						$search = '*' . $search;
					}
					$args['search'] = $search;
			}

			if ( in_array( $ppp_access, array( 'yes', 'no' ) ) ) {
				$this->__temp_ppp_id     = $ppp_id;
				$this->__temp_ppp_access = $ppp_access;
				add_action(
					'pre_user_query',
					function( $q ) {
						global $WishListMemberInstance, $wpdb;
						$not             = 'no' === $this->__temp_ppp_access ? 'NOT' : '';
						$q->query_where .= " AND concat('U-', `{$wpdb->users}`.`ID`) $not IN (SELECT `level_id` FROM `{$WishListMemberInstance->table_names->contentlevels}` WHERE `type` NOT LIKE '~%' AND `content_id` = {$this->__temp_ppp_id}) ";
						return $q;
					}
				);
			}

			$query = new \WP_User_Query( $args );

			wp_send_json(
				array(
					'users'         => $query->get_results(),
					'total_users'   => $query->total_users,
					'contentlevels' => $contentLevels,
				)
			);
		}

		public function add_user_ppp() {
			$user_id    = wlm_post_data()['user_id'];
			$content_id = wlm_post_data()['content_id'];
			$this->add_post_users( get_post_type( $content_id ), $content_id, $user_id );

			wp_send_json( array( 'success' => true ) );
		}
		public function remove_user_ppp() {
			$user_id    = wlm_post_data()['user_id'];
			$content_id = wlm_post_data()['content_id'];
			$this->remove_post_users( get_post_type( $content_id ), $content_id, $user_id );

			wp_send_json( array( 'success' => true ) );
		}

		public function save_postpage_system_page() {
			$post_id   = wlm_post_data()['post_id'];
			$page_type = wlm_post_data()['ptype'];

			$type     = sprintf( '%s_type_%d', $page_type, $post_id );
			$internal = sprintf( '%s_internal_%d', $page_type, $post_id );
			$message  = sprintf( '%s_message_%d', $page_type, $post_id );
			$page     = sprintf( '%s_%d', $page_type, $post_id );

			$this->save_option( $type, wlm_post_data()[ $type ] );
			$this->save_option( $internal, wlm_post_data()[ $internal ] );
			$this->save_option( $message, stripslashes( wlm_post_data()[ $message ] ) );
			$this->save_option( $page, wlm_post_data()[ $page ] );

			wp_send_json(
				array(
					'success' => true,
					'type'    => $type,
					'data'    => wlm_post_data( true ),
				)
			);
		}

		public function create_postpage_system_page() {
			wp_send_json( array( 'success' => true ) );
		}

		public function save_postpage_displayed_tab() {
			$target = (string) wlm_post_data()['target'];
			$this->save_option( 'wlm3_postpage_displayed_tab', $target );
			wp_send_json( array( 'success' => true ) );
		}

		/**
		 * Passes protection settings to all child pages
		 *
		 * @since 3.7
		 * Action: wp_ajax_wlm3_save_postpage_pass_protection
		 */
		public function save_postpage_pass_protection( $post_id = null ) {
			if ( ! empty( wlm_post_data()['post_id'] ) ) {
				$post_id    = wlm_post_data()['post_id'];
				$post_types = get_post_types();

				// get enabled post types
				$enabled_types = (array) wishlistmember_instance()->get_option( 'protected_custom_post_types' );

				// make sure that post type is enabled for the parent post type
				$ptype = get_post_type( $post_id );
				if ( ! in_array( $ptype, $enabled_types ) ) {
					$enabled_types[] = $ptype;
					wishlistmember_instance()->save_option( 'protected_custom_post_types', $enabled_types );
				}

				$children = array( $post_id );
				while ( $child = array_shift( $children ) ) {
					if ( $child != $post_id ) {
						wishlistmember_instance()->special_content_level( $child, 'Inherit', 'Y' );
						wishlistmember_instance()->inherit_protection( $child );
					}
					$x = get_children(
						array(
							'post_parent' => $child,
							'post_type'   => $post_types,
							'fields'      => 'ids',
						)
					);
					if ( $x ) {
						$children = array_merge( $children, $x );

						// make sure that protection is enabled for child post types
						$ptype = get_post_type( $x[0] );
						if ( ! in_array( $ptype, $enabled_types ) ) {
							$enabled_types[] = $ptype;
							wishlistmember_instance()->save_option( 'protected_custom_post_types', $enabled_types );
						}
					}
				}
			}
			wp_send_json( array( 'success' => true ) );
		}

		/**
		 * Remove scripts and styles from themes and other plugins to prevent conflicts with our scripts and styles
		 *
		 * @param  string $hook
		 */
		public function remove_theme_and_plugins_scripts_and_styles() {
			// todo improve logic to make it faster
			global $wp_styles, $wp_scripts;

			// only remove scripts and styles from themes and other plugins if on our page
			if ( wlm_get_data()['page'] != $this->MenuID ) {
				return;
			}

			// regex to match all themes and plugins except ours
			$regex = '#/wp-content/(themes/|plugins/(?!' . preg_quote( basename( $this->plugindir3 ) ) . ').+?/)#i';

			// regex of style handles to remove
			// dg_admin_styles = Divi Carousel Plugin
			// widgetkit-admin = All-in-One Addons for Elementor - WidgetKitwidgetkit-for-elementor
			$style_handles_regex = '#^(testify\-|optimizepress\-|dg_admin_styles|widgetkit\-admin|offloadingstyle|publitio)#';

			// selectively remove styles
			foreach ( $wp_styles->registered as $style ) {
				if ( preg_match( $regex, $style->src ) && preg_match( $style_handles_regex, $style->handle ) ) {
					wp_deregister_style( $style->handle );
				}
			}

			// remove all scripts from themes and other plugins
			foreach ( $wp_scripts->registered as $script ) {
				if ( preg_match( $regex, $script->src ) ) {
					wp_deregister_script( $script->handle );
				}
			}
		}

		public function frontend_init() {
			if ( is_admin() ) {
				return;
			}
			wp_enqueue_script( 'wlm3_js', $this->pluginURL3 . '/ui/js/frontend.js', array( 'jquery' ), $this->Version, true );
			wp_register_script( 'wlm3_form_js', $this->pluginURL3 . '/ui/js/frontend_form.js', array( 'jquery' ), $this->Version, true );
			wp_register_style( 'wlm3_form_css', $this->pluginURL3 . '/ui/css/frontend_form.css', array( 'dashicons' ), $this->Version );

			$data = array(
				'pluginurl' => $this->pluginURL3,
			);
			wp_localize_script( 'wlm3_form_js', 'WLM3VARS', $data );

			// translation for frontend form
			wp_localize_script(
				'wlm3_form_js',
				'wlm3frontl10n',
				array(
					'Strong' => esc_html__( 'Strong', 'wishlist-member' ),
					'Weak'   => esc_html__( 'Weak', 'wishlist-member' ),
					'Hide'   => esc_html__( 'Hide', 'wishlist-member' ),
				)
			);

			wp_register_script( 'wlm-clear-fancybox', $this->legacy_wlm_url . '/js/clear-fancybox.js', array( 'jquery' ), $this->Version, true );
			wp_register_script( 'wlm-jquery-fancybox', $this->legacy_wlm_url . '/js/jquery.fancybox.pack.js', array( 'wlm-clear-fancybox' ), $this->Version, true );
			wp_register_style( 'wlm-jquery-fancybox', $this->legacy_wlm_url . '/css/jquery.fancybox.css', array(), $this->Version );

			wp_register_script( 'wlm-popup-regform-card-validation', 'https://js.stripe.com/v2/', array( 'jquery' ), $this->Version, true );
			wp_register_script( 'wlm-popup-regform-card-validation2', 'https://js.stripe.com/v3/', array( 'jquery' ), $this->Version, true );

			wp_register_script( 'wlm-popup-regform', $this->legacy_wlm_url . '/js/wlm.popup-regform.js', array( 'wlm-popup-regform-card-validation' ), $this->Version, true );
			wp_register_script( 'wlm-popup-regform-stripev3', $this->legacy_wlm_url . '/js/wlm.popup-regform.js', array( 'wlm-popup-regform-card-validation2' ), $this->Version, true );
			wp_register_style( 'wlm-popup-regform-style', $this->legacy_wlm_url . '/css/wlm.popup-regform.css', array(), $this->Version );
		}

		public function get_level_stats() {
			$stats = array(
				'active'      => $this->active_member_ids( null, true, true ),
				'cancelled'   => $this->cancelled_member_ids( null, true, true ),
				'forapproval' => $this->for_approval_member_ids( null, true, true ),
				'unconfirmed' => $this->unconfirmed_member_ids( null, true, true ),
				'expired'     => $this->expired_members_id( true ),
			);
			wp_send_json( $stats );
		}

		/**
		 * Ensure that WP's original wp-mediaelement is loaded in our admin screens
		 */
		public function restore_wp_mediaelement() {
			global $wp_scripts;
			if ( ! is_admin() ) {
				return;
			}
			if ( 'WishListMember' != wlm_get_data()['page'] ) {
				return;
			}
			if ( empty( $this->orig_wp_mediaelement ) ) {
				return;
			}

			$wp_scripts->registered['wp-mediaelement'] = $this->orig_wp_mediaelement;
		}

		public function send_cancel_uncancel_notification( $uid, $level ) {
			static $wpm_levels;
			if ( is_null( $wpm_levels ) ) {
				$wpm_levels = $this->get_option( 'wpm_levels' );
			}

			// determine which template to send based on filter name
			$template = 'wishlistmember_cancel_user_levels' == current_filter() ? 'membership_cancelled' : 'membership_uncancelled';

			$more_macros = array( '[memberlevel]' => $wpm_levels[ $level[0] ]['name'] );

			list( $this->email_template_level ) = (array) $level;
			$this->send_email_template( $template, $uid, $more_macros );
		}

		public function sync_membership( $force_sync ) {
			global $wpdb;

			$userlevelsTable        = $this->table_names->userlevels;
			$userlevelsTableOptions = $this->table_names->userlevel_options;
			$userTableOptions       = $this->table_names->user_options;

			if ( ! get_transient( 'WLM_delete' ) || $force_sync ) {
				$deleted = 0;

				$deleted += $wpdb->query( 'DELETE ' . esc_sql( $userlevelsTable ) . ' FROM `' . esc_sql( $userlevelsTable ) . '` LEFT JOIN `' . $wpdb->users . '` ON `' . esc_sql( $userlevelsTable ) . '`.`user_id` = `' . $wpdb->users . '`.`ID` WHERE `' . $wpdb->users . '`.`ID` IS NULL' );

				$deleted += $wpdb->query( 'DELETE ' . esc_sql( $userTableOptions ) . ' FROM `' . esc_sql( $userTableOptions ) . '` LEFT JOIN `' . $wpdb->users . '` ON `' . esc_sql( $userTableOptions ) . '`.`user_id` = `' . $wpdb->users . '`.`ID` WHERE `' . $wpdb->users . '`.`ID` IS NULL' );

				$deleted += $wpdb->query( 'DELETE ' . esc_sql( $userlevelsTableOptions ) . ' FROM `' . esc_sql( $userlevelsTableOptions ) . '` LEFT JOIN `' . esc_sql( $userlevelsTable ) . '` ON `' . esc_sql( $userlevelsTableOptions ) . '`.`userlevel_id` = `' . esc_sql( $userlevelsTable ) . '`.`ID` WHERE `' . esc_sql( $userlevelsTable ) . '`.`ID` IS NULL' );

				set_transient( 'WLM_delete', 1, 60 * 60 );

				wlm_cache_flush();
				\WishListMember\Level::update_levels_count();
			}
		}

		public function login_screen_customization() {
			$login_styling_enable_custom_template = $this->get_option( 'login_styling_enable_custom_template' );
			if ( ! $login_styling_enable_custom_template ) {
				return;
			}

			$css_template = $this->get_option( 'login_styling_custom_template' );

			// template
			if ( $css_template ) {

				$vars     = array(
					'login_styling_custom_bgcolor',
					'login_styling_custom_bgblend',
					'login_styling_custom_bgimage',
					'login_styling_custom_bgposition',
					'login_styling_custom_bgrepeat',
					'login_styling_custom_bgsize',

					'login_styling_custom_loginbox_position',
					'login_styling_custom_loginbox_width',

					'login_styling_custom_loginbox_bgcolor',
					'login_styling_custom_loginbox_fgcolor',
					'login_styling_custom_loginbox_fontsize',

					'login_styling_custom_loginbox_btn_bgcolor',
					'login_styling_custom_loginbox_btn_fgcolor',
					'login_styling_custom_loginbox_btn_fontsize',
					'login_styling_custom_loginbox_btn_bordercolor',
					'login_styling_custom_loginbox_btn_bordersize',
					'login_styling_custom_loginbox_btn_roundness',

					'login_styling_custom_loginbox_fld_bgcolor',
					'login_styling_custom_loginbox_fld_fgcolor',
					'login_styling_custom_loginbox_fld_fontsize',
					'login_styling_custom_loginbox_fld_bordercolor',
					'login_styling_custom_loginbox_fld_bordersize',
					'login_styling_custom_loginbox_fld_roundness',

					'login_styling_custom_logo',
					'login_styling_custom_logo_height',
				);
				$css_vars = array();
				foreach ( $vars as $var ) {
					$value = wlm_trim( $this->get_option( $var ) );
					if ( empty( $value ) ) {
						continue;
					}

					if ( is_numeric( $value ) ) {
						$value .= 'px';
					}

					if ( preg_match( '#^http|https://#i', $value ) ) {
						$value = 'url(' . $value . ')';
					}

					$css_vars[] = sprintf( '%s:%s;', str_replace( 'login_styling_custom_', '--wlm3login_', $var ), $value );
				}

				wp_enqueue_style( 'wlm3-custom-login', $this->pluginURL3 . '/assets/templates/login-styles/' . $css_template . '/style.css', array(), $this->Version );
				if ( $css_vars ) {
					wp_add_inline_style( 'wlm3-custom-login', sprintf( ':root{%s}', implode( '', $css_vars ) ) );
				}

				// start: css vars pony fill
				wp_enqueue_script( 'wlm3-css-vars-ponyfill', $this->pluginURL3 . '/assets/js/css-vars-ponyfill.min.js', array(), $this->Version );
				$call_pony = 'cssVars({onlyLegacy:window.safari ? false : true});';
				if ( function_exists( 'wp_add_inline_script' ) ) {
					wp_add_inline_script( 'wlm3-css-vars-ponyfill', $call_pony );
				} else {
					printf( "\n<script type='text/javascript'>\nwindow.onload = function(){%s}\n</script>\n", esc_html( $call_pony ) );
				}
				// end: css vars pony fill

				// custom css
				// remove empty declarations that begin with body.login
				$custom_css = trim( preg_replace( '/^\s*body\.login.+?{\s*?}/m', '', $this->get_option( 'login_styling_custom_css' ) ) );
				if ( $custom_css ) {
					wp_add_inline_style( 'wlm3-custom-login', $custom_css );
				}
			}
		}

		// begin: front end media uploader hooks
		/**
		 * Limit access to non-admins to files that they have uploaded
		 * called by WordPress `ajax_query_attachments_args` filter
		 */
		public function filter_media_by_user( $wp_query ) {
			global $current_user, $pagenow;
			if ( in_array( $pagenow, array( 'upload.php', 'admin-ajax.php' ) ) ) {
				require_once ABSPATH . '/wp-includes/pluggable.php';
				if ( current_user_can( 'wlm_upload_files' ) ) {
					if ( 'attachment' == $wp_query->query_vars['post_type'] ) {
						$wp_query->set( 'author', $current_user->id );
					}
				}
			}
		}
		/**
		 * Give upload permissions to regular users
		 * called by WordPress `user_has_cap` filter
		 */
		public function frontend_give_upload_permissions( $allcaps ) {
			if ( ! is_admin() && is_user_logged_in() && empty( $allcaps['upload_files'] ) ) {
				$allcaps['upload_files']     = true;
				$allcaps['wlm_upload_files'] = true;
			}
			return $allcaps;
		}
		/**
		 * Restrict upload file types for regular users to to jpeg, png and gif
		 * called by WordPress `upload_mimes` filter
		 */
		public function restrict_upload_mimetypes( $mimes ) {
			if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
				$mimes = array(
					'jpg|jpeg|jpe' => 'image/jpeg',
					'gif'          => 'image/gif',
					'png'          => 'image/png',
				);
			}
			return $mimes;
		}
		// end: front end media uploader hooks

		/**
		 * Imports wlm_get_data()['wlm3img'] into the WP media library
		 * if it's not there yet and then redirect to it
		 *
		 * @return [type] [description]
		 */
		public function import_and_load_images() {
			// WishList's CDN URL
			$cdn = 'https://wishlist-member-images.s3.amazonaws.com';

			// get the image being requested
			$image = trim( basename( (string) wlm_get_data()['wlm3img'] ) );
			if ( ! preg_match( '/\.(jpeg|jpg|png|gif)$/i', $image ) ) {
				return; // images only
			}

			// create the CDN URL and transient name
			$cdn .= '/' . $image;

			// generate WishList Member upload_dir info
			$x       = wp_get_upload_dir();
			$basedir = $x['basedir'] . '/wishlist-member-assets';
			$baseurl = $x['baseurl'] . '/wishlist-member-assets';

			// create WishList Member upload dir
			if ( ! wp_mkdir_p( $basedir ) ) {
				return; // must have a valid upload directory
			}

			// create filename
			$file = $basedir . '/' . $image;

			// if the file does not exist the import it from CDN to WP Media Library
			if ( ! file_exists( $file ) ) {

				// get the URL being requested
				$get = wp_remote_get( $cdn, array( 'timeout' => 15 ) );

				// make sure content-type is image
				$x = wp_remote_retrieve_header( $get, 'content-type' );
				if ( ! preg_match( '/^image\//i', $x ) ) {
					return; // images only
				}

				// save file
				if ( ! file_put_contents( $file, wp_remote_retrieve_body( $get ) ) ) {
					return; // must create file
				}

				// insert to wp media
				$filetype   = wp_check_filetype( $file );
				$attachment = array(
					'guid'           => $baseurl . '/' . $image,
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				$attach_id  = wp_insert_attachment( $attachment, $file, 0 );

				// generate attachment metadata
				require_once ABSPATH . 'wp-admin/includes/image.php';
				wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $file ) );
			}

			// redirect to the requested image in WP media library
			wp_redirect( $baseurl . '/' . $image );
			exit;
		}

		public function wishlist_member_legacy_menu( $legacy, $key ) {
			static $wpm_levels;

			if ( empty( $wpm_levels ) ) {
				$wpm_levels = $this->get_option( 'wpm_levels' );
			}
			if ( $legacy ) {
				switch ( $key ) {
					case 'members/sequential':
						foreach ( $wpm_levels as $level ) {
							if ( ! empty( $level['upgradeMethod'] ) ) {
								return false;
							}
						}
						break;
				}
			}
			return $legacy;
		}

		public function wishlist_member_upgrader_pre_download( $reply, $package, $object ) {
			if ( 'WLMNOLICENSEKEY' === $package ) {
				$object->maintenance_mode( false );
				die( esc_html__( 'Manual update required.', 'wishlist-member' ) );
			}
			return $reply;
		}

		public function payperpost_as_after_login_redirect( $url, $level, $user ) {
			global $wpdb;
			if ( $user->ID ) {
				$ppp = $wpdb->get_col(
					$wpdb->prepare(
						'SELECT `b`.`content_id` FROM `' . esc_sql( $this->table_names->contentlevel_options ) . '` `a` JOIN `' . esc_sql( $this->table_names->contentlevels ) . '` `b` ON `b`.`ID`=`a`.`contentlevel_id` JOIN `' . esc_sql( $this->table_names->contentlevels ) . '` `c` ON `b`.`content_id`=`c`.`content_id` JOIN `' . esc_sql( $this->table_names->contentlevels ) . '` `d` ON `c`.`content_id`=`d`.`content_id` WHERE `a`.`option_name`="registration_date" AND `b`.`level_id`=%s AND `c`.`level_id`="PayPerPost_AfterLogin" AND `d`.`level_id`="PayPerPost" ORDER BY `a`.`option_value` DESC LIMIT 1',
						'U-' . $user->ID
					)
				);
				if ( $ppp ) {
					return get_permalink( $ppp[0] );
				}
			}
			return $url;
		}

		/**
		 * Filter: wishlistmember_autocreate_account
		 *
		 * Determines if an account for an integration-triggered registration can be auto-created.
		 * This feature is enabled on a per-level basis
		 *
		 * @param bool           $auto_created
		 * @param string|integer $level
		 * @param string         $email
		 * @param string         $orig_email
		 * @return bool Converted value of $auto_created
		 */
		public function autocreate_account_for_integration( $auto_created, $level, $email, $orig_email ) {
			// check if email is a temp account
			if ( 'temp_' . md5( $orig_email ) != $email ) {
				return;
			}

			// check if level exists
			$registration_level = new \WishListMember\Level( $level );
			if ( ! $registration_level->ID || ! $registration_level->autocreate_account_enable ) {
				return;
			}

			// grab the username format from level settings
			$username_format = wlm_or( wlm_trim( $registration_level->autocreate_account_username ), $this->level_defaults['autocreate_account_username'] );

			// do we have a delay for account auto-creation?
			if ( $registration_level->autocreate_account_enable_delay ) {
				// yes, schedule a single wp cron event

				// compute timestamp based on delay settings
				$timestamp = time() + $registration_level->autocreate_account_delay * $registration_level->autocreate_account_delay_type * 60;

				// schedule cron
				wp_schedule_single_event( $timestamp, 'wishlistmember_finish_incomplete_registration', array( $email, $orig_email, $username_format, true ) );
			} else {
				// no, trigger auto-creation now
				do_action( 'wishlistmember_finish_incomplete_registration', $email, $orig_email, $username_format );

				$auto_created = true;
			}

			return $auto_created;
		}

		/**
		 * Action: wishlistmember_finish_incomplete_registration
		 *
		 * Auto-create accounts for integrations.
		 * This feature is enabled on a per-level basis
		 *
		 * @param string $email
		 * @param string $orig_email
		 * @param string $username_format
		 */
		public function finish_incomplete_registration( $email, $orig_email, $username_format, $cron = false ) {
			global $wpdb, $WishListMemberInstance;

			if ( 'temp_' . md5( $orig_email ) != $email ) {
				return;
			}

			// get temp user. abort if not found
			$user = get_user_by( 'login', $email );
			if ( ! $user ) {
				return;
			}

			// get temp user's levels and txnids
			$levels = $WishListMemberInstance->get_membership_levels_txn_ids( $user->ID );
			foreach ( array_keys( $levels ) as $lid ) {
				$levels[ $lid ] = array( $lid, $levels[ $lid ] );
			}
			unset( $levels[ 'U-' . $user->ID ] );

			// generate API data
			$api_data          = array(
				'Levels'           => $levels,
				'SendMailPerLevel' => 1,
			);
			$mergewith_user_id = 0;
			$auto_created      = false;
			$new_user_id       = null;
			$userdata          = $api_data + wlm_post_data( true );

			// get original user from $orig_email if existing
			$orig_user = get_user_by( 'email', $orig_email );
			if ( $orig_user ) {
				/**
				 * Existing User
				 */

				// update existing user's levels
				wlm_post_data()['email'] = $orig_email;

				$result = wlmapi_update_member( $orig_user->ID, $api_data );
				// delete temp user on success
				if ( $result['success'] && $result['member'][0]['ID'] ) {

					// lets prepare data to be pass on our action below
					$udata             = array(
						'user_email' => $user->user_email,
						'user_login' => $user->user_login,
						'first_name' => $user->first_name,
						'last_name'  => $user->last_name,
						'mergewith'  => $user->ID,
					);
					$userdata          = array_merge( $userdata, $udata );
					$mergewith_user_id = $user->ID;
					$new_user_id       = $orig_user->ID;

					$auto_created = true;
					wp_delete_user( $user->ID, $orig_user->ID );
				}
				update_option( 'test_data' . $orig_email, 'existing' );
			} else {
				update_option( 'test_data' . $orig_email, 'new' );
				/**
				 * New User
				 */
				$password = wlm_post_data()['password1'];
				/* <generate strong password> */
				if ( ! $password ) {
					$min_passlength = (int) $this->get_option( 'min_passlength' );
					if ( ! $min_passlength || $min_passlength < 14 ) {
						$min_passlength = 14;
					}
					do {
						$password = wlm_generate_password( $min_passlength, true );
					} while ( ! wlm_check_password_strength( $password ) );
				}
				/* </generate strong password> */

				/* <generate username> */
				$userdata = array(
					'email'      => $orig_email,
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
				);
				$username = wlm_generate_username( $userdata, $username_format );
				$username = false !== $username ? $username : $orig_email;
				/* </generate username> */

				// add member with levels
				$userdata = array(
					'user_email' => $orig_email,
					'user_login' => $username,
					'user_pass'  => $password,
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'password1'  => $password, // used by other plugin that uses the hook
				);

				wlm_post_data()['email'] = $orig_email;

				$result = wlmapi_add_member( $userdata + $api_data );

				// update wpm_useraddress and delete temp user on success
				if ( $result['success'] && $result['member'][0]['ID'] ) {
					$new_user_id = $result['member'][0]['ID'];
					$WishListMemberInstance->Update_UserMeta( $new_user_id, 'wpm_useraddress', $WishListMemberInstance->Get_UserMeta( $user->ID, 'wpm_useraddress' ) );
					wp_delete_user( $user->ID );
					$auto_created = true;
					wlm_setcookie( 'wlm_autogen_pass', $password, time() + 600, '/' );
					// automatically login the user
					$WishListMemberInstance->wpm_auto_login( $new_user_id );
				}
			}

			if ( $auto_created ) {
				// call this action also since its being used by most of our integration
				$userdata['email']      = $userdata['user_email'];// pass the correct email
				$userdata['orig_email'] = $orig_email;
				do_action( 'wishlistmember_user_registered', $new_user_id, $userdata, $mergewith_user_id );

				add_action( 'wishlistmember_after_registration', array( $this, 'autocreate_account_for_integration_redirect' ) );
			}
		}

		/**
		 * Action: wishlistmember_after_registration
		 *
		 * Handles after registration redirect for auto-created accounts
		 */
		public function autocreate_account_for_integration_redirect() {
			if ( 'CREATE' != wlm_post_data()['cmd'] ) {
				$url = $this->get_after_reg_redirect( wlm_post_data()['wpm_id'] );
				wp_redirect( $url ) && exit;
			}
		}

		public function add_settings_link( $links ) {
			$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=' . $this->MenuID ), __( 'Settings', 'wishlist-member' ) );
			return $links;
		}

		/**
		 * Action: user_register
		 *
		 * Schedule a WP Cron to Automatically add non-WishList Member
		 * user registrations to membership levels that have it enabled
		 *
		 * @param int user id
		 */
		public function autoadd_other_registrations( $uid ) {
			global $wishlist_member_inserting_user;
			if ( ! empty( $wishlist_member_inserting_user ) ) {
				return;
			}

			// get levels.
			$wpm_levels = $this->get_option( 'wpm_levels' );

			// get level ids that have audoadd enabled.
			$levels = array();
			foreach ( $wpm_levels as $lid => $level ) {
				if ( ! empty( $level['autoadd_other_registrations'] ) ) {
					$levels[] = $lid;
				}
			}

			// add levels to user.
			if ( $levels ) {
				wlmapi_update_member( $uid, array( 'Levels' => $levels ) );
			}
		}


		/**
		 * Filter: wishlistmember_per_level_template_setting
		 *
		 * Filters new user email notifications (admin and user) to send only for new members
		 *
		 * Checks if the value of `newuser_notification_admin` or `newuser_notification_user`
		 * is 2 and return 0 or 1 accordingly
		 *
		 * @param int        $value
		 * @param string     $setting
		 * @param int        $user_id
		 * @param string|int $level_id
		 * @return int
		 */
		public function send_newuser_email_notification_to_new_members_only( $value, $setting, $user_id, $level_id ) {
			// we only filter the following
			if ( ! in_array( $setting, array( 'newuser_notification_admin', 'newuser_notification_user' ) ) ) {
				return $value;
			}

			// we only filter if $value is set 2 ( send to new members only )
			if ( 2 !== (int) $value ) {
				return $value;
			}

			// get user's levels
			$levels       = $this->get_membership_levels( $user_id, null, null, null, true );
			$level_parent = $this->level_parent( $level_id, $user_id ); // check level if it has a parent and use it

			if ( $level_parent || $levels[0] == $level_id ) {
				return $levels[0] == $level_id ? 1 : 0;
			} else {
				// return 1 if the earliest level matches $level_id, 0 otherwise
				return array_pop( $levels ) == $level_id ? 1 : 0;
			}
		}

		/**
		 * Action: wp_ajax_wlm3_generate_new_user_rss
		 *
		 * Generate new user RSS Feed URL
		 *
		 * @return json
		 */
		public function generate_new_user_rss() {
			$uid = wlm_post_data()['user_id'];
			$this->Update_UserMeta( $uid, 'wishlistmember_feedkey', uniqid( '', true ) );
			$user = $this->get_user_data( $uid );
			wp_send_json(
				array(
					'success'  => true,
					'feed_url' => $user->wlm_feed_url,
				)
			);
		}

		/**
		 * Action: plugins_loaded
		 *
		 * @return type
		 */
		public function version_change_check() {
			if ( ! empty( $this->version_has_changed ) ) {
				do_action(
					'wishlistmember_version_changed',
					$this->version_has_changed[0],
					$this->version_has_changed[1]
				);
				if ( ! empty( $this->first_install ) ) {
					do_action( 'wishlistmember_first_install', $this->version_has_changed[1] );
				}
			}
		}

		/**
		 * This makes sure that our html emails are sent with an alternate text body to help prevent triggering spam filters
		 *
		 * Action: phpmailer_init
		 *
		 * @param object $phpmailer WordPress PHPMailer Object
		 */
		public function send_multipart_email( $phpmailer ) {
			// clear AltBody if it is the same as our custom LastWLMAltBody
			if ( wlm_arrval( $phpmailer, 'LastWLMAltBody' ) == wlm_arrval( $phpmailer, 'AltBody' ) ) {
				$phpmailer->AltBody = '';
			}

			// we only make changes if WLM is sending the email
			if ( empty( $this->sending_mail ) ) {
				return;
			}

			// create text AltBody if messages looks like HTML
			if ( wlm_has_html( $phpmailer->Body ) ) {
				// create text AltBody
				$phpmailer->AltBody = preg_replace( '/[\n]{3,}/', "\n\n", strip_tags( str_replace( array( '<br>', '<br/>', '<br />', '</div>' ), "\n", str_replace( '</p>', "\n\n", $phpmailer->Body ) ) ) );

				// save the AltBody to LastWLMAltBody so we can clear AltBody it if the next wp_mail caller is not us
				$phpmailer->LastWLMAltBody = $phpmailer->AltBody;
			}
		}

		/**
		 * Process one-time login if wlm_get_data()[wlmotl] and wlm_get_data()['uid'] are both set
		 *
		 * Action: init
		 *
		 * @uses \WishListMember|User::do_onetime_login
		 */
		public function onetime_login() {
			$key = wlm_get_data()['wlmotl'];
			$uid = wlm_get_data()['uid'];

			if ( $key && $uid ) {
				\WishListMember\User::do_onetime_login( $uid, $key );
			}
		}

		/**
		 * Insert one-time login javascript and css that adds link to login via OTL below the submit button
		 *
		 * Action: login_footer
		 */
		public function onetime_login_footer() {
			$action = wlm_arrval( $_REQUEST, 'action' ) ? wlm_arrval( 'lastresult' ) : 'login';
			if ( in_array( $action, array( 'login', 'wishlistmember-otl' ) ) ) {
				include $this->plugindir3 . '/assets/onetime_login/footer.php';
			}
		}

		/**
		 * Displays one-time login form
		 *
		 * Action: login_form_wishlistmember-otl
		 */
		public function onetime_login_form() {
			$user_login = wlm_or( wlm_post_data()['user_login'], '' );
			if ( $user_login ) {

				// get user by login
				$user = get_user_by( 'login', $user_login );

				// get user by email
				if ( ! $user && strpos( $user_login, '@' ) ) {
					$user = get_user_by( 'email', $user_login );
				}

				// send link if user is found or display error otherwise
				if ( $user ) {
					$this->send_email_template( 'onetime_login_link', $user->ID );
					$message = __( 'Check your email for the one-time login link.', 'wishlist-member' );
				} else {
					$error = __( 'Unknown username or email address.', 'wishlist-member' );
				}
			}

			// load our form
			include $this->plugindir3 . '/assets/onetime_login/form.php';
			exit;
		}

		/**
		 * Generates new API Key for specified key name
		 *
		 * Action: wp_ajax_wlm3_generate_api_key
		 */
		public function generate_api_key() {
			$key_name = wlm_or( wlm_post_data()['key_name'], '' );
			if ( $key_name ) {
				$result = ( new \WishListMember\APIKey() )->update( $key_name );
			} else {
				$result = wlm_generate_password( 50, true );
			}
			if ( $result ) {
				wp_send_json(
					array(
						'success' => 1,
						'n'       => $key_name,
						'key'     => $result,
					)
				);
			} else {
				wp_send_json( array( 'success' => 0 ) );
			}
		}

		/**
		 * Sends email confirmed notification to user after user confirms email
		 *
		 * Action: wishlistmember_confirm_user_levels
		 *
		 * @param  integer $user_id User ID
		 * @param  array   $levels  Array of Membership Levels
		 */
		public function send_email_confirmed_notification( $user_id, $levels ) {
			static $wpm_levels;
			if ( ! is_numeric( $user_id ) || empty( $levels ) ) {
				return;
			}
			if ( ! $wpm_levels ) {
				$wpm_levels = $this->get_option( 'wpm_levels' );
			}
			foreach ( (array) $levels as $level ) {
				if ( empty( $wpm_levels[ $level ] ) ) {
					continue;
				}
				$macros = array( '[memberlevel]' => $wpm_levels[ $level ]['name'] );
				$this->send_email_template( 'email_confirmed', $user_id, $macros );
			}
		}

		/**
		 * WishList Member hook for handling password resets
		 * Action: retrieve_password, retrieve_password/wlminternal
		 *
		 * Note: The 'retrieve_password' action is triggered by `get_password_reset_key()` which we use when available
		 * To prevent an endless loop caused by repeated calls to the 'retrieve_password' action, we check if the static
		 * $counter variable is truish and return immediately if so.
		 *
		 * @param string  $user_login
		 * @param boolean $internal   (optional) default: false. true if this is an internal WLM call
		 */
		public function retrieve_password_hook( $user_login ) {
			// prevent 'retrieve_password' action loops
			static $counter = 0;
			if ( $counter ) {
				return;
			}
			$counter++;

			global $wpdb, $wp_version;

			switch ( current_action() ) {
				case 'retrieve_password_message':
					$user_login = func_get_args()[2];
					break;
				case 'retrieve_password/wlminternal':
					$internal = true;
					break;
			}

			if ( function_exists( 'get_password_reset_key' ) ) {
				$user = get_user_by( 'login', $user_login );
				$key  = get_password_reset_key( $user );
				if ( is_wp_error( $key ) ) {
					return;
				}
			} else {
				// Generate something random for a password reset key.
				$key = wlm_generate_password( 20, false );

				/**
				 * Fires when a password reset key is generated.
				 *
				 * @since 2.5.0
				 *
				 * @param string $user_login The username for the user.
				 * @param string $key        The generated password reset key.
				 */
				do_action( 'retrieve_password_key', $user_login, $key );

				// Now insert the key, hashed, into the DB.
				if ( empty( $wp_hasher ) ) {
					require_once ABSPATH . 'wp-includes/class-phpass.php';
					$wp_hasher = new PasswordHash( 8, true );
				}
				$hashed = time() . ':' . $wp_hasher->HashPassword( $key );

				$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
			}

			if ( empty( $user ) ) {
				$user = get_user_by( 'login', $user_login );
			}

			$reset_query = array(
				'action' => 'rp',
				'key'    => $key,
				'login'  => rawurlencode( $user->user_login ),
			);

			$macros = array(
				'[memberlevel]' => $this->get_membership_levels( $user->ID, true ),
				'[reseturl]'    => add_query_arg( $reset_query, wp_login_url() ),
			);
			$this->send_email_template( 'lost_password', $user->ID, $macros );

			// If not requested by ADMIN in the Member's profile page then do redirect
			if ( ! $internal ) {
				header( 'Location:' . wp_login_url() . '?checkemail=confirm' );
				exit;
			}
		}
	}
}
