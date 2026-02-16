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
 File: editusers.php
-----------------------------------------------------
 Use: Edit Users
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$id = isset($_REQUEST['id']) ? intval( $_REQUEST['id'] ) : 0;

if(!isset($_SESSION['admin_user_referrer'])) {
	$_SESSION['admin_user_referrer'] = '?mod=editusers&amp;action=list';
}

if( !$action ) $action = "list";

if( !$langformatdate ) $langformatdate = "d.m.Y";
if( !$langformatdatefull ) $langformatdatefull = "d.m.Y H:i";

if( $action == "list" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", $lang['index_denied'], $lang['index_denied'] );
	}
	
	$_SESSION['admin_user_referrer'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');

	echoheader( "<i class=\"fa fa-user-circle-o position-left\"></i><span class=\"text-semibold\">{$lang['user_head']}</span>", $lang['opt_user'] );

	$wait_for_delete = ''; $i = 0;

	$db->query("SELECT u.* FROM " . USERPREFIX . "_users_delete d LEFT JOIN " . USERPREFIX . "_users u ON (d.user_id=u.user_id)");

	while ($row = $db->get_row()) {
		$i ++;
		$last_login = langdate($langformatdatefull, $row['lastdate']);
		$user_name = "<a href=\"?mod=editusers&action=edituser&id={$row['user_id']}\">" . $row['name'] . "</a>";

		if ($row['news_num'] == 0) {

			$news_link = "$row[news_num]";

		} else {

			$row['name'] = urlencode($row['name']);

			if ($config['allow_alt_url']) {

				$url_user = $config['http_home_url'] . "user/" . urlencode($row['name']) . "/news/";
			} else {

				$url_user = $config['http_home_url'] . "index.php?subaction=allnews&user=" . $row['name'];
			}

			$row['news_num'] = number_format($row['news_num'], 0, ',', ' ');

			$news_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['rss_maxnews']}" class="tip"><b>{$row['news_num']}</b></a>
				  <ul class="dropdown-menu text-left dropdown-menu-right">
				   <li><a href="{$url_user}" target="_blank"><i class="fa fa-eye position-left"></i>{$lang['comm_view']}</a></li>
				   <li><a href="#" onclick="javascript:nchange('{$row['user_id']}'); return false;"><i class="fa fa-pencil-square-o position-left"></i>{$lang['change_news_user']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:ndelete('{$row['user_id']}','only'); return false;" href="?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id={$row['user_id']}&moderation=only"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['news_mdel']}</a></li>
				   <li><a onclick="javascript:ndelete('{$row['user_id']}',''); return false;" href="?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}

		if ($row['comm_num'] == 0) {
			$comms_link = $row['comm_num'];
		} else {

			$row['comm_num'] = number_format($row['comm_num'], 0, ',', ' ');

			$comms_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="tip"><b>{$row['comm_num']}</b></a>
				  <ul class="dropdown-menu text-left dropdown-menu-right">
				   <li><a href="{$config['http_home_url']}index.php?do=lastcomments&userid={$row['user_id']}" target="_blank"><i class="fa fa-eye position-left"></i>{$lang['comm_view']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:cdelete('{$row['user_id']}','only'); return(false)" href="?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id={$row['user_id']}&moderation=only"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['news_mdel']}</a></li>
				   <li><a onclick="javascript:cdelete('{$row['user_id']}',''); return(false)" href="?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}

		$user_delete = "<li class=\"divider\"></li><li><a onclick=\"javascript:confirmdelete('" . $row['user_id'] . "', '" . $row['name'] . "', 'self_delete_user'); return(false)\" href=\"#\"><i class=\"fa fa-trash-o position-left text-danger\"></i>{$lang['user_del']}</a></li>";

		if ($row['banned'] == 'yes') $user_level = "<span class=\"text-danger\">" . $lang['user_ban'] . "</span>";
		else $user_level = $user_group[$row['user_group']]['group_prefix'] . $user_group[$row['user_group']]['group_name'] . $user_group[$row['user_group']]['group_suffix'] . "<a href=\"?mod=usergroup&action=edit&id={$row['user_group']}\" target=\"_blank\" data-popup=\"tooltip\" title=\"{$lang['group_edit1']} {$user_group[$row['user_group']]['group_name']}\"><i class=\"fa fa-external-link position-left position-right\" style=\"font-size: 12px;\"></i></a>";

		if ($row['user_group'] == 1) $user_delete = "";

		$pmname = urlencode($row['name']);

		$menu_link = <<<HTML
       <div class="btn-group">
				<a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
				<ul class="dropdown-menu text-left dropdown-menu-right">
				  <li><a href="{$config['http_home_url']}index.php?subaction=userinfo&user={$pmname}" target="_blank"><i class="fa fa-external-link position-left"></i>{$lang['header_profile']}</a></li>
				  <li><a onclick="sendNotice('{$row['user_id']}');  return false;" href="?mod=editusers&action=dorejectrequests&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-retweet position-left"></i>{$lang['selfdel_wait_3']}</a></li>
				  {$user_delete}
				</ul>
        </div>
HTML;
		$avatar = "";

		if (count(explode("@", $row['foto'])) == 2) {
			$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
		} else {

			if ($row['foto']) {

				if (strpos($row['foto'], "//") === 0) $avatar = "http:" . $row['foto'];
				else $avatar = $row['foto'];

				$avatar = @parse_url($avatar);

				if ($avatar['host']) {
					$avatar = $row['foto'];
				} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];
			}
		}

		if($avatar) {
			$avatar = "<img src=\"{$avatar}\" class=\"img-circle img-responsive hidden-xs\">";
		} else {
			$avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-responsive hidden-xs" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
		}

		$wait_for_delete .= "<tr>
        <td><div class=\"user-list\">{$avatar}<h6>{$user_name}</h6><span class=\"text-size-small\">{$user_level}</span></div></td>
        <td class=\"hidden-xs\">";

		$wait_for_delete .= langdate($langformatdatefull, $row['reg_date']);

		$wait_for_delete .= "</td>
        <td class=\"hidden-xs\">$last_login</td>
        <td class=\"hidden-xs text-nowrap text-center\">{$news_link}</td>
        <td class=\"hidden-xs text-nowrap text-center\">{$comms_link}</td>
        <td class=\"text-center\">{$menu_link}</td>
		<td class=\"hidden-xs\"><input name=\"selected_users[]\" value=\"{$row['user_id']}\" type=\"checkbox\" class=\"icheck\"></td>
        </tr>";

	}
	

	if( $wait_for_delete ) {
		echo <<<HTML
<form method="post" name="editdeleteusers" id="editdeleteusers">
<input type="hidden" name=mod value="mass_user_actions">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<input type="hidden" name="self_delete_user" value="self_delete_user">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['selfdel_wait_2']} ({$i})
  </div>

    <table class="table table-xs table-hover">
      <thead>
      <tr>
        <th>{$lang['user_name']}</th>
        <th class="hidden-xs">{$lang['user_reg']}</th>
        <th class="hidden-xs">{$lang['user_last']}</th>
        <th class="hidden-xs text-center" style="width: 40px"><i class="fa fa-file-text-o  tip" data-original-title="{$lang['rss_maxnews']}"></i></th>
        <th class="hidden-xs text-center" style="width: 40px"><i class="fa fa-comments-o tip" data-original-title="{$lang['edit_com']}"></i></th>
        <th style="width: 70px">&nbsp;</th>
        <th class="hidden-xs" style="width: 40px"><input type="checkbox" name="master_box" class="icheck" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all_self()"></th>
      </tr>
      </thead>
	  <tbody>
		{$wait_for_delete}
	  </tbody>
	</table>
	<div class="panel-footer hidden-xs">
		<div class="pull-right">
		<select class="uniform" name="action" id="mass_self_action">
			<option value="">{$lang['edit_selact']}</option>
			<option value="mass_delete_comments">{$lang['massusers_comments']}</option>
			<option value="mass_rejectrequests">{$lang['selfdel_wait_3']}</option>
			<option value="mass_delete">{$lang['massusers_delete']}</option>
			</select>&nbsp;<input class="btn bg-brown-600 btn-sm btn-raised" type="submit" value="{$lang['b_start']}">
		</div>
	</div>
</div>
</form>
<script>
    function sendNotice( id, mass = false ){
		var b = {};

		b[dle_act_lang[3]] = function() {
			$(this).dialog('close');
		};

		b['{$lang['p_send']}'] = function() {

			if ( $('#dle-promt-text').val().length < 1) {

				$('#dle-promt-text').addClass('ui-state-error');

			} else {
				var response = $('#dle-promt-text').val()
				
				$(this).dialog('close');
				$('#dlepopup').remove();

				if( mass ) {

					$('#mass_message').remove();
					$('#editdeleteusers').append('<input type="hidden" name="text" id="mass_message" value="' + response + '">');
					$('#editdeleteusers').off('submit');
					HTMLFormElement.prototype.submit.call(document.getElementById('editdeleteusers'));

					return false;
				}

				ShowLoading('');

				$.post('?mod=editusers&action=dorejectrequests', { id: id,  text: response, user_hash: '{$dle_login_hash}' }, function(data){

					HideLoading('');
					
					if (data == 'ok') { 
						document.location='?mod=editusers';
					}

				});

			}
		};

		$('#dlepopup').remove();

		$('body').append("<div id='dlepopup' class='dle-promt' title='{$lang['p_title']}' style='display:none'>{$lang['selfdel_text']}<br><br><textarea dir='auto' name='dle-promt-text' id='dle-promt-text' class='classic' style='width:100%;height:100px; padding: .4em;'></textarea></div>");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			resizable: false,
			buttons: b
		});

	}
$(function(){
	$('#editdeleteusers').submit(function(event) {

		if($('#mass_self_action').val() == 'mass_rejectrequests') {
		
			event.preventDefault();
			
			sendNotice('', true);

			return false;
		}
		
		return true;
		
	});

});
</script>

HTML;

	}

	
	echo '<script>
	function confirmdelete(id, user, self_delete_user = false){
	    DLEconfirmDelete( "' . $lang['user_deluser'] . '", "' . $lang['p_confirm'] . '", function () {
			var url = "?mod=editusers&user_hash=' . $dle_login_hash . '&action=dodeleteuser&id="+id+"&user="+user;

			if( self_delete_user ) {
				url = url + "&self_delete_user="+self_delete_user;
			}

		    document.location=url;
		} );
    }
    function clearform(frm){

		for (var i=0;i<frm.length;i++) {
			var el=frm.elements[i];
			if (el.type=="checkbox" || el.type=="radio") { el.checked=0; continue; }
			if ((el.type=="text") || (el.type=="textarea") || (el.type == "password")) { el.value=""; continue; }
			if ((el.type=="select-one") || (el.type=="select-multiple")) { el.selectedIndex=0; }
		}

    	document.searchform.start_from.value="";
    }

    function list_submit(prm){
      document.searchform.start_from.value=prm;
      document.searchform.submit();
      return false;
    }
	
	$(function(){
		$(".groupselect").chosen({allow_single_deselect:true, no_results_text: "' .$lang['addnews_cat_fault']. '"});

		const search_form = document.getElementById("searchform");

		function handleInputChange(event) {
			document.searchform.start_from.value="";
		}

		search_form.addEventListener("input", handleInputChange);

	});
	
    // end -->
    </script>';

	$grouplist = get_groups( 4 );
	$group_list = get_groups();

	$_REQUEST['toregdate'] = isset($_REQUEST['toregdate']) ? $_REQUEST['toregdate'] : '';
	$_REQUEST['fromregdate'] = isset($_REQUEST['fromregdate']) ? $_REQUEST['fromregdate'] : '';
	$_REQUEST['fromentdate'] = isset($_REQUEST['fromentdate']) ? $_REQUEST['fromentdate'] : '';
	$_REQUEST['toentdate'] = isset($_REQUEST['toentdate']) ? $_REQUEST['toentdate'] : '';
	
	$toregdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['toregdate'] ) ) ) );
	$fromregdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['fromregdate'] ) ) ) );
	$fromentdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['fromentdate'] ) ) ) );
	$toentdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['toentdate'] ) ) ) );

	$search_news_f = isset($_REQUEST['search_news_f']) ? intval( $_REQUEST['search_news_f'] ) : 0;
	$search_news_t = isset($_REQUEST['search_news_t']) ? intval( $_REQUEST['search_news_t'] ) : 0;
	$search_coms_f = isset($_REQUEST['search_coms_f']) ? intval( $_REQUEST['search_coms_f'] ) : 0;
	$search_coms_t = isset($_REQUEST['search_coms_t']) ? intval( $_REQUEST['search_coms_t'] ) : 0;

	if ( !$search_news_f ) $search_news_f = "";
	if ( !$search_news_t ) $search_news_t = "";
	if ( !$search_coms_f ) $search_coms_f = "";
	if ( !$search_coms_t ) $search_coms_t = "";

	if ( isset($_REQUEST['news_per_page']) AND intval($_REQUEST['news_per_page']) > 0 ) $news_per_page = intval( $_REQUEST['news_per_page'] ); else $news_per_page = 50;

	echo <<<HTML
<div class="modal fade" name="advancedadd" id="advancedadd">
<div class="modal-dialog" role="document">
	<div class="modal-content">
	<form method="post" action="" autocomplete="off">
	<input type="hidden" name="action" value="adduser">
	<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
	<input type="hidden" name="mod" value="editusers">
	  <div class="modal-header ui-dialog-titlebar">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<span class="ui-dialog-title">{$lang['user_auser']}</span>
	  </div>
	  <div class="modal-body">
	  
		<div class="form-group">
			<div class="row">
				<div class="col-sm-6">
					<label>{$lang['user_name']}</label>
					<input name="regusername" type="text" dir="auto" class="form-control" maxlength="40" required>
				</div>
				<div class="col-sm-6">
					<label>{$lang['user_pass']}</label>
					<input name="regpassword" type="text" dir="auto" class="form-control" maxlength="70" required>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-6">
					<label>{$lang['user_mail']}</label>
					<input name="regemail" type="text" dir="auto" class="form-control" maxlength="50" required>
				</div>
				<div class="col-sm-6">
					<label>{$lang['user_acc']}</label>
					<select class="uniform" name="reglevel" data-width="100%">{$grouplist}</select>
				</div>
			</div>
		</div>	
	
	   </div>
      <div class="modal-footer" style="margin-top:-20px;">
		<button type="button" class="btn bg-grey-400 btn-sm btn-raised" data-dismiss="modal">{$lang['p_cancel']}</button>
	    <button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
      </div>
	  </form>
	</div>
</div>
</div>

