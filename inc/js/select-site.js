jQuery(document).ready(function($) {
	$('#pbrx-form').submit(function() {
		$('#pbrx_loading').show();
		$('#pbrx_submit').attr('disabled', true);
		// $('#pbrx_svalue').val();

      data = {
      	action: 'pbrx_get_results',
      	pbrx_nonce: pbrx_vars.pbrx_nonce,
      	site: $('.site-dropdown-select').val()
      };

     	$.post(ajaxurl, data, function (response) {
			$('#pbrx_results').html(response);
			$('#pbrx_loading').hide();
			$('#pbrx_submit').attr('disabled', false);
		});	
		
		return false;
	});
});
