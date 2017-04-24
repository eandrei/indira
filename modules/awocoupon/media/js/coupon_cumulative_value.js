/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

var $j = jQuery.noConflict();  // added so jquery does not conflict with mootools
var $ = jQuery.noConflict();  // added so jquery does not conflict with mootools
var ext = 3;


function showvaluedefinition() {

	html = '<table> \
			<tr><td style="border-bottom:1px solid #cccccc" colspan="1" valign="bottom"><b>'+str_cum_title+'</b></td></tr> \
			<tr><td height="10"></td></tr> \
			 \
			<tr><td colspan="1"> \
				<form name="frmcumulative" method="post" onsubmit="return CheckForm();"> \
				<div align="right"><input type="checkbox" name="cumqtytype" value="1"> '+str_cum_qty_type+'</div> \
				<table> \
				<tr><td> \
					<table id="tbldata"> \
					<tr><th style="border-bottom:1px solid #cccccc;">'+str_cum_lbl1+'</th> \
						<th style="border-bottom:1px solid #cccccc;">'+str_cum_lbl2+'</th> \
						<td width="100">&nbsp;</td></tr> \
						<tr valign="bottom"> \
							<td><input type="text" name="cumcount01" value="" maxlength="15" size="4" ></td> \
							<td><input type="text" name="cumvalue01" value="" maxlength="15" size="4" ></td> \
							<td></td> \
						</tr> \
						<tr id="trRow02" valign="bottom"> \
							<td><input type="text" name="cumcount02" value="" maxlength="15" size="4" ></td> \
							<td><input type="text" name="cumvalue02" value="" maxlength="15" size="4" ></td> \
							<td><input type="button" onclick="deleterowT(\'trRow02\');" class="p" value="x"></td> \
						</tr> \
						<tr id="trRow03" valign="bottom"> \
							<td><input type="text" name="cumcount03" value="" maxlength="15" size="4" ></td> \
							<td><input type="text" name="cumvalue03" value="" maxlength="15" size="4" ></td> \
							<td><input type="button" onclick="deleterowT(\'trRow03\');" class="p" value="x"></td> \
						</tr> \
					</table></td></tr> \
				<tr><td><input type="button" name="addaccount" value="'+str_cum_new+'" onclick="newline();"></td></tr> \
				<tr><td height="10"></td></tr> \
				<tr><td height="10"></td></tr> \
				<tr><td align="right"><input type="button" value="'+str_cum_subm+'" style="width:100%;" onclick="populateparent();"></td></tr> \
				</table> \
				</form> \
			</td></tr></table> \
		';
	page_alert('modal_coupon_value_def',html);
	
	//focus, loose focus from input field
	document.frmcumulative.elements['cumcount01'].focus()
	
	// populate if value exists
	ext = 3;
	if(document.adminForm.coupon_value_def.value!='') {
		eachrow = document.adminForm.coupon_value_def.value.split(';');
		counting = 0;
		for(i=0; i<eachrow.length; i++) {
			if(trim(eachrow[i])!='' && eachrow[i].substr(0,1)!='[') {
				tmp = eachrow[i].split('-');
				if(tmp[0]!=undefined && tmp[1]!=undefined) {
					counting++;
					var extstr = PadDigits(counting,2);
					if(counting>3) newline();
					document.frmcumulative.elements['cumcount'+extstr].value = tmp[0];
					document.frmcumulative.elements['cumvalue'+extstr].value = tmp[1];
				}
			}
		}
		if(eachrow[eachrow.length-1].substr(0,1)=='[') {
			opt = eachrow[eachrow.length-1].substr(1);
			opt = opt.substr(0,opt.length-1);
			tmp = opt.split('=');
			if(tmp[0]!=undefined && tmp[1]!=undefined) {
				if(tmp[0]=='qty_type' && tmp[1]=='distinct') document.frmcumulative.elements['cumqtytype'].checked = true;
			}
		}
	}

}

