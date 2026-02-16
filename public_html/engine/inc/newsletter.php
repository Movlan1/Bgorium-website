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
 File: newsletter.php
-----------------------------------------------------
 Use: Sending newsletter messages
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_newsletter'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if (isset ($_REQUEST['type'])) $type = htmlspecialchars( $_REQUEST['type'], ENT_QUOTES, 'UTF-8' ); else $type = "";
if (isset ($_REQUEST['action'])) $action = htmlspecialchars( $_REQUEST['action'], ENT_QUOTES, 'UTF-8' ); else $action = "";
if (isset ($_REQUEST['a_mail'])) $a_mail = intval($_REQUEST['a_mail']); else $a_mail = "";

if (isset ($_GET['empfanger'])) {

	$empfanger = array ();

	if( !count( $_GET['empfanger'] ) ) {
		$empfanger[] = '0';
	} else {

		foreach ( $_GET['empfanger'] as $value ) {
			$empfanger[] = intval($value);
		}

	}

	if ( $empfanger[0] ) $empfanger = $db->safesql( implode( ',', $empfanger ) ); else $empfanger = "0";

} else $empfanger = "0";

if ($action=="send") {
	
	include_once(DLEPlugins::Check(ENGINE_DIR . '/skins/default.skin.php'));

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		msg( "error", $lang['addnews_error'], $lang['sess_error'], "javascript:history.go(-1)" );
	}

	$parse = new ParseFilter();

	$title = strip_tags(stripslashes($parse->process($_POST['title'])));
	$message = stripslashes($parse->process($_POST['message']));
	$start_from = intval($_GET['start_from']);
	$limit = intval($_GET['limit']);
	$interval = intval($_GET['interval']) * 1000;

	if ($limit < 1) {

		$limit = 20;

	}

	$message = $parse->BB_Parse($message);
	
	if( isset($_GET['toregdate']) ) {
		
		$toregdate = intval(strtotime( (string)$_GET['toregdate'] ));
		
	} else $toregdate = 0;

	if( isset($_GET['fromregdate']) ) {
		
		$fromregdate = intval(strtotime( (string)$_GET['fromregdate'] ));
		
	} else $fromregdate = 0;	

	if( isset($_GET['fromentdate']) ) {
		
		$fromentdate = intval(strtotime( (string)$_GET['fromentdate'] ));
		
	} else $fromentdate = 0;	

	if( isset($_GET['toentdate']) ) {
		
		$toentdate = intval(strtotime( (string)$_GET['toentdate'] ));
		
	} else $toentdate = 0;
	
	$where = array();

	$where[] = "banned != 'yes'";

	if ($empfanger) {
	
		$user_list = array(); 
	
		$temp = explode(",", $empfanger); 
	
		foreach ( $temp as $value ) {
			$user_list[] = intval($value);
		}
	
		$user_list = implode( "','", $user_list );
	
		$user_list = "user_group IN ('" . $user_list . "')";
	
	} else $user_list = false;
	
	if( $fromregdate ) {
		$where[] = "reg_date>='" . $fromregdate . "'";
	}
	if( $toregdate ) {
		$where[] = "reg_date<='" . $toregdate . "'";
	}
	if( $fromentdate ) {
		$where[] = "lastdate>='" . $fromentdate . "'";
	}
	if( $toentdate ) {
		$where[] = "lastdate<='" . $toentdate . "'";
	}
	
	if ($user_list) $where[] = $user_list;
	if ($a_mail AND $type == "email") $where[] = "allow_mail = '1'";

	if (count($where)) $where = " WHERE ".implode (" AND ", $where);
	else $where = "";
	
	$row = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_users".$where);

	if ($start_from > $row['count'] OR $start_from < 0) $start_from = 0;

	if ($type == "email")
		$type_send = $lang['bb_b_mail'];
	else
		$type_send = $lang['nl_pm'];

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '47', '{$type_send}')" );

	$css = build_css($css_array);

