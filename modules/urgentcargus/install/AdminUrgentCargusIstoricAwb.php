<?php
class AdminUrgentCargusIstoricAwb extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        
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

                // UC get detalii awb
                $awb = $uc->CallMethod('Awbs?&barCode=' . Tools::getValue('BarCode'), array(), 'GET', $token);

                if (is_null($awb)) { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Detalii AWB serie nr. <?php echo Tools::getValue('BarCode'); ?></div>
                    Nu s-au putut prelua detaliile acestui AWB!
                </div>

                <?php } else { ?>

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Detalii AWB serie nr. <?php echo Tools::getValue('BarCode'); ?></div>
                    <div class="table-responsive-row clearfix">
                        <table class="table order">
                            <tbody>
                                <tr class="odd">
                                    <td colspan="2"><strong>Expeditor</strong></td>
                                </tr>
                                <tr class="even">
                                    <td style="width:250px">Nume</td>
                                    <td><?php echo trim($awb[0]['Sender']['Name']) ? trim($awb[0]['Sender']['Name']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Judet</td>
                                    <td><?php echo trim($awb[0]['Sender']['CountyName']) ? trim($awb[0]['Sender']['CountyName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Localitate</td>
                                    <td><?php echo trim($awb[0]['Sender']['LocalityName']) ? trim($awb[0]['Sender']['LocalityName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Strada</td>
                                    <td><?php echo trim($awb[0]['Sender']['StreetName']) ? trim($awb[0]['Sender']['StreetName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Numar</td>
                                    <td><?php echo trim($awb[0]['Sender']['BuildingNumber']) ? trim($awb[0]['Sender']['BuildingNumber']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Adresa</td>
                                    <td><?php echo trim($awb[0]['Sender']['AddressText']) ? trim($awb[0]['Sender']['AddressText']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Contact</td>
                                    <td><?php echo trim($awb[0]['Sender']['ContactPerson']) ? trim($awb[0]['Sender']['ContactPerson']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Telefon</td>
                                    <td><?php echo trim($awb[0]['Sender']['PhoneNumber']) ? trim($awb[0]['Sender']['PhoneNumber']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Email</td>
                                    <td><?php echo trim($awb[0]['Sender']['Email']) ? trim($awb[0]['Sender']['Email']) : '-'; ?></td>
                                </tr>

                                <tr class="odd">
                                    <td colspan="2"><strong>Destinatar</strong></td>
                                </tr>
                                <tr class="even">
                                    <td>Nume</td>
                                    <td><?php echo trim($awb[0]['Recipient']['Name']) ? trim($awb[0]['Recipient']['Name']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Judet</td>
                                    <td><?php echo trim($awb[0]['Recipient']['CountyName']) ? trim($awb[0]['Recipient']['CountyName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Localitate</td>
                                    <td><?php echo trim($awb[0]['Recipient']['LocalityName']) ? trim($awb[0]['Recipient']['LocalityName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Strada</td>
                                    <td><?php echo trim($awb[0]['Recipient']['StreetName']) ? trim($awb[0]['Recipient']['StreetName']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Numar</td>
                                    <td><?php echo trim($awb[0]['Recipient']['BuildingNumber']) ? trim($awb[0]['Recipient']['BuildingNumber']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Adresa</td>
                                    <td><?php echo trim($awb[0]['Recipient']['AddressText']) ? trim($awb[0]['Recipient']['AddressText']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Contact</td>
                                    <td><?php echo trim($awb[0]['Recipient']['ContactPerson']) ? trim($awb[0]['Recipient']['ContactPerson']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Telefon</td>
                                    <td><?php echo trim($awb[0]['Recipient']['PhoneNumber']) ? trim($awb[0]['Recipient']['PhoneNumber']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Email</td>
                                    <td><?php echo trim($awb[0]['Recipient']['Email']) ? trim($awb[0]['Recipient']['Email']) : '-'; ?></td>
                                </tr>

                                <tr class="odd">
                                    <td colspan="2"><strong>Detalii AWB</strong></td>
                                </tr>
                                <tr class="even">
                                    <td>Serie</td>
                                    <td><?php echo trim($awb[0]['BarCode']); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Plicuri</td>
                                    <td><?php echo trim($awb[0]['Envelopes']); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Colete</td>
                                    <td><?php echo trim($awb[0]['Parcels']); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Greutate</td>
                                    <td><?php echo trim($awb[0]['TotalWeight']); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Valoare declarata</td>
                                    <td><?php echo trim($awb[0]['DeclaredValue']); ?> lei</td>
                                </tr>
                                <tr class="even">
                                    <td>Ramburs numerar</td>
                                    <td><?php echo trim($awb[0]['CashRepayment']); ?> lei</td>
                                </tr>
                                <tr class="even">
                                    <td>Ramburs cont colector</td>
                                    <td><?php echo trim($awb[0]['BankRepayment']); ?> lei</td>
                                </tr>
                                <tr class="even">
                                    <td>Ramburs alt tip</td>
                                    <td><?php echo trim($awb[0]['OtherRepayment']) ? trim($awb[0]['OtherRepayment']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Deschidere colet</td>
                                    <td><?php echo $awb[0]['OpenPackage'] ? 'Da' : 'Nu'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Livrare dimineata</td>
                                    <td><?php echo $awb[0]['MorningDelivery'] ? 'Da' : 'Nu'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Livrare sambata</td>
                                    <td><?php echo $awb[0]['SaturdayDelivery'] ? 'Da' : 'Nu'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Platitor expeditie</td>
                                    <td><?php echo $awb[0]['ShipmentPayer'] == '1' ? 'Expeditor' : ($awb[0]['ShipmentPayer'] == 2 ? 'Destinatar' : 'Tert'); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Observatii</td>
                                    <td><?php echo trim($awb[0]['Observations']) ? trim($awb[0]['Observations']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Continut</td>
                                    <td><?php echo trim($awb[0]['PackageContent']) ? trim($awb[0]['PackageContent']) : '-'; ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Identificator</td>
                                    <td><?php echo trim($awb[0]['CustomString']); ?></td>
                                </tr>
                                <tr class="even">
                                    <td>Status</td>
                                    <td><?php echo trim($awb[0]['Status']); ?></td>
                                </tr>
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