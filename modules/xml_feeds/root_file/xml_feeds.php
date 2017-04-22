<?php
/**
 * Xml feeds Pro
 * @author Bl Modules <blmodules@gmail.com
 * @copyright Copyright (c) 2010 - 2014, Bl Modules
 * @page http://www.blmodules.com
 */

include(dirname(__FILE__) . '/config/config.inc.php');
include_once(dirname(__FILE__) . '/init.php');
die(33);
$id = Tools::getValue('id');
$part = Tools::getValue('part');
$affiliate = Tools::getValue('affiliate');
$multistore = Tools::getValue('multistore');

if (!is_numeric($id))
    die('wrong id');

if ($affiliate == 'affiliate_name')
    $affiliate = false;

//Check affiliate name
$check_affiliate = Db::getInstance()->getRow('SELECT `affiliate_name` FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price WHERE `affiliate_name` = "' . $affiliate . '"');

if (empty($check_affiliate['affiliate_name']))
    $affiliate_cache = false;
else
    $affiliate_cache = $affiliate;

$permissions = Db::getInstance()->getRow('
	SELECT f.*, c.file_name AS file_name_n, c.last_cache_time AS last_cache_time_n
	FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds f
	LEFT JOIN ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache c ON
	(f.id = c.feed_id AND c.feed_part = "' . $part . '" AND c.affiliate_name = "' . $affiliate_cache . '")
	WHERE f.id = "' . $id . '"
');

$feed_name = 'archive_' . $id;

$permissions['use_cache'] = isset($permissions['use_cache']) ? $permissions['use_cache'] : false;
$permissions['cache_time'] = isset($permissions['cache_time']) ? $permissions['cache_time'] : false;
$permissions['last_cache_time'] = isset($permissions['last_cache_time_n']) ? $permissions['last_cache_time_n'] : '0000-00-00 00:00:00';
$permissions['use_password'] = isset($permissions['use_password']) ? $permissions['use_password'] : false;
$permissions['password'] = isset($permissions['password']) ? $permissions['password'] : false;
$permissions['status'] = isset($permissions['status']) ? $permissions['status'] : false;
$permissions['file_name'] = isset($permissions['file_name_n']) ? $permissions['file_name_n'] : false;
$permissions['html_tags_status'] = isset($permissions['html_tags_status']) ? $permissions['html_tags_status'] : false;
$permissions['one_branch'] = isset($permissions['one_branch']) ? $permissions['one_branch'] : false;
$permissions['header_information'] = isset($permissions['header_information']) ? htmlspecialchars_decode($permissions['header_information'], ENT_QUOTES) : false;
$permissions['footer_information'] = isset($permissions['footer_information']) ? htmlspecialchars_decode($permissions['footer_information'], ENT_QUOTES) : false;
$permissions['extra_feed_row'] = isset($permissions['extra_feed_row']) ? htmlspecialchars_decode($permissions['extra_feed_row'], ENT_QUOTES) : false;
$permissions['only_enabled'] = isset($permissions['only_enabled']) ? $permissions['only_enabled'] : false;
$permissions['split_feed'] = isset($permissions['split_feed']) ? $permissions['split_feed'] : false;
$permissions['split_feed_limit'] = isset($permissions['split_feed_limit']) ? $permissions['split_feed_limit'] : false;
$permissions['cat_list'] = isset($permissions['cat_list']) ? $permissions['cat_list'] : false;
$permissions['categories'] = isset($permissions['categories']) ? $permissions['categories'] : false;
$feed_type = isset($permissions['feed_type']) ? $permissions['feed_type'] : false;

if ($permissions['status'] != 1)
    die('disabled');

if ($permissions['use_password'] == 1 and !empty($permissions['password'])) {
    $pass = Tools::getValue('password');

    if ($permissions['password'] != $pass)
        die('wrong password');
}

insert_statistics($id, $affiliate);

$now = date('Y-m-d h:i:s');
$cache_period = date('Y-m-d h:i:s', strtotime($permissions['last_cache_time'] . '+ ' . $permissions['cache_time'] . ' minutes'));

if ($permissions['use_cache']) {
    $file_url = 'modules/xml_feeds/xml_files/' . $permissions['file_name'] . '.xml';

    if ($now < $cache_period) {
        if (!empty($permissions['file_name']))
            $xml = Tools::file_get_contents($file_url, false, false, 20);

        if (!empty($xml)) {
            header("Content-type: text/xml;charset:UTF-8");

            $download = Tools::getValue('download');

            if (!empty($download))
                header("Content-Disposition:attachment;filename=" . $feed_name . "_feed.xml");

            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo $xml;
            die;
        }
    } else {
        //Delete old cache
        Db::getInstance()->getRow('DELETE FROM ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache WHERE `feed_id` = "' . $id . '" AND `affiliate_name` = "' . $affiliate_cache . '"');
        @unlink($file_url);
    }
}

if (!empty($permissions['cdata_status'])) {
    $pref_s = '<![CDATA[';
    $pref_e = ']]>';
} else {
    $pref_s = '';
    $pref_e = '';
}

$multistoreArray = array();
$multistoreString = false;

if (!empty($multistore)) {

    if ($multistore == 'auto') {
        $multistoreString = Context::getContext()->shop->id;
    } else {
        $multistoreArrayCheck = explode(',', $multistore);

        foreach ($multistoreArrayCheck as $m) {
            $mId = (int)$m;

            if (empty($mId)) {
                continue;
            }

            $multistoreArray[] = $mId;
        }

        $multistoreString = implode(',', $multistoreArray);
    }
}

function category($id, $pref_s, $pref_e, $html_tags_status, $extra_feed_row = false, $one_branch, $only_enabled, $multistoreString)
{
    $xml_name = array();
    $xml_name_l = array();

    $block_n = Db::getInstance()->ExecuteS('
		SELECT `name`, `value`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_block
		WHERE category = "' . $id . '"
	');

    $block_name = array();

    foreach ($block_n as $bn) {
        $block_name[$bn['name']] = $bn['value'];
    }

    $r = Db::getInstance()->ExecuteS('
		SELECT `name`, `status`, `title_xml`, `table`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` != "lang" AND `table` != "category_lang" AND status = "1"
		ORDER BY `table` ASC
	');

    $field = '';

    if (!empty($r)) {
        foreach ($r as $f) {
            $field .= ' `' . _DB_PREFIX_ . $f['table'] . '`.`' . $f['name'] . '` AS ' . $f['table'] . '_' . $f['name'] . ' ,';
            $xml_name[$f['table'] . '_' . $f['name']] = $f['title_xml'];
        }

        if (empty($field))
            exit;

        $field = ',' . trim($field, ',');
    }

    $where_only_actyve = '';

    if (!empty($only_enabled))
        $where_only_actyve = 'WHERE ' . _DB_PREFIX_ . 'category.active = "1"';

    if (!empty($multistoreString)) {
        if (empty($where_only_actyve))
            $where_only_actyve = 'WHERE ' . _DB_PREFIX_ . 'category.id_shop_default IN (' . $multistoreString . ')';
        else
            $where_only_actyve .= ' AND ' . _DB_PREFIX_ . 'category.id_shop_default IN (' . $multistoreString . ')';
    }

    $sql = '
		SELECT DISTINCT(' . _DB_PREFIX_ . 'category.id_category) AS cat_id ' . $field . '
		FROM ' . _DB_PREFIX_ . 'category
		LEFT JOIN ' . _DB_PREFIX_ . 'category_group ON
		' . _DB_PREFIX_ . 'category_group.id_category = ' . _DB_PREFIX_ . 'category.id_category ' .
        $where_only_actyve;

    $xml_d = Db::getInstance()->ExecuteS($sql);

    //Language
    $l = Db::getInstance()->ExecuteS('
		SELECT `name`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` = "lang"
	');

    $xml_lf = array();

    if (!empty($l)) {
        $l_where = '';
        $count_lang = count($l);

        foreach ($l as $ll)
            $l_where .= 'OR ' . _DB_PREFIX_ . 'category_lang.id_lang=' . $ll['name'] . ' ';

        $l_where = trim($l_where, 'OR');

        $rl = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
			WHERE category = "' . $id . '" AND `table` = "category_lang" and status=1
		');

        $field = '';

        if (!empty($rl)) {
            foreach ($rl as $fl) {
                $field .= ' `' . _DB_PREFIX_ . 'category_lang`.`' . $fl['name'] . '`,';
                $xml_name_l[$fl['name']] = $fl['title_xml'];
            }

            $field = ',' . trim($field, ',');
        }

        $xml_l = Db::getInstance()->ExecuteS('
			SELECT ' . _DB_PREFIX_ . 'category_lang.id_category, ' . _DB_PREFIX_ . 'lang.iso_code as blmodxml_l ' . $field . '
			FROM ' . _DB_PREFIX_ . 'category_lang
			LEFT JOIN ' . _DB_PREFIX_ . 'lang ON
			' . _DB_PREFIX_ . 'lang.id_lang = ' . _DB_PREFIX_ . 'category_lang.id_lang
			WHERE ' . $l_where . '
			ORDER BY ' . _DB_PREFIX_ . 'category_lang.id_category ASC
		');

        $all_l_iso = array();

        foreach ($xml_l as $xll) {
            $id_cat = $xll['id_category'];
            $l_iso = $xll['blmodxml_l'];
            $all_l_iso[] = $l_iso;

            $lang_prefix = '-' . $l_iso;

            if ($count_lang < 2)
                $lang_prefix = '';

            if (empty($one_branch))
                $xml_lf[$id_cat . $l_iso] = '<' . $block_name['desc-block-name'] . $lang_prefix . '>';
            else
                $xml_lf[$id_cat . $l_iso] = '';

            foreach ($xll as $idl => $vall) {
                if ($idl == 'id_category' or $idl == 'blmodxml_l')
                    continue;

                $vall = isset($vall) ? $vall : false;

                if ($html_tags_status)
                    $vall = strip_tags($vall);

                $xml_lf[$id_cat . $l_iso] .= '<' . $xml_name_l[$idl] . $lang_prefix . '>' . $pref_s . htmlspecialchars($vall) . $pref_e . '</' . $xml_name_l[$idl] . $lang_prefix . '>';
            }

            if (empty($one_branch))
                $xml_lf[$id_cat . $l_iso] .= '</' . $block_name['desc-block-name'] . $lang_prefix . '>';
        }

        $all_l_iso = array_unique($all_l_iso);
    }

    $xml = '<' . $block_name['file-name'] . '>';
    foreach ($xml_d as $xdd) {
        $xml .= '<' . $block_name['cat-block-name'] . '>';

        foreach ($xdd as $id => $val) {
            if ($id == 'cat_id')
                continue;

            $val = isset($val) ? $val : false;
            $xml .= '<' . $xml_name[$id] . '>' . $pref_s . $val . $pref_e . '</' . $xml_name[$id] . '>';
        }

        $id_cat = $xdd['cat_id'];

        if (!empty($all_l_iso)) {
            foreach ($all_l_iso as $iso) {
                $xml_lf[$id_cat . $iso] = isset($xml_lf[$id_cat . $iso]) ? $xml_lf[$id_cat . $iso] : false;
                $xml .= $xml_lf[$id_cat . $iso];
            }
        }

        $xml .= '</' . $block_name['cat-block-name'] . '>';
    }
    $xml .= '</' . $block_name['file-name'] . '>';

    return $xml;
}

function product($id, $pref_s, $pref_e, $html_tags_status, $extra_feed_row, $one_branch, $only_enabled, $split_feed_limit, $part, $categories, $cat_list, $multistoreString)
{
    $affiliate = Tools::getValue('affiliate');
    $block_name = array();
    $xml_name = array();
    $xml_name_l = array();
    $all_l_iso = array();
    $xml_cat_name = array();
    $xml_lf = array();
    $cover_i = array();
    $image_info = array();

    $default_lang = Configuration::get('PS_LANG_DEFAULT');

    $block_n = Db::getInstance()->ExecuteS('
		SELECT `name`, `value`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_block
		WHERE category = "' . $id . '"
	');

    foreach ($block_n as $bn)
        $block_name[$bn['name']] = $bn['value'];

    $r = Db::getInstance()->ExecuteS('
		SELECT `name`, `status`, `title_xml`, `table`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` != "lang" AND `table` != "img_blmod" AND `table` != "category_lang" AND `table` != "product_lang" AND `table` != "bl_extra" AND `table` != "bl_extra_att" AND status = "1"
		ORDER BY `table` ASC
	');

    $field = '';

    foreach ($r as $f) {
        $field .= ' `' . _DB_PREFIX_ . $f['table'] . '`.`' . $f['name'] . '` AS ' . $f['table'] . '_' . $f['name'] . ' ,';
        $xml_name[$f['table'] . '_' . $f['name']] = $f['title_xml'];
    }

    if (empty($field))
        exit;

    $field = ',' . trim($field, ',');

    $where_only_actyve = '';
    $order = '';
    $limit = '';

    if (!empty($only_enabled))
        $where_only_actyve = 'WHERE ' . _DB_PREFIX_ . 'product.active = "1"';

    if (!empty($split_feed_limit) and !empty($part)) {
        $order = ' ORDER BY ' . _DB_PREFIX_ . 'product.id_product ASC';
        $limit = ' LIMIT ' . ($split_feed_limit * --$part) . ',' . $split_feed_limit;
    }

    $category_table = false;

    if (!empty($categories) and !empty($cat_list)) {
        $category_table = '
		LEFT JOIN ' . _DB_PREFIX_ . 'category_product ON
		' . _DB_PREFIX_ . 'category_product.id_product = ' . _DB_PREFIX_ . 'product.id_product ';

        if (!empty($where_only_actyve))
            $where_only_actyve .= ' AND ';
        else
            $where_only_actyve .= ' WHERE ';

        $where_only_actyve .= _DB_PREFIX_ . 'category_product.id_category IN (' . $cat_list . ')';
    }

    if (!empty($multistoreString)) {
        if (!empty($where_only_actyve))
            $where_only_actyve .= ' AND ';
        else
            $where_only_actyve .= ' WHERE ';

        $where_only_actyve .= _DB_PREFIX_ . 'product.id_shop_default IN (' . $multistoreString . ')';
    }

    $sql = '
		SELECT DISTINCT(' . _DB_PREFIX_ . 'product.id_product) AS pro_id, ' . _DB_PREFIX_ . 'product.id_category_default AS blmod_cat_id, ' . _DB_PREFIX_ . 'product.price AS blmod_price' . $field . '
		FROM ' . _DB_PREFIX_ . 'product
		LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer ON
		' . _DB_PREFIX_ . 'manufacturer.id_manufacturer = ' . _DB_PREFIX_ . 'product.id_manufacturer
		' . $category_table . $where_only_actyve . $order . $limit;
    ;

    $xml_d = Db::getInstance()->ExecuteS($sql);

    //Language
    $l = Db::getInstance()->ExecuteS('
		SELECT `name`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` = "lang"
	');

    if (!empty($l)) {
        $count_lang = count($l);

        //Default category name
        $cat_name_status = Db::getInstance()->getRow('
			SELECT `name`, `status`, `title_xml`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
			WHERE category = "' . $id . '" AND `table` = "category_lang"
		');

        if (!empty($cat_name_status['status']) and !empty($cat_name_status['title_xml'])) {
            $l_where_cat = '';

            foreach ($l as $ll)
                $l_where_cat .= 'OR c.`id_lang`=' . $ll['name'] . ' ';

            $l_where_cat = trim($l_where_cat, 'OR');

            $cat_name = Db::getInstance()->ExecuteS('
				SELECT c.`id_category`, c.`name`, c.`id_lang`, l.iso_code
				FROM ' . _DB_PREFIX_ . 'category_lang c
				LEFT JOIN ' . _DB_PREFIX_ . 'lang l ON
				l.id_lang = c.id_lang
				WHERE ' . $l_where_cat . '
				ORDER BY c.`id_category`
			');

            if (!empty($cat_name)) {
                $cat_old = false;

                if ($count_lang < 2) {
                    foreach ($cat_name as $cn) {
                        if ($cat_old == $cn['id_category'])
                            $xml_cat_name[$cn['id_category']] .= '<' . $cat_name_status['title_xml'] . '>';
                        else
                            $xml_cat_name[$cn['id_category']] = '<' . $cat_name_status['title_xml'] . '>';

                        $xml_cat_name[$cn['id_category']] .= $pref_s . $cn['name'] . $pref_e;
                        $xml_cat_name[$cn['id_category']] .= '</' . $cat_name_status['title_xml'] . '>';

                        $cat_old = $cn['id_category'];
                    }
                } else {
                    foreach ($cat_name as $cn) {
                        if ($cat_old == $cn['id_category'])
                            $xml_cat_name[$cn['id_category']] .= '<' . $cat_name_status['title_xml'] . '-' . $cn['iso_code'] . '>';
                        else
                            $xml_cat_name[$cn['id_category']] = '<' . $cat_name_status['title_xml'] . '-' . $cn['iso_code'] . '>';

                        $xml_cat_name[$cn['id_category']] .= $pref_s . $cn['name'] . $pref_e;
                        $xml_cat_name[$cn['id_category']] .= '</' . $cat_name_status['title_xml'] . '-' . $cn['iso_code'] . '>';

                        $cat_old = $cn['id_category'];
                    }
                }
            }
        } else
            $xml_cat_name = array();

        //Description
        $l_where = '';

        foreach ($l as $ll)
            $l_where .= 'OR ' . _DB_PREFIX_ . 'product_lang.id_lang=' . $ll['name'] . ' ';

        $l_where = trim($l_where, 'OR');

        $rl = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`
			FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
			WHERE category = "' . $id . '" AND `table` = "product_lang" and status=1
		');

        $field = '';

        foreach ($rl as $fl) {
            $field .= ' `' . _DB_PREFIX_ . 'product_lang`.`' . $fl['name'] . '`,';
            $xml_name_l[$fl['name']] = $fl['title_xml'];
        }

        if (!empty($field))
            $field = ',' . trim($field, ',');

        $xml_l = Db::getInstance()->ExecuteS('
			SELECT ' . _DB_PREFIX_ . 'product_lang.id_product, ' . _DB_PREFIX_ . 'lang.iso_code as blmodxml_l ' . $field . '
			FROM ' . _DB_PREFIX_ . 'product_lang
			LEFT JOIN ' . _DB_PREFIX_ . 'lang ON
			' . _DB_PREFIX_ . 'lang.id_lang = ' . _DB_PREFIX_ . 'product_lang.id_lang
			WHERE ' . $l_where . '
			ORDER BY ' . _DB_PREFIX_ . 'product_lang.id_product ASC
		');

        if (!empty($xml_l) and !empty($field)) {
            foreach ($xml_l as $xll) {
                $id_cat = $xll['id_product'];
                $l_iso = $xll['blmodxml_l'];
                $all_l_iso[] = $l_iso;
                $lang_prefix = '-' . $l_iso;

                if ($count_lang < 2)
                    $lang_prefix = '';

                if (empty($one_branch))
                    $xml_lf[$id_cat . $l_iso] = '<' . $block_name['desc-block-name'] . $lang_prefix . '>';
                else
                    $xml_lf[$id_cat . $l_iso] = '';

                foreach ($xll as $idl => $vall) {
                    if ($idl == 'id_product' or $idl == 'blmodxml_l')
                        continue;

                    $vall = isset($vall) ? $vall : false;

                    if ($html_tags_status)
                        $vall = strip_tags($vall);

                    $xml_lf[$id_cat . $l_iso] .= '<' . $xml_name_l[$idl] . $lang_prefix . '>' . $pref_s . htmlspecialchars($vall) . $pref_e . '</' . $xml_name_l[$idl] . $lang_prefix . '>';
                }

                if (empty($one_branch))
                    $xml_lf[$id_cat . $l_iso] .= '</' . $block_name['desc-block-name'] . $lang_prefix . '>';
            }

            $all_l_iso = array_unique($all_l_iso);
        }
    }

    //Images
    if (_PS_VERSION_ < '1.5') {
        include_once(dirname(__FILE__) . '/classes/Image.php');

        $use_ps_images_class = false;

        $image_class_name = 'ImageCore';

        if (!class_exists($image_class_name, false))
            $image_class_name = 'Image';

        $img_class = new $image_class_name();

        if (method_exists($img_class, 'getExistingImgPath'))
            $use_ps_images_class = true;
    } else
        $use_ps_images_class = true;

    if (_PS_VERSION_ > '1.5.3') {
        $image_class_name = 'Image';
    }

    $img_name_extra = false;

    if (_PS_VERSION_ >= '1.5.1' and _PS_VERSION_ < '1.3')
        $img_name_extra = '_default';

    $img = Db::getInstance()->ExecuteS('
		SELECT `name`, `title_xml`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` = "img_blmod" AND status = "1"
	');

    $img_cover = Db::getInstance()->ExecuteS('
		SELECT `id_image`, `id_product`
		FROM ' . _DB_PREFIX_ . 'image
		WHERE cover = "1"
	');

    //Get extra field
    $extra_field = Db::getInstance()->ExecuteS('
		SELECT `name`, `title_xml`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` = "bl_extra" AND status = "1"
	');

    include_once(dirname(__FILE__) . '/classes/Link.php');
    $link_class = new Link;

    $product_class_name = 'ProductCore';

    if (!class_exists($product_class_name, false))
        $product_class_name = 'Product';

    $product_class = new $product_class_name();

    foreach ($img_cover as $c)
        $cover_i[$c['id_product']] = $c['id_image'];

    $base_dir_img = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/p/';

    $xml = '<' . $block_name['file-name'] . '>';
    $xml .= $extra_feed_row;

    //Get attributes
    $extra_attributes = Db::getInstance()->ExecuteS('
		SELECT `name`, `title_xml`
		FROM ' . _DB_PREFIX_ . 'blmod_xml_fields
		WHERE category = "' . $id . '" AND `table` = "bl_extra_att" AND status = "1"
	');

    $id_lang = (int)(Configuration::get('PS_LANG_DEFAULT'));
    $url_type = Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://';

    foreach ($xml_d as $xdd) {
        $product_class = new $product_class_name($xdd['pro_id'], false, $id_lang);

        $xml .= '<' . $block_name['cat-block-name'] . '>';

        foreach ($xdd as $id => $val) {
            if ($id == 'pro_id' or $id == 'blmod_cat_id' or $id == 'blmod_price' or $id == 'bl_extra_att')
                continue;

            if ($id == 'product_quantity' and _PS_VERSION_ > '1.5')
                $val = $product_class->getQuantity($xdd['pro_id']);

            $val = isset($val) ? $val : false;
            $xml .= '<' . $xml_name[$id] . '>' . $pref_s . $val . $pref_e . '</' . $xml_name[$id] . '>';
        }

        $id_cat = $xdd['pro_id'];
        $def_cat = isset($xdd['blmod_cat_id']) ? $xdd['blmod_cat_id'] : false;


        if (!empty($xml_lf)) {
            if (empty($one_branch))
                $xml .= '<' . $block_name['desc-block-name'] . '>';

            foreach ($all_l_iso as $iso) {
                $xml_lf[$id_cat . $iso] = isset($xml_lf[$id_cat . $iso]) ? $xml_lf[$id_cat . $iso] : false;
                $xml .= $xml_lf[$id_cat . $iso];
            }

            if (empty($one_branch))
                $xml .= '</' . $block_name['desc-block-name'] . '>';
        }

        if (!empty($img) and !empty($cover_i[$xdd['pro_id']])) {
            if (empty($one_branch))
                $xml .= '<' . $block_name['img-block-name'] . '>';

            if ($use_ps_images_class) {
                foreach ($img AS $i) {
                    $image_info['id_image'] = isset($cover_i[$xdd['pro_id']]) ? $cover_i[$xdd['pro_id']] : false;
                    $image_info['id_product'] = $xdd['pro_id'];

                    $link = new Link();
                    $img_dir_server = $link->getImageLink($product_class->link_rewrite, $image_info['id_product'] . '-' . $image_info['id_image'], $i['name'] . $img_name_extra);

                    if (!empty($img_dir_server{0}) and $img_dir_server{0} != '/' and $img_dir_server{0} != '\\')
                        $img_dir_server = $url_type . $img_dir_server;

                    $img_class = new $image_class_name($cover_i[$xdd['pro_id']]);
                    $img_class->id = $cover_i[$xdd['pro_id']];
                    $img_dir_file = _PS_PROD_IMG_DIR_ . $img_class->getExistingImgPath() . '-' . $i['name'] . '.jpg';

                    if (file_exists($img_dir_file))
                        $xml .= '<' . $i['title_xml'] . '>' . $pref_s . $img_dir_server . $pref_e . '</' . $i['title_xml'] . '>';
                }
            } else {
                foreach ($img AS $i) {
                    $img_dir_file = $xdd['pro_id'] . '-' . $cover_i[$xdd['pro_id']] . '-' . $i['name'] . '.jpg';

                    if (file_exists('img/p/' . $img_dir_file)) {
                        $img_dir = $base_dir_img . $img_dir_file;
                        $xml .= '<' . $i['title_xml'] . '>' . $pref_s . $img_dir . $pref_e . '</' . $i['title_xml'] . '>';
                    }
                }
            }

            if (empty($one_branch))
                $xml .= '</' . $block_name['img-block-name'] . '>';
        }

        if (!empty($xml_cat_name)) {
            if (empty($one_branch))
                $xml .= '<' . $block_name['def_cat-block-name'] . '>';

            $xml .= isset($xml_cat_name[$def_cat]) ? $xml_cat_name[$def_cat] : false;

            if (empty($one_branch))
                $xml .= '</' . $block_name['def_cat-block-name'] . '>';
        }

        if (!empty($extra_field)) {
            foreach ($extra_field as $b_e) {
                $xml .= '<' . $b_e['title_xml'] . '>' . $pref_s;

                if ($b_e['name'] == 'price_shipping_blmod') {
                    $xml .= $product_class->getPriceStatic($xdd['pro_id'], false, null, 2);
                } elseif ($b_e['name'] == 'price_sale_blmod') {
                    $xml .= $product_class->getPriceStatic($xdd['pro_id'], true, null, 2);
                } elseif ($b_e['name'] == 'product_url_blmod') {
                    $product_info = new Product($xdd['pro_id'], null, $default_lang);

                    if (!empty($product_info->id))
                        $xml .= $link_class->getProductLink($product_info);
                }

                $xml .= $pref_e . '</' . $b_e['title_xml'] . '>';
            }
        }

        if (!empty($extra_attributes)) {
            $a_groups = $product_class->getAttributesGroups($default_lang);

            if (!empty($a_groups)) {
                if (empty($one_branch))
                    $xml .= '<' . $block_name['attributes-block-name'] . '>';

                $nr = 0;

                foreach ($a_groups as $ag) {
                    $nr++;

                    if (empty($one_branch))
                        $xml .= '<' . $block_name['attributes-block-name'] . '-' . $nr . '>';

                    foreach ($extra_attributes as $a)
                        $xml .= '<' . $a['title_xml'] . '>' . $pref_s . $ag[$a['name']] . $pref_e . '</' . $a['title_xml'] . '>';

                    if (empty($one_branch))
                        $xml .= '</' . $block_name['attributes-block-name'] . '-' . $nr . '>';
                }

                if (empty($one_branch))
                    $xml .= '</' . $block_name['attributes-block-name'] . '>';
            }
        }

        //Affiliate price
        if (!empty($affiliate)) {
            $affiliate_prices = Db::getInstance()->ExecuteS('
				SELECT `affiliate_name`, `affiliate_formula`, `xml_name`
				FROM ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
				WHERE `affiliate_name` = "' . $affiliate . '"
				ORDER BY affiliate_name ASC			
			');
        }

        if (!empty($affiliate_prices)) {
            foreach ($affiliate_prices as $a_price)
                $xml .= '<' . $a_price['xml_name'] . '>' . $pref_s . calculate_affiliate_prices($product_class->getPriceStatic($xdd['pro_id'], true, null, 2), $a_price['affiliate_formula']) . $pref_e . '</' . $a_price['xml_name'] . '>';
        }

        $xml .= '</' . $block_name['cat-block-name'] . '>';
    }

    $xml .= '</' . $block_name['file-name'] . '>';

    return $xml;
}

function calculate_affiliate_prices($price = false, $formula = false)
{
    if (empty($price))
        return '0.00';

    if (empty($formula))
        return $price;

    $formula = str_replace('price', $price, $formula);

    $new_price = create_function(false, 'return ' . $formula . ';');

    return number_format($new_price(), 2, '.', '');
}

function create_split_xml_product($only_enabled = false, $limit = 5000, $page = 1, $use_password = false, $password = false, $affiliate = false, $multistoreString = false)
{
    $where_only_actyve = '';

    if (!empty($only_enabled))
        $where_only_actyve = 'WHERE ' . _DB_PREFIX_ . 'product.active = "1"';

    $sql = '
		SELECT COUNT(' . _DB_PREFIX_ . 'product.id_product) AS c
		FROM ' . _DB_PREFIX_ . 'product
		' . $where_only_actyve;

    $product_total = Db::getInstance()->getRow($sql);

    if ($product_total['c'] > $limit)
        $parts = ceil($product_total['c'] / $limit);
    else
        $parts = 1;

    if (!empty($use_password) and !empty($password))
        $pass_in_link = '&password=' . $password;
    else
        $pass_in_link = '';

    if (!empty($affiliate))
        $pass_in_link .= '&affiliate=' . $affiliate;

    $link = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'xml_feeds.php?id=' . $page . $pass_in_link . '&part=';

    $xml = '<feeds>';
    $xml .= '<feeds_total><![CDATA[' . $parts . ']]></feeds_total>';

    for ($i = 1; $i <= $parts; $i++)
        $xml .= '<feed_' . $i . '><![CDATA[' . $link . $i . ']]></feed_' . $i . '>';

    $xml .= '</feeds>';

    return $xml;
}

$xml = '';

if ($feed_type == 1) {
    if (empty($part) and !empty($permissions['split_feed']) and !empty($permissions['split_feed_limit']))
        $xml = create_split_xml_product($permissions['only_enabled'], $permissions['split_feed_limit'], $id, $permissions['use_password'], $permissions['password'], $affiliate, $multistoreString);
    else
        $xml = product($id, $pref_s, $pref_e, $permissions['html_tags_status'], $permissions['extra_feed_row'], $permissions['one_branch'], $permissions['only_enabled'], $permissions['split_feed_limit'], $part, $permissions['categories'], $permissions['cat_list'], $multistoreString);
} elseif ($feed_type == 2)
    $xml = category($id, $pref_s, $pref_e, $permissions['html_tags_status'], $permissions['extra_feed_row'], $permissions['one_branch'], $permissions['only_enabled'], $multistoreString);

$xml = $permissions['header_information'] . $xml . $permissions['footer_information'];

if ($permissions['use_cache']) {
    if ($now > $cache_period) {
        if (empty($check_affiliate['affiliate_name']))
            $affiliate = false;

        $create_name = '';

        if (empty($permissions['file_name'])) {
            $permissions['file_name'] = md5(md5(rand(99999, 99999999)));
            $create_name = 'file_name="' . $permissions['file_name'] . '", ';
        }

        $affiliate_name = false;

        if (!empty($affiliate))
            $affiliate_name = '_' . $affiliate;

        $file_url = 'modules/xml_feeds/xml_files/' . $permissions['file_name'] . $affiliate_name . '.xml';
        $fxml = fopen($file_url, 'w');
        fwrite($fxml, $xml);
        fclose($fxml);

        if (file_exists($file_url)) {
            Db::getInstance()->Execute('
				INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache
				(`feed_id`, `feed_part`, `file_name`, `last_cache_time`, `affiliate_name`)
				VALUES
				("' . $id . '", "' . $part . '", "' . $permissions['file_name'] . $affiliate_name . '", "' . $now . '", "' . $affiliate . '")
			');
        }
    }
}

function insert_statistics($feed_id = false, $affiliate = false)
{
    Db::getInstance()->Execute('
		INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_statistics
		(`feed_id`, `affiliate_name`, `date`, `ip_address`)
		VALUES
		("' . $feed_id . '", "' . $affiliate . '", "' . date('Y-m-d H:i:s') . '", "' . get_ip() . '")
	');

    Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'blmod_xml_feeds SET total_views = total_views + 1 WHERE id = "' . $feed_id . '"');
}

function get_ip()
{
    $ip = false;

    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; else
        $ip = $_SERVER['REMOTE_ADDR'];

    return $ip;
}

function pr($text = false)
{
    echo '<pre>';
    print_r($text);
    echo '</pre>';
}

header("Content-type: text/xml;charset:UTF-8");

if (!empty($_GET['download']))
    header("Content-Disposition:attachment;filename=" . $feed_name . "_feed.xml");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo $xml;
die;
