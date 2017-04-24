{*
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<style>
@media print {
	.noprint { display:none; }
}
</style>

<div style="text-align:center;">
	{if !empty($row.image)}
		<div><img src="{$row.url}" alt="coupon" /></div>
		<br />
		<div class="noprint">
			<button onclick="window.print();returnfalse;" type="button">{l s='Print' mod='awocoupon'}</button>
		</div>
	{/if}

</div>

