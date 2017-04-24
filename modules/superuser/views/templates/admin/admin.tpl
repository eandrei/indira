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
*  @copyright 2014 idnovate
*  @license   See above
*}

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

{if version_compare($smarty.const._PS_VERSION_, '1.5', '<') || isset($customer.id_shop) && $customer.id_shop == $shop_ori}
    {if isset($customer.is_guest) && $customer.is_guest == '0'}
        <div style="overflow:hidden">
            <label for="use_last_cart" class="control-label">{l s='Restore last cart' mod='superuser'}</label>
            <input type="checkbox" name="use_last_cart" id="use_last_cart" value="1" checked="" />
        </div>
        <div class="row-margin-top margin-form">
            <button class="btn btn-default button" id="frontoffice_url" onClick="window.open('{$frontoffice_url|escape:'htmlall':'UTF-8'}'); return false;">{l s='Open shop as' mod='superuser'} {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'} <i class="icon-external-link"></i></button>
        </div>
    {elseif isset($customer) && $customer.id > 0}
        <div class="not_logged margin-form">
            {l s='Not a registered customer.' mod='superuser'} {l s='You cannot connect as this customer.' mod='superuser'}
        </div>
    {/if}
{elseif isset($customer) && $customer.id > 0}
    <div class="not_logged margin-form">
        {l s='This customer does not belong to this shop.' mod='superuser'} {l s='You cannot connect as this customer. Change shop scope before.' mod='superuser'}
    </div>
{/if}