<?php

/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class AwoCouponHookActionCartSummary {

	public static function execute($summary) {
		
		if(!class_exists('AwoCouponCouponHandler')) require _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_coupon_code($summary['cart']);
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
		
		return $summary;
		
	}
	
}
