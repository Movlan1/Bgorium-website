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
 File: filesystem.class.php
-----------------------------------------------------
 Use: DLE Files System
=====================================================
*/

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionProvider;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\FilesystemException;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client as WebDAVClient;

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

abstract class DLEFiles {

	private static $root = null;

	private static $local_on_remote_errors = null;
	private static $run_force_local = false;
	private static $base_local_url = '';
	
	public static $driver = null;	
	public static $error  = null;
	public static $remote_error = null;

	private static $storages = null;
	private static $filesystem = array();
	private static $storages_list = null;

	public static function init( $driver = null, $local_on_remote_errors = false, $root = null ) {
		global $config, $lang;

		self::$error = self::$remote_error = null;
		
		if ( defined('DLE_PHP_MIN') AND !DLE_PHP_MIN ) {
			$lang['stat_phperror'] = str_replace('{minversion}', DLE_PHP_MIN_VERSION, $lang['stat_phperror']);
			$lang['stat_phperror'] = str_replace('{version}', PHP_VERSION, $lang['stat_phperror']);
			
			self::error($lang['stat_phperror']);
			return false;
		}

		if( !is_array( self::$storages ) ) {
			self::$storages = self::loadStorages();
		}

		if( is_null( $root ) ) {
			
			self::$root = ROOT_DIR.'/uploads/';
			self::$base_local_url = $config['http_home_url'] . 'uploads/';
			
		} else {
			
			$root = self::normalize_path( $root );
			
			if( $root ) {
				self::$root = ROOT_DIR.'/'. $root .'/';
				self::$base_local_url = $config['http_home_url'] . $root .'/';
			} else {
				self::$root = ROOT_DIR.'/';
				self::$base_local_url = $config['http_home_url'];
			}
			
		}
		
		if( is_null( $driver ) ) {

			self::$driver = self::$storages['default'];
			
		} elseif ( $driver ) {

			$driver = intval($driver);
			if( isset( self::$storages[$driver] ) ) self::$driver = $driver;
			else self::$driver = self::$storages['default'];
			
		}
			
		if( self::$driver > 0 AND $local_on_remote_errors ) {
			self::$local_on_remote_errors = true;
		}

		if( !isset( self::$filesystem[0] ) ) {

			try {

				$visibilityConverter = PortableVisibilityConverter::fromArray([
					'file' => [
						'public' => 0666,
						'private' => 0644
					],
					'dir' => [
						'public' => 0777,
						'private' => 0755
					]
				], "public");

				$adapter = new LocalFilesystemAdapter(self::$root, $visibilityConverter, LOCK_EX, LocalFilesystemAdapter::DISALLOW_LINKS);

				self::$filesystem[0] = new Filesystem($adapter, ['public_url' => self::$base_local_url, 'directory_visibility' => "public", 'visibility' => "public"]);
			} catch (Throwable $e) {

				self::error($e->getMessage());
				return false;
			} catch (FilesystemException $e) {
				self::error($e->getMessage());
				return false;
			}

		}

		if( self::$driver > 0  AND isset( self::$storages[self::$driver] )  ) {

			if( !isset(self::$filesystem[self::$driver]) ) {
				$adapter_info = self::$storages[self::$driver];

				if (!in_array($adapter_info['accesstype'], array("public", "private"))) {
					$adapter_info['accesstype'] = "public";
				}

				$visibilityConverter = PortableVisibilityConverter::fromArray([
					'file' => [
						'public' => 0666,
						'private' => 0644
					],
					'dir' => [
						'public' => 0777,
						'private' => 0755
					]
				], $adapter_info['accesstype']);


				try {

					$adapter_info['path'] = trim($adapter_info['path']);

					if ($adapter_info['path'] and  $adapter_info['type'] == '1' or $adapter_info['type'] == '2') {

						if (!$adapter_info['path']) $adapter_info['path'] = '/';
						else $adapter_info['path'] = '/' . trim($adapter_info['path'], '\\/') . '/';
					
					} elseif( $adapter_info['path'] ) {
						
						$adapter_info['path'] = trim($adapter_info['path'], '\\/');
					}

					if ($adapter_info['type'] == '1') {

						$adapter = new FtpAdapter(
							// Connection options
							FtpConnectionOptions::fromArray([
								'host' => $adapter_info['connect_url'],
								'port' => intval($adapter_info['connect_port']),
								'root' => $adapter_info['path'],
								'username' => $adapter_info['username'],
								'password' => $adapter_info['password'],
								'timeout' => 5
							]),
							null,
							null,
							$visibilityConverter
						);
					} elseif ($adapter_info['type'] == '2') {

						$adapter = new SftpAdapter(
							new SftpConnectionProvider(
								$adapter_info['connect_url'],
								$adapter_info['username'],
								$adapter_info['password'],
								null, // private key (optional, default: null) can be used instead of password, set to null if password is set
								null, // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
								intval($adapter_info['connect_port']),
								false, // use agent (optional, default: false)
								5, // timeout (optional, default: 10)
								0, // max tries (optional, default: 4)
								null, // host fingerprint (optional, default: null),
								null
							),
							$adapter_info['path'],
							$visibilityConverter
						);
					} elseif ($adapter_info['type'] == '3') {

						$clientoptions = [];

						if (trim($adapter_info['client_key']) and trim($adapter_info['secret_key'])) {

							$clientoptions['accessKeyId'] = trim($adapter_info['client_key']);
							$clientoptions['accessKeySecret'] = trim($adapter_info['secret_key']);
						}

						if (trim($adapter_info['region'])) {
							$clientoptions['region'] = trim($adapter_info['region']);
						}

						$clientoptions['sharedCredentialsFile'] = '';
						$clientoptions['sharedConfigFile'] = '';

						$client = new AsyncAws\SimpleS3\SimpleS3Client($clientoptions);

						$adapter = new League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter($client, $adapter_info['bucket'], $adapter_info['path'], new League\Flysystem\AsyncAwsS3\PortableVisibilityConverter($adapter_info['accesstype']));
					} elseif ($adapter_info['type'] == '4' or $adapter_info['type'] == '5') {

						if ($adapter_info['type'] == '4') {
							$clientoptions = ['endpoint' => 'https://storage.yandexcloud.net'];
						} else {
							$clientoptions = ['endpoint' => $adapter_info['connect_url']];
						}

						if (trim($adapter_info['client_key']) and trim($adapter_info['secret_key'])) {

							$clientoptions['accessKeyId'] = trim($adapter_info['client_key']);
							$clientoptions['accessKeySecret'] = trim($adapter_info['secret_key']);
						}

						if (trim($adapter_info['region'])) {
							$clientoptions['region'] = trim($adapter_info['region']);
						}

						$clientoptions['sharedCredentialsFile'] = '';
						$clientoptions['sharedConfigFile'] = '';

						$client = new AsyncAws\SimpleS3\SimpleS3Client($clientoptions);

						$adapter = new League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter($client, $adapter_info['bucket'], $adapter_info['path'], new League\Flysystem\AsyncAwsS3\PortableVisibilityConverter($adapter_info['accesstype']));
					} elseif ($adapter_info['type'] == '6') {

						$client = new WebDAVClient([
							'baseUri' => trim($adapter_info['connect_url']),
							'userName' => trim($adapter_info['username']),
							'password' => trim($adapter_info['password']),
						]);

						$adapter = new WebDAVAdapter($client);
					
					} else {

						self::$driver = 0;
						return false;
					}
					
					$adapter_config = ['directory_visibility' => $adapter_info['accesstype'], 'visibility' => $adapter_info['accesstype']];

					if( trim($adapter_info['http_url']) ) {
						$adapter_config['public_url'] = $adapter_info['http_url'];
					}

					self::$filesystem[self::$driver] = new Filesystem($adapter, $adapter_config );

				} catch (Throwable $e) {

					self::error($e->getMessage());
					return false;
				} catch (FilesystemException $e) {
					self::error($e->getMessage());
					return false;
				}
			}
			
		} else self::$driver = 0;

		return true;
	
	}
	
