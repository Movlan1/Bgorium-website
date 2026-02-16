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
 File: blockip.php
-----------------------------------------------------
 Use: Blocking visitors by IP
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_blockip'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['ip_add'] ) ) $ip_add = htmlspecialchars( strip_tags( trim( $_REQUEST['ip_add'] ) ), ENT_QUOTES, 'UTF-8' ); else $ip_add = "";
if( isset( $_REQUEST['ip'] ) ) $ip = htmlspecialchars( strip_tags( trim( $_REQUEST['ip'] ) ), ENT_QUOTES, 'UTF-8' ); else $ip = "";
if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = 0;

$start_from = isset( $_REQUEST['start_from'] ) ? intval( $_REQUEST['start_from'] ) : 0;
$news_per_page = 50;

if( $start_from < 0 ) $start_from = 0;

if( $start_from < 0 ) $start_from = 0;

if (isset( $_REQUEST['searchword'] ) AND $_REQUEST['searchword']) {
  
  $searchword = htmlspecialchars( strip_tags( stripslashes( trim( urldecode ( $_REQUEST['searchword'] ) ) ) ), ENT_COMPAT, 'UTF-8' );
  
} else $searchword = "";

if ($searchword) $urlsearch = "&searchword={$searchword}"; else $urlsearch = "";

$parse = new ParseFilter();
$parse->safe_mode = true;

if ($_REQUEST['action'] == "mass_delete") {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
	}

	if( !isset($_POST['selected_ips']) ) {
		msg( "error", $lang['mass_error'], $lang['ip_sel_error'], "?mod=blockip" );
	}
	
	foreach ( $_POST['selected_ips'] as $id ) {
		$id = intval($id);
		$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE id = '{$id}'" );
	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '10', '')" );
	
	@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
	header( "Location: ?mod=blockip&start_from={$start_from}{$urlsearch}" );
	die();
	
	
}

if ($_REQUEST['action'] == "edit") {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
	}

	$id = intval ( $_POST['id'] );
	$ip_add = $db->safesql(trim($ip_add));
	$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['descr'] ), false ) );
	$banned_from = $db->safesql($member_id['name']);

	if( !trim( $_POST['date'] ) OR (($_POST['date'] = strtotime( $_POST['date'] )) === - 1) OR !$_POST['date']) {
		$this_time = 0;
		$days = 0;
	} else {
		$this_time = $db->safesql($_POST['date']);
		$days = 1;
	}
	
	if( !$ip_add ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "?mod=blockip" );
	}
	
	$db->query( "UPDATE " . USERPREFIX . "_banned SET `descr`='{$banned_descr}', `date`='{$this_time}', `days`='{$days}', `ip`='{$ip_add}', banned_from='{$banned_from}' WHERE id='{$id}'" );
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '118', '{$ip_add}')" );
	
	@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
	header( "Location: ?mod=blockip&start_from={$start_from}{$urlsearch}" );
	die();
	
}

if( $_REQUEST['action'] == "add" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
	}
	
	$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['descr'] ), false ) );
	$banned_from = $db->safesql($member_id['name']);

	if( !trim( $_POST['date'] ) OR (($_POST['date'] = strtotime( $_POST['date'] )) === - 1) OR !$_POST['date']) {
		$this_time = 0;
		$days = 0;
	} else {
		$this_time = $db->safesql($_POST['date']);
		$days = 1;
	}
	
	if( !$ip_add ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "?mod=blockip" );
	}

	$ips = explode("\n", $ip_add);
	
	foreach ($ips as $ip_add) {
		$ip_add = $db->safesql(trim($ip_add));
		
		if($ip_add) {
			$row = $db->super_query( "SELECT id FROM " . PREFIX . "_banned WHERE ip ='{$ip_add}'" );
			
			if ( !$row['id'] ) {
				$db->query( "INSERT INTO " . USERPREFIX . "_banned (descr, date, days, ip, banned_from) values ('{$banned_descr}', '{$this_time}', '{$days}', '{$ip_add}', '{$banned_from}')" );
			}
		}
		
	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '9', '')" );
	
	@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
	header( "Location: ?mod=blockip&start_from={$start_from}{$urlsearch}" );
	die();
	
} elseif( $_REQUEST['action'] == "delete" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( !$id ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "?mod=blockip" );
	}
	
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE id = '{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '10', '')" );

	@unlink( ENGINE_DIR . '/cache/system/banned.json' );
	
	header( "Location: ?mod=blockip&start_from={$start_from}{$urlsearch}" );
	die();

}

