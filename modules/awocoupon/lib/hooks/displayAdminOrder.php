<?php

/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class AwoCouponHookDisplayAdminOrder {

	public static function execute($params) {
		
		$awocoupon_add = Tools::getValue('awocoupon_add');
		$errors = array();
		if ($awocoupon_add == 1) 
		{
		// process add
			if (!class_exists('AdminAwoCouponController'))  require _PS_MODULE_DIR_.'awocoupon/controllers/admin/AdminAwoCouponController.php';
			$ct = new AdminAwoCouponController();
			$ct->_taskcpaneldefaultorderaddvoucher();
			if (empty($ct->_errors)) 
			{
				Header('Location: '.awohelper::getPSAdminLink('AdminOrders', 'vieworder&id_order='.(int)Tools::getValue('id_order')));
				exit;
			}
			$errors = $ct->_errors;
		}

		if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$id_order = (int)Tools::getValue('id_order');
		$sql = 'SELECT uu.id,c.coupon_code,c.coupon_value_type,c.coupon_value,
					 c.min_value,c.discount_type,c.function_type,c.startdate,c.expiration,c.published,
					 uu.coupon_id,uu.coupon_entered_id,c2.coupon_code as coupon_entered_code,
					 uu.id as use_id,uv.firstname,uv.lastname,uu.user_id,uu.shipping_discount,uu.coupon_discount,
					 (uu.coupon_discount+uu.shipping_discount) AS discount,uu.productids,uu.timestamp,uu.user_email,
					 ov.id_order AS order_id,ov.date_add AS cdate,ov.id_currency
				 FROM #__awocoupon_history uu
				 JOIN #__awocoupon c ON c.id=uu.coupon_id
				 JOIN #__orders ov ON ov.id_order=uu.order_id
				 LEFT JOIN #__awocoupon c2 ON c2.id=uu.coupon_entered_id
				 LEFT JOIN #__customer uv ON uv.id_customer=uu.user_id
				WHERE 1=1 AND ov.id_order='.$id_order; //exit($sql);

		$rows = awoHelper::loadObjectList($sql);
		$data = array();
		foreach ($rows as $i => $row) 
		{
			$coupon_code = $row->coupon_entered_code.($row->coupon_id != $row->coupon_entered_id ? ' ('.$row->coupon_code.')' : '');
			
			$data[] = (object)array(
				'id'=>$row->id,
				'coupon_code'=>$coupon_code,
				'user_id'=>$row->user_id,
				'user_email'=>$row->user_email,
				'lastname'=>$row->lastname,
				'firstname'=>$row->firstname,
				'discount'=>number_format($row->discount, 2),
				'product_discount'=>number_format($row->coupon_discount, 2),
				'shipping_discount'=>number_format($row->shipping_discount, 2),
				//'product_discount'=>Tools::displayPrice($row->coupon_discount,$row->id_currency),
				//'shipping_discount'=>Tools::displayPrice($row->shipping_discount,$row->id_currency),
				'order_id'=>$row->order_id,
				'cdate'=>!empty($row->cdate) ? date('Y-m-d', strtotime($row->cdate)) : '',
			);
				
		}
		
		//$form_url = awohelper::admin_link().'&id_order='.$id_order.'&vieworder&token='.Tools::getValue('token');
		$form_url = 'index.php?controller=AdminOrders&id_order='.$id_order.'&vieworder&token='.Tools::getValue('token');
		$smarty_data = array(
			'form_url'=>$form_url,
			'rows'=>$data,
			'total_count'=>count($data),
			'id_order'=>$id_order,
			'errors'=>$errors,
		);
		
		Context::getContext()->smarty->assign($smarty_data);
		
		$folder = '';
		if (_PS_VERSION_ >= '1.6')
			$folder = '16';
		else
			$folder = '15';
			
		return true;
	}
	
}
