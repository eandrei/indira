<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class Order extends OrderCore {

	public function addDiscount($id_discount, $name, $value) {
		if($id_discount<0 || substr($id_discount,0,10)=='awocoupon-') {
			if($id_discount<0) $id_discount *= -1;
			else $id_discount = (int) substr($id_discount,10);

			$coupon_data = awoHelper::loadResult('SELECT coupon_data FROM '._DB_PREFIX_.'awocoupon_cart WHERE id_cart='.(int)$this->id_cart);
			if(empty($coupon_data) ) return;

			$coupon_data = unserialize($coupon_data);
			if(empty($coupon_data['processed_coupons'])) return;
			
			$name = $coupon_data['coupon_id']=='--multiple--' ? 'Multiple Coupons': $coupon_data['coupon_code'];
			$value = $coupon_data['product_discount'] + $coupon_data['shipping_discount'];
		

			require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$rtn = AwoCouponCouponHandler::remove_coupon_code($this->id_cart,$this->id);

			if(!is_null($rtn)) {
				Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_discount', array('id_order'=>(int)($this->id),'id_discount'=>0,'name'=> pSQL($name),'value'=>(float)($value)),'INSERT');
			}
			else {
				// remove from cart
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE `id_cart` = '.(int)($this->id_cart).' LIMIT 1');
				
				
				// recalculate order
				$value = round($coupon_data['product_discount']+$coupon_data['shipping_discount']+$coupon_data['giftwrap_discount'],2);
				$value_tax_excl = round($coupon_data['product_discount_notax']+$coupon_data['shipping_discount_notax']+$coupon_data['giftwrap_discount_notax'],2);


				// Update amounts of order
				$this->total_discounts -= $value;

				$this->total_paid += $value;
				if(!empty($this->total_paid_real)) $this->total_paid_real += $value;
				

				// Delete Order Cart Rule and update Order
				$this->update();
			}
			return;
			
		}
		
		return parent::addDiscount($id_discount, $name, $value);
	}
}

