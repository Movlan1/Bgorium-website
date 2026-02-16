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
 File: find_relates.php
-----------------------------------------------------
 Use: Search for relates news
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$is_logged ) die( "error" );

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	die( "error" );
}

function strip_data($text)
{

	$quotes = array("\x60", "\t", "\n", "\r", ".", ",", ";", ":", "&", "(", ")", "[", "]", "{", "}", "=", "*", "^", "%", "$", "<", ">", "+", "-");
	$goodquotes = array("'", '"');
	$repquotes = array("\'", '\"');
	$bom = pack('H*', 'EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	$text = stripslashes($text);
	$text = trim(strip_tags($text));
	$text = str_replace($quotes, ' ', $text);
	$text = str_replace($goodquotes, $repquotes, $text);

	return $text;
}

$query = isset($_POST['title']) ? dle_substr(strip_data($_POST['title']), 0, 250) : '';

if (!$query) die();

if ($config['full_search']) {

	$arr = explode(' ', $query);
	$query = array();

	foreach ($arr as $word) {
		$wordlen = dle_strlen(trim($word));

		if ($wordlen >= $config['search_length_min']) {

			$word =  $db->safesql($word);
			$word = '"' . $word . '"';

			$query[] = $word;
		}
	}

	if (count($query)) $query = '+' . implode(" +", $query);
	else $query = '';

} else {

	$arr = explode(' ', $query);
	$query = array();

	foreach ($arr as $word) {
		$wordlen = dle_strlen(trim($word));

		if ($wordlen >= $config['search_length_min']) $query[] = $db->safesql(addslashes($word));
	}

	if (count($query)) $query = implode("%", $query);
	else $query = '';
}

if( !$query ) die();

$buffer = "";
$full_s_addfield = "";

$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
$mode = isset($_POST['mode']) ? intval( $_POST['mode']) : 0;

if ( $mode ) {
	if( !$user_group[$member_id['user_group']]['allow_adds'] ) die( "error" );
} else {
	if( !$user_group[$member_id['user_group']]['allow_admin'] ) die( "error" );
}

if( $id ) $where = " AND id != '" . $id . "'";
else $where = "";

if ($config['full_search']) {

	$find_where = "MATCH(p.title, p.short_story, p.full_story, p.xfields) AGAINST ('{story}' IN BOOLEAN MODE)";
	$full_s_addfield = ", " . $find_where . " as score";
	$full_s_addfield = str_replace("{story}", $query, $full_s_addfield);

} else {

	$find_where = "p.short_story LIKE '%{story}%' OR p.full_story LIKE '%{story}%' OR p.xfields LIKE '%{story}%' OR p.title LIKE '%{story}%'";
}

$find_where = str_replace("{story}", $query, $find_where);

$sql = "SELECT p.id, p.title, p.date, p.category, p.alt_name{$full_s_addfield} FROM " . PREFIX . "_post p WHERE p.approve=1{$where}";

if ($config['full_search']) {
	$sql .= " AND {$find_where} ORDER by score DESC LIMIT 5";
} else {
	$sql .= " AND ({$find_where}) ORDER by date DESC LIMIT 5";
}

$db->query($sql);

while ( $related = $db->get_row() ) {
	
	$related['date'] = strtotime( $related['date'] );
	$related['category'] = intval( $related['category'] );
	$news_date = date( 'd-m-Y', $related['date'] );
	
	if( $config['allow_alt_url'] ) {
		
		if( $config['seo_type'] == 1 OR  $config['seo_type'] == 2 ) {
			
			if( $related['category'] and $config['seo_type'] == 2 ) {
				
				$full_link = $_ROOT_DLE_URL . get_url( $related['category'] ) . "/" . $related['id'] . "-" . $related['alt_name'] . ".html";
			
			} else {
				
				$full_link = $_ROOT_DLE_URL . $related['id'] . "-" . $related['alt_name'] . ".html";
			
			}
		
		} else {
			
			$full_link = $_ROOT_DLE_URL . date( 'Y/m/d/', $related['date'] ) . $related['alt_name'] . ".html";
		}
	
	} else {
		
		$full_link = $_ROOT_DLE_URL . "index.php?newsid=" . $related['id'];
	
	}

	if ( dle_strlen($related['title']) > 65 ) $related['title'] = dle_substr ($related['title'], 0, 65)." ...";

	if ( $user_group[$member_id['user_group']]['allow_all_edit'] ) {

		$d_link = "<a title=\"{$lang['edit_etitle']}\" href=\"?mod=editnews&action=editnews&id={$related['id']}\" target=\"_blank\"><i class=\"fa fa-pencil-square-o position-left\"></i></a><a title=\"{$lang['edit_seldel']}\" onclick=\"confirmDelete('?mod=editnews&action=doeditnews&ifdelete=yes&id={$related['id']}&user_hash={$dle_login_hash}', '{$related['id']}'); return false;\" href=\"?mod=editnews&action=doeditnews&ifdelete=yes&id={$related['id']}&user_hash={$dle_login_hash}\" target=\"_blank\"><i class=\"fa fa-trash-o position-left text-danger\"></i></a>";

	} else $d_link = "";

	if ( $mode ) $d_link = "";
	
	$buffer .= "<div style=\"padding:2px;\">{$d_link}{$news_date} - <a href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $related['title'] ) . "</a></div>";

}

$db->close();

if( $buffer ) echo "<div class=\"findrelated\">" . $buffer . "</div>";
else echo "<div class=\"findrelated\">" . $lang['related_not_found'] . "</div>";

?>