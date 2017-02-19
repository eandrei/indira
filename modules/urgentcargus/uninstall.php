<?php
try {
    Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."configuration WHERE `name` LIKE '%_URGENT_%'");
    $select = Db::getInstance()->ExecuteS("SELECT id_tab FROM "._DB_PREFIX_."tab WHERE class_name='AdminUrgentCargus' ORDER BY id_tab DESC LIMIT 0,1");
    $id_tab = $select[0]['id_tab'];
    Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."tab WHERE `id_tab` IN('".$id_tab."', '".($id_tab+1)."', '".($id_tab+2)."', '".($id_tab+3)."', '".($id_tab+4)."', '".($id_tab+5)."', '".($id_tab+6)."', '".($id_tab+7)."')");
    Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."tab_lang WHERE `id_tab` IN('".$id_tab."', '".($id_tab+1)."', '".($id_tab+2)."', '".($id_tab+3)."', '".($id_tab+4)."', '".($id_tab+5)."', '".($id_tab+6)."', '".($id_tab+7)."')");
    Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."access WHERE `id_tab` IN('".$id_tab."', '".($id_tab+1)."', '".($id_tab+2)."', '".($id_tab+3)."', '".($id_tab+4)."', '".($id_tab+5)."', '".($id_tab+6)."', '".($id_tab+7)."')");
    Db::getInstance()->execute("DROP TABLE `awb_urgent_cargus`");

    @unlink('../controllers/front/UrgentCargusController.php');
    @unlink('../controllers/admin/UrgentCargusAdminController.php');

    @unlink('tabs/AdminUrgentCargus.php');
    @unlink('tabs/AdminUrgentCargusLivrari.php');
    @unlink('tabs/AdminUrgentCargusEditareAwb.php');
    @unlink('tabs/AdminUrgentCargusIstoric.php');
    @unlink('tabs/AdminUrgentCargusIstoricComanda.php');
    @unlink('tabs/AdminUrgentCargusIstoricAwb.php');
    @unlink('tabs/AdminUrgentCargusPreferinte.php');
    @unlink('tabs/AdminUrgentCargusConfigurare.php');

    @unlink('../mails/ro/urgent_awb.html');
    @unlink('../mails/ro/urgent_awb.txt');
    @unlink('../mails/en/urgent_awb.html');
    @unlink('../mails/en/urgent_awb.txt');

    @unlink('../cache/class_index.php');
} catch(Exception $ex) {
    // TODO: exception management
}
?>