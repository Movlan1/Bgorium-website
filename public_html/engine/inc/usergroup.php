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
 File: usergroup.php
-----------------------------------------------------
 Use: Configuring user groups
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

function clear_html( $txt ) {

	if(!$txt) return '';

	$find = array ('/data:/i','/about:/i','/vbscript:/i','/onclick/i','/onload/i','/onunload/i','/onabort/i','/onerror/i','/onblur/i','/onchange/i','/onfocus/i','/onreset/i','/onsubmit/i','/ondblclick/i','/onkeydown/i','/onkeypress/i','/onkeyup/i','/onmousedown/i','/onmouseup/i','/onmouseover/i','/onmouseout/i','/onselect/i','/javascript/i','/onmouseenter/i','/onwheel/i','/onshow/i','/onafterprint/i','/onbeforeprint/i','/onbeforeunload/i','/onhashchange/i','/onmessage/i','/ononline/i','/onoffline/i','/onpagehide/i','/onpageshow/i','/onpopstate/i','/onresize/i','/onstorage/i','/oncontextmenu/i','/oninvalid/i','/oninput/i','/onsearch/i','/ondrag/i','/ondragend/i','/ondragenter/i','/ondragleave/i','/ondragover/i','/ondragstart/i','/ondrop/i','/onmousemove/i','/onmousewheel/i','/onscroll/i','/oncopy/i','/oncut/i','/onpaste/i','/oncanplay/i','/oncanplaythrough/i','/oncuechange/i','/ondurationchange/i','/onemptied/i','/onended/i','/onloadeddata/i','/onloadedmetadata/i','/onloadstart/i','/onpause/i','/onprogress/i',	'/onratechange/i','/onseeked/i','/onseeking/i','/onstalled/i','/onsuspend/i','/ontimeupdate/i','/onvolumechange/i','/onwaiting/i','/ontoggle/i');
	$replace = array ("d&#1072;ta:", "&#1072;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#1072;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow', '&#111;nafterprint','&#111;nbeforeprint','&#111;nbeforeunload','&#111;nhashchange','&#111;nmessage','&#111;nonline','&#111;noffline','&#111;npagehide','&#111;npageshow','&#111;npopstate','&#111;nresize','&#111;nstorage','&#111;ncontextmenu','&#111;ninvalid','&#111;ninput','&#111;nsearch','&#111;ndrag','&#111;ndragend','&#111;ndragenter','&#111;ndragleave','&#111;ndragover','&#111;ndragstart','&#111;ndrop','&#111;nmousemove','&#111;nmousewheel','&#111;nscroll','&#111;ncopy','&#111;ncut','&#111;npaste','&#111;ncanplay','&#111;ncanplaythrough','&#111;ncuechange','&#111;ndurationchange','&#111;nemptied','&#111;nended','&#111;nloadeddata','&#111;nloadedmetadata','&#111;nloadstart','&#111;npause','&#111;nprogress',	'&#111;nratechange','&#111;nseeked','&#111;nseeking','&#111;nstalled','&#111;nsuspend','&#111;ntimeupdate','&#111;nvolumechange','&#111;nwaiting','&#111;ntoggle');

	$txt = preg_replace( $find, $replace, $txt );
	$txt = preg_replace( "#<iframe#i", "&lt;iframe", $txt );
	$txt = preg_replace( "#<script#i", "&lt;script", $txt );
	$txt = str_replace( "<?", "&lt;?", $txt );
	$txt = str_replace( "?>", "?&gt;", $txt );

	return $txt;

}

if( $action == "del" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( !check_referer($_SERVER['PHP_SELF']."?mod=usergroup") ) {
		msg( "error", $lang['index_denied'], $lang['no_referer'], "javascript:history.go(-1)" );
	}
	
	$id = isset($_REQUEST['id']) ?  intval( $_REQUEST['id'] ) : 0;
	$grouplevel = isset($_REQUEST['grouplevel']) ? intval( $_REQUEST['grouplevel'] ) : 0;
	
	if( $id < 6 ) msg( "error", $lang['addnews_error'], $lang['group_notdel'], "?mod=usergroup" );
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '75', '{$id}')" );

	$row = $db->super_query( "SELECT count(*) as count FROM " . USERPREFIX . "_users WHERE user_group='{$id}'" );
	
	if( ! $row['count'] ) {

		$db->query( "DELETE FROM " . USERPREFIX . "_usergroups WHERE id = '{$id}'" );
		@unlink( ENGINE_DIR . '/cache/system/usergroup.json' );
		clear_cache();
        
        header("Location: ?mod=usergroup");

		msg( "success", $lang['all_info'], $lang['group_del'], "?mod=usergroup" );

	} else {

		if( $grouplevel and $grouplevel != $id ) {
			$db->query( "UPDATE " . USERPREFIX . "_users set user_group='{$grouplevel}' WHERE user_group='{$id}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_usergroups WHERE id = '{$id}'" );
			@unlink( ENGINE_DIR . '/cache/system/usergroup.json' );
			clear_cache();
            
            header("Location: ?mod=usergroup");

			msg( "success", $lang['all_info'], $lang['group_del'], "?mod=usergroup" );
		
        } else msg( "info", $lang['all_info'], "<form action=\"\" method=\"post\">{$lang['group_move']}&nbsp; <select class=\"uniform\" name=\"grouplevel\">" . get_groups( 4 ) . "</select> <input class=\"btn bg-brown-600 btn-sm btn-raised position-right\" type=\"submit\" value=\"{$lang['b_start']}\"></form>", "?mod=usergroup" );
	
    }

} elseif( $action == "selectgroup" ) {

	msg( "info", $lang['all_info'], "<form action=\"\" method=\"get\"><input type=\"hidden\" name=\"mod\" value=\"usergroup\"><input type=\"hidden\" name=\"action\" value=\"add\">{$lang['group_select']}&nbsp; <select class=\"uniform\" name=\"id\">" . get_groups( 4 ) . "</select> <input class=\"btn bg-brown-600 btn-sm btn-raised position-right\" type=\"submit\" value=\"{$lang['group_new']}\"></form>" );

} elseif( $action == "doadd" OR $action == "doedit" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( !check_referer($_SERVER['PHP_SELF']."?mod=usergroup") ) {
		msg( "error", $lang['index_denied'], $lang['no_referer'], "javascript:history.go(-1)" );
	}
	
	if( !isset($_REQUEST['allow_cats']) OR !is_array($_REQUEST['allow_cats']) ) $_REQUEST['allow_cats'] = array();
	if( !isset($_REQUEST['not_allow_cats']) OR !is_array($_REQUEST['not_allow_cats']) ) $_REQUEST['not_allow_cats'] = array();
	if( !isset($_REQUEST['cat_add']) OR !is_array($_REQUEST['cat_add']) ) $_REQUEST['cat_add'] = array();
	if( !isset($_REQUEST['cat_allow_addnews']) OR !is_array($_REQUEST['cat_allow_addnews']) ) $_REQUEST['cat_allow_addnews'] = array();
	
	if( !count( $_REQUEST['allow_cats'] ) ) $_REQUEST['allow_cats'][] = "all";
	if( !count( $_REQUEST['not_allow_cats'] ) ) $_REQUEST['not_allow_cats'][] = "";
	if( !count( $_REQUEST['cat_add'] ) ) $_REQUEST['cat_add'][] = "all";
	if( !count( $_REQUEST['cat_allow_addnews'] ) ) $_REQUEST['cat_allow_addnews'][] = "all";

	$group_name = $db->safesql( strip_tags( clear_html($_REQUEST['group_name']) ) );
	$group_icon = $db->safesql( strip_tags( clear_html($_REQUEST['group_icon']) ) );
	$files_type = $db->safesql( strip_tags( clear_html($_REQUEST['files_type']) ) );
	$files_type = str_replace(' ','', $files_type);
	$files_type = str_replace('.','', $files_type);
	$mail_files_type = $db->safesql( strip_tags( clear_html($_REQUEST['mail_files_type']) ) );
	$mail_files_type = str_replace(' ','', $mail_files_type);
	$mail_files_type = str_replace('.','', $mail_files_type);

	$group_prefix = $db->safesql( trim( clear_html($_REQUEST['group_prefix']) ) );
	$group_suffix = $db->safesql( trim( clear_html($_REQUEST['group_suffix']) ) );

	$list = array();
	
	foreach ( $_REQUEST['not_allow_cats'] as $value ) {
		if( intval($value) ) $list[] = intval($value);
	}
	
	$not_allow_cats = $db->safesql( implode( ',', $list) );

	$list = array();

	foreach ( $_REQUEST['allow_cats'] as $value ) {
		if($value == "all" AND count($_REQUEST['allow_cats']) < 2 ) $list[] = "all";
		elseif( intval($value) > 0 ) $list[] = intval($value);
	}
	
	$allow_cats = $db->safesql( implode( ',', $list) );

	$list = array();

	foreach ( $_REQUEST['cat_add'] as $value ) {
		if($value == "all" AND count($_REQUEST['cat_add']) < 2 ) $list[] = "all";
		elseif( intval($value) > 0 ) $list[] = intval($value);
	}
	
	$cat_add = $db->safesql( implode( ',', $list) );
	
	$list = array();

	foreach ( $_REQUEST['cat_allow_addnews'] as $value ) {
		if($value == "all" AND count($_REQUEST['cat_allow_addnews']) < 2 ) $list[] = "all";
		elseif( intval($value) > 0 ) $list[] = intval($value);
	}
	
	$cat_allow_addnews = $db->safesql( implode( ',', $list) );
	
	$allow_admin = isset($_REQUEST['allow_admin']) ? intval( $_REQUEST['allow_admin'] ) : 0;
	$allow_offline = isset($_REQUEST['allow_offline']) ? intval( $_REQUEST['allow_offline'] ): 0;
	$allow_main = isset($_REQUEST['allow_main']) ? intval( $_REQUEST['allow_main'] ): 0;
	$allow_adds = isset($_REQUEST['allow_adds']) ? intval( $_REQUEST['allow_adds'] ): 0;
	$moderation = isset($_REQUEST['moderation']) ? intval( $_REQUEST['moderation'] ): 0;
	$allow_edit = isset($_REQUEST['allow_edit']) ? intval( $_REQUEST['allow_edit'] ): 0;
	$allow_all_edit = isset($_REQUEST['allow_all_edit']) ? intval( $_REQUEST['allow_all_edit'] ): 0;
	$allow_addc = isset($_REQUEST['allow_addc']) ? intval( $_REQUEST['allow_addc'] ): 0;
	$allow_editc = isset($_REQUEST['allow_editc']) ? intval( $_REQUEST['allow_editc'] ): 0;
	$allow_delc = isset($_REQUEST['allow_delc']) ? intval( $_REQUEST['allow_delc'] ): 0;
	$edit_allc = isset($_REQUEST['edit_allc']) ? intval( $_REQUEST['edit_allc'] ): 0;
	$del_allc = isset($_REQUEST['del_allc']) ? intval( $_REQUEST['del_allc'] ): 0;
	$allow_hide = isset($_REQUEST['allow_hide']) ? intval( $_REQUEST['allow_hide'] ): 0;
	$allow_pm = isset($_REQUEST['allow_pm']) ? intval( $_REQUEST['allow_pm'] ): 0;
	$allow_vote = isset($_REQUEST['allow_vote']) ? intval( $_REQUEST['allow_vote'] ): 0;
	$allow_files = isset($_REQUEST['allow_files']) ? intval( $_REQUEST['allow_files'] ): 0;
    $max_downloads = isset($_REQUEST['max_downloads']) ? intval($_REQUEST['max_downloads']): 0;
	$allow_feed = isset($_REQUEST['allow_feed']) ? intval( $_REQUEST['allow_feed'] ): 0;
	$allow_search = isset($_REQUEST['allow_search']) ? intval( $_REQUEST['allow_search'] ): 0;
	$allow_rating = isset($_REQUEST['allow_rating']) ? intval( $_REQUEST['allow_rating'] ): 0;
	$allow_comments_rating = isset($_REQUEST['allow_comments_rating']) ? intval( $_REQUEST['allow_comments_rating'] ): 0;
	$max_pm = isset($_REQUEST['max_pm']) ? intval( $_REQUEST['max_pm'] ): 0;
	$max_foto = isset($_REQUEST['max_foto']) ? $db->safesql( $_REQUEST['max_foto'] ): '';
	$allow_short = isset($_REQUEST['allow_short']) ? intval( $_REQUEST['allow_short'] ): 0;
	$time_limit = isset($_REQUEST['time_limit']) ? intval( $_REQUEST['time_limit'] ): 0;
	$rid = isset($_REQUEST['rid']) ? intval( $_REQUEST['rid'] ): 0;
	$allow_fixed = isset($_REQUEST['allow_fixed']) ? intval( $_REQUEST['allow_fixed'] ): 0;
	$allow_poll = isset($_REQUEST['allow_poll']) ? intval( $_REQUEST['allow_poll'] ): 0;
	$captcha = isset($_REQUEST['captcha']) ? intval( $_REQUEST['captcha'] ): 0;
	$allow_modc = isset($_REQUEST['allow_modc']) ? intval( $_REQUEST['allow_modc'] ): 0;
	$max_signature = isset($_REQUEST['max_signature']) ? intval( $_REQUEST['max_signature'] ): 0;
	$max_info = isset($_REQUEST['max_info']) ? intval( $_REQUEST['max_info'] ): 0;
	$admin_addnews = isset($_REQUEST['admin_addnews']) ? intval( $_REQUEST['admin_addnews'] ): 0;
	$admin_editnews = isset($_REQUEST['admin_editnews']) ? intval( $_REQUEST['admin_editnews'] ): 0;
	$admin_comments = isset($_REQUEST['admin_comments']) ? intval( $_REQUEST['admin_comments'] ): 0;
	$admin_categories = isset($_REQUEST['admin_categories']) ? intval( $_REQUEST['admin_categories'] ): 0;
	$admin_editusers = isset($_REQUEST['admin_editusers']) ? intval( $_REQUEST['admin_editusers'] ): 0;
	$admin_wordfilter = isset($_REQUEST['admin_wordfilter']) ? intval( $_REQUEST['admin_wordfilter'] ): 0;
	$admin_xfields = isset($_REQUEST['admin_xfields']) ? intval( $_REQUEST['admin_xfields'] ): 0;
	$admin_userfields = isset($_REQUEST['admin_userfields']) ? intval( $_REQUEST['admin_userfields'] ): 0;
	$admin_static = isset($_REQUEST['admin_static']) ? intval( $_REQUEST['admin_static'] ) : 0;
	$admin_editvote = isset($_REQUEST['admin_editvote']) ? intval( $_REQUEST['admin_editvote'] ) : 0;
	$admin_newsletter = isset($_REQUEST['admin_newsletter']) ? intval( $_REQUEST['admin_newsletter'] ) : 0;
	$admin_blockip = isset($_REQUEST['admin_blockip']) ? intval( $_REQUEST['admin_blockip'] ) : 0;
	$admin_banners = isset($_REQUEST['admin_banners']) ? intval( $_REQUEST['admin_banners'] ) : 0;
	$admin_rss = isset($_REQUEST['admin_rss']) ? intval( $_REQUEST['admin_rss'] ) : 0;
	$admin_iptools = isset($_REQUEST['admin_iptools']) ? intval( $_REQUEST['admin_iptools'] ) : 0;
	$admin_rssinform = isset($_REQUEST['admin_rssinform']) ? intval( $_REQUEST['admin_rssinform'] ) : 0;
	$admin_googlemap = isset($_REQUEST['admin_googlemap']) ? intval( $_REQUEST['admin_googlemap'] ) : 0;
	$admin_tagscloud = isset($_REQUEST['admin_tagscloud']) ? intval( $_REQUEST['admin_tagscloud'] ) : 0;
	$admin_complaint = isset($_REQUEST['admin_complaint']) ? intval( $_REQUEST['admin_complaint'] ) : 0;
    $admin_links = isset($_REQUEST['admin_links']) ? intval($_REQUEST['admin_links']) : 0;
    $admin_redirects = isset($_REQUEST['admin_redirects']) ? intval($_REQUEST['admin_redirects']) : 0;
    $admin_meta = isset($_REQUEST['admin_meta']) ? intval($_REQUEST['admin_meta']) : 0;
	$allow_image_size = isset($_REQUEST['allow_image_size']) ? intval( $_REQUEST['allow_image_size'] ) : 0;
	$allow_image_upload = isset($_REQUEST['allow_image_upload']) ? intval( $_REQUEST['allow_image_upload'] ) : 0;
	$allow_file_upload = isset($_REQUEST['allow_file_upload']) ? intval( $_REQUEST['allow_file_upload'] ) : 0;
	$allow_public_file_upload = isset($_REQUEST['allow_public_file_upload']) ? intval( $_REQUEST['allow_public_file_upload'] ) : 0;
    $allow_change_storage = isset($_REQUEST['allow_change_storage']) ? intval($_REQUEST['allow_change_storage']) : 0;
	$allow_signature = isset($_REQUEST['allow_signature']) ? intval( $_REQUEST['allow_signature'] ) : 0;
	$allow_url = isset($_REQUEST['allow_url']) ? intval( $_REQUEST['allow_url'] ) : 0;
	$allow_image = isset($_REQUEST['allow_image']) ? intval( $_REQUEST['allow_image'] ) : 0;
	$news_sec_code = isset($_REQUEST['news_sec_code']) ? intval( $_REQUEST['news_sec_code'] ) : 0;
	$allow_subscribe = isset($_REQUEST['allow_subscribe']) ? intval( $_REQUEST['allow_subscribe'] ) : 0;
	$flood_news = isset($_REQUEST['flood_news']) ? intval( $_REQUEST['flood_news'] ) : 0;
	$max_day_news = isset($_REQUEST['max_day_news']) ? intval( $_REQUEST['max_day_news'] ) : 0;
	$edit_limit = isset($_REQUEST['edit_limit']) ? intval( $_REQUEST['edit_limit'] ) : 0;
	$captcha_pm = isset($_REQUEST['captcha_pm']) ? intval( $_REQUEST['captcha_pm'] ) : 0;
	$max_pm_day = isset($_REQUEST['max_pm_day']) ? intval( $_REQUEST['max_pm_day'] ) : 0;
	$max_comment_day = isset($_REQUEST['max_comment_day']) ? intval( $_REQUEST['max_comment_day'] ) : 0;
    $flood_time = isset($_REQUEST['flood_time']) ? intval($_REQUEST['flood_time']) : 0;
	$max_mail_day = isset($_REQUEST['max_mail_day']) ? intval( $_REQUEST['max_mail_day'] ) : 0;
	$comments_question = isset($_REQUEST['comments_question']) ? intval( $_REQUEST['comments_question'] ) : 0;
	$news_question = isset($_REQUEST['news_question']) ? intval( $_REQUEST['news_question'] ) : 0;
	$max_images = isset($_REQUEST['max_images']) ? intval( $_REQUEST['max_images'] ) : 0;
    $max_c_negative = isset($_REQUEST['max_c_negative']) ? intval($_REQUEST['max_c_negative']) : 0;
    $max_n_negative = isset($_REQUEST['max_n_negative']) ? intval($_REQUEST['max_n_negative']) : 0;
	$max_files = isset($_REQUEST['max_files']) ? intval( $_REQUEST['max_files'] ) : 0;
	$disable_news_captcha = isset($_REQUEST['disable_news_captcha']) ? intval( $_REQUEST['disable_news_captcha'] ) : 0;
	$disable_comments_captcha = isset($_REQUEST['disable_comments_captcha']) ? intval( $_REQUEST['disable_comments_captcha'] ) : 0;
	$pm_question = isset($_REQUEST['pm_question']) ? intval( $_REQUEST['pm_question'] ) : 0;
	$captcha_feedback = isset($_REQUEST['captcha_feedback']) ? intval( $_REQUEST['captcha_feedback'] ) : 0;
	$feedback_question = isset($_REQUEST['feedback_question']) ? intval( $_REQUEST['feedback_question'] ) : 0;
	$max_file_size = isset($_REQUEST['max_file_size']) ? intval( $_REQUEST['max_file_size'] ) : 0;
	$spamfilter = isset($_REQUEST['spamfilter']) ? intval( $_REQUEST['spamfilter'] ) : 0;
	$spampmfilter = isset($_REQUEST['spampmfilter']) ? intval( $_REQUEST['spampmfilter'] ) : 0;
    $self_delete = isset($_REQUEST['self_delete']) ? intval($_REQUEST['self_delete']) : 0;
	$max_edit_days = isset($_REQUEST['max_edit_days']) ? intval( $_REQUEST['max_edit_days'] ) : 0;
	$force_reg = isset($_REQUEST['force_reg']) ? intval( $_REQUEST['force_reg'] ) : 0;
	$force_reg_days = isset($_REQUEST['force_reg_days']) ? intval( $_REQUEST['force_reg_days'] ) : 0;
	$force_reg_group = isset($_REQUEST['force_reg_group']) ? intval( $_REQUEST['force_reg_group'] ) : 0;
	$force_news = isset($_REQUEST['force_news']) ? intval( $_REQUEST['force_news'] ) : 0;
	$force_news_count = isset($_REQUEST['force_news_count']) ? intval( $_REQUEST['force_news_count'] ) : 0;
	$force_news_group = isset($_REQUEST['force_news_group']) ? intval( $_REQUEST['force_news_group'] ) : 0;
	$force_comments = isset($_REQUEST['force_comments']) ? intval( $_REQUEST['force_comments'] ) : 0;
	$force_comments_count = isset($_REQUEST['force_comments_count']) ? intval( $_REQUEST['force_comments_count'] ) : 0;
	$force_comments_group = isset($_REQUEST['force_comments_group']) ? intval( $_REQUEST['force_comments_group'] ) : 0;
	$force_rating = isset($_REQUEST['force_rating']) ? intval( $_REQUEST['force_rating'] ) : 0;
	$force_rating_count = isset($_REQUEST['force_rating_count']) ? intval( $_REQUEST['force_rating_count'] ) : 0;
	$force_rating_group = isset($_REQUEST['force_rating_group']) ? intval( $_REQUEST['force_rating_group'] ) : 0;
	$force_comments_rating = isset($_REQUEST['force_comments_rating']) ? intval( $_REQUEST['force_comments_rating'] ) : 0;
	$force_comments_rating_count = isset($_REQUEST['force_comments_rating_count']) ? intval( $_REQUEST['force_comments_rating_count'] ) : 0;
	$force_comments_rating_group = isset($_REQUEST['force_comments_rating_group']) ? intval( $_REQUEST['force_comments_rating_group'] ) : 0;
	
	$allow_up_image = isset($_REQUEST['allow_up_image']) ? intval( $_REQUEST['allow_up_image'] ) : 0;
	$allow_up_watermark = isset($_REQUEST['allow_up_watermark']) ? intval( $_REQUEST['allow_up_watermark'] ) : 0;
	$allow_up_thumb = isset($_REQUEST['allow_up_thumb']) ? intval( $_REQUEST['allow_up_thumb'] ) : 0;
	$up_count_image = isset($_REQUEST['up_count_image']) ? intval( $_REQUEST['up_count_image'] ) : 0;
	$up_image_side = isset($_REQUEST['up_image_side']) ? $db->safesql( strip_tags( clear_html($_REQUEST['up_image_side']) ) ) : '';
	$min_image_side = isset($_REQUEST['min_image_side']) ? $db->safesql( strip_tags( clear_html($_REQUEST['min_image_side']) ) ) : '';
	$up_image_size = isset($_REQUEST['up_image_size']) ? intval($_REQUEST['up_image_size']) : 0;
	$up_thumb_size = isset($_REQUEST['up_thumb_size']) ? $db->safesql( strip_tags( clear_html($_REQUEST['up_thumb_size']) ) ) : 0;
	$allow_mail_files = isset($_REQUEST['allow_mail_files']) ? intval($_REQUEST['allow_mail_files']) : 0;
	$max_mail_files = isset($_REQUEST['max_mail_files']) ? intval($_REQUEST['max_mail_files']) : 0;
	$max_mail_allfiles = isset($_REQUEST['max_mail_allfiles']) ? intval($_REQUEST['max_mail_allfiles']) : 0;
	$video_comments = isset($_REQUEST['video_comments']) ? intval($_REQUEST['video_comments']) : 0;
	$media_comments = isset($_REQUEST['media_comments']) ? intval($_REQUEST['media_comments']) : 0;
    $allow_complaint_news = isset($_REQUEST['allow_complaint_news']) ? intval($_REQUEST['allow_complaint_news']) : 0;
    $allow_complaint_comments = isset($_REQUEST['allow_complaint_comments']) ? intval($_REQUEST['allow_complaint_comments']) : 0;
    $allow_complaint_orfo = isset($_REQUEST['allow_complaint_orfo']) ? intval($_REQUEST['allow_complaint_orfo']) : 0;

	if( $group_name == "" ) msg( "error", $lang['addnews_error'], $lang['group_err1'], "?mod=usergroup&action=add" );
	
	@unlink( ENGINE_DIR . '/cache/system/usergroup.json' );
	clear_cache();

	if( $action == "doadd" ) {
		$db->query( "INSERT INTO " . USERPREFIX . "_usergroups (group_name, allow_cats, allow_adds, cat_add, allow_admin, allow_addc, allow_editc, allow_delc, edit_allc, del_allc, moderation, allow_all_edit, allow_edit, allow_pm, max_pm, max_foto, allow_files, allow_hide, allow_short, time_limit, rid, allow_fixed, allow_feed, allow_search, allow_poll, allow_main, captcha, icon, allow_modc, allow_rating, allow_offline, allow_image_upload, allow_file_upload, allow_signature, allow_url, news_sec_code, allow_image, max_signature, max_info, admin_addnews, admin_editnews, admin_comments, admin_categories, admin_editusers, admin_wordfilter, admin_xfields, admin_userfields, admin_static, admin_editvote, admin_newsletter, admin_blockip, admin_banners, admin_rss, admin_iptools, admin_rssinform, admin_googlemap, group_prefix, group_suffix, allow_subscribe, allow_image_size, cat_allow_addnews, flood_news, max_day_news, edit_limit, captcha_pm, max_pm_day, max_mail_day, admin_tagscloud, allow_vote, admin_complaint, news_question, comments_question, max_comment_day, max_images, max_files, disable_news_captcha, disable_comments_captcha, pm_question, captcha_feedback, feedback_question, files_type, max_file_size, spamfilter, allow_comments_rating, max_edit_days, spampmfilter, force_reg, force_reg_days, force_reg_group, force_news, force_news_count, force_news_group, force_comments, force_comments_count, force_comments_group, force_rating, force_rating_count, force_rating_group, not_allow_cats, allow_up_image, allow_up_watermark, allow_up_thumb, up_count_image, up_image_side, up_image_size, up_thumb_size, allow_mail_files, max_mail_files, max_mail_allfiles, mail_files_type, video_comments, media_comments, min_image_side, allow_public_file_upload, force_comments_rating, force_comments_rating_count, force_comments_rating_group, max_downloads, admin_links, admin_meta, admin_redirects, allow_change_storage, self_delete, allow_complaint_news, allow_complaint_comments, allow_complaint_orfo, flood_time, max_c_negative, max_n_negative) values ('$group_name', '$allow_cats', '$allow_adds', '$cat_add', '$allow_admin', '$allow_addc', '$allow_editc', '$allow_delc', '$edit_allc', '$del_allc', '$moderation', '$allow_all_edit', '$allow_edit', '$allow_pm', '$max_pm', '$max_foto', '$allow_files', '$allow_hide', '$allow_short', '$time_limit', '$rid', '$allow_fixed', '$allow_feed', '$allow_search', '$allow_poll', '$allow_main', '$captcha', '$group_icon', '$allow_modc', '$allow_rating', '$allow_offline', '$allow_image_upload', '$allow_file_upload', '$allow_signature', '$allow_url', '$news_sec_code', '$allow_image', '$max_signature', '$max_info', '$admin_addnews', '$admin_editnews', '$admin_comments', '$admin_categories', '$admin_editusers', '$admin_wordfilter', '$admin_xfields', '$admin_userfields', '$admin_static', '$admin_editvote', '$admin_newsletter', '$admin_blockip', '$admin_banners', '$admin_rss', '$admin_iptools', '$admin_rssinform', '$admin_googlemap', '$group_prefix', '$group_suffix', '$allow_subscribe', '$allow_image_size', '$cat_allow_addnews', '$flood_news', '$max_day_news', '$edit_limit', '$captcha_pm', '$max_pm_day', '$max_mail_day', '$admin_tagscloud', '$allow_vote', '$admin_complaint', '$news_question', '$comments_question', '$max_comment_day', '$max_images', '$max_files', '$disable_news_captcha', '$disable_comments_captcha', '$pm_question', '$captcha_feedback', '$feedback_question', '$files_type', '$max_file_size', '$spamfilter', '$allow_comments_rating', '$max_edit_days', '$spampmfilter', '$force_reg','$force_reg_days','$force_reg_group','$force_news','$force_news_count','$force_news_group','$force_comments','$force_comments_count','$force_comments_group','$force_rating','$force_rating_count','$force_rating_group', '$not_allow_cats', '$allow_up_image', '$allow_up_watermark', '$allow_up_thumb', '$up_count_image', '$up_image_side', '$up_image_size', '$up_thumb_size', '$allow_mail_files', '$max_mail_files', '$max_mail_allfiles', '$mail_files_type', '$video_comments', '$media_comments', '$min_image_side', '$allow_public_file_upload', '$force_comments_rating','$force_comments_rating_count','$force_comments_rating_group', '$max_downloads', '$admin_links', '$admin_meta', '$admin_redirects', '$allow_change_storage', '$self_delete', '$allow_complaint_news', '$allow_complaint_comments', '$allow_complaint_orfo', '$flood_time', '$max_c_negative', '$max_n_negative')" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '76', '{$group_name}')" );
		msg( "success", $lang['all_info'], $lang['group_ok1'], "?mod=usergroup" );
	} else {
		$id = intval( $_REQUEST['id'] );

		if( $id == 1 ) { $allow_admin = 1; $self_delete = 0; }
		if( $id == 4 OR  $id == 5) $allow_admin = 0;

		$db->query( "UPDATE " . USERPREFIX . "_usergroups SET group_name='$group_name', allow_cats='$allow_cats', allow_adds='$allow_adds', cat_add='$cat_add', allow_admin='$allow_admin', allow_addc='$allow_addc', allow_editc='$allow_editc', allow_delc='$allow_delc', edit_allc='$edit_allc', del_allc='$del_allc', moderation='$moderation', allow_all_edit='$allow_all_edit', allow_edit='$allow_edit', allow_pm='$allow_pm', max_pm='$max_pm', max_foto='$max_foto', allow_files='$allow_files', allow_hide='$allow_hide', allow_short='$allow_short', time_limit='$time_limit', rid='$rid', allow_fixed='$allow_fixed', allow_feed='$allow_feed', allow_search='$allow_search', allow_poll='$allow_poll', allow_main='$allow_main', captcha='$captcha', icon='$group_icon', allow_modc='$allow_modc', allow_rating='$allow_rating', allow_offline='$allow_offline', allow_image_upload='$allow_image_upload', allow_file_upload='$allow_file_upload', allow_signature='$allow_signature', allow_url='$allow_url', news_sec_code='$news_sec_code', allow_image='$allow_image', max_signature='$max_signature', max_info='$max_info', admin_addnews='$admin_addnews', admin_editnews='$admin_editnews', admin_comments='$admin_comments', admin_categories='$admin_categories', admin_editusers='$admin_editusers', admin_wordfilter='$admin_wordfilter', admin_xfields='$admin_xfields', admin_userfields='$admin_userfields', admin_static='$admin_static', admin_editvote='$admin_editvote', admin_newsletter='$admin_newsletter', admin_blockip='$admin_blockip', admin_banners='$admin_banners', admin_rss='$admin_rss', admin_iptools='$admin_iptools', admin_rssinform='$admin_rssinform', admin_googlemap='$admin_googlemap', group_prefix='$group_prefix', group_suffix='$group_suffix', allow_subscribe='$allow_subscribe', allow_image_size='$allow_image_size', cat_allow_addnews='$cat_allow_addnews', flood_news='$flood_news', max_day_news='$max_day_news', edit_limit='$edit_limit', captcha_pm='$captcha_pm', max_pm_day='$max_pm_day', max_mail_day='$max_mail_day', admin_tagscloud='$admin_tagscloud', allow_vote='$allow_vote', admin_complaint='$admin_complaint', news_question='$news_question', comments_question='$comments_question', max_comment_day='$max_comment_day', max_images='$max_images', max_files='$max_files', disable_news_captcha='$disable_news_captcha', disable_comments_captcha='$disable_comments_captcha', pm_question='$pm_question', captcha_feedback='$captcha_feedback', feedback_question='$feedback_question', files_type='$files_type', max_file_size='$max_file_size', spamfilter='$spamfilter', allow_comments_rating='$allow_comments_rating', max_edit_days='$max_edit_days', spampmfilter='$spampmfilter', force_reg='$force_reg', force_reg_days='$force_reg_days', force_reg_group='$force_reg_group', force_news='$force_news', force_news_count='$force_news_count', force_news_group='$force_news_group', force_comments='$force_comments', force_comments_count='$force_comments_count', force_comments_group='$force_comments_group', force_rating='$force_rating', force_rating_count='$force_rating_count', force_rating_group='$force_rating_group', not_allow_cats='$not_allow_cats', allow_up_image='$allow_up_image', allow_up_watermark='$allow_up_watermark', allow_up_thumb='$allow_up_thumb', up_count_image='$up_count_image', up_image_side='$up_image_side', up_image_size='$up_image_size', up_thumb_size='$up_thumb_size', allow_mail_files='$allow_mail_files', max_mail_files='$max_mail_files', max_mail_allfiles='$max_mail_allfiles', mail_files_type='$mail_files_type', video_comments='$video_comments', media_comments='$media_comments', min_image_side='$min_image_side', allow_public_file_upload='$allow_public_file_upload', force_comments_rating='$force_comments_rating', force_comments_rating_count='$force_comments_rating_count', force_comments_rating_group='$force_comments_rating_group', max_downloads='$max_downloads', admin_links='$admin_links', admin_meta='$admin_meta', admin_redirects='$admin_redirects', allow_change_storage='$allow_change_storage', self_delete='$self_delete', allow_complaint_news='$allow_complaint_news', allow_complaint_comments='$allow_complaint_comments', allow_complaint_orfo='$allow_complaint_orfo', flood_time='$flood_time', max_c_negative='$max_c_negative', max_n_negative='$max_n_negative' WHERE id='{$id}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '77', '{$group_name}')" );
		msg( "success", $lang['all_info'], $lang['group_ok2'], "?mod=usergroup" );
	}

} elseif( $action == "add" or $action == "edit" ) {

	$id = intval( $_REQUEST['id'] );

	if (!$user_group[$id]['group_name']) {
		msg( "error", $lang['addnews_error'], $lang['group_err2'], "javascript:history.go(-1)" );
	}
	
	if( ! $config['allow_cmod'] ) $warning = "<br /><span class=\"text-danger\">" . $lang['modul_offline'] . "</span>";
	else $warning = "";

	if( ! $config['allow_subscribe'] ) $warning_1 = "<br /><span class=\"text-danger\">" . $lang['modul_offline_1'] . "</span>";
	else $warning_1 = "";

	$group_prefix_value = htmlspecialchars( stripslashes( $user_group[$id]['group_prefix'] ), ENT_QUOTES, 'UTF-8' );
	$group_suffix_value = htmlspecialchars( stripslashes( $user_group[$id]['group_suffix'] ), ENT_QUOTES, 'UTF-8' );
	$files_type_value = htmlspecialchars( stripslashes( $user_group[$id]['files_type'] ), ENT_QUOTES, 'UTF-8' );
	$mail_files_type_value = htmlspecialchars( stripslashes( $user_group[$id]['mail_files_type'] ), ENT_QUOTES, 'UTF-8' );
		
	if( $user_group[$id]['allow_offline'] ) $allow_offline = "checked"; else $allow_offline = "";
	if( $user_group[$id]['allow_admin'] ) $allow_admin = "checked"; else $allow_admin = "";
	if( $user_group[$id]['allow_adds'] ) $allow_adds = "checked"; else $allow_adds = "";
	if( $user_group[$id]['moderation'] ) $moderation = "checked"; else $moderation = "";
	if( $user_group[$id]['allow_edit'] ) $allow_edit = "checked"; else $allow_edit = "";
	if( $user_group[$id]['allow_all_edit'] ) $allow_all_edit = "checked"; else $allow_all_edit = "";
	if( $user_group[$id]['allow_addc'] ) $allow_addc = "checked"; else $allow_addc = "";
	if( $user_group[$id]['allow_editc'] ) $allow_editc = "checked"; else $allow_editc = "";
	if( $user_group[$id]['allow_delc'] ) $allow_delc = "checked"; else $allow_delc = "";
	if( $user_group[$id]['edit_allc'] ) $edit_allc = "checked"; else $edit_allc = "";
	if( $user_group[$id]['del_allc'] ) $del_allc = "checked"; else $del_allc = "";
	if( $user_group[$id]['allow_hide'] ) $allow_hide = "checked"; else $allow_hide = "";
	if( $user_group[$id]['allow_pm'] ) $allow_pm = "checked"; else $allow_pm = "";
	if( $user_group[$id]['allow_vote'] ) $allow_vote = "checked"; else $allow_vote = "";
	if( $user_group[$id]['allow_files'] ) $allow_files = "checked"; else $allow_files = "";
	if( $user_group[$id]['allow_feed'] ) $allow_feed = "checked"; else $allow_feed = "";
	if( $user_group[$id]['allow_search'] ) $allow_search = "checked"; else $allow_search = "";
	if( $user_group[$id]['allow_rating'] ) $allow_rating = "checked"; else $allow_rating = "";
	if( $user_group[$id]['allow_comments_rating'] ) $allow_comments_rating = "checked"; else $allow_comments_rating = "";
	if( $user_group[$id]['allow_short'] ) $allow_short = "checked"; else $allow_short = "";
	if( $user_group[$id]['time_limit'] ) $time_limit = "checked"; else $time_limit = "";
	if( $user_group[$id]['allow_fixed'] ) $allow_fixed = "checked"; else $allow_fixed = "";
	if( $user_group[$id]['allow_poll'] ) $allow_poll = "checked"; else $allow_poll = "";
	if( $user_group[$id]['allow_main'] ) $allow_main = "checked"; else $allow_main = "";
	if( $user_group[$id]['captcha'] ) $allow_captcha = "checked"; else $allow_captcha = "";
	if( $user_group[$id]['captcha_pm'] ) $allow_captcha_pm = "checked"; else $allow_captcha_pm = "";
	if( $user_group[$id]['allow_modc'] ) $allow_modc = "checked"; else $allow_modc = "";
	if( $user_group[$id]['allow_image_upload'] ) $allow_image_upload = "checked"; else $allow_image_upload = "";
	if( $user_group[$id]['allow_file_upload'] ) $allow_file_upload = "checked"; else $allow_file_upload = "";
	if( $user_group[$id]['allow_public_file_upload'] ) $allow_public_file_upload = "checked"; else $allow_public_file_upload = "";
    if ($user_group[$id]['allow_change_storage']) $allow_change_storage = "checked"; else $allow_change_storage = "";
	if( $user_group[$id]['allow_signature'] ) $allow_signature = "checked"; else $allow_signature = "";
	if( $user_group[$id]['allow_url'] ) $allow_url = "checked"; else $allow_url = "";
	if( $user_group[$id]['allow_image'] ) $allow_image = "checked"; else $allow_image = "";
	if( $user_group[$id]['news_sec_code'] ) $news_sec_code = "checked"; else $news_sec_code = "";
	if( $user_group[$id]['admin_addnews'] ) $admin_addnews = "checked"; else $admin_addnews = "";
	if( $user_group[$id]['admin_editnews'] ) $admin_editnews = "checked"; else $admin_editnews = "";
	if( $user_group[$id]['admin_comments'] ) $admin_comments = "checked"; else $admin_comments = "";
	if( $user_group[$id]['admin_categories'] ) $admin_categories = "checked"; else $admin_categories = "";
	if( $user_group[$id]['admin_editusers'] ) $admin_editusers = "checked"; else $admin_editusers = "";
	if( $user_group[$id]['admin_wordfilter'] ) $admin_wordfilter = "checked"; else $admin_wordfilter = "";
	if( $user_group[$id]['admin_xfields'] ) $admin_xfields = "checked"; else $admin_xfields = "";
	if( $user_group[$id]['admin_userfields'] ) $admin_userfields = "checked"; else $admin_userfields = "";
	if( $user_group[$id]['admin_static'] ) $admin_static = "checked"; else $admin_static = "";
	if( $user_group[$id]['admin_editvote'] ) $admin_editvote = "checked"; else $admin_editvote = "";
	if( $user_group[$id]['admin_newsletter'] ) $admin_newsletter = "checked"; else $admin_newsletter = "";
	if( $user_group[$id]['admin_blockip'] ) $admin_blockip = "checked"; else $admin_blockip = "";
	if( $user_group[$id]['admin_banners'] ) $admin_banners = "checked"; else $admin_banners = "";
	if( $user_group[$id]['admin_rss'] ) $admin_rss = "checked"; else $admin_rss = "";
	if( $user_group[$id]['admin_iptools'] ) $admin_iptools = "checked"; else $admin_iptools = "";
	if( $user_group[$id]['admin_rssinform'] ) $admin_rssinform = "checked"; else $admin_rssinform = "";
	if( $user_group[$id]['admin_googlemap'] ) $admin_googlemap = "checked"; else $admin_googlemap = "";
	if( $user_group[$id]['allow_subscribe'] ) $allow_subscribe = "checked"; else $allow_subscribe = "";
	if( $user_group[$id]['allow_image_size'] ) $allow_image_size = "checked"; else $allow_image_size = "";
	if( $user_group[$id]['admin_tagscloud'] ) $admin_tagscloud = "checked"; else $admin_tagscloud = "";
	if( $user_group[$id]['admin_complaint'] ) $admin_complaint = "checked"; else $admin_complaint = "";
    if ($user_group[$id]['admin_links']) $admin_links = "checked"; else $admin_links = "";
    if ($user_group[$id]['admin_redirects']) $admin_redirects = "checked"; else $admin_redirects = "";
    if ($user_group[$id]['admin_meta']) $admin_meta = "checked"; else $admin_meta = "";
	if( $user_group[$id]['comments_question'] ) $comments_question = "checked"; else $comments_question = "";
	if( $user_group[$id]['news_question'] ) $news_question = "checked"; else $news_question = "";
	if( $user_group[$id]['pm_question'] ) $pm_question = "checked"; else $pm_question = "";
	if( $user_group[$id]['captcha_feedback'] ) $captcha_feedback = "checked"; else $captcha_feedback = "";
	if( $user_group[$id]['feedback_question'] ) $feedback_question = "checked"; else $feedback_question = "";
	if( $user_group[$id]['force_reg'] ) $force_reg = "checked"; else $force_reg = "";
	if( $user_group[$id]['force_news'] ) $force_news = "checked"; else $force_news = "";
	if( $user_group[$id]['force_comments'] ) $force_comments = "checked"; else $force_comments = "";
	if( $user_group[$id]['force_rating'] ) $force_rating = "checked"; else $force_rating = "";
	if( $user_group[$id]['force_comments_rating'] ) $force_comments_rating = "checked"; else $force_comments_rating = "";
	if( $user_group[$id]['allow_up_image'] ) $allow_up_image = "checked"; else $allow_up_image = "";
	if( $user_group[$id]['allow_up_watermark'] ) $allow_up_watermark = "checked"; else $allow_up_watermark = "";
	if( $user_group[$id]['allow_up_thumb'] ) $allow_up_thumb = "checked"; else $allow_up_thumb = "";
	if( $user_group[$id]['allow_mail_files'] ) $allow_mail_files = "checked"; else $allow_mail_files = "";
	if( $user_group[$id]['video_comments'] ) $allow_video_comments = "checked"; else $allow_video_comments = "";
	if( $user_group[$id]['media_comments'] ) $allow_media_comments = "checked"; else $allow_media_comments = "";
    if( $user_group[$id]['allow_complaint_news'] ) $allow_complaint_news = "checked"; else $allow_complaint_news = "";
    if( $user_group[$id]['allow_complaint_comments'] ) $allow_complaint_comments = "checked"; else $allow_complaint_comments = "";
    if( $user_group[$id]['allow_complaint_orfo'] ) $allow_complaint_orfo = "checked"; else $allow_complaint_orfo = "";

	if( $id == 1 ) $admingroup = "disabled"; else $admingroup = "";
	if( $id == 5 ) $gastgroup = "disabled"; else $gastgroup = "";
	
	$group_list = get_groups( $user_group[$id]['rid'] );
	$force_reg_group = get_groups( $user_group[$id]['force_reg_group'] );
	$force_news_group = get_groups( $user_group[$id]['force_news_group'] );
	$force_comments_group = get_groups( $user_group[$id]['force_comments_group'] );
	$force_rating_group = get_groups( $user_group[$id]['force_rating_group'] );
	$force_comments_rating_group = get_groups( $user_group[$id]['force_comments_rating_group'] );

	$spamfilter_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$spamfilter_sel[$user_group[$id]['spamfilter']] = 'selected="selected"';

	$spampmfilter_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$spampmfilter_sel[$user_group[$id]['spampmfilter']] = 'selected="selected"';

    $self_delete_sel = array('0' => '', '1' => '', '2' => '');
    $self_delete_sel[$user_group[$id]['self_delete']] = 'selected="selected"';

	if( $user_group[$id]['allow_cats'] == "all" ) $allow_cats_value = "selected"; else $allow_cats_value = "";
	$categories_list = CategoryNewsSelection( explode( ',', $user_group[$id]['allow_cats'] ), 0, false );

	if( $user_group[$id]['not_allow_cats'] == "" ) $not_allow_cats_value = "selected"; else $not_allow_cats_value = "";
	$not_allow_cats_list = CategoryNewsSelection( explode( ',', $user_group[$id]['not_allow_cats'] ), 0, false );
		
	if( $user_group[$id]['cat_add'] == "all" ) $cat_add_value = "selected"; else $cat_add_value = "";
	if( $user_group[$id]['cat_allow_addnews'] == "all" ) $cat_allow_addnews_value = "selected"; else $cat_allow_addnews_value = "";

	$cat_add_list = CategoryNewsSelection( explode( ',', $user_group[$id]['cat_add'] ), 0, false );
	$cat_allow_addnews_list = CategoryNewsSelection( explode( ',', $user_group[$id]['cat_allow_addnews'] ), 0, false );
	
	$max_pm_value = $user_group[$id]['max_pm'];
	$max_foto_value = $user_group[$id]['max_foto'];
	$max_signature_value = $user_group[$id]['max_signature'];
	$max_info_value = $user_group[$id]['max_info'];
	$max_pm_day_value = $user_group[$id]['max_pm_day'];
	$max_comment_day_value = $user_group[$id]['max_comment_day'];
    $flood_time_value = $user_group[$id]['flood_time'];
	$max_mail_day_value = $user_group[$id]['max_mail_day'];
	$flood_news_value = $user_group[$id]['flood_news'];
	$max_images_value = $user_group[$id]['max_images'];
    $max_c_negative_value = $user_group[$id]['max_c_negative'];
    $max_n_negative_value = $user_group[$id]['max_n_negative'];
	$max_files_value = $user_group[$id]['max_files'];
	$max_day_news_value = $user_group[$id]['max_day_news'];
	$edit_limit_value = $user_group[$id]['edit_limit'];
	$disable_comments_captcha_value = $user_group[$id]['disable_comments_captcha'];
	$disable_news_captcha_value = $user_group[$id]['disable_news_captcha'];
	$max_file_size_value = $user_group[$id]['max_file_size'];
	$max_edit_days_value = $user_group[$id]['max_edit_days'];
	$force_reg_days = $user_group[$id]['force_reg_days'];
	$force_news_count = $user_group[$id]['force_news_count'];
	$force_comments_count = $user_group[$id]['force_comments_count'];
	$force_rating_count = $user_group[$id]['force_rating_count'];
	$force_comments_rating_count = $user_group[$id]['force_comments_rating_count'];
    $max_downloads = $user_group[$id]['max_downloads'];

	$up_count_image_value = $user_group[$id]['up_count_image'];
	$up_image_side_value = $user_group[$id]['up_image_side'];
	$min_image_side_value = $user_group[$id]['min_image_side'];
	$up_image_size_value = $user_group[$id]['up_image_size'];
	$up_thumb_size_value = $user_group[$id]['up_thumb_size'];
	$max_mail_files_value = $user_group[$id]['max_mail_files'];
	$max_mail_allfiles_value = $user_group[$id]['max_mail_allfiles'];

	if( $action == "add" ) {
		$submit_value = $lang['group_new'];
		$form_title = $lang['group_new1'];
		$form_action = "?mod=usergroup&amp;action=doadd";
		
		$group_name_value = "";
		$group_icon_value = "";
	
	} else {
	
		$group_name_value = htmlspecialchars( stripslashes( $user_group[$id]['group_name'] ), ENT_QUOTES, 'UTF-8' );
		$group_icon_value = htmlspecialchars( stripslashes( $user_group[$id]['icon'] ), ENT_QUOTES, 'UTF-8' );
		
		$form_title = $lang['group_edit1'] . $group_name_value;
		$form_action = "?mod=usergroup&amp;action=doedit&amp;id=" . $id;
		$submit_value = $lang['group_edit'];
	
	}
	
	echoheader( "<i class=\"fa fa-id-card-o position-left\"></i><span class=\"text-semibold\">{$lang['header_groups']}</span>", array('?mod=usergroup' => $lang['header_groups'], '' => $form_title ) );

	echo <<<HTML
<script>
	$(function(){
		$('[data-toggle="tab"]').on('shown.bs.tab', function(e) {
		  var id;
		  id = $(e.target).attr("href");
		  $(id).find(".cat_select").chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
		});
	});
</script>
<form action="{$form_action}" method="post">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="panel panel-default">

		    <div class="panel-heading">
				<ul class="nav nav-tabs nav-tabs-solid">
					<li class="active"><a href="#tabhome" data-toggle="tab"><i class="fa fa-home position-left"></i>{$lang['tabs_gr_all']}</a></li>
					<li><a href="#tabnews" data-toggle="tab"><i class="fa fa-file-text-o position-left"></i>{$lang['tabs_gr_news']}</a></li>
					<li><a href="#tabcomments" data-toggle="tab"><i class="fa fa-comments-o position-left"></i>{$lang['tabs_gr_comments']}</a></li>
					<li><a href="#tabcaptcha" data-toggle="tab"><i class="fa fa-lock position-left"></i>{$lang['tabs_gr_cap']}</a></li>
					<li><a href="#tabadmin" data-toggle="tab"><i class="fa fa-dashboard position-left"></i>{$lang['tabs_gr_admin']}</a></li>
				</ul>
			</div>
			
			
            <div class="table-responsive systemsettings">
                 <div class="tab-content">			
                     <div class="tab-pane active" id="tabhome">
						
<table class="table table-striped">
    <tr>
        <td style="width:58%"><h6 class="media-heading text-semibold">{$lang['group_name']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gtitle']}</span></td>
        <td style="width:42%"><input type="text" dir="auto" class="form-control" name="group_name" value="{$group_name_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_icon']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gicon']}</span></td>
        <td><input type="text" dir="auto" class="form-control" name="group_icon" value="{$group_icon_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_pref']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gpref']}</span></td>
        <td><input type="text" dir="auto" class="form-control" name="group_prefix" value="{$group_prefix_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_suf']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gsuf']}</span></td>
        <td><input type="text" dir="auto" class="form-control" name="group_suffix" value="{$group_suffix_value}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_sdel']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_sdel']}</span></td>
        <td><select name="self_delete" class="uniform">
			<option {$self_delete_sel['0']} value="0">{$lang['opt_sys_r7']}</option>
			<option {$self_delete_sel['1']} value="1">{$lang['opt_sys_r8']}</option>
            <option {$self_delete_sel['2']} value="2">{$lang['opt_sys_r9']}</option>
            </select></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_offline']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_goffline']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_offline" {$allow_offline} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_hic']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gvhide']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_hide" {$allow_hide} value="1"></td>
    </tr>
 	
   <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_svote']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_svoted']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_vote" {$allow_vote} value="1"></td>
    </tr>
	

   <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_apm']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gapm']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_pm" {$allow_pm} value="1" {$gastgroup}></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fpmspam']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_fpmspam']}</span></td>
        <td><select name="spampmfilter" class="uniform">
			<option {$spampmfilter_sel['0']} value="0">{$lang['opt_sys_r1']}</option>
			<option {$spampmfilter_sel['3']} value="3">{$lang['opt_sys_r6']}</option>
            <option {$spampmfilter_sel['2']} value="2">{$lang['opt_sys_r4']}</option>
			<option {$spampmfilter_sel['1']} value="1">{$lang['opt_sys_r5']}</option>
            </select></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_afil']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gafile']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_files" {$allow_files} value="1"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mfil']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_mfile']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_downloads" value="{$max_downloads}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['a_feed']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gafeed']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_feed" {$allow_feed} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['a_search']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gasearch']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_search" {$allow_search} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_tlim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_glimit']}</span></td>
        <td><input class="switch" type="checkbox" name="time_limit" {$time_limit} value="1" {$admingroup}{$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_rlim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_grid']}</span></td>
        <td><select name="rid" class="uniform">
           {$group_list}
            </select></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_reglim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_reglimd']}</span></td>
        <td><input class="switch" type="checkbox" name="force_reg" {$force_reg} value="1" {$admingroup}{$gastgroup}>
			<br /><br />{$lang['force_group_days']}&nbsp; 
			<input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="force_reg_days" value="{$force_reg_days}">
			<br />{$lang['force_group']}&nbsp; 
			<select name="force_reg_group" class="uniform">
           {$force_reg_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_nlim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_nlimd']}</span></td>
        <td><input class="switch" type="checkbox" name="force_news" {$force_news} value="1" {$admingroup}{$gastgroup}>
			<br><br>{$lang['force_group_news']}&nbsp; 
			<input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="force_news_count" value="{$force_news_count}">
			<br>{$lang['force_group']}&nbsp; 
			<select name="force_news_group" class="uniform">
           {$force_news_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_clim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_climd']}</span></td>
        <td><input class="switch" type="checkbox" name="force_comments" {$force_comments} value="1" {$admingroup}{$gastgroup}>
			<br /><br />{$lang['force_group_comm']}&nbsp; 
			<input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="force_comments_count" value="{$force_comments_count}">
			<br />{$lang['force_group']}&nbsp; 
			<select name="force_comments_group" class="uniform">
           {$force_comments_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_ratelim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_ratelimd']}</span></td>
        <td><input class="switch" type="checkbox" name="force_rating" {$force_rating} value="1" {$admingroup}{$gastgroup}>
			<br><br>{$lang['force_group_rating']}&nbsp; 
			<input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="force_rating_count" value="{$force_rating_count}">
			<br>{$lang['force_group']}&nbsp; 
			<select name="force_rating_group" class="uniform">
           {$force_rating_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_rateclim']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_rateclimd']}</span></td>
        <td><input class="switch" type="checkbox" name="force_comments_rating" {$force_comments_rating} value="1" {$admingroup}{$gastgroup}>
			<br><br>{$lang['force_group_rating']}&nbsp; 
			<input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="force_comments_rating_count" value="{$force_comments_rating_count}">
			<br>{$lang['force_group']}&nbsp; 
			<select name="force_comments_rating_group" class="uniform">
           {$force_comments_rating_group}
            </select>
		</td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mpmd']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gmpmd']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_pm_day" value="{$max_pm_day_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mpm']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gmpm']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_pm" value="{$max_pm_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_memd']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_memd']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_mail_day" value="{$max_mail_day_value}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fuf']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_fufd']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_mail_files" {$allow_mail_files} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fuf2']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_fufd2']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_mail_files" value="{$max_mail_files_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fuf3']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_fufd3']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_mail_allfiles" value="{$max_mail_allfiles_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fuf4']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_fufd4']}</span></td>
        <td><input type="text" dir="auto" name="mail_files_type" value="{$mail_files_type_value}" class="form-control"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mfot']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gmphoto']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_foto" value="{$max_foto_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_info']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_info']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_info" value="{$max_info_value}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_acn']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_acnd']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_complaint_news" {$allow_complaint_news} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_acc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_accd']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_complaint_comments" {$allow_complaint_comments} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_aco']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_acod']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_complaint_orfo" {$allow_complaint_orfo} value="1"></td>
    </tr>
