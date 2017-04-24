<?php
/**
 * @component AwoCoupon 
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;
if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

class AwoCouponGiftcertModelFront
{
	
	public static function getCouponImage($id_customer, $coupon_code)
	{
		$id_customer = (int)$id_customer;
		if (empty($id_customer)) return array();


		$codes = array(); 


		
		$sql = 'SELECT i.filename FROM #__awocoupon_image i JOIN #__awocoupon c ON c.id=i.coupon_id WHERE i.user_id='.(int)$id_customer.' AND c.coupon_code="'.awohelper::escape($coupon_code).'"';
		$filename = awohelper::loadResult($sql);
		if (empty($filename)) return array();
		

		$filename = awohelper::file_makeSafe($filename); 
		$filename = str_replace('.php', '', awohelper::file_makeSafe($filename)); 
		$fi = pathinfo($filename); 
		if (!empty($fi['extension']))
		{
			$type = $fi['extension']; 
			
			$full_filename = _PS_MODULE_DIR_.'awocoupon/media/customers/'.$id_customer.'/'.$filename.'.php';
			if (file_exists($full_filename))
			{
				$fcontent = file_get_contents($full_filename); 
				$fcontent = str_replace(urldecode('%3c%3fphp+die()%3b+%3f%3e'), '', $fcontent); 
				$arr = array(); 
				$arr['c'] = $fcontent;
				$arr['t'] = strtolower($type);
				$img = $arr;
			}
		}
		
		
		if (empty($img)) return array();
		
		$file = str_replace('.php', '', $filename);		
		$url = _PS_VERSION_ < '1.5'
			? AWO_URI.'/account.php?option=giftcert&display=ajax&format=image&file='.$file
			: Context::getContext()->link->getModuleLink('awocoupon', 'giftcert', array('format'=>'image','file'=>$file));

		//$url = JURI::base().'index.php?option=com_awocoupon&amp;format=image&amp;view=giftcerts&amp;file='.$file; 
		//$url = __PS_BASE_URI__.'index.php?fc=module&module=awocoupon&controller=giftcert&format=image&file='.$file;
		$img_code = '<img src="'.$url.'" alt="coupon" />';
		
		return array(
			'image'=>$img_code,
			'b64'=>$img, // comment line if you get out of memory error, the img is base64 encoded image
			'url'=>$url,
		);
	}
	
	/*
	 * this is safe function where filename can be handled from URL parameters
	 */
	public static function getRawCouponImage($filename, $user_id = 0)
	{
		// security
		$filename = awohelper::file_makeSafe($filename); 
		if (strpos($filename, '/') !== false) die('security alert sent to administrator'); 
		if (strpos($filename, '..') !== false) die('security alert sent to administrator'); 
		$fi = pathinfo($filename); 
		$filename = $fi['basename']; 
		//echo $filename; die();
		// end of security
		
		
		$user_id = (int)$user_id;
		if (empty($user_id)) return false;

		$fi = pathinfo($filename); 
		if (empty($fi['extension'])) return false;
		$type = $fi['extension']; 
		$full_filename = _PS_MODULE_DIR_.'awocoupon/media/customers/'.$user_id.'/'.$filename.'.php';

		if (!file_exists($full_filename)) return false;
		$fcontent = file_get_contents($full_filename); 
		$fcontent = str_replace(urldecode('%3c%3fphp+die()%3b+%3f%3e'), '', $fcontent); 
		return $fcontent; // base64_encoded content of the file
	}
	

}
