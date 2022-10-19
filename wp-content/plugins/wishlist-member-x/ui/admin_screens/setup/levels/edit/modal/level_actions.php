<div
	id="level-actions-modal"
	data-id="level-actions"
	data-label="level-actions"
	data-title="Add Level Actions"
	data-show-default-footer=""
	data-classes="modal-md"
	style="display:none">
	<div class="body">
		<div class="row" id="level-action-data">
			<template class="wlm3-form-group">
				{
					group_class : 'mb-1',
					label : '<?php esc_js_e( 'When this happens:', 'wishlist-member' ); ?>',
					name  : 'level_action_event',
					value : '',
					'data-placeholder': 'Select a Trigger',
					type  : 'select',
					options : [
						{value : '', text : ''},
						{value : 'added', text : 'Added to this Level'},
						{value : 'removed', text : 'Removed from this Level'},
						{value : 'cancelled', text : 'Cancelled from this Level'},
					],
					style : 'width: 100%',
					column : 'col-md-6',
				}
			</template>
			<template class="wlm3-form-group">
				{
					group_class : 'mb-1',
					label : '<?php esc_js_e( 'Then do this:', 'wishlist-member' ); ?>',
					name  : 'level_action_method',
					value : '',
					'data-placeholder': 'Select an Action',
					type  : 'select',
					options : [
						{value : '', text : ''},
						{value : 'add', text : 'Add to Level(s)'},
						{value : 'move', text : 'Move to Level(s)'},
						{value : 'cancel', text : 'Cancel from Level(s)'},
						{value : 'remove', text : 'Remove from Level(s)'},
						{value : 'add-ppp', text : 'Add to Pay Per Post'},
						{value : 'create-ppp', text : 'Create a Pay Per Post'},
						{value : 'remove-ppp', text : 'Remove Pay Per Post'},
					],
					style : 'width: 100%',
					column : 'col-md-6',
				}
			</template>
			<template class="wlm3-form-group">
				{
					group_class : 'mb-1',
					label : '<?php esc_js_e( 'Level(s)', 'wishlist-member' ); ?>',
					name  : 'action_levels',
					value : '',
					type  : 'select',
					'data-placeholder': 'Select Membership Level(s)',
					style : 'width: 100%',
					multiple : 1,
					column : 'col-md-12 wlm-levels-holder',
				}
			</template>
			<div class="col-md-12 inheritparent-holder">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Inherit Level Status', 'wishlist-member' ); ?>',
						name  : 'inheritparent',
						value : '1',
						uncheck_value : '',
						type  : 'toggle-switch',
						tooltip_size: 'lg',
						tooltip : '<p><?php esc_js_e( 'If enabled, the levels selected in the Add To section will inherit the status of the current level when a member is registered. This means if someone is cancelled from the current level and they were automatically added to another level, and this setting was enabled, they would also be cancelled from the level they were automatically added to.', 'wishlist-member' ); ?></p><p><?php esc_js_e( 'This must be turned on prior to member registration. This will not work retroactively.', 'wishlist-member' ); ?></p>',
						column : 'col-md-12 no-padding  inherit-levels-holder'
					}
				</template>
			</div>
			<div class="col-md-12 sched-options-holder">
				<label for=""><?php esc_html_e( 'Schedule', 'wishlist-member' ); ?></label>
				<div class="row no-gutters">
					<div class="col-6 schedule-type-holder">
						<div class="switch-toggle switch-toggle-wlm -compressed" style="margin-top: 3px;">
							<input class="toggle-radio toggle-radio-sched  sched-after" id="after" name="sched_toggle" type="radio" value="after" checked />
							<label for="after"><?php esc_html_e( 'After', 'wishlist-member' ); ?></label>
							<input class="toggle-radio toggle-radio-sched sched-ondate" id="ondate" name="sched_toggle" type="radio" value="ondate" />
							<label for="ondate"><?php esc_html_e( 'On', 'wishlist-member' ); ?></label>
							<a href="" class="btn btn-primary"></a>
						</div>
					</div>
					<div class="col-6 pl-3">
						<div class="form-inline date-ranger schedule-holder schedule-ondate-holder">
							<label class="sr-only" for=""><?php esc_html_e( 'Specific Date', 'wishlist-member' ); ?></label>
							<div class="date-ranger-container" style="width: 100%;">
								<input type="text" class="form-control wlm-datetimepicker schedule-ondate" name="sched_ondate" value="" style="width: 100%;">
								<i class="wlm-icons">date_range</i>
							</div>
						</div>
						<!--v4: start  -->
						<div class="form-inline -combo-form input-group schedule-holder schedule-after-holder">
							<label class="sr-only" for=""><?php esc_html_e( 'Fixed Term', 'wishlist-member' ); ?></label>
							<input type="number" min="0" name="sched_after_term" class="form-control text-center" placeholder="0" value="">
								<select class="form-control wlm-select" name="sched_after_period" style="width: 100px;">
									<option value="days"><?php esc_html_e( 'Day(s)', 'wishlist-member' ); ?></option>
									<option value="weeks"><?php esc_html_e( 'Week(s)', 'wishlist-member' ); ?></option>
									<option value="months"><?php esc_html_e( 'Month(s)', 'wishlist-member' ); ?></option>
									<option value="years"><?php esc_html_e( 'Year(s)', 'wishlist-member' ); ?></option>
								</select>
						</div>
						<!--v4: end  -->
					</div>
				</div>
				<div class="row mt-4">
					<div class="form-inline col-md-6">
						<label for="">Email Notification</label>
						<select class="form-control wlm-select wlm-levels-notification mt-2" name="level_email" style="width: 100%" placeholder="Email Notification">
							<option value="sendlevel">Use Level Notification Settings</option>
							<option value="send">Send Email Notification</option>
							<option value="dontsend">Do NOT Send Email Notification</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12 ppp-options-holder mt-2">
					<div class="row">
						<?php
							$args          = array( '_builtin' => false );
							$post_types    = get_post_types( $args, 'objects' );
							$enabled_types = (array) $this->get_option( 'protected_custom_post_types' );

							$ptypes = array(
								'post' => 'Posts',
								'page' => 'Pages',
							);
							foreach ( $post_types as $key => $value ) {
								if ( in_array( $value->name, $enabled_types ) ) {
									$ptypes[ $value->name ] = $value->label;
								}
							}
							?>
						<div class="form-inline col-md-6">
							<label for="">Post Type</label>
							<select class="form-control wlm-select mt-2" name="ppp_type" style="width: 100%" data-placeholder="Select Post Type">
								<?php foreach ( $ptypes as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-inline col-md-12 mt-2">
							<label for="">Select Content</label>
							<select class="form-control wlm-select mt-2" name="ppp_content" style="width: 100%">
							</select>
						</div>
						<div class="col-md-12 ppp-options-title-holder mt-2">
							<label for="">Content Title</label>
							<input type="text" name="ppp_title" class="form-control" value="">
							<small class="form-text text-muted">Supported Shortcodes: {name} {fname} {lname} {email} {username} {date} {time} </small>
						</div>
					</div>
			</div>
			<input type="hidden" name="level_action_id" value="">
		</div>
	</div>
	<div class="footer">
		<a data-toggle="modal" data-target="#level-actions" data-btype="cancel" href="#" class="btn -bare">
			<span><?php esc_html_e( 'Close', 'wishlist-member' ); ?></span>
		</a>
		<a data-toggle="modal" data-target="#level-actions" data-btype="save" href="" class="save-button btn -primary">
			<i class="wlm-icons">add</i>
			<span><?php esc_html_e( 'Add Action', 'wishlist-member' ); ?></span>
		</a>
	</div>
</div>
