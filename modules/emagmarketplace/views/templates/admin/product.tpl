{*
* 2007-2011 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 12915 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<h4 class="tab">1. Info.</h4>
<h4>eMAG Marketplace Settings</h4>

<div class="separation"></div>

<table>
	<tr>
		<td class="col-left"><label>Category:</label></td>
		<td style="padding-bottom:5px;">
			<input name="emagmp[emag_category_id]" type="text" value="{$emag_product.emag_category_id}" style="width: 100%;" />
			<p class="preference_description">Set to -1 to use the default value from the eMAG Category Mapping page. Otherwise it will be listed in this category.</p>
		</td>
	</tr>
	<tr>
		<td class="col-left"><label>Family Type:</label></td>
		<td style="padding-bottom:5px;">
			<select name="emagmp[emag_family_type_id]" style="width: 100%;"><option value="-1">-1</option></select>
			<p class="preference_description">Set to -1 to use the default value from the eMAG Category Mapping page. Otherwise it will be listed in this family.</p>
		</td>
	</tr>
	<tr>
		<td class="col-left"><label>Commission:</label></td>
		<td style="padding-bottom:5px;">
			<input name="emagmp[commission]" type="text" value="{$emag_product.commission}" style="width: 100%;" />
			<p class="preference_description">Set to -1 to use the default value from the eMAG Category Mapping page. Otherwise it will be listed with this commission.</p>
		</td>
	</tr>
</table>

<script type="text/javascript">
$(document).ready(function()
{
	var emag_category_definitions = [
		{foreach $emag_category_definitions key=key item=category_definition}
			"{$category_definition.emag_category_id} - {$category_definition.emag_category_name}",
		{/foreach}
	]
	var emag_family_type_definitions = {
		{foreach $emag_family_type_definitions key=key item=family_type_definitions}
			"{$key}": [
				{foreach $family_type_definitions key=key item=family_type_definition}
					{   "id": "{$family_type_definition.emag_family_type_id}", "value": "{$family_type_definition.emag_family_type_name}"   },
				{/foreach}
			],
		{/foreach}
	};
	$('[name^="emagmp\\[emag_category_id\\]"]').autocomplete(emag_category_definitions, {
		minChars: 1,
		matchContains: true
	}).result(function(event, item) {
        $(this).val(item);
		$('[name^="emagmp\\[emag_family_type_id\\]"]').val(-1);
		$('[name^="emagmp\\[emag_family_type_id\\]"] option').each(function(index, element)
		{
			if (index == 0)
				return;
			$(this).remove();
		});
		updateEmagFamilyTypes(item);
    });
	
	function updateEmagFamilyTypes(emag_category_id)
	{
		var pattern = /([0-9]+) - .+/;
		var result = pattern.exec(emag_category_id);
		if (!result)
			emag_category_id = -1;
		else
			emag_category_id = result[1];
		if (emag_category_id == -1)
			emag_category_id = {$emag_product.emag_category_id_default};
		if (typeof(emag_family_type_definitions[emag_category_id]) == 'undefined')
			return;
		for (i = 0; i < emag_family_type_definitions[emag_category_id].length; i++)
		{
			$('[name^="emagmp\\[emag_family_type_id\\]"]').append('<option value="'+emag_family_type_definitions[emag_category_id][i]['id']+'">'+emag_family_type_definitions[emag_category_id][i]['value']+'</option>');
		}
	}
	
	updateEmagFamilyTypes('{$emag_product.emag_category_id}');
	$('[name^="emagmp\\[emag_family_type_id\\]"]').val('{$emag_product.emag_family_type_id}');
});
</script>