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
*  @version  Release: $Revision: 7104 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class emagmarketplaceInvoicesModuleFrontController extends ModuleFrontController
{
	protected $display_header = false;
	protected $display_footer = false;

    public $content_only = true;

	protected $template;
	public $filename;

	public function display()
	{
		$id_order_invoice = Tools::getValue('id');
		
		$order_invoice = new OrderInvoice((int)$id_order_invoice);
		if (!Validate::isLoadedObject($order_invoice))
			die(Tools::displayError('Cannot find order invoice in database'));

		$pdf = new PDF($order_invoice, PDF::TEMPLATE_INVOICE, $this->context->smarty, $this->context->language->id);
		$pdf->render();
	}


	/**
	 * Returns the invoice template associated to the country iso_code
	 * @param string $iso_user
	 */
	public function getTemplate($iso_country)
	{
		$template = _PS_THEME_PDF_DIR_.'/invoice.tpl';

		$iso_template = _PS_THEME_PDF_DIR_.'/invoice.'.$iso_country.'.tpl';
		if (file_exists($iso_template))
			$template = $iso_template;

		return $template;
	}
}