<div class="modal fade" name="userexport" id="userexport">
<div class="modal-dialog" role="document">
	<div class="modal-content">
	<form method="post" action="" autocomplete="off" class="form-horizontal">
	<input type="hidden" name="action" value="export">
	<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
	<input type="hidden" name="mod" value="editusers">
	  <div class="modal-header ui-dialog-titlebar">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<span class="ui-dialog-title">{$lang['u_export_title']}</span>
	  </div>
	  <div class="modal-body">
		<div class="form-group">
		  <div class="col-sm-12">{$lang['u_export_title_1']}</div>
		</div>
		<div class="form-group">
				<div class="col-sm-4">
					<div class="checkbox"><label><input class="icheck" type="checkbox" name="login" value="1" checked>{$lang['u_export_title_2']}</label></div>
				</div>
				<div class="col-sm-4">
					<div class="checkbox"><label><input class="icheck" type="checkbox" name="name" value="1" checked>{$lang['u_export_title_3']}</label></div>
				</div>
				<div class="col-sm-4">
					<div class="checkbox"><label><input class="icheck" type="checkbox" name="mail" value="1" checked>{$lang['u_export_title_4']}</label></div>
				</div>
		</div>
		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['user_acc']}</label>
		  <div class="col-sm-8">
			<select data-placeholder="{$lang['group_select_1']}" name="groups[]" class="groupselect" multiple>
				<option value="all" selected>{$lang['edit_all']}</option>{$group_list}</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['edit_regdate']}</label>
		  <div class="col-sm-4">
			{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="fromregdate" class="form-control" style="width:140px;" value="" autocomplete="off">
		  </div>
		  <div class="col-sm-4">
			{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="toregdate" class="form-control" style="width:135px;" value="" autocomplete="off">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['edit_entedate']}</label>
		  <div class="col-sm-4">
			{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="fromentdate" class="form-control" style="width:140px;" value="" autocomplete="off">
		  </div>
		  <div class="col-sm-4">
			{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" dir="auto" name="toentdate" class="form-control" style="width:135px;" value="" autocomplete="off">
		  </div>
		 </div>
		<div class="form-group">
		  <div class="col-sm-6">
			<label class="radio-inline"><input class="icheck" type="radio" name="format" value="csv" checked>{$lang['u_export_title_5']}</label>
		  </div>
		  <div class="col-sm-6">
			<label class="radio-inline"><input class="icheck" type="radio" name="format" value="exel">{$lang['u_export_title_6']}</label>
		  </div>
		 </div>

	   </div>
      <div class="modal-footer" style="margin-top:-20px;">
		<button type="button" class="btn bg-grey-400 btn-sm btn-raised" data-dismiss="modal">{$lang['p_cancel']}</button>
	    <button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-upload position-left"></i>{$lang['u_export_action']}</button>
      </div>
	  </form>
	</div>
</div>
</div>
HTML;

	if( isset($_REQUEST['search_reglevel']) AND $_REQUEST['search_reglevel'] ) { $search_reglevel = $_REQUEST['search_reglevel']; $group_list = get_groups( $_REQUEST['search_reglevel'] ); }
	else { $search_reglevel = false; $group_list = get_groups(); }

	if( isset($_REQUEST['search_banned']) AND  $_REQUEST['search_banned'] == "yes" ) { $search_banned = "yes"; $ifch = "checked"; } else {$search_banned = ""; $ifch = "";}
	
	$disabled_news = isset($_REQUEST['disabled_news']) ? intval($_REQUEST['disabled_news']) : 0;
	$disabled_comments = isset($_REQUEST['disabled_comments']) ? intval($_REQUEST['disabled_comments']) : 0;
	
	if( $disabled_news ) $ifch1 = "checked"; else $ifch1 = "";
	if( $disabled_comments ) $ifch2 = "checked"; else $ifch2 = "";
	
	if( isset($_REQUEST['search_full']) AND  $_REQUEST['search_full'] ) { $search_full = 1; $ifsfn = "checked"; } else { $search_full = ""; $ifsfn = ""; }

	$search_order_user = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_u'] ) ) {
		$search_order_user[$_REQUEST['search_order_u']] = 'selected';
		if ($_REQUEST['search_order_u'] == "desc" or $_REQUEST['search_order_u'] == "asc") $search_order_u = $_REQUEST['search_order_u'];
	} else {
		$search_order_user['----'] = 'selected';
	}

	$search_order_reg = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_r'] ) ) {
		$search_order_reg[$_REQUEST['search_order_r']] = 'selected';
		if ($_REQUEST['search_order_r'] == "desc" or $_REQUEST['search_order_r'] == "asc") $search_order_r = $_REQUEST['search_order_r'];
	} else {
		$search_order_reg['----'] = 'selected';
	}

	$search_order_last = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_l'] ) ) {
		$search_order_last[$_REQUEST['search_order_l']] = 'selected';
		if ($_REQUEST['search_order_l'] == "desc" or $_REQUEST['search_order_l'] == "asc") $search_order_l = $_REQUEST['search_order_l'];
	} else {
		$search_order_last['----'] = 'selected';
	}

	$search_order_news = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_n'] ) ) {
		$search_order_news[$_REQUEST['search_order_n']] = 'selected';
		if ($_REQUEST['search_order_n'] == "desc" or $_REQUEST['search_order_n'] == "asc") $search_order_n = $_REQUEST['search_order_n'];
	} else {
		$search_order_news['----'] = 'selected';
	}

	$search_order_coms = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_c'] ) ) {
		$search_order_coms[$_REQUEST['search_order_c']] = 'selected';
		if ($_REQUEST['search_order_c'] == "desc" or $_REQUEST['search_order_c'] == "asc") $search_order_c = $_REQUEST['search_order_c'];
	} else {
		$search_order_coms['----'] = 'selected';
	}
	
	$start_from = isset($_REQUEST['start_from']) ? intval( $_REQUEST['start_from'] ) : 0;
	
	$search_field = isset($_REQUEST['search_field']) ? trim(htmlspecialchars(urldecode($_REQUEST['search_field']), ENT_QUOTES, 'UTF-8')) : '';
	
	$search_area = array('', '', '', '', '', '', '', '');

	if (isset($_REQUEST['search_area'])) {
		$_REQUEST['search_area'] = intval($_REQUEST['search_area']);
		$search_area[$_REQUEST['search_area']] = 'selected';
	} else {
		$search_area[1] = 'selected';
	}

	$where = array ();

	if( isset( $_REQUEST['search'] ) AND $_REQUEST['search'] AND $search_field ) {

		$search_field = $db->safesql($search_field);

		switch ( $_REQUEST['search_area'] ) {
			case 1:
				
				if ($search_full) $where[] = "name='{$search_field}'";
				else $where[] = "name LIKE '%{$search_field}%'";

				break;
			
			case 2:

				if ($search_full) $where[] = "email='{$search_field}'";
				else $where[] = "email LIKE '%{$search_field}%'";

				break;
			
			case 3:

				if ($search_full) $where[] = "xfields LIKE '%|{$search_field}%'";
				else $where[] = "xfields LIKE '%{$search_field}%'";

				break;

			case 4:

				if ($search_full) $where[] = "fullname='{$search_field}'";
				else $where[] = "fullname LIKE '%{$search_field}%'";

				break;

			case 5:

				if ($search_full) $where[] = "land='{$search_field}'";
				else $where[] = "land LIKE '%{$search_field}%'";

				break;

			case 6:

				if ($search_full) $where[] = "info='{$search_field}'";
				else $where[] = "info LIKE '%{$search_field}%'";

				break;

			case 7:

				if ($search_full) $where[] = "signature='{$search_field}'";
				else $where[] = "signature LIKE '%{$search_field}%'";

				break;
		}
		
	}

	if( ! empty( $search_banned ) ) {
		$where[] = "banned='yes'";
	}

	if( ! empty( $fromregdate ) ) {
		$where[] = "reg_date>='" . strtotime( $fromregdate ) . "'";
	}
	if( ! empty( $toregdate ) ) {
		$where[] = "reg_date<='" . strtotime( $toregdate ) . "'";
	}
	if( ! empty( $fromentdate ) ) {
		$where[] = "lastdate>='" . strtotime( $fromentdate ) . "'";
	}
	if( ! empty( $toentdate ) ) {
		$where[] = "lastdate<='" . strtotime( $toentdate ) . "'";
	}
	if( ! empty( $search_news_f ) ) {
		$search_news_f = intval( $search_news_f );
		$where[] = "news_num>='$search_news_f'";
	}
	if( ! empty( $search_news_t ) ) {
		$search_news_t = intval( $search_news_t );
		$where[] = "news_num<'$search_news_t'";
	}
	if( ! empty( $search_coms_f ) ) {
		$search_coms_f = intval( $search_coms_f );
		$where[] = "comm_num>='$search_coms_f'";
	}
	if( ! empty( $search_coms_t ) ) {
		$search_coms_t = intval( $search_coms_t );
		$where[] = "comm_num<'$search_coms_t'";
	}
	if( $search_reglevel ) {
		$search_reglevel = intval( $search_reglevel );
		$where[] = "user_group='$search_reglevel'";
	}
	if( $disabled_news ) {
		$where[] = "(restricted='1' OR restricted='3')";
	}
	if( $disabled_comments ) {
		$where[] = "(restricted='2' OR restricted='3')";
	}

	if( !isset($_REQUEST['search']) OR ( isset($_REQUEST['search']) AND !$_REQUEST['search'] ) ) {
		$where[] = "user_group < '4'";
		$hint_search = "<div class=\"alert alert-info alert-styled-left alert-arrow-left alert-component\">{$lang['hint_user']}</div>";
	} else $hint_search = "";
	
	if ( count($where) ) {
		$where = 'WHERE ' . implode(" AND ", $where);
	} else $where = '';

	$order_by = array();

	if (!empty($search_order_u)) {
		$order_by[] = "name $search_order_u";
	}
	if (!empty($search_order_r)) {
		$order_by[] = "reg_date $search_order_r";
	}
	if (!empty($search_order_l)) {
		$order_by[] = "lastdate $search_order_l";
	}
	if (!empty($search_order_n)) {
		$order_by[] = "news_num $search_order_n";
	}
	if (!empty($search_order_c)) {
		$order_by[] = "comm_num $search_order_c";
	}

	$order_by = implode(", ", $order_by);

	if (!$order_by) {
		$order_by = "reg_date asc";
	}

	echo <<<HTML
