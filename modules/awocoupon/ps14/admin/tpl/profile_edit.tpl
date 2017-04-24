{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

<script type="text/javascript">
	var iso = "{$isoTinyMCE}" ;
	var pathCSS = "{$theme_path}" ;
	var ad = "{$tiny_ad}";
	var pos_select = 0;
	function loadTab(id) { return; }
</script>
<script type="text/javascript" src="{$base_url}js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="{$base_url}js/tinymce.inc.js"></script>

<script src="{$base_url}/js/tabpane.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="{$base_url}/css/tabpane.css" />

<script language="javascript" type="text/javascript">
<!--
var $j = jQuery.noConflict();  // added so jquery does not conflict with mootools

$j(document).ready(function() {
	message_type_change(true);
	
	var form = document.adminForm;
	
	{if $row->couponcode_font_color!=''}form.couponcode_font_color.value = '{$row->couponcode_font_color}';{/if}


	{if $row->couponvalue_font_color!=''}form.couponvalue_font_color.value = '{$row->couponvalue_font_color}';{/if}
	{if $row->expiration_font_color!=''}form.expiration_font_color.value = '{$row->expiration_font_color}';{/if}
	{if $row->freetext1_font_color!=''}form.freetext1_font_color.value = '{$row->freetext1_font_color}';{/if}
	{if $row->freetext2_font_color!=''}form.freetext2_font_color.value = '{$row->freetext2_font_color}';{/if}
	{if $row->freetext3_font_color!=''}form.freetext3_font_color.value = '{$row->freetext3_font_color}';{/if}
	{foreach from=$row->imgplugin key=k item=r}
		{foreach from=$r key=k2 item=r2}
			{if !empty($r2->font)}form.elements["imgplugin[{$k}][{$k2}][font]"].value = "{$r2->font}";{/if}
			{if !empty($r2->text)}form.elements["imgplugin[{$k}][{$k2}][text]"].value = "{$r2->text}";{/if}
			{if !empty($r2->align)}form.elements["imgplugin[{$k}][{$k2}][align]"].value = "{$r2->align}";{/if}
			{if !empty($r2->font_color)}form.elements["imgplugin[{$k}][{$k2}][font_color]"].value = "{$r2->font_color}";{/if}
		{/foreach}
	{/foreach}
	
	checkimage();

	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k}] = {
			id_lang: {$language.id_lang},
			iso_code: '{$language.iso_code|escape:'quotes'}',
			name: '{$language.name|escape:'quotes'}'
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default});

});


var str_coupon_code = "{l s='Coupon Code' mod='awocoupon'}";
var str_coupon_value = "{l s='Value' mod='awocoupon'}";
var str_expiration = "{l s='Expiration' mod='awocoupon'}";
var str_free_text = "{l s='Free text' mod='awocoupon'}";
var str_padding = "{l s='Padding' mod='awocoupon'}";
var str_y_axis = "{l s='Y-Axis' mod='awocoupon'}";
var str_font_size = "{l s='Font size' mod='awocoupon'}";
var str_message_type = "{l s='Message Type' mod='awocoupon'}";
var str_image = "{l s='Image' mod='awocoupon'}";
var str_font = "{l s='Font' mod='awocoupon'}";
var str_title = "{l s='Title' mod='awocoupon'}";

var str_int_error = "{l s='positive integer is required' mod='awocoupon'}";
var str_selection_error = "{l s='please make a selection' mod='awocoupon'}";
var str_input_error = "{l s='please enter an input' mod='awocoupon'}";

