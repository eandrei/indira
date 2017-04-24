<br />
<fieldset>
	<legend><img src="../img/admin/tab-orders.gif" /> eMAG Marketplace Order</legend>
	<form method="post">
		<div>This is eMAG Marketplace order: <strong>#{$emag_order_id}</strong>. Payment mode for this order is: <strong>{$payment_mode}</strong> and payment status is: <strong>{$payment_status}</strong>.</div>
		{if (count($missing_products))} <br />
		<div>
			<p style="color:red;">The following products are missing from the original eMAG Marketplace order (because not enough stock was available):</p>
			<table class="table" width="100%" cellspacing="0" cellpadding="0">
				<colgroup>
				<col width="">
				<col width="10%">
				</colgroup>
				<thead>
					<tr>
						<th>Name</th>
						<th>Quantity</th>
					</tr>
				</thead>
				<tbody>
				
				{foreach from=$missing_products item=line}
				<tr>
					<td>{$line.name}</td>
					<td>{$line.quantity}</td>
				</tr>
				{/foreach}
					</tbody>
				
			</table>
		</div>
		{/if}<br>
		Clich this button if you want to refresh the current order from eMAG: <input class="button" type="button" value="   Refresh Order   " onClick="refreshEmagOrder();" />
	</form>
</fieldset>
<script type="text/javascript">
function refreshEmagOrder()
{
	if (!confirm('Are you sure you want to refresh the current order from the eMAG Marketplace system?'))
		return;
		
	doAdminAjax({
		"action": "refreshEmagOrder",
		"token": "{getAdminToken tab='AdminEmagMarketplaceMain'}",
		"controller": "AdminEmagMarketplaceMain",
		"ajax": 1,
		"id_order": {$id_order}
	}, function(data)
	{
		data = $.parseJSON(data);
		if (data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations, 900000);
			alert('Order refreshed successfully! This page will now reload!');
			this.location.href = this.location.href;
		}
		else
			showErrorMessage(data.error, 900000);
	});
}
</script>