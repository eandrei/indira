<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class awoAutoGenerate  {

	static function getCouponTemplates() {
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		return awoHelper::loadObjectList('SELECT id,coupon_code FROM '._DB_PREFIX_.'awocoupon WHERE published=-2 ORDER BY coupon_code,id');
	}
	
	static function generateCoupon($coupon_id,$coupon_code=null,$expiration=null,$override_user=null) {
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

		$coupon_id = (int)$coupon_id;
		if(!is_null($override_user)) $override_user = trim($override_user);
		if(!is_null($expiration)) $expiration = trim($expiration);
		
		$crow = awoHelper::loadObjectList('SELECT * FROM '._DB_PREFIX_.'awocoupon WHERE id='.$coupon_id);
		if(empty($crow)) return false;  // template coupon does not exist
		$crow = current($crow);
		
		if(empty($coupon_code)) $coupon_code = self::generateCouponCode();
		elseif(self::isCodeUsed($coupon_code)) $coupon_code = self::generateCouponCode();

		$db_expiration = !empty($crow->expiration) ? '"'.$crow->expiration.'"' : 'NULL';
		if($crow->function_type!='parent' && !empty($expiration) && ctype_digit($expiration)) {
			$db_expiration = '"'.date('Y-m-d',time()+(86400*(int)$expiration)).'"';
		}

		$passcode = substr( md5((string)time().rand(1,1000).$coupon_code ), 0, 6);

		$sql = 'INSERT INTO '._DB_PREFIX_.'awocoupon (	template_id,coupon_code,passcode,coupon_value_type,coupon_value,coupon_value_def,
										function_type,num_of_uses_total,num_of_uses_percustomer,
										min_value,discount_type,user_type,
										startdate,expiration,
										note,params,published)
				VALUES ('.$coupon_id.',
						"'.$coupon_code.'",
						"'.$passcode.'",
						'.(!empty($crow->coupon_value_type) ? '"'.$crow->coupon_value_type.'"' : 'NULL').',
						'.(!empty($crow->coupon_value) ? $crow->coupon_value : 'NULL').',
						'.(!empty($crow->coupon_value_def) ? '"'.$crow->coupon_value_def.'"' : 'NULL').',
						"'.$crow->function_type.'",
						'.(!empty($crow->num_of_uses_total) ? $crow->num_of_uses_total : 'NULL').',
						'.(!empty($crow->num_of_uses_percustomer) ? $crow->num_of_uses_percustomer : 'NULL').',
						'.(!empty($crow->min_value) ? $crow->min_value : 'NULL').',
						'.(!empty($crow->discount_type) ? '"'.$crow->discount_type.'"' : 'NULL').',
						'.(!empty($crow->user_type) ? '"'.$crow->user_type.'"' : 'NULL').',
						'.(!empty($crow->startdate) ? '"'.$crow->startdate.'"' : 'NULL').',
						'.$db_expiration.',
						'.(!empty($crow->note) ? '"'.$crow->note.'"' : 'NULL').',
						'.(!empty($crow->params) ? '"'.pSQL( $crow->params).'"' : 'NULL').',
						1
					)';
		awoHelper::query($sql);
		$gen_coupon_id = Db::getInstance()->Insert_ID();
		
		if($crow->function_type!='parent' && !empty($override_user) && ctype_digit(trim($override_user))) {
			$sql = 'INSERT INTO '._DB_PREFIX_.'awocoupon_user ( coupon_id,user_id ) VALUES ( '.$gen_coupon_id.','.$override_user.' )';
			awoHelper::query($sql);
		} else {
			self::populateTable(1,$coupon_id,$gen_coupon_id,'awocoupon_user','user_id');
			self::populateTable(1,$coupon_id,$gen_coupon_id,'awocoupon_usergroup','shopper_group_id');
		}

		self::populateTable(3,$coupon_id,$gen_coupon_id,'awocoupon_asset1');
		self::populateTable(3,$coupon_id,$gen_coupon_id,'awocoupon_asset2');
		self::populateTable(4,$coupon_id,$gen_coupon_id,'awocoupon_shop');
		
		self::populateTable(1,$coupon_id,$gen_coupon_id,'awocoupon_tag','tag');

		$obj = new stdClass();
		$obj->coupon_id = $gen_coupon_id;
		$obj->coupon_code = $coupon_code;
		return $obj;
	}
	
	static private function populateTable($type,$coupon_id,$gen_coupon_id,$table,$column1='') {
		$insert_str = '';

		if($type==1) {
			$sql = 'INSERT INTO '._DB_PREFIX_.$table.' (coupon_id,'.$column1.') 
						SELECT '.$gen_coupon_id.','.$column1.' FROM '._DB_PREFIX_.$table.' WHERE coupon_id='.$coupon_id;
			awoHelper::query($sql);
		}
		elseif($type==3) {
			$sql = 'INSERT INTO '._DB_PREFIX_.$table.' (coupon_id,asset_type,asset_id,order_by) 
						SELECT '.$gen_coupon_id.',asset_type,asset_id,order_by FROM '._DB_PREFIX_.$table.' WHERE coupon_id='.$coupon_id;
			awoHelper::query($sql);
		}
		elseif($type==4) {
			$sql = 'INSERT INTO '._DB_PREFIX_.$table.' (coupon_id,id_shop) 
						SELECT '.$gen_coupon_id.',id_shop FROM '._DB_PREFIX_.$table.' WHERE coupon_id='.$coupon_id;
			awoHelper::query($sql);
		}
		
	}
	
	static private function generateCouponCode() {
		$salt = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		do { $coupon_code = self::randomCode(rand(8,12),$salt); } while (self::isCodeUsed($coupon_code));
		return $coupon_code;
	}
	static private function isCodeUsed($code) {
		awoHelper::loadResult('SELECT id FROM '._DB_PREFIX_.'awocoupon WHERE coupon_code="'.$code.'"');
		
		if(empty($id)) return false;
		return true;
	}
	static private function randomCode($length,$chars){
		$rand_id='';
		$char_length = strlen($chars);
		if($length>0) { for($i=1; $i<=$length; $i++) { $rand_id .= $chars[mt_rand(0, $char_length-1)]; } }
		return $rand_id;
	}	

}
