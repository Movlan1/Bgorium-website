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
 File: fullsite.php
-----------------------------------------------------
 Use: WYSIWYG for website news
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if (!isset ($row['full_story'])) $row['full_story'] = "";

$fullarea = <<<HTML
    <div class="wseditor{$dark_theme}"><textarea id="full_story" name="full_story" class="wysiwygeditor" style="width:98%;height:300px;">{$row['full_story']}</textarea></div>
HTML;

?>