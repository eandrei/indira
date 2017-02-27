<?php
session_start();

class AdminUrgentCargusLivrari extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        if (Tools::isSubmit('submitPickup')) {
            Configuration::updateValue('_URGENT_PUNCT_RIDICARE_', Tools::getValue('_URGENT_PUNCT_RIDICARE_'));

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        // VALIDEAZA AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_valideaza')) {
            $errors = array();
            $success = array();
            foreach ($_POST['selected'] as $id) {
                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // UC create awb
                $row = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE barcode = '0' AND id = '".addslashes($id)."'");
                $fields = array(
                    'Sender' => array(
                        'LocationId' => $row[0]['pickup_id']
                    ),
                    'Recipient' => array(
                        'LocationId' => null,
                        'Name' => $row[0]['name'],
                        'CountyId' => null,
                        'CountyName' => $row[0]['county_name'],
                        'LocalityId' => null,
                        'LocalityName' => $row[0]['locality_name'],
                        'StreetId' => null,
                        'StreetName' => '-',
                        'AddressText' => $row[0]['address'],
                        'ContactPerson' => $row[0]['contact'],
                        'PhoneNumber' => $row[0]['phone'],
                        'Email' => $row[0]['email']
                    ),
                    'Parcels' => $row[0]['parcels'],
                    'Envelopes' => $row[0]['envelopes'],
                    'TotalWeight' => $row[0]['weight'],
                    'DeclaredValue' => $row[0]['value'],
                    'CashRepayment' => $row[0]['cash_repayment'],
                    'BankRepayment' => $row[0]['bank_repayment'],
                    'OtherRepayment' => $row[0]['other_repayment'],
                    'OpenPackage' => $row[0]['openpackage'] == 1 ? true : false,
                    //'PriceTableId' => Configuration::get('_URGENT_PLAN_TARIFAR_', $id_lang = NULL),
                    'PriceTableId' => null,
                    'ShipmentPayer' => $row[0]['payer'],
                    'ServiceId' => ($row[0]['payer'] != 1 ? 4 : 1),
                    'MorningDelivery' => $row[0]['morning_delivery'] == 1 ? true : false,
                    'SaturdayDelivery' => $row[0]['saturday_delivery'] == 1 ? true : false,
                    'Observations' => $row[0]['observations'],
                    'PackageContent' => $row[0]['contents'],
                    'CustomString' => $row[0]['order_id']
                );

                $barcode = $uc->CallMethod('Awbs', $fields, 'POST', $token);
                if (is_null($barcode)) {
                    $errors[] = addslashes($id);
                } else {
                    $update = Db::getInstance()->execute("UPDATE awb_urgent_cargus SET barcode = '".$barcode."' WHERE id = '".addslashes($id)."'");
                    if ($update == 1) {
                        $success[] = addslashes($id);
                    } else {
                        $errors[] = addslashes($id);
                    }
                }
            }

            if (count($errors) == 0) {
                $success = array('Toate AWB-urile selectate au fost validate cu succes!');
            } else if (count($success) == 0) {
                $errors = array('Niciun AWB selectat nu a putut fi validat!');
            } else {
                $success = array('ATENTIE: Doar o parte din AWB-urile selectate au fost validate cu succes!');
                $errors = array('Nu a fost posibila validarea urmatoarelor comenzi: '.implode(', ', $errors));
            }

            $_SESSION['post_status'] = array(
                'success' => $success,
                'errors' => $errors
            );

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        // STERGE AWB-urile AFLATE IN ASTEPTARE
        if (Tools::isSubmit('submit_sterge')) {
            $errors = array();
            $success = array();
            foreach ($_POST['selected'] as $id) {
                $delete = Db::getInstance()->execute("DELETE FROM awb_urgent_cargus WHERE id = '".addslashes($id)."'");
                if ($delete == 1) {
                    $success[] = addslashes($id);
                } else {
                    $errors[] = addslashes($id);
                }
            }

            if (count($errors) == 0) {
                $success = array('Toate AWB-urile selectate au fost sterse cu succes!');
            } else if (count($success) == 0) {
                $errors = array('Niciun AWB selectat nu a putut fi sters!');
            } else {
                $success = array('ATENTIE: Doar o parte din AWB-urile selectate au fost sterse cu succes!');
                $errors = array('Nu a fost posibila stergerea urmatoarelor comenzi: '.implode(', ', $errors));
            }

            $_SESSION['post_status'] = array(
                'success' => $success,
                'errors' => $errors
            );

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }

