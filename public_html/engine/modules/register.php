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
 File: register.php
-----------------------------------------------------
 Use: registration of visitors
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$parse = new ParseFilter();
$parse->safe_mode = true;
$parse->allow_video = false;
$parse->allow_media = false;

$stopregistration = false;
$_IP = get_ip();

if( !isset($_REQUEST['step']) ) $_REQUEST['step'] = false;
if( !isset($config['key']) ) $config['key'] = '';

$canonical = $PHP_SELF."?do=register";

if( isset( $_REQUEST['doaction'] ) ) $doaction = $_REQUEST['doaction']; else $doaction = "";
$config['reg_group'] = intval( $config['reg_group'] ) ? intval( $config['reg_group'] ) : 4;

$parse->allow_url = $user_group[$config['reg_group']]['allow_url'];
$parse->allow_image = $user_group[$config['reg_group']]['allow_image'];

function check_reg($name, $email, $password1, $password2, $sec_code = 1, $sec_code_session = 1) {
	global $lang, $db, $banned_info, $relates_word, $config;
	$stop = "";

	if( $sec_code != $sec_code_session OR !$sec_code_session ) $stop .= "<li>".$lang['recaptcha_fail']."</li>";
	if( $password1 != $password2 ) $stop .= $lang['reg_err_1'];
	if( strlen( $password1 ) < 6 ) $stop .= $lang['reg_err_2'];
	if( strlen( $password1 ) > 72 ) $stop .= $lang['reg_err_2'];
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\%|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\}\+]/", $name ) ) $stop .= $lang['reg_err_4'];
	if( !$email OR strlen( $email ) > 40 OR !filter_var($email, FILTER_VALIDATE_EMAIL) ) $stop .= $lang['reg_err_6'];
	if (strpos( strtolower ($name) , '.php' ) !== false) $stop .= $lang['reg_err_4'];
	
	if( dle_strlen( $name ) > 40 OR dle_strlen(trim($name)) < 3) $stop .= $lang['reg_err_3'];

	if( isset($banned_info['name']) AND is_array($banned_info['name']) AND count( $banned_info['name'] ) ) foreach ( $banned_info['name'] as $banned ) {

		$banned['name'] = str_replace( '\*', '.*', preg_quote( dle_strtolower($banned['name']), "#" ) );

		if( $banned['name'] and preg_match( "#^{$banned['name']}$#iu", dle_strtolower($name) ) ) {

			if( $banned['descr'] ) {
				$lang['reg_err_21'] = str_replace( "{descr}", $lang['reg_err_22'], $lang['reg_err_21'] );
				$lang['reg_err_21'] = str_replace( "{descr}", $banned['descr'], $lang['reg_err_21'] );
			} else
				$lang['reg_err_21'] = str_replace( "{descr}", "", $lang['reg_err_21'] );

			$stop .= $lang['reg_err_21'];
		}
	}

	if( isset($banned_info['email']) AND is_array($banned_info['email']) AND count( $banned_info['email'] ) ) foreach ( $banned_info['email'] as $banned ) {

		$banned['email'] = str_replace( '\*', '.*', preg_quote( dle_strtolower($banned['email']), "#" ) );

		if( $banned['email'] AND preg_match( "#^{$banned['email']}$#iu", dle_strtolower($email) ) ) {

			if( $banned['descr'] ) {
				$lang['reg_err_23'] = str_replace( "{descr}", $lang['reg_err_22'], $lang['reg_err_23'] );
				$lang['reg_err_23'] = str_replace( "{descr}", $banned['descr'], $lang['reg_err_23'] );
			} else
				$lang['reg_err_23'] = str_replace( "{descr}", "", $lang['reg_err_23'] );

			$stop .= $lang['reg_err_23'];
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
	
	if( !$stop ) {
		$name = trim(dle_strtolower($name));
		$search_name = strtr( $name, $relates_word );

		$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE email = '{$email}' OR LOWER(name) REGEXP '^{$search_name}$' OR name = '{$name}'" );

		if( $row['count'] ) $stop .= $lang['reg_err_8'];
	}

	return $stop;

}

if( !$config['allow_registration'] ) {

	msgbox( $lang['all_info'], $lang['reg_err_9'] );
	$stopregistration = TRUE;

}

if( $config['auth_only_social'] AND !$stopregistration) {

	msgbox( $lang['all_info'], $lang['reg_err_41'] );
	$stopregistration = TRUE;

}

if ( $config['sec_addnews'] AND !$stopregistration ) {

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_spam_log WHERE ip = '{$_IP}'" );
	
	if( !isset($row['id']) ) $row['id'] = false;
	
	if ( !$row['id'] ) {

		$sfs = new StopSpam($config['spam_api_key'], $config['sec_addnews']);
		$args = array('ip' => $_IP);

		if ($sfs->is_spammer( $args )) {

			$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, date) VALUES ('{$_IP}','1', '{$_TIME}')" );

			msgbox( $lang['all_info'], $lang['reg_err_28'] );
			$stopregistration = TRUE;

		} else {

			$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, date) VALUES ('{$_IP}','0', '{$_TIME}')" );
		}

	} else {

		if ($row['is_spammer']) {

			msgbox( $lang['all_info'], $lang['reg_err_28'] );
			$stopregistration = TRUE;

		}

	}

}

