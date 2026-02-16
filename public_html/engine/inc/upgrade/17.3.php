<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '18.0';
$config['cache_id'] = clear_static_cache_id(false);

$config['allow_bots'] = '1';
$config['block_vpn'] = '1';
$config['feedback_groups'] = '1,2';
$config['use_cloudflare_country'] = '0';
$config['enable_ai'] = '0';
$config['ai_key'] = '';
$config['ai_endpoint'] = 'https://api.openai.com/v1/chat/completions';
$config['ai_mode'] = 'gpt-4o';
$config['ai_tokens'] = '800';
$config['ai_temperature'] = '0.7';
$config['ai_groups'] = '1,2';

unset($config['allow_admin_wysiwyg']);
unset($config['allow_static_wysiwyg']);
unset($config['allow_site_wysiwyg']);
unset($config['allow_quick_wysiwyg']);
unset($config['comments_mobile_editor']);
unset($config['parse_links']);

if ($config['allow_comments_wysiwyg'] != '-1') {
	$config['allow_comments_wysiwyg'] = '1';
} else $config['allow_comments_wysiwyg'] = '0';

if ($config['simple_reply'] == '2') {
	$config['simple_reply'] = '1';
}

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `flood_time` smallint(6) NOT NULL DEFAULT '0', ADD `max_c_negative` smallint(6) NOT NULL DEFAULT '0', ADD `max_n_negative` smallint(6) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_rss` ADD `allow_source` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banners` ADD `allowed_country` VARCHAR(255) NOT NULL DEFAULT '', ADD `not_allowed_country` VARCHAR(255) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` ADD `allowed_country` VARCHAR(255) NOT NULL DEFAULT '', ADD `not_allowed_country` VARCHAR(255) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banned` ADD `banned_from` VARCHAR(40) NOT NULL DEFAULT ''";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_conversations";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_conversations (
	`id` int(11) unsigned NOT NULL auto_increment,
	`subject` varchar(255) NOT NULL default '',
	`created_at` int(11) unsigned NOT NULL DEFAULT '0',
	`updated_at` int(11) unsigned NOT NULL DEFAULT '0',
	`sender_id` int(11) NOT NULL,
	`recipient_id` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `sender_id` (`sender_id`),
	KEY `recipient_id` (`recipient_id`),
	KEY `updated_at` (`updated_at`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_conversation_reads";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_conversation_reads (
	`user_id` int(11) unsigned NOT NULL,
	`conversation_id` int(11) unsigned NOT NULL,
	`last_read_at` int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY  (`user_id`,`conversation_id`),
	KEY `last_read_at` (`last_read_at`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_conversation_users";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_conversation_users (
	`user_id` int(11) unsigned NOT NULL,
	`conversation_id` int(11) unsigned NOT NULL,
	PRIMARY KEY  (`user_id`,`conversation_id`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_conversations_messages";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_conversations_messages (
	`id` int(11) unsigned NOT NULL auto_increment,
	`conversation_id` int(11) unsigned NOT NULL,
	`sender_id` int(11) NOT NULL,
	`content` text NOT NULL,
	`created_at` int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY  (`id`),
	KEY `sender_id` (`sender_id`),
	KEY `conversation_id` (`conversation_id`),
	KEY `created_at` (`created_at`)
) ENGINE=" . $storage_engine . " DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci";

if( isset($config['flood_time']) AND $config['flood_time'] ) {
	$config['flood_time'] = intval($config['flood_time']);
	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `flood_time` = '{$config['flood_time']}' WHERE id > 2";
}

$tableSchema[] = "UPDATE " . PREFIX . "_users SET pm_all='0', pm_unread='0'";

foreach ($tableSchema as $table) {
	$db->query($table, false);
}

$time = time() - (365 * 86400);
$sql = "SELECT p.subj, p.text, p.date, p.pm_read, recipient.user_id, uf.user_id AS user_from FROM " . PREFIX . "_pm p LEFT JOIN " . PREFIX . "_users uf ON p.user_from = uf.name LEFT JOIN " . USERPREFIX . "_users recipient ON p.user = recipient.user_id WHERE p.folder = 'inbox' AND recipient.lastdate > '{$time}'";

$sql_result = $db->query($sql, false);

if ($sql_result instanceof mysqli_result) {
	while ($row = $db->get_row($sql_result)) {

		$row['subj'] = $db->safesql($row['subj']);
		$row['text'] = $db->safesql($row['text']);

		$db->query("INSERT INTO " . PREFIX . "_conversations (subject, created_at, updated_at, sender_id, recipient_id) values ('{$row['subj']}', '{$row['date']}', '{$row['date']}', '{$row['user_from']}', '{$row['user_id']}')", false);
		$conversation_id = $db->insert_id();
		$db->query("INSERT INTO " . PREFIX . "_conversation_users (user_id, conversation_id) values ('{$row['user_id']}', '{$conversation_id}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)", false);
		$db->query("INSERT INTO " . PREFIX . "_conversations_messages (conversation_id, sender_id, content, created_at) values ('{$conversation_id}', '{$row['user_from']}', '{$row['text']}', '{$row['date']}')", false);

		if( $row['pm_read'] ) {
			$db->query("INSERT INTO " . PREFIX . "_conversation_reads (user_id, conversation_id, last_read_at) values ('{$row['user_id']}', '{$conversation_id}', '{$row['date']}') ON DUPLICATE KEY UPDATE last_read_at='{$row['date']}'", false);
			$db->query("UPDATE " . PREFIX . "_users SET pm_all=pm_all+1 WHERE user_id='{$row['user_id']}'");
		} else {
			$db->query("UPDATE " . PREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1 WHERE user_id='{$row['user_id']}'");
		}

	}
}

$db->query("DROP TABLE IF EXISTS " . PREFIX . "_pm", false);

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?php \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>