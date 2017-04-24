<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelCoupon
{
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
	}

	public function getEntry()
	{
		$id = Tools::getValue('id', 0);
		$entry = awoHelper::dbTableRow('awocoupon', 'id', $id);

		$entry->userlist = $entry->assetlist = $entry->assetlist2 = $entry->shoplist = $entry->countrylist = $entry->statelist = array();		
		$entry->asset1_mode = null;
		$entry->asset2_mode = null;
		$entry->min_value_type = null;
		$entry->min_qty_type = null;
		$entry->min_qty = null;
		
		$entry->buy_xy_process_type = null;
		$entry->asset1_function_type = null;
		$entry->asset2_function_type = null;
		$entry->max_discount_qty = null;
		$entry->asset1_qty = null;
		$entry->asset2_qty = null;
		$entry->exclude_special = null;
		$entry->exclude_giftcert = null;
		$entry->product_match = null;
		$entry->addtocart = null;
		$entry->countrystate_mode = null;
		$entry->statelist_str = '';
		$entry->tags = null;

		if (empty($entry->id));
		else
		{
			$id_lang = (int)Context::getContext()->language->id;

			$entry->params = json_decode($entry->params);
			if (!empty($entry->params->asset1_mode)) $entry->asset1_mode = $entry->params->asset1_mode;
			if (!empty($entry->params->asset2_mode)) $entry->asset2_mode = $entry->params->asset2_mode;
			if (!empty($entry->params->asset1_type)) $entry->asset1_function_type = $entry->params->asset1_type;
			if (!empty($entry->params->asset2_type)) $entry->asset2_function_type = $entry->params->asset2_type;
			if (!empty($entry->params->min_value_type)) $entry->min_value_type = $entry->params->min_value_type;
			if (!empty($entry->params->exclude_special)) $entry->exclude_special = $entry->params->exclude_special;
			if (!empty($entry->params->exclude_giftcert)) $entry->exclude_giftcert = $entry->params->exclude_giftcert;
			if (!empty($entry->params->min_qty_type)) $entry->min_qty_type = $entry->params->min_qty_type;
			if (!empty($entry->params->min_qty)) $entry->min_qty = $entry->params->min_qty;
			
			
			
			if (awoHelper::is_multistore())
			{
				$sql = 'SELECT a.coupon_id,a.id_shop,u.name
						  FROM '._DB_PREFIX_.'awocoupon_shop a 
						  JOIN '._DB_PREFIX_.'shop u ON u.id_shop=a.id_shop 
						 WHERE a.coupon_id IN ('.$entry->id.')';
				$entry->shoplist = awoHelper::loadObjectList($sql);
			}
			if ($entry->user_type == 'user')
			{
				$sql = 'SELECT a.coupon_id,a.user_id,CONCAT(u.firstname," ",u.lastname) AS user_name 
						  FROM '._DB_PREFIX_.'awocoupon_user a 
						  JOIN '._DB_PREFIX_.'customer u ON u.id_customer=a.user_id 
						 WHERE a.coupon_id IN ('.$entry->id.')';
				$entry->userlist = awoHelper::loadObjectList($sql);
			}
			elseif ($entry->user_type == 'usergroup')
			{
				$sql = 'SELECT a.coupon_id,a.shopper_group_id as user_id,g_l.name as user_name
						  FROM '._DB_PREFIX_.'awocoupon_usergroup a
						  JOIN '._DB_PREFIX_.'group g ON g.id_group=a.shopper_group_id
						  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=g.id_group
						 WHERE g_l.id_lang='.$id_lang.' AND a.coupon_id IN ('.$entry->id.')';
				$entry->userlist = awoHelper::loadObjectList($sql);
			}
			

			$entry->assetlist = awoHelper::getAwoItem('1', $entry->id);


			list($entry->countrylist, $entry->statelist) = awoHelper::getCouponCountryState($entry->id);
			if (!empty($entry->params->countrystate_mode)) $entry->countrystate_mode = $entry->params->countrystate_mode;
			if (!empty($entry->statelist)) $entry->statelist_str = implode(',', array_keys($entry->statelist));

			

			if ($entry->function_type == 'giftcert' && !empty($entry->asset2_mode))
				$entry->assetlist2 = awoHelper::getAwoItem('2', $entry->id);

			if ($entry->function_type == 'shipping' && !empty($entry->asset2_mode)) 
				$entry->assetlist2 = awoHelper::getAwoItem('2', $entry->id);
				
			if ($entry->function_type == 'buy_x_get_y')
			{
				$entry->assetlist2 = awoHelper::getAwoItem('2', $entry->id);
					
				$entry->buy_xy_process_type = $entry->params->process_type;
				$entry->max_discount_qty = $entry->params->max_discount_qty;
				$entry->asset1_qty = $entry->params->asset1_qty;
				$entry->asset2_qty = $entry->params->asset2_qty;
				@$entry->product_match = $entry->params->product_match;
				@$entry->addtocart = $entry->params->addtocart;

			}
								
			$atags = array();
			$sql = 'SELECT tag FROM #__awocoupon_tag WHERE coupon_id='.$entry->id;
			$tmp = awoHelper::loadObjectList($sql);
			foreach($tmp as $t) $atags[] = $t->tag;
			$entry->tags = implode(',',$atags);
				
		
		}
//echo '<pre>'; print_r($entry);exit;
		return $entry;
	}





	public function _buildQuery($params)
	{
		$sql = 'SELECT c.id,c.coupon_code,c.num_of_uses_percustomer,c.num_of_uses_total,c.coupon_value_type,
						c.coupon_value,c.coupon_value_def,
						c.min_value,c.discount_type,c.user_type,c.function_type,c.startdate,c.expiration,c.order_id,c.published,
						0 as usercount,0 as asset1count,0 as asset2count,c.note,
						c.params
				 FROM #__awocoupon c
				 WHERE 1=1 '.$params->where.' 
				GROUP BY c.id 
				'.$params->orderbystr;

		return $sql;
	}
	public function getEntries($params)
	{
		// Lets load the files if it doesn't already exist
		
		$query = $this->_buildQuery($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query, 'id');
		$ids = array_keys($rows);
		$counters = array();
		
		if (!empty($ids))
		{
			$ids = implode(',', $ids);
			$tmp = awoHelper::loadObjectList('SELECT coupon_id,count(user_id) as cnt FROM '._DB_PREFIX_.'awocoupon_user WHERE coupon_id IN ('.$ids.') GROUP BY coupon_id');
			foreach ($tmp as $t) $counters[$t->coupon_id]['usercount'] = $t->cnt;

			$tmp = awoHelper::loadObjectList('SELECT coupon_id,count(shopper_group_id) as cnt FROM '._DB_PREFIX_.'awocoupon_usergroup WHERE coupon_id IN ('.$ids.') GROUP BY coupon_id');
			foreach ($tmp as $t) $counters[$t->coupon_id]['usergroupcount'] = $t->cnt;
			
			//$tmp = awoHelper::loadObjectList('SELECT coupon_id,asset_type,count(asset_id) as cnt FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE coupon_id IN ('.$ids.') GROUP BY coupon_id,asset_type');
			//foreach ($tmp as $t) $counters[$t->coupon_id]['asset1count'] = $t->cnt;
			$tmp = awoHelper::loadObjectList('SELECT coupon_id,asset_type,count(asset_id) as cnt FROM #__awocoupon_asset1 WHERE coupon_id IN ('.$ids.') GROUP BY coupon_id,asset_type');
			foreach ($tmp as $t)
			{
				if (!isset($counters[$t->coupon_id]['asset1count'])) $counters[$t->coupon_id]['asset1count'] = array();
				$counters[$t->coupon_id]['asset1count'][$t->asset_type] = $t->cnt;
			}


			$tmp = awoHelper::loadObjectList('SELECT coupon_id,asset_type,count(asset_id) as cnt FROM '._DB_PREFIX_.'awocoupon_asset2 WHERE coupon_id IN ('.$ids.') GROUP BY coupon_id,asset_type');
			foreach ($tmp as $t) $counters[$t->coupon_id]['asset2count'] = $t->cnt;

		}
			
		$ids = '';
		$data = array();
		foreach ($rows as $i => $row)
		{
			$row->params = json_decode($row->params);

			if ($row->published == 1) $published = '<a href="'.awohelper::admin_link().'&view=coupon&id='.$row->id.'&task=unpublish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/published.png" width="16" height="16" class="hand" border="0" /></a>';
			elseif ($row->published == -2) $published = '<img src="'.AWO_URI.'/media/img/template.png" width="16" height="16" class="hand" border="0" />';
			else $published = '<a href="'.awohelper::admin_link().'&view=coupon&id='.$row->id.'&task=publish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/unpublished.png" width="16" height="16" class="hand" border="0" /></a>';
				
				
				
			$num_of_uses = $discount_type = $min_value = '--';
			$user_column = '';
			$detail_button = '<a class="awomodal" href="javascript:coupon_detail('.$row->id.');"><span><img src="'.AWO_URI.'/media/img/coupon-details.png" /></span></a>';
			$product_column = '';
		

			$__map = array(
				'coupon'=>'',
				'product'=>'asset1_mode',
				'category'=>'asset1_mode',
				'manufacturer'=>'asset1_mode',
				'vendor'=>'asset1_mode',
				'shipping'=>'asset1_mode',
				'country'=>'countrystate_mode',
				'countrystate'=>'countrystate_mode',
			);

			
			if (!empty($counters[$row->id]['asset1count']))
			{
				foreach ($counters[$row->id]['asset1count'] as $asset_type => $count)
				{
					if ($asset_type == 'coupon')
						$product_column = '
							<div>
								<span>'.awoHelper::vars('parent_type', $row->params->process_type).' '.$count.'</span>&nbsp;
								<span>'.$this->awocoupon->l('coupons').'</span>
							</div>
						';
					else
						$product_column .= '
							<div>
								<span>'.awoHelper::vars('asset_mode', $row->params->{$__map[$asset_type]}).' '.$count.'</span>&nbsp;
								<span>'.$this->awocoupon->l(awoHelper::vars('asset_type', $asset_type)).'</span>
							</div>
						';
				}
			}
					//$product_column = '<span id="pr'.$row->id.'">'.awoHelper::vars('parent_type', $row->params->process_type).'<br />'.(@$counters[$row->id]['asset1count']).'</span>&nbsp;<span>'.$this->awocoupon->l('Coupons').'</span>';
		
				
			$function_type = awoHelper::vars('function_type', $row->function_type);
			$coupon_value_type = $row->function_type == 'parent' ? '': awoHelper::vars('coupon_value_type', $row->coupon_value_type);
			$coupon_value = !empty($row->coupon_value) ? number_format($row->coupon_value, 2): $row->coupon_value_def;
			
			if ($row->function_type == 'giftcert')
			{
				if (!empty($row->params->asset1_type))
				{
					$title = '';
					switch ($row->params->asset1_type)
					{
						case 'product' :
							$title = 'Products';
							break;
						case 'category' :
							$title = 'Categories';
							break;
						case 'manufacturer' :
							$title = 'Manufacturers';
							break;
						case 'vendor' :
							$title = 'Vendors';
							break;
					}
					//$product_column = '<div><span id="pr'.$row->params->asset1_type.$row->id.'">'.(empty($counters[$row->id]['asset1count']) ? $this->awocoupon->l( 'All' ) : awoHelper::vars('asset_mode', $row->params->asset1_mode).' '.$counters[$row->id]['asset1count']).'</span>&nbsp;<span>'.$this->awocoupon->l($title).'</span></div>';
				}
				if (!empty($row->params->asset2_type)) $product_column .= '<div><span id="prproduct'.$row->id.'">'.awoHelper::vars('asset_mode', $row->params->asset2_mode).' '.(@$counters[$row->id]['asset2count']).'</span>&nbsp;<span>'.awoHelper::vars('asset_type', $row->params->asset2_type).'</span></div>';
			}
			else
			{
				$num_of_uses = empty($row->num_of_uses) ? $this->awocoupon->l('Unlimited') : $row->num_of_uses.' '.awoHelper::vars('num_of_uses_type', $row->num_of_uses_type);

				
				if ($row->function_type == 'parent')
				{
					$coupon_value_type = $coupon_value = '--';
					//$product_column = '<span id="pr'.$row->id.'">'.awoHelper::vars('parent_type', $row->params->process_type).'<br />'.(@$counters[$row->id]['asset1count']).'</span>&nbsp;<span>'.$this->awocoupon->l('Coupons').'</span>';
				} 
				else
				{
					//$min_value = !empty($row->min_value) ? number_format($row->min_value, 2): '';
					$min_value = '';
					if (!empty($row->min_value))
						$min_value = number_format($row->min_value, 2).' '.awoHelper::vars('discount_type', !empty($row->params->min_value_type) ? $row->params->min_value_type : 'overall');

					switch ($row->user_type)
					{
						case 'user' :
							$title = 'Customers';
							break;
						case 'usergroup' :
							$title = 'Shopper Groups';
							break;
					}
					$user_column = '<div><span id="ur'.$row->id.'">'.(empty($counters[$row->id][$row->user_type.'count']) ? $this->awocoupon->l('All') : $counters[$row->id][$row->user_type.'count']).'</span>&nbsp;<span>'.$this->awocoupon->l($title).'</span></div>';

					if (!empty($row->discount_type)) $discount_type = awoHelper::vars('discount_type', $row->discount_type);
					if ($row->function_type == 'shipping')
					{
						//$product_column = '<div><span id="prshipping'.$row->id.'">'.(empty($counters[$row->id]['asset1count']) ? $this->awocoupon->l( 'All' ) : awoHelper::vars('asset_mode', $row->params->asset1_mode).' '.(@$counters[$row->id]['asset1count'])).'</span>&nbsp;<span>'.$this->awocoupon->l('Shipping').'</span></div>';
						//$product_column .= empty($counters[$row->id]['asset2count']) ? '' : '<div><span id="prproduct'.$row->id.'">'.awoHelper::vars('asset_mode', $row->params->asset2_mode).' '.$counters[$row->id]['asset2count'].'</span>&nbsp;<span>'.$this->awocoupon->l('Products').'</span></div>';
						if (!empty($counters[$row->id]['asset2count'])) $product_column .= '<div><span id="prproduct'.$row->id.'">'.awoHelper::vars('asset_mode', $row->params->asset2_mode).' '.$counters[$row->id]['asset2count'].'</span>&nbsp;<span>'.awoHelper::vars('asset_type', $row->params->asset2_type).'</span></div>';
					}
					elseif ($row->function_type == 'buy_x_get_y')
					{
						//$product_column = '';
						//$product_column .= '<div><span id="prproduct1'.$row->id.'">'.awoHelper::vars('asset_mode', $row->params->asset1_mode).' '.(@$counters[$row->id]['asset1count']).'</span>&nbsp;<span>'.awoHelper::vars('asset_type', $row->params->asset1_type).'</span></div>';
						$product_column .= '<div><span id="prproduct2'.$row->id.'">'.awoHelper::vars('asset_mode', $row->params->asset2_mode).' '.(@$counters[$row->id]['asset2count']).'</span>&nbsp;<span>'.awoHelper::vars('asset_type', $row->params->asset2_type).'</span></div>';
					}
					else
					{
						if (!empty($row->params->asset1_type))
						{
							$title = '';
							switch ($row->params->asset1_type)
							{
								case 'product' :
									$title = 'Products';
									break;
								case 'category' :
									$title = 'Categories';
									break;
								case 'manufacturer' :
									$title = 'Manufacturers';
									break;
								case 'vendor' :
									$title = 'Vendors';
									break;
							}
							//$product_column = '<div><span id="pr'.$row->params->asset1_type.$row->id.'">'.(empty($counters[$row->id]['asset1count']) ? $this->awocoupon->l( 'All' ) : awoHelper::vars('asset_mode', $row->params->asset1_mode).' '.$counters[$row->id]['asset1count']).'</span>&nbsp;<span>'.$this->awocoupon->l($title).'</span></div>';
						}
					}
				}
			} 
							
				
				
				
				
			$data[] = array(
				'id'=>$row->id,
				'coupon_code'=>$row->coupon_code,
				'function_type'=>$function_type,
				'coupon_value_type'=>$coupon_value_type,
				'coupon_value'=>$coupon_value,
				'num_of_uses'=>$num_of_uses,
				'min_value'=>$min_value,
				'discount_type'=>$discount_type,
				'startdate'=>str_replace(' ', '<br />', $row->startdate),
				'expiration'=>str_replace(' ', '<br />', $row->expiration),
				'details'=>'<table style="width:200px;"><tr><td style="border:0;" nowrap>'.$user_column.$product_column.'</td><td width="1%" style="border:0;">'.$detail_button.'</td></tr></table>',
				'published'=>$published,
				'note'=>$row->note,
			);
				
				
				
				
				
				
				
				
				
				
		}

			
		return $data;
	}
	public function getTotal($filters = array())
	{
		awoHelper::query($this->_buildQuery($filters));
		return Db::getInstance()->NumRows();
	}
	
	
	






	public function store($data)
	{
		$this->_errors = $this->storeEach($data);
		if (!empty($this->_errors))
			return false;
		
		return true;
	
	}
	public function storeEach($data, $error_check_only = false)
	{
		$errors = array();
//printrx($data);
		
		// set null fields
		$data['params'] = null;
		if (!isset($data['coupon_value']) || !is_numeric($data['coupon_value']) || $data['coupon_value'] < 0) $data['coupon_value'] = null;
		if (empty($data['coupon_value_def'])) $data['coupon_value_def'] = null;
		if (empty($data['num_of_uses_total'])) $data['num_of_uses_total'] = null;
		if (empty($data['num_of_uses_percustomer'])) $data['num_of_uses_percustomer'] = null;
		if (empty($data['min_value'])) $data['min_value'] = null;
		if (empty($data['discount_type'])) $data['discount_type'] = null;
		if (empty($data['asset2_mode'])) $data['asset2_mode'] = null;
		if (empty($data['startdate'])) $data['startdate'] = null;
		if (empty($data['expiration'])) $data['expiration'] = null;
		if (empty($data['order_id'])) $data['order_id'] = null;
		if (empty($data['template_id'])) $data['template_id'] = null;
		if (empty($data['note'])) $data['note'] = null;
		$data['product_match'] = empty($data['product_match']) ? 0 : 1;
		$data['addtocart'] = empty($data['addtocart']) ? 0 : 1;
		if (empty($data['countrystate_mode'])) $data['countrystate_mode'] = null;

				
		$row = awoHelper::dbTableRow('awocoupon', 'id', 0);
		
		
		// bind it to the table
		if (!($row = awoHelper::dbbind($row, $data)))
			$errors[] = 'Unable to bind item';
	
		// sanitise fields
		$row->id 			= (int)$row->id;

		
		// Make sure the data is valid
		$tmperr = $this->store_validation($row);
		foreach ($tmperr as $err) $errors[] = $err;

		if (empty($row->passcode)) $row->passcode = substr(md5((string)time().rand(1, 1000).$row->coupon_code), 0, 6);

				
		//error checker
		if ($row->function_type == 'parent' && empty($data['assetlist']))
			$errors[] = $this->awocoupon->l('Coupon: please make a selection');
		
		if ($row->function_type == 'coupon' && empty($data['assetlist']) && $row->discount_type == 'specific')
			$errors[] = $this->awocoupon->l('Please select at least one asset for discount type of specific');
		
		if ($data['function_type'] != 'parent' && !empty($data['assetlist']) && empty($data['asset1_mode']))
			$errors[] = $this->awocoupon->l('Please select include/exclude for the list');
		
		if ($row->function_type == 'shipping' && empty($data['assetlist']) && empty($data['assetlist2']) && $row->discount_type == 'specific') 
			$errors[] = $this->awocoupon->l('Please select at least one asset for discount type of specific');
	
		if ($data['function_type'] == 'buy_x_get_y')
		{
			if (empty($data['buy_xy_process_type']) || ($data['buy_xy_process_type'] != 'first' && $data['buy_xy_process_type'] != 'lowest' && $data['buy_xy_process_type'] != 'highest'))
				$errors[] = $this->awocoupon->l('Process type: please make a selection');
			if (!empty($data['max_discount_qty']) && !ctype_digit($data['max_discount_qty']))  $errors[] = $this->awocoupon->l('Maximum discount qty: please enter a valid value');

			if (empty($data['assetlist'])) $errors[] = $this->awocoupon->l('Buy X: please make a selection');
			if (empty($data['assetlist2'])) $errors[] = $this->awocoupon->l('Get Y: please make a selection');
					
			if (empty($data['asset1_qty']) || !ctype_digit($data['asset1_qty']))
				$errors[] = $this->awocoupon->l('Buy X -> Number: please enter a value');
			if (empty($data['asset2_qty']) || !ctype_digit($data['asset2_qty']))
				$errors[] = $this->awocoupon->l('Get Y -> Number: Please enter a value');
				
			if ($data['asset1_function_type'] != 'product' && $data['asset1_function_type'] != 'category'
			&& $data['asset1_function_type'] != 'manufacturer' && $data['asset1_function_type'] != 'vendor')
				$errors[] = $this->awocoupon->l('Buy X -> Type: please make a selection');
			if ($data['asset2_function_type'] != 'product' && $data['asset2_function_type'] != 'category'
			&& $data['asset2_function_type'] != 'manufacturer' && $data['asset2_function_type'] != 'vendor')
				$errors[] = $this->awocoupon->l('Get Y -> Type: please make a selection');
			
			if (!empty($data['asset2_mode']) && $data['asset2_mode'] != 'include' && $data['asset2_mode'] != 'exclude') $errors[] = $this->awocoupon->l('Please select include/exclude for the list');
		}
		if (!empty($data['countrystate_mode']) && $data['countrystate_mode'] != 'include' && $data['countrystate_mode'] != 'exclude') $errors[] = $this->awocoupon->l('Country/State: please select include/exclude for the list');
		
		
		
		
		
		// take a break and return if there are any errors
		if (!empty($errors) || $error_check_only) return $errors;
				
		

		//correct invalid data
		$params = array();
		if ($row->function_type == 'coupon')
		{
			if (empty($row->user_type)) $row->user_type = 'user';
			if (!empty($data['countrylist']) || !empty($data['statelist'])) $params['countrystate_mode'] = $data['countrystate_mode'];

			if (!is_null($row->coupon_value))	$row->coupon_value_def = null;
			else $row->coupon_value = null;
			if (empty($data['assetlist']))
				$row->discount_type = 'overall';
			else
			{
				$params['asset1_type'] = $data['asset1_function_type'];
				$params['asset1_mode'] = $data['asset1_mode'];
			}
			if (!empty($data['exclude_special'])) $params['exclude_special'] = 1;
			if (!empty($data['exclude_giftcert'])) $params['exclude_giftcert'] = 1;
			
			if (!empty($data['min_value']))
			{
				if (empty($data['min_value_type'])) $data['min_value_type'] = 'overall';
				$params['min_value_type'] = $data['min_value_type'];
			}

			$data['min_qty'] = (int)$data['min_qty'];
			if(!empty($data['min_qty']) && $data['min_qty']>0 && !empty($data['min_qty_type'])) {
				$params['min_qty'] = $data['min_qty'];
				$params['min_qty_type'] = $data['min_qty_type'];
			}

			
		} 
		elseif ($row->function_type == 'shipping')
		{
			if (empty($row->user_type)) $row->user_type = 'user';
			if (!empty($data['countrylist']) || !empty($data['statelist'])) $params['countrystate_mode'] = $data['countrystate_mode'];

			$row->coupon_value_def = null;
			if (!empty($data['assetlist']))
			{
				$params['asset1_mode'] = $data['asset1_mode'];
				$params['asset1_type'] = 'shipping';
			}
			if (empty($data['assetlist2'])) $row->discount_type = null;
			else
			{
				//$params['asset2_type'] = 'product';
				$params['asset2_type'] = $data['asset2_function_type'];
				$params['asset2_mode'] = $data['asset2_mode'];
			}

			
			if (!empty($data['min_value'])) 
			{
				if (empty($data['min_value_type'])) $data['min_value_type'] = 'overall';
				$params['min_value_type'] = $data['min_value_type'];
			}
			
			$data['min_qty'] = (int)$data['min_qty'];
			if(!empty($data['min_qty']) && $data['min_qty']>0 && !empty($data['min_qty_type'])) {
				$params['min_qty'] = $data['min_qty'];
				$params['min_qty_type'] = $data['min_qty_type'];
			}
		} 
		elseif ($row->function_type == 'buy_x_get_y')
		{
			if (empty($row->user_type)) $row->user_type = 'user';
			if (!empty($data['countrylist']) || !empty($data['statelist'])) $params['countrystate_mode'] = $data['countrystate_mode'];

			$params['process_type'] = $data['buy_xy_process_type'];
			$params['max_discount_qty'] = $data['max_discount_qty'];
			$params['asset1_type'] = $data['asset1_function_type'];
			$params['asset2_type'] = $data['asset2_function_type'];
			$params['asset1_qty'] = $data['asset1_qty'];
			$params['asset2_qty'] = $data['asset2_qty'];
			$params['asset1_mode'] = $data['asset1_mode'];
			$params['asset2_mode'] = $data['asset2_mode'];
			$params['product_match'] = $data['product_match'];
			$params['addtocart'] = $data['addtocart'];
			if (!empty($data['exclude_special'])) $params['exclude_special'] = 1;

			$row->coupon_value_def = null;
			$row->discount_type = null;
				
			
			if (!empty($data['min_value']))
			{
				if (empty($data['min_value_type'])) $data['min_value_type'] = 'overall';
				$params['min_value_type'] = $data['min_value_type'];
			}
			
		} 
		elseif ($row->function_type == 'parent')
		{
			if (empty($row->user_type)) $row->user_type = 'user';
			if (!empty($data['countrylist']) || !empty($data['statelist'])) $params['countrystate_mode'] = $data['countrystate_mode'];

			$params['process_type'] = $data['parent_type'];
			$row->coupon_value_type = null;
			$row->coupon_value = null;
			$row->coupon_value_def = null;
			$row->startdate = null;
			$row->expiration = null;
			$row->min_value = null;
			$row->discount_type = null;
			$row->user_type = null;
			$params['asset1_type'] = 'coupon';
			
			if (!empty($data['min_value']))
			{
				if (empty($data['min_value_type'])) $data['min_value_type'] = 'overall';
				$params['min_value_type'] = $data['min_value_type'];
			}
			
		} 
		elseif ($row->function_type == 'giftcert')
		{
			$row->coupon_value_type = 'amount';
			$row->coupon_value_def = null;
			$row->min_value = null;
			$row->discount_type = null;
			$row->user_type = null;
			if (!empty($data['exclude_giftcert'])) $params['exclude_giftcert'] = 1;
			
			if (!empty($data['assetlist']))
			{
				$params['asset1_type'] = $data['asset1_function_type'];
				$params['asset1_mode'] = $data['asset1_mode'];
			}
			if (!empty($data['assetlist2']))
			{
				$params['asset2_type'] = 'shipping';
				$params['asset2_mode'] = $data['asset2_mode'];
			}
		}
		
		if (!empty($params)) $row->params = json_encode($params);

		$row = awoHelper::dbstore('awocoupon', $row);

		/*
echo '<pre>';
print_r($errors);
print_r($data);
print_r($row);
exit;*/
		
			

		// clean out the products/users tables
		if (!empty($row->id))
		{
			awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_shop WHERE coupon_id = '.$row->id);
			
			awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_user WHERE coupon_id = '.$row->id);
			awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_usergroup WHERE coupon_id = '.$row->id);

			awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE coupon_id = '.$row->id);
			awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_asset2 WHERE coupon_id = '.$row->id);
			
			awoHelper::query('DELETE FROM #__awocoupon_tag WHERE coupon_id = '.$row->id);
		}
		
		//store products and users if chosen
		if (awoHelper::is_multistore() && !empty($data['shoplist']))
		{
			$insert_str = '';
			foreach ($data['shoplist'] as $tmp) $insert_str .= '('.$row->id.',\''.$tmp.'\'),';
			awoHelper::query('INSERT INTO '._DB_PREFIX_.'awocoupon_shop (coupon_id, id_shop) VALUES '.substr($insert_str, 0, -1));
		}

		if (!empty($data['userlist']))
		{
			$insert_str = '';
			foreach ($data['userlist'] as $tmp) $insert_str .= '('.$row->id.',\''.$tmp.'\'),';
			if ($row->user_type == 'user')
				awoHelper::query('INSERT INTO '._DB_PREFIX_.'awocoupon_user (coupon_id, user_id) VALUES '.substr($insert_str, 0, -1));
			elseif ($row->user_type == 'usergroup')
				awoHelper::query('INSERT INTO '._DB_PREFIX_.'awocoupon_usergroup (coupon_id, shopper_group_id) VALUES '.substr($insert_str, 0, -1));
		}

		if (!empty($data['countrylist']) || !empty($data['statelist']))
		{
			$insert_str = '';
			if (!empty($data['statelist']))
			{
				foreach ($data['statelist'] as $tmp) $insert_str .= '('.$row->id.',"countrystate",'.(int)$tmp.'),';
				awoHelper::query('INSERT INTO #__awocoupon_asset1 (coupon_id, asset_type, asset_id) VALUES '.substr($insert_str, 0, -1));
			}
			elseif (!empty($data['countrylist']))
			{
				foreach ($data['countrylist'] as $tmp) $insert_str .= '('.$row->id.',"country",'.(int)$tmp.'),';
				awoHelper::query('INSERT INTO #__awocoupon_asset1 (coupon_id, asset_type, asset_id) VALUES '.substr($insert_str, 0, -1));
			}
		}

		if (!empty($data['assetlist']))
		{
			$insert_str = '';
			if ($row->function_type == 'parent')
			{
				$i = 0;
				foreach ($data['assetlist'] as $tmp) $insert_str .= '('.$row->id.',"coupon",'.$tmp.','.++$i.'),';
				awoHelper::query('INSERT INTO #__awocoupon_asset1 (coupon_id, asset_type, asset_id, order_by) VALUES '.substr($insert_str, 0, -1));
			} 
			else
			{
				$asset_type = $row->function_type == 'shipping' ? 'shipping' : $data['asset1_function_type'];
				foreach ($data['assetlist'] as $tmp) $insert_str .= '('.$row->id.',"'.$asset_type.'",'.$tmp.'),';
				awoHelper::query('INSERT INTO #__awocoupon_asset1 (coupon_id, asset_type, asset_id) VALUES '.substr($insert_str, 0, -1));
			}
		}
		if (!empty($data['assetlist2']))
		{
			$insert_str = '';
			
			$asset_type_str = '';
			if ($row->function_type == 'shipping' || $row->function_type == 'buy_x_get_y')  $asset_type_str = $data['asset2_function_type'];
			elseif ($row->function_type == 'giftcert') $asset_type_str = 'shipping';
			
			if (!empty($asset_type_str))
			{
				foreach ($data['assetlist2'] as $tmp) $insert_str .= '('.$row->id.',"'.$asset_type_str.'",'.$tmp.'),';
				awoHelper::query('INSERT INTO '._DB_PREFIX_.'awocoupon_asset2 (coupon_id, asset_type, asset_id) VALUES '.substr($insert_str, 0, -1));
			}
		}
				
		if(!empty($data['tags'])) {
			$tags = explode(',',$data['tags']);
			$insert_str = '';
			foreach($tags as $tmp) $insert_str .= '('.$row->id.',\''.trim($tmp).'\'),';
			awoHelper::query('INSERT INTO #__awocoupon_tag (coupon_id, tag) VALUES '.substr($insert_str,0,-1));
		}
		
		return;
	}
	public function store_validation($row)
	{
		$err = array();
		
		if (empty($row->coupon_code)) $err[] = $this->awocoupon->l('Coupon : please enter a valid value');

		if ($row->function_type == 'giftcert') {
			if (empty($row->coupon_value) || !is_numeric($row->coupon_value)) $err[] = $this->awocoupon->l('Coupon value: please enter a valid value');		
		}
		elseif ($row->function_type == 'parent')
		{
//if (empty($row->parent_type) || ($row->parent_type!='first' && $row->parent_type!='all' && $row->parent_type!='allonly' && $row->parent_type!='lowest' && $row->parent_type!='highest'))
//	$err[] = JText::_('COM_AWOCOUPON_CP_PARENT_TYPE').': '.JText::_('COM_AWOCOUPON_ERR_MAKE_SELECTION');
		}
		else
		{
			if (empty($row->coupon_value_type) || ($row->coupon_value_type != 'percent' && $row->coupon_value_type != 'amount')) $err[] = $this->awocoupon->l('Value Type: please enter a valid value');
			if ($row->user_type != 'user' && $row->user_type != 'usergroup') $err[] = $this->awocoupon->l('User Type: please enter a valid value');

			if ($row->function_type == 'coupon')
			{
				if (!is_null($row->coupon_value) && (!is_numeric($row->coupon_value) || $row->coupon_value < 0)) $err[] = $this->awocoupon->l('Coupon value: please enter a valid value');
				if (!empty($row->coupon_value_def) && !preg_match('/^(\d+\-\d+([.]\d+)?;)+(\[[_a-z]+\=[a-z]+(\&[_a-z]+\=[a-z]+)*\])?$/', $row->coupon_value_def)) $err[] = $this->awocoupon->l('Coupon value Definition: please enter a valid value');
				if (is_null($row->coupon_value) && empty($row->coupon_value_def)) $err[] = $this->awocoupon->l('Coupon value: please enter a valid value');
				if (empty($row->discount_type) || ($row->discount_type != 'specific' && $row->discount_type != 'overall')) $err[] = $this->awocoupon->l('Discount type: please enter a valid value');
			}
			elseif ($row->function_type == 'shipping')
			{
				if (!is_numeric($row->coupon_value) || $row->coupon_value < 0) $err[] = $this->awocoupon->l('Coupon value: please enter a valid value');
				if (!empty($row->discount_type) && ($row->discount_type != 'specific' && $row->discount_type != 'overall')) $err[] = $this->awocoupon->l('Discount type: please enter a valid value');
			}
			elseif ($row->function_type == 'buy_x_get_y')
			{
				if (!is_numeric($row->coupon_value) || $row->coupon_value < 0)
					$err[] = $this->awocoupon->l('Coupon value: please enter a valid value');
						
			}
			else $err[] = $this->awocoupon->l('Function type: please enter a valid value');
				
			if (!empty($row->num_of_uses_total) && !is_numeric($row->num_of_uses_total)) $err[] = $this->awocoupon->l('Number of uses total: please enter a valid value');
			if (!empty($row->num_of_uses_percustomer) && !is_numeric($row->num_of_uses_percustomer)) $err[] = $this->awocoupon->l('Number of uses per customer: please enter a valid value');
			if (!empty($row->min_value) && !is_numeric($row->min_value)) $err[] = $this->awocoupon->l('Minumum value: please enter a valid value');
//if (!empty($row->asset1_mode) && $row->asset1_mode!='include' && $row->asset1_mode!='exclude') $err[] = JText::_('COM_AWOCOUPON_CP_FUNCTION_TYPE_MODE').': '.JText::_('COM_AWOCOUPON_ERR_ENTER_VALID_VALUE');
		}
		$is_start = true;
		if (!empty($row->startdate))
		{
			if (!preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $row->startdate))
			{
				$is_start = false;
				$err[] = $this->awocoupon->l('Start date: please enter a valid value');
			}
			else
			{
				list($dtmp, $ttmp) = explode(' ', $row->startdate);
				list($Y, $M, $D) = explode('-', $dtmp);
				list($h, $m, $s) = explode(':', $ttmp);
				if ($Y > 2100 || $M > 12 || $D > 31 || $h > 23 || $m > 59 || $s > 59)
				{
					$is_start = false;
					$err[] = $this->awocoupon->l('Start date: please enter a valid value');
				}
			}
		}
		else $is_start = false;
		$is_end = true;
		if (!empty($row->expiration))
		{
			if (!preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $row->expiration))
			{
				$is_end = true;
				$err[] = $this->awocoupon->l('Expiration: please enter a valid value');
			}
			else
			{
				list($dtmp, $ttmp) = explode(' ', $row->expiration);
				list($Y, $M, $D) = explode('-', $dtmp);
				list($h, $m, $s) = explode(':', $ttmp);
				if ($Y > 2100 || $M > 12 || $D > 31 || $h > 23 || $m > 59 || $s > 59)
				{
					$is_end = true;
					$err[] = $this->awocoupon->l('Expiration: please enter a valid value');
				}
			}
		}
		else $is_end = false;
		if ($is_start && $is_end)
		{
			list($dtmp, $ttmp) = explode(' ', $row->startdate);
			list($Y, $M, $D) = explode('-', $dtmp);
			list($h, $m, $s) = explode(':', $ttmp);
			$c1 = (int)$Y.$M.$D.'.'.$h.$m.$s;
			list($dtmp, $ttmp) = explode(' ', $row->expiration);
			list($Y, $M, $D) = explode('-', $dtmp);
			list($h, $m, $s) = explode(':', $ttmp);
			$c2 = (int)$Y.$M.$D.'.'.$h.$m.$s;
			if ($c1 > $c2) $err[] = $this->awocoupon->l('Start date/Expiration: please enter a valid value');
		}
		if (!empty($row->exclude_special) && $row->exclude_special != '1') $this->awocoupon->l('Exclude Products on Special: please enter a valid value');
		if (!empty($row->exclude_giftcert) && $row->exclude_giftcert != '1') $this->awocoupon->l('Exclude Gift certificate: please enter a valid value');
		if (!empty($row->order_id) && !ctype_digit($row->order_id)) $this->awocoupon->l('Order number: invalid');
		if (!empty($row->template_id) && !ctype_digit($row->template_id)) $err[] = $this->awocoupon->l('Template: invalid');
		if (empty($row->published) || ($row->published != '1' && $row->published != '-1' && $row->published != '-2')) $err[] = $this->awocoupon->l('Published: please enter a valid value');
		
		if (empty($row->id))
		{
		//Error: That coupon code already exists. Please try again.
			$sql = 'SELECT id FROM '._DB_PREFIX_.'awocoupon WHERE coupon_code = \''.$row->coupon_code.'\'';
			$tmp = awoHelper::loadObjectList($sql);
			if (!empty($tmp))
				$err[] = $this->awocoupon->l('That coupon code already exists. Please try again');
		}
		else
		{
			$sql = 'SELECT id FROM '._DB_PREFIX_.'awocoupon WHERE coupon_code = \''.$row->coupon_code.'\' AND id NOT IN ('.$row->id.')';
			$tmp = awoHelper::loadObjectList($sql);
			if (!empty($tmp))
				$err[] = $this->awocoupon->l('That coupon code already exists. Please try again');
		}

		return $err;
	}






	public function publish($data, $publish = 1)
	{
		@$id = (int)$data['id'];
		awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon SET published='.(int)$publish.' WHERE id='.(int)$id);
		return true;
	}
	
	public function delete($cids)
	{		
		if (empty($cids) || !is_array($cids))
		{
			$this->errors = array('Invalid Items');
			return false;
		}
		
		foreach ($cids as $k => $v) $cids[$k] = (int)$v;
		$cids = implode(',', $cids);

		awoHelper::query('DELETE FROM #__awocoupon_tag WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_shop WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_usergroup WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_user WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_asset2 WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE coupon_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon WHERE id IN ('.$cids.')');

		return true;
	}
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	


	public function storeGeneratecoupons($data)
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		
		$number = (int)$data['number'];
		$template_id = (int)$data['template'];
		if (empty($number) || empty($template_id))
		{
			$this->_errors[] = $this->awocoupon->l('Invalid setup');
			return;
		}
		
		$type = awohelper::loadResult('SELECT function_type FROM #__awocoupon WHERE id='.$template_id);
		if ($type != 'parent')
			for ($i = 0; $i < $number; $i++)
				awoAutoGenerate::generateCoupon($template_id);
		else
		{
			$sql = 'SELECT c.id
					  FROM #__awocoupon_asset1 a
					  JOIN #__awocoupon c ON c.id=a.asset_id
					 WHERE a.coupon_id='.$template_id.' AND asset_type="coupon" AND c.function_type="giftcert"';
			$giftcerts = awohelper::loadObjectList($sql);
			if (!empty($giftcerts))
			{
				foreach ($giftcerts as $gift)
					awohelper::query('UPDATE #__awocoupon SET published=-2 WHERE id='.$gift->id);
				for ($i = 0; $i < $number; $i++)
				{
					$gift_ids = array();
					foreach ($giftcerts as $gift)
					{
						$obj = awoAutoGenerate::generateCoupon($gift->id);
						if (!empty($obj->coupon_id)) $gift_ids[$gift->id] = $obj->coupon_id;
					}
					
					$obj = awoAutoGenerate::generateCoupon($template_id);
					if (!empty($obj->coupon_id))
					{
						foreach ($gift_ids as $old_id => $new_id)
							awohelper::query('UPDATE #__awocoupon_asset1 SET asset_id='.$new_id.' WHERE coupon_id='.$obj->coupon_id.' AND asset_id='.$old_id);
					}
					
				}
			}
			else
				for ($i = 0; $i < $number; $i++)
					awoAutoGenerate::generateCoupon($template_id);
		}
		return true;
	
	}

	public function duplicatecoupon($data)
	{
		@$template_id = (int)$data['id'];
		if (empty($template_id)) return false;
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		
		$rtn = awoAutoGenerate::generateCoupon($template_id);
		if (empty($rtn->coupon_id))
		{
			$this->_errors[] = $this->awocoupon->l('Could not duplicate coupon');
			return false;
		}
		return $rtn;
	
	}

}
