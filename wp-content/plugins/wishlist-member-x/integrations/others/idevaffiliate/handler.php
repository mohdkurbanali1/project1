<?php
/**
 * IDevAffiliate Integration
 * Code based on the work of Charly Leetham - dated July 21st, 2011
 *
 * This file contains code that handles sending of commission information to iDevAffiliate
 *
 * @package WishListMember/OtherProviders
 */

if ( ! class_exists( 'WLMiDev' ) ) {

	/**
	 * WishList Member iDevAffiliate Class
	 */
	class WLMiDev {

		public $mode;

		public function __construct() {
			// fix data - to include wlm_idevspecificamount
			$data = wishlistmember_instance()->get_option( 'WLMiDev' );
			$data = is_array( $data ) ? $data : array();
			if ( ! isset( $data['wlm_idevspecificamount'] ) ) {
				$keys = isset( $data['wlm_idevamountfirst'] ) ? array_keys( $data['wlm_idevamountfirst'] ) : array();
				foreach ( $keys as $key ) {
					$fixed                                  = $data['wlm_idevamountpayment'][ $key ] + $data['wlm_idevamountpaymentrecur'][ $key ];
					$data['wlm_idevspecificamount'][ $key ] = $fixed ? 'yes' : 'no';
				}
				wishlistmember_instance()->save_option( 'WLMiDev', $data );
			}

			$this->mode = basename( __FILE__ );

			// add hooks
			if ( function_exists( 'curl_init' ) ) {
				add_action( 'wishlistmember_shoppingcart_reactivate', array( $this, 'Reactivate' ), 10, 2 );
				add_filter( 'wishlistmember_registration_page', array( $this, 'RegPage' ), 10, 2 );
				add_action( 'wishlistmember_suppress_other_integrations', array( $this, 'RemoveHooks' ), 10, 2 );
			}

		}

		/**
		 * Checks if iDevAffiliate is active
		 *
		 * @param  WishListMember $wlm WishList Member 3.0 Object
		 * @return boolean
		 */
		public function IsActive( $wlm ) {
			$actives = $wlm->get_option( 'active_other_integrations' );
			if ( ! is_array( $actives ) ) {
				return false;
			}
			return in_array( 'idevaffiliate', $actives );
		}

		/**
		 * Check if $wlm is a valid WishListMember object
		 *
		 * @param  WishListMember $wlm WishList Member 3.0 object
		 * @return boolean
		 */
		public function CheckWLM( $wlm ) {
			if ( ! is_object( $wlm ) ) {
				return false;
			}
			return 'WishListMember' == get_class( $wlm );
		}

		/**
		 * Get the client's IP addresss
		 *
		 * @return string
		 */
		public function getClientIP() {
			foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
				if ( true === array_key_exists( $key, wlm_server_data( true ) ) ) {
					foreach ( array_map( 'trim', explode( ',', wlm_server_data()[ $key ] ) ) as $ip ) {
						if ( false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
							if ( wlm_server_data()['SERVER_ADDR'] != $ip ) {
								return $ip;
							}
						}
					}
				}
			}
			return '';
		}

		/**
		 * Runs when an integration calls the ShoppingCartReactivate
		 * wishlistmember_shoppingcart_reactivate action
		 *
		 * @param  WishListMember $wlm WishList Member 3.0 object
		 */
		public function Reactivate( $wlm ) {
			if ( ! $this->CheckWLM( $wlm ) ) {
				return;
			}

			/* load idevaffiliate information */
			$data = $wlm->get_option( 'WLMiDev' );
			$url  = wlm_trim( $data['wlm_idevurl'] );
			/* no url? we quit */
			if ( ! $url || ! $this->IsActive( $wlm ) ) {
				return false;
			}

			/* append "/sale.php?" to url */
			if ( '/' != substr( $url, -1 ) ) {
				$url .= '/';
			}
			$url .= 'sale.php?';

			/* the transaction ID */
			$sctxnid = wlm_post_data()['sctxnid'];
			/* get user based on transaction ID */
			$user = new WP_User( $wlm->get_user_id_from_txn_id( $sctxnid ) );
			/* get saved wlm_idevurl info for user */
			$idevurl                    = get_user_meta( $user->ID, 'wlm_idevurl', true );
			list($xurl, $recuramt, $ip) = $idevurl[ $sctxnid ];
			$recuramt                  += 0;
			/* no recurring amount? we quit */
			if ( ! $recuramt ) {
				return false;
			}

			/* replace value of idev_saleamt with recurring amount */
			parse_str( $xurl, $output );
			$output['idev_saleamt'] = $recuramt;
			/*
			 reconstruct URL */
			/* $xurl=implode('&',$xurl); */

			/* call iDevAffiliate */
			$ch          = curl_init();
			$sendidevurl = $url . $xurl;
			curl_setopt( $ch, CURLOPT_URL, $sendidevurl );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

			curl_exec( $ch );
			$this->log( array( $sendidevurl, curl_getinfo( $ch ), $curl_result ) );
			curl_close( $ch );
		}

		/**
		 * Runs when a registration page is loaded
		 * wishlistmember_shoppingcart_reactivate filter
		 *
		 * @param string         $content Registration Page content
		 * @param WishListMember $wlm     WishList Member 3.0 object
		 */
		public function RegPage( $content, $wlm ) {

			/*
			 get the user */
			// no user specified, return
			if ( empty( wlm_get_data()['u'] ) ) {
				return $content;
			}

			$user = new WP_User( 0, wlm_get_data()['u'] );
			if ( empty( $user->ID ) ) {
				return $content; // invalid username, return
			}
			$wlmuser = new \WishListMember\User( $user->ID, true );

			/* the Level ID */
			$wpm_id = wlm_get_data()['reg'];

			/* create txnid for iDevAffiliate */
			$user_level = $wlmuser->Levels[ $wpm_id ];
			$sctxnid    = $user_level->TxnID;
			$txnid      = urlencode( $sctxnid . '--' . $wpm_id );

			// we already sent this to idevaffiliate, return
			if ( get_user_meta( $user->ID, md5( $txnid ) ) ) {
				return $content;
			}

			/* the ip address */
			$remote_addr = $this->getClientIP();

			// $wlm is not WishList Member, return
			if ( ! $this->CheckWLM( $wlm ) ) {
				return $content;
			}

			// get iDevAffiliate config
			$data = $wlm->get_option( 'WLMiDev' );

			// get iDevAffiliate URL
			$url = wlm_trim( $data['wlm_idevurl'] );

			// no url, return
			if ( ! $url || ! $this->IsActive( $wlm ) ) {
				return $content;
			}

			$commissionflag = 0;

			/* append "sale.php?" to URL */
			$url = trailingslashit( $url ) . 'sale.php?';

			/* retrieve the amount for the membership level */
			$amt = $data['wlm_idevamountfirst'][ $wpm_id ] + 0;

			/* Check if the amount payable is $0 */
			if ( $amt > 0 ) {
				$commissionflag = 1;
				$firstamt       = $amt;
			}

			/* retrieve the recurring amount for the membership level */
			$recuramt = (float) $data['wlm_idevamountrecur'][ $wpm_id ] ? (float) $data['wlm_idevamountrecur'][ $wpm_id ] : 0;

			/* Check if the recurring amount payable is $0 */
			if ( $recuramt > 0 ) {
				$recuramt1 = $recuramt;
			}

			if ( 'yes' == $data['wlm_idevspecificamount'][ $wpm_id ] ) {
				/* retrieve the fixed commission amount for the membership level */
				$amtpayment = $data['wlm_idevamountpayment'][ $wpm_id ] + 0;
				/* Check if the fixed commission amount is $0 */
				if ( $amtpayment > 0 ) {
					$commissionflag = 1;
					$firstamt       = $amtpayment;
				}

				/* retrieve the recurring fixed commission amount for the membership level */
				$recuramtpayment = (float) $data['wlm_idevamountpaymentrecur'][ $wpm_id ] ? (float) $data['wlm_idevamountpaymentrecur'][ $wpm_id ] : 0;

				/* Check if recurring fixed commission is $0 */
				if ( $recuramtpayment > 0 ) {
					$recuramt1 = $recuramtpayment;
				}
			}

			/* Initial Payment Amount */
			if ( $amt > 0 ) {
				$xurl = 'profile=60&idev_saleamt=' . $amt . '&idev_ordernum=' . $txnid . '&ip_address=' . $remote_addr;
			}

			if ( $amtpayment > 0 ) {
				$xurl = $xurl . '&idev_commission=' . $amtpayment;
			}

			// Add currency parameter for initial payment URL to support multi-currency.
			if ( $data['wlm_idevcurrency'][ $wpm_id ] && $xurl ) {
				$xurl = $xurl . '&idev_currency=' . $data['wlm_idevcurrency'][ $wpm_id ];
			}

			/* Recurring Payment Amount */
			if ( $recuramt ) {
				$xurlrecur = 'profile=60&idev_saleamt=' . $recuramt . '&idev_ordernum=' . $txnid . '&ip_address=' . $remote_addr;
				if ( $recuramtpayment > 0 ) {
					$xurlrecur = $xurlrecur . '&idev_commission=' . $recuramtpayment;
				}

				// Add currency parameter for recurring payment URL to support multi-currency before saving.
				if ( $data['wlm_idevcurrency'][ $wpm_id ] && $xurlrecur ) {
					$xurlrecur = $xurlrecur . '&idev_currency=' . $data['wlm_idevcurrency'][ $wpm_id ];
				}

				/* save URL for recurring payments. This is used in the $this -> Reactivate function. */
				$idevurl = get_user_meta( $user->ID, 'wlm_idevurl', true );
				if ( ! is_array( $idevurl ) ) {
					$idevurl = array();
				}
				$idevurl[ $sctxnid ] = array( $xurlrecur, $recuramt, $remote_addr );
				update_user_meta( $user->ID, 'wlm_idevurl', $idevurl );
			}
			/* call iDevAffiliate */

			$sendidevurl = $url . $xurl;

			if ( $commissionflag > 0 ) {
				error_reporting( 0 );
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $sendidevurl );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

				$curl_result = curl_exec( $ch );
				$this->log( array( $sendidevurl, curl_getinfo( $ch ), $curl_result ) );

				if ( false === $curl_result ) {
					$response  = curl_error( $ch );
					$cjlrecord = 'after registration: CURL error response' . $response . "\n\r";
				} else {
					$cjlrecord = 'after registration: CURL success' . "\n\r";
					add_user_meta( $user->ID, md5( $txnid ), $txnid );
				}
				curl_close( $ch );
			}

			return $content;
		}

		/**
		 * Suppress other integrations
		 * wishlistmember_suppress_other_integrations action
		 */
		public function RemoveHooks() {
			remove_action( 'wishlistmember_shoppingcart_reactivate', array( $this, 'Reactivate' ), 10 );
			remove_filter( 'wishlistmember_registration_page', array( $this, 'RegPage' ), 10 );
		}

		public function log( $data ) {
			$data = json_encode( $data );
			set_transient( 'wlmidev_' . time() . '_' . md5( $data ), $data, MONTH_IN_SECONDS );
		}

	}

	// initialize
	if ( ! isset( $WLMiDev ) ) {
		$WLMiDev = new WLMiDev();
	}
}
