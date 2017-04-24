<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<h3>AwoCoupon</h3>
				

			{if !empty($errors)}
			<div class="alert alert-danger">
				{foreach from=$errors item='er'}
					<ol><li>{$er}</li></ol>
				{/foreach}	
			</div>
			{/if}

			{if $total_count}

			<table class="table">
			<thead>
				<tr>
					<th><span class="title_box ">ID</span></th>
					<th><span class="title_box ">COUPON CODE</span></th>
					<th><span class="title_box ">PRODUCT DISCOUNT</span></th>
					<th><span class="title_box ">SHIPPING DISCOUNT</span></th>
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
			
			
			<div>
				<form id="awocoupon_order_form" name="adminForm" method="post" action="{$form_url}"  >
					<input type="text" name="coupon_code" value="" />
					<input type="submit" value="Add Coupon" />
					<input type="hidden" name="id_order" value="{$id_order}" />
					<input type="hidden" name="awocoupon_add" value="1" />
				</form>
			</div>
			
	
		</div>
	</div>
</div>
	
	
	
