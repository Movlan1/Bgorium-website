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
 File: cmoderation.php
-----------------------------------------------------
 Use: comments moderation
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_comments'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'], "?mod=main" );
}

if( $action == "mass_approve" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( !$_POST['selected_comments'] OR !is_array($_POST['selected_comments'])) {
		msg( "error", $lang['mass_error'], $lang['mass_acomm'], "?mod=cmoderation" );
	}

	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		
		$row = $db->super_query("SELECT id, post_id, approve FROM " . PREFIX . "_comments WHERE id = '{$c_id}'");

		if ( !isset($row['id']) OR $row['approve']) {
			continue;
		}

		$post_id = intval($row['post_id']);

		$db->query( "UPDATE " . PREFIX . "_comments SET approve='1' WHERE id='{$c_id}'" );
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$post_id}'" );

		if ( $config['allow_subscribe'] ) {
	
			$row = $db->super_query( "SELECT autor, text, parent FROM " . PREFIX . "_comments WHERE id = '{$c_id}'" );
	
			$name = $row['autor'];
			$body = $row['text'];
			$parent = $row['parent'];
			
			$row = $db->super_query( "SELECT id, short_story, title, date, alt_name, category FROM ".PREFIX."_post WHERE id = '{$post_id}'" );
	
			$row['date'] = strtotime( $row['date'] );
			$row['category'] = intval( $row['category'] );
	
			if( $config['allow_alt_url'] ) {
					
				if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
					if( $row['category'] and $config['seo_type'] == 2 ) {
						
						$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					} else {
						
						$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					}
					
				} else {
					
					$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
				}
				
			} else {
					
				$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
				
			}
		
			$title = stripslashes($row['title']);
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='comments' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html'] );
	
			if (strpos($full_link, "//") === 0) $full_link = "http:".$full_link;
			elseif (strpos($full_link, "/") === 0) $full_link = "http://".$_SERVER['HTTP_HOST'].$full_link;
			
			if( !$langformatdatefull ) $langformatdatefull = "d.m.Y H:i";
	
			$row['template'] = stripslashes( $row['template'] );
			$row['template'] = str_replace( "{%username%}", $name, $row['template'] );
			$row['template'] = str_replace( "{%date%}", langdate( $langformatdatefull, $_TIME, true ), $row['template'] );
			$row['template'] = str_replace( "{%link%}", $full_link, $row['template'] );
			$row['template'] = str_replace( "{%title%}", $title, $row['template'] );
	
			$body = str_replace( '\n', "", $body );
			$body = str_replace( '\r', "", $body );
				
			$body = stripslashes( stripslashes( $body ) );
			$body = str_replace( "<br />", "\n", $body );
			$body = strip_tags( $body );
				
			if( $row['use_html'] ) {
				$body = str_replace("\n", "<br>", $body );
			}
						
			$row['template'] = str_replace( "{%text%}", $body, $row['template'] );
			$row['template'] = str_replace( "{%ip%}", "--", $row['template'] );
			
			if (strpos($config['http_home_url'], "//") === 0) $slink = "https:".$config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
			else $slink = $config['http_home_url'];
			
			$found_news_author_subscribe = false;
			$found_reply_author_subscribe = false;
			
			$news_author_subscribe = $db->super_query( "SELECT " . USERPREFIX . "_users.user_id, " . USERPREFIX . "_users.name, " . USERPREFIX . "_users.email, " . USERPREFIX . "_users.news_subscribe FROM " . PREFIX . "_post_extras LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_post_extras.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_post_extras.news_id='{$post_id}'" );
		
			if( $parent ) {
				
				$reply_author_subscribe = $db->super_query( "SELECT " . USERPREFIX . "_users.user_id, " . USERPREFIX . "_users.name, " . USERPREFIX . "_users.email, " . USERPREFIX . "_users.comments_reply_subscribe FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.id='{$parent}'" );
				
			} else $reply_author_subscribe = array();
			
			if( !$parent ) {
				
				$db->query( "SELECT user_id, name, email, hash FROM " . PREFIX . "_subscribe WHERE news_id='{$post_id}'" );
		
				while($rec = $db->get_row())
				{
					if( $rec['user_id'] == $news_author_subscribe['user_id'] ) {
						$found_news_author_subscribe = true;
					}
						
					if( $parent AND $rec['user_id'] == $reply_author_subscribe['user_id'] ) {
						$found_reply_author_subscribe = true;
					}
						
					if ($rec['user_id'] != $member_id['user_id'] ) {
						
						$body = str_replace( "{%username_to%}", $rec['name'], $row['template'] );
						$body = str_replace( "{%unsubscribe%}", $slink . "index.php?do=unsubscribe&post_id=" . $post_id . "&user_id=" . $rec['user_id'] . "&hash=" . $rec['hash'], $body );
						$mail->send( $rec['email'], $lang['mail_comments'], $body );
		
					}
		
				}
				
			}
			
			if($news_author_subscribe['news_subscribe'] AND !$found_news_author_subscribe) {
				
				$body = str_replace( "{%username_to%}", $news_author_subscribe['name'], $row['template'] );
				
				if ($config['allow_alt_url']) {
					$body = str_replace( "{%unsubscribe%}", $slink . "user/" . urlencode ( $news_author_subscribe['name'] ) . "/", $body );
				} else {
					$body = str_replace( "{%unsubscribe%}", $slink . "index.php?subaction=userinfo&user=" . urlencode ( $news_author_subscribe['name'] ), $body );
				}
				
				$mail->send( $news_author_subscribe['email'], $lang['mail_comments'], $body );
				
				$last_send = $news_author_subscribe['user_id'];
				
			} else $last_send = false;
			
			if($parent AND $reply_author_subscribe['comments_reply_subscribe'] AND !$found_reply_author_subscribe AND $reply_author_subscribe['user_id'] != $last_send) {
				
				$body = str_replace( "{%username_to%}", $reply_author_subscribe['name'], $row['template'] );
				
				if ($config['allow_alt_url']) {
					$body = str_replace( "{%unsubscribe%}", $slink . "user/" . urlencode ( $reply_author_subscribe['name'] ) . "/", $body );
				} else {
					$body = str_replace( "{%unsubscribe%}", $slink . "index.php?subaction=userinfo&user=" . urlencode ( $reply_author_subscribe['name'] ), $body );
				}
				
				$mail->send( $reply_author_subscribe['email'], $lang['mail_comments'], $body );
			}
			
			$db->free();
		}
	
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );

	clear_cache(array('news_', 'comm_', 'full_', 'stats'));
	
	msg( "success", $lang['mass_head'], $lang['mass_approve_ok'], "?mod=cmoderation" );

}

