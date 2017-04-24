<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/
 
if (!defined('_PS_VERSION_')) exit;


class AwoCouponMenu {
	function process() {
		$this->define_menu();
		$this->define_plugin_menu();
		return $this->print_menu();
	}
	
	
	function define_menu() {
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$myawocoupon = new AwoCoupon();
		
		$asset_path = __PS_BASE_URI__.'modules/awocoupon';
		
		if(version_compare(_PS_VERSION_,'1.5','>')) {
			$context = Context::getContext();
			$context->controller->addCSS($asset_path.'/media/css/style.css');
			$context->controller->addCSS($asset_path.'/media/css/menu.css');
			$context->controller->addJs($asset_path.'/media/js/bootstrap.min.js');
			$context->controller->addJs($asset_path.'/media/js/menu.js');
		}
		else {
			Tools::addCSS($asset_path.'/media/css/style.css');
			Tools::addCSS($asset_path.'/media/css/menu.css');
			Tools::addJs($asset_path.'/media/js/bootstrap.min.js');
			Tools::addJs($asset_path.'/media/js/menu.js');
		}
//$document->addCustomTag('<!--[if IE 7]><link rel="stylesheet" href="'.$asset_path.'/media/css/ie7.css" type="text/css" media="all" /><![endif]-->');
				
		$this->my_admin_link = awohelper::admin_link();

		$img_path = __PS_BASE_URI__.'modules/awocoupon/media/img';
		$token = Tools::getAdminTokenLite('AdminAwoCoupon');
		$this->menu_items = array(
			array($myawocoupon->l('AwoCoupon'),$this->my_admin_link.'&view=cpanel&token='.$token,$img_path.'/icon-16-awocoupon.png',array(
						array($myawocoupon->l('Dashboard'),$this->my_admin_link.'&view=cpanel&token='.$token,$img_path.'/icon-16-home.png'),
						array($myawocoupon->l('Configuration'),$this->my_admin_link.'&view=config&token='.$token,$img_path.'/icon-16-config.png'),
						array($myawocoupon->l('License'),$this->my_admin_link.'&view=license&token='.$token,$img_path.'/icon-16-license.png'),
						array($myawocoupon->l('About'),$this->my_admin_link.'&view=about&token='.$token,$img_path.'/icon-16-info.png'),
					),
				),
			array($myawocoupon->l('Coupons'),$this->my_admin_link.'&view=coupon&token='.$token,$img_path.'/icon-16-coupons.png',array(
						array($myawocoupon->l('New Coupon'),$this->my_admin_link.'&view=coupon&layout=edit&token='.$token,$img_path.'/icon-16-new.png'),
						array($myawocoupon->l('Coupons'),$this->my_admin_link.'&view=coupon&token='.$token,$img_path.'/icon-16-list.png'),
						array($myawocoupon->l('Automatic Discounts'),$this->my_admin_link.'&view=couponauto&token='.$token,$img_path.'/icon-16-auto.png'),
						array($myawocoupon->l('Generate Coupons'),$this->my_admin_link.'&view=coupon&layout=generateedit&token='.$token,$img_path.'/icon-16-copy.png'),
						array($myawocoupon->l('Import'),$this->my_admin_link.'&view=import&token='.$token,$img_path.'/icon-16-import.png'),
					),
				),
			array($myawocoupon->l('Tools'),'',$img_path.'/icon-16-tools.png',array(
						array($myawocoupon->l('New Gift Certificate Product'),$this->my_admin_link.'&view=giftcert&layout=edit&token='.$token,$img_path.'/icon-16-new.png'),
						array($myawocoupon->l('Gift Cerficate Products'),$this->my_admin_link.'&view=giftcert&token='.$token,$img_path.'/icon-16-giftcert.png'),
						array($myawocoupon->l('Codes'),$this->my_admin_link.'&view=giftcert&layout=codedefault&token='.$token,$img_path.'/icon-16-import.png'),
						array('--separator--'),
						array($myawocoupon->l('New Profile'),$this->my_admin_link.'&view=profile&layout=edit&token='.$token,$img_path.'/icon-16-new.png'),
						array($myawocoupon->l('Profiles'),$this->my_admin_link.'&view=profile&token='.$token,$img_path.'/icon-16-profile.png'),
						array('--separator--'),
						array($myawocoupon->l('Reports'),$this->my_admin_link.'&view=report&token='.$token,$img_path.'/icon-16-report.png'),
					),
				),
			array($myawocoupon->l('History of Uses'),$this->my_admin_link.'&view=history&token='.$token,$img_path.'/icon-16-history.png',array(
						array($myawocoupon->l('Coupons'),$this->my_admin_link.'&view=history&token='.$token,$img_path.'/icon-16-coupons.png'),
						array($myawocoupon->l('Gift Certificates'),$this->my_admin_link.'&view=history&layout=giftdefault&token='.$token,$img_path.'/icon-16-giftcert.png'),
						array($myawocoupon->l('Orders'),$this->my_admin_link.'&view=history&layout=orderdefault&token='.$token,$img_path.'/icon-16-cart.png'),
					),
				),
		);
	}
	
