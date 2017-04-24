{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<style>
.pane-sliders {
margin: 18px 0 0 0;
position: relative;
}

.pane-sliders .panel {
margin-bottom: 3px;
border: 1px solid #DFD5C3;
}
.pane-sliders .panel h3 span { color: #ffffff; }

.pane-sliders .panel h3 {
color: #ffffff !important;
background-color: #333333;
border: 1px solid #000000;
text-align: left;
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
table.adminlist th { background-color: #EBEDF4; }
.pane-sliders .content,div.function_type2_holder,table.adminlist td { background-color: #ffffff; }
.pane-sliders .content { margin-bottom:25px; }

</style>


<fieldset><legend>{l s='General' mod='awocoupon'}</legend>
	<div class="" >
		<table class="admintable">
		<tr><td class="key"><label>{l s='Coupon Code' mod='awocoupon'}</label></td>
			<td>{$row->coupon_code}</td>
		</tr>
		{if $row->description}
			<tr><td class="key"><label>{l s='Description' mod='awocoupon'}</label></td>
				<td>{$row->description}</td>
			</tr>
		{/if}
		<tr><td class="key"><label>{l s='Secret Key' mod='awocoupon'}</label></td>
			<td>{$row->passcode}</td>
		</tr>
		<tr><td class="key"><label>{l s='Published' mod='awocoupon'}</label></td>
			<td ><img src="{$url_module}/media/img/{if $row->published==1}published{elseif $row->published==-2}template{else}unpublished{/if}.png" width="16" height="16" border="0" /></td>
		</tr>
		<tr><td class="key"><label>{l s='Function Type' mod='awocoupon'}</label></td>
			<td>{$row->str_function_type}</td>
		</tr>
		{if $row->num_of_uses_total}
			<tr><td class="key"><label>{l s='Number of Uses Total' mod='awocoupon'}</label></td>
				<td>{$row->num_of_uses_total}</td>
			</tr>
		{/if}
		{if $row->num_of_uses_percustomer}
			<tr><td class="key"><label>{l s='Number of Uses Per' mod='awocoupon'}</label></td>
				<td>{$row->num_of_uses_percustomer}</td>
			</tr>
		{/if}
		{if $row->str_coupon_value_type}
			<tr><td class="key"><label>{l s='Percent or Amount' mod='awocoupon'}</label></td>
				<td>{$row->str_coupon_value_type}</td>
			</tr>
		{/if}
		{if $row->str_discount_type}
			<tr><td class="key"><label>{l s='Discount Type' mod='awocoupon'}</label></td>
				<td>{$row->str_discount_type}</td>
			</tr>
		{/if}
		{if $row->str_coupon_value}
			<tr><td class="key"><label>{l s='Value' mod='awocoupon'}</label></td>
				<td>{$row->str_coupon_value}</td>
			</tr>
		{/if}
		{if $row->str_buy_xy_process_type}
			<tr><td class="key"><label>{l s='Process Type' mod='awocoupon'}</label></td>
				<td>{$row->str_buy_xy_process_type}</td>
			</tr>
		{/if}
		{if isset($row->str_min_value)}
			<tr><td class="key"><label>{l s='Minimum Value' mod='awocoupon'}</label></td>
				<td>{$row->str_min_value}</td>
			</tr>
		{/if}
		{if isset($row->str_min_qty)}
			<tr><td class="key"><label>{l s='Minimum Product Quantity' mod='awocoupon'}</label></td>
				<td>{$row->str_min_qty}</td>
			</tr>
		{/if}
		{if isset($row->params->max_discount_qty)}
			<tr><td class="key"><label>{l s='Maximum Discount Qty' mod='awocoupon'}</label></td>
				<td>{$row->params->max_discount_qty}</td>
			</tr>
		{/if}
		{if $row->function_type=='parent'}
			<tr><td class="key"><label>{l s='Process Type' mod='awocoupon'}</label></td>
				<td>{$row->str_parent_type}</td>
			</tr>
		{/if}
		{if isset($row->params->asset1_qty)}
			<tr><td class="key"><label>{l s='Buy X' mod='awocoupon'}</label></td>
				<td>{$row->params->asset1_qty}</td>
			</tr>
		{/if}
		{if isset($row->params->asset2_qty)}
			<tr><td class="key"><label>{l s='Get Y' mod='awocoupon'}</label></td>
				<td>{$row->params->asset2_qty}</td>
			</tr>
		{/if}
		{if $row->function_type=='buy_x_get_y'}
			<tr><td class="key"><label>{l s='Do not Mix Products' mod='awocoupon'}</label></td>
				<td>{if $row->params->product_match == "1"} {l s='Yes' mod='awocoupon'} {else} {l s='No' mod='awocoupon'} {/if}</td>
			</tr>
			<tr><td class="key"><label>{l s='Automatically add to cart "Get Y" product' mod='awocoupon'}</label></td>
				<td>{if isset($row->params->addtocart) && $row->params->addtocart == "1"} {l s='Yes' mod='awocoupon'} {else} {l s='No' mod='awocoupon'} {/if}</td>
			</tr>
		{/if}
		{if $row->startdate}
			<tr><td class="key"><label>{l s='Start Date' mod='awocoupon'}</label></td>
				<td>{$row->startdate}</td>
			</tr>
		{/if}
		{if $row->expiration}
			<tr><td class="key"><label>{l s='Expiration' mod='awocoupon'}</label></td>
				<td>{$row->expiration}</td>
			</tr>
		{/if}
		{if isset($row->str_exclude)}
			<tr><td class="key"><label>{l s='Exclude' mod='awocoupon'}</label></td>
				<td>{$row->str_exclude}</td>
			</tr>
		{/if}
		{if $row->tags}
			<tr><td class="key"><label>{l s='Tags' mod='awocoupon'}</label></td>
				<td>{$row->tags}</td>
			</tr>
		{/if}
		{if $row->note}
			<tr><td class="key"><label>{l s='Admin Note' mod='awocoupon'}</label></td>
				<td>{$row->note}</td>
			</tr>
		{/if}


		</table>
		
	</div>
</fieldset>

<br />

{$slider.start}
{if isset($row->shoplist) && $row->shoplist}
	{$slider.panel_shops}

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">#</th>
			<th class="title">ID</th>
			<th class="title">{l s='Shop' mod='awocoupon'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$row->shoplist key=i item=r}
		<tr>
			<td>{$i+1}</td>
			<td align="center">{$r->id_shop}</td>
			<td align="center">{$r->name}</td>
		</tr>
		{/foreach}
	</tbody>
	</table>
	<br /><br />
{/if}

{if isset($row->userlist) && $row->userlist}
	{$slider.panel_customers}

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">#</th>
			<th class="title">ID</th>
			<th class="title">{l s='Asset' mod='awocoupon'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$row->userlist key=i item=r}
		<tr>
			<td>{$i+1}</td>
			<td align="center">{$r->user_id}</td>
			<td align="center">{$r->user_name}</td>
		</tr>
		{/foreach}
	</tbody>
	</table>
	<br /><br />
{/if}




{if isset($row->countrystatelist)}
	{$slider.panel_countrystate}

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">#</th>
			<th class="title">ID</th>
			<th class="title">{l s='Asset' mod='awocoupon'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$row->countrystatelist key=i item=r}
		<tr>
			<td>{$i+1}</td>
			<td align="center">{$r->asset_id}</td>
			<td align="center">{$r->asset_name}</td>
		</tr>
		{/foreach}
	</tbody>
	</table>
	<br /><br />
{/if}





{if $row->asset1_function_type}
	{$slider.panel_asset1}
	{if isset($row->assetlist) && $row->assetlist}
		<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th width="5">#</th>
				<th class="title">ID</th>
				<th class="title">{l s='Asset' mod='awocoupon'}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$row->assetlist key=i item=r}
			<tr>
				<td>{$i+1}</td>
				<td align="center">{$r->asset_id}</td>
				<td align="center">{$r->asset_name}</td>
			</tr>
			{/foreach}
		</tbody>
		</table>
	<br /><br />
	{else}
		{l s='All' mod='awocoupon'}
	{/if}
{/if}


{if isset($row->assetlist2) && $row->assetlist2}
	{$slider.panel_asset2}			
							
	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">#</th>
			<th class="title">ID</th>
			<th class="title">{l s='Asset' mod='awocoupon'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$row->assetlist2 key=i item=r}
		<tr>
			<td>{$i+1}</td>
			<td align="center">{$r->asset_id}</td>
			<td align="center">{$r->asset_name}</td>
		</tr>
		{/foreach}
	</tbody>
	</table>
	<br /><br />

{/if}
{$slider.end}


