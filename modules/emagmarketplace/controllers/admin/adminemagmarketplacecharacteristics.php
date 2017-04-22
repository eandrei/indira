<?php

/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7331 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../classes/emagmarketplacecharacteristic.php');
include_once(dirname(__FILE__).'/../../emagmarketplace.php');

class AdminEmagMarketplaceCharacteristicsController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap = true;
	 	$this->table = 'emagmp_characteristic_definitions';
		$this->className = 'EmagMarketplaceCharacteristic';
		$this->list_no_link = true;

		$this->fields_list = array(
			'emag_characteristic_id' => array(
				'title' => $this->l('ID'),
				'width' => 25,
				'orderby' => false,
				'search' => false
			),
			'emag_category_name' => array(
				'title' => $this->l('eMAG Category'),
				'width' => 'auto',
				'filter_key' => 'ecd!emag_category_name',
				'orderby' => false
			),
			'emag_characteristic_name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto',
				'orderby' => false,
				'search' => false
			),
			'id_feature' => array(
				'title' => $this->l('Feature'),
				'callback' => 'emagFeatureLabel',
				'width' => '300',
				'orderby' => false,
				'search' => false
			),
			'id_attribute_group' => array(
				'title' => $this->l('Attribute'),
				'callback' => 'emagAttributeGroupLabel',
				'width' => '300',
				'orderby' => false,
				'search' => false
			)
		);

		parent::__construct();
	}
	
	/**
	 * AdminController::renderList() override
	 * @see AdminController::renderList()
	 */
	public function renderList()
	{
		$this->_select = 'ecd.`emag_category_name`, IFNULL(ef.`id_feature`, 0) as id_feature, IFNULL(ea.`id_attribute_group`, 0) as id_attribute_group';
		$this->_join = '
			JOIN `'._DB_PREFIX_.'emagmp_category_definitions` ecd ON (a.`emag_category_id` = ecd.`emag_category_id`)
			JOIN `'._DB_PREFIX_.'emagmp_categories` ec ON (a.`emag_category_id` = ec.`emag_category_id` and ec.`sync_active` = 1)
			LEFT JOIN `'._DB_PREFIX_.'emagmp_features` ef ON (a.`emag_characteristic_id` = ef.`emag_characteristic_id` AND a.`emag_category_id` = ef.`emag_category_id`)
			LEFT JOIN `'._DB_PREFIX_.'emagmp_attribute_groups` ea ON (a.`emag_characteristic_id` = ea.`emag_characteristic_id` AND a.`emag_category_id` = ea.`emag_category_id`)
		';
		$this->_group = 'GROUP BY a.`emag_category_id`, a.`emag_characteristic_id`';
		
		return parent::renderList();
	}
	
	public function emagFeatureLabel($id_feature, $characteristic)
	{
		static $features = null;
		if (!is_array($features))
		{
			$features = array();
			$result = Feature::getFeatures($this->context->language->id);
			foreach ($result as $row)
			{
				$features[$row['id_feature']] = $row['name'];
			}
		}
		$options = '<option value=""></option>';
		foreach ($features as $id => $value)
		{
			$options .= '<option value="'.$id.'"'.($id == $id_feature ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
		return '<span style="white-space: nowrap;"><select name="id_feature['.$characteristic['emag_characteristic_id'].']['.$characteristic['emag_category_id'].']" style="width: 250px;">'.$options.'</select>&nbsp;<a class="button" href="javascript:;" onclick="updateEmagMarketplaceFeatureID('.$characteristic['emag_characteristic_id'].', '.$characteristic['emag_category_id'].', \''.Tools::getAdminTokenLite('AdminEmagMarketplaceCharacteristics').'\')">Update</a></span>';
	}
	
	public function emagAttributeGroupLabel($id_attribute_group, $characteristic)
	{
		static $attribute_groups = null;
		if (!is_array($attribute_groups))
		{
			$attribute_groups = array();
			$result = AttributeGroup::getAttributesGroups($this->context->language->id);
			foreach ($result as $row)
			{
				$attribute_groups[$row['id_attribute_group']] = $row['name'];
			}
		}
		$options = '<option value=""></option>';
		foreach ($attribute_groups as $id => $value)
		{
			$options .= '<option value="'.$id.'"'.($id == $id_attribute_group ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
		return '<span style="white-space: nowrap;"><select name="id_attribute_group['.$characteristic['emag_characteristic_id'].']['.$characteristic['emag_category_id'].']" style="width: 250px;">'.$options.'</select>&nbsp;<a class="button" href="javascript:;" onclick="updateEmagMarketplaceAttributeGroupID('.$characteristic['emag_characteristic_id'].', '.$characteristic['emag_category_id'].', \''.Tools::getAdminTokenLite('AdminEmagMarketplaceCharacteristics').'\')">Update</a></span>';
	}
	
	public function ajaxProcessUpdateEmagFeatureID()
	{
		$this->json = true;
		
		$emag_characteristic_id = Tools::getValue('emag_characteristic_id');
		$emag_category_id = Tools::getValue('emag_category_id');
		$id_feature = Tools::getValue('id_feature');
		
		if (EmagMarketplaceCharacteristic::updateEmagFeatureID($emag_characteristic_id, $emag_category_id, $id_feature))
			$this->confirmations[] = $this->l('Feature mapping saved successfully!');
		else
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not update the value in the database!!!');
		
		$this->status = 'ok';
	}

	public function ajaxProcessUpdateEmagAttributeGroupID()
	{
		$this->json = true;
		
		$emag_characteristic_id = Tools::getValue('emag_characteristic_id');
		$emag_category_id = Tools::getValue('emag_category_id');
		$id_attribute_group = Tools::getValue('id_attribute_group');
		
		if (EmagMarketplaceCharacteristic::updateEmagAttributeGroupID($emag_characteristic_id, $emag_category_id, $id_attribute_group))
			$this->confirmations[] = $this->l('Attribute mapping saved successfully!');
		else
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not update the value in the database!!!');
		
		$this->status = 'ok';
	}

	/**
	 * AdminController::initToolbar() override
	 * @see AdminController::initToolbar()
	 */
	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
		$this->toolbar_btn['previousEmagMarketplaceStep'] = array(
			'short' => 'Previous Step',
			'desc' => $this->l('Previous Step'),
			'href' => $this->context->link->getAdminLink('AdminEmagMarketplaceCategories')
		);
		$this->toolbar_btn['finalEmagMarketplaceStep'] = array(
			'short' => 'Upload Products',
			'desc' => $this->l('Upload Products'),
			'js' => 'uploadEmagMarketplaceProducts(\''.Tools::getAdminTokenLite('AdminEmagMarketplaceMain').'\', \''.$this->context->link->getAdminLink('AdminEmagMarketplaceMain').'\', \'wizard\')',
			'href' => 'javascript:;'
		);
	}

	/**
	 * AdminController::init() override
	 * @see AdminController::init()
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
		// toolbar (save, cancel, new, ..)
		$this->initToolbar();
		if ($this->display == 'edit' || $this->display == 'add')
		{
			if (!$this->loadObject(true))
				return;
			$this->content .= $this->renderForm();
		}
		else if ($this->display == 'view')
		{
			// Some controllers use the view action without an object
			if ($this->className)
				$this->loadObject(true);
			$this->content .= $this->renderView();
		}
		else if ($this->display == 'editFeatureValue')
		{
			if (!$this->object = new FeatureValue((int)Tools::getValue('id_feature_value')))
				return;

			$this->content .= $this->initFormEmagMarketplaceCharacteristicValue();
		}
		else if (!$this->ajax)
		{
			$this->content .= $this->renderList();
			$this->content .= $this->renderOptions();
		}

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryPlugin(array('autocomplete'));
		$this->addJS($this->module->path."views/js/admin.js");
		$this->addCSS($this->module->path.'views/css/admin.css', 'all');
	}

	/**
	 * AdminController::getList() override
	 * @see AdminController::getList()
	 */
	public function getList($id_lang, $order_by = 'ecd.emag_category_name, a.display_order, a.emag_characteristic_id', $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		/* Manage default params values */
		$use_limit = true;
		if ($limit === false)
			$use_limit = false;
		elseif (empty($limit))
		{
			if (isset($this->context->cookie->{$this->table.'_pagination'}) && $this->context->cookie->{$this->table.'_pagination'})
				$limit = $this->context->cookie->{$this->table.'_pagination'};
			else
				$limit = $this->_pagination[1];
		}

		if (!Validate::isTableOrIdentifier($this->table))
			throw new PrestaShopException(sprintf('Table name %s is invalid:', $this->table));

		if (empty($order_by))
		{
			if ($this->context->cookie->{$this->table.'Orderby'})
				$order_by = $this->context->cookie->{$this->table.'Orderby'};
			elseif ($this->_orderBy)
				$order_by = $this->_orderBy;
			else
				$order_by = $this->_defaultOrderBy;
		}

		if (empty($order_way))
		{
			if ($this->context->cookie->{$this->table.'Orderway'})
				$order_way = $this->context->cookie->{$this->table.'Orderway'};
			elseif ($this->_orderWay)
				$order_way = $this->_orderWay;
			else
				$order_way = $this->_defaultOrderWay;
		}

		$limit = (int)Tools::getValue('pagination', $limit);
		$this->context->cookie->{$this->table.'_pagination'} = $limit;

		/* Check params validity */
		/*if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)
			|| !is_numeric($start) || !is_numeric($limit)
			|| !Validate::isUnsignedId($id_lang))
			throw new PrestaShopException('get list params is not valid');*/

		/* Determine offset from current page */
		if ((Tools::getIsset('submitFilter'.$this->table) ||
		Tools::getIsset('submitFilter'.$this->table.'_x') ||
		Tools::getIsset('submitFilter'.$this->table.'_y')) &&
		Tools::getValue('submitFilter'.$this->table) &&
		is_numeric(Tools::getValue('submitFilter'.$this->table)))
			$start = ((int)Tools::getValue('submitFilter'.$this->table) - 1) * $limit;

		/* Cache */
		$this->_lang = (int)$id_lang;
		$this->_orderBy = $order_by;
		$this->_orderWay = Tools::strtoupper($order_way);

		/* SQL table : orders, but class name is Order */
		$sql_table = $this->table == 'order' ? 'orders' : $this->table;

		// Add SQL shop restriction
		$select_shop = $join_shop = $where_shop = '';
		if ($this->shopLinkType)
		{
			$select_shop = ', shop.name as shop_name ';
			$join_shop = ' LEFT JOIN '._DB_PREFIX_.$this->shopLinkType.' shop
							ON a.id_'.$this->shopLinkType.' = shop.id_'.$this->shopLinkType;
			$where_shop = Shop::addSqlRestriction($this->shopShareDatas, 'a', $this->shopLinkType);
		}

		$filter_shop = '';
		if ($this->multishop_context && Shop::isTableAssociated($this->table) && !empty($this->className))
		{
			$def = ObjectModel::getDefinition($this->className);
			if (Shop::getContext() != Shop::CONTEXT_ALL || !empty($def['multishop']) || !$this->context->employee->isSuperAdmin())
			{
				$idenfier_shop = Shop::getContextListShopID();
				if (!$this->_group)
					$this->_group = ' GROUP BY a.'.pSQL($this->identifier);
				elseif (!preg_match('#(\s|,)\s*a\.`?'.pSQL($this->identifier).'`?(\s|,|$)#', $this->_group))
					$this->_group .= ', a.'.pSQL($this->identifier);

				$test_join = !preg_match('#`?'.preg_quote(_DB_PREFIX_.$this->table.'_shop').'`? *sa#', $this->_join);
				if (Shop::isFeatureActive() && $test_join)
				{
					$filter_shop = ' JOIN `'._DB_PREFIX_.$this->table.'_shop` sa ';
					$filter_shop .= 'ON (sa.'.$this->identifier.' = a.'.$this->identifier.' AND sa.id_shop IN ('.implode(', ', $idenfier_shop).'))';
				}
			}
		}

		/* Query in order to get results with all fields */
		$lang_join = '';
		if ($this->lang)
		{
			$lang_join = 'LEFT JOIN `'._DB_PREFIX_.$this->table.'_lang` b ON (b.`'.$this->identifier.'` = a.`'.$this->identifier.'`';
			$lang_join .= ' AND b.`id_lang` = '.(int)$id_lang;
			if ($id_lang_shop)
				if (Shop::getContext() == Shop::CONTEXT_SHOP)
					$lang_join .= ' AND b.`id_shop`='.(int)$id_lang_shop;
				else
					$lang_join .= ' AND b.`id_shop` IN ('.implode(',', array_map('intval', Shop::getContextListShopID())).')';
			$lang_join .= ')';
		}

		$having_clause = '';
		if (isset($this->_filterHaving) || isset($this->_having))
		{
			 $having_clause = ' HAVING ';
			 if (isset($this->_filterHaving))
			 	$having_clause .= ltrim($this->_filterHaving, ' AND ');
			 if (isset($this->_having))
			 	$having_clause .= $this->_having.' ';
		}

		/*if (strpos($order_by, '.') > 0)
		{
			$order_by = explode('.', $order_by);
			$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
		}*/

		$sql = 'SELECT SQL_CALC_FOUND_ROWS
			'.($this->_tmpTableFilter ? ' * FROM (SELECT ' : '').'
			'.($this->lang ? 'b.*, ' : '').'a.*'.(isset($this->_select) ? ', '.$this->_select.' ' : '').$select_shop.'
			FROM `'._DB_PREFIX_.$sql_table.'` a
			'.$filter_shop.'
			'.$lang_join.'
			'.(isset($this->_join) ? $this->_join.' ' : '').'
			'.$join_shop.'
			WHERE 1 '.(isset($this->_where) ? $this->_where.' ' : '').($this->deleted ? 'AND a.`deleted` = 0 ' : '').
			(isset($this->_filter) ? $this->_filter : '').$where_shop.'
			'.(isset($this->_group) ? $this->_group.' ' : '').'
			'.$having_clause.'
			ORDER BY '.pSQL($order_by).' '.pSQL($order_way).
			($this->_tmpTableFilter ? ') tmpTable WHERE 1'.$this->_tmpTableFilter : '').
			(($use_limit === true) ? ' LIMIT '.(int)$start.','.(int)$limit : '');

		$this->_list = Db::getInstance()->executeS($sql);
		$this->_listTotal = Db::getInstance()->getValue('SELECT FOUND_ROWS() AS `'._DB_PREFIX_.$this->table.'`');
	}

}
