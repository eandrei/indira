<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class Cart extends CartCore {

	public function addDiscount($id_discount) {
		if(substr($id_discount,0,10)=='awocoupon-') {
			$id_discount = (int)substr($id_discount,10);

			$coupon_code = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT coupon_code FROM '._DB_PREFIX_.'awocoupon WHERE id='.(int)$id_discount);
			if(!empty($coupon_code)) {
				$rtn = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_cart FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE id_cart = '.(int)$this->id);
				if(empty($rtn)) 
					return Db::getInstance()->AutoExecute(_DB_PREFIX_.'awocoupon_cart', array('new_ids' => pSQL($coupon_code), 'id_cart' => (int)($this->id)), 'INSERT');
				else 
					return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'awocoupon_cart` SET new_ids="'.pSQL($coupon_code).'" WHERE `id_cart` = '.(int)($this->id).' LIMIT 1');
			}
			return;
		}
		
		return parent::addDiscount($id_discount);
	}
	
	
	
	public function deleteDiscount($id_discount) {
		if($id_discount < 0) {
			if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			AwoCouponCouponHandler::run_delete_cart_code($this->id,$id_discount*-1);
			return;
		}
		return parent::deleteDiscount($id_discount);
	}



	function checkDiscountValidity($discountObj, $discounts, $order_total, $products, $checkCartDiscount = false) {
		
		if(!empty($discountObj->is_awocoupon)) {
			require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$instance = new AwoCouponCouponHandler($this->id);
			$instance->ps_cart = $this;
			$instance->ps_cartitems = $this->getProducts();
			$instance->coupon_session['new_ids'] = $discountObj->coupon_code;
			$instance->validate_coupon_code();

			return empty($instance->_errors) ? '' : $instance->_errors;
			
		}
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		if($params->get('enable_store_coupon', 1) == 1) ; else return Tools::displayError($params->get('errNoRecord','Coupon code not found'));
		
		return parent::checkDiscountValidity($discountObj, $discounts, $order_total, $products, $checkCartDiscount);
	}


	public function getDiscounts($lite = false, $refresh = false) {
		if (!$this->id) return array();
		
		$discounts = parent::getDiscounts($lite, $refresh);
		
		if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_coupon_code($this);
		if(empty($coupon_session)) return $discounts;
		
		
		$discounts = array();
		foreach($coupon_session['processed_coupons'] as $id_discount=>$it) {
			//$id_discount = 'awocoupon-'.$id_discount;
			$id_discount = $id_discount*-1;
			$discounts[] = array(
				'id_discount' => $id_discount,
				'id_discount_type' => 1,
				'behavior_not_exhausted' => 1,
				'id_customer' => 0,
				'id_group' => 0,
				'id_currency' => 0,
				'value' => 0,
				'quantity' => 0,
				'quantity_per_user' => 0,
				'cumulable' => 0,
				'cumulable_reduction' => 0,
				'date_from' => '',
				'date_to' => '',
				'minimal' => 0,
				'include_tax' => 0,
				'active' => 0,
				'cart_display' => 0,
				'date_add' => '',
				'date_upd' => '',
				'id_cart' => $this->id,
				'description'=>'',
				
				'name' => str_replace(';',', ',$it['coupon_code_display']), # awocouponsyntax
				'value_real' => $it['product_discount']+$it['shipping_discount']+$it['giftwrap_discount'],
				'value_tax_exc' => $it['product_discount_notax']+$it['shipping_discount_notax']+$it['giftwrap_discount_notax'],
				//'obj'=> new CartRule($id_discount),
			);
		}
		
		return $discounts;
		

	}
	
	
	
	
	
	
	
	
	

	



}