	public static function Read( $path, $driver = null ) {
		
		if( is_null( self::$driver ) ) {
			DLEFiles::init();
		}

		if( is_null( $driver ) ) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);
			
			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->read($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
			}
		
		}
		
		return false;
		
	}
	
	public static function Save( $path, $contents, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {

			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );
		
		if( is_object(self::$filesystem[$driver]) ) {
			
			try {

				self::$filesystem[$driver]->write($path, $contents);
				return true;
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		if( self::$run_force_local ) {
			
			try {

				self::$filesystem[0]->write($path, $contents);
				return true;
			
			} catch(Throwable $e) {
				
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );

			}

			self::$run_force_local = false;
		
		}
		
		return false;
		
	}

	public static function FileExists( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->fileExists($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );

			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
			}
		
		}
		
		return false;
		
	}

	public static function Size( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->fileSize($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		return 0;
		
	}

	public static function Checksum($path, $driver = null)
	{

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if ( !is_object(self::$filesystem[$driver]) ) {
			DLEFiles::init($driver);
		}

		$path = self::normalize_path($path);

		if ( is_object(self::$filesystem[$driver]) ) {

			try {

				return self::$filesystem[$driver]->checksum($path);

			} catch (Throwable $e) {

				self::error($e->getMessage());

			} catch (FilesystemException $e) {

				self::error($e->getMessage());
			}
		}

		return '';
	}

	public static function Delete( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->delete($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
			}
		
		}
		
		return false;
		
	}
	
	public static function ReadStream( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );
		
		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->readStream($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
			}
		
		}
		
		return false;
		
	}
	
	public static function WriteStream( $path, $stream, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$path = self::normalize_path( $path );
		
		if( is_object(self::$filesystem[$driver]) ) {
			
			try {

				self::$filesystem[$driver]->writeStream($path, $stream);
				return true;
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		if( self::$run_force_local ) {
			
			try {

				self::$filesystem[0]->writeStream($path, $stream);
				return true;
			
			} catch(Throwable $e) {
				
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );

			}

			self::$run_force_local = false;
		
		}
		
		return false;
		
	}
	
	public static function Rename( $source, $destination, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}

		}
		
		$source = self::normalize_path( $source );
		$destination = self::normalize_path( $destination );
		
		if( is_object(self::$filesystem[$driver]) ) {
			
			try {

				self::$filesystem[$driver]->move($source, $destination);
				return true;
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		return false;
		
	}
	
	public static function MimeType( $path ) {
		
		$path = self::normalize_path( $path );
		
		try {
			$detector = new League\MimeTypeDetection\ExtensionMimeTypeDetector();
			return $detector->detectMimeTypeFromPath($path);
		
		} catch(Throwable $e) {
				
			self::error( $e->getMessage() );
			
		} catch (FilesystemException $e) {
			
			self::error( $e->getMessage() );
			
		}
		
		return false;
		
	}
	
	public static function ListDirectory( $path, $allowed_ext = null, $driver = null, $recursive = false ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}
		}
		
		$path = self::normalize_path( $path );
		$listing = array();

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {

				$listing = self::$filesystem[$driver]->listContents($path)->sortByPath();
				
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}

		$array = array('dirs' => array(), 'files' => array());

		foreach ($listing as $item) {
			
			if( $path == $item->path() ) continue;
			
			$path_info = $item->path();
			
			$finfo = pathinfo( $path_info );
			$name = $finfo['basename'];
			
			if ($item instanceof \League\Flysystem\FileAttributes) {
					
				if( is_array( $allowed_ext ) ) {
					$ext = strtolower($finfo['extension']);
					if(!$ext OR !in_array( $ext, $allowed_ext )) continue;
				}
				
				$array['files'][] = array('path' => $path_info, 'name' => $name, 'size' => $item->fileSize() );
			
			} elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {

				$array['dirs'][] = array('path' => $path_info, 'name' => $name );

			}
		}
	
		return $array;
		
	}

	public static function DeleteDirectory( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}
		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->deleteDirectory($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		return false;
		
	}
	
	public static function CreateDirectory( $path, $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}
			
		}
		
		$path = self::normalize_path( $path );

		if( is_object(self::$filesystem[$driver]) ) {
			
			try {
				
				return self::$filesystem[$driver]->createDirectory($path);
			
			} catch(Throwable $e) {
					
				self::error( $e->getMessage() );
				
			} catch (FilesystemException $e) {
				
				self::error( $e->getMessage() );
				
			}
		
		}
		
		return false;
		
	}

	public static function GetBaseURL( $driver = null ) {

		if (is_null(self::$driver)) {
			DLEFiles::init();
		}

		if (is_null($driver)) $driver = self::$driver;

		if (!isset(self::$filesystem[$driver]) OR !is_object(self::$filesystem[$driver])) {
			DLEFiles::init($driver);

			if (!is_object(self::$filesystem[$driver])) {
				$driver = self::$storages['default'];
			}
		}

		if (is_object(self::$filesystem[$driver])) {

			try {

				return self::$filesystem[$driver]->publicUrl('');

			} catch (Throwable $e) {
				
				if( $driver ) {

					return isset(self::$storages[$driver]['http_url']) ? self::$storages[$driver]['http_url'] : '';

				} else {

					return self::$base_local_url;

				}		

			}
		}

		return '';

	}

	private static function normalize_path( $path ) {
	
		$path = trim(str_replace(chr(0), '', (string)$path));
		$path = str_replace(array('/', '\\'), '/', $path);

		if( !$path ) return '';
		
		if (preg_match('#\p{C}+#u', $path)) {
			return '';
		}
	
		$path_parts = pathinfo( $path );

		$filename = $path_parts['basename'];
		
		$parts = array_filter(explode('/', $path_parts['dirname']), 'strlen');
		
		$absolutes = array();
		
		foreach ($parts as $part) {
			$part = trim($part);
			
			if ('.' == $part OR '..' == $part OR !$part) continue;
			
			$absolutes[] = $part;
		}
	
		$path = implode('/', $absolutes);
	
		if ( $path ) {
			$path = $path.'/';
		}
		
		if( $filename ) {
			$path .= $filename;
		}
	
		if( is_null( self::$root ) ) {
			$root = ROOT_DIR.'/';
		} else {
			$root = self::$root;
		}
		
		if(stripos ($path, $root) === 0) {
			$path = str_ireplace($root, '', $path);
		}
		
		return $path;
	
	}
	
	private static function error( $message ) {
		
		$message = str_ireplace( ROOT_DIR, '', $message );
		
		if( self::$driver > 0 AND self::$local_on_remote_errors) {
			
			self::$driver = 0;
			self::$remote_error = $message;
			self::$run_force_local = true;

		} else {
			
			self::$error = $message;
			
		}
		
	}

	public static function getStorages() {

		if (is_null(self::$storages_list)) {
			self::$storages = self::loadStorages();
		}

		return self::$storages_list;

	}

	public static function getDefaultStorage() {

		if (!is_array(self::$storages)) {
			self::$storages = self::loadStorages();
		}

		return self::$storages['default'];
	}

	private static function loadStorages() {
		global $db;

		if ( file_exists( ENGINE_DIR . '/cache/system/storages.php' ) ) {
			include_once ( ENGINE_DIR . '/cache/system/storages.php' );
			
			if( isset($storages) ) {
				$storages = json_decode($storages, true);
				if( !is_array($storages)) unset($storages);
			}

		}

		if ( !isset($storages) ) {
			$storages = array( 'default' => 0) ;

			$db->query("SELECT * FROM " . PREFIX . "_storage WHERE `enabled`='1' ORDER BY posi ASC, id DESC");

			while ($row = $db->get_row()) {

				$storages[$row['id']] = array();
				
				if( $row['default_storage'] ) {
					$storages['default'] = $row['id'];					
				}

				foreach ($row as $key => $value) {
					$storages[$row['id']][$key] = stripslashes($value);
				}
			}

			$db->free();

			$save_data = json_encode($storages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			$save_data = str_replace("'", "\'", $save_data);
			
			$save_data = "<?php \n\n//Storages Configurations\n\n\$storages = '" . $save_data . "';\n\n?>";

			file_put_contents(ENGINE_DIR . '/cache/system/storages.php', $save_data, LOCK_EX);
			@chmod(ENGINE_DIR . '/cache/system/storages.php', 0666);

		}

		self::$storages_list = array();

		foreach ($storages as $value) {
			if ( isset( $value['id'] ) ) {
				self::$storages_list[$value['id']] = $value['name'];
			}
		}

		return $storages;

	}

	public static function FindDriver( $url ) {

		if (!is_array(self::$storages)) {
			self::$storages = self::loadStorages();
		}

		$url = parse_url($url);

		if ( isset($url['scheme']) ) {
			$url = $url['scheme'] . '://' . $url['host'];
		} else {
			$url = '//' . $url['host'];
		}

		foreach (self::$storages as $value) {
			if ( isset( $value['id'] ) ) {
				if (isset($value['http_url']) AND stripos($value['http_url'], $url) === 0) {
					return $value['id'];
				}	
			}

		}

		return 0;
	}

}
