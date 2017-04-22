<?php

include_once(dirname(__FILE__).'/../../classes/emagmarketplaceapicall.php');
include_once(dirname(__FILE__).'/../../classes/emagmarketplacepayment.php');
include_once(dirname(__FILE__).'/../../emagmarketplace.php');

class emagmarketplaceCronjobsModuleFrontController extends ModuleFrontController
{
	
	protected $actions = array(
		'check_cron_jobs' => 'initCheckCronJobs',
		'check_errors' => 'initCheckErrors',
		'clean_logs' => 'initCleanLogs',
		'get_orders' => 'initGetOrders',
		'import_order' => 'initImportOrder',
		'refresh_definitions' => 'initRefreshDefinitions',
		'run_queue' => 'initRunQueue'
	);

	protected $payment_modes = array(
		'1' => 'Cash on delivery',
		'2' => 'Bank transfer',
		'3' => 'Online card payment'
	);
		
	public function __construct()
	{
		$this->content_only = true;
		parent::__construct();
		
		if (class_exists('EmagMarketplaceCustomization'))
			$this->customizationObject = new EmagMarketplaceCustomization();
	}
	
	public function initContent()
	{
		error_reporting(E_ALL ^ E_NOTICE);
		ini_set('display_errors', 'on');
		set_time_limit(700);
		ignore_user_abort(true);
		
		$action = Tools::getValue('action');
		
		if (!isset($this->actions[$action]))
			return;

		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'emagmp_cron_jobs`
			SET running = 1, last_started = now()
			WHERE `name` = \''.pSQL($action).'\' and running = 0
		');
		if (!Db::getInstance()->Affected_Rows())
		{
			//echo 'Already running';
			return;
		}
		
		try
		{
			$this->{$this->actions[$action]}();
		}
		catch (Exception $e)
		{
			echo 'Ooops! Exception caught: '.$e->getMessage();
		}
		
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'emagmp_cron_jobs`
			SET running = 0, last_ended = now()
			WHERE `name` = \''.pSQL($action).'\'
		');
		
		//echo "\n\n\nDone!";
	}
	
	public function display()
	{
	}
	
	public function initCheckCronJobs()
	{
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_cron_jobs`
			WHERE running = 1 and last_started < date_sub(now(), interval 15 minute)
		');
		$stuck_jobs = array();
		foreach ($result as $row)
		{
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'emagmp_cron_jobs`
				SET running = 0
				WHERE `name` = \''.pSQL($row['name']).'\'
			');
			$stuck_jobs[] = $row['name'];
		}
		if (count($stuck_jobs))
		{
			$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_SHOP_NAME'));
			Mail::Send($this->context->language->id, 'cron_job_errors', 'eMAG Marketplace Cron Job Errors', array('{jobs}' => implode('<br>', $stuck_jobs)), $configuration['PS_SHOP_EMAIL'], NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/../../mails/');
		}
	}
	
	public function initCheckErrors()
	{
		$result = Db::getInstance()->executeS('
			SELECT COUNT(*) as errors
			FROM `'._DB_PREFIX_.'emagmp_api_calls`
			WHERE status = \'error\' and date_sent >= date_sub(now(), interval 5 minute)
		');
		if ($result[0]['errors'])
		{
			$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_SHOP_NAME'));
			Mail::Send($this->context->language->id, 'api_call_errors', 'eMAG Marketplace API Call Errors', array('{errors}' => $result[0]['errors']), $configuration['PS_SHOP_EMAIL'], NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/../../mails/');
		}
	}
	
	public function initCleanLogs()
	{
		Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'emagmp_api_calls`
			WHERE status in (\'success\', \'error\') and date_sent < date_sub(now(), interval 30 day)
		');
	}
	
	public function initRunQueue()
	{
		$run_queue_limit = Configuration::get('EMAGMP_PRODUCT_QUEUE_LIMIT');
		if (!$run_queue_limit)
			$run_queue_limit = 20;

		$queue_result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_api_calls`
			WHERE `status` = "pending"
			ORDER BY `id_emagmp_api_call`
			limit '.$run_queue_limit.'
		', false);
		
		if (!$queue_result)
			return;
			
		while ($row = Db::getInstance()->nextRow($queue_result))
		{
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'emagmp_api_calls`
				SET `status` = "running"
				WHERE `id_emagmp_api_call` = '.$row['id_emagmp_api_call'].' AND `status` = "pending"
			');
			
			if (!Db::getInstance()->Affected_Rows())
				continue;
				
			$emagmp_api_call = new EmagMarketplaceAPICall($row['id_emagmp_api_call']);
			$emagmp_api_call->execute();
			$emagmp_api_call->save();
		}
	}
	
	public function initGetOrders()
	{
		$emagmp_api_call = new EmagMarketplaceAPICall();
		$emagmp_api_call->resource = 'order';
		$emagmp_api_call->action = 'read';
		$emagmp_api_call->data = array(
			'status' => 1,
			'currentPage' => 1,
			'itemsPerPage' => 2
		);
		
		// delete this when finished testing :)
		/*$emagmp_api_call->data = array(
			'status' => 2,
			'currentPage' => 1,
			'itemsPerPage' => 2
		);
		Db::getInstance()->execute('
			TRUNCATE TABLE `'._DB_PREFIX_.'emagmp_order_history`
		');*/
		// delete this when finished testing :)
		
		$emagmp_api_call->execute();
		$emagmp_api_call->save();
		
		if ($emagmp_api_call->status == 'error')
			return;
		
		if (!count($emagmp_api_call->message_in_json->results))
			return;

		foreach ($emagmp_api_call->message_in_json->results as $emag_order)
		{
			$ch = curl_init();
			$url = $this->context->link->getModuleLink('emagmarketplace', 'cronjobs', array('action' => 'import_order'));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('emag_order' => Tools::jsonEncode($emag_order))));
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
		}
	}
	