<form name="searchform" id="searchform" method="get" action="?mod=editusers&action=list" class="form-horizontal">
<input type="hidden" name="action" id="action" value="list">
<input type="hidden" name="search" id="search" value="search">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="mod" id="mod" value="editusers">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['user_se']}
	<div class="heading-elements not-collapsible">
		<ul class="icons-list">
			<li><a href="#" data-toggle="modal" data-target="#advancedadd"><i class="fa fa-user-plus position-left"></i><span class="visible-lg-inline visible-md-inline visible-sm-inline">{$lang['user_auser']}</span></a></li>
		</ul>
	</div>
  </div>
  <div class="panel-body">

	  <div class="col-md-6">

		<div class="form-group">
			<div class="col-md-12">
				<div class="input-group">
					<input name="search_field" value="{$search_field}" type="text" dir="auto" class="form-control">
					<span class="input-group-btn">
						<select name="search_area" class="uniform form-control" data-dropdown-align-right="true"><option value="1" {$search_area[1]}>{$lang['u_export_title_2']}</option><option value="2" {$search_area[2]}>{$lang['u_export_title_4']}</option><option value="3" {$search_area[3]}>{$lang['filter_search_6']}</option><option value="4" {$search_area[4]}>{$lang['filter_search_11']}</option><option value="5" {$search_area[5]}>{$lang['filter_search_12']}</option><option value="6" {$search_area[6]}>{$lang['filter_search_13']}</option><option value="7" {$search_area[7]}>{$lang['filter_search_14']}</option></select>
					</span>
				</div>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" id="search_full" name="search_full" value="1" {$ifsfn}>{$lang['search_full_name']}</label></div>
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="disabled_news" value="1" $ifch1>{$lang['disabled_news']}</label></div>
		  </div>
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="search_banned" id="search_banned" value="yes" $ifch>{$lang['user_banned']}</label></div>
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="disabled_comments" value="1" $ifch2>{$lang['disabled_comments']}</label></div>
		  </div>
		</div>

		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['user_acc']}</label>
		  <div class="col-md-10">
			<select class="uniform" name="search_reglevel" id="search_reglevel"><option selected value="0">{$lang['edit_all']}</option>{$group_list}</select>
		  </div>
		</div>
		
		<div class="form-group">
		  <label class="control-label col-md-7">{$lang['edit_upp']}</label>
		  <div class="col-md-5">
			<input class="form-control text-center" style="width:185px;" type="text" dir="auto" name="news_per_page" id="news_per_page" value="{$news_per_page}">
		  </div>
		 </div>

	  </div>

	  <div class="col-md-6">
		<div class="form-group">
		  <label class="control-label col-md-4">{$lang['edit_regdate']}</label>
		  <div class="col-md-8">
			{$lang['edit_fdate']}&nbsp;<input class="form-control" style="width:140px;" data-rel="calendardate" type="text" dir="auto" name="fromregdate" id="fromregdate" value="{$fromregdate}" autocomplete="off">
			{$lang['edit_tdate']}&nbsp;<input class="form-control" style="width:140px;" data-rel="calendardate" type="text" dir="auto" name="toregdate" id="toregdate" value="{$toregdate}" autocomplete="off">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-4">{$lang['edit_entedate']}</label>
		  <div class="col-md-8">
			{$lang['edit_fdate']}&nbsp;<input class="form-control" style="width:140px;" data-rel="calendardate" type="text" dir="auto" name="fromentdate" id="fromentdate" value="{$fromentdate}" autocomplete="off">
			{$lang['edit_tdate']}&nbsp;<input class="form-control" style="width:140px;" data-rel="calendardate" type="text" dir="auto" name="toentdate" id="toentdate" value="{$toentdate}" autocomplete="off">
		  </div>
	    </div>
		<div class="form-group">
 	   <label class="control-label col-md-4">{$lang['edit_newsnum']}</label>
		  <div class="col-md-8">
			{$lang['edit_fdate']}&nbsp;<input class="form-control text-center" style="width:70px;" type="text" dir="auto" name="search_news_f" id="search_news_f" value="{$search_news_f}">
			{$lang['edit_tdate']}&nbsp;<input class="form-control text-center" style="width:70px;" type="text" dir="auto" name="search_news_t" id="search_news_t" value="{$search_news_t}">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-4">{$lang['edit_comsnum']}</label>
		  <div class="col-md-8">
			{$lang['edit_fdate']}&nbsp;<input class="form-control text-center" style="width:70px;" type="text" dir="auto" name="search_coms_f" id="search_coms_f" value="{$search_coms_f}">
		    {$lang['edit_tdate']}&nbsp;<input class="form-control text-center" style="width:70px;" type="text" dir="auto" name="search_coms_t" id="search_coms_t" value="{$search_coms_t}">
	      </div>
		 </div>

	  </div>
	  
    </div>
	<div class="panel-body hidden-xs">
	{$lang['user_order']}
	</div>
	<div class="panel-body hidden-xs">
		<div class="col-md-2 col-xs-6">
		{$lang['user_name']}<br /><select class="uniform form-control" name="search_order_u" id="search_order_u">
           <option {$search_order_user['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_user['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_user['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_reg']}<br /><select class="uniform form-control" name="search_order_r" id="search_order_r">
           <option {$search_order_reg['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_reg['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_reg['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_last']}<br /><select class="uniform form-control" name="search_order_l" id="search_order_l">
           <option {$search_order_last['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_last['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_last['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_news']}<br /><select class="uniform form-control" name="search_order_n" id="search_order_n">
           <option {$search_order_news['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_news['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_news['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-4 col-xs-12">
		{$lang['user_coms']}<br /><select class="uniform form-control" name="search_order_c" id="search_order_c">
           <option {$search_order_coms['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_coms['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_coms['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
	</div>
	<div class="panel-footer">
		<input type="submit" class="btn bg-teal btn-sm btn-raised position-left" value="{$lang['b_find']}">
		<input type="button" class="btn bg-danger btn-sm btn-raised position-left" value="{$lang['user_breset']}" onclick="javascript:clearform(document.searchform); return false;">
		<input type="reset" class="btn bg-grey-400 btn-sm btn-raised position-left" value="{$lang['user_brestore']}">
   </div>
</div>
</form>
{$hint_search}
HTML;

	$query_count = "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users ".$where;
	$result_count = $db->super_query( $query_count );
	$all_count_news = $result_count['count'];

	if(!$all_count_news) {

		echo <<<HTML
<div class="alert alert-warning alert-styled-left alert-arrow-left alert-component">{$lang['search_nousers']}</div>
HTML;

	}	else {

echo <<<HTML
<script>
<!--
function cdelete(id, moderation){

		if(moderation == 'only') {
			var message = '{$lang['comm_mcdelconfirm']}';
		} else {
			var message = '{$lang['comm_alldelconfirm']}';
		}
		
	    DLEconfirmDelete( message, '{$lang['p_confirm']}', function () {
			document.location='?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id=' + id + '&moderation='+moderation;
		} );
}

function ndelete(id, moderation){
		if(moderation == 'only') {
			var message = '{$lang['news_mdelconfirm']}';
		} else {
			var message = '{$lang['news_alldelconfirm']}';
		}
	    DLEconfirmDelete( message, '{$lang['p_confirm']}', function () {
			document.location='?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id=' + id + '&moderation='+moderation;
		} );
}

function nchange(id){

	DLEprompt('{$lang['p_news_user']}', '', '{$lang['p_confirm']}', function (r) {

		document.location="?mod=editusers&action=dochangenews&user_hash={$dle_login_hash}&id=" + id + "&newuser=" + encodeURIComponent(r);

	}, false, '{$lang['b_start']}');
}
function ckeck_uncheck_all_self() {
    var frm = document.editdeleteusers;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('tr').removeClass('warning');}
            else{ elmnt.checked=true; $(elmnt).parents('tr').addClass('warning');}
        }
    }
    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
	
	$(frm.master_box).parents('tr').removeClass('warning');
	
	$.uniform.update();
}

function ckeck_uncheck_all() {
    var frm = document.editusers;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('tr').removeClass('warning');}
            else{ elmnt.checked=true; $(elmnt).parents('tr').addClass('warning');}
        }
    }
    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
	
	$(frm.master_box).parents('tr').removeClass('warning');
	
	$.uniform.update();
}
$(function() {
    $('.table').find('tr > td:last-child').find('input[type=checkbox]').on('change', function() {
        if($(this).is(':checked')) {
            $(this).parents('tr').addClass('warning');
        }
        else {
            $(this).parents('tr').removeClass('warning');
        }
    });

});
//-->
</script>
<form action="" method="post" name="editusers">
<input type="hidden" name=mod value="mass_user_actions">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['user_list']} ({$all_count_news})
		<div class="heading-elements">
		<ul class="icons-list">
			<li><a href="#" data-toggle="modal" data-target="#userexport"><i class="fa fa-upload position-left"></i>{$lang['u_export_title']}</a></li>
		</ul>
	</div>
  </div>

    <table class="table table-xs table-hover">
      <thead>
      <tr>
        <th>{$lang['user_name']}</th>
        <th class="hidden-xs">{$lang['user_reg']}</th>
        <th class="hidden-xs">{$lang['user_last']}</th>
        <th class="hidden-xs text-center" style="width: 40px"><i class="fa fa-file-text-o  tip" data-original-title="{$lang['rss_maxnews']}"></i></th>
        <th class="hidden-xs text-center" style="width: 40px"><i class="fa fa-comments-o tip" data-original-title="{$lang['edit_com']}"></i></th>
        <th style="width: 70px">&nbsp;</th>
        <th class="hidden-xs" style="width: 40px"><input type="checkbox" name="master_box" class="icheck" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></th>
      </tr>
      </thead>
	  <tbody>
HTML;

	$start_from = isset($_REQUEST['start_from']) ? intval( $_REQUEST['start_from'] ) : 0;
	$i = $start_from;

	$db->query( "SELECT * FROM " . USERPREFIX . "_users {$where} ORDER BY {$order_by} LIMIT {$start_from},{$news_per_page}" );
	$i = 0;

	while ( $row = $db->get_row() ) {
		$i ++;

		$last_login = langdate( $langformatdatefull, $row['lastdate'] );
		$user_name = "<a href=\"?mod=editusers&action=edituser&id={$row['user_id']}\">" . $row['name'] . "</a>";
		if( $row['news_num'] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			
			$row['name'] = urlencode( $row['name'] );
			
			if( $config['allow_alt_url'] ) {
				
				$url_user = $config['http_home_url']."user/".urlencode( $row['name'] )."/news/";
				
			} else {
				
				$url_user = $config['http_home_url']."index.php?subaction=allnews&user=".$row['name'];
				
			}
			
			$row['news_num'] = number_format( $row['news_num'], 0, ',', ' ');
			
			$news_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['rss_maxnews']}" class="tip"><b>{$row['news_num']}</b></a>
				  <ul class="dropdown-menu text-left dropdown-menu-right">
				   <li><a href="{$url_user}" target="_blank"><i class="fa fa-eye position-left"></i>{$lang['comm_view']}</a></li>
				   <li><a href="?mod=editusers&action=dorebuildnews&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-retweet position-left"></i>{$lang['r_countnews']}</a></li>
				   <li><a href="#" onclick="javascript:nchange('{$row['user_id']}'); return false;"><i class="fa fa-pencil-square-o position-left"></i>{$lang['change_news_user']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:ndelete('{$row['user_id']}','only'); return false;" href="?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id={$row['user_id']}&moderation=only"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['news_mdel']}</a></li>
				   <li><a onclick="javascript:ndelete('{$row['user_id']}',''); return false;" href="?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
			
		}

		if( $row['comm_num'] == 0 ) {
			$comms_link = $row['comm_num'];
		} else {
			
			$row['comm_num'] = number_format( $row['comm_num'], 0, ',', ' ');
			
			$comms_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="tip"><b>{$row['comm_num']}</b></a>
				  <ul class="dropdown-menu text-left dropdown-menu-right">
				   <li><a href="{$config['http_home_url']}index.php?do=lastcomments&userid={$row['user_id']}" target="_blank"><i class="fa fa-eye position-left"></i>{$lang['comm_view']}</a></li>
					<li><a href="?mod=editusers&action=dorebuildcomments&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-retweet position-left"></i>{$lang['r_countcomments']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:cdelete('{$row['user_id']}','only'); return(false)" href="?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id={$row['user_id']}&moderation=only"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['news_mdel']}</a></li>
				   <li><a onclick="javascript:cdelete('{$row['user_id']}',''); return(false)" href="?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}

		$user_delete = "<li class=\"divider\"></li><li><a onclick=\"javascript:confirmdelete('" . $row['user_id'] . "', '" . $row['name'] . "'); return(false)\" href=\"#\"><i class=\"fa fa-trash-o position-left text-danger\"></i>{$lang['user_del']}</a></li>";

		if( $row['banned'] == 'yes' ) $user_level = "<span class=\"text-danger\">" . $lang['user_ban'] . "</span>";
		else $user_level = $user_group[$row['user_group']]['group_prefix'].$user_group[$row['user_group']]['group_name'].$user_group[$row['user_group']]['group_suffix']."<a href=\"?mod=usergroup&action=edit&id={$row['user_group']}\" target=\"_blank\" data-popup=\"tooltip\" title=\"{$lang['group_edit1']} {$user_group[$row['user_group']]['group_name']}\"><i class=\"fa fa-external-link position-left position-right\" style=\"font-size: 12px;\"></i></a>";

		if( $row['user_group'] == 1 ) $user_delete = "";
		
		$pmname = urlencode($row['name']);

		$menu_link = <<<HTML
       <div class="btn-group">
				<a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
				<ul class="dropdown-menu text-left dropdown-menu-right">
				  <li><a href="{$config['http_home_url']}index.php?subaction=userinfo&user={$pmname}" target="_blank"><i class="fa fa-external-link position-left"></i>{$lang['header_profile']}</a></li>
				  <li><a href="{$config['http_home_url']}index.php?do=feedback&user={$row['user_id']}" target="_blank"><i class="fa fa-envelope-o position-left"></i>{$lang['bb_b_mail']}</a></li>
				  <li><a href="{$config['http_home_url']}index.php?do=pm&doaction=newpm&username={$pmname}" target="_blank"><i class="fa fa-user position-left"></i>{$lang['nl_pm']}</a></li>
				  {$user_delete}
				</ul>
        </div>
HTML;
		$avatar = '';

		if ( count(explode("@", $row['foto'])) == 2 ) {
			$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
		} else {
			
			if( $row['foto'] ) {
				
				if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];

				$avatar = @parse_url ( $avatar );

				if( $avatar['host'] ) {
					
					$avatar = $row['foto'];
					
				} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];
			
			}
		}

		if ($avatar) {
			$avatar = "<img src=\"{$avatar}\" class=\"img-circle img-responsive hidden-xs\">";
		} else {
			$avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-responsive hidden-xs" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
		}

		echo "<tr>
        <td><div class=\"user-list\">{$avatar}<h6>{$user_name}</h6><span class=\"text-size-small\">{$user_level}</span></div></td>
        <td class=\"hidden-xs\">";
		echo (langdate( $langformatdatefull, $row['reg_date'] ));
		echo "</td>
        <td class=\"hidden-xs\">$last_login</td>
        <td class=\"hidden-xs text-nowrap text-center\">{$news_link}</td>
        <td class=\"hidden-xs text-nowrap text-center\">{$comms_link}</td>
        <td class=\"text-center\">{$menu_link}</td>
		<td class=\"hidden-xs\"><input name=\"selected_users[]\" value=\"{$row['user_id']}\" type=\"checkbox\" class=\"icheck\"></td>
        </tr>";
	}
	$db->free();

	// pagination

	$npp_nav = "";


	if( $all_count_news > $news_per_page ) {

		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<li><a onclick=\"javascript:list_submit($previous); return(false)\" href=#> &lt;&lt; </a></li>";
		}

		$enpages_count = @ceil( $all_count_news / $news_per_page );
		$enpages_start_from = 0;
		$enpages = "";

		if( $enpages_count <= 10 ) {

			for($j = 1; $j <= $enpages_count; $j ++) {

				if( $enpages_start_from != $start_from ) {

					$enpages .= "<li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";

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

				$enpages .= "<li><a onclick=\"javascript:list_submit(0); return(false);\" href=\"#\">1</a></li> <li><span>...</span></li>";

			}

			for($j = $start; $j <= $end; $j ++) {

				if( $enpages_start_from != $start_from ) {

					$enpages .= "<li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";

				} else {

					$enpages .= "<li class=\"active\"><span>$j</span></li>";
				}

				$enpages_start_from += $news_per_page;
			}

			$enpages_start_from = ($enpages_count - 1) * $news_per_page;
			$enpages .= "<li><span>...</span></li><li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a></li>";

			$npp_nav .= $enpages;

		}

		if( $all_count_news > $i ) {
			$how_next = $all_count_news - $i;
			if( $how_next > $news_per_page ) {
				$how_next = $news_per_page;
			}
			$npp_nav .= "<li><a onclick=\"javascript:list_submit($i); return(false)\" href=#> &gt;&gt; </a></li>";
		}

		$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";

	}

	// pagination

	echo <<<HTML
	  </tbody>
	</table>
	<div class="panel-footer hidden-xs">
		<div class="pull-right">
		<select class="uniform" name="action">
<option value="">{$lang['edit_selact']}</option>
<option value="mass_move_to_group">{$lang['massusers_group']}</option>
<option value="mass_move_to_ban">{$lang['massusers_banned']}</option>
<option value="mass_delete_comments">{$lang['massusers_comments']}</option>
<option value="mass_delete_pm">{$lang['masspm_delete']}</option>
<option value="mass_delete">{$lang['massusers_delete']}</option>
</select>&nbsp;<input class="btn bg-brown-600 btn-sm btn-raised" type="submit" value="{$lang['b_start']}">
		</div>
	</div>
</div>
<div class="mb-20">
{$npp_nav}
</div>
</form>
HTML;

}
	echofooter();

} elseif( $action == "export" ) {

	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", $lang['index_denied'], $lang['index_denied'] );
	}

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$login = intval($_POST['login']);
	$name = intval($_POST['name']);
	$mail = intval($_POST['mail']);
	
	if( isset($_POST['toregdate']) ) {
		
		$toregdate = intval(strtotime( (string)$_POST['toregdate'] ));
		
	} else $toregdate = 0;

	if( isset($_POST['fromregdate']) ) {
		
		$fromregdate = intval(strtotime( (string)$_POST['fromregdate'] ));
		
	} else $fromregdate = 0;	

	if( isset($_POST['fromentdate']) ) {
		
		$fromentdate = intval(strtotime( (string)$_POST['fromentdate'] ));
		
	} else $fromentdate = 0;	

	if( isset($_POST['toentdate']) ) {
		
		$toentdate = intval(strtotime( (string)$_POST['toentdate'] ));
		
	} else $toentdate = 0;
	
	$where = array();
	$where[] = "banned != 'yes'";

	if (isset ($_POST['groups'])) {
	
		$groups = array ();
	
		if( count( $_POST['groups'] ) ) {
			
			foreach ( $_POST['groups'] as $value ) {
				if(intval($value)) $groups[] = intval($value);
			}
			
			if( count( $groups ) ) {
				$groups = implode( "','", $groups );
				
				$where[] = "user_group IN ('" . $groups . "')";
			}

		}
	
	}
	
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
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '108', '')" );
	
	$db->query("SELECT email, name, fullname FROM " . USERPREFIX . "_users WHERE ".implode (" AND ", $where)." ORDER BY user_id DESC");
	
	if( $_POST['format'] == "exel" ) {
		
		$rows = "<Table><Row>";
		
		if($login) $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$lang['u_export_title_2']}</Data></Cell>";
		if($name) $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$lang['u_export_title_3']}</Data></Cell>";
		if($mail) $rows .= "<Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">{$lang['u_export_title_4']}</Data></Cell>";
		
		$rows .= "</Row>";
		
		while( $row = $db->get_row() ) {
			$cells = "";
			
			if($login) $cells .= "<Cell><Data ss:Type=\"String\">{$row['name']}</Data></Cell>";
			if($name) $cells .= "<Cell><Data ss:Type=\"String\">{$row['fullname']}</Data></Cell>";
			if($mail) $cells .= "<Cell><Data ss:Type=\"String\">{$row['email']}</Data></Cell>";
			
			$rows .= "<Row>{$cells}</Row>";
		}
		
		$db->free();
		$db->close();
		
		$rows .= "</Table>";
	
		$rows = <<<HTML
	<?xml version="1.0" encoding="utf-8"?>
	<?mso-application progid="Excel.Sheet"?>
	<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
		<Styles>
			<Style ss:ID="bold">
				<Font ss:Bold="1"/>
			</Style>
		</Styles> 
		<Worksheet ss:Name="users">
		{$rows}
		</Worksheet>
	</Workbook>	
