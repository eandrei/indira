{*
* 2007-2016 PrestaShop
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
*  @author    FMM Modules
*  @copyright 2016 FME Modules
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="bootstrap">
	<div class="alert alert-warning">
	<ul class="list-unstyled">
		<li><h4>Please Fix the duplicate URLs below(if any) in order to avoid 404 errors.</h4></li>
	</ul>
	</div>
</div>

<div class="alert alert-info">The following tables shows products and categories sharing same URL which should NOT happen.</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-cogs"></i> Product URLs</div>
	<div class="row row-margin-bottom">
	<table class="table">
		<thead>
			<tr>
				<th class="text-left"><span class="title_box active">ID</span></th>
				<th class="text-left"><span class="title_box active">Name</span></th>
				<th class="text-left"><span class="title_box active" style="color: red">Link Rewrite</span></th>
				<th class="text-center"><span class="title_box active">Occurance</span></th>
			</tr>
		</thead>
		<tbody>
			{if empty($product_coll)}
				<tr><td>No Collisions found</td></tr>
			{else}
				{foreach from=$product_coll item=collision}
					<tr>
						<td class="text-left">{$collision.id_product|escape:'htmlall':'UTF-8'}</td>
						<td class="text-left">{$collision.name|escape:'htmlall':'UTF-8'}</td>
						<td class="text-left">{$collision.link_rewrite|escape:'htmlall':'UTF-8'}</td>
						<td class="text-center">{if $langs_active > 1}{$collision.times|escape:'htmlall':'UTF-8' - $langs_active|escape:'htmlall':'UTF-8'}{else}{$collision.times|escape:'htmlall':'UTF-8'}{/if}</td>
					</tr>
				{/foreach}
			{/if}
		</tbody>
	</table>
</div>
</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-cogs"></i> Category URLs</div>
	<div class="row row-margin-bottom">
	<table class="table">
		<thead>
			<tr>
				<th class="text-left"><span class="title_box active">ID</span></th>
				<th class="text-left"><span class="title_box active">Name</span></th>
				<th class="text-left"><span class="title_box active" style="color: red">Link Rewrite</span></th>
				<th class="text-center"><span class="title_box active">Occurance</span></th>
			</tr>
		</thead>
		<tbody>
			{if empty($category_coll)}
				<tr><td>No Collisions found</td></tr>
			{else}
			{foreach from=$category_coll item=collision}
				<tr>
					<td class="text-left">{$collision.id_category|escape:'htmlall':'UTF-8'}</td>
					<td class="text-left">{$collision.name|escape:'htmlall':'UTF-8'}</td>
					<td class="text-left">{$collision.link_rewrite|escape:'htmlall':'UTF-8'}</td>
					<td class="text-center">{if $langs_active > 1}{$collision.times|escape:'htmlall':'UTF-8' - $langs_active|escape:'htmlall':'UTF-8'}{else}{$collision.times|escape:'htmlall':'UTF-8'}{/if}</td>
				</tr>
			{/foreach}
			{/if}
		</tbody>
	</table>
</div>
</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-cogs"></i> Conflicts Between Category and Product URLs</div>
	<div class="row row-margin-bottom">
	<table class="table">
		<thead>
			<tr>
				<th class="text-left"><span class="title_box active">ID Product</span></th>
				<th class="text-left"><span class="title_box active">ID Category</span></th>
				<th class="text-left"><span class="title_box active" style="color: red">Shared Link Rewrite</span></th>
			</tr>
		</thead>
		<tbody>
			{if empty($compare_coll)}
				<tr><td>No Collisions found</td></tr>
			{else}
			{foreach from=$compare_coll item=collision}
				<tr>
					<td class="text-left">{$collision.id_product|escape:'htmlall':'UTF-8'}</td>
					<td class="text-left">{$collision.id_category|escape:'htmlall':'UTF-8'}</td>
					<td class="text-left">{$collision.link_rewrite|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/foreach}
			{/if}
		</tbody>
	</table>
</div>
</div>
