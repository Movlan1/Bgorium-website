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
 File: poll.php
-----------------------------------------------------
 Use: polls
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$_IP = get_ip();

if( $is_logged ) $log_id = intval( $member_id['user_id'] );
else $log_id = $_IP;

$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll WHERE news_id = '{$row['id']}'" );

if($config['allow_cache'] AND $dle_module != "showfull") {

	$log = array('count' => 0 );

} else $log = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll_log WHERE news_id = '{$row['id']}' AND `member` ='{$log_id}'" );

$poll['title'] = stripslashes( $poll['title'] );
$poll['frage'] = stripslashes( $poll['frage'] );
$body = str_replace( "<br />", "<br>", $poll['body'] );
$body = explode( "<br>", stripslashes( $body ) );

$tplpoll = new dle_template();
$tplpoll->dir = TEMPLATE_DIR;

$tplpoll->load_template( 'poll.tpl' );

$tplpoll->set( '{title}', $poll['title'] );
$tplpoll->set( '{question}', $poll['frage'] );
$tplpoll->set( '{votes}', $poll['votes'] );
$tplpoll->set( '{news-id}', $row['id'] );

if( $log['count'] OR $poll['closed'] ) {
	
	$tplpoll->set_block( "'\\[not-voted\\](.+?)\\[/not-voted\\]'si", "" );
	$tplpoll->set( '[voted]', '' );
	$tplpoll->set( '[/voted]', '' );

} else {
	
	$tplpoll->set_block( "'\\[voted\\](.+?)\\[/voted\\]'si", "" );
	$tplpoll->set( '[not-voted]', '' );
	$tplpoll->set( '[/not-voted]', '' );
}

if ( $poll['closed'] ) {

	$tplpoll->set_block("'\\[not-closed\\](.+?)\\[/not-closed\\]'si", "");
	$tplpoll->set('[closed]', '');
	$tplpoll->set('[/closed]', '');

	$compare_date = compare_days_date($poll['date_closed']);

	if (!$compare_date) {

		$tplpoll->set('{close-date}', $lang['time_heute'] . langdate(", H:i", $poll['date_closed']));

	} elseif ($compare_date == 1) {

		$tplpoll->set('{close-date}', $lang['time_gestern'] . langdate(", H:i", $poll['date_closed']));

	} else {

		$tplpoll->set('{close-date}', langdate('j F Y H:i', $poll['date_closed']));

	}

} else {

	$tplpoll->set_block("'\\[closed\\](.+?)\\[/closed\\]'si", "");
	$tplpoll->set('[not-closed]', '');
	$tplpoll->set('[/not-closed]', '');
	$tplpoll->set('[/not-closed]', '{close-date}');

}

$list = "<div id=\"dle-poll-list-{$row['id']}\">";

if( !$log['count'] AND $user_group[$member_id['user_group']]['allow_poll'] AND !$poll['closed'] ) {
	if( ! $poll['multiple'] ) {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			
			$list .= <<<HTML
<div class="pollanswer"><label for="vote{$row['id']}{$v}" class="form-check-label"><input id="vote{$row['id']}{$v}" name="dle_poll_votes" type="radio" class="form-check-input" value="{$v}"><span>{$body[$v]}</span></label></div>
HTML;
		
		}
	} else {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			
			$list .= <<<HTML
<div class="pollanswer"><label for="vote{$row['id']}{$v}" class="form-check-label"><input id="vote{$row['id']}{$v}" name="dle_poll_votes[]" type="checkbox" class="form-check-input" value="{$v}"><span>{$body[$v]}</span></label></div>
HTML;
		
		}
	
	}
	
	$allcount = 0;

} else {
	
	$answer = get_votes( $poll['answer'] );
	$allcount = $poll['votes'];
	$pn = 0;
	
	for($v = 0; $v < sizeof( $body ); $v ++) {
		
		$num = isset($answer[$v]) ? $answer[$v] : 0;
		++ $pn;
		if( $pn > 5 ) $pn = 1;
		
		if( !$num ) $num = 0;
		
		if( $allcount != 0 ) $proc = (100 * $num) / $allcount;
		else $proc = 0;

		$intproc =intval($proc);		
		$proc = round( $proc, 2 );
		
		$list .= <<<HTML
{$body[$v]} - {$num} ({$proc}%)<br>
<div class="pollprogress"><span class="poll{$pn}" style="width:{$intproc}%;">{$proc}%</span></div>
HTML;
	
	}
	
	$allcount = 1;

}

$list .= "</div>";

$tplpoll->set( '{list}', $list );

if($config['allow_cache']) $allcount = 0;

$ajax_script = <<<HTML
<script>
<!--
if(typeof dle_poll_voted !== "undefined"){
    dle_poll_voted[{$row['id']}] = {$allcount};
}
else{
	var dle_poll_voted = new Array();
    dle_poll_voted[{$row['id']}] = {$allcount};
}
//-->
</script>
HTML;

$tplpoll->copy_template = $ajax_script . "<form method=\"post\" name=\"dlepollform_{$row['id']}\" id=\"dlepollform_{$row['id']}\" action=\"\">" . $tplpoll->copy_template . "<input type=\"hidden\" name=\"news_id\" id=\"news_id\" value=\"" . $row['id'] . "\"><input type=\"hidden\" name=\"status\" id=\"status\" value=\"0\"></form>";

$tplpoll->compile( 'poll' );
$tplpoll->clear();

$tpl->result['poll'] = $tplpoll->result['poll'];
unset($tplpoll);

?>