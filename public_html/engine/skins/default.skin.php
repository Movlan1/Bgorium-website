<?PHP
/*
=====================================================
 Bgorium LLC - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2025 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: default.skin.php
-----------------------------------------------------
 Use: Adminpanel template
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$year = date('Y', time());
$lang_change = "";

if( !isset($pop_notice) ) {
	$pop_notice = "";
}
if( !isset($pm_list) ) {
	$pm_list = '';
}
if( !isset($avatar) ) {
	$avatar = '';
}
if( !isset($profile_link) ) {
	$profile_link = '';
}

if( !isset($message_notice) ) {
	$message_notice = '';
}

if( !isset($header_title) ) {
	$header_title = '';
}

if( !isset($breadcrumb) ) {
	$breadcrumb = '';
}

if( !isset($config['http_home_url']) ) {
	$config['http_home_url'] = explode( basename($_SERVER['PHP_SELF']), $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
}

if ($is_loged_in) {

	$language_list = get_folder_list( 'language' );
	$lang_change = "<li>";
	
	foreach ( $language_list as $key => $value ) {
		
			$lang_change .="<a href=\"#\" data-lang=\"{$key}\">";
			
			if( isset($value['icon']) AND $value['icon'] ) {
				$lang_change .= "<img src=\"language/{$key}/{$value['icon']}\">";
			}
			
			$lang_change .="{$value['name']}</a>";
	}
	
	$lang_change .= "</li>";

	$sql_result = $db->query("SELECT c.id, c.subject, m.content, c.updated_at, m.sender_id, u.name, u.foto, CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN 0 ELSE 1 END AS read_status FROM " . USERPREFIX . "_conversations c JOIN " . USERPREFIX . "_conversation_users cu ON c.id = cu.conversation_id JOIN " . USERPREFIX . "_conversations_messages m ON c.id = m.conversation_id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON c.id = cr.conversation_id AND cu.user_id = cr.user_id JOIN (SELECT conversation_id, MAX(created_at) AS last_message_time FROM " . USERPREFIX . "_conversations_messages GROUP BY conversation_id) AS lm ON m.conversation_id = lm.conversation_id AND m.created_at = lm.last_message_time JOIN " . USERPREFIX . "_users u ON m.sender_id = u.user_id WHERE cu.user_id = '{$member_id['user_id']}' ORDER BY read_status ASC, c.updated_at DESC LIMIT 5", false );

	$pm_list = array();

	if ($sql_result instanceof mysqli_result) {
		while ( $row = $db->get_array($sql_result) ) {
			
			$row['content'] = preg_replace ( "#\[hide(.*?)\]#i", "", $row['content'] );
			$row['content'] = str_ireplace( "[/hide]", "", $row['content']);
			$row['content'] = str_replace ("<br>", " ", $row['content']);
			$row['content'] = str_replace ("<br>", " ", $row['content']);
			$row['content'] = str_replace ("{", "&#123;", $row['content']);
			$row['content'] = dle_substr(strip_tags (stripslashes($row['content']) ),0,100)." ...";

			if( date( 'Ymd', $row['updated_at'] ) == date( 'Ymd', time() ) ) {
					
				$date = date( "H:i", $row['updated_at'] );
				
			} else {
					
				$date = langdate( "D H:i", $row['updated_at'] );
				
			}

			$pm_avatar = '';

			if ( $row['foto'] AND count(explode("@", $row['foto'])) == 2 ) {
			
				$pm_avatar = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);	
			
			} else {
				
				if( $row['foto'] ) {
					
					if (strpos($row['foto'], "//") === 0) $pm_avatar = "https:".$row['foto']; else $pm_avatar = $row['foto'];
		
					$pm_avatar = @parse_url ( $pm_avatar );
		
					if( $pm_avatar['host'] ) {
						
						$pm_avatar = $row['foto'];
						
					} else $pm_avatar = "uploads/fotos/" . $row['foto'];
				
				}
			
			}

			if ($pm_avatar) {
				$pm_avatar = "<img src=\"{$pm_avatar}\" class=\"img-circle img-sm\" alt=\"\">";
			} else {
				$pm_avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-sm" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
			}

		$pm_list[] = <<<HTML
<li class="media">
	<div class="media-left">
		{$pm_avatar}
	</div>

	<div class="media-body">
	  <div class="media-title">
		<a href="index.php?do=pm&amp;doaction=readpm&amp;pmid={$row['id']}" target="_blank" class="media-heading">
			<span class="text-semibold">{$row['name']}</span>
			<span class="media-annotation pull-right">{$date}</span>
		</a>
	  </div>
		<span class="text-muted">{$row['content']}</span>
	</div>
</li>
HTML;

		}
	}

	if( count($pm_list) ) {
		$pm_list = "<ul class=\"media-list dropdown-content-body\">".implode("", $pm_list)."</ul>";
	} else {
		$pm_list = "<div class=\"panel-body\">{$lang['no_message']}</div>";
	}

	$avatar = '';

	if ( count(explode("@", $member_id['foto'])) == 2 ) {
		
		$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($member_id['foto'])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']);
		
	} else {
		
		if( $member_id['foto'] ) {
			
			if (strpos($member_id['foto'], "//") === 0) $avatar = "http:".$member_id['foto']; else $avatar = $member_id['foto'];

			$avatar = @parse_url ( $avatar );

			if( $avatar['host'] ) {
				
				$avatar = $member_id['foto'];
				
			} else $avatar =  "uploads/fotos/" . $member_id['foto'];

		}
	}

	if ($avatar) {
		$avatar = "<img src=\"{$avatar}\" class=\"img-circle img-responsive\">";
	} else {
		$avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-responsive" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
	}

	if ( $member_id['pm_unread'] ) {
		$pop_notice = "<span class=\"badge bg-warning-800\">{$member_id['pm_unread']}</span>";
	}

	$message_notice = "<span class=\"badge bg-teal pull-right\">{$member_id['pm_all']}</span>";
	
	$profile_link =  "user/" . urlencode ( $member_id['name'] ) . "/";
	
	$options = array ();
	
	$options['config'] = array (
								
								array (
											'name' => $lang['opt_all'], 
											'url' => "?mod=options&action=syscon", 
											'mod' => "options", 
											'access' => "admin" 
								),

								array (
											'name' => $lang['opt_storages'], 
											'url' => "?mod=storage", 
											'mod' => "storage", 
											'access' => "admin"
								), 

								array (
											'name' => $lang['opt_cat'], 
											'url' => "?mod=categories", 
											'mod' => "categories", 
											'access' => $user_group[$member_id['user_group']]['admin_categories'] 
								), 
								
								array (
											'name' => $lang['opt_db'], 
											'url' => "?mod=dboption", 
											'mod' => "dboption", 
											'access' => "admin" 
								), 

								array (
											'name' => $lang['opt_vconf'], 
											'url' => "?mod=videoconfig", 
											'mod' => "videoconfig", 
											'access' => "admin" 
								),
								
								array (
											'name' => $lang['opt_xfil'], 
											'url' => "?mod=xfields&xfieldsaction=configure", 
											'mod' => "xfields", 
											'access' => $user_group[$member_id['user_group']]['admin_xfields'] 
								),

								array (
											'name' => $lang['opt_question'], 
											'url' => "?mod=question", 
											'mod' => "question", 
											'access' => "admin" 
								)
	);
	
	$options['user'] = array (
							
							array (
										'name' => $lang['opt_user'], 
										'url' => "?mod=editusers&action=list", 
										'mod' => "editusers", 
										'access' => $user_group[$member_id['user_group']]['admin_editusers'] 
							), 
							
							array (
										'name' => $lang['opt_xprof'], 
										'url' => "?mod=userfields&xfieldsaction=configure", 
										'mod' => "userfields", 
										'access' => $user_group[$member_id['user_group']]['admin_userfields'] 
							), 
							
							array (
										'name' => $lang['opt_group'], 
										'url' => "?mod=usergroup", 
										'mod' => "usergroup", 
										'access' => "admin" 
							),
							array (
										'name' => $lang['opt_social'], 
										'url' => "?mod=social", 
										'mod' => "social", 
										'access' => "admin"
							),
							array (
										'name' => $lang['opt_ipban'], 
										'url' => "?mod=blockip", 
										'mod' => "blockip", 
										'access' => $user_group[$member_id['user_group']]['admin_blockip'] 
							)
	);
	
	$options['templates'] = array (
									
									array (
											'name' => $lang['opt_t'], 
											'url' => "?mod=templates&user_hash=" . $dle_login_hash, 
											'mod' => "templates", 
											'access' => "admin" 
									), 
									
									array (
											'name' => $lang['opt_email'], 
											'url' => "?mod=email", 
											'mod' => "email", 
											'access' => "admin" 
									) 
	);

	
	
	$options['filter'] = array (
								array (
											'name' => $lang['opt_plugins'], 
											'url' => "?mod=plugins", 
											'mod' => "plugins", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_srebuild'], 
											'url' => "?mod=rebuild", 
											'mod' => "rebuild", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_fil'], 
											'url' => "?mod=wordfilter", 
											'mod' => "wordfilter", 
											'access' => $user_group[$member_id['user_group']]['admin_wordfilter'] 
								), 
								array (
											'name' => $lang['opt_iptools'], 
											'url' => "?mod=iptools", 
											'mod' => "iptools", 
											'access' => $user_group[$member_id['user_group']]['admin_iptools'] 
								), 
								array (
											'name' => $lang['opt_sfind'], 
											'url' => "?mod=search", 
											'mod' => "search", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_complaint'], 
											'url' => "?mod=complaint", 
											'mod' => "complaint",  
											'access' => $user_group[$member_id['user_group']]['admin_complaint'] 
								),
								array (
											'name' => $lang['opt_check'], 
											'url' => "?mod=check", 
											'mod' => "check",
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_links'], 
											'url' => "?mod=links", 
											'mod' => "links",
											'access' => $user_group[$member_id['user_group']]['admin_links'] 
								),
								array (
											'name' => $lang['opt_redirects'], 
											'url' => "?mod=redirects", 
											'mod' => "redirects",
											'access' => $user_group[$member_id['user_group']]['admin_redirects'] 
								),
								array (
											'name' => $lang['opt_metatags'], 
											'url' => "?mod=metatags", 
											'mod' => "metatags",
											'access' => $user_group[$member_id['user_group']]['admin_meta'] 
								)
	);

	
	
	$options['others'] = array (
								array (
											'name' => $lang['opt_rules'], 
											'url' => "?mod=static&action=doedit&page=rules", 
											'mod' => "rules",
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_static'], 
											'url' => "?mod=static", 
											'mod' => "static",
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_clean'], 
											'url' => "?mod=clean", 
											'mod' => "clean",
											'access' => "admin" 
								), 								
								
								array (
											'name' => $lang['main_newsl'], 
											'url' => "?mod=newsletter", 
											'mod' => "newsletter",
											'access' => $user_group[$member_id['user_group']]['admin_newsletter'] 
								), 
								array (
											'name' => $lang['opt_vote'], 
											'url' => "?mod=editvote", 
											'mod' => "editvote",
											'access' => $user_group[$member_id['user_group']]['admin_editvote'] 
								), 
								
								array (
											'name' => $lang['opt_img'], 
											'url' => "?mod=files", 
											'mod' => "files",
											'access' => "admin" 
								), 
								
								array (
											'name' => $lang['opt_banner'], 
											'url' => "?mod=banners&action=list", 
											'mod' => "banners",
											'access' => $user_group[$member_id['user_group']]['admin_banners'] 
								), 
								array (
											'name' => $lang['opt_google'], 
											'url' => "?mod=googlemap", 
											'mod' => "googlemap",
											'access' => $user_group[$member_id['user_group']]['admin_googlemap'] 
								),
								array (
											'name' => $lang['opt_rss'], 
											'url' => "?mod=rss", 
											'mod' => "rss",
											'access' => $user_group[$member_id['user_group']]['admin_rss'] 
								), 
								array (
											'name' => $lang['opt_rssinform'], 
											'url' => "?mod=rssinform", 
											'mod' => "rssinform",
											'access' => $user_group[$member_id['user_group']]['admin_rssinform'] 
								),
								array (
											'name' => $lang['opt_tagscloud'], 
											'url' => "?mod=tagscloud", 
											'mod' => "tagscloud",
											'access' => $user_group[$member_id['user_group']]['admin_tagscloud'] 
								),

								array (
											'name' => $lang['opt_logs'], 
											'url' => "?mod=logs", 
											'mod' => "logs",
											'access' => "admin" 
								),
	);


	$db->query( "SELECT * FROM " . PREFIX . "_admin_sections" );

	while ( $row = $db->get_array() ) {

		if ($row['allow_groups'] != "all") {

			$groups = explode(",", $row['allow_groups']);

			if ( !in_array($member_id['user_group'], $groups) AND $member_id['user_group'] !=1 ) continue;

		}

		$row['name'] = totranslit($row['name'], true, false);
		$row['title'] = strip_tags(stripslashes($row['title']));

		$options['admin_sections'][] = array (
											'name' => $row['title'], 
											'url' => "?mod={$row['name']}", 
											'mod' => "{$row['name']}",
											'access' => 1
										);

	}


	foreach ( $options as $sub_options => $value ) {
		$count_options = count( $value );
		
		for($i = 0; $i < $count_options; $i ++) {

			if ($member_id['user_group'] == 1 ) continue;

			if ($member_id['user_group'] != 1 AND  $value[$i]['access'] == "admin") unset( $options[$sub_options][$i] );

			if ( !$value[$i]['access'] ) unset( $options[$sub_options][$i] );
		}
	}
	
	$subs = 0;
	$sidebar= "";
	$menu_item = array();
	
	$menu_item[] = "<li><a href=\"?mod=options&action=options\"><i class=\"fa fa-globe\"></i> <span>{$lang['header_all']}</span></a></li>";

	foreach ( $options as $sub_options ) {
	
		$menu_item_header = $lang['opt_hopt'];
		$icon= "wrench";
		if( $subs == 1 ) { $menu_item_header = $lang['opt_s_acc']; $icon= "user";}
		if( $subs == 2 ) { $menu_item_header = $lang['opt_s_tem']; $icon= "laptop";}
		if( $subs == 3 ) { $menu_item_header = $lang['opt_s_fil']; $icon= "leaf";}
		if( $subs == 4 ) { $menu_item_header = $lang['opt_s_oth']; $icon= "link";}
		if( $subs == 5 ) { $menu_item_header = $lang['admin_other_section']; $icon= "list-alt";}
		
		$subs ++;
		
		if( !count( $sub_options ) ) continue;
		
		$submenu_item = array();

		foreach ( $sub_options as $option ) {
		
			if ($mod == $option['mod']) {
				$active_submenu = " class=\"active\"";
			} else $active_submenu = "";
		
			if ($mod == "options" AND $action != "syscon") {
				$active_submenu = "";
			}
			if ($mod == "static" AND isset($_GET['page']) AND $_GET['page'] == "rules") {
				$active_submenu = "";
			}
			
			$submenu_item[] = "<li{$active_submenu}><a href=\"{$option['url']}\">{$option['name']}</a></li>";
		}

		$submenu_item = implode("", $submenu_item);
	
		$menu_item[] = "<li><a href=\"#\" class=\"has-ul\"><i class=\"fa fa-{$icon}\"></i> <span>{$menu_item_header}</span></a><ul class=\"hidden-ul\">{$submenu_item}</ul></li>";	
	}
	
	if( count( $menu_item ) ) $sidebar= implode("", $menu_item);
	else $sidebar= "";
	
} else $sidebar= "";

if( @file_exists( ROOT_DIR . '/templates/'. $config['skin'].'/adminpanel.css' ) ) {
	
		$custom_css = "<link href=\"templates/{$config['skin']}/adminpanel.css?v={$config['cache_id']}\" rel=\"stylesheet\" type=\"text/css\">";
		
} else $custom_css = "";

if( $is_loged_in AND $member_id['user_group'] == 1 ) {
	$settings_bar = "<li><a href=\"?mod=videoconfig\"><i class=\"fa fa-play\"></i>{$lang['settings_video']}</a></li><li class=\"divider\"></li><li><a href=\"?mod=options&action=syscon\"><i class=\"fa fa-cogs\"></i>{$lang['opt_hopt']}</a></li>";
} else {
	$settings_bar = "";
}

$body_class = "";
$html_class = "";
$sidebardefault = "sidebar-default";


$html_classes = array('htmlfontsize-50', 'htmlfontsize-75', 'htmlfontsize-80', 'htmlfontsize-85', 'htmlfontsize-90', 'htmlfontsize-95', 'htmlfontsize-105', 'htmlfontsize-110', 'htmlfontsize-115', 'htmlfontsize-120', 'htmlfontsize-125', 'htmlfontsize-130', 'htmlfontsize-135', 'htmlfontsize-140', 'htmlfontsize-145', 'htmlfontsize-150', 'htmlfontsize-175', 'htmlfontsize-200');

if( $is_loged_in AND file_exists( ENGINE_DIR . "/cache/system/adminpanel/" . md5(SECURE_AUTH_KEY.$member_id['user_id']) ) ) {

	$body_class = trim(@file_get_contents( ENGINE_DIR . "/cache/system/adminpanel/" . md5(SECURE_AUTH_KEY.$member_id['user_id']) ));

} else $body_class = '';

$body_classes = explode(' ', $body_class);
$body_class = array();

foreach ($body_classes as $value) {
	$value = trim($value);

	if( in_array($value, $html_classes) ) {

		$html_class = $value;

	} elseif($value) $body_class[] = $value;

}

if( count($body_class) ) $body_class = implode(' ', $body_class); else $body_class = '';

if ($html_class) {
	$html_class = " class=\"".htmlspecialchars ( trim($html_class), ENT_QUOTES, 'UTF-8' )."\"";
}

if($body_class) {
	
	$dark_theme = strpos ( $body_class, "dle_theme_dark" );
	$auto_dark = strpos ( $body_class, "auto_dark_theme" );

	if( $auto_dark !== false AND $dark_theme === false ) {

		$night_time = array (
							 '01' => array('from' => '16:20',
										   'to'   => '08:40'),
							 '02' => array('from' => '17:30',
										   'to'   => '07:50'),
							 '03' => array('from' => '18:30',
										   'to'   => '06:40'),
							 '04' => array('from' => '19:30',
										   'to'   => '05:30'),
							 '05' => array('from' => '20:30',
										   'to'   => '04:20'),
							 '06' => array('from' => '21:20',
										   'to'   => '03:50'),
							 '07' => array('from' => '21:00',
										   'to'   => '04:00'),
							 '08' => array('from' => '20:00',
										   'to'   => '05:00'),
							 '09' => array('from' => '18:50',
										   'to'   => '06:00'),
							 '10' => array('from' => '17:30',
										   'to'   => '07:00'),
							 '11' => array('from' => '16:20',
										   'to'   => '08:00'),
							 '12' => array('from' => '16:00',
										   'to'   => '08:50')
							 );

		$detect_time = new DateTime('@'.time() );
		
		if (isset($member_id['timezone']) AND $member_id['timezone'] ) {
			$timezone = $member_id['timezone'];
		} else {
			$timezone = date_default_timezone_get();
		}
		
		if (!in_array($timezone, DateTimeZone::listIdentifiers())) $timezone = 'Europe/Moscow';
		
		$detect_time->setTimeZone(new DateTimeZone($timezone));
	
		$c_m = $detect_time->format('m');
		$c_t = $detect_time->format('H:i');
		
		if(is_array($night_time[$c_m])) {
			
			if( $c_t >= $night_time[$c_m]['from'] OR $c_t <= $night_time[$c_m]['to'] ) {
				$body_class = str_replace ('dle_theme_', 'saved_dle_theme_', $body_class);
				$body_class .= " dle_theme_dark auto_night_mode";
				$dark_theme = true;
			}
			
		}
	
	}
	
	if ( $dark_theme !== false ) {
		$sidebardefault = "";
	}
	
	$body_class = " class=\"".htmlspecialchars ( trim($body_class), ENT_QUOTES, 'UTF-8' )."\"";
}

$config['session_timeout'] = isset($config['session_timeout']) ? intval($config['session_timeout']) : 0;
$dle_login_hash = isset($dle_login_hash) ? $dle_login_hash : '';

$timeout_js = "";

if( $config['session_timeout'] ) {
	
	$warn_time = $config['session_timeout'] * 1000 * 60;
	$redir_time = $warn_time + 10000;
	
	$timeout_js = <<<HTML
<script>  
<!--
$(function(){
	$.sessionTimeout({
		title: '{$lang['timeout_title']}',
		message: '{$lang['timeout_text']}',
		keepBtnText: '{$lang['timeout_keep']}',
		logoutBtnText: '{$lang['skin_logout']}',
		keepBtnClass:   'btn bg-teal btn-raised btn-sm legitRipple',
		logoutBtnClass: 'btn bg-danger btn-sm btn-raised legitRipple',
		keepAlive: false,
		logoutUrl: '?action=logout',
		redirUrl:  '?mod=timeout',
		onRedir: function() {
			$.get("engine/ajax/controller.php?mod=adminfunction", { action: 'timeout', user_hash: '{$dle_login_hash}' }, function(data){
	
				location.reload(true);
	
			});
		},
		warnAfter: {$warn_time},
		redirAfter: {$redir_time}
	});
});
//-->
</script>
HTML;

}

if(!$is_loged_in) {
	$member_id['user_id'] = 0;
	$member_id['name'] = '';
	$member_id['user_group'] = 5;
	
	if( !isset($user_group) ) {
		$user_group[$member_id['user_group']]['group_name'] = '';
	}
	
}

$skin_header = <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}"{$html_class}>
<head>
	<meta charset="utf-8">
	<title>Bgorium LLC</title>
	<meta name="HandheldFriendly" content="true">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">

	{css_files}
	{$custom_css}
	{js_files}
</head>
<body{$body_class}>
<script>
<!--
var dle_act_lang   = ["{$lang['p_yes']}", "{$lang['p_no']}", "{$lang['p_enter']}", "{$lang['p_cancel']}", "{$lang['media_upload']}", "{$lang['edit_seldel']}"];
var cal_language   = '{$lang['language_code']}';
var filedefaulttext= '{$lang['file_def_1']}';
var filebtntext    = '{$lang['file_def_2']}';
var dle_login_hash = '{$dle_login_hash}';
//-->
</script>
	<div class="navbar navbar-inverse bg-primary-700">
		<div class="navbar-header">
			<a class="navbar-brand" href="?mod=main">Bgorium LLC</a>
			<ul class="nav navbar-nav visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="fa fa-angle-double-down"></i></a></li>
				<li><a class="sidebar-mobile-main-toggle"><i class="fa fa-bars"></i></a></li>
			</ul>
		</div>
		<div class="navbar-collapse collapse" id="navbar-mobile">
			<ul class="nav navbar-nav">
				<li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="fa fa-bars"></i></a></li>
			</ul>
			<div class="navbar-right">	
				<ul class="nav navbar-nav">
				
					
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{$lang['change_language']}"><i class="fa fa-language"></i><span class="visible-xs-inline-block position-right">{$lang['change_language']}</span></a>
								<ul class="dropdown-menu dropdown-menu-right lang_switcher_toggle">
									{$lang_change}
								</ul>
							</li>
						
					
				
					<li><a href="{$config['http_home_url']}" title="{$lang['skin_view']}" target="_blank"><i class="fa fa-globe"></i><span class="visible-xs-inline-block position-right">{$lang['skin_view']}</span></a></li>
					<li><a class="sidebar-control hidden-xs" id="full_screen_toggle" title="{$lang['fullscreen_toggle']}"><i class="fa fa-expand"></i></a></li>
					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{$lang['opt_pm_h']}"><i class="fa fa-envelope"></i><span class="visible-xs-inline-block position-right">{$lang['opt_pm_h']}</span>{$pop_notice}</a>
						<div class="dropdown-menu dropdown-content" style="min-width:25rem;">
							<div class="dropdown-content-heading">
								{$lang['opt_pm_h']}
								<ul class="icons-list">
									<li><a href="index.php?do=pm" target="_blank"><i class="fa fa-envelope-o"></i></a></li>
								</ul>
							</div>
							{$pm_list}
							<div class="dropdown-content-footer">
								<a href="index.php?do=pm" data-popup="tooltip" title="{$lang['all_messages']}" target="_blank"><i class="fa fa-ellipsis-h"></i></a>
							</div>
						</div>
					</li>					
				</ul>
			</div>
		</div>
	</div>
	<div class="page-container">
		<div class="page-content">
			<div class="sidebar sidebar-main {$sidebardefault}">
				<div class="sidebar-content">
					<div class="sidebar-user-material">
						<div class="category-content">
							<div class="sidebar-user-material-content">
								{$avatar}
								<h6>{$member_id['name']}</h6>
								<span class="text-size-small">{$user_group[$member_id['user_group']]['group_name']}</span>
							</div>
							<div class="sidebar-user-material-menu">
								<a href="#user-nav" data-toggle="collapse"><span>{$lang['account']}</span> <i class="caret"></i></a>
							</div>
						</div>
						<div class="navigation-wrapper collapse" id="user-nav">
							<ul class="navigation">
								<li><a href="{$profile_link}" target="_blank"><i class="fa fa-user"></i> <span>{$lang['header_profile']}</span></a></li>
								<li><a href="index.php?do=pm" target="_blank"><i class="fa fa-envelope"></i> <span>{$message_notice} {$lang['header_messages']}</span></a></li>
								<li class="divider"></li>
								<li><a href="?mod=editusers&action=edituser&id={$member_id['user_id']}"><i class="fa fa-cog"></i> <span>{$lang['header_settings']}</span></a></li>
								<li><a href="?action=logout"><i class="fa fa-power-off text-danger-800"></i> <span>{$lang['skin_logout']}</span></a></li>
							</ul>
						</div>
					</div>
					<div class="sidebar-category sidebar-category-visible">
						<div class="category-content no-padding">
							<ul class="navigation navigation-main navigation-accordion">
								<li class="navigation-header"><span>{$lang['modules_list']}</span> <i class="fa fa-ellipsis-h" title="{$lang['modules_list']}"></i></li>
								{$sidebar}
							</ul>
						</div>
					</div>

				</div>
			</div>
			<div class="content-wrapper">
				<div class="page-header page-header-default">
					<div class="page-header-content">
						<div class="page-title">
							<h4>{$header_title}</h4>
						</div>
						<div class="heading-elements">
							<div class="heading-btn-group">
								<a href="?mod=addnews&amp;action=addnews" class="btn btn-link btn-float text-size-small has-text"><i class="fa fa-file-text-o text-primary"></i> <span>{$lang['add_news']}</span></a>
								<a href="?mod=editnews&amp;action=list" class="btn btn-link btn-float text-size-small has-text"><i class="fa fa-edit text-primary"></i> <span>{$lang['edit_news']}</span></a>
							</div>
						</div>
					</div>
					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							{$breadcrumb}
						</ul>
						<ul class="breadcrumb-elements">
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog position-left"></i>{$lang['settings']}<span class="caret"></span></a>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a id="style_switcher_toggle"><i class="fa fa-paint-brush"></i>{$lang['settings_panel']}</a></li>
									<li><a href="?mod=editusers&action=edituser&id={$member_id['user_id']}"><i class="fa fa-user"></i>{$lang['header_settings']}</a></li>
									{$settings_bar}
								</ul>
							</li>
						</ul>
					</div>
				</div>
				<div class="content" >
	<!-- maincontent beginn -->
HTML;

$skin_footer = <<<HTML
					<div class="footer text-muted text-size-small">
						Bgorium LLC&reg;  Copyright 2004-{$year} &copy; <a href="https://okmedia.az/" target="_blank">Okmedia LLC</a> All rights reserved.
					</div>
				</div>
			</div>
		</div>
	</div>
    <div id="style_switcher" title="{$lang['settings_panel_1']}" style="display: none;">
        <div class="row">
            <h5>{$lang['settings_panel_9']}</h5>
            <ul class="switcher_app_themes" id="theme_switcher">
                <li class="app_style_default" data-app-theme="">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_j" data-app-theme="dle_theme_j">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_a" data-app-theme="dle_theme_a">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_b" data-app-theme="dle_theme_b">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_c" data-app-theme="dle_theme_c">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_d" data-app-theme="dle_theme_d">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_e" data-app-theme="dle_theme_e">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_f" data-app-theme="dle_theme_f">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_g" data-app-theme="dle_theme_g">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_h" data-app-theme="dle_theme_h">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_i" data-app-theme="dle_theme_i">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
                <li class="switcher_theme_dark" data-app-theme="dle_theme_dark">
                    <span class="app_color_main"></span>
                    <span class="app_color_accent"></span>
                </li>
            </ul>
			<div class="checkbox">
				<label><input type="checkbox" name="auto_dark_theme" id="auto_dark_theme" class="icheck">{$lang['settings_panel_10']}</label>
			</div>
        </div>
        <div class="row">
            <h5>{$lang['settings_panel_2']}</h5>
			<div class="checkbox">
				<label><input type="checkbox" name="style_sidebar_mini" id="style_sidebar_mini" class="icheck">{$lang['settings_panel_3']}</label>
			</div>
        </div>
        <div class="row">
            <h5>{$lang['settings_panel_4']}</h5>
			<div class="checkbox">
				<label><input type="checkbox" name="style_layout_boxed" id="style_layout_boxed" class="icheck">{$lang['settings_panel_5']}</label>
			</div>
        </div>

        <div class="row">
            <h5>{$lang['settings_panel_11']}</h5>
			<div class="col-sm-6 no-padding-left">
				<label class="checkbox-inline"><input type="checkbox" name="style_smoothing" id="style_smoothing" class="icheck">{$lang['settings_panel_12']}</label>
			</div>
			<div class="col-sm-6 no-padding-left">
				<span class="position-left">{$lang['settings_panel_13']}</span><select class="uniform" name="html_font_size" id="html_font_size" data-width="auto" data-size="7"><option value="htmlfontsize-80">80%</option><option value="htmlfontsize-85">85%</option><option value="htmlfontsize-90">90%</option><option value="htmlfontsize-95">95%</option><option value="">100%</option><option value="htmlfontsize-105">105%</option><option value="htmlfontsize-110">110%</option><option value="htmlfontsize-115">115%</option><option value="htmlfontsize-120">120%</option><option value="htmlfontsize-125">125%</option><option value="htmlfontsize-130">130%</option><option value="htmlfontsize-135">135%</option><option value="htmlfontsize-140">140%</option><option value="htmlfontsize-145">145%</option><option value="htmlfontsize-150">150%</option></select>
			</div>
        </div>

        <div class="row">
            <h5>{$lang['settings_panel_6']}</h5>
			<div class="col-sm-6 no-padding-left">
				<label class="radio-inline"><input class="icheck" type="radio" name="style_input" value="0">{$lang['settings_panel_7']}</label>
			</div>
			<div class="col-sm-6 no-padding-left">
				<label class="radio-inline"><input class="icheck" type="radio" name="style_input" value="1">{$lang['settings_panel_8']}</label>
			</div>
        </div>

        <div class="row">
            <h5>{$lang['settings_panel_14']}</h5>
			<div class="col-sm-6 no-padding-left">
				<label class="radio-inline"><input class="icheck" type="radio" name="editor_style" value="0">{$lang['settings_panel_15']}</label>
			</div>
			<div class="col-sm-6 no-padding-left">
				<label class="radio-inline"><input class="icheck" type="radio" name="editor_style" value="1">{$lang['settings_panel_16']}</label>
			</div>
        </div>

        <div class="row">
			<div class="checkbox">
				<label><input type="checkbox" name="editor_auto_height" id="editor_auto_height" class="icheck">{$lang['settings_panel_17']}</label>
			</div>
        </div>

    </div>
	{$timeout_js}
</body>
</html>
HTML;

$skin_login = <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
	<meta charset="utf-8">
	<title>Bgorium LLC - {$lang['skin_title']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="robots" content="noindex, nofollow">
	{css_files}
    {js_files}
</head>
<body class="no-theme">
<script>
<!--
var dle_act_lang   = [];
var cal_language   = '{$lang['language_code']}';
var filedefaulttext= '';
var filebtntext    = '';
//-->
</script>

<div class="container">
  <div class="col-md-4 col-md-offset-4">
    <div class="page-container">
<!--MAIN area-->


	<div class="panel panel-default" style="margin-top: 100px;">

      <div class="panel-heading">
        {$lang['skin_title']} Bgorium LLC
      </div>
	  
      <div class="panel-body">
        <form  name="login" action="" method="post" class="separate-sections"><input type="hidden" name="subaction" value="dologin">
			{result}
			<div class="form-group has-feedback has-feedback-left">
				<input class="form-control" type="text" name="username" placeholder="{mauth}">
				<div class="form-control-feedback">
					<i class="fa fa-{mauth2} text-muted"></i>
				</div>
			</div>
			<div class="form-group has-feedback has-feedback-left">
				<input class="form-control" type="password" name="password" placeholder="{$lang['login_box_3']}">
				<div class="form-control-feedback">
					<i class="fa fa-lock text-muted"></i>
				</div>
			</div>
			<div class="form-group">
				{select}
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label><input type="checkbox" class="icheck" name="login_not_save" id="login_not_save" value="1">{$lang['m_not_save']}</label>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary btn-raised btn-block">{$lang['login_button']} <i class="fa fa-sign-in"></i></button>
			</div>

        </form>
		<div class="text-right">
			<a href="?mod=lostpassword" class="status-info text-right">{$lang['lost_pass']}</a>
		</div>
      </div>

    </div>
	<div class="text-muted text-size-small text-center">Bgorium LLC&reg;  Copyright 2004-{$year}<br>&copy; <a href="https://dle-news.ru/" target="_blank">SoftNews Media Group</a> All rights reserved.</div>



	 <!--MAIN area-->
  </div>
</div>
</div>

</body>
</html>
HTML;


$skin_not_logged_header = <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
	<meta charset="utf-8">
	<title>Bgorium LLC - {$lang['skin_title']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="robots" content="noindex, nofollow">
	{css_files}
    {js_files}
</head>
<body class="no-theme">
<script>
<!--
var dle_act_lang   = [];
var cal_language   = '{$lang['language_code']}';
var filedefaulttext= '';
var filebtntext    = '';
//-->
</script>

<div class="container">
  <div class="col-md-8 col-md-offset-2">
    <div class="page-container mt-20">
<!--MAIN area-->
HTML;


$skin_not_logged_footer = <<<HTML
	 <!--MAIN area-->
  </div>
</div>
</div>

</body>
</html>
HTML;

?>