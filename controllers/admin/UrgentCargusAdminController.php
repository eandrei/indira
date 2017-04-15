<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

class UrgentCargusAdminController extends AdminController {
    public function __construct() {

        try {

            // verific secret key-ul
            $secret = '';
            if (isset($_GET['secret'])) {
                $secret = addslashes($_GET['secret']);
            }
            if ($secret != _COOKIE_KEY_) {
                die('Acces nepermis!');
            }

            // TRANSFORMA COMANDA IN AWB TEMPORAR
            if (isset($_GET['type']) && $_GET['type'] == 'ADDORDER') {

                // verific id-ul comenzii
                $id = 0;
                if (isset($_GET['id'])) {
                    $id = addslashes($_GET['id']);
                }
                if ($id == 0) {
                    die('Nicio comanda trimisa spre procesare!');
                }

                // obtin detaliile comenzii
                $order = new Order($id);

                // obtin adresa
                $address = new Address($order->id_address_delivery);

                // obtin detaliile clientului
                $customer = new Customer($order->id_customer);

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
                $currency_DEFAULT = new Currency($order->id_currency);

                // calculez totalul transportului inclusiv taxele
                $shipping_total = $order->total_shipping;

                // transform totalul transportului in lei
                $shipping_total = Tools::convertPriceFull($shipping_total, $currency_DEFAULT, $currency_RON);

                // calculez totalul comenzii inclusiv taxele
                $cart_total = $order->total_paid;

                // transform totalul comenzii in lei
                $cart_total = Tools::convertPriceFull($cart_total, $currency_DEFAULT, $currency_RON);

                $volume = 0;
                $liniar = 0;
                $contents = array();
                $products = $order->getCartProducts();
                foreach ($products as $p) {
                    // calculez greutatea volumetrica
                    $volume += (($p['width'] * $p['height'] * $p['depth']) / 6000) * $p['product_quantity'];

                    // calculez greutatea liniara
                    if ($p['width'] > 600 || $p['height'] > 600 || $p['depth'] > 600) {
                        die('Unul dintre produse are o latura mai mare de 600 cm');
                    } else if ($p['width'] > 550 || $p['height'] > 550 || $p['depth'] > 550) {
                        $liniar += (300 * $p['product_quantity']);
                    } else if ($p['width'] > 450 || $p['height'] > 450 || $p['depth'] > 450) {
                        $liniar += (200 * $p['product_quantity']);
                    } else if ($p['width'] > 350 || $p['height'] > 350 || $p['depth'] > 350) {
                        $liniar += (100 * $p['product_quantity']);
                    } else if ($p['width'] > 150 || $p['height'] > 150 || $p['depth'] > 150) {
                        $liniar += (50 * $p['product_quantity']);
                    }

                    // obtin produsele din comanda
                    $contents[] = $p['product_quantity'].' buc. '. str_replace('argint', '', $p['product_name']);
                }
                $volume = ceil($volume);

                // calculez greutatea totala a comenzii in kilograme
                $shipping = $order->getShipping();
                $weight = ceil($shipping[0]['weight']) + $liniar;
                if ($weight == 0) $weight = 1;
                if ($weight < $volume) $weight = $volume;

                // determin valoarea declarata
                if (Configuration::get('_URGENT_ASIGURARE_', $id_lang = NULL) != 1) {
                    $value = 0;
                } else {
                    $value = round($cart_total - $shipping_total, 2);
                }

                // determin livrarea sambata
                if (Configuration::get('_URGENT_SAMBATA_', $id_lang = NULL) != 1) {
                    $saturday = 0;
                } else {
                    $saturday = 1;
                }

                // determin livrarea dimineata
                if (Configuration::get('_URGENT_DIMINEATA_', $id_lang = NULL) != 1) {
                    $morning = 0;
                } else {
                    $morning = 1;
                }

                // determin deschidere colet
                if (Configuration::get('_URGENT_DESCHIDERE_COLET_', $id_lang = NULL) != 1) {
                    $openpackage = 0;
                } else {
                    $openpackage = 1;
                }

                // afla daca aceasta comanda a fost platita si daca nu determin rambursul si platitorul expeditiei
                if (
                    stristr($order->payment, 'cashondelivery')
                    || stristr($order->payment, 'cod')
                    || stristr($order->payment, 'ramburs')
                    || stristr($order->payment, 'cash')
                    || stristr($order->payment, 'numerar')
                    || stristr($order->payment, 'livrare')
                ) {
                    if (Configuration::get('_URGENT_PLATITOR_', $id_lang = NULL) != 'expeditor') {
                        $payer = 2;
                    } else {
                        $payer = 1;
                    }
                    if (Configuration::get('_URGENT_TIP_RAMBURS_', $id_lang = NULL) != 'cont') {
                        if ($payer == 1) {
                            $cash_repayment = round($cart_total, 2);
                        } else {
                            $cash_repayment = round($cart_total - $shipping_total, 2);
                        }
                        $bank_repayment = 0;
                    } else {
                        $cash_repayment = 0;
                        if ($payer == 1) {
                            $bank_repayment = round($cart_total, 2);
                        } else {
                            $bank_repayment = round($cart_total - $shipping_total, 2);
                        }
                    }
                } else {
                    $bank_repayment = 0;
                    $cash_repayment = 0;
                    $payer = 1;
                }

                // daca transportul este gratuit, serviciul este platit de expeditor
                if ($shipping_total == 0) {
                    $payer = 1;
                }

                // obtin indicativul judetului destinatarului
                $states = State::getStatesByIdCountry($address->id_country);
                $state_ISO = NULL;
                foreach ($states as $s) {
                    if ($s['id_state'] == $address->id_state) {
                        $state_ISO = $s['iso_code'];
                    }
                }
                if (is_null($state_ISO)) {
                    die('Nu am putut obtine indicativul judetului destinatarului');
                }

                $awbName = trim(implode(' ', array($address->lastname, $address->firstname)));

                // adaug awb-ul in baza de date
                $sql = "INSERT INTO awb_urgent_cargus SET
                                order_id = '".$id."',
                                pickup_id = '".addslashes(Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL))."',
                                name = '".addslashes($awbName)."',
                                locality_id = '0',
                                locality_name = '".addslashes($address->city)."',
                                county_id = '0',
                                county_name = '".addslashes($state_ISO)."',
                                street_id = '0',
                                street_name = '',
                                number = '',
                                address = '".addslashes(htmlentities(trim(implode('; ', array($address->address1, $address->address2)), '; ')))."',
                                contact = '".addslashes(trim(implode(' ', array($address->lastname, $address->firstname))))."',
                                phone = '".addslashes(trim(implode('; ', array($address->phone, $address->phone_mobile)), '; '))."',
                                email = '".addslashes($customer->email)."',
                                parcels = '".(Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) != 'plic' ? 1 : 0)."',
                                envelopes = '".(Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) == 'plic' ? 1 : 0)."',
                                weight = '".addslashes($weight)."',
                                value = '".addslashes($value)."',
                                cash_repayment = '".addslashes($cash_repayment)."',
                                bank_repayment = '".addslashes($bank_repayment)."',
                                other_repayment = '',
                                payer = '".addslashes($payer)."',
                                morning_delivery = '".addslashes($morning)."',
                                saturday_delivery = '".addslashes($saturday)."',
                                openpackage = '".addslashes($openpackage)."',
                                observations = '',
                                contents = '".addslashes(htmlentities(trim(implode('; ', $contents), '; ')))."',
                                barcode = '0'
                            ";
                $result = Db::getInstance()->execute($sql);
                if ($result == 1) {
                    echo 'ok';
                } else {
                    echo 'Eroare la inserarea datelor in baza!';
                }
            }

            // PRINTEAZA AWB-uri VALIDATE
            if (isset($_GET['type']) && $_GET['type'] == 'PRINTAWB') {

                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // UC print
                $print = $uc->CallMethod('AwbDocuments?type=PDF&format=0&barCodes='.addslashes($_GET['codes']), array(), 'GET', $token);

                header('Content-type:application/pdf');
                echo base64_decode($print);
                die();
            }

            // TRIMITE COMANDA CURENTA
            if (isset($_GET['type']) && $_GET['type'] == 'SENDORDER') {

                $data = array();
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone('Europe/Bucharest'));
                $today = $date->format('Y-m-d H:i:s');
                if (isset($_GET['date'])) {
                    $d = explode('.', Tools::getValue('date'));
                    $date->setDate($d[2], $d[1], $d[0]);
                }
                $cd = $date->format('Y-m-d H:i:s');

                if (date('w', strtotime($cd)) == 0) { // duminica
                    $date = date('d.m.Y', strtotime($cd.' +1 day'));
                    $h_start = 13;
                    $h_end = 18;
                    $h2_start = 14;
                    $h2_end = 19;
                } else if (date('w', strtotime($cd)) == 1 || date('w', strtotime($cd)) == 2 || date('w', strtotime($cd)) == 3 || date('w', strtotime($cd)) == 4) { // luni, marti, miercuri si joi
                    if ($cd == $today) {
                        if (date('H', strtotime($cd)) > 18) {
                            $date = date('d.m.Y', strtotime($cd.' +1 day'));
                            $h_start = 13;
                            $h_end = 18;
                            $h2_start = 14;
                            $h2_end = 19;
                        } else if (date('H', strtotime($cd)) == 18) {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = 18;
                            $h_end = 18;
                            $h2_start = 19;
                            $h2_end = 19;
                        } else {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = date('H', strtotime($cd)) + 1;
                            $h_end = 18;
                            $h2_start = date('H', strtotime($cd)) + 2;
                            $h2_end = 19;
                        }
                    } else {
                        $date = date('d.m.Y', strtotime($cd));
                        $h_start = 13;
                        $h_end = 18;
                        $h2_start = 14;
                        $h2_end = 19;
                    }
                } else if (date('w', strtotime($cd)) == 5) { // vineri
                    if ($cd == $today) {
                        if (date('H', strtotime($cd)) > 18) {
                            $date = date('d.m.Y', strtotime($cd.' +1 day'));
                            $h_start = 13;
                            $h_end = 14;
                            $h2_start = 14;
                            $h2_end = 15;
                        } else if (date('H', strtotime($cd)) == 18) {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = 18;
                            $h_end = 18;
                            $h2_start = 19;
                            $h2_end = 19;
                        } else {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = date('H', strtotime($cd)) + 1;
                            $h_end = 18;
                            $h2_start = date('H', strtotime($cd)) + 2;
                            $h2_end = 19;
                        }
                    } else {
                        $date = date('d.m.Y', strtotime($cd));
                        $h_start = 13;
                        $h_end = 18;
                        $h2_start = 14;
                        $h2_end = 19;
                    }
                } else if (date('w', strtotime($cd)) == 6) { // sambata
                    if ($cd == $today) {
                        if (date('H', strtotime($cd)) > 14) {
                            $date = date('d.m.Y', strtotime($cd.' +2 day'));
                            $h_start = 13;
                            $h_end = 18;
                            $h2_start = 14;
                            $h2_end = 19;
                        } else if (date('H', strtotime($cd)) == 14) {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = 14;
                            $h_end = 14;
                            $h2_start = 15;
                            $h2_end = 15;
                        } else {
                            $date = date('d.m.Y', strtotime($cd));
                            $h_start = date('H', strtotime($cd)) + 1;
                            $h_end = 14;
                            $h2_start = date('H', strtotime($cd)) + 2;
                            $h2_end = 15;
                        }
                    } else {
                        $date = date('d.m.Y', strtotime($cd));
                        $h_start = 13;
                        $h_end = 14;
                        $h2_start = 14;
                        $h2_end = 15;
                    }
                }

                $data['date'] = $date;

                if (isset($_GET['hour'])) {
                    $h = explode(':', Tools::getValue('hour'));
                    $h2_start = $h[0] + 1;
                    $hour = Tools::getValue('hour');
                } else {
                    $hour = false;
                }

                $html = '';
                for ($i = $h_start; $i <= $h_end; $i++) {
                    $html .= '<option'.($hour == $i.':00' ? ' selected="selected"' : '').'>'.$i.':00</option>';
                }
                $data['h_dela'] = $html;

                $html = '';
                for ($i = $h2_start; $i <= $h2_end; $i++) {
                    $html .= '<option>'.$i.':00</option>';
                }
                $data['h_panala'] = $html;
?>

                <link rel="stylesheet" href="themes/default/css/admin-theme.css" />
                <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
                <script src="//code.jquery.com/jquery-1.10.2.js"></script>
                <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

                <script>
                    $(function() {
                        $('#datepicker').datepicker({
                            minDate: 0,
                            firstDay: 1,
                            dateFormat: 'dd.mm.yy',
                            beforeShowDay: function(date) {
                                var day = date.getDay();
                                return [(day != 0), ''];
                            }
                        });

                        $('#datepicker').change(function(){
                            window.location = "index.php?controller=UrgentCargusAdmin&token=true&type=SENDORDER&secret=<?php echo _COOKIE_KEY_; ?>" + "&date=" + $(this).val();
                        });

                        $('select[name="hour_from"]').change(function(){
                            window.location = "index.php?controller=UrgentCargusAdmin&token=true&type=SENDORDER&secret=<?php echo _COOKIE_KEY_; ?>" + "&date=" + $('#datepicker').val() + "&hour=" + $(this).val();
                        });
                    });
                </script>
                <style>
                    .page-sidebar #content {
                        margin-left: 0px !important;
                    }
                    .bootstrap .page-head h2.page-title {
                        padding-left: 20px !important;
                    }
                </style>
                <body class="ps_back-office page-sidebar adminurgentcarguslivrari">
                    <header id="header" class="bootstrap">
                        <nav id="header_infos" role="navigation">
                            <div class="navbar-header">
                                <a id="header_shopversion" href="#"></a>
                                <a id="header_shopname" href="#">Urgent Cargus SA</a>
                            </div>
                        </nav>
                    </header>
                    <div id="main">
                        <div id="content" class="bootstrap">
                            <div class="bootstrap">
                                <div class="page-head">
                                    <h2 class="page-title">
                                        Intervalul de ridicare
                                    </h2>
                                </div>
                            </div>
                            <div class="entry-edit">
                                <div class="panel">
                                    <div class="panel-heading"><i class="icon-align-justify"></i> Va rugam sa alegeti data si intervalul orar pentru ridicarea comenzii</div>
                                    <form action="index.php?controller=UrgentCargusAdmin&token=true&type=COMPLETEORDER&secret=<?php echo _COOKIE_KEY_; ?>" method="post" enctype="multipart/form-data" id="form">
                                        <input class="form-control" name="date" type="text" id="datepicker" value="<?php echo $data['date']; ?>" style="width:200px; float:left; margin-right:10px;" />
                                        <select class="form-control" name="hour_from" style="width:90px; float:left; margin-right:10px;">
                                            <?php echo $data['h_dela']; ?>
                                        </select>
                                        <select class="form-control" name="hour_to" style="width:90px; float:left; margin-right:10px;">
                                            <?php echo $data['h_panala']; ?>
                                        </select>
                                        <button type="submit" value="submit" class="btn btn-primary">
                                            <i class="icon-plus-sign"></i>Trimite comanda
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>

                <?php
            }

