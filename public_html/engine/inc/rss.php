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
 File: rss.php
-----------------------------------------------------
 Use: RSS import
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_rss'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";


if( isset($_GET['subaction']) AND $_GET['subaction'] == "clear" ) {

	$lastdate = intval( $_GET['lastdate'] );
	if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );

}

if( $_REQUEST['action'] == "addnews" ) {

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$parse = new ParseFilter();
	
	$allow_comm = intval( $_POST['allow_comm'] );
	$allow_main = intval( $_POST['allow_main'] );
	$allow_rating = intval( $_POST['allow_rating'] );
	$news_fixed = 0;
	$lastdate = intval( $_POST['lastdate'] );
	
	if( count( $_POST['content'] ) ) {
		
		foreach ( $_POST['content'] as $content ) {
			$approve = intval( $content['approve'] );

			if( !is_array( $content['category'] ) ) $content['category'] = array();
			
			if( !count( $content['category'] ) ) {
				$content['category'] = array ();
				$content['category'][] = '0';
			}

			$category_list = array();
		
			foreach ( $content['category'] as $value ) {
				$category_list[] = intval($value);
			}
		
			$category_list = $db->safesql( implode( ',', $category_list ) );
			
			$full_story = isset($content['full']) ? $parse->process( $content['full'] ) : '';

			$short_story = $parse->process( $content['short'] );
			$title = $parse->process(  trim( strip_tags ($content['title']) ) );

			$_POST['title'] = $title;

			$alt_name = totranslit( stripslashes( $title ), true, false, $config['translit_url'] );
			$title = $db->safesql( $title );
			
			$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
			$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
			
			$metatags = create_metatags( $short_story . $full_story );
			$thistime = date( "Y-m-d H:i:s", strtotime( $content['date'] ) );
			
			if( trim( $title ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_ertitle'], "javascript:history.go(-1)" );
			}
			if( trim( $short_story ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
			}
			
			$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, descr, keywords, category, alt_name, allow_comm, approve, allow_main, allow_br) values ('$thistime', '{$member_id['name']}', '$short_story', '$full_story', '', '$title', '{$metatags['description']}', '{$metatags['keywords']}', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '0')" );

			$row = $db->insert_id();
			$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, user_id) VALUES('{$row}', '$allow_rating', '0', '{$member_id['user_id']}')" );

			if( $category_list AND $approve ) {
		
				$cat_ids = array ();
				
				$cat_ids_arr = explode( ",", $category_list );
				
				foreach ( $cat_ids_arr as $value ) {
					$cat_ids[] = "('" . $row . "', '" . intval( $value ) . "')";
				}
				
				$cat_ids = implode( ", ", $cat_ids );
				$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );
			
			}
	
			$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
		
		}
		
		if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );
		
		clear_cache();
		msg( "success", $lang['addnews_ok'], $lang['rss_added'], "?mod=rss" );
	
	}
	
	msg( "error", $lang['addnews_error'], $lang['rss_notadded'], "?mod=rss" );

} elseif( $_REQUEST['action'] == "news" and $id ) {
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/rss.class.php'));
	
	$parse = new ParseFilter();
	$parse->leech_mode = true;
	
	$rss = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='{$id}'" );
	
	$xml = new DLExmlParser( stripslashes( $rss['url'] ), $rss['max_news'] );
	$link = parse_url(urldecode($rss['url'] ));
		
	$xml->pre_lastdate = $rss['lastdate'];
	
	$xml->pre_parse( $rss['date'] );
	
	$i = 0;

	foreach ( $xml->content as $content ) {

		$xml->content[$i]['title'] = $parse->decodeBBCodes(strip_tags($xml->content[$i]['title']), false);
		$xml->content[$i]['date'] = date("Y-m-d H:i:s", $xml->content[$i]['date']);

		$xml->content[$i]['description'] = str_replace('src="//', "src=\"{$link['scheme']}://", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace("src='//", "src=\"{$link['scheme']}://", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace('srcset="//', "srcset=\"{$link['scheme']}://", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace("srcset='//", "srcset=\"{$link['scheme']}://", $xml->content[$i]['description']);

		$xml->content[$i]['description'] = str_replace('src="/', "src=\"{$link['scheme']}://{$link['host']}/", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace("src='/", "src=\"{$link['scheme']}://{$link['host']}/", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace('srcset="/', "srcset=\"{$link['scheme']}://{$link['host']}/", $xml->content[$i]['description']);
		$xml->content[$i]['description'] = str_replace("srcset='/", "srcset=\"{$link['scheme']}://{$link['host']}/", $xml->content[$i]['description']);

		if (isset($xml->content[$i]['image'])) $xml->content[$i]['description'] = "<img src=\"" . $xml->content[$i]['image'] . "\">\n" . $xml->content[$i]['description'];

		if( $xml->content[$i]['description'] AND ($rss['allow_source'] == 1 OR $rss['allow_source'] == 3) ) {
			$xml->content[$i]['description'] .= "<p>{$lang['rss_info']} <a href=\"{$xml->content[$i]['link']}\" target=\"_blank\">{$xml->content[$i]['link']}</a></p>";
		}

		$xml->content[$i]['description'] = $parse->decodeBBCodes($xml->content[$i]['description'], true, true);

		$i ++;
	}

	$editor_js_code = $bb_panel = $extra_textarea = $extra_class = '';
	$editor_class = '';
	$user_group[$member_id['user_group']]['allow_image_upload'] = false;
	$user_group[$member_id['user_group']]['allow_file_upload'] = false;

	$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";

	include(DLEPlugins::Check(ENGINE_DIR . '/editor/shortnews.php'));
	$editor_class = ' wysiwygeditor';

	echoheader( "<i class=\"fa fa-rss-square position-left\"></i><span class=\"text-semibold\">{$lang['opt_rss']}</span>", array("?mod=rss" => $lang['rss_list'], '' => $lang['header_n_title']) );
	
	echo <<<HTML
<script>

	function doFull( link, news_id, rss_id )
	{

		ShowLoading('');

		$.post('engine/ajax/controller.php?mod=rss', { link: link, news_id: news_id, rss_id: rss_id, user_hash: '{$dle_login_hash}', rss_charset: "{$xml->rss_charset}" }, function(data){
	
			HideLoading('');
	
			$('#cfull'+ news_id).html(data);
	
		});

	return false;
	}

	function RemoveTable( nummer ) {
	    DLEconfirmDelete( '{$lang['edit_cdel']}', '{$lang['p_confirm']}', function () {
			document.getElementById('ContentTable' + nummer).innerHTML = '';
		} );
	}

	function preview( id )
	{
HTML;

	echo "tinyMCE.triggerSave();";

	echo <<<HTML
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1');
        document.addnews.target='prv';
		document.addnews.title.value = document.getElementById('title_' + id).value;
		document.addnews.short_story.value = document.getElementById('short_' + id).value;

		if (document.getElementById('full_' + id)) {
			document.addnews.full_story.value = document.getElementById('full_' + id).value;
		} else {
			document.addnews.full_story.value = "";
		}

        document.addnews.submit();
    }
	$(function(){
		$('.categoryselect').chosen({no_results_text: '{$lang['addnews_cat_fault']}'});

	});
</script>
{$editor_js_code}
<form method=post name="addnewsrss" action="?mod=rss&action=addnews">
<div class="panel panel-default">
  <div class="panel-heading">
    {$rss['url']}
  </div>
  <div class="table-responsive">
HTML;
	
	$i = 0;
	$categories_list = CategoryNewsSelection( $rss['category'], 0 );
	
	if( count( $xml->content ) ) {
		foreach ( $xml->content as $content ) {
			
			echo '<span id="ContentTable' . $i . '"><table class="table form-horizontal" style="table-layout: fixed;"><tr><td>
    <b><a onclick="RemoveTable(' . $i . '); return false;" href="#" ><i class="fa fa-trash-o position-left text-danger"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:ShowOrHide(\'cp' . $i . '\',\'cc' . $i . '\')" >' . $content['title'] . '</a></td>
    </tr>
    <tr id=\'cp' . $i . '\' style=\'display:none\'>
    <td><div class="form-group">
	<label class="control-label col-md-2">' .$lang['addnews_title']. '</label>
	<div class="col-md-10"><input type="text" dir="auto" class="form-control width-550" id="title_' . $i . '" name="content[' . $i . '][title]" value="' . $content['title'] . '"></div>
	</div>
	<div class="form-group">
	<label class="control-label col-md-2">' .$lang['addnews_date']. '</label>
	<div class="col-md-10"><input class="form-control" autocomplete="off" style="width:190px;" data-rel="calendar" type="text" dir="auto" name="content[' . $i . '][date]" value="' . $content['date'] . '"></div>
	</div>
	<div class="form-group">
	<label class="control-label col-md-2">' .$lang['addnews_cat']. '</label>
	<div class="col-md-10"><select data-placeholder="' .$lang['addnews_cat_sel']. '" title="' .$lang['addnews_cat_sel']. '" name="content[' . $i . '][category][]" id="category" class="categoryselect" multiple>' . $categories_list . '</select></div>
	</div>
	</td>
    </tr>
    <tr id=\'cc' . $i . '\' style=\'display:none\'>
    <td>
    <div class="editor-panel"><div class="' . $extra_class . '">' . $bb_panel . '<textarea dir="auto" class="' . $editor_class . '" style="width:100%;max-width:950px;height:200px;" id="short_' . $i . '" name="content[' . $i . '][short]"' . $extra_textarea . '>' . $content['description'] . '</textarea></div></div>
	<div id="cfull' . $i . '" class="mt-10 mb-10">' . htmlspecialchars( $content['link'], ENT_QUOTES, 'UTF-8' ) . '</div>
	<div class="checkbox"><label><input class="icheck" type="checkbox" name="content[' . $i . '][approve]" id="content[' . $i . '][approve]" value="1" checked>' . $lang['addnews_mod'] . '</label></div>
	<br><input onclick="doFull(\'' . urlencode( rtrim( $content['link'] ) ) . '\', \'' . $i . '\', \'' . $rss['id'] . '\')" type="button" class="btn bg-teal btn-sm btn-raised position-left" value="' . $lang['rss_dofull'] . '"><input onclick="preview(' . $i . ')" type="button" class="btn bg-slate-600 btn-sm btn-raised position-left" value="' . $lang['btn_preview'] . '"><input onclick="RemoveTable(' . $i . '); return false;" type="button" class="btn bg-danger btn-sm btn-raised" value="' . $lang['edit_dnews'] . '"><br /><br />
  </tr></table></span>';
			
			$i ++;
		}
		
		echo <<<HTML
    <div class="panel-footer"><button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['rss_addnews']}</button>
	<button onclick="document.location='?mod=rss&action=news&subaction=clear&id={$id}&lastdate={$xml->lastdate}'" type="button" class="btn bg-danger btn-sm btn-raised position-left"><i class="fa fa-trash-o"></i>{$lang['rss_clear']}</button>
	<input type="hidden" name="allow_main" value="{$rss['allow_main']}">
	<input type="hidden" name="allow_rating" value="{$rss['allow_rating']}">
	<input type="hidden" name="allow_comm" value="{$rss['allow_comm']}">
	<input type="hidden" name="lastdate" value="{$xml->lastdate}">
	<input type="hidden" name="id" value="{$id}">
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
	</div>	
HTML;
	
	} else {
		
		echo "<div style=\"padding:10px;\" align=\"center\">" . $lang['rss_no_rss'] . "<br /><br><a class=\"btn bg-teal btn-sm btn-raised\" href=\"?mod=rss\">{$lang['func_msg']}</a></div>";
	
	}
	
	echo <<<HTML
   </div>
</div></form>

<form method="post" name="addnews" id="addnews">
<input type="hidden" name="mod" value="preview">
<input type="hidden" name="title" value="">
<input type="hidden" name="short_story" value="">
<input type="hidden" name="full_story" value="">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
</form>
HTML;
	
	echofooter();

} elseif( $_REQUEST['action'] == "doadd" or $_REQUEST['action'] == "doedit" ) {
	
	if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );
		
	}
	
	$url = str_replace("\r", "", $_POST['rss_url']);
	$url = str_replace("\n", "", $url);
	$url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	$url = str_replace ( "&amp;", "&", $url );
	$url = preg_replace( "/javascript:/i", "j&#1072;vascript:", $url );
	$url = preg_replace( "/data:/i", "da&#1072;ta:", $url );

	$url = $db->safesql( trim( $url ) );
	$description = $db->safesql( trim( $_POST['rss_descr'] ) );
	
	$max_news = intval( $_POST['rss_maxnews'] );
	$allow_main = intval( $_POST['allow_main'] );
	$allow_rating = intval( $_POST['allow_rating'] );
	$allow_comm = intval( $_POST['allow_comm'] );
	$allow_source = intval($_POST['allow_source']);

	$date = intval( $_POST['rss_date'] );
	$category = intval( $_POST['category'] );
	
	$search = $db->safesql( trim( $_POST['rss_search'] ) );
	$cookies = $db->safesql( trim( $_POST['rss_cookie'] ) );
	
	if( $url == "" ) msg( "error", $lang['addnews_error'], $lang['rss_err1'], "javascript:history.go(-1)" );
	
	if( $_REQUEST['action'] == "doadd" ) {
		$db->query( "INSERT INTO " . PREFIX . "_rss (url, description, allow_main, allow_rating, allow_comm, date, search, max_news, cookie, category, allow_source) values ('$url', '$description', '$allow_main', '$allow_rating', '$allow_comm', '$date', '$search', '$max_news', '$cookies', '$category', '$allow_source')" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '51', '{$url}')" );
		msg( "success", $lang['all_info'], $lang['rss_ok1'], "?mod=rss" );
	} else {
		$db->query( "UPDATE " . PREFIX . "_rss set url='$url', description='$description', allow_main='$allow_main', allow_rating='$allow_rating', allow_comm='$allow_comm', date='$date', search='$search', max_news='$max_news', cookie='$cookies', category='$category', lastdate='0', allow_source='$allow_source' WHERE id='{$id}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '52', '{$url}')" );
		msg( "success", $lang['all_info'], $lang['rss_ok2'], "?mod=rss" );
	}

} elseif( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	function makeDropDown($options, $name, $selected) {
		$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"$name\">\r\n";
		foreach ( $options as $value => $description ) {
			$output .= "<option value=\"$value\"";
			if( $selected == $value ) {
				$output .= " selected ";
			}
			$output .= ">$description</option>\n";
		}
		$output .= "</select>";
		return $output;
	}
	
	echoheader( "<i class=\"fa fa-rss-square position-left\"></i><span class=\"text-semibold\">{$lang['opt_rss']}</span>",array("?mod=rss" => $lang['rss_list'], '' => $lang['rss_edit']) );
	
	if( $action == "add" ) {
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", "1" );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", "1" );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", "1" );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", "1" );
		$allow_source = makeDropDown(array("0" => $lang['opt_sys_no'], "1" => $lang['rss_add_source_1'], "2" => $lang['rss_add_source_2'], "3" => $lang['rss_add_source_3']), "allow_source", "0");

		$rss_search_value = "<body>{get}</body>";
		$rss_maxnews_value = 5;
		
		$categories_list = CategoryNewsSelection( 0, 0 );
		$rss_info = $lang['rss_new'];
		$submit_value = $lang['rss_new'];
		$form_action = "?mod=rss&amp;action=doadd";
		
		$rss_url_value = '';
		$rss_descr_value = '';
		$rss_cookie_value = '';
		
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='$id'" );
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", $row['date'] );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", $row['allow_main'] );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", $row['allow_rating'] );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", $row['allow_comm'] );
		$allow_source = makeDropDown(array("0" => $lang['opt_sys_no'], "1" => $lang['rss_add_source_1'], "2" => $lang['rss_add_source_2'], "3" => $lang['rss_add_source_3']), "allow_source", $row['allow_source']);

		$rss_search_value = htmlspecialchars( stripslashes( $row['search'] ), ENT_QUOTES, 'UTF-8' );
		$rss_maxnews_value = $row['max_news'];
		
		$categories_list = CategoryNewsSelection( $row['category'], 0 );
		$rss_info = $row['url'];
		$submit_value = $lang['user_save'];
		$rss_url_value = htmlspecialchars( stripslashes( $row['url'] ), ENT_QUOTES, 'UTF-8' );
		$rss_descr_value = htmlspecialchars( stripslashes( $row['description'] ), ENT_QUOTES, 'UTF-8' );
		$rss_cookie_value = htmlspecialchars( stripslashes( $row['cookie'] ), ENT_QUOTES, 'UTF-8' );
		
		$form_action = "?mod=rss&amp;action=doedit&amp;id=" . $id;
	}
	
	echo <<<HTML