if( $action == "mass_delete" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] OR !is_array($_POST['selected_comments']) ) {
		msg( "error", $lang['mass_error'], $lang['mass_dcomm'], "?mod=cmoderation" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		deletecomments( $c_id );
	
	}

	clear_cache(array('news_', 'comm_', 'full_', 'stats'));
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	msg( "success", $lang['mass_head'], $lang['mass_delokc'], "?mod=cmoderation" );

}

if ($config['allow_comments_wysiwyg']) {
	
	$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";
	
}

echoheader( "<i class=\"fa fa-file-text-o position-left\"></i><span class=\"text-semibold\">{$lang['header_c_1']}</span>", $lang['header_c_2'] );

$entries = "";
$files = array();

$where = array ( PREFIX . "_comments.approve = '0'");
	
if(isset($_REQUEST['search_field']) AND $_REQUEST['search_field']) {
	
	$search_field = $db->safesql( addslashes(addslashes(trim( urldecode( $_REQUEST['search_field'] ) ) ) ) );
	$search_field = preg_replace('/\s+/u', '%', $search_field);
	
	$search_field2 = $db->safesql(trim( htmlspecialchars( urldecode( $_REQUEST['search_field'] ), ENT_QUOTES, 'UTF-8'  ) ) );
	$search_field2 = preg_replace('/\s+/u', '%', $search_field2);
	
	$where[] = "(".PREFIX ."_comments.text like '%{$search_field}%' OR ".PREFIX."_comments.text like '%{$search_field2}%')";
	
	$search_field = trim( htmlspecialchars( urldecode( $_REQUEST['search_field'] ), ENT_QUOTES, 'UTF-8' ) );
	
} else $search_field = "";

