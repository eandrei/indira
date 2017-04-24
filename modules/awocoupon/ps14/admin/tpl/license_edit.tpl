{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}


<script language="javascript" type="text/javascript">
var str_confirm = "{l s='Are you sure you want to delete the items?' mod='awocoupon'}";

var $j = jQuery.noConflict();  // added so jquery does not conflict with mootools

$j(document).ready(function() {
});

function submitbutton(pressbutton) {

	var form = document.adminForm;
	form.task.value = pressbutton;

	if (pressbutton != 'delete') form.submit();
	else if(confirm(str_confirm)) form.submit();

	return;
}
</script>
<style>
table.admintable td.key { white-space:nowrap; width:auto; }
table.admintable td.key2 { background-color:#ffffff;}
table.admintable td input.readonly, table.admintable td textarea.readonly  { background-color:#ffffff; }
</style>

{$errors}

<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" onsubmit="return submitbutton();">
<br />
{if $row->expiration!="" && $row->expiration<$current_time}
	&nbsp;&nbsp;<input class="button" type="button" value="Update Expired License" onclick="submitbutton('updexpired');" />
{/if}
{if $row->local_key=="" && $row->license==""}
	&nbsp;&nbsp;<input class="button" type="button" value="Activate" onclick="submitbutton('activate');" />
{elseif $row->ispermanent!="yes"}
	&nbsp;&nbsp;<input class="button" type="button" value="Update Local Key" onclick="submitbutton('updlocalkey');" />
{/if}
{if $row->license!=""}
	&nbsp;&nbsp;<input class="button" type="button" value="Delete License" onclick="submitbutton('delete');" />
{/if}
<br /><br />
		

	<div class="width-100">
		<fieldset>{if $row->ispermanent=='yes'}<legend>{l s='Permanent License' mod='awocoupon'}</legend>{/if}
					
			<table class="admintable">
			<tr><td class="key"><label>{l s='Website' mod='awocoupon'}</label></td>
				<td><input type="text" size="75" name="website" value="{$row->website}" {if $row->ispermanent=='yes'}READONLY DISABLED class="readonly"{/if}></td>
			</tr>
			<tr><td class="key"><label>{l s='License' mod='awocoupon'}</label></td>
				<td><input type="text" size="75" name="license" value="{$row->license}" {if $row->ispermanent=='yes'}READONLY DISABLED class="readonly"{/if}></td>
			</tr>
			{if $row->license!=''}
			<tr valign="top"><td class="key"><label>{l s='Local Key' mod='awocoupon'}</label></td>
				<td><textarea rows=20 cols=130 name="local_key" {if $row->ispermanent=='yes'}READONLY DISABLED class="readonly"{/if}>{$row->local_key}</textarea></td>
			</tr>
			{/if}
			</table>
			
		</fieldset>
	</div>

	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="license" />
	<input type="hidden" name="task" value="" />
</form>
