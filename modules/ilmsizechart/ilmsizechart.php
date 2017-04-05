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

include_once(_PS_MODULE_DIR_ . 'ilmsizechart/classes/IlmObject.php');

class ILMSizeChart extends Module
{

    public function __construct()
    {
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->bootstrap = true;
        $this->name = 'ilmsizechart';
        $this->tab = 'front_office_features';
        $this->version = '2.0.2';
        $this->author = 'Abdullah Ahamed';
        $this->module_key = '378d1b971221eb388238dbf15ddc7ce3';
        $this->psversion = Tools::substr(_PS_VERSION_, 0, 3);
        parent::__construct();
        $this->displayName = $this->l('ILM Size Chart');
        $this->description = $this->l('ILM Size Chart allows you to configure the sizes for the Products');
        $this->confirmUninstall = $this->l('Are you sure want to Uninstall');
        $this->context->smarty->assign('module_name', $this->name);
    }

    public function install()
    {
        $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ilmsizechart` (
					`id_value` int(11) NOT NULL AUTO_INCREMENT,
					`id_group` int(11) DEFAULT NULL,
					`row` int(11) NOT NULL,
					`col` int(11) NOT NULL,
					`chart_type` int(11) NOT NULL,
					`values` text,
					`category` text,
                                        `img` text,
					PRIMARY KEY (`id_value`)
				)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ilmsizechart_product` (
					`pid` int(11) NOT NULL,
					`chartid` int(11) NOT NULL
				)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ilmsize_group` (
					`id_group` int(11) NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL,
					`active` int(11) DEFAULT NULL,
					PRIMARY KEY (`id_group`),
					UNIQUE KEY `id_group` (`id_group`)
				)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ilmsize_label` (
					`id_label` int(11) NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL,
					`active` int(11) DEFAULT NULL,
					UNIQUE KEY `id_label` (`id_label`)
				)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        if (!parent::install() || !$this->registerHook('displayHeader') || !$this->registerHook('displayLeftColumnProduct') || !$this->registerHook('displayAdminProductsExtra') || !$this->registerHook('actionProductUpdate') || !$this->runSql($sql))
            return false;
        return true;
    }

    public function uninstall()
    {
        $sql = array();
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ilmsizechart`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ilmsizechart_product`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ilmsize_group`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ilmsize_label`';
        if (!parent::uninstall() || !$this->runSql($sql))
            return false;
        return true;
    }

    public function runSql($sql)
    {
        foreach ($sql as $s)
        {
            if (!Db::getInstance()->Execute($s))
                return false;
        }
        return true;
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/ilmsizechart.css', 'all');
    }

    public function hookDisplayLeftColumnProduct()
    {
        $id_product = Tools::getValue('id_product');
        $catid = IlmObject::ilmProduct($id_product);
        $catg = $catid[0]['id_category_default'];
        $id = IlmObject::ilmChartCat($catg);
        $pchart = IlmObject::selectProductChart($id_product);
        if (count($pchart) > 0)
            $id = $pchart[0]['chartid'];
        $values = IlmObject::selectSizeChart($id);
        $labels = $this->getLabelLists();
        if (count($values) > 0)
        {
            foreach ($values as $value)
                ;
            $image = $value['img'];
            $where = ' where id_group=' . $value['id_group'];
            $groups = $this->getGroupList($where);
            foreach ($groups as $group)
                ;
            $sizes = unserialize($value['values']);
            $table = '<p>' . $group['title'] . '</p>';
            $table .= '<table class="ilmsizechart table">';
            for ($j = 0; $j <= $value['col']; $j++)
                $table .= '<colgroup></colgroup>';
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
            $this->context->smarty->assign(
                    array(
                        'baseURL' => __PS_BASE_URI__,
                        'ilmtable' => $table,
                        'image' => $image,
                    )
            );
            return $this->display(__FILE__, 'views/templates/hook/ilmsizechart.tpl');
        }
    }

    public function hookDisplayLeftColumn()
    {
        return $this->hookDisplayLeftColumnProduct();
    }

    public function hookDisplayProductButtons()
    {
        return $this->hookDisplayLeftColumnProduct();
    }

    public function hookDisplayRightColumnProduct()
    {
        return $this->hookDisplayLeftColumnProduct();
    }

