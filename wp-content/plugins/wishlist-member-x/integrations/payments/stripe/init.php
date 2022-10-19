<?php
/**
 * Stripe init
 *
 * @package WishListMember/Payments
 */

if ( ! class_exists( 'WLM3_Stripe_Hooks' ) ) {
	/**
	 * WLM3_Stripe_Hooks class
	 */
	class WLM3_Stripe_Hooks {
		const MAX_PLAN_COUNT = 999;
		const MAX_PROD_COUNT = 999;
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_wlm3_stripe_test_keys', array( $this, 'test_keys' ) );
		}

		/**
		 * Test keys
		 */
		public function test_keys() {
			$data = array(
				'status'  => false,
				'message' => '',
			);

			$stripeapikey         = wlm_post_data()['data']['stripeapikey'];
			$stripepublishablekey = wlm_post_data()['data']['stripepublishablekey'];
			$save                 = wlm_post_data()['data']['save'];

			if ( ! empty( $stripeapikey ) ) {
				try {

					WLMStripe\WLM_Stripe::setApiKey( $stripeapikey );
					$plans     = WLMStripe\Price::all( array( 'count' => self::MAX_PLAN_COUNT ) );
					$_products = WLMStripe\Product::all( array( 'count' => self::MAX_PROD_COUNT ) );
					$products  = array();
					foreach ( $_products->data as $product ) {
						$products[ $product->id ] = $product->name;
					}

					$api_type        = false === strpos( $stripeapikey, 'test' ) ? 'LIVE' : 'TEST';
					$data['message'] = $api_type;
					$data['status']  = true;

					$data['data']['plan_options'] = array();
					foreach ( $plans->data as $plan ) {
						if ( $plan->recurring ) {
							$interval = $plan->recurring->interval;
							if ( 1 !== (int) $plan->recurring->interval_count ) {
								$interval = sprintf( '%d %ss', $plan->recurring->interval_count, $interval );
							}
						} else {
							$interval = __( 'One time', 'wishlist-member' );
						}
						$text = sprintf( '%s - %s (%s %s / %s)', $products[ $plan->product ], $plan->nickname ? $plan->nickname : $plan->id, strtoupper( $plan->currency ), number_format( $plan->unit_amount / 100, 2, '.', ',' ), $interval );

						// @since 3.6 create optgroup for select2
						if ( ! isset( $data['data']['plan_options'][ $plan->product ] ) ) {
							$data['data']['plan_options'][ $plan->product ] = array(
								'text'     => $products[ $plan->product ],
								'children' => array(),
							);
						}
						// @since 3.6 add plans to correct group
						$data['data']['plan_options'][ $plan->product ]['children'][] = array(
							'value' => $plan->id,
							'id'    => $plan->id,
							'text'  => $text,
						);
					}
					// @since 3.6 remove keys from optgroup as select2 wants an array
					$data['data']['plan_options'] = array_values( $data['data']['plan_options'] );

					$data['data']['plans'] = $plans;

					if ( $save ) {
						wishlistmember_instance()->save_option( 'stripeapikey', $stripeapikey );
						wishlistmember_instance()->save_option( 'stripepublishablekey', $stripepublishablekey );
					}
				} catch ( \Exception $e ) {
					$data['message'] = $e->getMessage();
				}
			} else {
				$data['message'] = 'No Stripe Secret Key';
			}
			wp_die( wp_json_encode( $data ) );
		}
	}
	new WLM3_Stripe_Hooks();
}