</table>
							  

					</div>
                    <div class="tab-pane" id="tabnews" >
<table class="table table-striped">
    <tr>
        <td class="white-line" style="width:58%"><h6 class="media-heading text-semibold">{$lang['group_ct']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gacats']}</span></td>
        <td class="white-line" style="width:42%"><select data-placeholder="{$lang['addnews_cat_sel']}" name="allow_cats[]" style="width:100%; max-width:350px;" class="cat_select" multiple >
<option value="all" {$allow_cats_value}>{$lang['edit_all']}</option>
{$categories_list}
</select></td>
    </tr>

    <tr>
        <td class="white-line" style="width:58%"><h6 class="media-heading text-semibold">{$lang['group_nct']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_nctd']}</span></td>
        <td class="white-line" style="width:42%"><select data-placeholder="{$lang['addnews_cat_sel']}" name="not_allow_cats[]" style="width:100%; max-width:350px;" class="cat_select" multiple >
<option value="" {$not_allow_cats_value}>--</option>
{$not_allow_cats_list}
</select></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_aladdnews']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_galaddnews']}</span></td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_allow_addnews[]" style="width:100%; max-width:350px;" class="cat_select" multiple >
<option value="all" {$cat_allow_addnews_value}>{$lang['edit_all']}</option>
{$cat_allow_addnews_list}
</select></td>
    </tr>

	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_alct']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gadc']}</span></td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_add[]" style="width:100%; max-width:350px;" class="cat_select" multiple >
