{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<script language="javascript" type="text/javascript">
<!--
var base_url = "{$ajax_url}";

$(document).ready(function() {
	$( "#coupon_id_search" )
		.autocomplete({
			source: function( request, response ) {
				$.getJSON( 
					base_url, 
					{ option:"awocoupon", task:'ajax_elements', type:'coupons', tmpl:'component', no_html:1,term: request.term }, 
					response 
				);
			},
			minLength: 2,
			selectFirst: true,
			delay:0,
			select: function( event, ui ) { if(ui.item) document.adminForm.coupon_id.value = ui.item.id; }
		})
		.attr("parameter_id", document.adminForm.coupon_id.value)
		.bind("empty_value", function(event){ document.adminForm.coupon_id.value = ''; })
		.bind("check_user", function(event){ return; })
	;
});



function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function submitbutton(pressbutton) {
	var form = document.adminForm;

	var err = '';
	
	if(form.coupon_id.value == '') err += "\n{l s='Coupon Code: please select an item' mod='awocoupon'}";
	if(!isUnsignedInteger(form.ordering.value)) err += "\n{l s='Ordering: please select an item' mod='awocoupon'}";
	if(form.published.value=='' || (form.published.value!='1' && form.published.value!='-1')) err += "\n{l s='Published: please enter a valid value' mod='awocoupon'}";
	
	if(err != '') {
		alert(err);
		submited = false;
		return false;
	}
	return true; 

}

//-->
</script>
{literal}
<style>
.awocoupon td.key {min-width:200px;}
</style>
{/literal}

<div class="panel awocoupon">
	<h3><i class="icon-tag"></i> {if $row->id>0}{l s='Edit' mod='awocoupon'}{else}{l s='New' mod='awocoupon'}{/if} {l s='Automatic Coupon' mod='awocoupon'}</h3>


	<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="return submitbutton();">

	<div class="width-100">
		<table class="admintable">
			<tr>
				<td class="key" nowrap><label class="control-label">{l s='Coupon code' mod='awocoupon'}</label></td>
				<td>{if $row->id > 0}
						{$row->coupon_code}
					{else}
						<input type="text" size="30" value="" id="coupon_id_search" class="inputbox ac_input"/>
					{/if}
					<input type="hidden" name="coupon_id" value="{$row->coupon_id}" />
				</td>
			</tr>
			<tr>
				<td class="key" nowrap><label class="control-label">{l s='Published' mod='awocoupon'}</label></td>
				<td>{$lists.published}</td>
			</tr>
			<tr>
				<td class="key" nowrap><label class="control-label">{l s='Ordering' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="ordering" size="10" value="{$row->ordering}" /></td>
			</tr>
		</table>
	
	</div>

	<div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>

	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="couponauto" />
	<input type="hidden" name="layout" value="edit" />
	<input type="hidden" name="task" value="store" />
	<input type="hidden" name="id" value="{$row->id}" />
	<input type="hidden" name="cid[]" value="{$row->id}" />
	</form>

	{include file="footer_toolbar.tpl"}
</div>


