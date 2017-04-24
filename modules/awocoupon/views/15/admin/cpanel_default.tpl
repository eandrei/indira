{*
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
*}

{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

{literal}<style>
	div.icon { text-align: center; margin-right: 15px; float: left; margin-bottom: 15px; }
	#cpanel span { display: block; text-align: center; }
	#cpanel img { padding: 10px 0; margin: 0 auto; }
	#cpanel div.icon a {background-color: white;background-position: -30px;display: block;float: left;height: 97px;width: 108px;color: #565656;vertical-align: middle;text-decoration: none;border: 1px solid #CCC;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;-webkit-transition-property: background-position, -webkit-border-bottom-left-radius, -webkit-box-shadow;-moz-transition-property: background-position, -moz-border-radius-bottomleft, -moz-box-shadow;-webkit-transition-duration: 0.8s;-moz-transition-duration: 0.8s;border-top-left-radius: 5px 5px;border-top-right-radius: 5px 5px;border-bottom-right-radius: 5px 5px;border-bottom-left-radius: 5px 5px;}
</style>{/literal}

<br /><br />
<div>
<table cellspacing="0" cellpadding="0" border="0" width="100%"><tbody><tr>
	<td width="55%" valign="top">
		<div id="cpanel">
			<div style="float:left;"><div class="icon"><a href="{$url}&view=coupon&layout=edit&token={$token}"><img src="{$img_url}/icon-48-new.png" alt=""><span>{l s='New Coupon' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=coupon&token={$token}"><img src="{$img_url}/coupons.png" alt=""><span>{l s='Coupons' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=giftcert&token={$token}"><img src="{$img_url}/icon-48-giftcert.png" alt=""><span>{l s='Gift Certificates' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=profile&token={$token}"><img src="{$img_url}/icon-48-profile.png" alt=""><span>{l s='Profiles' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=history&token={$token}"><img src="{$img_url}/icon-48-history.png" alt=""><span>{l s='History of Uses' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=import&token={$token}"><img src="{$img_url}/icon-48-import.png" alt=""><span>{l s='Import' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=report&token={$token}"><img src="{$img_url}/icon-48-report.png" alt=""><span>{l s='Reports' mod='awocoupon'}</span></a></div></div>
			<div style="float:left;"><div class="icon"><a href="{$url}&view=config&token={$token}"><img src="{$img_url}/icon-48-config.png" alt=""><span>{l s='Configuration' mod='awocoupon'}</span></a></div></div>
			<div style="clear"></div>
		</div>
	</td>
	<td width="45%" valign="top">			
		<div id="genstat-pane" class="pane-sliders">

			<div class="panel">
				<table>
				<thead><tr><th colspan="2">{l s='General Statistics' mod='awocoupon'}</th></tr></thead>
				<tr class="first"><td class="first b">{$genstats->total}</td><td class="t">{l s='Total Coupons' mod='awocoupon'}</td></tr>
				<tr><td class="first b">{$genstats->active}</td><td class=" t approved">{l s='Active Coupons' mod='awocoupon'}</td></tr>
				<tr><td class="first b">{$genstats->inactive}</td><td class=" t inactive">{l s='Inactive Coupons' mod='awocoupon'}</td></tr>
				<tr><td class="first b">{$genstats->templates}</td><td class=" t template">{l s='Templates' mod='awocoupon'}</td></tr>
				</table>
			</div>

			<br /><br />
			
			
			<table class="adminlist">
			<thead><tr><th colspan="2">{l s='License' mod='awocoupon'}</th></tr></thead>
			<tbody>
			{if $license->l!=''}
				<tr><td width="33%">{l s='License' mod='awocoupon'}:</td><td>{$license->l}</td></tr>
				<tr><td width="33%">{l s='Website' mod='awocoupon'}:</td><td>{$license->url}</td></tr>
				<tr>
					<td width="33%">{l s='Expiration' mod='awocoupon'}:</td>
					<td><b>{if $license->exp=='' || $license->exp==0}
								<font color="green">{l s='Permanent License' mod='awocoupon'}</font>
							{else}
								<font color="red">{$license->exp}</font> <span style="font-size:17px;">&raquo;</span> <a href="http://awodev.com/documentation/frequently-asked-questions#license-permanent" target="_blank">{l s='Make Permanent License' mod='awocoupon'}</a>
							{/if}
						</b>
					</td>
				</tr>
			{else}
				<tr><td colspan="2"><span style="color:red;">{l s='Invalid License' mod='awocoupon'}</span> <span style="font-size:17px;">&raquo;</span> <a href="{$url}&view=license&token={$token}">{l s='Activate' mod='awocoupon'}</a></td></tr>
			{/if}
			</tbody>
			</table>
		
		
			<br /><br />
			
			
			
			<table class="adminlist">
			<thead><tr><th colspan="2">{l s='Check for Updates' mod='awocoupon'}</th></tr></thead>
			<tbody>
			{if $status->connect==0}
				<tr><td colspan="2"><b><font color="red">{l s='Connection Failed' mod='awocoupon'}</font></b></td></tr>
			{elseif $status->enabled==1}
				<tr><td colspan="2">
					{if $status->current==0}
					<strong><font color="green">{l s='Latest Version Installed' mod='awocoupon'}</font></strong>
					{elseif $status->current==-1}
					<b><font color="red">{l s='Old Version currently installed' mod='awocoupon'}</font></b> &nbsp; &nbsp;
					{else}
					<b><font color="orange">{l s='Newer Version' mod='awocoupon'}</font></b>
					{/if}
					</td>
				</tr>
				{if $status->current!=0}
				<tr><td width="33%">{l s='Latest Version' mod='awocoupon'}</td><td>{$status->version} ({$status->released})</td></tr>
				{/if}
			{/if}
			<tr><td width="33%">{l s='Installed Version' mod='awocoupon'}:</td><td>{$status->current_version}</td></tr>
			</tbody>
			</table>



				
		
		</div>
	</td>
</tr></tbody></table>
</div>


