<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2015 Knowband
 * @license   see file: LICENSE.txt 
 */

class SupercheckoutConfiguration
{
	/*
	 * return default settings of the supercheckout page
	 */

	public function getDefaultSettings()
	{
		$settings = array(
			'adv_id' => 0,
			'loginizer_adv' => 1,
			'plugin_id' => 'PS0002',
			'version' => '0.1',
			'enable' => 0,
			'enable_guest_checkout' => 0,
			'enable_guest_register' => 0,
			'checkout_option' => 0,
			'super_test_mode' => 0,
			'qty_update_option' => 1,
			'inline_validation' => array('enable' => 0),
			'social_login_popup' => array('enable' => 1),
			'fb_login' => array('enable' => 0, 'app_id' => '', 'app_secret' => ''),
			'mailchimp' => array('enable' => 0, 'api' => '', 'list' => '', 'default' => 0),
			'google_login' => array('enable' => 0, 'app_id' => '', 'client_id' => '',
				'app_secret' => ''),
			'customer_personal' => array(
				'id_gender' => array('id' => 'id_gender', 'title' => 'Title', 'sort_order' => 1,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'dob' => array('id' => 'dob', 'title' => 'DOB', 'sort_order' => 2,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0))
			),
			'customer_subscription' => array(
				'newsletter' => array('id' => 'newsletter', 'title' => 'Sign up for NewsLetter', 'sort_order' => 3,
					'guest' => array('checked' => 0, 'display' => 0)),
				'optin' => array('id' => 'optin', 'sort_order' => 4, 'title' => 'Special Offer',
					'guest' => array('checked' => 0, 'display' => 0))
			),
			'hide_delivery_for_virtual'=> 0,
			'use_delivery_for_payment_add' => array('guest' => 1, 'logged' => 1),
			'show_use_delivery_for_payment_add' => array('guest' => 1, 'logged' => 1),
			'payment_address' => array(
				'firstname' => array('id' => 'firstname', 'title' => 'First Name', 'sort_order' => 1, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'lastname' => array('id' => 'lastname', 'title' => 'Last Name', 'sort_order' => 2, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'company' => array('id' => 'company', 'title' => 'Company', 'sort_order' => 4, 'conditional' => 0,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'vat_number' => array('id' => 'vat_number', 'title' => 'Vat Number', 'sort_order' => 5, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 1, 'display' => 0)),
				'address1' => array('id' => 'address1', 'title' => 'Address Line 1', 'sort_order' => 6, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'address2' => array('id' => 'address2', 'title' => 'Address Line 2', 'sort_order' => 7, 'conditional' => 0,
					'guest' => array('require' => 0, 'display' => 1), 'logged' => array('require' => 0, 'display' => 1)),
				'postcode' => array('id' => 'postcode', 'title' => 'Zip/Postal Code', 'sort_order' => 8, 'conditional' => 1,
					'guest' => array('require' => 0, 'display' => 1), 'logged' => array('require' => 0, 'display' => 1)),
				'city' => array('id' => 'city', 'title' => 'City', 'sort_order' => 9, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'id_country' => array('id' => 'id_country', 'title' => 'Country', 'sort_order' => 10, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'id_state' => array('id' => 'id_state', 'title' => 'State', 'sort_order' => 11, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'dni' => array('id' => 'dni', 'title' => 'Identification Number', 'sort_order' => 12, 'conditional' => 1,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'phone' => array('id' => 'phone', 'title' => 'Home Phone', 'sort_order' => 13, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'phone_mobile' => array('id' => 'phone_mobile', 'title' => 'Mobile Phone', 'sort_order' => 14, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'alias' => array('id' => 'alias', 'title' => 'Address Title', 'sort_order' => 15, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'other' => array('id' => 'other', 'title' => 'Other Information', 'sort_order' => 16, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
			),
			'shipping_address' => array(
				'firstname' => array('id' => 'firstname', 'title' => 'First Name', 'sort_order' => 1, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'lastname' => array('id' => 'lastname', 'title' => 'Last Name', 'sort_order' => 2, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'company' => array('id' => 'company', 'title' => 'Company', 'sort_order' => 3, 'conditional' => 0,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'vat_number' => array('id' => 'vat_number', 'title' => 'Vat Number', 'sort_order' => 4, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 1, 'display' => 0)),
				'address1' => array('id' => 'address1', 'title' => 'Address Line 1', 'sort_order' => 5, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'address2' => array('id' => 'address2', 'title' => 'Address Line 2', 'sort_order' => 6, 'conditional' => 0,
					'guest' => array('require' => 0, 'display' => 1), 'logged' => array('require' => 0, 'display' => 1)),
				'postcode' => array('id' => 'postcode', 'title' => 'Zip/Postal Code', 'sort_order' => 7, 'conditional' => 1,
					'guest' => array('require' => 0, 'display' => 1), 'logged' => array('require' => 0, 'display' => 1)),
				'city' => array('id' => 'city', 'title' => 'City', 'sort_order' => 8, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'id_country' => array('id' => 'id_country', 'title' => 'Country', 'sort_order' => 9, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'id_state' => array('id' => 'id_state', 'title' => 'State', 'sort_order' => 10, 'conditional' => 1,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'dni' => array('id' => 'dni', 'title' => 'Identification Number', 'sort_order' => 11, 'conditional' => 1,
					'guest' => array('require' => 0, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'phone' => array('id' => 'phone', 'title' => 'Home Phone', 'sort_order' => 12, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'phone_mobile' => array('id' => 'phone_mobile', 'title' => 'Mobile Phone', 'sort_order' => 13, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 1), 'logged' => array('require' => 1, 'display' => 1)),
				'alias' => array('id' => 'alias', 'title' => 'Address Title', 'sort_order' => 14, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0)),
				'other' => array('id' => 'other', 'title' => 'Other Information', 'sort_order' => 15, 'conditional' => 0,
					'guest' => array('require' => 1, 'display' => 0), 'logged' => array('require' => 0, 'display' => 0))
			),
			'payment_method' => array('enable' => 1, 'default' => '69', 'display_style' => 0),
			'shipping_method' => array('enable' => 1, 'default' => '', 'display_style' => 0),
			'display_cart' => 1,
			'cart_options' => array(
				'product_image' => array('id' => 'product_image', 'title' => 'Image', 'sort_order' => 2,
					'guest' => array('display' => 0), 'logged' => array('display' => 1)),
				'product_name' => array('id' => 'product_name', 'title' => 'Description', 'sort_order' => 2,
					'guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'product_model' => array('id' => 'product_model', 'title' => 'Model', 'sort_order' => 3,
					'guest' => array('display' => 0), 'logged' => array('display' => 0)),
				'product_qty' => array('id' => 'product_qty', 'title' => 'Quantity', 'sort_order' => 4,
					'guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'product_price' => array('id' => 'product_price', 'title' => 'Price', 'sort_order' => 5,
					'guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'product_total' => array('id' => 'product_total', 'title' => 'Total', 'sort_order' => 6,
					'guest' => array('display' => 1), 'logged' => array('display' => 1))
			),
			'cart_image_size' => array('name' => 'velsof_supercheckout_image', 'width' => 90, 'height' => 90),
			'order_total_option' => array(
				'product_sub_total' => array('guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'voucher' => array('guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'shipping_price' => array('guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'total' => array('guest' => array('display' => 1), 'logged' => array('display' => 1))
			),
			'confirm' => array(
				'order_comment_box' => array('guest' => array('display' => 1), 'logged' => array('display' => 1)),
				'term_condition' => array(
					'guest' => array('checked' => 1, 'require' => 1, 'display' => 1),
					'logged' => array('checked' => 1, 'require' => 1, 'display' => 1)
				)
			),
			'layout' => 3,
			'column_width' => array(
				'1_column' => array(1 => '100', 2 => '0', 3 => '0', 'inside' => array(1 => '0', 2 => '0')),
				'2_column' => array(1 => '30', 2 => '70', 3 => '0', 'inside' => array(1 => '50', 2 => '50')),
				'3_column' => array(1 => '30', 2 => '25', 3 => '45', 'inside' => array(1 => '0', 2 => '0'))
			),
			'modal_value' => 0,
			'design' => array(
				'login' => array(
					'1_column' => array('column' => 0, 'row' => 0, 'column-inside' => 0),
					'2_column' => array('column' => 1, 'row' => 0, 'column-inside' => 1),
					'3_column' => array('column' => 1, 'row' => 0, 'column-inside' => 0)
				),
				'shipping_address' => array(
					'1_column' => array('column' => 0, 'row' => 1, 'column-inside' => 0),
					'2_column' => array('column' => 1, 'row' => 1, 'column-inside' => 1),
					'3_column' => array('column' => 1, 'row' => 1, 'column-inside' => 0)
				),
				'payment_address' => array(
					'1_column' => array('column' => 0, 'row' => 2, 'column-inside' => 0),
					'2_column' => array('column' => 1, 'row' => 2, 'column-inside' => 1),
					'3_column' => array('column' => 1, 'row' => 2, 'column-inside' => 0)
				),
				'shipping_method' => array(
					'1_column' => array('column' => 0, 'row' => 3, 'column-inside' => 0),
					'2_column' => array('column' => 1, 'row' => 0, 'column-inside' => 3),
					'3_column' => array('column' => 2, 'row' => 0, 'column-inside' => 0)
				),
				'payment_method' => array(
					'1_column' => array('column' => 0, 'row' => 4, 'column-inside' => 0),
					'2_column' => array('column' => 2, 'row' => 0, 'column-inside' => 3),
					'3_column' => array('column' => 2, 'row' => 1, 'column-inside' => 0)
				),
				'cart' => array(
					'1_column' => array('column' => 0, 'row' => 5, 'column-inside' => 0),
					'2_column' => array('column' => 2, 'row' => 0, 'column-inside' => 2),
					'3_column' => array('column' => 3, 'row' => 0, 'column-inside' => 0)
				),
				'confirm' => array(
					'1_column' => array('column' => 0, 'row' => 6, 'column-inside' => 0),
					'2_column' => array('column' => 2, 'row' => 1, 'column-inside' => 4),
					'3_column' => array('column' => 3, 'row' => 1, 'column-inside' => 0)
				),
				'html' => array(
					'0_0' => array(
						'1_column' => array('column' => 0, 'row' => 7, 'column-inside' => 1),
						'2_column' => array('column' => 2, 'row' => 1, 'column-inside' => 4),
						'3_column' => array('column' => 3, 'row' => 4, 'column-inside' => 1),
						'value' => ''
					)
				)
			)
		);

		return $settings;
	}
}
