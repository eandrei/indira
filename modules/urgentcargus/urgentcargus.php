<?php
class UrgentCargus extends Module
{
	function __construct()
	{
		$this->name = 'urgentcargus';
		$this->tab = 'shipping_logistics';
		$this->version = '3.0';
		$this->author = 'Urgent Cargus SA';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Urgent Cargus');
		//$this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
		$this->description = $this->l('Curier Rapid');
	}

	function install()
	{
        include(dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php');

		if (!parent::install() OR !$this->registerHook('header') OR !$this->registerHook('rightColumn') OR !$this->registerHook('leftColumn') OR !$this->registerHook('backOfficeHeader'))
        {
			return false;
        }

        $this->addCarrier();

        return true;
	}

    public function uninstall()
    {
        $this->removeCarrier();

        include(dirname(__FILE__).DIRECTORY_SEPARATOR.'uninstall.php');

        if (!parent::uninstall() OR !$this->unregisterHook('header') OR !$this->unregisterHook('rightColumn') OR !$this->unregisterHook('leftColumn') OR !$this->unregisterHook('backOfficeHeader'))
        {
            return false;
        }

	    return true;
	}

	function hookHeader($params)
	{
		return $this->display(__FILE__, 'frontend/frontend_header.tpl');
	}

    function hookRightColumn($params)
    {
        return $this->display(__FILE__, 'frontend/awb_tracking.tpl');
    }

    function hookLeftColumn($params)
    {
        return $this->display(__FILE__, 'frontend/awb_tracking.tpl');
    }

    function hookBackOfficeHeader($params)
	{
		if (strtolower($_GET['controller']) == 'adminorders' && !isset($_GET['id_order'])) {
            return $this->display(__FILE__, 'backend/admin_mods.tpl');
        } else {
            return $this->display(__FILE__, 'backend/urgentcargus_autocomplete.tpl');
        }
	}

    public function getOrderShippingCost($params, $shipping_cost) {
        return $this->calculeazaTransport($params);
	}

	public function getOrderShippingCostExternal($params) {
		return $this->calculeazaTransport($params);
	}

    public function addCarrier()
	{
		$carrier = new Carrier();
		$carrier->name = 'Urgent Cargus';
		$carrier->id_tax_rules_group = 0;
		$carrier->id_zone = 1;
		$carrier->active = true;
		$carrier->deleted = 0;
		$carrier->shipping_handling = false;
		$carrier->range_behavior = 0;
		$carrier->is_module = true;
		$carrier->shipping_external = true;
		$carrier->external_module_name = 'urgentcargus';
		$carrier->need_range = true;

		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = '24 heures';
            if ($language['iso_code'] == 'ro')
				$carrier->delay[(int)$language['id_lang']] = '24 ore';
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = '24 hours';
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
				$carrier->delay[(int)$language['id_lang']] = '24 hours';
		}

		if ($carrier->add()) {
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])), 'INSERT');

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
			foreach ($zones as $zone) {
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
			}

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.png', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

            // Save Carrier ID
            Configuration::updateValue('_URGENT_CARRIER_ID_', (int)$carrier->id);

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}

    public function removeCarrier()
    {
        $carrier = new Carrier(Configuration::get('_URGENT_CARRIER_ID_', $id_lang = NULL));
        $carrier->delete();
    }

    public function calculeazaTransport($cart) {
        //error_reporting(E_ALL);
        //ini_set('display_errors', '1');

        try {
            // calculez greutatea volumetrica si liniara
            $products = $cart->getProducts();
            $volume = 0;
            $liniar = 0;
            foreach ($products as $p) {
                $volume += (($p['width'] * $p['height'] * $p['depth']) / 6000) * $p['cart_quantity'];

                if ($p['width'] > 600 || $p['height'] > 600 || $p['depth'] > 600) {
                    return 10000;
                } else if ($p['width'] > 550 || $p['height'] > 550 || $p['depth'] > 550) {
                    $liniar += (300 * $p['cart_quantity']);
                } else if ($p['width'] > 450 || $p['height'] > 450 || $p['depth'] > 450) {
                    $liniar += (200 * $p['cart_quantity']);
                } else if ($p['width'] > 350 || $p['height'] > 350 || $p['depth'] > 350) {
                    $liniar += (100 * $p['cart_quantity']);
                } else if ($p['width'] > 150 || $p['height'] > 150 || $p['depth'] > 150) {
                    $liniar += (50 * $p['cart_quantity']);
                }
            }
            $volume = ceil($volume);

            // determin greutatea
            $weight = ceil($cart->getTotalWeight()) + $liniar;
            if ($weight == 0) $weight = 1;
            if ($weight < $volume) $weight = $volume;

            // obtin id-ul monezii folosite in cos
            $id_currency = (int)$cart->id_currency;

            // obiectele pentru cursul de schimb
            $key_ron = 0;
            $currency = Currency::getCurrencies();
            foreach ($currency as $cc) {
                if (strtolower($cc['iso_code']) == 'ron') {
                    $key_ron = $cc['id_currency'];
                }
            }
            if ($key_ron == 0) {
                die('Trebuie adaugata moneda RON');
            }
            $currency_RON = new Currency($key_ron);
            $currency_DEFAULT = new Currency($id_currency);

            // obtin totalul cosului in lei
            $orderTotal = Tools::convertPriceFull($cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING), $currency_DEFAULT, $currency_RON);

            // daca totalul cosului depaseste plafonul, transportul este gratuit
            $plafon_plata_dest = Configuration::get('_URGENT_TRANSPORT_GRATUIT_', $id_lang = NULL);
            if (round($orderTotal, 2) > $plafon_plata_dest && $plafon_plata_dest > 0) {
                return 0;
            }

            // daca este ales un cost fix pentru expeditie, nu mai calculeaza transportul si returneaza costul fix
            $cost_fix_expeditie = Configuration::get('_URGENT_COST_FIX_', $id_lang = NULL);
            if ($cost_fix_expeditie != '') return round($cost_fix_expeditie, 2);

            // obtin valoarea declarata a expeditiei
            if (Configuration::get('_URGENT_ASIGURARE_', $id_lang = NULL) == '1') {
                $valoare_declarata = round($orderTotal, 2);
            } else {
                $valoare_declarata = 0;
            }

            // stabileste suma ramburs
            $suma_ramburs = round($orderTotal, 2);

            // daca se plateste cu altceva decat ramburs, suma rambursata devine 0
            $url_path = $_SERVER['REQUEST_URI'];
            if (
                !strstr($url_path, 'cashondelivery')
                && !strstr($url_path, 'cod')
                && !strstr($url_path, 'ramburs')
                && !strstr($url_path, 'cash')
                && !strstr($url_path, 'numerar')

                && strstr($url_path, 'module')
            ) {
                $suma_ramburs = 0;
            }

            // detarmin valorile pentru ramburs
            if (Configuration::get('_URGENT_TIP_RAMBURS_', $id_lang = NULL) == 'cont') {
                $ramburs_cont_colector = $suma_ramburs;
                $ramburs_cash = 0;
            } else {
                $ramburs_cont_colector = 0;
                $ramburs_cash = $suma_ramburs;
            }

            $id_address_delivery = (int)$cart->id_address_delivery;

            // obtine adresa de livrare
            if ($id_address_delivery != 0) {
                $delivery_address = new Address($id_address_delivery);
                if (!isset($delivery_address->city) || strlen($delivery_address->city) < 3) {
                    return null;
                }
            } else {
                return null;
            }

            // obtin indicativul judetului destinatarului
            $states = State::getStatesByIdCountry($delivery_address->id_country);
            $state_ISO = NULL;
            foreach ($states as $s) {
                if ($s['id_state'] == $delivery_address->id_state) {
                    $state_ISO = $s['iso_code'];
                }
            }
            if (is_null($state_ISO)) {
                die('Nu am putut obtine indicativul judetului destinatarului');
            }

            // include si instantiaza clasa urgent
            require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
            $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

            // UC login user
            $fields = array(
                'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
            );
            $token = $uc->CallMethod('LoginUser', $fields, 'POST');

            // UC punctul de ridicare default
            $location = array();
            $pickups = $uc->CallMethod('PickupLocations', array(), 'GET', $token);
            if (is_null($pickups)) {
                die('Nu exista niciun punct de ridicare asociat acestui cont!');
            }
            foreach ($pickups as $pick) {
                if (Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == $pick['LocationId']) {
                    $location = $pick;
                }
            }

            // UC shipping calculation
            $fields = array(
                'FromLocalityId' => $location['LocalityId'],
                'ToLocalityId' => 0,
                'FromCountyName' => '',
                'FromLocalityName' => '',
                'ToCountyName' => $state_ISO,
                'ToLocalityName' => $delivery_address->city,
                'Parcels' => Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) != 'plic' ? 1 : 0,
                'Envelopes' => Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) == 'plic' ? 1 : 0,
                'TotalWeight' => $weight,
                'DeclaredValue' => $valoare_declarata,
                'CashRepayment' => $ramburs_cash,
                'BankRepayment' => $ramburs_cont_colector,
                'OtherRepayment' => '',
                'PaymentInstrumentId' => 0,
                'PaymentInstrumentValue' => 0,
                'OpenPackage' => Configuration::get('_URGENT_DESCHIDERE_COLET_', $id_lang = NULL) != 1 ? false : true,
                'SaturdayDelivery' => Configuration::get('_URGENT_SAMBATA_', $id_lang = NULL) != 1 ? false : true,
                'MorningDelivery' => Configuration::get('_URGENT_DIMINEATA_', $id_lang = NULL) != 1 ? false : true,
                'ShipmentPayer' => Configuration::get('_URGENT_PLATITOR_', $id_lang = NULL) != 'expeditor' ? 2 : 1,
                'ServiceId' => Configuration::get('_URGENT_PLATITOR_', $id_lang = NULL) != 'expeditor' ? 4 : 1,
                //'PriceTableId' => Configuration::get('_URGENT_PLAN_TARIFAR_', $id_lang = NULL)
                'PriceTableId' => null
            );
            $result = $uc->CallMethod('ShippingCalculation', $fields, 'POST', $token);

            if (!isset($result['Subtotal'])) {
                return null;
            }

            if (!is_null($result)) {
                return Tools::convertPriceFull($result['Subtotal'], $currency_RON, $currency_DEFAULT);
            } else {
                echo '<pre>';
                print_r($fields);
                die();
            }

        } catch (Exception $ex) {
            print_r($ex);
            die();
        }
    }
}
?>