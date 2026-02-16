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
 File: memcache.class.php
-----------------------------------------------------
 Use: memcache class
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
	protected $server_type = null;
	protected $max_age = null;
	
	protected $host = null;
	protected $port = null;
	
	public $connection = null;
	
	public function __construct( $config ) {
		
		$this->suite_key = md5( DBNAME . PREFIX . SECURE_AUTH_KEY );
		
		$this->server = $this->connect();
		
		if($this->connection !== -1 ) {
			
			$memcache_server = explode(":", $config['memcache_server']);
			$this->connection = 1;
			
			if ($memcache_server[0] == 'unix') {
				
				$memcache_server = array($config['memcache_server'], 0);
				$this->host = $memcache_server[0];
				$this->port = -1;
				
			} else {
				$this->host = $memcache_server[0];
				$this->port = $memcache_server[1];
			}
			
			if ( !$this->server->addServer($memcache_server[0], $memcache_server[1]) ) {
				$this->connection = 0;
			}
			
			if ( $this->server->getStats() === false ) {
				$this->connection = 0;
			}
			
			if($this->connection > 0 AND $this->server_type == "memcached") {
				
				$this->server->setOption(Memcached::OPT_COMPRESSION, false);
				
			}
		}
		
		if ( $config['clear_cache'] ) $this->max_age = $config['clear_cache'] * 60; else $this->max_age = 86400;

	}
	
	protected function connect() {
		
		if( class_exists( 'Memcached' ) ) {
			
			$this->server_type = "memcached";
			
			return new Memcached();
		
		} elseif( class_exists( 'Memcache' ) ) {
			
			$this->server_type = "memcache";
			
			return new Memcache();

		} else {
			
			$this->connection = -1;
			
		}
		
	}
	
	public function get( $key ) {
		
		if($this->connection < 1 ) return false;

		return $this->server->get($key."_".$this->suite_key);
		
	}

	public function set($key, $value, $max_age = false) {
		
		if($this->connection < 1 ) return false;

		$this->_set( $key."_".$this->suite_key, $value, $max_age );
		
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
			
			$all_list = $this->getAllKeys();

			if(count($all_list)) {
		
				foreach ( $all_list as $key ) {
					
					foreach($cache_areas as $cache_area){
						
						if( stripos( $key, $cache_area ) === 0 ) {
							$this->server->delete($key);
						}
					
					}
				}
				
			} else {
				
				$this->_clear_all();
				
			}
			
		} else {
			
			$this->_clear_all();
			
		}
		
		return true;
		
	}
	
	protected function _set( $key, $value, $max_age) {
		
		if($this->connection < 1 ) return false;

		if ( $this->server_type == "memcache" ) {

			if ($max_age) {
				$this->server->set($key, $value, 0, $max_age);
			} else {
				$this->server->set($key, $value, 0, $this->max_age);
			}
			
		} else {

			if ($max_age) {
				$this->server->set($key, $value, $max_age);
			} else {
				$this->server->set($key, $value, $this->max_age);
			}
			
		}
		
		return true;
	}

	protected function getAllKeys() {

		if( method_exists( $this->server, 'getAllKeys' ) ) {

			$allKeys = $this->server->getAllKeys();
			return $allKeys;
			
		}

		$sock = fsockopen($this->host, $this->port);
		
		if ($sock === false) {
			return array();
		}
	
		if (fwrite($sock, "stats items\n") === false) {
			return array();
		}
	
		$slabCounts = $allKeys = array();
		
		while (($line = fgets($sock)) !== false) {
			$line = trim($line);
			if ($line === 'END') {
				break;
			}
	
			if (preg_match('!^STAT items:(\d+):number (\d+)$!', $line, $matches)) {
				$slabCounts[$matches[1]] = (int)$matches[2];
			}
		}
	
		foreach ($slabCounts as $slabNr => $slabCount) {
			if (fwrite($sock, "lru_crawler metadump {$slabNr}\n") === false) {
				return array();
			}
	
			$count = 0;
			while (($line = fgets($sock)) !== false) {
				$line = trim($line);
				if ($line === 'END') {
					break;
				}
	
				if (preg_match('!^key=(\S+)!', $line, $matches)) {
					
					if( strpos( $matches[1], $this->suite_key ) !== false ) {
						
						$allKeys[] = $matches[1];
						$count++;
					
					}
				}
			}
		}
	
		fclose($sock);
		
		return $allKeys;
		
	}
	
	protected function _clear_all() {
		
		if($this->connection < 1 ) return false;
		
		$this->server->flush();
		
		return true;
		
	}
	
	public function __destruct() {
		
		if($this->connection < 1 ) return;
		
		if( $this->server ) {
			if( method_exists( $this->server, 'quit' ) ) {
				$this->server->quit();
			} elseif( method_exists( $this->server, 'close' ) ) {
				$this->server->close();
			}
		}
	}
	
}


// deprecated name

class dle_memcache
{
	public $connection = null;
	
	public function __construct( $config ) {
		$this->connection = 0;
	}
	
}
