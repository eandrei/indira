/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store. 
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2015 Knowband
 */

var calledFromShipping = 0;
$(document).ready(function(){
		
	if (typeof cart_empty != 'undefined' && cart_empty == true)
	{
		return;
	}
	checkout_option('input:radio[name=checkout_option]:checked'); //@Nitin Jain, 24-Aug-2015 - when customer come back from Paypal like methods, he wasn't getting selected checkout option
	shipping_address_value($('input[name="shipping_address_value"]:checked'));// @Nitin Jain - to show address form for logged in customer, if new address is selected.
	    if(iscartvirtual == true){
	    $('#use_for_invoice').prop('checked', false);
	    $('#checkoutBillingAddress').slideDown();
	    updateInvoiceAddress();
	    }
	//to hide Delivery address block when by default login checkout is selected
	if($('input:radio[name=checkout_option]:checked').val() == 0){
	    $('#checkoutShippingAddress').hide();
	    $('#checkoutBillingAddress').hide();
	}

	// to update shipping methods list based on default address when customer login on supercheckout page.
        buildAddressBlock($('#shipping-existing select[name="shipping_address_id"]').val(), 'delivery');
	
        if($('#use_for_invoice').is(':checked')){
            updateInvoiceAddress();
        }
        if($('input[name="shipping_address_value"]:checked').val() == 0){
            loadCarriers();            
        }       

//to hide social login block when by default guest checkout is selected
	if($('input:radio[name=checkout_option]:checked').val() == 1)    
	        $('#social_login_block').hide();

    // Create State list
    statelist(default_country, 0, 'select[name="shipping_address[id_state]"]');
    statelist(default_country, 0, 'select[name="payment_address[id_state]"]');
    
    //Display Selected Address detail
    displayAddressDetail();
    loadCarriers();      //@Nitin Jain, 17-Aug-2015 - added function call here to update shipping method list based upon selected state on page load.
    if($('#gift').is(':checked')){
        $('#supercheckout-gift-comments').show();
    }
    
    //Get Payment Method Form
    if($('input:radio[name="payment_method"]:checked').length){
        actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
    }else{
        $('input:radio[name="payment_method"]').first().attr('checked', 'checked');
        $('input:radio[name="payment_method"]').first().parent().addClass('checked');
        actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
    }
    
    
    $('input:radio[name="payment_method"]').live('click', function(){
        actionOnPaymentSelect(this);
    });
    
    //Display password field, based on checkout option
    $('input[name="checkout_option"]').on('click', function(){
       checkout_option(this);
    });
    
    //Create shipping state list based on selected shipping country
    $('select[name="shipping_address[id_country]"]').change(function(){
        var selected_country = $(this).find('option:selected').attr('value');
        var selected_state = 0;
        statelist(selected_country, selected_state, 'select[name="shipping_address[id_state]"]');
        checkDniandVatNumber('delivery');
        if($('input[name="shipping_address[postcode]"]').length && $('input[name="shipping_address[postcode]"]').val() != ''){
            checkZipCode(this, true);
        }else{
            loadCarriers();
        }
    });

    
    //Change shipping state list, if shipping address is same as payment address
    $('select[name="shipping_address[id_state]"]').live('change', function(){
        if($('#use_for_invoice').is(':checked')){
            var selected_state = $(this).find('option:selected').attr('value');
            $('select[name="payment_address[id_state]"] option').removeAttr('selected');
            $('select[name="payment_address[id_state]"] option').each(function(){
                if($(this).val() == selected_state){
                    $(this).attr('selected', 'selected');
                }
            });   
        }
        loadCarriers();
    });
    

    // Display or hide Shipping address container, based on same address or another address for shipping status
    $('#use_for_invoice').live('click', function(){
        if($(this).is(':checked')){
            $('#checkoutBillingAddress').slideUp();
            updateInvoiceAddress();
        }else{
            $('#checkoutBillingAddress').slideDown();
            checkDniandVatNumber('invoice');
        }
        updateInvoiceStatus(this);
    });
    
    //Update cart on shipping change
    $('.supercheckout_shipping_option').live('change', function(){
	calledFromShipping = 1;
        updateCarrierOnDeliveryChange();
//	checkToDisplayRelayList();		//Uncomment for Mondial Relay Shipping Method
    });
    
    //Update cart summary on changing delivery extra parameters
    $('.supercheckout-delivery-extra').live('change', function(){
        updateDeliveryExtraChange();
    });
    $('#gift_message').live('blur', function(){
        updateDeliveryExtraChange();
    });
    
    
    //Change shipping methods and payment methods
    $('#shipping-existing select[name="shipping_address_id"]').live('change', function(e){
        buildAddressBlock($(this).val(), 'delivery');
        if($('#use_for_invoice').is(':checked')){
            updateInvoiceAddress();
        }
        if($('input[name="shipping_address_value"]:checked').val() == 0){
            loadCarriers();            
        }        
    });

    $('input[name="shipping_address_value"]').live('change', function(){
        shipping_address_value(this);
    });
    
    //Create payment state list based on selected payment country
    $('select[name="payment_address[id_country]"]').live('change', function(){
        var selected_country = $(this).find('option:selected').attr('value');
        var selected_state = 0;
        statelist(selected_country, selected_state,  'select[name="payment_address[id_state]"]');
        _loadInvoiceAddress();
        checkDniandVatNumber('invoice');
        checkZipCode(this, false);
    });
    $('select[name="shipping_address[id_state]"]').live('change', function(){
        _loadInvoiceAddress();
    });


    //Show or hide payment address form 
    $('input[name="payment_address_value"]').live('click', function(){
        if($(this).val() == 0){
            $('#payment-new').slideUp();
        }else if($(this).val() == 1){
            $('#payment-new').slideDown();
            checkDniandVatNumber('invoice');
            checkZipCode(this, false);
        }
        _loadInvoiceAddress();
    });

    //Show or hide gift comment 
    $('#gift').live('click', function(){
        if($(this).is(':checked')){
            $('#supercheckout-gift-comments').slideDown();
        }else{
            $('#supercheckout-gift-comments').slideUp();
        }
    });
    
    $('#payment-existing select[name="payment_address_id"]').live('change', function(e){
        buildAddressBlock($(this).val(), 'invoice');
        _loadInvoiceAddress();
        checkDniandVatNumber('invoice');
    });
    
    //Check Dni Number
    $('input[name="shipping_address[dni]"]').on('blur',function(){
        isValidDni('delivery');
    });
    $('input[name="payment_address[dni]"]').on('blur',function(){
        isValidDni('invoice');
    });
    
    //Check Vat Number
    $('input[name="shipping_address[vat_number]"]').on('blur',function(){
        isValidVatNumber('delivery');
    });
    $('input[name="payment_address[vat_number]"]').on('blur',function(){
        isValidVatNumber('invoice');
    });    
    
    
    //Check Zip/Postal Code required
    $('input[name="shipping_address[postcode]"]').on('blur',function(){
        checkZipCode(this, true);
    });
    $('input[name="payment_address[postcode]"]').on('blur',function(){
        checkZipCode(this, false);
    });
    
    //Remove Field Errors on active input of addresses
    $('#checkoutBillingAddress input, #checkoutShippingAddress input').on('focus', function(){
        $(this).parent().find('span.errorsmall').remove();
    });
    
    //Remove Field Errors on active input of checkout options
    $('input[name="supercheckout_email"], input[name="supercheckout_password"], input[name="customer_personal[password]"]').on('focus', function(){
        $(this).parent().find('span.errorsmall').remove();
    });
    $('.supercheckout_personal_dob select').on('focus', function(){
        $('.supercheckout_personal_dob').find('span.errorsmall').remove();
    });
    $('.supercheckout_personal_id_gender input').on('focus', function(){
        $('.supercheckout_personal_id_gender').find('span.errorsmall').remove();
    });
    $('.supercheckout_offers_option input').on('click', function(){
        $('.supercheckout_personal_id_gender').parent().parent().parent().parent().find('span.errorsmall').remove();
    });
    $('textarea[name="payment_address[other]"], textarea[name="shipping_address[other]"]').on('focus', function(){
        $(this).parent().find('span.errorsmall').remove();
    });
    
    //Confirm Order
    $("#supercheckout_confirm_order").click(function() {
        var id = parseInt($("input.delivery_option_radio:checked").val());
        if (typeof paczkawruchuOpts != 'undefined') {
            if ( id == paczkawruchuOpts.carrier && paczkawruchuOpts.selectedKiosk === false  )
            {
                showRuchGeoPicker();
                return;
            }
        }
        placeOrder();       
    });
    
    //trigger confirm order after confirming payment in dialog
    $("#velsof_payment_dialog #supercheckout_dialog_proceed").click(function() {
        confirmOrder();
    });
    
    //To close payment dialog box
    $('#velsof_payment_dialog .velsof_dialog_close, #velsof_payment_dialog #supercheckout_dialog_back').on('click', function(){
        hide_progress();
	proceed_to_payment=false; //important to set it to false else payment methods will be triggered on selection even without click on place order.
        $('#velsof_payment_dialog').hide();
    });
    
    // Login Action
    $('#button-login').live('click',function(){
        supercheckoutlogin();
    });
    
   
    //Need to change the payment method label, if any has additional cost
    changePaymentMethodLabel();
    
    //quantitty change on blur
    $('.quantitybox').blur(function(){
	var name = $(this).attr("name");
	updateQty(name);
	
    });
    
   
    $('.colorbox').colorbox({
	width: 640,
	height: 480
});
});



function shipping_address_value(e){ // @Nitin Jain - to show address form for logged in customer, if new address is selected.
    
    var loadcarriers = false;
        if($(e).val() == 0){
            loadcarriers = true;
            $('#shipping-new').slideUp();            
        }else if($(e).val() == 1){
            $('#shipping-new').slideDown();
            checkDniandVatNumber('delivery');
            if($('input[name="shipping_address[postcode]"]').length && $('input[name="shipping_address[postcode]"]').val() != ''){
                checkZipCode(this, true);
            }else{
                loadcarriers = true;
            }
        }
        if(loadcarriers){
            loadCarriers();
        }
}
function checkout_option(e){
	
    if(iscartvirtual != true){
	    if($(e).val() == 0){
            $('#supercheckout-login-box').show();
            $('#supercheckout-new-customer-form').hide();
            $('#social_login_block').show();
            $('#new_customer_password').hide();
	    $('#checkoutShippingAddress').hide();
	    $('#checkoutBillingAddress').hide();
        }else if($(e).val() == 1){
	    
	    if(!$('#use_for_invoice').is(':checked')){
		$('#checkoutBillingAddress').show();
	    }
            $('#supercheckout-login-box').hide();
            $('#new_customer_password').hide();
            $('#social_login_block').hide();
            $('#supercheckout-new-customer-form').show();
	    $('#checkoutShippingAddress').show();
        }else{
	    if(!$('#use_for_invoice').is(':checked')){
		$('#checkoutBillingAddress').show();
	    }
            $('#supercheckout-login-box').hide();
            $('#new_customer_password').show();
            $('#social_login_block').show();
            $('#supercheckout-new-customer-form').show();
	    $('#checkoutShippingAddress').show();
        }
    }
    else // because in case of virtual cart we need to hide delivery address block
     if(iscartvirtual == true){
	if($(e).val() == 0){ 
            $('#supercheckout-login-box').show();
            $('#supercheckout-new-customer-form').hide();
            $('#social_login_block').show();
            $('#new_customer_password').hide();
	    $('#checkoutShippingAddress').hide();
	    $('#checkoutBillingAddress').hide();
        }else if($(e).val() == 1){
            $('#supercheckout-login-box').hide();
            $('#new_customer_password').hide();
            $('#social_login_block').hide();
            $('#supercheckout-new-customer-form').show();
	    $('#checkoutShippingAddress').hide();
	    $('#use_for_invoice').prop('checked', false);
	    $('#checkoutBillingAddress').slideDown();
        }else{
            $('#supercheckout-login-box').hide();
            $('#new_customer_password').show();
            $('#social_login_block').show();
            $('#supercheckout-new-customer-form').show();
	    $('#checkoutShippingAddress').hide();
	    $('#use_for_invoice').prop('checked', false);
	    $('#checkoutBillingAddress').slideDown();
        }
    }
    
}

