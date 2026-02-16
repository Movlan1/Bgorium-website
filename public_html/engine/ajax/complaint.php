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
 File: complaint.php
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
	die ("error");
	
}

$parse = new ParseFilter();
$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
$parse->allowbbcodes = false;

$config['max_complaints'] = intval($config['max_complaints']) > 0 ? intval($config['max_complaints']) : 3;


$id = isset($_POST['id']) ? intval( $_POST['id'] ) : 0;
$text = isset($_POST['text']) ?  strip_tags($_POST['text']) : '';

if(dle_strlen( $text ) > 2000 ) {

	echo $lang['error_complaint_3']; die();

}

$text = $parse->BB_Parse( $parse->process( trim( $text ) ), false );
$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );

if ( $config['allow_complaint_mail'] ) {

	$mail = new dle_mail( $config );
	$lang['mail_complaint_1'] = str_replace( "{site}", $config['http_home_url'], $lang['mail_complaint_1'] );
}

$lang['error_complaint_6'] = str_replace('{group}', $user_group[$member_id['user_group']]['group_name'], $lang['error_complaint_6']);

if ($_POST['action'] == "pm") {

	if( !$is_logged ) die( "error" );

	if( !$id ) die( "error" ); 
	
	if( !$text ){ echo $lang['error_complaint_4']; die(); }

	$member_id['name'] = $db->safesql($member_id['name']);
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id != '0' AND `from`='{$member_id['name']}'" );

	if ($row['count'] >= $config['max_complaints'] ) { echo $lang['error_complaint_5']; die(); }
	
	$row = $db->super_query("SELECT m.id, m.content AS text, m.sender_id, u.name AS autor FROM " . USERPREFIX . "_conversations_messages m JOIN " . USERPREFIX . "_conversation_users cu ON m.conversation_id = cu.conversation_id LEFT JOIN " . USERPREFIX . "_users u ON m.sender_id = u.user_id WHERE m.id = '{$id}' AND cu.user_id ='{$member_id['user_id']}'");

	if( !isset($row['id']) OR !$row['id']) die("Operation not Allowed");

	if ($row['sender_id'] == $member_id['user_id']) { echo $lang['error_complaint_2']; die(); }

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE p_id='{$id}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$row['text'] = "<div class=\"quote\">".stripslashes( $row['text'] )."</div>";

	$text = $db->safesql( $row['text'].$text );
	$row['autor'] = $db->safesql($row['autor']);

	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`) values ('{$row['id']}', '0', '0', '{$text}', '{$member_id['name']}', '{$row['autor']}', '{$_TIME}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "comments") {

	if( !$is_logged ) {
		
		$author = $_IP;
		
		$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE `from`='{$author}'" );
		
		if ($db->num_rows() > 2) { echo $lang['error_complaint_1']; die(); }
		
	} else $author = $db->safesql($member_id['name']);

	if( !$id ) die( "error" );

	if(!$user_group[$member_id['user_group']]['allow_complaint_comments']) { echo $lang['error_complaint_6']; die(); }

	if( !$text ){ echo $lang['error_complaint_4']; die(); }
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE c_id != '0' AND `from`='{$author}'" );

	if ($row['count'] >= $config['max_complaints'] ) { echo $lang['error_complaint_5']; die(); }

	$row = $db->super_query( "SELECT id, autor FROM " . PREFIX . "_comments WHERE id='{$id}'" );

	if(!$row['id']) die("Operation not Allowed");

	if ($row['autor'] == $author) { echo $lang['error_complaint_2']; die(); }

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE c_id='{$id}' AND `from`='{$author}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$text = $db->safesql( $text );
	
	if( !$is_logged AND $_POST['mail'] ) {
		
		$sender_mail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['mail'] ) ) ) ) );
		
	} else $sender_mail = "";
	
	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`, `email`) values ('0', '{$row['id']}', '0', '{$text}', '{$author}', '', '{$_TIME}', '{$sender_mail}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "news") {

	if( !$is_logged ) {
		
		$author = $_IP;
		
		$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE `from`='{$author}'" );
		
		if ($db->num_rows() > 2) { echo $lang['error_complaint_1']; die(); }
		
	} else $author = $db->safesql($member_id['name']);

	if( !$id ) die( "error" );

	if(!$user_group[$member_id['user_group']]['allow_complaint_news']) { echo $lang['error_complaint_6']; die(); }

	if( !$text ){ echo $lang['error_complaint_4']; die(); }

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE n_id != '0' AND `from`='{$author}'" );

	if ($row['count'] >= $config['max_complaints'] ) { echo $lang['error_complaint_5']; die(); }


	$row = $db->super_query( "SELECT id, autor FROM " . PREFIX . "_post WHERE id='{$id}'" );

	if(!$row['id']) die("Operation not Allowed");

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE n_id='{$id}' AND `from`='{$author}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$text = $db->safesql( $text );

	if( !$is_logged AND $_POST['mail'] ) {
		
		$sender_mail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['mail'] ) ) ) ) );
		
	} else $sender_mail = "";
	
	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`, `email`) values ('0', '0', '{$row['id']}', '{$text}', '{$author}', '', '{$_TIME}', '{$sender_mail}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "orfo") {

	if( !$text ){ echo $lang['error_complaint_4']; die(); }

	if(!$user_group[$member_id['user_group']]['allow_complaint_orfo']) { echo $lang['error_complaint_6']; die(); }

	$seltext = $_POST['seltext'];

	$seltext = html_entity_decode($seltext, ENT_QUOTES | ENT_XML1, 'UTF-8');

	if(dle_strlen( $seltext ) > 256 ) {
	
		$seltext = dle_substr( $seltext, 0, 256 );

	}

	$seltext = htmlspecialchars( trim( $seltext ), ENT_QUOTES, 'UTF-8' );
	$url = $db->safesql( htmlspecialchars( $parse->clear_url( trim( $_POST['url'] ) ), ENT_QUOTES, 'UTF-8' ) );

	if(!$seltext) die( "error" );

	if( !$is_logged ) $author = $_IP; else $author = $db->safesql($member_id['name']);
	
	if( !$is_logged AND $_POST['mail'] ) {
		
		$sender_mail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['mail'] ) ) ) ) );
		
	} else $sender_mail = "";

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id='0' AND c_id='0' AND n_id='0' AND `from`='{$author}'" );

	if ($row['count'] >= $config['max_complaints'] ) { echo $lang['error_complaint_5']; die(); }

	$seltext = "<div class=\"quote\">".stripslashes( $seltext )."</div>";
	$text = $db->safesql( $seltext.$text );
	
	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`, `email`) values ('0', '0', '0', '{$text}', '{$author}', '{$url}', '{$_TIME}', '{$sender_mail}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

}

echo "ok";

?>