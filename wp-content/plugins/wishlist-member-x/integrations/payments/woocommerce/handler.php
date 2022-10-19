<?php // integration handler
if ( ! class_exists( 'WLM_INTEGRATION_WOOCOMMERCE' ) ) {
	class WLM_INTEGRATION_WOOCOMMERCE {
		public function __construct() {
			add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 3 );
			add_action( 'woocommerce_subscription_status_changed', array( $this, 'subscription_status_changed' ), 10, 3 );
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_metabox_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_metabox_panel' ), 1000 );
			add_action( 'save_post_product', array( $this, 'save_woocommerce_product' ) );
			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
				add_action( 'untrashed_post', array( $this, 'untrash_post' ), 1000 );
				// woocommerce products metabox tab and panel
			}
		}

		/**
		 * Action: save_post_product
		 *
		 * @param integer $postID
		 */
		public function save_woocommerce_product( $postID ) {
			if ( ! ( isset( wlm_post_data()['wishlist_member_woo_levels'] ) ) ) {
				return;
			}
			$wlmwoo = wishlistmember_instance()->get_option( 'woocommerce_products' );
			if ( ! is_array( $wlmwoo ) ) {
				$wlmwoo = array();
			}
			$wlmwoo[ $postID ] = wlm_post_data()['wishlist_member_woo_levels'];
			wishlistmember_instance()->save_option( 'woocommerce_products', $wlmwoo );
		}

		/**
		 * Filter: woocommerce_product_data_tabs
		 * Add WishList Member tab to the WooCommerce Product Meta box
		 *
		 * @param array $tabs Array of tabs
		 * @return array
		 */
		public function product_metabox_tabs( $tabs ) {
			$tabs['wishlist_member_woo'] = array(
				'label'    => __( 'WishList Member', 'woocommerce' ),
				'target'   => 'wishlist_member_woo',
				'class'    => array(),
				'priority' => 71,
			);
			return $tabs;
		}

		/**
		 * Action: woocommerce_product_data_panels
		 * Add WishList Member panel to the WooCommerce Product Meta Box
		 */
		public function product_metabox_panel() {
			require_once __DIR__ . '/resources/woocommerce_products_panel.php';
		}

		/**
		 * Removes levels from a member if an order is trashed
		 *
		 * @param int $post_id
		 */
		public function trash_post( $post_id ) {
			if ( ! function_exists( 'wc_get_order' ) ) {
				return;
			}
			$order = wc_get_order( $post_id );
			if ( ! $order ) {
				return;
			}
			$this->__remove_levels( $this->__generate_transaction_id( $order ) );
		}

		/**
		 * Restores an order from trash and updates levels accordingly
		 *
		 * @param int $post_id
		 */
		public function untrash_post( $post_id ) {
			if ( ! function_exists( 'wc_get_order' ) ) {
				return;
			}
			$order = wc_get_order( $post_id );
			if ( ! $order ) {
				return;
			}
			$function = function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $order ) ? 'subscription_status_changed' : 'order_status_changed';
			call_user_func( array( $this, $function ), $post_id, 'trash', $order->get_status() );
		}

		/**
		 * Map subscription status to either activate, remove or deactivate
		 * Called by woocommerce_subscription_status_changed action
		 *
		 * @uses WLM_INTEGRATION_WOOCOMMERCE::__status_changed
		 *
		 * @param int    $order_id
		 * @param string $old_status
		 * @param string $new_status
		 */
		public function subscription_status_changed( $order_id, $old_status, $new_status ) {
			switch ( $new_status ) {
				case 'active':
					$status = 'activate';
					break;
				case 'cancelled':
					$status = 'deactivate';
					break;
				case 'processing':
				case 'pending':
				case 'on-hold':
					$status = 'pending';
					break;
				case 'switched':
				case 'pending-cancel':
				case 'expired':
				default:
					$status = '';
			}
			if ( $status ) {
				$this->__status_changed( $order_id, $status );
			}
		}

		/**
		 * Map order status change to either activate, remove or deactivate
		 * Called by woocommerce_order_status_changed action
		 *
		 * @uses WLM_INTEGRATION_WOOCOMMERCE::__status_changed
		 *
		 * @param int    $order_id
		 * @param string $old_status
		 * @param string $new_status
		 */
		public function order_status_changed( $order_id, $old_status, $new_status ) {
			switch ( $new_status ) {
				case 'completed':
				case 'processing':
					$status = 'activate';
					break;
				case 'cancelled':
				case 'refunded':
					$status = 'deactivate';
					break;
				case 'pending':
				case 'on-hold':
				case 'failed':
					$status = 'remove';
					break;
				default:
					$status = '';
			}
			if ( $status ) {
				$this->__status_changed( $order_id, $status );
			}
		}

		/**
		 * Updates a member's levels or their status
		 * Creates a new member if one doesn't exist yet
		 * Used info is gathered from the $order_id
		 *
		 * @param int    $order_id
		 * @param string $status
		 */
		private function __status_changed( $order_id, $status ) {
			global $wlm_no_cartintegrationterminate;
			$wpm_levels = wishlistmember_instance()->get_option( 'wpm_levels' );
			if ( ! function_exists( 'wc_get_order' ) ) {
				return;
			}
			$woocommerce_products = wishlistmember_instance()->get_option( 'woocommerce_products' );
			$order                = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}
			$txnid = $this->__generate_transaction_id( $order );

			switch ( $status ) {
				case 'activate':
					// take care adding of new customer and levels
					$user = $order->get_customer_id();
					if ( ! $user ) {
						$user = get_user_by( 'email', $order->get_billing_email() );
						if ( ! $user ) {
							$user = array(
								'first_name'       => $order->get_billing_first_name(),
								'last_name'        => $order->get_billing_last_name(),
								'user_email'       => $order->get_billing_email(),
								'user_login'       => $order->get_billing_email(),
								'user_pass'        => wlm_generate_password(),

								// address
								'company'          => $order->get_billing_company(),
								'address1'         => $order->get_billing_address_1(),
								'address2'         => $order->get_billing_address_2(),
								'city'             => $order->get_billing_city(),
								'state'            => $order->get_billing_state(),
								'zip'              => $order->get_billing_postcode(),
								'country'          => WC()->countries->countries[ $order->get_billing_country() ],

								'SendMailPerLevel' => 1,
							);
						} else {
							$user = $user->ID;
						}
					}
					$levels = array();
					foreach ( $order->get_items() as $item ) {
						$pid = $item->get_product()->id;
						if ( isset( $woocommerce_products[ $pid ] ) && is_array( $woocommerce_products[ $pid ] ) ) {
							$levels = array_merge( $levels, $woocommerce_products[ $pid ] );
						}
					}
					if ( $levels ) {
						$memlevels = array();
						if ( is_int( $user ) ) {
							$memlevels = wishlistmember_instance()->get_membership_levels( $user, false, true );
							foreach ( (array) $levels as $level ) {
								if ( wishlistmember_instance()->level_for_approval( $level, $user ) ) {
									wishlistmember_instance()->level_for_approval( $level, $user, false );
								}
							}
						}
						$levels = array_unique( $levels );
						foreach ( $levels as &$level ) {
							if ( is_int( $user ) ) {
								$registration_date = wishlistmember_instance()->Get_UserLevelMeta( $user, $level, 'registration_date' );
								$expired           = wishlistmember_instance()->level_expired( $level, $user );
								if ( $expired && isset( $wpm_levels[ $level ]['registrationdatereset'] ) ) {
									$registration_date = null;
								}
								if ( isset( $wpm_levels[ $level ]['registrationdateresetactive'] ) ) {
									$registration_date = null;
								}
							} else {
								$registration_date = null;
							}
							$level = in_array( $level, $memlevels ) ? false : array( $level, $txnid, $registration_date );
						}
						unset( $level );
						$levels = array( 'Levels' => array_diff( $levels, array( false ) ) );
						$uid    = 0;
						if ( is_array( $user ) ) {
							$result = wlmapi_add_member( $user + $levels );
							if ( $result['success'] && $result['member'][0]['ID'] ) {
								$uid = $result['member'][0]['ID'];
								if ( ! is_admin() ) {
									wishlistmember_instance()->wpm_auto_login( $result['member'][0]['ID'] );
								}
							}
						} else {
							foreach ( $levels['Levels'] as $purchased_level ) {
								// Let's grab the SKU of the purchased Level/PPP
								$p_sku   = $purchased_level[0];
								$p_txnid = $purchased_level[1];

								$member = new \WishListMember\User( $user );

								// Initialize sequential upgrade for new users created during WooCommerce checkout process.
								wishlistmember_instance()->is_sequential( $user, true );

								// Let's manually add the user to PPP or to levels using the User() object
								// as doing it via wlmapi_update_member() has an issue where existing PPPs of
								// the user are removed when they purchase new PPP/Levels.
								$payperpost = preg_match( '/^payperpost-(\d+)$/', $p_sku, $match );
								if ( $payperpost ) {
									$member->add_payperposts( $p_sku );
									$payperpost = get_post( $match[1] );
								} else {
									// For cancelled members
									$cancelled      = wishlistmember_instance()->level_cancelled( $p_sku, $user );
									$resetcancelled = true; // lets make sure that old versions without this settings still works
									if ( isset( $wpm_levels[ $p_sku ]['uncancelonregistration'] ) ) {
										$resetcancelled = 1 == $wpm_levels[ $p_sku ]['uncancelonregistration'];
									}
									if ( $cancelled && $resetcancelled ) {
										$ret = wishlistmember_instance()->level_cancelled( $p_sku, $user, false );
										wishlistmember_instance()->set_membership_level_txn_id( $user, $p_sku, "{$p_txnid}" . time() );// update txnid
									}

									// For Expired Members
									$expired      = wishlistmember_instance()->level_expired( $p_sku, $user );
									$resetexpired = 1 == $wpm_levels[ $p_sku ]['registrationdatereset'];
									if ( $expired && $resetexpired ) {
											wishlistmember_instance()->user_level_timestamp( $user, $p_sku, time() );
											wishlistmember_instance()->set_membership_level_txn_id( $user, $p_sku, "{$p_txnid}" . time() );// update txnid
									} else {
										// if levels has expiration and allow reregistration for active members
										$levelexpires     = isset( $wpm_levels[ $p_sku ]['expire'] ) ? (int) $wpm_levels[ $p_sku ]['expire'] : false;
										$levelexpires_cal = isset( $wpm_levels[ $p_sku ]['calendar'] ) ? $wpm_levels[ $p_sku ]['calendar'] : false;
										$resetactive      = 1 == $wpm_levels[ $p_sku ]['registrationdateresetactive'];
										if ( $levelexpires && $resetactive ) {
											// get the registration date before it gets updated because we will use it later
											$levelexpire_regdate = wishlistmember_instance()->Get_UserLevelMeta( $user, $p_sku, 'registration_date' );

											$levelexpires_cal = in_array( $levelexpires_cal, array( 'Days', 'Weeks', 'Months', 'Years' ) ) ? $levelexpires_cal : false;
											if ( $levelexpires_cal && $levelexpire_regdate ) {
												list( $xdate, $xfraction )                                 = explode( '#', $levelexpire_regdate );
												list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split( '/[- :]/', $xdate );
												if ( 'Days' === $levelexpires_cal ) {
													$xday = $levelexpires + $xday;
												}
												if ( 'Weeks' === $levelexpires_cal ) {
													$xday = ( $levelexpires * 7 ) + $xday;
												}
												if ( 'Months' === $levelexpires_cal ) {
													$xmonth = $levelexpires + $xmonth;
												}
												if ( 'Years' === $levelexpires_cal ) {
													$xyear = $levelexpires + $xyear;
												}
												wishlistmember_instance()->user_level_timestamp( $user, $p_sku, mktime( $xhour, $xminute, $xsecond, $xmonth, $xday, $xyear ) );
												wishlistmember_instance()->set_membership_level_txn_id( $user, $p_sku, "{$p_txnid}" . time() );// update txnid
											}
										}
									}
									$member->AddLevel( $p_sku, $p_txnid );
								}

								$email_macros['[memberlevel]'] = $payperpost ? $payperpost->post_title : wlm_trim( $wpm_levels[ $p_sku ]['name'] );
								$email_macros['[password]']    = $user_pass ? $user_pass : '********';

								if ( 1 == $wpm_levels[ $p_sku ]['newuser_notification_user'] ) {
									wishlistmember_instance()->email_template_level = $p_sku;
									wishlistmember_instance()->send_email_template( 'registration', $user, $email_macros );
								}

								if ( 1 == $wpm_levels[ $p_sku ]['newuser_notification_admin'] ) {
									wishlistmember_instance()->email_template_level = $p_sku;
									wishlistmember_instance()->send_email_template( 'admin_new_member_notice', $user, $email_macros, wishlistmember_instance()->get_option( 'email_sender_address' ) );
								}
							}
							$uid = $user;
						}
						if ( $uid ) {
							// link order to user
							wc_update_new_customer_past_orders( $uid );
							// update billing and shipping meta
							$metas = get_post_meta( $order_id );
							foreach ( $metas as $key => $value ) {
								if ( preg_match( '/^_((billing|shipping)_.+)/', $key, $match ) ) {
									update_user_meta( $uid, $match[1], $value[0] );
								}
							}
						}
					}

					$old                             = $wlm_no_cartintegrationterminate;
					$wlm_no_cartintegrationterminate = true;
					wlm_post_data()['sctxnid']       = $txnid;

					wishlistmember_instance()->shopping_cart_reactivate();
					$wlm_no_cartintegrationterminate = $old;
					break;
				case 'deactivate':
					$old                             = $wlm_no_cartintegrationterminate;
					$wlm_no_cartintegrationterminate = true;
					wlm_post_data()['sctxnid']       = $txnid;
					$user_id                         = $order->get_customer_id();
					if ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $order_id ) ) {
						$subscription   = wcs_get_subscription( $order_id );
						$related_orders = $subscription->get_related_orders( 'ids', 'renewal' );

						foreach ( $related_orders as $order_id ) {
							$order            = wc_get_order( $order_id );
							$txnid            = $this->__generate_transaction_id( $order );
							$user_level_txnid = wishlistmember_instance()->get_membership_levels_txn_ids( $user_id, $txnid );
							if ( ! empty( $user_level_txnid ) ) {
								wlm_post_data()['sctxnid'] = $txnid;
								wishlistmember_instance()->shopping_cart_deactivate();
							}
						}
					}

					wishlistmember_instance()->shopping_cart_deactivate();
					$wlm_no_cartintegrationterminate = $old;
					break;
				case 'pending':
					$user_id = $order->get_customer_id();
					if ( $user_id ) {
						$levels = array();
						foreach ( $order->get_items() as $item ) {
							$pid = $item->get_product()->id;
							if ( isset( $woocommerce_products[ $pid ] ) && is_array( $woocommerce_products[ $pid ] ) ) {
								$levels = array_merge( $levels, $woocommerce_products[ $pid ] );
							}
						}
						if ( $levels ) {
							foreach ( (array) $levels as $level ) {
								wishlistmember_instance()->level_for_approval( $level, $user_id, true );
							}
						}
						if ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $order_id ) ) {
							$subscription   = wcs_get_subscription( $order_id );
							$related_orders = $subscription->get_related_orders( 'ids', 'renewal' );

							foreach ( $related_orders as $order_id ) {
								$order  = wc_get_order( $order_id );
								$txnid  = $this->__generate_transaction_id( $order );
								$levels = array_intersect( array_keys( (array) wishlistmember_instance()->get_membership_levels_txn_ids( $user_id, $txnid ) ), wishlistmember_instance()->get_membership_levels( $user_id ) );

								foreach ( (array) $levels as $level ) {
									wishlistmember_instance()->level_for_approval( $level, $user_id, true );
								}
							}
						}
					}
					break;
				case 'remove':
					$user_id = $order->get_customer_id();
					if ( $user_id ) {
						$levels = array();
						foreach ( $order->get_items() as $item ) {
							$pid = $item->get_product()->id;
							if ( isset( $woocommerce_products[ $pid ] ) && is_array( $woocommerce_products[ $pid ] ) ) {
								$levels = array_merge( $levels, $woocommerce_products[ $pid ] );
							}
						}
						if ( $levels ) {
							foreach ( $levels as $level ) {
								$expired = wishlistmember_instance()->level_expired( $level, $user_id );
								if ( $expired && isset( $wpm_levels[ $level ]['registrationdatereset'] ) ) {
									// wishlistmember_instance()->user_level_timestamp( $user_id, $level, time() );
									wlmapi_update_member( $user_id, array( 'RemoveLevels' => array( $level ) ) );
								}
								if ( isset( $wpm_levels[ $level ]['registrationdateresetactive'] ) ) {
									// wishlistmember_instance()->user_level_timestamp( $user_id, $level, time() );
									wlmapi_update_member( $user_id, array( 'RemoveLevels' => array( $level ) ) );
								}
							}
						}
					}
					// This does not work for existing level because txnid on each payment can be different
					// $this->__remove_levels( $txnid );
					break;
			}
		}

		/**
		 * Removes levels from a member based on transaction ID
		 *
		 * @param string $txnid
		 */
		private function __remove_levels( $txnid ) {
			$user_id = wishlistmember_instance()->get_user_id_from_txn_id( $txnid );
			if ( $user_id ) {
				$levels = wishlistmember_instance()->get_membership_levels_txn_ids( $user_id, $txnid );
				if ( $levels ) {
					wlmapi_update_member( $user_id, array( 'RemoveLevels' => array_keys( $levels ) ) );
				}
			}

		}

		/**
		 * Generates transaction id from order WooCommerce object
		 *
		 * @param WC_Order $order
		 */
		private function __generate_transaction_id( $order ) {
			return 'WooCommerce#' . $order->get_parent_id() . '-' . $order->get_order_number();
		}
	}
	new WLM_INTEGRATION_WOOCOMMERCE();
}
