<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

class HTMLTemplateInvoice extends HTMLTemplateInvoiceCore	 {

	public function __construct(OrderInvoice $order_invoice, $smarty) {
		parent::__construct($order_invoice,$smarty);
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$awocoupon_details = (object) array();
		$awocoupon_total_tax_toadd_s = $awocoupon_total_tax_toadd_p = 0;
		$awocoupon_details = awohelper::loadResult('SELECT details FROM #__awocoupon_history WHERE order_id='.(int)$this->order->id);
		if(!empty($awocoupon_details)) $awocoupon_details = json_decode($awocoupon_details);
		if(!empty($awocoupon_details->cart_items)) {
			$awocoupon_details->cart_items = (array)$awocoupon_details->cart_items;
			$c_processed_coupons = array();
			foreach($awocoupon_details->cart_items as $i=>$r) $c_cart_items[(int)$i] = $r;
			$awocoupon_details->cart_items = $c_cart_items;
		}
		if(!empty($awocoupon_details->processed_coupons)) {
			$params = new awoParams();
			
			$is_before_gift = $params->get('enable_giftcert_discount_before_tax');
			$is_before_coupon = $params->get('enable_coupon_discount_before_tax');
			
			$awocoupon_details->processed_coupons = (array)$awocoupon_details->processed_coupons;
			$c_processed_coupons = array();
			foreach($awocoupon_details->processed_coupons as $i=>$r) $c_processed_coupons[(int)$i] = $r;
			$awocoupon_details->processed_coupons = $c_processed_coupons;
			
			$rows = awohelper::loadObjectList('SELECT id,function_type FROM #__awocoupon WHERE id IN ('.implode(',',array_keys($awocoupon_details->processed_coupons)).')');
			foreach($rows as $row) {
				$awocoupon_details->processed_coupons[(int)$row->id]->function_type = $row->function_type;
				if($row->function_type=='giftcert') 
					$awocoupon_details->processed_coupons[(int)$row->id]->is_discount_before = $is_before_gift ? true : false;
				else 
					$awocoupon_details->processed_coupons[(int)$row->id]->is_discount_before = $is_before_coupon ? true : false;
			}
			
			
			foreach($awocoupon_details->processed_coupons as $i=>$r)
				if(!$r->is_discount_before) {
					$awocoupon_total_tax_toadd_p += $r->product_discount-$r->product_discount_notax;
					$awocoupon_total_tax_toadd_s += $r->shipping_discount-$r->shipping_discount_notax;
				}
		}
		$awocoupon_details->awocoupon_total_tax_toadd_p = $awocoupon_total_tax_toadd_p;
		$awocoupon_details->awocoupon_total_tax_toadd_s = $awocoupon_total_tax_toadd_s;
		$awocoupon_details->order_total_tax = $this->order_invoice->total_paid_tax_incl - $this->order_invoice->total_paid_tax_excl + $awocoupon_total_tax_toadd_p + $awocoupon_total_tax_toadd_s;

		$this->awocoupon_details = $awocoupon_details;
		//printrx($this->awocoupon_details);exit;
	}

	public function getContent() {
	
		$this->smarty->assign(array(
			'awocoupon_details' => $this->awocoupon_details,
		));
		return parent::getContent();
	}
	
	
	
	
	public function getTaxTabContent() {
			$address = new Address((int)$this->order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			$tax_exempt = Configuration::get('VATNUMBER_MANAGEMENT')
								&& !empty($address->vat_number)
								&& $address->id_country != Configuration::get('VATNUMBER_COUNTRY');
			$carrier = new Carrier($this->order->id_carrier);
			
			$product_tax_breakdown = $this->order_invoice->getProductTaxesBreakdown();
			
			foreach($this->awocoupon_details->cart_items as $item) {
				$taxrate = round(($item->totaldiscount/$item->totaldiscount_notax)-1,2);
				foreach($product_tax_breakdown as $r=>$p) {//echo $r.' '.($taxrate*100);
					if($r==($taxrate*100)) {//echo 'here';
						$product_tax_breakdown[$r]['total_price_tax_excl'] += $item->totaldiscount_notax;
						$product_tax_breakdown[$r]['total_amount'] += $item->totaldiscount - $item->totaldiscount_notax;
						break;
					}
				}
			}
			//printr($this->awocoupon_details->cart_items);
			//printrx($product_tax_breakdown);
			$this->smarty->assign(array(
				'tax_exempt' => $tax_exempt,
				'use_one_after_another_method' => $this->order_invoice->useOneAfterAnotherTaxComputationMethod(),
				'product_tax_breakdown' => $product_tax_breakdown,
				'shipping_tax_breakdown' => $this->order_invoice->getShippingTaxesBreakdown($this->order),
				'ecotax_tax_breakdown' => $this->order_invoice->getEcoTaxTaxesBreakdown(),
				'wrapping_tax_breakdown' => $this->order_invoice->getWrappingTaxesBreakdown(),
				'order' => $this->order,
				'order_invoice' => $this->order_invoice,
				'carrier' => $carrier
			));

			return $this->smarty->fetch($this->getTemplate('invoice.tax-tab'));
	}


}
