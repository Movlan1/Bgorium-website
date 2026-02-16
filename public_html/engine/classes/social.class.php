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
 File: social.class.php
-----------------------------------------------------
 Use: Authorization through social networks
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class AuthViaVK {

    function get_user( $social_config ) {
		global $config, $lang;

		if ( !isset($_SESSION['vk_access_token']) ) {
			$params = array(
				'client_id'     => $social_config['vkid'],
				'grant_type' => 'authorization_code',
				'code_verifier' => $_SESSION['vkcode'],
				'device_id' => $_GET['device_id'],
				'code' => $_GET['code'],
				'state' => $_SESSION['state'],
				'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=vk"
			);

			$token = @json_decode(http_get_contents('https://id.vk.com/oauth2/auth', $params), true);

		} else $token=array('access_token' => $_SESSION['vk_access_token'] );

		if (isset($token['access_token'])) {

			$params = array(
				'client_id'     => $social_config['vkid'],
				'access_token' => $token['access_token']
			);

			$user = @json_decode(http_get_contents('https://id.vk.com/oauth2/user_info', $params), true);

			if (isset($user['user']['user_id'])) {

	            $user = $user['user'];

				if (!isset($user['email']) AND !isset($_GET['email']) ) { $_SESSION['vk_access_token'] = $token['access_token']; $_SESSION['vk_access_code'] = $_GET['code']; }

				if( !isset($user['email']) AND isset($_GET['email']) ) $user['email'] = $_GET['email'];

				if (!isset($user['email'])) $user['email'] = '';
				if (!isset($user['nickname'])) $user['nickname'] = '';

				return array ('sid' => sha1 ('vkontakte'.$user['user_id']), 'nickname' => $user['nickname'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => $user['avatar'], 'provider' => 'vkontakte' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaGoogle {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['googleid'],
			'client_secret' => $social_config['googlesecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=google",

		);

		$token = @json_decode(http_get_contents('https://accounts.google.com/o/oauth2/token', $params), true);

		if (isset($token['access_token'])) {

			$params['access_token'] = $token['access_token'];

			$user = @json_decode(http_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {

				return array ('sid' => sha1 ('google'.$user['id']), 'nickname' => $user['name'], 'name' => $user['given_name'].' '.$user['family_name'], 'email' => $user['email'], 'avatar' => $user['picture'], 'provider' => 'Google' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaMailru {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['mailruid'],
			'client_secret' => $social_config['mailrusecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=mailru",

		);

		$token = @json_decode(http_get_contents('https://oauth.mail.ru/token', $params), true);

		if (isset($token['access_token'])) {

			$params = array(
				'access_token'  => $token['access_token']
			);

			$user = @json_decode(http_get_contents('https://oauth.mail.ru/userinfo' . '?' . http_build_query($params)), true);

			if (isset($user['nickname']) AND $user['nickname'] AND isset($user['email']) AND $user['email']) {
				
				$uid = $user['nickname'].$user['email'];

				return array ('sid' => sha1 ('mailru'.$uid), 'nickname' => $user['nickname'], 'name' => $user['name'], 'email' => $user['email'], 'avatar' => $user['image'], 'provider' => 'Mail.Ru' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaYandex {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['yandexid'],
			'client_secret' => $social_config['yandexsecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code']

		);

		$token = @json_decode(http_get_contents('https://oauth.yandex.ru/token', $params), true);

		if (isset($token['access_token'])) {

			$params = array(
				'format'       => 'json',
				'oauth_token'  => $token['access_token']
			);

			$user = @json_decode(http_get_contents('https://login.yandex.ru/info' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {
				
				if( $user['default_avatar_id'] ) {
					$user['avatar'] = "https://avatars.yandex.net/get-yapic/{$user['default_avatar_id']}/islands-200";
				} else $user['avatar'] = "";

				return array ('sid' => sha1 ('yandex'.$user['id']), 'nickname' => $user['display_name'], 'name' => $user['real_name'], 'email' => $user['default_email'], 'avatar' => $user['avatar'], 'provider' => 'Yandex' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaFacebook {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['fcid'],
			'client_secret' => $social_config['fcsecret'],
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=fc"
		);

		$token = @json_decode(http_get_contents('https://graph.facebook.com/oauth/access_token' . '?' . http_build_query($params)), true);

		if (isset($token['access_token'])) {

			$params = array('access_token' => $token['access_token'], 'fields' => "id,name,email,first_name,last_name,picture");

			$user = @json_decode(http_get_contents('https://graph.facebook.com/me' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {

				return array ('sid' => sha1 ('facebook'.$user['id']), 'nickname' => $user['name'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => "https://graph.facebook.com/".$user['id']."/picture?type=large", 'provider' => 'Facebook' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaOdnoklassniki {

    function get_user( $social_config ) {
		global $config, $lang;

		if ( !isset($_SESSION['od_access_token']) ) {

			$params = array(
				'client_id'     => $social_config['odid'],
				'client_secret' => $social_config['odsecret'],
				'grant_type' => 'authorization_code',
				'code' => $_GET['code'],
				'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=od"
			);

			$token = @json_decode(http_get_contents('https://api.ok.ru/oauth/token.do', $params), true);

		} else $token=array('access_token' => $_SESSION['od_access_token'] );

		if (isset($token['access_token'])) {

			$sign = md5("application_key={$social_config['odpublic']}fields=name,first_name,last_name,email,pic_2format=jsonmethod=users.getCurrentUser" . md5("{$token['access_token']}{$social_config['odsecret']}"));

			$params = array(
				'method'          => 'users.getCurrentUser',
				'access_token'    => $token['access_token'],
				'application_key' => $social_config['odpublic'],
				'fields'       	  => 'name,first_name,last_name,email,pic_2',
				'format'          => 'json',
				'sig'             => $sign
			);

			$user = @json_decode(http_get_contents('https://api.ok.ru/fb.do' . '?' . http_build_query($params)), true);

			if (isset($user['uid'])) {

				if ( !isset($_SESSION['od_access_token']) ) { $_SESSION['od_access_token'] = $token['access_token']; $_SESSION['od_access_code'] = $_GET['code']; }

				if(!$user['email'] AND isset($_GET['email']) ) $user['email'] = $_GET['email'];

				return array ('sid' => sha1 ('odnoklassniki'.$user['uid']), 'nickname' => $user['name'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => $user['pic_2'], 'provider' => 'Odnoklassniki' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class SocialAuth {

	private $auth = null;
	private $social_config = array();

    function __construct( $social_config ){
		
		if( !isset($_GET['provider']) ) {
			 return;
		}
	
        if ($_GET['provider'] == "vk" AND $social_config['vk']) {

            $this->auth = new AuthViaVK();

        } elseif ($_GET['provider'] == "google" AND $social_config['google']) {

            $this->auth = new AuthViaGoogle();

        } elseif ( $_GET['provider'] == "mailru" AND $social_config['mailru']) {

            $this->auth = new AuthViaMailru();

        } elseif ($_GET['provider'] == "yandex" AND $social_config['yandex']) {

            $this->auth = new AuthViaYandex();

        } elseif ($_GET['provider'] == "fc" AND $social_config['fc']) {

            $this->auth = new AuthViaFacebook();

        } elseif ($_GET['provider'] == "od" AND $social_config['od']) {

            $this->auth = new AuthViaOdnoklassniki();

        }

		$this->social_config = $social_config;

    }

    function getuser(){
		global $lang;

		if( $this->auth !== null ) {

			$user = $this->auth->get_user( $this->social_config );

			if( is_array($user) ) {

				if( !$user['nickname'] ) {

					$user['nickname'] = $user['name'];

				}

				$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
				$user['email'] = str_replace( $not_allow_symbol, '',  $user['email']);

				$user['nickname'] = preg_replace("/[\||\'|\<|\>|\[|\]|\%|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\}\+]/", '', $user['nickname'] );
				$user['nickname'] = str_ireplace( ".php", "_disabled", $user['nickname'] );
				$user['nickname'] = trim( htmlspecialchars( $user['nickname'], ENT_QUOTES, 'UTF-8' ) );
				$user['nickname'] = str_replace('&', '', $user['nickname']);
				$user['name'] = trim( htmlspecialchars( $user['name'], ENT_QUOTES, 'UTF-8' ) );
				if (dle_strlen($user['nickname']) > 40) $user['nickname'] = dle_substr($user['nickname'], 0, 40);

			}

			return $user;

		} else return $lang['social_err_2'];

	}

}