<form action="{$form_action}" method="post" class="form-horizontal">
<div class="panel panel-default">
  <div class="panel-heading">
    {$rss_info}
  </div>
  <div class="panel-body">

		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_url']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control width-400" maxlength="250" name="rss_url" value="{$rss_url_value}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['rss_hurl']}" ></i>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_descr']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control width-400" maxlength="250" name="rss_descr" value="{$rss_descr_value}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['rss_hdescr']}" ></i>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_maxnews']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control text-center" style="width:60px;" name="rss_maxnews" value="{$rss_maxnews_value}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['rss_hmaxnews']}" ></i>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['xfield_xcat']}</label>
		  <div class="col-md-10 col-sm-9">
			<select name="category" class="uniform">{$categories_list}</select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_date']}</label>
		  <div class="col-md-10 col-sm-9">
			{$rss_date}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_main']}</label>
		  <div class="col-md-10 col-sm-9">
			{$allow_main}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_rating']}</label>
		  <div class="col-md-10 col-sm-9">
			{$allow_rating}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_comm']}</label>
		  <div class="col-md-10 col-sm-9">
			{$allow_comm}
		  </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_add_source']}</label>
		  <div class="col-md-10 col-sm-9">
			{$allow_source}
		  </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['rss_search']}</label>
		  <div class="col-md-10 col-sm-9">
			<textarea dir="auto" class="classic" style="width:100%;max-width:350px;" rows="5" name="rss_search">{$rss_search_value}</textarea><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['rss_hsearch']}" ></i>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_cookie']}</label>
		  <div class="col-md-10 col-sm-9">
			<textarea dir="auto" class="classic" style="width:100%;max-width:350px;" rows="5" name="rss_cookie">{$rss_cookie_value}</textarea><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['rss_hcookie']}" ></i>
		  </div>
		 </div>		 
	
   </div>
	<div class="panel-footer">
		<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$submit_value}</button>
	</div>
