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
 File: twofactor.php
-----------------------------------------------------
 Use: Two-factor authentication
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

include_once ENGINE_DIR . '/classes/composer/vendor/autoload.php';

if( isset($_GET['mode']) AND $_GET['mode'] == 'createsecret') {

	if (!isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash) {
		die("error_code_1");
	}

	if( !$is_logged ) {
		die("error_code_2");
	}

	$tfa = new RobThree\Auth\TwoFactorAuth($_SERVER['HTTP_HOST']);
	$secret = $_SESSION['twofactor_secret'] = $tfa->createSecret();
	$url = $tfa->getQRCodeImageAsURL($member_id['email'], $secret);

	echo "<p>{$lang['twofactor_auth_4']}</p><p><img src=\"{$url}\" width=\"200\" height=\"200\" style=\"display: block; margin-left: auto; margin-right: auto;\"></p><p><b>{$lang['twofactor_auth_5']}</b></p><p><input type=\"text\" inputmode=\"numeric\" pattern=\"[0-9]*\" name=\"dle-promt-text\" id=\"dle-promt-text\" style=\"width:100%;\" class=\"ui-widget-content ui-corner-all classic\"></p><div id=\"twofactor_response\" style=\"color:red\"></div>";

	die();

} elseif(isset($_POST['mode']) and $_POST['mode'] == 'verifysecret') {

	if (!isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_3']}\"}";
		die();
	}

	if (!$is_logged) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_3']}\"}";
		die();
	}

	$code = (string)$_POST['pin'];

	if (!$code) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_2']}\"}";
		die();
	}

	if ( !$_SESSION['twofactor_secret'] ) {
		echo "{\"error\":true, \"errorinfo\":\"PHP Session Error\"}";
		die();
	}

	$tfa = new RobThree\Auth\TwoFactorAuth($_SERVER['HTTP_HOST']);

	if ($tfa->verifyCode($_SESSION['twofactor_secret'], $code) === true) {

		$secret = $db->safesql($_SESSION['twofactor_secret']);

		$db->query("UPDATE " . USERPREFIX . "_users SET twofactor_auth='2', twofactor_secret='{$secret}' WHERE user_id='{$member_id['user_id']}'");

		$_SESSION['twofactor_secret'] = 0;
		unset($_SESSION['twofactor_secret']);

		echo "{\"success\":true, \"message\":\"{$lang['twofactor_ok']}\"}";
		die();

	} else {

		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_6']}\"}";
		die();

	}


} else {

	if (!isset($_SESSION['twofactor_id']) or !isset($_SESSION['twofactor_auth'])) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_1']}\"}";
		die();
	}

	$_POST['pin'] = (string)$_POST['pin'];

	if (!$_POST['pin']) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_2']}\"}";
		die();
	}

	$user_id = intval($_SESSION['twofactor_id']);

	if (!$user_id or $user_id < 1) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['twofactor_err_1']}\"}";
		die();
	}

	$_IP = get_ip();
	$_TIME = time();
	$thisdate = $_TIME - 900;

	$db->query("DELETE FROM " . USERPREFIX . "_twofactor WHERE date < '$thisdate'");

	$member_id = $db->super_query("SELECT * FROM " . USERPREFIX . "_users WHERE user_id='{$user_id}'");

	if ($member_id['user_id'] AND $member_id['password'] AND $_SESSION['twofactor_auth'] AND md5($member_id['password']) == $_SESSION['twofactor_auth']) {

		$row = $db->super_query("SELECT * FROM " . USERPREFIX . "_twofactor WHERE user_id='{$user_id}'");

		if (!$row['id']) {

			$_SESSION['twofactor_id'] = 0;
			$_SESSION['twofactor_auth'] = "";

			unset($_SESSION['twofactor_id']);
			unset($_SESSION['twofactor_auth']);
			unset($_SESSION['twofactor_type']);
			echo "{\"error\":true, \"errorinfo\":\" {$lang['twofactor_err_4']}\"}";
			die();
		}

		$pass_pin_code = false;

		if( $member_id['twofactor_auth'] == 2 AND $member_id['twofactor_secret'] ) {

			$tfa = new RobThree\Auth\TwoFactorAuth($_SERVER['HTTP_HOST']);
			$pass_pin_code = $tfa->verifyCode($member_id['twofactor_secret'], $_POST['pin']);

		} elseif( $member_id['twofactor_auth'] == 1 AND $row['pin'] === $_POST['pin'] ) {

			$pass_pin_code = true;
		}

		if ( $pass_pin_code !== true ) {

			$db->query("UPDATE " . USERPREFIX . "_twofactor SET attempt=attempt+1 WHERE id='{$row['id']}'");

			if ($user_group[$member_id['user_group']]['allow_admin']) {

				$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '99', '')");
			}

			$attempt = 2 - $row['attempt'];

			if ($attempt < 1) {

				$db->query("DELETE FROM " . USERPREFIX . "_twofactor WHERE id='{$row['id']}'");

				$_SESSION['twofactor_id'] = 0;
				$_SESSION['twofactor_auth'] = "";
				unset($_SESSION['twofactor_id']);
				unset($_SESSION['twofactor_auth']);
				unset($_SESSION['twofactor_type']);
				echo "{\"success\":true}";
				die();
			}

			$lang['twofactor_err_5'] = str_replace("{attempt}", $attempt, $lang['twofactor_err_5']);
			echo "{\"error\":true, \"errorinfo\":\" {$lang['twofactor_err_5']}\"}";
			die();
		}

		session_regenerate_id();

		$db->query("DELETE FROM " . USERPREFIX . "_twofactor WHERE id='{$row['id']}'");

		if ($user_group[$member_id['user_group']]['allow_admin']) {

			$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '100', '')");
		}

		if ($_SESSION['no_save_cookie']) {

			set_cookie("dle_user_id", "", 0);
			set_cookie("dle_password", "", 0);
		} else {

			set_cookie("dle_user_id", $member_id['user_id'], 365);
			set_cookie("dle_password", md5($member_id['password']), 365);
		}

		$_SESSION['dle_user_id'] = $member_id['user_id'];
		$_SESSION['dle_password'] = md5($member_id['password']);
		$_SESSION['member_lasttime'] = $member_id['lastdate'];

		$_SESSION['twofactor_id'] = 0;
		$_SESSION['no_save_cookie'] = 0;
		$_SESSION['twofactor_auth'] = "";
		
		unset($_SESSION['twofactor_id']);
		unset($_SESSION['twofactor_auth']);
		unset($_SESSION['twofactor_type']);
		unset($_SESSION['no_save_cookie']);
		echo "{\"success\":true}";
		die();

	} else {

		$_SESSION['twofactor_id'] = 0;
		$_SESSION['twofactor_auth'] = "";

		unset($_SESSION['twofactor_id']);
		unset($_SESSION['twofactor_auth']);
		unset($_SESSION['twofactor_type']);
		echo "{\"error\":true, \"errorinfo\":\" {$lang['twofactor_err_3']}\"}";
		die();
	}

}
