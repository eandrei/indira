<?php
ini_set('max_execution_time', 240);
session_start();
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class fancourier extends CarrierModule
{
	private $_html = '';

	private $_postErrors = array();

	public $url = '';

	public $_errors = array();

	private $api_num_version = '3.0';

//	private $_config = array(
//		'name' => 'FAN Courier',
//		'id_tax_rules_group' => 0,
//		'url' => 'http://www.selfawb.ro/order.php?order_id=@',
//		'active' => true,
//		'deleted' => 0,
//		'shipping_handling' => false,
//		'range_behavior' => 0,
//		'is_module' => true,
//		'delay' => 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h',
//		'id_zone' => 1,
//		'shipping_external'=> true,
//		'external_module_name'=> 'fancourier',
//		'need_range' => true
//		);

	function __construct()
	{
		$this->name = 'fancourier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.1';
		$this->author = 'FAN Courier';
		$this->limited_countries = array('ro');
		$this->module_key = '037ac61cb2db434848fd7498ab3ac215';

		parent::__construct ();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('FAN Courier');
		$this->description = $this->l('Modul FAN Courier - selfAWB pentru PrestaShop eCommerce.');
		$this->url = Tools::getProtocol().htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php';

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		if (self::isInstalled($this->name))
		{
			$warning = array();
			$fanCarrier = new Carrier(Configuration::get('FANCOURIER_CARRIER_ID_1'));
			if (Validate::isLoadedObject($fanCarrier))
			{
				if (!$this->checkZone((int)($fanCarrier->id)))
					$warning[] .= $this->l('\'Carrier Zone(s)\'').' ';
				if (!$this->checkGroup((int)($fanCarrier->id)))
					$warning[] .= $this->l('\'Carrier Group\'').' ';
				if (!$this->checkRange((int)($fanCarrier->id)))
					$warning[] .= $this->l('\'Carrier Range(s)\'').' ';
				if (!$this->checkDelivery((int)($fanCarrier->id)))
					$warning[] .= $this->l('\'Carrier price delivery\'').' ';
			}

			//Check config and display warning
			if (!Configuration::get('fancourier_username'))
                                $warning[] .= $this->l('\'Cont utilizator\'').' ';
			if (!Configuration::get('fancourier_password'))
				$warning[] .= $this->l('\'Parola\'').' ';
			if (!Configuration::get('fancourier_clientid'))
				$warning[] .= $this->l('\'Client ID\'').' ';

			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l(' trebuie configurate pentru functionarea modulului.').' ';
		}
	}

	public function install()
	{
		if (!parent::install() OR !Configuration::updateValue('fancourier_username', NULL) OR !Configuration::updateValue('fancourier_password', NULL) ||
				!Configuration::updateValue('fancourier_clientid', NULL) OR !$this->registerHook('extraCarrier') OR !$this->registerHook('AdminOrder') OR !$this->registerHook('updateCarrier') ||
				!$this->registerHook('newOrder') OR !$this->registerHook('paymentTop') OR !$this->registerHook('actionValidateOrder'))
			return false;

		//create config table in database
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fancourier_delivery_info` (
				  `id_cart` int(10) NOT NULL,
				  `price` decimal(8,2) NOT NULL,
				  `fan_order` varchar(32) NOT NULL,
				  PRIMARY KEY  (`id_cart`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		if(!Db::getInstance()->execute($sql))
			return false;
                
                    $config = array(
                            'name' => "FAN Courier",
                            'id_tax_rules_group' => 0,
                            'url' => 'http://www.selfawb.ro/order.php?order_id=@',
                            'active' => true,
                            'deleted' => 0,
                            'shipping_handling' => false,
                            'shipping_free_price' => false,
                            'shipping_free_weight' => false,
                            'range_behavior' => 0,
                            'delay' => array('fr' => 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h', 'en' => 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h', Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h'),
                            'id_zone' => 1,
                            'is_module' => true,
                            'shipping_external' => true,
                            'external_module_name' => 'fancourier',
                            'need_range' => true
                    );         
                    
//	private $_config = array(
//		'name' => 'FAN Courier',
//		'id_tax_rules_group' => 0,
//		'url' => 'http://www.selfawb.ro/order.php?order_id=@',
//		'active' => true,
//		'deleted' => 0,
//		'shipping_handling' => false,
//		'range_behavior' => 0,
//		'is_module' => true,
//		'delay' => 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h',
//		'id_zone' => 1,
//		'shipping_external'=> true,
//		'external_module_name'=> 'fancourier',
//		'need_range' => true
//		);
                    

		//add carrier in back office
		if(!$this->createFANCourierCarrier($config))
			return false;

		return true;
	}

	public function uninstall()
	{

                if (Validate::isConfigName('fancourier_enabled')) Configuration::deleteByName('fancourier_enabled');
                if (Validate::isConfigName('fancourier_username')) Configuration::deleteByName('fancourier_username');
                if (Validate::isConfigName('fancourier_username')) Configuration::deleteByName('fancourier_password');
                if (Validate::isConfigName('fancourier_clientid')) Configuration::deleteByName('fancourier_clientid');
                if (Validate::isConfigName('fancourier_parcel')) Configuration::deleteByName('fancourier_parcel');
                if (Validate::isConfigName('fancourier_labels')) Configuration::deleteByName('fancourier_labels');
                if (Validate::isConfigName('fancourier_ramburs')) Configuration::deleteByName('fancourier_ramburs');
                if (Validate::isConfigName('fancourier_content')) Configuration::deleteByName('fancourier_content');
                if (Validate::isConfigName('fancourier_serviciu')) Configuration::deleteByName('fancourier_serviciu');
                if (Validate::isConfigName('fancourier_paymentdest')) Configuration::deleteByName('fancourier_paymentdest');
                if (Validate::isConfigName('fancourier_paymentrbdest')) Configuration::deleteByName('fancourier_paymentrbdest');
                if (Validate::isConfigName('fancourier_payment0')) Configuration::deleteByName('fancourier_payment0');
                if (Validate::isConfigName('fancourier_min_gratuit')) Configuration::deleteByName('fancourier_min_gratuit');
                if (Validate::isConfigName('fancourier_suma_fixa')) Configuration::deleteByName('fancourier_suma_fixa');
                if (Validate::isConfigName('fancourier_comment')) Configuration::deleteByName('fancourier_comment');
				//boby 02.05.2014 afisare persoana de contact expeditor
				if (Validate::isConfigName('pers_contact_expeditor')) Configuration::deleteByName('pers_contact_expeditor');
				//end boby
				//boby 05.05.2014 deschidere la livrare
				if (Validate::isConfigName('deschidere_livrare')) Configuration::deleteByName('deschidere_livrare');
				//end boby
                if (Validate::isConfigName('fancourier_asigurare')) Configuration::deleteByName('fancourier_asigurare');
                if (Validate::isConfigName('fancourier_totalrb')) Configuration::deleteByName('fancourier_totalrb');
                if (Validate::isConfigName('fancourier_onlyadm')) Configuration::deleteByName('fancourier_onlyadm');
                if (Validate::isConfigName('fancourier_fara_tva')) Configuration::deleteByName('fancourier_fara_tva');
				//fara km suplimentari
				//if (Validate::isConfigName('fancourier_fara_km')) Configuration::deleteByName('fancourier_fara_km');
				//
				//doar km suplimentari
				if (Validate::isConfigName('fancourier_doar_km')) Configuration::deleteByName('fancourier_doar_km');
				//

                $url = 'http://www.selfawb.ro/order.php';
                $c = curl_init ($url);
                curl_setopt ($c, CURLOPT_POST, true);
                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&return=services");
                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                $page = curl_exec ($c);
                curl_close ($c);

                //$servicii_data = str_getcsv($page,"\n"); // COMPATIBIL PENTRU VERSIUNE PHP > 5.3.X
                $servicii_data = explode("\n",ltrim(rtrim($page))); // COMPATIBIL PENTRU VERSIUNE PHP < 5.2.X

                foreach($servicii_data as $tip_serviciu_info){
                    $tip_serviciu_info = str_replace('"','',$tip_serviciu_info);
                    $tip_serviciu = explode(",",$tip_serviciu_info);

                    $so_id = (int)Configuration::get('FANCOURIER_CARRIER_ID'.'_'.$tip_serviciu[4]);
                    //Delete So Carrier
                    if (is_numeric($so_id) and $so_id>0){
                            $fanCarrier = new Carrier($so_id);

                            //if fancourier carrier is default set other one as default
                            if(Configuration::get('PS_CARRIER_DEFAULT') == (int)($fanCarrier->id))
                            {
                                    $carriersD = Carrier::getCarriers($this->context->language->id);
                                    foreach($carriersD as $carrierD)
                                            if ($carrierD['active'] AND !$carrierD['deleted'])
                                                    Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
                            }

                            //save old carrier id
                            Configuration::updateValue('FANCOURIER_CARRIER_ID'.'_'.$tip_serviciu[4].'_HIST', Configuration::get('FANCOURIER_CARRIER_ID'.'_'.$tip_serviciu[4],'_HIST').'|'.(int)($fanCarrier->id));
                            $fanCarrier->deleted = 1;

                            $fanCarrier->update();

                            unset($fanCarrier);
                    }
                    unset($so_id);
                }

                if (!parent::uninstall() ||
                                !Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'fancourier_delivery_info`') ||
                                !$this->unregisterHook('extraCarrier') ||
                                !$this->unregisterHook('payment') ||
                                !$this->unregisterHook('AdminOrder') ||
                                !$this->unregisterHook('newOrder') ||
                                !$this->unregisterHook('updateCarrier')  ||
                                !$this->unregisterHook('paymentTop') ||
                                !$this->unregisterHook('actionValidateOrder'))
                        return true;
                return true;
	}

	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('FAN Courier').'</h2>';

		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}


	private function _displayForm()
	{   
                $combo = '';
                $servicii = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('select distinct name from `'._DB_PREFIX_.'carrier` where deleted=0 and  external_module_name = \'fancourier\'');
                foreach ($servicii as $serviciu)
                {
                     $combo .= '<option value="'.$serviciu["name"].'"';
                     if (Configuration::get('fancourier_serviciu')==$serviciu["name"]) $combo .= 'selected="selected"';
                     $combo .='>'.$serviciu["name"].'</option>';
                }

		$this->_html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" class="form">
		<fieldset><legend><img src="'.$this->_path.'fancourier.jpg" alt="" /></legend>'.
		$this->l('Stimate client, puteti obtine informatii pentru configurare la adresa de email: <a href="mailto:selfawb@fancourier.ro">selfawb@fancourier.ro</a>.').'
		<br/>Va multumim pentru ca folositi serviciile FAN Courier.
		</fieldset>
		<div class="clear">&nbsp;</div>
		<fieldset><legend>'.$this->l('Configurare Modul Curierat').'</legend>
		<label>'.$this->l('A se utiliza modulul FAN Courier').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_enabled" id="enabled_on" value="1" '.(Configuration::get('fancourier_enabled') ? 'checked="checked" ' : '').'/>
			<label class="t" for="enabled_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
			<input type="radio" name="fancourier_enabled" id="enabled_off" value="0" '.(!Configuration::get('fancourier_enabled') ? 'checked="checked" ' : '').'/>
			<label class="t" for="enabled_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
		</div>
                <h4>'.$this->l('Securitate:').'</h4>
		<label>'.$this->l('Client ID').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_clientid" value="'.Tools::safeOutput(Tools::getValue('fancourier_clientid', Configuration::get('fancourier_clientid'))).'" />
		'.//<p>'.$this->l('Secure password for back office fancourier.').'</p>
		'</div>

		<label>'.$this->l('Cont utilizator').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_username" value="'.Tools::safeOutput(Tools::getValue('fancourier_username', Configuration::get('fancourier_username'))).'" />
		</div>

		<label>'.$this->l('Parola').' : </label>
		<div class="margin-form">
		<input type="password" name="fancourier_password" value="'.Tools::safeOutput(Tools::getValue('fancourier_password', Configuration::get('fancourier_password'))).'" />
		</div>

		<label>'.$this->l('Confirmare AWB de catre Admin').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_onlyadm" id="fancourier_onlyadm_on" value="1" '.(Configuration::get('fancourier_onlyadm') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_onlyadm_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_onlyadm" id="fancourier_onlyadm_off" value="0" '.(!Configuration::get('fancourier_onlyadm') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_onlyadm_off">'.$this->l('Nu').'</label>
		</div>

                <h4>'.$this->l('Optiuni AWB:').'</h4>

		<label>'.$this->l('Expeditere colete').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_parcel" id="fancourier_parcel_on" value="1" '.(Configuration::get('fancourier_parcel') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_parcel_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_parcel" id="fancourier_parcel_off" value="0" '.(!Configuration::get('fancourier_parcel') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_parcel_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Numar pachete / AWB').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_labels" value="'.Tools::safeOutput(Tools::getValue('fancourier_labels', Configuration::get('fancourier_labels'))).'" />
                <p>'.$this->l('Introduceti un numar intreg. Exemplu: 1 - daca expeditati 1 pachet / AWB.').'</p>
		</div>

		<label>'.$this->l('Plata AWB la destinatie').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_paymentdest" id="fancourier_paymentdest_on" value="1" '.(Configuration::get('fancourier_paymentdest') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_paymentdest_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_paymentdest" id="fancourier_paymentdest_off" value="0" '.(!Configuration::get('fancourier_paymentdest') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_paymentdest_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Afisare pret fara TVA').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_fara_tva" id="fancourier_fara_tva_on" value="1" '.(Configuration::get('fancourier_fara_tva') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_fara_tva_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_fara_tva" id="fancourier_fara_tva_off" value="0" '.(!Configuration::get('fancourier_fara_tva') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_fara_tva_off">'.$this->l('Nu').'</label>
		</div>
		
		<!-- --->
		<!-- fara km suplimentari --->
		<!--
		<label>'.$this->l('Afisare pret fara km suplimentari').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_fara_km" id="fancourier_fara_km_on" value="1" '.(Configuration::get('fancourier_fara_km') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_fara_km_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_fara_km" id="fancourier_fara_km_off" value="0" '.(!Configuration::get('fancourier_fara_km') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_fara_km_off">'.$this->l('Nu').'</label>
			<p>'.$this->l('In cadrul acestei optiuni este necesar sa se seteze Plata AWB la destinatie - Nu si Adaugare taxa transp. la ramburs - Da').'</p>
		</div>-->
		
		<!-- doar km suplimentari -->
		<label>'.$this->l('Afisare pret doar km suplimentari').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_doar_km" id="fancourier_doar_km_on" value="1" '.(Configuration::get('fancourier_doar_km') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_doar_km_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_doar_km" id="fancourier_doar_km_off" value="0" '.(!Configuration::get('fancourier_doar_km') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_doar_km_off">'.$this->l('Nu').'</label>
			<p>'.$this->l('In cadrul acestei optiuni este necesar sa se seteze Plata AWB la destinatie - Nu si Adaugare taxa transp. la ramburs - Da').'</p>
		</div>
		<!-- --->

		<label>'.$this->l('Ascundere taxa de transport').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_payment0" id="fancourier_payment0_on" value="1" '.(Configuration::get('fancourier_payment0') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_payment0_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_payment0" id="fancourier_payment0_off" value="0" '.(!Configuration::get('fancourier_payment0') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_payment0_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Suma minima transport gratuit').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_min_gratuit" value="'.Tools::safeOutput(Tools::getValue('fancourier_min_gratuit', Configuration::get('fancourier_min_gratuit'))).'" />
		</div>

		<label>'.$this->l('Afisare suma fixa transport').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_suma_fixa" value="'.Tools::safeOutput(Tools::getValue('fancourier_suma_fixa', Configuration::get('fancourier_suma_fixa'))).'" />
		</div>

                <h4>'.$this->l('Servicii disponibile:').'</h4>

		<label>'.$this->l('Serviciul activ').': </label>
		<div class="margin-form">
                        <select name="fancourier_serviciu" id="fancourier_serviciu">
                           '.$combo.'
                        </select>
		</div>

                <h4>'.$this->l('Optiuni ramburs:').'</h4>

		<label>'.$this->l('Solicitare ramburs valoare marfa').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_ramburs" id="fancourier_ramburs_on" value="1" '.(Configuration::get('fancourier_ramburs') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_ramburs_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_ramburs" id="fancourier_ramburs_off" value="0" '.(!Configuration::get('fancourier_ramburs') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_ramburs_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Adaugare taxa transp. la ramburs').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_totalrb" id="fancourier_totalrb_on" value="1" '.(Configuration::get('fancourier_totalrb') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_totalrb_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_totalrb" id="fancourier_totalrb_off" value="0" '.(!Configuration::get('fancourier_totalrb') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_totalrb_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Plata ramburs la destinatie').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_paymentrbdest" id="fancourier_paymentrbdest_on" value="1" '.(Configuration::get('fancourier_paymentrbdest') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_paymentrbdest_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_paymentrbdest" id="fancourier_paymentrbdest_off" value="0" '.(!Configuration::get('fancourier_paymentrbdest') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_paymentrbdest_off">'.$this->l('Nu').'</label>
                        <p>'.$this->l('Nu se aplica pentru serviciile de tip Cont Colector.').'</p>
		</div>

                <h4>'.$this->l('Asigurare:').'</h4>

		<label>'.$this->l('Solicitare asigurare de transport').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_asigurare" id="fancourier_asigurare_on" value="1" '.(Configuration::get('fancourier_asigurare') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_asigurare_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_asigurare" id="fancourier_asigurare_off" value="0" '.(!Configuration::get('fancourier_asigurare') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_asigurare_off">'.$this->l('Nu').'</label>
		</div>

		<label>'.$this->l('Include cod produse la continut').' : </label>
		<div class="margin-form">
			<input type="radio" name="fancourier_content" id="fancourier_content_on" value="1" '.(Configuration::get('fancourier_content') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_content_on">'.$this->l('Da').'</label>
			<input type="radio" name="fancourier_content" id="fancourier_content_off" value="0" '.(!Configuration::get('fancourier_content') ? 'checked="checked" ' : '').'/>
			<label class="t" for="fancourier_content_off">'.$this->l('Nu').'</label>
		</div>

                <h4>'.$this->l('Observatii si note:').'</h4>

		<label>'.$this->l('Observatii (imprimare pe AWB)').' : </label>
		<div class="margin-form">
		<input type="text" name="fancourier_comment" value="'.Tools::safeOutput(Tools::getValue('fancourier_comment', Configuration::get('fancourier_comment'))).'" />
		</div>
		
		<label>'.$this->l('Persoana de contact').' : </label>
		<div class="margin-form">
		<input type="text" name="pers_contact_expeditor" value="'.Tools::safeOutput(Tools::getValue('pers_contact_expeditor', Configuration::get('pers_contact_expeditor'))).'" />
		</div>
		
		<h4>'.$this->l('Optiuni servicii*:').'</h4>
			 
		<label>'.$this->l('Deschidere la livrare').' : </label>
		<div class="margin-form">
		<input type="checkbox" name="deschidere_livrare" value="A"'.(Configuration::get('deschidere_livrare') ? 'checked="checked" ' : '').' />
		</div>

		<div class="margin-form">
                <br><br>
		<input type="submit" value="'.$this->l('Salveaza').'" name="submitSave" class="button"/>
		</div>
		</fieldset></form>';
	}

	private function _postValidation()
	{
		if (Tools::getValue('fancourier_username') == NULL)
			$this->_postErrors[] = $this->l('Contul de utilizator este necesar.');

		if (Tools::getValue('fancourier_password') == NULL)
			$this->_postErrors[] = $this->l('Parola este necesara');

		if (Tools::getValue('fancourier_clientid') == NULL)
			$this->_postErrors[] = $this->l('Client ID este necesar');
	}

	private function _postProcess()
	{

                Db::getInstance()->execute('update `'._DB_PREFIX_.'carrier` set active = 0  where external_module_name= \'fancourier\' and name <> \''.Tools::getValue('fancourier_serviciu').'\'');
                Db::getInstance()->execute('update `'._DB_PREFIX_.'carrier` set active = 1  where external_module_name= \'fancourier\' and name = \''.Tools::getValue('fancourier_serviciu').'\'');
            
		if (Configuration::updateValue('fancourier_enabled', Tools::getValue('fancourier_enabled')) &&
                                Configuration::updateValue('fancourier_username', Tools::getValue('fancourier_username')) &&
				Configuration::updateValue('fancourier_password', Tools::getValue('fancourier_password')) &&
				Configuration::updateValue('fancourier_clientid', Tools::getValue('fancourier_clientid')) &&
                                Configuration::updateValue('fancourier_username', Tools::getValue('fancourier_username')) &&
                                Configuration::updateValue('fancourier_password', Tools::getValue('fancourier_password')) &&
                                Configuration::updateValue('fancourier_clientid', Tools::getValue('fancourier_clientid')) &&
                                Configuration::updateValue('fancourier_parcel', Tools::getValue('fancourier_parcel')) &&
                                Configuration::updateValue('fancourier_labels', Tools::getValue('fancourier_labels')) &&
                                Configuration::updateValue('fancourier_ramburs', Tools::getValue('fancourier_ramburs')) &&
                                Configuration::updateValue('fancourier_content', Tools::getValue('fancourier_content')) &&
                                Configuration::updateValue('fancourier_serviciu', Tools::getValue('fancourier_serviciu')) &&
                                Configuration::updateValue('fancourier_paymentdest', Tools::getValue('fancourier_paymentdest')) &&
                                Configuration::updateValue('fancourier_paymentrbdest', Tools::getValue('fancourier_paymentrbdest')) &&
                                Configuration::updateValue('fancourier_payment0', Tools::getValue('fancourier_payment0')) &&
                                Configuration::updateValue('fancourier_min_gratuit', Tools::getValue('fancourier_min_gratuit')) &&
                                Configuration::updateValue('fancourier_suma_fixa', Tools::getValue('fancourier_suma_fixa')) &&
                                Configuration::updateValue('fancourier_comment', Tools::getValue('fancourier_comment')) &&
								//boby 02.05.2014 afisare persoana de contact expeditor
								Configuration::updateValue('pers_contact_expeditor', Tools::getValue('pers_contact_expeditor')) &&
								//end boby
								//boby 05.05.2014 deschidere la livrare
								Configuration::updateValue('deschidere_livrare', Tools::getValue('deschidere_livrare')) &&
								//end boby
                                Configuration::updateValue('fancourier_asigurare', Tools::getValue('fancourier_asigurare')) &&
                                Configuration::updateValue('fancourier_totalrb', Tools::getValue('fancourier_totalrb')) &&
                                Configuration::updateValue('fancourier_onlyadm', Tools::getValue('fancourier_onlyadm')) &&
                                Configuration::updateValue('fancourier_fara_tva', Tools::getValue('fancourier_fara_tva')) &&
								//fara km suplimentari
								//Configuration::updateValue('fancourier_fara_km', Tools::getValue('fancourier_fara_km'))	&&							
								//doar km suplimentari
								Configuration::updateValue('fancourier_doar_km', Tools::getValue('fancourier_doar_km')))
                {
                        $this->_html .= $this->displayConfirmation($this->l('Modulul a fost configurat cu succes.<br>Va multumim pentru ca folositi serviciile FAN Courier. Pentru intrebari sau nelamuriri nu ezitati sa ne contactati la email: <a href="mailto:selfawb@fancourier.ro">selfawb@fancourier.ro</a>'));
		}
		else
			$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" /> '.$this->l('Cannot save settings').'</div>';
	}

	//alex g 18.10.2013
	public function hookExtraCarrier($params)
	{
			//generare eroare in caz de comanda nevalida
			//preluare informatii despre cost din sesiune
			if($_SESSION['cost']==99999)
			{								
				?>										
				<!-- Css pentru mesajul de eroare -->
				
				<style type="text/css">
				err
				{
				color:#FF0000;				
				font-weight:bold;
				}
				</style>
				
				<!-- activare jquery -->
				<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> -->
				<script type="text/javascript" src="/prestashop/js/jquery/jquery-1.7.2.min.js"></script>

				<!-- afisare mesaj de eroare -->
				<script>
				$(document).ready(function(){
				$(".delivery_options_address").first().after("<center><err>Comanda nu a fost procesata de catre FAN Courier.<br></br>Va rugam sa corectati datele de livrare.</err></center><br></br>");
				});
				</script>
				<?php 		
			}
									
            //file_put_contents('C:/xampp/php/logs/log.txt', 'hookExtraCarrier');
		}	
	//end alex g
	
	public function hookNewOrder($params)
	{
            //file_put_contents('C:/xampp/php/logs/log.txt', 'hookNewOrder');
	}
//
	public function hookAdminOrder($params)
	{
            $delivery_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('select * from `'._DB_PREFIX_.'fancourier_delivery_info` where id_cart = \''.($params["cart"]->id).'\'');
            foreach ($delivery_info as $delivery)
            {
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'order_carrier set tracking_number = \''.$delivery["fan_order"].'\' where id_order = '.$params["order"]->id);
            }
                                           
            //start alex g link generare awb
            $IdTracking = Db::getInstance()->getRow('select tracking_number from `'._DB_PREFIX_.'order_carrier` where id_order = '.$params["id_order"].'');                       
            if ($IdTracking["tracking_number"]!="")
            {
            ?>
            <!-- parte de html pt tracking link -->			
            <br></br>           
            <div class="panel" >
            <h3>
            <i class="icon-user"> Curierat</i>
            </h3>
            	<div class="well">
            		<fieldset>          		
            		<a href="http://www.selfawb.ro/order.php?order_id=<?php echo $IdTracking["tracking_number"]; ?>"><b>AWB - FAN Courier</b></a>
            		</fieldset>
            	</div> 
            </div>           			
            <?php
            }
            //end alex g			
            
            
	}
//
	public function hookUpdateCarrier($params)
	{
            //file_put_contents('C:/xampp/php/logs/log.txt', 'hookUpdateCarrier');
	}
//
	public function hookPaymentTop($params)
	{
            
	}

	public function hookactionValidateOrder($params)
	{
            //file_put_contents('C:/xampp/php/logs/log.txt', json_encode($params));
            $delivery_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('select * from `'._DB_PREFIX_.'fancourier_delivery_info` where id_cart = \''.($params["cart"]->id).'\'');
            foreach ($delivery_info as $delivery)
            {
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'order_carrier set tracking_number = \''.$delivery["fan_order"].'\' where id_order = '.$params["order"]->id);
            }
	}

	public static function createFANCourierCarrier($config)
	{

                $url = 'http://www.selfawb.ro/order.php';
                $c = curl_init ($url);
                curl_setopt ($c, CURLOPT_POST, true);
                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&return=services");
                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                $page = curl_exec ($c);
                curl_close ($c);

                //$servicii_data = str_getcsv($page,"\n"); // COMPATIBIL PENTRU VERSIUNE PHP > 5.3.X
                $servicii_data = explode("\n",ltrim(rtrim($page))); // COMPATIBIL PENTRU VERSIUNE PHP < 5.2.X
                $carrier_cont = 0;
                foreach($servicii_data as $tip_serviciu_info){
                    $tip_serviciu_info = str_replace('"','',$tip_serviciu_info);
                    $tip_serviciu = explode(",",$tip_serviciu_info);
                    
                    $carrier = new Carrier();
                    $carrier->name = $config['name'].' - '.$tip_serviciu[0];
                    $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
                    $carrier->id_zone = $config['id_zone'];
                    $carrier->url = $config['url'];
                    $carrier->active = $config['active'];
                    $carrier->deleted = $config['deleted'];
                    $carrier->delay = $config['delay'];
                    $carrier->shipping_handling = $config['shipping_handling'];
                    $carrier->shipping_free_price = $config['shipping_free_price'];
                    $carrier->shipping_free_weight = $config['shipping_free_weight'];
                    $carrier->range_behavior = $config['range_behavior'];
                    $carrier->is_module = $config['is_module'];
                    $carrier->shipping_external = $config['shipping_external'];
                    $carrier->external_module_name = $config['external_module_name'];//.'_'.str_replace(' ','_',str_replace('-','_',$tip_serviciu[0]));
                    $carrier->need_range = $config['need_range'];

                    $languages = Language::getLanguages(true);
                    foreach ($languages as $language)
                    {
                            if ($language['iso_code'] == 'fr')
                                    $carrier->delay[(int)$language['id_lang']] = 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h';
                            if ($language['iso_code'] == 'ro')
                                    $carrier->delay[(int)$language['id_lang']] = 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h';
                            if ($language['iso_code'] == 'en')
                                    $carrier->delay[(int)$language['id_lang']] = 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h';
                            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
                                    $carrier->delay[(int)$language['id_lang']] = 'Curierat rapid, termen mediu de livrare pentru serviciul Standard: 24h';
                    }

                    if($carrier->add())
                    {

                            Configuration::updateValue('FANCOURIER_CARRIER_ID'.'_'.$tip_serviciu[4],(int)($carrier->id));
                            $groups = Group::getgroups(true);
                            foreach ($groups as $group)
                            {
                                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');
                            }
                            $rangePrice = new RangePrice();
                            $rangePrice->id_carrier = $carrier->id;
                            $rangePrice->delimiter1 = '0';
                            $rangePrice->delimiter2 = '10000';
                            $rangePrice->add();

                            $rangeWeight = new RangeWeight();
                            $rangeWeight->id_carrier = $carrier->id;
                            $rangeWeight->delimiter1 = '0';
                            $rangeWeight->delimiter2 = '10000';
                            $rangeWeight->add();

                            $zones = Zone::getZones(true);
                            foreach ($zones as $zone)
                            {
                                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'carrier_zone  (id_carrier, id_zone) VALUE (\''.(int)($carrier->id).'\',\''.(int)($zone['id_zone']).'\')');
                                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'delivery (id_carrier, id_range_price, id_range_weight, id_zone, price) VALUE (\''.(int)($carrier->id).'\',\''.(int)($rangePrice->id).'\',NULL,\''.(int)($zone['id_zone']).'\',\'0\')');
                                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'delivery (id_carrier, id_range_price, id_range_weight, id_zone, price) VALUE (\''.(int)($carrier->id).'\',NULL,\''.(int)($rangeWeight->id).'\',\''.(int)($zone['id_zone']).'\',\'0\')');
                            }
                            //copy logo
                            if (!copy(dirname(__FILE__).'/fancourier.jpg',_PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg'))
                                    return false;

                            if ($carrier_cont==0){
                                    Configuration::updateValue('fancourier_enabled', 1);
                                    Configuration::updateValue('fancourier_username', '');
                                    Configuration::updateValue('fancourier_password', '');
                                    Configuration::updateValue('fancourier_clientid', '');
                                    Configuration::updateValue('fancourier_parcel', '0');
                                    Configuration::updateValue('fancourier_labels', '1');
                                    Configuration::updateValue('fancourier_ramburs', '0');
                                    Configuration::updateValue('fancourier_content', '0');
                                    Configuration::updateValue('fancourier_serviciu', $config['name'].' - '.$tip_serviciu[0]);
                                    Configuration::updateValue('fancourier_paymentdest', '0');
                                    Configuration::updateValue('fancourier_paymentrbdest', '0');
                                    Configuration::updateValue('fancourier_payment0', '0');
                                    Configuration::updateValue('fancourier_min_gratuit', '');
                                    Configuration::updateValue('fancourier_suma_fixa', '');
                                    Configuration::updateValue('fancourier_comment', '');
									//boby 02.05.2014 afisare persoana de contact expeditor
									Configuration::updateValue('pers_contact_expeditor', '');
									//end boby
									//boby 05.05.2014 deschidere la livrare
									Configuration::updateValue('deschidere_livrare', '');
									//end boby
                                    Configuration::updateValue('fancourier_asigurare', '0');
                                    Configuration::updateValue('fancourier_totalrb', '0');
                                    Configuration::updateValue('fancourier_onlyadm', '0');
                                    Configuration::updateValue('fancourier_fara_tva','0');
									//fara km suplimentari
									//Configuration::updateValue('fancourier_fara_km','0');
									//doar km suplimentari
									Configuration::updateValue('fancourier_doar_km','0');
                            }

                            $carrier_cont += 1;
                    }
                    
                }
                return true;
	}

	public function checkZone($id_carrier)
	{
		return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_zone WHERE id_carrier = '.(int)($id_carrier));
	}

	public function checkGroup($id_carrier)
	{
		return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_group WHERE id_carrier = '.(int)($id_carrier));
	}

 	public function checkRange($id_carrier)
	{
		switch (Configuration::get('PS_SHIPPING_METHOD'))
		{
			case '0' :
				$sql = 'SELECT * FROM '._DB_PREFIX_.'range_price WHERE id_carrier = '.(int)($id_carrier);
				break;
			case '1' :
				$sql = 'SELECT * FROM '._DB_PREFIX_.'range_weight WHERE id_carrier = '.(int)($id_carrier);
				break;
		}
		return (bool)Db::getInstance()->getRow($sql);
	}

	public function checkDelivery($id_carrier)
	{
		return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'delivery WHERE id_carrier = '.(int)($id_carrier));
	}

	public function getOrderShippingCost($params, $shipping_cost)
	{

		//selectare din baza de date a ratei de conversie
		global $cart;
		$currency_id=$cart->id_currency;		
		$RataConversie = Db::getInstance()->getRow('select conversion_rate from '._DB_PREFIX_.'currency where id_currency = '.$currency_id.'');
		$RataConversie["conversion_rate"]=round((float)$RataConversie["conversion_rate"],4);
		
		//selectare rata conversie leu
		$RataConversieLeu = Db::getInstance()->getRow('select conversion_rate from '._DB_PREFIX_.'currency where iso_code="RON"');
		$RataConversieLeu["conversion_rate"]=round((float)$RataConversieLeu["conversion_rate"],4);
		
		//echo $RataConversieLeu["conversion_rate"];
		
		
		if (!Configuration::get('fancourier_enabled')) {
			return false;
		}

                $suma_fixa = Configuration::get('fancourier_suma_fixa');

                if (!isset($this->context->cart)) {
                        if (isset($suma_fixa) and $suma_fixa!='' and is_numeric($suma_fixa)){
                            return $suma_fixa;
                        } else {
                            $delivery_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('select * from `'._DB_PREFIX_.'fancourier_delivery_info` where id_cart = \''.($params->id).'\'');
                            foreach ($delivery_info as $delivery)
                            {
                                $shipping_cost = $shipping_cost + $delivery["price"];
                                return $shipping_cost;
                            }
                        }
                }
                
                $address = new Address((int)($this->context->cart->id_address_delivery));
                
                $username = Configuration::get('fancourier_username');
                $parola = Configuration::get('fancourier_password');
                $clientid = Configuration::get('fancourier_clientid');
                $parcel = Configuration::get('fancourier_parcel');
                $labels = Configuration::get('fancourier_labels');
                $ramburs = Configuration::get('fancourier_ramburs');
                $content = Configuration::get('fancourier_content');
                $serviciu = Configuration::get('fancourier_serviciu');
                $paymentdest = Configuration::get('fancourier_paymentdest');
                $paymentrbdest = Configuration::get('fancourier_paymentrbdest');
                $payment0 = Configuration::get('fancourier_payment0');
                $min_gratuit = Configuration::get('fancourier_min_gratuit');
                
	 			//transformare minim gratuit in orice valuta                
                if (is_numeric($min_gratuit))
                {
                $min_gratuit=($min_gratuit*$RataConversie["conversion_rate"])/$RataConversieLeu["conversion_rate"];
                }
                
                $observatii = Configuration::get('fancourier_comment');
				//boby 02.05.2014 afisare persoana de contact expeditor
				$pers_contact_expeditor = Configuration::get('pers_contact_expeditor');
				//end boby
				//boby 05.05.2014 deschidere la livrare
				$optiuni = Configuration::get('deschidere_livrare');
				//end boby
                $asigurare = Configuration::get('fancourier_asigurare');
                $totalrb = Configuration::get('fancourier_totalrb');
                $onlyadm = Configuration::get('fancourier_onlyadm');
                $fara_tva = Configuration::get('fancourier_fara_tva');
				//fara km suplimentari
				//$fara_km = Configuration::get('fancourier_fara_km');
				//doar km suplimentari
				$doar_km = Configuration::get('fancourier_doar_km');
				
                $msg = "Comanda nu a fost procesata de catre FAN Courier.<br>Va rugam sa corectati datele de livrare conform mesajului de mai jos: <br><br>";

                $url = 'http://www.selfawb.ro/order.php';
                $c = curl_init ($url);
                curl_setopt ($c, CURLOPT_POST, true);
                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&return=services");
                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                $page = curl_exec ($c);
                curl_close ($c);

                //$servicii_data = str_getcsv($page,"\n"); // COMPATIBIL PENTRU VERSIUNE PHP > 5.3.X
                $servicii_data = explode("\n",ltrim(rtrim($page))); // COMPATIBIL PENTRU VERSIUNE PHP < 5.2.X

                foreach($servicii_data as $tip_serviciu_info){
                    $tip_serviciu_info = str_replace('"','',$tip_serviciu_info);
                    $tip_serviciu = explode(",",$tip_serviciu_info);

                    if ('FAN Courier - '.$tip_serviciu[0] == $serviciu){
                        $contcolector = $tip_serviciu[1];
                        if ($contcolector==1) $ramburs=1;
                        $redcode = $tip_serviciu[2];
                        $express = $tip_serviciu[3];
                    }
                }

                $method_data = array();
                $error = '';

                if (is_numeric($min_gratuit)) $min_gratuit = $min_gratuit + 0; else $min_gratuit = 0 + 0;

                if ($parcel){
                    $plic="0";
                    if (is_numeric($labels)){
                        $colet=$labels;
                    } else {
                        $colet=1;
                    }
                } else {
                    $colet="0";
                    if (is_numeric($labels)){
                        $plic=$labels;
                    } else {
                        $plic=1;
                    }
                }

                if ($totalrb){
                    $totalrb = "1";
                } else {
                    $totalrb = "0";
                }

                if ($asigurare){
                        $valoaredeclarata = number_format(round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2), 2, '.', '');
                } else {
                        $valoaredeclarata = 0;
                }

                $greutate = number_format(round((float)$this->context->cart->getTotalWeight(),0), 0, '.', '');
									//
									if ($greutate>1)
									{
										$plic=0;
										if (is_numeric($labels))
										{
											$colet=$labels;
										} 
										else 
										{
											$colet=1;
										}
									}
									//
				
                if (round((float)$this->context->cart->getTotalWeight(),0)>5) $redcode = false;

                $lungime=0;
                $latime=0;
                $inaltime=0;
                
                if ($paymentdest){
                    $plata_expeditiei="destinatar";
                }else{
                    $plata_expeditiei="expeditor";
                }

                $rambursare = '';
                $rambursare_number = 0 + 0;

                $plata_expeditiei_ramburs = "";

                if ($ramburs or $contcolector){
                    if ($contcolector){
                        $rambursare = number_format(round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2), 2, '.', '');
                        $rambursare_number = round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2)+0;
                        if ($paymentrbdest){
                            $plata_expeditiei_ramburs="destinatar";
                        }else{
                            $plata_expeditiei_ramburs="expeditor";
                        }
                    } else {
                        $rambursare = (string)number_format(round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2), 2, '.', '')." LEI";
                        $rambursare_number = round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2)+0;
                        if ($paymentrbdest){
                            $plata_expeditiei_ramburs="destinatar";
                        }else{
                            $plata_expeditiei_ramburs="expeditor";
                        }
                    }
                } else {
                    $rambursare_number = round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),2)+0;
                }

                $localitate_dest = $address->city;

                if ($address->id_state!=0 and is_numeric($address->id_state)){
                    $state_info = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('select name from `'._DB_PREFIX_.'state` where id_state = \''.($address->id_state).'\'');
                    foreach ($state_info as $state)
                    {
                        $judet_dest = $state["name"];
                    }
                } else {
                    $judet_dest = 'Bucuresti';
                }

                $localitate_dest = trim($localitate_dest);
                
                $judet_dest = trim($judet_dest);
                
                //$pers_contact_expeditor = '';

                $continut='';
                if ($content){
                    $continut = "";
                    foreach($this->context->cart->getProducts() as $Products){
                        //$continut = $continut.', '.$Products["category"];
						$continut = $continut.', '.$Products["quantity"].' x '.$Products["name"];
                    }
                    if ($continut!='') $continut = substr($continut, 2, 35);

                }

                if(trim($address->company)!=''){
                    $nume_destinatar =$address->company;
                    $persoana_contact =  $address->firstname." ".$address->lastname;
                } else {
                    $nume_destinatar = $address->firstname." ".$address->lastname;
                    $persoana_contact = '';
                }

                $telefon = $address->phone;
                if (!is_null($address->phone_mobile) and $address->phone_mobile!='') $telefon=$telefon." / ".$address->phone_mobile;
                               

                $strada = $address->address1;
                if ($address->address2!=''){
                    $strada = $strada.", ".$address->address2;
                }

                $postalcode = str_pad($address->postcode, 6 ,"0");

                $url = 'http://www.selfawb.ro/order.php';
                $c = curl_init ($url);
                curl_setopt ($c, CURLOPT_POST, true);
                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&return=services");
                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                $page = curl_exec ($c);
                curl_close ($c);

                //$servicii_data = str_getcsv($page,"\n"); // COMPATIBIL PENTRU VERSIUNE PHP > 5.3.X
                $servicii_data = explode("\n",ltrim(rtrim($page))); // COMPATIBIL PENTRU VERSIUNE PHP < 5.2.X

                foreach($servicii_data as $tip_serviciu_info){
                    $tip_serviciu_info = str_replace('"','',$tip_serviciu_info);
                    $tip_serviciu = explode(",",$tip_serviciu_info);
                    if ('FAN Courier - '.$tip_serviciu[0] == $serviciu){
                            if ((!$contcolector or round($ramburs, 0)==0)){
                                if ($tip_serviciu[1]==0 and (($tip_serviciu[2]==0 and $tip_serviciu[3]==0) or ($tip_serviciu[2]==1 and $redcode) or ($tip_serviciu[3]==1 and $express))){
                                		// alex g 22.04.2014
                                		if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {} else {$suma_fixa=0; $totalrb=0;}
                                		//sfarsit alex g
                                        $url = 'http://www.selfawb.ro/order.php';
                                        $c = curl_init ($url);
                                        curl_setopt ($c, CURLOPT_POST, true);
                                        curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&plata_expeditiei=$plata_expeditiei&tip_serviciu=$tip_serviciu[0]&localitate_dest=$localitate_dest&judet_dest=$judet_dest&plic=$plic&colet=$colet&greutate=$greutate&lungime=$lungime&latime=$latime&inaltime=$inaltime&valoare_declarata=$valoaredeclarata&plata_ramburs=$plata_expeditiei_ramburs&ramburs=$rambursare&pers_contact_expeditor=$pers_contact_expeditor&observatii=$observatii&continut=$continut&nume_destinatar=$nume_destinatar&persoana_contact=$persoana_contact&telefon=$telefon&strada=$strada&postalcode=$postalcode&totalrb=$totalrb&admin=$onlyadm&fara_tva=$fara_tva&suma_fixa=$suma_fixa&doar_km=$doar_km&optiuni=$optiuni");
                                        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                                        $page = curl_exec ($c);
                                        curl_close ($c);
                                        $price = explode("|||",$page);
                                        if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {$price_standard = $price[0];} else {$price_standard = 0;$suma_fixa=0;}
                                        $link_standard = $price[1];
                                        if (is_numeric($price_standard) and $link_standard!="")
										{
//                                               file_put_contents('C:/xampp/php/logs/log5.txt', 'FAN_PRICE'.$this->context->cart->id);
                                                //if (isset($suma_fixa) and $suma_fixa!='' and is_numeric($suma_fixa)){
                                                //   Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                                //   Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'fancourier_delivery_info VALUE (\''.($this->context->cart->id).'\',\''.($suma_fixa).'\',\''.($link_standard).'\')');                                                 
                                                //   $_SESSION['cost']=$shipping_cost;
                                                //   return ($shipping_cost*$RataConversie["conversion_rate"])/$RataConversieLeu["conversion_rate"];
                                                //} else {
                                                   Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                                   Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'fancourier_delivery_info VALUE (\''.($this->context->cart->id).'\',\''.($price_standard).'\',\''.($link_standard).'\')');
                                                   $shipping_cost = $shipping_cost + $price_standard;
                                                   $_SESSION['cost']=$shipping_cost;
                                                   return ($shipping_cost*$RataConversie["conversion_rate"])/$RataConversieLeu["conversion_rate"];
                                                //}
                                        }
										else
										{
                                               Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                               $shipping_cost=99999;
                                               $_SESSION['cost']=$shipping_cost;
                                               return false;
                                        }
                                }
                                unset($tip_serviciu);
                            } else {
                                $tip_serviciu = explode(",",$tip_serviciu_info);
                                if ($tip_serviciu[1]==1 and (($tip_serviciu[2]==0 and $tip_serviciu[3]==0) or ($tip_serviciu[2]==1 and $redcode) or ($tip_serviciu[3]==1 and $express))){
                                		// alex g 22.04.2014
                                		if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {} else {$suma_fixa=0; $totalrb=0;}
                                		//sfarsit alex g
                                        $url = 'http://www.selfawb.ro/order.php';
                                        $c = curl_init ($url);
                                        curl_setopt ($c, CURLOPT_POST, true);
                                        curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&plata_expeditiei=$plata_expeditiei&tip_serviciu=$tip_serviciu[0]&localitate_dest=$localitate_dest&judet_dest=$judet_dest&plic=$plic&colet=$colet&greutate=$greutate&lungime=$lungime&latime=$latime&inaltime=$inaltime&valoare_declarata=$valoaredeclarata&plata_ramburs=$plata_expeditiei_ramburs&ramburs=$rambursare&pers_contact_expeditor=$pers_contact_expeditor&observatii=$observatii&continut=$continut&nume_destinatar=$nume_destinatar&persoana_contact=$persoana_contact&telefon=$telefon&strada=$strada&postalcode=$postalcode&totalrb=$totalrb&admin=$onlyadm&fara_tva=$fara_tva&suma_fixa=$suma_fixa&doar_km=$doar_km&optiuni=$optiuni");
                                        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                                        $page = curl_exec ($c);
                                        curl_close ($c);
                                        $price = explode("|||",$page);
                                        if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {$price_standard = $price[0];} else {$price_standard = 0;$suma_fixa=0;}
                                        $link_standard = $price[1];
                                        if (is_numeric($price_standard) and $link_standard!="")
										{
//                                               file_put_contents('C:/xampp/php/logs/log5.txt', 'FAN_PRICE'.$this->context->cart->id);
                                               //if (isset($suma_fixa) and $suma_fixa!='' and is_numeric($suma_fixa)){
                                                 //  Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                                 //  Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'fancourier_delivery_info VALUE (\''.($this->context->cart->id).'\',\''.($suma_fixa).'\',\''.($link_standard).'\')');
                                                   //$shipping_cost = $shipping_cost + $suma_fixa;
												 //  $shipping_cost = $shipping_cost + $price_standard;
                                                //   $_SESSION['cost']=$shipping_cost;
                                                //   return ($shipping_cost*$RataConversie["conversion_rate"])/$RataConversieLeu["conversion_rate"];
                                                //} 
												//else 
												//{        
                                                   Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                                   Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'fancourier_delivery_info VALUE (\''.($this->context->cart->id).'\',\''.($price_standard).'\',\''.($link_standard).'\')');
                                                   $shipping_cost = $shipping_cost + $price_standard;
                                                   $_SESSION['cost']=$shipping_cost;
                                                   return ($shipping_cost*$RataConversie["conversion_rate"])/$RataConversieLeu["conversion_rate"];
                                                //}
                                        }
										else
										{
                                        	   
                                               Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'fancourier_delivery_info WHERE id_cart = \''.($this->context->cart->id).'\'');
                                                 //echo var_dump($params); exit;
                                               $shipping_cost=99999;
                                               $_SESSION['cost']=$shipping_cost;
                                               //echo $_SESSION['cost'];
                                               	//echo $shipping_cost;                                       
                                               return false;
                                        }
                                }
                                unset($tip_serviciu);
                            }
                    }
                }
                
                return false;

	}

	public function getOrderShippingCostExternal($params){
            
        }

	public function getNumVersion()
	{
		return $this->api_num_version;
	}

	/**
	 * Return the cecivility customer
	 *
	 * @return string
	 */
	public function getTitle(Customer $customer)
	{
		$title = 'MR';
		if (_PS_VERSION_ < '1.5')
		{
			$titles = array('1' => 'MR', '2' => 'MME');
			if (isset($titles[$customer->id_gender]))
				return $titles[$customer->id_gender];
		}
		else
		{
			$gender = new Gender($customer->id_gender);
			return $gender->name;
		}
		return $title;
	}

	/**
	 * @param $str
	 * @return mixed
	 */
	public function replaceDiacriticsChars($str)
	{
		return preg_replace(
			array(
				/* Lowercase */
				'/[\x{0105}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u',
				'/[\x{00E7}\x{010D}\x{0107}]/u',
				'/[\x{010F}]/u',
				'/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{011B}\x{0119}]/u',
				'/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}]/u',
				'/[\x{0142}\x{013E}\x{013A}]/u',
				'/[\x{00F1}\x{0148}]/u',
				'/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}]/u',
				'/[\x{0159}\x{0155}]/u',
				'/[\x{015B}\x{0161}]/u',
				'/[\x{00DF}]/u',
				'/[\x{0165}]/u',
				'/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}]/u',
				'/[\x{00FD}\x{00FF}]/u',
				'/[\x{017C}\x{017A}\x{017E}]/u',
				'/[\x{00E6}]/u',
				'/[\x{0153}]/u',

				/* Uppercase */
				'/[\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u',
				'/[\x{00C7}\x{010C}\x{0106}]/u',
				'/[\x{010E}]/u',
				'/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{011A}\x{0118}]/u',
				'/[\x{0141}\x{013D}\x{0139}]/u',
				'/[\x{00D1}\x{0147}]/u',
				'/[\x{00D3}]/u',
				'/[\x{0158}\x{0154}]/u',
				'/[\x{015A}\x{0160}]/u',
				'/[\x{0164}]/u',
				'/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{016E}]/u',
				'/[\x{017B}\x{0179}\x{017D}]/u',
				'/[\x{00C6}]/u',
				'/[\x{0152}]/u',
			),
			array(
				'a', 'c', 'd', 'e', 'i', 'l', 'n', 'o', 'r', 's', 'ss', 't', 'u', 'y', 'z', 'ae', 'oe',
				'A', 'C', 'D', 'E', 'L', 'N', 'O', 'R', 'S', 'T', 'U', 'Z', 'AE', 'OE'
			),
			$str);
	}
}

