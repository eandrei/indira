{if isset($error_list)}
	<div class="alert error">
		{l s='FANCourier' errors list:' mod='fancourier'}
		<ul style="margin-top: 10px;">
			{foreach from=$error_list item=current_error}
				<li>{$current_error}</li>
			{/foreach}
		</ul>
	</div>
{/if}
{if isset($so_url_back)}
	<a href="{$so_url_back}step=2&cgv=1" class="button_small" title="{l s='Back' mod='fancourier'}">Back</a>
{/if}