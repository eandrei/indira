<?php
class AdminUrgentCargusIstoric extends AdminTab {
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
    }

    public function display () { ?>

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
                $pickups = $uc->CallMethod('PickupLocations', array(), 'GET', $token);
                if (is_null($pickups)) {
                    die('<div class="panel">Nu exista niciun punct de ridicare asociat acestui cont!</div></form></div>');
                }
                if (Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == '' && count($pickups) > 0) {
                    Configuration::updateValue('_URGENT_PUNCT_RIDICARE_', $pickups[0]['LocationId']);
                }

                // UC get istoric comenzi
                $orders = $uc->CallMethod('Orders?locationId='.Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL).'&status=1&pageNumber=1&itemsPerPage=100', array(), 'GET', $token);
                      
                if (is_null($orders)) { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Istoric livrari Urgent Cargus</div>
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
                    Nu exista nicio comanda pentru punctul curent de ridicare!
                </div>

                <?php } else { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Istoric livrari Urgent Cargus</div>
                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                                <thead>
                                    <tr class="nodrag nodrop">
                                        <th><span class="title_box active">Nr. comanda</span></th>
                                        <th><span class="title_box active">Data validare</span></th>
                                        <th><span class="title_box active">Data ridicare</span></th>
                                        <th><span class="title_box active">Data procesare</span></th>
                                        <th><span class="title_box active">Nr. AWB-uri</span></th>
                                        <th><span class="title_box active">Plicuri</span></th>
                                        <th><span class="title_box active">Colete</span></th>
                                        <th><span class="title_box active">Greutate</span></th>
                                        <th><span class="title_box active">Status</span></th>
                                        <th></th>
                                    </tr>
                                    <tr class="nodrag nodrop filter row_hover">
                                        <th colspan="10">
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
                                    <?php for ($i = 0; $i < count($orders); $i++) { ?>
                                    <tr class="<?php echo $i % 2 == 0 ? 'even' : 'odd' ?>">
                                        <td><?php echo $orders[$i]['OrderId']; ?></td>
                                        <td><?php echo $orders[$i]['ValidationDate'] ? date('d.m.Y', strtotime($orders[$i]['ValidationDate'])) : '-'; ?></td>
                                        <td><?php echo $orders[$i]['PickupStartDate'] ? date('d.m.Y H:i', strtotime($orders[$i]['PickupStartDate']))  . ' - ' . date('H:i', strtotime($orders[$i]['PickupEndDate'])) : '-'; ?></td>
                                        <td><?php echo $orders[$i]['ProcessedDate'] ? date('d.m.Y', strtotime($orders[$i]['ProcessedDate'])) : '-'; ?></td>
                                        <td><?php echo $orders[$i]['NoAwb']; ?></td>
                                        <td><?php echo $orders[$i]['NoEnvelop']; ?></td>
                                        <td><?php echo $orders[$i]['NoParcel']; ?></td>
                                        <td><?php echo $orders[$i]['TotalWeight']; ?> kg</td>
                                        <td><?php echo $orders[$i]['OrdStatus']; ?></td>
                                        <td>
                                            <div class="btn-group-action">
                                                <div class="btn-group pull-right">
                                                    <a href="index.php?controller=AdminUrgentCargusIstoricComanda&token=<?php echo Tools::getAdminTokenLite('AdminUrgentCargusIstoricComanda'); ?>&OrderId=<?php echo $orders[$i]['OrderId']; ?>" title="Vizualizare" class="edit btn btn-default">
                                                        <i class="icon-search-plus"></i> Vizualizare
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <div style="color:#999; font-size:11px; padding:10px 0;">Sunt afisate ultimele 100 de comenzi efectuate pentru punctul curent de ridicare. Pentru comenzile anterioare, va rugam sa consultati pagina Urgent Cargus</div>
                    </div>
                </div>

                <?php }

                } ?>

            </form>
        </div>

    <?php }
} ?>