if( $config['max_users'] > 0 AND !$stopregistration) {

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users" );

	if ( $row['count'] >= $config['max_users'] ) {
		msgbox( $lang['all_info'], $lang['reg_err_10'] );
		$stopregistration = TRUE;
	}

}

if( !$config['reg_multi_ip'] AND !$is_logged AND !$stopregistration) {

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE logged_ip = '{$_IP}'" );

	if ( $row['count'] ) {
		msgbox( $lang['all_info'], $lang['reg_err_26'] );
		$stopregistration = TRUE;
	}

}

if ( $is_logged AND !isset( $_POST['submit_val'] ) AND !$stopregistration ) {

	msgbox( $lang['all_info'], $lang['reg_err_27'] );
	$stopregistration = TRUE;
}

if( isset( $_POST['submit_reg'] ) AND !$stopregistration ) {

	if( $config['allow_sec_code'] ) {

		if ($config['allow_recaptcha']) {

			$sec_code = 1;
			$sec_code_session = false;

			if ( $_POST['g-recaptcha-response'] ) {

				$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);
		
				$resp = $reCaptcha->verifyResponse(get_ip(), $_POST['g-recaptcha-response'] );

			    if ( $resp != null && $resp->success ) {
					$sec_code = 1;
					$sec_code_session = 1;

			    }
			}

		} else {
			$sec_code = $_POST['sec_code'];
			$sec_code_session = ($_SESSION['sec_code_session'] != '') ? $_SESSION['sec_code_session'] : false;
		}

	} else {
		$sec_code = 1;
		$sec_code_session = 1;
	}

	$password1 = $_POST['password1'];
	$password2 = $_POST['password2'];
	
	$name = strtr($_POST['name'], array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8')));
	$name = trim($name,chr(0xC2).chr(0xA0));
	$name = preg_replace('#\s+#u', ' ', $name);
	
	$name = $db->safesql( $parse->process( htmlspecialchars( trim( $name ), ENT_QUOTES, 'UTF-8' ) ) );

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
	$email = $db->safesql( filter_var( trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['email'] ) ) ) ), FILTER_VALIDATE_EMAIL) );

	$reg_error = check_reg( $name, $email, $password1, $password2, $sec_code, $sec_code_session );

	if( $config['reg_question'] ) {

		if ( intval($_SESSION['question']) ) {

			$answer = $db->super_query("SELECT id, answer FROM " . PREFIX . "_question WHERE id='".intval($_SESSION['question'])."'");

			$answers = explode( "\n", $answer['answer'] );

			$pass_answer = false;

			$question_answer = trim(dle_strtolower($_POST['question_answer']));

			if( count($answers) AND $question_answer ) {
				foreach( $answers as $answer ){

					$answer = trim(dle_strtolower($answer));

					if( $answer AND $answer == $question_answer ) {
						$pass_answer	= true;
						break;
					}
				}
			}

			if( !$pass_answer ) $reg_error .= $lang['reg_err_25'];

		} else $reg_error .= $lang['reg_err_25'];

	}

	if ( $config['sec_addnews'] ) {

		$sfs = new StopSpam($config['spam_api_key'], $config['sec_addnews']);
		$args = array('email' => $email);

		if ($sfs->is_spammer( $args )) {

			$db->query( "UPDATE " . PREFIX . "_spam_log SET is_spammer='1', email='{$email}' WHERE ip = '{$_IP}'" );
			$stopregistration = TRUE;
			$reg_error .= $lang['reg_err_35'];

		} else {

			$db->query( "UPDATE " . PREFIX . "_spam_log SET email='{$email}' WHERE ip = '{$_IP}'" );

		}

	}
	
	$_SESSION['sec_code_session'] = false;
	$_SESSION['question'] = false;
			
	if( !$reg_error AND !$stopregistration ) {

		$stronghash = sha1(DBHOST . DBNAME . SECURE_AUTH_KEY);

		if( $config['registration_type'] ) {

			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email where name='reg_mail' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html'] );
			
			$row['template'] = stripslashes( $row['template'] );

			$idlink = rawurlencode( base64_encode( $name . "||" . $email . "||" . $password1 . "||" . sha1( $name . $email . $stronghash . $config['key'] ) ) );
			
			if (strpos($config['http_home_url'], "//") === 0) $slink = "https:".$config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
			else $slink = $config['http_home_url'];
			
			$row['template'] = str_replace( "{%username%}", $name, $row['template'] );
			$row['template'] = str_replace( "{%email%}", $email, $row['template'] );
			$row['template'] = str_replace( "{%validationlink%}", $slink . "index.php?do=register&doaction=validating&id=" . $idlink, $row['template'] );
			$row['template'] = str_replace( "{%password%}", $password1, $row['template'] );

			$mail->send( $email, $lang['reg_subj'], $row['template'] );

			if( $mail->send_error ) msgbox( $lang['all_info'], $mail->smtp_msg );
			else msgbox( $lang['reg_vhead'], $lang['reg_vtext'] );

			$stopregistration = TRUE;

		} else {

			$doaction = "validating";
			$_REQUEST['id'] = rawurlencode( base64_encode( $name . "||" . $email . "||" . $password1 . "||" . sha1( $name . $email . $stronghash . $config['key'] ) ) );
		}

	} else {
		msgbox( $lang['reg_err_11'], "<ul>" . $reg_error . "</ul>" );
	}

}

