<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

require_once(_PS_MODULE_DIR_.'awocoupon/awocoupon.php');
class AdminAwoCouponController extends ModuleAdminController {
	private $awocoupon = null;
	public $multishop_context_group = false;
	
	public function __construct()
	{
		$this->awocoupon = new AwoCoupon();
		$this->table = ''; // compatibility with 1.5.6
		$this->className = 'AwoCoupon';
		if(_PS_VERSION_>='1.7') $this->translator = Context::getContext()->getTranslator();
		$this->views	= array(
					'cpanel'=>$this->l('Dashboard'),
					'coupon'=>$this->l('Coupons'),
					'giftcert'=>$this->l('Gift Certificates'),
					'profile'=>$this->l('Profiles'),
					'history'=>$this->l('History of Uses'),
					'import'=>$this->l('Import'),
					'report'=>$this->l('Reports'),
					'license'=>$this->l('License'),
					'about'=>$this->l('About'),
				);	
		$this->list_no_link = true;
		parent::__construct();
		$this->getmybreadcrumb();
		$this->override_folder = 'awocoupon/';
		$this->tpl_folder = 'awocoupon/';
		# version_compare(_PS_VERSION_,'1.6','>=')
			$this->show_page_header_toolbar = true;
			$this->bootstrap = true;

		//$this->context = Context::getContext();
		$this->_conf[101] = 'Data Saved';
		$this->_conf[102] = 'License Activated';
		$this->_conf[103] = 'Invalid License';
		$this->_conf[104] = 'Email Sent';
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		
		$this->my_admin_link = awohelper::admin_link();
		

	}
	
	public function initContent()
	{
		if (!$this->viewAccess())
		{
			$this->errors[] = Tools::displayError('You do not have permission to view this.');
			return;
		}
				
		$view = Tools::getValue('view', 'cpanel');
		$layout = Tools::getValue('layout', 'default');
		$task = Tools::getValue('task');

		if (Tools::getIsset('add') || Tools::getIsset('add'.$view.$layout)) $layout = str_replace('default', 'edit', $layout);
		if (Tools::getIsset('update') || Tools::getIsset('update'.$view.$layout)) $layout = str_replace('default', 'edit', $layout);
		if (Tools::getIsset('delete') || Tools::getIsset('submitBulkdelete') || Tools::getIsset('delete'.$view.$layout) || Tools::getIsset('submitBulkdelete'.$view.$layout)) $task = 'delete';
		if (Tools::getIsset('duplicate') || Tools::getIsset('duplicate'.$view.$layout)) $task = 'duplicate';
		if (Tools::getIsset('task_default') || Tools::getIsset('submitBulktask_default') || Tools::getIsset('task_default'.$view.$layout) || Tools::getIsset('submitBulktask_default'.$view.$layout)) $task = 'makedefault';
		if (Tools::getIsset('task_generate') || Tools::getIsset('submitBulktask_generate') || Tools::getIsset('task_generate'.$view.$layout) || Tools::getIsset('submitBulktask_generate'.$view.$layout)) $layout = 'generateedit';
		if ($task == 'runreport' && Tools::getIsset('report_type')) $layout = str_replace('_', '', Tools::getValue('report_type'));
		if (isset($_POST['submitReset'.$view.$layout])) $this->action = 'reset_filters';
		
		
		if (!empty($task)) $this->_task($view, $layout, $task);
		
		$function = '_display'.$view.$layout;
		$html = (method_exists($this, $function)) ? $this->$function() : '';
		
		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$this->context->smarty->assign(array(
				'toolbar_btn' => $this->page_header_toolbar_btn,
			));
		}
		
		
		$this->content = '
			<div>'.$this->getHTMLMenu().'</div>
			<table class="table_grid"><tr><td>
				<style>span.current { font-weight:bold; color:#7E3817;} table{width:100%;} table.admintable { width:auto; } div.path_bar { display:block; } </style>
				<div>'.$html.'</div><div class="clear"></div>
				'.$this->getHTMLFooter().'
			</td></tr></table>
		';


		
		$this->getLanguages();
		if (method_exists($this, 'initTabModuleList')) $this->initTabModuleList();
		
	}
	