<option value="all" {$cat_add_value}>{$lang['edit_all']}</option>
{$cat_add_list}
</select></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_shid']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gasr']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_short" {$allow_short} value="1"></td>
    </tr>
	
     <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_poll']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_poll_hint']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_poll" {$allow_poll} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['a_rating']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_garating']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_rating" {$allow_rating} value="1"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_ncr']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_ncr_1']}</span></td>
        <td><input type="text" class="form-control" style="max-width:100px; text-align: center;" name="max_n_negative" value="{$max_n_negative_value}"></td>
    </tr>

     <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_adds']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gaad']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_adds" {$allow_adds} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_moder']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gmod']}</span></td>
        <td><input class="switch" type="checkbox" name="moderation" {$moderation} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_main']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_main_hint']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_main" {$allow_main} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fixed']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gfixed']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_fixed" {$allow_fixed} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_aiu']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_aiud']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_image_upload" {$allow_image_upload} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_image_size']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_image_size']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_image_size" {$allow_image_size} value="1" {$gastgroup}></td>
    </tr>

	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_file']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_filed']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_file_upload" {$allow_file_upload} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_pfile']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_pfiled']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_public_file_upload" {$allow_public_file_upload} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_st']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_st']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_change_storage" {$allow_change_storage} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_images']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_images']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_images" value="{$max_images_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_files']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_files']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_files" value="{$max_files_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_file1']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_file1d']}</span></td>
        <td><input type="text" dir="auto" name="files_type" value="{$files_type_value}" class="form-control"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_maxfile']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_maxfiled']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_file_size" value="{$max_file_size_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_flood_news']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_flood_news']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="flood_news" value="{$flood_news_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_day_news']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_day_news']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_day_news" value="{$max_day_news_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_edit_days']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_edit_days']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_edit_days" value="{$max_edit_days_value}"></td>
    </tr>
