<?php
/**
 * Xml feeds Pro
 * @author Bl Modules <blmodules@gmail.com
 * @copyright Copyright (c) 2010 - 2014, Bl Modules
 * @page http://www.blmodules.com
 */

if (!defined('_PS_VERSION_'))
    exit;

class xml_feeds extends Module
{
    public $tags_info = array();
    public $_html = false;
    public $_html2 = false;

    function __construct()
    {
        $this->name = 'xml_feeds';
        $this->full_name = $this->name;
        $this->tab = 'export';
        $this->author = 'Bl Modules';
        $this->version = 1.5;
        $this->module_key = 'ddddeeab63827cf96dd07472fe9b6b5f';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Xml feeds');
        $this->description = $this->l('Export data from DB to xml');
        $this->confirmUninstall = $this->l('Are you sure you want to delete a module?');
    }

    function install()
    {
        if (!parent::install())
            return false;

        $sql_blmod_block =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_block
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
				`value` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
				`category` tinyint(2) DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_block_res = Db::getInstance()->Execute($sql_blmod_block);

        $sql_blmod_block_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_block
		(`id`, `name`, `value`, `category`) 
		VALUES 
		(49, 'desc-block-name', 'descriptions', 2),
		(48, 'cat-block-name', 'category', 2),
		(47, 'file-name', 'categories', 2),
		(53, 'img-block-name', 'images', 1),
		(52, 'desc-block-name', 'descriptions', 1),
		(51, 'cat-block-name', 'product', 1),
		(50, 'file-name', 'products', 1),
		(54, 'def_cat-block-name', 'default_category', 1),
		(55, 'attributes-block-name', 'attributes', 1)";
        $sql_blmod_block_val_res = Db::getInstance()->Execute($sql_blmod_block_val);

        $sql_blmod_feeds =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
				`use_cache` tinyint(1) DEFAULT NULL,
				`cache_time` int(5) DEFAULT NULL,
				`use_password` tinyint(1) DEFAULT NULL,
				`password` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
				`status` tinyint(1) DEFAULT NULL,
				`cdata_status` tinyint(1) DEFAULT NULL,
				`html_tags_status` tinyint(1) DEFAULT NULL,
				`one_branch` tinyint(1) DEFAULT NULL,
				`header_information` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`footer_information` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`extra_feed_row` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`feed_type` tinyint(2) DEFAULT NULL,
				`only_enabled` tinyint(1) DEFAULT NULL,		
				`split_feed` tinyint(1) DEFAULT NULL,
				`split_feed_limit` int(6) DEFAULT NULL,
				`cat_list` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
				`categories` tinyint(1) DEFAULT NULL,
				`total_views` int(11) NOT NULL DEFAULT  "0",
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_feeds_res = Db::getInstance()->Execute($sql_blmod_feeds);

        $sql_blmod_feeds_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_feeds
		(`id`, `name`, `use_cache`, `cache_time`, `use_password`, `password`, `status`, `cdata_status`, `html_tags_status`, `header_information`, `footer_information`, `extra_feed_row`, `feed_type`, `only_enabled`, `split_feed`, `split_feed_limit`) 
		VALUES
		(1, 'Products', 1, 800, 0, '', 1, 1, 1,'','', '', 1, 0, 0, 3000),
		(2, 'Categories', 1, 1440, 0, '', 1, 1, 1, '', '','', 2, 0, 0, 0)";
        $sql_blmod_feeds_val_res = Db::getInstance()->Execute($sql_blmod_feeds_val);

        $sql_blmod_fields =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_fields
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
				`status` tinyint(1) DEFAULT NULL,
				`title_xml` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
				`table` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
				`category` tinyint(1) DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_fields_res = Db::getInstance()->Execute($sql_blmod_fields);

        $sql_blmod_cache =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`feed_id` int(11) NOT NULL,
				`feed_part` int(11) NOT NULL DEFAULT "0",
				`file_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
				`last_cache_time` datetime DEFAULT NULL,
				`affiliate_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_cache_res = Db::getInstance()->Execute($sql_blmod_cache);

        $sql_blmod_statistics =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_statistics
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`feed_id` int(11) NOT NULL,
				`affiliate_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`date` datetime NOT NULL,
				`ip_address` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_statistics_res = Db::getInstance()->Execute($sql_blmod_statistics);

        $this->install_default_products_values(1);
        $this->install_default_categories_values(2);

        $sql_blmod_affliate_price =
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
			(
				`affiliate_id` int(11) NOT NULL AUTO_INCREMENT,
				`affiliate_name` varchar(255) CHARACTER SET utf8 NOT NULL,
				`affiliate_formula` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`xml_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,  
				PRIMARY KEY (`affiliate_id`)
			)';
        $sql_blmod_affliate_price_res = Db::getInstance()->Execute($sql_blmod_affliate_price);

        if (!$sql_blmod_block_res or !$sql_blmod_block_val_res or !$sql_blmod_feeds_res or !$sql_blmod_feeds_val_res or
            !$sql_blmod_fields_res or !$sql_blmod_cache_res or !$sql_blmod_statistics_res or !$sql_blmod_affliate_price_res
        )
            return false;

