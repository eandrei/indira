<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class BlockLayeredOverride extends BlockLayered
{
    public function install()
    {
        if(parent::install() && $this->registerHook('categoryTop'))
            return true;
        else return false;
    }

    public function hookCategoryTop($params)
    {
        return $this->hookLeftColumn($params);
    }

}