<div class="panel">
	<div class="panel-heading">
		<i class="icon-truck"></i>
		eMAG Marketplace AWB
	</div>
	<form class="form-horizontal hidden-print">
		{if ($awb_url)}
		<div>
			An eMAG Marketplace AWB has been generated for this order: <a href="{$awb_url}" target="_blank"><strong>Download AWB</strong></a>
		</div>
		<br />
		{/if}
		<p>Please choose a valid shipping locality from the eMAG Marketplace database, before generating an eMAG AWB:</p>
		<div class="row">
			<div class="col-lg-2">Shipping Locality:</div>
			<div class="col-lg-8"><input type="text" name="emag_locality_name" value="{$emag_locality_name}" class="form-control" /><input type="hidden" name="emag_locality_id" value="{$emag_locality_id}" /></div>
			<div class="col-lg-2"><button class="btn btn-primary" type="button" onClick="generateEmagAWB();">Generate eMAG AWB</button></div>
		</div>
	</form>
</div>
<script type="text/javascript">
$(document).ready(function()
{
	$('[name="emag_locality_name"]').autocomplete('{$link->getAdminLink("AdminEmagMarketplaceMain")}', {
		minChars: 1,
		matchContains: true,
		dataType: "json",
		formatItem: function(data, i, max, value, term) {
			return value;
		},
		parse: function(data) {
			var mytab = new Array();
			for (var i = 0; i < data.length; i++)
				mytab[mytab.length] = { data: data[i], value: data[i].name };
			return mytab;
		},
		extraParams: {
			ajax: "1",
			token: "{getAdminToken tab='AdminEmagMarketplaceMain'}",
			tab: "AdminEmagMarketplaceMain",
			action: "searchEmagLocalities"
		}
	}).result(function(event, data) {
		$('[name="emag_locality_name"]').val(data.name);
		$('[name="emag_locality_id"]').val(data.id);
    });
});

function generateEmagAWB()
{
	if (!confirm('Are you sure you want to generate an eMAG AWB and mark this order as finalized on the eMAG Marketplace system?'))
		return;
		
	doAdminAjax({
		"action": "GenerateEmagAWB",
		"token": "{getAdminToken tab='AdminEmagMarketplaceMain'}",
		"controller": "AdminEmagMarketplaceMain",
		"ajax": 1,
		"id_order": {$id_order},
		"emag_locality_id": $('[name="emag_locality_id"]').val()
	}, function(data)
	{
		data = $.parseJSON(data);
		if (data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations, 900000);
			alert('An eMAG AWB for this order has been generated successfully! This page will now refresh itself!');
			this.location.href = this.location.href;
		}
		else
			showErrorMessage(data.error, 900000);
	});
}
</script>