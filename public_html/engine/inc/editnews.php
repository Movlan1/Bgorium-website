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
 File: editnews.php
-----------------------------------------------------
 Use: News edit
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editnews'] ) {
	msg( "error", $lang['addnews_denied'], $lang['edit_denied'] );
}

if( isset( $_REQUEST['author'] ) ) $author = $db->safesql( trim( htmlspecialchars( $_REQUEST['author'], ENT_QUOTES, 'UTF-8' ) ) ); else $author = "";
if( isset( $_REQUEST['ifdelete'] ) ) $ifdelete = $_REQUEST['ifdelete']; else $ifdelete = "";
if( isset( $_REQUEST['news_fixed'] ) ) $news_fixed = $_REQUEST['news_fixed']; else $news_fixed = "";
if ( !$action ) $action = "list";

$parse = new ParseFilter();

if( $action == "list" ) {

	$_SESSION['admin_referrer'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );

	echoheader( "<i class=\"fa fa-pencil-square-o position-left\"></i><span class=\"text-semibold\">{$lang['header_ed_title']}</span>", $lang['edit_head'] );

	$_REQUEST['search_field'] = isset($_REQUEST['search_field']) ? $_REQUEST['search_field'] : '';
	$_REQUEST['search_author'] = isset($_REQUEST['search_author']) ? $_REQUEST['search_author'] : '';
	$_REQUEST['fromnewsdate'] = isset($_REQUEST['fromnewsdate']) ? $_REQUEST['fromnewsdate'] : '';
	$_REQUEST['tonewsdate'] = isset($_REQUEST['tonewsdate']) ? $_REQUEST['tonewsdate'] : '';
	$_REQUEST['search_cat'] = isset($_REQUEST['search_cat']) ? $_REQUEST['search_cat'] : '';
	$comb_cat = false;
	
	$search_field = $db->safesql( addslashes(addslashes(trim( urldecode( $_REQUEST['search_field'] ) ) ) ) );
	$search_author = $db->safesql( trim( htmlspecialchars( stripslashes( urldecode( $_REQUEST['search_author'] ) ), ENT_QUOTES, 'UTF-8' ) ) );
	$fromnewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['fromnewsdate'] ), ENT_QUOTES, 'UTF-8' ) ) );
	$tonewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['tonewsdate'] ), ENT_QUOTES, 'UTF-8' ) ) );

	
	if( !is_array($_REQUEST['search_cat']) ) $_REQUEST['search_cat'] = array ();
		
	if( !count( $_REQUEST['search_cat'] ) ) {
		
		$search_cat = array ();
		$search_cat[] = '0';
			
	} else $search_cat = $_REQUEST['search_cat'];

	$category_list = array();
	
	foreach ( $search_cat as $value ) {
		$value = intval($value);
		if( $value ) $category_list[] = $value;
	}
	
	$search_cat = $category_list;

	$have_poll = isset($_REQUEST['have_poll']) ? intval($_REQUEST['have_poll']) : 0;
	$have_pass = isset($_REQUEST['have_pass']) ? intval($_REQUEST['have_pass']) : 0;
	$disable_index = isset($_REQUEST['disable_index']) ? intval($_REQUEST['disable_index']) : 0;
	$disable_search = isset($_REQUEST['disable_search']) ? intval($_REQUEST['disable_search']) : 0;
	
	if( $have_poll ) $ifch1 = "checked"; else $ifch1 = "";
	if( $have_pass ) $ifch2 = "checked"; else $ifch2 = "";
	if( $disable_index ) $ifch3 = "checked"; else $ifch3 = "";
	if( $disable_search ) $ifch4 = "checked"; else $ifch4 = "";
	
	$start_from = isset($_REQUEST['start_from']) ? intval( $_REQUEST['start_from'] ) : 0;
	$news_per_page = isset($_REQUEST['news_per_page']) ? intval( $_REQUEST['news_per_page'] ) : 0;
	$gopage = isset($_REQUEST['gopage']) ? intval( $_REQUEST['gopage'] ) : 0;

	$_REQUEST['news_status'] = isset($_REQUEST['news_status']) ? intval( $_REQUEST['news_status'] ) : 0;
	$news_status_sel = array ('0' => '', '1' => '', '2' => '' );
	$news_status_sel[$_REQUEST['news_status']] = 'selected="selected"';

	if( ! $news_per_page or $news_per_page < 1 ) {
		$news_per_page = 50;
	}
	if( $gopage ) $start_from = ($gopage - 1) * $news_per_page;

	if( $start_from < 0 ) $start_from = 0;

	$where = array ();

	if( ! $user_group[$member_id['user_group']]['allow_all_edit'] and $member_id['user_group'] != 1 ) {

		$where[] = "autor = '{$member_id['name']}'";

	}

	if( $search_field ) {
		$search_field = preg_replace('/\s+/u', '%', $search_field);
		
		$_REQUEST['news_status'] = isset($_REQUEST['news_status']) ? $_REQUEST['news_status'] : '';
		
		if(!$_REQUEST['search_area']) {
			$where[] = "(title like '%{$search_field}%' OR short_story like '%{$search_field}%' OR full_story like '%{$search_field}%' OR xfields like '%{$search_field}%')";
		} elseif($_REQUEST['search_area'] == 1) {
			$where[] = "title like '%{$search_field}%'";
		} elseif($_REQUEST['search_area'] == 2) {
			$where[] = "short_story like '%{$search_field}%'";
		} elseif($_REQUEST['search_area'] == 3) {
			$where[] = "full_story like '%{$search_field}%'";
		} elseif($_REQUEST['search_area'] == 4) {
			$where[] = "xfields like '%{$search_field}%'";
		} elseif($_REQUEST['search_area'] == 5) {
			$where[] = "tags like '%{$search_field}%'";
		}

	}
	
	$search_field = trim( htmlspecialchars( urldecode( $_REQUEST['search_field'] ), ENT_QUOTES, 'UTF-8' ) );
	
	if( $search_author ) {

		$where[] = "autor like '$search_author%'";

	}
	
	if( count($search_cat) ) {
		
		$comb_cat = false;
		
		if ($search_cat[0] == -1) {
			unset($search_cat[0]);
			$comb_cat = true;
		}
	
		if( count($search_cat) ) {
			
			$w_cat = "category REGEXP '([[:punct:]]|^)(" . implode('|', $search_cat) . ")([[:punct:]]|$)'";
			
			if( $comb_cat ) {
				
				$where[] = "(category = '' OR category = '0' OR $w_cat)";
				
			} else $where[] = $w_cat;
		
		} elseif ($comb_cat) {
			$where[] = "(category = '' OR category = '0')";
		}

	}

	if( $fromnewsdate ) {

		$where[] = "date >= '$fromnewsdate'";

	}

	if( $tonewsdate ) {

		$where[] = "date <= '$tonewsdate'";

	}
	
	if($have_poll) {
		$where[] = "votes = '1'";
	}
	
	if($have_pass) {
		$where[] = "need_pass = '1'";
	}
	
	if($disable_index) {
		$where[] = "disable_index = '1'";
	}
	
	if($disable_search) {
		$where[] = "disable_search = '1'";
	}
	
	if( isset($_REQUEST['news_status']) AND $_REQUEST['news_status'] == 1 ) $where[] = "approve = '1'";
	elseif( isset($_REQUEST['news_status']) AND $_REQUEST['news_status'] == 2 ) $where[] = "approve = '0'";

	if( count( $where ) ) {

		$where = implode( " AND ", $where );
		$where = " WHERE " . $where;

	} else {
		$where = "";
	}

	$order_by = array ();
	
	$_REQUEST['search_order_f'] = isset($_REQUEST['search_order_f']) ? $_REQUEST['search_order_f'] : '';
	$_REQUEST['search_order_m'] = isset($_REQUEST['search_order_m']) ? $_REQUEST['search_order_m'] : '';
	$_REQUEST['search_order_d'] = isset($_REQUEST['search_order_d']) ? $_REQUEST['search_order_d'] : '';
	$_REQUEST['search_order_t'] = isset($_REQUEST['search_order_t']) ? $_REQUEST['search_order_t'] : '';
	$_REQUEST['search_order_c'] = isset($_REQUEST['search_order_c']) ? $_REQUEST['search_order_c'] : '';
	$_REQUEST['search_order_v'] = isset($_REQUEST['search_order_v']) ? $_REQUEST['search_order_v'] : '';

	if( $_REQUEST['search_order_f'] == "asc" or $_REQUEST['search_order_f'] == "desc" ) $search_order_f = $_REQUEST['search_order_f'];
	else $search_order_f = "";
	if( $_REQUEST['search_order_m'] == "asc" or $_REQUEST['search_order_m'] == "desc" ) $search_order_m = $_REQUEST['search_order_m'];
	else $search_order_m = "";
	if( $_REQUEST['search_order_d'] == "asc" or $_REQUEST['search_order_d'] == "desc" ) $search_order_d = $_REQUEST['search_order_d'];
	else $search_order_d = "";
	if( $_REQUEST['search_order_t'] == "asc" or $_REQUEST['search_order_t'] == "desc" ) $search_order_t = $_REQUEST['search_order_t'];
	else $search_order_t = "";
	if( $_REQUEST['search_order_c'] == "asc" or $_REQUEST['search_order_c'] == "desc" ) $search_order_c = $_REQUEST['search_order_c'];
	else $search_order_c = "";
	if( $_REQUEST['search_order_v'] == "asc" or $_REQUEST['search_order_v'] == "desc" ) $search_order_v = $_REQUEST['search_order_v'];
	else $search_order_v = "";


	if( ! empty( $search_order_f ) ) {
		$order_by[] = "fixed $search_order_f";
	}
	if( ! empty( $search_order_m ) ) {
		$order_by[] = "approve $search_order_m";
	}
	if( ! empty( $search_order_d ) ) {
		$order_by[] = "date $search_order_d";
	}
	if( ! empty( $search_order_t ) ) {
		$order_by[] = "title $search_order_t";
	}
	if( ! empty( $search_order_c ) ) {
		$order_by[] = "comm_num $search_order_c";
	}
	if( ! empty( $search_order_v ) ) {
		$order_by[] = "news_read $search_order_v";
	}
	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) $order_by = "fixed desc, approve asc, date desc";

	$search_order_fixed = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_f'] ) ) {
		$search_order_fixed[$search_order_f] = 'selected';
	} else {
		$search_order_fixed['desc'] = 'selected';
	}
	$search_order_mod = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_m'] ) ) {
		$search_order_mod[$search_order_m] = 'selected';
	} else {
		$search_order_mod['asc'] = 'selected';
	}
	$search_order_date = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_d'] ) ) {
		$search_order_date[$search_order_d] = 'selected';
	} else {
		$search_order_date['desc'] = 'selected';
	}
	$search_order_title = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_t ) ) {
		$search_order_title[$search_order_t] = 'selected';
	} else {
		$search_order_title['----'] = 'selected';
	}
	$search_order_comments = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_c ) ) {
		$search_order_comments[$search_order_c] = 'selected';
	} else {
		$search_order_comments['----'] = 'selected';
	}
	$search_order_view = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_v ) ) {
		$search_order_view[$search_order_v] = 'selected';
	} else {
		$search_order_view['----'] = 'selected';
	}

	$search_area = array('', '', '', '', '', '');
	
	if( isset( $_REQUEST['search_area'] ) ) {
		$_REQUEST['search_area'] = intval($_REQUEST['search_area']);
		$search_area[$_REQUEST['search_area']] = 'selected';
	} else {
		$search_area[0] = 'selected';
	}

	$db->query( "SELECT p.id, p.date, p.title, p.category, p.autor, p.alt_name, p.comm_num, p.approve, p.fixed, e.news_read, e.votes, e.user_id, e.need_pass FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . $where . " ORDER BY " . $order_by . " LIMIT {$start_from},{$news_per_page}" );
	// Prelist Entries

	$i = $start_from;
	
	if( $start_from == 0 ) {
		$start_from = "";
	}
	
	$entries_showed = 0;

	$entries = "";

	while ( $row = $db->get_array() ) {

		$i ++;
		
		if( $langformatdate ) {
			$itemdate = date( $langformatdate, strtotime( $row['date'] ) );
		} else {
			$itemdate = date( "d.m.Y", strtotime( $row['date'] ) );
		}

		$title = $row['title'];

		$title = htmlspecialchars( stripslashes( $title ), ENT_QUOTES, 'UTF-8' );
		$title = str_replace("&amp;","&", $title );

		$entries .= "<tr><td class=\"hidden-xs hidden-sm text-nowrap cursor-pointer\" onclick=\"document.location = '?mod=editnews&action=editnews&id={$row['id']}'; return false;\">{$itemdate}</td><td class=\"cursor-pointer\" onclick=\"document.location = '?mod=editnews&action=editnews&id={$row['id']}'; return false;\">";

		if( $config['allow_alt_url'] ) {

			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {

				if( $row['category'] and $config['seo_type'] == 2 ) {

					$cats_url = get_url( $row['category'] );
					
					if($cats_url) {
						
						$full_link = $config['http_home_url'] . $cats_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					} else $full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}

			} else {

				$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $row['date'] ) ) . $row['alt_name'] . ".html";
			}

		} else {

			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];

		}

		if( $row['comm_num'] > 0 ) {
			
			$row['comm_num'] = number_format( $row['comm_num'], 0, ',', ' ');
			
			$comm_link = <<<HTML
<div class="btn-group">
<a href="{$full_link}" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="tip">{$row['comm_num']}</a>
  <ul class="dropdown-menu text-left">
   <li><a href="{$full_link}" target="_blank"><i class="fa fa fa-eye"></i> {$lang['comm_view']}</a></li>
   <li><a href="?mod=comments&action=edit&id={$row['id']}"><i class="fa fa-pencil"></i> {$lang['vote_edit']}</a></li>
   <li><a href="?mod=comments&user_hash={$dle_login_hash}&action=dorebuild&id={$row['id']}"><i class="fa fa-retweet"></i> {$lang['news_r_comments']}</a></li>
   <li class="divider"></li>
   <li><a onclick="javascript:cdelete('{$row['id']}'); return(false)" href="?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id={$row['id']}"><i class="fa fa-trash-o"></i> {$lang['comm_del']}</a></li>
  </ul>
</div>
HTML;

		} else {
			$comm_link = <<<HTML
<div class="btn-group">
<a href="{$full_link}" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="tip">{$row['comm_num']}</a>
  <ul class="dropdown-menu text-left">
   <li><a href="?mod=comments&user_hash={$dle_login_hash}&action=dorebuild&id={$row['id']}"><i class="fa fa-retweet"></i> {$lang['news_r_comments']}</a></li>
  </ul>
</div>
HTML;
		}
		
		$row['news_read'] = isset($row['news_read']) ? number_format( $row['news_read'], 0, ',', ' ') : 0;
		
		if( $row['fixed'] ) $entries .= "<span class=\"badge badge-danger position-left\">{$lang['edit_fix']}</span>";

		if( $row['votes'] ) $entries .= "<i class=\"fa fa-bar-chart position-left text-muted\"></i>";
		if( $row['need_pass'] ) $entries .= "<i class=\"fa fa-lock position-left text-muted\"></i>";

		$entries .= "<a title='{$lang['edit_act']}' href=\"?mod=editnews&action=editnews&id={$row['id']}\">{$title}</a></td>
        <td class=\"hidden-xs text-nowrap text-center\"><a data-original-title=\"{$lang['st_views']}\" class=\"tip\" href=\"{$full_link}\" target=\"_blank\">{$row['news_read']}</a></td>";

		$entries .= "<td class=\"hidden-xs text-nowrap text-center\" style=\"text-align: center\">{$comm_link}</td><td style=\"text-align: center\" class=\"cursor-pointer\" onclick=\"document.location = '?mod=editnews&action=editnews&id={$row['id']}'; return false;\">";

		if( $row['approve'] ) $erlaub = "<span class=\"text-success\"><b><i class=\"fa fa-check-circle\"></i></b></span>";
		else $erlaub = "<span class=\"text-danger\"><b><i class=\"fa fa-exclamation-circle\"></i></b></span>";
		$entries .= $erlaub;

		if( ! $row['category'] ) $my_cat = "---";
		else {

			$my_cat = array ();
			$cat_list = explode( ',', $row['category'] );

			foreach ( $cat_list as $element ) {
				if( isset($cat_info[$element]['name']) AND $cat_info[$element]['name'] AND $element ) $my_cat[] = $cat_info[$element]['name'];
			}
			
			if( count($my_cat) ) $my_cat = implode( ',<br />', $my_cat );
			else $my_cat = "---";
			
		}
		
		$entries .= "</td><td class=\"hidden-xs cursor-pointer text-center\" onclick=\"document.location = '?mod=editnews&action=editnews&id={$row['id']}'; return false;\">{$my_cat}</td>";
		
		if( $user_group[$member_id['user_group']]['admin_editusers'] ) {
			$entries .= "<td class=\"hidden-xs hidden-sm\"><a href=\"?mod=editusers&action=edituser&id=" . $row['user_id'] . "\" target=\"_blank\">" . $row['autor'] . "</a></td>";
		} else {
			$entries .= "<td class=\"hidden-xs hidden-sm\">" . $row['autor'] . "</td>";
		}
		
		$entries .= "<td style=\"text-align: center\"><input name=\"selected_news[]\" value=\"{$row['id']}\" type=\"checkbox\" class=\"icheck\"></td></tr>";

		$entries_showed ++;

	}

	// End prelisting
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . $where);

	$all_count_news = $result_count['count'];

	if( $where ) {
		$lang['found_news'] = str_replace('{count}', $all_count_news, $lang['found_news']);
	} else $lang['found_news'] ='';

	///////////////////////////////////////////
	// Options Bar
	$category_list = CategoryNewsSelection( $search_cat, 0, false );

	if( !count($search_cat) AND !$comb_cat) $c_all_s = "selected"; else $c_all_s = "";

	if( $comb_cat ) $c_none_s = "selected"; else $c_none_s = "";

	echo <<<HTML