</table>

                     </div>
                    <div class="tab-pane" id="tabcomments" >
<table class="table table-striped">
    <tr>
        <td class="white-line" style="width:58%"><h6 class="media-heading text-semibold">{$lang['group_addc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gac']}</span></td>
        <td class="white-line" style="width:42%"><input class="switch" type="checkbox" name="allow_addc" {$allow_addc} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_modc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_modc']}</span>{$warning}</td>
        <td><input class="switch" type="checkbox" name="allow_modc" {$allow_modc} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_fspam']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_fspam']}</span></td>
        <td><select name="spamfilter" class="uniform">
			<option {$spamfilter_sel['0']} value="0">{$lang['opt_sys_r1']}</option>
			<option {$spamfilter_sel['3']} value="3">{$lang['opt_sys_r6']}</option>
            <option {$spamfilter_sel['2']} value="2">{$lang['opt_sys_r4']}</option>
			<option {$spamfilter_sel['1']} value="1">{$lang['opt_sys_r5']}</option>
            </select></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['c_rating']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gcrating']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_comments_rating" {$allow_comments_rating} value="1"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_ncr']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_ncr']}</span></td>
        <td><input type="text" class="form-control" style="max-width:100px; text-align: center;" name="max_c_negative" value="{$max_c_negative_value}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_subs']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_subsd']}</span>{$warning_1}</td>
        <td><input class="switch" type="checkbox" name="allow_subscribe" {$allow_subscribe} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_signature']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_signature']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_signature" {$allow_signature} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_max_signature']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_max_signature']}</span></td>
        <td><input type="text" class="form-control" style="max-width:100px; text-align: center;" name="max_signature" value="{$max_signature_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_url']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_group_url']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_url" {$allow_url} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_image']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_group_image']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_image" {$allow_image} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_up_image']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_group_up_image']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_up_image" {$allow_up_image} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_up_count_image']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_up_count_image']}</span></td>
        <td><input type="text" class="form-control" style="max-width:100px; text-align: center;" name="up_count_image" value="{$up_count_image_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_minside']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['xfield_xi22']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="min_image_side" value="{$min_image_side_value}"></td>
    </tr>	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['xfield_xi1']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['xfield_xi2']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="up_image_side" value="{$up_image_side_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['xfield_xi3']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['xfield_xi4']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="up_image_size" value="{$up_image_size_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['xfield_xi5']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_up_watermark']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_up_watermark" {$allow_up_watermark} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['xfield_xi6']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_up_thumb']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_up_thumb" {$allow_up_thumb} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['xfield_xi7']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['xfield_xi8']}</span></td>
        <td><input type="text" class="form-control" style="max-width:100px; text-align: center;" name="up_thumb_size" value="{$up_thumb_size_value}"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_vic']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_vicd']}</span></td>
        <td><input class="switch" type="checkbox" name="video_comments" {$allow_video_comments} value="1"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mic']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_micd']}</span></td>
        <td><input class="switch" type="checkbox" name="media_comments" {$allow_media_comments} value="1"></td>
    </tr>

    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_editc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gec']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_editc" {$allow_editc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_delc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gdc']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_delc" {$allow_delc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_edit_limit']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_edit_limit']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="edit_limit" value="{$edit_limit_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_mcmd']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gmcmd']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="max_comment_day" value="{$max_comment_day_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_af']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_afd']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="flood_time" value="{$flood_time_value}"></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_allc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gaec']}</span></td>
        <td><input class="switch" type="checkbox" name="edit_allc" {$edit_allc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_dllc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gadcom']}</span></td>
        <td><input class="switch" type="checkbox" name="del_allc" {$del_allc} value="1" {$gastgroup}></td>
    </tr>
</table>						

                     </div>
                    <div class="tab-pane" id="tabcaptcha" >
<table class="table table-striped">
     <tr>
        <td class="white-line" style="width:58%"><h6 class="media-heading text-semibold">{$lang['opt_sys_news_c']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_scode']}</span></td>
        <td class="white-line" style="width:42%"><input class="switch" type="checkbox" name="news_sec_code" {$news_sec_code} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_qsn']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_qcode']}</span></td>
        <td><input class="switch" type="checkbox" name="news_question" {$news_question} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_d_nc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_d_nc']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="disable_news_captcha" value="{$disable_news_captcha_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_code_com']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_codecd']}</span></td>
        <td><input class="switch" type="checkbox" name="captcha" {$allow_captcha} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_qsc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_qcode_1']}</span></td>
        <td><input class="switch" type="checkbox" name="comments_question" {$comments_question} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_d_cc']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_d_cc']}</span></td>
        <td><input type="text" dir="auto" class="form-control" style="max-width:100px; text-align: center;" name="disable_comments_captcha" value="{$disable_comments_captcha_value}"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_code_pm']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_code_pmd']}</span></td>
        <td><input class="switch" type="checkbox" name="captcha_pm" {$allow_captcha_pm} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_qspm']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_qcode_2']}</span></td>
        <td><input class="switch" type="checkbox" name="pm_question" {$pm_question} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_code_feed']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['opt_sys_code_feedd']}</span></td>
        <td><input class="switch" type="checkbox" name="captcha_feedback" {$captcha_feedback} value="1"></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['opt_sys_qsfeed']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_qcode_3']}</span></td>
        <td><input class="switch" type="checkbox" name="feedback_question" {$feedback_question} value="1"></td>
    </tr>
