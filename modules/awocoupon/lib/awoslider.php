<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;


class AwoJHtmlSliders
{
	public static function start($group = 'sliders', $params = array())
	{
		$js  = '<script language="javascript" type="text/javascript">
				var $j = jQuery.noConflict();
				jQuery(document).ready(function() {'.(isset($params['closeAll']) && $params['closeAll'] == 1 
							? 'jQuery("#'.$group.' .panel h3").removeClass("pane-toggler-down").addClass("pane-toggler");
								jQuery("#'.$group.' .panel div.pane-slider").hide();'
							: '').'
					jQuery("#'.$group.' .panel h3").click(function() {
						if(jQuery(this).parent().find(".pane-slider").is(":visible")) {
							jQuery(this).removeClass("pane-toggler-down").addClass("pane-toggler");
							jQuery(this).parent().find(".pane-slider").hide();
						}
						else {
							jQuery(this).removeClass("pane-toggler").addClass("pane-toggler-down");
							jQuery(this).parent().find(".pane-slider").show().css({"height":"auto"});
						}		
						
					});
				});
				</script>
				';
		return $js.'<div id="'.$group.'" class="pane-sliders"><div style="display:none;"><div>';
	}
	public static function end()
	{
		return '</div></div></div>';
	}
	public static function panel($text, $id)
	{
		return '</div></div><div class="panel"><h3 class="pane-toggler-down title" id="'.$id.'"><a href="javascript:void(0);"><span>'.$text.'</span></a></h3><div class="pane-slider content" style="height:auto;">';
	}

}
