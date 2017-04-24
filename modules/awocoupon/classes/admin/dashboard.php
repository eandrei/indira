<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoCouponModelDashboard {
	var $_errors;
	
	public function __construct()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
		require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';
		$this->awocoupon = new AwoCoupon();
	}

	/**
	 * Method to get general stats
	 **/
	public function getGeneralstats()
	{
		$_products = array();

		/*
		* Get total number of entries
		*/
		$_products['total'] = awoHelper::loadResult('SELECT count(id)  FROM '._DB_PREFIX_.'awocoupon');

		/*
		* Get total number of approved entries
		*/
		$current_date = date('Y-m-d H:i:s');
		$sql = 'SELECT count(id) 
				  FROM '._DB_PREFIX_.'awocoupon 
				 WHERE published=1 
				   AND ( ((startdate IS NULL OR startdate="") 	AND (expiration IS NULL OR expiration="")) OR
						 ((expiration IS NULL OR expiration="") AND startdate<="'.$current_date.'") OR
						 ((startdate IS NULL OR startdate="") 	AND expiration>="'.$current_date.'") OR
						 (startdate<="'.$current_date.'"		AND expiration>="'.$current_date.'")
					   )
				'; 
		$_products['active'] = awoHelper::loadResult($sql);
		
		$sql = 'SELECT count(id) 
				  FROM '._DB_PREFIX_.'awocoupon 
				 WHERE (published=-1  OR startdate>"'.$current_date.'" OR expiration<"'.$current_date.'")';
		$_products['inactive'] = awoHelper::loadResult($sql);
		
		$sql = 'SELECT count(id) 
				  FROM '._DB_PREFIX_.'awocoupon
				 WHERE published=-2'; 
		$_products['templates'] = awoHelper::loadResult($sql);
		
		return (object)$_products;
		
	}

	
	public function getVersionUpdate()
	{
		$path = 'sites/default/files/extstatus/awocouponps.xml';
		$domain = 'awodev.com';
		$url = 'http://'.$domain.'/'.$path;
		$data = '';
		$check = array();
		$check['connect'] = 0;
		$check['current_version'] = AwoCouponModelDashboard::getFullLocalVersion();

		//try to connect via cURL
		if (function_exists('curl_init') && function_exists('curl_exec'))
		{
			$ch = @curl_init();
			
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			//http code is greater than or equal to 300 ->fail
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			//timeout of 5s just in case
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
						
			$data = @curl_exec($ch);
						
			@curl_close($ch);
		}

		//try to connect via fsockopen
		if (function_exists('fsockopen') && $data == '')
		{

			$errno = 0;
			$errstr = '';

			//timeout handling: 5s for the socket and 5s for the stream = 10s
			$fsock = @fsockopen($domain, 80, $errno, $errstr, 5);
		
			if ($fsock)
			{
				@fputs($fsock, 'GET /'.$path." HTTP/1.1\r\n");
				@fputs($fsock, 'HOST: '.$domain."\r\n");
				@fputs($fsock, "Connection: close\r\n\r\n");
		
				//force stream timeout...
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);
				
				$get_info = false;
				while (!@feof($fsock))
				{
					if ($get_info)
						$data .= @fread($fsock, 1024);
					else
					{
						if (@fgets($fsock, 1024) == "\r\n")
							$get_info = true;
					}
				}        	
				@fclose($fsock);
				
				//need to check data cause http error codes aren't supported here
				if (!strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>'))
					$data = '';
			}
		}

		//try to connect via fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen') && $data == '')
		{
			//set socket timeout
			ini_set('default_socket_timeout', 5);
			
			$handle = @fopen ($url, 'r');
			
			//set stream timeout
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
			
			$data	= @fread($handle, 1000);
			
			@fclose($handle);
		}
						
		if ($data && strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>'))
		{
			preg_match('/\<version\>([^<]*).*?\<released\>([^<]*)/', $data, $matches);
			
			$check['version'] = $matches[1];
			$check['released'] = $matches[2];
			$check['connect'] 		= 1;
			$check['enabled'] 		= 1;
			$check['current'] 		= version_compare($check['current_version'], $check['version']);
		}
		
		return (object)$check;
	}
	public function getLocalBuild()
	{
		$versionString	= $this->getFullLocalVersion();
		$tmpArray		= explode('.', $versionString);
		
		if (isset($tmpArray[2]))
			return $tmpArray[2];
		
		// Unknown build number.
		return 0;
	}
	public function getLocalVersion()
	{
		$versionString	= $this->getFullLocalVersion();
		$tmpArray		= explode('.', $versionString);
		
		if (isset($tmpArray[0] ) && isset($tmpArray[1]))
			return doubleval($tmpArray[0].'.'.$tmpArray[1]); 

		return 0;
	}

	
	public function getFullLocalVersion()
	{
		return $this->awocoupon->version;
		
		/*$data = Tools::file_get_contents(_PS_MODULE_DIR_.'awocoupon/config.xml');
		preg_match('/\<version\>\<\!\[CDATA\[([^\]]*)/i',$data,$match);
		$version = empty($match[1]) ? '' : trim($match[1]);
		return $version;*/
	}

	public function getLicense()
	{
		$license = $website = $expiration = '';
		$rows = awoHelper::loadObjectList('SELECT id,value FROM '._DB_PREFIX_.'awocoupon_license WHERE id IN ("license", "expiration","website")');
		foreach ($rows as $row)
		{
			if ($row->id == 'license') $license = $row->value;
			elseif ($row->id == 'expiration') $expiration = $row->value;
			elseif ($row->id == 'website') $website = explode('|', $row->value);
		}
		return (object)array('l'=>$license,'url'=>!empty($website) ? current($website) : '', 'exp'=>empty($expiration) ? $expiration : date('Y-m-d H:i:s', $expiration));
	}
	

	public function deleteExpiredCoupons()
	{
		require_once _PS_MODULE_DIR_.'awocoupon/lib/awoparams.php';
		
		$params = new awoParams();
		$days_expired = $params->get('delete_expired', '');
		
		if (!empty($days_expired) && ctype_digit($days_expired))
		{
			$current_date = date('Y-m-d H:i:s', strtotime('-'.$days_expired.' days'));
			$list = awoHelper::loadObjectList('SELECT id FROM '._DB_PREFIX_.'awocoupon WHERE expiration<"'.$current_date.'"', 'id');

			if (!empty($list))
			{
				require_once _PS_MODULE_DIR_.'awocoupon/classes/admin/coupon.php';
				$model = new AwoCouponModelCoupon();
				$model->delete(array_keys($list));
			}
			
		}
	}

}
