<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '16.1';
$config['cache_id'] = clear_static_cache_id(false);

$config['backup_remote'] = '0';
$config['disable_short'] = "0";
$config['disable_full'] = "0";
$config['rss_params'] = 'xmlns:content=&quot;http://purl.org/rss/1.0/modules/content/&quot; xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:media=&quot;http://search.yahoo.com/mrss/&quot; xmlns:atom=&quot;http://www.w3.org/2005/Atom&quot;';
$config['rss_turboparams'] = "xmlns:yandex=&quot;http://news.yandex.ru&quot; xmlns:media=&quot;http://search.yahoo.com/mrss/&quot; xmlns:turbo=&quot;http://turbo.yandex.ru&quot;";
$config['rss_dzenparams'] = "xmlns:content=&quot;http://purl.org/rss/1.0/modules/content/&quot; xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:media=&quot;http://search.yahoo.com/mrss/&quot; xmlns:atom=&quot;http://www.w3.org/2005/Atom&quot; xmlns:georss=&quot;http://www.georss.org/georss&quot;";

if ($config['force_webp']) $config['force_webp'] = 'webp';

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_plugins` CHANGE `needplugin` `needplugin` VARCHAR(255) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `max_downloads` SMALLINT(6) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` ADD INDEX `allow_rss_turbo` (`allow_rss_turbo`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` ADD INDEX `allow_rss_dzen` (`allow_rss_dzen`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` ADD INDEX `editdate` (`editdate`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `twofactor_secret` VARCHAR(16) NOT NULL DEFAULT ''";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_downloads_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_downloads_log (
	`id` int(11) unsigned NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default '0',
	`ip` varchar(46) NOT NULL default '',
	`file_id` int(11) NOT NULL default '0',
	`date` int(11) unsigned NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `user_id` (`user_id`),
	KEY `ip` (`ip`),
	KEY `file_id` (`file_id`),
	KEY `date` (`date`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

foreach ($tableSchema as $table) {
	$db->query($table, false);
}

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>