echoheader( "<i class=\"fa fa-lock position-left\"></i><span class=\"text-semibold\">{$lang['opt_ipban']}</span>", $lang['header_filter_1'] );

echo <<<HTML
<div class="modal fade" id="newblock" tabindex="-1" role="dialog" aria-labelledby="newblockLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
	<form method="post" action="" class="form-horizontal">
	<input type="hidden" name="mod" value="blockip">
	<input type="hidden" name="action" value="add">
	<input type="hidden" name="user_hash" value="{$dle_login_hash}">
      <div class="modal-header ui-dialog-titlebar">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<span class="ui-dialog-title" id="newcatsLabel">{$lang['ip_add']}</span>
      </div>
      <div class="modal-body">

		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['ip_type']}</label>
		  <div class="col-sm-8">
		    <textarea dir="auto" class="classic" style="width:100%" rows="5" name="ip_add">{$ip}</textarea>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['ban_date']}</label>
		  <div class="col-sm-8">
			<input  class="form-control" style="width:190px;" data-rel="calendar" type="text" dir="auto" name="date" autocomplete="off">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-4">{$lang['ban_descr']}</label>
		  <div class="col-sm-8">
			<textarea dir="auto" class="classic" style="width:100%" rows="5" name="descr"></textarea>
		  </div>
		 </div>
	  
		<div class="text-muted text-size-small">{$lang['ip_example']}</div>
	  
      </div>
      <div class="modal-footer">
		<button type="button" class="btn bg-grey-400 btn-sm btn-raised" data-dismiss="modal">{$lang['p_cancel']}</button>
	    <button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
      </div>
	  </form>
    </div>
  </div>
</div>
HTML;

echo <<<HTML
<form action="?mod=links" method="get" name="navi" id="navi">
<input type="hidden" name="mod" value="blockip">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="searchword" value="{$searchword}">
</form>
<form action="?mod=blockip" method="post" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="blockip">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['ip_list']}
	<div class="heading-elements">
		<div class="form-group has-feedback" style="width:250px;">
			<input name="searchword" type="search" dir="auto" class="form-control" placeholder="{$lang['search_field']}" onchange="document.optionsbar.start_from.value=0;" value="{$searchword}">
			<div class="form-control-feedback">
			    <a href="#" onclick="$('#optionsbar').submit(); return false;"><i class="fa fa-search text-size-base text-muted"></i></a>
			</div>
		</div>
	</div>
  </div>
HTML;


$i = $start_from+$news_per_page;

if ( $searchword ) {
  
  $searchword = @$db->safesql($searchword);
  $where = "WHERE users_id = '0' AND (ip like '%$searchword%' OR descr like '%$searchword%') ";
  $lang['ip_empty'] = $lang['tags_s_not_found'];
  
} else $where = "WHERE users_id = '0'";

$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_banned {$where}");
$all_count_news = $result_count['count'];

// pagination

$npp_nav = "";

