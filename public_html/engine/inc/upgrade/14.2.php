<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = "14.3";
if( $config['simple_reply'] ) $config['simple_reply'] = "2";

unset( $config['extra_login'] );

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>