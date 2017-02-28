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
<div class="ilmsize_group bootstrap col-sm-12">
<div class="panel">
<fieldset>
    <legend><img src="{$baseURL|escape:'htmlall':'UTF-8'}modules/ilmsizechart/img/ruler.png" />  {l s='Create / Edit Size Chart' mod='ilmsizechart'}</legend>
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&addSizeChart=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				Add Size Chart
			</span>
		</a>
	</span>
	<table width="100%" class="table " cellpadding="0" cellspacing="0">
    <thead>
	<tr class="nodrag nodrop"><th align="left">ID</th><th align="left">Group</th><th align="left">Chart Type</th><th align="left">Action</th></tr>
    </thead>
    {foreach from=$sizes item=size}
    <tr><td width="10%">{$size.id_value|escape:'intval'}</td>
    <td width="60%">{$size.title|escape:'htmlall':'UTF-8'}</td>
    <td width="20%">{$type[$size.chart_type]|escape:'intval'}</td>
    <td width="10%">
    <div class="btn-group-action">
    <div class="btn-group pull-right">
    	<a title="Edit" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&addSizeChart&id_sizeedit={$size.id_value|escape:'htmlall':'UTF-8'}" class=""><img alt="" src="{$smarty.const._PS_ADMIN_IMG_}edit.gif"></a>
    	<a title="Delete" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&id_sizedelete={$size.id_value|escape:'htmlall':'UTF-8'}"><img alt="" src="{$smarty.const._PS_ADMIN_IMG_}delete.gif"></a>
    </div>
    </div>
    </td>
    </tr>
    {/foreach}
  
    </table>
</div>
</div>