<script language="javascript">
    function search_submit(prm){
      document.optionsbar.start_from.value=prm;
      document.optionsbar.submit();
      return false;
    }
    function gopage_submit(prm){
      document.optionsbar.start_from.value= (prm - 1) * {$news_per_page};
      document.optionsbar.submit();
      return false;
    }
	
	$(function(){
		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
</script>
<div class="modal fade" id="advancedsearch" name="advancedsearch" role="dialog" aria-labelledby="advancedsearchLabel">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<form action="?mod=editnews&amp;action=list" method="GET" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="editnews">
<input type="hidden" name="action" value="list">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
  <div class="modal-header ui-dialog-titlebar">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <span class="ui-dialog-title" id="newcatsLabel">{$lang['edit_stat']} {$entries_showed} {$lang['edit_stat_1']} {$all_count_news}</span>
  </div>
  <div class="modal-body">

	<div class="form-group">
		<div class="row">
			<div class="col-sm-12">
				<label>{$lang['edit_search_news']}</label>
				<div class="input-group">
					<input name="search_field" value="{$search_field}" type="text" dir="auto" class="form-control">
					<span class="input-group-btn">
						<select name="search_area" class="uniform form-control" data-dropdown-align-right="true"><option value="0" {$search_area[0]}>{$lang['filter_search_0']}</option><option value="1" {$search_area[1]}>{$lang['filter_search_3']}</option><option value="2" {$search_area[2]}>{$lang['filter_search_4']}</option><option value="3" {$search_area[3]}>{$lang['filter_search_5']}</option><option value="4" {$search_area[4]}>{$lang['filter_search_6']}</option><option value="5" {$search_area[5]}>{$lang['filter_search_10']}</option></select>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-sm-12">
				<label>{$lang['edit_cat']}</label>
				<div class="dblock">
					<select data-placeholder="{$lang['addnews_cat_sel']}" name="search_cat[]" class="categoryselect" style="width:100%;max-width:350px;" multiple><option value="" {$c_all_s}>{$lang['edit_all']}</option><option value="-1" {$c_none_s}>{$lang['cat_in_none']}</option>{$category_list}</select>
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="form-group">
		<div class="row">
			<div class="col-sm-6">
				<label>{$lang['search_by_author']}</label>
				<input name="search_author" value="{$search_author}" type="text" dir="auto" class="form-control">
			</div>
			<div class="col-sm-6">
				<label>{$lang['search_by_date']}</label>
				<div style="width:100%">{$lang['edit_fdate']} <input data-rel="calendar" class="form-control" style="width:160px;" type="text" dir="auto" name="fromnewsdate" id="fromnewsdate" value="{$fromnewsdate}" autocomplete="off">
				{$lang['edit_tdate']} <input data-rel="calendar" class="form-control" style="width:160px;" type="text" dir="auto" name="tonewsdate" id="tonewsdate" value="{$tonewsdate}" autocomplete="off"></div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-sm-6">
				<label>{$lang['search_by_status']}</label>
				<select class="uniform" data-width="100%" name="news_status" id="news_status">
					<option {$news_status_sel['0']} value="0">{$lang['news_status_all']}</option>
					<option {$news_status_sel['1']} value="1">{$lang['news_status_approve']}</option>
					<option {$news_status_sel['2']} value="2">{$lang['news_status_mod']}</option>
				</select>
			</div>
	
			<div class="col-sm-6">
				<label>{$lang['edit_page']}</label>
				<input class="form-control text-center" name="news_per_page" value="{$news_per_page}" type="text" dir="auto">
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<div class="row">
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="have_poll" value="1" {$ifch1}>{$lang['have_poll']}</label></div>
			</div>
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="have_pass" value="1" {$ifch2}>{$lang['have_pass']}</label></div>
			</div>
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="disable_index" value="1" {$ifch3}>{$lang['disable_index']}</label></div>
			</div>
			<div class="col-sm-6">
				<div class="checkbox"><label><input class="icheck" type="checkbox" name="disable_search" value="1" {$ifch4}>{$lang['disable_search']}</label></div>
			</div>
		</div>
	</div>	
	
	<div class="pb-10">{$lang['news_order']}</div>
	<div class="form-group">
		<div class="row">
			<div class="col-sm-4">
				<label>{$lang['news_order_fixed']}</label>
				<select class="uniform" data-width="100%" name="search_order_f" id="search_order_f">
				   <option {$search_order_fixed['----']} value="">{$lang['user_order_no']}</option>
				   <option {$search_order_fixed['asc']} value="asc">{$lang['user_order_plus']}</option>
				   <option {$search_order_fixed['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
			<div class="col-sm-4">
				<label>{$lang['edit_approve']}</label>
				<select class="uniform" data-width="100%" name="search_order_m" id="search_order_m">
					<option {$search_order_mod['----']} value="">{$lang['user_order_no']}</option>
					<option {$search_order_mod['asc']} value="asc">{$lang['user_order_plus']}</option>
					<option {$search_order_mod['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
			
			<div class="col-sm-4">
				<label>{$lang['search_by_date']}</label>
				<select class="uniform" data-width="100%" name="search_order_d" id="search_order_d">
					<option {$search_order_date['----']} value="">{$lang['user_order_no']}</option>
					<option {$search_order_date['asc']} value="asc">{$lang['user_order_plus']}</option>
					<option {$search_order_date['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-sm-4">
				<label>{$lang['edit_et']}</label>
				<select class="uniform" data-width="100%" name="search_order_t" id="search_order_t">
					<option {$search_order_title['----']} value="">{$lang['user_order_no']}</option>
					<option {$search_order_title['asc']} value="asc">{$lang['user_order_plus']}</option>
					<option {$search_order_title['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
			<div class="col-sm-4">
				<label>{$lang['search_by_comment']}</label>
				<select class="uniform" data-width="100%" name="search_order_c" id="search_order_c">
					<option {$search_order_comments['----']} value="">{$lang['user_order_no']}</option>
					<option {$search_order_comments['asc']} value="asc">{$lang['user_order_plus']}</option>
					<option {$search_order_comments['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
			
			<div class="col-sm-4">
				<label>{$lang['search_by_view']}</label>
				<select class="uniform" data-width="100%" name="search_order_v" id="search_order_v">
					<option {$search_order_view['----']} value="">{$lang['user_order_no']}</option>
					<option {$search_order_view['asc']} value="asc">{$lang['user_order_plus']}</option>
					<option {$search_order_view['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
			</div>
		</div>
	</div>

   </div>

   <div class="modal-footer">
		<button onclick="search_submit(0); return(false);" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-search position-left"></i>{$lang['edit_act_1']}</button>
		<button onclick="document.location='?mod=editnews&action=list'; return(false);" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-eraser position-left"></i>{$lang['drop_search']}</button>
   </div>

</form>
 </div>
</div>
</div>
HTML;
// End Options Bar


	echo <<<JSCRIPT
<script>
<!--
function ckeck_uncheck_all() {

    var frm = document.editnews;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; $(elmnt).parents('tr').removeClass('warning'); }
            else{ elmnt.checked=true; $(elmnt).parents('tr').addClass('warning'); }
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
-->
</script>
JSCRIPT;

	if( $entries_showed == 0 ) {

		echo <<<HTML
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['news_list']}
	<div class="heading-elements not-collapsible">
		<ul class="icons-list">
			<li><a data-toggle="modal" data-target="#advancedsearch" href="#"><i class="fa fa-search position-left"></i><span class="visible-lg-inline visible-md-inline visible-sm-inline">{$lang['news_advanced_search']}</span></a></li>
		</ul>
	</div>
  </div>
  <div class="panel-body">
	<div style="display: table;min-height:100px;width:100%;">
	  <div class="text-center" style="display: table-cell;vertical-align:middle;">{$lang['edit_nonews']}</div>
	</div>
   </div>
</div>
HTML;

	} else {

		echo <<<HTML
<script>
<!--
function cdelete(id){

	    DLEconfirmDelete( '{$lang['db_confirmclear']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id=' + id + '';
		} );
}
//-->
</script>
<form action="" method="post" name="editnews">
<input type=hidden name="mod" value="massactions">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['news_list']} {$lang['found_news']}
	<div class="heading-elements not-collapsible">
		<ul class="icons-list">
			<li><a data-toggle="modal" data-target="#advancedsearch" href="#"><i class="fa fa-search position-left"></i><span class="visible-lg-inline visible-md-inline visible-sm-inline">{$lang['news_advanced_search']}</span></a></li>
		</ul>
	</div>
  </div>

    <table class="table table-striped table-xs table-hover">
      <thead>
      <tr>
        <th class="hidden-xs hidden-sm" style="width: 60px;">&nbsp;</th>
        <th>{$lang['edit_title']}</th>
        <th class="hidden-xs text-center" style="width: 60px;"><i class="fa fa-eye tip" data-original-title="{$lang['st_views']}"></i></th>
        <th class="hidden-xs text-center" style="width: 60px;"><i class="fa fa-comment-o tip" data-original-title="{$lang['edit_com']}"></i></th>
        <th style="width: 30px;text-align:center;">&nbsp;</th>
        <th class="hidden-xs text-center">{$lang['edit_cl']}</th>
        <th class="hidden-xs hidden-sm" style="max-width: 140px">{$lang['edit_autor']}</th>
        <th style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all();" class="icheck"></th>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>

	<div class="panel-footer">
			  <div class="pull-right">
				<select name="action" class="uniform position-left">
					<option value="">{$lang['edit_selact']}</option>
					<option value="mass_add_cat">{$lang['add_selcat']}</option>
					<option value="mass_move_to_cat">{$lang['edit_selcat']}</option>
					<option value="mass_edit_symbol">{$lang['edit_selsymbol']}</option>
					<option value="mass_edit_author">{$lang['edit_selauthor']}</option>
					<option value="mass_add_cloud">{$lang['mass_add_cloud']}</option>
					<option value="mass_edit_cloud">{$lang['edit_cloud']}</option>
					<option value="mass_date">{$lang['mass_edit_date']}</option>
					<option value="mass_approve">{$lang['mass_edit_app']}</option>
					<option value="mass_not_approve">{$lang['mass_edit_notapp']}</option>
					<option value="mass_fixed">{$lang['mass_edit_fix']}</option>
					<option value="mass_not_fixed">{$lang['mass_edit_notfix']}</option>
					<option value="mass_comments">{$lang['mass_edit_comm']}</option>
					<option value="mass_not_comments">{$lang['mass_edit_notcomm']}</option>
					<option value="mass_rating">{$lang['mass_edit_rate']}</option>
					<option value="mass_not_rating">{$lang['mass_edit_notrate']}</option>
					<option value="mass_main">{$lang['mass_edit_main']}</option>
					<option value="mass_not_main">{$lang['mass_edit_notmain']}</option>
					<option value="mass_yandex_dzen">{$lang['mass_dzen']}</option>
					<option value="mass_not_yandex_dzen">{$lang['mass_notdzen']}</option>
					<option value="mass_clear_count">{$lang['mass_clear_count']}</option>
					<option value="mass_clear_rating">{$lang['mass_clear_rating']}</option>
					<option value="mass_clear_cloud">{$lang['mass_clear_cloud']}</option>
					<option value="mass_rebuild_comments">{$lang['news_r_comments']}</option>
					<option value="mass_delete_comments">{$lang['massusers_comments']}</option>
					<option value="mass_delete">{$lang['edit_seldel']}</option>
				</select><input class="btn bg-teal btn-sm btn-raised" type="submit" value="{$lang['b_start']}">
			  </div>
	</div>
</div>
HTML;

		// pagination
		$npp_nav = "";

		if( $all_count_news > $news_per_page ) {

			if( $start_from > 0 ) {
				$previous = $start_from - $news_per_page;
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\"><i class=\"fa fa-backward\"></i></a></li>";
			}
			
			if( !$start_from ) $start_from = 0;

			$enpages_count = @ceil( $all_count_news / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";

			if( $enpages_count <= 10 ) {

				for($j = 1; $j <= $enpages_count; $j ++) {

					if( $enpages_start_from != $start_from ) {

						$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";

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

					$enpages .= "<li><a onclick=\"javascript:search_submit(0); return(false);\" href=\"#\">1</a></li> <li><span>...</span></li>";

				}

				for($j = $start; $j <= $end; $j ++) {

					if( $enpages_start_from != $start_from ) {

						$enpages .= "<li><a onclick=\"javascript:search_submit({$enpages_start_from}); return(false);\" href=\"#\">{$j}</a></li>";

					} else {

						$enpages .= "<li class=\"active\"><span>{$j}</span></li>";
					}

					$enpages_start_from += $news_per_page;
				}

				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "<li><span>...</span></li>";
				
				if( $enpages_start_from != $start_from ) {

					$enpages .= "<li><a onclick=\"javascript:search_submit({$enpages_start_from}); return(false);\" href=\"#\">{$enpages_count}</a></li>";

				} else {

					$enpages .= "<li class=\"active\"><span>{$enpages_count}</span></li>";
				}
					
				$npp_nav .= $enpages;

			}

			if( $all_count_news > $i ) {
				$how_next = $all_count_news - $i;
				if( $how_next > $news_per_page ) {
					$how_next = $news_per_page;
				}
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\"><i class=\"fa fa-forward\"></i></a></li>";
			}

			echo "<ul class=\"pagination pagination-sm mb-20\">".$npp_nav."</ul>";


		}
// pagination

			echo <<<HTML
</form>
HTML;

	}

	echofooter();

} elseif( $action == "editnews" ) {
	
	if( !isset( $_SESSION['admin_referrer'] ) ) {

		$_SESSION['admin_referrer'] = "?mod=editnews&amp;action=list";

	}
	
	$id = intval( $_GET['id'] );
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );

	$found = FALSE;

	if( isset($row['id']) AND $id == $row['id'] ) $found = TRUE;
	
	if( !$found ) {
		msg( "error", $lang['cat_error'], $lang['edit_nonews'] );
	}

	$cat_list = explode( ',', $row['category'] );
	
	if( count($cat_list) > 1 ) {
		
		$sort_catlist = "['".implode("','", $cat_list)."']";
		
	} else { $sort_catlist = "[]"; }
	
	$have_perm = 0;

	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$have_perm = 1;
		
		if($member_id['cat_add']) $allow_list = explode( ',', $member_id['cat_add'] );
		else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

		foreach ( $cat_list as $selected ) {
			if( $allow_list[0] != "all" and !in_array( $selected, $allow_list )) $have_perm = 0;
		}
	}
	
	if( $user_group[$member_id['user_group']]['allow_edit'] AND $row['autor'] == $member_id['name'] ) {
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

	if( !$have_perm ) {
		msg( "error", $lang['addnews_denied'], $lang['edit_denied'], "?mod=editnews&action=list" );
	}

	$edit_alert = '';
	$save_edit_alert = '';
	
	if( $config['alert_edit_now'] ) {
		
		if( isset($row['edited_now']) AND $row['edited_now'] ) $row['edited_now'] = json_decode($row['edited_now'], true); else $row['edited_now'] = array('name' => '', 'time' => '');
		
		if( $row['edited_now']['name'] AND $row['edited_now']['name'] != $member_id['name']  AND time() < $row['edited_now']['time'] + 60 ) {
			
			$lang['edit_news_alert'] = str_replace('{name}', $row['edited_now']['name'],$lang['edit_news_alert'] );
			
			$edit_alert = <<<HTML
DLEPush.warning('{$lang['edit_news_alert']}', '', 20000);
HTML;

		} else {
		
			$db->query( "UPDATE " . PREFIX . "_post_extras SET edited_now='".$db->safesql(json_encode(array('name' => $member_id['name'], 'time' => time() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ))."' WHERE news_id='{$row['id']}'" );

			$save_edit_alert = <<<HTML
setTimeout(function() {
	save_edit_alert();
}, 20000);
HTML;
		}
		
	}

	$row['title'] = $parse->decodeBBCodes( $row['title'], false );
	$row['title'] = str_replace("&amp;","&", $row['title'] );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );
	$row['descr'] = str_replace("&amp;","&", $row['descr'] );
	$row['keywords'] = str_replace("&amp;", "&", $row['keywords']);

	$row['metatitle'] = stripslashes( $row['metatitle'] );

	$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, true );
	$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, true );

	$access = permload( $row['access'] );
	$allowed_country = isset($row['allowed_country']) ? htmlspecialchars($row['allowed_country'], ENT_QUOTES, 'UTF-8') : "";
	$not_allowed_country = isset($row['not_allowed_country']) ? htmlspecialchars($row['not_allowed_country'], ENT_QUOTES, 'UTF-8') : "";

	$poll = array();
	
	if( $row['votes'] ) {
		
		$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll WHERE news_id = '{$row['id']}'" );
		
		$poll['title'] = $parse->decodeBBCodes( $poll['title'], false );
		$poll['frage'] = $parse->decodeBBCodes( $poll['frage'], false );
		$poll['body'] = $parse->decodeBBCodes( $poll['body'], false );
		$poll['multiple'] = $poll['multiple'] ? "checked" : "";
		$poll['closed'] = $poll['closed'] ? "checked" : "";

		if ($user_group[$member_id['user_group']]['allow_all_edit'] AND $poll['votes']) {
			$clear_poll = "<br><input type=\"button\" onclick=\"clearPoll('{$id}'); return false;\" class=\"btn bg-danger btn-sm btn-raised\" value=\"{$lang['clear_poll']}\">";
		} else $clear_poll = "";

		if( $poll['closed'] ) {
			
			if( $langformatdatefull ) {
				$poll['date_closed'] = date( $langformatdatefull, $poll['date_closed'] );
			} else {
				$poll['date_closed'] = date( "d.m.Y H:i:s", $poll['date_closed'] );
			}
			
			$lang['v_closed_date'] = str_replace('{date}', $poll['date_closed'], $lang['v_closed_date'] );
			$lang['v_closed_date'] = '<span class="text-muted text-size-small"><i class="fa fa-exclamation-triangle position-left"></i>'.$lang['v_closed_date'].'</span>';

		} else $lang['v_closed_date'] = "";

	} else {
		$clear_poll = "";
		$poll['title'] = '';
		$poll['frage'] = '';
		$poll['body'] = '';
		$poll['multiple'] = '';
		$poll['closed'] = "";
		$lang['v_closed_date'] = "";
	}
	
	$password = "";
	
	if( $row['need_pass'] ) {
		$password = $db->super_query( "SELECT password FROM " . PREFIX . "_post_pass WHERE news_id = '{$row['id']}'" );
		$password  = htmlspecialchars( $password['password'], ENT_QUOTES, 'UTF-8' );
	}

	
	if( $config['allow_subscribe'] AND $member_id['user_group'] == 1 ) {
		$count_subscribe = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_subscribe WHERE news_id = '{$row['id']}' " );
		
		if($count_subscribe['count']) {
			
			$lang['count_subscribe'] = str_replace("{count}", $count_subscribe['count'], $lang['count_subscribe']);
			
			$clear_subscribe = <<<HTML
<div class="form-group">
	<label class="control-label col-md-2"></label>
	<div class="col-md-10">
		{$lang['count_subscribe']}
		<br><br>
		<input type="button" onclick="clearsubscribe('{$id}'); return false;" class="btn bg-danger btn-sm btn-raised" value="{$lang['btn_clearsubscribe']}">
	</div>
</div>
HTML;

		} else $clear_subscribe = "";
		
	} else $clear_subscribe = "";

	$expires = $db->super_query( "SELECT * FROM " . PREFIX . "_post_log where news_id = '{$row['id']}'" );

	if ( isset($expires['expires']) AND $expires['expires'] ) $expires['expires'] = date("Y-m-d", $expires['expires']); else $expires['expires'] = '';
	
	$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";
	$js_array[] = "engine/classes/js/sortable.js";
	$js_array[] = "engine/classes/uploads/html5/plupload/plupload.full.min.js";
	$js_array[] = "engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js";

	if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

	$css_array[] = "engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css";

	echoheader( "<i class=\"fa fa-pencil-square-o position-left\"></i><span class=\"text-semibold\">{$lang['header_ed_title']}</span>", array($_SESSION['admin_referrer'] => $lang['edit_all_title'], '' => $lang['edit_etitle'] ) );

	$xfieldsaction = "categoryfilter";
	include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
	echo $categoryfilter;

	$author = urlencode($row['autor']);

	echo <<<HTML
<script>
<!-- 
function clearPoll(id) {

    DLEconfirmDelete( '{$lang['clear_poll_1']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');

		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: 'clearpoll', user_hash: '{$dle_login_hash}' }, function(data){

			HideLoading('');
			
			DLEPush.info(data);

		});

	} );

	return false;
	
}

function clearsubscribe(id) {

    DLEconfirmDelete( '{$lang['confirm_action']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');

		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: 'clearsubscribenews', user_hash: '{$dle_login_hash}' }, function(data){

			HideLoading('');

			DLEPush.info(data);

		});

	} );

	return false;
	
}

function MarkSpam(id, hash) {

    DLEconfirm( '{$lang['mark_spam']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');

		$.get("engine/ajax/controller.php?mod=adminfunction", { id: id, action: 'newsspam', user_hash: hash }, function(data){

			HideLoading('');

			if (data != "error") {

			    DLEconfirm( data, '{$lang['p_info']}', function () {
					document.location='{$_SESSION['admin_referrer']}';
				} );

			}

		});

	} );

	return false;
};

