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
 File: pm.php
-----------------------------------------------------
 Use: PM
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$is_logged ) {
	echo "{\"error\":\" {$lang['pm_err_13']}\"}";
	die();
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	echo "{\"error\":\" {$lang['pm_err_15']}\"}";
	die();
}

$tpl = new dle_template();
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
define('TEMPLATE_DIR', $tpl->dir);

$allowed_tags = array('div[align|style|class|data-commenttime|data-commentuser|contenteditable]', 'span[style|class|data-userurl|data-username|contenteditable]', 'p[align|style|class]', 'pre[class]', 'code', 'br', 'strong', 'em', 'ul', 'li', 'ol', 'b', 'u', 'i', 's', 'hr');

if( $user_group[$member_id['user_group']]['allow_url'] ) $allowed_tags[] = 'a[href|target|style|class]';
if( $user_group[$member_id['user_group']]['allow_image'] ) $allowed_tags[] = 'img[style|class|src]';

$parse = new ParseFilter($allowed_tags);
	
$parse->safe_mode = true;
$parse->remove_html = false;
$parse->allow_video = false;
$parse->allow_media = false;
$parse->disable_leech = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
$comments_mobile_editor = false;

$_POST['action'] = isset($_POST['action']) ? $_POST['action'] : '';

if (isset($_GET['action']) and $_GET['action'] == "del_pm") {
	$message_id = isset($_GET['message_id']) ? intval($_GET['message_id']) : 0;
	$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

	if(!$message_id OR !$conversation_id) {
		echo "{\"error\": \"{$lang['pm_err_6']}\"}";
		die();
	}
	
	$row = $db->super_query("SELECT m.id FROM " . USERPREFIX . "_conversations_messages m JOIN " . USERPREFIX . "_conversation_users cu ON m.conversation_id = cu.conversation_id WHERE m.conversation_id = '{$conversation_id}' AND cu.user_id ='{$member_id['user_id']}' ORDER BY m.id ASC LIMIT 1" );

	if( !isset($row['id']) ) {

		echo "{\"error\": \"{$lang['pm_err_15']}\"}";
		die();

	} elseif($row['id'] == $message_id ){

		echo "{\"error\": \"{$lang['pm_err_16']}\"}";
		die();

	}
	
	$row = $db->super_query("SELECT m.id, m.conversation_id FROM " . USERPREFIX . "_conversations_messages m WHERE m.id = '{$message_id}' AND m.sender_id = '{$member_id['user_id']}'");
	
	if (isset($row['id']) AND $row['id'] ) {
		$db->query( "DELETE FROM " . USERPREFIX . "_conversations_messages WHERE id = '{$row['id']}'" );

		$row = $db->super_query("SELECT m.created_at FROM " . USERPREFIX . "_conversations_messages m WHERE m.conversation_id = '{$row['conversation_id']}' ORDER BY m.id DESC LIMIT 1");
		
		if (isset($row['created_at']) and $row['created_at']) {
			$db->query("UPDATE " . USERPREFIX . "_conversations SET updated_at='{$row['created_at']}' WHERE id='{$conversation_id}'");
		}

		die("{\"success\": 1}");
	} else {
		echo "{\"error\": \"{$lang['pm_err_15']}\"}";
		die();
	}

}

