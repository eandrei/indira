<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


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

class AwoCouponCouponHandler
{
	var $params = null;
	var $inparams = null;
	
	var $cart = null;
	var $ps_cart = null;
	var $ps_cartitems = null;
	var $shopshipping = null;
	var $_errors = null;

	public function __construct($id_cart, $cart = null)
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

		$this->id_cart = $id_cart;
		$this->ps_cart = !is_null($cart) ? $cart : new Cart($this->id_cart);
		$this->ps_cartitems = $this->ps_cart->getProducts();
		$this->coupon_session = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT * FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE id_cart = '.(int)$this->id_cart);

		$this->customer = new Customer((int)($this->ps_cart->id_customer));


		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		$this->params = new awoParams();
				
		$this->giftcert_discount_before_tax = $this->params->get('enable_giftcert_discount_before_tax', false);
		$this->coupon_discount_before_tax = $this->params->get('enable_coupon_discount_before_tax', false);

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.'awocoupon/backward_compatibility/backward.php');
	}
	
	

	public static function process_coupon_code($cart)
	{
		$instance = new AwoCouponCouponHandler($cart->id, $cart);
		//$instance->ps_cart = $cart;
		//$instance->ps_cartitems = $cart->getProducts();

		return $instance->validate_coupon_code();
	}
	public static function process_autocoupon($cart) {
	
		$instance = new AwoCouponCouponHandler($cart->id);
		$code = $instance->process_autocoupon_helper();
		if(empty($code)) return;
	}
	
	public static function run_delete_cart_code($id_cart, $id_cart_rule)
	{
		$instance = new AwoCouponCouponHandler($id_cart);
	
		return $instance->delete_cart_code($id_cart_rule);
	}

	
	
	public static function remove_coupon_code($id_cart, $id_order)
	{
		$instance = new AwoCouponCouponHandler($id_cart);
		$instance->id_order = $id_order;
	
		return $instance->cleanup_coupon_code();
	}

	protected function process_autocoupon_helper() {
		
		
		// if cart is the same, do not reproccess coupon
		$autosess = $this->get_coupon_auto();
		if(!empty($autosess) ){
			if( !empty($autosess->uniquecartstring) && $autosess->uniquecartstring==$this->getuniquecartstringauto()) {
				return $autosess->coupons[0]->coupon_code;
			}
		}
		
		$this->initialize_coupon_auto();
		
	
		// check coupons		
		$auto_coupon_code = array();
		$multiple_coupon_max_auto = (int)$this->params->get('multiple_coupon_max_auto', 100);
		$current_date = date('Y-m-d H:i:s');
		$sql = 'SELECT c.*,1 as isauto,0 as balance
				  FROM #__awocoupon c
				  JOIN #__awocoupon_auto a ON a.coupon_id=c.id
				 WHERE c.published=1 AND a.published=1
				   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
						 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
						 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
						 (c.startdate<="'.$current_date.'"		AND c.expiration>="'.$current_date.'")
					   )
				 ORDER BY a.ordering';
		$coupon_rows = awoHelper::loadObjectList($sql);
		if(empty($coupon_rows)) return false;

	
		// retreive cart items
		$this->define_cart_items();
		if (empty($this->cart->items)) return false;
		
		foreach($coupon_rows as $coupon_row) {
		
			if(empty($coupon_row)) {
			// no record, so coupon_code entered was not valid
				continue;
			} 

			// coupon returned
			$this->coupon_row = $coupon_row;


			if($coupon_row->function_type != 'parent') {
				
				$return = $this->validate_coupon_code_helper ( $coupon_row, false );
				if(!empty($return) && $return['redeemed']) {
					$auto_coupon_code[] = $coupon_row;
					if(count($auto_coupon_code)>=$multiple_coupon_max_auto) break;
				};
				continue;

			} 
			else {


				
				

				if(!empty($coupon_row->num_of_uses_total)) {
				// check to make sure it has not been used more than the limit
					$sql = 'SELECT COUNT(DISTINCT order_id) FROM #__awocoupon_history WHERE coupon_entered_id='.$coupon_row->id.' GROUP BY coupon_entered_id';
					$num = awoHelper::loadResult($sql);
					if(!empty($num) && $num>=$coupon_row->num_of_uses_total) {
					// total: already used max number of times
						continue;
					}
				}

				if(!empty($coupon_row->num_of_uses_percustomer)) {
				// check to make sure user has not used it more than the limit
					$num = 0;
					$user_id = (int)$this->ps_cart->id_customer;
					if(!empty($user_id)) {
						$sql = 'SELECT COUNT(DISTINCT order_id) FROM #__awocoupon_history WHERE coupon_entered_id='.$coupon_row->id.' AND user_id='.$user_id.' AND (user_email IS NULL OR user_email="") GROUP BY coupon_entered_id,user_id';
						$num = (int)awoHelper::loadResult($sql);
					}
					if(!$this->is_customer_num_uses($coupon_row->id,$coupon_row->num_of_uses_percustomer,$num, true)) {
					// per user: already used max number of times
						continue;
					}
				} 
				
				
				
				
				$sql = 'SELECT c.*,0 as balance
						  FROM #__awocoupon_asset1 ch 
						  JOIN #__awocoupon c ON c.id=ch.asset_id
						 WHERE ch.asset_type="coupon" AND ch.coupon_id='.$coupon_row->id.' 
						   AND c.published=1
						   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
								 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
								 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
								 (c.startdate<="'.$current_date.'"		AND c.expiration>="'.$current_date.'")
							   )
						 ORDER BY ch.order_by';
				$coupon_children_rows = awoHelper::loadObjectList($sql);
				if(empty($coupon_children_rows)) {
				// no record, so coupon_code entered was not valid
					continue;
				}

				// coupon returned
				
				if($coupon_row->parent_type == 'first' || $coupon_row->parent_type == 'lowest' || $coupon_row->parent_type=='highest' || $coupon_row->parent_type=='all') {
					foreach($coupon_children_rows as $child_row) {
						$return = $this->validate_coupon_code_helper ( $child_row, false );
						if(!empty($return) && $return['redeemed']) {
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$auto_coupon_code[] = $coupon_row;
							if(count($auto_coupon_code)>=$multiple_coupon_max_auto) break 2;
						};
					}
					continue;
				} 
				elseif($coupon_row->parent_type == 'allonly') {
					$found_valid_coupons = array();
					foreach($coupon_children_rows as $child_row) {
						$return = $this->validate_coupon_code_helper ( $child_row, false );
						if(!empty($return) && $return['redeemed']) {
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$found_valid_coupons[] = $return;
						}
					}
						
					if($coupon_row->parent_type == 'allonly' && count($found_valid_coupons)==count($coupon_children_rows)) {
						$auto_coupon_code[] = $coupon_row;
						if(count($auto_coupon_code)>=$multiple_coupon_max_auto) break;
					}
					continue;
				}
			}
		}
		
		$this->set_coupon_auto($auto_coupon_code);
		if(!empty($auto_coupon_code)) {
			$this->validate_coupon_code(true);
			return $auto_coupon_code[0]->coupon_code;
		}
		
		return;
		
	}
	protected function initialize_coupon_auto() { if(!isset($_SESSION)) session_start(); $_SESSION['awocouponCouponAuto'] = 0; }
	protected function set_coupon_auto($coupon_rows) {
		if(empty($coupon_rows)) $this->initialize_coupon_auto();
		else {
			$master_list = new stdClass();
			$master_list->uniquecartstring = $this->getuniquecartstringauto();
			$master_list->coupons = $coupon_rows;
			
			if(!isset($_SESSION)) session_start();
			$_SESSION['awocouponCouponAuto'] = serialize($master_list);			
		}
	}
	protected function get_coupon_auto() {
		if(!isset($_SESSION)) session_start();
		$coupon_row = isset($_SESSION['awocouponCouponAuto']) ? $_SESSION['awocouponCouponAuto'] : '';
		
		if(!empty($coupon_row)) {
			$coupon_row = unserialize($coupon_row);
			if(!empty($coupon_row->coupons)) return $coupon_row;
		}
		return '';
	}
	protected function getuniquecartstringauto() {
		$user_id = (int)$this->ps_cart->id_customer;
		$string = (float)$this->ps_cart->getOrderTotal(true, Cart::ONLY_PRODUCTS).'|'.(float)$this->ps_cart->getOrderTotal(true, Cart::ONLY_SHIPPING).'|'.$user_id;
		foreach ($this->ps_cartitems as $k => $r) $string .= '|'.$k.'|'.$r['id_product'].'|'.$r['quantity'];
		return $string.'|ship|'.$this->ps_cart->id_carrier;
	}
	public static function session_set($key, $value)
	{
		$context = Context::getContext();
		$context->cookie->__set($key, $value);

	}
	public static function session_get($key, $default_value = null)
	{
		$context = Context::getContext();
		if (isset($context->cookie->{$key})) return $context->cookie->{$key};

		return $default_value;

	}



	public function validate_coupon_code($is_auto = false)
	{
	// function to process a coupon_code entered by a user

		$submitted_coupon_code = trim($this->coupon_session['new_ids']);
		
		// if cart is the same, do not reproccess coupon
		$coupon_awo_entered_coupon_ids = array();
		$coupon_session = !empty($this->coupon_session['coupon_data']) ? $this->coupon_session['coupon_data'] : '';
		if (!empty($coupon_session))
		{
			$coupon_session = unserialize($coupon_session);
			//printrx($coupon_session);
			if ((empty($submitted_coupon_code) || strpos(';'.$coupon_session['coupon_code'].';', ';'.$submitted_coupon_code.';') !== false) && $coupon_session['uniquecartstring'] == $this->getuniquecartstring())
				return $coupon_session;
			
			if (!empty($coupon_session['processed_coupons']))
			{
				foreach ($coupon_session['processed_coupons'] as $k => $r)
				{
					$code = $r['coupon_code'];
					$coupon_awo_entered_coupon_ids[] = $code;
				}
			}
			
		}

		$iscaseSensitive = awoHelper::getCaseSensitive();

		$coupon_awo_entered_coupon_ids
			= $multiple_coupons['auto']
			= $multiple_coupons['coupon']
			= $multiple_coupons['giftcert']
			= array();
		$coupon_session = !empty($this->coupon_session['coupon_data']) ? $this->coupon_session['coupon_data'] : '';
		if(!empty($coupon_session) ) {
			$coupon_session = unserialize($coupon_session);
			if(!empty($coupon_session['processed_coupons'])) {
				foreach($coupon_session['processed_coupons'] as $k=>$r) {
					if($r['isauto']) continue;
					$coupon_awo_entered_coupon_ids[] = $r['coupon_code'];
					$multiple_coupons[$r['isgift'] ? 'giftcert' : 'coupon'][] = $r['coupon_code'];

				}
			}
		}
		if(!empty($submitted_coupon_code)) {
			$submited_multiple_coupons = explode(';',$submitted_coupon_code);
			foreach($submited_multiple_coupons as $___s_coupon) {
				$___s_coupon = trim($___s_coupon);
				$coupon_awo_entered_coupon_ids[] = pSQL($___s_coupon);
			}
		}
		$coupon_awo_entered_coupon_ids = $iscaseSensitive ? array_unique($coupon_awo_entered_coupon_ids) : $this->array_iunique($coupon_awo_entered_coupon_ids);
		if(!$is_auto && empty($coupon_awo_entered_coupon_ids)) return;
		
		
		$this->initialize_coupon();
		$auto_codes = @$this->get_coupon_auto()->coupons;
				
		
		
		if(empty($coupon_awo_entered_coupon_ids) && empty($auto_codes)) return $this->return_false('errNoRecord');
		if(empty($auto_codes)) $auto_codes = array();
		if(!empty($auto_codes)) {
			$reverse_auto_codes = array_reverse($auto_codes);
			foreach($reverse_auto_codes as $auto_code) {
				if($this->is_coupon_in_array($iscaseSensitive,$auto_code->coupon_code,$multiple_coupons['coupon'])) {
					if(($key = array_search($auto_code->coupon_code, $multiple_coupons['coupon'])) !== false)
						unset($multiple_coupons['coupon'][$key]);
				}
				if($this->is_coupon_in_array($iscaseSensitive,$auto_code->coupon_code,$multiple_coupons['giftcert'])) {
					if(($key = array_search($auto_code->coupon_code, $multiple_coupons['giftcert'])) !== false)
						unset($multiple_coupons['giftcert'][$key]);
				}
				if($this->is_coupon_in_array($iscaseSensitive,pSQL($auto_code->coupon_code),$coupon_awo_entered_coupon_ids)) {
					if(($key = array_search(pSQL($auto_code->coupon_code), $coupon_awo_entered_coupon_ids)) !== false)
						unset($coupon_awo_entered_coupon_ids[$key]);
				}
				
				$multiple_coupons['auto'][] = $auto_code->coupon_code;
				$coupon_awo_entered_coupon_ids[] = pSQL($auto_code->coupon_code);
			}
		}
		$coupon_awo_entered_coupon_ids = $iscaseSensitive ? array_unique($coupon_awo_entered_coupon_ids) : $this->array_iunique($coupon_awo_entered_coupon_ids);
		
		
		
		if($this->params->get('enable_multiple_coupon', 0)==0) {
			// remove all auto codes
			$last_auto_code = '';
			foreach($auto_codes as $auto_code) {
				if($this->is_coupon_in_array($iscaseSensitive,pSQL($auto_code->coupon_code),$coupon_awo_entered_coupon_ids)) {
					if(($key = array_search(pSQL($auto_code->coupon_code), $coupon_awo_entered_coupon_ids)) !== false) {
						unset($coupon_awo_entered_coupon_ids[$key]);
						$last_auto_code = $auto_code->coupon_code;
					}
				}
			}

			// get the last item in the coupon array
			$coupon_awo_entered_coupon_ids = array(array_pop($coupon_awo_entered_coupon_ids));
			
			// add the last auto code back
			$coupon_awo_entered_coupon_ids[] = $last_auto_code;
		}
		else {
		// remove coupons is maximums are set
		
		
			if(!empty($coupon_awo_entered_coupon_ids)) {
				
				$multiple_coupon_max_auto = (int)$this->params->get('multiple_coupon_max_auto', 0);
				$multiple_coupon_max_coupon = (int)$this->params->get('multiple_coupon_max_coupon', 0);
				$multiple_coupon_max_giftcert = (int)$this->params->get('multiple_coupon_max_giftcert', 0);
				if($multiple_coupon_max_auto>0 || $multiple_coupon_max_coupon>0 || $multiple_coupon_max_giftcert>0) {
					if(!empty($submitted_coupon_code)) {
						$submitted_not_in_coupons = $this->array_intersect_diff('diff',$iscaseSensitive,$submited_multiple_coupons,array_merge($multiple_coupons['coupon'],$multiple_coupons['giftcert']));
						if(!empty($submitted_not_in_coupons)) {
						// now add submitted coupon(s) not on any list to either automatic, giftcert, or coupon array
							foreach($submitted_not_in_coupons as $current_coupon_not_in_coupons) {
								$check_if_auto = false;
								foreach($auto_codes as $auto_code) {
									if(
										($iscaseSensitive && trim($auto_code->coupon_code)==$current_coupon_not_in_coupons)
									||	(!$iscaseSensitive && strtolower(trim($auto_code->coupon_code))==strtolower($current_coupon_not_in_coupons))
									) { 
										$check_if_auto = true;
										$multiple_coupons['auto'][] = $auto_code->coupon_code;
										break;
									}
								}
								if(!$check_if_auto) {
									$sql = 'SELECT function_type FROM #__awocoupon WHERE coupon_code="'.pSQL($current_coupon_not_in_coupons).'"';
									$test = awoHelper::loadResult($sql);
									if(!empty($test)) $multiple_coupons[$test=='giftcert' ? 'giftcert' : 'coupon'][] = $current_coupon_not_in_coupons;
								}
							}
						}
					}
					
					if($multiple_coupon_max_auto>0 && count($multiple_coupons['auto'])>1 ) {
						$multiple_coupons['auto'] = $iscaseSensitive ? array_unique($multiple_coupons['auto']) : $this->array_iunique($multiple_coupons['auto']);
						if(count($multiple_coupons['auto'])>$multiple_coupon_max_auto) {
							$removecoupons = array_slice($multiple_coupons['auto'],0,count($multiple_coupons['auto'])-$multiple_coupon_max_auto);
							if(!empty($removecoupons)) {
								foreach($removecoupons as $r) if(($key = array_search($r, $coupon_awo_entered_coupon_ids)) !== false) unset($coupon_awo_entered_coupon_ids[$key]);
							}
						}
					}
					if($multiple_coupon_max_coupon>0 && count($multiple_coupons['coupon'])>1 ) {
						$multiple_coupons['coupon'] = $iscaseSensitive ? array_unique($multiple_coupons['coupon']) : $this->array_iunique($multiple_coupons['coupon']);
						if(count($multiple_coupons['coupon'])>$multiple_coupon_max_coupon) {
							$removecoupons = array_slice($multiple_coupons['coupon'],0,count($multiple_coupons['coupon'])-$multiple_coupon_max_coupon);
							if(!empty($removecoupons)) {
								foreach($removecoupons as $r) if(($key = array_search($r, $coupon_awo_entered_coupon_ids)) !== false) unset($coupon_awo_entered_coupon_ids[$key]);
							}
						}
					}
					if($multiple_coupon_max_giftcert>0 && count($multiple_coupons['giftcert'])>1 ) {
						$multiple_coupons['giftcert'] = $iscaseSensitive ? array_unique($multiple_coupons['giftcert']) : $this->array_iunique($multiple_coupons['giftcert']);
						if(count($multiple_coupons['giftcert'])>$multiple_coupon_max_giftcert) {
							$removecoupons = array_slice($multiple_coupons['giftcert'],0,count($multiple_coupons['giftcert'])-$multiple_coupon_max_giftcert);
							if(!empty($removecoupons)) {
								foreach($removecoupons as $r) if(($key = array_search($r, $coupon_awo_entered_coupon_ids)) !== false) unset($coupon_awo_entered_coupon_ids[$key]);
							}
						}
					}
				}

				$multiple_coupon_max = (int)$this->params->get('multiple_coupon_max', 0);
				if($multiple_coupon_max>0 && count($coupon_awo_entered_coupon_ids)>$multiple_coupon_max ) {
					$coupon_awo_entered_coupon_ids = array_slice(
														$coupon_awo_entered_coupon_ids,
														count($coupon_awo_entered_coupon_ids)-$multiple_coupon_max
													);
				}
			}

			
		}
		
		

		if((int)$this->params->get('multiple_coupon_reorder_giftcert_last', 0)==1) {
		// reorder giftcerts last
			$sql = 'SELECT id,coupon_code,function_type FROM #__awocoupon WHERE coupon_code IN ("'.implode('","', $coupon_awo_entered_coupon_ids).'")';
			$entered_coupons_properties = awohelper::loadObjectList($sql,'coupon_code');
			$gcert = array();
			foreach($coupon_awo_entered_coupon_ids as $current_key=>$current_coupon) {
				if(!isset($entered_coupons_properties[$current_coupon])) continue;
				if($entered_coupons_properties[$current_coupon]->function_type!='giftcert') continue;
                                
				$gcert[] = $current_coupon;
				unset($coupon_awo_entered_coupon_ids[$current_key]);
			}
			$coupon_awo_entered_coupon_ids= array_merge($coupon_awo_entered_coupon_ids,$gcert);
		}

		
//printr($coupon_awo_entered_coupon_ids);
		// check coupons
		$master_output = $coupon_rows = array();
		$current_date = date('Y-m-d H:i:s');
		$coupon_codes = implode('","', $coupon_awo_entered_coupon_ids);

		if(!empty($coupon_codes)) {
			$sql = 'SELECT *
					  FROM '._DB_PREFIX_.'awocoupon 
					 WHERE published=1 
					   AND ( ((startdate IS NULL OR startdate="") 	AND (expiration IS NULL OR expiration="")) OR
							 ((expiration IS NULL OR expiration="") AND startdate<="'.$current_date.'") OR
							 ((startdate IS NULL OR startdate="") 	AND expiration>="'.$current_date.'") OR
							 (startdate<="'.$current_date.'"		AND expiration>="'.$current_date.'")
						   )
					   AND coupon_code IN ("'.$coupon_codes.'")
					  ORDER BY FIELD(coupon_code, "'.$coupon_codes.'")';
			$coupon_rows = awohelper::loadObjectList($sql,'id');
		}
		
		if(!empty($auto_codes))  { 
			$valid_auto_codes = array();
			foreach($auto_codes as $auto_code) {
				if(isset($coupon_rows[$auto_code->id])) {
					$valid_auto_codes[] = $auto_code;
					unset($coupon_rows[$auto_code->id]);
				}
			}
			$valid_auto_codes = array_reverse($valid_auto_codes);
			foreach($valid_auto_codes as $auto_code) $coupon_rows = array($auto_code->id => $auto_code) + $coupon_rows;  // need to preserve coupon_id as the key
		}
		
		if(!empty($submitted_coupon_code)) {
			$is_found = false;
			foreach($submited_multiple_coupons as $_current_submitted_coupon) {
				foreach($coupon_rows as $tmp) {
					if(
						($iscaseSensitive && trim($tmp->coupon_code)==$_current_submitted_coupon)
						||	(!$iscaseSensitive && strtolower(trim($tmp->coupon_code))==strtolower($_current_submitted_coupon))
				
					) {
						$is_found = true; 
						break 2;
					}
				}
			}
			if(!$is_found) {
				$this->coupon_row = new stdclass;
				$this->coupon_row->id = -1;
				$this->coupon_row->coupon_code = $submitted_coupon_code;
				$this->coupon_row->function_type = 'coupon';
				$this->coupon_row->isauto = in_array($submitted_coupon_code, $multiple_coupons['auto']) ? true : false;
				$this->return_false('errNoRecord');
			}
		}
		
				
		if (empty($coupon_rows)) return $this->return_false('errNoRecord');
	
		{ // get tags
			$sql = 'SELECT coupon_id,tag FROM #__awocoupon_tag WHERE coupon_id IN ('.implode(',',array_keys($coupon_rows)).') AND tag LIKE "{_%}"';
			$tmp = awoHelper::loadObjectList($sql);
			foreach($tmp as $tmp_item) {
				preg_match('/{(.*):(.*)}/i',$tmp_item->tag,$match);
				if(!empty($match[1])) $coupon_rows[$tmp_item->coupon_id]->tags[$match[1]] = $match[2];
				else {
					$key = trim($tmp_item->tag,'{}');
					if(!empty($key)) $coupon_rows[$tmp_item->coupon_id]->tags[$key] = 1;
				}
			}
		}
		/*foreach($coupon_rows as $key=>$coupon_row) {
			if(empty($coupon_row->note)) continue;
			if(strpos($coupon_row->note,'{exclusive}')===false) continue;
			
			if(empty($coupon_rows[$key]->tags)) $coupon_rows[$key]->tags = array();
			$coupon_rows[$key]->tags['exclusive'] = 1;
		}*/
		
		{ // check for coupon exclusivity
			foreach($coupon_rows as $coupon_row) {
				if(!empty($coupon_row->tags['exclusive']) && $coupon_row->tags['exclusive']==1 && $coupon_row->published==1) {
				// drop all other coupons and only use this one
					//if(count($coupon_rows)>1 && $submitted_coupon_code!=$coupon_row->coupon_code) { $this->_errors = 'The current code cannot be used with other codes';}
					$coupon_rows = array();
					$coupon_rows[$coupon_row->id] = $coupon_row;
					break;
				}
			}
		}


		// retreive cart items
		$this->define_cart_items();
		if (empty($this->cart->items))
		{
			$this->initialize_coupon();
			return false;
		}
		
		foreach ($coupon_rows as $coupon_row)
		{
		
			if (empty($coupon_row)) {
			// no record, so coupon_code entered was not valid
				continue;
			} 
			if (!empty($master_output[$coupon_row->id])) continue;

			// coupon returned
			//$coupon_row->params = json_decode($coupon_row->params);
			if (!empty($coupon_row->params) && !is_object($coupon_row->params)) $coupon_row->params = json_decode($coupon_row->params);
			$this->coupon_row = $coupon_row;
		

	

			if ($coupon_row->function_type != 'parent') {
				$return = $this->validate_coupon_code_helper ($coupon_row, true);
				if (!empty($return) && $return['redeemed']) {
					$master_output[$coupon_row->id] = array($coupon_row,$return);
					continue;
				};
				continue;

			} 
			else
			{
				if (awoHelper::is_multistore())
				{
					$id_shop = '';
					$coupon_row->shoplist = array();
					$tmp = awoHelper::loadObjectList('SELECT id_shop FROM '._DB_PREFIX_.'awocoupon_shop WHERE coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->shoplist[$tmp2->id_shop] = $tmp2->id_shop;
					
					if (!empty($coupon_row->shoplist) && !isset($coupon_row->shoplist[$this->ps_cart->id_shop]))
					{
					// this shop does not habe access to this coupon
						return $this->return_false('errShopPermission');
					}
				
				}

				if (!empty($coupon_row->num_of_uses_total))
				{
				// number of use check for parent coupon
					$sql = 'SELECT COUNT(DISTINCT order_id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_entered_id='.$coupon_row->id.' GROUP BY coupon_entered_id';
					$num = awoHelper::loadResult($sql);
					if (!empty($num) && $num >= $coupon_row->num_of_uses_total)
					{
					// total: already used max number of times
						$this->return_false('errTotalMaxUse');
						continue;
					}
				}
					
				if (!empty($coupon_row->num_of_uses_percustomer))
				{
					// check to make sure user has not used it more than the limit
					$user_id = (int)$this->ps_cart->id_customer;
					$num = 0;
					if (!empty($user_id))
					{
						$sql = 'SELECT COUNT(DISTINCT order_id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_entered_id='.$coupon_row->id.' AND user_id='.$user_id.' AND (user_email IS NULL OR user_email="") GROUP BY coupon_entered_id,user_id';
						$num = (int)awoHelper::loadResult($sql);
					}
					if (!$this->is_customer_num_uses($coupon_row->id, $coupon_row->num_of_uses_percustomer, $num, true))
					{
					// per user: already used max number of times
						$this->return_false('errUserMaxUse');
						continue;
					}
				}


				// country state check
				$r_err = $this->couponvalidate_country($coupon_row);
				if (!empty($r_err))
				{
					$this->return_false($r_err);
					continue;
				}



				$sql = 'SELECT c.*
						  FROM '._DB_PREFIX_.'awocoupon_asset1 ch 
						  JOIN '._DB_PREFIX_.'awocoupon c ON c.id=ch.asset_id
						 WHERE ch.asset_type="coupon" AND ch.coupon_id='.$coupon_row->id.' 
						   AND c.published=1 
						   AND ( ((c.startdate IS NULL OR c.startdate="") 	AND (c.expiration IS NULL OR c.expiration="")) OR
								 ((c.expiration IS NULL OR c.expiration="") AND c.startdate<="'.$current_date.'") OR
								 ((c.startdate IS NULL OR c.startdate="") 	AND c.expiration>="'.$current_date.'") OR
								 (c.startdate<="'.$current_date.'"		AND c.expiration>="'.$current_date.'")
							   )
						 ORDER BY ch.order_by';
				$coupon_children_rows = awoHelper::loadObjectList($sql);
				if (empty($coupon_children_rows))
				{
				// no record, so coupon_code entered was not valid
					continue;
				}

				// coupon returned
				
				if ($coupon_row->params->process_type == 'first')
				{
					foreach ($coupon_children_rows as $child_row)
					{
						if (!empty($master_output[$child_row->id])) continue;
						$return = $this->validate_coupon_code_helper ($child_row, true);
						if (!empty($return) && $return['redeemed'])
						{
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$return['coupon_entered_id'] = $coupon_row->id;
							$return['coupon_code'] = $coupon_row->coupon_code;
							$master_output[$coupon_row->id] = array($coupon_row,$return);
							break;
						};
					}
					continue;
				} 
				elseif ($coupon_row->params->process_type == 'lowest' || $coupon_row->params->process_type == 'highest')
				{
					$found_valid_coupons = array();
					foreach ($coupon_children_rows as $child_row)
					{
						if (!empty($master_output[$child_row->id])) continue;
						$return = $this->validate_coupon_code_helper ($child_row, true);
						if (!empty($return) && $return['redeemed'])
						{
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$found_valid_coupons[] = $return;
						};
					}
					if (!empty($found_valid_coupons))
					{
						$valid_id = -1;
						$valid_value = 0;
						foreach ($found_valid_coupons as $k => $valid_coupon)
						{
							if ($valid_id == -1)
							{
								$valid_id = $k;
								$valid_value = $valid_coupon['product_discount'] + $valid_coupon['shipping_discount'];
							}
							if ($coupon_row->params->process_type == 'lowest')
							{
								if ($valid_value > ($valid_coupon['product_discount'] + $valid_coupon['shipping_discount']))
								{
									$valid_id = $k;
									$valid_value = $valid_coupon['product_discount'] + $valid_coupon['shipping_discount'];
								}
							}
							elseif ($coupon_row->params->process_type == 'highest')
							{
								if ($valid_value < ($valid_coupon['product_discount'] + $valid_coupon['shipping_discount']))
								{
									$valid_id = $k;
									$valid_value = $valid_coupon['product_discount'] + $valid_coupon['shipping_discount'];
								}
							}
						}
						if (!empty($found_valid_coupons[$valid_id]))
						{
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$found_valid_coupons[$valid_id]['coupon_entered_id'] = $coupon_row->id;
							$found_valid_coupons[$valid_id]['coupon_code'] = $coupon_row->coupon_code;
							$master_output[$coupon_row->id] = array($coupon_row,$found_valid_coupons[$valid_id]);
							continue;
						}
					}
					continue;
				}
				elseif ($coupon_row->params->process_type == 'all' || $coupon_row->params->process_type == 'allonly')
				{
					$found_valid_coupons = array();
					foreach ($coupon_children_rows as $child_row)
					{
						if (!empty($master_output[$child_row->id])) continue;
						$return = $this->validate_coupon_code_helper ($child_row, true);
						if (!empty($return) && $return['redeemed'])
						{
						// mark this order as having used a coupon so people cant go and use coupons over and over 
							$found_valid_coupons[] = $return;
						}
					}
						
					if ($coupon_row->params->process_type == 'allonly' && count($found_valid_coupons) != count($coupon_children_rows))
					{
						// all do not match, coupon not found
						//$this->initialize_coupon();
						$this->return_false('errNoRecord');
						continue;
					}

					$return = array(	'coupon_id'=>$coupon_row->id,
										'coupon_code'=>$coupon_row->coupon_code,
										'product_discount'=>0,
										'product_discount_notax'=>0,
										'product_discount_tax'=>0,
										'shipping_discount'=>0,
										'shipping_discount_notax'=>0,
										'shipping_discount_tax'=>0,
										'giftwrap_discount'=>0,
										'giftwrap_discount_notax'=>0,
										'giftwrap_discount_tax'=>0,
										'percentage_used'=>0,
										'usedproducts'=>'',
									); 
					$usedproducts = $processed_coupons = array();
					foreach ($found_valid_coupons as $row)
					{
						if (!empty($row['force_add']) || !empty($row['product_discount']) || !empty($row['shipping_discount']))
						{
							if (!empty($row['force_add'])) $return['force_add'] = 1;
							$return['product_discount'] += $row['product_discount'];
							$return['product_discount_notax'] += $row['product_discount_notax'];
							$return['product_discount_tax'] += $row['product_discount_tax'];
							$return['shipping_discount'] += $row['shipping_discount'];
							$return['shipping_discount_notax'] += $row['shipping_discount_notax'];
							$return['shipping_discount_tax'] += $row['shipping_discount_tax'];
							$return['giftwrap_discount'] += $row['giftwrap_discount'];
							$return['giftwrap_discount_notax'] += $row['giftwrap_discount_notax'];
							$return['giftwrap_discount_tax'] += $row['giftwrap_discount_tax'];
							//$tmp = !empty($row['usedproducts']) ? array_fill_keys(explode(',',$row['usedproducts']),1) : array();
							$tmp = array();
							$tmpA = !empty($row['usedproducts']) ? explode(',', $row['usedproducts']) : array();
							foreach ($tmpA as $t) $tmp[$t] = 1;
							$usedproducts = $usedproducts + $tmp;
							
							$isauto = false;
							if(!empty($auto_codes))  { 
								foreach($auto_codes as $auto_code) {
									if($auto_code->id == $coupon_row->id) {
										$isauto = true;
										break;
									}
								}
							}
							
							$processed_coupons[$row['coupon_id']] = array(
								'coupon_entered_id'=>$coupon_row->id,
								'coupon_code'=>$coupon_row->coupon_code,
								'product_discount'=>$row['product_discount'],
								'product_discount_notax'=>$row['product_discount_notax'],
								'product_discount_tax'=>$row['product_discount_tax'],
								'shipping_discount'=>$row['shipping_discount'],
								'shipping_discount_notax'=>$row['shipping_discount_notax'],
								'shipping_discount_tax'=>$row['shipping_discount_tax'],
								'giftwrap_discount'=>$row['giftwrap_discount'],
								'giftwrap_discount_notax'=>$row['giftwrap_discount_notax'],
								'giftwrap_discount_tax'=>$row['giftwrap_discount_tax'],
								'percentage_used'=>$row['percentage_used'],
								'usedproducts'=>$row['usedproducts'],
								'isauto'=> $isauto,
								'isgift'=>false,
								'ischild'=>true,
								'note'=>$coupon_row->note,
							);
						}
						# set coupon display name
						if (!empty($coupon_row->note))
						{
							$match = array();
							preg_match('/{customer_display_text:(.*)?}/i', $coupon_row->note, $match);
							if (!empty($match[1])) $processed_coupons[$row['coupon_id']]['coupon_code_display'] = $match[1];
						}
					}
					
					if (!empty($return['force_add']) || !empty($return['product_discount']) || !empty($return['shipping_discount']))
					{
					// mark this order as having used a coupon so people cant go and use coupons over and over 
						$return['usedproducts'] = implode(',', array_keys($usedproducts));
						$master_output[$coupon_row->id] = array($coupon_row,$return,$processed_coupons);
						continue;
					}
					continue;
											
				}
			}
		}
		
		
		
		if (($coupon_session = $this->finalize_coupon($master_output)) !== false) return $coupon_session;
		
		
		$this->coupon_row = null;
		if (empty($this->_errors)) $this->return_false('errNoRecord');
		$this->initialize_coupon();
		return false;
	}
	
	public function validate_coupon_code_helper($coupon_row, $track_product_price = false)
	{
		$user_id = (int)$this->ps_cart->id_customer;

		$_SESSION_shipping = $_SESSION_shipping_tax = $_SESSION_shipping_notax = 0;
		$_SESSION_product = $_SESSION_product_notax = $_SESSION_product_tax = 0;
		$_SESSION_giftwrap = $_SESSION_giftwrap_tax = $_SESSION_giftwrap_notax = 0;
		$percentage_used = 0;
		$usedproductids = array();
		
//printrx($coupon_row);
		
		if (empty($coupon_row)) return;

		$coupon_row->params = !empty($coupon_row->params) ? (is_string($coupon_row->params) ? json_decode($coupon_row->params) : $coupon_row->params) : new stdclass;
		$coupon_row->cart_items = $this->cart->items;
		$coupon_row->cart_items_def = $this->cart->items_def;
		$coupon_row->cart_shipping = $this->cart->shipping;
		
		$is_discount_before_tax = $coupon_row->function_type == 'giftcert' ? $this->giftcert_discount_before_tax : $this->coupon_discount_before_tax;
		$coupon_row->is_discount_before_tax = $is_discount_before_tax;
		
		
		preg_match('/{instock_only}/i', $coupon_row->note, $match);
		if(!empty($match[0]))
		{
		// remove instock products
			foreach ($coupon_row->cart_items as $k => $tmp) {
				$quantity_in_stock = version_compare(_PS_VERSION_, '1.5', '>') ? $tmp['product']['quantity_available'] : $tmp['product']['stock_quantity'];
				if ($quantity_in_stock<=0) $coupon_row->cart_items[$k]['_exclude_from_discount'] = 1;
			}
			if (empty($coupon_row->cart_items))
			{
				// all products in cart are on special
				return $this->return_false('errDiscountedExclude');
			}
					
		}
		
		if (awoHelper::is_multistore())
		{
			$id_shop = '';
			$coupon_row->shoplist = array();
			$tmp = awoHelper::loadObjectList('SELECT id_shop FROM '._DB_PREFIX_.'awocoupon_shop WHERE coupon_id='.$coupon_row->id);
			foreach ($tmp as $tmp2) $coupon_row->shoplist[$tmp2->id_shop] = $tmp2->id_shop;
			
			if (!empty($coupon_row->shoplist) && !isset($coupon_row->shoplist[$this->ps_cart->id_shop]))
			{
			// this shop does not habe access to this coupon
				return $this->return_false('errShopPermission');
			}
		
		}
		
		$coupon_row->customer = new stdClass();
		$coupon_row->userlist 
			= $coupon_row->usergrouplist 
			= $coupon_row->productlist 
			= $coupon_row->categorylist 
			= $coupon_row->manufacturerlist 
			= $coupon_row->vendorlist 
			= $coupon_row->shippinglist
			= $coupon_row->countrylist
			= $coupon_row->countrystatelist
			= array();
		
		// ----------------------------------------------------
		// verify this coupon can be used in this circumstance
		// ----------------------------------------------------
		if ($coupon_row->function_type == 'giftcert')
		{
		// check value to make sure the full value of the gift cert has not been used
			$sql = 'SELECT SUM(coupon_discount+shipping_discount) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id='.$coupon_row->id.' GROUP BY coupon_id';
			$gift_cert_used_value = (float)awoHelper::loadResult($sql);
			if (!empty($gift_cert_used_value) && $gift_cert_used_value >= $coupon_row->coupon_value)
			{
			// total value of gift cert is used up
				return $this->return_false('errGiftUsed');
			}
			
			
			// check for giftcert products
			if (!empty($coupon_row->params->exclude_giftcert))
			{
				$ids = '';
				foreach ($coupon_row->cart_items as $tmp) $ids .= $tmp['product_id'].',';
				if (!empty($ids))
				{
					$sql = 'SELECT product_id FROM '._DB_PREFIX_.'awocoupon_giftcert_product WHERE product_id IN ('.substr($ids, 0, -1).')';
					$test_list = awoHelper::loadObjectList($sql, 'product_id');

					foreach ($coupon_row->cart_items as $k => $tmp)
						if (isset($test_list[$tmp['product_id']])) unset($coupon_row->cart_items[$k]);
				}
			}
			
			
			
			// check products to verify on asset list
			$asset1list = array();
			if (!empty($coupon_row->params->asset1_type))
			{
				$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="'.$coupon_row->params->asset1_type.'" AND coupon_id='.(int)$coupon_row->id);
				foreach ($tmp as $tmp2) $asset1list[$tmp2->asset_id] = $tmp2->asset_id;
			}
			if (!empty($asset1list))
			{
				$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
				if ($coupon_row->params->asset1_type == 'product');
				elseif ($coupon_row->params->asset1_type == 'category')
				{
					$sql = 'SELECT id_category AS category_id,id_product AS product_id FROM '._DB_PREFIX_.'category_product WHERE id_product IN ('.$ids_to_check.')';
					$tmp = awoHelper::loadObjectList($sql);
					foreach ($tmp as $tmp2)
					{
						if (isset($asset1list[$tmp2->category_id]))
							$coupon_row->cart_items_def[$tmp2->product_id]['category'] = $tmp2->category_id;
					}
				}
				elseif ($coupon_row->params->asset1_type == 'manufacturer')
				{
					$sql = 'SELECT id_manufacturer AS manufacturer_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
					$tmp = awoHelper::loadObjectList($sql);
					foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['manufacturer'] = $tmp2->manufacturer_id;
				}
				elseif ($coupon_row->params->asset1_type == 'vendor')
				{
					$sql = 'SELECT id_supplier AS vendor_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
					$tmp = awoHelper::loadObjectList($sql);
					foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['vendor'] = $tmp2->vendor_id;
				}
				
				if ($coupon_row->params->asset1_mode == 'include')
				{
					foreach ($coupon_row->cart_items as $k => $row)
						if (!isset($asset1list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset1_type]])) unset($coupon_row->cart_items[$k]);
				}
				elseif ($coupon_row->params->asset1_mode == 'exclude')
				{
					foreach ($coupon_row->cart_items as $k => $row)
						if (isset($asset1list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset1_type]])) unset($coupon_row->cart_items[$k]);
				}

			}
			

			// check shipping
			$asset2list = array();
			if (!empty($coupon_row->params->asset2_type) && $coupon_row->params->asset2_type == 'shipping')
			{
				$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset2 WHERE asset_type="'.$coupon_row->params->asset2_type.'" AND coupon_id='.(int)$coupon_row->id);
				foreach ($tmp as $tmp2) $asset2list[$tmp2->asset_id] = $tmp2->asset_id;
			}
			$total_shipping_notax = $total_shipping = 0;
			if(empty($asset1list) || (!empty($asset1list) && !empty($asset2list))) {
				$shipping_property = $coupon_row->cart_shipping;
				if (!empty($shipping_property->total))
				{
					$total_shipping_notax = $shipping_property->total_notax;
					$total_shipping = $shipping_property->total;
					if (!empty($asset2list))
					{
						if ($coupon_row->params->asset2_mode == 'include' && !isset($asset2list[$shipping_property->shipping_id])) $total_shipping_notax = $total_shipping = 0;
						elseif ($coupon_row->params->asset2_mode == 'exclude' && isset($asset2list[$shipping_property->shipping_id])) $total_shipping_notax = $total_shipping = 0;
					}
				}
			}
			$coupon_row->giftcert_shipping = $total_shipping;
			$coupon_row->giftcert_shipping_notax = $total_shipping_notax;
		
			
			
		}
		elseif ($coupon_row->function_type != 'giftcert')
		{

			if (1 == 1)
			{ // particulars
				$shopper_group_id = 0;


				// verify total is up to the minimum value for the coupon
				if (!empty($coupon_row->min_value) && round($this->product_total, 2) < $coupon_row->min_value)
					return $this->return_false('errMinVal');

				if (!empty($coupon_row->params->min_qty) && $this->product_qty<$coupon_row->params->min_qty) {
					return $this->return_false('errMinQty');
				}	

				if ($coupon_row->user_type == 'user')
				{
				// return user lists
					$tmp = awoHelper::loadObjectList('SELECT user_id FROM '._DB_PREFIX_.'awocoupon_user WHERE coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->userlist[$tmp2->user_id] = $tmp2->user_id;
				} 
				elseif ($coupon_row->user_type == 'usergroup')
				{
				// return shoppergroup list
					$tmp = awoHelper::loadObjectList('SELECT shopper_group_id FROM '._DB_PREFIX_.'awocoupon_usergroup WHERE coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->usergrouplist[$tmp2->shopper_group_id] = $tmp2->shopper_group_id;
					
					$customer = new Customer((int)$this->ps_cart->id_customer);
					$shopper_group_id = $customer->getGroups();

				}

				// country state check
				$r_err = $this->couponvalidate_country($coupon_row);
				if (!empty($r_err)) return $this->return_false($r_err);


				if (empty($user_id))
				{
					if (!empty($coupon_row->userlist))
					{
					// not a logged in user
						return $this->return_false('errUserLogin');
					}
				}


				// verify the user is on the list for this coupon
				if (!empty($coupon_row->userlist))
				{
					if (!isset($coupon_row->userlist[$user_id]))
					{
					// not on user list
						return $this->return_false('errUserNotOnList');
					}
				}
				elseif (!empty($coupon_row->usergrouplist))
				{
					$in_group = false;
					foreach ($shopper_group_id as $sgid)
						if (isset($coupon_row->usergrouplist[$sgid]))
						{
							$in_group = true; 
							break;
						}
					if (!$in_group)
					{
					// not on shopper group list
						return $this->return_false('errUserGroupNotOnList');
					}
				}

				// number of use check
				if (!empty($coupon_row->num_of_uses_total))
				{
				// check to make sure it has not been used more than the limit
					$num = awoHelper::loadResult('SELECT COUNT(id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id='.$coupon_row->id.' GROUP BY coupon_id');
					if (!empty($num) && $num >= $coupon_row->num_of_uses_total)
					{
					// total: already used max number of times
						return $this->return_false('errTotalMaxUse');
					}
				}
				if (!empty($coupon_row->num_of_uses_percustomer))
				{
				// check to make sure user has not used it more than the limit
					$num = 0;
					if (!empty($user_id))
						$num = (int)awoHelper::loadResult('SELECT COUNT(id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id='.$coupon_row->id.' AND user_id='.$user_id.' AND (user_email IS NULL OR user_email="") GROUP BY coupon_id,user_id');
					if (!$this->is_customer_num_uses($coupon_row->id, $coupon_row->num_of_uses_percustomer, $num))
					{
					// per user: already used max number of times
						return $this->return_false('errUserMaxUse');
					}
				}
								
				// check for specials
				if (!empty($coupon_row->params->exclude_special))
				{
					foreach ($coupon_row->cart_items as $k => $tmp)
						if ($tmp['on_sale']) unset($coupon_row->cart_items[$k]);// remove specials
					if (empty($coupon_row->cart_items))
					{
						// all products in cart are on special
						return $this->return_false('errDiscountedExclude');
					}
					
				}
			
				// check for giftcert products
				if (!empty($coupon_row->params->exclude_giftcert) && !empty($coupon_row->cart_items))
				{
					$sql = 'SELECT product_id FROM '._DB_PREFIX_.'awocoupon_giftcert_product WHERE product_id IN ('.implode(',', array_keys($coupon_row->cart_items_def)).')';
					$test_list = awoHelper::loadObjectList($sql, 'product_id');

					foreach ($coupon_row->cart_items as $k => $tmp)
						if (isset($test_list[$tmp['product_id']])) unset($coupon_row->cart_items[$k]);
					if (empty($coupon_row->cart_items))
					{
						// all products in cart are gift certs
						return $this->return_false('errGiftcertExclude');
					}					
				}
			}
		
			$specific_min_value = 0;
			$specific_min_qty = 0;
			if (empty($coupon_row->params->asset1_type)) $coupon_row->params->asset1_type = '';
			if ($coupon_row->function_type == 'coupon')
			{
				if ($coupon_row->params->asset1_type == 'product')
				{
					// return product lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="product" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->productlist[$tmp2->asset_id] = $tmp2->asset_id;


					// verify the product is on the list for this coupon
					if (!empty($coupon_row->productlist))
					{
						if ($coupon_row->params->asset1_mode == 'include')
						{
						// inclusive list of products
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->productlist[$row['product_id']]))
								{
									$is_in_list = true;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on product list
								return $this->return_false('errProductInclList');
							}
						}
						elseif ($coupon_row->params->asset1_mode == 'exclude')
						{
						// exclude products
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->productlist[$row['product_id']]))
								{
									$is_not_in_list = true;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on product list
								return $this->return_false('errProductExclList');
							}
						}
					}
				}
				elseif ($coupon_row->params->asset1_type == 'category')
				{
					// return category lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="category" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->categorylist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the category is on the list for this coupon
					if (!empty($coupon_row->categorylist))
					{
						// retreive the products in the order and their categories
						// get categories
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_category AS category_id,id_product AS product_id FROM '._DB_PREFIX_.'category_product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2)
						{
							if (isset($coupon_row->categorylist[$tmp2->category_id]))
								$coupon_row->cart_items_def[$tmp2->product_id]['category'] = $tmp2->category_id;
						}
						
						if ($coupon_row->params->asset1_mode == 'include')
						{
						// inclusive list of categories
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_category'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on category list
								return $this->return_false('errCategoryInclList');
							}
						}
						elseif ($coupon_row->params->asset1_mode == 'exclude')
						{
						// exclude categories
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_category'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on category list
								return $this->return_false('errCategoryExclList');
							}
						}
					}
				}
				elseif ($coupon_row->params->asset1_type == 'manufacturer')
				{
					// return manufacturer lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="manufacturer" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->manufacturerlist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the manufacturer is on the list for this coupon
					if (!empty($coupon_row->manufacturerlist))
					{
						// retreive the products in the order and their manufacturers
						// get manufacturers
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_manufacturer AS manufacturer_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['manufacturer'] = $tmp2->manufacturer_id;
						
						if ($coupon_row->params->asset1_mode == 'include')
						{
						// inclusive list of manufacturers
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_manufacturer'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on manufacturer list
								return $this->return_false('errManufacturerInclList');
							}
						}
						elseif ($coupon_row->params->asset1_mode == 'exclude')
						{
						// exclude manufacturers
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_manufacturer'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on manufacturer list
								return $this->return_false('errManufacturerExclList');
							}
						}
					}
				}
				elseif ($coupon_row->params->asset1_type == 'vendor')
				{
					// return vendor lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="vendor" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->vendorlist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the vendor is on the list for this coupon
					if (!empty($coupon_row->vendorlist))
					{
						// retreive the products in the order and their vendors
						// get vendors
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_supplier AS vendor_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['vendor'] = $tmp2->vendor_id;
						
						if ($coupon_row->params->asset1_mode == 'include')
						{
						// inclusive list of vendors
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_vendor'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on vendor list
								return $this->return_false('errVendorInclList');
							}
						}
						elseif ($coupon_row->params->asset1_mode == 'exclude')
						{
						// exclude vendors
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_vendor'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on vendor list
								return $this->return_false('errVendorExclList');
							}
						}
					}
				}
			}
			elseif ($coupon_row->function_type == 'shipping')
			{
				if(empty($coupon_row->params->asset2_type)) $coupon_row->params->asset2_type = '';
				if ($coupon_row->params->asset2_type == 'product')
				{
					// return product lists
					$sql = 'SELECT asset_id FROM #__awocoupon_asset2 WHERE asset_type="product" AND coupon_id='.$coupon_row->id;
					$tmp = awoHelper::loadObjectList($sql);
					foreach ($tmp as $tmp2) $coupon_row->productlist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the product is on the list for this coupon
					if (!empty($coupon_row->productlist))
					{
						if (empty($coupon_row->params->asset2_mode) || $coupon_row->params->asset2_mode == 'include')
						{
						// inclusive list of products
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->productlist[$row['product_id']]))
								{
									$is_in_list = true;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on product list
								return $this->return_false('errProductInclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (!isset($coupon_row->productlist[$row['product_id']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errProductInclList');
								}
							}
							
						}
						elseif ($coupon_row->params->asset2_mode == 'exclude')
						{
						// exclude products
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->productlist[$row['product_id']]))
								{
									$is_not_in_list = true;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on product list
								return $this->return_false('errProductExclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (isset($coupon_row->productlist[$row['product_id']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errProductExclList');
								}
							}
							
						}
					}
				
				}
				elseif ($coupon_row->params->asset2_type == 'category')
				{
					// return category lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM #__awocoupon_asset2 WHERE asset_type="category" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->categorylist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the category is on the list for this coupon
					if (!empty($coupon_row->categorylist))
					{
						// retreive the products in the order and their categories
						// get categories
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_category AS category_id,id_product AS product_id FROM #__category_product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2)
						{
							if (isset($coupon_row->categorylist[$tmp2->category_id]))
								$coupon_row->cart_items_def[$tmp2->product_id]['category'] = $tmp2->category_id;
						}

						if (empty($coupon_row->params->asset2_mode) || $coupon_row->params->asset2_mode == 'include')
						{
						// inclusive list of categories
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_category'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on category list
								return $this->return_false('errCategoryInclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (!isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errCategoryInclList');
								}
							}
						}
						elseif ($coupon_row->params->asset2_mode == 'exclude')
						{
						// exclude categories
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_category'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on category list
								return $this->return_false('errCategoryExclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (isset($coupon_row->categorylist[@$coupon_row->cart_items_def[$row['product_id']]['category']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errCategoryExclList');
								}
							}
						}

					}
				}
				elseif ($coupon_row->params->asset2_type == 'manufacturer')
				{
					// return manufacturer lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM #__awocoupon_asset2 WHERE asset_type="manufacturer" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->manufacturerlist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the manufacturer is on the list for this coupon
					if (!empty($coupon_row->manufacturerlist))
					{
						// retreive the products in the order and their manufacturers
						// get manufacturers
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_manufacturer AS manufacturer_id,id_product AS product_id FROM #__product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['manufacturer'] = $tmp2->manufacturer_id;
						
						if (empty($coupon_row->params->asset2_mode) || $coupon_row->params->asset2_mode == 'include')
						{
						// inclusive list of manufacturers
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_manufacturer'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on manufacturer list
								return $this->return_false('errManufacturerInclList');
							}
						
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (!isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errManufacturerInclList');
								}
							}
						}
						elseif ($coupon_row->params->asset2_mode == 'exclude')
						{
						// exclude manufacturers
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_manufacturer'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on manufacturer list
								return $this->return_false('errManufacturerExclList');
							}
						
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (isset($coupon_row->manufacturerlist[$coupon_row->cart_items_def[$row['product_id']]['manufacturer']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errManufacturerExclList');
								}
							}
						}
					}
				}
				elseif ($coupon_row->params->asset2_type == 'vendor')
				{
					// return vendor lists
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM #__awocoupon_asset2 WHERE asset_type="vendor" AND coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $coupon_row->vendorlist[$tmp2->asset_id] = $tmp2->asset_id;

					// verify the vendor is on the list for this coupon
					if (!empty($coupon_row->vendorlist))
					{
						// retreive the products in the order and their vendors
						// get vendors
						$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
						$sql = 'SELECT id_supplier AS vendor_id,id_product AS product_id FROM #__product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['vendor'] = $tmp2->vendor_id;
						
						if (empty($coupon_row->params->asset2_mode) || $coupon_row->params->asset2_mode == 'include')
						{
						// inclusive list of vendors
							$is_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
								{
									$is_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_vendor'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_in_list)
							{
							// (include) not on vendor list
								return $this->return_false('errVendorInclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (!isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errVendorInclList');
								}
							}
						}
						elseif ($coupon_row->params->asset2_mode == 'exclude')
						{
						// exclude vendors
							$is_not_in_list = false;
							foreach ($coupon_row->cart_items as $row)
							{
								if (!isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
								{
									$is_not_in_list = true;
									$coupon_row->cart_items_def[$row['product_id']]['is_valid_vendor'] = 1;
									$specific_min_value += $row['qty'] * $row['product_price'];
									$specific_min_qty += $row['qty'];
									//break 2;
								}
							}
							if (!$is_not_in_list)
							{
							// (exclude) all on vendor list
								return $this->return_false('errVendorExclList');
							}
							
							if ($coupon_row->discount_type == 'specific')
							{
								$is_not_in_list = false;
								foreach ($coupon_row->cart_items as $row)
								{
									if (isset($coupon_row->vendorlist[$coupon_row->cart_items_def[$row['product_id']]['vendor']]))
									{
										$is_not_in_list = true;
										break;
									}
								}
								if ($is_not_in_list)
								{
								// (exclude) all on product list
									return $this->return_false('errVendorExclList');
								}
							}
						}
						
					}
				}
			
				// return shipping lists
				$sql = 'SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE asset_type="shipping" AND coupon_id='.$coupon_row->id;
				$tmp = awoHelper::loadObjectList($sql);
				foreach ($tmp as $tmp2) $coupon_row->shippinglist[$tmp2->asset_id] = $tmp2->asset_id;
				


				$is_return_blank = $is_set_default = false;
				// see if user is using shipping and if so what is the cost
				$total = (float)$this->getshippingcosts();
				$id_carrier = $coupon_row->cart_shipping->shipping_id;

				if (empty($id_carrier) && !empty($total))
				{
					$is_set_default = true;
					$id_carrier = (int)awoHelper::loadResult('SELECT value FROM '._DB_PREFIX_.'configuration WHERE name="PS_CARRIER_DEFAULT"');
				}
//printr($coupon_row->shippinglist);
//printr($this->ps_cart->id_carrier);
//printrx($total);

				if ((empty($coupon_row->shippinglist) && empty($id_carrier) && empty($total)) 
				|| (!empty($coupon_row->shippinglist) && empty($id_carrier)))
				{
				// no shipping selected
					$is_return_blank = true;
				}
		
				if (!$is_return_blank)
				{
				// verify the shipping is on the list for this coupon
					if (!empty($coupon_row->shippinglist))
					{

						if ($coupon_row->params->asset1_mode == 'include')
						{
						// inclusive list of shipping
							if (empty($coupon_row->shippinglist[$id_carrier]))
							{
							// (include) selected shipping not on shipping list
								if ($is_set_default) $is_return_blank = true;
								else return $this->return_false('errShippingInclList');
							}
						}
						elseif ($coupon_row->params->asset1_mode == 'exclude')
						{
						// exclude shipping
							if (!empty($coupon_row->shippinglist[$id_carrier]))
							{
							// (exclude) selected shipping on shipping list'
								if ($is_set_default) $is_return_blank = true;
								else return $this->return_false('errShippingExclList');
							}
						}
					}
				}
				
				if ($is_return_blank)
				{
					return array(	'redeemed'=>true,
									'coupon_id'=>$coupon_row->id,
									'coupon_code'=>$coupon_row->coupon_code,
									'product_discount'=>0,
									'product_discount_notax'=>0,
									'product_discount_tax'=>0,
									'shipping_discount'=>0,
									'shipping_discount_notax'=>0,
									'shipping_discount_tax'=>0,
									'giftwrap_discount'=>0,
									'giftwrap_discount_notax'=>0,
									'giftwrap_discount_tax'=>0,
									'is_discount_before_tax'=>$is_discount_before_tax,
									'usedproducts'=>'',
									'percentage_used'=>0,
									'force_add'=>1,
								);
				}
			}
			
			elseif ($coupon_row->function_type == 'buy_x_get_y')
			{
				$do_continue = false;
				$do_count = 0;
				do
				{
					$products_x_count = $products_y_count = 0;
					$products_x_list = $products_y_list = $asset1list = array();
				
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset1 WHERE coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $asset1list[$tmp2->asset_id] = $tmp2->asset_id;
				
					$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
					if ($coupon_row->params->asset1_type == 'product');
					elseif ($coupon_row->params->asset1_type == 'category')
					{
						$sql = 'SELECT id_category AS category_id,id_product AS product_id FROM '._DB_PREFIX_.'category_product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2)
						{
							if (isset($asset1list[$tmp2->category_id]))
								$coupon_row->cart_items_def[$tmp2->product_id]['category'] = $tmp2->category_id;
						}

					}
					elseif ($coupon_row->params->asset1_type == 'manufacturer')
					{
						$sql = 'SELECT id_manufacturer AS manufacturer_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['manufacturer'] = $tmp2->manufacturer_id;
					}
					elseif ($coupon_row->params->asset1_type == 'vendor')
					{
						$sql = 'SELECT id_supplier AS vendor_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['vendor'] = $tmp2->vendor_id;
					}
				

					if ($coupon_row->params->asset1_mode == 'include')
					{
						$is_in_list = false;
						foreach ($coupon_row->cart_items as $row)
						{
							if (isset($asset1list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset1_type]]))
							{
								$is_in_list = true;
								$products_x_count += $row['qty'];
								$products_x_list[$row['product_id']] = $row['product_id'];
								$specific_min_value += $row['qty'] * $row['product_price'];
								//break 2;
							}
						}
						if (!$is_in_list)
						{
						// (include) not on manufacturer list
							return $this->return_false('errBuyXYList1IncludeEmpty');
						}
					}
					elseif ($coupon_row->params->asset1_mode == 'exclude')
					{
						$is_not_in_list = false;
						foreach ($coupon_row->cart_items as $row)
						{
							if (!isset($asset1list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset1_type]]))
							{
								$is_not_in_list = true;
								$products_x_count += $row['qty'];
								$products_x_list[$row['product_id']] = $row['product_id'];
								$specific_min_value += $row['qty'] * $row['product_price'];
								//break 2;
							}
						}
						if (!$is_not_in_list)
						{
						// (exclude) all on manufacturer list
							return $this->return_false('errBuyXYList1ExcludeEmpty');
						}
					}

				
				
					if (!empty($coupon_row->params->min_value_type) && $coupon_row->params->min_value_type == 'specific'
					&& !empty($coupon_row->min_value) && round($specific_min_value, 2) < $coupon_row->min_value)
						return $this->return_false('errMinVal');
				
				
				
					$tmp = awoHelper::loadObjectList('SELECT asset_id FROM '._DB_PREFIX_.'awocoupon_asset2 WHERE coupon_id='.$coupon_row->id);
					foreach ($tmp as $tmp2) $asset2list[$tmp2->asset_id] = $tmp2->asset_id;
				
					$ids_to_check = implode(',', array_keys($coupon_row->cart_items_def));
					if ($coupon_row->params->asset2_type == 'product');
					elseif ($coupon_row->params->asset2_type == 'category')
					{
						$sql = 'SELECT id_category AS category_id,id_product AS product_id FROM '._DB_PREFIX_.'category_product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2)
						{
							if (isset($asset2list[$tmp2->category_id]))
								$coupon_row->cart_items_def[$tmp2->product_id]['category'] = $tmp2->category_id;
						}
					}
					elseif ($coupon_row->params->asset2_type == 'manufacturer')
					{
						$sql = 'SELECT id_manufacturer AS manufacturer_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['manufacturer'] = $tmp2->manufacturer_id;
					}
					elseif ($coupon_row->params->asset2_type == 'vendor')
					{
						$sql = 'SELECT id_supplier AS vendor_id,id_product AS product_id FROM '._DB_PREFIX_.'product WHERE id_product IN ('.$ids_to_check.')';
						$tmp = awoHelper::loadObjectList($sql);
						foreach ($tmp as $tmp2) $coupon_row->cart_items_def[$tmp2->product_id]['vendor'] = $tmp2->vendor_id;
					}
					
					
					
					if (!$do_continue && !empty($coupon_row->params->addtocart))
						if ($this->buyxy_addtocart($coupon_row, $products_x_count, $products_x_list, $asset2list)) $do_continue = true;
					//if ($this->reprocess) return;
					$do_count++;
					
				}
				while ($do_count <= 1 && $do_continue);
				

				if ($coupon_row->params->asset2_mode == 'include')
				{
					$is_in_list = false;
					foreach ($coupon_row->cart_items as $row)
					{
						if (isset($asset2list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset2_type]]))
						{
							$is_in_list = true;
							$products_y_count += $row['qty'];
							$products_y_list[$row['product_id']] = $row['product_id'];
							//$specific_min_value += $row['qty'] * $row['product_price'];
							//break 2;
						}
					}
					if (!$is_in_list)
					{
					// (include) not on manufacturer list
						return $this->return_false('errBuyXYList2IncludeEmpty');
					}
				}
				elseif ($coupon_row->params->asset2_mode == 'exclude')
				{
					$is_not_in_list = false;
					foreach ($coupon_row->cart_items as $row)
					{
						if (!isset($asset2list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset2_type]]))
						{
							$is_not_in_list = true;
							$products_y_count += $row['qty'];
							$products_y_list[$row['product_id']] = $row['product_id'];
							//$specific_min_value += $row['qty'] * $row['product_price'];
							//break 2;
						}
					}
					if (!$is_not_in_list)
						return $this->return_false('errBuyXYList2ExcludeEmpty');
				}

				
			}
			
			
			//if ($coupon_row->discount_type == 'specific' && !empty($coupon_row->min_value) && round($specific_min_value, 2)<$coupon_row->min_value)
			//{
			//	return $this->return_false('errMinVal');
			//}	
			if (!empty($coupon_row->params->min_value_type) && $coupon_row->params->min_value_type == 'specific'
			&& !empty($coupon_row->min_value) && round($specific_min_value, 2) < $coupon_row->min_value)
				return $this->return_false('errMinVal');

			if (!empty($coupon_row->params->min_qty_type) && !empty($coupon_row->params->min_qty)) {
				if($coupon_row->params->min_qty_type=='specific') {
					if($specific_min_qty<$coupon_row->params->min_qty ) {
						return $this->return_false('errMinQty');
					}
				}
			}

		}
		else return $this->return_false('invalid function type');

		
		$is_valid_product = false;
		foreach ($coupon_row->cart_items as $k => $tmp) {
			if(empty($tmp['_exclude_from_discount'])) {
				$is_valid_product = true;
				break;
			}
		}
		if(!$is_valid_product) {
			// no products are eligible for discount
			return $this->return_false('errDiscountedExclude');
		}

		// for zero value coupons
		$coupon_row->coupon_value = (double)$coupon_row->coupon_value;
		if (empty($coupon_row->coupon_value) && empty($coupon_row->coupon_value_def))
		{
			return array(	'redeemed'=>true,
							'coupon_id'=>$coupon_row->id,
							'coupon_code'=>$coupon_row->coupon_code,
							'product_discount'=>0,
							'product_discount_notax'=>0,
							'product_discount_tax'=>0,
							'shipping_discount'=>0,
							'shipping_discount_notax'=>0,
							'shipping_discount_tax'=>0,
							'giftwrap_discount'=>0,
							'giftwrap_discount_notax'=>0,
							'giftwrap_discount_tax'=>0,
							'usedproducts'=>'',
							'is_discount_before_tax'=>$is_discount_before_tax,
						);
		}
		
		
		if (!empty($coupon_row->coupon_value) && $coupon_row->function_type != 'giftcert' && $coupon_row->coupon_value_type == 'amount')
			$coupon_row->coupon_value = Tools::convertPrice($coupon_row->coupon_value, Currency::getCurrencyInstance((int)($this->ps_cart->id_currency)));
		
		// ----------------------------------------------------
		// Compute Coupon Discount based on coupon parameters
		// ----------------------------------------------------
		if ($coupon_row->function_type == 'giftcert')
		{
		// gift certificate calculation
			$coupon_value = (float)($coupon_row->coupon_value - $gift_cert_used_value);
			$coupon_value = Tools::convertPrice($coupon_value, Currency::getCurrencyInstance((int)($this->ps_cart->id_currency)));
			if (!empty($coupon_value) && $coupon_value > 0)
			{
				$coupon_product_value = $coupon_shipping_value = $coupon_wrap_value = 0;
				$coupon_product_value_notax = $coupon_shipping_value_notax = $coupon_wrap_value_notax = 0;
				
				if (1 == 1)
				{ // get totals
					$total_to_use = $total_to_use_notax = 0;
					$qty = 0;
					foreach ($coupon_row->cart_items as $row)
					{
						if(!empty($row['_exclude_from_discount'])) continue;
						$total_to_use += $row['qty'] * $row['product_price'];
						$total_to_use_notax += $row['qty'] * $row['product_price_notax'];
						$usedproductids[$row['product_id']] = $row['product_id'];
						$qty += $row['qty'];
					}

				}
				
				$postfix = $is_discount_before_tax ? '_notax' : '';

				if (!empty($total_to_use))
				{ 
				# product calculation 
					$coupon_product_value = $coupon_product_value_notax = min(${'total_to_use'.$postfix}, $coupon_value);
					
					if ($is_discount_before_tax) $coupon_product_value *= 1 + (($total_to_use - $total_to_use_notax) / $total_to_use_notax);
					else $coupon_product_value_notax /= 1 + (($total_to_use - $total_to_use_notax) / $total_to_use_notax);
					if ($coupon_product_value > $total_to_use) $coupon_product_value = $total_to_use;
					if ($coupon_product_value_notax > $total_to_use_notax) $coupon_product_value_notax = $total_to_use_notax;
				}

				if (1 == 1)
				{ # shipping calculation
					$total_shipping_notax = $coupon_row->giftcert_shipping_notax;
					$total_shipping = $coupon_row->giftcert_shipping;
						
					if (!empty(${'total_shipping'.$postfix}) && $coupon_value > ${'coupon_product_value'.$postfix})
						$coupon_shipping_value = $coupon_shipping_value_notax = min((float)${'total_shipping'.$postfix}, $coupon_value - ${'coupon_product_value'.$postfix});

					if (!empty($coupon_shipping_value))
					{
						if ($is_discount_before_tax) $coupon_shipping_value *= 1 + (($total_shipping - $total_shipping_notax) / $total_shipping_notax);
						else $coupon_shipping_value_notax /= 1 + (($total_shipping - $total_shipping_notax) / $total_shipping_notax);
						if ($coupon_shipping_value > $total_shipping) $coupon_shipping_value = $total_shipping;
						if ($coupon_shipping_value_notax > $total_shipping_notax) $coupon_shipping_value_notax = $total_shipping_notax;
					}
				}
				
				
				if (1 == 1)
				{ # giftwrap calculation
					$total_wrapping = $this->ps_cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
					$total_wrapping_notax = $this->ps_cart->getOrderTotal(false, Cart::ONLY_WRAPPING);
						
					if (!empty(${'total_wrapping'.$postfix}) && $coupon_value > ((${'coupon_product_value'.$postfix} + ${'coupon_shipping_value'.$postfix})))
						$coupon_wrap_value = $coupon_wrap_value_notax = min((float)${'total_wrapping'.$postfix}, $coupon_value - (${'coupon_product_value'.$postfix} + ${'coupon_shipping_value'.$postfix}));

					if (!empty($coupon_wrap_value))
					{
						if ($is_discount_before_tax) $coupon_wrap_value *= 1 + (($total_wrapping - $total_wrapping_notax) / $total_wrapping_notax);
						else $coupon_wrap_value_notax /= 1 + (($total_wrapping - $total_wrapping_notax) / $total_wrapping_notax);
						if ($coupon_wrap_value > $total_wrapping) $coupon_wrap_value = $total_wrapping;
						if ($coupon_wrap_value_notax > $total_wrapping_notax) $coupon_wrap_value_notax = $total_wrapping_notax;
					}
				}
				
				
				// Total Amount 
				$_SESSION_product = $coupon_product_value;
				$_SESSION_product_notax = $coupon_product_value_notax;
				$_SESSION_shipping = $coupon_shipping_value;
				$_SESSION_shipping_notax = $coupon_shipping_value_notax;
				$_SESSION_giftwrap = $coupon_wrap_value;
				$_SESSION_giftwrap_notax = $coupon_wrap_value_notax;
				if($is_discount_before_tax) {
					$_SESSION_product_tax = $_SESSION_product-$_SESSION_product_notax;
					$_SESSION_shipping_tax = $_SESSION_shipping-$_SESSION_shipping_notax;
					$_SESSION_giftwrap_tax = $_SESSION_giftwrap-$_SESSION_giftwrap_notax;
				}
			
				
				//track product discount
				//$this->calc_product_realprice($coupon_row,null,$track_product_price,$coupon_product_value,$coupon_product_value_notax,$qty,$usedproductids);
				$this->calc_product_realprice(array(
					'track_product_price'=>$track_product_price,
					'is_discount_before_tax'=>$coupon_row->is_discount_before_tax,
					'coupon_row'=>$coupon_row,
					'coupon_percent'=>null,
					'discount_value'=>$coupon_product_value,
					'discount_value_notax'=>$coupon_product_value_notax,
					'shipping_discount_value'=>$_SESSION_shipping,
					'shipping_discount_value_notax'=>$_SESSION_shipping_notax,
					'qty'=>$qty,
					'usedproductids'=>$usedproductids,
				));
				
			}
		}
		elseif ($coupon_row->function_type == 'coupon')
		{
			if (!empty($coupon_row->coupon_value))
			{
			// product/category discount
				$total = $total_notax = $qty = 0;
				if ($coupon_row->discount_type == 'specific')
				{
				//specific
					foreach ($coupon_row->cart_items as $product_id => $row)
					{
						if(!empty($row['_exclude_from_discount'])) continue;
						$product_id = $row['product_id'];
						if (($coupon_row->params->asset1_type == 'product' && $coupon_row->params->asset1_mode == 'include' && isset($coupon_row->productlist[$product_id]))
						|| ($coupon_row->params->asset1_type == 'product' && $coupon_row->params->asset1_mode == 'exclude' && !isset($coupon_row->productlist[$product_id]))
						|| ($coupon_row->params->asset1_type == 'category' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_category']))
						|| ($coupon_row->params->asset1_type == 'manufacturer' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_manufacturer']))
						|| ($coupon_row->params->asset1_type == 'vendor' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_vendor'])))
						{
							$usedproductids[] = $product_id;
							$qty += $row['qty'];
							$total += $row['qty'] * $row['product_price'];
							$total_notax += $row['qty'] * $row['product_price_notax'];
						}
					}
				}
				elseif ($coupon_row->discount_type == 'overall')
				{
				// product total including tax
					foreach ($coupon_row->cart_items as $row)
					{
						if(!empty($row['_exclude_from_discount'])) continue;
						$usedproductids[] = $row['product_id'];
						$qty += $row['qty'];
						$total += $row['qty'] * $row['product_price'];
						$total_notax += $row['qty'] * $row['product_price_notax'];
					}
				}
				if (!empty($total))
				{
					$_SESSION_product = $_SESSION_product_notax = $coupon_row->coupon_value;
					if ($coupon_row->coupon_value_type == 'percent')
					{
						$percentage_used = $coupon_row->coupon_value;
						$_SESSION_product = round($total * $_SESSION_product / 100, 2);
						$_SESSION_product_notax = round($total_notax * $_SESSION_product_notax / 100, 2);
					}
					else
					{
						if ($is_discount_before_tax) $_SESSION_product *= 1 + (($total - $total_notax) / $total_notax);
						else $_SESSION_product_notax /= 1 + (($total - $total_notax) / $total_notax);
					}
					
					if ($total < $_SESSION_product) $_SESSION_product = (float)$total;
					if ($total_notax < $_SESSION_product_notax) $_SESSION_product_notax = (float)$total_notax;

					//if (!$is_discount_before_tax) $_SESSION_product_notax = $_SESSION_product;
					if($is_discount_before_tax) $_SESSION_product_tax = $_SESSION_product-$_SESSION_product_notax;

					//track product discount
					//$this->calc_product_realprice($coupon_row,$coupon_row->coupon_value,$track_product_price,$_SESSION_product,$_SESSION_product_notax,$qty,$usedproductids);
					$this->calc_product_realprice(array(
						'track_product_price'=>$track_product_price,
						'is_discount_before_tax'=>$coupon_row->is_discount_before_tax,
						'coupon_row'=>$coupon_row,
						'coupon_percent'=>$coupon_row->coupon_value,
						'discount_value'=>$_SESSION_product,
						'discount_value_notax'=>$_SESSION_product_notax,
						'qty'=>$qty,
						'usedproductids'=>$usedproductids,
						'percentage_used'=> $coupon_row->coupon_value_type == 'percent' ? $coupon_row->coupon_value : 0,
					));
				}
			}
			elseif (empty($coupon_row->coupon_value) && !empty($coupon_row->coupon_value_def)
					&& preg_match('/^(\d+\-\d+([.]\d+)?;)+(\[[_a-z]+\=[a-z]+(\&[_a-z]+\=[a-z]+)*\])?$/', $coupon_row->coupon_value_def))
			{
			// cumulative coupon calculation
				$vdef_table = $vdef_options = array();
				$each_row = explode(';', $coupon_row->coupon_value_def);
					
				//options
				$tmp = end($each_row);
				if (substr($tmp, 0, 1) == '[')
				{
					parse_str(trim($tmp, '[]'), $vdef_options);
					array_pop($each_row);
				}
				reset($each_row);
					
				foreach ($each_row as $row)
				{
					if (strpos($row, '-') !== false)
					{
						list($p, $v) = explode('-', $row);
						$vdef_table[$p] = $v;
					}
				}
				$max_qty = 0;
				if (!empty($vdef_table))
				{
					if (count($vdef_table) >= 2)
					{
						$tmp_table = $vdef_table;
						$tmp = array_pop($tmp_table);
						if (empty($tmp))
						{
							krsort($tmp_table, SORT_NUMERIC);
							$max_qty = key($tmp_table);
						}
					}
			
			
					$qty = $qty_distinct = $total = $total_notax = 0;
				
					if ($coupon_row->discount_type == 'overall')
					{
						foreach ($coupon_row->cart_items as $row)
						{
							if(!empty($row['_exclude_from_discount'])) continue;
							$total_qty = 0;
							if (empty($max_qty)) $total_qty = $row['qty'];
							else
							{
								if ($qty >= $max_qty);
								elseif (($qty + $row['qty']) <= $max_qty) $total_qty = $row['qty'];
								else $total_qty = $max_qty - $qty;
							}
							if (!empty($total_qty))
							{
								$usedproductids[] = $row['product_id'];
								$total += $total_qty * $row['product_price'];
								$total_notax += $total_qty * $row['product_price_notax'];
							}
							$qty += $row['qty'];
							$qty_distinct += 1; 
						}
						
					}
					elseif ($coupon_row->discount_type == 'specific')
					{
						foreach ($coupon_row->cart_items as $row)
						{
							if(!empty($row['_exclude_from_discount'])) continue;
							$product_id = $row['product_id'];
							if (($coupon_row->params->asset1_type == 'product' && $coupon_row->params->asset1_mode == 'include' && isset($coupon_row->productlist[$product_id]))
								|| ($coupon_row->params->asset1_type == 'product' && $coupon_row->params->asset1_mode == 'exclude' && !isset($coupon_row->productlist[$product_id]))
								|| ($coupon_row->params->asset1_type == 'category' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_category']))
								|| ($coupon_row->params->asset1_type == 'manufacturer' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_manufacturer']))
								|| ($coupon_row->params->asset1_type == 'vendor' && !empty($coupon_row->cart_items_def[$product_id]['is_valid_vendor'])))
							{
								$total_qty = 0;
								if (empty($max_qty)) $total_qty = $row['qty'];
								else
								{
									if ($qty >= $max_qty);
									elseif (($qty + $row['qty']) <= $max_qty) $total_qty = $row['qty'];
									else $total_qty = $max_qty - $qty;
								}
								if (!empty($total_qty))
								{
									$usedproductids[] = $product_id;
									$total += $total_qty * $row['product_price'];
									$total_notax += $total_qty * $row['product_price_notax'];
								}
								$qty += $row['qty'];
								$qty_distinct += 1; 
							}
						}
					}
					if (!empty($qty))
					{
						if (!empty($max_qty)) array_pop($vdef_table);
						krsort($vdef_table, SORT_NUMERIC);
						if (!empty($vdef_options['qty_type']) && $vdef_options['qty_type'] == 'distinct') $qty = $qty_distinct;

						foreach ($vdef_table as $pcount => $val)
						{
							if ($pcount <= $qty)
							{
								$coupon_value = $val;
								break;
							}
						}
						if (!empty($coupon_value))
						{
							if ($coupon_row->coupon_value_type == 'amount') 
								$coupon_value = Tools::convertPrice($coupon_value, Currency::getCurrencyInstance((int)($this->ps_cart->id_currency)));
							$_SESSION_product = $_SESSION_product_notax = $coupon_percent = $coupon_value;
							if ($coupon_row->coupon_value_type == 'percent')
							{
								$percentage_used = $coupon_value;
								$_SESSION_product = round($total * $coupon_value / 100, 2);
								$_SESSION_product_notax = round($total_notax * $coupon_value / 100, 2);
							}
							else
							{
								if ($is_discount_before_tax) $_SESSION_product *= 1 + (($total - $total_notax) / $total_notax);
								else $_SESSION_product_notax /= 1 + (($total - $total_notax) / $total_notax);
							}

							if ($total < $_SESSION_product) $_SESSION_product = (float)$total;
							if ($total_notax < $_SESSION_product_notax) $_SESSION_product_notax = (float)$total_notax;
							
							if($is_discount_before_tax) $_SESSION_product_tax = $_SESSION_product-$_SESSION_product_notax;

							//track product discount
							//$this->calc_product_realprice($coupon_row,$coupon_percent,$track_product_price,$_SESSION_product,$_SESSION_product_notax,$qty,$usedproductids);
							$this->calc_product_realprice(array(
								'track_product_price'=>$track_product_price,
								'is_discount_before_tax'=>$coupon_row->is_discount_before_tax,
								'coupon_row'=>$coupon_row,
								'coupon_percent'=>$coupon_percent,
								'discount_value'=>$_SESSION_product,
								'discount_value_notax'=>$_SESSION_product_notax,
								'qty'=>$qty,
								'usedproductids'=>$usedproductids,
							));
						}
						else
						{
						// cumulative coupon, threshold not reached
							return $this->return_false('errProgressiveThreshold');
						}
					}
				}
			}
		}
		elseif ($coupon_row->function_type == 'shipping')
		{
		// shipping discount
			$total = (float)$this->getshippingcosts();
			$total_notax = (float)$this->getshippingcosts(false);
			if (!empty($total))
			{
				$_SESSION_shipping = $_SESSION_shipping_notax = $coupon_row->coupon_value;
				if ($coupon_row->coupon_value_type == 'percent')
				{
					$_SESSION_shipping = round($total * $_SESSION_shipping / 100, 2);
					$_SESSION_shipping_notax = round($total_notax * $_SESSION_shipping_notax / 100, 2);
				}
				else
				{
					if ($is_discount_before_tax) $_SESSION_shipping *= 1 + (($total - $total_notax) / $total_notax);
					else $_SESSION_shipping_notax /= 1 + (($total - $total_notax) / $total_notax);
				}
				if ($total < $_SESSION_shipping) $_SESSION_shipping = (float)$total;
				if ($total_notax < $_SESSION_shipping_notax) $_SESSION_shipping_notax = (float)$total_notax;

				if($is_discount_before_tax) $_SESSION_shipping_tax = $_SESSION_shipping-$_SESSION_shipping_notax;
				
				//track shipping discount
				$this->calc_product_realprice(array(
					'track_product_price'=>$track_product_price,
					'is_discount_before_tax'=>$is_discount_before_tax,
					'coupon_row'=>$coupon_row,
					'coupon_percent'=>null,
					'discount_value'=>null,
					'discount_value_notax'=>null,
					'shipping_discount_value'=>$_SESSION_shipping,
					'shipping_discount_value_notax'=>$_SESSION_shipping_notax,
					'qty'=>null,
					'usedproductids'=>null,
				));
			}
		}
		elseif ($coupon_row->function_type == 'buy_x_get_y')
		{
		//$products_x_count = $products_y_count = 0;
		//$products_x_list = $products_y_list = $asset1list = array();
			
			$valid_items = array();
			$potential_items = $potential_items_details = array();
			$coupon_row->params->asset1_qty = (int)$coupon_row->params->asset1_qty;
			$coupon_row->params->asset2_qty = (int)$coupon_row->params->asset2_qty;
		
			$i = 0;
			foreach ($coupon_row->cart_items as $product_id => $row)
			{
				if(!empty($row['_exclude_from_discount'])) continue;
				if (!isset($products_y_list[$row['product_id']])) continue;
				for ($j = 0; $j < $row['qty']; $j++)
				{ 
					if (!empty($coupon_row->params->product_match))
					{
						$potential_items[$row['product_id']][$i] = $row['product_price'];
						$potential_items_details[$row['product_id']][$i] = array('product_id'=>$row['product_id'],'product_price'=>$row['product_price'],'product_price_notax'=>$row['product_price_notax']);
					}
					else
					{
						$potential_items[0][$i] = $row['product_price'];
						$potential_items_details[0][$i] = array('product_id'=>$row['product_id'],'product_price'=>$row['product_price'],'product_price_notax'=>$row['product_price_notax']);
					}
					$i++;
				}
			}
			
			if (!empty($potential_items)
			&& !empty($coupon_row->params->asset1_qty) && $coupon_row->params->asset1_qty > 0
			&& !empty($coupon_row->params->asset2_qty) && $coupon_row->params->asset2_qty > 0)
			{
				if (!empty($coupon_row->params->product_match))
				{
					if ($coupon_row->params->process_type == 'first');
					else
					{
						$tester = array();
						foreach ($potential_items as $k => $r1)
						{
							foreach ($r1 as $r2)
							{
								$tester[$k] = $r2;
								break;
							}
						}
						if ($coupon_row->params->process_type == 'lowest') asort($tester, SORT_NUMERIC);
						elseif ($coupon_row->params->process_type == 'highest') arsort($tester, SORT_NUMERIC);

						$tmp = $potential_items;
						$potential_items = array();
						foreach ($tester as $key => $val) $potential_items[$key] = $tmp[$key];
					}
				}
				else
				{
					if ($coupon_row->params->process_type == 'first');
					elseif ($coupon_row->params->process_type == 'lowest') asort($potential_items[0], SORT_NUMERIC);
					elseif ($coupon_row->params->process_type == 'highest') arsort($potential_items[0], SORT_NUMERIC);
				}
				
				foreach ($potential_items as $pindex => $current_potential_item)
				{
					$t_products_x_count = !empty($coupon_row->params->product_match) ? count($current_potential_item) : $products_x_count;

				
					while ($t_products_x_count >= $coupon_row->params->asset1_qty)
					{ 
						$t_products_x_count -= $coupon_row->params->asset1_qty;
						$items = array();
						for ($j = 0; $j < $coupon_row->params->asset2_qty; $j++)
						{
							if (empty($current_potential_item)) break 2;
						
							$keys = array_keys($current_potential_item);
							$pkey = array_shift($keys);
							$item = $potential_items_details[$pindex][$pkey];
							unset($current_potential_item[$pkey]);
							if (in_array($item['product_id'], $products_x_list)) $t_products_x_count -= 1;
		
							if ($t_products_x_count < 0) break 2; // not enough products, error
							$items[] = $item;
						}
						$valid_items = array_merge($valid_items, $items);
					}
					
				}
//printrx($valid_items);
				
				if (!empty($coupon_row->params->max_discount_qty)) $valid_items = array_slice($valid_items, 0, $coupon_row->params->max_discount_qty * $coupon_row->params->asset2_qty);
				
				if (empty($valid_items))
					return $this->return_false('errBuyXYList1IncludeEmpty');

				$total = $total_notax = 0;
				$qty = count($valid_items);
				foreach ($valid_items as $product_key => $item)
				{
					$total += $item['product_price'];
					$total_notax += $item['product_price_notax'];
					$usedproductids[$item['product_id']] = $item['product_id'];
				}
					
				if (!empty($total))
				{
					$_SESSION_product = $_SESSION_product_notax = $coupon_row->coupon_value;
					if ($coupon_row->coupon_value_type == 'percent')
					{
						$_SESSION_product = round($total * $_SESSION_product / 100, 2);
						$_SESSION_product_notax = round($total_notax * $_SESSION_product_notax / 100, 2);
					}
					else
					{
						$_SESSION_product = $_SESSION_product_notax = $coupon_row->coupon_value * count($valid_items);
						
						if ($is_discount_before_tax) $_SESSION_product *= 1 + (($total - $total_notax) / $total_notax);
						else $_SESSION_product_notax /= 1 + (($total - $total_notax) / $total_notax);
					}
					
					if ($total < $_SESSION_product) $_SESSION_product = (float)$total;
					if ($total_notax < $_SESSION_product_notax) $_SESSION_product_notax = (float)$total_notax;

					
					if($is_discount_before_tax) $_SESSION_product_tax = $_SESSION_product-$_SESSION_product_notax;

					//track product discount
					//$this->calc_product_realprice($coupon_row,$coupon_row->coupon_value,$track_product_price,$_SESSION_product,$_SESSION_product_notax,$qty,$usedproductids);
					$this->calc_product_realprice(array(
						'track_product_price'=>$track_product_price,
						'is_discount_before_tax'=>$coupon_row->is_discount_before_tax,
						'coupon_row'=>$coupon_row,
						'coupon_percent'=>$coupon_row->coupon_value,
						'discount_value'=>$_SESSION_product,
						'discount_value_notax'=>$_SESSION_product_notax,
						'qty'=>$qty,
						'usedproductids'=>$usedproductids,
					));
				}
			}
			
			
		}
		


		if (!empty($_SESSION_product) || !empty($_SESSION_shipping))
		{
			return array(	'redeemed'=>true,
							'coupon_id'=>$coupon_row->id,
							'coupon_code'=>$coupon_row->coupon_code,
							'product_discount'=>$_SESSION_product,
							'product_discount_notax'=>$_SESSION_product_notax,
							'product_discount_tax'=>$_SESSION_product_tax,
							'shipping_discount'=>$_SESSION_shipping,
							'shipping_discount_notax'=>$_SESSION_shipping_notax,
							'shipping_discount_tax'=>$_SESSION_shipping_tax,
							'giftwrap_discount'=>$_SESSION_giftwrap,
							'giftwrap_discount_notax'=>$_SESSION_giftwrap_notax,
							'giftwrap_discount_tax'=>$_SESSION_giftwrap_tax,
							'is_discount_before_tax'=>$is_discount_before_tax,
							'usedproducts'=>!empty($usedproductids) ? implode(',', $usedproductids) : '',
							'percentage_used' => $percentage_used,
						);
		}
	}    
	


	protected function calc_product_realprice($params)
	{
		if (empty($params['track_product_price'])) return;
		
		if (!empty($params['discount_value']))
		{ 
		//track product discount
			$tracking_discount = 0;
			$tracking_discount_notax = 0;
			if ($params['coupon_row']->function_type != 'buy_x_get_y' && $params['coupon_row']->coupon_value_type == 'percent')
			{
				$k_lastused = -1;
				foreach ($this->cart->items as $k => $row)
				{
					if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
					if (!in_array($row['product_id'], $params['usedproductids'])) continue;
					$k_lastused = $k;
					//if (!empty($params['usedproductids']) && !in_array($row['product_id'],$params['usedproductids'])) continue;
					
					$discount = round($row['product_price'] * $params['coupon_percent'] / 100, 2);
					$this->cart->items[$k]['product_price'] -= $discount;
					$this->cart->items[$k]['totaldiscount'] += $row['qty'] * $discount;
					$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] = $row['qty'] * $discount;
					$tracking_discount += $row['qty'] * $discount;
					
					$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] = 0;
					$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'] = 0;

					$discount = round($row['product_price_notax'] * $params['coupon_percent'] / 100, 2);
					$this->cart->items[$k]['product_price_notax'] -= $discount;
					//if ($params['is_discount_before_tax'])
					//{
						$this->cart->items[$k]['totaldiscount_notax'] += $row['qty'] * $discount;
						$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] = $row['qty'] * $discount;
					//}
					$tracking_discount_notax += $row['qty'] * $discount;
					
					
				}
				//penny problem
				if ($k_lastused != -1 && $tracking_discount != $params['discount_value'])
				{
					$this->cart->items[$k_lastused]['product_price'] -= $tracking_discount - $params['discount_value'];
					$this->cart->items[$k_lastused]['totaldiscount'] += $tracking_discount - $params['discount_value'];
					$this->cart->items[$k_lastused]['coupons'][$params['coupon_row']->id]['totaldiscount'] += $tracking_discount - $params['discount_value'];
				}
				if ($k_lastused != -1 && round($tracking_discount_notax, 4) != round($params['discount_value_notax'], 2))
				{
					$this->cart->items[$k_lastused]['product_price_notax'] -= $tracking_discount_notax - $params['discount_value_notax'];
					//if ($params['is_discount_before_tax'])
					//{
						$this->cart->items[$k_lastused]['totaldiscount_notax'] += $tracking_discount_notax - $params['discount_value_notax'];
						$this->cart->items[$k_lastused]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] += $tracking_discount_notax - $params['discount_value_notax'];
					//}
				}
				
				// calculate tax
				foreach ($this->cart->items as $k => $row)
				{
					if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
					if (!$params['is_discount_before_tax']) continue;
					if (!in_array($row['product_id'], $params['usedproductids'])) continue;
					
					$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'] = $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] - $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'];
					$this->cart->items[$k]['totaldiscount_tax'] += $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'];
				}
				
				
			}
			else
			{
				if ($params['is_discount_before_tax'])
				{
					//make sure all the money is distributed
					$fail_safe = 0;
					$tmp_discounts = array();
					while ($tracking_discount_notax < $params['discount_value_notax'])
					{
						$each_discount = round(($params['discount_value_notax'] - $tracking_discount_notax) / $params['qty'], 2);
							
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
							$discount = min($each_discount, $row['product_price_notax']);
							@$tmp_discounts[$k] += $discount;
							$this->cart->items[$k]['product_price_notax'] -= $discount;
							$this->cart->items[$k]['totaldiscount_notax'] += $row['qty'] * $discount;
							@$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] += $row['qty'] * $discount;
							$tracking_discount_notax += $row['qty'] * $discount;
							
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'] = 0;
						}
						
						$fail_safe++;
						if ($fail_safe == 200) break;
					}
					//penny problem
					if ($tracking_discount_notax != $params['discount_value_notax'])
					{
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
							$discount = min(($params['discount_value_notax'] - $tracking_discount_notax) / $row['qty'], $row['product_price_notax']);
							@$tmp_discounts[$k] += $discount;
							$this->cart->items[$k]['product_price_notax'] -= $discount;
							$this->cart->items[$k]['totaldiscount_notax'] += $row['qty'] * $discount;
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] += $row['qty'] * $discount;
							$tracking_discount_notax += $row['qty'] * round($discount, 2);
						}
					}
					
					foreach ($this->cart->items as $k => $row)
					{
						if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
						if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
						// calculate price after tax
						$discount = $tmp_discounts[$k] * (1 + $row['tax_rate']);
						$this->cart->items[$k]['product_price'] -= $discount;
						$this->cart->items[$k]['totaldiscount'] += $row['qty'] * $discount;
						@$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] += $row['qty'] * $discount;
						
						// calculate tax
						$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'] = $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] - $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'];
						$this->cart->items[$k]['totaldiscount_tax'] += $this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'];
					}
					
				}
				else
				{
					//make sure all the money is distributed
					$fail_safe = 0;
					while ($tracking_discount < $params['discount_value'])
					{
						$each_discount = round(($params['discount_value'] - $tracking_discount) / $params['qty'], 2);
					
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
							$discount = min($each_discount, $row['product_price']);
							$this->cart->items[$k]['product_price'] -= $discount;
							$this->cart->items[$k]['totaldiscount'] += $row['qty'] * $discount;
							@$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] += $row['qty'] * $discount;
							$tracking_discount += $row['qty'] * $discount;
							
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] = 0;
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_tax'] = 0;
						}
						
						$fail_safe++;
						if ($fail_safe == 200) break;
					}
					//penny problem
					if ($tracking_discount != $params['discount_value'])
					{
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
						
							$discount = min(($params['discount_value'] - $tracking_discount) / $row['qty'], $row['product_price']);
							$this->cart->items[$k]['product_price'] -= $discount;
							$this->cart->items[$k]['totaldiscount'] += $row['qty'] * $discount;
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount'] += $row['qty'] * $discount;
							$tracking_discount += $row['qty'] * round($discount, 2);
						}
					}
				
				
					//make sure all the money is distributed
					$fail_safe = 0;
					while ($tracking_discount_notax < $params['discount_value_notax'])
					{
						$each_discount = round(($params['discount_value_notax'] - $tracking_discount_notax) / $params['qty'], 2);
						
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
							$discount = min($each_discount, $row['product_price_notax']);
							$this->cart->items[$k]['product_price_notax'] -= $discount;
							$this->cart->items[$k]['totaldiscount_notax'] += $row['qty'] * $discount;
							@$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] += $row['qty'] * $discount;
							$tracking_discount_notax += $row['qty'] * $discount;
						}
						
						$fail_safe++;
						if ($fail_safe == 200) break;
					}
					//penny problem
					if ($tracking_discount_notax != $params['discount_value_notax'])
					{
						foreach ($this->cart->items as $k => $row)
						{
							if(!empty($params['coupon_row']->cart_items[$k]['_exclude_from_discount'])) continue;
							if (!in_array($row['product_id'], $params['usedproductids'])) continue;
							
							$discount = min(($params['discount_value_notax'] - $tracking_discount_notax) / $row['qty'], $row['product_price_notax']);
							$this->cart->items[$k]['product_price_notax'] -= $discount;
							$this->cart->items[$k]['totaldiscount_notax'] += $row['qty'] * $discount;
							$this->cart->items[$k]['coupons'][$params['coupon_row']->id]['totaldiscount_notax'] += $row['qty'] * $discount;
							$tracking_discount_notax += $row['qty'] * round($discount, 2);
						}
					}
				}	
				
			}
		}

		if (!empty($params['shipping_discount_value']))
		{ 
		//track shipping discount
			
			$this->cart->shipping->total -= $params['shipping_discount_value'];
			$this->cart->shipping->totaldiscount += $params['shipping_discount_value'];
			$this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount'] = $params['shipping_discount_value'];
			
			$this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_notax'] = 0;
			$this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_tax'] = 0;

			$this->cart->shipping->total_notax -= $params['shipping_discount_value_notax'];
			//if ($params['is_discount_before_tax']) 
			//{
				$this->cart->shipping->totaldiscount_notax += $params['shipping_discount_value_notax'];
				$this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_notax'] = $params['shipping_discount_value_notax'];
			//}
			
		
			// calculate tax
			if ($params['is_discount_before_tax'])
			{
				$this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_tax'] = $this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount'] - $this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_notax'];
				$this->cart->shipping->totaldiscount_tax += $this->cart->shipping->coupons[$params['coupon_row']->id]['totaldiscount_tax'];
			}
		}
			

	}
	
	public function return_false($key)
	{ //trigger_error($key);
		$do_not_display = Tools::getValue('autodiscount_error');
		if ($do_not_display)
		{
			$this->_errors = '__AUTODISCOUNT_ERROR__';
			return false;
		}

		//if ($this->reprocess) return;
		if (empty($this->coupon_row) || $this->coupon_row->function_type != 'parent')
		{
			$err = awohelper::getLangUserData($this->params->get($key, 'Coupon code not found'));
			$this->_errors = (!empty($this->coupon_row) ? $this->coupon_row->coupon_code.': ' : '').$err;
		}

		return false;
	}
	
	public function initialize_coupon()
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'awocoupon_cart` WHERE `id_cart` = '.(int)($this->id_cart).' LIMIT 1');
	}
	
	public function finalize_coupon($master_output)
	{
		$product_discount = $product_discount_notax = $product_discount_tax
			= $shipping_discount = $shipping_discount_notax = $shipping_discount_tax
			= $giftwrap_discount = $giftwrap_discount_notax = $giftwrap_discount_tax = 0;
		$usedproducts = '';
		$coupon_codes = $usedcoupons = $entered_coupon_ids = array();
		$auto_codes = @$this->get_coupon_auto()->coupons;
		
		foreach ($master_output as $coupon_id => $r) {
			if (empty($r[1]['force_add']) && empty($r[1]['product_discount']) && empty($r[1]['shipping_discount'])) continue;
			
			$isauto = false;
			if(!empty($auto_codes))  { 
				foreach($auto_codes as $auto_code) {
					if($auto_code->id == $r[1]['coupon_id']) {
						$isauto = true;
						break;
					}
				}
			}
			
			
			
			$coupon_codes[] = $r[1]['coupon_code'];
			$entered_coupon_ids[$r[1]['coupon_id']] = 1;
			$product_discount += $r[1]['product_discount'];
			$product_discount_notax += $r[1]['product_discount_notax'];
			$product_discount_tax += $r[1]['product_discount_tax'];
			$shipping_discount += $r[1]['shipping_discount'];
			$shipping_discount_notax += $r[1]['shipping_discount_notax'];
			$shipping_discount_tax += $r[1]['shipping_discount_tax'];
			$giftwrap_discount += $r[1]['giftwrap_discount'];
			$giftwrap_discount_notax += $r[1]['giftwrap_discount_notax'];
			$giftwrap_discount_tax += $r[1]['giftwrap_discount_tax'];
			if (!empty($r[1]['usedproducts'])) $usedproducts .= $r[1]['usedproducts'].',';
			if (!empty($r[2])) $usedcoupons = $usedcoupons + $r[2];
			else $usedcoupons[$r[1]['coupon_id']] = array(
						'coupon_entered_id'=>$r[1]['coupon_id'],
						'coupon_code'=>$r[1]['coupon_code'],
						'product_discount'=>$r[1]['product_discount'],
						'product_discount_notax'=>$r[1]['product_discount_notax'],
						'product_discount_tax'=>$r[1]['product_discount_tax'],
						'shipping_discount'=>$r[1]['shipping_discount'],
						'shipping_discount_notax'=>$r[1]['shipping_discount_notax'],
						'shipping_discount_tax'=>$r[1]['shipping_discount_tax'],
						'giftwrap_discount'=>$r[1]['giftwrap_discount'],
						'giftwrap_discount_notax'=>$r[1]['giftwrap_discount_notax'],
						'giftwrap_discount_tax'=>$r[1]['giftwrap_discount_tax'],
						'is_discount_before_tax'=>$r[1]['is_discount_before_tax'],
						'percentage_used'=>$r[1]['percentage_used'],
						'usedproducts'=>$r[1]['usedproducts'],
						'isauto'=>$isauto,
						'isgift'=>$r[0]->function_type=='giftcert' ? true : false,
						'ischild'=>false,
						'note'=>$r[0]->note,
					);
			if (1 == 1)
			{ # set coupon display name
				if (!isset($coupon_codes_noauto)) $coupon_codes_noauto = array();
				$display_text = '';
				if (!empty($r[0]->note))
				{
					$match = array();
					preg_match('/{customer_display_text:(.*)?}/i', $r[0]->note, $match);
					if (!empty($match[1])) $display_text = $match[1];
				}
				if (empty($display_text)) $display_text = $r[1]['coupon_code'];
				$coupon_codes_noauto[] = $display_text;
				if ($r[0]->function_type != 'parent') $usedcoupons[$r[1]['coupon_id']]['coupon_code_display'] = $display_text;
				else
				{
					foreach ($usedcoupons as &$item)
					{
						if ($item['coupon_entered_id'] == $r[1]['coupon_id'] && empty($item['coupon_code_display']))
							$item['coupon_code_display'] = $display_text;
					}
				}
			}
		}
		if (empty($usedcoupons)) return false;
		
		
		$cart_items = $this->cart->items;
		foreach($cart_items as $k=>$line) unset($cart_items[$k]['product']);
		
		
		$coupon_codes_str = implode(';', $coupon_codes);
		$session_array = array(
			'redeemed'=>true,
			'coupon_id'=>count($coupon_codes) == 1 ? key($master_output) : '--multiple--',
			'coupon_code'=>$coupon_codes_str,
			'coupon_code_display'=>implode(';', $coupon_codes_noauto),
			'product_discount'=>$product_discount,
			'product_discount_notax'=>$product_discount_notax,
			'product_discount_tax'=>$product_discount_tax,
			'shipping_discount'=>$shipping_discount,
			'shipping_discount_notax'=>$shipping_discount_notax,
			'shipping_discount_tax'=>$shipping_discount_tax,
			'giftwrap_discount'=>$giftwrap_discount,
			'giftwrap_discount_notax'=>$giftwrap_discount_notax,
			'giftwrap_discount_tax'=>$giftwrap_discount_tax,
			'productids'=>$usedproducts,
			'uniquecartstring'=>$this->getuniquecartstring($coupon_codes_str),
			'entered_coupon_ids'=>$entered_coupon_ids,
			'processed_coupons'=>$usedcoupons,
			'cart_items'=>$cart_items,
		);
		
				
		//Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'awocoupon_cart` SET coupon_data="'.pSQL(serialize($session_array)).'" WHERE `id_cart` = '.(int)($this->id_cart).' LIMIT 1');
		//Db::getInstance()->AutoExecute(_DB_PREFIX_.'awocoupon_cart', array('coupon_data' => serialize($session_array), 'id_cart' => (int)($this->id_cart)), 'INSERT');
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'awocoupon_cart` (id_cart, coupon_data) VALUES ('.(int)$this->id_cart.',"'.pSQL(serialize($session_array)).'")'); // 1.7.0

		
		return $session_array;
		
	}
	
	public function delete_cart_code($id_cart_rule)
	{
		if (substr($id_cart_rule, 0, 10) == 'awocoupon-') $id_cart_rule = substr($id_cart_rule, 10);
		$coupon_id = (int)$id_cart_rule;
		
		if (empty($coupon_id)) return;

		$coupon_awo_entered_coupon_ids = array();
		$coupon_session = !empty($this->coupon_session['coupon_data']) ? $this->coupon_session['coupon_data'] : '';
		if (empty($coupon_session)) return;
		
		$coupon_session = unserialize($coupon_session);
		if (!isset($coupon_session['entered_coupon_ids'][$coupon_id])) return;
		
		if (count($coupon_session['entered_coupon_ids']) == 1)
			awohelper::query('DELETE FROM #__awocoupon_cart WHERE `id_cart` = '.(int)($this->id_cart).' LIMIT 1');
		else
		{
			$coupon_session['uniquecartstring'] = mt_rand();
			unset($coupon_session['entered_coupon_ids'][$coupon_id]);
			unset($coupon_session['processed_coupons'][$coupon_id]);
			
			$this->coupon_session['new_ids'] = 0;
			$this->coupon_session['coupon_data'] = serialize($coupon_session);
			$this->validate_coupon_code(true);
		}
	}

	
	public function getuniquecartstring($coupon_code = null)
	{
		if (empty($coupon_code))
		{
			$coupon_session = $this->coupon_session['coupon_data'];
			if (!empty($coupon_session))
			{
				$coupon_session = unserialize($coupon_session);
				$coupon_code = $coupon_session['coupon_code'];
			}
		}
		if (!empty($coupon_code))
		{
			//$string = $this->vmcartPrices['basePriceWithTax'].'|'.$coupon_code;
			$user_id = (int)$this->ps_cart->id_customer;
			$user_email = !empty($this->customer->email) ? $this->customer->email : '';
			$string = '|'.$this->ps_cart->id_currency.'|'.$coupon_code.'|'.$user_id.'|'.$user_email;
			foreach ($this->ps_cartitems as $k => $r) $string .= '|'.$k.'|'.$r['id_product'].'|'.$r['quantity'];
			return $string.'|ship|'.$this->ps_cart->id_carrier;
		}
		return;
	}
	
	public function is_customer_num_uses($coupon_id, $max_num_uses, $customer_num_uses, $is_parent = false)
	{
		$email = !empty($this->customer->email) ? $this->customer->email : '';
		$customer_num_uses = (int)$customer_num_uses;
		$max_num_uses = (int)$max_num_uses;

		if (!empty($email))
		{
			if (!$is_parent)
			{
				$sql = 'SELECT COUNT(id) FROM '._DB_PREFIX_.'awocoupon_history
						 WHERE coupon_id='.$coupon_id.' AND user_email="'.pSQL($email).'"
						 GROUP BY coupon_id';
			}
			else
			{
				$sql = 'SELECT COUNT(DISTINCT order_id) FROM '._DB_PREFIX_.'awocoupon_history 
						 WHERE coupon_entered_id='.$coupon_id.' AND user_email="'.pSQL($email).'"
						 GROUP BY coupon_entered_id';
			
			}
			$customer_num_uses += (int)awoHelper::loadResult($sql);
		}
		
		if (!empty($customer_num_uses) && $customer_num_uses >= $max_num_uses)
		{
		// per user: already used max number of times
			return false;
		}
		
		return true;

	}
	
	public function define_cart_items()
	{
		// retreive cart items
		$this->cart = new stdClass();
		$this->cart->items = array();
		$this->cart->items_def = array();
		
		$this->ps_cart = new Cart($this->id_cart);
		$this->ps_cartitems = $this->ps_cart->getProducts();

		$customizedData = Product::getAllCustomizedDatas($this->ps_cart->id);
		
		$this->product_total  = 0;
		$this->product_qty  = 0;
		foreach ($this->ps_cartitems as $product)
		{
			$productId = $product['id_product'];
			if (empty($product['quantity']) || empty($productId))
				continue;

			$product_discount = $product['on_sale'];
			if (empty($product_discount))
			{
				$address_id = version_compare(_PS_VERSION_, '1.5', '>')
								? (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice' ? (int)$this->ps_cart->id_address_invoice : (int)$product['id_address_delivery'])
								: ((int)($this->ps_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) ? (int)($this->ps_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) : null);
				if (!Address::addressExists($address_id)) $address_id = null;			
				$product_discount = Product::getPriceStatic((int)$product['id_product'],
						true,
						isset($product['id_product_attribute']) ? (int)$product['id_product_attribute'] : null,
						2,
						null,
						true,
						true,
						(int)$product['cart_quantity'],
						false,
						((int)$this->ps_cart->id_customer ? (int)$this->ps_cart->id_customer : null),
						(int)$this->ps_cart->id,
						((int)$address_id ? (int)$address_id : null));
			}
			
			$this->cart->items_def[$productId]['product'] = $productId;
			$master_qty = 0;
			if(!empty($product['customization_quantity'])) {
				foreach($customizedData[$product['id_product']][$product['id_product_attribute']][$product['id_address_delivery']] AS $id_customization=>$customization) {
					$master_qty += $customization['quantity'];
					$this->cart->items[] = array(
						'key'=>$product['id_product'].':'.$product['id_product_attribute'].':'.$product['id_customization'],
						'product_id' => $productId,
						'attribute_id' => $product['id_product_attribute'],
						'customization_id' => $id_customization,
						'product_price' => $product['price_wt'],
						'product_price_notax' => $product['price'],
						'product_price_tax' => $product['price_wt'] - $product['price'],
						'qty' => $customization['quantity'],
						'tax_rate' => ($product['price_wt']-$product['price'])/$product['price'],
						'on_sale' => $product_discount,
						'totaldiscount'=>0,
						'totaldiscount_notax'=>0,
						'total_price_notax_reduction_amount'=>0,
						'product'=>$product,
						'customization_datas'=>$customization['datas'],
					);
				}
			}
			
			if($master_qty<$product['quantity']) {
				$this->cart->items[] = array(
					'key'=>$product['id_product'].':'.$product['id_product_attribute'].':'.$product['id_customization'],
					'product_id' => $productId,
					'attribute_id' => $product['id_product_attribute'],
					'customization_id' => 0,
					'product_price' => $product['price_wt'],
					'product_price_notax' => $product['price'],
					'product_price_tax' => $product['price_wt'] - $product['price'],
					'qty' => $product['quantity']-$master_qty,
					'tax_rate' => ($product['price_wt']-$product['price'])/$product['price'],
					'on_sale' => $product_discount,
					'totaldiscount'=>0,
					'totaldiscount_notax'=>0,
					'total_price_notax_reduction_amount'=>0,
					'product'=>$product,
				);
			}
			$this->product_total += $product['quantity'] * $product['price_wt'];
			$this->product_qty += $product['quantity'];
		}	
		
		
		if (empty($this->cart->items))
			return false;
		
		foreach ($this->cart->items as $k => $r)
		{
			$this->cart->items[$k]['totaldiscount'] = 0;
			$this->cart->items[$k]['totaldiscount_notax'] = 0;
			$this->cart->items[$k]['totaldiscount_tax'] = 0;
			$this->cart->items[$k]['coupons'] = array();
		}
		
		$this->cart->shipping = new stdClass;
		$this->cart->shipping->shipping_id = $this->ps_cart->id_carrier;
		$this->cart->shipping->total = $this->getshippingcosts();
		$this->cart->shipping->total_notax = $this->getshippingcosts(false);
		$this->cart->shipping->total_tax = $this->cart->shipping->total - $this->cart->shipping->total_notax;
		$this->cart->shipping->orig_total = $this->cart->shipping->total;
		$this->cart->shipping->totaldiscount = 0;
		$this->cart->shipping->totaldiscount_notax = 0;
		$this->cart->shipping->totaldiscount_tax = 0;
		$this->cart->shipping->coupons = array();
		
		
	}
	
	public function buyxy_addtocart(&$coupon_row, $products_x_count, $products_x_list, $asset2list)
	{
		$potential_items = array();
		$coupon_row->params->asset1_qty = (int)$coupon_row->params->asset1_qty;
		$coupon_row->params->asset2_qty = (int)$coupon_row->params->asset2_qty;
		$products_y_list = array();
		$products_y_count = 0;
		if ($coupon_row->params->asset2_mode == 'include')
		{
			foreach ($coupon_row->cart_items as $row)
			{
				if (isset($asset2list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset2_type]]))
				{
					$products_y_count += $row['qty'];
					$products_y_list[$row['product_id']] = $row['product_id'];
				}
			}
		}
		elseif ($coupon_row->params->asset2_mode == 'exclude')
		{
			foreach ($coupon_row->cart_items as $row)
			{
				if (!isset($asset2list[@$coupon_row->cart_items_def[$row['product_id']][$coupon_row->params->asset2_type]]))
				{
					$products_y_count += $row['qty'];
					$products_y_list[$row['product_id']] = $row['product_id'];
				}
			}
		}


		
		$i = 0;
		foreach ($coupon_row->cart_items as $product_id => $row)
		{
			for ($j = 0; $j < $row['qty']; $j++)
			{
				if (!empty($coupon_row->params->product_match))
					$potential_items[$row['product_id']][$i] = $row['product_id'];
				else
					$potential_items[0][$i] = $row['product_id'];
				$i++;
			}
		}
					
		if (empty($potential_items)
		|| empty($coupon_row->params->asset1_qty) && $coupon_row->params->asset1_qty > 0
		|| empty($coupon_row->params->asset2_qty) && $coupon_row->params->asset2_qty > 0) return;
			
				
		if (!empty($coupon_row->params->max_discount_qty));
				
		$adding = array();			
		$used_max_discount_qty = 0;
		foreach ($potential_items as $pindex => $current_potential_item)
		{
			$t_products_x_count = !empty($coupon_row->params->product_match) ? count($current_potential_item) : $products_x_count;
			$t_products_y_count = 0;
			while ($t_products_x_count > 0)
			{
				if (!empty($coupon_row->params->max_discount_qty) && $used_max_discount_qty >= $coupon_row->params->max_discount_qty) break;
				for ($i = 0; $i < $coupon_row->params->asset1_qty; $i++)
				{
					$is_unset = false;
					foreach ($current_potential_item as $ppindex => $product_id)
					{
						if ($t_products_x_count <= 0) break 3;
						if (isset($products_x_list[$product_id]))
						{
							$is_unset = true;
							unset($current_potential_item[$ppindex]);
							$t_products_x_count--;
							break;
						}
					}
					if (!$is_unset) break 2;
				}
				
				$used_max_discount_qty++;
				
				for ($i = 0; $i < $coupon_row->params->asset2_qty; $i++)
				{
					$isfound_ppindex = -1;
					foreach ($current_potential_item as $ppindex => $product_id)
					{
						if (isset($products_y_list[$product_id]))
						{
							$isfound_ppindex = $ppindex;
							unset($current_potential_item[$ppindex]);
							break;
						}
					}
					if ($isfound_ppindex == -1) $t_products_y_count += 1;
				}
			}

			if (empty($t_products_y_count) || $t_products_y_count < 0) continue;
			
			@$adding[$pindex] += $t_products_y_count;

			if (!empty($coupon_row->params->max_discount_qty) && $used_max_discount_qty >= $coupon_row->params->max_discount_qty) break;
		}
		
		if (empty($adding)) return;
		

		foreach ($adding as $item_id => $qty)
		{
			if (!empty($item_id)) $this->add_to_cart($item_id, $qty);
			else
			{
				$product_id = $this->buyxy_getproduct($coupon_row->params->asset2_mode, $coupon_row->params->asset2_type, $asset2list);
				if (!empty($product_id)) $this->add_to_cart($product_id, $qty);
			}
		}
		
		$this->define_cart_items();
		foreach ($this->cart->items as $k => $r)
		{
			$is_found = false;
			foreach ($coupon_row->cart_items as $k2 => $item)
			{
				if ($item['key'] == $r['key'])
				{
					$is_found = true;
					$coupon_row->cart_items[$k2]['qty'] = $r['qty'];
					break;
				}
			}
			if (!$is_found)
			{
				$coupon_row->cart_items[] = $this->cart->items[$k];
				$coupon_row->cart_items_def[$r['product_id']]['product'] = $r['product_id'];
				if ($coupon_row->params->asset2_type != 'product') 
					$coupon_row->cart_items_def[$r['product_id']][$coupon_row->params->asset2_type] = $coupon_row->params->asset2_mode == 'include' ? current($asset2list) : - 1;
			}
		}
		
		return true;
					
	}
	
	public function buyxy_getproduct($mode, $type, $assetlist)
	{
		$the_product = 0;
		$ids = implode(',', $assetlist);
		if (empty($ids)) return;
		
		if ($mode == 'include')
		{
			if ($type == 'product') $the_product = current($assetlist);
			elseif ($type == 'category')
			{
				$sql = 'SELECT c.id_product FROM '._DB_PREFIX_.'category_product c JOIN '._DB_PREFIX_.'product p ON p.id_product=c.id_product WHERE p.active=1 AND c.id_category IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
			elseif ($type == 'manufacturer')
			{
				$sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE active=1 AND id_manufacturer IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
			elseif ($type == 'vendor')
			{
				$sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE active=1 AND id_supplier IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
		}
		elseif ($mode == 'exclude')
		{
			if ($type == 'product')
			{
				$sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE active=1 AND id_product NOT IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
			elseif ($type == 'category')
			{
				$sql = 'SELECT c.id_product FROM '._DB_PREFIX_.'category_product c JOIN '._DB_PREFIX_.'product p ON p.id_product=c.id_product WHERE p.active=1 AND c.id_category NOT IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
			elseif ($type == 'manufacturer')
			{
				$sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE active=1 AND id_manufacturer NOT IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
			elseif ($type == 'vendor')
			{
				$sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE active=1 AND id_supplier NOT IN ('.$ids.') LIMIT 1';
				$the_product = awoHelper::loadResult($sql);
			}
		}

		return $the_product;
	
	}
	
	
	public function add_to_cart($product_id, $qty)
	{
		global $cookie;

		$idProduct = (int)$product_id;
		$qty = (int)$qty;
		$idProductAttribute = 0;
		$customizationId = 0;
		if (empty($qty) || empty($idProduct)) return;
		
		
		foreach ($this->cart->items as $k => $r)
		{
			if ($r['product_id'] == $product_id)
			{
				$idProductAttribute = (int)$r['attribute_id'];
				$customizationId = (int)$r['customization_id'];
				break;
			}
		}
		if (empty($idProductAttribute))
			$idProductAttribute = Product::getDefaultAttribute((int)$idProduct, (int)$qty);
			

		$producToAdd = new Product((int)($idProduct), true, (int)($cookie->id_lang));
		if (!$producToAdd->id || !$producToAdd->active) return false;
		if (!empty($idProductAttribute) && !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) && !Attribute::checkAttributeQty((int)$idProductAttribute, (int)$qty)) return false;
		if (empty($idProductAttribute) && $producToAdd->hasAttributes()) return false;
		if (empty($idProductAttribute) && !$producToAdd->checkQty((int)$qty)) return false;
		if (!$producToAdd->hasAllRequiredCustomizableFields() && !$customizationId) return false;

		
		$updateQuantity = $this->ps_cart->updateQty((int)($qty), (int)($idProduct), (int)($idProductAttribute), $customizationId, 'up');
		if ($updateQuantity < 0 || !$updateQuantity) return false;
		

		//$this->reprocess = true;
		return true;
	}
	
	

	
	
	
	



	public function cleanup_coupon_code()
	{
	// function to remove coupon coupon_code from the database

		//$this->ps_cart = new Cart($this->id_cart);
		$user_id = $this->ps_cart->id_customer;
		
		$coupon_session = $this->coupon_session['coupon_data'];
		if (empty($coupon_session)) return null;
		$coupon_session = unserialize($coupon_session);
		

		$order_id = $this->id_order;
		$user_email = $this->customer->email;

		if (empty($order_id)) $order_id = 'NULL';
		$user_email = empty($user_email) ? 'NULL' : '"'.pSQL($user_email).'"';
		
		$children_coupons = $coupon_session['processed_coupons'];
		
		$entered_coupon_ids = array_keys($coupon_session['entered_coupon_ids']);
		$sql = 'SELECT id,num_of_uses_total,num_of_uses_percustomer,function_type,coupon_value FROM '._DB_PREFIX_.'awocoupon WHERE published=1 AND id IN ('.implode(',', $entered_coupon_ids).')';
		$rows = awoHelper::loadObjectList($sql);
		if (count($entered_coupon_ids) != count($rows)) return null;

		
		$coupon_ids = implode(',', array_keys($children_coupons));
		$sql = 'SELECT id,num_of_uses_total,num_of_uses_percustomer,function_type,coupon_value FROM '._DB_PREFIX_.'awocoupon WHERE published=1 AND id IN ('.$coupon_ids.')';
		$rows = awoHelper::loadObjectList($sql);
		
		if (empty($rows)) return null;
		
		// only process once per load
		static $is_already_processed = false;
		if($is_already_processed) return;
		$is_already_processed = true;

		
		$parents = array();
		
		$coupon_details = pSQL(json_encode($coupon_session));

		foreach ($rows as $coupon_row)
		{
		// coupon found
		
			// mark coupon used
			$parent_coupon_id = (int)$children_coupons[$coupon_row->id]['coupon_entered_id'];
			if ($parent_coupon_id != $coupon_row->id) $parents[] = $parent_coupon_id;
			$usedproducts = !empty($children_coupons[$coupon_row->id]['usedproducts']) 
							? $children_coupons[$coupon_row->id]['usedproducts'] 
							: 'NULL';
							
			$postfix = $children_coupons[$coupon_row->id]['is_discount_before_tax'] ? '_notax' : '';
			$shipping_discount = (float)$children_coupons[$coupon_row->id]['shipping_discount'.$postfix];
			$product_discount = (float)$children_coupons[$coupon_row->id]['product_discount'.$postfix];
			
			$product_discount = $this->convertPriceFull($product_discount, Currency::getCurrencyInstance((int)($this->ps_cart->id_currency)));
			$shipping_discount = $this->convertPriceFull($shipping_discount, Currency::getCurrencyInstance((int)($this->ps_cart->id_currency)));
			
			$sql = 'INSERT INTO '._DB_PREFIX_.'awocoupon_history (coupon_entered_id,coupon_id,user_id,user_email,coupon_discount,shipping_discount,order_id,productids,details)
				    VALUES ('.$parent_coupon_id.','.$coupon_row->id.','.$user_id.','.$user_email.','.$product_discount.','.$shipping_discount.',"'.$order_id.'","'.$usedproducts.'","'.$coupon_details.'")';
			Db::getInstance()->Execute($sql);
				
			if (!empty($user_id))
			{
				$giftcard_id = (int)awoHelper::loadResult('SELECT id FROM #__awocoupon_giftcert_order_code WHERE coupon_id='.$coupon_row->id.' AND (recipient_user_id IS NULL OR recipient_user_id=0)');
				if (!empty($giftcard_id))
				{
				// if purchased voucher transfer to recipient user account
					Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'awocoupon_giftcert_order_code SET recipient_user_id='.(int)$user_id.' WHERE id='.$giftcard_id);
				}
			}

			if ($coupon_row->function_type == 'giftcert')
			{
			// gift certificate
				$sql = 'SELECT SUM(coupon_discount+shipping_discount) as total FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id='.$coupon_row->id.' GROUP BY coupon_id';
				$total = awoHelper::loadResult($sql);
				if (!empty($total) && $total >= $coupon_row->coupon_value)
				{
				// credits maxed out
					Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'awocoupon SET published=-1 WHERE id='.$coupon_row->id);
				}
			}
			if (!empty($coupon_row->num_of_uses_total))
			{
			// limited amount of uses so can be removed
				$sql = 'SELECT COUNT(id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_id='.$coupon_row->id.' GROUP BY coupon_id';
				$num = awoHelper::loadResult($sql);
				if (!empty($num) && $num >= $coupon_row->num_of_uses_total)
				{
				// already used max number of times
					Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'awocoupon SET published=-1 WHERE id='.$coupon_row->id);
				}
			}
		}


		foreach ($parents as $parent_coupon_id)
		{
			$sql = 'SELECT id,num_of_uses_total,num_of_uses_percustomer,function_type FROM '._DB_PREFIX_.'awocoupon WHERE published=1 AND id='.$parent_coupon_id;
			$parent_row = (object)Db::getInstance()->getRow($sql);
			if (!empty($parent_row) && $parent_row->function_type == 'parent')
			{
				if (!empty($parent_row->num_of_uses_total))
				{
				// limited amount of uses so can be removed
					$sql = 'SELECT COUNT(DISTINCT order_id) FROM '._DB_PREFIX_.'awocoupon_history WHERE coupon_entered_id='.$parent_row->id.' GROUP BY coupon_entered_id';
					$num = awoHelper::loadResult($sql);
					if (!empty($num) && $num >= $parent_row->num_of_uses_total)
					{
					// already used max number of times
						$sql = 'UPDATE '._DB_PREFIX_.'awocoupon SET published=-1 WHERE id='.$parent_row->id;
						Db::getInstance()->Execute($sql);
					}
				}
			}
		}
		
		//$this->initialize_coupon(); # do not delete data from awocoupon_cart, can be used later after order creation


		return true;
	}


	public static function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null)
	{
		if ($currency_from === $currency_to)
			return $amount;

		if ($currency_from === null)
			$currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		if ($currency_to === null)
			$currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT'))
		{
			$conversion_rate = ($currency_to->conversion_rate == 0 ? 1 : $currency_to->conversion_rate);
			$amount *= $conversion_rate;
		}
		else
		{
			$conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
			// Convert amount to default currency (using the old currency rate)
			$amount = Tools::ps_round($amount / $conversion_rate, 2);
			// Convert to new currency
			$conversion_rate = ($currency_to->conversion_rate == 0 ? 1 : $currency_to->conversion_rate);
			$amount *= $conversion_rate;
		}
		return Tools::ps_round($amount, 2);
	}
	
	
	public function getshippingcosts($use_tax = true)
	{
		$cost = 0;
		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$cost = empty($this->ps_cart->id_carrier)
						? $this->ps_cart->getTotalShippingCost(null, $use_tax)
						: $this->ps_cart->getPackageShippingCost($this->ps_cart->id_carrier, $use_tax, null, $this->ps_cart->getProducts());
		}
		else
			$cost = $this->ps_cart->getOrderShippingCost(null, $use_tax);
		
		return $cost;
	}
	
	
	private function couponvalidate_country(&$coupon_row)
	{
		if (empty($coupon_row->params->countrystate_mode)) return;
		if ($coupon_row->params->countrystate_mode != 'include' && $coupon_row->params->countrystate_mode != 'exclude') return;
				
		$tmp = awoHelper::loadObjectList('SELECT asset_id FROM #__awocoupon_asset1 WHERE asset_type="countrystate" AND coupon_id='.$coupon_row->id);
		foreach ($tmp as $tmp2) $coupon_row->countrystatelist[$tmp2->asset_id] = $tmp2->asset_id;
		if (empty($coupon_row->countrystatelist))
		{
			$tmp = awoHelper::loadObjectList('SELECT asset_id FROM #__awocoupon_asset1 WHERE asset_type="country" AND coupon_id='.$coupon_row->id);
			foreach ($tmp as $tmp2) $coupon_row->countrylist[$tmp2->asset_id] = $tmp2->asset_id;
		}
			
		$address = (object)array(
			'state_id'=>0,
			'state_name'=>'',
			'country_id'=>0,
			'country_name'=>'',
		);
		if (!empty($this->ps_cart->id_address_invoice))
		{
			$address_obj = new Address($this->ps_cart->id_address_invoice);
			if (!empty($address_obj->id_customer))
			{
				$address->state_id = $address_obj->id_state;
				$address->state_name = '';
				$address->country_id = $address_obj->id_country;
				$address->country_name = $address_obj->country;
			}
		}
		$coupon_row->customer->address = $address;
		
		if (!empty($coupon_row->countrystatelist))
		{
			if (empty($coupon_row->customer->address->state_id))
			{
			// not on  list
				return 'errCountryStateInclude';
			}
			if ($coupon_row->params->countrystate_mode == 'include')
			{
				if (!in_array($coupon_row->customer->address->state_id, $coupon_row->countrystatelist))
				{
				// not on  list
					return 'errCountryStateInclude';
				}
			}
			elseif ($coupon_row->params->countrystate_mode == 'exclude')
			{
				if (in_array($coupon_row->customer->address->state_id, $coupon_row->countrystatelist))
				{
				// on list
					return 'errCountryStateExclude';
				}
			}
			
			return;
		}
		
		if (!empty($coupon_row->countrylist))
		{
			if (empty($coupon_row->customer->address->country_id))
			{
			// not on  list
				return 'errCountryInclude';
			}
			if ($coupon_row->params->countrystate_mode == 'include')
			{
				if (!in_array($coupon_row->customer->address->country_id, $coupon_row->countrylist))
				{
				// not on shopper group list
					return 'errCountryInclude';
				}
			}
			elseif ($coupon_row->params->countrystate_mode == 'exclude')
			{
				if (in_array($coupon_row->customer->address->country_id, $coupon_row->countrylist))
				{
				// not on shopper group list
					return 'errCountryExclude';
				}
			}
		}
	}

	private function array_iunique($array) { return array_intersect_key($array,array_unique(array_map('strtolower',$array))); }	
	private function in_arrayi($needle, $haystack) { return in_array(strtolower($needle), array_map('strtolower', $haystack)); }
	private function is_coupon_in_array($iscaseSensitive,$coupon_code,$array) {
		return (
			($iscaseSensitive && (in_array($coupon_code,$array)))
			||	(!$iscaseSensitive && ($this->in_arrayi($coupon_code,$array)))
			) ? true : false;
	}
	private function array_intersect_diff($type,$iscaseSensitive,$find,$haystack) {
		return
				$type=='intersect'
					? ( $iscaseSensitive  ? array_intersect($haystack, $find) : array_uintersect($haystack, $find, 'strcasecmp') )
					: ( $iscaseSensitive  ? array_diff($find, $haystack) : array_udiff($find, $haystack, 'strcasecmp') )
		;
	}

}
