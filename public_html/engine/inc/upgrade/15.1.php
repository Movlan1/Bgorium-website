<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '15.2';
$config['recaptcha_score'] = '0.5';
$config['translit_url'] = "1";
$config['shared_remote'] = "1";
$config['sitemap_limit'] = "";
$config['sitemap_news_priority'] = "0.6";
$config['sitemap_stat_priority'] = "0.5";
$config['sitemap_cat_priority'] = "0.7";
$config['sitemap_news_changefreq'] = "weekly";
$config['sitemap_stat_changefreq'] = "monthly";
$config['sitemap_cat_changefreq'] = "daily";
$config['sitemap_news_per_file'] = "40000";

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` CHANGE `tpl` `tpl` VARCHAR(255) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `schema_org` VARCHAR(50) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_tags` CHANGE `tag` `tag` VARCHAR(100) CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_xfsearch` CHANGE `tagvalue` `tagvalue` VARCHAR(100) CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci NOT NULL DEFAULT ''";


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