    public function hookActionProductUpdate()
    {
        $pid = Tools::getValue('id_product');
        $chartid = Tools::getValue('size_list');
        $ilmsize = Tools::getValue('ilmsize');
        $pchart = IlmObject::selectProductChart($pid);
        if (count($pchart) < 1 && $chartid != 0)
            IlmObject::insertProductChart($pid, $chartid);
        elseif ($chartid == 0)
            IlmObject::deleteProductChart($pid);
        elseif ($ilmsize == 1)
            IlmObject::updateProductChart($pid, $chartid);
    }

    public function hookDisplayAdminProductsExtra()
    {
        if ($this->psversion == '1.5')
            $this->context->controller->addCSS($this->_path . 'views/css/admin_ilmsizechart.css', 'all');
        $id_product = Tools::getValue('id_product');
        $catid = IlmObject::ilmProduct($id_product);
        $catg = $catid[0]['id_category_default'];
        $id = IlmObject::ilmChartCat($catg);
        $pchart = IlmObject::selectProductChart($id_product);
        if (count($pchart) > 0)
            $id = $pchart[0]['chartid'];
        $values = IlmObject::selectSizeChart($id);
        $labels = $this->getLabelLists();
        foreach ($values as $value)
            ;
        $image = $value['img'];
        $sizes = unserialize($value['values']);
        $size_lists = $this->getValueList();
        $chart_label = array('Row Only', 'Column Only', 'Row & Column', 'No Row & Column');
        $size = '<select name="size_list" id="size_list">';
        $size .= '<option value="0">Default</option>';
        foreach ($size_lists as $size_list)
            $size .= '<option value="' . $size_list['id_value'] . '" ' . (($size_list['id_value'] == $id) ? 'selected="selected"' : '') . '>[' . $size_list['id_value'] . ']' . $size_list['title'] . ' (' . $chart_label[$size_list['chart_type']] . ')</option>';
        $size .= '</select>';
        $size .= "
			<script>
			jQuery(function(){
			$('#size_list').live('change',function(){
			var id = $(this).val();
			$.ajax({
			url: '" . __PS_BASE_URI__ . "modules/ilmsizechart/ilmsizeajax.php',
			type: 'get',
			data: 'id='+id,
			success: function(data) {
			console.log('success');
				$('#ilmsizechart').html(data);
			}
			});
			});
			});
			</script>
			";
        $table = '<table class="ilmsizechart table">';
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
        if ($value) {
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
        }
        $table .= '</table>';
        $this->context->smarty->assign(
                array(
                    'baseURL' => __PS_BASE_URI__,
                    'pid' => $id_product,
                    'sizes' => $size,
                    'ilmtable' => $table,
                    'image' => $image,
                )
        );
        $version = (($this->psversion == '1.5') ? '15/' : '16/');
        return $this->display(__FILE__, 'views/templates/admin/' . $version . 'ilmsizechartproduct.tpl');
    }

    public function getContent()
    {
        if ($this->psversion == '1.5')
            $this->context->controller->addCSS($this->_path . 'views/css/admin_ilmsizechart.css', 'all');
        $this->_html = '';
        if (Tools::isSubmit('saveGroupChart') || Tools::isSubmit('saveLabelChart') || Tools::isSubmit('saveSizeChart'))
            $this->_html .= $this->_saveChart();
        elseif (Tools::isSubmit('addGroupChart'))
            $this->_html .= $this->addGroupChart();
        elseif (Tools::isSubmit('addLabelChart'))
            $this->_html .= $this->addLabelChart();
        elseif (Tools::isSubmit('addSizeChart'))
            $this->_html .= $this->addSizeChart();
        elseif (Tools::isSubmit('id_groupdelete') || Tools::isSubmit('id_labeldelete') || Tools::isSubmit('id_sizedelete'))
            $this->_html .= $this->_deleteChart();
        else
        {
            $this->_html .= $this->ilmsizechart_header_left();
            $this->_html .= $this->ilmsizechart_header_right();
            $groups = $this->getGroupList();
            $labels = $this->getLabelLists();
            if (count($groups) > 0 && count($labels) > 0)
                $this->_html .= $this->ilmsizechart_list();
        }
        $this->_html .= '<div style="clear:both;"></div>';
        $this->_html .= '<div align="center"><a target="_blank" class="btn btn-primary" href="mailto:sales@ilmtech.in?subject=ILM SIZE CHART - IDEAS" >Share your idea at sales@ilmtech.in and get your free updates</a></div><br>';
        $this->_html .= '<div align="center"><a target="_blank" class="btn btn-default" href="https://addons.prestashop.com/en/product.php?id_product=18092" >If you like my extension please take a time to post your review.</a></div>';
        return $this->_html;
    }