function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function submitbutton(pressbutton) {
	var form = document.adminForm;

	var err = '';
	
	if($j.trim(form.title.value) == '') err += '\n'+str_title+': '+str_input_error;
	if(form.message_type.value=='') err += '\n'+str_message_type+': '+str_selection_error;
	else {
		if(form.message_type.value == 'text');
		else if(form.message_type.value == 'html') {
		
			//if(form.image.value=='') err += '\n'+str_image+': '+str_selection_error;
			
			if(form.image.selectedIndex != 0) {

			
				if(form.couponcode_align.value!='C' && (!isUnsignedInteger($j.trim(form.couponcode_padding.value)) || $j.trim(form.couponcode_padding.value)<1)) err += '\n'+str_coupon_code+'=>'+str_padding+': '+str_int_error;
				if(!isUnsignedInteger($j.trim(form.couponcode_y.value)) || $j.trim(form.couponcode_y.value)<1) err += '\n'+str_coupon_code+'=>'+str_y_axis+': '+str_int_error;
				if(form.couponcode_font.value=='') err += '\n'+str_coupon_code+'/'+str_font+': '+str_selection_error;
				if(!isUnsignedInteger($j.trim(form.couponcode_font_size.value)) || $j.trim(form.couponcode_font_size.value)<1) err += '\n'+str_coupon_code+'=>'+str_font_size+': '+str_int_error;

				if(form.couponvalue_align.value!='C' && (!isUnsignedInteger($j.trim(form.couponvalue_padding.value)) || $j.trim(form.couponvalue_padding.value)<1)) err += '\n'+str_coupon_value+'=>'+str_padding+': '+str_int_error;
				if(!isUnsignedInteger($j.trim(form.couponvalue_y.value)) || $j.trim(form.couponvalue_y.value)<1) err += '\n'+str_coupon_value+'=>'+str_y_axis+': '+str_int_error;
				if(form.couponvalue_font.value=='') err += '\n'+str_coupon_value+'/'+str_font+': '+str_selection_error;
				if(!isUnsignedInteger($j.trim(form.couponvalue_font_size.value)) || $j.trim(form.couponvalue_font_size.value)<1) err += '\n'+str_coupon_value+'=>'+str_font_size+': '+str_int_error;
				
				if($j.trim(form.expiration_text.value)!='') {
					if(form.expiration_align.value!='C' && (!isUnsignedInteger($j.trim(form.expiration_padding.value)) || $j.trim(form.expiration_padding.value)<1)) err += '\n'+str_expiration+'=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($j.trim(form.expiration_y.value)) || $j.trim(form.expiration_y.value)<1) err += '\n'+str_expiration+'=>'+str_y_axis+': '+str_int_error;
					if(form.expiration_font.value=='') err += '\n'+str_expiration+'=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($j.trim(form.expiration_font_size.value)) || $j.trim(form.expiration_font_size.value)<1) err += '\n'+str_expiration+'=>'+str_font_size+': '+str_int_error;
				}
				
				if($j.trim(form.freetext1_text.value)!='') {
					if(form.freetext1_align.value!='C' && (!isUnsignedInteger($j.trim(form.freetext1_padding.value)) || $j.trim(form.freetext1_padding.value)<1)) err += '\n'+str_free_text+' 1=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($j.trim(form.freetext1_y.value)) || $j.trim(form.freetext1_y.value)<1) err += '\n'+str_free_text+' 1=>'+str_y_axis+': '+str_int_error;
					if(form.freetext1_font.value=='') err += '\n'+str_free_text+' 1=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($j.trim(form.freetext1_font_size.value)) || $j.trim(form.freetext1_font_size.value)<1) err += '\n'+str_free_text+' 1=>'+str_font_size+': '+str_int_error;
				}
				
				if($j.trim(form.freetext2_text.value)!='') {
					if(form.freetext2_align.value!='C' && (!isUnsignedInteger($j.trim(form.freetext2_padding.value)) || $j.trim(form.freetext2_padding.value)<1)) err += '\n'+str_free_text+' 2=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($j.trim(form.freetext2_y.value)) || $j.trim(form.freetext2_y.value)<1) err += '\n'+str_free_text+' 2=>'+str_y_axis+': '+str_int_error;
					if(form.freetext2_font.value=='') err += '\n'+str_free_text+' 2=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($j.trim(form.freetext2_font_size.value)) || $j.trim(form.freetext2_font_size.value)<1) err += '\n'+str_free_text+' 2=>'+str_font_size+': '+str_int_error;
				}
				
				if($j.trim(form.freetext3_text.value)!='') {
					if(form.freetext3_align.value!='C' && (!isUnsignedInteger($j.trim(form.freetext3_padding.value)) || $j.trim(form.freetext3_padding.value)<1)) err += '\n'+str_free_text+' 3=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($j.trim(form.freetext3_y.value)) || $j.trim(form.freetext3_y.value)<1) err += '\n'+str_free_text+' 3=>'+str_y_axis+': '+str_int_error;
					if(form.freetext3_font.value=='') err += '\n'+str_free_text+' 3=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($j.trim(form.freetext3_font_size.value)) || $j.trim(form.freetext3_font_size.value)<1) err += '\n'+str_free_text+' 3=>'+str_font_size+': '+str_int_error;
				}
				{foreach from=$row->imgplugin key=k item=r}
					{foreach from=$r key=k2 item=r2}
						if($.trim(form.elements["imgplugin[{$k}][{$k2}][text]"].value)!='') {
							if(form.elements["imgplugin[{$k}][{$k2}][align]"].value!='C' && (!isUnsignedInteger($.trim(form.elements["imgplugin[{$k}][{$k2}][padding]"].value)) || $.trim(form.elements["imgplugin[{$k}][{$k2}][padding]"].value)<1)) err += '\n{if isset($r2->title)}{$r2->title|escape}{/if}=>'+str_padding+': '+str_int_error;
							if(!isUnsignedInteger($.trim(form.elements["imgplugin[{$k}][{$k2}][y]"].value)) || $.trim(form.elements["imgplugin[{$k}][{$k2}][y]"].value)<1) err += '\n{if isset($r2->title)}{$r2->title|escape}{/if}=>'+str_y_axis+': '+str_int_error;
							{if empty($r2->is_ignore_font)}if(form.elements["imgplugin[{$k}][{$k2}][font]"].value=='') err += '\n{if isset($r2->title)}{$r2->title|escape}{/if}=>'+str_font+': '+str_selection_error;{/if}
							{if empty($r2->is_ignore_font_size)}if(!isUnsignedInteger($.trim(form.elements["imgplugin[{$k}][{$k2}][font_size]"].value)) || $.trim(form.elements["imgplugin[{$k}][{$k2}][font_size]"].value)<1) err += '\n{if isset($r2->title)}{$r2->title|escape}{/if}=>'+str_font_size+': '+str_int_error;{/if}
						}
					{/foreach}
				{/foreach}
			
			}
		}
	}
	if(err != '') {
		alert(err);
		return false;
	}		
		

	return true;

}






