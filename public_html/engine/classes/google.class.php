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
 File: google.class.php
-----------------------------------------------------
 Use: Google Sitemap
=====================================================
*/

include_once ENGINE_DIR . '/classes/composer/vendor/autoload.php';

use Melbahja\Seo\Sitemap;
use Melbahja\Seo\Factory;

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class googlemap {
	
	public $allow_url = "";
	public $home = "";
	public $limit = 0;
	
	public $news_priority = "";
	public $stat_priority = "";
	public $cat_priority = "";
	
	public $news_changefreq = "";
	public $stat_changefreq = "";
	public $cat_changefreq = "";
	
	public $priority = "0.6";
	public $changefreq = "daily";
	
	public $set_images = false;

	public $news_per_file = 40000;
	
	public  $sitemap = null;
	private $db_result = null;
	private $allow_tags = null;

	private $googlenews = array();

	
	function __construct($config) {
		
		if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "https:".$config['http_home_url'];
		elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

		$this->allow_url = $config['allow_alt_url'];
		$this->home = $config['http_home_url'];
		$this->limit = $config['sitemap_limit'];
		$this->news_per_file = $config['sitemap_news_per_file'];
		$this->allow_tags = $config['allow_tags'];

		$this->news_priority = $config['sitemap_news_priority'];
		$this->stat_priority = $config['sitemap_stat_priority'];
		$this->cat_priority = $config['sitemap_cat_priority'];
		
		if( $config['sitemap_set_images'] ) $this->set_images = true;

		$this->news_changefreq = $config['sitemap_news_changefreq'];
		$this->stat_changefreq = $config['sitemap_stat_changefreq'];
		$this->cat_changefreq = $config['sitemap_cat_changefreq'];
		
		$this->sitemap = new Sitemap($this->home);
		$this->sitemap->setSavePath(ROOT_DIR. '/uploads');
		
		if( $this->allow_url ) {
			$this->sitemap->setSitemapsUrl($this->home);
		} else {
			$this->sitemap->setSitemapsUrl($this->home.'uploads');
		}

		$this->sitemap->setIndexName('sitemap.xml');

	}
	
	function generate() {
		
		$this->generate_static();
		$this->generate_categories();

		if ($this->allow_tags ) {
			$this->generate_tags();
		}

		$this->generate_news();
		$this->sitemap->save();
		
		if( count($this->googlenews) ) {
			
			$this->sitemap = new Sitemap($this->home);
			$this->sitemap->setSavePath(ROOT_DIR. '/uploads');
			$this->sitemap->setSitemapsUrl($this->home.'uploads');
			$this->sitemap->setIndexName('index.xml');
			
			$this->sitemap->news('google_news.xml', function($map) {
				global $config, $lang;
			
				foreach( $this->googlenews as $news) {
					
					$map->setPublication($config['home_title'], $lang['language_code']);
				
					$map->loc($news['loc'])->news(
					[
					   'title' => $news['title'],
					   'publication_date' => date('c', $news['last']),
					]);
				}
				
			});
			
			$this->sitemap->save();
			unlink(ROOT_DIR. '/uploads/index.xml');

		}
		
	}
	
	function generate_news() {
		
		global $db, $config, $user_group;

		$allow_list = explode ( ',', $user_group[5]['allow_cats'] );
		$not_allow_cats = explode ( ',', $user_group[5]['not_allow_cats'] );
		$stop_list = "";
		$cat_join = "";
	
		if ($allow_list[0] != "all") {
			
			if ($config['allow_multi_category']) {
				
				$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $allow_list ) . ")) c ON (p.id=c.news_id) ";
			
			} else {
				
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			
			}
			
		}
	
		if( $not_allow_cats[0] ) {
			
			if ($config['allow_multi_category']) {
				
				$stop_list = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") ) AND ";
	
				
			} else {
				
				$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
			
			}
			
		}
		
		$thisdate = date( "Y-m-d H:i:s", time() );
		if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'";
		else $where_date = "";
	
		$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post p {$cat_join}WHERE {$stop_list}approve=1{$where_date}" );
	
		if ( !$this->limit ) $this->limit = $row['count'];
		
		if ( $this->limit > $this->news_per_file ) {
	
			$pages_count = @ceil( $row['count'] / $this->news_per_file );
			
			$n = 0;
	
			for ($i =0; $i < $pages_count; $i++) {
	
				$n = $n+1;
	
				$this->get_news($n);
	
			}
	
	
		} else {
	
			$this->get_news();
		
		}
	
	}
	
	function generate_categories() {
		global $db, $user_group;

		$this->priority = $this->cat_priority;
		$this->changefreq = $this->cat_changefreq;

		$cat_info = get_vars("category");

		if (!is_array($cat_info)) {
			$cat_info = array();

			$db->query("SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC");

			while ($row = $db->get_row()) {

				if (!$row['active']) continue;

				$cat_info[$row['id']] = array();

				foreach ($row as $key => $value) {
					$cat_info[$row['id']][$key] = $value;
				}
			}

			set_vars("category", $cat_info);
			$db->free();
		}
		
		$allow_list = explode(',', $user_group[5]['allow_cats']);
		$not_allow_cats = explode(',', $user_group[5]['not_allow_cats']);

		foreach ($cat_info as $cats) {
			if ($cats['disable_index']) unset( $cat_info[$cats['id']] );
			
			if ($allow_list[0] != "all") {
				if (!$user_group[5]['allow_short'] and !in_array($cats['id'], $allow_list)) unset( $cat_info[$cats['id']] );
			}

			if ($not_allow_cats[0]) {
				if (!$user_group[5]['allow_short'] and in_array($cats['id'], $not_allow_cats)) unset( $cat_info[$cats['id']] );
			}

		}

		if( !count($cat_info) ) return;

		$this->sitemap->links('category_pages.xml', function($map) use ($cat_info) {
		
			foreach ( $cat_info as $cats ) {

				if( $this->allow_url ) $loc = get_url( $cats['id'] ) . "/";
				else $loc = "index.php?do=cat&category=" . get_url ($cats['id'] );
				
				$map->loc($loc)->freq($this->changefreq)->lastMod(date('c'))->priority( $this->priority );
				
			}
			
		});
		
	}
	
	function generate_static() {
		
		global $db;
		
		$this->priority = $this->stat_priority;
		$this->changefreq = $this->stat_changefreq;

		$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE name !='dle-rules-page' AND sitemap='1' AND password='' AND disable_index='0'");

		if( !$result_count['count'] ) return;

		if( $this->set_images ) {
			$file_params = ['name' => 'static_pages.xml', 'images' => true];	
		} else {
			$file_params = 'static_pages.xml';
		}

		$this->db_result = $db->query( "SELECT id, name, sitemap, disable_index, password FROM " . PREFIX . "_static" );

		$this->sitemap->links($file_params, function($map) {
			
			global $db;
			
			while ( $row = $db->get_row( $this->db_result ) ) {
				
				if( $row['name'] == "dle-rules-page" ) continue;
				if( !$row['sitemap'] OR $row['disable_index'] OR $row['password']) continue;
				
				if( $this->allow_url ) $loc = $row['name'] . ".html";
				else $loc = "index.php?do=static&page=" . $row['name'];
				
				$map->loc($loc)->freq($this->changefreq)->lastMod(date('c'))->priority( $this->priority );

				if ($this->set_images) {

					$images_sql = $db->query( "SELECT name FROM " . PREFIX . "_static_files WHERE static_id = '{$row['id']}' AND onserver = ''" );

					while ($images_row = $db->get_row( $images_sql )) {

						$image = get_uploaded_image_info($images_row['name']);

						$map->image($image->url);
						
					}
				}
				
			}
			
		});
		
	}
	
	function generate_tags() {
		
		global $db;
		
		$this->priority = $this->cat_priority;
		$this->changefreq = $this->cat_changefreq;

		$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_tags");

		if( !$result_count['count'] ) return;

		$this->db_result = $db->query( "SELECT tag FROM " . PREFIX . "_tags GROUP BY tag LIMIT 0, 40000" );
		
		$this->sitemap->links('tags_pages.xml', function($map) {
			
			global $db;
			
			while ( $row = $db->get_row( $this->db_result ) ) {
				
				$row['tag'] = str_replace(array("&#039;", "&quot;", "&amp;"), array("'", '"', "&"), $row['tag']);
				
				if( $this->allow_url ) $loc = "tags/" . rawurlencode( dle_strtolower($row['tag']) ) . "/";
				else $loc = "index.php?do=tags&tag=" .  rawurlencode( dle_strtolower($row['tag']) );	
				
				$map->loc($loc)->freq($this->changefreq)->lastMod(date('c'))->priority( $this->priority );
				
			}
			
		});
		
	}
	
	function get_news( $page = false ) {
		
		global $db, $config, $user_group;
		
		$this->priority = $this->news_priority;
		$this->changefreq = $this->news_changefreq;
		$prefix_page = '';
		
		if ( $page ) {
			
			if( $page != 1 ) $prefix_page = $page;

			$page = $page - 1;
			$page = $page * $this->news_per_file;
			$this->limit = " LIMIT {$page}, {$this->news_per_file}";

		} else {

			if( $this->limit < 1 ) $this->limit = false;
			
			if( $this->limit ) {
				
				$this->limit = " LIMIT 0," . $this->limit;
			
			} else {
				
				$this->limit = "";
			
			}
		}
		
		$thisdate = date( "Y-m-d H:i:s", time() );
		if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'";
		else $where_date = "";

		$allow_list = explode ( ',', $user_group[5]['allow_cats'] );
		$not_allow_cats = explode ( ',', $user_group[5]['not_allow_cats'] );
		$stop_list = "";
		$cat_join = "";

		if ($allow_list[0] != "all") {
			
			if ($config['allow_multi_category']) {
				
				$cat_join = " INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $allow_list ) . ")) c ON (p.id=c.news_id) ";
			
			} else {
				
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			
			}
		
		}

		if( $not_allow_cats[0] ) {
			
			if ($config['allow_multi_category']) {
				
				$stop_list = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") ) AND ";
			
			} else {
				
				$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
			
			}
			
		}
		
		$this->db_result = $db->query( "SELECT p.id, p.title, p.date, p.alt_name, p.category, e.access, e.editdate, e.disable_index, e.need_pass FROM " . PREFIX . "_post p {$cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1" . $where_date . " ORDER BY date DESC" . $this->limit );
		
		if( $this->set_images ) {
			$file_params = ['name' => "news_pages{$prefix_page}.xml", 'images' => true];	
		} else {
			$file_params = "news_pages{$prefix_page}.xml";
		}

		$this->sitemap->links($file_params, function($map) {	
			global $db, $config;
		
			$two_days = time() - (2 * 3600 * 24);

			$cat_info = get_vars("category");

			while ( $row = $db->get_row( $this->db_result ) ) {
				
				$row['date'] = strtotime($row['date']);
				
				$row['cats'] = explode(',', $row['category']);

				foreach ($row['cats'] as $element) {
					$element = trim(intval($element));
					if( $element AND isset($cat_info[$element]['id']) AND $cat_info[$element]['disable_index'] ) $row['disable_index'] = true;
				}

				$row['category'] = intval( $row['category'] );
	
				if ( $row['disable_index'] ) continue;
				
				if ( $row['need_pass'] ) continue;
				
				if (strpos( $row['access'], '5:3' ) !== false) continue;
	
				if( $this->allow_url ) {
					
					if( $config['seo_type'] == 1 OR  $config['seo_type'] == 2 ) {
						
						if( $row['category'] and $config['seo_type'] == 2 ) {
							
							$cats_url = get_url( $row['category'] );
							
							if($cats_url) {
								
								$loc = $cats_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
								
							} else $loc = $row['id'] . "-" . $row['alt_name'] . ".html";
						
						} else {
							
							$loc = $row['id'] . "-" . $row['alt_name'] . ".html";
						
						}
					
					} else {
						
						$loc = date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
					}
				
				} else {
					
					$loc = "index.php?newsid=" . $row['id'];
				
				}
	
				if ( $row['editdate'] AND $row['editdate'] > $row['date'] ){
				
					$row['date'] =  $row['editdate'];
				
				}
				
				if( $row['date'] > $two_days ) {
					$this->googlenews[] = array('title' => stripslashes($row['title']), 'loc' => $loc, 'last' => $row['date']);
				}

				$map->loc($loc)->freq($this->changefreq)->lastMod( date('c', $row['date'] ) )->priority( $this->priority );
				
				if ($this->set_images) {

					$images_row = $db->super_query("SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$row['id']}'");

					if (isset($images_row['images']) and $images_row['images']) {
						$listimages = explode("|||", $images_row['images']);

						foreach ($listimages as $dataimages) {

							$image = get_uploaded_image_info($dataimages);
							$map->image($image->url);

						}
					}
				}

			}
			
		});
		

	}

}