$where = implode( " AND ", $where );

$start_from = isset($_GET['start_from']) ? intval( $_GET['start_from'] ) : 0;
if( $start_from < 0 ) $start_from = 0;
$news_per_page = 20;
$i = $start_from;

$gopage = isset($_GET['gopage']) ? intval( $_GET['gopage'] ) : 0;
if( $gopage > 0 ) $start_from = ($gopage - 1) * $news_per_page;
	
$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE {$where}" );

$db->query( "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.date, " . PREFIX . "_comments.autor, " . PREFIX . "_comments.email, text, ip, is_register, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE {$where} ORDER BY " . PREFIX . "_comments.date DESC LIMIT $start_from, $news_per_page" );

while ( $row = $db->get_array() ) {

	$files[] = $row['id'];

	$row['text'] = "<div id='comm-id-" . $row['id'] . "'>" . stripslashes( $row['text'] ) . "</div>";
	$row['newsdate'] = strtotime( $row['newsdate'] );
	$row['date'] = strtotime( $row['date'] );
	if( !$langformatdatefull ) $langformatdatefull = "d.m.Y H:i:s";
	$date = date( $langformatdatefull, $row['date'] );
	
	if( $config['allow_alt_url'] ) {
		
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
			
			if( intval( $row['category'] ) and $config['seo_type'] == 2 ) {
				
				$full_link = $config['http_home_url'] . get_url( intval( $row['category'] ) ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
			
			} else {
				
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['newsdate'] ) . $row['alt_name'] . ".html";
		}
	
	} else {
		
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}
	
	$news_title = "<a href=\"" . $full_link . "\"  target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";
	
	if ($row['is_register']) {

		$row['autor'] = "<strong class=\"position-left\"><a href=\"?mod=editusers&action=edituser&user=" . urlencode($row['autor']) . "\" target=\"_blank\">{$row['autor']}</a></strong>";
	
	} else {

		if ($row['email']) {
			$row['email'] = " (<a href=\"mailto:{$row['email']}\" target=\"_blank\">{$row['email']}</a>)";
		}

		$row['autor'] = "<span class=\"position-left\">{$lang['com_gast']} {$row['autor']}{$row['email']}</span>";
	}

	$row['ip'] = "<a href=\"?mod=blockip&ip=".urlencode($row['ip'])."\" target=\"_blank\">{$row['ip']}</a>";

	$entries .= <<<HTML
<div id='table-comm-{$row['id']}' class="panel panel-default">
  <div class="panel-heading">
    <span class="label label-info position-left">{$lang['edit_autor']}</span>{$row['autor']}IP: {$row['ip']} {$lang['cmod_n_title']} {$news_title}
	<div class="heading-elements">
		<div class="checkbox checkbox-right"><label><input name="selected_comments[]" value="{$row['id']}" type="checkbox" class="icheck"></label></div>
	</div>
  </div>
  <div class="panel-body">
  {$row['text']}
  {uploaded files="{$row['id']}"}
  </div>
  <div class="panel-footer">
    <button id="save-button-{$row['id']}" onclick="public_comm('{$row['id']}', '{$row['post_id']}'); return false;" type="button" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-check-square-o position-left"></i>{$lang['bb_b_approve']}</button>
	<button onclick="ajax_comm_edit('{$row['id']}'); return false;" type="button" class="btn  bg-teal btn-sm btn-raised position-left"><i class="fa fa-pencil-square-o position-left"></i>{$lang['group_sel1']}</button>
	<button onclick="MarkSpam('{$row['id']}'); return false;" type="button" class="btn bg-brown-600 btn-sm btn-raised position-left"><i class="fa fa-minus-circle position-left"></i>{$lang['btn_spam']}</button>
	<button onclick="DeleteComments('{$row['id']}'); return false;" type="button" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-trash-o position-left"></i>{$lang['edit_dnews']}</button>
	<span class="pull-right" style="margin-top: 4px;"><i class="fa fa-clock-o position-left"></i>{$date}</span>
  </div>
</div>
<input type="hidden" name="post_id[{$row['id']}]" value="{$row['post_id']}">
HTML;

}