function resetall() {
	var form = document.adminForm;
	form.image.selectedIndex = 0;
	
	form.couponcode_padding.value = '';
	form.couponcode_y.value = '';
	form.couponcode_font_size.value = '';
	form.couponcode_align.selectedIndex = 0; 
	form.couponcode_font.selectedIndex = 0; 
	form.couponcode_font_color.selectedIndex = 0; 
	
	form.couponvalue_padding.value = '';
	form.couponvalue_y.value = '';
	form.couponvalue_font_size.value = '';
	form.couponvalue_align.selectedIndex = 0; 
	form.couponvalue_font.selectedIndex = 0; 
	form.couponvalue_font_color.selectedIndex = 0; 
	
	form.expiration_text.value = '';
	form.expiration_padding.value = '';
	form.expiration_y.value = '';
	form.expiration_font_size.value = '';
	form.expiration_align.selectedIndex = 0; 
	form.expiration_font.selectedIndex = 0; 
	form.expiration_font_color.selectedIndex = 0; 
	
	form.freetext1_text.value = '';
	form.freetext1_padding.value = '';
	form.freetext1_y.value = '';
	form.freetext1_font_size.value = '';
	form.freetext1_align.selectedIndex = 0; 
	form.freetext1_font.selectedIndex = 0; 
	form.freetext1_font_color.selectedIndex = 0; 
	
	form.freetext2_text.value = '';
	form.freetext2_padding.value = '';
	form.freetext2_y.value = '';
	form.freetext2_font_size.value = '';
	form.freetext2_align.selectedIndex = 0; 
	form.freetext2_font.selectedIndex = 0; 
	form.freetext2_font_color.selectedIndex = 0; 
	
	form.freetext3_text.value = '';
	form.freetext3_padding.value = '';
	form.freetext3_y.value = '';
	form.freetext3_font_size.value = '';
	form.freetext3_align.selectedIndex = 0; 
	form.freetext3_font.selectedIndex = 0; 
	form.freetext3_font_color.selectedIndex = 0; 
	

	{foreach from=$row->imgplugin key=k item=r}
		{foreach from=$r key=k2 item=r2}
		
		form.elements["imgplugin[{$k}][{$k2}][text]"].value = '';
		form.elements["imgplugin[{$k}][{$k2}][padding]"].value = '';
		form.elements["imgplugin[{$k}][{$k2}][y]"].value = '';
		form.elements["imgplugin[{$k}][{$k2}][font_size]"].value = '';
		form.elements["imgplugin[{$k}][{$k2}][align]"].selectedIndex = 0; 
		form.elements["imgplugin[{$k}][{$k2}][font]"].selectedIndex = 0; 
		form.elements["imgplugin[{$k}][{$k2}][font_color]"].selectedIndex = 0; 
		
		{/foreach}
	{/foreach}
	
	//form.email_body.value = ''; 
	{foreach from=$languages item=language}
		form.email_body_{$language.id_lang|intval}.value = ''; 
	{/foreach}
	
		
	hideall();
}
function hideall() {
	jQuery('#tbl_image,#tbl_html,#tbl_pdf,#tbl_text, .disabled_text_emails').hide();
}
function message_type_change(is_edit) {
	var form = document.adminForm;
	
	is_edit = (is_edit == undefined) ? false : is_edit;
	if(!is_edit) resetall();
	else hideall();
	
	if(form.message_type.value == 'text') {
		jQuery('#tbl_text, .disabled_text_emails').show();
	} else if(form.message_type.value == 'html') {
		jQuery('#tbl_image,#tbl_html,#tbl_pdf').show();
	}
}
function generate_preview() {
	if(submitbutton('previewprofile')) {
		var form = document.adminForm;
		var str = '';
			
		str += '&image='+encodeURIComponent(form.image.value);
		str += '&code='+encodeURIComponent(form.couponcode_align.value+'|'+form.couponcode_padding.value+'|'+form.couponcode_y.value+'|'+form.couponcode_font.value+'|'+form.couponcode_font_size.value+'|'+form.couponcode_font_color.value);
		str += '&value='+encodeURIComponent(form.couponvalue_align.value+'|'+form.couponvalue_padding.value+'|'+form.couponvalue_y.value+'|'+form.couponvalue_font.value+'|'+form.couponvalue_font_size.value+'|'+form.couponvalue_font_color.value);
		if($j.trim(form.expiration_text.value)!='')
			str += '&expiration='+encodeURIComponent(form.expiration_text.value+'|'+form.expiration_align.value+'|'+form.expiration_padding.value+'|'+form.expiration_y.value+'|'+form.expiration_font.value+'|'+form.expiration_font_size.value+'|'+form.expiration_font_color.value);
		if($j.trim(form.freetext1_text.value)!='')
			str += '&freetext1='+encodeURIComponent(form.freetext1_text.value+'|'+form.freetext1_align.value+'|'+form.freetext1_padding.value+'|'+form.freetext1_y.value+'|'+form.freetext1_font.value+'|'+form.freetext1_font_size.value+'|'+form.freetext1_font_color.value);
		if($j.trim(form.freetext2_text.value)!='')
			str += '&freetext2='+encodeURIComponent(form.freetext2_text.value+'|'+form.freetext2_align.value+'|'+form.freetext2_padding.value+'|'+form.freetext2_y.value+'|'+form.freetext2_font.value+'|'+form.freetext2_font_size.value+'|'+form.freetext2_font_color.value);
		if($j.trim(form.freetext3_text.value)!='')
			str += '&freetext3='+encodeURIComponent(form.freetext3_text.value+'|'+form.freetext3_align.value+'|'+form.freetext3_padding.value+'|'+form.freetext3_y.value+'|'+form.freetext3_font.value+'|'+form.freetext3_font_size.value+'|'+form.freetext3_font_color.value);
		{foreach from=$row->imgplugin key=k item=r}
			{foreach from=$r key=k2 item=r2}
				if($.trim(form.elements["imgplugin[{$k}][{$k2}][text]"].value)!='')
					str += '&imgplugin[{$k}][{$k2}]='+encodeURIComponent(form.elements["imgplugin[{$k}][{$k2}][text]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][align]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][padding]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][y]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font_size]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font_color]"].value);
			{/foreach}
		{/foreach}

		$j(document).ready(function () {
				$j.fancybox({
					"width"				: "75%",
					"height"			: "75%",
					"autoScale"     	: false,
					"transitionIn"		: "none",
					"transitionOut"		: "none",
					'type': 'iframe',
					'href': "{$ajax_url}?task=previewprofileEdit"+str
				});
		});		

		//	SqueezeBox.setContent('iframe',"{$ajax_url}?task=previewprofileEdit"+str);
	}

}

