<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '17.2';
$config['cache_id'] = clear_static_cache_id(false);

$config['post_new'] = '24';
$config['post_updated'] = '24';
$config['max_complaints'] = '3';
$config['last_comm_nummers'] = $config['comm_nummers'];

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_poll` ADD `closed` TINYINT(1) NOT NULL DEFAULT '0', ADD `date_closed` VARCHAR(15) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_complaint_news` TINYINT(1) NOT NULL DEFAULT '1', ADD `allow_complaint_comments` TINYINT(1) NOT NULL DEFAULT '1', ADD `allow_complaint_orfo` TINYINT(1) NOT NULL DEFAULT '1'";

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