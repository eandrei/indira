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

function upgrade_module_1_27_0($object, $install = false)
{
	//this is used to bypass meaningless validator rules, since install parameter is never used
	if ($install)
		$install = true;

	$bt_version = Configuration::get('BRAINTREE_VERSION');

	if ((!$bt_version) || (empty($bt_version)) || ($bt_version < $object->version))
		Configuration::updateValue('BRAINTREE_VERSION', '1.27.0');

	try 
	{
		//default to off for new installs. 0=off, 1=on
		Configuration::updateValue('BRAINTREE_3DS', 0);
	}
	catch (Exception $e)
	{
		//die quitely
		$e = $e;
	}

	return true;
}
