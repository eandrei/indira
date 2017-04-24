<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

class AwoCouponInstaller {

	function __construct() {
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once(_PS_MODULE_DIR_.'awocoupon/awocoupon.php');
		$this->awocoupon = new AwoCoupon();
		
	}
	

	
	
	public function install($installer) {

		//INSERT INTO `ps_hook` SET `name`= 'AwoCouponView',`title`= 'Hook for viewing',`description`= 'Hook description';
		if (!$installer->registerHook(_PS_VERSION_ < '1.5' ? 'paymentConfirm' : 'actionPaymentConfirmation')) return false;
		if (!$installer->registerHook(_PS_VERSION_ < '1.5' ? 'customerAccount' : 'displayCustomerAccount')) return false;
		if (!$installer->registerHook(_PS_VERSION_ < '1.5' ? 'adminOrder' : 'displayAdminOrder')) return false;
		if (!$installer->registerHook(_PS_VERSION_ < '1.5' ? 'newOrder' : 'actionValidateOrder')) return false;
		if (!$installer->registerHook(_PS_VERSION_ < '1.5' ? 'cart' : 'actionCartSave')) return false;
		if (version_compare(_PS_VERSION_,'1.7','>=')) {
			if (!$installer->registerHook('actionCartSummary')) return false;
		}

		
		// database
		$sql_file = _PS_MODULE_DIR_.'awocoupon/lib/sql/mysql.install.sql';
		if ((!file_exists($sql_file)) || (!$sql = file_get_contents($sql_file))) return false;
		$sql = str_replace('#__', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach($sql AS $k => $query) if (!empty($query)) Db::getInstance()->execute(trim($query));

		
		
		// admin tab
		$result = Db::getInstance()->getRow('SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name="AdminAwoCoupon"');
		if (!$result) {
			// id_tab
			$id_parent = _PS_VERSION_ < '1.5' ? 4 : 12;
			if(_PS_VERSION_>='1.7') $id_parent = 9;

			/*tab install */
			$result = Db::getInstance()->getRow('SELECT position FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent` = '.(int)$id_parent.' ORDER BY `'. _DB_PREFIX_ .'tab`.`position` DESC');
			$pos = (isset($result['position'])) ? $result['position'] + 1 : 0;

			Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'tab (id_parent, class_name, position, module) VALUES('.(int)$id_parent.', "AdminAwoCoupon",  "'.(int)($pos).'", "awocoupon")');
			$id_tab = Db::getInstance()->Insert_ID();

			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
				Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'tab_lang (id_lang, id_tab, name) VALUES("'.(int)($language['id_lang']).'", "'.(int)($id_tab).'", "AwoCoupon")');

			if (!Tab::initAccess($id_tab)) return false;

		}
		
		
		// install overrides and files needed
		if(version_compare(_PS_VERSION_,'1.5','<')) {
		
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/Cart', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override14/classes/Cart.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/Discount', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override14/classes/Discount.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/Order', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override14/classes/Order.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/controllers/ParentOrderController', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override14/controllers/ParentOrderController.php');
						
			copy($myopath.'/AdminAwoCoupon.php',_PS_MODULE_DIR_.'awocoupon/AdminAwoCoupon.php');
		}
		elseif(version_compare(_PS_VERSION_,'1.7','<')) {

			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/Cart', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override15/classes/Cart.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/CartRule', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override15/classes/CartRule.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/order/Order', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override15/classes/order/Order.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/controllers/front/ParentOrderController', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override15/controllers/front/ParentOrderController.php');

		}
		else {
		
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/Cart', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override17/classes/Cart.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/CartRule', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override17/classes/CartRule.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/classes/order/Order', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override17/classes/order/Order.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/controllers/front/CartController', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override17/controllers/front/CartController.php');
			$this->installOverride(_PS_MODULE_DIR_.'../override/controllers/front/OrderController', _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override17/controllers/front/OrderController.php');
			
		}
				

		Configuration::updateValue('AWOCOUPON_VERSION', $installer->version); //set awocoupon version
		Configuration::updateValue(_PS_VERSION_ < '1.5' ? 'PS_VOUCHERS' : 'PS_CART_RULE_FEATURE_ACTIVE', '1'); // enable vouchers
		
		
		return true;
	}

