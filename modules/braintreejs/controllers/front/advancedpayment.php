<?php
/**
 * This file is part of module Stripe Reloaded
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

class BraintreejsAdvancedpaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;

		parent::initContent();

		//this appears to be defective in the 'advanced eu compliance' module.  If the customer has not logged in yet, the token created will not be the same value as after the customer logs in.  
		// this means we can expect this error to appear if the customer logs into an existing account during the checkout process.  If they reload the page, then it works properly.
		// it would seem that the 'advanced eu compliance' module needs to obtain a new token when it detects that the customer has logged into an existing account.
//		if (!$this->isTokenValid())
//			die($this->module->l($this->module->displayName.' Error: (invalid token)'));

		if (!$this->module->checkSettings())
			die($this->module->l($this->module->displayName.' Error: (Invalid Settings)'));

		if (!Validate::isLoadedObject($this->context->customer))
			return;

		$config = Configuration::getMultiple(array('BRAINTREE_UI_MODE', 'BRAINTREE_CARD_VISA', 'BRAINTREE_CARD_MASTERCARD', 'BRAINTREE_CARD_AMEX', 'BRAINTREE_CARD_JCB', 'BRAINTREE_CARD_DISCOVER', 'BRAINTREE_CARD_DINERS', 'BRAINTREE_CARD_MAESTRO', 'PS_SSL_ENABLED'));

		$braintree_dropinui_enabled = $config['BRAINTREE_UI_MODE'] ? 1 : 0;

		//for advanced payment, we only support a dedicated page mode, so we ignore the option
		$braintree_pay_url = $this->module->getModuleLink('braintreejs', 'payment', array(), $config['PS_SSL_ENABLED']);

		$cards = array();
		$cards['visa'] = $config['BRAINTREE_CARD_VISA'];
		$cards['mastercard'] = $config['BRAINTREE_CARD_MASTERCARD'];
		$cards['amex'] = $config['BRAINTREE_CARD_AMEX'];
		$cards['jcb'] = $config['BRAINTREE_CARD_JCB'];
		$cards['discover'] = $config['BRAINTREE_CARD_DISCOVER'];
		$cards['diners'] = $config['BRAINTREE_CARD_DINERS'];
		$cards['maestro'] = $config['BRAINTREE_CARD_MAESTRO'];

		$this->context->smarty->assign(array(
			'braintree_payment_advanced' => 1,
			'braintree_cards' => $cards,
			'braintree_pay_url' => $braintree_pay_url,
			'buttonText' => $this->module->l('Pay by Credit Card or Paypal'),
			'this_path_bt' => $this->module->getPathUri(),
		));

		$this->module->displayPaymentForm(1, false);

		if ($braintree_dropinui_enabled)
			$this->setTemplate('payment_dropin.tpl');
		else
			$this->setTemplate('payment_hostedfields.tpl');

	}

}