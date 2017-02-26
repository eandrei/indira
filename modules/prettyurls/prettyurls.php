<?php
/**
 * Pretty URLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  FMM Modules
 * @package   Prettyurls
 * @author    FMM Modules
 * @copyright Copyright 2016 © FMM Modules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
exit;
if (!defined('_MYSQL_ENGINE_'))
define('_MYSQL_ENGINE_', 'MyISAM');
class Prettyurls extends Module
{
	public function __construct()
	{
		$this->name = 'prettyurls';
		$this->tab = 'seo';
		$this->version = '2.0.0';
		$this->author = 'FMM Modules';
		$this->module_key = 'db5a06ecc8e806d8941ed9a91d6d3ad2';
		parent::__construct();
		$this->displayName = $this->l('Pretty URLs');
		$this->description = $this->l('This module will remove IDs from URLs and make them SEO friendly.');
		$this->tabClass = 'AdminPrettyUrls';
	}

	public function install()
	{
		return (parent::install()
				&& $this->clearCache()
				&& $this->moveFiles()
				&& $this->registerHook('displayBackOfficeHeader'));
	}

	public function uninstall()
	{
		return ($this->removeOverloadedFiles()
				&& parent::uninstall()
				&& $this->clearCache());
	}

	public function moveFiles()
	{
		Tools::copy(_PS_MODULE_DIR_.$this->name.'/override/controllers/front/CategoryController.php', _PS_OVERRIDE_DIR_.'controllers/front/listing/CategoryController.php');
		Tools::copy(_PS_MODULE_DIR_.$this->name.'/override/controllers/front/ManufacturerController.php', _PS_OVERRIDE_DIR_.'controllers/front/listing/ManufacturerController.php');
		Tools::copy(_PS_MODULE_DIR_.$this->name.'/override/controllers/front/SupplierController.php', _PS_OVERRIDE_DIR_.'controllers/front/listing/SupplierController.php');
		return true;
	}

	private function clearCache()
	{
		$this->_clearCache('*');
		if (file_exists(_PS_CACHE_DIR_.'class_index.php'))
		(rename(_PS_CACHE_DIR_.'class_index.php', _PS_CACHE_DIR_.rand(pow(10, 3 - 1), pow(10, 3) - 1).'__class_index.php'));
		if (file_exists(_PS_CACHE_DIR_.'class_stub.php'))
		(rename(_PS_CACHE_DIR_.'class_stub.php', _PS_CACHE_DIR_.rand(pow(10, 3 - 1), pow(10, 3) - 1).'__class_stub.php'));
		return true;
	}

	public function removeOverloadedFiles()
	{
		unlink(_PS_OVERRIDE_DIR_.'classes/Dispatcher.php');
		unlink(_PS_OVERRIDE_DIR_.'classes/Link.php');
		unlink(_PS_OVERRIDE_DIR_.'controllers/front/listing/SupplierController.php');
		unlink(_PS_OVERRIDE_DIR_.'controllers/front/listing/ManufacturerController.php');
		unlink(_PS_OVERRIDE_DIR_.'controllers/front/listing/CategoryController.php');
		return true;
	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'views/css/admin.css');
	}
}