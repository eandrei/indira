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
	$('#braintree-dropin-form').append('<input type="hidden" id="braintree_setup_complete" value="1" />');

	if ($('.braintree-payment-errors').text())
		$('.braintree-payment-errors').fadeIn(1000);

	/* add dropin ui with card and paypal */
	braintree.setup('{$braintree_client_token}', "dropin", {
		dataCollector: {
			kount: {
				environment: '{$braintree_env}',
			},
		},
		container: "card-container",
		paypal: {
			container: "paypal-container",
			{if (!$braintree_paypal_future)}
			singleUse: true,
			intent: 'authorize',
			{/if}
			amount: {$braintree_amount},
			locale: '{$braintree_locale}',
			currency: '{$braintree_currency}',
			onUnsupported: paypalOnUnsupported
		},
		onReady: function (braintreeInstance) {
			$('#braintree-dropin-form input[name="device_data"]').val(braintreeInstance.deviceData);
			$('#braintree-dropin-form #braintree-submit-button').prop('disabled', false);
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

				$('#braintree-dropin-form').append("<div class='braintree-payment-errors'>" + $('#braintree-config-error').text() + "</div>"); 
				$('.braintree-payment-errors').fadeIn(1000);
			}
			else
			{
				$('#braintree-dropin-form').append("<div class='braintree-payment-errors'>" + $('#braintree-invalid-submit').text() + "</div>"); 
				$('.braintree-payment-errors').fadeIn(1000);
			}
		},
	});	
}

function paypalOnUnsupported()
{
	alert('Your browser does not support this Paypal feature.  Please try another payment method or contact our support team if you believe this is an error');
}
