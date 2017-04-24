<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

require_once(_PS_MODULE_DIR_.'awocoupon/awocoupon.php');

class AdminAwoCoupon extends AdminTab {
	private $awocoupon = NULL;
	public $multishop_context_group = false;

	public function __construct() {
		$this->awocoupon = new AwoCoupon();
		$this->context = Context::getContext();
		parent::__construct();
		//$this->context = Context::getContext();
		$this->_conf[101] = 'Data Saved';
		$this->_conf[102] = 'License Activated';
		$this->_conf[103] = 'Invalid License';
		$this->_conf[104] = 'Email Sent';
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$this->_filter = '';
		$this->_filterHaving = '';
		$this->_orderBy = '';
		$this->_defaultOrderBy = '';
		$this->_orderWay = '';
		$this->_defaultOrderWay = '';
		$this->_orderByAll  = '';
		
		$this->view_final = '';

	}
	
	public function display() {
		
				
		$view = Tools::getValue('view','cpanel');
		$layout = Tools::getValue('layout','default');
		$task = Tools::getValue('task');
		if(Tools::getIsset('add')) $layout = str_replace('default','edit',$layout);
		if(Tools::getIsset('update')) $layout = str_replace('default','edit',$layout);
		if(Tools::getIsset('delete') || Tools::getIsset('submitDel')) $task = 'delete';
		if(Tools::getIsset('duplicate')) $task = 'duplicate';
		if(Tools::getIsset('task_default')) $task = 'makedefault';
		if(Tools::getIsset('task_generate')) $layout = 'generateedit';
		
		if(!empty($task)) {
			$function = '_task'.$view.$layout.$task;
			if(method_exists($this,$function)) $this->$function();
		}
		
		$this->view_final = $view.$layout;
		$this->processFilter();

		$function = '_display'.$view.$layout;
		$html = (method_exists($this,$function)) ? $this->$function() : '';
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/menu.php';
		$menu = new AwoCouponMenu();
		$menu_html = $menu->process();
		global $css_files, $js_files;
		$script_css_html = '<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery.min.js"></script>';
		foreach($css_files as $css_file=>$css_type) $script_css_html .= '<link type="text/css" rel="stylesheet" href="'.$css_file.'" />';
		foreach($js_files as $js_file) $script_css_html .= '<script type="text/javascript" src="'.$js_file.'"></script>';
		$js_files = $css_files = array();

		//		<div>'.$this->displayMenu().'</div>
		echo $script_css_html.
			'
			<div>'.$menu_html.'</div>
			<table class="table_grid"><tr><td>
				<style>span.current { font-weight:bold; color:#7E3817;} table{width:100%;} table.admintable { width:auto; } div.path_bar { display:block; } </style>
				<div>'.$html.'</div><div class="clear"></div>
				<br><div align="right" style="font-size:9px;">&copy;'.date('Y').' <a href="http://awodev.com" target="_blank">AwoCoupon Pro</a> by Seyi Awofadeju</div>
			</td></tr></table>
		';
	}
	
	
	
	public function displayMenu() {
		$views	= array(
					array('cpanel',			$this->l('Dashboard')),
					array('coupon',			$this->l('Coupons')),
					array('giftcert',			$this->l('Gift Certificates')),
					array('profile',			$this->l('Profiles')),
					array('history',			$this->l('History of Uses')),
					array('import',			$this->l('Import')),
					array('report',			$this->l('Reports')),
					array('license',			$this->l('License')),
					array('about',				$this->l('About')),
				);	


		$inview = Tools::getValue('view','cpanel');
		$o = '<div class="path_bar" style="background-color:#F4E6C9;margin-bottom:0;">';
		foreach ($views AS $view) {
			if($inview == $view[0]) $o .= '<span class="current">'.$view[1].'</span> &nbsp; | &nbsp; ';
			else $o .= '<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view='.$view[0].'&token='.Tools::getValue('token').'">'.$view[1].'</a> &nbsp; | &nbsp; ';
		}
		$o .= '</div>';
		
		return $o;
	}
	

	public function _displayCpanelDefault() {
		global $smarty;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/dashboard.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$model = new AwoCouponModelDashboard();
		
		
		$params = new awoParams();

		$time = $params->get('cache_updatecheck_time', 0);
		if ($time>time()) $check = json_decode($params->get('cache_updatecheck_data'));
		else { 
			$check = $model->getVersionUpdate();
			$params->set('cache_updatecheck_time', time()+(3600*72));
			$params->set('cache_updatecheck_data', json_encode($check));
		}
		
		if(awohelper::param_get('version') != $this->awocoupon->version) {
		// clean up everything
			$this->awocoupon->version_mismatch(awohelper::param_get('version'));
			$check = $model->getVersionUpdate();
			$params->set('cache_updatecheck_time', time()+(3600*72));
			$params->set('cache_updatecheck_data', json_encode($check));
		}
		
		$time = $params->get('cache_deleteexpired_time', 0);
		if ($time>time());
		else { 
			$model->deleteExpiredCoupons();
			$params->set('cache_deleteexpired_time', time()+(3600*24));
		}
		

		$smarty->assign(array(
			'status' => $check,
			'genstats'=>$model->getGeneralstats(),
			'license'=>$model->getLicense(),
			'url' => 'index.php?tab=AdminAwoCoupon&module=awocoupon',
			'ajax_url'=>AWO_URI.'/ajax.php',
			'img_url'=>AWO_URI.'/media/img',
			'token'=>$this->token,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'cpanel_default');
	}

	
	public function _displayCouponDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon');
		
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = true;
		$this->noLink = true;
	 	$this->delete = true;
		$this->duplicate = true;
		$this->customlistfooter =' &nbsp; <input type="submit" class="button" name="task_generate" value="'.$this->l('Generate Coupons').'" />';
		$this->fieldsDisplay = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int',),
			'coupon_code' => array('title' => $this->l('Code'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'function_type' => array('title' => $this->l('Function Type'), 'width' => 85,'type' => 'select', 'select' => awoHelper::vars('function_type'),'filter_key' => 'function_type','mytype'=>'select',),
			//'note' => array('title' => $this->l('Description'), 'width' => 100, ),
			'coupon_value_type' => array('title' => $this->l('Type'), 'type' => 'select', 'select' => awoHelper::vars('coupon_value_type'),'filter_key' => 'coupon_value_type','mytype'=>'select',),
			'coupon_value' => array('title' => $this->l('Value'), 'width' => 50, 'align' => 'right'),
			'startdate' => array('title' => $this->l('From'), 'width' => 60,  'align' => 'center'),
			'expiration' => array('title' => $this->l('To'), 'width' => 60,  'align' => 'center'),
			//'date_to' => array('title' => $this->l('To'), 'width' => 60, 'type' => 'date', 'align' => 'right'),
			'details' => array('title' => $this->l('Details'), 'width' => 60,  'align' => '', 'orderby' => false, 'search' => false),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'select' => awoHelper::vars('published'),'filter_key' => 'published','mytype'=>'select',)
		);
		
		$this->real_processFilter();
		
		$model = new AwoCouponModelCoupon();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);
		
		$admin_dir = basename(dirname(__PS_BASE_URI__.substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__) )));
		//				"href": "index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&layout=detaildefault&id="+id+"&token='.$this->token.'&dir='.urlencode($admin_dir).'"
		
		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		
		$fancy_js = _PS_VERSION_ < '1.5' ? _PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.js';
		$fancy_css = _PS_VERSION_ < '1.5' ? _PS_CSS_DIR_.'jquery.fancybox-1.3.4.css' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css';
		return '<script type="text/javascript" src="'.$fancy_js.'"></script>
			<link href="'.$fancy_css.'" rel="stylesheet" type="text/css" media="screen" />
			<script language="javascript" type="text/javascript">
			function coupon_detail(id) {
				jQuery(document).ready(function () {
					jQuery.fancybox({
						"width"				: "75%",
						"height"			: "75%",
						"autoScale"     	: false,
						"transitionIn"		: "none",
						"transitionOut"		: "none",
						"type": "iframe",
						"href": "'.AWO_URI.'/ajax.php?id="+id+"&dir='.urlencode($admin_dir).'&task=coupondetail"
					});
				});	
			}
			</script>'.$html;
		
	}
	public function _displayCouponGenerateEdit() {
		global $smarty;
		//return 'im smiling';
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$row = (object) array('template'=>'','number'=>'');
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }		
				

		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		$lists['templatelist'] = awoHelper::DD(awoAutoGenerate::getCouponTemplates(), 'template', 'class="inputbox" style="width:250px;"', $row->template,'','id','coupon_code' );		

		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'coupon_generateedit');
		
	}
	public function _displayCouponEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoslider.php';
		
		$model = new AwoCouponModelCoupon();
		$row = $model->getEntry();
		
		
		$post = awoHelper::getValues($_POST);
		if ( !empty($post) ) {
			if(!empty($post['userlist'])) {
				$tmp = $post['userlist'];
				$post['userlist'] = array();
				foreach($tmp as $id) $post['userlist'][$id] = (object) array('user_id'=>$id,'user_name'=>$post['usernamelist'][$id]);
			}
			
			if(!empty($post['assetlist'])) {
				$tmp = $post['assetlist'];
				$post['assetlist'] = array();
				foreach($tmp as $id) $post['assetlist'][$id] = (object) array('asset_id'=>$id,'asset_name'=>$post['assetnamelist'][$id]);
			}

			if(!empty($post['assetlist2'])) {
				$tmp = $post['assetlist2'];
				$post['assetlist2'] = array();
				foreach($tmp as $id) $post['assetlist2'][$id] = (object) array('asset_id'=>$id,'asset_name'=>$post['asset2namelist'][$id]);
			}

			if(!empty($post['countrylist'])) {
				$tmp = $post['countrylist'];
				$post['countrylist'] = array();
				foreach($tmp as $id) $post['countrylist'][$id] = (object) array('asset_id'=>$id);
			}

			if(!empty($post['statelist'])) {
				$tmp = $post['statelist'];
				$post['statelist'] = array();
				foreach($tmp as $id) $post['statelist'][$id] = (object) array('asset_id'=>$id);
			}

			$row = (object) array_merge((array) $row, (array) $post); //bind the db return and post
		}
		
				
		//Tools::addJS(AWO_URI.'/media/js/coupon.js');
		//Tools::addJS(AWO_URI.'/media/js/coupon_cumulative_value.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery-ui-1.8.10.autocomplete.min.js');
		
		$slider['start'] = awoJHtmlSliders::start('extra_options', array('closeAll'=>1));
		$slider['panel_customers'] = awoJHtmlSliders::panel($this->l('Customers'), 'pn_user');
		$slider['panel_asset1'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset');
		$slider['panel_asset2'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset2');
		$slider['end'] = awoJHtmlSliders::end();
				
		$slider2['start'] = awoJHtmlSliders::start('extra_options2', array('closeAll'=>0));
		$slider2['panel_asset1'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset');
		$slider2['panel_asset2'] = awoJHtmlSliders::panel($this->l('Assets'), 'pn_asset2');
		$slider2['end'] = awoJHtmlSliders::end();
		
		
		$lists['function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'function_type', 'class="inputbox" style="width:147px;" onchange="funtion_type_change();"', $row->function_type,'' );		
		$lists['dd_function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'dd_function_type', 'id="dd_function_type" class="inputbox" style="width:147px;" onchange="document.adminForm.function_type.value=this.value;funtion_type_change();"', $row->function_type,'' );		
		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" style="width:147px;"', $row->published );		
		$lists['parent_type'] = awoHelper::DD(awoHelper::vars('parent_type'), 'parent_type', 'class="inputbox" style="width:147px;"', @$row->params->process_type );		
		$lists['buy_xy_process_type'] = awoHelper::DD(awoHelper::vars('buy_xy_process_type'), 'buy_xy_process_type', 'class="inputbox" style="width:147px;"', $row->buy_xy_process_type );		
		$lists['coupon_value_type'] = awoHelper::DD(awoHelper::vars('coupon_value_type'), 'coupon_value_type', 'class="inputbox" style="width:147px;"', $row->coupon_value_type );		
		$lists['discount_type'] = awoHelper::DD(awoHelper::vars('discount_type'), 'discount_type', 'class="inputbox" style="width:147px;" ', $row->discount_type );		
		$lists['min_value_type'] = awoHelper::DD(awoHelper::vars('min_value_type'), 'min_value_type', 'class="inputbox" style="width:100px;"', $row->min_value_type );		
		$lists['min_qty_type'] = awoHelper::DD(awoHelper::vars('min_qty_type'), 'min_qty_type', 'class="inputbox" style="width:100px;"', $row->min_qty_type);		
		$lists['user_type'] = awoHelper::DD(awoHelper::vars('user_type'), 'user_type', 'class="inputbox" style="width:200px;" onchange="user_type_change();"', $row->user_type );		
		$states = array('product'=>'Product',
						'category'=>'Category',
						'manufacturer'=>'Manufacturer',
						'vendor'=>'Vendor',
					);
		$lists['asset1_function_type'] = awoHelper::DD($states, 'asset1_function_type', 'class="inputbox" style="width:147px;" onchange="asset_type_change(1);"', $row->asset1_function_type ,'');	
		$lists['asset2_function_type'] = awoHelper::DD($states, 'asset2_function_type', 'class="inputbox" style="width:147px;" onchange="asset_type_change(2);"', $row->asset2_function_type ,'');	
		
		$country_list = awohelper::getCountryList();
		$lists['countrylist'] = awoHelper::DD($country_list, 'countrylist[]', 'MULTIPLE class="inputbox" style="width:90%;" ', array_keys($row->countrylist) ,'','country_id','country_name');	
		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'slider' =>$slider,
			//'slider1' =>$slider1,
			'slider2' =>$slider2,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
			
			'is_multistore'=>awoHelper::is_multistore(),
		
			'errors'=>$errors,
		));

		return '
			<link rel="stylesheet" href="'.AWO_URI.'/media/css/jquery-ui.css" type="text/css" /> 
			<link rel="stylesheet" href="'.AWO_URI.'/media/css/select2.css" type="text/css" /> 
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery-ui.min.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/select2.min.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/'.( _PS_VERSION_ < '1.5' ? 'coupon_14.js' : 'coupon.js').'?115"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/coupon_cumulative_value.js"></script>
			'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', _PS_VERSION_ < '1.5' ? 'coupon_edit' : 'coupon_edit_15');
		
	}
	public function _displayCouponDetailDefault() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoslider.php';
		

		
		$model = new AwoCouponModelCoupon();
		$row = $model->getEntry();
		
		
		$slider['start'] = awoJHtmlSliders::start('extra_options', array('closeAll'=>0));
		if(!empty($row->userlist)) {
			$user_title = '';
			if($row->user_type == 'user') $user_title = $this->l('Customers');		
			elseif($row->user_type == 'usergroup') $user_title = $this->l('Shopper Groups');	
			$slider['panel_customers'] = awoJHtmlSliders::panel($user_title, 'pn_user');
		}
		
		if(!empty($row->shoplist)) {
			$slider['panel_shops'] = awoJHtmlSliders::panel($this->l('Shop'), 'pn_shop');
		}
		
		$asset1_title = '';
		if($row->function_type == 'coupon' || $row->function_type=='giftcert') {
			if($row->asset1_function_type=='product') $asset1_title = $this->l('Products');		
			elseif($row->asset1_function_type == 'category') $asset1_title = $this->l('Categories');	
			elseif($row->asset1_function_type == 'manufacturer') $asset1_title = $this->l('Manufacturers');
			elseif($row->asset1_function_type == 'vendor') $asset1_title = $this->l('Vendors');
			if(!empty($asset1_title)) $asset1_title .= ' ('.awoHelper::vars('asset_mode',empty($row->asset1_mode) ? 'include' : $row->asset1_mode).')';
		}
		elseif($row->function_type == 'shipping') $asset1_title = $this->l('Shipping').' ('.awoHelper::vars('asset_mode',$row->asset1_mode).')';
		elseif($row->function_type == 'parent') $asset1_title = $this->l('Coupons');
		elseif($row->function_type == 'buy_x_get_y') $asset1_title = $this->l('Buy X').' ('.awoHelper::vars('asset_mode',$row->asset1_mode).' '.awoHelper::vars('asset_type',$row->params->asset1_type).')';	
		$slider['panel_asset1'] = awoJHtmlSliders::panel($asset1_title, 'pn_asset1');
		
		if(!empty($row->countrylist) || !empty($row->statelist)) {
			$title = !empty($row->statelist) ? $this->l('State') : $this->l('Country');
			$title .= ' ('.$this->l(awohelper::vars('asset_mode',empty($row->params->countrystate_mode) ? 'include' : $row->params->countrystate_mode)).')';		
	
			$row->countrystatelist = !empty($row->statelist)  ? $row->statelist : $row->countrylist;
			$slider['panel_countrystate'] = awoJHtmlSliders::panel($title, 'pn_countrystate');
		}


		if(!empty($row->assetlist2)) {
			$asset2_title = '';
			if($row->function_type == 'giftcert') $asset2_title = $this->l('Shipping').' ('.awoHelper::vars('asset_mode',$row->asset2_mode).')';
			//elseif($row->function_type == 'shipping') $asset2_title = $this->l('Products').' ('.awoHelper::vars('asset_mode',$row->asset2_mode).')';
			elseif($row->function_type == 'buy_x_get_y') $asset2_title = $this->l('Get Y').' ('.awoHelper::vars('asset_mode',$row->params->asset2_mode).' '.awoHelper::vars('asset_type',$row->params->asset2_type).')';
			else {
				$lang = 'Products';
				if(!empty($row->params->asset2_type)) {
					if($row->params->asset2_type == 'product') $lang = 'Products';
					elseif($row->params->asset2_type == 'category') $lang = 'Categories';
					elseif($row->params->asset2_type == 'manufacturer') $lang = 'Manufacturers';
					elseif($row->params->asset2_type == 'vendor') $lang = 'Vendors';
				}
				$asset2_title = $asset2_title = $this->l($lang).' '.(!empty($row->asset2_mode) ? '('.awoHelper::vars('asset_mode',$row->asset2_mode).')' : '');
			}
			$slider['panel_asset2'] = awoJHtmlSliders::panel($asset2_title, 'pn_asset2');
		}
		$slider['end'] = awoJHtmlSliders::end();
		
		$row->str_coupon_value_type = $row->function_type == 'parent' ? '' : awoHelper::vars('coupon_value_type',$row->coupon_value_type);
		$row->str_discount_type = awoHelper::vars('discount_type',empty($row->discount_type) ? '' : $row->discount_type);
		$row->str_function_type = awoHelper::vars('function_type',$row->function_type);
		$row->str_coupon_value = !empty($row->coupon_value) ? $row->coupon_value: $row->coupon_value_def;
		$row->str_buy_xy_process_type = awoHelper::vars('buy_xy_process_type',empty($row->params->process_type) ? 'abc' : $row->params->process_type);
		if(!empty($row->min_value)) $row->str_min_value = number_format($row->min_value,2).' '.awoHelper::vars('min_value_type',!empty($row->params->min_value_type) ? $row->params->min_value_type : 'overall');
		if (!empty($row->params->min_qty)) $row->str_min_qty = ((int)$row->min_qty).' '.awoHelper::vars('min_qty_type', !empty($row->params->min_qty_type) ? $row->params->min_qty_type : 'overall');
		$row->str_parent_type = awoHelper::vars('parent_type',empty($row->params->process_type) ? 'abc' : $row->params->process_type);
		if(empty($row->params)) $row->params = array();

		$exclude_str = array();
		if(!empty($row->exclude_special)) $exclude_str[] = $this->l('Specials');
		if(!empty($row->exclude_giftcert)) $exclude_str[] = $this->l('Gift Products');
		if(!empty($exclude_str)) $row->str_exclude = implode(', ',$exclude_str);

//printrx($row);
		$smarty->assign(array(
			'row'=>$row,
			'slider' => $slider,
			'url_module' => AWO_URI,
		
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'coupon_detaildefault');
		
	}


	public function _displayCouponAutoDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto');

		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = true;
	 	$this->delete = true;
		$this->noLink = true;
		$this->fieldsDisplay = array(
			'coupon_code' => array('title' => $this->l('Code'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'function_type' => array('title' => $this->l('Function Type'), 'width' => 85,'type' => 'select', 'select' => awoHelper::vars('function_type'),'filter_key' => 'function_type','mytype'=>'select',),
			'coupon_value_type' => array('title' => $this->l('Type'), 'type' => 'select', 'select' => awoHelper::vars('coupon_value_type'),'filter_key' => 'coupon_value_type','mytype'=>'select',),
			'coupon_value' => array('title' => $this->l('Value'), 'width' => 50, 'align' => 'right'),
			'ordering' => array('title' => $this->l('Ordering'), 'align' => '', 'width' => 40,'orderby' => true, 'search' => false,),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'select' => awoHelper::vars('published'),'filter_key' => 'published','mytype'=>'select',)
		);		
		
		$this->real_processFilter();
		$model = new AwoCouponModelCouponAuto();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);

		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
		
	}
	public function _displayCouponAutoEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelCouponAuto();
		$row = $model->getEntry();
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }		
				
		
		$published = awoHelper::vars('published');
		unset($published[-2]);
		$lists['published'] = awoHelper::DD($published, 'published', 'class="inputbox" style="width:147px;"', $row->published);		
		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
			'question_img'=>AWO_URI.'/media/img/question_mark.png',
		
			'errors'=>$errors,
		));

		return '
			<link rel="stylesheet" href="'.AWO_URI.'/media/css/jquery-ui.css" type="text/css" /> 
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery-ui.min.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/coupon_cumulative_value.js"></script>
			<br />'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'couponauto_edit');
		
	}

	public function _displayGiftcertDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert');

		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = true;
	 	$this->delete = true;
		$this->noLink = true;
		$this->fieldsDisplay = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int','havingFilter'=>1),
			'product_name' => array('title' => $this->l('Product'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'coupon_code' => array('title' => $this->l('Template'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'profile' => array('title' => $this->l('Profile Image'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'codecount' => array('title' => $this->l('Codes'), 'width' => 60,  'align' => '', 'orderby' => false, 'search' => false),
			'expiration' => array('title' => $this->l('Expiration'), 'width' => 60,  'align' => '', 'orderby' => false, 'search' => false),
			'vendor_name' => array('title' => $this->l('Vendor'), 'width' => 60,  'align' => '', ),
			'published' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'select' => awoHelper::vars('published'),'filter_key' => 'published','mytype'=>'select','havingFilter'=>1,)
		);		
		
		$this->real_processFilter();
		$model = new AwoCouponModelGiftCert();
		$this->_list = $model->getEntriesGift($this->my_filters);
		$this->_listTotal = $model->getTotalGift($this->my_filters);

		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Products').'</span> &nbsp; | &nbsp; 
					<a href="'.$currentIndex.'&layout=codedefault&token='.Tools::getValue('token').'">'.$this->l('Codes').'</a> &nbsp; | &nbsp; 
				</div>'.$html;*/
		return $html;
		
	}
	public function _displayGiftcertEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelGiftCert();
		$row = $model->getEntryGift();
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }		
				
		//Tools::addJS(AWO_URI.'/media/js/coupon_cumulative_value.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js');
		//Tools::addJS(AWO_URI.'/media/js/jquery-ui-1.8.10.autocomplete.min.js');
		
		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" style="width:147px;"', $row->published );		
		$lists['expiration_type'] = awoHelper::DD(awoHelper::vars('expiration_type'), 'expiration_type', 'class="inputbox" style="width:147px;"', $row->expiration_type,'' );		
		$lists['profilelist'] = awoHelper::DD($model->getProfileList(), 'profile_id', 'class="inputbox" style="width:147px;"', $row->profile_id,'','id','title' );		
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'coupon_template_id', 'class="inputbox" style="width:147px;"', $row->coupon_template_id,'','id','coupon_code' );		

		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
			'question_img'=>AWO_URI.'/media/img/question_mark.png',
		
			'errors'=>$errors,
		));

		return '
			<link rel="stylesheet" href="'.AWO_URI.'/media/css/jquery-ui.css" type="text/css" /> 
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery-ui.min.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/coupon_cumulative_value.js"></script>
			<br />'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'giftcert_edit');
		/*return '
			<link rel="stylesheet" href="'.AWO_URI.'/media/css/jquery-ui.css" type="text/css" /> 
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery-ui.min.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/jquery.ui.autocomplete.ext.js"></script>
			<script type="text/javascript" src="'.AWO_URI.'/media/js/coupon_cumulative_value.js"></script>
			<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Products').'</span> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&token='.Tools::getValue('token').'">'.$this->l('Codes').'</a> &nbsp; | &nbsp; 
				</div><br />'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'giftcert_edit');*/
		
	}
	public function _displayGiftcertCodeDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = false;
	 	$this->delete = true;
		$this->noLink = true;
		$this->fieldsDisplay = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25, 'mytype'=>'int',),
			'product_name' => array('title' => $this->l('Product'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'code' => array('title' => $this->l('Code'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'note' => array('title' => $this->l('Description'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'status' => array('title' => $this->l('Status'), 'align' => 'center',  'orderby' => false, 'type' => 'select', 'select' => awoHelper::vars('status'),'mytype'=>'select',)
		);		
		
		$this->real_processFilter();

		$model = new AwoCouponModelGiftCert();
		$this->_list = $model->getEntriesCode($this->my_filters);
		$this->_listTotal = $model->getTotalCode($this->my_filters);

		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		return '<br /><a href="'.$currentIndex.'&add&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />
				'.$html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&token='.Tools::getValue('token').'">'.$this->l('Products').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Codes').'</span> &nbsp; | &nbsp; 
				</div>
				<br /><a href="'.$currentIndex.'&add&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />
				'.$html;*/
		
	}
	public function _displayGiftcertCodeEdit() {
		global $smarty;

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelGiftCert();
		
		$post = awoHelper::getValues($_POST);
				
		$lists['productlist'] = awoHelper::DD($model->getGiftCertProductList(), 'product_id', 'class="inputbox" style="width:147px;"', null,'','product_id','product_name' );		

		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'lists' => $lists,
			'exclude_first_row'=>Tools::getValue('exclude_first_row','1'),
			'store_none_errors'=>Tools::getValue('store_none_errors','1'),
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'giftcert_codeedit');
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&token='.Tools::getValue('token').'">'.$this->l('Products').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Codes').'</span> &nbsp; | &nbsp; 
				</div><br />'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'giftcert_codeedit');*/
		
	}


	public function _displayProfileDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = true;
	 	$this->delete = true;
		$this->duplicate = true;
		//$this->view = true;
		$this->noLink = true;
		$this->customlistfooter =' &nbsp; <input type="submit" class="button" name="task_default" value="'.$this->l('Default').'" />';

		$this->fieldsDisplay = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int'),
			'title' => array('title' => $this->l('Title'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'from_name' => array('title' => $this->l('From Name'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'from_email' => array('title' => $this->l('From Email'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'bcc_admin' => array('title' => $this->l('Bcc Admin'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>', 'search' => false),
			'email_subject' => array('title' => $this->l('Email Subject'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'message_type' => array('title' => $this->l('Type'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>', 'search' => false),
			'preview' => array('title' => $this->l(''), 'width' => 60,  'align' => '', 'orderby' => false, 'search' => false),
			'default' => array('title' => $this->l('Default'), 'width' => 60,  'align' => '', 'orderby' => false, 'search' => false),
		);		
		
		$this->real_processFilter();
		
		$model = new AwoCouponModelProfile();
		$this->_list = $model->getEntries($this->my_filters);
		$this->_listTotal = $model->getTotal($this->my_filters);
		
		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		$fancy_js = _PS_VERSION_ < '1.5' ? _PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.js';
		$fancy_css = _PS_VERSION_ < '1.5' ? _PS_CSS_DIR_.'jquery.fancybox-1.3.4.css' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css';
		return '<script type="text/javascript" src="'.$fancy_js.'"></script>
				<link href="'.$fancy_css.'" rel="stylesheet" type="text/css" media="screen" />
				<script type="text/javascript">
				jQuery(document).ready(function() {
					$(".modal").fancybox({
						"width"				: "75%",
						"height"			: "75%",
						"autoScale"     	: false,
						"transitionIn"		: "none",
						"transitionOut"		: "none",
						"type"				: "iframe"
					});
				});
				</script>
				'.$html;
		
	}
	public function _displayProfileEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelProfile();
		$row = $model->getEntry();
		$font_color     	= $model->getfontcolor();
		$imagedd     	= $model->getimages();
		$fontdd     	= $model->getfonts();
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) {
			$row = (object) array_merge((array) $row, (array) $post); //bind the db return and post
			$text = $_POST['email_html'];
			if(!empty($text)) {
				$text		= str_replace( '<br>', '<br />', $text );
				$row->email_body = $text;
			}
			foreach($row->imgplugin as $k=>$r1) foreach($r1 as $k2=>$r2) $row->imgplugin[$k][$k2] = (object)$r2;
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
		$lists['expiration_text'] = awoHelper::DD($tmp, 'expiration_text', 'class="inputbox" size="1"', $row->expiration_text );		
		$lists['message_type'] = awoHelper::DD(awoHelper::vars('giftcert_message_type'), 'message_type', 'class="inputbox" onchange="message_type_change()" ', $row->message_type , '');		
		$lists['image'] = awoHelper::DD($imagedd, 'image', 'class="inputbox" onchange="checkimage()" ', $row->image, '---' );		
				
		$aligndd = array('L'=>$this->l('Left'),'C'=>$this->l('Middle'),'R'=>$this->l('Right'),);
		$lists['couponcode_align'] = awoHelper::DD($aligndd, 'couponcode_align', 'class="inputbox" size="1"', $row->couponcode_align );		
		$lists['couponvalue_align'] =awoHelper::DD( $aligndd, 'couponvalue_align', 'class="inputbox" size="1"', $row->couponvalue_align );		
		$lists['expiration_align'] = awoHelper::DD($aligndd, 'expiration_align', 'class="inputbox" size="1"', $row->expiration_align );		
		$lists['freetext1_align'] = awoHelper::DD($aligndd, 'freetext1_align', 'class="inputbox" size="1"', $row->freetext1_align );		
		$lists['freetext2_align'] = awoHelper::DD($aligndd, 'freetext2_align', 'class="inputbox" size="1"', $row->freetext2_align );		
		$lists['freetext3_align'] = awoHelper::DD($aligndd, 'freetext3_align', 'class="inputbox" size="1"', $row->freetext3_align );		
		
		$lists['couponcode_font'] = awoHelper::DD($fontdd, 'couponcode_font', 'class="inputbox" size="1"', $row->couponcode_font );		
		$lists['couponvalue_font'] = awoHelper::DD($fontdd, 'couponvalue_font', 'class="inputbox" size="1"', $row->couponvalue_font );		
		$lists['expiration_font'] = awoHelper::DD($fontdd, 'expiration_font', 'class="inputbox" size="1"', $row->expiration_font );		
		$lists['freetext1_font'] = awoHelper::DD($fontdd, 'freetext1_font', 'class="inputbox" size="1"', $row->freetext1_font );		
		$lists['freetext2_font'] = awoHelper::DD($fontdd, 'freetext2_font', 'class="inputbox" size="1"', $row->freetext2_font );		
		$lists['freetext3_font'] = awoHelper::DD($fontdd, 'freetext3_font', 'class="inputbox" size="1"', $row->freetext3_font );		
				
		$tmp_color = '';
		foreach($font_color as $key=>$value) $tmp_color .= '<option value="'.$key.'" style="background-color:'.$key.';">'.$value.'</option>';
		$lists['couponcode_font_color'] = '<select name="couponcode_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['couponvalue_font_color'] = '<select name="couponvalue_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['expiration_font_color'] = '<select name="expiration_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext1_font_color'] = '<select name="freetext1_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext2_font_color'] = '<select name="freetext2_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		$lists['freetext3_font_color'] = '<select name="freetext3_font_color" class="inputbox size="1">'.$tmp_color.'</select>';
		
		$yes_no = array('0'=>$this->l('No'),'1'=>$this->l('Yes'),);
		$lists['is_pdf'] = awoHelper::DD($yes_no, 'is_pdf', 'class="inputbox" size="1"', $row->is_pdf );		
		
		if(!empty($row->imgplugin)) {
			foreach($row->imgplugin as $k=>$r1) {
				if(!empty($r1)) {
					foreach($r1 as $k2=>$r2) {
						$lists[$k.'_'.$k2.'_align'] = awoHelper::DD($aligndd, 'imgplugin['.$k.']['.$k2.'][align]', 'class="inputbox" size="1"', @$row->align  );		
						$lists[$k.'_'.$k2.'_font'] = awoHelper::DD($fontdd, 'imgplugin['.$k.']['.$k2.'][font]', 'class="inputbox" size="1"', @$row->font  );		
						$lists[$k.'_'.$k2.'_font_color'] = '<select name="imgplugin['.$k.']['.$k2.'][font_color]" class="inputbox size="1">'.$tmp_color.'</select>';
					}
				}
			}
		}
		

		global $cookie;
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER["PHP_SELF"]);
		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
			
			'base_url'=>__PS_BASE_URI__,
			'isoTinyMCE'=>$isoTinyMCE,
			'theme_path'=>_THEME_CSS_DIR_,
			'tiny_ad'=>$ad,
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
			'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
			'languages'=>Language::getLanguages(),
		));


		$fancy_js = _PS_VERSION_ < '1.5' ? _PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.js';
		$fancy_css = _PS_VERSION_ < '1.5' ? _PS_CSS_DIR_.'jquery.fancybox-1.3.4.css' : _PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css';
		return '<script type="text/javascript" src="'.$fancy_js.'"></script>
				<link href="'.$fancy_css.'" rel="stylesheet" type="text/css" media="screen" />
				'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', _PS_VERSION_ < '1.5' ? 'profile_edit' : 'profile_edit_15');
		
	}
	
	
	public function _displayhistoryhistdefault() { return $this->_displayHistoryDefault(); }
	public function _displayHistoryDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = false;
	 	$this->delete = true;
		$this->noLink = true;
		$this->fieldsDisplay = array(
			'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int','havingFilter'=>1),
			'coupon_code' => array('title' => $this->l('Coupon Code'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'user_id' => array('title' => $this->l('Customer ID'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','mytype'=>'int'),
			'user_email' => array('title' => $this->l('E-mail'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'lastname' => array('title' => $this->l('Last Name'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'firstname' => array('title' => $this->l('First Name'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'discount' => array('title' => $this->l('Discount'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'order_number' => array('title' => $this->l('Order ID'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','havingFilter'=>1),
			'cdate' => array('title' => $this->l('Order Date'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>', 'search'=>false),
		);		

		$this->real_processFilter();
				
		$model = new AwoCouponModelHistory();
		$this->_list = $model->getEntriesHist($this->my_filters);
		$this->_listTotal = $model->getTotalHist($this->my_filters);
		
		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		return '<br /><a href="'.$currentIndex.'&add&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />
				'.$html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Coupons').'</span> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div>
				<br /><a href="'.$currentIndex.'&add&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />
				'.$html;*/
		
	}
	public function _displayHistoryEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelHistory();
		$row = $model->getEntryHist();
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }		
				
		
		$lists['couponlist'] = awoHelper::DD($model->getCouponList(), 'coupon_id', 'class="inputbox" style="width:147px;"', $row->coupon_id,'','id','coupon_code' );		

		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'history_edit');
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<span class="current">'.$this->l('Coupons').'</span> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div>				
				<br />'.$this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'history_edit');*/
	}
	public function _displayHistoryGiftDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=giftdefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
		$this->noAdd = true;
	 	$this->edit = false;
	 	$this->delete = true;
		$this->noLink = true;
		$this->fieldsDisplay = array(
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


		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&token='.$this->token.'">'.$this->l('Coupons').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Gift Certificates').'</span> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=orderdefault&token='.$this->token.'">'.$this->l('Orders').'</a> &nbsp; | &nbsp; 
				</div><br /><br />
				'.$html;*/
		
	}
	public function _displayhistoryorder() { return $this->_displayHistoryOrderDefault(); }
	public function _displayHistoryOrderDefault() {
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$currentIndex = $this->setcurrentindex('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=orderdefault');
		//$this->table = 'awocoupon';
		$this->identifier = 'id';
	 	$this->edit = false;
	 	$this->delete = false;
		$this->noLink = true;
		$this->fieldsDisplay = array(
			//'id' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25,'mytype'=>'int'),
			'order_id' => array('title' => $this->l('Order ID'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','mytype'=>'int'),
			'codes' => array('title' => $this->l('Codes'), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>'),
			'button' => array('title' => $this->l(''), 'width' => 85, 'prefix' => '<span class="">', 'suffix' => '</span>','search'=>false,'orderby'=>false,),
		);		
			
		
		$this->real_processFilter();

		$model = new AwoCouponModelHistory();
		$this->_list = $model->getEntriesOrder($this->my_filters);
		$this->_listTotal = $model->getTotalOrder($this->my_filters);

		ob_start();
		$this->displayList();
		$html = ob_get_contents();
		ob_end_clean();
		
		return '
		<script type="text/javascript" src="'.AWO_URI.'/media/js/awocoupon.js"></script>
		<br /><a href="'.$currentIndex.'&add&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />
				'.$html;
		/*return '<div class="path_bar" style="background-color:#ECEADE;margin-bottom:0;margin-left:10px;">
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&token='.$this->token.'">'.$this->l('Coupons').'</a> &nbsp; | &nbsp; 
					<a href="index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=giftdefault&token='.$this->token.'">'.$this->l('Gift Certificates').'</a> &nbsp; | &nbsp; 
					<span class="current">'.$this->l('Orders').'</span> &nbsp; | &nbsp; 
				</div><br /><br />
				'.$html;*/
		
	}
	public function _displayHistoryOrderEdit() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelHistory();
		$row = $model->getEntryOrder();
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }		
				
		
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'coupon_template_id', 'class="inputbox" size="1""', null, '- '.$this->l('Select Template').' -' ,'id','coupon_code');

		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		
		$smarty->assign(array(
			'row'=>$row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=order&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'historyorder_edit');
	}

	
	public function _displayImportDefault() {
		global $smarty;

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/import.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$model = new AwoCouponModelImport();
		
		$row = new stdclass;
		$row->exclude_first_row = '1';
		$row->store_none_errors = '1';
		
		$post = awoHelper::getValues($_POST);
		if ( $post ) { $row = (object) array_merge((array) $row, (array) $post); }				

		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row' => $row,
			'ajax_url'=>AWO_URI.'/ajax.php',
			'errors'=>$errors,
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'import_default');

	}


	public function _displayReportDefault() {
		global $smarty;

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		
		$model = new AwoCouponModelReport();
		

		$lists['published'] = awoHelper::DD(awoHelper::vars('published'), 'published', 'class="inputbox" size="1"', null, '- '.$this->l('Select Status').' -' );
		$lists['coupon_value_type'] = awoHelper::DD(awoHelper::vars('coupon_value_type'), 'coupon_value_type', 'class="inputbox" size="1"', null, '- '.$this->l('Select Percent or Amount').' -' );
		$lists['discount_type'] = awoHelper::DD(awoHelper::vars('discount_type'), 'discount_type', 'class="inputbox" size="1""', null, '- '.$this->l('Select Discount Type').' -' );
		$lists['function_type'] = awoHelper::DD(awoHelper::vars('function_type'), 'function_type', 'class="inputbox" size="1""', null, '- '.$this->l('Select Function Type').' -' );		
		$lists['giftcert_product'] = awoHelper::DD( $model->getGiftCertProducts(), 'giftcert_product', 'class="inputbox" size="1""', null, '- '.$this->l('Select Gift Certificate Product').' -','product_id','product_name' );
		$lists['templatelist'] = awoHelper::DD($model->getTemplateList(), 'templatelist', 'class="inputbox" size="1""', null, '- '.$this->l('Select Template').' -' ,'id','coupon_code');
		$lists['order_status'] = awoHelper::DD($model->getOrderstatuses(), 'order_status[]', 'class="inputbox" size="5" MULTIPLE style="width:350px;"' , null, null,'id_order_state','name' );
		$shoplist = $model->getShopList();
		$lists['shop_list'] = empty($shoplist) ? '' : awoHelper::DD($shoplist, 'shoplist', 'class="inputbox" size="1" ' , null, '- '.$this->l('Select Shop').' -','id_shop','name' );

		$smarty->assign(array(
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_default');

	}


	public function _displayAboutDefault() {
		return '
			<br /><br />
			<table cellpadding="4" cellspacing="0" border="0" width="100%">
			<tr><td width="100%"><img src="'.AWO_URI.'/media/img/logo.png" style="margin-left:10px;"></td></tr>
			<tr><td>
				<blockquote>
					<p>AwoCoupon Pro is created by Seyi Awofadeju.</p>
					<p>Please visit <a href="http://awodev.com" target="_blank">http://awodev.com </a>to find out more about us.</p>
					<p>&nbsp;</p>
				</blockquote>
			</td></tr>
			<tr><td><div style="font-weight: 700;">Version: '.$this->awocoupon->version.'</div></td></tr>
			</table>
		';
	}


	public function _displayConfigDefault() {
		global $smarty;

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/config.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		
		$model = new AwoCouponModelConfig();
		$row = $model->getEntry();

		$yes_no = array(0=>$this->l('No'),1=>$this->l('Yes'));
		$lists['enable_store_coupon'] = awoHelper::DD($yes_no, 'params[enable_store_coupon]', 'class="inputbox" size="1"', $row->enable_store_coupon );
		$lists['enable_giftcert_discount_before_tax'] = awoHelper::DD($yes_no, 'params[enable_giftcert_discount_before_tax]', 'class="inputbox" size="1"', $row->enable_giftcert_discount_before_tax );
		$lists['enable_coupon_discount_before_tax'] = awoHelper::DD($yes_no, 'params[enable_coupon_discount_before_tax]', 'class="inputbox" size="1"', $row->enable_coupon_discount_before_tax );
		$lists['enable_multiple_coupon'] = awoHelper::DD($yes_no, 'params[enable_multiple_coupon]', 'class="inputbox" size="1" ',$row->enable_multiple_coupon );		
		$lists['casesensitive'] = awoHelper::DD($yes_no, 'casesensitive', 'class="inputbox" size="1" ', $row->casesensitive );
		$lists['giftcert_vendor_enable'] = awoHelper::DD($yes_no, 'params[giftcert_vendor_enable]', 'class="inputbox" size="1" ', $row->giftcert_vendor_enable );		
		$lists['enable_frontend_image'] = awoHelper::DD($yes_no, 'params[enable_frontend_image]', 'class="inputbox" size="1" ', isset($row->enable_frontend_image) ? $row->enable_frontend_image : '' );		
		$lists['giftcert_coupon_activate'] = awoHelper::DD($yes_no, 'params[giftcert_coupon_activate]', 'class="inputbox" size="1" ', isset($row->giftcert_coupon_activate) ? $row->giftcert_coupon_activate : '' );		

		$lists['csvDelimiter'] = awoHelper::DD(array(','=>',',';'=>';'), 'params[csvDelimiter]', 'class="inputbox" size="1" ', $row->csvDelimiter );		
		

		global $cookie;
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER["PHP_SELF"]);

		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row' => $row,
			'lists' => $lists,
			'ajax_url'=>AWO_URI.'/ajax.php',
			'errors'=>$errors,
			
			'base_url'=>__PS_BASE_URI__,
			'isoTinyMCE'=>$isoTinyMCE,
			'theme_path'=>_THEME_CSS_DIR_,
			'tiny_ad'=>$ad,
			'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
			'languages'=>Language::getLanguages(),
		));

		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'config_default');

	}
	
	
	public function _displayLicenseDefault() {
		global $smarty;
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		
		$model = new AwoCouponModelLicense();
		$row = $model->getData();
					
		
		ob_start();
		$this->displayErrors();
		$errors = ob_get_contents();
		ob_end_clean();
		

		$smarty->assign(array(
			'row'=>$row,
			'ajax_url'=>AWO_URI.'/ajax.php',
			'current_time'=>time(),
			
			'base_url'=>__PS_BASE_URI__,
		
			'back_url'=>'index.php?tab=AdminAwoCoupon&module=awocoupon&view=license&token='.$this->token,
			'back_img'=>'../img/admin/arrow2.gif',
		
			'errors'=>$errors,
		));


		return $this->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'license_edit');
		
	}
		
	
	
	
	public function _taskCouponEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelCoupon();
		if($model->store($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskCouponDefaultPublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCoupon();
		if($model->publish($post,1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskCouponDefaultUnpublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCoupon();
		if($model->publish($post,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskCouponDefaultDelete() {
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelCoupon();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskCouponGenerateEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelCoupon();
		if($model->storeGeneratecoupons($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskCouponDefaultDuplicate() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$get = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCoupon();
		if ($model->duplicatecoupon($get)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=5&token='.$this->token);
		else $this->_errors = $model->_errors;
	}
	
	
	public function _taskCouponAutoEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelCouponAuto();
		if($model->store($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskCouponAutoDefaultPublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCouponAuto();
		if($model->publish($post,1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskCouponAutoDefaultUnpublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelCouponAuto();
		if($model->publish($post,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskCouponAutoDefaultDelete() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/couponauto.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelCouponAuto();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=couponauto&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	
	
	
	public function _taskGiftcertEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelGiftcert();
		if($model->store($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskGiftcertDefaultPublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelGiftcert();
		if($model->publish($post,1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskGiftcertDefaultUnpublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelGiftcert();
		if($model->publish($post,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskGiftcertDefaultDelete() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelGiftcert();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}

	public function _taskGiftcertCodeEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';

		$post = awoHelper::getValues($_POST);
		
		$data = array();
		$file = $_FILES;
		$exclude_first_row = Tools::getValue('exclude_first_row','');
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		$delimiter = $params->get('csvDelimiter', ',') ;
		

		if(strtolower(substr($file['file']['name'],-4))=='.csv') {
			ini_set('auto_detect_line_endings',TRUE); //needed for mac users
			if (($handle = fopen($file['file']['tmp_name'], "r")) !== FALSE) {
				while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
					if(count($row)>3) $row = array_slice($row,0,3);
					$data[] = $row;
				}
				fclose($handle);
			}
		}
		if(!empty($exclude_first_row)) array_shift($data);
		if(empty($data)) $this->_errors[] = $this->l('Empty Import File');
		else {
			$post['data'] = $data;
			$model = new AwoCouponModelGiftcert();
			if($model->storeCode($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&conf=3&token='.$this->token);
			else {
				$this->_errors = $model->_errors;
			}
		}
	}
	public function _taskGiftcertCodeDefaultPublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelGiftcert();
		if($model->publishCode($post,1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskGiftcertCodeDefaultUnpublish() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_GET);
		$model = new AwoCouponModelGiftcert();
		if($model->publishCode($post,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&conf=5&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskGiftcertCodeDefaultDelete() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/giftcert.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelGiftcert();
		if($model->deleteCode($ids)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=giftcert&layout=codedefault&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	
	public function _taskProfileEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelProfile();
		if($model->store($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskProfileDefaultDelete() {
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';

		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelProfile();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskProfileDefaultDuplicate() {
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';
		
		$get = awoHelper::getValues($_GET);
		$model = new AwoCouponModelProfile();
		if($model->duplicate($get)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile&conf=19&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskProfileDefaultMakeDefault() {
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/profile.php';

		$post = awoHelper::getValues($_REQUEST);
		
		$ids = $post['Box'] ;
		$id = current($ids);
		$model = new AwoCouponModelProfile();
		if($model->makedefault($id)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=profile&conf=4&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	
	public function _taskHistoryHiststore() { $this->_taskHistoryEditStore(); }
	public function _taskHistoryEditStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelHistory();
		if($model->storehist($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskHistoryDefaultDelete() {
	//echo '<pre>'; print_r($_POST); exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelHistory();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskHistoryGiftDefaultDelete() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
		
		$post = awoHelper::getValues($_REQUEST);
		$ids = isset($post['submitDel']) ? $post['Box'] : array($post['id']);
		$model = new AwoCouponModelCoupon();
		if($model->delete($ids,-1)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=giftdefault&conf=1&token='.$this->token);
		else {
			$this->_errors = $model->errors;
		}
	}
	public function _taskHistoryOrderEditStore() {//exit;
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelHistory();
		if($model->storeorder($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=order&conf=3&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	
	public function _taskImportDefaultStore() {
		header('Content-type: text/html; charset=utf-8');

		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/import.php';
		$model = new AwoCouponModelImport();


		$data = array();
		$file = $_FILES;
		
		$exclude_first_row = Tools::getValue('exclude_first_row','');
		$store_none_errors = Tools::getValue('store_none_errors','');

		if(strtolower(substr($file['file']['name'],-4))=='.csv') {
			ini_set('auto_detect_line_endings',TRUE); //needed for mac users
			if (($handle = fopen($file['file']['tmp_name'], "r")) !== FALSE) {
				require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
				$params = new awoParams();
				$delimiter = $params->get('csvDelimiter', ',') ;
				
				while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
					$data[] = $row;
				}
				fclose($handle);
			}
		}
		if(!empty($exclude_first_row)) array_shift($data);
		
		if(empty($data)) {
			$this->_errors[] = $this->l('Empty Import File');
		} else {
		
			$errors = $model->store($data,$store_none_errors);
			
			if(empty($errors)) {
				//$this->setRedirect('index.php?option=com_awocoupon&view=coupons', JText::_( 'COM_AWOCOUPON_MSG_DATA_SAVED' ));
				Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=coupon&conf=101&token='.$this->token);
				
			} else {
				foreach($errors as $id=>$errarray) {
					$errText = '<br /><div>ID: '.$id.'<hr /></div>';
					foreach($errarray as $err) $errText .= '<div style="padding-left:20px;">-- '.$err.'</div>';
					$this->_errors[] = $errText;
				}
				//$msg = empty($store_none_errors) ? '' : JText::_( 'COM_AWOCOUPON_IMP_SAVED_NO_ERRS');
				//$this->setRedirect('index.php?option=com_awocoupon&view=import'.(empty($exclude_first_row) ? '&exclude_first_row=' : '').(empty($store_none_errors) ? '&store_none_errors=' : ''), $msg );
			}
		}


	}

	public function _taskConfigDefaultStore() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/config.php';
		
		$post = awoHelper::getValues($_POST);
		$model = new AwoCouponModelConfig();
		if($model->store($post)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=config&conf=6&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	
	public function _taskLicenseDefaultActivate() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if($model->activate()) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=cpanel&conf=102&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskLicenseDefaultDelete() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if($model->uninstall()) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=license&conf=101&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskLicenseDefaultUpdlocalkey() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ( $model->update_localkey($_POST['license'],$_POST['local_key']) ) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=cpanel&conf=101&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	public function _taskLicenseDefaultUpdexpired() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/license.php';
		
		$model = new AwoCouponModelLicense();
		if ( $model->check() ) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=license&conf=102&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	
	public function _taskHistoryOrderDefaultGiftcertResend() {
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/history.php';

		$order_id = (int)Tools::getValue('order_id');
		$model = new AwoCouponModelHistory();
		if($model->resend_giftcert($order_id)) Tools::redirectAdmin('index.php?tab=AdminAwoCoupon&module=awocoupon&view=history&layout=orderdefault&conf=104&token='.$this->token);
		else {
			$this->_errors = $model->_errors;
		}
	}
	
	
	
	
	
	
	
	private function setcurrentindex($val) {
		if(_PS_VERSION_ < '1.5') {
			global $currentIndex;
			$currentIndex = $val;
		}
		else self::$currentIndex = $val;
		
		return $val;
	}
	
	private function getcurrentindex() {
		if(_PS_VERSION_ < '1.5') {
			global $currentIndex;
			return $currentIndex;
		}
		else return self::$currentIndex;
	}
	
	
	
	
	
	protected function 	_displayDuplicate($token = NULL, $id) {

		$currentIndex = $this->getcurrentindex();
		
		$_cacheLang['Duplicate'] = $this->l('Duplicate');
		$_cacheLang['Copy images too?'] = $this->l('Are you sure?', __CLASS__, TRUE, FALSE);

		$duplicate = $currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;

		echo '
			<a class="pointer" onclick="if (confirm(\''.$_cacheLang['Copy images too?'].'\')) document.location = \''.$duplicate.'&token='.($token!=NULL ? $token : $this->token).'\';">
			<img src="../img/admin/duplicate.png" alt="'.$_cacheLang['Duplicate'].'" title="'.$_cacheLang['Duplicate'].'" /></a>';
	}
	public function displayListFooter($token = NULL)
	{
		echo '</table><p>';
		if ($this->delete)
			echo '<input type="submit" class="button" name="submitDel'.$this->table.'" value="'.$this->l('Delete selection').'" onclick="return confirm(\''.$this->l('Delete selected items?', __CLASS__, TRUE, FALSE).'\');" />';
		if(!empty($this->customlistfooter)) echo $this->customlistfooter;
		echo '</p>
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" />
		</form>';
		if (isset($this->_includeTab) AND sizeof($this->_includeTab))
			echo '<br /><br />';
	}

	public function processFilter() {
		$prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this))).'_'.$this->view_final.'_';		
		// Filter memorization
		foreach ($_POST as $key => $value) {
			if (stripos($key, 'Filter_') === 0)
				$this->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
			elseif(stripos($key, 'submitFilter') === 0)
				$this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
		}
		foreach ($_GET as $key => $value)
			if (stripos($key, 'OrderBy') === 0 || stripos($key, 'Orderway') === 0)
				$this->context->cookie->{$prefix.$key} = $value;

		// add filters to get so filters can be displayed in filter boxes
		$filters = $this->context->cookie->getFamily($prefix.'Filter_');
		foreach($filters as $key=>$value) {
			$_GET[str_replace($prefix,'',$key)] = $value;
		}
	}
	public function real_processFilter() {
		
		$this->my_filters = new stdClass();
	
		$prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this))).'_'.$this->view_final.'_';		
		$this->my_filters->filters = $this->context->cookie->getFamily($prefix.'Filter_');

		$this->my_filters->where = $this->my_filters->having = '';
		foreach ($this->my_filters->filters as $key => $value) {
			if ($value != null && !strncmp($key, $prefix.'Filter_', 7 + Tools::strlen($prefix))) {
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
					$check_key = ($key == $this->identifier || $key == '`'.$this->identifier.'`');
					
					$tprefix = array_key_exists('tprefix',$field) ? $field['tprefix'].'.' : '';

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
		$limit = !isset($this->context->cookie->awocoupon_pagination) ? $this->_pagination[0] : $this->context->cookie->awocoupon_pagination;
		$limit = (int)(Tools::getValue('pagination', $limit));
		$this->context->cookie->awocoupon_pagination = $limit;
		$this->context->cookie->_pagination = $limit;
		$this->my_filters->limit = $limit;
		
		
		// get list start
		$start = 0;
		if ((Tools::getIsset('submitFilter') OR Tools::getIsset('submitFilter_x') OR Tools::getIsset('submitFilter_y'))
			AND Tools::getValue('submitFilter','')!=''AND Tools::getValue('submitFilter','')!=0 
			AND	is_numeric(Tools::getValue('submitFilter'))) $start = (int)(Tools::getValue('submitFilter') - 1) * $this->my_filters->limit;
		$this->my_filters->start = $start;
		
		
		// get order by
		if ($this->context->cookie->{$prefix.'Orderby'}) $order_by = $this->context->cookie->{$prefix.'Orderby'};
		elseif ($this->_orderBy) $order_by = $this->_orderBy;
		else $order_by = $this->_defaultOrderBy;
		$this->my_filters->orderby = $order_by;

		if ($this->context->cookie->{$prefix.'Orderway'}) $order_way = $this->context->cookie->{$prefix.'Orderway'};
		elseif ($this->_orderWay) $order_way = $this->_orderWay;
		else $order_way = $this->_defaultOrderWay;
		$this->my_filters->orderway = $order_way;
		
		$this->my_filters->orderbystr = !empty($this->my_filters->orderby) ? ' ORDER BY '.pSQL($this->my_filters->orderby).' '.pSQL($this->my_filters->orderway) : '';
		
//printr($filters);
//printrx($this->context->cookie);
	}

}
