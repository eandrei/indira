<?php
/**
* Super User Module
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2016 idnovate
*  @license   See above
*/

class SuperUser extends Module
{
    protected $errors = array();
    protected $success;

    public function __construct()
    {
        $this->name = 'superuser';
        $this->tab = 'front_office_features';
        $this->version = '2.1.2';
        $this->author = 'idnovate';
        $this->module_key = '7ef93903ff837a50dd1702bcc2553ab7';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Super User');
        $this->description = $this->l('Log in to your shop as one of your customers!');

        /* Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
            $this->local_path = _PS_MODULE_DIR_.$this->name.'/';
        }
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('adminCustomers') ||
            !$this->registerHook('adminOrder')) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitSuperuserModule') == true) {
            if ($customer = new Customer(Tools::getValue('SUPERUSER_CUSTOMERS'))) {
                $params = array('id_customer' => $customer->id, 'secure_key' => hash('sha256', $customer->passwd), 'use_last_cart' => '1');
                $this->context->smarty->assign(array(
                    'su_path'                   => $this->_path,
                    'errors'                    => $this->errors,
                    'success'                   => $this->success,
                    'cookie_id_customer'        => $customer->id,
                    'cookie_customer_firstname' => $customer->firstname,
                    'cookie_customer_lastname'  => $customer->lastname,
                    'customers'                 => Customer::getCustomers(),
                    'displayName'               => $this->displayName,
                    'shop_ori'                  => Context::getContext()->shop->id,
                    'customer'                  => (array)$customer,
                    'customers_controller'      => ((version_compare(_PS_VERSION_, '1.5', '<') && strtolower(Tools::getValue('tab')) == 'admincustomers') || (version_compare(_PS_VERSION_, '1.5', '>=') && strtolower(Dispatcher::getInstance()->getController()) == "admincustomers")) ? true : false,
                    'frontoffice_url'           => version_compare(_PS_VERSION_, '1.5', '<') ? (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/superuser/setuser.php?'.http_build_query($params) : $this->context->link->getModuleLink('superuser', 'setuser', $params, true),

                ));
            }
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $this->renderForm14();
        } else {
            return $this->renderForm();
        }
    }

    protected function renderForm()
    {
        $html = '';
        $html .= $this->_displayIdnovateHeader();

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
        );

        $html .= $helper->generateForm(array($this->getConfigForm()));

        return $html;
    }

    protected function renderForm14()
    {
        $html = '';
        $html .= $this->_displayIdnovateHeader();

        $helper = new Helper();

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
        );

        $html .= $helper->generateForm(array($this->getConfigForm()));

        return $html;
    }

    protected function getConfigForm()
    {
        $customers_list = Customer::getCustomers();

        foreach ($customers_list as &$customer) {
            $customer['id'] = $customer['id_customer'];
            $customer['name'] = $customer['id_customer'].' - '.$customer['firstname'].' '.$customer['lastname'];
        }

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 9,
                        'type' => 'free',
                        'label' => '',
                        'name' => 'SUPERUSER_CONNECT_FORM',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Customers'),
                        'name' => 'SUPERUSER_CUSTOMERS',
                        'options' => array(
                            'query' => $customers_list,
                            'id' => 'id_customer',
                            'name' => 'name',
                        ),
                        'hint' => 'Select the customer that you want to log with in frontoffice'
                    ),
                    array(
                        'col' => 9,
                        'type' => 'free',
                        'label' => '',
                        'name' => 'SUPERUSER_CONNECT_BUTTON',
                    ),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        $fields = array();

        $fields['SUPERUSER_CUSTOMERS'] = '';
        $fields['SUPERUSER_CONNECT_BUTTON'] = '<div class="margin-form"><button type="submit" class="btn btn-primary button" name="submitSuperuserModule"><i class="icon-key"></i>'.$this->l('Connect as customer').'</button></div>';

        $fields['SUPERUSER_CONNECT_FORM'] =
            $this->context->smarty->fetch($this->local_path.'views/templates/admin/admin.tpl');

        return $fields;
    }

    protected function _displayIdnovateHeader()
    {
        $this->context->smarty->assign('this_path', $this->_path);

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $this->display(__FILE__, '/views/templates/hook/info.tpl');
        } else {
            return $this->display(__FILE__, 'info.tpl');
        }
    }

    public function hookAdminCustomers($param)
    {
        if (isset($param['id_customer'])) {
            //Customer block
            $id_customer = (int)$param['id_customer'];
        } else {
            //Order block
            $order = new Order($param['id_order']);
            $id_customer = (int)$order->id_customer;
        }

        $customer = new Customer($id_customer);
        $params = array('id_customer' => $id_customer, 'secure_key' => hash('sha256', $customer->passwd), 'use_last_cart' => '1');
        $this->context->smarty->assign(array(
            'displayName'               => $this->displayName,
            'su_path'                   => $this->_path,
            'shop_ori'                  => Context::getContext()->shop->id,
            'customer'                  => (array)$customer,
            'customers_controller'      => ((version_compare(_PS_VERSION_, '1.5', '<') && strtolower(Tools::getValue('tab')) == 'admincustomers') || (version_compare(_PS_VERSION_, '1.5', '>=') && strtolower(Dispatcher::getInstance()->getController()) == "admincustomers")) ? true : false,
            'frontoffice_url'           => version_compare(_PS_VERSION_, '1.5', '<') ? (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/superuser/setuser.php?'.http_build_query($params) : $this->context->link->getModuleLink('superuser', 'setuser', $params, true),
        ));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            echo $this->display(__FILE__, 'views/templates/hook/backoffice_block.tpl');
        } else {
            echo $this->display(__FILE__, 'backoffice_block.tpl');
        }
    }

    public function hookAdminOrder($param)
    {
        $this->hookAdminCustomers($param);
    }
}