if( $doaction != "validating" AND !$stopregistration ) {

	if( isset($_POST['dle_rules_accept']) AND $_POST['dle_rules_accept'] == "yes" ) {

		$_SESSION['dle_rules_accept'] = 1;

	}

	if( $config['registration_rules'] and !isset($_SESSION['dle_rules_accept']) ) {

		$_GET['page'] = "dle-rules-page";
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/static.php'));

	} else {

		$tpl->load_template( 'registration.tpl' );

		$tpl->set( '[registration]', "" );
		$tpl->set( '[/registration]', "" );
		$tpl->set_block( "'\\[validation\\](.*?)\\[/validation\\]'si", "" );

		if( $vk_url ) {
			$tpl->set( '[vk]', "" );
			$tpl->set( '[/vk]', "" );
			$tpl->set( '{vk_url}', $vk_url );	
		} else {
			$tpl->set_block( "'\\[vk\\](.*?)\\[/vk\\]'si", "" );
			$tpl->set( '{vk_url}', '' );	
		}
		if( $odnoklassniki_url ) {
			$tpl->set( '[odnoklassniki]', "" );
			$tpl->set( '[/odnoklassniki]', "" );
			$tpl->set( '{odnoklassniki_url}', $odnoklassniki_url );
		} else {
			$tpl->set_block( "'\\[odnoklassniki\\](.*?)\\[/odnoklassniki\\]'si", "" );
			$tpl->set( '{odnoklassniki_url}', '' );	
		}
		if( $facebook_url ) {
			$tpl->set( '[facebook]', "" );
			$tpl->set( '[/facebook]', "" );
			$tpl->set( '{facebook_url}', $facebook_url );	
		} else {
			$tpl->set_block( "'\\[facebook\\](.*?)\\[/facebook\\]'si", "" );
			$tpl->set( '{facebook_url}', '' );	
		}
		if( $google_url ) {
			$tpl->set( '[google]', "" );
			$tpl->set( '[/google]', "" );
			$tpl->set( '{google_url}', $google_url );
		} else {
			$tpl->set_block( "'\\[google\\](.*?)\\[/google\\]'si", "" );
			$tpl->set( '{google_url}', '' );	
		}
		if( $mailru_url ) {
			$tpl->set( '[mailru]', "" );
			$tpl->set( '[/mailru]', "" );
			$tpl->set( '{mailru_url}', $mailru_url );	
		} else {
			$tpl->set_block( "'\\[mailru\\](.*?)\\[/mailru\\]'si", "" );
			$tpl->set( '{mailru_url}', '' );	
		}
		if( $yandex_url ) {
			$tpl->set( '[yandex]', "" );
			$tpl->set( '[/yandex]', "" );
			$tpl->set( '{yandex_url}', $yandex_url );
		} else {
			$tpl->set_block( "'\\[yandex\\](.*?)\\[/yandex\\]'si", "" );
			$tpl->set( '{yandex_url}', '' );
		}

		if( $config['reg_question'] ) {

			$tpl->set( '[question]', "" );
			$tpl->set( '[/question]', "" );

			$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
			$tpl->set( '{question}', htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, 'UTF-8' ) );

			$_SESSION['question'] = $question['id'];

		} else {

			$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
			$tpl->set( '{question}', "" );

		}

		if( $config['allow_sec_code'] ) {

			if ( $config['allow_recaptcha'] ) {

				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );
				
				$captcha_name = "g-recaptcha";
				$captcha_url = "https://www.google.com/recaptcha/api.js?hl={$lang['language_code']}";
				
				if( $config['allow_recaptcha'] == 3) {
					
					$captcha_name = "h-captcha";
					$captcha_url = "https://js.hcaptcha.com/1/api.js?hl={$lang['language_code']}";
				
				}

				if ($config['allow_recaptcha'] == 4) {

					$captcha_name = "cf-turnstile";
					$captcha_url = "https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha";
				}

				if( $config['allow_recaptcha'] == 2) {
						
					$tpl->set( '{recaptcha}', "");
					$tpl->copy_template .= "<script src=\"https://www.google.com/recaptcha/api.js?render={$config['recaptcha_public_key']}\" async defer></script>";
						
				} else {
					
					$tpl->set( '{recaptcha}', "<div class=\"{$captcha_name}\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\" data-language=\"{$lang['language_code']}\"></div><script src='{$captcha_url}' async defer></script>" );
					
				}
				
				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{reg_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$tpl->set( '{reg_code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

		} else {

			$tpl->set( '{reg_code}', "" );
			$tpl->set( '{recaptcha}', "" );
			$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
			$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
		}

		$tpl->copy_template = "<form  method=\"post\" name=\"registration\" id=\"registration\" action=\"\">\n" . $tpl->copy_template . "
<input name=\"submit_reg\" type=\"hidden\" id=\"submit_reg\" value=\"submit_reg\">
<input name=\"do\" type=\"hidden\" id=\"do\" value=\"register\">
</form>";

		if( $config['allow_recaptcha'] == 2) {
			
			$onload_scripts[] = <<<HTML
			
				$('#registration').submit(function(event) {
				
					event.preventDefault();
					
					if (!check_reg_daten()) {
						return false;
					}
				
					grecaptcha.execute('{$config['recaptcha_public_key']}', {action: 'user_register'}).then(function(token) {
						$('#registration').append('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
						$('#registration').off('submit');
						HTMLFormElement.prototype.submit.call(document.forms.registration);
					});
			
					return false;
				});
HTML;


		} else {
			
			$onload_scripts[] = <<<HTML
				$('#registration').submit(function() {
					if (!check_reg_daten()) {
						return false;
					} else {
						return true;
					}
				});
HTML;
			
		}


		$tpl->copy_template .= <<<HTML
<script>
<!--
function check_reg_daten () {

	if(document.forms.registration.name.value == '') {

		DLEPush.error('{$lang['reg_err_30']}');return false;

	}

	if(document.forms.registration.password1.value.length < 6) {

		DLEPush.error('{$lang['reg_err_31']}');return false;

	}

	if(document.forms.registration.password1.value != document.forms.registration.password2.value) {

		DLEPush.error('{$lang['reg_err_32']}');return false;

	}

	if(document.forms.registration.email.value == '') {

		DLEPush.error('{$lang['reg_err_33']}');return false;

	}

	return true;

};
//-->
</script>
HTML;
		$tpl->compile( 'content' );
		$tpl->clear();

	}

}

if( isset( $_POST['submit_val'] ) AND !$stopregistration ) {

	$stop = '';
	$fullname = $db->safesql( $parse->process( $_POST['fullname'] ) );
	$land = $db->safesql( $parse->process( $_POST['land'] ) );

	$info = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['info'] ), false ) );

	$user_arr = explode( "||", base64_decode( @rawurldecode( $_POST['id'] ) ) );

	if( $user_arr[0] == "" OR  $user_arr[2]== "" ) die("Hacking attempt!");

	$user = $db->safesql( trim( $user_arr[0] ) );
	$email = $db->safesql( trim( $user_arr[1] ) );
	$pass = $user_arr[2];
	$stronghash = sha1(DBHOST . DBNAME . SECURE_AUTH_KEY);

	if( sha1( $user . $email . $stronghash . $config['key'] ) != $user_arr[3] ) die( 'ID not valid!' );

	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\{\+]/", $user ) ) die( 'USER not valid!' );

	$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE name = '{$user}'" );

	if( !$row['user_id'] ) die("Access Denied!");
	
	if( !password_verify($pass, $row['password']) ) die("Access Denied!");

	$db->free();

	if( intval( $user_group[$member_id['user_group']]['max_info'] ) > 0 and dle_strlen( $info ) > $user_group[$member_id['user_group']]['max_info'] ) $stop .= $lang['reg_err_14'];
	if( dle_strlen( $fullname ) > 100 ) $stop .= $lang['reg_err_15'];
	if( dle_strlen( $land ) > 100 ) $stop .= $lang['reg_err_16'];

	if( $parse->not_allowed_tags ) $stop .= $lang['news_err_34'];
	if( $parse->not_allowed_text ) $stop .= $lang['news_err_38'];

	if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $fullname ) ) {

		$stop .= $lang['news_err_35'];
	}

	if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $land ) ) {

		$stop .= $lang['news_err_36'];
	}
	
	$image = $_FILES['image']['tmp_name'];
	$image_size = $_FILES['image']['size'];
	$file_parts = pathinfo( $_FILES['image']['name'] );

	if( !$stop AND is_uploaded_file( $image ) ) {

		if( intval( $user_group[$config['reg_group']]['max_foto'] ) > 0 ) {

			if( !$config['avatar_size'] OR $image_size < ($config['avatar_size'] * 1024) ) {

				$driver = DLEFiles::getDefaultStorage();
				$config['avatar_remote'] = intval($config['avatar_remote']);
				if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];
					
				DLEFiles::init( $driver, $config['local_on_fail'] );
				$thumb = new thumbnail( $_FILES['image']['tmp_name'] );
			
				if ( !$thumb->error) {
					
					if( !$config['tinypng_avatar'] ) {
						$thumb->tinypng = false;
					}
					
					$thumb->tinypng_resize = true;
					$thumb->size_auto( $user_group[$config['reg_group']]['max_foto'] );
		
					$foto_name = $thumb->save( "fotos/foto_" . $row['user_id'] . '_' . $_TIME . "." . $file_parts['extension'] );
					
					if ( $foto_name AND !$thumb->error) {
						
						if ( $driver AND !DLEFiles::$remote_error ) {
							
							$foto_name = $db->safesql( DLEFiles::GetBaseURL() . "fotos/" . $foto_name );
							
						} else {
							
							if (strpos($config['http_home_url'], "//") === 0) $avatar_url = $config['http_home_url'];
							elseif (strpos($config['http_home_url'], "/") === 0) $avatar_url = "//".$_SERVER['HTTP_HOST'].$config['http_home_url'];
							else $avatar_url = $config['http_home_url'];
							
							$avatar_url = str_ireplace("https:", "", $avatar_url);
							$avatar_url = str_ireplace("http:", "", $avatar_url);
							
							$foto_name = $db->safesql( $avatar_url . "uploads/fotos/" . $foto_name );
							
						}
						
						$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$foto_name}' WHERE user_id = '{$row['user_id']}'" );

					} else $stop .= $thumb->error;
					
				} else $stop .= $thumb->error;
				
			} else $stop .= str_replace("{size}", $config['avatar_size'], $lang['news_err_16']);
			
		} else $stop .= $lang['news_err_32'];

	}
	
	if( !$stop) {
		$xfieldsaction = "init";
		$xfieldsadd = true;
		$xfieldsid = "";
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/userfields.php'));
		$filecontents = array ();
		$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );

		if( is_array($postedxfields) AND count( $postedxfields ) ) {
			foreach ( $postedxfields as $xfielddataname => $xfielddatavalue ) {

				if( !trim($xfielddatavalue) ) {
					continue;
				}

				$xfielddatavalue = $db->safesql($xfielddatavalue);

				$xfielddataname = $db->safesql( str_replace( $not_allow_symbol, '', $xfielddataname) );

				$xfielddataname = str_replace( "|", "&#124;", $xfielddataname );
				$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
				$filecontents[] = "$xfielddataname|$xfielddatavalue";
			}

			$filecontents = implode( "||", $filecontents );
		} else
			$filecontents = '';
	}
	
	if( $stop ) {
		
		msgbox( $lang['reg_err_18'], $stop );
		
	} else {

		$db->query( "UPDATE " . USERPREFIX . "_users SET fullname='{$fullname}', info='{$info}', land='{$land}', xfields='{$filecontents}' WHERE user_id='{$row['user_id']}'" );

		msgbox( $lang['reg_ok'], $lang['reg_ok_1'] );

		header("HTTP/1.0 301 Moved Permanently");
		header('Location: ' . $config['http_home_url'] );
		die();

	}
}

