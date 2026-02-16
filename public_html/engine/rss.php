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
 File: rss.php
-----------------------------------------------------
 Use: the news feeds
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../' );
	die( "Hacking attempt!" );
}

if($dle_module != "main" AND $dle_module != "allnews" AND $dle_module != "catalog" AND $dle_module != "cat") {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: /' );
	die("Hacking attempt!");
}

include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));

if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "https:".$config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates';
define( 'TEMPLATE_DIR', $tpl->dir );

$member_id['user_group'] = 5;

if (isset($_GET['category']) AND $_GET['category']) {
	$category_id = get_ID($_GET['category']);
} else $category_id = false;

$view_template = "rss";
$rssmode = isset($_REQUEST['rssmode']) ? $_REQUEST['rssmode'] : '';


$config['allow_cache'] = true;
$config['allow_banner'] = false;
$config['rss_number'] = intval( $config['rss_number'] );
$cstart = 0;

$config['rss_params'] = trim(html_entity_decode($config['rss_params'], ENT_QUOTES, 'utf-8'));
$config['rss_turboparams'] = trim(html_entity_decode($config['rss_turboparams'], ENT_QUOTES, 'utf-8'));
$config['rss_dzenparams'] = trim(html_entity_decode($config['rss_dzenparams'], ENT_QUOTES, 'utf-8'));

if ( $user ) $config['allow_cache'] = false;

if( isset($_GET['subaction']) AND $_GET['subaction'] == 'allnews' ) $config['home_title'] = $lang['show_user_news'] . ' ' . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . " - " . $config['home_title'];
elseif( isset($_GET['do']) AND $_GET['do'] == 'cat' ) $config['home_title'] = stripslashes( $cat_info[$category_id]['name'] ) . " - " . $config['home_title'];

$self_url = htmlspecialchars($_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, "utf-8");

$rss_content = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" {$config['rss_params']}>
<channel>
<title>{$config['home_title']}</title>
<link>{$config['http_home_url']}</link>
<atom:link href="$self_url" rel="self" type="application/rss+xml" />
<language>{$lang['language_code']}</language>
<description>{$config['home_title']}</description>
XML;

if( $rssmode == 'dzen') {

	$rss_content = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" {$config['rss_dzenparams']}>
<channel>
<title>{$config['home_title']}</title>
<link>{$config['http_home_url']}</link>
<language>{$lang['language_code']}</language>
XML;

}


if( !file_exists( $tpl->dir . "/rss.tpl" ) ) {

	$tpl->template = <<<HTML
[rss]<item>
<title>{title}</title>
<guid isPermaLink="true">{rsslink}</guid>
<link>{rsslink}</link>
<dc:creator>{rssauthor}</dc:creator>
<pubDate>{rssdate}</pubDate>
<category>{category}</category>
<description><![CDATA[{short-story}]]></description>
</item>[/rss]

[turbo]<item turbo="true">
<turbo:extendedHtml>true</turbo:extendedHtml>
<link>{rsslink}</link>
<author>{rssauthor}</author>
<category>{category}</category>
<pubDate>{rssdate}</pubDate>
<turbo:content><![CDATA[{full-story}]]></turbo:content>
</item>[/turbo]

[dzen]<item>
<title>{title}</title>
<link>{rsslink}</link>
<pdalink>{rsslink}</pdalink>
<guid>{news-id}</guid>
<pubDate>{rssdate}</pubDate>
<category>native-yes</category>
{images}
<content:encoded><![CDATA[{full-story}]]></content:encoded>
</item>[/dzen]
HTML;

	$tpl->copy_template = $tpl->template;

} else {
	
	$tpl->load_template( 'rss.tpl' );
	
}


if( $config['site_offline'] OR !$config['allow_rss'] OR ($rssmode == 'dzen' AND !$config['allow_yandex_dzen']) ) {
	
	$rss_content .= <<<XML
<item>
<title>RSS in offline mode</title>
<guid isPermaLink="true"></guid>
<link></link>
<description>RSS in offline mode</description>
<category>undefined</category>
<author>DataLife Engine</author>
<pubDate>DataLife Engine</pubDate>
</item>
XML;

} else {
	
	$tpl->template = preg_replace("'\\[turbo\\](.*?)\\[/turbo\\]'si", "", $tpl->template);

	if( $rssmode == 'dzen' ) {
		
		$tpl->template = str_replace( '[dzen]', '', $tpl->template );
		$tpl->template = str_replace('[/dzen]', '', $tpl->template );
		$tpl->template = preg_replace("'\\[rss\\](.*?)\\[/rss\\]'si", "", $tpl->template );
		$tpl->template = trim($tpl->template);
		
	} else {
		
		$tpl->template = str_replace('[rss]', '', $tpl->template );
		$tpl->template = str_replace('[/rss]', '', $tpl->template );
		$tpl->template = preg_replace("'\\[dzen\\](.*?)\\[/dzen\\]'si", "", $tpl->template );
		$tpl->template = trim($tpl->template);	
	}
	
	$tpl->copy_template = $tpl->template;
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/engine.php'));
	
	$rss_content .= $tpl->result['content'];
}

$rss_content .= '</channel></rss>';

$rss_content = str_ireplace( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $rss_content );

header( "Content-type: application/xml; charset=utf-8" );
echo $rss_content;

die();