</div>
<input type="hidden" name="user_hash" value="$dle_login_hash" />
</form>
HTML;
	
	echofooter();
	
} else {
	
	$entries = '';
	
	if( $_REQUEST['action'] == "del" and $id ) {
		
		if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
			
			die( "Hacking attempt! User not found" );
		
		}
		
		$db->query( "DELETE FROM " . PREFIX . "_rss WHERE id = '$id'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '50', '{$id}')" );

	}
	
	echoheader( "<i class=\"fa fa-rss-square position-left\"></i><span class=\"text-semibold\">{$lang['opt_rss']}</span>", $lang['rss_list'] );
	
	$db->query( "SELECT id, url, description FROM " . PREFIX . "_rss ORDER BY id DESC" );
	
	while ( $row = $db->get_row() ) {

		$row['description'] = htmlspecialchars(strip_tags( trim( stripslashes($row['description']) ) ) , ENT_QUOTES, 'UTF-8');

		$menu_link = <<<HTML
        <div class="btn-group">
          <a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
          <ul class="dropdown-menu text-left dropdown-menu-right">
            <li><a href="?mod=rss&action=news&id={$row['id']}"><i class="fa fa-download position-left"></i>{$lang['rss_news']}</a></li>
            <li><a href="?mod=rss&action=edit&id={$row['id']}"><i class="fa fa-pencil-square-o position-left"></i>{$lang['rss_edit']}</a></li>
			<li class="divider"></li>
            <li><a onclick="javascript:confirmdelete('{$row['id']}'); return(false);" href="#"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['rss_del']}</a></li>
          </ul>
        </div>
