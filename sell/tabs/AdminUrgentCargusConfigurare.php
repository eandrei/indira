<?php
session_start();

class AdminUrgentCargusConfigurare extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        if (Tools::isSubmit('submit')) {
            Configuration::updateValue('_URGENT_APIURL_', Tools::getValue('_URGENT_APIURL_'));
            Configuration::updateValue('_URGENT_APIKEY_', Tools::getValue('_URGENT_APIKEY_'));
	        Configuration::updateValue('_URGENT_USERNAME_', Tools::getValue('_URGENT_USERNAME_'));
            Configuration::updateValue('_URGENT_PASSWORD_', Tools::getValue('_URGENT_PASSWORD_'));

            $_SESSION['post_status'] = array(
                'success' => array('Setarile au fost salvate cu succes!'),
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

                <div class="panel">
                    <div class="panel-heading"><i class="icon-align-justify"></i> Configurare modul Urgent Cargus</div>
                    
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">API Url</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="_URGENT_APIURL_" value="<?php echo Configuration::get('_URGENT_APIURL_', $id_lang = NULL); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Subscription Key</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="_URGENT_APIKEY_" value="<?php echo Configuration::get('_URGENT_APIKEY_', $id_lang = NULL); ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Username</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="text" name="_URGENT_USERNAME_" value="<?php echo Configuration::get('_URGENT_USERNAME_', $id_lang = NULL); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">
                            <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Password</span>
                        </label>
                        <div class="col-lg-4">
                            <input type="password" name="_URGENT_PASSWORD_" value="<?php echo Configuration::get('_URGENT_PASSWORD_', $id_lang = NULL); ?>" />
                        </div>
                    </div>
                    <div class="panel-footer">
			            <button type="submit" name="submit" value="submit" class="btn btn-default pull-right">
					        <i class="process-icon-save"></i> Salveaza
				        </button>
			        </div>
                </div>
            </form>
        </div>

    <?php }
} ?>