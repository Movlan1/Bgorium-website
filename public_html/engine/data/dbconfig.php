<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

define ("DBHOST", "localhost");

define ("DBNAME", "bgorho_data");

define ("DBUSER", "bgorho_data");

define ("DBPASS", "bgorium_2390%");

define ("PREFIX", "dle");

define ("USERPREFIX", "dle");

define ("COLLATE", "utf8mb4");

define('SECURE_AUTH_KEY', '2g*eezrj8~nyhV-GsacM-#<N?/96 I3j;mB|rD3/#a-D+xTUUHvi3`!0jZ~B XlA');

$db = new db;

?>