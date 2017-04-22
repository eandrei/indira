<?php

include_once(dirname(__FILE__).'/../../classes/emagmarketplaceapicall.php');
include_once(dirname(__FILE__).'/../../emagmarketplace.php');

class AdminEmagMarketplaceConfigController extends ModuleAdminController
{
	
	public function __construct()
	{
		$this->bootstrap = true;
		$this->context = Context::getContext();
		$this->className = 'Configuration';
		$this->table = 'configuration';

		$fields = array(
			'EMAGMP_URL' => array(
				'title' => $this->l('Marketplace URL'),
				'desc' => $this->l('Homepage address of the eMAG Marketplace website'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_API_URL' => array(
				'title' => $this->l('API URL'),
				'desc' => $this->l('Main web address of the API'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_PROTOCOL' => array(
				'title' => $this->l('API Protocol'),
				'desc' => $this->l('Protocal used for comunicating with the API'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_LOCALE' => array(
				'title' => $this->l('API Locale'),
				'desc' => $this->l('Locale to use with the API'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_CURRENCY' => array(
				'title' => $this->l('API Currency'),
				'desc' => $this->l('Currency to use with the API'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_VENDORCODE' => array(
				'title' => $this->l('API Vendor Code'),
				'desc' => $this->l('Your API Vendor Code'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_VENDORUSERNAME' => array(
				'title' => $this->l('API Vendor Username'),
				'desc' => $this->l('Your API Vendor Username'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
			'EMAGMP_VENDORPASSWORD' => array(
				'title' => $this->l('API Password'),
				'desc' => $this->l('Your API Vendor Password'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text',
				'required' => true
			),
		);
		
		$delivery_options = array();
		$result = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
		foreach ($result as $k => $row)
		{
			$delivery_options[] = array(
				'id' => $row['id_carrier'],
				'name' => $row['name']
			);
		}
		
		$order_states = array();
		$result = OrderState::getOrderStates($this->context->language->id);
		foreach ($result as $row)
		{
			$order_states[] = array(
				'id' => $row['id_order_state'],
				'name' => $row['name']
			);
		}
		
		$fields2 = array(
			'EMAGMP_PRODUCT_QUEUE_LIMIT' => array(
				'title' => $this->l('Product Queue Limit'),
				'desc' => $this->l('How many product updates to send per minute'),
				'validation' => 'isInt',
				'cast' => 'intval',
				'type' => 'text',
				'size' => 30,
				'default' => '20',
				'required' => true
			),
			'EMAGMP_PRODUCT_DESCRIPTION_TYPE' => array(
				'title' => $this->l('Product Description Method'),
				'desc' => $this->l('Which description to send to eMAG Marketplace'),
				'validation' => 'isGenericName',
				'type' => 'select',
				'list' => array(
					array('id' => 'long', 'name' => 'Long Description'),
					array('id' => 'short', 'name' => 'Short Description'),
					array('id' => 'combined', 'name' => 'Short Description + Long Description')
				),
				'identifier' => 'id',
				'required' => true
			),
			'EMAGMP_ORDER_DELIVERY_OPTION_ID' => array(
				'title' => $this->l('Delivery Option'),
				'desc' => $this->l('The default delivery option used to import orders with'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $delivery_options,
				'identifier' => 'id',
				'required' => true
			),
			'EMAGMP_ORDER_STATE_ID_INITIAL' => array(
				'title' => $this->l('Initial Order Status'),
				'desc' => $this->l('The default order status used to import orders with'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $order_states,
				'identifier' => 'id',
				'required' => true
			),
			'EMAGMP_ORDER_STATE_ID_FINALIZED' => array(
				'title' => $this->l('Finalized Order Status'),
				'desc' => $this->l('The order status that marks the order as finalized'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $order_states,
				'identifier' => 'id',
				'required' => true
			),
			'EMAGMP_ORDER_STATE_ID_CANCELLED' => array(
				'title' => $this->l('Cancelled Order Status'),
				'desc' => $this->l('The order status that marks the order as cancelled'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $order_states,
				'identifier' => 'id',
				'required' => true
			),
			'EMAGMP_HANDLING_TIME' => array(
				'title' => $this->l('Handling Time'),
				'desc' => $this->l('How many days until delivery of goods to the customer (0 = same day delivery)'),
				'validation' => 'isInt',
				'cast' => 'intval',
				'type' => 'text',
				'size' => 30,
				'default' => '1'
			),
			'EMAGMP_USE_EMAG_AWB' => array(
				'title' => $this->l('Use eMAG AWB'),
				'desc' => $this->l('Use the eMAG AWB system to generate dispatch documents and mark the orders as finalized'),
				'validation' => 'isBool',
				'cast' => 'intval',
				'type' => 'bool',
				'default' => '1'
			),
			'EMAGMP_AWB_SENDER_NAME' => array(
				'title' => $this->l('AWB Sender Name'),
				'desc' => $this->l('Sender name to use with the eMAG Marketplace AWB'),
				'validation' => 'isGenericName',
				'type' => 'text',
				'size' => 30
			),
			'EMAGMP_AWB_SENDER_CONTACT' => array(
				'title' => $this->l('AWB Sender Contact'),
				'desc' => $this->l('Sender contact name to use with the eMAG Marketplace AWB'),
				'validation' => 'isGenericName',
				'type' => 'text',
				'size' => 30
			),
			'EMAGMP_AWB_SENDER_PHONE' => array(
				'title' => $this->l('AWB Sender Phone'),
				'desc' => $this->l('Sender phone number to use with the eMAG Marketplace AWB'),
				'validation' => 'isGenericName',
				'type' => 'text',
				'size' => 30
			),
			'EMAGMP_AWB_SENDER_LOCALITY' => array(
				'title' => $this->l('AWB Sender Locality'),
				'desc' => $this->l('Sender locality to use with the eMAG Marketplace AWB'),
				'validation' => 'isGenericName',
				'type' => 'text',
				'size' => 30
			),
			'EMAGMP_AWB_SENDER_STREET' => array(
				'title' => $this->l('AWB Sender Street'),
				'desc' => $this->l('Sender street address to use with the eMAG Marketplace AWB'),
				'validation' => 'isGenericName',
				'type' => 'text',
				'size' => 30
			)
		);

		parent::__construct();

		$this->fields_options = array(
			'identity' => array(
				'title' =>	$this->l('Identity'),
				'fields' =>	$fields,
				'submit' => array('title' => $this->l('   Save   '), 'class' => 'button'),
				'buttons' => array(
					'testEmagMarketplaceConnectionBtn' => array(
						'title' => 'Test Connection',
						'name' => 'testEmagMarketplaceConnectionBtn',
						'short' => 'Test Connection',
						'desc' => $this->l('Test Connection'),
						'class' => 'btn btn-default',
						'icon' => 'process-icon-preview',
						'js' => 'testEmagMarketplaceConnection(\''.$this->token.'\');'
					),
					'downloadEmagMarketplaceLocalitiesBtn' => array(
						'title' => 'Download Localities',
						'name' => 'downloadEmagMarketplaceLocalitiesBtn',
						'href' => 'javascript:;',
						'js' => 'downloadEmagMarketplaceLocalities(\''.$this->token.'\')',
						'desc' => $this->l('Download Localities'),
						'class' => 'btn btn-default',
						'icon' => 'process-icon-download-alt'
					)
				)
			),
			'orders' => array(
				'title' =>	$this->l('Products and Orders'),
				'fields' =>	$fields2,
				'submit' => array('title' => $this->l('   Save   '), 'class' => 'button'),
				'buttons' => array(
					'nextEmagMarketplaceStepBtn' => array(
						'title' => 'Next Step',
						'name' => 'nextEmagMarketplaceStepBtn',
						'short' => 'Next Step',
						'desc' => $this->l('Next Step'),
						'class' => 'btn btn-default',
						'icon' => 'process-icon-circle-arrow-right',
						'href' => Link::getAdminLink('AdminEmagMarketplaceCategories')
					)
				)
			)
		);
	}

	public function postProcess()
	{
		$log_dir = _PS_MODULE_DIR_.$this->module->name.'/logs/';
		Tools::clearCache($this->context->smarty);
		parent::postProcess();
		$this->displayInformation($this->l('1) Please fill in the Identity form first and use the "Test Connection" button to make sure your settings are correct!'));
		$this->displayInformation($this->l('2) Save your changes and then download the eMAG localities!'));
		$this->displayInformation($this->l('3) Fill in the Orders form and then save your changes!'));
		$this->displayInformation($this->l('4) Install the following Cron job on your server: */5 * * * * wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'check_cron_jobs')).'" >> '.$log_dir.'check_cron_jobs.log'));
		$this->displayInformation($this->l('5) Install the following Cron job on your server: */5 * * * * wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'check_errors')).'" >> '.$log_dir.'check_errors.log'));
		$this->displayInformation($this->l('6) Install the following Cron job on your server: 0 5 * * * wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'clean_logs')).'" >> '.$log_dir.'clean_logs.log'));
		$this->displayInformation($this->l('7) Install the following Cron job on your server: * * * * * wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'get_orders')).'" >> '.$log_dir.'get_orders.log'));
		$this->displayInformation($this->l('8) Install the following Cron job on your server: 10 5 * * 1 wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'refresh_definitions')).'" >> '.$log_dir.'refresh_definitions.log'));
		$this->displayInformation($this->l('9) Install the following Cron job on your server: * * * * * wget -O - "'.$this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'run_queue')).'" >> '.$log_dir.'run_queue.log'));
		$this->displayInformation($this->l('10) Proceed to the next step!'));
		$this->context->smarty->assign(array(
			'link' => Context::getContext()->link
		));
	}
	
	public function ajaxProcessTestConnection()
	{
		$this->json = true;
		
		$emagmp_api_call = new EmagMarketplaceAPICall();
		
		$emagmp_api_call->emagmp_api_url = Tools::getValue('EMAGMP_API_URL');
		$emagmp_api_call->emagmp_vendorcode = Tools::getValue('EMAGMP_VENDORCODE');
		$emagmp_api_call->emagmp_vendorusername = Tools::getValue('EMAGMP_VENDORUSERNAME');
		$emagmp_api_call->emagmp_vendorpassword = Tools::getValue('EMAGMP_VENDORPASSWORD');
		
		$emagmp_api_call->resource = 'vat';
		$emagmp_api_call->action = 'read';
		
		$emagmp_api_call->execute();
		if ($emagmp_api_call->save())
		{
			if ($emagmp_api_call->status == 'error')
			{
				$this->errors[] = Tools::displayError('Connection could not be established! The API function returned the following error: '.htmlentities($emagmp_api_call->message_in));
			}
			elseif ($emagmp_api_call->status == 'success')
			{
				$this->confirmations[] = $this->l('Connection established successfully!');
			}
			else
			{
				$this->errors[] = Tools::displayError('The API call could not be executed!');
			}
		}
		else
		{
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not save the API call in the database!!!');
		}
		
		$this->status = 'ok';
	}
	
	public function initToolbar()
	{
		$this->toolbar_btn['testEmagMarketplaceConnection'] = array(
			'short' => 'Test Connection',
			'desc' => $this->l('Test Connection'),
			'class' => 'process-icon-preview',
			'js' => 'testEmagMarketplaceConnection(\''.$this->token.'\');'
		);
		parent::initToolbar();
		$this->toolbar_btn['downloadEmagMarketplaceLocalities'] = array(
			'href' => 'javascript:;',
			'js' => 'downloadEmagMarketplaceLocalities(\''.$this->token.'\')',
			'desc' => $this->l('Download Localities'),
			'class' => 'process-icon-refresh'
		);
		$this->toolbar_btn['nextEmagMarketplaceStep'] = array(
			'short' => 'Next Step',
			'desc' => $this->l('Next Step'),
			'class' => 'process-icon-circle-arrow-right',
			'href' => $this->context->link->getAdminLink('AdminEmagMarketplaceCategories')
		);
	}
	
	public function ajaxProcessDownloadEmagLocalities()
	{
		$this->json = true;
		
		$page = 1;
		$per_page = 2000;
		do {
			$emagmp_api_call = new EmagMarketplaceAPICall();
			$emagmp_api_call->resource = 'locality';
			$emagmp_api_call->action = 'read';
			$emagmp_api_call->data = array(
				'currentPage' => $page,
				'itemsPerPage' => $per_page
			);
			$emagmp_api_call->execute();
			$emagmp_api_call->save();
			if ($emagmp_api_call->status == 'success')
			{
				foreach ($emagmp_api_call->message_in_json->results as $locality_definition)
				{
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'emagmp_locality_definitions`
						SET
						`emag_locality_id` = '.(int)$locality_definition->emag_id.',
						`emag_region2_latin` = "'.pSQL($locality_definition->region2_latin).'",
						`emag_region3_latin` = "'.pSQL($locality_definition->region3_latin).'",
						`emag_name_latin` = "'.pSQL($locality_definition->name_latin).'"
						ON DUPLICATE KEY UPDATE
						`emag_region2_latin` = "'.pSQL($locality_definition->region2_latin).'",
						`emag_region3_latin` = "'.pSQL($locality_definition->region3_latin).'",
						`emag_name_latin` = "'.pSQL($locality_definition->name_latin).'"
					');
				}
				$page++;
			}
			else
			{
				$this->errors[] = Tools::displayError('Connection could not be established! The API function returned the following error: '.htmlentities($emagmp_api_call->message_in));
				$errors = true;
				break;
			}
		} while (count($emagmp_api_call->message_in_json->results) == $per_page);
		
		if (!$errors)
			$this->confirmations[] = $this->l('Localities downloaded successfully!');
		
		$this->status = 'ok';
	}
	
	public function init()
	{
		parent::init();
		$this->context->smarty->assign(array(
			'link' => Context::getContext()->link
		));
	}
	
	public function initContent()
	{
		parent::initContent();
		$this->context->smarty->assign(array(
			'link' => Context::getContext()->link
		));
	}
	
	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryPlugin(array('autocomplete'));
		$this->addJS($this->module->path."views/js/admin.js");
		$this->addCSS($this->module->path.'views/css/admin.css', 'all');
	}

}
