<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 22/04/17
 * Time: 22:36
 */
//require(dirname(__FILE__).'/indira/config/config.inc.php');

require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');

class UrgentCargus {

    public $cargusClient;

    public $cargusToken;

    public function __construct() {
        $this->cargusClient = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

        // UC login user
        $fields = array(
            'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
            'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
        );
        $this->cargusToken = $this->cargusClient->CallMethod('LoginUser', $fields, 'POST');
    }

    public function getCash($awb) {

        if (! $awb) {
            throw new Exception("Awb number not provided ");
        }

       return $this->cargusClient->CallMethod("CashAccount?barcode=$awb",  array(), 'GET', $this->cargusToken);
    }

    public function getAwb($awb)
    {
        if (! $awb) {
            throw new Exception("Awb number not provided ");
        }

        return $this->cargusClient->CallMethod("Awbs?barcode=$awb",  array(), 'GET', $this->cargusToken);
    }

    public function getAwbTracking($awb = null, $from = null, $to = null)
    {

        if (! $from) {
            $from = (new DateTime())->modify('-24 hours')->format("Y-m-d");
        }

        if (! $to) {
            $to = (new DateTime())->format("Y-m-d");
        }

        $packages = $this->cargusClient->CallMethod("AwbTrace/GetDeltaEvents?FromDate=$from" ."T00:00:00-05:00&ToDate=$to" ."T00:00:00-05:00",  array(), 'GET', $this->cargusToken);

        $result = [];
        foreach ($packages as $package) {
            $result[$package['BarCode']] = [
                'status' => $package['StatusExpression'],
                'cashedin_id' => $package['DeductionId'],
                'value' => $package['RepaymentValue']
            ];
        }
        if ($awb && ! isset($result[$awb])) {
            return null;
        }

        if ($awb && isset($result[$awb])) {

            return $result[$awb];
        }

        return $result;
    }

    /**
     * If AWB status is Tiparit then means is shipped to challenge later
     *
     * @param $order
     * @return bool
     */
    public function isShipped($order)
    {
        $awbStatus = $this->getAwb($order->shipping_number);

        if ($awbStatus[0]['Status'] == 'Tiparit') {
            return True;
        }
        return False;
    }

    /**
     * If AWB status is Tiparit then means is shipped to challenge later
     *
     * @param $order
     * @return bool
     */
    public function isDelivered($order)
    {
        $awbStatus = $this->getAwbTracking($order->shipping_number);

        if (! is_array($awbStatus)) {
            return false;
        }

        if ($awbStatus['status'] == 'Confirmat') {
            return True;
        }

        return False;
    }


}
