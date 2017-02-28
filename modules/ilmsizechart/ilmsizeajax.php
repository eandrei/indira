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
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

$sql = 'SELECT a.*,b.id_group FROM ' . _DB_PREFIX_ . 'ilmsizechart AS a
LEFT JOIN ' . _DB_PREFIX_ . 'ilmsize_group AS b ON a.id_group = b.id_group WHERE a.id_value = ' . (int) Tools::getValue('id');
$charts = Db::getInstance()->ExecuteS($sql);

$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ilmsize_label';
$labels = Db::getInstance()->ExecuteS($sql);

foreach ($charts as $value)
    ;
$sizes = unserialize($value['values']);

$table = '';
if (Tools::getValue('id') != 0)
{
    if ($value['img'])
        $table .= '<img src="' . __PS_BASE_URI__ . 'modules/ilmsizechart/images' . '/' . $value['img'] . '" /><br/><br/>';
    $table .= '<table class="ilmsizechart table">';
    if ($value['chart_type'] != 0 && $value['chart_type'] != 3)
    {
        $table .= '<tr>';
        if ($value['chart_type'] != 1)
            $table .= '<td class="ilmcol">' . $sizes[0][0] . '</td>';
        for ($i = 1; $i <= $value['col']; $i++)
        {
            foreach ($labels as $label)
                $table .= (($label['id_label'] == $sizes[0][$i]) ? '<td class="ilmcol"><b>' . $label['title'] . '</b></td>' : '');
        }
        $table .= '</tr>';
    }
    for ($i = 1; $i <= $value['row']; $i++)
    {
        $table .= '<tr>';
        for ($j = 0; $j <= $value['col']; $j++)
        {
            if ($j == 0 && $value['chart_type'] != 1 && $value['chart_type'] != 3)
            {
                foreach ($labels as $label)
                    $table .= (($label['id_label'] == $sizes[$i][$j]) ? '<td class="ilmrow"><b>' . $label['title'] . '</b></td>' : '');
            }
            elseif ($j > 0)
                $table .= '<td>' . $sizes[$i][$j] . '</td>';
        }
        $table .= '</tr>';
    }
    $table .= '</table>';
    $table .= '<input type="hidden" name="ilmsize" value="1" />';
}
else
    $table = 'Dont panic it will restore to default Size Chart after you save the product';
echo $table;
