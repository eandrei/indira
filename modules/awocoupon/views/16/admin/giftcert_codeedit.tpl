{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<style>
.admintable tr.columnheaders td.key { text-align: center; }
.admintable tr.columnheaders td.key2 { font-weight;900;background-color:#bbbbbb;color:#ffffff;text-align: center; 	border-bottom: 1px solid #e9e9e9; border-right: 1px solid #e9e9e9; }
.admintable tr.columndata td { border:1px solid #cccccc; color:#777777; }
</style>


<div class="panel awocoupon">
	<h3><i class="icon-tag"></i> {l s='New Gift Certificate Code' mod='awocoupon'}</h3>


	<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="" enctype="multipart/form-data">


		<div class="width-100">
			<fieldset class="adminform"><legend>{l s='Product' mod='awocoupon'}</legend>
				{$lists.productlist}
			</fieldset>
		</div>
		<br /><br />
		<div class="width-100">
			<fieldset class="adminform"><legend>{l s='File' mod='awocoupon'}</legend>
				<div><input type="checkbox" value="1" name="exclude_first_row" {if $exclude_first_row=='1'} CHECKED {/if}>{l s='Exclude the First Row' mod='awocoupon'}</div>
				<div><input type="checkbox" value="1" name="store_none_errors" {if $store_none_errors == '1'} CHECKED {/if}>{l s='Even with Errors in the batch, Save coupons with no Errors' mod='awocoupon'}</div>
				<div><input type="file" name="file" style="width:100%;"></div>
			</fieldset>
		</div>
		<div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>
		<input type="hidden" name="module" value="awocoupon" />
		<input type="hidden" name="view" value="giftcert" />
		<input type="hidden" name="layout" value="codeedit" />
		<input type="hidden" name="task" value="store" />
	</form>



	<br /><br /><br /><br />
	<div >
		<fieldset class="adminform"><legend>{l s='CSV Spreadsheet Format' mod='awocoupon'}</legend>
			<table class="admintable" style="width:300px;">
			<tr class="columnheaders" valign="top">
				<td class="key">{l s='Coupon Code' mod='awocoupon'}</td>
				<td class="key">{l s='Status' mod='awocoupon'}</td>
				<td class="key">{l s='Admin Note' mod='awocoupon'}</td>
			</tr>
			<tr class="columnheaders" >
				<td class="key2">A</td>
				<td class="key2">B</td>
				<td class="key2">C</td>
			</tr>
			<tr class="columndata">
				<td>23MXRTC45</td>
				<td>{l s='Active' mod='awocoupon'}</td>
				<td>&nbsp;</td>
			</tr>
			<tr class="columndata">
				<td>y8B3A0E8</td>
				<td>{l s='Inactive' mod='awocoupon'}</td>
				<td>my admin note</td>
			</tr>
			<tr class="columndata"><td colspan="12" style="border:0;">......</td></tr>
			</table>
		</fieldset>
	</div>

	{include file="footer_toolbar.tpl"}
</div>