$db->free();

$images_found = false;

if (count($files)) {

	$find_files = implode(',', $files);
	$ids = array();
	$sql_result = $db->query("SELECT id, c_id, name, author FROM " . PREFIX . "_comments_files WHERE c_id IN ({$find_files})");

	while ($row = $db->get_row($sql_result)) {

		$ids[$row['c_id']]['uploaded_images'][] = array('id' => $row['id'], 'c_id' => $row['c_id'],  'file' => $row['name'], 'author' => urlencode($row['author']));
	}

	foreach ($files as $file) {

		$uploaded_list = array();

		if (isset($ids[$file]['uploaded_images']) and is_array($ids[$file]['uploaded_images']) and count($ids[$file]['uploaded_images'])) {

			$images_found = true;

			foreach ($ids[$file]['uploaded_images'] as $temp_value) {

				$image = get_uploaded_image_info($temp_value['file'], 'posts',  true);

				$img_url =  $image->url;
				$size = $image->size;
				$dimension = $image->dimension;

				if ($image->medium) {

					$img_url = $image->medium;
				}

				if ($image->thumb) {

					$img_url = $image->thumb;
				}

				if ($size) $size = "({$size})";

				$file_name = explode("_", $image->name);

				if (count($file_name) > 1 AND strlen($file_name[0]) == 10) unset($file_name[0]);

				$file_name = implode("_", $file_name);
				$base_name = pathinfo($file_name, PATHINFO_FILENAME);
				$file_type = explode(".", $file_name);
				$file_type = totranslit(end($file_type));

				$uploaded_list[] = <<<HTML
<div class="file-preview-card uploadedfile" data-type="image" data-cid="{$temp_value['c_id']}" data-deleteid="{$temp_value['id']}" data-author="{$temp_value['author']}">
	<div class="file-content select-disable">
		<div class="file-ext">{$file_type}</div>
		<a href="{$image->url}" data-highslide="single" rel="tooltip" title="{$lang['up_im_expand']}" target="_blank"><img src="{$img_url}" class="file-preview-image"></a>
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$image->name}">{$base_name}</div>
			<div class="file-size-info">{$dimension} {$size}</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-delete"><a class="comments-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;
			}
		}

		if (count($uploaded_list)) $uploaded_list = "<div class=\"qq-uploader\" style=\"padding-top:5px;\">" . implode("", $uploaded_list) . "</div>";
		else $uploaded_list = "";

		$entries = str_ireplace("{uploaded files=\"{$file}\"}", $uploaded_list, $entries);
	}
}

// pagination

$npp_nav = "";

if( $start_from > 0 ) {
	$previous = $start_from - $news_per_page;
	$npp_nav .= "<li><a href=\"?mod=cmoderation&start_from={$previous}&search_field={$search_field}\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a></li>";
}

