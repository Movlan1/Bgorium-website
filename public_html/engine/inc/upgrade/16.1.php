<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '17.0';
$config['cache_id'] = clear_static_cache_id(false);

$config['fastsearch_result'] = '5';

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_redirects` ADD `enabled` TINYINT(1) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_redirects` ADD INDEX `enabled` (`enabled`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_metatags` ADD `enabled` TINYINT(1) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_metatags` ADD INDEX `enabled` (`enabled`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_links` ADD `enabled` TINYINT(1) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_links` ADD INDEX `enabled` (`enabled`)";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `admin_links` TINYINT(1) NOT NULL DEFAULT '0', ADD `admin_meta` TINYINT(1) NOT NULL DEFAULT '0', ADD `admin_redirects` TINYINT(1) NOT NULL DEFAULT '0', ADD `allow_change_storage` TINYINT(1) NOT NULL DEFAULT '0', ADD `self_delete` TINYINT(1) NOT NULL DEFAULT '2'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static_files` CHANGE `driver` `driver` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` CHANGE `driver` `driver` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments_files` CHANGE `driver` `driver` MEDIUMINT(9) NOT NULL DEFAULT '0'";

$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `admin_links` = '1', `admin_meta` = '1', `admin_redirects` = '1', `allow_change_storage` = '1' WHERE id = '1'";
$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `self_delete` = '0' WHERE id < '3' OR id = '5'";
$tableSchema[] = "UPDATE " . PREFIX . "_static_files SET `driver` = '1' WHERE `driver` != '0'";
$tableSchema[] = "UPDATE " . PREFIX . "_files SET `driver` = '1' WHERE `driver` != '0'";
$tableSchema[] = "UPDATE " . PREFIX . "_comments_files SET `driver` = '1' WHERE `driver` != '0'";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_storage (
	`id` mediumint(9) NOT NULL auto_increment,
	`name` varchar(255) NOT NULL default '0',
	`type` smallint(6) NOT NULL default '0',
	`accesstype` varchar(10) NOT NULL default '',
	`connect_url` varchar(255) NOT NULL default '',
	`connect_port` mediumint(9) NOT NULL default '0',
	`username` varchar(255) NOT NULL default '',
	`password` varchar(255) NOT NULL default '',
	`path` varchar(255) NOT NULL default '',
	`http_url` varchar(255) NOT NULL default '',
	`client_key` varchar(255) NOT NULL default '',
	`secret_key` varchar(255) NOT NULL default '',
	`bucket` varchar(255) NOT NULL default '',
	`region` varchar(255) NOT NULL default '',
	`default_storage` tinyint(1) NOT NULL default '0',
	`enabled` tinyint(1) NOT NULL default '1',
	PRIMARY KEY  (`id`),
	KEY `enabled` (`enabled`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_users_delete";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_users_delete (
	`id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `user_id` (`user_id`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

if( isset($config['file_driver']) AND intval($config['file_driver']) ) {
	$config['file_driver'] = intval($config['file_driver']);

	if( $config['file_driver'] == 6 ) {
		$config['ftp_server'] = $config['webdav_baseurl'];
		$config['ftp_username'] = $config['webdav_user'];
		$config['ftp_password'] = $config['webdav_pass'];
	}
	if ($config['file_driver'] == 5) {
		$config['ftp_server'] = $config['remote_endpoint'];
	}

	$tableSchema[] =  "INSERT INTO " . PREFIX . "_storage (id, name, type, accesstype, connect_url, connect_port, username, password, path, http_url, client_key, secret_key, bucket, region, default_storage, enabled) values (1, 'External Storage', '{$config['file_driver']}', '{$config['files_access']}', '{$config['ftp_server']}', '{$config['ftp_port']}', '{$config['ftp_username']}', '{$config['ftp_password']}', '{$config['ftp_path']}', '{$config['remote_url']}', '{$config['remote_key_id']}', '{$config['remote_secret_key']}', '{$config['bucket_name']}', '{$config['region_name']}', '1', '1')";
}

foreach ($tableSchema as $table) {
	$db->query($table, false);
}

if( $config['file_driver'] ) {

	if ($config['image_remote']) $config['image_remote'] = '-1'; else $config['image_remote'] = '0';
	if ($config['comments_remote']) $config['comments_remote'] = '-1'; else $config['comments_remote'] = '0';
	if ($config['static_remote']) $config['static_remote'] = '-1'; else $config['static_remote'] = '0';
	if ($config['files_remote']) $config['files_remote'] = '-1'; else $config['files_remote'] = '0';
	if ($config['avatar_remote']) $config['avatar_remote'] = '-1'; else $config['avatar_remote'] = '0';
	if ($config['shared_remote']) $config['shared_remote'] = '-1'; else $config['shared_remote'] = '0';
	if ($config['backup_remote']) $config['backup_remote'] = '-1'; else $config['backup_remote'] = '0';

} else {

	$config['image_remote'] = '-1';
	$config['comments_remote'] = '-1';
	$config['static_remote'] = '-1';
	$config['files_remote'] = '-1';
	$config['avatar_remote'] = '-1';
	$config['shared_remote'] = '-1';
	$config['backup_remote'] = '-1';
	
}

unset($config['file_driver']);
unset($config['ftp_server']);
unset($config['ftp_port']);
unset($config['ftp_username']);
unset($config['ftp_password']);
unset($config['ftp_path']);
unset($config['webdav_baseurl']);
unset($config['webdav_user']);
unset($config['webdav_pass']);
unset($config['remote_endpoint']);
unset($config['remote_key_id']);
unset($config['remote_secret_key']);
unset($config['bucket_name']);
unset($config['region_name']);
unset($config['remote_url']);
unset($config['files_access']);


$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>