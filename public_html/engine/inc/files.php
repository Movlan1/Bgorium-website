<?PHP
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
 Files: files.php
-----------------------------------------------------
 Use: manage uploaded pictures
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$path = '';
$userdir = '';
$files_per_page = 30;
$start_from = isset( $_REQUEST['start_from'] ) ? intval( $_REQUEST['start_from'] ) : 0;
$config['file_chunk_size'] =  number_format(floatval($config['file_chunk_size']), 1, '.', '');
if ($config['file_chunk_size'] < 1) $config['file_chunk_size'] = '1.5';

if( $start_from < 0 ) $start_from = 0;

if( isset($_GET['userdir']) AND $_GET['userdir'] ) {
	
	$path = $userdir = cleanpath( $_GET['userdir'] );
	
}

DLEFiles::init();

$storages_list = DLEFiles::getStorages();
$storages_list['0'] = $lang['opt_sys_imfs_1'];
ksort($storages_list);

if( isset($_REQUEST['location']) ) {
	
	$location = $disk = intval($_REQUEST['location']);
	$url_location = "&location=". $location;
	$upload_driver = "\"upload_driver\" : \"{$disk}\",";

} else { $disk = DLEFiles::$driver;  $location = $url_location = $upload_driver = ''; }


$max_file_size = (int)$config['max_up_size'] * 1024;
$allowed_extensions = array ("gif", "jpg", "png", "jpeg", "webp" , "bmp", "avif");
$simple_ext = implode( ",", $allowed_extensions );


if ( $path == "files" ) {
	msg( "error", $lang['addnews_denied'], $lang['index_denied'] );
}

if( $action == "createfolder" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( isset( $_REQUEST['folder'] ) AND (string)$_REQUEST['folder'] ) {
		
		$folder = cleanpath( $_REQUEST['folder'] );
		
		DLEFiles::CreateDirectory( $userdir . "/". $folder, $disk );
	
	}
	
	header( "Location: ?mod=files&userdir={$userdir}{$url_location}" );
	die();
	
}

