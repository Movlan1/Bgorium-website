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
 File: mass_user_actions.php
-----------------------------------------------------
 Use: Bulk actions on users
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editusers'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if (!isset($_SESSION['admin_user_referrer'])) {
	$_SESSION['admin_user_referrer'] = '?mod=editusers&amp;action=list';
}

$selected_users = isset($_REQUEST['selected_users']) ? $_REQUEST['selected_users'] : array();

if( ! $selected_users ) {
	msg( "error", $lang['mass_error'], $lang['massusers_denied'], $_SESSION['admin_user_referrer'] );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	die( "Hacking attempt! User not found" );

}

if( !check_referer($_SERVER['PHP_SELF']."?mod=editusers") ) {

	msg( "error", $lang['index_denied'], $lang['no_referer'], $_SESSION['admin_user_referrer'] );

}
	
if( $_POST['action'] == "mass_delete" ) {
	
	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['massusers_head'] );

	if (isset($_REQUEST['self_delete_user']) and $_REQUEST['self_delete_user'] == 'self_delete_user') {
		$self = '<input type="hidden" name="self_delete_user" value="self_delete_user">';
	} else $self = '';

	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['massusers_head']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center settingstd">{$lang['massusers_confirm']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=\"submit\" value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='{$_SESSION['admin_user_referrer']}'\">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">{$self}";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div></form>
HTML;


	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_delete") {

	$deleted = 0;

	$driver = DLEFiles::getDefaultStorage();
	$config['avatar_remote'] = intval($config['avatar_remote']);
	if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];
	
	DLEFiles::init( $driver );
			
	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		if( $id == 1 ) {
			msg( "error", $lang['mass_error'], $lang['user_undel'], $_SESSION['admin_user_referrer'] );
		}
	
		$row = $db->super_query("SELECT email, name, user_id, user_group FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
	
		if( !isset($row['user_id']) OR !$row['user_id'] ){
			msg("error", $lang['mass_error'], $lang['user_undel'], $_SESSION['admin_user_referrer']);
		}
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 ) {
			msg("error", $lang['mass_error'], $lang['user_undel'], $_SESSION['admin_user_referrer']);
		}

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '41', '{$row['name']}')" );

		deleteuserbyid( $id );

		if (isset($_REQUEST['self_delete_user']) and $_REQUEST['self_delete_user'] == 'self_delete_user') {

			if (strpos($config['http_home_url'], "//") === 0) {
				$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https:" . $config['http_home_url'] : $config['http_home_url'] = "http:" . $config['http_home_url'];
			} elseif (strpos($config['http_home_url'], "/") === 0) {
				$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'] : "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
			} elseif (isSSL() and stripos($config['http_home_url'], 'http://') !== false) {
				$config['http_home_url'] = str_replace("http://", "https://", $config['http_home_url']);
			}

			$mail = new dle_mail($config, false);

			$lang['selfdel_wait_5'] = str_replace('{name}', $row['name'], $lang['selfdel_wait_5']);
			$lang['selfdel_wait_5'] = str_replace('{site}', $config['http_home_url'], $lang['selfdel_wait_5']);

			$mail->send($row['email'], $lang['selfdel_wait_4'], $lang['selfdel_wait_5']);
		}

		$deleted ++;
	}

	clear_cache(array('stats'));
	@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
	if( count( $selected_users ) == $deleted ) {
		msg( "success", $lang['massusers_head'], $lang['massusers_delok'], $_SESSION['admin_user_referrer'] );
	} else {
		msg( "error", $lang['mass_error'], "$deleted $lang[mass_i] " . count( $selected_users ) . " $lang[massusers_confirm_2]", $_SESSION['admin_user_referrer'] );
	}

} elseif ($_POST['action'] == "mass_delete_comments") {

	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['massusers_head_1'] );


	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['massusers_head_1']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center">{$lang['massusers_confirm_3']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=\"submit\" value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='{$_SESSION['admin_user_referrer']}'\">