if( $result_count['count'] > $news_per_page ) {
	
	$enpages_count = @ceil( $result_count['count'] / $news_per_page );
	$enpages_start_from = 0;
	$enpages = "";
	
	if( $enpages_count <= 10 ) {
		
		for($j = 1; $j <= $enpages_count; $j ++) {
			
			if( $enpages_start_from != $start_from ) {
				
				$enpages .= "<li><a href=\"?mod=cmoderation&start_from={$enpages_start_from}&search_field={$search_field}\">$j</a></li>";
			
			} else {
				
				$enpages .= "<li class=\"active\"><span>$j</span></li>";
			}
			
			$enpages_start_from += $news_per_page;
		}
		
		$npp_nav .= $enpages;
	
	} else {
		
		$start = 1;
		$end = 10;
		
		if( $start_from > 0 ) {
			
			if( ($start_from / $news_per_page) > 4 ) {
				
				$start = @ceil( $start_from / $news_per_page ) - 3;
				$end = $start + 9;
				
				if( $end > $enpages_count ) {
					$start = $enpages_count - 10;
					$end = $enpages_count - 1;
				}
				
				$enpages_start_from = ($start - 1) * $news_per_page;
			
			}
		
		}
		
		if( $start > 2 ) {
			
			$enpages .= "<li><a href=\"?mod=cmoderation&start_from=0&search_field={$search_field}\">1</a></li> <li><span>...</span></li>";
		
		}
		
		for($j = $start; $j <= $end; $j ++) {
			
			if( $enpages_start_from != $start_from ) {
				
				$enpages .= "<li><a href=\"?mod=cmoderation&start_from={$enpages_start_from}&search_field={$search_field}\">$j</a></li>";
			
			} else {
				
				$enpages .= "<li class=\"active\"><span>$j</span></li>";
			}
			
			$enpages_start_from += $news_per_page;
		}
		
		$enpages_start_from = ($enpages_count - 1) * $news_per_page;
		$enpages .= "<li><span>...</span></li><li><a href=\"?mod=cmoderation&start_from={$enpages_start_from}&search_field={$search_field}\">$enpages_count</a></li>";
		
		$npp_nav .= $enpages;
	
	}

	if( $result_count['count'] > $i ) {
		$how_next = $result_count['count'] - $i;
		if( $how_next > $news_per_page ) {
			$how_next = $news_per_page;
		}
		$npp_nav .= "<li><a href=\"?mod=cmoderation&start_from={$i}&search_field={$search_field}\" title=\"{$lang['edit_next']}\">&gt;&gt;</a></li>";
	}
	
	$npp_nav = "<div class=\"pull-left\"><ul class=\"pagination pagination-sm\">".$npp_nav."</ul></div>";
}		
// pagination


echo <<<HTML
<style type="text/css">
	.bb-editor textarea {
		transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
		color: #000;
		padding: 0.188rem 0.313rem 0.188rem 0.313rem;
		border: 1px solid #a7a6a6ab;
		display: inline-block;
		background: #ffffff;
		font-size: .9rem;
		border-radius: 0.3rem;
		margin-bottom: 10px;
	}
	.wseditor {
		border-top: 5px solid #0c5f7e;
		border-top-left-radius: 2px;
		border-top-right-radius: 2px;
		margin-bottom: 10px;
	}
	.editor-style-light .wseditor {
		border-top: none;
	}
	.bbcodes {
		display:inline-block;
		margin-bottom:0;
		font-size: .9rem;
		font-weight: 400;
    	line-height: 1.6;
   		padding: 0.188rem 0.75rem;
		cursor:pointer;
		border: 1px solid transparent;
		background-color: #009688;
        color: #fff;
		text-shadow: 1px 1px 2px rgba(51, 51, 51, .5);
		border-radius: .3rem;
		vertical-align: top;
		white-space:nowrap;
		outline:0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
        transition: all ease-in-out 0.15s;

	}

	.bbcodes:first-child {
	  background-color: #767676;
	  border-color: #767676;
	  margin-right: 0.5rem;
	}

	.bbcodes:hover {
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
	}
</style>
<script>
<!--

var c_cache = [];
var dle_root = '';
var dle_prompt = '{$lang['p_prompt']}';
var dle_wysiwyg    = {$config['allow_comments_wysiwyg']};

function setNewField(which, formname)
{
	if (which != selField)
	{
		fombj    = formname;
		selField = which;

	}
};

