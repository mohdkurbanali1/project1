<?php
foreach ( $wpm_levels as $level_id => $level ) :
	?>
<div
	data-process="modal"
	id="drip2-tags-<?php echo esc_attr( $level_id ); ?>-template" 
	data-id="drip2-tags-<?php echo esc_attr( $level_id ); ?>"
	data-label="drip2-tags-<?php echo esc_attr( $level_id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Tags for <?php echo esc_attr( $level['name'] ); ?>"
	data-show-default-footer="1"
	data-classes="modal-lg"
	style="display:none">
	<div class="body">
		<div class="row">
			<div class="col-md-12">
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#when-added-<?php echo esc_attr( $level_id ); ?>">When Added</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-cancelled-<?php echo esc_attr( $level_id ); ?>">When Cancelled</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-reregistered-<?php echo esc_attr( $level_id ); ?>">When Re-Registered</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-removed-<?php echo esc_attr( $level_id ); ?>">When Removed</a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="row tab-pane active in" id="when-added-<?php echo esc_attr( $level_id ); ?>">
				<div class="col-md-12">
					<div class="form-check pl-0">
						<input type="checkbox" name="<?php echo esc_attr( $level_id ); ?>[add][record_event]" value="1" uncheck_value="0">
						<label>Fire Add Event</label>
					</div>
					<br>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Apply Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[add][apply_tag][]"></select>
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Remove Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[add][remove_tag][]"></select>
					</div>
				</div>
			</div>
			<div class="row tab-pane" id="when-cancelled-<?php echo esc_attr( $level_id ); ?>">
				<div class="col-md-12">
					<div class="form-check pl-0">
						<input type="checkbox" name="<?php echo esc_attr( $level_id ); ?>[cancel][record_event]" value="1" uncheck_value="0">
						<label>Fire Cancel Event</label>
					</div>
					<br>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Apply Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[cancel][apply_tag][]"></select>
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Remove Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[cancel][remove_tag][]"></select>
					</div>
				</div>
			</div>
			<div class="row tab-pane" id="when-reregistered-<?php echo esc_attr( $level_id ); ?>">
				<div class="col-md-12">
					<div class="form-check pl-0">
						<input type="checkbox" name="<?php echo esc_attr( $level_id ); ?>[rereg][record_event]" value="1" uncheck_value="0">
						<label>Fire Re-Registration Event</label>
					</div>
					<br>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Apply Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[rereg][apply_tag][]"></select>
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Remove Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[rereg][remove_tag][]"></select>
					</div>
				</div>
			</div>
			<div class="row tab-pane" id="when-removed-<?php echo esc_attr( $level_id ); ?>">
				<div class="col-md-12">
					<div class="form-check pl-0">
						<input type="checkbox" name="<?php echo esc_attr( $level_id ); ?>[remove][record_event]" value="1" uncheck_value="0">
						<label>Fire Remove Event</label>
					</div>
					<br>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Apply Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[remove][apply_tag][]"></select>
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Remove Tags', 'wishlist-member' ); ?></label>
						<select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr( $level_id ); ?>[remove][remove_tag][]"></select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
