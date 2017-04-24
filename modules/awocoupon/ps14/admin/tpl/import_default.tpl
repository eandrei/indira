{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

<br />

<script language="javascript" type="text/javascript">
<!--
jQuery(document).ready(function () {
	jQuery("#div_sample").width(jQuery("#div_form_content").width()-40);
})
jQuery(window).on('resize', function(){
	jQuery("#div_sample").width(jQuery("#div_form_content").width()-40);
});
//-->
</script>

<style>
.admintable tr.columnheaders td.key { text-align: center; background-color:#f4f4f4; }
.admintable tr.columnheaders td.key2 { font-weight;900;background-color:#bbbbbb;color:#ffffff;text-align: center; 	border-bottom: 1px solid #e9e9e9; border-right: 1px solid #e9e9e9; }
.admintable tr.columndata td { border:1px solid #cccccc; color:#777777; }
</style>


{$errors}


<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="div_form_content" class="width-100">
		<fieldset class="adminform"><legend>{l s='File' mod='awocoupon'}</legend>
			<div><input type="checkbox" value="1" name="exclude_first_row" {if $row->exclude_first_row == 1} CHECKED {/if}>{l s='Exclude the First Row' mod='awocoupon'}</div>
			<br />
			<div><input type="checkbox" value="1" name="store_none_errors" {if $row->store_none_errors == 1} CHECKED {/if}>{l s='Save all coupons with no errors, even with errors in the batch' mod='awocoupon'}</div>
			<div><input type="file" name="file" style="width:100%;"></div>
		</fieldset>
	</div>

	<br /><div class="margin-form"><input type="submit" value="   {l s='Upload' mod='awocoupon'}   " name="submit" class="button"></div>

	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="import" />
	<input type="hidden" name="task" value="store" />
</form>




<br /><br />

<fieldset class="adminform" style="width:97%;"><legend>{l s='CSV Spreadsheet Format' mod='awocoupon'}</legend>
	<div id="div_sample" style="border:1px solid #555;overflow-x:auto;background-color:#fff;">


		<table class="admintable">
		<tr class="columnheaders" valign="top">
			<td class="key" rowspan="2">{l s='ID' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Coupon Code' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Published' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Function Type' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Percent or Amount' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Discount Type' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Value' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Value Definition' mod='awocoupon'}</td>
			<td class="key" colspan="2">{l s='Number of Uses' mod='awocoupon'}</td>
			<td class="key" rowspan="2" colspan="2">{l s='Minimum Value' mod='awocoupon'}</td>
			<td class="key">{l s='Start Date' mod='awocoupon'}</td>
			<td class="key">{l s='Expiration' mod='awocoupon'}</td>
			<td class="key" colspan="2">{l s='Customers' mod='awocoupon'}</td>
			<td class="key" colspan="4">{l s='Asset' mod='awocoupon'}</td>
			<td class="key" colspan="4">{l s='Asset 2' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Exclude Products on Special' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Exclude Gift Certificate Products' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Admin Note' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Process Type' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Maximum Discount Qty' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Do not Mix Products' mod='awocoupon'}</td>
			<td class="key" rowspan="2">{l s='Automatically add to cart "Get Y" product' mod='awocoupon'}</td>
			<td class="key" colspan="3">{l s='Country/State' mod='awocoupon'}</td>
			<td class="key" colspan="2">{l s='Minimum Product Quantity' mod='awocoupon'}</td>
				<td class="key" rowspan="2">{l s='Description' mod='awocoupon'}</td>
				<td class="key" rowspan="2">{l s='Tags' mod='awocoupon'}</td>
		</tr>
		<tr class="columnheaders" valign="top">
			<td class="key">{l s='Total' mod='awocoupon'}</td>
			<td class="key">{l s='Per Customer' mod='awocoupon'}</td>
			<td class="key">(YYYYMMDD hhmmss)</td>
			<td class="key">(YYYYMMDD hhmmss)</td>
			<td class="key">{l s='Type' mod='awocoupon'}</td>
			<td class="key">{l s='ID' mod='awocoupon'}</td>
			<td class="key">{l s='Type' mod='awocoupon'}</td>
			<td class="key">{l s='Mode' mod='awocoupon'}</td>
			<td class="key">{l s='Number' mod='awocoupon'}</td>
			<td class="key">{l s='ID' mod='awocoupon'}</td>
			<td class="key">{l s='Type' mod='awocoupon'}</td>
			<td class="key">{l s='Mode' mod='awocoupon'}</td>
			<td class="key">{l s='Number' mod='awocoupon'}</td>
			<td class="key">{l s='ID' mod='awocoupon'}</td>
			<td class="key">{l s='Type' mod='awocoupon'}</td>
			<td class="key">{l s='Country' mod='awocoupon'}</td>
			<td class="key">{l s='State' mod='awocoupon'}</td>
				<td class="key">{l s='Type' mod='awocoupon'}</td>
				<td class="key">{l s='Number' mod='awocoupon'}</td>
		</tr>
		<tr class="columnheaders" >
			<td class="key2">A</td>
			<td class="key2">B</td>
			<td class="key2">C</td>
			<td class="key2">D</td>
			<td class="key2">E</td>
			<td class="key2">F</td>
			<td class="key2">G</td>
			<td class="key2">H</td>
			<td class="key2">I</td>
			<td class="key2">J</td>
			<td class="key2">K</td>
			<td class="key2">L</td>
			<td class="key2">M</td>
			<td class="key2">N</td>
			<td class="key2">O</td>
			<td class="key2">P</td>
			<td class="key2">Q</td>
			<td class="key2">R</td>
			<td class="key2">S</td>
			<td class="key2">T</td>
			<td class="key2">U</td>
			<td class="key2">V</td>
			<td class="key2">W</td>
			<td class="key2">X</td>
			<td class="key2">Y</td>
			<td class="key2">Z</td>
			<td class="key2">AA</td>
			<td class="key2">AB</td>
			<td class="key2">AC</td>
			<td class="key2">AD</td>
			<td class="key2">AE</td>
			<td class="key2">AF</td>
			<td class="key2">AG</td>
			<td class="key2">AH</td>
				<td class="key2">AI</td>
				<td class="key2">AJ</td>
				<td class="key2">AK</td>
				<td class="key2">AL</td>
		</tr>
		<tr class="columndata">
			<td>1</td>
			<td>RTC45</td>
			<td>{l s='Published' mod='awocoupon'}</td>
			<td>{l s='Coupon' mod='awocoupon'}</td>
			<td>{l s='Percent' mod='awocoupon'}</td>
			<td>{l s='Overall' mod='awocoupon'}</td>
			<td>10</td>
			<td>&nbsp;</td>
			<td>100</td>
			<td>2</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>20100810</td>
			<td>{l s='Customer' mod='awocoupon'}</td>
			<td>5,7,9</td>
			<td NOWRAP>{l s='Product' mod='awocoupon'}</td>
			<td NOWRAP>{l s='Include' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td>12,24</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>&nbsp;</td>
			<td>{l s='No' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td NOWRAP>{l s='Include' mod='awocoupon'}</td>
			<td>223</td>
			<td>&nbsp;</td>
				<td>{l s='Overall' mod='awocoupon'}</td>
				<td>3</td>
				<td>&nbsp;</td>
				<td>tag1,tag2</td>
		</tr>
		<tr class="columndata">
			<td>2</td>
			<td>A0E8</td>
			<td>{l s='Unpublished' mod='awocoupon'}</td>
			<td nowrap>{l s='Coupon' mod='awocoupon'}</td>
			<td>{l s='Amount' mod='awocoupon'}</td>
			<td>{l s='Specific' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td nowrap>2-10;4-12;</td>
			<td nowrap>&nbsp;</td>
			<td>1</td>
			<td nowrap>{l s='Overall' mod='awocoupon'}</td>
			<td>100</td>
			<td>20100510 022305</td>
			<td>&nbsp;</td>
			<td>{l s='Shopper Group' mod='awocoupon'}</td>
			<td>1</td>
			<td>{l s='Category' mod='awocoupon'}</td>
			<td>{l s='Exclude' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td>25,8,44</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>{l s='Yes' mod='awocoupon'}</td>
			<td>{l s='No' mod='awocoupon'}</td>
			<td>my notes</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>{l s='Exclude' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td>7,25,32,14</td>
				<td>{l s='Specific' mod='awocoupon'}</td>
				<td>2</td>
				<td>my front end description</td>
				<td>&nbsp;</td>
		</tr>
		<tr class="columndata">
			<td>3</td>
			<td>ZJ72M</td>
			<td>{l s='Published' mod='awocoupon'}</td>
			<td>{l s='Shipping' mod='awocoupon'}</td>
			<td>{l s='Percent' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td>100</td>
			<td>&nbsp;</td>
			<td nowrap>200</td>
			<td>&nbsp;</td>
			<td nowrap>{l s='Specific' mod='awocoupon'}</td>
			<td>50</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="center">---</td>
			<td nowrap>{l s='Include' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td >7,24,3</td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>&nbsp;</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td nowrap>{l s='Include' mod='awocoupon'}</td>
			<td>153,24</td>
			<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
		</tr>
		<tr class="columndata">
			<td>4</td>
			<td>MBSI97</td>
			<td>{l s='Published' mod='awocoupon'}</td>
			<td>{l s='Gift Certificate' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>50</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>20100110</td>
			<td>20100810 160000</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>{l s='Yes' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
				<td>front end description</td>
				<td>tag1,tag2</td>
		</tr>
		<tr class="columndata">
			<td>5</td>
			<td>S4a2Bp</td>
			<td>{l s='Published' mod='awocoupon'}</td>
			<td>{l s='Parent' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">50</td>
			<td align="center">&nbsp;</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">7,24,56,23</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>note for parent coupon</td>
			<td>{l s='All that apply' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>{l s='Include' mod='awocoupon'}</td>
			<td>&nbsp;</td>
			<td>89</td>
			<td align="center">---</td>
			<td align="center">---</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
		</tr>
		<tr class="columndata">
			<td>6</td>
			<td>SHIP283</td>
			<td>{l s='Published' mod='awocoupon'}</td>
			<td>{l s='Shipping' mod='awocoupon'}</td>
			<td>{l s='Percent' mod='awocoupon'}</td>
			<td>{l s='Specific' mod='awocoupon'}</td>
			<td>100</td>
			<td>&nbsp;</td>
			<td nowrap></td>
			<td></td>
			<td nowrap>1000</td>
			<td>50</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td nowrap>&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td ></td>
			<td nowrap>{l s='Shipping' mod='awocoupon'}</td>
			<td nowrap>{l s='Exclude' mod='awocoupon'}</td>
			<td align="center">---</td>
			<td>2,3,4,5</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td align="center">---</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
		</tr>
		<tr class="columndata"><td colspan="12" style="border:0;">......</td></tr>
		</table>
	</div>
</fieldset>





