<?php
/**
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *         DISCLAIMER   *
 * *************************************** */
 /* Do not edit or add to this file if you wish to upgrade Prestashop to newer
 * versions in the future.
 * ****************************************************
 *
 *  @author     BEST-KIT.COM (contact@best-kit.com)
 *  @copyright  http://best-kit.com
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'bestkit_customblocks/classes/BestkitCustomBlocks.php');

class bestkit_customblocks extends Module
{
    public function __construct()
    {
        $this->name = 'bestkit_customblocks';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'best-kit.com';
        $this->need_instance = 0;
        $this->module_key = '595f8afcfc9d510f5fdef3bf2be9002e';

        parent::__construct();

        $this->displayName = $this->l('BestKit Custom Blocks');
        $this->description = $this->l('BestKit Custom Blocks');
    }

	public static function getHooks()
	{
		$_hooks = Hook::getHooks();
		foreach ($_hooks as $key => $_hook) {
			if (Tools::substr($_hook['name'], 0, 6) == 'action') {
				unset($_hooks[$key]);
			}
		}
		
		return $_hooks;
	}

    public function install()
    {
        $sql = array();
        $languages = Language::getLanguages();
        $this->_path = __PS_BASE_URI__.'modules/'.$this->name.'/';
        $this->context->smarty->assign('module_path', $this->_path);

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks` (
              `id_bestkit_customblocks` int(10) unsigned NOT NULL auto_increment,
              `status` int(1) NOT NULL default "1",
              `use_in_hooks` int(1) NOT NULL default "1",
              `block_identifier` varchar(255) NOT NULL,
              `hook_ids` text,
              `position` int(10) NOT NULL default "0",
              `date_add` datetime NOT NULL,
              `date_upd` datetime NOT NULL,
              PRIMARY KEY  (`id_bestkit_customblocks`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks_shop` (
              `id_bestkit_customblocks` int(10) unsigned NOT NULL auto_increment,
              `id_shop` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id_bestkit_customblocks`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks_lang` (
              `id_bestkit_customblocks` int(10) unsigned NOT NULL,
              `id_lang` int(10) unsigned NOT NULL,
              `title` varchar(255) NOT NULL,
              `content` text,
              PRIMARY KEY (`id_bestkit_customblocks`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            "INSERT INTO `" . _DB_PREFIX_ . "bestkit_customblocks` VALUES 
			('1','1','1','homepage-banner','','0','2014-12-27 01:35:05','2014-12-27 01:58:10'),	
			('2','1','1','footer-features-block','','0','2014-12-27 01:43:14','2015-02-20 13:14:24')";
        foreach ($languages as $language) {
		$sql[] = 
			"INSERT INTO `" . _DB_PREFIX_ . "bestkit_customblocks_lang` VALUES 			
			('1','".(int)$language['id_lang']."','Homepage banner','" . pSQL($this->display($this->name, 'customblock1.tpl'), true) . "'),
			('2','".(int)$language['id_lang']."','Footer Features Block','" . pSQL($this->display($this->name, 'customblock2.tpl'), true) . "')";
		}

        foreach ($sql as $_sql) {
            Db::getInstance()->Execute($_sql);
        }

        $new_tab = new Tab();
        $new_tab->class_name = 'AdminCustomBlocks';
        $new_tab->id_parent = Tab::getCurrentParentId();
        $new_tab->module = $this->name;
        foreach ($languages as $language) {
            $new_tab->name[$language['id_lang']] = 'BestKit Custom Blocks';
        }

        $new_tab->add();

        $install = parent::install();
        foreach (self::getHooks() as $hook) {
	        $this->registerHook($hook['name']);
        }

        return $install;
    }

    public function uninstall()
    {
        $sql = array();

        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks_shop`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_customblocks_lang`';

        foreach ($sql as $_sql) {
            Db::getInstance()->Execute($_sql);
        }

        $idTab = Tab::getIdFromClassName('AdminCustomBlocks');
        if ($idTab) {
            $tab = new Tab($idTab);
            $tab->delete();
        }

        foreach (self::getHooks() as $hook) {
	        $this->unregisterHook($hook['name']);
        }

        return parent::uninstall();
    }

	public function __call($function, $args)
	{
		$html = '';
		$hookName = str_replace('hook', '', $function);
		$blocks = BestkitCustomBlocks::getBlocksByHookName($hookName);
		foreach ($blocks as $block) {
			$html .= self::getBlockContent($block->block_identifier);
		}

		return $html;
	}

	//{Module::getInstanceByName('bestkit_customblocks')->getBlockContent('block_identifier')}
	public static function getBlockContent($block_identifier)
	{
		return self::getBlockObject($block_identifier)->content;
	}

	//{Module::getInstanceByName('bestkit_customblocks')->getBlockObject('block_identifier')->title}
	public static function getBlockObject($block_identifier)
	{
		return BestkitCustomBlocks::getBlockObject($block_identifier);
	}
}
