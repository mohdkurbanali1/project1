<?php
/**
 * WishList Member Wizard: License Confirmation
 *
 * @package WishListMember/Wizard
 */

?>
<div class="wizard-form -dark">
	<div class="content-wrapper -no-header level-data">
		<div class="row align-items-center">
			<div class="col-md-5">
				<div class="information text-center">
					<img src="<?php echo esc_url( wishlistmember_instance()->pluginURL3 ); ?>/ui/images/wishlist-member-logo.png" class="mx-auto d-block" alt="">
				</div>
			</div>
			<div class="col-md-7">
				<div class="white-background">
					<div class="row">
						<div class="col-md-12">
							<?php require wishlistmember_instance()->plugindir3 . '/helpers/header-icons.php'; ?>
						</div>
					</div>
					<br>
					<?php
					$wpwl_key = wishlistmember_instance()->get_option( 'LicenseKey' );
					if ( $wpwl_key ) {
						$wpwl_key_expire = wishlistmember_instance()->get_option( 'LicenseExpiration' );
						$wpwl_email      = wishlistmember_instance()->get_option( 'LicenseEmail' );
						// make sure we have a valid license info.
						$wpwl_key   = false !== $wpwl_key ? ( '' !== wlm_trim( $wpwl_key ) ? wlm_trim( $wpwl_key ) : false ) : false;
						$wpwl_email = false !== $wpwl_email ? ( '' !== wlm_trim( $wpwl_email ) ? wlm_trim( $wpwl_email ) : false ) : false;

						$key_is_expired = wlm_date( 'Y-m-d 00:00:00' ) > $wpwl_key_expire;
						$lifetime       = substr( $wpwl_key_expire, 0, 4 ) > 2999;
						$text1          = $key_is_expired ? __( 'Support Plan Expired', 'wishlist-member' ) : __( 'Support Plan Expiration', 'wishlist-member' );
						$text2          = $key_is_expired ? __( 'Click Here to update your expired Support Plan Now', 'wishlist-member' ) : __( 'Click Here for more information on a Support Plan Renewal', 'wishlist-member' );
						$span_style     = $key_is_expired ? ' style="color:red"' : '';
					}
					?>
					<div class="row">
						<?php if ( $wpwl_key ) : ?>
						<div class="col-md-12">
							<div class="panel-body mb-4 text-center">
								<h4 class="mb-3"><?php esc_html_e( 'Congratulations, your license was successfully activated.', 'wishlist-member' ); ?></h4>
								<p><strong><?php esc_html_e( 'License Key', 'wishlist-member' ); ?>:</strong> ************************<?php echo esc_html( substr( $wpwl_key, -4 ) ); ?></p>
								<p><?php esc_html_e( 'Support Plan Expiration', 'wishlist-member' ); ?>: <span<?php echo esc_html( $span_style ); ?>><?php echo $lifetime ? esc_html__( 'Lifetime', 'wishlist-member' ) : esc_html( wlm_date( 'F j, Y', strtotime( $wpwl_key_expire ) ) ); ?></span></p>
							</div>
						</div>
						<?php else : ?>
						<div class="col-md-12">
							<div class="text-center form-text text-danger help-block mb-5">
								<h4><?php esc_html_e( 'A valid WishList Member license key is required to qualify for updates and support.', 'wishlist-member' ); ?></h4>
							</div>
						</div>
						<?php endif; ?>
						<div class="col-md-12 text-center">
							<?php $wpm_levels = wishlistmember_instance()->get_option( 'wpm_levels' ); ?>
							<a href="#" data-screen="license-confirm" next-screen="start" class="btn -success -lg next-btn">
								<?php esc_html_e( 'Run the Setup Wizard now', 'wishlist-member' ); ?>
								<i class="wlm-icons">arrow_forward</i>
							</a>
							<br><br>
							<a href="#" class="btn -bare -lg next-btn" data-screen="thanks" next-screen="home">
								<?php esc_html_e( 'No Thanks', 'wishlist-member' ); ?>
							</a>
							<br><br>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
