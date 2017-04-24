{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<script type="text/javascript">
	var iso = "{$isoTinyMCE}" ;
	var pathCSS = "{$theme_path}" ;
	var ad = "{$tiny_ad}";
</script>
<script type="text/javascript">
jQuery(document).ready(function() {
	displayCartRuleTab('general');

	tinySetup({
		editor_selector :"autoload_rte"
	});

	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k}] = {
			id_lang: {$language.id_lang},
			iso_code: '{$language.iso_code|escape:'quotes'}',
			name: '{$language.name|escape:'quotes'}'
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default});

});
function displayCartRuleTab(tab) {
	$('.cart_rule_tab').hide();
	$('.tab-page').removeClass('selected');
	$('#awocoupon_block_' + tab).show();
	$('#awocoupon_block_link_' + tab).addClass('selected');
}

function error_messages_debug() {
	jQuery("input.error_message").each(function() {
		//jQuery(this).val(jQuery(this).parent().parent().find('label span').html());
		jQuery(this).val(jQuery(this).parents('tr').find('label span').html());
	});
}
function error_messages_clear() {
	jQuery("input.error_message").each(function() {
		jQuery(this).val('');
	});
}


</script>

{literal}
<style>
	table.admintable td.key { white-space:nowrap; width:auto; padding-right:10px; }
	div.current {background-color:#ffffff;}
	table.admintable td.key label { width: 100%; }
</style>
<style>

#div_master_list .productTabs { width:180px; }
#div_master_list form.form_tab { margin-left:180px; }


</style>


{/literal}

<div class="panel awocoupon">
	<h3><i class="icon-tag"></i> {l s='Configuration' mod='awocoupon'}</h3>

	<div id="div_master_list">


	<div>
		<div class="productTabs">
			<ul class="tab">
				<li class="tab-row"><a class="tab-page" id="awocoupon_block_link_general" href="javascript:displayCartRuleTab('general');">{l s='General' mod='awocoupon'}</a></li>
				<li class="tab-row"><a class="tab-page" id="awocoupon_block_link_multiple" href="javascript:displayCartRuleTab('multiple');">{l s='Multiple Coupons' mod='awocoupon'}</a></li>
				<li class="tab-row"><a class="tab-page" id="awocoupon_block_link_giftcert" href="javascript:displayCartRuleTab('giftcert');">{l s='Gift Certificate' mod='awocoupon'}</a></li>
				<li class="tab-row"><a class="tab-page" id="awocoupon_block_link_errorcode" href="javascript:displayCartRuleTab('errorcode');">{l s='Error Codes' mod='awocoupon'}</a></li>
			</ul>
		</div>
	</div>


	<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" class="form_tab">
	<div class="width-100">



		<div id="awocoupon_block_general" class="cart_rule_tab">
			<h4>{l s='General' mod='awocoupon'}</h4>
			<div class="separation"></div>
				
			<table class="admintable general">
			<tr><td class="key"><label>{l s='Enable Store Coupons' mod='awocoupon'}</label></td>
				<td>{$lists.enable_store_coupon}</td>
			</tr>
			<tr><td class="key"><label>{l s='Calculate the discount before tax (Gift Certificates)' mod='awocoupon'}</label></td>
				<td>{$lists.enable_giftcert_discount_before_tax}</td>
			</tr>
			<tr><td class="key"><label>{l s='Calculate the discount before tax (Coupons)' mod='awocoupon'}</label></td>
				<td>{$lists.enable_coupon_discount_before_tax}</td>
			</tr>
			<tr><td class="key"><label>{l s='Store generated vouchers for front end display' mod='awocoupon'}</label></td>
				<td>{$lists.enable_frontend_image}</td>
			</tr>
			<tr><td class="key"><label>{l s='Delete expired coupons' mod='awocoupon'}</label></td>
				<td><input type="text" size="4" name="params[delete_expired]" value="{$row->delete_expired}" > {l s='days after expiration	' mod='awocoupon'}</td>
			</tr>
			<tr><td class="key"><label>{l s='Case Sensitive Coupon Codes' mod='awocoupon'}</label></td>
				<td>{$lists.casesensitive}<input type="hidden" name="casesensitiveold" value="{$row->casesensitive}" /></td>
			</tr>
			<tr><td class="key"><label>{l s='CSV Delimiter' mod='awocoupon'}</label></td>
				<td>{$lists.csvDelimiter}</td>
			</tr>
			</table>

		</div>

		<div id="awocoupon_block_multiple" class="cart_rule_tab">
			<h4>{l s='Multiple Coupons' mod='awocoupon'}</h4>
			<div class="separation"></div>
				
			<table class="admintable general">
			<tr><td class="key"><label>{l s='Enable Multiple Coupons' mod='awocoupon'}</label></td>
				<td>{$lists.enable_multiple_coupon}</td>
			</tr>
			<tr><td class="key">{l s='All (Max)' mod='awocoupon'}</td>
				<td nowrap=""><input type="text" value="{$row->multiple_coupon_max}" name="params[multiple_coupon_max]" size="4"> &nbsp;</td>
			</tr>
			<tr><td class="key">{l s='Automatic Discounts (Max)' mod='awocoupon'}</td>
				<td nowrap=""><input type="text" value="{$row->multiple_coupon_max_auto}" name="params[multiple_coupon_max_auto]" size="4"> &nbsp;</td>
			</tr>
			<tr><td class="key">{l s='Gift Certificates (Max)' mod='awocoupon'}</td>
				<td nowrap=""><input type="text" value="{$row->multiple_coupon_max_giftcert}" name="params[multiple_coupon_max_giftcert]" size="4"> &nbsp;</td>
			</tr>
			<tr><td class="key">{l s='Coupons (Max)' mod='awocoupon'}</td>
				<td nowrap=""><input type="text" value="{$row->multiple_coupon_max_coupon}" name="params[multiple_coupon_max_coupon]" size="4"> &nbsp;</td>
			</tr>
			</table>
		</div>


		<div id="awocoupon_block_giftcert" class="cart_rule_tab">
			<h4>{l s='Gift Certificate' mod='awocoupon'}</h4>
			<div class="separation"></div>
		
			<table class="admintable">
			<tr><td class="key"><label>{l s='Require Purchased Voucher Activation' mod='awocoupon'}</label></td>
				<td>{$lists.giftcert_coupon_activate}</td>
			</tr>
			<tr><td class="key"><label>{l s='Activate Vendor Email' mod='awocoupon'}</label></td>
				<td>{$lists.giftcert_vendor_enable}</td>
			</tr>
			<tr><td class="key"><label>{l s='Vendor Email Subject' mod='awocoupon'}</label></td>
				<td><input type="text" name="params[giftcert_vendor_subject]" value="{$row->giftcert_vendor_subject}" size="35"></td>
			</tr>
			<tr valign="top"><td class="key"><label>{l s='Vendor Email Body' mod='awocoupon'}</label>
				<br /><br />
				<table style="text-align:left;font-weight:normal;" align="right" cellspacing=0><tr><th>{l s='Tags' mod='awocoupon'}</th></tr>
				{literal}
				<tr><td>{vendor_name}</td></tr>
				<tr><td>{vouchers}</td></tr>
				<tr><td>{purchaser_first_name}</td></tr>
				<tr><td>{purchaser_last_name}</td></tr>
				<tr><td>{today_date}</td></tr>
				<tr><td>{order_id}</td></tr>
				{/literal}
				</table>
			</td>
				<td><textarea class="autoload_rte" cols="100" rows="20" id="params_giftcert_vendor_email" name="params[giftcert_vendor_email]">{$row->giftcert_vendor_email}</textarea></td>
			</tr>
			<tr><td class="key"><label>{literal}{vouchers}{/literal}</label></td>
				<td><input type="text" name="params[giftcert_vendor_voucher_format]" value="{$row->giftcert_vendor_voucher_format}" size="65"></td>
			</tr>

			</table>
		</div>
		
		
		<div id="awocoupon_block_errorcode" class="cart_rule_tab">
			<h4>{l s='Coupon Code Error Description' mod='awocoupon'}</h4>
			<div class="separation"></div>


		<table class="admintable errormsg">
		<tr><td align="right" colspan="2">
			<button type="button" onclick="error_messages_debug();return false;">{l s='Debug' mod='awocoupon'}</button>
			<button type="button" onclick="error_messages_clear();return false;">{l s='Clear' mod='awocoupon'}</button>
		</td></tr>
		<tr><td class="key"><label><span>{l s='No record, unpublished or expired' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" style="width:100%;" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errNoRecord]" value="{if isset($row->languages[$language.id_lang|intval]->errNoRecord)}{$row->languages[$language.id_lang|intval]->errNoRecord}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<?php if(awoHelper::is_multistore()) { ?>
			<tr><td class="key"><label><span>{l s='Shop does not have permission to use' mod='awocoupon'}</span></label></td>
				<td>
					{foreach from=$languages item=language}
						{if $languages|count > 1}
							<div class="translatable-field row lang-{$language.id_lang}">
								<div class="col-lg-9">
						{/if}
						<input type="text" style="width:100%;" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errShopPermission]" value="{if isset($row->languages[$language.id_lang|intval]->errShopPermission)}{$row->languages[$language.id_lang|intval]->errShopPermission}{/if}" maxlength="255">
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
										{$language.iso_code}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
											<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					{/foreach}
				</td>
			</tr>
		<?php } ?>
		<tr><td class="key"><label><span>{l s='Minimum value not reached' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" style="width:100%;" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errMinVal]" value="{if isset($row->languages[$language.id_lang|intval]->errMinVal)}{$row->languages[$language.id_lang|intval]->errMinVal}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Minimum product quantity not reached' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" style="width:100%;" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errMinQty]" value="{if isset($row->languages[$language.id_lang|intval]->errMinQty)}{$row->languages[$language.id_lang|intval]->errMinQty}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Customer not logged in' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errUserLogin]" value="{if isset($row->languages[$language.id_lang|intval]->errUserLogin)}{$row->languages[$language.id_lang|intval]->errUserLogin}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Customer not on customer list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errUserNotOnList]" value="{if isset($row->languages[$language.id_lang|intval]->errUserNotOnList)}{$row->languages[$language.id_lang|intval]->errUserNotOnList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Customer not on shopper group list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errUserGroupNotOnList]" value="{if isset($row->languages[$language.id_lang|intval]->errUserGroupNotOnList)}{$row->languages[$language.id_lang|intval]->errUserGroupNotOnList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Per user: already used coupon max number of times' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errUserMaxUse]" value="{if isset($row->languages[$language.id_lang|intval]->errUserMaxUse)}{$row->languages[$language.id_lang|intval]->errUserMaxUse}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Total: already used coupon max number of times' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errTotalMaxUse]" value="{if isset($row->languages[$language.id_lang|intval]->errTotalMaxUse)}{$row->languages[$language.id_lang|intval]->errTotalMaxUse}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(include) Product(s) not on product list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errProductInclList]" value="{if isset($row->languages[$language.id_lang|intval]->errProductInclList)}{$row->languages[$language.id_lang|intval]->errProductInclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Product(s) on product list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errProductExclList]" value="{if isset($row->languages[$language.id_lang|intval]->errProductExclList)}{$row->languages[$language.id_lang|intval]->errProductExclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(include) Product(s) not on category list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCategoryInclList]" value="{if isset($row->languages[$language.id_lang|intval]->errCategoryInclList)}{$row->languages[$language.id_lang|intval]->errCategoryInclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Product(s) on category list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCategoryExclList]" value="{if isset($row->languages[$language.id_lang|intval]->errCategoryExclList)}{$row->languages[$language.id_lang|intval]->errCategoryExclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(include) Product(s) not on manufacturer list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errManufacturerInclList]" value="{if isset($row->languages[$language.id_lang|intval]->errManufacturerInclList)}{$row->languages[$language.id_lang|intval]->errManufacturerInclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Product(s) on manufacturer list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errManufacturerExclList]" value="{if isset($row->languages[$language.id_lang|intval]->errManufacturerExclList)}{$row->languages[$language.id_lang|intval]->errManufacturerExclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(include) Product(s) not on vendor list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errVendorInclList]" value="{if isset($row->languages[$language.id_lang|intval]->errVendorInclList)}{$row->languages[$language.id_lang|intval]->errVendorInclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Product(s) on vendor list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errVendorExclList]" value="{if isset($row->languages[$language.id_lang|intval]->errVendorExclList)}{$row->languages[$language.id_lang|intval]->errVendorExclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='No shipping selected' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errShippingSelect]" value="{if isset($row->languages[$language.id_lang|intval]->errShippingSelect)}{$row->languages[$language.id_lang|intval]->errShippingSelect}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='No valid shipping selected' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errShippingValid]" value="{if isset($row->languages[$language.id_lang|intval]->errShippingValid)}{$row->languages[$language.id_lang|intval]->errShippingValid}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(include) Selected shipping not on shipping list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errShippingInclList]" value="{if isset($row->languages[$language.id_lang|intval]->errShippingInclList)}{$row->languages[$language.id_lang|intval]->errShippingInclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Selected shipping on shipping list' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errShippingExclList]" value="{if isset($row->languages[$language.id_lang|intval]->errShippingExclList)}{$row->languages[$language.id_lang|intval]->errShippingExclList}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Gift certificate already used' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errGiftUsed]" value="{if isset($row->languages[$language.id_lang|intval]->errGiftUsed)}{$row->languages[$language.id_lang|intval]->errGiftUsed}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Coupon value definition, threshold not reached' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errProgressiveThreshold]" value="{if isset($row->languages[$language.id_lang|intval]->errProgressiveThreshold)}{$row->languages[$language.id_lang|intval]->errProgressiveThreshold}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(exclude) Discounted Products' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errDiscountedExclude]" value="{if isset($row->languages[$language.id_lang|intval]->errDiscountedExclude)}{$row->languages[$language.id_lang|intval]->errDiscountedExclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='Exclude Gift Certificate Products' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errGiftcertExclude]" value="{if isset($row->languages[$language.id_lang|intval]->errGiftcertExclude)}{$row->languages[$language.id_lang|intval]->errGiftcertExclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		
		
		
		<tr><td class="key"><label><span>{l s='BuyXY (include) Product(s) not on list 1' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errBuyXYList1IncludeEmpty]" value="{if isset($row->languages[$language.id_lang|intval]->errBuyXYList1IncludeEmpty)}{$row->languages[$language.id_lang|intval]->errBuyXYList1IncludeEmpty}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='BuyXY (exclude) Product(s) on list 1' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errBuyXYList1ExcludeEmpty]" value="{if isset($row->languages[$language.id_lang|intval]->errBuyXYList1ExcludeEmpty)}{$row->languages[$language.id_lang|intval]->errBuyXYList1ExcludeEmpty}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='BuyXY (include) Product(s) not on list 2' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errBuyXYList2IncludeEmpty]" value="{if isset($row->languages[$language.id_lang|intval]->errBuyXYList2IncludeEmpty)}{$row->languages[$language.id_lang|intval]->errBuyXYList2IncludeEmpty}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='BuyXY (exclude) Product(s) on list 2' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errBuyXYList2ExcludeEmpty]" value="{if isset($row->languages[$language.id_lang|intval]->errBuyXYList2ExcludeEmpty)}{$row->languages[$language.id_lang|intval]->errBuyXYList2ExcludeEmpty}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		
		
		
		
		
		<tr><td class="key"><label><span>{l s='(Include) Country' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCountryInclude]" value="{if isset($row->languages[$language.id_lang|intval]->errCountryInclude)}{$row->languages[$language.id_lang|intval]->errCountryInclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(Exclude) Country' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCountryExclude]" value="{if isset($row->languages[$language.id_lang|intval]->errCountryExclude)}{$row->languages[$language.id_lang|intval]->errCountryExclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(Include) State' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCountryStateInclude]" value="{if isset($row->languages[$language.id_lang|intval]->errCountryStateInclude)}{$row->languages[$language.id_lang|intval]->errCountryStateInclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td class="key"><label><span>{l s='(Exclude) State' mod='awocoupon'}</span></label></td>
			<td>
				{foreach from=$languages item=language}
					{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang}">
							<div class="col-lg-9">
					{/if}
					<input type="text" class="error_message" size="60" name="lang[{$language.id_lang|intval}][errCountryStateExclude]" value="{if isset($row->languages[$language.id_lang|intval]->errCountryStateExclude)}{$row->languages[$language.id_lang|intval]->errCountryStateExclude}{/if}" maxlength="255">
					{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{$language.iso_code}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>



		
		</table>
		</div>
	</div>	


		<br /><div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="random" class="button"></div>

		<input type="hidden" name="module" value="awocoupon" />
		<input type="hidden" name="view" value="config" />
		<input type="hidden" name="task" value="store" />

	</form>





	</div>

	{include file="footer_toolbar.tpl"}
</div>

<script type="text/javascript">
	hideOtherLanguage({$id_lang_default});
</script>