HTML;
		
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header( "Cache-Control: private", false);
		header( "Content-Type: application/x-msexcel; charset=utf-8" );
		header( 'Content-Disposition: attachment; filename="users.xls"' );
		header( "Content-Transfer-Encoding: binary" );
		header( "Connection: close");
		print( $rows );
	
		die();
	
	}	else {

		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header( "Cache-Control: private", false);
		header( "Content-Type: text/csv; charset=utf-8" );
		header( 'Content-Disposition: attachment; filename="users.csv"' );

		$output = fopen('php://output', 'w');
		fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

		$header_column = array();
		
		if($login) $header_column[] = $lang['u_export_title_2'];
		if($name) $header_column[] = $lang['u_export_title_3'];
		if($mail) $header_column[] = $lang['u_export_title_4'];
			
		fputcsv($output, $header_column, ";", "\"", "\\");

		while( $row = $db->get_row() ) {
			$cells = array();
		
			if($login) $cells[] = $row['name'];
			if($name)  $cells[] = $row['fullname'];
			if($mail)  $cells[] = $row['email'];
			
			fputcsv($output, $cells, ";", "\"", "\\");
			
		}
		fclose($output);
		
		$db->free();
		$db->close();
		
		die();	
	}

} elseif( $action == "adduser" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", $lang['index_denied'], $lang['index_denied'] );
	}
	
	if( !check_referer($_SERVER['PHP_SELF']."?mod=editusers") ) {
		msg( "error", $lang['index_denied'], $lang['no_referer'], $_SESSION['admin_user_referrer'] );
	}
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	if( ! $_POST['regusername'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_err_1'], $_SESSION['admin_user_referrer'] );
	}
	
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\%|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $_POST['regusername'] ) ) msg( "error", $lang['user_err'], $lang['user_err_6'], "javascript:history.go(-1)" );

	if( ! $_POST['regpassword'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']) , $lang['user_err_2'], $_SESSION['admin_user_referrer'] );
	}
	
	if( empty( $_POST['regemail'] ) OR @count(explode("@", $_POST['regemail'])) != 2) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err_1']), $lang['user_err_1'], $_SESSION['admin_user_referrer'] );
	}

	$regusername = $db->safesql($_POST['regusername']);

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
	$regemail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['regemail'] ) ) ) ) );

	$row = $db->super_query( "SELECT name, email FROM " . USERPREFIX . "_users WHERE name = '{$regusername}' OR email = '{$regemail}'" );

	if( isset($row['email']) AND $row['email'] == $regemail ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_err_4'], $_SESSION['admin_user_referrer'] );
	}

	if( isset($row['name']) AND $row['name'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_err_3'], $_SESSION['admin_user_referrer'] );
	}

	$add_time = time();
	$regpassword = $db->safesql( password_hash($_POST['regpassword'], PASSWORD_DEFAULT) );

	$reglevel = intval( $_POST['reglevel'] );

	if ( $member_id['user_group'] != 1 AND $reglevel < 2 ) $reglevel = 4;

	$db->query( "INSERT INTO " . USERPREFIX . "_users (name, password, email, user_group, reg_date, lastdate, info, signature, favorites, xfields) values ('$regusername', '$regpassword', '$regemail', '$reglevel', '$add_time', '$add_time','','','','')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '63', '{$regusername}')" );
	clear_cache('stats');

	msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_addok']), "{$lang['user_ok']} <b>{$regusername}</b> {$lang['user_ok_1']} <b>{$user_group[$reglevel]['group_name']}</b>", $_SESSION['admin_user_referrer'] );

} elseif( $action == "edituser" ) {

	if( isset( $_REQUEST['user'] ) ) {

		$user = $db->safesql( strip_tags( urldecode( $_GET['user'] ) ) );

		$skin = isset($_REQUEST['skin']) ? trim( totranslit($_REQUEST['skin'], false, false) ) : '';

		if ( $skin ) $skin = "&skin=".$skin;

		if( $user ) {

			$row = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE name = '$user'" );

			
			if( isset($row['user_id']) AND  $row['user_id'] ) {
				header( "Location: ?mod=editusers&action=edituser&id=" . $row['user_id'].$skin );
				die();	
			} else {
				header( "Location: ?mod=editusers".$skin );
				die();
			}

		}
	}
	
	$skin = isset($_REQUEST['skin']) ? trim( totranslit($_REQUEST['skin'], false, false) ) : '';
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		
		if($member_id['user_id'] != $id) {
			msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
		}
		
		$id = $member_id['user_id'];
	}

	$row = $db->super_query( "SELECT " . USERPREFIX . "_users.*, " . USERPREFIX . "_banned.days, " . USERPREFIX . "_banned.descr, " . USERPREFIX . "_banned.date as banned_date, " . USERPREFIX . "_banned.banned_from FROM " . USERPREFIX . "_users LEFT JOIN " . USERPREFIX . "_banned ON " . USERPREFIX . "_users.user_id=" . USERPREFIX . "_banned.users_id WHERE user_id = '{$id}'" );

	if( !$row['user_id'] ) {
		
		if($skin) die( $lang['user_nouser'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['user_nouser'], "javascript:history.go(-1)" );
		
	}

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 ) {
		
		if($skin) die( $lang['edit_not_admin'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['edit_not_admin'], "javascript:history.go(-1)" );

	}

	$parse = new ParseFilter();
	$parse->safe_mode = true;

	$row['fullname'] = $parse->decodeBBCodes( $row['fullname'], false );
	$row['land'] = $parse->decodeBBCodes( $row['land'], false );
	$row['info'] = $parse->decodeBBCodes( $row['info'], false );
	$row['signature'] = $parse->decodeBBCodes( $row['signature'], false );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );
	
	if($row['banned'] AND $row['banned_from']) {
		$lang['banned_from'] = str_ireplace('{name}', $row['banned_from'], $lang['banned_from']);
		$banned_from = "<div class=\"text-muted text-size-small\">{$lang['banned_from']}</div>";
	} else $banned_from = '';

	$last_date = langdate( $langformatdatefull, $row['lastdate'] );
	$reg_date = langdate( $langformatdatefull, $row['reg_date'] );
	
	if( !$row['cat_allow_addnews']) $cat_allow_addnews_value = "selected"; else $cat_allow_addnews_value = "";
	if( !$row['cat_add'] ) $cat_add_value = "selected"; else $cat_add_value = "";
	
	$cat_allow_addnews_list = CategoryNewsSelection( explode( ',', $row['cat_allow_addnews'] ), 0, false );
	$cat_add_list = CategoryNewsSelection( explode( ',', $row['cat_add'] ), 0, false );
	
	if( $row['time_limit'] != "" ) $row['time_limit'] = date( "Y-m-d H:i", $row['time_limit'] );

	if ( ($row['lastdate'] + 1200) > time() ) {

		$status ="<span class=\"text-success\">".$lang['stats_online_1']."</span>";

	} else {
		
		$status ="<span class=\"text-danger\">".$lang['stats_online_2']."</span>";
	}
	
	$avatar = '';
	if ( count(explode("@", $row['foto'])) == 2 ) {
	
		$avatar = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
		$gravatar = $row['foto'];
			
	} else {
	
		if( $row['foto'] ) {
			
			if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];
	
			$avatar = @parse_url ( $avatar );
	
			if( $avatar['host'] ) {
				
				$avatar = $row['foto'];
				
			} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];
	
	
		}
	
		$gravatar = "";
	}

	if ($avatar) {
		$avatar = "<img src=\"{$avatar}\" class=\"img-circle\">";
	} else {
		$avatar = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="img-circle img-responsive" fill="none" stroke="currentColor" stroke-width=".5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"></path><circle cx="12" cy="10" r="4"></circle><circle cx="12" cy="12" r="10"></circle></svg>';
	}

	if( $row['banned'] == "yes" ) $ifch = "checked";
	else $ifch = "";
	
	$row['days'] = intval( $row['days'] );
	
	if( $row['banned'] == "yes" and $row['days'] ) $endban = $lang['ban_edate'] . " " . langdate( $langformatdatefull, $row['banned_date'] );
	else $endban = "";
	
	$restricted_selected = array (0 => '', 1 => '', 2 => '', 3 => '' );
	$restricted_selected[$row['restricted']] = 'selected';
	
	if( $row['restricted'] and $row['restricted_days'] ) $end_restricted = $lang['edit_tdate'] . " " . langdate( $langformatdatefull, $row['restricted_date'] );
	else $end_restricted = "";
	
	if( $row['restricted'] ) $lang['restricted_none'] = $lang['restricted_clear'];

	$group_list = get_groups( $row['user_group'] );

	$timezones = timezone_list();

	$defaultzone = $timezones[$config['date_adjust']];

	if (isset($langtimezones[$config['date_adjust']])) {
		$defaultzone = ($lastIndex = strrpos($defaultzone, ")")) !== false ? substr($defaultzone, 0, $lastIndex + 1) : $defaultzone;
		$defaultzone .= ' ' . $langtimezones[$config['date_adjust']];
	}

	$timezoneselect = "<select class=\"uniform\" name=\"timezone\" data-live-search=\"true\" data-none-results-text=\"{$lang['addnews_cat_fault']}\"><option value=\"\">{$lang['system_default']} {$defaultzone}</option>\r\n";

	foreach ( $timezones as $value => $description ) {
		$timezoneselect .= "<option value=\"{$value}\"";

		if( $row['timezone'] == $value ) {
			$timezoneselect .= " selected ";
		}

		if( isset( $langtimezones[$value] ) ) {

			$description = ($lastIndex = strrpos($description, ")")) !== false ? substr($description, 0, $lastIndex+1) : $description;
			$description .= ' '. $langtimezones[$value];
		}

		$timezoneselect .= ">{$description}</option>\n";
	}

	$timezoneselect .= "</select>";
	
	$row['allowed_ip'] = stripslashes( str_replace( "|", "\n", $row['allowed_ip'] ) );

	if( $row['news_subscribe'] ) $row['news_subscribe'] = "checked"; else $row['news_subscribe'] = "";
	
	$newssubscribe = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" name=\"news_subscribe\" value=\"1\" {$row['news_subscribe']} />{$lang['news_subscribe']}</label></div>";
	
	if( $row['comments_reply_subscribe'] ) $row['comments_reply_subscribe'] = "checked"; else $row['comments_reply_subscribe'] = "";
	
	$commsubscribe = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" name=\"comments_reply_subscribe\" value=\"1\" {$row['comments_reply_subscribe']} />{$lang['comments_reply_subscribe']}</label></div>";

	$unsubscribe = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" name=\"unsubscribe\" value=\"1\" />{$lang['news_unsubscribe_1']}</label></div>";

	
	if( !$row['allow_mail'] ) $mailbox = "checked";
	else $mailbox = "";
	
	if ( !$skin ) {
		
		$ignore_list = array();
		$temp_result = $db->query( "SELECT * FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}'" );
		while ( $temp_row = $db->get_row( $temp_result ) ) {
	
			if( $config['allow_alt_url'] ) {
				
				$user_name = "<a href=\"" . $config['http_home_url'] . "user/" . urlencode( $temp_row['user_from'] ) . "/\" target=\"_blank\">" . $temp_row['user_from'] . "</a>";
			
			} else {
				
				$user_name = "<a href=\"index.php?subaction=userinfo&amp;user=" . urlencode( $temp_row['user_from'] ) . "\" target=\"_blank\">" . $temp_row['user_from'] . "</a>";
		
			}
	
			$ignore_list[] = "<span id=\"dle-ignore-list-{$temp_row['id']}\">{$user_name}<a title=\"{$lang['del_from_ignore_1']}\" href=\"javascript:DelIgnorePM('" . $temp_row['id'] . "', '" . $lang['del_from_ignore'] . "')\"><i class=\"fa fa-trash-o position-right text-danger\"></i></a>";
		}
		$db->free( $temp_result );
		
		if (count($ignore_list)) $ignore_list = implode("</span>, ", $ignore_list)."</span>"; else $ignore_list = "";
		
		if( $config['twofactor_auth'] ) {

			$checked_auth = array('0' => "", '1' => "", '2' => "");

			if ($row['twofactor_auth']) $checked_auth[$row['twofactor_auth']] = " selected ";

			if ($member_id['user_id'] === $row['user_id']) $allow_change = 1; else $allow_change = 0;

			if ($row['twofactor_auth'] == 2) $allow_change = 0;
			
			$twofactor_auth = "<select class=\"uniform\" name=\"twofactor_auth\" onchange=\"onTwofactoryChange(this, {$allow_change}); return false;\" ><option value=\"0\"{$checked_auth[0]}>{$lang['twofactor_auth_1']}</option><option value=\"1\"{$checked_auth[1]}>{$lang['twofactor_auth_2']}</option><option value=\"2\"{$checked_auth[2]}>{$lang['twofactor_auth_3']}</option></select><input type=\"hidden\" id=\"twofactor_auth_prev\" name=\"twofactor_auth_prev\" value=\"{$row['twofactor_auth']}\">";
	
		} else {
			
			$twofactor_auth = "";
	
		}
		
		if($member_id['user_id'] != $row['user_id']) {
			
			$del_button = "<button onclick=\"confirmDelete(); return false;\" class=\"btn bg-danger btn-sm btn-raised\"><i class=\"fa fa-trash-o position-left\"></i>{$lang['edit_dnews']}</button>";

		} else $del_button = "";
		
		$xfieldsaction = "list";
		$adminmode = true;
		$xfieldsadd = false;
		$xfieldsid = $row['xfields'];
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/userfields.php'));
		
		$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";

		echoheader( "<i class=\"fa fa-user-circle-o position-left\"></i><span class=\"text-semibold\">{$lang['user_head']}</span>", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_edhead'] . " <span class=\"text-semibold\">{$row['name']}</span>") );
		

		echo <<<HTML
<script>
<!--
var dle_root = '';
function onTwofactoryChange( obj, allowchange ) {

	if ( !allowchange ) {
		return false;
	}

	var value = $(obj).val();
	var prev_value = $('#twofactor_auth_prev').val();

	if (value && value == 2 && value != prev_value) {

		ShowLoading('');

		$.get("engine/ajax/controller.php?mod=twofactor", { mode: 'createsecret', user_hash: dle_login_hash }, function (data) {

			HideLoading('');

			$("#dletwofactorsecret").remove();

			$("body").append("<div id='dletwofactorsecret' title='{$lang['p_confirm']}' style='display:none'>" + data + "</div>");

			var b = {};

			b[dle_act_lang[3]] = function () {
				$(obj).val(prev_value);
				$(obj).selectpicker('refresh');
				$("#dletwofactorsecret").remove();
			};

			b[dle_act_lang[2]] = function () {
				if ($("#dle-promt-text").val().length < 1) {
					$("#dle-promt-text").addClass('ui-state-error');
				} else {
					var pin = $("#dle-promt-text").val();
					$.post("engine/ajax/controller.php?mod=twofactor", { mode: 'verifysecret', pin: pin, user_hash: dle_login_hash }, function (data) {

						if (data.success) {

							$("#twofactor_auth_prev").val('2');
							$('#dletwofactorsecret').remove();
							DLEPush.info(data.message);

						} else if (data.error) {
							
							DLEPush.error(data.errorinfo);
							$(".dle-popup-twofactor-secret").css('max-height', '');
							$("#dletwofactorsecret").css('height', 'auto');

						}

					}, "json");

				}
			};

			$('#dletwofactorsecret').dialog({
				autoOpen: true,
				show: 'fade',
				hide: 'fade',
				width: 550,
				resizable: false,
				dialogClass: "dle-popup-twofactor-secret",
				buttons: b
			});

		});

	}

	return false;
}

function confirmDelete() {

	DLEconfirmDelete( '{$lang['user_deluser']}', '{$lang['p_confirm']}', function () {

		document.location='?mod=editusers&action=dodeleteuser&id={$row['user_id']}&user_hash={$dle_login_hash}';

	} );

}

function DelIgnorePM( id, text ){

    DLEconfirm( text, '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: "del_ignore", user_hash: '{$dle_login_hash}', skin: '{$config['skin']}' }, function(data){
	
			HideLoading('');
	
			$("#dle-ignore-list-" + id).html('');
			DLEPush.info(data);
			return false;
		
	
		});

	} );
	
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

