<?php

/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7331 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../classes/emagmarketplaceapicall.php');
include_once(dirname(__FILE__).'/../../emagmarketplace.php');

class AdminEmagMarketplaceMainController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap = true;
	 	$this->table = 'emagmp_api_calls';
		$this->className = 'EmagMarketplaceAPICall';
		$this->list_no_link = true;

		$this->fields_list = array(
			'id_emagmp_api_call' => array(
				'title' => $this->l('ID'),
				'width' => 25
			),
			'date_created' => array(
				'title' => $this->l('Date Created'),
				'width' => 'auto'
			),
			'resource' => array(
				'title' => $this->l('Resource'),
				'width' => 'auto'
			),
			'action' => array(
				'title' => $this->l('Action'),
				'width' => 'auto'
			),
			'status' => array(
				'title' => $this->l('Status'),
				'width' => 'auto'
			),
			'message_in' => array(
				'title' => $this->l('Message'),
				'width' => 'auto',
				'callback' => 'resultMessages'
			),
			'date_sent' => array(
				'title' => $this->l('Date Sent'),
				'width' => 'auto'
			)
		);

		parent::__construct();
	}

	/**
	 * AdminController::renderList() override
	 * @see AdminController::renderList()
	 */
	public function renderList()
	{
	 	$this->_defaultOrderBy = 'id_emagmp_api_call';
	 	$this->_defaultOrderWay = 'DESC';

		return parent::renderList();
	}

	/**
	 * AdminController::initToolbar() override
	 * @see AdminController::initToolbar()
	 */
	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
	 	$this->toolbar_btn['reUploadEmagMarketplaceProducts'] = array(
			'short' => 'Re-Upload Products',
			'desc' => $this->l('Re-Upload Products'),
			'js' => 'uploadEmagMarketplaceProducts(\''.$this->token.'\', \''.$this->context->link->getAdminLink('AdminEmagMarketplaceMain').'\', \'self\')',
			'href' => 'javascript:;'
		);
	}

	/**
	 * AdminController::init() override
	 * @see AdminController::init()
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
		// toolbar (save, cancel, new, ..)
		$this->initToolbar();
		parent::initContent();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryPlugin(array('autocomplete'));
		$this->addJS($this->module->path."views/js/admin.js");
		$this->addCSS($this->module->path.'views/css/admin.css', 'all');
	}

	/**
	 * AdminController::getList() override
	 * @see AdminController::getList()
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
	}
	
	public function resultMessages($message_in, $api_call)
	{
		$message_in_json = Tools::jsonDecode($message_in);
		return implode('<BR>', (array)$message_in_json->messages);
	}
	
	public function ajaxProcessUploadProducts()
	{
		$this->json = true;
		
		set_time_limit(600);
		ignore_user_abort(true);
		
		$this->module->updateProduct(null, false);
		
		$this->confirmations[] = $this->l('Products added to upload queue successfully!');
		
		$this->status = 'ok';
	}

	public function displayAjaxSearchEmagLocalities()
	{
		if (Tools::getValue('autocomplete_classic'))
			$keyword = Tools::getValue('term');
		else
			$keyword = Tools::getValue('q');
		
		$results = array();
		$result = Db::getInstance()->executeS('
			SELECT * FROM `'._DB_PREFIX_.'emagmp_locality_definitions`
			WHERE emag_name_latin like \'%'.pSQL($keyword).'%\'
		', false);
		if ($result)
		{
			while ($row = Db::getInstance()->nextRow($result))
			{
				$option = array(
					'id' => $row['emag_locality_id'],
					'value' => $row['emag_name_latin'].', '.$row['emag_region3_latin'].', '.$row['emag_region2_latin'],
					'label' => $row['emag_name_latin'].', '.$row['emag_region3_latin'].', '.$row['emag_region2_latin'],
					'name' => $row['emag_name_latin'].', '.$row['emag_region3_latin'].', '.$row['emag_region2_latin'],
				);
				$results[] = $option;
			}
		}
		
		echo Tools::jsonEncode($results);
	}
	
	public function ajaxProcessGenerateEmagAWB()
	{
		$this->json = true;
		
		$id_order = Tools::getValue('id_order');
		$emag_locality_id = Tools::getValue('emag_locality_id');
		
		$order = new Order($id_order);
		
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_order_history`
			WHERE id_order = '.(int)$id_order.'
		');
		$order_history = $result[0];
		
		$result = Db::getInstance()->executeS('
			SELECT count(*) as pending_calls FROM `'._DB_PREFIX_.'emagmp_api_calls`
			WHERE id_order = '.(int)$id_order.' AND `status` IN ("pending", "running")
		');
		$pending_calls = $result[0]['pending_calls'];

		if ($order_history['awb_id'])
		{
			$this->errors[] = Tools::displayError('An eMAG Marketplace AWB has already been generated for this order! Please refresh this page and check the AWB download link!');
		}
		elseif ($pending_calls)
		{
			$this->errors[] = Tools::displayError('An update sent to the eMAG Marketplace, for this order, is still running in the background! Please try again in a few seconds!');
		}
		else
		{
			$configuration = Configuration::getMultiple(array('EMAGMP_AWB_SENDER_NAME', 'EMAGMP_AWB_SENDER_CONTACT', 'EMAGMP_AWB_SENDER_PHONE', 'EMAGMP_AWB_SENDER_LOCALITY', 'EMAGMP_AWB_SENDER_STREET'));
			
			$tmp = explode(", ", $configuration['EMAGMP_AWB_SENDER_CONTACT']);
			$result = Db::getInstance()->executeS('
				SELECT * FROM `'._DB_PREFIX_.'emagmp_locality_definitions`
				WHERE emag_region2_latin like \'%'.pSQL($tmp[2]).'%\' or emag_region3_latin like \'%'.pSQL($tmp[1]).'%\' or emag_name_latin like \'%'.pSQL($tmp[0]).'%\'
			');
			$sender_locality_id = $result[0]['emag_locality_id'];
			
			$addressDelivery = new Address($order->id_address_delivery, $this->context->language->id);
			if ($addressDelivery->id_state)
				$deliveryState = new State($addressDelivery->id_state);
			else
				$deliveryState = $addressDelivery->address2;
				
			$order_history['emag_definition'] = unserialize($order_history['emag_definition']);
			
			$emagmp_api_call = new EmagMarketplaceAPICall();
			$emagmp_api_call->resource = 'awb';
			$emagmp_api_call->action = 'save';
			$emagmp_api_call->data = array(
				'order_id' => $order_history['emag_order_id'],
				'sender' => array(
					'name' => $configuration['EMAGMP_AWB_SENDER_NAME'],
					'contact' => $configuration['EMAGMP_AWB_SENDER_CONTACT'],
					'phone1' => $configuration['EMAGMP_AWB_SENDER_PHONE'],
					'locality_id' => $sender_locality_id,
					'street' => $configuration['EMAGMP_AWB_SENDER_STREET']
				),
				'receiver' => array(
					'name' => $addressDelivery->company ? $addressDelivery->company : $addressDelivery->firstname.' '.$addressDelivery->lastname,
					'contact' =>  $addressDelivery->firstname.' '.$addressDelivery->lastname,
					'phone1' => $addressDelivery->phone,
					'locality_id' => $emag_locality_id,
					'street' => $addressDelivery->address1
				),
				'envelope_number' => 0,
				'parcel_number' => 1,
				'cod' => $order_history['emag_definition']->payment_mode_id == 1 ? $order->total_paid : 0
			);
			$emagmp_api_call->id_order = $order->id;
			$emagmp_api_call->execute();
			$emagmp_api_call->save();
			
			if ($emagmp_api_call->status == 'success')
			{
				$order_history['last_definition'] = unserialize($order_history['last_definition']);
				if (!is_array($order_history['last_definition']))
					$order_history['last_definition'] = array();

				$order_history['last_definition']['status'] = 4;
				
				$awb_id = $emagmp_api_call->message_in_json->results->awb[0]->emag_id;
				
				Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'emagmp_order_history` SET
					last_definition = \''.pSQL(serialize($order_history['last_definition']), true).'\',
					awb_id = '.(int)$awb_id.'
					WHERE id_order = '.(int)$id_order.'
				');
				
				$awb_number = $emagmp_api_call->message_in_json->results->awb[0]->awb_number;
				
				$order->shipping_number = $awb_number;
				$order->update();
				
				$order_carriers = $order->getShipping();
				foreach ((array) $order_carriers as $row)
				{
					$order_carrier = new OrderCarrier($row['id_order_carrier']);
					if (!Validate::isLoadedObject($order_carrier))
						continue;
					$order_carrier->tracking_number = $awb_number;
					$order_carrier->update();
					break;
				}
				
				$this->confirmations[] = $this->l('eMAG AWB generated successfully!');
			}
			else
				$this->errors[] = Tools::displayError('The eMAG AWB could not be generated! The API function returned the following error: '.htmlentities($emagmp_api_call->message_in));
		}
		
		$this->status = 'ok';
	}
	
	public function ajaxProcessRefreshEmagOrder()
	{
		$this->json = true;
		
		$id_order = Tools::getValue('id_order');
		$order = new Order($id_order);
		
		if (!Validate::isLoadedObject($order))
		{
			$this->status = 'ok';
			return;
		}
			
		$result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'emagmp_order_history`
			WHERE id_order = '.$order->id.'
		');
		
		if (!$result)
		{
			$this->status = 'ok';
			return;
		}
		
		$order_history = $result[0];
		
		if ($this->module->refreshEmagOrder($order, $order_history))
		{
			$this->confirmations[] = $this->l('Order refreshed successfully!');
		}
		else
			$this->errors[] = Tools::displayError('The order could not be refreshed! Something went wrong!');

		
		$this->status = 'ok';
	}

}
