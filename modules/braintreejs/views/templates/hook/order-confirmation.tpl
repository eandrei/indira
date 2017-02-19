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

{if $bt_order.valid == 1 || $os_back_ordered}
	<div class="conf confirmation">{l s='Congratulations, your payment has been approved and your order has been saved under the reference' mod='braintreejs'} <b>{$bt_order.reference|escape:'htmlall':'UTF-8'}</b>.</div>
{else}
	{if ($order_pending || $settlement_pending)}
		<div class="conf confirmation">{l s='Congratulations, your payment has been received and your order has been saved under the reference' mod='braintreejs'} <b>{$bt_order.reference|escape:'htmlall':'UTF-8'}</b>.</div>

		<div class="conf confirmation">{l s='We will review and process your order shortly.' mod='braintreejs'}</div>

	{else}
		<div class="error">{l s='Sorry, unfortunately an error occured during the transaction.' mod='braintreejs'}<br /><br />
		{l s='Please double-check your credit card details and try again or feel free to contact us to resolve this issue.' mod='braintreejs'}<br /><br />
		({l s='Your Order\'s Reference:' mod='braintreejs'} <b>{$bt_order.reference|escape:'htmlall':'UTF-8'}</b>)</div>
	{/if}
{/if}