function DLESendPM( name ) {
	var b = {};
	
	var ww = 800 * getBaseSize();

	if (ww > ($(window).width() * 0.95)) { ww = $(window).width() * 0.95; }

	$('#dlesendpmpopup').remove();
	$('#dleprofilepopup').remove();

	b['{$lang['p_cancel']}'] = function() { 
		$(this).dialog('close');
	};

	b['{$lang['p_send']}'] = function() {
		
		tinyMCE.triggerSave();
		
		var subj = 	$('#pm_subj').val();
		var comments = 	$('#pm_text').val();
		var name = 	$('#pm_name').val();
		var question_answer = $('#pm_question_answer').val();	
		var sec_code = $('#sec_code_pm').val();
		var dle_captcha_type  = '{$config['allow_recaptcha']}';

		var g_recaptcha_response = '';
		
		if (name == '')
		{
			DLEPush.error ( dle_req_field[0] );
			return false;
		}
		
		if (comments == '')
		{
			DLEPush.error ( dle_req_field[1] );
			return false;
		}
		
		if (subj == '')
		{
			DLEPush.error ( dle_req_field[2] );
			return false;
		}
		
		if ( dle_captcha_type == "1" ) {

			if ( typeof grecaptcha != "undefined"  ) {
				g_recaptcha_response = grecaptcha.getResponse(recaptcha_widget);
			}

		} else if (dle_captcha_type == "2" && typeof grecaptcha != "undefined") {

			g_recaptcha_response = $('#pm-recaptcha-response').val();

			if( g_recaptcha_response == '') {

				var recaptcha_public_key = $('#pm-recaptcha-response').data('key');

				grecaptcha.execute(recaptcha_public_key, {action: 'personal_message'}).then(function(token) {
					$('#pm-recaptcha-response').val(token);
					b[dle_p_send]();
				});

				return false;

			}

		} else if (dle_captcha_type == "3") {

			if ( typeof hcaptcha != "undefined"  ) {
			   	g_recaptcha_response = hcaptcha.getResponse(recaptcha_widget);
			}

		} else if (dle_captcha_type == "4") {

			if (typeof turnstile != "undefined") {
				g_recaptcha_response = turnstile.getResponse(recaptcha_widget);
			}

		}
		
		if (!sec_code) {
			sec_code = '';
		}
		if (!question_answer) {
			question_answer = '';
		}

		ShowLoading('');
	
		$.post("engine/ajax/controller.php?mod=pm", { action: 'send_pm', subj: subj, comments: comments, name: name, sec_code: sec_code, question_answer: question_answer, g_recaptcha_response: g_recaptcha_response, user_hash: '{$dle_login_hash}'}, function(data){
	
			HideLoading('');
			
			if ( data.success ) {
				$('#dlesendpmpopup').dialog('close');
				$('#dlesendpmpopup').remove();
				DLEPush.info ( data.success );

			} else if (data.error) {
				
				if (dle_captcha_type == "2") {
					if ( typeof grecaptcha != "undefined"  ) {
						var recaptcha_public_key = $('#pm-recaptcha-response').data('key');
						grecaptcha.execute(recaptcha_public_key, {action: 'pm'}).then(function(token) {
						$('#pm-recaptcha-response').val(token);
						});
					}
				} else if (dle_captcha_type == "4") {

					if (typeof turnstile != "undefined") {
						turnstile.reset(recaptcha_widget);
					}

				} else if (dle_captcha_type == "3") {

					if (typeof hcaptcha != "undefined") {
						hcaptcha.reset(recaptcha_widget);
					}

				} else if (dle_captcha_type == "1") {

					if (typeof grecaptcha != "undefined") {
						grecaptcha.reset(recaptcha_widget);
					}

				}
					
				DLEPush.error ( data.error );
			}
	
		}, 'json');
		
		return false;
	};
	
	ShowLoading('');

	$.get("engine/ajax/controller.php?mod=pm", { name: name, action: 'show_send', user_hash: '{$dle_login_hash}'}, function(data){

		HideLoading('');

		$('body').append(data);

		$('#dlesendpmpopup').dialog({
			autoOpen: true,
			width: ww,
			resizable: false,
			dialogClass: "modalfixed dle-popup-sendpm",
			buttons: b
		});
		
		$('.modalfixed.ui-dialog').css({ position: "fixed" });
		$('.dle-popup-sendpm').css({ 'cssText': 'width:' + ww +'px;max-height: none !important' });
		$('#dlesendpmpopup').css({ 'cssText': 'height: auto !important' });
		$('#dlesendpmpopup').dialog("option", "position", { my: "center", at: "center", of: window });

	}, 'html');
	
	return false;

};

$(function(){
	$('.cat_select').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
});

//-->
</script>
<style type="text/css">
	.wseditor {
		border-top: 5px solid #0c5f7e;
		border-top-left-radius: 2px;
		border-top-right-radius: 2px;
		margin-bottom: 10px;
	}
	.editor-style-light .wseditor {
		border-top: none;
	}
	.quick-edit-text {
		width: 100%;
	}
</style>
<div class="row">
	<div class="col-md-8">
		<form name="saveuserform" id="saveuserform" action="" method="post" enctype="multipart/form-data" class="form-horizontal">
		<div class="panel panel-default">
			<div class="panel-heading">
				{$lang['user_edhead']} <span class="text-semibold">{$row['name']}</span>
			</div>
			<div class="panel-body edit_profile">
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_mail']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control width-200 position-left" maxlength="50" type="text" dir="auto" name="editmail" value="{$row['email']}"><label class="checkbox-inline"><input class="icheck" type="checkbox" name="allow_mail" value="1" {$mailbox}>{$lang['no_mail']}</label>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['opt_sys_at']}</label>
				  <div class="col-md-9 col-sm-9">
					{$timezoneselect}
				  </div>
				 </div>
				<div class="list-group-divider"></div>
HTML;
if( $user_group[$member_id['user_group']]['admin_editusers'] ) {
	
echo <<<HTML
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_newlogin']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control width-300" maxlength="40" type="text" dir="auto" name="editlogin">
				  </div>
				 </div>
HTML;

}

echo <<<HTML
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_newpass']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control width-300" maxlength="70" type="text" dir="auto" name="editpass">
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['twofactor_auth']}</label>
				  <div class="col-md-9 col-sm-9">
					{$twofactor_auth}
				  </div>
				 </div>
				<div class="list-group-divider"></div>
HTML;

if( $user_group[$member_id['user_group']]['admin_editusers'] ) {
	
echo <<<HTML

				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_acc']}</label>
				  <div class="col-md-9 col-sm-9">
					<select name="editlevel" class="uniform">{$group_list}</select>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_gtlimit']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control" type="text" dir="auto" style="width:220px;" data-rel="calendardatetime" name="time_limit" id="time_limit" value="{$row['time_limit']}" autocomplete="off">
				  </div>
				 </div>
				<div class="list-group-divider"></div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_banned']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="icheck" type="checkbox" name="banned" value="yes" $ifch>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['ban_date']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control" style="width:50px;" type="text" dir="auto" name="banned_date" value="{$row['days']}"> {$endban}
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['ban_descr']}</label>
				  <div class="col-md-9 col-sm-9">
					<textarea dir="auto" style="width:100%; height:3.75rem;" name="banned_descr" class="classic">{$row['descr']}</textarea>
					{$banned_from}
				  </div>
				 </div>
				<div class="list-group-divider"></div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['restricted']}</label>
				  <div class="col-md-9 col-sm-9">
					<select name="restricted" class="uniform"><option value="0" $restricted_selected[0]>{$lang['restricted_none']}</option><option value="1" $restricted_selected[1]>{$lang['restricted_news']}</option><option value="2" $restricted_selected[2]>{$lang['restricted_comm']}</option><option value="3" $restricted_selected[3]>{$lang['restricted_all']}</option></select>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['restricted_date']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control" style="width:50px;" name="restricted_days" type="text" dir="auto" value="{$row['restricted_days']}"> {$end_restricted}
				  </div>
				 </div>
				<div class="list-group-divider"></div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['group_aladdnews']}</label>
				  <div class="col-md-9 col-sm-9">
					<select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_allow_addnews[]" style="width:100%; max-width:350px;" class="cat_select" multiple ><option value="" {$cat_allow_addnews_value}>{$lang['ng_group']}</option>{$cat_allow_addnews_list}</select>
					<div class="text-muted text-size-small">{$lang['hint_galaddnews']}</div>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['group_alct']}</label>
				  <div class="col-md-9 col-sm-9">
					<select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_add[]" style="width:100%; max-width:350px;" class="cat_select" multiple ><option value="" {$cat_add_value}>{$lang['ng_group']}</option>{$cat_add_list}</select>
					<div class="text-muted text-size-small">{$lang['hint_gadc']}</div>
				  </div>
				 </div>
				 
				<div class="list-group-divider"></div>
HTML;

}

echo <<<HTML
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">Gravatar:</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control width-300" maxlength="50" type="text" dir="auto" name="gravatar" value="{$gravatar}">
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_avatar']}</label>
				  <div class="col-md-9 col-sm-9">
					<input type="file" name="image" style="width:304px;" class="icheck">
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_del_avatar']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="icheck" type="checkbox" name="del_foto" value="yes">
				  </div>
				 </div>
				<div class="list-group-divider"></div>
HTML;

if( $user_group[$member_id['user_group']]['admin_editusers'] ) {
	
echo <<<HTML
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['user_del_comments']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="icheck" type="checkbox" name="del_comments" value="yes">
				  </div>
				 </div>
HTML;

}

$row['news_num'] = number_format( $row['news_num'], 0, ',', ' ');
$row['comm_num'] = number_format( $row['comm_num'], 0, ',', ' ');

$send_pm_link = "<a onclick=\"DLESendPM('" . urlencode($row['name']) . "'); return false;\" href=\"#\" class=\"btn bg-teal btn-sm btn-raised legitRipple mb-5\" target=\"_blank\"><i class=\"fa fa-paper-plane-o position-left\"></i>" . $lang['news_pmnew'] . "</a>";
$comments_link = "<a href=\"{$config['http_home_url']}index.php?do=lastcomments&userid={$row['user_id']}\" class=\"btn bg-slate-600 btn-sm btn-raised legitRipple mb-5 mt-5\" target=\"_blank\"><i class=\"fa fa-desktop position-left\"></i>" . $lang['see_user_comments'] . "</a>";


if( $config['allow_alt_url'] ) {

	$news_link = "<a href=\"{$config['http_home_url']}user/" . urlencode($row['name']) . "/news/\" class=\"btn bg-slate-600 btn-sm btn-raised legitRipple mb-5 mt-5\" target=\"_blank\"><i class=\"fa fa-desktop position-left\"></i>" . $lang['see_user_news'] . "</a>";	

} else {
	
	$news_link = "<a href=\"{$config['http_home_url']}index.php?subaction=allnews&amp;user=" . urlencode($row['name']) . "\" class=\"btn bg-slate-600 btn-sm btn-raised legitRipple mb-5 mt-5\" target=\"_blank\"><i class=\"fa fa-desktop position-left\"></i>" . $lang['see_user_news'] . "</a>";	
	
}

