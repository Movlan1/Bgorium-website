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
 File: rss.class.php
-----------------------------------------------------
 Use: XML Parser
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class DLExmlParser {
	
	var $att;
	var $id;
	var $title;
	var $content = array ();
	var $max_news = 0;
	var $rss_charset = '';
	var $lastdate = '';
	var $pre_lastdate = '';
	
	function __construct($file, $max) {
		
		$this->max_news = $max;
		
		if( ! ($data = $this->_get_contents( $file )) ) {
			$this->content[0]['title'] = "Fatal Error";
			$this->content[0]['description'] = "Fatal Error: could not open XML input (" . $file . ")";
			$this->content[0]['link'] = "#";
			$this->content[0]['date'] = time();

			return false;
		}

		preg_replace_callback( "#encoding=\"(.+?)\"#i", array( &$this, 'get_charset'), $data );

		$data = str_replace("content:encoded>","content>",$data);
		$data = preg_replace( "#content:encoded(.+?)>#i", "content>", $data );

		libxml_use_internal_errors(true);
		
		$xml = simplexml_load_string($data);
		$image_extensions = array ("gif", "jpg", "jpeg", "bmp", "png", "webp", "avif" );
		
		if ($xml !== false) {

			$i = 0;
			if ( $xml->channel->item ) {
		
				foreach ($xml->channel->item as $item) {

					if ( $item->title ) $this->content[$i]['title'] = (string)$item->title;
					if ( $item->description ) $this->content[$i]['description'] = (string)$item->description;
					if ( $item->link ) $this->content[$i]['link'] = (string)$item->link;
					if ( $item->pubDate ) $this->content[$i]['date'] = (string)$item->pubDate;
					if ( $item->category ) $this->content[$i]['category'] = (string)$item->category;
					if ( $item->content ) $this->content[$i]['content'] = (string)$item->content;
					
					if ( $item->enclosure ) {
						$enclosure = (string)$item->enclosure->attributes()->url;
						
						if( $enclosure ) {
							$filename_arr = explode( ".", $enclosure );
							$type = end( $filename_arr );
							
							if( in_array( $type, $image_extensions ) ) {
								$this->content[$i]['image'] = $enclosure;
							}
						}
					}
					
    				$dc = $item->children("http://purl.org/dc/elements/1.1/");
					if ( $dc->creator ) $this->content[$i]['author'] = (string)$dc->creator;


					$i ++;
					if ( $i == $this->max_news ) break;

				} 
			
			} else {

				$atom = $xml->children('http://www.w3.org/2005/Atom');

				foreach ($atom->entry as $item) {

					if ( $item->title ) $this->content[$i]['title'] = (string)$item->title;
					if ( $item->summary ) $this->content[$i]['description'] = (string)$item->summary;
					if ( $item->link ) $this->content[$i]['link'] = (string)$item->link->attributes()->href;
					if ( $item->published ) $this->content[$i]['date'] = (string)$item->published;
					if ( $item->updated ) $this->content[$i]['date'] = (string)$item->updated;
					if ( $item->category ) $this->content[$i]['category'] = (string)$item->category;
					
					if ( $item->enclosure ) {
						$enclosure = (string)$item->enclosure->attributes()->url;
						
						if( $enclosure ) {
							$filename_arr = explode( ".", $enclosure );
							$type = end( $filename_arr );
							
							if( in_array( $type, $image_extensions ) ) {
								$this->content[$i]['image'] = $enclosure;
							}
						}
					}
					
					if ( $item->author->name ) $this->content[$i]['author'] = (string)$item->author->name;

					if ( $item->content ) {

						$details = $item->children('http://www.w3.org/2005/Atom');

						$this->content[$i]['content'] = $details->content->asXML();

						$this->content[$i]['content'] = str_replace("</content>", "", $this->content[$i]['content']);
						$this->content[$i]['content'] = preg_replace("#<content[^>]*>#", "", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&lt;", "<", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&gt;", ">", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&amp;", "&", $this->content[$i]['content']);

					}


					$i ++;
					if ( $i == $this->max_news ) break;

				}

			}

		} else {

				$this->content[0]['title'] = "File: " . $file;
				$this->content[0]['link'] = "#";
				$this->content[0]['date'] = time();

			    $errors = libxml_get_errors();

				if( isset($errors[0]) ) {

					$error = $errors[0];

					switch ($error->level) {
						case LIBXML_ERR_WARNING:
							$return = "XML Warning $error->code: ";
							break;
						case LIBXML_ERR_ERROR:
							$return = "XML Error $error->code: ";
							break;
						case LIBXML_ERR_FATAL:
							$return = "XML Fatal Error $error->code: ";
							break;
					}

					$this->content[0]['description'] = sprintf("%s %s at line %d column %d", $return, $error->message, $error->line, $error->column);

				} else $this->content[0]['description'] = 'Undefined Error';

			    libxml_clear_errors();
		}

		
	}
	
	function _get_contents($file) {
		
		$data = false;

		if (stripos($file, "http://") !== 0 AND stripos($file, "https://") !== 0) {
			return false;
		}

		if( function_exists( 'curl_init' ) ) {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file );
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$data = curl_exec( $ch );
			curl_close( $ch );

			if( $data ) return $data;
			else return false;
		
		} else {

			$data = trim( file_get_contents( $file ) );
			
			if( $data ) return $data;
			else return false;

		}
	
	}
	
	function pre_parse($date) {
		
		global $config;
		
		$i = 0;
		
		foreach ( $this->content as $content ) {
			
			$content_date = strtotime( $content['date'] );
			
			if( $date ) {
				$this->content[$i]['date'] = time()-($i);
			} else {
				$this->content[$i]['date'] = $content_date;
			}
			
			if( ! $i ) $this->lastdate = $content_date;
			
			if( $i and $content_date > $this->lastdate ) $this->lastdate = $content_date;
			
			if( $this->pre_lastdate != "" and $this->pre_lastdate >= $content_date ) {
				unset( $this->content[$i] );
				$i ++;
				continue;
			}
			
			if( isset($this->content[$i]['description']) ) {
				$this->content[$i]['description'] = trim( $this->content[$i]['description'] );
			}
			
			if( isset($this->content[$i]['content']) ) {
				$this->content[$i]['content'] = trim( $this->content[$i]['content'] );
			}
			
			if( isset($this->content[$i]['content']) AND $this->content[$i]['content'] != '' ) {
				$this->content[$i]['description'] = $this->content[$i]['content'];
			}
			
			unset( $this->content[$i]['content'] );
			
			$i ++;
		}
	
	}
	
	function get_charset($matches=array()) {
	
		if( $this->rss_charset == '' ) $this->rss_charset = strtolower( trim($matches[1]) );
	
	}
}
