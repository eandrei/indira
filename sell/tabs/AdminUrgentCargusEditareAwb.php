<?php
class AdminUrgentCargusEditareAwb extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        if (Tools::isSubmit('submit')) {
            $sql = "UPDATE awb_urgent_cargus SET 
                                pickup_id = '".addslashes(Tools::getValue('pickup_id'))."',
                                name = '".addslashes(Tools::getValue('name'))."',
                                locality_name = '".addslashes(Tools::getValue('city'))."',
                                county_name = '".addslashes(Tools::getValue('id_state'))."',
                                address = '".addslashes(Tools::getValue('address'))."',
                                contact = '".addslashes(Tools::getValue('contact'))."',
                                phone = '".addslashes(Tools::getValue('phone'))."',
                                email = '".addslashes(Tools::getValue('email'))."',
                                parcels = '".addslashes(Tools::getValue('parcels'))."',
                                envelopes = '".addslashes(Tools::getValue('envelopes'))."',
                                weight = '".addslashes(Tools::getValue('weight'))."',
                                value = '".addslashes(Tools::getValue('value'))."',
                                cash_repayment = '".addslashes(Tools::getValue('cash_repayment'))."',
                                bank_repayment = '".addslashes(Tools::getValue('bank_repayment'))."',
                                other_repayment = '".addslashes(Tools::getValue('other_repayment'))."',
                                payer = '".addslashes(Tools::getValue('payer'))."',
                                morning_delivery = '".addslashes(Tools::getValue('morning_delivery'))."',
                                saturday_delivery = '".addslashes(Tools::getValue('saturday_delivery'))."',
                                openpackage = '".addslashes(Tools::getValue('openpackage'))."',
                                observations = '".addslashes(Tools::getValue('observations'))."',
                                contents = '".addslashes(Tools::getValue('contents'))."'
                            WHERE id = '".Tools::getValue('Id')."'
                            ";
            $result = Db::getInstance()->execute($sql);
            if ($result == 1) {
                ob_end_clean();
                header('Location: index.php?controller=AdminUrgentCargusLivrari&token='.Tools::getAdminTokenLite('AdminUrgentCargusLivrari'));
                die();
            } else {
                echo 'Eroare la inserarea datelor in baza!';
            }
        }
    }

    public function display () { ?>

        <div class="entry-edit">
            <form id="edit_form" class="form-horizontal" name="edit_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

                <?php if (Configuration::get('_URGENT_USERNAME_', $id_lang = NULL) == '' || Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL) == '') { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Comanda curenta Urgent Cargus</div>
                    Va rugam sa completati username-ul si parola in pagina de configurare a modulului!
                </div>

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
                        die('Nu exista niciun punct de ridicare disponibil pentru acest utilizator!');
                    }

                    $data = Db::getInstance()->ExecuteS("SELECT * FROM awb_urgent_cargus WHERE id = '".Tools::getValue('Id')."'");

                    $states = State::getStatesByIdCountry(36);
                ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Editare AWB comanda nr. <?php echo $data[0]['order_id']; ?></div>
                    <br /><br />
                    <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Expeditor</div>
                    <div class="form-group">
                        <input type="hidden" name="id_country" value="36" />

                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punct de ridicare</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="pickup_id">
                                <?php
                                foreach ($pickups as $pick) {
                                    echo '<option '.($data[0]['pickup_id'] == $pick['LocationId'] ? 'selected="selected"' : '').' value="'.$pick['LocationId'].'">'.$pick['Name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br />
                    <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Destinatar</div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Nume destinatar</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="name" value="<?php echo $data[0]['name']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Judet</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="id_state">
                                <?php
                                foreach ($states as $state) {
                                    echo '<option '.($data[0]['county_name'] == $state['iso_code'] ? 'selected="selected"' : '').' value="'.$state['iso_code'].'">'.$state['name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Localitate</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="city" value="<?php echo $data[0]['locality_name']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Adresa de livrare</span>
                        </label>
                        <div class="col-lg-4">
                            <textarea name="address"><?php echo $data[0]['address']; ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Persoana de contact</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="contact" value="<?php echo $data[0]['contact']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Telefon</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="phone" value="<?php echo $data[0]['phone']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Email</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="email" value="<?php echo $data[0]['email']; ?>" />
                        </div>
                    </div>
                    <br />
                    <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Detalii AWB</div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Plicuri</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="envelopes" value="<?php echo $data[0]['envelopes']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Colete</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="parcels" value="<?php echo $data[0]['parcels']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Greutate</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="weight" value="<?php echo $data[0]['weight']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Valoare declarata</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="value" value="<?php echo $data[0]['value']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs numerar</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="cash_repayment" value="<?php echo $data[0]['cash_repayment']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs cont colector</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="bank_repayment" value="<?php echo $data[0]['bank_repayment']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs alt tip</span>
                        </label>
                        <div class="col-lg-4">
                            <textarea name="other_repayment"><?php echo $data[0]['other_repayment']; ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Platitor expeditie</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="payer">
                                <option value="1" <?php echo $data[0]['payer'] == '1' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="2" <?php echo $data[0]['payer'] == '2' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare dimineata</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="morning_delivery">
                                <option value="0" <?php echo $data[0]['morning_delivery'] == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?php echo $data[0]['morning_delivery'] == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="saturday_delivery">
                                <option value="0" <?php echo $data[0]['saturday_delivery'] == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?php echo $data[0]['saturday_delivery'] == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                        </label>
                        <div class="col-lg-4">
                            <select name="openpackage">
                                <option value="0" <?php echo $data[0]['openpackage'] == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?php echo $data[0]['openpackage'] == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Observatii</span>
                        </label>
                        <div class="col-lg-4">
                            <textarea name="observations"><?php echo $data[0]['observations']; ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Continut</span>
                        </label>
                        <div class="col-lg-4">
                            <textarea name="contents"><?php echo $data[0]['contents']; ?></textarea>
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

    <?php }
} ?>