            // FINALIZEAZA COMANDA CURENTA
            if (isset($_GET['type']) && $_GET['type'] == 'COMPLETEORDER') {

                $d = explode('.', Tools::getValue('date'));
                $from = $d[2].'-'.$d[1].'-'.$d[0].' '.Tools::getValue('hour_from').':00';
                $to = $d[2].'-'.$d[1].'-'.$d[0].' '.Tools::getValue('hour_to').':00';

                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // UC send order
                $order_id = $uc->CallMethod('Orders?locationId='.Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL).'&PickupStartDate='.date('Y-m-d%20H:i:s', strtotime($from)).'&PickupEndDate='.date('Y-m-d%20H:i:s', strtotime($to)).'&action=1', array(), 'PUT', $token);

                // trimite email cu link-ul pentru tracking
                $awbs = $uc->CallMethod('Awbs?orderId='.$order_id, array(), 'GET', $token);
                echo '<pre>';
                foreach ($awbs as $a) {
                    if ($a['Status'] != 'Deleted') {
                        $data = Db::getInstance()->ExecuteS("SELECT
                                                                    c.firstname,
                                                                    c.lastname,
                                                                    c.email,
                                                                    o.id_order,
                                                                    o.date_add
                                                                FROM
                                                                    "._DB_PREFIX_."customer c,
                                                                    "._DB_PREFIX_."orders o,
                                                                    awb_urgent_cargus u
                                                                WHERE
                                                                    u.barcode = '".$a['BarCode']."'
                                                                    AND u.order_id = o.id_order
                                                                    AND o.id_customer = c.id_customer");

                        $templateVars['{firstname}'] = $data[0]['firstname'];
                        $templateVars['{lastname}'] = $data[0]['lastname'];
                        $templateVars['{id_order}'] = $data[0]['id_order'];
                        $templateVars['{order_date}'] = date('d.m.Y H:i', strtotime($data[0]['date_add']));
                        $templateVars['{awb}'] = $a['BarCode'];

                        global $cookie;
                        $id_lang = $cookie->id_lang;
                        $template_name = 'urgent_awb';
                        $title = Mail::l('Comanda ridicata de Urgent Cargus');
                        $from = Configuration::get('PS_SHOP_EMAIL');
                        $fromName = Configuration::get('PS_SHOP_NAME');
                        $mailDir = PS_ADMIN_DIR.'/../mails/';
                        $toName = $data[0]['firstname'].' '.$data[0]['lastname'];
                        $send = Mail::Send($id_lang, $template_name, $title, $templateVars, $data[0]['email'], $toName, $from, $fromName, NULL, NULL, $mailDir);
                    }
                }

                // UC print borderou
                echo '<script>window.opener.location.reload(); window.resizeTo(916, 669); window.location = "index.php?controller=UrgentCargusAdmin&token=true&type=PRINTBORDEROU&secret='._COOKIE_KEY_.'&orderId='.$order_id.'";</script>';
            }

            // PRINT BORDEROU COMANDA CURENTA
            if (isset($_GET['type']) && $_GET['type'] == 'PRINTBORDEROU') {

                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // UC print borderou
                $borderou = $uc->CallMethod('OrderDocuments?orderId='.Tools::getValue('orderId').'&docType=0', array(), 'GET', $token);

                header('Content-type:application/pdf');
                echo base64_decode($borderou);
                die();

            }

        } catch (Exception $ex) {
            print_r($ex);
        }

        die();
    }
}