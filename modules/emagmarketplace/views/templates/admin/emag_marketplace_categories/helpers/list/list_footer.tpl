{extends file="helpers/list/list_footer.tpl"}

{block name="after"} 
	<script type="text/javascript">
	$(document).ready(function()
	{
		var emag_category_definitions = [
			{foreach $emag_category_definitions key=key item=category_definition}
				"{$category_definition.emag_category_id} - {$category_definition.emag_category_name}",
			{/foreach}
		]
		$('[name^="emag_category_id"]').autocomplete(emag_category_definitions, {
			minChars: 1,
			matchContains: true
		});
	});
	</script>
{/block}