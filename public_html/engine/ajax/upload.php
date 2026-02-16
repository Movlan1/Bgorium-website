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
 File: upload.php
-----------------------------------------------------
 Use: upload files
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

function xfparamload( $xfname ) {

	$path = ENGINE_DIR . '/data/xfields.txt';
	$filecontents = file( $path );
	
	if( !is_array( $filecontents ) ) {
		return false;
	}
	
	foreach ( $filecontents as $name => $value ) {
		$filecontents[$name] = explode( "|", trim( $value ) );
		if($filecontents[$name][0] == $xfname ) return $filecontents[$name];
	}
	
	return false;
}

$allowed_extensions = array ("gif", "jpg", "png", "jpeg", "webp" , "bmp", "avif", "heic");
$allowed_video = array ("mp4", "mp3", "m4v", "m4a", "mov", "webm", "m3u8", "mkv", "flac", "aac", "ogg" );
$allowed_files = explode( ',', strtolower( $user_group[$member_id['user_group']]['files_type'] ) );

if( intval( $_REQUEST['news_id'] ) ) $news_id = intval( $_REQUEST['news_id'] ); else $news_id = 0;
if( isset( $_REQUEST['area'] ) ) $area = totranslit( $_REQUEST['area'] ); else $area = "";
$_REQUEST['subaction'] = isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '';


if( !$is_logged ) {
	die ( "{\"error\":\"{$lang['err_notlogged']}\"}" );
}

if( !$user_group[$member_id['user_group']]['allow_image_upload'] AND !$user_group[$member_id['user_group']]['allow_file_upload'] ) {
	if ( $area != "comments" ) {
		die ( "{\"error\":\"{$lang['err_noupload']}\"}" );	
	}
}

$author = $db->safesql($member_id['name']);

if( isset( $_REQUEST['author'] ) AND $_REQUEST['author'] ) {
	
	$author = strip_tags(urldecode( (string)$_REQUEST['author'] ) );
	
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $author ) ) {
		die ( "{\"error\":\"{$lang['user_err_6']}\"}" );		
	}
	
	$author = $db->safesql($author);
	
}

if ( !$user_group[$member_id['user_group']]['allow_all_edit'] AND $area != "comments" ) $author = $db->safesql($member_id['name']);

if ( $area == "template" ) {

	if ( !$user_group[$member_id['user_group']]['admin_static'] ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );

}

if ( $area == "comments" AND !$user_group[$member_id['user_group']]['allow_up_image'] ) {

	die ( "{\"error\":\"{$lang['opt_denied']}\"}" );

}

if ( $area == "adminupload" ) {

	if ( $member_id['user_group'] != 1 ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );

}

if ( $news_id AND $area != "template" AND $area != "comments" ) {

	$row = $db->super_query( "SELECT id, autor, approve FROM " . PREFIX . "_post WHERE id = '{$news_id}'" );

	if ( !$row['id'] ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );

	if ( !$user_group[$member_id['user_group']]['allow_all_edit'] AND $row['autor'] != $member_id['name'] ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );
	
	if ($row['approve'] AND !$user_group[$member_id['user_group']]['moderation'] AND ($_REQUEST['subaction'] == "upload" OR $_POST['subaction'] == "deluploads") ) {
		$db->query( "UPDATE " . PREFIX . "_post SET approve='0' WHERE id='{$news_id}'" );
	}
}

if ( $news_id AND $area == "comments" ) {

	$row = $db->super_query( "SELECT id, user_id, date, is_register FROM " . PREFIX . "_comments WHERE id = '{$news_id}'" );

	if ( !$row['id'] ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );

	$have_perm = 0;
	$row['date'] = strtotime( $row['date'] );
	
	if( ($member_id['user_id'] == $row['user_id'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_editc']) OR $user_group[$member_id['user_group']]['edit_allc'] ) {
		$have_perm = 1;
	}
	
	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ((int)$user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}
	
	if ( !$have_perm ) die ( "{\"error\":\"{$lang['opt_denied']}\"}" );
	
}

if( $area == "comments" ) {
	
	$user_group[$member_id['user_group']]['allow_image_size'] = false;
	$user_group[$member_id['user_group']]['allow_file_upload'] = false;
	$config['max_up_side'] = $user_group[$member_id['user_group']]['up_image_side'];
	$config['max_up_size'] = $user_group[$member_id['user_group']]['up_image_size'];
	
	if ( !$user_group[$member_id['user_group']]['edit_allc'] ) $author = $db->safesql($member_id['name']);
	
}

//////////////////////
// go go upload
//////////////////////
if( $_REQUEST['subaction'] == "upload" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ( "{\"error\":\"{$lang['sess_error']}\"}" );
	
	}
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/uploads/upload.class.php'));

	if( isset($_REQUEST['mode']) AND $_REQUEST['mode'] == "quickload") $user_group[$member_id['user_group']]['allow_image_size'] = $user_group[$member_id['user_group']]['allow_change_storage'] = false;

	if( $area != "comments" AND $area != "adminupload" AND $user_group[$member_id['user_group']]['allow_change_storage'] AND isset($_REQUEST['upload_driver'])) {
		$_REQUEST['upload_driver'] = intval($_REQUEST['upload_driver']);

		if( $_REQUEST['upload_driver'] > -1) {
			$config['image_remote'] = $config['files_remote'] = $config['static_remote'] = $_REQUEST['upload_driver'];
		}
	}

	if( $user_group[$member_id['user_group']]['allow_image_size'] ) {

		if ( isset($_REQUEST['t_seite']) ) $t_seite = intval( $_REQUEST['t_seite'] ); else $t_seite = intval($config['t_seite']);
		if ( isset($_REQUEST['m_seite']) ) $m_seite = intval( $_REQUEST['m_seite'] ); else $m_seite = intval($config['t_seite']);
		if ( isset($_REQUEST['make_thumb']) ) $make_thumb = intval( $_REQUEST['make_thumb'] ); else $make_thumb = true;
		if ( isset($_REQUEST['make_medium']) ) $make_medium = intval( $_REQUEST['make_medium'] ); else $make_medium = true;

		$t_size = isset($_REQUEST['t_size']) ? $_REQUEST['t_size'] : $config['max_image'];
		$m_size = isset($_REQUEST['m_size']) ? $_REQUEST['m_size'] : $config['medium_image'];
		$make_watermark = isset($_REQUEST['make_watermark']) ? intval($_REQUEST['make_watermark']) : false;
		$hidpi = isset($_REQUEST['hidpi']) ? intval($_REQUEST['hidpi']) : false;


		if(!$t_size) $make_thumb = false;
		if(!$m_size) $make_medium = false;

		if ( $area == "adminupload" ) {
		
			if ($config['allow_watermark']) $make_watermark = true; else $make_watermark = false;
			$t_seite = intval($config['t_seite']);
			$m_seite = intval($config['t_seite']);
			$t_size = $config['max_image'];
			$m_size = $config['medium_image'];
			$make_thumb = false;
			$make_medium = false;
			$hidpi = false;
		
		}

	} else {
		
		$t_seite = intval($config['t_seite']);
		$m_seite = intval($config['t_seite']);
		$t_size = $config['max_image'];
		$m_size = $config['medium_image'];
		$make_thumb = true;
		$make_medium = true;
		$hidpi = false;
		if ($config['allow_watermark']) $make_watermark = true; else $make_watermark = false;

		if(!$t_size) $make_thumb = false;
		if(!$m_size) $make_medium = false;
	
	}

	if ($area == "xfieldsimage" OR $area == "xfieldsimagegalery" OR $area == "xfieldsvideo" OR $area == "xfieldsaudio" OR $area == "xfieldsfile" ) {

		$xfparam = xfparamload($_REQUEST['xfname']);

		if (!is_array($xfparam)) die("{\"error\":\"xfieldname not found\"}");

		$xfparam[33] = isset($xfparam[33]) ? intval($xfparam[33]) : -1;

		if ($xfparam[33] > -1) {
			$config['image_remote'] = $config['files_remote'] = $xfparam[33];
		}

	}

	if( $area == "xfieldsimage" OR $area == "xfieldsimagegalery") {
		
		$xfparam = xfparamload( $_REQUEST['xfname'] );
		
		if( !is_array( $xfparam ) ) die ( "{\"error\":\"xfieldname not found\"}" );
		
		$_REQUEST['xfname'] = $xfparam[0];
		$t_seite = isset($xfparam[39]) ? intval($xfparam[39]) : intval($config['t_seite']);
		$m_seite = isset($xfparam[39]) ? intval($xfparam[39]) : intval($config['t_seite']);
		$t_size = $xfparam[13];
		$m_size = 0;
		$config['max_up_side'] = $xfparam[9];
		$config['max_up_size'] = $xfparam[10];
		$config['min_up_side'] = $xfparam[22];
		$config['o_seite'] = isset($xfparam[38]) ? intval($xfparam[38]) : intval($config['o_seite']);
		
		$config['files_allow'] = false;
		$user_group[$member_id['user_group']]['allow_file_upload'] = false;
		$make_watermark = $xfparam[11] ? true : false;
		$make_thumb = $xfparam[12] ? true : false;
		$make_medium = false;
		$hidpi = false;
		
	}
	
	if( $area == "xfieldsfile" ) {
		$xfparam = xfparamload( $_REQUEST['xfname'] );
		
		if( !is_array( $xfparam ) ) die ( "{\"error\":\"xfieldname not found\"}" );
		
		$_REQUEST['xfname'] = $xfparam[0];
		$_REQUEST['public_file'] = intval($xfparam[27]);
		
		$user_group[$member_id['user_group']]['allow_image_upload'] = false;
		$user_group[$member_id['user_group']]['files_type'] = $xfparam[14];
		$user_group[$member_id['user_group']]['max_file_size'] = $xfparam[15];
		$user_group[$member_id['user_group']]['allow_public_file_upload'] = intval($xfparam[27]);

	}

	if ($area == "xfieldsvideo" OR $area == "xfieldsaudio" ) {
		$xfparam = xfparamload($_REQUEST['xfname']);

		if (!is_array($xfparam)) die("{\"error\":\"xfieldname not found\"}");

		$_REQUEST['xfname'] = $xfparam[0];
		$_REQUEST['public_file'] = 1;

		$user_group[$member_id['user_group']]['allow_image_upload'] = false;

		if( $area == "xfieldsvideo" ) {

			$user_group[$member_id['user_group']]['files_type'] = "mp4,m4v,m4a,mov,webm,m3u8,mkv";

		} else $user_group[$member_id['user_group']]['files_type'] = "mp3,flac,aac,ogg";

		$user_group[$member_id['user_group']]['max_file_size'] = $xfparam[32];
		$user_group[$member_id['user_group']]['allow_public_file_upload'] = 1;

	}

	if( $area == "comments" ) {
		$user_group[$member_id['user_group']]['allow_image_size'] = false;
		$user_group[$member_id['user_group']]['allow_file_upload'] = false;
		$user_group[$member_id['user_group']]['allow_image_upload'] = true;
		$config['max_up_side'] = $user_group[$member_id['user_group']]['up_image_side'];
		$config['max_up_size'] = $user_group[$member_id['user_group']]['up_image_size'];
		$config['min_up_side'] = $user_group[$member_id['user_group']]['min_image_side'];
		$t_seite = intval($config['t_seite']);
		$m_seite = intval($config['t_seite']);
		$t_size = $user_group[$member_id['user_group']]['up_thumb_size'];
		$m_size = 0;
		$make_watermark = $user_group[$member_id['user_group']]['allow_up_watermark'] ? true : false;
		$make_thumb = $user_group[$member_id['user_group']]['allow_up_thumb'] ? true : false;
		$make_medium = false;
		$hidpi = false;
	}

	$t_size = explode ("x", $t_size);
	
	if ( count($t_size) == 2) {
	
		$t_size = intval($t_size[0]) . "x" . intval($t_size[1]);
	
	} else {
	
		$t_size = intval( $t_size[0] );
	
	}

	$m_size = explode ("x", $m_size);
	
	if ( count($m_size) == 2) {
	
		$m_size = intval($m_size[0]) . "x" . intval($m_size[1]);
	
	} else {
	
		$m_size = intval( $m_size[0] );
	
	}

	$uploader = new FileUploader($area, $news_id, $author, $t_size, $t_seite, $make_thumb, $make_watermark, $m_size, $m_seite, $make_medium, $hidpi);
	$result = $uploader->FileUpload();
	echo $result;
	die();

}
//////////////////////
// go go delete uploaded files
//////////////////////
check_xss ();

if( $_REQUEST['subaction'] == "deluploads" ) {

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die ( "{\"error\":\"User not found\"}" );
	
	}
	
	DLEFiles::init();
	
	if( isset( $_POST['images'] ) ) {

		$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE author = '{$author}' AND news_id = '{$news_id}'" );
		
		$listimages = explode( "|||", $row['images'] );

		$temp_images = $listimages;

		foreach ( $_POST['images'] as $image ) {
			
			$i = 0;
			$image = get_uploaded_image_info($image);

			reset( $listimages );
			
			foreach ( $temp_images as $dataimage ) {
				
				$dataimage = get_uploaded_image_info($dataimage);
				
				if( $dataimage->remote ) $disk = DLEFiles::FindDriver($dataimage->url);
				else $disk = 0;

				if( $dataimage->path == $image->path ) {
					
					unset( $listimages[$i] );
	
					DLEFiles::Delete( "posts/" . $dataimage->path, $disk );

					if($dataimage->hidpi) {
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
				
				$i ++;
			}
	
		}

		if( count( $listimages ) ) $row['images'] = implode( "|||", $listimages );
		else $row['images'] = "";

		if( $row['images'] ) $db->query( "UPDATE " . PREFIX . "_images set images='{$row['images']}' WHERE author = '{$author}' AND news_id = '{$news_id}'" );
		else $db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$news_id}'" );

		if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '32', '{$news_id}')" );
	
	}

	if( $user_group[$member_id['user_group']]['allow_file_upload'] AND isset($_POST['files']) AND is_array($_POST['files']) AND count( $_POST['files'] ) ) {
		
		foreach ( $_POST['files'] as $file ) {
			
			if( is_numeric($file) ) {
				
				$file = intval( $file );
				$row = $db->super_query( "SELECT * FROM " . PREFIX . "_files WHERE author = '{$author}' AND news_id = '{$news_id}' AND id='{$file}'" );	
			} else {
				
				$file = $db->safesql( $file );
				$row = $db->super_query( "SELECT * FROM " . PREFIX . "_files WHERE author = '{$author}' AND news_id = '{$news_id}' AND onserver='{$file}'" );
				
			}	

			if ( $row['id'] AND $row['onserver'] ) {
				
				if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
				
				if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';
	
				DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

				$db->query( "DELETE FROM " . PREFIX . "_files WHERE id='{$row['id']}'" );
			}
		
		}

		if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '34', '{$news_id}')" );
	
	}

	if( $user_group[$member_id['user_group']]['admin_static'] AND isset($_POST['static_files']) AND is_array($_POST['static_files']) AND count( $_POST['static_files'] ) ) {
		
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '33', '{$news_id}')" );
					
		foreach ( $_POST['static_files'] as $file ) {
			
			$file = intval( $file );
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_static_files WHERE static_id = '{$news_id}' AND id='{$file}'" );
			
			if( $row['id'] AND $row['onserver'] ) {
					
				if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
				
				if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';
	
				DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

				$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE id='{$row['id']}'" );
			
			} else {
				
				if( $row['id'] ) {
				
					$dataimage = get_uploaded_image_info( $row['name'] );
				
					DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );
					
					if( $dataimage->thumb ) {
						
						DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );
						
					}
					
					if( $dataimage->medium ) {
						
						DLEFiles::Delete( "posts/{$dataimage->folder}/medium/{$dataimage->name}", $row['driver'] );
						
					}
					
					$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE id='{$row['id']}'" );
				
				}
			
			}
		}
	}

	if( $user_group[$member_id['user_group']]['allow_up_image'] AND isset($_POST['comments_files']) AND is_array($_POST['comments_files']) AND count( $_POST['comments_files'] ) ) {
		
		foreach ( $_POST['comments_files'] as $file ) {
			
			$file = intval( $file );

			$row = $db->super_query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE c_id = '{$news_id}' AND id='{$file}' AND author = '{$author}'" );
				
			if( $row['id'] ) {
				
				$dataimage = get_uploaded_image_info( $row['name'] );
				
				DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );
				
				if( $dataimage->thumb ) {
					
					DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );
					
				}
				
				$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE id='{$row['id']}'" );
			
			}
			
		}
	}

	die( "{\"status\": \"ok\"}" );
}