if ($_POST['action'] == "send_pm") {

	if(!$user_group[$member_id['user_group']]['allow_pm'] ) {
		echo "{\"error\":\" {$lang['pm_err_1']}\"}";
		die();
	}

	$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;

	if( $user_group[$member_id['user_group']]['max_pm_day'] ) {
	
		$this_time = $_TIME - 86400;
		$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='1'" );
	
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX ."_sendlog WHERE user = '" . $db->safesql($member_id['name']) . "' AND flag='1'");
	
		if( $row['count'] >=  $user_group[$member_id['user_group']]['max_pm_day'] ) {
			$lang['pm_err_10'] = str_replace('{max}', $user_group[$member_id['user_group']]['max_pm_day'], $lang['pm_err_10']);
			echo "{\"error\":\" {$lang['pm_err_10']}\"}";
			die();
		}
	}

	$name = isset($_POST['name']) ? $db->safesql( htmlspecialchars(strip_tags( trim( $_POST['name'] ) ), ENT_QUOTES, 'UTF-8' ) ) : '';
	$subj = isset($_POST['subj']) ?  htmlspecialchars(strip_tags( trim( $_POST['subj'] ) ), ENT_QUOTES, 'UTF-8' ) : '';
	
	$parse->wysiwyg = true;
	$comments = isset($_POST['comments']) ? $parse->BB_Parse($parse->process(trim($_POST['comments']))) : '';
	$preview = false;

	if ($conversation_id) {

		$row = $db->super_query("SELECT c.id, c.subject, c.sender_id, c.recipient_id FROM " . USERPREFIX . "_conversations c JOIN " . USERPREFIX . "_conversation_users cu ON c.id = cu.conversation_id WHERE c.id='{$conversation_id}' AND cu.user_id = '{$member_id['user_id']}'");

		if (isset($row['id']) and $row['id']) {

			$conversation_id = $row['id'];

			if($member_id['user_id'] == $row['sender_id'] ) {
				$recipient_id = $row['recipient_id'];
			} else {
				$recipient_id = $row['sender_id'];
			}

			$subj = $row['subject'];

		} else {
			echo "{\"error\":\" {$lang['pm_err_6']}\"}";
			die();
		}

	} else{
		$conversation_id = 0;
		$recipient_id = 0;
	}

	if( dle_strlen($comments ) > 65000 ) $comments = "";
	
	$stop = array();
	
	if(!$comments) $stop[] = $lang['pm_err_2'];
	
	if (!$conversation_id AND !$name) $stop[] = $lang['pm_err_2'];

	if( !$conversation_id AND !$subj ) $stop[] = $lang['pm_err_2'];
	
	if( !$conversation_id AND dle_strlen( $subj ) > 255 ) {
		$stop[] = $lang['pm_err_3'];
	}
	
	if(!$conversation_id AND dle_strlen( $name ) > 40 ) {
		$stop[] = $lang['reg_err_3'];
	}
	
	if( $parse->not_allowed_tags ) {

		$stop[] = $lang['news_err_33'];
	}

	if( $parse->not_allowed_text ) {

		$stop[] = $lang['news_err_37'];
	}
	
	if( !$conversation_id AND $user_group[$member_id['user_group']]['captcha_pm'] ) {

		if ($config['allow_recaptcha']) {

			$sec_code = 1;
			$sec_code_session = false;
			$captcha_response = '';

			if (isset($_POST['g_recaptcha_response']) AND $_POST['g_recaptcha_response']) $captcha_response = $_POST['g_recaptcha_response'];
			if (isset($_POST['g-recaptcha-response']) AND $_POST['g-recaptcha-response']) $captcha_response = $_POST['g-recaptcha-response'];

			if ($captcha_response) {
			
					$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);

					$resp = $reCaptcha->verifyResponse(get_ip(), $captcha_response );
			
			        if ($resp === null OR !$resp->success) {

						$stop[] = $lang['recaptcha_fail'];

			        }

			} else $stop[] = $lang['recaptcha_fail'];

		} elseif(!isset( $_REQUEST['sec_code']) OR $_REQUEST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] OR !$_REQUEST['sec_code'] ) $stop[] = $lang['news_err_30'];
	
	}

	if( !$conversation_id AND $user_group[$member_id['user_group']]['pm_question'] ) {
	
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

			if( !$pass_answer ) $stop[] = $lang['reg_err_24'];

		} else $stop[] = $lang['reg_err_24'];
	
	}
	
	if( !$conversation_id AND !count($stop) AND $user_group[$member_id['user_group']]['spampmfilter'] ) {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_spam_log WHERE ip = '{$_IP}'" );
		$member_id['email'] = $db->safesql($member_id['email']);

		if (!isset($row['id']) OR !$row['id'] OR !$row['email'] ) {
	
			$sfs = new StopSpam($config['spam_api_key'], $user_group[$member_id['user_group']]['spampmfilter'] );
			$args = array('ip' => $_IP, 'email' => $member_id['email']);
	
			if ($sfs->is_spammer( $args )) {
	
				if ( !isset($row['id']) OR !$row['id'] ) {
					$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','1', '{$member_id['email']}', '{$_TIME}')" );
				} else {
					$db->query( "UPDATE " . PREFIX . "_spam_log SET is_spammer='1', email='{$member_id['email']}' WHERE id='{$row['id']}'" );
				}

				$stop[] = $lang['reg_err_34'];
	
			} else {
				
				if ( !isset($row['id']) OR !$row['id'] ) {
					$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','0', '{$member_id['email']}', '{$_TIME}')" );
				} else {
					$db->query( "UPDATE " . PREFIX . "_spam_log SET email='{$member_id['email']}' WHERE id='{$row['id']}'" );
				}
				
			}
		
		} else {
	
			if ($row['is_spammer']) {

				$stop[] = $lang['reg_err_34'];
			
			}
	
		}
	
	}
	
	if( !count($stop) ) {
		
		if( $conversation_id ) {
			$row = $db->super_query("SELECT email, name, user_id, pm_all, user_group, banned FROM " . USERPREFIX . "_users WHERE user_id = '{$recipient_id}' LIMIT 1");
		} else {
			$row = $db->super_query("SELECT email, name, user_id, pm_all, user_group, banned FROM " . USERPREFIX . "_users WHERE name = '{$name}' LIMIT 1");
		}
		
		if( !isset($row['user_id']) OR !$row['user_id'] ) {
			echo "{\"error\":\" {$lang['pm_err_4']}\"}";
			die();
		}
		
		if(!$user_group[$row['user_group']]['allow_pm'] ) {
			echo "{\"error\":\" {$lang['pm_err_11']}\"}";
			die();
		}

		if( $row['banned'] ) {
			echo "{\"error\":\" {$lang['pm_err_14']}\"}";
			die();
		}
	}
	
	if( !count($stop) ) {

		$db->query( "SELECT id FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' AND user_from='". $db->safesql($member_id['name']) ."'" );
		if( $db->num_rows() ) $stop[] = $lang['pm_ignored'];
		$db->free();

	}
	
	if( !$conversation_id AND !count($stop) AND ($user_group[$row['user_group']]['max_pm'] AND $row['pm_all'] >= $user_group[$row['user_group']]['max_pm']) ) {
		$stop[] = $lang['pm_err_8'];
	}
	
	if( !count($stop) ) {
		
		unset($_SESSION['question']);
		unset($_SESSION['sec_code_session']);
		
		$safe_comments = $db->safesql($comments);
		$safe_subj = $db->safesql($subj);

		if( $conversation_id ) {

			$preview = true;
			$db->query("UPDATE " . USERPREFIX . "_conversations SET updated_at='{$_TIME}' WHERE id='{$conversation_id}'" );
			$db->query("INSERT INTO " . USERPREFIX . "_conversation_users (user_id, conversation_id) values ('{$member_id['user_id']}', '{$conversation_id}'), ('{$row['user_id']}', '{$conversation_id}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)");
			$db->query("INSERT INTO " . USERPREFIX . "_conversations_messages (conversation_id, sender_id, content, created_at) values ('{$conversation_id}', '{$member_id['user_id']}', '{$safe_comments}', '{$_TIME}')");
			$message_id = $db->insert_id();
			$db->query("INSERT INTO " . USERPREFIX . "_conversation_reads (user_id, conversation_id, last_read_at) values ('{$member_id['user_id']}', '{$conversation_id}', '{$_TIME}') ON DUPLICATE KEY UPDATE last_read_at='{$_TIME}'");

		} else {

			$db->query("INSERT INTO " . USERPREFIX . "_conversations (subject, created_at, updated_at, sender_id, recipient_id) values ('{$safe_subj}', '{$_TIME}', '{$_TIME}', '{$member_id['user_id']}', '{$row['user_id']}')");
			$conversation_id = $db->insert_id();
			$db->query("INSERT INTO " . USERPREFIX . "_conversation_users (user_id, conversation_id) values ('{$member_id['user_id']}', '{$conversation_id}'), ('{$row['user_id']}', '{$conversation_id}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)");
			$db->query("INSERT INTO " . USERPREFIX . "_conversations_messages (conversation_id, sender_id, content, created_at) values ('{$conversation_id}', '{$member_id['user_id']}', '{$safe_comments}', '{$_TIME}')");
			
			if ($member_id['user_id'] != $row['user_id']) {
				$db->query("INSERT INTO " . USERPREFIX . "_conversation_reads (user_id, conversation_id, last_read_at) values ('{$member_id['user_id']}', '{$conversation_id}', '{$_TIME}') ON DUPLICATE KEY UPDATE last_read_at='{$_TIME}'");
			}

		}
		
		if ($user_group[$member_id['user_group']]['max_pm_day']) {
			$db->query("INSERT INTO " . PREFIX ."_sendlog (user, date, flag) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '1')");
		}

		$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$row['user_id']}'");
		$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$row['user_id']}'");

		if ($member_id['user_id'] != $row['user_id']) {
			$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}'");
			$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$member_id['user_id']}'");
		}

		if( $config['mail_pm'] ) {
			
			$mail_template = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='pm' LIMIT 1" );
			$mail = new dle_mail( $config, $mail_template['use_html'] );
			
			$slink = $config['http_home_url'] . "index.php?do=pm&doaction=readpm&pmid=" . $conversation_id;
			
			$mail_template['template'] = stripslashes( $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%username%}", $row['name'], $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $_TIME ), $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%fromusername%}", $member_id['name'], $mail_template['template'] );
			
			if(!$mail_template['use_html']) {
				$subj = str_replace('&quot;', '"', $subj);
				$subj = str_replace('&#039;',"'", $subj);
				$subj = str_replace('&amp;', "&", $subj);
			}

			$mail_template['template'] = str_replace( "{%title%}", strip_tags( $subj ), $mail_template['template'] );

			$mail_template['template'] = str_replace( "{%url%}", $slink, $mail_template['template'] );
			
			$body = stripslashes( $comments );

			$body = remove_quotes_from_text($body);
			
			$body = str_replace( "<br />", "\n", $body );
			$body = str_replace( "<br>", "\n", $body );
			$body = str_replace( "</p>", "</p>\n", $body );
			$body = trim(strip_tags( $body ));
			$body = preg_replace("/(\n{2})\n+/", "$1", $body);
			
			if( $mail_template['use_html'] ) {
				$body = str_replace("\n", "<br>", $body );
			} else{
				$body = str_replace('&amp;', "&", $body);
			}
			
			$mail_template['template'] = str_replace( "{%text%}", $body, $mail_template['template'] );
			
			$mail->send( $row['email'], $lang['mail_pm'], $mail_template['template'] );
		
		}

		if ( !$preview ) {

			msgbox($lang['all_info'], $lang['pm_sendok'] . " <a href=\"?do=pm&amp;doaction=newpm\">" . $lang['pm_noch'] . "</a> " . $lang['pm_or'] . " <a href=\"?do=pm\">" . $lang['pm_main'] . "</a>");

			$tpl->result['info'] = str_replace('{THEME}', $_ROOT_DLE_URL . 'templates/' . $config['skin'], $tpl->result['info']);

			echo json_encode(array("success" => $lang['pm_sendok'], "text" => $tpl->result['info']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		}

	} else {
		echo "{\"error\": \"".implode('<br><br>', $stop)."\"}";
	}

	if(!$preview) die();
}