echo <<<HTML
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['allowed_ip']}</label>
				  <div class="col-md-9 col-sm-9">
					<textarea dir="auto" style="width:100%; height:70px;" name="allowed_ip" class="classic">{$row['allowed_ip']}</textarea>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['ignore_list']}</label>
				  <div class="col-md-9 col-sm-9">
					{$ignore_list}
				  </div>
				 </div>
				<div class="list-group-divider"></div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['opt_fullname']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control" maxlength="100" type="text" dir="auto" name="editfullname" value="{$row['fullname']}">
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['opt_land']}</label>
				  <div class="col-md-9 col-sm-9">
					<input class="form-control" maxlength="100" type="text" dir="auto" name="editland" value="{$row['land']}">
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['extra_minfo']}</label>
				  <div class="col-md-9 col-sm-9">
					<textarea dir="auto" style="width:100%; height:70px;" name="editinfo" class="classic">{$row['info']}</textarea>
				  </div>
				 </div>
				<div class="form-group">
				  <label class="control-label col-md-3 col-sm-3">{$lang['extra_signature']}</label>
				  <div class="col-md-9 col-sm-9">
					<textarea dir="auto" style="width:100%; height:70px;" name="editsignature" class="classic">{$row['signature']}</textarea>
				  </div>
				 </div>
				 {$output}
				<div class="form-group">
				  <div class="col-md-12">
					{$newssubscribe}
				  </div>
				 </div>
				<div class="form-group">
				  <div class="col-md-12">
					{$commsubscribe}
				  </div>
				 </div>
				<div class="form-group">
				  <div class="col-md-12">
					{$unsubscribe}
				  </div>
				 </div>
			</div>
			<div class="panel-footer">
				<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
				{$del_button}
			</div>
		</div>
		
		<input type="hidden" name="id" value="{$id}">
		<input type="hidden" name="mod" value="editusers">
		<input type="hidden" name="user_hash" value="{$dle_login_hash}">
		<input type="hidden" name="action" value="doedituser">
		<input type="hidden" name="prev_restricted" value="{$row['restricted_days']}">
		</form>
	</div>
	<div class="col-md-4">
		<div class="panel">

			<div class="user_heading bg-primary-700">
				<div class="user_heading_avatar">
					{$avatar}
					<h6>{$row['name']}</h6>
					<span>{$user_group[$row['user_group']]['group_name']}</span>
				</div>
				<div class="user_heading_content">
						 <ul class="user_stats">
							<li><h4>{$row['news_num']}<span class="sub-heading">{$lang['stats_news']}</span></h4></li>
							<li><h4>{$row['comm_num']}<span class="sub-heading">{$lang['stats_comments']}</span></h4></li>
						</ul>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">E-Mail</div>
					<div class="col-sm-6 tip" style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;" data-original-title="{$row['email']}">{$row['email']}</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">{$lang['stats_reg']}</div>
					<div class="col-sm-6">{$reg_date}</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">{$lang['stats_last']}</div>
					<div class="col-sm-6">{$last_date}</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">{$lang['stats_status']}</div>
					<div class="col-sm-6">{$status}</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">IP</div>
					<div class="col-sm-6"><a href="?mod=iptools&ip={$row['logged_ip']}" target="_blank">{$row['logged_ip']}</a></div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">{$lang['stats_name']}</div>
					<div class="col-sm-6">{$row['fullname']}</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row" style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;">
					<div class="col-sm-12 text-center">{$send_pm_link}</div>
					<div class="col-sm-12 text-center">{$news_link}</div>
					<div class="col-sm-12 text-center">{$comments_link}</div>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
		echofooter();
		
	} else {

		if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
			die( $lang['index_denied'] );
		}
	
		$css_path = $config['http_home_url']."templates/".$skin."/frame.css";
		$theme = $config['http_home_url']."templates/".$skin;
		
		$ignore_list = array();
		$temp_result = $db->query( "SELECT * FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}'" );
		while ( $temp_row = $db->get_row( $temp_result ) ) {
	
			if( $config['allow_alt_url'] ) {
				
				$user_name = "<a href=\"" . $config['http_home_url'] . "user/" . urlencode( $temp_row['user_from'] ) . "/\" target=\"_blank\">" . $temp_row['user_from'] . "</a>";
			
			} else {
				
				$user_name = "<a href=\"index.php?subaction=userinfo&amp;user=" . urlencode( $temp_row['user_from'] ) . "\" target=\"_blank\">" . $temp_row['user_from'] . "</a>";
		
			}
	
			$ignore_list[] = "<span id=\"dle-ignore-list-{$temp_row['id']}\">{$user_name}&nbsp;<a title=\"{$lang['del_from_ignore_1']}\" href=\"javascript:DelIgnorePM('" . $temp_row['id'] . "', '" . $lang['del_from_ignore'] . "');\"><svg width=\"18\" height=\"18\" fill=\"#f44336\" viewBox=\"0 0 256 256\" style=\"vertical-align: middle;\"><path d=\"M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z\"></path></svg></a>";
			
		}
		$db->free( $temp_result );
		
		if (count($ignore_list)) $ignore_list = implode("</span>, ", $ignore_list)."</span>"; else $ignore_list = "";

		$_SERVER['PHP_SELF'] = htmlspecialchars( $_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8' );
		
	echo <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
<meta charset="utf-8">
<title>{$lang['user_edhead']}</title>
<link rel="stylesheet" type="text/css" href="{$css_path}">
<link rel="stylesheet" type="text/css" media="all" href="engine/classes/calendar/calendar.css" />
<script src="engine/classes/js/jquery.js"></script>
<script src="engine/classes/calendar/calendar.js"></script>
</head>
<body>
<script>
<!--

jQuery.datetimepicker.setLocale('{$lang['language_code']}');

function confirmDelete(url){

	parent.DLEconfirmDelete( '{$lang['user_deluser']}', '{$lang['p_confirm']}', function () {

		document.location='{$_SERVER['PHP_SELF']}?mod=editusers&action=dodeleteuser&popup=yes&skin={$skin}&id={$row['user_id']}&user_hash='+url;

	} );

}

function DelIgnorePM( id, text ){

    parent.DLEconfirm( text, '{$lang['p_confirm']}', function () {

		parent.ShowLoading('');
	
		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: "del_ignore", user_hash: '{$dle_login_hash}', skin: '{$config['skin']}' }, function(data){
	
			parent.HideLoading('');
	
			$("#dle-ignore-list-" + id).html('');
			parent.DLEPush.info(data);
			return false;
		
	
		});

	} );
	
	return false;
};

//-->
</script>
HTML;
	
		$xfieldsaction = "admin";
		$xfieldsid = $row['xfields'];
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/userfields.php'));

		echo <<<HTML
<form name="saveuserform" id="saveuserform" action="" method="post" enctype="multipart/form-data">
<table width="99%">
    <tr>
        <td width="150" style="padding:4px;">{$lang['user_name']}</td>
        <td>{$row['name']}</td>
        <td rowspan="6" valign="top" align="right">{$avatar}</td>
    </tr>
    <tr>
        <td style="padding:4px;">IP:</td>
        <td><a href="#" onclick="parent.document.location='?mod=iptools&ip={$row['logged_ip']}'; return false;">{$row['logged_ip']}</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_news']}</td>
        <td>{$row['news_num']}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_last']}</td>
        <td>{$last_date}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_reg']}</td>
        <td>{$reg_date}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_mail']}</td>
        <td><input size="30" class="edit bk" name="editmail" value="{$row['email']}" dir="auto"> <label class="checkbox-inline"><input class="icheck" type="checkbox" name="allow_mail" value="1" {$mailbox}>{$lang['no_mail']}</label></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_newlogin']}</td>
        <td colspan="2"><input dir="auto" size="30" name="editlogin" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_newpass']}</td>
        <td colspan="2"><input dir="auto" size="30" name="editpass" class="edit bk"></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_acc']}</td>
        <td colspan="2"><select name="editlevel">{$group_list}</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_gtlimit']}</td>
        <td colspan="2"><input data-rel="calendardatetime" type="text" dir="auto" size="30" name="time_limit" id="time_limit" class="edit bk" value="{$row['time_limit']}" autocomplete="off"></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_banned']}</td>
        <td colspan="2"><input type="checkbox" name="banned" value="yes" $ifch></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['ban_date']}</td>
        <td colspan="2"><input dir="auto" size="5" name="banned_date" class="edit bk" value="{$row['days']}"> {$endban}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['ban_descr']}</td>
        <td colspan="2"><textarea dir="auto" style="width:100%; height:3.75rem;" name="banned_descr" class="bk">{$row['descr']}</textarea>{$banned_from}</td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['restricted']}</td>
        <td colspan="2"><select name="restricted"><option value="0" $restricted_selected[0]>{$lang['restricted_none']}</option>
<option value="1" $restricted_selected[1]>{$lang['restricted_news']}</option>
<option value="2" $restricted_selected[2]>{$lang['restricted_comm']}</option>
<option value="3" $restricted_selected[3]>{$lang['restricted_all']}</option>
</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['restricted_date']}</td>
        <td colspan="2"><input dir="auto" size="5" name="restricted_days" class="edit bk" value="{$row['restricted_days']}"> {$end_restricted}</td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_del_comments']}</td>
        <td colspan="2"><input type="checkbox" name="del_comments" value="yes" /></td>
    </tr>
    <tr>
        <td colspan="3"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_fullname']}</td>
        <td colspan="2"><input dir="auto" style="width:100%;" name="editfullname" value="{$row['fullname']}" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_land']}</td>
        <td colspan="2"><input dir="auto" style="width:100%;" name="editland" value="{$row['land']}" class="edit bk"></td>
    </tr>

    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">Gravatar:</td>
        <td colspan="2"><input dir="auto" size="30" name="gravatar" value="{$gravatar}" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_avatar']}</td>
        <td colspan="2"><input type="file" name="image" style="width:304px;" class="edit" /></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_del_avatar']}</td>
        <td colspan="2"><input type="checkbox" name="del_foto" value="yes" /></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['extra_minfo']}</td>
        <td colspan="2" style="padding-bottom:4px;"><textarea dir="auto" style="width:100%; height:70px;" name="editinfo" class="bk">{$row['info']}</textarea></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['extra_signature']}</td>
        <td colspan="2"><textarea dir="auto" style="width:100%; height:70px;" name="editsignature" class="bk">{$row['signature']}</textarea></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_at']}</td>
        <td colspan="2">{$timezoneselect}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['allowed_ip']}</td>
        <td colspan="2"><textarea dir="auto" style="width:100%; height:70px;" name="allowed_ip" class="bk">{$row['allowed_ip']}</textarea></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['ignore_list']}</td>
        <td colspan="2">{$ignore_list}</td>
    </tr>
	{$output}
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td colspan="3">{$newssubscribe}</td>
    </tr>
    <tr>
        <td colspan="3">{$commsubscribe}</td>
    </tr>
    <tr>
        <td colspan="3">{$unsubscribe}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;
    <input type="hidden" name="id" value="{$id}">
    <input type="hidden" name="mod" value="editusers">
    <input type="hidden" name="user_hash" value="$dle_login_hash">
    <input type="hidden" name="action" value="doedituser">
	<input type="hidden" name="popup" value="1">
	<input type="hidden" name="prev_restricted" value="{$row['restricted_days']}"></td>
    </tr>
