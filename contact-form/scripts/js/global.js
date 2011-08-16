jQuery.noConflict();

function submit_form() {
	var str = jQuery('#wp-contact-form').serialize();
	jQuery.post(_cw_ajaxurl, {
		"action": "cw_send_mail",
		"data": str
	}, function(msg){
		jQuery("#message").ajaxComplete(function(event, request, settings){
			result = msg;
			if( result.search('Please') == 0 || result.search('Please') == -1 ) {
				jQuery(':input','#wp-contact-form').not(':button, :submit, :reset, :hidden').val('');
			}
			jQuery('#message').html(result);
			if (typeof Recaptcha != 'undefined') Recaptcha.reload();
		});
	});
}

(function ($) {
$(function () {
	if (typeof Recaptcha != 'undefined') {
		$("#recaptcha_image").attr("title", _cw_refresh_message).click(Recaptcha.reload);
		if (_cw_compact) $("#cw_refresh").hide();
	}
	
	if (!_cw_compact) return false;
	var $form = $("#wp-contact-form");
	if (!$form.length) return false;

	$form.find("label").each(function () {
		var $me = $(this);
		$me.hide();
		if (!$me.attr("for")) return true;
		var $obj = $("#" + $me.attr("for"));
		if (!$obj.length) return true;
		$obj
			.addClass('cw_inactive')
			.val($me.text())
			.focus(function () {
				$obj.val('').removeClass('cw_inactive');
			})
			.blur(function () {
				if ($obj.val()) return true;
				$obj.addClass('cw_inactive').val($me.text());
			})
		;
	});
});
})(jQuery);