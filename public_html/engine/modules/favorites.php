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
 File: favorites.php
=====================================================
*/
if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( isset( $_REQUEST['doaction'] ) ) $doaction = $_REQUEST['doaction']; else $doaction = "";

$allow_add_comment = false;
$allow_full_story = false;
$allow_comments = false;
$allow_userinfo = false;
$allow_active_news = true;
$where_date = "";
$config['allow_cache'] = false;

if( ! isset( $cstart ) ) $cstart = 0;

if( $cstart ) {
	$cstart = $cstart - 1;
	$cstart = $cstart * $config['news_number'];
	$start_from = $cstart;
}

$cstart = intval($cstart);

$url_page = $config['http_home_url'] . "favorites";
$user_query = "do=favorites";

$list = explode( ",", $member_id['favorites'] );
$list = array_reverse ( $list );
$fav_list = array();
$order_list = array();

foreach ( $list as $daten ) {
	$daten = intval($daten);
	$fav_list[] = "'" . $daten . "'";
	$order_list[] = $daten;
}

$list = implode( ",", $fav_list );

$favorites = "(" . $list . ")";

if( count($order_list) ) {
	
	$order_list = implode( ",", $order_list );
	$order_list = "ORDER BY FIND_IN_SET(id, '".$order_list."') ";

} else $order_list = "";

$stop_list = "";
$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
$cat_join = "";

if( $allow_list[0] != "all" ) {
	
	if( $config['allow_multi_category'] ) {
		
		$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode( "','", $allow_list ) . "')) c ON (p.id=c.news_id) ";
	
	} else {
		
		$stop_list = "category IN ('" . implode( "','", $allow_list ) . "') AND ";
	
	}

}

$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );

if( $not_allow_cats[0] != "" ) {
			
	if ($config['allow_multi_category']) {
			
		$stop_list = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode( "','", $not_allow_cats ) . "') ) AND ";
		
	} else {
			
		$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
		
	}

}

if( $config['user_in_news'] ) {
	
	$user_select = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields ";
	$user_join = "LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id) ";
	
} else { $user_select = ""; $user_join = ""; }
		
if( $user_group[$member_id['user_group']]['allow_short'] ) $stop_list = "";

$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p {$cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE {$stop_list}approve=1 AND id in $favorites " .$order_list . "LIMIT " . $cstart . "," . $config['news_number'];
$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post p {$cat_join}WHERE {$stop_list}approve=1 AND id in {$favorites}";

require (DLEPlugins::Check(ENGINE_DIR . '/modules/show.short.php'));

if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
	$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
}