	public function display()
	{
		$this->display_header = true;
		$this->display_footer = true;
		$this->content_only = false;
		//$this->lite_display = true;
		
		$this->errors = isset($this->_errors) ? $this->_errors : array();
		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
			'title'=>'AwoCoupon',
		));
		parent::display();
	}
	
	
	
		
	

	public function _displayCpanelDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/dashboard.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$model = new AwoCouponModelDashboard();
		
		
		$params = new awoParams();

		$time = $params->get('cache_updatecheck_time', 0);
		if ($time > time()) $check = json_decode($params->get('cache_updatecheck_data'));
		else
		{ 
			$check = $model->getVersionUpdate();
			$params->set('cache_updatecheck_time', time() + (3600 * 72));
			$params->set('cache_updatecheck_data', json_encode($check));
		}
		
		if (awohelper::param_get('version') != $this->awocoupon->version)
		{
		// clean up everything
			$this->awocoupon->version_mismatch(awohelper::param_get('version'));
			$check = $model->getVersionUpdate();
			$params->set('cache_updatecheck_time', time() + (3600 * 72));
			$params->set('cache_updatecheck_data', json_encode($check));
		}
		
		$time = $params->get('cache_deleteexpired_time', 0);
		if ($time > time());
		else
		{ 
			$model->deleteExpiredCoupons();
			$params->set('cache_deleteexpired_time', time() + (3600 * 24));
		}
		

		$url = $this->my_admin_link.'&token='.$this->token;
		$this->context->smarty->assign(array(
			'status' => $check,
			'genstats'=>$model->getGeneralstats(),
			'license'=>$model->getLicense(),
			'url' => $this->my_admin_link,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'img_url'=>AWO_URI.'/media/img',
			'token'=>$this->token,
			
				'show_toolbar' => true,
				'toolbar_btn' => $this->toolbar_btn,
				'toolbar_scroll' => $this->toolbar_scroll,
				'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('cpanel_default.tpl')->fetch();
	}

	
	
	
	
	
	public function _displayCouponDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$this->table = $this->list_id = 'coupondefault';
		
		
		$currentIndex = $this->setcurrentindex('view=coupon');
		
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		//$this->customlistfooter =' &nbsp; <input type="submit" class="button" name="task_generate" value="'.$this->l('Generate Coupons').'" />';
		
		
		$this->display = 'list';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('duplicate');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),
									'task_generate'=>array('text' => $this->l('Generate Coupons'),),
									);
		$this->page_header_toolbar_btn['new_cart_rule'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new coupon', null, null, false),
			'icon' => 'process-icon-new'
		); # _PS_VERSION_ >= 1.6
		
		
		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int',),
			'coupon_code' => array('title' => $this->l('Code'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'function_type' => array('title' => $this->l('Function Type'), 'type' => 'select', 'list' => awoHelper::vars('function_type'), 'filter_key' => 'function_type', 'mytype'=>'select',),
			//'note' => array('title' => $this->l('Description'),),
			'coupon_value_type' => array('title' => $this->l('Type'), 'type' => 'select', 'list' => awoHelper::vars('coupon_value_type'), 'filter_key' => 'coupon_value_type', 'mytype'=>'select',),
			'coupon_value' => array('title' => $this->l('Value'),  'align' => 'right'),
			'startdate' => array('title' => $this->l('From'),  'align' => 'center', 'callback'=>'rawhtml', 'callback_object'=>'AdminAwoCouponController'),
			'expiration' => array('title' => $this->l('To'), 'align' => 'center', 'callback'=>'rawhtml', 'callback_object'=>'AdminAwoCouponController'),
			//'date_to' => array('title' => $this->l('To'), 'type' => 'date', 'align' => 'right'),
			'details' => array('title' => $this->l('Details'), 'align' => '', 'width' => 40,'orderby' => false, 'search' => false, 'callback'=>'rawhtml', 'callback_object'=>'AdminAwoCouponController'),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'list' => awoHelper::vars('published'),'filter_key' => 'published', 'callback'=>'rawhtml', 'callback_object'=>'AdminAwoCouponController', 'mytype'=>'select',)
		);

		$this->real_processFilter();
		
		$model = new AwoCouponModelCoupon();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);
		
		$this->initToolbar();
		$html = $this->renderList();
		
		$this->addjQueryPlugin(array('fancybox'));
		$html = '<script language="javascript" type="text/javascript">
			function coupon_detail(id) {
				jQuery(document).ready(function () {
					jQuery.fancybox({
						"width"				: "75%",
						"height"			: "75%",
						"autoScale"     	: false,
						"transitionIn"		: "none",
						"transitionOut"		: "none",
						"type": "iframe",
						"href": "'.$this->my_admin_link.'&id="+id+"&view=coupondetail&token='.$this->token.'"
					});
				});	
			}
			</script>'.$html;
		
		return $html;
		
	}
	public function _displayCouponGenerateEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$row = (object)array('template'=>'', 'number'=>'');
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post); 
				

		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		$lists['templatelist'] = awoHelper::DD(awoAutoGenerate::getCouponTemplates(), 'template', 'class="inputbox" style="width:250px;"', $row->template, '', 'id', 'coupon_code');		

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=coupon&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('coupon_generateedit.tpl')->fetch();
		
	}
	public function _displayCouponEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoslider.php';
		
		$model = new AwoCouponModelCoupon();
		$row = $model->getEntry();
		
		
		$post = awoHelper::getValues($_POST);
		if (!empty($post))
		{
			if (!empty($post['userlist']))
			{
				$tmp = $post['userlist'];
				$post['userlist'] = array();
				foreach ($tmp as $id) $post['userlist'][$id] = (object)array('user_id'=>$id,'user_name'=>$post['usernamelist'][$id]);
			}
			
			if (!empty($post['assetlist']))
			{
				$tmp = $post['assetlist'];
				$post['assetlist'] = array();
				foreach ($tmp as $id) $post['assetlist'][$id] = (object)array('asset_id'=>$id,'asset_name'=>$post['assetnamelist'][$id]);
			}

			if (!empty($post['assetlist2']))
			{
				$tmp = $post['assetlist2'];
				$post['assetlist2'] = array();
				foreach ($tmp as $id) $post['assetlist2'][$id] = (object)array('asset_id'=>$id,'asset_name'=>$post['asset2namelist'][$id]);
			}

			if (!empty($post['countrylist']))
			{
				$tmp = $post['countrylist'];
				$post['countrylist'] = array();
				foreach ($tmp as $id) $post['countrylist'][$id] = (object)array('asset_id'=>$id);
			}

			if (!empty($post['statelist'])) 
			{
				$tmp = $post['statelist'];
				$post['statelist'] = array();
				foreach ($tmp as $id) $post['statelist'][$id] = (object)array('asset_id'=>$id);
			}

			$row = (object)array_merge((array)$row, (array)$post); //bind the db return and post
		}
		
		
		$slider['start'] = awoJHtmlSliders::start('extra_options', array('closeAll'=>1));
		$slider['panel_customers'] = awoJHtmlSliders::panel($this->l('Customers'), 'pn_user');
		$slider['panel_asset1'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset');
		$slider['panel_asset2'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset2');
		$slider['end'] = awoJHtmlSliders::end();
				
		$slider2['start'] = awoJHtmlSliders::start('extra_options2', array('closeAll'=>0));
		$slider2['panel_asset1'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset');
		$slider2['panel_asset2'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset2');
		$slider2['end'] = awoJHtmlSliders::end();
		
		
		$lists['function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'function_type', 'class="inputbox" style="width:147px;" onchange="funtion_type_change();"', $row->function_type, '');		
		$lists['dd_function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'dd_function_type', 'id="dd_function_type" class="inputbox" style="width:147px;" onchange="document.adminForm.function_type.value=this.value;funtion_type_change();"', $row->function_type, '');		
		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" style="width:147px;"', $row->published);		
		$lists['parent_type'] = awoHelper::DD(awoHelper::vars('parent_type'), 'parent_type', 'class="inputbox" style="width:147px;"', @$row->params->process_type);		
		$lists['buy_xy_process_type'] = awoHelper::DD(awoHelper::vars('buy_xy_process_type'), 'buy_xy_process_type', 'class="inputbox" style="width:147px;"', $row->buy_xy_process_type);		
		$lists['coupon_value_type'] = awoHelper::DD(awoHelper::vars('coupon_value_type'), 'coupon_value_type', 'class="inputbox" style="width:147px;"', $row->coupon_value_type);		
		$lists['discount_type'] = awoHelper::DD(awoHelper::vars('discount_type'), 'discount_type', 'class="inputbox" style="width:147px;" ', $row->discount_type);		
		$lists['min_value_type'] = awoHelper::DD(awoHelper::vars('min_value_type'), 'min_value_type', 'class="inputbox" style="width:100px;"', $row->min_value_type);		
		$lists['min_qty_type'] = awoHelper::DD(awoHelper::vars('min_qty_type'), 'min_qty_type', 'class="inputbox" style="width:100px;"', $row->min_qty_type);		
		$lists['user_type'] = awoHelper::DD(awoHelper::vars('user_type'), 'user_type', 'class="inputbox" style="width:200px;" onchange="user_type_change();"', $row->user_type);		
		$states = array('product'=>'Product',
						'category'=>'Category',
						'manufacturer'=>'Manufacturer',
						'vendor'=>'Vendor',
					);
		$lists['asset1_function_type'] = awoHelper::DD($states, 'asset1_function_type', 'class="inputbox" style="width:147px;" onchange="asset_type_change(1);"', $row->asset1_function_type, '');	
		$lists['asset2_function_type'] = awoHelper::DD($states, 'asset2_function_type', 'class="inputbox" style="width:147px;" onchange="asset_type_change(2);"', $row->asset2_function_type, '');	
		
		$country_list = awohelper::getCountryList();
		$lists['countrylist'] = awoHelper::DD($country_list, 'countrylist[]', 'MULTIPLE class="inputbox" style="width:90%;" ', array_keys($row->countrylist), '', 'country_id', 'country_name');	

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=coupon&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'slider' =>$slider,
			//'slider1' =>$slider1,
			'slider2' =>$slider2,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'is_multistore'=>awoHelper::is_multistore(),
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		$this->addCSS(AWO_URI.'/media/css/select2.css');
		$this->addJS(AWO_URI.'/media/js/select2.min.js');
		$this->addJS(AWO_URI.'/media/js/coupon.js');
		$this->addJS(AWO_URI.'/media/js/coupon_cumulative_value.js');
		return $this->createTemplate('coupon_edit.tpl')->fetch();
		
	}
	public function _displayCouponDetailDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoslider.php';
		$this->ajax = true;
		

		
		$model = new AwoCouponModelCoupon();
		$row = $model->getEntry();
		
		
		$slider['start'] = awoJHtmlSliders::start('extra_options', array('closeAll'=>0));
		if (!empty($row->userlist))
		{
			$user_title = '';
			if ($row->user_type == 'user') $user_title = $this->l('Customers');		
			elseif ($row->user_type == 'usergroup') $user_title = $this->l('Shopper Groups');	
			$slider['panel_customers'] = awoJHtmlSliders::panel($user_title, 'pn_user');
		}
		
		if (!empty($row->shoplist))
			$slider['panel_shops'] = awoJHtmlSliders::panel($this->l('Shop'), 'pn_shop');
		
		if (!empty($row->countrylist) || !empty($row->statelist))
		{
			$title = !empty($row->statelist) ? $this->l('State') : $this->l('Country');
			$title .= ' ('.$this->l(awohelper::vars('asset_mode', empty($row->params->countrystate_mode) ? 'include' : $row->params->countrystate_mode)).')';		
	
			$row->countrystatelist = !empty($row->statelist)  ? $row->statelist : $row->countrylist;
			$slider['panel_countrystate'] = awoJHtmlSliders::panel($title, 'pn_countrystate');
		}
		
		
		$asset1_title = '';
		if ($row->function_type == 'coupon' || $row->function_type == 'giftcert')
		{
			if ($row->asset1_function_type == 'product') $asset1_title = $this->l('Products');		
			elseif ($row->asset1_function_type == 'category') $asset1_title = $this->l('Categories');	
			elseif ($row->asset1_function_type == 'manufacturer') $asset1_title = $this->l('Manufacturers');
			elseif ($row->asset1_function_type == 'vendor') $asset1_title = $this->l('Vendors');
			if (!empty($asset1_title)) $asset1_title .= ' ('.awoHelper::vars('asset_mode', empty($row->asset1_mode) ? 'include' : $row->asset1_mode).')';
		}
		elseif ($row->function_type == 'shipping') $asset1_title = $this->l('Shipping').' ('.awoHelper::vars('asset_mode', $row->asset1_mode).')';
		elseif ($row->function_type == 'parent') $asset1_title = $this->l('Coupons');
		elseif ($row->function_type == 'buy_x_get_y') $asset1_title = $this->l('Buy X').' ('.awoHelper::vars('asset_mode', $row->asset1_mode).' '.awoHelper::vars('asset_type', $row->params->asset1_type).')';	
		$slider['panel_asset1'] = awoJHtmlSliders::panel($asset1_title, 'pn_asset1');

		if (!empty($row->assetlist2))
		{
			$asset2_title = '';
			if ($row->function_type == 'giftcert') $asset2_title = $this->l('Shipping').' ('.awoHelper::vars('asset_mode', $row->asset2_mode).')';
			//elseif ($row->function_type == 'shipping') $asset2_title = $this->l('Products').' ('.awoHelper::vars('asset_mode',$row->asset2_mode).')';
			elseif ($row->function_type == 'buy_x_get_y') $asset2_title = $this->l('Get Y').' ('.awoHelper::vars('asset_mode', $row->params->asset2_mode).' '.awoHelper::vars('asset_type', $row->params->asset2_type).')';
			else
			{
				$lang = 'Products';
				if (!empty($row->params->asset2_type))
				{
					if ($row->params->asset2_type == 'product') $lang = 'Products';
					elseif ($row->params->asset2_type == 'category') $lang = 'Categories';
					elseif ($row->params->asset2_type == 'manufacturer') $lang = 'Manufacturers';
					elseif ($row->params->asset2_type == 'vendor') $lang = 'Vendors';
				}
				$asset2_title = $asset2_title = $this->l($lang).' '.(!empty($row->asset2_mode) ? '('.awoHelper::vars('asset_mode', $row->asset2_mode).')' : '');
			}
			$slider['panel_asset2'] = awoJHtmlSliders::panel($asset2_title, 'pn_asset2');
		}
		$slider['end'] = awoJHtmlSliders::end();
		
		$row->str_coupon_value_type = $row->function_type == 'parent' ? '' : awoHelper::vars('coupon_value_type', $row->coupon_value_type);
		$row->str_discount_type = awoHelper::vars('discount_type', empty($row->discount_type) ? '' : $row->discount_type);
		$row->str_function_type = awoHelper::vars('function_type', $row->function_type);
		$row->str_coupon_value = !empty($row->coupon_value) ? $row->coupon_value: $row->coupon_value_def;
		$row->str_buy_xy_process_type = awoHelper::vars('buy_xy_process_type', empty($row->params->process_type) ? 'abc' : $row->params->process_type);
		if (!empty($row->min_value)) $row->str_min_value = number_format($row->min_value, 2).' '.awoHelper::vars('min_value_type', !empty($row->params->min_value_type) ? $row->params->min_value_type : 'overall');
		if (!empty($row->params->min_qty)) $row->str_min_qty = ((int)$row->min_qty).' '.awoHelper::vars('min_qty_type', !empty($row->params->min_qty_type) ? $row->params->min_qty_type : 'overall');
		$row->str_parent_type = awoHelper::vars('parent_type', empty($row->params->process_type) ? 'abc' : $row->params->process_type);
		if (empty($row->params)) $row->params = array();

		$exclude_str = array();
		if (!empty($row->exclude_special)) $exclude_str[] = $this->l('Specials');
		if (!empty($row->exclude_giftcert)) $exclude_str[] = $this->l('Gift Products');
		if (!empty($exclude_str)) $row->str_exclude = implode(', ', $exclude_str);

//printrx($row);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'slider' => $slider,
			'url_module' => AWO_URI,
		
		));

		return $this->createTemplate('coupon_detaildefault.tpl')->fetch();
		
	}



	public function _displayCouponAutoDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		
		$this->table = $this->list_id = 'couponautodefault';
		
		
		$currentIndex = $this->setcurrentindex('view=couponauto');
		
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		//$this->customlistfooter =' &nbsp; <input type="submit" class="button" name="task_generate" value="'.$this->l('Generate Coupons').'" />';
		
		
		$this->display = 'list';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),);
		$this->page_header_toolbar_btn['new_cart_rule'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new coupon', null, null, false),
			'icon' => 'process-icon-new'
		); # _PS_VERSION_ >= 1.6
		
		
		$this->fields_list = array(
			'coupon_code' => array('title' => $this->l('Code'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'function_type' => array('title' => $this->l('Function Type'), 'type' => 'select', 'list' => awoHelper::vars('function_type'), 'filter_key' => 'function_type', 'mytype'=>'select',),
			'coupon_value_type' => array('title' => $this->l('Type'), 'type' => 'select', 'list' => awoHelper::vars('coupon_value_type'), 'filter_key' => 'coupon_value_type', 'mytype'=>'select',),
			'coupon_value' => array('title' => $this->l('Value'),  'align' => 'right'),
			'ordering' => array('title' => $this->l('Ordering'), 'align' => '', 'width' => 40,'orderby' => true, 'search' => false,),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'list' => awoHelper::vars('published'),'filter_key' => 'published', 'callback'=>'rawhtml', 'callback_object'=>'AdminAwoCouponController', 'mytype'=>'select',)
		);

		$this->real_processFilter();
		
		$model = new AwoCouponModelCouponAuto();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);
		
		$this->initToolbar();
		$html = $this->renderList();
				
		return $html;
		
	}
	public function _displayCouponAutoEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelCouponAuto();
		$row = $model->getEntry();
		
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post);	
		
		$published = awoHelper::vars('published');
		unset($published[-2]);
		$lists['published'] = awoHelper::DD($published, 'published', 'class="inputbox" style="width:147px;"', $row->published);		

		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=couponauto&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'lists' => $lists,
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('couponauto_edit.tpl')->fetch();
	}



	public function _displayGiftcertDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$this->table = $this->list_id = 'giftcertdefault';
		
		$currentIndex = $this->setcurrentindex('view=giftcert');

		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		
		$this->display = 'list';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),);
		$this->page_header_toolbar_btn['new_add'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new', null, null, false),
			'icon' => 'process-icon-new'
		); # version_compare(_PS_VERSION_, '1.6', '>=')

		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int','havingFilter'=>1),
			'product_name' => array('title' => $this->l('Product'),  'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'coupon_code' => array('title' => $this->l('Template'),  'prefix' => '<span class="">', 'suffix' => '</span>'),
			'profile' => array('title' => $this->l('Profile Image'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'codecount' => array('title' => $this->l('Codes'), 'align' => '', 'orderby' => false, 'search' => false,'callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController'),
			'expiration' => array('title' => $this->l('Expiration'), 'align' => '', 'orderby' => false, 'search' => false),
			'vendor_name' => array('title' => $this->l('Vendor'), 'align' => '','callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController'),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'list' => awoHelper::vars('published'),'filter_key' => 'published','callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController','mytype'=>'select','havingFilter'=>1)
		);		
		
		$this->real_processFilter();
		
		$model = new AwoCouponModelGiftCert();
		$this->_list = $model->getEntriesGift($this->my_filters);
		$this->_listTotal = $model->getTotalGift($this->my_filters);
		
		$this->initToolbar();
		$html = $this->renderList();
		
		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Products').'</span> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=codedefault&token='.Tools::getValue('token').'">'.$this->l('Codes').'</a> &nbsp; | &nbsp; 
				</div>'.$html;*/
		
	}
	public function _displayGiftcertEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelGiftCert();
		$row = $model->getEntryGift();
		
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post);	
				
		//Tools::addJS(AWO_URI.'/media/js/coupon_cumulative_value.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery-ui-1.8.10.autocomplete.min.js');
		
		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" style="width:147px;"', $row->published);		
		$lists['expiration_type'] = awoHelper::DD(awoHelper::vars('expiration_type'), 'expiration_type', 'class="inputbox" style="width:147px;"', $row->expiration_type, '');		
		$lists['profilelist'] = awoHelper::DD($model->getProfileList(), 'profile_id', 'class="inputbox" style="width:147px;"', $row->profile_id, '', 'id', 'title');		
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'coupon_template_id', 'class="inputbox" style="width:147px;"', $row->coupon_template_id, '', 'id', 'coupon_code');		

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=giftcert&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
		
			'question_img'=>AWO_URI.'/media/img/question_mark.png',
		
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('giftcert_edit.tpl')->fetch();
		/*return '
			<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Products').'</span> &nbsp; | &nbsp; 
					<a href="'.$this->my_admin_link.'&view=giftcert&layout=codedefault&token='.Tools::getValue('token').'">'.$this->l('Codes').'</a> &nbsp; | &nbsp; 
				</div><br />'.$this->createTemplate('giftcert_edit.tpl')->fetch();*/
	}
	public function _displayGiftcertCodeDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$this->table = $this->list_id = 'giftcertcodedefault';
		
		
		$currentIndex = $this->setcurrentindex('view=giftcert&layout=codedefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->display = 'list';
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),);
		$this->page_header_toolbar_btn['new_add'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new', null, null, false),
			'icon' => 'process-icon-new'
		); # version_compare(_PS_VERSION_, '1.6', '>=')

		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25, 'mytype'=>'int',),
			'product_name' => array('title' => $this->l('Product'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'code' => array('title' => $this->l('Code'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'note' => array('title' => $this->l('Description'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'status' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'list' => awoHelper::vars('status'),'filter_key' => 'status','callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController','mytype'=>'select')
		);		


		$this->real_processFilter();

		$model = new AwoCouponModelGiftCert();
		$this->_list = $model->getEntriesCode($this->my_filters);
		$this->_listTotal = $model->getTotalCode($this->my_filters);
		
		
		$this->initToolbar();
		$html = $this->renderList();
		
		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="'.$this->my_admin_link.'&view=giftcert&token='.Tools::getValue('token').'">'.$this->l('Products').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Codes').'</span> &nbsp; | &nbsp; 
				</div>'.$html;*/
		
	}
	public function _displayGiftcertCodeEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelGiftCert();
		
		$post = awoHelper::getValues($_POST);
				
		$lists['productlist'] = awoHelper::DD($model->getGiftCertProductList(), 'product_id', 'class="inputbox" style="width:147px;"', null, '', 'product_id', 'product_name');		

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=giftcert&layout=codedefault&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'lists' => $lists,
			'exclude_first_row'=>Tools::getValue('exclude_first_row', '1'),
			'store_none_errors'=>Tools::getValue('store_none_errors', '1'),
		
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));


		return $this->createTemplate('giftcert_codeedit.tpl')->fetch();
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="'.$this->my_admin_link.'&view=giftcert&token='.Tools::getValue('token').'">'.$this->l('Products').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Codes').'</span> &nbsp; | &nbsp; 
				</div><br />'.$this->createTemplate('giftcert_codeedit.tpl')->fetch();*/
		
	}



	public function _displayProfileDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$this->table = $this->list_id = 'profiledefault';
		
		$currentIndex = $this->setcurrentindex('view=profile');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->display = 'list';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('duplicate');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),
									'task_default'=>array('text' => $this->l('Default'),),
									);
		$this->page_header_toolbar_btn['new_add'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new', null, null, false),
			'icon' => 'process-icon-new'
		); # version_compare(_PS_VERSION_, '1.6', '>=')

		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int'),
			'title' => array('title' => $this->l('Title'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'from_name' => array('title' => $this->l('From Name'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'from_email' => array('title' => $this->l('From Email'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'bcc_admin' => array('title' => $this->l('Bcc Admin'), 'prefix' => '<span class="">', 'suffix' => '</span>', 'search' => false),
			'email_subject' => array('title' => $this->l('Email Subject'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'message_type' => array('title' => $this->l('Type'), 'prefix' => '<span class="">', 'suffix' => '</span>', 'search' => false),
			'preview' => array('title' => $this->l(''), 'align' => '', 'orderby' => false, 'search' => false,'callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController'),
			'default' => array('title' => $this->l('Default'), 'align' => '', 'orderby' => false, 'search' => false,'callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController'),
		);		

		$this->real_processFilter();
		
		$model = new AwoCouponModelProfile();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);
		
		$this->initToolbar();
		$html = $this->renderList();
		
		$this->addjQueryPlugin(array('fancybox'));
		$html = '<script language="javascript" type="text/javascript">
				jQuery(document).ready(function () {
					$(".awomodal").fancybox({
						"width"				: "75%",
						"height"			: "75%",
						"autoScale"     	: false,
						"transitionIn"		: "none",
						"transitionOut"		: "none",
						"type": "iframe"
					});
				});	
			</script>'.$html;
		return $html;
		
	}
	public function _displayProfileEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelProfile();
		$row = $model->getEntry();
		$font_color     	= $model->getfontcolor();
		$imagedd     	= $model->getimages();
		$fontdd     	= $model->getfonts();
		
		$post = awoHelper::getValues($_POST);
		if ($post)
		{
			$row = (object)array_merge((array)$row, (array)$post); //bind the db return and post
			$text = $_POST['email_html'];
			if (!empty($text))
			{
				$text		= str_replace('<br>', '<br />', $text);
				$row->email_body = $text;
			}
			foreach ($row->imgplugin as $k => $r1) 
				foreach ($r1 as $k2 => $r2) $row->imgplugin[$k][$k2] = (object)$r2;
		}	
					
				
		$tmp = array(''=>$this->l('Do not display'),
					'Y-m-d'=>date('Y-m-d'),
					'm/d/Y'=>date('m/d/Y'),
					'd/m/Y'=>date('d/m/Y'),
					'Y/m/d'=>date('Y/m/d'),
					'd.m.Y'=>date('d.m.Y'),
					'M j Y'=>date('M j Y'),
					'j M Y'=>date('j M Y'),
					'Y M j'=>date('Y M j'),
					'F j Y'=>date('F j Y'),
					'j F Y'=>date('j F Y'),
					'Y F j'=>date('Y F j')
				);
		$lists['expiration_text'] = awoHelper::DD($tmp, 'expiration_text', 'class="inputbox" size="1"', $row->expiration_text);		
		$lists['message_type'] = awoHelper::DD(awoHelper::vars('giftcert_message_type'), 'message_type', 'class="inputbox" onchange="message_type_change()" ', $row->message_type, '');		
		$lists['image'] = awoHelper::DD($imagedd, 'image', 'class="inputbox" onchange="checkimage()" ', $row->image, '---');		
				
		$aligndd = array('L'=>$this->l('Left'),'C'=>$this->l('Middle'),'R'=>$this->l('Right'),);
		$lists['couponcode_align'] = awoHelper::DD($aligndd, 'couponcode_align', 'class="inputbox" size="1"', $row->couponcode_align);		
		$lists['couponvalue_align'] = awoHelper::DD($aligndd, 'couponvalue_align', 'class="inputbox" size="1"', $row->couponvalue_align);		
		$lists['expiration_align'] = awoHelper::DD($aligndd, 'expiration_align', 'class="inputbox" size="1"', $row->expiration_align);		
		$lists['freetext1_align'] = awoHelper::DD($aligndd, 'freetext1_align', 'class="inputbox" size="1"', $row->freetext1_align);		
		$lists['freetext2_align'] = awoHelper::DD($aligndd, 'freetext2_align', 'class="inputbox" size="1"', $row->freetext2_align);		
		$lists['freetext3_align'] = awoHelper::DD($aligndd, 'freetext3_align', 'class="inputbox" size="1"', $row->freetext3_align);		
		
		$lists['couponcode_font'] = awoHelper::DD($fontdd, 'couponcode_font', 'class="inputbox" size="1"', $row->couponcode_font);		
		$lists['couponvalue_font'] = awoHelper::DD($fontdd, 'couponvalue_font', 'class="inputbox" size="1"', $row->couponvalue_font);		
		$lists['expiration_font'] = awoHelper::DD($fontdd, 'expiration_font', 'class="inputbox" size="1"', $row->expiration_font);		
		$lists['freetext1_font'] = awoHelper::DD($fontdd, 'freetext1_font', 'class="inputbox" size="1"', $row->freetext1_font);		
		$lists['freetext2_font'] = awoHelper::DD($fontdd, 'freetext2_font', 'class="inputbox" size="1"', $row->freetext2_font);		
		$lists['freetext3_font'] = awoHelper::DD($fontdd, 'freetext3_font', 'class="inputbox" size="1"', $row->freetext3_font);		
				
		$tmp_color = '';
		foreach ($font_color as $key => $value) $tmp_color .= '<option value="'.$key.'" style="background-color:'.$key.';">'.$value.'</option>';
		$lists['couponcode_font_color'] = '<select name="couponcode_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['couponvalue_font_color'] = '<select name="couponvalue_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['expiration_font_color'] = '<select name="expiration_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext1_font_color'] = '<select name="freetext1_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext2_font_color'] = '<select name="freetext2_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext3_font_color'] = '<select name="freetext3_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		
		
		$yes_no = array('0'=>$this->l('No'),'1'=>$this->l('Yes'),);
		$lists['is_pdf'] = awoHelper::DD($yes_no, 'is_pdf', 'class="inputbox" size="1"', $row->is_pdf);		
		
		
		if (!empty($row->imgplugin))
		{
			foreach ($row->imgplugin as $k => $r1)
			{
				if (!empty($r1))
				{
					foreach ($r1 as $k2 => $r2)
					{
						$lists[$k.'_'.$k2.'_align'] = awoHelper::DD($aligndd, 'imgplugin['.$k.']['.$k2.'][align]', 'class="inputbox" size="1"', @$row->align);		
						$lists[$k.'_'.$k2.'_font'] = awoHelper::DD($fontdd, 'imgplugin['.$k.']['.$k2.'][font]', 'class="inputbox" size="1"', @$row->font);		
						$lists[$k.'_'.$k2.'_font_color'] = '<select name="imgplugin['.$k.']['.$k2.'][font_color]" class="inputbox size="1">'.$tmp_color.'</select>';
					}
				}
			}
		}

		$id_lang = (int)Context::getContext()->language->id;
		$iso = Language::getIsoById((int)($id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER['PHP_SELF']);
		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=profile&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		
		$this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
		$this->addJS(_PS_JS_DIR_.(version_compare(_PS_VERSION_,'1.6.1','>=') ? 'admin/' : '').'tinymce.inc.js');
		$this->context->smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			
			'base_url'=>__PS_BASE_URI__,
			'isoTinyMCE'=>$isoTinyMCE,
			'theme_path'=>_THEME_CSS_DIR_,
			'tiny_ad'=>$ad,
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
			'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
			'languages'=>Language::getLanguages(),
		));
		
		$this->addjQueryPlugin(array('fancybox'));

		return $this->createTemplate('profile_edit.tpl')->fetch();
		
	}
	
	
	
	public function _displayImportDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/import.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$model = new AwoCouponModelImport();
		
		$row = new stdclass;
		$row->exclude_first_row = '1';
		$row->store_none_errors = '1';
		
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post);

		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
		);
		$this->context->smarty->assign(array(
			'row' => $row,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));
		return $this->createTemplate('import_default.tpl')->fetch();


	}

	

	public function _displayHistoryHistDefault()
	{
		return $this->_displayHistoryDefault();
	}
	public function _displayHistoryDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$this->table = $this->list_id = 'historydefault';

		
		$currentIndex = $this->setcurrentindex('view=history');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->display = 'list';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),);
		$this->page_header_toolbar_btn['new_add'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new', null, null, false),
			'icon' => 'process-icon-new'
		); # version_compare(_PS_VERSION_, '1.6', '>=')

		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int','havingFilter'=>1),
			'coupon_code' => array('title' => $this->l('Coupon Code'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'user_id' => array('title' => $this->l('Customer ID'), 'prefix' => '<span class="">', 'suffix' => '</span>','mytype'=>'int'),
			'user_email' => array('title' => $this->l('E-mail'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'lastname' => array('title' => $this->l('Last Name'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'firstname' => array('title' => $this->l('First Name'), 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'discount' => array('title' => $this->l('Discount'), 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'order_number' => array('title' => $this->l('Order ID'),  'callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController','havingFilter'=>1),
			'cdate' => array('title' => $this->l('Order Date'), 'prefix' => '<span class="">', 'suffix' => '</span>', 'search'=>false),
		);		

		$this->real_processFilter();
		
		$model = new AwoCouponModelHistory();
		$this->_list = $model->getEntriesHist($this->my_filters);
		$this->_listTotal = $model->getTotalHist($this->my_filters);
		
		
		$this->initToolbar();
		$html = $this->renderList();
		

		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Coupons').'</span> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div>'.$html;*/
		
	}
	public function _displayHistoryEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelHistory();
		$row = $model->getEntryHist();
		
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post);	
				
		
		$lists['couponlist'] = awoHelper::DD($model->getCouponList(), 'coupon_id', 'class="inputbox" style="width:147px;"', $row->coupon_id, '', 'id', 'coupon_code');		

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=history&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
		
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('history_edit.tpl')->fetch();
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Coupons').'</span> &nbsp; | &nbsp; 
					<a href="'.$this->my_admin_link.'&view=history&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<a href="'.$this->my_admin_link.'&view=history&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div><br />'.$this->createTemplate('history_edit.tpl')->fetch();*/
	}
	public function _displayHistoryGiftDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$this->table = $this->list_id = 'historygiftdefault';
	
		
		$currentIndex = $this->setcurrentindex('view=history&layout=giftdefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->fields_list = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int','havingFilter'=>1),
			'coupon_code' => array('title' => $this->l('Gift Certificate'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'coupon_value' => array('title' => $this->l('Value'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'coupon_value_used' => array('title' => $this->l('Value Used'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'balance' => array('title' => $this->l('Balance'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'expiration' => array('title' => $this->l('Expiration'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
		);		

		$this->real_processFilter();
		
		$model = new AwoCouponModelHistory();
		$this->_list = $model->getEntriesGift($this->my_filters);
		$this->_listTotal = $model->getTotalGift($this->my_filters);

		$this->initToolbar();
		$html = $this->renderList();
		

		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="'.$currentIndex.'&token='.$this->token.'">'.$this->l('Coupons').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Gift Certificates').'</span> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div><br /><br />'.$html;*/
		
	}
	public function _displayhistoryorder()
	{
		return $this->_displayHistoryOrderDefault();
	}
	public function _displayHistoryOrderDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
	
		$this->table = $this->list_id = 'historyorderdefault';
		
		
		
		$this->identifier = 'id';
		$this->display = 'list'; // adds the ADD icon at top
		$this->page_header_toolbar_btn['new_add'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $this->l('Add new', null, null, false),
			'icon' => 'process-icon-new'
		); # version_compare(_PS_VERSION_, '1.6', '>=')



		$currentIndex = $this->setcurrentindex('view=history&layout=orderdefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->fields_list = array(
			//'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int'),
			'order_id' => array('title' => $this->l('Order ID'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','mytype'=>'int'),
			'codes' => array('title' => $this->l('Codes'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'button' => array('title' => $this->l(''),'align' => 'right', 'width' => 5, 'prefix' => '<span class="">', 'suffix' => '</span>','search'=>false,'orderby'=>false,'callback'=>'rawhtml','callback_object'=>'AdminAwoCouponController',),
		);		

		$this->real_processFilter();
		
		$model = new AwoCouponModelHistory();
		$this->_list = $model->getEntriesOrder($this->my_filters);
		$this->_listTotal = $model->getTotalOrder($this->my_filters);
		
		$this->initToolbar();
		$html = $this->renderList();
		
		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="'.$currentIndex.'&token='.$this->token.'">'.$this->l('Coupons').'</a> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Orders').'</span> &nbsp; | &nbsp; 
				</div><br /><br />
				'.$html;*/
		
	}
	public function _displayHistoryOrderEdit()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelHistory();
		$row = $model->getEntryOrder();
		
		$post = awoHelper::getValues($_POST);
		if ($post) $row = (object)array_merge((array)$row, (array)$post);	
				
		
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'coupon_template_id', 'class="inputbox" size="1""', null, '- '.$this->l('Select Template').' -', 'id', 'coupon_code');

		
		$this->display = 'add';
		$this->toolbar_btn = array(
			'save'=>array('href' => '#','desc' => $this->l('Save')),
			'back'=>array('href' => $this->my_admin_link.'&view=history&layout=order&token='.$this->token, 'desc' => $this->l('Back to list')),
		);
		$this->context->smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
		
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('historyorder_edit.tpl')->fetch();
	}

	
	
	public function _displayReportDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		
		$model = new AwoCouponModelReport();
		

		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" size="1"', null, '- '.$this->l('Select Status').' -');
		$lists['coupon_value_type'] = awoHelper::DD(awoHelper::vars('coupon_value_type'), 'coupon_value_type', 'class="inputbox" size="1"', null, '- '.$this->l('Select Percent or Amount').' -');
		$lists['discount_type'] = awoHelper::DD(awoHelper::vars('discount_type'), 'discount_type', 'class="inputbox" size="1""', null, '- '.$this->l('Select Discount Type').' -');
		$lists['function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'function_type', 'class="inputbox" size="1""', null, '- '.$this->l('Select Function Type').' -');		
		$lists['giftcert_product'] = awoHelper::DD($model->getGiftCertProducts(), 'giftcert_product', 'class="inputbox" size="1""', null, '- '.$this->l('Select Gift Certificate Product').' -', 'product_id', 'product_name');
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'templatelist', 'class="inputbox" size="1""', null, '- '.$this->l('Select Template').' -', 'id', 'coupon_code');
		$lists['order_status'] = awoHelper::DD($model->getOrderstatuses(), 'order_status[]', 'class="inputbox" size="5" MULTIPLE style="width:350px;"', null, null, 'id_order_state', 'name');
		$shoplist = $model->getShopList();
		$lists['shop_list'] = empty($shoplist) ? '' : awoHelper::DD($shoplist, 'shoplist', 'class="inputbox" size="1" ', null, '- '.$this->l('Select Shop').' -', 'id_shop', 'name');

		$this->display = 'add';
		$this->toolbar_btn = array(
			'run'=>array('href' => '#','desc' => $this->l('Run Report')),
		);

		$this->context->smarty->assign(array(
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('report_default.tpl')->fetch();

	}
	public function _displayreportcouponlist()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'coupon_list';
		
		$labels = array('~ID',$this->l('Coupon Code'),$this->l('Published'),$this->l('Function Type'),
			$this->l('Percent or Amount'),$this->l('Discount Type'),
			$this->l('Value'),$this->l('Value Definition'),
			$this->l('Number of Uses Total'),$this->l('Number of uses Per'),
			$this->l('Minumum Value'),$this->l('Minimum Value'),
			$this->l('Start Date'),$this->l('Expiration'),
			$this->l('Customers'),$this->l('Customers'),
	
	
			$this->l('Asset - Type'),$this->l('Asset'),$this->l('Asset - Number'),$this->l('Asset'),
			$this->l('Asset 2 - Type'),$this->l('Asset 2'),$this->l('Asset 2 - Number'),$this->l('Asset 2'),
			$this->l('Exclude Special'),$this->l('Exclude Gift Certificate'),$this->l('Admin Note'),
			$this->l('Process Type'),$this->l('Maximum Qty Discount'),$this->l('Do not Mix Products'),
			$this->l('Automatically add to cart "Get Y" product'),
			$this->l('Country/State'),$this->l('Country'),$this->l('State'),
			$this->l('Minimum Product Quantity'),$this->l('Minimum Product Quantity'),
			$this->l('Description'),$this->l('Tags'),
			
	
			$this->l('Secret Key'),$this->l('Customers'),$this->l('Asset'),$this->l('Asset2'),$this->l('Country'),$this->l('State'),
		);
		$columns = array('id','coupon_code','str_published','str_function_type',
			'str_coupon_value_type','str_discount_type',
			'str_coupon_value','coupon_value_def',
			'num_of_uses_total','num_of_uses_percustomer',
			'str_min_value_type','str_min_value',
			'str_startdate','str_expiration',
			'str_user_type','str_userlist',
			
			
			'str_asset1_type','str_asset1_mode','str_asset1_qty','str_asset',
			'str_asset2_type','str_asset2_mode','str_asset2_qty','str_asset2',
			'str_exclude_special','str_exclude_giftcert','str_note',
			'str_process_type','str_max_discount_qty','str_product_match','str_addtocart',
			'str_countrystate_mode','str_countrylist','str_statelist',
			'str_min_qty_type','str_min_qty','str_description','str_tags',

			
			'passcode','str_userliststr','str_assetstr','str_assetstr2','str_countryliststr','str_stateliststr',);

		$row = $model->getData($report_type);
		$arrstr = array();
		if (!empty($row->rows)) 
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			
		}
		
		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
			
		));

		return $this->createTemplate('report_runCouponList.tpl')->fetch();
	}
	public function _displayreportpurchasedgiftcertlist()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'purchased_giftcert_list';
	
		$labels = array($this->l('Coupon Code'),$this->l('Product'),$this->l('Value'),$this->l('Expiration'),
					$this->l('CustomerID'),$this->l('Last Name'),$this->l('First Name'),$this->l('Email'),
					$this->l('order ID'),$this->l('Order Date'),$this->l('Order Total'),);
		$columns = array('coupon_code','product_name','coupon_valuestr','expiration','user_id','last_name','first_name','email','order_number','order_date','order_total',);

		$row = $model->getData($report_type);
		$arrstr = array();
		if (!empty($row->rows))
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			
		}
		
		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'start_date'=>Tools::getValue('start_date'),
			'end_date'=>Tools::getValue('end_date'),
			'order_status'=>Tools::getValue('order_status'),
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
			
		));

		return $this->createTemplate('report_runPurchasedGiftcert.tpl')->fetch();
	}
	public function _displayreportcouponvstotal()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'coupon_vs_total';

		$labels = array($this->l('Coupon Code'),$this->l('Discount'),$this->l('Revenue'),$this->l('Volume'), '% '.$this->l('Revenue'), '% '.$this->l('Volume'));
		$columns = array('coupon_code','discountstr','totalstr','count','alltotal','allcount');

		$row = $model->getData($report_type);
		$arrstr = array();
		$barvolume = $barrevenue = '';
		if (!empty($row->rows))
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			$barvolume = $barrevenue = '';
			//echo '<pre>'; print_r($row); exit;
			foreach ($row->rows as $r)
			{
				$barvolume .= '<tr><td width="100">'.$r['coupon_code'].'</td>
							<td width="300" class="bar"><div style="width: '.round($r['count'] / $row->count * 100).'%"></div>'.$r['count'].'</td>
							<td>'.round($r['count'] / $row->count * 100).'%</td>
					</tr>';
				$barrevenue .= '<tr><td width="100">'.$r['coupon_code'].'</td>
							<td width="300" class="bar"><div style="width: '.round($r['total'] / $row->total * 100).'%"></div>'.number_format($r['total'], 2).'</td>
							<td>'.round($r['total'] / $row->total * 100).'%</td>
					</tr>';
			}
			
		}
				
		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'start_date'=>Tools::getValue('start_date'),
			'end_date'=>Tools::getValue('end_date'),
			'order_status'=>Tools::getValue('order_status'),
			'barvolume'=>$barvolume,
			'barrevenue'=>$barrevenue,
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
			
		));

		return $this->createTemplate('report_runUsageTotal.tpl')->fetch();
	}
	public function _displayreportcouponvslocation()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'coupon_vs_location';

		$labels = array($this->l('Coupon Code'),$this->l('Country'),$this->l('State'),$this->l('City'),$this->l('Discount'),
							$this->l('Total'),$this->l('Count'), '% '.$this->l('Total'), '% '.$this->l('Count'));
		$columns = array('coupon_code','country','state','city','discountstr','totalstr','count','alltotal','allcount');

		$row = $model->getData($report_type);
		$arrstr = array();
		if (!empty($row->rows))
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			
		}

		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'start_date'=>Tools::getValue('start_date'),
			'end_date'=>Tools::getValue('end_date'),
			'order_status'=>Tools::getValue('order_status'),
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('report_runUsageLocation.tpl')->fetch();
	}
	public function _displayreporthistoryusescoupons()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'history_uses_coupons';
	
	
		$labels = array($this->l('Coupon Code'),'ID',
					$this->l('Last Name'),$this->l('First Name'),$this->l('Discount'),
					$this->l('Order ID'),$this->l('Order Date'),$this->l('Order Total'),
					$this->l('Sub Total'),$this->l('Shipping'),$this->l('Fee'));
		$columns = array('coupon_code_str','user_id','last_name','first_name','discountstr','order_number','order_date',
						'total_paid','total_products_wt','total_shipping','order_fee');

		$row = $model->getData($report_type);
		$arrstr = array();
		if (!empty($row->rows))
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			
		}

		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'start_date'=>Tools::getValue('start_date'),
			'end_date'=>Tools::getValue('end_date'),
			'order_status'=>Tools::getValue('order_status'),
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('report_runHistoryCoupon.tpl')->fetch();
	}
	public function _displayreporthistoryusesgiftcerts()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$this->ajax = true;
		
		$model = new AwoCouponModelReport();
		$report_type = 'history_uses_giftcerts';

		$labels = array($this->l('Coupon Code'),$this->l('Product'),$this->l('Value'),
					$this->l('Value Used'),$this->l('Balance'),$this->l('Expiration'),
					$this->l('Customer ID'),$this->l('Last Name'),$this->l('First Name'),
					$this->l('Order ID'),$this->l('Order Date'),);
		$columns = array('coupon_code','product_name','coupon_valuestr','coupon_value_usedstr','balancestr','expiration','user_id','last_name','first_name','order_number','order_date',);

		$row = $model->getData($report_type);
		$arrstr = array();
		if (!empty($row->rows))
		{
			$style = null;
			$arrstr = $this->reportgrid('grid', $row->rows, $labels, $columns, $style);
			
		}
		
		$this->context->smarty->assign(array(
			'report_type'=>$report_type,
			'row'=>$row,
			'parameters' =>$this->getUserParameters(),
			'pagination' => $model->getPagination(),
			'arrstr' => $arrstr,
			'is_empty' => !empty($arrstr) ? '' : '1',
			'awo_uri'=>AWO_URI,
			
			'start_date'=>Tools::getValue('start_date'),
			'end_date'=>Tools::getValue('end_date'),
			'order_status'=>Tools::getValue('order_status'),
			
			'labels'=>htmlentities(json_encode($labels)),
			'columns'=>htmlentities(json_encode($columns)),
			
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
		));

		return $this->createTemplate('report_runHistoryGiftcert.tpl')->fetch();
	}
	


	public function _displayAboutDefault()
	{
		$this->context->smarty->assign(array(
			'version' => $this->awocoupon->version,
			'url_awo'=>AWO_URI,
			
				'show_toolbar' => true,
				'toolbar_btn' => $this->toolbar_btn,
				'toolbar_scroll' => $this->toolbar_scroll,
				'title' => $this->breadcrumbs,
		));
		return $this->createTemplate('about.tpl')->fetch();
	}


	
	public function _displayConfigDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/config.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$model = new AwoCouponModelConfig();
		$row = $model->getEntry();

		$yes_no = array(0=>$this->l('No'),1=>$this->l('Yes'));
		$lists['enable_store_coupon'] = awoHelper::DD($yes_no, 'params[enable_store_coupon]', 'class="inputbox" size="1"', $row->enable_store_coupon);
		$lists['enable_giftcert_discount_before_tax'] = awoHelper::DD($yes_no, 'params[enable_giftcert_discount_before_tax]', 'class="inputbox" size="1"', $row->enable_giftcert_discount_before_tax);
		$lists['enable_coupon_discount_before_tax'] = awoHelper::DD($yes_no, 'params[enable_coupon_discount_before_tax]', 'class="inputbox" size="1"', $row->enable_coupon_discount_before_tax);
		$lists['enable_multiple_coupon'] = awoHelper::DD($yes_no, 'params[enable_multiple_coupon]', 'class="inputbox" size="1" ', $row->enable_multiple_coupon);		
		$lists['casesensitive'] = awoHelper::DD($yes_no, 'casesensitive', 'class="inputbox" size="1" ', $row->casesensitive);
		$lists['giftcert_vendor_enable'] = awoHelper::DD($yes_no, 'params[giftcert_vendor_enable]', 'class="inputbox" size="1" ', $row->giftcert_vendor_enable);		
		$lists['enable_frontend_image'] = awoHelper::DD($yes_no, 'params[enable_frontend_image]', 'class="inputbox" size="1" ', isset($row->enable_frontend_image) ? $row->enable_frontend_image : '');		
		$lists['giftcert_coupon_activate'] = awoHelper::DD($yes_no, 'params[giftcert_coupon_activate]', 'class="inputbox" size="1" ', isset($row->giftcert_coupon_activate) ? $row->giftcert_coupon_activate : '');		
		
		$lists['csvDelimiter'] = awoHelper::DD(array(','=>',',';'=>';'), 'params[csvDelimiter]', 'class="inputbox" size="1" ', $row->csvDelimiter);		

		$id_lang = (int)Context::getContext()->language->id;
		$iso = Language::getIsoById((int)($id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER['PHP_SELF']);

		$this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
		$this->addJS(_PS_JS_DIR_.(version_compare(_PS_VERSION_,'1.6.1','>=') ? 'admin/' : '').'tinymce.inc.js');
		$this->context->smarty->assign(array(
			'row' => $row,
			'lists' => $lists,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			
			'base_url'=>__PS_BASE_URI__,
			'isoTinyMCE'=>$isoTinyMCE,
			'theme_path'=>_THEME_CSS_DIR_,
			'tiny_ad'=>$ad,
		
			'show_toolbar' => true,
			'toolbar_btn' => $this->toolbar_btn,
			'toolbar_scroll' => $this->toolbar_scroll,
			'title' => $this->breadcrumbs,
			'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
			'languages'=>Language::getLanguages(),
		));

		return $this->createTemplate('config_default.tpl')->fetch();

	}

	
	
	public function _displayAjaxDefault()
	{
		$this->ajax = true;
		$method = Tools::getValue('task');
		
				
		switch ($method)
		{
			case 'ajax_generate_coupon_code': {
				echo awoHelper::generate_coupon_code();
				exit;
				break;
			}
			
			case 'ajax_tags': {
				$output = array();
				$sql = 'SELECT DISTINCT tag FROM #__awocoupon_tag ORDER by tag';
				$dbresults = awoHelper::loadObjectList($sql);
				foreach($dbresults as $r) $output[] = $r->tag;
				echo json_encode($output);
				exit;
			}
				
				
			case 'ajax_elements': {
				$q = Tools::getValue('term');
				//trigger_error(print_r($_GET,1));
				if (empty($q) || strlen($q) < 2) exit;

				$type = Tools::getValue('type');
				
				$result = array();
				$dbresults = array();
				$id_lang = (int)Context::getContext()->language->id;
				switch ($type)
				{
					case 'shop':
						$sql = 'SELECT id_shop AS id,name AS label 
								  FROM '._DB_PREFIX_.'shop
								 WHERE 1=1 AND active=1 AND deleted=0
								 AND name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,id
								 LIMIT 25';
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'product':
						$sql = 'SELECT p.id_product AS id,lang.name AS label 
								  FROM '._DB_PREFIX_.'product p
								  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
								 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$id_lang.'"
								 AND lang.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,p.id_product
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'productgift':
						$sql = 'SELECT p.id_product AS id,lang.name AS label 
								  FROM '._DB_PREFIX_.'product p
								  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
								  LEFT JOIN '._DB_PREFIX_.'awocoupon_giftcert_product g ON g.product_id=p.id_product
								 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$id_lang.'"
								 AND g.product_id IS NULL
								 AND lang.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,p.id_product
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'category':
						$sql = 'SELECT c.id_category AS id,lang.name AS label 
								  FROM '._DB_PREFIX_.'category c
								  JOIN `'._DB_PREFIX_.'category_lang` as lang using (`id_category`)
								 WHERE 1=1 AND c.active=1 AND lang.id_lang="'.(int)$id_lang.'"
								 AND lang.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,c.id_category
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'manufacturer':
						$sql = 'SELECT m.id_manufacturer AS id,m.name AS label 
								  FROM '._DB_PREFIX_.'manufacturer m
								 WHERE 1=1 AND m.active=1
								 AND m.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,m.id_manufacturer
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'vendor':
						$sql = 'SELECT s.id_supplier AS id,s.name AS label 
								  FROM '._DB_PREFIX_.'supplier s
								 WHERE 1=1 AND s.active=1
								 AND s.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,s.id_supplier
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'shipping':
						$sql = 'SELECT s.id_carrier AS id, s.name AS label
								  FROM '._DB_PREFIX_.'carrier s
								 WHERE s.active=1 AND deleted=0
								 AND s.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,id
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'user': 
						$sql = 'SELECT u.id_customer as id,CONCAT(u.lastname," ",u.firstname) AS label
								  FROM '._DB_PREFIX_.'customer u 
								 WHERE 1=1 AND u.active=1 AND u.deleted=0
								 AND CONCAT(u.lastname," ",u.firstname) LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,id
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					
					case 'usergroup': 
						$sql = 'SELECT g.id_group as id,g_l.name as label
								  FROM '._DB_PREFIX_.'group g
								  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=g.id_group
								 WHERE 1=1 AND g_l.id_lang="'.(int)$id_lang.'"
								 AND g_l.name LIKE "%'.awoHelper::escape(strtolower($q)).'%"
								 ORDER BY label,id
								 LIMIT 25';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'parent':
						$sql = 'SELECT id,coupon_code AS label
								  FROM '._DB_PREFIX_.'awocoupon
								 WHERE published=1 AND function_type!="parent" AND LOWER(coupon_code) LIKE "%'.awoHelper::escape(strtolower($q)).'%" ORDER BY label,id LIMIT 25';
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'coupons':
						$sql = 'SELECT id,coupon_code AS label
								  FROM '._DB_PREFIX_.'awocoupon
								 WHERE published=1 AND LOWER(coupon_code) LIKE "%'.awoHelper::escape(strtolower($q)).'%" ORDER BY label,id LIMIT 25';
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
				}
				if (!empty($dbresults))
					foreach ($dbresults as $row) array_push($result, array('id'=>$row->id, 'label'=>$row->label, 'value' => strip_tags($row->label)));

				echo json_encode($result);
				exit;	
				break;
			}
			
			
			case 'ajax_elements_all': {
				$type = Tools::getValue('type');
				
				$result = array();
				$dbresults = array();
				$id_lang = (int)Context::getContext()->language->id;
				switch ($type)
				{
					case 'shop':
						$sql = 'SELECT id_shop AS id,name AS label 
								  FROM '._DB_PREFIX_.'shop
								 WHERE 1=1 AND active=1 AND deleted=0
								 ORDER BY label,id';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'product':
						$sql = 'SELECT p.id_product AS id,lang.name AS label 
								  FROM '._DB_PREFIX_.'product p
								  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
								 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$id_lang.'"
								 ORDER BY label,p.id_product';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'category':
						$sql = 'SELECT c.id_category AS id,lang.name AS label 
								  FROM '._DB_PREFIX_.'category c
								  JOIN `'._DB_PREFIX_.'category_lang` as lang using (`id_category`)
								 WHERE 1=1 AND c.active=1 AND lang.id_lang="'.(int)$id_lang.'"
								 ORDER BY label,c.id_category';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'manufacturer':
						$sql = 'SELECT m.id_manufacturer AS id,m.name AS label 
								  FROM '._DB_PREFIX_.'manufacturer m
								 WHERE 1=1 AND m.active=1
								 ORDER BY label,m.id_manufacturer';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'vendor':
						$sql = 'SELECT s.id_supplier AS id,s.name AS label 
								  FROM '._DB_PREFIX_.'supplier s
								 WHERE 1=1 AND s.active=1
								 ORDER BY label,s.id_supplier';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'shipping':
						$sql = 'SELECT s.id_carrier AS id, s.name AS label
								  FROM '._DB_PREFIX_.'carrier s
								 WHERE s.active=1 AND deleted=0
								 ORDER BY label,id';
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'user': 
						$sql = 'SELECT u.id_customer as id,CONCAT(u.lastname," ",u.firstname) AS label
								  FROM '._DB_PREFIX_.'customer u 
								 WHERE 1=1 AND u.active=1 AND u.deleted=0
								 ORDER BY label,id';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'usergroup': 
						$sql = 'SELECT g.id_group as id,g_l.name as label
								  FROM '._DB_PREFIX_.'group g
								  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=g.id_group
								 WHERE 1=1 AND g_l.id_lang="'.(int)$id_lang.'"
								 ORDER BY label,id';//trigger_error($sql);
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'parent':
						$sql = 'SELECT id,coupon_code AS label
								  FROM '._DB_PREFIX_.'awocoupon
								 WHERE published=1 AND function_type!="parent" 
								 ORDER BY label,id';
						$dbresults = awoHelper::loadObjectList($sql, 'id');
						break;
					case 'countrystate':
						$country_ids = Tools::getValue('country_id');
						foreach ($country_ids as $country_id) 
						{
							$country_id = (int)$country_id;
							if ($country_id > 0)
								$result[$country_id] = awohelper::getCountryStateList($country_id);
						}
						break;
				}
				if (!empty($dbresults))
					foreach ($dbresults as $row) array_push($result, array('id'=>$row->id, 'label'=>$row->label, 'value' => strip_tags($row->label)));
				
				echo json_encode($result);
				exit;
				break;
			}
			
			
			case 'previewprofileid': {
				$profile_id = Tools::getValue('id');
				$image = awoHelper::writeToImage('ABSIE@SD12bSeA', '$25.00', 1462304000, 'screen', null, $profile_id);
				if ($image === false) exit;
				header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Content-type: image/png');
				if ($image === false) echo 'error';
				else
				{
					imagepng($image);					// save image to file
					imagedestroy($image);				// destroy resource
				}
				exit;
			}
			
			case 'previewprofileEdit': {
				$profile = array();
				
				$get = awoHelper::getValues($_GET);
				
					
				$profile['image'] = $get['image'];
				$profile['message_type'] = 'html';
				if (empty($profile['image'])) exit;
			
				list($x1,$x2,$x3,$x4,$x5,$x6) = explode('|', $get['code']);
				$profile['coupon_code_config'] = array('align'=>$x1,'pad'=>$x2,'y'=>$x3,'font'=>$x4,'size'=>$x5,'color'=>$x6,);
				list($x1,$x2,$x3,$x4,$x5,$x6) = explode('|', $get['value']);
				$profile['coupon_value_config'] = array('align'=>$x1,'pad'=>$x2,'y'=>$x3,'font'=>$x4,'size'=>$x5,'color'=>$x6,);
				
				if (!empty($get['expiration']))
				{
					list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|', $get['expiration']);
					$profile['expiration_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
				}
				if (!empty($get['freetext1']))
				{
					list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|', $get['freetext1']);
					$profile['freetext1_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
				}
				if (!empty($get['freetext2']))
				{
					list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|', $get['freetext2']);
					$profile['freetext2_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
				}
				if (!empty($get['freetext3']))
				{
					list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|', $get['freetext3']);
					$profile['freetext3_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
				}
				
				if (!empty($get['imgplugin']) && is_array($get['imgplugin']))
				{
					foreach ($get['imgplugin'] as $k => $r)
					{
						foreach ($r as $k2 => $r2)
						{
							list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|', $r2);
							$profile['imgplugin'][$k][$k2] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'padding'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
						}
					}
				}

				$image = awoHelper::writeToImage('ABSIE@SD12bSeA', '$25.00', 1462304000, 'screen', $profile);
				header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Content-type: image/png');
				if ($image === false) echo 'error';
				else
				{
					imagepng($image);					// save image to file
					imagedestroy($image);				// destroy resource
				}
				exit;
			}

			case 'exportreports': {
			//exit('export to excel');
			
				
				require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
				$model = new AwoCouponModelReport();
				$post = awoHelper::getValues($_POST);

				$file = $model->export($post);

				if (!empty($file))
				{
					$filename = Tools::getValue('filename', 'file.csv');
					
					// required for IE, otherwise Content-disposition is ignored
					if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

					//  default: $ctype="application/force-download";
					header('Pragma: public'); // required
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', false); // required for certain browsers 
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment; filename=\"".$filename."\";');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: '.strlen($file));
					echo $file;
					exit();
				}
				
				break;
			}

			
			default:
			
		}

		exit;
		

		
	}
	
	
	
	public function _displayLicenseDefault()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelLicense();
		$row = $model->getData();
					
		
		$this->display = 'add';
		$this->toolbar_btn = array();
		

		$this->context->smarty->assign(array(
			'row'=>$row,
			'ajax_url'=>$this->my_admin_link.'&view=ajax&token='.$this->token,
			'current_time'=>time(),
			
			'base_url'=>__PS_BASE_URI__,
		
				'show_toolbar' => true,
				'toolbar_btn' => $this->toolbar_btn,
				'toolbar_scroll' => $this->toolbar_scroll,
				'title' => $this->breadcrumbs,
		));


		return $this->createTemplate('license_edit.tpl')->fetch();
		
	}
		
		
	
	
	public function _task($view, $layout, $task)
	{
		if ($view == 'ajax') return;

		$function = '_task'.$view.$layout.$task;
		if (method_exists($this, $function))
		{
			$this->$function();
			return;
		}
		
		$item = '';
		if ($layout != 'default')
		{
			$item = $layout;
			if (substr($item, -4) == 'edit') $item = substr($item, 0, -4);
			elseif (substr($item, -7) == 'default') $item = substr($item, 0, -7);
		}		
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/'.strtolower($view).'.php';
		$post = awoHelper::getValues($_REQUEST);
		$class = 'AwoCouponModel'.$view;
		$model = new $class();

		$clean = $conf = '';
		switch (strtolower($task))
		{
			case 'store':
				$conf = 3;
				$clean = method_exists($model, 'store'.$item) ? $model->{'store'.$item}($post) : $model->store($post);
				break;
				
			case 'publish':
				$conf = 5;
				$clean = method_exists($model, 'publish'.$item) ? $model->{'publish'.$item}($post,1) : $model->publish($post, 1);
				//$clean = $model->publish($post,1);
				break;
				
			case 'unpublish':
				$conf = 5;
				$clean = method_exists($model, 'publish'.$item) ? $model->{'publish'.$item}($post,-1) : $model->publish($post, -1);
				//$clean = $model->publish($post,-1);
				break;
				
			case 'delete':
				$ids = isset($post['submitBulkdelete'.$view.$layout]) ? $post[$view.$layout.'Box'] : (isset($post['submitBulkdelete']) ? $post['Box'] : array($post['id']));
				$conf = 1;
				$clean = method_exists($model, 'delete'.$item) ? $model->{'delete'.$item}($ids,-1) : $model->delete($ids, -1);
				//$clean = $model->delete($ids,-1);
				break;
		}
		
		if ($clean)  Tools::redirectAdmin($this->my_admin_link.'&view='.$view.'&layout='.$item.'default&conf='.$conf.'&token='.$this->token);
		else $this->_errors = $model->_errors;
	}


	public function _taskcpaneldefaultorderaddvoucher()
	{
		$coupon_code = trim(Tools::getValue('coupon_code'));
		if (empty($coupon_code))
		{
			$this->_errors[] = 'No coupon code entered';
			return;
		}
		$order = new Order((int)Tools::getValue('id_order'));
		if (empty($order->id))
		{
			$this->_errors[] = 'Order not found';
			return;
		}
		$cart = new Cart($order->id_cart);
		if (empty($cart->id))
		{
			$this->_errors[] = 'Cart not found';
			return;
		}
		
		$code_id = CartRule::getIdByCode($coupon_code);
		$code_id = (int)substr($code_id, 10);
		if (empty($code_id))
		{
			$this->_errors[] = 'Coupon code not found';
			return;
		}
		
		$test = awohelper::loadResult('SELECT id FROM #__awocoupon_history WHERE (coupon_id='.$code_id.' || coupon_entered_id='.$code_id.') AND order_id='.$order->id);
		if (!empty($test))
		{
			$this->_errors[] = 'Coupon code already used in order';
			return;
		}
		

		
		// add coupon to awocoupon_cart
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE `id_cart` = '.(int)($cart->id).' LIMIT 1');
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'awocoupon_cart` (new_ids,id_cart) VALUES("",'.(int)($cart->id).')');
		//Db::getInstance()->AutoExecute(_DB_PREFIX_.'awocoupon_cart', array('new_ids' => '', 'id_cart' => (int)($cart->id)), 'INSERT');

		// get current AwoCoupon Discounts and process
		$old_coupons = awohelper::loadObjectList('SELECT c.coupon_code FROM #__awocoupon c JOIN #__awocoupon_history h ON h.coupon_entered_id=c.id AND h.order_id='.$order->id);
		foreach ($old_coupons as $c)
		{
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'awocoupon_cart` SET new_ids="'.pSQL($c->coupon_code).'" WHERE `id_cart` = '.(int)($cart->id).' LIMIT 1');
			require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
			$coupon_session = AwoCouponCouponHandler::process_coupon_code($cart);
		}
		
		// process new coupon
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'awocoupon_cart` SET new_ids="'.pSQL($coupon_code).'" WHERE `id_cart` = '.(int)($cart->id).' LIMIT 1');
		require_once _PS_MODULE_DIR_.'awocoupon/lib/couponhandler.php';
		$coupon_session = AwoCouponCouponHandler::process_coupon_code($cart);
	
		if (!empty($coupon_session))
		{
			// delete old order cart rule
			awohelper::query('DELETE FROM #__order_cart_rule WHERE id_order='.$order->id);
			
			// Create OrderCartRule
			$order_cart_rule = new OrderCartRule();
			$order_cart_rule->id_order = $order->id;
			$order_cart_rule->id_cart_rule = 0;
			$order_cart_rule->id_order_invoice = 0;
			$order_cart_rule->name = $coupon_code;
			$order_cart_rule->value = $coupon_session['product_discount'] + $coupon_session['shipping_discount'];
			$order_cart_rule->value_tax_excl = $coupon_session['product_discount_notax'] + $coupon_session['shipping_discount_notax'];
			$order_cart_rule->add();

			// update the order with the discount
			$order->total_discounts = $order_cart_rule->value;
			$order->total_discounts_tax_incl = $order_cart_rule->value;
			$order->total_discounts_tax_excl = $order_cart_rule->value_tax_excl;
			$order->total_paid = $order->total_products_wt + $order->total_shipping + $order->total_wrapping - $order_cart_rule->value;
			$order->total_paid_tax_incl = $order->total_products_wt + $order->total_shipping_tax_incl + $order->total_wrapping_tax_incl - $order_cart_rule->value;
			$order->total_paid_tax_excl = $order->total_products + $order->total_shipping_tax_excl + $order->total_wrapping_tax_excl - $order_cart_rule->value_tax_excl;
			$order->update();
			
			// delete from awocoupon history
			awohelper::query('DELETE FROM #__awocoupon_history WHERE order_id='.$order->id);
			
			// re-add to awocoupon history
			AwoCouponCouponHandler::remove_coupon_code($cart->id, $order->id);

		}
		else
		{
			$this->_errors[] = 'No coupon code was processed for this order';
			return;
		}
		
		
		// remove coupon from awocoupon_cart once finished with discounting
		//Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE `id_cart` = '.(int)($cart->id).' LIMIT 1'); # no, leave it for future use
		
		$is_found = false;
		foreach ($coupon_session['processed_coupons'] as $c)
		{
			if ($c['coupon_code'] == $coupon_code)
			{
				$is_found = true;
				break;
			}
		}
		if (!$is_found)
		{
			$this->_errors[] = 'The coupon code was not found';
			return;
		}
		//Tools::redirectAdmin(awohelper::getPSAdminLink('AdminOrders','vieworder&id_order='.$order->id));
		//Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
	}

	public function _taskLicenseDefaultActivate()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ($model->activate()) Tools::redirectAdmin($this->my_admin_link.'&view=cpanel&conf=102&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskLicenseDefaultDelete()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ($model->uninstall()) Tools::redirectAdmin($this->my_admin_link.'&view=license&conf=101&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskLicenseDefaultUpdlocalkey()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ($model->update_localkey($_POST['license'], $_POST['local_key'])) Tools::redirectAdmin($this->my_admin_link.'&view=cpanel&conf=101&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskLicenseDefaultUpdexpired()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ($model->check()) Tools::redirectAdmin($this->my_admin_link.'&view=license&conf=102&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskCouponGenerateEditStore()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelCoupon();
		if ($model->storeGeneratecoupons($post)) Tools::redirectAdmin($this->my_admin_link.'&view=coupon&conf=3&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskCouponDefaultDuplicate()
	{
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$get = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCoupon();
		if ($model->duplicatecoupon($get)) Tools::redirectAdmin($this->my_admin_link.'&view=coupon&conf=19&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskGiftcertCodeEditStore()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';

		$post = awoHelper::getValues($_POST);
		
		$data = array();
		$file = $_FILES;
		$exclude_first_row = Tools::getValue('exclude_first_row', '');
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		$delimiter = $params->get('csvDelimiter', ',');
		

		if (strtolower(substr($file['file']['name'], -4)) == '.csv')
		{
			ini_set('auto_detect_line_endings', true); //needed for mac users
			if (($handle = fopen($file['file']['tmp_name'], 'r')) !== false)
			{
				while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
				{
					if (count($row) > 3) $row = array_slice($row, 0, 3);
					$data[] = $row;
				}
				fclose($handle);
			}
		}
		if (!empty($exclude_first_row)) array_shift($data);
		if (empty($data)) $this->_errors[] = $this->l('Empty Import File');
		else
		{
			$post['data'] = $data;
			$model = new AwoCouponModelGiftcert();
			if ($model->storeCode($post)) Tools::redirectAdmin($this->my_admin_link.'&view=giftcert&layout=codedefault&conf=3&token='.$this->token);
			else $this->_errors = $model->_errors;
		}
	}
	public function _taskProfileDefaultDuplicate()
	{
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		
		$get = awoHelper::getValues($_GET);
		$model = new AwoCouponModelProfile();
		if ($model->duplicate($get)) Tools::redirectAdmin($this->my_admin_link.'&view=profile&conf=19&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskProfileDefaultMakeDefault()
	{
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';

		$post = awoHelper::getValues($_REQUEST);
		
		//$ids = $post['Box'] ;
		$view = Tools::getValue('view', 'cpanel');
		$ids = isset($post['submitBulktask_default'.$view.'default']) ? $post[$view.'defaultBox'] : (isset($post['submitBulktask_default']) ? $post['Box'] : array($post['id']));
		$id = current($ids);
		$model = new AwoCouponModelProfile();
		if ($model->makedefault($id)) Tools::redirectAdmin($this->my_admin_link.'&view=profile&conf=4&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	public function _taskReportDefaultExportReports()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$model = new AwoCouponModelReport();
		$post = awoHelper::getValues($_POST);

		$file = $model->export($post);

		if (!empty($file))
		{
			$filename = Tools::getValue('filename', 'file.csv');
			
			// required for IE, otherwise Content-disposition is ignored
			if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

			//  default: $ctype="application/force-download";
			header('Pragma: public'); // required
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false); // required for certain browsers 
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="'.$filename.'";');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.strlen($file));
			echo $file;
			exit();
		}
		
	}
	public function _taskImportDefaultStore()
	{
		header('Content-type: text/html; charset=utf-8');

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/import.php';
		$model = new AwoCouponModelImport();


		$data = array();
		$file = $_FILES;
		
		$exclude_first_row = Tools::getValue('exclude_first_row', '');
		$store_none_errors = Tools::getValue('store_none_errors', '');

		if (strtolower(substr($file['file']['name'], -4)) == '.csv')
		{
			ini_set('auto_detect_line_endings', true); //needed for mac users
			if (($handle = fopen($file['file']['tmp_name'], 'r')) !== false)
			{
				require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
				$params = new awoParams();
				$delimiter = $params->get('csvDelimiter', ',');
				
				while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) $data[] = $row;
				fclose($handle);
			}
		}
		if (!empty($exclude_first_row)) array_shift($data);
		
		if (empty($data))
			$this->_errors[] = $this->l('Empty Import File');
		else
		{
			$errors = $model->store($data, $store_none_errors);
			
			if (empty($errors))
			{
				Tools::redirectAdmin($this->my_admin_link.'&view=coupon&conf=101&token='.$this->token);
				
			}
			else
			{
				foreach ($errors as $id => $errarray)
				{
					$errText = '<br /><div>ID: '.$id.'<hr /></div>';
					foreach ($errarray as $err) $errText .= '<div style="padding-left:20px;">-- '.$err.'</div>';
					$this->_errors[] = $errText;
				}
			}
		}


	}

	public function _taskHistoryOrderDefaultGiftcertResend()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';

		$order_id = (int)Tools::getValue('order_id');
		$model = new AwoCouponModelHistory();
		if ($model->resend_giftcert($order_id)) Tools::redirectAdmin($this->my_admin_link.'&view=history&layout=orderdefault&conf=104&token='.$this->token);
		else $this->_errors = $model->_errors;
	}


	
	
	
	
	
	
	
	
	
	
	public static function rawhtml($val)
	{
		return $val;
	}
	private function getmybreadcrumb()
	{
		$view = Tools::getValue('view', 'dashboard');
		if (!empty($this->views[$view])) $this->breadcrumbs[] = $this->views[$view];
	}
	private function getHTMLMenu()
	{
		if ($this->ajax) return '';
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/menu.php';
		$menu = new AwoCouponMenu();
		return $menu->process();
		
		
		/*
		$inview = Tools::getValue('view', 'cpanel');
		$o = '<div class="path_bar" style="background-color:#F4E6C9;margin-bottom:0;">';
		foreach ($this->views AS $view=>$view_text) {
			if ($inview == $view) $o .= '<span class="current">'.$view_text.'</span> &nbsp; | &nbsp; ';
			else $o .= '<a href="'.$this->my_admin_link.'&view='.$view.'&token='.Tools::getValue('token').'">'.$view_text.'</a> &nbsp; | &nbsp; ';
		}
		$o .= '</div>';
		
		return $o;
		*/
	}
	private function getHTMLFooter()
	{
		if ($this->ajax) return '';
		return '<br><div align="right" style="font-size:9px;">&copy;'.date('Y').' <a href="http://awodev.com" target="_blank">AwoCoupon Pro</a> by Seyi Awofadeju</div>';
	}
	private function getUserParameters()
	{
		return '
			<input type="hidden" name="shoplist" value="'.Tools::getValue('shoplist').'" />
			<input type="hidden" name="function_type" value="'.Tools::getValue('function_type').'" />
			<input type="hidden" name="coupon_value_type" value="'.Tools::getValue('coupon_value_type').'" />
			<input type="hidden" name="discount_type" value="'.Tools::getValue('discount_type').'" />
			<input type="hidden" name="published" value="'.Tools::getValue('published').'" />
			<input type="hidden" name="start_date" value="'.Tools::getValue('start_date').'" />
			<input type="hidden" name="end_date" value="'.Tools::getValue('end_date').'" />
			<input type="hidden" name="order_status" value="'.Tools::getValue('order_status').'" />
			<input type="hidden" name="templatelist" value="'.(int)Tools::getValue('templatelist').'" />
			<input type="hidden" name="giftcert_product" value="'.((int)Tools::getValue('giftcert_product')).'" />
		';
	}
	private function reportgrid($name, $ardata, $arlabels, $arcolumns, $arrstyle = array())
	{
		if (!empty($ardata) && !empty($arlabels) && !empty($arcolumns) 
		&& is_array($ardata) && is_array($arlabels) && is_array($arcolumns))
		{

	//array("6|color:red;text-align:right;","7|color:green;text-align:right;","8|text-align:right;"));
			$header = '<div class="gridOuter"><div id="'.$name.'" class="gridInner"><table><thead><tr><td>&nbsp;</td>';
			foreach ($arlabels as $val) $header .= '<td>'.$val.'</td>';
			$header .= '</tr></thead>';
			
			//INITIALIZE ROW DATA
			$rowdata = '';
			foreach ($arcolumns as $key => $col)
				$rowdata .= '<td '.(isset($arrstyle[$key]) ? ' style=\"'.$arrstyle[$key].'\" ' : '').'>{$line[\''.$col.'\']}</td>';
			$rowdata = '<tr ".($i%2==0 ? \'class="alt"\' : \'\')."><td class=\'count\'>$i</td>'.$rowdata.'</tr>';


			$i = 1;
			$body = '<tbody>'; 
			foreach ($ardata as $line)
			{
				eval('	
						$body .= "'.$rowdata.'";
					');
				$i++;
			}
			$body .= '</tbody></table></div></div>';
				
			$script = '<script>new ScrollHeader(document.getElementById("'.$name.'"), true, true);</script>';

			return array('html'=>$header.$body,				//return output to write to screen
							'js'=>$script,
						);
			
		}
		return null;
	}

	public function real_processFilter()
	{
		$this->postProcess();

		$this->my_filters = new stdClass();
	
		$prefix = 'awocoupon'.$this->list_id;
		$this->my_filters->filters = $this->context->cookie->getFamily($prefix.'Filter_');
		foreach ($this->my_filters->filters as $key => $val) $this->my_filters->filters[str_replace('awocoupon'.$this->list_id, '', $key)] = $val;
	
		$this->my_filters->where = $this->my_filters->having = '';
		foreach ($this->my_filters->filters as $key => $value)
		{
			if ($value != null && !strncmp($key, $prefix.'Filter_', 7 + Tools::strlen($prefix)))
			{
				$key = Tools::substr($key, 7 + Tools::strlen($prefix));
				/* Table alias could be specified using a ! eg. alias!field */
				$tmp_tab = explode('!', $key);
				$filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

				if ($field = $this->filterToField($key, $filter))
				{
					$type = (array_key_exists('mytype', $field) ? $field['mytype'] : false);
					$key = isset($tmp_tab[1]) ? $tmp_tab[0].'.`'.$tmp_tab[1].'`' : '`'.$tmp_tab[0].'`';

					// Assignement by reference
					if (array_key_exists('havingFilter', $field)) $sql_filter = & $this->my_filters->having;
					else $sql_filter = & $this->my_filters->where;

					
					$sql_filter .= ' AND ';
					//$check_key = ($key == $this->identifier || $key == '`'.$this->identifier.'`');
					
					$tprefix = array_key_exists('tprefix', $field) ? $field['tprefix'].'.' : '';

					if ($type == 'int' || $type == 'bool')
						$sql_filter .= $tprefix.pSQL($key).'='.(int)$value.' ';
					elseif ($type == 'decimal')
						$sql_filter .= $tprefix.pSQL($key).'='.(float)$value.' ';
					elseif ($type == 'select')
						$sql_filter .= $tprefix.pSQL($key).'=\''.pSQL($value).'\' ';
					else
						$sql_filter .= $tprefix.pSQL($key).' LIKE "%'.awohelper::escape($value).'%" ';
					
				}
			}
		}
		
		
		
		// get list limits
		//$this->my_list_limit = null;
		$this->my_filters->limit = null;
		if (isset($this->context->cookie->{$this->list_id.'_pagination'}) && $this->context->cookie->{$this->list_id.'_pagination'})
			$this->my_filters->limit = $this->context->cookie->{$this->list_id.'_pagination'};
		else $this->my_filters->limit = $this->_pagination[1];
		$this->my_filters->limit = (int)Tools::getValue($this->list_id.'_pagination', $this->my_filters->limit);
		$this->context->cookie->{$this->list_id.'_pagination'} = $this->my_filters->limit;

		// get list start
		$this->my_filters->start = 0;
		if (Tools::getIsset('submitFilter'.$this->list_id) && is_numeric(Tools::getValue('submitFilter'.$this->list_id))) {
			$start = (int)Tools::getValue('submitFilter'.$this->list_id);
			$start = max(0, $start - 1);
			$this->my_filters->start = $start * $this->my_filters->limit;
		}
		elseif (isset($this->context->cookie->{$this->list_id.'_start'})) {
			$this->my_filters->start = $this->context->cookie->{$this->list_id.'_start'};
			if (!Tools::getIsset('submitFilter'.$this->list_id)) $_POST['submitFilter'.$this->list_id] = ($this->my_filters->start / $this->my_filters->limit) + 1;
		}
		$this->context->cookie->{$this->list_id.'_start'} = $this->my_filters->start;

		
		
		$this->my_filters->orderby = $this->my_filters->orderway = '';
		foreach ($_GET as $key => $value)
		{
			if (stripos($key, $this->list_id.'OrderBy') === 0)
			{
				$this->my_filters->orderby = $value;
				$this->context->cookie->{'awocoupon'.$key} = $value;
			}
			elseif (stripos($key, $this->list_id.'Orderway') === 0)
			{
				$this->my_filters->orderway = $value;
				$this->context->cookie->{'awocoupon'.$key} = $value;
			}
		}
		if (empty($this->my_filters->orderby) && !empty($this->context->cookie->{'awocoupon'.$this->list_id.'Orderby'}))
			$this->my_filters->orderby = $this->context->cookie->{'awocoupon'.$this->list_id.'Orderby'};
		if (empty($this->my_filters->orderway) && !empty($this->context->cookie->{'awocoupon'.$this->list_id.'Orderway'}))
			$this->my_filters->orderway = $this->context->cookie->{'awocoupon'.$this->list_id.'Orderway'};
		
		$this->my_filters->orderbystr = !empty($this->my_filters->orderby) ? ' ORDER BY '.pSQL($this->my_filters->orderby).' '.pSQL($this->my_filters->orderway) : '';

//printr($filters);
//printrx($this->context->cookie);
	}
	


	/*
	* ========== start overrides =============================================================================
	*/
	
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		return;
	}
	public function createTemplate($tpl_name)
	{
		if (version_compare(_PS_VERSION_, '1.6', '>=')) $path = _PS_MODULE_DIR_.'awocoupon/views/16/admin/';
		elseif (version_compare(_PS_VERSION_, '1.5', '>='))  $path = _PS_MODULE_DIR_.'awocoupon/views/15/admin/';
		if (file_exists($path.$tpl_name) && $this->viewAccess())
			return $this->context->smarty->createTemplate($path.$tpl_name, $this->context->smarty);

		return parent::createTemplate($tpl_name);
	}
	public function setMedia()
	{
		if (_PS_VERSION_ >= '1.6') parent::setMedia();

		$this->addCSS(AWO_URI.'/media/css/style.css');
//parent::setMedia();
//return;
		$tmp_js_files = $this->js_files;
		$this->js_files = array();
		$this->addCSS(AWO_URI.'/media/css/jquery-ui.css');
		$this->addJS(AWO_URI.'/media/js/jquery.min.js');
		$this->addJS(AWO_URI.'/media/js/jquery-ui.min.js');
		$this->addJS(AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js');
		$this->addJS(AWO_URI.'/media/js/awocoupon.js');
		
		foreach ($tmp_js_files as $file)
		{
			$filename = basename($file);
			if (substr(strtolower($filename), 0, 7) == 'jquery-') continue;
			if (substr(strtolower($filename), 0, 9) == 'jquery.ui') continue;
			
			$this->js_files[] = $file;
		}
	//echo '<pre>'; print_r($this->js_files);exit;
	
	
	
	
		if (version_compare(_PS_VERSION_, '1.6', '<')) $this->addCSS(_PS_CSS_DIR_.'admin.css', 'all');
		$admin_webpath = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
		$admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath);
		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$this->addCSS(__PS_BASE_URI__.$admin_webpath.'/themes/'.$this->bo_theme.'/css/admin.css', 'all');
		if ($this->context->language->is_rtl)
			$this->addCSS(_THEME_CSS_DIR_.'rtl.css');

		//$this->addJquery();
		$this->addjQueryPlugin(array('cluetip', 'hoverIntent', 'scrollTo', 'alerts', 'chosen'));

		$this->addjQueryPlugin('fancybox');
		$this->addJS(array(
			_PS_JS_DIR_.'admin.js',
			_PS_JS_DIR_.'toggle.js',
			_PS_JS_DIR_.'tools.js',
			_PS_JS_DIR_.'ajax.js',
			_PS_JS_DIR_.'toolbar.js'
		));

		if (!Tools::getValue('submitFormAjax'))
		{
			$this->addJs(_PS_JS_DIR_.'notifications.js');
			if (Configuration::get('PS_HELPBOX'))
				$this->addJS(_PS_JS_DIR_.'helpAccess.js');
		}

		// Execute Hook AdminController SetMedia
		awohelper::psHook('actionAdminControllerSetMedia', array());
	}

	/*
	*========== end overrides =============================================================================
	*/
	
	
	
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	




	
	
	
	
	private function setcurrentindex($val)
	{
		if (_PS_VERSION_ < '1.5')
		{
			global $currentIndex;
			$currentIndex = $this->my_admin_link.'&'.$val;
		}
		else
		{
			self::$currentIndex = $this->my_admin_link.'&'.$val;
			$this->context->smarty->assign('current', self::$currentIndex);
		}
		
		return $val;
	}
	

	
	


}
