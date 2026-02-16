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
 File: seo.class.php
-----------------------------------------------------
 Use: Generate SEO shemes
=====================================================
*/
use Melbahja\Seo\Schema;
use Melbahja\Seo\Schema\Thing;
use Melbahja\Seo\MetaTags;
use Melbahja\Seo\Indexing;

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Interfaces/SeoInterface.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Interfaces/SchemaInterface.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Schema.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Schema/Thing.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/MetaTags.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Indexing.php'));
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/composer/vendor/melbahja/seo/src/Helper.php'));
	
abstract class DLESEO {
	
	private static $schema = null;
	
	public static function AddSchema($thing)
	{
		if ( self::$schema === null ) {
			self::$schema = new Schema($thing);
		} else {
			self::$schema->add($thing);
		}
	}
	
	public static function CompileSchema()
	{
		if ( self::$schema != null ) {
			
			return json_encode(self::$schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		
		} else return '';
		
	}
	
	public static function Thing(string $type, array $data = [], bool $need_context = true)
	{
		return new Thing($type, $data, $need_context);
	}
	
	public static function MetaTags($charset = 'utf-8')
	{
		$meta = new MetaTags();

		if( !is_string($charset) ) {
			$charset = 'utf-8';
		}

		$meta->push('meta', [
			'charset'    => $charset
		]);

		return $meta;
	}
	
	public static function IndexNow(string $url)
	{
		global $config, $lang;
		
		if( !file_exists( ROOT_DIR . '/'. md5(SECURE_AUTH_KEY).'.txt' ) ) {
			return null;
		}
		
		if( trim(file_get_contents( ROOT_DIR . '/'. md5(SECURE_AUTH_KEY).'.txt' )) !=  md5(SECURE_AUTH_KEY) ) {
			return -1;
		}
		
		if( !$config['http_home_url'] ) {
			$config['http_home_url'] = "//".$_SERVER['HTTP_HOST']."/";
			$url = "/".$url;
		}

		if (strpos($config['http_home_url'], "//") === 0) {
			$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https:".$config['http_home_url'] : $config['http_home_url'] = "http:".$config['http_home_url'];
		} elseif (strpos($config['http_home_url'], "/") === 0) {
			$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'] : "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
		} elseif( isSSL() AND stripos( $config['http_home_url'], 'http://' ) !== false ) {
			$config['http_home_url'] = str_replace( "http://", "https://", $config['http_home_url'] );
		}
		
		$host = dle_strtolower(parse_url($config['http_home_url'], PHP_URL_HOST) );

		if (strpos($url, "//") === 0) {
			$url = isSSL() ? $url = "https:".$url : $url = "http:".$url;
		} elseif (strpos($url, "/") === 0) {
			$url = isSSL() ? $url = "https://".$host.$url : "http://".$host.$url;
		} elseif( isSSL() AND stripos( $url, 'http://' ) !== false ) {
			$url = str_replace( "http://", "https://", $url );
		}

		$indexer = new Indexing($host, [
			$config['indexnow_provider'] => md5(SECURE_AUTH_KEY)
		]);

		$result = $indexer->indexUrl($url);

		foreach ($result as $key => $value ) {
			if($value !== true) {
				
				if(isset($lang['indexnow_error_'.$value])) $value = $lang['indexnow_error_'.$value];
				
				return array (0 => $key, 1 => $value);
			}
		}
		
		return true;
	}
}
