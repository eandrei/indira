<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelReport 
{
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
		
		
		$this->def_lists = array(
			'function_type' => array(
				'coupon'=>'Coupon',
				'giftcert'=>'Gift Certificate',
				'shipping'=>'Shipping',
				'buy_x_get_y'=>'Buy X Get Y',
				'parent'=>'Parent',
			),
			'function_type2_mode' => array(
				'include'=>'Include',
				'exclude'=>'Exclude',
			),
			'asset_type' => array(
				'product'=>'Product',
				'category'=>'Category',
				'manufacturer'=>'Manufacturer',
				'vendor'=>'Vendor',
				'shipping'=>'Shipping',
				'coupon'=>'Coupon',
			),
			'parent_type' => array(
				'first'=>'First Found Match',
				'lowest'=>'Lowest Value',
				'highest'=>'Highest Value',
				'all'=>'Apply All',
				'allonly'=>'Apply only if ALL apply',
			),
			'buy_xy_process_type' => array(
				'first'=>'First Found Match',
				'lowest'=>'Lowest Value',
				'highest'=>'Highest Value',
			),
			'published' => array(
				'1'=>'Published',
				'-1'=>'Unpublished',
				'-2'=>'Template',
			),
			'coupon_value_type' => array(
				'percent'=>'Percent',
				'amount'=>'Amount',
			),
			'discount_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'min_value_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'min_qty_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'user_type' => array(
				'user'=>'Customer',
				'usergroup'=>'Shopper Group',
			),
			'num_of_uses_type' => array(
				'total'=>'total',
				'per_user'=>'per customer',
			),	
			'expiration_type' => array(
				'day'=>'Day(s)',
				'month'=>'Month(s)',
				'year'=>'Year(s)',
			),
			'giftcert_message_type' => array(
				'text'=>'Text',
				'html'=>'HTML',
			),
			'status' => array(
				'active'=>'Active',
				'inactive'=>'Inactive',
				'used'=>'Value Used',
			),
			'asset_mode' => array(
				'include'=>'Include',
				'exclude'=>'Exclude',
			),
			
		);
		
	}

	public function getData($report_type)
	{
		// Lets load the files if it doesn't already exist
		if (empty($this->_data))
		{
						
			if ($report_type == 'coupon_list') $this->rpt_coupon_list();
			elseif ($report_type == 'purchased_giftcert_list') $this->rpt_purchased_giftcert_list();
			elseif ($report_type == 'coupon_vs_total') $this->rpt_coupon_vs_total();
			elseif ($report_type == 'coupon_vs_location') $this->rpt_coupon_vs_location();
			elseif ($report_type == 'history_uses_coupons') $this->rpt_history_uses_coupons();
			elseif ($report_type == 'history_uses_giftcerts') $this->rpt_history_uses_giftcerts();


		}

		return $this->_data;
	}

	
	/**
	 * Reports
	 **/

	public function rpt_coupon_list($force_all = false)
	{		
		$def_lists = $this->def_lists;
		$post = awoHelper::getValues($_REQUEST);
		
		$this->_data = null;
		$function_type		= $post['function_type'];
		$coupon_value_type	= $post['coupon_value_type'];
		$discount_type		= $post['discount_type'];
		$template			= (int)$post['templatelist'];
		$published			= $post['published'];
		
		@$id_shop = (int)$post['shoplist'];
		$shop_coupon_ids = '';
		if (!empty($id_shop))
		{
			$r = awoHelper::loadObjectList('SELECT coupon_id FROM '._DB_PREFIX_.'awocoupon_shop WHERE id_shop='.$id_shop, 'coupon_id');
			if (!empty($r)) $shop_coupon_ids .= implode(',', array_keys($r));
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c JOIN '._DB_PREFIX_.'orders o ON o.id_order=c.order_id WHERE id_shop='.$id_shop, 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c LEFT JOIN '._DB_PREFIX_.'awocoupon_shop s ON s.coupon_id=c.id WHERE s.coupon_id IS NULL AND (c.order_id IS NULL OR c.order_id=0)', 'id');			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
		}
		
		$coupon_ids = array();
		$sql = 'SELECT *
				  FROM '._DB_PREFIX_.'awocoupon
				 WHERE 1=1
				 '.(!empty($shop_coupon_ids) ? 'AND id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($function_type) ? 'AND function_type="'.$function_type.'" ' : '').'
				 '.(!empty($coupon_value_type) ? 'AND coupon_value_type="'.$coupon_value_type.'" ' : '').'
				 '.(!empty($discount_type) ? 'AND discount_type="'.$discount_type.'" ' : '').'
				 '.(!empty($template) ? 'AND template_id="'.$template.'" ' : '').'
				 '.(!empty($published) ? 'AND published="'.$published.'" ' : '');
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);
		
		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		foreach ($rtn as $row)
		{
			$coupon_ids[] = $row['id'];
			$row['params'] = json_decode($row['params']);
			$row['userlist'] = $row['userliststr'] 
			= $row['usergrouplist'] = $row['usergroupliststr'] 
			= $row['aseet1list'] = $row['asset1liststr'] 
			= $row['asset2list'] = $row['asset2liststr'] 
			= $row['tags'] 
				= array();

			$row['str_function_type'] = !empty($row['function_type']) ? $def_lists['function_type'][$row['function_type']] : '';
			$row['str_published'] = !empty($row['published']) ? $def_lists['published'][$row['published']] : '';
			$row['str_coupon_value_type'] = !empty($row['coupon_value_type']) ? $def_lists['coupon_value_type'][$row['coupon_value_type']] : '';
			$row['str_discount_type'] = !empty($row['discount_type']) ? $def_lists['discount_type'][$row['discount_type']] : '';
			$row['str_user_type'] = !empty($row['user_type']) ? $def_lists['user_type'][$row['user_type']] : '';
			$row['str_shipping_module'] = !empty($row['shipping_module']) ? $def_lists['shipping_module'][$row['shipping_module']] : '';
			$row['str_startdate'] = str_replace(array(':','-'), '', $row['startdate']);
			$row['str_expiration'] = str_replace(array(':','-'), '', $row['expiration']);
			$row['str_coupon_value'] = !empty($row['coupon_value']) ? round($row['coupon_value'], 2) : '';
			$row['str_min_value_type'] = !empty($row['min_value']) && !empty($row['params']->min_value_type) ? $def_lists['min_value_type'][$row['params']->min_value_type] : '';
			$row['str_min_value'] = !empty($row['min_value']) ? round($row['min_value'], 2) : '';
			$row['str_exclude_special'] = !empty($row['params']->exclude_special) ? $this->awocoupon->l('Yes') : $this->awocoupon->l('No');
			$row['str_exclude_giftcert'] = !empty($row['params']->exclude_giftcert) ? $this->awocoupon->l('Yes'): $this->awocoupon->l('No');
			$row['str_asset'] = '';
			$row['str_assetstr'] = '';
			$row['str_asset2'] = '';
			$row['str_assetstr2'] = '';
			$row['str_asset1_type'] = !empty($row['params']->asset1_type) ? $def_lists['asset_type'][$row['params']->asset1_type] : '';
			$row['str_asset2_type'] = !empty($row['params']->asset2_type) ? $def_lists['asset_type'][$row['params']->asset2_type] : '';
			$row['str_asset1_qty'] = !empty($row['params']->asset1_qty) ? $row['params']->asset1_qty : '';
			$row['str_asset2_qty'] = !empty($row['params']->asset2_qty) ? $row['params']->asset2_qty : '';
			$row['str_asset1_mode'] = !empty($row['params']->asset1_mode) ? $def_lists['function_type2_mode'][$row['params']->asset1_mode] : '';
			$row['str_asset2_mode'] = !empty($row['params']->asset2_mode) ? $def_lists['function_type2_mode'][$row['params']->asset2_mode] : '';
			$row['str_max_discount_qty'] = !empty($row['params']->max_discount_qty) ? $row['params']->max_discount_qty : '';
			$row['str_process_type'] = '';
			if ($row['function_type'] == 'parent') $row['str_process_type'] = $def_lists['parent_type'][$row['params']->process_type];
			elseif ($row['function_type'] == 'buy_x_get_y') $row['str_process_type'] = $def_lists['buy_xy_process_type'][$row['params']->process_type];
			$row['str_product_match'] = $row['function_type'] == 'buy_x_get_y' 
											? (empty($row['params']->product_match) ? $this->awocoupon->l('No') : $this->awocoupon->l('Yes'))
											: '';
			$row['str_addtocart'] = $row['function_type'] == 'buy_x_get_y' 
											? (empty($row['params']->addtocart) ? $this->awocoupon->l('No') : $this->awocoupon->l('Yes'))
											: '';
			$row['str_description'] = empty($row['description']) ? '' : $row['description'];
			$row['str_note'] = empty($row['note']) ? '' : $row['note'];
			$row['str_countrystate_mode'] = !empty($row['params']->countrystate_mode) ? $def_lists['asset_mode'][$row['params']->countrystate_mode] : '';
			$row['str_countrylist'] = '';
			$row['str_countryliststr'] = '';
			$row['str_statelist'] = '';
			$row['str_stateliststr'] = '';
			$row['str_min_qty_type'] = !empty($row['params']->min_qty) && !empty($row['params']->min_qty_type) ? $def_lists['min_qty_type'][$row['params']->min_qty_type] : '';
			$row['str_min_qty'] = !empty($row['params']->min_qty) ? (int)$row['params']->min_qty : '';
			
			$this->_data->rows[$row['id']] = $row;
		}
		
		if (!empty($coupon_ids))
		{
			
			//$coupon_ids = implode(',',$coupon_ids);
			$sql = 'SELECT a.coupon_id,a.user_id,CONCAT(u.lastname," ",u.firstname) as user_name
					  FROM '._DB_PREFIX_.'awocoupon_user a 
					  JOIN '._DB_PREFIX_.'customer u ON u.id_customer=a.user_id WHERE a.coupon_id IN ('.implode(',', $coupon_ids).')';
			$tmp = awoHelper::loadObjectList($sql);
			foreach ($tmp as $row)
			{
				$this->_data->rows[$row->coupon_id]['userlist'][] = $row->user_id;
				$this->_data->rows[$row->coupon_id]['userliststr'][] = $row->user_name;
			}
			
			$sql = 'SELECT a.coupon_id,a.shopper_group_id as user_id,g_l.name as user_name
					  FROM '._DB_PREFIX_.'awocoupon_usergroup a
					  JOIN '._DB_PREFIX_.'group u ON u.id_group=a.shopper_group_id
					  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=u.id_group
					 WHERE a.coupon_id IN ('.implode(',', $coupon_ids).')
					 GROUP BY a.coupon_id,a.shopper_group_id';
			$tmp = awoHelper::loadObjectList($sql);
			foreach ($tmp as $row)
			{
				$this->_data->rows[$row->coupon_id]['usergrouplist'][] = $row->user_id;
				$this->_data->rows[$row->coupon_id]['usergroupliststr'][] = $row->user_name;
			}
			
			
			$tmp = awohelper::getCouponCountryState($coupon_ids);
			foreach ($tmp[0] as $row)
			{
				$this->_data->rows[$row->coupon_id]['countrylist'][] = $row->asset_id;
				$this->_data->rows[$row->coupon_id]['countryliststr'][] = $row->asset_name;
			}
			foreach ($tmp[1] as $row)
			{
				$this->_data->rows[$row->coupon_id]['statelist'][] = $row->asset_id;
				$this->_data->rows[$row->coupon_id]['stateliststr'][] = $row->asset_name;
			}
			
			$sql = 'SELECT coupon_id,tag FROM #__awocoupon_tag WHERE coupon_id IN ('.implode(',',$coupon_ids).')';
			$tmp = awoHelper::loadObjectList($sql);
			foreach($tmp as $row) {
				$this->_data->rows[$row->coupon_id]['tags'][] = $row->tag;
			}
			

			$tmp = awoHelper::getAwoItem('1', $coupon_ids);
			foreach ($tmp as $row)
			{
				$this->_data->rows[$row->coupon_id]['asset1list'][] = $row->asset_id;
				$this->_data->rows[$row->coupon_id]['asset1liststr'][] = $row->asset_name;
			}
			$tmp = awoHelper::getAwoItem('2', $coupon_ids);
			foreach ($tmp as $row)
			{
				$this->_data->rows[$row->coupon_id]['asset2list'][] = $row->asset_id;
				$this->_data->rows[$row->coupon_id]['asset2liststr'][] = $row->asset_name;
			}
			
			
			
			foreach ($this->_data->rows as $k => $row)
			{
				if ($this->_data->rows[$k]['user_type'] == 'user' && !empty($this->_data->rows[$k]['userlist']))
				{
					$this->_data->rows[$k]['str_userlist'] = implode(',', $this->_data->rows[$k]['userlist']);
					$this->_data->rows[$k]['str_userliststr'] = implode(',', $this->_data->rows[$k]['userliststr']);
				}
				elseif ($this->_data->rows[$k]['user_type'] == 'usergroup' && !empty($this->_data->rows[$k]['usergrouplist']))
				{
					$this->_data->rows[$k]['str_userlist'] = implode(',', $this->_data->rows[$k]['usergrouplist']);
					$this->_data->rows[$k]['str_userliststr'] = implode(',', $this->_data->rows[$k]['usergroupliststr']);
				}
				else
				{
					$this->_data->rows[$k]['str_user_type'] 
						= $this->_data->rows[$k]['str_userlist'] 
						= $this->_data->rows[$k]['str_userliststr'] 
						= '';
				}
				
				if (!empty($this->_data->rows[$k]['countrylist']))
				{
					$this->_data->rows[$k]['str_countrylist'] = implode(',', $this->_data->rows[$k]['countrylist']);
					$this->_data->rows[$k]['str_countryliststr'] = implode(',', $this->_data->rows[$k]['countryliststr']);
				}
				if (!empty($this->_data->rows[$k]['statelist']))
				{
					$this->_data->rows[$k]['str_statelist'] = implode(',', $this->_data->rows[$k]['statelist']);
					$this->_data->rows[$k]['str_stateliststr'] = implode(',', $this->_data->rows[$k]['stateliststr']);
				}
				if (!empty($this->_data->rows[$k]['asset1list']))
				{
					$this->_data->rows[$k]['str_asset'] = implode(',', $this->_data->rows[$k]['asset1list']);
					$this->_data->rows[$k]['str_assetstr'] = implode(',', $this->_data->rows[$k]['asset1liststr']);
				}
				if (!empty($this->_data->rows[$k]['asset2list']))
				{
					$this->_data->rows[$k]['str_asset2'] = implode(',', $this->_data->rows[$k]['asset2list']);
					$this->_data->rows[$k]['str_assetstr2'] = implode(',', $this->_data->rows[$k]['asset2liststr']);
				}
				if (empty($this->_data->rows[$k]['str_asset'])) $this->_data->rows[$k]['str_function_type2_mode'] = '';
					
				$this->_data->rows[$k]['str_tags'] = implode(',',$this->_data->rows[$k]['tags']);
			}
		}
		
		
	}
	
	public function rpt_purchased_giftcert_list($force_all = false)
	{
		$this->_total = 0;
		$post = awoHelper::getValues($_REQUEST);

		$this->_data = null;
		$published			= $post['published'];
		$start_date			= $post['start_date'];
		$end_date			= $post['end_date'];
		@$order_status		= $post['order_status'];
		$giftcert_product	= (int)$post['giftcert_product'];
		
		@$id_shop = (int)$post['shoplist'];
		

		$datestr = '';
		if (!empty($start_date) && !empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) BETWEEN '.strtotime($start_date).' AND '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		elseif (!empty($start_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) >= '.strtotime($start_date).' ';
		elseif (!empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) <= '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		$initial_list = array();
		$coupon_ids = array();
		$sql = 'SELECT go.codes,gc.product_id,p.name AS product_name,
					 uv.id_customer AS user_id,uv.firstname as first_name,uv.lastname as last_name,u.email,
					 o.id_order AS order_id,o.total_paid AS order_total,UNIX_TIMESTAMP(o.date_add) AS ocdate,go.codes,
					 o.total_products_wt,o.total_shipping,o.total_discounts*-1 AS order_fee,
					 c.id,c.coupon_code,c.coupon_value_type,c.coupon_value,
					 c.min_value,c.discount_type,c.function_type,c.expiration,c.published
				 FROM #__awocoupon_giftcert_order_code gc
				 JOIN #__awocoupon_giftcert_order go ON go.id=gc.giftcert_order_id
				 LEFT JOIN #__awocoupon c ON c.id=gc.coupon_id
				 LEFT JOIN #__product_lang as p ON p.id_product=gc.product_id
				 LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_order=go.order_id
				 LEFT JOIN '._DB_PREFIX_.'address uv ON uv.id_address=o.id_address_invoice
				 LEFT JOIN '._DB_PREFIX_.'customer u ON u.id_customer=o.id_customer
				WHERE 1=1
				 '.$datestr.'
				 '.(!empty($id_shop) ? ' AND o.id_shop='.$id_shop : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND o.order_status IN ("'.implode('","', $order_status).'") ' : '').'
				 '.(!empty($published) ? 'AND c.published="'.$published.'" ' : '').'
				 GROUP BY gc.code
				 ORDER BY go.order_id';			
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);
		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		
		foreach ($rtn as $row)
		{
			$row['order_date'] = !empty($row['cdate']) ? date('Y-m-d', $row['cdate']) : '';
			$row['order_number'] = !empty($row['order_id']) ? sprintf('%08d', $row['order_id']) : '';
			
			$row['order_total'] = !empty($row['order_total']) ? number_format($row['order_total'], 2) : '';
			$row['total_products_wt'] = !empty($row['total_products_wt']) ? number_format($row['total_products_wt'], 2) : '';
			$row['total_shipping'] = !empty($row['total_shipping']) ? number_format($row['total_shipping'], 2) : '';
			$row['order_fee'] = !empty($row['order_fee']) ? number_format($row['order_fee'], 2) : '';

			$row['coupon_valuestr'] = number_format($row['coupon_value'], 2);
			
			$this->_data->rows[$row['id']] = $row;
		}
		
	}

	public function rpt_coupon_vs_total($force_all = false)
	{
		global $cookie;

		$post = awoHelper::getValues($_REQUEST);

		$this->_data = null;
		$function_type		= $post['function_type'];
		$coupon_value_type	= $post['coupon_value_type'];
		$discount_type		= $post['discount_type'];
		$published			= $post['published'];
		$start_date			= $post['start_date'];
		$end_date			= $post['end_date'];
		@$order_status		= $post['order_status'];
		$template			= (int)$post['templatelist'];
	
		@$id_shop = (int)$post['shoplist'];
		$shop_coupon_ids = '';
		if (!empty($id_shop))
		{
			$r = awoHelper::loadObjectList('SELECT coupon_id FROM '._DB_PREFIX_.'awocoupon_shop WHERE id_shop='.$id_shop, 'coupon_id');
			if (!empty($r)) $shop_coupon_ids .= implode(',', array_keys($r));
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c JOIN '._DB_PREFIX_.'orders o ON o.id_order=c.order_id WHERE id_shop='.$id_shop, 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c LEFT JOIN '._DB_PREFIX_.'awocoupon_shop s ON s.coupon_id=c.id WHERE s.coupon_id IS NULL AND (c.order_id IS NULL OR c.order_id=0)', 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
		}

	

		$datestr = '';
		if (!empty($start_date) && !empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) BETWEEN '.strtotime($start_date).' AND '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		elseif (!empty($start_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) >= '.strtotime($start_date).' ';
		elseif (!empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) <= '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		

		$sql = 'SELECT c.id, SUM(o.total_paid) as total, COUNT(c.id) as count
				  FROM '._DB_PREFIX_.'awocoupon c
				  JOIN (SELECT coupon_entered_id,order_id FROM '._DB_PREFIX_.'awocoupon_history GROUP BY order_id,coupon_entered_id) uu ON uu.coupon_entered_id=c.id
				  JOIN '._DB_PREFIX_.'orders o ON o.id_order=uu.order_id
				  LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = o.`id_order`)
				 WHERE 1=1
				 AND (oh.id_order IS NULL OR oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = o.`id_order` GROUP BY moh.`id_order`))
				 '.(!empty($shop_coupon_ids) ? 'AND c.id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND oh.id_order_state IN ('.implode(',', $order_status).') ' : '').'
				 '.$datestr.'
				 '.(!empty($function_type) ? ' AND c.function_type="'.$function_type.'" ' : '').'
				 '.(!empty($coupon_value_type) ? ' AND c.coupon_value_type="'.$coupon_value_type.'" ' : '').'
				 '.(!empty($discount_type) ? ' AND c.discount_type="'.$discount_type.'" ' : '').'
				 '.(!empty($template) ? ' AND c.template_id="'.$template.'" ' : '').'
				 '.(!empty($published) ? ' AND c.published="'.$published.'" ' : '').'
				 GROUP BY c.id';
		$order_details = awoHelper::loadAssocList($sql, 'id');

		//		  LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		//		  LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($cookie->id_lang).')

		
		$this->_data = new stdClass();
		$this->_data->total = $this->_data->count = 0;
		$coupon_ids = $productids = array();
		
		if (empty($order_details)) return;

		
		$sql = 'SELECT c.id,c.coupon_code, uu.productids,
						SUM(uu.coupon_discount+uu.shipping_discount) as discount
				  FROM '._DB_PREFIX_.'awocoupon_history uu
				  JOIN '._DB_PREFIX_.'awocoupon c ON c.id=uu.coupon_entered_id 
				 WHERE c.id IN ('.implode(',', array_keys($order_details)).')
				 GROUP BY c.id
				 ORDER BY c.coupon_code';
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);

		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		foreach ($rtn as $row)
		{
			$row['total'] = $order_details[$row['id']]['total'];
			$row['count'] = $order_details[$row['id']]['count'];
			
			$coupon_ids[] = $row['id'];
			$this->_data->total += $row['total'];
			$this->_data->count += $row['count'];
			
			$row['products'] = array();
			if (!empty($row['productids']))
			{
				$tmp = explode(',', $row['productids']);
				foreach ($tmp as $tmprow)
				{
					$tmpid = (int)$tmprow;
					$productids[$tmpid] = '';
					$row['products'][$tmpid] = &$productids[$tmpid];
				}
			}
			$row['totalstr'] = number_format($row['total'], 2);
			$row['discountstr'] = number_format($row['discount'], 2);
			$row['alltotal'] = 0;
			$row['allcount'] = 0;
			$this->_data->rows[] = $row;
		}
		
		if (!empty($this->_data->rows))
		{
			foreach ($this->_data->rows as $k => $row)
			{
				$this->_data->rows[$k]['alltotal'] = round($this->_data->rows[$k]['total'] / $this->_data->total * 100, 2).'%';
				$this->_data->rows[$k]['allcount'] = round($this->_data->rows[$k]['count'] / $this->_data->count * 100, 2).'%';
			}
			
			if (!empty($productids))
			{
				$sql = 'SELECT p.id_product AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'product p
						  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
						 WHERE 1=1
						 AND p.id_product IN ('.awoHelper::scrubids(array_keys($productids)).') 
						 ORDER BY label,p.id_product';
				$tmp = awoHelper::loadObjectList($sql, 'id');
				foreach ($tmp as $row) $productids[$row->id] = $row->label;
			}
		}
		
		
	}
	
	public function rpt_coupon_vs_location($force_all = false)
	{
		$post = awoHelper::getValues($_REQUEST);

		$this->_data = null;
		$function_type		= $post['function_type'];
		$coupon_value_type	= $post['coupon_value_type'];
		$discount_type		= $post['discount_type'];
		$published			= $post['published'];
		$start_date			= $post['start_date'];
		$end_date			= $post['end_date'];
		@$order_status		= $post['order_status'];
		$template			= (int)$post['templatelist'];
		
		@$id_shop = (int)$post['shoplist'];
		$shop_coupon_ids = '';
		if (!empty($id_shop))
		{
			$r = awoHelper::loadObjectList('SELECT coupon_id FROM '._DB_PREFIX_.'awocoupon_shop WHERE id_shop='.$id_shop, 'coupon_id');
			if (!empty($r)) $shop_coupon_ids .= implode(',', array_keys($r));
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c JOIN '._DB_PREFIX_.'orders o ON o.id_order=c.order_id WHERE id_shop='.$id_shop, 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c LEFT JOIN '._DB_PREFIX_.'awocoupon_shop s ON s.coupon_id=c.id WHERE s.coupon_id IS NULL AND (c.order_id IS NULL OR c.order_id=0)', 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
		}
		
		$this->_data = new stdClass();
		$this->_data->total = $this->_data->count = 0;
		$coupon_ids = $productids = array();

		
		
		$datestr = '';
		if (!empty($start_date) && !empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) BETWEEN '.strtotime($start_date).' AND '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		elseif (!empty($start_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) >= '.strtotime($start_date).' ';
		elseif (!empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) <= '.(strtotime($end_date) + (3600 * 24) - 1).' ';
				
		$sql = 'SELECT c.id,c.coupon_code, SUM(o.total_paid) as total, COUNT(uu.order_id) as count,ctry.iso_code as country,st.iso_code as state,u.city,
					 CONCAT(c.id,"-",IF(ISNULL(ctry.iso_code),"0",ctry.iso_code),"-",IF(ISNULL(st.iso_code),"0",st.iso_code),"-",u.city) as realid
				  FROM '._DB_PREFIX_.'awocoupon c
				  JOIN (SELECT coupon_entered_id,order_id FROM '._DB_PREFIX_.'awocoupon_history GROUP BY order_id,coupon_entered_id) uu ON uu.coupon_entered_id=c.id
				  JOIN '._DB_PREFIX_.'orders o ON o.id_order=uu.order_id
				  JOIN '._DB_PREFIX_.'address u ON u.id_address=o.id_address_invoice
				  LEFT JOIN '._DB_PREFIX_.'country ctry ON ctry.id_country=u.id_country
				  LEFT JOIN '._DB_PREFIX_.'state st ON st.id_state=u.id_state
				  LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = o.`id_order`)
				 WHERE 1=1
				 AND (oh.id_order IS NULL OR oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = o.`id_order` GROUP BY moh.`id_order`))
				 '.(!empty($shop_coupon_ids) ? 'AND c.id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND oh.id_order_state IN ('.implode(',', $order_status).') ' : '').'
				 '.$datestr.'
				 '.(!empty($function_type) ? ' AND c.function_type="'.$function_type.'" ' : '').'
				 '.(!empty($coupon_value_type) ? ' AND c.coupon_value_type="'.$coupon_value_type.'" ' : '').'
				 '.(!empty($discount_type) ? ' AND c.discount_type="'.$discount_type.'" ' : '').'
				 '.(!empty($template) ? ' AND c.template_id="'.$template.'" ' : '').'
				 '.(!empty($published) ? ' AND c.published="'.$published.'" ' : '').'
				 GROUP BY c.id,u.id_country,u.id_state,u.city';
		$order_details = awoHelper::loadAssocList($sql, 'realid');

		if (empty($order_details)) return;


		$sql = 'SELECT c.id,c.coupon_code, uu.productids,
						SUM(uu.coupon_discount+uu.shipping_discount) as discount,
						ctry.iso_code as country,st.iso_code as state,u.city
				  FROM '._DB_PREFIX_.'awocoupon_history uu
				  JOIN '._DB_PREFIX_.'awocoupon c ON c.id=uu.coupon_entered_id 
				  JOIN '._DB_PREFIX_.'orders o ON o.id_order=uu.order_id
				  JOIN '._DB_PREFIX_.'address u ON u.id_address=o.id_address_invoice
				  LEFT JOIN '._DB_PREFIX_.'country ctry ON ctry.id_country=u.id_country
				  LEFT JOIN '._DB_PREFIX_.'state st ON st.id_state=u.id_state
				 WHERE 1=1
				 '.(!empty($shop_coupon_ids) ? 'AND c.id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND o.order_status IN ("'.implode('","', $order_status).'") ' : '').'
				 '.$datestr.'
				 '.(!empty($function_type) ? ' AND c.function_type="'.$function_type.'" ' : '').'
				 '.(!empty($coupon_value_type) ? ' AND c.coupon_value_type="'.$coupon_value_type.'" ' : '').'
				 '.(!empty($discount_type) ? ' AND c.discount_type="'.$discount_type.'" ' : '').'
				 '.(!empty($template) ? ' AND c.template_id="'.$template.'" ' : '').'
				 '.(!empty($published) ? ' AND c.published="'.$published.'" ' : '').'
				 GROUP BY c.id,u.id_country,u.id_state,u.city
				 ORDER BY c.coupon_code';//exit($sql);
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);

		
		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		foreach ($rtn as $row)
		{
			$country_id = empty($row['country']) ? '0' : $row['country'];
			$state_id = empty($row['state']) ? '0' : $row['state'];
			
			$row['total'] = $order_details[$row['id'].'-'.$country_id.'-'.$state_id.'-'.$row['city']]['total'];
			$row['count'] = $order_details[$row['id'].'-'.$country_id.'-'.$state_id.'-'.$row['city']]['count'];
			$coupon_ids[] = $row['id'];
			$this->_data->total += $row['total'];
			$this->_data->count += $row['count'];
			
			$row['products'] = array();
			if (!empty($row['productids']))
			{
				$tmp = explode(',', $row['productids']);
				foreach ($tmp as $tmprow)
				{
					$tmpid = (int)$tmprow;
					$productids[$tmpid] = '';
					$row['products'][$tmpid] = &$productids[$tmpid];
				}
			}
			$row['totalstr'] = number_format($row['total'], 2);
			$row['discountstr'] = number_format($row['discount'], 2);
			$row['alltotal'] = 0;
			$row['allcount'] = 0;
			$this->_data->rows[] = $row;
		}
		
		if (!empty($this->_data->rows))
		{
			foreach ($this->_data->rows as $k => $row)
			{
				$this->_data->rows[$k]['alltotal'] = round($this->_data->rows[$k]['total'] / $this->_data->total * 100, 2).'%';
				$this->_data->rows[$k]['allcount'] = round($this->_data->rows[$k]['count'] / $this->_data->count * 100, 2).'%';
			}
			
			if (!empty($productids))
			{
				$sql = 'SELECT p.id_product AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'product p
						  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
						 WHERE 1=1
						 AND p.id_product IN ('.awoHelper::scrubids(array_keys($productids)).')
						 ORDER BY label,p.id_product';
				$tmp = awoHelper::loadObjectList($sql, 'id');
				foreach ($tmp as $row) $productids[$row->id] = $row->label;
			}
		}
		
	}
	
	public function rpt_history_uses_coupons($force_all = false)
	{
		$this->_total = 0;
		$post = awoHelper::getValues($_REQUEST);
		
		$this->_data = null;
		$function_type		= $post['function_type'];
		$coupon_value_type	= $post['coupon_value_type'];
		$discount_type		= $post['discount_type'];
		$published			= $post['published'];
		$start_date			= $post['start_date'];
		$end_date			= $post['end_date'];
		@$order_status		= $post['order_status'];
		$template			= (int)$post['templatelist'];

		@$id_shop = (int)$post['shoplist'];
		$shop_coupon_ids = '';
		if (!empty($id_shop))
		{
			$r = awoHelper::loadObjectList('SELECT coupon_id FROM '._DB_PREFIX_.'awocoupon_shop WHERE id_shop='.$id_shop, 'coupon_id');
			if (!empty($r)) $shop_coupon_ids .= implode(',', array_keys($r));
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c JOIN '._DB_PREFIX_.'orders o ON o.id_order=c.order_id WHERE id_shop='.$id_shop, 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c LEFT JOIN '._DB_PREFIX_.'awocoupon_shop s ON s.coupon_id=c.id WHERE s.coupon_id IS NULL AND (c.order_id IS NULL OR c.order_id=0)', 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
		}

		$datestr = '';
		if (!empty($start_date) && !empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(ov.date_add) BETWEEN '.strtotime($start_date).' AND '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		elseif (!empty($start_date))
			$datestr = ' AND UNIX_TIMESTAMP(ov.date_add) >= '.strtotime($start_date).' ';
		elseif (!empty($end_date)) 
			$datestr = ' AND UNIX_TIMESTAMP(ov.date_add) <= '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		
		$sql = 'SELECT c.id,c.coupon_code,c.num_of_uses_total,c.coupon_value_type,c.coupon_value,
					 c.min_value,c.discount_type,c.function_type,c.expiration,c.published,
					 uu.coupon_id,uu.coupon_entered_id,c2.coupon_code as coupon_entered_code,
					 uv.firstname as first_name,uv.lastname as last_name,uu.user_id,
					 (uu.coupon_discount+uu.shipping_discount) AS discount,uu.productids,uu.timestamp,
					 ov.id_order AS order_id,ov.total_paid,UNIX_TIMESTAMP(ov.date_add) AS cdate,uu.id as num_uses_id,
					 ov.total_products_wt,ov.total_shipping,ov.total_discounts*-1 AS order_fee
				 FROM '._DB_PREFIX_.'awocoupon c
				 JOIN '._DB_PREFIX_.'awocoupon_history uu ON uu.coupon_id=c.id
				 JOIN '._DB_PREFIX_.'awocoupon c2 ON c2.id=uu.coupon_entered_id
				 JOIN '._DB_PREFIX_.'customer uv ON uv.id_customer=uu.user_id
				 LEFT JOIN '._DB_PREFIX_.'orders ov ON ov.id_order=uu.order_id
				  LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = ov.`id_order`)
				WHERE 1=1
				 '.$datestr.'
				 AND (oh.id_order iS NULL OR oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = ov.`id_order` GROUP BY moh.`id_order`))
				 '.(!empty($shop_coupon_ids) ? 'AND c.id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND oh.id_order_state IN ('.implode(',', $order_status).') ' : '').'
				 '.(!empty($function_type) ? ' AND c.function_type="'.$function_type.'" ' : '').'
				 '.(!empty($coupon_value_type) ? ' AND c.coupon_value_type="'.$coupon_value_type.'" ' : '').'
				 '.(!empty($discount_type) ? ' AND c.discount_type="'.$discount_type.'" ' : '').'
				 '.(!empty($template) ? ' AND c.template_id="'.$template.'" ' : '').'
				 '.(!empty($published) ? ' AND c.published="'.$published.'" ' : '').'
				 ORDER BY uv.lastname,uv.firstname';//exit($sql);
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);
		
		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		foreach ($rtn as $row)
		{
			$row['order_date'] = !empty($row['cdate']) ? date('Y-m-d', $row['cdate']) : '';
			$row['order_number'] = !empty($row['order_id']) ? sprintf('%08d', $row['order_id']) : '';
			
			$row['total_paid'] = !empty($row['total_paid']) ? number_format($row['total_paid'], 2) : '';
			$row['total_products_wt'] = !empty($row['total_products_wt']) ? number_format($row['total_products_wt'], 2) : '';
			$row['total_shipping'] = !empty($row['total_shipping']) ? number_format($row['total_shipping'], 2) : '';
			$row['order_fee'] = !empty($row['order_fee']) ? number_format($row['order_fee'], 2) : '';
			
			$row['discountstr'] = number_format($row['discount'], 2);
			$row['coupon_code_str'] = $row['coupon_entered_code'].($row['coupon_id'] != $row['coupon_entered_id'] ? ' ('.$row['coupon_code'].')' : '');
			$this->_data->rows[$row['num_uses_id']] = $row;
		}
		
	}
	
	public function rpt_history_uses_giftcerts($force_all = false)
	{
		$this->_total = 0;
		
		$post = awoHelper::getValues($_REQUEST);

		$this->_data = null;
		$published			= $post['published'];
		$start_date			= $post['start_date'];
		$end_date			= $post['end_date'];
		@$order_status		= $post['order_status'];
		$giftcert_product	= (int)$post['giftcert_product'];

/*total_discounts
total_paid
total_paid_real
total_products
total_products_wt
total_shipping
total_wrapping*/

		

		@$id_shop = (int)$post['shoplist'];
		$shop_coupon_ids = '';
		if (!empty($id_shop))
		{
			$r = awoHelper::loadObjectList('SELECT coupon_id FROM '._DB_PREFIX_.'awocoupon_shop WHERE id_shop='.$id_shop, 'coupon_id');
			if (!empty($r)) $shop_coupon_ids .= implode(',', array_keys($r));
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c JOIN '._DB_PREFIX_.'orders o ON o.id_order=c.order_id WHERE id_shop='.$id_shop, 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
			$r = awoHelper::loadObjectList('SELECT c.id FROM '._DB_PREFIX_.'awocoupon c LEFT JOIN '._DB_PREFIX_.'awocoupon_shop s ON s.coupon_id=c.id WHERE s.coupon_id IS NULL AND (c.order_id IS NULL OR c.order_id=0)', 'id');
			if (!empty($r))
			{
				if (!empty($shop_coupon_ids)) $shop_coupon_ids .= ',';
				$shop_coupon_ids .= implode(',', array_keys($r));
			}
		}
		

		
		$datestr = '';
		if (!empty($start_date) && !empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) BETWEEN '.strtotime($start_date).' AND '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		elseif (!empty($start_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) >= '.strtotime($start_date).' ';
		elseif (!empty($end_date))
			$datestr = ' AND UNIX_TIMESTAMP(o.date_add) <= '.(strtotime($end_date) + (3600 * 24) - 1).' ';
		
		$sql = 'SELECT c.id,c.coupon_code,c.coupon_value_type,c.coupon_value,
					 c.min_value,c.discount_type,c.function_type,c.expiration,c.published,
					 uv.id_customer as user_id,uv.firstname as first_name,uv.lastname as last_name,
					 o.id_order,o.total_paid,UNIX_TIMESTAMP(o.date_add) AS cdate,go.codes,
					 o.total_products_wt,o.total_shipping,o.total_discounts*-1 AS order_fee,
					 SUM(au.coupon_discount)+SUM(au.shipping_discount) AS coupon_value_used,
					 c.coupon_value-IFNULL(SUM(au.coupon_discount),0)-IFNULL(SUM(au.shipping_discount),0) AS balance,gc.product_id,p.name AS product_name
				 FROM #__awocoupon c
				 LEFT JOIN #__orders o ON o.id_order=c.order_id
				 LEFT JOIN #__address uv ON uv.id_address=o.id_address_invoice
				 LEFT JOIN #__awocoupon_history au ON au.coupon_id=c.id
				 LEFT JOIN #__awocoupon_giftcert_order go ON go.order_id=o.id_order
				 LEFT JOIN #__awocoupon_giftcert_order_code gc ON gc.giftcert_order_id=go.id AND gc.coupon_id=c.id
				 LEFT JOIN #__product_lang as p ON p.id_product=gc.product_id
				 LEFT JOIN #__order_history oh ON oh.`id_order` = o.`id_order` AND oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = o.`id_order` GROUP BY moh.`id_order`)
				WHERE c.function_type="giftcert"
				 '.$datestr.'
				 '.(!empty($shop_coupon_ids) ? 'AND c.id IN ('.$shop_coupon_ids.') ' : '').'
				 '.(!empty($order_status) && is_array($order_status) ? ' AND oh.id_order_state IN ('.implode(',', $order_status).') ' : '').'
				 '.(!empty($published) ? 'AND c.published="'.$published.'" ' : '').'
				 '.(!empty($giftcert_product) ? 'AND gc.product_id="'.$giftcert_product.'" ' : '').'
				 GROUP BY c.id
				 ORDER BY uv.lastname,uv.firstname';//exit($sql);
		$rtn = awoHelper::loadAssocList($sql);
		$this->_total = count($rtn);
		
		if (!$force_all && $this->getState('limit') != 0) $rtn = array_slice($rtn, $this->getState('limitstart'), $this->getState('limit'));
		foreach ($rtn as $row)
		{
			$row['order_date'] = !empty($row['cdate']) ? date('Y-m-d', $row['cdate']) : '';
			$row['order_number'] = !empty($row['order_id']) ? sprintf('%08d', $row['order_id']) : '';
			
			$row['total_paid'] = !empty($row['total_paid']) ? number_format($row['total_paid'], 2) : '';
			$row['total_shipping'] = !empty($row['total_shipping']) ? number_format($row['total_shipping'], 2) : '';
			$row['total_products_wt'] = !empty($row['total_products_wt']) ? number_format($row['total_products_wt'], 2) : '';
			$row['order_fee'] = !empty($row['order_fee']) ? number_format($row['order_fee'], 2) : '';
			
			$row['coupon_valuestr'] = number_format($row['coupon_value'], 2);
			$row['coupon_value_usedstr'] = number_format($row['coupon_value_used'], 2);
			$row['balancestr'] = number_format($row['balance'], 2);
			$this->_data->rows[$row['id']] = $row;
		}
	}
	
	

	public function export($data)
	{
		if (empty($data['report_type']) || empty($data['rpt_labels']) || empty($data['rpt_columns'])) return;
		
		@$labels = json_decode($data['rpt_labels']);
		@$columns = json_decode($data['rpt_columns']);
				
		if (empty($labels) || empty($columns) || count($labels) != count($columns) || !method_exists('AwoCouponModelReport', 'rpt_'.$data['report_type'])) return;
		
		$columns = array_flip($columns);
		
		$this->_data = null;
		$this->{'rpt_'.$data['report_type']}(true);
		
		if (empty($this->_data)) return;
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();

		$delimiter = $params->get('csvDelimiter', ',');
		
		
		$output = '';
		$output .= $this->fputcsv2($labels, $delimiter);

		foreach ($this->_data->rows as $row)
		{
			$row = array_intersect_key($row, $columns);
			$d = array_merge($columns, $row);
			
			$output .= $this->fputcsv2($d, $delimiter);
		}
		
		return $output;
		
	}
	public function fputcsv2(array $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false)
	{ 
		$delimiter_esc = preg_quote($delimiter, '/'); 
		$enclosure_esc = preg_quote($enclosure, '/'); 

		$output = array(); 
		foreach ($fields as $field)
		{ 
			if ($field === null && $mysql_null)
			{ 
				$output[] = 'NULL'; 
				continue; 
			} 

			$output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? ( 
				$enclosure.str_replace($enclosure, $enclosure.$enclosure, $field).$enclosure 
			) : $field; 
		} 

		return join($delimiter, $output)."\n"; 
	} 

	
	
	public function getState($type)
	{
		if ($type == 'limit') 	return ((isset($_POST['limit']) && (int)($_POST['limit']) > 0)?(int)($_POST['limit']):10);
		elseif ($type == 'limitstart')
		{
			$page = ((isset($_POST['page']) && (int)($_POST['page']) > 0)?(int)($_POST['page']):1);
			return ($page - 1) * $this->getState('limit');

		}
	}
	public function getPagination() 
	{
		require_once _PS_MODULE_DIR_.'/awocoupon/lib/pagination.php';
		$pagination = new pagination();
		$pagination->setTotal($this->_total);
		$pagination->setLimit($this->getState('limit'));
		
		return $pagination->getPaginationHTML();
		
	}

	public function getOrderstatuses()
	{
		global $cookie;
		$sql = 'SELECT o.id_order_state,ol.name
				  FROM '._DB_PREFIX_.'order_state o
				  JOIN '._DB_PREFIX_.'order_state_lang ol ON ol.id_order_state=o.id_order_state AND ol.id_lang='.(int)$cookie->id_lang.'
				 ORDER BY ol.name,o.id_order_state';
		return awoHelper::loadObjectList($sql);
	}

	public function getGiftCertProducts()
	{
		$sql = 'SELECT g.*,lang.name as product_name,pr.title as profile, COUNT(pc.id) as codecount,c.coupon_code
				  FROM '._DB_PREFIX_.'awocoupon_giftcert_product g
				  LEFT JOIN '._DB_PREFIX_.'awocoupon c ON c.id=g.coupon_template_id
				  LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product=g.product_id
				  LEFT JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
				  LEFT JOIN '._DB_PREFIX_.'awocoupon_profile pr ON pr.id=g.profile_id
				  LEFT JOIN '._DB_PREFIX_.'awocoupon_giftcert_code pc ON pc.product_id=p.id_product
			   GROUP BY g.id
			   ORDER BY product_name,g.product_id ';//echo $sql; exit;
		return awoHelper::loadObjectList($sql);
	}
	
	public function getTemplateList()
	{
		return awoHelper::loadObjectList('SELECT id,coupon_code FROM '._DB_PREFIX_.'awocoupon WHERE published=-2 ORDER BY coupon_code,id', 'id');
	}

	public function getShopList()
	{
		return !awoHelper::is_multistore() ? array() : awoHelper::loadObjectList('SELECT id_shop,name FROM '._DB_PREFIX_.'shop WHERE active=1 AND deleted=0 ORDER BY name,id_shop', 'id_shop');
	}
	
}
