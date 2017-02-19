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

{if isset($smarty.get.bt_error)}<a id="bt_error" name="bt_error"></a><div class="braintree-payment-errors">{l s='There was a problem processing your credit card, please double check your data and try again.' mod='braintreejs'}</div>{/if}

<p class="payment_module">
	<a href="{$braintree_pay_url|escape:'html':'UTF-8'}" title="{$buttonText|escape:'html':'UTF-8'}">
		{if $braintree_cards.visa == 1}<img rel="Visa" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-visa-logo.png" />{/if}
		{if $braintree_cards.mastercard == 1}<img rel="MasterCard" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-mastercard-logo.png" />{/if}
		{if $braintree_cards.discover == 1}<img rel="Discover" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-discover-logo.png" />{/if}
		{if $braintree_cards.amex == 1}<img rel="American Express" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-amex_logo.png" />{/if}
		{if $braintree_cards.jcb == 1}<img rel="JCB" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-jcb-logo.png" />{/if}
		{if $braintree_cards.diners == 1}<img rel="Diners Club" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-diners-logo.png" />{/if}
		{if $braintree_cards.maestro == 1}<img rel="Maestro" alt="" src="{$this_path_bt|escape:'html':'UTF-8'}views/img/cc-maestro-logo.png" />{/if}

		{$buttonText|escape:'html':'UTF-8'}
	</a>
</p>

{literal}
<script type="text/javascript">
	$(document).ready(function() {
		if ($('.braintree-payment-errors').text())
			$('.braintree-payment-errors').fadeIn(1000);
	});
</script>
{/literal}
