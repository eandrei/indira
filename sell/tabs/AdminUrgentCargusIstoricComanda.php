<?php
class AdminUrgentCargusIstoricComanda extends AdminTab {

    public function __construct () {
        parent::__construct ();
    }

    public function postProcess() {
        
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

                // UC get istoric awb-uri comanda
                $awb = $uc->CallMethod('Awbs?&orderId=' . Tools::getValue('OrderId'), array(), 'GET', $token);

                if (is_null($awb)) { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Istoric AWB-uri pentru comanda nr. <?php echo Tools::getValue('OrderId'); ?></div>
                    Nu exista niciun AWB asociat acestei comenzi!
                </div>

                <?php } else { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Istoric AWB-uri pentru comanda nr. <?php echo Tools::getValue('OrderId'); ?></div>
                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                            <thead>
                                <tr class="nodrag nodrop">
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
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < count($awb); $i++) { ?>
                                <tr class="<?php echo $i % 2 == 0 ? 'even' : 'odd' ?>">
                                    <td height="38"><?php echo $awb[$i]['CustomString']; ?></td>
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
                                    <td><?php echo $awb[$i]['Status'] == 'Deleted' ? '<span class="label color_field" style="background-color:#DC143C; color:white;">'.$awb[$i]['Status'].'</span>' : $awb[$i]['Status']; ?></td>
                                    <td>
                                        <div class="btn-group-action">
                                            <div class="btn-group pull-right">
                                                <a href="index.php?controller=AdminUrgentCargusIstoricAwb&token=<?php echo Tools::getAdminTokenLite('AdminUrgentCargusIstoricAwb'); ?>&BarCode=<?php echo $awb[$i]['BarCode']; ?>" title="Vizualizare" class="edit btn btn-default">
                                                    <i class="icon-search-plus"></i> Vizualizare
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php }
                } ?>

            </form>
        </div>

    <?php }
} ?>