</table>						
                     </div>
                    <div class="tab-pane" id="tabadmin" >

<table class="table table-striped">
    <tr>
        <td class="white-line" style="width:58%"><h6 class="media-heading text-semibold">{$lang['group_aadm']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gadmin']}</span></td>
        <td class="white-line" style="width:42%"><input class="switch" type="checkbox" name="allow_admin" {$allow_admin} value="1" {$gastgroup}{$admingroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_addnews']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_addnews']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_addnews" {$admin_addnews} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_editnews']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_editnews']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_editnews" {$admin_editnews} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_edit2']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gned']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_edit" {$allow_edit} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_edit3']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['hint_gnaed']}</span></td>
        <td><input class="switch" type="checkbox" name="allow_all_edit" {$allow_all_edit} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_comments']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_comments']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_comments" {$admin_comments} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_categories']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_categories']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_categories" {$admin_categories} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_editusers']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_editusers']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_editusers" {$admin_editusers} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_wordfilter']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_wordfilter']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_wordfilter" {$admin_wordfilter} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_xfields']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_xfields']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_xfields" {$admin_xfields} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_userfields']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_userfields']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_userfields" {$admin_userfields} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_static']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_static']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_static" {$admin_static} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_editvote']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_editvote']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_editvote" {$admin_editvote} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_newsletter']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_newsletter']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_newsletter" {$admin_newsletter} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_blockip']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_blockip']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_blockip" {$admin_blockip} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_banners']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_banners']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_banners" {$admin_banners} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_rss']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_rss']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_rss" {$admin_rss} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_iptools']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_iptools']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_iptools" {$admin_iptools} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_rssinform']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_rssinform']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_rssinform" {$admin_rssinform} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_googlemap']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_googlemap']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_googlemap" {$admin_googlemap} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_tagscloud']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_tagscloud']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_tagscloud" {$admin_tagscloud} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_complaint']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_complaint']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_complaint" {$admin_complaint} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_meta']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_meta']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_meta" {$admin_meta} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_links']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_links']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_links" {$admin_links} value="1" {$gastgroup}></td>
    </tr>
    <tr>
        <td><h6 class="media-heading text-semibold">{$lang['group_a_redirects']}</h6><span class="text-muted text-size-small hidden-xs">{$lang['group_h_redirects']}</span></td>
        <td><input class="switch" type="checkbox" name="admin_redirects" {$admin_redirects} value="1" {$gastgroup}></td>
    </tr>  
