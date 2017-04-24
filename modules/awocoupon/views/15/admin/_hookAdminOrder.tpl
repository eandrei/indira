<fieldset><legend>AwoCoupon</legend>

{if !empty($errors)}
<p style="" class="error">
	{foreach from=$errors item='er'}
		{$er}<br />
	{/foreach}	
</p>
{/if}

<div>
	{if $total_count}

	<table width="100%" cellspacing="0" cellpadding="0" id="shipping_table" class="table">
	<thead>
		<tr>
			<th>ID</th>
			<th>COUPON CODE</th>
			<th>PRODUCT DISCOUNT</th>
			<th>SHIPPING DISCOUNT</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$rows item='row'}
		<tr>
			<td>{$row->id}</td>
			<td>{$row->coupon_code}</td>
			<td>{$row->product_discount}</td>
			<td>{$row->shipping_discount}</td>
		</tr>
	{/foreach}
	</tbody>
	</table>

	{else}
		<p class="">{l s='No coupons in this order' mod="awocoupon"}</p>
	{/if}
</div>


<div>
<form id="awocoupon_order_form" name="adminForm" method="post" action="{$form_url}"  >
<input type="text" name="coupon_code" value="" />
<input type="submit" value="Add Coupon" />
<input type="hidden" name="id_order" value="{$id_order}" />
<input type="hidden" name="awocoupon_add" value="1" />
</form>
</div>
									
</fieldset>
									