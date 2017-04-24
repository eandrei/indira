<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponGiftcertHandler
{

	public static function process($inparams)
	{
		$order = new Order((int)($inparams['id_order']));
		if (empty($order->id)) return;
//echo '<pre>'; print_r($order);

		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$params = new awoParams();
		
		$allcodes = array();
		$cleanup_data = array();
		$sql = 'SELECT i.id_order_detail AS order_item_id,i.id_order AS order_id,i.product_price,i.product_quantity,
						u.id_customer AS user_id,u.email,u.firstname AS first_name,u.lastname AS last_name,ap.expiration_number,
						ap.expiration_type,ap.coupon_template_id,c.iso_code AS order_currency,ap.profile_id,i.product_id,i.product_reference,
						i.product_attribute_id,o.id_cart,i.product_name AS order_item_name,ap.vendor_name,ap.vendor_email,o.id_currency,
						ap.recipient_email_id,ap.recipient_name_id,ap.recipient_mesg_id,o.id_lang
						,"" as custom_price,i.product_attribute_id as id_product_attribute
						
				  FROM #__order_detail i 
				  JOIN #__orders o ON o.id_order=i.id_order
				  JOIN #__awocoupon_giftcert_product ap ON ap.product_id=i.product_id
				  JOIN #__customer u ON u.id_customer=o.id_customer
				  LEFT JOIN #__awocoupon_giftcert_order g ON g.order_id=i.id_order AND g.email_sent=1
				  LEFT JOIN #__currency c ON c.id_currency=o.id_currency
				 WHERE i.id_order='.$order->id.' AND g.order_id IS NULL AND ap.published=1
				 GROUP BY i.id_order_detail';
		//exit($sql);
		$rows = awoHelper::loadObjectList($sql);
		self::generate_auto_email($order, $rows, $params);

	}
	
	public static function process_resend($order_id, $rows)
	{
		$order = new Order((int)$order_id);
		if (empty($order->id)) return;
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$params = new awoParams();

		return self::generate_auto_email($order, $rows, $params, false);
		
	}
	
	
	public static function generate_auto_email($order, $rows, $params, $is_new = true)
	{
		if (empty($rows)) return;
		
		global $cookie;

		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		require_once _PS_CLASS_DIR_.'Tools.php';
		require_once _PS_CLASS_DIR_.'Validate.php';
		require_once _PS_CLASS_DIR_.'Currency.php';
		$myawocoupon = new AwoCoupon();

		
		$allcodes = array();
		$cleanup_data = array();
		
		//$tmp = current($rows)
		//$currency_class = new Currency($tmp->id_currency);
		$currency_class = Currency::getDefaultCurrency();
		
		$mail_key = array();
				
		// retreive gift cert profile
		$profiles = array();
		$profile_default = awoHelper::loadAssocList('SELECT p.*,pg.email_subject,pg.email_body,pg.pdf_header,pg.pdf_body,pg.pdf_footer FROM #__awocoupon_profile p JOIN #__awocoupon_profile_lang pg ON pg.profile_id=p.id AND pg.id_lang='.$order->id_lang.' WHERE is_default=1');
		if (empty($profile_default))
		{
			$profile_default = awoHelper::loadAssocList('SELECT p.*,pg.email_subject,pg.email_body,pg.pdf_header,pg.pdf_body,pg.pdf_footer FROM #__awocoupon_profile p JOIN #__awocoupon_profile_lang pg ON pg.profile_id=p.id AND pg.id_lang='.$order->id_lang.' LIMIT 1');
			if (empty($profile_default))
			{
				self::cleanup_error($cleanup_data, $is_new, 'could not find a gift certificate profile');
				return false;
			}
		}
		$profile_default = awoHelper::decrypt_profile(current($profile_default));
		
		$purchaser_user_id = $customer_first_name = $customer_last_name = '';
		foreach ($rows as $row)
		{
			//$this_mail_key = self::getproductattributes($row,$params);
			//$mail_key[$this_mail_key['email']] = $this_mail_key;
			$to_properties = self::getproductattributes($row);
			foreach ($to_properties as $x) $mail_key[$x['email']] = $x;
//echo '<pre>'; print_r($mail_key); print_r($to_properties); exit;

			$current_profile = $profile_default;
			if (!empty($row->profile_id))
			{
				if (!empty($profiles[$row->profile_id])) $current_profile = $profiles[$row->profile_id];
				else
				{
					$current_profile = awoHelper::loadAssocList('SELECT p.*,pg.email_subject,pg.email_body,pg.pdf_header,pg.pdf_body,pg.pdf_footer FROM #__awocoupon_profile p JOIN #__awocoupon_profile_lang pg ON pg.profile_id=p.id AND pg.id_lang='.$order->id_lang.' WHERE id='.(int)$row->profile_id);
					$current_profile = empty($current_profile) 
							? $profile_default 
							: awoHelper::decrypt_profile(current($current_profile));
				}
			}
			$profiles[$current_profile['id']] = $current_profile;
					
			$purchaser_user_id = $row->user_id;
			$customer_first_name = $row->first_name;
			$customer_last_name = $row->last_name;
			if ($is_new)
			{
				for ($i = 0; $i < $row->product_quantity; $i++)
				{
					$this_mail_key = current(array_keys($to_properties));
					if (empty($this_mail_key)) break;
					$to_properties[$this_mail_key]['quantity'] -= 1;
					if ($to_properties[$this_mail_key]['quantity'] < 1) unset($to_properties[$this_mail_key]);
					
					
//echo($this_mail_key);
//echo '<pre>'; print_r($mail_key); print_r($to_properties); exit;
					$code = self::get_giftcertcode($row, $params, $cleanup_data);
					if (empty($code->coupon_id))
					{
						self::cleanup_error($cleanup_data, $is_new, 'could not create coupon code');
						return;
					}
						
					$sql = 'SELECT id,coupon_code,expiration,coupon_value,coupon_value_type FROM #__awocoupon WHERE id='.$code->coupon_id;
					$coupon_row = awoHelper::loadObjectList($sql);
					$coupon_row = current($coupon_row);
					if (empty($coupon_row))
					{
						self::cleanup_error($cleanup_data, $is_new, 'could not find coupon');
						return;
					}

					if(!empty($row->custom_price)) {
						awoHelper::query('UPDATE #__awocoupon SET coupon_value='.(float)$row->custom_price.',coupon_value_type="amount" WHERE id='.$code->coupon_id);
						$coupon_row->coupon_value_type = 'amount';
						$coupon_row->coupon_value = $row->custom_price;
					}
						
					awoHelper::query('UPDATE #__awocoupon SET order_id='.$row->order_id.' WHERE id='.$code->coupon_id);
						
					$price = '';
					if (!empty($coupon_row->coupon_value))
						$price = $coupon_row->coupon_value_type == 'amount' 
										? Tools::displayPrice($coupon_row->coupon_value, $currency_class)
										: round($coupon_row->coupon_value).'%';

					$allcodes[$this_mail_key][] = array(
										'id'=>$coupon_row->id,
										'order_item_id'=>$row->order_item_id,
										'user_id'=>$row->user_id,
										'product_id'=>$row->product_id,
										'product_name'=>$row->order_item_name,
										'email'=>$row->email,
										'code'=>$coupon_row->coupon_code,
										'price'=>$price,
										'expiration'=>$coupon_row->expiration,
										'expirationraw'=>!empty($coupon_row->expiration) ? strtotime($coupon_row->expiration) : 0,
										'profile'=>$current_profile,
										'file'=>'',
								);
					if (!empty($row->vendor_email))
					{
						$vendor_codes[$row->vendor_email]['name'] = $row->vendor_name;
						$vendor_codes[$row->vendor_email]['codes'][] = $coupon_row->coupon_code.' - '.$price;
					}
					if (!empty($row->vendor_email) && (int)$params->get('giftcert_vendor_enable', 0) == 1)
					{
						$code_format = $params->get('giftcert_vendor_voucher_format', '<div>{voucher} - {price} - {product_name}</div>');
						if (strpos($code_format, '{voucher}') === false) $message .= '<div>{voucher}</div>';
						$vendor_codes[$row->vendor_email]['name'] = $row->vendor_name;
						$vendor_codes[$row->vendor_email]['codes'][] = str_replace(array('{voucher}','{price}','{product_name}','{purchaser_first_name}','{purchaser_last_name}','{today_date}','{order_id}',),
									array($coupon_row->coupon_code, $price, $row->order_item_name,$row->first_name,$row->last_name,awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false),$order->id,),
									$code_format);
					}
				}
			} 
			else
			{
				if (empty($row->coupons)) continue;
				
				foreach ($row->coupons as $kc => $crow)
				{
					$this_mail_key = current(array_keys($to_properties));
					if (empty($this_mail_key)) break;
					$to_properties[$this_mail_key]['quantity'] -= 1;
					if ($to_properties[$this_mail_key]['quantity'] < 1) unset($to_properties[$this_mail_key]);
				
				
					$crow['profile'] = $current_profile;
					if (substr($crow['price'], -1) != '%') $crow['price'] = Tools::displayPrice($crow['price'], $currency_class);
					$allcodes[$this_mail_key][] = $crow;
				}
			}
		}

		if (empty($allcodes)) return;
		
		// load language file
				
		$codes = array();
				
		$store_name = Configuration::get('PS_SHOP_NAME');
		$vendor_from_email = Configuration::get('PS_SHOP_EMAIL');

		foreach ($allcodes as $this_mail_key => $mycodes)
		{
			//print codes
			$text_gift = '';
			$attachments = array();
			$myprofiles = array();
			$products = array();
			foreach ($mycodes as $k => $row)
			{
				$codes_array = array('i'=>$row['order_item_id'],'p'=>$row['product_id'],'c'=>$row['code'],'cid'=>$row['id']);
				$text_gift .= $myawocoupon->l('Gift Certificate').': '.$row['code']."\r\n".
								$myawocoupon->l('Value').': '.$row['price']."\r\n".
								(!empty($row['expiration']) ? $myawocoupon->l('Expiration').': '.awoHelper::dateDisplay(trim($row['expiration'], '"'))."\r\n":'').
								"\r\n";
				$products[$row['product_name']] = 1;
				$myprofiles[$row['profile']['id']] = $row['profile'];
						
						
				if ($row['profile']['message_type'] == 'html' && !is_null($row['profile']['image']))
				{
					$r_file = awoHelper::writeToImage($row['code'],
								$row['price'],
								$row['expirationraw'],
								'email',
								$row['profile'],
								null,
								array(
									'find'=>array(	'{siteurl}',
											'{store_name}',
											'{purchaser_first_name}',
											'{purchaser_last_name}',
											'{recipient_name}',
											'{recipient_email}',
											'{recipient_message}',
											'{order_id}',
											'{order_total}',
											'{order_date}',
											//'{order_link}',
											'{today_date}',
											'{product_name}',
										),
									'replace'=>array(	awoHelper::shop_url(),
											$store_name,
											$customer_first_name,
											$customer_last_name,
											$mail_key[$this_mail_key]['recipient_name'],
											$this_mail_key,
											$mail_key[$this_mail_key]['message'],
											$order->id,
											Tools::displayPrice($order->total_paid, $currency_class),
											awohelper::dateDisplay($order->date_add, (int)$cookie->id_lang, false),
											awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false),
											$row['product_name'],
										), 	
									));
					if ($r_file === false)
					{
						self::cleanup_error($cleanup_data, $is_new, 'cannot create gift certificate images');
						return false;
					}
					$attachments[] = $r_file;
					$allcodes[$this_mail_key][$k]['file'] = $r_file;
					$cleanup_data['files'][] = $r_file;
					
					
					if ((int)$params->get('enable_frontend_image', 0) == 1)
					{
						$fi = pathinfo($r_file); 
						$fname = $fi['basename']; 
						$codes_array['f'] = awohelper::file_makeSafe($fi['basename']); 
					}
					
				}
				$codes[] = $codes_array;
			}
			$allcodes[$this_mail_key]['text_gift'] = $text_gift;
			$allcodes[$this_mail_key]['attachments'] = $attachments;
			$allcodes[$this_mail_key]['profiles'] = $myprofiles;
			$allcodes[$this_mail_key]['products'] = $products;
				
			//email codes
			if (!Validate::isEmail($this_mail_key))
			{
				self::cleanup_error($cleanup_data, $is_new, 'invalid to email');
				return false;
			}
		}



		foreach ($allcodes as $this_mail_key => $mycodes)
		{
			//USE DEFAULT profile 
			//$profile = $profile_default;
			//if (isset($profiles[$profile_default['id']])) $profile = $profile_default;
			//else {
			//	$profile = count($profiles) == 1 ? current($profiles) : $profile_default;
			//}
			//profile logic 
			$profile = $profile_default;
			if (isset($mycodes['profiles'][$profile_default['id']])) $profile = $profile_default;
			else $profile = count($mycodes['profiles']) == 1 ? current($mycodes['profiles']) : $profile_default;
				
			//vendor info
			$from_name = !empty($profile['from_name']) ? $profile['from_name'] : $store_name;
			$from_email = !empty($profile['from_email']) ? $profile['from_email'] : $vendor_from_email;
				
			// message info
			$to = $this_mail_key;
			$subject = $profile['email_subject'];
			$bcc = !empty($profile['bcc_admin']) ? $from_email : null;
			$message = $profile['email_body'];
			$is_embed = false;
			$embed_text = '';
			if ($profile['message_type'] == 'text') {
				if (strpos($message, '{vouchers}') === false) $message .= "\r\n".'{vouchers}';
			}
			else {
				$mycodes['text_gift'] = nl2br($mycodes['text_gift']);
				if (is_null($profile['image']) && strpos($message, '{vouchers}') === false) $message .= '<br />{vouchers}';
				if (!is_null($profile['image']))
				{
					if (strpos($message, '{image_embed}') !== false)
					{
						$is_embed = true;
						$i = 0;
						foreach ($mycodes['attachments'] as $attachment) $embed_text .= '<div><img src="cid:couponimageembed'.(++$i).'"></div>';
					}
				}
			}
			
			
			$string_attachments = array();
			if ($profile['message_type'] == 'html' && !empty($profile['is_pdf']) && !empty($profile['pdf_body']))
			{
				$pdf_image_embed = '';
				if (!is_null($profile['image']) && strpos($profile['pdf_body'], '{image_embed}') !== false)
					foreach ($mycodes['attachments'] as $attachment) $pdf_image_embed .= '<div><img src="'.$attachment.'"></div>';
				
				$pdf_body = str_replace(array(	'{siteurl}',
										'{store_name}',
										'{vouchers}',
										'{purchaser_first_name}',
										'{purchaser_last_name}',
										'{recipient_name}',
										'{recipient_email}',
										'{recipient_message}',
										'{order_id}',
										'{order_total}',
										'{order_date}',
										'{today_date}',
										'{product_name}',
										'{image_embed}',
									),
								array(	awoHelper::shop_url(),
										$store_name,
										$mycodes['text_gift'],
										$customer_first_name,
										$customer_last_name,
										$mail_key[$this_mail_key]['recipient_name'],
										$to,
										$mail_key[$this_mail_key]['message'],
										$order->id,
										Tools::displayPrice($order->total_paid, $currency_class),
										awohelper::dateDisplay($order->date_add, (int)$cookie->id_lang, false),
										awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false),
										implode(', ', array_keys($mycodes['products'])),
										$pdf_image_embed,
									), 
								$profile['pdf_body']);
							
							
				require_once _PS_MODULE_DIR_.'awocoupon/lib/html2tcpdf.php';
				$html2pdf = new Html2Tcpdf($profile['pdf_header'], $pdf_body, $profile['pdf_footer']);
				$pdf_data = $html2pdf->processpdf('S');
				
				//$pdf_data = awoHelper::html2pdf($profile['pdf_header'],$profile['pdf_body'],$profile['pdf_footer'],'S');
				if (!empty($pdf_data)) $string_attachments['attachment.pdf'] = $pdf_data;

			}

			$message = str_replace(array(	'{siteurl}',
									'{store_name}',
									'{vouchers}',
									'{image_embed}',
									'{purchaser_first_name}',
									'{purchaser_last_name}',
									'{recipient_name}',
									'{recipient_email}',
									'{recipient_message}',
									'{order_id}',
									'{order_total}',
									'{order_date}',
									//'{order_link}',
									'{today_date}',
									'{product_name}',
								), array(	awoHelper::shop_url(),
									$store_name,
									$mycodes['text_gift'],
									$embed_text,
									$customer_first_name,
									$customer_last_name,
									$mail_key[$this_mail_key]['recipient_name'],
									$to,
									$mail_key[$this_mail_key]['message'],
									$order->id,
									Tools::displayPrice($order->total_paid, $currency_class),
									awohelper::dateDisplay($order->date_add, (int)$cookie->id_lang, false),
									awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false),
									implode(', ', array_keys($mycodes['products'])),
								), 
							$message);
		
			if (awoHelper::sendMail($from_email, 
					$from_name, 
					$to, 
					$subject, 
					$message,
					$profile['message_type'] == 'html' ? 1 : 0, 
					$bcc, 
					$mycodes['attachments'], 
					$is_embed,
					$string_attachments) !== true)
			{
				self::cleanup_error($cleanup_data, $is_new, 'cannot mail codes');
				return false;
			}
		}

		if ($is_new)
		{
			//update giftcert table so we dont send them more coupons by mistake
			$codes_compact = urldecode(http_build_query($codes));
			awoHelper::query('INSERT INTO #__awocoupon_giftcert_order (order_id,user_id,email_sent,codes) VALUES ('.$order->id.','.$purchaser_user_id.',1,"'.$codes_compact.'")');
			$giftcert_order_id = Db::getInstance()->Insert_ID();
			
			// insert each code into its own row
				$insert_sql = array();
				foreach ($codes as $code)
					$insert_sql[] = '('.(int)$giftcert_order_id.','.(int)$code['i'].','.(int)$code['p'].','.(int)$code['cid'].',"'.awohelper::escape($code['c']).'")';
				if (!empty($insert_sql)) 
					awohelper::query('INSERT INTO #__awocoupon_giftcert_order_code (giftcert_order_id,order_item_id,product_id,coupon_id,code) VALUES '.implode(',', $insert_sql));

			if ((int)$params->get('giftcert_vendor_enable', 0) == 1 && !empty($vendor_codes))
			{
				$t_subject = $params->get('giftcert_vendor_subject', 'Vendor Email - Codes');
				$t_message = $params->get('giftcert_vendor_email', '');
				if (strpos($t_message, '{vouchers}') === false) $t_message .= '<br /><br />{vouchers}<br />';
				foreach ($vendor_codes as $vendor_email => $codes)
				{
					if (empty($vendor_email) || !Validate::isEmail($vendor_email)) continue;
					$subject = str_replace(array('{vendor_name}','{purchaser_first_name}','{purchaser_last_name}','{order_id}','{today_date}',),
							array($codes['name'],$customer_first_name,$customer_last_name,$order->id,awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false)),
							$t_subject);
					$message = str_replace(array('{vendor_name}','{vouchers}','{purchaser_first_name}','{purchaser_last_name}','{order_id}','{today_date}',),
							array($codes['name'],implode('', $codes['codes']),$customer_first_name,$customer_last_name,$order->id,awohelper::dateDisplay(date('Y-m-d H:i:s'), (int)$cookie->id_lang, false)),
							$t_message);
					awoHelper::sendMail($vendor_from_email, $store_name, $vendor_email, $subject, $message, 1);
				}
			}
			
			
			// save for display in front end
			if ((int)$params->get('enable_frontend_image', 0) == 1)
			{

				$dir = _PS_MODULE_DIR_.'awocoupon/media/customers';
				if (!empty($purchaser_user_id))
				{
					$dir = $dir.'/'.$purchaser_user_id;
					if (!file_exists($dir)) mkdir($dir, 0755);
					
					if (file_exists($dir))
					{
						// basic security
						file_put_contents($dir.'/.htaccess', 'error 500 !'); 
						file_put_contents($dir.'/index.html', 'error!'); 
						
						foreach ($allcodes as $mycodes)
						{
							foreach ($mycodes as $k => $row)
							{
								if (!empty($row['file']) && file_exists($row['file']))
								{
									$f2 = file_get_contents($row['file']); 
									$fi = pathinfo($row['file']); 
									$filename = awohelper::file_makeSafe($fi['basename']); 
									$fcontent = urldecode('%3c%3fphp+die()%3b+%3f%3e').base64_encode($f2);

									// might not be compatible with FTP-based access
									file_put_contents($dir.'/'.$filename.'.php', urldecode('%3c%3fphp+die()%3b+%3f%3e').base64_encode($f2)); 
									
									// add table link
									$sql = 'INSERT INTO #__awocoupon_image (coupon_id,user_id,filename)
													VALUES ('.(int)$row['id'].','.$purchaser_user_id.',"'.awohelper::escape($filename).'")
													ON DUPLICATE KEY UPDATE filename="'.awohelper::escape($filename).'"';
									awohelper::query($sql);
								}
							}
						} 
					}
				}
			}
			
			
			
		}

		//delete created images
		foreach ($allcodes as $mycodes)
			foreach ($mycodes as $k => $row)
				if (!empty($row['file'])) @unlink($row['file']);
	
		return true;
	}
	
	public static function getproductattributes($row)
	{
		$attrlist = array();
		if (!empty($row->recipient_email_id))
		{
			$sql = 'SELECT cd.`id_customization`, c.`id_product`, c.`id_product_attribute`, cd.`type`, cd.`index`, cd.`value`,c.quantity
					  FROM `#__customized_data` cd
					  JOIN `#__customization` c ON c.id_customization=cd.id_customization
					 WHERE c.`id_cart` = '.(int)$row->id_cart.' AND c.id_product='.(int)$row->product_id.'
					   AND cd.index IN ('.(int)$row->recipient_email_id.','.(int)$row->recipient_name_id.','.(int)$row->recipient_mesg_id.')
					   AND c.id_product_attribute='.(int)$row->product_attribute_id.'';
			$tmp = awoHelper::loadObjectList($sql);
			if (!empty($tmp))
			{
				$custom_arr = array();
				foreach ($tmp as $r)
				{
					if ($r->index == $row->recipient_email_id) $custom_arr[$r->id_customization]['email'] = trim($r->value);
					elseif ($r->index == $row->recipient_name_id) $custom_arr[$r->id_customization]['recipient_name'] = trim($r->value);
					elseif ($r->index == $row->recipient_mesg_id) $custom_arr[$r->id_customization]['message'] = trim($r->value);
					$custom_arr[$r->id_customization]['quantity'] = $r->quantity;
				}
				
				$rtn = array();
				$total_qty = 0;
				foreach ($custom_arr as $r)
				{
					if (empty($r['email']) || !Validate::isEmail($r['email']))
					{
						if (!empty($rtn[$row->email])) $rtn[$row->email]['quantity'] += $r['quantity'];
						else $rtn[$row->email] = array('recipient_name'=>$row->first_name.' '.$row->last_name,'email'=>$row->email,'message'=>'','quantity'=>$r['quantity']);
					}
					else
					{
						if (!empty($rtn[$r['email']])) $rtn[$r['email']]['quantity'] += $r['quantity'];
						else $rtn[$r['email']] = $r;
					}
					$total_qty += $r['quantity'];
				}
				$remaining_qty = $row->product_quantity - $total_qty;
				if ($remaining_qty > 0)
				{
					if (!empty($rtn[$row->email])) $rtn[$row->email]['quantity'] += $remaining_qty;
					else $rtn[$row->email] = array('recipient_name'=>$row->first_name.' '.$row->last_name,'email'=>$row->email,'message'=>'','quantity'=>$remaining_qty);
				}
				return $rtn;
			}
		}
		return array($row->email=>array('recipient_name'=>$row->first_name.' '.$row->last_name,'email'=>$row->email,'message'=>'','quantity'=>$row->product_quantity));
	}


	public static function cleanup_error($cleanup_data, $is_new, $message)
	{
//trigger_error($message);

		if (!empty($cleanup_data['files']))
			foreach ($cleanup_data['files'] as $file)
				if (!empty($file)) @unlink($file);
		
		if (!$is_new) return false;
		
		if (!empty($cleanup_data['coupon_codes']))
			foreach ($cleanup_data['coupon_codes'] as $coupon_id)
				awoHelper::query('DELETE FROM #__awocoupon WHERE id='.$coupon_id);
		
		if (!empty($cleanup_data['manual_codes']))
			foreach ($cleanup_data['manual_codes'] as $product_id => $codes)
				awoHelper::query('UPDATE #__awocoupon_giftcert_code SET status="active" WHERE status="used" AND product_id='.$product_id.' AND code IN ("'.implode('","', $codes).'")');

		return false;
	}
	
	public static function get_giftcertcode($order_row, $params, &$cleanup_data)
	{
		$update_codes = false;

		$coupon_code = null;
		$expirationdays = null;
		
		$usedstr = !empty($cleanup_data['manual_codes'][$order_row->product_id]) ? ' AND code NOT IN ("'.implode('","', $cleanup_data['manual_codes'][$order_row->product_id]).'")' : '';
		$sql = 'SELECT code FROM #__awocoupon_giftcert_code WHERE product_id='.$order_row->product_id.' AND status="active" '.$usedstr;
		$tmp = awoHelper::loadResult($sql);
		if (!empty($tmp)) $coupon_code = $tmp;
		
		
		if (!empty($order_row->expiration_number) && !empty($order_row->expiration_type))
		{
			if ($order_row->expiration_type == 'day') $expirationdays = (int)$order_row->expiration_number;
			elseif ($order_row->expiration_type == 'month') $expirationdays = (int)$order_row->expiration_number * 30;
			elseif ($order_row->expiration_type == 'year') $expirationdays = (int)$order_row->expiration_number * 365;
		}
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/plgautogenerate.php';
		$rtn = awoAutoGenerate::generateCoupon($order_row->coupon_template_id, $coupon_code, $expirationdays);
		if (empty($rtn->coupon_id))
			return;

		$cleanup_data['coupon_codes'][] = $rtn->coupon_id;
		
		
		if (!empty($coupon_code) && $rtn->coupon_code == $coupon_code)
		{
			$cleanup_data['manual_codes'][$order_row->product_id][] = $rtn->coupon_code;
			awoHelper::query('UPDATE #__awocoupon_giftcert_code SET status="used" WHERE product_id='.$order_row->product_id.' AND code="'.$rtn->coupon_code.'" AND status="active"');
		}
		
		if ((int)$params->get('giftcert_coupon_activate', 0) == 1)
			awoHelper::query('UPDATE #__awocoupon SET published=-1 WHERE id='.(int)$rtn->coupon_id);

		
		return $rtn;
	}

}
