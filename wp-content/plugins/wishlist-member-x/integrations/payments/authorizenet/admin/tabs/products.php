<p>
	<?php esc_html_e( 'Create a Simple Checkout item for each Membership Level using the Item IDs below.', 'wishlist-member' ); ?>
</p>
<div id="authorizenet-products-table"></div>
<script type="text/template" id="authorizenet-products-template">
	<h3 style="margin-bottom: 5px">{%= data.label %}</h3>
	<div class="table-wrapper -no-shadow">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th width="30%">Item ID</th>
				</tr>
			</thead>
			<tbody>
				{% _.each(data.levels, function(level) { %}
				<tr>
					<td>{%= level.name %}</td>
					<td>{%= level.id %}</td>
				</tr>
				{% }); %}
			</tbody>
		</table>
	</div>
</script>

<script type="text/javascript">
	$('#authorizenet-products-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#authorizenet-products-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#authorizenet-products-table').append(html);
	});
</script>