<input type=hidden name=action value=\"do_mass_delete_comments\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div></form>
HTML;

	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_delete_comments") {

	foreach ( $selected_users as $id ) {

		$id = intval( $id );
		
		$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' WHERE user_id ='$id'" );
		deletecommentsbyuserid($id);

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '42', '{$id}')" );

	}

	clear_cache(array('news_', 'comm_', 'full_', 'stats'));
	msg( "success", $lang['massusers_head_1'], $lang['massusers_comok'], $_SESSION['admin_user_referrer'] );

} elseif ($_POST['action'] == "mass_move_to_group") {

	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['massusers_head_2'] );


	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['massusers_head_2']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center settingstd">{$lang['massusers_confirm_4']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
{$lang['user_acc']} <select name=\"editlevel\" class=\"uniform\">".get_groups()."</select> {$lang['user_gtlimit']} <input data-rel=\"calendar\" class=\"form-control\" style=\"width:190px;\" dir=\"auto\" name=\"time_limit\" id=\"time_limit\" value=\"\"><i class=\"help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left\" data-rel=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-content=\"{$lang['hint_glhel']}\" ></i>
<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=\"submit\" value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='{$_SESSION['admin_user_referrer']}'\">
<input type=hidden name=action value=\"do_mass_move_to_group\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div></form>
HTML;

	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_move_to_group") {

	$editlevel = intval( $_POST['editlevel'] );
	$time_limit = trim( $_POST['time_limit'] ) ? strtotime( $_POST['time_limit'] ) : "";

	if( ! $user_group[$editlevel]['time_limit'] ) $time_limit = "";

	if ($member_id['user_group'] != 1 AND $editlevel < 2 ) 
		msg( "error", $lang['mass_error'], $lang['admin_not_access'], $_SESSION['admin_user_referrer'] );

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		$row = $db->super_query( "SELECT name, user_group FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
			msg( "error", $lang['mass_error'], $lang['edit_not_admin'], $_SESSION['admin_user_referrer'] );

		$db->query( "UPDATE " . USERPREFIX . "_users SET user_group='$editlevel', time_limit='$time_limit' WHERE user_id ='$id'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '43', '{$row['name']}')" );
	}

	msg( "success", $lang['massusers_head_2'], $lang['massusers_groupok']." <b>".$user_group[$editlevel]['group_name']."</b>", $_SESSION['admin_user_referrer'] );

} elseif ($_POST['action'] == "mass_move_to_ban") {

	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['massusers_head_3'] );


	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['massusers_head_3']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100">{$lang['massusers_confirm_5']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<div style=\"width:350px;\" align=\"left\">{$lang['ban_date']} <input dir=\"auto\" class=\"form-control text-center\" style=\"width:60px;\" name=\"banned_date\" value=\"0\"><i class=\"help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left\" data-rel=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-content=\"{$lang['hint_bandescr']}\" ></i>
<br><br>{$lang['ban_descr']}<br><textarea dir=\"auto\" class=\"classic\" style=\"width:100%; height:80px;\" name=\"banned_descr\"></textarea>
<br><br></div>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=\"submit\" value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='{$_SESSION['admin_user_referrer']}'\">
<input type=hidden name=action value=\"do_mass_move_to_ban\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div></form>
HTML;

	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_move_to_ban") {

	$parse = new ParseFilter();
	$parse->safe_mode = true;
	$banned_from = $db->safesql($member_id['name']);
	$banned_descr = $db->safesql($parse->BB_Parse($parse->process($_POST['banned_descr']), false));
	$this_time = time();
	$banned_date = intval($_POST['banned_date']);
	$this_time = $banned_date ? $this_time + ($banned_date * 60 * 60 * 24) : 0;

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		$row = $db->super_query( "SELECT name, user_group FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 ) {
			msg("error", $lang['mass_error'], $lang['edit_not_admin'], $_SESSION['admin_user_referrer']);
		}

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '44', '{$row['name']}')" );

		$row = $db->super_query( "SELECT users_id, days FROM " . USERPREFIX . "_banned WHERE users_id = '{$id}'" );
		
		if( !isset($row['users_id']) ) {
			$db->query("INSERT INTO " . USERPREFIX . "_banned (users_id, descr, date, days, banned_from) values ('{$id}', '{$banned_descr}', '{$this_time}', '{$banned_date}', '{$banned_from}')");
		} else {
			$db->query( "UPDATE " . USERPREFIX . "_banned SET descr='{$banned_descr}', days='{$banned_date}', date='{$this_time}', banned_from='{$banned_from}' WHERE users_id = '{$id}'" );
		}
		
		@unlink( ENGINE_DIR . '/cache/system/banned.json' );

		$db->query( "UPDATE " . USERPREFIX . "_users SET banned='yes' WHERE user_id ='$id'" );


	}

	msg( "success", $lang['massusers_head_3'], $lang['massusers_banok'], $_SESSION['admin_user_referrer']);

} elseif ($_POST['action'] == "mass_delete_pm") {

	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['massusers_head_4'] );


	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['massusers_head_4']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center">{$lang['massusers_confirm_6']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=\"submit\" value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='{$_SESSION['admin_user_referrer']}'\">
