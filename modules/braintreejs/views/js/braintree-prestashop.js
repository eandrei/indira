/*
 * This file is part of module Braintree
 *
 *  @author    Bellini Services <bellini@bellini-services.com>
 *  @copyright 2007-2016 bellini-services.com
 *  @license   readme
 *
 * Your purchase grants you usage rights subject to the terms outlined by this license.
 *
 * You CAN use this module with a single, non-multi store configuration, production installation and unlimited test installations of PrestaShop.
 * You CAN make any modifications necessary to the module to make it fit your needs. However, the modified module will still remain subject to this license.
 *
 * You CANNOT redistribute the module as part of a content management system (CMS) or similar system.
 * You CANNOT resell or redistribute the module, modified, unmodified, standalone or combined with another product in any way without prior written (email) consent from bellini-services.com.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

{* this javascript file is parsed by smarty, if you intend to show comments they must be /* */ and not // *}

var cardEnabled = true;
var btamount = {$braintree_amount};
var streetAddress1 = "{$braintree_address1}";
var postalCode = "{$braintree_postalCode}";
{if ($braintree_debug)}
var isDebug = true;
{else}
var isDebug = false;
{/if}

$(document).ready(function() {
	/* check if the form element exists, cancel if not */
	if ($('#braintree-payment-form').length)
		braintreeSetup();
});

function braintreeSetup()
{
	if ($('#braintree_setup_complete').val()==1)
		return false;

	/* use a hidden input field to capture that setup was already executed */
	$('#braintree-payment-form').append('<input type="hidden" id="braintree_setup_complete" value="1" />');

	if ($('.braintree-payment-errors').text())
		$('.braintree-payment-errors').fadeIn(1000);
	
	{if ($braintree_3ds)}

	$('form#braintree-payment-form').submit(function(e) {
		e.preventDefault();
		validate3DSForm(e);
	});

	{else}

	var braintree1 = Braintree.create('{$braintree_clientside}');

	/* setup card tokenization */
	braintree1.onSubmitEncryptForm("braintree-payment-form", validateBTForm);

	{/if}

	/* setup braintree data collection */
	BraintreeData.setup('{$braintree_merchant_id}', 'braintree-payment-form', {$braintree_env});

	{if ($braintree_paypal_enabled)}
		/* add paypal button to existing form */
		braintree.setup('{$braintree_client_token}', "paypal", {
			container: "paypal-button",
			{if (!$braintree_paypal_future)}
			singleUse: true,
			{/if}
			amount: {$braintree_amount},
			currency: '{$braintree_currency}',
			onSuccess: paypalOnSuccess,
			onCancelled: paypalOnCancelled,
			onUnsupported: paypalOnUnsupported
		});

		/* handle click for existing paypal account */
		$("#braintree-paypal-form").submit(function( event ) {
			/* disable card functionality */
			cardEnabled=false;

			/* hide card form */
			$('#card-container').hide();

			/* hide new paypal button form */
			$('#new-paypal-container').hide();

			/* hide the submit button */
			$('#braintree-submit-button').hide();

			/* show the progress bar */
			$('#braintree-ajax-loader-paypal').show();

		});
		
		$(".bt-pp-submit").click(function(event) {
			event.preventDefault();
			token = $(this).data('token');
			$('#braintree-paypal-form input[name="payment_method_token"]').val(token);
			$( "#braintree-paypal-form" ).submit();
		});
		$(".bt-pp-submit").submit(function(event) {
			event.preventDefault();
		});
	{/if}

{literal}

    $(function() {
        $('.braintree-card-number').validateCreditCard(function(result) {
			if ($(this).val().length >= 2)
			{
				if (isDebug)
					console.log('Card type: ' + (result.card_type == null ? '-' : result.card_type.name));

				bt_card_type = (result.card_type == null ? '' : result.card_type.name);

				if (bt_card_type == 'diners_club_carte_blanche' || bt_card_type == 'diners_club_international')
					bt_card_type = 'diners';
				else if (bt_card_type == 'laser')
					bt_card_type = 'maestro';
				else if (bt_card_type == 'visa_electron')
					bt_card_type = 'visa';

				$('.cc-icon').removeClass('enable');
				$('.cc-icon').removeClass('disable');

				$('.cc-icon').each(function() {
					if ($(this).attr('rel') == bt_card_type)
						$(this).addClass('enable');
					else
						$(this).addClass('disable');
				});
			}
			else
			{
				$('.cc-icon').removeClass('enable');
				$('.cc-icon:not(.disable)').addClass('disable');
			}
        });
    });
}
{/literal}

	{if ($braintree_3ds)}