HTML;
		
		$entries .= "
    <tr>
    <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=rss&action=news&id={$row['id']}'; return false;\"><b>{$row['id']}</b></td>
    <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=rss&action=news&id={$row['id']}'; return false;\">{$row['url']}</td>
    <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=rss&action=news&id={$row['id']}'; return false;\">{$row['description']}</td>
    <td>{$menu_link}</td>
     </tr>";
	}
	$db->free();

	echo <<<HTML
<script>
<!--
function confirmdelete(id){
	DLEconfirmDelete( '{$lang['rsschannel_del']}', '{$lang['p_confirm']}', function () {
		document.location="?mod=rss&action=del&user_hash={$dle_login_hash}&id="+id;
	} );
}
//-->
</script>
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['rss_list']}
  </div>
  <div class="table-responsive">

    <table class="table table-xs table-hover">
      <thead>
      <tr>
        <th style="width: 4.375rem">ID</th>
        <th>{$lang['rss_url']}</th>
        <th>{$lang['rss_descr']}</th>
        <th style="width: 4.375rem">&nbsp;</th>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	
   </div>
   	<div class="panel-footer">
	  <button class="btn bg-teal btn-sm btn-raised" type="button" onclick="document.location='?mod=rss&action=add'"><i class="fa fa-plus-circle position-left"></i>{$lang['rss_new']}</button>
	</div>	
</div>	
HTML;
	
	echofooter();
}
?>