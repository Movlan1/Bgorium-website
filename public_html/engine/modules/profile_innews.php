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
 File: profile_innews.php
-----------------------------------------------------
 Use: profile data in news
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if(!$row['user_group']) $row['user_group'] = 5;

if ($row['foto'] AND count(explode("@", $row['foto'])) == 2 ) {

	$tpl->set( '{foto}', 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']) );

} else {

	if( $row['foto'] ) {
		
		if (strpos($row['foto'], "//") === 0) $avatar = "https:".$row['foto']; else $avatar = $row['foto'];

		$avatar = @parse_url ( $avatar );

		if( $avatar['host'] ) {
			
			$tpl->set( '{foto}', $row['foto']);
			
		} else $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto']) ;
		
	} else $tpl->set( '{foto}', "{THEME}/dleimages/noavatar.png" );

}

if( $row['fullname'] ) {
	
	$tpl->set( '[fullname]', "");
	$tpl->set( '[/fullname]', "");
	$tpl->set( '{fullname}', stripslashes( $row['fullname'] ) );
	$tpl->set_block("'\\[not-fullname\\](.*?)\\[/not-fullname\\]'si", "");

} else {
	
	$tpl->set_block("'\\[fullname\\](.*?)\\[/fullname\\]'si", "");
	$tpl->set( '{fullname}', "");
	$tpl->set( '[not-fullname]', "");
	$tpl->set( '[/not-fullname]', "");

}

if( $row['land'] ) {
	
	$tpl->set( '[land]',  "");
	$tpl->set( '[/land]',  "");
	$tpl->set( '{land}',  stripslashes( $row['land'] ) );
	$tpl->set_block("'\\[not-land\\](.*?)\\[/not-land\\]'si", "");

} else {
	
	$tpl->set_block("'\\[land\\](.*?)\\[/land\\]'si", "");
	$tpl->set( '{land}',  "");
	$tpl->set( '[not-land]',  "");
	$tpl->set( '[/not-land]',  "");

}

if ( ($row['lastdate'] + 1200) > $_TIME AND !$row['banned'] ) {

	$tpl->set( '[online]', "" );
	$tpl->set( '[/online]', "" );
	$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );

} else {
	$tpl->set( '[offline]', "" );
	$tpl->set( '[/offline]', "" );
	$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
}

$tpl->set( '{mail}',  stripslashes( (string)$row['email'] ) );
$tpl->set( '{group}',  $user_group[$row['user_group']]['group_prefix'].$user_group[$row['user_group']]['group_name'].$user_group[$row['user_group']]['group_suffix']);

if ($row['lastdate']) {

	$tpl->set('{lastdate}', difflangdate("j F Y, H:i", $row['lastdate']));

	$news_date = $row['lastdate'];
	$tpl->copy_template = preg_replace_callback("#\{lastdate=(.+?)\}#i", "formdate", $tpl->copy_template);
	
} else {

	$tpl->set('{lastdate}', '--');
}

if ($row['reg_date']) {

	$tpl->set('{registration}', difflangdate("j F Y, H:i", $row['reg_date']));

	$news_date = $row['reg_date'];
	$tpl->copy_template = preg_replace_callback("#\{registration=(.+?)\}#i", "formdate", $tpl->copy_template);

} else $tpl->set('{registration}', '--');


if( $user_group[$row['user_group']]['icon'] ) $tpl->set( '{group-icon}', "<img src=\"" . $user_group[$row['user_group']]['icon'] . "\" alt=\"\">");
else $tpl->set( '{group-icon}',  "");

if( $user_group[$row['user_group']]['time_limit'] ) {
	
	$tpl->set_block("'\\[time_limit\\](.*?)\\[/time_limit\\]'si", "\\1");
	
	if( $row['time_limit'] ) {
		
		$tpl->set( '{time_limit}', langdate( "j F Y H:i", $row['time_limit'] ) );
	
	} else {
		
		$tpl->set( '{time_limit}', $lang['no_limit'] );
	
	}

} else {
	
	$tpl->set_block("'\\[time_limit\\](.*?)\\[/time_limit\\]'si", "");
	$tpl->set( '{time_limit}', "");

}

if( $row['user_comm_num'] ) {
	$tpl->set( '[comm-num]', "" );
	$tpl->set( '[/comm-num]', "" );
	$tpl->set( '{comm-num}', number_format($row['user_comm_num'], 0, ',', ' ') );
	$tpl->set_block("'\\[not-comm-num\\](.*?)\\[/not-comm-num\\]'si", "");

} else {
	$tpl->set( '{comm-num}', 0 );
	$tpl->set( '[not-comm-num]', "");
	$tpl->set( '[/not-comm-num]', "");
	$tpl->set_block("'\\[comm-num\\](.*?)\\[/comm-num\\]'si", "");
}

$tpl->set( '{comments-url}', "{$PHP_SELF}?do=lastcomments&amp;userid=" . $row['user_id'] );

if( $row['news_num'] ) {

	$tpl->set( '{news-num}', number_format($row['news_num'], 0, ',', ' ') );
	$tpl->set( '[news-num]', "");
	$tpl->set( '[/news-num]', "");
	$tpl->set_block("'\\[not-news-num\\](.*?)\\[/not-news-num\\]'si", "");

} else {
	
	$tpl->set( '{news-num}', 0);
	$tpl->set( '[not-news-num]', "");
	$tpl->set( '[/not-news-num]', "");
	$tpl->set_block("'\\[news-num\\](.*?)\\[/news-num\\]'si", "");

}

