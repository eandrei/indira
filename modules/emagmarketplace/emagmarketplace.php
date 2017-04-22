<?php

if (!defined('_PS_VERSION_'))
	exit;
	
include_once(dirname(__FILE__).'/classes/emagmarketplaceapicall.php');
@include_once(dirname(__FILE__).'/classes/emagmarketplacecustomization.php');

class EmagMarketplace extends Module
{
	public $global_settings = array(
		'EMAGMP_URL' => 'https://marketplace.emag.ro',
		'EMAGMP_API_URL' => 'https://marketplace.emag.ro/api-3',
		'EMAGMP_PROTOCOL' => 'HTTPS',
		'EMAGMP_LOCALE' => 'ro_RO',
		'EMAGMP_CURRENCY' => 'RON',
		'EMAGMP_VENDORCODE' => '',
		'EMAGMP_VENDORUSERNAME' => '',
		'EMAGMP_VENDORPASSWORD' => '',
		'EMAGMP_PRODUCT_QUEUE_LIMIT' => '20',
		'EMAGMP_PRODUCT_DESCRIPTION_TYPE' => 'long',
		'EMAGMP_ORDER_DELIVERY_OPTION_ID' => 2,
		'EMAGMP_ORDER_STATE_ID_INITIAL' => 10,
		'EMAGMP_ORDER_STATE_ID_FINALIZED' => 4,
		'EMAGMP_ORDER_STATE_ID_CANCELLED' => 6,
		'EMAGMP_HANDLING_TIME' => 1,
		'EMAGMP_USE_EMAG_AWB' => 1,
		'EMAGMP_AWB_SENDER_NAME' => '',
		'EMAGMP_AWB_SENDER_CONTACT' => '',
		'EMAGMP_AWB_SENDER_PHONE' => '',
		'EMAGMP_AWB_SENDER_LOCALITY' => '',
		'EMAGMP_AWB_SENDER_STREET' => '',
	);
	
	private $admin_tabs = array(
		'AdminEmagMarketplaceConfig' => array('name' => 'Main Configuration', 'active' => 1),
		'AdminEmagMarketplaceCategories' => array('name' => 'Category Mapping', 'active' => 1),
		'AdminEmagMarketplaceCharacteristics' => array('name' => 'Characteristic Mapping', 'active' => 1),
		'AdminEmagMarketplaceMain' => array('name' => 'API Call Logs', 'active' => 1),
		'AdminEmagMarketplaceInvoices' => array('name' => 'Invoices', 'active' => 0)
	);
	
	public $path;
	
