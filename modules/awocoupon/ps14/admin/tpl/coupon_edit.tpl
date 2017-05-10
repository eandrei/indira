{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

<br />
<script language="javascript" type="text/javascript">
<!--
var $j = jQuery.noConflict();  // added so jquery does not conflict with mootools

$j(document).ready(function() {

	funtion_type_change(true);
	user_type_change(true);
	
	
	jQuery('#countrylist,#statelist').select2({
		minimumResultsForSearch: -1
	});
	
	countrystatechange('#countrylist','#statelist','{$row->statelist_str}');
	jQuery("#countrylist").on("change", function(e){ 
		ids = '';
		countrystatechange(this,'#statelist',ids);
	
	});

	var base_url = "{$ajax_url}";
	{literal}
	jQuery.getJSON(
		base_url, 
		{option:'com_awocoupon', task:'ajax_tags', tmpl:'component', no_html:1}, 
		function(opts){
			jQuery("#e12").select2({
				tags: opts,
				tokenSeparators: [","]
			});
		}
	);
	{/literal}
	
		
});


var my_option = "com_awocoupon";
var base_url = "{$ajax_url}";

var str_cum_title = "{l s='VAlue Definition' mod='awocoupon'}";
var str_cum_lbl1 = "{l s='Number of Products' mod='awocoupon'}";
var str_cum_lbl2 = "{l s='Value' mod='awocoupon'}";
var str_cum_new = "{l s='Add Entry' mod='awocoupon'}";
var str_cum_subm = "{l s='Submit' mod='awocoupon'}";
var str_cum_qty_type = "{l s='Apply Distinct Count' mod='awocoupon'}";

var str_coupons = "{l s='Coupons' mod='awocoupon'}";
var str_products = "{l s='Products' mod='awocoupon'}";
var str_categories = "{l s='Categories' mod='awocoupon'}";
var str_manufacturers = "{l s='Manufacturers' mod='awocoupon'}";
var str_vendors = "{l s='Vendors' mod='awocoupon'}";
var str_shipping = "{l s='Shipping' mod='awocoupon'}";


var str_coup_date = "";
var str_coup_err_invalid = "{l s='Invalid' mod='awocoupon'}";
var str_coup_err_valid_code = "{l s='Coupon: please enter a valid value' mod='awocoupon'}";
var str_coup_err_value_type = "{l s='Value type: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_value = "{l s='Value: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_def = "{l s='Value Definition: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_discount = "{l s='Discount Type: please enter a valid value' mod='awocoupon'}";
var str_coup_err_choose_product = "{l s='Please select at least one asset for discount type of specific' mod='awocoupon'}";
var str_coup_err_choose_category = "{l s='Please select at least one asset for discount type of specific' mod='awocoupon'}";
var str_coup_err_choose_manufacturer = "{l s='Please select at least one asset for discount type of specific' mod='awocoupon'}";
var str_coup_err_choose_vendor = "{l s='Please select at least one asset for discount type of specific' mod='awocoupon'}";
var str_coup_err_choose_inclexcl = "{l s='Please select include/exclude for the list' mod='awocoupon'}";
var str_coup_err_valid_uses_total = "{l s='Number of uses (Total): please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_uses_percustomer = "{l s='Number of uses (Per Customer): please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_min = "{l s='Minimum Value: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_expiration = "{l s='Expiration: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_publish = "{l s='Published: please enter a valid value' mod='awocoupon'}";
var str_coup_err_confirm_expiration = "{l s='Expiration Date is in the past, are you sure you want to submit?' mod='awocoupon'}";
var str_coup_err_valid_usertype = "{l s='User Type: please enter a valid value' mod='awocoupon'}";
var str_coup_err_valid_input = "{l s='please enter a valid value' mod='awocoupon'}";
var str_coup_err_discount_qty = "{l s='Maximum Discount Qty: please enter a valid value' mod='awocoupon'}";


var str_startdate = "{l s='Start Date' mod='awocoupon'}";
var str_expiration = "{l s='Expiration' mod='awocoupon'}";

var str_id = "{l s='ID' mod='awocoupon'}";
var str_name = "{l s='Name' mod='awocoupon'}";

var str_selection_error = "{l s='please make a selection' mod='awocoupon'}";
var str_parent_type = "{l s='Process Type' mod='awocoupon'}";
var str_coupon = "{l s='Coupon' mod='awocoupon'}";
var str_asset = "{l s='Asset' mod='awocoupon'}";

var str_buy_x = "{l s='Buy X' mod='awocoupon'}";
var str_get_y = "{l s='Get Y' mod='awocoupon'}";
var str_number = "{l s='Number' mod='awocoupon'}";
var str_min_qty = "{l s='Minimum Product Quantity' mod='awocoupon'}";



//-->
</script>

<style>
.pane-sliders {
margin: 18px 0 0 0;
position: relative;
}

.pane-sliders .panel {
margin-bottom: 3px;
border: 1px solid #DFD5C3;
}

.pane-sliders .panel h3 {
background: #FFF6D3;
border: 1px solid #DFD5C3;
color: #666;
}
.pane-sliders .title {
margin: 0;
padding: 2px 2px 2px 5px;
color: #666;
cursor: pointer;
}
.pane-sliders .content {
background: #FFFFF0;
}
.function_type2_holder {
height: 250px;
overflow: auto;
overflow-x: hidden;
border-top: 1px inset #CCC;
border-left: 1px inset #CCC;
border-bottom: 1px inset #CCC;
border-right: 1px inset #CCC;
}

.pane-sliders .adminlist {
border: 0 none;
font-size: 1em;
}
table.adminlist {
width: 100%;
border-spacing: 1px;
background-color: #ffffff;
color: #666;
}
table.adminlist th { background-color: #FFF6D3; }

</style>


{$errors}


<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" onsubmit="return submitbutton();">


	<div class="width-50 fltlft">
		<fieldset class="adminform">
		<legend>{l s='Coupon Details' mod='awocoupon'}</legend>

		<table class="admintable">
			<tr id="tr_function_type" >
				<td class="key" nowrap><label>{l s='Function Type' mod='awocoupon'}</label></td>
				<td>{$lists.function_type}</td>
			</tr>
			<tr id="tr_coupon_code" style="display:none;">
				<td class="key" nowrap><label>{l s='Coupon Code' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="coupon_code" size="30" maxlength="255" value="{$row->coupon_code}" />
					<button type="button" onclick="generate_code()">{l s='Generate Code' mod='awocoupon'}</button>
				</td>
			</tr>
			<tr>
				<td class="key" nowrap><label class="control-label">{l s='Description' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="description" size="30" maxlength="255" value="{$row->description}" /></td>
			</tr>
			<tr id="tr_published" style="display:none;">
				<td class="key" nowrap><label>{l s='Published' mod='awocoupon'}</label></td>
				<td>{$lists.published}</td>
			</tr>
			<tr id="tr_parent_type" style="display:none;">
				<td class="key" nowrap><label>{l s='Process Type' mod='awocoupon'}</label></td>
				<td>{$lists.parent_type}</td>
			</tr>
			<tr id="tr_coupon_value_type" style="display:none;">
				<td class="key" nowrap><label>{l s='Percent or Amount' mod='awocoupon'}</label></td>
				<td>{$lists.coupon_value_type}</td>
			</tr>
			<tr id="tr_discount_type" style="display:none;">
				<td class="key" nowrap><label>{l s='Discount Type' mod='awocoupon'}</label></td>
				<td>{$lists.discount_type}</td>
			</tr>
			<tr id="tr_coupon_value" style="display:none;">
				<td class="key" nowrap><label>{l s='Value' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="coupon_value" size="30" maxlength="255" value="{$row->coupon_value}" /></td>
			</tr>
			<tr id="tr_or" style="display:none;"><td class="key" colspan="2" align="center">{l s='OR' mod='awocoupon'}</td></tr>
			<tr id="tr_coupon_value_def" style="display:none;">
				<td class="key" nowrap><label>{l s='Value Definition' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="coupon_value_def" size="26" onfocus="showvaluedefinition();" maxlength="255" value="{$row->coupon_value_def}" /><input type="button" value="..." onclick="showvaluedefinition();"></td>
			</tr>
			<tr id="tr_buy_xy_type" style="display:none;">
				<td class="key" nowrap><label>{l s='Process Type' mod='awocoupon'}</label></td>
				<td>{$lists.buy_xy_process_type}</td>
			</tr>
		</table>

		</fieldset>


		<fieldset  id="fs_optionals" class="adminform" style="display:none;">
		<legend>{l s='Optional Fields' mod='awocoupon'}</legend>


		<table class="admintable">
			<tr id="tr_num_of_uses_total" style="display:none;"><td class="key" nowrap><label>{l s='Number of Uses (Total)' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="num_of_uses_total" size="2" maxlength="255" value="{$row->num_of_uses_total}" /></td>
			</tr>
			<tr id="tr_num_of_uses_percustomer" style="display:none;"><td class="key" nowrap><label>{l s='Number of Uses (Per Customer)' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="num_of_uses_percustomer" size="2" maxlength="255" value="{$row->num_of_uses_percustomer}" /></td>
			</tr>
			<tr id="tr_min_value" style="display:none;"><td class="key" nowrap><label>{l s='Minimum Value' mod='awocoupon'}</label></td>
				<td>{$lists.min_value_type}
					<input class="inputbox" type="text" name="min_value" size="8" maxlength="255" value="{$row->min_value}" />
				</td>
			</tr>
			<tr id="tr_min_qty" style="display:none;"><td class="key" nowrap><label class="control-label">{l s='Minimum Product Quantity' mod='awocoupon'}</label></td>
				<td>{$lists.min_qty_type}
					<input class="inputbox" type="text" name="min_qty" size="8" maxlength="255" value="{$row->min_qty}" />
				</td>
			</tr>
			<tr id="tr_max_discount_qty" style="display:none;"><td class="key" nowrap><label>{l s='Maximum Discount Qty' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="text" name="max_discount_qty" size="8" maxlength="255" value="{$row->max_discount_qty}" /></td>
			</tr>
			<tr id="tr_startdate" style="display:none;"><td class="key" nowrap valign="top"><label>{l s='Start Date' mod='awocoupon'}</label></td>
				<td><input type="text" size="20" id="startdate" name="startdate" value="{$row->startdate}" />
					<p class="clear">{l s='Format: YYYY-MM-DD HH:MM:SS' mod='awocoupon'}</p>
				</td>
			</tr>
			<tr id="tr_expiration" style="display:none;"><td class="key" nowrap valign="top"><label>{l s='Expiration' mod='awocoupon'}</label></td>
				<td><input type="text" size="20" id="expiration" name="expiration" value="{$row->expiration}" />
					<p class="clear">{l s='Format: YYYY-MM-DD HH:MM:SS' mod='awocoupon'}</p>
				</td>
			</tr>
			
			<tr id="tr_product_match" style="display:none;"><td class="key" nowrap><label>{l s='Do not Mix Products' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="checkbox" name="product_match" value="1" {if $row->product_match =="1"} CHECKED {/if} /></td>
			</tr>
			<tr id="tr_addtocart" style="display:none;"><td class="key" nowrap><label>{l s='Automatically add to cart' mod='awocoupon'}<br />{l s='"Get Y" product' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="checkbox" name="addtocart" value="1" {if $row->addtocart=="1"} CHECKED {/if} /></td>
			</tr>
			<tr id="tr_exclude_special" style="display:none;"><td class="key" nowrap><label>{l s='Exclude Products on Special' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="checkbox" name="exclude_special" value="1" {if $row->exclude_special == "1"} CHECKED {/if} /></td>
			</tr>
			<tr id="tr_exclude_giftcert" style="display:none;"><td class="key" nowrap><label>{l s='Exclude Gift Certificates' mod='awocoupon'}</label></td>
				<td><input class="inputbox" type="checkbox" name="exclude_giftcert" value="1" {if $row->exclude_giftcert == "1"} CHECKED {/if} /></td>
			</tr>
				<tr><td class="key" nowrap><label class="control-label">{l s='Tags' mod='awocoupon'}</label></td>
					<td><input name="tags" type="hidden" style="min-width:200px;max-width:500px;" id="e12" value="{$row->tags}"/></td>
				</tr>
			<tr><td class="key" nowrap><label>{l s='Admin Note' mod='awocoupon'}</label></td>
				<td><textarea cols="18" rows="3" name="note" style="width:147px;">{$row->note}</textarea>
			</tr>
		</table>

		</fieldset>
	</div>

	<div class="">
	
		<div id="div_countrystate" class="">
			<fieldset  id="fs_optionals" class="adminform" style="display:none;">
			<legend>{l s='Country/State' mod='awocoupon'}</legend>
			
			<table class="admintable">
				<tr id="tr_num_of_uses_total" style="display:none;"><td class="key" nowrap><label>{l s='Country' mod='awocoupon'}</label></td>
					<td>{$lists.countrylist}</td>
				</tr>
				<tr id="tr_num_of_uses_percustomer" style="display:none;"><td class="key" nowrap><label>{l s='State' mod='awocoupon'}</label></td>
					<td><select id="statelist" name="statelist[]" style="width:90%;" MULTIPLE><option></option></select></td>
				</tr>
				<tr><td></td><td>
					<div id="tr_f2_mode">
						<input type="hidden" name="countrystate_mode" value="" />
						<input type="radio" name="countrystate_mode" value="include" {if $row->countrystate_mode=='' || $row->countrystate_mode=='include'} CHECKED {/if} />{l s='Include' mod='awocoupon'} &nbsp;&nbsp;
						<input type="radio" name="countrystate_mode" value="exclude" {if $row->countrystate_mode=='exclude'} CHECKED {/if} />{l s='Exclude' mod='awocoupon'}
					</div>
				</td></tr>
			</table>
			</fieldset>
			
		</div>
		
		{$slider.start}
		{$slider.panel_customers}


			<div id="div_users" style="padding:10px;">
				<div>{$lists.user_type}</div>
				<div id="div_user_simple_table">
					<span style="width:70px;display:inline-block;">{l s='Search' mod='awocoupon'}:</span>
					<input class="inputbox" type="text" id="user_search" name="user_name" size="60" maxlength="255" value="" />
					<input type="hidden" name="user_id" value="" />
					<button type="button" onclick="dd_itemselectf('user'); return false;">{l s='Add' mod='awocoupon'}</button>
					[ <a href="javascript:view_all('user');">{l s='View All' mod='awocoupon'}</a> ]
				</div>
				
				<div id="div_user_advanced_table" style="display:none;">
					<div>
						<span style="width:70px;display:inline-block;">{l s='Search' mod='awocoupon'}:</span>
						<input type="text" id="user_search_txt" size="60" onkeyup="dd_searchg('user')">
						<button onclick="dd_itemselectg('user'); return false;">{l s='Add' mod='awocoupon'}</button>
						[ <a href="javascript:view_some('user');">{l s='Return' mod='awocoupon'}</a> ]
					</div>
					<select name="_userlist" MULTIPLE class="inputbox" size="2" style="width:100%; height:160px;" ondblclick="dd_itemselectg('user')"></select>
					<div style="color:#777777;"><i>{l s='Ctrl/Shift' mod='awocoupon'}</i></div>
					<br />
				</div>
				
				<div class="function_type2_holder">
					<table id="tbl_users" class="adminlist" cellspacing="1">
					<thead><tr><th>{l s='ID' mod='awocoupon'}</th><th>{l s='Name' mod='awocoupon'}</th><th>&nbsp;</th></tr></thead>
					<tbody>
					{foreach from=$row->userlist key=case_num item=user}
						<tr id="tbl_users_tr{$user->user_id}" style="background-color:#ffffff;">
							<td>{$user->user_id}</td>
							<td>{$user->user_name}</td>
							<td class="last" align="right">
								<button type="button" onclick="deleterow('tbl_users_tr{$user->user_id}');return false;" >X</button>
								<input type="hidden" name="userlist[]" value="{$user->user_id}">
								<input type="hidden" name="usernamelist[{$user->user_id}]" value="{$user->user_name}"></td>
						</tr>
					{/foreach}
					</tbody></table>
				</div>
			</div>

				

		{$slider.panel_asset1}


			<div style="padding:10px;">
				<div id="div_asset_qty" style="display:none;">
					<span style="width:70px;display:inline-block;padding-bottom:5px;">{l s='Number' mod='awocoupon'}:</span>
					<input type="text" name="asset1_qty" value="{$row->asset1_qty}" size="4" />
				</div>

				<div id="div_asset1_type"><span style="width:70px;display:inline-block;">{l s='Type' mod='awocoupon'}</span> {$lists.asset1_function_type}<br /></div>
				
				<div id="div_asset1_inner" style="display:none;">
					<div id="div_asset_simple_table">
						<span style="width:70px;display:inline-block;">{l s='Search' mod='awocoupon'}:</span>
						<input class="inputbox" type="text" id="asset_search" name="asset_name" size="60" maxlength="255" value="" />
						<input type="hidden" name="asset_id" value="" />
						<button type="button" onclick="dd_itemselectf('asset'); return false;">{l s='Add' mod='awocoupon'}</button>
						[ <a href="javascript:view_all('asset');">{l s='View All' mod='awocoupon'}</a> ]
					</div>
							
					<div id="div_asset_advanced_table" style="display:none;">
						<div>
							<span style="width:70px;display:inline-block;">Search:</span>
							<input type="text" id="asset_search_txt" size="60" onkeyup="dd_searchg('asset')">
							<button onclick="dd_itemselectg('asset'); return false;">{l s='Add' mod='awocoupon'}</button>
							[ <a href="javascript:view_some('asset');">{l s='Return' mod='awocoupon'}</a> ]
						</div>
						<select name="_assetlist" MULTIPLE class="inputbox" size="2" style="width:100%; height:160px;" ondblclick="dd_itemselectg('asset')"></select>
						<div style="color:#777777;"><i>{l s='Ctrl/Shift' mod='awocoupon'}</i></div>
						<br />
					</div>
					
					<div class="function_type2_holder">
						<table id="tbl_assets" class="adminlist" cellspacing="1">
						<thead><tr><th>{l s='ID' mod='awocoupon'}</th><th>{l s='Name' mod='awocoupon'}</th><th>&nbsp;</th></tr></thead>
						<tbody>
						{foreach from=$row->assetlist key=case_num item=asset}
							<tr id="tbl_assets_tr{$asset->asset_id}">
								<td>{$asset->asset_id}</td>
								<td>{$asset->asset_name}</td>
								<td class="last" align="right">
									{if $row->function_type=='parent'}
										<button type="button" onclick="moverow('tbl_assets_tr{$asset->asset_id}','up');" >&#8593;</button><button 
												type="button" onclick="moverow('tbl_assets_tr{$asset->asset_id}','down');" >&#8595;</button>&nbsp; 
									{/if}
									<button type="button" onclick="deleterow('tbl_assets_tr{$asset->asset_id}');return false;" >X</button>
									<input type="hidden" name="assetlist[]" value="{$asset->asset_id}">
									<input type="hidden" name="assetnamelist[{$asset->asset_id}]" value="{$asset->asset_name}"></td>
							</tr>
						{/foreach}
						</tbody></table>
					</div>
					<div id="tr_f2_mode">
						<input type="hidden" name="asset1_mode" value="" />
						<input type="radio" name="asset1_mode" value="include" {if $row->asset1_mode=='' || $row->asset1_mode=='include'} CHECKED {/if} />{l s='Include' mod='awocoupon'} &nbsp;&nbsp;
						<input type="radio" name="asset1_mode" value="exclude" {if $row->asset1_mode=='exclude'} CHECKED {/if} />{l s='Exclude' mod='awocoupon'}
					</div>
				</div>
			</div>


		{$slider.panel_asset2}


			<div style="padding:10px;">
			
				<div id="div_asset2_qty" style="display:none;">
					<span style="width:70px;display:inline-block;padding-bottom:5px;">{l s='Number' mod='awocoupon'}:</span>
					<input type="text" name="asset2_qty" value="{$row->asset2_qty}" size="4" />
				</div>

				<div id="div_asset2_type"><span style="width:70px;display:inline-block;padding-bottom:5px;">{l s='Type' mod='awocoupon'}:</span> {$lists.asset2_function_type}<br /></div>
				
				<div id="div_asset2_inner" style="display:none;">
					<div id="div_asset2_simple_table">
						<span style="width:70px;display:inline-block;">{l s='Search' mod='awocoupon'}:</span>
						<input class="inputbox" type="text" id="asset_search2" name="asset_name2" size="60" maxlength="255" value="" />
						<input type="hidden" name="asset_id2" value="" />
						<button type="button" onclick="dd_itemselectf('asset2'); return false;">{l s='Add' mod='awocoupon'}</button>
						[ <a href="javascript:view_all('asset2');">{l s='View All' mod='awocoupon'}</a> ]
					</div>
					
					<div id="div_asset2_advanced_table" style="display:none;">
						<div>
							<span style="width:70px;display:inline-block;">{l s='Search' mod='awocoupon'}:</span>
							<input type="text" size="60" id="asset_search_txt2" onkeyup="dd_searchg('asset2')">
							<button onclick="dd_itemselectg('asset2'); return false;">{l s='Add' mod='awocoupon'}</button>
							[ <a href="javascript:view_some('asset2');">{l s='Return' mod='awocoupon'}</a> ]
						</div>
						<select name="_assetlist2" MULTIPLE class="inputbox" size="2" style="width:100%; height:160px;" ondblclick="dd_itemselectg('asset2')"></select>
						<div style="color:#777777;"><i>{l s='Ctrl/Shift' mod='awocoupon'}</i></div>
						<br />
					</div>
					
					<div class="function_type2_holder">
						<table id="tbl_assets2" class="adminlist" cellspacing="1">
						<thead><tr><th>{l s='ID' mod='awocoupon'}</th><th>{l s='Name' mod='awocoupon'}</th><th>&nbsp;</th></tr></thead>
						<tbody>
						{foreach from=$row->assetlist2 key=case_num item=asset}
							<tr id="tbl_assets2_tr{$asset->asset_id}">
								<td>{$asset->asset_id}</td>
								<td>{$asset->asset_name}</td>
								<td class="last" align="right">
									{if $row->function_type=='parent'}
										<button type="button" onclick="moverow('tbl_assets2_tr{$asset->asset_id}','up');" >&#8593;</button><button 
												type="button" onclick="moverow('tbl_assets2_tr{$asset->asset_id}','down');" >&#8595;</button>&nbsp; 
									{/if}
									<button type="button" onclick="deleterow('tbl_assets2_tr{$asset->asset_id}');return false;" >X</button>
									<input type="hidden" name="assetlist2[]" value="{$asset->asset_id}">
									<input type="hidden" name="asset2namelist[{$asset->asset_id}]" value="{$asset->asset_name}"></td>
							</tr>
						{/foreach}
						</tbody></table>
					</div>
					<div id="tr_f2_mode2">
						<input type="hidden" name="asset2_mode" value="" />
						<input type="radio" name="asset2_mode" value="include" {if $row->asset2_mode=='' || $row->asset2_mode=='include'} CHECKED {/if} />{l s='Include' mod='awocoupon'} &nbsp;&nbsp;
						<input type="radio" name="asset2_mode" value="exclude" {if $row->asset2_mode=='exclude'} CHECKED {/if} />{l s='Exclude' mod='awocoupon'}
					</div>
				</div>
			</div>


		{$slider.end}
				
	</div>


	<div class="clr"></div>


<div class="margin-form">
	<input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="submit" class="button">
</div>


<input type="hidden" name="module" value="awocoupon" />
<input type="hidden" name="view" value="coupon" />
<input type="hidden" name="layout" value="edit" />
<input type="hidden" name="task" value="store" />
<input type="hidden" name="id" value="{$row->id}" />
<input type="hidden" name="cid[]" value="{$row->id}" />
</form>
<div class="clr"></div>

<a href="{$back_url}"><img src="{$back_img}"> Back to list</a>