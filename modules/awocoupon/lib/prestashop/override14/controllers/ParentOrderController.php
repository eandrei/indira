<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class ParentOrderController extends ParentOrderControllerCore {

	public function preProcess() {

		if ($this->nbProducts) {
			if (Tools::isSubmit('submitAddDiscount') AND Tools::getValue('discount_name')) {
				$discountName = Tools::getValue('discount_name');
				
				$discount = new Discount(Discount::getIdByName($discountName));
				if (Validate::isLoadedObject($discount)) {
					if ($tmpError = self::$cart->checkDiscountValidity($discount, self::$cart->getDiscounts(), self::$cart->getOrderTotal(), self::$cart->getProducts(), true))
						$this->errors[] = $tmpError;
				}
				else $this->errors[] = Tools::displayError('Voucher name invalid.');
				if (!sizeof($this->errors)) {
					self::$cart->addDiscount($discount->id);
					Tools::redirect('order-opc.php');
				}

				self::$smarty->assign(array(
					'errors' => $this->errors,
					'discount_name' => Tools::safeOutput($discountName)
				));
			}
			elseif (isset($_GET['deleteDiscount'])) {
				self::$cart->deleteDiscount((int)($_GET['deleteDiscount']));
				Tools::redirect('order-opc.php');
			}
			
			unset($_GET['submitAddDiscount'],$_GET['submitAddDiscount_x'],$_POST['submitAddDiscount'],$_POST['submitAddDiscount_X'],
					$_GET['deleteDiscount'],$_POST['deleteDiscounT'],$_GET['discount_name'],$_POST['discount_name']);

		}
		
		parent::preProcess();

	}

}