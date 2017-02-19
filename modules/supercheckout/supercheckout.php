<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2015 Knowband
 * @license   see file: LICENSE.txt
 * 
 * Description
 * Allow admin to configure module settings for shop.
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once dirname(__FILE__).'/classes/supercheckout_configuration.php';

class Supercheckout extends Module
{
	private $supercheckout_settings = array();
	public $submit_action = 'submit';
	private $custom_errors = array();

	public function __construct()
	{
		$this->name = 'supercheckout';
		$this->tab = 'checkout';
		$this->version = '1.0.2';
		$this->author = 'Knowband';
		$this->need_instance = 0;
		$this->module_key = '68a34cdd0bc05f6305874ea844eefa05';
//		$this->ps_versions_compliancy = array('min' => '1.5.0.1', 'max' <= _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('SuperCheckout');
		$this->description = $this->l('One page Super checkout');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

	}

	public function getErrors()
	{
		return $this->custom_errors;
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install()
			|| !$this->registerHook('displayOrderConfirmation')
			|| !$this->registerHook('displayHeader'))
			return false;

		$create_table = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'velsof_supercheckout_translation` (
            `id_field` int(10) NOT NULL auto_increment,
            `id_lang` int(10) NOT NULL,
            `iso_code` char(4) NOT NULL,
            `key` varchar(255) NOT NULL,
            `key_variable` Text NOT NULL,
            `description` Text NULL,
            PRIMARY KEY (`id_field`),
            INDEX (  `id_lang` )
            ) CHARACTER SET utf8 COLLATE utf8_general_ci';

		Db::getInstance()->execute($create_table);

		$previous_data = array();
		$check_query = 'SELECT * FROM `'._DB_PREFIX_.'velsof_supercheckout_translation`';
		$previous_data = Db::getInstance()->executeS($check_query);
		if (empty($previous_data))
		{
			$languages = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'lang`');
			foreach ($languages as $lang)
			{
				$iso_code = 'en';
				if (file_exists(dirname(__FILE__).'/translations/translation_sql/'.$lang['iso_code'].'.sql'))
					$iso_code = $lang['iso_code'];

$languages = Db::getInstance()->execute('delete FROM `'._DB_PREFIX_.'velsof_supercheckout_translation` where id_lang = '.(int)$lang['id_lang']);
				$sql = Tools::file_get_contents(dirname(__FILE__).'/translations/translation_sql/'.$iso_code.'.sql');
				$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
				$sql = str_replace('ID_LANG', $lang['id_lang'], $sql);
				$sql = str_replace('ISO_CODE', $lang['iso_code'], $sql);

				$sql = preg_split("/[\r\n]+/", $sql);
				array_pop($sql);
				$total_query = count($sql);
				for ($i = 1; $i < $total_query; $i++)
				{
					$ins_query = trim($sql[0].$sql[$i], ',');
					Db::getInstance()->execute(trim($ins_query, ';'));
				}
			}
		}

		if (Configuration::get('VELOCITY_SUPERCHECKOUT'))
			Configuration::deleteByName('VELOCITY_SUPERCHECKOUT');


		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall()
			|| !Configuration::deleteByName('VELOCITY_SUPERCHECKOUT')
			|| !$this->unregisterHook('displayOrderConfirmation')
			|| !$this->unregisterHook('displayHeader'))
			return false;

		if (Configuration::get('VELOCITY_SUPERCHECKOUT_ANALYTIC'))
		{
			$analyticdata = unserialize((Configuration::get('VELOCITY_SUPERCHECKOUT_ANALYTIC')));
			$analyticdata['enable'] = 0;
			Configuration::updateValue('VELOCITY_SUPERCHECKOUT_ANALYTIC', serialize($analyticdata));
		}

		return true;
	}

	public function getContent()
	{
		ini_set('max_input_vars', 2000);
		if (Tools::isSubmit('ajax'))
		{
			if (Tools::isSubmit('tranlationType'))
			{
				switch (Tools::getValue('tranlationType'))
				{
					case 'save':
						{
							$this->saveTranslation();
						}
					case 'saveDownload':
						{
							$this->saveTranslation();
						}
					case 'download':
						{
							$this->generateTmpLanguageFile();
						}
				}
			}
			else if (Tools::isSubmit('method'))
			{
				switch (Tools::getValue('method'))
				{
					case 'validation':
						{
							$this->ajaxHandler();
						}
				}
			}
		}
		else if (Tools::isSubmit('downloadTranslation') && Tools::getValue('downloadTranslation') != '')
		{
			if (Tools::isSubmit('translationTmp'))
				$this->downloadTranslation(Tools::getValue('downloadTranslation'), true);
			else
				$this->downloadTranslation(Tools::getValue('downloadTranslation'));
		}

		$this->addBackOfficeMedia();

		$browser = ($_SERVER['HTTP_USER_AGENT']);
		$is_ie7 = false;
		if (preg_match('/(?i)msie [1-7]/', $browser))
			$is_ie7 = true;

		$output = null;

		$supercheckout_config = new SupercheckoutConfiguration();

		if (Tools::isSubmit($this->submit_action.$this->name))
		{
			$post_data = Tools::getValue('velocity_supercheckout');

			Configuration::updateValue('VELOCITY_SUPERCHECKOUT', serialize($post_data));
			
			if (count($this->custom_errors) > 0)
				$output .= $this->displayError(implode('<br>', $this->custom_errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings has been updated successfully'));
			
		}

		if (!Configuration::get('VELOCITY_SUPERCHECKOUT') || Configuration::get('VELOCITY_SUPERCHECKOUT') == '')
			$this->supercheckout_settings = $supercheckout_config->getDefaultSettings();
		else {
            $setting = unserialize(Configuration::get('VELOCITY_SUPERCHECKOUT'));
            $this->supercheckout_settings = $supercheckout_config->getDefaultSettings();
            if (isset($setting['enable']))
                $this->supercheckout_settings['enable'] = $setting['enable'];
            if (isset($setting['super_test_mode']))
                $this->supercheckout_settings['super_test_mode'] = $setting['super_test_mode'];
        }
			

		
			$custombutton = array('button_color' => 'F77219', 'button_border_color' => 'EC6723',
				'button_text_color' => 'F9F9F9', 'border_bottom_color' => 'C52F2F');
				$paymentdata = array();

		$this->supercheckout_settings['customizer']['button_border_color'] = $custombutton['button_border_color'];
		$this->supercheckout_settings['customizer']['button_color'] = $custombutton['button_color'];
		$this->supercheckout_settings['customizer']['button_text_color'] = $custombutton['button_text_color'];
		$this->supercheckout_settings['customizer']['border_bottom_color'] = $custombutton['border_bottom_color'];
        
        $headerfooterhtml = array('header' => '', 'footer' => '');
		

		$this->supercheckout_settings['html_value']['header'] = $headerfooterhtml['header'];
		$this->supercheckout_settings['html_value']['footer'] = $headerfooterhtml['footer'];

		//Decode Extra Html
		$this->supercheckout_settings['html_value']['header'] = html_entity_decode($this->supercheckout_settings['html_value']['header']);
		$this->supercheckout_settings['html_value']['footer'] = html_entity_decode($this->supercheckout_settings['html_value']['footer']);

		$layout = $this->supercheckout_settings['layout'];

		$payments = array();
		foreach (PaymentModule::getInstalledPaymentModules() as $pay_method)
		{
			if (file_exists(_PS_MODULE_DIR_.$pay_method['name'].'/'.$pay_method['name'].'.php'))
			{
			require_once( _PS_MODULE_DIR_.$pay_method['name'].'/'.$pay_method['name'].'.php' );
			if (class_exists($pay_method['name'], false))
			{
				$temp = array();
				$temp['id_module'] = $pay_method['id_module'];
				$temp['name'] = $pay_method['name'];
				$pay_temp = new $pay_method['name'];
				$temp['display_name'] = $pay_temp->displayName;
				$payments[] = $temp;
			}
			}
		}

		//Get Default Language Variables
		$curr_lang_code = $this->context->language->iso_code;
		$eng_langs = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'velsof_supercheckout_translation` 
			where iso_code = "'.pSQL($curr_lang_code).'"');
		$current_lang_translation = array();
		foreach ($eng_langs as $eng_lang)
		{
			$keys = explode('_', $eng_lang['key']);
			$labels = $keys[count($keys) - 1];
			array_pop($keys);
			$keys = implode('_', $keys);
			$current_lang_translation[$keys][$labels][0] = $eng_lang['key_variable'];
			$current_lang_translation[$keys][$labels][1] = $eng_lang['description'];
		}

		$selected_lang_translation = array();

		if (isset($_REQUEST['velsof_translate_lang']) && $_REQUEST['velsof_translate_lang'] != '')
		{
			$temp_lang = explode('_', $_REQUEST['velsof_translate_lang']);
			$sel_lang_id = $temp_lang[0];
			$curr_lang_code = $temp_lang[1];
			$default_selected_language = $sel_lang_id;
			$sel_langs = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'velsof_supercheckout_translation` 
				where iso_code = "'.pSQL($curr_lang_code).'"');
			if ($sel_langs && count($sel_langs) > 0)
			{
				foreach ($sel_langs as $cur_lang)
				{
					$keys = explode('_', $cur_lang['key']);
					$labels = $keys[count($keys) - 1];
					array_pop($keys);
					$keys = implode('_', $keys);
					$selected_lang_translation[$keys][$labels][0] = $cur_lang['key_variable'];
					$selected_lang_translation[$keys][$labels][1] = $cur_lang['description'];
				}
			}
			else
				$selected_lang_translation = $current_lang_translation;
		}
		else
		{
			$default_selected_language = $this->context->language->id;
			$selected_lang_translation = $current_lang_translation;
		}
		if (_PS_VERSION_ < '1.6.0')
			$lang_img_dir = _PS_IMG_DIR_.'l/';
		else
			$lang_img_dir = _PS_LANG_IMG_DIR_;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			$custom_ssl_var = 1;
		if ((bool)Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1)
		{
			$ps_base_url = _PS_BASE_URL_SSL_;
			$manual_dir = _PS_BASE_URL_SSL_.__PS_BASE_URI__;
		}
		else
		{
			$ps_base_url = _PS_BASE_URL_;
			$manual_dir = _PS_BASE_URL_.__PS_BASE_URI__;
		}

		$this->_clearCache('supercheckout.tpl');
		//AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name,
		$this->smarty->assign(array(
			'root_path' => $this->_path,
			'action' => 'index.php?controller=AdminModules&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name,
			'cancel_action' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'velocity_supercheckout' => $this->supercheckout_settings,
			'highlighted_fields' => array('company', 'address2', 'postcode', 'other', 'phone', 'phone_mobile', 'vat_number', 'dni'),
			'layout' => $layout,
			'manual_dir' => $manual_dir,
			'domain' => $_SERVER['HTTP_HOST'],
			'payment_methods' => $payments,
			'carriers' => Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS),
			'languages' => Language::getLanguages(),
			'submit_action' => $this->submit_action.$this->name,
			'default_selected_language' => $default_selected_language,
			'current_lang_translator_vars' => $current_lang_translation,
			'selected_lang_translator_vars' => $selected_lang_translation,
			'IE7' => $is_ie7,
			'guest_is_enable_from_system' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
			'velocity_supercheckout_payment' => $paymentdata,
			'root_dir' => _PS_ROOT_DIR_,
			'languages' => Language::getLanguages(true),
			'img_lang_dir' => $ps_base_url.__PS_BASE_URI__.str_replace(_PS_ROOT_DIR_.'/', '', $lang_img_dir),
	'module_url' => $this->context->link->getModuleLink('supercheckout', 'supercheckout', array(), (bool)Configuration::get('PS_SSL_ENABLED'))
		));

		//Added to assign current version of prestashop in a new variable
		if (version_compare(_PS_VERSION_, '1.6.0.1', '<'))
			$this->smarty->assign('ps_version', 15);
		else
			$this->smarty->assign('ps_version', 16);

		$output .= $this->display(__FILE__, 'views/templates/admin/supercheckout.tpl');
		return $output;
	}

	/*
	 * Add css and javascript
	 */

	protected function addBackOfficeMedia()
	{
		//CSS files
		$this->context->controller->addCSS($this->_path.'views/css/supercheckout.css');
		$this->context->controller->addCSS($this->_path.'views/css/bootstrap.css');
		$this->context->controller->addCSS($this->_path.'views/css/responsive.css');
		$this->context->controller->addCSS($this->_path.'views/css/jquery-ui/jquery-ui.min.css');
		$this->context->controller->addCSS($this->_path.'views/css/fonts/glyphicons/glyphicons_regular.css');
		$this->context->controller->addCSS($this->_path.'views/css/fonts/font-awesome/font-awesome.min.css');
		$this->context->controller->addCSS($this->_path.'views/css/pixelmatrix-uniform/uniform.default.css');
		$this->context->controller->addCSS($this->_path.'views/css/bootstrap-switch/bootstrap-switch.css');
		$this->context->controller->addCSS($this->_path.'views/css/select2/select2.css');
		$this->context->controller->addCSS($this->_path.'views/css/style-light.css');
		$this->context->controller->addCSS($this->_path.'views/css/bootstrap-select/bootstrap-select.css');
		$this->context->controller->addCSS($this->_path.'views/css/jQRangeSlider/iThing.css');
		$this->context->controller->addCSS($this->_path.'views/css/jquery-miniColors/jquery.miniColors.css');

		$this->context->controller->addJs($this->_path.'views/js/jquery-ui/jquery-ui.min.js');
		$this->context->controller->addJs($this->_path.'views/js/bootstrap.min.js');
		$this->context->controller->addJs($this->_path.'views/js/common.js');
		$this->context->controller->addJs($this->_path.'views/js/system/less.min.js');
		$this->context->controller->addJs($this->_path.'views/js/tinysort/jquery.tinysort.min.js');
		$this->context->controller->addJs($this->_path.'views/js/jquery/jquery.autosize.min.js');
		$this->context->controller->addJs($this->_path.'views/js/uniform/jquery.uniform.min.js');
		$this->context->controller->addJs($this->_path.'views/js/tooltip/tooltip.js');
		$this->context->controller->addJs($this->_path.'views/js/bootbox.js');
		$this->context->controller->addJs($this->_path.'views/js/bootstrap-select/bootstrap-select.js');
		$this->context->controller->addJs($this->_path.'views/js/bootstrap-switch/bootstrap-switch.js');
		$this->context->controller->addJs($this->_path.'views/js/system/jquery.cookie.js');
		$this->context->controller->addJs($this->_path.'views/js/themer.js');
		$this->context->controller->addJs($this->_path.'views/js/admin/jscolor.js');

		$this->context->controller->addJs($this->_path.'views/js/jquery-miniColors/jquery.miniColors.js');

		$this->context->controller->addJs($this->_path.'views/js/supercheckout.js');

		if (!version_compare(_PS_VERSION_, '1.6.0.1', '<'))
			$this->context->controller->addCSS($this->_path.'views/css/supercheckout_16_admin.css');
		else
			$this->context->controller->addCSS($this->_path.'views/css/supercheckout_15_admin.css');
	}

	/*
	 * Handle ajax requests
	 */

	public function ajaxHandler()
	{
		$json = array();
		
				$json['success'] = '1';
		
		echo Tools::jsonEncode($json);
		die;
	}

	/*
	 * Handle ajax requests for language translation
	 */

	public function saveTranslation()
	{
		$data = array('velocity_transalator' => Tools::getValue('velocity_transalator'));
		$temp_var = explode('_', $data['velocity_transalator']['selected_language']);
		$language_id = $temp_var[0];
		$language_iso_code = $temp_var[1];
		$json = array();
		$translation_dir = _PS_MODULE_DIR_.'supercheckout/translations/';
		$file_path = $translation_dir.$language_iso_code.'.php';

		unset($data['velocity_transalator']['selected_language']);

		

		Db::getInstance()->execute('delete FROM `'._DB_PREFIX_.'velsof_supercheckout_translation` where id_lang = '.(int)$language_id);
		foreach ($data['velocity_transalator'] as $key => $lang_label)
		{
			$ins_query = 'INSERT INTO `'._DB_PREFIX_.'velsof_supercheckout_translation` (`id_lang`, `iso_code`, `key`, `key_variable`, `description`) VALUES ';
			if (isset($lang_label['label']))
			{
				$query = '('.(int)$language_id.', \''.pSQL($language_iso_code).'\', \''.pSQL($key).'_label\', \''
					.str_replace("'", "''", pSQL($lang_label['label'][0]))
					.'\', \''.str_replace("'", "''", pSQL($lang_label['label'][1])).'\')';
				Db::getInstance()->execute($ins_query.$query);
			}
			if (isset($lang_label['tooltip']))
			{
				$query = '('.(int)$language_id.', \''.pSQL($language_iso_code).'\', \''.pSQL($key).'_tooltip\', \''
					.str_replace("'", "''", pSQL($lang_label['tooltip'][0]))
					.'\', \''.str_replace("'", "''", pSQL($lang_label['tooltip'][1])).'\')';
				Db::getInstance()->execute($ins_query.$query);
			}
		}

		$json['success'] = $this->l('Language successfully translated');
		if (is_writable($translation_dir))
			$this->generateLanguageFile($file_path, $data);
		else
			$json['error'] = $this->l('Permission errorred occur for language file creating');

		echo Tools::jsonEncode($json);
		die;
	}

	private function generateLanguageFile($file_path, $data)
	{
		$f = fopen($file_path, 'w+');
		fwrite($f, '<?php '.PHP_EOL.PHP_EOL);
		fwrite($f, 'global $_MODULE;'.PHP_EOL);
		fwrite($f, '$_MODULE = array();'.PHP_EOL.PHP_EOL);

		foreach ($data['velocity_transalator'] as $lang_label)
		{
			$template_files = array();
			if (isset($lang_label['label']))
			{
				if (isset($lang_label['label'][2]))
					$template_files = explode('|', $lang_label['label'][2]);
				array_push($template_files, 'supercheckout');
				foreach ($template_files as $template)
				{
				$language = '$_MODULE[\'<{supercheckout}prestashop>'.$template.'_'.md5($lang_label['label'][0]).'\'] = \''
					.strip_tags(addslashes($lang_label['label'][1])).'\';';
				fwrite($f, $language.PHP_EOL);
				}
			}
			$template_files = array();
			if (isset($lang_label['tooltip']))
			{
				if (isset($lang_label['tooltip'][2]))
					$template_files = explode('|', $lang_label['tooltip'][2]);
				array_push($template_files, 'supercheckout');
				foreach ($template_files as $template)
				{
				$language = '$_MODULE[\'<{supercheckout}prestashop>'.$template.'_'.md5($lang_label['tooltip'][0]).'\'] = \''
					.strip_tags(addslashes($lang_label['tooltip'][1])).'\'; //'.$lang_label['tooltip'][0];
				fwrite($f, $language.PHP_EOL);
				}
			}
		}

		fwrite($f, PHP_EOL);
		fwrite($f, 'return $_MODULE;');
		fclose($f);
	}

	private function generateTmpLanguageFile()
	{
		$data = array('velocity_transalator' => Tools::getValue('velocity_transalator'));
		$temp_var = explode('_', $data['velocity_transalator']['selected_language']);
		$language_iso_code = $temp_var[1];
		unset($data['velocity_transalator']['selected_language']);

		$json = array();
		$translation_dir = _PS_MODULE_DIR_.'supercheckout/translations/tmp/';
		$file_path = $translation_dir.$language_iso_code.'.php';

		if (is_writable($translation_dir))
		{
			$this->generateLanguageFile($file_path, $data, $language_iso_code);
			$json['success'] = $language_iso_code;
		}
		else
			$json['error'] = $this->l('Permission errorred occur for language file creating');

		echo Tools::jsonEncode($json);
		die;
	}

	private function downloadTranslation($file_name, $is_tmp = false)
	{
		$translation_dir = _PS_MODULE_DIR_.'supercheckout/translations/';
		if ($is_tmp)
			$translation_dir .= 'tmp/';
		$file = $translation_dir.$file_name.'.php';
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
		header('Content-Length: '.filesize($file));
		readfile($file);
		exit;
	}

	

	/*
	 * Creae log of all scratch coupon activity on front end
	 */

	private function writeLog($type, $msg)
	{
		$f = fopen('log.txt', 'a+');
		fwrite($f, $type."\t".date('m-d-Y H:i:s', time())."\t".$msg);
		fwrite($f, "\n");
		fclose($f);
	}

	public function hookDisplayHeader()
	{
		$settings = array();
		$supercheckout_config = new SupercheckoutConfiguration();
		if (!Configuration::get('VELOCITY_SUPERCHECKOUT') || Configuration::get('VELOCITY_SUPERCHECKOUT') == '')
			$settings = $supercheckout_config->getDefaultSettings();
		else
			$settings = unserialize(Configuration::get('VELOCITY_SUPERCHECKOUT'));
		if (!Tools::getValue('klarna_supercheckout'))
		{
			if (isset($settings['super_test_mode']) && $settings['super_test_mode'] != 1)
			{
				if ($this->context->smarty->tpl_vars['page_name']->value == 'order-opc' || $this->context->smarty->tpl_vars['page_name']->value == 'order')
				{
					if ($settings['enable'] == 1)
					{
						$current_page_url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
						$query_string = parse_url($current_page_url);
						$query_params = array();
						if (isset($query_string['query']))
                        {
                            parse_str($query_string['query'], $query_params);
                            if (isset($query_params['isPaymentStep']))
                                unset($query_params['isPaymentStep']);
                        }
						//Tools::redirect($this->context->link->getModuleLink($this->name, $this->name, $query_params, (bool)Configuration::get('PS_SSL_ENABLED')));
					}
				}
			}
		}

		if (Configuration::get('VELOCITY_SUPERCHECKOUT_CSS') || Configuration::get('VELOCITY_SUPERCHECKOUT_CSS') != '')
		{
			$settings['custom_css'] = unserialize((Configuration::get('VELOCITY_SUPERCHECKOUT_CSS')));
			$settings['custom_css'] = urldecode($settings['custom_css']);
		}

		if (Configuration::get('VELOCITY_SUPERCHECKOUT_JS') || Configuration::get('VELOCITY_SUPERCHECKOUT_JS') != '')
		{
			$settings['custom_js'] = unserialize((Configuration::get('VELOCITY_SUPERCHECKOUT_JS')));
			$settings['custom_js'] = urldecode($settings['custom_js']);
		}

		if (isset($settings['custom_css']))
		$this->smarty->assign($settings['custom_css']); //return '<style type="text/css">'.$settings['custom_css'].'</style>';

		if (isset($settings['custom_js']))
		$this->smarty->assign($settings['custom_js']);
	}

	public function hookDisplayOrderConfirmation($params = null)
	{
		if (Configuration::get('PACZKAWRUCHU_CARRIER_ID'))
		{
			$carrier = Configuration::get('PACZKAWRUCHU_CARRIER_ID');
			$order_carrier_id = $params['objOrder']->id_carrier;
			$cart_id = $params['objOrder']->id_cart;
			if ($order_carrier_id != $carrier)
			{
				$delete_query = 'delete from `'._DB_PREFIX_.'paczkawruchu` WHERE id_cart='.(int)$cart_id;
				Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($delete_query);
			}
		}
		unset($params);
		if (isset($this->context->cookie->supercheckout_temp_address_delivery) && $this->context->cookie->supercheckout_temp_address_delivery)
		{
			if ($this->context->cookie->supercheckout_temp_address_delivery != $this->context->cookie->supercheckout_perm_address_delivery)
				Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('delete from '._DB_PREFIX_.'address 
					where id_address = '.(int)$this->context->cookie->supercheckout_temp_address_delivery);
			$this->context->cookie->supercheckout_temp_address_delivery = 0;
			$this->context->cookie->__unset($this->context->cookie->supercheckout_temp_address_delivery);
		}
		if (isset($this->context->cookie->supercheckout_temp_address_invoice) && $this->context->cookie->supercheckout_temp_address_invoice)
		{
			if ($this->context->cookie->supercheckout_temp_address_invoice != $this->context->cookie->supercheckout_perm_address_invoice)
				Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('delete from '._DB_PREFIX_.'address 
					where id_address = '.(int)$this->context->cookie->supercheckout_temp_address_invoice);
			$this->context->cookie->supercheckout_temp_address_invoice = 0;
			$this->context->cookie->__unset($this->context->cookie->supercheckout_temp_address_invoice);
		}
		$this->context->cookie->supercheckout_perm_address_delivery = 0;
		$this->context->cookie->__unset($this->context->cookie->supercheckout_perm_address_delivery);
		$this->context->cookie->supercheckout_perm_address_invoice = 0;
		$this->context->cookie->__unset($this->context->cookie->supercheckout_perm_address_invoice);
	}

	

	
}