function checkAction(e)
{
    if (typeof e == 'undefined' && window.event) { e = window.event; }
    if (e.keyCode == 13)
    {
        supercheckoutlogin();
    }
}

function supercheckoutlogin()
{
    $.ajax( {
        type: "POST",
        url: $('#module_url').val()+'&ajax=true',
        data: $('input:text[name="supercheckout_email"], #supercheckout-login-box input'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-login').parent().find('img').show();
            $('#checkoutLogin .supercheckout-checkout-content .permanent-warning').remove();
            $('.errorsmall').remove();
        },
        complete: function() {

        },
        success: function( json ) {
            if(json['success'] != undefined){
                location.href = json['success'];
            }else if(json['error']['general'] != undefined){
                $('#button-login').parent().find('img').hide();
               $('#checkoutLogin .supercheckout-checkout-content').html('<div class="permanent-warning">'+json['error']['general']+'</div>');
            }else{
                $('#button-login').parent().find('img').hide();
                if(json['error']['email'] != undefined){
                   $('#checkoutLogin input:text[name="supercheckout_email"]').parent().append('<span class="errorsmall">'+json['error']['email']+'</span>');
		   if (inline_validation == 1)
			$('#checkoutLogin input:text[name="supercheckout_email"]').addClass('error-form').removeClass('ok-form');
                }
                if(json['error']['password'] != undefined){
                   $('#supercheckout-login-box input:password[name="supercheckout_password"]').parent().append('<span class="errorsmall">'+json['error']['password']+'</span>');
		   if (inline_validation == 1)
			$('#supercheckout-login-box input:password[name="supercheckout_password"]').addClass('error-form').removeClass('ok-form');
                }   
            }            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('#checkoutLogin .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
        }
    } );    
}

function checkStateVisibility(selected_country, element){
    var state_html = ''; //<option value="0">Select State</option>
    var has_states = false;
    var show_state = false;
    for (var id_country in countries){
        if(id_country == selected_country){
            if(countries[id_country]['contains_states'] == 1){
                has_states = true;                   
            }
        }        
    }
    if(element.indexOf("shipping")>=0 && show_shipping_state == 1){
        show_state = true;
    }
    else if(element.indexOf("payment")>=0 && show_payment_state == 1){
        show_state = true;
    }
    
    if(has_states && show_state){
        return true;
    }else{
        return false;
    }
}

function statelist(selected_country, selected_state, element){
    var state_html = ''; //<option value="0">Select State</option>
    var has_states = false;
    var show_state = false;
    for (var id_country in countries){
        if(id_country == selected_country){
            if(countries[id_country]['contains_states'] == 1){
                has_states = true;
                for (var i in countries[id_country]['states']){
                    if(countries[id_country]['states'][i]['id_state'] == selected_state){
                        state_html += '<option value="'+countries[id_country]['states'][i]['id_state']+'" selected="selected" >'+countries[id_country]['states'][i]['name']+'</option>';
                    }else{
                        state_html += '<option value="'+countries[id_country]['states'][i]['id_state']+'">'+countries[id_country]['states'][i]['name']+'</option>';
                    }

                }    
            }
        }
        
    }
    if(element.indexOf("shipping")>=0 && show_shipping_state == 1){
        show_state = true;
    }
    else if(element.indexOf("payment")>=0 && show_payment_state == 1){
        show_state = true;
    }
    
    if(has_states && show_state){
        $(element).html(state_html);
        $(element).parent().parent().show();
    }else{
        $(element).parent().parent().hide();
    }
    
}

function updateInvoiceAddress(){
    $('select[name="payment_address_id"] option').removeAttr('selected');
    $('select[name="payment_address_id"] option').each(function(){
        if($(this).val() == $('select[name="shipping_address_id"]').find('option:selected').attr('value')){
            $(this).attr('selected', 'selected');
        }
    });
    buildAddressBlock($('select[name="payment_address_id"] option:selected').val(), 'invoice');
    $('input[name="payment_address_value"]').removeAttr('checked');
        $('input[name="payment_address_value"]').parent().removeClass('checked');
        $('input[name="payment_address_value"]').each(function(){
        if($(this).val() == $('input[name="shipping_address_value"]:checked').val()){
            $(this).attr('checked', 'checked');
            $(this).parent().addClass('checked');
        }
    });
    if($('input[name="payment_address_value"]:checked').val() == 0){
        $('#payment-new').slideUp();
    }

    $('select[name="payment_address[id_country]"] option').removeAttr('selected');
    $('select[name="payment_address[id_country]"] option').each(function(){
        if($(this).val() == $('select[name="shipping_address[id_country]"]').find('option:selected').attr('value')){
            $(this).attr('selected', 'selected');
        }
    });

    var selected_country = $('select[name="shipping_address[id_country]"]').find('option:selected').attr('value');
    var selected_state = 0;
    statelist(selected_country, selected_state, 'select[name="payment_address[id_state]"]');

    $('select[name="payment_address[id_state]"] option').removeAttr('selected');
    $('select[name="payment_address[id_state]"] option').each(function(){
        if($(this).val() == $('select[name="shipping_address[id_state]"]').find('option:selected').attr('value')){
            $(this).attr('selected', 'selected');
        }
    });    
}

function displayAddressDetail(){
    if (typeof formatedAddressFieldsValuesList !== 'undefined' && formatedAddressFieldsValuesList != null){
        buildAddressBlock($('select[name="payment_address_id"] option:selected').val(),'delivery');
        buildAddressBlock($('select[name="shipping_address_id"] option:selected').val(),'invoice');
    }
}

function buildAddressBlock(id_address, type){
    if((typeof formatedAddressFieldsValuesList != 'undefined') && formatedAddressFieldsValuesList != null){
        var html = '';
        var reg = /[\s,]+/;
        var field = '';
        var keys = '';
        for (var i in formatedAddressFieldsValuesList[id_address]['ordered_fields']){
            field = formatedAddressFieldsValuesList[id_address]['ordered_fields'][i];
            if(reg.test(field)){
                keys = field.split(reg);
                var values = '';
                for (var j in keys){
                    values += formatedAddressFieldsValuesList[id_address]['formated_fields_values'][keys[j]]+' ';                
                }
                html += '<span>'+values+'</span>';
            }else{
                html += '<span>'+formatedAddressFieldsValuesList[id_address]['formated_fields_values'][field]+'</span>';
            }  
        }
        $('#'+type+'_address_detail').html(html);        
    }
}


function checkDniandVatNumber(type){
    
    var id_country = $('select[name="shipping_address[id_country]"] option:selected').val();
    if(type == 'invoice'){
        id_country = $('select[name="payment_address[id_country]"] option:selected').val();
    }
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=checkDniandVat'
            +'&id_country='+id_country
            +'&token=' + static_token,
        beforeSend: function() {
            hideGeneralError();
        },
        complete: function() {},
        success: function(jsonData){
            if(type == 'delivery'){
                if(jsonData['is_need_dni']){
                    $('input[name="shipping_address[dni]"]').parent().parent().show();
                }else{
                    $('input[name="shipping_address[dni]"]').attr('value', '');
                    $('input[name="shipping_address[dni]"]').parent().parent().hide();
                }
                if(jsonData['is_need_vat']){
                    $('input[name="shipping_address[vat_number]"]').parent().parent().show();
                }else{
                    $('input[name="shipping_address[vat_number]"]').attr('value', '');
                    $('input[name="shipping_address[vat_number]"]').parent().parent().hide();
                }
                if(jsonData['is_need_states'] && show_shipping_state == 1){
                    $('select[name="shipping_address[id_state]"]').parent().parent().show();
                }else{
                    $('select[name="shipping_address[id_state]"]').removeAttr('selected');
                    $('select[name="shipping_address[id_state]"]').parent().parent().hide();
                }
                if(jsonData['is_need_zip_code'] != 0 && show_shipping_postcode==1){
                    $('input[name="shipping_address[postcode]"]').parent().parent().show();
                }else{
                    $('input[name="shipping_address[postcode]"]').attr('value', '');
                    $('input[name="shipping_address[postcode]"]').parent().parent().hide();
                }
            }
            if(type == 'invoice'){
                if(jsonData['is_need_dni']){
                    $('input[name="payment_address[dni]"]').parent().parent().show();
                }else{
                    $('input[name="payment_address[dni]"]').attr('value', '');
                    $('input[name="payment_address[dni]"]').parent().parent().hide();
                }
                if(jsonData['is_need_vat']){
                    $('input[name="payment_address[vat_number]"]').parent().parent().show();
                }else{
                    $('input[name="payment_address[vat_number]"]').attr('value', '');
                    $('input[name="payment_address[vat_number]"]').parent().parent().hide();
                }
                if(jsonData['is_need_states'] && show_payment_state == 1){
                    $('select[name="payment_address[id_state]"]').parent().parent().show();
                }else{
                    $('select[name="payment_address[id_state]"]').removeAttr('selected');
                    $('select[name="payment_address[id_state]"]').parent().parent().hide();
                }
                if(jsonData['is_need_zip_code'] != 0 && show_payment_postcode==1){
                    $('input[name="payment_address[postcode]"]').parent().parent().show();
                }else{
                    $('input[name="payment_address[postcode]"]').attr('value', '');
                    $('input[name="payment_address[postcode]"]').parent().parent().hide();
                }
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('#checkoutShippingAddress .supercheckout-checkout-content .permanent-warning').html(errors);
        }
    });    
}

function isValidDni(type){    
    var id_country = $('select[name="shipping_address[id_country]"] option:selected').val();
    var dni = $('input[name="shipping_address[dni]"]').val();
    if(type == 'invoice'){
        id_country = $('select[name="payment_address[id_country]"] option:selected').val();
        dni = $('input[name="payment_address[dni]"]').val();
    }
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=isValidDni'
            +'&id_country='+id_country
            +'&dni='+dni
            +'&token=' + static_token,
        beforeSend: function() {
            hideGeneralError();
	    if (inline_validation == 1)
	    {
		    if(type == 'delivery'){
		    $('input[name="shipping_address[dni]"]').removeClass('ok-form error-form');
                }
                if(type == 'invoice'){
		    $('input[name="payment_address[dni]"]').removeClass('ok-form error-form');
                }
	    }
	    
        },
        complete: function() {},
        success: function(jsonData){
            if(jsonData['error'] != undefined){
                if(type == 'delivery'){
                    $('input[name="shipping_address[dni]"]').parent().append('<span class="errorsmall">'+jsonData['error']+'</span>');
		    if (inline_validation == 1)
			$('input[name="shipping_address[dni]"]').addClass('error-form');
                }
                if(type == 'invoice'){
                    $('input[name="payment_address[dni]"]').parent().append('<span class="errorsmall">'+jsonData['error']+'</span>');
		    if (inline_validation == 1)
			$('input[name="payment_address[dni]"]').addClass('error-form');
                }
            }else
	    {
		if (inline_validation == 1)
		{
			if (type == 'delivery') {
				$('input[name="shipping_address[dni]"]').addClass('ok-form');
			}
			if (type == 'invoice') {
				$('input[name="payment_address[dni]"]').addClass('ok-form');
			}
		}
		    
	    }
            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors)
        }
    });    
}

