<br />
<fieldset>
	<legend><img src="../img/admin/details.gif" /> eMAG Marketplace Invoice</legend>
	<form action="{$link->getAdminLink('AdminEmagMarketplaceInvoices')}&submitAddattachment=1" method="post" enctype="multipart/form-data">
		{if ($invoice_url)}
		<div>
			An external invoice has been uploaded for this order: <a href="{$invoice_url}" target="_blank"><strong>Download Invoice</strong></a>
		</div>
		<br />
		{/if}
		<div>
			Upload an external invoice for the current order here:<br />
			<br />
			<input type="file" name="file" /><input type="hidden" name="id_order" value="{$id_order}" />
			<input class="button" type="submit" name="submitAddattachment" value="   Upload Invoice   " />
		</div>
	</form>
</fieldset>