{literal}

	function validate3DSForm()
	{

		if (cardEnabled)
		{
			var result = $('.braintree-card-number').validateCreditCard();

			if (isDebug)
			{
				console.log('Card result: ');
				console.log(JSON.stringify(result, null, 4));
			}

			if (!result.valid)
				$('.braintree-payment-errors').text($('#braintree-wrong-card').text() + ' ' + $('#braintree-please-fix').text());
			else if (!validateExpiry($('#braintree-card-expiry-month').val(), $('#braintree-card-expiry-year').val()))
				$('.braintree-payment-errors').text($('#braintree-wrong-expiry').text() + ' ' + $('#braintree-please-fix').text());
			else if (!validateCVC($('.braintree-card-cvc').val()))
				$('.braintree-payment-errors').text($('#braintree-wrong-cvc').text() + ' ' + $('#braintree-please-fix').text());
			else
			{
				/* clear any existing error messages */
				$('.braintree-payment-errors').text('');

				/* hide the form elements, replace with a loader bar */
				$('.braintree-payment-errors').hide();
				$('#card-wrapper').hide();
				$('#paypal-container').hide();
				$('#braintree-ajax-loader').show();

				/* Disable the submit button to prevent repeated clicks */
				$('#braintree-submit-button').attr('disabled', 'disabled');

				check3DS(btamount, $('.braintree-card-number').val(), $('#braintree-card-expiry-month').val(), $('#braintree-card-expiry-year').val(), $('.braintree-card-cvc').val(), streetAddress1, postalCode);

				/* return false to prevent the form submit.  That will occur in an event handler in check3DS */
				return false;
			}

			$('.braintree-payment-errors').fadeIn(1000);
			return false;
		}

		return false;
	}

{/literal}

	{/if}

{literal}

	function validateBTForm()
	{
		if (cardEnabled)	/* card */
		{
			var result = $('.braintree-card-number').validateCreditCard();

			if (isDebug)
			{
				console.log('Card result: ');
				console.log(JSON.stringify(result, null, 4));
			}

			if (!result.valid)
				$('.braintree-payment-errors').text($('#braintree-wrong-card').text() + ' ' + $('#braintree-please-fix').text());
			else if (!validateExpiry($('#braintree-card-expiry-month').val(), $('#braintree-card-expiry-year').val()))
				$('.braintree-payment-errors').text($('#braintree-wrong-expiry').text() + ' ' + $('#braintree-please-fix').text());
			else if (!validateCVC($('.braintree-card-cvc').val()))
				$('.braintree-payment-errors').text($('#braintree-wrong-cvc').text() + ' ' + $('#braintree-please-fix').text());
			else
			{

				$('#braintree-payment-form').append("<input type='hidden' name='submitCardPayment' value='1'/>"); 

				$('.braintree-payment-errors').hide();
				$('#card-wrapper').hide();
				$('#paypal-container').hide();
				$('#braintree-ajax-loader').show();
				/* Disable the submit button to prevent repeated clicks */
				$('#braintree-submit-button').attr('disabled', 'disabled');
				/* Prevent the form from submitting with the default action */
				return true;
			}

			$('.braintree-payment-errors').fadeIn(1000);
			return false;

		}
		else	/* paypal */
		{
			$('#braintree-payment-form').append("<input type='hidden' name='submitPaypalPayment' value='1'/>"); 

			/* hide new paypal button form */
			$('#new-paypal-container').hide();
			/* hide the existing paypal account */
			$('#default-paypal-container').hide();
			/* hide errors */
			$('.braintree-payment-errors').hide();
			/* show progress bar */
			$('#braintree-ajax-loader-paypal').show();
			/*  Disable the submit button to prevent repeated clicks */
			$('#braintree-submit-paypal-button').attr('disabled', 'disabled'); 
			/* hide the submit button */
			$('#braintree-submit-paypal-button').hide();

			return true; /*  Prevent the form from submitting with the default action */
		}

	}

	function validateCVC(t)
	{
		return t=trim(t),/^\d+$/.test(t)&&t.length>=3&&t.length<=4
	}
	function validateExpiry(t,n)
	{
		var r,i;return t=trim(t),n=trim(n),/^\d+$/.test(t)?/^\d+$/.test(n)?parseInt(t,10)<=12?(i=new Date(n,t),r=new Date,i.setMonth(i.getMonth()-1),i.setMonth(i.getMonth()+1,1),i>r):!1:!1:!1;
	}

	function trim(e){return(e+"").replace(/^\s+|\s+$/g,"")}

