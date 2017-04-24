<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelGiftCert
{
	var $_errors;

	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
		$c=new AwoCouponModelLicense();$myawo=$c->getlocalkey();if(!@eval($myawo->evaluation)){Tools::redirectAdmin(awohelper::admin_link().'&view=license&conf=103&token='.Tools::getAdminTokenLite('AdminAwoCoupon'));return;}
	}

	public function getEntryGift()
	{
		$id = Tools::getValue('id', 0);
		$entry = awoHelper::dbTableRow('awocoupon_giftcert_product', 'id', $id);
		
		$entry->product_name = '';

		if (!empty($entry->id))
		{
			$sql = 'SELECT lang.name
					  FROM '._DB_PREFIX_.'product p
					  JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
					 WHERE 1=1 AND p.id_product='.$entry->product_id;
			$entry->product_name = awoHelper::loadResult($sql);
		}

		return $entry;
	}
	
	
	public function store($data)
	{
		$this->_errors = $this->storeGiftEach($data);
		if (!empty($this->_errors))
			return false;

		return true;
	
	}
	public function storeCode($data)
	{
		$this->_errors = $this->storeCodeEach($data);
		if (!empty($this->_errors))
			return false;

		return true;
	
	}
	public function storeGiftEach($data) 
	{
		$errors = array();
		
		
		// set null fields
		if (empty($data['profile_id'])) $data['profile_id'] = null;
		if (empty($data['expiration_number'])) $data['expiration_number'] = null;
		if (empty($data['expiration_type'])) $data['expiration_type'] = null;
		if (empty($data['vendor_name'])) $data['vendor_name'] = null;
		if (empty($data['vendor_email'])) $data['vendor_email'] = null;
		
		$row = awoHelper::dbTableRow('awocoupon_giftcert_product', 'id', 0);
		
		
		// bind it to the table
		if (!($row = awoHelper::dbbind($row, $data)))
			$errors[] = 'Unable to bind item';

		// sanitise fields
		$row->id 			= (int)$row->id;

		// Make sure the data is valid
		$tmperr = $this->storeGift_validation($row);
		foreach ($tmperr as $err) $errors[] = $err;

		// take a break and return if there are any errors
		if (!empty($errors)) return $errors;
		
		
		$row = awoHelper::dbstore('awocoupon_giftcert_product', $row);
		
		return;
	}
	public function storeCodeEach($data)
	{
		$errors = array();
		$error_check_only = !empty($data['store_none_errors']) ? false : true;

		$product_id = (int)$data['product_id'];
		if (empty($product_id))
		{
			$errors[] = $this->awocoupon->l('Product: please select an item');
			return $errors;
		}
		
		
		
		$data = $data['data'];
		$arrdistinctcodes = array();
		foreach ($data as $row)
			@$arrdistinctcodes[$row[1]]++;
		
		
		$_map = array(
			'status' => array(
				trim(strtolower($this->awocoupon->l('Active')))=>'active',
				trim(strtolower($this->awocoupon->l('Inactive')))=>'inactive',
			),
		);
		$sql = 'SELECT code FROM '._DB_PREFIX_.'awocoupon_giftcert_code WHERE product_id='.$product_id;
		$_map_code = awoHelper::loadObjectList($sql, 'code');
		

		$datadistinct = array();
		foreach ($data as $row)
		{
		// first level error checking
			$id = $row[0];
			if (trim($id) == '')
			{
				$errors['          '][] = $this->awocoupon->l('No Id Specified');
				continue;
			}
			if (isset($datadistinct[$id]))
			{
				$errors[$id][] = $this->awocoupon->l('That coupon code already exists. Please try again');
				continue;
			}
			$datadistinct[$id] = $row;
			if (count($row) < 2)
			{
				$errors[$id][] = $this->awocoupon->l('Not enough columns to process');
				continue;
			}
			$row = array_pad($row, 3, '');

			if (empty($row[0])) $errors[$id][] = $this->awocoupon->l('Coupon: please enter a value');
			elseif (isset($_map_code[$row[0]])) $errors[$id][] = $this->awocoupon->l('That coupon code already exists. Please try again');
			
			$datadistinct[$id][1] = $row[1] = trim(strtolower($row[1]));
			if (empty($_map['status'][$row[1]])) $errors[$id][] = $this->awocoupon->l('Status: please enter a valid value');
			$datadistinct[$id][2] = trim($row[2]);
		}

//printr($datadistinct);
		if ($error_check_only && !empty($errors)) return $errors;
		
		$sql_arr = array();
		foreach ($datadistinct as $id => $row)
		{
			if (empty($errors[$id]))
				$sql_arr[] = '('.$product_id.',"'.$id.'","'.$_map['status'][$row[1]].'","'.$row[2].'")';
		}	
		
		if (!empty($sql_arr))
		{
			$tmp_size = count($sql_arr);
			for ($i = 0; $i < $tmp_size; $i = $i + 300)
			{
				$sql = 'INSERT INTO '._DB_PREFIX_.'awocoupon_giftcert_code (product_id,code,status,note) VALUES '.implode(',', array_slice($sql_arr, $i, 300));
				awoHelper::query($sql);
			}
		}
		return $errors;		
	}		

	public function storeGift_validation($row)
	{
		$err = array();
		
		if (empty($row->product_id) || !ctype_digit($row->product_id)) $err[] = $this->awocoupon->l('Product: please select an item');
		if (empty($row->coupon_template_id) || !ctype_digit($row->coupon_template_id)) $err[] = $this->awocoupon->l('Template: please select an item');
		if (!empty($row->profile_id) && !ctype_digit($row->profile_id)) $err[] = $this->awocoupon->l('Profile: please make a selection');
		if (!empty($row->expiration_number) || !empty($row->expiration_type))
		{
			if (empty($row->expiration_number) || empty($row->expiration_type)) $err[] = $this->awocoupon->l('Expiration: please enter a valid value');
			elseif (!ctype_digit($row->expiration_number))  $err[] = $this->awocoupon->l('Expiration: please enter a valid value');
			elseif ($row->expiration_type != 'day' && $row->expiration_type != 'month' && $row->expiration_type != 'year') $err[] = $this->awocoupon->l('Expiration: please enter a valid value');
		}
		
		
		if (empty($row->published) || ($row->published != '1' && $row->published != '-1')) $err[] = $this->awocoupon->l('Published: please enter a valid value');
		if (!empty($row->recipient_email_id) && !ctype_digit($row->recipient_email_id)) $err[] = $this->awocoupon->l('Recipient Email ID: please enter a valid value');
		if (!empty($row->recipient_name_id) && !ctype_digit($row->recipient_name_id)) $err[] = $this->awocoupon->l('Recipient Name ID: please enter a valid value');
		if (!empty($row->recipient_mesg_id) && !ctype_digit($row->recipient_mesg_id)) $err[] = $this->awocoupon->l('Recipient Message ID: please enter a valid value');
		
		return $err;
	}



	public function publish($data, $publish = 1)
	{
		@$id = (int)$data['id'];
		awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon_giftcert_product SET published='.(int)$publish.' WHERE id='.(int)$id);
		return true;
	}
	public function publishCode($data, $publish = 1)
	{
		@$id = (int)$data['id'];
		$publish = (int)$publish;
		$publish = $publish == 1 ? 'active' : 'inactive';
		awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon_giftcert_code SET status = "'.$publish.'" WHERE id='.(int)$id);
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

		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_giftcert_product WHERE id IN ('.$cids.')');

		return true;
	}
	public function deleteCode($cids)
	{		
		if (empty($cids) || !is_array($cids))
		{
			$this->errors = array('Invalid Items');
			return false;
		}
		
		foreach ($cids as $k => $v) $cids[$k] = (int)$v;
		$cids = implode(',', $cids);

		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_giftcert_code WHERE id IN ('.$cids.')');

		return true;
	}



	public function _buildQueryGift($params)
	{
		$id_lang = (int)Context::getContext()->language->id;
		$sql = 'SELECT g.*,lang.name as product_name,pr.title as profile, COUNT(DISTINCT pc.id) as codecount,c.coupon_code
				  FROM #__awocoupon_giftcert_product g
				  LEFT JOIN #__awocoupon c ON c.id=g.coupon_template_id
				  LEFT JOIN #__product p ON p.id_product=g.product_id
				  LEFT JOIN #__product_lang as lang using (`id_product`)
				  LEFT JOIN #__awocoupon_profile pr ON pr.id=g.profile_id
				  LEFT JOIN #__awocoupon_giftcert_code pc ON pc.product_id=p.id_product
				 WHERE 1=1 AND lang.id_lang="'.(int)$id_lang.'"
						'.$params->where.'
				   GROUP BY g.id
				HAVING 1=1 '.$params->having.'
						'.$params->orderbystr;
		return $sql;
		

	}
	public function _buildQueryCode($params)
	{
		$where = '';
		$product_id = Tools::getValue('filter_product_id');
		if (!empty($product_id)) $where .= ' AND g.product_id='.(int)$product_id.' ';
	
		//exit($where);

		$sql = 'SELECT g.*,lang.name as product_name
				  FROM #__awocoupon_giftcert_code g
				  LEFT JOIN #__product p ON p.id_product=g.product_id
				  LEFT JOIN #__product_lang as lang using (`id_product`)
				  WHERE 1=1
						'.$params->where.'
						'.$where.'
				 GROUP BY g.id
				HAVING 1=1 '.$params->having.'
						'.$params->orderbystr;
		return $sql;
		

	}
	public function getEntriesGift($params)
	{
		// Lets load the files if it doesn't already exist
		
		
		$query = $this->_buildQueryGift($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);

		$data = array();
		foreach ($rows as $row)
		{
			if ($row->published == 1) $published = '<a href="'.awohelper::admin_link().'&view=giftcert&id='.$row->id.'&task=unpublish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/published.png" width="16" height="16" class="hand" border="0" /></a>';
			else $published = '<a href="'.awohelper::admin_link().'&view=giftcert&id='.$row->id.'&task=publish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/unpublished.png" width="16" height="16" class="hand" border="0" /></a>';

	
			$expiration = !empty($row->expiration_number) && !empty($row->expiration_type) 
					? $row->expiration_number.' '.awoHelper::vars('expiration_type', $row->expiration_type)
					: '';
			$vendor = $row->vendor_name.(!empty($row->vendor_email) ? ' &lt;'.$row->vendor_email.'&gt;' : '');
			$codecount = $row->codecount;
			if (!empty($row->codecount))
				$codecount .= ' [ <a href="'.awohelper::admin_link().'&view=giftcert&layout=codedefault&amp;filter_product_id='.$row->product_id.'&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'" ><span>'.$this->awocoupon->l('View').'</span></a> ]';
			
			$data[] = array(
				'id'=>$row->id,
				'product_name'=>$row->product_name,
				'coupon_code'=>$row->coupon_code,
				'profile'=>$row->profile,
				'codecount'=>$codecount,
				'expiration'=>$expiration,
				'vendor_name'=>$vendor,
				'published'=>$published,
			);
			
		}
		
		return $data;
	}
	public function getEntriesCode($params)
	{
		// Lets load the files if it doesn't already exist
		
		
		$query = $this->_buildQueryCode($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);

		$data = array();
		foreach ($rows as $row)
		{
			if ($row->status == 'active') $published = '<a href="'.awohelper::admin_link().'&view=giftcert&layout=codedefault&id='.$row->id.'&task=unpublish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/published.png" width="16" height="16" class="hand" border="0" /></a>';
			elseif ($row->status == 'inactive') $published = '<a href="'.awohelper::admin_link().'&view=giftcert&&layout=codedefault&id='.$row->id.'&task=publish&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'"><img src="'.AWO_URI.'/media/img/unpublished.png" width="16" height="16" class="hand" border="0" /></a>';
			else $published = $this->awocoupon->l('Used');

			$data[] = array(
				'id'=>$row->id,
				'product_name'=>$row->product_name,
				'code'=>$row->code,
				'status'=>$published,
				'note'=>$row->note,
			);
			
		}
		
		return $data;
	}
	public function getTotalGift($filters = array())
	{
		awoHelper::query($this->_buildQueryGift($filters));
		return Db::getInstance()->NumRows();
	}
	public function getTotalCode($filters = array())
	{
		awoHelper::query($this->_buildQueryCode($filters));
		return Db::getInstance()->NumRows();
	}

	
	public function getProfileList()
	{
		return awoHelper::loadObjectList('SELECT id,title FROM '._DB_PREFIX_.'awocoupon_profile ORDER BY title,id');
	}
	public function getTemplateList()
	{
		return awoHelper::loadObjectList('SELECT id,coupon_code FROM '._DB_PREFIX_.'awocoupon WHERE published=-2 ORDER BY coupon_code,id');
	}
	public function getGiftCertProductList()
	{
		$sql = 'SELECT g.*,lang.name as product_name,pr.title as profile, COUNT(pc.id) as codecount,c.coupon_code
				  FROM '._DB_PREFIX_.'awocoupon_giftcert_product g
				  LEFT JOIN '._DB_PREFIX_.'awocoupon c ON c.id=g.coupon_template_id
				  LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product=g.product_id
				  LEFT JOIN `'._DB_PREFIX_.'product_lang` as lang using (`id_product`)
				  LEFT JOIN '._DB_PREFIX_.'awocoupon_profile pr ON pr.id=g.profile_id
				  LEFT JOIN '._DB_PREFIX_.'awocoupon_giftcert_code pc ON pc.product_id=p.id_product
				 GROUP BY g.id
				 ORDER BY product_name,g.product_id ';
		return awoHelper::loadObjectList($sql);
	}

}

