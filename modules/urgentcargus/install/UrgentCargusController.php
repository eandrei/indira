<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

class UrgentCargusController extends FrontController {
    public $php_self = 'urgentcargus';

	public function init() {
	    if (isset($_GET['judet'])) {
            
            // include si instantiaza clasa urgent
            require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
            $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

            // UC login user
            $fields = array(
                'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
            );
            $token = $uc->CallMethod('LoginUser', $fields, 'POST');

            if (is_numeric(addslashes($_GET['judet']))) {
                $states = State::getStatesByIdCountry(36);
                $state_ISO = NULL;
                foreach ($states as $s) {
                    if ($s['id_state'] == addslashes($_GET['judet'])) {
                        $state_ISO = trim(strtolower($s['iso_code']));
                    }
                }
            } else {
                $state_ISO = addslashes(trim(strtolower($_GET['judet'])));
            }

            if ($state_ISO == 'b') {

                echo '<option value="Bucuresti" km="0">Bucuresti</option>';

            } else {

                $countyId = NULL;
                $counties = array();
                $counties = $uc->CallMethod('Counties?countryId=1', array(), 'GET', $token);
                foreach ($counties as $c) {
                    if (trim(strtolower($c['Abbreviation'])) == $state_ISO) {
                        $countyId = trim($c['CountyId']);
                    }
                }

                if (is_null($countyId)) {
                    echo '<option value="" km="0">ERROR: Nu am putut obtine indicativul judetului</option>';
                    die();
                } else {
                    $localities = $uc->CallMethod('Localities?countryId=1&countyId='.$countyId, array(), 'GET', $token);

                    if (count($localities) > 1) {
                        echo '<option value="" km="0">-</option>'."\n";
                    }

                    $val = '';
                    if (isset($_GET['val'])) {
                        $val = trim(strtolower(addslashes($_GET['val'])));
                    }

                    foreach ($localities as $l) {
                        echo '<option'.($val == trim(strtolower($l['Name'])) ? ' selected="selected"' : '').' km="'.($l['InNetwork'] ? 0 : $l['ExtraKm'] ? $l['ExtraKm'] : 0).'">'.$l['Name'].'</option>'."\n";
                    }
                }

            }
        }

        die();
	}
}
?>