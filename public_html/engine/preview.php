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
 File: preview.php
-----------------------------------------------------
 Use: Preview news on the website
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../' );
	die( "Hacking attempt!" );
}

if ( !count($_POST) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: /' );
	die("Hacking attempt!");
}

@header( "Cache-Control: no-cache, must-revalidate, max-age=0" );
@header( "Expires: 0" );
@header( "Content-type: text/html; charset=utf-8" );

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	echo $lang['sess_error'];
	die();
}

if( !isset( $_REQUEST['catlist'] ) ) $_REQUEST['catlist'] = array ();

if( is_array( $_REQUEST['catlist'] ) ) $catlist = $_REQUEST['catlist'];
else {$catlist = array (); $_REQUEST['catlist'] = array (); }

$config['category_separator'] = htmlspecialchars_decode( $config['category_separator'], ENT_QUOTES);
$config['tags_separator'] = htmlspecialchars_decode( $config['tags_separator'], ENT_QUOTES);

if( !count( $catlist ) ) {
	
	$my_cat = "---";
	$my_cat_link = "---";
	
} else {
	
	$my_cat = array ();
	$my_cat_link = array ();

	if( $cat_info[$catlist[0]]['skin'] ) {
		
		$cat_info[$catlist[0]]['skin'] = trim( totranslit( $cat_info[$catlist[0]]['skin'] , false, false) );
		
		if( @is_dir ( ROOT_DIR . '/templates/' . $cat_info[$catlist[0]]['skin'] ) ) {
			
			$tpl->dir = ROOT_DIR . '/templates/' . $cat_info[$catlist[0]]['skin'];
			
		}
	}
	
	foreach ( $catlist as $element ) {
		if( $element ) {
			$element = intval($element);
			$my_cat[] = $cat_info[$element]['name'];
			$my_cat_link[] = "<a href=\"#\">{$cat_info[$element]['name']}</a>";
		}
	}
	
	$my_cat = stripslashes( implode( $config['category_separator'], $my_cat ) );
	$my_cat_link = stripslashes( implode( $config['category_separator'], $my_cat_link ) );
}

$css = file_get_contents( $tpl->dir."/".'preview.css' );

$config['jquery_version'] = intval($config['jquery_version']);
	
$ver = $config['jquery_version'] ? $config['jquery_version'] : "";

echo <<<HTML
<!DOCTYPE html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
<meta charset="utf-8">
<link href="engine/classes/html5player/plyr.css" type="text/css" rel="stylesheet">
<script src="engine/classes/js/jquery{$ver}.js"></script>
<script src="engine/classes/js/jqueryui{$ver}.js"></script>
<script src="engine/classes/js/dle_js.js"></script>
<script src="engine/classes/html5player/hls.js"></script>
<script src="engine/classes/html5player/plyr.js"></script>
<script src="engine/classes/fancybox/fancybox.js"></script>
<script src="engine/classes/highlight/highlight.code.js"></script>
<style type="text/css">
{$css}
fieldset {
    border: none;
	padding: 0;
    padding-top: 10px;
    margin-bottom: 10px;
}
</style>
</head> 
<body style="padding:20px;">
<script>
	var dle_root = '';
</script>
HTML;

$parse = new ParseFilter();

$title = stripslashes($parse->process(trim(strip_tags($_POST['title']))));

$parse->allow_code = false;
$full_story = $parse->process( $_POST['full_story'] );
$short_story = $parse->process( $_POST['short_story'] );
	
$full_story = $parse->BB_Parse( $full_story );
$short_story = $parse->BB_Parse( $short_story );

$dle_module = "main";

if ( @is_file($tpl->dir."/preview.tpl") ) $tpl->load_template('preview.tpl');
else $tpl->load_template('shortstory.tpl');
 
if ( $parse->not_allowed_text ) $tpl->copy_template = $lang['news_err_39'];

$tpl->copy_template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->copy_template);
$tpl->template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->template);
	
$tpl->set('[short-preview]', "");
$tpl->set('[/short-preview]', "");
$tpl->set_block("'\\[full-preview\\](.*?)\\[/full-preview\\]'si","");
$tpl->set_block("'\\[static-preview\\](.*?)\\[/static-preview\\]'si","");

$tpl->set( '{title}', str_replace("&amp;amp;", "&amp;",  htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) ) );

if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
	$count= intval($matches[1]);
	$title = strip_tags( $title );

	if( $count AND dle_strlen( $title ) > $count ) {
					
		$title = dle_substr( $title, 0, $count );
					
		if( ($temp_dmax = dle_strrpos( $title, ' ' )) ) $title = dle_substr( $title, 0, $temp_dmax );
					
	}

	$tpl->set( $matches[0], str_replace("&amp;amp;", "&amp;",  htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) ) );

		
}

if( !count( $_REQUEST['catlist'] ) ) {
	$_REQUEST['catlist'] = array ();
	$_REQUEST['catlist'][] = '0';
}

$c_list = array();

foreach ( $_REQUEST['catlist'] as $value ) {
	$c_list[] = intval($value);
}

$category_id = implode (',', $c_list);

if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
}
	
if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
}