function ajax_comm_edit( c_id )
{

	for (var i = 0, length = c_cache.length; i < length; i++) {
	    if (i in c_cache) {
			if ( c_cache[ i ] !== '' )
			{
				ajax_cancel_comm_edit( i );
			}
	    }
	}

	if ( ! c_cache[ c_id ] || c_cache[ c_id ] === '' )
	{
		c_cache[ c_id ] = $('#comm-id-'+c_id).html();
	}

	ShowLoading('');

	$.get("engine/ajax/controller.php?mod=editcomments", { id: c_id, area: 'news', mode: "adminpanel", action: "edit" }, function(data){

		HideLoading('');

		$('#comm-id-'+c_id).html(data);

		setTimeout(function() {
           $("html,body").stop().animate({scrollTop: $("#comm-id-" + c_id).offset().top - 70}, 700);
        }, 100);

	}, 'html');
	return false;
};

function DLEPasteSafeText(args, allow_url) {

	if (typeof args.node.innerHTML != "undefined" ) { 
		var text = args.node.innerHTML;
		
		if (allow_url ) {
			var existingLinks = [];
			text = text.replace(/<a[^>]*?href=["'](https?:\/\/[^\s<]+)["'][^>]*?>.*?<\/a>/gi, match => {
				existingLinks.push(match);
				var index = existingLinks.length - 1;
				return '__LINK' + index + '__';
			});

			text = text.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank">$1</a>');

			existingLinks.forEach((link, index) => {
				text = text.replace('__LINK' + index + '__', link);
			});
		}
	
		args.node.innerHTML = text;
	}

	return args;
};

function ajax_cancel_comm_edit( c_id ) {
	if ( c_cache[ c_id ] != "" )
	{
		$("#comm-id-"+c_id).html(c_cache[ c_id ]);
	}

	c_cache[ c_id ] = '';

	return false;
};

function ajax_save_comm_edit( c_id, area ) {

	if (dle_wysiwyg) {

		tinyMCE.triggerSave();

	}

	var comm_txt = $('#dleeditcomments'+c_id).val();

	if ( $('#c_edit_autor' + c_id).val() ) {
		var c_autor = $('#c_edit_autor' + c_id).val();
	} else {
		var c_autor = '';
	}

	ShowLoading('');

	$.post("engine/ajax/controller.php?mod=editcomments", { id: c_id, name: c_autor, comm_txt: comm_txt, area: area, action: "save", user_hash: "{$dle_login_hash}" }, function(data){

		HideLoading('');
		if (data.success) {

			c_cache[ c_id ] = '';
			$('#table-comm-'+c_id).remove();

		} else if (data.error) {

			DLEPush.error(data.message);
		}

	}, "json" );
	return false;
	
};

function public_comm( c_id, post_id ) {

	ShowLoading('');

	$.post('engine/ajax/controller.php?mod=adminfunction', { id: c_id, post_id:post_id, action: "commentspublic", user_hash: '{$dle_login_hash}' }, function(data){
	
		HideLoading('');
		$('#table-comm-'+c_id).remove();
	
	});

	return false;
};

function DeleteComments(id) {

    DLEconfirmDelete( '{$lang['d_c_confirm']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/controller.php?mod=deletecomments", { id: id, dle_allow_hash: '{$dle_login_hash}' }, function(r){
	
			HideLoading('');
			$('#table-comm-'+id).remove();
	
		});

	} );

};
function MarkSpam(id) {

    DLEconfirm( '{$lang['mark_spam_c']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: 'commentsspam', user_hash: '{$dle_login_hash}' }, function(data){
	
			HideLoading('');
	
			if (data != "error") {
	
				location.reload(true);
	
			}
	
		});

	} );

};
function ckeck_uncheck_all() {
    var frm = document.dlemasscomments;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('.panel').find('.panel-body').removeClass('warning'); }
            else{ elmnt.checked=true; $(elmnt).parents('.panel').find('.panel-body').addClass('warning'); }
        }
    }
    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
	
	$.uniform.update();
	
	return false;
};
$(function() {
    $('.heading-elements input[type=checkbox]').on('change', function() {
        if($(this).is(':checked')) {
            $(this).parents('.panel').find('.panel-body').addClass('warning');
        }
        else {
            $(this).parents('.panel').find('.panel-body').removeClass('warning');
        }
    });
});
//-->
</script>
<form action="" method="post" name="dlemasscomments" id="dlemasscomments">
<input type="hidden" name="mod" value="cmoderation">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="panel panel-flat">
	<div class="panel-heading">
		<div class="has-feedback width-350">
			<input name="search_field" type="search" dir="auto" class="form-control" placeholder="{$lang['search_field']}" value="{$search_field}">
			<div class="form-control-feedback">
			    <a href="#" onclick="$(this).closest('form').submit(); return false;"><i class="fa fa-search text-size-base text-muted"></i></a>
			</div>
		</div>
		<div class="heading-elements">
			<div class="checkbox checkbox-right"><label><input name="master_box" id="master_box" type="checkbox" class="icheck" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all();">{$lang['edit_selall']}</label></div>
		</div>
	</div>
