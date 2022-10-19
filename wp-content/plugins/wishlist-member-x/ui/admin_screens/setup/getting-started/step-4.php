<?php
/**
 * WishList Member Wizard: Step 4
 *
 * @package WishListMember/Wizard
 */

?>
<div class="wizard-form step-4 d-none">
	<div class="row">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<h3 class="title"><span class="number"><?php esc_html_e( '4', 'wishlist-member' ); ?></span> <?php esc_html_e( 'Email Setup', 'wishlist-member' ); ?></h3>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<?php require wishlistmember_instance()->plugindir3 . '/helpers/header-icons.php'; ?>
		</div>
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="progress">
				<div class="progress-bar -success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 80%;">
				</div>
			</div>
		</div>
	</div>
	<div class="content-wrapper -no-header level-data">
		<div class="row">
			<div class="col-md-12">
				<h4 class="mb-4"><?php esc_html_e( 'WishList Member will send various email messages to members.', 'wishlist-member' ); ?></h4>
				<h4><?php esc_html_e( 'Messages will be sent from:', 'wishlist-member' ); ?></h4>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for=""><?php esc_html_e( 'Name', 'wishlist-member' ); ?></label>
							<input type="text" name="email_sender_name" class="form-control" value="<?php echo esc_attr( wishlistmember_instance()->get_option( 'email_sender_name' ) ); ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for=""><?php esc_html_e( 'Email Address', 'wishlist-member' ); ?></label>
							<input type="text" name="email_sender_address" class="form-control" value="<?php echo esc_attr( wishlistmember_instance()->get_option( 'email_sender_address' ) ); ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer -content-footer">
			<div class="row">
				<div class="col-sm-4 col-md-3 col-lg-3 order-sm-1 order-md-0">
					<div class="pull-left">
						<a href="#" class="btn -outline -bare isexit" data-screen="thanks"><?php esc_html_e( 'Exit Wizard', 'wishlist-member' ); ?></a>
					</div>
				</div>
				<div class="col-sm-12 col-md-4 col-lg-4 order-sm-0">
					<div class="indicator text-center">4/5</div>
				</div>
				<div class="col-sm-8 col-md-5 col-lg-5 order-sm-2">
					<div class="pull-right">
						<a href="#" class="btn -default next-btn isback" data-screen="step-4" next-screen="step-3">
							<i class="wlm-icons">arrow_back</i>
							<span><?php esc_html_e( 'Back', 'wishlist-member' ); ?></span>
						</a>
						<a href="#" class="btn -primary next-btn" data-screen="step-4" next-screen="step-5">
							<span><?php esc_html_e( 'Next', 'wishlist-member' ); ?></span>
							<i class="wlm-icons">arrow_forward</i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
