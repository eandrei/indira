{*
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
*}

{$braintree_javascript} {* html content, it cannot be escaped *}
{literal}
<script type="text/javascript">
	$(document).ready(function() {
		//execute the setup
		braintreeSetup();
	});
</script>
{/literal}

{if $braintree_is_dedicated_page}
{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='braintreejs'}">{l s='Checkout' mod='braintreejs'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Secure Payment' mod='braintreejs'} {* there is nothing to be escaped *}
{/capture}

<h1 class="page-heading">
    {l s='Secure Payment' mod='braintreejs'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{/if}

<div class="payment_module"{if $braintree_ps_version < '1.5'}style="border: 1px solid #595A5E; padding: 0.6em; margin-left: 0.7em;"{/if}>
	<h3 class="braintree_title"><img alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/secure-icon.png" />{l s='Pay using our secured payment server' mod='braintreejs'}</h3>

	{if isset($smarty.get.bt_error)}<a id="bt_error" name="bt_error"></a><div class="braintree-payment-errors">{l s='There was a problem processing your credit card, please double check your data and try again.' mod='braintreejs'}</div>{/if}

	<form data-ajax="true" action="{$this_path_bt|escape:'html':'UTF-8'}validation.php" method="POST" id="braintree-dropin-form">
		<input name="device_data" type="hidden" value="">
		<input name="submitDropInPayment" type="hidden" value="1">
		{if isset($braintree_payment_advanced) && $braintree_payment_advanced}
		<input name="braintree_payment_advanced" type="hidden" value="1">
		{/if}
		<div id="card-container"></div>
		<div style="float:none" class="ui-block-b"><input disabled type="submit" id="braintree-submit-button" name="submitDropInPayment" value="{l s='Submit' mod='braintreejs'}" class="exclusive button yellow" data-icon="check" data-iconpos="right" data-theme="b" data-ajax="true" /></div>
	</form>
</div>

<div id="braintree-translations">
	<span id="braintree-config-error">{l s='There was a problem creating the payment form, please contact us to report the issue' mod='braintreejs'}</span>
	<span id="braintree-cvc-default">{l s='Card Verification Number' mod='braintreejs'}</span>
	<span id="braintree-3ds-cancelled">{l s='3D Secure process cancelled by user' mod='braintreejs'}</span>
	<span id="braintree-invalid-submit">{l s='Opps, your payment information was not properly formatted, please correct and try again' mod='braintreejs'}</span>
	<span id="braintree-wrong-cvc">{l s='Wrong CVC.' mod='braintreejs'}</span>
	<span id="braintree-wrong-expiry">{l s='Wrong Credit Card Expiry date.' mod='braintreejs'}</span>
	<span id="braintree-wrong-card">{l s='Wrong Credit Card number.' mod='braintreejs'}</span>
	<span id="braintree-please-fix">{l s='Please fix it and submit your payment again.' mod='braintreejs'}</span>
	<span id="braintree-card-del">{l s='Your Credit Card has been successfully deleted, please enter a new Credit Card:' mod='braintreejs'}</span>
	<span id="braintree-card-del-error">{l s='An error occured while trying to delete this Credit card. Please contact us.' mod='braintreejs'}</span>
</div>

{if $braintree_is_dedicated_page}
<p class="cart_navigation clearfix" id="cart_navigation">
    <a class="button_large button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
	<i class="icon-chevron-left"></i>{l s='Other payment methods' mod='braintreejs'}
    </a>
</p>
{/if}