function isValidVatNumber(type){
    var id_country = $('select[name="shipping_address[id_country]"] option:selected').val();
    var vat_number = $('input[name="shipping_address[vat_number]"]').val();
    if(type == 'invoice'){
        id_country = $('select[name="payment_address[id_country]"] option:selected').val();
        vat_number = $('input[name="payment_address[vat_number]"]').val();
    }
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=isValidVatNumber'
            +'&id_country='+id_country
            +'&vat_number='+vat_number
            +'&token=' + static_token,
        beforeSend: function() {
            hideGeneralError();
	    if (inline_validation == 1)
	    {
		    if(type == 'delivery'){
		    $('input[name="shipping_address[vat_number]"]').removeClass('ok-form error-form');
                }
                if(type == 'invoice'){
		    $('input[name="payment_address[vat_number]"]').removeClass('ok-form error-form');
                }
	    }
	    
        },
        complete: function() {},
        success: function(jsonData){
            if(jsonData['error'] != undefined){
                var errors = jsonData['error'].join('<br>');
                if(type == 'delivery'){
                    $('input[name="shipping_address[vat_number]"]').parent().append('<span class="errorsmall">'+errors+'</span>');
		    if (inline_validation == 1)
			$('input[name="shipping_address[vat_number]"]').addClass('error-form');
                }
                if(type == 'invoice'){
                    $('input[name="payment_address[vat_number]"]').parent().append('<span class="errorsmall">'+errors+'</span>');
		    if (inline_validation == 1)
			$('input[name="payment_address[vat_number]"]').addClass('error-form');
                }
            } else {
				if (inline_validation == 1)
				{
					if (type == 'delivery')
					{
						if ($('input[name="shipping_address[vat_number]"]').siblings('.supercheckout-required').css('display') == "none" && $('input[name="shipping_address[vat_number]"]').val() == '')
						{
							$('input[name="shipping_address[vat_number]"]').removeClass('ok-form error-form');
						}
						else
						{
							$('input[name="shipping_address[vat_number]"]').addClass('ok-form');
						}

					}
					if (type == 'invoice')
					{
						if ($('input[name="payment_address[vat_number]"]').siblings('.supercheckout-required').css('display') == "none" && $('input[name="payment_address[vat_number]"]').val() == '')
						{
							$('input[name="payment_address[vat_number]"]').removeClass('ok-form error-form');
						}
						else
						{
							$('input[name="payment_address[vat_number]"]').addClass('ok-form');
						}

					}
				}

			}
            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors)
        }
    });
}


function updateInvoiceStatus(element){
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=setSameInvoice'
            +'&use_for_invoice='+(($(element).is(':checked'))?'1':'0')
            +'&token=' + static_token,
        beforeSend: function() {
            $('.input-different-shipping').parent().find('.errorsmall').remove();
        },
        complete: function() {},
        success: function(jsonData){},
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('.input-different-shipping').parent().append('<div class="errorsmall">'+errors+'</div>');
        }
    });    
}

function _loadInvoiceAddress(){
    var id_country = 0;
    var id_address_invoice = '';
    if($('input[name="payment_address_value"]').length){
        if($('input[name="payment_address_value"]:checked').val() == 1){
            id_country = $('select[name="payment_address[id_country]"] option:selected').val();
        }else if($('input[name="payment_address_value"]:checked').val() == 0){
            id_address_invoice = $('select[name="payment_address_id"] option:selected').val();
        }
    }else{
        id_country = $('select[name="payment_address[id_country]"] option:selected').val();
    }
    var id_state = $('select[name="payment_address[id_state]"]').val();
    var postcode = $('input[name="payment_address[postcode]"]').val();
    var city = $('input[name="payment_address[city]"]').val();
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=loadInvoiceAddress'
            +'&id_country='+id_country
            +'&id_state='+id_state
            +'&postcode='+postcode
            +'&city='+city
            +'&id_address_invoice='+id_address_invoice
            +'&token=' + static_token,
        beforeSend: function() {
            hideGeneralError();
        },
        complete: function() {},
        success: function(jsonData){},
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors);
        }
    });    
}

function getCounrtryAndIdDelivery(){
    var id_country = 0;
    var id_address_delivery = '';
    if($('input[name="shipping_address_value"]').length){
        if($('input[name="shipping_address_value"]:checked').val() == 1){
            id_country = $('select[name="shipping_address[id_country]"] option:selected').val();
        }else if($('input[name="shipping_address_value"]:checked').val() == 0){
            id_address_delivery = $('select[name="shipping_address_id"] option:selected').val();
        }
    }else{
        id_country = $('select[name="shipping_address[id_country]"] option:selected').val();
    }
    
    var arr = [];
    arr.push(id_country);
    arr.push(id_address_delivery);
    return arr;
}

var shipping_error_found_on_load = false;
function loadCarriers(){
    var requestParam = getCounrtryAndIdDelivery();
    var id_country = requestParam[0];
    var id_state = 0;
    if(checkStateVisibility(id_country, 'select[name="shipping_address[id_state]"]')) {
        id_state = $('select[name="shipping_address[id_state]"]').val();
    }
    var postcode = $('input[name="shipping_address[postcode]"]').val();
    var city = $('input[name="shipping_address[city]"]').val();
    var id_address_delivery = requestParam[1];
    shipping_error_found_on_load = false;
    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true'
                +'&id_country='+id_country
                +'&id_state='+id_state
                +'&postcode='+postcode
                +'&city='+city
                +'&id_address_delivery='+id_address_delivery
                +'&method=loadCarriers&token=' + static_token,
            beforeSend: function() {
                $('#shippingMethodLoader').show();
                $('#shipping-method .supercheckout-checkout-content').find('.permanent-warning').html('');
            },
            complete: function() {
                //$('#shippingMethodLoader').hide();
            },
            success: function(jsonData)
            {
                carriers_count = jsonData['carriers_count'];
                is_cart_virtual = jsonData['is_cart_virtual'];   
		$('#hook-extracarrier').html(jsonData['HOOK_EXTRACARRIER']);
                if(jsonData['hasError']){
                    $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+jsonData['errors'][0]+'</div>');
                    shipping_error_found_on_load = true;
                }else{
                    shipping_error_found_on_load = false;
                }
                if (calledFromShipping == 0)
			$('#shipping-method').html(jsonData['carrier_block']);

		calledFromShipping = 0;

                updateShippingExtra(jsonData);                    
                set_column_inside_height();
                //updateCartSummary(jsonData[0].summary);
                updateCarriers();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
            }
    });    
}


function updateCarriers(){
    var delivery_option = ($('.supercheckout_shipping_option').length)? '&'+$('.supercheckout_shipping_option:checked').attr('name')+'='+$('.supercheckout_shipping_option:checked').attr('value') : '';
    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true'
                +delivery_option
                +'&method=updateCarrier&token=' + static_token,
            beforeSend: function() {
                if(!shipping_error_found_on_load && !is_cart_virtual){
                    $('#shipping-method .supercheckout-checkout-content').find('.permanent-warning').remove();
                }
                $('#shippingMethodLoader').show();
            },
            complete: function() {
                $('#shippingMethodLoader').hide();
            },
            success: function(jsonData)
            {
                if(jsonData['hasError']){
                    if(jsonData['errors'][0] != undefined && jsonData['errors'][0] != ''){
                        $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+jsonData['errors'][0]+'</div>');
                    }
                }
                loadPayments();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
            }
    });    
}

function loadPayments(){
    var requestParam = getCounrtryAndIdDelivery();
    var selected_payment_method_id=$('input:radio[name="payment_method"]:checked').val(); // getting value of selected payment methods
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&id_country='+requestParam[0]
            +'&id_address_delivery='+requestParam[1]
		+'&selected_payment_method_id='+selected_payment_method_id
            +'&method=loadPayment&token=' + static_token,
        beforeSend: function() {
            $('#payment-method .supercheckout-checkout-content').find('.permanent-warning').html('');            
            $('#paymentMethodLoader').show();
        },
        complete: function() {
            $('#paymentMethodLoader').hide();
        },
        success: function(jsonData)
        {
            $('#payment-method').html(jsonData['payment_method']);
            
            if(jsonData['payment_method_list']['methods'] != undefined && jsonData['payment_method_list']['methods'].length){
                $('#payment_method_default_description').html('');
                var description_html = '';
                for (var i in jsonData['payment_method_list']['methods']){
                    description_html += '<div id="paymentmodule_'+jsonData['payment_method_list']['methods'][i]['id_module']+'_'+jsonData['payment_method_list']['methods'][i]['name']+'">';
                    description_html += jsonData['payment_method_list']['methods'][i]['html'];
                    description_html += '</div>';
                }
                $('#payment_method_default_description').html(description_html);
            }
            
            if($('input:radio[name="payment_method"]').length && !$('input:radio[name="payment_method"]:checked').length){
                $('#payment_display_block .supercheckout-payment-info').hide();
                $('#display_payment').html('');
            }
			if (typeof changePaymentMethodFee == 'function') {
				changePaymentMethodFee();
            }
            changePaymentMethodLabel();
            set_column_inside_height();
            loadCart();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('#payment-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
        }
    });    
}

function updateCarrierOnDeliveryChange(){
    var delivery_option = ($('.supercheckout_shipping_option').length)? '&'+$('.supercheckout_shipping_option:checked').attr('name')+'='+$('.supercheckout_shipping_option:checked').attr('value') : '';

    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true'
                +delivery_option
                +'&method=updateCarrier&token=' + static_token,
            beforeSend: function() {
                $('#shipping-method .supercheckout-checkout-content').find('.permanent-warning').html('');
                $('#shippingMethodLoader').show();
            },
            complete: function() {
                $('#shippingMethodLoader').hide();
            },
            success: function(jsonData)
            {
                if(jsonData['hasError']){
                    $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+jsonData['errors'][0]+'</div>');
                }
                loadCarriers();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
            }
    });    
}

function updateDeliveryExtraChange(){
    var messagePattern = /[<>{}]/i;
    var gift_message = '';
    var extrasError = false;
    if($('#gift').length && $('#gift').is(':checked')){
        gift_message = $('#gift_message').val();
        if(messagePattern.test(gift_message)){
            extrasError = true;
            $('#gift_message').parent().append('<span class="errorsmall">'+commentInvalid+'</span>');
        }
    }
    
    if(!extrasError){
        var recycle = ($('#recyclable').length && $('#recyclable').is(':checked'))?1:0;
        var gift = ($('#gift').length && $('#gift').is(':checked'))?1:0;
        gift_message = $('#gift_message').val();
        $.ajax({
                type: 'POST',
                headers: { "cache-control": "no-cache" },
                url: $('#module_url').val() + '&rand=' + new Date().getTime(),
                async: true,
                cache: false,
                dataType : "json",
                data: 'ajax=true'
                    +'&recycle='+recycle
                    +'&gift='+gift
                    +'&gift_message='+gift_message
                    +'&method=updateDeliveryExtra&token=' + static_token,
                beforeSend: function() {
                    $('#supercheckout-empty-page-content').find('.permanent-warning').html('');
                },
                success: function(jsonData)
                {
                    if(jsonData['hasError']){
                        var arr = jsonData['errors'];
                        $('#supercheckout-empty-page-content').html('<div class="permanent-warning">'+arr.join('<br>')+'</div>');
                        $("html, body").animate({ scrollTop: 0 }, "fast");
                    }
                    loadCart();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                    displayGeneralError(errors);
                }
        });        
    }
}

