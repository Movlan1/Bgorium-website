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
 File: storage.php
-----------------------------------------------------
 Use: The management of storages
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1  ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

function showRow($title = "", $description = "", $field = "", $class = "") {

	if ($class) {
		$class = " class=\"{$class}\"";
	}
	echo "<tr{$class}>
        <td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td>
        <td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td>
        </tr>";
}
function clean_array($a) {
	global $db;

	$a = html_entity_decode($a, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'utf-8');
	$a = htmlspecialchars(strip_tags($a), ENT_QUOTES, 'utf-8');
	return $db->safesql($a);

}

function makeDropDown($options, $name, $selected, $optional = false) {

	if (!$optional) {
		$optional = "";
	}

	$output = "<select class=\"uniform\" name=\"$name\" {$optional}>\r\n";

	foreach ($options as $value => $description) {

		$output .= "<option value=\"{$value}\"";

		if ($selected == $value) {
			$output .= " selected ";
		}

		if (is_array($description)) {

			if (isset($description['icon']) and $description['icon']) {
				$output .= " data-content=\"<span class='select-icon'><img src='language/{$value}/{$description['icon']}'></span><span class='select-descr'>{$description['name']}</span>\" ";
			}

			$output .= ">{$description['name']}</option>\n";
		} else {
			$output .= ">{$description}</option>\n";
		}
	}

	$output .= "</select>";

	return $output;
}

if ($_GET['action'] == "setdefault") {
	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$id = intval($_GET['id']);

	$db->query("UPDATE " . PREFIX . "_storage SET `default_storage`='0'");
	$db->query("UPDATE " . PREFIX . "_storage SET `default_storage`='1', `enabled`='1' WHERE id='{$id}'");
	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '135', '')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_GET['action'] == "disable") {
	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$id = intval($_GET['id']);

	$db->query("UPDATE " . PREFIX . "_storage SET `default_storage`='0', `enabled`='0' WHERE id='{$id}'");
	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '130', '')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_GET['action'] == "enable") {
	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$id = intval($_GET['id']);

	$db->query("UPDATE " . PREFIX . "_storage SET `enabled`='1' WHERE id='{$id}'");
	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '131', '')");

	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_POST['action'] == "mass_enable") {

	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	if (!isset($_POST['selected_ids']) or !is_array($_POST['selected_ids'])) {
		msg("error", $lang['mass_error'], $lang['mass_storage_err'], "?mod=storage");
	}

	foreach ($_POST['selected_ids'] as $id) {
		$id = intval($id);
		$db->query("UPDATE " . PREFIX . "_storage SET `enabled`='1' WHERE id='{$id}'");
	}

	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '133', '')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_POST['action'] == "mass_disable") {

	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	if (!isset($_POST['selected_ids']) or !is_array($_POST['selected_ids'])) {
		msg("error", $lang['mass_error'], $lang['mass_storage_err'], "?mod=storage");
	}

	foreach ($_POST['selected_ids'] as $id) {
		$id = intval($id);
		$db->query("UPDATE " . PREFIX . "_storage SET `default_storage`='0', `enabled`='0' WHERE id='{$id}'");
	}

	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '134', '')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_POST['action'] == "mass_delete") {

	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	if (!isset($_POST['selected_ids']) OR !is_array($_POST['selected_ids']) ) {
		msg("error", $lang['mass_error'], $lang['mass_storage_err'], "?mod=storage");
	}

	foreach ($_POST['selected_ids'] as $id) {
		$id = intval($id);
		$db->query("DELETE FROM " . PREFIX . "_storage WHERE id='{$id}'");
	}

	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '132', '')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}

if ($_GET['action'] == "delete") {

	if (!isset($_REQUEST['user_hash']) or !$_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$id = intval($_GET['id']);
	$row = $db->super_query("SELECT id, name FROM " . PREFIX . "_storage WHERE id='{$id}'");

	if (!isset($row['id'])) msg("error", $lang['storage_error'], $lang['storage_error']);

	$db->query("DELETE FROM " . PREFIX . "_storage WHERE id='{$row['id']}'");
	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '136', '{$row['id']}')");
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();
}


