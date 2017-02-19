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

class BestkitCustomBlocks extends ObjectModel {

    public $id;

    /** @var integer block ID */
    public $id_bestkit_customblocks;

    /** @var string Title */
    public $title;

    /** @var string Identifier */
    public $block_identifier;

    /** @var boolean Status for display */
    public $status = 1;
    
    public $use_in_hooks = 1;

    public $hook_ids;
    
    public $position;

    /** @var string Content */
    public $content;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $definition = array(
        'table' => 'bestkit_customblocks',
        'primary' => 'id_bestkit_customblocks',
        'multilang' => TRUE,
        'fields' => array(
            'block_identifier' =>     	array('type' => self::TYPE_STRING, 'validate' => 'isModuleName', 'required' => TRUE, 'size' => 50),
            'status' =>             	array('type' => self::TYPE_INT),
            'use_in_hooks' =>           array('type' => self::TYPE_INT),
            'position' =>           	array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' =>             	array('type' => self::TYPE_DATE),
            'date_upd' =>             	array('type' => self::TYPE_DATE),
            'hook_ids' => 				array('type' => self::TYPE_STRING),

            // Lang fields
            'title' =>                 	array('type' => self::TYPE_STRING, 'lang' => TRUE, 'validate' => 'isCatalogName', 'required' => TRUE, 'size' => 128),
            'content' =>             	array('type' => self::TYPE_HTML, 'lang' => TRUE, 'validate' => 'isString', 'size' => 3999999999999, 'required' => TRUE),
        ),
    );

	public function useDataAsArray($field, array $data = array())
	{
		if (empty($data)) {
			return explode(',', $this->$field);
		}

		$this->$field = implode(',', $data);
	}

    public static function getBlockByIdentifier($block_identifier)
    {
        $sql = '
        SELECT `id_bestkit_customblocks`
        FROM `' . _DB_PREFIX_ . 'bestkit_customblocks`
        WHERE `block_identifier` = "' . pSQL($block_identifier) . '";';

        $block_id = (int)Db::getInstance()->getValue($sql);
        $_block = new self((int)$block_id);

        if ($_block->id) {
            return $_block;
        }

        return FALSE;
    }

	public static function getBlocksByHookName($hookName)
	{
		$hooks = array();
		if ($hookName) {
			$hooks = new Collection(__CLASS__);
			$hooks->where('use_in_hooks', '=', 1);
			$hooks->where('status', '=', 1);
			$hooks->where('hook_ids', 'like', '%' . pSQL($hookName) . '%');
			$hooks->orderBy('position');
		}

		return $hooks;
	}

    public static function getBlockObject($block_identifier)
    {
        if (Module::isEnabled('bestkit_customblocks')) {
	        $sql = '
	        SELECT `id_bestkit_customblocks`
	        FROM `' . _DB_PREFIX_ . 'bestkit_customblocks`
	        WHERE `block_identifier` = "' . pSQL($block_identifier) . '" AND `status` = "1"';
	
	        if (Shop::isFeatureActive()) {
	            $sql .= ' AND `id_bestkit_customblocks` IN (
	                SELECT sa.`id_bestkit_customblocks`
	                FROM `' . _DB_PREFIX_ . 'bestkit_customblocks_shop` sa
	                WHERE sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
	            )';
	        }
	
	        $block_id = (int)Db::getInstance()->getValue($sql);
	
	        if ($block_id) {
	            $block = new self($block_id, Context::getContext()->cookie->id_lang);
	            return $block;
	        }
        }

        return new self;
    }
}