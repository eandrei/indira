<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


if (version_compare(_PS_VERSION_, '1.7', '>=')) {
	class AwoCouponParentFrontController extends ModuleFrontController  {
		public function setTemplate($template, $params = array(), $locale = NULL) {
			if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template;
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
				if (Tools::file_exists_cache($file)) $this->template = $file;
				else parent::setTemplate($template, $params, $locale);
			}
		}
		
		public function setMedia() {
			parent::setMedia();
			$this->addCSS(__PS_BASE_URI__.'modules/awocoupon/media/css/style.css');
			$this->addjQueryPlugin(array('fancybox'));
			$this->addJS(__PS_BASE_URI__.'modules/awocoupon/media/js/modalpopup.js');
		}

	}
}
else {
	class AwoCouponParentFrontController extends ModuleFrontController  {
		public function setTemplate($template) {
			if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template;
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
				if (Tools::file_exists_cache($file)) $this->template = $file;
				else parent::setTemplate($template);
			}
		}
		
		public function setMedia() {
			parent::setMedia();
			$this->addCSS(__PS_BASE_URI__.'modules/awocoupon/media/css/style.css');
			$this->addjQueryPlugin(array('fancybox'));
			$this->addJS(__PS_BASE_URI__.'modules/awocoupon/media/js/modalpopup.js');
		}

	}
}
