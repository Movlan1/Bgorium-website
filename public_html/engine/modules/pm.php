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

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$allowed_tags = array('div[align|style|class|data-commenttime|data-commentuser|contenteditable]', 'span[style|class|data-userurl|data-username|contenteditable]', 'p[align|style|class]', 'pre[class]', 'code', 'br', 'strong', 'em', 'ul', 'li', 'ol', 'b', 'u', 'i', 's', 'hr');
	
if( $user_group[$member_id['user_group']]['allow_url'] ) $allowed_tags[] = 'a[href|target|style|class|title]';
if( $user_group[$member_id['user_group']]['allow_image'] ) $allowed_tags[] = 'img[style|class|src|alt|width|height]';
	
$parse = new ParseFilter( $allowed_tags );

$parse->safe_mode = true;
$parse->remove_html = false;
$parse->allow_video = false;
$parse->allow_media = false;
$parse->disable_leech = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$user_group[$member_id['user_group']]['allow_up_image'] = 0;
$user_group[$member_id['user_group']]['video_comments'] = 0;
$user_group[$member_id['user_group']]['media_comments'] = 0;

$p_name = "";
$p_id = "";

$stop_pm = false;

if( isset( $_REQUEST['doaction'] ) ) $doaction = $_REQUEST['doaction']; else $doaction = "";

if( !$is_logged OR !$user_group[$member_id['user_group']]['allow_pm'] ) {
	
	if( !$is_logged AND isset($_GET['pmid']) AND $_GET['pmid'] ) {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_12'] );
		
	} elseif ( !$is_logged ) {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_13'] );
		
	} else {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_1'] );
		
	}
	
	$stop_pm = true;
}

if( $user_group[$member_id['user_group']]['max_pm'] AND $member_id['pm_all'] >= $user_group[$member_id['user_group']]['max_pm'] AND !$stop_pm ) {
	msgbox( $lang['all_info'], $lang['pm_err_9'] );
}


if( $user_group[$member_id['user_group']]['max_pm_day'] AND $doaction == "newpm" ) {

	$this_time = time() - 86400;
	$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='1'" );

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX ."_sendlog WHERE user = '" . $db->safesql($member_id['name']) . "' AND flag='1'");

	if( $row['count'] >=  $user_group[$member_id['user_group']]['max_pm_day'] ) {

		msgbox( $lang['all_err_1'], str_replace('{max}', $user_group[$member_id['user_group']]['max_pm_day'], $lang['pm_err_10']) );
		$stop_pm = true;
	}
}


if( $doaction == "del" AND !$stop_pm AND ( ( isset($_POST['selected_pm']) AND is_array($_POST['selected_pm']) AND count($_POST['selected_pm']) ) OR isset($_GET['pmid']) ) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	if( isset($_GET['pmid']) ) {
		$_POST['selected_pm'][] = intval($_GET['pmid']);
	}

	foreach ( $_POST['selected_pm'] as $pmid ) {
	
		$pmid = intval( $pmid );

		$db->query("DELETE FROM " . USERPREFIX . "_conversation_users WHERE conversation_id='{$pmid}' AND user_id={$member_id['user_id']}");
		
		$count = $db->super_query("SELECT COUNT(*) AS count FROM " . USERPREFIX . "_conversation_users WHERE conversation_id='{$pmid}'");

		if( !$count['count'] ) {
			$db->query("DELETE FROM " . USERPREFIX . "_conversations WHERE id='{$pmid}'");
			$db->query("DELETE FROM " . USERPREFIX . "_conversation_reads WHERE conversation_id='{$pmid}'");
			$db->query("DELETE FROM " . USERPREFIX . "_conversations_messages WHERE conversation_id='{$pmid}'");
		}

		
	}

	$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}'");
	$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$member_id['user_id']}'");
	$member_id['pm_all'] = $count['total'];
	$member_id['pm_unread'] = $count['unread'];

}

if( $doaction == "setunread" AND !$stop_pm AND isset($_POST['selected_pm']) AND is_array($_POST['selected_pm']) AND count($_POST['selected_pm']) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	foreach ( $_POST['selected_pm'] as $pmid ) {

		$pmid = intval( $pmid );

		$db->query( "DELETE FROM " . USERPREFIX . "_conversation_reads WHERE conversation_id='{$pmid}' AND user_id={$member_id['user_id']}" );

	}

	$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}'");
	$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$member_id['user_id']}'");
	
	$member_id['pm_all'] = $count['total'];
	$member_id['pm_unread'] = $count['unread'];
}