if (isset($_GET['action']) AND $_GET['action'] == "show_send") {

	$name = htmlspecialchars(strip_tags( trim( urldecode($_GET['name'] ) ) ), ENT_QUOTES, 'UTF-8' );
	
	if(!$user_group[$member_id['user_group']]['allow_pm'] ) {
		echo "<div id='dlesendpmpopup' title='{$lang['send_pm']} {$name}' style='display:none'><script>DLEPush.error ( '{$lang['pm_err_1']}' );$('#dlesendpmpopup').remove();</script></div>";
		die();
	}
	
	if( $user_group[$member_id['user_group']]['max_pm_day'] ) {
	
		$this_time = time() - 86400;
		$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='1'" );
	
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX ."_sendlog WHERE user = '" . $db->safesql($member_id['name']) . "' AND flag='1'");
	
		if( $row['count'] >=  $user_group[$member_id['user_group']]['max_pm_day'] ) {
			$lang['pm_err_10'] = str_replace('{max}', $user_group[$member_id['user_group']]['max_pm_day'], $lang['pm_err_10']);
			echo "<div id='dlesendpmpopup' title='{$lang['send_pm']} {$name}' style='display:none'><script>DLEPush.error ( '{$lang['pm_err_10']}' );$('#dlesendpmpopup').remove();</script></div>";
			die();
		}
	}

	$is_pm_ajax_mode = true;
	include_once(DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php'));

	$response = <<<HTML
	<input type="hidden" name="pm_name" id="pm_name" value="{$name}">
	<div style="padding-bottom:5px;"><input type="text" name="pm_subj" id="pm_subj" class="quick-edit-text classic" placeholder="{$lang['send_pm_1']}"></div>
	<div class="{$box_class}{$dark_theme}">
		<textarea name="pm_text" id="pm_text" style="width:100%;height:{$area_height}px;" class="ajaxwysiwygeditor"></textarea>
	</div>
	<script>	
	setTimeout(function() {
		{$editor_scrips}
	}, 100);
	</script>
HTML;

	if( $user_group[$member_id['user_group']]['pm_question'] ) {
		$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
	
		$_SESSION['question'] = $question['id'];
	
		$question = htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, 'UTF-8' );
		
		$response .= <<<HTML
	<div id="dle-question" style="padding-top:5px;">{$question}</div>
	<div><input type="text" name="pm_question_answer" id="pm_question_answer" placeholder="{$lang['question_hint']}" class="quick-edit-text classic"></div>
HTML;
	
	}

	if( $user_group[$member_id['user_group']]['captcha_pm'] ) {
	
		if ( $config['allow_recaptcha'] ) {

			if( $config['allow_recaptcha'] == 2) {
				
				$response .= <<<HTML
		<input type="hidden" name="pm-recaptcha-response" id="pm-recaptcha-response" data-key="{$config['recaptcha_public_key']}" value="">
		<script>
		if ( typeof grecaptcha === "undefined"  ) {
		
			$.getScript( "https://www.google.com/recaptcha/api.js?render={$config['recaptcha_public_key']}");
	
		}
		</script>
HTML;

			} elseif($config['allow_recaptcha'] == 3 )  {
				
				$response .= <<<HTML
		<div id="dle_pm_recaptcha" style="padding-top:5px;height:78px;"></div>
		<script>
		<!--
		var recaptcha_widget;
		
		if ( typeof hcaptcha === "undefined"  ) {
		
			$.getScript( "https://js.hcaptcha.com/1/api.js?hl={$lang['language_code']}&render=explicit").done(function () {
			
				var setIntervalID = setInterval(function () {
					if (window.hcaptcha) {
						clearInterval(setIntervalID);
						recaptcha_widget = hcaptcha.render('dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
					};
				}, 300);
			});
	
		} else {
			recaptcha_widget = hcaptcha.render('dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
		}
		//-->
		</script>
HTML;
			} elseif ($config['allow_recaptcha'] == 4) {

				$response .= <<<HTML
		<div id="dle_pm_recaptcha" style="padding-top:5px;height:78px;"></div>
		<script>
		<!--
		var recaptcha_widget;
		
		if ( typeof turnstile === "undefined"  ) {
		
			$.getScript( "https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha&render=explicit").done(function () {
			
				var setIntervalID = setInterval(function () {
					if (window.turnstile) {
						clearInterval(setIntervalID);
						recaptcha_widget = turnstile.render('#dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}', 'language':'{$lang['language_code']}'});
					};
				}, 300);
			});
	
		} else {
			recaptcha_widget = turnstile.render('#dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}', 'language':'{$lang['language_code']}'});
		}
		//-->
		</script>
HTML;
			} else {
	
				$response .= <<<HTML
		<div id="dle_pm_recaptcha" style="padding-top:5px;height:78px;"></div>
		<script>
		<!--
		var recaptcha_widget;
		
		if ( typeof grecaptcha === "undefined"  ) {
		
			$.getScript( "https://www.google.com/recaptcha/api.js?hl={$lang['language_code']}&render=explicit").done(function () {
			
				var setIntervalID = setInterval(function () {
					if (window.grecaptcha) {
						clearInterval(setIntervalID);
						recaptcha_widget = grecaptcha.render('dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
					};
				}, 300);
			});
	
		} else {
			recaptcha_widget = grecaptcha.render('dle_pm_recaptcha', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
		}
		//-->
		</script>
HTML;
	
			}
			
		} else {
	
			$response .= <<<HTML
	<div style="padding-top:5px;" class="dle-captcha"><a onclick="reload_pm(); return false;" title="{$lang['reload_code']}" href="#"><span id="dle-captcha_pm"><img src="{$_ROOT_DLE_URL}engine/modules/antibot/antibot.php" alt="{$lang['reload_code']}" width="160" height="80"></span></a>
	<input class="ui-widget-content ui-corner-all sec-code" type="text" name="sec_code" id="sec_code_pm" placeholder="{$lang['captcha_hint']}">
	</div>
	<script>
	<!--
	function reload_pm () {
	
		var rndval = new Date().getTime(); 
	
		document.getElementById('dle-captcha_pm').innerHTML = '<img src="{$_ROOT_DLE_URL}engine/modules/antibot/antibot.php?rndval=' + rndval + '" width="160" height="80" alt="" />';
		document.getElementById('sec_code_pm').value = '';
	};
	//-->
	</script>
HTML;
	
		}
	}
	

	echo "<div id=\"dlesendpmpopup\" title=\"{$lang['send_pm']} {$name}\" style=\"display:none\"><form  method=\"post\" name=\"dle-send-pm\" id=\"dle-send-pm\">{$response}</form></div>";
	die();

}

if (!$user_group[$member_id['user_group']]['allow_pm']) {
	echo "{\"error\":\" {$lang['pm_err_1']}\"}";
	die();
}

if ($_POST['action'] == "save_edit_pm") {
	
	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

	$parse->wysiwyg = true;
	$message = isset($_POST['message']) ? $parse->BB_Parse($parse->process(trim($_POST['message']))) : '';

	if (dle_strlen($message) > 65000) $message = "";
	
	if (!$message) {
		die("{\"error\":\" {$lang['pm_err_2']}\"}");
	}

	if ($parse->not_allowed_tags) {
		die("{\"error\":\" {$lang['news_err_33']}\"}");
	}

	if ($parse->not_allowed_text) {
		die("{\"error\":\" {$lang['news_err_37']}\"}");
	}

	$row = $db->super_query("SELECT m.id, c.id AS conversation_id, m.content, m.created_at FROM " . USERPREFIX . "_conversations_messages m JOIN " . USERPREFIX . "_conversations c ON m.conversation_id = c.id WHERE m.id='{$id}' AND m.sender_id = '{$member_id['user_id']}'");

	if (!isset($row['id']) OR !$row['id']) {
		die("{\"error\":\" {$lang['pm_err_1']}\"}");
	}

	$is_read = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_conversation_reads WHERE user_id != '{$member_id['user_id']}' AND conversation_id = '{$row['conversation_id']}' AND last_read_at > '{$row['created_at']}'");

	if ($is_read['count'] > 0) {
		die("{\"error\":\" {$lang['pm_err_17']}\"}");
	}

	$db->query("UPDATE " . USERPREFIX . "_conversations_messages SET content='". $db->safesql($message)."' WHERE id = '{$id}'");

	$message = preg_replace("#\[hide(.*?)\]#i", "", $message);
	$message = str_ireplace("[/hide]", "", $message);
	$message = stripslashes($message);
	$message = str_replace('{THEME}', $_ROOT_DLE_URL . 'templates/' . $config['skin'], $message);

	echo json_encode(array("success" => true, "response" => $message), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$db->close();
	die();
}

if (isset($_GET['action']) AND $_GET['action'] == "edit") {

	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

	$row = $db->super_query("SELECT m.id, c.id AS conversation_id, m.content, m.created_at FROM " . USERPREFIX . "_conversations_messages m JOIN " . USERPREFIX . "_conversations c ON m.conversation_id = c.id WHERE m.id='{$id}' AND m.sender_id = '{$member_id['user_id']}'");
	
	if (!isset($row['id']) or !$row['id']) {
		die("{\"error\":\" {$lang['pm_err_1']}\"}");
	}

	$is_read = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_conversation_reads WHERE user_id != '{$member_id['user_id']}' AND conversation_id = '{$row['conversation_id']}' AND last_read_at > '{$row['created_at']}'");
	
	if( $is_read['count'] > 0 ) {
		die("{\"error\":\" {$lang['pm_err_17']}\"}");
	}

	$message = $parse->decodeBBCodes($row['content'], true, true);
	
	$is_pm_ajax_mode = true;
	$is_pm_ajax_edit_mode = true;
	include_once(DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php'));

	$response = <<<HTML
<div class="pm-edit-area ignore-select">	
	<div class="{$box_class}{$dark_theme}">
		<textarea name="dleeditpm{$id}" id="dleeditpm{$id}" style="width:100%;height:{$area_height}px;" class="ajaxwysiwygeditor">{$message}</textarea>
	</div>
	<div class="save-buttons" style="width:100%;padding-top:5px;text-align: right;">
		<input class="bbcodes cancelchanges" title="{$lang['bb_t_cancel']}" type="button" onclick="ajax_cancel_pm_edit('{$id}'); return false;" value="{$lang['bb_b_cancel']}">
		<input class="bbcodes applychanges" title="{$lang['bb_t_apply']}" type="button" onclick="ajax_save_pm_edit('{$id}'); return false;" value="{$lang['bb_b_apply']}">
	</div>
</div>
	<script>	
	setTimeout(function() {
		{$editor_scrips}
	
		setTimeout(function() {
			tinyMCE.get('dleeditpm{$id}').focus(true);
 		}, 500);
	}, 100);
	</script>
HTML;

	echo json_encode(array("success" => true, "response" => $response), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$db->close();
	die();
}

function del_tpl( $matches=array() ) {
	global $tpl;

	$tpl->copy_template = $matches[1];
}

$parse->wysiwyg = true;

$name = isset($_POST['name']) ? htmlspecialchars(strip_tags(trim($_POST['name'])), ENT_QUOTES, 'UTF-8') : '';
$subj = isset($_POST['subj']) ? htmlspecialchars(strip_tags(trim($_POST['subj'])), ENT_QUOTES, 'UTF-8') : '';
$_POST['text'] = isset($_POST['text']) ? $_POST['text'] : '';
$text = trim(stripslashes($parse->BB_Parse($parse->process($_POST['text']))));
$id = 0;
$conversation_id = isset($conversation_id) ? intval($conversation_id) : 0;

if( isset($preview) AND isset($message_id) AND $preview AND $message_id ) {
	$message_id = intval($message_id);
	$row = $db->super_query("SELECT m.id, c.subject, m.content FROM " . USERPREFIX . "_conversations_messages m JOIN " . USERPREFIX . "_conversations c ON m.conversation_id = c.id WHERE m.id='{$message_id}' AND m.sender_id = '{$member_id['user_id']}'");
	
	if(isset($row['id']) AND $row['id']) {
		$subj = stripslashes($row['subject']);
		$text =  stripslashes($row['content']);
		$id = $row['id'];
	}

}

$tpl->load_template( 'pm.tpl' );

preg_replace_callback("'\\[messages\\](.*?)\\[/messages\\]'is", "del_tpl", $tpl->copy_template );

$tpl->copy_template = "<div id='message-id-{id}'>" . $tpl->copy_template . "</div>";
$tpl->template = "<div id='message-id-{id}'>" . $tpl->copy_template . "</div>";

$tpl->set('{id}', $id);

if( strpos( $tpl->copy_template, "[xf" ) !== false ) $xfound = true;
else $xfound = false;

if( $xfound ) {

	$xfields = xfieldsload( true );

	$xfieldsdata = xfieldsdataload( $member_id['xfields'] );

	foreach ($xfields as $value) {
		$preg_safe_name = preg_quote($value[0], "'");

		if( isset($xfieldsdata[$value[0]]) AND $xfieldsdata[$value[0]] != "" ) $xfgiven = true; else $xfgiven = false;

		if ( !$xfgiven ) {

			$tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template);
			$tpl->copy_template = str_replace("[xfnotgiven_{$value[0]}]", "", $tpl->copy_template);
			$tpl->copy_template = str_replace("[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template);

		} else {

			$tpl->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template);
			$tpl->copy_template = str_replace("[xfgiven_{$value[0]}]", "", $tpl->copy_template);
			$tpl->copy_template = str_replace("[/xfgiven_{$value[0]}]", "", $tpl->copy_template);

		}
		
		$tpl->set("[xfvalue_{$value[0]}]", stripslashes($xfieldsdata[$value[0]]));
	}
}

if ($member_id['signature'] and $user_group[$member_id['user_group']]['allow_signature']) {
	$tpl->set_block("'\\[signature\\](.*?)\\[/signature\\]'si", "\\1");
	$tpl->set('{signature}', stripslashes($member_id['signature']));
} else {
	$tpl->set_block("'\\[signature\\](.*?)\\[/signature\\]'si", "");
}

if ($user_group[$member_id['user_group']]['icon']) $tpl->set('{group-icon}', "<img src=\"" . $user_group[$member_id['user_group']]['icon'] . "\" border=\"0\" alt=\"\">");
else $tpl->set('{group-icon}', "");

$tpl->set('{group-name}', $user_group[$member_id['user_group']]['group_prefix'] . $user_group[$member_id['user_group']]['group_name'] . $user_group[$member_id['user_group']]['group_suffix']);
$tpl->set('{news-num}', intval($member_id['news_num']));
$tpl->set('{comm-num}', intval($member_id['comm_num']));

if (count(explode("@", $member_id['foto'])) == 2) {
	$tpl->set('{foto}', 'https://www.gravatar.com/avatar/' . md5(trim($member_id['foto'])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']));
} else {

	if ($member_id['foto']) {

		if (strpos($member_id['foto'], "//") === 0) $avatar = "https:" . $member_id['foto'];
		else $avatar = $member_id['foto'];

		$avatar = @parse_url($avatar);

		if ($avatar['host']) {

			$tpl->set('{foto}', $member_id['foto']);
		} else $tpl->set('{foto}', $_ROOT_DLE_URL . "uploads/fotos/" . $member_id['foto']);
	} else $tpl->set('{foto}', "{THEME}/dleimages/noavatar.png");
}

$tpl->set('{date}', difflangdate($config['timestamp_comment'], $_TIME));

$news_date = $_TIME;
$tpl->copy_template = preg_replace_callback("#\{date=(.+?)\}#i", "formdate", $tpl->copy_template);

if ($member_id['reg_date']) {

	$tpl->set('{registration}', difflangdate("j F Y, H:i", $member_id['reg_date']));

	$news_date = $member_id['reg_date'];
	$tpl->copy_template = preg_replace_callback("#\{registration=(.+?)\}#i", "formdate", $tpl->copy_template);
} else $tpl->set('{registration}', '--');

$tpl->set('{lastdate}', difflangdate("j F Y, H:i", $_TIME));
$tpl->set('[online]', "");
$tpl->set('[/online]', "");
$tpl->set_block("'\\[offline\\](.*?)\\[/offline\\]'si", "");

if ($config['allow_alt_url']) {

	$name = $_ROOT_DLE_URL . "user/" . urlencode($member_id['name']) . "/";
	$name = "onclick=\"ShowProfile('" . urlencode($member_id['name']) . "', '" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
	$tpl->set('{author}', "<a {$name} class=\"pm_list\" href=\"" . $_ROOT_DLE_URL . "user/" . urlencode($member_id['name']) . "/\">" . $member_id['name'] . "</a>");
} else {

	$name = "?subaction=userinfo&amp;user=" . urlencode($member_id['name']);
	$name = "onclick=\"ShowProfile('" . urlencode($member_id['name']) . "', '" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
	$tpl->set('{author}', "<a {$name} class=\"pm_list\" href=\"?subaction=userinfo&amp;user=" . urlencode($member_id['name']) . "\">" . $member_id['name'] . "</a>");
}

$tpl->set('{login}', $member_id['name'] );

$tpl->set('[reply]', "<a onmouseover=\"dle_copy_quote('" . str_replace(array("'"), array("\'"), $member_id['name']) . "', '{$_TIME}', '" . difflangdate($config['timestamp_comment'], $_TIME) . ", " . str_replace(array("'"), array("\'"), $member_id['name']) . " " . $lang['user_says'] . "', 'pm'); return false;\" onclick=\"dle_ins('{$id}', 'pm'); return false;\" href=\"#\">");
$tpl->set('[/reply]', "</a>");

$tpl->set('[del]', "<a href=\"javascript:DeleteMessage('{$id}', '{$conversation_id}', '{$dle_login_hash}')\">");
$tpl->set('[/del]', "</a>");
$tpl->set('[pm-edit]', "<a onclick=\"ajax_pm_edit('{$id}'); return false;\" href=\"#\">");
$tpl->set('[/pm-edit]', "</a>");
$tpl->set('[pm-author]', "");
$tpl->set('[/pm-author]', "");
$tpl->set_block("'\\[not-pm-author\\](.*?)\\[/not-pm-author\\]'si", "");
$tpl->set_block("'\\[ignore\\](.*?)\\[/ignore\\]'si", "");
$tpl->set_block("'\\[complaint\\](.*?)\\[/complaint\\]'si", "");

$tpl->set( '{subj}', $subj );

$text = preg_replace("#\[hide(.*?)\]#i", "", $text);
$text = str_ireplace("[/hide]", "", $text);

if (stripos($text, "title_quote") !== false) {
	$text = preg_replace_callback("#<div class=['\"]title_quote['\"](.*?)>(.+?)</div>#i",  'fix_quote_title', $text);
}

$tpl->set( '{text}',  "<div id='pm-id-" . $id . "'>" . $text . "</div>" );

$tpl->compile( 'content' );
$tpl->clear();

$tpl->result['content'] = preg_replace ( "#\[hide(.*?)\]#i", "", $tpl->result['content'] );
$tpl->result['content'] = str_ireplace( "[/hide]", "", $tpl->result['content']);
$tpl->result['content'] = str_replace( '{THEME}', $_ROOT_DLE_URL . 'templates/' . $config['skin'], $tpl->result['content'] );

$tpl->result['content'] = "<div id=\"blind-animation-{$id}\" style=\"display:none\">".$tpl->result['content']."<div>";

echo json_encode(array("success" => true, "id" => $id, "content" => $tpl->result['content']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$db->close();
