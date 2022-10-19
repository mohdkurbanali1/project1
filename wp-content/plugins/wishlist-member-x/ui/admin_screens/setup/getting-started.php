<?php
$wpm_levels = $this->get_option( 'wpm_levels' );
$levelid    = isset( wlm_get_data()['levelid'] ) ? wlm_get_data()['levelid'] : false;
if ( $levelid ) {
	$level_data = isset( $wpm_levels[ $levelid ] ) ? $wpm_levels[ $levelid ] : $this->level_defaults;
} else {
	$level_data = $this->level_defaults;
}
if ( $levelid ) {
	$level_data['id'] = $levelid;
}
?>
<div class="row">
	<div class="col-md-1 col-sm-1"></div>
	<div class="col-md-10 col-sm-10">
			<div class="getting-started">
				<?php if ( $levelid ) : ?>
					<input type="hidden" name="levelid" value="<?php echo esc_attr( $levelid ); ?>" />
				<?php endif; ?>
				<?php
				if ( 1 != $this->get_option( 'LicenseStatus' ) ) {
					include $this->plugindir3 . '/ui/admin_screens/setup/getting-started/license.php';
				} else {
					if ( count( $wpm_levels ) > 0 && false === $levelid ) {
						include $this->plugindir3 . '/ui/admin_screens/setup/getting-started/start.php';
					} else {
						include $this->plugindir3 . '/ui/admin_screens/setup/getting-started/step-1.php';
					}
				}
				?>
			</div>
	</div>
	<div class="col-md-1 col-sm-1"></div>
</div>
