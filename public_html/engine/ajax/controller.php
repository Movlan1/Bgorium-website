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
 File: controller.php
-----------------------------------------------------
 Use: AJAX Controller
=====================================================
*/

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

header("Content-type: text/html; charset=utf-8");

require_once(ENGINE_DIR . '/classes/plugins.class.php');

$mod = str_replace(chr(0), '', (string)$_REQUEST['mod']);
$mod = trim( strtolower(strip_tags( $mod )) );
$mod = preg_replace( "/\s+/ms", "_", $mod );
$mod = str_replace( "/", "_", $mod );
$mod = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $mod );

if( !$mod ) {
	
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
	
}

date_default_timezone_set ( $config['date_adjust'] );

$admin_modules = array( "adminfunction", "antivirus", "clean", "upload", "find_relates", "find_tags", "keywords", "rebuild", "rss", "sitemap", "templates", "updates", "plugins" );

$block_country = false;

if( in_array($mod, $admin_modules) ) {
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/inc/include/functions.inc.php'));

	$selected_language = $config['langs'];
	
	if (isset( $_COOKIE['selected_language'] )) { 
	
		$_COOKIE['selected_language'] = trim(totranslit( $_COOKIE['selected_language'], false, false ));
	
		if ($_COOKIE['selected_language'] != "" AND @is_dir ( ROOT_DIR . '/language/' . $_COOKIE['selected_language'] )) {
			$selected_language = $_COOKIE['selected_language'];
		}
	
	}
	
	if ( file_exists( DLEPlugins::Check(ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng') ) ) {
		include_once (DLEPlugins::Check(ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng'));
	}
	
	if (isset($config['allowed_panel_country']) and trim($config['allowed_panel_country'])) {
		if (!DLECountry::Check($config['allowed_panel_country'])) $block_country = true;
	}

	if (isset($config['declined_panel_country']) and trim($config['declined_panel_country'])) {
		if (DLECountry::Check($config['declined_panel_country'])) $block_country = true;
	}

} else {
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php'));

	if( isset($_REQUEST['skin']) AND $_REQUEST['skin'] ) {
		$_REQUEST['skin'] = $_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['skin'], false, false));
	}
	
	if(isset($_REQUEST['dle_skin']) AND $_REQUEST['dle_skin'] ) {
		
		$_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['dle_skin'], false, false));
		
		if( $_REQUEST['dle_skin'] AND @is_dir( ROOT_DIR . '/templates/' . $_REQUEST['dle_skin'] ) ) {
			
			$config['skin'] = $_REQUEST['dle_skin'];
			
		} else {
			
			$_REQUEST['dle_skin'] = $_REQUEST['skin'] = $config['skin'];
			
		}
		
	} elseif (isset($_COOKIE['dle_skin']) AND $_COOKIE['dle_skin'] ) {
		
		$_COOKIE['dle_skin'] = trim(totranslit( (string)$_COOKIE['dle_skin'], false, false ));
		
		if( $_COOKIE['dle_skin'] AND is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
			$config['skin'] = $_COOKIE['dle_skin'];
		}
		
	}

	if ( isset($config["lang_" . $config['skin']]) AND $config["lang_" . $config['skin']] AND file_exists( DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng') ) ) {
		
		include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng'));
		
	} else {
		
		include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));
		
	}

	if( isset($config['allowed_country']) AND trim($config['allowed_country']) AND (!$config['allow_bots'] OR ($config['allow_bots'] AND !isBotDetected()) ) ) {
		if( !DLECountry::Check($config['allowed_country']) ) {

			$block_country = true;
		
		} elseif($config['block_vpn'] AND isset($_COOKIE['dle_possible_vpn'])) {

			$dle_possible_vpn = json_decode($_COOKIE['dle_possible_vpn'], true);

			if (is_array($dle_possible_vpn) AND isset($dle_possible_vpn['site'])) {
				$block_country = true;
			}

		}
	}

	if( isset($config['declined_country']) AND trim($config['declined_country']) AND (!$config['allow_bots'] OR ($config['allow_bots'] AND !isBotDetected()) ) ) {
		if( DLECountry::Check($config['declined_country']) ) {

			$block_country = true;

		} elseif ($config['block_vpn'] AND isset($_COOKIE['dle_possible_vpn'])) {

			$dle_possible_vpn = json_decode($_COOKIE['dle_possible_vpn'], true);

			if (is_array($dle_possible_vpn) AND isset($dle_possible_vpn['site'])) {
				$block_country = true;
			}
		}

	}

}