        // DEZACTIVEAZA AWB-urile DEJA VALIDATE
        if (Tools::isSubmit('submit_dezactiveaza')) {
            $errors = array();
            $success = array();
            foreach ($_POST['awbs'] as $barcode) {
                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // UC delete awb
                $result = $uc->CallMethod('Awbs?barCode=' . addslashes($barcode), array(), 'DELETE', $token);
                if ($result == 1) {
                    $update = Db::getInstance()->execute("UPDATE awb_urgent_cargus SET barcode = '0' WHERE barcode = '".addslashes($barcode)."'");
                    if ($update == 1) {
                        $success[] = addslashes($barcode);
                    } else {
                        $errors[] = addslashes($barcode);
                    }
                } else {
                    $errors[] = addslashes($barcode);
                }
            }

            if (count($errors) == 0) {
                $success = array('Toate AWB-urile selectate au fost dezactivate cu succes!');
            } else if (count($success) == 0) {
                $errors = array('Niciun AWB selectat nu a putut fi dezactivat!');
            } else {
                $success = array('ATENTIE: Doar o parte din AWB-urile selectate au fost dezactivate cu succes!');
                $errors = array('Nu a fost posibila dezactivarea urmatoarelor AWB-uri: '.implode(', ', $errors));
            }

            $_SESSION['post_status'] = array(
                'success' => $success,
                'errors' => $errors
            );

            ob_end_clean();
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }
    }

    public function display() { ?>

        <script>
            $(function () {
                $('#content').removeClass('nobootstrap').addClass('bootstrap');
            });
        </script>

        <style>
            .icon-AdminUrgentCargus:before {
                content: "\f0d1";
            }

            label {
                width: 220px;
                font-weight: normal;
                padding: 0.4em 0.3em 0 0;
            }

            input[type="text"] {
                margin-bottom: 3px;
                width: 250px;
            }

            #edit_form span {
                color: #666;
                font-size: 11px;
                line-height: 0px;
            }
        </style>

        <div class="entry-edit">
            <form id="edit_form" class="form-horizontal" name="edit_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

                <?php if (Configuration::get('_URGENT_USERNAME_', $id_lang = NULL) == '' || Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL) == '') { ?>

                <div class="panel">Va rugam sa completati username-ul si parola in pagina de configurare a modulului!</div>

                <?php } else {
                
                // include si instantiaza clasa urgent
                require_once(_PS_MODULE_DIR_.'/urgentcargus/urgentcargus.class.php');
                $uc = new UrgentClass(Configuration::get('_URGENT_APIURL_', $id_lang = NULL), Configuration::get('_URGENT_APIKEY_', $id_lang = NULL));

                // UC login user
                $fields = array(
                    'UserName' => Configuration::get('_URGENT_USERNAME_', $id_lang = NULL),
                    'Password' => Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL)
                );
                $token = $uc->CallMethod('LoginUser', $fields, 'POST');

                // obtine lista punctelor de ridicare
                $pickups = array();
                $result = $uc->CallMethod('PickupLocations', array(), 'GET', $token);
                if (is_null($result)) {
                    die('<div class="panel">Nu exista niciun punct de ridicare asociat acestui cont!</div></form></div>');
                } else {
                    foreach ($result as $row) {
                        $pickups[$row['LocationId']] = $row;
                    }
                }
                if (Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == '' && count($pickups) > 0) {
                    Configuration::updateValue('_URGENT_PUNCT_RIDICARE_', $pickups[0]['LocationId']);
                }

                // UC get comanda curenta
                $orders = $uc->CallMethod('Orders?locationId='.Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL).'&status=0&pageNumber=1&itemsPerPage=1000', array(), 'GET', $token);

                // UC get awb-uri curente
                $awb = array();
                if (!is_null($orders)) {
                    $result = $uc->CallMethod('Awbs?&orderId='.(isset($orders['OrderId']) == 1 ? $orders['OrderId'] : $orders[0]['OrderId']), array(), 'GET', $token);
                    if (!is_null($result)) {
                        foreach ($result as $t) {
                            if ($t['Status'] != 'Deleted') {
                                $awb[] = $t;
                            }
                        }
                    }
                }

                // get comenzi in asteptare
                $lines = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE barcode = '0'");
                ?>

                <div id="alert_zone">
                <?php
                if (isset($_SESSION['post_status'])) {
                    if (count($_SESSION['post_status']['success']) > 0) {
                        echo '<div class="bootstrap"><div class="alert alert-success" style="display:block;">'.implode('<br/>', $_SESSION['post_status']['success']).'</div></div>';
                    }
                    if (count($_SESSION['post_status']['errors']) > 0) {
                        echo '<div class="bootstrap"><div class="alert alert-danger" style="display:block;">'.implode('<br/>', $_SESSION['post_status']['errors']).'</div></div>';
                    }
                    unset($_SESSION['post_status']);
                } ?>
                </div>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> AWB-uri in asteptare</div>
                    <?php if (count($lines) == 0) { echo 'Nu exista niciun AWB in asteptare!'; } else { ?>

                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                            <thead>
                                <tr class="nodrag nodrop">
                                    <th><input style="position:absolute; margin-top:-6px;" type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
                                    <th><span class="title_box active">ID comanda</span></th>
                                    <th><span class="title_box active">Punct de ridicare</span></th>
                                    <th><span class="title_box active">Nume destinatar</span></th>
                                    <th><span class="title_box active">Localitate destinatar</span></th>
                                    <th><span class="title_box active">Plicuri</span></th>
                                    <th><span class="title_box active">Colete</span></th>
                                    <th><span class="title_box active">Greutate</span></th>
                                    <th><span class="title_box active">Ramburs numerar</span></th>
                                    <th><span class="title_box active">Ramburs cont colector</span></th>
                                    <th><span class="title_box active">Platitor expeditie</span></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < count($lines); $i++) { ?>
                                <tr class="<?php echo $i % 2 == 0 ? 'even' : 'odd' ?>">
                                    <td><input style="position:absolute; margin-top:-6px;" type="checkbox" name="selected[]" value="<?php echo $lines[$i]['id']; ?>" /></td>
                                    <td><?php echo $lines[$i]['order_id']; ?></td>
                                    <td><?php echo isset($pickups[$lines[$i]['pickup_id']]['Name']) ? $pickups[$lines[$i]['pickup_id']]['Name'] : '-/-'; ?></td>
                                    <td><?php echo $lines[$i]['name']; ?></td>
                                    <td><?php echo $lines[$i]['locality_name'].($lines[$i]['county_name'] ? ', ' : '').$lines[$i]['county_name']; ?></td>
                                    <td><?php echo $lines[$i]['envelopes']; ?></td>
                                    <td><?php echo $lines[$i]['parcels']; ?></td>
                                    <td><?php echo $lines[$i]['weight']; ?> kg</td>
                                    <td><?php echo $lines[$i]['cash_repayment']; ?> lei</td>
                                    <td><?php echo $lines[$i]['bank_repayment']; ?> lei</td>
                                    <td><?php echo $lines[$i]['payer'] == 2 ? 'Destinatar' : 'Expeditor'; ?></td>
                                    <td>
                                        <div class="btn-group-action">
                                            <div class="btn-group pull-right">
                                                <a href="index.php?controller=AdminUrgentCargusEditareAwb&token=<?php echo Tools::getAdminTokenLite('AdminUrgentCargusEditareAwb'); ?>&Id=<?php echo $lines[$i]['id']; ?>" title="Editare" class="edit btn btn-default">
                                                    <i class="icon-pencil"></i> Editare
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="btn-group bulk-actions dropup">
                        <button type="submit" name="submit_valideaza" value="submit" class="btn btn-primary">
                            <i class="icon-plus-sign"></i> Valideaza AWB-urile selectate
                        </button>
                        <button type="submit" name="submit_sterge" value="submit" class="btn btn-default">
                            <i class="icon-trash"></i> Sterge AWB-urile selectate
                        </button>
                    </div>
                    <script>
                        // VALIDEAZA AWB-urile SELECTATE
                        $('[name="submit_valideaza"]').click(function () {
                            $('#alert_zone').html('');
                            var coduri = new Array();
                            $('input[name*=\'selected\']:checked').each(function () {
                                coduri.push($(this).val());
                            });
                            if (coduri.length > 0) {
                                return true;
                            } else {
                                $('#alert_zone').html('<div class="bootstrap"><div class="alert alert-danger" style="display:block;">Nu ati selectat niciun AWB pentru validare!</div></div></div>');
                            }
                            return false;
                        });

                        // STERGE AWB-urile SELECTATE
                        $('[name="submit_sterge"]').click(function () {
                            $('#alert_zone').html('');
                            var coduri = new Array();
                            $('input[name*=\'selected\']:checked').each(function () {
                                coduri.push($(this).val());
                            });
                            if (coduri.length > 0) {
                                return true;
                            } else {
                                $('#alert_zone').html('<div class="bootstrap"><div class="alert alert-danger" style="display:block;">Nu ati selectat niciun AWB pentru stergere!</div></div></div>');
                            }
                            return false;
                        });
                    </script>

                    <?php } ?>
                </div>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> AWB-uri validate</div>
                    <?php if (count($awb) == 0) { ?>

                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                            <thead>
                                <tr class="nodrag nodrop">
                                    <th></th>
                                </tr>
                                <tr class="nodrag nodrop filter row_hover">
                                    <th>
                                        <div style="float:left; height:31px; line-height:31px; padding-right:5px;">Punctul de ridicare</div>
                                        <select name="_URGENT_PUNCT_RIDICARE_" class="filter center" style="width:200px; float:left;">
                                            <?php
                                            foreach ($pickups as $pick) {
                                                echo '<option '.(Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == $pick['LocationId'] ? 'selected="selected"' : '').' value="'.$pick['LocationId'].'">'.$pick['Name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                        <button type="submit" name="submitPickup" value="submit" class="btn btn-default" style="float:left; margin-left:5px;">
                                            <i class="icon-pencil"></i> Schimba punctul de ridicare implicit
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <br />
                    Nu exista niciun AWB validat pentru punctul curent de ridicare!
            
                    <?php } else { ?>

                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                            <thead>
                                <tr class="nodrag nodrop">
                                    <th><input style="position:absolute; margin-top:-6px;" type="checkbox" onclick="$('input[name*=\'awbs\']').prop('checked', this.checked);" /></th>
                                    <th><span class="title_box active">ID comanda</span></th>
                                    <th><span class="title_box active">Serie AWB</span></th>
                                    <th><span class="title_box active">Cost livrare</span></th>
                                    <th><span class="title_box active">Nume destinatar</span></th>
                                    <th><span class="title_box active">Localitate destinatar</span></th>
                                    <th><span class="title_box active">Plicuri</span></th>
                                    <th><span class="title_box active">Colete</span></th>
                                    <th><span class="title_box active">Greutate</span></th>
                                    <th><span class="title_box active">Ramburs numerar</span></th>
                                    <th><span class="title_box active">Ramburs cont colector</span></th>
                                    <th><span class="title_box active">Platitor expeditie</span></th>
                                    <th><span class="title_box active">Status</span></th>
                                </tr>
                                <tr class="nodrag nodrop filter row_hover">
                                    <th colspan="13">
                                        <div style="float:left; height:31px; line-height:31px; padding-right:5px;">Punctul de ridicare</div>
                                        <select name="_URGENT_PUNCT_RIDICARE_" class="filter center" style="width:200px; float:left;">
                                            <?php
                                            foreach ($pickups as $pick) {
                                                echo '<option '.(Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == $pick['LocationId'] ? 'selected="selected"' : '').' value="'.$pick['LocationId'].'">'.$pick['Name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                        <button type="submit" name="submitPickup" value="submit" class="btn btn-default" style="float:left; margin-left:5px;">
									        <i class="icon-pencil"></i> Schimba punctul de ridicare implicit
								        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < count($awb); $i++) { ?>
                                <tr class="<?php echo $i % 2 == 0 ? 'even' : 'odd' ?>">
                                    <td height="38"><input style="position:absolute; margin-top:-6px;" type="checkbox" name="awbs[]" value="<?php echo $awb[$i]['BarCode']; ?>" /></td>
                                    <td><?php echo $awb[$i]['CustomString']; ?></td>
                                    <td><?php echo $awb[$i]['BarCode']; ?></td>
                                    <td><?php echo $awb[$i]['ShippingCost']['GrandTotal']; ?> lei</td>
                                    <td><?php echo $awb[$i]['Recipient']['Name']; ?></td>
                                    <td><?php echo $awb[$i]['Recipient']['LocalityName']; ?></td>
                                    <td><?php echo $awb[$i]['Envelopes']; ?></td>
                                    <td><?php echo $awb[$i]['Parcels']; ?></td>
                                    <td><?php echo $awb[$i]['TotalWeight']; ?> kg</td>
                                    <td><?php echo $awb[$i]['CashRepayment']; ?> lei</td>
                                    <td><?php echo $awb[$i]['BankRepayment']; ?> lei</td>
                                    <td><?php echo $awb[$i]['ShipmentPayer'] == 2 ? 'Destinatar' : 'Expeditor'; ?></td>
                                    <td><?php echo $awb[$i]['Status']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="btn-group bulk-actions dropup">
                        <button type="submit" name="submit_printeaza" value="submit" class="btn btn-primary">
                            <i class="icon-plus-sign"></i> Printeaza AWB-urile selectate
                        </button>
                        <button type="submit" name="submit_trimite" value="submit" class="btn btn-default">
                            <i class="icon-plus-sign"></i> Trimite comanda curenta
                        </button>
                        <button type="submit" name="submit_dezactiveaza" value="submit" class="btn btn-default">
                            <i class="icon-trash"></i> Dezactiveaza AWB-urile selectate
                        </button>
                    </div>
                    <script>
                        // PRINTEAZA AWB-urile SELECTATE
                        $('[name="submit_printeaza"]').click(function () {
                            $('#alert_zone').html('');
                            var coduri = new Array();
                            $('input[name*=\'awbs\']:checked').each(function () {
                                coduri.push($(this).val());
                            });
                            if (coduri.length > 0) {
                                window.open('index.php?controller=UrgentCargusAdmin&token=true&type=PRINTAWB&secret=<?php echo _COOKIE_KEY_; ?>&codes=[' + coduri.join(',') + ']', '', 'width=900, height=600, left=50, top=50');
                            } else {
                                $('#alert_zone').html('<div class="bootstrap"><div class="alert alert-danger" style="display:block;">Nu ati selectat niciun AWB pentru printare!</div></div></div>');
                            }
                            return false;
                        });

                        // TRIMITE COMANDA CURENTA
                        $('[name="submit_trimite"]').click(function () {
                            $('#alert_zone').html('');
                            window.open('index.php?controller=UrgentCargusAdmin&token=true&type=SENDORDER&secret=<?php echo _COOKIE_KEY_; ?>', '', 'width=900, height=600, left=50, top=50');
                            return false;
                        });

                        // DEZACTIVEAZA AWB-urile SELECTATE
                        $('[name="submit_dezactiveaza"]').click(function () {
                            $('#alert_zone').html('');
                            var coduri = new Array();
                            $('input[name*=\'awbs\']:checked').each(function () {
                                coduri.push($(this).val());
                            });
                            if (coduri.length > 0) {
                                return true;
                            } else {
                                $('#alert_zone').html('<div class="bootstrap"><div class="alert alert-danger" style="display:block;">Nu ati selectat niciun AWB pentru dezactivare!</div></div></div>');
                            }
                            return false;
                        });
                    </script>

                    <?php } ?>
                </div>

                <?php } ?>
            </form>
        </div>

    <?php }
} ?>