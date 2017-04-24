{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<script language="javascript" type="text/javascript">
<!--
var base_url = "{$ajax_url}";

$(document).ready(function() {
	$( "#product_id_search" )
		.autocomplete({
			source: function( request, response ) {
				$.getJSON( 
					base_url, 
					{ option:"awocoupon", task:'ajax_elements', type:'productgift', tmpl:'component', no_html:1,term: request.term }, 
					response 
				);
			},
			minLength: 2,
			selectFirst: true,
			delay:0,
			select: function( event, ui ) { if(ui.item) document.adminForm.product_id.value = ui.item.id; }
		})
		.attr("parameter_id", document.adminForm.product_id.value)
		.bind("empty_value", function(event){ document.adminForm.product_id.value = ''; })
		.bind("check_user", function(event){ return; })
	;
});



function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function submitbutton(pressbutton) {
	var form = document.adminForm;

	var err = '';
	
	if(form.product_id.value == '') err += "\n{l s='Product: please select an item' mod='awocoupon'}";
	if(form.coupon_template_id.value == '') err += "\n{l s='Template: please select an item' mod='awocoupon'}";
	if(form.profile_id.value != '' && !isUnsignedInteger(form.profile_id.value)) err += "\n{l s='Profile: please select an item' mod='awocoupon'}";
	if(form.published.value=='' || (form.published.value!='1' && form.published.value!='-1')) err += "\n{l s='Published: please enter a valid value' mod='awocoupon'}";
	
	if($.trim(form.expiration_number.value)!='' || form.expiration_type.value!='') {
		if($.trim(form.expiration_number.value)=='' || form.expiration_type.value=='') err += "\n{l s='Expiration: please enter a valid value' mod='awocoupon'}";
		else if(!isUnsignedInteger($.trim(form.expiration_number.value)) || $.trim(form.expiration_number.value)<1) err += "\n{l s='Expiration: please enter a valid value' mod='awocoupon'}";
		else if(form.expiration_type.value!='day' && form.expiration_type.value!='month' && form.expiration_type.value!='year' ) err += "\n{l s='Expiration: please enter a valid value' mod='awocoupon'}";
	}
	

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
	<h3><i class="icon-tag"></i> {if $row->id>0}{l s='Edit' mod='awocoupon'}{else}{l s='New' mod='awocoupon'}{/if} {l s='Gift Certificate' mod='awocoupon'}</h3>


	<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="return submitbutton();">

	<div class="width-100">
		<fieldset class="adminform"><legend>{l s='General' mod='awocoupon'}</legend>
			<table class="admintable">
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Product' mod='awocoupon'}</label></td>
					<td>{if $row->id > 0}
							{$row->product_name} <input type="hidden" name="product_id" value="{$row->product_id}" />
						{else}
							<input type="text" size="30" value="" id="product_id_search" class="inputbox ac_input"/>
						{/if}
						<input type="hidden" name="product_id" value="{$row->product_id}" />
					</td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Template' mod='awocoupon'}</label></td>
					<td>{$lists.templatelist} <a href="http://awodev.com/documentation/awocoupon-pro/tutorials/how-create-coupon-template" target="_blank"><img src="{$question_img}" alt="question mark" height="27" /></a></td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Image' mod='awocoupon'}</label></td>
					<td>{$lists.profilelist}</td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Published' mod='awocoupon'}</label></td>
					<td>{$lists.published}</td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Expiration' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="expiration_number" size="10" value="{$row->expiration_number}" />{$lists.expiration_type}</td>
				</tr>
			</table>
		</fieldset>
		
		<fieldset class="adminform"><legend>{l s='Vendor' mod='awocoupon'}</legend>
			<table class="admintable">
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Name' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="vendor_name" size="30" maxlength="255" value="{$row->vendor_name}" /></td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Email' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="vendor_email" size="30" maxlength="255" value="{$row->vendor_email}" /></td>
				</tr>
			</table>

		</fieldset>
		
		<fieldset class="adminform"><legend>{l s='Personal Message' mod='awocoupon'}</legend>
			<table class="admintable">
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Recipient Name ID' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="recipient_name_id" size="30" maxlength="255" value="{$row->recipient_name_id}" /></td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Recipient Email ID' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="recipient_email_id" size="30" maxlength="255" value="{$row->recipient_email_id}" /></td>
				</tr>
				<tr>
					<td class="key" nowrap><label class="control-label">{l s='Recipient Message ID' mod='awocoupon'}</label></td>
					<td><input class="inputbox" type="text" name="recipient_mesg_id" size="30" maxlength="255" value="{$row->recipient_mesg_id}" /></td>
				</tr>
			</table>

		</fieldset>
	</div>

	<div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>

	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="giftcert" />
	<input type="hidden" name="layout" value="edit" />
	<input type="hidden" name="task" value="store" />
	<input type="hidden" name="id" value="{$row->id}" />
	<input type="hidden" name="cid[]" value="{$row->id}" />
	</form>

	{include file="footer_toolbar.tpl"}
</div>