function loadCart(){
    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true'
                +'&method=loadCart&token=' + static_token,
            beforeSend: function() {
                $('#cart_update_warning .permanent-warning').remove();
                $('#confirmLoader').show();
            },
            success: function(jsonData)
            {
                $('#confirmLoader').hide();
                updateCartSummary(jsonData);
                
                //Update Payment Information
                if($('input:radio[name="payment_method"]:checked').length){
                    actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
                }else{
                    $('input:radio[name="payment_method"]').first().attr('checked', 'checked');
                    $('input:radio[name="payment_method"]').first().parent().addClass('checked');
                    actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#cart_update_warning').html('<div class="permanent-warning">'+errors+'</div>');
            }
    });    
}

//Update Cart Quantity
function updateQty(element){
    $('#cart_update_warning .permanent-warning').remove();
    var exp = new RegExp("^[0-9]+$");
    var hidden = $('#confirmCheckout input[name=' + element + '_hidden]').val();
    var input = $('#confirmCheckout  input[name=' + element + ']').val();
    if (exp.test(input) == true){
        var QtyToUpDate = parseInt(input) - parseInt(hidden);
        var qty = QtyToUpDate;
        var updateQuantity = true;
        var id = element.replace('quantity_', '');
        if(parseInt(QtyToUpDate) < 0){
            var val = $('input[name=' + element + ']').val();
            var newVal = val;
            if(typeof(qty) == 'undefined' || !qty){
                    qty = 1;
                    newVal = val - 1;
            }else if (qty < 0)
                    qty = -qty;
            
            if (newVal > 0 || $('#product_' + id + '_gift').length){
                updateQuantity = true; //downQuantity(id.replace('quantity_', ''), QtyToUp);
            }else{
                updateQuantity = false;
            }
        }
        //console.log(input+'====='+hidden);
        if(updateQuantity){
            if (qty != 0){
                var customizationId = 0;
                var productId = 0;
                var productAttributeId = 0;
                var id_address_delivery = 0;
                var ids = 0;
                ids = id.split('_');
                productId = parseInt(ids[0]);
                var errors = '';

                if (typeof(ids[1]) !== 'undefined')
                        productAttributeId = parseInt(ids[1]);
                if (typeof(ids[2]) !== 'undefined' && ids[2] !== 'nocustom')
                        customizationId = parseInt(ids[2]);
                if (typeof(ids[3]) !== 'undefined')
                        id_address_delivery = parseInt(ids[3]);

                $.ajax( {
                   type: "POST",
                   headers: { "cache-control": "no-cache" },
                   url: baseUri + '?rand=' + new Date().getTime(),
                   data: 'controller=cart'
                                    + '&ajax=true'
                                    + '&add=true'
                                    + '&getproductprice=true'
                                    + '&summary=true'
                                    + '&id_product='+productId
                                    + '&ipa='+productAttributeId
                                    + '&id_address_delivery='+id_address_delivery
                                    + ((parseInt(QtyToUpDate) < 0) ? '&op=down': '')
                                    //+ '&op=down'
                                    + ((customizationId !== 0) ? '&id_customization='+customizationId : '')
                                    + '&qty='+qty
                                    + '&token='+static_token
                                    + '&allow_refresh=1',
                   async: true,
                   cache: false,
                   dataType: 'json',
                   beforeSend: function() {
                       $('#cart_update_warning .permanent-warning').remove();
                       $('#confirmLoader').show();
                   },
                   complete: function() {
                       $('#confirmLoader').hide();
                   },
                   success: function( jsonData ) {                   
                       if (jsonData.hasError){
                            for(var error in jsonData.errors){
                                if(error !== 'indexOf')
                                    errors += jsonData.errors[error] + "<br>";
                            }
                            $('#cart_update_warning').html('<div class="permanent-warning">'+errors+'</div>');//alert(errors);
                            $('input[name='+ element +']').val($('input[name='+ element +'_hidden]').val());
                        }else{
                            $.gritter.add({
                                title: notification,
                                text: product_qty_update_success,
                                class_name:'gritter-success',
                                sticky: false,
                                time: '3000'
                            });
                                if (jsonData.refresh)
                                        location.reload();
                                updateCartSummary(jsonData.summary);

                                if (customizationId !== 0)
                                        updateCustomizedDatas(jsonData.customizedDatas);
                                loadCarriers();					
                        }
                   },
                   error: function(XMLHttpRequest, textStatus, errorThrown) {
                        errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                        $('#cart_update_warning').html('<div class="permanent-warning">'+errors+'</div>');
                   }
                } );    
            }else if(hidden == input){
                $('#cart_update_warning').html('<div class="permanent-warning">'+updateSameQty+'</div>');
            }else{
                $('#cart_update_warning').html('<div class="permanent-warning">'+scInvalidQty+'</div>');
            }
        }else{
            deleteProductFromSummary(id);
        }
        
    }else{
        $('#cart_update_warning').html('<div class="permanent-warning">'+scInvalidQty+'</div>');
    }
}


