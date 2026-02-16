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
 File: findcomments.php
-----------------------------------------------------
 Use: Find Comments on the website
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post_id = isset($_GET['postid']) ? intval($_GET['postid']) : 0;

if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "https:" . $config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

if(!$comment_id OR !$comment_id) {
	header("HTTP/1.0 301 Moved Permanently");
	header("Location: {$config['http_home_url']}");
	die("Redirect");
}

function build_comments_tree($data){

	$tree = array();
	foreach ($data as $id => &$node) {
		if ($node['parent'] === false) {
			$tree[$id] = &$node;
		} else {
			if (!isset($data[$node['parent']]['children'])) $data[$node['parent']]['children'] = array();
			$data[$node['parent']]['children'][$id] = &$node;
		}
	}

	return $tree;
}

function searchByFieldValue($array, $field, $value){

	foreach ($array as $item) {

		if (isset($item[$field]) and $item[$field] == $value) {

			return true;
		}

		if (isset($item['children']) and is_array($item['children'])) {
			if (searchByFieldValue($item['children'], $field, $value)) {
				return true;
			}
		}
	}

	return false;
}

$rows = array();

if ($config['allow_cmod']) $where_approve = " AND " . PREFIX . "_comments.approve=1";
else $where_approve = "";

$sql_result = $db->query("SELECT " . PREFIX . "_comments.id, " . PREFIX . "_comments.parent FROM " . PREFIX . "_comments WHERE " . PREFIX . "_comments.post_id = '{$post_id}'{$where_approve}  ORDER BY " . PREFIX . "_comments.id ASC");

while ($row = $db->get_row($sql_result)) {
	$rows[$row['id']] = array();

	foreach ($row as $key => $value) {
		if ($key == "parent" and ($value == 0 or !$config['tree_comments'])) $value = false;
		$rows[$row['id']][$key] = $value;
	}
}

$db->free($sql_result);
unset($row);

if (count($rows)) {
	$rows = build_comments_tree($rows);

	if ($config['comm_msort'] == "DESC") $rows = array_reverse($rows, true);

	$rows = array_chunk($rows, intval($config['comm_nummers']));

	$page = 1;
	$page_found = false;

	foreach ($rows as $arr) {

		if (searchByFieldValue($arr, 'id', $comment_id)) {
			$page_found = true;
			break;
		}

		$page++;
	}

	if ($page && $page_found) {
		
		$row = $db->super_query("SELECT id, short_story, title, date, alt_name, category FROM " . PREFIX . "_post WHERE id = '{$post_id}'");

		$row['date'] = strtotime($row['date']);
		$row['category'] = intval($row['category']);

		if ($config['allow_alt_url']) {

			if ($config['seo_type'] == 1 or $config['seo_type'] == 2) {

				if ($row['category'] and $config['seo_type'] == 2) {

					$full_link = $config['http_home_url'] . get_url($row['category']) . "/{cpage}" . $row['id'] . "-" . $row['alt_name'] . ".html";
				} else {

					$full_link = $config['http_home_url'] . "{cpage}" . $row['id'] . "-" . $row['alt_name'] . ".html";
				}
			} else {

				$full_link = $config['http_home_url'] . date('Y/m/d/', $row['date']) . "{cpage}" . $row['alt_name'] . ".html";
			}
		} else {

			$full_link = $config['http_home_url'] . "index.php?newsid={$row['id']}{cpage}";
		}

		if( $page > 1 ) {

			if ($config['allow_alt_url']) {
				$full_link = str_replace("{cpage}", "page,1,{$page},", $full_link);
			} else $full_link = str_replace("{cpage}", "&cstart={$page}", $full_link);

		} else {
			$full_link = str_replace("{cpage}", "", $full_link);
		}

		$full_link .= '#findcomment' . $comment_id;

		header("HTTP/1.0 301 Moved Permanently");
		header("Location: {$full_link}");
		die("Redirect");
	}
}

header("HTTP/1.0 301 Moved Permanently");
header("Location: {$config['http_home_url']}");
die("Redirect");

?>