if( $doaction == "validating" AND !$stopregistration AND !isset($_POST['submit_val']) ) {

	$user_arr = explode( "||", base64_decode( @rawurldecode( trim($_REQUEST['id']) ) ) );

	$regpassword = $user_arr[2];

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );	
	$email = $db->safesql(filter_var(trim(str_replace($not_allow_symbol, '', strip_tags(stripslashes($user_arr[1])))), FILTER_VALIDATE_EMAIL));

	$stronghash = sha1(DBHOST . DBNAME . SECURE_AUTH_KEY);
	
	$name = strtr($user_arr[0], array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8')));
	$name = trim($name,chr(0xC2).chr(0xA0));
	$name = preg_replace('#\s+#u', ' ', $name);
	
	$name = $db->safesql( htmlspecialchars( $parse->process( trim($name) ), ENT_QUOTES, 'UTF-8' ) );
	
	if( sha1( $name . $email . $stronghash . $config['key'] ) != $user_arr[3] ) die( 'ID not valid!' );
	
	$reg_error = check_reg( $name, $email, $regpassword, $regpassword );

	$regpassword = $db->safesql( password_hash($regpassword, PASSWORD_DEFAULT) );
	
	if( !$regpassword ) {
		die("PHP extension Crypt must be loaded for password_hash to function");
	}

	if( $reg_error ) {
		
		msgbox( $lang['reg_err_11'], $reg_error );
		$stopregistration = TRUE;
		
	} else {

		if( ($_REQUEST['step'] != 2) and $config['registration_type'] ) {
			
			$stopregistration = TRUE;
			header("HTTP/1.1 403 Forbidden");
			header('Location: ' . $config['http_home_url'] . "index.php?do=register&doaction=validating&step=2&id=" . rawurlencode($_REQUEST['id']) );
			die();

		} else {

			$add_time = time();
			$_IP = get_ip();
			
			if( intval( $config['reg_group'] ) < 3 ) $config['reg_group'] = 4;
				
			$hash = md5( random_bytes(32) );
			
			$db->query( "INSERT INTO " . USERPREFIX . "_users (name, password, email, reg_date, lastdate, user_group, info, signature, favorites, xfields, logged_ip, hash) VALUES ('{$name}', '{$regpassword}', '{$email}', '{$add_time}', '{$add_time}', '{$config['reg_group']}', '', '', '', '', '{$_IP}', '{$hash}')" );
			$id = $db->insert_id();

			set_cookie( "dle_user_id", $id, 365 );
			set_cookie( "dle_password", md5($regpassword), 365 );
			
			if( $config['log_hash'] ) {
				set_cookie( "dle_hash", $hash, 365 );
			}
			
			$_SESSION['dle_user_id'] = $id;
			$_SESSION['dle_password'] = md5($regpassword);

		}

	}

}