function updateCartSummary(json){
    ajaxCart.refresh();
    var i;
    var nbrProducts = 0;
    var product_list = new Array();

    if (typeof json == 'undefined')
            return;		

    for (i=0;i<json.products.length;i++)
            product_list[json.products[i].id_product + '_' + json.products[i].id_product_attribute + '_' + json.products[i].id_address_delivery] = json.products[i];

    if (!$('.multishipping-cart:visible').length)
    {
            for (i=0;i<json.gift_products.length;i++)
                    if (typeof(product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery]) !== 'undefined')
                            product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery].quantity -= json.gift_products[i].cart_quantity;
    }
    else
            for (i=0;i<json.gift_products.length;i++)
                    if (typeof(product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery]) == 'undefined')
                            product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery] = json.gift_products[i];

    for (i in product_list)
    {
            // if reduction, we need to show it in the cart by showing the initial price above the current one
            var reduction = product_list[i].reduction_applies;
            var initial_price_text = '';
            initial_price = '';
            if (typeof(product_list[i].price_without_quantity_discount) !== 'undefined'){
                initial_price = formatCurrency(product_list[i].price_without_quantity_discount, currencyFormat, currencySign, currencyBlank);
                var tmp1 = ps_round(product_list[i].price_without_quantity_discount) - product_list[i].price_wt;
                var tmp2 = ps_round(product_list[i].price_without_quantity_discount);
                priceReductionPercent = -ps_round(ps_round(tmp1/tmp2) * 100);
            }

            var current_price = '';
            if (priceDisplayMethod !== 0)
                    current_price = formatCurrency(product_list[i].price, currencyFormat, currencySign, currencyBlank);
            else
                    current_price = formatCurrency(product_list[i].price_wt, currencyFormat, currencySign, currencyBlank);

            if (priceReductionPercent && typeof(initial_price) !== 'undefined')
                    if (initial_price !== '' && product_list[i].price_without_quantity_discount > product_list[i].price)
                            initial_price_text = '<br><span class="price-percent-reduction-description">'+priceReductionPercent+'%</span><span class="supercheckout-old-price">' + initial_price + '</span>';

            var key_for_blockcart = product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery;
            var key_for_blockcart_nocustom = product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + ((product_list[i].id_customization && product_list[i].quantity_without_customization != product_list[i].quantity)? 'nocustom' : '0') + '_' + product_list[i].id_address_delivery;

            if (priceDisplayMethod !== 0)
            {
                    $('#product_price_' + key_for_blockcart).html('<span class="price">' + current_price + '</span>' + initial_price_text);
                    if (typeof(product_list[i].customizationQuantityTotal) !== 'undefined' && product_list[i].customizationQuantityTotal > 0)			
                            $('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_list[i].total_customization, currencyFormat, currencySign, currencyBlank));
                    else
                            $('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_list[i].total, currencyFormat, currencySign, currencyBlank));
                    if (product_list[i].quantity_without_customization != product_list[i].quantity)
                            $('#total_product_price_' + key_for_blockcart_nocustom).html(formatCurrency(product_list[i].total, currencyFormat, currencySign, currencyBlank));				
            }
            else
            {	
                    $('#product_price_' + key_for_blockcart).html('<span class="price">' + current_price + '</span>' + initial_price_text);
                    if (typeof(product_list[i].customizationQuantityTotal) !== 'undefined' && product_list[i].customizationQuantityTotal > 0)
                            $('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_list[i].total_customization_wt, currencyFormat, currencySign, currencyBlank));
                    else
                            $('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_list[i].total_wt, currencyFormat, currencySign, currencyBlank));
                    if (product_list[i].quantity_without_customization != product_list[i].quantity)
                            $('#total_product_price_' + key_for_blockcart_nocustom).html(formatCurrency(product_list[i].total_wt, currencyFormat, currencySign, currencyBlank));									
            }
            $('input[name=quantity_' + key_for_blockcart_nocustom + ']').val(product_list[i].id_customization? product_list[i].quantity_without_customization : product_list[i].cart_quantity);
            $('input[name=quantity_' + key_for_blockcart_nocustom + '_hidden]').val(product_list[i].id_customization? product_list[i].quantity_without_customization : product_list[i].cart_quantity);
            if (typeof(product_list[i].customizationQuantityTotal) !== 'undefined' && product_list[i].customizationQuantityTotal > 0)
                    $('#cart_quantity_custom_' + key_for_blockcart).html(product_list[i].customizationQuantityTotal);
            nbrProducts += parseInt(product_list[i].quantity);			
    }

    // Update discounts
    var discount_count = 0;
    for(var e in json.discounts)
    {
            discount_count++;
            break;
    }

    $('.cart_discount').each(function(){$(this).remove();});
    $('.cart_total_voucher').remove();
    
    if (discount_count){

        //Update Discounts
        var total_discount_html = '';
        var show_total_discount = (json.total_discounts <= 0)?'style="display:none"':'';
        var total_discount_value = 0;
        var total_voucher_label = totalVoucherText;
        if(priceDisplayMethod !== 0){            
            total_discount_value = '-' + formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank);
            total_voucher_label += ' '+tax_excl_text;
        }else{
            total_discount_value = '-' + formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank);
            total_voucher_label += ' '+tax_incl_text;
        }
         
        total_discount_html += '<tr class="cart_total_voucher" '+show_total_discount+'>'
                                + '<td class="title"><b>'+total_voucher_label+'</b></td>'
                                + '<td class="value"><span class="price" id="total_discount">'+total_discount_value+'</span></td>'
                                + '</tr>';
                        
        var individual_discount_html = '';

        for (var i in json.discounts){
            var discount_value = 0;
            if(priceDisplayMethod == 0){
                discount_value = formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank);
            }else{
                discount_value = formatCurrency(json.discounts[i].value_tax_exc*-1, currencyFormat, currencySign, currencyBlank);
            }
            individual_discount_html += '<tr id="cart_discount_'+json.discounts[i].id_discount+'" class="cart_discount" >'
                            +'<td class="title"><b>'+json.discounts[i].name+'<a href="javascript:void(0)" onclick="removeDiscount('+json.discounts[i].id_discount+')"><div title="Redeem" class="removeProduct"></div></a></td></b></td>'
                            +'<td class="value"><span class="price">'+discount_value+'</span> </td>'                               
                            +'</tr>';
        }
        
        var discount_html= total_discount_html + individual_discount_html;

        $('#supercheckout_voucher_input_row').before(discount_html);
        
        if (priceDisplayMethod !== 0)
                $('#total_discount').html('-' + formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank));
        else
                $('#total_discount').html('-' + formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));            
    }

    // Block cart
    $('#cart_block_shipping_cost').show();
    $('#cart_block_shipping_cost').next().show();
    if (json.total_shipping > 0)
    {
            if (priceDisplayMethod !== 0)
            {
                    $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
                    $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping_tax_exc, currencyFormat, currencySign, currencyBlank));
                    $('#cart_block_total').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
            }
            else
            {
                    $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
                    $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
                    $('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
            }
    }
    else
    {
            if (json.carrier.id == null)
            {
                    $('#cart_block_shipping_cost').hide();
                    $('#cart_block_shipping_cost').next().hide();
            }
    }

    $('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
    $('.ajax_cart_quantity').html(nbrProducts);

    // Cart summary
    //$('#summary_products_quantity').html(nbrProducts + ' ' + (nbrProducts > 1 ? txtProducts : txtProduct));
    if (priceDisplayMethod !== 0)
            $('#total_product').html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));
    else
            $('#total_product').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
    $('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
    $('#total_price_wfee').val(json.total_price);
    $('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
    $('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
    
    if (scp_use_taxes)
    {
        scp_order_total_price = json.total_price;
    }else{
        scp_order_total_price = json.total_price_without_tax;
    }
    
    $('#cart_total_delivery').show();
    if (json.total_shipping > 0)
    {
            if (priceDisplayMethod !== 0)
                    $('#total_shipping').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
            else
                    $('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
    }
    else
    {
        if(!shipping_error_found_on_load){
            if (json.carrier.id != null)
                        $('#total_shipping').html(freeShippingTranslation);
                else
                        $('#cart_total_delivery').hide();    
        }else{
            $('#cart_total_delivery').hide(); 
        }
    }

    if (json.free_ship > 0 && !json.is_virtual_cart)
    {
            $('.cart_free_shipping').fadeIn();
		if (typeof (json.free_ship) == "boolean")
			$('#free_shipping').html(json.free_ship);
		else
			$('#free_shipping').html(formatCurrency(json.free_ship, currencyFormat, currencySign, currencyBlank));

    }
    else
            $('.cart_free_shipping').hide();

    if (json.total_wrapping > 0)
    {
            $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
            $('#total_wrapping').parent().parent().show();
    }
    else
    {
            $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
            $('#total_wrapping').parent().parent().hide();
    }
    if (json.HOOK_SHOPPING_CART)
	$('#loyalty_text_holder').html(json.HOOK_SHOPPING_CART);
}

function deleteProductFromSummary(id){
    var customizationId = 0;
    var productId = 0;
    var productAttributeId = 0;
    var id_address_delivery = 0;
    var ids = 0;
    ids = id.split('_');
    var errors = '';
    productId = parseInt(ids[0]);
    if (typeof(ids[1]) !== 'undefined')
            productAttributeId = parseInt(ids[1]);
    if (typeof(ids[2]) !== 'undefined' && ids[2] !== 'nocustom')
            customizationId = parseInt(ids[2]);
    if (typeof(ids[3]) !== 'undefined')
            id_address_delivery = parseInt(ids[3]);
    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: baseUri + '?rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=cart'
                    + '&ajax=true&delete=true&summary=true'
                    + '&id_product='+productId
                    + '&ipa='+productAttributeId
                    + '&id_address_delivery='+id_address_delivery 
                    + ((customizationId !== 0) ? '&id_customization=' + customizationId : '')
                    + '&token=' + static_token
                    + '&allow_refresh=1',
            beforeSend: function(){
                $('#cart_update_warning .permanent-warning').remove();
            },
            success: function(jsonData)
            {
                    if (jsonData.hasError)
                    {                            
                            for(var error in jsonData.errors)
                            //IE6 bug fix
                            if (error !== 'indexOf')
                                    errors += jsonData.errors[error] + "\n";
                            $('#cart_update_warning').html('<div class="permanent-warning">'+errors+'</div>');                            
                    }
                    else
                    {
                        $.gritter.add({
                            title: notification,
                            text: product_remove_success,
                            class_name:'gritter-success',
                            sticky: false,
                            time: '3000'
                        });
                            if (jsonData.refresh)
                                    location.reload();
                            if (parseInt(jsonData.summary.products.length) == 0){
                                    location.reload();			//Code to reload the page in case cart is empty instead of removing the blocks.
                            }else{
                                    $('#product_' + id).fadeOut('slow', function() {
                                            $(this).remove();
                                            //cleanSelectAddressDelivery();
                                            if (!customizationId){
                                                    //refreshOddRow();
                                            }
                                    });
                                    var exist = false;
                                    for (i=0;i<jsonData.summary.products.length;i++)
                                    {
                                            if (jsonData.summary.products[i].id_product == productId
                                                    && jsonData.summary.products[i].id_product_attribute == productAttributeId
                                                    && jsonData.summary.products[i].id_address_delivery == id_address_delivery
                                                    && (parseInt(jsonData.summary.products[i].customization_quantity) > 0))
                                                            exist = true;
                                    }
                                    // if all customization removed => delete product line
                                    if (!exist && customizationId)
                                            $('#product_' + productId + '_' + productAttributeId + '_0_' + id_address_delivery).fadeOut('slow', function() {
                                                    $(this).remove();
                                                    var line = $('#product_' + productId + '_' + productAttributeId + '_nocustom_' + id_address_delivery);
                                                    if (line.length > 0)
                                                    {
                                                            line.find('td.supercheckout-qty input[name^=quantity_], td.supercheckout-qty input[name^=quantity_], td.supercheckout-qty a.supercheckout-product-delete').each(function(){
                                                                    if (typeof($(this).attr('name')) != 'undefined')
                                                                            $(this).attr('name', $(this).attr('name').replace(/nocustom/, '0'));
                                                                    if (typeof($(this).attr('id')) != 'undefined')
                                                                            $(this).attr('id', $(this).attr('id').replace(/nocustom/, '0'));
                                                            });
                                                            line.find('span[id^=total_product_price_]').each(function(){
                                                                    $(this).attr('id', $(this).attr('id').replace(/_nocustom/, ''));
                                                            });
                                                            line.attr('id', line.attr('id').replace(/nocustom/, '0'));
                                                    }
                                                    //refreshOddRow();
                                            });
                            }
                            updateCartSummary(jsonData.summary);

                            updateCustomizedDatas(jsonData.customizedDatas);
                            loadCarriers();
                    }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#cart_update_warning').html('<div class="permanent-warning">'+errors+'</div>');
            }
    });
}


function updateCustomizedDatas(json){
	for(var i in json)
		for(var j in json[i])
			for(var k in json[i][j])
				for(var l in json[i][j][k])
				{
					var quantity = json[i][j][k][l]['quantity'];
					$('input[name=quantity_' + i + '_' + j + '_' + l + '_' + k + '_hidden]').val(quantity);
					$('input[name=quantity_' + i + '_' + j + '_' + l + '_' + k + ']').val(quantity);
				}
}


function callCoupon(){
    $.ajax( {
        type: "POST",
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime()+'&ajax=true',
        async: true,
        cache: false,
        data: $('#voucher-form input'),
        dataType: 'json',
        beforeSend: function() {
            $('#cart_update_warning .permanent-warning').remove();
            $('#confirmLoader').show();
        },
        complete: function() {
            $('#confirmLoader').hide();
        },
        success: function( json ) {
            if(json['success'] != undefined){
                $.gritter.add({
                        title: notification,
                        text: json['success'],
                //	image: '',
                        class_name:'gritter-success',
                        sticky: false,
                        time: '3000'
                });
                $('#discount_name').attr('value','');
                loadCarriers();
            }else if(json['error'] != undefined){
               $('#cart_update_warning').html('<div class="permanent-warning">'+json['error']+'</div>');
            }
			$('#highlighted_cart_rules').html(json['cart_rule']);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var error = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('#cart_update_warning').html('<div class="permanent-warning">'+error+'</div>');
        }
    } );    
}

function removeDiscount(voucher_id){
    $.ajax( {
        type: "POST",
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        data: '&ajax=true&deleteDiscount='+voucher_id,
        dataType: 'json',
        beforeSend: function() {
            $('#cart_update_warning .permanent-warning').remove();
            $('#confirmLoader').show();
        },
        complete: function() {
            $('#confirmLoader').hide();
        },
        success: function( json ) {
            if(json['success'] != undefined){
                $.gritter.add({
                        title: notification,
                        text: json['success'],
                //	image: '',
                        class_name:'gritter-success',
                        sticky: false,
                        time: '3000'
                });
                $('#discount_name').attr('value','');
                loadCarriers();
            }else if(json['error'] != undefined){
               $('#cart_update_warning').html('<div class="permanent-warning">'+json['error']+'</div>');
            }
			$('#highlighted_cart_rules').html(json['cart_rule']);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var error = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            $('#cart_update_warning').html('<div class="permanent-warning">'+error+'</div>');
        }
    } );
}

function selectShippingOption(selected_value){
    //$('.supercheckout_shipping_option').removeAttr('checked');

    $('.supercheckout_shipping_option').each(function(){
        if(selected_value == $(this).val()){
            $(this).attr('checked','checked');
        }
    });
    
}

function updateShippingExtra(jsonData){
                    
    var recyclable = (($('#supercheckout_recyclepack_container input[type=checkbox]').is(':checked'))? 1 : 0);
    var checked_gift = (($('#supercheckout-gift_container input[type=checkbox]').is(':checked'))? 1 : 0);
    var comment_gift = $('#gift_message').val();
    var checked_termCondition = (($('#supercheckout-agree input[type=checkbox]').is(':checked'))? 1 : 0);
    var isAlreadyDisplayed = ($('#supercheckout-gift-comments').is(':visible'))? 1 : 0;

    $('#order-shipping-extra').html(jsonData['order-shipping-extra']);

    if(recyclable == 1){
        $('#supercheckout_recyclepack_container input[type=checkbox]').attr('checked', 'checked');
    }
    if(checked_gift == 1){
        $('#supercheckout-gift_container input[type=checkbox]').attr('checked', 'checked');
    }
    $('#supercheckout-gift-comments textarea').html(comment_gift);
    if(isAlreadyDisplayed == 1){
        $('#supercheckout-gift-comments').show();
    }

    if(checked_termCondition == 1){
        $('#supercheckout-agree input[type=checkbox]').attr('checked', 'checked');
    }    
}


function set_column_inside_height(){
    var col_1_inside = $('#column-1-inside').height();
    var col_2_inside = $('#column-2-inside').height();

    if(col_1_inside > col_2_inside){
        $('#column-2-inside').css('height', col_1_inside+'px');
    }else if(col_1_inside < col_2_inside){
        $('#column-1-inside').css('height', col_2_inside+'px');
    }
}

function checkZipCode(e, isCarrierLoad){
    var checkZip = false;
    var address_type = $(e).attr('name').split('[');
    address_type = address_type[0];
    var container = 'checkoutShippingAddress';
    if(address_type == 'payment_address'){
        container = 'checkoutBillingAddress';
    }
    
    if($('#'+container+' input[name="'+address_type+'[postcode]"]').length){
        checkZip = true;
    }
    if(checkZip){
        var checkData = {
            'ajax'      : true,
            'method'    : 'checkZipCode',
            'id_country': ($('select[name="'+address_type+'[id_country]"]').length > 0) ? $('select[name="'+address_type+'[id_country]"] option:selected').val() : '',
            'postcode'  : ($('input[name="'+address_type+'[postcode]"]').length > 0) ? $('input[name="'+address_type+'[postcode]"]').val() : '',
        } 
        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: checkData,
            beforeSend: function() {
                $('#'+container+' input[name="'+address_type+'[postcode]"]').parent().find('span.errorsmall').remove();
		if (inline_validation == 1)
		{
			$('#'+container+' input[name="'+address_type+'[postcode]"]').removeClass('error-form');
		$('#'+container+' input[name="'+address_type+'[postcode]"]').removeClass('ok-form');
		}
		
            },
            complete: function() {

            },
            success: function(jsonData)
            {
                if(jsonData['error'] != undefined){
                    $('#'+container+' input[name="'+address_type+'[postcode]"]').parent().append('<span class="errorsmall">'+jsonData['error']+'</span>');
		    if (inline_validation == 1)
			$('#'+container+' input[name="'+address_type+'[postcode]"]').addClass('error-form');
                }
		else
		{
			if (inline_validation == 1)
				$('#'+container+' input[name="'+address_type+'[postcode]"]').addClass('ok-form');
		}
                if(isCarrierLoad){
                    loadCarriers();
                }               
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                $('#'+container+' .supercheckout-checkout-content').html('<div class="permanent-warning">'+errors+'</div>');
            }
        });    
    }        
}


