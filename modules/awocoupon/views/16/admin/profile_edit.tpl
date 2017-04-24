{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}



<script type="text/javascript">
	var iso = "{$isoTinyMCE}" ;
	var pathCSS = "{$theme_path}" ;
	var ad = "{$tiny_ad}";
</script>


<script language="javascript" type="text/javascript">
<!--

$(document).ready(function() {
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

	displayCartRuleTab('general');
	
	
	
	
	tinySetup({
		editor_selector :"autoload_rte"
	});
	
	
	
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

function displayCartRuleTab(tab) {
	$('.cart_rule_tab').hide();
	$('.tab-page').removeClass('selected');
	$('#awocoupon_block_' + tab).show();
	$('#awocoupon_block_link_' + tab).addClass('selected');
}

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
	
	if($.trim(form.title.value) == '') err += '\n'+str_title+': '+str_input_error;
	if(form.message_type.value=='') err += '\n'+str_message_type+': '+str_selection_error;
	else {
		if(form.message_type.value == 'text');
		else if(form.message_type.value == 'html') {
		
			//if(form.image.value=='') err += '\n'+str_image+': '+str_selection_error;
			
			if(form.image.selectedIndex != 0) {

			
				if(form.couponcode_align.value!='C' && (!isUnsignedInteger($.trim(form.couponcode_padding.value)) || $.trim(form.couponcode_padding.value)<1)) err += '\n'+str_coupon_code+'=>'+str_padding+': '+str_int_error;
				if(!isUnsignedInteger($.trim(form.couponcode_y.value)) || $.trim(form.couponcode_y.value)<1) err += '\n'+str_coupon_code+'=>'+str_y_axis+': '+str_int_error;
				if(form.couponcode_font.value=='') err += '\n'+str_coupon_code+'/'+str_font+': '+str_selection_error;
				if(!isUnsignedInteger($.trim(form.couponcode_font_size.value)) || $.trim(form.couponcode_font_size.value)<1) err += '\n'+str_coupon_code+'=>'+str_font_size+': '+str_int_error;

				if(form.couponvalue_align.value!='C' && (!isUnsignedInteger($.trim(form.couponvalue_padding.value)) || $.trim(form.couponvalue_padding.value)<1)) err += '\n'+str_coupon_value+'=>'+str_padding+': '+str_int_error;
				if(!isUnsignedInteger($.trim(form.couponvalue_y.value)) || $.trim(form.couponvalue_y.value)<1) err += '\n'+str_coupon_value+'=>'+str_y_axis+': '+str_int_error;
				if(form.couponvalue_font.value=='') err += '\n'+str_coupon_value+'/'+str_font+': '+str_selection_error;
				if(!isUnsignedInteger($.trim(form.couponvalue_font_size.value)) || $.trim(form.couponvalue_font_size.value)<1) err += '\n'+str_coupon_value+'=>'+str_font_size+': '+str_int_error;
				
				if($.trim(form.expiration_text.value)!='') {
					if(form.expiration_align.value!='C' && (!isUnsignedInteger($.trim(form.expiration_padding.value)) || $.trim(form.expiration_padding.value)<1)) err += '\n'+str_expiration+'=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($.trim(form.expiration_y.value)) || $.trim(form.expiration_y.value)<1) err += '\n'+str_expiration+'=>'+str_y_axis+': '+str_int_error;
					if(form.expiration_font.value=='') err += '\n'+str_expiration+'=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($.trim(form.expiration_font_size.value)) || $.trim(form.expiration_font_size.value)<1) err += '\n'+str_expiration+'=>'+str_font_size+': '+str_int_error;
				}
				
				if($.trim(form.freetext1_text.value)!='') {
					if(form.freetext1_align.value!='C' && (!isUnsignedInteger($.trim(form.freetext1_padding.value)) || $.trim(form.freetext1_padding.value)<1)) err += '\n'+str_free_text+' 1=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($.trim(form.freetext1_y.value)) || $.trim(form.freetext1_y.value)<1) err += '\n'+str_free_text+' 1=>'+str_y_axis+': '+str_int_error;
					if(form.freetext1_font.value=='') err += '\n'+str_free_text+' 1=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($.trim(form.freetext1_font_size.value)) || $.trim(form.freetext1_font_size.value)<1) err += '\n'+str_free_text+' 1=>'+str_font_size+': '+str_int_error;
				}
				
				if($.trim(form.freetext2_text.value)!='') {
					if(form.freetext2_align.value!='C' && (!isUnsignedInteger($.trim(form.freetext2_padding.value)) || $.trim(form.freetext2_padding.value)<1)) err += '\n'+str_free_text+' 2=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($.trim(form.freetext2_y.value)) || $.trim(form.freetext2_y.value)<1) err += '\n'+str_free_text+' 2=>'+str_y_axis+': '+str_int_error;
					if(form.freetext2_font.value=='') err += '\n'+str_free_text+' 2=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($.trim(form.freetext2_font_size.value)) || $.trim(form.freetext2_font_size.value)<1) err += '\n'+str_free_text+' 2=>'+str_font_size+': '+str_int_error;
				}
				
				if($.trim(form.freetext3_text.value)!='') {
					if(form.freetext3_align.value!='C' && (!isUnsignedInteger($.trim(form.freetext3_padding.value)) || $.trim(form.freetext3_padding.value)<1)) err += '\n'+str_free_text+' 3=>'+str_padding+': '+str_int_error;
					if(!isUnsignedInteger($.trim(form.freetext3_y.value)) || $.trim(form.freetext3_y.value)<1) err += '\n'+str_free_text+' 3=>'+str_y_axis+': '+str_int_error;
					if(form.freetext3_font.value=='') err += '\n'+str_free_text+' 3=>'+str_font+': '+str_selection_error;
					if(!isUnsignedInteger($.trim(form.freetext3_font_size.value)) || $.trim(form.freetext3_font_size.value)<1) err += '\n'+str_free_text+' 3=>'+str_font_size+': '+str_int_error;
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
		submited = false;
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
	document.getElementById('tbl_html').style.display = 'none';
	document.getElementById('tbl_image').style.display = 'none';
	document.getElementById('tbl_pdf').style.display = 'none';
	document.getElementById('tbl_text').style.display = 'none';
	document.getElementById('li_label_email').style.display = 'none';
	document.getElementById('li_label_image').style.display = 'none';
	document.getElementById('li_label_pdf').style.display = 'none';
	
}
function message_type_change(is_edit) {
	var form = document.adminForm;
	
	is_edit = (is_edit == undefined) ? false : is_edit;
	if(!is_edit) resetall();
	else hideall();
	displayCartRuleTab('general');
	
	if(form.message_type.value == 'text') {
		document.getElementById('tbl_text').style.display = '';
		document.getElementById('li_label_email').style.display = '';
		
	} else if(form.message_type.value == 'html') {
		document.getElementById('tbl_html').style.display = '';
		document.getElementById('tbl_image').style.display = '';
		document.getElementById('tbl_pdf').style.display = '';
		document.getElementById('li_label_email').style.display = '';
		document.getElementById('li_label_image').style.display = '';
		document.getElementById('li_label_pdf').style.display = '';
	}
}
function generate_preview() {
	if(submitbutton('previewprofile')) {
		var form = document.adminForm;
		var str = '';
			
		str += '&image='+encodeURIComponent(form.image.value);
		str += '&code='+encodeURIComponent(form.couponcode_align.value+'|'+form.couponcode_padding.value+'|'+form.couponcode_y.value+'|'+form.couponcode_font.value+'|'+form.couponcode_font_size.value+'|'+form.couponcode_font_color.value);
		str += '&value='+encodeURIComponent(form.couponvalue_align.value+'|'+form.couponvalue_padding.value+'|'+form.couponvalue_y.value+'|'+form.couponvalue_font.value+'|'+form.couponvalue_font_size.value+'|'+form.couponvalue_font_color.value);
		if($.trim(form.expiration_text.value)!='')
			str += '&expiration='+encodeURIComponent(form.expiration_text.value+'|'+form.expiration_align.value+'|'+form.expiration_padding.value+'|'+form.expiration_y.value+'|'+form.expiration_font.value+'|'+form.expiration_font_size.value+'|'+form.expiration_font_color.value);
		if($.trim(form.freetext1_text.value)!='')
			str += '&freetext1='+encodeURIComponent(form.freetext1_text.value+'|'+form.freetext1_align.value+'|'+form.freetext1_padding.value+'|'+form.freetext1_y.value+'|'+form.freetext1_font.value+'|'+form.freetext1_font_size.value+'|'+form.freetext1_font_color.value);
		if($.trim(form.freetext2_text.value)!='')
			str += '&freetext2='+encodeURIComponent(form.freetext2_text.value+'|'+form.freetext2_align.value+'|'+form.freetext2_padding.value+'|'+form.freetext2_y.value+'|'+form.freetext2_font.value+'|'+form.freetext2_font_size.value+'|'+form.freetext2_font_color.value);
		if($.trim(form.freetext3_text.value)!='')
			str += '&freetext3='+encodeURIComponent(form.freetext3_text.value+'|'+form.freetext3_align.value+'|'+form.freetext3_padding.value+'|'+form.freetext3_y.value+'|'+form.freetext3_font.value+'|'+form.freetext3_font_size.value+'|'+form.freetext3_font_color.value);
		{foreach from=$row->imgplugin key=k item=r}
			{foreach from=$r key=k2 item=r2}
				if($.trim(form.elements["imgplugin[{$k}][{$k2}][text]"].value)!='')
					str += '&imgplugin[{$k}][{$k2}]='+encodeURIComponent(form.elements["imgplugin[{$k}][{$k2}][text]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][align]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][padding]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][y]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font_size]"].value+'|'+form.elements["imgplugin[{$k}][{$k2}][font_color]"].value);
			{/foreach}
		{/foreach}

		$(document).ready(function () {
				$.fancybox({
					"width"				: "75%",
					"height"			: "75%",
					"autoScale"     	: false,
					"transitionIn"		: "none",
					"transitionOut"		: "none",
					'type': 'iframe',
					'href': "{$ajax_url}&task=previewprofileEdit"+str
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
form#_form { background-color:#ebedf4; border:1px solid #ccced7; min-height:404px; padding: 5px 10px 10px; margin-left:140px;}
form#_form  h4 { font-size:18px; font-weight:normal; margin-top:0;}

</style>


<div class="panel awocoupon">
	<h3><i class="icon-tag"></i> {if $row->id>0}{l s='Edit' mod='awocoupon'}{else}{l s='New' mod='awocoupon'}{/if} {l s='Profile' mod='awocoupon'}</h3>



	<br /><br />

	<div>
		<div class="productTabs">
			<ul class="tab">
				<li class="tab-row">
					<a class="tab-page" id="awocoupon_block_link_general" href="javascript:displayCartRuleTab('general');">{l s='General' mod='awocoupon'}</a>
				</li>
				<li class="tab-row" id="li_label_image">
					<a class="tab-page" id="awocoupon_block_link_image" href="javascript:displayCartRuleTab('image');">{l s='Image' mod='awocoupon'}</a>
				</li>
				<li class="tab-row" id="li_label_pdf">
					<a class="tab-page" id="awocoupon_block_link_pdf" href="javascript:displayCartRuleTab('pdf');">{l s='PDF' mod='awocoupon'}</a>
				</li>
				<li class="tab-row" id="li_label_email">
					<a class="tab-page" id="awocoupon_block_link_email" href="javascript:displayCartRuleTab('email');">{l s='Email' mod='awocoupon'}</a>
				</li>
			</ul>
		</div>
	</div>

	<form id="{$table}_form" name="adminForm" method="post" action="{$smarty.server.REQUEST_URI}"  onsubmit="return submitbutton();">

		<div id="awocoupon_block_general" class="cart_rule_tab">
			<h4>{l s='General' mod='awocoupon'}</h4>
			<div class="separation"></div>

			<table class="admintable">
				<tr><td class="key key2"><label class="control-label">{l s='Title' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="title" value="{$row->title}" maxlength="255"></td>
				</tr>
				<tr><td class="key key2"><label class="control-label">{l s='From Name' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="from_name" value="{$row->from_name}" maxlength="255"></td>
				</tr>
				<tr><td class="key key2"><label class="control-label">{l s='From Email' mod='awocoupon'}</label></td>
					<td><input type="text" size="60" name="from_email" value="{$row->from_email}" maxlength="255"></td>
				</tr>
				<tr valign="top"><td class="key key2"><label class="control-label">{l s='Email Subject' mod='awocoupon'}</label></td>
					<td><!--<input type="text" size="60" name="email_subject" value="{$row->email_subject}" maxlength="255">-->
						{foreach from=$languages item=language}
							{if $languages|count > 1}
								<div class="translatable-field row lang-{$language.id_lang}">
									<div class="col-lg-9">
							{/if}
								<input type="text" style="width:100%;" size="60" name="email_subject_{$language.id_lang|intval}" value="{if isset($row->languages[$language.id_lang|intval]->email_subject)}{$row->languages[$language.id_lang|intval]->email_subject}{/if}" maxlength="255">
							{if $languages|count > 1}
									</div>
									<div class="col-lg-2">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											{$language.iso_code}
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											{foreach from=$languages item=language}
												<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
											{/foreach}
										</ul>
									</div>
								</div>
							{/if}
						{/foreach}
					</td>
				</tr>
				<tr><td class="key key2"><label class="control-label">{l s='Bcc Admin' mod='awocoupon'}</label></td>
					<td><input type="checkbox" size="60" name="bcc_admin" {if $row->bcc_admin!=''} CHECKED {/if} value="1"></td>
				</tr>
				<tr><td class="key key2"><label class="control-label">{l s='Message Type' mod='awocoupon'}</label></td>
					<td>{$lists.message_type}</td>
				</tr>
			</table>
		</div>
		

		<div id="awocoupon_block_image" class="cart_rule_tab">
			<h4>{l s='Image' mod='awocoupon'}</h4>
			<div class="separation"></div>
			
			<table class="" id="tbl_image" style="display:none;">
			<tr><td class="key" colspan="2">{l s='Image' mod='awocoupon'} &nbsp; &nbsp;{$lists.image}
				&nbsp;&nbsp;&nbsp;<input type="button" onclick="generate_preview();" value="{l s='Preview' mod='awocoupon'}"></td></tr>
			<tr><td colspan="2">
			<table bgcolor="#ffffff" id="image_properties" style="display:none;width:auto;">
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
		</div>
		
		
		<div id="awocoupon_block_email" class="cart_rule_tab">
			<h4>{l s='Email' mod='awocoupon'}</h4>
			<div class="separation"></div>
			
			<table class="admintable" id="tbl_text" style="display:none;">
				<tr valign="top"><td class="key key2"><label class="control-label">{l s='Email Body' mod='awocoupon'}</label></td>
					<td>
						<!--<textarea cols="45" rows="10" name="email_body" >{$row->email_body}</textarea>-->
						{foreach from=$languages item=language}
							{if $languages|count > 1}
								<div class="translatable-field row lang-{$language.id_lang}">
									<div class="col-lg-9">
							{/if}
							<textarea style="width:100%;" cols="45" rows="10" name="email_body_{$language.id_lang|intval}" >{if isset($row->languages[$language.id_lang|intval]->email_body)}{$row->languages[$language.id_lang|intval]->email_body}{/if}</textarea>
							{if $languages|count > 1}
									</div>
									<div class="col-lg-2">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											{$language.iso_code}
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											{foreach from=$languages item=language}
												<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
											{/foreach}
										</ul>
									</div>
								</div>
							{/if}
						{/foreach}
					</td>
				</tr>
			</table>

			<table class="admintable" id="tbl_html" style="display:none;">
			<tr valign="top">
				<td>
					<!--<textarea class="autoload_rte" cols="100" rows="20" id="email_html" name="email_html">{$row->email_body}</textarea>-->
					{foreach from=$languages item=language}
						{if $languages|count > 1}
							<div class="translatable-field row lang-{$language.id_lang}">
								<div class="col-lg-9">
						{/if}
							<textarea class="autoload_rte" style="width:100%;" cols="100" rows="20" id="email_html_{$language.id_lang|intval}" name="email_html_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->email_body)}{$row->languages[$language.id_lang|intval]->email_body}{/if}</textarea>
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
										{$language.iso_code}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
											<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					{/foreach}
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
			
			
			
			
		</div>
		
		
		<div id="awocoupon_block_pdf" class="cart_rule_tab">
			<h4>{l s='PDF' mod='awocoupon'}</h4>
			<div class="separation"></div>
			
			<table class="admintable" id="tbl_pdf" style="display:none;">
			<tr><td>{l s='Activate' mod='awocoupon'}</td><td>{$lists.is_pdf}</td></tr>
			<tr valign="top">
				<td>{l s='Header' mod='awocoupon'}</td>
				<td>
					{foreach from=$languages item=language}
						{if $languages|count > 1}
							<div class="translatable-field row lang-{$language.id_lang}">
								<div class="col-lg-9">
						{/if}
						<textarea class="autoload_rte" style="width:100%;" cols="100" rows="10" id="pdf_header_{$language.id_lang|intval}" name="pdf_header_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_header)}{$row->languages[$language.id_lang|intval]->pdf_header}{/if}</textarea>
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
										{$language.iso_code}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
											<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					{/foreach}
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr valign="top">
				<td>{l s='Body' mod='awocoupon'}</td>
				<td>
					{foreach from=$languages item=language}
						{if $languages|count > 1}
							<div class="translatable-field row lang-{$language.id_lang}">
								<div class="col-lg-9">
						{/if}
						<textarea class="autoload_rte" style="width:100%;" cols="100" rows="20" id="pdf_body_{$language.id_lang|intval}" name="pdf_body_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_body)}{$row->languages[$language.id_lang|intval]->pdf_body}{/if}</textarea>
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
										{$language.iso_code}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
											<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					{/foreach}
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
					{foreach from=$languages item=language}
						{if $languages|count > 1}
							<div class="translatable-field row lang-{$language.id_lang}">
								<div class="col-lg-9">
						{/if}
						<textarea class="autoload_rte" style="width:100%;" cols="100" rows="10" id="pdf_footer_{$language.id_lang|intval}" name="pdf_footer_{$language.id_lang|intval}">{if isset($row->languages[$language.id_lang|intval]->pdf_footer)}{$row->languages[$language.id_lang|intval]->pdf_footer}{/if}</textarea>
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
										{$language.iso_code}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
											<li><a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					{/foreach}
				</td>
				<td>&nbsp;</td>
			</tr>

			</table>
			
			
			
			
		</div>
		
		
		
		
		<div class="separation"></div>
		<div class="margin-form"><input type="submit" value="   {l s='Save' mod='awocoupon'}   " name="randome" class="button" id="{$table}_form_submit_btn"></div>





	<input type="hidden" name="module" value="awocoupon" />
	<input type="hidden" name="view" value="profile" />
	<input type="hidden" name="layout" value="edit" />
	<input type="hidden" name="task" value="store" />
	<input type="hidden" name="id" value="{$row->id}" />
	<input type="hidden" name="cid[]" value="{$row->id}" />

	</form>

	<br /><br />





	{include file="footer_toolbar.tpl"}
</div>



<script type="text/javascript">
	hideOtherLanguage({$id_lang_default});
</script>

