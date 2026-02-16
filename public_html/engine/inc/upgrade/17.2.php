<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '17.3';
$config['cache_id'] = clear_static_cache_id(false);

$config['www_redirect'] = '0';
$config['max_pm_list'] = '20';
$config['allowed_country'] = '';
$config['declined_country'] = '';
$config['allowed_panel_country'] = '';
$config['declined_panel_country'] = '';
$config['country_decline_reason'] = '';

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banners` ADD `comments_place` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `disable_index` TINYINT(1) NOT NULL DEFAULT '0'";

foreach ($tableSchema as $table) {
	$db->query($table, false);
}

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?php \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>