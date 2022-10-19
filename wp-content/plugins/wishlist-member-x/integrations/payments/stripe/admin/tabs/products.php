<div id="stripe-products-table" class="table-wrapper"></div>
<script type="text/template" id="stripe-products-template">
	<h3 class="mt-4 mb-2">{%= data.label %}</h3>
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="50%">
			<col width="100">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'wishlist-member' ); ?></th>
				<th class="text-left"><?php esc_html_e( 'Amount/Stripe Plan', 'wishlist-member' ); ?></th>
				<th class="text-center"><?php esc_html_e( 'Button Code', 'wishlist-member' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover" valign="top">
				<td class="align-top"><a href="#" data-toggle="modal" data-target="#products-stripe-{%- level.id %}">{%= level.name %}</a></td>
				<td class="align-top">
					<span id="stripe-product-{%- level.id %}" class="stripe-price" href="#">
						{% print( wlm3_stripe_display_plans(WLM3ThirdPartyIntegration.stripe.stripeconnections[level.id] ) ); %}
					</span>
				</td>
				<td class="text-center align-top">
					<a href="" class="wlm-popover clipboard tight btn wlm-icons md-24 -icon-only" title="Copy Button Code" data-text='[wlm_stripe_btn sku={%- level.id %} button_label="" pay_button_label="" coupon="1"]'><span>code</span></a>
				</td>
				<td class="text-right align-top">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#products-stripe-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>
