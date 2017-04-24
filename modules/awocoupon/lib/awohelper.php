<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

class AwoHelper
{

	public static function init()
	{
		if (!defined('AWO_URI')) define('AWO_URI', __PS_BASE_URI__.'modules/awocoupon');
	}
	
	public static function admin_link()
	{
		return version_compare(_PS_VERSION_, '1.6', '>=') ? 'index.php?controller=adminawocoupon' : 'index.php?tab=AdminAwoCoupon&module=awocoupon';
	}

	public static function shop_url()
	{
		return Tools::getShopDomain(true, true).__PS_BASE_URI__;
	}
	public static function vars($type, $item = null)
	{
		$vars = array(
			'function_type' => array(
				'coupon'=>'Coupon',
				'giftcert'=>'Gift Certificate',
				'shipping'=>'Shipping',
				'buy_x_get_y'=>'Buy X Get Y',
				'parent'=>'Parent',
			),
			'asset_mode' => array(
				'include'=>'Include',
				'exclude'=>'Exclude',
			),
			'asset_type' => array(
				'product'=>'Product',
				'category'=>'Category',
				'manufacturer'=>'Manufacturer',
				'vendor'=>'Vendor',
				'shipping'=>'Shipping',
				'coupon'=>'Coupon',
				'country'=>'Country',
				'countrystate'=>'State',
			),
			'parent_type' => array(
				'first'=>'First Found Match',
				'lowest'=>'Lowest Value',
				'highest'=>'Highest Value',
				'all'=>'Apply All',
				'allonly'=>'Apply only if ALL apply',
			),
			'buy_xy_process_type' => array(
				'first'=>'First Found Match',
				'lowest'=>'Lowest Value',
				'highest'=>'Highest Value',
			),
			'published' => array(
				'1'=>'Published',
				'-1'=>'Unpublished',
				'-2'=>'Template',
			),
			'coupon_value_type' => array(
				'percent'=>'Percent',
				'amount'=>'Amount',
			),
			'discount_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'min_value_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'min_qty_type' => array(
				'overall'=>'Overall',
				'specific'=>'Specific',
			),
			'user_type' => array(
				'user'=>'Customer',
				'usergroup'=>'Shopper Group',
			),
			'num_of_uses_type' => array(
				'total'=>'total',
				'per_user'=>'per customer',
			),	
			'expiration_type' => array(
				'day'=>'Day(s)',
				'month'=>'Month(s)',
				'year'=>'Year(s)',
			),
			'giftcert_message_type' => array(
				'text'=>'Text',
				'html'=>'HTML',
			),
			'status' => array(
				'active'=>'Active',
				'inactive'=>'Inactive',
				'used'=>'Value Used',
			),
			
		);
		
		if (isset($vars[$type]))
		{
			if (isset($item))
			{
				if (isset($vars[$type][$item])) return $vars[$type][$item];
				else return '';
			}
			else return $vars[$type];
		}
	}

	public static function DD($data, $name, $attribs = null, $selected = null, $blankvalue = null, $optKey = 'value', $optText = 'text')
	{
		// Set default options

		$id = $name;
		$id = str_replace(array('[', ']'), '', $id);

		$html = '<select'.($id !== '' ? ' id="'.$id.'"' : '').' name="'.$name.'"'.$attribs.'>';
		if (!is_null($blankvalue)) $html .= '<option value="">'.$blankvalue.'</option>';
		foreach ($data as $k => $item)
		{
			if (is_object($item))
			{
				$key = $item->$optKey;
				$text = $item->$optText;
			} 
			elseif (is_array($item))
			{
				$key = $item[$optKey];
				$text = $item[$optText];
			}
			else
			{
				$key = $k;
				$text = $item;
			}
			$is_selected = false;
			if (!is_null($selected))
			{
				if (is_array($selected))
				{
					// is an array so process
					if (in_array($key, $selected)) $is_selected = true;
				}
				elseif ($key == $selected) $is_selected = true;
			}
			$html .= '<option value="'.$key.'" '.($is_selected ? 'SELECTED' : '').'>'.$text.'</option>';
		}
		$html .= '</select>';
		
		return $html;
	}

	public static function loadObjectList($sql, $key = null)
	{
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		//trigger_error($sql);
		
		$sql = str_replace('#__', _DB_PREFIX_, $sql);
		$rows = version_compare(_PS_VERSION_, '1.5', '>')
					? $db->query($sql)
					: $db->executeS($sql, false);
		$rtn = array();
		while ($row = $db->nextRow($rows))
		{
			if (!empty($key)) $rtn[$row[$key]] = (object)$row;
			else $rtn[] = (object)$row;
		}
		return $rtn;
	}
	public static function loadObject($sql)
	{
		$rows = self::loadObjectList($sql);
		reset($rows);
		return current($rows);
	}
	public static function loadAssocList($sql, $key = null)
	{
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		//trigger_error($sql);
		$rows = version_compare(_PS_VERSION_, '1.5', '>')
					? $db->query(str_replace('#__', _DB_PREFIX_, $sql))
					: $db->executeS(str_replace('#__', _DB_PREFIX_, $sql), false);

		$rtn = array();
		while ($row = $db->nextRow($rows))
		{
			if (!empty($key)) $rtn[$row[$key]] = $row;
			else $rtn[] = $row;
		}
		
		return $rtn;
	}
	public static function loadResult($sql)
	{
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		$o = $db->getRow(str_replace('#__', _DB_PREFIX_, $sql));
		
		if (empty($o)) return null;
		
		//$out = array_shift(array_values($o));
		return reset($o);
		
	}
	public static function query($sql)
	{
		Db::getInstance()->execute(str_replace('#__', _DB_PREFIX_, $sql));
	}
	public static function escape($s)
	{
		return str_replace(array('_','%'), array('\_','\%'), pSQL($s));
	}

