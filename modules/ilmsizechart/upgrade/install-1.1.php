<?php

/**
 * ILM Size Chart act as Size guide for Prestashop store. Admin Can configure the Size chart in 4 different type all information explanined in the Documnet or You can view a demo.
 *
 * @author    Abdullah Ahamed
 * @copyright Copyright (c) 2014 ILM Tech. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @site		http://www.ilmtech.in
 * @contact		info@ilmtech.in

 */
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1($object)
{
    return Configuration::updateValue('PS_BLOCK_CART_XSELL_LIMIT', 12);
}
