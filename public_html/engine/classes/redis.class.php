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
 File: redis.class.php
-----------------------------------------------------
 Use: redis class
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class dle_fastcache
{
	protected $server = null;
	protected $suite_key = null;
	protected $max_age = null;
	public $connection = null;
	
	public function __construct( $config ) {
		
		$this->suite_key = md5( DBNAME . PREFIX . SECURE_AUTH_KEY ).'_';
		
		$this->server = $this->connect();
		
		if($this->connection !== -1 ) {
			
			$redis_server = explode(":", $config['memcache_server']);
			$this->connection = 1;

			if ( count($redis_server) == 2 ) {

				try {
					
					if( !$this->server->connect( trim($redis_server[0]), trim($redis_server[1]) ) ) {
						$this->connection = 0;
					}
					
				} catch (Exception $e) {
					
					$this->connection = 0;
			
				}
			
			} else {

				try {
					
					if( !$this->server->connect(trim($redis_server[0])) ) {
						$this->connection = 0;
					}
					
				} catch (Exception $e) {
					
					$this->connection = 0;
			
				}
				
			}
			
			$auth = array();
			
			if( $config['redis_user'] ) $auth['user'] = html_entity_decode($config['redis_user'], ENT_QUOTES | ENT_XML1, 'UTF-8');
			if( $config['redis_pass'] ) $auth['pass'] = html_entity_decode($config['redis_pass'], ENT_QUOTES | ENT_XML1, 'UTF-8');

			if( count($auth) ) {
				try {
					
					if( !$this->server->auth($auth) ) {
						$this->connection = -2;
					}
					
				} catch (Exception $e) {
					
					$this->connection = -2;
			
				}
			}
		
			if($this->connection > 0) {
				
				try {
					
					if( !$this->server->ping() ){
						$this->connection = 0;
					}
					
				} catch (Exception $e) {
					
					$this->connection = 0;
			
				}	
			}
		
		}
		
		if ( $config['clear_cache'] ) $this->max_age = $config['clear_cache'] * 60; else $this->max_age = 86400;

	}
	
	protected function connect() {
		
		if( class_exists( 'Redis' ) ) {
			
			return new Redis();
		
		} else {
			
			$this->connection = -1;
			
		}
		
	}
	
	public function get( $key ) {
		
		if($this->connection < 1 ) return false;

		return $this->server->get($this->suite_key.$key);
		
	}

	public function set($key, $value, $max_age = false) {
		
		if($this->connection < 1 ) return false;
		
		if( $max_age ) {

			$this->server->setEx($this->suite_key . $key, $max_age, $value);
		} else {
			$this->server->setEx($this->suite_key . $key, $this->max_age, $value);
		}
		
		return true;
		
	}
	
	public function clear( $cache_areas = false ) {
		
		if($this->connection < 1 ) return false;
		
		if ( $cache_areas ) {
			if(!is_array($cache_areas)) {
				$cache_areas = array($cache_areas);
			}
		}
		
		if( $cache_areas ) {
				
			foreach($cache_areas as $cache_area){
			
				$allKeys = $this->server->keys($this->suite_key.$cache_area.'*');

				if(is_array($allKeys) AND count($allKeys)) {
					foreach ( $allKeys as $key) {
						$this->server->del($key);
					}
				}

			}
			
		} else {
			
			$this->_clear_all();
			
		}
		
		return true;
		
	}
	
	protected function _clear_all() {
		
		if($this->connection < 1 ) return false;
		
		$allKeys = $this->server->keys($this->suite_key.'*');

		if(is_array($allKeys) AND count($allKeys)) {
			foreach ( $allKeys as $key) {
				$this->server->del($key);
			}
		}
		
		return true;
		
	}
	
	
	public function __destruct() {
		
		if($this->connection < 1 ) return;
		
		if( $this->server ) {
			$this->server->close();
		}
	}
	
}
