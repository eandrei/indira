<script>
    fbq('track', 'InitiateCheckout');
</script>
{if !isset($empty)}
    <script>
        var supercheckoutLayout = {$settings['layout']|intval};
        var currencyFormat = {$currencyFormat|intval};
        var currencySign = "{$currencySign|escape:'htmlall':'UTF-8'}";
        var currencyBlank = {$currencyBlank|intval};
        var static_token = "{$token_cart|escape:'htmlall':'UTF-8'}";
        var supercheckout_image_path = "{$module_image_path|escape:'htmlall':'UTF-8'}";
        var empty_cart_warning = "{$empty_cart_warning|escape:'htmlall':'UTF-8'}";
        var notification = "{$notification|escape:'htmlall':'UTF-8'}";
        var warning = "{$warning|escape:'htmlall':'UTF-8'}";
        var product_remove_success = "{$product_remove_success|escape:'htmlall':'UTF-8'}";
        var product_qty_update_success = "{$product_qty_update_success|escape:'htmlall':'UTF-8'}";
        var freeShippingTranslation = "{l s='Free Shipping' mod='supercheckout'}";
        var noShippingRequired = "{l s='No Delivery Method Required' mod='supercheckout'}";
        var ShippingRequired = "{l s='Delivery Method Required' mod='supercheckout'}";
        var paymentRequired = "{l s='Payment Method Required' mod='supercheckout'}";
        var updateSameQty = "{l s='No change found in quantity' mod='supercheckout'}";
        var scInvalidQty = "{l s='Invalid Quantity' mod='supercheckout'}";
        var scOtherError = "{l s='Technical Error Occured. Please contact to support.' mod='supercheckout'}";
        var commentInvalid = "{l s='Message is in invalid format' mod='supercheckout'}";
        var tosRequire = "{l s='Please acccept our terms & conditions before confirming your order' mod='supercheckout'}";
        var requestToLogin = "{l s='Please login first' mod='supercheckout'}";
        var ajaxRequestFailedMsg = "{l s='TECHNICAL ERROR: Request Failed' mod='supercheckout'}";
        var validationfailedMsg = "{l s='Please provide required Information' mod='supercheckout'}";
        var totalVoucherText = "{l s='Total Vouchers' mod='supercheckout'}";
        var tax_incl_text = "{l s='(Tax incl.)' mod='supercheckout'}";
        var tax_excl_text = "{l s='(Tax excl.)' mod='supercheckout'}";
        var formatedAddressFieldsValuesList = null;
        {if $formatedAddressFieldsValuesList != null}
        formatedAddressFieldsValuesList = {$formatedAddressFieldsValuesList|json_encode};
        {/if}
        var idAddress_delivery = {$id_address_delivery|intval};
        var scp_use_taxes = {$use_taxes|escape:'htmlall':'UTF-8'};
        var scp_order_total_price = {$total_price|escape:'htmlall':'UTF-8'};
        var scp_order_total_price_wt = {$total_price_without_tax|escape:'htmlall':'UTF-8'};
        var scp_guest_tracking_url = "{$link->getPageLink("guest-tracking", true)|addslashes}";		//Variable contains url, escape not required
        var scp_history_url = "{$link->getPageLink("history", true)|addslashes}";	//Variable contains url, escape not required
        var payment_method_url = "{$payment_method_url}";	//Variable contains url, escape not required
        var payment_content_id = 'center_column';
        var scp_required_tos = {$settings['confirm']['term_condition'][$user_type]['require']|intval};
        var iscartvirtual = false;
        {if $isvirtualcart eq true}
        iscartvirtual = true;
        {/if}
        var orderOpcUrl = "{$link->getPageLink("order-opc", true)|escape:'quotes':'UTF-8'}";
        var required_error = "{l s='Required Field' mod='supercheckout'}";
        var invalid_email = "{l s='Email is invalid' mod='supercheckout'}";
        var pwd_error = "{l s='(Five characters minimum)' mod='supercheckout'}";
        var invalid_city = "{l s='Special Characters !<>;?=+@#"°{}_$% are not allowed' mod='supercheckout'}";
        var invalid_address = "{l s='Special Characters !<>?=+@{}_$% are not allowed' mod='supercheckout'}";
        var invalid_title = "{l s='Special Characters <>={} are not allowed' mod='supercheckout'}";
        var invalid_number = "{l s='Only +.-() and numbers are allowed' mod='supercheckout'}";
        var invalid_other_info = "{l s='Special Characters <>{} are not allowed' mod='supercheckout'}";
        var invalid_dob = "{l s='Invalid Date of Birth' mod='supercheckout'}";
        var invalid_name = "{l s='Name is invalid' mod='supercheckout'}";
        var number_error = "{l s='Numbers not allowed' mod='supercheckout'}";
        var splchar_error = "{l s='Special Characters !<>,;?=+()@#"°{}_$%: are not allowed' mod='supercheckout'}";
        var inline_validation = {$settings['inline_validation']['enable']|intval};

    </script>
    {assign var='login_boxes_width' value=50|intval}
    {if $settings['fb_login']['enable'] || $settings['fb_login']['enable']}
        {$login_boxes_width = 33}
    {/if}
    <style>
        {literal}
        .supercheckout_top_boxes{width:{/literal}{$login_boxes_width|intval}{literal}%;}
        {/literal}
    </style>
    {*{if isset($HOOK_EXTRACARRIER)}*}
    {*{$HOOK_EXTRACARRIER}{*Variable contains html content, escape not required*}
    {*{/if}*}
    <a style="display:none;" href="javascript:void(0)" id="bancasella_process_payment" ></a>
    {capture name=path}<span class="navigation_page">{l s='Your shopping cart' mod='supercheckout'}</span>{/capture}
    <div id="fb-root"></div>
    <div id="supercheckout-empty-page-content" class="supercheckout-empty-page-content" style="display:block">
        {if isset($velsof_errors) && count($velsof_errors) > 0}

            <div class="permanent-warning">
                {foreach $velsof_errors as $err}
                    {$err|escape:'htmlall':'UTF-8'}<br>
                {/foreach}</div>
        {/if}
    </div>
    <form id="velsof_supercheckout_form" action="{$supercheckout_url|escape:'htmlall':'UTF-8'}" method="POST">
        <input type='hidden' name='{$plugin_name|escape:'htmlall':'UTF-8'}PlaceOrder' value='1' />

        <div id="submission_progress_overlay" class="submit_progress_disable"></div>
        <div id="supercheckout_order_progress_bar">
            <div class="supercheckout_order_progress_status">
                <div id="supercheckout_order_progress_status_text">20%</div>
                <img src="{$module_image_path|escape:'htmlall':'UTF-8'}progress.gif" />
                {*<div id="order_progress_status_color1"></div><div id="order_progress_status_color2"></div><div id="order_progress_status_text">20%</div>*}
            </div>
        </div>
        <fieldset class="group-select" id="supercheckout-fieldset">

            <div class="supercheckout-threecolumns supercheckout-container supercheckout-skin-generic " id="supercheckout-columnleft">

                {assign var='layout_name' value='1_column'}
                {assign var='multiplier' value=1}
                {if $ps_version eq 15}
                    {assign var='multiplier_3' value=0.895}
                    {assign var='multiplier_2' value=0.935}
                {else}
                    {assign var='multiplier_3' value=0.98}
                    {assign var='multiplier_2' value=0.99}
                {/if}
                {if $settings['layout'] eq 3}
                    {$layout_name='3_column'}
                    {$multiplier=$multiplier_3}
                {else if $settings['layout'] eq 2}
                    {$layout_name='2_column'}
                    {$multiplier=$multiplier_2}
                {/if}

                <div class="supercheckout-column-left columnleftsort" id="columnleft-1" style="width:{$settings['column_width'][$layout_name][1]*$multiplier|escape:'htmlall':'UTF-8'}%">
                    <div  class="supercheckout-blocks" data-column="{$settings['design']['login'][$layout_name]['column']|intval}" data-row="{$settings['design']['login'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['login'][$layout_name]['column-inside']|intval}"  >

                        <div id="checkoutLogin">
                            <div class="supercheckout-checkout-content"></div>
                            {if $logged}

                            {else}
                                <div class="supercheckout-extra-wrap">
                                    {l s='Email' mod='supercheckout'}<span class="supercheckout-required">*</span><br />
                                    <input type="text" id="email" name="supercheckout_email" value="" class="supercheckout-large-field" />
                                </div>
                                <div id="supercheckout-option" style="display:block">
                                    <div class="supercheckout-extra-wrap">
                                        {if $settings['checkout_option'] eq 0}
                                            <input type="radio" name="checkout_option" value="0" id="logged_checkout" checked="checked"/>
                                        {else}
                                            <input type="radio" name="checkout_option" value="0" id="logged_checkout"/>
                                        {/if}
                                        <label for="logged_checkout">{l s='Login into shop' mod='supercheckout'}</label>
                                        <br />
                                    </div>

                                    <div class="supercheckout-extra-wrap">
                                        {if $settings['checkout_option'] eq 2 || ($settings['enable_guest_checkout'] eq 0 && $settings['checkout_option'] eq 1)}
                                            <input type="radio" name="checkout_option" value="2" id="register_checkout" checked="checked" />
                                        {else}
                                            <input type="radio" name="checkout_option" value="2" id="register_checkout" />
                                        {/if}
                                        <label for="register_checkout">{l s='Create an account for later use' mod='supercheckout'}</label>
                                        <br />
                                    </div>
                                </div>
                                <div id="supercheckout-login-box" style="display:{if $settings['checkout_option'] eq 0}block{else}none{/if};">
                                    <div id="supercheckout-login-password-box" class="supercheckout-extra-wrap">
                                        {l s='Password' mod='supercheckout'}<span class="supercheckout-required">*</span><br />
                                        <input type="password" id="password" name="supercheckout_password" onkeydown="checkAction(event)" value="" class="supercheckout-large-field" />
                                    </div>
                                    <div id="supercheckout-login-action" class="supercheckout-extra-wrap">
                                        <div id="forgotpasswordlink"><a href="{$forgotten_link|escape:'htmlall':'UTF-8'}">{l s='Forgot Password' mod='supercheckout'}</a></div>
                                        <br>
                                        <input type="hidden" name="SubmitLogin" value="SubmitLogin" />
                                        <input type="button" value="{l s='Login' mod='supercheckout'}" id="button-login" class="orangebuttonsmall" /><img src="{$module_image_path|escape:'htmlall':'UTF-8'}loading12.gif" style="display:none;"/><br />
                                    </div>
                                </div>
                                <div id="supercheckout-new-customer-form" style="display:{if $settings['checkout_option'] neq 0}block{else}none{/if};">
                                    <table id="customer_person_information_table" class="supercheckout-form" style="margin-bottom:0 !important;">
                                        <tr id="new_customer_password" class="sort_data"  data-percentage="0" style="display:{if $settings['checkout_option'] eq 2}block{else}none{/if};" >
                                            <td>
                                                <div class="inline-fields" style="margin-right: 18px;">{l s='Password' mod='supercheckout'}:<span style="display:inline;" class="supercheckout-required">*</span></div>
                                                <div class="supercheckout-large-field">
                                                    <input type="password" id="password" name="customer_personal[password]" value="" class="supercheckout-large-field" />
                                                </div>
                                            </td>
                                        </tr>
                                        {foreach from=$settings['customer_personal'] key='cus_per_info' item='cus_info_field'}
                                            {if $settings['customer_personal'][$cus_per_info][$user_type]['display'] eq 1}
                                                <tr class="sort_data"  data-percentage="{$settings['customer_personal'][$cus_per_info]['sort_order']|intval}" >
                                                    <td>
                                                        {if $cus_per_info eq 'id_gender'}
                                                            <div class="inline-fields" style="margin-right: 18px;">{l s={$settings['customer_personal'][$cus_per_info]['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}:<span style="display:{if $settings['customer_personal'][$cus_per_info][$user_type]['require'] eq 1}inline{else}none{/if};" class="supercheckout-required">*</span></div>
                                                            <div class="supercheckout_personal_id_gender inline-fields supercheckout-large-field">
                                                                <div class="">
                                                                    {foreach from=$genders key=k item=gender}
                                                                        <div class="inline-fields" style="width: 50px;">
                                                                            <div class="radio" id="uniform-customer_male_title"><span class="checked"><input type="radio" name="customer_personal[id_gender]" value="{$gender->id|intval}" id="customer_gender_{$gender->id|intval}" checked="checked" /></span></div>
                                                                            <label for="customer_gender_{$gender->id|intval}">{$gender->name|escape:'htmlall':'UTF-8'}</label>
                                                                        </div>
                                                                    {/foreach}
                                                                </div>
                                                            </div>
                                                        {else if $cus_per_info eq 'dob'}
                                                            <div class="inline-fields" style="margin-right: 18px;">{l s={$settings['customer_personal'][$cus_per_info]['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}:<span style="display:{if $settings['customer_personal'][$cus_per_info][$user_type]['require'] eq 1}inline{else}none{/if};" class="supercheckout-required">*</span></div>
                                                            <div class="supercheckout_personal_dob inline-fields supercheckout-large-field">
                                                                <div class="">
                                                                    <select name="customer_personal[dob_days]">
                                                                        <option value="">--</option>
                                                                        {foreach from=$days item='day'}
                                                                            <option value="{$day|intval}">{$day|intval}</option>
                                                                        {/foreach}
                                                                    </select>
                                                                    <select name="customer_personal[dob_months]">
                                                                        <option value="">--</option>
                                                                        {foreach from=$months key=month_value item=month_name}
                                                                            <option value="{$month_value|escape:'htmlall':'UTF-8'}">{$month_name|escape:'htmlall':'UTF-8'}</option>
                                                                        {/foreach}
                                                                    </select>
                                                                    <select name="customer_personal[dob_years]">
                                                                        <option value="">--</option>
                                                                        {foreach from=$years item='year'}
                                                                            <option value="{$year|escape:'htmlall':'UTF-8'}">{$year|escape:'htmlall':'UTF-8'}</option>
                                                                        {/foreach}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                        {foreach from=$settings['customer_subscription'] key='cus_subs_info' item='cus_info_field'}
                                            {if $settings['customer_subscription'][$cus_subs_info]['guest']['display'] eq 1}
                                                <tr class="sort_data"  data-percentage="{$settings['customer_subscription'][$cus_subs_info]['sort_order']|intval}" >
                                                    <td>
                                                        <div class="input-box" >
                                                            <input type="checkbox" class="supercheckout_offers_option" name="customer_personal[{$cus_subs_info|escape:'htmlall':'UTF-8'}]" id="customer_personal_{$cus_subs_info|escape:'htmlall':'UTF-8'}"  {if $settings['customer_subscription'][$cus_subs_info]['guest']['checked'] eq 1}checked="checked"{/if} >
                                                            <label for="customer_personal_{$cus_subs_info|escape:'htmlall':'UTF-8'}">{l s=$settings['customer_subscription'][$cus_subs_info]['title']|escape:'htmlall' mod='supercheckout'}</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                    </table>
                                </div>
                                <div id="social_login_block">
                                    {if $settings['fb_login']['enable'] neq 1 && $settings['google_login']['enable'] neq 1}
                                        <script>
                                            if((typeof show_on_supercheckout != 'undefined') && show_on_supercheckout == 'small_buttons') {
                                                document.write('<div class="orSeparator"><span>{l s='OR' mod='supercheckout'}</span></div> <h3>{l s='Sign in with' mod='supercheckout'}</h3>');
                                                document.write(loginizer_small);
                                                document.write('<div style="height:10px;"></div>');
                                            }
                                            else if((typeof show_on_supercheckout != 'undefined') && show_on_supercheckout == 'large_buttons') {
                                                document.write('<div class="orSeparator"><span>{l s='OR' mod='supercheckout'}</span></div> <h3>{l s='Sign in with' mod='supercheckout'}</h3>');
                                                document.write(loginizer_large);
                                            }
                                        </script>
                                    {/if}
                                    {if $settings['fb_login']['enable'] eq 1 || $settings['google_login']['enable'] eq 1}
                                        <div class="orSeparator"><span>{l s='OR' mod='supercheckout'}</span></div>
                                        <h3>{l s='Sign in with' mod='supercheckout'}</h3>
                                        <div class="socialNetwork">
                                            {if $settings['fb_login']['enable'] eq 1}
                                                {if $settings['social_login_popup']['enable'] eq 1}
                                                    <a onclick="return !window.open(this.href, 'popup', 'width=450,height=300,left=500,top=500')" target="_blank" href="{$supercheckout_url|escape:'htmlall':'UTF-8'}&myfbLogin" class="fbButton" id="fb-auth" ></a>
                                                {else}
                                                    <a href="{$supercheckout_url|escape:'htmlall':'UTF-8'}&myfbLogin" class="fbButton" id="fb-auth" ></a>
                                                {/if}
                                            {/if}
                                            {if $settings['google_login']['enable'] eq 1}
                                                {if $settings['social_login_popup']['enable'] eq 1}
                                                    <a onclick="return !window.open(this.href, 'popup', 'width=500,height=500,left=500,top=500')" target="_blank" href="{$supercheckout_url|escape:'htmlall':'UTF-8'}&myGoogleLogin" class="googleButton" ></a>
                                                {else}
                                                    <a href="{$supercheckout_url|escape:'htmlall':'UTF-8'}&myGoogleLogin" class="googleButton" ></a>
                                                {/if}
                                            {/if}
                                            <div class="supercheckout-clear"></div>
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                        </div>

                    </div>
                    <div class="supercheckout-blocks" data-column="{$settings['design']['shipping_address'][$layout_name]['column']|intval}" data-row="{$settings['design']['shipping_address'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['shipping_address'][$layout_name]['column-inside']|intval}">
                        {if $isvirtualcart eq true}
                        <div id="checkoutShippingAddress" style="display:none;">
                            {else}
                            <div id="checkoutShippingAddress">
                                {/if}
                                <div class="supercheckout-checkout-content"></div>

                                <div class="supercheckout-column-title">
                                    <div class="supercheckout-column-title-number">1</div>
                                    <div class="supercheckout-numbers">{l s='Delivery Address' mod='supercheckout'}</div>
                                </div>
                                {if $addresses}
                                    <div class="supercheckout-extra-wrap">
                                        <input type="radio" name="shipping_address_value" value="0" id="shipping-address-existing" checked="checked" />
                                        <label for="shipping-address-existing">{l s='Use Existing Address' mod='supercheckout'}</label>
                                    </div>
                                    <div id="shipping-existing" class="styled-select">
                                        <select name="shipping_address_id" style="width: 92%; margin-bottom: 15px;">
                                            {foreach from=$addresses item='shipping_addr'}
                                                <option value="{$shipping_addr['id_address']|intval}" {if $shipping_addr['id_address'] == $id_address_invoice} selected="selected"{/if}>{$shipping_addr['alias']|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                        <div id="delivery_address_detail" class="supercheckout_address_detail"></div>
                                    </div>
                                    <div class="supercheckout-extra-wrap">
                                        <p>
                                            <input type="radio" name="shipping_address_value" value="1" id="shipping-address-new" />
                                            <label for="shipping-address-new">{l s='Use New Address' mod='supercheckout'}</label>
                                        </p>
                                    </div>
                                {/if}
                                <div id="shipping-new"style="display: {if $addresses}none{else}block{/if};">

                                    <ul>

                                        <li class="clearfix">
                                            <div class="input-box input-firstname">
                                                <input placeholder="{l s={$settings['shipping_address']['firstname']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="30" required id="shipping:firstname" type="text" name="shipping_address[{'firstname'|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" />
                                            </div>
                                            <div class="input-box input-lastname">
                                                <input placeholder="{l s={$settings['shipping_address']['lastname']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="30" required id="shipping:lastname" type="text" name="shipping_address[{'lastname'|escape:'htmlall':'UTF-8'}]" value="" class="" />
                                            </div>
                                        </li>
                                        <li class="clearfix">
                                            <div class="input-box">
                                                <input placeholder="{l s={$settings['shipping_address']['address1']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="30" required id="shipping:address1" type="text" name="shipping_address[{'address1'|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" />
                                            </div>
                                        </li>
                                        <li class="clearfix">
                                            <div class="input-box">
                                                <input placeholder="{l s={$settings['shipping_address']['address2']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="30" id="shipping:address2" type="text" name="shipping_address[{'address2'|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" />
                                            </div></li>
                                        <li class="clearfix">

                                            <div class="input-box input-city">
                                                <input placeholder="{l s={$settings['shipping_address']['city']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="30" required id="shipping:city" type="text" name="shipping_address[{'city'|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" />
                                            </div>
                                            <div class="input-box input-region">
                                                <script>var show_shipping_state = {$settings['shipping_address']['id_state'][$user_type]['display']|escape:'htmlall':'UTF-8'};</script>

                                                <select  id="shipping:id_state" name="shipping_address[id_state]" class="supercheckout-large-field">
                                                    <option value="0">{l s='Select State' mod='supercheckout'}</option>
                                                </select>

                                            </div>
                                            <div class="input-box input-postcode">
                                                <script>var show_shipping_postcode = {$settings['shipping_address']['postcode'][$user_type]['display']|escape:'htmlall':'UTF-8'};</script>

                                                <input placeholder="{l s={$settings['shipping_address']['postcode']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="10" required id="shipping:postcode" type="text" name="shipping_address[postcode]" value="" class="supercheckout-large-field" />

                                            </div>
                                        </li>
                                        <select style="display:none" name="shipping_address[id_country]" class="supercheckout-large-field"><option value="36" selected="selected">Romania</option></select>
                                        <li class="clearfix">
                                            <div class="input-box input-telephone">
                                                <input placeholder="{l s={$settings['shipping_address']['phone_mobile']['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}" maxlength="19" required id="shipping:phone_mobile" type="text" name="shipping_address[phone_mobile]" value="" class="supercheckout-large-field" />
                                            </div>
                                        </li>

                                    </ul>


                                </div>
                                <ul>
                                    <li>
                                        <div class="input-box input-different-shipping" {if !$settings['show_use_delivery_for_payment_add'][$user_type]} style="display:none;" {/if}>
                                            <input type="checkbox" name="use_for_invoice" id="use_for_invoice" {if $settings['use_delivery_for_payment_add'][$user_type]}checked="checked"{/if} >
                                            <label for="use_for_invoice"><b>{l s='Same invoice address' mod='supercheckout'}</b></label>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div>
                        <div  class="supercheckout-blocks"  data-column="{$settings['design']['payment_address'][$layout_name]['column']|intval}" data-row="{$settings['design']['payment_address'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['payment_address'][$layout_name]['column-inside']|intval}">
                            <div id="checkoutBillingAddress" style="display:{if $settings['use_delivery_for_payment_add'][$user_type]}none{else}block{/if};">
                                <ul>
                                    <li>
                                        <p class="supercheckout-numbers supercheckout-numbers-2">{l s='Invoice Address' mod='supercheckout'}</p>
                                    </li>
                                </ul>
                                <div class="supercheckout-checkout-content"></div>
                                {if $addresses}
                                    <div class="supercheckout-extra-wrap">
                                        <input type="radio" name="payment_address_value" value="0" id="payment-address-existing" checked="checked" />
                                        <label for="payment-address-existing">{l s='Use Existing Address' mod='supercheckout'}</label>
                                    </div>
                                    <div id="payment-existing">
                                        <select name="payment_address_id" style="width: 92%; margin-bottom: 15px;">
                                            {foreach from=$addresses item='payment_addr'}
                                                <option value="{$payment_addr['id_address']|intval}" {if $payment_addr['id_address'] == $id_address_invoice} selected="selected"{/if}>{$payment_addr['alias']|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                        <div id="invoice_address_detail" class="supercheckout_address_detail"></div>
                                    </div>
                                    <div class="supercheckout-extra-wrap">
                                        <p>
                                            <input type="radio" name="payment_address_value" value="1" id="payment-address-new" />
                                            <label for="payment-address-new">{l s='Use New Address' mod='supercheckout'}</label>
                                        </p>
                                    </div>
                                {/if}
                                <div id="payment-new" style="display: {if $addresses}none{else}block{/if};">
                                    <table id="payment_address_table" class="supercheckout-form">
                                        {assign var='display_row' value=''}
                                        {foreach from=$settings['payment_address'] key='p_address_key' item='p_address_field'}
                                            {$display_row = ''}
                                            {if $settings['payment_address'][$p_address_key][$user_type]['display'] eq 1 || (isset($settings['payment_address'][$p_address_key]['conditional']) && $settings['payment_address'][$p_address_key]['conditional'] eq 1)}
                                                {if $p_address_key eq 'dni' && !$need_dni}
                                                    {$display_row = 'display:none;'}
                                                {/if}
                                                {if $p_address_key eq 'vat_number' && !$need_vat}
                                                    {$display_row = 'display:none;'}
                                                {/if}
                                                {if ($p_address_key eq 'postcode' || $p_address_key eq 'id_country' || $p_address_key eq 'id_state' || $p_address_key eq 'alias') && !$settings['payment_address'][$p_address_key][$user_type]['require'] && !$settings['payment_address'][$p_address_key][$user_type]['display']}
                                                    {$display_row = 'display:none;'}
                                                {/if}
                                            {if $p_address_key eq 'id_state'}
                                                <script>var show_payment_state = {$settings['payment_address'][$p_address_key][$user_type]['display']|escape:'htmlall':'UTF-8'};</script>
                                            {/if}
                                            {if $p_address_key eq 'postcode'}
                                                <script>var show_payment_postcode = {$settings['payment_address'][$p_address_key][$user_type]['display']|escape:'htmlall':'UTF-8'};</script>
                                                <tr class="sort_data" id="payment_post_code" data-percentage="{$settings['payment_address'][$p_address_key]['sort_order']|intval}" style="{$display_row|escape:'htmlall':'UTF-8'}" >
                                                    {else}
                                                <tr class="sort_data" data-percentage="{$settings['payment_address'][$p_address_key]['sort_order']|intval}" style="{$display_row|escape:'htmlall':'UTF-8'}" >
                                                    {/if} <td>{l s={$settings['payment_address'][$p_address_key]['title']|escape:'htmlall':'UTF-8'} mod='supercheckout'}:<span style="display:{if $settings['payment_address'][$p_address_key][$user_type]['require'] eq 1}inline{else}none{/if};" class="supercheckout-required">*</span>
                                                        {if $p_address_key eq 'id_country' || $p_address_key eq 'id_state'}
                                                            <select name="payment_address[{$p_address_key|escape:'htmlall':'UTF-8'}]" class="supercheckout-large-field">
                                                                {if $p_address_key eq 'id_country'}
                                                                    {foreach from=$countries item='country'}
                                                                        <option value="{$country['id_country']|intval}" {if $country['id_country'] == $default_country} selected="selected"{/if}>{$country['name']|escape:'html':'UTF-8'}</option>
                                                                    {/foreach}
                                                                {else}
                                                                    <option value="0">{l s='Select State' mod='supercheckout'}</option>
                                                                {/if}
                                                            </select>
                                                        {else if $p_address_key eq 'dob'}
                                                            <div class="supercheckout_dob_box supercheckout-large-field">
                                                                <select name="payment_address[dob_days]">
                                                                    <option value="">--</option>
                                                                    {foreach from=$days item='day'}
                                                                        <option value="{$day|intval}">{$day|intval}</option>
                                                                    {/foreach}
                                                                </select>
                                                                <select name="payment_address[dob_months]">
                                                                    <option value="">--</option>
                                                                    {foreach from=$months item='month'}
                                                                        <option value="{$month|escape:'htmlall':'UTF-8'}">{$month|escape:'htmlall':'UTF-8'}</option>
                                                                    {/foreach}
                                                                </select>
                                                                <select name="payment_address[dob_years]">
                                                                    <option value="">--</option>
                                                                    {foreach from=$years item='year'}
                                                                        <option value="{$year|escape:'htmlall':'UTF-8'}">{$year|escape:'htmlall':'UTF-8'}</option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                        {else if  $p_address_key eq 'other'}
                                                            <textarea name="payment_address[{$p_address_key|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" style="width: 96%;"></textarea>
                                                        {else}
                                                            <input type="text" name="payment_address[{$p_address_key|escape:'htmlall':'UTF-8'}]" value="" class="supercheckout-large-field" />
                                                        {/if}

                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                    </table>
                                </div>
                            </div>
                            <br/>
                        </div>

                        <div style="{if $settings['shipping_method']['enable'] eq 0}display:none;{/if}"  class="supercheckout-blocks" data-column="{$settings['design']['shipping_method'][$layout_name]['column']|intval}" data-row="{$settings['design']['shipping_method'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['shipping_method'][$layout_name]['column-inside']|intval}" >

                            <div class="supercheckout-column-title">
                                <div class="supercheckout-column-title-number">2</div>
                                <div class="supercheckout-numbers">{l s='Delivery Method' mod='supercheckout'}</div>
                                <div class="loader" id="shippingMethodLoader"></div>
                            </div>


                            <div id="shipping-method">
                                <script>
                                    {if isset($IS_VIRTUAL_CART) && $IS_VIRTUAL_CART}
                                    var is_cart_virtual = {$IS_VIRTUAL_CART|escape:'htmlall':'UTF-8'};
                                    {/if}
                                    var carriers_count = {if isset($carriers_count)}{$carriers_count|escape:'htmlall':'UTF-8'}{else}0{/if};
                                </script>
                                {if isset($IS_VIRTUAL_CART) && $IS_VIRTUAL_CART}
                                    <input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
                                    <div class="supercheckout-checkout-content" style="display:block">
                                        <div class="not-required-msg" style="display: block;">{l s='No Delivery Method Required' mod='supercheckout'}</div>
                                    </div>
                                {else}
                                    {if isset($shipping_errors) && is_array($shipping_errors)}
                                        {foreach from=$shipping_errors item='shippig_error'}
                                            <div class="supercheckout-checkout-content" style="display:block">
                                                <div class="permanent-warning" style="display: block;">{$shippig_error|escape:'htmlall':'UTF-8'}</div>
                                            </div>
                                        {/foreach}
                                    {else}
                                        <div class="supercheckout-checkout-content" style="display:block"></div>
                                    {/if}

                                    {if isset($delivery_option_list)}
                                        {foreach $cart->getDeliveryAddressesWithoutCarriers(true) as $address}
                                            <input type="hidden" name="no_shipping_method" value="1">
                                            <div class="supercheckout-checkout-content" style="display:block">
                                                <div class="permanent-warning" style="display: block;">
                                                    {if empty($address->alias)}
                                                        {l s='No Delivery Method Available' mod='supercheckout'}
                                                    {else}
                                                        {l s='No Delivery Method Available for this Address' mod='supercheckout'}
                                                    {/if}
                                                </div>
                                            </div>
                                            {foreachelse}
                                            {foreach $delivery_option_list as $id_address => $option_list}
                                                <table class="radio">
                                                    {foreach $option_list as $key => $option}
                                                        <tr class="highlight">
                                                            <td>
                                                                {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}
                                                                    <input class='supercheckout_shipping_option delivery_option_radio' type="radio" name="delivery_option[{$id_address|intval}]" value="{$key|escape:'htmlall':'UTF-8'}" id="shipping_method_{$id_address|intval}_{$option@index|intval}" checked="checked" />
                                                                {else if isset($default_shipping_method) && $key == $default_shipping_method}
                                                                    <input class='supercheckout_shipping_option delivery_option_radio' type="radio" name="delivery_option[{$id_address|intval}]" value="{$key|escape:'htmlall':'UTF-8'}" id="shipping_method_{$id_address|intval}_{$option@index|intval}" checked="checked" />
                                                                {else}
                                                                    <input class='supercheckout_shipping_option delivery_option_radio' type="radio" name="delivery_option[{$id_address|intval}]" value="{$key|escape:'htmlall':'UTF-8'}" id="shipping_method_{$id_address|intval}_{$option@index|intval}" />
                                                                {/if}

                                                            </td>
                                                            <td class="shipping_info">
                                                                <label for="shipping_method_{$id_address|intval}_{$option@index|intval}">
                                                                    {assign var='sub_carriers_count' value=count($option.carrier_list)}
                                                                    {if $display_carrier_style neq 0}
                                                                        {foreach $option.carrier_list as $carrier}
                                                                            {if $carrier.logo}
                                                                                <img src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}" {if isset($carrier.width) && $carrier.width != ""}width="{$carrier.width|escape:'htmlall':'UTF-8'}"{else}width='95'{/if} {if isset($carrier.height) && $carrier.height != ""}height="{$carrier.height|escape:'htmlall':'UTF-8'}"{else}height='20'{/if}/>{if $display_carrier_style neq 2}<br>{/if}
                                                                            {/if}
                                                                        {/foreach}
                                                                    {/if}
                                                                    {if $display_carrier_style neq 2}
                                                                        {if $option.unique_carrier}
                                                                            {foreach $option.carrier_list as $carrier}
                                                                                {$carrier.instance->name|escape:'htmlall':'UTF-8'}
                                                                            {/foreach}
                                                                        {/if}
                                                                        {if !$option.unique_carrier}
                                                                            {foreach $option.carrier_list as $carrier}
                                                                                {$carrier.instance->name|escape:'htmlall':'UTF-8'}
                                                                                {$sub_carriers_count = $sub_carriers_count - 1}
                                                                                {if ($sub_carriers_count lt $option.carrier_list) && $sub_carriers_count gt 0}&{/if}
                                                                            {/foreach}
                                                                        {/if}
                                                                        {if count($option_list) > 1}
                                                                            {if $option.is_best_grade}
                                                                                {if $option.is_best_price}
                                                                                    {l s='The best price and speed' mod='supercheckout'}
                                                                                {else}
                                                                                    {l s='The Fastest' mod='supercheckout'}
                                                                                {/if}
                                                                            {else}
                                                                                {if $option.is_best_price}
                                                                                    {l s='The Best Price' mod='supercheckout'}
                                                                                {/if}
                                                                            {/if}
                                                                        {/if}
                                                                    {/if}
                                                                </label>
                                                                {if $option.unique_carrier && isset($carrier.instance->delay[$cookie->id_lang])}
                                                                    <span class="supercheckout-shipping-small-title">{$carrier.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}</span>
                                                                {/if}
                                                                {if count($option_list) > 1}
                                                                    <span class="supercheckout-shipping-small-title">
								    {if $option.is_best_grade}
                                        {if $option.is_best_price}
                                            {l s='The best price and speed' mod='supercheckout'}
                                        {else}
                                            {l s='The Fastest' mod='supercheckout'}
                                        {/if}
                                    {else}
                                        {if $option.is_best_price}
                                            {l s='The Best Price' mod='supercheckout'}
                                        {/if}
                                    {/if}
								    </span>
                                                                {/if}
                                                            </td>
                                                            <td class="">
                                                                <label for="shipping_method_{$id_address|intval}_{$option@index|intval}">
                                                                    {if $option.total_price_with_tax && (isset($option.is_free) && $option.is_free == 0) && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
                                                                        {if $use_taxes == 1}
                                                                            {if $priceDisplay == 1}
                                                                                {convertPrice price=$option.total_price_without_tax} {l s='(Tax excl.)' mod='supercheckout'}
                                                                            {else}
                                                                                {convertPrice price=$option.total_price_with_tax} {l s='(Tax incl.)' mod='supercheckout'}
                                                                            {/if}
                                                                        {else}
                                                                            {convertPrice price=$option.total_price_without_tax}
                                                                        {/if}
                                                                    {else}
                                                                        {l s='Free' mod='supercheckout'}
                                                                    {/if}
                                                                </label>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                                {foreachelse}
                                                <div class="supercheckout-checkout-content" style="display:block">
                                                    <div class="permanent-warning" style="display: block;">{l s='No Delivery Method Available' mod='supercheckout'}</div>
                                                </div>
                                            {/foreach}
                                        {/foreach}
                                        {if isset($HOOK_BEFORECARRIER)}
                                            {$HOOK_BEFORECARRIER}{*Variable contains html content, escape not required*}
                                        {/if}
                                        <div id="hook-extracarrier">
                                            {if isset($HOOK_EXTRACARRIER)}
                                                {$HOOK_EXTRACARRIER}{*Variable contains html content, escape not required*}
                                            {/if}
                                        </div>
                                    {/if}
                                    <br />
                                {/if}

                            </div>

                        </div>

                        <div style="{if $settings['payment_method']['enable'] eq 0}display:none;{/if}" class="supercheckout-blocks" data-column="{$settings['design']['payment_method'][$layout_name]['column']|intval}" data-row="{$settings['design']['payment_method'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['payment_method'][$layout_name]['column-inside']|intval}">



                            <div id="payment-method">
                                {if isset($payment_methods['not_required'])}
                                    <div class='supercheckout-checkout-content' style='display:block'>
                                        <div class='permanent-warning not-required-msg'>{$payment_methods['not_required']|escape:'htmlall':'UTF-8'}</div>
                                    </div>
                                {else}
                                    <div class="supercheckout-checkout-content" style="display:block">
                                        {if isset($payment_methods['warning']) && $payment_methods['warning'] neq ''}
                                            <div class="permanent-warning">{$payment_methods['warning']|escape:'htmlall':'UTF-8'}</div>
                                        {/if}
                                    </div>
                                    {if isset($payment_methods['methods']) && count($payment_methods['methods']) gt 0}
                                        <table class="radio">
                                            {foreach from=$payment_methods['methods'] item='payment_method'}
                                                {if $payment_method['name'] eq 'codr_klarnacheckout'}
                                                    <tr class="highlight">
                                                        <td style="display: inline;">
                                                            <a href="{$link->getPageLink('order-opc', null, null, 'klarna_supercheckout=true')|escape:'htmlall':'UTF-8'}" class="orangebuttonapply">Klarna Checkout</a>
                                                        </td>
                                                    </tr>
                                                {else}
                                                    <tr class="highlight">
                                                        <td>
                                                            <input type="radio" name="payment_method" value="{$payment_method['id_module']|intval}" id="{$payment_method['name']|escape:'htmlall':'UTF-8'}" {if $payment_method['id_module'] == $selected_payment_method}checked="checked"{/if} />
                                                            <input type="hidden" id="{$payment_method['id_module']|intval}_name" value="{$payment_method['payment_module_url']|escape:'htmlall':'UTF-8'}" />
                                                        </td>
                                                        <td>
                                                            <label id="payment_lbl_{$payment_method['id_module']|intval}" for="{$payment_method['name']|escape:'htmlall':'UTF-8'}">
                                                                {if $display_payment_style neq 0}
                                                                    {if $payment_method['payment_image_url'] neq ''}
                                                                        <img src='{$payment_method['payment_image_url']|escape:'htmlall':'UTF-8'}' alt='{$payment_method['display_name']|escape:'htmlall':'UTF-8'}' {if isset($payment_method['width']) && $payment_method['width'] !=""}width='{$payment_method['width']|escape:'htmlall':'UTF-8'}'{else} width="92"{/if} {if isset($payment_method['height']) && $payment_method['height'] !=""}height='{$payment_method['height']|escape:'htmlall':'UTF-8'}'{else} height="20"{/if}/>{if $display_payment_style neq 2}<br>{/if}
                                                                    {/if}
                                                                {/if}

                                                                {if $display_payment_style neq 2}
                                                                    <span id='payment_method_name_{$payment_method['id_module']|intval}'>
                                                                        {$payment_method['display_name']|escape:'htmlall':'UTF-8'}</span>
                                                                {/if}
                                                            </label>
                                                        </td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        </table>
                                        <div id="selected_payment_method_html"> </div>
                                        <div id="payment_method_html" style="display:none;">
                                            {foreach from=$payment_methods['methods'] item='payment_method'}
                                                <div id="payment_method_{$payment_method['id_module']|intval}">
                                                    {$payment_method['html']}{*Variable contains html content, escape not required*}
                                                </div>
                                            {/foreach}
                                        </div>
                                    {else}
                                        <div class="supercheckout-checkout-content" style="display:block">
                                            <div class="permanent-warning">{$payment_methods['warning']|escape:'htmlall':'UTF-8'}</div>
                                        </div>
                                    {/if}
                                {/if}
                            </div>

                        </div>
                        <div class="supercheckout-blocks confirmCheckoutBack" data-column="{$settings['design']['cart'][$layout_name]['column']|intval}" data-row="{$settings['design']['cart'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['cart'][$layout_name]['column-inside']|intval}" style="display:{if $settings['display_cart'] eq 1}block{else}none{/if};">
                            <div class="supercheckout-column-title">
                                <div class="supercheckout-column-title-number">3</div>
                                <div class="supercheckout-numbers">{l s='Confirm Your Order' mod='supercheckout'}</div>
                                <div class="loader" id="confirmLoader"></div>
                            </div>

                            <div id="confirmCheckout">
                                <div id="cart_update_warning" class="supercheckout-checkout-content"></div>
                                {if !isset($empty)}
                                <table class="supercheckout-summary">
                                    <thead>
                                    <tr>
                                        <th style="display:{if $logged}{if $settings['cart_options']['product_name']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_name']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-name">{l s='Description' mod='supercheckout'}</th>
                                        <th style="display:{if $logged}{if $settings['cart_options']['product_model']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_model']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-model">{l s='Model' mod='supercheckout'}</th>
                                        <th style="display:{if $logged}{if $settings['cart_options']['product_qty']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_qty']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-qty" style="text-align:center;">{l s='Qty' mod='supercheckout'}</th>
                                        <th style="display:{if $logged}{if $settings['cart_options']['product_price']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_price']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-total">{l s='Price' mod='supercheckout'}</th>
                                        <th style="display:{if $logged}{if $settings['cart_options']['product_total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_total']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-total">{l s='Total' mod='supercheckout'}</th>
                                        <th class="supercheckout-qty"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {assign var='image_display' value=0}
                                    {assign var='odd' value=0}
                                    {assign var='have_non_virtual_products' value=false}
                                    <!-- Product loop start -->
                                    {foreach $products as $product}
                                    {if $product.is_virtual == 0}
                                        {assign var='have_non_virtual_products' value=true}
                                    {/if}
                                    {assign var='productId' value=$product.id_product}
                                    {assign var='productAttributeId' value=$product.id_product_attribute}
                                    {assign var='quantityDisplayed' value=0}
                                    {assign var='odd' value=($odd+1)%2}
                                    {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
                                    <tr id="product_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_name']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_name']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-name">

                                            <div >
                                                {if $logged}
                                                    {$image_display = $settings['cart_options']['product_image']['logged']['display']}
                                                {else}
                                                    {$image_display = $settings['cart_options']['product_image']['guest']['display']}
                                                {/if}
                                                {if $image_display eq 1}
                                                    <img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />
                                                    <br>
                                                    <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>
                                                {else}
                                                    <a data-toggle="popover" data-title="{$product.name|escape:'html':'UTF-8'}" data-content="<img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />" data-placement="right" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>

                                                {/if}
                                                {if isset($product.attributes) && $product.attributes}
                                                    <br />
                                                    &nbsp;<small><a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.attributes|escape:'html':'UTF-8'}</a></small>
                                                {/if}
                                            </div>
                                        </td>
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_model']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_model']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-model">
                                            {if $product.reference}{$product.reference|escape:'html':'UTF-8'}{/if}
                                        </td>
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_qty']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_qty']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-qty supercheckout-product-qty-input" >
                                            {if isset($cannotModify) AND $cannotModify == 1}
                                                <span>
                                                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                                    {$product.customizationQuantityTotal|escape:'html':'UTF-8'}
                                                {else}
                                                    {$product.cart_quantity-$quantityDisplayed|escape:'html':'UTF-8'}
                                                {/if}
                                            </span>
                                            {else}
                                                {if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}
                                                    <span id="cart_quantity_custom_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$product.id_address_delivery|intval}" >{$product.customizationQuantityTotal|intval}</span>
                                                {/if}
                                                {if !isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0}
                                                    <input type="hidden" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{($customizedDatas.$productId.$productAttributeId|@count)|intval}{else}{$product.cart_quantity-$quantityDisplayed|intval}{/if}" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}_hidden" disabled/>
                                                    {if isset($settings['qty_update_option']) && $settings['qty_update_option'] eq 0 }

                                                        <div><input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed|intval}{/if}" disabled></div>


                                                    {else}

                                                        <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed|intval}{/if}" disabled><br>

                                                    {/if}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_price']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_price']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-unit-total">
                                        <span class="price product_individual_price_wrapper" id="product_price_{$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                                {if !empty($product.gift)}
                                                    <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                                                {else}                                                
                                                    {if !$priceDisplay}
                                                    <span class="price">{convertPrice price=$product.price_wt}</span>
                                                    {else}
                                                        <span class="price">{convertPrice price=$product.price}</span>
                                                {/if}
                                                    {if isset($product.is_discounted) && $product.is_discounted}
                                                    {if !$priceDisplay}
                                                        {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                                            {assign var='priceReduction' value=($product.price_wt - $product.price_without_quantity_discount)}
                                                            {assign var='symbol' value=$currency->sign}
                                                        {else}
                                                            {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price_wt)/$product.price_without_quantity_discount) * 100 * -1}
                                                            {assign var='symbol' value='%'}
                                                        {/if}
                                                    {else}
                                                        {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                                            {assign var='priceReduction' value=($product.price - $product.price_without_quantity_discount)}
                                                            {assign var='symbol' value=$currency->sign}
                                                        {else}
                                                            {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price)/$product.price_without_quantity_discount) * 100 * -1}
                                                            {assign var='symbol' value='%'}
                                                        {/if}
                                                    {/if}

                                                    {if $symbol == '%'}
                                                        {assign var='priceReduction' value=$priceReduction|round|string_format:"%d"}
                                                    {else}
                                                        {assign var='priceReduction' value=$priceReduction|string_format:"%.2f"}
                                                    {/if}
                                                    {if $priceReduction neq 0}  {* earlier it was {if $priceReduction gt 0}, changed because $priceReduction can also be equal to -10  *}
                                                        <br>
                                                        <span class="price-percent-reduction-description">
                                                                {$priceReduction|escape:'htmlall':'UTF-8'}{$symbol|escape:'htmlall':'UTF-8'}
                                                        </span>
                                                        <span class="supercheckout-old-price">{convertPrice price=$product.price_without_quantity_discount}</span>
                                                    {/if}
                                                {/if}
                                                {/if}
                                        </span>
                                        </td>
                                        <td id="total_product_price_{$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}" style="display:{if $logged}{if $settings['cart_options']['product_total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_total']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-total">
                                            {if !empty($product.gift)}
                                                <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                                            {else}
                                                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                                    {if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
                                                {else}
                                                    {if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td class="supercheckout-qty">
                                            {if !isset($customizedDatas.$productId.$productAttributeId)}
                                                <a href="javascript://" id="{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" onclick="deleteProductFromSummary(this.id);" class="removeProduct supercheckout-product-delete"><div  title="Delete"></div></a>
                                            {/if}
                                        </td>
                                    </tr>
                                    {if isset($customizedDatas.$productId.$productAttributeId)}
                                    {foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
                                        <tr id="product_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" class="product_customization_for_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$product.id_address_delivery|intval}{if $odd} odd{else} even{/if} customization alternate_item {if $product@last && $customization@last && !count($gift_products)}last_item{/if}">
                                            <td colspan="2">
                                                {foreach $customization.datas as $type => $custom_data}
                                                    {if $type == $CUSTOMIZE_FILE}
                                                        <div class="customizationUploaded">
                                                            <ul class="customizationUploaded">
                                                                {foreach $custom_data as $picture}
                                                                    <li><img src="{$pic_dir|escape:'htmlall':'UTF-8'}{$picture.value|escape:'htmlall':'UTF-8'}_small" alt="" class="customizationUploaded" /></li>
                                                                {/foreach}
                                                            </ul>
                                                        </div>
                                                    {elseif $type == $CUSTOMIZE_TEXTFIELD}
                                                        <ul class="typedText">
                                                            {foreach $custom_data as $textField}
                                                                <li>
                                                                    {if $textField.name}
                                                                        {$textField.name|escape:'htmlall':'UTF-8'}
                                                                    {else}
                                                                        {l s='Text #' mod='supercheckout'}{$textField@index+1|escape:'htmlall':'UTF-8'}
                                                                    {/if}
                                                                    : {$textField.value|escape:'htmlall':'UTF-8'}
                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    {/if}
                                                {/foreach}
                                            </td>
                                            <td class="supercheckout-qty">
                                                {if isset($cannotModify) AND $cannotModify == 1}
                                                    {if $logged}
                                                        <span>{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$id_address_delivery.$id_customization.quantity|escape:'htmlall':'UTF-8'}{else}{$product.cart_quantity-$quantityDisplayed|escape:'htmlall':'UTF-8'}{/if}</span>
                                                    {else}
                                                        {assign var='tempDelId' value=$product.id_address_delivery}
                                                        <span>{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$tempDelId.$id_customization.quantity|escape:'htmlall':'UTF-8'}{else}{$product.cart_quantity-$quantityDisplayed|escape:'htmlall':'UTF-8'}{/if}</span>
                                                    {/if}
                                                {else}
                                                    <input type="hidden" value="{if isset($customizedDatas.$productId.$productAttributeId)}{($customizedDatas.$productId.$productAttributeId|@count)|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}_hidden" />
                                                    {if isset($settings['qty_update_option']) && $settings['qty_update_option'] eq 0 }

                                                        {if $logged}
                                                            <div>  <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" value="{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$id_address_delivery.$id_customization.quantity|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" disabled></div>
                                                        {else}
                                                            {assign var='tempDelId' value=$product.id_address_delivery}
                                                            <div> <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" value="{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$tempDelId.$id_customization.quantity|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" disabled></div>
                                                        {/if}


                                                    {else}

                                                        {if $logged}
                                                            <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" value="{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$id_address_delivery.$id_customization.quantity|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" disabled><br>
                                                        {else}
                                                            {assign var='tempDelId' value=$product.id_address_delivery}
                                                            <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" value="{if isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId.$tempDelId.$id_customization.quantity|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" disabled><br>
                                                        {/if}

                                                    {/if}

                                                {/if}
                                            </td>
                                            <td class="cart_delete supercheckout-qty" colspan="3" style="text-align: right !important;">
                                                {if isset($cannotModify) AND $cannotModify == 1}
                                                {else}
                                                    <a href="javascript://" id="{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$id_customization|intval}_{$product.id_address_delivery|intval}" onclick="deleteProductFromSummary(this.id);" class="removeProduct supercheckout-product-delete"><div  title="Delete"></div></a>
                                                {/if}
                                            </td>
                                        </tr>
                                        {assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
                                    {/foreach}

                                    {* If it exists also some uncustomized products *}
                                    {if $product.quantity-$quantityDisplayed > 0}
                                    <tr id="product_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_name']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_name']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-name">

                                            <div >
                                                {if $logged}
                                                    {$image_display = $settings['cart_options']['product_image']['logged']['display']}
                                                {else}
                                                    {$image_display = $settings['cart_options']['product_image']['guest']['display']}
                                                {/if}
                                                {if $image_display eq 1}
                                                    <img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />
                                                    <br>
                                                    <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>
                                                {else}
                                                    <a data-toggle="popover" data-title="{$product.name|escape:'html':'UTF-8'}" data-content="<img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />" data-placement="right" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>

                                                {/if}
                                                {if isset($product.attributes) && $product.attributes}
                                                    <br />
                                                    &nbsp;<small><a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.attributes|escape:'html':'UTF-8'}</a></small>
                                                {/if}
                                            </div>
                                        </td>
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_model']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_model']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-model">
                                            {if $product.reference}{$product.reference|escape:'html':'UTF-8'}{/if}
                                        </td>
                                        <td style="display:{if $logged}{if $settings['cart_options']['product_qty']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_qty']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-qty supercheckout-product-qty-input" >
                                            {if isset($cannotModify) AND $cannotModify == 1}
                                            <span>
                                                        {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                                            {$product.customizationQuantityTotal|escape:'html':'UTF-8'}
                                                        {else}
                                                            {$product.cart_quantity-$quantityDisplayed|escape:'html':'UTF-8'}
                                                        {/if}
                                                    </span>
                                            {else}
                                            {if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}
                                                <span id="cart_quantity_custom_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{$product.id_address_delivery|intval}" >{$product.customizationQuantityTotal|intval}</span>
                                            {/if}
                                            {if !isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0}
                                            <input type="hidden" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{($customizedDatas.$productId.$productAttributeId|@count)|intval}{else}{($product.cart_quantity-$quantityDisplayed)|intval}{/if}" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}_hidden" disabled/>
                                            {if isset($settings['qty_update_option']) && $settings['qty_update_option'] eq 0 }

                                            <div><input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed|intval}{/if}" disabled></div>

                            </div></div>
                        {else}
                    <input size="2" class="quantitybox" autocomplete="off" type="text" name="quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed|intval}{/if}" disabled><br>


                        {/if}
                        {/if}
                        {/if}


                                            </td>
                                            <td style="display:{if $logged}{if $settings['cart_options']['product_price']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_price']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-unit-total">
                                                <span class="price product_individual_price_wrapper" id="product_price_{$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                                        {if !empty($product.gift)}
                        <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                                                        {else}                                                
                                                            {if !$priceDisplay}
                            <span class="price">{convertPrice price=$product.price_wt}</span>
                                                            {else}
                                                                <span class="price">{convertPrice price=$product.price}</span>
                        {/if}
                                                            {if isset($product.is_discounted) && $product.is_discounted}
                            {if !$priceDisplay}
                                {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                    {assign var='priceReduction' value=($product.price_wt - $product.price_without_quantity_discount)}
                                    {assign var='symbol' value=$currency->sign}
                                {else}
                                    {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price_wt)/$product.price_without_quantity_discount) * 100 * -1}
                                    {assign var='symbol' value='%'}
                                {/if}
                            {else}
                                {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                    {assign var='priceReduction' value=($product.price - $product.price_without_quantity_discount)}
                                    {assign var='symbol' value=$currency->sign}
                                {else}
                                    {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price)/$product.price_without_quantity_discount) * 100 * -1}
                                    {assign var='symbol' value='%'}
                                {/if}
                            {/if}

                            {if $symbol == '%'}
                                {assign var='priceReduction' value=$priceReduction|round|string_format:"%d"}
                            {else}
                                {assign var='priceReduction' value=$priceReduction|string_format:"%.2f"}
                            {/if}
                            {if $priceReduction neq 0}  {* earlier it was {if $priceReduction gt 0}, changed because $priceReduction can also be equal to -10  *}
                                <br>
                                <span class="price-percent-reduction-description">
                                                                        {$priceReduction|escape:'htmlall':'UTF-8'}{$symbol|escape:'htmlall':'UTF-8'}
                                                                </span>
                                <span class="supercheckout-old-price">{convertPrice price=$product.price_without_quantity_discount}</span>
                            {/if}
                        {/if}
                    {/if}
                        </span>
                        </td>
                        {$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}" style="display:{if $logged}{if $settings['cart_options']['product_total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_total']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-total">
                                                {if !empty($product.gift)}
                        <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                    {else}
                        {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                            {if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
                        {else}
                            {if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
                        {/if}
                    {/if}
                        </td>
                        {$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}" onclick="deleteProductFromSummary(this.id);" class="removeProduct supercheckout-product-delete"><div  title="Delete"></div></a></td>

                        {/if}
                        {/if}
                        {/foreach}
                        {foreach $gift_products as $product}
                            {if $product.is_virtual == 0}
                                {assign var='have_non_virtual_products' value=true}
                            {/if}
                            {assign var='productId' value=$product.id_product}
                            {assign var='productAttributeId' value=$product.id_product_attribute}
                            {assign var='quantityDisplayed' value=0}
                            {assign var='odd' value=($odd+1)%2}
                            {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
                            <tr id="product_{$product.id_product|intval}_{$product.id_product_attribute|intval}_{if $quantityDisplayed > 0}nocustom{else}0{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                <td style="display:{if $logged}{if $settings['cart_options']['product_name']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_name']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-name">

                                    <div >
                                        {if $logged}
                                            {$image_display = $settings['cart_options']['product_image']['logged']['display']}
                                        {else}
                                            {$image_display = $settings['cart_options']['product_image']['guest']['display']}
                                        {/if}
                                        {if $image_display eq 1}
                                            <img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />
                                            <br>
                                            <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>
                                        {else}
                                            <a data-toggle="popover" data-title="{$product.name|escape:'html':'UTF-8'}" data-content="<img width='{$settings['cart_image_size']['width']|escape:"html":"UTF-8"}' height='{$settings['cart_image_size']['height']|escape:"html":"UTF-8"}' src='{$link->getImageLink($product.link_rewrite, $product.id_image)|escape:"html":"UTF-8"}' alt='{$product.name|escape:"html":"UTF-8"}' />" data-placement="right" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>

                                        {/if}
                                        {if isset($product.attributes) && $product.attributes}
                                            <br />
                                            &nbsp;<small><a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.attributes|escape:'html':'UTF-8'}</a></small>
                                        {/if}
                                    </div>
                                </td>
                                <td style="display:{if $logged}{if $settings['cart_options']['product_model']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_model']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-model">
                                    {if $product.reference}{$product.reference|escape:'html':'UTF-8'}{/if}
                                </td>
                                <td style="display:{if $logged}{if $settings['cart_options']['product_qty']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_qty']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-qty supercheckout-product-qty-input" >
                                        
                                            <span>
                                                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                                    {$product.customizationQuantityTotal|escape:'html':'UTF-8'}
                                                {else}
                                                    {$product.cart_quantity-$quantityDisplayed|escape:'html':'UTF-8'}
                                                {/if}
                                            </span>

                                </td>
                                <td style="display:{if $logged}{if $settings['cart_options']['product_price']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_price']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-unit-total">
                                        <span class="price product_individual_price_wrapper" id="product_price_{$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
                                                {if !empty($product.gift)}
                                                    <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                                                {else}                                                
                                                    {if !$priceDisplay}
                                                    <span class="price">{convertPrice price=$product.price_wt}</span>
                                                    {else}
                                                        <span class="price">{convertPrice price=$product.price}</span>
                                                {/if}
                                                    {if isset($product.is_discounted) && $product.is_discounted}
                                                    {if !$priceDisplay}
                                                        {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                                            {assign var='priceReduction' value=($product.price_wt - $product.price_without_quantity_discount)}
                                                            {assign var='symbol' value=$currency->sign}
                                                        {else}
                                                            {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price_wt)/$product.price_without_quantity_discount) * 100 * -1}
                                                            {assign var='symbol' value='%'}
                                                        {/if}
                                                    {else}
                                                        {if isset($product.reduction_type) && $product.reduction_type == 'amount'}
                                                            {assign var='priceReduction' value=($product.price - $product.price_without_quantity_discount)}
                                                            {assign var='symbol' value=$currency->sign}
                                                        {else}
                                                            {assign var='priceReduction' value=(($product.price_without_quantity_discount - $product.price)/$product.price_without_quantity_discount) * 100 * -1}
                                                            {assign var='symbol' value='%'}
                                                        {/if}
                                                    {/if}

                                                    {if $symbol == '%'}
                                                        {assign var='priceReduction' value=$priceReduction|round|string_format:"%d"}
                                                    {else}
                                                        {assign var='priceReduction' value=$priceReduction|string_format:"%.2f"}
                                                    {/if}
                                                    {if $priceReduction neq 0}  {* earlier it was {if $priceReduction gt 0}, changed because $priceReduction can also be equal to -10  *}
                                                        <br>
                                                        <span class="price-percent-reduction-description">
                                                                {$priceReduction|escape:'htmlall':'UTF-8'}{$symbol|escape:'htmlall':'UTF-8'}
                                                        </span>
                                                        <span class="supercheckout-old-price">{convertPrice price=$product.price_without_quantity_discount}</span>
                                                    {/if}
                                                {/if}
                                                {/if}
                                        </span>
                                </td>
                                <td id="total_product_price_{$product.id_product|intval}_{$product.id_product_attribute|intval}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}" style="display:{if $logged}{if $settings['cart_options']['product_total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['cart_options']['product_total']['guest']['display'] eq 1}{else}none{/if}{/if};" class="supercheckout-total">
                                    {if !empty($product.gift)}
                                        <span class="gift-icon">{l s='Gift!' mod='supercheckout'}</span>
                                    {else}
                                        {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                            {if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
                                        {else}
                                            {if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
                                        {/if}
                                    {/if}
                                </td>
                                <td class="supercheckout-qty">

                                </td>
                            </tr>

                        {/foreach}
                            <!-- Product loop end -->
                        </tbody>                    
                    </table>
                    <table class="supercheckout-totals">
                        <tbody>
                            <!-- Order total start -->
                            <tr style="display:{if $logged}{if $settings['order_total_option']['product_sub_total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['order_total_option']['product_sub_total']['guest']['display'] eq 1}{else}none{/if}{/if};">
                            {if $use_taxes}
                            {if $priceDisplay}
                                <td class="title"><b>{if $display_tax_label}{l s='Total products' mod='supercheckout'} {l s='(Tax excl.)' mod='supercheckout'}{else}{l s='Total products' mod='supercheckout'}{/if}</b></td>
                                <td class="value"><span id="total_product" class="price">{displayPrice price=$total_products}</span> </td>
                            {else}
                                <td class="title"><b>{if $display_tax_label}{l s='Total products' mod='supercheckout'} {l s='(Tax incl.)' mod='supercheckout'}{else}{l s='Total products' mod='supercheckout'}{/if}</b></td>
                                <td class="value"><span id="total_product" class="price">{displayPrice price=$total_products_wt}</span></td>
                            {/if}
                        {else}
                            <td class="title"><b>{l s='Total products' mod='supercheckout'}</b></td>
                            <td class="value"><span id="total_product" class="price">{displayPrice price=$total_products}</span></td>
                        {/if}
                            </tr>
                            <tr{if $total_wrapping == 0} style="display: none;"{/if}>
                                <td class="title"><b>{if $use_taxes}{if $display_tax_label}{l s='Total gift wrapping cost' mod='supercheckout'} {l s='(Tax incl.)' mod='supercheckout'}{else}{l s='Total gift wrapping cost' mod='supercheckout'}{/if}{else}{l s='Total gift wrapping cost' mod='supercheckout'}{/if}</b></td>
                                <td class="value">
                                    <span id="total_wrapping" class="price">
                                        {if $use_taxes}
                            {if $priceDisplay}
                                {displayPrice price=$total_wrapping_tax_exc}
                            {else}
                                {displayPrice price=$total_wrapping}
                            {/if}
                        {else}
                            {displayPrice price=$total_wrapping_tax_exc}
                        {/if}
                                    </span>
                                </td>
                            </tr>
                            {assign var='shipping_setting_display' value=1}
                        {if $logged}{if $settings['order_total_option']['shipping_price']['logged']['display'] eq 1}{else}{$shipping_setting_display = 0}{/if}{else}{if $settings['order_total_option']['shipping_price']['guest']['display'] eq 1}{else}{$shipping_setting_display = 0}{/if}{/if}
                        {if $shipping_setting_display}
                            {if $total_shipping_tax_exc <= 0 && !isset($virtualCart)}
                                <tr id="cart_total_delivery" style="{if !isset($carrier->id) || is_null($carrier->id)}display:none;{/if}">
                                    <td class="title"><b>{l s='Shipping' mod='supercheckout'}</b></td>
                                    <td class="value"><span id="total_shipping" class="price">{l s='Free Shipping' mod='supercheckout'}</span> </td>
                                </tr>
                            {else}
                                {if $use_taxes && $total_shipping_tax_exc != $total_shipping}
                                    {if $priceDisplay}
                                        <tr id="cart_total_delivery" {if $total_shipping_tax_exc <= 0} style="display:none;"{/if}>
                                            <td class="title"><b>{if $display_tax_label}{l s='Total Shipping' mod='supercheckout'} {l s='(Tax excl.)' mod='supercheckout'}{else}{l s='Total Shipping' mod='supercheckout'}{/if}</b></td>
                                            <td class="value"><span id="total_shipping" class="price">{displayPrice price=$total_shipping_tax_exc}</span> </td>
                                        </tr>
                                    {else}
                                        <tr id="cart_total_delivery" {if $total_shipping <= 0} style="display:none;"{/if}>
                                            <td class="title"><b>{if $display_tax_label}{l s='Total Shipping' mod='supercheckout'} {l s='(Tax incl.)' mod='supercheckout'}{else}{l s='Total Shipping' mod='supercheckout'}{/if}</b></td>
                                            <td class="value"><span id="total_shipping" class="price">{displayPrice price=$total_shipping}</span> </td>
                                        </tr>
                                    {/if}
                                {else}
                                    <tr id="cart_total_delivery" {if $total_shipping_tax_exc <= 0} style="display:none;"{/if}>
                                        <td class="title"><b>{l s='Total Shipping' mod='supercheckout'}</b></td>
                                        <td class="value"><span id="total_shipping" class="price">{displayPrice price=$total_shipping_tax_exc}</span> </td>
                                    </tr>
                                {/if}
                            {/if}
                        {/if}
                        {if $use_taxes}
                            <tr>
                                <td class="title"><b>{l s='Total Tax' mod='supercheckout'}</b></td>
                                <td class="value"><span id="total_tax" class="price">{displayPrice price=$total_tax}</span></td>
                            </tr>
                        {/if}
                            <tr class="cart_total_voucher" {if $total_discounts == 0}style="display:none"{/if}>
                                <td class="title">
                                    <b>
                                        {if $display_tax_label}
                            {if $use_taxes && $priceDisplay == 0}
                                {l s='Total Vouchers' mod='supercheckout'} {l s='(Tax incl.)' mod='supercheckout'}
                            {else}
                                {l s='Total Vouchers' mod='supercheckout'} {l s='(Tax excl.)' mod='supercheckout'}
                            {/if}
                        {else}
                            {l s='Total Vouchers' mod='supercheckout'}
                        {/if}
                                    </b>
                                </td>
                                <td class="value">
                                    <span class="price price-percent-reduction small"  id="total_discount">
                                        {if $use_taxes && $priceDisplay == 0}
                            {assign var='total_discounts_negative' value=$total_discounts * -1}
                        {else}
                            {assign var='total_discounts_negative' value=$total_discounts_tax_exc * -1}
                        {/if}
                        {displayPrice price=$total_discounts_negative}
                                    </span>
                                </td>                                
                            </tr>

                            <!-- Order total end -->
                            {if $voucherAllowed}

                        {else}
                            <tr id="supercheckout_voucher_input_row" style="display:none;"></tr>
                        {/if}
                            <tr style="display:{if $logged}{if $settings['order_total_option']['total']['logged']['display'] eq 1}{else}none{/if}{else}{if $settings['order_total_option']['total']['guest']['display'] eq 1}{else}none{/if}{/if};">
                                {if $use_taxes}
                            <td class="title"><b>{l s='Total' mod='supercheckout'}</b></td>
                            <td class="value">
                                <input type="hidden" id="total_price_wfee" value="{$total_price}" >
                                <span id="total_price" class="price">{displayPrice price=$total_price}</span>
                            </td>
                        {else}
                            <td class="title"><b>{l s='Total' mod='supercheckout'} {l s='(Tax incl.)' mod='supercheckout'}</b></td>
                            <td class="value"><span id="total_price_without_tax" class="price">{displayPrice price=$total_price_without_tax}</span><input type="hidden" id="total_price_wfee" value="{$total_price_without_tax}" ></td>
                        {/if}
                            </tr>
                        </tbody>
                    </table>
                    {else}
                        <div class="supercheckout-checkout-content" style="display:block">
                            <div class="permanent-warning">{l s='Your shopping cart is empty.' mod='supercheckout'}</div>
                        </div>
                    {/if}
                    </div>
                    <!-- Added to show cart rules -->
                   
                    <div id="loyalty_text_holder">
                        {$HOOK_SHOPPING_CART}{*Variable contains html content, escape not required*}
                    </div>

                </div>
                <div id="payment_display_block"  class="supercheckout-blocks" data-column="{$settings['design']['confirm'][$layout_name]['column']|intval}" data-row="{$settings['design']['confirm'][$layout_name]['row']|intval}" data-column-inside="{$settings['design']['confirm'][$layout_name]['column-inside']|intval}" >
                    <ul style="{if $settings['payment_method']['enable'] eq 0}display:none;{/if}">
                        <li>
                            <!--<p class="supercheckout-numbers supercheckout-payment-info">Payment Method Review</p>-->
                            <div class="loader" id="paymentFormDisplayLoader"></div>
                        </li>
                    </ul>
                    <div class="supercheckout-checkout-content"> </div>
                    <div id="display_payment"></div>


                    <div id='order-shipping-extra'>
                        {if !$IS_VIRTUAL_CART}
                            {if $recyclablePackAllowed}
                                <div id="supercheckout_recyclepack_container" class='order-shipping-extra' style="display:padding-bottom: 0 !important;">
                                    <input type="checkbox" name="recyclable" class="supercheckout-delivery-extra" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} />
                                    {l s='I would like to receive my order in recycled packaging.' mod='supercheckout'}
                                </div>
                            {/if}

                            {if $giftAllowed}
                                <div id="supercheckout-gift_container" class='order-shipping-extra' style="display:padding-bottom: 0 !important;">
                                    <input type="checkbox" class="supercheckout-delivery-extra" name="gift" id="gift" value="1" {if $cartGiftChecked == 1}checked="checked"{/if} />
                                    {l s='I would like my order to be gift wrapped.' mod='supercheckout'}
                                    {if $gift_wrapping_price > 0}
                                        {l s='Additional cost of' mod='supercheckout'}
                                        {if $priceDisplay == 1}
                                            {convertPrice price=$total_wrapping_tax_exc_cost}
                                        {else}
                                            {convertPrice price=$total_wrapping_cost}
                                        {/if}
                                        {if $use_taxes}
                                            {if $priceDisplay == 1}
                                                {l s='(Tax excl.)' mod='supercheckout'}
                                            {else}
                                                {l s='(Tax incl.)' mod='supercheckout'}
                                            {/if}
                                        {/if}
                                    {/if}
                                </div>
                                <div id="supercheckout-gift-comments" style="display:none; margin-top: 0; margin-bottom: 15px;">
                                    <b>{l s='If you would like, you can add a note to the gift' mod='supercheckout'}:</b>
                                    <textarea id="gift_message" name="gift_comment" rows="8" >{$cart->gift_message|escape:'html':'UTF-8'}</textarea>
                                </div>
                            {/if}
                        {/if}
                        {if $conditions AND $cms_id AND $show_TOS}

                            <script>
                                $(document).ready(function() {
                                    $(".various").fancybox({
                                        scrolling: 'auto',
                                        width: '65%',
                                        height: '60%',
                                        fitToView: false,
                                        autoSize: false,
                                        'type': 'ajax',
                                        'ajax': {
                                            dataFilter: function(data) {
                                                return $(data).find('#center_column')[0];
                                            }
                                        }
                                    });
                                });
                            </script>

                        {/if}
                    </div>
                    <div id="placeorderButton">
                        <div id="buttonWithProgres" style="width:206px;">
                            <div  id="supercheckout_confirm_order" class="orangebutton" >
                                {l s='Place Order' mod='supercheckout'}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="supercheckout_submission" value="" />
                </div>

            </div>

            <div class="supercheckout-column-middle columnleftsort" id="columnleft-2"  style="width:{$settings['column_width'][$layout_name][2]*$multiplier|escape:'htmlall':'UTF-8'}%;margin-right:0px;">
                <div class="supercheckout-column-left columnleftsort" id="column-2-upper" style="width:100%;height:auto;">
                </div>
                <div class="supercheckout-column-left columnleftsort" id="column-1-inside" style="width:{$settings['column_width'][$layout_name]['inside'][1]*1|escape:'htmlall':'UTF-8'}%">
                </div>
                <div class="supercheckout-column-left columnleftsort" id="column-2-inside"  style="width:{$settings['column_width'][$layout_name]['inside'][2]*1|escape:'htmlall':'UTF-8'}%">

                </div>
                <div class="supercheckout-column-left columnleftsort" id="column-2-lower"  style="width:100%;height:auto;">

                </div>
            </div>
            <div class="supercheckout-column-right columnleftsort" id="columnleft-3" style="width:{$settings['column_width'][$layout_name][3]*$multiplier|escape:'htmlall':'UTF-8'}%">


            </div>

            </div>
            <input type="hidden" id="module_url" value="{$supercheckout_url|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" id="addon_url" value="{$addon_url|escape:'quotes':'UTF-8'}" />
            <input type="hidden" id="analytic_url" value="{$analytic_url|escape:'quotes':'UTF-8'}" />
        </fieldset>
    </form>
    <div id="velsof_payment_dialog">
        <div id="velsof_dialog_content">
            <div class="velsof_payment_dialog_overlay"></div>
            <div id="velsof_payment_container">
                <span class="velsof_dialog_close">X</span>
                <div class="velsof_title_section">{l s='Payment Information' mod='supercheckout'}</div>
                <div class="velsof_content_section"></div>
                <div class="velsof_action_section">
                    <a id="supercheckout_dialog_back" href="javascript:void(0)" class="velsof_dialog_action velsof_back_action">
                        <span>{if $ps_version eq 16}<i class="icon-chevron-left left"></i>{/if}{l s='Back' mod='supercheckout'}</span>
                    </a>
                    <a id="supercheckout_dialog_proceed" href="javascript:void(0)" class="velsof_dialog_action velsof_payment_action">
                        <span>{l s='Proceed' mod='supercheckout'}{if $ps_version eq 16}<i class="icon-chevron-right right"></i>{/if}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        var default_country = {$default_country|escape:'htmlall':'UTF-8'};
        var countries = {$countries|json_encode};
        var hash_error = document.location.hash;
        if(hash_error.indexOf('#stripe_error') > -1){
            $('#supercheckout-empty-page-content').append('<div class="permanent-warning">There was a problem with your payment</div>');
        }
        $(document).ready(function() {

            $('#{$iso_code}_content').show();
        });

    </script>
{else}
    <div class="supercheckout-empty-page-content" style="display:block">
        <div class="permanent-warning">{l s='Your shopping cart is empty.' mod='supercheckout'}</div>
    </div>
    <script>
        var cart_empty = {$empty|escape:'htmlall':'UTF-8'};
    </script>
{/if}
{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer tohttp://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2015 Knowband
*}
