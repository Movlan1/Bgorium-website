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

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$id = isset( $_REQUEST['fav_id'] ) ? intval( $_REQUEST['fav_id'] ) : 0;

if( !$id OR $id < 1) {
	
	echo json_encode(array("error" => true, "content" => 'action not correct' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
	
}

if( !$is_logged ){
	
	echo json_encode(array("error" => true, "content" => $lang['fav_error'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
	
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

	echo json_encode(array("error" => true, "content" => $lang['sess_error'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
	
}

$row = $db->super_query("SELECT id, approve, category FROM " . PREFIX . "_post WHERE id ='{$id}'" );

if( !$row['id'] ) {
	echo json_encode(array("error" => true, "content" => $lang['news_page_err'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
}

if( !$row['approve'] AND $_REQUEST['action'] == "plus") {
	echo json_encode(array("error" => true, "content" => $lang['fav_plus_err_2'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
}

$replace_fav = [];

if (defined('TEMPLATE_DIR')) {
	$template_dir = TEMPLATE_DIR;
} else $template_dir = ROOT_DIR . "/templates/" . $config['skin'];

if( $_REQUEST['module'] === 'short' OR $_REQUEST['module'] === 'full' ) {
	
	$category_id = intval($row['category']);

	if($_REQUEST['module'] == 'short') {
		
		if ($category_id and $cat_info[$category_id]['short_tpl']) $data = file_get_contents($template_dir . '/' . $cat_info[$category_id]['short_tpl'] . '.tpl');
		else $data = file_get_contents($template_dir . "/shortstory.tpl");

	} else {
		
		if ($category_id AND $cat_info[$category_id]['full_tpl']) $data = file_get_contents($template_dir . '/' . $cat_info[$category_id]['full_tpl'] . '.tpl');
		else $data = file_get_contents($template_dir . "/fullstory.tpl");

	}

	if (preg_match("'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", $data, $match)) {
		$replace_fav['add_fav_html'] = "<a onclick=\"doFavorites('{$id}', 'plus', 1, '{$_REQUEST['module']}'); return false;\" href=\"{$_ROOT_DLE_URL}index.php?do=favorites&doaction=add&id={$id}\">".$match[1]."</a>";
	}

	if (preg_match("'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", $data, $match)) {
		$replace_fav['del_fav_html'] = "<a onclick=\"doFavorites('{$id}', 'minus', 1, '{$_REQUEST['module']}'); return false;\" href=\"{$_ROOT_DLE_URL}index.php?do=favorites&doaction=del&id={$id}\">".$match[1]."</a>";
	}

}

if( $_REQUEST['action'] == "plus" ) {

	if( trim($member_id['favorites']) ) {
		
		$list = explode(",", $member_id['favorites'] );
		$i = 0;
		
		foreach ( $list as $daten ) {
	
			if( $daten == $id ) {
				echo json_encode(array("error" => true, "content" => $lang['fav_plus_err_1'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				die ();
			}
			
			$daten = intval($daten);
			
			if( !$daten OR $daten < 1 ) unset( $list[$i] );
			
			$i ++;
	
		}
	
	} else $list = array();
	
	$list[] = $id;

	if( count( $list ) ) $member_id['favorites'] = $db->safesql(implode( ",", $list ));
	else $member_id['favorites'] = "";
	
	$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$member_id['favorites']}' WHERE user_id = '{$member_id['user_id']}'" );
	
	if( isset($replace_fav['add_fav_html']) ) unset($replace_fav['add_fav_html']);

	if ( $_REQUEST['alert'] ) $buffer = $lang['fav_plus'];
	else $buffer = "<img src=\"" . $_ROOT_DLE_URL . "templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('" . $id . "', 'minus'); return false;\" title=\"" . $lang['news_minfav'] . "\" style=\"vertical-align: middle;border: none;\" />";

} elseif( $_REQUEST['action'] == "minus" ) {
	
	if( trim($member_id['favorites']) ) {
		
		$list = explode(",", $member_id['favorites'] );
		$i = 0;
		
		foreach ( $list as $daten ) {
			
			$daten = intval($daten);
				
			if( !$daten OR $daten < 1 ) unset( $list[$i] );
			
			if( $daten == $id ) unset( $list[$i] );
			
			$i ++;
	
		}
		
	} else $list = array();

	if( count( $list ) ) $member_id['favorites'] = $db->safesql(implode( ",", $list ));
	else $member_id['favorites'] = "";
	
	$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$member_id['favorites']}' WHERE user_id = '{$member_id['user_id']}'" );
	
	if (isset($replace_fav['del_fav_html'])) unset($replace_fav['del_fav_html']);

	if ( $_REQUEST['alert'] ) $buffer = $lang['fav_minus'];
	else $buffer = "<img src=\"" . $_ROOT_DLE_URL . "templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('" . $id . "', 'plus'); return false;\" title=\"" . $lang['news_addfav'] . "\" style=\"vertical-align: middle;border: none;\">";

} else {
	
	echo json_encode(array("error" => true, "content" => 'action not correct' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	die ();
	
}

echo json_encode(array("success" => true, "content" => $buffer, "modify" => $replace_fav ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
?>