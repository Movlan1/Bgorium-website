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
 File: editnews.php
-----------------------------------------------------
 Use: AJAX news edit
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$parse = new ParseFilter();

if( !$is_logged ) die( "error" );

$id =  isset($_REQUEST['id']) ? intval( $_REQUEST['id'] ) : 0;
$_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if( !$id ) die( "error" );

if( $_REQUEST['action'] == "edit" ) {

	$dark_theme = "";

	if (defined('TEMPLATE_DIR')) {
		$template_dir = TEMPLATE_DIR;
	} else $template_dir = ROOT_DIR . "/templates/" . $config['skin'];

	if (is_file($template_dir . "/info.json")) {

		$data = json_decode(trim(file_get_contents($template_dir . "/info.json")), true);

		if (isset($data['type']) and $data['type'] == "dark") {
			$dark_theme = " dle_theme_dark";
		}
	}

	$row = $db->super_query("SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.category, p.approve, p.allow_br, e.reason, e.edited_now FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE p.id = '$id'" );
	
	if( $id != $row['id'] ) die( "error" );
	
	$cat_list = explode( ',', $row['category'] );
	$categories_list = CategoryNewsSelection($cat_list, 0);

	$xfieldsaction = "categoryfilter";
	include_once(DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));


	if ($config['allow_multi_category']) {

		$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"edit_category_list\" onchange=\"onCategoryChange(this)\" style=\"width:100%;max-width:350px;height:140px;\" multiple=\"multiple\">";
	} else {

		$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"edit_category_list\" onchange=\"onCategoryChange(this)\" style=\"width:100%;max-width:350px;\">";
	}

	$cats .= $categories_list . "</select>";	

	$have_perm = 0;

	if( $user_group[$member_id['user_group']]['allow_edit'] AND $row['autor'] == $member_id['name'] ) {
		$have_perm = 1;
	}
	
	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$have_perm = 1;
		
		if ($member_id['cat_add']) $allow_list = explode( ',', $member_id['cat_add'] );
		else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
		
		foreach ( $cat_list as $selected ) {
			if( $allow_list[0] != "all" AND !in_array( $selected, $allow_list ) ) $have_perm = 0;
		}
	}
	
	if( $user_group[$member_id['user_group']]['max_edit_days'] ) {
		$newstime = strtotime( $row['date'] );
		$maxedittime = $_TIME - ($user_group[$member_id['user_group']]['max_edit_days'] * 3600 * 24);
		if( $maxedittime > $newstime ) $have_perm = 0;
	}
	
	if( ($member_id['user_group'] == 1) ) {
		$have_perm = 1;
	}

	
	if( !$have_perm ) die( $lang['editnews_error'] );

	$edit_alert = '';
	$save_edit_alert = '';

	if ($config['alert_edit_now']) {

		if (isset($row['edited_now']) and $row['edited_now']) $row['edited_now'] = json_decode($row['edited_now'], true);
		else $row['edited_now'] = array('name' => '', 'time' => '');

		if ($row['edited_now']['name'] and $row['edited_now']['name'] != $member_id['name']  and time() < $row['edited_now']['time'] + 60) {

			$lang['edit_news_alert'] = str_replace('{name}', $row['edited_now']['name'], $lang['edit_news_alert']);

			$edit_alert = <<<HTML
DLEPush.warning('{$lang['edit_news_alert']}', '', 20000);
HTML;
		} else {

			$db->query("UPDATE " . PREFIX . "_post_extras SET edited_now='" . $db->safesql(json_encode(array('name' => $member_id['name'], 'time' => time()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . "' WHERE news_id='{$row['id']}'");

			$save_edit_alert = <<<HTML
setTimeout(function() {
	save_edit_alert();
}, 20000);
HTML;
		}
	}

	$news_txt = $row['short_story'];
	$full_txt = $row['full_story'];
	$author = urlencode($row['autor']);

	$news_txt = $parse->decodeBBCodes($news_txt, true, true);
	$full_txt = $parse->decodeBBCodes($full_txt, true, true);

	if( $row['approve'] ) {
		$fix_approve = "checked";
	} else $fix_approve = "";
	
	$row['title'] = $parse->decodeBBCodes( $row['title'], false );

	$xfields = xfieldsload();
	$xfieldsdata = xfieldsdataload ($row['xfields']);
	$xfbuffer = "";
	
	$config['file_chunk_size'] =  number_format(floatval($config['file_chunk_size']), 1, '.', '');
	if ($config['file_chunk_size'] < 1) $config['file_chunk_size'] = '1.5';

	foreach ($xfields as $name => $value) {
		$fieldname = $value[0];
		$fieldcount = md5($fieldname);
		
		$holderid = "xfield_holder_".$fieldname;

		if ( isset($xfieldsdata[$value[0]]) OR $config['quick_edit_mode'] ) {
			
			if( isset($xfieldsdata[$value[0]]) ) $fieldvalue = $xfieldsdata[$value[0]];
			else $fieldvalue = '';

		} else continue;

		if( $value[19] ) {
			
			$value[19] = explode( ',', $value[19] );
			
			if( $value[19][0] AND !in_array( $member_id['user_group'], $value[19] ) ) {
				continue;
			}
			
		}
		
		$value[1] = htmlspecialchars($value[1], ENT_QUOTES, 'UTF-8' );
		 
		$fieldvalue = str_ireplace( "&#123;title", "{title", $fieldvalue );
		$fieldvalue = str_ireplace( "&#123;short-story", "{short-story", $fieldvalue );
		$fieldvalue = str_ireplace( "&#123;full-story", "{full-story", $fieldvalue );

		if ($value[8] OR $value[6] OR $value[3] == "image" OR $value[3] == "imagegalery" OR $value[3] == "file" OR $value[3] == "datetime" OR $value[3] == "select") {
			
			$fieldvalue = html_entity_decode(stripslashes($fieldvalue), ENT_QUOTES, 'UTF-8' );
			$fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES, 'UTF-8' );
			
		} elseif($value[3] == "htmljs") {
			
			 $fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES, 'UTF-8' );
			 
		} else {
			
			$fieldvalue = $parse->decodeBBCodes( $fieldvalue, true, true);

		}
		

		if ($value[3] == "textarea") {
			
			if ( $value[7] ) {
	
				$params = "class=\"wysiwygeditor\" ";
				$class_name = "wseditor dlefastedit-editor";
				$panel="";
				
			} else {
				$params = "class=\"quick-edit-textarea\" ";
				$class_name = "";
				$panel="";
			}
		
			 $xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\">{$value[1]}:<br /><div class=\"{$class_name}{$dark_theme}\">{$panel}<textarea name=\"xfield[{$fieldname}]\" id=\"xf_$fieldname\" {$params}>{$fieldvalue}</textarea></div></div>";

		} elseif ($value[3] == "htmljs") {
			
			 $xfbuffer .= "<div class=\"xfieldsrow\">{$value[1]}:<br /><textarea name=\"xfield[{$fieldname}]\" id=\"xf_$fieldname\" class=\"quick-edit-textarea\">{$fieldvalue}</textarea></div>";

		} elseif ($value[3] == "text") {

			$fieldvalue = str_replace('&amp;', '&', $fieldvalue);

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><input type=\"text\" name=\"xfield[{$fieldname}]\" id=\"xfield[{$fieldname}]\" value=\"{$fieldvalue}\" class=\"quick-edit-text\" /></div></div>";

		} elseif ($value[3] == "datetime") {

			if ($value[23] == 1) {
				$e_params = "data-rel=\"calendardate\" ";
			} elseif ($value[23] == 2) {
				$e_params = "data-rel=\"calendartime\" ";
			} else {
				$e_params = "data-rel=\"calendardatetime\" ";
			}

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><input type=\"text\" name=\"xfield[{$fieldname}]\" id=\"xfield[{$fieldname}]\" value=\"{$fieldvalue}\" class=\"quick-edit-datetime\" {$e_params}></div></div>";

		} elseif ($value[3] == "select") {
			
			if ($value[34]) {
				$sel_multiple = "data-placeholder=\" \" multiple";
			} else {
				$sel_multiple = "";
			}

			$fieldvalue = str_replace('&amp;', '&', $fieldvalue);
			$fieldvalue = explode(',', $fieldvalue);
			$fieldvalue = array_map('clear_select', $fieldvalue);

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><select name=\"xfield[{$fieldname}][]\" class=\"quick-edit-select\" {$sel_multiple}>";

	        foreach (explode("\r\n", htmlspecialchars($value[4], ENT_QUOTES, 'UTF-8' )) as $index => $value) {
			  
			  $value = explode("|", $value);
			  if( count($value) < 2) $value[1] = $value[0];
			  
	          $xfbuffer .= "<option value=\"$index\"" . (in_array($value[0], $fieldvalue) ? " selected" : "") . ">$value[1]</option>\r\n";
	        }

			$xfbuffer .= "</select></div></div>";

		} elseif ($value[3] == "yesorno") {
			
			$fieldvalue = intval($fieldvalue);
			
			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><div class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"xfield[{$fieldname}]\" value=\"1\"" . ($fieldvalue ? "checked " : "") . "></label></div></div></div>";

		} elseif( $value[3] == "image" ) {
			
			$max_file_size = (int)$value[10] * 1024;
			
			if( $fieldvalue ) {
				
				$temp_array = explode('|', $fieldvalue);
					
				if (count($temp_array) == 1 OR count($temp_array) == 5 ){
						
					$temp_alt = '';
					$temp_value = implode('|', $temp_array );
						
				} else {
						
					$temp_alt = $temp_array[0];
					unset($temp_array[0]);
					$temp_value =  implode('|', $temp_array );
						
				}
			
				$dataimage = get_uploaded_image_info($temp_value);
	
				if( $value[12] AND $dataimage->thumb ) {
					$img_url = 	$dataimage->thumb;
				} else {
					$img_url = 	$dataimage->url;
				}
				
				$filename = explode("_", $dataimage->name);
				if( count($filename) > 1 AND strlen($filename[0]) == 10) unset($filename[0]);
				$filename = implode("_", $filename);

				$base_name = pathinfo($filename, PATHINFO_FILENAME);
				$file_type = explode(".", $filename);
				$file_type = totranslit(end($file_type));

				$xf_id = md5($temp_value);
				$up_image = "<div class=\"file-preview-card uploadedfile\" id=\"xf_{$xf_id}\" data-id=\"{$temp_value}\" data-alt=\"{$temp_alt}\"><div class=\"active-ribbon\"><span><i class=\"mediaupload-icon mediaupload-icon-ok\"></i></span></div><div class=\"file-content select-disable\"><div class=\"file-ext\">{$file_type}</div><img src=\"{$img_url}\" class=\"file-preview-image\"></div><div class=\"file-footer\"><div class=\"file-footer-caption\"><div class=\"file-caption-info\" rel=\"tooltip\" title=\"{$filename}\">{$base_name}</div><div class=\"file-size-info\">{$dataimage->dimension} ({$dataimage->size})</div></div><div class=\"file-footer-bottom\"><div class=\"file-preview\"><a onclick=\"xfaddalt(\\'".$xf_id."\\', \\'".$fieldname."\\');return false;\" href=\"#\" rel=\"tooltip\" title=\"{$lang['xf_img_descr']}\"><i class=\"mediaupload-icon mediaupload-icon-edit\"></i></a></div><div class=\"file-delete\"><a href=\"#\" onclick=\"xfimagedelete(\\'".$fieldname."\\',\\'".$temp_value."\\');return false;\" href=\"#\"><i class=\"mediaupload-icon mediaupload-icon-trash\"></i></a></div></div></div></div>";
				
			} else $up_image = "";

$max_file_size = number_format($max_file_size, 0, '', '');

$uploadscript = <<<HTML

$('#xfupload_{$fieldname}').html('<div class="qq-uploader"><div id="uploadedfile_{$fieldname}">{$up_image}</div><div style="position: relative;"><div id="upload_button_{$fieldname}" class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$lang['xfield_xfim']}</div></div></div>');

file_uploaders['{$fieldname}'] = new plupload.Uploader({

    runtimes : 'html5',
    file_data_name: "qqfile",
    browse_button: 'upload_button_{$fieldname}',
    container: document.getElementById('xfupload_{$fieldname}'),
	drop_element: document.getElementById('xfupload_{$fieldname}'),
    url: dle_root + "engine/ajax/controller.php?mod=upload",
	multipart_params: {"subaction" : "upload", "news_id" : "{$row['id']}", "area" : "xfieldsimage", "author" : "{$author}", "xfname" : "{$fieldname}", "user_hash" : "{$dle_login_hash}"},
	multi_selection: false,
	chunk_size: '{$config['file_chunk_size']}mb',
     
    filters : {
        max_file_size : '{$max_file_size}',
        mime_types: [
            {title : "Image files", extensions : "gif,jpg,jpeg,png,bmp,webp,avif"}
        ]
    },
     
 
    init: {
 
        FilesAdded: function(up, files) {
		
            plupload.each(files, function(file) {
				$('<div id="uploadfile-'+file.id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+file.name+'</span>&nbsp;<span class="qq-status" ><span class="qq-upload-spinner"></span> <span class="qq-upload-size"></span></span><div class="progress"><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_{$fieldname}');
            });
			
			up.start();
			up.refresh();
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
					
						var returnbox = response.returnbox;
						var returnval = response.xfvalue;

						returnbox = returnbox.replace(/&lt;/g, "<");
						returnbox = returnbox.replace(/&gt;/g, ">");
						returnbox = returnbox.replace(/&amp;/g, "&");

						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st9']}');
						$('#uploadedfile_{$fieldname}').html( returnbox );
						$('#xf_{$fieldname}').val(returnval);

						$('#upload_button_{$fieldname}').attr("disabled","disabled");
						
						up.disableBrowse(true);
						
						setTimeout(function() {
						
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
							
						}, 1000);
						
						$('#mediaupload').remove();

					} else {
					
						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st10']}');

						if( response.error ) $('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">' + response.error + '</span>' );

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
						}, 10000);
					}
						
				} else {
				
					$('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">HTTP Error:' + result.status + '</span>' );
					
					setTimeout(function() {
						$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
					}, 10000);
				}

				up.refresh();
				
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

setTimeout(function() {
	
	file_uploaders['{$fieldname}'].init();

	if($('#xf_{$fieldname}').val() != "" ) {
		$('#upload_button_{$fieldname}').attr("disabled","disabled");

		setTimeout(function() {
			file_uploaders['{$fieldname}'].disableBrowse(true);
		}, 100);

	}
}, 300);
	
	if ( typeof Sortable != "undefined"  ) {
	
		var sortable_{$fieldcount} = Sortable.create(document.getElementById('uploadedfile_{$fieldname}'), {
		  group: {
			name: 'xfuploadedimages',
			put: function (to, from) {

				if(from.options.group.name != to.options.group.name ){
					return false;
				}

				return to.el.children.length < 1;
			}
		  },
		  handle: '.file-content',
		  draggable: '.uploadedfile',
		  onSort: function (evt) {
				
				if( sortable_{$fieldcount}.el.children.length ) {
					$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
					file_uploaders['{$fieldname}'].disableBrowse(true);
				} else {
					$('#xfupload_{$fieldname} .qq-upload-button').removeAttr('disabled');
					file_uploaders['{$fieldname}'].disableBrowse(false);
				}
				
				xfsinc('{$fieldname}');
				file_uploaders['{$fieldname}'].refresh();
		  },
		  animation: 150
		});
		
	}
	
HTML;
			
			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><div id=\"xfupload_{$fieldname}\"></div><input type=\"hidden\" name=\"xfield[$fieldname]\" id=\"xf_$fieldname\" value=\"{$fieldvalue}\" /><script>{$uploadscript}</script></div></div>";

		} elseif( $value[3] == "imagegalery" ) {

	    $max_file_size = (int)$value[10] * 1024;

		if( $fieldvalue ) {
			$fieldvalue_arr = explode(',', $fieldvalue);
			$up_image = array();
			
			foreach ($fieldvalue_arr as $temp_value) {
				
				$temp_value = trim($temp_value);
				
				if($temp_value == "") continue;
				
				$temp_array = explode('|', $temp_value);
					
				if (count($temp_array) == 1 OR count($temp_array) == 5 ){
						
					$temp_alt = '';
					$temp_value = implode('|', $temp_array );
						
				} else {
						
					$temp_alt = $temp_array[0];
					unset($temp_array[0]);
					$temp_value =  implode('|', $temp_array );
						
				}
			
				$dataimage = get_uploaded_image_info($temp_value);
				
				if( $value[12] AND $dataimage->thumb ) {
					$img_url = 	$dataimage->thumb;
				} else {
					$img_url = 	$dataimage->url;
				}
				
				$filename = explode("_", $dataimage->name);
				if( count($filename) > 1 AND strlen($filename[0]) == 10 ) unset($filename[0]);
				$filename = implode("_", $filename);

				$base_name = pathinfo($filename, PATHINFO_FILENAME);
				$file_type = explode(".", $filename);
				$file_type = totranslit(end($file_type));

				$xf_id = md5($temp_value);
				$up_image[] = "<div class=\"file-preview-card uploadedfile\" id=\"xf_{$xf_id}\" data-id=\"{$temp_value}\" data-alt=\"{$temp_alt}\"><div class=\"active-ribbon\"><span><i class=\"mediaupload-icon mediaupload-icon-ok\"></i></span></div><div class=\"file-content select-disable\"><div class=\"file-ext\">{$file_type}</div><img src=\"{$img_url}\" class=\"file-preview-image\"></div><div class=\"file-footer\"><div class=\"file-footer-caption\"><div class=\"file-caption-info\" rel=\"tooltip\" title=\"{$filename}\">{$base_name}</div><div class=\"file-size-info\">{$dataimage->dimension} ({$dataimage->size})</div></div><div class=\"file-footer-bottom\"><div class=\"file-preview\"><a onclick=\"xfaddalt(\\'".$xf_id."\\', \\'".$fieldname."\\');return false;\" href=\"#\" rel=\"tooltip\" title=\"{$lang['xf_img_descr']}\"><i class=\"mediaupload-icon mediaupload-icon-edit\"></i></a></div><div class=\"file-delete\"><a onclick=\"xfimagegalerydelete_{$fieldcount}(\\'".$fieldname."\\',\\'".$temp_value."\\', \\'".$xf_id."\\');return false;\" href=\"#\"><i class=\"mediaupload-icon mediaupload-icon-trash\"></i></a></div></div></div></div>";

			}
			
			$totaluploadedfiles = count($up_image);
			$up_image = implode($up_image);

			
		} else { $up_image = ""; $totaluploadedfiles = 0; }
		
		if (!$value[5]) { 
			$params = "rel=\"essential\" "; 
			$uid = "uid=\"essential\" "; 

		} else { 

			$params = ""; 
			$uid = "";

		}
		
$max_file_size = number_format($max_file_size, 0, '', '');

$uploadscript = <<<HTML
	var maxallowfiles_{$fieldcount} = {$value[16]};
	var totaluploaded_{$fieldcount} = {$totaluploadedfiles};
	var totalqueue_{$fieldcount} = 0;
	
	function xfimagegalerydelete_{$fieldcount} ( xfname, xfvalue, id )
	{
		DLEconfirmDelete( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');

			$.post(dle_root + 'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'images[]' : xfvalue }, function(data){
	
				HideLoading('');

				$('#xf_'+id).remove();
				totaluploaded_{$fieldcount} --;
				xfsinc('{$fieldname}');
				
				$('#xfupload_' + xfname + ' .qq-upload-button').removeAttr('disabled');
				file_uploaders[xfname].disableBrowse(false);
				file_uploaders[xfname].refresh();

				$('#mediaupload').remove();
				
			});
			
		} );
		
		return false;

	};
	
