<?php
/**
 * @component AwoCoupon
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

if (!class_exists('awohelper'))  require _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

class AwoCouponCouponsModelFront
{
	var $_errors;


	public static function getData($id_customer, $pagination = false, $nb = 10, $page = 1)
	{
		$id_customer = (int)$id_customer;
		if (empty($id_customer)) return self::getGuestData();
		$total_count = 0;
		
		// Lets load the files if it doesn't already exist
		$final_rows = $cc_codes = $gc_codes = $im_codes = $activate_codes = array();

		$current_date = date('Y-m-d H:i:s');
		$sql = 'SELECT u.coupon_id,c.num_of_uses_total,c.num_of_uses_percustomer
				  FROM #__awocoupon_user u
				  JOIN #__awocoupon c ON c.id=u.coupon_id
				 WHERE u.user_id='.$id_customer.' AND c.published=1 
				   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"			AND c.expiration>="'.$current_date.'")
					   )
					   
										UNION
					 
				SELECT u.coupon_id,c.num_of_uses_total,c.num_of_uses_percustomer
				  FROM #__awocoupon_usergroup u
				  JOIN #__awocoupon c ON c.id=u.coupon_id
				  JOIN #__customer_group g ON g.id_group=u.shopper_group_id
				 WHERE g.id_customer='.$id_customer.' AND c.published=1 
				   AND ( ((c.startdate IS NULL OR c.startdate="")     AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="")   AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="")     AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"            AND c.expiration>="'.$current_date.'")
					   )
					   
					   '; 
		$rows = awohelper::loadObjectList($sql, 'coupon_id');
		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				if (!empty($row->num_of_uses_total) && !empty($row->num_of_uses_percustomer))
				{
					if (!empty($row->num_of_uses_percustomer))
					{
						$userlist = array();		
						$sql = 'SELECT COUNT(id) AS cnt FROM #__awocoupon_history WHERE coupon_id='.$row->coupon_id.' AND user_id='.$id_customer.' GROUP BY coupon_id,user_id';
						$cnt = awohelper::loadResult($sql);
						if (!empty($cnt) && $cnt >= $row->num_of_uses_percustomer) unset($rows[$row->coupon_id]);
					}
					if (!empty($row->num_of_uses_total))
					{
						$sql = 'SELECT COUNT(id) FROM #__awocoupon_history WHERE coupon_id='.$row->coupon_id.' GROUP BY coupon_id';
						$num = awohelper::loadResult($sql);
						if (!empty($num) && $num >= $row->num_of_uses_total) unset($rows[$row->coupon_id]);
					}
				}
				
			}
		
			if (!empty($rows)) $cc_codes = array_keys($rows);
		}
		
		$sql = '
				SELECT c.id
				  FROM #__awocoupon c
				  JOIN #__awocoupon_giftcert_order_code gc ON gc.coupon_id=c.id
				  JOIN #__awocoupon_giftcert_order g ON g.id=gc.giftcert_order_id
				 WHERE g.user_id='.(int)$id_customer.'
				   AND (gc.recipient_user_id IS NULL OR gc.recipient_user_id=0)
				   AND c.published=1
				   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"			AND c.expiration>="'.$current_date.'")
					   )
				 GROUP BY c.id
				 
						UNION
				 
				SELECT c.id
				  FROM #__awocoupon c
				  JOIN #__awocoupon_giftcert_order_code gc ON gc.coupon_id=c.id
				  JOIN #__awocoupon_giftcert_order g ON g.id=gc.giftcert_order_id
				 WHERE gc.recipient_user_id='.(int)$id_customer.'
				   AND c.published=1
				   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"			AND c.expiration>="'.$current_date.'")
					   )
				 GROUP BY c.id
				 
				 '; 
		$tmp = awohelper::loadObjectList($sql);
		$gc_codes = array();
		foreach ($tmp as $a) $gc_codes[] = $a->id;
		
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$params = new awoParams();
		if ((int)$params->get('giftcert_coupon_activate', 0) == 1)
		{
			$sql = 'SELECT c.id
					  FROM #__awocoupon c
					  JOIN #__awocoupon_giftcert_order_code gc ON gc.coupon_id=c.id
					  JOIN #__awocoupon_giftcert_order g ON g.id=gc.giftcert_order_id
					 WHERE g.user_id='.(int)$id_customer.'
					   AND c.published=-1
					   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
							 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
							 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
							 (c.startdate<="'.$current_date.'"			AND c.expiration>="'.$current_date.'")
						   )
					 GROUP BY c.id'; 
			$tmp = awohelper::loadObjectList($sql);
			foreach ($tmp as $a) $activate_codes[] = $a->id;
		}
		
		
		
		
		
		
		
		
		$current_date = date('Y-m-d H:i:s');
		$sql = 'SELECT i.coupon_id,c.num_of_uses_total,c.num_of_uses_percustomer
				  FROM #__awocoupon c
				  JOIN #__awocoupon_image i ON i.coupon_id=c.id
				 WHERE i.user_id='.$id_customer.' AND c.published=1 
				   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"			AND c.expiration>="'.$current_date.'")
					   )'; 
		$rows = awohelper::loadObjectList($sql, 'coupon_id');
		if (!empty($rows)) $im_codes = array_keys($rows);
		
		
		
		
		if (!empty($cc_codes) || !empty($gc_codes) || !empty($im_codes) || !empty($activate_codes))
		{
			$sql = 'SELECT c.id,c.published,c.function_type,c.coupon_code,c.coupon_value_type,c.coupon_value,c.startdate,c.expiration,i.filename,c.note,c.description
					  FROM #__awocoupon c
					  LEFT JOIN #__awocoupon_image i ON i.coupon_id=c.id
					 WHERE c.id IN ('.implode(',', array_merge($gc_codes, $cc_codes, $im_codes, $activate_codes)).') 
					 ORDER BY c.coupon_code';
			$final_rows = awohelper::loadObjectList($sql, 'id');
			
			
			// get gift cert balance
			$sql = 'SELECT c.*,
						 uv.id_customer AS user_id,uv.firstname,uv.lastname,
						 o.id_order AS order_id,UNIX_TIMESTAMP(o.date_add) AS cdate,
						 SUM(au.coupon_discount)+SUM(au.shipping_discount) AS coupon_value_used,
						 c.coupon_value-IFNULL(SUM(au.coupon_discount),0)-IFNULL(SUM(au.shipping_discount),0) AS balance,au.user_email
					 FROM #__awocoupon c
					 LEFT JOIN #__orders o ON o.id_order=c.order_id
					 LEFT JOIN #__address uv ON uv.id_address=o.id_address_invoice
					 LEFT JOIN #__awocoupon_history au ON au.coupon_id=c.id
					WHERE c.function_type="giftcert"
					  AND c.id IN ('.implode(',', array_merge($gc_codes, $cc_codes, $im_codes, $activate_codes)).')
					 GROUP BY c.id';
			$giftcards = awohelper::loadObjectList($sql, 'id');

			
			require_once _PS_CLASS_DIR_.'Tools.php';
			require_once _PS_CLASS_DIR_.'Currency.php';
			$currency_class = Currency::getDefaultCurrency();
			
			foreach ($final_rows as $i => $row)
			{
			
				$price = '';
				if (!empty($row->coupon_value))
					$price = $row->coupon_value_type == 'amount' 
									? Tools::displayPrice($row->coupon_value, $currency_class)
									: round($row->coupon_value).'%';
				$final_rows[$i]->str_coupon_value = $price;

				$final_rows[$i]->str_function_type = awoHelper::vars('function_type', $row->function_type == 'parent' ? 'coupon': $row->function_type);

			
			
			
				if (!empty($giftcards[$i]))
				{
					$final_rows[$i]->balance = $giftcards[$i]->balance;
					$final_rows[$i]->str_balance = Tools::displayPrice($giftcards[$i]->balance, $currency_class);
				}
				$full_filename = _PS_MODULE_DIR_.'awocoupon/media/customers/'.$id_customer.'/'.$row->filename.'.php';
				if (!file_exists($full_filename)) $final_rows[$i]->filename = '';
				else
				{
					$final_rows[$i]->filename = $full_filename;
					$final_rows[$i]->image_link = _PS_VERSION_ < '1.5'
								? AWO_URI.'/account.php?option=giftcert&display=ajax&coupon_code='.$row->coupon_code
								: Context::getContext()->link->getModuleLink('awocoupon', 'giftcert', array('coupon_code'=>$row->coupon_code));
				}
				
			}
								
		}

		return $final_rows;
	}
	
	
	public static function getGuestData($pagination = false, $nb = 10, $page = 1) {
		$group_id = (int) Context::getContext()->customer->id_default_group;
		if(empty($group_id)) return array();
		
		$current_date = date('Y-m-d H:i:s');
		
		$sql = 'SELECT c.id,c.published,c.function_type,c.coupon_code,c.coupon_value_type,c.coupon_value,c.startdate,c.expiration,i.filename,c.note,c.description
				  FROM #__awocoupon c
				  JOIN #__awocoupon_usergroup u ON u.coupon_id=c.id
				  LEFT JOIN #__awocoupon_image i ON i.coupon_id=c.id
				 WHERE u.shopper_group_id='.$group_id.' AND c.published=1 
				   AND ( ((c.startdate IS NULL OR c.startdate="")     AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="")   AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="")     AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"            AND c.expiration>="'.$current_date.'")
					   )
				 GROUP BY c.id
				 ORDER BY c.coupon_code';
		$final_rows = awohelper::loadObjectList($sql, 'id');
		
		require_once _PS_CLASS_DIR_.'Tools.php';
		require_once _PS_CLASS_DIR_.'Currency.php';
		$currency_class = Currency::getDefaultCurrency();
		
		foreach ($final_rows as $i => $row) {
			$price = '';
			if (!empty($row->coupon_value))
				$price = $row->coupon_value_type == 'amount' 
								? Tools::displayPrice($row->coupon_value, $currency_class)
								: round($row->coupon_value).'%';
			$final_rows[$i]->str_coupon_value = $price;

			$final_rows[$i]->str_function_type = awoHelper::vars('function_type', $row->function_type == 'parent' ? 'coupon': $row->function_type);

			$final_rows[$i]->filename = '';
			
		}
		
		return $final_rows;
	}
	
}