//////////////////////
// go go show
//////////////////////

include (ENGINE_DIR . '/data/videoconfig.php');

$uploaded_list = array();
$images_count = $files_count = 0;

$root = explode("engine/ajax/controller.php", $_SERVER['PHP_SELF']);
$root = reset($root);

if( $area == "template" OR $area == "comments" ) {

	if( $area == "template" ) $db->query( "SELECT id, name FROM " . PREFIX . "_static_files WHERE static_id = '{$news_id}' AND onserver = ''" );
	else $db->query( "SELECT id, name FROM " . PREFIX . "_comments_files WHERE c_id = '{$news_id}' AND author = '{$author}'" );

	while ( $row = $db->get_row() ) {
		
		$images_count ++;

		$image = get_uploaded_image_info( $row['name'], 'posts',  true );
		
		if( $area == "template" ) $del_name = 'static_files';
		else $del_name = "comments_files";

		$img_url =  $image->url;
		$size = $image->size;
		$dimension = $image->dimension;
		
		if( $size ) $size = "({$size})";
		
		if($image->medium) {
			
			$img_url = $image->medium;
			$medium_data = "yes";
			
		} else $medium_data = "no";
		
		if($image->thumb) {
			
			$img_url = $image->thumb;
			$thumb_data = "yes";
			
		} else $thumb_data = "no";

		if ($image->hidpi) {
			$hidpi_data = " data-hidpi=\"{$image->hidpi}\"";
			$hidpi_url = str_replace($image->name, $image->hidpi, $image->url);
			$hidpi_url = " data-srcset=\"{$hidpi_url} 2x\"";
		} else { $hidpi_data = ''; $hidpi_url = '';}

		$file_name = explode("_", $image->name);
		
		if( count($file_name) > 1 AND strlen($file_name[0]) == 10 ) unset($file_name[0]);
		
		$file_name = implode("_", $file_name);
		$base_name = pathinfo($file_name, PATHINFO_FILENAME);
		$file_type = explode(".", $file_name);
		$file_type = totranslit(end($file_type));

$uploaded_list[] = <<<HTML
<div class="file-preview-card" data-type="image" data-area="{$del_name}" data-deleteid="{$row['id']}" data-url="{$image->url}" data-path="{$image->path}" data-thumb="{$thumb_data}" data-medium="{$medium_data}"{$hidpi_data}>
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<div class="file-ext">{$file_type}</div>
		<img src="{$img_url}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$image->name}">{$base_name}</div>
			<div class="file-size-info">{$dimension} {$size}</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a href="{$image->url}"{$hidpi_url} data-highslide="single" rel="tooltip" title="{$lang['up_im_expand']}" target="_blank"><i class="mediaupload-icon mediaupload-icon-zoom"></i></a>
				<a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a>	
			</div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;
	
	}

} else {
		
	$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$news_id}' AND author = '{$author}'" );

	if( isset($row['images']) AND $row['images'] ) {

		$listimages = explode( "|||", $row['images'] );	
		$images_count = count($listimages);

		foreach ( $listimages as $dataimages ) {

			$image = get_uploaded_image_info( $dataimages, 'posts',  true );

			$img_url =  $image->url;
			$size = $image->size;
			$dimension = $image->dimension;
			
			if( $size ) $size = "({$size})";
			
			if($image->medium) {
				
				$img_url = $image->medium;
				$medium_data = "yes";
				
			} else $medium_data = "no";
			
			if($image->thumb) {
				
				$img_url = $image->thumb;
				$thumb_data = "yes";
				
			} else $thumb_data = "no";

			if ($image->hidpi) {
				$hidpi_data = " data-hidpi=\"{$image->hidpi}\"";
				$hidpi_url = str_replace($image->name, $image->hidpi, $image->url);
				$hidpi_url = " data-srcset=\"{$hidpi_url} 2x\"";
			} else { $hidpi_data = ''; $hidpi_url = '';}

			$file_name = explode("_", $image->name);
			
			if( count($file_name) > 1 AND strlen($file_name[0]) == 10) unset($file_name[0]);
			
			$file_name = implode("_", $file_name);
			
			$base_name = pathinfo($file_name, PATHINFO_FILENAME);
			$file_type = explode(".", $file_name);
			$file_type = totranslit(end($file_type));

$uploaded_list[] = <<<HTML
<div class="file-preview-card" data-type="image" data-area="images" data-deleteid="{$image->path}" data-url="{$image->url}" data-path="{$image->path}" data-thumb="{$thumb_data}" data-medium="{$medium_data}"{$hidpi_data}>
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<div class="file-ext">{$file_type}</div>
		<img src="{$img_url}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$image->name}">{$base_name}</div>
			<div class="file-size-info">{$dimension} {$size}</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a href="{$image->url}"{$hidpi_url} data-highslide="single" target="_blank" rel="tooltip" title="{$lang['up_im_expand']}"><i class="mediaupload-icon mediaupload-icon-zoom"></i></a>
				<a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a>
			</div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;

		}
		
	}

}