$tpl->set( '{views}', 0 );
$date = time ();
$tpl->set( '{date}', langdate( $config['timestamp_active'], $date ) );
$news_date = $date;
$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
$tpl->copy_template = preg_replace_callback("#\{edit-date=(.+?)\}#i", "formdate", $tpl->copy_template);
$tpl->set( '[link]', "<a href=#>" );
$tpl->set( '[/link]', "</a>" );
$tpl->set( '{comments-num}', 0 );
$tpl->set( '[full-link]', "<a href=#>" );
$tpl->set( '[/full-link]', "</a>" );
$tpl->set( '[com-link]', "<a href=#>" );
$tpl->set( '[/com-link]', "</a>" );
$tpl->set( '[day-news]', "<a href=#>");
$tpl->set( '[/day-news]', "</a>");
$tpl->set( '{rating}', "" );
$tpl->set( '{ratingscore}', 0 );
$tpl->set( '[rating]', "" );
$tpl->set( '[/rating]', "" );
$tpl->set( '{author}', "--" );
$tpl->set( '{approve}', "" );
$tpl->set( '{category}', $my_cat );
$tpl->set( '{favorites}', '' );
$tpl->set( '{link-category}', $my_cat_link );
$tpl->set( '{edit-date}', "" );
$tpl->set( '{editor}', "" );
$tpl->set( '{edit-reason}', "" );
$tpl->set( '{category-url}', "#" );
$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
$tpl->set( '[not-comments]', "" );
$tpl->set( '[/not-comments]', "" );
$tpl->set_block( "'\\[comments\\](.*?)\\[/comments\\]'si", "" );

if( isset($cat_info[$c_list[0]]['icon']) AND $cat_info[$c_list[0]]['icon'] != '' ) {
	$tpl->set( '{category-icon}', $cat_info[$c_list[0]]['icon'] );
	$tpl->set( '[category-icon]', "" );
	$tpl->set( '[/category-icon]', "" );
	$tpl->set_block( "'\\[not-category-icon\\](.*?)\\[/not-category-icon\\]'si", "" );
} else {
	$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
	$tpl->set( '[not-category-icon]', "" );
	$tpl->set( '[/not-category-icon]', "" );
	$tpl->set_block( "'\\[category-icon\\](.*?)\\[/category-icon\\]'si", "" );
}

$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
$tpl->set( '{tags}', "" );

if ( isset($_POST['news_fixed']) AND $_POST['news_fixed'] ) {

	$tpl->set( '[fixed]', "" );
	$tpl->set( '[/fixed]', "" );
	$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

} else {

	$tpl->set( '[not-fixed]', "" );
	$tpl->set( '[/not-fixed]', "" );
	$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
}

$tpl->set( '[mail]', "" );
$tpl->set( '[/mail]', "" );
$tpl->set( '{news-id}', "ID Unknown" );

$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#is", "\\2", $tpl->copy_template );

$tpl->set_block( "'\\[edit\\].*?\\[/edit\\]'si", "" );
$tpl->set_block( "'{banner_(.*?)}'si", "" );

$xfieldsaction = "templatereplacepreview";
$xfieldsinput = $tpl->copy_template;
include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
$tpl->copy_template = $xfieldsoutput;

$tpl->set( '{short-story}', stripslashes( $short_story ) );
$tpl->set( '{full-story}', stripslashes( $full_story ) );

$tpl->copy_template = "<fieldset><legend><span>{$lang['preview_short']}</span> </legend>" . $tpl->copy_template . "</fieldset>";
$tpl->compile( 'shortstory' );
$tpl->result['shortstory'] = preg_replace ( "#\[hide(.*?)\]#i", "", $tpl->result['shortstory'] );
$tpl->result['shortstory'] = str_ireplace( "[/hide]", "", $tpl->result['shortstory']);
$tpl->result['shortstory'] = str_replace ( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['shortstory'] );

echo $tpl->result['shortstory'];

$dle_module = "showfull";

if ( @is_file($tpl->dir."/preview.tpl") ) $tpl->load_template('preview.tpl');
else $tpl->load_template('fullstory.tpl');

if ( $parse->not_allowed_text ) $tpl->copy_template = $lang['news_err_39'];

$tpl->copy_template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->copy_template);
$tpl->template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->template);
	
$tpl->copy_template = str_replace('[full-preview]', "", $tpl->copy_template);
$tpl->copy_template = str_replace('[/full-preview]', "", $tpl->copy_template);
$tpl->copy_template = preg_replace("'\\[short-preview\\](.*?)\\[/short-preview\\]'si","", $tpl->copy_template);
$tpl->copy_template = preg_replace("'\\[static-preview\\](.*?)\\[/static-preview\\]'si","", $tpl->copy_template);

if( strlen( $full_story ) < 10 AND strpos( $tpl->copy_template, "{short-story}" ) === false ) {
	$full_story = $short_story;
}

$tpl->set( '{title}', str_replace("&amp;amp;", "&amp;",  htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) ) );

