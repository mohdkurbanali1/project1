<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="aweberapi-lists-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="aweberapi-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="aweberapi-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	data-classes="modal-lg"
	style="display:none">
	<div class="body">
		<div class="row">
			<div class="col-md-12">		
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#aweberapi-settings-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'Settings', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#aweberapi-when-added-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Added', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#aweberapi-when-cancelled-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Cancelled', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#aweberapi-when-removed-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Removed', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="tab-pane active in" id="aweberapi-settings-<?php echo esc_attr( $level->id ); ?>">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'List', 'wishlist-member' ); ?>',
							type : 'select',
							column : 'col-md-12 lists_column',
							name : 'connections[<?php echo esc_attr( $level->id ); ?>]',
							'data-mirror-value' : '#aweberapi-lists-<?php echo esc_attr( $level->id ); ?>',
							style : 'width: 100%',
							class : 'aweberapi-connections',
						}
					</template>

					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Ad Tracking', 'wishlist-member' ); ?>',
							column : 'col-md-4',
							type : 'text',
							name : 'ad_tracking[<?php echo esc_attr( $level->id ); ?>]',
							'data-mirror-value' : '#aweberapi-adtracking-<?php echo esc_attr( $level->id ); ?>',
						}
					</template>			

					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Action if Member is Removed or Cancelled from Level', 'wishlist-member' ); ?>',
							type : 'select',
							column : 'col-md-8',
							name : 'autounsub[<?php echo esc_attr( $level->id ); ?>]',
							'data-mirror-value' : '#aweberapi-unsubscribe-<?php echo esc_attr( $level->id ); ?>',
							options : [
							{value : 'nothing', text : 'Do Nothing (Contact will remain on Selected List)'},
							{value : 'unsubscribe', text : 'Unsubscribe Contact from Selected List'},
							{value : 'delete', text : 'Delete Contact from Selected List'},
							],
							style : 'width: 100%',
						}
					</template>
				</div>
			</div>

			<div class="row tab-pane" id="aweberapi-when-added-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][added][apply]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][added][remove]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>			
			</div>

			<div class="row tab-pane" id="aweberapi-when-cancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][cancelled][apply]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][cancelled][remove]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>			
			</div>

			<div class="row tab-pane" id="aweberapi-when-removed-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][removed][apply]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
						column : 'col-md-12',
						type : 'text',
						name : 'level_tag[<?php echo esc_attr( $level->id ); ?>][removed][remove]',
						placeholder : '<?php esc_js_e( 'tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>',
						tooltip : '<?php esc_js_e( 'Type in your tags separated by commas. Ex. tag 1, tag 2, tag 3 ...', 'wishlist-member' ); ?>'
					}
				</template>			
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
