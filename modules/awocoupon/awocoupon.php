<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

class AwoCoupon extends Module
{

	public static $definition = array();

	public function __construct()
	{
		$this->name = 'awocoupon';
		$this->tab	= 'pricing_promotion';
		
		$config_file = dirname(__FILE__).'/config.xml';
		preg_match('/\<version\>\<\!\[CDATA\[(.*?)\]\]\>\<\/version\>/', file_get_contents(dirname(__FILE__).'/config.xml'), $match);
		$this->version = @$match[1];
		$this->author = 'Seyi Awofadeju';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('AwoCoupon');
		$this->description = $this->l('Better coupons');
		
		if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}
	

	public function hookPaymentConfirm($params)  { if (version_compare(_PS_VERSION_, '1.5', '<')) return $this->hookPaymentConfirm($params); }
	public function hookActionPaymentConfirmation($params) { 
		require_once _PS_MODULE_DIR_.'awocoupon/lib/giftcerthandler.php';
		AwoCouponGiftcertHandler::process($params);
	}
	
	public function hookCart($params) { if (version_compare(_PS_VERSION_, '1.5', '<')) return $this->hookActionCartSave($params); }
	public function hookActionCartSave($params) {
	
		if(empty($params['cart'])) return;
		$cart = $params['cart'];
		
		if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_autocoupon($cart);

		return;
	}
	
	public function hookCustomerAccount($params) {
		global $smarty, $cookie;
		
		$user_id = (int)(_PS_VERSION_ < '1.5' ? $cookie->id_customer : $this->context->customer->id);

		if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

		if (_PS_VERSION_ < '1.5') 
		{
			$smarty->assign(array(
				'awocouponlink'=>__PS_BASE_URI__.'/modules/awocoupon/account.php',
				'awocouponimg'=>AWO_URI.'/media/img/front-icon-16.png',
			));
		}
		else 
		{
			$smarty->assign(array(
				'awocouponlink'=>Context::getContext()->link->getModuleLink('awocoupon', 'coupons'),
				'awocouponimg'=>AWO_URI.'/media/img/front-icon-32.png',
			));
		}
		return $this->displayawo(__FILE__, '_hookCustomerAccount.tpl');
		//return $this->fetchTemplate('/views/'.(version_compare(_PS_VERSION_,'1.6','>=') ? '16' : '15').'/front/', '_hookCustomerAccount');
	}
	
	public function hookAdminOrder($params) { if (version_compare(_PS_VERSION_, '1.5', '<')) return $this->hookDisplayAdminOrder($params); }
	public function hookDisplayAdminOrder($params) {
		if (version_compare(_PS_VERSION_, '1.5', '<')) return;
		if (!class_exists('AwoCouponHookDisplayAdminOrder'))  require _PS_MODULE_DIR_.'awocoupon/lib/hooks/displayAdminOrder.php';
		if(AwoCouponHookDisplayAdminOrder::execute($params))
			return  $this->fetchTemplate('/views/'.(_PS_VERSION_ >= '1.6' ? '16' : '15').'/admin/', '_hookAdminOrder');
	}
	
	public function hookNewOrder($params) { if (version_compare(_PS_VERSION_, '1.5', '<')) return $this->hookActionValidateOrder($params); }
	public function hookActionValidateOrder($params) { 
		if (!class_exists('AwoCouponHookActionValidateOrder'))  require _PS_MODULE_DIR_.'awocoupon/lib/hooks/actionValidateOrder.php';
		AwoCouponHookActionValidateOrder::execute($params);
	}
	
	public function hookActionCartSummary($params) { 
		if (version_compare(_PS_VERSION_, '1.7', '<')) return;
		if (!class_exists('AwoCouponHookActionCartSummary'))  require _PS_MODULE_DIR_.'awocoupon/lib/hooks/actionCartSummary.php';
		return AwoCouponHookActionCartSummary::execute($params);
	}
	

	
	
	
	
	
	
	
	
	public function install()
	{
		if (!parent::install()) return false;
		
		if (!class_exists('AwoCouponInstaller')) require _PS_MODULE_DIR_.'awocoupon/lib/installer.php';
		$installer = new AwoCouponInstaller();
		return $installer->install($this);
		
	}

