<?php

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('../../modules/fancourier/fancourier.php');

// To have context available and translation
$fancourier = new FANCourier();

// Default answer values => key
$result = array(
	'answer' => true,
	'msg' => ''
);

// Check Token
if (Tools::getValue('token') != sha1('fancourier'._COOKIE_KEY_.Context::getContext()->cart->id))
{
	$result['answer'] = false;
	$result['msg'] = $fancourier->l('Invalid token');
}

// If no problem with token but no delivery available
if ($result['answer'] && !($result = $fancourier->getDeliveryInfos(Context::getContext()->cart->id, Context::getContext()->customer->id)))
{
	$result['answer'] = false;
	$result['msg'] = $fancourier->l('No delivery information selected');
}

die(Tools::jsonEncode($result));

?>