if( $all_count_news > $news_per_page ) {

	if( $start_from > 0 ) {
		$previous = $start_from - $news_per_page;
		$npp_nav .= "<li><a onclick=\"javascript:search_submit($previous); return false;\" href=\"#\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a></li>";
	}
	
	$enpages_count = @ceil( $all_count_news / $news_per_page );
	$enpages_start_from = 0;
	$enpages = "";
	
	if( $enpages_count <= 10 ) {
		
		for($j = 1; $j <= $enpages_count; $j ++) {
			
			if( $enpages_start_from != $start_from ) {
				
				$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return false;\" href=\"#\">$j</a></li>";
			
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
			
			$enpages .= "<li><a onclick=\"javascript:search_submit(0); return false;\" href=\"#\">1</a></li> <li><span>...</span></li>";
		
		}
		
		for($j = $start; $j <= $end; $j ++) {
			
			if( $enpages_start_from != $start_from ) {
				
				$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return false;\" href=\"#\">$j</a></li>";
			
			} else {
				
				$enpages .= "<li class=\"active\"><span>$j</span></li>";
			}
			
			$enpages_start_from += $news_per_page;
		}
		
		$enpages_start_from = ($enpages_count - 1) * $news_per_page;
		$enpages .= "<li><span>...</span></li><li><a onclick=\"javascript:search_submit($enpages_start_from); return false;\" href=\"#\">$enpages_count</a></li>";
		
		$npp_nav .= $enpages;
	
	}
	
	if( $all_count_news > $i ) {
		$how_next = $all_count_news - $i;
		if( $how_next > $news_per_page ) {
			$how_next = $news_per_page;
		}
		$npp_nav .= "<li><a onclick=\"javascript:search_submit($i); return false;\" href=\"#\" title=\"{$lang['edit_next']}\">&gt;&gt;</a></li>";
	}
	
	$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";

}

// pagination
if ( $all_count_news ) {

$db->query( "SELECT * FROM " . USERPREFIX . "_banned {$where}ORDER BY id DESC LIMIT {$start_from},{$news_per_page}" );

$i = 0;
if( !$langformatdatefull ) $langformatdatefull = "d.m.Y H:i";

echo <<<HTML
  <div class="table-responsive">
    <table class="table table-striped table-xs table-hover">
      <thead>
      <tr>
        <th style="width: 12.5rem">{$lang['title_filter']}</th>
        <th style="width: 11.875rem">{$lang['ban_date']}</th>
        <th>{$lang['ban_descr']}</th>
        <th style="width: 4.375rem">&nbsp;</th>
		<th style="width: 2.5rem"><input class="icheck" type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></th>
      </tr>
      </thead>
	  <tbody>
HTML;
	
	while ( $row = $db->get_row() ) {
		$i ++;
		
		if( $row['date'] ) {
			$endban = langdate( $langformatdatefull, $row['date'] );
			$editendban = date( "Y-m-d H:i:s", $row['date'] );
		} else {
			$endban = $lang['banned_info'];
			$editendban = "";
		}
	
	$row['edit_descr'] = $parse->decodeBBCodes( $row['descr'], false );
	
	$menu_link = <<<HTML
        <div class="btn-group">
          <a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
          <ul class="dropdown-menu text-left dropdown-menu-right">
            <li><a uid="{$row['id']}" href="?mod=blockip" class="editlink"><i class="fa fa-pencil-square-o position-left"></i>{$lang['word_ledit']}</a></li>
			<li class="divider"></li>
            <li><a href="?mod=blockip&action=delete&id={$row['id']}&user_hash={$dle_login_hash}&start_from={$start_from}{$urlsearch}"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['ip_unblock']}</a></li>
          </ul>
        </div>
HTML;

	echo "<tr>
			<td id=\"content_{$row['id']}\" data-date=\"{$editendban}\">{$row['ip']}</td>
			<td>{$endban}</td>
			<td>" . stripslashes( $row['descr'] ) . "<textarea dir=\"auto\" id=\"descr_{$row['id']}\" style=\"display:none;\">{$row['edit_descr']}</textarea></td>
			<td>{$menu_link}</td>
			<td><input name=\"selected_ips[]\" value=\"{$row['id']}\" type=\"checkbox\" class=\"icheck\"></td>
		 </tr>";
}

echo <<<HTML
	  </tbody>
	</table>
  </div>
<div class="panel-footer">
	<div class="btn bg-teal btn-sm btn-raised position-left" onclick="$('#newblock').modal(); return false;"><i class="fa fa-plus-circle position-left"></i>{$lang['news_add']}</div>
	<div class="pull-right">
	<select class="uniform position-left" name="action">
	<option value="">{$lang['edit_selact']}</option>
	<option value="mass_delete">{$lang['ip_unblock']}</option>
	</select><input class="btn bg-brown-600 btn-sm btn-raised" type="submit" value="{$lang['b_start']}">
	</div>
</div>
</div>
</form>
{$npp_nav}
HTML;

} else {
	
echo <<<HTML
<div class="panel-body">
<table width="100%">
    <tr>
        <td style="height:50px;"><div align="center">{$lang['ip_empty']}</div></td>
    </tr>
</table>
</div>
<div class="panel-footer">
	<div class="btn bg-teal btn-sm btn-raised position-left" onclick="$('#newblock').modal(); return false;"><i class="fa fa-plus-circle position-left"></i>{$lang['news_add']}</div>
</div>
HTML;
	
}

