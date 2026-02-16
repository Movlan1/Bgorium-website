<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '15.1';
$config['news_indexnow'] = '0';
$config['schema_org'] = "0";
$config['site_icon'] = "";
$config['site_type'] = "Person";
$config['pub_name'] = "";

	
$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_public_file_upload` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `allow_public_file_upload` = '1' WHERE id < '3'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` ADD `is_public` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static_files` ADD `is_public` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_plugins_files` ADD `filedisable` TINYINT(1) NOT NULL DEFAULT '1', ADD `filedleversion` VARCHAR(10) NOT NULL DEFAULT '', ADD `fileversioncompare` CHAR(2) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_plugins_logs` ADD `action_id` INT(11) NOT NULL DEFAULT '0'";

foreach($tableSchema as $table) {
	$db->query ($table, false);
}

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>