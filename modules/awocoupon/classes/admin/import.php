<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

class AwoCouponModelImport
{
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
		$this->keys = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL');
	}
	
	public function map_keys($row)
	{
		$rtn = array();
		return array_combine($this->keys, $row);
	}

	/**
	 * Method to store the entry
	 **/
	public function store($data, $store_none_errors)
	{
		$number_of_rows = count($this->keys);

		$error_check_only = $store_none_errors ? false : true;
		
		$arrdistinctcodes = array();
		foreach ($data as $row)
			@$arrdistinctcodes[$row[1]]++;
		

		$_map = array(
			'function_type' => array(
				trim(strtolower($this->awocoupon->l('Coupon')))=>'coupon',
				trim(strtolower($this->awocoupon->l('Gift Certificate')))=>'giftcert',
				trim(strtolower($this->awocoupon->l('Shipping')))=>'shipping',
				trim(strtolower($this->awocoupon->l('Parent')))=>'parent',
				trim(strtolower($this->awocoupon->l('Buy X Get Y')))=>'buy_x_get_y',
			),
			'asset_type' => array(
				trim(strtolower($this->awocoupon->l('Product')))=>'product',
				trim(strtolower($this->awocoupon->l('Category')))=>'category',
				trim(strtolower($this->awocoupon->l('Manufacturer')))=>'manufacturer',
				trim(strtolower($this->awocoupon->l('Vendor')))=>'vendor',
				trim(strtolower($this->awocoupon->l('Shipping')))=>'shipping',
				trim(strtolower($this->awocoupon->l('Coupon')))=>'coupon',
			),
			'parent_type' => array(
				trim(strtolower($this->awocoupon->l('First found match')))=>'first',
				trim(strtolower($this->awocoupon->l('All that apply')))=>'all',
				trim(strtolower($this->awocoupon->l('Lowest Value')))=>'lowest',
				trim(strtolower($this->awocoupon->l('Highest Value')))=>'highest',
			),
			'buy_xy_process_type' => array(
				trim(strtolower($this->awocoupon->l('First found match')))=>'first',
				trim(strtolower($this->awocoupon->l('Lowest Value')))=>'lowest',
				trim(strtolower($this->awocoupon->l('Highest Value')))=>'highest',
			),
			'asset_mode' => array(
				trim(strtolower($this->awocoupon->l('Include')))=>'include',
				trim(strtolower($this->awocoupon->l('Exclude')))=>'exclude',
			),
			'published' => array(
				trim(strtolower($this->awocoupon->l('Published')))=>1,
				trim(strtolower($this->awocoupon->l('Unpublished')))=>-1,
				trim(strtolower($this->awocoupon->l('Template')))=>-2,
			),
			'coupon_value_type' => array(
				trim(strtolower($this->awocoupon->l('Percent')))=>'percent',
				trim(strtolower($this->awocoupon->l('Amount')))=>'amount',
			),
			'discount_type' => array(
				trim(strtolower($this->awocoupon->l('Overall')))=>'overall',
				trim(strtolower($this->awocoupon->l('Specific')))=>'specific',
			),
			'min_value_type' => array(
				trim(strtolower($this->awocoupon->l('Overall')))=>'overall',
				trim(strtolower($this->awocoupon->l('Specific')))=>'specific',
			),
			'user_type' => array(
				trim(strtolower($this->awocoupon->l('Customer')))=>'user',
				trim(strtolower($this->awocoupon->l('Shopper Group')))=>'usergroup',
			),
			'exclude_special' => array(
				trim(strtolower($this->awocoupon->l('Yes')))=>1,
				trim(strtolower($this->awocoupon->l('No')))=>0,
			),
			'exclude_giftcert' => array(
				trim(strtolower($this->awocoupon->l('Yes')))=>1,
				trim(strtolower($this->awocoupon->l('No')))=>0,
			),
			'product_match' => array(
				trim(strtolower($this->awocoupon->l('Yes')))=>1,
				trim(strtolower($this->awocoupon->l('No')))=>0,
			),
			'addtocart' => array(
				trim(strtolower($this->awocoupon->l('Yes')))=>1,
				trim(strtolower($this->awocoupon->l('No')))=>0,
			),
		);

		$_map_user = array_keys(awoHelper::loadObjectList('SELECT id_customer FROM '._DB_PREFIX_.'customer', 'id_customer'));
		$_map_usergroup = array_keys(awoHelper::loadObjectList('SELECT id_group FROM '._DB_PREFIX_.'group', 'id_group'));
		$_map_product = array_keys(awoHelper::loadObjectList('SELECT id_product FROM '._DB_PREFIX_.'product', 'id_product'));
		$_map_coupon = array_keys(awoHelper::loadObjectList('SELECT id FROM '._DB_PREFIX_.'awocoupon', 'id'));
		$_map_category = array_keys(awoHelper::loadObjectList('SELECT id_category FROM '._DB_PREFIX_.'category', 'id_category'));
		$_map_manufacturer = array_keys(awoHelper::loadObjectList('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer', 'id_manufacturer'));
		$_map_vendor = array_keys(awoHelper::loadObjectList('SELECT id_supplier FROM '._DB_PREFIX_.'supplier', 'id_supplier'));
		$_map_shipping = array_keys(awoHelper::loadObjectList('SELECT id_carrier FROM '._DB_PREFIX_.'carrier', 'id_carrier'));
		$_map_country = array_keys(awoHelper::getCountryList());
		$_map_countrystate = array_keys(awoHelper::getCountryStateList());



		$errors = array();
		$datadistinct = array();
		foreach ($data as $row)
		{
			if (count($row) > $number_of_rows) $row = array_slice($row, 0, $number_of_rows);
			
		// first level error checking
			$id = $row[0];
			if (trim($id) == '')
			{
				$errors['          '][] = $this->awocoupon->l('No Id Specified');
				continue;
			}
			if (isset($datadistinct[$id]))
			{
				$errors[$id][] = $this->awocoupon->l('Duplicate Id fields');
				continue;
			}
			if ($arrdistinctcodes[$row[1]] > 1)
			{
				$errors[$id][] = $this->awocoupon->l('That coupon code already exists. Please try again');
				//continue;
			}
			if (count($row) < 8)
			{
				$errors[$id][] = $this->awocoupon->l('Not enough columns to process');
				continue;
			}
			$row = array_pad($row, $number_of_rows, '');
			
			$row = $this->map_keys($row);

			$datadistinct[$id] = $row;

			if (empty($row['B'])) $errors[$id][] = '[B] '.$this->awocoupon->l('Coupon: please enter a valid value');
			$datadistinct[$id]['C'] = $row['C'] = trim(strtolower($row['C']));
			if (empty($_map['published'][$row['C']])) $errors[$id][] = '[C] '.$this->awocoupon->l('Published: please enter a valid value');
			$datadistinct[$id]['D'] = $row['D'] = trim(strtolower($row['D']));
			if (empty($_map['function_type'][$row['D']])) $errors[$id][] = '[D] '.$this->awocoupon->l('Function Type: please enter a valid value');
			if (!empty($row['I']) && !ctype_digit($row['I'])) $errors[$id][] = '[I] '.$this->awocoupon->l('Number of Uses Total: please enter a valid value');
			if (!empty($row['J']) && !ctype_digit($row['J'])) $errors[$id][] = '[J] '.$this->awocoupon->l('Number of Uses Per Customer: please enter a valid value');
			$datadistinct[$id]['AA'] = trim($row['AA']);
			$datadistinct[$id]['AK'] = trim($row['AK']);
			$datadistinct[$id]['AL'] = trim($row['AL']);
			
			if ($_map['function_type'][$row['D']] == 'parent')
			{
				$datadistinct[$id]['AB'] = $row['AB'] = trim(strtolower($row['AB']));
				if (empty($row['AB']) || empty($_map['parent_type'][$row['AB']])) $errors[$id][] = '[AB] '.$this->awocoupon->l('Process Type: please enter a valid value');
			} 
			else
			{
				$datadistinct[$id]['E'] = $row['E'] = trim(strtolower($row['E']));
				if (!empty($row['E']) && empty($_map['coupon_value_type'][$row['E']])) $errors[$id][] = '[E] '.$this->awocoupon->l('Value Type: please enter a valid value');
				$datadistinct[$id]['F'] = $row['F'] = trim(strtolower($row['F']));
				if (!empty($row['F']) && empty($_map['discount_type'][$row['F']])) $errors[$id][] = '[F] '.$this->awocoupon->l('Discount Type: please enter a valid value');
				if (!empty($row['G']) && (!is_numeric($row['G']) || $row['G'] < 0)) $errors[$id][] = '[G] '.$this->awocoupon->l('Value: please enter a valid value');

				// extra rows
				if (!empty($row['H']) && !preg_match('/^(\d+\-\d+;)+$/', $row['H'])) $errors[] = '[H] '.$this->awocoupon->l('Value Definition: please enter a valid value');
				$datadistinct[$id]['K'] = $row['K'] = trim(strtolower($row['K']));
				if (!empty($row['K']) && empty($_map['min_value_type'][$row['K']])) $errors[$id][] = '[K] '.$this->awocoupon->l('Minimum Value: please enter a valid value');
				if (!empty($row['L']) && (!is_numeric($row['L']) || $row['L'] < 0)) $errors[$id][] = '[L] '.$this->awocoupon->l('Minimum Value: please enter a valid value');
				$datadistinct[$id]['AI'] = $row['AI'] = trim(strtolower($row['AI']));
				if(!empty($row['AI']) && empty($_map['min_value_type'][$row['AI']])) $errors[$id][] = '[AI] '.$this->awocoupon->l('Minimum Product Quantity: please enter a valid value');
				if(!empty($row['AJ']) && (!ctype_digit($row['AJ']) || $row['AJ']<1)) $errors[$id][] = '[AJ] '.$this->awocoupon->l('Minimum Product Quantity: please enter a valid value');
				if (!empty($row['M']))
				{
					if (strlen($row['M']) == 8)
						if (!ctype_digit($row['M']))  $errors[$id][] = '[M] '.$this->awocoupon->l('Start Date: please enter a valid value');
					elseif (strlen($row['M']) == 15)
						if ((!ctype_digit(substr($row['M'], 0, 8)) || !ctype_digit(substr($row['M'], 9, 6))))  $errors[$id][] = '[M] '.$this->awocoupon->l('Start Date: please enter a valid value');
					else $errors[$id][] = '[M] '.$this->awocoupon->l('Start Date: please enter a valid value');
				}
				if (!empty($row['N']))
				{
					if (strlen($row['N']) == 8)
						if (!ctype_digit($row['N']))  $errors[$id][] = '[N] '.$this->awocoupon->l('Expiration: please enter a valid value');
					elseif (strlen($row['N']) == 15)
						if ((!ctype_digit(substr($row['N'], 0, 8)) || !ctype_digit(substr($row['N'], 9, 6))))  $errors[$id][] = '[N] '.$this->awocoupon->l('Expiration: please enter a valid value');
					else $errors[$id][] = '[N] '.$this->awocoupon->l('Expiration: please enter a valid value');
				}

				$datadistinct[$id]['O'] = $row['O'] = trim(strtolower($row['O']));
				if (!empty($row['P']) && !empty($_map['user_type'][$row['O']]))
				{
					if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['P'])) $errors[$id][] = '[P] '.$this->awocoupon->l('Asset: please enter a valid value');
					else
					{
						$map = null;
						switch ($row['O'])
						{
							case trim(strtolower($this->awocoupon->l('Customer'))) :
								$map = $_map_user;
								break;
							case trim(strtolower($this->awocoupon->l('Shopper Group'))) :
								$map = $_map_usergroup;
								break;
						}
						$users = explode(',', $row['P']);
						if (!empty($map))
						{
							$err = array_diff($users, $map);
							if (!empty($err)) $errors[$id][] = '[P] '.$this->awocoupon->l('Asset: One or more do not exist');
							$datadistinct[$id]['userlist'] = $users;
						}
					}		
				}		
				
				$datadistinct[$id]['R'] = $row['R'] = trim(strtolower($row['R']));
				if (!empty($row['R']) && empty($_map['asset_mode'][$row['R']])) $errors[$id][] = '[R] '.$this->awocoupon->l('Asset Mode: please enter a valid value');
				$datadistinct[$id]['V'] = $row['V'] = trim(strtolower($row['V']));
				if (!empty($row['V']) && empty($_map['asset_mode'][$row['V']])) $errors[$id][] = '[V] '.$this->awocoupon->l('Asset Mode: please enter a valid value');
				
				$datadistinct[$id]['Y'] = $row['Y'] = trim(strtolower($row['Y']));
				if (!empty($row['Y']) && !isset($_map['exclude_special'][$row['Y']])) $errors[$id][] = '[Y] '.$this->awocoupon->l('Exclude Products on Special: Invalid');
				
				$datadistinct[$id]['Z'] = $row['Z'] = trim(strtolower($row['Z']));
				if (!empty($row['Z']) && !isset($_map['exclude_giftcert'][$row['Z']])) $errors[$id][] = '[Z] '.$this->awocoupon->l('Exclude Gift Certificate Products: Invalid');
			}


			$datadistinct[$id]['Q'] = $row['Q'] = trim(strtolower($row['Q']));
			if ($_map['function_type'][$row['D']] == 'buy_x_get_y')
			{
				if (empty($row['T']) || !preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['T'])) $errors[$id][] = '[T] '.$this->awocoupon->l('Asset: please enter a valid value');

				$map = null;
				if (empty($_map['asset_type'][$row['Q']])) $errors[$id][] = '[Q] '.$this->awocoupon->l('Asset Type: please enter a valid value');
				if (empty($row['S']) || !ctype_digit($row['S'])) $errors[$id][] = '[S] '.$this->awocoupon->l('Buy X Number: please enter a valid value');

				switch ($row['Q'])
				{
					case trim(strtolower($this->awocoupon->l('Product'))) :
						$map = $_map_product;
						break;
					case trim(strtolower($this->awocoupon->l('Category'))) :
						$map = $_map_category;
						break;
					case trim(strtolower($this->awocoupon->l('Manufacturer'))) :
						$map = $_map_manufacturer;
						break;
					case trim(strtolower($this->awocoupon->l('Vendor'))) :
						$map = $_map_vendor;
						break;
					case trim(strtolower($this->awocoupon->l('Shipping'))) :
						$map = $_map_shipping;
						break;
					case trim(strtolower($this->awocoupon->l('Coupon'))) :
						$map = $_map_coupon;
						break;
				}
				$assets = explode(',', $row['T']);
				if (!empty($map))
				{
					$err = array_diff($assets, $map);
					if (!empty($err)) $errors[$id][] = '[T] '.$this->awocoupon->l('Asset: One or more do not exist');
					$datadistinct[$id]['assetlist'] = $assets;
				}
				
				$map = null;
				$datadistinct[$id]['U'] = $row['U'] = trim(strtolower($row['U']));
				if (empty($_map['asset_type'][$row['U']])) $errors[$id][] = '[U] '.$this->awocoupon->l('Asset Type: please enter a valid value');
				if (empty($row['W']) || !ctype_digit($row['W'])) $errors[$id][] = '[W] '.$this->awocoupon->l('Get Y Number: please enter a valid value');

				switch ($row['U'])
				{
					case trim(strtolower($this->awocoupon->l('Product'))) :
						$map = $_map_product;
						break;
					case trim(strtolower($this->awocoupon->l('Category'))) :
						$map = $_map_category;
						break;
					case trim(strtolower($this->awocoupon->l('Manufacturer'))) :
						$map = $_map_manufacturer;
						break;
					case trim(strtolower($this->awocoupon->l('Vendor'))) :
						$map = $_map_vendor;
						break;
					case trim(strtolower($this->awocoupon->l('Shipping'))) :
						$map = $_map_shipping;
						break;
					case trim(strtolower($this->awocoupon->l('Coupon'))) :
						$map = $_map_coupon;
						break;
				}
				$assets = explode(',', $row['X']);
				if (!empty($map))
				{
					$err = array_diff($assets, $map);
					if (!empty($err)) $errors[$id][] = '[X] '.$this->awocoupon->l('Asset 2: One or more do not exist');
					$datadistinct[$id]['assetlist2'] = $assets;
				}
				
				$datadistinct[$id]['AB'] = $row['AB'] = trim(strtolower($row['AB']));
				if (empty($row['AB']) || empty($_map['buy_xy_process_type'][$row['AB']])) $errors[$id][] = '[AB] '.$this->awocoupon->l('Process Type: please enter a valid value');
				
				if (!empty($row['AC']) && !ctype_digit($row['AC'])) $errors[$id][] = '[AC] '.$this->awocoupon->l('Maximum Discount Qty: please enter a valid value');
				$datadistinct[$id]['AD'] = $row['AD'] = trim(strtolower($row['AD']));
				if (!empty($row['AD']) && !isset($_map['product_match'][$row['AD']])) $errors[$id][] = '[AD] '.$this->awocoupon->l('Do not mix products: please enter a valid value');
				$datadistinct[$id]['AE'] = $row['AE'] = trim(strtolower($row['AE']));
				if (!empty($row['AE']) && !isset($_map['addtocart'][$row['AE']])) $errors[$id][] = '[AE] '.$this->awocoupon->l('Automatically add to cart "Get Y" product: please enter a valid value');
			}


			if (!empty($row['T']) && !empty($_map['asset_type'][$row['Q']]))
			{
				if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['T'])) $errors[$id][] = '[T] '.$this->awocoupon->l('Asset: please enter a valid value');
				else 
				{
					$map = null;
					switch ($row['Q'])
					{
						case trim(strtolower($this->awocoupon->l('Product'))) :
							$map = $_map_product;
							break;
						case trim(strtolower($this->awocoupon->l('Category'))) :
							$map = $_map_category;
							break;
						case trim(strtolower($this->awocoupon->l('Manufacturer'))) :
							$map = $_map_manufacturer;
							break;
						case trim(strtolower($this->awocoupon->l('Vendor'))) :
							$map = $_map_vendor;
							break;
						case trim(strtolower($this->awocoupon->l('Shipping'))) :
							$map = $_map_shipping;
							break;
						case trim(strtolower($this->awocoupon->l('Coupon'))) :
							$map = $_map_coupon;
							break;
					}
					$assets = explode(',', $row['T']);
					if (!empty($map))
					{
						$err = array_diff($assets, $map);
						if (!empty($err)) $errors[$id][] = '[T] '.$this->awocoupon->l('Asset: One or more do not exist');
						$datadistinct[$id]['assetlist'] = $assets;
					}
						
				}		
			}		
			if ($_map['function_type'][$row['D']] == 'shipping' && !empty($row['X']))
			{
				if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['X'])) $errors[$id][] = '[X] '.$this->awocoupon->l('Asset 2: please enter a valid value');
				else
				{
					$map = null;
					$datadistinct[$id]['U'] = $row['U'] = trim(strtolower($row['U']));
					if (empty($_map['asset_type'][$row['U']])) $errors[$id][] = '[U] '.$this->awocoupon->l('Asset Type: please enter a valid value');
					switch ($row['U'])
					{
						case trim(strtolower($this->awocoupon->l('Product'))) :
							$map = $_map_product;
							break;
						case trim(strtolower($this->awocoupon->l('Category'))) :
							$map = $_map_category;
							break;
						case trim(strtolower($this->awocoupon->l('Manufacturer'))) :
							$map = $_map_manufacturer;
							break;
						case trim(strtolower($this->awocoupon->l('Vendor'))) :
							$map = $_map_vendor;
							break;
					}

					$assets = explode(',', $row['X']);
					$err = array_diff($assets, $map);
					if (!empty($err)) $errors[$id][] = '[X] '.$this->awocoupon->l('Asset 2: One or more do not exist');
					$datadistinct[$id]['assetlist2'] = $assets;
				}
			}
			
			if ($_map['function_type'][$row['D']] == 'giftcert' && !empty($row['X']))
			{
				if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['X'])) $errors[$id][] = '[X] '.$this->awocoupon->l('Asset 2: please enter a valid value');
				else
				{
					$shippings = explode(',', $row['X']);
					$err = array_diff($shippings, $_map_shipping);
					if (!empty($err)) $errors[$id][] = '[X] '.$this->awocoupon->l('Asset 2: One or more do not exist');
					$datadistinct[$id]['assetlist2'] = $shippings;
				}
			}
			
			
			if ($_map['function_type'][$row['D']] != 'giftcert')
			{
				if (!empty($row['AH']))
				{
					if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['AH'])) $errors[$id][] = '[AH]'.$this->awocoupon->l('State: please enter a valid value');
					else
					{
						$states = explode(',', $row['AH']);
						$err = array_diff($states, $_map_countrystate);
						if (!empty($err)) $errors[$id][] = '[AH] '.$this->awocoupon->l('State: One or more do not exist');
						$datadistinct[$id]['statelist'] = $states;

						$datadistinct[$id]['AF'] = $row['AF'] = trim(strtolower($row['AF']));
						if (!empty($row['AF']) && empty($_map['asset_mode'][$row['AF']])) $errors[$id][] = '[AF] '.$this->awocoupon->l('Asset Mode: please enter a valid value');
					}		
				}	
				elseif (!empty($row['AG']))
				{
					if (!preg_match('/^\s*\d+\s*(,\s*\d+)*\s*$/', $row['AG'])) $errors[$id][] = '[AG] '.$this->awocoupon->l('Country: please enter a valid value');
					else
					{
						$countries = explode(',', $row['AG']);
						$err = array_diff($countries, $_map_country);
						if (!empty($err)) $errors[$id][] = '[AG] '.$this->awocoupon->l('Country: One or more do not exist');
						$datadistinct[$id]['countrylist'] = $countries;

						$datadistinct[$id]['AF'] = $row['AF'] = trim(strtolower($row['AF']));
						if (!empty($row['AF']) && empty($_map['asset_mode'][$row['AF']])) $errors[$id][] = '[AF] '.$this->awocoupon->l('Asset Mode: please enter a valid value');
					}		
				}
			
			}
			
	
		}

