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
 File: registration.php
-----------------------------------------------------
 Use: AJAX check name
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

function check_name($name) {
	global $lang, $db, $banned_info, $relates_word, $config;

	$stop = '';

	if (dle_strlen($name) > 40 OR dle_strlen(trim($name)) < 3) {
            $stop .= $lang['reg_err_3'];
	}

	if (preg_match("/[\||\'|\<|\>|\[|\]|\%|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\}\+]/",$name)) {
            $stop .= $lang['reg_err_4'];
	}
	
	if (strpos( strtolower ($name) , '.php' ) !== false) {
            $stop .= $lang['reg_err_4'];
	}

	if (isset($banned_info['name']) AND is_array($banned_info['name']) AND count($banned_info['name']))
		foreach($banned_info['name'] as $banned){

			$banned['name'] = str_replace( '\*', '.*' ,  preg_quote(dle_strtolower($banned['name']), "#") );

			if ( $banned['name'] AND preg_match( "#^{$banned['name']}$#iu", dle_strtolower($name) ) ) {

				if ($banned['descr']) {
					$lang['reg_err_21']	= str_replace("{descr}", $lang['reg_err_22'], $lang['reg_err_21']);
					$lang['reg_err_21']	= str_replace("{descr}", $banned['descr'], $lang['reg_err_21']);
				} else $lang['reg_err_21']	= str_replace("{descr}", "", $lang['reg_err_21']);

				$stop .= $lang['reg_err_21'];
			}
	}
	
	$all_words = @file( ENGINE_DIR . '/data/wordfilter.db.php' );
	
	if( $all_words AND is_array($all_words) AND count( $all_words ) ) {
		
		foreach ( $all_words as $word_line ) {
			$word_arr = explode( "|", $word_line );
		
			$word_arr[1] = str_replace( "&#036;", "$", $word_arr[1] );
			$word_arr[1] = str_replace( "&#123;", "{", $word_arr[1] );
			$word_arr[1] = str_replace( "&#125;", "}", $word_arr[1] );
			
			if( $word_arr[4] ) {

				$register ="";

			} else $register ="i";

			$register .= "u";

			if( $word_arr[3] ) {

				$find_text = "#(^|\b|\s|\<br \/\>)" . preg_quote( $word_arr[1], "#" ) . "(\b|\s|!|\?|\.|,|$)#".$register;

			} else {

				$find_text = "#(" . preg_quote( $word_arr[1], "#" ) . ")#".$register;

			}
			
			if ( preg_match($find_text, $name) ) {

				$stop .= $lang['reg_err_4'];

			}

		}
		
	}
	
	if (!$stop) {

		$name = trim(dle_strtolower($name));
		
		$search_name=strtr($name, $relates_word);

		$db->query ("SELECT name FROM " . USERPREFIX . "_users WHERE LOWER(name) REGEXP '^{$search_name}$' OR name = '{$name}'");

        if ($db->num_rows() > 0) {
			$stop .= $lang['reg_err_20'];
		}
	}

	if (!$stop) return false; else return $stop;
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	echo $lang['sess_error'];
	die();
}

$name = isset($_POST['name']) ? (string)$_POST['name'] : "";
$name = strtr($name, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8')));
$name = trim($name,chr(0xC2).chr(0xA0));
$name = preg_replace('#\s+#i', ' ', $name);

$name = $db->safesql( htmlspecialchars( trim( $name ), ENT_QUOTES, 'UTF-8' ) );
$allow = check_name($name);

if (!$allow) {

	$return_array = array(
		'success' => true,
		'message' => $lang['reg_ok_ajax']
	);
	
	echo json_encode($return_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	
} else {

	$return_array = array(
		'success' => false,
		'message' => $allow
	);

	echo json_encode($return_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