if( $action == "doimagedelete" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( !isset( $_POST['images'] ) AND !isset($_POST['folders']) ) {
		msg( "error", $lang['images_delerr'], $lang['images_delerr_1'], 'javascript:history.go(-1)' );
	}

	if( isset( $_POST['images'] ) AND is_array($_POST['images']) AND count($_POST['images']) ) {
		
		foreach ( $_POST['images'] as $image ) {
	
			$image = totranslit($image);
	
			if( $image ) {
				
				if( stripos ( $image, ".htaccess" ) !== false ) die("Hacking attempt!");
		
				$img_name_arr = explode( ".", $image );
				$type = totranslit( end( $img_name_arr ) );
		
				if( !in_array( $type, $allowed_extensions ) ) die("Hacking attempt!");
	
				DLEFiles::Delete( $userdir . "/". $image, $disk );
				DLEFiles::Delete( $userdir . "/thumbs/". $image, $disk );
				DLEFiles::Delete( $userdir . "/medium/". $image, $disk );
		
				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '37', '{$image}')" );
		
			}
		}
	
	}
	
	if( isset( $_POST['folders'] ) AND is_array($_POST['folders']) AND count($_POST['folders']) ) {
		
		
		foreach ( $_POST['folders'] as $folder ) {
			
			$folder = cleanpath( $folder );
			
			if( !$userdir ){
				
				$not_allowed = array("files", "posts", "fotos", "shared", "icons");
				
				if( in_array( $folder, $not_allowed ) ) {
					msg( "error", $lang['images_delerr'], $lang['images_delerr_2'], 'javascript:history.go(-1)' );
				}
			
			}
		
			DLEFiles::DeleteDirectory( $userdir . "/". $folder, $disk );
			
		}
	
	}
	
}

	$js_array[] = "engine/classes/uploads/html5/plupload/plupload.full.min.js";
	$js_array[] = "engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js";
	$js_array[] = "engine/classes/fancybox/fancybox.js";

	$files = DLEFiles::ListDirectory( $path, $allowed_extensions, $disk );
	
	if( DLEFiles::$error ) {
		msg( "error", $lang['addnews_denied'], DLEFiles::$error );
	}

	echoheader( "<i class=\"fa fa-file-image-o position-left\"></i><span class=\"text-semibold\">{$lang['header_f_1']}</span>", $lang['header_f_2'] );
	
	$folder_list = array();

	if( $userdir ) {
		$prev_link = explode("/", $userdir);
		array_pop($prev_link);
		$prev_link = implode("/", $prev_link);
		
		$folder_list[] = <<<HTML
<div class="folder-preview-card">
	<div class="file-content" onclick="document.location='?mod=files&userdir={$prev_link}{$url_location}'; return false;" return false;">
		<i class="fa fa-arrow-circle-o-left text-slate-600\"></i>
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info">{$lang['files_prev']}</div>
		</div>
	</div>
</div>
HTML;

	}
	
	foreach ( $files['dirs'] as $entryname) {
		
		$folder = $entryname['name'];
		
		if( $userdir ) $link = $userdir."/".$folder; else $link = $folder;
		
		if($link == "files") continue;
		if($link == "posts") $folder = $lang['images_news'];
		if($link == "fotos") $folder = $lang['images_foto'];
		if($link == "shared") $folder = $lang['images_shared'];
		if($link == "icons") $folder = $lang['images_icons'];
		
		$not_allowed = array("files", "posts", "fotos", "shared", "icons");
		
		if( !in_array( $link, $not_allowed ) ) {
			$del_label ="<label><input type=\"checkbox\" class=\"icheck\" name=\"folders[]\" value=\"{$entryname['name']}\" ></label>";
		} else $del_label = '';
				
		$folder_list[] = <<<HTML
<div class="folder-preview-card">
	<div class="file-content">
		<img src="engine/skins/images/folder.png" class="file-preview-image" onclick="document.location='?mod=files&userdir={$link}{$url_location}'; return false;">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info">{$folder}</div>
		</div>
	</div>
	{$del_label}
</div>
HTML;

	}

	$total_size = 0;
	$all_count_files = count($files['files']);
	$i = $start_from + $files_per_page;
	$files['files'] = array_slice($files['files'], $start_from, $files_per_page);
	
	// pagination

	$npp_nav = "";
	
	if( $all_count_files > $files_per_page ) {

		if( $start_from > 0 ) {
			$previous = $start_from - $files_per_page;
			$npp_nav .= "<li><a onclick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a></li>";
		}
		
		$enpages_count = @ceil( $all_count_files / $files_per_page );
		$enpages_start_from = 0;
		$enpages = "";
		
		if( $enpages_count <= 10 ) {
			
			for($j = 1; $j <= $enpages_count; $j ++) {
				
				if( $enpages_start_from != $start_from ) {
					
					$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";
				
				} else {
					
					$enpages .= "<li class=\"active\"><span>$j</span></li>";
				}
				
				$enpages_start_from += $files_per_page;
			}
			
			$npp_nav .= $enpages;
		
		} else {
			
			$start = 1;
			$end = 10;
			
			if( $start_from > 0 ) {
				
				if( ($start_from / $files_per_page) > 4 ) {
					
					$start = @ceil( $start_from / $files_per_page ) - 3;
					$end = $start + 9;
					
					if( $end > $enpages_count ) {
						$start = $enpages_count - 10;
						$end = $enpages_count - 1;
					}
					
					$enpages_start_from = ($start - 1) * $files_per_page;
				
				}
			
			}
			
			if( $start > 2 ) {
				
				$enpages .= "<li><a onclick=\"javascript:search_submit(0); return(false);\" href=\"#\">1</a></li> <li><span>...</span></li>";
			
			}
			
			for($j = $start; $j <= $end; $j ++) {
				
				if( $enpages_start_from != $start_from ) {
					
					$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";
				
				} else {
					
					$enpages .= "<li class=\"active\"><span>$j</span></li>";
				}
				
				$enpages_start_from += $files_per_page;
			}
			
			$enpages_start_from = ($enpages_count - 1) * $files_per_page;
			$enpages .= "<li><span>...</span></li><li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a></li>";
			
			$npp_nav .= $enpages;
		
		}
		
		if( $all_count_files > $i ) {
			$how_next = $all_count_files - $i;
			if( $how_next > $files_per_page ) {
				$how_next = $files_per_page;
			}
			$npp_nav .= "<li><a onclick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\">&gt;&gt;</a></li>";
		}
		
		$npp_nav = "<div class=\"mt-20 mb-20\"><ul class=\"pagination pagination-sm\">".$npp_nav."</ul></div>";

	}

