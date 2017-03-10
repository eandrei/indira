<?php
/**
 * 2007-2015 PrestaShop.
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class MlcInstagram extends Module
{
    private $_hooks = array();
    public function __construct()
    {
        $this->name = 'mlcinstagram';
        $this->tab = 'front_office_features';
        $this->version = '1.3.0';
        $this->author = 'milinhc';
        $this->need_instance = 0;
        $this->module_key = 'd90b39e1d64f45c6fffd86f965a97b9f';

        $this->bootstrap = true;
        parent::__construct();

        $this->initHookArray();

        $this->displayName = $this->l('mlc Instagram');
        $this->description = $this->l('Adds a block containing your instagram.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    private function initHookArray()
    {
        $this->_hooks = array(
            'Hooks' => array(
                array(
                    'id' => 'displayTopColumn',
                    'val' => '1',
                    'name' => $this->l('displayTopColumn'),
                ),
                array(
                    'id' => 'displayHome',
                    'val' => '1',
                    'name' => $this->l('displayHome'),
                ),
                array(
                    'id' => 'displayFooter',
                    'val' => '1',
                    'name' => $this->l('displayFooter'),
                ),
                array(
                    'id' => 'displayLeftColumn',
                    'val' => '1',
                    'name' => $this->l('displayLeftColumn'),
                ),
                array(
                    'id' => 'displayRightColumn',
                    'val' => '1',
                    'name' => $this->l('displayRightColumn'),
                ),
            ),
        );
    }

    private function saveHook()
    {
        foreach ($this->_hooks as $key => $values) {
            if (!$key) {
                continue;
            }
            foreach ($values as $value) {
                $id_hook = Hook::getIdByName($value['id']);

                if (Tools::getValue($key.'_'.$value['id'])) {
                    if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id)) {
                        continue;
                    }
                    if (!$this->isHookableOn($value['id'])) {
                        $this->validation_errors[] = $this->l('This module cannot be transplanted to '.$value['id'].'.');
                    } else {
                        $rs = $this->registerHook($value['id'], Shop::getContextListShopID());
                    }
                } else {
                    if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id)) {
                        $this->unregisterHook($id_hook, Shop::getContextListShopID());
                        $this->unregisterExceptions($id_hook, Shop::getContextListShopID());
                    }
                }
            }
        }
        // clear module cache to apply new data.
        Cache::clean('hook_module_list');
    }

    public function install()
    {
        $success = (parent::install()
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('MLCINSTAGRAM_USERNAME', '')
            && Configuration::updateValue('MLCINSTAGRAM_ACCESS_TOKEN', '')
            && Configuration::updateValue('MLCINSTAGRAM_INTERVAL', 3000)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_LG', 2)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_LG', 9)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_MD', 2)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_MD', 7)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_SM', 2)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_SM', 6)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_XS', 3)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_XS', 5)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_XXS', 3)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_XXS', 5)
            && Configuration::updateValue('MLCINSTAGRAM_ROWS_XXXS', 3)
            && Configuration::updateValue('MLCINSTAGRAM_COLS_XXXS', 4)
            && Configuration::updateValue('MLCINSTAGRAM_COLOR', '#182838')
            && Configuration::updateValue('MLCINSTAGRAM_HOVER_EFFECT', 0)
        );

        return $success;
    }

    public function getContent()
    {
        $output = '';
        $errors = array();
        if (Tools::isSubmit('submitMlcInstagram')) {
            $insUsername = Tools::getValue('MLCINSTAGRAM_USERNAME');
            if (!Tools::strlen($insUsername)) {
                $errors[] = $this->l('Please complete the "Username" field.');
            }
            $insToken = Tools::getValue('MLCINSTAGRAM_ACCESS_TOKEN');
            if (!Tools::strlen($insToken)) {
                $errors[] = $this->l('Please complete the "Access Token" field.');
            }
            $insInterval = Tools::getValue('MLCINSTAGRAM_INTERVAL');
            if (!Tools::strlen($insInterval)) {
                $errors[] = $this->l('Please complete the "Interval" field.');
            }
            
            $insRowsLG = Tools::getValue('MLCINSTAGRAM_ROWS_LG');
            if (!Tools::strlen($insRowsLG)) {
                $errors[] = $this->l('Please complete the "Number of rows" field.');
            }
            $insColsLG = Tools::getValue('MLCINSTAGRAM_COLS_LG');
            if (!Tools::strlen($insColsLG)) {
                $errors[] = $this->l('Please complete the "Number of colums" field.');
            }
            
            $insRowsMD = Tools::getValue('MLCINSTAGRAM_ROWS_MD');
            if (!Tools::strlen($insRowsMD)) {
                $errors[] = $this->l('Please complete the "Number of rows (screen < 1024px)" field.');
            }
            $insColsMD = Tools::getValue('MLCINSTAGRAM_COLS_MD');
            if (!Tools::strlen($insColsMD)) {
                $errors[] = $this->l('Please complete the "Number of colums (screen < 1024px)" field.');
            }
            
            $insRowsSM = Tools::getValue('MLCINSTAGRAM_ROWS_SM');
            if (!Tools::strlen($insRowsSM)) {
                $errors[] = $this->l('Please complete the "Number of rows (screen < 768px)" field.');
            }
            $insColsSM = Tools::getValue('MLCINSTAGRAM_COLS_SM');
            if (!Tools::strlen($insColsSM)) {
                $errors[] = $this->l('Please complete the "Number of colums (screen < 768px)" field.');
            }
            
            $insRowsXS = Tools::getValue('MLCINSTAGRAM_ROWS_XS');
            if (!Tools::strlen($insRowsXS)) {
                $errors[] = $this->l('Please complete the "Number of rows (screen < 480px)" field.');
            }
            $insColsXS = Tools::getValue('MLCINSTAGRAM_COLS_XS');
            if (!Tools::strlen($insColsXS)) {
                $errors[] = $this->l('Please complete the "Number of colums (screen < 480px)" field.');
            }
            
            $insRowsXXS = Tools::getValue('MLCINSTAGRAM_ROWS_XXS');
            if (!Tools::strlen($insRowsXXS)) {
                $errors[] = $this->l('Please complete the "Number of rows (screen < 320px)" field.');
            }
            $insColsXXS = Tools::getValue('MLCINSTAGRAM_COLS_XXS');
            if (!Tools::strlen($insColsXXS)) {
                $errors[] = $this->l('Please complete the "Number of colums (screen < 320px)" field.');
            }
            
            $insRowsXXXS = Tools::getValue('MLCINSTAGRAM_ROWS_XXXS');
            if (!Tools::strlen($insRowsXXXS)) {
                $errors[] = $this->l('Please complete the "Number of rows (screen < 240px)" field.');
            }
            $insColsXXXS = Tools::getValue('MLCINSTAGRAM_COLS_XXXS');
            if (!Tools::strlen($insColsXXXS)) {
                $errors[] = $this->l('Please complete the "Number of colums (screen < 240px)" field.');
            }

            if (count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('MLCINSTAGRAM_USERNAME', $insUsername);
                Configuration::updateValue('MLCINSTAGRAM_ACCESS_TOKEN', $insToken);
                Configuration::updateValue('MLCINSTAGRAM_INTERVAL', $insInterval);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_LG', $insRowsLG);
                Configuration::updateValue('MLCINSTAGRAM_COLS_LG', $insColsLG);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_MD', $insRowsMD);
                Configuration::updateValue('MLCINSTAGRAM_COLS_MD', $insColsMD);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_SM', $insRowsSM);
                Configuration::updateValue('MLCINSTAGRAM_COLS_SM', $insColsSM);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_XS', $insRowsXS);
                Configuration::updateValue('MLCINSTAGRAM_COLS_XS', $insColsXS);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_XXS', $insRowsXXS);
                Configuration::updateValue('MLCINSTAGRAM_COLS_XXS', $insColsXXS);
                
                Configuration::updateValue('MLCINSTAGRAM_ROWS_XXXS', $insRowsXXXS);
                Configuration::updateValue('MLCINSTAGRAM_COLS_XXXS', $insColsXXXS);
                
                $insColor = Tools::getValue('MLCINSTAGRAM_COLOR');
                Configuration::updateValue('MLCINSTAGRAM_COLOR', $insColor);
                
                $insHoverEffect = Tools::getValue('MLCINSTAGRAM_HOVER_EFFECT');
                Configuration::updateValue('MLCINSTAGRAM_HOVER_EFFECT', $insHoverEffect);

                $this->saveHook();

                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->renderForm();
    }
    private function _prepareHook($hook_hash = '')
    {
        $username = Configuration::get('MLCINSTAGRAM_USERNAME');
        $access_token = Configuration::get('MLCINSTAGRAM_ACCESS_TOKEN');
        $interval = Configuration::get('MLCINSTAGRAM_INTERVAL');
        $rows_lg = Configuration::get('MLCINSTAGRAM_ROWS_LG');
        $cols_lg = Configuration::get('MLCINSTAGRAM_COLS_LG');
        $rows_md = Configuration::get('MLCINSTAGRAM_ROWS_MD');
        $cols_md = Configuration::get('MLCINSTAGRAM_COLS_MD');
        $rows_sm = Configuration::get('MLCINSTAGRAM_ROWS_SM');
        $cols_sm = Configuration::get('MLCINSTAGRAM_COLS_SM');
        $rows_xs = Configuration::get('MLCINSTAGRAM_ROWS_XS');
        $cols_xs = Configuration::get('MLCINSTAGRAM_COLS_XS');
        $rows_xxs = Configuration::get('MLCINSTAGRAM_ROWS_XXS');
        $cols_xxs = Configuration::get('MLCINSTAGRAM_COLS_XXS');
        $rows_xxxs = Configuration::get('MLCINSTAGRAM_ROWS_XXXS');
        $cols_xxxs = Configuration::get('MLCINSTAGRAM_COLS_XXXS');
        $hover_effect = Configuration::get('MLCINSTAGRAM_HOVER_EFFECT');
        $token_arr = explode(".", $access_token);
        $userid = $token_arr[0];
        $instagram = array();

        $this->smarty->assign(array(
            'instagram' => $instagram,
            'mlcinstagram_username' => $username,
            'mlcinstagram_access_token' => $access_token,
            'mlcinstagram_userid' => $userid,
            'mlcinstagram_interval' => $interval,
            'mlcinstagram_limit' => ($rows_lg * $cols_lg) + 4,
            'mlcinstagram_rows_lg' => $rows_lg,
            'mlcinstagram_cols_lg' => $cols_lg,
            'mlcinstagram_rows_md' => $rows_md,
            'mlcinstagram_cols_md' => $cols_md,
            'mlcinstagram_rows_sm' => $rows_sm,
            'mlcinstagram_cols_sm' => $cols_sm,
            'mlcinstagram_rows_xs' => $rows_xs,
            'mlcinstagram_cols_xs' => $cols_xs,
            'mlcinstagram_rows_xxs' => $rows_xxs,
            'mlcinstagram_cols_xxs' => $cols_xxs,
            'mlcinstagram_rows_xxxs' => $rows_xxxs,
            'mlcinstagram_cols_xxxs' => $cols_xxxs,
            'mlcinstagram_hover_effect' => $hover_effect,
            'hook_hash' => $hook_hash,
        ));

        return true;
    }
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addJS($this->_path.'views/js/modernizr.js');
        $this->context->controller->addJS($this->_path.'views/js/instafeed.min.js');
        $this->context->controller->addJS($this->_path.'views/js/jquery.gridrotator.js');
        $this->context->controller->addCSS($this->_path.'views/css/style.css');
        if (!$this->isCached('header.tpl', $this->getCacheId())) {
            $custom_css = '';
            
            if ($color = Configuration::get('MLCINSTAGRAM_COLOR')) {
                $custom_css .= '.center_column .instagram_block .title_block{color:'.$color.';}';
            }

            if ($custom_css) {
                $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
            }
        }

        return $this->display(__FILE__, 'header.tpl', $this->getCacheId());
    }
    public function hookDisplayTopColumn($params)
    {
        return $this->hookDisplayHome($params, $this->getHookHash(__FUNCTION__), 2);
    }
    public function hookDisplayHome($params, $hook_hash = '', $flag = 0)
    {
        if (!$hook_hash) {
            $hook_hash = $this->getHookHash(__FUNCTION__);
        }
        if (!$this->isCached('instagram.tpl', $this->mlcGetCacheId($hook_hash))) {
            if (!$this->_prepareHook($hook_hash)) {
                return false;
            }
        }

        return $this->display(__FILE__, 'instagram.tpl', $this->mlcGetCacheId($hook_hash));
    }
    public function hookDisplayFooter($params)
    {
        return $this->hookDisplayHome($params, $this->getHookHash(__FUNCTION__), 2);
    }
    public function hookDisplayLeftColumn($params)
    {
        return $this->hookDisplayHome($params, $this->getHookHash(__FUNCTION__), 2);
    }
    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayHome($params, $this->getHookHash(__FUNCTION__), 2);
    }
    protected function mlcGetCacheId($key, $name = null)
    {
        $cache_id = parent::getCacheId($name);

        return $cache_id.'_'.$key;
    }
    public function getHookHash($func = '')
    {
        if (!$func) {
            return '';
        }

        return Tools::substr(md5($func), 0, 10);
    }
    public function renderForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Username:'),
                    'name' => 'MLCINSTAGRAM_USERNAME',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Access Token:'),
                    'name' => 'MLCINSTAGRAM_ACCESS_TOKEN',
                    'class' => 'fixed-width-xxl',
                    'desc' => $this->l('Find it here: http://instagram.pixelunion.net/'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Color:'),
                    'name' => 'MLCINSTAGRAM_COLOR',
                    'class' => 'color',
                    'size' => 20,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Interval (ms):'),
                    'name' => 'MLCINSTAGRAM_INTERVAL',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows:'),
                    'name' => 'MLCINSTAGRAM_ROWS_LG',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns:'),
                    'name' => 'MLCINSTAGRAM_COLS_LG',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows (screen < 1024px):'),
                    'name' => 'MLCINSTAGRAM_ROWS_MD',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns (screen < 1024px):'),
                    'name' => 'MLCINSTAGRAM_COLS_MD',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows (screen < 768px):'),
                    'name' => 'MLCINSTAGRAM_ROWS_SM',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns (screen < 768px):'),
                    'name' => 'MLCINSTAGRAM_COLS_SM',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows (screen < 480px):'),
                    'name' => 'MLCINSTAGRAM_ROWS_XS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns (screen < 480px):'),
                    'name' => 'MLCINSTAGRAM_COLS_XS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows (screen < 320px):'),
                    'name' => 'MLCINSTAGRAM_ROWS_XXS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns (screen < 320px):'),
                    'name' => 'MLCINSTAGRAM_COLS_XXS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of rows (screen < 240px):'),
                    'name' => 'MLCINSTAGRAM_ROWS_XXXS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of columns (screen < 240px):'),
                    'name' => 'MLCINSTAGRAM_COLS_XXXS',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Hover effect:'),
                    'name' => 'MLCINSTAGRAM_HOVER_EFFECT',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'hover_effect_0',
                            'value' => 0,
                            'label' => $this->l('None'),
                        ),
                        array(
                            'id' => 'hover_effect_1',
                            'value' => 1,
                            'label' => $this->l('Fade & scale'),
                        ),
                        array(
                            'id' => 'hover_effect_2',
                            'value' => 2,
                            'label' => $this->l('White line'),
                        ),
                        array(
                            'id' => 'hover_effect_3',
                            'value' => 3,
                            'label' => $this->l('White block'),
                        ),
                        array(
                            'id' => 'hover_effect_4',
                            'value' => 4,
                            'label' => $this->l('Fade'),
                        ),
                        array(
                            'id' => 'hover_effect_5',
                            'value' => 5,
                            'label' => $this->l('Black line'),
                        ),
                        array(
                            'id' => 'hover_effect_5',
                            'value' => 6,
                            'label' => $this->l('Black block'),
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Hook manager'),
                'icon' => 'icon-cogs',
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        foreach ($this->_hooks as $key => $values) {
            if (!is_array($values) || !count($values)) {
                continue;
            }
            $this->fields_form[1]['form']['input'][] = array(
                'type' => 'checkbox',
                'label' => $this->l($key),
                'name' => $key,
                'lang' => true,
                'values' => array(
                    'query' => $values,
                    'id' => 'id',
                    'name' => 'name',
                ),
            );
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMlcInstagram';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($this->fields_form);
    }

    public function getConfigFieldsValues()
    {
        $fields_values = array(
            'MLCINSTAGRAM_USERNAME' => Tools::getValue('MLCINSTAGRAM_USERNAME', Configuration::get('MLCINSTAGRAM_USERNAME')),
            'MLCINSTAGRAM_ACCESS_TOKEN' => Tools::getValue('MLCINSTAGRAM_ACCESS_TOKEN', Configuration::get('MLCINSTAGRAM_ACCESS_TOKEN')),
            'MLCINSTAGRAM_COLOR' => Tools::getValue('MLCINSTAGRAM_COLOR', Configuration::get('MLCINSTAGRAM_COLOR')),
            'MLCINSTAGRAM_INTERVAL' => Tools::getValue('MLCINSTAGRAM_INTERVAL', Configuration::get('MLCINSTAGRAM_INTERVAL')),
            'MLCINSTAGRAM_ROWS_LG' => Tools::getValue('MLCINSTAGRAM_ROWS_LG', Configuration::get('MLCINSTAGRAM_ROWS_LG')),
            'MLCINSTAGRAM_COLS_LG' => Tools::getValue('MLCINSTAGRAM_COLS_LG', Configuration::get('MLCINSTAGRAM_COLS_LG')),
            'MLCINSTAGRAM_ROWS_MD' => Tools::getValue('MLCINSTAGRAM_ROWS_MD', Configuration::get('MLCINSTAGRAM_ROWS_MD')),
            'MLCINSTAGRAM_COLS_MD' => Tools::getValue('MLCINSTAGRAM_COLS_MD', Configuration::get('MLCINSTAGRAM_COLS_MD')),
            'MLCINSTAGRAM_ROWS_SM' => Tools::getValue('MLCINSTAGRAM_ROWS_SM', Configuration::get('MLCINSTAGRAM_ROWS_SM')),
            'MLCINSTAGRAM_COLS_SM' => Tools::getValue('MLCINSTAGRAM_COLS_SM', Configuration::get('MLCINSTAGRAM_COLS_SM')),
            'MLCINSTAGRAM_ROWS_XS' => Tools::getValue('MLCINSTAGRAM_ROWS_XS', Configuration::get('MLCINSTAGRAM_ROWS_XS')),
            'MLCINSTAGRAM_COLS_XS' => Tools::getValue('MLCINSTAGRAM_COLS_XS', Configuration::get('MLCINSTAGRAM_COLS_XS')),
            'MLCINSTAGRAM_ROWS_XXS' => Tools::getValue('MLCINSTAGRAM_ROWS_XXS', Configuration::get('MLCINSTAGRAM_ROWS_XXS')),
            'MLCINSTAGRAM_COLS_XXS' => Tools::getValue('MLCINSTAGRAM_COLS_XXS', Configuration::get('MLCINSTAGRAM_COLS_XXS')),
            'MLCINSTAGRAM_ROWS_XXXS' => Tools::getValue('MLCINSTAGRAM_ROWS_XXXS', Configuration::get('MLCINSTAGRAM_ROWS_XXXS')),
            'MLCINSTAGRAM_COLS_XXXS' => Tools::getValue('MLCINSTAGRAM_COLS_XXXS', Configuration::get('MLCINSTAGRAM_COLS_XXXS')),
            'MLCINSTAGRAM_HOVER_EFFECT' => Tools::getValue('MLCINSTAGRAM_HOVER_EFFECT', Configuration::get('MLCINSTAGRAM_HOVER_EFFECT')),
        );

        foreach ($this->_hooks as $key => $values) {
            if (!$key) {
                continue;
            }
            foreach ($values as $value) {
                $fields_values[$key.'_'.$value['id']] = 0;
                if ($id_hook = Hook::getIdByName($value['id'])) {
                    if (Hook::getModulesFromHook($id_hook, $this->id)) {
                        $fields_values[$key.'_'.$value['id']] = 1;
                    }
                }
            }
        }

        return $fields_values;
    }
}
