<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin#list
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * The reCAPTCHA server URL's
 */
define( "RECAPTCHA_API_SERVER", "http://www.google.com/recaptcha/api" );
define( "RECAPTCHA_API_SECURE_SERVER", "https://www.google.com/recaptcha/api" );
define( "RECAPTCHA_VERIFY_SERVER", "www.google.com" );

/**
 * A ReCaptchaResponse is returned from checkAnswer().
 */
class ReCaptchaResponse {
	public $success;
	public $errorCodes;
}

class ReCaptcha {
	private static $_signupUrl = "https://www.google.com/recaptcha/admin";
	private static $_siteVerifyUrl =
		"https://www.google.com/recaptcha/api/siteverify?";
	private $_secret;
	private static $_version = "php_1.0";

	/**
	 * Constructor.
	 *
	 * @param string $secret shared secret between site and ReCAPTCHA server.
	 */
	function ReCaptcha( $secret ) {
		if ( $secret == null || $secret == "" ) {
			die( "To use reCAPTCHA you must get an API key from <a href='"
			     . self::$_signupUrl . "'>" . self::$_signupUrl . "</a>" );
		}
		$this->_secret = $secret;
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param array $data array of string elements to be encoded.
	 *
	 * @return string - encoded request.
	 */
	private function _encodeQS( $data ) {
		$req = "";
		foreach ( $data as $key => $value ) {
			$req .= $key . '=' . urlencode( stripslashes( $value ) ) . '&';
		}

		// Cut the last '&'
		$req = substr( $req, 0, strlen( $req ) - 1 );

		return $req;
	}

	/**
	 * Submits an HTTP GET to a reCAPTCHA server.
	 *
	 * @param string $path url path to recaptcha server.
	 * @param array $data array of parameters to be sent.
	 *
	 * @return array response
	 */
	private function _submitHTTPGet( $path, $data ) {
		$req      = $this->_encodeQS( $data );
		$response = file_get_contents( $path . $req );

		return $response;
	}

	/**
	 * Calls the reCAPTCHA siteverify API to verify whether the user passes
	 * CAPTCHA test.
	 *
	 * @param string $remoteIp IP address of end user.
	 * @param string $response response string from recaptcha verification.
	 *
	 * @return ReCaptchaResponse
	 */
	public function verifyResponse( $remoteIp, $response ) {
		// Discard empty solution submissions
		if ( $response == null || strlen( $response ) == 0 ) {
			$recaptchaResponse             = new ReCaptchaResponse();
			$recaptchaResponse->success    = false;
			$recaptchaResponse->errorCodes = 'missing-input';

			return $recaptchaResponse;
		}

		$getResponse       = $this->_submitHttpGet(
			self::$_siteVerifyUrl,
			array(
				'secret'   => $this->_secret,
				'remoteip' => $remoteIp,
				'v'        => self::$_version,
				'response' => $response
			)
		);
		$answers           = json_decode( $getResponse, true );
		$recaptchaResponse = new ReCaptchaResponse();

		if ( trim( $answers ['success'] ) == true ) {
			$recaptchaResponse->success = true;
		} else {
			$recaptchaResponse->success    = false;
			$recaptchaResponse->errorCodes = !empty( $answers ['error-codes'] ) ? $answers ['error-codes'] : '';
		}

		echo "<pre>";
		print_r( $recaptchaResponse );
		echo "</pre>";
		return $recaptchaResponse;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 *
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int port
	 *
	 * @return array response
	 */
	function _http_post( $host, $path, $data, $port = 80 ) {

		$req = $this->_encodeQS( $data );

		$http_request = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen( $req ) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		if ( false == ( $fs = @fsockopen( $host, $port, $errno, $errstr, 10 ) ) ) {
			die ( 'Could not open socket' );
		}

		fwrite( $fs, $http_request );

		while ( ! feof( $fs ) ) {
			$response .= fgets( $fs, 1160 );
		} // One TCP-IP packet
		fclose( $fs );
		$response = explode( "\r\n\r\n", $response, 2 );

		return $response;
	}

	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 *
	 * @param string $privkey
	 * @param string $remoteip
	 * @param string $challenge
	 * @param string $response
	 * @param array $extra_params an array of extra variables to post to the server
	 *
	 * @return ReCaptchaResponse
	 */
	function check_answer( $privkey, $remoteip, $challenge, $response, $extra_params = array() ) {
		if ( $privkey == null || $privkey == '' ) {
			die ( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>" );
		}

		if ( $remoteip == null || $remoteip == '' ) {
			die ( "For security reasons, you must pass the remote ip to reCAPTCHA" );
		}


		//discard spam submissions
		if ( $challenge == null || strlen( $challenge ) == 0 || $response == null || strlen( $response ) == 0 ) {
			$recaptcha_response           = new ReCaptchaResponse();
			$recaptcha_response->is_valid = false;
			$recaptcha_response->error    = 'incorrect-captcha-sol';

			return $recaptcha_response;
		}

		$response = $this->_http_post( RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
			array(
				'privatekey' => $privkey,
				'remoteip'   => $remoteip,
				'challenge'  => $challenge,
				'response'   => $response
			) + $extra_params
		);

		$answers            = explode( "\n", $response [1] );
		$recaptcha_response = new ReCaptchaResponse();

		if ( trim( $answers [0] ) == 'true' ) {
			$recaptcha_response->is_valid = true;
		} else {
			$recaptcha_response->is_valid = false;
			$recaptcha_response->error    = $answers [1];
		}

		return $recaptcha_response;

	}

	/**
	 * gets a URL where the user can sign up for reCAPTCHA. If your application
	 * has a configuration page where you enter a key, you should provide a link
	 * using this function.
	 *
	 * @param string $domain The domain where the page is hosted
	 * @param string $appname The name of your application
	 */
	function _get_signup_url( $domain = null, $appname = null ) {
		return "https://www.google.com/recaptcha/admin/create?" . $this->_encodeQS( array(
			'domains' => $domain,
			'app'     => $appname
		) );
	}

	function _aes_pad( $val ) {
		$block_size = 16;
		$numpad     = $block_size - ( strlen( $val ) % $block_size );

		return str_pad( $val, strlen( $val ) + $numpad, chr( $numpad ) );
	}

	/* Mailhide related code */

	function _aes_encrypt( $val, $ky ) {
		if ( ! function_exists( "mcrypt_encrypt" ) ) {
			die ( "To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed." );
		}
		$mode = MCRYPT_MODE_CBC;
		$enc  = MCRYPT_RIJNDAEL_128;
		$val  = $this->_aes_pad( $val );

		return mcrypt_encrypt( $enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0" );
	}


	function _mailhide_urlbase64( $x ) {
		return strtr( base64_encode( $x ), '+/', '-_' );
	}

	/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
	function _mailhide_url( $pubkey, $privkey, $email ) {
		if ( $pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null ) {
			die ( "To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
			      "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>" );
		}


		$ky        = pack( 'H*', $privkey );
		$cryptmail = $this->_aes_encrypt( $email, $ky );

		return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . $this->_mailhide_urlbase64( $cryptmail );
	}

	/**
	 * gets the parts of the email to expose to the user.
	 * eg, given johndoe@example,com return ["john", "example.com"].
	 * the email is then displayed as john...@example.com
	 */
	function _mailhide_email_parts( $email ) {
		$arr = preg_split( "/@/", $email );

		if ( strlen( $arr[0] ) <= 4 ) {
			$arr[0] = substr( $arr[0], 0, 1 );
		} else if ( strlen( $arr[0] ) <= 6 ) {
			$arr[0] = substr( $arr[0], 0, 3 );
		} else {
			$arr[0] = substr( $arr[0], 0, 4 );
		}

		return $arr;
	}

	/**
	 * Gets html to display an email address given a public an private key.
	 * to get a key, go to:
	 *
	 * http://www.google.com/recaptcha/mailhide/apikey
	 */
	function _mailhide_html( $pubkey, $privkey, $email ) {
		$emailparts = $this->_mailhide_email_parts( $email );
		$url        = $this->_mailhide_url( $pubkey, $privkey, $email );

		return htmlentities( $emailparts[0] ) . "<a href='" . htmlentities( $url ) .
		       "' onclick=\"window.open('" . htmlentities( $url ) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities( $emailparts [1] );

	}
}

?>