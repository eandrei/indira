{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

<script language="javascript" type="text/javascript">
<!--

$(document).ready(function() {
});

function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function submitbutton() {
	var form = document.adminForm;

	var err = '';
	
	if(form.coupon_id.value == '') err += "\n{l s='Coupon Code: please make a selection' mod='awocoupon'}";
	if($.trim(form.user_email.value)=='') err += "\n{l s='User: please enter a value' mod='awocoupon'}";		

	if(err != '') { 
		alert(err); 
		submited = false;
		return false; 
	}
	return true;

}

//-->
</script>

<h2>{if $row->id>0}{l s='Edit' mod='awocoupon'}{else}{l s='New' mod='awocoupon'}{/if} {l s='Coupon Usage' mod='awocoupon'}</h2>


<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="return submitbutton();">


<table class="admintable">
	<tr>
		<td class="key" nowrap><label>{l s='Coupon Code' mod='awocoupon'}</label></td>
		<td>{$lists.couponlist}</td>
	</tr>
	<tr>
		<td class="key" nowrap><label>{l s='User Email' mod='awocoupon'}</label></td>
		<td><input class="inputbox" type="text" name="user_email" size="30" maxlength="255" value="{$row->user_email}" /></td>
	</tr>
	<tr>
		<td class="key" nowrap><label>{l s='Product Discount' mod='awocoupon'}</label></td>
		<td><input class="inputbox" type="text" name="coupon_discount" size="30" maxlength="255" value="{$row->coupon_discount}" /></td>
	</tr>
	<tr>
		<td class="key" nowrap><label>{l s='Shipping Discount' mod='awocoupon'}</label></td>
		<td><input class="inputbox" type="text" name="shipping_discount" size="30" maxlength="255" value="{$row->shipping_discount}" /></td>
	</tr>
	<tr>
		<td class="key" nowrap><label>{l s='Order ID' mod='awocoupon'}</label></td>
		<td><input class="inputbox" type="text" name="order_id" size="30" maxlength="255" value="{$row->order_id}" /></td>
	</tr>
</table>


<div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>

<input type="hidden" name="module" value="awocoupon" />
<input type="hidden" name="view" value="history" />
<input type="hidden" name="layout" value="hist" />
<input type="hidden" name="task" value="store" />
<input type="hidden" name="id" value="{$row->id}" />
<input type="hidden" name="cid[]" value="" />
</form>



