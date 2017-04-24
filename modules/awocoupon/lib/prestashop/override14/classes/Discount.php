<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class Discount extends DiscountCore {
	
	public function __construct($id = NULL, $id_lang = NULL) {
		if($id<0 || substr($id,0,10)=='awocoupon-') {
			if($id<0) $id *= -1;
			else $id = (int) substr($id,10);

			if (!empty($id)) {
				if (!isset(self::$_cache['awocoupon'][$id]))
					self::$_cache['awocoupon'][$id] = Db::getInstance()->getRow('SELECT *, id*-1 as id_discount FROM `'._DB_PREFIX_.'awocoupon` a WHERE a.`id` = '.$id);

				$result = self::$_cache['awocoupon'][$id];
				if ($result) {
					foreach ($result AS $key => $value) $this->{$key} = $value;
					$this->id = 'awocoupon-'.$id;
					$this->is_awocoupon = 1;
				}
			}
			return;
		}
		
		parent::__construct($id,$id_lang);

	}
	
	public static function getIdByName($discountName) {
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `id` FROM `'._DB_PREFIX_.'awocoupon` WHERE `coupon_code` = \''.pSQL($discountName).'\'');
		if(isset($result['id'])) { return 'awocoupon-'.$result['id'];}

		return parent::getIdByName($discountName);
	}
	
	public function getValue($nb_discounts = 0, $order_total_products = 0, $shipping_fees = 0, $idCart = false, $useTax = true) {
	
		if(!empty($this->is_awocoupon)) {
			$cart = new Cart((int)($idCart));
			if (!Validate::isLoadedObject($cart)) return 0;
			
			$coupon_data = awoHelper::loadResult('SELECT coupon_data FROM '._DB_PREFIX_.'awocoupon_cart WHERE id_cart='.(int)$cart->id);
			if(empty($coupon_data) ) return 0;

			$coupon_data = unserialize($coupon_data);
			if(empty($coupon_data['processed_coupons'])) return 0;
			
			{ # one coupon code per line
				$id = $this->id<0 ? $this->id*-1 : (int) substr($this->id,10);
				if(empty($coupon_data['processed_coupons'][$id])) return 0;
				$data = $coupon_data['processed_coupons'][$id];
				return ($useTax 
							? $data['product_discount']+$data['shipping_discount']+$data['giftwrap_discount']
							: $data['product_discount_notax']+$data['shipping_discount_notax']+$data['giftwrap_discount_notax']
						) ;
			}
			

			return ($useTax 
						? $coupon_data['product_discount']+$coupon_data['shipping_discount']+$data['giftwrap_discount']
						: $coupon_data['product_discount_notax']+$coupon_data['shipping_discount_notax']+$data['giftwrap_discount_notax']
					) ;
				
		}
		
		return parent::getValue($nb_discounts, $order_total_products, $shipping_fees, $idCart, $useTax);
		
		
    }

	
	
	public static function getVouchersToCartDisplay($id_lang, $id_customer) {
	
		$available_cart_rules = parent::getVouchersToCartDisplay($id_lang,$id_customer);
		
		if(!class_exists('AwoCouponCouponsModelFront')) require _PS_MODULE_DIR_.'awocoupon/classes/front/coupons.php';
		$my_coupons = AwoCouponCouponsModelFront::getData($id_customer);
		
		if(empty($my_coupons)) return $available_cart_rules;
		
		
		//reformat available coupons
		$my_coupons_reformatted = array();
		foreach($my_coupons as $coupon) {
			$description = $coupon->coupon_code;
			if(!empty($coupon->description)) $description = $coupon->description;
			else {
				preg_match('/{customer_description:(.*)?}/i', $coupon->note, $match);
				if (!empty($match[1])) $description = $match[1];
			}

			$my_coupons_reformatted[] = array (
					'id_discount' => 'awocoupon-'.$coupon->id,
					'name' => $coupon->coupon_code,
					'description' => $description,
			);

		}
		$available_cart_rules = array_merge($my_coupons_reformatted,$available_cart_rules);
		
		return $available_cart_rules;		
	}


	public function update($autodate = true, $nullValues = false, $categories = false) {
		if(!empty($this->is_awocoupon)) return true;

		return parent::update($autodate, $nullValues, $categories);
	}
	
	
}