function display_progress(value){
    $('#supercheckout_confirm_order').attr('disabled', true);
    $('#submission_progress_overlay').css('height', $('#supercheckout-fieldset').height());
    $('#supercheckout_order_progress_status_text').html(value + '%');
    $('#submission_progress_overlay').show();
    $('#supercheckout_order_progress_bar').show();    
}

function hide_progress(){
    $('#supercheckout_confirm_order').removeAttr('disabled');
    $('#submission_progress_overlay').hide();
    $('#supercheckout_order_progress_bar').hide();
    $('#supercheckout_order_progress_status_text').html('0%');    
}

var proceed_to_payment = false;

function actionOnPaymentSelect(element){
    var payment_module_name = $('input:radio[name="payment_method"]:checked').attr('id');
    var id = $('input:radio[name="payment_method"]:checked').val();    
    
    if (payment_module_name != 'epay')
	{
		$('#supercheckout_dialog_proceed').show();
		$('#velsof_payment_container .velsof_content_section').css('height', '200px');
	}
    
    var redirectHtml = '';
    //to fix stripjs method you need to edit its stripjs.php file too.. check pmsheet wiki for more information
    //to fix braintreejs method you need to edit its braintreejs.php file too.. check pmsheet wiki for more information
    if(payment_module_name != 'stripejs' && payment_module_name != 'firstdata' && payment_module_name != 'conektatarjeta' && payment_module_name != 'stripepro' && payment_module_name != 'braintreejs_backup' && payment_module_name != 'twocheckout' && payment_module_name != 'brinkscheckout' && payment_module_name != 'ewayrapid' && payment_module_name != 'npaypalpro' && payment_module_name != 'authorizeaim'){
			$('#selected_payment_method_html').html(''); // to hide form if customer select any other payment method later
		}
    if(payment_module_name == 'librapay' || payment_module_name == 'cashondelivery' || payment_module_name == 'secureframe' || payment_module_name == 'compropago' || payment_module_name == 'checkout' || payment_module_name == 'westernunion' || payment_module_name == 'billmateinvoice' || payment_module_name == 'billmatepartpayment' || payment_module_name == 'mercadoc' ||payment_module_name == 'boletosantanderpro' || payment_module_name == 'payu' || payment_module_name == 'payulatam' || payment_module_name == 'zipcheck' || payment_module_name == 'megareembolso' || payment_module_name == 'payinstore' || payment_module_name == 'codfee' || payment_module_name == 'finanziamento' || payment_module_name == 'megashoppay' ){
        redirectHtml += '<input type="hidden" id="payment_redirect" value="'+$('#'+id+'_name').val()+'" />';
        $('#velsof_payment_dialog .velsof_content_section').html(redirectHtml);
    }else if(payment_module_name == 'bankwire' || payment_module_name == 'boleto' || payment_module_name == 'invoicepayment' || payment_module_name == 'pagofacil' || payment_module_name == 'postepay' || payment_module_name == 'paysera' || payment_module_name == 'offlinecreditcard' || payment_module_name == 'trustly' || payment_module_name == 'cheque' || payment_module_name == 'deluxecodfees'){
        getPaymentForm(element);
    }else if(payment_module_name == 'stripejs' || payment_module_name == 'stripepro' || payment_module_name == 'firstdata' || payment_module_name == 'conektatarjeta' || payment_module_name == 'braintreejs_backup' || payment_module_name == 'twocheckout' || payment_module_name == 'brinkscheckout' || payment_module_name == 'ewayrapid' || payment_module_name == 'npaypalpro' ||  payment_module_name == 'mobilpay_cc' || payment_module_name == 'authorizeaim' || payment_module_name == 'khipupayment' || payment_module_name == 'paynl_paymentmethods' || payment_module_name == 'mollie' || payment_module_name == 'quickpay' || payment_module_name == 'moneybookers' || payment_module_name == 'faspay' || payment_module_name == 'paynlpaymentmethods' || payment_module_name == 'add_gopay_new' || payment_module_name == 'paypal' || payment_module_name == 'parspalpayment' || payment_module_name == 'pronesis_bancasella' || payment_module_name == 'paypalmx' || payment_module_name == 'cmcic_tbweb' || payment_module_name == 'sisoweb' || payment_module_name == 'citrus' || payment_module_name == 'banc_sabadell' || payment_module_name == 'ccavenue' || payment_module_name == 'ogone' || payment_module_name == 'epay' || payment_module_name == 'creditcardpaypal' || payment_module_name == 'paypalusa' || payment_module_name == 'sisowideal' || payment_module_name == 'paypalwithfee' || payment_module_name == 'sisowmc'){
        getPaymentForm1(element);
    }else{
        getPaymentForm1(element);
    }
    if (typeof changePaymentMethodFeeCart == 'function') {
        changePaymentMethodFeeCart();
    }
}

function getPaymentForm(element){
    var url = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
    var payment_module_name = $('input:radio[name="payment_method"]:checked').attr('id');
    var setErrorResponse = '<input type="hidden" id="payment_fetch_error" value="0" />';
    $.ajax({
        type: 'GET',
        headers: { "cache-control": "no-cache" },
        url: url,
        async: true,
        //cache: false,
        dataType : "html",
        beforeSend: function() {
            $('#paymentMethodLoader').show();
            $('#velsof_payment_dialog .velsof_content_section').html(setErrorResponse);
        },
        complete: function() {
            $('#paymentMethodLoader').hide();
        },
        success: function(dataHtml)
        {
            try{
                var payment_info_html = $(dataHtml).find('#'+payment_content_id);
                $(payment_info_html).find('#order_step').remove();
                $('h1', payment_info_html).remove();
                $('#cart_navigation', payment_info_html).remove();                
                $('.cart_navigation', payment_info_html).remove();      // Added for Prestashop 1.5 for removing the buttons in the payment method html         
                $('#amount', payment_info_html).removeClass('price');
                $(payment_info_html).find('form:first').find('div:first, div.box').find('p:last-child').remove();
                $(payment_info_html).find('form:first').find('div:first, div.box').find('#currency_payement').parent().hide();
                $('#velsof_payment_dialog .velsof_content_section').html(payment_info_html.html());
            }catch(err){
                $('#velsof_payment_dialog .velsof_content_section').html(setErrorResponse);
            }
            if(proceed_to_payment){
                moveToPayment();
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors);
        }
    });
}

function getPaymentForm1(element){
    $('#display_payment').html('');
    $('#display_payment').parent().find('.supercheckout-checkout-content .permanent-warning').remove();
    $('#payment_display_block .supercheckout-payment-info').hide();
    var url = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
    var payment_module_name = $('input:radio[name="payment_method"]:checked').attr('id');
    var payment_module_id = $('input:radio[name="payment_method"]:checked').val();
    var setErrorResponse = '<input type="hidden" id="payment_fetch_error" value="0" />';
    $.ajax({
        type: 'GET',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&rand=' + new Date().getTime(),
        async: true,
        //cache: false,
        dataType : "json",
        data: 'ajax=true'
            +'&method=getPaymentInformation'
            +'&id_module='+payment_module_id
            +'&payment_module_name='+payment_module_name
            +'&token=' + static_token,
        beforeSend: function() {
            $('#paymentMethodLoader').show();
            $('#velsof_payment_dialog .velsof_content_section').html(setErrorResponse);
        },
        complete: function() {
            $('#paymentMethodLoader').hide();
        },
        success: function(json)
        {
            var html = '';
            if(json['error'] != undefined){
                html = '<input type="hidden" id="payment_fetch_error" value="0" />';
                $('#velsof_payment_dialog .velsof_content_section').html(html);
            }else{
                $('#velsof_payment_dialog .velsof_content_section').html(json['html']);                
            }
            $('#velsof_payment_dialog .velsof_action_section').show();
            if(payment_module_name == 'authorizeaim'){
		 $('#selected_payment_method_html').html($('.velsof_content_section').html());
		$('#click_authorizeaim').trigger('click');
		$('#x_exp_date_m').children('option').each(function() { // to fix issue of extra 0 prefix to month drop down value
			if ($(this).val() < 10) { 
			var value=$(this).val();
			value=value.slice(-1); // to get last character
			$(this).html('0'+value); 
			$(this).val('0'+value); 
			}
		});
		$('#asubmit').hide();
            }else if(payment_module_name == 'stripejs' || payment_module_name == 'stripepro' || payment_module_name == 'firstdata' || payment_module_name == 'conektatarjeta' || payment_module_name == 'braintreejs_backup' || payment_module_name == 'brinkscheckout' || payment_module_name == 'twocheckout'){
		    //$('.velsof_content_section').appendTo('#selected_payment_method_html');
		$('#selected_payment_method_html').html($('.velsof_content_section').html());
		$('#firstdata_submit').hide();
		$('#conekta-submit-button').hide();
		$('.stripe-submit-button').hide();
		//$('#braintree-submit-button').hide();
		$('#twocheckoutCCForm #submit_payment').hide();
		$('#twocheckoutCCForm input.button').hide();
		if (payment_module_name == 'stripepro')
			$('#selected_payment_method_html').hide();
		else
			$('#selected_payment_method_html').show();
            }else if(payment_module_name == 'ewayrapid'){
		    //$('.velsof_content_section').appendTo('#selected_payment_method_html');
		$('#selected_payment_method_html').html($('.velsof_content_section').html());
		$('#processPayment').hide();
            }else if(payment_module_name == 'npaypalpro'){
		    //$('.velsof_content_section').appendTo('#selected_payment_method_html');
		$('#selected_payment_method_html').html($('.velsof_content_section').html());
		$('.paypalpro-submit-button').hide();
            }else if(payment_module_name == 'moneybookers' || payment_module_name == 'faspay' || payment_module_name == 'paynlpaymentmethods' ||  payment_module_name == 'add_gopay_new' ||  payment_module_name == 'quickpay' || payment_module_name == 'mollie' || payment_module_name == 'paynl_paymentmethods'){
                $('#velsof_payment_dialog .velsof_action_section').hide();
            }else if(payment_module_name == 'khipupayment'){
                $('#velsof_payment_dialog .velsof_action_section').hide();
            }else if(payment_module_name == 'epay'){
		$('#velsof_payment_container .velsof_content_section').css('height', '340px');
		$('#supercheckout_dialog_proceed').hide();
            }
            if(proceed_to_payment){
                moveToPayment();
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors);
        }
    });
}

