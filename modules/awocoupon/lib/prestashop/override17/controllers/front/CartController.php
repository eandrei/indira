<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class CartController extends CartControllerCore {
		
	public function initContent() {
		parent::initContent();
		
		$presenter = new CartPresenter();
		$presented_cart = $presenter->present($this->context->cart, $shouldSeparateGifts = true);
		
		$summary = $this->context->cart->getSummaryDetails();
		if(!empty($summary)) {
			$this->priceFormatter = new PriceFormatter();
			$presented_cart['subtotals']['tax']['amount'] = $summary['total_tax'];
			$presented_cart['subtotals']['tax']['value'] = $this->priceFormatter->format($summary['total_tax']);
		}

        $this->context->smarty->assign([
            'cart' => $presented_cart,
        ]);
    }


    protected function updateCart() {
 		parent::updateCart();
		
      // Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
        if ($this->context->cookie->exists() && !$this->errors && !($this->context->customer->isLogged() && !$this->isTokenValid())) {
           if (CartRule::isFeatureActive()) {
                if ($id_cart_rule = Tools::getValue('deleteDiscount')) {
                    $this->context->cart->removeCartRule($id_cart_rule);
                    CartRule::autoAddToCart($this->context);
                }
            }
        }
		
    }

}
