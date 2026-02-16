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
 File: go.php
-----------------------------------------------------
 Use: Forwarding links
=====================================================
*/

error_reporting ( E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );

function reset_url($url) {

	$value = (string)$url;
	$value = str_replace ( "http://", "", $url );
	$value = str_replace ( "https://", "", $value );
	$value = str_replace ( "www.", "", $value );
	$value = explode ( "/", $value );
	$value = reset ( $value );

	return $value;

}

$url = isset($_GET['url']) ? (string)$_GET['url'] : '';
$area = isset($_GET['area']) ? (string)$_GET['area'] : '';

$url = rawurldecode ( $url  );
$url = base64_decode ( $url );
$url = html_entity_decode($url, ENT_QUOTES, "ISO-8859-1");
$url = str_replace("\r", "", $url);
$url = str_replace("\n", "", $url);
$url = htmlspecialchars( strip_tags($url), ENT_QUOTES, "ISO-8859-1" );
$url = str_replace ( "&amp;", "&", $url );
$url = preg_replace( "/javascript:/i", "j&#1072;vascript:", $url );
$url = preg_replace( "/data:/i", "d&#1072;t&#1072;:", $url );

if( !preg_match( "#^(http|https)://#", $url ) ) {
	$url = 'https://' . $url;
}

if( stripos( $url, "go.php" ) !== false OR stripos( $url, "do=go" ) !== false) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../' );
	die ( "Access denied!!!" );
}

$_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? reset_url ( $_SERVER['HTTP_REFERER'] ) : '';
$_SERVER['HTTP_HOST'] = reset_url ( $_SERVER['HTTP_HOST'] );

if (($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) OR !$url) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../' );
	die ( "Access denied!!!" );
}

@header('X-XSS-Protection: 1; mode=block');

if( ( $area == 'comments' AND $config['comm_noreferrer'] ) OR (!$area AND $config['news_noreferrer'] ) ) {

	@header('Referrer-Policy: no-referrer');
}

header("HTTP/1.0 301 Moved Permanently");
header('Location: ' . $url );

die ( "Link Redirect" );
