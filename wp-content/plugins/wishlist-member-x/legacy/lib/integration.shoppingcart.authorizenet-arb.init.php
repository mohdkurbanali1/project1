<?php

class WLMAuthorizeNetARB {
	private $wlm;
	private $formsettings;
	private $apisettings;
	public $subscriptions;
	private $arb;

	public function __construct() {
		global $WishListMemberInstance;
		if ( empty( $WishListMemberInstance ) ) {
			return;
		}

		$default_form_settings = array(
			'supportemail'         => '',
			'logo'                 => '',
			'formheading'          => 'Register for %level',
			'formheadingrecur'     => 'Subscribe to %level',
			'formbuttonlabel'      => 'Pay',
			'formbuttonlabelrecur' => 'Pay',
			'beforetext'           => '',
			'beforetextrecur'      => '',
			'aftertext'            => '',
			'aftertextrecur'       => '',
		);

		$this->wlm = $WishListMemberInstance;

		// subscriptions
		$this->subscriptions = $this->wlm->get_option( 'anetarbsubscriptions' );
		$this->subscriptions = $this->subscriptions ? $this->subscriptions : array();

		// api settings
		$this->apisettings = $this->wlm->get_option( 'anetarbsettings' );
		$this->apisettings = $this->apisettings ? $this->apisettings : array();

		// settings of the payment form
		$this->formsettings = $this->wlm->get_option( 'authnet_arb_formsettings' );
		$this->formsettings = is_array( $this->formsettings ) && count( $this->formsettings ) > 0 ? $this->formsettings : $default_form_settings;
	}

	public function init() {
		if ( empty( $this->wlm ) ) {
			return;
		}

		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'admin_init', array( $this, 'include_underscorejs' ) );
		add_shortcode( 'wlm_authorizenet_arb_btn', array( $this, 'anet_arb_btn' ) );
		add_action( 'wp_footer', array( $this, 'footer' ), 100 );

		add_action( 'wp_ajax_wlm_anetarb_new-subscription', array( $this, 'new_subscription' ) );
		add_action( 'wp_ajax_wlm_anetarb_all-subscriptions', array( $this, 'get_all_subscriptions' ) );
		add_action( 'wp_ajax_wlm_anetarb_save-subscription', array( $this, 'save_subscription' ) );
		add_action( 'wp_ajax_wlm_anetarb_delete-subscription', array( $this, 'delete_subscription' ) );

		add_action( 'wishlistmember_arb_sync', array( $this, 'syn_arb' ) );
		// TinyMCE Editor Buttons