if( $doaction == "validating" AND !$stopregistration ) {

	$tpl->load_template( 'registration.tpl' );

	$tpl->set( '[validation]', "" );
	$tpl->set( '[/validation]', "" );
	$tpl->set_block( "'\\[registration\\].*?\\[/registration\\]'si", "" );

	if( $vk_url ) {
		$tpl->set( '[vk]', "" );
		$tpl->set( '[/vk]', "" );
		$tpl->set( '{vk_url}', $vk_url );	
	} else {
		$tpl->set_block( "'\\[vk\\](.*?)\\[/vk\\]'si", "" );
		$tpl->set( '{vk_url}', '' );	
	}
	if( $odnoklassniki_url ) {
		$tpl->set( '[odnoklassniki]', "" );
		$tpl->set( '[/odnoklassniki]', "" );
		$tpl->set( '{odnoklassniki_url}', $odnoklassniki_url );
	} else {
		$tpl->set_block( "'\\[odnoklassniki\\](.*?)\\[/odnoklassniki\\]'si", "" );
		$tpl->set( '{odnoklassniki_url}', '' );	
	}
	if( $facebook_url ) {
		$tpl->set( '[facebook]', "" );
		$tpl->set( '[/facebook]', "" );
		$tpl->set( '{facebook_url}', $facebook_url );	
	} else {
		$tpl->set_block( "'\\[facebook\\](.*?)\\[/facebook\\]'si", "" );
		$tpl->set( '{facebook_url}', '' );	
	}
	if( $google_url ) {
		$tpl->set( '[google]', "" );
		$tpl->set( '[/google]', "" );
		$tpl->set( '{google_url}', $google_url );
	} else {
		$tpl->set_block( "'\\[google\\](.*?)\\[/google\\]'si", "" );
		$tpl->set( '{google_url}', '' );	
	}
	if( $mailru_url ) {
		$tpl->set( '[mailru]', "" );
		$tpl->set( '[/mailru]', "" );
		$tpl->set( '{mailru_url}', $mailru_url );	
	} else {
		$tpl->set_block( "'\\[mailru\\](.*?)\\[/mailru\\]'si", "" );
		$tpl->set( '{mailru_url}', '' );	
	}
	if( $yandex_url ) {
		$tpl->set( '[yandex]', "" );
		$tpl->set( '[/yandex]', "" );
		$tpl->set( '{yandex_url}', $yandex_url );
	} else {
		$tpl->set_block( "'\\[yandex\\](.*?)\\[/yandex\\]'si", "" );
		$tpl->set( '{yandex_url}', '' );
	}

	$xfieldsaction = "list";
	$xfieldsadd = true;
	include (DLEPlugins::Check(ENGINE_DIR . '/inc/userfields.php'));
	$tpl->set( '{xfields}', $output );

	if ( count( $xfieldinput ) ) {
		foreach ( $xfieldinput as $key => $value ) {
			$tpl->copy_template = str_replace( "[xfinput_{$key}]", $value, $tpl->copy_template );
		}		
	}	
	
	$_REQUEST['id'] = htmlspecialchars( $_REQUEST['id'], ENT_QUOTES, 'UTF-8' );

	$tpl->copy_template = "<form  method=\"post\" name=\"registration\" enctype=\"multipart/form-data\" action=\"\">\n" . $tpl->copy_template . "
<input name=\"submit_val\" type=\"hidden\" id=\"submit_val\" value=\"submit_val\" />
<input name=\"do\" type=\"hidden\" id=\"do\" value=\"register\" />
<input name=\"doaction\" type=\"hidden\" id=\"doaction\" value=\"validating\" />
<input name=\"id\" type=\"hidden\" id=\"id\" value=\"{$_REQUEST['id']}\" />
</form>";

	$tpl->compile( 'content' );
	$tpl->clear();
}

?>