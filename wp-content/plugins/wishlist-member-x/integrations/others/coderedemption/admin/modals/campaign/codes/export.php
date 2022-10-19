<div role="tabpanel" class="tab-pane" id="coderedemption-campaign-modal-codes-export">
  <div class="row">
	<div class="col-12">
	  <label>
		<?php esc_html_e( 'Choose which codes to export', 'wishlist-member; ?>' ); ?>
	  </label>
	</div>
	<template class="wlm3-form-group">
	  {
		type: 'select',
		id: 'coderedemption-code-export-status',
		options: [
		  {value : '', text : '<?php esc_js_e( 'All', 'wishlist-member' ); ?>'},
		  {value : '0', text : '<?php esc_js_e( 'Available', 'wishlist-member' ); ?>'},
		  {value : '1', text : '<?php esc_js_e( 'Redeemed', 'wishlist-member' ); ?>'},
		  {value : '2', text : '<?php esc_js_e( 'Cancelled', 'wishlist-member' ); ?>'},
		],
		column: 'col-auto'
	  }
	</template>
	<div class="col-auto pl-0">
	  <button id="coderedemption-code-export-button" type="button" class="btn -default -condensed"><?php esc_html_e( 'Export', 'wishlist-member' ); ?></button>
	</div>
  </div>
</div>
