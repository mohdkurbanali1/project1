<?php
/**
 * WooCommerce init
 *
 * @package WishListMember/Payments
 */

if ( ! class_exists( 'WLM3_WooCommerce_Hooks' ) ) {
	/**
	 * WLM3_WooCommerce_Hooks class
	 */
	class WLM3_WooCommerce_Hooks {
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_wlm3_save_woocommerce_product', array( $this, 'save_product' ) );
			add_action( 'wp_ajax_wlm3_delete_woocommerce_product', array( $this, 'delete_product' ) );
		}

		/**
		 * Save product
		 */
		public function save_product() {
			$data = array(
				'status'  => false,
				'message' => '',
				'data'    => array(),
			);

			$id     = trim( wlm_post_data()[ 'id' ] );
			$old_id = wlm_post_data()[ 'old_id' ];
			$access = wlm_post_data()[ 'access' ];

			if ( empty( $id ) ) {
				$data['message'] = 'Product ID Required';
				wp_send_json( $data );
			}
			$products = $this->__delete_product( $old_id );

			$products[ $id ] = array_merge( (array) $products[ $id ], (array) $access );
			$products[ $id ] = array_diff( array_unique( $products[ $id ] ), array( '', null, 0 ) );

			wishlistmember_instance()->save_option( 'woocommerce_products', array_diff( $products, array( '', null, 0 ) ) );
			$data['status']                       = true;
			$data['message']                      = 'Product Saved';
			$data['data']['woocommerce_products'] = $products;
			wp_send_json( $data );
		}

		/**
		 * Delete product
		 */
		public function delete_product() {
			$data = array(
				'status'  => true,
				'message' => '',
				'data'    => array(),
			);

			$data['data']['woocommerce_products'] = $this->__delete_product( wlm_post_data()[ 'id' ] );
			wp_send_json( $data );
		}

		/**
		 * Private delete product method
		 *
		 * @param  int $id Product ID.
		 * @return array
		 */
		private function __delete_product( $id ) {
			$products = wishlistmember_instance()->get_option( 'woocommerce_products' );
			unset( $products[ $id ] );
			wishlistmember_instance()->save_option( 'woocommerce_products', $products );
			return $products;
		}
	}
	new WLM3_WooCommerce_Hooks();
}
