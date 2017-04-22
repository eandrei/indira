<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminEmagMarketplaceInvoicesController extends AdminAttachmentsController
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function postProcess()
	{
		$id_order = Tools::getValue('id_order');
		$_POST['name_'.$this->context->language->id] = 'External Invoice for Order '.$id_order;
		
		$attachment = parent::postProcess();
		
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'emagmp_order_history` SET
			id_attachment = '.(int)$attachment->id.'
			WHERE id_order = '.(int)$id_order.'
		');
		
		$this->redirect_after = $_SERVER['HTTP_REFERER'];
		
		return $attachment;
	}
}
