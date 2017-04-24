<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelHistory
{
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
	}

	public function getEntryHist()
	{
		$id = Tools::getValue('id', 0);
		$entry = awoHelper::dbTableRow('awocoupon_history', 'id', $id);

		return $entry;
	}
	public function getEntryOrder()
	{
		global $cookie;

		$id = Tools::getValue('id', 0);
		$entry = awoHelper::dbTableRow('awocoupon_giftcert_order', 'order_id', $id);

		return $entry;
	}

	


	public function storehist($data)
	{
		$this->_errors = $this->storehistEach($data);
		if (!empty($this->_errors))
			return false;

		return true;
	
	}
	public function storehistEach($data)
	{
		$errors = array();
		
		// set null fields
		$data['coupon_entered_id'] = null;
		$data['productids'] = null;
		if (empty($data['coupon_discount'])) $data['coupon_discount'] = 0;
		if (empty($data['shipping_discount'])) $data['shipping_discount'] = 0;
		if (empty($data['order_id'])) $data['order_id'] = null;
		//$data['user_email'] = $data['email'];

		$data['user_id'] = (int)awoHelper::loadResult('SELECT id_customer FROM '._DB_PREFIX_.'customer WHERE email="'.$data['user_email'].'"');
		
		if (!empty($data['order_id']))
		{
			$tmp = awohelper::loadResult('SELECT id_order FROM #__orders WHERE id_order='.(int)$data['order_id']);
			if (empty($tmp)) $errors[] = $this->awocoupon->l('Order does not exist');
		}

		$row = awoHelper::dbTableRow('awocoupon_history', 'id', 0);

		// bind it to the table
		if (!($row = awoHelper::dbbind($row, $data)))
			$errors[] = 'Unable to bind item';

		// sanitise fields
		$row->id 			= (int)$row->id;
		$extra = (object)array(
					'user_id_isnull' => false,
					'product_discount_isnull' => false,
					'shipping_discount_isnull' => false,
				);
		// Make sure the data is valid
		$tmperr = $this->storehist_validation($row);
		foreach ($tmperr as $err) $errors[] = $err;

		// take a break and return if there are any errors
		if (!empty($errors)) return $errors;
		
		
		$row = awoHelper::dbstore('awocoupon_history', $row, $extra);
		
		return;
	}
	public function storehist_validation($row)
	{
		$err = array();

		if (empty($row->coupon_id) || !ctype_digit($row->coupon_id)) $err[] = $this->awocoupon->l('Coupon code: please make a selection');
		if (empty($row->user_email) || !Validate::isEmail($row->user_email)) $err[] = $this->awocoupon->l('User email: please enter a valid email address');
		//if (empty($row->user_id) || (int) $row->user_id != $row->user_id) $err[] = $this->awocoupon->l('Customer does not exist');
		if (!empty($row->coupon_discount) && (!is_numeric($row->coupon_discount) || $row->coupon_discount < 0))  $err[] = $this->awocoupon->l('Product Discount: please enter a valid value');
		if (!empty($row->shipping_discount) && (!is_numeric($row->shipping_discount) || $row->shipping_discount < 0))  $err[] = $this->awocoupon->l('Shipping Discount: please enter a valid value');

		return $err;
	}





	public function storeorder($data)
	{
		$this->_errors = $this->storeorderEach($data);
		if (!empty($this->_errors))
			return false;

		return true;
	
	}
	public function storeorderEach($data)
	{
		$errors = array();
		
		// set null fields
		$data['coupon_entered_id'] = null;
		$data['productids'] = null;
		if (empty($data['coupon_discount'])) $data['coupon_discount'] = 0;
		if (empty($data['shipping_discount'])) $data['shipping_discount'] = 0;
		if (empty($data['order_id'])) $data['order_id'] = null;
		//$data['user_email'] = $data['email'];


		if (empty($data['coupon_template_id']) || !ctype_digit($data['coupon_template_id'])) $errors[] = $this->awocoupon->l('Coupon Template: please make a selection');
		if (empty($data['coupon_code'])) $errors[] = $this->awocoupon->l('Coupon code: please enter a value');
		if (empty($data['order_id']) || !ctype_digit($data['order_id'])) $errors[] = $this->awocoupon->l('Order ID: please enter a valid value');
		
		// take a break and return if there are any errors
		if (!empty($errors)) return $errors;
		
		$order = awohelper::loadObject('SELECT * FROM #__orders WHERE id_order='.(int)$data['order_id']);
		if (empty($order)) $errors[] = $this->awocoupon->l('Order does not exist');
		
		$tmp = awohelper::loadResult('SELECT id FROM #__awocoupon WHERE coupon_code="'.awohelper::escape($data['coupon_code']).'"');
		if (!empty($tmp)) $errors[] = $this->awocoupon->l('Coupon code already exists');
		
		
		// take a break and return if there are any errors
		if (!empty($errors)) return $errors;
		
		// generate coupon code
		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		$obj = awoAutoGenerate::generateCoupon((int)$data['coupon_template_id'], $data['coupon_code']);
		if (empty($obj->coupon_id)) $errors[] = $this->awocoupon->l('Could not save voucher');
		if (!empty($errors)) return $errors;

		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		if ((int)$params->get('giftcert_coupon_activate', 0) == 1)
			awoHelper::query('UPDATE #__awocoupon SET published=-1 WHERE id='.(int)$obj->coupon_id);
		
		awohelper::query('UPDATE #__awocoupon SET order_id='.(int)$order->id_order.' WHERE id='.(int)$obj->coupon_id);

		
		// save data
		$codes = array();
		$g_order = awohelper::loadObject('SELECT * FROM #__awocoupon_giftcert_order WHERE order_id='.(int)$order->id_order);
		if (!empty($g_order) && !empty($g_order->codes))
			parse_str($g_order->codes, $codes);
		
		$codes_array = array();
		$codes_array['c'] = $obj->coupon_code;
		//$codes_array['i'] = $row['order_item_id'];
		//$codes_array['p'] = $row['product_id'];
		//$codes_array['f'] = 'filename';
					
		$codes[] = $codes_array;
		$codes = awohelper::escape(urldecode(http_build_query($codes)));
		
		if (empty($g_order))
		{
			awoHelper::query('INSERT INTO #__awocoupon_giftcert_order (order_id,user_id,email_sent,codes) VALUES ('.$order->id_order.','.$order->id_customer.',1,"'.$codes.'")');
			$giftcert_order_id = Db::getInstance()->Insert_ID();
		}
		else
		{
			awohelper::query('UPDATE #__awocoupon_giftcert_order SET codes="'.$codes.'" WHERE order_id='.(int)$order->id_order);
			$giftcert_order_id = $g_order->id;
		}
		
		awohelper::query('INSERT INTO #__awocoupon_giftcert_order_code (giftcert_order_id,order_item_id,product_id,coupon_id,code)
					VALUES ('.$giftcert_order_id.',0,0,'.(int)$obj->coupon_id.',"'.awohelper::escape($obj->coupon_code).'")');
		return;
	}
	
		
	
	
	
	
	public function _buildQueryHist($params)
	{
		$sql = 'SELECT uu.id,c.coupon_code,c.coupon_value_type,c.coupon_value,
					 c.min_value,c.discount_type,c.function_type,c.startdate,c.expiration,c.published,
					 uu.coupon_id,uu.coupon_entered_id,c2.coupon_code as coupon_entered_code,
					 uu.id as use_id,uv.firstname,uv.lastname,uu.user_id,
					 (uu.coupon_discount+uu.shipping_discount) AS discount,uu.productids,uu.timestamp,uu.user_email,
					 ov.id_order AS order_id,ov.date_add AS cdate,'.awohelper::select_order_num('ov').' as order_number,'.awohelper::select_order_num('ov').' as reference
				 FROM #__awocoupon_history uu
				 JOIN #__awocoupon c ON c.id=uu.coupon_id
				 LEFT JOIN #__awocoupon c2 ON c2.id=uu.coupon_entered_id
				 LEFT JOIN #__customer uv ON uv.id_customer=uu.user_id
				 LEFT JOIN #__orders ov ON ov.id_order=uu.order_id
				 WHERE 1=1 '.$params->where.'
				HAVING 1=1 '.$params->having.'
				'.$params->orderbystr;//exit($sql);

		return $sql;
	}
	public function _buildQueryGift($params)
	{
		$sql = 'SELECT c.*,
					 uv.id_customer AS user_id,uv.firstname,uv.lastname,
					 o.id_order AS order_id,UNIX_TIMESTAMP(o.date_add) AS cdate,
					 SUM(au.coupon_discount)+SUM(au.shipping_discount) AS coupon_value_used,
					 c.coupon_value-IFNULL(SUM(au.coupon_discount),0)-IFNULL(SUM(au.shipping_discount),0) AS balance
				 FROM #__awocoupon c
				 LEFT JOIN #__orders o ON o.id_order=c.order_id
				 LEFT JOIN #__address uv ON uv.id_address=o.id_address_invoice
				 LEFT JOIN #__awocoupon_history au ON au.coupon_id=c.id
				 WHERE c.function_type="giftcert" '.$params->where.'
				GROUP BY c.id
				HAVING 1=1 '.$params->having.'
				'.$params->orderbystr;
//exit($sql);
		return $sql;
	}
	public function _buildQueryOrder($params)
	{
		$sql = 'SELECT go.order_id,go.codes,o.id_order as order_number,"" AS coupon_code
				 FROM #__awocoupon_giftcert_order go
				 LEFT JOIN #__orders o ON o.id_order=go.order_id
				 WHERE 1=1 '.$params->where.'
				HAVING 1=1 '.$params->having.'
				'.$params->orderbystr;
		return $sql;

	}
	
	public function getEntriesHist($params)
	{
		// Lets load the files if it doesn't already exist
		
		
		$query = $this->_buildQueryHist($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);
		
		
			
		$data = array();
		foreach ($rows as $i => $row)
		{
			$coupon_code = $row->coupon_entered_code.($row->coupon_id != $row->coupon_entered_id ? ' ('.$row->coupon_code.')' : '');
			$order_link = !empty($row->order_id) ? '<a href="'.awohelper::getPSAdminLink('AdminOrders', 'vieworder&id_order='.$row->order_id).'">'.$row->reference.'</a>' : '';
			
			$data[] = array(
				'id'=>$row->id,
				'coupon_code'=>$coupon_code,
				'user_id'=>$row->user_id,
				'user_email'=>$row->user_email,
				'lastname'=>$row->lastname,
				'firstname'=>$row->firstname,
				'discount'=>number_format($row->discount, 2),
				'order_number'=>$order_link,
				'cdate'=>!empty($row->cdate) ? date('Y-m-d', strtotime($row->cdate)) : '',
			);
				
		}
			
		return $data;
	}
	public function getEntriesGift($params)
	{
		// Lets load the files if it doesn't already exist
		

		$query = $this->_buildQueryGift($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);
		
			
		$data = array();
		foreach ($rows as $i => $row)
		{
			$data[] = array(
				'id'=>$row->id,
				'coupon_code'=>$row->coupon_code,
				'coupon_value'=>number_format($row->coupon_value, 2),
				'coupon_value_used'=>number_format($row->coupon_value_used, 2),
				'balance'=>number_format($row->balance, 2),
				'expiration'=>$row->expiration,
			);
				
		}
			
		return $data;
	}
	public function getEntriesOrder($params)
	{
		// Lets load the files if it doesn't already exist
		
		$query = $this->_buildQueryOrder($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);
		

		$send = '';
		
		$data = array();
		foreach ($rows as $i => $row)
		{
			@parse_str($row->codes, $codes);
			$code_list = array();
			if (!empty($codes[0]['c']))
				foreach ($codes as $code) $code_list[] = $code['c'];
			
			$codestr = implode(', ', $code_list);
			$button = '<input type="button" value="'.$this->awocoupon->l('resend').'" onclick="customFormSubmit(\''.$_SERVER['REQUEST_URI'].'\',[{name:\'task\',value:\'giftcertresend\'},{name:\'order_id\',value:\''.$row->order_id.'\'}])" />';

			$data[] = array(
				'id'=>$row->order_id,
				'order_id'=>$row->order_number,
				'codes'=>$codestr,
				'button'=>$button,
			);
				
		}
			
		return $data;
	}

	public function getTotalHist($filters = array())
	{
		awoHelper::query($this->_buildQueryHist($filters));
		return Db::getInstance()->NumRows();
	}
	public function getTotalGift($filters = array()) 
	{
		awoHelper::query($this->_buildQueryGift($filters));
		return Db::getInstance()->NumRows();
	}
	public function getTotalOrder($filters = array())
	{
		awoHelper::query($this->_buildQueryOrder($filters));
		return Db::getInstance()->NumRows();
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

		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_history WHERE id IN ('.$cids.')');

		return true;
	}
	
	
	
	
	
	
	
	
	
	
	public function getCouponList()
	{
		$sql = 'SELECT id,coupon_code,id as dd_id,coupon_code as dd_name 
				  FROM '._DB_PREFIX_.'awocoupon 
				 WHERE published=1 AND function_type!="parent"
				 ORDER BY coupon_code,id';
		return awoHelper::loadObjectList($sql, 'id');
	}
	public function getTemplateList()
	{
		return awoHelper::loadObjectList('SELECT id,coupon_code FROM '._DB_PREFIX_.'awocoupon WHERE published=-2 ORDER BY coupon_code,id', 'id');
	}

	
	
	public function resend_giftcert($order_id)
	{
		$sql = 'SELECT gc.coupon_id,gc.order_item_id,go.user_id,gc.product_id,u.email,c.coupon_code,c.coupon_value,c.coupon_value_type,c.coupon_value_type,c.expiration,i.product_name,
					ap.profile_id,u.firstname AS first_name,u.lastname AS last_name,o.id_cart,ap.recipient_email_id,ap.recipient_name_id,ap.recipient_mesg_id,i.product_quantity,i.product_attribute_id
				  FROM #__awocoupon_giftcert_order_code gc 
				  JOIN #__awocoupon_giftcert_order go ON go.id=gc.giftcert_order_id
				  JOIN #__orders o ON o.id_order=go.order_id
				  JOIN #__awocoupon c ON c.id=gc.coupon_id
				  LEFT JOIN #__awocoupon_giftcert_product ap ON ap.product_id=gc.product_id
				  LEFT JOIN #__order_detail i ON i.id_order_detail=gc.order_item_id
				  LEFT JOIN #__address uv ON uv.id_address=o.id_address_invoice
				  LEFT JOIN #__customer u ON u.id_customer=o.id_customer
				 WHERE go.order_id='.(int)$order_id.'
				 GROUP BY gc.coupon_id
				 ';
		$rows = awohelper::loadObjectList($sql);
		if (empty($rows))
		{
			$this->_errors[] = $this->awocoupon->l('Order not found');
			return false;
		}
		
		$rtn = current($rows);
		$coupons = array();
		foreach ($rows as $k => $row)
		{
			$price = '';
			if (!empty($row->coupon_value))
				$price = $row->coupon_value_type == 'amount' 
								? $row->coupon_value
								: round($row->coupon_value).'%';

			$coupons[] = array(
				'id'=>$row->coupon_id,
				'order_item_id'=>$row->order_item_id,
				'user_id'=>$row->user_id,
				'product_id'=>$row->product_id,
				'product_name'=>$row->product_name,
				'email'=>$row->email,
				'code'=>$row->coupon_code,
				'price'=>$price,
				'expiration'=>$row->expiration,
				'expirationraw'=>!empty($row->expiration) ? strtotime($row->expiration) : 0,
				'profile'=>'',
				'file'=>'',						
			);
		
		}
		
		$rtn->coupons = $coupons;
		
		if (!class_exists('AwoCouponGiftcertHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/giftcerthandler.php';
		$is_success = AwoCouponGiftcertHandler::process_resend($order_id, array($rtn));
		if (!$is_success)
		{
			$this->_errors[] = $this->awocoupon->l('Error sending voucher');
			return false;
		}
		
		return true;
			
		
	}	
	
	
	
	


}