// pagination

	$http_url = DLEFiles::GetBaseURL($disk);

	foreach ( $files['files'] as $entryname ) {
		
		$file = $entryname['name'];
		
		$total_size += $entryname['size'];
			
		if($userdir) {
			$img_url = $http_url . $userdir . "/" . $entryname['name'];	
		} else {
			$img_url = $http_url . $entryname['name'];
		}
		
		$size = formatsize( $entryname['size'] );
		
		$base_name = pathinfo($entryname['name'], PATHINFO_FILENAME);
		$file_type = explode(".", $entryname['name']);
		$file_type = totranslit(end($file_type));

		$folder_list[] = <<<HTML
<div class="file-preview-card">
	<div class="file-content">
		<div class="file-ext">{$file_type}</div>
		<img src="{$img_url}" class="file-preview-image" data-highslide="files" target="_blank">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info">{$base_name}</div>
			<div class="file-size-info">({$size})</div>
		</div>
	</div>
	<label><input type="checkbox" class="icheck" name="images[]" value="{$entryname['name']}" ></label>
</div>
HTML;
	
	}
	
	$folder_list = implode('', $folder_list);
	
	if( $total_size ) {
		$total_size = formatsize( $total_size );
		$total_size = "<div class=\"mt-20\"><span class=\"position-left\">{$lang['images_size']}</span>{$total_size}</div>";
	} else $total_size = "";


$storages_select = "<select class=\"uniform\" name=\"location\" onchange=\"changeLocation(this.value)\">\r\n";

foreach ($storages_list as $value => $description) {

	$storages_select .= "<option value=\"{$value}\"";

	if ($disk == $value) {
		$storages_select .= " selected ";
	}

	$storages_select .= ">{$description}</option>\n";

}

$storages_select .= "</select>";
	
$storage_list = <<<HTML
	<div class="heading-elements">
		<div class="form-group has-feedback">
			<span class="position-left">{$lang['select_storage']}</span>
			{$storages_select}
		</div>
	</div>
HTML;

	
	echo <<<HTML
<form action="?mod=files" method="get" name="navi" id="navi">
<input type="hidden" name="mod" value="files">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="userdir" id="userdir" value="{$userdir}">
<input type="hidden" name="location" id="location" value="{$location}">
</form>

<form action="" method="post" name="delimages" id="delimages">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['uploaded_file_list']}
	{$storage_list}
  </div>
	
  <div class="panel-body row-seamless">
	<div class="file-list">
	{$folder_list}
	</div>
	{$npp_nav}
	{$total_size}
  </div>
HTML;


	echo "<div class=\"panel-footer\">
		<div id=\"file-uploader\" style=\"float:left;\"></div>
		<button onclick=\"createfolder(); return false;\" class=\"btn bg-slate-600 btn-sm btn-raised\"><i class=\"fa fa-folder-o position-left\"></i>{$lang['btn_folder']}</button>
		<div style=\"float:right;\"><button class=\"btn bg-danger btn-sm btn-raised\" onclick=\"delete_file(); return false;\">{$lang['images_del']}</button><input type=\"hidden\" name=\"action\" value=\"doimagedelete\"><input type=\"hidden\" name=\"user_hash\" value=\"$dle_login_hash\" /></div>
	</div>";

	$max_file_size = number_format($max_file_size, 0, '', '');

	echo <<<HTML
   </div>
</form>
<script>
function changeLocation(value){

	document.location='?mod=files&location='+value;

}

function search_submit(prm){
  document.navi.start_from.value=prm;
  document.navi.submit();
  return false;
}
	
