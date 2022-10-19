<p><?php esc_html_e( 'A contact will be added to the Ontraport contacts once a member is added to an enabled Membership Level.', 'wishlist-member' ); ?></p>
<div id="ontraport-lists-table" class="table-wrapper"></div>
<script type="text/template" id="ontraport-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="1%">
			<col width="30%">
			<col width="30%">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Membership Level</th>
				<th>Enabled</th>
				<th>Add Tags</th>
				<th>Add Sequences</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#ontraport-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="ontraport-enable-{%- level.id %}" class="text-center"></td>
				<td id="ontraport-tags-{%- level.id %}"></td>
				<td id="ontraport-sequences-{%- level.id %}"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#ontraport-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#ontraport-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#ontraport-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#ontraport-lists-table').append(html);
		return false;
	});
</script>