//printr($datadistinct);




		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		$couponclass = new AwoCouponModelCoupon(); 

		$dbdata = array();
		foreach ($datadistinct as $id => $row)
		{
			if (empty($errors[$id]))
			{
				$startdate = $expiration = ''; 
				if (!empty($row['M']))
				{
					$startdate = substr($row['M'], 0, 4).'-'.substr($row['M'], 4, 2).'-'.substr($row['M'], 6, 2).' ';
					$startdate .= strlen($row['M']) == 8 ? '00:00:00' : substr($row['M'], 9, 2).':'.substr($row['M'], 11, 2).':'.substr($row['M'], 13, 2);
				}
				if (!empty($row['N']))
				{
					$expiration = substr($row['N'], 0, 4).'-'.substr($row['N'], 4, 2).'-'.substr($row['N'], 6, 2).' ';
					$expiration .= strlen($row['N']) == 8 ? '23:59:59' : substr($row['N'], 9, 2).':'.substr($row['N'], 11, 2).':'.substr($row['N'], 13, 2);
				}
				$mydata = array(
					'function_type'=>$_map['function_type'][$row['D']],
					'parent_type'=>$_map['function_type'][$row['D']] == 'parent' && isset($_map['parent_type'][$row['AB']]) ? $_map['parent_type'][$row['AB']] : '',
					'coupon_code'=>$row['B'],
					'published'=>$_map['published'][$row['C']],
					'coupon_value_type'=>isset($_map['coupon_value_type'][$row['E']]) ? $_map['coupon_value_type'][$row['E']] : '',
					'discount_type'=>isset($_map['discount_type'][$row['F']]) ? $_map['discount_type'][$row['F']] : '',
					'coupon_value'=>$row['G'],
					'coupon_value_def'=>$row['H'],
					'num_of_uses_total'=>$row['I'],
					'num_of_uses_percustomer'=>$row['J'],
					'min_value_type'=>$row['K'],
					'min_value'=>$row['L'],
					
					'startdate' => $startdate,
					'expiration' => $expiration,
					
					'exclude_special'=> !empty($_map['exclude_special'][$row['Y']]) ? $_map['exclude_special'][$row['Y']] : null,
					'exclude_giftcert'=>!empty($_map['exclude_giftcert'][$row['Z']]) ? $_map['exclude_giftcert'][$row['Z']] : null,
					
					'user_type'=>isset($_map['user_type'][$row['O']]) ? $_map['user_type'][$row['O']] : 'user',
					'userlist'=>!empty($row['userlist']) ? $row['userlist'] : array(),
					
					'countrylist'=>!empty($row['countrylist']) ? $row['countrylist'] : array(),
					'statelist'=>!empty($row['statelist']) ? $row['statelist'] : array(),
					'countrystate_mode'=>isset($_map['asset_mode'][$row['AF']]) ? $_map['asset_mode'][$row['AF']] : '',

					'description'=>$row['AK'],
					'tags'=>$row['AL'],
					'note'=>$row['AA'],
					
					'asset1_function_type'=>isset($_map['asset_type'][$row['Q']]) ? $_map['asset_type'][$row['Q']] : '',
					'asset2_function_type'=>isset($_map['asset_type'][$row['U']]) ? $_map['asset_type'][$row['U']] : '',
					'asset1_qty'=>$row['S'],
					'asset2_qty'=>$row['W'],
					'assetlist'=>!empty($row['assetlist']) ? $row['assetlist'] : array(),
					'assetlist2'=>!empty($row['assetlist2']) ? $row['assetlist2'] : array(),
					'asset1_mode'=>isset($_map['asset_mode'][$row['R']]) ? $_map['asset_mode'][$row['R']] : '',
					'asset2_mode'=>isset($_map['asset_mode'][$row['V']]) ? $_map['asset_mode'][$row['V']] : '',
					
					'buy_xy_process_type'=>$_map['function_type'][$row['D']] == 'buy_x_get_y' && isset($_map['buy_xy_process_type'][$row['AB']]) ? $_map['buy_xy_process_type'][$row['AB']] : '',
					'max_discount_qty'=>$row['AC'],
					'product_match'=>!empty($_map['product_match'][$row['AD']]) ? $_map['product_match'][$row['AD']] : null,
					'addtocart'=>!empty($_map['addtocart'][$row['AE']]) ? $_map['addtocart'][$row['AE']] : null,
					
					'min_qty_type'=>$row['AI'],
					'min_qty'=>$row['AJ'],
				);

				// check or insert into database
				$rtnErrors = $couponclass->storeEach($mydata, $error_check_only);
				if (!empty($rtnErrors)) $errors[$id] = $rtnErrors;
				else $dbdata[] = $mydata;
			}
			if ($error_check_only && count($dbdata) == count($data))
			{
			// if just check and there are no errors, insert everything
				foreach ($dbdata as $mydata)
				{
					$rtnErrors = $couponclass->storeEach($mydata);
					if (!empty($rtnErrors)) $errors[$id] = $rtnErrors;
				}
			}
		}	
		return $errors;		
	}
}