function checkimage() {
	elem = document.adminForm.image;
	if(elem.selectedIndex == 0) document.getElementById('image_properties').style.display = 'none';
	else document.getElementById('image_properties').style.display = '';
}
//-->
</script>

<style>
table.admintable td.top { width:auto; text-align:center; }
</style>

<!--
<input type="hidden" name="tabs" id="tabs" value="0" />
<div class="tab-pane" id="tabPane1">

		<div class="tab-page" id="step1">
				<h4 class="tab">.1 sdklklsd</h4>
				tester
		</div>
		<div class="tab-page" id="step2">
				<h4 class="tab">2. Images (8)</h4>
				and another
		</div>
	</div>-->
{$errors}

<br /><br />

<form method="post" action="{$smarty.server.REQUEST_URI}" name="adminForm" id="adminForm" onsubmit="return submitbutton();">

	<input type="hidden" name="tabs" id="tabs" value="0" />
	<div class="tab-pane" id="tabPane1">

		<div class="tab-page" id="awocoupon_block_general">
			<h4 class="tab">{l s='General' mod='awocoupon'}</h4>

			<fieldset><legend>{l s='General' mod='awocoupon'}</legend>
			<table class="admintable">
				<tr><td class="key key2"><label>{l s='Title' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="title" value="{$row->title}" maxlength="255"></td>
				</tr>
				<tr><td class="key key2"><label>{l s='From Name' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="from_name" value="{$row->from_name}" maxlength="255"></td>
				</tr>
				<tr><td class="key key2"><label>{l s='From Email' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="from_email" value="{$row->from_email}" maxlength="255"></td>
				</tr>
				<tr valign="top"><td class="key key2"><label>{l s='Email Subject' mod='awocoupon'}</label></td>
					<td><!--<input type="text" size="60" name="email_subject" value="{$row->email_subject}" maxlength="255">-->
						<div class="translatable">
						{foreach from=$languages item=language}
							<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
								<input type="text" size="60" name="email_subject_{$language.id_lang|intval}" value="{if isset($row->languages[$language.id_lang|intval]->email_subject)}{$row->languages[$language.id_lang|intval]->email_subject}{/if}" maxlength="255">
							</div>
						{/foreach}
						</div>
					</td>
				</tr>
				<tr><td class="key key2"><label>{l s='Bcc Admin' mod='awocoupon'}</label></td>
					<td><input type="checkbox" size="60" name="bcc_admin" {if $row->bcc_admin!=''} CHECKED {/if} value="1"></td>
				</tr>
				<tr><td class="key key2"><label>{l s='Message Type' mod='awocoupon'}</label></td>
					<td>{$lists.message_type}</td>
				</tr>
			</table>

			</fieldset>
		</div>

		<div class="tab-page" id="awocoupon_block_image">
			<h4 class="tab">{l s='Image' mod='awocoupon'}</h4>
			

			<fieldset><legend>{l s='Image' mod='awocoupon'}</legend>

			<div class="disabled_text_emails">{l s='Disabled for text emails' mod='awocoupon'}</div>
			<table class="admintable" id="tbl_image" style="display:none;">
			<tr><td class="key" colspan="2">{l s='Image' mod='awocoupon'} &nbsp; &nbsp;{$lists.image}
				&nbsp;&nbsp;&nbsp;<input type="button" onclick="generate_preview();" value="{l s='Preview' mod='awocoupon'}"></td></tr>
			<tr><td colspan="2">
			<table bgcolor="#ffffff" id="image_properties" style="display:none;">
			<tr>
				<td class="key top">{l s='Description' mod='awocoupon'}</td>
				<td class="key top">{l s='Text' mod='awocoupon'}</td>
				<td class="key top">{l s='Align' mod='awocoupon'}</td>
				<td class="key top">{l s='Padding' mod='awocoupon'}</td>
				<td class="key top">{l s='Y-Axis' mod='awocoupon'}</td>
				<td class="key top">{l s='Font' mod='awocoupon'}</td>
				<td class="key top">{l s='Font Size' mod='awocoupon'}</td>
				<td class="key top">{l s='Font Color' mod='awocoupon'}</td>
			</tr>
			<tr><td class="key">{l s='Coupon Code' mod='awocoupon'}</td>
				<td>---</td>
				<td>{$lists.couponcode_align}</td>
				<td><input type="text" size="5" name="couponcode_padding" value="{$row->couponcode_padding}"></td>
				<td><input type="text" size="5" name="couponcode_y" value="{$row->couponcode_y}"></td>
				<td>{$lists.couponcode_font}</td>
				<td><input type="text" size="5" name="couponcode_font_size" value="{$row->couponcode_font_size}"></td>
				<td>{$lists.couponcode_font_color}</td>
			</tr>
			<tr><td class="key">{l s='Value' mod='awocoupon'}</td>
				<td>---</td>
				<td>{$lists.couponvalue_align}</td>
				<td><input type="text" size="5" name="couponvalue_padding" value="{$row->couponvalue_padding}"></td>
				<td><input type="text" size="5" name="couponvalue_y" value="{$row->couponvalue_y}"></td>
				<td>{$lists.couponvalue_font}</td>
				<td><input type="text" size="5" name="couponvalue_font_size" value="{$row->couponvalue_font_size}"></td>
				<td>{$lists.couponvalue_font_color}</td>
			</tr>
			<tr><td class="key">{l s='Expiration' mod='awocoupon'}</td>
				<td>{$lists.expiration_text}</td>
				<td>{$lists.expiration_align}</td>
				<td><input type="text" size="5" name="expiration_padding" value="{$row->expiration_padding}"></td>
				<td><input type="text" size="5" name="expiration_y" value="{$row->expiration_y}"></td>
				<td>{$lists.expiration_font}</td>
				<td><input type="text" size="5" name="expiration_font_size" value="{$row->expiration_font_size}"></td>
				<td>{$lists.expiration_font_color}</td>
			</tr>
			<tr><td class="key">{l s='Free Text' mod='awocoupon'} 1</td>
				<td><input type="text"  name="freetext1_text" value="{$row->freetext1_text}"></td>
				<td>{$lists.freetext1_align}</td>
				<td><input type="text" size="5" name="freetext1_padding" value="{$row->freetext1_padding}"></td>
				<td><input type="text" size="5" name="freetext1_y" value="{$row->freetext1_y}"></td>
				<td>{$lists.freetext1_font}</td>
				<td><input type="text" size="5" name="freetext1_font_size" value="{$row->freetext1_font_size}"></td>
				<td>{$lists.freetext1_font_color}</td>
			</tr>
			<tr><td class="key">{l s='Free Text' mod='awocoupon'} 2</td>
				<td><input type="text"  name="freetext2_text" value="{$row->freetext2_text}"></td>
				<td>{$lists.freetext2_align}</td>
				<td><input type="text" size="5" name="freetext2_padding" value="{$row->freetext2_padding}"></td>
				<td><input type="text" size="5" name="freetext2_y" value="{$row->freetext2_y}"></td>
				<td>{$lists.freetext2_font}</td>
				<td><input type="text" size="5" name="freetext2_font_size" value="{$row->freetext2_font_size}"></td>
				<td>{$lists.freetext2_font_color}</td>
			</tr>
			<tr><td class="key">{l s='Free Text' mod='awocoupon'} 3</td>
				<td><input type="text"  name="freetext3_text" value="{$row->freetext3_text}"></td>
				<td>{$lists.freetext3_align}</td>
				<td><input type="text" size="5" name="freetext3_padding" value="{$row->freetext3_padding}"></td>
				<td><input type="text" size="5" name="freetext3_y" value="{$row->freetext3_y}"></td>
				<td>{$lists.freetext3_font}</td>
				<td><input type="text" size="5" name="freetext3_font_size" value="{$row->freetext3_font_size}"></td>
				<td>{$lists.freetext3_font_color}</td>
			</tr>
					{foreach from=$row->imgplugin key=k item=r}
						{foreach from=$r key=k2 item=r2}
							<tr><td class="key">{if isset($r2->title)}{$r2->title}{/if} <input type="hidden" name="imgplugin[{$k}][{$k2}][title]" value="{if isset($r2->title)}{$r2->title}{/if}" /></td>
								<td>{if isset($r2->text_html)}{$r2->text_html|replace:'{text_name}':"imgplugin[`$k`][`$k2`][text]"}{/if} <input type="hidden" name="imgplugin[{$k}][{$k2}][text_html]" value="{if isset($r2->text_html)}{$r2->text_html|escape:'htmlall'}{/if}" /></td>
								<td>{$lists.{"`$k`_`$k2`_align"}}</td>
								<td><input type="text" size="5" name="imgplugin[{$k}][{$k2}][padding]" value="{if isset($r2->padding)}{$r2->padding}{/if}"></td>
								<td><input type="text" size="5" name="imgplugin[{$k}][{$k2}][y]" value="{$r2->y}"></td>
								<td>{$lists.{"`$k`_`$k2`_font"}}{if !empty($r2->is_ignore_font)}<input type="hidden" name="imgplugin[{$k}][{$k2}][is_ignore_font]" value="1" />{/if}</td>
								<td><input type="text" size="5" name="imgplugin[{$k}][{$k2}][font_size]" value="{if isset($r2->font_size)}{$r2->font_size}{/if}">{if !empty($r2->is_ignore_font_size)}<input type="hidden" name="imgplugin[{$k}][{$k2}][is_ignore_font_size]" value="1" />{/if}</td>
								<td>{$lists.{"`$k`_`$k2`_font_color"}}{if !empty($r2->is_ignore_font_color)}<input type="hidden" name="imgplugin[{$k}][{$k2}][is_ignore_font_color]" value="1" />{/if}</td>
							</tr>
						{/foreach}
					{/foreach}
			</table>


			</td></tr>
			</table>
			</fieldset>
		</div>

		<div class="tab-page" id="awocoupon_block_pdf">
			<h4 class="tab">{l s='PDF' mod='awocoupon'}</h4>

			<fieldset><legend>{l s='PDF' mod='awocoupon'}</legend>

			<div class="disabled_text_emails">{l s='Disabled for text emails' mod='awocoupon'}</div>
			
			<table class="admintable" id="tbl_pdf" style="display:none;">
			<tr><td>{l s='Activate' mod='awocoupon'}</td><td>{$lists.is_pdf}</td></tr>
			<tr valign="top">
				<td>{l s='Header' mod='awocoupon'}</td>
				<td>
					<div class="translatable">
					{foreach from=$languages item=language}
						<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
							<textarea class="rte" cols="100" rows="10" id="pdf_header_{$language.id_lang|intval}" name="pdf_header_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_header)}{$row->languages[$language.id_lang|intval]->pdf_header}{/if}</textarea>
						</div>
					{/foreach}
					</div>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr valign="top">
				<td>{l s='Body' mod='awocoupon'}</td>
				<td>
					<div class="translatable">
					{foreach from=$languages item=language}
						<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
							<textarea class="rte" cols="100" rows="20" id="pdf_body_{$language.id_lang|intval}" name="pdf_body_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_body)}{$row->languages[$language.id_lang|intval]->pdf_body}{/if}</textarea>
						</div>
					{/foreach}
					</div>
				</td>
				<td><br /><br /><br /><br />
				
				<div style="padding:10px;">
					<div><b>{l s='Tags' mod='awocoupon'}</b></div>
					{literal}
						<div>{store_name}</div>
						<div>{siteurl}</div>
						<div>{vouchers}</div>
						<div>{image_embed}</div>
						<div>{purchaser_first_name}</div>
						<div>{purchaser_last_name}</div>
						<div>{recipient_name}</div>
						<div>{recipient_email}</div>
						<div>{recipient_message}</div>
						<div>{today_date}</div>
						<div>{order_id}</div>
						<div>{order_date}</div>
						<div>{order_total}</div>
						<div>{product_name}</div>
					{/literal}
				</div>

			</td></tr>
			<tr valign="top">
				<td>{l s='Footer' mod='awocoupon'}</td>
				<td>
					<div class="translatable">
					{foreach from=$languages item=language}
						<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
							<textarea class="rte" cols="100" rows="10" id="pdf_footer_{$language.id_lang|intval}" name="pdf_footer_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_footer)}{$row->languages[$language.id_lang|intval]->pdf_footer}{/if}</textarea>
						</div>
					{/foreach}
					</div>
				</td>
				<td>&nbsp;</td>
			</tr>

			</table>
			
			
			</fieldset>
			
		</div>
		

		<div class="tab-page" id="awocoupon_block_email">
			<h4 class="tab">{l s='Email' mod='awocoupon'}</h4>
			<fieldset><legend>{l s='Email' mod='awocoupon'}</legend>

			<table class="admintable" id="tbl_text" style="display:none;">
				<tr valign="top"><td class="key key2"><label>{l s='Email Body' mod='awocoupon'}</label></td>
					<td><!--<textarea cols="45" rows="10" name="email_body" >{$row->email_body}</textarea>-->
						<div class="translatable">
						{foreach from=$languages item=language}
							<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
								<textarea cols="45" rows="10" name="email_body_{$language.id_lang|intval}" >{if isset($row->languages[$language.id_lang|intval]->email_body)}{$row->languages[$language.id_lang|intval]->email_body}{/if}</textarea>
							</div>
						{/foreach}
						</div>
					</td>
				</tr>
			</table>

			<table class="admintable" id="tbl_html" style="display:none;">
			<tr valign="top">
				<td><!--<textarea class="rte" cols="100" rows="20" id="email_html" name="email_html">{$row->email_body}</textarea>-->
					<div class="translatable">
					{foreach from=$languages item=language}
						<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
							<textarea class="rte" cols="100" rows="20" id="email_html_{$language.id_lang|intval}" name="email_html_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->email_body)}{$row->languages[$language.id_lang|intval]->email_body}{/if}</textarea>
						</div>
					{/foreach}
					</div>
				</td>
				<td><br /><br /><br /><br />
				
				<div style="padding:10px;">
					<div><b>{l s='Tags' mod='awocoupon'}</b></div>
					{literal}
						<div>{store_name}</div>
						<div>{siteurl}</div>
						<div>{vouchers}</div>
						<div>{image_embed}</div>
						<div>{purchaser_first_name}</div>
						<div>{purchaser_last_name}</div>
						<div>{recipient_name}</div>
						<div>{recipient_email}</div>
						<div>{recipient_message}</div>
						<div>{today_date}</div>
						<div>{order_id}</div>
						<div>{order_date}</div>
						<div>{order_total}</div>
						<div>{product_name}</div>
					{/literal}
				</div>

			</td></tr>

			</table>


			</fieldset>

		</div>

	</div>



	<div class="margin-form">
		<input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="submit" class="button">
	</div>





	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="profile" />
	<input type="hidden" name="layout" value="edit" />
	<input type="hidden" name="task" value="store" />
	<input type="hidden" name="id" value="{$row->id}" />
	<input type="hidden" name="cid[]" value="{$row->id}" />

</form>



<a href="{$back_url}"><img src="{$back_img}"> Back to list</a>








