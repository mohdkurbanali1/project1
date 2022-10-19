<div id="idevaffiliate-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="idevaffiliate-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="100">
			<col width="100">
			<col width="1%">
			<col width="100">
			<col width="100">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th rowspan="2">Membership Level</th>
				<th colspan="2" class="text-center">Price</th>
				<th colspan="3" class="text-center -has-left-border">Commission</th>
				<th rowspan="2"></th>
			</tr>
			<tr>
				<th class="text-center">Initial</th>
				<th class="text-center">Recurring</th>
				<th class="text-center -has-left-border" style="white-space: nowrap">Fixed</th>
				<th class="text-center">Initial</th>
				<th class="text-center">Recurring</th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr>
				<td><a href="#" data-toggle="modal" data-target="#idevaffiliate-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="idev-values-initial-{%- level.id %}" class="text-center"></td>
				<td id="idev-values-recur-{%- level.id %}" class="text-center"></td>
				<td id="idev-values-fixed-{%- level.id %}" class="text-center"></td>
				<td colspan="2" class="-commission-idev-{%- level.id %}" style="text-align: center; display:none"><em>Payout levels set in iDevAffiliate</em></td>
				<td id="idev-values-initialc-{%- level.id %}" class="text-center -commission-fixed-{%- level.id %}" style="display:none"></td>
				<td id="idev-values-recurc-{%- level.id %}" class="text-center -commission-fixed-{%- level.id %}" style="display:none"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#idevaffiliate-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#idevaffiliate-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#idevaffiliate-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#idevaffiliate-lists-table').append(html);
		return false;
	});
</script>