if( $doaction == "setread" AND !$stop_pm AND isset($_POST['selected_pm']) AND is_array($_POST['selected_pm']) AND count($_POST['selected_pm']) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	foreach ($_POST['selected_pm'] as $pmid) {

		$pmid = intval($pmid);
		
		$db->query("INSERT INTO " . USERPREFIX . "_conversation_reads (user_id, conversation_id, last_read_at) values ('{$member_id['user_id']}', '{$pmid}', '{$_TIME}') ON DUPLICATE KEY UPDATE last_read_at='{$_TIME}'");

	}

	$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}'");
	$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$member_id['user_id']}'");


}

$tpl->load_template( 'pm.tpl' );

$tpl->set( '[inbox]', "<a href=\"$PHP_SELF?do=pm\">" );
$tpl->set( '[/inbox]', "</a>" );
$tpl->set_block("'\\[outbox\\](.*?)\\[/outbox\\]'si", "");
$tpl->set( '[new_pm]', "<a href=\"$PHP_SELF?do=pm&amp;doaction=newpm\">" );
$tpl->set( '[/new_pm]', "</a>" );

if ( $user_group[$member_id['user_group']]['max_pm'] ) {

	$prlim = intval( ($member_id['pm_all'] / $user_group[$member_id['user_group']]['max_pm']) * 100 );

	if ($prlim > 100) $prlim = 100;

	$tpl->set( '{proc-pm-limit}', $prlim );
	$tpl->set( '{pm-limit}', $user_group[$member_id['user_group']]['max_pm'] );

} else {
	$prlim = 0;
	$tpl->set( '{proc-pm-limit}', $prlim );
	$tpl->set( '{pm-limit}', $lang['no_pm_limit'] );
}

$tpl->set( '{pm-progress-bar}', "<div class=\"pm_progress_bar\" title=\"{$lang['pm_progress_bar']} {$prlim}%\"><span style=\"width: {$prlim}%\">{$prlim}%</span></div>" );