$('#xfupload_{$fieldname}').html('<div class="qq-uploader"><div id="uploadedfile_{$fieldname}">{$up_image}</div><div style="position: relative;"><div id="upload_button_{$fieldname}" class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$lang['xfield_xfimg']}</div></div></div>');

file_uploaders['{$fieldname}'] = new plupload.Uploader({

    runtimes : 'html5',
    file_data_name: "qqfile",
    browse_button: 'upload_button_{$fieldname}',
    container: document.getElementById('xfupload_{$fieldname}'),
	drop_element: document.getElementById('xfupload_{$fieldname}'),
    url: dle_root + "engine/ajax/controller.php?mod=upload",
	multipart_params: {"subaction" : "upload", "news_id" : "{$row['id']}", "area" : "xfieldsimagegalery", "author" : "{$author}", "xfname" : "{$fieldname}", "user_hash" : "{$dle_login_hash}"},

	chunk_size: '{$config['file_chunk_size']}mb',
     
    filters : {
        max_file_size : '{$max_file_size}',
        mime_types: [
            {title : "Image files", extensions : "gif,jpg,jpeg,png,bmp,webp,avif"}
        ]
    },
     
 
    init: {
 
        FilesAdded: function(up, files) {
		
            plupload.each(files, function(file) {
			
				totalqueue_{$fieldcount} ++;
				
				if(maxallowfiles_{$fieldcount} && (totaluploaded_{$fieldcount} + totalqueue_{$fieldcount} ) > maxallowfiles_{$fieldcount} ) {
					totalqueue_{$fieldcount} --;
				
					$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
					
					up.disableBrowse(true);
					up.removeFile(file);

				} else {
					$('<div id="uploadfile-'+file.id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+file.name+'</span>&nbsp;<span class="qq-status"> <span class="qq-upload-spinner"></span> <span class="qq-upload-size"></span></span><div class="progress"><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_{$fieldname}');
				}
					
            });
			
			up.start();
			up.refresh();
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
				
				totalqueue_{$fieldcount} --;
				
				if( result.status == 200 ) {
				
					if ( response.success ) {
					
						totaluploaded_{$fieldcount} ++;

						var fieldvalue = $('#xf_{$fieldname}').val();
					
						var returnbox = response.returnbox;
						var returnval = response.xfvalue;

						returnbox = returnbox.replace(/&lt;/g, "<");
						returnbox = returnbox.replace(/&gt;/g, ">");
						returnbox = returnbox.replace(/&amp;/g, "&");

						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st9']}');
						$('#uploadedfile_{$fieldname}').append( returnbox );
						
						if (fieldvalue == "") {
							$('#xf_{$fieldname}').val(returnval);
						} else {
							fieldvalue += ',' +returnval;
							$('#xf_{$fieldname}').val(fieldvalue);
						}

						if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} == maxallowfiles_{$fieldcount} ) {
								$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
								up.disableBrowse(true);
						}

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
						}, 1000);
						
						$('#mediaupload').remove();

					} else {
					
						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st10']}');

						if( response.error ) $('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">' + response.error + '</span>' );

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
						}, 10000);
					}
						
				} else {
				
					$('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">HTTP Error:' + result.status + '</span>' );
					
					setTimeout(function() {
						$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh(); });
					}, 10000);
				}

				up.refresh();
				
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
	
