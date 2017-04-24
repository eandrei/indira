<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class Cart extends CartCore {

	public function addCartRule($id_discount) {
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
		
		return parent::addCartRule($id_discount);
	}
	
	
	public function removeCartRule($id_discount) {
		if(substr($id_discount,0,10)=='awocoupon-') {
			if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			AwoCouponCouponHandler::run_delete_cart_code($this->id,$id_discount);
			return;
		}
		return parent::removeCartRule($id_discount);
	}

	

	public function getCartRules($filter = CartRule::FILTER_ACTION_ALL) {
		if (!$this->id) return array();
		
		$discounts = parent::getCartRules($filter);
		
		$is_admin = (is_object(Context::getContext()->controller) && Context::getContext()->controller->controller_type == 'admin');
		if($is_admin) {
			if((int)Tools::getValue('ajax')==1 && Tools::getValue('action')=='addVoucher' && Tools::getValue('controller')=='admincarts'); // ajax add coupon to backend adding order
			elseif((int)Tools::getValue('submitAddorder')==1 && Tools::getValue('controller')=='AdminOrders'); // submit backend adding order
			else return $discounts;
		}
		
		if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_coupon_code($this);
		if(empty($coupon_session)) return $discounts;

		
		foreach($coupon_session['processed_coupons'] as $id_discount=>$it) {
			//$id_discount = 'awocoupon-'.$it['coupon_entered_id'];
			$id_discount = 'awocoupon-'.$id_discount;
			$arrdiscount = array(
				'id_cart' => $this->id,
				'id_cart_rule' => $id_discount,
				'id_discount' => $id_discount,
				'id_customer' => 0,
				'date_from' => '',
				'date_to' => '',
				'description' => '',
				'quantity' => 0,
				'quantity_per_user' => 0,
				'priority' => 0,
				'partial_use' => 0,
				'code' => 0,
				'minimum_amount' => '',
				'minimum_amount_tax' => '',
				'minimum_amount_currency' => '',
				'minimum_amount_shipping' => '',
				'country_restriction' => '',
				'carrier_restriction' => '',
				'group_restriction' => '',
				'cart_rule_restriction' => '',
				'product_restriction' => '',
				'shop_restriction' => '',
				'free_shipping' => '',
				'reduction_percent' => '',
				'reduction_amount' => '',
				'reduction_tax' => '',
				'reduction_currency' => '',
				'reduction_product' => '',
				'gift_product' => '',
				'gift_product_attribute' => '',
				'active' => '',
				'date_add' => '',
				'date_upd' => '',
				'id_lang' => '',
				//'name' => str_replace(';',', ',$it['coupon_code']),
				'name' => str_replace(';',', ',$it['coupon_code_display']), # awocouponsyntax
				'value_real' => $it['product_discount']+$it['shipping_discount']+$it['giftwrap_discount'],
				'value_tax_exc' => $it['product_discount_notax']+$it['shipping_discount_notax']+$it['giftwrap_discount_notax'],
				'obj'=> new CartRule($id_discount),
			);
			$arrdiscount['reduction_amount'] = $arrdiscount['obj']->reduction_amount = 1;
			$arrdiscount['obj']->id = ((int)substr($id_discount,10)) * -1; // need for prestashop 1.6.1.2 since there is a cast int when calling the rule in PaymentModule
			$arrdiscount['obj']->partial_use = 0;
			$discounts[] = $arrdiscount;
		}
			
			

		
		return $discounts;
		

	}

	public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true) {
		$amount = parent::getOrderTotal($with_taxes, $type, $products, $id_carrier, $use_cache);
		$array_type = array(
			Cart::ONLY_DISCOUNTS,
			Cart::BOTH,
			Cart::BOTH_WITHOUT_SHIPPING,
		);
		if(!in_array($type,$array_type)) return $amount;
		
		$order_id = Order::getOrderByCartId($this->id);
		if(empty($order_id)) {
		// still in the cart
			if(version_compare(_PS_VERSION_,'1.6.1','<')) return $amount;
			
			$array_type = array(
				Cart::ONLY_DISCOUNTS,
				Cart::BOTH,
			);
			if(!in_array($type,$array_type)) return $amount;
			
			// get shipping discount from AwoCoupon
			if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$coupon_session = AwoCouponCouponHandler::process_coupon_code($this);
			if(empty($coupon_session)) return $amount;
			$true_discount = $this->getDiscountAmount($with_taxes, $products, $id_carrier, $use_cache);
			
			if($type==Cart::BOTH) {
				$fake_discount = parent::getOrderTotal($with_taxes, Cart::ONLY_DISCOUNTS, $products, $id_carrier, $use_cache);
				return max(0, ($amount + $fake_discount - $true_discount));
			}
			elseif($type==Cart::ONLY_DISCOUNTS) return $true_discount;
			
			return $amount;
		}
		
		$details = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT details FROM '._DB_PREFIX_.'awocoupon_history WHERE order_id='.(int)$order_id);
		if(empty($details)) return $amount;
		
		$details = json_decode($details);
		@$awocoupon_discount = $details->product_discount + $details->shipping_discount;
		if(empty($awocoupon_discount)) return $amount;
		
		if($type==Cart::ONLY_DISCOUNTS) return ($amount+$awocoupon_discount);
		elseif(in_array($type,array(Cart::BOTH,Cart::BOTH_WITHOUT_SHIPPING))) {
			return ($amount-$awocoupon_discount);
		}
		
		return $amount;
		
	}
	
	private function getDiscountAmount($with_taxes = true, $products = null, $id_carrier = null, $use_cache = true) {
		$param_product = true;
		if (is_null($products)) {
			$products = $this->getProducts();
			$param_product = false;
		}
        $virtual_context = Context::getContext()->cloneContext();
        $virtual_context->cart = $this;
		$configuration        = Adapter_ServiceLocator::get('Core_Business_ConfigurationInterface');
		$compute_precision = $configuration->get('_PS_PRICE_COMPUTE_PRECISION_');

        $order_total_discount = 0;
		$cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
		$id_address_delivery = 0;
		if (isset($products[0])) $id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
		$package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);

		// Then, calculate the contextual value for each one
		$flag = false;
		foreach ($cart_rules as $cart_rule) {
			// If the cart rule offers free shipping, add the shipping cost
			if ($cart_rule['obj']->free_shipping && !$flag) {
				$order_shipping_discount = (float)Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), $compute_precision);
				$flag = true;
			}

			// If the cart rule is a free gift, then add the free gift value only if the gift is in this package
			if ((int)$cart_rule['obj']->gift_product) {
				$in_order = false;
				if (is_null($products)) {
					$in_order = true;
				} else {
					foreach ($products as $product) {
						if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute']) {
							$in_order = true;
						}
					}
				}

				if ($in_order) {
					$order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
				}
			}

			// If the cart rule offers a reduction, the amount is prorated (with the products in the package)
			if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0) {
				$order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), $compute_precision);
			}
		}
		//$order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float)$order_total_products) + (float)$order_shipping_discount;
		return $order_total_discount;
	}
	
	public function getSummaryDetails($id_lang = null, $refresh = false) {
		$summary = parent::getSummaryDetails($id_lang, $refresh);
		
		if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_coupon_code($this);
		if(empty($coupon_session)) return $summary;

		$total_tax_toadd_s = $total_tax_toadd_p = $total_tax_toadd_g = $total_amount_toadd_s = $total_amount_toadd_p = 0;
		foreach($coupon_session['processed_coupons'] as $i=>$r) {
			if($r['is_discount_before_tax'] == 0) {
				$total_tax_toadd_p += $r['product_discount']-$r['product_discount_notax'];
				$total_tax_toadd_s += $r['shipping_discount']-$r['shipping_discount_notax'];
				$total_tax_toadd_g += $r['giftwrap_discount']-$r['giftwrap_discount_notax'];
				
				foreach($summary['discounts'] as $skey=>$discount) {
					if($discount['id_cart_rule']!='awocoupon-'.$i) continue;
					$summary['discounts'][$skey]['value_tax_exc'] += 
						($r['product_discount']-$r['product_discount_notax'])
						+ ($r['shipping_discount']-$r['shipping_discount_notax'])
						+ ($r['giftwrap_discount']-$r['giftwrap_discount_notax'])
					;
				}
				
			}
		}
		
		$test = $total_tax_toadd_p + $total_tax_toadd_s;
		if(empty($test)) return $summary;
		
		$summary['total_tax'] += $total_tax_toadd_p + $total_tax_toadd_s + $total_tax_toadd_g;
		$summary['total_discounts_tax_exc'] += $total_tax_toadd_p + $total_tax_toadd_s + $total_tax_toadd_g;
		$summary['total_price_without_tax'] -= $total_tax_toadd_p + $total_tax_toadd_s + $total_tax_toadd_g;
		if($summary['total_price_without_tax']<0) $summary['total_price_without_tax'] = 0;
		
//printrx($summary);
		return $summary;
		
	}

}

