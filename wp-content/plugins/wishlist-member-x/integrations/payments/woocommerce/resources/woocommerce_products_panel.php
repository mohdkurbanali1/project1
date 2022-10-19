<div id="wishlist_member_woo" class="panel woocommerce_options_panel hidden">
	<div class="options_group">
		<?php
			// make sure we have access to WLM and the current post
			global $post;

			// get the levels linked to this product
			$wlmwoo = (array) wlm_arrval( (array) wishlistmember_instance()->get_option( 'woocommerce_products' ), $post->ID );

			// generate options for our select
			$options   = array();
			$options[] = sprintf( '<optgroup label="%s"> </option>', __( 'Membership Levels', 'wishlist-member' ) );
		foreach ( \WishListMember\Level::get_all_levels( true ) as $level ) {
			$selected  = in_array( $level->ID, $wlmwoo ) ? 'selected' : '';
			$options[] = sprintf( '<option value="%s" %s>%s</option>', $level->ID, $selected, $level->name );
		}
		foreach ( wishlistmember_instance()->get_pay_per_posts( array( 'post_title', 'post_type' ) ) as $post_type => $posts ) {
			$options[] = sprintf( '<optgroup label="%s"> </option>', ucfirst( $post_type ) );
			foreach ( $posts as $post ) {
				$selected  = in_array( 'payperpost-' . $post->ID, $wlmwoo ) ? 'selected' : '';
				$options[] = sprintf( '<option value="%s" %s>%s</option>', 'payperpost-' . $post->ID, $selected, $post->post_title );
			}
		}
		?>
		<p>
			<?php esc_html_e( 'After purchasing this product, the customer will be added as a Member to the Membership Level(s) selected below.', 'wishlist-member' ); ?>
		</p>
		<p class="form-field">
			<label for="wishlist_member_woo_levels"><?php esc_html_e( 'Membership Level(s)', 'wishlist-member' ); ?></label>
			<select id="wishlist_member_woo_levels" name="wishlist_member_woo_levels[]" multiple style="width: 50%">
				<?php echo implode( '', $options ); ?>
			</select>
		</p>
	</div>
</div>

<script type="text/javascript">
jQuery(function() {
	jQuery('#wishlist_member_woo_levels').select2();
});
</script>

<style type="text/css">
/* tab icon */
#woocommerce-product-data .wishlist_member_woo_options.active:hover > a:before,
#woocommerce-product-data .wishlist_member_woo_options > a:before {
	background: url( '<?php echo wishlistmember_instance()->pluginURL3; ?>/ui/images/WishListMember-logomark-16px-wp.svg' ) center center no-repeat;
	content: " " !important;
	background-size: 100%;
	width: 13px;
	height: 13px;
	display: inline-block;
	line-height: 1;
}
@media only screen and (max-width: 900px) {
	#woocommerce-product-data .wishlist_member_woo_options.active:hover > a:before,
	#woocommerce-product-data .wishlist_member_woo_options > a:before,
	#woocommerce-product-data .wishlist_member_woo_options:hover a:before {
		background-size: 35%;
	}
}
.wishlist_member_woo_options:hover a:before {
	background: url( '<?php echo wishlistmember_instance()->pluginURL3; ?>/ui/images/WishListMember-logomark-16px-wp.svg' ) center center no-repeat;
}
</style>
