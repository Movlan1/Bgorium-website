<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '15.3';
$config['allow_cat_sort'] = '1';
$config['alert_edit_now'] = "1";
$config['read_count_time'] = "5";

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` ADD `edited_now` VARCHAR(100) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_comments_rating` TINYINT(1) NOT NULL DEFAULT '0', ADD `force_comments_rating_count` MEDIUMINT(9) NOT NULL DEFAULT '0', ADD `force_comments_rating_group` SMALLINT(6) NOT NULL DEFAULT '4'";

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