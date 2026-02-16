<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '15.0';
$config['image_driver'] = '0';
$config['watermark_type'] = "0";
$config['watermark_text'] = "Powered by DataLife Engine ©";
$config['watermark_font'] = "16";
$config['watermark_color_dark'] = "#000000";
$config['watermark_color_light'] = "#ffffff";
$config['watermark_rotate'] = "0";
$config['watermark_opacity'] = "100";
$config['remote_key_id'] = "";
$config['remote_secret_key'] = "";
$config['bucket_name'] = "";
$config['region_name'] = "";
$config['ftp_server'] = "";
$config['ftp_port'] = "";
$config['ftp_username'] = "";
$config['ftp_password'] = "";
$config['ftp_path'] = "";
$config['remote_url'] = "";
$config['local_on_fail'] = "1";
$config['image_remote'] = "1";
$config['comments_remote'] = "1";
$config['static_remote'] = "1";
$config['files_remote'] = "1";
$config['avatar_remote'] = "1";

unset($config['allow_share']);
unset($config['files_force']);

if( !is_dir( ROOT_DIR . "/uploads/icons" ) ) {

	@mkdir( ROOT_DIR . "/uploads/icons", 0777, true );
	@chmod( ROOT_DIR . "/uploads/icons", 0777 );

}

if( !is_dir( ROOT_DIR . "/uploads/shared" ) ) {

	@mkdir( ROOT_DIR . "/uploads/icons", 0777, true );
	@chmod( ROOT_DIR . "/uploads/icons", 0777 );

}
	
$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` ADD `driver` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments_files` ADD `driver` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static_files` ADD `driver` TINYINT(1) NOT NULL DEFAULT '0'";

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