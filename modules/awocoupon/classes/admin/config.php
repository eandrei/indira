<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

class AwoCouponModelConfig  {
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
		$this->lang_ids = array(
			'errNoRecord',
			'errShopPermission',
			'errMinVal',
			'errMinQty',
			'errUserLogin',
			'errUserNotOnList',
			'errUserGroupNotOnList',
			'errUserMaxUse',
			'errTotalMaxUse',
			'errProductInclList',
			'errProductExclList',
			'errCategoryInclList',
			'errCategoryExclList',
			'errManufacturerInclList',
			'errManufacturerExclList',
			'errVendorInclList',
			'errVendorExclList',
			'errShippingSelect',
			'errShippingValid',
			'errShippingInclList',
			'errShippingExclList',
			'errGiftUsed',
			'errProgressiveThreshold',
			'errDiscountedExclude',
			'errGiftcertExclude',
			'errBuyXYList1IncludeEmpty',
			'errBuyXYList1ExcludeEmpty',
			'errBuyXYList2IncludeEmpty',
			'errBuyXYList2ExcludeEmpty', 
			'errCountryInclude',
			'errCountryExclude',
			'errCountryStateInclude',
			'errCountryStateExclude',
		);
	}



	public function getEntry()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$params = new awoParams();

		
		$casesensitive = $this->getCaseSensitive() ? 1 : 0;
		
		$entry = (object)array(
					'enable_store_coupon'=>$params->get('enable_store_coupon', 1),
					'enable_giftcert_discount_before_tax'=>$params->get('enable_giftcert_discount_before_tax', 0),
					'enable_coupon_discount_before_tax'=>$params->get('enable_coupon_discount_before_tax', 0),
					'enable_multiple_coupon'=>$params->get('enable_multiple_coupon', 0),
					'giftcert_vendor_enable'=>$params->get('giftcert_vendor_enable', 0),
					'csvDelimiter'=>$params->get('csvDelimiter', ','),
					'casesensitive'=> $casesensitive,
					'multiple_coupon_max'=>$params->get('multiple_coupon_max', ''),
					'multiple_coupon_max_auto'=>$params->get('multiple_coupon_max_auto', ''),
					'multiple_coupon_max_giftcert'=>$params->get('multiple_coupon_max_giftcert', ''),
					'multiple_coupon_max_coupon'=>$params->get('multiple_coupon_max_coupon', ''),
					'delete_expired'=>$params->get('delete_expired', ''),
					'giftcert_vendor_subject'=>$params->get('giftcert_vendor_subject', ''),
					'giftcert_vendor_email'=>$params->get('giftcert_vendor_email', ''),
					'giftcert_vendor_voucher_format'=>$params->get('giftcert_vendor_voucher_format', '<div>{voucher} - {price} - {product_name}</div>'),
					'errNoRecord'=>$params->get('errNoRecord', 'Coupon code not found'),
					'errShopPermission'=>$params->get('errShopPermission', 'Coupon code not found'),
					'errMinVal'=>$params->get('errMinVal', 'Coupon code not found'),
					'errMinQty'=>$params->get('errMinQty', 'Coupon code not found'),
					'errUserLogin'=>$params->get('errUserLogin', 'Coupon code not found'),
					'errUserNotOnList'=>$params->get('errUserNotOnList', 'Coupon code not found'),
					'errUserGroupNotOnList'=>$params->get('errUserGroupNotOnList', 'Coupon code not found'),
					'errUserMaxUse'=>$params->get('errUserMaxUse', 'Coupon code not found'),
					'errTotalMaxUse'=>$params->get('errTotalMaxUse', 'Coupon code not found'),
					'errProductInclList'=>$params->get('errProductInclList', 'Coupon code not found'),
					'errProductExclList'=>$params->get('errProductExclList', 'Coupon code not found'),
					'errCategoryInclList'=>$params->get('errCategoryInclList', 'Coupon code not found'),
					'errCategoryExclList'=>$params->get('errCategoryExclList', 'Coupon code not found'),
					'errManufacturerInclList'=>$params->get('errManufacturerInclList', 'Coupon code not found'),
					'errManufacturerExclList'=>$params->get('errManufacturerExclList', 'Coupon code not found'),
					'errVendorInclList'=>$params->get('errVendorInclList', 'Coupon code not found'),
					'errVendorExclList'=>$params->get('errVendorExclList', 'Coupon code not found'),
					'errShippingSelect'=>$params->get('errShippingSelect', 'Coupon code not found'),
					'errShippingValid'=>$params->get('errShippingValid', 'Coupon code not found'),
					'errShippingInclList'=>$params->get('errShippingInclList', 'Coupon code not found'),
					'errShippingExclList'=>$params->get('errShippingExclList', 'Coupon code not found'),
					'errGiftUsed'=>$params->get('errGiftUsed', 'Coupon code not found'),
					'errProgressiveThreshold'=>$params->get('errProgressiveThreshold', 'Coupon code not found'),
					'errDiscountedExclude'=>$params->get('errDiscountedExclude', 'Coupon code not found'),
					'errGiftcertExclude'=>$params->get('errGiftcertExclude', 'Coupon code not found'),
					'errBuyXYList1IncludeEmpty'=>$params->get('errBuyXYList1IncludeEmpty', 'Coupon code not found'),
					'errBuyXYList1ExcludeEmpty'=>$params->get('errBuyXYList1ExcludeEmpty', 'Coupon code not found'),
					'errBuyXYList2IncludeEmpty'=>$params->get('errBuyXYList2IncludeEmpty', 'Coupon code not found'),
					'errBuyXYList2ExcludeEmpty'=>$params->get('errBuyXYList2ExcludeEmpty', 'Coupon code not found'),
					'enable_frontend_image'=>$params->get('enable_frontend_image', ''),
					'giftcert_coupon_activate'=>$params->get('giftcert_coupon_activate', ''),
					'errCountryInclude'=>$params->get('errCountryInclude', 'Coupon code not found'),
					'errCountryExclude'=>$params->get('errCountryExclude', 'Coupon code not found'),
					'errCountryStateInclude'=>$params->get('errCountryStateInclude', 'Coupon code not found'),
					'errCountryStateExclude'=>$params->get('errCountryStateExclude', 'Coupon code not found'),
		);
		
		$entry->languages = array();
		$items = awoHelper::loadObjectList('
				SELECT c.name,l.elem_id,l.id_lang,l.text
				  FROM #__awocoupon_lang l 
				  JOIN #__awocoupon_config c ON c.value=l.elem_id 
				 WHERE c.name IN ("'.implode('","', $this->lang_ids).'")
			');
		foreach ($items as $item)
		{
			if (!isset($entry->languages[$item->id_lang])) $entry->languages[$item->id_lang] = new stdclass;
			$entry->languages[$item->id_lang]->{$item->name} = $item->text;
		}

					
//echo '<pre>'; print_r($entry);exit;
		return $entry;
	}

	
	public function getCaseSensitive()
	{
		$rtn = array_change_key_case((array)current(awoHelper::loadObjectList('SHOW FULL COLUMNS FROM '._DB_PREFIX_.'awocoupon LIKE "coupon_code"')));
		return substr($rtn['collation'], -4) == '_bin' ? true : false;
	}
		
	public function store($data)
	{
//printrx($data);

		if (!empty($data['params']))
		{
			require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
			$params = new awoParams();
			foreach ($data['params'] as $name => $value) $params->set($name, $value);
		}
		if (!empty($data['lang']))
		{
			foreach ($data['lang'] as $id_lang => $languages)
			{
				foreach ($languages as $name => $value)
				{
					$elem_id = awohelper::setLangData($params->get($name), $id_lang, $value);
					if (!empty($elem_id)) $params->set($name, $elem_id);
				}
			}

		}
		if (!empty($_POST['params']['giftcert_vendor_email']))
		{
			$value = $_POST['params']['giftcert_vendor_email'];
			awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon_config SET value="'.pSQL($value, true).'" WHERE name="giftcert_vendor_email"');
		}

				
		if (isset($data['casesensitive'],$data['casesensitiveold']) 
		&& $data['casesensitive'] != $data['casesensitiveold']
		&& ($data['casesensitive'] == 1 || $data['casesensitive'] == 0))
		{
			$sql = $data['casesensitive'] == 0 
					? 'ALTER TABLE `'._DB_PREFIX_.'awocoupon` MODIFY `coupon_code` VARCHAR(32) NOT NULL DEFAULT ""'
					: 'ALTER TABLE `'._DB_PREFIX_.'awocoupon` MODIFY `coupon_code` VARCHAR(32) BINARY NOT NULL DEFAULT ""';
			awoHelper::query($sql);
		}
				
		return true;
	}
		

}