<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class AwoCouponGiftcertModuleFrontController {
	
	public function __construct() { 
		global $cookie;
		$this->option = 'giftcert';
		if (empty($cookie->id_customer)) Tools::redirect('authentication.php?back=modules/awocoupon/account.php');
	}
	

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent() {
		global $smarty, $cookie;
		
		
		$format = Tools::getValue('format','default');
		$task = Tools::getValue('coupon_code','');
		
		
		$this->ajax = true;
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/front/giftcert.php';
		
		if($format=='image') {
			$file = Tools::getValue('file','');
			$b64 = AwoCouponGiftcertModelFront::getRawCouponImage($file,(int)$cookie->id_customer); 
			if (!empty($b64)) {
				$fi = pathinfo($file); 
				$ext = strtolower($fi['extension']);
				Header('Content-Type: image/'.$ext);
				echo base64_decode($b64);  
			}
			else echo 'Not found';

		}
		else {
			$row = AwoCouponGiftcertModelFront::getCouponImage((int)$cookie->id_customer,Tools::getValue('coupon_code',''));
			$smarty->assign(array(
				'row' => $row,
				'id_customer'=>(int)$cookie->id_customer,
				'module_uri'=>AWO_URI,
			));
			echo $smarty->fetch(_PS_MODULE_DIR_.'awocoupon/ps14/front/tpl/giftcert.tpl');
		}
		

		exit;
	}
	

}