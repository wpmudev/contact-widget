(function ($) {

function cw_submit_form () {
	var $me = jQuery(this);
	var $form = $me.parents("form.wp-contact-form");
	var str = $form.serialize();
	jQuery.post(_cw_ajaxurl, {
		"action": "cw_send_mail",
		"data": str
	}, function (data) {
		var status = 0;
		var msg = '';
		try { status = parseInt(data.status, 10); } catch (e) { status = 0; }
		try { msg = data.message; } catch (e) { msg = ''; }
		if (status) {
			jQuery(':input',$form).not(':button, :submit, :reset, :hidden').val('');
			$form.trigger('cw-mail_sent');
		}
		$form.find('.cw-message').html(msg);
		if (typeof Recaptcha != 'undefined') Recaptcha.reload();
	}, 'json');
	return false;
}

function cw_spawn_captcha () {
	if (typeof Recaptcha == "undefined") return;

	var $me = $(this);
	var $new = $me.parents("form.wp-contact-form");
	if (!$new.is(".cw-has_captcha")) return;

	var $old = $("#cw-recaptcha_widget").parents("form.wp-contact-form");
	if ($new.attr("id") == $old.attr("id")) return;

	Recaptcha.destroy();
	$new.append($("#cw-recaptcha_widget"));
	if ($new.is(".cw-compact_form")) $("#cw_refresh").hide();
	else  $("#cw_refresh a").text($new.find(".cw-refresh_link").val()).show();
	Recaptcha.create(
		'6LcHObsSAAAAAIfHD_wQ92EWdOV0JTcIN8tYxN07',
		'cw-recaptcha_widget',
		RecaptchaOptions
	);
	$("#recaptcha_image").attr("title", $new.find(".cw-refresh_message").val());
}


$(function () {

if (typeof Recaptcha != "undefined") {
	$("#recaptcha_image").click(Recaptcha.reload);
}

// Initialize forms
$("form.wp-contact-form").each(function () {
	var $form = $(this);
	if (!$form.length) return true;

	if ($form.find("#cw-recaptcha_widget").length) {
		if ($form.is(".cw-compact_form")) $("#cw_refresh").hide();
		else $("#cw_refresh a").text($form.find(".cw-refresh_link").val()).show();

		$("#recaptcha_image").attr("title", $form.find(".cw-refresh_message").val());
	}
	$form.find('input:button[name="submit"]').bind('click', cw_submit_form); // Bind submission
	$form.find(":input").bind('focus', cw_spawn_captcha);

	if (!$form.is(".cw-compact_form")) return true; // Compacting forms below

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
				if ($obj.val() == $me.text()) $obj.val('');
				$obj.removeClass('cw_inactive');
			})
			.blur(function () {
				if ($obj.val()) return true;
				$obj.addClass('cw_inactive').val($me.text());
			})
		;
	});
});


});
})(jQuery);