if( $area != "comments" ) {
	
	if( $area == "template" ) {
		
		$sql_result = $db->query( "SELECT * FROM " . PREFIX . "_static_files WHERE static_id = '{$news_id}' AND onserver != ''" );
		$del_name = 'static_files';
		
	} else {

		$sql_result = $db->query( "SELECT *  FROM " . PREFIX . "_files WHERE author = '{$author}' AND news_id = '{$news_id}'" );
		$del_name = "files";
		
	}

	while ( $row = $db->get_row( $sql_result ) ) {
		$files_count ++;
		
		$data_url = "#";
		$http_url = DLEFiles::GetBaseURL( $row['driver'] );

		if( $row['is_public'] ) {
			
			$uploaded_path = 'public_files/';
			$data_url = $download_url = $http_url . $uploaded_path . $row['onserver'];
			
		} else {
			$uploaded_path = 'files/';
			$download_url = $root . 'index.php?do=download&amp;id='.$row['id'];
		}
		
		if( $row['size'] ) {
			
			$size = formatsize( $row['size'] );
			
		} else {
			
			$size = formatsize( @filesize( ROOT_DIR . "/uploads/" . $uploaded_path . $row['onserver'] ) );
			
		}

		$file_type = explode( ".", $row['name'] );
		$file_type = totranslit( end( $file_type ) );
		$base_name = pathinfo($row['name'], PATHINFO_FILENAME);
		$file_play = "";

		$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-43.755 -32.246)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#f9f9f9" stroke="#cecece" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#cecece"></path></g></svg>';
		$b_color = 'transparent';
		
		if ( in_array($file_type, array('doc', 'docx')) ) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-588.829 -297.644)"><g transform="translate(545.074 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#2a60ae" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#2a60ae"></path></g><g transform="translate(596.025 337.278)"><rect width="17.063" height="3.707" rx="1.853" transform="translate(0 5.926)" fill="#2a60ae"></rect><rect width="11.25" height="3.707" rx="1.853" transform="translate(0 11.851)" fill="#2a60ae"></rect><rect width="30.474" height="3.707" rx="1.853" fill="#2a60ae"></rect></g><path d="M3.42,0A.749.749,0,0,1,2.9-.181.723.723,0,0,1,2.66-.627L.627-12.787A.265.265,0,0,1,.608-12.9a.381.381,0,0,1,.124-.276.381.381,0,0,1,.275-.124H3.5q.551,0,.608.437L5.263-5.681,6.574-9.842a.62.62,0,0,1,.627-.513H8.626a.62.62,0,0,1,.627.513L10.564-5.7l1.178-7.163a.51.51,0,0,1,.171-.332.676.676,0,0,1,.418-.1H14.82a.372.372,0,0,1,.285.124.4.4,0,0,1,.114.276v.114L13.186-.627a.705.705,0,0,1-.247.446A.757.757,0,0,1,12.426,0H10.507a.69.69,0,0,1-.475-.152A.742.742,0,0,1,9.8-.494L7.923-5.757,6.042-.494A.908.908,0,0,1,5.8-.152.69.69,0,0,1,5.32,0Z" transform="translate(597 323)" fill="#2a60ae"></path></g></svg>';
			$b_color = '#e9eff7';
		}
		
		if (in_array($file_type, array('ppt', 'pptx'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-236.224 -502.325)"><g transform="translate(192.469 470.079)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#c64122" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#c64122"></path></g><path d="M1.767,0a.456.456,0,0,1-.332-.143.456.456,0,0,1-.143-.332V-12.806a.5.5,0,0,1,.133-.352.447.447,0,0,1,.342-.142H7.144a6.059,6.059,0,0,1,3.876,1.121,3.953,3.953,0,0,1,1.406,3.287,3.818,3.818,0,0,1-1.406,3.24A6.25,6.25,0,0,1,7.144-4.579H4.978v4.1a.472.472,0,0,1-.133.332A.447.447,0,0,1,4.5,0ZM7.049-7.3A1.747,1.747,0,0,0,8.275-7.7a1.554,1.554,0,0,0,.446-1.206,1.726,1.726,0,0,0-.408-1.2,1.613,1.613,0,0,0-1.264-.456H4.921V-7.3Z" transform="translate(245 527)" fill="#c64122"></path><g transform="translate(4 9)"><rect width="21.546" height="4.463" rx="2.232" transform="translate(249.483 542.098)" fill="#c64122"></rect><path d="M0,10V.03C.245.01.491,0,.74,0A9.443,9.443,0,0,1,10,9.615q0,.193-.008.385Z" transform="translate(261.791 518.347)" fill="#c64122" opacity="0.42"></path><path d="M10.5,21A10.519,10.519,0,0,1,2.8,3.33,10.461,10.461,0,0,1,9.664,0V10.9H21A10.51,10.51,0,0,1,10.5,21Z" transform="translate(250 519.053)" fill="#c64122"></path></g></g></svg>';
			$b_color = '#f9ebe8';
		}
		
		if (in_array($file_type, array('xls', 'xlsx'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-410.326 -502.325)"><g transform="translate(366.571 470.079)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#209c61" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#209c61"></path></g><path d="M.589,0A.381.381,0,0,1,.313-.124.381.381,0,0,1,.19-.4.506.506,0,0,1,.247-.627L4.389-6.783.57-12.673A.506.506,0,0,1,.513-12.9a.381.381,0,0,1,.123-.276A.381.381,0,0,1,.912-13.3H3.819a.79.79,0,0,1,.684.418L6.65-9.576l2.223-3.306a.776.776,0,0,1,.665-.418h2.774a.382.382,0,0,1,.276.124.381.381,0,0,1,.123.276.506.506,0,0,1-.057.228L8.8-6.821l4.18,6.194a.506.506,0,0,1,.057.228.382.382,0,0,1-.124.276A.381.381,0,0,1,12.635,0h-3a.759.759,0,0,1-.665-.38L6.536-3.914,4.161-.38A.759.759,0,0,1,3.5,0Z" transform="translate(419 527)" fill="#209c61"></path><g transform="translate(-2.695 2.152)"><g transform="translate(421.695 536.07)"><rect width="6.546" height="4.463" rx="2" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(7.851)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(15.701)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(15.701 6)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(15.701 12)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(7.851 6)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(7.851 12)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(0 6)" fill="#209c61"></rect><rect width="6.546" height="4.463" rx="2" transform="translate(0 12)" fill="#209c61"></rect></g></g></g></svg>';
			$b_color = '#e8f5ef';
		}
		
		if (in_array($file_type, array('txt'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-934.326 -297.644)"><g transform="translate(890.571 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#c6c8db" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#c6c8db"></path></g><g transform="translate(945.655 324.912)"><rect width="32.783" height="4.463" rx="2.232" transform="translate(0 14.27)" fill="#c6c8db"></rect><rect width="32.783" height="4.463" rx="2.232" transform="translate(0 7.135)" fill="#c6c8db"></rect><rect width="32.783" height="4.463" rx="2.232" fill="#c6c8db"></rect></g></g></svg>';
			$b_color = '#f8f8fb';
		}
		
		if (in_array($file_type, array('pdf'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-760.79 -297.644)"><g transform="translate(717.035 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#fa3225" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#fa3225"></path></g><g transform="translate(768.517 337.278)"><rect width="17.063" height="3.707" rx="1.853" transform="translate(0 5.926)" fill="#fa3225"></rect><rect width="11.25" height="3.707" rx="1.853" transform="translate(0 11.851)" fill="#fa3225"></rect><rect width="30.474" height="3.707" rx="1.853" fill="#fa3225"></rect></g><g transform="translate(762.773 294.187)"><path d="M49.9-138.9a7.264,7.264,0,0,1-3.09-3.893c.326-1.339.84-3.372.449-4.646a1.812,1.812,0,0,0-3.459-.492c-.362,1.324-.029,3.191.586,5.572a67.964,67.964,0,0,1-2.953,6.209c-.007,0-.007.007-.014.007-1.961,1.006-5.326,3.22-3.944,4.921a2.249,2.249,0,0,0,1.556.724c1.3,0,2.584-1.3,4.422-4.472a41.242,41.242,0,0,1,5.717-1.679,10.968,10.968,0,0,0,4.632,1.411,1.873,1.873,0,0,0,1.426-3.141C54.216-139.367,51.292-139.085,49.9-138.9Zm-11.137,6.969a10,10,0,0,1,2.526-2.909C39.713-132.326,38.758-131.877,38.758-131.935Zm6.788-15.841c.608,0,.55,2.67.145,3.394C45.329-145.54,45.336-147.776,45.546-147.776Zm-2.034,11.347a33.39,33.39,0,0,0,2.055-4.537,9.373,9.373,0,0,0,2.5,2.953A26.647,26.647,0,0,0,43.513-136.429Zm10.935-.413s-.413.492-3.1-.651C54.266-137.7,54.744-137.037,54.447-136.842Z" transform="translate(-29.503 163.391)" fill="#fa3225"></path></g></g></svg>';
			$b_color = '#ffeae8';
		}

		if (in_array($file_type, array('apk'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="85" class="file-icon file-ext-' . $file_type . '"><g id="file__x2C__apk__x2C__android__x2C_"><g id="Layer_36"><g><g><polygon points="107.07,25.535 349.078,25.535 420.6,97.777 420.6,447.809 107.07,447.809" style="fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;"/><path d="M420.6,454.668H107.07c-3.79,0-6.859-3.07-6.859-6.859V25.535c0-3.791,3.069-6.86,6.859-6.86 h242.008c1.831,0,3.583,0.729,4.877,2.032l71.522,72.243c1.271,1.285,1.986,3.019,1.986,4.827v350.031 C427.464,451.598,424.39,454.668,420.6,454.668z M113.931,440.949h299.813v-340.35l-67.525-68.204H113.931V440.949z" style="fill:#4C8CF9;"/></g><g><rect height="105.598" style="fill-rule:evenodd;clip-rule:evenodd;fill:#4C8CF9;" width="313.531" x="129.979" y="177.687"/></g><g><path d="M213.491,258.4h-11.189v-9.82h11.189V258.4z M254.513,246.423h-19.446l-3.861,11.977h-11.189 l19.047-55.963h11.456L269.43,258.4h-11.187L254.513,246.423z M237.729,237.682h13.986l-6.927-22.062h-0.133L237.729,237.682z M285.815,238.086V258.4h-11.191v-55.963h22.112c6.392,0,11.319,1.615,15.05,4.974c3.598,3.23,5.461,7.534,5.461,12.917 s-1.863,9.687-5.461,12.917c-3.73,3.226-8.658,4.841-15.05,4.841H285.815z M285.815,229.473h10.921 c3.061,0,5.46-0.803,7.057-2.551c1.597-1.752,2.396-3.905,2.396-6.46c0-2.689-0.799-4.974-2.396-6.727 c-1.597-1.748-3.996-2.689-7.057-2.689h-10.921V229.473z M342.686,235.126h-6.13V258.4h-11.187v-55.963h11.187v22.87h4.8 l14.784-22.87h13.582l-18.776,26.1L371.19,258.4h-13.587L342.686,235.126z" style="fill:#FEFEFE;"/></g><g><path d="M263.497,146.43c-14.012,0-26.189-5.08-32.584-13.591c-2.275-3.028-1.665-7.328,1.365-9.604      c3.028-2.28,7.328-1.666,9.606,1.363c3.761,5.006,12.038,8.113,21.612,8.113c0.004,0,0.009,0,0.018,0 c9.691-0.005,18.639-3.263,22.269-8.108c2.276-3.038,6.575-3.648,9.604-1.372c3.032,2.271,3.647,6.571,1.376,9.604 c-6.286,8.379-19.02,13.592-33.239,13.596C263.515,146.43,263.506,146.43,263.497,146.43z" style="fill:#4C8CF9;"/></g><g><path d="M143.968,233.241c-0.025,0-0.051,0-0.078,0c-41.683-0.455-66.408-11.325-73.488-32.308 c-9.214-27.302,17.032-62.657,32.452-74.669c2.99-2.322,7.303-1.785,9.63,1.197c2.328,2.992,1.791,7.3-1.195,9.631 c-13.252,10.32-34.507,39.833-27.888,59.454c6.248,18.51,36.424,22.708,60.641,22.975c3.787,0.041,6.825,3.147,6.784,6.933 C150.783,230.216,147.721,233.241,143.968,233.241z" style="fill:#4C8CF9;"/></g><g><polygon points="349.078,97.777 420.6,97.777 349.078,25.535" style="fill-rule:evenodd;clip-rule:evenodd;fill:#D4E4FF;"/><path d="M420.6,104.637h-71.521c-3.79,0-6.86-3.07-6.86-6.86V25.535c0-2.781,1.68-5.286,4.249-6.346 c2.579-1.055,5.534-0.454,7.488,1.519l71.522,72.243c1.95,1.968,2.519,4.914,1.454,7.47 C425.867,102.976,423.371,104.637,420.6,104.637z M355.938,90.917h48.217l-48.217-48.703V90.917z" style="fill:#4C8CF9;"/></g><g><path d="M239.338,493.326c-0.364,0-0.731-0.027-1.104-0.088l-22.509-3.633      c-3.322-0.537-5.766-3.406-5.766-6.773v-35.023c0-3.791,3.069-6.859,6.859-6.859c3.791,0,6.86,3.068,6.86,6.859v29.182      l16.743,2.703c3.74,0.605,6.282,4.125,5.679,7.865C245.558,490.932,242.645,493.326,239.338,493.326z" style="fill:#4C8CF9;"/></g><g><path d="M325.249,493.326c-0.367,0-0.733-0.027-1.105-0.088l-22.507-3.633      c-3.322-0.537-5.768-3.406-5.768-6.773v-35.023c0-3.791,3.069-6.859,6.859-6.859s6.86,3.068,6.86,6.859v29.182l16.743,2.703      c3.74,0.605,6.282,4.125,5.681,7.865C331.467,490.932,328.554,493.326,325.249,493.326z" style="fill:#4C8CF9;"/></g><g><path d="M290.872,65.221c6.259-1.748,13.188,0.941,16.519,6.86      c4.13,7.13,1.73,16.142-5.332,20.18c-7.057,4.171-15.978,1.749-19.979-5.382c-3.193-5.516-2.396-12.375,1.469-16.95      c1.331,1.748,3.726,2.285,5.727,1.211C291.275,69.929,292.074,67.24,290.872,65.221L290.872,65.221z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#4C8CF9;"/></g><g><path d="M228.808,65.221c6.259-1.748,13.185,0.941,16.649,6.86      c3.994,7.13,1.597,16.142-5.463,20.18c-6.924,4.171-15.981,1.749-19.979-5.382c-3.196-5.516-2.397-12.375,1.466-16.95      c1.331,1.748,3.861,2.285,5.727,1.211C229.34,69.929,230.005,67.24,228.808,65.221L228.808,65.221z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#4C8CF9;"/></g><g><path d="M301.793,328.887l13.985-23.539c1.598-2.693,0.67-6.189-1.862-7.805      c-2.8-1.611-6.126-0.809-7.728,1.885l-14.784,24.889c-8.787-3.098-18.244-4.979-28.101-4.979c-9.855,0-19.18,1.881-28.104,4.979      l-14.648-24.889c-1.732-2.693-5.063-3.496-7.728-1.885c-2.664,1.615-3.595,5.111-1.996,7.805l13.983,23.539      c-27.038,14.123-45.551,42.508-45.551,75.334c0,3.23,2.531,5.648,5.594,5.648H341.75c3.203,0,5.598-2.418,5.598-5.648      C347.348,371.395,328.833,343.01,301.793,328.887L301.793,328.887z M190.716,398.572c2.797-37.938,34.361-67.938,72.588-67.938      c38.36,0,69.792,30,72.72,67.938H190.716z" style="fill:#4C8CF9;"/></g><g><path d="M341.75,415.656H184.854c-3.063,0-5.594,2.557-5.594,5.648v26.504h11.188v-20.852h145.714v20.852      h11.187v-26.504C347.348,418.213,344.953,415.656,341.75,415.656L341.75,415.656z" style="fill:#4C8CF9;"/></g><g><path d="M373.585,315.836c-10.787,0-19.579,8.885-19.579,19.777v107.758c0,1.479,0.133,3.092,0.532,4.438      h11.99c-0.799-1.211-1.331-2.826-1.331-4.438V335.613c0-4.574,3.859-8.475,8.521-8.475c4.528,0,8.392,3.9,8.392,8.475v107.758      c0,1.611-0.532,3.227-1.33,4.438h11.985c0.399-1.346,0.532-2.959,0.532-4.438V335.613      C393.298,324.721,384.506,315.836,373.585,315.836L373.585,315.836z" style="fill:#4C8CF9;"/></g><g><path d="M153.954,415.656c-10.79,0-19.579,8.879-19.579,19.777v12.375h11.187v-12.375      c0-4.709,3.864-8.477,8.525-8.477c4.529,0,8.391,3.768,8.391,8.477v12.375h11.189v-12.375      C173.667,424.535,164.875,415.656,153.954,415.656L153.954,415.656z" style="fill:#4C8CF9;"/></g><g><path d="M233.868,356.197c4.396,0,7.991,3.631,7.991,8.072      c0,4.438-3.595,8.07-7.991,8.07c-4.263,0-7.857-3.633-7.857-8.07C226.011,359.828,229.605,356.197,233.868,356.197      L233.868,356.197z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#4C8CF9;"/></g><g><path d="M292.739,356.197c4.392,0,7.86,3.631,7.86,8.072      c0,4.438-3.469,8.07-7.86,8.07c-4.396,0-7.993-3.633-7.993-8.07C284.746,359.828,288.344,356.197,292.739,356.197      L292.739,356.197z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#4C8CF9;"/></g></g></g></g><g /></svg>';
			$b_color = '#e9eff7';
		}

		if (in_array($file_type, array('7z', 'rar', 'zip', 'gz'))) {
			$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-412.5 -297.644)"><g transform="translate(368.745 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#891fa8" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#891fa8"></path></g><g transform="translate(435.02 313.808)"><g transform="translate(3.765)"><rect width="5.04" height="3.26" rx="1.63" transform="translate(3.26) rotate(90)" fill="#891fa8"></rect><rect width="5.04" height="3.26" rx="1.63" transform="translate(3.26 6.155) rotate(90)" fill="#891fa8"></rect><rect width="5.04" height="3.26" rx="1.63" transform="translate(3.26 12.31) rotate(90)" fill="#891fa8"></rect></g><path d="M0,9.67A6.031,6.031,0,0,1,2.219,4.906L3.406,0H6.984L8.238,5.015A6.057,6.057,0,0,1,10.316,9.67c0,3.207-2.307,5.8-5.156,5.8S0,12.877,0,9.67Z" transform="translate(0 18.548)" fill="#891fa8"></path><ellipse cx="2.5" cy="2" rx="2.5" ry="2" transform="translate(2.658 26.285)" fill="#fff"></ellipse></g></g></svg>';
			$b_color = '#f4e9f5';
		}

		if( in_array( $file_type, $allowed_video ) ) {
			$data_url = $http_url . $uploaded_path . $row['onserver'];
			
			if( in_array($file_type, array('mp3', 'flac', 'aac', 'ogg')) ) {
					
				$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-240.5 -297.644)"><g transform="translate(196.745 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#ffa734" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#ffa734"></path></g><path d="M23.3-6.68H21.372l-.759-3.432a.778.778,0,0,0-.723-.574.778.778,0,0,0-.722.571l-1.179,5.2-1.225-8.7a.765.765,0,0,0-.735-.636.761.761,0,0,0-.737.653L14.2-4.319,12.61-15.984a.764.764,0,0,0-.735-.64.764.764,0,0,0-.735.64L9.551-4.318,8.456-13.6a.761.761,0,0,0-.737-.654.764.764,0,0,0-.735.638L5.76-4.908,4.582-10.114a.778.778,0,0,0-.722-.572.778.778,0,0,0-.723.573L2.378-6.68H.445A.445.445,0,0,0,0-6.234v.594A.445.445,0,0,0,.445-5.2H2.972a.772.772,0,0,0,.719-.575l.173-.74L5.215-.573A.719.719,0,0,0,5.966,0h.008a.769.769,0,0,0,.7-.637l.983-7.027L8.762,1.721a.742.742,0,0,0,1.473.013L11.875-10.3l1.64,12.037a.742.742,0,0,0,1.473-.013L16.1-7.664l.983,7.026a.771.771,0,0,0,.7.638.717.717,0,0,0,.755-.573L19.886-6.51l.173.739a.772.772,0,0,0,.72.576H23.3a.445.445,0,0,0,.445-.445v-.594A.445.445,0,0,0,23.3-6.68Z" transform="translate(256.344 339.5)" fill="#ffa734"></path></g></svg>';
				$b_color = '#fff6ea';
				$file_play = "audio";
				
			} else {

				$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-'.$file_type.'"><g transform="translate(-586.74 -502.325)"><g transform="translate(542.985 470.079)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#04a0b2" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#04a0b2"></path></g><g transform="translate(0.887 3.384)"><g transform="translate(603.613 524.116)"><path d="M3,16a3,3,0,0,1-3-3V3A3,3,0,0,1,3,0h8.3a3,3,0,0,1,3,3V5.943L20.471,2.1A1,1,0,0,1,22,2.944V13.055a1,1,0,0,1-1.529.849L14.3,10.057V13a3,3,0,0,1-3,3Z" fill="#04a0b2"></path></g></g></g></svg>';
				$b_color = '#e5f5f7';
				$file_play = "video";
			}
			
		}

$uploaded_list[] = <<<HTML
<div class="file-preview-card" data-type="file" data-area="{$del_name}" data-deleteid="{$row['id']}" data-url="{$data_url}" data-path="{$row['id']}:{$row['name']}" data-play="{$file_play}" data-public="{$row['is_public']}">
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content" style="background-color: {$b_color};">
		<div class="file-ext">{$file_type}</div>
		{$file_icon}
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="ID: {$row['id']}, {$row['name']}">{$base_name}</div>
			<div class="file-size-info">({$size})</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a href="{$download_url}" class="position-left" rel="tooltip" title="{$lang['plugins_a_3']}" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M7.646 10.854a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 9.293V5.5a.5.5 0 0 0-1 0v3.793L6.354 8.146a.5.5 0 1 0-.708.708z"/><path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383m.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z"/></svg></a><a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a>
			</div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;


	}

	$db->free($sql_result);
}

if ( count ($uploaded_list) ) $uploaded_list = implode("", $uploaded_list); else $uploaded_list = "";

$image_align = array ('0' => '', 'left' => '', 'right' => '', 'center' => '');
$image_align[$config['image_align']] = "selected";

if( $user_group[$member_id['user_group']]['allow_file_upload'] ) {
		
	if( $user_group[$member_id['user_group']]['max_file_size'] ) {
			
		$lang['files_max_info'] = $lang['files_max_info'] . " " . formatsize( (int)$user_group[$member_id['user_group']]['max_file_size'] * 1024 );
		
	} else {
			
		$lang['files_max_info'] = $lang['files_max_info_2'];
		
	}
		
	$lang['files_max_info_1'] = $lang['files_max_info'] . "<br>" . $lang['files_max_info_1'] . " " . formatsize( (int)$config['max_up_size'] * 1024 );
	
} else {
		
	$lang['files_max_info_1'] = $lang['files_max_info_1'] . " " . formatsize( (int)$config['max_up_size'] * 1024 );
	
}

$max_images_allowed = -1;
$max_files_allowed = -1;

if( $area != "template" AND $area != "adminupload" AND $area != "comments" AND $user_group[$member_id['user_group']]['max_images'] ) {

	$max_images_allowed = intval($user_group[$member_id['user_group']]['max_images']) - $images_count;

	$lang['files_max_info_4'] = str_ireplace (array('{count}', '{uploaded}', '{allowed}'), array($user_group[$member_id['user_group']]['max_images'], '<span id="imagesuploaded">'.$images_count.'</span>', '<span id="imagesallowmore">'.$max_images_allowed.'</span>'), $lang['files_max_info_4'] );
	
	$lang['files_max_info_1'] .=  "<br>".$lang['files_max_info_4'];
	
}

if( $area == "comments" AND $user_group[$member_id['user_group']]['up_count_image'] ) {

	$max_images_allowed = intval($user_group[$member_id['user_group']]['up_count_image']) - $images_count;

	$lang['files_max_info_4'] = str_ireplace (array('{count}', '{uploaded}', '{allowed}'), array($user_group[$member_id['user_group']]['up_count_image'], '<span id="imagesuploaded">'.$images_count.'</span>', '<span id="imagesallowmore">'.$max_images_allowed.'</span>'), $lang['files_max_info_4'] );
	
	$lang['files_max_info_1'] .=  "<br>".$lang['files_max_info_4'];

}

if( $area != "template" AND $user_group[$member_id['user_group']]['max_files'] ) {

	$max_files_allowed = intval($user_group[$member_id['user_group']]['max_files']) - $files_count;

	$lang['files_max_info_5'] = str_ireplace (array('{count}', '{uploaded}', '{allowed}'), array($user_group[$member_id['user_group']]['max_files'], '<span id="filesuploaded">'.$files_count.'</span>', '<span id="filesallowmore">'.$max_files_allowed.'</span>'), $lang['files_max_info_5'] );
	
	$lang['files_max_info_1'] .=  "<br>".$lang['files_max_info_5'];

}


$upload_param = "";

if( $user_group[$member_id['user_group']]['allow_image_size'] ) {
	
	$t_seite_selected = array('0' => '', '1' => '', '2' => '');
	$t_seite_selected[$config['t_seite']] = "selected";

	if ( $config['max_image'] )	{

		$upload_param .= <<<HTML
<div class="checkbox"><label class="checkbox-inline margin-left form-check-label"><input class="icheck form-check-input" type="checkbox" name="make_thumb" id="make_thumb" value="1" checked="checked"><span>{$lang['images_ath']}</span></label><input class="classic margin-left" type="text" name="t_size" id="t_size" style="width:100px;" autocomplete="off" value="{$config['max_image']}"><select name="t_seite" id="t_seite" class="uniform"><option value="0" {$t_seite_selected[0]}>{$lang['upload_t_seite_1']}</option><option value="1" {$t_seite_selected[1]}>{$lang['upload_t_seite_2']}</option><option value="2" {$t_seite_selected[2]}>{$lang['upload_t_seite_3']}</option></select></div>
HTML;

	}

	if ( $config['medium_image'] )	{

		$upload_param .= <<<HTML
<div class="checkbox"><label class="checkbox-inline margin-left form-check-label"><input class="icheck form-check-input" type="checkbox" name="make_medium" id="make_medium" value="1" checked="checked"><span>{$lang['images_amh']}</span></label><input class="classic margin-left" type="text" name="m_size" id="m_size" style="width:100px;" autocomplete="off" value="{$config['medium_image']}"><select name="m_seite" id="m_seite" class="uniform"><option value="0" {$t_seite_selected[0]}>{$lang['upload_t_seite_1']}</option><option value="1" {$t_seite_selected[1]}>{$lang['upload_t_seite_2']}</option><option value="2" {$t_seite_selected[2]}>{$lang['upload_t_seite_3']}</option></select></div>
HTML;

	}

	if( $config['allow_watermark'] ) $upload_param .= "<div class=\"checkbox\"><label class=\"checkbox-inline margin-left form-check-label\"><input class=\"icheck form-check-input\" type=\"checkbox\" name=\"make_watermark\" value=\"yes\" id=\"make_watermark\" checked=\"checked\"><span>{$lang['images_water']}</span></label></div>";

	if ( $area != "comments" ) $upload_param .= "<div class=\"checkbox\"><label class=\"checkbox-inline margin-left form-check-label\"><input class=\"icheck form-check-input\" type=\"checkbox\" name=\"hidpi\" value=\"1\" id=\"hidpi\"><span>{$lang['hidpi_upl']}</span></label></div>";

}

if( $user_group[$member_id['user_group']]['allow_public_file_upload'] AND $area != "comments") $upload_param .= "<div class=\"checkbox\"><label class=\"checkbox-inline margin-left form-check-label\"><input class=\"icheck form-check-input\" type=\"checkbox\" name=\"public_file\" value=\"1\" id=\"public_file\"><span>{$lang['public_file_upl']}</span></label></div>";

if( $member_id['user_group'] == 1 AND $area != "comments" ) {
	
	$locate = "FTP /uploads/files/";
	
	if( DLEFiles::getDefaultStorage() ) {
		$locate = "Remote /files/";
	}

	$ftp_input = <<<HTML
	<div class="mediaupload-row">
		<div class="mediaupload-col1">
			{$locate}
		</div>
		<div class="mediaupload-col2">
			<input class="classic" type="text" id="ftpurl" name="ftpurl" autocomplete="off" style="width:100%;max-width:400px;">
		</div>
		<div class="mediaupload-col3">
			<button onclick="upload_from_url('ftp'); return false;">{$lang['db_load_a']}</button>
		</div>
	</div>
	<div id="upload-viaftp-status"></div>
HTML;

} else $ftp_input = "";

$storage_input = "";

if ($user_group[$member_id['user_group']]['allow_change_storage'] AND $area != "comments") {


	$storages_list = DLEFiles::getStorages();

	if( count( $storages_list ) ) {

		$storages_list = array('-1' => $lang['storage_default'], '0' => $lang['opt_sys_imfs_1']) + $storages_list;

		$storages_select = "<select class=\"uniform\" name=\"upload_driver\" id=\"upload_driver\">\r\n";

		foreach ($storages_list as $value => $description) {

			$storages_select .= "<option value=\"{$value}\"";

			if ($value == '-1') {
				$storages_select .= " selected ";
			}

			$storages_select .= ">{$description}</option>\n";
		}

		$storages_select .= "</select>";

		$storage_input = <<<HTML
	<div class="mediaupload-row">
		<div class="mediaupload-col1">
			<div class="margin-left">{$lang['storage_upload']}</div>
		</div>
		<div class="mediaupload-col2">
			{$storages_select}
		</div>
	</div>
	<div id="upload-viaftp-status"></div>
HTML;

	}

}

	
	if( $user_group[$member_id['user_group']]['allow_file_upload'] ) {
		
		if( ! $user_group[$member_id['user_group']]['max_file_size'] ) $max_file_size = 0;
		elseif( $user_group[$member_id['user_group']]['max_file_size'] > $config['max_up_size'] ) $max_file_size = ( int ) $user_group[$member_id['user_group']]['max_file_size'];
		else $max_file_size = ( int )$config['max_up_size'];
	
	} else {
		
		$max_file_size = ( int )$config['max_up_size'];
	
	}

	$max_file_size = $max_file_size * 1024;

	$image_ext =implode( ",", $allowed_extensions );

	if( $config['files_allow'] and $user_group[$member_id['user_group']]['allow_file_upload'] ) {

		$file_ext = ',{title : "Another files", extensions : "'. implode( ",", $allowed_files ) . '"}';

	} else $file_ext = '';

	$author = urlencode($author);
	
	if( $area != "comments") {
		$gen_tab = "<li><a href='#' id=\"link3\" onclick=\"tabClick(1); return false;\" title=\"{$lang['images_lgem']}\"><span>{$lang['images_lgem']}</span></a></li>";
		$hidden_params="";
	} else {
		$gen_tab = "";
		$hidden_params=" style=\"display:none;\"";
	}
	
echo <<<HTML
<div class="tabs">
	<div class="tabsitems">
	  <ul>
		<li><a href='#' id="link1" onclick="tabClick(2); return false;" title='{$lang['media_upload_st']}' class="current" ><span>{$lang['media_upload_st']}</span></a></li>
		<li><a href='#' id="link2" onclick="tabClick(0); return false;" title='{$lang['images_iln']}'><span>{$lang['images_iln']}</span></a></li>
		{$gen_tab}
	  </ul>
	</div>
	<div id="check-all-box">
	  <label class="form-check-label"><input class="icheck form-check-input" type="checkbox" name="check_all" id="check_all" value="1"  onchange="check_all(this); return false;"><span class="position-right">{$lang['edit_selall']}</span></label>
	</div>
</div>
<div style="clear: both;"></div>
<div class="mediaupload-box">
<div id="stmode" class="file-upload-box" >
	<div class="media-upload-button-area">
		<div id="file-uploader"></div>
	</div>
	<div class="mediaupload-row">
		<div class="mediaupload-col1">
			{$lang['images_upurl']}
		</div>
		<div class="mediaupload-col2">
			<input class="classic" type="text" id="copyurl" name="copyurl" autocomplete="off" style="width:100%;max-width:400px;">
		</div>
		<div class="mediaupload-col3">
			<button onclick="upload_from_url('url'); return false;">{$lang['db_load_a']}</button>
		</div>
	</div>
	<div id="upload-viaurl-status"></div>
	{$ftp_input}
	{$storage_input}
	<div class="upload-options">{$upload_param}</div>
	<div class="upload-restriction">{$lang['files_max_info_1']}</div>
</div>
<div id="cont1" class="file-preview-box file-can-all-selected" style="display:none;">{$uploaded_list}</div>
<div id="cont2" style="display:none;"></div>

<div id="mediaupload-buttonpane" style="display:none;">
	<div class="mediaupload-insert-params" style="display:none;">
		<div class="mediaupload-image-title" style="display:none;">
			<div class="insert-imagetitle"><input id="imagetitle" name="imagetitle" type="text" value="" placeholder="{$lang['media_upload_title']}" class="classic" autocomplete="off" style="width:100%;"></div>
			<div class="insert-properties"><span class="margin-left">{$lang['images_align']}</span><select id="imagealign" name="imagealign" class="dropup uniform" data-width="auto" data-dropdown-align-right="true" data-dropup-auto="false">
				  <option value="none" {$image_align[0]}>{$lang['opt_sys_no']}</option>
				  <option value="left" {$image_align['left']}>{$lang['images_left']}</option>
				  <option value="right" {$image_align['right']}>{$lang['images_right']}</option>
				  <option value="center" {$image_align['center']}>{$lang['images_center']}</option>
				</select>
		</div>
		</div>
		<div class="mediaupload-thumbs-params" style="display:none;"><span class="mediaupload-insert-descr">{$lang['media_upload_b1']}</span>
			<label id="mediaupload-thumb" class="radio-inline form-check-label" style="display:none;"><input class="icheck form-check-input" type="radio" name="thumbimg" id="thumbimg" value="1"><span>{$lang['media_upload_ip2']}</span></label>
			<label id="mediaupload-medium" class="radio-inline form-check-label" style="display:none;"><input class="icheck form-check-input" type="radio" name="thumbimg" id="thumbimg1" value="2"><span>{$lang['media_upload_ip6']}</span></label>
			<label id="mediaupload-original" class="radio-inline margin-left form-check-label" style="display:none;"><input class="icheck form-check-input" type="radio" name="thumbimg" id="thumbimg2" value="0"><span>{$lang['media_upload_ip3']}</span></label>
			<label id="mediaupload-enlarge" class="checkbox-inline form-check-label" style="display:none;"><input class="icheck form-check-input" type="checkbox" name="insertoriginal" id="insertoriginal" value="1" checked="checked"><span>{$lang['media_upload_ip7']}</span></label>
		</div>
		
		<div class="mediaupload-file-params" style="display:none;"><span class="mediaupload-insert-descr">{$lang['media_upload_b2']}</span>
			<label class="radio-inline form-check-label"><input id="attachfordownload" class="icheck form-check-input" type="radio" name="filemode" value="1"><span>{$lang['media_upload_ip4']}</span></label>
			<label class="radio-inline form-check-label"><input id="attachforplayer" class="icheck form-check-input" type="radio" name="filemode" value="0" checked="checked"><span>{$lang['media_upload_ip5']}</span></label>
		</div>
		
	</div>
	<div class="mediaupload-footer ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
		<div class="ui-dialog-buttonset">
		<button type="button" class="ui-button" onclick="$('#mediaupload').dialog('close'); return false;">{$lang['p_cancel']}</button>
		<button id='mediaupload-insert' type="button" onclick="media_insert_selected(); return false;" class="ui-button bg-teal" style="display:none;">{$lang['images_all_insert']}</button>
		<button id='mediaupload-delete' type="button" onclick="media_delete_selected(); return false;" class="ui-button" style="display:none;">{$lang['images_del']}</button>
		</div>
	</div>
</div>
HTML;



$max_file_size = number_format($max_file_size, 0, '', '');
$config['file_chunk_size'] =  number_format(floatval($config['file_chunk_size']), 1, '.', '');
if ($config['file_chunk_size'] < 1) $config['file_chunk_size'] = '1.5';

if ( $uploaded_list ) $im_show = "tabClick(0);"; else $im_show = "";

if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

echo <<<HTML
<script>
jQuery(function($){

	setTimeout(function() {
		initmediauploadpopup();
	}, 200);

});

var plupoad_ui_plugin_loaded = true;
var max_images_allowed = {$max_images_allowed};
var max_files_allowed = {$max_files_allowed};

function initmediauploadpopup() {
	
	LoadDLEFont();
	RestoreDefaultUploadOptions();

	if (typeof $.fn.selectpicker === "function") {
	
		$('.dle-popup-mediaupload select.uniform').selectpicker();
		
		$('.dle-popup-mediaupload select.uniform').on('hide.bs.select', function () {
		
			setTimeout(function() {
				$('.dle-popup-mediaupload .insert-properties .btn-group.bootstrap-select.uniform').addClass('dropup');
			}, 10);
		
		});
	
	}
	
	if (typeof $.fn.tooltip === "function") {
	
		$('[rel=tooltip]').tooltip({
		  container: 'body'
		});
	
	}
	
	if (typeof $.fn.uniform === "function") {
		
		$(".dle-popup-mediaupload .icheck").uniform({
			radioClass: 'choice',
			wrapperClass: 'border-teal-600 text-teal-800',
			fileButtonClass: 'btn bg-teal btn-sm btn-raised'
		});

	}

	$(document).off("click", '.file-preview-card .clipboard-copy-link');
	$(document).off("click", '.file-preview-card .file-delete-link');
	$(document).on("click", '.file-preview-card .file-delete-link',	function(e){
		e.preventDefault();
		media_delete_file( $(this).closest('.file-preview-card') );
		
		return false;
	});

	$(document).on("click", '.file-preview-card .clipboard-copy-link',	function(e){
	
		e.preventDefault();
		document.activeElement.blur();
		var box = $(this).closest('.file-preview-card');
		var copytext = '';

		if ( box.data('type') == 'image') {
		
			copytext = box.data('url');
			
		} else {
		
			if ( (box.data('play') == "video" || box.data('play') == "audio") && $('#attachforplayer').prop('checked') ) {
				copytext = '['+box.data('play')+'='+box.data('url')+']';
			} else {
				if(box.data('public') == "1") {
					copytext = box.data('url');
				} else {
					copytext = '[attachment='+box.data('path')+']';
				}
			}

		}
		
		DLEcopyToClipboard(copytext);
		
		return false;
	});	

	$(document).off("click", '.file-preview-card .file-content:not(.select-disable)');
	$(document).on("click", '.file-preview-card .file-content:not(.select-disable)', function(e){
		e.preventDefault();
		$(this).parent().toggleClass("active");
		insert_props_panel();
		
		return false;
	});


	if (typeof $.fn.plupload !== "function" ) {

		$.getCachedScript('{$root}engine/classes/uploads/html5/plupload/plupload.full.min.js?v={$config['cache_id']}').done(function() {
			$.getCachedScript('{$root}engine/classes/uploads/html5/plupload/plupload.ui.min.js?v={$config['cache_id']}').done(function() {
				$.getCachedScript('{$root}engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js?v={$config['cache_id']}').done(function() {
					loadmediauploader();
				});
			});	
		});
		
	} else {
		loadmediauploader();
	}

	if (typeof Fancybox == "undefined" ) {

		$.getCachedScript( '{$root}engine/classes/fancybox/fancybox.js?v={$config['cache_id']}' );
	}

	setTimeout(function() {
		get_shared_list('');
	}, 1000);
	
};

function RestoreDefaultUploadOptions() {
	
	LoadUploadOptions();
	
	$('#mediaupload .upload-options select, #mediaupload .upload-options input[type="checkbox"], #mediaupload .upload-options input[type="text"], #upload_driver').change(function() {
		SaveUploadOptions( $(this) );
	});
};

function LoadUploadOptions() {

    try {
        var savedData = localStorage.getItem('dle_upload_options');
        if (savedData) {
			try {
				SavedEL = JSON.parse(savedData);

				for (var key in SavedEL) {
					if (SavedEL.hasOwnProperty(key)) {
						if( SavedEL[key][0] == 'checkbox' ) {
							$('#' + key).prop('checked', SavedEL[key][1]);
						} else {
							$('#' + key).val(SavedEL[key][1]);
						}
					}
				}
			} catch (e) {
				SavedEL = {};
			}
        }
    } catch (e) {}

};

function SaveUploadOptions(el) {
    var value = null;
    var SavedEL = {};

    try {
        var savedData = localStorage.getItem('dle_upload_options');
        if (savedData) {
			try {
				SavedEL = JSON.parse(savedData);
			} catch (e) {
				SavedEL = {};
			}
        }
    } catch (e) {}

    if (el.attr('type') === 'checkbox') {
        value = el.is(':checked');
    } else {
        value = el.val();
    }

	var key = el.attr('id');
	var values = [el.attr('type'), value];

    SavedEL[key] = values;

    try {
        localStorage.setItem('dle_upload_options', JSON.stringify(SavedEL));
    } catch (e) {}

};

function LoadDLEFont() {
    const elem = document.createElement('i');
    elem.className = 'mediaupload-icon';
	elem.style.position = 'absolute';
	elem.style.left = '-9999px';
	document.body.appendChild(elem);

	if ($( elem ).css('font-family') !== 'mediauploadicons') {
		$('head').append('<link rel="stylesheet" type="text/css" href="{$root}engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css">');
	}
  
    document.body.removeChild(elem);
};

function DLEcopyToClipboard(text) {

   try {
		const elem = document.createElement('textarea');
		elem.value = text;
		elem.setAttribute('readonly', '');
		elem.style.position = 'absolute';
		elem.style.left = '-9999px';
		document.body.appendChild(elem);
		elem.select();
		document.execCommand('copy');
		document.body.removeChild(elem);
		
		DLEPush.info('{$lang['up_im_copy1']}', '', 2000);
	
  } catch (err) {
  
    console.log('Unable to copy');
	
  }

};

function loadmediauploader() {

	var totaluploaded = 0;

	$("#file-uploader").plupload({

		runtimes: 'html5',
		url: "{$root}engine/ajax/controller.php?mod=upload",
		file_data_name: "qqfile",
 
		max_file_size: '{$max_file_size}',
 
		chunk_size: '{$config['file_chunk_size']}mb',
 
		filters: [
			{title : "Image files", extensions : "{$image_ext}"}{$file_ext}
		],
		
		rename: true,
		sortable: true,
		dragdrop: true,
 
		views: {
			list: true,
			thumbs: true,
			remember: true,
			active: 'list'
		},
		
		multipart_params: {"subaction" : "upload", "news_id" : "{$news_id}", "area" : "{$area}", "author" : "{$author}", "user_hash" : "{$dle_login_hash}"},
		
		ready: function(event, args) {
			{$im_show}
		},

		started: function(event, args) {
			var uploader = args.up;

			uploader.settings.multipart_params['t_size'] = $('#t_size').val();
			uploader.settings.multipart_params['t_seite'] = $('#t_seite').val();
			uploader.settings.multipart_params['make_thumb'] = $("#make_thumb").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['m_size'] = $('#m_size').val();
			uploader.settings.multipart_params['m_seite'] = $('#m_seite').val();
			uploader.settings.multipart_params['make_medium'] = $("#make_medium").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['make_watermark'] = $("#make_watermark").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['public_file'] = $("#public_file").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['hidpi'] = $("#hidpi").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['upload_driver'] = $('#upload_driver').val();
			
		},
		
		selected: function(event, args) {
			var uploader = args.up;
			var image_extensions = ["gif", "jpg", "png", "jpeg", "webp" , "bmp", "avif"];
			var images_each_count = 0;
			var files_each_count = 0;
			var count_errors = false;

			uploader.settings.multipart_params['t_size'] = $('#t_size').val();
			uploader.settings.multipart_params['t_seite'] = $('#t_seite').val();
			uploader.settings.multipart_params['make_thumb'] = $("#make_thumb").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['m_size'] = $('#m_size').val();
			uploader.settings.multipart_params['m_seite'] = $('#m_seite').val();
			uploader.settings.multipart_params['make_medium'] = $("#make_medium").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['make_watermark'] = $("#make_watermark").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['public_file'] = $("#public_file").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['hidpi'] = $("#hidpi").is(":checked") ? 1 : 0;
			uploader.settings.multipart_params['upload_driver'] = $('#upload_driver').val();

			$('.plupload_container').addClass('plupload_files_selected');

			plupload.each(uploader.files, function(file) {
				var queue_name = file.name
				var fileext = queue_name.split('.').pop();

				if ( jQuery.inArray( fileext, image_extensions ) >=0 ) {
					images_each_count ++;

					if(max_images_allowed > -1 && images_each_count > max_images_allowed ) {
						count_errors = true;

						setTimeout(function() {
							uploader.removeFile( file );
						}, 100);

					}

				} else {

					files_each_count ++;

					if(max_files_allowed > -1 && files_each_count > max_files_allowed ) {
						count_errors = true;

						setTimeout(function() {
							uploader.removeFile( file );
						}, 100);

					}

				}

			});

			if( count_errors ) {
				$('#file-uploader').plupload('notify', 'error', "{$lang['error_max_queue']}");
			}

			$('#file-uploader').plupload('refresh');

		},

		removed: function(event, args) {
			if(args.up.files.length) {
				$('.plupload_container').addClass('plupload_files_selected');
			} else {
				$('.plupload_container').removeClass('plupload_files_selected');
			}
			$('#file-uploader').plupload('refresh');
		},

		uploaded: function(event, args) {
		
			try {
			   var response = JSON.parse(args.result.response);
			} catch (e) {
				var response = '';
			}
	
			var status = args.result.status;
			var file = args.file;
			var uploader = args.up;
			
			if( status == 200 ) {
			
				if ( response.success ) {
				
					var returnbox = response.returnbox;

					returnbox = returnbox.replace(/&lt;/g, "<");
					returnbox = returnbox.replace(/&gt;/g, ">");
					returnbox = returnbox.replace(/&amp;/g, "&");

					if( $( '#imagesallowmore' ).length ) {
						
						if ( $('<div>' + returnbox + '</div>').find( ".file-preview-card" ).data('type') == "image" ) {
						
							var allow_more = parseInt( $('#imagesallowmore').text() );
							var images_uploaded = parseInt( $('#imagesuploaded').text() );
							
							allow_more --;
							images_uploaded ++;
							
							if( allow_more < 0 ) allow_more = 0;

							max_images_allowed = allow_more;

							$('#imagesallowmore').text(allow_more);
							$('#imagesuploaded').text(images_uploaded);
						
						}
					}
					
					if( $( '#filesallowmore' ).length ) {
						
						if ( $('<div>' + returnbox + '</div>').find( ".file-preview-card" ).data('type') == "file" ) {
						
							var allow_more = parseInt( $('#filesallowmore').text() );
							var files_uploaded = parseInt( $('#filesuploaded').text() );
							
							allow_more --;
							files_uploaded ++;
							
							if( allow_more < 0 ) allow_more = 0;

							max_files_allowed = allow_more;
							
							$('#filesallowmore').text(allow_more);
							$('#filesuploaded').text(files_uploaded);
						
						}
					}
					
					if( response.remote_error ) {

						$('#file-uploader').plupload('notify', 'info', "{$lang['media_upload_st6']} <b>" + file.name + "</b> {$lang['media_upload_st9']} <br><span style=\"color:red;\">{$lang['remote_error']}<br>" + response.remote_error + "</span><br>{$lang['remote_error_1']}" );
					
					}
					
					if( response.tinypng_error ) {

						$('#file-uploader').plupload('notify', 'info', "{$lang['media_upload_st6']} <b>" + file.name + "</b> {$lang['media_upload_st9']} <br><span style=\"color:red;\">{$lang['tinyapi_error']}<br>" + response.tinypng_error + "</span>" );
					
					}

					$('#cont1').append( returnbox );
					
					setTimeout(function() {
						$('#' + file.id).fadeOut("slow");
					}, 500);
					
					totaluploaded ++;

				} else if( response.error ){
				
					$('#file-uploader').plupload('notify', 'error', "{$lang['media_upload_st6']} <b>" + file.name + "</b> {$lang['media_upload_st10']} <br><span style=\"color:red;\">" + response.error + "</span>" );
					
				} else {
				
					args.result.response = args.result.response.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
						 
					$('#file-uploader').plupload('notify', 'error', "{$lang['media_upload_st6']} <b>" + file.name + "</b> {$lang['media_upload_st10']} <br><span style=\"color:red;\">" + args.result.response + "</span>" );

				}
				
			} else {
			
				$('#file-uploader').plupload('notify', 'error', "{$lang['media_upload_st6']} <b>" + file.name + "</b> {$lang['media_upload_st10']} <br><span style=\"color:red;\">HTTP: " + status + "</span>" );
				
			}
		
		},
		
		complete: function(event, args) {

					$('.plupload_container').removeClass('plupload_files_selected');
					$('#file-uploader').plupload('refresh');
					$('#file-uploader').plupload('clearQueue');

					if (totaluploaded ) {
					
						if (typeof $.fn.tooltip === "function") {
						
							$('[rel=tooltip]').tooltip({
							  container: 'body'
							});
						
						}
					
						tabClick(0);
						
						totaluploaded = 0;
					}

		},
		
		error: function(event, args) {

			if( args.error.response ) {
				try {
				   var response = JSON.parse(args.error.response);
				} catch (e) {
					var response = '';
				}
				
				if( response.error ){
				
					$('#file-uploader').plupload('notify', 'error', "{$lang['media_upload_st6']} <b>" + args.error.file.name + "</b> {$lang['media_upload_st10']} <br><span style=\"color:red;\">" + response.error + "</span>" );
					
				}

			}

		}
		
	});
	
}

function check_all( obj ) {

	if(obj && obj.checked) {
	
		$('.file-can-all-selected .file-preview-card').addClass("active");
		
	} else {
	
		$('.file-preview-card').removeClass("active");
		$("#check_all").prop('checked', false);
		
		if (typeof $.fn.uniform === "function") {	
			$.uniform.update();
		}
	}
	
	insert_props_panel();
	return false;
}

function insert_props_panel() {

	if( $('.file-preview-card.active').length ) {
	
		var backup_state = $('.mediaupload-insert-params').outerHeight();
		
		$('#mediaupload-insert').show();
		$('#mediaupload-delete').show();
		
		var show = false;
		$('.mediaupload-image-title').hide();
		$('.mediaupload-thumbs-params').hide();
		$('#mediaupload-thumb').hide();
		$('#mediaupload-medium').hide();
		$('#mediaupload-original').hide();
		$('#mediaupload-enlarge').hide();
		$('.mediaupload-file-params').hide();

		$('.file-preview-card.active').each(function(){
		
			if($(this).data('type') == 'image'){
				show = true;
				$('.mediaupload-image-title').show();
				
				if( $(this).data('thumb') == 'yes' || $(this).data('medium') == 'yes' ) {
					$('.mediaupload-thumbs-params').show();
					$('#mediaupload-original').show();
					$('#mediaupload-enlarge').show();
				}

				if( $(this).data('thumb') == 'yes' ) {
					$('#mediaupload-thumb').show();
					$('#thumbimg').prop('checked', true);
				}
				
				if( $(this).data('medium') == 'yes' ) {
					$('#mediaupload-medium').show();
					if( !$('#thumbimg').prop('checked') || ($(this).data('thumb') != 'yes' && !$('#mediaupload-thumb').is(':visible')) ) {
						$('#thumbimg1').prop('checked', true);
					}
				}
				
				if (typeof $.fn.uniform === "function") {	
					$.uniform.update();
				}

			
			} else {

				if ( $(this).data('play') == "video" || $(this).data('play') == "audio" ) {
					show = true;
					$('.mediaupload-file-params').show();
					
					if (typeof $.fn.uniform === "function") {	
						$.uniform.update();
					}
				}
				
			}
			
			
		});
			
		if( $('.mediaupload-insert-params').is(':visible') ) {
			var current_state = $('.mediaupload-insert-params').outerHeight();
			
			if(current_state != backup_state) {
				current_state = current_state - backup_state;
				$('.mediaupload-body').height( $('.mediaupload-body').height() - current_state );
			}
			
		} else {
			if( show ) {
				$('.mediaupload-insert-params').show();
				$('.mediaupload-body').height( $('.mediaupload-body').height() - $('.mediaupload-insert-params').outerHeight() );				
			}
		}
		
		
	} else {
		
		$('#mediaupload-insert').hide();
		$('#mediaupload-delete').hide();
		
		if( $('.mediaupload-insert-params').is(':visible') ) {		
				$('.mediaupload-body').height( $('.mediaupload-body').height() + $('.mediaupload-insert-params').outerHeight() );
				$('.mediaupload-insert-params').hide();
		}
		
	}

	return false;
}

function tabClick(n) {

	if (n == 0) {
		$("#cont2").hide();
		$("#stmode").hide();
		$("#linkbox").hide();
		$("#cont1").fadeTo('slow', 1);
		$("#link2").addClass("current");
		$("#link1").removeClass("current");
		$("#link3").removeClass("current");
		$("#check-all-box").show();

	}

	if (n == 1) {
		$("#stmode").hide();
		$("#cont1").hide();
		$("#linkbox").hide();
		$("#cont2").fadeTo('slow', 1);
		$("#link3").addClass("current");
		$("#link1").removeClass("current");
		$("#link2").removeClass("current");
		$("#check-all-box").hide();
	}

	if (n == 2) {
		$("#cont2").hide();
		$("#cont1").hide();
		$("#linkbox").hide();
		$("#stmode").fadeTo('slow', 1);
		$("#link1").addClass("current");
		$("#link2").removeClass("current");
		$("#link3").removeClass("current");
		$("#check-all-box").hide();
	}

};


function media_insert_selected() {

    var frm = document.delimages;
	var allways_bbimages = '{$config['bbimages_in_wysiwyg']}';
	var links = new Array();
	var align = $('#imagealign').val();
	var content = '';
	var t = 0;
	var url = ''
	var hidpi_name = ''
	var have_images = false;

	if( $('.file-preview-card.active').length ) {
	
		$('.file-preview-card.active').each(function() {
		
			if($(this).data('type') == 'image'){
			
				have_images = true;
				url = $(this).data('url');
				
				if( $(this).data('hidpi') ) {
					hidpi_name = $(this).data('hidpi');
				}

				if ( !$('#insertoriginal').prop('checked') ) {
					
					if( $('#thumbimg').prop('checked') || $('#thumbimg1').prop('checked') ) {
			
						if( $('#thumbimg').prop('checked') ) {
							var folder="thumbs";
						} else {
							var folder="medium";
						}
			
						url = url.split('/');
						var filename = url.pop();
						url.push(folder);
						url.push(filename);
						url = url.join('/');
					
					}
			
					links[t] = buildimage (url, hidpi_name);
			
				} else {
			
					if ( $(this).data('thumb') == "yes" || $(this).data('medium') == "yes" ) {
					
						if( $('#thumbimg').prop('checked') ) {
						
							links[t] = buildthumb (url, 'thumb', hidpi_name);
							
						} else if( $('#thumbimg1').prop('checked') ) {
						
							links[t] = buildthumb (url, 'medium', hidpi_name);
							
						} else {
						
							links[t] = buildimage ( url, hidpi_name );
	
						}
					} else {
					
						links[t] = buildimage ( url, hidpi_name );
						
					}
			
				}	
			

				
			} else {

				if ( ($(this).data('play') == "video" || $(this).data('play') == "audio") && $('#attachforplayer').prop('checked') ) {
					links[t] = '['+$(this).data('play')+'='+$(this).data('url')+']';
				} else {
					if( $(this).data('public') == "1" ) {
						links[t] = '<a href="'+$(this).data('url')+'">'+$(this).data('url')+'</a>';
					} else {
						links[t] = '[attachment='+$(this).data('path')+']';
					}
				}
			}
			
			t++;
		});
		
	}
	
	if( $('.file-preview-card.active').length > 1 ) {
	
		if( !have_images ) {
		
			content = links.join(' ');
			
		} else if (align == 'center') {
		    var lastElement = links[links.length - 1];
			var lastCaret = '';
			if (lastElement.indexOf('<img') > -1 ) {
				lastCaret = '<br>';
			}

			if(allways_bbimages == '1') {
				content = links.join('</p><p style="text-align: center;">');
				content = '<p style="text-align: center;">'+ content +'</p>';
			} else {
				content = links.join('</p><p>');
				content = '<p>'+ content +'</p>' + lastCaret;
			}
			
		} else {
			content = links.join(' ');
		}
		
	} else { content = links.join(''); }

	insertcontent( content );

};


function buildthumb( image, tag, hidpi_name ) {

	var align = $('#imagealign').val();
	var imagealt = $('#imagetitle').val();
	var content = '';
	var url = '';
	var hidpi_url = '';
	var allways_bbimages = '{$config['bbimages_in_wysiwyg']}';

	if( allways_bbimages != '1') {
	
		if( tag == 'thumb' ) {
			var folder="thumbs";
		} else {
			var folder="medium";
		}

		if(hidpi_name) {

			url = image.split('/');
			url.pop();
			url.push(hidpi_name);
			url = url.join('/');

			hidpi_url = ' data-srcset="' + url + ' 2x" ';

		} else {
			hidpi_url = '';
		}

		url = image.split('/');
		var filename = url.pop();
		url.push(folder);
		url.push(filename);
		url = url.join('/');

		content = '<a href="'+image+'" class="highslide" target="_blank"'+ hidpi_url +'>';
		content += buildimage( url, hidpi_name );
		content += '</a>';
		
	} else {
	
		var imgoption = "";
	
		if (imagealt != "") { 
	
			imgoption = "|"+imagealt;
	
		}
	
		if (align != "none" && align != "center") { 

			imgoption = align+imgoption;

		}
	
		if (imgoption != "" ) {
	
			imgoption = "="+imgoption;

		}
	
		content = '['+tag+''+imgoption+']'+ image +'[/'+tag+']';
	
	}


	return content;
};

function buildimage( image, hidpi_name ) {

	var content = '';
	var url = '';
	var align = $('#imagealign').val();
	var imagealt = $('#imagetitle').val();
	var allways_bbimages = '{$config['bbimages_in_wysiwyg']}';
	
	imagealt = escapeHtml(imagealt);

	if(hidpi_name) {

		url = image.split('/');
		url.pop();
		url.push(hidpi_name);
		url = url.join('/');

		hidpi_name = 'srcset="' + url + ' 2x" ';

	} else {
		hidpi_name = '';
	}
	
	if (allways_bbimages != '1') {
		
		if (align == 'center' || align == 'none') {
		
			if(align == 'center') {
				img_opt = " style=\"display: block; margin-left: auto; margin-right: auto;\"";
			} else {
				img_opt = "";
			}
			
			content = '<img '+ hidpi_name +'src="'+ image +'" alt="'+ imagealt +'"'+ img_opt +'>';
			
		} else {
		
			content = '<img '+ hidpi_name +'src="'+ image +'" style="float:' + align+ ';" alt="'+ imagealt +'">';
			
		}

	} else {

		var imgoption = "";
		var imagealt = $('#imagetitle').val();

		if (imagealt != "") { 

			imgoption = "|"+imagealt;

		}

		if (align != "none" && align != "center") { 

			imgoption = align+imgoption;

		}

		if (imgoption != "" ) {

			imgoption = "="+imgoption;

		}

		content = '[img'+imgoption+']'+ image +'[/img]';

	}

	return content;
};

function insertcontent( content ) {

	var allways_bbimages = '{$config['bbimages_in_wysiwyg']}';
	var editor = tinymce.activeEditor;
	var dom = editor.dom;
	var node = editor.selection.getNode();
	var newline = '<br>';
	var hasText = node.innerText.trim();

	if(content.indexOf('<p>') > -1 || allways_bbimages == '1' || hasText.length ) {
		newline = '';
	}

	editor.insertContent( content + newline );
	
	if (content.indexOf('[video=') > -1 || content.indexOf('[audio=') > -1) {
		
		var node = editor.selection.getNode();

		if (node.nodeName == 'P') {
			
			var stylenode = dom.getAttrib(node, 'style');
			var classnode = dom.getAttrib(node, 'class');

			if (stylenode) {
				stylenode = ' style="' + stylenode + '"';
			}

			if (classnode) {
				classnode = ' class="' + classnode + '"';
			}

			var newnode = '<div' + stylenode + classnode + '>' + editor.selection.select(node).innerHTML + '</div>';

			editor.selection.select(node);
			editor.insertContent(newnode);

		}

	}

	$('#mediaupload').dialog('close');
	
	return false;
};

function escapeHtml( string ) {

	var entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
		'=': '&#x3D;',
		'?': '&#x3F'
	};
	
	return String(string).replace(/[&<>"'`=\/\?]/g, function (match) {
		return entityMap[match];
	});
	
}

function upload_from_url( url ) {

	var t_size = $('#t_size').val();
	var upload_driver = $('#upload_driver').val();
	var t_seite = $('#t_seite').val();
	var m_size = $('#m_size').val();
	var m_seite = $('#m_seite').val();
	var make_thumb = $("#make_thumb").is(":checked") ? 1 : 0;
	var make_medium = $("#make_medium").is(":checked") ? 1 : 0;
	var make_watermark = $("#make_watermark").is(":checked") ? 1 : 0;
	var public_file = $("#public_file").is(":checked") ? 1 : 0;
	var hidpi = $("#hidpi").is(":checked") ? 1 : 0;

	if (url == 'url' ) {

		var copyurl = $('#copyurl').val();
		var ftpurl = '';
		var error_id = 'upload-viaurl-status';		
	} else {

		var ftpurl = $('#ftpurl').val();
		var copyurl = '';
		var error_id = 'upload-viaftp-status';
	}

	$('#'+error_id).html( '<span style="color:green;">{$lang['ajax_info']}</span>' );

	$.post( "{$root}engine/ajax/controller.php?mod=upload", { news_id: "{$news_id}", imageurl: copyurl, ftpurl: ftpurl, t_size: t_size, upload_driver: upload_driver, hidpi: hidpi, t_seite: t_seite, make_thumb: make_thumb, m_size: m_size, m_seite: m_seite, make_medium: make_medium, make_watermark: make_watermark, public_file: public_file, area: "{$area}", author: "{$author}", subaction: "upload", user_hash : "{$dle_login_hash}" }, function(data){

		if ( data.success ) {

			var returnbox = data.returnbox;

			returnbox = returnbox.replace(/&lt;/g, "<");
			returnbox = returnbox.replace(/&gt;/g, ">");
			returnbox = returnbox.replace(/&amp;/g, "&");

			$('#cont1').append( returnbox );

			$('#'+error_id).html('');

			if (url == 'url' ) {
				$('#copyurl').val('');
			} else {
				$('#ftpurl').val('');
			}

			tabClick(0);

		} else {

			if( data.error ) $('#'+error_id).html( '<span style="color:red;">' + data.error + '</span>' );

		}

	}, "json");
	return false;

};

function media_delete_file( file ) {

	DLEconfirmDelete( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
	
		var formData = new FormData();
		formData.append('subaction', 'deluploads');
		formData.append('user_hash', '{$dle_login_hash}');
		formData.append('area', '{$area}');
		formData.append('news_id', '{$news_id}');
		formData.append('author', '{$author}');
		formData.append( file.data('area')+'[]', file.data('deleteid') );

		if( $( '#imagesallowmore' ).length ) {
			
			if ( file.data('area') == "images" ) {
			
				var allow_more = parseInt( $('#imagesallowmore').text() );
				var images_uploaded = parseInt( $('#imagesuploaded').text() );
				
				allow_more ++;
				images_uploaded --;
				
				if( allow_more < 0 ) allow_more = 0;

				max_images_allowed = allow_more;
				
				$('#imagesallowmore').text(allow_more);
				$('#imagesuploaded').text(images_uploaded);
			
			}
		}
		
		if( $( '#filesallowmore' ).length ) {
			
			if ( file.data('area') == "files" ) {
			
				var allow_more = parseInt( $('#filesallowmore').text() );
				var files_uploaded = parseInt( $('#filesuploaded').text() );
				
				allow_more ++;
				files_uploaded --;
				
				if( allow_more < 0 ) allow_more = 0;

				max_files_allowed = allow_more;
				
				$('#filesallowmore').text(allow_more);
				$('#filesuploaded').text(files_uploaded);
			
			}
		}

		ShowLoading('');
	
		$.ajax({
			url: "{$root}engine/ajax/controller.php?mod=upload",
			data: formData,
			processData: false,
			contentType: false,
			type: 'POST',
			dataType: 'json',
			success: function(data) {
				HideLoading('');
			
				if (data.status) {
	
					file.fadeOut("slow", function() {
						file.remove();
					});
	
				} else {

					DLEPush.error('{$lang['files_del_error']}');
	
				}

			}
		});
		
		return false;
		
	} );
	
	return false;
};


function media_delete_selected() {

	if( $('.file-preview-card.active').length ) {
	
		DLEconfirmDelete( '{$lang['delete_selected']}', '{$lang['p_info']}', function () {
		
			var allow_del = true;
			var formData = new FormData();
			formData.append('subaction', 'deluploads');
			formData.append('user_hash', '{$dle_login_hash}');
			formData.append('area', '{$area}');
			formData.append('news_id', '{$news_id}');
			formData.append('author', '{$author}');
			
			
			
			$('.file-preview-card.active').each(function(){
			
				if( $(this).data('area') == 'shared' ) {
				
					allow_del = false;
					check_all();
					return false;
					
				} else if( $(this).data('deleteid') ) {
				
					formData.append( $(this).data('area')+'[]', $(this).data('deleteid') );
					
					if( $( '#imagesallowmore' ).length ) {
						
						if ( $(this).data('area') == "images" ) {
						
							var allow_more = parseInt( $('#imagesallowmore').text() );
							var images_uploaded = parseInt( $('#imagesuploaded').text() );
							
							allow_more ++;
							images_uploaded --;
							
							if( allow_more < 0 ) allow_more = 0;

							max_images_allowed = allow_more;
							
							$('#imagesallowmore').text(allow_more);
							$('#imagesuploaded').text(images_uploaded);
						
						}
					}
					
					if( $( '#filesallowmore' ).length ) {
						
						if ( $(this).data('area') == "files" ) {
						
							var allow_more = parseInt( $('#filesallowmore').text() );
							var files_uploaded = parseInt( $('#filesuploaded').text() );
							
							allow_more ++;
							files_uploaded --;
							
							if( allow_more < 0 ) allow_more = 0;

							max_files_allowed = allow_more;
							
							$('#filesallowmore').text(allow_more);
							$('#filesuploaded').text(files_uploaded);
						
						}
					}
					
		
				}
			
			});
		
			if(!allow_del) {
				return false;
			}
			
			ShowLoading('');
		
			$.ajax({
				url: "{$root}engine/ajax/controller.php?mod=upload",
				data: formData,
				processData: false,
				contentType: false,
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					HideLoading('');
				
					if (data.status) {
		
						$('.file-preview-card.active').fadeOut("slow", function() {
							$('.file-preview-card.active').remove();
							check_all();
						});
		
					} else {
	
						DLEPush.error('{$lang['files_del_error']}');
		
					}
	
				}
			});
			
			return false;
	
	
	
	
		} );
	
	}	
	return false;
};
function get_shared_list( userdir ) {

	if( !$('#link3').length ){
		return false;
	}

	$.get("{$root}engine/ajax/controller.php?mod=adminfunction", { action: 'viewshared', userdir: userdir, user_hash: '{$dle_login_hash}' }, function(data){

		if (data.success) {
		
			$('#cont2').html(data.response);

		} else {
		
			$('#cont2').html('<div class="mediaupload-file-box mediaupload-file-error" style="margin:10px;">' + data.error + '</div>');
			
		}

	}, "json").fail(function(jqXHR, textStatus, errorThrown ) {

			var error_status = '';
		
			if (jqXHR.status < 200 || jqXHR.status >= 300) {
			  error_status = 'HTTP Error: ' + jqXHR.status;
			} else {
				error_status = 'Invalid JSON: ' + jqXHR.responseText;
			}
	
			$('#cont2').html('<div class="mediaupload-file-box mediaupload-file-error" style="margin:10px;">' + error_status + '</div>');
		
	});
	
	return false;
	
};
		
</script>
HTML;

?>