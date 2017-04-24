<?php

if (!defined('_PS_VERSION_'))
	exit;
	
class EmagMarketplaceAPICall extends ObjectModel
{
	public $id;

	public $id_emagmp_api_call;
	public $date_created;
	public $resource;
	public $action;
	public $last_definition;
	public $message_out;
	public $message_in;
	public $status;
	public $date_sent;
	public $id_order;
	
	public $emagmp_api_url = null;
	public $emagmp_vendorcode = null;
	public $emagmp_vendorusername = null;
	public $emagmp_vendorpassword = null;
	
	public $data = null;
	public $message_in_json = null;
	
	public $module;

	/**
	* @see ObjectModel::$definition
	*/
	public static $definition = array(
		'table' => 'emagmp_api_calls',
		'primary' => 'id_emagmp_api_call',
		'fields' => array(
			'date_created' => array('type' => self::TYPE_DATE),
			'resource' => array('type' => self::TYPE_STRING),
			'action' => array('type' => self::TYPE_STRING),
			'last_definition' => array('type' => self::TYPE_HTML),
			'message_out' => array('type' => self::TYPE_HTML),
			'message_in' => array('type' => self::TYPE_HTML),
			'status' => array('type' => self::TYPE_STRING, 'default' => 'pending'),
			'date_sent' => array('type' => self::TYPE_DATE),
			'id_order' => array('type' => self::TYPE_INT)
		)
	);
	
	public function __construct($id = null)
	{
		parent::__construct($id);
		
		$this->emagmp_api_url = Configuration::get('EMAGMP_API_URL');
		$this->emagmp_vendorcode = Configuration::get('EMAGMP_VENDORCODE');
		$this->emagmp_vendorusername = Configuration::get('EMAGMP_VENDORUSERNAME');
		$this->emagmp_vendorpassword = Configuration::get('EMAGMP_VENDORPASSWORD');
		
		if (!$id)
		{
			$this->date_created = date('Y-m-d H:i:s');
			$this->data = array();
		}
		else
		{
			$this->data = unserialize($this->message_out);
		}

		$this->module = new EmagMarketplace();
	}
	
	public function execute()
	{
		$debug_info = array(
			'site' => Tools::getHttpHost(true).__PS_BASE_URI__,
			'platform' => 'PrestaShop',
			'version' => _PS_VERSION_,
			'extension_version' => $this->module->version,
			'others' => ''
		);
			
		$hash = sha1(http_build_query($this->data) . sha1($this->emagmp_vendorpassword));
		$requestData = array(
		    'code' => $this->emagmp_vendorcode,
		    'username' => $this->emagmp_vendorusername,
		    'data' => $this->data,
		    'hash' => $hash,
		    'debug_info' => $debug_info
		);

		$ch = curl_init();
		$url = $this->emagmp_api_url.'/'.$this->resource.'/'.$this->action;

		if ($this->resource == 'order' && $this->action == 'acknowledge')
			$url .= '/'.$this->data['id'];

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
		
		//$fp = fopen(dirname(__FILE__).'/../logs/call_result.txt', 'a');
		/*curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, $fp);*/
		
		$result = curl_exec($ch);
		
		$this->date_sent = date("Y-m-d H:i:s");

		/*ob_start();
		echo "\n\n---------------------------------------------------------------------------\n";
		print_r($this->data);
		print_r(curl_getinfo($ch, CURLINFO_HEADER_OUT));
		fwrite($fp, ob_get_contents());
		ob_end_clean();*/

		//fwrite($fp, "\n\n\n".$result);

		if (curl_errno($ch))
		{
			$this->message_in = curl_error($ch);
			$this->status = 'error';
		}
		else
		{
			$this->message_in = $result;
			$this->message_in_json = Tools::jsonDecode($result);
			
			if ($this->message_in_json->isError === false)
			{
				$this->status = 'success';
			}
			else
			{
				$this->status = 'error';
			}
		}
		
		curl_close($ch);
		
		//fclose($fp);
		
		// save last sent data for products and orders
		if ($this->status == 'success' && $this->action == 'save')
		{
			switch ($this->resource)
			{
				case 'product_offer':
					$definition_table_name = 'emagmp_product_combinations';
					$definition_table_primary_field = 'combination_id';
					break;
				case 'order':
					$definition_table_name = 'emagmp_order_history';
					$definition_table_primary_field = 'emag_order_id';
					break;
			}
			
			if ($definition_table_name && $definition_table_primary_field)
			{
				Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.$definition_table_name.'` SET
					last_definition = \''.pSQL($this->last_definition, true).'\'
					WHERE '.$definition_table_primary_field.' = '.(int)$this->data[0]['id'].'
				');
			}
		}
		
		// save last eMAG order definition
		if ($this->status == 'success' && $this->action == 'read' && $this->resource == 'order')
		{
			$definition_table_name = 'emagmp_order_history';
			$definition_table_primary_field = 'emag_order_id';
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.$definition_table_name.'` SET
				emag_definition = \''.pSQL(serialize($this->message_in_json->results[0]), true).'\'
				WHERE '.$definition_table_primary_field.' = '.(int)$this->data['id'].'
			');
		}
	}
	
	public function save($null_values = false, $autodate = true)
	{
		//$fp = fopen(dirname(__FILE__).'/../logs/call_queue.txt', 'a');
		ob_start();
		echo "\n\n---------------------------------------------------------------------------\n";
		print_r($this->data);
		echo $this->resource.'/'.$this->action."\n";
		//fwrite($fp, ob_get_contents());
		ob_end_clean();
		//fclose($fp);

		$this->message_out = serialize($this->data);
		return parent::save();
	}

}

?>