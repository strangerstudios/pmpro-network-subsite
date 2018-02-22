jQuery(document).ready(function($) {
	$('#select-site-form').submit(function() {
		$('#select_site_loading').show();
		$('#select_site_submit').attr('disabled', true);

      data = {
      	action: 'select_site_get_results',
      	select_site_nonce: select_site_vars.select_site_nonce,
      	site: $('.site-dropdown-select').val()
      };

     	$.post(ajaxurl, data, function (response) {
			$('#select_site_results').html(response);
			$('#select_site_loading').hide();
			$('#select_site_submit').attr('disabled', false);
		});	
		
		return false;
	});
});
