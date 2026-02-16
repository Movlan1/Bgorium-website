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
 File: keywords.php
-----------------------------------------------------
 Use: Generation of keywords
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$is_logged OR !$user_group[$member_id['user_group']]['allow_admin'] ) { die ("error"); }

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
	die ("error");
	
}

$parse = new ParseFilter();
$short_story = isset($_REQUEST['short_txt']) ? $parse->BB_Parse($parse->process($_REQUEST['short_txt']), false) : '';
$full_story = isset($_REQUEST['full_txt']) ? $parse->BB_Parse($parse->process($_REQUEST['full_txt']), false) : '';

if( dle_strlen($full_story) > 12 ) $story = $full_story; else $story = $short_story;

$metatags = create_metatags ($story, true);

$metatags['description'] = str_replace("&amp;","&", $metatags['description'] );
$metatags['description'] = str_replace("&quot;", '"', $metatags['description'] );
$metatags['description'] = str_replace("&#039;", "'", $metatags['description']);
$metatags['keywords'] = str_replace("&quot;", '"', $metatags['keywords']);
$metatags['keywords'] = str_replace("&#039;", "'", $metatags['keywords']);

if ($_REQUEST['key'] == 1) echo stripslashes($metatags['description']);
else echo stripslashes($metatags['keywords']);

?>