function save_edit_alert() {

	$.post( "engine/ajax/controller.php?mod=adminfunction", { 'id': '{$row['id']}', action: 'saveeditnews', user_hash: dle_login_hash }, function(data){

		if (data.success) {
		
			setTimeout(function() {
				save_edit_alert();
			}, 20000);
			
		}
		
	}, "json");
};
// -->
</script>
HTML;

	echo "
    <script>
    function preview(){ 

		tinyMCE.triggerSave();

		if(document.addnews.title.value == '') { 
			DLEPush.error('{$lang['addnews_alert']}'); return false; 
		} else{
			var width  = screen.width * .8;
			var height = screen.height * .8;
			
			if(width > 900) width = 950;
			if(height > 550) height = 550;

			var left   = (screen.width  - width)/2;
			var top    = (screen.height - height)/2;

			dd=window.open('','prv','width='+width+', height='+height+', top='+top+', left='+left+', directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no');
			document.addnews.mod.value='preview';document.addnews.target='prv';
			document.addnews.submit();dd.focus();
			setTimeout(\"document.addnews.mod.value='editnews';document.addnews.target='_self'\",500);
   		}
    }
    function sendNotice( id ){
		
		var ww = 500 * getBaseSize();

		if(ww > ( $(window).width() * 0.95 ) )  { ww = $(window).width() * 0.95;  }
		
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
				ShowLoading('');
				$.post('engine/ajax/controller.php?mod=message', { id: id,  text: response, user_hash: '{$dle_login_hash}', allowdelete: \"no\" },
					function(data){
						HideLoading('');
						if (data == 'ok') { DLEPush.info('{$lang['p_send_ok']}'); }
					});

			}
		};

		$('#dlepopup').remove();

		$('body').append(\"<div id='dlepopup' class='dle-promt' title='{$lang['p_title']}' style='display:none'>{$lang['p_text']}<br /><br /><textarea dir='auto' name='dle-promt-text' id='dle-promt-text' class='classic' style='width:100%;height:100px; padding: .4em;'></textarea></div>\");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: ww,
			resizable: false,
			buttons: b
		});

	}

    function confirmDelete(url, id){
		var ww = 500 * getBaseSize();

		if(ww > ( $(window).width() * 0.95 ) )  { ww = $(window).width() * 0.95;  }

		var b = {};

		b[dle_act_lang[1]] = function() {
						$(this).dialog(\"close\");
				    };

		b['{$lang['p_message']}'] = {
			text: '{$lang['p_message']}',
			class: 'bg-slate-600',
			click: function () {
				$(this).dialog(\"close\");

				var bt = {};

				bt[dle_act_lang[3]] = function() {
								$(this).dialog('close');
							};

				bt['{$lang['p_send']}'] = function() {
								if ( $('#dle-promt-text').val().length < 1) {
										$('#dle-promt-text').addClass('ui-state-error');
								} else {
									var response = $('#dle-promt-text').val()
									$(this).dialog('close');
									$('#dlepopup').remove();
									ShowLoading('');
									$.post('engine/ajax/controller.php?mod=message', { id: id,  text: response, user_hash: '{$dle_login_hash}' },
										function(data){
											HideLoading('');
											if (data == 'ok') { document.location=url; } else { DLEPush.error('{$lang['p_not_send']}'); }
									});

								}
							};

				$('#dlepopup').remove();

				$('body').append(\"<div id='dlepopup' title='{$lang['p_title']}' class='dle-promt' style='display:none'>{$lang['p_text']}<br><br><textarea dir='auto' name='dle-promt-text' id='dle-promt-text' class='classic' style='width:100%;height:100px;'></textarea></div>\");

				$('#dlepopup').dialog({
					autoOpen: true,
					width: ww,
					resizable: false,
					buttons: bt
				});

			}
		};

		b['{$lang['edit_dnews']}'] = {
			text: '{$lang['edit_dnews']}',
			class: 'ui-button-delete',
			click: function () {
				$(this).dialog(\"close\");
				document.location=url;
			}
		};

		$(\"#dlepopup\").remove();

		$(\"body\").append(\"<div id='dlepopup' title='{$lang['p_confirm']}' class='dle-promt' style='display:none'><div class='dle-confirm-message'>{$lang['edit_cdel']}</div></div>\");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: ww,
			resizable: false,
			buttons: b
		});


    }

	function auto_keywords ( key )
	{

		tinyMCE.triggerSave();

		var short_txt = '';

		if( document.getElementById('short_story') ) {
			short_txt = document.getElementById('short_story').value;
		}

		var full_txt = '';
		
		if( document.getElementById('full_story') ) {
			full_txt = document.getElementById('full_story').value;
		}

		ShowLoading('');

		$.post(\"engine/ajax/controller.php?mod=keywords\", { short_txt: short_txt, full_txt: full_txt, key: key, user_hash: '{$dle_login_hash}' }, function(data){

			HideLoading('');

			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').tokenfield('setTokens', data);}

		});

		return false;
	}

	function find_relates ()
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post('engine/ajax/controller.php?mod=find_relates', { title: title, id: '{$row['id']}', user_hash: '{$dle_login_hash}' }, function(data){

			HideLoading('');

			$('#related_news').html(data);

		});

		return false;

	};
	
	function find_related_ids ( id ){

		tinyMCE.triggerSave();
		
		var title = document.getElementById('title').value;
		var short_txt = '';

		if( document.getElementById('short_story') ) {
			short_txt = document.getElementById('short_story').value;
		}

		var full_txt = '';
		
		if( document.getElementById('full_story') ) {
			full_txt = document.getElementById('full_story').value;
		}

		var catselect = document.getElementById('category');
		var catselected = Array.from(catselect.selectedOptions).map(option => option.value);

		ShowLoading('');

		$.post(\"engine/ajax/controller.php?mod=adminfunction\", { action: 'relatedids', id: id, title: title, short_txt: short_txt, full_txt: full_txt, category: catselected, user_hash: '{$dle_login_hash}' }, function(data){
	
			HideLoading('');

			$('#related_ids').val(data);
	
		});

		return false;
	}
	
	function xfimagedelete( xfname, xfvalue )
	{
		
		DLEconfirmDelete( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post('engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'images[]' : xfvalue }, function(data){
	
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

	function xffiledelete( xfname, xfvalue )
	{
		
		DLEconfirmDelete( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post('engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'files[]' : xfvalue }, function(data){
	
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
		sel_alt = sel_alt.replace(/\"/g, '&quot;');
		
		DLEprompt('{$lang['bb_descr']}', sel_alt, '{$lang['p_prompt']}', function (r) {
	
			r = r.replace(/</g, '');
			r = r.replace(/>/g, '');
			r = r.replaceAll(',', '&#44;');
			r = r.replaceAll('|', '&#124;');
			
			$('#xf_'+id).data('alt', r);
			xfsinc(xfname);
		
		}, true, '{$lang['news_save']}');
		
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

	function StripHTML(html) {
		var tmp = document.createElement(\"DIV\");
		tmp.innerHTML = html;
		return tmp.textContent || tmp.innerText || \"\";
	};

	function checkxf () {
		var status = '';
		var xfempty = false;

		tinyMCE.triggerSave();

		$('[uid=\"essential\"]:visible').each(function(indx) {
			
			var tempval = StripHTML($(this).find('[rel=\"essential\"]').val());
			tempval = tempval.trim();

			if(tempval.length < 1) {
				
				if( $(this).find('[rel=\"essential\"]').data('alert') ) {
				
					var alert_text = '{$lang['addnews_xf_alert_1']}';
					
					alert_text = alert_text.replace(/{field}/g, $(this).find('[rel=\"essential\"]').data('alert') );
				
					DLEPush.error(alert_text);
					
				} else {
					xfempty = true;
				}
			
				status = 'fail';
			}

		});

		if(xfempty) {
			DLEPush.error('{$lang['addnews_xf_alert']}');
		}

		$('[data-blockminlen]:visible').each(function(indx) {
			var tempval = StripHTML($(this).find('[data-minlen]').val());
			tempval = tempval.trim();

			if( tempval.length && tempval.length < $(this).find('[data-minlen]').data('minlen')) {
				
				var alert_text = '{$lang['addnews_xf_alert_2']}';
				
				alert_text = alert_text.replace(/{field}/g, $(this).find('[data-minlen]').data('alert') );
				alert_text = alert_text.replace(/{count}/g, $(this).find('[data-minlen]').data('minlen') );
			
				DLEPush.error(alert_text);
			
				status = 'fail';
			}

		});

		$('[data-blockmaxlen]:visible').each(function(indx) {
			var tempval = StripHTML($(this).find('[data-maxlen]').val());
			tempval = tempval.trim();

			if( tempval.length && tempval.length > $(this).find('[data-maxlen]').data('maxlen')) {
				
				var alert_text = '{$lang['addnews_xf_alert_3']}';
				
				alert_text = alert_text.replace(/{field}/g, $(this).find('[data-maxlen]').data('alert') );
				alert_text = alert_text.replace(/{count}/g, $(this).find('[data-maxlen]').data('maxlen') );
			
				DLEPush.error(alert_text);
			
				status = 'fail';
			}

		});

		if(document.addnews.title.value.trim() == ''){

			DLEPush.error('{$lang['addnews_alert']}');

			status = 'fail';

		}

		return status;

	};
	
	function moveCategoryChange(obj) {
  
	  var value = $(obj).val();

	  if (value == 5) {
		$('#movecatlist').show();
	  } else {
		$('#movecatlist').hide();
	  }
	  
	}
	
	function onPassChange(obj) {
  
	  var value = obj.checked;
	  
	  if (value == true) {
		$('#passlist').show();
	  } else {
		$('#passlist').hide();
	  }
	  
	}
	
	$(function(){

{$edit_alert}
{$save_edit_alert}

		$( '#new_author' ).autocomplete({
			source: function( request, response ) {
				$.getJSON( 'engine/ajax/controller.php?mod=find_tags&user_hash={$dle_login_hash}&mode=authors', {
					term: request.term
				}, response );
			},
			search: function() {
				var term = this.value;
				if ( term.length < 2 ) {
					return false;
				}
			},
			focus: function() {
				return false;
			},
			select: function( event, ui ) {
				this.value = ui.item.value;
				return false;
			}
		});

		$('#tags').tokenfield({
		  autocomplete: {
		    source: 'engine/ajax/controller.php?mod=find_tags&user_hash={$dle_login_hash}',
			minLength: 3,
		    delay: 500
		  },
		  createTokensOnBlur:true
		});

		$('[data-rel=links]').tokenfield({
		  autocomplete: {
		    source: 'engine/ajax/controller.php?mod=find_tags&user_hash={$dle_login_hash}&mode=xfield',
			minLength: 3,
		    delay: 500
		  },
		  createTokensOnBlur:true
		});

		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
			
		if( $('#category').hasClass('categoryselect') && $('#category_chosen').length ) {

			$('#category').setSelectionOrder({$sort_catlist});
			
			var category_results = $('#category').getSelectionOrder();
			
			$('#category').on('change',function(){
				var selected_values = $(this).val();
				var temp_results = category_results;
				category_results = [];
			
				//FOR REMOVED VALUES
				for(i in temp_results){
					if($.inArray(temp_results[i],selected_values)>=0){
						category_results.push(temp_results[i]);
					}
				}
			
				//FOR ADDED VALUES
				for(i in selected_values){
					if($.inArray(selected_values[i],temp_results)<=-1){
						category_results.push(selected_values[i]);
					}
				}
				
				$('#category_custom_sort').val(category_results.join('::'));
	
			});
			
			$('#category_custom_sort').val(category_results.join('::'));
		
		}
		
		moveCategoryChange($('#expires_action'));
		
		if( document.getElementById('need_pass') ) {
			onPassChange(document.getElementById('need_pass'));
		}
		
	});
    </script>";

	$categories_list = CategoryNewsSelection( $cat_list, 0 );

	if( $config['allow_multi_category'] ) $category_multiple = "class=\"categoryselect\" multiple";
	else $category_multiple = "class=\"uniform\" data-live-search=\"true\" data-none-results-text=\"{$lang['addnews_cat_fault']}\" data-width=\"350\"";

	if( $member_id['user_group'] < 3 ) {

		$author_info = "<input type=\"text\" dir=\"auto\" name=\"new_author\" id=\"new_author\" class=\"form-control position-left\" style=\"width: 10rem;\" value=\"{$row['autor']}\"><input type=\"hidden\" name=\"old_author\" value=\"{$row['autor']}\" />";

	} else {

		$author_info = "<b>{$row['autor']}</b>";

	}

	if ( $user_group[$member_id['user_group']]['admin_editusers'] ) {

		$author_info .= "<a href=\"?mod=editusers&action=edituser&id=" . $row['user_id'] . "\" target=\"_blank\"><i class=\"fa fa-user-circle-o\"></i></a>";

	}

	if( $row['allow_comm'] ) $ifch = "checked";	else $ifch = "";
	if( $row['allow_main'] ) $ifmain = "checked"; else $ifmain = "";
	if( $row['approve'] ) $ifapp = "checked"; else $ifapp = "";
	if( $row['fixed'] ) $iffix = "checked";	else $iffix = "";
	if( $row['allow_rate'] ) $ifrat = "checked"; else $ifrat = "";
	if( $row['disable_index'] ) $ifdis = "checked"; else $ifdis = "";
	if( $row['disable_search'] ) $ifdiss = "checked"; else $ifdiss = "";
	if( $row['need_pass'] ) $ifnpass = "checked"; else $ifnpass = "";

	if( $row['allow_rss'] ) $ifrss = "checked"; else $ifrss = "";
	if( $row['allow_rss_dzen'] ) $ifrssd = "checked"; else $ifrssd = "";
	
	if( $row['related_ids'] == '0') $row['related_ids'] = '';
	
	if( $user_group[$member_id['user_group']]['allow_fixed'] and $config['allow_fixed'] ) $fix_input = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" id=\"news_fixed\" name=\"news_fixed\" value=\"1\" {$iffix}>{$lang['addnews_fix']}</label></div>"; else $fix_input = "";
	if( $user_group[$member_id['user_group']]['allow_main'] ) $main_input = "<div class=\"checkbox\" id=\"opt_holder_main\"><label><input class=\"icheck\" type=\"checkbox\" id=\"allow_main\" name=\"allow_main\" value=\"1\" {$ifmain}>{$lang['addnews_main']}</label></div>"; else $main_input = "";

	if($member_id['user_group'] < 3 ) {
		$disable_index = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" id=\"disable_index\" name=\"disable_index\" value=\"1\" {$ifdis}>{$lang['add_disable_index']}</label></div>";
		$disable_search = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" id=\"disable_search\" name=\"disable_search\" value=\"1\" {$ifdiss}>{$lang['cat_d_search']}</label></div>";
		$need_pass = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" id=\"need_pass\" name=\"need_pass\" onchange=\"onPassChange(this)\" value=\"1\" {$ifnpass}>{$lang['pass_list_1']}</label></div>";

		if( $config['allow_yandex_dzen'] ) {
			$yandex_dzen = "<div class=\"checkbox\"><label><input class=\"icheck\" type=\"checkbox\" name=\"allow_rss_dzen\" value=\"1\" {$ifrssd}>{$lang['allow_rss_dzen']}</label></div>";
		} else $yandex_dzen = "";
		
		if( $config['allow_rss'] ) {
			
			$rss_option = <<<HTML
				<div class="row mt-15" id="opt_cat_rss">
					<div class="col-sm-6" style="max-width:19.5rem;">
						<div class="checkbox"><label><input class="icheck" type="checkbox" name="allow_rss" value="1" {$ifrss}>{$lang['allow_rss_news']}</label></div>
					</div>
					<div class="col-sm-6">
						{$yandex_dzen}
					</div>
				</div>
			
HTML;
		}
	
	} else {
		$disable_index = "";
		$disable_search = "";
		$need_pass = "";
		$rss_option = "";
	}

	if( $row['editdate'] ) {
		
		if( $langformatdatefull ) {
			$row['editdate'] = date( $langformatdatefull, $row['editdate'] );
		} else {
			$row['editdate'] = date( "d.m.Y H:i:s", $row['editdate'] );
		}
		
		$lang['news_edit_date'] = $lang['news_edit_date'] . " " . $row['editor'] . " - " . $row['editdate'];
	
	} else $lang['news_edit_date'] = "";

	if( $row['view_edit'] == '1' ) $view_edit_cheked = "checked";
	else $view_edit_cheked = "";

	$exp_action = array('', '', '', '', '','', '');
	
	if( isset($expires['action']) AND $expires['action']) {
		$exp_action[$expires['action']] = "selected=\"selected\"";
	}
	
	if( isset($expires['action']) AND $expires['move_cat'] ) {
		$move_cat_list = CategoryNewsSelection( explode( ',', $expires['move_cat'] ), 0 );
	} else $move_cat_list = CategoryNewsSelection( 0, 0 );
	
	
	if ($row['autor'] != $member_id['name']) $notice_btn = "<button  onclick=\"sendNotice('{$id}');  return false;\" class=\"btn bg-slate-600 btn-sm btn-raised position-left\"><i class=\"fa fa-envelope-o position-left\"></i>{$lang['btn_notice']}</button>"; else $notice_btn = "";
	if ($row['autor'] != $member_id['name'] AND $user_group[$member_id['user_group']]['allow_all_edit'] AND !$row['approve']) $spam_btn = "<button  onclick=\"MarkSpam('{$id}', '{$dle_login_hash}'); return false;\" class=\"btn bg-brown-600 btn-sm btn-raised position-left\"><i class=\"fa fa-minus-square-o position-left\"></i> {$lang['btn_spam']}</button>"; else $spam_btn = "";

	echo <<<HTML
<div class="panel panel-default">

		    <div class="panel-heading">
				<ul class="nav nav-tabs nav-tabs-solid">
					<li class="active"><a href="#tabhome" data-toggle="tab"><i class="fa fa-home position-left"></i> {$lang['tabs_news']}</a></li>
					<li><a href="#tabvote" data-toggle="tab"><i class="fa fa-bar-chart position-left"></i> {$lang['tabs_vote']}</a></li>
					<li><a href="#tabextra" data-toggle="tab"><i class="fa fa-tasks position-left"></i> {$lang['tabs_extra']}</a></li>
					<li id="tab-perimit"><a href="#tabperm" data-toggle="tab"><i class="fa fa-lock position-left"></i> {$lang['tabs_perm']}</a></li>
				</ul>
                <div class="heading-elements">
	                <ul class="icons-list">
						<li><a href="#" class="panel-fullscreen"><i class="fa fa-expand"></i></a></li>
					</ul>
                </div>
			</div>

			<form method="post" class="form-horizontal" name="addnews" id="addnews" onsubmit="if(checkxf()=='fail') return false;" action="">
                 <div class="panel-tab-content tab-content">
                     <div class="tab-pane active" id="tabhome">
						<div class="panel-body">

							<div class="form-group">
							  <label class="control-label col-sm-2">{$lang['edit_info']}</label>
							  <div class="col-sm-10">
								<span class="position-left">ID=<b>{$row['id']}</b>, {$lang['edit_eau']}</span>{$author_info}
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">{$lang['edit_et']}</label>
							  <div class="col-sm-10">
								<input type="text" dir="auto" class="form-control width-550 position-left" name="title" id="title" value="{$row['title']}" maxlength="250"><input type="button" onclick="find_relates(); return false;" class="visible-lg-inline-block btn bg-info-800 btn-sm btn-raised" value="{$lang['b_find_related']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_title']}" ></i><span id="related_news"></span>
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">{$lang['edit_edate']}</label>
							  <div class="col-sm-10">
								<input type="text" dir="auto" name="newdate" data-rel="calendar" class="form-control position-left" style="width: 10rem;" value="{$row['date']}" autocomplete="off"><label class="checkbox-inline"><input class="icheck" type="checkbox" name="allow_now" id="allow_now" value="yes">{$lang['edit_jdate']}</label>
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">{$lang['edit_cat']}</label>
							  <div class="col-sm-10">
								<select data-placeholder="{$lang['addnews_cat_sel']}" name="category[]" id="category" onchange="onCategoryChange(this)" {$category_multiple} style="width:100%;max-width:350px;">{$categories_list}</select><input type="hidden" name="category_custom_sort" id="category_custom_sort" value="">
							  </div>
							 </div>
HTML;

	if (!$config['disable_short']) {
		echo <<<HTML
							 <div class="form-group editor-group">
							  <label class="control-label col-md-2">{$lang['addnews_short']}</label>
							  <div class="col-md-10">
HTML;
	}


	include (DLEPlugins::Check(ENGINE_DIR . '/editor/shortnews.php'));

	if (!$config['disable_short']) {
		echo <<<HTML
							  </div>
							</div>
HTML;
	}

	if (!$config['disable_full']) {
		echo <<<HTML
							 <div class="form-group editor-group">
							  <label class="control-label col-md-2">{$lang['addnews_full']}</label>
							  <div class="col-md-10">
HTML;

		include(DLEPlugins::Check(ENGINE_DIR . '/editor/fullnews.php'));

		echo <<<HTML
							  </div>
							</div>
HTML;

	}

	// XFields Call
	$xfieldsaction = "list";
	$xfieldsid = $row['xfields'];
	$xfieldscat = $row['category'];
	$news_id = $id;
	$author = urlencode($row['autor']);
	include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
	// End XFields Call

echo <<<HTML
{$output}
						</div>
							<div class="panel-body">
							  <label class="control-label col-md-2">{$lang['news_edit_reason']}</label>
							  <div class="col-md-10">
								<input type="text" dir="auto" class="form-control width-450 position-left" name="editreason" id="editreason" value="{$row['reason']}">
								{$lang['news_edit_date']}
								<div class="checkbox mt-10"><label><input class="icheck" type="checkbox" id="view_edit" name="view_edit" value="1" {$view_edit_cheked}>{$lang['allow_view_edit']}</label></div>
							  </div>
							 </div>
						
							<div class="panel-body">
							  <label class="control-label col-md-2">{$lang['addnews_option']}</label>
							  <div class="col-md-10">
								<div class="row">
									<div class="col-sm-6" style="max-width:19.5rem;">
										<div class="checkbox"><label><input class="icheck" type="checkbox" id="approve" name="approve" value="1" {$ifapp}>{$lang['addnews_mod']}</label></div>
										{$main_input}
										<div class="checkbox" id="opt_holder_rating"><label><input class="icheck" type="checkbox" id="allow_rating" name="allow_rating" value="1" {$ifrat}>{$lang['addnews_allow_rate']}</label></div>
									</div>
									<div class="col-sm-6">
										<div class="checkbox" id="opt_holder_comments"><label><input class="icheck" type="checkbox" id="allow_comm" name="allow_comm" value="1" {$ifch}>{$lang['addnews_comm']}</label></div>
										{$fix_input}
										{$disable_index}
										{$disable_search}
									</div>
								</div>
								{$rss_option}
							  </div>
							 </div>

					</div>
                    <div class="tab-pane" id="tabvote" >
						<div class="panel-body">

							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['v_ftitle']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="vote_title" class="form-control width-400" maxlength="200" value="{$poll['title']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_ftitle']}" ></i>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['vote_title']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="frage" class="form-control width-400" maxlength="200" value="{$poll['frage']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_vtitle']}" ></i>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['vote_body']}<div class="text-muted text-size-small">{$lang['vote_str_1']}</div></label>
							  <div class="col-md-10 col-sm-9">
								<textarea dir="auto" rows="7" class="classic width-400" name="vote_body">{$poll['body']}</textarea>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3"></label>
							  <div class="col-md-10 col-sm-9">
								<div class="checkbox"><label><input class="icheck" type="checkbox" id="allow_m_vote" name="allow_m_vote" value="1" {$poll['multiple']}>{$lang['v_multi']}</label></div>
								<div class="checkbox"><label><input class="icheck" type="checkbox" id="closed_vote" name="closed_vote" value="1" {$poll['closed']}>{$lang['v_closed']}</label><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_vclosed']}" ></i>{$lang['v_closed_date']}</div>
								{$clear_poll}
							  </div>
							 </div>
						</div>
						<div class="panel-body">
							<div class="col-md-12"><span class="text-muted text-size-small"> <i class="fa fa-exclamation-triangle position-left"></i>{$lang['v_info']}</span></div>
						</div>

                     </div>
                    <div class="tab-pane" id="tabextra" >
						<div class="panel-body">

							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['catalog_url']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="catalog_url" class="form-control" maxlength="3" style="width:55px;" value="{$row['symbol']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['catalog_hint_url']}" ></i>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['addnews_url']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="alt_name" class="form-control width-500" maxlength="190" value="{$row['alt_name']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_url']}" ></i>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-sm-2">{$lang['label_related']}</label>
							  <div class="col-sm-10">
								<input type="text" dir="auto" class="form-control width-350 position-left" name="related_ids" id="related_ids" value="{$row['related_ids']}"><input type="button" onclick="find_related_ids('{$row['id']}'); return false;" class="visible-lg-inline-block btn bg-info-800 btn-sm btn-raised" value="{$lang['b_related_renew']}">
							  </div>	
							</div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['addnews_tags']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="tags" id="tags" autocomplete="off" value="{$row['tags']}" />
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['date_expires']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="expires" data-rel="calendardate" class="form-control" style="width:200px;" value="{$expires['expires']}" autocomplete="off"><span class="position-right position-left visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs">{$lang['cat_action']}</span><select class="uniform" name="expires_action" id="expires_action" onchange="moveCategoryChange(this)"><option value="0">{$lang['mass_noact']}</option><option value="1" {$exp_action[1]}>{$lang['edit_dnews']}</option><option value="2" {$exp_action[2]}>{$lang['mass_edit_notapp']}</option><option value="3" {$exp_action[3]}>{$lang['mass_edit_notmain']}</option><option value="4" {$exp_action[4]}>{$lang['mass_edit_notfix']}</option><option value="5" {$exp_action[5]}>{$lang['m_cat_list_2']}</option><option value="6" {$exp_action[6]}>{$lang['v_closed']}</option></select><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_expires']}" ></i>
							  </div>
							 </div> 
							 <div class="form-group" id="movecatlist" style="display:none;">
							  <label class="control-label col-sm-2">{$lang['m_cat_list_1']}</label>
							  <div class="col-sm-10">
								<select data-placeholder="{$lang['addnews_cat_sel']}" title="{$lang['addnews_cat_sel']}" name="movecat[]" $category_multiple style="width:100%;max-width:350px;">{$move_cat_list}</select>
							  </div>
							</div>

							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3"></label>
							  <div class="col-md-10 col-sm-9">
								{$need_pass}
							  </div>
							  <div id="passlist" style="display:none;">
								<label class="control-label col-md-2 col-sm-3">{$lang['pass_list_2']}<div class="text-muted text-size-small">{$lang['pass_list_3']}</div></label>
								<div class="col-md-10 col-sm-9">
									<textarea dir="auto" rows="5" class="classic width-500" name="password">{$password}</textarea>
								</div>
							   </div>
							 </div>
						
							</div>
						 <div class="panel-body">
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['meta_title']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="meta_title" class="form-control width-500" maxlength="140" value="{$row['metatitle']}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['meta_descr']}</label>
							  <div class="col-md-10 col-sm-9">
								<input type="text" dir="auto" name="descr" id="autodescr" class="form-control width-500" maxlength="300" value="{$row['descr']}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$lang['meta_keys']}</label>
							  <div class="col-md-10 col-sm-9">
								<textarea dir="auto" class="tags" dir="auto" name="keywords" id='keywords'>{$row['keywords']}</textarea><br /><br>
									<input type="button" onclick="auto_keywords(1); return false;" class="btn bg-primary-600 btn-sm btn-raised position-left" value="{$lang['btn_descr']}">
									<input type="button" onclick="auto_keywords(2); return false;" class="btn bg-primary-600 btn-sm btn-raised" value="{$lang['btn_keyword']}">
							  </div>
							 </div>
							{$clear_subscribe}
						</div>

						<div class="panel-body">
							<div class="col-md-12"><span class="text-muted text-size-small"><i class="fa fa-exclamation-triangle position-left"></i>{$lang['hint_metas']}</span></div>
						</div>
                     </div>
                    <div class="tab-pane" id="tabperm" >
						<div class="panel-body">
HTML;

	if( $member_id['user_group'] < 3 ) {
		foreach ( $user_group as $group ) {
			if( $group['id'] > 1 ) {
				
				$access[$group['id']][1] = isset($access[$group['id']][1]) ? $access[$group['id']][1] : '';
				$access[$group['id']][2] = isset($access[$group['id']][2]) ? $access[$group['id']][2] : '';
				$access[$group['id']][3] = isset($access[$group['id']][3]) ? $access[$group['id']][3] : '';
				
				echo <<<HTML
							<div class="form-group">
							  <label class="control-label col-md-2 col-sm-3">{$group['group_name']}</label>
							  <div class="col-md-10 col-sm-9">
								<select class="uniform" name="group_extra[{$group['id']}]">
										<option value="0">{$lang['ng_group']}</option>
										<option value="1" {$access[$group['id']][1]}>{$lang['ng_read']}</option>
										<option value="2" {$access[$group['id']][2]}>{$lang['ng_all']}</option>
										<option value="3" {$access[$group['id']][3]}>{$lang['ng_denied']}</option>
								</select>
							   </div>
							 </div>
HTML;
			}
		}

		echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['banner_country']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" name="allowed_country" class="form-control" style="max-width:350px;" value="{$allowed_country}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['banner_country_2']}" ></i>
		  </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-md-2 col-sm-3">{$lang['banner_country_1']}</label>
		  <div class="col-md-10 col-sm-9">
			<input type="text" dir="auto" name="not_allowed_country" class="form-control" style="max-width:350px;" value="{$not_allowed_country}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['banner_country_2']}" ></i>
		  </div>
		</div>
HTML;

	} else {

		echo <<<HTML
    <div class="text-center pt-20 pb-20">{$lang['tabs_not']}</div>
HTML;

	}
	
	$expires['expires'] = isset($expires['expires']) ? $expires['expires'] : '';
	$expires['action'] = isset($expires['action']) ? $expires['action'] : '';
	$expires['move_cat'] = isset($expires['move_cat']) ? $expires['move_cat'] : '';

echo <<<HTML
						</div>
							<div class="panel-body">
								<div class="col-md-12"><span class="text-muted text-size-small"><i class="fa fa-exclamation-triangle position-left"></i>{$lang['tabs_g_info']}</span></div>
							</div>
                     </div>
				</div>
				<div class="panel-footer">
					<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['news_save']}</button>
					<button onclick="preview(); return false;" class="btn bg-slate-600 btn-sm btn-raised position-left"><i class="fa fa-desktop position-left"></i>{$lang['btn_preview']}</button>
					{$notice_btn}
					{$spam_btn}
					<button onclick="confirmDelete('?mod=editnews&action=doeditnews&ifdelete=yes&id=$id&user_hash=$dle_login_hash', '{$id}'); return false;" class="btn bg-danger btn-sm btn-raised"><i class="fa fa-trash-o position-left"></i>{$lang['edit_dnews']}</button>
					<input type="hidden" name="id" value="$id" />
					<input type="hidden" name="expires_alt" value="{$expires['expires']}{$expires['action']}{$expires['move_cat']}" />
					<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
					<input type="hidden" name="action" value="doeditnews" />
					<input type="hidden" name="mod" value="editnews" />
				</div>
</form>
</div>
HTML;

	echofooter();

} elseif( $action == "doeditnews" ) {
	
	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		msg( "error", $lang['addnews_error'], $lang['sess_error'], "javascript:history.go(-1)" );
	}
	
	@header('X-XSS-Protection: 0;');
	
	$id = intval( $_GET['id'] );
	$mail_send = false;

	$allow_comm = isset( $_POST['allow_comm'] ) ? intval( $_POST['allow_comm'] ) : 0;
	$allow_main = isset( $_POST['allow_main'] ) ? intval( $_POST['allow_main'] ) : 0;
	$approve = isset( $_POST['approve'] ) ? intval( $_POST['approve'] ) : 0;
	$allow_rating = isset( $_POST['allow_rating'] ) ? intval( $_POST['allow_rating'] ) : 0;
	$news_fixed = isset( $_POST['news_fixed'] ) ? intval( $_POST['news_fixed'] ) : 0;
	$view_edit = isset( $_POST['view_edit'] ) ? intval( $_POST['view_edit'] ) : 0;
	$category = isset( $_POST['category'] ) ? $_POST['category'] : array();
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;
	$disable_search = isset( $_POST['disable_search'] ) ? intval( $_POST['disable_search'] ) : 0;
	$need_pass = isset( $_POST['need_pass'] ) ? intval( $_POST['need_pass'] ) : 0;

	$allow_rss = isset( $_POST['allow_rss'] ) ? intval( $_POST['allow_rss'] ) : 0;
	$allow_rss_dzen = isset( $_POST['allow_rss_dzen'] ) ? intval( $_POST['allow_rss_dzen'] ) : 0;

	if( $config['allow_cat_sort'] AND isset($_POST['category_custom_sort']) AND count(explode('::', $_POST['category_custom_sort'])) > 1 ) {
		$category = explode('::', $_POST['category_custom_sort']);
	}
	
	$disable_rss_dzen = 0;
	
	if($member_id['user_group'] > 2 ) {
		$disable_index = 0;
		$disable_search = 0;
		$need_pass = 0;
		$allow_rss = 1;
		$allow_rss_dzen = 1;
	}

	if( !$config['allow_rss'] ) { $allow_rss = 1; }
	if( !$config['allow_yandex_dzen'] ) { $allow_rss_dzen = 0; }
	
	if($need_pass AND isset($_POST['password']) AND trim($_POST['password']) )$need_pass = 1; else $need_pass = 0;

	if( !is_array($category) ) $category = array ();
	
	if( !count($category) ) $category[] = '0';

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	if($member_id['cat_add']) $allow_list = explode( ',', $member_id['cat_add'] );
	else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

	foreach ( $category_list as $selected ) {
		
		if( $allow_list[0] != "all" AND !in_array( $selected, $allow_list ) ) {
			$approve = 0;
			$mail_send = true;
		}
		
		if(isset($cat_info[$selected]['disable_main']) AND $cat_info[$selected]['disable_main']) $allow_main = 0;
		if(isset($cat_info[$selected]['disable_comments']) AND $cat_info[$selected]['disable_comments']) $allow_comm = 1;
		if(isset($cat_info[$selected]['disable_rating']) AND $cat_info[$selected]['disable_rating']) $allow_rating = 0;

		if($member_id['user_group'] > 2 ) {
			if(!$cat_info[$selected]['enable_dzen']) $disable_rss_dzen ++;
		}
		
	}

	if($member_id['user_group'] > 2 ) {
		if( $disable_rss_dzen AND $disable_rss_dzen = count($category_list) ) $allow_rss_dzen = 0;
	}
	
	if($member_id['cat_allow_addnews']) $allow_list = explode( ',', $member_id['cat_allow_addnews'] );
	else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );

	foreach ( $category_list as $selected ) {
		if( $allow_list[0] != "all" AND ! in_array( $selected, $allow_list ) AND $ifdelete != "yes") msg( "error", $lang['addnews_error'], $lang['news_err_41'], "javascript:history.go(-1)" );
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );

	if( !$user_group[$member_id['user_group']]['moderation'] ) {
		$approve = 0;
		$mail_send = true;
	}

	$_POST['title'] = isset($_POST['title']) ? $_POST['title'] : '';
	$title = $parse->process( trim( strip_tags ($_POST['title']) ) );

	$parse->allow_code = false;

	$_POST['full_story'] = isset($_POST['full_story']) ? $_POST['full_story'] : '';
	$_POST['short_story'] = isset($_POST['short_story']) ? $_POST['short_story'] : '';
	
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );

	$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
	$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );

	if( $parse->not_allowed_text ) {
		msg( "error", $lang['addnews_error'], $lang['news_err_39'], "javascript:history.go(-1)" );
	}

	if( !$title AND $ifdelete != "yes" ) msg( "error", $lang['cat_error'], $lang['addnews_alert'], "javascript:history.go(-1)" );

	if( dle_strlen( $title ) > 255 ) {
		msg( "error", $lang['cat_error'], $lang['addnews_ermax'], "javascript:history.go(-1)" );
	}
	
	$alt_name = isset($_POST['alt_name']) ? trim($_POST['alt_name']) : '';
	
	if(!$alt_name) $alt_name = totranslit( stripslashes( $title ), true, false, $config['translit_url'] );
	else $alt_name = totranslit( stripslashes( $alt_name ), true, false, $config['translit_url'] );
	
	$title = $db->safesql( $title );
	$alt_name = $db->safesql( $alt_name );

	$_POST['catalog_url'] = isset($_POST['catalog_url']) ? $_POST['catalog_url'] : '';
	$catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['catalog_url'] ) ) ), ENT_QUOTES, 'UTF-8' ), 0, 3 ) );

	if ($config['create_catalog'] AND !$catalog_url) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ), ENT_QUOTES, 'UTF-8' ), 0, 1 ) );

	$_POST['editreason'] = isset($_POST['editreason']) ? $_POST['editreason'] : '';
	$editreason = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['editreason'] ) ) ), ENT_QUOTES, 'UTF-8' ) );

	$_POST['tags'] = isset($_POST['tags']) ? $_POST['tags'] : '';
	
	if( preg_match( "/[\||\<|\>]/", (string)$_POST['tags'] ) ) $_POST['tags'] = "";
	else $_POST['tags'] = htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_COMPAT, 'UTF-8' );

	if ( $_POST['tags'] ) {

		$tags_array = array();
		$temp_array = explode (",", $_POST['tags']);

		if (count($temp_array)) {

			foreach ( $temp_array as $value ) {
				if( trim($value) ) $tags_array[] = $db->safesql(trim( $value ));
			}

		}

		if ( count($tags_array) ) $_POST['tags'] = implode(", ", array_unique($tags_array) ); else $_POST['tags'] = "";

	}

	$_POST['vote_title'] = isset($_POST['vote_title']) ? $_POST['vote_title'] : '';
	
	if( trim( $_POST['vote_title'] ) ) {

		$add_vote = 1;
		$vote_title = trim( $db->safesql( $parse->process( strip_tags($_POST['vote_title']) ) ) );
		$frage = trim( $db->safesql( $parse->process( strip_tags($_POST['frage']) ) ) );
		$vote_body = $db->safesql( $parse->BB_Parse( $parse->process( strip_tags($_POST['vote_body']) ), false ) );
		$allow_m_vote = isset($_POST['allow_m_vote']) ? intval( $_POST['allow_m_vote'] ) : 0;
		$closed_vote = isset($_POST['closed_vote']) ? intval($_POST['closed_vote']) : 0;
		$closed_time = '';

		if ($closed_vote) {
			$closed_time = $_TIME;
			$closed_vote = 1;
		}

	} else $add_vote = 0;

	$_POST['related_ids'] = isset($_POST['related_ids']) ? $_POST['related_ids'] : '';
	
	if( trim( $_POST['related_ids'] ) ) {
		
		$_POST['related_ids'] = explode(',', $_POST['related_ids']);
		
		foreach ( $_POST['related_ids'] as $value ) {
			if( intval($value) ){
				$related_ids[] = intval($value);
			}
		}
		
		$related_ids = implode(',', $related_ids);
	
	} else $related_ids = '';
	
	if( $member_id['user_group'] < 3 AND $ifdelete != "yes" ) {

		$group_regel = array ();

		foreach ( $_POST['group_extra'] as $key => $value ) {
			if( $value ) $group_regel[] = intval( $key ) . ':' . intval( $value );
		}

		if( count( $group_regel ) ) $group_regel = implode( "||", $group_regel );
		else $group_regel = "";

		$allowed_country = isset($_REQUEST['allowed_country']) ? $db->safesql(strip_tags(trim($_POST['allowed_country']))) : '';
		$not_allowed_country = isset($_REQUEST['not_allowed_country']) ? $db->safesql(strip_tags(trim($_POST['not_allowed_country']))) : '';

	} else {

		$group_regel = '';
		$allowed_country = '';
		$not_allowed_country = '';

	}

	$movecat = isset($_POST['movecat']) ? $_POST['movecat'] : array();
	
	if( !is_array($movecat) ) $movecat = array ();

	if( !count($movecat) ) $movecat[] = '0';

	$movecat_list = array();

	foreach ( $movecat as $value ) {
		$movecat_list[] = intval($value);
	}
				
	$movecat_list = $db->safesql( implode( ',', $movecat_list ) );
	
	$_POST['expires'] = isset($_POST['expires']) ? $_POST['expires'] : '';
	$_POST['expires_action'] = isset($_POST['expires_action']) ? $_POST['expires_action'] : '';
	$_POST['expires_alt'] = isset($_POST['expires_alt']) ? $_POST['expires_alt'] : '';
	
	if (!$add_vote AND $_POST['expires_action'] == 6) {
		$_POST['expires'] = '';
		$_POST['expires_action'] = 0;
	}

	if ( ($_POST['expires'].$_POST['expires_action'].$movecat_list) != $_POST['expires_alt'] ) {
		
		if( trim( $_POST['expires'] ) != "" ) {
			if( (($expires = strtotime( $_POST['expires'] )) === - 1) OR !$expires) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
			}
		} else $expires = '';

		$expires_change = true;

	} else $expires_change = false;

	$no_permission = FALSE;
	$okdeleted = FALSE;
	$okchanges = FALSE;

	$db->query( "SELECT id, autor, date, xfields, title, category, approve, tags, news_id, disable_index, disable_search, need_pass, allow_rss, allow_rss_dzen  FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );

	while ( $row = $db->get_row() ) {
		$item_db[0] = $row['id'];
		$item_db[1] = $row['autor'];
		$item_db[2] = $row['tags'];
		$item_db[3] = $row['approve'];
		$item_db[4] = $db->safesql( $row['title'] );
		$item_db[5] = explode( ',', $row['category'] );
		$item_db[6] = $row['news_id'];
		$item_db[7] = strtotime( $row['date'] );
		$item_db[8] = $row['category'];
		$xf_existing = xfieldsdataload($row['xfields']);
		
		if($member_id['user_group'] > 2 ) {
			$disable_index = $row['disable_index'];
			$disable_search = $row['disable_search'];
			$need_pass = $row['need_pass'];
			$allow_rss = $row['allow_rss'];
			$allow_rss_dzen = $row['allow_rss_dzen'];
		}
		
	}

	$db->free();

	if( $ifdelete != "yes" ) {

		$xfieldsaction = "init";
		$xfieldsid = $item_db[0];
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));

		if (dle_strlen($full_story) > 12) $all_xf_content = $full_story . ' ' . $all_xf_content;
		else $all_xf_content = $short_story . ' ' . $all_xf_content;

		$metatags = create_metatags($all_xf_content);

		if ($config['related_news'] AND !$related_ids) {
			$related_ids = get_related_ids($title . ' ' . $all_xf_content, $item_db[0], $category_list);
		}

	}


	if( isset($item_db[0]) AND $item_db[0] ) {

		$have_perm = 0;

		if( $user_group[$member_id['user_group']]['allow_edit'] and $item_db[1] == $member_id['name'] ) {
			$have_perm = 1;
		}
		
		if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
			$have_perm = 1;
			
			if($member_id['cat_add']) $allow_list = explode( ',', $member_id['cat_add'] );
			else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
	
			foreach ( $item_db[5] as $selected ) {
				if( $allow_list[0] != "all" AND !in_array($selected, $allow_list) ) $have_perm = 0;
			}
		}
	
		if( $ifdelete == "yes" ) {

			if( !$user_group[$member_id['user_group']]['moderation']) {

				$have_perm = 0;

			}
		}
		
		if( $user_group[$member_id['user_group']]['max_edit_days'] ) {
			$maxedittime = $_TIME - ($user_group[$member_id['user_group']]['max_edit_days'] * 3600 * 24);
			if( $maxedittime > $item_db[7] ) $have_perm = 0;
		}
		
		if( ($member_id['user_group'] == 1) ) {
			$have_perm = 1;
		}
		
		if( $have_perm ) {

			if( $ifdelete != "yes" ) {
				$okchanges = TRUE;

				$added_time = time();
				$newdate = trim($_POST['newdate']);
				$index_now_allow = true;
				
				if( $config['allow_alt_url'] AND !$config['seo_type'] ) {
					
					$db->query( "SELECT id, date FROM " . PREFIX . "_post WHERE alt_name ='{$alt_name}' AND id != '$item_db[0]' " );
			
					while($found_news = $db->get_row()) {
						if( $found_news['id'] AND date( 'Y-m-d', strtotime( $found_news['date'] ) ) == date( 'Y-m-d', $_TIME ) ) {
							msg( "error", array($_SESSION['admin_referrer'] => $lang['edit_all_title'], '' => $lang['addnews_error'] ), $lang['news_err_42'], "javascript:history.go(-1)" );
						}	
					}
				
				}
	
				if( $newdate ) {

					if( isset($_POST['allow_now']) AND $_POST['allow_now'] == "yes" ) {
						
						$thistime = date( "Y-m-d H:i:s", $added_time );
						
					} elseif( (($newsdate = strtotime( $newdate )) === - 1) OR !$newsdate ) {
						
						msg( "error", $lang['cat_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
						
					} else {

						$thistime = date( "Y-m-d H:i:s", $newsdate );

						if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
							$thistime = date( "Y-m-d H:i:s", $added_time );
						}

						if ($newsdate > $added_time AND !$config['news_future']) $index_now_allow = false;

					}

				} else $thistime = date( "Y-m-d H:i:s", $added_time );

				$db->query( "UPDATE " . PREFIX . "_post SET title='{$title}', date='{$thistime}', short_story='{$short_story}', full_story='{$full_story}', xfields='{$filecontents}', descr='{$metatags['description']}', keywords='{$metatags['keywords']}', category='{$category_list}', alt_name='{$alt_name}', allow_comm='{$allow_comm}', approve='{$approve}', allow_main='{$allow_main}', fixed='{$news_fixed}', allow_br='0', symbol='{$catalog_url}', tags='{$_POST['tags']}', metatitle='{$metatags['title']}' WHERE id='{$item_db[0]}'" );

				if ($item_db[6]) $db->query( "UPDATE " . PREFIX . "_post_extras SET allow_rate='{$allow_rating}', votes='{$add_vote}', disable_index='{$disable_index}', related_ids='{$related_ids}', access='{$group_regel}', editdate='{$added_time}', editor='{$member_id['name']}', reason='{$editreason}', view_edit='{$view_edit}', disable_search='{$disable_search}', need_pass='{$need_pass}', allow_rss='{$allow_rss}', allow_rss_dzen='{$allow_rss_dzen}', edited_now='', allowed_country='{$allowed_country}', not_allowed_country='{$not_allowed_country}' WHERE news_id='{$item_db[0]}'" );
				else $db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, related_ids, access, editdate, editor, reason, view_edit, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country) VALUES('{$item_db[0]}', '{$allow_rating}', '{$add_vote}', '{$disable_index}', '{$related_ids}', '{$group_regel}', '{$added_time}', '{$member_id['name']}', '{$editreason}', '{$view_edit}', '{$disable_search}', '{$need_pass}', '{$allow_rss}', '{$allow_rss_dzen}', '{$allowed_country}', '{$not_allowed_country}')" );

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '25', '{$title}')" );


				if( $add_vote ) {

					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll WHERE news_id = '$item_db[0]'" );

					if( $count['count'] ) {

						$closed = '';

						if($closed_vote) {

							$is_closed = $db->super_query("SELECT closed FROM " . PREFIX . "_poll WHERE news_id = '$item_db[0]'");

							if( !$is_closed['closed'] ) {
								$closed = ", closed='1', date_closed='{$_TIME}'";
							}

						} else $closed = ", closed='0', date_closed=''";

						$db->query( "UPDATE  " . PREFIX . "_poll SET title='{$vote_title}', frage='{$frage}', body='{$vote_body}', multiple='{$allow_m_vote}'{$closed} WHERE news_id = '{$item_db[0]}'" );

					} else {

						$db->query("INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer, closed, date_closed) VALUES('$item_db[0]', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '', '{$closed_vote}', '{$closed_time}')");
					
					}

				} else {
					
					$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='$item_db[0]'" );
					$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='$item_db[0]'" );
					
				}
				
				if ( $need_pass ) {
					$post_password = $db->safesql($_POST['password']);
					
					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post_pass WHERE news_id = '{$item_db[0]}'" );
					
					if($post_password) {
						if( $count['count'] ) $db->query( "UPDATE  " . PREFIX . "_post_pass SET password='{$post_password}' WHERE news_id = '{$item_db[0]}'" );
						else $db->query( "INSERT INTO " . PREFIX . "_post_pass (news_id, password) VALUES('{$item_db[0]}', '{$post_password}')" );
					}

				} else {
					
					$db->query( "DELETE FROM " . PREFIX . "_post_pass WHERE news_id='{$item_db[0]}'" );
					
				}
	
				if ( $expires_change ) {

					$expires_action = intval($_POST['expires_action']);
		
					$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='$item_db[0]'" );

					if( $expires AND $expires_action ) {
						$db->query( "INSERT INTO " . PREFIX . "_post_log (news_id, expires, action, move_cat) VALUES('$item_db[0]', '$expires', '$expires_action', '$movecat_list')" );
					}

				}

				if( $_POST['tags'] != $item_db[2] OR $approve != $item_db[3] ) {
					$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '$item_db[0]'" );

					if( $_POST['tags'] != "" AND $approve ) {

						$tags = array ();

						$_POST['tags'] = explode( ",", $_POST['tags'] );

						foreach ( $_POST['tags'] as $value ) {

							$tags[] = "('" . $item_db[0] . "', '" . trim( $value ) . "')";
						}

						$tags = implode( ", ", $tags );
						$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );

					}
				}

				if( $category_list != $item_db[8] OR $approve != $item_db[3] ) {
					$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '$item_db[0]'" );

					if( $category_list AND $approve ) {

						$cat_ids = array ();

						$cat_ids_arr = explode( ",", $category_list );

						foreach ( $cat_ids_arr as $value ) {

							$cat_ids[] = "('" . $item_db[0] . "', '" . trim( $value ) . "')";
						}

						$cat_ids = implode( ", ", $cat_ids );
						$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );

					}
				}
				
				$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$item_db[0]}'" );

				if ( count($xf_search_words) AND $approve ) {
					
					$temp_array = array();
					
					foreach ( $xf_search_words as $value ) {
						
						$temp_array[] = "('" . $item_db[0] . "', '" . $value[0] . "', '" . $value[1] . "')";
					}
					
					$xf_search_words = implode( ", ", $temp_array );
					$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
				}
				
				if( $member_id['user_group'] < 3 AND $_POST['new_author'] != $_POST['old_author'] ) {

					$_POST['new_author'] = $db->safesql( $_POST['new_author'] );

					$row = $db->super_query( "SELECT user_id  FROM " . USERPREFIX . "_users WHERE name = '{$_POST['new_author']}'" );

					if( isset($row['user_id']) AND $row['user_id'] ) {

						$db->query( "UPDATE " . PREFIX . "_post SET autor='{$_POST['new_author']}' WHERE id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_post_extras SET user_id='{$row['user_id']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_images SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_files SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );

						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num+1 WHERE user_id='{$row['user_id']}'" );
						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 WHERE name='$item_db[1]'" );

					} else {

						msg( "warning", $lang['addnews_error'], $lang['edit_no_author'], "javascript:history.go(-1)" );

					}

				}
				
				if( !$approve AND $approve != $item_db[3] AND $mail_send AND $config['mail_news'] ) {
					
					$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='new_news' LIMIT 0,1" );
					$mail = new dle_mail( $config, $row['use_html'] );
					
					$row['template'] = stripslashes( $row['template'] );
					$row['template'] = str_replace( "{%username%}", $member_id['name'], $row['template'] );
					$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $added_time, true ), $row['template'] );
					$row['template'] = str_replace( "{%title%}", stripslashes( stripslashes( $title ) ), $row['template'] );
					
					$category_list = explode( ",", $category_list );
					$my_cat = array ();
					
					foreach ( $category_list as $element ) {
						
						$my_cat[] = $cat_info[$element]['name'];
					
					}
					
					$my_cat = stripslashes( implode( ', ', $my_cat ) );
					
					$row['template'] = str_replace( "{%category%}", $my_cat, $row['template'] );
					
					$mail->send( $config['admin_mail'], $lang['mail_news'], $row['template'] );
				
				}
	
			} else {
	
				deletenewsbyid( $item_db[0] );
				$okdeleted = TRUE;

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '26', '{$item_db[4]}')" );

			}
			
		} else $no_permission = TRUE;

	}

	$item_db[0] = isset($item_db[0]) ? $item_db[0] : '';
	
	if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$item_db[0];
	
	clear_cache( array('news_', $cprefix, 'comm_'.$item_db[0], 'tagscloud_', 'archives_', 'related_', 'calendar_', 'rss', 'stats') );

	if( !isset($_SESSION['admin_referrer']) OR (isset($_SESSION['admin_referrer']) AND !$_SESSION['admin_referrer']) ) {

		$_SESSION['admin_referrer'] = "?mod=editnews&amp;action=list";

	}

	if( $no_permission ) {
		msg( "error", $lang['addnews_error'], $lang['edit_denied'], $_SESSION['admin_referrer'] );
	} elseif( $okdeleted ) {
		msg( "success", $lang['edit_delok'], $lang['edit_delok_1'], array( $_SESSION['admin_referrer'] => $lang['add_s_3'] ) );
	} elseif( $okchanges ) {
		
		$row = $db->super_query( "SELECT id, date, category, alt_name FROM " . PREFIX . "_post WHERE id='{$item_db[0]}' LIMIT 1" );
		$config['http_home_url'] = dle_strtolower($config['http_home_url']);
		
		if( $config['allow_alt_url'] ) {
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( $row['category'] and $config['seo_type'] == 2 ) {

					$cats_url = get_url( $row['category'] );
					
					if($cats_url) {
						
						$full_link = $config['http_home_url'] . $cats_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					} else $full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}
				
			} else {
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $row['date'] ) ) . $row['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		}
		
		if( $config['news_indexnow'] AND ($approve OR (!$approve AND $approve != $item_db[3]) ) AND $index_now_allow) {

			$result = DLESEO::IndexNow( $full_link );
			
			if( $result !== true ) {
				
				$lang['indexnow_error_1'] = str_ireplace('{name}', md5(SECURE_AUTH_KEY), $lang['indexnow_error_1']);
				$lang['indexnow_error_2'] = str_ireplace('{name}', md5(SECURE_AUTH_KEY), $lang['indexnow_error_2']);
		
				if( $result === null ) $result = $lang['indexnow_error_1'];
				elseif( $result === -1 ) $result = $lang['indexnow_error_2'];
				elseif( is_array($result) ) {
					
					$lang['indexnow_error_3'] = str_ireplace('{searchengine}', $result[0], $lang['indexnow_error_3']);
					$lang['indexnow_error_3'] = str_ireplace('{errorcode}',    $result[1], $lang['indexnow_error_3']);
					
					$result = $lang['indexnow_error_3'];
				}
				
				$result = "<div class=\"alert alert-warning alert-styled-left alert-arrow-left text-left\">".$result."</div>";
				
			} else $result = ''; 
	
		} else $result = '';
	
		msg( "success", $lang['edit_alleok'], $lang['edit_alleok_1'].$result, array( $_SESSION['admin_referrer'] => $lang['add_s_3'], '?mod=editnews&action=editnews&id='.$item_db[0] => $lang['add_s_4'], $full_link => $lang['add_s_5'] ) );
		
	} else {
		msg( "error", $lang['addnews_error'], $lang['edit_allerr'], $_SESSION['admin_referrer'] );
	}
}
?>