$_ROOT_DLE_URL = explode( "engine/ajax/controller.php", $_SERVER['PHP_SELF'] );
$_ROOT_DLE_URL = reset( $_ROOT_DLE_URL );

if( !$config['http_home_url'] ) {
	$config['http_home_url'] = $_ROOT_DLE_URL;
}

if (strpos($config['http_home_url'], "//") === 0) {
	$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https:".$config['http_home_url'] : $config['http_home_url'] = "http:".$config['http_home_url'];
} elseif (strpos($config['http_home_url'], "/") === 0) {
	$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'] : "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
} elseif( isSSL() AND stripos( $config['http_home_url'], 'http://' ) !== false ) {
	$config['http_home_url'] = str_replace( "http://", "https://", $config['http_home_url'] );
}

if (substr ( $config['http_home_url'], - 1, 1 ) != '/') $config['http_home_url'] .= '/';

dle_session();

if ( $config['cache_type'] ) {

	if( $config['cache_type'] == "2" ) {
		
		include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/redis.class.php'));
		
	} else {
		
		include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/memcache.class.php'));
		
	}

	$dlefastcache = new dle_fastcache($config);
	
}

$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

$cat_info = get_vars( "category" );

if( ! is_array( $cat_info ) ) {
	$cat_info = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row() ) {
		
		if( !$row['active'] ) continue;
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = stripslashes( $value );
		}
	
	}
	set_vars( "category", $cat_info );
	$db->free();
}

$banned_info = get_vars("banned");

if (!is_array ( $banned_info )) {

	$banned_info = array ();

	$db->query ( "SELECT * FROM " . USERPREFIX . "_banned" );
	while ( $row = $db->get_row () ) {

		if ($row['users_id']) {

			$banned_info['users_id'][$row['users_id']] = array (
																'users_id' => $row['users_id'],
																'descr' => stripslashes ( $row['descr'] ),
																'date' => $row['date'],
															    'banned_from' => $row['banned_from']
															   );

		} else {

			if (count ( explode ( ".", $row['ip'] ) ) == 4 OR filter_var( $row['ip'] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) OR strpos($row['ip'], ":") !== false )
				$banned_info['ip'][$row['ip']] = array (
														'ip' => $row['ip'],
														'descr' => stripslashes ( $row['descr'] ),
														'date' => $row['date'],
														'banned_from' => $row['banned_from']
														);
			elseif (strpos ( $row['ip'], "@" ) !== false)
				$banned_info['email'][$row['ip']] = array (
															'email' => $row['ip'],
															'descr' => stripslashes ( $row['descr'] ),
															'date' => $row['date'],
															'banned_from' => $row['banned_from']
														  );
			else $banned_info['name'][$row['ip']] = array (
															'name' => $row['ip'],
															'descr' => stripslashes ( $row['descr'] ),
															'date' => $row['date'],
															'banned_from' => $row['banned_from']
														  );

		}

	}
	set_vars ( "banned", $banned_info );
	$db->free ();
}

$is_logged = false;

require_once(DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php'));

if ( !in_array($mod, $admin_modules) AND !$config['allow_registration']) {

	$dle_login_hash = sha1(SECURE_AUTH_KEY . $_SERVER['HTTP_USER_AGENT']);

}

if( !$is_logged ) $member_id['user_group'] = 5;

if ((isset($banned_info['ip']) AND check_ip($banned_info['ip'])) OR ($is_logged AND $member_id['banned'] == "yes") OR $block_country) {
	echo "{\"error\":true, \"content\":\"banned\"}";
	die();
}

if ( $mod != 'controller' AND file_exists( DLEPlugins::Check(ENGINE_DIR . '/ajax/' . $mod . '.php') )) {

	include_once (DLEPlugins::Check(ENGINE_DIR . '/ajax/' . $mod . '.php'));

} else {

	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
	
}

?>