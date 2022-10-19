<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="webhooks-outgoing-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="webhooks-outgoing-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="webhooks-outgoing-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing WebHook URLs for <?php echo esc_attr( $level->name ); ?>"
	data-classes="modal-lg"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation"><a class="nav-link active" href="#outgoing-modal-add-<?php echo esc_attr( $level->id ); ?>" role="tab" data-toggle="tab"><?php esc_html_e( 'When Added', 'wishlist-member' ); ?></a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#outgoing-modal-remove-<?php echo esc_attr( $level->id ); ?>" role="tab" data-toggle="tab"><?php esc_html_e( 'When Removed', 'wishlist-member' ); ?></a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#outgoing-modal-cancel-<?php echo esc_attr( $level->id ); ?>" role="tab" data-toggle="tab"><?php esc_html_e( 'When Cancelled', 'wishlist-member' ); ?></a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#outgoing-modal-uncancel-<?php echo esc_attr( $level->id ); ?>" role="tab" data-toggle="tab"><?php esc_html_e( 'When Uncancelled', 'wishlist-member' ); ?></a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="outgoing-modal-add-<?php echo esc_attr( $level->id ); ?>">
				<div class="row">
					<template class="wlm3-form-group">
						{
							column : 'col-12',
							label : '<?php esc_js_e( 'Outgoing WebHook URLs', 'wishlist-member' ); ?>',
							type : 'textarea',
							placeholder : 'https://...',
							tooltip : '<?php esc_js_e( 'Enter one URL per line', 'wishlist-member' ); ?>',
							name : 'webhooks_settings[outgoing][<?php echo esc_attr( $level->id ); ?>][add]'
						}
					</template>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="outgoing-modal-remove-<?php echo esc_attr( $level->id ); ?>">
				<div class="row">
					<template class="wlm3-form-group">
						{
							column : 'col-12',
							label : '<?php esc_js_e( 'Outgoing WebHook URLs', 'wishlist-member' ); ?>',
							type : 'textarea',
							placeholder : 'https://...',
							tooltip : '<?php esc_js_e( 'Enter one URL per line', 'wishlist-member' ); ?>',
							name : 'webhooks_settings[outgoing][<?php echo esc_attr( $level->id ); ?>][remove]'
						}
					</template>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="outgoing-modal-cancel-<?php echo esc_attr( $level->id ); ?>">
				<div class="row">
					<template class="wlm3-form-group">
						{
							column : 'col-12',
							label : '<?php esc_js_e( 'Outgoing WebHook URLs', 'wishlist-member' ); ?>',
							type : 'textarea',
							placeholder : 'https://...',
							tooltip : '<?php esc_js_e( 'Enter one URL per line', 'wishlist-member' ); ?>',
							name : 'webhooks_settings[outgoing][<?php echo esc_attr( $level->id ); ?>][cancel]'
						}
					</template>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="outgoing-modal-uncancel-<?php echo esc_attr( $level->id ); ?>">
				<div class="row">
					<template class="wlm3-form-group">
						{
							column : 'col-12',
							label : '<?php esc_js_e( 'Outgoing WebHook URLs', 'wishlist-member' ); ?>',
							type : 'textarea',
							placeholder : 'https://...',
							tooltip : '<?php esc_js_e( 'Enter one URL per line', 'wishlist-member' ); ?>',
							name : 'webhooks_settings[outgoing][<?php echo esc_attr( $level->id ); ?>][uncancel]'
						}
					</template>
				</div>
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
