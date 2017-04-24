<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

require_once _PS_MODULE_DIR_.'awocoupon/controllers/front/ParentController.php';
class AwoCouponCouponsModuleFrontController extends AwoCouponParentFrontController {
	
	public function init()
	{
		if (!$this->context->customer->isLogged()) Tools::redirect('index.php?controller=authentication&back='.urlencode('coupons&fc=module&module=awocoupon'));
		$this->display_column_left = false;
		parent::init();
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		
		// Declare smarty function to render pagination link
		smartyRegisterFunction($this->context->smarty, 'function', 'couponspaginationlink', array('AwoCouponCouponsModuleFrontController', 'getCouponsPaginationLink'));
	}
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		if (!isset($_SESSION)) session_start();
		
		$_errors = $_success = '';
		
		
		$this->_checktasks();
		
		
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/front/coupons.php';
		
		//list($rows,$total_count) =
		$rows = AwoCouponCouponsModelFront::getData((int)$this->context->customer->id,
						true,
						((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
						((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1));
		$total_count = count($rows);
		
		$this->context->smarty->assign(array(
		
			'rows' => $rows,
			'total_count'=>$total_count,
			'id_customer'=>$this->context->customer->id,
			'module_uri'=>AWO_URI,
			'error'=>$_errors,
			'success'=>Tools::getValue('success'),
		));

		if(version_compare(_PS_VERSION_, '1.6', '<=')) {
			$this->context->smarty->assign(array(
				'page' => ((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1),
				'nbpagination' => ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
				'nArray' => array(10, 20, 50),
				'max_page' => ceil($total_count / ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10)),
				'pagination_link' => $this->getCouponsPaginationLink(array(), $this->context->smarty),
			));
		}
		
		$this->setTemplate('coupons.tpl');
		

		return;
	}
	
	public function _checktasks()
	{
		$_errors = $_success = '';
		
		$task = Tools::getValue('task', '');
		switch ($task)
		{
			case 'activate':
				$this->task_activate();
				break;
		}
		
		return $_errors;
	}

	
	public function task_activate()
	{
		$coupon_id = Tools::getValue('id', '');
		$user_id = (int)$this->context->customer->id;
		if (!empty($coupon_id) && !empty($user_id))
		{
			$coupon_id = awohelper::loadResult('
					SELECT c.id 
					  FROM #__awocoupon c 
					  JOIN #__awocoupon_giftcert_order o ON o.order_id=c.order_id 
					  JOIN #__awocoupon_giftcert_order_code gc ON gc.giftcert_order_id=o.id AND gc.coupon_id=c.id 
					 WHERE c.published=-1 
					   AND c.id='.$coupon_id.' 
					   AND o.user_id='.$user_id);
			if (!empty($coupon_id))
			{
				awohelper::query('UPDATE #__awocoupon SET published=1 WHERE id='.$coupon_id);
				Tools::redirect('index.php?fc=module&module=awocoupon&controller=coupons&success=1');
			}
		}
		Tools::redirect('index.php?fc=module&module=awocoupon&controller=coupons');
	}
	

	
	/**
	 * Render pagination link for payment
	 *
	 * @param (array) $params Array with to parameters p (for page number) and n (for nb of items per page)
	 * @return string link
	 */
	public static function getCouponsPaginationLink($params, &$smarty)
	{
		$p = !isset($params['p']) ? 1 : $params['p'];
		$n = !isset($params['n']) ? 10 : $params['n'];

		return Context::getContext()->link->getModuleLink('awocoupon',
			'coupons',
			array(
				'process' => 'coupons',
				'p' => $p,
				'n' => $n,
			));
	}
	

}