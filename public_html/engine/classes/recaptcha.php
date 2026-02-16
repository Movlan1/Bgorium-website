<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2025 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: recaptcha.php
-----------------------------------------------------
 Use: Captcha Services
=====================================================
*/

class ReCaptchaResponse
{
    public $success;
    public $errorCodes;
}

class ReCaptcha
{
    private $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
    private $_secret;
	private $_score = '0.5';
	private $_hcaptha = false;
    private $_turnstile = false;
	

    function __construct($secret)
    {   global $config;
	
        $this->_secret=$secret;
		
		if( $config['allow_recaptcha'] == 3 ) {
			$this->_siteVerifyUrl = "https://hcaptcha.com/siteverify?";
			$this->_hcaptha = true;
		}

        if ($config['allow_recaptcha'] == 4) {
            $this->_siteVerifyUrl = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
            $this->_turnstile = true;
        }

		if( isset($config['recaptcha_score']) AND floatval($config['recaptcha_score']) > 0 AND floatval($config['recaptcha_score']) < 1) {
			$this->_score = floatval($config['recaptcha_score']);
		} else $this->_score = floatval($this->_score);

    }

    private function _encodeQS($data)
    {
        $req = "";
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }
        // Cut the last '&'
        $req=substr($req, 0, strlen($req)-1);
        return $req;
    }

    private function _submitHTTPGet($path, $data)
    {
        $req = $this->_encodeQS($data);
        $response = file_get_contents($path . $req);
        return $response;
    }

    function _submitHttpPost($url, $data)
    {

        if (function_exists('curl_init')) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

        } else {

            $options = array(
                'http' => array(
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
        }

        return $response;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remoteIp   IP address of end user.
     * @param string $response   response string from recaptcha verification.
     *
     * @return ReCaptchaResponse
     */
    public function verifyResponse($remoteIp, $response)
    {
        // Discard empty solution submissions
        if ($response == null || strlen($response) == 0) {
            $recaptchaResponse = new ReCaptchaResponse();
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = 'missing-input';
            return $recaptchaResponse;
        }

		if ( $this->_turnstile ) {

            $getResponse = $this->_submitHttpPost(
                $this->_siteVerifyUrl,
                array(
                    'secret' => $this->_secret,
                    'remoteip' => $remoteIp,
                    'response' => $response
                )
            );

        } else {

            $getResponse = $this->_submitHttpGet(
                $this->_siteVerifyUrl,
                array(
                    'secret' => $this->_secret,
                    'remoteip' => $remoteIp,
                    'response' => $response
                )
            );

        }
		
        $answers = json_decode($getResponse, true);

        $recaptchaResponse = new ReCaptchaResponse();
        if (trim($answers['success']) == true) {
			if( isset($answers['score']) AND $answers['score'] ) {
				if( $this->_hcaptha ) {
					if (floatval($answers['score']) <= $this->_score) {
						$recaptchaResponse->success = true;
					} else $recaptchaResponse->success = false;
				} else {
					if (floatval($answers['score']) >= $this->_score) {
						$recaptchaResponse->success = true;
					} else $recaptchaResponse->success = false;
				}
				
			} else  $recaptchaResponse->success = true;
        } else {
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = $answers['error-codes'];
        }
        return $recaptchaResponse;
    }
}
