{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

<div class="panel awocoupon">
	<h3><i class="icon-tag"></i> {l s='Reports' mod='awocoupon'}</h3>

	<br />
	<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="" target="_blank">

		<div class="width-100">
			<fieldset class="adminform">
				<legend>{l s='Reports' mod='awocoupon'}</legend>
				<select name="report_type">
					<option value="coupon_list">{l s='Coupon List' mod='awocoupon'}</option>
					<option value="purchased_giftcert_list">{l s='Purchased Gift Certificate List' mod='awocoupon'}</option>
					<option value="coupon_vs_total">{l s='Coupon Usage vs. Total Sales' mod='awocoupon'}</option>
					<option value="coupon_vs_location">{l s='Couon Usage vs. Location' mod='awocoupon'}</option>
					<option value="history_uses_coupons">{l s='History of uses - Coupons' mod='awocoupon'}</option>
					<option value="history_uses_giftcerts">{l s='History of uses - Gift Certificates' mod='awocoupon'}</option>
				</select>
			</fieldset>
		</div>
		<br /><br />

		<div class="width-100">
			<fieldset class="adminform">
				<legend>{l s='Order Details' mod='awocoupon'}</legend>
				<table class="admintable">
					<tr><td class="key" nowrap><label class="control-label">{l s='Start Date' mod='awocoupon'}</label></td>
						<td><input type="text" name="start_date" id="start_date" /><i style="color:#777777;">(YYYY-MM-DD)</i></td>
					</tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='End Date' mod='awocoupon'}</label></td>
						<td><input type="text" name="end_date" id="end_date" /><i style="color:#777777;">(YYYY-MM-DD)</i></td>
					</tr>
					<tr><td class="key" nowrap valign="top"><label class="control-label">{l s='Status' mod='awocoupon'}</label></td><td>{$lists.order_status}<input type="button" onclick="clearbox()" value="{l s='Clear' mod='awocoupon'}" /></td></tr>
				</table>
			</fieldset>
		</div>
		<br /><br />
								

		<div class="width-100">
			<fieldset class="adminform">
				<legend>{l s='Coupon Details' mod='awocoupon'}</legend>
				<table class="admintable">
					{if $lists.shop_list}
						<tr><td class="key" nowrap><label class="control-label">{l s='Shop' mod='awocoupon'}</label></td><td>{$lists.shop_list}</td></tr>
					{/if}
					<tr><td class="key" nowrap><label class="control-label">{l s='Function Type' mod='awocoupon'}</label></td><td>{$lists.function_type}</td></tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='Percent or Amount' mod='awocoupon'}</label></td><td>{$lists.coupon_value_type}</td></tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='Discount Type' mod='awocoupon'}</label></td><td>{$lists.discount_type}</td></tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='Template' mod='awocoupon'}</label></td><td>{$lists.templatelist}</td></tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='Status' mod='awocoupon'}</label></td><td>{$lists.published}</td></tr>
					<tr><td class="key" nowrap><label class="control-label">{l s='Gift Certificate Product' mod='awocoupon'}</label></td><td>{$lists.giftcert_product}</td></tr>
				</table>
			</fieldset>
		</div>

		<br /><div class="margin-form"><input type="submit" value="   {l s='Run Report' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>

		<input type="hidden" name="module" value="awocoupon" />
		<input type="hidden" name="view" value="report" />
		<input type="hidden" name="task" value="runreport" />
	</form>

	<script language="javascript" type="text/javascript">
	<!--
	function clearbox(val) {
		var form = document.adminForm;
		form.elements["order_status[]"].selectedIndex = -1;
	}
	//-->
	</script>

</div>

