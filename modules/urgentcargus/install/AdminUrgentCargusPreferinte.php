<?php
session_start();

class AdminUrgentCargusPreferinte extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        if (Tools::isSubmit('submit')) {
            //Configuration::updateValue('_URGENT_PLAN_TARIFAR_', Tools::getValue('_URGENT_PLAN_TARIFAR_'));
            Configuration::updateValue('_URGENT_PUNCT_RIDICARE_', Tools::getValue('_URGENT_PUNCT_RIDICARE_'));
            Configuration::updateValue('_URGENT_ASIGURARE_', Tools::getValue('_URGENT_ASIGURARE_'));
            Configuration::updateValue('_URGENT_SAMBATA_', Tools::getValue('_URGENT_SAMBATA_'));
            Configuration::updateValue('_URGENT_DIMINEATA_', Tools::getValue('_URGENT_DIMINEATA_'));
            Configuration::updateValue('_URGENT_DESCHIDERE_COLET_', Tools::getValue('_URGENT_DESCHIDERE_COLET_'));
            Configuration::updateValue('_URGENT_TIP_RAMBURS_', Tools::getValue('_URGENT_TIP_RAMBURS_'));
            Configuration::updateValue('_URGENT_PLATITOR_', Tools::getValue('_URGENT_PLATITOR_'));
            Configuration::updateValue('_URGENT_TIP_EXPEDITIE_', Tools::getValue('_URGENT_TIP_EXPEDITIE_'));
            Configuration::updateValue('_URGENT_TRANSPORT_GRATUIT_', Tools::getValue('_URGENT_TRANSPORT_GRATUIT_'));
            Configuration::updateValue('_URGENT_COST_FIX_', Tools::getValue('_URGENT_COST_FIX_'));

            $_SESSION['post_status'] = array(
                'success' => array('Preferintele au fost salvate cu succes!'),
                'errors' => array()
            );

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

                // obtine lista planurilor tarifare
                //$tarife = $uc->CallMethod('PriceTables', array(), 'GET', $token);
                //if (is_null($tarife)) {
                //    die('<div class="panel">Nu exista niciun plan tarifar asociat acestui cont!</div></form></div>');
                //}
                //if (Configuration::get('_URGENT_PLAN_TARIFAR_', $id_lang = NULL) == '') {
                //    Configuration::updateValue('_URGENT_PLAN_TARIFAR_', $tarife[0]['PriceTableId']);
                //}

