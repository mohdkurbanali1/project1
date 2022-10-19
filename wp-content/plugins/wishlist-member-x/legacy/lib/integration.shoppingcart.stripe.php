<?php
/*
 * Stripe Shopping Cart Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.shoppingcart.stripe.php 8248 2022-03-22 14:49:27Z mike $
 */

// $__classname__ = 'WLM_INTEGRATION_STRIPE';
// $__optionname__ = 'stripethankyou';
// $__methodname__ = 'stripe';

if ( ! class_exists( 'WLM_INTEGRATION_STRIPE' ) ) {

	class WLM_INTEGRATION_STRIPE {
		public $wlm;
		public function __construct() {
			$sc = new WLM_Stripe_ShortCodes();
			add_action( 'admin_notices', array( $this, 'notices' ) );
		}
		public function stripe( $wlm ) {
			$this->wlm     = $wlm;
			$action        = trim( strtolower( wlm_request_data()['stripe_action'] ) );
			$valid_actions = array( 'charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice', 'migrate', 'check_coupon', 'get_coupon', 'sca_redirect' );
			if ( ! in_array( $action, $valid_actions ) ) {
				esc_html_e( 'Permission Denied', 'wishlist-member' );
				die();
			}
			if ( ( 'sync' !== $action && 'migrate' !== $action ) && ! wp_verify_nonce( wlm_request_data()['nonce'], "stripe-do-$action" ) ) {
				esc_html_e( 'Permission Denied', 'wishlist-member' );
				die();
			}
			switch ( $action ) {
				case 'migrate':
					$this->migrate();
					break;
				case 'charge':
					// code...
					$this->charge( wlm_post_data( true ) );
					break;
				case 'sync':
					$this->sync( wlm_post_data( true ) );
					break;
				case 'update_payment':
					$this->update_payment( wlm_post_data( true ) );
					break;
				case 'cancel':
					$this->cancel( wlm_post_data( true ) );
					break;
				case 'invoices':
					$this->invoices( wlm_post_data( true ) );
					break;
				case 'invoice':
					$this->invoice( wlm_post_data( true ) );
					break;
				case 'check_coupon':
					$this->check_coupon( wlm_post_data( true ) );
					break;
				case 'get_coupon':
					$this->get_coupon( wlm_post_data( true ) );
					break;
				case 'sca_redirect':
					$this->handle_sca_redirect( $_REQUEST );
					break;
				default:
					// code...
					break;
			}
		}
		public function promocode_to_pid( $promocode_name ) {
			$list_promotion_codes = WLMStripe\PromotionCode::all( array( 'code' => $promocode_name ) );
			if ( count( $list_promotion_codes->data ) ) {
				return $list_promotion_codes->data[0]->id;
			}
		}
		public function coupon_to_cid( $coupon_name ) {
			$list_coupons = WLMStripe\Coupon::all();
			foreach ( $list_coupons as $coupon_code ) {
				if ( $coupon_name == $coupon_code->name ) {
						return $coupon_code->id;
				}
			}

		}
		public function check_coupon( $data = array() ) {
			$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			try {
				$promo_code_id = $this->promocode_to_pid( $data['coupon'] );
				if ( ! $promo_code_id ) {
					$coupon_id = $this->coupon_to_cid( $data['coupon'] );
				}
				if ( $coupon_id ) {
					$coupon = WLMStripe\Coupon::retrieve( $coupon_id );
					if ( 1 == $coupon->valid ) {
						echo json_encode( true );
					} else {
						echo json_encode( false );
					}
				} elseif ( $promo_code_id ) {
					$coupon = WLMStripe\PromotionCode::retrieve( $promo_code_id );
					if ( 1 == $coupon['coupon']->valid ) {
						echo json_encode( true );
					} else {
						echo json_encode( false );
					}
				} else {
					$coupon = WLMStripe\Coupon::retrieve( $data['coupon'] );
					if ( 1 == $coupon->valid ) {
						echo json_encode( true );
					} else {
						echo json_encode( false );
					}
				}
			} catch ( Exception $e ) {
				echo json_encode( false );
			}

			die();
		}
		public function get_coupon( $data = array() ) {
			$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			try {
				$promo_code_id = $this->promocode_to_pid( $data['coupon'] );
				if ( ! $promo_code_id ) {
					$coupon_id = $this->coupon_to_cid( $data['coupon'] );
				}
				if ( $coupon_id ) {
					$coupon  = WLMStripe\Coupon::retrieve( $coupon_id );
					$coupons = array(
						'c_type'   => $coupon->amount_off ? 'amount_off' : 'percent_off',
						'c_amount' => $coupon->amount_off ? $coupon->amount_off : $coupon->percent_off,
					);
					echo json_encode( $coupons );
				} elseif ( $promo_code_id ) {
					$coupon  = WLMStripe\PromotionCode::retrieve( $promo_code_id );
					$coupons = array(
						'c_type'   => $coupon['coupon']->amount_off ? 'amount_off' : 'percent_off',
						'c_amount' => $coupon['coupon']->amount_off ? $coupon['coupon']->amount_off : $coupon['coupon']->percent_off,
					);
					echo json_encode( $coupons );
				} else {
					$coupon  = WLMStripe\Coupon::retrieve( $data['coupon'] );
					$coupons = array(
						'c_type'   => $coupon->amount_off ? 'amount_off' : 'percent_off',
						'c_amount' => $coupon->amount_off ? $coupon->amount_off : $coupon->percent_off,
					);
					echo json_encode( $coupons );
				}
			} catch ( Exception $e ) {
				echo json_encode( '' );
			}

			die();
		}
		public function migrate() {
			$users = get_users();
			echo sprintf( "migrating %s stripe users<br/>\n", count( $users ) );

			$live = wlm_get_data()['live'];
			foreach ( $users as $u ) {
				$cust_id = $this->wlm->Get_UserMeta( $u->ID, 'custom_stripe_cust_id' );

				printf( 'migrating user %s with stripe_cust_id: <br/>', esc_html( $u->ID ), esc_html( $cust_id ) );
				if ( $live || ! empty( $cust_id ) ) {
					$this->wlm->Update_UserMeta( $u->ID, 'stripe_cust_id', $cust_id );
				}
			}
		}
		public function cancel( $data = array() ) {
			global $current_user;
			if ( empty( $current_user->ID ) ) {
				return;
			}
			$stripeapikey   = $this->wlm->get_option( 'stripeapikey' );
			$stripe_cust_id = $this->wlm->Get_UserMeta( $current_user->ID, 'stripe_cust_id' );
			$stripesettings = $this->wlm->get_option( 'stripesettings' );
			$connections    = $this->wlm->get_option( 'stripeconnections' );
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			/*
			 * Use Customer ID from transaction ID if it's different from stripe_cust_id
			 * or if stripe_cust_id is empty but the txn is still connected to a plan.
			 */
			if ( ! empty( $data['txn_id'] ) ) {
				list( $c_id, $plan_id ) = explode( '-', $data['txn_id'], 2 );
				if ( $stripe_cust_id != $c_id || empty( $stripe_cust_id ) ) {
					$stripe_cust_id = $c_id;
				}
			}

			try {
				// also handle onetime payments
				// $this->wlm->shopping_cart_deactivate();
				$stripe_level_settings = $connections[ wlm_post_data()['wlm_level'] ];
				if ( ! empty( $stripe_level_settings['subscription'] ) ) {
					$cust = WLMStripe\Customer::retrieve( $stripe_cust_id );
					if ( ! $cust->subscriptions ) {
						$cust = WLMStripe\Customer::retrieve(
							array(
								'id'     => $stripe_cust_id,
								'expand' => array( 'subscriptions' ),
							)
						);
					}
					$at_period_end = false;
					if ( ! empty( $stripesettings['endsubscriptiontiming'] ) && 'periodend' == $stripesettings['endsubscriptiontiming'] ) {
						$at_period_end = true;
					}
					// Check if customer has more than 1 subscription, if so then get the
					// subscription ID and only cancel the subscription that matches the STRIPE PLAN
					// passed in the $_POST data
					if ( count( $cust->subscriptions->data ) > 1 ) {
						list($c_id, $plan_id) = explode( '-', $data['txn_id'] );
						foreach ( $cust->subscriptions->data as $d ) {
							if ( $d->plan->id == $plan_id ) {
								$sub_id = $d->id;

								if ( $at_period_end ) {
									$update = WLMStripe\Subscription::update(
										$sub_id,
										array(
											'cancel_at_period_end' => $at_period_end,
										)
									);
								} else {
									$subscription = WLMStripe\Subscription::retrieve( $sub_id );
									$subscription->cancel();
								}
							}
						}
					} else {
						if ( $at_period_end ) {
							$sub_id = $cust->subscriptions->data[0]->id;
							$update = WLMStripe\Subscription::update(
								$sub_id,
								array(
									'cancel_at_period_end' => $at_period_end,
								)
							);
						} else {
							$sub_id = $cust->subscriptions->data[0]->id;

							$subscription = WLMStripe\Subscription::retrieve( $sub_id );
							$subscription->cancel();
						}
					}
				} else {
					wlm_post_data()['sctxnid'] = wlm_request_data()['txn_id'];
					$this->wlm->shopping_cart_deactivate();
				}
				$status = 'ok';
			} catch ( Exception $e ) {
				$status = 'fail&err=' . $e->getMessage();
			}
			$uri = $data['redirect_to'];
			if ( ! empty( $stripesettings['cancelredirect'] ) ) {
				$uri = get_permalink( $stripesettings['cancelredirect'] );
			}
			if ( false !== stripos( $uri, '?' ) ) {
				$uri .= "&status=$status";
			} else {
				$uri .= "?&status=$status";
			}
			wp_redirect( $uri );
			die();
		}

		public function update_payment( $data = array() ) {
			$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			try {
				global $current_user;
				if ( empty( $current_user->ID ) ) {
					throw new Exception( __( 'An error occured while processing the request, Please try again', 'wishlist-member' ) );
				}
				$cust_id = $this->wlm->Get_UserMeta( $current_user->ID, 'stripe_cust_id' );
				if ( empty( $cust_id ) ) {
					// user is a member but not linked
					// try to create this user in stripe
					$cust_details = array(
						'name'        => sprintf( '%s %s', $current_user->first_name, $current_user->last_name ),
						'description' => sprintf( '%s %s', $current_user->first_name, $current_user->last_name ),
						'email'       => $current_user->user_email,
					);
					$cust         = WLMStripe\Customer::create( $cust_details );

					$payment_method = WLMStripe\PaymentMethod::create(
						array(
							'type' => 'card',
							'card' => array(
								'token' => $data['stripeToken'],
							),
						)
					);

					$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
					$payment_method->attach( array( 'customer' => $cust->id ) );

					$cust->invoice_settings->default_payment_method = $payment_method->id;
					$cust->save();

					$this->wlm->Update_UserMeta( $current_user->ID, 'stripe_cust_id', $cust->id );
				} else {
					$cust = WLMStripe\Customer::retrieve( $cust_id );

					$payment_method = WLMStripe\PaymentMethod::create(
						array(
							'type' => 'card',
							'card' => array(
								'token' => $data['stripeToken'],
							),
						)
					);

					$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
					$payment_method->attach( array( 'customer' => $cust->id ) );

					$cust->invoice_settings->default_payment_method = $payment_method->id;
					$cust->save();
				}
				$status = 'ok';
			} catch ( Exception $e ) {
				$err    = preg_replace( '/\s+/', '+', $e->getMessage() );
				$status = 'fail&err=' . $err;
			}

			$uri = $data['redirect_to'];
			if ( false !== stripos( $uri, '?' ) ) {
				$uri .= "&status=$status";
			} else {
				$uri .= "?&status=$status";
			}
			wp_redirect( $uri );
			die();
		}

		public function sync( $data = array() ) {
			$this->wlm->schedule_sync_membership();
			$obj    = json_decode( file_get_contents( 'php://input' ) );
			$id     = null;
			$action = null;
			WLMStripe\WLM_Stripe::setApiKey( $this->wlm->get_option( 'stripeapikey' ) );

			// If $obj is empty then just return, otherwise it will show errors when viewed in browser
			if ( empty( $obj ) ) {
				die( "\n" );
			}

			// Means this came from a test web hook URL
			// Skip sync process to avoid 500 internal server error as
			// the Sync process will throw errors
			if ( 'evt_00000000000000' == $obj->id ) {
				die( "\n" );
			}

			// Request for the stripe event object to
			// make sure this is a legit stripe notification
			$obj = WLMStripe\Event::retrieve( $obj->id );

			switch ( $obj->type ) {
				// do not handler creates anymore
				// case 'customer.subscription.created':
				// $cust_id = $obj->data->object->customer;
				// $plan_id = $obj->data->object->plan->id;
				// $id = $cust_id . "-" . $plan_id;
				// $action = 'move';
				// break;
				case 'customer.subscription.deleted':
					$cust_id = $obj->data->object->customer;
					$plan_id = $obj->data->object->plan->id;
					$id      = $cust_id . '-' . $plan_id;
					$action  = 'deactivate';
					break;

				case 'customer.subscription.created':
				case 'customer.subscription.updated':
					$cust_id = $obj->data->object->customer;
					$plan_id = $obj->data->object->plan->id;
					$id      = $cust_id . '-' . $plan_id;

					switch ( $obj->data->object->status ) {
						case 'trialing':
						case 'past_due':
							$action = 'reactivate';
							break;
						case 'active':
							$action = 'reactivate';
							if ( ! empty( $obj->data->previous_attributes->plan->id ) ) {
								// we are changing subscriptions
								$prev_id = sprintf( '%s-%s', $cust_id, $obj->data->previous_attributes->plan->id );
								$action  = 'move';
							}
							break;
						case 'unpaid':
						case 'cancelled':
						default:
							$action = 'deactivate';
							break;
					}

					// This is an active subscription
					// reactivate? No need
					break;
				case 'invoice.payment_failed':
					// no need, we'll also be able to catch this under charge_failed
					break;

				case 'customer.deleted':
					$cust_id = $obj->data->object->id;
					$user_id = $this->wlm->Get_UserID_From_UserMeta( 'stripe_cust_id', $cust_id );
					$levels  = $this->wlm->get_membership_levels( $user_id, null, true, null, true );
					if ( empty( $levels ) ) {
						die( "\n" );
					}
					$id     = $this->wlm->get_membership_levels_txn_id( $user_id, $levels[0] );
					$action = 'deactivate';
					break;
				case 'charge.refunded':
					$id     = $obj->data->object->id;
					$action = 'deactivate';
					break;
				case 'charge.failed':
					// no need to handle as failed charges are handled
					// in the merchant site
					// $cust_id = $obj->data->object->customer;
					// $customer = WLMStripe\Customer::retrieve($cust_id);
					// if (empty($customer->plan)) {
					// return;
					// }
					// $id = sprintf("%s-%s", $cust_id, $customer->plan->id);
					// $action = 'deactivate';
					//
					break;
			}

			wlm_post_data()['sctxnid'] = $id;
			switch ( $action ) {
				case 'deactivate':
					printf( 'info(deact): deactivating subscription: %s', esc_html( $id ) );
					wlm_post_data()['sctxnid'] = $id;
					$this->wlm->shopping_cart_deactivate();
					break;
				case 'reactivate':
					printf( 'info(react): reactivating subscription: %s', esc_html( $id ) );
					wlm_post_data()['sctxnid'] = $id;

					wlm_post_data()['sc_type'] = 'Stripe';
					do_action_deprecated( 'wlm_shoppingcart_rebill', array( wlm_post_data( true ) ), '3.10', 'wishlistmember_shoppingcart_rebill' );
					do_action( 'wishlistmember_shoppingcart_rebill', wlm_post_data( true ) );

					$this->wlm->shopping_cart_reactivate();

					break;
				case 'move':
					// activate the new one
					$connections = $this->wlm->get_option( 'stripeconnections' );

					// get the correct level
					$wpm_level      = $this->stripe_plan_id_to_sku( $connections, $obj->data->object->plan->id );
					$prev_wpm_level = $this->stripe_plan_id_to_sku( $connections, $obj->data->previous_attributes->plan->id );

					// get the correct user
					$user_id = $this->wlm->Get_UserID_From_UserMeta( 'stripe_cust_id', $cust_id );

					if ( ! empty( $wpm_level ) && ! empty( $user_id ) ) {
						// remove from previous level
						$current_levels = $this->wlm->get_membership_levels( $user_id, null, null, true );
						$levels         = array_diff( $current_levels, array( $prev_wpm_level ) );
						printf( 'removing from %s', esc_html( $prev_wpm_level ) );
						$this->wlm->set_membership_levels( $user_id, $levels );

						printf( 'info(move): moving user:%s to new subscription:%s with tid:%s', esc_html( $user_id ), esc_html( $wpm_level ), esc_html( $id ) );
						$this->add_to_level( $user_id, $wpm_level, $id );
					}
					break;
			}
			die( "\n" );
		}
		public function stripe_plan_id_to_sku( $connections, $id ) {
			foreach ( $connections as $c ) {
				if ( $c['plan'] == $id ) {
					return $c['sku'];
				}
			}
		}
		public function add_to_level( $user_id, $level_id, $txn_id ) {
			$user = new \WishListMember\User( $user_id );

			 $wpm_levels = $this->wlm->get_option( 'wpm_levels' );

			 $this->wlm->set_membership_levels( $user_id, array( $level_id ), array( 'keep_existing_levels' => true ) );

			// Send email notifications
			if ( 1 == $wpm_levels[ $level_id ]['newuser_notification_user'] ) {
				$email_macros                    = array(
					'[password]'    => '********',
					'[memberlevel]' => $wpm_levels[ $level_id ]['name'],
				);
				$this->wlm->email_template_level = $level_id;
				$this->wlm->send_email_template( 'registration', $user_id, $email_macros );
			}

			if ( 1 == $wpm_levels[ $level_id ]['newuser_notification_admin'] ) {
				$this->wlm->email_template_level = $level_id;
				$this->wlm->send_email_template( 'admin_new_member_notice', $user_id, $email_macros, $this->wlm->get_option( 'email_sender_address' ) );
			}

			if ( isset( $wpm_levels[ $level_id ]['registrationdatereset'] ) ) {
				$timestamp = time();
				$this->wlm->user_level_timestamp( $user_id, $level_id, $timestamp );
			}

			if ( $this->wlm->is_ppp_level( $level_id ) ) {
				list($tmp, $content_id) = explode( '-', $level_id );
				$this->wlm->add_user_post_transaction_id( $user_id, $content_id, $txn_id );

				if ( empty( $timestamp ) ) {
					$timestamp = time();
				}

				$this->wlm->add_user_post_timestamp( $user_id, $content_id, $timestamp );
			} else {
				$this->wlm->set_membership_level_txn_id( $user_id, $level_id, $txn_id );
			}
		}
		public function charge_existing( $data ) {

			global $WishListMemberInstance;
			$this->wlm = $WishListMemberInstance;

			$connections    = $this->wlm->get_option( 'stripeconnections' );
			$stripesettings = $this->wlm->get_option( 'stripesettings' );
			$stripe_plan    = $connections[ $data['wpm_id'] ]['plan'];
			$settings       = $connections[ $data['wpm_id'] ];

			WLMStripe\WLM_Stripe::setApiVersion( '2019-08-14' );

			try {

				global $current_user;
				$stripe_cust_id = $this->wlm->Get_UserMeta( $current_user->ID, 'stripe_cust_id' );

				if ( $data['subscription'] ) {

					// since 3.6 change the plan to customer-selected plan if there is one
					$stripe_plan = $this->choose_plan( $stripe_plan, $connections, $data );

					if ( ! empty( $stripe_cust_id ) && 'wlm_stripe_existing_card' == wlm_post_data()['wlm_stripe_radio'] ) {
						$cust = WLMStripe\Customer::retrieve( $stripe_cust_id );

						$stripe_cust_payment_method_id = $cust->invoice_settings->default_payment_method;

						// If customer has Stripe Customer ID but doesn't have a payment Method ID (they might have bought using // token before) then create a payment method ID using the new card they used on purchase and attach it.
						if ( empty( $stripe_cust_payment_method_id ) ) {
							$payment_method = WLMStripe\PaymentMethod::create(
								array(
									'type' => 'card',
									'card' => array(
										'token' => $data['stripeToken'],
									),
								)
							);
						} else {
							$payment_method = WLMStripe\PaymentMethod::retrieve( $stripe_cust_payment_method_id );
						}

						$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
						$payment_method->attach( array( 'customer' => $cust->id ) );

						$cust->invoice_settings->default_payment_method = $payment_method->id;
						$cust->save();

						if ( empty( $payment_method->id ) ) {
							throw new Exception( 'Could not verify credit card information' );
						}
					} else {
						if ( empty( $data['stripeToken'] ) ) {
							throw new Exception( 'Could not verify credit card information' );
						}
						$cust_details = array(
							'name'        => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'description' => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'email'       => $data['email'],
						);
						$cust         = WLMStripe\Customer::create( $cust_details );

						$payment_method = WLMStripe\PaymentMethod::create(
							array(
								'type' => 'card',
								'card' => array(
									'token' => $data['stripeToken'],
								),
							)
						);

						$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
						$payment_method->attach( array( 'customer' => $cust->id ) );

						$cust->invoice_settings->default_payment_method = $payment_method->id;
						$cust->save();

						$this->wlm->Update_UserMeta( $current_user->ID, 'stripe_cust_id', $cust->id );
					}

					$prorate = true;
					if ( ! empty( $stripesettings['prorate'] ) && 'no' == $stripesettings['prorate'] ) {
						$prorate = false;
					}

					$automatic_tax = false;
					if ( ! empty( $stripesettings['automatictax'] ) && 'yes' == $stripesettings['automatictax'] ) {
						$automatic_tax = true;
					}

					if ( ! empty( $stripe_plan ) ) {
						foreach ( $cust->subscriptions->data as $sub ) {
							if ( $sub->plan->id == $stripe_plan ) {
								throw new Exception( __( 'Cannot purchase an active plan', 'wishlist-member' ) );
							}
						}
					}

					if ( empty( $data['coupon'] ) ) {
						unset( $params['coupon'] );
					}

					$txn_id = $this->charge_plan( $stripe_plan, $cust, $prorate, $automatic_tax, $data, 'charge_existing', $current_user->ID );

				} else {

					if ( ! empty( $stripe_cust_id && 'wlm_stripe_existing_card' == wlm_post_data()['wlm_stripe_radio'] ) ) {
						$cust = WLMStripe\Customer::retrieve( $stripe_cust_id );

						$stripe_cust_payment_method_id = $cust->invoice_settings->default_payment_method;

						// If customer has Stripe Customer ID but doesn't have a payment Method ID (they might have bought using // token before) then create a payment method ID using the new card they used on purchase and attach it.
						if ( empty( $stripe_cust_payment_method_id ) ) {
							$payment_method = WLMStripe\PaymentMethod::create(
								array(
									'type' => 'card',
									'card' => array(
										'token' => $data['stripeToken'],
									),
								)
							);
						} else {
							$payment_method = WLMStripe\PaymentMethod::retrieve( $stripe_cust_payment_method_id );
						}

						$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
						$payment_method->attach( array( 'customer' => $cust->id ) );

						$cust->invoice_settings->default_payment_method = $payment_method->id;
						$cust->save();

						if ( empty( $payment_method->id ) ) {
							throw new Exception( 'Could not verify credit card information' );
						}
					} else {
						// Create USER
						$cust = WLMStripe\Customer::create(
							array(
								'name'        => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
								'description' => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
								'email'       => $data['email'],
							)
						);

						$this->wlm->Update_UserMeta( $current_user->ID, 'stripe_cust_id', $cust->id );

						// Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then
						// attach it to customer for future charges. This is a bit different from Stripe tokens.
						$payment_method = WLMStripe\PaymentMethod::create(
							array(
								'type' => 'card',
								'card' => array(
									'token' => $data['stripeToken'],
								),
							)
						);
					}

					$currency = empty( $stripesettings['currency'] ) ? 'USD' : $stripesettings['currency'];

					// override amount and currency if set in shortcode
					$currency = isset( $data['stripe_currency'] ) ? strtoupper( $data['stripe_currency'] ) : $currency;
					$amt      = isset( $data['stripe_amount'] ) ? (float) $data['stripe_amount'] : $settings['amount'];
					$amt      = number_format( $amt * 100, 0, '.', '' );

					$level      = wlmapi_get_level( $data['wpm_id'] );
					$level_name = $level['level']['name'];

					if ( empty( $level_name ) ) {
						$ppp_level  = $WishListMemberInstance->is_ppp_level( $data['sku'] );
						$level_name = $ppp_level->post_title;
					}

					// Create the PaymentIntent
					$intent = WLMStripe\PaymentIntent::create(
						array(
							'customer'            => $cust->id,
							'payment_method'      => $payment_method->id,
							'amount'              => $amt,
							'currency'            => $currency,
							'confirmation_method' => 'automatic',
							'confirm'             => true,
							'description'         => sprintf( '%s - One Time Payment', $level_name ),
						)
					);

					$txn_id = $intent->charges->data[0]->id;

					// If payment requires AUTH then let process_sca_auth handle it.
					if ( 'requires_action' === $intent->status && 'use_stripe_sdk' == $intent->next_action->type ) {
						$this->process_sca_auth( $data, $intent->client_secret, $cust->id, '', $intent->id, 'charge_existing', $current_user->ID );
					}
				}

				// add user to level and redirect to the after reg url
				$this->add_to_level( $current_user->ID, $data['sku'], $txn_id );
				$url = $this->wlm->get_after_reg_redirect( $data['sku'] );
				wp_redirect( $url );
				die();
			} catch ( Exception $e ) {
				$this->fail(
					array(
						'msg' => $e->getMessage(),
						'sku' => $data['wpm_id'],
					)
				);
			}
		}
		public function charge_new( $data ) {
			global $WishListMemberInstance;
			$this->wlm = $WishListMemberInstance;

			$connections    = $this->wlm->get_option( 'stripeconnections' );
			$stripesettings = $this->wlm->get_option( 'stripesettings' );
			$stripe_plan    = $connections[ $data['wpm_id'] ]['plan'];
			$settings       = $connections[ $data['wpm_id'] ];
			WLMStripe\WLM_Stripe::setApiVersion( '2019-08-14' );

			try {
				if ( $data['subscription'] ) {

					// since 3.6 change the plan to customer-selected plan if there is one
					$stripe_plan = $this->choose_plan( $stripe_plan, $connections, $data );

					// Create USER
					$cust = WLMStripe\Customer::create(
						array(
							'name'        => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'description' => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'email'       => $data['email'],
						)
					);

					$prorate = true;
					if ( ! empty( $stripesettings['prorate'] ) && 'no' == $stripesettings['prorate'] ) {
						$prorate = false;
					}

					$automatic_tax = false;
					if ( ! empty( $stripesettings['automatictax'] ) && 'yes' == $stripesettings['automatictax'] ) {
						$automatic_tax = true;
					}

					if ( empty( $data['coupon'] ) ) {
						unset( $params['coupon'] );
						unset( $params['promotion_code'] );
					}

					// Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then
					// attach it to customer for future charges. This is a bit different from Stripe tokens.
					$payment_method = WLMStripe\PaymentMethod::create(
						array(
							'type' => 'card',
							'card' => array(
								'token' => $data['stripeToken'],
							),
						)
					);

					$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
					$payment_method->attach( array( 'customer' => $cust->id ) );

					$cust->invoice_settings->default_payment_method = $payment_method->id;
					$cust->save();

					$txn_id = $this->charge_plan( $stripe_plan, $cust, $prorate, $automatic_tax, $data, 'charge_new' );

				} else {
					$currency = empty( $stripesettings['currency'] ) ? 'USD' : $stripesettings['currency'];

					// override amount and currency if set in shortcode
					$currency = isset( $data['stripe_currency'] ) ? strtoupper( $data['stripe_currency'] ) : $currency;
					$amt      = isset( $data['stripe_amount'] ) ? (float) $data['stripe_amount'] : $settings['amount'];
					$amt      = number_format( $amt * 100, 0, '.', '' );

					// Create USER
					$cust = WLMStripe\Customer::create(
						array(
							'name'        => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'description' => sprintf( '%s %s', $data['firstname'], $data['lastname'] ),
							'email'       => $data['email'],
						)
					);

					// Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then
					// attach it to customer for future charges. This is a bit different from Stripe tokens.
					$payment_method = WLMStripe\PaymentMethod::create(
						array(
							'type' => 'card',
							'card' => array(
								'token' => $data['stripeToken'],
							),
						)
					);

					$payment_method = WLMStripe\PaymentMethod::retrieve( $payment_method->id );
					$payment_method->attach( array( 'customer' => $cust->id ) );

					$cust->invoice_settings->default_payment_method = $payment_method->id;
					$cust->save();

					// Get the level name as using $settings['membershiplevel'] may cause issues if the admin changes the name of // the membership level.
					$level      = wlmapi_get_level( $data['wpm_id'] );
					$level_name = $level['level']['name'];

					// Create the PaymentIntent
					$intent = WLMStripe\PaymentIntent::create(
						array(
							'customer'            => $cust->id,
							'payment_method'      => $payment_method->id,
							'amount'              => $amt,
							'currency'            => $currency,
							'confirmation_method' => 'automatic',
							'confirm'             => true,
							'description'         => sprintf( '%s - One Time Payment', $level_name ),
						)
					);

					$txn_id = $intent->charges->data[0]->id;

					// If payment requires AUTH then let process_sca_auth handle it.
					if ( 'requires_action' === $intent->status && 'use_stripe_sdk' == $intent->next_action->type ) {
						$this->process_sca_auth( $data, $intent->client_secret, $cust->id, '', $intent->id, 'charge_new' );
					}
				}

				wlm_post_data()['sctxnid'] = $txn_id;
				$this->wlm->shopping_cart_registration( true, false );

				$user = get_user_by( 'login', 'temp_' . md5( $data['email'] ) );
				$this->wlm->Update_UserMeta( $user->ID, 'stripe_cust_id', $cust->id );
				$this->wlm->Update_UserMeta( $user->ID, 'stripe_payment_method_id', $payment_method->id );
				$url = $this->wlm->get_continue_registration_url( $data['email'] );
				wp_redirect( $url );
				die();
			} catch ( Exception $e ) {

				if ( 'requires_action' == $subs->latest_invoice->payment_intent->status ) {
					$this->fail(
						array(
							'msg'             => $e->getMessage(),
							'sku'             => $data['wpm_id'],
							'p_intent_secret' => $subs->latest_invoice->payment_intent->client_secret,
							'cus_id'          => $subs->latest_invoice->customer,
						)
					);
				} else {
						$cust->delete();

						$this->fail(
							array(
								'msg' => $e->getMessage(),
								'sku' => $data['wpm_id'],
							)
						);
				}
			}

		}

		public function charge_plan( $stripe_plan, $cust, $prorate, $automatic_tax, $data, $sca_charge_type, $cuid = '' ) {
			$plan = WLMStripe\Price::retrieve( $stripe_plan );
			if ( $data['coupon'] ) {
					$coupon_id     = $this->coupon_to_cid( $data['coupon'] );
					$promo_code_id = $this->promocode_to_pid( $data['coupon'] );
			}
			if ( $plan->recurring ) {
				// recurring plan
				if ( $coupon_id ) {
					$subs = WLMStripe\Subscription::create(
						array(
							'customer'        => $cust->id,
							'prorate'         => $prorate,
							'automatic_tax'   => array(
								'enabled' => $automatic_tax,
							),
							'coupon'          => $coupon_id,
							'trial_from_plan' => true,
							'items'           => array(
								array(
									'plan' => $stripe_plan,
								),
							),
							'expand'          => array( 'latest_invoice.payment_intent' ),
						)
					);
				} elseif ( $promo_code_id ) {
					$subs = WLMStripe\Subscription::create(
						array(
							'customer'        => $cust->id,
							'prorate'         => $prorate,
							'automatic_tax'   => array(
								'enabled' => $automatic_tax,
							),
							'promotion_code'  => $promo_code_id,
							'trial_from_plan' => true,
							'items'           => array(
								array(
									'plan' => $stripe_plan,
								),
							),
							'expand'          => array( 'latest_invoice.payment_intent' ),
						)
					);
				} else {
					$subs = WLMStripe\Subscription::create(
						array(
							'customer'        => $cust->id,
							'prorate'         => $prorate,
							'automatic_tax'   => array(
								'enabled' => $automatic_tax,
							),
							'coupon'          => $data['coupon'],
							'trial_from_plan' => true,
							'items'           => array(
								array(
									'plan' => $stripe_plan,
								),
							),
							'expand'          => array( 'latest_invoice.payment_intent' ),
						)
					);
				}
				$latest_invoice = $subs->latest_invoice;
			} else {
				// one time payment plan
				$discount = null;
				if ( $coupon_id ) {
					$discount     = $cust->discount;
					$cust->coupon = $coupon_id;
					$cust->save();
				} elseif ( $promo_code_id ) {
					$discount             = $cust->discount;
					$cust->promotion_code = $promo_code_id;
					$cust->save();
				} else {
					$discount     = $cust->discount;
					$cust->coupon = $coupon_id;
					$cust->save();
				}
				$invitem        = WLMStripe\InvoiceItem::create(
					array(
						'customer' => $cust->id,
						'price'    => $stripe_plan,
					)
				);
				$latest_invoice = WLMStripe\Invoice::create(
					array(
						'customer' => $cust->id,
					)
				);
				$latest_invoice->pay();
			}

			if ( 'failed' == $latest_invoice->payment_intent->charges->data[0]->status ) {
				throw new Exception( $latest_invoice->payment_intent->charges->data[0]->failure_message );
			}

			if ( 'requires_action' == $latest_invoice->payment_intent->status ) {
				// If card needs authentication then let's initiate SCA popup
				$this->process_sca_auth( $data, $latest_invoice->payment_intent->client_secret, $cust->id, $stripe_plan, '', $sca_charge_type, $cuid );
			}

			if ( $data['coupon'] ) {
				$cust->coupon = $discount ? $discount->coupon->id : null;
				$cust->save();
			}

			$txn_id = sprintf( '%s-%s', $cust->id, $stripe_plan );
			return $txn_id;
		}
		/**
		 * Process payments that needs SCA authentication
		 * in a form of a pop up modal which Stripe handles via Stripe JS.
		 *
		 * @param array  $data array of data needed to create temp accounts for users
		 * @param string $payment_intent_secret - Needed to trigger the SCA pop up modal
		 * @param string $cust_id - Customer ID created in STripe
		 * @param string $stripe_plan - ID of the Stripe Plan
		 * @param string $payment_intent - Payment Intent ID needed to get the charges->id in function handle_sca_redirect()
		 * @param string $charge_type - (charge_new, charge_existing)
		 * @param int    $user_id - User's WordPress USER ID
		 */
		public function process_sca_auth( $data, $payment_intent_secret, $cust_id, $stripe_plan = '', $payment_intent = '', $charge_type = '', $user_id = '' ) {

			global $WishListMemberInstance;
			$this->wlm = $WishListMemberInstance;

			$stripepublishablekey = wlm_trim( $this->wlm->get_option( 'stripepublishablekey' ) );

			// Build the Success Redirect
			$sca_redirect_nonce = wp_create_nonce( 'stripe-do-sca_redirect' );
			$stripethankyou     = $this->wlm->get_option( 'stripethankyou' );
			$stripethankyou_url = $this->wlm->make_thankyou_url( $stripethankyou );
			$sca_params         = sprintf(
				'?stripe_action=sca_redirect&cus_id=%s&sku=%s&plan_id=%s&fn=%s&ln=%s&p_intent=%s&charge_type=%s&u_id=%s&nonce=%s',
				rawurlencode( $cust_id ),
				rawurlencode( wlm_arrval( $data, 'wpm_id' ) ),
				rawurlencode( $stripe_plan ),
				rawurlencode( wlm_post_data()['firstname'] ),
				rawurlencode( wlm_post_data()['lastname'] ),
				rawurlencode( $payment_intent ),
				rawurlencode( $charge_type ),
				rawurlencode( $user_id ),
				rawurlencode( $sca_redirect_nonce )
			);
			$success_redirect   = $stripethankyou_url . $sca_params;

			// Build the error redirect URL so that we can redirect and tell them in case SCA
			// Authentication Failed
			$error_redirect = wlm_request_data()['redirect_to'];

			if ( false !== stripos( $error_redirect, '?' ) ) {
				$error_redirect .= '&status=fail&reason=' . preg_replace( '/\s+/', '+', 'Failed to complete the Strong Customer Authentication. The payment was not processed.' );
			} else {
				$error_redirect .= '?&status=fail&reason=' . preg_replace( '/\s+/', '+', 'Failed to complete the Strong Customer Authentication. The payment was not be processed.' );
			}
			$error_redirect .= '#regform-' . $data['sku'];

			wlm_print_script( 'https://js.stripe.com/v3/' );
			?>
			<script type="text/javascript">

				var stripe = Stripe('<?php echo esc_js( $stripepublishablekey ); ?>');

				var paymentIntentSecret = "<?php echo esc_js( $payment_intent_secret ); ?>";

					stripe.handleCardPayment(paymentIntentSecret).then(function(result) {
					  if (result.error) {
						  window.location.replace('<?php echo esc_js( $error_redirect ); ?>');
					  } else {
						window.location.replace('<?php echo esc_js( $success_redirect ); ?>');
					  }
					});
			</script>
			<?php
			$animation_image = $WishListMemberInstance->pluginURL . '/images/loadingAnimation.gif';
			?>
			<br><br><br>
			<center>
				<?php esc_html_e( 'The payment will not be processed and services will not be provisioned until authentication is completed. Please complete the authentication.', 'wishlist-member' ); ?>
				<br><br>
				<img src="<?php echo esc_url( $animation_image ); ?>">
			</center>
			<?php
			die();
		}

		/**
		 * This handles creating temp account, redirecting users to reg page and adding them to levels
		 *
		 * @param array $data array of data from $_GET
		 */
		public function handle_sca_redirect( $data ) {

			$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			$cust = WLMStripe\Customer::retrieve( $data['cus_id'] );

			if ( ! empty( $data['plan_id'] ) ) {
				$txn_id = sprintf( '%s-%s', $cust->id, $data['plan_id'] );
			} else {
				// If it's one time then we get the charges ID from payment intent created for the customer
				$payment_intent_id = $data['p_intent'];
				$intent            = WLMStripe\PaymentIntent::retrieve( $payment_intent_id );
				$txn_id            = $intent->charges->data[0]->id;
			}

			if ( 'charge_new' == $data['charge_type'] ) {

				wlm_post_data()['sctxnid']          = $txn_id;
				wlm_post_data()['stripe_wlm_level'] = $data['sku'];
				wlm_post_data()['lastname']         = $data['ln'];
				wlm_post_data()['firstname']        = $data['fn'];
				wlm_post_data()['wpm_id']           = $data['sku'];
				wlm_post_data()['username']         = $cust->email;
				wlm_post_data()['email']            = $cust->email;
				wlm_post_data()['password1']        = $this->wlm->pass_gen();
				wlm_post_data()['password2']        = wlm_post_data()['password1'];

				$this->wlm->shopping_cart_registration( true, false );
				$user = get_user_by( 'login', 'temp_' . md5( $cust->email ) );
				$this->wlm->Update_UserMeta( $user->ID, 'stripe_cust_id', $cust->id );

				// If p_intent is present then this is one time which uses PaymentMethod to make charges.
				// Let's save the payment method ID to the user ID.
				if ( ! empty( $data['p_intent'] ) ) {
					$this->wlm->Update_UserMeta( $user->ID, 'stripe_payment_method_id', $intent->payment_method );
				}

				$url = $this->wlm->get_continue_registration_url( $cust->email );

			} elseif ( 'charge_existing' == $data['charge_type'] ) {
				// add user to level and redirect to the after reg url
				$this->add_to_level( $data['u_id'], $data['sku'], $txn_id );
				$url = $this->wlm->get_after_reg_redirect( $data['sku'] );
			}
			wp_redirect( $url );
			die();
		}

		public function fail( $data ) {
			$uri = wlm_request_data()['redirect_to'];
			if ( false !== stripos( $uri, '?' ) ) {
				$uri .= '&status=fail&reason=' . preg_replace( '/\s+/', '+', $data['msg'] );
			} else {
				$uri .= '?&status=fail&reason=' . preg_replace( '/\s+/', '+', $data['msg'] );
			}
			$uri .= '#regform-' . $data['sku'];
			// error_log($uri);
			wp_redirect( $uri, 307 );

			die();
		}

		public function charge( $data = array() ) {
			$stripeconnections = $this->wlm->get_option( 'stripeconnections' );
			$stripeapikey      = $this->wlm->get_option( 'stripeapikey' );
			$settings          = $stripeconnections[ $data['sku'] ];
			WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

			try {
				$btn_hash        = isset( $data['btn_hash'] ) ? $data['btn_hash'] : false;
				$custom_amount   = isset( $data['custom_amount'] ) ? $data['custom_amount'] : false;
				$custom_currency = isset( $data['custom_currency'] ) ? $data['custom_currency'] : false;
				if ( false !== $custom_amount || false !== $custom_currency ) {
					if ( ! wp_verify_nonce( $btn_hash, "{$stripeapikey}-{$custom_amount}-{$custom_currency}" ) ) {
						throw new Exception( 'Your request is invalid or expired. Please try again.' );
					}
				}

				$last_name  = $data['last_name'];
				$first_name = $data['first_name'];
				if ( 'new' === $charge_type ) {
					if ( empty( $last_name ) || empty( $first_name ) || empty( $data['email'] ) ) {
						throw new Exception( 'All fields are required' );
					}

					if ( empty( $data['stripeToken'] ) ) {
						throw new Exception( 'Payment Processing Failed' );
					}
				}

				wlm_post_data()['stripe_wlm_level'] = $data['sku'];
				wlm_post_data()['lastname']         = $last_name;
				wlm_post_data()['firstname']        = $first_name;
				wlm_post_data()['wpm_id']           = $data['sku'];
				wlm_post_data()['username']         = $data['email'];
				wlm_post_data()['email']            = $data['email'];
				wlm_post_data()['password1']        = $this->wlm->pass_gen();
				wlm_post_data()['password2']        = wlm_post_data()['password1'];

				// lets add custom currency and amount
				if ( $custom_amount ) {
					wlm_post_data()['stripe_amount'] = $custom_amount;
				}
				if ( $custom_currency ) {
					wlm_post_data()['stripe_currency'] = wlm_trim( $custom_currency );
				}

				if ( 'new' == $data['charge_type'] ) {
					$this->charge_new( wlm_post_data( true ) );
				} else {
					$this->charge_existing( wlm_post_data( true ) );
				}
			} catch ( Exception $e ) {
				$this->fail(
					array(
						'msg' => $e->getMessage(),
						'sku' => $data['sku'],
					)
				);
			}
		}

		// following functions are used to query invoices
		// and returns content ready for display for member profile
		public function invoice( $data ) {
			global $current_user;
			if ( empty( $current_user->ID ) ) {
				return;
			}

			try {
				$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
				WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

				$inv  = WLMStripe\Invoice::retrieve( $data['txn_id'] );
				$cust = WLMStripe\Customer::retrieve( $inv['customer'] );
				include $this->get_view_path( 'invoice_details' );
				die();
			} catch ( Exception $e ) {
				null;
			}
		}

		public function invoices( $data ) {
			global $WishListMemberInstance;
			global $current_user;
			if ( empty( $current_user->ID ) ) {
				return;
			}
			$cust_id = $this->wlm->Get_UserMeta( $current_user->ID, 'stripe_cust_id' );
			try {
				$stripeapikey = $this->wlm->get_option( 'stripeapikey' );
				$txns         = $this->wlm->get_membership_levels_txn_ids( $current_user->ID );
				WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );

				$inv      = WLMStripe\Invoice::all(
					array(
						'count'    => 100,
						'customer' => $cust_id,
					)
				);
				$invoices = array();
				if ( ! empty( $inv['data'] ) ) {
					$invoices = array_merge( $invoices, $inv['data'] );
				}
				// try to get manual charges
				// $manual_charges = WLMStripe\Charge::all(array("count" => 100, 'customer' => $cust_id));
				// $invoices = array_merge($invoices, $inv['data']);
				// var_dump($manual_charges);

				include $this->get_view_path( 'invoice_list' );
				die();
			} catch ( Exception $e ) {
				printf( '<p>%s</p>', esc_html__( 'No invoices found for this user', 'wishlist-member' ) );
				die();
			}
		}
		public function get_view_path( $handle ) {
			global $WishListMemberInstance;
			return sprintf( $WishListMemberInstance->plugindir . '/extlib/wlm_stripe/%s.php', $handle );
		}

		/**
		 * Replace $stripe_plan with the customer-selected payment plan if the latter is valid
		 *
		 * @since 3.6
		 *
		 * @param  string $stripe_plan   The original payment plan
		 * @param  array  $connections   Configured stripe connections
		 * @param  array  $data          Post data
		 * @return string                Customer-selected payment plan if valid, otherwise return $stripe_plan
		 */
		private function choose_plan( $stripe_plan, $connections, $data ) {
			/**
			 * Check if customer chose a plan from our payment form and if it is
			 * not the same as $stripe_plan then check if it's any of the configured plans
			 * and if so then change $stripe_plan to $selected_plan
			 *
			 * @since 3.6
			 */
			$selected_plan = wlm_arrval( $data, 'stripe_plan' );
			if ( $selected_plan && $selected_plan != $stripe_plan ) {
				$plans = json_decode( stripslashes( (string) wlm_arrval( $connections[ $data['wpm_id'] ], 'plans' ) ) );
				if ( $plans && is_array( $plans ) && in_array( $selected_plan, $plans ) ) {
					$stripe_plan = $selected_plan;
				}
			}
			return $stripe_plan;
		}
	}

}