if ( typeof Sortable != "undefined"  ) {

	var sortable_{$fieldcount} = Sortable.create(document.getElementById('uploadedfile_{$fieldname}'), {
		group: {
		name: 'xfuploadedimages',
		put: function (to, from) {

			if(from.options.group.name != to.options.group.name ){
				return false;
			}

			if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >= maxallowfiles_{$fieldcount} ) {
				return false;
			} else {return true;}
		}
		},
		handle: '.file-content',
		draggable: '.uploadedfile',
		onSort: function (evt) {

			totaluploaded_{$fieldcount} = sortable_{$fieldcount}.el.children.length;
			
			if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >= maxallowfiles_{$fieldcount} ) {
				$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
				file_uploaders['{$fieldname}'].disableBrowse(true);
			} else {
				$('#xfupload_{$fieldname} .qq-upload-button').removeAttr('disabled');
				file_uploaders['{$fieldname}'].disableBrowse(false);
			}
			
			xfsinc('{$fieldname}');
			file_uploaders['{$fieldname}'].refresh();
		},
		animation: 150
	});
	
}

setTimeout(function() {
	file_uploaders['{$fieldname}'].init();

	if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >=  maxallowfiles_{$fieldcount} ) {
		$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
		setTimeout(function() {
			file_uploaders['{$fieldname}'].disableBrowse(true);
			file_uploaders['{$fieldname}'].refresh();
		}, 100);
	}

}, 300);

