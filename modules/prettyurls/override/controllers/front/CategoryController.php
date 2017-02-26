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
 * @copyright Copyright 2016 © fmemodules.com All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class CategoryController extends CategoryControllerCore
{
	public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('category_rewrite')));
		$cat_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cat_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '')
			$link_rewrite = $url_array[2];
		if ($link_rewrite)
		{
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT `id_category`
					FROM '._DB_PREFIX_.'category_lang
					WHERE `link_rewrite` = \''.pSQL($link_rewrite).'\'
					AND `id_lang` = '.(int)$id_lang.'
					AND `id_shop` = '.(int)$id_shop;
			$id_category = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_category > 0)
			{
				$_POST['id_category'] = $id_category;
				$_GET['category_rewrite'] = '';
			}
			//IF no Route to category Found than it must be 404 page
			elseif ($id_category <= 0)
			{
				$_GET['category_rewrite'] = '';
				Tools::redirect($this->context->link->getPageLink('page-not-found'));
			}
		}
		parent::init();
	}
}