<?php
/**
 * WishList Member Wizard: License
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
					<h4 class="mb-3"><?php esc_html_e( 'Enter your WishList Products License Key to activate.', 'wishlist-member' ); ?></h4>
					<p><?php esc_html_e( 'Your WishList Products License Key was sent to you in an email after purchase. It can also be found in the Customer Center.', 'wishlist-member' ); ?></p>
					<p>
						<?php
						printf(
							'%s <a href="https://member.wishlistproducts.com/" target="_blank">%s</a>',
							esc_html__( "Don't have a License Key?", 'wishlist-member' ),
							esc_html__( 'Get one here.', 'wishlist-member' )
						);
						?>
					</p>
					<div class="form-group large-form">
						<label for=""><?php esc_html_e( 'License Key', 'wishlist-member' ); ?></label>
						<?php $license = wishlistmember_instance()->get_option( 'LicenseKey' ); ?>
						<input type="text" name="license" class="form-control input-lg mb-0" value="<?php echo esc_attr( $license ); ?>">
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<a href="#" data-screen="license" next-screen="license-confirm" class="btn -primary -lg pull-left save-btn">
								<?php esc_html_e( 'Activate License', 'wishlist-member' ); ?>
								<i class="wlm-icons">arrow_forward</i>
							</a>
							<a href="#" class="btn -bare -lg pull-right skip-license">
								<?php esc_html_e( 'Skip', 'wishlist-member' ); ?>
							</a>
						</div>
						<div class="col-12 text-center">
							<span class="form-text text-danger help-block mt-3 d-none">
							</span>
						</div>
					</div>
				</div>
			</div>
			<br>
		</div>
	</div>
</div>