jQuery(function($){


	$('#file-uploader').html('<div class="qq-uploader"><div id="uploadedfile" class="qq-upload-button btn bg-teal btn-sm btn-raised position-left" style="width: auto;">{$lang['media_upload_st14']}</div></div>');
	
	var uploader = new plupload.Uploader({
	
		runtimes : 'html5',
		file_data_name: "qqfile",
		browse_button: 'uploadedfile',
		container: document.getElementById('file-uploader'),
		url: "engine/ajax/controller.php?mod=upload",
		multipart_params: {"subaction" : "upload", "news_id" : "0", "area" : "adminupload", "userdir" : "{$userdir}",{$upload_driver} "user_hash" : "{$dle_login_hash}"},
		multi_selection: true,
		chunk_size: '{$config['file_chunk_size']}mb',
		
		filters : {
			max_file_size : '{$max_file_size}',
			mime_types: [
				{title : "Files", extensions : "{$simple_ext}"}
			]
		},
		 
	 
		init: {
	 
			FilesAdded: function(up, files) {
			
				plupload.each(files, function(file) {
					$('<div id="uploadfile-'+file.id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+file.name+'</span>&nbsp;<span class="qq-status" ><span class="qq-upload-spinner"></span> <span class="qq-upload-size"></span></span><div class="progress"><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('.panel-body.row-seamless');
				});
				
				up.start();
			},
	 
			UploadProgress: function(up, file) {
			
				  $('#uploadfile-'+file.id+' .qq-upload-size').text(plupload.formatSize(file.loaded) + ' {$lang['media_upload_st8']} ' + plupload.formatSize(file.origSize));
				  $('#uploadfile-'+file.id+' .progress-bar').css( "width", file.percent + '%' );
				  $('#uploadfile-'+file.id+' .qq-upload-spinner').css( "display", "inline-block");
	
			},
			
			FileUploaded: function(up, file, result) {
			
					try {
					   var response = JSON.parse(result.response);
					} catch (e) {
						var response = '';
					}
					
					if( result.status == 200 ) {
					
						if ( response.success ) {

							$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st9']}');  

							setTimeout(function() {
								$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
							}, 1000);
	
						} else {
						
							$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st10']}');
	
							if( response.error ) $('#uploadfile-'+file.id+' .qq-status').append( '<br><span class="text-danger">' + response.error + '</span>' );
	
							setTimeout(function() {
								$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
							}, 10000);
						}
							
					} else {
					
						$('#uploadfile-'+file.id+' .qq-status').append( '<br><span class="text-danger">HTTP Error:' + result.status + '</span>' );
						
						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
						}, 10000);
					}
					
			},
			
			UploadComplete: function(up, files) {
				setTimeout("location.replace(window.location)", 1000);
			},
			
			Error: function(up, err) {
				var type_err = '{$lang['media_upload_st11']}';
				var size_err = '{$lang['media_upload_st12']}';
				
				type_err = type_err.replace('{file}', err.file.name);
				type_err = type_err.replace('{extensions}', up.settings.filters.mime_types[0].extensions);
				size_err = size_err.replace('{file}', err.file.name);
				size_err = size_err.replace('{sizeLimit}', plupload.formatSize(up.settings.filters.max_file_size));
				
				if(err.code == '-600') {
					DLEPush.error(size_err);
					
				} else if(err.code == '-601') {
					DLEPush.error(type_err);
					
				} else {
					DLEPush.error(err.message);
					
				}
			
			}
		}
	});
	
	uploader.init();

});

function delete_file() {
	DLEconfirmDelete( '{$lang['delete_selected']}', '{$lang['p_info']}', function () {
		document.delimages.submit();
	} );
};

function createfolder( ){

	DLEprompt("{$lang['folder_enter']}", '', "{$lang['p_prompt']}", function (folder) {

		document.location='?mod=files&user_hash={$dle_login_hash}&userdir={$userdir}{$url_location}&action=createfolder&folder='+folder;

	}, false, '{$lang['btn_folder']}');

};

</script>
HTML;

	echofooter();

?>