	function define_plugin_menu() {
	
		$files = array(
			'aworewardsmenu'=>_PS_MODULE_DIR_.'aworewards/lib/menu.php',
			'awoaffiliatemenu'=>_PS_MODULE_DIR_.'awoaffiliate/lib/menu.php',
		);
		foreach($files as $class=>$file) {
			if(file_exists($file)) {
				require_once $file;
				$this->menu_items[] = call_user_func(array($class, 'define_menu'));
			}
		}
	}

	function print_menu() {
	
		// get all the urls into an array
		$menu_urls = array();
		foreach($this->menu_items as $item) {
			//if(!empty($item[1])) $menu_urls[] = rtrim(preg_replace('/token=[^&]*/i', '', $item[1]),'&');
			if(!empty($item[1])) $menu_urls[] = $item[1];
			if(!empty($item[3]) && is_array($item[3])) {
				foreach($item[3] as $item2) {
					if(!empty($item2[1])) $menu_urls[] = $item2[1];
					if(!empty($item2[3]) && is_array($item2[3])) {
						foreach($item2[3] as $item3) {
							if(!empty($item3[1])) $menu_urls[] = $item3[1];
						}
					}
				}
			}
		}

		
		// set current url
		$current_url = '';
		preg_match('/index.php\?.*(token=[^&]*).*/',$_SERVER['REQUEST_URI'],$match);
		if(!empty($match)) {
			$current_url = $match[0];
			$token = $match[1];
			if(!in_array($current_url,$menu_urls)){
				$tab = Tools::getValue('tab');
				$module = Tools::getValue('module');
				$view = Tools::getValue('view');
				$layout = Tools::getValue('layout');
				$current_url = 'index.php?tab='.$tab.'&module='.$module;
				if(empty($view)) $current_url .= '&view=cpanel';
				else {
					$current_url .= '&view='.$view;
					
					if(empty($layout)) {
						if((Tools::getIsset('add') || Tools::getIsset('update')) && in_array($current_url.'&layout=edit&'.$token,$menu_urls)) $current_url .= '&layout=edit';
					}
					elseif(!empty($layout)) {//exit($current_url.'&layout='.$layout.'&add&'.$token);
						if(in_array($current_url.'&layout='.$layout.'&'.$token,$menu_urls)
						|| in_array($current_url.'&layout='.$layout.'&add&'.$token,$menu_urls)
						|| in_array($current_url.'&layout='.$layout.'&edit&'.$token,$menu_urls)) $current_url .= '&layout='.$layout;
					}
				}
				$current_url .= '&'.$token;//echo $current_url;printrx($menu_urls);exit;
			}
		}
		
		
		// process
		$html_menu = '
			<div id="awomenu">
				<div id="awomenu_container">
					<div class="navbar">
						<div class="navbar-inner">
							<ul id="" class="nav" >
		';	
						
		foreach($this->menu_items as $item) {
			if(empty($item)) continue; 
			$is_active_1 = false;
			$html_menu_2 = '';
			if(!empty($item[3]) && is_array($item[3])) {
				$html_menu_2 = '<ul class="dropdown-menu">';
				foreach($item[3] as $item2) {
					if(empty($item2)) continue;
					if(!empty($item2[1]) && $current_url==$item2[1]) $is_active_1 = true;
					$is_active_2 = false;
					$html_menu_3 = '';
					if(!empty($item2[3]) && is_array($item2[3])) {
						$html_menu_3 = '<ul>';
						foreach($item2[3] as $item3) {
							if(empty($item3)) continue; 
							if(!empty($item3[1]) && $current_url==$item3[1]) $is_active_2 = true;
							$html_menu_3 .= $this->print_menu_helper($item3,3,$current_url).'</li>';
						}
						$html_menu_3 .= '</ul>';
					}
					$html_menu_2 .= $this->print_menu_helper($item2,2,$current_url,$is_active_2).$html_menu_3.'</li>';
				}
				$html_menu_2 .= '</ul>';
			}
			$html_menu .= $this->print_menu_helper($item,1,$current_url,$is_active_1).$html_menu_2.'</li>';
		}
		$html_menu .= '</ul></div></div></div></div><div class="clr"></div>';
		
		return $html_menu;
	
	}

	function print_menu_helper($item,$level,$current_url,$force_active=false) {
		$html = '';
		$image = '';
		$a_class = '';
		if(!empty($item[2])) {
			if(substr($item[2],0,6)=='class:') $a_class = substr($item[2],6);
			else $image = '<img src="'.$item[2].'" class="tmb"/>';
		}
		else $image = '<div style="display:inline-block;width:16px;">&nbsp;</div>';
		
		$active_css = $force_active || (!empty($item[1]) && $current_url==$item[1]) ? 'current' : '';
		
		$html .= '<li class="';
		if($level==1) $html .= ' dropdown ';
		else {
			if($item[0]=='--separator--') $html .= ' divider ';
			else ;
		}
		$html .= $active_css;
		$html .= '">';
		
		if($item[0]!='--separator--') {
			$html .= '<a class="';
			//if($level==1) $html .= ' dropdown-toggle ';
			$html .= '" ';
			//if($level==1) $html .= 'data-toggle="dropdown"';
			$html .= ' href="'.(!empty($item[1]) ? $item[1] : '#').'"';
			$html .= '>'.$image.' '.$item[0].'</a>';
		}
		else $html .= '<span></span>';
		return $html;
	
	}
}
