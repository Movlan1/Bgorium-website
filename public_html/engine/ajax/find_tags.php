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
 File: find_tags.php
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	die( "error" );
}

if( !isset($_GET['term']) ) die("[]");

if( !$_GET['term'] ) die("[]");

$_GET['mode'] = isset($_GET['mode']) ? $_GET['mode'] : '';

if ($_GET['mode'] == "authors") {
	
	if (!$user_group[$member_id['user_group']]['admin_addnews'] OR !$user_group[$member_id['user_group']]['admin_editnews']) die("[]");

	$buffer = "[]";
	$tags = array();
	
	$search_name = $db->safesql(trim(htmlspecialchars(strip_tags($_GET['term']), ENT_QUOTES, 'UTF-8')));
	
	$db->query("SELECT name FROM " . USERPREFIX . "_users WHERE name LIKE '{$search_name}%' ORDER BY lastdate DESC LIMIT 10");

	while ($row = $db->get_row()) {

		$tags[] = $row['name'];
	}

	if (count($tags)) $buffer = "[\"" . implode("\",\"", $tags) . "\"]";

	echo $buffer;

} elseif ($_GET['mode'] == "users") {

	if( !$user_group[$member_id['user_group']]['allow_addc'] OR !$config['allow_comments']) die("[]");

	$buffer = array();
	$buffer['found'] = false;

	$search_name = $db->safesql(trim(htmlspecialchars(strip_tags($_GET['term']), ENT_QUOTES, 'UTF-8')));

	$db->query("SELECT * FROM " . USERPREFIX . "_users WHERE name LIKE '{$search_name}%' ORDER BY lastdate DESC LIMIT 10");
	
	while ($row = $db->get_row()) {
		$buffer['found'] = true;

		if ($config['allow_alt_url']) {

			$go_page = $_ROOT_DLE_URL . "user/" . urlencode($row['name']) . "/";
		} else {

			$go_page = "{$_ROOT_DLE_URL}index.php?subaction=userinfo&amp;user=" . urlencode($row['name']);
		}


		$value = "<span class=\"comments-user-profile noncontenteditable\" data-username=\"".urlencode($row['name'])."\" data-userurl=\"{$go_page}\">@{$row['name']}</span> ";

		if (count(explode("@", $row['foto'])) == 2) {

			$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);

		} else {

			if ($row['foto']) {

				if (strpos($row['foto'], "//") === 0) $avatar = "https:" . $row['foto'];
				else $avatar = $row['foto'];

				$avatar = @parse_url($avatar);

				if ($avatar['host']) {
					$avatar = $row['foto'];
				} else $avatar = $_ROOT_DLE_URL . "uploads/fotos/" . $row['foto'];

			} else $avatar = $_ROOT_DLE_URL."templates/". $config['skin']."/dleimages/noavatar.png";

		}

		$avatar = "<img src=\"{$avatar}\">";

		$buffer['items'][] = array(
			'type' => "autocompleteitem",
			'text' => $row['name'],
			'value' => $value,
			'icon' => $avatar
		);

	}

	echo json_encode($buffer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	die();


} else {

	$buffer = "[]";

	$tags = array();

	if ($_GET['mode'] == "xfield") {

		$term = dle_strtolower(htmlspecialchars(strip_tags(stripslashes(trim(rawurldecode($_GET['term'])))), ENT_QUOTES, 'UTF-8'));
		$term = $db->safesql(str_replace(array("{", "[", ":", "&amp;frasl;"), array("&#123;", "&#91;", "&#58;", "/"), $term));

		$db->query("SELECT tagvalue as tag, COUNT(*) AS count FROM " . PREFIX . "_xfsearch WHERE LOWER(`tagvalue`) like '{$term}%' GROUP BY tagvalue ORDER by count DESC LIMIT 15");

	} else {

		if (preg_match("/[\||\<|\>]/", $_GET['term'])) $term = "";
		else $term = $db->safesql(dle_strtolower(htmlspecialchars(strip_tags(stripslashes(trim(rawurldecode($_GET['term'])))), ENT_COMPAT, 'UTF-8')));

		if (!$term) die("[]");

		$db->query("SELECT tag, COUNT(*) AS count FROM " . PREFIX . "_tags WHERE LOWER(`tag`) like '{$term}%' GROUP BY tag ORDER by count DESC LIMIT 15");
	}

	while ($row = $db->get_row()) {

		$row['tag'] = html_entity_decode($row['tag'], ENT_QUOTES | ENT_XML1, 'UTF-8');
		$row['tag'] = str_replace('"', '\"', $row['tag']);


		$tags[] = $row['tag'];
	}

	if (count($tags)) $buffer = "[\"" . implode("\",\"", $tags) . "\"]";

	echo $buffer;
}

?>