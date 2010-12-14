<?php
/*
Plugin Name: Contact Form
Plugin URI: http://premium.wpmudev.org/
Description: Adds a contact form widget to your blog.
Author: AdamGold
Author URI: http://themeforest.net/user/AdamGold/?ref=AdamGold
Version: 1.0
WPD ID: 151

Copyright 2009-2010 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
function add_jquery_support() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
}  
add_action('init', 'add_jquery_support');
*/

require( ABSPATH . 'wp-includes/pluggable.php');
wp_enqueue_script("jquery");

add_action("widgets_init", array('Contact_form', 'register'));
register_activation_hook( __FILE__, array('Contact_form', 'activate'));
register_deactivation_hook( __FILE__, array('Contact_form', 'deactivate'));
class Contact_form {
  function activate(){
      $data = array( 'contact_form_widget_title' => 'Contact Form', 'contact_form_subject_label' => 'Subject',
    'contact_form_from_label' => 'From', 'contact_form_message_label' => 'Message', 'contact_form_admin_email' => get_option('contact_form_admin_email'),
    'contact_form_success_message' => get_option('contact_form_success message'), 'contact_form_text_after' => '',
    'contact_form_custom_css' => '', 'contact_form_captcha' => 'on');
    foreach( $data as $key => $value )
    {
    	update_option($key , $value);
    }
  }
  function deactivate(){
      $data = array( 'contact_form_widget_title' => 'Contact Form', 'contact_form_subject_label' => 'Subject',
    'contact_form_from_label' => 'From', 'contact_form_message_label' => 'Message', 'contact_form_admin_email' => get_option('contact_form_admin_email'),
    'contact_form_success_message' => get_option('contact_form_success message'), 'contact_form_text_after' => '',
    'contact_form_custom_css' => '', 'contact_form_captcha' => 'on');
    foreach( $data as $key => $value )
    {
    	delete_option($key);
    }
  }
  function control(){
      $data = array( 'contact_form_widget_title' => 'Contact Form', 'contact_form_subject_label' => 'Subject',
    'contact_form_from_label' => 'From', 'contact_form_message_label' => 'Message', 'contact_form_admin_email' => get_option('contact_form_admin_email'),
    'contact_form_success_message' => get_option('contact_form_success message'), 'contact_form_text_after' => '',
    'contact_form_custom_css' => '', 'contact_form_captcha' => 'on');
    foreach( $data as $key => $value )
    {
    	${$key} = get_option($key);
    }
    $captcha = ( $contact_form_captcha == 'on' ) ? ' checked="checked"' : '';
    ?>
    <p>Widget title:<br />
    <input class="widefat" type="text" name="contact_form_widget_title" value="<?php echo $contact_form_widget_title; ?>" /></p>
    <p>Subject label:<br />
    <input class="widefat" type="text" name="contact_form_subject_label" value="<?php echo $contact_form_subject_label; ?>" /></p>
    <p>From label:<br />
    <input class="widefat" type="text" name="contact_form_from_label" value="<?php echo $contact_form_from_label; ?>" /></p>
    <p>Message label:<br />
    <input class="widefat" type="text" name="contact_form_message_label" value="<?php echo $contact_form_message_label; ?>" /></p>
    <p>Admin email (optional):<br />
    <input class="widefat" type="text" name="contact_form_admin_email" value="<?php echo $contact_form_admin_email; ?>" /></p>
    <p>Success message:<br />
    <input class="widefat" type="text" name="contact_form_success_message" value="<?php echo $contact_form_success_message; ?>" /></p>
    <p>Text after the form:<br />
    <textarea rows="10" cols="20" class="widefat" name="contact_form_text_after" tabindex="0">
    <?php echo $contact_form_text_after; ?>
    </textarea></p>
    <p>
    Custom CSS:<br />
    <textarea rows="10" cols="20" class="widefat" name="contact_form_custom_css" tabindex="0">
    <?php echo $contact_form_custom_css; ?>
    </textarea>
    </p>
    <p>Enable CAPTCHA:
    <input type="checkbox" name="contact_form_captcha"<?php echo $captcha; ?> /> Enable</p>
    <?php
if( isset($_POST['contact_form_widget_title']) )
{
	foreach( $data as $key => $value )
	{
		update_option($key , $_POST[ $key ]);
	}
}
	
  }
function widget($args){
      $data = array( 'contact_form_widget_title' => 'Contact Form', 'contact_form_subject_label' => 'Subject',
    'contact_form_from_label' => 'From', 'contact_form_message_label' => 'Message', 'contact_form_admin_email' => get_option('admin_email'),
    'contact_form_text_after' => '', 'contact_form_custom_css' => '', 'contact_form_captcha' => 'on');
    foreach( $data as $key => $value )
    {
    	${$key} = get_option($key);
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/contact-form/';
    echo $args['before_widget'];
    echo $args['before_title'] . $contact_form_widget_title . $args['after_title'];
?>
<script type="text/javascript" src="<?php bloginfo('url'); ?>/wp-content/plugins/contact-form/scripts/js/global.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('url'); ?>/wp-content/plugins/contact-form/style.css" /> 
<?php 
if( get_option('contact_form_custom_css') )
{
	?>
	<style type="text/css">
	<?php echo get_option('contact_form_custom_css'); ?>
	</style>
	<?php 
}
?>
<div id="message"></div>
<div id="form-fields">
<form id="wp-contact-form" action="javascript:submit_form();">
<label><?php echo $contact_form_subject_label; ?></label><input class="text" type="text" name="subject" value=""><br />
<label><?php echo $contact_form_from_label; ?></label><input class="text" type="text" name="email" value=""><br />
<label><?php echo $contact_form_message_label; ?></label><textarea name="message" rows="5" cols="25"></textarea><br />
   <?php
if( $contact_form_captcha == 'on' )
{
     require_once( $plugin_dir . 'scripts/recaptchalib.php' );
     $publickey = "6LcHObsSAAAAAIfHD_wQ92EWdOV0JTcIN8tYxN07";
     $privatekey = "6LcHObsSAAAAAJpBq0g501raPqH7koKyU-Po8RLL";
   ?>
<script type= "text/javascript">

var RecaptchaOptions = {
theme: 'custom',
lang: 'en',
custom_theme_widget: 'recaptcha_widget'
};

</script>
<div id="recaptcha_widget" style="display: none;">
<div id="recaptcha_image"></div>
<div class="recaptcha_only_if_incorrect_sol" style="color: red;">Incorrect please try again</div>
<label class="recaptcha_only_if_image">Enter the words above:</label>
<label class="recaptcha_only_if_audio">Enter the numbers you hear:</label>
<input id="recaptcha_response_field" name="recaptcha_response_field" type="text">

<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=<?php echo $publickey;?>&lang=en"></script>

<noscript>
<iframe src="http://api.recaptcha.net/noscript?k=<?php echo $publickey;?>&lang=en" height="200" width="200" frameborder="0"></iframe>
<textarea name="recaptcha_challenge_field" rows="3" cols="25"></textarea>
<input type="'hidden'" name="'recaptcha_response_field'" value="'manual_challenge'">
</noscript>
</div>
<?php } ?>
<label>&nbsp;</label><input class="button" type="submit" name="submit" value="Send Message">
</form>
</div>
<?php
    echo $contact_form_text_after;
echo '<a href="http://www.google.com/recaptcha">reCAPTCHA</a>';
    echo $args['after_widget'];
}
  function register(){
    register_sidebar_widget('Contact Form', array('Contact_form', 'widget'));
    register_widget_control('Contact Form', array('Contact_form', 'control'));
  }
}
?>