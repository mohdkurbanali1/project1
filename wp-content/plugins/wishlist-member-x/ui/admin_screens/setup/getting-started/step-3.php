<?php
/**
 * WishList Member Wizard: Step 3
 *
 * @package WishListMember/Wizard
 */

?>
<div class="wizard-form step-3 d-none">
	<div class="row">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<h3 class="title"><span class="number"><?php esc_html_e( '3', 'wishlist-member' ); ?></span> <?php esc_html_e( 'Content Protection', 'wishlist-member' ); ?></h3>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<?php require wishlistmember_instance()->plugindir3 . '/helpers/header-icons.php'; ?>
		</div>
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="progress">
				<div class="progress-bar -success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
				</div>
			</div>
		</div>
	</div>
	<div class="content-wrapper -no-header level-data">
		<div class="row">
			<div class="col-md-12">
				<p>
					<?php esc_html_e( 'When you create content on your site, WishList Member can automatically set it to be protected. This default protection setting can be turned off later. You can always edit the protection of pages and posts on an individual basis regardless of this setting.', 'wishlist-member' ); ?>
				</p>
				<div class="row">
					<div class="col-md-12">
						<div class="form-check -with-tooltip">
							<label class="cb-container"><?php esc_html_e( 'Automatically Protect New Pages/Posts', 'wishlist-member' ); ?>
								<?php $default_protect = (bool) wishlistmember_instance()->get_option( 'default_protect' ); ?>
								<input name="default_protect" value="1" type="checkbox" <?php echo $default_protect ? "checked='checked'" : ''; ?>>
								<span class="checkmark"></span>
							</label>
						</div>
					</div>
				</div>
				<br><br>
				<p><?php esc_html_e( 'WishList Member can hide protected content from your site. This is helpful when you only want to show content to members who are logged in and have access to it.', 'wishlist-member' ); ?></p>
				<div class="row">
					<div class="col-md-12">
						<div class="form-check -with-tooltip">
							<label class="cb-container"><?php esc_html_e( 'Only show content for each membership level', 'wishlist-member' ); ?>
								<?php $only_show_content_for_level = (bool) wishlistmember_instance()->get_option( 'only_show_content_for_level' ); ?>
								<input name="only_show_content_for_level" value="1" type="checkbox" <?php echo $only_show_content_for_level ? "checked='checked'" : ''; ?>>
								<span class="checkmark"></span>
							</label>
						</div>
					</div>
				</div>
				<br><br>
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
					<div class="indicator text-center">3/5</div>
				</div>
				<div class="col-sm-8 col-md-5 col-lg-5 order-sm-2">
					<div class="pull-right">
						<a href="#" class="btn -default next-btn isback" data-screen="step-3" next-screen="step-2">
							<i class="wlm-icons">arrow_back</i>
							<span><?php esc_html_e( 'Back', 'wishlist-member' ); ?></span>
						</a>
						<a href="#" class="btn -primary next-btn" data-screen="step-3" next-screen="step-4">
							<span><?php esc_html_e( 'Next', 'wishlist-member' ); ?></span>
							<i class="wlm-icons">arrow_forward</i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