HTML;

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><div id=\"xfupload_{$fieldname}\"></div><input type=\"hidden\" name=\"xfield[$fieldname]\" id=\"xf_$fieldname\" value=\"{$fieldvalue}\" /><script>{$uploadscript}</script></div></div>";

		} elseif ($value[3] == "video" OR $value[3] == "audio") {

			$max_file_size = (int)$value[32] * 1024;

			if ($fieldvalue) {
				$fieldvalue_arr = explode(',', $fieldvalue);
				$up_files = array();

				foreach ($fieldvalue_arr as $temp_value) {

					$temp_value = trim($temp_value);

					if (!$temp_value) continue;

					$temp_array = explode('|', $temp_value);

					if (count($temp_array) < 4) {

						$temp_alt = '';
						$temp_id = $temp_array[1];
						$temp_size = $temp_array[2];
						$temp_url = $temp_array[0];
						$temp_value = implode('|', $temp_array);
					} else {

						$temp_alt = $temp_array[0];
						$temp_id = $temp_array[2];
						$temp_size = $temp_array[3];
						$temp_url = $temp_array[1];
						unset($temp_array[0]);
						$temp_value =  implode('|', $temp_array);
					}

					$filename = pathinfo($temp_url, PATHINFO_BASENAME);
					$filename = explode("_", $filename);
					if (count($filename) > 1 AND strlen($filename[0]) == 10 ) unset($filename[0]);
					$filename = implode("_", $filename);
					
					$base_name = pathinfo($filename, PATHINFO_FILENAME);
					$file_type = explode(".", $filename);
					$file_type = totranslit(end($file_type));

					if ( in_array($file_type, array('mp3', 'flac', 'aac', 'ogg')) ) {
						$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-240.5 -297.644)"><g transform="translate(196.745 265.397)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#ffa734" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#ffa734"></path></g><path d="M23.3-6.68H21.372l-.759-3.432a.778.778,0,0,0-.723-.574.778.778,0,0,0-.722.571l-1.179,5.2-1.225-8.7a.765.765,0,0,0-.735-.636.761.761,0,0,0-.737.653L14.2-4.319,12.61-15.984a.764.764,0,0,0-.735-.64.764.764,0,0,0-.735.64L9.551-4.318,8.456-13.6a.761.761,0,0,0-.737-.654.764.764,0,0,0-.735.638L5.76-4.908,4.582-10.114a.778.778,0,0,0-.722-.572.778.778,0,0,0-.723.573L2.378-6.68H.445A.445.445,0,0,0,0-6.234v.594A.445.445,0,0,0,.445-5.2H2.972a.772.772,0,0,0,.719-.575l.173-.74L5.215-.573A.719.719,0,0,0,5.966,0h.008a.769.769,0,0,0,.7-.637l.983-7.027L8.762,1.721a.742.742,0,0,0,1.473.013L11.875-10.3l1.64,12.037a.742.742,0,0,0,1.473-.013L16.1-7.664l.983,7.026a.771.771,0,0,0,.7.638.717.717,0,0,0,.755-.573L19.886-6.51l.173.739a.772.772,0,0,0,.72.576H23.3a.445.445,0,0,0,.445-.445v-.594A.445.445,0,0,0,23.3-6.68Z" transform="translate(256.344 339.5)" fill="#ffa734"></path></g></svg>';
						$b_color = '#fff6ea';
						$img_url = $_ROOT_DLE_URL . "engine/skins/images/mp3_file.png";
					} else {
						$file_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.054 66.35" width="66" height="76" class="file-icon file-ext-' . $file_type . '"><g transform="translate(-586.74 -502.325)"><g transform="translate(542.985 470.079)"><path d="M82.585,33.746H53.6a8.342,8.342,0,0,0-8.342,8.342V88.754A8.342,8.342,0,0,0,53.6,97.1H89.966a8.342,8.342,0,0,0,8.342-8.342V49.469Z" fill="#fff" stroke="#04a0b2" stroke-miterlimit="10" stroke-width="3"></path><path d="M204.77,33.746v9.866a7.156,7.156,0,0,0,7.156,7.156h9.866Z" transform="translate(-123.189)" fill="#04a0b2"></path></g><g transform="translate(0.887 3.384)"><g transform="translate(603.613 524.116)"><path d="M3,16a3,3,0,0,1-3-3V3A3,3,0,0,1,3,0h8.3a3,3,0,0,1,3,3V5.943L20.471,2.1A1,1,0,0,1,22,2.944V13.055a1,1,0,0,1-1.529.849L14.3,10.057V13a3,3,0,0,1-3,3Z" fill="#04a0b2"></path></g></g></g></svg>';
						$b_color = '#e5f5f7';
						$img_url = $_ROOT_DLE_URL . "engine/skins/images/video_file.png";
					}

					$xf_id = md5($temp_value);

					$up_files[] = "<div class=\"file-preview-card uploadedfile\" id=\"xf_{$xf_id}\" data-id=\"{$temp_value}\" data-alt=\"{$temp_alt}\"><div class=\"active-ribbon\"><span><i class=\"mediaupload-icon mediaupload-icon-ok\"></i></span></div><div class=\"file-content select-disable\" style=\"background-color: {$b_color};\"><div class=\"file-ext\">{$file_type}</div>{$file_icon}</div><div class=\"file-footer\"><div class=\"file-footer-caption\"><div class=\"file-caption-info\" rel=\"tooltip\" title=\"{$filename}\">{$base_name}</div><div class=\"file-size-info\">({$temp_size})</div></div><div class=\"file-footer-bottom\"><div class=\"file-preview\"><a onclick=\"xfaddalt(\\'" . $xf_id . "\\', \\'" . $fieldname . "\\');return false;\" href=\"#\" rel=\"tooltip\" title=\"{$lang['xf_img_descr']}\"><i class=\"mediaupload-icon mediaupload-icon-edit\"></i></a></div><div class=\"file-delete\"><a onclick=\"xfplaylistdelete_{$fieldcount}(\\'" . $fieldname . "\\',\\'" . $temp_id . "\\', \\'" . $xf_id . "\\');return false;\" href=\"#\"><i class=\"mediaupload-icon mediaupload-icon-trash\"></i></a></div></div></div></div>";
				}

				$totaluploadedfiles = count($up_files);
				$up_files = implode($up_files);

			} else {
				$up_files = "";
				$totaluploadedfiles = 0;
			}

			if (!$value[5]) {
				$params = "rel=\"essential\" ";
				$uid = "uid=\"essential\" ";
			} else {
				$params = "";
				$uid = "";
			}

			$max_file_size = number_format($max_file_size, 0, '', '');

			if ($value[3] == "audio") {
				$allowed_files = "mp3,flac,aac,ogg";
				$button_text = $lang['xfield_xfaudio'];
			} else {
				$allowed_files = "mp4,m4v,m4a,mov,webm,m3u8,mkv";
				$button_text = $lang['xfield_xfvideo'];
			}

			$uploadscript = <<<HTML
	var maxallowfiles_{$fieldcount} = {$value[31]};
	var totaluploaded_{$fieldcount} = {$totaluploadedfiles};
	var totalqueue_{$fieldcount} = 0;
	
	function xfplaylistdelete_{$fieldcount} ( xfname, xfvalue, id )
	{
		DLEconfirmDelete( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
	
			$.post(dle_root +'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'files[]' : xfvalue }, function(data){
	
				HideLoading('');

				$('#xf_'+id).remove();
				totaluploaded_{$fieldcount} --;
				xfsinc('{$fieldname}');
				
				$('#xfupload_' + xfname + ' .qq-upload-button').removeAttr('disabled');
				
				if (typeof file_uploaders[xfname] !== 'undefined') {
					file_uploaders[xfname].disableBrowse(false);
					file_uploaders[xfname].refresh();
				}
				
				$('#mediaupload').remove();
				
			});
			
		} );
		
		return false;

	};
	
$('#xfupload_{$fieldname}').html('<div class="qq-uploader"><div id="uploadedfile_{$fieldname}">{$up_files}</div><div style="position: relative;"><div id="upload_button_{$fieldname}" class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$button_text}</div></div></div>');

file_uploaders['{$fieldname}'] = new plupload.Uploader({

    runtimes : 'html5',
    file_data_name: "qqfile",
    browse_button: 'upload_button_{$fieldname}',
    container: document.getElementById('xfupload_{$fieldname}'),
	drop_element: document.getElementById('xfupload_{$fieldname}'),
    url: dle_root + "engine/ajax/controller.php?mod=upload",
	multipart_params: {"subaction" : "upload", "news_id" : "{$row['id']}", "area" : "xfields{$value[3]}", "author" : "{$author}", "xfname" : "{$fieldname}", "user_hash" : "{$dle_login_hash}"},

	chunk_size: '{$config['file_chunk_size']}mb',
     
    filters : {
        max_file_size : '{$max_file_size}',
        mime_types: [
            {title : "Files", extensions : "{$allowed_files}"}
        ]
    },

    init: {
 
        FilesAdded: function(up, files) {
		
            plupload.each(files, function(file) {
			
				totalqueue_{$fieldcount} ++;
				
				if(maxallowfiles_{$fieldcount} && (totaluploaded_{$fieldcount} + totalqueue_{$fieldcount} ) > maxallowfiles_{$fieldcount} ) {
					totalqueue_{$fieldcount} --;
				
					$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
					
					up.disableBrowse(true);
					up.removeFile(file);

				} else {
					$('<div id="uploadfile-'+file.id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+file.name+'</span>&nbsp;<span class="qq-status"> <span class="qq-upload-spinner"></span> <span class="qq-upload-size"></span></span><div class="progress"><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_{$fieldname}');
				}
					
            });
			
			up.start();
			up.refresh();
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
				
				totalqueue_{$fieldcount} --;
				
				if( result.status == 200 ) {
				
					if ( response.success ) {
					
						totaluploaded_{$fieldcount} ++;

						var fieldvalue = $('#xf_{$fieldname}').val();
					
						var returnbox = response.returnbox;
						var returnval = response.xfvalue;

						returnbox = returnbox.replace(/&lt;/g, "<");
						returnbox = returnbox.replace(/&gt;/g, ">");
						returnbox = returnbox.replace(/&amp;/g, "&");

						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st9']}');
						$('#uploadedfile_{$fieldname}').append( returnbox );
						
						if (fieldvalue == "") {
							$('#xf_{$fieldname}').val(returnval);
						} else {
							fieldvalue += ',' +returnval;
							$('#xf_{$fieldname}').val(fieldvalue);
						}

						if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} == maxallowfiles_{$fieldcount} ) {
								$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
								up.disableBrowse(true);
						}

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
						}, 1000);
						
						$('#mediaupload').remove();

					} else {
					
						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st10']}');

						if( response.error ) $('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">' + response.error + '</span>' );

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh();});
						}, 10000);
					}
						
				} else {
				
					$('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">HTTP Error:' + result.status + '</span>' );
					
					setTimeout(function() {
						$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); up.refresh(); });
					}, 10000);
				}

				up.refresh();
				
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
	
