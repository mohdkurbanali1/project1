<?php
/**
 * Integration Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Integration Methods trait
*/
trait Integration_Methods {
	/**
	 * Subscribe to Autoresponder.
	 *
	 * @param string $fname    First name.
	 * @param string $lname    Last name.
	 * @param string $email    Email address.
	 * @param int    $level_id Level ID.
	 */
	public function ar_subscribe( $fname, $lname, $email, $level_id ) {
		// Autoresponder subscription.

		if ( $this->get_option( 'privacy_enable_consent_to_market' ) && $this->get_option( 'privacy_consent_affects_autoresponder' ) ) {
			$user = get_user_by( 'email', $email );
			if ( false !== $user && ! $this->Get_UserMeta( $user->ID, 'wlm_consent_to_market' ) ) {
				return; // no consent to market by email.
			}
		}

		$this->sending_mail = true; // we add this to trigger our hook.
		$this->ar_sender    = array(
			'name'       => "{$fname} {$lname}",
			'email'      => "{$email}",
			'first_name' => $fname,
			'last_name'  => $lname,
		);
		$ars                = $this->get_option( 'Autoresponders' );
		$arps               = (array) $this->get_option( 'active_email_integrations' );

		if ( ! empty( $ars['ARProvider'] ) ) {
			$arps[] = (string) $ars['ARProvider'];
			$arps   = array_unique( $arps );
		}

		foreach ( $arps as $arp ) { // go through all active integrations.
			if ( empty( $ars[ $arp ] ) ) {
				continue;
			}
			$ar_settings = $ars[ $arp ];
			// retrieve the method to call.
			$ar_integration_info = $this->ARIntegrationMethods[ $arp ];
			// and call it.
			if ( $ar_integration_info ) {
				if ( ! class_exists( $ar_integration_info['class'] ) ) {
					include_once $ar_integration_info['file'];
					$this->RegisterClass( $ar_integration_info['class'] );
				}
				call_user_func_array( array( wishlistmember_instance(), $ar_integration_info['method'] ), array( $ar_settings, $level_id, $email, false ) );
			}
		}

		do_action_deprecated( 'wishlistmember3_autoresponder_subscribe', array( $email, $level_id ), '3.10', 'wishlistmember_autoresponder_subscribe' );
		do_action( 'wishlistmember_autoresponder_subscribe', $email, $level_id );

		$this->ar_sender    = '';
		$this->sending_mail = false;
	}

	/**
	 * Unsubscribe from Autoresponder
	 *
	 * @param string $fname    First name.
	 * @param string $lname    Last name.
	 * @param string $email    Email address.
	 * @param int    $level_id Level ID.
	 */
	public function ar_unsubscribe( $fname, $lname, $email, $level_id ) {
		$this->sending_mail = true; // we add this to trigger our hook.
		$this->ar_sender    = array(
			'name'  => "{$fname} {$lname}",
			'email' => "{$email}",
		);
		$ars                = $this->get_option( 'Autoresponders' );
		$arps               = (array) $this->get_option( 'active_email_integrations' );

		if ( ! empty( $ars['ARProvider'] ) ) {
			$arps[] = (string) $ars['ARProvider'];
			$arps   = array_unique( $arps );
		}

		foreach ( $arps as $arp ) { // go through all active integrations.
			if ( empty( $ars[ $arp ] ) ) {
				continue;
			}
			$ar_settings = $ars[ $arp ];
			// retrieve the method to call.
			$ar_integration_info = $this->ARIntegrationMethods[ $arp ];
			// and call it.
			if ( $ar_integration_info ) {
				if ( ! class_exists( $ar_integration_info['class'] ) ) {
					include_once $ar_integration_info['file'];
					$this->RegisterClass( $ar_integration_info['class'] );
				}
				call_user_func_array( array( wishlistmember_instance(), $ar_integration_info['method'] ), array( $ar_settings, $level_id, $email, true ) );
			}
		}

		do_action_deprecated( 'wishlistmember3_autoresponder_unsubscribe', array( $email, $level_id ), '3.10', 'wishlistmember_autoresponder_unsubscribe' );
		do_action( 'wishlistmember_autoresponder_unsubscribe', $email, $level_id );

		$this->ar_sender    = '';
		$this->sending_mail = false;
	}

