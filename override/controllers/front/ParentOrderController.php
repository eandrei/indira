<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class ParentOrderController extends ParentOrderControllerCore {

	public function init() {
	
		parent::init();
	
		if ($this->nbProducts) {
		
			if (CartRule::isFeatureActive()) {
			
				if ($id_cart_rule = Tools::getValue('deleteDiscount')) {
					$this->context->cart->removeCartRule($id_cart_rule);
					Tools::redirect('index.php?controller=order-opc');
				}
				
			}
			
		}
		

	}

}