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

$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/braintreejs.php');

$context = Context::getContext();

if (!$context->cookie->isLogged(true))
	Tools::redirect('authentication.php?back=order.php');
elseif (!Customer::getAddressesTotalById((int)($context->cookie->id_customer)))
	Tools::redirect('address.php?back=order.php?step=1');

$config = Configuration::getMultiple(array('BRAINTREE_CHECKOUT_MODE', 'BRAINTREE_UI_MODE'));

/* if the mode is inline (0), then this controller should not be receiving this request */
$braintree_checkout_mode = $config['BRAINTREE_CHECKOUT_MODE'];
if ($braintree_checkout_mode == 0)
	return;

/* we use dedicated mode and we need content returned */
$braintreejs = new braintreejs();
echo $braintreejs->displayPaymentForm(1, true);

include_once(dirname(__FILE__).'/../../footer.php');
