<?php
/**
 * This file is part of module Braintree
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

if (!defined('_PS_VERSION_'))
	exit;

class Braintreejs extends PaymentModule
{
	protected $backward = false;
	const ENV_SANDBOX = 'sandbox';
	const ENV_PRODUCTION = 'production';

	public function __construct()
	{
		$this->name = 'braintreejs';
		$this->tab = 'payments_gateways';
		$this->version = '2.1.0';
		$this->author = 'Bellini Services';
		$this->need_instance = 1;
		$this->module_key = '24e3bbe49a1980178d83d9da1a6c7913';

		parent::__construct();

		$this->displayName = $this->l('Braintree');
		$this->description = $this->l('Accept payments by Credit Card with Braintree (Visa, Mastercard, Amex, Discover and Diners Club)');
		$this->confirmUninstall = $this->l('Warning: All transaction information recorded in your Prestashop database will be deleted. Are you sure you want uninstall this module?');

		if (!$this->checkSettings())
			$this->warning = $this->l('You must complete the configuration of the Braintree module before it will function properly.');

		$this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, the Braintree module requires the backward compatibility module at least v0.4.').'<br />'.
			$this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');

		// Backward compatibility
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
			$this->backward = true;
		}
		else
			$this->backward = true;

		if (self::isInstalled($this->name))
			$this->loadDefaults();

	}

	/**
	 * Braintree's module installation
	 *
	 * @return boolean Install result
	 */
	public function install()
	{
		if (!$this->backward && _PS_VERSION_ < 1.5)
		{
			echo '<div class="error">'.Tools::safeOutput($this->backward_error).'</div>';
			return false;
		}

		/* For 1.4.3 and less compatibility */
		$update_config = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($update_config as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		$ret = parent::install();
		if (!$ret)
			return false;

		//install module hooks
		$ret = $this->installHooks();
		if (!$ret)
			return false;

		//update configs
		$ret = $this->installConfiguration();
		if (!$ret)
			return false;

		//install database tables
		$ret = $this->installDb();
		if (!$ret)
			return false;

		return $ret;
	}

	/**
	 * Register all hooks that the module will use
	 *
	 * @return boolean result
	 */
	public function installHooks()
	{
		//global hooks apply to all PS versions
		$ret = $this->registerHook('payment') && $this->registerHook('header') && $this->registerHook('orderConfirmation');

		//add hooks based on version of Prestashop
		//PS v1.6+ 
		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$ret = $this->registerHook('adminOrder');
			$ret = $this->registerHook('displayPaymentEU');
		}

		//PS v1.5+ 
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$ret = $this->registerHook('actionOrderStatusPostUpdate');	//1.5+
			$ret = $this->registerHook('displayMobileHeader');	//1.5+
		}

		//PS v1.4 only 
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$ret = $this->registerHook('postUpdateOrderStatus');	//1.4 only

		//PS v1.4 and v1.5 only 
		if (version_compare(_PS_VERSION_, '1.4', '>=') && version_compare(_PS_VERSION_, '1.6', '<'))
			$ret = $this->registerHook('backOfficeHeader'); //1.4 and 1.5

		return $ret;
	}

	/**
	 * Install the modules configuration
	 *
	 * @return boolean result
	 */
	public function installConfiguration()
	{
		$ret = true;

		$default_config = Configuration::getMultiple(array('PS_OS_PAYMENT', 'PS_OS_ERROR'));

		$bt_config = array(
			'BRAINTREE_MODE' => 0,	//0=test, 1=live
			'BRAINTREE_PAYMENT_ORDER_STATUS' => (int)$default_config['PS_OS_PAYMENT'],	//default order status to use
			'BRAINTREE_PENDING_ORDER_STATUS' => (int)$default_config['PS_OS_PAYMENT'],	//order status to use when their are AVS or CVV check failures
			'BRAINTREE_SETTLEMENT_PENDING_OS' => (int)$default_config['PS_OS_ERROR'],	//Order status to use when Paypal authorization succeeds, but Settlement is Pending
			'BRAINTREE_SUBMIT_SETTLE' => 1,	//Submit for Settlement when transaction is created. 0=No, 1=Yes
			'BRAINTREE_PAYPAL_ENABLED' => 0,	//should we accept paypal. 0=No, 1=Yes
			'BRAINTREE_PAYPAL_FUTURE' => 0,	//if paypal is accepted, should we allow storage of the paypal account.  0=No, 1=Yes
			'BRAINTREE_LOG_TRANSACTIONS' => 0,	//record all transactions in the log file.  0=No, 1=Yes
			'BRAINTREE_VERSION' => $this->version,	//the current version of the installed module
			'BRAINTREE_UI_MODE' => 1,	//default to Dropin UI for new installs. 0=hosted fields, 1=dropin
			'BRAINTREE_CHECKOUT_MODE' => 0,	//default to inline for new installs. 0=inline, 1=dedicated page
			'BRAINTREE_3DS' => 0,	//default to off for new installs. 0=off, 1=on
			'BRAINTREE_HOSTED_POSTCODE' => 0,	//default to off for new installs. 0=off, 1=on

			/* new configuration parameters to control display of card logos during dedicated page checkout.  The logos will appear in the payment method selection list */
			'BRAINTREE_CARD_VISA' => 1,
			'BRAINTREE_CARD_MASTERCARD' => 1,
			'BRAINTREE_CARD_AMEX' => 1,
			'BRAINTREE_CARD_JCB' => 0,
			'BRAINTREE_CARD_DISCOVER' => 0,
			'BRAINTREE_CARD_DINERS' => 0,
			'BRAINTREE_CARD_MAESTRO' => 0,

			//not currently used
			'BRAINTREE_SAVE_TOKENS' => 0,	//not currently used, intention is for storing cards in the vault
			'BRAINTREE_SAVE_TOKENS_ASK' => 0,	//not currently used, intention is for asking the customer if they want their card stored in the vault
			'BRAINTREE_CHARGEBACK_ORDERSTATUS' => (int)$default_config['PS_OS_ERROR'],	//not currently used, intention is if webhooks are implemented 
			'BRAINTREE_WEBHOOK_TOKEN' => md5(Tools::passwdGen()),	//not currently used, intention is if webhooks are implemented 
		);	

		foreach ($bt_config as $u => $v)
		{
			$ret = Configuration::updateValue($u, $v);
			if (!$ret)
				return false;
		}

		if (!Configuration::get('BRAINTREE_UUID'))
		{
			$uuid = Tools::substr(md5(Tools::passwdGen()), 0, 20);
			$ret = Configuration::updateValue('BRAINTREE_UUID', $uuid);
		}

		return $ret;
	}

	/**
	 * Braintree's module database tables installation
	 *
	 * @return boolean Database tables installation result
	 */
	public function installDb()
	{
		return Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'braintree_transaction` (
				`id_braintree_transaction` int(11) NOT NULL AUTO_INCREMENT,
				`type` enum(\'payment\',\'refund\') NOT NULL, 
				`id_braintree_customer` int(10) unsigned NOT NULL, 
				`id_cart` int(10) unsigned NOT NULL,
				`id_order` int(10) unsigned NOT NULL, 
				`id_transaction` varchar(32) NOT NULL, 
				`amount` decimal(10,2) NOT NULL, 
				`status` enum(\'paid\',\'unpaid\') NOT NULL,
				`currency` varchar(3) NOT NULL, 
				`cvc_check` tinyint(1) NOT NULL DEFAULT \'0\',
				`line1_check` tinyint(1) NOT NULL DEFAULT \'0\',
				`zip_check` tinyint(1) NOT NULL DEFAULT \'0\',
				`avs_check` tinyint(1) NOT NULL DEFAULT \'0\', 
				`mode` enum(\'live\',\'test\') NOT NULL,
				`date_add` datetime NOT NULL, 
				`payment_instrument_type` varchar(255) NOT NULL DEFAULT \'credit_card\',
				PRIMARY KEY (`id_braintree_transaction`), 
				KEY `idx_transaction` (`type`,`id_order`,`status`),
				KEY `payment_instrument_type` (`payment_instrument_type`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
	}

	/**
	 * Launch upgrade process
	 */
	public function runUpgrades()
	{
		//we only need to execute if we are PS v1.4, as PS v1.5+ includes upgrade functionality automatically.
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			return;

		//represents the module version, for each upgrade file created, we need to include that version number in the array
		foreach (array('1.13', '1.14', '1.16', '1.18', '1.19', '1.20', '1.22', '1.24', '1.26.0', '1.27.0', '2.0.0') as $version)
		{
			$file = dirname(__FILE__).'/upgrade/install-'.$version.'.php';
			if (Configuration::get('BRAINTREE_VERSION') < $version && file_exists($file))
			{
				include_once($file);
				call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
			}
		}
	}

	/**
	 * Initialize default values
	 */
	protected function loadDefaults()
	{
		//only execute if the module was loaded from the back office
		if (defined('_PS_ADMIN_DIR_'))
		{
			/* Upgrade and compatibility checks */
			$this->runUpgrades();
		}
	}

	/**
	 * Braintree's module uninstallation (Configuration values, database tables...)
	 *
	 * @return boolean Uninstall result
	 */
	public function uninstall()
	{
		return parent::uninstall() && Configuration::deleteByName('BRAINTREE_VERSION') && Configuration::deleteByName('BRAINTREE_SUBMIT_SETTLE') && Configuration::deleteByName('BRAINTREE_CLIENTSIDE_TEST') && Configuration::deleteByName('BRAINTREE_CLIENTSIDE_LIVE') && Configuration::deleteByName('BRAINTREE_MERCHANTID_TEST') && Configuration::deleteByName('BRAINTREE_MERCHANTID_LIVE') && Configuration::deleteByName('BRAINTREE_PUBLIC_KEY_TEST') && Configuration::deleteByName('BRAINTREE_PUBLIC_KEY_LIVE') && Configuration::deleteByName('BRAINTREE_MODE') && Configuration::deleteByName('BRAINTREE_PRIVATE_KEY_TEST') && Configuration::deleteByName('BRAINTREE_PRIVATE_KEY_LIVE') && Configuration::deleteByName('BRAINTREE_SAVE_TOKENS') && Configuration::deleteByName('BRAINTREE_SAVE_TOKENS_ASK') && Configuration::deleteByName('BRAINTREE_CHARGEBACK_ORDERSTATUS') && Configuration::deleteByName('BRAINTREE_PENDING_ORDER_STATUS') && Configuration::deleteByName('BRAINTREE_PAYMENT_ORDER_STATUS') && Configuration::deleteByName('BRAINTREE_WEBHOOK_TOKEN') && Configuration::deleteByName('BRAINTREE_PAYPAL_ENABLED') && Configuration::deleteByName('BRAINTREE_PAYPAL_FUTURE') && Configuration::deleteByName('BRAINTREE_LOG_TRANSACTIONS') && Configuration::deleteByName('BRAINTREE_SETTLEMENT_PENDING_OS') && Configuration::deleteByName('BRAINTREE_UI_MODE') && Configuration::deleteByName('BRAINTREE_CARD_VISA') && Configuration::deleteByName('BRAINTREE_CARD_MASTERCARD') && Configuration::deleteByName('BRAINTREE_CARD_AMEX') && Configuration::deleteByName('BRAINTREE_CARD_JCB') && Configuration::deleteByName('BRAINTREE_CARD_DISCOVER') && Configuration::deleteByName('BRAINTREE_CARD_DINERS') && Configuration::deleteByName('BRAINTREE_CHECKOUT_MODE') && Configuration::deleteByName('BRAINTREE_3DS') && Configuration::deleteByName('BRAINTREE_HOSTED_POSTCODE') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'braintree_transaction`');
	}

	public function hookDisplayMobileHeader()
	{
		return $this->hookHeader();
	}

	/**
	 * Load Javascripts and CSS related to the Braintree's module
	 * Only loaded during the checkout process
	 *
	 * @return string HTML/JS Content
	 */
	public function hookHeader()
	{
		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return;

		if (!$this->checkSettings())
			return;

		$braintree_checkout_mode = Configuration::get('BRAINTREE_CHECKOUT_MODE');
		//should be one of the following 'order', 'order-opc', 'orderopc', or 'advancedpayment'
		$controller = Tools::getValue('controller');
		$allowJs = false;
		$allowCss = false;

		//1. allow to proceed if we are in one page checkout
		if ($controller && ($controller == 'order-opc' || $controller == 'orderopc'))
			$allowJs = $allowCss = true;

		//2. if we are in 5 page checkout, then process only if we are on the payment step and checkout mode is inline
		if ($controller && $controller == 'order' && Tools::getValue('step') == 3 && $braintree_checkout_mode == 0)
			$allowJs = $allowCss = true;
		
		//3. if we are in 5 page checkout, on payment step 3, in dedicated mode and there is an error, then include CSS
		if ($controller && $controller == 'order' && Tools::getValue('step') == 3 && $braintree_checkout_mode == 1 && Tools::getValue('bt_error') == 1)
			$allowCss = true;

		//4. if we are in dedicated page mode, then process if the module is braintree and the controller is payment
		if ($controller && $controller == 'payment' && Tools::getValue('module') == 'braintreejs' && $braintree_checkout_mode == 1)
			$allowJs = $allowCss = true;

		//5. if we are in advancedpayment page mode, then process if the module is braintreejs and the controller is advancedpayment (ps 1.6.1+ only)
		if ($controller && $controller == 'advancedpayment' && Tools::getValue('module') == 'braintreejs')
			$allowJs = $allowCss = true;

		//todo: For PS 1.4, we need to add other checks
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			// 'controller' parameter does not exist in PS v1.4, so we look at the base uri instead

			//1. allow to proceed if we are in one page checkout
			if ($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order-opc.php')
				$allowJs = $allowCss = true;

			//2. allow to proceed if we are in one page checkout with friendly url
			if ($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'quick-order')
				$allowJs = $allowCss = true;

			//3. if we are in 5 page checkout, then process only if we are on the payment step and checkout mode is inline
			if ($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order.php' && Tools::getValue('step') == 3 && $braintree_checkout_mode == 0)
				$allowJs = $allowCss = true;

			//4. if we are in 5 page checkout, on payment step 3, in dedicated mode and there is an error, then include CSS
			if ($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order.php' && Tools::getValue('step') == 3 && $braintree_checkout_mode == 1 && Tools::getValue('bt_error') == 1)
				$allowCss = true;

			//5. if we are in dedicated page mode, then process if the module is braintree and the controller is payment
			if ($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'modules/braintreejs/payment.php' && $braintree_checkout_mode == 1)
				$allowJs = $allowCss = true;
		}

		// Load CSS files through CCC
		if ($allowCss)
			$this->context->controller->addCSS($this->_path.'views/css/braintree-prestashop.css');

		if (!$allowJs)
			return;

		return '<script type="text/javascript" src="https://js.braintreegateway.com/js/braintree-2.26.0.min.js"></script>';
	}

	/**
	 * Display the Braintree's payment form
	 *
	 * @return string Braintree's Smarty template content
	 */
	public function hookPayment($params)
	{
		//this is used to bypass meaningless validator rules, since params parameter is never used
		$params = $params;
		
		// If 1.4 and no backward then leave
		if (!$this->backward)
			return;

		if (!$this->checkSettings())
			return;

		if (!Validate::isLoadedObject($this->context->customer))
			return;

		$config = Configuration::getMultiple(array('BRAINTREE_CHECKOUT_MODE', 'BRAINTREE_CARD_VISA', 'BRAINTREE_CARD_MASTERCARD', 'BRAINTREE_CARD_AMEX', 'BRAINTREE_CARD_JCB', 'BRAINTREE_CARD_DISCOVER', 'BRAINTREE_CARD_DINERS', 'BRAINTREE_CARD_MAESTRO', 'PS_SSL_ENABLED', 'BRAINTREE_PAYPAL_ENABLED'));

		$braintree_checkout_mode = $config['BRAINTREE_CHECKOUT_MODE'];
		$braintree_paypal_enabled = $config['BRAINTREE_PAYPAL_ENABLED'];

		//if we are in dedicated page mode, then we only need to show a new payment option
		if ($braintree_checkout_mode == 1)
		{
			$braintree_pay_url = $this->getModuleLink('braintreejs', 'payment', array(), $config['PS_SSL_ENABLED']);

			$cards = array();
			$cards['visa'] = $config['BRAINTREE_CARD_VISA'];
			$cards['mastercard'] = $config['BRAINTREE_CARD_MASTERCARD'];
			$cards['amex'] = $config['BRAINTREE_CARD_AMEX'];
			$cards['jcb'] = $config['BRAINTREE_CARD_JCB'];
			$cards['discover'] = $config['BRAINTREE_CARD_DISCOVER'];
			$cards['diners'] = $config['BRAINTREE_CARD_DINERS'];
			$cards['maestro'] = $config['BRAINTREE_CARD_MAESTRO'];

			$buttonText = $this->l('Pay by Credit Card');
			if ($braintree_paypal_enabled == 1)
				$buttonText .= $this->l(' or Paypal');

			$this->context->smarty->assign(array(
				'braintree_cards' => $cards,
				'braintree_pay_url' => $braintree_pay_url,
				'buttonText' => $buttonText,
			));

			if (version_compare(_PS_VERSION_, '1.5', '<'))
				$this->context->smarty->assign('this_path_bt', $this->_path);
			else
				$this->context->smarty->assign('this_path_bt', $this->getPathUri());

			return $this->fetchTemplate('payment_inline.tpl');
		}
		//otherwise if we are in inline mode, then we need to show the payment form
		else
			return $this->displayPaymentForm(0, true);	//we are in inline mode, so we need content returned
	}

	public function hookDisplayPaymentEU($params)
	{
		if (!$this->active)
			return;

		$payment_options = array(
			'cta_text' => $this->l('Pay by Credit Card or Paypal'),
			'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'),
			'action' => $this->context->link->getModuleLink($this->name, 'advancedpayment',	array(), true),
		);

		return $payment_options;
	}

	public function displayPaymentForm($is_dedicated_page, $return_content)
	{
		// If 1.4 and no backward then leave
		if (!$this->backward)
			return;

		if (!$this->checkSettings())
			return;

		$config = Configuration::getMultiple(array('BRAINTREE_MODE', 'BRAINTREE_LOG_TRANSACTIONS', 'BRAINTREE_MERCHANTID_LIVE', 'BRAINTREE_MERCHANTID_TEST', 'BRAINTREE_CLIENTSIDE_LIVE', 'BRAINTREE_CLIENTSIDE_TEST', 'BRAINTREE_PAYPAL_ENABLED', 'BRAINTREE_PAYPAL_FUTURE', 'BRAINTREE_UI_MODE', 'BRAINTREE_UUID', 'BRAINTREE_CARD_VISA', 'BRAINTREE_CARD_MASTERCARD', 'BRAINTREE_CARD_AMEX', 'BRAINTREE_CARD_JCB', 'BRAINTREE_CARD_DISCOVER', 'BRAINTREE_CARD_DINERS', 'BRAINTREE_CARD_MAESTRO', 'BRAINTREE_3DS', 'BRAINTREE_HOSTED_POSTCODE'));

		//the environment is based on the global configuration since this is a new transaction
		$bt_env = $config['BRAINTREE_MODE'] ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
		$this->setupBraintreeEnv($bt_env);

		$bt_data_env = $config['BRAINTREE_MODE'] ? 'production' : 'sandbox';
		$merchant_id = $config['BRAINTREE_MODE'] ? $config['BRAINTREE_MERCHANTID_LIVE'] : $config['BRAINTREE_MERCHANTID_TEST'];
		$braintree_paypal_enabled = $config['BRAINTREE_PAYPAL_ENABLED'] ? 1 : 0;
		$braintree_paypal_future = $config['BRAINTREE_PAYPAL_FUTURE'] ? 1 : 0;
		$braintree_dropinui_enabled = $config['BRAINTREE_UI_MODE'] ? 1 : 0;
		$braintree_3ds = $config['BRAINTREE_3DS'] ? 1 : 0;
		$braintree_use_postcode = $config['BRAINTREE_HOSTED_POSTCODE'] ? 1 : 0;

		//3DS only supported when dropin ui is disabled
		if ($braintree_dropinui_enabled) 
			$braintree_3ds = false;

		//only execute if we have a valid customer
		$customerFound = false;
		try
		{
			$customer = Braintree_Customer::find($config['BRAINTREE_UUID'].'__'.$this->context->customer->id);
			$this->recordTransaction($customer, $this->context->cart->id, 'findcustomer');
			$customerFound = true;
		}
		catch (Braintree_Exception_NotFound $e)
		{
			//options used to create the customer
			$options = array(
				'id' => $config['BRAINTREE_UUID'].'__'.$this->context->customer->id,
				'firstName' => $this->context->customer->firstname,
				'lastName' => $this->context->customer->lastname,
				'email' => $this->context->customer->email,
			);

			$this->recordTransaction($options, $this->context->cart->id, 'createcustomerrequest');
			$result = Braintree_Customer::create($options);
			$this->recordTransaction($result, $this->context->cart->id, 'createcustomerresponse');

			if ($result->success)
				$customerFound = true;
		}

		$currency_iso_code = $this->context->currency->iso_code;
		$merchant_account_id = Configuration::get('BRAINTREE_MODE') ? Configuration::get('BRAINTREE_CURRENCY_LIVE_'.$currency_iso_code) : Configuration::get('BRAINTREE_CURRENCY_TEST_'.$currency_iso_code);

		if ($customerFound)
		{
			$clientToken = Braintree_ClientToken::generate(array(
				'customerId' => $config['BRAINTREE_UUID'].'__'.$this->context->customer->id,
				'merchantAccountId' => $merchant_account_id,
			));
		}
		else
			$clientToken = Braintree_ClientToken::generate(array(
				'merchantAccountId' => $merchant_account_id,
			));

		$cards = array();
		$cards['visa'] = $config['BRAINTREE_CARD_VISA'];
		$cards['mastercard'] = $config['BRAINTREE_CARD_MASTERCARD'];
		$cards['amex'] = $config['BRAINTREE_CARD_AMEX'];
		$cards['jcb'] = $config['BRAINTREE_CARD_JCB'];
		$cards['discover'] = $config['BRAINTREE_CARD_DISCOVER'];
		$cards['diners'] = $config['BRAINTREE_CARD_DINERS'];
		$cards['maestro'] = $config['BRAINTREE_CARD_MAESTRO'];

		$currency = new Currency($this->context->cart->id_currency);

		$language_iso_code = $this->context->language->iso_code;
		$bt_locale = Braintreejs::getLocale($language_iso_code);

		// negative use case testing.  only if in debug mode and if get parameter exists
		$forceError = Tools::getValue('setuperror', false);
		if ($forceError && $config['BRAINTREE_LOG_TRANSACTIONS'])
			$bt_data_env = 'asda';

		//setup smarty variables required to preprocess the javascript
		$this->context->smarty->assign(array(
			'braintree_ps_version' => _PS_VERSION_,
			'braintree_clientside' => addslashes($config['BRAINTREE_MODE'] ? $config['BRAINTREE_CLIENTSIDE_LIVE'] : $config['BRAINTREE_CLIENTSIDE_TEST']),
			'braintree_env' => $bt_data_env,
			'braintree_merchant_id' => $merchant_id,
			'braintree_client_token' => $clientToken,
			'braintree_paypal_enabled' => $braintree_paypal_enabled,
			'braintree_paypal_future' => $braintree_paypal_future,
			'braintree_dropinui_enabled' => $braintree_dropinui_enabled,
			'braintree_amount' => $this->context->cart->getOrderTotal(),
			'braintree_currency' => $currency->iso_code,
			'braintree_3ds' => $braintree_3ds,
			'braintree_debug' => $config['BRAINTREE_LOG_TRANSACTIONS'],
			'braintree_locale' => $bt_locale,
			'braintree_use_postcode' => $braintree_use_postcode,
		));

		$js_output = '';
		if ($braintree_dropinui_enabled)
			$js_output = '<script type="text/javascript">'.$this->fetchTemplate('views/js/braintree-dropin-prestashop.js').'</script>';
		else
			$js_output = '<script type="text/javascript">'.$this->fetchTemplate('views/js/braintree-hosted-prestashop.js').'</script>';

		//now add additional smarty parameters for the payment form
		$this->context->smarty->assign(array(
			'braintree_cards' => $cards,
			'braintree_is_dedicated_page' => $is_dedicated_page,
			'braintree_javascript' => $js_output,
		));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$this->context->smarty->assign('this_path_bt', $this->_path);
		else
			$this->context->smarty->assign('this_path_bt', $this->getPathUri());

		//we only need to lookup existing paypal accounts if paypal is enabled and they are not using the dropin UI. 
		//the dropin UI will automatically deal with this feature
		if ($braintree_paypal_enabled && !$braintree_dropinui_enabled)
		{
			try
			{
				//locate the existing customer so that we can find any existing paypal accounts in the vault
				$customer = Braintree_Customer::find($config['BRAINTREE_UUID'].'__'.$this->context->customer->id);
				$paypalAccounts = $customer->paypalAccounts;
				$this->recordTransaction($customer, $this->context->cart->id, 'findcustomer');

				$braintree_pp_accounts = array();
				foreach ($paypalAccounts as $paypalAccount)
				{
						$temp = array();
						$temp['token'] = $paypalAccount->token;
						$temp['imageUrl'] = $paypalAccount->imageUrl;
						$temp['email'] = $paypalAccount->email;
						$braintree_pp_accounts[] = $temp;
				}
				$this->context->smarty->assign(array(
					'braintree_pp_accounts' => $braintree_pp_accounts,
				));
			}
			catch (Braintree_Exception_NotFound $e)	/* we ignore the exception intentionally, therefor the catch statement should be empty. */
			{
				//ignore
			}
		}

		if ($return_content)
			if ($braintree_dropinui_enabled)
				return $this->fetchTemplate('payment_dropin.tpl');
			else
				return $this->fetchTemplate('payment_hostedfields.tpl');
	}


	/* 1.4 */
	public function hookPostUpdateOrderStatus($params)
	{
		$this->hookActionOrderStatusPostUpdate($params);
	}

	/* 1.5 and 1.6 */
	public function hookActionOrderStatusPostUpdate($params)
	{
		$new_order_status = $params['newOrderStatus'];	//object
		$id_order = $params['id_order'];

		// only if the new order status is payment accepted
		if ($new_order_status->id != Configuration::get('PS_OS_PAYMENT'))
			return;

		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return;

		//continue only if Braintree settings are complete
		if (!$this->checkSettings())
			return;

		// Get the transaction details
		$braintree_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' ');
		if (isset($braintree_transaction_details['id_transaction']))
		{
			try
			{
				//the environment is based on the transaction record since this is an existing transaction
				$bt_env = ($braintree_transaction_details['mode'] == 'live') ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
				$setup_ok = $this->setupBraintreeEnv($bt_env);

				if (!$setup_ok)
					return;

				$transaction = Braintree_Transaction::find($braintree_transaction_details['id_transaction']);

				//update the transaction status
				Db::getInstance()->update('braintree_transaction', array(
						'status' => ($transaction->status == Braintree_Transaction::AUTHORIZED || $transaction->status == Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT || $transaction->status == Braintree_Transaction::AUTHORIZING || $transaction->status == Braintree_Transaction::SETTLED || $transaction->status == Braintree_Transaction::SETTLING ? 'paid' : 'unpaid'),
					), '`id_order` = \''.$id_order.'\'', 1, false, false, true);

				/** @since 1.5.0 Attach the Braintree Transaction ID to this Order */
				if (version_compare(_PS_VERSION_, '1.5', '>='))
				{
					$new_order = new Order((int)$id_order);
					if (Validate::isLoadedObject($new_order))
					{
						$payment = $new_order->getOrderPaymentCollection();
						if (isset($payment[0]))
						{
							$payment_instrument = $this->convertInstrumentType($transaction->paymentInstrumentType);
							$payment[0]->payment_method = pSQL($payment_instrument);
							$payment[0]->transaction_id = pSQL($transaction->id);
							$payment[0]->save();
						}
					}
				}

			} catch(Braintree_Exception $e) {
				$message = $e->getMessage();
				if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
					Logger::addLog($this->l('Braintree - Unable to record card details').' '.$message, 2, null, 'Order', (int)$id_order, true);
			}
		}
	}

	/* this is for PS v1.6 */
	public function hookAdminOrder($params)
	{
		if (version_compare(_PS_VERSION_, '1.6', '<'))
			return;

		//continue only if Braintree settings are complete
		if (!$this->checkSettings())
			return;

		$id_order = (int)$params['id_order'];

		// Get the braintree mode.  If not found then we return since we did not use Braintree for this order.  Or the database record is missing and we cannot do anything anyway
		$bt_mode = Db::getInstance()->getValue('SELECT mode FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' ');
		if (!$bt_mode)
			return;

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			return;

		$customer = new Customer((int)$order->id_customer);
		if (!Validate::isLoadedObject($customer))
			return;

		//the environment is based on the transaction record since this is an existing transaction
		$bt_env = ($bt_mode == 'live') ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
		$setup_ok = $this->setupBraintreeEnv($bt_env);

		$token = Tools::getAdminTokenLite('AdminOrders');

		if (!$setup_ok)
			return;

		// If the "Void" button has been clicked, then attempt to void it with Braintree
		if (Tools::isSubmit('SubmitBraintreeVoid') && Tools::getIsset('id_transaction_braintree') && Tools::getIsset('id_braintree_transaction'))
		{
			$id_transaction_braintree = Tools::getValue('id_transaction_braintree');
			$id_braintree_transaction = Tools::getValue('id_braintree_transaction');

			try
			{
				//attempt to void
				$result = Braintree_Transaction::void($id_transaction_braintree);

				if ($result->success)
				{
					//Transaction successfully voided, update original transaction to unpaid.
					Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'braintree_transaction SET status =  \'unpaid\' WHERE id_braintree_transaction = '.(int)$id_braintree_transaction);

					/** add a negative entry to the payment table */
					$order->addOrderPayment(($order->total_paid * -1), null, $result->transaction->id);

					//change order status to error
					$order = new Order((int)$id_order);
					if (Validate::isLoadedObject($order))
						$order->setCurrentState((int)Configuration::get('PS_OS_ERROR'), $this->context->employee->id);

					Tools::redirectAdmin('index.php?controller=AdminOrders&id_order='.$order->id.'&vieworder&conf=4&token='.$token);
				} 
				else
					$this->_errors['braintree_void_error'] = $result->message;

			} catch(Braintree_Exception $e) {
				$this->_errors['braintree_void_error'] = 'Unable to process the void transaction, check with Braintree';
//				$message = $e->getMessage();
//				$code = $e->code();
			}
		}

		// If the "Refund" button has been clicked, check if we can perform a partial or full refund on this order
		if (Tools::isSubmit('SubmitBraintreeRefund') && Tools::getIsset('id_transaction_braintree') && Tools::getIsset('braintree_amount_to_refund'))
		{
			$id_transaction_braintree = Tools::getValue('id_transaction_braintree');
			$braintree_amount_to_refund = Tools::getValue('braintree_amount_to_refund');

			try {
				// Get transaction details and make sure the token is valid
				$braintree_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' AND status = \'paid\'');
				if (isset($braintree_transaction_details['id_transaction']) && $braintree_transaction_details['id_transaction'] === $id_transaction_braintree)
				{
					// Check how much has been refunded already on this order
					$braintree_refunded = Db::getInstance()->getValue('SELECT SUM(amount) FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'refund\' AND status = \'paid\'');
					if ($braintree_amount_to_refund <= number_format($braintree_transaction_details['amount'] - $braintree_refunded, 2, '.', ''))
					{
						//attempt the refund
						$result = Braintree_Transaction::refund($id_transaction_braintree, $braintree_amount_to_refund);
						if ($result->success)
						{
							$transaction = $result->transaction;
							$this->processRefund($transaction->id, (float)$braintree_amount_to_refund, $braintree_transaction_details, $order);
							
							Tools::redirectAdmin('index.php?controller=AdminOrders&id_order='.$order->id.'&vieworder&conf=4&token='.$token);
						}
						else
							$this->_errors['braintree_refund_error'] = $result->message;
					}
					else
						$this->_errors['braintree_refund_error'] = $this->l('You cannot refund more than').' '.Tools::displayPrice($braintree_transaction_details['amount'] - $braintree_refunded).' '.$this->l('on this order');
				}
			}
			catch(Braintree_Exception $e)
			{
				$this->_errors['braintree_refund_error'] = 'Unable to process the refund transaction, check with Braintree';
			}
		}

		// Check if the order was paid with Braintree and display the transaction details
		if (Db::getInstance()->getValue('SELECT module FROM '._DB_PREFIX_.'orders WHERE id_order = '.(int)$id_order) == $this->name)
		{
			$can_refund = false;
			$can_void = false;
			$can_capture = false;

			// Get the transaction details
			$braintree_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' ');

			// Get all the refunds previously made (to build a list and determine if another refund is still possible)
			$braintree_refunded = 0;
			$output_refund = '';
			$braintree_refund_details = Db::getInstance()->ExecuteS('SELECT amount, status, date_add FROM '._DB_PREFIX_.'braintree_transaction
			WHERE id_order = '.(int)$id_order.' AND type = \'refund\' ORDER BY date_add DESC');
			foreach ($braintree_refund_details as $braintree_refund_detail)
			{
				$braintree_refunded += ($braintree_refund_detail['status'] == 'paid' ? $braintree_refund_detail['amount'] : 0);
				$output_refund .= '<tr'.($braintree_refund_detail['status'] != 'paid' ? ' style="background: #FFBBAA;"': '').'><td>'.
				Tools::safeOutput($braintree_refund_detail['date_add']).'</td><td>'.Tools::displayPrice($braintree_refund_detail['amount']).
				'</td><td>'.($braintree_refund_detail['status'] == 'paid' ? $this->l('Processed') : $this->l('Error')).'</td></tr>';
			}

			$output = '<div class="col-lg-7"><div class="panel"><h3><i class="icon-money"></i> '.$this->l('Braintree Payment Details').'</h3>';
			$output .= '
				<ul class="nav nav-tabs" id="tabBraintree">
					<li class="active">
						<a href="#bt_details">
							<i class="icon-money"></i> '.$this->l('Details').' <span class="badge">'.$braintree_transaction_details['id_transaction'].'</span>
						</a>
					</li>
					<li>
						<a href="#bt_refund_void">
							<i class="icon-file-text"></i> '.$this->l('Refund and Void').'
						</a>
					</li>
				</ul>';
			$output .= '
				<div class="tab-content panel">
					<div class="tab-pane active" id="bt_details">';

			if (isset($braintree_transaction_details['id_transaction']))
			{
				try
				{
					//check with Braintree if the transaction can be refunded
					$transaction = Braintree_Transaction::find($braintree_transaction_details['id_transaction']);
					$this->recordTransaction($transaction, $id_order, 'transaction');

					if ($transaction->status == Braintree_Transaction::SETTLED || $transaction->status == Braintree_Transaction::SETTLING) 
					{
						$can_refund = true;
						$can_void = false;
					}
					else if ($transaction->status == Braintree_Transaction::AUTHORIZED || $transaction->status == Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT)
					{
						$can_refund = false;
						$can_void = true;
					}
					else
					{
						$can_refund = false;
						$can_void = false;
					}

					if ($transaction->status == Braintree_Transaction::AUTHORIZED)
						$can_capture = true;

					$output .= '
						<p>
						<b>'.$this->l('Braintree Transaction ID:').'</b> '.Tools::safeOutput($transaction->id).'<br /><br />
						<b>'.$this->l('Status:').'</b> '.Tools::safeOutput($this->convertTransactionStatus($transaction->status)).'<br>'.
						'<b>'.$this->l('Payment Instrument:').'</b> '.$this->convertInstrumentType($transaction->paymentInstrumentType).'<br>';

					if ($transaction->paymentInstrumentType == 'paypal_account')
					{
						if ($customer->email != $transaction->paypal['payerEmail'])
							$output .= '<b>'.$this->l('Paypal Address:').'</b> <span style="color:red">'.$transaction->paypal['payerEmail'].'</span><br>';
						else
							$output .= '<b>'.$this->l('Paypal Address:').'</b> '.$transaction->paypal['payerEmail'].'<br>';

						$output .=
							'<b>'.$this->l('Paypal Name:').'</b> '.$transaction->paypal['payerFirstName'].' '.$transaction->paypal['payerLastName'].'<br>'.
							'<b>'.$this->l('Paypal Seller Protection Status:').'</b> '.$transaction->paypal['sellerProtectionStatus'].'<br>';
					}
					else
					{
						$output .= '<b>'.$this->l('BIN:').'</b> '.$transaction->creditCard['bin'].'<br>';
						$output .= '<b>'.$this->l('Postal Code:').'</b> '.$transaction->billing['postalCode'].'<br>';
						if (isset($transaction->riskData))
							$output .= '<b>'.$this->l('Risk Data:').'</b> '.$transaction->riskData->decision.'<br>';

						if (isset($transaction->threeDSecureInfo->status))
						{
							$output .= '<b>'.$this->l('3D Secure Status:').'</b> '.$transaction->threeDSecureInfo->status.'<br>';
							$output .= '<b>'.$this->l('3D Secure Enrolled:').'</b> '.$transaction->threeDSecureInfo->enrolled.'<br>';
							$output .= '<b>'.$this->l('3D Secure Liability Shifted:').'</b> '.$transaction->threeDSecureInfo->liabilityShifted.'<br>';
							$output .= '<b>'.$this->l('3D Secure Liability Shift Possible:').'</b> '.$transaction->threeDSecureInfo->liabilityShiftPossible.'<br>';
						}
					}

					$output .= 
						'<b>'.$this->l('Amount:').'</b> '.Tools::displayPrice($transaction->amount).'<br>'.
						'<b>'.$this->l('Processed on:').'</b> '.Tools::safeOutput($transaction->createdAt->format('Y-m-d h:i:s a e')).'<br><br>'.
						'<b>'.$this->l('Mode:').'</b> <span style="font-weight: bold; color: '.($braintree_transaction_details['mode'] == 'live' ? 'green;">'.$this->l('Live') : '#CC0000;">'.$this->l('Test (You will not receive any payment, until you enable the "Live" mode)')).'</span>
						</p>';

					if ($braintree_transaction_details['payment_instrument_type'] == 'credit_card')
					{
						$output .= '
							<table class="table">
								<thead>
									<tr>
										<th><span class="title_box ">Can Refund</span></th>
										<th><span class="title_box ">Can Void</span></th>
										<th><span class="title_box ">CVC</span></th>
										<th><span class="title_box ">Address 1</span></th>
										<th><span class="title_box ">Postal Code</span></th>
										<th><span class="title_box ">AVS</span></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>'.($can_refund ? $this->l('Yes') : $this->l('No')).'</td>
										<td>'.($can_void ? $this->l('Yes') : $this->l('No')).'</td>
										<td>'.$transaction->cvvResponseCode.'</td>
										<td>'.$transaction->avsStreetAddressResponseCode.'</td>
										<td>'.$transaction->avsPostalCodeResponseCode.'</td>
										<td>'.$transaction->avsErrorResponseCode.'</td>
									</tr>
								</tbody>
							</table>';
					}
				}
				catch(Braintree_Exception $e)
				{
					$message = $e->getMessage();
					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree_Exception - Unable to retrieve Braintree transaction details: ').' '.$message, 3, null, 'Order', (int)$id_order, true);

					$output .= '<b style="color: #CC0000;">'.$this->l('Error:').'</b> '.$this->l('Unable to retrieve Braintree Transaction details.');
				}
			}
			else
				$output .= '<b style="color: #CC0000;">'.$this->l('Warning:').'</b> '.$this->l('The customer paid using Braintree. check details at the bottom of this page');

			$output .= '</div>';
			$output .= '<div class="tab-pane" id="bt_refund_void">';

			//refund block
			$output .= '<fieldset><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Proceed to a full or partial refund via Braintree').'</legend>'.
			((empty($this->_errors['braintree_refund_error']) && Tools::isSubmit('SubmitBraintreeRefund')) ? '<div class="conf confirmation">'.$this->l('The refund was successfully processed').'</div>' : '').
			(!empty($this->_errors['braintree_refund_error']) ? '<span style="color: #CC0000; font-weight: bold;">'.$this->l('Error:').' '.Tools::safeOutput($this->_errors['braintree_refund_error']).'</span><br /><br />' : '');

			if ($can_refund)
			{
				//get the currency symbol to use
				$order = new Order((int)$id_order);
				$currency = new Currency($order->id_currency);
				$symbol = $currency->getSign();

				$output .= $this->l('Already refunded:').' <b>'.Tools::displayPrice($braintree_refunded, $currency).'</b><br /><br />'.($braintree_refunded ? '<table class="table" cellpadding="0" cellspacing="0" style="font-size: 12px;"><tr><th>'.$this->l('Date').'</th><th>'.$this->l('Amount refunded').'</th><th>'.$this->l('Status').'</th></tr>'.$output_refund.'</table><br />' : '').
				($braintree_transaction_details['amount'] > $braintree_refunded ? '<form action="" method="post">'.$this->l('Refund:').' '.$symbol.' <input type="text" value="'.number_format($braintree_transaction_details['amount'] - $braintree_refunded, 2, '.', '').
				'" name="braintree_amount_to_refund" style="text-align: right; width: 102px;" /> <input type="hidden" name="id_braintree_transaction" value="'.Tools::safeOutput($braintree_transaction_details['id_braintree_transaction']).'" /><input type="hidden" name="id_transaction_braintree" value="'.
				Tools::safeOutput($braintree_transaction_details['id_transaction']).'" /><input type="submit" class="button" onclick="return confirm(\\\''.addslashes($this->l('Do you want to process this refund?')).'\\\');" name="SubmitBraintreeRefund" value="'.$this->l('Process Refund').'" /></form>' : '');
			}
			else
				$output .= $this->l('This transaction cannot be refunded');

			$output .= '</fieldset><br />';

			//void block
			$output .= '<br /><fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Proceed to void the transaction via Braintree').'</legend>'.
			((empty($this->_errors['braintree_void_error']) && Tools::isSubmit('SubmitBraintreeVoid')) ? '<div class="conf confirmation">'.$this->l('The void was successfully processed').'</div>' : '').
			(!empty($this->_errors['braintree_void_error']) ? '<span style="color: #CC0000; font-weight: bold;">'.$this->l('Error:').' '.Tools::safeOutput($this->_errors['braintree_void_error']).'</span><br /><br />' : '');

			if ($can_void)
				$output .= '<form action="" method="post"><input type="hidden" name="id_braintree_transaction" value="'.Tools::safeOutput($braintree_transaction_details['id_braintree_transaction']).'" /><input type="hidden" name="id_transaction_braintree" value="'.Tools::safeOutput($braintree_transaction_details['id_transaction']).'" /><input type="submit" class="button" onclick="return confirm(\\\''.addslashes($this->l('Do you want to process this void?')).'\\\');" name="SubmitBraintreeVoid" value="'.$this->l('Process Void').'" /></form>';
			else
				$output .= $this->l('This transaction cannot be voided');

			$output .= '</fieldset><br />';
			$output .= '</div>';

			$output .= "
				<script>
					$('#tabBraintree a').click(function (e) {
						e.preventDefault()
						$(this).tab('show')
					})
				</script>";

			$output .= '</div></div></div>';
			return $output;
		}
	}

	/**
	 * Display the two fieldsets containing Braintree's transactions details
	 * Visible on the Order's detail page in the Back-office only
	 *
	 * @return string HTML/JS Content
	 *
	 * todo: need to refactor this in v1.4 and v1.5: shouldn't using header, just use hookAdminOrder
	 */
	public function hookBackOfficeHeader()
	{
		//do not use this function for PS v1.6+
		if (version_compare(_PS_VERSION_, 1.6, '>='))
			return;

		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return;

		//continue only if Braintree settings are complete
		if (!$this->checkSettings())
			return;

		$id_order = Tools::getValue('id_order');

		// Continue only if we are on the order's details page (Back-office)
		if (!Tools::getIsset('vieworder') || !$id_order)
			return;

		// Get the braintree mode.  If not found then we return since we did not use Braintree for this order.  Or the database record is missing and we cannot do anything anyway
		$bt_mode = Db::getInstance()->getValue('SELECT mode FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' ');
		if (!$bt_mode)
			return;

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			return;

		$customer = new Customer((int)$order->id_customer);
		if (!Validate::isLoadedObject($customer))
			return;

		//the environment is based on the transaction record since this is an existing transaction
		$bt_env = ($bt_mode == 'live') ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
		$setup_ok = $this->setupBraintreeEnv($bt_env);

		$token = Tools::getAdminTokenLite('AdminOrders');

		if (!$setup_ok)
			return;

		// If the "Void" button has been clicked, then attempt to void it with Braintree
		if (Tools::isSubmit('SubmitBraintreeVoid') && Tools::getIsset('id_transaction_braintree') && Tools::getIsset('id_braintree_transaction')) 
		{
			$id_transaction_braintree = Tools::getValue('id_transaction_braintree');
			$id_braintree_transaction = Tools::getValue('id_braintree_transaction');
			
			try {
				//attempt to void
				$result = Braintree_Transaction::void($id_transaction_braintree);

				if ($result->success)
				{
					//Transaction successfully voided, update original transaction to unpaid.
					Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'braintree_transaction SET status =  \'unpaid\' WHERE id_braintree_transaction = '.(int)$id_braintree_transaction);
					
					/** add a negative entry to the payment table */
					if (version_compare(_PS_VERSION_, '1.5', '>='))
						$order->addOrderPayment(($order->total_paid * -1), null, $result->transaction->id);

					//change order status to error
					$order = new Order((int)$id_order);
					if (Validate::isLoadedObject($order))
						$order->setCurrentState((int)Configuration::get('PS_OS_ERROR'), $this->context->employee->id);

					Tools::redirectAdmin('index.php?controller=AdminOrders&id_order='.$order->id.'&vieworder&conf=4&token='.$token);

				}
				else
					$this->_errors['braintree_void_error'] = $result->message;

			} catch(Braintree_Exception $e) {
				$this->_errors['braintree_void_error'] = 'Unable to process the void transaction, check with Braintree';
//				$message = $e->getMessage();
//				$code = $e->code();
			}
		}

		// If the "Refund" button has been clicked, check if we can perform a partial or full refund on this order
		if (Tools::isSubmit('SubmitBraintreeRefund') && Tools::getIsset('id_transaction_braintree') && Tools::getIsset('braintree_amount_to_refund'))
		{
			$id_transaction_braintree = Tools::getValue('id_transaction_braintree');
			$braintree_amount_to_refund = Tools::getValue('braintree_amount_to_refund');

			try {
				// Get transaction details and make sure the token is valid
				$braintree_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' AND status = \'paid\'');
				if (isset($braintree_transaction_details['id_transaction']) && $braintree_transaction_details['id_transaction'] === $id_transaction_braintree)
				{
					// Check how much has been refunded already on this order
					$braintree_refunded = Db::getInstance()->getValue('SELECT SUM(amount) FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'refund\' AND status = \'paid\'');
					if ($braintree_amount_to_refund <= number_format($braintree_transaction_details['amount'] - $braintree_refunded, 2, '.', ''))
					{
						//attempt the refund
						$result = Braintree_Transaction::refund($id_transaction_braintree, $braintree_amount_to_refund);
						if ($result->success)
						{
							$transaction = $result->transaction;
							$this->processRefund($transaction->id, (float)$braintree_amount_to_refund, $braintree_transaction_details, $order);

							Tools::redirectAdmin('index.php?controller=AdminOrders&id_order='.$order->id.'&vieworder&conf=4&token='.$token);
						} 
						else
							$this->_errors['braintree_refund_error'] = $result->message;
					}
					else
						$this->_errors['braintree_refund_error'] = $this->l('You cannot refund more than').' '.Tools::displayPrice($braintree_transaction_details['amount'] - $braintree_refunded).' '.$this->l('on this order');
				}
			} 
			catch(Braintree_Exception $e) 
			{
				$this->_errors['braintree_refund_error'] = 'Unable to process the refund transaction, check with Braintree';
			}
		}

		// Check if the order was paid with Braintree and display the transaction details
		if (Db::getInstance()->getValue('SELECT module FROM '._DB_PREFIX_.'orders WHERE id_order = '.(int)$id_order) == $this->name)
		{
			$can_refund = false;
			$can_void = false;
			$can_capture = false;

			// Get the transaction details
			$braintree_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'braintree_transaction WHERE id_order = '.(int)$id_order.' AND type = \'payment\' ');

			// Get all the refunds previously made (to build a list and determine if another refund is still possible)
			$braintree_refunded = 0;
			$output_refund = '';
			$braintree_refund_details = Db::getInstance()->ExecuteS('SELECT amount, status, date_add FROM '._DB_PREFIX_.'braintree_transaction
			WHERE id_order = '.(int)$id_order.' AND type = \'refund\' ORDER BY date_add DESC');
			foreach ($braintree_refund_details as $braintree_refund_detail)
			{
				$braintree_refunded += ($braintree_refund_detail['status'] == 'paid' ? $braintree_refund_detail['amount'] : 0);
				$output_refund .= '<tr'.($braintree_refund_detail['status'] != 'paid' ? ' style="background: #FFBBAA;"': '').'><td>'.
				Tools::safeOutput($braintree_refund_detail['date_add']).'</td><td>'.Tools::displayPrice($braintree_refund_detail['amount']).
				'</td><td>'.($braintree_refund_detail['status'] == 'paid' ? $this->l('Processed') : $this->l('Error')).'</td></tr>';
			}

			$output = '
			<script type="text/javascript">
				$(document).ready(function() {
					$(\'<fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Braintree Payment Details').'</legend>';

			if (isset($braintree_transaction_details['id_transaction']))
			{
				try 
				{
					//check with Braintree if the transaction can be refunded
					$transaction = Braintree_Transaction::find($braintree_transaction_details['id_transaction']);
					$this->recordTransaction($transaction, $id_order, 'transaction');

					if ($transaction->status == Braintree_Transaction::SETTLED || $transaction->status == Braintree_Transaction::SETTLING)
					{
						$can_refund = true;
						$can_void = false;
					}
					else if ($transaction->status == Braintree_Transaction::AUTHORIZED || $transaction->status == Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT)
					{
						$can_refund = false;
						$can_void = true;
					}
					else
					{
						$can_refund = false;
						$can_void = false;
					}

					if ($transaction->status == Braintree_Transaction::AUTHORIZED)
						$can_capture = true;


					$output .= 
						'<b>'.$this->l('Braintree Transaction ID:').'</b> '.Tools::safeOutput($transaction->id).'<br /><br />'.
						'<b>'.$this->l('Status:').'</b> '.Tools::safeOutput($this->convertTransactionStatus($transaction->status)).'<br />'.
						'<b>'.$this->l('Payment Instrument:').'</b> '.$this->convertInstrumentType($transaction->paymentInstrumentType).'<br />';
					
					if ($transaction->paymentInstrumentType == 'paypal_account')
					{
						if ($customer->email != $transaction->paypal['payerEmail'])
							$output .= '<b>'.$this->l('Paypal Address:').'</b> <span style="color:red">'.$transaction->paypal['payerEmail'].'</span><br>';
						else
							$output .= '<b>'.$this->l('Paypal Address:').'</b> '.$transaction->paypal['payerEmail'].'<br>';

						$output .=
							'<b>'.$this->l('Paypal Name:').'</b> '.$transaction->paypal['payerFirstName'].' '.$transaction->paypal['payerLastName'].'<br>'.
							'<b>'.$this->l('Paypal Seller Protection Status:').'</b> '.$transaction->paypal['sellerProtectionStatus'].'<br>';
					}
					else
					{
						$output .= '<b>'.$this->l('BIN:').'</b> '.$transaction->creditCard['bin'].'<br>';
						$output .= '<b>'.$this->l('Postal Code:').'</b> '.$transaction->billing['postalCode'].'<br>';
					}

					$output .=
						'<b>'.$this->l('Amount:').'</b> '.Tools::displayPrice($transaction->amount).'<br />'.
						'<b>'.$this->l('Processed on:').'</b> '.Tools::safeOutput($transaction->createdAt->format('Y-m-d h:i:s a e')).'<br />'.
						'<b>'.$this->l('Can Refund:').'</b> '.($can_refund ? $this->l('Yes') : $this->l('No')).'<br />'.
						'<b>'.$this->l('Can Void:').'</b> '.($can_void ? $this->l('Yes') : $this->l('No')).'<br />';

					if ($transaction->paymentInstrumentType == 'credit_card')
					{
						$output .= 
							'<br /><b><u>'.$this->l('Basic Fraud Protection:').'</u></b><br>'.
							'<b>'.$this->l('CVC Response Code:').'</b> '.$transaction->cvvResponseCode.'<br />'.
							'<b>'.$this->l('Address 1 Response Code:').'</b> '.$transaction->avsStreetAddressResponseCode.'<br />'.
							'<b>'.$this->l('Postal Code Response Code:').'</b> '.$transaction->avsPostalCodeResponseCode.'<br />'.
							'<b>'.$this->l('AVS Response Code:').'</b> '.$transaction->avsErrorResponseCode.'<br />';
						

						if (isset($transaction->riskData))
							$output .= '<b>'.$this->l('Risk Data:').'</b> '.$transaction->riskData->decision.'<br />';

						if (isset($transaction->threeDSecureInfo->status))
						{
							$output .= '<b>'.$this->l('3D Secure Status:').'</b> '.$transaction->threeDSecureInfo->status.'<br>';
							$output .= '<b>'.$this->l('3D Secure Enrolled:').'</b> '.$transaction->threeDSecureInfo->enrolled.'<br>';
							$output .= '<b>'.$this->l('3D Secure Liability Shifted:').'</b> '.$transaction->threeDSecureInfo->liabilityShifted.'<br>';
							$output .= '<b>'.$this->l('3D Secure Liability Shift Possible:').'</b> '.$transaction->threeDSecureInfo->liabilityShiftPossible.'<br>';
						}

					}
					
					$output .= 
						'<br /><b>'.$this->l('Mode:').'</b> <span style="font-weight: bold; color: '.($braintree_transaction_details['mode'] == 'live' ? 'green;">'.$this->l('Live') : '#CC0000;">'.$this->l('Test (You will not receive any payment, until you enable the "Live" mode)')).'</span>';

				} catch(Braintree_Exception $e) {
					$message = $e->getMessage();
					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree_Exception - Unable to retrieve transaction details: ').' '.$message, 3, null, 'Order', (int)$id_order, true);

						$output .= '<b style="color: #CC0000;">'.$this->l('Error:').'</b> '.$this->l('Unable to retrieve Braintree Transaction details.');
				}
			}
			else
				$output .= '<b style="color: #CC0000;">'.$this->l('Warning:').'</b> '.$this->l('The customer paid using Braintree. check details at the bottom of this page');

			$output .= '</fieldset><br>';

			//refund block
			$output .= '<br /><fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Proceed to a full or partial refund via Braintree').'</legend>'.
			((empty($this->_errors['braintree_refund_error']) && Tools::isSubmit('SubmitBraintreeRefund')) ? '<div class="conf confirmation">'.$this->l('The refund was successfully processed').'</div>' : '').
			(!empty($this->_errors['braintree_refund_error']) ? '<span style="color: #CC0000; font-weight: bold;">'.$this->l('Error:').' '.Tools::safeOutput($this->_errors['braintree_refund_error']).'</span><br /><br />' : '');

			if ($can_refund)
			{
				//get the currency symbol to use
				$order = new Order((int)$id_order);
				$currency = new Currency($order->id_currency);
				$symbol = $currency->getSign();

				$output .= $this->l('Already refunded:').' <b>'.Tools::displayPrice($braintree_refunded, $currency).'</b><br /><br />'.($braintree_refunded ? '<table class="table" cellpadding="0" cellspacing="0" style="font-size: 12px;"><tr><th>'.$this->l('Date').'</th><th>'.$this->l('Amount refunded').'</th><th>'.$this->l('Status').'</th></tr>'.$output_refund.'</table><br />' : '').
				($braintree_transaction_details['amount'] > $braintree_refunded ? '<form action="" method="post">'.$this->l('Refund:').' '.$symbol.' <input type="text" value="'.number_format($braintree_transaction_details['amount'] - $braintree_refunded, 2, '.', '').
				'" name="braintree_amount_to_refund" style="text-align: right; width: 45px;" /> <input type="hidden" name="id_braintree_transaction" value="'.Tools::safeOutput($braintree_transaction_details['id_braintree_transaction']).'" /><input type="hidden" name="id_transaction_braintree" value="'.
				Tools::safeOutput($braintree_transaction_details['id_transaction']).'" /><input type="submit" class="button" onclick="return confirm(\\\''.addslashes($this->l('Do you want to process this refund?')).'\\\');" name="SubmitBraintreeRefund" value="'.$this->l('Process Refund').'" /></form>' : '');
			}
			else
				$output .= $this->l('This transaction cannot be refunded');

			$output .= '</fieldset><br />';

			//void block
			$output .= '<br /><fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Proceed to void the transaction via Braintree').'</legend>'.
			((empty($this->_errors['braintree_void_error']) && Tools::isSubmit('SubmitBraintreeVoid')) ? '<div class="conf confirmation">'.$this->l('The void was successfully processed').'</div>' : '').
			(!empty($this->_errors['braintree_void_error']) ? '<span style="color: #CC0000; font-weight: bold;">'.$this->l('Error:').' '.Tools::safeOutput($this->_errors['braintree_void_error']).'</span><br /><br />' : '');

			if ($can_void)
				$output .= '<form action="" method="post"><input type="hidden" name="id_braintree_transaction" value="'.Tools::safeOutput($braintree_transaction_details['id_braintree_transaction']).'" /><input type="hidden" name="id_transaction_braintree" value="'.Tools::safeOutput($braintree_transaction_details['id_transaction']).'" /><input type="submit" class="button" onclick="return confirm(\\\''.addslashes($this->l('Do you want to process this void?')).'\\\');" name="SubmitBraintreeVoid" value="'.$this->l('Process Void').'" /></form>';
			else
				$output .= $this->l('This transaction cannot be voided');

			$output .= '</fieldset><br />';

			$output .= '\').insertBefore($(\'select[name=id_order_state]\').parent().parent().find(\'fieldset\').first());
				});
			</script>';

			return $output;
		}
	}

	/**
	 * Process a partial or full refund
	 *
	 * @param string $id_transaction_braintree Braintree Transaction ID (token)
	 * @param float $amount Amount to refund
	 * @param array $original_transaction Original transaction details
	 */
	public function processRefund($id_transaction_braintree, $amount, $original_transaction, $order)
	{
		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return;

		// Store the refund details
		Db::getInstance()->Execute('
		INSERT INTO '._DB_PREFIX_.'braintree_transaction (type, id_braintree_customer, id_cart, id_order,
		id_transaction, amount, status, currency, cvc_check, line1_check, zip_check, avs_check, mode, date_add)
		VALUES (\'refund\', '.(int)$original_transaction['id_braintree_customer'].', '.(int)$original_transaction['id_cart'].', '.
		(int)$original_transaction['id_order'].', \''.pSQL($id_transaction_braintree).'\',
		\''.(float)$amount.'\', \''.(!isset($this->_errors['braintree_refund_error']) ? 'paid' : 'unpaid').'\', \''.pSQL($original_transaction['currency']).'\',
		0, 0, 0, 0, \''.(Configuration::get('BRAINTREE_MODE') ? 'live' : 'test').'\', NOW())');

		/** add a negative entry to the payment table */
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			if (Validate::isLoadedObject($order))
				$order->addOrderPayment(($amount * -1), null, $id_transaction_braintree);
		}
	}
	
	/**
	 * Display a confirmation message after an order has been placed
	 *
	 * @param array Hook parameters
	 */
	public function hookOrderConfirmation($params)
	{
		if (!isset($params['objOrder']) || ($params['objOrder']->module != $this->name))
			return false;
		
		$state = $params['objOrder']->getCurrentState();

		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && isset($params['objOrder']->valid))
			$this->smarty->assign('bt_order', array('reference' => isset($params['objOrder']->reference) ? $params['objOrder']->reference : '#'.sprintf('%06d', $params['objOrder']->id), 'valid' => $params['objOrder']->valid));

		if ($state == Configuration::get('PS_OS_OUTOFSTOCK'))
			$this->smarty->assign('os_back_ordered', true);
		else
			$this->smarty->assign('os_back_ordered', false);

		//added this so we could present a better/meaningful message to the customer when the charge suceeds, but verifications have failed.
		$pending_order_status = (int)Configuration::get('BRAINTREE_PENDING_ORDER_STATUS');
		$current_order_status = (int)$params['objOrder']->getCurrentState();
		if ($pending_order_status == $current_order_status)
			$this->smarty->assign('order_pending', true);
		else
			$this->smarty->assign('order_pending', false);

		//added this so we could present a better/meaningful message to the customer when the Paypal authorization suceeds, but settlement remains pending
		$settlement_pending_order_status = (int)Configuration::get('BRAINTREE_SETTLEMENT_PENDING_OS');
		$current_order_status = (int)$params['objOrder']->getCurrentState();
		if ($settlement_pending_order_status == $current_order_status)
			$this->smarty->assign('settlement_pending', true);
		else
			$this->smarty->assign('settlement_pending', false);

		return $this->fetchTemplate('order-confirmation.tpl');
	}

	/**
	 * Process a payment
	 *
	 */
	public function processPayment()
	{
		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return;

		$redirect_controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
		$redirect_url = $this->context->link->getPageLink($redirect_controller, true).(strpos($redirect_controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';

		if (Tools::isSubmit('braintree_payment_advanced'))
		{
			$redirect_controller = 'advancedpayment';
			$redirect_url = $this->context->link->getModuleLink($this->name, 'advancedpayment',
					array('token' => Tools::getToken(false), 'bt_error' => '1'), true);
		}

		$config = Configuration::getMultiple(array('BRAINTREE_MODE', 'BRAINTREE_SUBMIT_SETTLE', 'BRAINTREE_UI_MODE', 'BRAINTREE_3DS'));

		//the environment is based on the global configuration since this is a new transaction
		$bt_env = $config['BRAINTREE_MODE'] ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
		$this->setupBraintreeEnv($bt_env);

		$submit_settle = $config['BRAINTREE_SUBMIT_SETTLE'];
		$merchant_account_id = $config['BRAINTREE_MODE'] ? Configuration::get('BRAINTREE_CURRENCY_LIVE_'.$this->context->currency->iso_code) : Configuration::get('BRAINTREE_CURRENCY_TEST_'.$this->context->currency->iso_code);
		$braintree_dropinui_enabled = $config['BRAINTREE_UI_MODE'] ? 1 : 0;
		$braintree_3ds = $config['BRAINTREE_3DS'] ? 1 : 0;

		//3DS only supported when dropin ui is disabled
		if ($braintree_dropinui_enabled) 
			$braintree_3ds = false;

		try
		{
			//default the cardholder name to the customer name
			$card_holder = $this->context->customer->firstname.' '.$this->context->customer->lastname;

			//if the billing address was provided, the use the billing address name as the card holder
			if (isset($this->context->cart->id_address_invoice))
			{
				$billing_address = new Address((int)$this->context->cart->id_address_invoice);
				if (Validate::isLoadedObject($billing_address)) 
					$card_holder = $billing_address->firstname.' '.$billing_address->lastname;
			}

			$require3ds = array(
					'required' => ($braintree_3ds == '1' ? true : false),
				);

			//sale options used for any payment type
			$options = array(
				'amount' => $this->context->cart->getOrderTotal(),
				'orderId' => $this->context->cart->id,
				'merchantAccountId' => $merchant_account_id,
				'options' => array(
					'submitForSettlement' => ($submit_settle == '1' ? true : false),
					'three_d_secure'	  => $require3ds,
				),
				'deviceData' => Tools::getValue('device_data'),
			);

			//always add the basic customer information for each sale
			$customerArray = array(
				'firstName' => $this->context->customer->firstname,
				'lastName' => $this->context->customer->lastname,
				'email' => $this->context->customer->email,
			);
			$options['customer'] = $customerArray;

			//customer is either paying with card or paypal
			$isPaypal = Tools::isSubmit('submitPaypalPayment');
			$isDropIn = Tools::isSubmit('submitDropInPayment');
			$isHosted = Tools::isSubmit('submitHostedPayment');

			if ($isPaypal)
			{
				$payment_method_nonce = Tools::getValue('payment_method_nonce');	//for new paypal account
				$payment_method_token = Tools::getValue('payment_method_token');	//for existing paypal account
				if ($payment_method_nonce) // new paypal account
					$options['paymentMethodNonce'] = $payment_method_nonce;
				else if ($payment_method_token) // existing paypal token 
					$options['paymentMethodToken'] = $payment_method_token;
				else 
				{
					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree - Exception occured').' Invalid payment method selected.  Please report issue to module developer ', 4, null, 'Cart', (int)$this->context->cart->id, true);

//					$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//					$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
					Tools::redirectLink($redirect_url);
				}

				//if the delivery address is set and valid, then send it
				if (isset($this->context->cart->id_address_delivery))
				{
					//only send the address if not virtual
					$isVirtual = $this->context->cart->isVirtualCart();

					$delivery_address = new Address((int)$this->context->cart->id_address_delivery);
					if (Validate::isLoadedObject($delivery_address) && !$isVirtual) 
					{
						$shipping = array(
							'firstName' => $delivery_address->firstname,
							'lastName' => $delivery_address->lastname,
							'company' => $delivery_address->company,
							'streetAddress' => $delivery_address->address1,
							'extendedAddress' => $delivery_address->address2,
							'locality' => $delivery_address->city,
							'postalCode' => $delivery_address->postcode,
						);

						if ($delivery_address->id_country)
							$shipping['countryCodeAlpha2'] = Country::getIsoById($delivery_address->id_country);

						if ($delivery_address->id_state)
						{
							$state = new State((int)$delivery_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$shipping['region'] = $state->iso_code;
						}

						$options['shipping'] = $shipping;
					}
				}

			}
			else if ($isDropIn)
			{
				$payment_method_nonce = Tools::getValue('payment_method_nonce');

				if ($payment_method_nonce)
					$options['paymentMethodNonce'] = $payment_method_nonce;
				else 
				{
					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree - Exception occured').' Invalid payment method selected.  Please report issue to module developer ', 4, null, 'Cart', (int)$this->context->cart->id, true);

//					$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//					$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
					Tools::redirectLink($redirect_url);
				}

				//if the delivery address is set and valid, then send it
				//we do not send the billing address, as Braintree does not allow it when using Dropin UI.  The UI will collect any required AVS information.
				if (isset($this->context->cart->id_address_delivery))
				{
					//only send the address if not virtual
					$isVirtual = $this->context->cart->isVirtualCart();

					$delivery_address = new Address((int)$this->context->cart->id_address_delivery);
					if (Validate::isLoadedObject($delivery_address) && !$isVirtual) 
					{
						$shipping = array(
							'firstName' => $delivery_address->firstname,
							'lastName' => $delivery_address->lastname,
							'company' => $delivery_address->company,
							'streetAddress' => $delivery_address->address1,
							'extendedAddress' => $delivery_address->address2,
							'locality' => $delivery_address->city,
							'postalCode' => $delivery_address->postcode,
						);

						if ($delivery_address->id_country)
							$shipping['countryCodeAlpha2'] = Country::getIsoById($delivery_address->id_country);

						if ($delivery_address->id_state)
						{
							$state = new State((int)$delivery_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$shipping['region'] = $state->iso_code;
						}

						$options['shipping'] = $shipping;
					}
				}
			}
			else if ($isHosted)
			{
				// a payment_method_nonce is required, if it does not exists, then there was an error
				$payment_method_nonce = Tools::getValue('payment_method_nonce');

				if ($payment_method_nonce)
					$options['paymentMethodNonce'] = $payment_method_nonce;
				else 
				{
					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree - Exception occured').' Invalid payment method selected.  Please report issue to module developer ', 4, null, 'Cart', (int)$this->context->cart->id, true);

//					$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//					$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
					Tools::redirectLink($redirect_url);
				}

				//if the billing address is set and valid, then send it
				if (isset($this->context->cart->id_address_invoice))
				{
					$billing_address = new Address((int)$this->context->cart->id_address_invoice);
					if (Validate::isLoadedObject($billing_address)) 
					{
						$billing = array(
							'firstName' => $billing_address->firstname,
							'lastName' => $billing_address->lastname,
							'company' => $billing_address->company,
							'streetAddress' => $billing_address->address1,
							'extendedAddress' => $billing_address->address2,
							'locality' => $billing_address->city,
							'postalCode' => $billing_address->postcode,
						);

						if ($billing_address->id_country)
							$billing['countryCodeAlpha2'] = Country::getIsoById($billing_address->id_country);

						if ($billing_address->id_state)
						{
							$state = new State((int)$billing_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$billing['region'] = $state->iso_code;
						}

						$options['billing'] = $billing;
					}
				}
				//if the delivery address is set and valid, then send it
				if (isset($this->context->cart->id_address_delivery))
				{
					$delivery_address = new Address((int)$this->context->cart->id_address_delivery);
					if (Validate::isLoadedObject($delivery_address)) 
					{
						$shipping = array(
							'firstName' => $delivery_address->firstname,
							'lastName' => $delivery_address->lastname,
							'company' => $delivery_address->company,
							'streetAddress' => $delivery_address->address1,
							'extendedAddress' => $delivery_address->address2,
							'locality' => $delivery_address->city,
							'postalCode' => $delivery_address->postcode,
						);

						if ($delivery_address->id_country)
							$shipping['countryCodeAlpha2'] = Country::getIsoById($delivery_address->id_country);

						if ($delivery_address->id_state)
						{
							$state = new State((int)$delivery_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$shipping['region'] = $state->iso_code;
						}

						$options['shipping'] = $shipping;
					}
				}
			}
			else 
			{
				die('no longer supported');

				if ($braintree_3ds)
				{
					$payment_method_nonce = Tools::getValue('payment_method_nonce');

					if ($payment_method_nonce)
						$options['paymentMethodNonce'] = $payment_method_nonce;
					else 
					{
						if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
							Logger::addLog($this->l('Braintree - Exception occured').' Invalid payment method selected.  Please report issue to module developer ', 4, null, 'Cart', (int)$this->context->cart->id, true);

//						$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//						$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
						Tools::redirectLink($redirect_url);
					}
				}
				else
				{
					$creditCard = array(
						'number' => Tools::getValue('braintree-card-number'),
						'cvv' => Tools::getValue('braintree-card-cvc'),
						'expirationMonth' => Tools::getValue('month'),
						'expirationYear' => Tools::getValue('year'),
						'cardholderName' => $card_holder,
					);
					$options['creditCard'] = $creditCard;
				}

				//if the billing address is set and valid, then send it
				if (isset($this->context->cart->id_address_invoice))
				{
					$billing_address = new Address((int)$this->context->cart->id_address_invoice);
					if (Validate::isLoadedObject($billing_address)) 
					{
						$billing = array(
							'firstName' => $billing_address->firstname,
							'lastName' => $billing_address->lastname,
							'company' => $billing_address->company,
							'streetAddress' => $billing_address->address1,
							'extendedAddress' => $billing_address->address2,
							'locality' => $billing_address->city,
							'postalCode' => $billing_address->postcode,
						);

						if ($billing_address->id_country)
							$billing['countryCodeAlpha2'] = Country::getIsoById($billing_address->id_country);

						if ($billing_address->id_state)
						{
							$state = new State((int)$billing_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$billing['region'] = $state->iso_code;
						}

						$options['billing'] = $billing;
					}
				}
				//if the delivery address is set and valid, then send it
				if (isset($this->context->cart->id_address_delivery))
				{
					$delivery_address = new Address((int)$this->context->cart->id_address_delivery);
					if (Validate::isLoadedObject($delivery_address)) 
					{
						$shipping = array(
							'firstName' => $delivery_address->firstname,
							'lastName' => $delivery_address->lastname,
							'company' => $delivery_address->company,
							'streetAddress' => $delivery_address->address1,
							'extendedAddress' => $delivery_address->address2,
							'locality' => $delivery_address->city,
							'postalCode' => $delivery_address->postcode,
						);

						if ($delivery_address->id_country)
							$shipping['countryCodeAlpha2'] = Country::getIsoById($delivery_address->id_country);

						if ($delivery_address->id_state)
						{
							$state = new State((int)$delivery_address->id_state);
							if (Validate::isLoadedObject($state)) 
								$shipping['region'] = $state->iso_code;

						}

						$options['shipping'] = $shipping;
					}
				}

			}

			$this->recordTransaction($options, $this->context->cart->id, 'salerequest');
			$result = Braintree_Transaction::sale($options);
			$this->recordTransaction($result, $this->context->cart->id, 'saleresponse');

			if ($result->success)
			{
				$transaction = $result->transaction;
				$paymentInstrumentType = $transaction->paymentInstrumentType;
				$payment_instrument = $this->convertInstrumentType($paymentInstrumentType);
				$order_status = (int)Configuration::get('BRAINTREE_PAYMENT_ORDER_STATUS');
				$message = $this->l('Braintree Transaction Details:')."\n\n".
					$this->l('Braintree Transaction ID:').' '.$transaction->id."\n".
					$this->l('Status:').' '.$transaction->status."\n".
					$this->l('Instrument Type:').' '.$paymentInstrumentType."\n".
					$this->l('Processed on:').' '.$transaction->createdAt->format('Y-m-d h:i:s a e')."\n".
					$this->l('Currency:').' '.Tools::strtoupper($transaction->currencyIsoCode)."\n\n".
					$this->l('Initial Processor Response Details:')."\n".
					$this->l('Authorization Code:').' '.$transaction->processorAuthorizationCode."\n".
					$this->l('Response Code:').' '.$transaction->processorResponseCode."\n".
					$this->l('Response Text:').' '.$transaction->processorResponseText."\n\n";

				if ($paymentInstrumentType == 'credit_card')
				{	
					$message .=
					$this->l('Basic Fraud Details:')."\n".
					$this->l('CVC Check:').' '.$transaction->cvvResponseCode."\n".
					$this->l('Address 1 Check:').' '.$transaction->avsStreetAddressResponseCode."\n".
					$this->l('Postal Code Check:').' '.$transaction->avsPostalCodeResponseCode."\n".
					$this->l('AVS Check:').' '.$transaction->avsErrorResponseCode."\n";
				}

				//customer paid using a credit card from the vault.
				if ($paymentInstrumentType == 'credit_card' && $transaction->creditCard['token'] != '')
					$message .= "\n".$this->l('Note: You are using the Dropin UI and the customer paid with a Credit Card from their Vault.  Due to PCI Compliance regulations, Braintree is not permitted to store the CVV code in the Vault.  Therefore we anticipate that the CVC Check value will be an I (Not Provided).');

				//customer paid using a credit card and the merchant is using the Dropin UI
				if ($paymentInstrumentType == 'credit_card' && $isDropIn)
					$message .= "\n".$this->l('Note: You are using the Dropin UI and we expect the Address 1 Check value to be an I (Not Provided).  This is because the Dropin UI does not collect the Address from the customer.  Since the value is not collected, Braintree bypasses the checks.');

				if ($paymentInstrumentType == 'credit_card')
				{
					// In case of successful payment, the address / zip-code can however fail
					if (isset($transaction->avsStreetAddressResponseCode) && $transaction->avsStreetAddressResponseCode == 'N')
						$order_status = (int)Configuration::get('BRAINTREE_PENDING_ORDER_STATUS');
					if (isset($transaction->avsPostalCodeResponseCode) && $transaction->avsPostalCodeResponseCode == 'N')
						$order_status = (int)Configuration::get('BRAINTREE_PENDING_ORDER_STATUS');

					//warn if cvc check fails, this should only apply if the merchant has not configured 'basic fraud protection' in braintree dashboard to decline
					if (isset($transaction->cvvResponseCode) && $transaction->cvvResponseCode != 'M')
						$order_status = (int)Configuration::get('BRAINTREE_PENDING_ORDER_STATUS');
					//warn if avs check fails, this should only apply if the merchant has not configured 'basic fraud protection' in braintree dashboard to decline
					if (isset($transaction->avsErrorResponseCode) && $transaction->avsErrorResponseCode == 'E')
						$order_status = (int)Configuration::get('BRAINTREE_PENDING_ORDER_STATUS');
				}

				//up until PS v1.6.0.7, the payment method had to be equal to the modules displayName or the order confirmation page would not work
				$payment_method = $this->displayName;
				if (version_compare(_PS_VERSION_, '1.6.0.7', '>='))
					$payment_method = $this->convertInstrumentType($paymentInstrumentType);

				// Create the PrestaShop order in database
				$this->validateOrder((int)$this->context->cart->id, (int)$order_status, ($transaction->amount), $payment_method, $message, array(), null, false, $this->context->customer->secure_key);

				/** @since 1.5.0 Attach the Braintree Transaction ID to this Order */
				if (version_compare(_PS_VERSION_, '1.5', '>='))
				{
					$new_order = new Order((int)$this->currentOrder);
					if (Validate::isLoadedObject($new_order))
					{
						$payment = $new_order->getOrderPaymentCollection();
						if (isset($payment[0]))
						{
							$payment[0]->payment_method = pSQL($payment_instrument);
							$payment[0]->transaction_id = pSQL($transaction->id);
							$payment[0]->save();
						}
					}
				}

				// Store the transaction details
				if (isset($transaction->id))
					Db::getInstance()->Execute('
					INSERT INTO '._DB_PREFIX_.'braintree_transaction (type, id_braintree_customer, id_cart, id_order,
					id_transaction, amount, status, currency, cvc_check, line1_check, zip_check, avs_check, mode, date_add, payment_instrument_type)
					VALUES (\'payment\', '.(isset($transaction->customer['id']) ? (int)$transaction->customer['id'] : 0).', '.(int)$this->context->cart->id.', '.(int)$this->currentOrder.', \''.pSQL($transaction->id).'\',
					\''.($transaction->amount).'\', \''.($transaction->status == Braintree_Transaction::AUTHORIZED || $transaction->status == Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT || $transaction->status == Braintree_Transaction::AUTHORIZING || $transaction->status == Braintree_Transaction::SETTLED || $transaction->status == Braintree_Transaction::SETTLING ? 'paid' : 'unpaid').'\', \''.pSQL($transaction->currencyIsoCode).'\',
					'.($transaction->cvvResponseCode == 'M' ? 1 : 0).',
					'.($transaction->avsStreetAddressResponseCode == 'M' ? 1 : 0).',
					'.($transaction->avsPostalCodeResponseCode == 'M' ? 1 : 0).',
					'.($transaction->avsErrorResponseCode == '' ? 1 : 0).', \''.(Configuration::get('BRAINTREE_MODE') ? 'live' : 'test').'\', NOW(), \''.pSQL($paymentInstrumentType).'\')');

				// Redirect the user to the order confirmation page / history
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					$redirect = 'order-confirmation.php?id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;
				else
					$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;

				Tools::redirect($redirect);

			}
			else if ($result->transaction)
			{
//				print_r("Error processing transaction:");
//				print_r("<br>&nbsp;code: " . $result->transaction->processorResponseCode);
//				print_r("<br>&nbsp;text: " . $result->transaction->processorResponseText);
//				$this->pre($result);
//				die();
				
				$transaction = $result->transaction;

				//it is possible with Paypal to receive a status of "settlement_pending".  In this use case, we should create an order using a configured order status.
				if ($transaction->status == Braintree_Transaction::SETTLEMENT_PENDING)
				{
					$order_status = (int)Configuration::get('BRAINTREE_SETTLEMENT_PENDING_OS');
					$message = $this->l('Braintree Transaction Details:')."\n\n".
						$this->l('Braintree Transaction ID:').' '.$transaction->id."\n".
						$this->l('Status:').' '.$transaction->status."\n".
						$this->l('Processed on:').' '.$transaction->createdAt->format('Y-m-d h:i:s a e')."\n".
						$this->l('Currency:').' '.Tools::strtoupper($transaction->currencyIsoCode)."\n".
						$this->l('Instrument Type:').' '.($transaction->paymentInstrumentType )."\n";

					// Create the PrestaShop order in database
					$this->validateOrder((int)$this->context->cart->id, (int)$order_status, ($transaction->amount), $this->displayName, $message, array(), null, false, $this->context->customer->secure_key);

					/** @since 1.5.0 Attach the Braintree Transaction ID to this Order */
					if (version_compare(_PS_VERSION_, '1.5', '>='))
					{
						$new_order = new Order((int)$this->currentOrder);
						if (Validate::isLoadedObject($new_order))
						{
							$payment = $new_order->getOrderPaymentCollection();
							if (isset($payment[0]))
							{
								$payment_instrument = $this->convertInstrumentType($transaction->paymentInstrumentType);
								$payment[0]->payment_method = pSQL($payment_instrument);
								$payment[0]->transaction_id = pSQL($transaction->id);
								$payment[0]->save();
							}
						}
					}

					// Store the transaction details
					if (isset($transaction->id))
						Db::getInstance()->Execute('
						INSERT INTO '._DB_PREFIX_.'braintree_transaction (type, id_braintree_customer, id_cart, id_order,
						id_transaction, amount, status, currency, cvc_check, line1_check, zip_check, avs_check, mode, date_add, payment_instrument_type)
						VALUES (\'payment\', '.(isset($transaction->customer['id']) ? (int)$transaction->customer['id'] : 0).', '.(int)$this->context->cart->id.', '.(int)$this->currentOrder.', \''.pSQL($transaction->id).'\',
						\''.($transaction->amount).'\', \''.($transaction->status == Braintree_Transaction::AUTHORIZED || $transaction->status == Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT || $transaction->status == Braintree_Transaction::AUTHORIZING || $transaction->status == Braintree_Transaction::SETTLED || $transaction->status == Braintree_Transaction::SETTLING || $transaction->status == Braintree_Transaction::SETTLEMENT_PENDING ? 'paid' : 'unpaid').'\', \''.pSQL($transaction->currencyIsoCode).'\',
						'.($transaction->cvvResponseCode == 'M' ? 1 : 0).',
						'.($transaction->avsStreetAddressResponseCode == 'M' ? 1 : 0).',
						'.($transaction->avsPostalCodeResponseCode == 'M' ? 1 : 0).',
						'.($transaction->avsErrorResponseCode == '' ? 1 : 0).', \''.(Configuration::get('BRAINTREE_MODE') ? 'live' : 'test').'\', NOW(), \''.pSQL($transaction->paymentInstrumentType).'\')');

					// Redirect the user to the order confirmation page / history
					if (version_compare(_PS_VERSION_, '1.5', '<'))
						$redirect = 'order-confirmation.php?id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;
					else
						$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;

					Tools::redirect($redirect);

				}
				else 
				{
					$response = array(
						'status' => $transaction->status,
						'id' => $transaction->id,
						'processorResponseCode' => $transaction->processorResponseCode,
						'processorResponseText' => $transaction->processorResponseText,
						'avsErrorResponseCode' => $transaction->avsErrorResponseCode,
						'avsPostalCodeResponseCode' => $transaction->avsPostalCodeResponseCode,
						'avsStreetAddressResponseCode' => $transaction->avsStreetAddressResponseCode,
						'cvvResponseCode' => $transaction->cvvResponseCode,
						'gatewayRejectionReason' => $transaction->gatewayRejectionReason,
					);
					$message = '';
					foreach ($response as $key => $value)
						$message .= "$key: $value, ";

					if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
						Logger::addLog($this->l('Braintree - Payment transaction failed').' '.$message, 2, null, 'Cart', (int)$this->context->cart->id, true);

//					$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//					$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
					Tools::redirectLink($redirect_url);
				}
			}
			else
			{
				//validation problems (like cvv or expiration).  We log the errors for the merchant and redirect back to payment page so they can try again.
				if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
				{
					foreach ($result->errors->deepAll() as $error) 
					{
						$code = $error->code;
						$message = $error->message;

						Logger::addLog($this->l('Braintree - Validation Error occurred').' '.$code.': '.$message, 3, $code, 'Cart', (int)$this->context->cart->id, true);
					}
				}

//				$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//				$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
				Tools::redirectLink($redirect_url);
			}

		} catch(Braintree_Exception_Authentication $e) {
			$message = $e->getMessage();

			if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
				Logger::addLog($this->l('Braintree - Braintree_Exception_Authentication occurred').' '.$message, 4, null, 'Cart', (int)$this->context->cart->id, true);

//			$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//			$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
			Tools::redirectLink($redirect_url);

		} catch(Braintree_Exception $e) {
			$message = $e->getMessage();

			if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
				Logger::addLog($this->l('Braintree - Braintree_Exception occurred').' '.$message, 4, null, 'Cart', (int)$this->context->cart->id, true);

//			$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//			$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
			Tools::redirectLink($redirect_url);

		} catch (Exception $e) {
			$message = $e->getMessage();
			if (version_compare(_PS_VERSION_, '1.4.0.3', '>') && class_exists('Logger'))
				Logger::addLog($this->l('Braintree - Exception occured').' '.$message, 4, null, 'Cart', (int)$this->context->cart->id, true);

//			$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc.php' : 'order.php';
//			$location = $this->context->link->getPageLink($controller, true).(strpos($controller, '?') !== false ? '&' : '?').'step=3&bt_error=1#bt_error';
			Tools::redirectLink($redirect_url);
		}

	}

	/**
	 * Delete a Customer's Credit Card
	 *
	 * @return integer Credit Card deletion result (1 = worked, 0 = did not worked)
	 */
	public function deleteCreditCard()
	{
		return 0;
/* not yet implemented
		if (isset($this->context->cookie->id_customer) && $this->context->cookie->id_customer)
			return (int)Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'braintree_customer WHERE id_customer = '.(int)$this->context->cookie->id_customer);

		return 0;
*/
	}

	/**
	 * Check settings requirements to make sure the Braintree's module will work properly
	 *
	 * @return boolean Check result
	 */
	public function checkSettings()
	{
		$mode = Configuration::get('BRAINTREE_MODE');

		if ($mode)
			return Configuration::get('BRAINTREE_PUBLIC_KEY_LIVE') != '' && Configuration::get('BRAINTREE_PRIVATE_KEY_LIVE') != '' && Configuration::get('BRAINTREE_MERCHANTID_LIVE') != '' && Configuration::get('BRAINTREE_CLIENTSIDE_LIVE') != '';
		else
			return Configuration::get('BRAINTREE_PUBLIC_KEY_TEST') != '' && Configuration::get('BRAINTREE_PRIVATE_KEY_TEST') != '' && Configuration::get('BRAINTREE_MERCHANTID_TEST') != '' && Configuration::get('BRAINTREE_CLIENTSIDE_TEST') != '';
	}

	/**
	 * Check technical requirements to make sure the Braintree's module will work properly
	 *
	 * @return array Requirements tests results
	 */
	public function checkRequirements()
	{
		$tests = array('result' => true);
		$tests['curl'] = array('name' => $this->l('PHP cURL extension must be enabled on your server'), 'result' => function_exists('curl_init'));
		$tests['xmlwriter'] = array('name' => $this->l('PHP xmlwriter extension must be enabled on your server'), 'result' => extension_loaded('xmlwriter'));
		$tests['SimpleXML'] = array('name' => $this->l('PHP SimpleXML extension must be enabled on your server'), 'result' => extension_loaded('SimpleXML'));
		$tests['openssl'] = array('name' => $this->l('PHP openssl extension must be enabled on your server'), 'result' => extension_loaded('openssl'));
		$tests['dom'] = array('name' => $this->l('PHP dom extension must be enabled on your server'), 'result' => extension_loaded('dom'));
		$tests['hash'] = array('name' => $this->l('PHP hash extension must be enabled on your server'), 'result' => extension_loaded('hash'));
		if (Configuration::get('BRAINTREE_MODE') && Configuration::get('BRAINTREE_PAYPAL_ENABLED'))
			$tests['ssl'] = array('name' => $this->l('Warning: Using Paypal on Live mode requires an SSL certificate.'), 'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));
		$tests['php52'] = array('name' => $this->l('Your server must run PHP 5.2.1 or greater'), 'result' => version_compare(PHP_VERSION, '5.2.1', '>='));
		$tests['configuration'] = array('name' => $this->l('Your must sign-up for Braintree and configure your account settings in the module (publishable key, secret key...etc.)'), 'result' => $this->checkSettings());

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$tests['backward'] = array('name' => $this->l('You are using the backward compatibility module'), 'result' => $this->backward, 'resolution' => $this->backward_error);
			$tmp = Module::getInstanceByName('mobile_theme');
			if ($tmp && isset($tmp->version) && !version_compare($tmp->version, '0.3.8', '>='))
				$tests['mobile_version'] = array('name' => $this->l('You are currently using the default mobile template, the minimum version required is v0.3.8').' (v'.$tmp->version.' '.$this->l('detected').' - <a target="_blank" href="http://addons.prestashop.com/en/mobile-iphone/6165-prestashop-mobile-template.html">'.$this->l('Please Upgrade').'</a>)', 'result' => version_compare($tmp->version, '0.3.8', '>='));
		}

		foreach ($tests as $k => $test)
			if ($k != 'result' && !$test['result'])
				$tests['result'] = false;

		return $tests;
	}

	/**
	 * Display the Back-office interface of the Braintree's module
	 *
	 * @return string HTML/JS Content
	 */
	public function getContent()
	{
		// If 1.4 and no backward, then leave
		if (!$this->backward)
			return false;

		$output = '';
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$output .= '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';

		$currencies = Currency::getCurrencies(false, 1);	//only active

		$output .= '
		<link href="'.$this->_path.'views/css/braintree-prestashop-admin.css" rel="stylesheet" type="text/css" media="all" />
		<div class="braintree-module-wrapper">';

		/* Update Configuration Values when settings are updated */
		if (Tools::isSubmit('SubmitBraintree'))
		{
			$configuration_values = array(
				'BRAINTREE_MODE' => Tools::getValue('braintree_mode'), 
				'BRAINTREE_SAVE_TOKENS' => Tools::getValue('braintree_save_tokens'),
				'BRAINTREE_SAVE_TOKENS_ASK' => Tools::getValue('braintree_save_tokens_ask'), 
				'BRAINTREE_PUBLIC_KEY_TEST' => trim(Tools::getValue('braintree_public_key_test')),
				'BRAINTREE_PUBLIC_KEY_LIVE' => trim(Tools::getValue('braintree_public_key_live')), 
				'BRAINTREE_PRIVATE_KEY_TEST' => trim(Tools::getValue('braintree_private_key_test')),
				'BRAINTREE_PRIVATE_KEY_LIVE' => trim(Tools::getValue('braintree_private_key_live')), 
				'BRAINTREE_PENDING_ORDER_STATUS' => (int)Tools::getValue('braintree_pending_status'),
				'BRAINTREE_PAYMENT_ORDER_STATUS' => (int)Tools::getValue('braintree_payment_status'), 
				'BRAINTREE_MERCHANTID_TEST' => trim(Tools::getValue('braintree_merchantid_test')), 
				'BRAINTREE_MERCHANTID_LIVE' => trim(Tools::getValue('braintree_merchantid_live')), 
				'BRAINTREE_CLIENTSIDE_TEST' => trim(Tools::getValue('braintree_clientside_test')), 
				'BRAINTREE_CLIENTSIDE_LIVE' => trim(Tools::getValue('braintree_clientside_live')), 
				'BRAINTREE_SUBMIT_SETTLE' => (int)Tools::getValue('braintree_submit_settle'),
				'BRAINTREE_PAYPAL_ENABLED' => (int)Tools::getValue('braintree_paypal_enabled'),
				'BRAINTREE_PAYPAL_FUTURE' => (int)Tools::getValue('braintree_paypal_future'),
				'BRAINTREE_LOG_TRANSACTIONS' => (int)Tools::getValue('braintree_log_transactions'),
				'BRAINTREE_SETTLEMENT_PENDING_OS' => (int)Tools::getValue('braintree_settlement_pending_os'),
				'BRAINTREE_UI_MODE' => (int)Tools::getValue('braintree_ui_mode'),
				'BRAINTREE_CARD_VISA' => (Tools::getValue('braintree_card_visa') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_MASTERCARD' => (Tools::getValue('braintree_card_mastercard') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_AMEX' => (Tools::getValue('braintree_card_amex') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_JCB' => (Tools::getValue('braintree_card_jcb') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_DISCOVER' => (Tools::getValue('braintree_card_discover') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_DINERS' => (Tools::getValue('braintree_card_diners') == 'on' ? 1 : 0),
				'BRAINTREE_CARD_MAESTRO' => (Tools::getValue('braintree_card_maestro') == 'on' ? 1 : 0),
				'BRAINTREE_CHECKOUT_MODE' => (int)Tools::getValue('braintree_checkout_mode'), 
				'BRAINTREE_3DS' => (int)Tools::getValue('braintree_3ds'),
				'BRAINTREE_HOSTED_POSTCODE' => (int)Tools::getValue('braintree_hosted_postcode'),
			);

			foreach ($configuration_values as $configuration_key => $configuration_value)
				Configuration::updateValue($configuration_key, $configuration_value);

			foreach ($currencies as $currency)
			{
				Configuration::updateValue('BRAINTREE_CURRENCY_LIVE_'.$currency['iso_code'], trim(Tools::getValue('braintree_currency_live_'.$currency['iso_code'])));
				Configuration::updateValue('BRAINTREE_CURRENCY_TEST_'.$currency['iso_code'], trim(Tools::getValue('braintree_currency_test_'.$currency['iso_code'])));
			}

			$output .= '
				<fieldset>
					<legend><img src="'.$this->_path.'views/img/checks-icon.gif" alt="" />'.$this->l('Confirmation').'</legend>
					<div class="form-group">
						<div class="col-lg-9">
							<div class="conf confirmation">'.$this->l('Settings successfully saved').'</div>
						</div>
					</div>
				</fieldset>';

			try
			{
				//perform a config test by executing a transaction sale.  
				//We don't care about the results, since the transaction will result in failure.  
				//We only care if we receive an Exception since that means implies a configuration issue
				$bt_env = Configuration::get('BRAINTREE_MODE') ? Braintreejs::ENV_PRODUCTION : Braintreejs::ENV_SANDBOX;
				$this->setupBraintreeEnv($bt_env);
				$merchant_account_id = Configuration::get('BRAINTREE_MODE') ? Configuration::get('BRAINTREE_CURRENCY_LIVE_'.Currency::getDefaultCurrency()->iso_code) : Configuration::get('BRAINTREE_CURRENCY_TEST_'.Currency::getDefaultCurrency()->iso_code);

				//we try to create a Transaction
				Braintree_Transaction::sale(array(
						'amount' => '1.00',
						'merchantAccountId' => $merchant_account_id,
						'paymentMethodNonce' => '99999999999999999999',	//this should always fail as an invalid paymentMethodNonce
						'customer' => array(
						'firstName' => 'config_test',
						'lastName' => 'config_test',
					),
						'options' => array(
						'submitForSettlement' => true
					),
				));
			}
			catch(Braintree_Exception $e)
			{
//				$message = $e->getMessage();

				$output .= '
					<fieldset>
						<legend><img src="'.$this->_path.'views/img/checks-icon.gif" alt="" />'.$this->l('Configuration Issue').'</legend>
						<div class="form-group">
							<div class="col-lg-9">
								<div class="alert alert-danger clearfix">
									<span style="color:red">'.$this->l('There is an issue with your configuration settings').'<br></span>
									<span style="color:red">'.$this->l('The most common issue is the "Merchant Account Id" field. This is not the same as the Merchant Id').'<br></span>
									<span style="color:red">'.$this->l('Please read through the Module Documentation which explains how to locate the correct settings within your Braintree Dashboard').'</span>
								</div>
							</div>
						</div>
					</fieldset>';
			}
		}

		$requirements = $this->checkRequirements();

		$output .= '
			<fieldset>
				<legend><img src="'.$this->_path.'views/img/checks-icon.gif" alt="" />'.$this->l('Technical Checks').'</legend>
				<div class="'.($requirements['result'] ? 'conf">'.$this->l('Good news! All the checks were successfully performed. You can now configure your module and start using Braintree.') :
				'warn">'.$this->l('Unfortunately, at least one issue is preventing you from using Braintree. Please fix the issue and reload this page.')).'</div>
				<table cellspacing="0" cellpadding="0" class="braintree-technical">';
				foreach ($requirements as $k => $requirement)
					if ($k != 'result')
						$output .= '
						<tr>
							<td><img src="../img/admin/'.($requirement['result'] ? 'ok' : 'forbbiden').'.gif" alt="" /></td>
							<td>'.$requirement['name'].(!$requirement['result'] && isset($requirement['resolution']) ? '<br />'.Tools::safeOutput($requirement['resolution'], true) : '').'</td>
						</tr>';
				$output .= '
				</table>
			</fieldset>';

		$output .= '
			<fieldset class="braintree-documentation">
				<legend><img src="'.$this->_path.'views/img/checks-icon.gif" alt="" />'.$this->l('Module Documentation').'</legend>
				<div class="form-group">
					<div class="col-lg-9">
						<div class="alert conf alert-info">
							<a target="_new" class="addImageDescription" href="'.$this->_path.'readme_en.pdf">Click here</a> to read the Modules Documentation
						</div>
					</div>
				</div>
			</fieldset>';

		$statuses = OrderState::getOrderStates((int)$this->context->cookie->id_lang);
		$output .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="braintree-settings">
				<legend><img src="'.$this->_path.'views/img/technical-icon.gif" alt="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Mode').'</label>
				<input type="radio" name="braintree_mode" value="0"'.(!Configuration::get('BRAINTREE_MODE') ? ' checked="checked"' : '').' /> Test
				<input type="radio" name="braintree_mode" value="1"'.(Configuration::get('BRAINTREE_MODE') ? ' checked="checked"' : '').' /> Live
				<br /><br />
				<input type="hidden" name="braintree_save_tokens" value="0"/>
				<input type="hidden" name="braintree_save_tokens_ask" value="0"/>

				<table cellspacing="0" cellpadding="0" class="braintree-settings">';
				$output .= '
					<tr>
						<td align="center" valign="middle" colspan="2">
							<table cellspacing="0" cellpadding="0" class="innerTable">
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Merchant Id').'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_merchantid_test" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_MERCHANTID_TEST')).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Merchant Id').'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_merchantid_live" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_MERCHANTID_LIVE')).'" /></td>
								</tr>
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Client-Side Encryption Key').'</td>
									<td align="left" valign="middle"><textarea rows="4" cols="22" name="braintree_clientside_test">'.Tools::safeOutput(Configuration::get('BRAINTREE_CLIENTSIDE_TEST')).'</textarea></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Client-Side Encryption Key').'</td>
									<td align="left" valign="middle"><textarea rows="4" cols="22" name="braintree_clientside_live">'.Tools::safeOutput(Configuration::get('BRAINTREE_CLIENTSIDE_LIVE')).'</textarea>
								</tr>
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Public Key').'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_public_key_test" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_PUBLIC_KEY_TEST')).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Public Key').'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_public_key_live" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_PUBLIC_KEY_LIVE')).'" /></td>
								</tr>
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Private Key').'</td>
									<td align="left" valign="middle"><input type="password" name="braintree_private_key_test" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_PRIVATE_KEY_TEST')).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Private Key').'</td>
									<td align="left" valign="middle"><input type="password" name="braintree_private_key_live" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_PRIVATE_KEY_LIVE')).'" /></td>
								</tr>';

							foreach ($currencies as $currency)
							{
								$output .= '
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Merchant Account Id for ').$currency['iso_code'].'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_currency_test_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_CURRENCY_TEST_'.$currency['iso_code'])).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Merchant Account Id for ').$currency['iso_code'].'</td>
									<td align="left" valign="middle"><input type="text" name="braintree_currency_live_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Configuration::get('BRAINTREE_CURRENCY_LIVE_'.$currency['iso_code'])).'" /></td>
								</tr>';
							}

						$output .= '
							</table>
						</td>
					</tr>';

					$statuses_options = array(
							array('name' => 'braintree_payment_status', 'label' => $this->l('Order status to use when Payment is Authorized:'), 'current_value' => Configuration::get('BRAINTREE_PAYMENT_ORDER_STATUS')),
							array('name' => 'braintree_pending_status', 'label' => $this->l('Order status to use when Payment is Authorized but has address/zip-code/cvc failures:'), 'current_value' => Configuration::get('BRAINTREE_PENDING_ORDER_STATUS')),
							array('name' => 'braintree_settlement_pending_os', 'label' => $this->l('Order status to use when Paypal Settlement is Pending:'), 'current_value' => Configuration::get('BRAINTREE_SETTLEMENT_PENDING_OS')),
							//not implement, braintree does not support webhooks for chargebacks
							//array('name' => 'braintree_chargebacks_status', 'label' => $this->l('Order status in case of a chargeback (dispute):'), 'current_value' => Configuration::get('BRAINTREE_CHARGEBACK_ORDERSTATUS'))
						);
					foreach ($statuses_options as $status_options)
					{
						$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$status_options['label'].'</label></td>
							<td align="left" valign="middle" class="td-right">
								<select name="'.$status_options['name'].'">';
									foreach ($statuses as $status)
										$output .= '<option value="'.(int)$status['id_order_state'].'"'.($status['id_order_state'] == $status_options['current_value'] ? ' selected="selected"' : '').'>'.Tools::safeOutput($status['name']).'</option>';
						$output .= '
								</select>
							</td>
						</tr>';
					}

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Submit for Settlement when transaction is created?:').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_submit_settle" value="0"'.(!Configuration::get('BRAINTREE_SUBMIT_SETTLE') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_submit_settle" value="1"'.(Configuration::get('BRAINTREE_SUBMIT_SETTLE') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Enable Dropin UI?:').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_ui_mode" value="0"'.(!Configuration::get('BRAINTREE_UI_MODE') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_ui_mode" value="1"'.(Configuration::get('BRAINTREE_UI_MODE') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Enable 3D Secure?:').'<br>'.$this->l('Note: Only applies when Dropin UI is disabled').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_3ds" value="0"'.(!Configuration::get('BRAINTREE_3DS') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_3ds" value="1"'.(Configuration::get('BRAINTREE_3DS') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Use Postal Code?:').'<br>'.$this->l('Note: Only applies when Dropin UI is disabled').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_hosted_postcode" value="0"'.(!Configuration::get('BRAINTREE_HOSTED_POSTCODE') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_hosted_postcode" value="1"'.(Configuration::get('BRAINTREE_HOSTED_POSTCODE') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Enable Paypal payment option?:').'<br>'.$this->l('Note: When using Dropin UI, you need to enable Paypal in your Braintree Account.').'<br>'.$this->l('When using Dedicated Checkout Mode, then this option toggles the display of "or Paypal".').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_paypal_enabled" value="0"'.(!Configuration::get('BRAINTREE_PAYPAL_ENABLED') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_paypal_enabled" value="1"'.(Configuration::get('BRAINTREE_PAYPAL_ENABLED') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Enable Paypal Future Payments?:').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_paypal_future" value="0"'.(!Configuration::get('BRAINTREE_PAYPAL_FUTURE') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_paypal_future" value="1"'.(Configuration::get('BRAINTREE_PAYPAL_FUTURE') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$this->l('Enable Transaction Logs (debug)?:').'<br>'.$this->l('Note: Only enable when requested by our support team').'</label></td>
								<td align="left" valign="middle" class="td-right">
								<input type="radio" name="braintree_log_transactions" value="0"'.(!Configuration::get('BRAINTREE_LOG_TRANSACTIONS') ? ' checked="checked"' : '').' /> No
								<input type="radio" name="braintree_log_transactions" value="1"'.(Configuration::get('BRAINTREE_LOG_TRANSACTIONS') ? ' checked="checked"' : '').' /> Yes
								<br /><br />
							</td>
						</tr>';

					$output .= '
				</table>
			</fieldset>

			<fieldset class="braintree-display-settings">
				<legend><img src="'.$this->_path.'views/img/technical-icon.gif" alt="" />'.$this->l('Display Settings').'</legend>
				<p>'.$this->l('Use these settings to control what credit card logos are displayed during checkout when using Dedicated Checkout mode.  This has no bearing on what credit cards Braintree allows you to accept.').'</p>
				<label for="braintree_cards">'.$this->l('Accepted Cards:').'</label>
				<div class="margin-form" id="braintree_cards">
					<input type="checkbox" name="braintree_card_visa" '.(Configuration::get('BRAINTREE_CARD_VISA') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-visa.png" alt="visa" />
					<input type="checkbox" name="braintree_card_mastercard" '.(Configuration::get('BRAINTREE_CARD_MASTERCARD') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-mastercard.png" alt="visa" />
					<input type="checkbox" name="braintree_card_amex" '.(Configuration::get('BRAINTREE_CARD_AMEX') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-amex.png" alt="visa" />
					<input type="checkbox" name="braintree_card_jcb" '.(Configuration::get('BRAINTREE_CARD_JCB') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-jcb.png" alt="visa" />
					<input type="checkbox" name="braintree_card_discover" '.(Configuration::get('BRAINTREE_CARD_DISCOVER') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-discover.png" alt="visa" />
					<input type="checkbox" name="braintree_card_diners" '.(Configuration::get('BRAINTREE_CARD_DINERS') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-diners.png" alt="visa" />
					<input type="checkbox" name="braintree_card_maestro" '.(Configuration::get('BRAINTREE_CARD_MAESTRO') ? 'checked="checked"' : '').' />
						<img src="../modules/'.$this->name.'/views/img/cc-maestro.png" alt="visa" />
				</div>

				<p>'.$this->l('Use the following setting to control where the payment form is located.  Inline means the payment form will display in the payment method selection list.  Dedicated means the payment form will appear on a separate page').'</p>
				<label for="braintree_checkout_mode">'.$this->l('Checkout Mode:').'</label>
				<div class="margin-form" id="braintree_checkout_mode">
					<input type="radio" name="braintree_checkout_mode" value="0"'.(!Configuration::get('BRAINTREE_CHECKOUT_MODE') ? ' checked="checked"' : '').' /> Inline
					<input type="radio" name="braintree_checkout_mode" value="1"'.(Configuration::get('BRAINTREE_CHECKOUT_MODE') ? ' checked="checked"' : '').' /> Dedicated
				</div>
			</fieldset>

			<fieldset class="braintree-cc-numbers">
				<legend><img src="'.$this->_path.'views/img/cc-icon.gif" alt="" />'.$this->l('Test Credit Card Numbers').'</legend>
				<p>These cards may change over time, for the latest list of cards, visit the <a target="_new" href="https://developers.braintreepayments.com/javascript+php/reference/general/testing">Braintree Test Page</a></p>
				<table cellspacing="0" cellpadding="0" class="braintree-cc-numbers">
				  <thead>
					<tr>
					  <th>'.$this->l('Number').'</th>
					  <th>'.$this->l('Card type').'</th>
					</tr>
				  </thead>
				  <tbody>
					<tr><td class="number"><code>4111111111111111</code></td><td>Visa</td></tr>
					<tr><td class="number"><code>4009348888881881</code></td><td>Visa</td></tr>
					<tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
					<tr><td class="number"><code>378282246310005</code></td><td>American Express</td></tr>
					<tr><td class="number"><code>371449635398431</code></td><td>American Express</td></tr>
					<tr><td class="number"><code>6011111111111117</code></td><td>Discover</td></tr>
					<tr><td class="number last"><code>3530111333300000</code></td><td class="last">JCB</td></tr>
				  </tbody>
				</table>
			</fieldset>
			<fieldset class="braintree-paypal-testing">
				<legend><img src="'.$this->_path.'views/img/cc-icon.gif" alt="" />'.$this->l('Testing Paypal').'</legend>
				<p>When testing the Paypal payment method during checkout in Sandbox mode, you may use any random email and password.  Braintree will ignore what you provide and default to a test account automatically.</p>
			</fieldset>
			<div class="clear"></div>
			<fieldset class="braintree-submit-form">
				<div>
					<td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitBraintree" value="'.$this->l('Save Settings').'" /></td>
				</div>
			</fieldset>
		</div>
		</form>
		<script type="text/javascript">
			function updateBraintreeSettings()
			{
				if ($(\'input:radio[name=braintree_mode]:checked\').val() == 1)
				{
					$(\'fieldset.braintree-cc-numbers\').hide();
					$(\'fieldset.braintree-paypal-testing\').hide();
				}
				else
				{
					$(\'fieldset.braintree-cc-numbers\').show(1000);
					$(\'fieldset.braintree-paypal-testing\').show(1000);
				}
			}

			$(\'input:radio[name=braintree_mode]\').click(function() { updateBraintreeSettings(); });
			$(\'input:radio[name=braintree_save_tokens]\').click(function() { updateBraintreeSettings(); });
			$(document).ready(function() { updateBraintreeSettings(); });
		</script>';

		return $output;
	}

	/*
	* This function sets up the Braintree SDK
	* environment can be either 'sandbox' or 'production'.  It defaults to 'sandbox'
	*/
	private function setupBraintreeEnv($environment = Braintreejs::ENV_SANDBOX)
	{
		$config = Configuration::getMultiple(array('BRAINTREE_MODE', 'BRAINTREE_MERCHANTID_TEST', 'BRAINTREE_PUBLIC_KEY_TEST', 'BRAINTREE_PRIVATE_KEY_TEST', 'BRAINTREE_MERCHANTID_LIVE', 'BRAINTREE_PUBLIC_KEY_LIVE', 'BRAINTREE_PRIVATE_KEY_LIVE'));

		include_once(dirname(__FILE__).'/lib/Braintree.php');

		//default to sandbox settings
		$merchant_id = $config['BRAINTREE_MERCHANTID_TEST'];
		$public_key = $config['BRAINTREE_PUBLIC_KEY_TEST'];
		$private_key = $config['BRAINTREE_PRIVATE_KEY_TEST'];
		if ($environment == Braintreejs::ENV_PRODUCTION)
		{
			$merchant_id = $config['BRAINTREE_MERCHANTID_LIVE'];
			$public_key = $config['BRAINTREE_PUBLIC_KEY_LIVE'];
			$private_key = $config['BRAINTREE_PRIVATE_KEY_LIVE'];
		}

		//verify the data is not empty
		if (empty($merchant_id) || empty($public_key) || empty($private_key))
			return false;

		Braintree_Configuration::environment($environment);
		Braintree_Configuration::merchantId($merchant_id);
		Braintree_Configuration::publicKey($public_key);
		Braintree_Configuration::privateKey($private_key);

		return true;
	}

	public function fetchTemplate($name)
	{
		if (version_compare(_PS_VERSION_, '1.4', '<'))
			$this->context->smarty->currentTemplate = $name;
		elseif (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			$views = 'views/templates/';
			if (@filemtime(dirname(__FILE__).'/'.$name))
				return $this->display(__FILE__, $name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'hook/'.$name))
				return $this->display(__FILE__, $views.'hook/'.$name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'front/'.$name))
				return $this->display(__FILE__, $views.'front/'.$name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'admin/'.$name))
				return $this->display(__FILE__, $views.'admin/'.$name);
		}
		return $this->display(__FILE__, $name);
	}

	public function pre($data) 
	{
		print '<pre>'.print_r($data, true).'</pre>';
	}

	public function recordTransaction($data, $id_cart, $transaction_type)
	{
		$record = Configuration::get('BRAINTREE_LOG_TRANSACTIONS');
		if (!$record)
			return;

		$time = time();
		$location = dirname(__FILE__).'/logs/'.$id_cart.'_'.$transaction_type.'_'.$time.'.log';
		error_log($time.': '.print_r($data, true), 3, $location);
	}

	public function convertTransactionStatus($type)
	{
		if ($type == 'authorization_expired')
			return 'Authorization Expired';
		else if ($type == 'authorizing')
			return 'Authorizing'; 
		else if ($type == 'authorized')
			return 'Authorized'; 
		else if ($type == 'gateway_rejected')
			return 'Gateway Rejected'; 
		else if ($type == 'failed')
			return 'Failed'; 
		else if ($type == 'processor_declined')
			return 'Processor Declined'; 
		else if ($type == 'settled')
			return 'Settled'; 
		else if ($type == 'settling')
			return 'Settling'; 
		else if ($type == 'submitted_for_settlement')
			return 'Submitted For Settlement'; 
		else if ($type == 'voided')
			return 'Voided'; 
		else if ($type == 'unrecognized')
			return 'Unrecognized'; 
		else if ($type == 'settlement_declined')
			return 'Settlement Declined'; 
		else if ($type == 'settlement_pending')
			return 'Settlement Pending'; 
		else
			return $type;
	}

	public function convertInstrumentType($type)
	{
		if ($type == 'paypal_account')
			return 'Paypal';
		else
			return 'Credit Card';
	}

	public function getModuleLink($module, $controller = 'default', array $params = array(), $ssl = null)
	{
		//bellini: need to append '.php' to the end of the controller for PS v1.4.  Cannot assume Friendly URL is turned on, so either need to add a check, or append .php
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$link = Tools::getShopDomainSsl(true)._MODULE_DIR_.$module.'/'.$controller.'.php';
		else
			$link = $this->context->link->getModuleLink($module, $controller, $params, $ssl);
			
		return $link;
	}

	private static function getLocale($language_iso_code)
	{
		if (in_array($language_iso_code, Braintreejs::$languageISO))
			return Braintreejs::$languageISO[$languageISO];		//validator is wrong, the variable does exist
		else
			return 'en_us';
	}

//		  "au" => "en_au",	//no language pack exists for Australia, will default to en_us
//		  "??" => "en_be",	// English (Belgium)
//		  "??" => "en_ca",	//English (Canada)
//		  "??" => "de_at",	//German (Austria)
//		  "??" => "en_ch",	//??
	private static $languageISO = array(
		"en" => "en_us",
		"de" => "de_de",	//German (Germany)
		"da" => "da_dk",	//Danish (Denmark)
		"gb" => "en_gb",	//English (United Kingdom)
		"fr" => "fr_fr",	//French (France)
		"zh" => "zh_hk",	//Simplified Chinese
		"it" => "it_it",	//Italian (Italy)
		"nl" => "nl_nl",	//Dutch (Netherlands)
		"no" => "no_no",	//Norwegian
		"pl" => "pl_pl",	//Polish (Poland)
		"es" => "es_es",	//Spanish (Spain)
		"sv" => "sv_se",	//Swedish (Sweden)
		"tr" => "tr_tr"		//Turkish (Turkey)
	);
}