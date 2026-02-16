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
 File: engine.php
=====================================================
*/

if (!defined('DATALIFEENGINE')) {
	die ( "Hacking attempt!" );
}

if ($cstart < 0) $cstart = 0;

$allow_add_comment = false;
$allow_active_news = true;
$allow_comments = false;
$allow_userinfo = false;
$active = false;
$disable_index = false;
$social_tags = array();
$canonical = false;
$url_page = false;
$user_query = false;
$news_author = false;
$attachments = array ();
$short_news_cache = false;
$sql_select_ids = false;

switch ( $do ) {
	
	case "search" :
		
		if (isset($_REQUEST['mode']) AND $_REQUEST['mode'] == "advanced") $_REQUEST['full_search'] = 1;
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/search.php'));
		break;

	case "download" :
		if (isset($_REQUEST['mode']) AND $_REQUEST['mode'] == 'error') {
			include_once(DLEPlugins::Check(ENGINE_DIR . '/download.php'));
		}

		break;

	case "changemail":
		include(DLEPlugins::Check(ENGINE_DIR . '/modules/changemail.php'));
		break;

	case "findcomments":
		include(DLEPlugins::Check(ENGINE_DIR . '/modules/findcomments.php'));
		break;
		
	case "deletenews" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/deletenews.php'));
		break;

	case "comments" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/comments.php'));
		break;
	
	case "stats" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/stats.php'));
		break;
	
	case "addnews" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/addnews.php'));
		break;
	
	case "register" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/register.php'));
		break;
	
	case "lostpassword" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/lostpassword.php'));
		break;
	
	case "rules" :
		$_GET['page'] = "dle-rules-page";
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/static.php'));
		break;
	
	case "static" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/static.php'));
		break;
	
	case "alltags" :
		include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/tagscloud.php'));
		break;

	case "auth-social" :
		include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/social.php'));
		break;
	
	case "favorites" :
		
		if ($is_logged) {
			
			include (DLEPlugins::Check(ENGINE_DIR . '/modules/favorites.php'));
		
		} else {
			
			@header( "HTTP/1.1 403 Forbidden" );
			msgbox ( $lang['all_err_1'], $lang['fav_error'] );
			
		}
			
		break;
	
	case "feedback" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/feedback.php'));
		break;
	
	case "lastcomments" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/lastcomments.php'));
		break;
	
	case "pm" :
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/pm.php'));
		break;

	case "unsubscribe" :
		$_GET['post_id'] = intval ($_GET['post_id']);
		$_GET['user_id'] = intval ($_GET['user_id']);

		if ($_GET['post_id'] AND $_GET['user_id'] AND $_GET['hash']) {

			$row = $db->super_query( "SELECT hash FROM " . PREFIX . "_subscribe WHERE news_id='{$_GET['post_id']}' AND user_id='{$_GET['user_id']}'" );

			if ($row['hash'] AND $row['hash'] == $_GET['hash']) {

				$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE news_id='{$_GET['post_id']}' AND user_id='{$_GET['user_id']}'" );
				msgbox( $lang['all_info'],  $lang['unsubscribe_ok']);

			} else {
				msgbox( $lang['all_info'],  $lang['unsubscribe_err']);
			}

		} else {
			msgbox( $lang['all_info'],  $lang['unsubscribe_err']);
		}

		break;
	
	case "newsletterunsubscribe" :
		
		$_GET['user_id'] = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
		$_GET['hash'] = isset($_GET['hash']) ? $_GET['hash'] : 0;

		if ($_GET['user_id'] AND $_GET['hash']) {

			$row = $db->super_query( "SELECT password, user_id FROM " . USERPREFIX . "_users WHERE user_id='{$_GET['user_id']}'" );
			
			if ($row['user_id']) {
				
				if( !isset($config['key']) ) $config['key'] = '';
				$unsubscribe_hash = md5( SECURE_AUTH_KEY . $_SERVER['HTTP_HOST'] . $row['user_id'] . sha1( substr($row['password'], 0, 6) ) . $config['key'] );
	
				if ($unsubscribe_hash == $_GET['hash']) {
	
					$db->query( "UPDATE " . USERPREFIX . "_users SET allow_mail='0' WHERE user_id = '{$_GET['user_id']}'" );
					
					msgbox( $lang['all_info'],  $lang['n_unsubscribe_ok']);
	
				} else {
					
					msgbox( $lang['all_info'],  $lang['n_unsubscribe_err']);
					
				}
				
			} else {
				msgbox( $lang['all_info'],  $lang['n_unsubscribe_err']);
			}

		} else {
			msgbox( $lang['all_info'],  $lang['n_unsubscribe_err']);
		}

		break;
	
	default :
		
		$active = false;
		$user_query = "";
		$url_page = "";
		
		$thisdate = date ( "Y-m-d H:i:s", time () );
		if ($config['no_date'] AND !$config['news_future']) $where_date = " AND date < '" . $thisdate . "'";
		else $where_date = "";
		
		if ($config['allow_fixed']) $fixed = "fixed desc, ";
		else $fixed = "";
		
		$config['news_number'] = intval ( $config['news_number'] );

		if( $config['news_number'] < 1 ) $config['news_number'] = 1;

		if ( $smartphone_detected AND $config['mobile_news'] ) $config['news_number'] = intval ( $config['mobile_news'] );
		
		$news_sort_by = $config['news_sort'] ? $config['news_sort'] : "date";
		$news_direction_by = $config['news_msort'] ? $config['news_msort'] : "DESC";

		$allow_list = explode ( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		$stop_list = "";
		$cat_join = "";
		$cat_join_count = "";
		$extra_join = "LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) ";

		if ($allow_list[0] != "all") {
			
			if ($config['allow_multi_category']) {
				
				$cat_join = "INNER JOIN (SELECT DISTINCT " . PREFIX . "_post_extras_cats.news_id FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $allow_list ) . ")) c ON (p.id=c.news_id) ";
				$cat_join_count = "p ".$cat_join;
			
			} else {
				
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			
			}
		
		}
		
		$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
		
		if( $not_allow_cats[0] ) {
			
			if ($config['allow_multi_category']) {
				
				$stop_list = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") ) AND ";
				$cat_join_count = "p ";
				
			} else {
				
				$stop_list = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
			
			}
			
		}

		if( $config['user_in_news'] ) {
			
			$user_select = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields ";
			$user_join = "LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id) ";
			
		} else { $user_select = ""; $user_join = ""; }
		
		if ($user_group[$member_id['user_group']]['allow_short']) { $stop_list = ""; $cat_join = ""; $cat_join_count = ""; }
		
		$sql_select = '';
		$sql_count = '';
		$sql_news = '';

		// ################ Show of a category #################
		if ($do == "cat" AND $category AND !$subaction) {

			$allow_sub_cats = true;
			
			if( $config['allow_alt_url'] AND $config['seo_control'] AND $category_id AND $view_template != "rss") {

				$re_cat = get_url( $category_id );

				if ($re_cat != $_GET['category'] OR substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR strpos ($_SERVER['REQUEST_URI'], "do=cat" ) !== false ) {
					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );

					if( (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//') AND $_GET['cstart'] AND $_GET['cstart'] != 1 ) {
					 $re_cat .= "/page/".intval($_GET['cstart']);
					}
					
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}{$re_cat}/");
					die("Redirect");
				}
			}

			if (!$category_id) $category_id = 'not detected';
			
			if ($allow_list[0] != "all") {
				if (!$user_group[$member_id['user_group']]['allow_short'] AND !in_array( $category_id, $allow_list )) $category_id = 'not detected';
			}

			if ($not_allow_cats[0]) {
				if (!$user_group[$member_id['user_group']]['allow_short'] AND in_array( $category_id, $not_allow_cats )) $category_id = 'not detected';
			}

			$cat_restric_join = '';

			if (!$user_group[$member_id['user_group']]['allow_short'] AND $allow_list[0] != "all") {

				foreach ($cat_info as $temp_cat) {
					if( !in_array($temp_cat['id'], $allow_list) ) {
						if( $not_allow_cats[0] ) $not_allow_cats[] = $temp_cat['id']; else $not_allow_cats[0] = $temp_cat['id'];
					}
				}

			}

			if (!$user_group[$member_id['user_group']]['allow_short'] AND $not_allow_cats[0] ) {

				if ($config['allow_multi_category']) {

					$cat_restric_join = " AND news_id NOT IN (SELECT news_id FROM dle_post_extras_cats WHERE cat_id IN ('" . implode("','", $not_allow_cats) . "'))";

				}
			}

			if( !intval($category_id) ) {
				$allow_active_news = false;
			}
			
			if ( isset($cat_info[$category_id]['show_sub']) AND $cat_info[$category_id]['show_sub'] ) {

				if ( $cat_info[$category_id]['show_sub'] == 1 ) $get_cats = get_sub_cats ( $category_id );
				else { $get_cats = $category_id; $allow_sub_cats = false; }

			} else {

				if ( $config['show_sub_cats'] ) $get_cats = get_sub_cats ( $category_id );
				else { $get_cats = $category_id; $allow_sub_cats = false; }

			}

			if (isset($cat_info[$category_id]['news_sort']) AND $cat_info[$category_id]['news_sort']) $news_sort_by = $cat_info[$category_id]['news_sort'];
			if (isset($cat_info[$category_id]['news_msort']) AND $cat_info[$category_id]['news_msort']) $news_direction_by = $cat_info[$category_id]['news_msort'];
			if (isset($cat_info[$category_id]['news_number']) AND $cat_info[$category_id]['news_number']) $config['news_number'] = $cat_info[$category_id]['news_number'];
			if (isset($cat_info[$category_id]['disable_index']) AND $cat_info[$category_id]['disable_index']) $disable_index = true;

			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			if( isset( $cat_info[$category_id]['alt_name'] ) ) {
				$url_page = $config['http_home_url'] . get_url ( $category_id );
				$user_query = "do=cat&amp;category=" . get_url ( $category_id );
			} else {
				$url_page = '';
				$user_query = '';
			}
			
			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?do=cat&category=" . get_url ( $category_id );
			
			if ($config['allow_multi_category']) {

				$get_cats = str_replace ( "|", "','", $get_cats );
				$join_category = "INNER JOIN (SELECT DISTINCT " . PREFIX . "_post_extras_cats.news_id FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $get_cats . "'){$cat_restric_join}) c ON (p.id=c.news_id) ";
				$where_category = "";
				
			} else {
				
				if ( $allow_sub_cats ) {
					
					$get_cats = str_replace ( "|", "','", $get_cats );
					$where_category = "category IN ('" . $get_cats . "') AND ";
				
				} else {
					
					$where_category = "category = '{$get_cats}' AND ";
				
				}
				
				$join_category = "";
			
			}

			if ($view_template == "rss") {
				
				if( $rssmode == 'dzen' ) {
					$where_rss = ' AND e.allow_rss_dzen=1';
				} else $where_rss = '';

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason, e.allow_rss_dzen {$user_select}FROM " . PREFIX . "_post p {$join_category}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE {$where_category}approve=1 AND allow_rss=1" . $where_rss . $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];


			} else {
				
				if (isset ( $_SESSION['dle_sort_cat_'.$category_id] )) $news_sort_by = $_SESSION['dle_sort_cat_'.$category_id];
				if (isset ( $_SESSION['dle_direction_cat_'.$category_id] )) $news_direction_by = $_SESSION['dle_direction_cat_'.$category_id];

				if( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$join_category}{$extra_join}WHERE {$where_category}approve=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post p {$join_category}WHERE {$where_category}approve=1";
			}
			
		} elseif ($do == 'lastnews') {
			// ################ Show all news #################			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			if( $config['allow_alt_url'] AND $config['seo_control'] AND isset($_GET['cstart']) AND $_GET['cstart'] ) {
	
				if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR $_GET['cstart'] == 1 ) {

					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );
						
					$re_url .= "lastnews/";
						
					if(substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' AND $_GET['cstart'] != 1 ) {
						$re_url .= "page/".intval($_GET['cstart'])."/";
					}
					
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}");
					die("Redirect");
				}
			}
				
			$url_page = $config['http_home_url'] . "lastnews";
			$user_query = "do=lastnews";
			
			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?do=lastnews";
			
			if (isset ( $_SESSION['dle_sort_lastnews'] )) $news_sort_by = $_SESSION['dle_sort_lastnews']; else $news_sort_by = "date";
			if (isset ( $_SESSION['dle_direction_lastnews'] )) $news_direction_by = $_SESSION['dle_direction_lastnews']; else $news_direction_by = "DESC";

			if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

			$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}approve=1";
		
		} elseif ($do == 'tags') {
			// ################ Seach news by tags #################			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$tag = rawurldecode( $_GET['tag'] );

			$tag = htmlspecialchars ( strip_tags ( stripslashes ( trim ( $tag ) ) ), ENT_COMPAT, 'UTF-8' );
			
			$encoded_tag = rawurlencode(dle_strtolower(str_replace(array("&#039;", "&quot;", "&amp;", "&amp;frasl;"), array("'", '"', "&", "&frasl;"), $tag)));

			$tag = str_replace( "&amp;frasl;", "/", $tag );

			$_CLOUDSTAG = $tag;
			
			$url_page = $config['http_home_url'] . "tags/" . $encoded_tag;
			$user_query = "do=tags&amp;tag=" . $encoded_tag;
			
			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?do=tags&tag=" . $encoded_tag;

			if( $config['allow_alt_url'] AND $config['seo_control'] ) {

				if ( substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR strpos ($_SERVER['REQUEST_URI'], "do=tags" ) !== false OR dle_strtolower($tag)  !== $tag ) {
	
					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );
						
					$re_url .= "tags/" . $encoded_tag . "/";
						
					if( $_GET['cstart'] > 1 ) {
						$re_url .= "page/".intval($_GET['cstart'])."/";
					}
					
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}");
					die("Redirect");
				}
			}
			
			if (isset ( $_SESSION['dle_sort_tags'] )) $news_sort_by = $_SESSION['dle_sort_tags'];
			if (isset ( $_SESSION['dle_direction_tags'] )) $news_direction_by = $_SESSION['dle_direction_tags'];

			$tag = $db->safesql($tag);

			if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

			$sql_select_ids = "SELECT p.id, t.tag FROM " . PREFIX . "_post p INNER JOIN " . PREFIX . "_tags t on (t.news_id=p.id) {$cat_join}{$extra_join}WHERE {$stop_list}t.tag = '{$tag}' AND p.approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post p INNER JOIN " . PREFIX . "_tags t on (t.news_id=p.id) {$cat_join}WHERE {$stop_list}t.tag = '{$tag}' AND approve=1";

		} elseif ($do == 'xfsearch') {
			// ################ Seach news by xfields #################		
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}
			
			if (isset ( $_SESSION['dle_sort_xfsearch'] )) $news_sort_by = $_SESSION['dle_sort_xfsearch'];
			if (isset ( $_SESSION['dle_direction_xfsearch'] )) $news_direction_by = $_SESSION['dle_direction_xfsearch'];

			if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

			$xf = rawurldecode($_GET['xf']);

			if (dle_substr ( $xf, - 1, 1 ) == '/') $xf = dle_substr ( $xf, 0, - 1 );
			
			$xf = explode ( '/', $xf );
			$xfname = "";
			
			if( isset($_GET['xfname']) AND $_GET['xfname'] ) {
				$xfname =totranslit(trim($_GET['xfname']));
			} elseif(count($xf) > 1 ) {
				$xfname =totranslit(trim($xf[0]));
				unset($xf[0]);
			}

			$xf = implode(' ', $xf);
			$xf = htmlspecialchars ( strip_tags ( stripslashes ( trim ( $xf ) ) ), ENT_QUOTES, 'UTF-8' );
			$xf = str_replace( array("{", "[", ":", "&amp;frasl;"), array("&#123;", "&#91;", "&#58;", "/"), $xf );
			$xf_encoded = rawurlencode ( dle_strtolower(str_replace(array("&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;", "/"), array("'", '"', "&", "{", "[", ":", "&frasl;"), $xf ) ) );

			if($xfname) {
				
				$url_page = $config['http_home_url'] . "xfsearch/{$xfname}/{$xf_encoded}";
				$user_query = "do=xfsearch&amp;xfname={$xfname}&amp;xf={$xf_encoded}";
				
				if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?do=xfsearch&xfname={$xfname}&xf={$xf_encoded}";
				
			} else {
				
				$url_page = $config['http_home_url'] . "xfsearch/{$xf_encoded}";
				$user_query = "do=xfsearch&amp;xf={$xf_encoded}";
				
				if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?do=xfsearch&xf={$xf_encoded}";
				
				$xfields = xfieldsload();
				$xfields_name = array();
				
				foreach ( $xfields as $value ) {
					$xfields_name[] = $value[0];
				}
				
				if( in_array($xf, $xfields_name) ) {$xf .= "|";}
				
				
			}
			
			$xfname = $db->safesql($xfname);
			$xf = $db->safesql($xf);

			if ( $xfname ) {
				
				if( $config['allow_alt_url'] AND $config['seo_control'] ) {

					if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR strpos ($_SERVER['REQUEST_URI'], "do=xfsearch" ) !== false OR dle_strtolower($xf) !== $xf) {
							
						$re_url = $url_page . "/";
							
						if( $_GET['cstart'] > 1 ) {
							$re_url .= "page/".intval($_GET['cstart'])."/";
						}
						
						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}");
						die("Redirect");
					}
				}

				$sql_select_ids = "SELECT p.id, xf.tagvalue FROM " . PREFIX . "_post p INNER JOIN " . PREFIX . "_xfsearch xf on (xf.news_id=p.id) {$cat_join}{$extra_join}WHERE {$stop_list}xf.tagname = '{$xfname}' AND xf.tagvalue='{$xf}' AND p.approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count  = "SELECT COUNT(*) as count FROM " . PREFIX . "_post p INNER JOIN " . PREFIX . "_xfsearch xf on (xf.news_id=p.id) {$cat_join}WHERE {$stop_list}xf.tagname = '{$xfname}' AND xf.tagvalue='{$xf}' AND approve=1";
		
			} else {
				
				if(!$xf) {
					
					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );
						
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}");
					die("Redirect");
				}

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}p.xfields LIKE '%{$xf}%' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}xfields LIKE '%{$xf}%' AND approve=1";
		
				$xf = str_replace ( '|', '', $xf );
			}
		
		} elseif ($subaction == 'userinfo') {
			// ################ show user profile #################
			if ($cstart) {
				
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			
			}
			
			$url_page = $config['http_home_url'] . "user/" . urlencode ( $user );
			$user_query = "subaction=userinfo&amp;user=" . urlencode ( $user );
			
			if ($member_id['name'] == $user OR $user_group[$member_id['user_group']]['allow_all_edit']) {
				if (isset ( $_SESSION['dle_sort_userinfo'] )) $news_sort_by = $_SESSION['dle_sort_userinfo'];
				if (isset ( $_SESSION['dle_direction_userinfo'] )) $news_direction_by = $_SESSION['dle_direction_userinfo'];

				if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$extra_join}WHERE autor = '{$user}' AND approve=0 ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE autor = '{$user}' AND approve=0";
				$where_date = "";
				
				if( !$config['profile_news'] ) {
					$allow_active_news = false;
					$news_found = false;		
				}

			} else {
				$allow_active_news = false;
				$news_found = false;
			}
			
			$config['allow_cache'] = false;
			
		} elseif ($subaction == 'allnews') {
			// ################ show all news by user #################
			if ($cstart) {
				
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			
			}
			
			$url_page = $config['http_home_url'] . "user/" . urlencode ( $user ) . "/news";
			$user_query = "subaction=allnews&amp;user=" . urlencode ( $user );
			
			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?subaction=allnews&user=" . urlencode ( $user );
			
			if ($view_template == "rss") {

				if ($rssmode == 'dzen') {
					$where_rss = ' AND e.allow_rss_dzen=1';
				} else $where_rss = '';

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason, e.allow_rss_dzen {$user_select}FROM " . PREFIX . "_post p {$cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE {$stop_list}p.autor = '{$user}' AND p.approve=1 AND e.allow_rss=1" . $where_rss. $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];
			
			} else {
				
				if (isset ( $_SESSION['dle_sort_allnews'] )) $news_sort_by = $_SESSION['dle_sort_allnews'];
				if (isset ( $_SESSION['dle_direction_allnews'] )) $news_direction_by = $_SESSION['dle_direction_allnews'];

				if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}autor = '$user' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}autor = '$user' AND approve=1";
			}
		
		} elseif ($subaction == 'newposts') {
			// ################ show all unread news #################
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}
			
			$url_page = $config['http_home_url'] . "newposts";
			$user_query = "subaction=newposts";
			
			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?subaction=newposts";
			
			$thistime = date ( "Y-m-d H:i:s", $_TIME );
			
			if (isset ( $_SESSION['member_lasttime'] )) {
				$lasttime = date ( "Y-m-d H:i:s", $_SESSION['member_lasttime'] );
			} else {
				$lasttime = date ( "Y-m-d H:i:s", (time () - (3600 * 4)) );
			}
			
			if (isset ( $_SESSION['dle_sort_newposts'] )) $news_sort_by = $_SESSION['dle_sort_newposts'];
			if (isset ( $_SESSION['dle_direction_newposts'] )) $news_direction_by = $_SESSION['dle_direction_newposts'];

			if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

			$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}approve=1 AND date between '$lasttime' and '$thistime' order by " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}approve=1 AND date between '$lasttime' and '$thistime'";
			$where_date = "";
			
			$config['allow_cache'] = false;
			
		} elseif ( isset ($_GET['catalog']) ) {
			
			// ################ show by catalog #################
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			if( $config['allow_alt_url'] AND $config['seo_control']) {

				if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR !$catalog) {
					
					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );
					
					if( $catalog ) {
						$re_url .= "catalog/".urlencode ( $catalog )."/";
					}
					
					if( $_GET['cstart'] > 1 ) {
						$re_url .= "page/".intval($_GET['cstart'])."/";
					}
					
					
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}");
					die("Redirect");
				}
			}
				
			$url_page = $config['http_home_url'] . "catalog/" . urlencode ( $catalog );
			$user_query = "catalog=" . urlencode ( $catalog );

			if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?catalog=" . urlencode ( $catalog );

			$news_sort_by = $config['catalog_sort'] ? $config['catalog_sort'] : "date";
			$news_direction_by = $config['catalog_msort'] ? $config['catalog_msort'] : "DESC";
			
			if (isset ( $_SESSION['dle_sort_catalog'] )) $news_sort_by = $_SESSION['dle_sort_catalog'];
			if (isset ( $_SESSION['dle_direction_catalog'] )) $news_direction_by = $_SESSION['dle_direction_catalog'];
			if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

			$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}symbol = '$catalog' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}symbol = '$catalog' AND approve=1";

		} else {

			// ################ show main page #################
			if ($year == '' AND $month == '' AND $day == '' AND !$newsid) {

				$canonical = $config['http_home_url'];

				if( $config['start_site'] == 2 AND $view_template != "rss") {
					
					break;				
				}
				
				if( $config['allow_alt_url'] AND $config['seo_control'] AND isset($_GET['cstart']) AND $_GET['cstart'] ) {
	
					if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR $_GET['cstart'] == 1 ) {
						
						$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
						$re_url = reset ( $re_url );
						
						if(substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' AND $_GET['cstart'] != 1 ) {
							$re_url .= "page/".intval($_GET['cstart'])."/";
						}
					
						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}");
						die("Redirect");
					}
				}
			
				if ($cstart) {
					
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];		
				}
			
				$url_page = substr ( $config['http_home_url'], 0, strlen ( $config['http_home_url'] ) - 1 );
				$user_query = "";
				
				if ($view_template == "rss") {
	
					$not_allow_cats = array();
					
					foreach($cat_info as $value) {
						if( !$value['allow_rss'] ) $not_allow_cats[] = $value['id'];
					}
					
					if( count($not_allow_cats) ) {

						if ($config['allow_multi_category']) {
							
							$not_allow_cats = "id NOT IN ( SELECT DISTINCT " . PREFIX . "_post_extras_cats.news_id FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") ) AND ";
						
						} else {
							
							$not_allow_cats = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "') AND ";
						
						}
						
					} else $not_allow_cats = "";
					
					$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason, e.allow_rss_dzen {$user_select}FROM " . PREFIX . "_post p {$cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE {$not_allow_cats}{$stop_list}p.approve=1 AND e.allow_rss=1";
					
					if ($config['rss_mtype']) {
						
						$sql_select .= " AND p.allow_main=1";
					
					}

					if ($rssmode == 'dzen') {
						$sql_select .= " AND e.allow_rss_dzen=1";
					}

					$sql_select .= $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];
				
				} else {
					
					if (isset ( $_SESSION['dle_sort_main'] )) $news_sort_by = $_SESSION['dle_sort_main'];
					if (isset ( $_SESSION['dle_direction_main'] )) $news_direction_by = $_SESSION['dle_direction_main'];
					if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

					$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

					$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
					$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}approve=1 AND allow_main=1";
				
				}
			}
	
			// ################ Show news by year #################
			if ($year != '' and $month == '' and $day == '') {
				if ($cstart) {
					
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}
				
				if( $config['allow_alt_url'] AND $config['seo_control']) {

					if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100) {
						
						$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
						$re_url = reset ( $re_url );
						
						if (intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100) {
							$year= date( 'Y', $_TIME );
						}
						
						$re_url .= $year."/";
						
						if( $_GET['cstart'] > 1 ) {
							$re_url .= "page/".intval($_GET['cstart'])."/";
						}
						
						
						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}");
						die("Redirect");
					}
				}
				
				$url_page = $config['http_home_url'] . $year;
				$user_query = "year=" . $year;
				
				if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?year=" . $year;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];

				if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}date >= '{$year}-01-01'AND date < '{$year}-01-01' + INTERVAL 1 YEAR AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}where {$stop_list}date >= '{$year}-01-01'AND date < '{$year}-01-01' + INTERVAL 1 YEAR AND approve=1";
			}
			
			// ################ Show news by month #################
			if ($year != '' and $month != '' and $day == '') {
				if ($cstart) {
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}
				
				if( $config['allow_alt_url'] AND $config['seo_control']) {

					if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100 OR intval($_GET['month']) < 1 OR intval($_GET['month']) > 12) {
						
						$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
						$re_url = reset ( $re_url );
						
						if (intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100) {
							$year= date( 'Y', $_TIME );
						}
						
						$re_url .= $year."/";
						
						if (intval($_GET['month']) < 1 OR intval($_GET['month']) > 12) {
							$month= date( 'm', $_TIME );
						}
						
						$re_url .= $month."/";
						
						if( $_GET['cstart'] > 1 ) {
							$re_url .= "page/".intval($_GET['cstart'])."/";
						}

						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}");
						die("Redirect");
					}
				}

				$url_page = $config['http_home_url'] . $year . "/" . $month;
				$user_query = "year=" . $year . "&amp;month=" . $month;
				
				if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?year=" . $year . "&month=" . $month;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];
				if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}date >= '{$year}-{$month}-01'AND date < '{$year}-{$month}-01' + INTERVAL 1 MONTH AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}where {$stop_list}date >= '{$year}-{$month}-01'AND date < '{$year}-{$month}-01' + INTERVAL 1 MONTH AND approve=1";
			}
		
			// ################ Show news by day #################

			if ($year != '' and $month != '' and $day != '' and $subaction == '') {
				if ($cstart) {
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}

				if( $config['allow_alt_url'] AND $config['seo_control']) {

					if (substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' OR (isset($_GET['cstart']) AND $_GET['cstart'] == 1) OR substr ( $_SERVER['REQUEST_URI'], - 2 ) == '//' OR intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100 OR intval($_GET['month']) < 1 OR intval($_GET['month']) > 12 OR intval($_GET['day']) < 1 OR intval($_GET['day']) > 31) {
						
						$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
						$re_url = reset ( $re_url );
						
						if (intval($_GET['year']) < 1970 OR intval($_GET['year']) > 2100) {
							$year= date( 'Y', $_TIME );
						}
						
						$re_url .= $year."/";
						
						if (intval($_GET['month']) < 1 OR intval($_GET['month']) > 12) {
							$month= date( 'm', $_TIME );
						}
						
						$re_url .= $month."/";
						
						if (intval($_GET['day']) < 1 OR intval($_GET['day']) > 31) {
							$day= date( 'd', $_TIME );
						}
						
						$re_url .= $day."/";
						
						if( $_GET['cstart'] > 1 ) {
							$re_url .= "page/".intval($_GET['cstart'])."/";
						}

						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}");
						die("Redirect");
					}
				}
				
				$url_page = $config['http_home_url'] . $year . "/" . $month . "/" . $day;
				$user_query = "year=" . $year . "&amp;month=" . $month . "&amp;day=" . $day;
				
				if( $config['allow_alt_url'] ) $canonical = $url_page . "/"; else $canonical = $PHP_SELF."?year=" . $year . "&month=" . $month . "&day=" . $day;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];
				if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

				$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR AND approve=1";
		
			}
			
			// ################ Full News #################
			if ($subaction != '' OR $newsid) {
				
				if ( !$newsid ) $sql_news = "SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.allow_br, p.symbol, p.tags, p.metatitle, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.related_ids, e.access, e.editdate, e.editor, e.reason, e.user_id, e.disable_search, e.need_pass, e.allow_rss, e.allow_rss_dzen, e.allowed_country, e.not_allowed_country {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE alt_name ='{$news_name}' AND date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR LIMIT 1";
				else $sql_news = "SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.allow_br, p.symbol, p.tags, p.metatitle, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.related_ids, e.access, e.editdate, e.editor, e.reason, e.user_id, e.disable_search, e.need_pass, e.allow_rss, e.allow_rss_dzen, e.allowed_country, e.not_allowed_country {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}WHERE  p.id = '{$newsid}'";

				if ($subaction == '') $subaction = "showfull";
			}
		}
		
		if ( ($subaction == "showfull" OR $subaction == "addcomment") AND ((!isset($_GET['category']) OR !$_GET['category'])) ) {
			
			$allow_active_news = false;
			
			//####################################################################################################################
			//          Add a comment to the database
			//####################################################################################################################
			if (isset( $_POST['subaction'] ) AND $_POST['subaction'] == "addcomment") {
				
				$allow_add_comment = true;
				$allow_comments = true;
				$ajax_adds = false;
				
				include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/addcomments.php'));
			}
			//####################################################################################################################
			//         Show the full news
			//####################################################################################################################
			if ($subaction == "showfull") {
				$allow_comments = true;
			
				include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/show.full.php'));
			}
		
		} else {
			
			//####################################################################################################################
			//         Viewing user profile
			//####################################################################################################################
			if ($subaction == 'userinfo') {
				
				$allow_userinfo = true;
				include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/profile.php'));
			
			}
			
			//####################################################################################################################
			//         Viewing short news
			//####################################################################################################################
			
			$cache_prefix = "content_".$dle_module;

			$_SESSION['referrer'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );
			
			if ($catalog != "") {
				
				$cache_prefix .= "_catalog_" . $catalog;
			
			} elseif ($do == "lastnews") {
				
				$cache_prefix .= "_lastnews";
			
			} elseif ($subaction == 'allnews') {

				$cache_prefix .= "_allnews_". $user;

			} elseif ($do == 'tags') {

				$cache_prefix .= "_tagscl_". $tag;

			} elseif ($do == 'xfsearch') {
				
				if($xfname) $cache_prefix .= "_xfsearch_" . $xfname . "_" . $xf;
				else $cache_prefix .= "_xfsearch_". $xf;

			} else {
				
				$cache_prefix .= "_";
				
				if ($month) $cache_prefix .= "month_" . $month;
				if ($year) $cache_prefix .= "year_" . $year;
				if ($day) $cache_prefix .= "day_" . $day;
				if ($category) $cache_prefix .= "category_" . $category_id;
			}
			
			$cache_prefix .= "_tempate_" . $config['skin'];

			if ($view_template == "rss") {

				if ($catalog) $active = dle_cache ( "rss", $rssmode.$catalog, false );
				else $active = dle_cache ( "rss", $rssmode.$category_id, false );
				
				if( $active ) {
					$active = json_decode($active, true);
				}
					
			} else {
				
				if ($is_logged and ($user_group[$member_id['user_group']]['allow_edit'] and ! $user_group[$member_id['user_group']]['allow_all_edit'])) $config['allow_cache'] = false;
				if (isset($_SESSION['dle_no_cache']) AND $_SESSION['dle_no_cache']) $config['allow_cache'] = false;
				if ($cstart) $cache_id = ($cstart / $config['news_number']) + 1;
				else $cache_id = 1;
				
				$config['max_cache_pages'] = intval($config['max_cache_pages']);
				if($config['max_cache_pages'] < 3) $config['max_cache_pages'] = 3;

				if ($config['allow_cache'] AND $cache_id <= $config['max_cache_pages']) {
					$active = dle_cache( "news", $cache_id . $cache_prefix, true );
					
					if( $active ) {
						$active = json_decode($active, true);
					}
	
					$short_news_cache = true;
					
				} else {
					
					$active = false;
					$short_news_cache = false;
					
				}
			
			}
			
			if ( is_array($active) ) {

				if( isset( $active['content'] ) ) {
					$tpl->result['content'] .= $active['content'];
				}
				
				if( isset($active['navigation']) ) {
					
					$tpl->result['navigation'] = $active['navigation'];
					
				} else $tpl->result['navigation'] = '';
				
				if( isset( $active['last-modified'] ) ) {
					
					if( $active['last-modified'] > $_DOCUMENT_DATE ) {
						$_DOCUMENT_DATE = $active['last-modified'];
					}
					
				}

				if( isset( $active['showed_news_ids'] ) AND is_array($active['showed_news_ids']) ) {
					$showed_news_ids = array_merge($showed_news_ids, $active['showed_news_ids']);
				}

				if ($do == 'xfsearch' AND isset($active['xf']) AND $active['xf'] ) {
					$xf = $active['xf'];
				}

				if ($do == 'tags' and isset($active['tag']) and $active['tag']) {
					$_CLOUDSTAG = $tag = $active['tag'];
				}
	
				if( isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] ){
					if( isset($active['description']) AND  $active['description'] ) $metatags['description'] = $active['description'];
					if( isset($active['keywords']) AND  $active['keywords'] ) $metatags['keywords'] = $active['keywords'];
				}
				
				$active = null;
				$news_found = true;
				if ( $user_group[$member_id['user_group']]['allow_edit'] OR $user_group[$member_id['user_group']]['allow_all_edit'] ) $allow_comments_ajax = true;
				else $allow_comments_ajax = false;

			} else {

				if(!$sql_select) {

					if ( !in_array($news_sort_by, array('rating', 'news_read', 'editdate')) ) $extra_join = '';

					$sql_select_ids = "SELECT p.id FROM " . PREFIX . "_post p {$cat_join}{$extra_join}WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

					$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";
					$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post {$cat_join_count}WHERE {$stop_list}approve=1 AND allow_main=1";

				}

				if( $sql_select_ids ) {

					$sql_select_ids = $db->super_query($sql_select_ids, true);

					if (count($sql_select_ids)) {

						$first_post = true;
						$temp_arr = array();
						foreach ($sql_select_ids as $value) {
							$temp_arr[] = $value['id'];

							if( isset($value['tagvalue']) AND $value['tagvalue'] AND $first_post ) {
								$xf = $value['tagvalue'];
								$first_post = false;
							}

							if (isset($value['tag']) and $value['tag'] and $first_post) {
								$_CLOUDSTAG = $tag = $value['tag'];
								$first_post = false;
							}

						}

						$sql_select_ids = implode(',', $temp_arr);

					} else { $sql_select_ids = '0'; $allow_active_news = false; $news_found = false; }

					$sql_select .= "WHERE p.id IN ({$sql_select_ids}) ORDER BY FIND_IN_SET(p.id, '" . $sql_select_ids . "')";
					
				}

				include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/show.short.php'));

				if( isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] ){
					if( isset($page_description) AND $page_description ) $metatags['description'] = $page_description; else $page_description = '';
					if( isset($page_keywords) AND $page_keywords ) $metatags['keywords'] = $page_keywords; else $page_keywords = '';
				}
				
				if ($config['files_allow']) if (strpos ( $tpl->result['content'], "[attachment=" ) !== false) {
					$tpl->result['content'] = show_attach ( $tpl->result['content'], $attachments );
				}
				
				if ($view_template == "rss" AND $news_found) {
					
					if ($catalog) create_cache ( "rss", json_encode( array('content' => $tpl->result['content'] ) , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $rssmode . $catalog, false );
					else create_cache ( "rss", json_encode( array('content' => $tpl->result['content'] ) , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $rssmode . $category_id, false );
				
				} elseif ($news_found AND $cache_id <= $config['max_cache_pages'] ) {

					$cache_content = array('content' => $tpl->result['content'], 'navigation' => $tpl->result['navigation'], 'description' => $page_description, 'keywords' => $page_keywords, 'showed_news_ids' => $showed_news_ids, 'last-modified' => $_DOCUMENT_DATE);
					
					if (isset($xf) AND $xf) $cache_content['xf'] = $xf;

					if (isset($_CLOUDSTAG) AND $_CLOUDSTAG) $cache_content['tag'] = $_CLOUDSTAG;

					create_cache ( "news", json_encode( $cache_content , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $cache_id . $cache_prefix, true );

				}

			}
			
			if($tpl->result['content'] AND $canonical AND isset($_GET['cstart']) AND intval($_GET['cstart']) AND intval($_GET['cstart']) != 1 ) {
					
					if( $config['allow_alt_url'] ) {
						
						$canonical .= "page/".intval($_GET['cstart'])."/";
						
					} else {
						
						if ($user_query) {
							
							$canonical = "{$PHP_SELF}?cstart=".intval($_GET['cstart'])."&".str_replace('&amp;', '&', $user_query);
							
						} else $canonical = "{$PHP_SELF}?cstart=".intval($_GET['cstart']);
					}
					
			}
			
		
		}

}

