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
 File: comments.php
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$id = isset($_REQUEST['id']) ? intval( $_REQUEST['id'] ) : 0;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$subaction = isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '';
$_SESSION['referrer'] = isset($_SESSION['referrer']) ? str_replace("&amp;","&", $_SESSION['referrer'] ) : '';
$_POST['selected_comments'] = isset($_POST['selected_comments']) ? $_POST['selected_comments'] : array();

if(isset($_POST['mass_action']) AND $_POST['mass_action'] == "mass_combine" AND is_array($_POST['selected_comments']) AND count($_POST['selected_comments']) > 1) {

	if( $_POST['dle_allow_hash'] != "" AND $_POST['dle_allow_hash'] == $dle_login_hash AND $is_logged AND $user_group[$member_id['user_group']]['del_allc'] ) {

		$comments_array = array();
		$ids_array = array();

		foreach ( $_POST['selected_comments'] as $id ) {
			$comments_array[] = intval( $id );
		}

		$comments = implode("','", $comments_array);
		$sql_result = $db->query( "SELECT id, text FROM " . PREFIX . "_comments where id IN ('" . $comments . "') ORDER BY id ASC" );

		$comments = array();
		while ( $row = $db->get_row( $sql_result ) ) {
			$ids_array[] = $row['id'];
			$comments[] = stripslashes( $row['text'] );
		}
		$db->free( $sql_result );

		if (!$config['allow_comments_wysiwyg']) $c_implode = "<br><br>";
		else $c_implode = "";

		$comment = $db->safesql( implode($c_implode, $comments) );

		$db->query( "UPDATE " . PREFIX . "_comments SET text='{$comment}' WHERE id='{$ids_array[0]}'" );

		$parent = $ids_array[0];
		unset ($ids_array[0]);
		
		foreach ( $ids_array as $id ) {
			
			if ( $config['tree_comments'] ) {
				$db->query( "UPDATE " . PREFIX . "_comments SET parent='{$parent}' WHERE parent ='{$id}'" );
			}
			
			deletecomments( $id );

		}

		clear_cache( array('news_', 'full_', 'comm_', 'rss' ) );
			
		header( "Location: {$_SESSION['referrer']}" );
		die();	

	} else msgbox( $lang['comm_err_2'], $lang['comm_err_4'] );

} elseif(isset($_POST['mass_action']) AND $_POST['mass_action'] == "mass_delete" AND count($_POST['selected_comments']) ) {

	if( $_POST['dle_allow_hash'] != "" AND $_POST['dle_allow_hash'] == $dle_login_hash AND $is_logged AND $user_group[$member_id['user_group']]['del_allc'] ) {

		foreach ( $_POST['selected_comments'] as $id ) {
			
			$id = intval( $id );

			deletecomments( $id );

		}

		clear_cache( array('news_', 'full_', 'comm_', 'rss' ) );
	
		header( "Location: {$_SESSION['referrer']}" );
		die();	

	} else msgbox( $lang['comm_err_2'], $lang['comm_err_4'] );


} else msgbox( $lang['comm_err_2'], $lang['comm_err_5']."&nbsp;<a href=\"javascript:history.go(-1);\">{$lang['all_prev']}</a>" );

?>