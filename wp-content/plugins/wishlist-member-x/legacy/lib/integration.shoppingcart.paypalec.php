<?php


if ( extension_loaded( 'curl' ) ) {
	global $WishListMemberInstance;
	include_once $WishListMemberInstance->plugindir . '/extlib/paypal/PPAutoloader.php';
	PPAutoloader::register();
}

if ( ! class_exists( 'WLM_INTEGRATION_PAYPALEC' ) ) {
	class WLM_INTEGRATION_PAYPALEC {
		private $settings;
		private $wlm;

		private $thankyou_url;
		private $pp_settings;
		public function __construct() {
			global $WishListMemberInstance;
			$this->wlm      = $WishListMemberInstance;
			$this->products = $this->wlm->get_option( 'paypalecproducts' );

			$settings           = $this->wlm->get_option( 'paypalecthankyou_url' );
			$paypalecthankyou   = $this->wlm->get_option( 'paypalecthankyou' );
			$this->thankyou_url = $this->wlm->make_thankyou_url( $paypalecthankyou );

			$this->cancel_url = $this->wlm->get_option( 'paypalec_cancel_url' );

			$pp_settings = $this->wlm->get_option( 'paypalecsettings' );

			$index = 'live';
			if ( $pp_settings['sandbox_mode'] ) {
				$index = 'sandbox';
			}

			$this->pp_settings = array(
				'acct1.UserName'  => $pp_settings[ $index ]['api_username'],
				'acct1.Password'  => $pp_settings[ $index ]['api_password'],
				'acct1.Signature' => $pp_settings[ $index ]['api_signature'],
				'mode'            => $pp_settings['sandbox_mode'] ? 'sandbox' : 'live',
				'gateway'         => $pp_settings['sandbox_mode'] ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com',
			);

		}
		public function paypalec( $that ) {
			$action = strtolower( wlm_trim( wlm_get_data()['action'] ) );

			switch ( $action ) {
				case 'purchase-express':
					try {
						$this->purchase_express( wlm_get_data()['id'] );
					} catch ( Exception $e ) {
						null;
					}

					break;
				case 'confirm':
					try {
						$this->confirm( wlm_get_data()['id'], wlm_get_data()['token'], wlm_get_data()['PayerID'] );
					} catch ( Exception $e ) {
						null;
					}

					break;
				case 'ipn':
					$this->ipn( wlm_get_data()['id'] );
					break;
			}
		}
		public function ipn( $id = null ) {
			$this->forward_ipn();

			$ipn_message = new PPIPNMessage( null, $this->pp_settings );
			$raw_data    = $ipn_message->getRawData();

			if ( ! $ipn_message->validate() ) {
				return false;
			}

			$txn_id                    = isset( $raw_data['parent_txn_id'] ) ? $raw_data['parent_txn_id'] : $raw_data['txn_id'];
			$txn_id                    = isset( $raw_data['recurring_payment_id'] ) ? $raw_data['recurring_payment_id'] : $txn_id;
			wlm_post_data()['sctxnid'] = $txn_id;

			switch ( $raw_data['txn_type'] ) {
				// anything related to recurring, we follow
				// the profiles status
				case 'subscr_signup':
				case 'recurring_payment':
				case 'recurring_payment_skipped':
				case 'subscr_modify':
				case 'subscr_payment':
				case 'recurring_payment_profile_cancel':
				case 'recurring_payment_expired':
				case 'subscr_eot':
					switch ( $raw_data['profile_status'] ) {
						case 'Active':
							// Don't run Reactive when the recurring_payment is from the trial (initial trial payment)
							$period_type = wlm_trim( $raw_data['period_type'] );
							if ( 'recurring_payment' == $raw_data['txn_type'] ) {
								if ( isset( $period_type ) && 'Trial' === $period_type ) {
									return;
								}
							}

							// Don't run Reactive when the txn_type is recurring_payment_failed
							if ( 'recurring_payment_failed' == $raw_data['txn_type'] ) {
								return;
							}

							// Means the recurring payment failed but profile_status is still Active so don't run reactivate.
							if ( 'recurring_payment_skipped' == $raw_data['txn_type'] ) {
								return;
							}

							$this->wlm->shopping_cart_reactivate();
							break;
						case 'Suspended':
						case 'Cancelled':
							// lets get the level id first so that we know if the settings is Cancel Immediately
							if ( ! isset( wlm_post_data()['wpm_id'] ) || is_null( wlm_post_data()['wpm_id'] ) || empty( wlm_post_data()['wpm_id'] ) ) {
								// get the user of this txnid
								$uid = $this->wlm->get_user_id_from_txn_id( wlm_post_data()['sctxnid'] );
								if ( ! $uid ) {
									break; // let stop it!
								}
								// get the levels who uses this txnid
								$levels = $this->wlm->get_membership_levels_txn_ids( $uid, wlm_post_data()['sctxnid'] );
								if ( ! is_array( $levels ) || count( $levels ) <= 0 ) {
									break; // let stop it!
								}
								$levels = array_keys( $levels );

								wlm_post_data()['wpm_id'] = $levels[0];
							}

							// If Cancel Membership Immediately is enabled for the level then cancel the level
							$paypalecimmediatecancel = $this->wlm->get_option( 'paypalecsubscrcancel' );
							if ( $paypalecimmediatecancel ) {
								$paypalecimmediatecancel = wlm_maybe_unserialize( $paypalecimmediatecancel );
							} else {
								$paypalecimmediatecancel = array();
							}

							if ( ! isset( $paypalecimmediatecancel[ wlm_post_data()['wpm_id'] ] ) || ( isset( $paypalecimmediatecancel[ wlm_post_data()['wpm_id'] ] ) && 1 == $paypalecimmediatecancel[ wlm_post_data()['wpm_id'] ] ) ) {
								$this->wlm->shopping_cart_deactivate();
								return;
							}

							// If Cancel Membership immediately is off then go try the eot settings
							$paypaleceotcancel = $this->wlm->get_option( 'paypaleceotcancel' );
							if ( $paypaleceotcancel ) {
								$paypaleceotcancel = wlm_maybe_unserialize( $paypaleceotcancel );
							} else {
								$paypaleceotcancel = array();
							}

							if ( isset( $paypaleceotcancel[ wlm_post_data()['wpm_id'] ] ) && 1 == $paypaleceotcancel[ wlm_post_data()['wpm_id'] ] ) {
								// This means that we cancel the level at end of Paypal subscription so only do this when
								// we receive the IPN for EOT.

								if ( 'subscr_eot' == $raw_data['txn_type'] ) {
									$this->wlm->shopping_cart_deactivate();
									return;
								}

								// First check if there's a user that matches the transaction ID from the IPN.
								$user_id = wishlistmember_instance()->get_user_id_from_txn_id( wlm_post_data()['sctxnid'] );

								if ( ! $user_id ) {
									return;
								}

								// Get Users recurring payment details to calculate the cancellation date.
								$getRPPDetailsReqest            = new GetRecurringPaymentsProfileDetailsRequestType();
								$getRPPDetailsReqest->ProfileID = wlm_post_data()['sctxnid'];
								$getRPPDetailsReq               = new GetRecurringPaymentsProfileDetailsReq();
								$getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsReqest;
								$pp_service = new PayPalAPIInterfaceServiceService( $this->pp_settings );

								try {
									$getRPPDetailsResponse = $pp_service->GetRecurringPaymentsProfileDetails( $getRPPDetailsReq );

									if ( isset( $getRPPDetailsResponse ) ) {
										$prof_details = $getRPPDetailsResponse->GetRecurringPaymentsProfileDetailsResponseDetails;

										$date_created = $raw_data['time_created'];
										$date_created = wlm_date( 'Y-m-d H:i:s', strtotime( $date_created ) );

										// If TNumberCyclesCompleted is 0 then it means the Trial period is not yet over.
										// Set the cancellation date at the end of the trial period.
										if ( $prof_details->TrialRecurringPaymentsPeriod && 0 == $prof_details->TNumberCyclesCompleted ) {
											$billing_period    = $prof_details->TrialRecurringPaymentsPeriod->BillingPeriod;
											$billing_frequency = $prof_details->TrialRecurringPaymentsPeriod->BillingFrequency;
											$date_to_add       = '+' . $billing_frequency . ' ' . $billing_period;

										} else {
											$billing_period          = $prof_details->RegularRecurringPaymentsPeriod->BillingPeriod;
											$billing_frequency       = $prof_details->RegularRecurringPaymentsPeriod->BillingFrequency;
											$number_cycles_completed = $prof_details->RecurringPaymentsSummary->NumberCyclesCompleted;

											$total_billing_frequency = $number_cycles_completed * $billing_frequency;
											$date_to_add             = '+' . $total_billing_frequency . ' ' . $billing_period;
										}

										$cancel_date_timestamp = wlm_date( 'Y-m-d H:i:s', strtotime( $date_to_add, strtotime( $date_created ) ) );
										$cancel_date_timestamp = strtotime( $cancel_date_timestamp );
										$users_levels          = wlmapi_get_member_levels( $user_id );

										foreach ( $users_levels as $users_level ) {
											if ( wlm_post_data()['sctxnid'] == $users_level->TxnID ) {

												wishlistmember_instance()->schedule_level_deactivation(
													$users_level->Level_ID,
													array( $user_id ),
													$cancel_date_timestamp,
													array(
														'type' => 'paypal',
														'icon' => 'paypal_logo',
														'text' => 'Paypal Subscription Was Cancelled.',
													)
												);
											}
										}
									}
								} catch ( Exception $ex ) {
									return;
								}
								return;
							}
							break;

						default:
							break;
					}

					break;
				case 'subscr_cancel':
					// In case subscr_cancel is from Paypal Standard then use subscr_id as the txn_id in case wlm_post_data()['sctxnid']  is empty
					wlm_post_data()['sctxnid'] = isset( wlm_post_data()['sctxnid'] ) ? wlm_post_data()['sctxnid'] : $raw_data['subscr_id'];

					// lets cancel for trial subscriptions
					$paypalecsubscrcancel = $this->wlm->get_option( 'paypalecsubscrcancel' );
					if ( $paypalecsubscrcancel ) {
						$paypalecsubscrcancel = wlm_maybe_unserialize( $paypalecsubscrcancel );
					} else {
						$paypalecsubscrcancel = false;
					}

					if ( isset( wlm_post_data()['amount1'] ) && '0.00' == wlm_post_data()['amount1'] ) {
						$this->wlm->shopping_cart_deactivate();
					} elseif ( isset( wlm_post_data()['mc_amount1'] ) && '0.00' == wlm_post_data()['mc_amount1'] ) {
						$this->wlm->shopping_cart_deactivate();
					} elseif ( false === $paypalecsubscrcancel ) { // default settings
						$this->wlm->shopping_cart_deactivate();
					} else {
						// lets get the level id first so that we know if the settings is cancelled
						if ( ! isset( wlm_post_data()['wpm_id'] ) || is_null( wlm_post_data()['wpm_id'] ) || empty( wlm_post_data()['wpm_id'] ) ) {
							// get the user of this txnid
							$uid = $this->wlm->get_user_id_from_txn_id( wlm_post_data()['subscr_id'] );
							if ( ! $uid ) {
								break; // let stop it!
							}
							// get the levels who uses this txnid
							$levels = $this->wlm->get_membership_levels_txn_ids( $uid, wlm_post_data()['subscr_id'] );
							if ( ! is_array( $levels ) || count( $levels ) <= 0 ) {
								break; // let stop it!
							}
							$levels = array_keys( $levels );

							// if multiple levels is found using the txnid
							// lets check the name and amount to get the real level
							// -- needed for levels with child and parent
							$p = $this->wlm->get_option( 'paypalecproducts' );
							if ( count( $p ) >= 1 && count( $levels ) > 1 ) {
								// lets get the price and name
								$item_name   = isset( wlm_post_data()['item_name'] ) ? wlm_post_data()['item_name'] : '';
								$item_amount = isset( wlm_post_data()['amount3'] ) ? wlm_post_data()['amount3'] : null;
								$item_amount = ( is_null( $item_amount ) && isset( wlm_post_data()['mc_amount3'] ) ) ? wlm_post_data()['mc_amount3'] : $item_amount;

								// lets check all products and make sure we process the recurring only
								foreach ( $p as $key => $value ) {
									if ( '1' == $value['recurring'] ) {
										// if their name and amount matches, we got our guy
										if ( $value['name'] == $item_name && $value['recur_amount'] == $item_amount ) {
											wlm_post_data()['wpm_id'] = $value['sku'];
											break; // lets end the loop (only the loop not the switch)
										}
									}
								}
							}
							// still empty? lets use the first level we found
							if ( ! isset( wlm_post_data()['wpm_id'] ) || is_null( wlm_post_data()['wpm_id'] ) || empty( wlm_post_data()['wpm_id'] ) ) {
								wlm_post_data()['wpm_id'] = $levels[0];
							}
						}

						if ( isset( $paypalecsubscrcancel[ wlm_post_data()['wpm_id'] ] ) && 1 == $paypalecsubscrcancel[ wlm_post_data()['wpm_id'] ] ) {
							$this->wlm->shopping_cart_deactivate();
						}
					}
					break;
				case 'recurring_payment_failed':
				case 'recurring_payment_suspended_due_to_max_failed_payment':
				case 'recurring_payment_suspended':
				case 'subscr_failed':
					switch ( $raw_data['profile_status'] ) {
						case 'Active':
							$this->wlm->shopping_cart_reactivate();
							break;
						case 'Suspended':
						case 'Cancelled':
							$this->wlm->shopping_cart_deactivate();
							break;
						default:
							// ignore
							break;
					}
					// were done
					return;
					break;
				case 'subscr_cancel':
					wlm_post_data()['sctxnid'] = isset( wlm_post_data()['sctxnid'] ) ? wlm_post_data()['sctxnid'] : $raw_data['subscr_id'];
					$this->wlm->shopping_cart_deactivate();
					break;
			}

			// this is a one time payment
			switch ( $raw_data['payment_status'] ) {
				case 'Completed':
					if ( isset( $raw_data['echeck_time_processed'] ) ) {
						$this->wlm->shopping_cart_reactivate( 1 );
					} else {
						$this->wlm->shopping_cart_registration( null, false );
						$this->wlm->cart_integration_terminate();
					}
					break;
				case 'Canceled-Reversal':
					$this->wlm->shopping_cart_reactivate();
					break;
				case 'Processed':
					$this->wlm->shopping_cart_reactivate( 'Confirm' );
					break;
				case 'Expired':
				case 'Failed':
				case 'Refunded':
				case 'Reversed':
					$this->wlm->shopping_cart_deactivate();
					break;

			}
		}
		public function confirm( $id, $token, $payer_id ) {
			$products = $this->products;
			$product  = $products[ $id ];
			if ( empty( $product ) ) {
				return;
			}

			$paypal_service = new PayPalAPIInterfaceServiceService( $this->pp_settings );

			$ec_details_req_type = new GetExpressCheckoutDetailsRequestType( $token );
			$ec_detail_req       = new GetExpressCheckoutDetailsReq();

			$ec_detail_req->GetExpressCheckoutDetailsRequest = $ec_details_req_type;

			$ec_resp = $paypal_service->GetExpressCheckoutDetails( $ec_detail_req );

			if ( ! $ec_resp && 'Success' != $ec_resp->Ack ) {
				throw new Exception( 'Paypal Request Failed' );
			}

			// we now have the payer info
			$payer_info = $ec_resp->GetExpressCheckoutDetailsResponseDetails->PayerInfo;

			if ( $product['recurring'] ) {
				$order_total = new BasicAmountType( $product['currency'], 0 );
			} else {
				$order_total = new BasicAmountType( $product['currency'], $product['amount'] );
			}

			$payment_details                   = new PaymentDetailsType();
			$payment_details->OrderTotal       = $order_total;
			$payment_details->OrderDescription = $product['name'];
			$payment_details->NotifyURL        = $this->thankyou_url . '?action=ipn&id=' . $id;

			$item_details       = new PaymentDetailsItemType();
			$item_details->Name = $product['name'];

			$item_details->Amount   = $product['amount'];
			$item_details->Quantity = 1;

			$payment_details->PaymentDetailsItem[ $i ] = $item_details;

			$do_ec_details                    = new DoExpressCheckoutPaymentRequestDetailsType();
			$do_ec_details->PayerID           = $payer_id;
			$do_ec_details->Token             = $token;
			$do_ec_details->PaymentDetails[0] = $payment_details;

			$do_ec_request = new DoExpressCheckoutPaymentRequestType();
			$do_ec_request->DoExpressCheckoutPaymentRequestDetails = $do_ec_details;

			if ( $order_total->value > 0 ) {
				$do_ec                                  = new DoExpressCheckoutPaymentReq();
				$do_ec->DoExpressCheckoutPaymentRequest = $do_ec_request;

				$do_ec_resp = $paypal_service->DoExpressCheckoutPayment( $do_ec );
				if ( ! $do_ec_resp || 'Success' != $do_ec_resp->Ack ) {
					throw new Exception( 'Paypal Checkout Error Has Occured' );
				}

				// we now have a payment info. Yeehaaa
				$payment_info = current( $do_ec_resp->DoExpressCheckoutPaymentResponseDetails->PaymentInfo );

				$accept_statuses = array( 'Completed', 'In-Progress', 'Pending', 'Processed' );
				if ( ! in_array( $payment_info->PaymentStatus, $accept_statuses ) ) {
					throw new Exception( 'Paypal Payment Checkout Failed' );
				}
			}

			if ( $product['recurring'] ) {
				// create a recurring payment profile
				$schedule_details = new ScheduleDetailsType();

				$schedule_details->MaxFailedPayments = $product['max_failed_payments'] ? $product['max_failed_payments'] : $product['max_failed_payments'];

				$payment_billing_period                   = new BillingPeriodDetailsType();
				$payment_billing_period->BillingFrequency = $product['recur_billing_frequency'];
				$payment_billing_period->BillingPeriod    = $product['recur_billing_period'];
				$payment_billing_period->Amount           = new BasicAmountType( $product['currency'], $product['recur_amount'] );
				if ( $product['recur_billing_cycles'] > 1 ) {
					$payment_billing_period->TotalBillingCycles = $product['recur_billing_cycles'];
				}
				$schedule_details->PaymentPeriod = $payment_billing_period;

				if ( $product['trial'] && is_numeric( $product['trial_amount'] ) ) {
					$trial_payment_billing_period                     = new BillingPeriodDetailsType();
					$trial_payment_billing_period->BillingFrequency   = $product['trial_recur_billing_frequency'];
					$trial_payment_billing_period->BillingPeriod      = $product['trial_recur_billing_period'];
					$trial_payment_billing_period->Amount             = new BasicAmountType( $product['currency'], $product['trial_amount'] );
					$trial_payment_billing_period->TotalBillingCycles = 1;
					$schedule_details->TrialPeriod                    = $trial_payment_billing_period;
				}

				$schedule_details->Description = wlm_paypal_create_description( $product );

				$recur_profile_details = new RecurringPaymentsProfileDetailsType();
				// $recur_profile_details->BillingStartDate = wlm_date(DATE_ATOM, strtotime(sprintf("+%s %s", $product['recur_billing_frequency'], $product['recur_billing_period'])));
				$recur_profile_details->BillingStartDate = wlm_date( DATE_ATOM );

				$create_recur_paypay_profile_details                                  = new CreateRecurringPaymentsProfileRequestDetailsType();
				$create_recur_paypay_profile_details->Token                           = $token;
				$create_recur_paypay_profile_details->ScheduleDetails                 = $schedule_details;
				$create_recur_paypay_profile_details->RecurringPaymentsProfileDetails = $recur_profile_details;

				$create_recur_profile = new CreateRecurringPaymentsProfileRequestType();
				$create_recur_profile->CreateRecurringPaymentsProfileRequestDetails = $create_recur_paypay_profile_details;

				$create_recur_profile_req                                        = new CreateRecurringPaymentsProfileReq();
				$create_recur_profile_req->CreateRecurringPaymentsProfileRequest = $create_recur_profile;
				$create_profile_resp = $paypal_service->CreateRecurringPaymentsProfile( $create_recur_profile_req );

				if ( ! $create_profile_resp || 'Success' != $create_profile_resp->Ack ) {
					throw new Exception( 'Could not create recurring profile' );
				}
			}

			$address             = array();
			$address['company']  = $payer_info->PayerBusiness;
			$address['address1'] = $payer_info->Address->Street1;
			$address['address2'] = $payer_info->Address->Street2;
			$address['city']     = $payer_info->Address->CityName;
			$address['state']    = $payer_info->Address->StateOrProvince;
			$address['zip']      = $payer_info->Address->PostalCode;
			$address['country']  = $payer_info->Address->CountryName;

			wlm_post_data()['wpm_useraddress'] = $address;
			wlm_post_data()['lastname']        = $payer_info->PayerName->LastName;
			wlm_post_data()['firstname']       = $payer_info->PayerName->FirstName;
			wlm_post_data()['action']          = 'wpm_register';
			wlm_post_data()['wpm_id']          = $product['sku'];
			wlm_post_data()['username']        = $payer_info->Payer;
			wlm_post_data()['email']           = $payer_info->Payer;
			wlm_post_data()['password1']       = $this->wlm->pass_gen();
			wlm_post_data()['password2']       = wlm_post_data()['password1'];
			wlm_post_data()['sctxnid']         = $product['recurring'] ? $create_profile_resp->CreateRecurringPaymentsProfileResponseDetails->ProfileID :
			$payment_info->TransactionID;

			$pending_statuses = array( 'In-Progress', 'Pending' );
			if ( in_array( $payment_info->PaymentStatus, $pending_statuses ) || 'PendingProfile' == $create_profile_resp->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus ) {
				$this->wlm->shopping_cart_registration( null, null, 'Paypal Pending' );
			} else {
				$this->wlm->shopping_cart_registration();
			}

		}
		public function purchase_express( $id ) {
			$products = $this->products;
			$product  = $products[ $id ];
			if ( empty( $product ) ) {
				return;
			}

			$paypal_service  = new PayPalAPIInterfaceServiceService( $this->pp_settings );
			$payment_details = new PaymentDetailsType();

			if ( $product['recurring'] ) {
				$item_details                                   = new PaymentDetailsItemType();
				$billing_agreement                              = new BillingAgreementDetailsType( 'RecurringPayments' );
				$billing_agreement->BillingAgreementDescription = wlm_paypal_create_description( $product );
			} else {
				$item_details                = new PaymentDetailsItemType();
				$item_details->Name          = $product['name'];
				$item_details->Amount        = $product['amount'];
				$item_details->Quantity      = 1;
				$payment_details->OrderTotal = new BasicAmountType( $product['currency'], $product['amount'] );
			}

			$payment_details->PaymentDetailsItem[ $i ] = $item_details;

			$ec_req_details                     = new SetExpressCheckoutRequestDetailsType();
			$ec_req_details->NoShipping         = empty( $product['shipping'] ) ? 1 : 0;
			$ec_req_details->ReqConfirmShipping = 0;
			$ec_req_details->SolutionType       = 'Sole';
			$ec_req_details->ReturnURL          = $this->thankyou_url . '?action=confirm&id=' . $id;

			if ( ! $this->cancel_url ) {
				$this->cancel_url = get_bloginfo( 'url' );
			}

			$ec_req_details->CancelURL         = $this->cancel_url;
			$ec_req_details->LandingPage       = 'Billing';
			$ec_req_details->PaymentDetails[0] = $payment_details;

			if ( isset( $billing_agreement ) ) {
				$ec_req_details->BillingAgreementDetails = array( $billing_agreement );
			}

			$ec_req_type                                   = new SetExpressCheckoutRequestType();
			$ec_req_type->SetExpressCheckoutRequestDetails = $ec_req_details;

			$ec_req                            = new SetExpressCheckoutReq();
			$ec_req->SetExpressCheckoutRequest = $ec_req_type;

			$ec_res = $paypal_service->SetExpressCheckout( $ec_req );

			if ( $ec_res && 'Success' == $ec_res->Ack ) {
				if ( ! empty( wlm_get_data()['spb'] ) ) {
					wp_send_json( array( 'token' => $ec_res->Token ) );
				}
				$next_loc = sprintf( '%s/webscr?cmd=_express-checkout&useraction=commit&token=%s', $this->pp_settings['gateway'], $ec_res->Token );
				wp_redirect( $next_loc );
				die();
			}

		}

		private function forward_ipn() {
			global $WishListMemberInstance;

			$urls = wlm_trim( $WishListMemberInstance->get_option( 'paypalec_ipnforwarding' ) );
			if ( empty( $urls ) ) {
				return;
			}

			$urls = explode( "\n", $urls );
			$get  = wlm_get_data( true );
			unset( $get['action'] );
			unset( $get['wlmdebug'] );
			unset( $get['WishListMemberAction'] );

			$post = wlm_post_data( true );
			unset( $post['wlmdebug'] );
			unset( $post['WishListMemberAction'] );

			$params = array(
				'body'     => $post,
				'method'   => $post ? 'POST' : 'GET',
				'blocking' => false,
			);

			foreach ( $urls as $url ) {
				$url = add_query_arg( $get, esc_url_raw( wlm_trim( $url ) ) );
				wp_remote_request( $url, $params );
			}
		}
	}
}
