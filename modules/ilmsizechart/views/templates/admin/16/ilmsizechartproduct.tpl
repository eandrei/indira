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
    <div class="panel">
        <h3><img src="{$baseURL|escape:'intval'}modules/ilmsizechart/img/ruler.png" />{l s='ILM Size Chart' mod='ilmsizechart'}</h3>
<table width="100%">
    <tr>
        <td>
            <label>Size List</label>
            {$sizes|escape:'intval'}<br><br>
        </td>
    </tr>
    <tr>
        <td align="center" id="ilmsizechart">
			{if $image}
			<img src="{$baseURL|escape:'intval'}modules/ilmsizechart/images/{$image|escape:'intval'}" /><br /><br />
            {/if}
            {$ilmtable|escape:'intval'}
        </td>
    </tr>
</table>

{if $version|substr:0:3 eq 1.6}
   <div class="panel-footer">
    <button class="btn btn-default pull-right" name="submitAddproduct" type="submit"><i class="process-icon-save"></i>Save</button>
	<button class="btn btn-default pull-right" name="submitAddproductAndStay" type="submit"><i class="process-icon-save"></i>Save and stay</button>
    </div>
{/if}
        
</div>
</div>