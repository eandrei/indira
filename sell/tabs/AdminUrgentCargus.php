<?php
class AdminUrgentCargus extends AdminTab {

    public function __construct () {
        parent::__construct();
    }

    public function postProcess () {
        
    }

    public function display () {
        $obj = new AdminUrgentCargusLivrari();
        $obj->display();
    }
}
?>