</div>
{$entries}
{$npp_nav}
<div class="pull-right">
	<select class="uniform" name="action">
	<option value="">{$lang['edit_selact']}</option>
	<option value="mass_approve">{$lang['bb_b_approve']}</option>
	<option value="mass_delete">{$lang['edit_seldel']}</option>
	</select> <input class="btn bg-slate-600 btn-sm btn-raised position-right" type="submit" value="{$lang['b_start']}" />
</div>
</form>
HTML;

if ($images_found) {

	if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

	echo <<<HTML
		<script>
			var elemfont = document.createElement('i');
			elemfont.className = 'mediaupload-icon';
			elemfont.style.position = 'absolute';
			elemfont.style.left = '-9999px';
			document.body.appendChild(elemfont);

			if ($( elemfont ).css('font-family') !== 'mediauploadicons') {
				$('head').append('<link rel="stylesheet" type="text/css" href="{$config['http_home_url']}engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css">');
			}

			document.body.removeChild(elemfont);

			if (typeof Fancybox == "undefined" ) {
				$.getCachedScript( dle_root + 'engine/classes/fancybox/fancybox.js?v={$config['cache_id']}');
			}

			$(document).off("click", '.file-preview-card .comments-delete-link');
			$(document).on("click", '.file-preview-card .comments-delete-link',	function(e){
				e.preventDefault();
				comment_delete_file( $(this).closest('.file-preview-card') );
				
				return false;
			});

			function comment_delete_file( file ) {

				DLEconfirmDelete( '{$lang['file_delete']}', '{$lang['p_info']}', function () {

					var formData = new FormData();
					formData.append('subaction', 'deluploads');
					formData.append('user_hash', '{$dle_login_hash}');
					formData.append('area', 'comments');
					formData.append('news_id', file.data('cid') );
					formData.append('author', file.data('author') );
					formData.append('comments_files[]', file.data('deleteid') );

					ShowLoading('');
				
					$.ajax({
						url: dle_root + "engine/ajax/controller.php?mod=upload",
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

								$('#mediaupload').remove();

							} else {
								DLEPush.error(data.error);
							}

						}
					});
					
					return false;
					
				} );
				
				return false;
			};

		</script>
HTML;
}

	if( strpos ( $entries, "dleplyrplayer" ) !== false ) {
		
		if( strpos ( $entries, ".m3u8" ) !== false ) {

echo <<<HTML
<script src="{$config['http_home_url']}engine/classes/html5player/hls.js"></script>
HTML;
		}
		
		echo <<<HTML
		<link href="{$config['http_home_url']}engine/classes/html5player/plyr.css" rel="stylesheet" type="text/css">
		<script src="{$config['http_home_url']}engine/classes/html5player/plyr.js"></script>
HTML;

	}

	if ($config['allow_comments_wysiwyg']) {
	
		echo <<<HTML
		<link href="engine/editor/jscripts/tiny_mce/plugins/dlebutton/dlebutton.css" rel="stylesheet" type="text/css">
HTML;
	
	}
	
echofooter();
?>