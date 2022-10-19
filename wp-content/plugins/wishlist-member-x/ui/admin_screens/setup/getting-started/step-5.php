<?php
/**
 * WishList Member Wizard: Step 5
 *
 * @package WishListMember/Wizard
 */

?>
<div class="wizard-form step-5 d-none">
	<div class="row">
		<div class="col-md-8 col-sm-8 col-xs-8">
			<h3 class="title"><span class="number"><?php esc_html_e( '5', 'wishlist-member' ); ?></span> <?php esc_html_e( 'Integrations', 'wishlist-member' ); ?></h3>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<?php require wishlistmember_instance()->plugindir3 . '/helpers/header-icons.php'; ?>
		</div>
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="progress">
				<div class="progress-bar -success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
				</div>
			</div>
		</div>
	</div>
	<?php
	$show_legacy = wishlistmember_instance()->get_option( 'show_legacy_integrations' );

	$payment_providers = array();
	$providers         = glob( wishlistmember_instance()->plugindir3 . '/integrations/payments/*', GLOB_ONLYDIR );
	foreach ( $providers as $provider ) {
		$config = include $provider . '/config.php';
		if ( ! isset( $config['legacy'] ) || $show_legacy ) {
			$payment_providers[ $config['id'] ] = empty( $config['nickname'] ) ? $config['name'] : $config['nickname'];
		}
	}
	asort( $payment_providers, SORT_STRING | SORT_FLAG_CASE );
	/**
	 * Filters the payment providers that are to be listed in the wizard.
	 *
	 * Returning a falsy value will not show the wizard's payment provider section at all.
	 *
	 * @param string[] $payment_providers An array of payment provider names keyed by their corresponding id.
	 */
	$payment_providers = apply_filters( 'wishlistmember_wizard_payment_providers', $payment_providers );

	$email_providers = array();
	$providers       = glob( wishlistmember_instance()->plugindir3 . '/integrations/emails/*', GLOB_ONLYDIR );
	foreach ( $providers as $provider ) {
		$config = include $provider . '/config.php';
		if ( ! isset( $config['legacy'] ) || $show_legacy ) {
			$email_providers[ $config['id'] ] = empty( $config['nickname'] ) ? $config['name'] : $config['nickname'];
		}
	}
	asort( $email_providers, SORT_STRING | SORT_FLAG_CASE );

	/**
	 * Filters the email providers that are to be listed in the wizard.
	 *
	 * Returning a falsy value will not show the wizard's email provider section at all.
	 *
	 * @param string[] $payment_providers An array of email provider names keyed by their corresponding id.
	 */
	$email_providers = apply_filters( 'wishlistmember_wizard_email_providers', $email_providers );
	?>
	<div class="content-wrapper -no-header wizard-integration-holder level-data">
		<div class="row">
			<?php if ( $payment_providers ) : ?>
			<div class="col-md-12 no-margin">
				<label for=""><?php esc_html_e( 'Would you like to accept payments using a 3rd party payment provider? If so, you can enable a Payment Provider integration by selecting it from the list.', 'wishlist-member' ); ?></label>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<select class="form-control integration-wlm-select wlm-select" name="payment_provider" style="width: 100%" data-placeholder="<?php esc_html_e( 'Select payment provider', 'wishlist-member' ); ?>">
						<option></option>
						<?php foreach ( $payment_providers as $xid => $name ) : ?>
						<option value="<?php echo esc_attr( $xid ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="col-md-8" style="display: none;">
				<p class="pt-2" style="opacity: 0.5; font-style: italic;">(<?php esc_html_e( 'Further setup will be required later)', 'wishlist-member' ); ?></p>
			</div>
			<?php else : ?>
			<input type="hidden" name="payment_provider">
			<?php endif; ?>
			<?php if ( $email_providers ) : ?>
			<div class="col-md-12 no-margin">
				<label for=""><?php esc_html_e( 'Would you like to add your members to a mailing list using a 3rd party email service? If so, you can enable an Email Provider integration by selecting it from the list.', 'wishlist-member' ); ?></label>
			</div>
			<div class="col-md-4 no-margin">
				<div class="form-group no-margin">
					<select class="form-control integration-wlm-select wlm-select" name="email_provider" style="width: 100%" data-placeholder="<?php esc_html_e( 'Select email provider', 'wishlist-member' ); ?>">
						<option></option>
						<?php foreach ( $email_providers as $xid => $name ) : ?>
						<option value="<?php echo esc_attr( $xid ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="col-md-8" style="display: none;">
				<p class="pt-2" style="opacity: 0.5; font-style: italic;">(<?php esc_html_e( 'Further setup will be required later)', 'wishlist-member' ); ?></p>
			</div>
			<?php else : ?>
			<input type="hidden" name="payment_provider">
			<?php endif; ?>
		</div>
		<div class="panel-footer -content-footer">
			<div class="row">
				<div class="col-sm-4 col-md-3 col-lg-3 order-sm-1 order-md-0">
					<div class="pull-left">
						<a href="#" class="btn -outline -bare isexit" data-screen="thanks"><?php esc_html_e( 'Exit Wizard', 'wishlist-member' ); ?></a>
					</div>
				</div>
				<div class="col-sm-12 col-md-4 col-lg-4 order-sm-0">
					<div class="indicator text-center">5/5</div>
				</div>
				<div class="col-sm-8 col-md-5 col-lg-5 order-sm-2">
					<div class="pull-right">
						<a href="#" class="btn -default next-btn isback" data-screen="step-5" next-screen="step-4">
							<i class="wlm-icons">arrow_back</i>
							<span><?php esc_html_e( 'Back', 'wishlist-member' ); ?></span>
						</a>
						<a href="#" class="btn -success save-btn" data-screen="step-5" next-screen="congrats">
							<i class="wlm-icons">save</i>
							<span><?php esc_html_e( 'Save', 'wishlist-member' ); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