	/**
	 * Subscribe to Webinar
	 *
	 * @param string $fname    First name.
	 * @param string $lname    Last name.
	 * @param string $email    Email address.
	 * @param int    $level_id Level ID.
	 */
	public function webinar_subscribe( $fname, $lname, $email, $level_id ) {
		$data = array(
			'first_name' => $fname,
			'last_name'  => $lname,
			'email'      => $email,
			'level'      => $level_id,
		);
		do_action( 'wishlistmember_webinar_subscribe', $data );
	}

	/**
	 * Registers a WishList Member Extensions
	 *
	 * @param string $name        Extension name.
	 * @param string $url         Extension Website.
	 * @param string $version     Extension version.
	 * @param string $description Extension description.
	 * @param string $author      Extension's author.
	 * @param string $authorurl   Extension author's URL.
	 * @param string $file        Extension's filename.
	 */
	public function register_extension( $name, $url, $version, $description, $author, $authorurl, $file ) {
		$file = basename( $file );
		if ( $file ) {
			$this->loadedExtensions[ $file ] = array(
				'Name'        => $name,
				'URL'         => $url,
				'Version'     => $version,
				'Description' => $description,
				'Author'      => $author,
				'AuthorURL'   => $authorurl,
				'File'        => $file,
			);
		}
	}

	/**
	 * Unregisters an extension
	 *
	 * @param string $file Extension's filename.
	 */
	public function unregister_extension( $file ) {
		unset( $this->loadedExtensions[ $file ] );
	}

	/**
	 * Returns an array of loaded extensions
	 *
	 * @return array Loaded extensions
	 */
	public function get_registered_extensions() {
		return $this->loadedExtensions;
	}

	/**
	 * Loads the init file for the integration
	 *
	 * @param $file File name.
	 */
	public function load_init_file( $file ) {
		global $WishListMemberInstance;
		$init_file = str_replace( '.php', '.init.php', $file );
		if ( basename( $init_file ) === $init_file ) {
			$init_file = $this->plugindir . '/lib/' . $init_file;
		}
		if ( file_exists( $init_file ) ) {
			include_once $init_file;
		}
	}

	/**
	 * Register a Shopping Cart Integration Function
	 *
	 * @param string $uri        URI Prefix.
	 * @param string $filename   File name.
	 * @param string $classname  Class name.
	 * @param string $methodname Method name.
	 */
	public function register_sc_integration( $uri, $filename, $classname, $methodname ) {
		if ( ! isset( $this->SCIntegrationURIs ) ) {
			$this->SCIntegrationURIs = array();
		}

		$this->SCIntegrationURIs[ $uri ] = array(
			'file'   => $filename,
			'class'  => $classname,
			'method' => $methodname,
		);
	}

	/**
	 * Register an Autoresponder Integration Function
	 *
	 * @param string $ar_option  Autoresponder Option Name.
	 * @param string $filename   File name.
	 * @param string $classname  Class name.
	 * @param string $methodname Method name.
	 */
	public function register_ar_integration( $ar_option, $filename, $classname, $methodname ) {
		if ( ! isset( $this->ARIntegrationMethods ) ) {
			$this->ARIntegrationMethods = array();
		}

		if ( $classname && $methodname ) {
			$this->ARIntegrationMethods[ $ar_option ] = array(
				'file'   => $filename,
				'class'  => $classname,
				'method' => $methodname,
			);
		}
	}

	/**
	 * Register an Webinar Integration Function
	 *
	 * @param string $webinar    Autoresponder Option Name.
	 * @param string $filename   File name.
	 * @param string $classname  Class name.
	 */
	public function register_webinar_integration( $webinar, $filename, $classname ) {
		if ( ! isset( $this->WebinarIntegrations ) ) {
			$this->WebinarIntegrations = array();
		}

		$this->WebinarIntegrations[ $webinar ] = array(
			'file'  => $filename,
			'class' => $classname,
		);
	}

