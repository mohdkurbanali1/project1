<p><?php esc_html_e( 'Infusionsoft Tags allow tags to be applied to members when they are Added To, Cancelled From and Removed From Membership Levels.', 'wishlist-member' ); ?></p>
<div id="infusionsoft-lists-table" class="table-wrapper"></div>
<script type="text/template" id="infusionsoft-lists-template">
	<h3 style="margin-bottom: 5px">{%= data.label %}</h3>
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Name</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#infusionsoft-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td class="text-right">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#infusionsoft-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit Tag Settings"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>
