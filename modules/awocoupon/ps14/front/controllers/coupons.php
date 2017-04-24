<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


class AwoCouponCouponsModuleFrontController {
	
	public function __construct() { 
		global $cookie;
		$this->option = 'coupons';
		if (empty($cookie->id_customer)) Tools::redirect('authentication.php?back=modules/awocoupon/account.php');
	}

	public function initContent() {
		global $smarty, $cookie;
		
		$this->_checktasks();

		require_once _PS_MODULE_DIR_.'awocoupon/classes/front/coupons.php';
		
		//list($rows,$total_count) =
		$rows = AwoCouponCouponsModelFront::getData(
						(int)$cookie->id_customer,
						true,
						((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
						((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1)
					);
		$total_count = count($rows);
		
		foreach($rows as &$row) {
			$row->startdate_str = !empty($row->startdate) ? Tools::displayDate($row->startdate,$cookie->id_lang,true) : '';
			$row->expiration_str = !empty($row->expiration) ? Tools::displayDate($row->expiration,$cookie->id_lang,true) : '';
		}
		
		$smarty->assign(array(
		
			'rows' => $rows,
			'total_count'=>$total_count,
			'id_customer'=>(int)$cookie->id_customer,
			'module_uri'=>AWO_URI,
			'success'=>Tools::getValue('success'),

			'page' => ((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1),
			'nbpagination' => ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
			'nArray' => array(10, 20, 50),
			'max_page' => floor(count($total_count) / ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10)),
			'pagination_link' => __PS_BASE_URI__.'modules/awocoupon/account.php?option='.$this->option,
			'page_link' => __PS_BASE_URI__.'modules/awocoupon/account.php',
			'option' => $this->option,
		));
			
		
		return $smarty->fetch(_PS_MODULE_DIR_.'awocoupon/ps14/front/tpl/'.$this->option.'.tpl');
		
	}
	
	function _checktasks() {
		$_errors = $_success = '';
		
		$task = Tools::getValue('task','');
		switch($task) {
			case 'activate': $this->task_activate(); break;
		}
		
		return $_errors;
	}

	
	function task_activate() {
		global $cookie;
		$coupon_id = Tools::getValue('id','');
		$user_id = (int)$cookie->id_customer;
		if(!empty($coupon_id) && !empty($user_id)) {
			$coupon_id = awohelper::loadResult('
					SELECT c.id 
					  FROM #__awocoupon c 
					  JOIN #__awocoupon_giftcert_order o ON o.order_id=c.order_id 
					  JOIN #__awocoupon_giftcert_order_code gc ON gc.giftcert_order_id=o.id AND gc.coupon_id=c.id 
					 WHERE c.published=-1 
					   AND c.id='.$coupon_id.' 
					   AND o.user_id='.$user_id);
			if(!empty($coupon_id)) {
				awohelper::query('UPDATE #__awocoupon SET published=1 WHERE id='.$coupon_id);
				Tools::redirect('modules/awocoupon/account.php?option=coupons&success=1');
			}
		}
		Tools::redirect('modules/awocoupon/account.php?option=coupons');
	}
	

	
	public function setMedia() {
		Tools::addJS(_PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js');
		Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css');
		Tools::addJS(__PS_BASE_URI__.'modules/awocoupon/media/js/modalpopup.js');
	}

}