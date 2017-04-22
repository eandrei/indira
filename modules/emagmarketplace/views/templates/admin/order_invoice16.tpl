<div class="panel">
	<div class="panel-heading">
		<i class="icon-money"></i>
		eMAG Marketplace Invoice
	</div>
	<form action="{$link->getAdminLink('AdminEmagMarketplaceInvoices')}&submitAddattachment=1" method="post" enctype="multipart/form-data" class="form-horizontal hidden-print">
		{if ($invoice_url)}
		<div>
			An external invoice has been uploaded for this order: <a href="{$invoice_url}" target="_blank"><strong>Download Invoice</strong></a>
		</div>
		<br />
		{/if}
		<div class="row">
			Upload an external invoice for the current order here:<br />
			<br />
			<div class="col-lg-10"><input type="file" name="file" /><input type="hidden" name="id_order" value="{$id_order}" class="form-control" /></div>
			<div class="col-lg-2"><button type="submit" name="submitAddattachment" class="btn btn-primary">Upload Invoice</button></div>
		</div>
	</form>
</div>