	public static function scrubids($ids)
	{
		if (!is_array($ids)) $ids = array($ids);
		array_walk($ids, create_function('&$val', '$val = (int)$val;'));
		if (empty($ids)) $ids = array(0);
		return implode(',', $ids);
	}
	public static function getAwoItem($table, $coupon_id, $order_by = null)
	{
		if ($table != '1' && $table != '2') return;
		
		$coupon_ids = self::scrubids($coupon_id);
		$id_lang = (int)Context::getContext()->language->id;
		
		$shop_str = '';
		if (awoHelper::is_multistore())
		{
			$id_shop = (int)Context::getContext()->shop->id;
			if (!empty($id_shop)) $shop_str = ' AND c.id_shop='.$id_shop.' ';
		}

		$sql = 'SELECT a.id,a.coupon_id,a.asset_id,c.name AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'product b ON b.id_product=a.asset_id
				  JOIN '._DB_PREFIX_.'product_lang c ON c.id_product=b.id_product
				 WHERE a.asset_type="product" AND c.id_lang='.(int)$id_lang.' AND a.coupon_id IN ('.$coupon_ids.') '.$shop_str.'
								UNION
				 SELECT a.id,a.coupon_id,a.asset_id,c.name AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'category b ON b.id_category=a.asset_id
				  JOIN '._DB_PREFIX_.'category_lang c ON c.id_category=b.id_category
				 WHERE a.asset_type="category" AND c.id_lang='.(int)$id_lang.' AND a.coupon_id IN ('.$coupon_ids.') '.$shop_str.'
								UNION
				 SELECT a.id,a.coupon_id,a.asset_id,b.name AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'manufacturer b ON b.id_manufacturer=a.asset_id
				 WHERE a.asset_type="manufacturer" AND a.coupon_id IN ('.$coupon_ids.')
								UNION
				 SELECT a.id,a.coupon_id,a.asset_id,b.name AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'supplier b ON b.id_supplier=a.asset_id
				 WHERE a.asset_type="vendor" AND a.coupon_id IN ('.$coupon_ids.')
								UNION
				 SELECT a.id,a.coupon_id,a.asset_id,b.coupon_code AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'awocoupon b ON b.id=a.asset_id
				 WHERE a.asset_type="coupon" AND a.coupon_id IN ('.$coupon_ids.')
								UNION
				 SELECT a.id,a.coupon_id,a.asset_id,s.name AS asset_name,a.asset_type
				  FROM '._DB_PREFIX_.'awocoupon_asset'.$table.' a
				  JOIN '._DB_PREFIX_.'carrier s ON s.id_carrier=a.asset_id
				 WHERE a.asset_type="shipping" AND a.coupon_id IN ('.$coupon_ids.')
				 
				 '.(!empty($order_by) ? $order_by : '');
		$rtn = self::loadObjectList($sql);
		return $rtn;

	}
	
	
	public static function generate_coupon_code()
	{
		$salt = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		do
			$coupon_code = self::randomCode(rand(8, 12), $salt);
		while (self::isCodeUsed($coupon_code));
		return $coupon_code;
	}
	private static function isCodeUsed($code)
	{
		$sql = 'SELECT id FROM '._DB_PREFIX_.'awocoupon WHERE coupon_code="'.$code.'"';
		$id = self::loadResult($sql);
		
		if (empty($id)) return false;
		return true;
	}
	private static function randomCode($length, $chars)
	{
		$rand_id = '';
		$char_length = strlen($chars);
		if ($length > 0) 
			for ($i = 1; $i <= $length; $i++)
				$rand_id .= $chars[mt_rand(0, $char_length - 1)];
		return $rand_id;
	}
	
	
	public static function dbTableRow($table, $key, $id)
	{
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		
		$sql = 'SELECT * FROM '._DB_PREFIX_.$table.' WHERE '.$key.'="'.$id.'"';
		$o = $db->getRow($sql);
		if (!empty($o)) return (object)$o;
		
		$sql = 'DESC  '._DB_PREFIX_.$table;
		$rows = version_compare(_PS_VERSION_, '1.5', '>')
					? $db->query($sql)
					: $db->executeS($sql, false);
		$rtn = array();
		while ($row = $db->nextRow($rows))
			$rtn[$row['Field']] = '';
		
		return (object)$rtn;
	}
	public static function dbbind($prop, $from)
	{
		$fromArray    = is_array($from);
		$fromObject    = is_object($from);
		if (!$fromArray && !$fromObject) return false;
		
		foreach ($prop as $k => $v)
		{
			if ($fromArray)
			{
				if (!isset($from[$k])) $prop->$k = null;
				else $prop->$k = $from[$k];
			}
			elseif ($fromObject)
			{
				if (!isset($from->$k)) $prop->$k = null;
				else $prop->$k = $from->$k;
			}
		}
		return $prop;
	}	
	public static function dbstore($table, $row, $extra = null)
	{
		if (empty($row->id))
		{
			$columns = $values = array();
			
			foreach ($row as $c => $item)
			{
				if ($c == 'id') continue;
				$columns[] = $c;
				$values[] = (empty($item) && (	(isset($extra->{$c.'_isnull'}) && $row->{'__'.$c.'_is_null__'})
									||
									!isset($extra->{$c.'_isnull'})
								)
							) ? 'NULL' : '"'.pSQL($item).'"';
			}
			$sql = 'INSERT INTO '._DB_PREFIX_.$table.' ('.implode(',', $columns).') VALUES ('.implode(',', $values).')';
//exit($sql);
			self::query($sql);
			$row->id = Db::getInstance()->Insert_ID();
		}
		else
		{
			$cols = array();
			foreach ($row as $c => $item)
			{
				if ($c == 'id') continue;
				$value = (empty($item) && (	(isset($extra->{$c.'_isnull'}) && $row->{'__'.$c.'_is_null__'})
									||
									!isset($extra->{$c.'_isnull'})
								)
							) ? 'NULL' : '"'.pSQL($item).'"';
				$cols[] = $c.'='.$value;
			}
			$sql = 'UPDATE '._DB_PREFIX_.$table.' SET '.implode(',', $cols).' WHERE id='.$row->id;
			self::query($sql);
		}
		return $row;
	}