</table>
</form>
</body>
</html>
HTML;


	}

} elseif( $action == "doedituser" ) {

	if( !$id ) {
		die( $lang['user_nouser'] );
	}

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		
		if($member_id['user_id'] != $id) {
			msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
		}
		
		$id = $member_id['user_id'];
	}

	if( !check_referer($_SERVER['PHP_SELF']."?mod=editusers") ) {
		
		if(isset($_POST['popup']) AND $_POST['popup']) die( $lang['no_referer'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['no_referer'], "javascript:history.go(-1)" );

	}
	
	$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id = '$id'" );

	$xfieldsid = stripslashes( $row['xfields'] );
	$member_user_group = $row['user_group'];

	if( !$row['user_id'] ) {
		
		if($_POST['popup']) die( "User not found" );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_nouser']), $lang['user_nouser'], "javascript:history.go(-1)" );
		
	}

	$sets=array();
	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );

	$parse = new ParseFilter();
	$parse->safe_mode = true;
	$parse->remove_html = true;
	$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
	$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
	$parse->allow_video = false;
	$parse->allow_media = false;

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 ) {
		
		if($_POST['popup']) die( $lang['edit_not_admin'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['edit_not_admin'], "javascript:history.go(-1)" );
		
	}
	
    if($_POST['editmail']) {
		
		$editmail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['editmail'] ) ) ) ) );
		
		if( empty( $editmail ) OR strlen( $editmail ) > 50 OR @count(explode("@", $editmail)) != 2) {
			
			if($_POST['popup']) die( $lang['mail_error'] );
			else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['mail_error'], "javascript:history.go(-1)" );
			
		}
		
		if ($editmail != $row['email']) {
	
			if ( $db->num_rows( $db->query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE email = '$editmail'" ) ) ) {
				if($_POST['popup']) die( $lang['user_err_4'] );
				else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_err_4'], "javascript:history.go(-1)" );
			}
			
			$sets[] = "email='{$editmail}'";
			
			$db->query( "UPDATE " . PREFIX . "_subscribe SET email='{$editmail}' WHERE user_id = '{$id}'" );
	
		}
	
	}
	
	if( $user_group[$member_id['user_group']]['admin_editusers'] ) {
		
		$editlevel = intval( $_POST['editlevel'] );

		if ($member_id['user_group'] != 1 AND $editlevel < 2 ){
			
			if($_POST['popup']) die( $lang['admin_not_access'] );
			else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['admin_not_access'], "javascript:history.go(-1)" );
			
		}
	
		if( $row['user_id'] == $member_id['user_id'] AND $editlevel != $row['user_group'] ) $editlevel = $row['user_group'];
		
		if( $editlevel == 5 ) $editlevel = 4;
		
		$sets[] = "user_group='{$editlevel}'";
		
		$time_limit = trim( $_POST['time_limit'] ) ? strtotime( $_POST['time_limit'] ) : "";
		
		if( !$user_group[$editlevel]['time_limit'] ) $time_limit = "";
		
		$sets[] = "time_limit='$time_limit'";
		
		if( isset($_POST['cat_add']) ) {
			$list = array();
			
			foreach ( $_POST['cat_add'] as $value ) {
				if( intval($value) > 0 ) $list[] = intval($value);
			}
			$sets[] = "cat_add='".$db->safesql( implode( ',', $list) )."'";
		}
		
		if( isset($_POST['cat_allow_addnews']) ) {
			$list = array();
			
			foreach ( $_POST['cat_allow_addnews'] as $value ) {
				if( intval($value) > 0 ) $list[] = intval($value);
			}
			$sets[] = "cat_allow_addnews='".$db->safesql( implode( ',', $list) )."'";
		}
		
		if( $_POST['editlogin'] ) {
			
			$editlogin = strtr($_POST['editlogin'], array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8')));
			$editlogin = trim($editlogin, chr(0xC2).chr(0xA0));
			$editlogin = preg_replace('#\s+#i', ' ', $editlogin);
		
			$editlogin = $db->safesql( $parse->process( htmlspecialchars( trim( $editlogin ), ENT_QUOTES, 'UTF-8' ) ) );
	
			if( preg_match( "/[\||\'|\<|\>|\[|\]|\%|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $editlogin ) OR dle_strlen($editlogin ) > 40 OR dle_strlen($editlogin) < 3 OR strpos( strtolower ($editlogin) , '.php' ) !== false) {
				
				if(isset($_POST['popup']) AND $_POST['popup']) die( $lang['user_err_6'] );
				else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_err_6'], "javascript:history.go(-1)" );
				
			}
			
			if( trim( $editlogin ) ) {
		
				$find_user = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE name='{$editlogin}'" );
		
				if( !isset($find_user['user_id']) ) {
		
					$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
					
					$db->query( "UPDATE " . PREFIX . "_post SET autor='{$editlogin}' WHERE autor='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_comments SET autor='{$editlogin}' WHERE autor='{$row['name']}' AND is_register='1'" );
					$db->query( "UPDATE " . USERPREFIX . "_ignore_list SET user_from='{$editlogin}' WHERE user_from='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_vote_result SET name='{$editlogin}' WHERE name='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_images SET author='{$editlogin}' WHERE author='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_files SET author='{$editlogin}' WHERE author='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_comments_files SET author='{$editlogin}' WHERE author='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_comment_rating_log SET `member`='{$editlogin}' WHERE `member`='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_logs SET `member`='{$editlogin}' WHERE `member`='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_post_extras SET editor='{$editlogin}' WHERE editor='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_subscribe SET name='{$editlogin}' WHERE name='{$row['name']}'" );
					$db->query( "UPDATE " . PREFIX . "_complaint SET `from`='{$editlogin}' WHERE `from`='{$row['name']}'" );
					
					$sets[] = "name='{$editlogin}'";
					
				} else {
					
					if($_POST['popup']) die( $lang['user_edit_found'] );
					else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_edit_found'], "javascript:history.go(-1)" );
					
				}
				
			}
			
		}
		
		if ( isset($_POST['banned']) AND $_POST['banned'] AND $row['user_group'] != 1 ) $banned = "yes"; else $banned = "";
		
		$sets[] = "banned='{$banned}'";

		if( $banned ) {
			$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['banned_descr'] ), false ) );
			
			$this_time = time();
			$banned_date = intval( $_POST['banned_date'] );
			$this_time = $banned_date ? $this_time + ($banned_date * 60 * 60 * 24) : 0;
			$banned_from = $db->safesql($member_id['name']);

			$banned_row = $db->super_query( "SELECT users_id, days FROM " . USERPREFIX . "_banned WHERE users_id = '{$id}'" );
	
			if( !isset($banned_row['users_id']) ) {
	
				$db->query( "INSERT INTO " . USERPREFIX . "_banned (users_id, descr, date, days, banned_from) values ('{$id}', '{$banned_descr}', '{$this_time}', '{$banned_date}', '{$banned_from}')" );
	
			} else {
	
				if( $banned_row['days'] != $banned_date ) $db->query( "UPDATE " . USERPREFIX . "_banned set descr='{$banned_descr}', days='{$banned_date}', date='{$this_time}', banned_from='{$banned_from}' WHERE users_id = '{$id}'" );
				else $db->query( "UPDATE " . USERPREFIX . "_banned set descr='{$banned_descr}' WHERE users_id = '{$id}'" );
	
			}
	
			$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE user_id='{$id}'" );
	
			@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
		} else {
	
			$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE users_id = '{$id}'" );
			@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
		}

		if( $_POST['restricted'] ) {
	
			$restricted = intval( $_POST['restricted'] );
			$restricted_days = intval( $_POST['restricted_days'] );
	
			$sets[] = "restricted='{$restricted}'";
	
			if( $restricted_days != $_POST['prev_restricted'] ) {
	
				$restricted_date = time();
				$restricted_date = $restricted_days ? $restricted_date + ($restricted_days * 60 * 60 * 24) : '';
	
				$sets[] = "restricted_days='$restricted_days', restricted_date='$restricted_date'";
	
			}
	
		} else {
	
			$sets[] = "restricted='0', restricted_days='0', restricted_date=''";
	
		}
		
		if( isset($_POST['del_comments']) AND $_POST['del_comments'] ) {
	
			$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' WHERE user_id ='{$id}'" );
			deletecommentsbyuserid($id);
	
		}
	}
	
	if( trim( $_POST['editpass'] ) != "" ) {
		
		$editpass = $db->safesql( password_hash($_POST['editpass'], PASSWORD_DEFAULT) );
		
		if( !$editpass ) {
			die("PHP extension Crypt must be loaded for password_hash to function");
		}
		
		$sets[] = "password='{$editpass}'";

	}
	
	if( isset($_POST['allow_mail']) AND $_POST['allow_mail'] ) $allow_mail = 0; else $allow_mail = 1;
	
	$sets[] = "allow_mail='{$allow_mail}'";

	$timezone = $db->safesql( (string)$_POST['timezone'] );		
		
	if ( !in_array( $timezone, DateTimeZone::listIdentifiers() ) ) $timezone = '';
	
	$sets[] = "timezone='{$timezone}'";

	if ($_POST['allowed_ip']) {

		$_POST['allowed_ip'] = str_replace( "\r", "", trim( $_POST['allowed_ip'] ) );
		$allowed_ip = str_replace( "\n", "|", $_POST['allowed_ip'] );
	
		$temp_array = explode ("|", $allowed_ip);
		$allowed_ip	= array();
	
		if (count($temp_array)) {
	
			foreach ( $temp_array as $value ) {
				$value = explode ('/', trim($value) );
				$value1 = $value[0];
				
				$value[0] = str_replace( "*", "0", $value[0] );

				
				if ( filter_var( $value[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
					$value[0] = filter_var( $value[0] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
				} elseif ( filter_var( $value[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
					$value[0] = filter_var( $value[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
				} else $value[0] = false;
		
				if( $value[0] ) {
					$value[0] = $value1;
					if( intval($value[1]) ) {
						$allowed_ip[] = trim($value[0])."/".intval($value[1]);
					} else $allowed_ip[] = trim($value[0]);
				}
			}
		
		}
	
		if ( count($allowed_ip) ) $allowed_ip = $db->safesql( $parse->process( implode("|", $allowed_ip) ) ); else $allowed_ip = "";

	} else $allowed_ip = "";
	
	$_POST['editfullname'] = isset($_POST['editfullname']) ? $_POST['editfullname'] : '';
	$_POST['editland'] = isset($_POST['editland']) ? $_POST['editland'] : '';
	$_POST['editinfo'] = isset($_POST['editinfo']) ? $_POST['editinfo'] : '';
	$_POST['editsignature'] = isset($_POST['editsignature']) ? $_POST['editsignature'] : '';
	
	$_POST['news_subscribe'] = isset($_POST['news_subscribe']) ? $_POST['news_subscribe'] : 0;
	$_POST['comments_reply_subscribe'] = isset($_POST['comments_reply_subscribe']) ? $_POST['comments_reply_subscribe'] : 0;
	$_POST['twofactor_auth'] = isset($_POST['twofactor_auth']) ? intval($_POST['twofactor_auth']) : 0;
	
	$sets[] = "allowed_ip='{$allowed_ip}'";
	$sets[] = "fullname='".$db->safesql( $parse->process( $_POST['editfullname'] ) )."'";
	$sets[] = "land='".$db->safesql( $parse->process( $_POST['editland'] ) )."'";
	$sets[] = "info='".$db->safesql( $parse->BB_Parse( $parse->process( $_POST['editinfo'] ), false ) )."'";
	$sets[] = "signature='".$db->safesql( $parse->BB_Parse( $parse->process( $_POST['editsignature'] ), false ) )."'";
	$sets[] = "news_subscribe='".intval($_POST['news_subscribe'])."'";
	$sets[] = "comments_reply_subscribe='".intval($_POST['comments_reply_subscribe'])."'";

	if ( !isset($_POST['popup']) OR !$_POST['popup'] ) {

		if ($_POST['twofactor_auth'] == 2) {

			if ($row['twofactor_secret']) $sets[] = "twofactor_auth='2'"; else $sets[] = "twofactor_auth='0'";

		} else {

			$sets[] = "twofactor_auth='" . intval($_POST['twofactor_auth']) . "', twofactor_secret=''";

		}

	}
	
	if ( isset($_POST['unsubscribe']) AND $_POST['unsubscribe'] ) $db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE user_id = '{$row['user_id']}'" );

	if ( isset($_POST['gravatar']) AND $_POST['gravatar'] ) {

		$gravatar = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['gravatar'] ) ) ) ) );

		if ( count(explode("@", $gravatar)) == 2 AND strlen( $gravatar ) < 50 ) {
			$sets[] = "foto='{$gravatar}'";
		} else $sets[] = "foto=''";

	} else {

		if (count(explode("@", $row['foto'])) == 2) $sets[] = "foto=''";

	}

	$image = $_FILES['image']['tmp_name'];
	$image_size = $_FILES['image']['size'];
	$file_parts = pathinfo( $_FILES['image']['name'] );

	if( is_uploaded_file( $image ) ) {
		
		if( intval( $user_group[$member_id['user_group']]['max_foto'] ) > 0 ) {
			
			if( !$config['avatar_size'] OR $image_size < ($config['avatar_size'] * 1024) ) {

				$driver = DLEFiles::getDefaultStorage();
				$config['avatar_remote'] = intval($config['avatar_remote']);
				if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];

				DLEFiles::init( $driver, $config['local_on_fail'] );
				$thumb = new thumbnail( $_FILES['image']['tmp_name'] );
				
				if ( !$thumb->error) {
					
					if( !$config['tinypng_avatar'] ) {
						$thumb->tinypng = false;
					}
					
					$thumb->tinypng_resize = true;
					$thumb->size_auto( $user_group[$member_id['user_group']]['max_foto'] );
					
					if( $row['foto'] ) {
						
						$url = @parse_url ( $row['foto'] );
						$row['foto'] = basename($url['path']);
						
						DLEFiles::Delete( "fotos/".totranslit($row['foto']) );
						
						$db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id = '{$id}'" );
					
					}
		
					$foto_name = $thumb->save( "fotos/foto_" . $row['user_id'] . '_' . $_TIME . "." . $file_parts['extension'] );
					
					if ( $foto_name AND !$thumb->error) {
						
						if ( $driver AND !DLEFiles::$remote_error ) {
							
							$foto_name = $db->safesql( DLEFiles::GetBaseURL() . "fotos/" . $foto_name );
							
						} else {
							
							if (strpos($config['http_home_url'], "//") === 0) $avatar_url = $config['http_home_url'];
							elseif (strpos($config['http_home_url'], "/") === 0) $avatar_url = "//".$_SERVER['HTTP_HOST'].$config['http_home_url'];
							else $avatar_url = $config['http_home_url'];
							
							$avatar_url = str_ireplace("https:", "", $avatar_url);
							$avatar_url = str_ireplace("http:", "", $avatar_url);
							
							$foto_name = $db->safesql( $avatar_url . "uploads/fotos/" . $foto_name );
							
						}
						
						$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$foto_name}' WHERE user_id = '{$id}'" );	

					}
					
				}
				
			}
			
		}

	}

	if( isset($_POST['del_foto']) AND $_POST['del_foto'] == "yes" ) {
		$row_foto = $db->super_query( "SELECT foto FROM " . USERPREFIX . "_users WHERE user_id='$id'" );

		if(isset($row_foto['foto']) AND $row_foto['foto']) {
			$sets[] = "foto=''";
			
			$url = @parse_url ($row_foto['foto'] );
			$row_foto['foto'] = basename($url['path']);

			$driver = DLEFiles::getDefaultStorage();
			$config['avatar_remote'] = intval($config['avatar_remote']);
			if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];

			DLEFiles::init( $driver );
			DLEFiles::Delete( "fotos/".totranslit($row_foto['foto']) );
		}
	}

	$xfieldsaction = "init";
	$xfieldsadd = false;

	$parse->allow_url = $user_group[$member_user_group]['allow_url'];
	$parse->allow_image = $user_group[$member_user_group]['allow_image'];

	include (DLEPlugins::Check(ENGINE_DIR . '/inc/userfields.php'));
	$filecontents = array ();

	if( is_array($postedxfields) AND count( $postedxfields ) ) {
		
		foreach ( $postedxfields as $xfielddataname => $xfielddatavalue ) {
			
			if( trim($xfielddatavalue)  == "" ) {
				continue;
			}

			$xfielddatavalue = $db->safesql($xfielddatavalue);
		
			$xfielddataname = $db->safesql( str_replace( $not_allow_symbol, '', $xfielddataname) );
			
			$xfielddataname = str_replace( "|", "&#124;", $xfielddataname );
			$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
			$filecontents[] = "$xfielddataname|$xfielddatavalue";
		}
		
		$filecontents = implode( "||", $filecontents );
		
		$sets[] = "xfields='{$filecontents}'";
		
	} else $filecontents = '';

	$db->query( "UPDATE " . USERPREFIX . "_users SET ".implode(", ", $sets)." WHERE user_id='{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '64', '{$row['name']}')" );

	if(isset($_POST['popup']) AND $_POST['popup']) {
		
		$_SERVER['REQUEST_URI'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );
		$_SERVER['REQUEST_URI'] = str_replace("&amp;","&", $_SERVER['REQUEST_URI'] );
		
		header( "Location: {$_SERVER['REQUEST_URI']}" );
		die();
		
	} else msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_editok']), $lang['opt_peok'], array($_SESSION['admin_user_referrer'] => $lang['add_s_6'], "?mod=editusers&action=edituser&id=".$id  => $lang['add_s_4']) );

} elseif( $action == "dodeleteuser" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	if( ! $id ) {
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['user_nouser'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_nouser'] );
	}

	if( $id == 1 ) {
		
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['user_undel'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_undel'] );
		
	}

	$row = $db->super_query("SELECT email, name, user_id, user_group, foto, news_num FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
	
	if( !isset($row['user_id']) OR !$row['user_id'] ) {
		
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['user_nouser'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_nouser'] );
		
	}
	
	if( $member_id['user_id'] == $row['user_id']) {
		
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['no_self'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['no_self'] );
		
	}

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 ) {
		
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['user_undel'] );
		else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['user_undel'] );
		
	}

	if( !isset($_REQUEST['new_username']) AND $row['news_num']) {
		
		if (isset($_REQUEST['popup']) AND $_REQUEST['popup']  == "yes") {

			$css_path = $config['http_home_url'] . "templates/" . trim(totranslit($_REQUEST['skin'], false, false)) . "/frame.css";

			echo <<<HTML
<!doctype html>
<html lang="{$lang['language_code']}" dir="{$lang['direction']}">
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="{$css_path}">
</head>
<body>
<form method="get">
<table width="100%">
	<tr>
	<td style="padding:4px;" colspan="2">{$lang['set_new_name']}</td>
	</tr>
	<tr>
		<td width="230" style="padding:4px;">{$lang['edit_selauthor_2']}</td>
		<td><input dir="auto" size="40" name="new_username" class="edit bk"></td>
	</tr>
	<tr>
	<td style="padding:4px;" colspan="2"><input type="submit" value="{$lang['b_start']}">
	
			<input type="hidden" name="action" value="dodeleteuser">
			<input type="hidden" name="mod" value="editusers">
			<input type="hidden" name="popup" value="yes">
			<input type="hidden" name="id" value="{$row['user_id']}">
			<input type=hidden name=user_hash value="{$dle_login_hash}">
	</td>
	</tr>
</table>
</form>
</body>
</html>
HTML;

			die();			
		
		} else {

			echoheader("<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['edit_selauthor_1']) );
			
			if (isset($_REQUEST['self_delete_user']) and $_REQUEST['self_delete_user'] == 'self_delete_user') {
				$self = '<input type="hidden" name="self_delete_user" value="self_delete_user">';
			} else $self = '';

			echo <<<HTML
	<form method="get">
	<div class="panel panel-default">
	  <div class="panel-heading">
		{$lang['edit_selauthor_1']}
	  </div>
	  <div class="panel-body">
			<table width="100%">
				<tr>
					<td height="100" class="text-center"><div class="alert alert-warning alert-styled-left text-left">{$lang['set_new_name']}</div>{$lang['edit_selauthor_2']}<input type="text" dir="auto" name="new_username" class="form-control position-left position-right" style="width:200px;">
					<input type="hidden" name="action" value="dodeleteuser">
					<input type="hidden" name="mod" value="editusers">
					<input type="hidden" name="id" value="{$row['user_id']}">
					<input type=hidden name=user_hash value="{$dle_login_hash}">{$self}
					<input type="submit" value="{$lang['b_start']}" class="btn bg-teal btn-sm btn-raised"></td>
				</td>
				</tr>
			</table>
	  </div>
	</div></form>
HTML;

			echofooter();
			die();

		}
		
	} elseif( isset($_REQUEST['new_username']) AND $_REQUEST['new_username'] AND $row['news_num']) {

		$new_username = $db->safesql( trim( strip_tags( urldecode( $_REQUEST['new_username'] ) ) ) );
		$row_new_user = $db->super_query( "SELECT user_id, name, news_num FROM " . USERPREFIX . "_users WHERE name = '{$new_username}' AND user_id != '{$row['user_id']}' " );

		if( !$row_new_user['user_id'] ) {
			
			if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") die( $lang['edit_selauthor_3'] );
			else msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_err']), $lang['edit_selauthor_3'] );
			
		}
		
		$db->query( "UPDATE " . PREFIX . "_post SET autor='{$row_new_user['name']}' WHERE autor='{$row['name']}'" );
		$db->query( "UPDATE " . PREFIX . "_post_extras SET user_id='{$row_new_user['user_id']}' WHERE user_id='{$row['user_id']}'" );
		$db->query( "UPDATE " . PREFIX . "_images SET author='{$row_new_user['name']}' WHERE author='{$row['name']}'" );
		$db->query( "UPDATE " . PREFIX . "_files SET author='{$row_new_user['name']}' WHERE author='{$row['name']}'" );
		$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num+{$row['news_num']} WHERE user_id='{$row_new_user['user_id']}'" );
		clear_cache( array('news_', 'comm_', 'full_') );
	}

	deleteuserbyid($id);

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '65', '{$row['name']}')" );

	clear_cache(array('stats'));

	if( isset($_REQUEST['self_delete_user']) AND $_REQUEST['self_delete_user'] == 'self_delete_user') {

		if (strpos($config['http_home_url'], "//") === 0) {
			$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https:" . $config['http_home_url'] : $config['http_home_url'] = "http:" . $config['http_home_url'];
		} elseif (strpos($config['http_home_url'], "/") === 0) {
			$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'] : "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
		} elseif (isSSL() and stripos($config['http_home_url'], 'http://') !== false) {
			$config['http_home_url'] = str_replace("http://", "https://", $config['http_home_url']);
		}

		$mail = new dle_mail($config, false);
		
		$lang['selfdel_wait_5'] = str_replace('{name}', $row['name'], $lang['selfdel_wait_5']);
		$lang['selfdel_wait_5'] = str_replace('{site}', $config['http_home_url'], $lang['selfdel_wait_5']);

		$mail->send($row['email'], $lang['selfdel_wait_4'], $lang['selfdel_wait_5']);

	}

	if (isset($_REQUEST['popup']) AND $_REQUEST['popup'] == "yes") {

		die( $lang['user_ok']." ".$lang['user_delok_1'] );

	} else {

		msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_delok']), "{$lang['user_ok']} {$lang['user_delok_1']}", $_SESSION['admin_user_referrer'] );

	}

} elseif( $action == "dodelcomments" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( !$id ) {
		msg("error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['user_nouser']);
	}

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '97', '".$db->safesql($row['name'])."')" );

	if($_GET['moderation'] == "only") {
		
		$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1' AND approve='0'" );
		
	} else {
		
		$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1'" );
		
	}
	while ( $row = $db->get_array( $result ) ) {

		deletecomments( $row['id'] );

	}
	$db->free( $result );
	
	if($_GET['moderation'] != "only") {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num='0' WHERE user_id ='$id'" );
	}

	clear_cache(array('news_', 'comm_', 'full_', 'stats'));

	msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_delok']), $lang['comm_alldel'], $_SESSION['admin_user_referrer'] );
	
} elseif( $action == "dodelnews" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( ! $id ) {
		die( $lang['user_nouser'] );
	}

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
	}
	
	$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '98', '".$db->safesql($row['name'])."')" );

	if($_GET['moderation'] == "only") {
		$result = $db->query( "SELECT id FROM " . PREFIX . "_post WHERE autor='".$db->safesql($row['name'])."' AND approve='0'" );
	} else {
		$result = $db->query( "SELECT news_id as id FROM " . PREFIX . "_post_extras WHERE user_id='{$id}'" );	
	}

	while ( $row = $db->get_array( $result ) ) {
		
		deletenewsbyid( $row['id'] );
		
	}
	
	if($_GET['moderation'] != "only") {
		$db->query( "UPDATE " . USERPREFIX . "_users SET news_num='0' WHERE user_id ='{$id}'" );
	}
	
	$db->free( $result );
	clear_cache(array('news_', 'full_', 'comm_', 'related_', 'tagscloud_', 'archives_', 'calendar_', 'topnews_', 'rss', 'stats'));
	
	msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['user_delok']), $lang['news_alldel'], $_SESSION['admin_user_referrer'] );
	
} elseif( $action == "dochangenews" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$newuser = $db->safesql( trim( urldecode ( $_GET['newuser'] ) ) );
	$old_user_id = intval($_GET['id']);

	$row = $db->super_query( "SELECT user_id, name FROM " . USERPREFIX . "_users WHERE name = '{$newuser}'" );
	
	if( isset($row['user_id']) AND $row['user_id'] ) {

		$new_user_id = $row['user_id'];
		$new_user_name = $db->safesql($row['name']);
		
		$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id = '{$old_user_id}'" );
		
		$old_user_name = $db->safesql($row['name']);
		
		if($new_user_id AND $new_user_name AND $old_user_id AND $old_user_name ) {
			
			$db->query( "UPDATE " . PREFIX . "_post SET autor='{$new_user_name}' WHERE autor='{$old_user_name}'" );
			$db->query( "UPDATE " . PREFIX . "_post_extras SET user_id='{$new_user_id}' WHERE user_id='{$old_user_id}'" );
			$db->query( "UPDATE " . PREFIX . "_images SET author='{$new_user_name}' WHERE author='{$old_user_name}'" );
			$db->query( "UPDATE " . PREFIX . "_files SET author='{$new_user_name}' WHERE author='{$old_user_name}'" );
			$db->query( "UPDATE " . USERPREFIX . "_users SET news_num='0' WHERE user_id='{$old_user_id}'" );
			
			$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post_extras WHERE user_id='{$new_user_id}'" );
			
			$db->query( "UPDATE " . USERPREFIX . "_users SET news_num='{$row['count']}' WHERE user_id='{$new_user_id}'" );
			
			clear_cache(array('news_', 'full_', 'related_', 'topnews_'));
			msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['edit_selauthor_4']), $lang['news_allchange']." <b>{$new_user_name}</b>", $_SESSION['admin_user_referrer'] );
			

		} else {
			msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['addnews_error']), $lang['user_nouser'], "javascript:history.go(-1)" );
		}
		
		

	} else {

		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['addnews_error']), $lang['user_nouser'], "javascript:history.go(-1)" );

	}
	
} elseif( $action == "dorebuildnews" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$user_id = intval($_GET['id']);
	
	$row = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE user_id = '{$user_id}'" );
	
	if( !$row['user_id'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['user_nouser'], "javascript:history.go(-1)" );
	}
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post_extras WHERE user_id='{$user_id}'" );
			
	$db->query( "UPDATE " . USERPREFIX . "_users SET news_num='{$row['count']}' WHERE user_id='{$user_id}'" );

	msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['r_ok1']), $lang['r_ok1'], $_SESSION['admin_user_referrer'] );
	
} elseif( $action == "dorebuildcomments" ) {
	
	if( !$user_group[$member_id['user_group']]['admin_editusers'] ) {
		msg( "error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied'] );
	}
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$user_id = intval($_GET['id']);
	
	$row = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE user_id = '{$user_id}'" );
	
	if( !$row['user_id'] ) {
		msg( "error", $lang['addnews_error'], $lang['user_nouser'], "javascript:history.go(-1)" );
	}
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE user_id='{$user_id}'" );
			
	$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num='{$row['count']}' WHERE user_id='{$user_id}'" );

	msg( "success", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['r_ok2']), $lang['r_ok2'], $_SESSION['admin_user_referrer'] );

} elseif ($action == "dorejectrequests") {

	if (!$user_group[$member_id['user_group']]['admin_editusers']) {
		msg("error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['index_denied']);
	}

	if (!isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash) {

		die("Hacking attempt! User not found");
	}

	$user_id = intval($_REQUEST['id']);

	$row = $db->super_query("SELECT email, name, user_id FROM " . USERPREFIX . "_users WHERE user_id = '{$user_id}'");

	if (!isset($row['user_id']) OR !$row['user_id']) {
		msg("error", array($_SESSION['admin_user_referrer'] => $lang['user_head'], '' => $lang['index_denied']), $lang['user_nouser'], "javascript:history.go(-1)");
	}

	$db->query("DELETE FROM " . USERPREFIX . "_users_delete WHERE user_id='{$row['user_id']}'");

	if( $_POST['text'] ) {

		$parse = new ParseFilter();
		$parse->safe_mode = true;
		$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
		$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
		$parse->allowbbcodes = false;
		
		$message = <<<HTML
{$lang['selfdel_wait_6']}

[quote]{$_POST['text']}[/quote]
HTML;

		$message = $db->safesql($parse->BB_Parse($parse->process(trim($message)), false));

		$db->query("INSERT INTO " . USERPREFIX . "_conversations (subject, created_at, updated_at, sender_id, recipient_id) values ('{$lang['selfdel_wait_4']}', '{$_TIME}', '{$_TIME}', '{$member_id['user_id']}', '{$row['user_id']}')");
		$conversation_id = $db->insert_id();
		$db->query("INSERT INTO " . USERPREFIX . "_conversation_users (user_id, conversation_id) values ('{$row['user_id']}', '{$conversation_id}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)");
		$db->query("INSERT INTO " . USERPREFIX . "_conversations_messages (conversation_id, sender_id, content, created_at) values ('{$conversation_id}', '{$member_id['user_id']}', '{$message}', '{$_TIME}')");

		$db->query("UPDATE " . USERPREFIX . "_users set pm_all=pm_all+1, pm_unread=pm_unread+1 WHERE user_id='{$row['user_id']}'");

		if ($config['mail_pm']) {

			$mail_template = $db->super_query("SELECT * FROM " . PREFIX . "_email WHERE name='pm' LIMIT 0,1");
			$mail = new dle_mail($config, $mail_template['use_html']);

			if (strpos($config['http_home_url'], "//") === 0) $slink = "https:" . $config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
			else $slink = $config['http_home_url'];

			$slink = $slink . "index.php?do=pm&doaction=readpm&pmid=" . $conversation_id;

			$mail_template['template'] = stripslashes($mail_template['template']);
			$mail_template['template'] = str_replace("{%username%}", $row['name'], $mail_template['template']);
			$mail_template['template'] = str_replace("{%date%}", langdate("j F Y H:i", $_TIME), $mail_template['template']);
			$mail_template['template'] = str_replace("{%fromusername%}", $member_id['name'], $mail_template['template']);
			$mail_template['template'] = str_replace("{%title%}", $lang['selfdel_wait_4'], $mail_template['template']);
			$mail_template['template'] = str_replace("{%url%}", $slink, $mail_template['template']);

			$message = stripslashes(stripslashes($message));

			if (!$mail_template['use_html']) {
				$message = str_replace("<br>", "\n", $message);
				$message = str_replace('&quot;', '"', $message);
				$message = strip_tags($message);
			}

			$mail_template['template'] = str_replace("{%text%}", $message, $mail_template['template']);

			$mail->send($row['email'], $lang['selfdel_wait_4'], $mail_template['template']);
		}

		die('ok');
	}

	header("Location: ?mod=editusers");
	die();

} else {

	header("Location: ?mod=editusers");
	die();

}

?>