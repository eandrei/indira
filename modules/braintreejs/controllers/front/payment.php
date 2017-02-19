<?php
/**
 * This file is part of module Stripe Reloaded
 *
 *  @author    Bellini Services <bellini@bellini-services.com>
 *  @copyright 2007-2015 bellini-services.com
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

class BraintreejsPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;
	public $display_column_right = false;

	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();

		if (!Validate::isLoadedObject($this->context->customer))
			return;
		
		// Check that the module is active
		if ($this->module->active)
		{
			$config = Configuration::getMultiple(array('BRAINTREE_CHECKOUT_MODE', 'BRAINTREE_UI_MODE'));

			$braintree_checkout_mode = $config['BRAINTREE_CHECKOUT_MODE'];
			//if the mode is inline (0), then this controller should not be receiving this request
			if ($braintree_checkout_mode == 0)
				return;

			//we default to dedicated mode and no returned content
			$this->module->displayPaymentForm(1, false);

			$braintree_dropinui_enabled = $config['BRAINTREE_UI_MODE'] ? 1 : 0;
			if ($braintree_dropinui_enabled)
				$this->setTemplate('payment_dropin.tpl');	//needs to exist in 'front' folder
			else
				$this->setTemplate('payment_hostedfields.tpl');	//needs to exist in 'front' folder
		}
		else
			return;
	}
}