<?php
$pp_upgrade_instructions = <<<STRING
<div class="col-md-12">
	<p><a href="#%1\$s-upgrade" class="hide-show">%2\$s</a></p>
	<div class="d-none" id="%1\$s-upgrade">
		<div class="panel">
			<div class="panel-body">
				<ol style="list-style: decimal;">
					<li><p class="mb-0">Go to <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_registration-run" target="_blank">https://www.paypal.com/cgi-bin/webscr?cmd=_registration-run</a></p></li>
					<li><p class="mb-0">%3\$s</p></li>
					<li><p class="mb-0">%4\$s</p></li>
					<li><p class="mb-0">%5\$s</p></li>
					<li><p class="mb-0">%6\$s</p></li>
					<li><p class="mb-0">%7\$s</p></li>
				</ol>
			</div>
		</div>
	</div>
</div>
STRING;

$pp_upgrade_instructions = sprintf(
	$pp_upgrade_instructions,
	$config['id'],
	esc_html__( 'PayPal Personal Account Users Upgrade Instructions', 'wishlist-member' ),
	esc_html__( 'Click on the Upgrade Your Account link.', 'wishlist-member' ),
	esc_html__( 'Click on the Upgrade Now Button.', 'wishlist-member' ),
	esc_html__( 'If the existing account is a Personal PayPal account, there will be a choice to upgrade to a Premier or Business account.', 'wishlist-member' ),
	esc_html__( 'Choose to upgrade to a Premier or Business account and follow the instructions.', 'wishlist-member' ),
	esc_html__( 'If the existing account is a Premier PayPal account, the ability to upgrade to a Business account will be presented with instructions that can be followed.', 'wishlist-member' )
);
