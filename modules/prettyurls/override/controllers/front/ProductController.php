<?php
/**
 * FMM PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  FMM Modules
 * @package   PrettyURLs
 * @author    FMM Modules
 * @copyright Copyright 2016 © Fmemodules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class ProductController extends ProductControllerCore
{
	public function init()
	{
		$link_rewrite 	= Tools::safeOutput(urldecode(Tools::getValue('product_rewrite')));
		$prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)\.html/';
		preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $url_array);

		if (isset($url_array[2]) && $url_array[2] != '')
			$link_rewrite = $url_array[2];
		
		if ($link_rewrite)
		{
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
			$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_product > 0)
			{
				$_POST['id_product'] = $id_product;
				$_GET['product_rewrite'] = '';
			}
			else
			{
				$prod_pattern_sec = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*\-[0-9\pL]*)\.html/';
				preg_match($prod_pattern_sec, $_SERVER['REQUEST_URI'], $url_array_sec);
			
				if (isset($url_array_sec[2]) && $url_array_sec[2] != '')
				{
					$segments = explode('-', $url_array_sec[2]);
					array_pop($segments);
					$link_rewrite = implode('-', $segments);
				}
				$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
				$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
				if ($id_product > 0)
				{
					$_POST['id_product'] = $id_product;
					$_GET['product_rewrite'] = '';
				}
			}
		}
		parent::init();
	}
}