if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
	$count= intval($matches[1]);
	$title = strip_tags( $title );

	if( $count AND dle_strlen( $title ) > $count ) {
					
		$title = dle_substr( $title, 0, $count );
					
		if( ($temp_dmax = dle_strrpos( $title, ' ' )) ) $title = dle_substr( $title, 0, $temp_dmax );
				
	}

	$tpl->set( $matches[0], str_replace("&amp;amp;", "&amp;",  htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) ) );

	
}

if( !count( $_REQUEST['catlist'] ) ) {
	$_REQUEST['catlist'] = array ();
	$_REQUEST['catlist'][] = '0';
}

$c_list = array();

foreach ( $_REQUEST['catlist'] as $value ) {
	$c_list[] = intval($value);
}

$category_id = implode (',', $c_list);

if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
}
	
if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
}

$tpl->set( '{views}', 0 );
$tpl->set( '{poll}', '' );
$tpl->set( '{date}', langdate( $config['timestamp_active'], $date ) );
$news_date = $date;
$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
$tpl->copy_template = preg_replace_callback("#\{edit-date=(.+?)\}#i", "formdate", $tpl->copy_template);

$tpl->set( '[link]', "<a href=#>" );
$tpl->set( '[/link]', "</a>" );
$tpl->set( '{comments-num}', 0 );
$tpl->set( '[full-link]', "<a href=#>" );
$tpl->set( '[/full-link]', "</a>" );
$tpl->set( '[com-link]', "<a href=#>" );
$tpl->set( '[/com-link]', "</a>" );
$tpl->set( '[day-news]', "<a href=#>");
$tpl->set( '[/day-news]', "</a>");
$tpl->set( '{rating}', "" );
$tpl->set( '{ratingscore}', 0 );
$tpl->set( '[rating]', "" );
$tpl->set( '[/rating]', "" );
$tpl->set( '{author}', "--" );
$tpl->set( '{category}', $my_cat );
$tpl->set( '{link-category}', $my_cat_link );
$tpl->set( '{related-news}', "" );
$tpl->set('{vote-num}', "0");
$tpl->set( '{category-url}', "#" );

if( isset($cat_info[$c_list[0]]['icon']) AND $cat_info[$c_list[0]]['icon'] ) {
	
	$tpl->set( '{category-icon}', $cat_info[$c_list[0]]['icon'] );
	$tpl->set( '[category-icon]', "" );
	$tpl->set( '[/category-icon]', "" );
	$tpl->set_block( "'\\[not-category-icon\\](.*?)\\[/not-category-icon\\]'si", "" );
	
} else {
	$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
	$tpl->set( '[not-category-icon]', "" );
	$tpl->set( '[/not-category-icon]', "" );
	$tpl->set_block( "'\\[category-icon\\](.*?)\\[/category-icon\\]'si", "" );
	
}
$tpl->set( '{edit-date}', "" );
$tpl->set( '{editor}', "" );
$tpl->set( '{edit-reason}', "" );
$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
$tpl->set( '{pages}', '' );
$tpl->set( '{favorites}', '' );
$tpl->set( '[mail]', "" );
$tpl->set( '[/mail]', "" );
$tpl->set( '{news-id}', "ID Unknown" );
$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
$tpl->set( '{tags}', "" );
$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
$tpl->set_block( "'\\[pages\\](.*?)\\[/pages\\]'si", "" );
$tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );
$tpl->set('{addcomments}', "");
$tpl->set('{comments}', "");
$tpl->set('{navigation}', "");
$tpl->set( '[not-comments]', "" );
$tpl->set( '[/not-comments]', "" );
$tpl->set_block( "'\\[comments\\](.*?)\\[/comments\\]'si", "" );

if ( isset($_POST['news_fixed']) AND $_POST['news_fixed'] ) {

	$tpl->set( '[fixed]', "" );
	$tpl->set( '[/fixed]', "" );
	$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

} else {

	$tpl->set( '[not-fixed]', "" );
	$tpl->set( '[/not-fixed]', "" );
	$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
}

$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#is", "\\2", $tpl->copy_template );
$tpl->set_block( "'\\[edit\\].*?\\[/edit\\]'si", "" );

$tpl->set( '[print-link]', "<a href=#>" );
$tpl->set( '[/print-link]', "</a>" );
$tpl->set_block( "'{banner_(.*?)}'si", "" );

$xfieldsaction = "templatereplacepreview";
$xfieldsinput = $tpl->copy_template;
include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
$tpl->copy_template = $xfieldsoutput;

$tpl->set( '{short-story}', stripslashes( $short_story ) );
$tpl->set( '{full-story}', stripslashes( $full_story ) );

$tpl->copy_template = "<fieldset><legend><span>{$lang['preview_full']}</span> </legend>" . $tpl->copy_template . "</fieldset>";
$tpl->compile( 'fullstory' );
$tpl->result['fullstory'] = preg_replace ( "#\[hide(.*?)\]#i", "", $tpl->result['fullstory'] );
$tpl->result['fullstory'] = str_ireplace( "[/hide]", "", $tpl->result['fullstory']);
$tpl->result['fullstory'] = str_replace ( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['fullstory'] );

echo $tpl->result['fullstory'];

echo <<<HTML
</body></html>
HTML;

die();