{/literal}

{if ($braintree_3ds)}
	function check3DS(amount, card, month, year, cvv, address, postalcode)
	{
		if (isDebug)
		{
			console.log('check3DS called');
			console.log('amount is: ' + amount);
			console.log('card is: ' + card);
			console.log('month is: ' + month);
			console.log('year is: ' + year);
			console.log('cvv is: ' + cvv);
			console.log('address is: ' + address);
			console.log('postalcode is: ' + postalcode);
		}

		try
		{

			var braintree_api_client = new braintree.api.Client({
			  clientToken: '{$braintree_client_token}'
			});

			braintree_api_client.verify3DS({
			  amount: amount,
			  creditCard: {
				number: card,
				expirationMonth: month,
				expirationYear: year,
				cvv: cvv,
				billingAddress: {
					streetAddress: address,
					postalCode: postalcode,
				}
			  },
			  onUserClose: function () {
					$('.braintree-payment-errors').text('Process cancelled by user');

					$('#card-wrapper').show();
					$('#paypal-container').show();
					$('#braintree-ajax-loader').hide();

					$('#braintree-submit-button').removeAttr('disabled');

			  }

			}, function (error, response) {
				/* test error scenario */
				/*
				var error = [];
				error['message'] = 'fake error';
				*/

				if (!error) 
				{
					if (isDebug)
					{
						console.log('response: ');
						console.log(JSON.stringify(response, null, 4));
					}

					$('#braintree-payment-form').append("<input type='hidden' name='submitCardPayment' value='1'/>"); 
					$('#braintree-payment-form').append("<input type='hidden' name='payment_method_nonce' value='" + response.nonce + "'/>"); 
					
					$('#braintree-payment-form').unbind('submit');
					$('#braintree-payment-form').submit();

				}
				else
				{
					if (isDebug)
						console.log('verify3DS error occurred: ' + error.message);

					/* capture the error message to display */
					$('.braintree-payment-errors').text(error.message);

					/* show the form elements */
					$('#card-wrapper').show();
					$('#paypal-container').show();
					$('#braintree-ajax-loader').hide();

					/* enabled the submit button */
					$('#braintree-submit-button').removeAttr('disabled');

					/* show the message */
					$('.braintree-payment-errors').fadeIn(1000);
				}
			});

			if (isDebug)
				console.log('after verify3DS call');

		}
		catch (e)
		{
			if (isDebug)
				console.log('caught an error: ' + e.stack);
		}
	}

{/if}

{if ($braintree_paypal_enabled)}

	/* called when the customer adds a new paypal payment method */
	function paypalOnSuccess()
	{
		if (isDebug)
			console.log('paypalOnSuccess called');

		/* hide the card form */
		$('#card-container').hide();

		{if ($braintree_3ds)}
			/* we need to remove the submit event since the 3ds event was added	*/
			$('#braintree-payment-form').unbind('submit');

			$("#braintree-submit-paypal-button").click(function(event) {
				event.preventDefault();

				$('#braintree-payment-form').append("<input type='hidden' name='submitPaypalPayment' value='1'/>"); 

				/* hide new paypal button form */
				$('#new-paypal-container').hide();
				/* hide the existing paypal account */
				$('#default-paypal-container').hide();
				/* hide errors */
				$('.braintree-payment-errors').hide();
				/* show progress bar */
				$('#braintree-ajax-loader-paypal').show();
				/*  Disable the submit button to prevent repeated clicks */
				$('#braintree-submit-paypal-button').attr('disabled', 'disabled'); 
				/* hide the submit button */
				$('#braintree-submit-paypal-button').hide();

				$('#braintree-payment-form').submit();

			});

		{/if}

		/* show the submit button */
		$('#braintree-submit-paypal-button').show();

		/* disable card validations */
		cardEnabled=false;
	}

	function paypalOnCancelled()
	{
		if (isDebug)
			console.log('the paypal flow was cancelled by user');

		/* show the card form */
		$('#card-container').show();

		/* hide the submit button */
		$('#braintree-submit-paypal-button').hide();

		/* enable card validations */
		cardEnabled=true;
	}

	function paypalOnUnsupported()
	{
		if (isDebug)
			console.log('paypal is not supported with this browser.  most likely an SSL certificate is not properly installed and enabled');
		alert('Your browser does not support this Paypal feature.  Please try another payment method or contact our support team if you believe this is an error');
	}

{/if}
