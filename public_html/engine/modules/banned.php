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
 File: banned.php
-----------------------------------------------------
 Use: Banned users
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$this_time = time();
$del = false;
$blocked = false;
$banned_from = '';

if (isset($block_country) AND $block_country) {
	
	$blocked = true;

	if( trim($config['country_decline_reason']) ) {
		$descr = trim(html_entity_decode($config['country_decline_reason'], ENT_QUOTES | ENT_HTML5, 'utf-8') );
	} else $descr = $lang['country_declined'];

	$endban = $lang['banned_info'];
	
	if($config['block_vpn']) {

		if (isset($_COOKIE['dle_possible_vpn'])) {
			$dle_possible_vpn = json_decode($_COOKIE['dle_possible_vpn'], true);

			if (!is_array($dle_possible_vpn)) $dle_possible_vpn = array();

			$dle_possible_vpn['site'] = 1;

		} else { $dle_possible_vpn = array(); $dle_possible_vpn['site'] = 1; }

		set_cookie("dle_possible_vpn", json_encode($dle_possible_vpn, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) , 1);
	}

} else {

	$sel_banned = $db->query("SELECT users_id FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '{$this_time}'");

	while ($row = $db->get_row($sel_banned)) {
		$del = true;

		if ($row['users_id']) $db->query("UPDATE " . USERPREFIX . "_users SET banned='' WHERE user_id = '{$row['users_id']}'");
	}

	$db->free($sel_banned);

	if ($del) {

		$db->query("DELETE FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '{$this_time}'");
		@unlink(ENGINE_DIR . '/cache/system/banned.json');
	}

	if (isset($blockip) and $blockip) {

		$blocked = true;

		if ($banned_info['ip'][$blockip]['date']) {

			if ($banned_info['ip'][$blockip]['date'] > $this_time) $endban = langdate("j F Y H:i", $banned_info['ip'][$blockip]['date'], true);
			else $blocked = false;
		} else $endban = $lang['banned_info'];

		$descr = $lang['ip_block'] . "<br><br>" . $banned_info['ip'][$blockip]['descr'];
		$banned_from = isset($banned_info['ip'][$blockip]['banned_from']) ? $banned_info['ip'][$blockip]['banned_from'] : '';

	} elseif (isset($banned_info['users_id'][$member_id['user_id']]['users_id']) AND $banned_info['users_id'][$member_id['user_id']]['users_id']) {

		$blocked = true;

		if ($banned_info['users_id'][$member_id['user_id']]['date']) {

			if ($banned_info['users_id'][$member_id['user_id']]['date'] > $this_time) $endban = langdate("j F Y H:i", $banned_info['users_id'][$member_id['user_id']]['date'], true);
			else $blocked = false;
		} else $endban = $lang['banned_info'];

		$descr = $banned_info['users_id'][$member_id['user_id']]['descr'];
		$banned_from = isset($banned_info['users_id'][$member_id['user_id']]['banned_from']) ? $banned_info['users_id'][$member_id['user_id']]['banned_from'] : '';
	}

}

if( $blocked ) {
	
	$tpl->dir = ROOT_DIR . '/templates';
	
	$tpl->load_template( 'banned.tpl' );
	$tpl->set( '{description}', $descr );
	$tpl->set( '{end}', $endban );
	
	if($banned_from) {

		$tpl->set('[banned-from]', "");
		$tpl->set('[/banned-from]', "");
		$tpl->set('{banned-from}', $banned_from);

	} else {

		$tpl->set_block("'\\[banned-from\\](.*?)\\[/banned-from\\]'si", "");
		$tpl->set('{banned-from}', '');

	}

	$tpl->compile( 'content' );

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');	
	header("Content-type: text/html; charset=utf-8");
	echo $tpl->result['content'];
	die();

}
?>