if ($_REQUEST['action'] == "doadd" OR $_REQUEST['action'] == "doedit") {

	if (!isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$storage = array_map("clean_array", $_POST['storage']);
	$storage['type'] = intval($storage['type']);

	if (!$storage['name']) msg("error", array('?mod=storage' => $lang['header_s_1'], '' => $lang['addnews_error']), $lang['storage_error_1'], "javascript:history.go(-1)");

	if( $storage['http_url'] AND stripos($storage['http_url'], "https://") !== 0 AND stripos($storage['http_url'], "http://") !== 0 AND stripos($storage['http_url'], "//") !== 0 ) {
		msg("error", array('?mod=storage' => $lang['header_s_1'], '' => $lang['addnews_error']), $lang['upload_error_8'], "javascript:history.go(-1)");
	}

	if ( !$storage['http_url'] AND $storage['type'] != 3 AND $storage['type'] != 4 AND $storage['type'] != 5 AND $storage['type'] != 6) {
		msg("error", array('?mod=storage' => $lang['header_s_1'], '' => $lang['addnews_error']), $lang['upload_error_8'], "javascript:history.go(-1)");
	}

	if ($storage['http_url'] AND substr($storage['http_url'], -1, 1) != '/') $storage['http_url'] .= '/';

	if($storage['type'] == 5) {
		$storage['connect_url'] = $storage['remote_endpoint'];
	}

	if ($storage['type'] == 6) {
		$storage['connect_url'] = $storage['webdav_baseurl'];
		$storage['username'] = $storage['webdav_user'];
		$storage['password'] = $storage['webdav_pass'];
	}

	if ($_REQUEST['action'] == "doedit") {

		$id = intval($_REQUEST['id']);

		if (!$id) msg("error", $lang['storage_error'], $lang['storage_error']);

		$row = $db->super_query("SELECT id, name, password FROM " . PREFIX . "_storage WHERE id='$id'");

		if (!isset($row['id'])) msg("error", $lang['storage_error'], $lang['storage_error']);

		if( !$storage['password'] ) $storage['password'] = $row['password'];

		$db->query("UPDATE " . PREFIX . "_storage SET name='{$storage['name']}', type='{$storage['type']}', accesstype='{$storage['accesstype']}', connect_url='{$storage['connect_url']}', connect_port='{$storage['connect_port']}', username='{$storage['username']}', password='{$storage['password']}', path='{$storage['path']}', http_url='{$storage['http_url']}', client_key='{$storage['client_key']}', secret_key='{$storage['secret_key']}', bucket='{$storage['bucket']}', region='{$storage['region']}' WHERE id='{$row['id']}'");
		$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '129', '{$storage['name']}')");

	} else {

		$db->query("INSERT INTO " . PREFIX . "_storage (name, type, accesstype, connect_url, connect_port, username, password, path, http_url, client_key, secret_key, bucket, region) values ('{$storage['name']}', '{$storage['type']}', '{$storage['accesstype']}', '{$storage['connect_url']}', '{$storage['connect_port']}', '{$storage['username']}', '{$storage['password']}', '{$storage['path']}', '{$storage['http_url']}', '{$storage['client_key']}', '{$storage['secret_key']}', '{$storage['bucket']}', '{$storage['region']}')");
		$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '128', '{$storage['name']}')");

	}
	
	@unlink(ENGINE_DIR . '/cache/system/storages.php');

	if (function_exists('opcache_reset')) {
		opcache_reset();
	}

	header("Location: ?mod=storage");
	die();

}

