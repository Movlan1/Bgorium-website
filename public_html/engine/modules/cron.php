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
 File: cron.php
-----------------------------------------------------
 Use: Performing automatic operations
=====================================================
*/
if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !isset($cron_time['locked']) OR !isset($cron_time['time']) OR (isset($cron_time['locked']) AND $cron_time['locked'] AND $cron_time['lasttime'] < $_TIME - 120 ) ) {

	$cron_data = array( 'time' => $_TIME, 'locked' => true);
	$cron_clear_cache = false;
 
	if( !isset($cron_time['time']) ) {
		
		$cron_data['successtime'] = $_TIME - (3600 * 25);
		
	} else $cron_data['successtime'] = $cron_time['time'];
	
	set_vars( "cron", $cron_data );

	if( $cron == 1 ) {
		$db->query( "DELETE FROM " . PREFIX . "_spam_log WHERE is_spammer = '0'" );
	}
	
	if( $config['cache_count'] ) {
		$result = $db->query( "SELECT COUNT(*) as count, news_id FROM " . PREFIX . "_views GROUP BY news_id" );
		
		while ( $row = $db->get_array( $result ) ) {

			$cron_clear_cache = true;

			$db->query( "UPDATE " . PREFIX . "_post_extras SET news_read=news_read+{$row['count']} WHERE news_id='{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_views WHERE news_id = '{$row['news_id']}'" );
		
		}
		
		$db->free( $result );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_views" );

		if ( $cron_clear_cache ) clear_cache( array('news_', 'full_') );
	
	}
	
	if( $cron == 2 ) {
		
		$db->query( "TRUNCATE TABLE " . PREFIX . "_login_log" );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_flood" );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_mail_log" );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_read_log" );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_spam_log" );
		$db->query( "TRUNCATE TABLE " . PREFIX . "_banners_logs" );
		$db->query( "TRUNCATE TABLE " . USERPREFIX . "_downloads_log" );
	
		$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_lostdb" );
		
		if($row['count'] > 3 ) {
			$row['count'] = $row['count'] - 3;
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb ORDER BY id LIMIT {$row['count']}" );
		}
		
		$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '$_TIME' AND users_id = '0'" );
		@unlink( ENGINE_DIR . '/cache/system/banned.json' );
		
		$sql_cron = $db->query( "SELECT * FROM " . PREFIX . "_post_log WHERE expires <= '" . $_TIME . "'" );
		
		while ( $row = $db->get_row( $sql_cron ) ) {
			
			if ($row['action'] == 1) {

				$cron_clear_cache = true;

				$row_title = $db->super_query("SELECT title  FROM " . PREFIX . "_post WHERE id='{$row['news_id']}'");
				$row_title = $db->safesql($row_title['title']);

				$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('dle_cron_auto', '{$_TIME}', 'localhost', '96', '{$row_title}')");

				deletenewsbyid($row['news_id']);

			} elseif ( $row['action'] == 2 ) {

				$cron_clear_cache = true;

				$db->query( "UPDATE " . PREFIX . "_post SET approve='0' WHERE id='{$row['news_id']}'" );
				$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['news_id']}'" );
				$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '{$row['news_id']}'" );
				$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$row['news_id']}'" );
		
			} elseif ( $row['action'] == 3 ) {
	
				$db->query( "UPDATE " . PREFIX . "_post SET allow_main='0' WHERE id='{$row['news_id']}'" );
	
			} elseif ( $row['action'] == 4 ) {
	
				$db->query( "UPDATE " . PREFIX . "_post SET fixed='0' WHERE id='{$row['news_id']}'" );
				
			} elseif ( $row['action'] == 5 ) {

				$cron_clear_cache = true;

				$db->query( "UPDATE " . PREFIX . "_post SET category='{$row['move_cat']}' WHERE id='{$row['news_id']}'" );
	
				$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '{$row['news_id']}'" );
	
				if( $row['move_cat'] ) {
	
					$cat_ids = array ();
	
					$cat_ids_arr = explode( ",", $row['move_cat'] );
	
					foreach ( $cat_ids_arr as $value ) {
	
						$cat_ids[] = "('" . $row['news_id'] . "', '" . trim( $value ) . "')";
					}
	
					$cat_ids = implode( ", ", $cat_ids );
					$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );
	
				}
			
			} elseif ( $row['action'] == 6 ) {
				$is_closed = $db->super_query("SELECT id, closed FROM " . PREFIX . "_poll WHERE news_id = '{$row['news_id']}'");
				
				if (isset($is_closed['id']) AND !$is_closed['closed']) {
					$db->query("UPDATE " . PREFIX . "_poll SET closed='1', date_closed='{$_TIME}' WHERE news_id='{$row['news_id']}'");
				}
	
			}
			
			$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id = '{$row['news_id']}'" );

		}
		
		$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE expires <= '" . $_TIME . "'" );
		
		$db->free( $sql_cron );
		
		if( intval( $config['max_users_day'] ) ) {
			$thisdate = $_TIME - ($config['max_users_day'] * 3600 * 24);
			
			$sql_result = $db->query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE lastdate < '{$thisdate}' AND user_group > '1'" );
			
			while ( $row = $db->get_row( $sql_result ) ) {
				deleteuserbyid($row['user_id']);
			}
	
			$db->free( $sql_result );
			
		}
		
		if( intval( $config['max_image_days'] ) ) {
			
			DLEFiles::init();
		
			$thisdate = $_TIME - ($config['max_image_days'] * 3600 * 24);
			
			$sql_result = $db->query( "SELECT id, images  FROM " . PREFIX . "_images WHERE date < '$thisdate' AND news_id = '0'" );
			
			while ( $row = $db->get_row( $sql_result ) ) {
				
				$db->query( "DELETE FROM " . PREFIX . "_images WHERE id = '{$row['id']}'" );
				
				if( isset($row['images']) AND $row['images']) {
					
					$listimages = explode( "|||", $row['images'] );
				
					foreach ( $listimages as $dataimage ) {
						
						$dataimage = get_uploaded_image_info($dataimage);
						
						$query = $db->safesql( $dataimage->path );
						$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE short_story LIKE '%{$query}%' OR full_story LIKE '%{$query}%' OR xfields LIKE '%{$query}%'");
			
						if( isset($row['count']) AND $row['count'] ) {
							continue;
						}
						
						if( $dataimage->remote ) $disk = DLEFiles::FindDriver($dataimage->url);
						else $disk = 0;
				
						DLEFiles::Delete( "posts/" . $dataimage->path, $disk );

						if ($dataimage->hidpi) {
							DLEFiles::Delete("posts/{$dataimage->folder}/{$dataimage->hidpi}", $disk);
						}

						if( $dataimage->thumb ) {
							
							DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $disk );

							if ($dataimage->hidpi) {
								DLEFiles::Delete("posts/{$dataimage->folder}/thumbs/{$dataimage->hidpi}", $disk);
							}
						}
						
						if( $dataimage->medium ) {
							
							DLEFiles::Delete( "posts/{$dataimage->folder}/medium/{$dataimage->name}", $disk );
							
							if ($dataimage->hidpi) {
								DLEFiles::Delete("posts/{$dataimage->folder}/medium/{$dataimage->hidpi}", $disk);
							}	
						}
					
					}
					
				}
			
			}
			
			$db->free( $sql_result );
	
			$sql_result = $db->query( "SELECT * FROM " . PREFIX . "_files WHERE date < '$thisdate' AND news_id = '0'" );
					
			while ( $row = $db->get_row( $sql_result ) ) {
				
				$db->query( "DELETE FROM " . PREFIX . "_files WHERE id = '{$row['id']}'" );
				
				if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
				
				if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';
	
				DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

			}
			
			$db->free( $sql_result );
			
			$sql_result = $db->query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE date < '{$thisdate}' AND c_id = '0'" );
					
			while ( $row = $db->get_row( $sql_result ) ) {
				
				$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE id = '{$row['id']}'" );
	
				$dataimage = get_uploaded_image_info( $row['name'] );
				
				DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );
				
				if( $dataimage->thumb ) {
					
					DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );
					
				}
	
			}
			
			$db->free( $sql_result );
			
		
		}

		if( $cron_clear_cache ) {
			clear_cache(array('news_', 'full_', 'tagscloud_', 'archives_', 'related_', 'calendar_', 'rss', 'stats'));
		}
	
	}
	
	unset($cron_data['locked']);
	unset($cron_data['successtime']);
	
	set_vars( "cron", $cron_data );

}

?>