if( $doaction == "readpm" AND !$stop_pm ) {
	
	$pmid = intval( $_GET['pmid'] );
	
	$tpl->set( '[readpm]', "" );
	$tpl->set( '[/readpm]', "" );
	$tpl->set_block( "'\\[pmlist\\].*?\\[/pmlist\\]'si", "" );
	$tpl->set_block( "'\\[newpm\\].*?\\[/newpm\\]'si", "" );

	$row = $db->super_query("SELECT c.id, c.subject, cr.last_read_at FROM " . USERPREFIX ."_conversations c JOIN " . USERPREFIX ."_conversation_users cu ON c.id = cu.conversation_id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON c.id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}' AND c.id='{$pmid}'" );

	if( !isset($row['id']) OR !$row['id']) {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_6'] );
		$stop_pm = true;
	
	} else {

		$pmid = $row['id'];
		$last_read_at = $row['last_read_at'];

		$db->query("INSERT INTO " . USERPREFIX . "_conversation_reads (user_id, conversation_id, last_read_at) VALUES ('{$member_id['user_id']}', '{$pmid}', '{$_TIME}') ON DUPLICATE KEY UPDATE last_read_at = '{$_TIME}'");

		$count = $db->super_query("SELECT COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$member_id['user_id']}'");
		$db->query("UPDATE " . USERPREFIX . "_users SET pm_unread='{$count['unread']}' WHERE user_id='{$member_id['user_id']}'");

		preg_match('/\[messages\](.*?)\[\/messages\]/si', $tpl->copy_template, $matches);

		if (!empty($matches[1])) {
			$messages_tpl = $matches[1];
		} else $messages_tpl = '';

		$tpl->set('{subj}', stripslashes($row['subject']));
		$tpl->set_block("'\\[messages\\](.*?)\\[/messages\\]'si", "{DLE-PM-MESSAGES}");
		
		$tpl->set('[del]', "<a href=\"javascript:confirmDelete('" . $config['http_home_url'] . "index.php?do=pm&amp;doaction=del&amp;pmid=" . $pmid . "&amp;dle_allow_hash=" . $dle_login_hash . "')\">");
		$tpl->set('[/del]', "</a>");

		if ($tpl->smartphone or $tpl->tablet) $comments_mobile_editor = true; else $comments_mobile_editor = false;

		include_once(DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php'));
		$allow_comments_ajax = true;

		$tpl->set('{editor}', $wysiwyg);

		$tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\">" . $tpl->copy_template . "<input type=\"hidden\" name=\"conversation_id\" value=\"{$pmid}\"><input type=\"hidden\" name=\"action\" value=\"send_pm\"><input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\"></form>";

$onload_scripts[] = <<<HTML
	
		$('#dle-comments-form').submit(function(event) {
			event.preventDefault();
			tinyMCE.triggerSave();

			doSendPM();
			return false;
			
		});

		setTimeout(function() {
			
			if( $('#dle-lastread-pm').length ) {
				var pm_node = $('#dle-lastread-pm').next();
			} else if( $('#dle-ajax-pm' ).length ) {
				var pm_node = $('#dle-ajax-pm').prev();
			}

			if (pm_node !== undefined) {
			
				if( pm_node.attr('id') !== undefined ) {
					var pm_id = pm_node.attr('id');
				} else {
					var pm_id = 'pm-id-last';
					pm_node.attr('id', pm_id);
				}
			
				scrollToCenterPosition('#'+pm_id, function () {

					scrollToCenterPosition('#'+pm_id, null, 1);

				});

			}

		}, 200);
HTML;

		$tpl->compile('content');
		$tpl->clear();

		$tpl->copy_template = "<div id='message-id-{id}'>". $messages_tpl . "</div>";
		$tpl->template = "<div id='message-id-{id}'>" . $messages_tpl . "</div>";

		if( strpos( $tpl->copy_template, "[xf" ) !== false ) {
			$xfound = true;
			$xfields = xfieldsload(true);
		} else $xfound = false;

		$sql_result = $db->query("SELECT m.id, m.content, m.created_at, m.sender_id AS user_id, u.name, u.news_num, u.comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.signature, u.foto, u.fullname, u.land, u.xfields FROM " . USERPREFIX . "_conversations_messages m LEFT JOIN " . USERPREFIX . "_users u ON m.sender_id = u.user_id WHERE m.conversation_id = '{$pmid}' ORDER BY m.id ASC");
		
		$first_pm = true;
		$last_is_set = false;

		while ($row = $db->get_row($sql_result)) {
			
			$tpl->set('{id}', $row['id']);

			if ($xfound) {

				$xfieldsdata = xfieldsdataload($row['xfields']);

				foreach ($xfields as $value) {
					$preg_safe_name = preg_quote($value[0], "'");

					if( isset($xfieldsdata[$value[0]]) AND $xfieldsdata[$value[0]] != "" ) $xfgiven = true; else $xfgiven = false;

					if ($value[5] != 1 OR $member_id['user_group'] == 1 OR $member_id['user_id'] == $row['user_id'] ) {
						
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

					} else {

						$tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template);
						$tpl->copy_template = preg_replace("'\\[xfvalue_{$preg_safe_name}\\]'i", "", $tpl->copy_template);
						$tpl->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template);

					}
				}
			}

			if( !$last_is_set AND $row['created_at'] > $last_read_at) {
				$tpl->copy_template = '<a id="dle-lastread-pm"></a>'. $tpl->copy_template;
				$last_is_set = true;
			}

			if ($row['signature'] and $user_group[$row['user_group']]['allow_signature']) {

				$tpl->set_block("'\\[signature\\](.*?)\\[/signature\\]'si", "\\1");
				$tpl->set('{signature}', stripslashes($row['signature']));
			} else {
				$tpl->set_block("'\\[signature\\](.*?)\\[/signature\\]'si", "");
			}


			if ($user_group[$row['user_group']]['icon']) $tpl->set('{group-icon}', "<img src=\"" . $user_group[$row['user_group']]['icon'] . "\" border=\"0\" alt=\"\">");
			else $tpl->set('{group-icon}', "");

			$tpl->set('{group-name}', $user_group[$row['user_group']]['group_prefix'] . $user_group[$row['user_group']]['group_name'] . $user_group[$row['user_group']]['group_suffix']);

			$tpl->set('{news-num}', number_format($row['news_num'], 0, ',', ' '));
			$tpl->set('{comm-num}', number_format($row['comm_num'], 0, ',', ' '));

			if ($row['foto'] and count(explode("@", $row['foto'])) == 2) {

				$tpl->set('{foto}', 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']));
			} else {

				if ($row['foto']) {

					if (strpos($row['foto'], "//") === 0) $avatar = "http:" . $row['foto'];
					else $avatar = $row['foto'];

					$avatar = @parse_url($avatar);

					if ($avatar['host']) {

						$tpl->set('{foto}', $row['foto']);
					} else $tpl->set('{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto']);
				} else $tpl->set('{foto}', "{THEME}/dleimages/noavatar.png");
			}

			$tpl->set('{date}', difflangdate($config['timestamp_comment'], $row['created_at']));

			$news_date = $row['created_at'];
			$tpl->copy_template = preg_replace_callback("#\{date=(.+?)\}#i", "formdate", $tpl->copy_template);

			if ($row['reg_date']) {

				$tpl->set('{registration}', difflangdate("j F Y, H:i", $row['reg_date']));

				$news_date = $row['reg_date'];
				$tpl->copy_template = preg_replace_callback("#\{registration=(.+?)\}#i", "formdate", $tpl->copy_template);

			} else $tpl->set('{registration}', '--');

			if ($row['lastdate']) {

				$tpl->set('{lastdate}', difflangdate("j F Y, H:i", $row['lastdate']));

				$news_date = $row['lastdate'];
				$tpl->copy_template = preg_replace_callback("#\{lastdate=(.+?)\}#i", "formdate", $tpl->copy_template);

				if (($row['lastdate'] + 1200) > $_TIME  and !$row['banned']) {

					$tpl->set('[online]', "");
					$tpl->set('[/online]', "");
					$tpl->set_block("'\\[offline\\](.*?)\\[/offline\\]'si", "");
				} else {
					$tpl->set('[offline]', "");
					$tpl->set('[/offline]', "");
					$tpl->set_block("'\\[online\\](.*?)\\[/online\\]'si", "");
				}
			} else {

				$tpl->set('{lastdate}', '--');
				$tpl->set_block("'\\[offline\\](.*?)\\[/offline\\]'si", "");
				$tpl->set_block("'\\[online\\](.*?)\\[/online\\]'si", "");
			}

			if ($config['allow_alt_url']) {

				$name = $config['http_home_url'] . "user/" . urlencode($row['name']) . "/";
				$name = "onclick=\"ShowProfile('" . urlencode($row['name']) . "', '" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
				$tpl->set('{author}', "<a {$name} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode($row['name']) . "/\">" . $row['name'] . "</a>");

			} else {

				$name = "?subaction=userinfo&amp;user=" . urlencode($row['name']);
				$name = "onclick=\"ShowProfile('" . urlencode($row['name']) . "', '" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
				$tpl->set('{author}', "<a {$name} class=\"pm_list\" href=\"?subaction=userinfo&amp;user=" . urlencode($row['name']) . "\">" . $row['name'] . "</a>");
			}

			$tpl->set('{login}', $row['name']);
			$tpl->set('[reply]', "<a onmouseover=\"dle_copy_quote('" . str_replace(array("'"), array("\'"), $row['name']) . "', '{$row['created_at']}', '" . difflangdate($config['timestamp_comment'], $row['created_at']) . ", " . str_replace(array("'"), array("\'"), $row['name']) . " " . $lang['user_says'] . "', 'pm'); return false;\" onclick=\"dle_ins('{$row['id']}', 'pm'); return false;\" href=\"#\">");
			$tpl->set('[/reply]', "</a>");

			if($first_pm) {

				$tpl->set('[del]', "<a href=\"javascript:confirmDelete('" . $config['http_home_url'] . "index.php?do=pm&amp;doaction=del&amp;pmid=" . $pmid . "&amp;dle_allow_hash=" . $dle_login_hash . "')\">");
				$tpl->set('[/del]', "</a>");

			} else {

				if( $member_id['user_id'] == $row['user_id'] ) {
					$tpl->set('[del]', "<a href=\"javascript:DeleteMessage('{$row['id']}', '{$pmid}', '{$dle_login_hash}')\">");
					$tpl->set('[/del]', "</a>");
				} else {
					$tpl->set_block("'\\[del\\](.*?)\\[/del\\]'si", "");
				}
			}

			if( $member_id['user_id'] == $row['user_id'] ) {
				$tpl->set('[pm-edit]', "<a onclick=\"ajax_pm_edit('" . $row['id'] . "'); return false;\" href=\"#\">");
				$tpl->set('[/pm-edit]', "</a>");
				$tpl->set('[pm-author]', "");
				$tpl->set('[/pm-author]', "");
				$tpl->set_block("'\\[not-pm-author\\](.*?)\\[/not-pm-author\\]'si", "");
			} else {
				$tpl->set_block("'\\[pm-author\\](.*?)\\[/pm-author\\]'si", "");
				$tpl->set('[not-pm-author]', "");
				$tpl->set('[/not-pm-author]', "");
				$tpl->set_block("'\\[pm-edit\\](.*?)\\[/pm-edit\\]'si", "");
			}
			
			if ($member_id['user_id'] != $row['user_id']) {
				$tpl->set('[ignore]', "<a href=\"javascript:AddIgnorePM('" . $row['user_id'] . "', '" . $lang['add_to_ignore'] . "')\">");
				$tpl->set('[/ignore]', "</a>");
				$tpl->set('[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'pm')\">");
				$tpl->set('[/complaint]', "</a>");
			} else {
				$tpl->set_block("'\\[ignore\\](.*?)\\[/ignore\\]'si", "");
				$tpl->set_block("'\\[complaint\\](.*?)\\[/complaint\\]'si", "");
			}
			
			$row['content'] = stripslashes($row['content']);

			$row['content'] = preg_replace("#\[hide(.*?)\]#i", "", $row['content']);
			$row['content'] = str_ireplace("[/hide]", "", $row['content']);
			

			if (stripos($row['content'], "title_quote") !== false) {
				$row['content'] = preg_replace_callback("#<div class=['\"]title_quote['\"](.*?)>(.+?)</div>#i",  'fix_quote_title', $row['content']);
			}

			$tpl->set('{text}', "<div id='pm-id-" . $row['id'] . "'>" .$row['content'] . "</div>");

			$tpl->compile('pm_messages');
			$first_pm = false;
		}
		$tpl->clear();

		$tpl->result['pm_messages'] .= "<div id=\"dle-ajax-pm\"></div>";

		$tpl->result['content'] = str_replace('{DLE-PM-MESSAGES}', $tpl->result['pm_messages'], $tpl->result['content']);
		unset($tpl->result['pm_messages']);
	}

} elseif( $doaction == "newpm" AND !$stop_pm ) {
	
	$ajax_form = <<<HTML
<span id="dle-pm-preview"></span>
<script>
<!--
function dlePMPreview(){ 

	var pm_text = tinyMCE.get('comments').getContent();

	if(document.getElementById('dle-comments-form').name.value == '' || document.getElementById('dle-comments-form').subj.value == '' || pm_text == '')
	{
		DLEPush.error('{$lang['comm_req_f']}');return false;

	}

	var name = document.getElementById('dle-comments-form').name.value;
	var subj = document.getElementById('dle-comments-form').subj.value;

	ShowLoading('');

	$.post(dle_root + "engine/ajax/controller.php?mod=pm", { text: pm_text, name: name, subj: subj, skin: dle_skin, user_hash: '{$dle_login_hash}' }, function(data){

		HideLoading('');
		
		if (data.success) {

			$("#dle-pm-preview").html(data.content);
			scrollToCenterPosition("#dle-pm-preview", function() {
				
				$('#blind-animation-0' ).show('blind',{},500);
				
			});

		} else if (data.error) {

			DLEPush.error(data.error);

		}

	}, "json");

};
//-->
</script>
HTML;
	
	$tpl->set( '[newpm]', $ajax_form );
	$tpl->set( '[/newpm]', "" );
	$tpl->set_block( "'\\[pmlist\\].*?\\[/pmlist\\]'si", "" );
	$tpl->set_block( "'\\[readpm\\].*?\\[/readpm\\]'si", "" );
	
	if( $user_group[$member_id['user_group']]['captcha_pm'] ) {

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
						
					$tpl->set( '{recaptcha}', "<div class=\"{$captcha_name}\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\" data-language=\"{$lang['language_code']}\"></div><script src=\"{$captcha_url}\" async defer></script>" );

				}
				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{sec_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$tpl->set( '{sec_code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" border=\"0\" width=\"160\" height=\"80\" /></span></a>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

	} else {

		$tpl->set( '{sec_code}', "" );
		$tpl->set( '{recaptcha}', "" );
		$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
		$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );

	}

	if( $user_group[$member_id['user_group']]['pm_question'] ) {

		$tpl->set( '[question]', "" );
		$tpl->set( '[/question]', "" );

		$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
		$tpl->set( '{question}', "<span id=\"dle-question\">".htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, 'UTF-8' )."</span>" );

		$_SESSION['question'] = $question['id'];

	} else {

		$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
		$tpl->set( '{question}', "" );

	}
	
	if (isset($_GET['username']) AND $_GET['username'] ) $username = $db->safesql(trim(strip_tags(urldecode($_GET['username'])))); else $username = '';
	
	if ($username) {

		$row = $db->super_query("SELECT name FROM " . USERPREFIX . "_users WHERE name='{$username}'");
		$row['name'] = isset($row['name']) ? $row['name'] : '';

		$tpl->set('{author}', $row['name']);
	} else {
		$tpl->set('{author}', "");
	}

	$tpl->set( '{subj}', "" );

	if ($tpl->smartphone or $tpl->tablet) $comments_mobile_editor = true; else $comments_mobile_editor = false;

	include_once (DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php'));
	$allow_comments_ajax = true;

	$tpl->set( '{editor}', $wysiwyg );
	
	$tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" action=\"\">\n" . $tpl->copy_template . "<input name=\"action\" type=\"hidden\" value=\"send_pm\"><input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\"></form>";

		
	$onload_scripts[] = <<<HTML
	
		$('#dle-comments-form').submit(function(event) {
		
			tinyMCE.triggerSave();
			
			if( document.getElementById('dle-comments-form').name.value == '' || document.getElementById('dle-comments-form').subj.value == '' || document.getElementById('comments').value == '') {
				DLEPush.error('{$lang['comm_req_f']}');
				return false;
			}
		
			if(dle_captcha_type == 2 && typeof grecaptcha != "undefined") {
			
				event.preventDefault();
				
				grecaptcha.execute('{$config['recaptcha_public_key']}', {action: 'personal_message'}).then(function(token) {
					$('#dle-comments-form').append('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
					doSendPM();
				});
		
				return false;
			}

			doSendPM();
			return false;
			
		});
HTML;


	if (isset($row['user_id']) AND $row['user_id']) {

		$db->query( "SELECT id FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' AND user_from='" . $db->safesql($member_id['name']) . "'" );
		if( $db->num_rows() ) { $stop_pm = true; $lang['pm_err_8'] = $lang['pm_ignored'];}
		$db->free();

	}

	if( !$stop_pm ) {
		
		$tpl->compile( 'content' );
		$tpl->clear();
		
	} else {
		
		$tpl->clear();
		if( ! $tpl->result['info'] ) msgbox( $lang['all_info'], $lang['pm_err_8'] );
		
	}

} elseif( !$stop_pm ) {
	
	$tpl->set( '[pmlist]', "" );
	$tpl->set( '[/pmlist]', "" );
	$tpl->set_block( "'\\[newpm\\].*?\\[/newpm\\]'si", "" );
	$tpl->set_block( "'\\[readpm\\].*?\\[/readpm\\]'si", "" );

	$pm_per_page = intval($config['max_pm_list']) > 0 ?  intval($config['max_pm_list']) : 20;
	
	if (isset ( $_GET['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 0;

	if ($cstart) {
		$cstart = $cstart - 1;
		$cstart = $cstart * $pm_per_page;
	}

	if ($cstart < 0) $cstart = 0;
	
	$pmlist = <<<HTML
<form action="" method="post" name="pmlist" id="pmlist">
<input type="hidden" name="dle_allow_hash" value="{$dle_login_hash}" />
HTML;
	

	$sql = "SELECT c.id AS conversation_id, c.subject, c.sender_id AS c_sender_id, c.recipient_id, m.content AS last_message, c.updated_at, m.sender_id, u.name, sender.name AS sender_name, recipient.name AS recipient_name, CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN 0 ELSE 1 END AS read_status FROM " . USERPREFIX . "_conversations c JOIN " . USERPREFIX . "_conversation_users cu ON c.id = cu.conversation_id JOIN " . USERPREFIX . "_conversations_messages m ON c.id = m.conversation_id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON c.id = cr.conversation_id AND cu.user_id = cr.user_id JOIN (SELECT conversation_id, MAX(created_at) AS last_message_time FROM " . USERPREFIX . "_conversations_messages GROUP BY conversation_id) AS lm ON m.conversation_id = lm.conversation_id AND m.created_at = lm.last_message_time JOIN " . USERPREFIX . "_users u ON m.sender_id = u.user_id JOIN " . USERPREFIX . "_users sender ON c.sender_id = sender.user_id JOIN " . USERPREFIX . "_users recipient ON c.recipient_id = recipient.user_id WHERE cu.user_id = '{$member_id['user_id']}' ORDER BY read_status ASC, c.updated_at DESC LIMIT " . $cstart . "," . $pm_per_page;
	
	$sql_count = "SELECT COUNT(DISTINCT cu.conversation_id) AS count FROM " . USERPREFIX . "_conversation_users cu WHERE cu.user_id = '{$member_id['user_id']}'";
	$user_query = "do=pm";
	
	$pmlist .= "<table class=\"pm\" style=\"width:100%;\"><thead><tr><th width=\"20\" class=\"pm_head pm_icon\">&nbsp;</th><th class=\"pm_head pm_subj\">" . $lang['pm_subj'] . "</th><th width=\"130\" class=\"pm_head pm_last_user\">" . $lang['pm_from'] . "</th><th width=\"50\" class=\"pm_head pm_checkbox\" align=\"center\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"master_box\" title=\"{$lang['pm_selall']}\" onclick=\"javascript:ckeck_uncheck_all()\"></label></th></tr></thead><tbody>";
	
	$sql_result = $db->query( $sql );
	$i = 0;
	$cc = $cstart;
	
	while ( $row = $db->get_row($sql_result) ) {
		$i ++;
		$cc ++;
		
		if( $config['allow_alt_url'] ) {
			
			$user_from = $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/";
			$user_from = "onclick=\"event.stopPropagation(); ShowProfile('" . urlencode( $row['name'] ) . "', '" . htmlspecialchars( $user_from, ENT_QUOTES, 'UTF-8' ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_from = "<a {$user_from} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/\">" . $row['name'] . "</a>";
		
		} else {
			
			$user_from = "?subaction=userinfo&amp;user=" . urlencode( $row['name'] );
			$user_from = "onclick=\"event.stopPropagation(); ShowProfile('" . urlencode( $row['name'] ) . "', '" . $user_from . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_from = "<a {$user_from} class=\"pm_list\" href=\"?subaction=userinfo&amp;user=" . urlencode( $row['name'] ) . "\">" . $row['name'] . "</a>";

		}

		$user_from = '<div class="pm_last_user">'. $user_from . '</div><div class="pm_last_date">'. difflangdate('j.m.Y H:i', $row['updated_at']) . '</div>';
		
		if ($member_id['user_id'] == $row['c_sender_id']) {
			$user_name = $row['recipient_name'];
		} else {
			$user_name = $row['sender_name'];
		}

		if ($config['allow_alt_url']) {
			$with_user = $config['http_home_url'] . "user/" . urlencode($user_name) . "/";
			$with_user = "onclick=\"event.stopPropagation(); ShowProfile('" . urlencode($user_name) . "', '" . htmlspecialchars($with_user, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$with_user = "<a {$with_user} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode($user_name) . "/\">" . $user_name . "</a>";
		} else {

			$with_user = "?subaction=userinfo&amp;user=" . urlencode($user_name);
			$with_user = "onclick=\"event.stopPropagation(); ShowProfile('" . urlencode($user_name) . "', '" . $with_user . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$with_user = "<a {$with_user} class=\"pm_list\" href=\"$?subaction=userinfo&amp;user=" . urlencode($user_name) . "\">" . $user_name . "</a>";
		}

		$with_user = "<div class=\"pm_with_user\">{$lang['pm_with_user']} " . $with_user . '</div>';
		
		if ($row['c_sender_id'] == $row['recipient_id']){
			$with_user ='';
		}

		if( $row['read_status'] ) {
			
			$subj = "<a class=\"pm_list\" href=\"?do=pm&amp;doaction=readpm&amp;pmid=" . $row['conversation_id'] . "\">" . stripslashes( $row['subject'] ) . "</a>";
			$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8.47 1.318a1 1 0 0 0-.94 0l-6 3.2A1 1 0 0 0 1 5.4v.817l5.75 3.45L8 8.917l1.25.75L15 6.217V5.4a1 1 0 0 0-.53-.882zM15 7.383l-4.778 2.867L15 13.117zm-.035 6.88L8 10.082l-6.965 4.18A1 1 0 0 0 2 15h12a1 1 0 0 0 .965-.738ZM1 13.116l4.778-2.867L1 7.383v5.734ZM7.059.435a2 2 0 0 1 1.882 0l6 3.2A2 2 0 0 1 16 5.4V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5.4a2 2 0 0 1 1.059-1.765z"/></svg>';
			$class = "pm-read-image";
		
		} else {
			
			$subj = "<a class=\"pm_list\" href=\"?do=pm&amp;doaction=readpm&amp;pmid=" . $row['conversation_id'] . "\"><b>" . stripslashes( $row['subject'] ) . "</b></a>";
			$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg>';
			$class = "pm-unread-image";
		
		}
		
		$row['last_message'] = remove_quotes_from_text($row['last_message']);
		$row['last_message'] = clear_content($row['last_message'], 0, false);
		
		if (dle_strlen($row['last_message']) > 100) {

			$row['last_message'] = dle_substr($row['last_message'], 0, 100);

			if (($temp_dmax = dle_strrpos($row['last_message'], ' '))) $row['last_message'] = dle_substr($row['last_message'], 0, $temp_dmax);

			$row['last_message'] .= ' ...'; 
		}

		$subj = str_replace(array("{", "["), array("&#123;", "&#91;"), $subj);

		$subj = '<div class="pm_subj">' . $subj . '</div><div class="pm_last_message">' . $row['last_message'] . '</div>';

		if ($row['sender_id'] == $member_id['user_id'] AND $row['read_status'] AND $row['c_sender_id'] != $row['recipient_id']) {
			$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8.098 5.013a.144.144 0 0 1 .202.134V6.3a.5.5 0 0 0 .5.5c.667 0 2.013.005 3.3.822.984.624 1.99 1.76 2.595 3.876-1.02-.983-2.185-1.516-3.205-1.799a8.7 8.7 0 0 0-1.921-.306 7 7 0 0 0-.798.008h-.013l-.005.001h-.001L8.8 9.9l-.05-.498a.5.5 0 0 0-.45.498v1.153c0 .108-.11.176-.202.134L4.114 8.254l-.042-.028a.147.147 0 0 1 0-.252l.042-.028zM9.3 10.386q.102 0 .223.006c.434.02 1.034.086 1.7.271 1.326.368 2.896 1.202 3.94 3.08a.5.5 0 0 0 .933-.305c-.464-3.71-1.886-5.662-3.46-6.66-1.245-.79-2.527-.942-3.336-.971v-.66a1.144 1.144 0 0 0-1.767-.96l-3.994 2.94a1.147 1.147 0 0 0 0 1.946l3.994 2.94a1.144 1.144 0 0 0 1.767-.96z"/><path d="M5.232 4.293a.5.5 0 0 0-.7-.106L.54 7.127a1.147 1.147 0 0 0 0 1.946l3.994 2.94a.5.5 0 1 0 .593-.805L1.114 8.254l-.042-.028a.147.147 0 0 1 0-.252l.042-.028 4.012-2.954a.5.5 0 0 0 .106-.699"/></svg>';
			$class = "pm-reply-image";
		}

		$pmlist .= "<tr><td class=\"pm_list pm_icon {$class}\" onclick=\"document.location='?do=pm&doaction=readpm&pmid={$row['conversation_id']}'; return false;\">{$icon}</td><td class=\"pm_list pm_subj\" onclick=\"document.location='?do=pm&doaction=readpm&pmid={$row['conversation_id']}'; return false;\">{$subj}{$with_user}</td><td class=\"pm_list pm_last_user\" onclick=\"document.location='?do=pm&doaction=readpm&pmid={$row['conversation_id']}'; return false;\">{$user_from}</td><td class=\"pm_list pm_checkbox\" align=\"center\"><label class=\"form-check-label\"><input name=\"selected_pm[]\" value=\"{$row['conversation_id']}\" type=\"checkbox\" class=\"form-check-input\"></label></td></tr>";
	
	}
	$pmlist .= '</tbody></table>';

	$db->free();

	$count_all = $db->super_query( $sql_count );
	$count_all = $count_all['count'];
	$pages = "";

	if( $count_all AND $count_all > $pm_per_page) {

		if( isset( $cstart ) and $cstart > 0 ) {
			$prev = $cstart / $pm_per_page;

				if ($prev == 1)
					$pages .= "<a href=\"$PHP_SELF?{$user_query}\"> << </a> ";
				else
					$pages .= "<a href=\"$PHP_SELF?cstart=$prev&amp;$user_query\"> << </a> ";
		
		}
				
		$enpages_count = @ceil( $count_all / $pm_per_page );
				
		$cstart = ($cstart / $pm_per_page) + 1;
				
		if( $enpages_count <= 10 ) {
					
			for($j = 1; $j <= $enpages_count; $j ++) {
						
				if( $j != $cstart ) {
							
					if ($j == 1)
						$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
					else
						$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
						
				} else {
					
					$pages .= "<span>$j</span> ";
				}
			}
				
		} else {
					
			$start = 1;
			$end = 10;
			$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
			
			if( $cstart > 0 ) {
						
				if( $cstart > 6 ) {
							
					$start = $cstart - 4;
					$end = $start + 8;
							
					if( $end >= $enpages_count ) {
						$start = $enpages_count - 9;
						$end = $enpages_count - 1;
						$nav_prefix = "";
				} else
						$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
				}
					
			}
					
			if( $start >= 2 ) {
				
				$pages .= "<a href=\"$PHP_SELF?{$user_query}\">1</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
			
			}
					
			for($j = $start; $j <= $end; $j ++) {
						
				if( $j != $cstart ) {
					if ($j == 1)
						$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
					else
						$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
						
				} else {
							
					$pages .= "<span>$j</span> ";
				}
					
			}
					
			if( $cstart != $enpages_count ) {
						
				$pages .= $nav_prefix . "<a href=\"$PHP_SELF?cstart={$enpages_count}&amp;$user_query\">{$enpages_count}</a>";
					
			} else
				$pages .= "<span>{$enpages_count}</span> ";
		
		}

		if( $pm_per_page < $count_all AND $cc < $count_all ) {
			$next_page = $cc / $pm_per_page + 1;
			$pages .= "<a href=\"$PHP_SELF?cstart=$next_page&amp;$user_query\"> >> </a>";			
		
		}	
	}
	if($pages) {
		$pages = "<div class=\"navigation\">{$pages}</div>";
	} else {
		$pages = "&nbsp;";
	}

	$pmlist .= "<table class=\"pm_navigation\" style=\"width:100%;\"<tr><td>{$pages}</td><td align=\"right\"><select id=\"pmlist_doaction\"name=\"doaction\"><optgroup label=\"{$lang['edit_selact']}\"><option value=\"\">---</option><option value=\"del\">{$lang['edit_seldel']}</option><option value=\"setread\">{$lang['pm_set_read']}</option><option value=\"setunread\">{$lang['pm_set_unread']}</option></optgroup></select>&nbsp;&nbsp;<input class=\"bbcodes\" type=\"submit\" value=\"{$lang['b_start']}\" /></td></tr></table></form>";
	
	if( $i ) {
		
		$tpl->set( '{pmlist}', $pmlist );

			$onload_scripts[] = <<<HTML
$('#pmlist').submit(function() {

	if( $(this).find('#pmlist_doaction').val() == 'del' ) {
	
	    DLEconfirmDelete( dle_del_agree, dle_confirm, function () {
			$('#pmlist').off('submit').submit();
		} );
		
		return false;
	}
	
	return true;
});
HTML;
	
	} else $tpl->set( '{pmlist}', "<span class=\"pm-no-messages\">".$lang['no_message']."</span>" );
	
	$tpl->compile( 'content' );
	$tpl->clear();
}
?>