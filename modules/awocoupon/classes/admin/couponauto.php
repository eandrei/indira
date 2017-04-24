<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelCouponAuto
{
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
	}

	public function getEntry()
	{
		$id = Tools::getValue('id', 0);
		$entry = awoHelper::dbTableRow('awocoupon_auto', 'id', $id);
		
		$entry->coupon_code = '';

		if (!empty($entry->id)) {
			$sql = 'SELECT coupon_code FROM #__awocoupon WHERE id='.$entry->coupon_id;
			$entry->coupon_code = awoHelper::loadResult($sql);
		}
		return $entry;
	}





	public function _buildQuery($params) {
		$sql = 'SELECT a.id,a.coupon_id,a.ordering,a.published,c.coupon_code,c.function_type,
						c.coupon_value_type,c.coupon_value,c.coupon_value_def,c.discount_type
				  FROM #__awocoupon_auto a
				  JOIN #__awocoupon c ON c.id=a.coupon_id
				 WHERE 1=1 '.$params->where.' 
				'.$params->orderbystr;
		return $sql;

		return $sql;
	}
	public function getEntries($params)
	{
		// Lets load the files if it doesn't already exist
		
		$query = $this->_buildQuery($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query, 'id');
		
		$ids = '';
		$data = array();
		foreach ($rows as $i => $row) {

			if ($row->published == 1) $published = '<a href="'.awohelper::admin_link().'&view=couponauto&id='.$row->id.'&task=unpublish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/published.png" width="16" height="16" class="hand" border="0" /></a>';
			elseif ($row->published == -2) $published = '<img src="'.AWO_URI.'/media/img/template.png" width="16" height="16" class="hand" border="0" />';
			else $published = '<a href="'.awohelper::admin_link().'&view=couponauto&id='.$row->id.'&task=publish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/unpublished.png" width="16" height="16" class="hand" border="0" /></a>';
				
				
			$function_type = awoHelper::vars('function_type', $row->function_type);
			$coupon_value_type = $row->function_type == 'parent' ? '': awoHelper::vars('coupon_value_type', $row->coupon_value_type);
			$coupon_value = !empty($row->coupon_value) ? number_format($row->coupon_value, 2): $row->coupon_value_def;


			$data[] = array(
				'id'=>$row->id,
				'coupon_code'=>$row->coupon_code,
				'function_type'=>$function_type,
				'coupon_value_type'=>$coupon_value_type,
				'coupon_value'=>$coupon_value,
				'ordering'=>$row->ordering,
				'published'=>$published,
			);
				
		}

			
		return $data;
	}
	public function getTotal($filters = array())
	{
		awoHelper::query($this->_buildQuery($filters));
		return Db::getInstance()->NumRows();
	}
	
	






	public function store($data)
	{
		$this->_errors = $this->storeEach($data);
		if (!empty($this->_errors))
			return false;
		
		return true;
	
	}
	public function storeEach($data, $error_check_only = false)
	{
		$errors = array();
//printrx($data);
		

		$row = awoHelper::dbTableRow('awocoupon_auto', 'id', 0);
		
		
		// bind it to the table
		if (!($row = awoHelper::dbbind($row, $data)))
			$errors[] = 'Unable to bind item';
	
		$row->ordering = (int)$row->ordering;
		if($row->ordering<=0) {
			$sql = 'SELECT MAX(ordering) FROM #__awocoupon_auto';
			$row->ordering = ((int)awoHelper::loadResult($sql)) + 1;
		}

		// sanitise fields
		$row->id 			= (int)$row->id;

		
		// Make sure the data is valid
		$tmperr = $this->store_validation($row);
		foreach ($tmperr as $err) $errors[] = $err;		
		
		// take a break and return if there are any errors
		if (!empty($errors) || $error_check_only) return $errors;
				
		
		$row = awoHelper::dbstore('awocoupon_auto', $row);

		
		return;
	}
	public function store_validation($row)
	{
		$err = array();
		
		
		if(empty($row->coupon_id) || !ctype_digit($row->coupon_id)) $this->awocoupon->l('Coupon : please enter a valid value');
		if (empty($row->published) || ($row->published != '1' && $row->published != '-1')) $err[] = $this->awocoupon->l('Published: please enter a valid value');
		if (!ctype_digit($row->ordering)) $this->awocoupon->l('Ordering: invalid');
		
		return $err;
		
	}






	public function publish($data, $publish = 1)
	{
		@$id = (int)$data['id'];
		awoHelper::query('UPDATE #__awocoupon_auto SET published='.(int)$publish.' WHERE id='.(int)$id);
		return true;
	}
	
	public function delete($cids)
	{		
		if (empty($cids) || !is_array($cids))
		{
			$this->errors = array('Invalid Items');
			return false;
		}
		
		foreach ($cids as $k => $v) $cids[$k] = (int)$v;
		$cids = implode(',', $cids);

		awoHelper::query('DELETE FROM #__awocoupon_auto WHERE id IN ('.$cids.')');

		return true;
	}
		
	
	
	
	
	
}
