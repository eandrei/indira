{**
 * 
 *
 * ILM Size Chart act as Size guide for Prestashop store.
 * Admin Can configure the Size chart in 4 different type all information explanined in the Documnet or You can view a demo.
 *
 * @author    Abdullah Ahamed
 * @copyright Copyright (c) 2014 ILM Tech. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @site		http://www.ilmtech.in
 * @contact		info@ilmtech.in
 
 *}
<div class="ilmsize_label bootstrap col-sm-6">
<div class="panel">
<fieldset>
    <legend>{l s='Sizechart Label' mod='ilmsizechart'}</legend>
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&addLabelChart=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				Add Label
			</span>
		</a>
	</span>

	<table width="100%" class="table " cellpadding="0" cellspacing="0">
    <thead>
	<tr class="nodrag nodrop"><th align="left">ID</th><th align="left">Title</th><th align="left">Active</th><th align="left">Action</th></tr>
    </thead>
    {foreach from=$labels item=label}
    <tr><td width="10%">{$label.id_label|escape:'intval'}</td>
    <td width="60%">{$label.title|escape:'htmlall':'UTF-8'}</td>
    <td width="20%">{$label.active|escape:'intval'}</td>
    <td width="10%">
    <div class="btn-group-action">
    <div class="btn-group pull-right">
    	<a title="Edit" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&addLabelChart=1&id_labeledit={$label.id_label|escape:'htmlall':'UTF-8'}" class=""><img alt="" src="{$smarty.const._PS_ADMIN_IMG_}edit.gif"></a>
    	<a title="Delete" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&id_labeldelete={$label.id_label|escape:'htmlall':'UTF-8'}"><img alt="" src="{$smarty.const._PS_ADMIN_IMG_}delete.gif"></a>
    </div>
    </div>
    </td>
    </tr>
    {/foreach}
  
    </table>
</fieldset>
</div>
</div>
