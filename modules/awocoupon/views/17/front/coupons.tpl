{*
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


{extends file='customer/page.tpl'}


<script language="javascript" type="text/javascript">
str_voucher_confirm = "{l s='Are you sure you want to activate the voucher?' mod='awocoupon'}";

function awocoupon_activateme() {
	if(confirm(str_voucher_confirm)) {
		return true;
	}
	return false;
}

</script>


{block name='page_title'}
  {l s='My Coupons' mod='awocoupon'}
{/block}

{if !empty($success)}
	{if $success==1}<p class="success">{l s='Successfully updated' mod='awocoupon'}</p>
	{/if}
{/if}


{block name='page_content'}

	<table class="table table-striped table-bordered hidden-sm-down">
	<thead class="thead-default">
		<tr>
			<th>{l s='Coupon Code' mod='awocoupon'}</th>
			<th>{l s='Value Type' mod='awocoupon'}</th>
			<th>{l s='Value' mod='awocoupon'}</th>
			<th>{l s='Balance' mod='awocoupon'}</th>
			<th>{l s='Start Date' mod='awocoupon'}</th>
			<th>{l s='Expiration' mod='awocoupon'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$rows item=row}
			<tr>
				<td>{$row->coupon_code}
					{if !empty($row->filename)} 
						<a class="awomodal" href="{$row->image_link}"><img src="{$module_uri}/media/img/icon_view.png" style="height:20px;" ></a>
					{/if}
					{if $row->published==-1}
						<form method="post" action="{$smarty.server.REQUEST_URI}" name="" id="" onsubmit="return awocoupon_activateme();">
							<button type="submit">activate</button>
							<input type="hidden" name="task" value="activate" />
							<input type="hidden" name="id" value="{$row->id}" />
						</form>
					{/if}
				</td>
				<td>{$row->str_function_type}</td>
				<td>{$row->str_coupon_value}</td>
				<td>{if isset($row->balance)}{$row->str_balance}{else}---{/if}</td>
				<td>{if !empty($row->startdate)}{Tools::displayDate($row->startdate,null , true)}{/if}</td>
				<td>{if !empty($row->expiration)}{Tools::displayDate($row->expiration,null , true)}{/if}</td>
			</tr>
        {/foreach}
	</tbody>
    </table>

{/block}




<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-chevron-left"></i> {l s='Back to Your Account'}
			</span>
		</a>
	</li>
	<li>
		<a class="btn btn-default button button-small" href="{$base_dir}">
			<span><i class="icon-chevron-left"></i> {l s='Home'}</span>
		</a>
	</li>
</ul>