if ( typeof Sortable != "undefined"  ) {

	var sortable_{$fieldcount} = Sortable.create(document.getElementById('uploadedfile_{$fieldname}'), {
		group: {
		name: 'xfuploaded{$value[3]}',
		put: function (to, from) {

			if(from.options.group.name != to.options.group.name ){
				return false;
			}

			if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >= maxallowfiles_{$fieldcount} ) {
				return false;
			} else {return true;}
		}
		},
		handle: '.file-content',
		draggable: '.uploadedfile',
		onSort: function (evt) {

			totaluploaded_{$fieldcount} = sortable_{$fieldcount}.el.children.length;
			
			if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >= maxallowfiles_{$fieldcount} ) {
				$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
				file_uploaders['{$fieldname}'].disableBrowse(true);
			} else {
				$('#xfupload_{$fieldname} .qq-upload-button').removeAttr('disabled');
				file_uploaders['{$fieldname}'].disableBrowse(false);
			}
			
			xfsinc('{$fieldname}');
			file_uploaders['{$fieldname}'].refresh();
		},
		animation: 150
	});
	
}

setTimeout(function() {
	file_uploaders['{$fieldname}'].init();

	if(maxallowfiles_{$fieldcount} && totaluploaded_{$fieldcount} >=  maxallowfiles_{$fieldcount} ) {
		$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
		setTimeout(function() {
			file_uploaders['{$fieldname}'].disableBrowse(true);
			file_uploaders['{$fieldname}'].refresh();
		}, 100);
	}

}, 300);

HTML;

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><div id=\"xfupload_{$fieldname}\"></div><input type=\"hidden\" name=\"xfield[$fieldname]\" id=\"xf_$fieldname\" value=\"{$fieldvalue}\" /><script>{$uploadscript}</script></div></div>";


		} elseif( $value[3] == "file" ) {
			
			$max_file_size = (int)$value[15] * 1024;
			$allowed_files = strtolower( $value[14] );
	
			$fieldvalue = str_replace('&amp;', '&', $fieldvalue);
			
			if( $fieldvalue ) {
				
				$fileid = intval(preg_replace( "'\[attachment=(.*?):(.*?)\]'si", "\\1", $fieldvalue ));
				
				$fileid = "&nbsp;<button class=\"qq-upload-button btn btn-sm btn-red\" onclick=\"xffiledelete('".$fieldname."','".$fileid."');return false;\">{$lang['xfield_xfid']}</button>";
	
				$show="display:inline-block;";
				
			} else { $show="display:none;"; $fileid="";}

			$max_file_size = number_format($max_file_size, 0, '', '');
			
$uploadscript = <<<HTML
$('#xfupload_{$fieldname}').html('<div class="qq-uploader"><div style="position: relative;"><div id="upload_button_{$fieldname}" class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">{$lang['xfield_xfif']}</div></div></div>');

file_uploaders['{$fieldname}'] = new plupload.Uploader({

    runtimes : 'html5',
    file_data_name: "qqfile",
    browse_button: 'upload_button_{$fieldname}',
    container: document.getElementById('xfupload_{$fieldname}'),
	drop_element: document.getElementById('xfupload_{$fieldname}'),
    url: dle_root + "engine/ajax/controller.php?mod=upload",
	multipart_params: {"subaction" : "upload", "news_id" : "{$row['id']}", "area" : "xfieldsfile", "author" : "{$author}", "xfname" : "{$fieldname}", "user_hash" : "{$dle_login_hash}"},
	multi_selection: false,
	chunk_size: '{$config['file_chunk_size']}mb',
     
    filters : {
        max_file_size : '{$max_file_size}',
        mime_types: [
            {title : "Files", extensions : "{$allowed_files}"}
        ]
    },
     
 
    init: {
 
        FilesAdded: function(up, files) {
		
            plupload.each(files, function(file) {
				$('<div id="uploadfile-'+file.id+'" class="file-box"><span class="qq-upload-file-status">{$lang['media_upload_st6']}</span><span class="qq-upload-file">&nbsp;'+file.name+'</span>&nbsp;<span class="qq-status"> <span class="qq-upload-spinner"></span> <span class="qq-upload-size"></span></span><div class="progress"><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#xfupload_{$fieldname}');
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
					
						var returnbox = response.returnbox;
						var returnval = response.xfvalue;

						returnbox = returnbox.replace(/&lt;/g, "<");
						returnbox = returnbox.replace(/&gt;/g, ">");
						returnbox = returnbox.replace(/&amp;/g, "&");

						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st9']}');
						$('#xf_{$fieldname}').show();
						$('#uploadedfile_{$fieldname}').html( returnbox );
						$('#xf_{$fieldname}').val(returnval);
						$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");
						
						up.disableBrowse(true);
						
						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
						}, 1000);
						
						$('#mediaupload').remove();

					} else {
					
						$('#uploadfile-'+file.id+' .qq-status').html('{$lang['media_upload_st10']}');

						if( response.error ) $('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">' + response.error + '</span>' );

						setTimeout(function() {
							$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
						}, 10000);
					}
						
				} else {
				
					$('#uploadfile-'+file.id+' .qq-status').append( '<br><span style="color:red">HTTP Error:' + result.status + '</span>' );
					
					setTimeout(function() {
						$('#uploadfile-'+file.id).fadeOut('slow', function() { $(this).remove(); });
					}, 10000);
				}
				
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

setTimeout(function() {

	file_uploaders['{$fieldname}'].init();

	if($('#xf_{$fieldname}').val() != "" ) {

		$('#xfupload_{$fieldname} .qq-upload-button').attr("disabled","disabled");

		setTimeout(function() {
			file_uploaders['{$fieldname}'].disableBrowse(true);
		}, 100);
		
	}

}, 300);
HTML;

			$xfbuffer .= "<div id=\"{$holderid}\" class=\"xfieldsrow\"><div class=\"xfieldscolleft\">{$value[1]}:</div><div class=\"xfieldscolright\"><input style=\"{$show}\" class=\"quick-edit-text\" type=\"text\" name=\"xfield[$fieldname]\" id=\"xf_$fieldname\" value=\"{$fieldvalue}\" /><span id=\"uploadedfile_{$fieldname}\">{$fileid}</span><div id=\"xfupload_{$fieldname}\"></div><script>{$uploadscript}</script></div></div>";
		
		}
	
	}
	
	$addtype = "addnews";

	$p_name = urlencode($row['autor']);

	if($config['bbimages_in_wysiwyg']) {
		$implugin = 'dleimage';
	} else $implugin = 'image';

	$image_upload = array();
	
	if ( $user_group[$member_id['user_group']]['allow_image_upload'] ) {

		$image_upload[0] = "dleupload ";

		$image_upload[1] = <<<HTML
var dle_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
  var xhr, formData;

  xhr = new XMLHttpRequest();
  xhr.withCredentials = false;
  xhr.open('POST', dle_root + 'engine/ajax/controller.php?mod=upload');
  
  xhr.upload.onprogress = (e) => {
    progress(e.loaded / e.total * 100);
  };

  xhr.onload = function() {
    var json;

    if (xhr.status === 403) {
      reject('HTTP Error: ' + xhr.status, { remove: true });
      return;
    }

    if (xhr.status < 200 || xhr.status >= 300) {
      reject('HTTP Error: ' + xhr.status);
      return;
    }

    json = JSON.parse(xhr.responseText);

    if (!json || typeof json.link != 'string') {

		if(typeof json.error == 'string') {
			reject(json.error);
		} else {
			reject('Invalid JSON: ' + xhr.responseText);	
		}
		
		var editor = tinymce.activeEditor;
		var node = editor.selection.getEnd();
		editor.selection.select(node);
		editor.selection.setContent('');
		
      return;
    }

	if( json.flink ) {
		
		var editor = tinymce.activeEditor;
		var node = editor.selection.getEnd();

		var object = $(node);
		object.removeAttr('width');
		object.removeAttr('height');
		object.attr('src', json.link );
		object.attr('style', 'display: block; margin-left: auto; margin-right: auto;' );
		
		editor.selection.select(node);
		editor.selection.setContent('<a href="'+json.flink+'" class="highslide">'+object.prop('outerHTML')+'</a><br>');
		editor.notificationManager.close();
		$('#mediaupload').remove();

	} else {
		resolve(json.link);
		$('#mediaupload').remove();
	}
	
  };

  xhr.onerror = function () {
    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
  };

  formData = new FormData();
  formData.append('qqfile', blobInfo.blob(), blobInfo.filename());
  formData.append("subaction", "upload");
  formData.append("news_id", "{$row['id']}");
  formData.append("area", "short_story");
  formData.append("author", "{$p_name}");
  formData.append("mode", "quickload");
  formData.append("editor_mode", "tinymce");
  formData.append("user_hash", "{$dle_login_hash}");    
  
  xhr.send(formData);
});
HTML;

		$image_upload[2] = <<<HTML
paste_data_images: true,
automatic_uploads: true,
images_upload_handler: dle_image_upload_handler,
images_reuse_filename: true,
image_uploadtab: false,
images_file_types: 'gif,jpg,png,jpeg,bmp,webp,avif',
file_picker_types: 'image',

