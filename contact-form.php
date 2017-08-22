<?php
/*
Plugin Name: Contact Form
Plugin URI: http://premium.wpmudev.org/project/contact-widget
Description: Adds a contact form widget to your blog.
Text Domain: contact_widget
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Version: 2.2.1-BETA-1
WDP ID: 151

Copyright 2009-2011 Incsub (http://incsub.com)
Author - AdamGold
Contributor - Ve Bailovity (Incsub), Umesh Kumar
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

define ( 'WC_PLUGIN_SELF_DIRNAME', basename( dirname( __FILE__ ) ), true );
if ( ! defined( 'WC_PLUGIN_BASE_DIR' ) ) {
	define ( 'WC_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WC_PLUGIN_SELF_DIRNAME, true );
}

if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
	$textdomain_handler = 'load_muplugin_textdomain';
} else {
	$textdomain_handler = 'load_plugin_textdomain';
}
$textdomain_handler( 'contact_widget', false, basename( dirname( __FILE__ ) ) . '/languages/' );


add_action( "widgets_init", array( 'Contact_form', 'register' ) );

add_action( 'wp_ajax_cw_send_mail', 'cw_send_mail' );
add_action( 'wp_ajax_nopriv_cw_send_mail', 'cw_send_mail' );

function cw_send_mail() {
	parse_str( $_POST['data'], $_POST );
	require_once( rtrim( dirname( __FILE__ ), '/' ) . '/scripts/ajax.php' );
	die;
}

class Contact_form extends WP_Widget {

	private $_kses = array(
		'contact_form_subject_label',
		'contact_form_from_label',
		'contact_form_message_label',
		'contact_form_refresh_link',
		'contact_form_submit_label',
		'contact_form_text_after',
		'contact_form_response_field',
		'contact_form_public_key',
		'contact_form_private_key',
	);

	public function __construct() {
		$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Contact Form', 'contact_widget' ) );
		parent::__construct( __CLASS__, __( 'Contact Form', 'contact_widget' ), $widget_ops );

		add_action( 'admin_print_scripts-widgets.php', array( $this, 'js_load_scripts' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_print_scripts', array( $this, 'enqueue_frontend_dependencies' ) );
		}
	}

	function enqueue_frontend_dependencies() {
		if ( ! is_active_widget( false, false, $this->id_base ) ) {
			return false;
		}
		if ( ! defined( 'CW_SCRIPT_INCLUDED' ) ) {
			wp_enqueue_script( 'contact-form', plugins_url( '/scripts/js/global.js', __FILE__ ), array( 'jquery' ) );
			define( 'CW_SCRIPT_INCLUDED', true, true );
		}
		if ( ! defined( 'CW_STYLE_INCLUDED' ) ) {
			wp_enqueue_style( 'contact-form', plugins_url( '/style.css', __FILE__ ) );
			define( 'CW_STYLE_INCLUDED', true, true );
		}
	}

	function js_load_scripts() {
		wp_enqueue_script( 'contact_widget-admin', plugins_url( '/scripts/js/admin.js', __FILE__ ), array( 'jquery' ) );
	}

	public static function register() {
		register_widget( __CLASS__ );
	}

	public static function get_instance_data( $instance_id ) {
		$instances = get_option( 'widget_' . __CLASS__ );
		$data      = isset( $instances[ $instance_id ] ) ? $instances[ $instance_id ] : array();

		return $data;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	function form( $instance ) {
		$data = $this->_get_data( $instance );
		extract( $data );

		$hostname                           = preg_replace( '/^www\./', '', parse_url( site_url(), PHP_URL_HOST ) );
		$captcha                            = ( $contact_form_captcha == 'on' ) ? 'checked="checked"' : '';
		$compact_mode                       = ( $contact_form_compact == 'on' ) ? 'checked="checked"' : '';
		$contact_form_generic_from          = ( $contact_form_generic_from == 'on' ) ? 'checked="checked"' : '';
		$contact_form_generic_from_reply_to = ( $contact_form_generic_from_reply_to == 'on' ) ? 'checked="checked"' : '';
		$contact_form_generic_from_body     = ( $contact_form_generic_from_body == 'on' ) ? 'checked="checked"' : '';
		?>
		<p>
			<b><?php _e( 'Widget title:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_widget_title' ); ?>" value="<?php echo esc_attr( $contact_form_widget_title ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'Subject label:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_subject_label' ); ?>" value="<?php echo esc_attr( $contact_form_subject_label ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'From label:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_from_label' ); ?>" value="<?php echo esc_attr( $contact_form_from_label ); ?>"/>
		</p>
		<p class="cw_generic_from_container">
			<b><?php _e( 'Use generic &quot;from&quot; address:', 'contact_widget' ); ?></b><br/>
			<input type="hidden" name="<?php echo $this->get_field_name( 'contact_form_generic_from' ); ?>" value="0"/>
			<input type="checkbox" class="cw-contact_form_generic_from" name="<?php echo $this->get_field_name( 'contact_form_generic_from' ); ?>" <?php echo $contact_form_generic_from; ?> />
			<label><b><?php _e( 'Enable', 'contact_widget' ); ?></b></label>
			<br/>
				<span class="cw_generic_from_options" style="display:none">
					<input type="text" name="<?php echo $this->get_field_name( 'contact_form_generic_from_user' ); ?>" size="7" value="<?php echo esc_attr( $contact_form_generic_from_user ); ?>"/>
					@<?php echo $hostname; ?>
					<br/>
					<input type="hidden" name="<?php echo $this->get_field_name( 'contact_form_generic_from_reply_to' ); ?>" value="0"/>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_form_generic_from_reply_to' ); ?>" <?php echo $contact_form_generic_from_reply_to; ?> />
						<label><?php _e( 'Use senders email as Reply-To address?', 'contact_widget' ); ?></label>
					<br/>
					<input type="hidden" name="<?php echo $this->get_field_name( 'contact_form_generic_from_body' ); ?>" value="0"/>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_form_generic_from_body' ); ?>" <?php echo $contact_form_generic_from_body; ?> />
						<label><?php _e( 'Add senders email to message body?', 'contact_widget' ); ?></label>
				</span>
		</p>
		<p>
			<b><?php _e( 'Message label:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_message_label' ); ?>" value="<?php echo esc_attr( $contact_form_message_label ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'Send Message button text:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_submit_label' ); ?>" value="<?php echo esc_attr( $contact_form_submit_label ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'Admin email (optional):', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_admin_email' ); ?>" value="<?php echo esc_attr( $contact_form_admin_email ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'Success message:', 'contact_widget' ); ?></b><br/>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_success_message' ); ?>" value="<?php echo esc_attr( $contact_form_success_message ); ?>"/>
		</p>
		<p>
			<b><?php _e( 'Text after the form:', 'contact_widget' ); ?></b><br/>
			<textarea rows="10" cols="20" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_text_after' ); ?>" tabindex="0"><?php echo $contact_form_text_after; ?></textarea>
		</p>
		<p>
			<b><?php _e( 'Custom CSS:', 'contact_widget' ); ?></b><br/>
			<textarea rows="10" cols="20" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_custom_css' ); ?>" tabindex="0"><?php echo $contact_form_custom_css; ?></textarea>
		</p>
		<p class="cw_captcha">
			<b><?php _e( 'Enable CAPTCHA:', 'contact_widget' ); ?></b>
			<br/>
			<input type="hidden" name="<?php echo $this->get_field_name( 'contact_form_captcha' ); ?>" value="0"/>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_form_captcha' ); ?>" <?php echo $captcha; ?> /> <?php _e( 'Enable', 'contact_widget' ); ?>
			<br/>
			<span class="cw_captcha_keys" style="display:none">
				<br/>
				<?php printf(
					__( 'To start using ReCaptcha protection, you will first need to <a href="%s" target="_blank">generate a set of API keys here</a>.', 'contact_widget' ),
					'https://www.google.com/recaptcha/admin#list'
				); ?>
				<br/>
				<?php if ( ! empty( $captcha ) && ( empty( $contact_form_public_key ) || empty( $contact_form_private_key ) ) ) { ?>
					<div class="error below-h2"><?php _e( 'Please, remember that your submissions will <b>not</b> be protected until you set up the API keys.', 'contact_widget' ); ?></div>
				<?php } ?>
				<br/>
				<b><?php _e( 'Site Key', 'contact_widget' ); ?></b>
				<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_public_key' ); ?>" value="<?php echo esc_attr( $contact_form_public_key ); ?>"/>
				<br/>
				<b><?php _e( 'Secret Key', 'contact_widget' ); ?></b>
				<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'contact_form_private_key' ); ?>" value="<?php echo esc_attr( $contact_form_private_key ); ?>"/>
				<br/>
			</span>
		</p>
		<div class="recaptcha-version">
			<p>
				<b><?php _e( 'ReCaptcha Version:', 'contact_widget' ); ?></b>
				<br/>
				<label>
					<input type="radio" class="widefat old-recaptcha recaptcha-version" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_version' ); ?>" value="old" <?php checked( 'old', $contact_form_recaptcha_version, true ); ?> />
					<?php _e( 'Old', 'contact_widget' ); ?>
				</label>
				<br/>
				<label>
					<input type="radio" class="widefat new-recaptcha recaptcha-version" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_version' ); ?>" value="new" <?php checked( 'new', $contact_form_recaptcha_version, true ); ?> />
					<?php _e( 'New', 'contact_widget' ); ?>
				</label>
			</p>
		</div>
		<div class="old-recaptcha-settings<?php echo $contact_form_recaptcha_version != 'old' ? ' hidden' : ''; ?>">
			<p>
				<b><?php _e( 'Refresh CAPTCHA link:', 'contact_widget' ); ?></b>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_refresh_link' ); ?>" value="<?php echo esc_attr( $contact_form_refresh_link ); ?>"/>
			</p>

			<p>
				<b><?php _e( 'Refresh CAPTCHA message:', 'contact_widget' ); ?></b>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_refresh_message' ); ?>" value="<?php echo esc_attr( $contact_form_refresh_message ); ?>"/>
				<br/>
				<small><?php _e( 'This is the message that will appear when user rolls over CAPTCHA image', 'contact_widget' ); ?></small>
			</p>
			<p>
				<b><?php _e( 'Captcha input label:', 'contact_widget' ); ?></b>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_response_field' ); ?>" value="<?php echo esc_attr( $contact_form_response_field ); ?>"/>
			</p>
		</div>
		<div class="new-recaptcha-settings<?php echo $contact_form_recaptcha_version != 'new' ? ' hidden' : ''; ?>">
			<p>
				<b><?php _e( 'Recaptcha Theme:', 'contact_widget' ); ?></b>
				<br/>
				<label>
					<input type="radio" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_theme' ); ?>" value="light" <?php checked( 'light', $contact_form_recaptcha_theme, true ); ?>/>
					<?php _e( 'Light', 'contact_widget' ); ?>
				</label>
				<br/>
				<label>
					<input type="radio" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_theme' ); ?>" value="dark" <?php checked( 'dark', $contact_form_recaptcha_theme, true ); ?>/>
					<?php _e( 'Dark', 'contact_widget' ); ?>
				</label>
			</p>

			<p>
				<b><?php _e( 'Recaptcha Type:', 'contact_widget' ); ?></b>
				<br/>
				<label>
					<input type="radio" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_type' ); ?>" value="image" <?php checked( 'image', $contact_form_recaptcha_type, true ); ?>/>
					<?php _e( 'Image', 'contact_widget' ); ?>
				</label>
				<br/>
				<label>
					<input type="radio" class="widefat" name="<?php echo $this->get_field_name( 'contact_form_recaptcha_type' ); ?>" value="audio" <?php checked( 'audio', $contact_form_recaptcha_type, true ); ?>/>
					<?php _e( 'Audio', 'contact_widget' ); ?>
				</label>
			</p>
		</div>
		<p>
			<b><?php _e( 'Compact mode:', 'contact_widget' ); ?></b>
			<br/>
			<input type="hidden" name="<?php echo $this->get_field_name( 'contact_form_compact' ); ?>" value="0"/>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_form_compact' ); ?>" <?php echo $compact_mode; ?> /> <?php _e( 'Enable', 'contact_widget' ); ?>
		</p>
	<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	function update( $new_instance, $old_instance ) {
		$instance = array();
		foreach ( $new_instance as $key => $value ) {
			$instance[ $key ] = in_array( $key, $this->_kses ) ? wp_kses_post( $value ) : strip_tags( $value );
			if ( $key == 'contact_form_private_key' ) {
				update_option( 'wpmu_contact_form_private_key', $value );
			} elseif ( $key == 'contact_form_recaptcha_version' ) {
				update_option( 'wpmu_contact_form_recaptcha_version', $value );
			}
		}

		return $this->_get_data( $instance );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		$data      = $this->_get_data( $instance );
		$cw_uniqid = md5( serialize( $data ) . uniqid() );
		extract( $data );
		$contact_form_refresh_message   = ! empty( $contact_form_refresh_message ) ? $contact_form_refresh_message : __( 'Click to refresh', 'contact_widget' );
		$contact_form_refresh_link      = ! empty( $contact_form_refresh_link ) ? $contact_form_refresh_link : __( 'Reload Captcha', 'contact_widget' );
		$contact_form_submit_label      = ! empty( $contact_form_submit_label ) ? $contact_form_submit_label : $data['contact_form_submit_label'];
		$contact_form_response_field    = ! empty( $contact_form_response_field ) ? $contact_form_response_field : __( 'Type the characters you see in image above', 'contact_widget' );
		$contact_form_recaptcha_version = ! empty( $contact_form_recaptcha_version ) ? $contact_form_recaptcha_version : 'old';
		$contact_form_recaptcha_theme   = ! empty( $contact_form_recaptcha_theme ) ? $contact_form_recaptcha_theme : 'light';
		$contact_form_recaptcha_type    = ! empty( $contact_form_recaptcha_type ) ? $contact_form_recaptcha_type : 'image';

		$contact_form_compact = ( 'on' == $contact_form_compact ) ? 1 : 0;
		$contact_form_captcha = ( 'on' == $contact_form_captcha ) ? 1 : 0;
		$publickey            = $contact_form_public_key;
		//Reduce number of rows for textare in 2013
		$theme = wp_get_theme();
		$rows  = 5;
		$cols  = 25;
		if ( trim( $theme ) == 'Twenty Thirteen' ) {
			$rows = 2;
			$cols = 20;
		}

		$plugin_dir = rtrim( dirname( __FILE__ ), '/' ) . '/';
		echo $args['before_widget'];
		echo $args['before_title'] . esc_html( $contact_form_widget_title ) . $args['after_title'];
		?>
		<script type="text/javascript">
			var _cw_ajaxurl = "<?php echo admin_url('/admin-ajax.php');?>";
		</script>
		<?php if ( ! defined( 'CW_SCRIPT_INCLUDED' ) && ( defined( 'CW_FORCED_DEPENDENCIES_LOADING' ) && CW_FORCED_DEPENDENCIES_LOADING ) ) { ?>
			<script type="text/javascript" src="<?php echo plugins_url( '/scripts/js/global.js', __FILE__ ); ?>"></script>
			<?php
			define( 'CW_SCRIPT_INCLUDED', true );
		}
		?>
		<?php if ( ! defined( 'CW_STYLE_INCLUDED' ) && ( defined( 'CW_FORCED_DEPENDENCIES_LOADING' ) && CW_FORCED_DEPENDENCIES_LOADING ) ) { ?>
			<link rel="stylesheet" type="text/css" media="all" href="<?php echo plugins_url( '/style.css', __FILE__ ); ?>"/>
			<?php
			define( 'CW_STYLE_INCLUDED', true );
		}
		?>
		<?php if ( $contact_form_custom_css ) { ?>
			<style type="text/css">
				<?php echo $contact_form_custom_css; ?>
			</style>
		<?php } ?>
		<div id="form-fields">
			<form class="wp-contact-form <?php echo( $contact_form_compact ? 'cw-compact_form' : '' ); ?> <?php echo( $contact_form_captcha ? 'cw-has_captcha' : '' ); ?>" id="cw-form-<?php echo $cw_uniqid; ?>">
				<?php do_action( 'contact_form-form_start', $cw_uniqid ); ?>
				<div class="cw-message">
					<?php do_action( 'contact_form-form_message', $cw_uniqid ); ?>
				</div>
				<input type="hidden" class="cw-refresh_message" value="<?php esc_attr_e( $contact_form_refresh_message ); ?>"/>
				<input type="hidden" class="cw-refresh_link" value="<?php esc_attr_e( $contact_form_refresh_link ); ?>"/>
				<input type="hidden" name="instance" value="<?php esc_attr_e( $this->number ); ?>"/>

				<?php do_action( 'contact_form-fields_start', $cw_uniqid ); ?>

				<label for="cw_subject-<?php echo $cw_uniqid; ?>"><?php echo wp_kses_post( $contact_form_subject_label ); ?></label>
				<input class="text" type="text" name="subject" id="cw_subject-<?php echo $cw_uniqid; ?>" value=""><br/>
				<?php do_action( 'contact_form-after_subject', $cw_uniqid ); ?>

				<label for="cw_email-<?php echo $cw_uniqid; ?>"><?php echo wp_kses_post( $contact_form_from_label ); ?></label>
				<input class="text" type="text" name="email" id="cw_email-<?php echo $cw_uniqid; ?>" value="">
				<br/>
				<?php do_action( 'contact_form-after_email', $cw_uniqid ); ?>

				<!-- Message -->
				<label for="cw_message-<?php echo $cw_uniqid; ?>"><?php echo wp_kses_post( $contact_form_message_label ); ?></label>
				<textarea name="message" id="cw_message-<?php echo $cw_uniqid; ?>" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>"></textarea>
				<br/>
				<?php do_action( 'contact_form-after_message', $cw_uniqid ); ?>

				<?php
				if ( $contact_form_captcha && ! empty( $contact_form_public_key ) && ! empty( $contact_form_private_key ) && ! defined( 'CW_RECAPTCHA_DONE' ) ) {
					if ( ! function_exists( '_recaptcha_qsencode' ) ) {
						require_once( $plugin_dir . 'scripts/recaptchalib.php' );
					}
					//Use old recaptcha
					define( 'CW_RECAPTCHA_DONE', true );
					if ( $contact_form_recaptcha_version == 'old' ) { ?>
						<!--Old Recaptcha-->
						<script type="text/javascript">
							var RecaptchaOptions = {
								theme: 'custom',
								lang: 'en',
								custom_theme_widget: 'cw-recaptcha_widget'
							};
						</script>
						<div id="cw-recaptcha_widget" style="display: none;">
							<div id="recaptcha_image"></div>
							<div id="cw_refresh">
								<a href="javascript:Recaptcha.reload()"><span><?php echo wp_kses_post( $contact_form_refresh_link ); ?></span></a>
							</div>
							<br/>

							<!--Incorrect message-->
							<div class="recaptcha_only_if_incorrect_sol" style="color: red;"><?php _e( 'Incorrect please try again', 'contact_widget' ); ?></div>

							<!-- Captcha Input field-->
							<label><?php echo $contact_form_response_field; ?></label>
							<br/>
							<input id="recaptcha_response_field" name="recaptcha_response_field" type="text">
							<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=<?php echo $publickey; ?>&lang=en"></script>
						</div>
						<br/><?php
					} else { ?>
						<br/>
						<div class="g-recaptcha" id="wpmu_grecaptcha" data-sitekey="<?php echo $contact_form_public_key; ?>" data-type="<?php echo $contact_form_recaptcha_type; ?>" data-theme="<?php echo $contact_form_recaptcha_theme; ?>"></div>
						<script src="https://www.google.com/recaptcha/api.js"></script>
						<br/><?php
					} ?>
				<?php } ?>
				<input class="button" type="button" name="submit" value="<?php echo wp_kses_post( $contact_form_submit_label ); ?>">
			</form>
		</div>
		<?php
		echo wp_kses_post( $contact_form_text_after );
		echo $args['after_widget'];
	}

	private function _get_data( $data = array() ) {
		return wp_parse_args(
			$data,
			array(
				'contact_form_widget_title'          => __( 'Contact Form', 'contact_widget' ),
				'contact_form_subject_label'         => __( 'Subject', 'contact_widget' ),
				'contact_form_from_label'            => __( 'From', 'contact_widget' ),
				'contact_form_message_label'         => __( 'Message', 'contact_widget' ),
				'contact_form_submit_label'          => __( 'Send Message', 'contact_widget' ),
				'contact_form_admin_email'           => get_option( 'admin_email' ),
				'contact_form_success_message'       => __( 'Mail sent!', 'contact_widget' ),
				'contact_form_generic_from'          => false,
				'contact_form_generic_from_user'     => 'noreply',
				'contact_form_generic_from_reply_to' => '',
				'contact_form_generic_from_body'     => '',
				'contact_form_text_after'            => '',
				'contact_form_custom_css'            => '',
				'contact_form_captcha'               => 'on',
				'contact_form_compact'               => '',
				'contact_form_refresh_message'       => __( 'Click to refresh', 'contact_widget' ),
				'contact_form_refresh_link'          => __( 'Reload Captcha', 'contact_widget' ),
				'contact_form_response_field'        => __( 'Type the characters you see in image above', 'contact_widget' ),
				'contact_form_public_key'            => '',
				'contact_form_private_key'           => '',
				'contact_form_recaptcha_version'     => 'old',
				'contact_form_recaptcha_theme'       => 'light',
				'contact_form_recaptcha_type'        => 'image',
			)
		);
	}

} // End class

//Register WPMU Dev Notification
if ( is_admin() && file_exists( WC_PLUGIN_BASE_DIR . '/dash-notice/wpmudev-dash-notification.php' ) ) {
	// Dashboard notification
	global $wpmudev_notices;
	if ( ! is_array( $wpmudev_notices ) ) {
		$wpmudev_notices = array();
	}
	$wpmudev_notices[] = array(
		'id'   => 151,
		'name' => 'Contact Form'
	);
	require_once WC_PLUGIN_BASE_DIR . '/dash-notice/wpmudev-dash-notification.php';
}