function page_alert(id,html) {
	
	id = id == undefined ? 'tester' : id;
	
	//if page msg instance exists, return
	if ($j("#"+id).length > 0) return;

	//if html is not set or is empty, existing page_msg will be closed
	if (html == "") return;
	
	html += '<br><input style="position:absolute;top:0;right:0;padding:3px;" type="button" value="x" onclick="$j(\'#'+id+'\').empty().remove()"> \
				<div align="center" style="margin-top:10px;position:relative;"></div>';
	
	
	//create container div
	var new_div = document.createElement("div");
	new_div.setAttribute("name",id);
	new_div.id = id;
	document.body.appendChild(new_div);
	
	
	//new_div.style.width = '500px';
	new_div.style.border = '2px solid #bbbbbb';
	new_div.style.whiteSpace = 'nowrap';
	new_div.style.backgroundColor = '#ffffff';
	new_div.style.color = '#000000';
	new_div.style.padding = '10px';
	new_div.style.position = 'absolute';
	new_div.style.left = '0';
	new_div.style.top = '0';
	new_div.style.zIndex = '1';
	
	//load ajax content into div
	$j("#"+id).empty().html(html);

	the_x = 0;
	the_y = 0;
	oH = 0;
	oW = 0;
	
	if (self.pageYOffset) {
	// browsers other than internet explorer
		the_x = self.pageXOffset;
		the_y = self.pageYOffset;
		oH = window.outerHeight; 
		oW = window.outerWidth; 

	}else if (document.documentElement && document.documentElement.scrollTop){
	// internet explorer 6      
		the_x = document.documentElement.scrollLeft;
		the_y = document.documentElement.scrollTop;
		oW = document.body.clientWidth; 
		oH = document.body.clientHeight; 
	}else if (document.body){
	 // all other internet explorer versions
		the_x = document.body.scrollLeft;
		the_y = document.body.scrollTop;
		oW = document.body.clientWidth; 
		oH = document.body.clientHeight; 
	}

	//reposition alert box to center of the page
	new_div.style.left = parseInt((oW - $j("#"+id).width())/2 + the_x)+'px';
	new_div.style.top = parseInt((oH - $j("#"+id).height())/2 + the_y-100)+'px';
	
//alert(the_x+' '+the_y+' '+oW+' '+oH+' '+new_div.style.left+' '+new_div.style.top);
	//alert(screen.height+' '+window.outerHeight+' '+window.outerWidth+' '+window.pageXOffset+' '+window.pageYOffset+' '+$j("#page_alert").height()+' '+new_div.style.top);
	
}


function newline() {
	ext++;
	var extstr = PadDigits(ext,2);
	var tbl = document.getElementById('tbldata');
	var row = document.createElement("tr");
	row.id = "trRow"+extstr;
	
	
	var cell = document.createElement("td");
	cell.innerHTML = '<input type="text" name="cumcount'+extstr+'" value="" maxlength="15" size="4">';
	row.appendChild(cell);

	var cell = document.createElement("td");
	cell.innerHTML = '<input type="text" name="cumvalue'+extstr+'" value="" maxlength="15" size="4">';
	row.appendChild(cell);

	var cell = document.createElement("td");
	cell.innerHTML = '<input type="button" onclick="deleterowT(\'trRow'+extstr+'\');" class="p" value="x">';
	row.appendChild(cell);

	var tbody = document.createElement("tbody");
	tbody.appendChild(row);
           
	tbl.appendChild(tbody);

}
function deleterowT(id) {
	var tr = document.getElementById(id);
	tr.parentNode.removeChild(tr);
	runtotal();
}

function PadDigits(n, totalDigits) { 
	var n = n.toString(); 
	var pd = ''; 
	if (totalDigits > n.length) for (m=0; m < (totalDigits-n.length); m++) pd += '0'; 
	return pd + n.toString(); 
}
function populateparent() {
	string = '';
	used = {};
	for(i=1; i<=ext; i++) {
		myext = PadDigits(i,2);
		if(document.frmcumulative.elements['cumcount'+myext] != undefined) {
			cnt = document.frmcumulative.elements['cumcount'+myext].value;
			val = document.frmcumulative.elements['cumvalue'+myext].value;

			if( (Object.keys(used).length==0 && (val=='' || isNaN(val) || val<0.01))
			|| (Object.keys(used).length>0 && (val=='' || isNaN(val) || val<0))
			|| cnt=='' 
			|| isNaN(cnt) 
			|| cnt<1 ) ;
			else  {
				if(used[cnt] == undefined) {
					used[cnt] = 1;
					string += cnt+'-'+val+';';
					if(val==0) break;
				}
			}
		}
	}
	if(string != '') {
		if(document.frmcumulative.elements['cumqtytype'].checked) {
			string += '[qty_type=distinct]';
		}
	}
	document.adminForm.coupon_value_def.value = string;
	$j("#modal_coupon_value_def").empty().remove();
}

if (typeof Object.keys != 'function') {
	Object.keys = function(obj) {
		if (typeof obj != "object" && typeof obj != "function" || obj == null) {
			throw TypeError("Object.keys called on non-object");
		}
		var keys = [];
		for (var p in obj) obj.hasOwnProperty(p) &&keys.push(p);
		return keys;
	}
}
