/**
 Author: Bl Modules
 Email: blmodules@gmail.com
 Page: http://www.blmodules.com
 */
$(document).ready(function()
{	
	$('#affiliate_price_url').click(function(){
		$('#affiliate_price_url_list').slideToggle('fast');
		$('#multistore_url_list').hide();
	});
	
	$('#multistore_url').click(function(){
		$('#multistore_url_list').slideToggle('fast');
		$('#affiliate_price_url_list').hide();
	});
	
	$('.multistore_url_checkbox').change(function() {
		var div_id = $(this).attr('id');
		var url = $('#feed_url').val().split('&multistore');
		
		if (div_id == 'all_multistore') {
			$('.multistore_url_checkbox').prop('checked', false);
			$('#all_multistore').prop('checked', true);
			$('#feed_url').val(url[0]);
		} else if (div_id == 'domain_multistore') {
			$('.multistore_url_checkbox').prop('checked', false);
			$('#domain_multistore').prop('checked', true);
			$('#feed_url').val(url[0]+'&multistore=auto');
		} else {
			$('#all_multistore').prop('checked', false);	
			$('#domain_multistore').prop('checked', false);			
			var count_checked = $('.multistore_url_checkbox:checked').length;			
			
			if (count_checked > 0) {
				url[0] = url[0]+'&multistore=';
				
				$('.multistore_url_checkbox:checked').each(function() {
				   url[0] = url[0]+this.value+',';
				})				
				
				$('#feed_url').val(url[0].slice(0,-1));
			} else {
				$('#feed_url').val(url[0]);
			}
		}
	});
});	