echo <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}"{$html_class}>
<head>
	<meta charset="utf-8">
	<title>DataLife Engine - {$lang['nl_seng']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	{$css}
	<script src="engine/classes/js/jquery.js"></script>
</head>
<body{$body_class}>
<script>
var total = {$row['count']};

	$(function() {

		$('#button').click(function() {
			$('#status').html('{$lang['nl_sinfo']}');
			$('#button').attr("disabled", "disabled");
			$('#button').val("{$lang['send_forw']}");

			senden( $('#sendet_ok').val() );
			return false;
		});
		
		if(total == 0) {
			$('#button').attr("disabled", "disabled");
		}

	});

function senden( startfrom ){

	var title = $('#title').html();
	var message = $('#message').html();
	
	$('#ajaxerror').html('');

	try {

		$.post("engine/ajax/controller.php?mod=newsletter", { startfrom: startfrom, title: title, message: message, user_hash: '{$dle_login_hash}', type: '{$type}', empfanger: '{$empfanger}', a_mail: '{$a_mail}', limit: '{$limit}', fromregdate: '{$fromregdate}', toregdate: '{$toregdate}', fromentdate: '{$fromentdate}', toentdate: '{$toentdate}'  },
			function(data){

				if (data) {

					if (data.status == "ok") {

						$('#gesendet').html(data.count);
						$('#sendet_ok').val(data.count);

						var proc = Math.round( (100 * data.count) / total );

						if ( proc > 100 ) proc = 100;

						$('.progress-bar').css( "width", proc + '%' );

						if (data.count >= total || data.complete == 1) 
						{
							$('#status').html('{$lang['nl_finish']}');
						}
						else 
						{ 
							setTimeout("senden(" + data.count + ")", {$interval} );
						}


					}

				}
			}, "json").fail(function(jqXHR, textStatus, errorThrown ) {

					var error_status = '';
					var startagain = parseInt($('#sendet_ok').val());
					startagain = startagain + {$limit};
				
					if (jqXHR.status < 200 || jqXHR.status >= 300) {
					error_status = 'HTTP Error: ' + jqXHR.status;
					} else {
						error_status = 'Invalid JSON: ' + jqXHR.responseText;
					}
			
					$('#sendet_ok').val( startagain );
					$('#status').html('{$lang['nl_error']}');
					$('#ajaxerror').html('<div class="alert alert-danger alert-styled-left alert-bordered">' + error_status + '</div>');
					$('#button').attr("disabled", false);
				
			});

	} catch (err) {

		var startagain = parseInt($('#sendet_ok').val());
		startagain = startagain + {$limit};

		$('#sendet_ok').val( startagain );
		$('#status').html('{$lang['nl_error']}');
		$('#button').attr("disabled", false);

		$('#ajaxerror').html('<div class="alert alert-danger alert-styled-left alert-bordered">' + err.message + '</div>');

	}

	return false;
}
</script>
<div class="p-5">
<div class="panel panel-default m-20">
  <div class="panel-heading">
    {$lang['nl_seng']}
  </div>
  <div class="panel-body">

<table width="100%">
    <tr>
        <td style="width:8.125rem">{$lang['nl_empf']}</td>
        <td>{$row['count']}</td>
    </tr>
    <tr>
        <td>{$lang['nl_type']}</td>
        <td>{$type_send}</td>
    </tr>
    <tr>
        <td colspan="2">
		<div class="progress">
          <div class="progress-bar progress-blue" style="width:0%;"><span></span></div>
        </div>
		{$lang['nl_sendet']} <span style="color:red;" id='gesendet'>{$start_from}</span> {$lang['mass_i']} <span style="color:blue;">{$row['count']}</span> {$lang['nl_status']} <span id="status"></span>
		</td>
    </tr>
</table>
	</div>
	<div class="panel-body">
		<div id="ajaxerror"></div>
		<div class="text-muted text-size-small">{$lang['nl_info']}</div>
	</div>	
	<div class="panel-footer">
	<button id="button" type="button" class="btn bg-teal btn-sm btn-raised"><i class="fa fa-paper-plane-o position-left"></i>{$lang['btn_send']}</button>
	<input type="hidden" id="sendet_ok" name="sendet_ok" value="{$start_from}">
	</div>	
</div>
</div>
HTML;

$message = stripslashes($message);

echo <<<HTML
<pre style="display:none;" id="title">{$title}</pre>
<pre style="display:none;" id="message">{$message}</pre>
</body>

</html>
HTML;

} elseif ($action=="preview") {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		msg( "error", $lang['addnews_error'], $lang['sess_error'], "javascript:history.go(-1)" );
	}
	
	$parse = new ParseFilter();

	$title = strip_tags(stripslashes($parse->process($_POST['title'])));
	$message = stripslashes($parse->process($_POST['message']));
	
	$message = $parse->BB_Parse($message);

	$message = stripslashes($message);
	
	include_once(DLEPlugins::Check(ENGINE_DIR . '/skins/default.skin.php'));

	$css = build_css($css_array);

	echo <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}"{$html_class}>
<head>
	<meta charset="utf-8">
	<title>DataLife Engine - {$lang['nl_seng']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	{$css}
</head>
<style>
h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    font-weight: bold;
    margin-top: 0.625rem;
    margin-bottom: 0.625rem;
}
</style>
<body{$body_class}>
<div class="p-5">
	<div class="panel panel-default m-20">
	<div class="panel-heading">
	{$title}
	</div>
	<div class="panel-body">
		{$message}
		</div>
	</div>
</div>
</body>

</html>
HTML;

