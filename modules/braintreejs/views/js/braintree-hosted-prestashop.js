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
{if ($braintree_debug)}
var isDebug = true;
{else}
var isDebug = false;
{/if}

$(document).ready(function() {
	/* check if the form element exists, cancel if not */
	if ($('#braintree-hosted-form').length)
		braintreeSetup();
});

function braintreeSetup()
{
	/* if setup was already completed, then we skip */
	if ($('#braintree_setup_complete').val()==1)
		return false;

	/* use a hidden input field to capture that setup was already executed */
	$('#braintree-hosted-form').append('<input type="hidden" id="braintree_setup_complete" value="1" />');

	/* if errors exist, then display them */
	if ($('.braintree-payment-errors').text())
		$('.braintree-payment-errors').fadeIn(1000);
	try
	{
		
		braintree.setup("{$braintree_client_token}", "custom", {
			dataCollector: {
				kount: {
					environment: '{$braintree_env}',
				},
			},
			id: "braintree-hosted-form",
			onReady: function (obj) {
				if (isDebug)
				{
					console.log('onReady called');
					console.log(obj);
				}

				$('#braintree-hosted-form input[name="device_data"]').val(obj.deviceData);
				$('#braintree-paypal-form input[name="device_data"]').val(obj.deviceData);

				/* show the submit button */
				$('#braintree-submit-button').show();
			},
			onError: function (obj) {
				if (isDebug)
				{
					console.log('onError called');
					console.log(obj);
				}

				if (obj.type == 'CONFIGURATION')
				{
					$('#braintree-submit-button').hide();
					$('.braintree-payment-errors').text($('#braintree-config-error').text());
					$('.braintree-payment-errors').fadeIn(1000);
				}
				else
				{
					$('.braintree-payment-errors').text($('#braintree-invalid-submit').text());
					$('.braintree-payment-errors').fadeIn(1000);
				}
			},
			onPaymentMethodReceived: function (obj) {
				if (isDebug)
				{
					console.log('onPaymentMethodReceived called');
					console.log(obj);
				}

				/* clear any existing error messages */
				$('.braintree-payment-errors').text('');

				/* hide the form elements, replace with a loader bar */
				$('.braintree-payment-errors').hide();
				$('#card-container').hide();
				$('#paypal-container').hide();
				$('#braintree-ajax-loader').show();

				/* Disable the submit button to prevent repeated clicks */
				$('#braintree-submit-button').attr('disabled', 'disabled');

{if ($braintree_3ds)}
				if (obj.type == 'CreditCard')
					check3DSPMN(obj.nonce, btamount);
{else}
				$('#braintree-hosted-form').append("<input type='hidden' name='payment_method_nonce' value='" + obj.nonce + "'/>"); 
				
				$('#braintree-hosted-form').unbind('submit');
				$('#braintree-hosted-form').submit();
{/if}

			},

			hostedFields: {
			  onFieldEvent: function (event) {
				if (event.type === "focus") {
				  /* Handle focus */
				} else if (event.type === "blur") {
				  /* Handle blur */
				} else if (event.type === "fieldStateChange") {
					handleFieldStateChange(event);
				}
			  },
			  styles: {
				/* Style all elements */
				"input": {
				  "font-size": "16pt",
				  "color": "#3A3A3A"
				},

				/* Styling a specific field */
				".number": {
				  "font-family": "monospace"
				},

				/* Styling element state */
				":focus": {
				  "color": "blue"
				},
				".valid": {
				  "color": "green"
				},
				".invalid": {
				  "color": "red"
				},

				/* Media queries */
				/* Note that these apply to the iframe, not the root window. */
				"@media screen and (max-width: 700px)": {
				  "input": {
					"font-size": "14pt"
				  }
				}
			  },
			  number: {
				selector: "#card-number",
			  },
			  cvv: {
				selector: "#cvv-number"
			  },
{if ($braintree_use_postcode)}
			  postalCode: {
				selector: "#postal-code-number"
			  },
{/if}
			  expirationDate: {
				selector: "#expiration-date"
			  },
{* not used
			  expirationMonth: {
				selector: "#expiration-month"
			  },
			  expirationYear: {
				selector: "#expiration-year"
			  },
*}
			}
		});

{if ($braintree_paypal_enabled)}
		/* add paypal button to existing form */
		braintree.setup('{$braintree_client_token}', "paypal", {
			container: "paypal-button",
			{if (!$braintree_paypal_future)}
			singleUse: true,
			intent: 'authorize',
			{/if}
			amount: {$braintree_amount},
			currency: '{$braintree_currency}',
			locale: '{$braintree_locale}',
			onSuccess: paypalOnSuccess,
			onCancelled: paypalOnCancelled,
			onUnsupported: paypalOnUnsupported,

			onPaymentMethodReceived: function (obj) {
				if (isDebug)
				{
					console.log('onPaymentMethodReceived called');
					console.log(obj);
				}
				/* clear any existing error messages */
				$('.braintree-payment-errors').text('');

				/* hide the form elements, replace with a loader bar */
				$('.braintree-payment-errors').hide();
				$('#card-container').hide();
				$('#paypal-container').hide();
				$('#braintree-ajax-loader').show();

				/* Disable the submit button to prevent repeated clicks */
				$('#braintree-submit-button').attr('disabled', 'disabled');

				$('#braintree-hosted-form').append("<input type='hidden' name='payment_method_nonce' value='" + obj.nonce + "'/>"); 
				
				$('#braintree-hosted-form').unbind('submit');
				$('#braintree-hosted-form').submit();
			},

		});

{if ($braintree_paypal_future)}
		/* handle click for existing paypal account */
		$("#braintree-paypal-form").submit(function( event ) {
			/* disable card functionality */
			cardEnabled=false;

			/* hide card form */
			$('#card-container').hide();

			/* hide new paypal button form */
			$('#new-paypal-container').hide();

			/* hide existing paypal button form */
			$('#default-paypal-container').hide();

			/* hide the submit button */
			$('#braintree-submit-button').hide();

			/* show the progress bar */
			$('#braintree-ajax-loader-paypal').show();

		});
{/if}

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

	}
	catch (e)
	{
		if (isDebug)
			console.log('caught an error: ' + e.stack);
		$('#braintree-submit-button').hide();
		$('.braintree-payment-errors').text($('#braintree-config-error').text());
		$('.braintree-payment-errors').fadeIn(1000);
	}

{literal}

	function handleFieldStateChange(event)
	{
		if (isDebug)
		{
			console.log('handleFieldStateChange called');
			console.log(event);
		}

		/* visa|master-card|american-express|diners-club|discover|jcb|unionpay|maestro	 */
		if (event.isEmpty) {
			/* reset the class to remove any existing card types */
			$('#braintree-hosted-form .payment-method-icon').attr('class','payment-method-icon');

			/* reset the CVV label */
			$('#cvv-number-label').text($('#braintree-cvc-default').text());
		}

		if (event.card) {
			/* reset the class to remove any existing card types */
			$('#braintree-hosted-form .payment-method-icon').attr('class','payment-method-icon');

			/* then add the new class */
			$('#braintree-hosted-form .payment-method-icon').addClass(event.card.type);

			if (event.card.code) {
				$('#cvv-number-label').text(event.card.code.name + ' (' + event.card.code.size + ' digits)');
			}
		}
	}
}
{/literal}

