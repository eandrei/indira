<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/


// Can't use Tools at this time... Need to know if _PS_ADMIN_DIR_ has to be defined
$method = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));
require_once(realpath(dirname(__FILE__).'/../../init.php'));
require_once(realpath(dirname(__FILE__).'/../../modules/awocoupon/awocoupon.php'));

if (!defined('_PS_VERSION_') || (class_exists('Context') && is_object(Context::getContext()->customer) && !Tools::getToken(FALSE, Context::getContext()))) exit;
if(_PS_VERSION_>='1.5') exit;



if(!class_exists('awoHelper')) require dirname(__FILE__).'/lib/awohelper.php';
if(!class_exists('AwoCouponModelLicense')) require dirname(__FILE__).'/classes/admin/license.php';


switch($method) {
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
		global $cookie;
		$q =Tools::getValue('term');
		//trigger_error(print_r($_GET,1));
		if(empty($q) || strlen($q)<2) exit;

		$type =Tools::getValue('type');
		
		$result = array();
		$dbresults = array();
		switch($type) {
			case 'shop':
				$sql = 'SELECT id_shop AS id,name AS label 
						  FROM '._DB_PREFIX_.'shop
						 WHERE 1=1 AND active=1 AND deleted=0
						 AND name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,id
						 LIMIT 25';
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'product':
				$sql = 'SELECT p.id_product AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'product p
						  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
						 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$cookie->id_lang.'"
						 AND lang.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,p.id_product
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'productgift':
				$sql = 'SELECT p.id_product AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'product p
						  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
						  LEFT JOIN '._DB_PREFIX_.'awocoupon_giftcert_product g ON g.product_id=p.id_product
						 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$cookie->id_lang.'" AND g.product_id IS NULL
						 AND lang.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,p.id_product
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'category':
				$sql = 'SELECT c.id_category AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'category c
						  JOIN `'._DB_PREFIX_.'category_lang` as lang using (`id_category`)
						 WHERE 1=1 AND c.active=1 AND lang.id_lang="'.(int)$cookie->id_lang.'"
						 AND lang.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,c.id_category
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'manufacturer':
				$sql = 'SELECT m.id_manufacturer AS id,m.name AS label 
						  FROM '._DB_PREFIX_.'manufacturer m
						 WHERE 1=1 AND m.active=1
						 AND m.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,m.id_manufacturer
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'vendor':
				$sql = 'SELECT s.id_supplier AS id,s.name AS label 
						  FROM '._DB_PREFIX_.'supplier s
						 WHERE 1=1 AND s.active=1
						 AND s.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,s.id_supplier
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'shipping':
				$sql = 'SELECT s.id_carrier AS id, s.name AS label
						  FROM '._DB_PREFIX_.'carrier s
						 WHERE s.active=1 AND deleted=0
						 AND s.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,id
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'user': 
				$sql = 'SELECT u.id_customer as id,CONCAT(u.lastname," ",u.firstname) AS label
						  FROM '._DB_PREFIX_.'customer u 
						 WHERE 1=1 AND u.active=1 AND u.deleted=0
						 AND CONCAT(u.lastname," ",u.firstname) LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,id
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			
			case 'usergroup': 
				$sql = 'SELECT g.id_group as id,g_l.name as label
						  FROM '._DB_PREFIX_.'group g
						  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=g.id_group
						 WHERE 1=1 AND g_l.id_lang="'.(int)$cookie->id_lang.'"
						 AND g_l.name LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%"
						 ORDER BY label,id
						 LIMIT 25';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'parent':
				$sql = 'SELECT id,coupon_code AS label
						  FROM '._DB_PREFIX_.'awocoupon
						 WHERE published=1 AND function_type!="parent" AND LOWER(coupon_code) LIKE "%'.awoHelper::escape(strtolower( $q ) ).'%" ORDER BY label,id LIMIT 25';
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'coupons':
				$sql = 'SELECT id,coupon_code AS label
						  FROM #__awocoupon
						 WHERE published=1 AND LOWER(coupon_code) LIKE "%'.awoHelper::escape(strtolower($q)).'%" ORDER BY label,id LIMIT 25';
				$dbresults = awoHelper::loadObjectList($sql, 'id');
				break;
		}
		if(!empty($dbresults)) {
			foreach($dbresults as $row) array_push($result, array("id"=>$row->id, "label"=>$row->label, "value" => strip_tags($row->label)));
		}

		echo json_encode($result);
		exit;	
		break;
	}
	
	
	case 'ajax_elements_all': {
		$type =Tools::getValue('type');
		
		$result = array();
		$dbresults = array();
		switch($type) {
			case 'shop':
				$sql = 'SELECT id_shop AS id,name AS label 
						  FROM '._DB_PREFIX_.'shop
						 WHERE 1=1 AND active=1 AND deleted=0
						 ORDER BY label,id';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'product':
				$sql = 'SELECT p.id_product AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'product p
						  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
						 WHERE 1=1 AND p.active=1 AND lang.id_lang="'.(int)$cookie->id_lang.'"
						 ORDER BY label,p.id_product';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'category':
				$sql = 'SELECT c.id_category AS id,lang.name AS label 
						  FROM '._DB_PREFIX_.'category c
						  JOIN `'._DB_PREFIX_.'category_lang` as lang using (`id_category`)
						 WHERE 1=1 AND c.active=1 AND lang.id_lang="'.(int)$cookie->id_lang.'"
						 ORDER BY label,c.id_category';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'manufacturer':
				$sql = 'SELECT m.id_manufacturer AS id,m.name AS label 
						  FROM '._DB_PREFIX_.'manufacturer m
						 WHERE 1=1 AND m.active=1
						 ORDER BY label,m.id_manufacturer';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'vendor':
				$sql = 'SELECT s.id_supplier AS id,s.name AS label 
						  FROM '._DB_PREFIX_.'supplier s
						 WHERE 1=1 AND s.active=1
						 ORDER BY label,s.id_supplier';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'shipping':
				$sql = 'SELECT s.id_carrier AS id, s.name AS label
						  FROM '._DB_PREFIX_.'carrier s
						 WHERE s.active=1 AND deleted=0
						 ORDER BY label,id';
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'user': 
				$sql = 'SELECT u.id_customer as id,CONCAT(u.lastname," ",u.firstname) AS label
						  FROM '._DB_PREFIX_.'customer u 
						 WHERE 1=1 AND u.active=1 AND u.deleted=0
						 ORDER BY label,id';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'usergroup': 
				$sql = 'SELECT g.id_group as id,g_l.name as label
						  FROM '._DB_PREFIX_.'group g
						  JOIN '._DB_PREFIX_.'group_lang g_l ON g_l.id_group=g.id_group
						 WHERE 1=1 AND g_l.id_lang="'.(int)$cookie->id_lang.'"
						 ORDER BY label,id';//trigger_error($sql);
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'parent':
				$sql = 'SELECT id,coupon_code AS label
						  FROM '._DB_PREFIX_.'awocoupon
						 WHERE published=1 AND function_type!="parent" 
						 ORDER BY label,id';
				$dbresults = awoHelper::loadObjectList($sql,'id');
				break;
			case 'countrystate':
				$country_ids =Tools::getValue('country_id');
				foreach($country_ids as $country_id) {
					$country_id = (int)$country_id;
					if($country_id>0) {
						$result[$country_id] = awohelper::getCountryStateList($country_id);
					}
				}
				break;
		}
		if(!empty($dbresults)) {
			foreach($dbresults as $row) array_push($result, array("id"=>$row->id, "label"=>$row->label, "value" => strip_tags($row->label)));
		}
		
		echo json_encode($result);
		exit;
		break;
	}
	
	
	case 'previewprofileid': {
		$profile_id =Tools::getValue('id');
		$image = awoHelper::writeToImage('ABSIE@SD12bSeA','25.00 USD',1462304000,'screen',null,$profile_id);
		if($image === false) exit;
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Content-type: image/png');
		if($image === false) echo 'error';
		else {
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
		if(empty($profile['image'])) exit;
	
		list($x1,$x2,$x3,$x4,$x5,$x6) = explode('|',$get['code']);
		$profile['coupon_code_config'] = array('align'=>$x1,'pad'=>$x2,'y'=>$x3,'font'=>$x4,'size'=>$x5,'color'=>$x6,);
		list($x1,$x2,$x3,$x4,$x5,$x6) = explode('|',$get['value']);
		$profile['coupon_value_config'] = array('align'=>$x1,'pad'=>$x2,'y'=>$x3,'font'=>$x4,'size'=>$x5,'color'=>$x6,);
		
		if(!empty($get['expiration'])) {
			list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|',$get['expiration']);
			$profile['expiration_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
		}
		if(!empty($get['freetext1'])) {
			list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|',$get['freetext1']);
			$profile['freetext1_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
		}
		if(!empty($get['freetext2'])) {
			list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|',$get['freetext2']);
			$profile['freetext2_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
		}
		if(!empty($get['freetext3'])) {
			list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|',$get['freetext3']);
			$profile['freetext3_config'] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
		}
		
		if(!empty($get['imgplugin']) && is_array($get['imgplugin'])) {
			foreach($get['imgplugin'] as $k=>$r) {foreach($r as $k2=>$r2) {
				list($x1,$x2,$x3,$x4,$x5,$x6,$x7) = explode('|',$r2);
				$profile['imgplugin'][$k][$k2] = array('text'=>$x1,'align'=>$x2,'pad'=>$x3,'padding'=>$x3,'y'=>$x4,'font'=>$x5,'size'=>$x6,'color'=>$x7,);
			}}
		}

		$image = awoHelper::writeToImage('ABSIE@SD12bSeA','25.00 USD',1462304000,'screen',$profile);
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Content-type: image/png');
		if($image === false) echo 'error';
		else {
			imagepng($image);					// save image to file
			imagedestroy($image);				// destroy resource
		}
		exit;
	}
	
	
	case 'runreport': {
		global $smarty;
		
		function getUserParameters() {
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
	
		function reportgrid($name,$ardata,$arlabels,$arcolumns,$arrstyle=array()) {
			
			if(!empty($ardata) && !empty($arlabels) && !empty($arcolumns) 
			&& is_array($ardata) && is_array($arlabels) && is_array($arcolumns)) {

		//array("6|color:red;text-align:right;","7|color:green;text-align:right;","8|text-align:right;"));
				$header = '<div class="gridOuter"><div id="'.$name.'" class="gridInner"><table><thead><tr><td>&nbsp;</td>';
				foreach ($arlabels as $val) $header .= '<td>'.$val.'</td>';
				$header .= '</tr></thead>';
				
				//INITIALIZE ROW DATA
				$rowdata = '';
				foreach($arcolumns as $key=>$col) {
					$rowdata .= '<td '.(isset($arrstyle[$key]) ? ' style=\"'.$arrstyle[$key].'\" ' : '').'>{$line[\''.$col.'\']}</td>';
				}
				$rowdata = '<tr ".($i%2==0 ? \'class="alt"\' : \'\')."><td class=\'count\'>$i</td>'.$rowdata.'</tr>';


				$i = 1;
				$body = '<tbody>'; 
				foreach($ardata as $line) {
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

		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$model = new AwoCouponModelReport();
		$report_type =Tools::getValue('report_type');
		
		
		switch($report_type) {
			case 'coupon_list': {
				$labels = array('~ID',$model->awocoupon->l('Coupon Code'),$model->awocoupon->l('Published'),$model->awocoupon->l('Function Type'),
					$model->awocoupon->l('Percent or Amount'),$model->awocoupon->l('Discount Type'),
					$model->awocoupon->l('Value'),$model->awocoupon->l('Value Definition'),
					$model->awocoupon->l('Number of Uses Total'),$model->awocoupon->l('Number of uses Per'),
					$model->awocoupon->l('Minumum Value'),$model->awocoupon->l('Minimum Value'),
					$model->awocoupon->l('Start Date'),$model->awocoupon->l('Expiration'),
					$model->awocoupon->l('Customers'),$model->awocoupon->l('Customers'),
			
			
					$model->awocoupon->l('Asset - Type'),$model->awocoupon->l('Asset'),$model->awocoupon->l('Asset - Number'),$model->awocoupon->l('Asset'),
					$model->awocoupon->l('Asset 2 - Type'),$model->awocoupon->l('Asset 2'),$model->awocoupon->l('Asset 2 - Number'),$model->awocoupon->l('Asset 2'),
					$model->awocoupon->l('Exclude Special'),$model->awocoupon->l('Exclude Gift Certificate'),$model->awocoupon->l('Admin Note'),
					$model->awocoupon->l('Process Type'),$model->awocoupon->l('Maximum Qty Discount'),$model->awocoupon->l('Do not Mix Products'),
					$model->awocoupon->l('Automatically add to cart "Get Y" product'),
					$model->awocoupon->l('Country/State'),$model->awocoupon->l('Country'),$model->awocoupon->l('State'),
					$model->awocoupon->l('Minimum Product Quantity'),$model->awocoupon->l('Minimum Product Quantity'),
					$model->awocoupon->l('Description'),$model->awocoupon->l('Tags'),
			
					$model->awocoupon->l('Secret Key'),$model->awocoupon->l('Customers'),$model->awocoupon->l('Asset'),$model->awocoupon->l('Asset2'),
					$model->awocoupon->l('Country'),$model->awocoupon->l('State'),
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
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					
				}
				$parameters = getUserParameters();
				
				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
					'pagination' => $model->getPagination(),
					'arrstr' => $arrstr,
					'is_empty' => !empty($arrstr) ? '' : '1',
					'awo_uri'=>AWO_URI,
					
					'labels'=>htmlentities(json_encode($labels)),
					'columns'=>htmlentities(json_encode($columns)),
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runCouponList');
				exit;
				
				break;
			}
			case 'purchased_giftcert_list': {
				$labels = array($model->awocoupon->l('Coupon Code'),$model->awocoupon->l('Product'),$model->awocoupon->l('Value'),$model->awocoupon->l('Expiration'),
							$model->awocoupon->l('CustomerID'),$model->awocoupon->l('Last Name'),$model->awocoupon->l('First Name'),$model->awocoupon->l('Email'),
							$model->awocoupon->l('order ID'),$model->awocoupon->l('Order Date'),$model->awocoupon->l('Order Total'),);
				$columns = array('coupon_code','product_name','coupon_valuestr','expiration','user_id','last_name','first_name','email','order_number','order_date','order_total',);

				$row = $model->getData($report_type);
				$arrstr = array();
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					
				}
				$parameters = getUserParameters();
				
				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
					'pagination' => $model->getPagination(),
					'arrstr' => $arrstr,
					'is_empty' => !empty($arrstr) ? '' : '1',
					'awo_uri'=>AWO_URI,
					
					'start_date'=>Tools::getValue('start_date'),
					'end_date'=>Tools::getValue('end_date'),
					'order_status'=>Tools::getValue('order_status'),
					
					'labels'=>htmlentities(json_encode($labels)),
					'columns'=>htmlentities(json_encode($columns)),
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runPurchasedGiftcert');
				exit;
				
				break;
			}
			case 'coupon_vs_total': {
				$labels = array($model->awocoupon->l('Coupon Code'),$model->awocoupon->l('Discount'),$model->awocoupon->l('Revenue'),$model->awocoupon->l('Volume'), '% '.$model->awocoupon->l('Revenue'), '% '.$model->awocoupon->l('Volume'));
				$columns = array('coupon_code','discountstr','totalstr','count','alltotal','allcount' );

				$row = $model->getData($report_type);
				$arrstr = array(); $barvolume = $barrevenue = '';
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					$barvolume = $barrevenue = '';
					//echo '<pre>'; print_r($row); exit;
					foreach($row->rows as $r) {
						$barvolume .= '<tr><td width="100">'.$r['coupon_code'].'</td>
									<td width="300" class="bar"><div style="width: '.round($r['count']/$row->count*100).'%"></div>'.$r['count'].'</td>
									<td>'.round($r['count']/$row->count*100).'%</td>
							</tr>';
						$barrevenue .= '<tr><td width="100">'.$r['coupon_code'].'</td>
									<td width="300" class="bar"><div style="width: '.round($r['total']/$row->total*100).'%"></div>'.number_format($r['total'],2).'</td>
									<td>'.round($r['total']/$row->total*100).'%</td>
							</tr>';
					}
					
				}
				$parameters = getUserParameters();
				
				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
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
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runUsageTotal');
				exit;
				
				break;
			}
			
			case 'coupon_vs_location' : {
				$labels = array($model->awocoupon->l('Coupon Code'),$model->awocoupon->l('Country'),$model->awocoupon->l('State'),$model->awocoupon->l('City'),$model->awocoupon->l('Discount'),
								 $model->awocoupon->l('Total'),$model->awocoupon->l('Count'), '% '.$model->awocoupon->l('Total'), '% '.$model->awocoupon->l('Count'));
				$columns = array('coupon_code','country','state','city','discountstr','totalstr','count','alltotal','allcount' );

				$row = $model->getData($report_type);
				$arrstr = array();
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					
				}
				$parameters = getUserParameters();

				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
					'pagination' => $model->getPagination(),
					'arrstr' => $arrstr,
					'is_empty' => !empty($arrstr) ? '' : '1',
					'awo_uri'=>AWO_URI,
					
					'start_date'=>Tools::getValue('start_date'),
					'end_date'=>Tools::getValue('end_date'),
					'order_status'=>Tools::getValue('order_status'),
					
					'labels'=>htmlentities(json_encode($labels)),
					'columns'=>htmlentities(json_encode($columns)),
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runUsageLocation');
				exit;
				
				break;
			}
			case 'history_uses_coupons' : {
				$labels = array($model->awocoupon->l('Coupon Code'),'ID',
							$model->awocoupon->l('Last Name'),$model->awocoupon->l('First Name'),$model->awocoupon->l('Discount'),
							$model->awocoupon->l('Order ID'),$model->awocoupon->l('Order Date'),$model->awocoupon->l('Order Total'),
							$model->awocoupon->l('Sub Total'),$model->awocoupon->l('Shipping'),$model->awocoupon->l('Fee'));
				$columns = array('coupon_code_str','user_id','last_name','first_name','discountstr','order_number','order_date',
								'total_paid','total_products_wt','total_shipping','order_fee');

				$row = $model->getData($report_type);
				$arrstr = array();
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					
				}
				$parameters = getUserParameters();

				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
					'pagination' => $model->getPagination(),
					'arrstr' => $arrstr,
					'is_empty' => !empty($arrstr) ? '' : '1',
					'awo_uri'=>AWO_URI,
					
					'start_date'=>Tools::getValue('start_date'),
					'end_date'=>Tools::getValue('end_date'),
					'order_status'=>Tools::getValue('order_status'),
					
					'labels'=>htmlentities(json_encode($labels)),
					'columns'=>htmlentities(json_encode($columns)),
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runHistoryCoupon');
				exit;
				
				break;
			}
			case 'history_uses_giftcerts' : {
				$labels = array($model->awocoupon->l('Coupon Code'),$model->awocoupon->l('Product'),$model->awocoupon->l('Value'),
							$model->awocoupon->l('Value Used'),$model->awocoupon->l('Balance'),$model->awocoupon->l('Expiration'),
							$model->awocoupon->l('Customer ID'),$model->awocoupon->l('Last Name'),$model->awocoupon->l('First Name'),
							$model->awocoupon->l('Order ID'),$model->awocoupon->l('Order Date'),);
				$columns = array('coupon_code','product_name','coupon_valuestr','coupon_value_usedstr','balancestr','expiration','user_id','last_name','first_name','order_number','order_date',);

				$row = $model->getData($report_type);
				$arrstr = array();
				if(!empty($row->rows)) {
					$style = null;
					$arrstr = reportgrid('grid',$row->rows,$labels,$columns,$style);
					
				}
				$parameters = getUserParameters();
				
				$smarty->assign(array(
					'report_type'=>$report_type,
					'row'=>$row,
					'parameters' =>getUserParameters(),
					'pagination' => $model->getPagination(),
					'arrstr' => $arrstr,
					'is_empty' => !empty($arrstr) ? '' : '1',
					'awo_uri'=>AWO_URI,
					
					'start_date'=>Tools::getValue('start_date'),
					'end_date'=>Tools::getValue('end_date'),
					'order_status'=>Tools::getValue('order_status'),
					
					'labels'=>htmlentities(json_encode($labels)),
					'columns'=>htmlentities(json_encode($columns)),
				));

				echo $model->awocoupon->fetchTemplate('/ps14/admin/tpl/', 'report_runHistoryGiftcert');
				exit;
				
				break;
			}
		}
		break;
	}

	case 'exportreports': {
	//exit('export to excel');
	
		
		require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/report.php';
		$model = new AwoCouponModelReport();
		$post = awoHelper::getValues($_POST);

		$file = $model->export($post);

		if(!empty($file)) {
			$filename =Tools::getValue('filename','file.csv');
			
			// required for IE, otherwise Content-disposition is ignored
			if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

			//  default: $ctype="application/force-download";
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers 
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=\"".$filename."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".strlen($file));
			echo $file;
			exit();
		}
		
		break;
	}

	case 'coupondetail': {
		$dir =Tools::getValue('dir');
		require_once _PS_MODULE_DIR_.'awocoupon/AdminAwoCoupon.php';
		
		global $_LANGADM;
		$_LANGADM = array();
		
		$model = new AdminAwoCoupon();
		$text = $model->_displayCouponDetailDefault();
		echo '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'/css/admin.css" /> 
				<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'/css/jquery.cluetip.css" /> 
				<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'/'.$dir.'/themes/oldschool/admin.css" /> 
				'.$text;
		exit;
		break;
	}
	
	
	default:
	
}

