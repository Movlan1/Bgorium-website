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
 File: fullnews.php
-----------------------------------------------------
 Use: WYSIWYG for adminpanel
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if ($mod != "editnews") {
$row['id'] = "";
$row['autor'] = $member_id['name'];
}

if (!isset ($row['full_story'])) $row['full_story'] = "";

echo <<<HTML
<div class="editor-panel"><textarea id="full_story" name="full_story" class="wysiwygeditor" style="width:98%;height:400px;">{$row['full_story']}</textarea></div>
HTML;

?>