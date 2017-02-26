<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminPrettyUrlsController extends ModuleAdminController {

	public function __construct()
	{
		$this->bootstrap = true;
		$this->context = Context::getContext();
		parent::__construct();
	}

	public function renderList()
	{
		$product_urls = $this->getAllProductCollisions();
		$category_urls = $this->getAllCategoryCollisions();
		$compare_urls = $this->getAllCompareCollisions();
		$langs = Language::getLanguages();
		$langs = count($langs);
		//echo '<pre>'; print_r($compare_urls); exit;
		$this->context->smarty->assign(array(
			'product_coll' => $product_urls,
			'category_coll' => $category_urls,
			'compare_coll' => $compare_urls,
			'langs_active' => (int)$langs
		));

		parent::renderList();
		return $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/pretty_urls/helpers/form/form.tpl');
	}

	private function getAllProductCollisions()
	{
		$langs = Language::getLanguages();
		$langs = count($langs);
		return Db::getInstance()->executeS('
		SELECT DISTINCT `link_rewrite`, `id_product`, `name`, count(`link_rewrite`) as times
		FROM `'._DB_PREFIX_.'product_lang`
		GROUP BY `link_rewrite`
		HAVING COUNT(`link_rewrite`) > '.(int)$langs);
	}

	private function getAllCategoryCollisions()
	{
		$langs = Language::getLanguages();
		$langs = count($langs);
		return Db::getInstance()->executeS('
		SELECT DISTINCT `link_rewrite`, `id_category`, `name`, count(`link_rewrite`) as times
		FROM `'._DB_PREFIX_.'category_lang`
		GROUP BY `link_rewrite`
		HAVING COUNT(`link_rewrite`) > '.(int)$langs);
	}

	private function getAllCompareCollisions()
	{
		return Db::getInstance()->executeS('
		SELECT DISTINCT p.`link_rewrite`, p.`id_product`, c.`link_rewrite`, c.`id_category`
		FROM `'._DB_PREFIX_.'product_lang` p, `'._DB_PREFIX_.'category_lang` c
		WHERE p.`link_rewrite` = c.`link_rewrite`');
	}
}