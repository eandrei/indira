<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class OrderDetail extends OrderDetailCore {

	public function saveTaxCalculator(Order $order, $replace = false) {
	
		// Nothing to save
		if ($this->tax_calculator == null) return true;
		if (!($this->tax_calculator instanceOf TaxCalculator)) return false;
		if (count($this->tax_calculator->taxes) == 0) return true;
		if ($order->total_products <= 0) return true;

		
		static $static_coupon_data = null;
		if(is_null($static_coupon_data)) {
			if(!class_exists('awoHelper')) require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

			$coupon_data = awoHelper::loadResult('SELECT coupon_data FROM '._DB_PREFIX_.'awocoupon_cart WHERE id_cart='.(int)$order->id_cart);
			if(empty($coupon_data)) {
				$coupon_data = awohelper::loadResult('SELECT details FROM #__awocoupon_history WHERE order_id='.(int)$order->id);
				if(!empty($coupon_data)) $coupon_data = json_decode($coupon_data,true);
			}
			else $coupon_data = unserialize($coupon_data);
			$static_coupon_data = !empty($coupon_data['cart_items']) ? $coupon_data : '';
		}

		$coupon_data = $static_coupon_data;
		if(empty($coupon_data['cart_items'])) return parent::saveTaxCalculator($order,$replace);
		
		$discounted_price_tax_excl = -1;
		foreach($coupon_data['cart_items'] as $item) {
			list($product_id,$attribute_id) = explode(':',$item['key']);
			if($product_id==$this->product_id && $attribute_id==$this->product_attribute_id && $item['qty']==$this->product_quantity) {
				$discounted_price_tax_excl = $this->unit_price_tax_excl-$item['total_price_notax_reduction_amount'];
				break;
			}
		}
		if($discounted_price_tax_excl<0) return parent::saveTaxCalculator($order,$replace);
				
		
		// Prestashop Default

		$values = '';
		foreach ($this->tax_calculator->getTaxesAmount($discounted_price_tax_excl) as $id_tax => $amount)
		{
			$unit_amount = (float)Tools::ps_round($amount, 2);
			$total_amount = $unit_amount * $this->product_quantity;
			$values .= '('.(int)$this->id.','.(float)$id_tax.','.$unit_amount.','.(float)$total_amount.'),';
		}

		if ($replace)
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_detail_tax` WHERE id_order_detail='.(int)$this->id);
			
		$values = rtrim($values, ',');
		$sql = 'INSERT INTO `'._DB_PREFIX_.'order_detail_tax` (id_order_detail, id_tax, unit_amount, total_amount)
				VALUES '.$values;
		
		return Db::getInstance()->execute($sql);
	}

}