/*
=====================================================
 The output of the page header
=====================================================
*/
$titl_e = '';
$nam_e = '';
$rss_url = '';
$rssdzen_url = '';
$rss_title = '';


if ($do == "cat" AND $category AND !$subaction) {
	
	if( isset($cat_info[$category_id]['descr']) AND $cat_info[$category_id]['descr'] ){
		
		if( !isset($_GET['cstart']) OR ( isset($_GET['cstart']) AND intval($_GET['cstart']) < 2 ) ) {
			$metatags['description'] = $cat_info[$category_id]['descr'];
		}
	}
	
	if( isset($cat_info[$category_id]['keywords']) AND $cat_info[$category_id]['keywords'] ){
		if( !isset($_GET['cstart']) OR ( isset($_GET['cstart']) AND intval($_GET['cstart']) < 2 ) ) {
			$metatags['keywords'] = $cat_info[$category_id]['keywords'];
		}
	}	


	if (isset( $cat_info[$category_id]['metatitle'] ) AND $cat_info[$category_id]['metatitle']) {
		$metatags['header_title'] = $cat_info[$category_id]['metatitle'];
	} else {
		$nam_e = isset($cat_info[$category_id]['name']) ? stripslashes ( $cat_info[$category_id]['name'] ) : '';
	}
	
	if ($config['allow_alt_url'] ) {
		$rss_url = $url_page . "/" . "rss.xml";
		$rssdzen_url = $url_page . "/" . "rssdzen.xml";
	} else {
		$rss_url = $PHP_SELF . "?mod=rss&do=cat&category=" . get_url ( $category_id );
		$rssdzen_url = $PHP_SELF . "?mod=rss&rssmode=dzen&do=cat&category=" . get_url ( $category_id );

	}

} elseif ($subaction == 'userinfo') {

	$nam_e = $user;
	
	if ($config['allow_alt_url'] ) {
		$rss_url = $url_page . "/" . "rss.xml";
		$rssdzen_url = $url_page . "/" . "rssdzen.xml";
	} else {
		$rss_url = $PHP_SELF . "?mod=rss&subaction=allnews&user=" . urlencode ( $user );
		$rssdzen_url = $PHP_SELF . "?mod=rss&rssmode=dzen&subaction=allnews&user=" . urlencode($user);
	}

} elseif ($subaction == 'allnews') {
	$nam_e = $lang['show_user_news'] . ' ' . $user;
	
	if ($config['allow_alt_url']) {
		$rss_url = $config['http_home_url'] . "user/" . urlencode ( $user ) . "/" . "rss.xml";
		$rssdzen_url = $config['http_home_url'] . "user/" . urlencode($user) . "/" . "rssdzen.xml";
	} else {
		$rss_url = $PHP_SELF . "?mod=rss&subaction=allnews&user=" . urlencode ( $user );
		$rssdzen_url = $PHP_SELF . "?mod=rss&rssmode=dzen&subaction=allnews&user=" . urlencode($user);
	}

} elseif ($subaction == 'newposts') $nam_e = $lang['title_new'];
elseif ($do == 'stats') $nam_e = $lang['title_stats'];
elseif ($do == 'addnews') { if( isset($_REQUEST['id']) AND intval($_REQUEST['id']) ) $nam_e = $lang['title_editnews']; else $nam_e = $lang['title_addnews']; }
elseif ($do == 'register') $nam_e = $lang['title_register'];
elseif ($do == 'favorites') $nam_e = $lang['title_fav'];
elseif ($do == 'pm') $nam_e = $lang['title_pm'];
elseif ($do == 'feedback') $nam_e = $lang['title_feed'];
elseif ($do == 'lastcomments') $nam_e = $lang['title_last'];
elseif ($do == 'lostpassword') $nam_e = $lang['title_lost'];
elseif ($do == 'search') $nam_e = $lang['title_search'];
elseif ($do == 'static' AND isset($static_descr) AND $static_descr) $titl_e = $static_descr;
elseif ($do == 'lastnews') $nam_e = $lang['last_news'];
elseif ($do == 'alltags') $nam_e = $lang['tag_cloud'];
elseif ($do == 'rules') $nam_e = $lang['rules_page'];
elseif ($do == 'tags') $nam_e = stripslashes($tag);
elseif ($do == 'xfsearch') $nam_e = $xf;
elseif ($catalog) { 
	$nam_e = $lang['title_catalog'] . ' &raquo; ' . $catalog;

	if ($config['allow_alt_url']) {

		$rss_url = $config['http_home_url'] . "catalog/" . urlencode ( $catalog ) . "/" . "rss.xml";
		$rssdzen_url = $config['http_home_url'] . "catalog/" . urlencode($catalog) . "/" . "rssdzen.xml";

	} else {
		$rss_url = $PHP_SELF . "?mod=rss&catalog=" . urlencode ( $catalog );
		$rssdzen_url = $PHP_SELF . "?mod=rss&rssmode=dzen&catalog=" . urlencode($catalog);
	}

} else {

	if ($year != '' AND $month == '' AND $day == '') $nam_e = $lang['title_date'] . ' ' . $year . ' ' . $lang['title_year'];
	if ($year != '' AND $month != '' AND $day == '') $nam_e = $lang['title_date'] . ' ' . $r[$month - 1] . ' ' . $year . ' ' . $lang['title_year1'];
	if ($year != '' AND $month != '' AND $day != '' and $subaction == '') $nam_e = $lang['title_date'] . ' ' . $day . '.' . $month . '.' . $year;
	if (($subaction OR $newsid) AND $news_found) $titl_e = $metatags['title'];

}

