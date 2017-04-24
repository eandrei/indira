{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<script language="javascript" type="text/javascript">
<!--

function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function submitbutton(pressbutton) {
	var form = document.adminForm;

	var err = '';
		
	if(form.template.value == '' || !isUnsignedInteger(form.template.value)) err += "\n{l s='Coupon Template: please make a selection' mod='awocoupon'}";
	if(form.number.value=='' || !isUnsignedInteger(form.number.value) || form.number.value<1) err += "\n{l s='Plese enter a valid number' mod='awocoupon'}";
		

	if(err != '') { alert(err); return false; }
	return true; 

}

//-->
</script>


<br />
<br />

<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" onsubmit="return submitbutton();">

<div class="width-100">
	<fieldset>
		<table class="admintable">
			<tr>
				<td class="key" nowrap><label>{l s='Coupon Template' mod='awocoupon'}</label></td>
				<td>{$lists.templatelist}</td>
			</tr>
			<tr>
				<td class="key" nowrap><label>{l s='Number' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="number" size="20" maxlength="10" value="{$row->number}" /></td>
			</tr>
		</table>
	</fieldset>
</div>

<div class="margin-form">
	<input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="submit" class="button">
</div>


<input type="hidden" name="module" value="awocoupon" />
<input type="hidden" name="view" value="coupon" />
<input type="hidden" name="layout" value="generateedit" />
<input type="hidden" name="task" value="store" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="cid[]" value="" />
</form>
<div class="clr"></div>

<a href="{$back_url}"><img src="{$back_img}"> Back to list</a>