</table>						
                     </div>
				</div>
			</div>
</div>

<div class="mb-20">
	<button type="submit" class="btn bg-teal btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$submit_value}</button>
</div>
</form>
HTML;
	
	echofooter();
} else {
	
	echoheader( "<i class=\"fa fa-id-card-o position-left\"></i><span class=\"text-semibold\">{$lang['header_groups']}</span>", $lang['header_groups_1'] );
	
	$db->query( "SELECT user_group, count(*) as count FROM " . USERPREFIX . "_users GROUP BY user_group" );
	$entries = "";

	while ( $row = $db->get_row() )
		$count_list[$row['user_group']] = $row['count'];
	$db->free();
	
	foreach ( $user_group as $group ) {
		$count = number_format( isset($count_list[$group['id']]) ? intval( $count_list[$group['id']] ) : 0, 0, ',', ' ');

		if ( $group['id'] > 5 ) {
			$dlink="<li><a onclick=\"javascript:confirmdelete('{$group['id']}'); return(false);\" href=\"#\"><i class=\"fa fa-trash-o position-left text-danger\"></i>{$lang['group_sel2']}</a></li>";
		} else {
			$dlink="<li><a href=\"#\"><i class=\"fa fa-trash-o position-left text-danger\"></i>{$lang['group_sel3']}</a></li>";		
		}
		
		if( $group['allow_admin'] ) $group['group_name'] .= " (<span class=\"text-danger\">{$lang['have_adm']}</span>)";
		
		$menu_link = <<<HTML
        <div class="btn-group">
          <a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
          <ul class="dropdown-menu text-left dropdown-menu-right">
            <li><a href="?mod=usergroup&action=edit&id={$group['id']}"><i class="fa fa-pencil-square-o position-left"></i>{$lang['group_sel1']}</a></li>
			<li class="divider"></li>
            {$dlink}
          </ul>
        </div>
HTML;

		$entries .= "
    <tr>
    <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=usergroup&action=edit&id={$group['id']}'; return false;\">{$group['id']}</td>
    <td class=\"cursor-pointer\" onclick=\"document.location = '?mod=usergroup&action=edit&id={$group['id']}'; return false;\">{$group['group_name']}</td>
    <td class=\"text-center cursor-pointer\" onclick=\"document.location = '?mod=usergroup&action=edit&id={$group['id']}'; return false;\">{$count}</td>
    <td>{$menu_link}</td>
     </tr>";
	}
	
	echo <<<HTML
<script>
<!--
function confirmdelete(id){
	DLEconfirmDelete( '{$lang['usergroup_del']}', '{$lang['p_confirm']}', function () {
		document.location="?mod=usergroup&action=del&user_hash={$dle_login_hash}&id="+id;
	} );
}
//-->
</script>
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['group_list']}
  </div>

    <table class="table table-xs table-hover">
      <thead>
      <tr>
        <th style="width: 3.75rem">ID</th>
        <th>{$lang['group_name']}</th>
        <th class="text-center">{$lang['group_sel4']}</th>
        <th style="width: 4.375rem"></th>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>

	<div class="panel-footer">
		<button class="btn bg-teal btn-sm btn-raised" type="button" onclick="document.location='?mod=usergroup&action=selectgroup'"><i class="fa fa-plus-circle position-left"></i>{$lang['group_sel5']}</button>
	</div>	

</div>	
HTML;
	
	echofooter();
}
?>