	public function initImportOrder()
	{
		$emag_order = Tools::jsonDecode(Tools::getValue('emag_order'));
		
		if (!$emag_order)
			return;
		
		//echo "----------------------------------------------\n";
		
		$emag_order->id = (int)$emag_order->id;
		
		$errors = array();
		
		if (!isset($this->payment_modes[$emag_order->payment_mode_id]))
		{
			$errors[] = "Invalid payment_mode_id for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_order_history`
			WHERE emag_order_id = '.$emag_order->id.'
		');
		if (is_array($result[0]))
			return;

		Db::getInstance()->execute('
			INSERT IGNORE INTO `'._DB_PREFIX_.'emagmp_order_history`
			SET emag_order_id = '.$emag_order->id.',
			original_emag_definition = \''.pSQL(serialize($emag_order), true).'\',
			emag_definition = \''.pSQL(serialize($emag_order), true).'\'
		');
		$id_emagmp_order_history = Db::getInstance()->Insert_ID();
		//echo $id_emagmp_order_history."\n";
		if (!$id_emagmp_order_history)
		{
			// return anyway
			return;
		}
		
		/*echo "__IMPORT__\n";
		print_r($emag_order);
		echo "\n";*/
		
		$this->context->customer = new Customer();
		
		if (preg_match('`(.+?)\s(.+)`', $emag_order->customer->name, $match))
		{
			$this->context->customer->firstname = $match[1];
			$this->context->customer->lastname = $match[2];
		}
		else
		{
			$this->context->customer->firstname = $emag_order->customer->name;
			$this->context->customer->lastname = 'Customer';
		}
		$this->context->customer->email = $emag_order->customer->email;
		if (!Validate::isEmail($this->context->customer->email))
			$this->context->customer->email = Configuration::get('PS_SHOP_EMAIL');
		$this->context->customer->passwd = md5(time()._COOKIE_KEY_);
		$this->context->customer->active = 1;
		$this->context->customer->is_guest = 1;
		
		$this->context->customer->firstname = preg_replace('`[0-9]+`', '', $this->context->customer->firstname);
		$this->context->customer->lastname = preg_replace('`[0-9]+`', '', $this->context->customer->lastname);
		$validation_message = $this->context->customer->validateFields(false, true);
		if ($validation_message !== true)
		{
			$errors[] = "Could not validate customer for eMAG order #".$emag_order->id.", because: ".$validation_message;
			$this->emailErrors($errors);
			return;
		}
		
		if (!$this->context->customer->add())
		{
			$errors[] = "Could not save customer for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		$this->context->customer->cleanGroups();
		$this->context->customer->addGroups(array((int)Configuration::get('PS_GUEST_GROUP')));
		$this->context->cookie->id_customer = $this->context->customer->id;
		
		$billing_address = new Address();
		$billing_address->id_customer = $this->context->customer->id;
		$billing_address->id_country = Country::getByIso(Tools::strtoupper($emag_order->customer->billing_country));
		$billing_address->id_state = State::getIdByName($emag_order->customer->billing_suburb);
		$billing_address->alias = 'Billing address';
		$billing_address->company = $emag_order->customer->company;
		$billing_address->firstname = $this->context->customer->firstname;
		$billing_address->lastname = $this->context->customer->lastname;
		$billing_address->vat_number = $emag_order->customer->code;
		$billing_address->address1 = $emag_order->customer->billing_street;
		if (!$billing_address->id_state)
			$billing_address->address2 = $emag_order->customer->billing_suburb;
		$billing_address->postcode = $emag_order->customer->billing_postal_code;
		$billing_address->city = $emag_order->customer->billing_city;
		$billing_address->phone = $emag_order->customer->phone_1;
		$billing_address->phone_mobile = $emag_order->customer->phone_2;
		$billing_address->dni = null;
		
		if (is_callable(array($this->customizationObject, 'customizeBillingAddress'))) {
			$this->customizationObject->customizeBillingAddress($billing_address, $emag_order, $this->context);
		}
		
		if (!Validate::isAddress($billing_address->address1) || ($billing_address->address2 && !Validate::isAddress($billing_address->address2)))
		{
			$errors[] = "Billing address is invalid for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		if (!$billing_address->add())
		{
			$errors[] = "Could not save billing address for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		$shipping_address = new Address();
		$shipping_address->id_customer = $this->context->customer->id;
		$shipping_address->id_country = Country::getByIso(Tools::strtoupper($emag_order->customer->shipping_country));
		$shipping_address->id_state = State::getIdByName($emag_order->customer->shipping_suburb);
		$shipping_address->alias = 'Shipping address';
		$shipping_address->firstname = $this->context->customer->firstname;
		$shipping_address->lastname = $this->context->customer->lastname;
		$shipping_address->address1 = $emag_order->customer->shipping_street;
		if (!$shipping_address->id_state)
			$shipping_address->address2 = $emag_order->customer->shipping_suburb;
		$shipping_address->postcode = $emag_order->customer->shipping_postal_code;
		$shipping_address->city = $emag_order->customer->shipping_city;
		$shipping_address->phone = $emag_order->customer->phone_1;
		$shipping_address->phone_mobile = $emag_order->customer->phone_2;
		$shipping_address->dni = null;
		
		if (is_callable(array($this->customizationObject, 'customizeShippingAddress'))) {
			$this->customizationObject->customizeShippingAddress($shipping_address, $emag_order, $this->context);
		}
		
		if (!Validate::isAddress($shipping_address->address1) || ($shipping_address->address2 && !Validate::isAddress($shipping_address->address2)))
		{
			$errors[] = "Shipping address is invalid for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		if (!$shipping_address->add())
		{
			$errors[] = "Could not save shipping address for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		$this->context->cart = new Cart();
		$this->context->cart->id_customer = $this->context->customer->id;
		$this->context->cart->secure_key = $this->context->customer->secure_key;
		$this->context->cart->id_shop = (int)$this->context->shop->id;
		$this->context->cart->id_lang = $this->context->language->id;
		$this->context->cart->id_currency = $this->context->currency->id;
		$this->context->cart->id_address_invoice = $billing_address->id;
		$this->context->cart->id_address_delivery = $shipping_address->id;
		$this->context->cart->save();
		
		$this->context->cookie->id_cart = $this->context->cart->id;
		
		$nop = 0;
		foreach ($emag_order->products as $emag_product)
		{
			if (!$emag_product->status)
				continue;

			$emag_product->product_id = trim($emag_product->product_id);
			$result = Db::getInstance()->executeS('
				SELECT *
				FROM `'._DB_PREFIX_.'emagmp_product_combinations`
				WHERE combination_id = '.(int)$emag_product->product_id.'
			');
			$id_product = $result[0]['id_product'];
			$id_product_attribute = $result[0]['id_product_attribute'];
			$qty = trim($emag_product->quantity);
			$qty = (int)$qty;

			$product = new Product($id_product, true, $this->context->language->id);
			if (!Validate::isLoadedObject($product))
			{
				$errors[] = "Product REF '".$emag_product->part_number."' not found for eMAG order #".$emag_order->id;
				continue;
			}
			
			if ($id_product_attribute)
			{
				$qty = min($qty, StockAvailable::getQuantityAvailableByProduct(null, $id_product_attribute));
				$qty = max(0, $qty);
				if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($id_product_attribute, (int)$qty))
				{
					$errors[] = "Product REF '".$emag_product->part_number."' has insufficient stock for eMAG order #".$emag_order->id;
					continue;
				}
			}
			else
			{
				$qty = min($qty, StockAvailable::getQuantityAvailableByProduct($id_product, null));
				$qty = max(0, $qty);
				if (!$product->checkQty((int)$qty))
				{
					$errors[] = "Product REF '".$emag_product->part_number."' has insufficient stock for eMAG order #".$emag_order->id;
					continue;
				}
			}
			
			if ($qty == 0)
				continue;

			$qty_upd = $this->context->cart->updateQty($qty, $id_product, $id_product_attribute, 0, 'up');
			if (!$qty_upd)
			{
				$errors[] = "Product REF '".$emag_product->part_number."' has reached maximum available quantity for eMAG order #".$emag_order->id;
				continue;
			}
			elseif ($qty_upd < 0)
			{
				$errors[] = "Product REF '".$emag_product->part_number."' quantity is less than the minimal quantity required for eMAG order #".$emag_order->id;
				continue;
			}
			
			SpecificPrice::deleteByIdCart($this->context->cart->id, $id_product, $id_product_attribute);
			$specific_price = new SpecificPrice();
			$specific_price->id_cart = $this->context->cart->id;
			$specific_price->id_shop = 0;
			$specific_price->id_group_shop = 0;
			$specific_price->id_currency = 0;
			$specific_price->id_country = 0;
			$specific_price->id_group = 0;
			$specific_price->id_customer = $this->context->customer->id;
			$specific_price->id_product = $id_product;
			$specific_price->id_product_attribute = $id_product_attribute;
			$specific_price->price = (float)trim($emag_product->sale_price);
			$specific_price->from_quantity = 1;
			$specific_price->reduction = 0;
			$specific_price->reduction_type = 'amount';
			$specific_price->from = '0000-00-00 00:00:00';
			$specific_price->to = '0000-00-00 00:00:00';
			if (!$specific_price->add())
			{
				$errors[] = "Product REF '".$emag_product->part_number."' could not be saved with specific price for eMAG order #".$emag_order->id;
				continue;
			}
			$nop++;
		}
		
		if (!$nop)
		{
			$errors[] = "No valid products found for eMAG order #".$emag_order->id;
			$this->emailErrors($errors);
			return;
		}
		
		if ($errors)
			$this->emailErrors($errors);
					
		$this->context->cart->setDeliveryOption(array($this->context->cart->id_address_delivery => (int)Configuration::get('EMAGMP_ORDER_DELIVERY_OPTION_ID').','));
		$this->context->cart->save();
		
		$payment_module = new EmagMarketplacePayment();
		$payment_module->validateOrder((int)$this->context->cart->id, Configuration::get('EMAGMP_ORDER_STATE_ID_INITIAL'), $this->context->cart->getOrderTotal(true, Cart::BOTH), $this->payment_modes[$emag_order->payment_mode_id], 'Automatically imported from eMAG Marketplace', array(), null, false, $this->context->cart->secure_key);
		if ($payment_module->currentOrder)
		{
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'emagmp_order_history`
				SET id_order = '.(int)$payment_module->currentOrder.'
				WHERE id_emagmp_order_history = '.$id_emagmp_order_history.'
			');
			
			$emagmp_api_call = new EmagMarketplaceAPICall();
			$emagmp_api_call->resource = 'order';
			$emagmp_api_call->action = 'acknowledge';
			$emagmp_api_call->data = array(
				'id' => $emag_order->id
			);
			$emagmp_api_call->execute();
			$emagmp_api_call->save();
			
			$order = new Order($payment_module->currentOrder);
			$order->id_carrier = (int)Configuration::get('EMAGMP_ORDER_DELIVERY_OPTION_ID');
			
			$emag_order->shipping_tax = Tools::ps_round(trim($emag_order->shipping_tax), 2);
			$order_carriers = $order->getShipping();
			foreach ((array) $order_carriers as $row)
			{
				$order_carrier = new OrderCarrier($row['id_order_carrier']);
				if (!Validate::isLoadedObject($order_carrier))
				{
					$carrier = new Carrier((int)Configuration::get('EMAGMP_ORDER_DELIVERY_OPTION_ID'), $this->context->cart->id_lang);
					$order->carrier_tax_rate = $carrier->getTaxesRate(new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
					$order_carrier = new OrderCarrier();
					$order_carrier->id_order = (int)$order->id;
					$order_carrier->id_carrier = (int)Configuration::get('EMAGMP_ORDER_DELIVERY_OPTION_ID');
					$order_carrier->weight = (float)$order->getTotalWeight();
					$order_carrier->add();
				}
				$order_carrier->id_carrier = (int)Configuration::get('EMAGMP_ORDER_DELIVERY_OPTION_ID');
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
			
			$order->update();
			
			//echo "Imported ".(++$no_imported)."\n";
		}
	}
		
	public function emailErrors($errors)
	{
		if ($errors)
		{
			$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_SHOP_NAME'));
			Mail::Send($this->context->language->id, 'order_import_errors', 'eMAG Marketplace Import Errors', array('{errors}' => implode("<br />\r\n", $errors)), $configuration['PS_SHOP_EMAIL'], NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/../../mails/');
		}
	}
	
	public function initRefreshDefinitions()
	{
	}

}