file_picker_callback: function (cb, value, meta) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');

    input.addEventListener('change', (e) => {
      const file = e.target.files[0];

		var filename = file.name;
		filename = filename.split('.').slice(0, -1).join('.');
	
      const reader = new FileReader();
      reader.addEventListener('load', () => {

        const id = filename;
        const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        const base64 = reader.result.split(',')[1];
        const blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        cb(blobInfo.blobUri());

      });
      reader.readAsDataURL(file);
    });

    input.click();
},
HTML;
		
			} else {
				
				$image_upload[0] = "";
				$image_upload[1] = "";
				$image_upload[2] = "";
				
			}	
			
			if( $user_group[$member_id['user_group']]['allow_file_upload'] ) {
				$image_upload[0] = "dleupload ";
			}
$chat_gpt = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '');

if ( $config['enable_ai'] AND in_array($member_id['user_group'], explode(',', trim($config['ai_groups'])) ) ) {

	$chat_gpt[0] = 'ai ';
	$chat_gpt[1] = 'aidialog ';
	$chat_gpt[2] = 'aishortcuts ';
	$chat_gpt[3] = "ai_request,
	ai_shortcuts: [
		{ title: 'Summarize content', prompt: '{$lang['ai_command_1']}', selection: true },
		{ title: 'Improve writing', prompt: '{$lang['ai_command_2']}', selection: true },
		{ title: 'Simplify language', prompt: '{$lang['ai_command_3']}', selection: true },
		{ title: 'Expand upon', prompt: '{$lang['ai_command_4']}', selection: true },
		{ title: 'Trim content', prompt: '{$lang['ai_command_5']}', selection: true },
		{
			title: 'Change tone', subprompts: [
			{ title: 'Professional', prompt: '{$lang['ai_command_6']}', selection: true },
			{ title: 'Casual', prompt: '{$lang['ai_command_7']}', selection: true },
			{ title: 'Direct', prompt: '{$lang['ai_command_8']}', selection: true },
			{ title: 'Confident', prompt: '{$lang['ai_command_9']}', selection: true },
			{ title: 'Friendly', prompt: '{$lang['ai_command_10']}', selection: true },
			]
		},
		{
			title: 'Change style', subprompts: [
			{ title: 'Business', prompt: '{$lang['ai_command_11']}', selection: true },
			{ title: 'Legal', prompt: '{$lang['ai_command_12']}', selection: true },
			{ title: 'Journalism', prompt: '{$lang['ai_command_13']}', selection: true },
			{ title: 'Medical', prompt: '{$lang['ai_command_14']}', selection: true },
			{ title: 'Poetic', prompt: '{$lang['ai_command_15']}', selection: true },
			]
		},
		{
			title: 'Translate', subprompts: [
			{ title: 'Translate to English', prompt: 'Translate this content to English language.', selection: true },
			{ title: 'Translate to Russian', prompt: 'Translate this content to Russian language.', selection: true },
			{ title: 'Translate to German', prompt: 'Translate this content to German language.', selection: true },
			{ title: 'Translate to Spanish', prompt: 'Translate this content to Spanish language.', selection: true },
			{ title: 'Translate to Portuguese', prompt: 'Translate this content to Portuguese language.', selection: true },
			{ title: 'Translate to French', prompt: 'Translate this content to French language.', selection: true },
			{ title: 'Translate to Norwegian', prompt: 'Translate this content to Norwegian language.', selection: true },
			{ title: 'Translate to Ukrainian', prompt: 'Translate this content to Ukrainian language.', selection: true },
			{ title: 'Translate to Japanese', prompt: 'Translate this content to Japanese language.', selection: true },
			{ title: 'Translate to Korean', prompt: 'Translate this content to Korean language.', selection: true },
			{ title: 'Translate to Simplified Chinese', prompt: 'Translate this content to Simplified Chinese language.', selection: true },
			{ title: 'Translate to Hebrew', prompt: 'Translate this content to Hebrew language.', selection: true },
			{ title: 'Translate to Hindi', prompt: 'Translate this content to Hindi language.', selection: true },
			{ title: 'Translate to Arabic', prompt: 'Translate this content to Arabic language.', selection: true },
			]
		},
	],
";

	if ($config['ai_tokens'] == '') $config['ai_tokens'] = 'null';
	if ($config['ai_completion_tokens'] == '') $config['ai_completion_tokens'] = 'null';
	if ($config['ai_temperature'] == '') $config['ai_temperature'] = 'null';

	$chat_gpt[4] = <<<HTML
const gptFetchApi = import(dle_root + "engine/editor/jscripts/tiny_mce/plugins/ai/fetch/index.js").then(module => module.fetchEventSource);

const gpt_api_key = '{$config['ai_key']}';
const gpt_api_endurl = '{$config['ai_endpoint']}';
const gpt_api_mode = '{$config['ai_mode']}';
const gpt_api_tokens = {$config['ai_tokens']};
const gpt_api_completion_tokens = {$config['ai_completion_tokens']};
const gpt_api_temperature = {$config['ai_temperature']};

const ai_request = (request, respondWith) => {
  respondWith.stream((signal, streamMessage) => {
    const conversation = request.thread.flatMap((event) => {
      if (event.response) {
        return [
          { role: 'user', content: event.request.query },
          { role: 'assistant', content: event.response.data }
        ];
      } else {
        return [];
      }
    });

    const pluginSystemMessages = request.system.map((content) => ({
      role: 'system',
      content
    }));

    const systemMessages = [
      ...pluginSystemMessages,
      { role: 'system', content: 'Remove lines with ``` from the response start and response end.' },
	  { role: 'system', content: 'Write PHP, CSS, Javascript, SQL code examples in the response inside <pre class="language-markup"><code></code></pre> tags formatted and converted special characters to HTML entities.' }
   ]

    const content = request.context.length === 0 || conversation.length > 0
      ? request.query
      : `Question: \${request.query} Context: """\${request.context}"""`;

    const messages = [
      ...conversation,
      ...systemMessages,
      { role: 'user', content }
    ];

	let hasHead = false;
	let markdownHead = '';

	const hasMarkdown = (message) => {
		if (message.includes('`') && markdownHead !== '```') {
			const numBackticks = message.split('`').length - 1;
			markdownHead += '`'.repeat(numBackticks);
			if (hasHead && markdownHead === '```') {
				markdownHead = '';
				hasHead = false;
			}
			return true;
		} else if (message.includes('html') && markdownHead === '```') {
			markdownHead = '';
			hasHead = true;
			return true;
		}
		return false;
	};

    var requestBody = {
      model: gpt_api_mode,
      messages,
      stream: true
    };

	if(gpt_api_temperature !== null ) {
		requestBody = { ...requestBody, temperature: gpt_api_temperature };
	}

	if(gpt_api_tokens !== null ) {
		requestBody = { ...requestBody, max_tokens: gpt_api_tokens };
	}

	if(gpt_api_completion_tokens !== null ) {
		requestBody = { ...requestBody, max_completion_tokens: gpt_api_completion_tokens };
	}

    const openAiOptions = {
      signal,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer \${gpt_api_key}`
      },
      body: JSON.stringify(requestBody)
    };

    const onopen = async (response) => {
      if (response) {
        const contentType = response.headers.get('content-type');
        if (response.ok && contentType?.includes('text/event-stream')) {
          return;
        } else if (contentType?.includes('application/json')) {
          const data = await response.json();
          if (data.error) {
            throw new Error(`\${data.error.type}: \${data.error.message}`);
          } else if(data.detail){
			 throw new Error(`\${data.detail}`);
		  }
        }
      } else {
        throw new Error('Failed to communicate with the ChatGPT API');
      }
    };

    const onmessage = (ev) => {
      const data = ev.data;
      if (data !== '[DONE]') {
        const parsedData = JSON.parse(data);
        const firstChoice = parsedData?.choices[0];
        const message = firstChoice?.delta?.content;
        if (message) {
			if (!hasMarkdown(message)) {
				streamMessage(message);
			}
        }
      }
    };

    const onerror = (error) => {
      throw error;
    };

    return gptFetchApi
    .then(fetchEventSource =>
      fetchEventSource(gpt_api_endurl, {
        ...openAiOptions,
        openWhenHidden: true,
        onopen,
        onmessage,
        onerror
      })
    )
    .then(async (response) => {
      if (response && !response.ok) {
        const data = await response.json();
        if (data.error) {
          throw new Error(`\${data.error.type}: \${data.error.message}`);
        }
      }
    })
    .catch(onerror);
  });
};
HTML;

}			
			if( @file_exists( ROOT_DIR . '/templates/'. $config['skin'].'/editor.css' ) ) {
				
					$editor_css = "templates/{$config['skin']}/editor.css?v={$config['cache_id']}";
					
			} else $editor_css = "engine/editor/css/content.css?v={$config['cache_id']}";
			
			$js_code = <<<HTML
<script>
var text_upload = "{$lang['bb_t_up']}";

setTimeout(function() {

	tinymce.remove('textarea.wysiwygeditor');
	
	tinyMCE.baseURL = dle_root + 'engine/editor/jscripts/tiny_mce';
	tinyMCE.suffix = '.min';

	var dle_theme = '{$dark_theme}';
	dle_theme = dle_theme.trim();

	if(dle_theme != '') {
		$('body').addClass( dle_theme );
	}

	{$image_upload[1]}
	{$chat_gpt[4]}

	tinymce.init({
		selector: 'textarea.wysiwygeditor',
		language : "{$lang['language_code']}",
		directionality: '{$lang['direction']}',
		element_format : 'html',		
		body_class: dle_theme,
		skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',

		width : "100%",
		height : 400,
		min_height: 50,
		max_height: 400,
		autoresize_bottom_margin: 1,
		statusbar: false,

		deprecation_warnings: false,
		promotion: false,
		cache_suffix: '?v={$config['cache_id']}',
		license_key: 'gpl',
		sandbox_iframes: false,
		
		plugins: "{$chat_gpt[0]}autoresize accordion fullscreen advlist autolink lists link image charmap anchor searchreplace visualblocks visualchars nonbreaking table codemirror dlebutton codesample quickbars autosave wordcount pagebreak toc",
		
		setup: function(editor) {
			editor.on('PreInit', function() {
				var shortEndedElements = editor.schema.getVoidElements();
				shortEndedElements['path'] = {};
				shortEndedElements['source'] = {};
				shortEndedElements['use'] = {};
			});
		},
		paste_postprocess: (editor, args) => {
			args.node.innerHTML = DLEclearPasteText(args.node.innerHTML, editor);
		},

		indentation : '20px',
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		verify_html: false,
		nonbreaking_force_tab: true,
		branding: false,
		link_default_target: '_blank',
		browser_spellcheck: true,
		pagebreak_separator: '{PAGEBREAK}',
		pagebreak_split_block: true,
		editable_class: 'contenteditable',
		noneditable_class: 'noncontenteditable',
		text_patterns: [],
		image_advtab: true,
		image_caption: true,
		image_dimensions: true,
		{$image_upload[2]}
		{$chat_gpt[3]}

		draggable_modal: true,
		menubar: false,
		
		toolbar: [
			'{$chat_gpt[1]}bold italic underline strikethrough align bullist numlist link unlink dleleech table {$image_upload[0]} {$implugin} dlemp dlaudio dletube dleemo dlequote dlehide dlespoiler codesample code dlemore',
			'fontformatting forecolor backcolor pastetext | outdent indent subscript superscript anchor accordion pagebreak dlepage hr charmap searchreplace toc dletypo visualblocks | restoredraft undo redo removeformat fullscreen'
		],
		
		toolbar_mode: 'floating',
		toolbar_groups: {
		  
		  fontformatting: {
			icon: 'change-case',
			tooltip: 'Formatting',
			items: 'blocks styles fontfamily fontsizeinput lineheight'
		  },
		  
		  align: {
			icon: 'align-center',
			tooltip: 'Formatting',
			items: 'alignleft aligncenter alignright alignjustify'
		  },
		  
		  dle: {
			icon: 'icon-dle',
			tooltip: 'DLE Tags',
			items: 'dlequote dlespoiler accordion dlehide codesample | pagebreak dlepage'
		  }
		  
		},
		
		statusbar: false,
		contextmenu: 'image table lists',

		block_formats: 'Tag (p)=p;Tag (div)=div;Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6;',
		style_formats: [
			{ title: 'Information Block', block: 'div', wrapper: true, styles: { 'color': '#333333', 'border': 'solid 1px #00897B', 'padding': '0.7rem', 'background-color': '#E0F2F1', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Warning Block', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #FF9800', 'padding': '0.7rem', 'background-color': '#FFF3E0', 'color': '#aa3510', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Error Block', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #FF5722', 'padding': '0.7rem', 'background-color': '#FBE9E7', 'color': '#9c1f1f', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Borders', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #ccc', 'padding': '0.7rem' } },
			{ title: 'Borders top and bottom', block: 'div', wrapper: true, styles: { 'border-top': 'solid 1px #ccc', 'border-bottom': 'solid 1px #ccc', 'padding': '10px 0' } },
			{ title: 'Use a shadow', block: 'div', wrapper: true, styles: { 'box-shadow': '0 5px 12px rgba(126,142,177,0.2)' } },
			{ title: 'Increased letter spacing', inline: 'span', styles: { 'letter-spacing': '1px' } },
			{ title: 'Сapital letters', inline: 'span', styles: { 'text-transform': 'uppercase' } },
			{ title: 'Gray background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#607D8B', 'padding': '0.7rem' } },
			{ title: 'Brown background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#795548', 'padding': '0.7rem' } },
			{ title: 'Blue background', block: 'div', wrapper: true, styles: { 'color': '#104d92', 'background-color': '#E3F2FD', 'padding': '0.7rem' } },
			{ title: 'Green background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#009688', 'padding': '0.7rem' } },
		],

		image_class_list: [
			{ title: 'None', value: '' },
			{ title: 'Image Border', value: 'image-bordered' },
			{ title: 'Image Shadow', value: 'image-shadows' },
			{ title: 'Image Padding', value: 'image-padded' },
			{ title: 'Borders Padding', value: 'image-bordered image-padded' },
			{ title: 'Shadow Padding', value: 'image-shadows image-padded' },
		],
		
		codesample_languages: [
			{ text: 'HTML/XML', value: 'markup' },
			{ text: 'JavaScript', value: 'javascript' },
			{ text: 'CSS', value: 'css' },
			{ text: 'PHP', value: 'php' },
			{ text: 'SQL', value: 'sql' },
			{ text: 'Ruby', value: 'ruby' },
			{ text: 'Python', value: 'python' },
			{ text: 'Java', value: 'java' },
			{ text: 'C', value: 'c' },
			{ text: 'C#', value: 'csharp' },
			{ text: 'C++', value: 'cpp' }
		],

		quickbars_insert_toolbar: false,
		quickbars_selection_toolbar: 'bold italic underline | quicklink | dlequote dlespoiler dlehide | forecolor backcolor | styles | blocks fontsizeinput',
		quickbars_image_toolbar: 'alignleft aligncenter alignright | image link',
  
		formats: {
		  bold: {inline: 'b'},  
		  italic: {inline: 'i'},
		  underline: {inline: 'u', exact : true},  
		  strikethrough: {inline: 's', exact : true}
		},
		
		toc_depth : 4,
		
		dle_root : dle_root,
		dle_upload_area : "short_story",
		dle_upload_user : "{$p_name}",
		dle_upload_news : "{$row['id']}",
		
		content_css : dle_root + "{$editor_css}"
	});

}, 100);

</script>
HTML;

		
	$params = "class=\"wysiwygeditor\"";
	$box_class = "wseditor dlefastedit-editor";

	if( $news_txt OR ($config['quick_edit_mode'] AND !$config['disable_short']) ) {
	
		$short_area = <<<HTML
<div class="xfieldsrow"><b>{$lang['s_fshort']}</b>
<div class="{$box_class}{$dark_theme}">
<textarea id="news_txt" name="news_txt" {$params}>{$news_txt}</textarea>
</div>
</div>
HTML;

	} else $short_area = '';

	if($full_txt OR ($config['quick_edit_mode'] AND !$config['disable_short']) ) {
	
		$full_area = <<<HTML
<div class="xfieldsrow"><b>{$lang['s_ffull']}</b>
<div class="{$box_class}{$dark_theme}">
<textarea id="full_txt" name="full_txt" {$params}>{$full_txt}</textarea>
</div>
</div>
HTML;

	} else $full_area = '';

	if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

	$buffer = <<<HTML
<script src="{$_ROOT_DLE_URL}engine/classes/js/sortable.js?v={$config['cache_id']}"></script>
<script src="{$_ROOT_DLE_URL}engine/classes/uploads/html5/plupload/plupload.full.min.js?v={$config['cache_id']}"></script>
<script src="{$_ROOT_DLE_URL}engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js?v={$config['cache_id']}"></script>
<script src="{$_ROOT_DLE_URL}engine/classes/calendar/calendar.js?v={$config['cache_id']}"></script>
<link href="{$_ROOT_DLE_URL}engine/classes/calendar/calendar.css?v={$config['cache_id']}" rel="stylesheet" type="text/css">
<form name="ajaxnews{$id}" id="ajaxnews{$id}" metod="post" action="">
<div><input type="text" name="title" class="quick-edit-text" value="{$row['title']}"></div>
<div class="xfieldsrow"><div class="xfieldscolleft">{$lang['ajax_edit_cat']}</div><div class="xfieldscolright">{$cats}</div></div>
{$short_area}
{$full_area}
{$xfbuffer}
<div class="xfieldsrow"><div class="xfieldscolleft">{$lang['reason']}</div><div class="xfieldscolright"><input type="text" name="reason" class="quick-edit-text" value="{$row['reason']}"></div></div>
<div class="xfieldsrow"><label class="form-check-label"><input class="form-check-input" type="checkbox" name="approve" value="1" {$fix_approve}><span>{$lang['add_al_ap']}</span></label></div>
</form>
{$js_code}
{$categoryfilter}
<script>
setTimeout(function() {
	$(".xfieldsrow textarea.quick-edit-textarea").each(function () {
		this.style.height = "auto";
		if (this.scrollHeight > 300) {
			this.style.height = "300px";
			this.style.overflowY = "auto";
		} else {
			this.style.height = this.scrollHeight + "px";
			this.style.overflowY = "hidden";
		}
	}).on("input", function () {
		this.style.height = "auto";
		if (this.scrollHeight > 300) {
			this.style.height = "300px";
			this.style.overflowY = "auto";
		} else {
			this.style.height = this.scrollHeight + "px";
			this.style.overflowY = "hidden";
		}
	});
}, 100);

    var elemfont = document.createElement('i');
    elemfont.className = 'mediaupload-icon';
	elemfont.style.position = 'absolute';
	elemfont.style.left = '-9999px';
	document.body.appendChild(elemfont);

	if ($( elemfont ).css('font-family') !== 'mediauploadicons') {
		$('head').append('<link rel="stylesheet" type="text/css" href="' + dle_root +'engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css">');
	}

    document.body.removeChild(elemfont);
	
	function xfimagedelete( xfname, xfvalue ) {
		
		DLEconfirmDelete( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post(dle_root + 'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'images[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
				$('#xfupload_' + xfname + ' .qq-upload-button').removeAttr('disabled');
				
				if (typeof file_uploaders[xfname] !== 'undefined') {
					file_uploaders[xfname].disableBrowse(false);
					file_uploaders[xfname].refresh();
				}
				
				$('#mediaupload').remove();
				
			});
			
		} );

		return false;

	};
	function xffiledelete( xfname, xfvalue ) {
		
		DLEconfirmDelete( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post(dle_root + 'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'files[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
				$('#xf_'+xfname).hide('');
				$('#xfupload_' + xfname + ' .qq-upload-button').removeAttr('disabled');
				
				if (typeof file_uploaders[xfname] !== 'undefined') {
					file_uploaders[xfname].disableBrowse(false);
					file_uploaders[xfname].refresh();
				}
				
				$('#mediaupload').remove();
			});
			
		} );

		return false;

	};
	
	function xfaddalt( id, xfname ) {
	
		var sel_alt = $('#xf_'+id).data('alt').toString().trim();
		sel_alt = sel_alt.replace(/"/g, '&quot;');
		sel_alt = sel_alt.replace(/'/g, '&#039;');

		DLEprompt('{$lang['bb_descr']}', sel_alt, '{$lang['p_prompt']}', function (r) {
			r = r.replace(/</g, '');
			r = r.replace(/>/g, '');
			r = r.replaceAll(',', '&#44;');
			r = r.replaceAll('|', '&#124;');
			
			$('#xf_'+id).data('alt', r);
			xfsinc(xfname);
		
		}, true);
		
	};
	
	function xfsinc(xfname) {
	
		var order = [];
		
		$( '#uploadedfile_' + xfname + ' .uploadedfile' ).each(function() {
			var xfurl = $(this).data('id').toString().trim();
			var xfalt = $(this).data('alt').toString().trim();
			
			if(xfalt) {
				order.push(xfalt + '|'+ xfurl);
			} else {
				order.push(xfurl);
			}

		});
	
		$('#xf_' + xfname).val(order.join(','));
	};

	function save_edit_alert() {

		$.post( dle_root + "engine/ajax/controller.php?mod=adminfunction", { 'id': '{$row['id']}', action: 'saveeditnews', user_hash: '{$dle_login_hash}' }, function(data){

			if (data.success) {
			
				setTimeout(function() {
					save_edit_alert();
				}, 20000);
				
			}
			
		}, "json");

	};

	onCategoryChange($('#edit_category_list'));

	{$edit_alert}
	{$save_edit_alert}	
</script>	
HTML;

} elseif( $_REQUEST['action'] == "save" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}
	
	$row = $db->super_query( "SELECT id, date, xfields, title, category, approve, short_story, full_story, autor, alt_name FROM " . PREFIX . "_post where id = '{$id}'" );
	
	if( $id != $row['id'] ) die( "News Not Found" );

	if (!isset($_POST['catlist']) OR (isset($_POST['catlist']) AND !is_array($_POST['catlist']))) $_POST['catlist'] = array();

	if (!count($_POST['catlist'])) {
		$catlist = array();
		$catlist[] = '0';
	} else $catlist = $_POST['catlist'];

	$category_list = array();

	foreach ($catlist as $value) {
		$category_list[] = intval($value);
	}

	$catlist = $category_list;
	$category_list = $db->safesql(implode(',', $category_list));

	if( $config['allow_alt_url'] ) {
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
			if( intval( $category_list ) and $config['seo_type'] == 2 ) {
				$full_link = $config['http_home_url'] . get_url( intval( $category_list ) ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
			} else {
				$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $row['date'] ) ) . $row['alt_name'] . ".html";
		}
	} else {
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
	}
	
	$have_perm = 0;
	
	if ($member_id['cat_add']) $allow_list = explode(',', $member_id['cat_add']);
	else $allow_list = explode(',', $user_group[$member_id['user_group']]['cat_add']);

	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$have_perm = 1;
		
		foreach ( $catlist as $selected ) {
			if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) ) $have_perm = 0;
		}
	}
	
	if( $user_group[$member_id['user_group']]['allow_edit'] and $row['autor'] == $member_id['name'] ) {
		$have_perm = 1;
	}
	
	if( $user_group[$member_id['user_group']]['max_edit_days'] ) {
		$newstime = strtotime( $row['date'] );
		$maxedittime = $_TIME - ($user_group[$member_id['user_group']]['max_edit_days'] * 3600 * 24);
		if( $maxedittime > $newstime ) $have_perm = 0;
	}
	
	if( ($member_id['user_group'] == 1) ) {
		$have_perm = 1;
	}
	
	if( !$have_perm ) die( "Access it is refused" );

	$approve = isset(  $_REQUEST['approve'] ) ? intval(  $_REQUEST['approve'] ) : 0;

	if( !$user_group[$member_id['user_group']]['moderation'] ) $approve = 0;
	
	if ($user_group[$member_id['user_group']]['moderation']) {
		foreach ($catlist as $selected) {
			if ($allow_list[0] != "all" and !in_array($selected, $allow_list)) {
				$approve = 0;
			}
		}
	}

	$_POST['title'] = $db->safesql( $parse->process( trim( strip_tags ($_POST['title'] ) ) ) );

	$parse->allow_code = false;

	$news_txt = isset($_POST['news_txt']) ? $db->safesql($parse->BB_Parse( $parse->process( $_POST['news_txt'] ))) : '';
	$full_txt = isset($_POST['full_txt']) ?  $db->safesql($parse->BB_Parse( $parse->process( $_POST['full_txt'] ))) : '';

	$add_module = "yes";
	$ajax_edit = "yes";
	$stop = "";
	$category = $catlist;
	$xf_existing = xfieldsdataload($row['xfields']);
	$xfieldsaction = "init";
	include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));

	$editreason = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['reason'] ) ) ), ENT_QUOTES, 'UTF-8' ) );
	
	if( $editreason != "" ) $view_edit = 1;
	else $view_edit = 0;
	$added_time = time();
	
	if( !trim($_POST['title']) ) die( $lang['add_err_7'] );

	if ($parse->not_allowed_text ) die( $lang['news_err_39'] );

	if($stop) die($stop);

	$db->query( "UPDATE " . PREFIX . "_post SET title='{$_POST['title']}', short_story='{$news_txt}', full_story='{$full_txt}', xfields='{$filecontents}', category='{$category_list}', approve='{$approve}', allow_br='0' WHERE id = '{$id}'" );
	$db->query( "UPDATE " . PREFIX . "_post_extras SET editdate='$added_time', editor='{$member_id['name']}', reason='$editreason', view_edit='$view_edit' WHERE news_id = '{$id}'" );

	$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$id}'" );

	if ( count($xf_search_words) AND $approve ) {
					
		$temp_array = array();
					
		foreach ( $xf_search_words as $value ) {
						
			$temp_array[] = "('" . $id . "', '" . $value[0] . "', '" . $value[1] . "')";
		}
					
		$xf_search_words = implode( ", ", $temp_array );
		$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
	}

	if( $category_list != $row['category'] OR $approve != $row['approve'] ) {
		
		$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '{$id}'" );

		if($approve) {

			$cat_ids = array ();
	
			$cat_ids_arr = explode( ",",  $category_list );
	
			foreach ( $catlist as $value ) {
	
				$cat_ids[] = "('" . $id . "', '" . trim( $value ) . "')";
	
			}
	
			$cat_ids = implode( ", ", $cat_ids );
			$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );

		}

	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '25', '{$_POST['title']}')" );

	if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$id;

	clear_cache(array('news_', $cprefix, 'related_', 'rss', 'stats'));

	if( $config['news_indexnow'] AND ($approve OR (!$approve AND $approve != $row['approve'] ) ) ) {

		DLESEO::IndexNow( $full_link );

	}

	$buffer = "ok";

} else die( "error" );

$db->close();

echo $buffer;
?>