	public function uninstall()
	{
		if (!parent::uninstall()) return false;

		if (!class_exists('AwoCouponInstaller')) require _PS_MODULE_DIR_.'awocoupon/lib/installer.php';
		$installer = new AwoCouponInstaller();
		return $installer->uninstall($this);
	}


	public function version_mismatch($old_version)
	{
		if (!class_exists('AwoCouponInstaller')) require _PS_MODULE_DIR_.'awocoupon/lib/installer.php';
		$installer = new AwoCouponInstaller();
		return $installer->upgrade($old_version);

	}





	
	
	public function fetchTemplate($path, $name)
	{
		global $smarty;
		return $smarty->fetch(dirname(__FILE__).$path.$name.'.tpl'); 
	}
	public function displayawo($file, $template, $cacheId = null, $compileId = null)
	{
		$module_name = basename($file, '.php');
		$overloaded = Module::_isTemplateOverloadedStatic(basename($file, '.php'), $template);
		if ($overloaded === null) $overloaded = false;
		
		
		$template_path = $this->getTemplatePath($template);

		if ($template_path === null) 
			return Tools::displayError('No template found for module').' '.$module_name;
		else
		{
			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{

				$this->smarty->assign(array(
					'module_dir' =>				__PS_BASE_URI__.'modules/'.$module_name.'/',
					'module_template_dir' =>	($overloaded ? _THEME_DIR_ : __PS_BASE_URI__).'modules/'.$module_name.'/'
				));
				
				
				if (version_compare(_PS_VERSION_, '1.5.6', '<'))
				{
					$smarty_subtemplate = $this->context->smarty->createTemplate($this->getTemplatePath($template), $cacheId, $compileId, $this->smarty);
					$result = $smarty_subtemplate->fetch();
				}
				else
				{
					if ($cacheId !== null) Tools::enableCache();
					$result = $this->getCurrentSubTemplate($template, $cacheId, $compileId)->fetch();
					if ($cacheId !== null) Tools::restoreCacheSettings();
					$this->resetCurrentSubTemplate($template, $cacheId, $compileId);
				}
			}
			else 
			{
				global $smarty;

				if (Configuration::get('PS_FORCE_SMARTY_2')) /* Keep a backward compatibility for Smarty v2 */
				{
					$previousTemplate = $smarty->currentTemplate;
					$smarty->currentTemplate = substr(basename($template), 0, -4);
				}
				$smarty->assign('module_dir', __PS_BASE_URI__.'modules/'.$module_name.'/');
				$smarty->assign('module_template_dir', ($overloaded ? _THEME_DIR_ : __PS_BASE_URI__).'modules/'.$module_name.'/');
				$result = $smarty->fetch($template_path, $cacheId, $compileId);
				if (Configuration::get('PS_FORCE_SMARTY_2')) /* Keep a backward compatibility for Smarty v2 */
					$smarty->currentTemplate = $previousTemplate;
			}

			return $result;
		}
	}
	public function getTemplatePath($template) 
	{
		$overloaded = $this->_isTemplateOverloaded($template);
		if ($overloaded === null) $overloaded = false;
		if ($overloaded === null)
			return null;
		
		if ($overloaded) 
		{
			if (version_compare(_PS_VERSION_, '1.5', '<')) return _PS_THEME_DIR_.'modules/'.$this->name.'/'.$template;
			return $overloaded;
		}
		else 
		{
			if (version_compare(_PS_VERSION_, '1.5', '<')) $file = _PS_MODULE_DIR_.'awocoupon/ps14/front/tpl/'.$template;
			else 
			{
				if(version_compare(_PS_VERSION_, '1.7', '>=')) {
					$file = _PS_MODULE_DIR_.'awocoupon/views/17/front/'.$template;
				}
				elseif(version_compare(_PS_VERSION_, '1.6', '>=')) {
					$file = _PS_MODULE_DIR_.'awocoupon/views/16/front/'.$template;
				}
				else {
					$file = _PS_MODULE_DIR_.'awocoupon/views/15/front/'.$template;
				}
			}
			if (Tools::file_exists_cache($file)) return $file;
			
			if (version_compare(_PS_VERSION_, '1.5', '<')) return null;
			return parent::getTemplatePath($template);
		}
	}
	
	
	
	
	
}
