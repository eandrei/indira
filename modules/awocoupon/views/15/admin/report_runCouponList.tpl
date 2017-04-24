{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

<link type="text/css" rel="stylesheet" href="{$awo_uri}/media/css/bargraph.css" />
<link type="text/css" rel="stylesheet" href="{$awo_uri}/media/css/scrollheader.css" />
<script type="text/javascript" src="{$awo_uri}/media/js/scrollheader.js"></script>

<script language="javascript" type="text/javascript">
<!--
function submitform(task) {
	form = document.adminForm;
	form.task.value = (typeof(task) !== 'undefined') ? task : 'runreport';

	// Submit the form.
	if (typeof form.onsubmit == 'function') form.onsubmit();
	if (typeof form.fireEvent == "function") form.fireEvent('submit');
	form.submit();
}
//-->
</script>

<center>
<div><font size="5">{l s='Coupon List' mod='awocoupon'}</font></div>

<br><br>

{if $is_empty==''}
<!--<form action="{$awo_uri}/ajax.php" method="post" name="adminForm">-->
<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}">

	<table border="0">
	<tr><td><INPUT TYPE="image" onclick="submitform('exportreports');" NAME="submit" src="{$awo_uri}/media/img/excel.gif" border="0" alt="Export CSV">
			<font size="4">{l s='Coupon List' mod='awocoupon'}</font>
	</td></tr>
	<tr><td>{$arrstr.html}</td></tr>
	</table>
	<br><br>
	
	{$pagination}
	<br><br>

	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="report" />
	<input type="hidden" name="task" value="runreport" />
	<input type="hidden" name="rpt_labels" value="{$labels}" />
	<input type="hidden" name="rpt_columns" value="{$columns}" />
	<input type="hidden" name="report_type" value="{$report_type}" />
	<INPUT type="hidden" name="filename" value="coupon_list.csv">
	<INPUT type="hidden" name="page" value="">
	{$parameters}
</form>
{$arrstr.js}

{else}
<br><br><div style=""><b>{l s='No Data Exists' mod='awocoupon'}</b></div>
{/if}