	public static function writeToImage($code, $value, $expiration, $output, $profile = null, $profile_id = null, $dynamic_text = null)
	{
		if (!empty($profile_id))
		{
			$sql = 'SELECT id,message_type,image,coupon_code_config,coupon_value_config,expiration_config,freetext1_config,freetext2_config,freetext3_config
					  FROM '._DB_PREFIX_.'awocoupon_profile WHERE id='.(int)$profile_id.' AND message_type="html"';
			$profile = awoHelper::loadAssocList($sql);
			if (!empty($profile)) $profile = self::decrypt_profile(current($profile));
		}

		if (empty($profile)) return false;
		if (is_null($profile['image'])) return false;
		
		
		$baseimg = _PS_MODULE_DIR_.'awocoupon/giftcert/images/'.$profile['image'];

		if (!file_exists($baseimg)) return false;
		$image_parts = pathinfo($baseimg);
		$accepted_formats = array('png','jpg');
		if (!in_array($image_parts['extension'], $accepted_formats)) return false;
		
		// create image
		switch ($image_parts['extension'])
		{
			case 'png': {
				if (!($im = @imagecreatefrompng($baseimg))) return false; 		
				imagealphablending($im, true); 									// setting alpha blending on
				imagesavealpha($im, true); 										// save alphablending setting (important)
				break;
			}
			case 'jpg': {
				if (!($im = @imagecreatefromjpeg($baseimg))) return false;
				break;
			}
		}
		
		if (self::writeToImage_helper($im, $code, $profile['coupon_code_config']) === false) return false;
		if (self::writeToImage_helper($im, $value, $profile['coupon_value_config']) === false) return false;
		if (!empty($expiration) && !empty($profile['expiration_config']))
		{
			$str = date($profile['expiration_config']['text'], $expiration);
			if (self::writeToImage_helper($im, $str, $profile['expiration_config']) === false) return false;
		}
		if (!empty($profile['freetext1_config']))
		{
			if (!empty($dynamic_text['find'])) $profile['freetext1_config']['text'] = str_replace($dynamic_text['find'], $dynamic_text['replace'], $profile['freetext1_config']['text']);
			if (self::writeToImage_helper($im, $profile['freetext1_config']['text'], $profile['freetext1_config']) === false) return false;
		}
		if (!empty($profile['freetext2_config']))
		{
			if (!empty($dynamic_text['find'])) $profile['freetext2_config']['text'] = str_replace($dynamic_text['find'], $dynamic_text['replace'], $profile['freetext2_config']['text']);
			if (self::writeToImage_helper($im, $profile['freetext2_config']['text'], $profile['freetext2_config']) === false) return false;
		}
		if (!empty($profile['freetext3_config']))
		{
			if (!empty($dynamic_text['find'])) $profile['freetext3_config']['text'] = str_replace($dynamic_text['find'], $dynamic_text['replace'], $profile['freetext3_config']['text']);
			if (self::writeToImage_helper($im, $profile['freetext3_config']['text'], $profile['freetext3_config']) === false) return false;
		}
		

		$args = (object)array(
			'code'=>$code,
			'value'=>$value,
			'expiration'=>$expiration,
			'dynamic_text'=>$dynamic_text,
			'profile'=>$profile,
			'is_preview'=> $output == 'screen' ? true : false,
			'is_request'=> $output == 'email' || !empty($profile_id) ? true : false,

		);
		self::psHook('actionAwoGiftcertimageOnBeforeCreate', array('args'=>&$args, 'im'=>&$im));
		
		if ($output == 'screen') return $im;
		elseif ($output == 'email')
		{
			$path = _PS_MODULE_DIR_.'awocoupon/giftcert/temp';
			if (!is_dir($path)) if (!mkdir($path, 0777, true)) return false;

			// write coupon code
			switch ($image_parts['extension'])
			{
				case 'png': {
					$filename = time().mt_rand().'.png';
					imagepng($im, $path.'/'.$filename);					// save image to file
					break;
				}
				case 'jpg': {
					$filename = time().mt_rand().'.jpg';
					imagejpeg($im, $path.'/'.$filename, 82);					// save image to file
					break;
				}
			}

			imagedestroy($im);									// destroy resource

			return $path.'/'.$filename;
		}
		
		imagedestroy($im);										// destroy resource

	}
	public static function writeToImage_helper(&$im, $text, $config)
	{
		// write coupon code
		//$text = mb_encode_numericentity($text, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
		$font = _PS_MODULE_DIR_.'awocoupon/giftcert/font/'.$config['font'];
		if (!file_exists($font)) return false;
		$rgb = self::html2rgb($config['color']);
		$color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);							// create the text color
		$align_func = 'imagettftextL';
		if ($config['align'] == 'R') $align_func = 'imagettftextR';
		elseif ($config['align'] == 'C') $align_func = 'imagettftextC';
		self::$align_func($im,
			$config['size'],
			$config['y'],
			$color,
			$font,
			$text,
			$config['pad']);				// add the word 'code'
		return true;
	}
	public static function imagettftextR($image, $fontsize, $y, $fontcolor, $font, $str, $padding = 1)
	{
		$bbox = imagettfbbox ($fontsize, 0, $font, $str);
		$textWidth = $bbox[2] - $bbox[0];
		imagettftext ($image, $fontsize, 0, ImageSX($image) - $textWidth - $padding, $y, $fontcolor, $font, $str);
	}
	public static function imagettftextL($image, $fontsize, $y, $fontcolor, $font, $str, $padding = 1)
	{
		//imagettftext (resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text)
		imagettftext ($image, $fontsize, 0, $padding, $y, $fontcolor, $font, $str);
	}
	public static function imagettftextC($image, $fontsize, $y, $fontcolor, $font, $str)
	{
		$bbox = imagettfbbox ($fontsize, 0, $font, $str);
		$textWidth = $bbox[2] - $bbox[0];
		imagettftext ($image, $fontsize, 0, (int)(ImageSX($image) - $textWidth) / 2, $y, $fontcolor, $font, $str);
	}

	public static function decrypt_profile($profile)
	{
		if ($profile['message_type'] == 'html')
		{
			$profile['coupon_code_config'] = unserialize($profile['coupon_code_config']);
			$profile['coupon_value_config'] = unserialize($profile['coupon_value_config']);
			if (!empty($profile['expiration_config'])) $profile['expiration_config'] = unserialize($profile['expiration_config']);
			if (!empty($profile['freetext1_config'])) $profile['freetext1_config'] = unserialize($profile['freetext1_config']);
			if (!empty($profile['freetext2_config'])) $profile['freetext2_config'] = unserialize($profile['freetext2_config']);
			if (!empty($profile['freetext3_config'])) $profile['freetext3_config'] = unserialize($profile['freetext3_config']);
		}
		return $profile;				
	}
	
	public static function html2rgb($color)
	{ 
		if ($color[0] == '#') $color = substr($color, 1);
		if (strlen($color) == 6) list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
		elseif (strlen($color) == 3) list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else return false;
		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		return array($r, $g, $b);
	}
	public static function rgb2html($r, $g = -1, $b = -1)
	{
		if (is_array($r) && count($r) == 3) list($r, $g, $b) = $r;
		$r = intval($r);
		$g = intval($g);
		$b = intval($b);
		$r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
		$g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
		$b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));
		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
	
	public static function sendMail($from, $fromname, $to, $subject, $body, $mode = 0, $bcc = null, $attachments = null, $is_embed = false, $string_attachments = null)
	{
		if (!class_exists('AwoCouponMail')) require _PS_MODULE_DIR_.'awocoupon/lib//mail.php';
		return AwoCouponMail::sendMail($from, $fromname, $to, $subject, $body, $mode, $bcc, $attachments, $is_embed, $string_attachments);
	}

	public static function getValues($array)
	{
		foreach ($array as &$row)
		{
			if (is_array($row)) $row = awoHelper::getValues($row);
			else $row = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($row))));
		}
		return $array;
	}
	
	public static function is_multistore()
	{
		if (_PS_VERSION_ < '1.5') return false;
		
		static $feature_active = null;

		if ($feature_active === null)
			$feature_active = Configuration::getGlobalValue('PS_MULTISHOP_FEATURE_ACTIVE')
								&& (Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'shop') > 1);

		return $feature_active;
	}
	
	
	
	
	
	
	
	public static function profile_sendEmail($user, $codes, $profile_id, $tag_replace, $force_send = false)
	{
		if (!Validate::isEmail($user->email))
			return false;

		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$myawocoupon = new AwoCoupon();


		$profile = self::loadAssocList('SELECT p.*,pg.email_subject,pg.email_body,pg.pdf_header,pg.pdf_body,pg.pdf_footer FROM #__awocoupon_profile p JOIN #__awocoupon_profile_lang pg ON pg.profile_id=p.id AND pg.id_lang='.(int)$user->id_lang.' WHERE id='.(int)$profile_id);
		if (!empty($profile)) $profile = awoHelper::decrypt_profile(current($profile));
		if (empty($profile))
			return false;
	
		
		//print codes
		$text_gift = '';
		$attachments = $attachments_ids = array();
		$myprofiles = array();
		$cleanup_data = array();
		
		
		
		
		foreach ($codes as $k => $row)
		{
			$text_gift .= $myawocoupon->l('Voucher').': '.$row->coupon_code."\r\n".
							$myawocoupon->l('Value').': '.$row->coupon_price."\r\n".
							(!empty($row->expiration) ? $myawocoupon->l('Expiration').': '.trim($row->expiration, '"')."\r\n":'').
							"\r\n";
			
			if ($profile['message_type'] == 'html' && !is_null($profile['image']))
			{
				if (empty($row->profile)) $row->profile = $profile;
				$r_file = self::writeToImage($row->coupon_code, $row->coupon_price, !empty($row->expiration) ? strtotime($row->expiration) : 0, 'email', $row->profile, null, $tag_replace);
				if ($r_file === false)
				{
					if (!$force_send)
					{
						self::profile_cleanupError($cleanup_data, 'cannot create voucher images');
						return false;
					}
				}
				else
				{
					$attachments[] = $r_file;
					$attachments_ids[] = $row->id;
					$cleanup_data['files'][] = $r_file;
				}
			}
		}


		//vendor info
		$from_name = !empty($profile['from_name']) ? $profile['from_name'] : Configuration::get('PS_SHOP_NAME');
		$from_email = !empty($profile['from_email']) ? $profile['from_email'] : Configuration::get('PS_SHOP_EMAIL');
				
		// message info
		$subject = $profile['email_subject'];
		$bcc = !empty($profile['bcc_admin']) ? $from_email : null;
		$message = $profile['email_body'];
		$is_embed = false;
		$embed_text = '';
		if ($profile['message_type'] == 'text')
			if (strpos($message, '{vouchers}') === false) $message .= "\r\n".'{vouchers}';
		else
		{
			$text_gift = nl2br($text_gift);
			if (empty($attachments) && strpos($message, '{vouchers}') === false) $message .= '<br />{vouchers}';
			if (!empty($attachments))
			{
				if (strpos($message, '{image_embed}') !== false)
				{
					$is_embed = true;
					$i = 0;
					foreach ($attachments as $attachment) $embed_text .= '<div><img src="cid:couponimageembed'.(++$i).'"></div>';
				}
			}
		}
		array_push($tag_replace['find'], '{image_embed}', '{vouchers}');
		array_push($tag_replace['replace'], $embed_text, $text_gift);
		$message = str_replace($tag_replace['find'], $tag_replace['replace'], $message);
		
		if (self::sendMail($from_email, $from_name, $user->email, $subject, $message, $profile['message_type'] == 'html' ? 1 : 0, $bcc, $attachments, $is_embed) !== true)
		{
			self::profile_cleanupError($cleanup_data, 'cannot mail codes');
			return false;
		}
		
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$awoparams = new awoParams();
		if ((int)$awoparams->get('enable_frontend_image', 0) == 1)
		{
			$dir = _PS_MODULE_DIR_.'awocoupon/media/customers';
			@$user->id = (int)$user->id;
			if (empty($user->id)) @$user->id = (int)$user->id_customer;
			if (!empty($user->id))
			{
				$dir = $dir.'/'.$user->id;
				if (!file_exists($dir)) mkdir($dir, 0755);
				
				if (file_exists($dir))
				{
					// basic security
					file_put_contents($dir.'/.htaccess', 'error 500 !'); 
					file_put_contents($dir.'/index.html', 'error!'); 
				
					foreach ($attachments as $k => $file)
					{
						@$coupon_id = (int)$attachments_ids[$k];
						if (empty($coupon_id)) continue;
						if (empty($file) || !file_exists($file)) continue;
						
						$f2 = file_get_contents($file); 
						$fi = pathinfo($file); 
						$filename = self::file_makeSafe($fi['basename']); 
						$fcontent = urldecode('%3c%3fphp+die()%3b+%3f%3e').base64_encode($f2);
						
						// might not be compatible with FTP-based access
						file_put_contents($dir.'/'.$filename.'.php', urldecode('%3c%3fphp+die()%3b+%3f%3e').base64_encode($f2)); 
									
						// add table link
						$sql = 'INSERT INTO #__awocoupon_image (coupon_id,user_id,filename)
										VALUES ('.(int)$coupon_id.','.$user->id.',"'.awohelper::escape($filename).'")
										ON DUPLICATE KEY UPDATE filename="'.awohelper::escape($filename).'"';
						awohelper::query($sql);
					}
				}
			}
		}
			
		
		
		//delete created images
		if (!empty($cleanup_data['files'])) 
			foreach ($cleanup_data['files'] as $file)
			{
				if (!empty($file))
					@unlink($file);
			}
		
		return true;
	
	}
	public static function profile_cleanupError($cleanup_data, $message)
	{
//trigger_error($message);

		if (!empty($cleanup_data['files']))
		{
			foreach ($cleanup_data['files'] as $file)
			{
				if (!empty($file)) 
					@unlink($file);
			}
		}
		
		return false;
	}
	
	public static function file_makeSafe($file)
	{
		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		return preg_replace($regex, '', $file);
	}
	
	
	protected static $_hookModulesCache;
	public static function psHook($hook_name, $hook_args = array())
	{
		if (_PS_VERSION_ < '1.5')
		{
			//return Module::hookExec($hook_name,$hook_args);
			if (!Validate::isHookName($hook_name)) die(Tools::displayError());

			global $cart, $cookie;
			$live_edit = false;
			if (!isset($hook_args['cookie']) || !$hook_args['cookie']) $hook_args['cookie'] = $cookie;
			if (!isset($hook_args['cart']) || !$hook_args['cart']) $hook_args['cart'] = $cart;
			$hook_name = strtolower($hook_name);

			if (!isset(self::$_hookModulesCache))
			{
				$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
				$result = $db->ExecuteS('
					SELECT h.`name` as hook, m.`id_module`, h.`id_hook`, m.`name` as module, h.`live_edit`
					FROM `'._DB_PREFIX_.'module` m
					LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
					LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
					AND m.`active` = 1
					ORDER BY hm.`position`', false);
				self::$_hookModulesCache = array();

				if ($result)
					while ($row = $db->nextRow())
					{
						$row['hook'] = strtolower($row['hook']);
						if (!isset(self::$_hookModulesCache[$row['hook']]))
							self::$_hookModulesCache[$row['hook']] = array();
						self::$_hookModulesCache[$row['hook']][] = array('id_hook' => $row['id_hook'], 'module' => $row['module'], 'id_module' => $row['id_module'], 'live_edit' => $row['live_edit']);
					}
			}

			if (!isset(self::$_hookModulesCache[$hook_name])) return;

			$altern = 0;
			$output = array();
			foreach (self::$_hookModulesCache[$hook_name] as $array)
			{
				if (!($moduleInstance = Module::getInstanceByName($array['module']))) continue;

				$exceptions = $moduleInstance->getExceptions((int)$array['id_hook'], (int)$array['id_module']);
				foreach ($exceptions as $exception)
					if (strstr(basename($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'], $exception['file_name']) && !strstr($_SERVER['QUERY_STRING'], $exception['file_name'])) continue 2;

				if (is_callable(array($moduleInstance, 'hook'.$hook_name)))
				{
					$hook_args['altern'] = ++$altern;

					$display = $moduleInstance->{'hook'.$hook_name}($hook_args);
					$output[] = $display;
				}
			}
			return $output;
		}
		else
			return Hook::exec($hook_name, $hook_args, null, true);
		
	}
		
	public static function dateDisplay($date, $id_lang = null, $full = false)
	{
		if (empty($date)) return '';
		
		if (version_compare(_PS_VERSION_, '1.5', '>=')) $id_lang = null;
		
		if (version_compare(_PS_VERSION_, '1.5', '<') && empty($id_lang))
		{
			global $cookie;
			$id_lang = $cookie->id_lang;
		}
		return Tools::displayDate($date, $id_lang, $full);
	}
	
	
	public static function getPSAdminLink($module, $rest_of_link)
	{
		$link = '';
		if (_PS_VERSION_ < '1.5')
			$link = 'index.php?tab='.$module.'&token='.Tools::getAdminTokenLite($module).(!empty($rest_of_link) ? '&'.$rest_of_link : '');
		else 
			$link = Context::getContext()->link->getAdminLink($module).(!empty($rest_of_link) ? '&'.$rest_of_link : '');

		return $link;
	}
	
	public static function param_get($key, $default = '')
	{
		$value = Configuration::get('AWOCOUPON_'.Tools::strtoupper($key));
		if (is_bool($value) === true && !$value) $value = $default;
		return $value;
	}

	
	public static function select_id_lang($prefix = '')
	{
		if (!empty($prefix)) $prefix = $prefix.'.';
		return version_compare(_PS_VERSION_, '1.5.4', '<') ? '"'.Configuration::get('PS_LANG_DEFAULT').'" AS ': $prefix.'id_lang AS ';
	}
	
	public static function setLangData($elem_id, $id_lang, $text)
	{
		$elem_id = (int)$elem_id;
						
		$text = self::escape($text);
		
		if (empty($elem_id))
		{
			if (empty($text)) return;
			
			$elem_id = (int)self::loadResult('SELECT MAX(elem_id) FROM #__awocoupon_lang');
			$elem_id++;
					
		}

		$sql = 'INSERT INTO #__awocoupon_lang (elem_id,id_lang,text)
				VALUES ('.$elem_id.',"'.$id_lang.'","'.$text.'")
				ON DUPLICATE KEY UPDATE text="'.$text.'"';
		self::query($sql);
				
		return $elem_id;

	}
	
	public static function getLangUser($user_id = 0)
	{
		$languages = array();
		$user_id = (int)$user_id;
		
		$languages[] = (int)Context::getContext()->language->id;
		
		if (!empty($user_id))
			$languages[] = (int)self::loadResult('SELECT '.self::select_id_lang().' id_lang FROM #__customer WHERE id_customer='.$user_id); // user language
		
		$languages[] = (int)Configuration::get('PS_LANG_DEFAULT'); // site

		$languages[] = 1; // original installed language
		
		return array_unique($languages);
	}


	public static function getLangUserData($elem_id, $user_id = 0, $default = null)
	{
		$elem_id = (int)$elem_id;
		if (empty($elem_id)) return;
		
		static $stored_languages;
		if (!isset($stored_languages[$user_id])) $stored_languages[$user_id] = self::getLangUser($user_id);
		
		$languages = implode(',', $stored_languages[$user_id]);
		$sql = 'SELECT text FROM #__awocoupon_lang WHERE elem_id='.$elem_id.' AND id_lang IN ('.$languages.') ORDER BY FIELD(id_lang,'.$languages.')';
		$text = self::loadResult($sql);
		
		return !empty($text) ? $text : $default;
	}
	

	
	
	
	public static function getCountryList()
	{
		$id_lang = (int)Context::getContext()->language->id;

		$sql = 'SELECT c.id_country as id,c.id_country AS country_id,c2.name AS country_name
				  FROM #__country c
				  JOIN #__country_lang c2 ON c2.id_country=c.id_country AND c2.id_lang='.$id_lang.'
				 ORDER BY c2.name,c.id_country';
		return self::loadObjectList($sql);
	}
	
	public static function getCountryStateList($country_id = null)
	{
		$sql = 'SELECT id_state as id,name AS label
				  FROM #__state
				 WHERE 1=1
				 '.(!empty($country_id) ? ' AND id_country= "'.(int)$country_id.'" ' : '').'
				 ORDER BY name';
		return self::loadObjectList($sql);
	}
	public static function getCouponCountryState($coupon_id, $order_by = null)
	{
		$countrylist = $statelist = array();
		
		$coupon_ids = self::scrubids($coupon_id);
		if (empty($coupon_ids)) return;
		
		$id_lang = (int)Context::getContext()->language->id;
		
		$sql = 'SELECT a.coupon_id,a.asset_id,b.name AS asset_name,a.order_by,c.id_country as country_id,c2.name AS country_name
				  FROM #__awocoupon_asset1 a
				  JOIN #__state b ON b.id_state=a.asset_id
				  JOIN #__country c ON c.id_country=b.id_country
				  JOIN #__country_lang c2 ON c2.id_country=b.id_country AND c2.id_lang='.$id_lang.'
				 WHERE a.asset_type="countrystate" AND a.coupon_id IN ('.$coupon_ids.')
				 '.(!empty($order_by) ? $order_by : '');
		$statelist = self::loadObjectList($sql, 'asset_id');
		
		if (!empty($statelist))
		{
			foreach ($statelist as $item)
				$countrylist[$item->country_id] = (object)array('coupon_id'=>$item->coupon_id,'asset_id'=>$item->country_id, 'asset_name'=>$item->country_name,'order_by'=>'');
		}
		
		if (empty($countrylist))
		{
			$sql = 'SELECT a.coupon_id,a.asset_id,b2.name AS asset_name,a.order_by
					  FROM #__awocoupon_asset1 a
					  JOIN #__country b ON b.id_country=a.asset_id
					  JOIN #__country_lang b2 ON b2.id_country=b.id_country AND b2.id_lang='.$id_lang.'
					 WHERE a.asset_type="country" AND a.coupon_id IN ('.$coupon_ids.')
					 '.(!empty($order_by) ? $order_by : '');
			$countrylist = self::loadObjectList($sql, 'asset_id');
		}
		
		return array($countrylist,$statelist);
	}
	public static function where_id_shop($prefix = '')
	{
		if (!self::is_multistore()) return '';
		
		$context = Context::getContext();
		$shop_context = Shop::getContext();
		if ($shop_context == Shop::CONTEXT_ALL || ($context->controller->multishop_context_group == false && $shop_context == Shop::CONTEXT_GROUP)) return '';
		else if ($shop_context == Shop::CONTEXT_GROUP) return; //$value = 'g-'.Shop::getContextShopGroupID();
		
		return ' AND '.$prefix.'.id_shop='.Shop::getContextShopID().' ';
	}

	public static function select_order_num($px)
	{
		if (_PS_VERSION_ < '1.5') return $px.'.id_order';
		return $px.'.reference';
	}
	
	public static function getCaseSensitive() {
		$sql = 'SHOW FULL COLUMNS FROM #__awocoupon LIKE "coupon_code"';
		$rtn = array_change_key_case((array)self::loadObject($sql));
		return substr($rtn['collation'],-4)=='_bin' ? true : false;
	}

	public static function getOrderCoupon($order_id) {
		$awocoupon_details = awohelper::loadResult('SELECT details FROM #__awocoupon_history WHERE order_id='.(int)$order_id);
		if (empty($awocoupon_details)) return;
		
		$awocoupon_details = json_decode($awocoupon_details);
		if (!empty($awocoupon_details->cart_items))	{
			$awocoupon_details->cart_items = (array)$awocoupon_details->cart_items;
			$c_cart_items = array();
			foreach ($awocoupon_details->cart_items as $i => $r) $c_cart_items[(int)$i] = $r;
			$awocoupon_details->cart_items = $c_cart_items;
		}
		
		if (!empty($awocoupon_details->processed_coupons)) {
			$awocoupon_details->processed_coupons = (array)$awocoupon_details->processed_coupons;
			$c_processed_coupons = array();
			foreach ($awocoupon_details->processed_coupons as $i => $r) $c_processed_coupons[(int)$i] = $r;
			$awocoupon_details->processed_coupons = $c_processed_coupons;
		}
		
		$total_tax_toadd_s = $total_tax_toadd_p = $total_tax_toadd_g = 0;
		$total_amount_toadd_s = $total_amount_toadd_p = $total_amount_toadd_g = 0;
		$total_tax_actual_p = $total_tax_actual_s = $total_tax_actual_g = 0;
		foreach ($awocoupon_details->processed_coupons as $i => $r) {
			if ($r->is_discount_before_tax == 0) {
				$total_tax_toadd_p += $r->product_discount - $r->product_discount_notax;
				$total_tax_toadd_s += $r->shipping_discount - $r->shipping_discount_notax;
				$total_tax_toadd_g += $r->giftwrap_discount - $r->giftwrap_discount_notax;
				
				$total_amount_toadd_p += $r->product_discount_notax;
				$total_amount_toadd_s += $r->shipping_discount_notax;
				$total_amount_toadd_g += $r->giftwrap_discount_notax;
				
				$total_tax_actual_p += $r->product_discount_tax;
				$total_tax_actual_s += $r->shipping_discount_tax;
				$total_tax_actual_g += $r->giftwrap_discount_tax;
			}
		}
		
		$awocoupon_details->totalaftertax_product_tax = $total_tax_toadd_p;
		$awocoupon_details->totalaftertax_shipping_tax = $total_tax_toadd_s;
		$awocoupon_details->totalaftertax_giftwrap_tax = $total_tax_toadd_g;
		
		$awocoupon_details->totalaftertax_product_notax = $total_amount_toadd_p;
		$awocoupon_details->totalaftertax_shipping_notax = $total_amount_toadd_s;
		$awocoupon_details->totalaftertax_giftwrap_notax = $total_amount_toadd_g;
		
		$awocoupon_details->totalaftertax_product_tax_real = $total_tax_actual_p;
		$awocoupon_details->totalaftertax_shipping_tax_real = $total_tax_actual_s;
		$awocoupon_details->totalaftertax_giftwrap_tax_real = $total_tax_actual_g;
		
		return $awocoupon_details;
	}
}

awoHelper::init();

if (!function_exists('awotrace'))
{
	function awotrace()
	{
		ob_start();
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$rtn = ob_get_contents();
		ob_end_clean();
		return $rtn;
	}
}
if (!function_exists('printr'))
{
	function printr($a)
	{
		echo '<pre>'.print_r($a, 1).'</pre>';
	}
}
if (!function_exists('printrx'))
{
	function printrx($a)
	{
		echo '<pre>'.print_r($a, 1).'</pre>';
		exit;
	}
}

if (!function_exists('json_encode'))
{
	require_once _PS_MODULE_DIR_.'awocoupon/lib/JSON.php';
	function json_encode($data)
	{
		$json = new Services_JSON();
		return ($json->encode($data));
	}
}

if (!function_exists('json_decode'))
{
	require_once _PS_MODULE_DIR_.'awocoupon/lib/JSON.php';
	function json_decode($data, $bool = false)
	{
		if ($bool) $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		else $json = new Services_JSON();
		return ($json->decode($data));
	}
}