        @copy('../modules/xml_feeds/root_file/xml_feeds.php', '../xml_feeds.php');

        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_block');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_fields');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_statistics');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price');

        @unlink('../xml_feeds.php');

        return parent::uninstall();
    }

    public function getContent()
    {
        $this->_html = '<h2>' . $this->displayName . '</h2>';
        $tab = Tools::getValue('tab');
        $full_address_no_t = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . Tools::substr($_SERVER['PHP_SELF'], Tools::strlen(__PS_BASE_URI__)) . '?tab=' . $tab . '&configure=' . Tools::getValue('configure');
        $token = '&token=' . Tools::getValue('token');
        $this->_html .= '<link rel="stylesheet" type="text/css" href="../modules/xml_feeds/css/xml_feeds.css" />';
        $this->_html .= '<script type="text/javascript" src="../modules/xml_feeds/js/xml_feeds.js"></script>';

        $this->_html .= '
		    <div class="xml_feed_module">';

        if (_PS_VERSION_ >= '1.5' and _PS_VERSION_ < '1.6') {
            $this->_html .= '
            <style>
            .xml_feed_module .conf img, .xml_feed_module .warn img, .xml_feed_module .error img {
                display: none;
            }
            </style>';
        }

        $this->clean_cache();

        $POST = array();
        $POST['update_feeds_s'] = Tools::getValue('update_feeds_s');
        $POST['feeds_name'] = Tools::getValue('feeds_name');

        if (!empty($POST['update_feeds_s']) and !empty($POST['feeds_name'])) {
            $POST['name'] = Tools::getValue('name', $this->l('Products feed'));
            $POST['status'] = Tools::getValue('status');
            $POST['use_cache'] = Tools::getValue('use_cache');
            $POST['cache_time'] = Tools::getValue('cache_time');
            $POST['use_password'] = Tools::getValue('use_password');
            $POST['password'] = Tools::getValue('password');
            $POST['cdata_status'] = Tools::getValue('cdata_status');
            $POST['html_tags_status'] = Tools::getValue('html_tags_status');
            $POST['one_branch'] = Tools::getValue('one_branch');
            $POST['header_information'] = Tools::getValue('header_information');
            $POST['footer_information'] = Tools::getValue('footer_information');
            $POST['extra_feed_row'] = Tools::getValue('extra_feed_row');
            $POST['only_enabled'] = Tools::getValue('only_enabled');
            $POST['split_feed'] = Tools::getValue('split_feed');
            $POST['split_feed_limit'] = Tools::getValue('split_feed_limit');
            $POST['categories'] = Tools::getValue('categories');

            $cat_list = false;

            $POST['categoryBox'] = Tools::getValue('categoryBox');

            if (!empty($POST['categoryBox']))
                $cat_list = implode(',', $POST['categoryBox']);

            $this->update_feeds_s($POST['name'], $POST['status'], $POST['use_cache'], $POST['cache_time'], $POST['use_password'], $POST['password'], $POST['feeds_name'], $POST['cdata_status'], $POST['html_tags_status'], $POST['one_branch'], $POST['header_information'], $POST['footer_information'], $POST['extra_feed_row'], $POST['only_enabled'], $POST['split_feed'], $POST['split_feed_limit'], $cat_list, $POST['categories']);
        }

        if (!file_exists('../xml_feeds.php'))
            $this->_html .= '<div class="warning warn" style="width:96%;"><img src="../img/admin/warning.gif" alt="' . $this->l('error') . '" />' . $this->l('Missing "xml_feeds.php" file! Please copy file from "modules/xml_feeds/root_file/xml_feeds.php" to Prestashop root directory.') . '</div>';

        $delete_feed = Tools::getValue('delete_feed');

        if (!empty($delete_feed))
            $this->delete_feed($delete_feed);

        $POST['settings_cat'] = Tools::getValue('settings_cat');

        if (!empty($POST['settings_cat']))
            $this->update_fields($_POST, 2);

        $POST['settings_prod'] = Tools::getValue('settings_prod');

        if (!empty($POST['settings_prod']))
            $this->update_fields($_POST, 1);

        //page
        $this->page_structure($full_address_no_t, $token);

        return $this->_html;
    }

    public function install_default_products_values($feed_id = false)
    {
        if (empty($feed_id))
            return false;

        $sql_blmod_fields_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_fields
		(`name`, `status`, `title_xml`, `table`, `category`)
		VALUES 
		('available_later', 1, 'available_later', 'product_lang', '" . $feed_id . "'),
		('available_now', 1, 'available_now', 'product_lang', '" . $feed_id . "'),
		('name', 1, 'name', 'product_lang', '" . $feed_id . "'),
		('meta_title', 1, 'meta_title', 'product_lang', '" . $feed_id . "'),
		('meta_keywords', 1, 'meta_keywords', 'product_lang', '" . $feed_id . "'),
		('meta_description', 1, 'meta_description', 'product_lang', '" . $feed_id . "'),
		('link_rewrite', 1, 'link_rewrite', 'product_lang', '" . $feed_id . "'),
		('description_short', 1, 'description_short', 'product_lang', '" . $feed_id . "'),
		('description', 1, 'description', 'product_lang', '" . $feed_id . "'),
		('large_scene', 1, 'large_scene', 'img_blmod', '" . $feed_id . "'),
		('thumb_scene', 1, 'thumb_scene', 'img_blmod', '" . $feed_id . "'),
		('home', 1, 'home', 'img_blmod', '" . $feed_id . "'),
		('category', 1, 'category', 'img_blmod', '" . $feed_id . "'),
		('thickbox', 1, 'thickbox', 'img_blmod', '" . $feed_id . "'),
		('small', 1, 'small', 'img_blmod', '" . $feed_id . "'),
		('medium', 1, 'medium', 'img_blmod', '" . $feed_id . "'),
		('large', 1, 'large', 'img_blmod', '" . $feed_id . "'),
		('name', 1, 'category_default_name', 'category_lang', '" . $feed_id . "'),
		('out_of_stock', 1, 'out_of_stock', 'product', '" . $feed_id . "'),
		('id_category_default', 1, 'category_default_id', 'product', '" . $feed_id . "'),
		('quantity_discount', 1, 'quantity_discount', 'product', '" . $feed_id . "'),
		('quantity', 1, 'quantity', 'product', '" . $feed_id . "'),
		('ecotax', 1, 'ecotax', 'product', '" . $feed_id . "'),
		('wholesale_price', 1, 'wholesale_price', 'product', '" . $feed_id . "'),
		('price', 1, 'price', 'product', '" . $feed_id . "'),
		('date_upd', 1, 'date_upd', 'product', '" . $feed_id . "'),
		('date_add', 1, 'date_add', 'product', '" . $feed_id . "'),
		('active', 1, 'active', 'product', '" . $feed_id . "'),
		('on_sale', 1, 'on_sale', 'product', '" . $feed_id . "'),
		('weight', 1, 'weight', 'product', '" . $feed_id . "'),
		('location', 1, 'location', 'product', '" . $feed_id . "'),
		('name', 1, 'manufacturer_name', 'manufacturer', '" . $feed_id . "'),
		('id_manufacturer', 1, 'manufacturer_id', 'product', '" . $feed_id . "'),
		('id_product', 1, 'product_id', 'product', '" . $feed_id . "'),
		('id_supplier', 1, 'supplier_id', 'product', '" . $feed_id . "'),
		('price_shipping_blmod', 1, 'price_shipping', 'bl_extra', '" . $feed_id . "'),
		('price_sale_blmod', 1, 'price_sale', 'bl_extra', '" . $feed_id . "'),
		('product_url_blmod', 1, 'product_url', 'bl_extra', '" . $feed_id . "'),
		('supplier_reference', 1, 'supplier_reference', 'product', '" . $feed_id . "'),
		('ean13', 1, 'ean13', 'product', '" . $feed_id . "'),
		('reference', 1, 'reference', 'product', '" . $feed_id . "')";

        Db::getInstance()->Execute($sql_blmod_fields_val);

        $sql_blmod_block_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_block
		(`name`, `value`, `category`) 
		VALUES 
		('desc-block-name', 'descriptions', '" . $feed_id . "'),
		('cat-block-name', 'category', '" . $feed_id . "'),
		('file-name', 'categories', '" . $feed_id . "'),
		('img-block-name', 'images', '" . $feed_id . "'),
		('desc-block-name', 'descriptions', '" . $feed_id . "'),
		('cat-block-name', 'product', '" . $feed_id . "'),
		('file-name', 'products', '" . $feed_id . "'),
		('def_cat-block-name', 'default_category', '" . $feed_id . "'),
		('attributes-block-name', 'attributes', '" . $feed_id . "')";
        Db::getInstance()->Execute($sql_blmod_block_val);
    }

    public function install_default_categories_values($feed_id = false)
    {
        if (empty($feed_id))
            return false;

        $sql_blmod_fields_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_fields
		(`name`, `status`, `title_xml`, `table`, `category`)
		VALUES 
		('meta_description', 1, 'meta_description', 'category_lang', '" . $feed_id . "'),
		('meta_keywords', 0, 'meta_keywords', 'category_lang', '" . $feed_id . "'),
		('meta_title', 0, 'meta_title', 'category_lang', '" . $feed_id . "'),
		('link_rewrite', 0, 'link_rewrite', 'category_lang', '" . $feed_id . "'),
		('description', 0, 'description', 'category_lang', '" . $feed_id . "'),
		('name', 1, 'name', 'category_lang', '" . $feed_id . "'),
		('id_lang', 1, 'id_lang', 'category_lang', '" . $feed_id . "'),
		('date_upd', 0, 'data_upd', 'category', '" . $feed_id . "'),
		('date_add', 0, 'data_add', 'category', '" . $feed_id . "'),
		('active', 0, 'active', 'category', '" . $feed_id . "'),
		('level_depth', 0, 'level_depth', 'category', '" . $feed_id . "'),
		('id_parent', 1, 'category_parent_id', 'category', '" . $feed_id . "'),
		('id_category', 1, 'category_id', 'category', '" . $feed_id . "')";

        Db::getInstance()->Execute($sql_blmod_fields_val);

        $sql_blmod_block_val =
            "INSERT INTO " . _DB_PREFIX_ . "blmod_xml_block
		(`name`, `value`, `category`) 
		VALUES 
		('desc-block-name', 'descriptions', '" . $feed_id . "'),
		('cat-block-name', 'category', '" . $feed_id . "'),
		('file-name', 'categories', '" . $feed_id . "')";

        Db::getInstance()->Execute($sql_blmod_block_val);
    }

    public function clean_cache()
    {
        $cache = Db::getInstance()->ExecuteS('
			SELECT f.cache_time, c.feed_id, c.feed_part, c.file_name, c.last_cache_time
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds f
			LEFT JOIN ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache c ON
			f.id = c.feed_id
			WHERE c.feed_id > 0
		');

        if (empty($cache))
            return true;

        $now = date('Y-m-d h:i:s');

        foreach ($cache as $c) {
            $cache_period = date('Y-m-d h:i:s', strtotime($c['last_cache_time'] . '+' . $c['cache_time'] . ' minutes'));

            if ($now > $cache_period and !empty($c['file_name'])) {
                $file_url = 'xml_files/' . $c['file_name'] . '.xml';

                @unlink('../modules/xml_feeds/xml_files/' . $c['file_name'] . '.xml');

                if (!file_exists($file_url))
                    Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache WHERE feed_id = "' . $c['feed_id'] . '" AND feed_part = "' . $c['feed_part'] . '"');
            }
        }
    }

    public function status($status = false, $disabled = false)
    {
        if ($disabled)
            $disabled = 'disabled';
        else
            $disabled = '';

        if (isset($status) and $status == 1)
            $status_text = ' value = "1" checked ' . $disabled . ' /> <img src="../img/admin/enabled.gif" alt = "' . $this->l('Enabled') . '" />' . $this->l('Enabled');
        else
            $status_text = ' value = "1" ' . $disabled . '/> <img src="../img/admin/disabled.gif" alt = "' . $this->l('Disabled') . '" />' . $this->l('Disabled');

        return $status_text;
    }

    public function page_structure($full_address_no_t = false, $token = false)
    {
        $page = Tools::getValue('page');
        $add_feed = Tools::getValue('add_feed');
        $statistics = false;
        $add_affiliate_price = Tools::getValue('add_affiliate_price');
        $about_page = Tools::getValue('about_page');

        if (empty($page) and empty($statistics) and empty($add_affiliate_price) and empty($add_feed))
            $page = $this->check_get_default_feed();

        $this->_html .= '
			<div style="float: left; width: 230px;">';
        $this->categories($full_address_no_t, $token, $page, $statistics, $add_affiliate_price, $add_feed, $about_page);
        $this->_html .= '</div>
			<div style="float: left; margin-left: 20px; width: 677px;">';

        if (!empty($add_feed))
            $this->add_new_feed($add_feed, $full_address_no_t, $token);
        elseif (!empty($add_affiliate_price))
            $this->add_affiliate_price($full_address_no_t, $token);
        elseif (!empty($about_page))
            $this->about_page(); else
            $this->feeds_settings($page);

        $this->_html .= '
			</div>
			<div style = "clear: both; font-size: 0px;"></div>
		</div>
		';

        //end .xml_feed_module
    }

    public function about_page()
    {
        $module_version_ok = '<span style="color: green; font-size: 11px; line-height: 11px;">[' . $this->l('Module\'s version is up to date') . ']</span>';

        $module_version = $module_version_ok;

        if (!empty($this->full_name) and !empty($this->version)) {
            $current_version = Tools::file_get_contents('http://www.blmodules.com/check_module_version.php?n=' . $this->full_name . '&v=' . $this->version);

            if (!empty($current_version))
                $module_version = '<span style="color: orange; font-size: 11px; line-height: 11px;">[' . $this->l($current_version . ' version is available! You can contact us and update.') . ']</span>';
        }

        $this->_html .= '
			<style type="text/css">
			.blmod_about a{
				color: #268CCD;
			}
			.blmod_about{
				line-height: 25px;
			}
			</style>
			<fieldset style="width: 560px;">
				<legend><img src="../img/admin/help.png" alt="" title="" />' . $this->l('About') . '</legend>
				<div class="blmod_about">
					<div style="float: right;">
						<a href="http://www.blmodules.com" target="_blank">
							<img style="border: 1px solid #CCCED7; padding: 0px;" alt="Bl Modules" title="Bl Modules home page" src="../modules/' . $this->name . '/img/blmod_logo.png" />
						</a>
					</div>
					<div style="float: left; width: 350px;">
						' . $this->l('Contact module\'s support team at') . ' <a href="mailto:blmodules@gmail.com">blmodules@gmail.com</a><br/>
						' . $this->l('Find more information at') . '  <a href="http://www.blmodules.com" target="_blank">www.blmodules.com</a><br/>
						' . $this->l('Module version:') . ' ' . $this->version . '<br/>' . $module_version . '
					</div>
				</div>
			</fieldset>';
    }

    public function check_get_default_feed()
    {
        $feed = Db::getInstance()->getRow('
			SELECT `id`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds
			ORDER BY `id` DESC
		');

        if (!empty($feed['id']))
            return $feed['id'];

        return false;
    }

    public function categories($full_address_no_t, $token, $page = false, $statistics = false, $add_affiliate_price = false, $add_feed = false, $about_page = false)
    {
        if (empty($page))
            $page = $statistics;

        $style = 'color: #268CCD;';

        $feeds = Db::getInstance()->ExecuteS('
			SELECT `id`, `name`, `feed_type`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds
			ORDER BY `id` ASC
		');

        $products = array();
        $categories = array();
        $add_affiliate = false;
        $add_feed_style1 = false;
        $add_feed_style2 = false;
        $style_about = false;

        if (!empty($add_affiliate_price)) {
            $add_affiliate = $style;
        }

        if (!empty($about_page)) {
            $style_about = $style;
            $page = false;
        }

        if (!empty($feeds)) {
            foreach ($feeds as $id => $f) {
                if ($f['feed_type'] == 1)
                    $products[] = $f;
                elseif ($f['feed_type'] == 2)
                    $categories[] = $f;
            }
        }

        if ($add_feed == 1)
            $add_feed_style1 = $style;
        elseif ($add_feed == 2)
            $add_feed_style2 = $style;

        $this->_html .= '		
			<fieldset><legend><img src="../img/admin/summary.png" alt="' . $this->l('Feeds') . '" title="' . $this->l('Feeds') . '" />' . $this->l('List of feeds') . '</legend>
				<table border="0" width="100%">
					<tr>
						<td style ="line-height: 20px;">
							<div style="margin-bottom: 10px;" >  
								<img src="../img/admin/products.gif" alt="" title="" /><span style="font-weight:bold;">' . $this->l('Feeds of products') . '</span><br/>';

        if (!empty($products))
            foreach ($products as $p) {
                $style_s = false;

                if ($page == $p['id'])
                    $style_s = $style;

                $this->_html .= '<a style="margin-left: 15px; font-size: 90%; ' . $style_s . '" href="' . $full_address_no_t . '&page=' . $p['id'] . $token . '">' . $p['name'] . '</a>
										<a href="' . $full_address_no_t . '&delete_feed=' . $p['id'] . $token . '" onclick="return confirm(\'' . $this->l('Are you sure you want to delete?') . '\')"><img style="margin-bottom:3px;" alt="' . $this->l('Delete feed') . '" title="' . $this->l('Delete feed') . '" src="../img/admin/delete.gif"</a><br/>';
            }

        $this->_html .= '
								<a style="margin-left: 15px; ' . $add_feed_style1 . '" href="' . $full_address_no_t . '&add_feed=1' . $token . '"><img src="../img/admin/add.gif" alt="" title="" /> ' . $this->l('Add new') . '</a><br/>
								<a style="margin-left: 15px; ' . $add_affiliate . '" href="' . $full_address_no_t . '&add_affiliate_price=1' . $token . '"><img src="../img/admin/add.gif" alt="" title="" /> ' . $this->l('Add a new affiliate price') . '</a>
							</div>
							<div>
								<hr/>
								<img src="../img/admin/tab-categories.gif" alt="" title="" /><span style="font-weight:bold;">' . $this->l('Feeds of categories') . '</span><br/>';

        if (!empty($categories))
            foreach ($categories as $c) {
                $style_s = false;

                if ($page == $c['id'])
                    $style_s = $style;

                $this->_html .= '<a style="margin-left: 15px; font-size: 90%; ' . $style_s . '" href="' . $full_address_no_t . '&page=' . $c['id'] . $token . '">' . $c['name'] . '</a>
										<a href="' . $full_address_no_t . '&delete_feed=' . $c['id'] . $token . '" onclick="return confirm(\'' . $this->l('Are you sure you want to delete?') . '\')"><img style="margin-bottom:3px;" alt="' . $this->l('Delete feed') . '" title="' . $this->l('Delete feed') . '" src="../img/admin/delete.gif"</a><br/>';
            }

        $this->_html .= '
								<a style="margin-left: 15px; ' . $add_feed_style2 . '" href="' . $full_address_no_t . '&add_feed=2' . $token . '"><img src="../img/admin/add.gif" alt="" title="" /> ' . $this->l('Add new') . '</a>
								<br/><hr/>
								<img src="../img/admin/help.png" alt="" title="" />
								<a style="' . $style_about . '" href = "' . $full_address_no_t . '&about_page=1' . $token . '">' . $this->l('About') . '</a>
							</div>
						</td>
					</tr>
				</table>
		</fieldset><br/><br/>';
    }

    public function add_affiliate_price($full_address_no_t, $token)
    {
        $post_url = $_SERVER['REQUEST_URI'];

        $delete_affiliate_price = Tools::getValue('delete_affiliate_price');

        if (!empty($delete_affiliate_price)) {
            $get_affiliate_name = Db::getInstance()->getRow('SELECT `affiliate_name` FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price WHERE affiliate_id = "' . htmlspecialchars($delete_affiliate_price, ENT_QUOTES) . '"');

            if (!empty($get_affiliate_name['affiliate_name']))
                $get_affiliate_info = Db::getInstance()->ExecuteS('SELECT `file_name` FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache WHERE affiliate_name = "' . $get_affiliate_name['affiliate_name'] . '"');


            if (Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price WHERE affiliate_id = "' . htmlspecialchars($delete_affiliate_price, ENT_QUOTES) . '"')) {
                //Delete feeds cache
                if (!empty($get_affiliate_info)) {
                    Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache WHERE affiliate_name = "' . $get_affiliate_name['affiliate_name'] . '"');

                    foreach ($get_affiliate_info as $c)
                        @unlink('../modules/xml_feeds/xml_files/' . $c['file_name'] . '.xml');
                }
            }

            $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Delete successfully') . '</div>';
        }

        $POST = array();
        $POST['add_affiliate_price_post'] = Tools::getValue('add_affiliate_price_post');

        if (!empty($POST['add_affiliate_price_post'])) {
            $POST['name'] = Tools::getValue('name');
            $POST['price'] = Tools::getValue('price');
            $POST['xml_name'] = Tools::getValue('xml_name');

            if (empty($POST['name']) or empty($POST['price']) or empty($POST['xml_name']))
                $this->_html .= '<div style="width: 94%" class="warning warn"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Please fill in all fields') . '</div>';
            else {
                $find_price = strpos(' ' . $POST['price'], 'price');

                if (empty($find_price)) {
                    $this->_html .= '<div style="width: 94%" class="warning warn"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Please insert the prices constant, the word "price". I will replace it in the product price.') . '</div>';
                } else {
                    Db::getInstance()->Execute('
						INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
						(`affiliate_name`, `affiliate_formula`, `xml_name`)
						VALUE
						("' . htmlspecialchars($this->on_special($POST['name']), ENT_QUOTES) . '", "' . htmlspecialchars($POST['price'], ENT_QUOTES) . '", "' . htmlspecialchars($this->on_special($POST['xml_name']), ENT_QUOTES) . '")
					');

                    $this->delete_cache(false, true);

                    $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Insert successfully') . '</div>';
                }
            }
        }

        $post_url = str_replace('delete_affiliate_price', 'delete_affiliate_price_done', $post_url);

        $this->_html .= '		
			<form action="' . $post_url . '" method="post">
				<fieldset><legend><img src="../img/admin/add.gif" alt="' . $this->l('Add a new affiliate price') . '" title="' . $this->l('Add a new affiliate price') . '" />' . $this->l('Add a new affiliate price') . '</legend>
					<table border="0" width="100%" cellpadding="3" cellspacing="0">
						<tr>
							<td width="20"><img src="../img/admin/translation.gif" /></td>
							<td width="140"><b>' . $this->l('Affiliate name:') . '</b></td>
							<td>
								<input style="width: 462px;" type="text" name="name" value="">
							</td>
						</tr>					
						<tr>
							<td width="20"><img src="../img/admin/prefs.gif" /></td>
							<td width="140"><b>' . $this->l('Field name in xml:') . '</b></td>
							<td>
								<input style="width: 462px;" type="text" name="xml_name" value="">
							</td>
						</tr>						
						<tr>
							<td width="20"><img src="../img/admin/invoice.gif" /></td>
							<td width="140"><b>' . $this->l('Price formula:') . '</b></td>
							<td>
								<input style="width: 462px;" type="text" name="price" value="">
								<div class="bl_comments">' . $this->l('[Word "price" will be replaced in the product price, formulas example: price - 15]') . '</div>
							</td>
						</tr>						
						</table>
						<center><input type="submit" name="add_affiliate_price_post" value="' . $this->l('Insert') . '" class="button" /></center>';


        $prices = Db::getInstance()->ExecuteS('
			SELECT `affiliate_id`, `affiliate_name`, `affiliate_formula`, `xml_name`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
			ORDER BY affiliate_name ASC
		');

        if (!empty($prices)) {
            $this->_html .= '<br/><hr/><br/><ul class="bl_affiliate_prices_list">';

            foreach ($prices as $p) {
                $this->_html .= '
				<li>
					' . htmlspecialchars_decode($p['affiliate_name'], ENT_QUOTES) . ': <span style="color: #268CCD">' . htmlspecialchars_decode($p['affiliate_formula'], ENT_QUOTES) . '</span>	
					<a href="' . $full_address_no_t . '&add_affiliate_price=1&delete_affiliate_price=' . $p['affiliate_id'] . $token . '" onclick="return confirm(\'' . $this->l('Are you sure you want to delete?') . '\')">
						<img style="margin-bottom:0px;" alt="' . $this->l('Delete affiliate price') . '" title="' . $this->l('Delete affiliate price') . '" src="../img/admin/delete.gif">
					</a><br/>
					<div style="margin-bottom: 3px; margin-top: 3px;" class="bl_comments">&#60;' . $p['xml_name'] . '&#62;...&#60;/' . $p['xml_name'] . '&#62;</div>
					<div class="bl_comments">URL: http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'xml_feeds.php?id=<b>FEED_ID</b>&affiliate=' . htmlspecialchars_decode($p['affiliate_name'], ENT_QUOTES) . '</div>
				</li>';
            }

            $this->_html .= '</ul>';
        }

        $this->_html .= '
					</fieldset>
			</form>
		<br/>';
    }

    public function add_new_feed($feed_type = 1, $full_address_no_t, $token)
    {
        $feed_type = (int)$feed_type;

        $POST = array();

        $POST['add_new_feed_insert'] = Tools::getValue('add_new_feed_insert');

        if (!empty($POST['add_new_feed_insert'])) {
            $POST['name'] = Tools::getValue('name');
            $POST['feed_type'] = Tools::getValue('feed_type');

            if (!empty($POST['name'])) {
                Db::getInstance()->Execute('
					INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_feeds
					(`name`, `status`, `feed_type`, `cache_time`, `cdata_status`, `html_tags_status`, `split_feed_limit`)
					VALUE
					("' . htmlspecialchars($POST['name'], ENT_QUOTES) . '", "1", "' . htmlspecialchars($POST['feed_type'], ENT_QUOTES) . '", "800", "1", "1", "5000")
				');

                $new_id = Db::getInstance()->Insert_ID();

                if ($POST['feed_type'] == 1)
                    $this->install_default_products_values($new_id);
                else
                    $this->install_default_categories_values($new_id);

                header('Location: ' . $full_address_no_t . '&page=' . $new_id . $token);
                die;
            } else
                $this->_html .= '<div class="warning warn"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Error, empty feed name') . '</div>';
        }

        $this->_html .= '		
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset><legend><img src="../img/admin/add.gif" alt="' . $this->l('Add new feed') . '" title="' . $this->l('Add new feed') . '" />' . $this->l('Add new feed') . '</legend>
					<table border="0" width="100%" cellpadding="3" cellspacing="0">
						<tr>
							<td width="20"><img src="../img/admin/translation.gif" /></td>
							<td width="140"><b>' . $this->l('Feed name:') . '</b></td>
							<td>
								<input style="width: 462px;" type="text" name="name" value="">
							</td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						</table>
						<input type="hidden" name="feed_type" value="' . $feed_type . '">
						<center><input type="submit" name="add_new_feed_insert" value="' . $this->l('Insert') . '" class="button" /></center>
				</fieldset>
			</form>
		<br/>';
    }

    public function pagination($full_address_no_t, $token, $in_cat = 0, $max_in_page = 60)
    {
        if ($max_in_page >= $in_cat)
            return array(0, $in_cat, false);

        $page = Tools::getValue('page_number');
        $curent_page = $page;
        $this->_html2 = '<div class = "bl_pagination">';

        if (empty($page)) {
            $page = 1;
            $curent_page = 1;
        }

        $start = ($max_in_page * $page) - $max_in_page;

        if ($in_cat <= $max_in_page)
            $num_of_pages = 1;
        elseif (($in_cat % $max_in_page) == 0)
            $num_of_pages = $in_cat / $max_in_page; else
            $num_of_pages = $in_cat / $max_in_page + 1;

        if ($curent_page > 1) {
            $back = $curent_page - 1;
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=' . $back . $token . '"> << </a>' . ' ';
        }

        $next = $curent_page + 1;

        $this->_html2 .= ' | ';
        $num_of_pages_f = $num_of_pages;

        if ($curent_page - 4 > 1)
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=1' . $token . '">1</a> | ';

        if ($curent_page - 5 > 1)
            $this->_html2 .= ' ... ';

        $firs_element = $curent_page - 4;

        if ($firs_element < 1)
            $firs_element = 1;

        for ($i = $firs_element; $i < $curent_page; $i++) {
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=' . $i . $token . '">' . $i . '</a> | ';
        }

        $this->_html2 .= $curent_page . ' | ';

        for ($i = $curent_page + 1; $i < $curent_page + 5; $i++) {
            if ($i > $num_of_pages_f)
                break;
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=' . $i . $token . '">' . $i . '</a> | ';
        }

        if ($curent_page + 5 < $num_of_pages_f)
            $this->_html2 .= ' ... | ';

        if ($curent_page + 4 < $num_of_pages_f)
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=' . $num_of_pages_f . $token . '">' . $num_of_pages_f . '</a> | ';

        if ($curent_page + 1 < $num_of_pages) {
            $next = $curent_page + 1;
            $this->_html2 .= '<a href = "' . $full_address_no_t . '&page_number=' . $next . $token . '"> >> </a>';
        }

        $this->_html2 .= '</div>';

        return array($start, $max_in_page, $this->_html2);
    }

    public function feeds_settings($page)
    {
        $s = Db::getInstance()->getRow('
			SELECT name, status, use_cache, cache_time, use_password, password, cdata_status, html_tags_status, one_branch, header_information, 
			footer_information, extra_feed_row, feed_type, only_enabled, split_feed, split_feed_limit, categories, cat_list
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds
			WHERE id = "' . $page . '"
		');

        $s['name'] = isset($s['name']) ? $s['name'] : false;
        $s['status'] = isset($s['status']) ? $s['status'] : false;
        $s['use_cache'] = isset($s['use_cache']) ? $s['use_cache'] : false;
        $s['cache_time'] = !empty($s['cache_time']) ? $s['cache_time'] : 0;
        $s['use_password'] = isset($s['use_password']) ? $s['use_password'] : false;
        $s['password'] = !empty($s['password']) ? $s['password'] : false;
        $s['cdata_status'] = isset($s['cdata_status']) ? $s['cdata_status'] : false;
        $s['html_tags_status'] = isset($s['html_tags_status']) ? $s['html_tags_status'] : false;
        $s['one_branch'] = isset($s['one_branch']) ? $s['one_branch'] : false;
        $s['header_information'] = isset($s['header_information']) ? htmlspecialchars_decode($s['header_information'], ENT_QUOTES) : false;
        $s['footer_information'] = isset($s['footer_information']) ? htmlspecialchars_decode($s['footer_information'], ENT_QUOTES) : false;
        $s['extra_feed_row'] = isset($s['extra_feed_row']) ? htmlspecialchars_decode($s['extra_feed_row'], ENT_QUOTES) : false;
        $s['feed_type'] = isset($s['feed_type']) ? $s['feed_type'] : false;
        $s['only_enabled'] = isset($s['only_enabled']) ? $s['only_enabled'] : false;
        $s['split_feed'] = isset($s['split_feed']) ? $s['split_feed'] : false;
        $s['split_feed_limit'] = isset($s['split_feed_limit']) ? $s['split_feed_limit'] : false;
        $s['cat_list'] = isset($s['cat_list']) ? $s['cat_list'] : false;

        if ($s['feed_type'] == '1') {
            $name = $this->l('Products');

            $prices_affiliate = Db::getInstance()->ExecuteS('
				SELECT `affiliate_id`, `affiliate_name`, `affiliate_formula`, `xml_name`
				FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
				ORDER BY affiliate_name ASC
			');
        } elseif ($s['feed_type'] == '2')
            $name = $this->l('Categories');

        $name_full = $s['name'] . ', ' . $name;

        if ($s['use_password'])
            $pass_in_link = '&password=XML_PASSWORD';
        else
            $pass_in_link = '';

        $multistore_status = false;

        if (_PS_VERSION_ >= '1.5') {

            $multistore = Shop::getShops();

            if (count($multistore) > 1) {
                $multistore_status = true;
            }
        }

        $link = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'xml_feeds.php?id=' . $page . $pass_in_link . '&affiliate=affiliate_name';

        $this->_html .= '		
			<fieldset><legend><img src="../img/admin/subdomain.gif" alt="' . $this->l('XML file:') . '" title="' . $this->l('XML file:') . '" />' . $this->l('XML file:') . '</legend>
				<table border="0" width="100%" cellpadding="1" cellspacing="0">
					<tr>
						<td>
							<input id="feed_url" style="width: 98%; margin-bottom: 7px;" type="text" name="xml_link" value="' . $link . '" /><br/>
								<a style="font-weight: bold; text-decoration: underline;" href="' . $link . '" target="_blank">' . $this->l('Open') . '</a> | <a style="font-weight: bold; text-decoration: underline;" href="' . $link . '&download=1" target="_blank">' . $this->l('Download') . '</a>';

        if ($multistore_status) {
            $this->_html .= '<div id="multistore_url" class="bl_comments" style="float: right; cursor: pointer; padding-right: 4px;">' . $this->l('[Show/Hide Multistore options]') . '</div>';
        }

        if (!empty($prices_affiliate)) {
            $this->_html .= '<div id="affiliate_price_url" class="bl_comments" style="cursor: pointer; float: right; padding-right: 10px;">' . $this->l('[Show/Hide Special prices affiliate]') . '</div><div class="cb"></div>';
            $this->_html .= '<div id="affiliate_price_url_list" style="margin-top: 15px; display:none;"><hr/>';

            foreach ($prices_affiliate as $p) {
                $link = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'xml_feeds.php?id=' . $page . $pass_in_link . '&affiliate=' . $p['affiliate_name'];

                $this->_html .= '
									<input style="width: 98%; margin-bottom: 7px;" type="text" name="xml_link" value="' . $link . '" /><br/>
									<span style="color: #268CCD; font-size: 12px;">Affiliate ' . $p['affiliate_name'] . ':</span> <a style="font-weight: bold; text-decoration: underline;" href="' . $link . '" target="_blank">' . $this->l('Open') . '</a> | <a style="font-weight: bold; text-decoration: underline;" href="' . $link . '&download=1" target="_blank">' . $this->l('Download') . '</a>
									<div class="bl_comments">' . $this->l('Special prices affiliate:') . ' ' . $p['affiliate_formula'] . ', ' . $this->l('price name:') . ' ' . $p['xml_name'] . '</div>
									<hr/>';
            }

            $this->_html .= '</div>';
        }

        if ($multistore_status) {
            $this->_html .= '
								<div id="multistore_url_list" style="margin-top: 15px; display:none;">';

            $this->_html .= '<div><hr/>';
            $this->_html .= '<label for="all_multistore"><input class="multistore_url_checkbox" id="all_multistore" type="checkbox" name="all_multistore" value="0" checked> ' . $this->l('All stores') . '</label></div>';

            $this->_html .= '<div><hr/>';
            $this->_html .= '<label for="domain_multistore"><input class="multistore_url_checkbox" id="domain_multistore" type="checkbox" name="domain_multistore" value="0"> ' . $this->l('Detect by domain') . '</label></div>';

            foreach ($multistore as $h) {
                $this->_html .= '<div><hr/>';
                $this->_html .= '<label for="multistore_' . $h['id_shop'] . '"><input id="multistore_' . $h['id_shop'] . '" class="multistore_url_checkbox" type="checkbox" name="status" value="' . $h['id_shop'] . '"> ' . $h['name'] . '</label></div>';
            }

            $this->_html .= '</div>';
        }

        $this->_html .= '
						</td>
					</tr>
				</table>
			</fieldset>
		<br/>';

        $this->_html .= '		
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset><legend><img src="../img/admin/submenu-configuration.gif" alt="' . $name_full . '" title="' . $name_full . '" />' . $name_full . '</legend>
				<table border="0" width="100%" cellpadding="3" cellspacing="0">
					<tr>
						<td width="20"><img src="../img/admin/nav-home.gif" /></td>
						<td width="140"><b>' . $this->l('Feed id:') . '</b></td>
						<td>
							<input style="width: 35px;" type="text" readonly="readonly" name="feed_id" value="' . $page . '">
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/translation.gif" /></td>
						<td width="140"><b>' . $this->l('Feed name:') . '</b></td>
						<td>
							<input style="width: 462px;" type="text" name="name" value="' . $s['name'] . '">
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/access.png" /></td>
						<td width="140"><b>' . $this->l('XML status:') . '</b></td>
						<td>
							<label for="xmf_feed_status">
								<input id="xmf_feed_status" type="checkbox" name="status"';
        $this->_html .= $this->status($s['status']) . '
							</label>	
						</td>
					</tr>					
					<tr>
						<td width="20"><img src="../img/admin/tab-tools.gif" /></td>
						<td width="140"><b>' . $this->l('Add CDATA:') . ' </b><a style="#7F7F7F; font-size:11px;" href="http://www.w3schools.com/xml/xml_cdata.asp" target="_blank">(?)</a></td>
						<td>
							<label for="cdata_status">
								<input id="cdata_status" type="checkbox" name="cdata_status"';
        $this->_html .= $this->status($s['cdata_status']) . '
							</label>
						</td>
					</tr>					
					<tr>
						<td width="20"><img src="../img/admin/translation.gif" /></td>
						<td width="140"><b>' . $this->l('Drop html tags:') . '</b></td>
						<td>
							<label for="html_tags_status">
								<input id="html_tags_status" type="checkbox" name="html_tags_status"';
        $this->_html .= $this->status($s['html_tags_status']) . '
							</label>
						</td>
					</tr>					
					<tr>
						<td width="20"><img src="../img/admin/summary.png" /></td>
						<td width="140"><b>' . $this->l('All in one branch:') . '</b></td>
						<td>
							<label for="one_branch">
								<input id="one_branch" type="checkbox" name="one_branch"';
        $this->_html .= $this->status($s['one_branch']) . '
							</label>
						</td>
					</tr>					
					<tr>
						<td width="20"><img src="../img/admin/enabled.gif" /></td>
						<td width="140"><b>' . $this->l('Only enabled ' . $name . ':') . '</b></td>
						<td>
							<label for="only_enabled">
								<input id="only_enabled" type="checkbox" name="only_enabled"';
        $this->_html .= $this->status($s['only_enabled']) . '
							</label>
						</td>
					</tr>';

        if ($s['feed_type'] == '1') {
            $this->_html .= '
						<tr>
							<td width="20"><img src="../img/admin/copy_files.gif" /></td>
							<td width="140"><b>' . $this->l('Split feed:') . '</b></td>
							<td>
								<label for="split_feed">
									<input id="split_feed" type="checkbox" name="split_feed"';
            $this->_html .= $this->status($s['split_feed']) . '
								</label>
							</td>
						</tr>
						<tr>
							<td width="20"><img src="../img/admin/invoice.gif" /></td>
							<td width="140"><b>' . $this->l('Split feed limit:') . '</b></td>
							<td>
								<input type="text" name="split_feed_limit" value="' . $s['split_feed_limit'] . '" size="6">
							</td>
						</tr>';
        }

        $this->_html .= '
					<tr>
						<td width="20"><img src="../img/admin/comment.gif" /></td>
						<td width="140"><b>' . $this->l('Header information:') . '</b></td>
						<td>
							<textarea name="header_information" style="width: 462px; height: 40px;">' . $s['header_information'] . '</textarea>
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/comment.gif" /></td>
						<td width="140"><b>' . $this->l('Footer information:') . '</b></td>
						<td>
							<textarea name="footer_information" style="width: 462px; height: 40px;">' . $s['footer_information'] . '</textarea>
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/edit.gif" /></td>
						<td width="140"><b>' . $this->l('Extra feed rows:') . '</b></td>
						<td>
							<textarea name="extra_feed_row" style="width: 462px; height: 65px;">' . $s['extra_feed_row'] . '</textarea>
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/database_gear.gif" /></td>
						<td width="140"><b>' . $this->l('Use cache:') . '</b></td>
						<td>
							<label for="use_cache">
								<input id="use_cache" type="checkbox" name="use_cache"';
        $this->_html .= $this->status($s['use_cache']) . '
							</label>
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/time.gif" /></td>
						<td width="140"><b>' . $this->l('Cache time:') . '</b></td>
						<td>
							<input type="text" name="cache_time" value="' . $s['cache_time'] . '" size="6"> ' . $this->l('[min.]') . '
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/nav-user.gif" /></td>
						<td width="140"><b>' . $this->l('Only with password:') . '</b></td>
						<td>
							<label for="use_password">
								<input id="use_password" type="checkbox" name="use_password"';
        $this->_html .= $this->status($s['use_password']) . '
							</label>
						</td>
					</tr>
					<tr>
						<td width="20"><img src="../img/admin/htaccess.gif" /></td>
						<td width="140"><b>' . $this->l('Password:') . '</b></td>
						<td>
							<input type="password" name="password" value="' . $s['password'] . '">';

        if ($s['use_password'] == 1 and empty($s['password']))
            $this->_html .= ' <span style="color: RED;">' . $this->l('[Please set password]') . '</span>';

        $this->_html .= '
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
				</table>
				<center><input type="submit" name="update_feeds_s" value="' . $this->l('Update') . '" class="button" /></center>		
				<input type="hidden" name="feeds_name" value="' . $page . '" />
			</fieldset>
			</form><br/>';

        if ($s['feed_type'] == '1') {
            $prod_id = $this->get_rand_product();

            if (empty($prod_id))
                $this->_html .= '<div class="warning warn" style="width: 95%"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('You do not have a product with attributes, consequently attribute options are not displayed') . '</div>';

            $this->products_xml($page);
        } elseif ($s['feed_type'] == '2')
            $this->categories_xml($page);
    }

    public function recurseCategoryForInclude_pref($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = NULL, $selected = array())
    {
        $img_type = 'gif';

        if (_PS_VERSION_ >= '1.4.0')
            $img_type = 'png';

        global $done;

        static $irow;

        if (!isset($done[$current['infos']['id_parent']]))
            $done[$current['infos']['id_parent']] = 0;
        $done[$current['infos']['id_parent']] += 1;

        $categories[$current['infos']['id_parent']] = isset($categories[$current['infos']['id_parent']]) ? $categories[$current['infos']['id_parent']] : false;

        $todo = sizeof($categories[$current['infos']['id_parent']]);
        $doneC = $done[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;
        $img = $level == 1 ? 'lv1.' . $img_type : 'lv' . $level . '_' . ($todo == $doneC ? 'f' : 'b') . '.' . $img_type;

        $checked = false;

        if (in_array($id_category, $selected))
            $checked = 'checked="yes"';

        $this->_html .= '
		<tr class="' . ($irow++ % 2 ? 'alt_row' : '') . '">
			<td class="center">
				<input type="checkbox" id="categoryBox_' . $id_category . '" name="categoryBox[]" ' . $checked . ' value="' . $id_category . '" class="noborder">
			</td>
			<td>
				' . $id_category . '
			</td>
			<td>
				<img src="../img/admin/' . $img . '" alt="" /> &nbsp;<label style="line-height: 26px;" for="categoryBox_' . $id_category . '" class="t">' . Tools::stripslashes($current['infos']['name']) . '
			</td>
		</tr>';

        if (isset($categories[$id_category]))
            foreach ($categories[$id_category] AS $key => $row)
                if ($key != 'infos')
                    $this->recurseCategoryForInclude_pref($indexedCategories, $categories, $categories[$id_category][$key], $key, null, $selected);
    }

    static public function getCategories($id_lang, $active = true, $order = true)
    {
        //if (!Validate::isBool($active))
        //	die(Tools::displayError());

        $result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . 'category` c
		LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = ' . $id_lang . '
		' . ($active ? 'AND `active` = 1' : '') . '
		ORDER BY `name` ASC');

        if (!$order)
            return $result;

        $categories = array();
        foreach ($result AS $row)
            $categories[$row['id_parent']][$row['id_category']]['infos'] = $row;

        return $categories;
    }

    public function categories_tree($selected = false)
    {
        if (_PS_VERSION_ >= '1.5') {
            $langId = $this->context->language->id;
        } else {
            global $cookie;
            $langId = $cookie->id_lang;
        }

        if (!empty($selected))
            $sel_array = explode(',', $selected);
        else
            $sel_array = array();

        $this->_html .= '
			<div style = "margin: 10px;">
				<table cellspacing="0" cellpadding="0" class="table" id = "radio_div">
					<tr>
						<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)"></th>
						<th>' . $this->l('ID') . '</th>
						<th style="width: 400px">' . $this->l('Name') . '</th>
					</tr>';

        $categories = Category::getCategories($langId, false);

        if (!empty($categories)) {
            $categories[0][1] = isset($categories[0][1]) ? $categories[0][1] : false;
            $this->recurseCategoryForInclude_pref(null, $categories, $categories[0][1], 1, null, $sel_array);
        }

        $this->_html .= '
				</table>
				<div class="categories_list_button" style="cursor: pointer; color: #268CCD; text-align: left; margin-top: 10px;">' . $this->l('[Hide]') . '</div>				
			</div>';
    }

    public function products_xml($page)
    {
        $this->_html .= '		
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset><legend><img src="../img/admin/prefs.gif" alt="' . $this->l('XML structure') . '" title="' . $this->l('XML structure') . '" />' . $this->l('XML of products') . '</legend>';
        $this->_html .= $this->products_xml_settings($page);
        $this->_html .= '
				<br/>		
				<input type="hidden" name="feeds_id" value="' . $page . '" />
				<center><input type="submit" name="settings_prod" value="' . $this->l('Update') . '" class="button" /></center>			
			</fieldset>
		</form>';
    }

    public function categories_xml($page)
    {
        $this->_html .= '		
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
				<legend><img src="../img/admin/prefs.gif" alt="' . $this->l('XML structure') . '" title="' . $this->l('XML structure') . '" />' . $this->l('XML of categories') . '</legend>';
        $this->_html .= $this->categories_xml_settings($page);
        $this->_html .= '
				<br/>		
				<input type="hidden" name="feeds_id" value="' . $page . '" />				
				<center><input type="submit" name="settings_cat" value="' . $this->l('Update') . '" class="button" /></center>			
			</fieldset>
		</form>';
    }

    public function print_block($block_name = false, $info, $only_checkbox = false)
    {
        if (empty($info))
            return false;

        $html =
            '<div class="table_name">' . $this->l($block_name) . '</div>
			<div class="cb"></div>
			<div class="cn_table">
				<div class="cn_top">
					<div class="cn_name">
						' . $this->l('Name') . '
					</div>
					<div class="cn_status">
						' . $this->l('Status') . '
					</div>';

        if (empty($only_checkbox))
            $html .= '
					<div class="cn_name_xml">
						' . $this->l('Name in XML') . '
					</div>';
        else
            $html .= '<div class="cb"></div>';

        $html .= '</div>';

        foreach ($info as $i) {
            $find = isset($this->tags_info[$i['name'] . '+' . $i['table'] . '+status']) ? $this->tags_info[$i['name'] . '+' . $i['table'] . '+status'] : false;
            $value = !empty($this->tags_info[$i['name'] . '+' . $i['table']]) ? $this->tags_info[$i['name'] . '+' . $i['table']] : $i['title'];

            $box_id = $i['name'] . '_' . $i['table'] . '_option';

            $html .=
                '<div class="cn_line">
				<div class="cn_name">
					' . $this->l($i['title']) . '
				</div>
				<div class="cn_status">
					<label for="' . $box_id . '">
						<input id="' . $box_id . '" type="checkbox" name="' . $i['name'] . '+' . $i['table'] . '+status"';
            $html .= $this->status($find) . '
					</label>
				</div>';

            if (empty($i['only_checkbox'])) {

                $html .= '
				<div class="cn_name_xml">
					<input type="text" name="' . $i['name'] . '+' . $i['table'] . '" value="' . $value . '" size="30"/>
				</div>';
            } else
                $html .= '<div class="cb"></div>';

            $html .= '</div>';
        }

        $html .= '</div>
		<div class="cb"></div>';

        return $html;
    }

    public function products_xml_settings($page_id = false)
    {
        $v = array();
        $b_name = array();
        $lang_array = array();

        $settings = Db::getInstance()->getRow('
			SELECT one_branch
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds
			WHERE id = "' . $page_id . '"
		');

        $disabled_branch_name = '';

        if (!empty($settings['one_branch']))
            $disabled_branch_name = 'disabled="disabled"';

        $r = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`, `table`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
			WHERE category = "' . $page_id . '"
		');

        foreach ($r as $k) {
            $v[$k['name'] . '+' . $k['table']] = isset($k['title_xml']) ? $k['title_xml'] : false;
            $v[$k['name'] . '+' . $k['table'] . '+status'] = isset($k['status']) ? $k['status'] : false;
        }

        if (!empty($v))
            $this->tags_info = $v;

        $r_b = Db::getInstance()->ExecuteS('
			SELECT `name`, `value`, `category`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_block
			WHERE category = "' . $page_id . '"
		');

        foreach ($r_b as $bl)
            $b_name[$bl['name']] = isset($bl['value']) ? $bl['value'] : false;

        $html = '
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('File name:') . '</div><div style="float: left;"><input type="text" name="file-name" value="' . $b_name['file-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('Product block name:') . '</div><div style="float: left;"><input type="text" name="cat-block-name" value="' . $b_name['cat-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('Description block name:') . '</div><div style="float: left;"><input ' . $disabled_branch_name . ' type="text" name="desc-block-name" value="' . $b_name['desc-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('Images block name:') . '</div><div style="float: left;"><input ' . $disabled_branch_name . ' type="text" name="img-block-name" value="' . $b_name['img-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('Default category block name:') . '</div><div style="float: left;"><input ' . $disabled_branch_name . ' type="text" name="def_cat-block-name" value="' . $b_name['def_cat-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 230px; font-weight: bold;">' . $this->l('Attributes block name:') . '</div><div style="float: left;"><input ' . $disabled_branch_name . ' type="text" name="attributes-block-name" value="' . $b_name['attributes-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<br/><br/>
		';

        $html .= $this->print_block('Product basic information',
            array(
                array('name' => 'product_url_blmod', 'title' => 'product_url', 'table' => 'bl_extra'),
                array('name' => 'id_product', 'title' => 'id_product', 'table' => 'product'),
                array('name' => 'id_supplier', 'title' => 'id_supplier', 'table' => 'product'),
                array('name' => 'supplier_reference', 'title' => 'supplier_reference', 'table' => 'product'),
                array('name' => 'id_manufacturer', 'title' => 'id_manufacturer', 'table' => 'product'),
                array('name' => 'name', 'title' => 'manufacturer_name', 'table' => 'manufacturer'),
                array('name' => 'location', 'title' => 'location', 'table' => 'product'),
                array('name' => 'weight', 'title' => 'weight', 'table' => 'product'),
                array('name' => 'on_sale', 'title' => 'on_sale', 'table' => 'product'),
                array('name' => 'reference', 'title' => 'reference', 'table' => 'product'),
                array('name' => 'ean13', 'title' => 'ean13', 'table' => 'product'),
                array('name' => 'active', 'title' => 'active', 'table' => 'product'),
                array('name' => 'date_add', 'title' => 'date_add', 'table' => 'product'),
                array('name' => 'date_upd', 'title' => 'date_upd', 'table' => 'product')
            )
        );

        $html .= $this->print_block('Prices, Tax',
            array(
                array('name' => 'price', 'title' => 'base price', 'table' => 'product'),
                array('name' => 'price_sale_blmod', 'title' => 'sale price', 'table' => 'bl_extra'),
                array('name' => 'price_shipping_blmod', 'title' => 'shipping price', 'table' => 'bl_extra'),
                array('name' => 'wholesale_price', 'title' => 'wholesale price', 'table' => 'product'),
                array('name' => 'ecotax', 'title' => 'ecotax', 'table' => 'product')
            )
        );

        $html .= $this->print_block('Quantity',
            array(
                array('name' => 'quantity', 'title' => 'quantity', 'table' => 'product'),
                array('name' => 'quantity_discount', 'title' => 'quantity_discount', 'table' => 'product'),
                array('name' => 'out_of_stock', 'title' => 'out_of_stock', 'table' => 'product')
            )
        );

        $html .= $this->print_block('Categories',
            array(
                array('name' => 'id_category_default', 'title' => 'id_category_default', 'table' => 'product'),
                array('name' => 'name', 'title' => 'name_category_default', 'table' => 'category_lang')
            )
        );

        //Attributes	
        $prod_id = $this->get_rand_product();
        $att_array = array();

        if (!empty($prod_id)) {
            $product_class_name = 'ProductCore';

            if (!class_exists($product_class_name, false))
                $product_class_name = 'Product';

            $product_class = new $product_class_name();

            $product_class->id = $prod_id;
            $attributes = $product_class->getAttributesGroups(Configuration::get('PS_LANG_DEFAULT'));

            if (!empty($attributes[0])) {
                foreach ($attributes[0] as $id => $val)
                    $att_array[] = array('name' => $id, 'title' => $id, 'table' => 'bl_extra_att');

                $html .= $this->print_block('Attributes', $att_array);
            }
        }

        //get images
        $img_array = array();
        $images = Db::getInstance()->ExecuteS('
			SELECT id_image_type, name FROM
			' . _DB_PREFIX_ . 'image_type
		');

        if (!empty($images)) {
            foreach ($images as $img)
                $img_array[] = array('name' => $img['name'], 'title' => $img['name'], 'table' => 'img_blmod');

            $html .= $this->print_block('Image (cover)', $img_array);
        }

        $html .= $this->print_block('Descriptions',
            array(
                array('name' => 'description', 'title' => 'description', 'table' => 'product_lang'),
                array('name' => 'description_short', 'title' => 'description_short', 'table' => 'product_lang'),
                array('name' => 'link_rewrite', 'title' => 'link_rewrite', 'table' => 'product_lang'),
                array('name' => 'meta_description', 'title' => 'meta_description', 'table' => 'product_lang'),
                array('name' => 'meta_keywords', 'title' => 'meta_keywords', 'table' => 'product_lang'),
                array('name' => 'meta_title', 'title' => 'meta_title', 'table' => 'product_lang'),
                array('name' => 'name', 'title' => 'name', 'table' => 'product_lang'),
                array('name' => 'available_now', 'title' => 'available_now', 'table' => 'product_lang'),
                array('name' => 'available_later', 'title' => 'available_later', 'table' => 'product_lang')
            )
        );

        //get languages
        $languages = Db::getInstance()->ExecuteS('
			SELECT id_lang, name FROM
			' . _DB_PREFIX_ . 'lang
		');

        if (!empty($languages)) {
            foreach ($languages as $lan)
                $lang_array[] = array('name' => $lan['id_lang'], 'title' => $lan['name'], 'table' => 'lang', 'only_checkbox' => 1);

            $html .= $this->print_block('Descriptions languages', $lang_array, 1);
        }

        return $html;
    }

    public function categories_xml_settings($page_id = false)
    {
        $b_name = array();
        $v = array();

        $settings = Db::getInstance()->getRow('
			SELECT one_branch
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds
			WHERE id = "' . $page_id . '"
		');

        $disabled_branch_name = '';

        if (!empty($settings['one_branch']))
            $disabled_branch_name = 'disabled="disabled"';

        $r = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`, `table`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
			WHERE category = "' . $page_id . '"
		');

        foreach ($r as $k) {
            $v[$k['name'] . '+' . $k['table']] = isset($k['title_xml']) ? $k['title_xml'] : false;
            $v[$k['name'] . '+' . $k['table'] . '+status'] = isset($k['status']) ? $k['status'] : false;
        }

        $this->tags_info = $v;

        $r_b = Db::getInstance()->ExecuteS('
			SELECT `name`, `value`, `category`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_block
			WHERE category = "' . $page_id . '"
		');

        foreach ($r_b as $bl)
            $b_name[$bl['name']] = isset($bl['value']) ? $bl['value'] : false;

        $html = '
			<div style="float: left; width: 190px; font-weight: bold;">' . $this->l('File name:') . '</div><div style="float: left;"><input type="text" name="file-name" value="' . $b_name['file-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 190px; font-weight: bold;">' . $this->l('Category block name:') . '</div><div style="float: left;"><input type="text" name="cat-block-name" value="' . $b_name['cat-block-name'] . '" size="30"/></div>
			<div class="cb"></div>
			<div style="float: left; width: 190px; font-weight: bold;">' . $this->l('Description block name:') . '</div><div style="float: left;"><input type="text" ' . $disabled_branch_name . ' name="desc-block-name" value="' . $b_name['desc-block-name'] . '" size="30"/></div>
			<br/><br/>
		';

        $html .= $this->print_block('Category basic information',
            array(
                array('name' => 'id_category', 'title' => 'id_category', 'table' => 'category'),
                array('name' => 'id_parent', 'title' => 'id_parent', 'table' => 'category'),
                array('name' => 'level_depth', 'title' => 'level_depth', 'table' => 'category'),
                array('name' => 'active', 'title' => 'active', 'table' => 'category'),
                array('name' => 'date_add', 'title' => 'date_add', 'table' => 'category'),
                array('name' => 'date_upd', 'title' => 'date_upd', 'table' => 'category')
            )
        );

        $html .= $this->print_block('Descriptions',
            array(
                array('name' => 'id_lang', 'title' => 'id_lang', 'table' => 'category_lang'),
                array('name' => 'name', 'title' => 'name', 'table' => 'category_lang'),
                array('name' => 'description', 'title' => 'description', 'table' => 'category_lang'),
                array('name' => 'link_rewrite', 'title' => 'link_rewrite', 'table' => 'category_lang'),
                array('name' => 'meta_title', 'title' => 'meta_title', 'table' => 'category_lang'),
                array('name' => 'meta_keywords', 'title' => 'meta_keywords', 'table' => 'category_lang'),
                array('name' => 'meta_description', 'title' => 'meta_description', 'table' => 'category_lang')
            )
        );

        //get languages
        $lang_array = array();

        $languages = Db::getInstance()->ExecuteS('
			SELECT id_lang, name FROM
			' . _DB_PREFIX_ . 'lang
		');

        if (!empty($languages)) {
            foreach ($languages as $lan)
                $lang_array[] = array('name' => $lan['id_lang'], 'title' => $lan['name'], 'table' => 'lang', 'only_checkbox' => 1);

            $html .= $this->print_block('Descriptions languages', $lang_array, 1);
        }

        return $html;
    }

    public function update_feeds_s($name = false, $status, $use_cache, $cache_time, $use_password, $password, $id, $cdata_status, $html_tags_status, $one_branch, $header_information, $footer_information, $extra_feed_row, $only_enabled, $split_feed, $split_feed_limit, $cat_list, $categories)
    {
        $cache_time = (int)$cache_time;
        $split_feed_limit = (int)$split_feed_limit;

        $query = Db::getInstance()->Execute('
			UPDATE ' . _DB_PREFIX_ . 'blmod_xml_feeds SET
			name="' . htmlspecialchars($name, ENT_QUOTES) . '", status = "' . $status . '", use_cache = "' . $use_cache . '", cache_time = "' . htmlspecialchars($cache_time, ENT_QUOTES) . '", use_password = "' . $use_password . '",
			password = "' . htmlspecialchars($password, ENT_QUOTES) . '", cdata_status = "' . $cdata_status . '", html_tags_status = "' . $html_tags_status . '", one_branch = "' . $one_branch . '",
			header_information = "' . htmlspecialchars($header_information, ENT_QUOTES) . '", footer_information = "' . htmlspecialchars($footer_information, ENT_QUOTES) . '", extra_feed_row = "' . htmlspecialchars($extra_feed_row, ENT_QUOTES) . '",
			only_enabled = "' . $only_enabled . '", split_feed = "' . $split_feed . '", split_feed_limit = "' . $split_feed_limit . '", cat_list = "' . $cat_list . '", categories = "' . $categories . '"
			WHERE id = "' . $id . '"
		');

        if (!empty($id))
            $this->delete_cache($id);

        if ($query)
            $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Save successfully') . '</div>';
        else
            $this->_html .= '<div class="warning warn"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('error') . '</div>';
    }

    public function update_fields($post, $type)
    {
        $category = Tools::getValue('feeds_id');

        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_fields WHERE category = "' . $category . '"');
        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_block WHERE category = "' . $category . '"');

        if (!empty($category))
            $this->delete_cache($category);

        if ($type == 2) {
            $post['file-name'] = !empty($post['file-name']) ? $this->on_special($post['file-name']) : 'categories';
            $post['cat-block-name'] = !empty($post['cat-block-name']) ? $this->on_special($post['cat-block-name']) : 'category';
            $post['desc-block-name'] = !empty($post['desc-block-name']) ? $this->on_special($post['desc-block-name']) : 'description';

            Db::getInstance()->Execute('
				INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_block
				(`name`, `value`, `category`)
				VALUE
				("file-name", "' . htmlspecialchars($post['file-name'], ENT_QUOTES) . '", "' . $category . '"),
				("cat-block-name", "' . htmlspecialchars($post['cat-block-name'], ENT_QUOTES) . '", "' . $category . '"),
				("desc-block-name", "' . htmlspecialchars($post['desc-block-name'], ENT_QUOTES) . '", "' . $category . '")
			');
        } elseif ($type == 1) {
            $post['file-name'] = !empty($post['file-name']) ? $this->on_special($post['file-name']) : 'products';
            $post['cat-block-name'] = !empty($post['cat-block-name']) ? $this->on_special($post['cat-block-name']) : 'product';
            $post['desc-block-name'] = !empty($post['desc-block-name']) ? $this->on_special($post['desc-block-name']) : 'description';
            $post['img-block-name'] = !empty($post['img-block-name']) ? $this->on_special($post['img-block-name']) : 'images';
            $post['def_cat-block-name'] = !empty($post['def_cat-block-name']) ? $this->on_special($post['def_cat-block-name']) : 'default_cat';
            $post['attributes-block-name'] = !empty($post['attributes-block-name']) ? $this->on_special($post['attributes-block-name']) : 'attributes';

            Db::getInstance()->Execute('
				INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_block
				(`name`, `value`, `category`)
				VALUE
				("file-name", "' . htmlspecialchars($post['file-name'], ENT_QUOTES) . '", "' . $category . '"),
				("cat-block-name", "' . htmlspecialchars($post['cat-block-name'], ENT_QUOTES) . '", "' . $category . '"),
				("desc-block-name", "' . htmlspecialchars($post['desc-block-name'], ENT_QUOTES) . '", "' . $category . '"),
				("img-block-name", "' . htmlspecialchars($post['img-block-name'], ENT_QUOTES) . '", "' . $category . '"),
				("def_cat-block-name", "' . htmlspecialchars($post['def_cat-block-name'], ENT_QUOTES) . '", "' . $category . '"),
				("attributes-block-name", "' . htmlspecialchars($post['attributes-block-name'], ENT_QUOTES) . '", "' . $category . '")
			');
        }

        $value = '';

        foreach ($post as $id => $val) {
            $name = explode('+', $id);

            if (empty($name[1]) or (!empty($name[2]) and $name[1] != 'lang'))
                continue;

            $title = isset($val) ? $this->on_special($val) : false;
            $status = isset($post[$id . '+status']) ? $post[$id . '+status'] : false;

            if ($name[1] == 'lang')
                $status = !empty($post[$id]) ? $post[$id] : false;

            $value .= '("' . $name[0] . '", "' . $status . '", "' . htmlspecialchars($title, ENT_QUOTES) . '", "' . $name[1] . '", "' . $category . '"),';
        }

        if (!empty($value)) {
            $value = trim($value, ',');

            $insert = Db::getInstance()->Execute('
				INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_fields
				(`name`, `status`, `title_xml`, `table`, `category`)
				VALUE
				' . $value . '
			');
        }

        if ($insert)
            $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Save successfully') . '</div>';
        else
            $this->_html .= '<div class="warning warn"><img src="../img/admin/warning.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('error') . '</div>';
    }

    public function delete_feed($feed_id = false)
    {
        if (empty($feed_id))
            return false;

        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_block WHERE category = "' . $feed_id . '"');
        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds WHERE id = "' . $feed_id . '"');
        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_fields WHERE category = "' . $feed_id . '"');
        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_statistics WHERE feed_id = "' . $feed_id . '"');

        $this->delete_cache($feed_id);

        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Delete successfully') . '</div>';
    }

    public function delete_cache($feed_id = false, $all_feeds = false)
    {
        if (empty($feed_id) and empty($all_feeds))
            return false;

        $where = false;

        if (!empty($feed_id))
            $where = ' WHERE feed_id = "' . $feed_id . '"';

        $cache = Db::getInstance()->ExecuteS('
			SELECT file_name
			FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache			
			' . $where
        );

        if (!empty($cache))
            foreach ($cache as $c)
                @unlink('../modules/xml_feeds/xml_files/' . $c['file_name'] . '.xml');

        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache' . $where);
    }

    public function on_special($v)
    {
        return preg_replace('/[^a-zA-Z0-9_:-]/', '_', $v);
    }

    public function get_rand_product()
    {
        $random_product = Db::getInstance()->getRow('
			SELECT id_product
			FROM ' . _DB_PREFIX_ . 'product_attribute
		');

        if (!empty($random_product['id_product']))
            return $random_product['id_product'];

        return false;
    }
}

function pr($text = false)
{
    echo '<pre>';
    print_r($text);
    echo '</pre>';
}