	/**
	 * This function returns a 200 OK Response Header and
	 * Displays the text WishList Member and a link to the WP homepage
	 *
	 * @param string $scuri Shopping cart URI.
	 */
	public function cart_integration_terminate( $scuri = '' ) {
		global $wlm_no_cartintegrationterminate;
		if ( ! empty( $wlm_no_cartintegrationterminate ) ) {
			return;
		}

		if ( 'POST' === wlm_server_data['REQUEST_METHOD'] ) {
			exit;
		}

		$url = add_query_arg( 'sp', $scuri ? 'invalid_registration2' : 'invalid_registration1', $this->magic_page() );

		// http redirect.
		wp_safe_redirect( $url );
		// meta redirect.
		printf( '<meta http-equiv="refresh" content="0;URL=\'%s\'" />', esc_url( $url ) );
		// javascript redirect.
		printf( '<script type="text/javascript">document.location = "%s";</script>', esc_url( $url ) );
		exit;
	}

	/**
	 * Get/set active status of an "Other Provider" integration.
	 *
	 * @param  string $integration_file Integration file.
	 * @param  bool   $status           Active status.
	 * @return bool|null                Active status. Null if not found.
	 */
	public function integration_active( $integration_file, $status = null ) {
		$integrations = (array) $this->get_option( 'ActiveIntegrations' );
		if ( ! is_null( $status ) ) {
			$integrations[ $integration_file ] = (bool) $status;
			$this->save_option( 'ActiveIntegrations', $integrations );
		}

		if ( isset( $integrations[ $integration_file ] ) ) {
			return (bool) $integrations[ $integration_file ];
		} else {
			return null;
		}
	}

	public function integration_shortcodes() {
		// register tinymce plugin for integrations
		global $WLMTinyMCEPluginInstanceOnly;
		if ( $WLMTinyMCEPluginInstanceOnly && count( $this->IntegrationShortcodes ) > 0 ) {
			$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes( 'Integrations', array(), array(), 0, null, $this->IntegrationShortcodes );
		}
	}

	public function integration_errors() {
		if ( ! empty( $this->integration_errors ) ) {
			$ActiveShoppingCarts = (array) $this->get_option( 'ActiveShoppingCarts' );
			foreach ( (array) $this->integration_errors as $key => $error ) {
				if ( in_array( $key, $ActiveShoppingCarts ) ) {
					$show_error = true;
					if ( 'WishListMember' == wlm_get_data()['page' ] && 'integration' == wlm_get_data()['wl' ] ) {
						$show_error = false;
					} else {
						if ( ! empty( $this->active_integration_indicators[ $key ] ) && is_array( $this->active_integration_indicators[ $key ] ) ) {
							foreach ( $this->active_integration_indicators[ $key ] as $option ) {
								$show_error = $show_error & ( (bool) $this->get_option( $option ) );
							}
						}
					}
					if ( $show_error ) {
						printf( '<div class="error">%s</div>', wp_kses( $error, 'data' ) );
					}
				}
			}
		}
	}