{if ($braintree_3ds)}

	function check3DSPMN(nonce, amount)
	{
		if (isDebug)
		{
			console.log('check3DS called');
			console.log('nonce is: ' + nonce);
			console.log('amount is: ' + amount);
		}

		try
		{
			var braintree_api_client = new braintree.api.Client({
			  clientToken: '{$braintree_client_token}'
			});

			braintree_api_client.verify3DS({
				amount: amount,
				creditCard: nonce,
			    onUserClose: function () {
					if (isDebug)
						console.log('onUserClose called');
				
					$('.braintree-payment-errors').text($('#braintree-3ds-cancelled').text());
					$('.braintree-payment-errors').fadeIn(1000);

					$('#card-container').show();
					$('#paypal-container').show();
					$('#braintree-ajax-loader').hide();

					$('#braintree-submit-button').removeAttr('disabled');
			    }

			}, function (error, response) {
{*
				/* test error scenario */
				/*
				var error = [];
				error['message'] = 'fake error';
				*/
*}
				if (!error) 
				{
					if (isDebug)
					{
						console.log('response: ');
						console.log(JSON.stringify(response, null, 4));
					}

					$('#braintree-hosted-form').append("<input type='hidden' name='payment_method_nonce' value='" + response.nonce + "'/>"); 
					
					$('#braintree-hosted-form').unbind('submit');
					$('#braintree-hosted-form').submit();

				}
				else
				{
					if (isDebug)
						console.log('verify3DS error occurred: ' + error.message);

					/* capture the error message to display */
					$('.braintree-payment-errors').text(error.message);

					/* show the form elements */
					$('#card-container').show();
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
			$('#braintree-hosted-form').unbind('submit');

			$("#braintree-submit-paypal-button").click(function(event) {
				event.preventDefault();

				$('#braintree-hosted-form').append("<input type='hidden' name='submitPaypalPayment' value='1'/>"); 

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

				$('#braintree-hosted-form').submit();

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
