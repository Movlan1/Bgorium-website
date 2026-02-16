<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = "14.1";
$config['tags_separator'] = ", ";
$config['session_timeout'] = "0";

$config['speedbar_separator'] = " ".$config['speedbar_separator']." ";
$config['category_separator'] = " ".$config['category_separator']." ";

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `rating_type` TINYINT(1) NOT NULL DEFAULT '-1'";

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