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
<div class="ilmsizechart bootstrap col-sm-12">
<form enctype="multipart/form-data" method="post" action="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart&saveSizeChart&ilmconfig=1&addSizeChart&id_sizeedit={$value.id_value|escape:'htmlall':'UTF-8'}">    
<div class="panel">
<fieldset>
    <legend>{l s='Size Chart' mod='ilmsizechart'}</legend>

    <input name="id_sizeedit" value="{$value.id_value|escape:'intval'}" type="hidden" />
    <div class="col-lg-6 col-sm-6">
    <div class="form-group">
        <label class="control-label col-lg-6 col-sm-6">
        <span data-original-title="Enter the Row Value" class="label-tooltip pull-right" data-toggle="tooltip" title="" align="center">Row</span>
        </label>
        <div class="col-lg-6 col-sm-6">
            <input type="text" maxlength="14" name="ilmrow" id="ilmrow" value="{$value.row|escape:'intval'}" />
        </div>
        <div class="clear">&nbsp;</div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-lg-6 col-sm-6">
        <span data-original-title="Enter the Column Value" class="label-tooltip  pull-right" data-toggle="tooltip" title="">Column</span>
        </label>
        <div class="col-lg-6 col-sm-6">
            <input type="text" maxlength="14" name="ilmcol" id="ilmcol" value="{$value.col|escape:'intval'}" />
        </div>
        <div class="clear">&nbsp;</div>
    </div>
        
    </div>
    
    <div class="col-lg-6 col-sm-6">
    <div class="form-group">
        <label class="control-label col-lg-4 col-sm-4">
        <span data-original-title="Select the chart Type" class="label-tooltip pull-right" data-toggle="tooltip" title="">Chart Type</span>
        </label>
        <div class="col-lg-6 col-sm-6">
            
            <select name="ilmcharttype">
                <option value="0" {if ($value.chart_type==0)}selected="selected"{/if}>Row Only</option>
                <option value="1" {if ($value.chart_type==1)}selected="selected"{/if}>Column Only</option>
                <option value="2" {if ($value.chart_type==2)}selected="selected"{/if}>Row &AMP; Column</option>
                <option value="3" {if ($value.chart_type==3)}selected="selected"{/if}>No Row &AMP; Column</option>
            </select>
        </div> 
        <div style="clear: both;">&nbsp;</div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-4 col-sm-4">
        <span data-original-title="Select the chart Type" class="label-tooltip pull-right" data-toggle="tooltip" title="">Chart Group</span>
        </label>
        <div class="col-lg-6 col-sm-6">
            <select name="ilmchartgroup">
                {foreach from=$groups item=group}
                <option value="{$group.id_group|escape:'intval'}" {if ($value.id_group == $group.id_group)}selected="selected"{/if}>{$group.title|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
    </div>
    
            
    <div class="col-lg-12 col-sm-12">
        <label class="control-label col-lg-3 col-sm-3">
        <span data-original-title="Select the chart Type" class="label-tooltip pull-right" data-toggle="tooltip" title="">Select Category</span>
        </label>
        <div class="col-lg-8 col-sm-8">
            {$categ|escape:'intval'}
        </div>
        <div class="clear">&nbsp;</div>
    </div>       
            
            
    <div class="clear">&nbsp;</div>
    <div class="clear">&nbsp;</div>
    <div class="panel-footer" align="center">
    <a class="btn btn-default pull-left inverse" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart"><i class="process-icon-back"></i> Back to List</a>
    <button type="submit" class="btn btn-default pull-right"><i class="process-icon-cogs"></i>Configure</button>
    </div>
    </fieldset>

</div>
    <br><br>

<div class="panel">
<fieldset>
    <legend>{l s='Configure Chart' mod='ilmsizechart'}</legend>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <input type="file" class="hide" name="ilmchartimg" id="ilmchartimg">
            <div class="dummyfile input-group">
		<span class="input-group-addon"><i class="icon-file"></i></span>
                <input type="text" readonly="" name="filename" class="disabled" id="ilmchartimg-name" value="{$imgname|escape:'intval'}">
		<span class="input-group-btn">
		<button class="btn btn-default" name="submitAddAttachments" type="button" id="ilmchartimg-selectbutton">
		<i class="icon-folder-open"></i> Choose a file
		</button>
		</span>
		</div>
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td><a href="javascript:void(0)" class="btn btn-danger" id="imgbtn"><i class="icon-trash"></i>Remove Image</a><br /><br /></td></tr>
    {$image|escape:'intval'}
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="center">
            {$table|escape:'intval'}
        </td>
    </tr>
</table><br><br>
    <div class="panel-footer" align="center">
    <a class="btn btn-default pull-left inverse" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=ilmsizechart"><i class="process-icon-back"></i> Back to List</a>
    <button type="submit" class="btn btn-default pull-right"><i class="process-icon-save"></i>Save Chart</button>
    </div>
</fieldset>
</div>
</form>
</div>
{literal}
    <script>
        jQuery(function(){
           jQuery('#ilmchartimg-selectbutton').click(function(){
               jQuery('#ilmchartimg').click();
           });
           jQuery('#ilmchartimg').change(function(){
                jQuery('#ilmchartimg-name').val(jQuery('#ilmchartimg').val());
           });
           jQuery('#imgbtn').click(function(){
               jQuery('#ilmchartimg-name').val('');
               jQuery('#imgrow').remove();
           });
        });
    </script>    
{/literal}