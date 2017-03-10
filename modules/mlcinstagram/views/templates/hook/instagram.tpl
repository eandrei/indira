{*
 * 2007-2016 PrestaShop.
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
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE instagram -->
<div id="instagram_block_{$hook_hash|escape:'htmlall':'UTF-8'}" class="block column_block instagram_block hover_effect_{$mlcinstagram_hover_effect|escape:"htmlall":"UTF-8"}">
	<h2 class="title_block">
		<i class="fa fa-instagram"></i> {$mlcinstagram_username|escape:"htmlall":"UTF-8"}
	</h2>
  <ul id="instagram_{$hook_hash|escape:'htmlall':'UTF-8'}" class="instafeed"></ul>
	<script type="text/javascript">
  //<![CDATA[
  {literal}
	var ins_ger = jQuery("#instagram_block_{/literal}{$hook_hash|escape:'htmlall':'UTF-8'}{literal}");
	var feed = new Instafeed({
			get: 'user',
			userId: {/literal}{$mlcinstagram_userid|escape:"htmlall":"UTF-8"}{literal},
			accessToken: '{/literal}{$mlcinstagram_access_token|escape:"htmlall":"UTF-8"}{literal}',
			target: 'instagram_{/literal}{$hook_hash|escape:"htmlall":"UTF-8"}{literal}',
			resolution: 'standard_resolution',
			template: '<li class="il-item"><a href="{{link}}" target="_blank"><img src="{{image}}" /></a></li>',
			after: function() {
	      ins_ger.gridrotator({
					{/literal}
	        rows : '{$mlcinstagram_rows_lg|escape:"htmlall":"UTF-8"}',
	        columns : '{$mlcinstagram_cols_lg|escape:"htmlall":"UTF-8"}',
	        interval : '{$mlcinstagram_interval|escape:"htmlall":"UTF-8"}',
					preventClick: false,
	        w1024 : {
			      rows : '{$mlcinstagram_rows_md|escape:"htmlall":"UTF-8"}',
			      columns : '{$mlcinstagram_cols_md|escape:"htmlall":"UTF-8"}',
	        },
	        w768 : {
		        rows : '{$mlcinstagram_rows_sm|escape:"htmlall":"UTF-8"}',
		        columns : '{$mlcinstagram_cols_sm|escape:"htmlall":"UTF-8"}',
	        },
	        w480 : {
		        rows : '{$mlcinstagram_rows_xs|escape:"htmlall":"UTF-8"}',
		        columns : '{$mlcinstagram_cols_xs|escape:"htmlall":"UTF-8"}',
	        },
	        w320 : {
		        rows : '{$mlcinstagram_rows_xxs|escape:"htmlall":"UTF-8"}',
		        columns : '{$mlcinstagram_cols_xxs|escape:"htmlall":"UTF-8"}',
	        },
	        w240 : {
		        rows : '{$mlcinstagram_rows_xxxs|escape:"htmlall":"UTF-8"}',
		        columns : '{$mlcinstagram_cols_xxxs|escape:"htmlall":"UTF-8"}',
	        },
					{literal}
	      });
			}
	});
	jQuery(function($) {
		feed.run();
	});
  {/literal} 
  //]]>
	</script>
</div>
<!-- /MODULE instagram -->