	public function uninstall($installer) {

		// Tab uninstall
		$result = Db::getInstance()->getRow('SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name="AdminAwoCoupon"');
		if ($result) {
			$id_tab = (int)$result['id_tab'];
			if (!empty($id_tab)) {
				Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'tab WHERE id_tab = '.(int)($id_tab));
				Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'tab_lang WHERE id_tab = '.(int)($id_tab));
				Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'access WHERE id_tab = '.(int)($id_tab));
			}
		}
		

		Configuration::deleteByName('AWOCOUPON_VERSION');

		
		// remove overrides 
		if(version_compare(_PS_VERSION_,'1.5','<')) {
			
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/Cart');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/Discount');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/Order');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/controllers/ParentOrderController');
			
		}
		elseif(version_compare(_PS_VERSION_,'1.7','<')) {
		
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/Cart');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/CartRule');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/order/Order');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/controllers/front/ParentOrderController');
			
		}
		else {
		
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/Cart');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/CartRule');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/classes/order/Order');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/controllers/front/CartController');
			$this->uninstallOverride(_PS_MODULE_DIR_.'../override/controllers/front/OrderController');
			
		}
		
		
		if (Tools::getValue('keepDatabase')) return true;

		// Drop databases
		Db::getInstance()->execute('
				DROP TABLE  '._DB_PREFIX_ .'awocoupon,
							'._DB_PREFIX_ .'awocoupon_asset1,
							'._DB_PREFIX_ .'awocoupon_asset2,
							'._DB_PREFIX_ .'awocoupon_cart,
							'._DB_PREFIX_ .'awocoupon_config,
							'._DB_PREFIX_ .'awocoupon_giftcert_code,
							'._DB_PREFIX_ .'awocoupon_giftcert_order,
							'._DB_PREFIX_ .'awocoupon_giftcert_product,
							'._DB_PREFIX_ .'awocoupon_history,
							'._DB_PREFIX_ .'awocoupon_profile,
							'._DB_PREFIX_ .'awocoupon_user,
							'._DB_PREFIX_ .'awocoupon_usergroup,
							'._DB_PREFIX_ .'awocoupon_license,
							'._DB_PREFIX_ .'awocoupon_profile_lang,
							'._DB_PREFIX_ .'awocoupon_shop'
		);
		

		return true;
	}


	public function upgrade($old_version) {
		global $smarty;
		


		$this->remove_ps14controller();

		//Manage Database Upgrades
		$dbupgrades = $version_array = array();
		$iup = -1;
		
		$iup++;
		$version_array['1.0.0'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.0";

		$iup++;
		$version_array['1.1.0'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.1";

		$iup++;
		$version_array['1.1.1'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.2";
		
		
		$iup++;
		$version_array['1.1.2'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.3";
		
		$iup++;
		$version_array['1.1.3'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.4";
		
		$iup++;
		$version_array['1.1.4'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_history ADD COLUMN user_email VARCHAR(255) AFTER user_id;';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.5";
		
	

		$iup++;
		$version_array['1.1.5'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.6";


		$iup++;
		$version_array['1.1.6'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_shop ( `coupon_id` int(10) unsigned NOT NULL, `id_shop` int(10) unsigned NOT NULL, PRIMARY KEY (`coupon_id`,`id_shop`));';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.7";

		$iup++;
		$version_array['1.1.7'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.8";

		
		$iup++;
		$version_array['1.1.8'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_profile_lang ( `profile_id` int(16) NOT NULL, `id_lang` int(16) NOT NULL, `title` VARCHAR(255), `email_subject` VARCHAR(255), `email_body` TEXT, PRIMARY KEY  (`profile_id`,`id_lang`) )';
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_119';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.1.9";

		
		$iup++;
		$version_array['1.1.9'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.0";

		
		$iup++;
		$version_array['1.2.0'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon ADD COLUMN passcode VARCHAR(10) AFTER coupon_code;';
		$dbupgrades[$iup]['updates'][] = 'UPDATE #__awocoupon SET passcode=SUBSTRING(MD5(CONCAT(UNIX_TIMESTAMP(),FLOOR(1+RAND()*1000),coupon_code)),1,6);';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_profile_lang ADD COLUMN pdf_header TEXT;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_profile_lang ADD COLUMN pdf_body TEXT;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_profile_lang ADD COLUMN pdf_footer TEXT;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_profile ADD COLUMN is_pdf TINYINT(1) AFTER freetext3_config;';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.1";
		
		

		$iup++;
		$version_array['1.2.1'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_history ADD COLUMN `details` TEXT;';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.2";
		
		
		$iup++;
		$version_array['1.2.2'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_image ( `coupon_id` INT NOT NULL, `user_id` INT NOT NULL, `filename` varchar(255), PRIMARY KEY  (`coupon_id`) )';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_giftcert_order ADD COLUMN user_id INT NOT NULL AFTER order_id;';
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_123';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.3";

		$iup++;
		$version_array['1.2.3'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.4";

		$iup++;
		$version_array['1.2.4'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_125';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.5";

		$iup++;
		$version_array['1.2.5'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_giftcert_order DROP PRIMARY KEY;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_giftcert_order ADD COLUMN id int(16) NOT NULL PRIMARY KEY auto_increment AFTER order_id;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_giftcert_order MODIFY order_id INT(16) NOT NULL AFTER Id;';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_giftcert_order ADD UNIQUE INDEX (order_id);';
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_giftcert_order_code (`id` int(16) NOT NULL auto_increment,`giftcert_order_id` int(16) NOT NULL,`order_item_id` int(16) NOT NULL,`product_id` int(16) NOT NULL,`coupon_id` int(16) NOT NULL,`code` VARCHAR(255) NOT NULL,`recipient_user_id` INT,PRIMARY KEY  (`id`));';
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_126';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.6";

		$iup++;
		$version_array['1.2.6'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_lang (`id` int(16) NOT NULL auto_increment,`elem_id` int(16) NOT NULL,`id_lang` int(16) NOT NULL,`text` TEXT,PRIMARY KEY  (`id`),UNIQUE KEY  (`elem_id`,`id_lang`));';
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_127';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.7";

		
		$iup++;
		$version_array['1.2.7'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon_asset1 MODIFY asset_type enum("coupon","product","category","manufacturer","vendor","shipping","country","countrystate") NOT NULL;';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.8";


		$iup++;
		$version_array['1.2.8'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'] = array();
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_129';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.2.9";

		
		$iup++;
		$version_array['1.2.8'] = 1;
		$version_array['1.2.9'] = 1;
		$version_array['1.3.0'] = 1;
		$version_array['1.3.1'] = 1;
		$version_array['1.3.2'] = 1;
		$version_array['1.3.3'] = 1;
		$version_array['1.3.4'] = 1;
		$version_array['1.3.5'] = 1;
		$version_array['1.3.6'] = 1;
		$dbupgrades[$iup]['versions']	=	$version_array;
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_tag (`coupon_id` int(16) NOT NULL,`tag` VARCHAR(255) NOT NULL,PRIMARY KEY  (`coupon_id`,`tag`));';
		$dbupgrades[$iup]['updates'][] = 'CREATE TABLE IF NOT EXISTS #__awocoupon_auto (`id` int(16) NOT NULL auto_increment,`coupon_id` varchar(32) NOT NULL default "",`ordering` INT NULL,`published` TINYINT NOT NULL DEFAULT 1,PRIMARY KEY  (`id`));';
		$dbupgrades[$iup]['updates'][] = 'ALTER TABLE #__awocoupon ADD COLUMN description VARCHAR(255) AFTER published;';
		$dbupgrades[$iup]['functions_after'][] = 'UPGRADE_137';
		$dbupgrades[$iup]['message'] = "AwoCoupon Pro 1.3.7";

		//Apply Upgrades
		foreach ($dbupgrades AS $dbupdate) {
			if(isset($dbupdate['versions'][$old_version])) {
			
				//if(!empty($dbupdate['functions_before'] )) {
				//	foreach($dbupdate['functions_before'] as $function) {
				//		call_user_func(array($this,$function));
				//	}
				//}
				
				foreach( $dbupdate['updates'] as $query ) {
				
					$query = str_replace('#__', _DB_PREFIX_, $query);
					Db::getInstance()->execute($query);
				
				}
				
				if(!empty($dbupdate['functions_after'] )) {
					foreach($dbupdate['functions_after'] as $function) {
						call_user_func(array($this,$function));
					}
				}
				
				//Upgrade was successful
				echo "<div>Database Updates for ".$dbupdate['message'].": <font color=green>Upgrade Applied Successfully.</font></div>";			
			} 
		}






		
		
		
		// clean up
		Configuration::updateValue('AWOCOUPON_VERSION', $this->awocoupon->version);
		
		// clear compiled templates
		$dir = _PS_MODULE_DIR_.'awocoupon/ps14/admin/tpl';
		$files = scandir($dir);
		foreach($files as $file) {
			if(substr(strtolower($file),-4) == '.tpl') {
				$smarty->clearCompiledTemplate($file);
			}
		}
		
		// clear db cache
		awoHelper::query('DELETE FROM '._DB_PREFIX_.'awocoupon_config WHERE name LIKE "cache_%"');

	}
	
	
	private function installOverride($file, $newFile) {
	
		$file_backup = $file.'.awocoupon.bak.php';
		$file_override = $file.'.php';

		$is_override = true;
		if(file_exists($file_override)) $is_override = rename($file_override, $file_backup);
		if($is_override) copy($newFile,$file_override);
		
		return;



		if(_PS_VERSION_ < '1.5' ) {
			$opath = _PS_MODULE_DIR_.'../override';
			$myopath = _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override14';
			$override_cart = $override_discount = $override_order = $override_control = true;
			if(file_exists($opath.'/classes/Cart.php')) $override_cart = rename($opath.'/classes/Cart.php', $opath.'/classes/Cart.awocoupon.bak.php');
			if(file_exists($opath.'/classes/Discount.php')) $override_discount = rename($opath.'/classes/Discount.php', $opath.'/classes/Discount.awocoupon.bak.php');
			if(file_exists($opath.'/classes/Order.php')) $override_order = rename($opath.'/classes/Order.php', $opath.'/classes/Order.awocoupon.bak.php');
			if(file_exists($opath.'/controllers/ParentOrderController.php')) $override_control = rename($opath.'/controllers/ParentOrderController.php', $opath.'/controllers/ParentOrderController.awocoupon.bak.php');
			
			if($override_cart) copy($myopath.'/classes/Cart.php',$opath.'/classes/Cart.php');
			if($override_discount) copy($myopath.'/classes/Discount.php',$opath.'/classes/Discount.php');
			if($override_order) copy($myopath.'/classes/Order.php',$opath.'/classes/Order.php');
			if($override_control) copy($myopath.'/controllers/ParentOrderController.php',$opath.'/controllers/ParentOrderController.php');
			
			copy($myopath.'/AdminAwoCoupon.php',_PS_MODULE_DIR_.'awocoupon/AdminAwoCoupon.php');
		}
		else {
			$opath = _PS_MODULE_DIR_.'../override';
			$myopath = _PS_MODULE_DIR_.'awocoupon/lib/prestashop/override15';
			$override_cart = $override_cartrule = $override_order = $override_control = true;
			if(file_exists($opath.'/classes/Cart.php')) $override_cart = rename($opath.'/classes/Cart.php', $opath.'/classes/Cart.awocoupon.bak.php');
			if(file_exists($opath.'/classes/CartRule.php')) $override_cartrule = rename($opath.'/classes/CartRule.php', $opath.'/classes/CartRule.awocoupon.bak.php');
			if(file_exists($opath.'/classes/order/Order.php')) $override_order = rename($opath.'/classes/order/Order.php', $opath.'/classes/order/Order.awocoupon.bak.php');
			if(file_exists($opath.'/controllers/front/ParentOrderController.php')) $override_control = rename($opath.'/controllers/front/ParentOrderController.php', $opath.'/controllers/front/ParentOrderController.awocoupon.bak.php');
			
			if($override_cart) copy($myopath.'/classes/Cart.php',$opath.'/classes/Cart.php');
			if($override_cartrule) copy($myopath.'/classes/CartRule.php',$opath.'/classes/CartRule.php');
			if($override_order) copy($myopath.'/classes/order/Order.php',$opath.'/classes/order/Order.php');
			if($override_control) copy($myopath.'/controllers/front/ParentOrderController.php',$opath.'/controllers/front/ParentOrderController.php');
		}
	}
	
	private function uninstallOverride($file) {
	
		$file_backup = $file.'.awocoupon.bak.php';
		$file_override = $file.'.php';
	
		$remove_file = false;
		if(file_exists($file_backup)) $remove_file = true;
		elseif(file_exists($file_override)) {
			$data = file_get_contents($file_backup);
			if(strpos($data,'http://awodev.com')!==false) $remove_file = true;
		}
		
		if($remove_file==true) {
			unlink($file_override);
			@rename($file_backup, $file_override);
		}
		
		return;
	
	
		if(_PS_VERSION_ < '1.5' ) {
			$opath = _PS_MODULE_DIR_.'../override';
			$remove_cart = $remove_discount = $remove_order = $remove_control = false;
			if(file_exists($opath.'/classes/Cart.awocoupon.bak.php')) $remove_cart = true;
			elseif(file_exists($opath.'/classes/Cart.php')) {
				$data = file_get_contents($opath.'/classes/Cart.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_cart = true;
			}
			if(file_exists($opath.'/classes/Discount.awocoupon.bak.php')) $remove_discount = true;
			elseif(file_exists($opath.'/classes/Discount.php')) {
				$data = file_get_contents($opath.'/classes/Discount.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_discount = true;
			}
			if(file_exists($opath.'/classes/Order.awocoupon.bak.php')) $remove_order = true;
			elseif(file_exists($opath.'/classes/Order.php')) {
				$data = file_get_contents($opath.'/classes/Order.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_order = true;
			}
			if(file_exists($opath.'/controllers/ParentOrderController.awocoupon.bak.php')) $remove_control = true;
			elseif(file_exists($opath.'/controllers/ParentOrderController.php')) {
				$data = file_get_contents($opath.'/controllers/ParentOrderController.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_control = true;
			}
			
			if($remove_cart==true) { unlink($opath.'/classes/Cart.php');	@rename($opath.'/classes/Cart.awocoupon.bak.php', $opath.'/classes/Cart.php'); }
			if($remove_discount==true) { unlink($opath.'/classes/Discount.php');	@rename($opath.'/classes/Discount.awocoupon.bak.php', $opath.'/classes/Discount.php'); }
			if($remove_order==true) { unlink($opath.'/classes/Order.php');	@rename($opath.'/classes/Order.awocoupon.bak.php', $opath.'/classes/Order.php'); }
			if($remove_control==true) { unlink($opath.'/controllers/ParentOrderController.php');	@rename($opath.'/controllers/ParentOrderController.awocoupon.bak.php', $opath.'/controllers/ParentOrderController.php'); }
		}
		else {
			$opath = _PS_MODULE_DIR_.'../override';
			$remove_cart = $remove_cartrule = $remove_order = $remove_control = false;
			if(file_exists($opath.'/classes/Cart.awocoupon.bak.php')) $remove_cart = true;
			elseif(file_exists($opath.'/classes/Cart.php')) {
				$data = file_get_contents($opath.'/classes/Cart.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_cart = true;
			}
			if(file_exists($opath.'/classes/CartRule.awocoupon.bak.php')) $remove_cartrule = true;
			elseif(file_exists($opath.'/classes/CartRule.php')) {
				$data = file_get_contents($opath.'/classes/CartRule.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_cartrule = true;
			}
			if(file_exists($opath.'/classes/order/Order.awocoupon.bak.php')) $remove_order = true;
			elseif(file_exists($opath.'/classes/order/Order.php')) {
				$data = file_get_contents($opath.'/classes/order/Order.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_order = true;
			}
			if(file_exists($opath.'/controllers/front/ParentOrderController.awocoupon.bak.php')) $remove_control = true;
			elseif(file_exists($opath.'/controllers/front/ParentOrderController.php')) {
				$data = file_get_contents($opath.'/controllers/front/ParentOrderController.awocoupon.bak.php');
				if(strpos($data,'http://awodev.com')!==false) $remove_control = true;
			}
			
			if($remove_cart==true) { unlink($opath.'/classes/Cart.php');	@rename($opath.'/classes/Cart.awocoupon.bak.php', $opath.'/classes/Cart.php'); }
			if($remove_cartrule==true) { unlink($opath.'/classes/CartRule.php');	@rename($opath.'/classes/CartRule.awocoupon.bak.php', $opath.'/classes/CartRule.php'); }
			if($remove_order==true) { unlink($opath.'/classes/order/Order.php');	@rename($opath.'/classes/order/Order.awocoupon.bak.php', $opath.'/classes/order/Order.php'); }
			if($remove_control==true) { unlink($opath.'/controllers/front/ParentOrderController.php');	@rename($opath.'/controllers/front/ParentOrderController.awocoupon.bak.php', $opath.'/controllers/front/ParentOrderController.php'); }
		}
		
	}
	
	private function remove_ps14controller() {
		if(_PS_VERSION_ >= '1.5' ) {
			$file = _PS_MODULE_DIR_.'awocoupon/AdminAwoCoupon.php';
			if(file_exists($file)) unlink($file);
		}
	}
	
	private function UPGRADE_119() {
		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		$sql = 'INSERT INTO #__awocoupon_profile_lang (profile_id,id_lang,title,email_subject,email_body)
					SELECT id,'.$id_lang.',title,email_subject,email_body FROM #__awocoupon_profile';
		awoHelper::query($sql);
		
	}

	private function UPGRADE_123() {
		$file = _PS_MODULE_DIR_.'awocoupon/views/templates/admin/awocoupon/helpers/list/list_header.tpl';
		@rename($file, dirname($file).'/_list_header.tpl');
		@unlink($file);
		@unlink(dirname($file).'/_list_header.tpl');

		$this->awocoupon->registerHook(_PS_VERSION_ < '1.5' ? 'customerAccount' : 'displayCustomerAccount');
	}
	
	private function UPGRADE_125() {
		$this->awocoupon->registerHook(_PS_VERSION_ < '1.5' ? 'adminOrder' : 'displayAdminOrder');
	}
	
	private function UPGRADE_126() {
		
		$rows = awohelper::loadObjectList('SELECT * FROM #__awocoupon_giftcert_order');
	
		$insert_sql = array();
		foreach($rows as $k=>$row) {
			$codes = array();
			@parse_str($row->codes,$codes);
			if(empty($codes[0]['c'])) {
				continue;
			}
		
			foreach($codes as $code) {
				$insert_sql[] = '('.(int)$row->id.','.(int)@$code['i'].','.(int)@$code['p'].',0,"'.awohelper::escape($code['c']).'")';
			}
		}
		if(!empty($insert_sql)) {
			$insert_sql_array = array_chunk($insert_sql, 100);
			foreach ($insert_sql_array as $insert_sql) {
				awohelper::query('INSERT INTO #__awocoupon_giftcert_order_code (giftcert_order_id,order_item_id,product_id,coupon_id,code) VALUES '.implode(',',$insert_sql));
			}
			awohelper::query('UPDATE #__awocoupon_giftcert_order_code go,#__awocoupon c SET go.coupon_id=c.id WHERE go.code=c.coupon_code');
		}
	}

	private function UPGRADE_127() {
		$elem_id = 1;
		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		if(empty($id_lang)) $id_lang = 1;
	
		$rows = awohelper::loadObjectList('SELECT id,name,value FROM #__awocoupon_config WHERE name like "err%"');
		foreach($rows as $row) {
			if(empty($row->value)) continue;
			
			awohelper::query('INSERT INTO #__awocoupon_lang (elem_id,id_lang,text) VALUES ('.$elem_id.','.$id_lang.',"'.awohelper::escape($row->value).'")');
			awohelper::query('UPDATE #__awocoupon_config SET value='.$elem_id.' WHERE id='.$row->id);
			$elem_id++;
		}
	}
	
	
	private function UPGRADE_129() {
		$this->awocoupon->registerHook(_PS_VERSION_ < '1.5' ? 'newOrder' : 'actionValidateOrder');
	}
	
	private function UPGRADE_137() {
		$this->awocoupon->registerHook(_PS_VERSION_ < '1.5' ? 'cart' : 'actionCartSave');
	}
	
}


