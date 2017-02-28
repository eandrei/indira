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
<script type="text/javascript">
{literal}
$('document').ready(function(){
	$('#ilm_size_chart').fancybox({
		'hideOnContentClick': false
	});
        $(".ilmsize table").delegate('td','mouseover mouseleave', function(e) {
    if (e.type == 'mouseover') {
      $(this).parent().addClass("hover");
      $("colgroup").eq($(this).index()).addClass("hover");
      $(".ilmcol").eq($(this).index()).addClass("hover");
    }
    else {
      $(this).parent().removeClass("hover");
      $("colgroup").eq($(this).index()).removeClass("hover");
      $(".ilmcol").eq($(this).index()).removeClass("hover");
    }
});
});

{/literal}
</script>

<a id="ilm_size_chart" href="#ilm_size_chart_form"><b><img src="{$baseURL|escape:'htmlall':'UTF-8'}modules/ilmsizechart/img/ruler.png" /> {l s='Size Chart' mod='ilmsizechart'}</b></a>
            
<div style="display: none;">
	<div id="ilm_size_chart_form">
            <h2 class="page-subheading">{l s='Size Chart' mod='ilmsizechart'}</h2>
<table width="100%">
    <tr>
        <td align="center">
            
            {if $image}
        <center>
            <img src="{$baseURL|escape:'htmlall':'UTF-8'}modules/ilmsizechart/images/{$image|escape:'htmlall':'UTF-8'}" /><br /><br />
        </center>
            {/if}
            <div class="ilmsize">
            {$ilmtable|escape:'intval'}
            </div>
        </td>
    </tr>
</table>
	</div>
</div>