var custom_epay = 0;
function placeOrder(){
    var errors = '';
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: $('#module_url').val() + '&ajax=true&rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: $('#velsof_supercheckout_form').serialize(),
        beforeSend: function() {            
            $('.errorsmall').remove();
            hideGeneralError();
            display_progress(20);
        },
        complete: function() {

        },
        success: function(jsonData)
        {
            if(jsonData['error'] != undefined){
                var has_validation_error = false;
                var i=0;
                if(jsonData['error']['checkout_option'] != undefined){
                    has_validation_error = true;
                    for(i in jsonData['error']['checkout_option']){
                        $('input[name="'+jsonData['error']['checkout_option'][i]['key']+'"]').parent().append('<span class="errorsmall">'+jsonData['error']['checkout_option'][i]['error']+'</span>');
			if (inline_validation == 1)
				$('input[name="'+jsonData['error']['checkout_option'][i]['key']+'"]').addClass('error-form').removeClass('ok-form');
                    }
                }

                var i=0;
                var key = '';
                if(jsonData['error']['customer_personal'] != undefined){
                    has_validation_error = true;
                    for(i in jsonData['error']['customer_personal']){
                        key = jsonData['error']['customer_personal'][i]['key'];
                        if(key == 'dob' || key == 'id_gender'){
                            $('.supercheckout_personal_'+key).append('<span class="errorsmall">'+jsonData['error']['customer_personal'][i]['error']+'</span>');
                        }else if(key == 'password'){
                            $('input[name="customer_personal['+key+']"]').parent().append('<span class="errorsmall">'+jsonData['error']['customer_personal'][i]['error']+'</span>');
			    if (inline_validation == 1)
				$('input[name="customer_personal['+key+']"]').addClass('error-form').removeClass('ok-form');
                        }else{
                            $('input[name="customer_personal['+key+']"]').parent().parent().parent().parent().append('<span class="errorsmall">'+jsonData['error']['customer_personal'][i]['error']+'</span>');
			    if (inline_validation == 1)
				$('input[name="customer_personal['+key+']"]').addClass('error-form').removeClass('ok-form');
                        }                        
                    }
                }

                var tmp_index;
                if(jsonData['error']['shipping_address'] != undefined){
                    has_validation_error = true;
                   for(tmp_index in jsonData['error']['shipping_address']){
		       $('input[name="shipping_address['+jsonData['error']['shipping_address'][tmp_index]['key']+']"]').parent().append('<span class="errorsmall">'+jsonData['error']['shipping_address'][tmp_index]['error']+'</span>');
		       if (inline_validation == 1)
				$('input[name="shipping_address['+jsonData['error']['shipping_address'][tmp_index]['key']+']"]').addClass('error-form').removeClass('ok-form');
			if(jsonData['error']['shipping_address'][tmp_index]['key']=='postcode')
			    $('#shipping_post_code').css("display","block");// helpful when postcode is hidden from our module but is equired for some country
                    }
                }

                
                var tmp_index;
                if(jsonData['error']['payment_address'] != undefined){
                    has_validation_error = true;
                   for(tmp_index in jsonData['error']['payment_address']){
                        $('input[name="payment_address['+jsonData['error']['payment_address'][tmp_index]['key']+']"]').parent().append('<span class="errorsmall">'+jsonData['error']['payment_address'][tmp_index]['error']+'</span>'); 
			if (inline_validation == 1)
				$('input[name="payment_address['+jsonData['error']['payment_address'][tmp_index]['key']+']"]').addClass('error-form').removeClass('ok-form');
			if(jsonData['error']['payment_address'][tmp_index]['key']=='postcode')
			    $('#payment_post_code').css("display","block"); // helpful when postcode is hidden from our module but is equired for some country
                    } 
                }
                i=0;
                if(jsonData['error']['general'] != undefined){
                    errors = '';
                    for(var i in jsonData['error']['general']){
                        errors += jsonData['error']['general'][i]+'<br>';
                    }                        
                }else if(has_validation_error){
                    errors = validationfailedMsg;
                }else{
                    errors = scOtherError;
                }
                displayGeneralError(errors);                                                   
                hide_progress();
                $("html, body").animate({ scrollTop: 0 }, "fast");
            }else{
                if(jsonData['warning'] != undefined){
                    //handle warning here
                }
                display_progress(30);
                var is_carrier_selected = true;

                //validate Methods
                $('#shipping-method .supercheckout-checkout-content .permanent-warning').remove();
                if($('#shipping-method .supercheckout_shipping_option').length){
                   if(!$('#shipping-method .supercheckout_shipping_option:checked').length){
                       is_carrier_selected = false;
                   } 
                }

                var is_payment_selected = true;
                $('#payment-method .supercheckout-checkout-content .permanent-warning').remove();
                if($('#payment-method input[name="payment_method"]').length){
                   if(!$('#payment-method input[name="payment_method"]:checked').length){
                       is_payment_selected = false;
                   } 
                }

                if(carriers_count == 0 && !(is_cart_virtual))
                    is_carrier_selected = false;
                
                if(!is_carrier_selected){
                    $('#shipping-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+ShippingRequired+'</div>');
                }
                if(!is_payment_selected){
                    $('#payment-method .supercheckout-checkout-content').html('<div class="permanent-warning">'+paymentRequired+'</div>');
                }

                if(!is_carrier_selected || !is_payment_selected){
                    hide_progress();
                    displayGeneralError('Please provide required Information');
                    $("html, body").animate({ scrollTop: 0 }, "fast");
                }else{

                    display_progress(50);
                    //Validate Order Extras
                    var messagePattern = /[<>{}]/i;
                    var message = '';
                    var extrasError = false;
                    if($('#supercheckout-comment_order').length){
                        message = $('#supercheckout-comment_order').val();
                        if(messagePattern.test(message)){
                            extrasError = true;
                            $('#supercheckout-comment_order').parent().append('<span class="errorsmall">'+commentInvalid+'</span>');
                        }
                    }

                    if($('#gift').length && $('#gift').is(':checked')){
                        message = $('#gift_message').val();
                        if(messagePattern.test(message)){
                            extrasError = true;
                            $('#gift_message').parent().append('<span class="errorsmall">'+commentInvalid+'</span>');
                        }
                    }

                    if($('#supercheckout-agree input[name="cgv"]').length && (!$('#supercheckout-agree input[name="cgv"]').is(':checked') && scp_required_tos == 1)){
                        extrasError = true;
                        $('#supercheckout-agree').after('<span class="errorsmall">'+tosRequire+'</span>');
                    }

                    if(extrasError){
                        hide_progress();
                    }else{
                        display_progress(80);
                        var is_free_order = false;
                        if (scp_use_taxes && scp_order_total_price <= 0){
                            is_free_order = true;
                        }else if(!scp_use_taxes && scp_order_total_price_wt <= 0){
                            is_free_order = true;
                        }
                        if(is_free_order){
                            createFreeOrder();
                        }else{
                            proceed_to_payment = true;
                            if($('input:radio[name="payment_method"]:checked').length){
                                var p_m_name = $('input:radio[name="payment_method"]:checked').attr('id');
                                if(p_m_name == 'stripejs' || p_m_name == 'stripepro' || p_m_name == 'firstdata' || p_m_name == 'conektatarjeta' || p_m_name == 'braintreejs_backup' || p_m_name == 'twocheckout' || p_m_name == 'brinkscheckout' || p_m_name == 'ewayrapid' || p_m_name == 'npaypalpro' ||  p_m_name == 'authorizeaim' || p_m_name == 'librapay' || p_m_name == 'secureframe' || p_m_name == 'cashondelivery' || p_m_name == 'compropago' || p_m_name == 'checkout' || p_m_name == 'westernunion' || p_m_name == 'billmatepartpayment' || p_m_name == 'billmateinvoice' || p_m_name == 'paysondirect' || p_m_name == 'mercadoc' || p_m_name == 'boletosantanderpro' || p_m_name == 'payu' || p_m_name == 'megashoppay' || p_m_name == 'zipcheck' || p_m_name == 'megareembolso' || p_m_name == 'payinstore' || p_m_name == 'codfee' || p_m_name == 'obsredsys' || p_m_name == 'hipay' || p_m_name == 'psphipay' || p_m_name == 'finanziamento' || p_m_name == 'cecatpv' || p_m_name == 'dineromail' || p_m_name == 'payulatam' || p_m_name == 'cuatrob'){
                                    moveToPayment();
                                }else{
                                    actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
                                }
				if (p_m_name == 'epay' && custom_epay == 0)
				{
					if($('#velsof_payment_container').is(':visible')) {
						$('#velsof_payment_container .velsof_dialog_close').click();
					}
					custom_epay = 1;
					$('#supercheckout_confirm_order').click();
				}
                            }else{
                                $('input:radio[name="payment_method"]').first().attr('checked', 'checked');
                                $('input:radio[name="payment_method"]').first().parent().addClass('checked');
                                actionOnPaymentSelect($('input:radio[name="payment_method"]:checked'));
                            }
                        }
                    }

                }
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
            displayGeneralError(errors);
            hide_progress();
            $("html, body").animate({ scrollTop: 0 }, "fast");
        }
    });
}

function createFreeOrder(){
    $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: $('#module_url').val() + '&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true'
                +'&method=createFreeOrder&token=' + static_token,
            beforeSend: function() {
            },
            success: function(jsonData)
            {
                if (typeof isGuest != 'undefined')
                    document.location.href = scp_guest_tracking_url+'?id_order='+encodeURIComponent(jsonData['order_reference'])+'&email='+encodeURIComponent(jsonData['email']);
                else
                    document.location.href = scp_history_url;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errors = sprintf(ajaxRequestFailedMsg, XMLHttpRequest, textStatus);
                displayGeneralError(errors);                                                   
                hide_progress();
            }
    });
}

