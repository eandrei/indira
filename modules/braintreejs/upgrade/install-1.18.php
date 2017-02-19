<?php
/**
 * This file is part of module Braintree
 *
 *  @author    Bellini Services <bellini@bellini-services.com>
 *  @copyright 2007-2014 bellini-services.com
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

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_18($object, $install = false)
{
	//this is used to bypass meaningless validator rules, since install parameter is never used
	if ($install)
		$install = true;

	$bt_version = Configuration::get('BRAINTREE_VERSION');

	if ((!$bt_version) || (empty($bt_version)) || ($bt_version < $object->version))
		Configuration::updateValue('BRAINTREE_VERSION', '1.18');

	//new paypal feature, disabled by default
	Configuration::updateValue('BRAINTREE_PAYPAL_ENABLED', 0);

	//new payment Instrument Type to determine if card or paypal was used
	Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'braintree_transaction` ADD `payment_instrument_type` VARCHAR( 255 ) NOT NULL DEFAULT \'credit_card\', ADD INDEX ( `payment_instrument_type` )');
	
	//new UUID
	Configuration::updateValue('BRAINTREE_UUID', md5(Tools::passwdGen()));

	return true;
}
