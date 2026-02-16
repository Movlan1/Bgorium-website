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
 File: download.php
-----------------------------------------------------
 Use: Files download
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../' );
	die( "Hacking attempt!" );
}

if( isset($_REQUEST['mode']) AND $_REQUEST['mode'] == 'error') {

	$lang['download_error'] = str_replace('{count}', $user_group[$member_id['user_group']]['max_downloads'], $lang['download_error'] );

	@header("HTTP/1.0 403 Forbidden");
	msgbox($lang['all_err_1'], $lang['download_error']);

} else {

	if ($config['allow_registration']) {
		include_once(DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php'));
	}

	if (!$is_logged) {
		$member_id['user_group'] = 5;
	}

	require_once(DLEPlugins::Check(ENGINE_DIR . '/classes/download.class.php'));

	$id = isset($_REQUEST['id']) ?  intval($_REQUEST['id']) : 0;
	$viewonline = isset($_REQUEST['viewonline']) ? true : false;

	$perm = true;
	$onlineview_ext = array("pdf", "doc", "docx", "docm", "dotm", "dotx", "xlsx", "xlsb", "xls", "xlsm", "pptx", "ppsx", "ppt", "pps", "pptm", "potm", "ppam", "potx", "ppsm", "odt", "odx");
	$full_link = $config['http_home_url'];

	if (isset($_REQUEST['area']) and $_REQUEST['area'] == "static") {

		$row = $db->super_query("SELECT * FROM " . PREFIX . "_static_files WHERE id ='{$id}'");

		$row_news = $db->super_query("SELECT id, name FROM " . PREFIX . "_static WHERE id ='{$row['static_id']}'");

		if (isset($row_news['id']) and $row_news['id']) {

			if ($config['allow_alt_url']) $full_link = $config['http_home_url'] . $row_news['name'] . ".html";
			else $full_link = $config['http_home_url'] . "index.php?do=static&page=" . $row_news['name'];

		}
		
	} else {

		$row = $db->super_query("SELECT * FROM " . PREFIX . "_files WHERE id ='{$id}'");

		if (isset($row['news_id']) and $row['news_id'] and !$viewonline) {

			$row_news = $db->super_query("SELECT id, autor, date, category, alt_name, approve, access FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id ='{$row['news_id']}'");

			if ($row_news['id']) {

				$row_news['date'] = strtotime($row_news['date']);

				if ($config['allow_alt_url']) {

					if ($config['seo_type'] == 1 or $config['seo_type'] == 2) {

						if ($row_news['category'] and $config['seo_type'] == 2) {

							$full_link = $config['http_home_url'] . get_url($row_news['category']) . "/" . $row_news['id'] . "-" . $row_news['alt_name'] . ".html";
						} else {

							$full_link = $config['http_home_url'] . $row_news['id'] . "-" . $row_news['alt_name'] . ".html";
						}
					} else {

						$full_link = $config['http_home_url'] . date('Y/m/d/', $row_news['date']) . $row_news['alt_name'] . ".html";
					}
				} else {

					$full_link = $config['http_home_url'] . "index.php?newsid=" . $row_news['id'];
				}

				$options = news_permission($row_news['access']);
				if (isset($options[$member_id['user_group']]) and $options[$member_id['user_group']] and $options[$member_id['user_group']] != 3) $perm = true;
				if (isset($options[$member_id['user_group']]) and $options[$member_id['user_group']] == 3) $perm = false;

				if ($config['no_date'] and !$config['news_future'] and !$user_group[$member_id['user_group']]['allow_all_edit']) {

					if ($row_news['date'] > $_TIME) {
						$perm = false;
					}
				}

				$cat_list = explode(',', $row_news['category']);

				if (count($cat_list)) {

					$allow_list = explode(',', $user_group[$member_id['user_group']]['allow_cats']);
					$not_allow_cats = explode(',', $user_group[$member_id['user_group']]['not_allow_cats']);

					foreach ($cat_list as $element) {

						if ($allow_list[0] != "all" and !in_array($element, $allow_list)) $perm = false;

						if ($not_allow_cats[0] != "" and in_array($element, $not_allow_cats)) $perm = false;
					}
				}

				if (!$row_news['approve'] and $member_id['name'] != $row_news['autor'] and !$user_group[$member_id['user_group']]['allow_all_edit']) $perm = false;
			}

		}
		
	}

	if (!$perm) {
		header("HTTP/1.1 403 Forbidden");
		die("You don't have access to download this file");
	}

	if (!isset($row['name']) OR !isset($row['onserver']) OR !$row['name'] OR !$row['onserver']) {
		header("HTTP/1.1 403 Forbidden");
		die("Access denied");
	}

	$file_name = pathinfo($row['onserver']);

	if ($viewonline AND in_array($file_name['extension'], $onlineview_ext)) {

		$config['files_antileech'] = false;
		$user_group[$member_id['user_group']]['allow_files'] = true;

	} else $viewonline = false;

	if (!$user_group[$member_id['user_group']]['allow_files']) {
		header("HTTP/1.1 403 Forbidden");
		die("Access denied");
	}

	if ($config['files_antileech']) {

		function reset_url($url)
		{
			$url = (string)$url;

			$value = str_replace("http://", "", $url);
			$value = str_replace("https://", "", $value);
			$value = str_replace("www.", "", $value);
			$value = explode("/", $value);
			$value = reset($value);
			return $value;
		}

		$_SERVER['HTTP_REFERER'] = reset_url($_SERVER['HTTP_REFERER']);
		$_SERVER['HTTP_HOST'] = reset_url($_SERVER['HTTP_HOST']);

		if ($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) {
			header("HTTP/1.1 403 Forbidden");
			header("Location: " . $full_link);
			die("Access denied!!!<br /><br />Please visit <a href=\"{$config['http_home_url']}\">{$config['http_home_url']}</a>");
		}
	}

	if ($row['is_public']) $uploaded_path = 'public_files/';
	else $uploaded_path = 'files/';

	$file = new download($uploaded_path . $row['onserver'], $row['name'], $row['driver']);

	if ($user_group[$member_id['user_group']]['max_downloads'] and !$viewonline) {

		$today_time = strtotime('today midnight');

		if( $today_time ) {
			$db->query("DELETE FROM " . USERPREFIX . "_downloads_log WHERE date < '{$today_time}'");
		}

		$_IP = $db->safesql($_IP);

		if ($is_logged and $member_id['user_id']) {
			$where = "user_id ='{$member_id['user_id']}'";
		} else {
			$where = "ip ='{$_IP}'";
		}

		$down_log = $db->super_query("SELECT id FROM " . USERPREFIX . "_downloads_log WHERE file_id ='{$id}' AND {$where}");

		if (isset($down_log['id']) and $down_log['id']) $downloaded = true;
		else $downloaded = false;

		if (!$downloaded) {

			$down_log = $db->super_query("SELECT count(*) as count FROM " . USERPREFIX . "_downloads_log WHERE {$where}");

			if ($down_log['count'] >= $user_group[$member_id['user_group']]['max_downloads']) {

				header("HTTP/1.0 301 Moved Permanently");
				header("Location: ?do=download&mode=error");
				die("Redirect");
			}

			if ($is_logged and $member_id['user_id']) {
				$db->query("INSERT INTO " . USERPREFIX . "_downloads_log (user_id, file_id, date) VALUES('{$member_id['user_id']}', '{$id}', '{$_TIME}')");
			} else {
				$db->query("INSERT INTO " . USERPREFIX . "_downloads_log (ip, file_id, date) VALUES('{$_IP}', '{$id}', '{$_TIME}')");
			}

		}
	}

	if (isset($_REQUEST['area']) and $_REQUEST['area'] == "static") {

		if ($config['files_count'] and !$file->range) {
			$db->query("UPDATE " . PREFIX . "_static_files SET dcount=dcount+1 WHERE id ='$id'");
		}
	} else {

		if ($config['files_count'] and !$file->range) {
			$db->query("UPDATE " . PREFIX . "_files SET dcount=dcount+1 WHERE id ='$id'");
		}
	}

	$db->close();
	session_write_close();

	$file->download_file();

	die();

}