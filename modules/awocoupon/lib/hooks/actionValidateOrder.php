<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class AwoCouponHookActionValidateOrder {

	public static function execute($params) {
		
		if (empty($params['order'])) return;
		
		$order = $params['order'];
		$cart = $params['cart'];
		if (empty($order->id)) return;
		
		
		if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		$awocoupon_details = awohelper::getOrderCoupon($order->id);
		if(empty($awocoupon_details)) return;
		
		
		if (version_compare(_PS_VERSION_, '1.6', '>=')) {
			//$order->total_discounts_tax_excl += round($awocoupon_details->totalaftertax_product_tax + $awocoupon_details->totalaftertax_shipping_tax + $awocoupon_details->totalaftertax_giftwrap_tax 
			//	- $awocoupon_details->totalaftertax_product_tax_real - $awocoupon_details->totalaftertax_shipping_tax_real - $awocoupon_details->totalaftertax_giftwrap_tax_real, 2);
			$order->total_discounts_tax_excl -= round(
					$awocoupon_details->totalaftertax_product_notax
					+ $awocoupon_details->totalaftertax_shipping_notax
					+ $awocoupon_details->totalaftertax_giftwrap_notax
			, 2);
		}
		else $order->total_discounts_tax_excl -= round($awocoupon_details->totalaftertax_product_notax, 2);
		$order->total_paid_tax_excl -= round($awocoupon_details->totalaftertax_product_tax + $awocoupon_details->totalaftertax_shipping_tax, 2);
		if ($order->total_paid_tax_excl < 0) $order->total_paid_tax_excl = 0;
		$order->update();
		
	
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{

			#
			# clear out the order detail tax information
			#
			$order_details = awohelper::loadObjectList('SELECT id_order_detail FROM #__order_detail WHERE id_order='.(int)$order->id);
			foreach ($order_details as $row)
				awohelper::query('DELETE FROM #__order_detail_tax WHERE id_order_detail='.(int)$row->id_order_detail);
			awohelper::query('DELETE FROM #__order_detail WHERE id_order='.(int)$order->id);

			#
			# rebuild the order tax information
			#
			$order_detail = new OrderDetail();
			$product_list = $cart->getProducts();
			$order_state = $order->getCurrentOrderState();
			
			// fix stock as it is updated again in createList
			foreach ($product_list as $product)
			{
				if ($order_state != Configuration::get('PS_OS_CANCELED') && $order_state != Configuration::get('PS_OS_ERROR'))
				{
					if (!StockAvailable::dependsOnStock($product['id_product']))
						$update_quantity = StockAvailable::updateQuantity($product['id_product'], $product['id_product_attribute'], (int)$product['cart_quantity']);

					Product::updateDefaultAttribute($product['id_product']);
				}
			}
			
			$order_detail->createList(
				$order, 
				$cart, 
				$order_state, 
				$product_list, 
				$id_order_invoice = 0,
				$use_taxes = true, 
				$id_warehouse = 0
			);
			//$order_detail->updateTaxAmount($order);
		
		}
		
		
	}
	
}