if ($_REQUEST['action'] === "add" OR $_REQUEST['action'] === "edit") {

	if ( $_REQUEST['action'] == "edit" ) {

		$header =  $lang['header_s_4'];
		$id = intval($_REQUEST['id']);
		
		if (!$id) msg("error", $lang['storage_error'], $lang['storage_error']);

		$row = $db->super_query("SELECT * FROM " . PREFIX . "_storage WHERE id='$id'");

		if (!isset($row['id'])) msg( "error", $lang['storage_error'], $lang['storage_error'] );

		$row = array_map("clean_array", $row);


	} else {

		$header =  $lang['header_s_3'];
		$row['id'] = '0';
		$row['name'] = '';
		$row['type'] ='1';
		$row['connect_url'] = '';
		$row['connect_port'] = '21';
		$row['username'] = '';
		$row['password'] = '';
		$row['bucket'] = '';
		$row['region'] = '';
		$row['client_key'] = '';
		$row['secret_key'] = '';
		$row['path'] = '';
		$row['http_url'] = '';
		$row['accesstype'] = '';
	}

	echoheader("<i class=\"fa fa-hdd-o position-left\"></i><span class=\"text-semibold\">{$lang['opt_storages']}</span>", array('?mod=storage' => $lang['header_s_1'], '' => $header));

	echo <<<HTML
<form action="?mod=storage" method="post" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="storage">
<input type="hidden" name="action" value="do{$_REQUEST['action']}">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<input type="hidden" name="id" value="{$row['id']}">
<div class="panel panel-default">
  <div class="panel-heading">
    {$header}
  </div>
  <table class="table table-striped">
HTML;

	showRow($lang['storage_name'], $lang['storage_name_d'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name=\"storage[name]\" value=\"{$row['name']}\">");
	showRow($lang['opt_sys_imfs'], $lang['opt_sys_imfsd'], makeDropDown(array("1" => $lang['opt_sys_imfs_2'], "2" => $lang['opt_sys_imfs_3'], "3" => $lang['opt_sys_imfs_4'], "4" => $lang['opt_sys_imfs_5'], "5" => $lang['opt_sys_imfs_6'], "6" => $lang['opt_sys_imfs_7']), "storage[type]", "{$row['type']}", "onchange=\"ShowOrHideRemote(this.value)\""));
	showRow($lang['opt_sys_imfsf'], $lang['opt_sys_imfsfd'], "<input dir=\"auto\" type=\"text\" name=\"storage[connect_url]\" value=\"{$row['connect_url']}\" class=\"form-control\">", "ftp-server");
	showRow($lang['opt_sys_imfsfp'], $lang['opt_sys_imfsfpd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" style=\"max-width:100px; text-align: center;\"  name='storage[connect_port]' value=\"{$row['connect_port']}\" >", "ftp-server");
	showRow($lang['opt_sys_imfsfu'], $lang['opt_sys_imfsfud'], "<input dir=\"auto\" type=\"text\" class=\"form-control\"  name='storage[username]' value=\"{$row['username']}\" >", "ftp-server");

	if ( !$row['password'] ) $pass_hidden = ''; else $pass_hidden = $lang['pass_hidden'];
	showRow($lang['opt_sys_imfsfpp'], $lang['opt_sys_imfsfppd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[password]' value=\"\" placeholder=\"{$pass_hidden}\">", "ftp-server");

	showRow($lang['opt_sys_imfwe'], $lang['opt_sys_imfwed'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[webdav_baseurl]' value=\"{$row['connect_url']}\" >", "webdav-server");
	showRow($lang['opt_sys_imfweu'], $lang['opt_sys_imfweud'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[webdav_user]' value=\"{$row['username']}\" >", "webdav-server");

	if (!$row['password']) $pass_hidden = ''; else $pass_hidden = $lang['pass_hidden'];
	showRow($lang['opt_sys_imfwep'], $lang['opt_sys_imfwepd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[webdav_pass]' value=\"\" placeholder=\"{$pass_hidden}\">", "webdav-server");

	showRow('EndPoint', $lang['opt_sys_imfend'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[remote_endpoint]' value=\"{$row['connect_url']}\" >", "cloud-endpoint");
	showRow('Client Key ID', $lang['opt_sys_imfski'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[client_key]' value=\"{$row['client_key']}\" >", "cloud-server");
	showRow('Secret Key', $lang['opt_sys_imfsks'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[secret_key]' value=\"{$row['secret_key']}\" >", "cloud-server");
	showRow($lang['opt_sys_imfskb'], $lang['opt_sys_imfskbd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[bucket]' value=\"{$row['bucket']}\" >", "cloud-server");
	showRow($lang['opt_sys_imfskr'], $lang['opt_sys_imfskrd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[region]' value=\"{$row['region']}\" >", "cloud-server");

	showRow($lang['opt_sys_imfsfpa'], $lang['opt_sys_imfsfpad'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[path]' value=\"{$row['path']}\" >", "remote-server webdav-hidden");
	showRow($lang['opt_sys_imfsfur'], $lang['opt_sys_imfsfurd'], "<input dir=\"auto\" type=\"text\" class=\"form-control\" name='storage[http_url]' value=\"{$row['http_url']}\" >", "remote-server");
	showRow($lang['opt_sys_fa'], $lang['opt_sys_fad'], makeDropDown(array("public" => $lang['files_public'], "private" => $lang['files_private']), "storage[accesstype]", "{$row['accesstype']}"));

	echo <<<HTML
	</table>
	<div class="panel-footer">
		<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
	</div>
</div>
</form>
<script>
	function ShowOrHideRemote(value) {

		if(value == '1' || value == '2' || value == '3' || value == '4' || value == '5' || value == '6') {
			$(".remote-server").show();
		} else {
			$(".remote-server").hide();
		}

		if(value == '1' || value == '2') {
			$(".ftp-server").show();
		} else {
			$(".ftp-server").hide();
		}
		
		if(value == '3' || value == '4' || value == '5') {
			$(".cloud-server").show();
		} else {
			$(".cloud-server").hide();
		}
		
		if(value == '5') {
			$(".cloud-endpoint").show();
		} else {
			$(".cloud-endpoint").hide();
		}

		if(value == '6') {
			$(".webdav-server").show();
			$(".webdav-hidden").hide();
		} else {
			$(".webdav-server").hide();
		}

	}

	ShowOrHideRemote('{$row['type']}');
</script>
HTML;
	echofooter();

} else {

	echoheader( "<i class=\"fa fa-hdd-o position-left\"></i><span class=\"text-semibold\">{$lang['opt_storages']}</span>", $lang['header_s_1'] );

echo <<<HTML
<form action="?mod=storage" method="post" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="storage">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['header_s_2']}
  </div>
HTML;

$entries = "";
$default_status = "";
$remote_default_status = false;

$db->query("SELECT * FROM " . PREFIX . "_storage ORDER BY posi ASC, id DESC");

while($row = $db->get_row()) {

	if ($row['enabled']) {
		$status =  "<span title=\"{$lang['storage_on']}\" class=\"text-success tip\"><b><i class=\"fa fa-check-circle\"></i></b></span>";
		$lang['led_active'] = $lang['opt_sys_r1'];
		$led_action = "disable";
	} else {
		$status = "<span title=\"{$lang['storage_off']}\" class=\"text-danger tip\"><b><i class=\"fa fa-exclamation-circle\"></i></b></span>";
		$lang['led_active'] = $lang['all_enable'];
		$led_action = "enable";
	}

	if ($row['default_storage']) {
		$default_status =  "<span title=\"{$lang['storage_default']}\" class=\"text-success tip\"><b><i class=\"fa fa-check-circle\"></i></b></span>";
		$remote_default_status = true;
	} else {
		$default_status = "";
	}


	$menu_link = <<<HTML
	<div class="btn-group">
		<a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
		<ul class="dropdown-menu text-left dropdown-menu-right">
		<li><a href="?mod=storage&action=edit&id={$row['id']}"><i class="fa fa-pencil-square-o position-left"></i>{$lang['word_ledit']}</a></li>
		<li><a href="?mod=storage&user_hash={$dle_login_hash}&action=setdefault&id={$row['id']}"><i class="fa fa-hand-o-up position-left"></i>{$lang['storage_setd']}</a></li>
		<li><a href="?mod=storage&user_hash={$dle_login_hash}&action={$led_action}&id={$row['id']}"><i class="fa fa-eye position-left"></i>{$lang['led_active']}</a></li>
		<li class="divider"></li>
		<li><a uid="{$row['id']}" class="dellink" href="?mod=storage"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['word_ldel']}</a></li>
		</ul>
	</div>
HTML;
		$type = array("1" => $lang['opt_sys_imfs_2'], "2" => $lang['opt_sys_imfs_3'], "3" => $lang['opt_sys_imfs_4'], "4" => $lang['opt_sys_imfs_5'], "5" => $lang['opt_sys_imfs_6'], "6" => $lang['opt_sys_imfs_7']);
		$type = $type[$row['type']];

		$entries .= "<tr class=\"dd-item dd-item-table\" data-id=\"{$row['id']}\">
		<td class=\"dd-handles\"></td>
        <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=storage&action=edit&id={$row['id']}'; return false;\" style=\"word-break: break-all;\"><div id=\"content_{$row['id']}\">{$row['name']}</div></td>
		<td class=\"cursor-pointer\" onclick=\"document.location = '?mod=storage&action=edit&id={$row['id']}'; return false;\" style=\"word-break: break-all;\">{$type}</td>
        <td class=\"cursor-pointer text-center\" onclick=\"document.location = '?mod=storage&action=edit&id={$row['id']}'; return false;\">{$default_status}</td>
        <td class=\"cursor-pointer text-center\" onclick=\"document.location = '?mod=storage&action=edit&id={$row['id']}'; return false;\">{$status}</td>
		<td class=\"cursor-pointer text-center\">{$menu_link}</td>
        <td class=\"cursor-pointer\"><input name=\"selected_ids[]\" value=\"{$row['id']}\" type=\"checkbox\" class=\"icheck\"></td>
        </tr>";

	}

	if(!$remote_default_status) {

		$default_status =  "<span title=\"{$lang['storage_default']}\" class=\"text-success tip\"><b><i class=\"fa fa-check-circle\"></i></b></span>";
		$menu_link = '';

	} else {
		$default_status = '';
		$menu_link = <<<HTML
	<div class="btn-group">
		<a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
		<ul class="dropdown-menu text-left dropdown-menu-right">
			<li><a href="?mod=storage&user_hash={$dle_login_hash}&action=setdefault&id=0"><i class="fa fa-hand-o-up position-left"></i>{$lang['storage_setd']}</a></li>
		</ul>
	</div>
HTML;
	}

	$status =  "<span title=\"{$lang['storage_on']}\" class=\"text-success tip\"><b><i class=\"fa fa-check-circle\"></i></b></span>";

	$entries = "<tr>
		<td></td>
        <td style=\"word-break: break-all;\">{$lang['opt_sys_imfs_1']}</td>
		<td style=\"word-break: break-all;\"></td>
        <td class=\"text-center\">{$default_status}</td>
        <td class=\"text-center\">{$status}</td>
		<td class=\"text-center\">{$menu_link}</td>
        <td></td>
        </tr>". $entries;

	$db->free();

echo <<<HTML
<div class="dd table-responsive" id="nestable">
    <table class="table table-xs table-hover">
      <thead>
      <tr>
		<th></th>
        <th>{$lang['storage_name']}</th>
		<th style="width: 20rem">{$lang['storage_type']}</th>
        <th class="text-center" style="width: 10rem">{$lang['storage_default']}</th>
		<th class="text-center" style="width: 10rem">{$lang['storage_enabled']}</th>
        <th style="width: 4.375rem">&nbsp;</th>
        <th style="width: 2.5rem"><input class="icheck" type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></th>
      </tr>
      </thead>
	  <tbody class="dd-list dd-table">
		{$entries}
	  </tbody>
	</table>
</div>
	<div class="panel-footer">
		<div class="pull-right">
		<a href="?mod=storage&action=add" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-plus-circle position-left"></i>{$lang['storage_add']}</a>
		<select class="uniform position-left" name="action" data-dropdown-align-right="auto">
		<option value="">{$lang['edit_selact']}</option>
		<option value="mass_enable">{$lang['all_enable']}</option>
		<option value="mass_disable">{$lang['opt_sys_r1']}</option>
		<option value="mass_delete">{$lang['edit_seldel']}</option>
		</select><input class="btn bg-brown-600 btn-sm btn-raised" type="submit" value="{$lang['b_start']}">
		</div>
	</div>
</div>
</form>


<div class="alert alert-info alert-styled-left alert-arrow-left alert-component">{$lang['opt_storeagehelp']}</div>
<script>  
<!--

function ckeck_uncheck_all() {
	var frm = document.optionsbar;
	for (var i=0;i<frm.elements.length;i++) {
		var elmnt = frm.elements[i];
		if (elmnt.type=='checkbox') {
			if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('tr').removeClass('warning'); }
			else{ elmnt.checked=true; $(elmnt).parents('tr').addClass('warning');}
		}
	}
	if(frm.master_box.checked == true){ frm.master_box.checked = false; }
	else{ frm.master_box.checked = true; }
	
	$(frm.master_box).parents('tr').removeClass('warning');
	
	$.uniform.update();

}

$(function(){

		$('.dd').nestable({
			listNodeName: 'tbody',
			itemNodeName: 'tr.dd-item',
			handleClass: 'dd-handles',
			emptyClass: 'dd-emptys',
			placeClass: 'dd-placeholders-table',
			placeElementDefault: '<tr class="dd-placeholders-table"><td></td><td></td><td></td><td></td><td</td><td></td><td></td><td></td></tr>',
			maxDepth: 1
		});

		$('#nestable').nestable().on('change',function(){
		
			var url = "action=storagesort&user_hash={$dle_login_hash}&list="+window.JSON.stringify($(this).nestable('serialize'));
				
			ShowLoading('');
			$.post('engine/ajax/controller.php?mod=adminfunction', url, function(data){
	
				HideLoading('');
	
				if (data != 'ok') {
					DLEPush.error('{$lang['cat_sort_fail']}');

				}
	
			});
			
		});

		$('.table').find('tr > td:last-child').find('input[type=checkbox]').on('change', function() {
			if($(this).is(':checked')) {
				$(this).parents('tr').addClass('warning');
			}
			else {
				$(this).parents('tr').removeClass('warning');
			}
		});

		$('.dellink').click(function(){

			var tag_name = $('#content_'+$(this).attr('uid')).text();
			var urlid = $(this).attr('uid');

		    DLEconfirmDelete( '{$lang['storage_del']} <b>&laquo;'+tag_name+'&raquo;</b>', '{$lang['p_confirm']}', function () {

				document.location="?mod=storage&user_hash={$dle_login_hash}&action=delete&id=" + urlid;

			} );

			return false;
		});

});
//-->
</script>
HTML;


	echofooter();

}