if($row['name']) {

	if ($config['allow_alt_url']) {
		$tpl->set('{profile-link}', $config['http_home_url'] . "user/" . urlencode($row['name']) . "/");
		$tpl->set('{news}', $config['http_home_url'] . "user/" . urlencode($row['name']) . "/news/");
		$tpl->set('{rss}', $config['http_home_url'] . "user/" . urlencode($row['name']) . "/rss.xml");
	} else {
		$tpl->set('{profile-link}', $PHP_SELF . "?subaction=userinfo&user=" . urlencode($row['name']));
		$tpl->set('{news}', $PHP_SELF . "?subaction=allnews&amp;user=" . urlencode($row['name']));
		$tpl->set('{rss}', $PHP_SELF . "?mod=rss&amp;subaction=allnews&amp;user=" . urlencode($row['name']));
	}

} else {

	$tpl->set('{news}', '');
	$tpl->set('{rss}', '');
	$tpl->set('{profile-link}', '');
	
}

if ( $row['user_xfields'] ) {

	$userxfields = xfieldsload( true );
	$userxfieldsdata = xfieldsdataload( $row['user_xfields'] );

	foreach ( $userxfields as $value ) {
		$preg_safe_name = preg_quote( $value[0], "'" );

		if( !isset($userxfieldsdata[$value[0]]) OR empty( $userxfieldsdata[$value[0]] ) ) {

			$tpl->set_block("'\\[profile_xfgiven_{$preg_safe_name}\\](.*?)\\[/profile_xfgiven_{$preg_safe_name}\\]'is", "");
			$tpl->set("[profile_xfnotgiven_{$value[0]}]", "");
			$tpl->set("[/profile_xfnotgiven_{$value[0]}]", "");

		} else {
			
			$tpl->set_block("'\\[profile_xfnotgiven_{$preg_safe_name}\\](.*?)\\[/profile_xfnotgiven_{$preg_safe_name}\\]'is", "");
			$tpl->set("[profile_xfgiven_{$value[0]}]", "");
			$tpl->set("[/profile_xfgiven_{$value[0]}]", "");
		}

		if ( !isset( $userxfieldsdata[$value[0]] ) ) $userxfieldsdata[$value[0]] = '';

		$tpl->set("[profile_xfvalue_{$value[0]}]", stripslashes( $userxfieldsdata[$value[0]] ) );

	}

} else {
	
	$tpl->set_block("'\\[profile_xfgiven_(.*?)\\](.*?)\\[/profile_xfgiven_(.*?)\\]'is", "");
	$tpl->set_block("'\\[profile_xfvalue_(.*?)\\]'i", "");
	$tpl->set_block("'\\[profile_xfnotgiven_(.*?)\\]'is", "");
	$tpl->set_block("'\\[/profile_xfnotgiven_(.*?)\\]'is", "");

}

$tpl->set( '{all-pm}', $row['pm_all'] );

if ($row['favorites']) {
	$tpl->set( '{favorite-count}', count(explode("," ,$row['favorites'])) );
} else $tpl->set( '{favorite-count}', 0);


if ( $user_group[$row['user_group']]['allow_pm'] ) {
	
	$tpl->set( '[pm]', "<a onclick=\"DLESendPM('" . urlencode($row['name']) . "'); return false;\" href=\"$PHP_SELF?do=pm&amp;doaction=newpm&amp;username=" . urlencode($row['name']) . "\">" );
	$tpl->set( '[/pm]', "</a>" );

} else {
	
	$tpl->set_block("'\\[pm\\](.*?)\\[/pm\\]'si", "");

}

if (stripos ( $tpl->copy_template, "[author-group=" ) !== false) {

	$tpl->copy_template = preg_replace_callback ( '#\\[author-group=(.+?)\\](.*?)\\[/author-group\\]#is',
		function ($matches) {
			global $row;

			$groups = $matches[1];
			$block = $matches[2];
			
			$groups = explode( ',', $groups );
			
			if( !in_array( $row['user_group'], $groups ) ) return "";
	
			return $block;
		},		
	$tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[not-author-group=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( '#\\[not-author-group=(.+?)\\](.*?)\\[/not-author-group\\]#is',
		function ($matches) {
			global $row;
			
			$groups = $matches[1];
			$block = $matches[2];
			
			$groups = explode( ',', $groups );
			
			if( in_array( $row['user_group'], $groups ) ) return "";
	
			return $block;
		},		
	$tpl->copy_template );
}
	
if( $row['signature'] and $user_group[$row['user_group']]['allow_signature'] ) {
	
	$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "\\1" );
	$tpl->set_block( "'\\[not-signature\\](.*?)\\[/not-signature\\]'si", "" );
	$tpl->set( '{signature}', stripslashes( $row['signature'] ) );

} else {
	
	$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "" );
	$tpl->set( '{signature}', "" );
	$tpl->set( '[not-signature]', "" );
	$tpl->set( '[/not-signature]', "" );
}

if( $row['info'] ) {
	$tpl->set( '[user-info]', "" );
	$tpl->set( '[/user-info]', "" );
	$tpl->set( '{user-info}', stripslashes( $row['info'] ) );
	$tpl->set_block( "'\\[not-user-info\\](.*?)\\[/not-user-info\\]'si", "" );	
} else {
	$tpl->set_block( "'\\[user-info\\](.*?)\\[/user-info\\]'si", "" );
	$tpl->set( '{user-info}', "" );
	$tpl->set( '[not-user-info]', "" );
	$tpl->set( '[/not-user-info]', "" );
}

?>