<input type=hidden name=action value=\"do_mass_delete_pm\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div></form>
HTML;

	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_delete_pm") {

	foreach ( $selected_users as $id ) {

		$id = intval( $id );
		$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='$id'" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '45', '{$row['name']}')" );

		deletepmuserbyid($id);
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread='0', pm_all='0'  WHERE user_id ='{$id}'" );

	}

	msg( "success", $lang['massusers_head_4'], $lang['massusers_pm_ok'], $_SESSION['admin_user_referrer'] );

} elseif ($_POST['action'] == "mass_rejectrequests") {

	if ($_POST['text']) {

		$parse = new ParseFilter();
		$parse->safe_mode = true;
		$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
		$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
		$parse->allowbbcodes = false;

		$message = <<<HTML
{$lang['selfdel_wait_6']}

[quote]{$_POST['text']}[/quote]
HTML;

		$message = $db->safesql($parse->BB_Parse($parse->process(trim($message)), false));

		if ($config['mail_pm']) {

			$mail_template = $db->super_query("SELECT * FROM " . PREFIX . "_email WHERE name='pm' LIMIT 0,1");
			$mail = new dle_mail($config, $mail_template['use_html']);

			if (strpos($config['http_home_url'], "//") === 0) $slink = "https:" . $config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
			else $slink = $config['http_home_url'];

			$slink = $slink . "index.php?do=pm";

			$mail_template['template'] = stripslashes($mail_template['template']);
			$mail_template['template'] = str_replace("{%date%}", langdate("j F Y H:i", $_TIME), $mail_template['template']);
			$mail_template['template'] = str_replace("{%fromusername%}", $member_id['name'], $mail_template['template']);
			$mail_template['template'] = str_replace("{%title%}", $lang['selfdel_wait_4'], $mail_template['template']);
			$mail_template['template'] = str_replace("{%url%}", $slink, $mail_template['template']);

			$mail_message = stripslashes(stripslashes($message));

			if (!$mail_template['use_html']) {
				$mail_message = str_replace("<br>", "\n", $mail_message);
				$mail_message = str_replace('&quot;', '"', $mail_message);
				$mail_message = strip_tags($mail_message);
			}

			$mail_template['template'] = str_replace("{%text%}", $mail_message, $mail_template['template']);

		}

	}


	foreach ($selected_users as $id) {

		$id = intval($id);

		$row = $db->super_query("SELECT email, name, user_id FROM " . USERPREFIX . "_users WHERE user_id = '{$id}'");

		if (!isset($row['user_id']) or !$row['user_id']) {
			msg("error", $lang['addnews_error'], $lang['user_nouser'], $_SESSION['admin_user_referrer'] );
		}

		$db->query("DELETE FROM " . USERPREFIX . "_users_delete WHERE user_id='{$row['user_id']}'");

		if ($_POST['text']) {

			$db->query("INSERT INTO " . USERPREFIX . "_conversations (subject, created_at, updated_at, sender_id, recipient_id) values ('{$lang['selfdel_wait_4']}', '{$_TIME}', '{$_TIME}', '{$member_id['user_id']}', '{$row['user_id']}')");
			$conversation_id = $db->insert_id();
			$db->query("INSERT INTO " . USERPREFIX . "_conversation_users (user_id, conversation_id) values ('{$row['user_id']}', '{$conversation_id}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)");
			$db->query("INSERT INTO " . USERPREFIX . "_conversations_messages (conversation_id, sender_id, content, created_at) values ('{$conversation_id}', '{$member_id['user_id']}', '{$message}', '{$_TIME}')");

			$db->query("UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$row['user_id']}'");
			
			if ($config['mail_pm']) {

				$send_message = str_replace("{%username%}", $row['name'], $mail_template['template']);

				$mail->send($row['email'], $lang['selfdel_wait_4'], $send_message);
			}
		}

	}

	header("Location: ?mod=editusers");
	die();

} else {

	msg( "info", $lang['mass_noact'], $lang['mass_noact_1'], $_SESSION['admin_user_referrer'] );

}
?>