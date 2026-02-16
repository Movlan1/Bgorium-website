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
 File: deletenews.php
-----------------------------------------------------
 Use: delete news
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$_SESSION['referrer'] = isset($_SESSION['referrer']) ? str_replace("&amp;","&", $_SESSION['referrer'] ) : '../../';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id < 1 OR !$is_logged OR !isset($_REQUEST['hash']) OR !$_REQUEST['hash'] OR $_REQUEST['hash'] != $dle_login_hash) {
	header("HTTP/1.1 403 Forbidden");
	die("Hacking attempt! Not valid data");
}

if ($user_group[$member_id['user_group']]['moderation'] AND ($user_group[$member_id['user_group']]['allow_all_edit'] OR $user_group[$member_id['user_group']]['allow_edit']) ) {

	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$row = $db->super_query("SELECT id, title, category, alt_name FROM " . PREFIX . "_post WHERE id = '{$id}'");
	} else {
		$row = $db->super_query("SELECT id, title, category, alt_name FROM " . PREFIX . "_post WHERE id = '{$id}' AND autor='" . $db->safesql($member_id['name']) . "'");
	}

	if (isset($row['id']) AND $row['id']) {

		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
		$category = explode( ',', $row['category'] );
			
		foreach ( $category as $selected ) {

			if( $allow_list[0] != "all" AND !in_array( $selected, $allow_list ) AND $member_id['user_group'] != 1 ) {
				header("Location: {$_SESSION['referrer']}");
				die();
			}

		}

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '26', '".$db->safesql($row['title'])."')" );

		deletenewsbyid( $row['id'] );

		clear_cache(array('news_', 'full_' . $row['id'], 'comm_' . $row['id'], 'tagscloud_', 'archives_', 'related_', 'calendar_', 'rss', 'stats'));

	} else {
		
		header("HTTP/1.1 403 Forbidden");
		header("Location: {$_SESSION['referrer']}");
		die("Hacking attempt! ID not found");

	}


	if ( strpos( $_SESSION['referrer'], $row['alt_name'] ) !== false OR strpos( $_SESSION['referrer'], "newsid=".$row['id'] ) !== false OR strpos( $_SESSION['referrer'], "do=deletenews" ) !== false OR $_SESSION['referrer'] == "") { 

		msgbox ($lang['all_info'], $lang['news_del_ok']);

	} else {

		header("Location: {$_SESSION['referrer']}");
		die();

	}

} else {
	
	header("HTTP/1.1 403 Forbidden");
	header("Location: {$_SESSION['referrer']}");
  	die("Hacking attempt! User Not allow delete");
  
}