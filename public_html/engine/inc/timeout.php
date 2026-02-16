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
 File: timeout.php
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$is_loged_in ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$result = "";

if( isset($_POST['password']) AND $_POST['password'] ) {
	
		
	$name = $member_id['name'];
	$foto = $member_id['foto'];
	
	if ( !$config['auth_metod']) $check_user = $member_id['name']; else $check_user = $member_id['email'];
	
	if( check_login( $check_user, $_POST['password'], true, true ) ) {

		set_cookie( "timeout_session", 0, 0 );
		unset($_SESSION['timeout_session']);

		header( "Location: {$_SERVER['REQUEST_URI']}" );
		
		die();
		
	} else {
		
		$member_id['name'] = $name;
		$member_id['foto'] = $foto;
		$result = $lang['password_incorect'];
		
	}
	
}

if( !isset($_SESSION['timeout_session']) ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$year = date('Y', time());
$avatar = "";

if ( count(explode("@", $member_id['foto'])) == 2 ) {
	
	$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($member_id['foto'])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']);
	
} else {
	
	if( $member_id['foto'] ) {
		
		if (strpos($member_id['foto'], "//") === 0) $avatar = "http:".$member_id['foto']; else $avatar = $member_id['foto'];

		$avatar = @parse_url ( $avatar );

		if( $avatar['host'] ) {
			
			$avatar = $member_id['foto'];
			
		} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $member_id['foto'];

	}
}

if ($avatar) {
	$avatar = "<img src=\"{$avatar}\">";
} else {
	$avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-responsive hidden-xs" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
}

$skin_login = <<<HTML
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
	{css_files}
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
	  <form method="post">
		<div class="thumb thumb-rounded thumb-timeout">
			{$avatar}
		</div>
		
		<h6 class="content-group text-center text-semibold no-margin-top">{$member_id['name']}<small class="display-block">{$lang['unlock']}</small></h6>
		<div class="form-group has-feedback has-feedback-right">
			<input class="form-control" type="password" dir="auto" name="password" placeholder="{$lang['login_box_3']}" autofocus>
			<div class="form-control-feedback">
				<i class="fa fa-lock text-muted"></i>
			</div>
		</div>
		<div class="form-group" style="color:red">{$result}</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-raised btn-block">{$lang['login_button']} <i class="fa fa-sign-in"></i></button>
          </div>
		</form>
		<div class="text-right">
			<a href="?action=logout" class="status-info text-right">{$lang['skin_logout']}</a>
		</div>
      </div>

    </div>
	
	<div class="text-muted text-size-small text-center">DataLife Engine&reg;  Copyright 2004-{$year}<br>&copy; <a href="https://dle-news.ru/" target="_blank">SoftNews Media Group</a> All rights reserved.</div>
  </div>
</div>
</div>

</body>
</html>
HTML;

	$skin_login = str_replace( "{css_files}", build_css($css_array), $skin_login );
	
	echo $skin_login;
	die();
?>