	public function __construct()
	{
		$this->name = 'emagmarketplace';
		$this->tab = 'market_place';
		$this->version = '1.0.6';
		$this->author = 'S.C. Online Business Solutions S.R.L. Romania';
		$this->module_key = '45c601756bfc8c3a220b742a3fab7156';
		$this->need_instance = 0;
		//$this->ps_versions_compliancy = array('min' => '1.5.0.15', 'max' => '1.6.0.14');

		parent::__construct();
		
		$this->path = $this->_path;

		$this->displayName = $this->l('eMAG Marketplace');
		$this->description = $this->l('Lists your products on the eMAG Marketplace, keeps stock info updated in real time and brings back orders from the eMAG Marketplace to your store. Everything done automagically.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (class_exists('EmagMarketplaceCustomization'))
			$this->customizationObject = new EmagMarketplaceCustomization();
	}
	
	public function install()
	{
		if (!function_exists('curl_version'))
			return false;
			
		if (Shop::isFeatureActive()) {
			Shop::setContext(Shop::CONTEXT_ALL);
		}
		
		foreach ($this->global_settings as $key => $value)
		{
			if (!Configuration::get($key))
				Configuration::updateValue($key, $value);
		}
		
		$parent_tab = $this->installModuleTab('AdminEmagMarketplaceMain', 'eMAG Marketplace', 0, 1);
		foreach ($this->admin_tabs as $key => $config)
		{
			$this->installModuleTab($key, $config['name'], $parent_tab->id, $config['active']);
		}
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_category_definitions` (
				`emag_category_id` int(11) unsigned NOT NULL,
				`emag_category_name` varchar(255) NOT NULL,
				PRIMARY KEY (`emag_category_id`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_characteristic_definitions` (
				`emag_characteristic_id` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) unsigned NOT NULL,
				`emag_characteristic_name` varchar(255) NOT NULL,
				`display_order` int(11) NOT NULL,
				UNIQUE KEY `emag_characteristic_id` (`emag_characteristic_id`,`emag_category_id`),
				KEY `emag_category_id` (`emag_category_id`),
				KEY `display_order` (`display_order`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_family_type_definitions` (
				`emag_family_type_id` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) unsigned NOT NULL,
				`emag_family_type_name` varchar(255) NOT NULL,
				UNIQUE KEY `emag_family_type_id` (`emag_family_type_id`,`emag_category_id`),
				KEY `emag_category_id` (`emag_category_id`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_locality_definitions` (
				`emag_locality_id` int(11) unsigned NOT NULL,
				`emag_region2_latin` varchar(255) NOT NULL,
				`emag_region3_latin` varchar(255) NOT NULL,
				`emag_name_latin` varchar(255) NOT NULL,
				PRIMARY KEY (`emag_locality_id`),
				KEY `emag_region2_latin` (`emag_region2_latin`),
				KEY `emag_region3_latin` (`emag_region3_latin`),
				KEY `emag_name_latin` (`emag_name_latin`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_categories` (
				`id_emagmp_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_category` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) unsigned NOT NULL,
				`emag_family_type_id` int(11) unsigned NOT NULL,
				`commission` decimal(7,4) NOT NULL,
				`sync_active` tinyint(1) NOT NULL,
				PRIMARY KEY (`id_emagmp_category`),
				UNIQUE KEY `id_category` (`id_category`),
				KEY `emag_category_id` (`emag_category_id`),
				KEY `sync_active` (`sync_active`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_products` (
				`id_emagmp_product` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_product` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) NOT NULL,
				`emag_family_type_id` int(11) NOT NULL,
				`commission` decimal(7,4) NOT NULL,
				PRIMARY KEY (`id_emagmp_product`),
				UNIQUE KEY `id_product` (`id_product`),
				KEY `emag_category_id` (`emag_category_id`),
				KEY `emag_family_type_id` (`emag_family_type_id`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_features` (
				`id_emagmp_feature` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`emag_characteristic_id` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) unsigned NOT NULL,
				`id_feature` int(11) unsigned NOT NULL,
				PRIMARY KEY (`id_emagmp_feature`),
				UNIQUE KEY `emag_characteristic_id` (`emag_characteristic_id`,`emag_category_id`),
				KEY `emag_category_id` (`emag_category_id`),
				KEY `id_feature` (`id_feature`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_attribute_groups` (
				`id_emagmp_attribute_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`emag_characteristic_id` int(11) unsigned NOT NULL,
				`emag_category_id` int(11) unsigned NOT NULL,
				`id_attribute_group` int(11) unsigned NOT NULL,
				PRIMARY KEY (`id_emagmp_attribute_group`),
				UNIQUE KEY `emag_characteristic_id` (`emag_characteristic_id`,`emag_category_id`),
				KEY `emag_category_id` (`emag_category_id`),
				KEY `id_attribute_group` (`id_attribute_group`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_api_calls` (
				`id_emagmp_api_call` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`date_created` datetime NOT NULL,
				`resource` varchar(20) NOT NULL,
				`action` varchar(20) NOT NULL,
				`last_definition` longtext NOT NULL,
				`message_out` longtext NOT NULL,
				`message_in` longtext NOT NULL,
				`status` varchar(20) NOT NULL,
				`date_sent` datetime NOT NULL,
				`id_order` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_emagmp_api_call`),
				KEY `date_created` (`date_created`),
				KEY `status` (`status`),
				KEY `date_sent` (`date_sent`),
				KEY `id_order` (`id_order`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_product_combinations` (
				`combination_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_product` int(11) unsigned NOT NULL,
				`id_product_attribute` int(11) unsigned NOT NULL,
				`last_definition` longtext NOT NULL,
				PRIMARY KEY (`combination_id`),
				UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_order_history` (
				`id_emagmp_order_history` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`emag_order_id` int(11) unsigned NOT NULL,
				`original_emag_definition` longtext NOT NULL,
				`emag_definition` longtext NOT NULL,
				`id_order` int(11) unsigned NOT NULL,
				`last_definition` longtext NOT NULL,
				`awb_id` int(11) unsigned NOT NULL,
				`id_attachment` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_emagmp_order_history`),
				UNIQUE KEY `emag_order_id` (`emag_order_id`),
				KEY `id_order` (`id_order`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_order_vouchers` (
				`id_order` int(11) unsigned NOT NULL,
				`emag_voucher_id` int(11) unsigned NOT NULL,
				`id_order_cart_rule` int(11) unsigned NOT NULL,
				KEY `id_order` (`id_order`),
				KEY `id_order_cart_rule` (`id_order_cart_rule`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");
		
		Db::getInstance()->execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."emagmp_cron_jobs` (
				`name` varchar(100) NOT NULL,
				`running` tinyint(1) NOT NULL,
				`last_started` datetime NOT NULL,
				`last_ended` datetime NOT NULL,
				PRIMARY KEY (`name`)
			) ENGINE="._MYSQL_ENGINE_."  DEFAULT CHARSET=utf8;
		");

		Db::getInstance()->execute("
			INSERT IGNORE INTO `"._DB_PREFIX_."emagmp_cron_jobs` (`name`, `running`, `last_started`, `last_ended`) VALUES
			('check_cron_jobs', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('check_errors', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('clean_logs', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('get_orders', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('import_order', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('refresh_definitions', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
			('run_queue', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
		");

		
		// add missing hook (if not exists)
		Db::getInstance()->execute("
			INSERT IGNORE INTO `"._DB_PREFIX_."hook` (`name`, `title`, `description`, `position`, `live_edit`) VALUES
			('actionAdminProductsControllerStatusAfter', 'Status toggle action from Admin product list', '', 0, 0);
		");
		
		// add missing hook (if not exists)
		Db::getInstance()->execute("
			INSERT IGNORE INTO `"._DB_PREFIX_."hook` (`name`, `title`, `description`, `position`, `live_edit`) VALUES
			('actionObjectOrderDetailDeleteAfter', 'Delete product line from order', '', 0, 0);
		");

		$result = parent::install();
		$result = $result && $this->registerHook('displayAdminProductsExtra');
		$result = $result && $this->registerHook('actionProductAdd');
		$result = $result && $this->registerHook('actionProductUpdate');
		$result = $result && $this->registerHook('actionUpdateQuantity');
		$result = $result && $this->registerHook('actionProductDelete');
		$result = $result && $this->registerHook('actionProductAttributeDelete');
		$result = $result && $this->registerHook('actionAdminProductsControllerStatusAfter');
		$result = $result && $this->registerHook('actionObjectOrderUpdateAfter');
		$result = $result && $this->registerHook('actionObjectOrderDetailUpdateAfter');
		$result = $result && $this->registerHook('actionObjectOrderDetailDeleteAfter');
		$result = $result && $this->registerHook('displayAdminOrder');
		$result = $result && $this->registerHook('displayBackOfficeHeader');
		
		// hack for hook deleteProductAttribute (renamed to actionProductAttributeDelete, which is never called)
		$hook_name = 'deleteProductAttribute';
		Db::getInstance()->execute("
			INSERT IGNORE INTO `"._DB_PREFIX_."hook` (`name`, `title`, `description`, `position`, `live_edit`) VALUES
			('$hook_name', 'Product Attribute Deletion', 'Same as \'actionProductAttributeDelete\'', 0, 0);
		");
		$cache_id = 'hook_idbyname_'.$hook_name;
		Cache::store($cache_id, Db::getInstance()->getValue('
			SELECT `id_hook`
			FROM `'._DB_PREFIX_.'hook`
			WHERE `name` = \''.pSQL($hook_name).'\'
		'));
		$id_hook = Cache::retrieve($cache_id);
		$shop_list = Shop::getShops(true, null, true);
		foreach ($shop_list as $shop_id)
		{
			// Check if already register
			$sql = 'SELECT hm.`id_module`
				FROM `'._DB_PREFIX_.'hook_module` hm, `'._DB_PREFIX_.'hook` h
				WHERE hm.`id_module` = '.(int)($this->id).' AND h.`id_hook` = '.$id_hook.'
				AND h.`id_hook` = hm.`id_hook` AND `id_shop` = '.(int)$shop_id;
			if (Db::getInstance()->getRow($sql))
				continue;

			// Get module position in hook
			$sql = 'SELECT MAX(`position`) AS position
				FROM `'._DB_PREFIX_.'hook_module`
				WHERE `id_hook` = '.(int)$id_hook.' AND `id_shop` = '.(int)$shop_id;
			if (!$position = Db::getInstance()->getValue($sql))
				$position = 0;

			// Register module in hook
			Db::getInstance()->insert('hook_module', array(
				'id_module' => (int)$this->id,
				'id_hook' => (int)$id_hook,
				'id_shop' => (int)$shop_id,
				'position' => (int)($position + 1),
			));
		}

		return $result;
	}
	
	public function installModuleTab($tabClass, $tabName, $idTabParent, $active)
	{
		$tab = new Tab();
		foreach (Language::getLanguages(true) as $lang)
		{
			$tab->name[$lang['id_lang']] = $tabName;
		}
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $idTabParent;
		$tab->active = $active;
		$tab->save();
		
		return $tab;
	}
	
	public function uninstall()
	{
		$remove_data = Tools::getValue('remove_data');
		
		if ($remove_data)
		{
			foreach ($this->global_settings as $key => $value)
			{
				Configuration::deleteByName($key);
			}
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_category_definitions`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_characteristic_definitions`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_family_type_definitions`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_locality_definitions`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_categories`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_products`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_features`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_attribute_groups`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_api_calls`
			");
			
			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_product_combinations`
			");

			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_order_history`
			");

			Db::getInstance()->execute("
				DROP TABLE IF EXISTS `"._DB_PREFIX_."emagmp_cron_jobs`
			");
		}
		
		foreach ($this->admin_tabs as $key => $value)
		{
			$this->uninstallModuleTab($key);
		}
		$this->uninstallModuleTab('AdminEmagMarketplaceMain');
		
		return parent::uninstall();
	}
	
	public function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);
		if ($idTab)
		{
			$tab = new Tab($idTab);
			$tab->delete();
		}
	}
	
	/* Configuration */
	public function getContent()
	{
		//update fields
		//$output = $this->postProcess();

		// display form
		//return $output.$this->displayForm();
		return $this->displayForm();
	}

	/* Configuration Form */
	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$this->context->smarty->assign('config_link', $this->context->link->getAdminLink('AdminEmagMarketplaceConfig'));
		
		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
	}
	
	public function hookDisplayAdminProductsExtra($params)
	{
		$id_product = Tools::getValue('id_product');
		
		$emag_category_definitions = array();
		$result = Db::getInstance()->executeS('
			SELECT `emag_category_id`, `emag_category_name`
			FROM `'._DB_PREFIX_.'emagmp_category_definitions`
		');
		foreach ($result as $category_definition)
		{
			$emag_category_definitions[$category_definition['emag_category_id']] = $category_definition;
		}

		$emag_family_type_definitions = array();
		$result = Db::getInstance()->executeS('
			SELECT `emag_family_type_id`, `emag_category_id`, `emag_family_type_name`
			FROM `'._DB_PREFIX_.'emagmp_family_type_definitions`
		');
		foreach ($result as $family_type_definition)
		{
			$emag_family_type_definitions[$family_type_definition['emag_category_id']][] = $family_type_definition;
		}

		$emag_product = array('emag_category_id' => '-1', 'emag_family_type_id' => '-1', 'commission' => '-1');
		
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_products`
			WHERE id_product = '.(int)$id_product.'
		');
		if ($result)
		{
			$emag_product = $result[0];
		}
		
		if ($emag_product['emag_category_id'] != '-1')
		{
			$emag_product['emag_category_id'] = htmlentities($emag_product['emag_category_id'].' - '.$emag_category_definitions[$emag_product['emag_category_id']]['emag_category_name']);
		}
		
		$result = Db::getInstance()->executeS('
			SELECT ec.`emag_category_id`
			FROM `'._DB_PREFIX_.'product` AS p
			LEFT JOIN `'._DB_PREFIX_.'emagmp_categories` AS ec ON (p.`id_category_default` = ec.`id_category`)
			WHERE p.`id_product` = '.(int)$id_product.'
		');
		$emag_product['emag_category_id_default'] = (int)$result[0]['emag_category_id'];
		
		$this->context->smarty->assign(array(
			'emag_product' => $emag_product,
			'emag_category_definitions' => $emag_category_definitions,
			'emag_family_type_definitions' => $emag_family_type_definitions
		));

		return $this->display(__FILE__, 'views/templates/admin/product.tpl');
	}
	
	public function hookDisplayAdminOrder($params)
	{
		$id_order = Tools::getValue('id_order');
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_order_history`
			WHERE id_order = '.(int)$id_order.'
		');
		
		if (!$result)
			return;
		
		$order_history = $result[0];
		$order = new Order($id_order);
		
		if (Configuration::get('EMAGMP_USE_EMAG_AWB'))
		{
			if ($order_history['awb_id'])
			{
				$awb_url = Configuration::get('EMAGMP_URL').'/awb/read_pdf?emag_id='.$order_history['awb_id'].'&code='.Configuration::get('EMAGMP_VENDORCODE').'&username='.Configuration::get('EMAGMP_VENDORUSERNAME').'&hash='.sha1(Configuration::get('EMAGMP_VENDORPASSWORD'));
			}
			
			$addressDelivery = new Address($order->id_address_delivery, $this->context->language->id);
			
			if ($addressDelivery->id_state)
			{
				$deliveryState = new State($addressDelivery->id_state);
				$state_query = 'emag_region2_latin = \''.pSQL($deliveryState->name).'\' and';
			}
			else
			{
				$state_query = 'emag_region3_latin = \''.pSQL($addressDelivery->address2).'\' and';
			}
			$result = Db::getInstance()->executeS('
				SELECT *
				FROM `'._DB_PREFIX_.'emagmp_locality_definitions`
				WHERE '.$state_query.' emag_name_latin = \''.pSQL($addressDelivery->city).'\'
			');
			
			if (count($result) == 1)
			{
				$emag_locality_name = $result[0]['emag_name_latin'].', '.$result[0]['emag_region3_latin'].', '.$result[0]['emag_region2_latin'];
				$emag_locality_id = $result[0]['emag_locality_id'];
			}
			
			$this->context->smarty->assign(array(
				'awb_url' => $awb_url,
				'id_order' => $id_order,
				'emag_locality_name' => $emag_locality_name,
				'emag_locality_id' => $emag_locality_id
			));

			$template_name = 'order_awb';
			if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>'))
				$template_name .= '16';
			$content_awb = $this->display(__FILE__, 'views/templates/admin/'.$template_name.'.tpl');
		}
		
		if (!Configuration::get('PS_INVOICE'))
		{
			if ($order_history['id_attachment'])
			{
				$invoice_url = $this->context->link->getPageLink('attachment', true, NULL, "id_attachment=".$order_history['id_attachment']);
			}			
			
			$this->context->smarty->assign(array(
				'invoice_url' => $invoice_url,
				'id_order' => $id_order
			));
			
			$template_name = 'order_invoice';
			if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>'))
				$template_name .= '16';
			$content_invoice = $this->display(__FILE__, 'views/templates/admin/'.$template_name.'.tpl');
		}
		
		$order_history['original_emag_definition'] = unserialize($order_history['original_emag_definition']);
		$order_history['emag_definition'] = unserialize($order_history['emag_definition']);
		$payment_modes = array(
			'1' => 'Cash on delivery',
			'2' => 'Bank transfer',
			'3' => 'Online card payment'
		);
		$payment_mode = $payment_modes[$order_history['emag_definition']->payment_mode_id];
		$payment_status = $order_history['emag_definition']->payment_status == 1 ? 'Paid' : 'Unpaid';
		
		$emag_order_products = array();
		foreach ($order_history['original_emag_definition']->products as $emag_order_product)
		{
			$emag_order_products[$emag_order_product->product_id] = $emag_order_product;
		}
		
		$order_products = $order->getProducts();
		foreach ($order_products as $order_product)
		{
			$product_combination_info = $this->get_combination_info($order_product['product_id'], $order_product['product_attribute_id']);
			$product_id = $product_combination_info['combination_id'];
			
			if (isset($emag_order_products[$product_id]) && $emag_order_products[$product_id]->status == 1)
				$emag_order_products[$product_id]->quantity -= $order_product['product_quantity'];
		}
		
		$missing_products = array();
		foreach ($emag_order_products as $emag_order_product)
		{
			if ($emag_order_product->status == 1 && $emag_order_product->quantity > 0)
			{
				$emag_order_product->product_id = trim($emag_order_product->product_id);
				$result = Db::getInstance()->executeS('
					SELECT *
					FROM `'._DB_PREFIX_.'emagmp_product_combinations`
					WHERE combination_id = '.(int)$emag_order_product->product_id.'
				');
				$id_product = $result[0]['id_product'];
				$id_product_attribute = $result[0]['id_product_attribute'];
				
				$product = new Product($id_product, false, $this->context->language->id);
				$emag_order_product->name = $product->name;
				if ($id_product_attribute)
				{
					$emag_order_product->name .= ', ';
					$combinations = $product->getAttributesResume($this->context->language->id);
					foreach ($combinations as $combination)
					{
						if ($combination['id_product_attribute'] == $id_product_attribute)
							$emag_order_product->name .= $combination['attribute_designation'];
					}
				}
					
				$missing_products[] = array(
					'name' => $emag_order_product->name,
					'quantity' => $emag_order_product->quantity
				);
			}
		}
		
		$this->context->smarty->assign(array(
			'id_order' => $id_order,
			'payment_mode' => $payment_mode,
			'payment_status' => $payment_status,
			'missing_products' => $missing_products,
			'emag_order_id' => $order_history['emag_order_id'],
			'awb_url' => $awb_url
		));
		
		$template_name = 'order';
		if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>'))
			$template_name .= '16';
		return $this->display(__FILE__, 'views/templates/admin/'.$template_name.'.tpl').$content_awb.$content_invoice;
	}
	
	public function hookDisplayBackOfficeHeader()
	{
	   $this->context->controller->addCss($this->path.'views/css/admin.css');
	}
	
	public function hookActionProductAdd($params)
	{
		if (isset($params['product']))
		{
			$product = $params['product'];
			$product_id = $product->id;
		}
		elseif (isset($params['id_product']))
		{
			$product_id = $params['id_product'];
		}
		$this->updateProduct((int)$product_id, false);
	}
	
	public function hookActionProductUpdate($params)
	{
		if (Tools::getIsset('id_product') && Tools::getIsset('emagmp'))
		{
			$id_product = Tools::getValue('id_product');
			$emagmp = Tools::getValue('emagmp');

			if ($emagmp['emag_category_id'] != '-1')
			{
				preg_match('`^([0-9]+) - `', $emagmp['emag_category_id'], $match);
				
				if (!$match[1])
					!$match[1] = 0;
					
				$emagmp['emag_category_id'] = $match[1];
			}

			Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'emagmp_products`
				SET
				`id_product` = '.(int)$id_product.',
				`emag_category_id` = '.(int)$emagmp['emag_category_id'].',
				`emag_family_type_id` = '.(int)$emagmp['emag_family_type_id'].',
				`commission` = '.(float)$emagmp['commission'].'
				ON DUPLICATE KEY UPDATE
				`emag_category_id` = '.(int)$emagmp['emag_category_id'].',
				`emag_family_type_id` = '.(int)$emagmp['emag_family_type_id'].',
				`commission` = '.(float)$emagmp['commission'].'
			');
		}
		
		if (isset($params['product']))
		{
			$product = $params['product'];
			$product_id = $product->id;
		}
		elseif (isset($params['id_product']))
		{
			$product_id = $params['id_product'];
		}
		$this->updateProduct((int)$product_id);
	}
	
	public function hookActionUpdateQuantity($params)
	{
		if (isset($params['product']))
		{
			$product = $params['product'];
			$product_id = $product->id;
		}
		elseif (isset($params['id_product']))
		{
			$product_id = $params['id_product'];
		}
		$this->updateProduct((int)$product_id);
	}
	
	public function hookActionProductDelete($params)
	{
		if (isset($params['product']))
		{
			$product = $params['product'];
			$product_id = $product->id;
		}
		elseif (isset($params['id_product']))
		{
			$product_id = $params['id_product'];
		}
		$this->deleteProduct((int)$product_id);
	}
	
	public function hookDeleteProductAttribute($params)
	{
		if (isset($params['id_product']) && isset($params['id_product_attribute']))
		{
			$this->deleteProduct($params['id_product'], $params['id_product_attribute']);
		}
	}
	
	public function hookActionProductAttributeDelete($params)
	{
		if (isset($params['id_product']) && isset($params['id_product_attribute']))
		{
			$this->deleteProduct($params['id_product'], $params['id_product_attribute']);
		}
	}
	
	public function hookActionAdminProductsControllerStatusAfter($params)
	{
		$product = $params['return'];
		$this->updateProduct($product->id);
	}
	
	public function hookActionObjectOrderUpdateAfter($params)
	{
		$order = $params['object'];
		
		$this->updateOrder($order);
	}
	
	public function hookActionObjectOrderDetailUpdateAfter($params)
	{
		if ($GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'])
			return;
			
		$order_detail = $params['object'];
		$order = new Order((int)$order_detail->id_order);
		$this->updateOrder($order);
	}
	
	public function hookActionObjectOrderDetailDeleteAfter($params)
	{
		if ($GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'])
			return;
			
		$order_detail = $params['object'];
		$order = new Order((int)$order_detail->id_order);
		$this->updateOrder($order);
	}
	
	public function updateOrder($order)
	{
		if (!Validate::isLoadedObject($order))
			return;
			
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_order_history`
			WHERE id_order = '.$order->id.'
		');
		
		if (!$result)
			return;
		
		$order_history = $result[0];
		$order_history['emag_definition'] = unserialize($order_history['emag_definition']);
		
		$order_history['last_definition'] = unserialize($order_history['last_definition']);
		if (!is_array($order_history['last_definition']))
			$order_history['last_definition'] = array();
		
		$status = null;
		$emagmp_order_states = array(
			'EMAGMP_ORDER_STATE_ID_INITIAL' => 2,
			'EMAGMP_ORDER_STATE_ID_FINALIZED' => 4,
			'EMAGMP_ORDER_STATE_ID_CANCELLED' => 0
		);
		$configuration = Configuration::getMultiple(array_keys($emagmp_order_states));
		foreach ($configuration as $state_name => $state_id)
		{
			if ($order->current_state == $state_id)
			{
				$status = $emagmp_order_states[$state_name];
				break;
			}
		}
		
		if ($status === null)
		{
			if (count($order_history['last_definition']))
				$status = $order_history['last_definition']['status'];
			else
				return;
		}
		
		$emag_order_products = array();
		foreach ($order_history['emag_definition']->products as $emag_order_product)
		{
			$emag_order_product->status = 0;
			$emag_order_products[$emag_order_product->product_id] = $emag_order_product;
		}
			
		$order_products = $order->getProducts();
		foreach ($order_products as $order_product)
		{
			$product_combination_info = $this->get_combination_info($order_product['product_id'], $order_product['product_attribute_id']);
			$product_id = $product_combination_info['combination_id'];
			
			if (!is_object($emag_order_products[$product_id]))
				$emag_order_products[$product_id] = new stdClass();
				
			$emag_order_products[$product_id]->product_id = $product_id;
			$emag_order_products[$product_id]->status = 1;
			$emag_order_products[$product_id]->quantity = $order_product['product_quantity'];
			$emag_order_products[$product_id]->sale_price = $order_product['unit_price_tax_excl'];
		}
		
		$products = array();
		foreach ($emag_order_products as $emag_order_product)
		{
			$products[] = (array) $emag_order_product;
		}
		
		$attachments = array();
		if (Configuration::get('PS_INVOICE'))
		{
			$order_invoices = $order->getInvoicesCollection()->getResults();
			foreach ($order_invoices as $invoice)
			{
				$attachments[] = array(
					'name' => $invoice->getInvoiceNumberFormatted($this->context->language->id),
					'url' => $this->context->link->getModuleLink('emagmarketplace', 'Invoices').'&id='.$invoice->id,
					'type' => 1
				);
			}
		}
		else
		{
			$attachment = new Attachment($order_history['id_attachment'], $this->context->language->id);
			if (Validate::isLoadedObject($attachment))
			{
				$attachments[] = array(
					'name' => $attachment->name,
					'url' => $this->context->link->getPageLink('attachment', true, NULL, "id_attachment=".$attachment->id),
					'type' => 1
				);
			}
		}
		
		$new_definition = array(
			'status' => $status,
			'id' => $order_history['emag_order_id'],
			'products' => $products,
			'shipping_tax' => Tools::ps_round($order->total_shipping_tax_incl, 2),
			'attachments' => $attachments
		);
		
		$emag_definition = (array) $order_history['emag_definition'];
		foreach ($emag_definition as $key => $value)
		{
			if (is_object($value))
				$value = (array) $value;
				
			if (is_array($value))
			{
				foreach ($value as $k => $v)
				{
					if (is_object($v))
						$value[$k] = (array) $v;
				}
			}
				
			if (!isset($new_definition[$key]))
				$new_definition[$key] = $value;
		}
			
		$emagmp_api_call = new EmagMarketplaceAPICall();
		$emagmp_api_call->resource = 'order';
		$emagmp_api_call->action = 'save';
		$emagmp_api_call->data = array(
			$new_definition
		);
		$emagmp_api_call->last_definition = serialize($new_definition);
		$emagmp_api_call->id_order = $order->id;
		$emagmp_api_call->execute();
		$emagmp_api_call->save();
		
		return $this->refreshEmagOrder($order, $order_history);
	}
	
	public function refreshEmagOrder($order, $order_history)
	{
		// re-read order & update if necessary
		
		if (!isset($GLOBALS['EMAGMP_REFRESH_ORDER_'.$order->id]))
			$GLOBALS['EMAGMP_REFRESH_ORDER_'.$order->id] = 0;
			
		$GLOBALS['EMAGMP_REFRESH_ORDER_'.$order->id]++;
		
		if ($GLOBALS['EMAGMP_REFRESH_ORDER_'.$order->id] > 5)
		{
			return;
		}
		
		$emagmp_api_call = new EmagMarketplaceAPICall();
		$emagmp_api_call->resource = 'order';
		$emagmp_api_call->action = 'read';
		$emagmp_api_call->data = array(
			'id' => $order_history['emag_order_id']
		);
		$emagmp_api_call->execute();
		$emagmp_api_call->save();
		
		if ($emagmp_api_call->status != 'success')
			return;
		
		$emag_order = $emagmp_api_call->message_in_json->results[0];
		
		$order_updated = false;
		
		// refresh shipping tax
		
		if (Tools::ps_round(trim($emag_order->shipping_tax), 2) != Tools::ps_round($order->total_shipping, 2))
		{
			$emag_order->shipping_tax = Tools::ps_round(trim($emag_order->shipping_tax), 2);
			$order_carriers = $order->getShipping();
			foreach ((array) $order_carriers as $row)
			{
				$order_carrier = new OrderCarrier($row['id_order_carrier']);
				if (!Validate::isLoadedObject($order_carrier))
					continue;
				$order_carrier->shipping_cost_tax_incl = $emag_order->shipping_tax;
				$order_carrier->shipping_cost_tax_excl = Tools::ps_round($emag_order->shipping_tax / (1 + $order->carrier_tax_rate / 100), 4);
				$order_carrier->update();
				break;
			}

			$old_total_shipping_tax_excl = $order->total_shipping_tax_excl;
			$old_total_shipping_tax_incl = $order->total_shipping_tax_incl;
			
			$order->total_shipping_tax_excl = $order_carrier->shipping_cost_tax_excl;
			$order->total_shipping_tax_incl = $order_carrier->shipping_cost_tax_incl;
			$order->total_shipping = $order->total_shipping_tax_incl;
			
			$order->total_paid_tax_excl += $order->total_shipping_tax_excl - $old_total_shipping_tax_excl;
			$order->total_paid_tax_incl += $order->total_shipping_tax_incl - $old_total_shipping_tax_incl;
			$order->total_paid = $order->total_paid_tax_incl;
			
			if ($order->total_paid_real > 0)
			{
				foreach ($order->getOrderPaymentCollection() as $order_payment)
				{
					$order_payment->amount += $order->total_shipping_tax_incl - $old_total_shipping_tax_incl;
					$order_payment->update();
					break;
				}
				$order->total_paid_real += $order->total_shipping_tax_incl - $old_total_shipping_tax_incl;
			}
			
			foreach ($order->getInvoicesCollection() as $order_invoice)
			{
				$order_invoice->total_shipping_tax_excl += $order->total_shipping_tax_excl - $old_total_shipping_tax_excl;
				$order_invoice->total_shipping_tax_incl += $order->total_shipping_tax_incl - $old_total_shipping_tax_incl;
				$order_invoice->total_paid_tax_excl += $order->total_shipping_tax_excl - $old_total_shipping_tax_excl;
				$order_invoice->total_paid_tax_incl += $order->total_shipping_tax_incl - $old_total_shipping_tax_incl;
				$order_invoice->update();
				break;
			}
			
			$order_updated = true;
		}
		
		// refresh products
		if (count($emag_order->products))
		{
			$products = array();
			
			$order_products = $order->getProducts();
			foreach ($order_products as $order_product)
			{
				$product_combination_info = $this->get_combination_info($order_product['product_id'], $order_product['product_attribute_id']);
				$products[$product_combination_info['combination_id']]['old'] = array(
					'price' => Tools::ps_round($order_product['unit_price_tax_excl'], 2),
					'quantity' => (int)$order_product['product_quantity'],
					'id_order_detail' => $order_product['id_order_detail']
				);
			}
			
			$emag_order_products = array();
			foreach ($emag_order->products as $emag_product)
			{
				$products[$emag_product->product_id]['new'] = array(
					'price' => Tools::ps_round($emag_product->sale_price, 2),
					'quantity' => (int)$emag_product->quantity
				);
			}
			
			foreach ($products as $combination_id => $values)
			{
				// update existing products
				
				if (isset($values['old']) && isset($values['new']))
				{
					if ($values['old']['price'] == $values['new']['price'] && $values['old']['quantity'] == $values['new']['quantity'])
						continue;
						
					$order_detail = new OrderDetail($values['old']['id_order_detail']);
					
					$diff_qty = $values['new']['quantity'] - $values['old']['quantity'];
					if ($diff_qty > 0)
					{
						if ($order_detail->product_attribute_id)
						{
							$diff_qty = min($diff_qty, StockAvailable::getQuantityAvailableByProduct(null, $order_detail->product_attribute_id));
							$diff_qty = max(0, $diff_qty);
						}
						else
						{
							$diff_qty = min($diff_qty, StockAvailable::getQuantityAvailableByProduct($order_detail->product_id, null));
							$diff_qty = max(0, $diff_qty);
						}
					}
					
					unset($order_invoice);
					foreach ($order->getInvoicesCollection() as $order_invoice)
					{
						break;
					}

					$product_quantity = $values['old']['quantity'] + $diff_qty;

					$product_price_tax_excl = $values['new']['price'];
					$taxcalc = $order_detail->getTaxCalculator();
					$product_price_tax_incl = Tools::ps_round($taxcalc->addTaxes($product_price_tax_excl), 2);
					$total_products_tax_incl = $product_price_tax_incl * $product_quantity;
					$total_products_tax_excl = $product_price_tax_excl * $product_quantity;

					// Calculate differences of price (Before / After)
					$diff_price_tax_incl = $total_products_tax_incl - $order_detail->total_price_tax_incl;
					$diff_price_tax_excl = $total_products_tax_excl - $order_detail->total_price_tax_excl;

					// Apply change on OrderInvoice
					if (isset($order_invoice))
						// If OrderInvoice to use is different, we update the old invoice and new invoice
						if ($order_detail->id_order_invoice != $order_invoice->id)
						{
							$old_order_invoice = new OrderInvoice($order_detail->id_order_invoice);
							// We remove cost of products
							$old_order_invoice->total_products -= $order_detail->total_price_tax_excl;
							$old_order_invoice->total_products_wt -= $order_detail->total_price_tax_incl;

							$old_order_invoice->total_paid_tax_excl -= $order_detail->total_price_tax_excl;
							$old_order_invoice->total_paid_tax_incl -= $order_detail->total_price_tax_incl;

							$old_order_invoice->update();

							$order_invoice->total_products += $order_detail->total_price_tax_excl;
							$order_invoice->total_products_wt += $order_detail->total_price_tax_incl;

							$order_invoice->total_paid_tax_excl += $order_detail->total_price_tax_excl;
							$order_invoice->total_paid_tax_incl += $order_detail->total_price_tax_incl;

							$order_detail->id_order_invoice = $order_invoice->id;
						}

					if ($diff_price_tax_incl != 0 && $diff_price_tax_excl != 0)
					{
						$order_detail->unit_price_tax_excl = $product_price_tax_excl;
						$order_detail->unit_price_tax_incl = $product_price_tax_incl;

						$order_detail->total_price_tax_incl += $diff_price_tax_incl;
						$order_detail->total_price_tax_excl += $diff_price_tax_excl;

						if (isset($order_invoice))
						{
							// Apply changes on OrderInvoice
							$order_invoice->total_products += $diff_price_tax_excl;
							$order_invoice->total_products_wt += $diff_price_tax_incl;

							$order_invoice->total_paid_tax_excl += $diff_price_tax_excl;
							$order_invoice->total_paid_tax_incl += $diff_price_tax_incl;
						}

						// Apply changes on Order
						$order->total_products += $diff_price_tax_excl;
						$order->total_products_wt += $diff_price_tax_incl;

						$order->total_paid += $diff_price_tax_incl;
						$order->total_paid_tax_excl += $diff_price_tax_excl;
						$order->total_paid_tax_incl += $diff_price_tax_incl;
					}

					$old_quantity = $order_detail->product_quantity;

					$order_detail->product_quantity = $product_quantity;
					// Save order detail
					$GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'] = true;
					$order_detail->update();
					$GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'] = false;
					// Save order invoice
					if (isset($order_invoice))
						 $order_invoice->update();

					// Update product available quantity
					StockAvailable::updateQuantity($order_detail->product_id, $order_detail->product_attribute_id, ($old_quantity - $order_detail->product_quantity), $order->id_shop);
					
					$order_updated = true;				
				}
				
				// delete existing products
				
				elseif (isset($values['old']) && !isset($values['new']))
				{
					$order_detail = new OrderDetail($values['old']['id_order_detail']);

					// Update OrderInvoice of this OrderDetail
					if ($order_detail->id_order_invoice != 0)
					{
						$order_invoice = new OrderInvoice($order_detail->id_order_invoice);
						$order_invoice->total_paid_tax_excl -= $order_detail->total_price_tax_excl;
						$order_invoice->total_paid_tax_incl -= $order_detail->total_price_tax_incl;
						$order_invoice->total_products -= $order_detail->total_price_tax_excl;
						$order_invoice->total_products_wt -= $order_detail->total_price_tax_incl;
						$order_invoice->update();
					}

					// Update Order
					$order->total_paid -= $order_detail->total_price_tax_incl;
					$order->total_paid_tax_incl -= $order_detail->total_price_tax_incl;
					$order->total_paid_tax_excl -= $order_detail->total_price_tax_excl;
					$order->total_products -= $order_detail->total_price_tax_excl;
					$order->total_products_wt -= $order_detail->total_price_tax_incl;

					// Delete OrderDetail
					$GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'] = true;
					$order_detail->delete();
					$GLOBALS['EMAGMP_IGNORE_ORDER_UPDATE'] = false;
					
					$order_updated = true;
				}
				
				// add new products
				
				elseif (!isset($values['old']) &&  isset($values['new']))
				{
				}
			}
		}
		
		// refresh vouchers
		
		if (count($emag_order->vouchers))
		{
			$order_cart_rules = array();
			$result = Db::getInstance()->executeS('
				SELECT *
				FROM `'._DB_PREFIX_.'emagmp_order_vouchers` ov
				JOIN `'._DB_PREFIX_.'order_cart_rule` ocr ON ocr.id_order_cart_rule = ov.id_order_cart_rule
				JOIN `'._DB_PREFIX_.'cart_rule` cr ON cr.`id_cart_rule` = ocr.`id_cart_rule`
				WHERE ov.`id_order` = '.$order->id.' AND ocr.`id_order` = '.$order->id.'
			');
			foreach ($result as $row)
			{
				$order_cart_rules[$row['emag_voucher_id']] = $row;
			}
			//echo "order_cart_rules:\n"; print_r($order_cart_rules);
			
			foreach ($emag_order->vouchers as $emag_voucher)
			{
				//echo 'emag_voucher_id: '.$emag_voucher->id."\n\n";
				if (!isset($order_cart_rules[$emag_voucher->id]))
				{
					$cartRuleObj = new CartRule();
					$cartRuleObj->date_from = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($order->date_add)));
					$cartRuleObj->date_to = date('Y-m-d H:i:s', strtotime('+1 hour'));
					$cartRuleObj->name[$this->context->language->id] = $emag_voucher->voucher_name;
					$cartRuleObj->quantity = 0;
					$cartRuleObj->quantity_per_user = 1;
					$cartRuleObj->reduction_amount = Tools::ps_round(abs($emag_voucher->sale_price), 2);
					$cartRuleObj->active = 0;
					$cartRuleObj->add();

					$order_cart_rule = new OrderCartRule();
					$order_cart_rule->id_order = $order->id;
					$order_cart_rule->id_cart_rule = $cartRuleObj->id;
					$order_cart_rule->id_order_invoice = 0;
					$order_cart_rule->name = $emag_voucher->voucher_name;
					$order_cart_rule->value = Tools::ps_round(abs($emag_voucher->sale_price + $emag_voucher->sale_price_vat), 2);
					$order_cart_rule->value_tax_excl = Tools::ps_round(abs($emag_voucher->sale_price), 2);
					$order_cart_rule->add();

					$order->total_discounts += $order_cart_rule->value;
					$order->total_discounts_tax_incl += $order_cart_rule->value;
					$order->total_discounts_tax_excl += $order_cart_rule->value_tax_excl;
					$order->total_paid -= $order_cart_rule->value;
					$order->total_paid_tax_incl -= $order_cart_rule->value;
					$order->total_paid_tax_excl -= $order_cart_rule->value_tax_excl;
					
					if ($order->total_paid_real > 0)
					{
						foreach ($order->getOrderPaymentCollection() as $order_payment)
						{
							$order_payment->amount -= $order_cart_rule->value;
							$order_payment->update();
							break;
						}
						$order->total_paid_real -= $order_cart_rule->value;
					}
					
					foreach ($order->getInvoicesCollection() as $order_invoice)
					{
						$order_invoice->total_discount_tax_excl += $order_cart_rule->value_tax_excl;
						$order_invoice->total_discount_tax_incl += $order_cart_rule->value;
						$order_invoice->total_paid_tax_excl -= $order_cart_rule->value_tax_excl;
						$order_invoice->total_paid_tax_incl -= $order_cart_rule->value;
						$order_invoice->update();
						break;
					}
		
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'emagmp_order_vouchers`
						SET id_order = '.$order->id.',
						emag_voucher_id = '.$emag_voucher->id.',
						id_order_cart_rule = '.$order_cart_rule->id.'
					');
					$order_updated = true;
				}
				else
				{
					$cartRuleObj = new CartRule($order_cart_rules[$emag_voucher->id]['id_cart_rule']);
					if (Tools::ps_round($cartRuleObj->reduction_amount, 2) != Tools::ps_round(abs($emag_voucher->sale_price), 2))
					{
						$cartRuleObj->reduction_amount = abs($emag_voucher->sale_price);
						$cartRuleObj->update();
						
						$order_cart_rule = new OrderCartRule($order_cart_rules[$emag_voucher->id]['id_order_cart_rule']);
						$order_cart_rule->value = Tools::ps_round(abs($emag_voucher->sale_price + $emag_voucher->sale_price_vat), 2);
						$order_cart_rule->value_tax_excl = Tools::ps_round(abs($emag_voucher->sale_price), 2);
						$order_cart_rule->update();
						
						$order->total_discounts += $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
						$order->total_discounts_tax_incl += $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
						$order->total_discounts_tax_excl += $order_cart_rule->value_tax_excl - $order_cart_rules[$emag_voucher->id]['value_tax_excl'];
						$order->total_paid -= $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
						$order->total_paid_tax_incl -= $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
						$order->total_paid_tax_excl -= $order_cart_rule->value_tax_excl - $order_cart_rules[$emag_voucher->id]['value_tax_excl'];

						if ($order->total_paid_real > 0)
						{
							foreach ($order->getOrderPaymentCollection() as $order_payment)
							{
								$order_payment->amount -= $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
								$order_payment->update();
								break;
							}
							$order->total_paid_real -= $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
						}
					
						foreach ($order->getInvoicesCollection() as $order_invoice)
						{
							$order_invoice->total_discount_tax_excl += $order_cart_rule->value_tax_excl - $order_cart_rules[$emag_voucher->id]['value_tax_excl'];
							$order_invoice->total_discount_tax_incl += $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
							$order_invoice->total_paid_tax_excl -= $order_cart_rule->value_tax_excl - $order_cart_rules[$emag_voucher->id]['value_tax_excl'];
							$order_invoice->total_paid_tax_incl -= $order_cart_rule->value - $order_cart_rules[$emag_voucher->id]['value'];
							$order_invoice->update();
							break;
						}
		
						$order_updated = true;
					}
				}
			}
		}

		// Update Order if necessary
		if ($order_updated)
			$order->update();
			
		return true;
	}
	
	public function get_combination_info($id_product, $id_product_attribute, $force_create = false)
	{
		$id_product = (int)$id_product;
		$id_product_attribute = (int)$id_product_attribute;
		
		if ($force_create)
		{
			// avoid auto increment gaps
			Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'emagmp_product_combinations` (id_product, id_product_attribute)
				SELECT '.$id_product.', '.$id_product_attribute.' FROM DUAL
				WHERE NOT EXISTS (
					SELECT *
					FROM `'._DB_PREFIX_.'emagmp_product_combinations`
					WHERE id_product = '.$id_product.' AND id_product_attribute = '.$id_product_attribute.'
				)
			');
			$combination_id = Db::getInstance()->Insert_ID();
			$last_definition = array();
		}
		if (!$combination_id)
		{
			$result2 = Db::getInstance()->executeS('
				SELECT combination_id, last_definition
				FROM `'._DB_PREFIX_.'emagmp_product_combinations`
				WHERE id_product = '.$id_product.' AND id_product_attribute = '.$id_product_attribute.'
			');
			$combination_id = $result2[0]['combination_id'];
			$last_definition = unserialize($result2[0]['last_definition']);
			if (!is_array($last_definition))
				$last_definition = array();
		}
		
		return array('combination_id' => $combination_id, 'last_definition' => $last_definition);
	}
	
	public function updateProduct($id_product = null, $delta = true)
	{
		$offer_fields = array(
			'id' => true,
			'status' => true,
			'sale_price' => true,
			'vat_id' => true,
			'commission' => true,
			'availability' => true,
			'stock' => true,
			'handling_time' => true
		);
				
		// get VAT rates
		$vat_rates = array();
		$emagmp_api_call = new EmagMarketplaceAPICall();
		$emagmp_api_call->resource = 'vat';
		$emagmp_api_call->action = 'read';
		
		$emagmp_api_call->execute();
		$emagmp_api_call->save();
		
		if ($emagmp_api_call->status == 'success')
		{
			foreach ($emagmp_api_call->message_in_json->results as $vat_def)
			{
				$tax_rate = Tools::ps_round($vat_def->vat_rate, 2);
				$vat_rates["$tax_rate"] = $vat_def->vat_id;
			}
		}
				
		// get category and characteristic mapping
		$categories = array();
		$result = Db::getInstance()->executeS('
			SELECT ec.*
			FROM `'._DB_PREFIX_.'emagmp_categories` AS ec
			WHERE ec.emag_category_id > 0 AND ec.sync_active = 1
		', false);
		if ($result)
		{
			while ($row = Db::getInstance()->nextRow($result))
			{
				$categories[$row['emag_category_id']] = $row;
			}
		}
		
		$characteristics = array();
		$result = Db::getInstance()->executeS('
			SELECT ec.emag_category_id, ef.emag_characteristic_id as emag_characteristic_id_feature, ea.emag_characteristic_id as emag_characteristic_id_attribute, ef.id_feature, ea.id_attribute_group
			FROM `'._DB_PREFIX_.'emagmp_categories` AS ec
			JOIN `'._DB_PREFIX_.'emagmp_characteristic_definitions` AS ecd ON (ec.emag_category_id = ecd.emag_category_id)
			LEFT JOIN `'._DB_PREFIX_.'emagmp_features` AS ef ON (ecd.emag_characteristic_id = ef.emag_characteristic_id AND ecd.emag_category_id = ef.emag_category_id)
			LEFT JOIN `'._DB_PREFIX_.'emagmp_attribute_groups` AS ea ON (ecd.emag_characteristic_id = ea.emag_characteristic_id AND ecd.emag_category_id = ea.emag_category_id)
			WHERE ec.emag_category_id > 0 AND ec.sync_active = 1 and (ef.id_feature > 0 or ea.id_attribute_group > 0)
		', false);
		if ($result)
		{
			while ($row = Db::getInstance()->nextRow($result))
			{
				$row['emag_characteristic_id'] = $row['emag_characteristic_id_attribute'] ? $row['emag_characteristic_id_attribute'] : $row['emag_characteristic_id_feature'];
				$characteristics[$row['emag_category_id']][$row['emag_characteristic_id']] = $row;
			}
		}
		
		// get products
		$sql = '
			SELECT ec.emag_category_id, ec.emag_family_type_id, ep.emag_category_id AS emag_category_id_p, ep.emag_family_type_id AS emag_family_type_id_p, ec.commission, ep.commission AS commission_p, p.id_product, IFNULL(pa.`id_product_attribute`, 0) AS id_product_attribute
			FROM `'._DB_PREFIX_.'emagmp_categories` ec
			JOIN `'._DB_PREFIX_.'product` p ON (ec.id_category = p.id_category_default)
			'.$this->context->shop->addSqlAssociation('product', 'p').'
			LEFT JOIN `'._DB_PREFIX_.'emagmp_products` ep ON (p.`id_product` = ep.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
			WHERE ec.emag_category_id > 0 AND ec.sync_active = 1
		';
		if ($id_product === null)
		{
			// upload all active products
			$sql .= ' AND p.`active` = 1';
		}
		else
		{
			// upload only current product
			$sql .= ' AND p.`id_product` = '.(int)$id_product;
			
			if (!$delta)
				$sql .= ' AND p.`active` = 1';
		}
		$sql .= ' GROUP BY p.`id_product`, pa.`id_product_attribute`';
		//echo $sql;
		$result = Db::getInstance()->executeS($sql, false);
		if ($result)
		{
			while ($row = Db::getInstance()->nextRow($result))
			{
				$product = new Product($row['id_product'], true, $this->context->language->id);
				
				$product_combination_info = $this->get_combination_info($row['id_product'], $row['id_product_attribute'], $product->active ? true : false);
				$combination_id = $product_combination_info['combination_id'];
				$last_definition = $product_combination_info['last_definition'];
				
				if (!$combination_id)
					continue;
				
				Cache::clean('StockAvailable::getQuantityAvailableByProduct_'.$row['id_product'].'*');
				Cache::clean($row['id_product'].'_'.$row['id_product_attribute'].'_quantity');
				$attribute_count = $product->hasAttributes();
				if (!$attribute_count)
				{
					$name = $product->name;
					$reference = $product->reference;
					$quantity = StockAvailable::getQuantityAvailableByProduct($row['id_product'], $row['id_product_attribute']);
				}
				else
				{
					$name = $product->name;
					$combinations = $product->getAttributeCombinationsById($row['id_product_attribute'], $this->context->language->id);
					foreach ((array)$combinations as $combination)
					{
						$attribute_name = $this->customizeValue('customizeAttributeName', $combination['attribute_name']);
						$name .= ', '.$attribute_name;
					}
					$reference = $combinations[0]['reference'];
					if (!$reference)
						$reference = $product->reference;
					$quantity = $combinations[0]['quantity'];
				}
				
				if (!$reference)
					$reference = 'EMAGMPREF_'.$combination_id;
				
				$description = '';
				switch (Configuration::get('EMAGMP_PRODUCT_DESCRIPTION_TYPE'))
				{
					case 'long':
						$description = $product->description;
						break;
					case 'short':
						$description = $product->description_short;
						break;
					case 'combined':
						$description = $product->description_short.'<br /><br />'.$product->description;
						break;
				}
				
				$tax_rate = Tools::ps_round((float)$product->getTaxesRate(null) / 100, 2);
				$specific_price_output = null;
				$sale_price = Product::getPriceStatic($row['id_product'], false, $row['id_product_attribute'], 4, null, false, true, 1);
				$recommended_price = Product::getPriceStatic($row['id_product'], false, $row['id_product_attribute'], 4, null, false, false, 1);
				
				if ($row['emag_category_id_p'] > 0)
				{
					$row['emag_category_id'] = $row['emag_category_id_p'];
				}
					
				if ($row['emag_family_type_id_p'] > 0)
				{
					$row['emag_family_type_id'] = $row['emag_family_type_id_p'];
				}
					
				if ($row['commission_p'] > 0)
					$row['commission'] = $row['commission_p'];
				
				$images = array();
				$product_images = $product->getImages((int)$this->context->language->id);
				foreach ((array)$product_images as $image)
				{
					$images[] = array(
						'display_type' => $image['cover'] ? 1 : 2,
						'url' => $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$image['id_image'], 'emag')
					);
				}
				
				$features = array();
				$product_features = $product->getFrontFeatures((int)$this->context->language->id);
				foreach ((array)$product_features as $feature)
				{
					$features[$feature['id_feature']] = $feature['value'];
				}
				
				$attributes = array();
				$product_attributes = $product->getAttributeCombinationsById((int)$row['id_product_attribute'], (int)$this->context->language->id);
				foreach ((array)$product_attributes as $attribute)
				{
					$attributes[$attribute['id_attribute_group']] = $attribute['attribute_name'];
				}
				
				if ($quantity < 0)
					$quantity = 0;
				
				if ($quantity <= 0)
					$availability = 5;
				elseif ($quantity <= 3)
					$availability = 2;
				else
					$availability = 3;
					
				$handling_time = (int) Configuration::get('EMAGMP_HANDLING_TIME');
					
				$product_data = array(
					'id' => $combination_id,
					'category_id' => $row['emag_category_id'],
					'name' => $name,
					'part_number' => $reference,
					'description' => $description,
					'brand' => $product->manufacturer_name,
					'images' => $images,
					'url' => $this->context->link->getProductLink($product),
					'status' => (int)$product->active,
					'sale_price' => $sale_price,
					'availability' => array(
						array(
							'warehouse_id' => 1,
							'id' => $availability
						)
					),
					'stock' => array(
						array(
							'warehouse_id' => 1,
							'value' => $quantity
						)
					),
					'handling_time' => array(
						array(
							'warehouse_id' => 1,
							'value' => $handling_time
						)
					),
					'commission' => array(
						'type' => 'percentage',
						'value' => $row['commission']
					),
					'vat_id' => $vat_rates["$tax_rate"],
					'recommended_price' => $recommended_price,
					'characteristics' => array(),
					'family' => array('id' => 0)
				);
				
				if ($availability == 5)
				{
					$product_data['status'] = 0;
				}
				
				foreach ((array)$characteristics[$row['emag_category_id']] as $characteristic_id => $characteristic)
				{
					$id_feature = $characteristic['id_feature'];
					$id_attribute_group = $characteristic['id_attribute_group'];
					if (!$features[$id_feature] && !$attributes[$id_attribute_group])
						continue;
						
					$product_data['characteristics'][] = array(
						'id' => $characteristic_id,
						'value' => $attributes[$id_attribute_group] ? $attributes[$id_attribute_group] : $features[$id_feature]
					);
				}
				
				if ($attribute_count > 1 && $row['emag_family_type_id'] > 0)
				{
					$product_data['family'] = array(
						'id' => $row['id_product'],
						'family_type_id' => $row['emag_family_type_id'],
						'name' => $product->name
					);
				}
				
				// don't send unnecessary data unless it has changed or is part of the mandatory offer update data, or we are sending all products
				$new_definition = $product_data;
				if ($delta)
				{
					$new_definition_keys = array_keys($new_definition);
					$last_definition_keys = array_keys($last_definition);
					$all_keys = array_unique(array_merge($new_definition_keys, $last_definition_keys));
					foreach ($all_keys as $key)
					{
						if (isset($offer_fields[$key]))
							continue;
							
						if ($new_definition[$key] === $last_definition[$key])
							unset($product_data[$key]);
					}
				}
				
				$emagmp_api_call = new EmagMarketplaceAPICall();
				$emagmp_api_call->resource = 'product_offer';
				$emagmp_api_call->action = 'save';
				$emagmp_api_call->data = array(
					$product_data
				);
				$emagmp_api_call->last_definition = serialize($new_definition);
				$emagmp_api_call->save();
			}
		}
	}
	
	public function deleteProduct($id_product, $id_product_attribute = 0)
	{
		$sql = '
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_product_combinations`
			WHERE id_product = '.(int)$id_product.'
		';
		if ($id_product_attribute > 0)
			$sql .= ' AND id_product_attribute = '.(int)$id_product_attribute;
			
		$result = Db::getInstance()->executeS($sql);
		foreach ($result as $row)
		{
			$product_data = array(
				'id' => $row['combination_id'],
				'status' => 0
			);
			
			$emagmp_api_call = new EmagMarketplaceAPICall();
			$emagmp_api_call->resource = 'product_offer';
			$emagmp_api_call->action = 'save';
			$emagmp_api_call->data = array(
				$product_data
			);
			$emagmp_api_call->last_definition = serialize($product_data);
			$emagmp_api_call->save();
		}
	}
	
	public function customizeValue($method_name, $value)
	{
		if (!isset($this->customizationObject))
			return $value;
		
		if (!is_callable(array($this->customizationObject, $method_name)))
			return $value;
			
		return $this->customizationObject->{$method_name}($value);
	}
}

?>
