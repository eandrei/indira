<?php
try {
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`) VALUES ('0', 'AdminUrgentCargus', '50')");
    $select1 = Db::getInstance()->ExecuteS("SELECT id_tab FROM "._DB_PREFIX_."tab WHERE class_name='AdminUrgentCargus' ORDER BY id_tab DESC LIMIT 0,1");
    $id_tab = $select1[0]['id_tab'];

    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`) VALUES ('".$id_tab."', 'AdminUrgentCargusLivrari', '1')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`) VALUES ('".$id_tab."', 'AdminUrgentCargusIstoric', '2')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`) VALUES ('".$id_tab."', 'AdminUrgentCargusPreferinte', '3')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`) VALUES ('".$id_tab."', 'AdminUrgentCargusConfigurare', '4')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`, `active`) VALUES ('".$id_tab."', 'AdminUrgentCargusIstoricComanda', '0', '0')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`, `active`) VALUES ('".$id_tab."', 'AdminUrgentCargusIstoricAwb', '0', '0')");
    Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab (`id_parent`, `class_name`, `position`, `active`) VALUES ('".$id_tab."', 'AdminUrgentCargusEditareAwb', '0', '0')");

    $select2 = Db::getInstance()->ExecuteS("SELECT id_lang FROM "._DB_PREFIX_."lang");
    foreach ($select2 as $val2) {
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".$id_tab."', '".$val2['id_lang']."', 'Urgent Cargus')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+1)."', '".$val2['id_lang']."', 'Comanda curenta')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+2)."', '".$val2['id_lang']."', 'Istoric livrari')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+3)."', '".$val2['id_lang']."', 'Preferinte')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+4)."', '".$val2['id_lang']."', 'Configurare')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+5)."', '".$val2['id_lang']."', 'Lista AWB-uri comanda')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+6)."', '".$val2['id_lang']."', 'Detaliu AWB comanda')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."tab_lang (`id_tab`, `id_lang`, `name`) VALUES ('".($id_tab+7)."', '".$val2['id_lang']."', 'Editare AWB')");
    }

    $select3 = Db::getInstance()->ExecuteS("SELECT id_profile FROM "._DB_PREFIX_."profile");
    foreach ($select3 as $val3) {
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".$id_tab."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+1)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+2)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+3)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+4)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+5)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+6)."', '1', '1', '1', '1')");
        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."access (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES ('".$val3['id_profile']."', '".($id_tab+7)."', '1', '1', '1', '1')");
    }

    Db::getInstance()->execute("DROP TABLE IF EXISTS `awb_urgent_cargus`");
    Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `awb_urgent_cargus` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `pickup_id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `locality_id` int(11) NOT NULL,
            `locality_name` varchar(128) NOT NULL,
            `county_id` int(11) NOT NULL,
            `county_name` varchar(128) NOT NULL,
            `street_id` int(11) NOT NULL,
            `street_name` varchar(128) NOT NULL,
            `number` varchar(32) NOT NULL,
            `address` varchar(256) NOT NULL,
            `contact` varchar(64) NOT NULL,
            `phone` varchar(32) NOT NULL,
            `email` varchar(96) NOT NULL,
            `parcels` int(11) NOT NULL,
            `envelopes` int(11) NOT NULL,
            `weight` int(11) NOT NULL,
            `value` double NOT NULL,
            `cash_repayment` double NOT NULL,
            `bank_repayment` double NOT NULL,
            `other_repayment` varchar(256) NOT NULL,
            `payer` tinyint(1) NOT NULL,
            `morning_delivery` tinyint(1) NOT NULL,
            `saturday_delivery` tinyint(1) NOT NULL,
	        `openpackage` tinyint(1) NOT NULL,
            `observations` varchar(256) NOT NULL,
            `contents` varchar(256) NOT NULL,
            `barcode` varchar(50) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    ");

    $source = _PS_MODULE_DIR_.'urgentcargus/install/UrgentCargusController.php';
    $destination = '../controllers/front/UrgentCargusController.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/UrgentCargusAdminController.php';
    $destination = '../controllers/admin/UrgentCargusAdminController.php';
    @copy($source,$destination);

    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargus.php';
    $destination = 'tabs/AdminUrgentCargus.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusLivrari.php';
    $destination = 'tabs/AdminUrgentCargusLivrari.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusEditareAwb.php';
    $destination = 'tabs/AdminUrgentCargusEditareAwb.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusIstoric.php';
    $destination = 'tabs/AdminUrgentCargusIstoric.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusIstoricComanda.php';
    $destination = 'tabs/AdminUrgentCargusIstoricComanda.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusIstoricAwb.php';
    $destination = 'tabs/AdminUrgentCargusIstoricAwb.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusPreferinte.php';
    $destination = 'tabs/AdminUrgentCargusPreferinte.php';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/AdminUrgentCargusConfigurare.php';
    $destination = 'tabs/AdminUrgentCargusConfigurare.php';
    @copy($source,$destination);

    $source = _PS_MODULE_DIR_.'urgentcargus/install/urgent_awb.html';
    $destination = '../mails/ro/urgent_awb.html';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/urgent_awb.txt';
    $destination = '../mails/ro/urgent_awb.txt';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/urgent_awb.html';
    $destination = '../mails/en/urgent_awb.html';
    @copy($source,$destination);
    $source = _PS_MODULE_DIR_.'urgentcargus/install/urgent_awb.txt';
    $destination = '../mails/en/urgent_awb.txt';
    @copy($source,$destination);

    @unlink('../cache/class_index.php');
} catch (Exception $ex) {
    // TODO: exception management
}
?>