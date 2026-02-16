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
 File: rating.php
-----------------------------------------------------
 Use: AJAX rating news
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	echo "{\"error\":true, \"errorinfo\":\"{$lang['sess_error']}\"}";
	die();
}

if( ! $is_logged ) $member_id['user_group'] = 5;

if( ! $user_group[$member_id['user_group']]['allow_rating'] ) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
		die();
}

if( isset($_REQUEST['go_rate']) AND $_REQUEST['go_rate'] == "minus" ) $_REQUEST['go_rate'] = -1;
if( isset($_REQUEST['go_rate']) AND $_REQUEST['go_rate'] == "plus" ) $_REQUEST['go_rate'] = 1;

$go_rate = isset($_REQUEST['go_rate']) ? intval( $_REQUEST['go_rate'] ) : 0 ;
$news_id = isset($_REQUEST['go_rate']) ? intval( $_REQUEST['news_id'] ) : 0;
$negative_rate = false;

$row = $db->super_query( "SELECT id, category FROM " . PREFIX . "_post WHERE id ='{$news_id}'" );

if( !isset($row['id']) OR !$row['id'] ) {
	echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
	die();
}

$temp_rating = $config['rating_type'];
$config['rating_type'] = if_category_rating( $row['category'] );
	
if ( $config['rating_type'] === false ) {
	$config['rating_type'] = $temp_rating;
}
	
if ( !$config['rating_type'] ) {
	if( $go_rate > 5 or $go_rate < 1 ) $go_rate = false;

	if( $go_rate < 3 ) $negative_rate = true;
}

if ( $config['rating_type'] == "1" ) {
	$go_rate = 1;
}

if ( $config['rating_type'] == "2" OR $config['rating_type'] == "3") {
	if( $go_rate != 1 AND $go_rate != -1 ) $go_rate = false;

	if ( $go_rate == -1 ) $negative_rate = true;
}

if( !$go_rate ) {
	echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
	die();
}

if ($user_group[$member_id['user_group']]['max_n_negative'] AND $negative_rate ) {
	
	$this_time = $_TIME - 86400;
	$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '{$this_time}' AND flag='5'" );

	if ( !$is_logged ) $check_user = $_IP; else $check_user = $db->safesql($member_id['name']);

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_sendlog WHERE user = '{$check_user}' AND flag='5'");

	if ($row['count'] >=  $user_group[$member_id['user_group']]['max_n_negative']) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error6']}\"}";
		die();
	}

	$db->query("INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$check_user}', '{$_TIME}', '5')");		

}

if( $is_logged ){
	$member_id['name'] = $db->safesql($member_id['name']);
	$where = "`member` = '{$member_id['name']}'";
} else $where = "ip ='{$_IP}'";

$row = $db->super_query( "SELECT news_id, rating FROM " . PREFIX . "_logs WHERE news_id ='{$news_id}' AND {$where}" );

if( !isset($row['news_id']) OR !$row['news_id'] ) {

	$allrate = $db->super_query( "SELECT allow_rate, rating, user_id FROM " . PREFIX . "_post_extras WHERE news_id ='{$news_id}'" );
	
	if( isset($member_id['user_id']) AND isset($allrate['user_id']) AND $allrate['user_id'] == $member_id['user_id'] ) {
		
		$db->close();
		
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error1']}\"}";
		die();
	}
	
	if( !$allrate['allow_rate'] ) {
		
		$db->close();
		
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
		die();
	}
	
	if( $config['rating_type'] == "1" AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} elseif ( !$config['rating_type'] AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} else {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating+'{$go_rate}', vote_num=vote_num+1 WHERE news_id ='{$news_id}'" );
		
	}	

	if ( $db->get_affected_rows() )	{
		if( $is_logged ) $user_name = $member_id['name'];
		else $user_name = "";
		
		$db->query( "INSERT INTO " . PREFIX . "_logs (news_id, ip, `member`, rating) values ('{$news_id}', '{$_IP}', '{$user_name}', '{$go_rate}')" );

		if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$news_id;	
	
		clear_cache( array( 'news_', $cprefix ) );

	}
	
} elseif( $row['rating'] AND $row['rating'] != $go_rate ) {
	
	$allrate = $db->super_query( "SELECT rating, user_id FROM " . PREFIX . "_post_extras WHERE news_id ='{$news_id}'" );
	
	if( $config['rating_type'] == "1" AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} elseif ( !$config['rating_type'] AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} else {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating-'{$row['rating']}' WHERE news_id ='{$news_id}'" );
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating+'{$go_rate}' WHERE news_id ='{$news_id}'" );
		
	}
	
	$db->query( "UPDATE " . PREFIX . "_logs SET rating='{$go_rate}' WHERE news_id ='{$news_id}' AND {$where}" );
	
	if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$news_id;
	clear_cache( array( 'news_', $cprefix ) );
	
} else {
	
	$db->close();
	
	echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error2']}\"}";
	die();
}

$row = $db->super_query( "SELECT news_id, rating, vote_num FROM " . PREFIX . "_post_extras WHERE news_id ='{$news_id}'" );

if ( $config['rating_type'] ) {
	$dislikes = ($row['vote_num'] - $row['rating'])/2;
	$likes = $row['vote_num'] - $dislikes;	
} else {
	$dislikes = 0;
	$likes = 0;	
}

$buffer = ShowRating( $row['news_id'], $row['rating'], $row['vote_num'], true );

$buffer = addcslashes($buffer, "\t\n\r\"\\/");

$buffer = htmlspecialchars("{\"success\":true, \"rating\":\"{$buffer}\", \"votenum\":\"{$row['vote_num']}\", \"likes\":\"{$likes}\", \"dislikes\":\"{$dislikes}\"}", ENT_NOQUOTES, 'UTF-8');

$db->close();

echo $buffer;
?>