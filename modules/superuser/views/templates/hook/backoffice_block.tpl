{**
* Super User Module
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2016 idnovate
*  @license   See above
*}

<style type="text/css">
	.bootstrap #superuser {
		padding: 10px;
		border: solid 1px #e6e6e6;
		background-color: #fff;
		-webkit-border-radius: 5px;
		border-radius: 5px;
		-webkit-box-shadow: rgba(0,0,0,0.1) 0 2px 0,#fff 0 0 0 3px inset;
		box-shadow: rgba(0,0,0,0.1) 0 2px 0,#fff 0 0 0 3px inset;
	}
	.bootstrap #superuser h2 {
		margin: 0;
	}
	.bootstrap #superuser .logged,
	.bootstrap #superuser .logged a {
		text-align: center;
		font-weight: bold;
		color: black;
		font-size: 13px;
	}
	.bootstrap #superuser .panel-heading {
		border: none;
		font-size: 1.2em;
		line-height: 2.2em;
		height: 2.2em;
		text-transform: uppercase;
		border-bottom: solid 1px #eee;
		padding: 0 0 0 5px;
		margin: -20px -16px 15px -16px;
		border-top-right-radius: 2px;
		border-top-left-radius: 2px;
	}
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $('input#use_last_cart').change(function() {
            var href = $('a#frontoffice_url')[0].href;
            if($(this).is(":checked")) {
                $('a#frontoffice_url')[0].href = href.replace('use_last_cart=0','use_last_cart=1');
            } else {
                $('a#frontoffice_url')[0].href = href.replace('use_last_cart=1','use_last_cart=0');
            }
        });
    });
</script>

<div class="clear">&nbsp;</div>

<div class="panel" id="superuser">
	<form method="post">
		{if version_compare($smarty.const._PS_VERSION_, '1.5', '<')}<h2>{else}<div class="panel-heading">{/if}
			<i class="icon-user"></i>
			{$displayName|escape:'htmlall':'UTF-8'}
		{if version_compare($smarty.const._PS_VERSION_, '1.5', '<')}</h2>{else}</div>{/if}
		{if version_compare($smarty.const._PS_VERSION_, '1.5', '<')}<fieldset>{else}<div class="row">{/if}
		    {if version_compare($smarty.const._PS_VERSION_, '1.5', '<') || isset($customer.id_shop) && $customer.id_shop == $shop_ori}
    			{if isset($customer.is_guest) && $customer.is_guest == '0'}
    				<div class="logged">
    					<div style="overflow:hidden">
	                        <label class="control-label" for="use_last_cart">{l s='Restore last cart' mod='superuser'}</label>
	                        <input type="checkbox" name="use_last_cart" id="use_last_cart" value="1" checked="" />
	                    </div>
                        <div class="row-margin-top center">
                            <button class="btn btn-default button" id="frontoffice_url" onClick="window.open('{$frontoffice_url|escape:'htmlall':'UTF-8'}'); return false;">{l s='Connect as' mod='superuser'} {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'} <i class="icon-external-link"></i></button>
    					</div>
    				</div>
    			{else}
                    <div class="not_logged">
                        {l s='Not a registered customer.' mod='superuser'} {l s='You cannot connect as this user.' mod='superuser'}
                    </div>
    			{/if}
    		{else}
                <div class="not_logged">
                    {l s='This customer does not belong to this shop.' mod='superuser'} {l s='You cannot connect now as this user. Change shop scope before.' mod='superuser'}
                </div>
    		{/if}
		{if version_compare($smarty.const._PS_VERSION_, '1.5', '<')}</fieldset>{else}</div>{/if}
	</form>
</div>