		add_filter( 'wishlistmember_integration_shortcodes', array( $this, 'anet_arb_shortcode_btns' ) );
	}

	public function wp_init() {
		$login = isset( $this->apisettings['api_login_id'] ) ? $this->apisettings['api_login_id'] : '';
		$key   = isset( $this->apisettings['api_transaction_key'] ) ? $this->apisettings['api_transaction_key'] : '';
		if ( $login && $key ) { // run CRON only if API SETTINGS are set.
			// cron for syncing arb
			$next_schedule = wp_next_scheduled( 'wishlistmember_arb_sync' );
			if ( ! $next_schedule ) {
				wp_schedule_event( time(), 'twicedaily', 'wishlistmember_arb_sync' );
				// this will be cleared on WLM cron clearing
			} else {
				if ( $next_schedule <= time() ) {
					spawn_cron( time() );
				}
			}
		}
	}

	public function load_js() {
		static $loaded = false;
		if ( ! $loaded ) {
			global $WishListMemberInstance;
			wp_enqueue_script( 'wlm-jquery-fancybox' );
			wp_enqueue_style( 'wlm-jquery-fancybox' );
			wp_enqueue_script( 'wlm-popup-regform' );
			wp_enqueue_style( 'wlm-popup-regform-style' );
			$loaded = true;
		}
	}

	public function include_underscorejs() {
		global $WishListMemberInstance;
		if ( is_admin() && isset( wlm_get_data()['page'] ) && wlm_get_data()['page'] == $this->wlm->MenuID && isset( wlm_get_data()['wl'] ) && 'integration' == wlm_get_data()['wl'] ) {
			wp_enqueue_script( 'underscore-wlm', $WishListMemberInstance->pluginURL . '/js/underscore-min.js', array( 'underscore' ), $WishListMemberInstance->Version );
		}
	}

	public function get_all_subscriptions() {
		echo json_encode( $this->subscriptions );
		die();
	}

	public function new_subscription() {
		$subscriptions = $this->subscriptions;
		if ( empty( $subscriptions ) ) {
			$subscriptions = array();
		}

		// create an id for this button
		$id = strtoupper( substr( sha1( microtime() ), 1, 10 ) );

		$subscription = array(
			'id'        => $id,
			'name'      => wlm_post_data()['name'],
			'currency'  => 'USD',
			'amount'    => 10,
			'recurring' => 0,
			'sku'       => wlm_post_data()['sku'],
		);

		$this->subscriptions[ $id ] = $subscription;
		$this->wlm->save_option( 'anetarbsubscriptions', $this->subscriptions );

		echo json_encode( $subscription );
		die();
	}

	public function save_subscription() {
		$id           = wlm_post_data()['id'];
		$subscription = wlm_post_data( true );
		if ( isset( $subscription['recurring'] ) && 1 == $subscription['recurring'] ) {
			if ( isset( $subscription['recur_billing_cycle'] ) && 0 == $subscription['recur_billing_cycle'] ) {
				$subscription['recur_billing_cycle'] = '';
			}
			if ( isset( $subscription['trial_billing_cycle'] ) && 0 == $subscription['trial_billing_cycle'] ) {
				$subscription['trial_billing_cycle'] = '';
				if ( isset( $subscription['trial_amount'] ) ) {
					$subscription['trial_amount'] = '';
				}
			}

			if ( isset( $subscription['trial_amount'] ) && 0 == $subscription['trial_amount'] ) {
				$subscription['trial_amount'] = 0;
			}
		}
		$this->subscriptions[ $id ] = $subscription;
		$this->wlm->save_option( 'anetarbsubscriptions', $this->subscriptions );
		echo json_encode( $this->subscriptions[ $id ] );
		die();
	}

	public function delete_subscription() {
		$id = wlm_post_data()['id'];
		unset( $this->subscriptions[ $id ] );
		$this->wlm->save_option( 'anetarbsubscriptions', $this->subscriptions );
		die();
	}

	public function anet_arb_btn( $atts, $content ) {
		$atts      = extract(
			shortcode_atts(
				array(
					'sku'       => null,
					'link_text' => '',
				),
				$atts
			)
		);
		$button_id = $sku ? $sku : false;
		if ( ! $button_id ) {
			return ''; // if button id is not present in the shortcode
		}

		$subscriptions = $this->subscriptions;

		$subscription = isset( $subscriptions[ $button_id ] ) ? $subscriptions[ $button_id ] : false;
		if ( ! $subscription ) {
			return ''; // if id is not valid
		}

		// the real sku
		$sku = isset( $subscription['sku'] ) ? $subscription['sku'] : false;
		if ( ! $sku ) {
			return ''; // invalid level
		}

		global $current_user;

		// load the js files
		$this->load_js();

		$wpm_levels   = $this->wlm->get_option( 'wpm_levels' );
		$formsettings = $this->formsettings;
		$formsettings = is_array( $formsettings ) ? $formsettings : array();

		$anetarbthankyou     = $this->wlm->get_option( 'anetarbthankyou' );
		$anetarbthankyou_url = $this->wlm->make_thankyou_url( $anetarbthankyou );

		$frequency    = isset( $subscription['recur_billing_frequency'] ) ? (int) $subscription['recur_billing_frequency'] : '';
		$period       = isset( $subscription['recur_billing_period'] ) ? $subscription['recur_billing_period'] : '';
		$recur_cycle  = isset( $subscription['recur_billing_cycle'] ) ? (int) $subscription['recur_billing_cycle'] : 0;
		$trial_cycle  = isset( $subscription['trial_billing_cycle'] ) ? (int) $subscription['trial_billing_cycle'] : 0;
		$trial_amount = isset( $subscription['trial_amount'] ) ? (float) $subscription['trial_amount'] : 0;
		$total_cycle  = $recur_cycle + $trial_cycle;

		$amount     = $subscription['recurring'] ? (float) $subscription['recur_amount'] : (float) $subscription['amount'];
		$currency   = $subscription['currency'] ? $subscription['currency'] : '';
		$level_name = $wpm_levels[ $sku ]['name'];

		$supportemail    = isset( $formsettings['supportemail'] ) && ! empty( $formsettings['supportemail'] ) ? $formsettings['supportemail'] : '';
		$logo            = isset( $formsettings['logo'] ) && ! empty( $formsettings['logo'] ) ? $formsettings['logo'] : false;
		$display_address = isset( $formsettings['display_address'] ) && ! empty( $formsettings['display_address'] ) ? true : false;

		if ( $subscription['recurring'] ) {
			$formheading     = isset( $formsettings['formheadingrecur'] ) && ! empty( $formsettings['formheadingrecur'] ) ? $formsettings['formheadingrecur'] : false;
			$formbuttonlabel = isset( $formsettings['formbuttonlabelrecur'] ) && ! empty( $formsettings['formbuttonlabelrecur'] ) ? $formsettings['formbuttonlabelrecur'] : false;
			$beforetext      = isset( $formsettings['beforetextrecur'] ) && ! empty( $formsettings['beforetextrecur'] ) ? $formsettings['beforetextrecur'] : false;
			$aftertext       = isset( $formsettings['aftertextrecur'] ) && ! empty( $formsettings['aftertextrecur'] ) ? $formsettings['aftertextrecur'] : false;
		} else {
			$formheading     = isset( $formsettings['formheading'] ) && ! empty( $formsettings['formheading'] ) ? $formsettings['formheading'] : false;
			$formbuttonlabel = isset( $formsettings['formbuttonlabel'] ) && ! empty( $formsettings['formbuttonlabel'] ) ? $formsettings['formbuttonlabel'] : false;
			$beforetext      = isset( $formsettings['beforetext'] ) && ! empty( $formsettings['beforetext'] ) ? $formsettings['beforetext'] : false;
			$aftertext       = isset( $formsettings['aftertext'] ) && ! empty( $formsettings['aftertext'] ) ? $formsettings['aftertext'] : false;
		}

		$card_types                   = array(
			'Visa'        => 'Visa',
			'MasterCard'  => 'MasterCard',
			'Discover'    => 'Discover',
			'Amex'        => 'American Express',
			'Diners Club' => 'Diners Club',
			'JCB'         => 'JCB',
		);
		$formsettings['credit_cards'] = isset( $formsettings['credit_cards'] ) ? $formsettings['credit_cards'] : array( 'Visa', 'MasterCard', 'Discover', 'Amex' );
		$formsettings['credit_cards'] = count( $formsettings['credit_cards'] ) > 0 ? $formsettings['credit_cards'] : array( 'Visa', 'MasterCard', 'Discover', 'Amex' );
		foreach ( $card_types as $key => $value ) {
			if ( ! in_array( $key, $formsettings['credit_cards'] ) ) {
				unset( $card_types[ $key ] );
			}
		}

		// prepare codes value
		$codes = array(
			'level'        => $level_name,
			'amount'       => $amount,
			'frequency'    => $frequency ? $frequency : 0,
			'period'       => $period,
			'cycle'        => $recur_cycle ? $recur_cycle : 'Unlimited',
			'trial_cycle'  => $trial_cycle ? $trial_cycle : '',
			'trial_amount' => $trial_cycle ? ( $trial_amount ? $trial_amount : 0 ) : '',
			'total_cycle'  => $recur_cycle ? $total_cycle : 'Unlimited',
			'currency'     => $currency,
			'supportemail' => $supportemail,
		);

		// prepare form data
		include $this->wlm->plugindir . '/extlib/wlm_authorizenet_arb/form_new_field.php';

		$this->forms[ $button_id ] = wlm_build_payment_form( $data );

		return sprintf( '<a id="go-regform-%s" class="go-regform" href="#regform-%s">%s %s</a>', $button_id, $button_id, $link_text, $content );
	}

	public function process_form_codes( $str, $codes = array() ) {
		foreach ( $codes as $code => $value ) {
			$str = str_replace( "%{$code}", $value, $str );
		}
		return $str;
	}

	public function footer() {
		if ( isset( $this->forms ) && ! empty( $this->forms ) && is_array( $this->forms ) ) {
			$js = '';
			foreach ( (array) $this->forms as $sku => $f ) {
				$js .= " $('#regform-{$sku} .regform-form').PopupRegForm();\n";
				fwrite( WLM_STDOUT, $f );
			}
			$js = "\n jQuery(function($) { \n{$js} });\n";
			$js = "\n<script type='text/javascript'> {$js} </script>\n";
			fwrite( WLM_STDOUT, $js );
		}
	}

	/**
	 * Add integration shortcodes via wishlistmember_integration_shortcodes filter
	 *
	 * @param  array $shortcodes
	 * @return array
	 */
	public function anet_arb_shortcode_btns( $shortcodes ) {
		if ( ! is_array( $shortcodes ) ) {
			return $shortcodes;
		}

		$wlm_shortcodes = array();

		foreach ( $this->subscriptions as $id => $p ) {
			if ( $p['recurring'] ) {
				$buy_now_str = __( 'Subscribe Now', 'wishlist-member' );
				$period      = ucfirst( $p['recur_billing_period'] );
				$freq        = $p['recur_billing_frequency'];
				if ( 1 !== (int) $freq ) {
					$period = $freq . ' ' . $period . 's';
				}

				$currency = strtoupper( $p['currency'] );
				$desc     = sprintf( '%s %s every %s', $currency, number_format( $p['recur_amount'], 2 ), $period );
				if ( $p['trial_amount'] && $p['trial_billing_cycle'] ) {
					$desc .= sprintf( ' with %s %s Trial', $currency, number_format( $p['trial_amount'], 2 ) );
					if ( $p['trial_billing_cycle'] > 1 ) {
						$desc .= sprintf( ' for %d cycles', $p['trial_billing_cycle'] );
					}
				}

				$title          = sprintf( '%s (%s)', $p['name'], $desc );
				$wlm_shortcodes = array( sprintf( 'wlm_authorizenet_arb_btn sku=%s link_text="%s"', $id, htmlentities( $buy_now_str ) ) => array( 'label' => $title ) ) + $wlm_shortcodes;
			} else {
				$buy_now_str    = __( 'Buy Now', 'wishlist-member' );
				$title          = sprintf( '%s (%s %s)', $p['name'], strtoupper( $p['currency'] ), number_format( $p['amount'], 2 ) );
				$wlm_shortcodes = $wlm_shortcodes + array( sprintf( 'wlm_authorizenet_arb_btn sku=%s link_text="%s"', $id, htmlentities( $buy_now_str ) ) => array( 'label' => $title ) );
			}
		}

		if ( $wlm_shortcodes ) {
			$shortcodes['Authorize.Net (ARB) Integration'] = $wlm_shortcodes;
		}

		return $shortcodes;
	}

	public function syn_arb() {
		// allow sync at least every 1 hour only
		$logs = $this->wlm->get_option( 'auhtnetarb_sync_log' );
		if ( $logs && is_array( $logs ) ) {
			$previous = isset( $logs['start'] ) ? $logs['start'] : '';
			$previous = strtotime( $previous );
			$now      = time();
			$diff     = $now - $previous;
			$day      = 60 * 60;
			if ( $diff < $day ) {
				$msg = 'Cannot sync now. ' . ( $day - $diff ) . ' second/s left';
				return array(
					'end'     => wlm_date( 'Y-m-d H:i:s' ),
					'message' => $msg,
					'count'   => 0,
				);
			}
		}

		wlm_set_time_limit( 0 ); // override max execution time
		if ( ! class_exists( 'AuthnetARB' ) ) {
			include_once $this->wlm->plugindir . '/extlib/wlm_authorizenet_arb/authnet_arb.php';
		}
		$counter = 0;
		$message = '';
		$login   = isset( $this->apisettings['api_login_id'] ) ? $this->apisettings['api_login_id'] : '';
		$key     = isset( $this->apisettings['api_transaction_key'] ) ? $this->apisettings['api_transaction_key'] : '';
		$sandbox = isset( $this->apisettings['sandbox_mode'] ) ? $this->apisettings['sandbox_mode'] : 0;
		$sandbox = $sandbox ? true : false;

		$txnids = $this->wlm->get_option( 'auhtnetarb_transaction_ids' );

		if ( empty( $txnids ) || ! $txnids || ! is_array( $txnids ) ) {
			$txnids = $this->get_arb_txnids();
			$this->wlm->save_option( 'auhtnetarb_transaction_ids', $txnids );
		}

		// initial the log since it was called
		$sync_start = wlm_date( 'Y-m-d H:i:s' );
		$log        = array(
			'count'   => 0,
			'message' => 'ARB Sync started.',
			'start'   => $sync_start,
			'end'     => '',
		);
		$this->wlm->save_option( 'auhtnetarb_sync_log', $log );

		try {

			$this->arb = new WLMAuthnet\AuthnetARB( $login, $key, $sandbox );
			foreach ( $txnids as $key => $txnid ) {
				list( $market, $subid ) = explode( '-', $txnid, 2 );

				$stat = $this->get_subscription_status( $subid );

				wlm_post_data()['sctxnid'] = 'arb-' . $subid;
				switch ( $stat ) {
					case 'active':
					case 'expired':
						$this->wlm->shopping_cart_reactivate();
						break;
					case 'suspended':
					case 'canceled':
					case 'terminated':
						$this->wlm->shopping_cart_deactivate();
						break;
					default:
						  // we do nothing, it might be an error when doing api call
						break;
				}
				unset( $txnids[ $key ] );
				$this->wlm->save_option( 'auhtnetarb_transaction_ids', $txnids );
				$counter++;
			}
			$message = 'Synced successfully.';
		} catch ( Exception $e ) {
			$message = $e->getMessage();
		}

		// update the log
		$log = array(
			'count'   => $counter,
			'message' => $message,
			'start'   => $sync_start,
			'end'     => wlm_date( 'Y-m-d H:i:s' ),
		);
		$this->wlm->save_option( 'auhtnetarb_sync_log', $log );

		return $log;
	}

	private function get_subscription_status( $subid ) {
		$status = false;
		if ( ! $this->arb ) {
			return false;
		}
		try {
			$this->arb->do_apicall( 'ARBGetSubscriptionStatusRequest', array( 'subscriptionId' => $subid ) );
			if ( $this->arb->isSuccessful() ) {
				$status = strtolower( $this->arb->getStatus() );
			}
		} catch ( Exception $e ) {
			$status = 'invalid';
		}
		return $status;
	}

	private function get_arb_txnids() {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare( 'SELECT uo.`option_value` as option_value  FROM `' . esc_sql( $this->wlm->table_names->userlevel_options ) . '` AS uo LEFT JOIN `' . esc_sql( $this->wlm->table_names->userlevels ) . '` AS ul ON uo.`userlevel_id` = ul.`ID` WHERE uo.`option_value` LIKE %s', 'arb-%' ) );
	}
}

$wlm_aurthorizenet_arb_init = new WLMAuthorizeNetARB();
$wlm_aurthorizenet_arb_init->init();
