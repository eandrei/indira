<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class CartRule extends CartRuleCore {

	public function __construct($id = NULL, $id_lang = NULL) {
		if($id<0 || substr($id,0,10)=='awocoupon-') {
			if($id<0) $id *= -1;
			else $id = (int) substr($id,10);

			if (!empty($id)) {
			
				$cache_id = 'objectmodel_awocoupon_'.(int)$id;
				if (!Cache::isStored($cache_id))
					Cache::store($cache_id, Db::getInstance()->getRow('SELECT *, id*-1 as id_discount FROM `'._DB_PREFIX_.'awocoupon` a WHERE a.`id` = '.$id));
			
				$result = Cache::retrieve($cache_id);
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

	public static function getIdByCode($discountName) {
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `id` FROM `'._DB_PREFIX_.'awocoupon` WHERE `coupon_code` = \''.pSQL($discountName).'\'');
		if(isset($result['id'])) { return 'awocoupon-'.$result['id'];}

		return parent::getIdByCode($discountName);
	}
	
	
	//public function checkValidity(Context $context, $alreadyInCart = false, $display_error = true) { # 1.6.0 and below
	public function checkValidity(Context $context, $alreadyInCart = false, $display_error = true, $check_carrier = true) {
		if(!empty($this->is_awocoupon)) {
			require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$instance = new AwoCouponCouponHandler($context->cart->id);
			$instance->ps_cart = $context->cart;
			$instance->ps_cartitems = $context->cart->getProducts();
			$instance->coupon_session['new_ids'] = $this->coupon_code;
			$instance->validate_coupon_code();

			return empty($instance->_errors) ? '' : $instance->_errors;
			
		}
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		if($params->get('enable_store_coupon', 1) == 1) ; else return Tools::displayError($params->get('errNoRecord','Coupon code not found'));
		
		return parent::checkValidity($context, $alreadyInCart, $display_error, $check_carrier);
	}
	
	
	
	public function getContextualValue($use_tax, Context $context = null, $filter = null, $package = null, $use_cache = true) {
	
		if(!empty($this->is_awocoupon)) {
			$coupon_data = awoHelper::loadResult('SELECT coupon_data FROM '._DB_PREFIX_.'awocoupon_cart WHERE id_cart='.(int)$context->cart->id);
			if(empty($coupon_data) ) return 0;

			$coupon_data = unserialize($coupon_data);
			if(empty($coupon_data['processed_coupons'])) return 0;
			
			{ # one coupon code per line
				$id = $this->id<0 ? $this->id*-1 : (int) substr($this->id,10);
				if(empty($coupon_data['processed_coupons'][$id])) return 0;
				$data = $coupon_data['processed_coupons'][$id];
				return ($use_tax 
							? $data['product_discount']+$data['shipping_discount']+$data['giftwrap_discount']
							: $data['product_discount_notax']+$data['shipping_discount_notax']+$data['giftwrap_discount_notax']
						) ;
			}
			

			return ($use_tax 
						? $coupon_data['product_discount']+$coupon_data['shipping_discount']+$data['giftwrap_discount']
						: $coupon_data['product_discount_notax']+$coupon_data['shipping_discount_notax']+$data['giftwrap_discount_notax']
					) ;
				
		}
		
		return parent::getContextualValue($use_tax, $context, $filter, $package, $use_cache);
		
		
    }
	
	public static function getCartsRuleByCode($name, $id_lang, $extended = false) {
	
		$sql = 'SELECT id*-1 AS id_cart_rule, coupon_code AS name, coupon_code AS code, "" AS description
				  FROM '._DB_PREFIX_.'awocoupon
				  WHERE coupon_code LIKE \'%'.pSQL($name).'%\'';
		$code = Db::getInstance()->executeS($sql);
		if(!empty($code)) return $code;
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		if($params->get('enable_store_coupon', 1) != 1) return null;

		return parent::getCartsRuleByCode($name, $id_lang, $extended);
	}
	
	
	//public static function getCustomerCartRules($id_lang, $id_customer, $active = false, $includeGeneric = true, $inStock = false, Cart $cart = null) { # 1.6.0 and below
	public static function getCustomerCartRules($id_lang, $id_customer, $active = false, $includeGeneric = true, $inStock = false, Cart $cart = null, $free_shipping_only = false, $highlight_only = false) {
	
		$available_cart_rules = parent::getCustomerCartRules($id_lang,$id_customer,$active,$includeGeneric,$inStock,$cart,$free_shipping_only,$highlight_only);
		if(!$active || !$includeGeneric || !$inStock) return $available_cart_rules;
		$is_admin = (is_object(Context::getContext()->controller) && Context::getContext()->controller->controller_type == 'admin');
		if($is_admin) return $available_cart_rules;
		

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
					'id_cart_rule' => 'awocoupon-'.$coupon->id,
					'name' => $description,
					'code' => $coupon->coupon_code,
					'highlight' => 1,
					'id_customer' => $id_customer,
					'description' => '',
					'quantity' => 1,
					'quantity_per_user' => 1,
					'priority' => 1,
					'partial_use' => 0,
					'active' => 1,
					'quantity_for_user' => 1,
			);

		}
		$available_cart_rules = array_merge($my_coupons_reformatted,$available_cart_rules);
		
		return $available_cart_rules;		

	}


	public function update($null_values = false) {
		Cache::clean('getContextualValue_'.$this->id.'_*');
		if(!empty($this->is_awocoupon)) return;
		return parent::update($null_values);	
	}


}

