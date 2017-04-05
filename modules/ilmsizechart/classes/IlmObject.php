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
class IlmObject extends ObjectModel
{

    public static function insertChart($table, $title, $active)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . $table . '` (title, active) values("' . pSQL($title) . '", ' . (int) $active . ')';
        return Db::getInstance()->execute($sql);
    }

    public static function selectChart($table, $field, $id_group)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . $table . '` WHERE `' . $field . '` = ' . (int) $id_group;
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function updateChart($table, $field, $id_group, $title, $active)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . $table . '` SET title="' . pSQL($title) . '", active=' . (int) $active . ' WHERE `' . $field . '`=' . (int) $id_group;
        return Db::getInstance()->execute($sql);
    }

    public static function deleteChart($table, $field, $id_group)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $table . '` WHERE `' . $field . '`=' . (int) $id_group;
        return Db::getInstance()->execute($sql);
    }

    public static function insertSizeChart($row, $column, $chart_type, $chart_group, $category)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'ilmsizechart` (row, id_group, col, chart_type, category) 
                    values(' . (int) $row . ', ' . (int) $chart_group . ', ' . (int) $column . ', ' . (int) $chart_type . ', \'' . pSQL($category) . '\')';
        return Db::getInstance()->execute($sql);
    }

    public static function selectSizeChart($id_value)
    {
        $sql = 'SELECT a.*, b.id_group FROM ' . _DB_PREFIX_ . 'ilmsizechart AS a 
                    LEFT JOIN ' . _DB_PREFIX_ . 'ilmsize_group AS b ON a.id_group = b.id_group 
                    WHERE a.id_value = ' . (int) $id_value;
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function updateSizeChart($row, $column, $chart_type, $chart_group, $ilmsizes, $category, $img, $id_value)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'ilmsizechart` SET row=' . (int) $row . ', id_group=' . (int) $chart_group . ', col=' . (int) $column . ', chart_type=' . (int) $chart_type . ', `values`=\'' . pSQL($ilmsizes) . '\', `category`=\'' . $category . '\', `img`=\'' . pSQL($img) . '\' WHERE id_value = ' . (int) $id_value;
        return Db::getInstance()->execute($sql);
    }

    public static function deleteSizeChart($id_value)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'ilmsizechart` WHERE id_value = ' . (int) $id_value;
        return Db::getInstance()->execute($sql);
    }

    public static function ilmProduct($id_product)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product` WHERE id_product = ' . (int) $id_product;
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function ilmChartCat($catg)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'ilmsizechart`';
        $charts = Db::getInstance()->ExecuteS($sql);
        $id_value = null;
        foreach ($charts as $chart)
        {
            $catgs = unserialize($chart['category']);
            if (in_array($catg, $catgs))
                $id_value = $chart['id_value'];
        }
        return $id_value;
    }

    public static function selectProductChart($pid)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'ilmsizechart_product` WHERE pid = ' . (int) $pid;
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function insertProductChart($pid, $chartid)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'ilmsizechart_product` (pid, chartid) values(' . (int) $pid . ', ' . (int) $chartid . ')';
        return Db::getInstance()->execute($sql);
    }

    public static function updateProductChart($pid, $chartid)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'ilmsizechart_product` SET chartid=' . $chartid . ' WHERE `pid`=' . (int) $pid;
        return Db::getInstance()->execute($sql);
    }

    public static function deleteProductChart($pid)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'ilmsizechart_product` WHERE pid = ' . (int) $pid;
        return Db::getInstance()->execute($sql);
    }

}
