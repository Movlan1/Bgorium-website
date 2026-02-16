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
 File: lostpassword.php
-----------------------------------------------------
 Use: Forgotten password recovery
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $is_loged_in ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$year = date('Y', time());

$skin_login = $skin_not_logged_header = <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
	<meta charset="utf-8">
	<title>DataLife Engine - {$lang['skin_title']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="robots" content="noindex, nofollow">
	{css_files}
    {js_files}
</head>
<body class="no-theme">
<script>
<!--
var dle_act_lang   = [];
var cal_language   = '{$lang['language_code']}';
var filedefaulttext= '';
var filebtntext    = '';
//-->
</script>

<div class="container">
  <div class="col-md-4 col-md-offset-4">
    <div class="page-container">
<!--MAIN area-->


	<div class="panel panel-default" style="margin-top: 100px;">

      <div class="panel-heading">
        {$lang['skin_title']} DataLife Engine
      </div>
	  
      <div class="panel-body">
		{text}
      </div>
	  <div class="text-right panel-body">
			<a href="?mod=main" class="text-right">{$lang['lost_pass_3']}</a>
	   </div>
    </div>
	<div class="text-muted text-size-small text-center">DataLife Engine&reg;  Copyright 2004-{$year}<br>&copy; <a href="https://dle-news.ru/" target="_blank">SoftNews Media Group</a> All rights reserved.</div>

	 <!--MAIN area-->
  </div>
</div>
</div>

</body>
</html>
HTML;

$skin_footer = "";

$skin_login = str_replace( "{js_files}", build_js($js_array), $skin_login );
$skin_login = str_replace( "{css_files}", build_css($css_array), $skin_login );

if( isset($_GET['douser']) AND isset($_GET['lostid']) AND intval( $_GET['douser'] ) AND $_GET['lostid'] ) {
	
	$douser = intval( $_GET['douser'] );
	$lostid = $_GET['lostid'];
	
	$row = $db->super_query( "SELECT lostid FROM " . USERPREFIX . "_lostdb WHERE lostname='{$douser}'" );
	
	if( isset($row['lostid']) AND $row['lostid'] AND $lostid AND $row['lostid'] == $lostid ) {

		$row = $db->super_query( "SELECT email, name FROM " . USERPREFIX . "_users WHERE user_id='{$douser}' LIMIT 0,1" );
			
		$username = $row['name'];
		$lostmail = $row['email'];
		
		if (isset($_GET['action'] ) AND $_GET['action'] == "ip") {

			$db->query( "UPDATE " . USERPREFIX . "_users SET allowed_ip = '' WHERE user_id='{$douser}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='{$douser}'" );

			$lang['lost_pass_12'] = str_replace("{username}", $username, $lang['lost_pass_12']);

			$skin_login = str_replace ("{text}", $lang['lost_pass_12'], $skin_login);
			echo $skin_login;
			die();


		} else {

			$salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789".sha1(random_bytes(32)));

			$new_pass = "";

			for($i = 0; $i < 11; $i ++) {
				$new_pass .= $salt[random_int(0, 72)];
			}
			
			$new_pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
			
			if( !$new_pass_hash ) {
				die("PHP extension Crypt must be loaded for password_hash to function");
			}
		
			$db->query( "UPDATE " . USERPREFIX . "_users SET password='" . $db->safesql($new_pass_hash) . "', allowed_ip = '' WHERE user_id='{$douser}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$douser'" );

			$mail = new dle_mail( $config );

			if ($config['auth_metod']) $username = $lostmail;

			$message = $lang['lost_pass_13']."\n\n{$lang['lost_pass_14']} {$username}\n{$lang['lost_pass_15']} {$new_pass}\n\n{$lang['lost_pass_16']}\n\n{$lang['lost_pass_19']} ".$config['http_home_url'];
			$mail->send( $lostmail, $lang['lost_pass_11'], $message );
			
			$skin_login = str_replace ("{text}", $lang['lost_pass_20']." <b>{$lostmail}</b>. ".$lang['lost_pass_16'], $skin_login);
			echo $skin_login;
			die();
			
		}	

	} else {

		if( $row['lostid'] ) {
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='{$douser}'" );	
		}
		
		$skin_login = str_replace ("{text}", $lang['lost_pass_18'], $skin_login);
		echo $skin_login;
		die();
			

	}
	

} elseif( isset( $_POST['submit_lost'] ) ) {

	if ($config['allow_recaptcha']) {

		if ( isset($_POST['g-recaptcha-response']) AND $_POST['g-recaptcha-response'] ) {
			
			$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);
		
			$resp = $reCaptcha->verifyResponse(get_ip(), $_POST['g-recaptcha-response'] );
			
		    if ($resp != null && $resp->success) {

				$_POST['sec_code'] = 1;
				$_SESSION['sec_code_session'] = 1;

		    } else $_SESSION['sec_code_session'] = false;
	
		} else $_SESSION['sec_code_session'] = false;

	}
	
	$_POST['lostname'] = isset($_POST['lostname']) ? $_POST['lostname'] : '';

	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\/|\\\|\&\~\*\{\+]/", $_POST['lostname'] ) OR !trim($_POST['lostname'])) {
		
		$skin_login = str_replace ("{text}", $lang['lost_pass_4'], $skin_login);
		echo $skin_login;
		die();
	
	} elseif( $_POST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
		
		$skin_login = str_replace ("{text}", $lang['lost_pass_5'], $skin_login);
		echo $skin_login;
		die();
	
	} else {
		
		$_SESSION['sec_code_session'] = false;
		$lostname = $db->safesql( $_POST['lostname'] );
		
		if( @count(explode("@", $lostname)) == 2 ) $search = "email = '" . $lostname . "'";
		else $search = "name = '" . $lostname . "'";
		
		$row = $db->super_query( "SELECT email, password, name, user_id, user_group FROM " . USERPREFIX . "_users WHERE {$search}" );
		
		if( isset($row['user_id']) AND $row['user_id'] AND $user_group[$row['user_group']]['allow_admin'] ) {
			
			$lostmail = $row['email'];
			$userid = $row['user_id'];
			$lostname = $row['name'];
			$lostpass = $row['password'];
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email where name='lost_mail' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html'] );
			
			$row['template'] = stripslashes( $row['template'] );
			
			$lostid = sha1( md5( $lostname . $lostmail . $lostpass ) . microtime() . random_bytes(32) );

			if ( strlen($lostid) != 40 ) die ("US Secure Hash Algorithm 1 (SHA1) disabled by Hosting");
			
			if (strpos($config['http_home_url'], "//") === 0) $slink = "https:".$config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
			else $slink = $config['http_home_url'];
					
			$lostlink = $slink . $config['admin_path']."?mod=lostpassword&action=password&douser=" . $userid . "&lostid=" . $lostid;
			$iplink = $slink . $config['admin_path']."?mod=lostpassword&action=ip&douser=" . $userid . "&lostid=" . $lostid;

			if( $row['use_html'] ) {
				$link = "{$lang['lost_pass_8']}<br><a href=\"{$lostlink}\" target=\"_blank\">{$lostlink}</a><br><br>{$lang['lost_pass_9']}<br><a href=\"{$iplink}\" target=\"_blank\">{$iplink}</a>";
			} else {
				$link = $lang['lost_pass_8']."\n".$lostlink."\n\n".$lang['lost_pass_9']."\n".$iplink;
			}
			
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$userid'" );
			
			$db->query( "INSERT INTO " . USERPREFIX . "_lostdb (lostname, lostid) values ('$userid', '$lostid')" );
			
			$row['template'] = str_replace( "{%username%}", $lostname, $row['template'] );
			$row['template'] = str_replace( "{%lostlink%}", $link, $row['template'] );
			$row['template'] = str_replace( "{%losturl%}", $lostlink, $row['template'] );
			$row['template'] = str_replace( "{%ipurl%}", $iplink, $row['template'] );
			$row['template'] = str_replace( "{%ip%}", get_ip(), $row['template'] );
			
			$mail->send( $lostmail, $lang['lost_pass_11'], $row['template'] );
			
			if( $mail->send_error ) $skin_login = str_replace ("{text}", $mail->smtp_msg, $skin_login);
			else $skin_login = str_replace ("{text}", $lang['lost_pass_10'], $skin_login);

			echo $skin_login;
			die();
		
		} elseif( !isset($row['user_id']) ) {

			$skin_login = str_replace ("{text}", $lang['lost_pass_6'], $skin_login);
			echo $skin_login;
			die();

		} else {

			$skin_login = str_replace ("{text}", $lang['lost_pass_7'], $skin_login);
			echo $skin_login;
			die();

		}
	}

} else {

	$text = "";

    $text .= "<div class=\"form-group has-feedback has-feedback-left\">
            <input type=\"text\" dir=\"auto\" name=\"lostname\" class=\"form-control\" placeholder=\"{$lang['lost_pass_1']}\" required>
			<div class=\"form-control-feedback\">
				<i class=\"fa fa-user text-muted\"></i>
			</div>
          </div>";	

	if ( $config['allow_recaptcha'] ) {
		
		$captcha_name = "g-recaptcha";
		$captcha_url = "https://www.google.com/recaptcha/api.js?hl={$lang['language_code']}";
		
		if( $config['allow_recaptcha'] == 3) {
			
			$captcha_name = "h-captcha";
			$captcha_url = "https://js.hcaptcha.com/1/api.js?hl={$lang['language_code']}";
		
		}

		if ($config['allow_recaptcha'] == 4) {

			$captcha_name = "cf-turnstile";
			$captcha_url = "https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha";
		}

		if( $config['allow_recaptcha'] == 2) {
			
			$text .= "<script src=\"https://www.google.com/recaptcha/api.js?render={$config['recaptcha_public_key']}\" async defer></script>";
			
		} else {
			$text .= "<div class=\"form-group\"><div class=\"{$captcha_name}\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\" data-language=\"{$lang['language_code']}\"></div><script src=\"{$captcha_url}\" async defer></script></div>";
		}
		
	} else {

		$text .= "<div class=\"form-group\"><a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" style=\"width: 130px;height: 46px;\" /></span></a>&nbsp;<input placeholder=\"{$lang['repead_code']}\" type=\"text\" dir=\"auto\" name=\"sec_code\" id=\"sec_code\" class=\"classic\" style=\"height: 46px;vertical-align: middle;\" required></div>";

	}

	$text .= "<div class=\"form-group\">
			<button type=\"submit\" class=\"btn btn-primary btn-raised btn-block\">{$lang['lost_pass_2']} <i class=\"fa fa-sign-in\"></i></button>
          </div>";
	
	$text = "<form  method=\"post\" name=\"dle-lostpassword\" id=\"dle-lostpassword\" action=\"?mod=lostpassword\">\n" . $text;


$text .= <<<HTML
	<script>
		$(function(){
			$('#dle-lostpassword').submit(function(event) {
			
				var dle_captcha_type  = '{$config['allow_recaptcha']}';
				
				if(dle_captcha_type == 2 ) {

					event.preventDefault();
					
					grecaptcha.execute('{$config['recaptcha_public_key']}', {action: 'lostpassword'}).then(function(token) {
						$('#dle-lostpassword').append('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
						$('#dle-lostpassword').off('submit');
						HTMLFormElement.prototype.submit.call(document.getElementById('dle-lostpassword'));
					});
			
					return false;
				}
				
				return true;
				
			});
		});
    </script>
HTML;
	
	$text .= "<input name=\"submit_lost\" type=\"hidden\" id=\"submit_lost\" value=\"submit_lost\"></form>";
	
	$skin_login = str_replace ("{text}", $text, $skin_login);
	
	echo $skin_login;
	
}
?>