function moveToPayment(){
    var url = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
    var p_m_name = $('input:radio[name="payment_method"]:checked').attr('id');
    var dialogContainer = '#velsof_payment_dialog .velsof_content_section ';
    var payment_method_html = ''
    if($('#payment_method_html').length){
        payment_method_html = $('#payment_method_html').html();
        $('#payment_method_html').html('');
    }
    
    
    if($(dialogContainer+'#payment_fetch_error').length){
        window.reload();
    }else{
        if(p_m_name == 'paypal' && url == 'javascript:void(0)'){
            $(dialogContainer+'#paypal_process_payment').trigger('click');
            $('#paypal_payment_form').submit(); // above statement was not working for Prestashop 1.6.1.0
        }else if(p_m_name == 'boleto'){
	    	$('#supercheckout_dialog_proceed').trigger('click');
	}
	else if(p_m_name == 'parspalpayment'){
		var form_action = $('#parspalpayment_form').attr('action');
		$('#parspalpayment_form').attr('action', '/'+form_action);
	    $('#parspalpayment_form').submit();
	}else if(p_m_name == 'pronesis_bancasella'){
            $('#bancasella_process_payment').trigger('click');
        }else if(p_m_name == 'deluxeservired'){
            $('#deluxeservired_form').submit();
        }else if(p_m_name == 'plationline'){
            location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
        }else if(p_m_name == 'bmsboletobancario'){
            location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
        }else if(p_m_name == 'paypalmx'){
	    $('#paypal-express-checkout-form').submit(); 
        }else if(p_m_name == 'cmcic_tbweb'){
            javascript:$('#PaymentRequest1').submit();
        }else if(p_m_name == 'sisoweb'){
	    $('#sisow_ebill_form').submit();
	}else if(p_m_name == 'sisowob'){
	    $('#sisow_overboeking_form').submit();
	}else if(p_m_name == 'sisowpp'){
	    $('#sisow_paypalec_form').submit();
	}else if(p_m_name == 'citrus'){
	    javascript:$('#citrus_form').submit();
	}else if(p_m_name == 'banc_sabadell'){
            javascript:$('#SabadellTPVForm').submit();
        }
	else if(p_m_name == 'ogone'){
	    document.forms['ogone_form'].submit();
        }else if(p_m_name == 'creditcardpaypal' && url == 'javascript:void(0)'){
            $('#paypal_payment_form_credit_card input[name=\'express_checkout\']').val('payment_cart');
		$(dialogContainer+'#paypal_process_payment_credit_card').trigger('click');
        }else if(p_m_name == 'paypalusa'){
            $(dialogContainer+'#paypal-standard-btn').trigger('click');
	    $('#velsof_payment_dialog #paypal-express-checkout-btn-product').click(); //for mexico paypalusa
        }else if(p_m_name == 'ccavenue'){
		javascript:document.redirect.submit();
        }else if(p_m_name == 'paypalwithfee'){
            $(dialogContainer+'#paypal_process_payment_').trigger('click');
        }else if(p_m_name == 'sisowideal'){
            $(dialogContainer+'#sisowideal_process_payment').trigger('click');
        }else if(p_m_name == 'sisowmc'){
            $(dialogContainer+'#sisowmistercash_process_payment').trigger('click');
        }else if(p_m_name == 'dineromail'){
            location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
        }else if(p_m_name == 'add_gopay_new'){
	    createPaymentPop();
	    $('#velsof_payment_dialog .velsof_action_section').show(); // @nitin 27 July - Important to show proceed button if it is hide in previous payment method selection	    
	    $('#gopay-payment-form .payment_module').css('display','none');
	}else if(p_m_name == 'khipupayment' || p_m_name == 'paynl_paymentmethods' || p_m_name == 'mollie' || p_m_name == 'moneybookers' || p_m_name == 'faspay' || p_m_name == 'paynlpaymentmethods' || p_m_name == 'epay' || p_m_name == 'quickpay'){
            createPaymentPop();
	    $('#velsof_payment_dialog .velsof_action_section').show(); // @nitin 27 July - Important to show proceed button if it is hide in previous payment method selection	    
        }else if(p_m_name == 'offlinecreditcard'){
	location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
        }else if(p_m_name == 'mobilpay_cc'){
        $('#mobilpay_cc_form').submit();
        }
        else if(p_m_name == 'paysera'  || p_m_name == 'pagofacil'){
	location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
	}else if(p_m_name == 'bankwire' || p_m_name == 'mercadopago' || p_m_name == 'add_bankwire' || p_m_name == 'swipp' || p_m_name == 'boleto' || p_m_name == 'pstransparenteloja5' || p_m_name == 'spsbradesco' || p_m_name == 'cielows' || p_m_name == 'edinar' || p_m_name == 'clictopay' || p_m_name == 'allpay' || p_m_name == 'pay2go' || p_m_name == 'cash' || p_m_name == 'postfinance' || p_m_name == 'pagseguro' || p_m_name == 'braintreejs' || p_m_name == 'bcash' || p_m_name == 'invoicepayment' || p_m_name == 'przelewy24' || p_m_name == 'prestalia_cashondelivery' ||  p_m_name == 'virtpaypayment' || p_m_name == 'cashondeliveryfeeplus' || p_m_name == 'pagonlineimprese' || p_m_name == 'mokejimai' || p_m_name == 'payplug' || p_m_name == 'seurcashondelivery' || p_m_name == 'cashondeliveryplus' || p_m_name == 'universalpay' || p_m_name == 'mandiri' || p_m_name == 'bni' || p_m_name == 'bca' || p_m_name == 'veritranspay' || p_m_name == 'przelewy24' || p_m_name == 'transbancaria' || p_m_name == 'cashondeliveryplusmax' || p_m_name == 'multibanco' || p_m_name == 'ceca' || p_m_name == 'dotpay' || p_m_name == 'postepay' || p_m_name == 'paypaladvanced' || p_m_name == 'trustly' || p_m_name == 'billmateinvoice' || p_m_name == 'billmatepartpayment' || p_m_name == 'cheque' || p_m_name == 'westernunion' ||  p_m_name == 'paysondirect' || p_m_name == 'mercadoc' || p_m_name == 'boletosantanderpro' || p_m_name == 'payu' ||  p_m_name == 'librapay' ||  p_m_name == 'secureframe' || p_m_name == 'cashondelivery' || p_m_name == 'compropago' || p_m_name == 'checkout' || p_m_name == 'megashoppay' || p_m_name == 'payulatam' || p_m_name == 'zipcheck' || p_m_name == 'megareembolso' || p_m_name == 'deluxecodfees' || p_m_name == 'payinstore' || p_m_name == 'codfee' || p_m_name == 'obsredsys' || p_m_name == 'hipay' || p_m_name == 'psphipay' || p_m_name == 'finanziamento'){
            if($(dialogContainer+'form').length){
                createPaymentPop();
		$('#velsof_payment_dialog .velsof_action_section').show(); // @nitin 27 July - Important to show proceed button if it is hide in previous payment method selection
            }
            else if(p_m_name == 'paysondirect'){ 
                 disableBtn(); } 
            else { 
                location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
            }        
        }else if(p_m_name == 'redsys'){
            $('#redsys_form').submit();
        }else if(p_m_name == 'cecatpv'){
            $('#cecatpv_form').submit();
	}else if(p_m_name == 'firstdata'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('#firstdata_submit').trigger('click');
	}else if(p_m_name == 'conektatarjeta'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('#conekta-submit-button').trigger('click');
	}else if(p_m_name == 'stripepro'){
	    $('#stripe-proceed-button').trigger('click');
	}else if(p_m_name == 'braintreejs_backup'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    //$('#braintree-dropin-form').submit();
	}else if(p_m_name == 'stripejs'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('.stripe-submit-button').trigger('click');
	}else if(p_m_name == 'twocheckout'){
		hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    //('#twocheckoutCCForm input.button').trigger('click');
	    $('#twocheckoutCCForm').submit();
	}else if(p_m_name == 'brinkscheckout'){
		hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('#twocheckoutCCForm #submit_payment').trigger('click');
	}else if(p_m_name == 'ewayrapid'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('#processPayment').trigger('click');
	}else if(p_m_name == 'npaypalpro'){
	    hide_progress(); // to hide progress bar in case some error occur in first data payment form
	    $('.paypalpro-submit-button').trigger('click');
	}else if(p_m_name == 'authorizeaim'){
	    hide_progress(); // to hide progress bar in case some error occur in authorizeaim payment form
	     $('#asubmit').trigger('click');
        }else if(p_m_name == 'iupay'){
            $('#iupay_form').submit();
        }else if(p_m_name == 'cuatrob'){
            $('#cuatrob_form').submit();
        }else if(p_m_name == 'gopay'){
            $('#gopay_form').submit();
        }else if($(dialogContainer+'button').length){
            createPaymentPop();
	    $('#velsof_payment_dialog .velsof_action_section').show(); // @nitin 27 July - Important to show proceed button if it is hide in previous payment method selection
        }else{
            if($(dialogContainer+'button').length){
                $(dialogContainer+'button').trigger('click');
            }else if($(dialogContainer+'form').length){
                $(dialogContainer+'form').trigger('click');
            }else if($(dialogContainer+'a').length){
                $(dialogContainer+'a').trigger('click');
            }else{
                if($('#payment_method_html').length){
                    $('#payment_method_html').html(payment_method_html);
                }
                alert('Payment Processing Error');
            }
        }        
    }
}

function createPaymentPop(){
    $('#velsof_payment_dialog').show();
}

function confirmOrder(){
    var payment_module_name = $('input:radio[name="payment_method"]:checked').attr('id');
    var dialogContainer = '#velsof_payment_dialog .velsof_content_section ';
    if(payment_module_name == 'bankwire' || payment_module_name == 'invoicepayment'){
	$('#velsof_payment_container .velsof_action_section').css('display','none'); //@Nitin Jain, 1-Oct-2015, to hide proceed button on click, because if clickd twice it was showing error.
    }else if(payment_module_name == 'add_gopay_new'){
	document.getElementById('gopay-payment-form').submit(); return false;
    }
    if(payment_module_name == 'bankwire' ||  payment_module_name == 'mercadopago' || payment_module_name == 'add_bankwire' || payment_module_name == 'cielows' || payment_module_name == 'spsbradesco' || payment_module_name == 'pstransparenteloja5' || payment_module_name == 'boleto' || payment_module_name == 'edinar' || payment_module_name == 'clictopay' || payment_module_name == 'allpay' || payment_module_name == 'pay2go' || payment_module_name == 'cash' || payment_module_name == 'postfinance' || payment_module_name == 'pagseguro' || payment_module_name == 'bcash' || payment_module_name == 'braintreejs' || payment_module_name == 'invoicepayment' || payment_module_name == 'przelewy24' || payment_module_name == 'prestalia_cashondelivery' ||  payment_module_name == 'virtpaypayment' || payment_module_name == 'cashondeliveryfeeplus' || payment_module_name == 'pagonlineimprese' || payment_module_name == 'mokejimai' || payment_module_name == 'payplug' || payment_module_name == 'seurcashondelivery' || payment_module_name == 'cashondeliveryplus' || payment_module_name == 'universalpay' || payment_module_name == 'mandiri' || payment_module_name == 'bni' || payment_module_name == 'bca' || payment_module_name == 'veritranspay' || payment_module_name == 'przelewy24' || payment_module_name == 'transbancaria' || payment_module_name == 'cashondeliveryplusmax' || payment_module_name == 'multibanco' || payment_module_name == 'ceca' || payment_module_name == 'dotpay' || payment_module_name == 'pagofacil' || payment_module_name == 'postepay' || payment_module_name == 'paysera' || payment_module_name == 'offlinecreditcard' || payment_module_name == 'paypaladvanced' || payment_module_name == 'trustly' || payment_module_name == 'cheque' || payment_module_name == 'deluxecodfees'){
        if($(dialogContainer+'form').length){
            $(dialogContainer+'form').submit();
        }else{
            location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
        }        
    }
    else if (payment_module_name == 'swipp'){ //@Gaurav Pal, 28-Dec-2015 for compatibility of swipp payment method(itcsm.dk)
        location.href = $('#'+$('input:radio[name="payment_method"]:checked').attr('value')+'_name').val();
    }
    else{
        if($(dialogContainer+'button').length){
            $(dialogContainer+'button').trigger('click');
        }else if($(dialogContainer+'form').length){
            $(dialogContainer+'form').trigger('click');
        }else if($(dialogContainer+'a').length){
            $(dialogContainer+'a').trigger('click');
        }else{
            alert('Payment Processing Error');
        }
    }    
}


function displayGeneralError(errors){
    if($('#supercheckout-empty-page-content .permanent-warning').length){
        $('#supercheckout-empty-page-content .permanent-warning').html(errors);
    }else{
        $('#supercheckout-empty-page-content').html('<div class="permanent-warning">'+errors+'</div>');
    }     
}

function hideGeneralError(){
    $('#supercheckout-empty-page-content .permanent-warning').remove();
}

function changePaymentMethodLabel(){
    if($('input:radio[name="payment_method"]').length){
        $('input:radio[name="payment_method"]').each(function(){
            var id = $(this).val();
            if($('#payment_method_'+id+' .payment_method_lbl').length){
                if($('#payment_method_'+id+' .payment_method_lbl a').length){
                    var label = $('#payment_method_'+id+' .payment_method_lbl a').html();
                }else{
                    var label = $('#payment_method_'+id+' .payment_method_lbl').html();
                }
		var current_name = $('#payment_method_name_'+id).html();
		$('#payment_method_name_'+id).after('<span class="fee_lbl"> ('+label+')</span>');
//                $('#payment_lbl_'+id).html(label);
             } 
        });
    }    
}





