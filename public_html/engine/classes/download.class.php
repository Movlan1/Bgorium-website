<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2025 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: download.class.php
-----------------------------------------------------
 Use: Download files
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class download {
	
	public $properties = array ();

	public $range = 0;
	
	function __construct($path, $name, $driver) {

		DLEFiles::init();
			
		if ( !DLEFiles::FileExists( $path, $driver ) ) {
			header( "HTTP/1.1 403 Forbidden" );
			die ( "The file was not found on the server" );
		}
		
		$size = DLEFiles::Size( $path, $driver );
		$type = DLEFiles::MimeType( $path, $driver );

		if ( DLEFiles::$error ){
			header( "HTTP/1.1 403 Forbidden" );
			echo DLEFiles::$error;
			die ();
		}
		
		$this->properties = array ('path' => $path, 'name' => $name, 'disk' => $driver, 'type' => $type, 'size' => $size);
		
		if (isset($_SERVER['HTTP_RANGE'])) {

			$this->range = $_SERVER['HTTP_RANGE'];
			$this->range = str_replace("bytes=", "", $this->range);
			$this->range = str_replace("-", "", $this->range);

		} else {

			$this->range = 0;
		}

		if ($this->range > $this->properties['size']) $this->range = 0;

	}
	
	function download_file() {

		if ($this->range) {
			header($_SERVER['SERVER_PROTOCOL'] . " 206 Partial Content");
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . " 200 OK");
		}

		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header( "Cache-Control: private", false);

		if( $this->properties['type'] ) {
			header( "Content-Type: " . $this->properties['type'] );
		} else {
			header( "Content-Type: application/octet-stream" );
		}

		header( 'Content-Disposition: attachment; filename="' . $this->properties['name'] . '"' );
		header( "Content-Transfer-Encoding: binary" );
		header('Accept-Ranges: bytes');

		if ($this->range) {

			header("Content-Range: bytes {$this->range}-" . ($this->properties['size'] - 1) . "/" . $this->properties['size']);
			header("Content-Length: " . ($this->properties['size'] - $this->range));
			
		} else {

			header("Content-Length: " . $this->properties['size']);
		}

		header("Connection: close");
 		
		@ini_set( 'max_execution_time', 0 );
		@set_time_limit(0);
		
		$this->_download();
	}
	
	function _download() {

		@ob_end_clean();
		
		$handle = DLEFiles::ReadStream( $this->properties['path'], $this->properties['disk']);
	
		if ( DLEFiles::$error ){
			header( "HTTP/1.1 403 Forbidden" );
			echo DLEFiles::$error;
			die ();
		}
		
		if (is_resource($handle)) {
			fseek($handle, $this->range );

			while ( !feof( $handle ) ) {
				print( fread( $handle, 8192 ) );
				ob_flush();
				flush();
			}
			
			fclose( $handle );
		}
	}

}