if ( ( isset($_GET['cstart']) AND intval($_GET['cstart']) > 1 ) OR (isset($_GET['news_page']) AND intval($_GET['news_page']) > 1) ){

	if ( isset($_GET['cstart']) AND intval($_GET['cstart']) > 1 ) $page_extra = ' &raquo; '.$lang['news_site'].' '.intval($_GET['cstart']);
	else $page_extra = ' &raquo; '.$lang['news_site'].' '.intval($_GET['news_page']);

} else $page_extra = '';



if ($nam_e) {

	$metatags['title'] = $nam_e . $page_extra . ' &raquo; ' . $metatags['title'];
	$rss_title = $metatags['title'];

} elseif ($titl_e) {

	if ($config['start_site'] == 3 AND $dle_module == "main" AND (!isset($_GET['mod']) OR (isset($_GET['mod']) AND $_GET['mod'] != "rss"))) {

		$metatags['title'] = $config['home_title'];

	} else $metatags['title'] = $titl_e . $page_extra . ' &raquo; ' . $config['home_title'];

} else $metatags['title'] .= $page_extra;

if ( $metatags['header_title'] ) $metatags['title'] = stripslashes($metatags['header_title'].$page_extra);

if ( !$rss_url ) {
	
	if ($config['allow_alt_url']) {

		$rss_url = $config['http_home_url'] . "rss.xml";
		$rssdzen_url = $config['http_home_url'] . "rssdzen.xml";

	} else {
		$rss_url = $PHP_SELF . "?mod=rss";
		$rssdzen_url = $PHP_SELF . "?mod=rss&rssmode=dzen";
	}
	
	$rss_title = $config['home_title'];
}

