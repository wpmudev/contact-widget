<?php

require('../../../../wp-load.php');
require_once('recaptchalib.php');
$privatekey = "6LcHObsSAAAAAJpBq0g501raPqH7koKyU-Po8RLL";
$resp = recaptcha_check_answer ($privatekey,
                               $_SERVER["REMOTE_ADDR"],
                               $_POST["recaptcha_challenge_field"],
                               $_POST["recaptcha_response_field"]);

$post = (!empty($_POST)) ? true : false;

function ValidateEmail($email)
{

$regex = '/([a-z0-9_.-]+)'.

'@'.

'([a-z0-9.-]+){1,255}'.

'.'.

"([a-z]+){2,10}/i";

if($email == '') {
	return false;
}
else {
$eregi = preg_replace($regex, '', $email);
}

return empty($eregi) ? true : false;
}

if($post)
{

$email = $_POST['email'];
$subject = stripslashes($_POST['subject']);
$message = stripslashes($_POST['message']);

$error = '';

if(!$subject)
{
	$error .= '<p>Please enter a subject.</p>';
}

if(!$email)
{
$error .= '<p>Please enter an e-mail address.</p>';
}

if($email && !ValidateEmail($email))
{
$error .= '<p>Please enter a valid e-mail address.</p>';
}

if( get_option('contact_form_captcha') == 'on' )
{
	if (!$resp->is_valid)
	{
		$error .= '<p>Please enter a valid captcha.</p>';
	}
}
if(!$error)
{
//echo get_option('contact_form_admin_email');
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

$headers .= 'To: Site admin <' . get_option('contact_form_admin_email') . '>' . "\r\n";
$headers .= 'From: <' . $email . '>' . "\r\n";
$headers .= 'Reply-To: <' . $email . '>' . "\r\n";

$mail = wp_mail(get_option('contact_form_admin_email'), $subject, $message, $headers);
if($mail)
{
	$success = ( get_option('contact_form_success_message') ) ? get_option('contact_form_success_message') : 'Your message has been sent. Thank you!';
	echo $success;
}
}
else
{
	echo $error;
}

}

?>