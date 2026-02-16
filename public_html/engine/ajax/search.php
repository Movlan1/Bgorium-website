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
 File: search.php
-----------------------------------------------------
 Use: Fast search
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$config['fast_search'] OR !$user_group[$member_id['user_group']]['allow_search'] ) die( "error" );

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

	echo $lang['sess_error'];
	die();

}

function strip_data($text) {

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

if (isset($_POST['query'])) {
	$query = $static_query = dle_substr(strip_data($_POST['query']), 0, 90); 
} else {
	$query = $static_query = "";
}

$link_query = '<span class="seperator"><a href="' . $_ROOT_DLE_URL . '?do=search&amp;mode=advanced&amp;subaction=search&amp;story=' . rawurlencode($query) . '">' . $lang['s_ffullstart'] . '</a></span>';
$not_found =  "<span class=\"notfound\">{$lang['related_not_found']}</span>";

$config['fastsearch_result'] = intval($config['fastsearch_result']);
if($config['fastsearch_result'] < 1 OR $config['fastsearch_result'] > 1000) $config['fastsearch_result'] = 5;

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
	
	if( count($query) ) $query = '+' . implode(" +", $query);
	else $query = '';

} else {


	$arr = explode(' ', $query);
	$query = array();

	foreach ($arr as $word) {
		$wordlen = dle_strlen(trim($word));

		if ($wordlen) $query[] = $db->safesql(addslashes($word));

	}

	if (count($query)) $query = implode("%", $query);
	else $query = '';

}

if( !$query ) {
	echo $not_found.$link_query;
	die();
}

$_TIME = time ();
$this_date = date( "Y-m-d H:i:s", $_TIME );
if( $config['no_date'] AND !$config['news_future'] ) $this_date = " AND p.date < '" . $this_date . "'"; else $this_date = "";
$full_s_addfield = "";

$disable_search = array();

if( count( $cat_info ) ) {
	foreach ($cat_info as $cats) {
		if($cats['disable_search']) $disable_search[] = $cats['id'];
	}
}

if( $user_group[$member_id['user_group']]['not_allow_cats'] ) {
	$n_c = explode(',', $user_group[$member_id['user_group']]['not_allow_cats'] );
	
	foreach ($n_c as $cats) {
		if(!in_array($cats, $disable_search)) $disable_search[] = $cats;
	}

}

if( count( $disable_search ) ) {

	if( $config['allow_multi_category'] ) {
		
		$where_category = " AND p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode ("','", $disable_search ) . "') )";
	
	} else {
		
		$where_category = " AND category NOT IN ('" . implode ("','", $disable_search ) . "')";
	}
	
} else $where_category = "";

if ($config['user_in_news']) {
	$user_select = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields";
	$user_join = " LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id)";
} else {
	$user_select = "";
	$user_join = "";
}

if ($config['full_search']) {

	$find_where = "MATCH(p.title, p.short_story, p.full_story, p.xfields) AGAINST ('{story}' IN BOOLEAN MODE)";
	$full_s_addfield = ", " . $find_where . " as score";
	$full_s_addfield = str_replace("{story}", $query, $full_s_addfield);

} else {

	$find_where = "p.short_story LIKE '%{story}%' OR p.full_story LIKE '%{story}%' OR p.xfields LIKE '%{story}%' OR p.title LIKE '%{story}%'";

}

$find_where = str_replace("{story}", $query, $find_where);

$sql = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason{$user_select}{$full_s_addfield} FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id){$user_join} WHERE p.approve=1 AND e.disable_search=0{$this_date}{$where_category}";

if ($config['full_search']) {
	$sql .= " AND {$find_where} ORDER by score DESC LIMIT {$config['fastsearch_result']}";
} else {
	$sql .= " AND ({$find_where}) ORDER by date DESC LIMIT {$config['fastsearch_result']}";
}

$sql_result = $db->query($sql);
$found_result = $db->num_rows($sql_result);

if ( $found_result ) {

	$tpl = new dle_template();
	$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
	define('TEMPLATE_DIR', $tpl->dir);

	$tpl->load_template('fastsearchresult.tpl');

	$build_navigation = false;
	$short_news_cache = false;
	$use_banners = false;
	$_DOCUMENT_DATE = NULL;
	
	include(DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));

	if ($config['files_allow']) if (strpos($tpl->result['content'], "[attachment=") !== false) {
		$tpl->result['content'] = show_attach($tpl->result['content'], $attachments);
	}

	$tpl->result['content'] = str_ireplace('{THEME}', $_ROOT_DLE_URL . 'templates/' . $config['skin'], $tpl->result['content']);

	echo $tpl->result['content'].$link_query;
	die();

} else {

	$buffer = '';
	$db->query("SELECT id, name, descr FROM " . PREFIX . "_static WHERE disable_search=0 AND (descr LIKE '%" . $db->safesql($static_query) . "%' OR template LIKE '%".$db->safesql($static_query)."%') ORDER BY id DESC");

	while ($row = $db->get_row()) {

		if ($config['allow_alt_url']) $full_link = $_ROOT_DLE_URL . $row['name'] . ".html";
		else $full_link = "{$_ROOT_DLE_URL}index.php?do=static&amp;page=" . $row['name'];

		$buffer .= "<a href=\"" . $full_link . "\"><span class=\"searchheading\">" . stripslashes($row['descr']) . "</span></a>";
	}

	if ($buffer) {
		echo $buffer . $link_query;
		die();
	} else {
		echo $not_found . $link_query;
		die();
	}

}


?>