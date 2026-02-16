<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '18.1';
$config['cache_id'] = clear_static_cache_id(false);

$config['ai_completion_tokens'] = '';
$config['protected_groups'] = '';

unset($config['allow_yandex_turbo']);
unset($config['rss_turboparams']);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` DROP `enable_turbo`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post_extras` DROP `allow_rss_turbo`";
$tableSchema[] = "CREATE INDEX approve_id ON " . PREFIX . "_comments(approve, id)";
$tableSchema[] = "CREATE INDEX approve_id ON " . PREFIX . "_post(approve, id)";

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

listdir(ENGINE_DIR . '/editor/jscripts/tiny_mce/plugins/codemirror/codemirror');
listdir(ENGINE_DIR . '/skins/codemirror');
listdir(ENGINE_DIR . '/classes/composer/vendor/async-aws/core/src/Test');
listdir(ENGINE_DIR . '/classes/composer/vendor/symfony/http-client-contracts/Test');
listdir(ENGINE_DIR . '/classes/composer/vendor/paragonie/constant_time_encoding/tests');

@unlink(ENGINE_DIR . '/modules/pm_alert.php');
@unlink(ENGINE_DIR . '/classes/mobiledetect.class.php');
@unlink(ENGINE_DIR . '/ajax/sitemap.php');
@unlink(ENGINE_DIR . '/ajax/bbcode.php');
@unlink(ENGINE_DIR . '/modules/bbcode.php');
@unlink(ENGINE_DIR . '/inc/include/inserttag.php');
@unlink(ENGINE_DIR . '/opensearch.php');
@unlink(ENGINE_DIR . '/classes/js/typograf.min.js');