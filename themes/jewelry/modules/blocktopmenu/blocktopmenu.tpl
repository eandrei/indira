{if $MENU != ''}
	<!-- Menu -->
	<div id="block_top_menu" class="sf-contener clearfix col-lg-12">
        <div class="menu-header">
		<div class="cat-title">
            <i style="padding-left:20px;" class="fa fa-search" aria-hidden="true"></i>
        </div>
        <div class="logo-container">
            <a href="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl}{else}{$base_dir}{/if}" title="{$shop_name|escape:'html':'UTF-8'}">
                <img class="logo img-responsive logo-mobile" src="{$img_dir}logo-mobile.jpg" alt="{$shop_name|escape:'html':'UTF-8'}"/>
            </a>
        </div>
        <div class="shopping_cart_mobile">
            <a href="/cos" title="{l s='View my shopping cart' mod='blockcart'}" rel="nofollow">
                <sup class="ajax_cart_quantity">{$cart_qties}</sup>
            </a>
        </div>

		<ul class="sf-menu clearfix menu-content">
            <li class="referral">
                <a href="/ofera" title="Castiga 20 de Ron" class="{if !$logged}login{/if} mobile-referral"><i class="fa fa-gift"><b>Invita prieteni castiga 20 RON!</b></i></a>
            </li>
            {if $MENU_SEARCH}
                <li class="sf-search noBack">
                    <form id="searchbox" action="{$link->getPageLink('search')|escape:'html':'UTF-8'}" method="get">
                        <p>
                            <input type="hidden" name="controller" value="search" />
                            <input type="hidden" value="position" name="orderby"/>
                            <input type="hidden" value="desc" name="orderway"/>

                            <input type="text" name="search_query" placeholder="{l s='Cauta'}" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|escape:'html':'UTF-8'}{/if}" />
                            <button type="submit" name="submit_search" class="btn btn-default button-search">
                                <span>{l s='Search' mod='blocksearch'}</span>
                            </button>
                        </p>
                    </form>
                </li>
            {/if}
            <li><h3>Categorii</h3></li>
			{$MENU}
		</ul>
	</div>
	<!--/ Menu -->
{/if}