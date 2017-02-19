<?php
/**
 * This file is part of module Braintree
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

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_26_0($object, $install = false)
{
	//this is used to bypass meaningless validator rules, since install parameter is never used
	if ($install)
		$install = true;

	$bt_version = Configuration::get('BRAINTREE_VERSION');

	if ((!$bt_version) || (empty($bt_version)) || ($bt_version < $object->version))
		Configuration::updateValue('BRAINTREE_VERSION', '1.26.0');

	try 
	{
		Configuration::updateValue('BRAINTREE_CARD_VISA', 1);
		Configuration::updateValue('BRAINTREE_CARD_MASTERCARD', 1);
		Configuration::updateValue('BRAINTREE_CARD_AMEX', 1);
		Configuration::updateValue('BRAINTREE_CARD_JCB', 0);
		Configuration::updateValue('BRAINTREE_CARD_DISCOVER', 0);
		Configuration::updateValue('BRAINTREE_CARD_DINERS', 0);

		//default to inline for new installs. 0=inline, 1=dedicated page
		Configuration::updateValue('BRAINTREE_CHECKOUT_MODE', 0);

		//delete payment.tpl and payment_dropin.tpl from \views\templates\hook, they were moved to front
		$path = realpath(_PS_MODULE_DIR_.$object->name);
		$file1 = realpath($path.'/views/templates/hook/payment.tpl');
		$file2 = realpath($path.'/views/templates/hook/payment_dropin.tpl');
		if (file_exists($file1))
			unlink($file1);
		if (file_exists($file2))
			unlink($file2);
	}
	catch (Exception $e)
	{
		//die quitely
		$e = $e;
	}

	return true;
}
