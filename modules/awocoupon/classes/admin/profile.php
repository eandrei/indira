<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelProfile {
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
		$entry = awoHelper::dbTableRow('awocoupon_profile', 'id', $id);
		$entry->languages = awoHelper::loadObjectList('SELECT * FROM #__awocoupon_profile_lang WHERE profile_id='.(int)$id, 'id_lang');

	
		$entry = $this->_initEntry_helper($entry);
		if (!empty($entry->id))
		{
			if ($entry->message_type == 'html')
			{
				$tmp = unserialize($entry->coupon_code_config);
				$entry->couponcode_align = $tmp['align'];
				$entry->couponcode_padding = $tmp['pad'];
				$entry->couponcode_y = $tmp['y'];
				$entry->couponcode_font = $tmp['font'];
				$entry->couponcode_font_size = $tmp['size'];
				$entry->couponcode_font_color = $tmp['color'];
				
				$tmp = unserialize($entry->coupon_value_config);
				$entry->couponvalue_align = $tmp['align'];
				$entry->couponvalue_padding = $tmp['pad'];
				$entry->couponvalue_y = $tmp['y'];
				$entry->couponvalue_font = $tmp['font'];
				$entry->couponvalue_font_size = $tmp['size'];
				$entry->couponvalue_font_color = $tmp['color'];
				
				if (!empty($entry->expiration_config))
				{
					$tmp = unserialize($entry->expiration_config);
					$entry->expiration_text = $tmp['text'];
					$entry->expiration_align = $tmp['align'];
					$entry->expiration_padding = $tmp['pad'];
					$entry->expiration_y = $tmp['y'];
					$entry->expiration_font = $tmp['font'];
					$entry->expiration_font_size = $tmp['size'];
					$entry->expiration_font_color = $tmp['color'];
				}
				if (!empty($entry->freetext1_config))
				{
					$tmp = unserialize($entry->freetext1_config);
					$entry->freetext1_text = $tmp['text'];
					$entry->freetext1_align = $tmp['align'];
					$entry->freetext1_padding = $tmp['pad'];
					$entry->freetext1_y = $tmp['y'];
					$entry->freetext1_font = $tmp['font'];
					$entry->freetext1_font_size = $tmp['size'];
					$entry->freetext1_font_color = $tmp['color'];
				}
				if (!empty($entry->freetext2_config))
				{
					$tmp = unserialize($entry->freetext2_config);
					$entry->freetext2_text = $tmp['text'];
					$entry->freetext2_align = $tmp['align'];
					$entry->freetext2_padding = $tmp['pad'];
					$entry->freetext2_y = $tmp['y'];
					$entry->freetext2_font = $tmp['font'];
					$entry->freetext2_font_size = $tmp['size'];
					$entry->freetext2_font_color = $tmp['color'];
				}
				if (!empty($entry->freetext3_config))
				{
					$tmp = unserialize($entry->freetext3_config);
					$entry->freetext3_text = $tmp['text'];
					$entry->freetext3_align = $tmp['align'];
					$entry->freetext3_padding = $tmp['pad'];
					$entry->freetext3_y = $tmp['y'];
					$entry->freetext3_font = $tmp['font'];
					$entry->freetext3_font_size = $tmp['size'];
					$entry->freetext3_font_color = $tmp['color'];
				}
				
				$entry->imgplugin = array();

				$rtn = awohelper::psHook('actionAwoProfileOnBeforeEntryView', array('profile_id'=>$entry->id, 'rtn'=>&$entry->imgplugin));
				/*foreach ($rtn as $items) {
					foreach ($items as $k2 => $row) {
						if (empty($row->key)) continue;
						$entry->imgplugin[$row->key][$k2] = $row;
					}
				}*/
			}
		}

		return $entry;
	}
	public function _initEntry_helper($entry)
	{
		$entry->couponcode_align = '';
		$entry->couponcode_padding = '';
		$entry->couponcode_y = '';
		$entry->couponcode_font = '';
		$entry->couponcode_font_size = '';
		$entry->couponcode_font_color = '';
		
		$entry->couponvalue_align = '';
		$entry->couponvalue_padding = '';
		$entry->couponvalue_y = '';
		$entry->couponvalue_font = '';
		$entry->couponvalue_font_size = '';
		$entry->couponvalue_font_color = '';

		$entry->expiration_text = '';
		$entry->expiration_align = '';
		$entry->expiration_padding = '';
		$entry->expiration_y = '';
		$entry->expiration_font = '';
		$entry->expiration_font_size = '';
		$entry->expiration_font_color = '';
		
		$entry->freetext1_text = '';
		$entry->freetext1_align = '';
		$entry->freetext1_padding = '';
		$entry->freetext1_y = '';
		$entry->freetext1_font = '';
		$entry->freetext1_font_size = '';
		$entry->freetext1_font_color = '';
		
		$entry->freetext2_text = '';
		$entry->freetext2_align = '';
		$entry->freetext2_padding = '';
		$entry->freetext2_y = '';
		$entry->freetext2_font = '';
		$entry->freetext2_font_size = '';
		$entry->freetext2_font_color = '';
		
		$entry->freetext3_text = '';
		$entry->freetext3_align = '';
		$entry->freetext3_padding = '';
		$entry->freetext3_y = '';
		$entry->freetext3_font = '';
		$entry->freetext3_font_size = '';
		$entry->freetext3_font_color = '';
		
		$entry->imgplugin = array();
		
		$rtn = awohelper::psHook('actionAwoProfileOnInitializeEntryView', array('rtn'=>&$entry->imgplugin));
		/*foreach ($rtn as $items) {
			foreach ($items as $k2 => $row) {
				if (empty($row->key)) continue;
				$entry->imgplugin[$row->key][$k2] = $row;
			}
		}*/
		return $entry;
	}


	
	public function _buildQuery($params)
	{
		$sql = 'SELECT p.id,p.title,p.message_type,p.bcc_admin,p.is_default,p.from_name,p.from_email,pg.email_subject,p.is_pdf
				  FROM #__awocoupon_profile p
				  LEFT JOIN #__awocoupon_profile_lang pg ON pg.profile_id=p.id AND pg.id_lang='.(int)Configuration::get('PS_LANG_DEFAULT').' 
				 WHERE 1=1 '.$params->where.'
				HAVING 1=1 '.$params->having.'
				'.$params->orderbystr;
		return $sql;
	}
	public function getEntries($params)
	{
		// Lets load the files if it doesn't already exist
		
		
		$query = $this->_buildQuery($params).' LIMIT '.$params->start.','.$params->limit;
		$rows = awoHelper::loadObjectList($query);
		
		$ids = '';
		$ptr = null;
		$data = array();
		foreach ($rows as $i => $row)
		{
			$default = $row->is_default == 1 ? '<img src="'.AWO_URI.'/media/img/icon-16-default.png" border="0"  />' : '';
			$message_type = awoHelper::vars('giftcert_message_type', $row->message_type);
			$bcc_admin = $this->awocoupon->l(!empty($row->bcc_admin) ? 'Yes' : 'No');
		
		
			//$url = AWO_URI.'/ajax.php?task=previewprofileid&id='.$row->id;
			$url = _PS_VERSION_ < '1.5' 
				? AWO_URI.'/ajax.php?task=previewprofileid&id='.$row->id
				: awohelper::admin_link().'&view=ajax&token='.Tools::getAdminTokenLite('AdminAwoCoupon').'&task=previewprofileid&id='.$row->id;

			$preview = $row->message_type == 'html' ? '<a class="awomodal" href="'.$url.'" >'.$this->awocoupon->l('Preview').'</a>' : '';
							
			$data[] = array(
				'id'=>$row->id,
				'title'=>$row->title,
				'from_name'=>$row->from_name,
				'from_email'=>$row->from_email,
				'bcc_admin'=>$bcc_admin,
				'email_subject'=>$row->email_subject,
				'message_type'=>$message_type,
				'preview'=>$preview,
				'default'=>$default,
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
	public function storeEach($data)
	{
		$errors = array();
		
		
		// set null fields
		if (empty($data['from_name'])) $data['from_name'] = null;
		if (empty($data['from_email'])) $data['from_email'] = null;
		if (empty($data['bcc_admin'])) $data['bcc_admin'] = null;
		if (empty($data['email_subject'])) $data['email_subject'] = null;
		if (empty($data['is_pdf'])) $data['is_pdf'] = null;
		
		$data['is_default'] = null;
		
		$row = awoHelper::dbTableRow('awocoupon_profile', 'id', 0);
		
		
		// bind it to the table
		if (!($row = awoHelper::dbbind($row, $data)))
			$errors[] = 'Unable to bind item';

		// sanitise fields
		$row->id 			= (int)$row->id;

		
		// Make sure the data is valid
		$tmperr = $this->store_validation($row);
		foreach ($tmperr as $err) $errors[] = $err;


		if (!empty($row->id))
		{
			$tmp = awoHelper::loadResult('SELECT is_default FROM '._DB_PREFIX_.'awocoupon_profile WHERE id='.$row->id);
			if (!empty($tmp)) $row->is_default = 1;
		}

		// take a break and return if there are any errors
		if (!empty($errors)) return $errors;
		
		
		
		if ($row->message_type == 'text')
		{
			$row->image = null;
			$row->coupon_code_config = null;
			$row->coupon_value_config = null;
			$row->expiration_config = null;
			$row->freetext1_config = null;
			$row->freetext2_config = null;
			$row->freetext3_config = null;
		}
		else
		{
			if (empty($row->image)) $row->image = null;
			else
			{
				$row->coupon_code_config = serialize(array(
					'align'=>$data['couponcode_align'],
					'pad'=>$data['couponcode_align'] == 'C' ? '' : $data['couponcode_padding'],
					'y'=>$data['couponcode_y'],
					'font'=>$data['couponcode_font'],
					'size'=>$data['couponcode_font_size'],
					'color'=>$data['couponcode_font_color'],
				));
				
				if ($data['couponcode_align'] != 'C' && (empty($data['couponcode_padding']) || !ctype_digit($data['couponcode_padding']))) $errors[] = $this->awocoupon->l('Coupon code=>Padding: positive integer is required');
				if (empty($data['couponcode_y']) || !ctype_digit($data['couponcode_y'])) $errors[] = $this->awocoupon->l('Coupon code=>Y-Axis: positive integer is required');
				if (empty($data['couponcode_font'])) $errors[] = $this->awocoupon->l('Coupon code=>Font: please make a selection');
				if (empty($data['couponcode_font_size']) || !ctype_digit($data['couponcode_font_size'])) $errors[] = $this->awocoupon->l('Coupon code=>Font size: positive integer is required');

				$row->coupon_value_config = serialize(array(
					'align'=>$data['couponvalue_align'],
					'pad'=>$data['couponvalue_align'] == 'C' ? '' : $data['couponvalue_padding'],
					'y'=>$data['couponvalue_y'],
					'font'=>$data['couponvalue_font'],
					'size'=>$data['couponvalue_font_size'],
					'color'=>$data['couponvalue_font_color'],
				));
				if ($data['couponvalue_align'] != 'C' && (empty($data['couponvalue_padding']) || !ctype_digit($data['couponvalue_padding']))) $errors[] = $this->awocoupon->l('Value=>Padding: positive integer is required');
				if (empty($data['couponvalue_y']) || !ctype_digit($data['couponvalue_y'])) $errors[] = $this->awocoupon->l('Value=>Y-Axis: positive integer is required');
				if (empty($data['couponvalue_font'])) $errors[] = $this->awocoupon->l('Value=>Font: please make a selection');
				if (empty($data['couponvalue_font_size']) || !ctype_digit($data['couponvalue_font_size'])) $errors[] = $this->awocoupon->l('Value=>Font size: positive integer is required');
					
				if (empty($data['expiration_text'])) $row->expiration_config = null;
				else
				{
					$row->expiration_config = serialize(array(
						'text'=>$data['expiration_text'],
						'align'=>$data['expiration_align'],
						'pad'=>$data['expiration_align'] == 'C' ? '' : $data['expiration_padding'],
						'y'=>$data['expiration_y'],
						'font'=>$data['expiration_font'],
						'size'=>$data['expiration_font_size'],
						'color'=>$data['expiration_font_color'],
					));
					if ($data['expiration_align'] != 'C' && (empty($data['expiration_padding']) || !ctype_digit($data['expiration_padding']))) $errors[] = $this->awocoupon->l('Expiration=>Padding: positive integer is required');
					if (empty($data['expiration_y']) || !ctype_digit($data['expiration_y'])) $errors[] = $this->awocoupon->l('Expiration=>Y-Axis: positive integer is required');
					if (empty($data['expiration_font'])) $errors[] = $this->awocoupon->l('Expiration=>Font: please make a selection');
					if (empty($data['expiration_font_size']) || !ctype_digit($data['expiration_font_size'])) $errors[] = $this->awocoupon->l('Expiration=>Font size: positive integer is required');
				}

				if (empty($data['freetext1_text'])) $row->freetext1_config = null;
				else
				{
					$row->freetext1_config = serialize(array(
						'text'=>$data['freetext1_text'],
						'align'=>$data['freetext1_align'],
						'pad'=>$data['freetext1_align'] == 'C' ? '' : $data['freetext1_padding'],
						'y'=>$data['freetext1_y'],
						'font'=>$data['freetext1_font'],
						'size'=>$data['freetext1_font_size'],
						'color'=>$data['freetext1_font_color'],
					));
					if ($data['freetext1_align'] != 'C' && (empty($data['freetext1_padding']) || !ctype_digit($data['freetext1_padding']))) $errors[] = $this->awocoupon->l('Free Text 1=>Padding: positive integer is required');
					if (empty($data['freetext1_y']) || !ctype_digit($data['freetext1_y'])) $errors[] = $this->awocoupon->l('Free Text 1=>Y-Axis: positive integer is required');
					if (empty($data['freetext1_font'])) $errors[] = $this->awocoupon->l('Free Text 1=>Font: please make a selection');
					if (empty($data['freetext1_font_size']) || !ctype_digit($data['freetext1_font_size'])) $errors[] = $this->awocoupon->l('Free Text 1=>Font size: positive integer is required');
				}
				
				if (empty($data['freetext2_text'])) $row->freetext2_config = null;
				else
				{
					$row->freetext2_config = serialize(array(
						'text'=>$data['freetext2_text'],
						'align'=>$data['freetext2_align'],
						'pad'=>$data['freetext2_align'] == 'C' ? '' : $data['freetext2_padding'],
						'y'=>$data['freetext2_y'],
						'font'=>$data['freetext2_font'],
						'size'=>$data['freetext2_font_size'],
						'color'=>$data['freetext2_font_color'],
					));
					if ($data['freetext2_align'] != 'C' && (empty($data['freetext2_padding']) || !ctype_digit($data['freetext2_padding']))) $errors[] = $this->awocoupon->l('Free Text 2=>Padding: positive integer is required');
					if (empty($data['freetext2_y']) || !ctype_digit($data['freetext2_y'])) $errors[] = $this->awocoupon->l('Free Text 2=>Y-Axis: positive integer is required');
					if (empty($data['freetext2_font'])) $errors[] = $this->awocoupon->l('Free Text 2=>Font: please make a selection');
					if (empty($data['freetext2_font_size']) || !ctype_digit($data['freetext2_font_size'])) $errors[] = $this->awocoupon->l('Free Text 2=>Font Size: positive integer is required');
				}
					
				if (empty($data['freetext3_text'])) $row->freetext3_config = null;
				else
				{
					$row->freetext3_config = serialize(array(
						'text'=>$data['freetext3_text'],
						'align'=>$data['freetext3_align'],
						'pad'=>$data['freetext3_align'] == 'C' ? '' : $data['freetext3_padding'],
						'y'=>$data['freetext3_y'],
						'font'=>$data['freetext3_font'],
						'size'=>$data['freetext3_font_size'],
						'color'=>$data['freetext3_font_color'],
					));
					if ($data['freetext3_align'] != 'C' && (empty($data['freetext3_padding']) || !ctype_digit($data['freetext3_padding']))) $errors[] = $this->awocoupon->l('Free Text 3=>Padding: positive integer is required');
					if (empty($data['freetext3_y']) || !ctype_digit($data['freetext3_y'])) $errors[] = $this->awocoupon->l('Free Text 3=>Y-Axis: positive integer is required');
					if (empty($data['freetext3_font'])) $errors[] = $this->awocoupon->l('Free Text 3=>Font: please make a selection');
					if (empty($data['freetext3_font_size']) || !ctype_digit($data['freetext3_font_size'])) $errors[] = $this->awocoupon->l('Free Text 3=>Font size: positive integer is required');
				}
			
			
				if (!empty($data['imgplugin']))
				{
					foreach ($data['imgplugin'] as $pluginitems)
					{
						foreach ($pluginitems as $pluginrow)
						{
							if (!empty($pluginrow['text']))
							{
								if ($pluginrow['align'] != 'C' && (empty($pluginrow['padding']) || !ctype_digit($pluginrow['padding']))) $errors[] = $pluginrow['title'].'=>'.$this->awocoupon->l('Padding: positive integer is required');
								if (empty($pluginrow['y']) || !ctype_digit($pluginrow['y'])) $errors[] = $pluginrow['title'].'=>'.$this->awocoupon->l('=>Y-Axis: positive integer is required');
								if (empty($pluginrow['is_ignore_font']) && empty($pluginrow['font'])) $errors[] = $pluginrow['title'].'=>'.$this->awocoupon->l('Font: please make a selection');
								if (empty($pluginrow['is_ignore_font_size']) && (empty($pluginrow['font_size']) || !ctype_digit($pluginrow['font_size']))) $errors[] = $pluginrow['title'].'=>'.$this->awocoupon->l('Font size: positive integer is required');
							}
						}
					}
				}
				
			
			
			
			
			}
		}
		
		
		if (!empty($errors)) return $errors;
		
		$row = awoHelper::dbstore('awocoupon_profile', $row);
		
		awohelper::psHook('actionAwoProfileOnAfterUpdate', array('row'=>$row, 'data'=>$data));


		// add language
		if (!empty($row->id))
		{
			$languages = Language::getLanguages();
			$langrows = awoHelper::loadObjectList('DESC #__awocoupon_profile_lang');
			array_shift($langrows);
			array_shift($langrows);
			foreach ($languages as $lang)
			{
				$datalang = array('profile_id'=>$row->id,'id_lang'=>$lang['id_lang']);
				foreach ($langrows as $langrow)
				{
	//if ($langrow->Field != 'email_subject') continue;
					if ($row->message_type == 'html' && $langrow->Field == 'email_body')
					{
						if (!empty($data['email_html_'.$lang['id_lang']]))
						{
							$datalang[$langrow->Field] = pSQL($_POST['email_html_'.$lang['id_lang']], true);
							//$datalang[$langrow->Field] = pSQL(str_replace( '<br>', '<br />', $_POST[$langrow->Field.'_'.$lang['id_lang']]),true);
						}
					}
					elseif (substr($langrow->Field, 0, 4) == 'pdf_')
					{
						if (!empty($data[$langrow->Field.'_'.$lang['id_lang']]))
							$datalang[$langrow->Field] = pSQL($_POST[$langrow->Field.'_'.$lang['id_lang']], true);
					}
					else
					{
						if (!empty($data[$langrow->Field.'_'.$lang['id_lang']]))
							$datalang[$langrow->Field] = pSQL($data[$langrow->Field.'_'.$lang['id_lang']]);
					}
				}
				
				if (count($datalang) > 2)
				{
					$sql = 'INSERT INTO #__awocoupon_profile_lang ('.implode(',', array_keys($datalang)).') VALUES ("'.implode('","', $datalang).'")
							ON DUPLICATE KEY UPDATE ';
					array_shift($datalang);
					array_shift($datalang);
					foreach ($datalang as $col => $val) $sql .= $col.'="'.$val.'",';
					//printrx(substr($sql,0,-1));
					awoHelper::query(substr($sql, 0, -1));
				}
				
			}
		}
		
		
		return;
	}
	public function store_validation($row)
	{
		$err = array();
		
		if (empty($row->title)) $err[] = $this->awocoupon->l('Title: please enter a value');
		if (empty($row->message_type)) $err[] = $this->awocoupon->l('Message type: please make a selection'); 

		return $err;
	}
	
	
	
	

	public function makedefault($id)
	{
		$id = (int)$id;
		
		$tmp = awoHelper::loadResult('SELECT id FROM '._DB_PREFIX_.'awocoupon_profile WHERE id='.(int)$id);
		if (!empty($tmp))
		{
			awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon_profile SET is_default=NULL');
			awoHelper::query('UPDATE '._DB_PREFIX_.'awocoupon_profile SET is_default=1 WHERE id='.(int)$id);
		}
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

		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_profile_lang WHERE profile_id IN ('.$cids.')');
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_profile WHERE id IN ('.$cids.')');

		return true;
	}
	
	public function duplicate($data)
	{
		@$id = (int)$data['id'];
	
	
		$desc_table = awoHelper::loadObjectList('DESC #__awocoupon_profile');
		array_shift($desc_table);
		$cols = array();
		foreach ($desc_table as $col) $cols[] = $col->Field;
		
		$sql = 'INSERT INTO #__awocoupon_profile('.implode(',', $cols).')
					SELECT '.implode(',', $cols).' FROM #__awocoupon_profile WHERE id='.$id;
		awoHelper::query($sql);
		$profile_id = (int)Db::getInstance()->Insert_ID();
		if (!empty($profile_id))
		{
			awoHelper::query('UPDATE #__awocoupon_profile SET is_default=NULL WHERE id='.$profile_id);
			
			$desc_table = awoHelper::loadObjectList('DESC #__awocoupon_profile_lang');
			array_shift($desc_table);
			$cols = array();
			foreach ($desc_table as $col) $cols[] = $col->Field;
			
			$sql = 'INSERT INTO #__awocoupon_profile_lang (profile_id,'.implode(',', $cols).')
						SELECT '.$profile_id.','.implode(',', $cols).' FROM #__awocoupon_profile_lang WHERE profile_id='.$id;
			awoHelper::query($sql);
		}

		return true;
	}
	
	
	
	
	
	
	
	
	
	public function getimages()
	{
		$imagedd = array();
		$accepted_formats = array('png','jpg');
		foreach (glob(_PS_MODULE_DIR_.'awocoupon/giftcert/images/*.*') as $img)
		{
			$parts = pathinfo($img);
			if (in_array(strtolower($parts['extension']), $accepted_formats)) $imagedd[$parts['basename']] = ucwords($parts['filename']);
		}
		return $imagedd;
	}
	
	public function getfonts()
	{
		$fontdd = array();
		foreach (glob(_PS_MODULE_DIR_.'awocoupon/giftcert/font/*.[tT][tT][fF]') as $font)
		{
			$font = basename($font);
			$fontdd[$font] = ucwords(substr($font, 0, -4));
		}
		return $fontdd;
	}
	
	public function getfontcolor()
	{
		return array(
			'#F0F8FF'=>'ALICEBLUE',
			'#FAEBD7'=>'ANTIQUEWHITE',
			'#00FFFF'=>'AQUA',
			'#7FFFD4'=>'AQUAMARINE',
			'#F0FFFF'=>'AZURE',
			'#F5F5DC'=>'BEIGE',
			'#FFE4C4'=>'BISQUE',
			'#000000'=>'BLACK',
			'#FFEBCD'=>'BLANCHEDALMOND',
			'#0000FF'=>'BLUE',
			'#8A2BE2'=>'BLUEVIOLET',
			'#A52A2A'=>'BROWN',
			'#DEB887'=>'BURLYWOOD',
			'#5F9EA0'=>'CADETBLUE',
			'#7FFF00'=>'CHARTREUSE',
			'#D2691E'=>'CHOCOLATE',
			'#FF7F50'=>'CORAL',
			'#6495ED'=>'CORNFLOWERBLUE',
			'#FFF8DC'=>'CORNSILK',
			'#DC143C'=>'CRIMSON',
			'#00FFFF'=>'CYAN',
			'#00008B'=>'DARKBLUE',
			'#008B8B'=>'DARKCYAN',
			'#B8860B'=>'DARKGOLDENROD',
			'#A9A9A9'=>'DARKGRAY',
			'#006400'=>'DARKGREEN',
			'#BDB76B'=>'DARKKHAKI',
			'#8B008B'=>'DARKMAGENTA',
			'#556B2F'=>'DARKOLIVEGREEN',
			'#FF8C00'=>'DARKORANGE',
			'#9932CC'=>'DARKORCHID',
			'#8B0000'=>'DARKRED',
			'#E9967A'=>'DARKSALMON',
			'#8FBC8F'=>'DARKSEAGREEN',
			'#483D8B'=>'DARKSLATEBLUE',
			'#2F4F4F'=>'DARKSLATEGRAY',
			'#00CED1'=>'DARKTURQUOISE',
			'#9400D3'=>'DARKVIOLET',
			'#FF1493'=>'DEEPPINK',
			'#00BFFF'=>'DEEPSKYBLUE',
			'#696969'=>'DIMGRAY',
			'#1E90FF'=>'DODGERBLUE',
			'#B22222'=>'FIREBRICK',
			'#FFFAF0'=>'FLORALWHITE',
			'#228B22'=>'FORESTGREEN',
			'#FF00FF'=>'FUCHSIA',
			'#DCDCDC'=>'GAINSBORO',
			'#F8F8FF'=>'GHOSTWHITE',
			'#FFD700'=>'GOLD',
			'#DAA520'=>'GOLDENROD',
			'#BEBEBE'=>'GRAY',
			'#008000'=>'GREEN',
			'#ADFF2F'=>'GREENYELLOW',
			'#F0FFF0'=>'HONEYDEW',
			'#FF69B4'=>'HOTPINK',
			'#CD5C5C'=>'INDIANRED',
			'#4B0082'=>'INDIGO',
			'#FFFFF0'=>'IVORY',
			'#F0D58C'=>'KHAKI',
			'#E6E6FA'=>'LAVENDER',
			'#FFF0F5'=>'LAVENDERBLUSH',
			'#7CFC00'=>'LAWNGREEN',
			'#FFFACD'=>'LEMONCHIFFON',
			'#ADD8E6'=>'LIGHTBLUE',
			'#F08080'=>'LIGHTCORAL',
			'#E0FFFF'=>'LIGHTCYAN',
			'#FAFAD2'=>'LIGHTGOLDENRODYELLOW',
			'#90EE90'=>'LIGHTGREEN',
			'#D3D3D3'=>'LIGHTGREY',
			'#FFB6C1'=>'LIGHTPINK',
			'#FFA07A'=>'LIGHTSALMON',
			'#20B2AA'=>'LIGHTSEAGREEN',
			'#87CEFA'=>'LIGHTSKYBLUE',
			'#778899'=>'LIGHTSLATEGRAY',
			'#B0C4DE'=>'LIGHTSTEELBLUE',
			'#FFFFE0'=>'LIGHTYELLOW',
			'#00FF00'=>'LIME',
			'#32CD32'=>'LIMEGREEN',
			'#FAF0E6'=>'LINEN',
			'#FF00FF'=>'MAGENTA',
			'#800000'=>'MAROON',
			'#66CDAA'=>'MEDIUMAQUAMARINE',
			'#0000CD'=>'MEDIUMBLUE',
			'#BA55D3'=>'MEDIUMORCHID',
			'#9370DB'=>'MEDIUMPURPLE',
			'#3CB371'=>'MEDIUMSEAGREEN',
			'#7B68EE'=>'MEDIUMSLATEBLUE',
			'#00FA9A'=>'MEDIUMSPRINGGREEN',
			'#48D1CC'=>'MEDIUMTURQUOISE',
			'#C71585'=>'MEDIUMVIOLETRED',
			'#191970'=>'MIDNIGHTBLUE',
			'#F5FFFA'=>'MINTCREAM',
			'#FFE4E1'=>'MISTYROSE',
			'#FFE4B5'=>'MOCCASIN',
			'#FFDEAD'=>'NAVAJOWHITE',
			'#000080'=>'NAVY',
			'#FDF5E6'=>'OLDLACE',
			'#808000'=>'OLIVE',
			'#6B8E23'=>'OLIVEDRAB',
			'#FFA500'=>'ORANGE',
			'#FF4500'=>'ORANGERED',
			'#DA70D6'=>'>ORCHID',
			'#EEE8AA'=>'PALEGOLDENROD',
			'#98FB98'=>'PALEGREEN',
			'#AFEEEE'=>'PALETURQUOISE',
			'#DB7093'=>'PALEVIOLETRED',
			'#FFEFD5'=>'PAPAYAWHIP',
			'#FFDAB9'=>'PEACHPUFF',
			'#CD853F'=>'PERU',
			'#FFC0CB'=>'PINK',
			'#DDA0DD'=>'PLUM',
			'#B0E0E6'=>'POWDERBLUE',
			'#800080'=>'PURPLE',
			'#FF0000'=>'RED',
			'#BC8F8F'=>'ROSYBROWN',
			'#4169E1'=>'ROYALBLUE',
			'#8B4513'=>'SADDLEBROWN',
			'#FA8072'=>'SALMON',
			'#F4A460'=>'SANDYBROWN',
			'#2E8B57'=>'SEAGREEN',
			'#FFF5EE'=>'SEASHELL',
			'#A0522D'=>'SIENNA',
			'#C0C0C0'=>'SILVER',
			'#87CEEB'=>'SKYBLUE',
			'#6A5ACD'=>'SLATEBLUE',
			'#708090'=>'SLATEGRAY',
			'#FFFAFA'=>'SNOW',
			'#00FF7F'=>'SPRINGGREEN',
			'#4682B4'=>'STEELBLUE',
			'#D2B48C'=>'TAN',
			'#008080'=>'TEAL',
			'#D8BFD8'=>'THISTLE',
			'#FF6347'=>'TOMATO',
			'#40E0D0'=>'TURQUOISE',
			'#EE82EE'=>'VIOLET',
			'#F5DEB3'=>'WHEAT',
			'#FFFFFF'=>'WHITE',
			'#F5F5F5'=>'WHITESMOKE',
			'#FFFF00'=>'YELLOW',
			'#9ACD32'=>'YELLOWGREEN',
		);
	}

}

