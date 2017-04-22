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
*  @version  Release: $Revision: 8971 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../classes/emagmarketplaceapicall.php');
include_once(dirname(__FILE__).'/../../classes/emagmarketplacecategory.php');
include_once(dirname(__FILE__).'/../../emagmarketplace.php');

class AdminEmagMarketplaceCategoriesController extends ModuleAdminController
{
	/**
	 *  @var object EmagMarketplaceCategory() instance for navigation
	 */
	private $_category = null;
	
	public $emag_category_definitions = null;

	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'category';
		$this->className = 'EmagMarketplaceCategory';
		$this->lang = true;
		$this->list_no_link = true;

		$this->context = Context::getContext();

 		$this->fieldImageSettings = array(
 			'name' => 'image',
 			'dir' => 'c'
 		);

		$this->fields_list = array(
			'id_category' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 20,
				'search' => false,
				'orderby' => false
			),
			'name' => array(
				'title' => $this->l('Name'),
				'callback' => 'categoryTreeName',
				'width' => 'auto',
				'search' => false,
				'orderby' => false
			),
			'emag_category_id' => array(
				'title' => $this->l('eMAG Category'),
				'callback' => 'emagCategoryLabel',
				'width' => '300',
				'search' => false,
				'orderby' => false
			),
			'emag_family_type_id' => array(
				'title' => $this->l('eMAG Family Type'),
				'callback' => 'emagFamilyTypeLabel',
				'width' => '300',
				'search' => false,
				'orderby' => false
			),
			'commission' => array(
				'title' => $this->l('Commission'),
				'align' => 'right',
				'callback' => 'emagCommissionLabel',
				'width' => '130',
				'search' => false,
				'orderby' => false
			),
			'sync_active' => array(
				'title' => $this->l('Sync'),
				'active' => 'status',
				'type' => 'bool',
				'width' => 70,
				'search' => false,
				'orderby' => false
			)
		);
		
		parent::__construct();

		$this->getEmagCategories();
	}
	
	public function getEmagCategories()
	{
		$result = Db::getInstance()->executeS('
			SELECT `emag_category_id`, `emag_category_name`
			FROM `'._DB_PREFIX_.'emagmp_category_definitions`
		');
		foreach ($result as $category_definition)
		{
			$this->emag_category_definitions[$category_definition['emag_category_id']] = $category_definition;
		}
	}
	
	public function ajaxProcessDownloadEmagCategories()
	{
		$this->json = true;
		
		$page = 1;
		$per_page = 200;
		do {
			$emagmp_api_call = new EmagMarketplaceAPICall();
			$emagmp_api_call->resource = 'category';
			$emagmp_api_call->action = 'read';
			$emagmp_api_call->data = array(
				'currentPage' => $page,
				'itemsPerPage' => $per_page
			);
			$emagmp_api_call->execute();
			$emagmp_api_call->save();
			if ($emagmp_api_call->status == 'success')
			{
				foreach ($emagmp_api_call->message_in_json->results as $category_definition)
				{
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'emagmp_category_definitions`
						SET
						`emag_category_id` = '.(int)$category_definition->id.',
						`emag_category_name` = "'.pSQL($category_definition->name).'"
						ON DUPLICATE KEY UPDATE
						`emag_category_name` = "'.pSQL($category_definition->name).'"
					');
					foreach ($category_definition->characteristics as $characteristic)
					{
						Db::getInstance()->execute('
							INSERT INTO `'._DB_PREFIX_.'emagmp_characteristic_definitions`
							SET
							`emag_characteristic_id` = '.(int)$characteristic->id.',
							`emag_category_id` = '.(int)$category_definition->id.',
							`emag_characteristic_name` = "'.pSQL($characteristic->name).'",
							`display_order` = '.(int)$characteristic->display_order.'
							ON DUPLICATE KEY UPDATE
							`emag_characteristic_name` = "'.pSQL($characteristic->name).'",
							`display_order` = '.(int)$characteristic->display_order.'
						');
					}
					foreach ($category_definition->family_types as $family_type)
					{
						Db::getInstance()->execute('
							INSERT INTO `'._DB_PREFIX_.'emagmp_family_type_definitions`
							SET
							`emag_family_type_id` = '.(int)$family_type->id.',
							`emag_category_id` = '.(int)$category_definition->id.',
							`emag_family_type_name` = "'.pSQL($family_type->name).'"
							ON DUPLICATE KEY UPDATE
							`emag_family_type_name` = "'.pSQL($family_type->name).'"
						');
					}
				}
				$page++;
			}
			else
			{
				$this->errors[] = Tools::displayError('Connection could not be established! The API function returned the following error: '.htmlentities($emagmp_api_call->message_in));
				$errors = true;
				break;
			}
		} while (count($emagmp_api_call->message_in_json->results) == $per_page);
		
		if (!$errors)
			$this->confirmations[] = $this->l('Categories downloaded successfully!');
		
		$this->status = 'ok';
	}

	public function init()
	{
		parent::init();

		// context->shop is set in the init() function, so we move the _category instanciation after that
		if (($id_category = Tools::getvalue('id_category')) && $this->action != 'select_delete')
			$this->_category = new EmagMarketplaceCategory($id_category);
		else
			if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
				$this->_category = new EmagMarketplaceCategory($this->context->shop->id_category);
			else if (count(EmagMarketplaceCategory::getCategoriesWithoutParent()) > 1)
				$this->_category = EmagMarketplaceCategory::getTopCategory();
			else
				$this->_category = new Category(Configuration::get('PS_HOME_CATEGORY'));
	}
	
	public function initContent()
	{
		if ($this->action == 'select_delete')
			$this->context->smarty->assign(array(
				'delete_form' => true,
				'url_delete' => htmlentities($_SERVER['REQUEST_URI']),
				'boxes' => $this->boxes,
			));

		parent::initContent();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryPlugin(array('autocomplete'));
		$this->addJS($this->module->path."views/js/admin.js");
		$this->addCSS($this->module->path.'views/css/admin.css', 'all');
	}

	public function renderList()
	{
		$count_categories_without_parent = count(EmagMarketplaceCategory::getCategoriesWithoutParent());
		$is_multishop = Shop::isFeatureActive();
		$top_category = EmagMarketplaceCategory::getTopCategory();
		/*if (Tools::isSubmit('id_category'))
			$id_parent = $this->_category->id;
		else if (!$is_multishop && $count_categories_without_parent > 1)
			$id_parent = $top_category->id;
		else if ($is_multishop && $count_categories_without_parent == 1)
			$id_parent = 2; //TODO need to get the ID category where category = Home
		else if ($is_multishop && $count_categories_without_parent > 1 && $this->context->shop() != Shop::CONTEXT_SHOP)
			$id_parent = $top_category->id;
		else
			$id_parent = $this->context->shop->id_category;*/

		$this->_filter .= ' AND `id_parent` != 0 ';
		$this->_select = 'IFNULL(ec.`emag_category_id`, 0) AS `emag_category_id`, IFNULL(ecd.`emag_category_name`, "") AS `emag_category_name`, IFNULL(ec.`emag_family_type_id`, 0) as `emag_family_type_id`, IFNULL(ec.`commission`, 0) AS commission, ec.`sync_active` ';
		$id = $this->context->shop->id;
		$id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');
		$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'category_shop` cs ON (a.`id_category` = cs.`id_category` AND cs.`id_shop` = '.(int)$id_shop.')
			LEFT JOIN `'._DB_PREFIX_.'emagmp_categories` ec ON (a.`id_category` = ec.`id_category`)
			LEFT JOIN `'._DB_PREFIX_.'emagmp_category_definitions` ecd ON (ec.`emag_category_id` = ecd.`emag_category_id`)
		';
		// we add restriction for shop
		if (Shop::getContext() == Shop::CONTEXT_SHOP && $is_multishop)
			$this->_where = ' AND cs.`id_shop` = '.(int)Context::getContext()->shop->id;

		$categories_tree = $this->_category->getParentsCategories();
		if (empty($categories_tree)
			&& ($this->_category->id_category != 1 || Tools::isSubmit('id_category'))
			&& (Shop::getContext() == Shop::CONTEXT_SHOP && !$is_multishop && $count_categories_without_parent > 1))
			$categories_tree = array(array('name' => $this->_category->name[$this->context->language->id]));

		asort($categories_tree);
		$this->tpl_list_vars['categories_tree'] = $categories_tree;

		if (Tools::isSubmit('submitBulkdelete'.$this->table) || Tools::isSubmit('delete'.$this->table))
		{
			$category = new EmagMarketplaceCategory(Tools::getValue('id_category'));
			if ($category->is_root_category)
				$this->tpl_list_vars['need_delete_mode'] = false;
			else
				$this->tpl_list_vars['need_delete_mode'] = true;
			$this->tpl_list_vars['delete_category'] = true;
			$this->tpl_list_vars['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
			$this->tpl_list_vars['POST'] = $_POST;
		}
		
		$this->tpl_list_vars['emag_category_definitions'] = $this->emag_category_definitions;
		
		return parent::renderList();
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, 'nleft', $order_way, $start, $limit, Context::getContext()->shop->id);
		// Check each row to see if there are combinations and get the correct action in consequence

		$nb_items = count($this->_list);
		for ($i = 0; $i < $nb_items; $i++)
		{
			$item = &$this->_list[$i];
			$category_tree = EmagMarketplaceCategory::getChildren((int)$item['id_category'], $this->context->language->id);
			if (!count($category_tree))
				$this->addRowActionSkipList('view', array($item['id_category']));
		}
	}

	public function renderView()
	{
		$this->initToolbar();
		return $this->renderList();
	}

	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
		$this->toolbar_btn['previousEmagMarketplaceStep'] = array(
			'short' => 'Previous Step',
			'desc' => $this->l('Previous Step'),
			'href' => $this->context->link->getAdminLink('AdminEmagMarketplaceConfig')
		);
		$this->toolbar_btn['downloadEmagMarketplaceCategories'] = array(
			'href' => 'javascript:;',
			'js' => 'downloadEmagMarketplaceCategories(\''.$this->token.'\')',
			'short' => $this->l('Download Categories'),
			'desc' => $this->l('Download Categories')
		);
		$this->toolbar_btn['nextEmagMarketplaceStep'] = array(
			'short' => 'Next Step',
			'desc' => $this->l('Next Step'),
			'href' => $this->context->link->getAdminLink('AdminEmagMarketplaceCharacteristics')
		);
	}

	public function initProcess()
	{
		parent::initProcess();
		/*$this->module->uninstallModuleTab('AdminEmagMarketplaceFeatures');
		$this->module->installModuleTab('AdminEmagMarketplaceCharacteristics', 'Characteristic Mapping', Tab::getIdFromClassName('AdminEmagMarketplaceMain'));*/

		if ($this->action == 'delete' || $this->action == 'bulkdelete')
			if (Tools::getIsset('cancel'))
				Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminCategories'));
			elseif (Tools::getValue('deleteMode') == 'link' || Tools::getValue('deleteMode') == 'linkanddisable' || Tools::getValue('deleteMode') == 'delete')
				$this->delete_mode = Tools::getValue('deleteMode');
			else
				$this->action = 'select_delete';
		
		if (!count($this->emag_category_definitions))
			$this->displayInformation($this->l('Before you start mapping your categories, you will need to download all category definitions from eMAG Marketplace, using the "Download Categories" button at the top right toolbar!'));
	}

	/*public function renderForm()
	{
		$this->initToolbar();
		$obj = $this->loadObject(true);
		$id_shop = Context::getContext()->shop->getID(true);
		$selected_cat = array((isset($obj->id_parent) && $obj->isParentCategoryAvailable($id_shop))? $obj->id_parent : Tools::getValue('id_parent', EmagMarketplaceCategory::getRootCategory()->id));
		$unidentified = new Group(Configuration::get('PS_UNIDENTIFIED_GROUP'));
		$guest = new Group(Configuration::get('PS_GUEST_GROUP'));
		$default = new Group(Configuration::get('PS_CUSTOMER_GROUP'));

		$unidentified_group_information = sprintf($this->l('%s - All persons without a customer account or unauthenticated.'), '<b>'.$unidentified->name[$this->context->language->id].'</b>');
		$guest_group_information = sprintf($this->l('%s - Customer who placed an order with the Guest Checkout.'), '<b>'.$guest->name[$this->context->language->id].'</b>');
		$default_group_information = sprintf($this->l('%s - All persons who created an account on this site.'), '<b>'.$default->name[$this->context->language->id].'</b>');
		$root_category = EmagMarketplaceCategory::getRootCategory();
		$root_category = array('id_category' => $root_category->id_category, 'name' => $root_category->name);
		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('Category'),
				'image' => '../img/admin/tab-categories.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'lang' => true,
					'size' => 48,
					'required' => true,
					'class' => 'copy2friendlyUrl',
					'hint' => $this->l('Invalid characters:').' <>;=#{}',
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Displayed:'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				),
				array(
					'type' => 'categories',
					'label' => $this->l('Parent category:'),
					'name' => 'id_parent',
					'values' => array(
						'trads' => array(
							 'Root' => $root_category,
							 'selected' => $this->l('selected'),
							 'Collapse All' => $this->l('Collapse All'),
							 'Expand All' => $this->l('Expand All')
						),
						'selected_cat' => $selected_cat,
						'input_name' => 'id_parent',
						'use_radio' => true,
						'use_search' => false,
						'disabled_categories' => array(4),
						'top_category' => EmagMarketplaceCategory::getTopCategory(),
					)
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Root Category:'),
					'name' => 'is_root_category',
					'required' => false,
					'is_bool' => true,
					'class' => 't',
					'values' => array(
						array(
							'id' => 'is_root_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'is_root_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					)
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Description:'),
					'name' => 'description',
					'lang' => true,
					'rows' => 10,
					'cols' => 100,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'file',
					'label' => $this->l('Image:'),
					'name' => 'image',
					'display_image' => true,
					'desc' => $this->l('Upload category logo from your computer')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta title:'),
					'name' => 'meta_title',
					'lang' => true,
					'hint' => $this->l('Forbidden characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta description:'),
					'name' => 'meta_description',
					'lang' => true,
					'hint' => $this->l('Forbidden characters:').' <>;=#{}'
				),
				array(
					'type' => 'tags',
					'label' => $this->l('Meta keywords:'),
					'name' => 'meta_keywords',
					'lang' => true,
					'hint' => $this->l('Forbidden characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Friendly URL:'),
					'name' => 'link_rewrite',
					'lang' => true,
					'required' => true,
					'hint' => $this->l('Forbidden characters:').' <>;=#{}'
				),
				array(
					'type' => 'group',
					'label' => $this->l('Group access:'),
					'name' => 'groupBox',
					'values' => Group::getGroups(Context::getContext()->language->id),
					'info_introduction' => $this->l('You have now three default customer groups.'),
					'unidentified' => $unidentified_group_information,
					'guest' => $guest_group_information,
					'customer' => $default_group_information,
					'desc' => $this->l('Mark all groups you want to give access to this category')
				)
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			)
		);
		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shop association:'),
				'name' => 'checkBoxShopAsso',
				'values' => Shop::getTree()
			);
		}
		if (Tools::isSubmit('add'.$this->table.'root'))
			unset($this->fields_form['input'][2],$this->fields_form['input'][3]);

		if (!($obj = $this->loadObject(true)))
			return;

		$image = ImageManager::thumbnail(_PS_CAT_IMG_DIR_.'/'.$obj->id.'.jpg', $this->table.'_'.(int)$obj->id.'.'.$this->imageType, 350, $this->imageType, true);

		$this->fields_value = array(
			'image' => $image ? $image : false,
			'size' => $image ? filesize(_PS_CAT_IMG_DIR_.'/'.$obj->id.'.jpg') / 1000 : false
		);

		// Added values of object Group
		$category_groups = $obj->getGroups();
		$category_groups_ids = array();
		if (is_array($category_groups))
			foreach ($category_groups as $category_group)
				$category_groups_ids[] = $category_group['id_group'];

		$groups = Group::getGroups($this->context->language->id);
		// if empty $carrier_groups_ids : object creation : we set the default groups
		if (empty($category_groups_ids))
		{
			$preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
			$category_groups_ids = array_merge($category_groups_ids, $preselected);
		}
		foreach ($groups as $group)
			$this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue('groupBox_'.$group['id_group'], (in_array($group['id_group'], $category_groups_ids)));

		return parent::renderForm();
	}*/
	
	public static function categoryTreeName($category_name, $category)
	{
		return str_repeat('&nbsp;', 4 * ($category['level_depth'] - 1)).$category_name;
	}

	/**
	  * Allows to display the category description without HTML tags and slashes
	  *
	  * @return string
	  */
	public static function emagCategoryLabel($emag_category_id, $category)
	{
		$label = '';
		if ($emag_category_id != 0)
			$label = $emag_category_id.' - '.$category['emag_category_name'];
		return '<span style="white-space: nowrap;"><input type="text" name="emag_category_id['.$category['id_category'].']" value="'.htmlentities($label).'" style="width: 250px;" />&nbsp;<a class="button" href="javascript:;" onclick="updateEmagMarketplaceCategory('.$category['id_category'].', \''.Tools::getAdminTokenLite('AdminEmagMarketplaceCategories').'\')">Update</a></span>';
	}
	
	public static function emagFamilyTypeLabel($emag_family_type_id, $category)
	{
		static $family_types = null;
		if (!is_array($family_types))
		{
			$family_types = array();
			$result = Db::getInstance()->executeS('
				SELECT *
				FROM `'._DB_PREFIX_.'emagmp_family_type_definitions` AS ef
			', false);
			if ($result)
			{
				while ($row = Db::getInstance()->nextRow($result))
				{
					$family_types[$row['emag_category_id']][$row['emag_family_type_id']] = $row['emag_family_type_name'];
				}
			}
		}
		$label = '<select type="text" name="emag_family_type_id['.$category['id_category'].']" style="width: 250px;" /><option value="0"> </option>';
		foreach ((array)$family_types[$category['emag_category_id']] as $family_type_id => $family_type_name)
		{
			$label .= '<option value="'.$family_type_id.'"'.($family_type_id == $emag_family_type_id ? ' selected="selected"' : '').'>'.$family_type_name.'</option>';
		}
		$label .= '</select>';
		return '<span style="white-space: nowrap;">'.$label.'&nbsp;<a class="button" href="javascript:;" onclick="updateEmagMarketplaceFamilyType('.$category['id_category'].', \''.Tools::getAdminTokenLite('AdminEmagMarketplaceCategories').'\')">Update</a></span>';
	}
	
	public static function emagCommissionLabel($commission, $category)
	{
		if ($commission <= 0)
		{
			$commission = '';
		}
		return '<span style="white-space: nowrap;"><input type="text" name="commission['.$category['id_category'].']" value="'.$commission.'" style="width: 80px; text-align: right" />&nbsp;<a class="button" href="javascript:;" onclick="updateEmagMarketplaceCommission('.$category['id_category'].', \''.Tools::getAdminTokenLite('AdminEmagMarketplaceCategories').'\')">Update</a></span>';
	}
	
	public function ajaxProcessUpdateEmagCategoryID()
	{
		$this->json = true;
		
		$id_category = Tools::getValue('id_category');
		$emagMarketplaceCategory = new EmagMarketplaceCategory($id_category);
		
		$emag_category_label = Tools::getValue('emag_category_label');
		preg_match('`^([0-9]+) - `', $emag_category_label, $match);
		
		if (!$match[1])
			!$match[1] = 0;
			
		$emagMarketplaceCategory->emag_category_id = $match[1];
		$result = $emagMarketplaceCategory->updateEmagCategoryID();
		if ($result)
		{
			$this->confirmations[] = $this->l('Category mapping saved successfully!');
			if ($result == 2)
				$this->content = $this->emagFamilyTypeLabel(0, array('id_category' => $id_category, 'emag_category_id' => $emagMarketplaceCategory->emag_category_id));
		}
		else
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not update the value in the database!!!');
		
		$this->status = 'ok';
	}
	
	public function ajaxProcessUpdateEmagFamilyTypeID()
	{
		$this->json = true;
		
		$id_category = Tools::getValue('id_category');
		$emagMarketplaceCategory = new EmagMarketplaceCategory($id_category);
		
		$emag_family_type_id = Tools::getValue('emag_family_type_id');
			
		$emagMarketplaceCategory->emag_family_type_id = $emag_family_type_id;
		if ($emagMarketplaceCategory->updateEmagFamilyTypeID())
			$this->confirmations[] = $this->l('Family type mapping saved successfully!');
		else
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not update the value in the database!!!');
		
		$this->status = 'ok';
	}
	
	public function ajaxProcessUpdateEmagCommission()
	{
		$this->json = true;
		
		$id_category = Tools::getValue('id_category');
		$emagMarketplaceCategory = new EmagMarketplaceCategory($id_category);
		
		$commission = Tools::ps_round(Tools::getValue('commission'), 4);
			
		$emagMarketplaceCategory->commission = $commission;
		if ($emagMarketplaceCategory->updateCommission())
			$this->confirmations[] = $this->l('Commission saved successfully!');
		else
			$this->errors[] = Tools::displayError('Something is terribly wrong! Could not update the value in the database!!!');
		
		$this->status = 'ok';
	}
}