if( $config['allow_own_meta'] ) {
	
	if(isset($custom_metatags['simple']) AND is_array($custom_metatags['simple']) AND count($custom_metatags['simple']) AND isset($custom_metatags['simple'][$r_uri]) AND $custom_metatags['simple'][$r_uri] ) {
		if( $custom_metatags['simple'][$r_uri]['title'] ) $metatags['title'] = $custom_metatags['simple'][$r_uri]['title'];
		if( $custom_metatags['simple'][$r_uri]['description'] ) $metatags['description'] = $custom_metatags['simple'][$r_uri]['description'];
		if( $custom_metatags['simple'][$r_uri]['keywords'] ) $metatags['keywords'] = $custom_metatags['simple'][$r_uri]['keywords'];
		if( $custom_metatags['simple'][$r_uri]['robots'] ) $metatags['robots'] = $custom_metatags['simple'][$r_uri]['robots'];
	}
	
	if(isset($custom_metatags['regex']) AND is_array($custom_metatags['regex']) AND count($custom_metatags['regex'])) {	
		foreach ($custom_metatags['regex'] as $key => $value) {
			if(preg_match($key, $r_uri)){
				if( $value['title'] ) $metatags['title'] = $value['title'];
				if( $value['description'] ) $metatags['description'] = $value['description'];
				if( $value['keywords'] ) $metatags['keywords'] = $value['keywords'];
				if( $value['robots'] ) $metatags['robots'] = $value['robots'];
		    }
		}
	}

}