    public function ilmsizechart_header_left()
    {
        $groups = $this->getGroupList();
        foreach ($groups as $key => $list)
            $groups[$key]['active'] = $this->displayStatus($list['id_group'], $list['title'], $list['active'], 'ilmsize_group');
        $this->context->smarty->assign(
                array(
                    'link' => $this->context->link,
                    'groups' => $groups
                )
        );
        $version = (($this->psversion == '1.5') ? '15/' : '16/');
        return $this->display(__FILE__, 'views/templates/admin/' . $version . 'ilmsizechart_header_left.tpl');
    }

    public function getGroupList($where = '')
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ilmsize_group' . $where);
    }

    public function addGroupChart()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add Group'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_group',
                        'lang' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'title',
                        'lang' => false,
                    ),
                    array(
                        'type' => (($this->psversion == '1.5') ? 'radio' : 'switch'),
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveGroupChart';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'fields_value' => $this->_editChart()
        );
        return $helper->generateForm(array($fields_form));
    }

    public function ilmsizechart_header_right()
    {
        $labels = $this->getLabelLists();
        foreach ($labels as $key => $list)
            $labels[$key]['active'] = $this->displayStatus($list['id_label'], $list['title'], $list['active'], 'ilmsize_label');
        $this->context->smarty->assign(
                array(
                    'link' => $this->context->link,
                    'labels' => $labels
                )
        );
        $version = (($this->psversion == '1.5') ? '15/' : '16/');
        return $this->display(__FILE__, 'views/templates/admin/' . $version . 'ilmsizechart_header_right.tpl');
    }

    public function getLabelLists($where = '')
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ilmsize_label' . $where);
    }

    public function addLabelChart()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add Label'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_label',
                        'lang' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'title',
                        'lang' => false,
                    ),
                    array(
                        'type' => (($this->psversion == '1.5') ? 'radio' : 'switch'),
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveLabelChart';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'fields_value' => $this->_editChart()
        );
        return $helper->generateForm(array($fields_form));
    }

    public function ilmsizechart_list()
    {
        $sizes = $this->getValueList();
        $chart_label = array('Row Only', 'Column Only', 'Row & Column', 'No Row & Column');
        $this->context->smarty->assign(
                array(
                    'baseURL' => __PS_BASE_URI__,
                    'link' => $this->context->link,
                    'sizes' => $sizes,
                    'type' => $chart_label
                )
        );
        $version = (($this->psversion == '1.5') ? '15/' : '16/');
        return $this->display(__FILE__, 'views/templates/admin/' . $version . 'ilmsizechart_list.tpl');
    }

    public function getValueList()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT a.*,b.title FROM ' . _DB_PREFIX_ . 'ilmsizechart AS a
			LEFT JOIN ' . _DB_PREFIX_ . 'ilmsize_group AS b ON a.id_group = b.id_group
			ORDER BY a.id_value ASC
			');
    }

    public function addSizeChart()
    {
        $where = ' where active=1';
        $groups = $this->getGroupList($where);
        $labels = $this->getLabelLists($where);
        $image = $imgname = '';
        if (Tools::isSubmit('id_sizeedit'))
            $values = IlmObject::selectSizeChart(Tools::getValue('id_sizeedit'));
        foreach ($values as $value)
            ;
        $sizes = unserialize($value['values']);
        if ($this->psversion == '1.5')
        {
            $helper = new Helper();
            $categ = $helper->renderCategoryTree();
            if ($value['category'] != 'b:0;')
            {
                $categs = unserialize($value['category']);
                if (isset($value['category']))
                    $categ = $helper->renderCategoryTree(null, $categs);
            }
        }
        else
        {
            $helper = new HelperTreeCategories('categories-treeview');
            $helper->setRootCategory((Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0));
            $categ = $helper->setUseCheckBox(true);
            if ($value['category'] != 'b:0;' && !empty($value['category']))
            {
                $categs = unserialize($value['category']);
                if (isset($value['category']))
                    $categ = $helper->setSelectedCategories($categs);
            }
        }
        if ($value['img'])
        {
            $image = '<tr id="imgrow"><td><img src="' . $this->getPathUri() . 'images' . '/' . $value['img'] . '" /></td></tr>';
            $imgname = $value['img'];
        }
        $table = '';
        $table = '<table class="ilmsizechart table">';
        if ($value['chart_type'] != 0 && $value['chart_type'] != 3)
        {
            $table .= '<tr>';
            if ($value['chart_type'] != 1)
                $table .= '<td><input name="ilmsizes[0][0]" type="text" value="' . $sizes[0][0] . '" placeholder="Custom title" /></td>';
            for ($i = 1; $i <= $value['col']; $i++)
            {
                $table .= '<td><select name="ilmsizes[0][' . $i . ']">';
                foreach ($labels as $label)
                    $table .= '<option value=' . $label['id_label'] . ' ' . (($label['id_label'] == $sizes[0][$i]) ? 'selected="selected"' : '') . '>' . $label['title'] . '</option>';
                $table .= '</select></td>';
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
                    $table .= '<td><select name="ilmsizes[' . $i . '][' . $j . ']">';
                    foreach ($labels as $label)
                        $table .= '<option value=' . $label['id_label'] . ' ' . (($label['id_label'] == $sizes[$i][$j]) ? 'selected="selected"' : '') . '>' . $label['title'] . '</option>';
                    $table .= '</select></td>';
                }
                elseif ($j > 0)
                    $table .= '<td><input name="ilmsizes[' . $i . '][' . $j . ']" type="text" value="' . $sizes[$i][$j] . '" /></td>';
            }
            $table .= '<tr>';
        }
        $table .= '</table>';
        $this->context->smarty->assign(
                array(
                    'link' => $this->context->link,
                    'groups' => $groups,
                    'value' => $value,
                    'image' => $image,
                    'imgname' => $imgname,
                    'table' => $table,
                    'categ' => $categ
                )
        );
        $version = (($this->psversion == '1.5') ? '15/' : '16/');
        return $this->display(__FILE__, 'views/templates/admin/' . $version . 'ilmsizechart.tpl');
    }

    public function _saveChart()
    {
        if (Tools::isSubmit('saveGroupChart'))
        {
            $id_group = Tools::getValue('id_group');
            $title = Tools::getValue('title');
            $active = Tools::getValue('active');
            if (!empty($id_group) && $id_group != 0)
                IlmObject::updateChart('ilmsize_group', 'id_group', $id_group, $title, $active);
            elseif (!empty($title))
                IlmObject::insertChart('ilmsize_group', $title, $active);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }
        if (Tools::isSubmit('saveLabelChart'))
        {
            $id_label = Tools::getValue('id_label');
            $title = Tools::getValue('title');
            $active = Tools::getValue('active');
            if (!empty($id_label) && $id_label != 0)
                IlmObject::updateChart('ilmsize_label', 'id_label', $id_label, $title, $active);
            elseif (!empty($title))
                IlmObject::insertChart('ilmsize_label', $title, $active);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }
        if (Tools::isSubmit('addSizeChart') && Tools::isSubmit('ilmconfig'))
        {
            $id_size = Tools::getValue('id_sizeedit');
            $row = Tools::getValue('ilmrow');
            $column = Tools::getValue('ilmcol');
            $chart_label = Tools::getValue('ilmcharttype');
            $chart_group = Tools::getValue('ilmchartgroup');
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
            if (isset($_FILES['ilmchartimg']) && isset($_FILES['ilmchartimg']['tmp_name']) && !empty($_FILES['ilmchartimg']['tmp_name']))
                if ($error = ImageManager::validateUpload($_FILES['ilmchartimg'], 4000000))
                    return $error;
                else
                if (!move_uploaded_file($_FILES['ilmchartimg']['tmp_name'], $path . $_FILES['ilmchartimg']['name']))
                    return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                else
                    $imgpath = $_FILES['ilmchartimg']['name'];
            elseif (Tools::getValue('filename'))
                $imgpath = Tools::getValue('filename');
            else
                $imgpath = '';
            $ilmsizes = serialize(Tools::getValue('ilmsizes'));
            $category = serialize(Tools::getValue('categoryBox'));
            if (!empty($id_size) && $id_size != 0)
            {
                IlmObject::updateSizeChart($row, $column, $chart_label, $chart_group, $ilmsizes, $category, $imgpath, $id_size);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&module_name=' . $this->name . '&addSizeChart&id_sizeedit=' . $id_size);
            }
            else
            {
                IlmObject::insertSizeChart($row, $column, $chart_label, $chart_group, $category);
                $values = $this->getValueList();
                foreach ($values as $value)
                    ;
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&module_name=' . $this->name . '&addSizeChart&id_sizeedit=' . $value['id_value']);
            }
        }
    }

    public function _editChart()
    {
        $fields = array();
        if (Tools::getValue('id_groupedit'))
        {
            $groups = IlmObject::selectChart('ilmsize_group', 'id_group', Tools::getValue('id_groupedit'));
            foreach ($groups as $group)
            {
                $fields['id_group'] = $group['id_group'];
                $fields['title'] = $group['title'];
                $fields['active'] = $group['active'];
            }
            return $fields;
        }
        if (Tools::getValue('id_labeledit'))
        {
            $labels = IlmObject::selectChart('ilmsize_label', 'id_label', Tools::getValue('id_labeledit'));
            foreach ($labels as $label)
            {
                $fields['id_label'] = $label['id_label'];
                $fields['title'] = $label['title'];
                $fields['active'] = $label['active'];
            }
            return $fields;
        }
    }

    public function _deleteChart()
    {
        $id_group = Tools::getValue('id_groupdelete');
        $id_label = Tools::getValue('id_labeldelete');
        $id_size = Tools::getValue('id_sizedelete');
        if (!empty($id_group) && $id_group != 0)
            IlmObject::deleteChart('ilmsize_group', 'id_group', $id_group);
        if (!empty($id_label) && $id_label != 0)
            IlmObject::deleteChart('ilmsize_label', 'id_label', $id_label);
        if (!empty($id_size) && $id_size != 0)
            IlmObject::deleteSizeChart($id_size);
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=1&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
    }

    public function displayStatus($id, $title, $active, $table)
    {
        if ($this->psversion == '1.5')
        {
            $text = ((int) $active == 0 ? 'Disabled' : 'Active');
            $class = ((int) $active == 0 ? 'action-disabled' : 'action-enabled');
            $active = ((int) $active == 0 ? (int) $active = 1 : (int) $active = 0);
            if ($table == 'ilmsize_group')
            {
                $html = '<a class="list-action-enable ' . $class . '" href="' . AdminController::$currentIndex .
                        '&configure=' . $this->name . '
				&token=' . Tools::getAdminTokenLite('AdminModules') . '
				&saveGroupChart&id_group=' . (int) $id . '&title=' . $title . '&active=' . (int) $active . '" title="' . $title . '">' . $text . '</a>';
            }
            if ($table == 'ilmsize_label')
            {
                $html = '<a class="list-action-enable ' . $class . '" href="' . AdminController::$currentIndex .
                        '&configure=' . $this->name . '
				&token=' . Tools::getAdminTokenLite('AdminModules') . '
				&saveLabelChart&id_label=' . (int) $id . '&title=' . $title . '&active=' . (int) $active . '" title="' . $title . '">' . $text . '</a>';
            }
        }
        else
        {
            $icon = ((int) $active == 0 ? 'icon-remove' : 'icon-check');
            $class = ((int) $active == 0 ? 'action-disabled' : 'action-enabled');
            $active = ((int) $active == 0 ? (int) $active = 1 : (int) $active = 0);
            if ($table == 'ilmsize_group')
            {
                $html = '<a class="list-action-enable ' . $class . '" href="' . AdminController::$currentIndex .
                        '&configure=' . $this->name . '
				&token=' . Tools::getAdminTokenLite('AdminModules') . '
				&saveGroupChart&id_group=' . (int) $id . '&title=' . $title . '&active=' . (int) $active . '" title="' . $title . '"><i class="' . $icon . '"></i></a>';
            }
            if ($table == 'ilmsize_label')
            {
                $html = '<a class="list-action-enable ' . $class . '" href="' . AdminController::$currentIndex .
                        '&configure=' . $this->name . '
				&token=' . Tools::getAdminTokenLite('AdminModules') . '
				&saveLabelChart&id_label=' . (int) $id . '&title=' . $title . '&active=' . (int) $active . '" title="' . $title . '"><i class="' . $icon . '"></i></a>';
            }
        }


        return $html;
    }

}