	/**
	 * Load payment providers
	 */
	public function load_integrations_payment_providers() {
		// payment providers.
		$providers = require WLM_PLUGIN_DIR . '/legacy/lib/integration.shoppingcarts.php';
		// active providers.
		$active = (array) $this->get_option( 'ActiveShoppingCarts' );
		// load providers.
		foreach ( $providers as $i_file => $i_data ) {
			if ( ! empty( $i_data['php_minimum'] ) ) {
				if ( version_compare( phpversion(), $i_data['php_minimum'] ) < 0 ) {
					if ( ! empty( $i_data['php_minimum_msg'] ) ) {
						$this->integration_errors[ $i_file ] = $i_data['php_minimum_msg'];
					}
					if ( ! empty( $i_data['active_indicators'] ) ) {
						$this->active_integration_indicators[ $i_file ] = $i_data['active_indicators'];
					}
					continue;
				}
			}
			if ( in_array( $i_file, $active, true ) ) {
				if ( empty( $i_data['handler'] ) ) {
					$this->load_init_file( $i_file );
					$this->register_sc_integration( $i_data['optionname'], $i_file, $i_data['classname'], $i_data['methodname'] );
				} else {
					$handler = sprintf( '%s/integrations/payments/%s/handler.php', $this->plugindir3, $i_data['name'] );
					if ( file_exists( $handler ) ) {
						require_once $handler;
					}
				}
			}
		}
	}

	/**
	 * Load email providers
	 */
	public function load_integrations_email_providers() {
		
		// pre v3.0 active email providers
		$providers = wlm_arrval( $this->get_option( 'Autoresponders' ), 'ARProvider' );
		if ( ! is_array( $providers ) ) {
			$providers = array();
		}

		// 3.0 active email providers
		$active_email_integrations = $this->get_option( 'active_email_integrations' );
		if ( ! is_array( $active_email_integrations ) ) {
			$active_email_integrations = array();
		}
		$providers = array_merge( $providers, $active_email_integrations );

		foreach ( $providers as $provider ) {
			if ( ! $provider ) {
				continue;
			}
			require_once $this->plugindir . '/lib/integration.autoresponders.php';
			foreach ( $wishlist_member_autoresponders as $i_file => $i_data ) {
				// only load the currently used autoresponder init file.
				if ( $provider === $i_data['optionname'] ) {
					if ( ! empty( $i_data['handler'] ) ) {
						$i_file = sprintf( '%s/integrations/emails/%s/handler.php', $this->plugindir3, $i_file );
					} else {
						$i_file = sprintf( '%s/legacy/lib/%s', $this->plugindir3, $i_file );
					}
					$this->load_init_file( $i_file );
					$this->register_ar_integration( wlm_arrval( $i_data, 'optionname' ), $i_file, wlm_arrval( $i_data, 'classname' ), wlm_arrval( $i_data, 'methodname' ) );
				}
			}
		}
	}

	/**
	 * Load othe providers
	 */
	public function load_integrations_other_providers() {
		$providers = (array) $this->get_option( 'active_other_integrations' );
		foreach ( $providers as $provider ) {
			$i_files = array(
				sprintf( '%s/lib/integration.other.%s.php', $this->plugindir, $provider ),
				sprintf( '%s/lib/integration.webinar.%s.php', $this->plugindir, $provider ),
				sprintf( '%s/integrations/others/%s/handler.php', $this->plugindir3, $provider ),
			);
			foreach ( $i_files as $i_file ) {
				if ( file_exists( $i_file ) ) {
					include_once $i_file;
				}
			}
		}
	}

	/**
	 * Load legacy extensions.
	 */
	public function load_legacy_extensions() {
		// support for old extensions folder.
		$extensions = glob( WLM_PLUGIN_DIR . '/legacy/extensions/*.php' );
		foreach ( (array) $extensions as $k => $ex ) {
			if ( 'api.php' === basename( $ex ) ) {
				unset( $extensions[ $k ] );
			}
		}
		sort( $extensions );
		$this->extensions = $extensions;
	}
}

// register hooks.
add_action(
	'wishlistmember_register_hooks',
	function( $wlm ) {
		add_action( 'admin_footer', array( $wlm, 'integration_errors' ) );
		add_action( 'wishlistmember_load_integrations', array( $wlm, 'load_integrations_email_providers' ) );
		add_action( 'wishlistmember_load_integrations', array( $wlm, 'load_integrations_other_providers' ) );
		add_action( 'wishlistmember_load_integrations', array( $wlm, 'load_integrations_payment_providers' ) );
		add_action( 'wishlistmember_load_integrations', array( $wlm, 'load_legacy_extensions' ) );
	}
);