$meta = DLESEO::MetaTags( 'utf-8' );

if( !isset($social_tags['image']) OR !$social_tags['image'] ) {
	$meta->twitter('card', 'summary');
}

$meta->og('type', 'article');
$meta->og('site_name', $config['home_title']);
$meta->title($metatags['title']);

if( isset($social_tags['description']) AND $social_tags['description'] ) {
	$meta->meta('description', $metatags['description'] );
} else {
	$meta->description($metatags['description']);
}

$meta->meta('keywords', $metatags['keywords']);
$meta->meta('generator', "DataLife Engine (https://dle-news.ru)");

		
if ( $disable_index ) $metatags['robots'] = "noindex,nofollow";

if ( isset($metatags['robots']) ) {
	
	$meta->robots($metatags['robots']);
	
}

if ( count($social_tags) ) {

	foreach ($social_tags as $key => $value) {

		if( $key == "news_keywords" ) {
			$meta->meta('news_keywords', $value);
		} elseif ($key == "description") {
			$meta->og('description', $value);
			$meta->twitter('description', $value);
		} elseif ($key == "image") {
			$meta->image($value);
		} elseif ($key == "url") {
			$meta->url($value);
		} else {
			$meta->og($key, $value);
		}

	}
}

if ($canonical) {
	
	if (strpos($canonical, "//") === 0) $canonical = "https:".$canonical;
	elseif (strpos($canonical, "/") === 0) $canonical = "https://".$_SERVER['HTTP_HOST'].$canonical;

	if( stripos( $canonical, 'http://' ) !== false ) {
		
		if ( isSSL() OR $config['only_ssl'] ) {
			$canonical = str_replace( "http://", "https://", $canonical );
		}
		
	}
	
	$meta->canonical($canonical);

}