die();

} elseif ($action=="message") {

	
	$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";

	echoheader( "<i class=\"fa fa-envelope-o position-left\"></i><span class=\"text-semibold\">{$lang['main_newsl']}</span>", $lang['header_ne_1'] );

    echo "
    <script>
    function send(){
	
		tinyMCE.triggerSave();
	 
		if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEPush.error('$lang[vote_alert]'); }
		else{
			dd=window.open('','snd','height=400,width=780, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no');
			document.addnews.action.value='send';document.addnews.target='snd'
			document.addnews.submit();dd.focus()
		}
    }
    </script>";

    echo "
    <script>
    function preview(){
	
		tinyMCE.triggerSave();
		
		if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEPush.error('$lang[vote_alert]'); }
		else{
			var width  = 770;
			var height = 450;
			var left   = (screen.width  - width)/2;
			var top    = (screen.height - height)/2;

			dd=window.open('','prv','width='+width+', height='+height+', top='+top+', left='+left+', directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no');
			document.addnews.action.value='preview';document.addnews.target='prv';
			document.addnews.submit();dd.focus();
			setTimeout(\"document.addnews.action.value='send';document.addnews.target='_self'\",500);
		}
    }
    </script>";

	$start_from = intval($_GET['start_from']);

echo <<<HTML
<form method="POST" name="addnews" id="addnews" action="" class="form-horizontal">
<input type="hidden" name="mod" value="newsletter">
<input type="hidden" name="action" value="send">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="a_mail" value="{$a_mail}">
<input type="hidden" name="start_from" value="{$start_from}">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="alert alert-info alert-styled-left alert-arrow-left alert-component text-size-small">{$lang['nl_info_1']} {$lang['nl_info_2']}</div>
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['nl_main']}
	<div class="heading-elements">
	    <ul class="icons-list">
			<li><a href="#" class="panel-fullscreen"><i class="fa fa-expand"></i></a></li>
		</ul>
    </div>
  </div>
  <div class="panel-body">
	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['edit_title']}</label>
		  <div class="col-md-10">
			<input type="text" dir="auto" class="form-control width-550" name="title" maxlength="160">
		  </div>
		 </div>	
		<div class="form-group editor-group">
		  <label class="control-label col-md-2">{$lang['nl_message']}</label>
		  <div class="col-md-10">
HTML;
		
		include(DLEPlugins::Check(ENGINE_DIR.'/editor/newsletter.php'));

echo <<<HTML
		  </div>
		</div>
	
   </div>
   <div class="panel-footer">
	<button type="button" onclick="send(); return false;" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-paper-plane-o position-left"></i>{$lang['btn_send']}</button>
	<button onclick="preview(); return false;" class="btn bg-slate-600 btn-sm btn-raised"><i class="fa fa-desktop position-left"></i>{$lang['btn_preview']}</button>
   </div>
</div>		
</form>
HTML;

  echofooter();
} else {

	echoheader( "<i class=\"fa fa-envelope-o position-left\"></i><span class=\"text-semibold\">{$lang['main_newsl']}</span>", $lang['header_ne_1'] );
	$group_list = get_groups ();

echo <<<HTML
<form method="GET" action="" class="form-horizontal">
<input type="hidden" name="mod" value="newsletter">
<input type="hidden" name="action" value="message">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['nl_main']}
  </div>
  <div class="panel-body">
	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['nl_type']}</label>
		  <div class="col-md-10 col-sm-9">
			<select class="uniform" name="type">
           <option value="email">{$lang['bb_b_mail']}</option>
          <option value="pm">{$lang['nl_pm']}</option></select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['nl_empf']}</label>
		  <div class="col-md-10 col-sm-9">
			<select data-placeholder="{$lang['group_select_1']}" name="empfanger[]" class="empfangerselect" multiple>
           <option value="all" selected>{$lang['edit_all']}</option>
           {$group_list}
		   </select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['edit_regdate']}</label>
		  <div class="col-md-10 col-sm-9">
			{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="fromregdate" id="fromregdate" class="form-control" style="width:130px;" value="" autocomplete="off">
			{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="toregdate" id="toregdate" class="form-control" style="width:130px;" value="" autocomplete="off">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['edit_entedate']}</label>
		  <div class="col-md-10 col-sm-9">
			{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="fromentdate" id="fromentdate" class="form-control" style="width:130px;" value="" autocomplete="off">
			{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="toentdate" id="toentdate" class="form-control" style="width:130px;" value="" autocomplete="off">
		   </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['nl_startfrom']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control text-center" style="width:60px;" name="start_from" value="0"> {$lang['nl_user']}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['nl_n_mail']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control text-center" style="width:60px;" name="limit" value="20">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['nl_interval']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" class="form-control text-center" style="width:60px;" name="interval" value="3">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3"></label>
		  <div class="col-md-10 col-sm-9">
			<div class="checkbox"><label><input type="checkbox" name="a_mail" value="1" class="icheck" checked>{$lang['nl_amail']}</label></div>
		  </div>
		 </div>
	
   </div>
   <div class="panel-footer">
	<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-step-forward position-left"></i>{$lang['edit_next']}</button>
   </div>
</div>
</form>
<script>
	$(function(){
		$('.empfangerselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
</script>
HTML;

  echofooter();
}
?>