jQuery.noConflict();
	function submit_form()
	{
			var str = jQuery('#wp-contact-form').serialize();
			jQuery.ajax({
			type: "POST",
			url: "wp-content/plugins/contact-form/scripts/ajax.php",
			data: str,
			success: function(msg){
			jQuery("#message").ajaxComplete(function(event, request, settings){
			result = msg;
			if( result.search('Please') == 0 || result.search('Please') == -1 )
			{
				jQuery(':input','#wp-contact-form').not(':button, :submit, :reset, :hidden').val('');
			}
			jQuery('#message').html(result);
			});
			}
			});
	}