if ($config['allow_rss']) {
	
	$meta->push('link', [
			'rel' => 'alternate',
			'type' => 'application/rss+xml',
			'title' => $rss_title.' RSS',
			'href' => $rss_url
		]);

}

if ($config['allow_yandex_dzen']) {

	$meta->push('link', [
		'rel' => 'alternate',
		'type' => 'application/rss+xml',
		'title' => $rss_title . ' RSS Dzen',
		'href' => $rssdzen_url
	]);
}

$meta->push('link', [
	'rel' => 'preconnect',
	'href' => $config['http_home_url'],
	'fetchpriority' => 'high'
]);

$metatags = (string) $meta;
unset($meta);

/*
=====================================================
 speedbar creation
=====================================================
*/

if ($config['speedbar'] AND !$view_template ) {
	
	$elements	= [];
	$position	= 1;
	
	$elements[] = array(
		'@type'		=> "ListItem",
		'position'	=> $position,
		'item'		=> array(
			'@id'	=> $config['http_home_url'],
			'name'	=> $config['short_title'],
		)
	);
	$position++;
	
	$s_navigation = "<a href=\"{$config['http_home_url']}\">" . $config['short_title'] . "</a>";

	if( $config['start_site'] == 3 AND $dle_module == "main") $titl_e = "";

	if (intval($category_id)){
		
		if($titl_e OR (isset($_GET['cstart']) AND intval($_GET['cstart']) > 1) ) {
			$last_link = true;
		} else $last_link = false;
		
		$s_navigation .= "{$config['speedbar_separator']}" . get_breadcrumbcategories ( intval($category_id), $config['speedbar_separator'], $last_link );
		
	} elseif ($do == 'tags') {
		
		$elements[] = array(
			'@type'		=> "ListItem",
			'position'	=> $position,
			'item'		=> array(
				'@id'	=> $config['http_home_url'].'tags/',
				'name'	=> $lang['tag_cloud'],
			)
		);
		$position++;
	
		if ($config['allow_alt_url']){
			
			$uri = $url_page . "/";
			$s_navigation .= "{$config['speedbar_separator']}<a href=\"{$config['http_home_url']}tags/\">{$lang['tag_cloud']}</a>";

		} else {

			$uri = $PHP_SELF."?do=tags&tag=" . $encoded_tag;
			$s_navigation .= "{$config['speedbar_separator']}<a href=\"?do=tags\">{$lang['tag_cloud']}</a>";

		}

		if ( isset($_GET['cstart']) AND intval($_GET['cstart']) > 1 ){
			$s_navigation .= $config['speedbar_separator']."<a href=\"{$uri}\">{$tag}</a>";
		} else $s_navigation .= $config['speedbar_separator'].$tag;
		
		$elements[] = array(
			'@type'		=> "ListItem",
			'position'	=> $position,
			'item'		=> array(
				'@id'	=> $uri,
				'name'	=> $tag,
			)
		);
		$position++;
	
	} elseif ($nam_e) {
		
		$s_navigation .= "{$config['speedbar_separator']}" . $nam_e;
		
		if ($canonical) {
			$elements[] = array(
				'@type'		=> "ListItem",
				'position'	=> $position,
				'item'		=> array(
					'@id'	=> $canonical,
					'name'	=> $nam_e,
				)
			);
			$position++;
		}
	}

	if ($titl_e) {
		
		$s_navigation .= "{$config['speedbar_separator']}" . $titl_e;
		
		if ($canonical) {
			$elements[] = array(
				'@type'		=> "ListItem",
				'position'	=> $position,
				'item'		=> array(
					'@id'	=> $canonical,
					'name'	=> $titl_e,
				)
			);
			$position++;
		}
		
	} else {

		if ( isset($_GET['cstart']) AND intval($_GET['cstart']) > 1 ){
		
			$page_extra = "{$config['speedbar_separator']}".$lang['news_site']." ".intval($_GET['cstart']);
			
			if ($canonical) {
				$elements[] = array(
					'@type'		=> "ListItem",
					'position'	=> $position,
					'item'		=> array(
						'@id'	=> $canonical,
						'name'	=> $lang['news_site']." ".intval($_GET['cstart']),
					)
				);
				$position++;
			}
		
		} else $page_extra = '';

		$s_navigation .= $page_extra;

	}
	
	if ( is_array($elements) AND count($elements) > 1) {
		DLESEO::AddSchema( DLESEO::Thing('BreadcrumbList', array("itemListElement" => $elements) ) );
	}
	
	$tpl->load_template ( 'speedbar.tpl' );
	$tpl->set ( '{speedbar}', stripslashes ( $s_navigation ) );
	$tpl->compile ( 'speedbar' );
	$tpl->clear ();

}
