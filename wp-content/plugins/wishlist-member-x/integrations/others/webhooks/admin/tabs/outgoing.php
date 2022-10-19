<div class="table-wrapper">
	<table id="wlm-webhooks-outgoing" class="wlm-webhooks table table-striped">
		<colgroup>
			<col>
			<col width="15%">
			<col width="15%">
			<col width="15%">
			<col width="15%">
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
				<th><?php esc_html_e( 'Membership Level', 'wishlist-member' ); ?></th>
				<th class="text-center"><?php esc_html_e( 'Add', 'wishlist-member' ); ?></th>
				<th class="text-center"><?php esc_html_e( 'Remove', 'wishlist-member' ); ?></th>
				<th class="text-center"><?php esc_html_e( 'Cancel', 'wishlist-member' ); ?></th>
				<th class="text-center"><?php esc_html_e( 'Uncancel', 'wishlist-member' ); ?></th>
				<th></th>
			</tr>
		</thead>
	</table>
</div>
<script type="text/template" id="wlm-webhooks-outoing-template">
	{% _.each(data.levels, function(level) { %}
	{%
		// ensure we have data to process
		data.outgoing[level.id] = $.extend( { add : '', remove : '', cancel : '', uncancel : ''}, data.outgoing[level.id] );
		// regexp to search for non-empty lines
		var lines_regexp = /[^\n\r]+/g;
	%}
	<tr class="button-hover" data-id="{%- level.id %}">
		<td><a data-toggle="modal" href="#webhooks-outgoing-modal-{%- level.id %}">{%- level.name %}</a></td>
		<td class="text-center">{%= ((data.outgoing[level.id].add.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
		<td class="text-center">{%= ((data.outgoing[level.id].remove.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
		<td class="text-center">{%= ((data.outgoing[level.id].cancel.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
		<td class="text-center">{%= ((data.outgoing[level.id].uncancel.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
		<td class="text-right">
			<div class="btn-group-action">
				<a data-toggle="modal" href="#webhooks-outgoing-modal-{%- level.id %}" class="btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
			</div>
			<div class="btn-group-action">
			</div>
		</td>
	</tr>
	{% }); %}
</script>
