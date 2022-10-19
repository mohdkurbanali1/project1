<div class="table-wrapper">
	<table id="woocommerce-products" class="table table-striped">
		<colgroup>
			<col width="35%">
			<col>
			<col width="80">
		</colgroup>
		<tbody>
			<tr>
				<td colspan="100">
					<?php esc_html_e( 'Loading...', 'wishlist-member' ); ?>
				</td>
			</tr>
		</tbody>
		<thead>
			<tr>
				<th><?php esc_html_e( 'WooCommerce Product', 'wishlist-member' ); ?></th>
				<th><?php esc_html_e( 'Access', 'wishlist-member' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="pt-3" colspan="7">
					<p><?php esc_html_e( 'No linked products.', 'wishlist-member' ); ?></p>
				</td>
			</tr>
			<tr>
				<td class="pt-3 text-center" colspan="7">
					<a href="#" class="btn -success -add-btn -condensed">
						<i class="wlm-icons">add</i>
						<span><?php esc_html_e( 'Link New Product', 'wishlist-member' ); ?></span>
					</a>
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<script type="text/template" id="woocommerce-products-template2">
	{% _.each(data.products, function(levels, product_id) { %}
	{% if( product_id in WLM3ThirdPartyIntegration.woocommerce.products ) { %}
	<tr class="button-hover" data-access="{%- levels.join() %}" data-id="{%- product_id %}">
		<td><a href="#" class="-edit-btn">{%- WLM3ThirdPartyIntegration.woocommerce.products[product_id].name %}</a></td>
		<td>
			{% var lnames = []; _.each(levels, function(level) { lnames.push(all_levels_flat[level].name) }); %}
			{%- lnames.join(', ') %}
		</td>
		<td class="text-right">
			<div class="btn-group-action">
				<a href="#" class="btn -edit-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
				<a href="#" class="btn wlm-icons md-24 -icon-only -del-btn"><span>delete</span></a>
			</div>
			<div class="btn-group-action">
			</div>
		</td>
	</tr>
	{% } %}
	{% }); %}
</script>

<div
	data-process="modal"
	id="products-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="products-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="products-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="Link <?php echo esc_attr( $config['name'] ); ?> Product"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<input type="hidden" name="action" value="wlm3_save_woocommerce_product">
		<input type="hidden" name="old_access" value="">
		<input type="hidden" name="old_id" value="">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'WooCommerce Product', 'wishlist-member' ); ?>',
					type : 'select',
					name : 'id',
					options : WLM3ThirdPartyIntegration.woocommerce.products,
					'data-placeholder' : '<?php esc_js_e( 'Choose a Product', 'wishlist-member' ); ?>',
					value : '-1',
					column : 'col-12',
					style : 'width: 100%',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Access', 'wishlist-member' ); ?>',
					type : 'select',
					name : 'access',
					grouped : 1,
					options : all_levels_select_options,
					multiple : true,
					'data-placeholder' : '<?php echo count( $all_levels ) > 1 ? __( 'Choose a Membership Level or Pay Per Post', 'wishlist-member' ) : __( 'Choose a Membership Level', 'wishlist-member' ); ?>',
					value : '-1',
					column : 'col-12',
					style : 'width: 100%',
				}
			</template>
		</div>
	</div>
</div>


