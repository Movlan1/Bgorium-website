<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '17.1';
$config['cache_id'] = clear_static_cache_id(false);

$config['comments_mobile_editor'] = '1';
$config['quick_edit_mode'] = '0';
$config['images_uniqid'] = '0';
$config['sitemap_set_images'] = '0';
$config['indexnow_provider'] = 'yandex.com';

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_storage` ADD `posi` MEDIUMINT(9) NOT NULL DEFAULT '1'";

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