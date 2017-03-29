{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if isset($products) && $products}
    {*define number of products per line in other page for desktop*}
    {if $page_name !='index' && $page_name !='product'}
        {assign var='nbItemsPerLine' value=4}
        {assign var='nbItemsPerLineTablet' value=2}
        {assign var='nbItemsPerLineMobile' value=3}
    {else}
        {assign var='nbItemsPerLine' value=6}
        {assign var='nbItemsPerLineTablet' value=3}
        {assign var='nbItemsPerLineMobile' value=2}
    {/if}
    {*define numbers of product per line in other page for tablet*}
    {assign var='nbLi' value=$products|@count}
    {math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
    {math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
    <!-- Products list -->
    <ul{if isset($id) && $id} id="{$id}"{/if} class="product_list grid row{if isset($class) && $class} {$class}{/if}">
        {foreach from=$products item=product name=products}
            {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
            {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
            {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
            {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
            {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
            {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
            <li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-xs-6 col-sm-4 col-md-2{else} col-xs-6 col-sm-6 col-md-3{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModuloMobile)} last-mobile-line{/if}">
                <div class="product-container" itemscope itemtype="http://schema.org/Product">
                    <div class="left-block">
                        <div class="product-image-container">
                            <a class="product_img_link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
                                <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
                                {hook h='productImageHover' id_product=$product.id_product}
                            </a>
                            {if isset($quick_view) && $quick_view}
                                <div class="quick-view-wrapper-mobile">
                                    <a class="quick-view-mobile" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
                                        <i class="icon-eye-open"></i>
                                    </a>
                                </div>
                                <a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
                                    <span>{l s='+'}</span>
                                </a>
                            {/if}
                            {if (!$PS_CATALOG_MODE && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                                <div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                    {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
									<span itemprop="price" class="price product-price">
                                        {hook h="displayProductPriceBlock" product=$product type="before_price"}
                                        {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
									</span>
									<span itemprop="priceCurrency" content="{$currency->iso_code}" />{$currency->iso_code}</span>
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                        {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                        <span class="old-price product-price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>
                                        {if $product.specific_prices.reduction_type == 'percentage'}
                                            <span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
                                        {/if}
                                    {/if}
                                        {if $PS_STOCK_MANAGEMENT && isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                                            <span class="unvisible">
											{if ($product.allow_oosp || $product.quantity > 0)}
                                                <link itemprop="availability" href="http://schema.org/InStock" />{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later}{else}{l s='In Stock'}{/if}{else}{l s='Out of stock'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now}{else}{l s='In Stock'}{/if}{/if}
											{elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
													<link itemprop="availability" href="http://schema.org/LimitedAvailability" />{l s='Product available with different options'}

											{else}
													<link itemprop="availability" href="http://schema.org/OutOfStock" />{l s='Out of stock'}
                                            {/if}
										</span>
                                        {/if}
                                        {hook h="displayProductPriceBlock" product=$product type="price"}
                                        {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                    {/if}
                                </div>
                            {/if}
                            {if isset($product.new) && $product.new == 1}
                                <a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
                                    <span class="new-label">{l s='Nou'}</span>
                                </a>
                            {/if}
                            {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                                <a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
                                    <span class="sale-label">{l s='Sale!'}</span>
                                </a>
                            {/if}
                        </div>
                        {if isset($product.is_virtual) && !$product.is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
                        {hook h="displayProductPriceBlock" product=$product type="weight"}
                    </div>
                    <div class="right-block">
                        <h5 itemprop="name">
                            {if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
                            <a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                {$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
                            </a>
                        </h5>
                        {hook h='displayProductListReviews' product=$product}
                        <p class="product-desc" itemprop="description">
                            {$product.description_short|strip_tags:'UTF-8'|truncate:142:'...'}
                        </p>
                        {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            <div class="content_price">
                                {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                    {hook h="displayProductPriceBlock" product=$product type='before_price'}
                                    <span class="price product-price">
								{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
							</span>
                                    {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                        {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                        <span class="old-price product-price">
									{displayWtPrice p=$product.price_without_reduction}
								</span>
                                        {hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
                                        {if $product.specific_prices.reduction_type == 'percentage'}
                                            <span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
                                        {/if}
                                    {/if}
                                    {hook h="displayProductPriceBlock" product=$product type="price"}
                                    {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                    {hook h="displayProductPriceBlock" product=$product type='after_price'}
                                {/if}
                            </div>
                        {/if}

                        {if isset($product.color_list)}
                            <div class="color-list-container">{$product.color_list}</div>
                        {/if}
                        <div class="product-flags">
                            {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                                {if isset($product.online_only) && $product.online_only}
                                    <span class="online_only">{l s='Online only'}</span>
                                {/if}
                            {/if}
                            {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                            {elseif isset($product.reduction) && $product.reduction && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                                <span class="discount">{l s='Reduced price!'}</span>
                            {/if}
                        </div>
                        {if (!$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            {if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                                <span class="availability">
								{if ($product.allow_oosp || $product.quantity > 0)}
                                    <span  style="color:#41c1ca;" class="{if $product.quantity <= 0 && isset($product.allow_oosp) && !$product.allow_oosp} label-out-of-stock{elseif $product.quantity <= 0} label-stock-low{else} label-in-stock{/if}">
										{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later}{else}{l s='In Stock'}{/if}{else}{l s='Out of stock'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now}{else}{l s='In Stock'}{/if}{/if}
									</span>
								{elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
									<span style="color:#41c1ca;" class="label-in-stock">
										{l s='In stoc'}
									</span>
								{else}
									<span class="label-out-of-stock" style="color:black">
										{l s='Out of stock'}
									</span>
                                {/if}
							</span>
                            {/if}
                        {/if}

                    </div>

                </div><!-- .product-container> -->
            </li>
        {/foreach}
    </ul>
    {addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
    {addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
    {addJsDef comparator_max_item=$comparator_max_item}
    {addJsDef comparedProductsIds=$compared_products}
{/if}
