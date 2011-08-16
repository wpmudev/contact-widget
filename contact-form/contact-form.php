<?php
/*
Plugin Name: Contact Form
Plugin URI: http://premium.wpmudev.org/project/contact-widget/
Description: Adds a contact form widget to your blog.
Author: AdamGold
Author URI: http://premium.wpmudev.org/
Version: 1.2.1
WDP ID: 151

Copyright 2009-2011 Incsub (http://incsub.com)

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

add_action('wp_ajax_cw_send_mail', 'cw_send_mail');
add_action('wp_ajax_nopriv_cw_send_mail', 'cw_send_mail');

function cw_send_mail () {
	parse_str($_POST['data'], $_POST);
	require_once(rtrim(dirname(__FILE__), '/') . '/scripts/ajax.php');
	die;
}

class Contact_form {
	function activate(){
		$data = array(
			'contact_form_widget_title' => 'Contact Form',
			'contact_form_subject_label' => 'Subject',
			'contact_form_from_label' => 'From',
			'contact_form_message_label' => 'Message',
			'contact_form_admin_email' => get_option('contact_form_admin_email'),
			'contact_form_success_message' => get_option('contact_form_success message'),
			'contact_form_text_after' => '',
			'contact_form_custom_css' => '',
			'contact_form_captcha' => 'on',
			'contact_form_compact' => '',
			'contact_form_refresh_message' => 'Click to refresh',
			'contact_form_refresh_link' => 'Refresh',
		);
		foreach( $data as $key => $value ) {
			update_option($key , $value);
		}
	}

	function deactivate(){
		$data = array(
			'contact_form_widget_title' => 'Contact Form',
			'contact_form_subject_label' => 'Subject',
			'contact_form_from_label' => 'From',
			'contact_form_message_label' => 'Message',
			'contact_form_admin_email' => get_option('contact_form_admin_email'),
			'contact_form_success_message' => get_option('contact_form_success message'),
			'contact_form_text_after' => '',
			'contact_form_custom_css' => '',
			'contact_form_captcha' => 'on',
			'contact_form_compact' => '',
			'contact_form_refresh_message' => 'Click to refresh',
			'contact_form_refresh_link' => 'Refresh',
		);
		foreach( $data as $key => $value ) {
			delete_option($key);
		}
	}

	function control(){
		$data = array(
			'contact_form_widget_title' => 'Contact Form',
			'contact_form_subject_label' => 'Subject',
			'contact_form_from_label' => 'From',
			'contact_form_message_label' => 'Message',
			'contact_form_admin_email' => get_option('contact_form_admin_email'),
			'contact_form_success_message' => get_option('contact_form_success message'),
			'contact_form_text_after' => '',
			'contact_form_custom_css' => '',
			'contact_form_captcha' => 'on',
			'contact_form_compact' => '',
			'contact_form_refresh_message' => 'Click to refresh',
			'contact_form_refresh_link' => 'Refresh',
		);
		foreach( $data as $key => $value ) {
			${$key} = get_option($key);
		}
		$captcha = ( $contact_form_captcha == 'on' ) ? 'checked="checked"' : '';
		$compact_mode = ( $contact_form_compact == 'on' ) ? 'checked="checked"' : '';
		?>
		<p>
			Widget title:<br />
			<input class="widefat" type="text" name="contact_form_widget_title" value="<?php echo $contact_form_widget_title; ?>" />
		</p>
		<p>
			Subject label:<br />
			<input class="widefat" type="text" name="contact_form_subject_label" value="<?php echo $contact_form_subject_label; ?>" />
		</p>
		<p>
			From label:<br />
			<input class="widefat" type="text" name="contact_form_from_label" value="<?php echo $contact_form_from_label; ?>" />
		</p>
		<p>
			Message label:<br />
			<input class="widefat" type="text" name="contact_form_message_label" value="<?php echo $contact_form_message_label; ?>" />
		</p>
		<p>
			Admin email (optional):<br />
			<input class="widefat" type="text" name="contact_form_admin_email" value="<?php echo $contact_form_admin_email; ?>" />
		</p>
		<p>
			Success message:<br />
			<input class="widefat" type="text" name="contact_form_success_message" value="<?php echo $contact_form_success_message; ?>" />
		</p>
		<p>
			Text after the form:<br />
			<textarea rows="10" cols="20" class="widefat" name="contact_form_text_after" tabindex="0"><?php echo $contact_form_text_after; ?></textarea>
		</p>
		<p>
			Custom CSS:<br />
			<textarea rows="10" cols="20" class="widefat" name="contact_form_custom_css" tabindex="0"><?php echo $contact_form_custom_css; ?></textarea>
		</p>
		<p>
			Enable CAPTCHA:
			<input type="checkbox" name="contact_form_captcha" <?php echo $captcha; ?> /> Enable
		</p>
		<p>
			Refresh CAPTCHA link:
			<input type="text" class="widefat" name="contact_form_refresh_link" value="<?php echo $contact_form_refresh_link; ?>" />
		</p>
		<p>
			Refresh CAPTCHA message:
			<input type="text" class="widefat" name="contact_form_refresh_message" value="<?php echo $contact_form_refresh_message; ?>" />
			<br />
			<small>This is the message that will appear when user rolls over CAPTCHA image</small>
		</p>
		<p>
			Compact mode:
			<input type="checkbox" name="contact_form_compact" <?php echo $compact_mode; ?> /> Enable
		</p>
		<?php
		if( isset($_POST['contact_form_widget_title']) ) {
			foreach( $data as $key => $value ) {
				update_option($key , $_POST[ $key ]);
			}
		}
	}

	function widget($args){
		$data = array(
			'contact_form_widget_title' => 'Contact Form',
			'contact_form_subject_label' => 'Subject',
			'contact_form_from_label' => 'From',
			'contact_form_message_label' => 'Message',
			'contact_form_admin_email' => get_option('admin_email'),
			'contact_form_text_after' => '',
			'contact_form_custom_css' => '',
			'contact_form_captcha' => 'on',
			'contact_form_compact' => '',
			'contact_form_refresh_message' => 'Click to refresh',
			'contact_form_refresh_link' => 'Refresh',
		);
		foreach( $data as $key => $value ) {
			${$key} = get_option($key);
		}
		$contact_form_refresh_message = $contact_form_refresh_message ? $contact_form_refresh_message : 'Click to refresh';
		$contact_form_refresh_link = $contact_form_refresh_link ? $contact_form_refresh_link : 'Refresh';
		$contact_form_compact = ('on' == $contact_form_compact) ? 1 : 0;

		$plugin_dir = rtrim(dirname(__FILE__), '/') . '/';
		echo $args['before_widget'];
		echo $args['before_title'] . $contact_form_widget_title . $args['after_title'];
		?>
		<script type="text/javascript">
			var _cw_ajaxurl = "<?php echo admin_url('/admin-ajax.php');?>";
			var _cw_compact = <?php echo (int)$contact_form_compact;?>;
			var _cw_refresh_message = "<?php echo $contact_form_refresh_message;?>";
		</script>
		<script type="text/javascript" src="<?php echo plugins_url('/scripts/js/global.js', __FILE__); ?>"></script>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo plugins_url('/style.css', __FILE__); ?>" />
		<?php if( get_option('contact_form_custom_css') ) { ?>
		<style type="text/css">
			<?php echo get_option('contact_form_custom_css'); ?>
		</style>
		<?php } ?>
		<div id="message"></div>
		<div id="form-fields">
			<form id="wp-contact-form" action="javascript:submit_form();">
			<label for="cw_subject"><?php echo $contact_form_subject_label; ?></label>
				<input class="text" type="text" name="subject" id="cw_subject" value=""><br />
			<label for="cw_email"><?php echo $contact_form_from_label; ?></label>
				<input class="text" type="text" name="email" id="cw_email" value=""><br />
			<label for="cw_message"><?php echo $contact_form_message_label; ?></label>
				<textarea name="message" id="cw_message" rows="5" cols="25"></textarea><br />
			<?php
			if( $contact_form_captcha == 'on' ) {
				if (!function_exists('_recaptcha_qsencode')) require_once( $plugin_dir . 'scripts/recaptchalib.php' );
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
					<div id="cw_refresh"><a href="javascript:Recaptcha.reload()"><span><?php echo $contact_form_refresh_link;?></span></a></div>
					<div class="recaptcha_only_if_incorrect_sol" style="color: red;">Incorrect please try again</div>
					<label class="recaptcha_only_if_image">Enter the words above:</label>
					<label class="recaptcha_only_if_audio">Enter the numbers you hear:</label>
					<input id="recaptcha_response_field" name="recaptcha_response_field" type="text">
					<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=<?php echo $publickey;?>&lang=en"></script>
					<noscript>
						<iframe src="http://api.recaptcha.net/noscript?k=<?php echo $publickey;?>&lang=en" height="200" width="200" frameborder="0"></iframe>
						<textarea name="recaptcha_challenge_field" rows="3" cols="25"></textarea>
						<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
					</noscript>
				</div>
			<?php } ?>
			<label>&nbsp;</label>
				<input class="button" type="submit" name="submit" value="Send Message">
			</form>
		</div>
		<?php
		echo $contact_form_text_after;
		//echo '<a href="http://www.google.com/recaptcha">reCAPTCHA</a>';
		echo $args['after_widget'];
	}

	function register(){
		register_sidebar_widget('Contact Form', array('Contact_form', 'widget'));
		register_widget_control('Contact Form', array('Contact_form', 'control'));
	}
} // End class

if ( !function_exists( 'wdp_un_check' ) ) {
  add_action( 'admin_notices', 'wdp_un_check', 5 );
  add_action( 'network_admin_notices', 'wdp_un_check', 5 );
  function wdp_un_check() {
    if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
      echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</p></div>';
  }
}
