<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<div class="col-md-12">
				<p><a href="#sendstudio-enable-api" class="hide-show"><?php esc_html_e( 'Enable the Interspire Email Marketing XML API', 'wishlist-member' ); ?></a></p>
				<div class="panel d-none" id="sendstudio-enable-api">
					<div class="panel-body">
						<ol style="list-style: decimal">
							<li><p><?php esc_html_e( 'Log in to the Interspire Email Marketer account', 'wishlist-member' ); ?></p></li>
							<li><p><?php esc_html_e( 'Navigate to the following section:', 'wishlist-member' ); ?><br>Member &amp; Groups > View Member Acounts > (Edit Member) > Advance Member Settings</p></li>
							<li><p><?php esc_html_e( 'Check Enable the XML API', 'wishlist-member' ); ?></p></li>
						</ol>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'XML Path', 'wishlist-member' ); ?>',
					name : 'sspath',
					column : 'col-md-12',
					type : 'url',
					help_block : '<?php esc_js_e( 'Example: http://www.yourdomain.com/[path/to/IEM/installation]/xml.php', 'wishlist-member' ); ?>',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'XML Username', 'wishlist-member' ); ?>',
					name : 'ssuname',
					column : 'col-md-4',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'XML Token', 'wishlist-member' ); ?>',
					name : 'sstoken',
					column : 'col-md-8',
				}
			</template>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p><a href="#sendstudio-custom-fields" class="hide-show"><?php esc_html_e( 'Assign Custom Field IDs for the First Name and Last Name', 'wishlist-member' ); ?></a></p>
				<div class="panel d-none" id="sendstudio-custom-fields">
					<div class="panel-body">
						<ol style="list-style: decimal">
							<li><p class="mb-0"><?php esc_html_e( 'Log in to the Interspire Email Marketer account', 'wishlist-member' ); ?></p></li>
							<li><p class="mb-0">Navigate to the following section:<br><?php esc_html_e( 'Contact Lists Tab > View Custom Fields and then click "Edit"', 'wishlist-member' ); ?></p></li>
							<li><p class="mb-0"><?php esc_html_e( 'Copy the value of the "ID" parameter from the browser URL Example:', 'wishlist-member' ); ?><br>http://www.yourdomain.com/[path/to/IEM]/admin/index.php?Page=CustomFields&Action=Edit&id=<mark>2</mark><br>(<?php esc_html_e( 'The number 2 is the ID in this example)', 'wishlist-member' ); ?></p></li>
						</ol>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'First Name Custom Field ID', 'wishlist-member' ); ?>',
					name : 'ssfnameid',
					'data-default' : 2,
					column : 'col-md-6',
					tooltip : '<p><?php esc_js_e( 'Your SendStudio First Name Field Id.', 'wishlist-member' ); ?></p><p><?php printf( esc_js( /* Translators: %s the number 2 in bold text */ __( 'By default Email Marketer Installation has the value of %s', 'wishlist-member' ) ), '<strong>2</strong>' ); ?></p>',
					tooltip_size : 'lg',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Last Name Custom Field ID', 'wishlist-member' ); ?>',
					name : 'sslnameid',
					'data-default' : 3,
					column : 'col-md-6',
					tooltip : '<p><?php esc_js_e( 'Your SendStudio Last Name Field Id.', 'wishlist-member' ); ?></p><p><?php printf( esc_js( /* Translators: %s the number 3 in bold text */ __( 'By default Email Marketer Installation has the value of %s', 'wishlist-member' ) ), '<strong>3</strong>' ); ?></p>',
					tooltip_size : 'lg',
				}
			</template>
		</div>
	</div>
</div>
