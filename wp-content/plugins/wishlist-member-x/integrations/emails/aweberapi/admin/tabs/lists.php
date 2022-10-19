<div id="aweberapi-lists-table" class="table-wrapper"></div>
<script type="text/template" id="aweberapi-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="20%">
			<col width="20%">
			<col width="250">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Membership Level', 'wishlist-member' ); ?></th>
				<th><?php esc_html_e( 'List', 'wishlist-member' ); ?></th>
				<th><?php esc_html_e( 'Ad Tracking', 'wishlist-member' ); ?></th>
				<th><?php esc_html_e( 'Action if Member is Removed or Cancelled from Level', 'wishlist-member' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr>
				<td><a href="#" data-toggle="modal" data-target="#aweberapi-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="aweberapi-lists-{%- level.id %}"></td>
				<td id="aweberapi-adtracking-{%- level.id %}"></td>
				<td id="aweberapi-unsubscribe-{%- level.id %}"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#aweberapi-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
(function($){
	window.setTimeout(function(obj) {
		$('#aweberapi-lists-table').empty();
		$.each(all_levels, function(k, v) {
			var data = {
				label : post_types[k].labels.name,
				levels : v
			}
			var tmpl = _.template($('script#aweberapi-lists-template').html(), {variable: 'data'});
			var html = tmpl(data);
			$('#aweberapi-lists-table').append(html);
			return false;
		});

		$('#aweberapi-lists-table').transformers();
		$('#aweberapi-lists-table').set_form_data(WLM3ThirdPartyIntegration.aweberapi);
	}, 2000, this);
}(jQuery));
</script>
