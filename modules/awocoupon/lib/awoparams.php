<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';

class AwoParams
{
	var $params = null;

	public function __construct()
	{
		$this->params = awoHelper::loadObjectList('SELECT id,name,value FROM '._DB_PREFIX_.'awocoupon_config', 'name');
	}
	
	public function get($param, $default = '')
	{ 
		$value = isset($this->params[$param]->value) ? $this->params[$param]->value : '';
		return (empty($value) && ($value !== 0) && ($value !== '0')) ? $default : $value; 
	}
	
	public function set($key, $value = '')
	{
		if (!empty($key))
		{
			$dbvalue = (empty($value) && ($value !== 0) && ($value !== '0')) ? 'NULL' : '"'.pSQL($value).'"';
			$tmp = awoHelper::loadResult('SELECT name FROM '._DB_PREFIX_.'awocoupon_config WHERE name="'.$key.'"');
			$sql = empty($tmp)
						? 'INSERT INTO '._DB_PREFIX_.'awocoupon_config (name,value) VALUES ("'.$key.'",'.$dbvalue.')'
						: 'UPDATE '._DB_PREFIX_.'awocoupon_config SET value='.$dbvalue.' WHERE name="'.$key.'"';
			awoHelper::query($sql);
			
			if(!isset($this->params[$key])) $this->params[$key] = new stdClass;
			$this->params[$key]->value = (empty($value) && ($value !== 0) && ($value !== '0')) ? null : $value;
		}
	}


}
