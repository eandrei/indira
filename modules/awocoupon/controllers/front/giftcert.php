<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


require_once _PS_MODULE_DIR_.'awocoupon/controllers/front/ParentController.php';
class AwoCouponGiftcertModuleFrontController extends AwoCouponParentFrontController
{
	
	public function init()
	{
		if (!$this->context->customer->isLogged()) Tools::redirect('index.php?controller=authentication&back='.urlencode('coupons&fc=module&module=awocoupon'));
		$this->display_column_left = false;
		parent::init();
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		
	}
	

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		if (!isset($_SESSION)) session_start();
		
		$_errors = $_success = '';
		
		
		$format = Tools::getValue('format', 'default');
		$task = Tools::getValue('coupon_code', '');
		
		
		$this->ajax = true;
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/front/giftcert.php';
		
		if ($format == 'image')
		{
			$file = Tools::getValue('file', '');
			$b64 = AwoCouponGiftcertModelFront::getRawCouponImage($file, (int)$this->context->customer->id); 
			if (!empty($b64))
			{
				$fi = pathinfo($file); 
				$ext = strtolower($fi['extension']);
				Header('Content-Type: image/'.$ext);
				echo base64_decode($b64);  
			}
			else echo 'Not found';

		}
		else
		{
			$row = AwoCouponGiftcertModelFront::getCouponImage((int)$this->context->customer->id, Tools::getValue('coupon_code', ''));
			$this->context->smarty->assign(array(
				'row' => $row,
				'id_customer'=>$this->context->customer->id,
				'module_uri'=>AWO_URI,
			));
			$this->setTemplate('giftcert.tpl');
			echo $this->smartyOutputContent($this->template);
		}
		

		exit;
	}
	


	
	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(__PS_BASE_URI__.'modules/awocoupon/media/css/style.css');
		//$this->addJqueryPlugin(array('thickbox', 'idTabs'));
	}
}