echo <<<HTML
<script>  
<!--
	$(function() {
		$('.table').find('tr > td:last-child').find('input[type=checkbox]').on('change', function() {
			if($(this).is(':checked')) {
				$(this).parents('tr').addClass('warning');
			}
			else {
				$(this).parents('tr').removeClass('warning');
			}
		});
		
		$('.editlink').click(function(){

			var ip = $('#content_'+$(this).attr('uid')).text();
			ip = ip.replace(/'/g, "&#039;");
			var ipid = $(this).attr('uid');
			var description = $('#descr_'+$(this).attr('uid')).val();
			var date = $('#content_'+$(this).attr('uid')).data('date');
			
			var b = {};
		
			b[dle_act_lang[3]] = function() { 
							$(this).dialog("close");						
					    };
		
			b[dle_act_lang[2]] = function() { 
						if ( $("#dle-promt-ip").val().length < 1) {
							 $("#dle-promt-ip").addClass('ui-state-error');
						} else {
							$("#editip").submit();
						}				
					};
	
			$("#dlepopup").remove();

			$("body").append("<div id='dlepopup' title='{$lang['ip_add']}' style='display:none'><form id='editip' method='post'><input type='hidden' name='id' value='"+ipid+"'><input type='hidden' name='mod' value='blockip'><input type='hidden' name='action' value='edit'><input type='hidden' name='user_hash' value='{$dle_login_hash}'>{$lang['title_filter']}<br><input type='text' dir='auto' name='ip_add' id='dle-promt-ip' class='classic' style='width:100%;' value='"+ip+"'/><br><br>{$lang['ban_date']}<br /><input type='text' dir='auto' name='date' class='form-control' data-rel='calendar' style='width:190px;' value='"+date+"' autocomplete='off'><br><br>{$lang['ban_descr']}<br><textarea dir='auto' name='descr' class='classic' style='width:100%;' rows='5'>"+description+"</textarea></form></div>");

			var ww = 600 * getBaseSize();

			if(ww > ( $(window).width() * 0.95 ) )  { ww = $(window).width() * 0.95;  }

			$('#dlepopup').dialog({
				autoOpen: true,
				width: ww,
				resizable: false,
				buttons: b,
				open: function( event, ui ) {
					$('#dlepopup [data-rel=calendar]').datetimepicker({
					  format:'Y-m-d H:i:s',
					  step: 30,
					  closeOnDateSelect:true,
					  dayOfWeekStart: 1,
					  scrollMonth:false,
					  scrollInput:false
					});
				}
			});

			return false;
		});
		
	});
	
	function ckeck_uncheck_all() {
	    var frm = document.optionsbar;
	    for (var i=0;i<frm.elements.length;i++) {
	        var elmnt = frm.elements[i];
	        if (elmnt.type=='checkbox') {
	            if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('tr').removeClass('warning'); }
	            else{ elmnt.checked=true; $(elmnt).parents('tr').addClass('warning');}
	        }
	    }
	    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
	    else{ frm.master_box.checked = true; }
		
		$(frm.master_box).parents('tr').removeClass('warning');
		
		$.uniform.update();
	
	}
	
    function search_submit(prm){
      document.navi.start_from.value=prm;
      document.navi.submit();
      return false;
    }
	
//-->
</script>
HTML;

echofooter();
?>