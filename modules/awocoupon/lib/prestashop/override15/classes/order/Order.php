<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class Order extends OrderCore {

	public function addCartRule($id_cart_rule, $name, $values, $id_order_invoice = 0, $free_shipping = null) {
		//if(substr($id_cart_rule,0,10)=='awocoupon-') {
		//	$id_cart_rule = (int) substr($id_cart_rule,10);
		if($id_cart_rule<0 || substr($id_cart_rule,0,10)=='awocoupon-') {
			if($id_cart_rule<0) $id_cart_rule *= -1;
			else $id_cart_rule = (int) substr($id_cart_rule,10);

			$coupon_data = awoHelper::loadResult('SELECT coupon_data FROM '._DB_PREFIX_.'awocoupon_cart WHERE id_cart='.(int)$this->id_cart);
			if(empty($coupon_data) ) return;

			$coupon_data = unserialize($coupon_data);
			if(empty($coupon_data['processed_coupons'])) return;
			
			$name = $coupon_data['coupon_id']=='--multiple--' ? 'Multiple Coupons': $coupon_data['coupon_code'];
			$value = $coupon_data['product_discount'] + $coupon_data['shipping_discount'];
		

			require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$rtn = AwoCouponCouponHandler::remove_coupon_code($this->id_cart,$this->id);

			if(!is_null($rtn)) {
				$order_cart_rule = new OrderCartRule();
				$order_cart_rule->id_order = $this->id;
				$order_cart_rule->id_cart_rule = 0;
				$order_cart_rule->id_order_invoice = $id_order_invoice;
				$order_cart_rule->name = $name;
				$order_cart_rule->value = $coupon_data['product_discount']+$coupon_data['shipping_discount']+$coupon_data['giftwrap_discount'];
				$order_cart_rule->value_tax_excl = $coupon_data['product_discount_notax']+$coupon_data['shipping_discount_notax']+$coupon_data['giftwrap_discount_notax'];
				$order_cart_rule->add();
			}
			else {
				// remove from cart
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE `id_cart` = '.(int)($this->id_cart).' LIMIT 1');

				// recalculate order
				$value = round($coupon_data['product_discount']+$coupon_data['shipping_discount']+$coupon_data['giftwrap_discount'],2);
				$value_tax_excl = round($coupon_data['product_discount_notax']+$coupon_data['shipping_discount_notax']+$coupon_data['giftwrap_discount_notax'],2);
				
				$order_invoice = new OrderInvoice($id_order_invoice);
				if (Validate::isLoadedObject($order_invoice)) {

					// Update amounts of Order Invoice
					$order_invoice->total_discount_tax_excl -= $value_tax_excl;
					$order_invoice->total_discount_tax_incl -= $value;

					$order_invoice->total_paid_tax_excl += $value_tax_excl;
					$order_invoice->total_paid_tax_incl += $value;

					// Update Order Invoice
					$order_invoice->update();
				}

				// Update amounts of order
				$this->total_discounts -= $value;
				$this->total_discounts_tax_incl -= $value;
				$this->total_discounts_tax_excl -= $value_tax_excl;

				$this->total_paid += $value;
				$this->total_paid_tax_incl += $value;
				$this->total_paid_tax_excl += $value_tax_excl;
				

				// Delete Order Cart Rule and update Order
				$this->update();
			}

			return;
			
		}
		
		return parent::addCartRule($id_cart_rule, $name, $values, $id_order_invoice, $free_shipping);
	}
}