                // obtine lista punctelor de ridicare
                $pickups = $uc->CallMethod('PickupLocations', array(), 'GET', $token);
                if (is_null($pickups)) {
                    die('<div class="panel">Nu exista niciun punct de ridicare asociat acestui cont!</div></form></div>');
                }
                if (Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == '' && count($pickups) > 0) {
                    Configuration::updateValue('_URGENT_PUNCT_RIDICARE_', $pickups[0]['LocationId']);
                }
                
                ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Preferinte modul Urgent Cargus</div>
                    <?php /*
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Plan tarifar</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_PLAN_TARIFAR_">
                            <?php foreach ($tarife as $tarif) {
                                echo '<option '.(Configuration::get('_URGENT_PLAN_TARIFAR_', $id_lang = NULL) == $tarif['PriceTableId'] ? 'selected="selected"' : '').' value="'.$tarif['PriceTableId'].'">'.$tarif['Name'].'</option>';
                            } ?>
                            </select>
                        </div>
                    </div>
                    */ ?>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punctul de ridicare</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_PUNCT_RIDICARE_">
                            <?php foreach ($pickups as $pick) {
                                echo '<option '.(Configuration::get('_URGENT_PUNCT_RIDICARE_', $id_lang = NULL) == $pick['LocationId'] ? 'selected="selected"' : '').' value="'.$pick['LocationId'].'">'.$pick['Name'].'</option>';
                            } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Asigurare expeditie</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_ASIGURARE_">
                                <option <?php if (Configuration::get('_URGENT_ASIGURARE_', $id_lang = NULL) != 1) echo 'selected="selected"'; ?> value="0">Nu</option>
                                <option <?php if (Configuration::get('_URGENT_ASIGURARE_', $id_lang = NULL) == 1) echo 'selected="selected"'; ?> value="1">Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_SAMBATA_">
                                <option <?php if (Configuration::get('_URGENT_SAMBATA_', $id_lang = NULL) != 1) echo 'selected="selected"'; ?> value="0">Nu</option>
                                <option <?php if (Configuration::get('_URGENT_SAMBATA_', $id_lang = NULL) == 1) echo 'selected="selected"'; ?> value="1">Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare dimineata</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_DIMINEATA_">
                                <option <?php if (Configuration::get('_URGENT_DIMINEATA_', $id_lang = NULL) != 1) echo 'selected="selected"'; ?> value="0">Nu</option>
                                <option <?php if (Configuration::get('_URGENT_DIMINEATA_', $id_lang = NULL) == 1) echo 'selected="selected"'; ?> value="1">Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_DESCHIDERE_COLET_">
                                <option <?php if (Configuration::get('_URGENT_DESCHIDERE_COLET_', $id_lang = NULL) != 1) echo 'selected="selected"'; ?> value="0">Nu</option>
                                <option <?php if (Configuration::get('_URGENT_DESCHIDERE_COLET_', $id_lang = NULL) == 1) echo 'selected="selected"'; ?> value="1">Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip ramburs</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_TIP_RAMBURS_">
                                <option <?php if (Configuration::get('_URGENT_TIP_RAMBURS_', $id_lang = NULL) != 'cont') echo 'selected="selected"'; ?> value="cash">Numerar</option>
                                <option <?php if (Configuration::get('_URGENT_TIP_RAMBURS_', $id_lang = NULL) == 'cont') echo 'selected="selected"'; ?> value="cont">Cont colector</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Alegeti cine plateste costul serviciului de transport catre Urgent Cargus" data-html="true">Platitor expeditie</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_PLATITOR_">
                                <option <?php if (Configuration::get('_URGENT_PLATITOR_', $id_lang = NULL) != 'expeditor') echo 'selected="selected"'; ?> value="destinatar">Destinatar</option>
                                <option <?php if (Configuration::get('_URGENT_PLATITOR_', $id_lang = NULL) == 'expeditor') echo 'selected="selected"'; ?> value="expeditor">Expeditor</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip expeditie</span>
                        </label>
                        <div class="col-lg-10">
                            <select name="_URGENT_TIP_EXPEDITIE_">
                                <option <?php if (Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) != 'plic') echo 'selected="selected"'; ?> value="colet">Colet</option>
                                <option <?php if (Configuration::get('_URGENT_TIP_EXPEDITIE_', $id_lang = NULL) == 'plic') echo 'selected="selected"'; ?> value="plic">Plic</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Daca totalul cosului depaseste suma in lei introdusa, transportul va fi gratuit" data-html="true">Limita transport gratuit</span>
                        </label>
                        <div class="col-lg-10">
                            <input type="text" name="_URGENT_TRANSPORT_GRATUIT_" value="<?php echo Configuration::get('_URGENT_TRANSPORT_GRATUIT_', $id_lang = NULL); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Modulul nu va mai calcula dinamic costul transportului si va fi afisata suma in lei introdusa" data-html="true">Cost fix transport</span>
                        </label>
                        <div class="col-lg-10">
                            <input type="text" name="_URGENT_COST_FIX_" value="<?php echo Configuration::get('_URGENT_COST_FIX_', $id_lang = NULL); ?>" />
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" name="submit" value="submit" class="btn btn-default pull-right">
                            <i class="process-icon-save"></i> Salveaza
                        </button>
                    </div>
                </div>

                <?php } ?>

            </form>
        </div>

    <?php }
} ?>