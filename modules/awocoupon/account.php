<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (!defined('_PS_VERSION_') || (class_exists('Context') && is_object(Context::getContext()->customer) && !Tools::getToken(FALSE, Context::getContext()))) exit;

// initialize
if(_PS_VERSION_>='1.5') Tools::redirect('index.php');
require_once _PS_MODULE_DIR_.'awocoupon/lib/awohelper.php';
require_once _PS_MODULE_DIR_.'awocoupon/awocoupon.php';


// get output
$controller = Tools::getValue('option');
if(empty($controller)) $controller = 'coupons';
include_once(_PS_MODULE_DIR_.'awocoupon/ps14/front/controllers/'.$controller.'.php');
$controller_class = 'AwoCoupon'.$controller.'ModuleFrontController';
$runner = new $controller_class();


// output
Tools::addCSS(__PS_BASE_URI__.'modules/awocoupon/media/css/style.css', 'all');
if(method_exists($runner,'setMedia')) $runner->setMedia();

$display_type = Tools::getValue('display');
if($display_type!='ajax') include(dirname(__FILE__).'/../../header.php');
echo $runner->initContent();
if($display_type!='ajax') include(dirname(__FILE__).'/../../footer.php'); 

