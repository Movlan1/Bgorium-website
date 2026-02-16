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
 File: addnews.php
-----------------------------------------------------
 Use: Add news
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$allow_addnews = true;
$parse = new ParseFilter();

$id = (isset( $_REQUEST['id'] )) ? intval( $_REQUEST['id'] ) : 0;
$found = false;
$approve_find = '';

if( !$user_group[$member_id['user_group']]['allow_all_edit'] AND !$user_group[$member_id['user_group']]['allow_edit'] ) {
	$approve_find = " AND approve = '0'";
}

if( $config['allow_alt_url'] ) $canonical = $config['http_home_url'] . "addnews.html"; else $canonical = $PHP_SELF."?do=addnews";


if( $id AND $is_logged AND $user_group[$member_id['user_group']]['allow_adds'] ) {

	$foundrow = $db->super_query("SELECT id, autor, category, xfields, tags FROM " . PREFIX . "_post WHERE id = '{$id}'{$approve_find}" );

	if( isset($foundrow['id']) AND $id == $foundrow['id'] AND ($member_id['name'] == $foundrow['autor'] OR $user_group[$member_id['user_group']]['allow_all_edit']) ) $found = true;
	else $found = false;
}

if( $id AND !$found){

	if( $approve_find AND $is_logged) $lang['add_err_9'] = $lang['add_err_10'];
	else $lang['add_err_9'] = $lang['add_err_12'];
	$allow_addnews = false;
}

if( $config['max_moderation'] AND !$user_group[$member_id['user_group']]['moderation'] AND !$found ) {
	
	$stats_approve = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve != '1'" );
	$stats_approve = $stats_approve['count'];
	
	if( $stats_approve >= $config['max_moderation'] ) $allow_addnews = false;

}

if ($is_logged AND $config['news_restricted'] AND (($_TIME - $member_id['reg_date']) < ($config['news_restricted'] * 86400)) ) {
	$lang['add_err_9'] = str_replace( '{days}', intval($config['news_restricted']), $lang['news_info_7'] );
	$allow_addnews = false;
}

if(isset($member_id['restricted']) AND $member_id['restricted'] AND $member_id['restricted_days'] AND $member_id['restricted_date'] < $_TIME ) {
	
	$member_id['restricted'] = 0;
	$db->query( "UPDATE LOW_PRIORITY " . USERPREFIX . "_users SET restricted='0', restricted_days='0', restricted_date='' WHERE user_id='{$member_id['user_id']}'" );

}

if(isset($member_id['restricted']) AND ($member_id['restricted'] == 1 OR $member_id['restricted'] == 3) ) {
	
	if( $member_id['restricted_days'] ) {
		
		$lang['news_info_4'] = str_replace( '{date}', langdate( "j F Y H:i", $member_id['restricted_date'] ), $lang['news_info_4'] );
		$lang['add_err_9'] = $lang['news_info_4'];
	
	} else {
		
		$lang['add_err_9'] = $lang['news_info_5'];
	
	}
	
	$allow_addnews = false;

}

if( !$allow_addnews ) {
	
	msgbox( $lang['all_info'], $lang['add_err_9'] . "<br><br><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );

} else {
	
	if( isset( $_REQUEST['mod'] ) AND $_REQUEST['mod'] == "addnews" AND $is_logged AND $user_group[$member_id['user_group']]['allow_adds'] ) {
	
		@header('X-XSS-Protection: 0;');
		
		$stop = "";
		$go_back ="<a href=\"javascript:history.go(-1)\">{$lang['all_prev']}</a>";
		
		if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
			$stop .= "<li>" . $lang['sess_error'] . "</li>";
		}

		$categories_default = "";

		if( isset($_POST['categories_default']) ) {
			
			$temp_array = explode( ',', $_POST['categories_default'] );
			$categories_default = array();
			
			foreach ( $temp_array as $element ) {
				$element = intval(trim($element));
				
				if( $element > 0 ) {
					$categories_default[] = $element;
				}
			}
			
			if( count($categories_default) ) $categories_default = htmlspecialchars(implode(',', $categories_default), ENT_QUOTES, 'UTF-8' );
			else $categories_default = "";
			
		}
		
		if( $categories_default ) {
			$add_url = "<a href=\"{$PHP_SELF}?do=addnews&amp;category={$categories_default}\">{$lang['add_noch']}</a>";
		} elseif ($config['allow_alt_url']) {
			$add_url = "<a href=\"{$config['http_home_url']}addnews.html\">{$lang['add_noch']}</a>";
		} else $add_url = "<a href=\"{$PHP_SELF}?do=addnews\">{$lang['add_noch']}</a>";
			
		if( !isset($_POST['duplicateprotection']) OR (isset($_SESSION['dp']['dp_'.md5($_POST['duplicateprotection'])]) AND $_SESSION['dp']['dp_'.md5($_POST['duplicateprotection'])] === true ) ) {
			$stop .= "<li>" . $lang['duplicate_protect'] . "</li>";
			$go_back = $add_url;
		}
	
		$allow_comm = isset($_POST['allow_comm']) ? intval( $_POST['allow_comm'] ) : 0;

		if( $user_group[$member_id['user_group']]['allow_main'] ) {

			$allow_main = isset($_POST['allow_main']) ?  intval($_POST['allow_main']) : 0;

		} else $allow_main = 0;
		
		$allow_rss_dzen = 1;
		$disable_rss_dzen = 0;
		$approve = isset($_POST['approve']) ? intval( $_POST['approve'] ) : 0;
		$allow_rating = isset($_POST['allow_rating']) ? intval( $_POST['allow_rating'] ) : 0;
		
		if( $user_group[$member_id['user_group']]['allow_fixed'] AND isset($_POST['news_fixed']) ) $news_fixed = intval( $_POST['news_fixed'] );
		else $news_fixed = 0;
		
		if (!$user_group[$member_id['user_group']]['moderation']) {
			$approve = 0;
			$allow_comm = 1;

			if ($user_group[$member_id['user_group']]['allow_main']) $allow_main = 1;
			else $allow_main = 0;

			$allow_rating = 1;
			$news_fixed = 0;
		}

		if( !isset($_POST['catlist']) OR (isset($_POST['catlist']) AND !is_array($_POST['catlist']) ) ) $_POST['catlist'] = array ();
		
		if( !count( $_POST['catlist'] ) ) {
			
			$catlist = array ();
			$catlist[] = '0';
			
		} else $catlist = $_POST['catlist'];

		$category_list = array();
	
		foreach ( $catlist as $value ) {
			$category_list[] = intval($value);
		}
		
		$catlist = $category_list;
		$category_list = $db->safesql( implode( ',', $category_list ) );

		
		foreach ( $catlist as $selected ) {

			if( isset($cat_info[$selected]) AND is_array( $cat_info[$selected] ) ) {

				if (isset($cat_info[$selected]['disable_main']) and $cat_info[$selected]['disable_main']) $allow_main = 0;
				if (isset($cat_info[$selected]['disable_comments']) and  $cat_info[$selected]['disable_comments']) $allow_comm = 1;
				if (isset($cat_info[$selected]['disable_rating']) and $cat_info[$selected]['disable_rating']) $allow_rating = 0;

				if ($member_id['user_group'] > 2) {
					if (!$cat_info[$selected]['enable_dzen']) $disable_rss_dzen++;
				}

			}
		
		}
		
		if($member_id['user_group'] > 2 ) {
			if( $disable_rss_dzen AND $disable_rss_dzen = count($catlist) ) $allow_rss_dzen = 0;
		}
	
		if( ! $config['allow_add_tags'] ) $_POST['tags'] = "";
		elseif( @preg_match( "/[\||\<|\>]/", $_POST['tags'] ) ) $_POST['tags'] = "";
		else $_POST['tags'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_COMPAT, 'UTF-8' ) );

		if ( $_POST['tags'] ) {
	
			$temp_array = array();
			$tags_array = array();
			$temp_array = explode (",", $_POST['tags']);
	
			if (count($temp_array)) {
	
				foreach ( $temp_array as $value ) {
					if( trim($value) ) $tags_array[] = trim( $value );
				}
	
			}
	
			if ( count($tags_array) ) $_POST['tags'] = implode(", ", $tags_array); else $_POST['tags'] = "";
	
		}
		
		if( $approve ) $msg = $lang['add_ok_1'];
		else $msg = $lang['add_ok_2'];
		
		if ($member_id['cat_add']) $allow_list = explode( ',', $member_id['cat_add'] );
		else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
		
		if( $user_group[$member_id['user_group']]['moderation'] ) {
			foreach ( $catlist as $selected ) {
				if( $allow_list[0] != "all" AND !in_array( $selected, $allow_list ) ) {
					$approve = 0;
					$msg = $lang['add_ok_3'];
				}
			}
		}

		if($member_id['cat_allow_addnews']) $allow_list = explode( ',', $member_id['cat_allow_addnews'] );
		else $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
		
		if( $allow_list[0] != "all" ) {
			foreach ( $catlist as $selected ) {
				if( !in_array( $selected, $allow_list ) ) {
					$stop .= "<li>" . $lang['news_err_41'] . "</li>";
				}
			}
		}

		$_POST['short_story'] = isset($_POST['short_story']) ? (string)$_POST['short_story'] : '';
		$_POST['full_story'] = isset($_POST['full_story']) ? (string)$_POST['full_story'] : '';

		$parse->allow_code = false;			
		$full_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['full_story'] ) ) );
		$short_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['short_story'] ) ) );

		if (trim($_POST['vote_title'])) {

			$add_vote = 1;
			$vote_title =  $db->safesql(trim($parse->process(strip_tags($_POST['vote_title']))));
			$frage =  $db->safesql(trim($parse->process(strip_tags($_POST['frage']))));
			$vote_body = $db->safesql($parse->BB_Parse($parse->process(strip_tags($_POST['vote_body'])), false));
			$allow_m_vote = intval($_POST['allow_m_vote']);
		} else $add_vote = 0;


		if( $parse->not_allowed_text ) {
			$stop .= "<li>" . $lang['news_err_39'] . "</li>";
		}

		$title = $db->safesql( $parse->process( trim( strip_tags ($_POST['title']) ) ) );
		
		$alt_name = isset($_POST['alt_name']) ? trim($_POST['alt_name']) : '';
		
		if( $alt_name == "" OR !$alt_name ) $alt_name = totranslit( stripslashes( $title ), true, false, $config['translit_url'] );
		else $alt_name = totranslit( $alt_name, true, false, $config['translit_url'] );
		
		$alt_name = $db->safesql( $alt_name );
		
		$add_module = "yes";
		$xfieldsaction = "init";
		$category = $catlist;

		if( $found AND $foundrow['xfields'] ) {

			$xf_existing = xfieldsdataload($foundrow['xfields']);

		} else $xf_existing = array();

		include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
		
		if( !$title ) $stop .= $lang['add_err_1'];
		if( dle_strlen( $title ) > 255 ) $stop .= $lang['add_err_2'];
		
		if( $config['allow_alt_url'] AND !$config['seo_type'] ) {
			
			$db->query( "SELECT id, date FROM " . PREFIX . "_post WHERE alt_name ='{$alt_name}'" );
	
			while($found_news = $db->get_row()) {
				if( $found_news['id'] AND date( 'Y-m-d', strtotime( $found_news['date'] ) ) == date( 'Y-m-d', $_TIME ) ) {
					$stop .= "<li>" .$lang['add_err_11'] . "</li>";
					break;
				}	
			}
		
		}
	
		if ($config['create_catalog']) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ), ENT_QUOTES, 'UTF-8' ), 0, 1 ) ); else $catalog_url = "";

		if ( $user_group[$member_id['user_group']]['disable_news_captcha'] AND $member_id['news_num'] >= $user_group[$member_id['user_group']]['disable_news_captcha'] ) {

			$user_group[$member_id['user_group']]['news_question'] = false;
			$user_group[$member_id['user_group']]['news_sec_code'] = false;

		}
		
		if( $user_group[$member_id['user_group']]['news_sec_code']) {
			
			if ($config['allow_recaptcha']) {
	
				$sec_code = 1;
				$sec_code_session = false;
	
				if (isset($_POST['g-recaptcha-response']) AND $_POST['g-recaptcha-response']) {
				
					$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);

					$resp = $reCaptcha->verifyResponse(get_ip(), $_POST['g-recaptcha-response'] );
				
				    if ($resp === null OR !$resp->success) {
	
							$stop .= "<li>" . $lang['recaptcha_fail'] . "</li>";
	
				    }
	
				} else $stop .= "<li>" . $lang['recaptcha_fail'] . "</li>";
	
			} elseif( $_REQUEST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) $stop .= "<li>" . $lang['recaptcha_fail'] . "</li>";

		
		}

		if( $user_group[$member_id['user_group']]['news_question'] ) {
	
			if ( intval($_SESSION['question']) ) {
	
				$answer = $db->super_query("SELECT id, answer FROM " . PREFIX . "_question WHERE id='".intval($_SESSION['question'])."'");
	
				$answers = explode( "\n", $answer['answer'] );
	
				$pass_answer = false;

				$question_answer = trim(dle_strtolower($_POST['question_answer']));
	
				if( count($answers) AND $question_answer ) {
					foreach( $answers as $answer ){

						$answer = trim(dle_strtolower($answer));
	
						if( $answer AND $answer == $question_answer ) {
							$pass_answer	= true;
							break;
						}
					}
				}
	
				if( !$pass_answer ) $stop .= $lang['reg_err_24'];
	
			} else $stop .= $lang['reg_err_24'];
		
		}

		if( $user_group[$member_id['user_group']]['flood_news'] ) {
			if( flooder( $member_id['name'],  $user_group[$member_id['user_group']]['flood_news'] )) {
				$stop .= "<li>" .$lang['news_err_4'] . " " . $lang['news_err_43'] . " {$user_group[$member_id['user_group']]['flood_news']} " . $lang['news_err_6']. "</li>";
			}
		}

		$max_detected = false;
		if( $user_group[$member_id['user_group']]['max_day_news'] AND !$found) {
			$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE date >= '".date("Y-m-d", $_TIME)."' AND date < '".date("Y-m-d", $_TIME)."' + INTERVAL 24 HOUR AND autor = '{$member_id['name']}'");
			if ($row['count'] >= $user_group[$member_id['user_group']]['max_day_news'] ) {
				$stop .= "<li>" .$lang['news_err_44'] . "</li>";
				$max_detected = true;
			}
		}

		if( $stop ) {
			$stop = "<ul>{$stop}</ul>{$go_back}";
			msgbox( $lang['add_err_6'], $stop  );
		}
		
		if( !$stop ) {
			
			$_SESSION['sec_code_session'] = 0;
			$_SESSION['question'] = false;
			
			if (dle_strlen($full_story) > 12) $all_xf_content = $full_story . ' ' . $all_xf_content;
			else $all_xf_content = $short_story . ' ' . $all_xf_content;
			
			if ($config['related_news'] AND $approve) {
				
				if ($found) {
					$related_ids = get_related_ids($title . ' ' . $all_xf_content, $foundrow['id'], $category_list);
				} else {
					$related_ids = get_related_ids($title . ' ' . $all_xf_content, false, $category_list);
				}

			} else $related_ids = '';

			if( $found ) {

				$msg = $lang['add_ok_4'];
				$lang['add_ok'] = $lang['title_editnews'];
				$db->query( "UPDATE " . PREFIX . "_post set title='{$title}', short_story='{$short_story}', full_story='{$full_story}', xfields='{$filecontents}', category='{$category_list}', alt_name='{$alt_name}', allow_comm='{$allow_comm}', approve='{$approve}', allow_main='{$allow_main}', fixed='{$news_fixed}', allow_br='0', tags='" . $_POST['tags'] . "' WHERE id='{$foundrow['id']}'" );
				$db->query( "UPDATE " . PREFIX . "_post_extras SET allow_rate='{$allow_rating}', related_ids='{$related_ids}', votes='{$add_vote}' WHERE news_id='{$foundrow['id']}'" );
				$insert_id = $foundrow['id'];

				if( $_POST['tags'] != $foundrow['tags'] OR $approve ) {
					$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$foundrow['id']}'" );
					
					if( $_POST['tags'] != "" and $approve ) {
						
						$tags = array ();
						
						$_POST['tags'] = explode( ",", $_POST['tags'] );
						
						foreach ( $_POST['tags'] as $value ) {
							
							$tags[] = "('" . $foundrow['id'] . "', '" . trim( $value ) . "')";
						}
						
						$tags = implode( ", ", $tags );
						$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
					
					}
				}

				if( $category_list != $foundrow['category'] OR $approve ) {
					$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '{$foundrow['id']}'" );

					if( $category_list AND $approve ) {

						$cat_ids = array ();

						$cat_ids_arr = explode( ",", $category_list );

						foreach ( $cat_ids_arr as $value ) {

							$cat_ids[] = "('" . $foundrow['id'] . "', '" . trim( $value ) . "')";
						}

						$cat_ids = implode( ", ", $cat_ids );
						$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );

					}
				}
				
				$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$foundrow['id']}'" );

				if ( count($xf_search_words) AND $approve ) {
					
					$temp_array = array();
					
					foreach ( $xf_search_words as $value ) {
						
						$temp_array[] = "('" . $foundrow['id'] . "', '" . $value[0] . "', '" . $value[1] . "')";
					}
					
					$xf_search_words = implode( ", ", $temp_array );
					$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
				}
				
				
				if( $add_vote ) {
					
					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll WHERE news_id = '{$id}'" );
					
					if( $count['count'] ) $db->query( "UPDATE  " . PREFIX . "_poll set title='$vote_title', frage='$frage', body='$vote_body', multiple='$allow_m_vote' WHERE news_id = '{$row['id']}'" );
					else $db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$id}', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );
				
				} else {
					$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='{$foundrow['id']}'" );
					$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$foundrow['id']}'" );
				}
				
				clear_cache( array('full_'. $foundrow['id'], 'comm_'. $foundrow['id']) );
			
			} else {

				if ( $max_detected ) die( "Hacking attempt!" );
				$added_time = time();
				$thistime = date( "Y-m-d H:i:s", $added_time );
				
				$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, fixed, allow_br, symbol, tags) values ('{$thistime}', '{$member_id['name']}', '{$short_story}', '{$full_story}', '{$filecontents}', '{$title}', '', '{$category_list}', '{$alt_name}', '{$allow_comm}', '{$approve}', '{$allow_main}', '{$news_fixed}', '0', '{$catalog_url}', '" . $_POST['tags'] . "')" );
				
				$row['id'] = $insert_id = $db->insert_id();

				$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, related_ids, user_id, allow_rss, allow_rss_dzen) VALUES('{$row['id']}', '{$allow_rating}', '{$add_vote}','{$related_ids}', '{$member_id['user_id']}', '1', '{$allow_rss_dzen}')" );

				if ( $approve ) {
					
					$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
					
				}
				
				if( $add_vote ) {
					$db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$row['id']}', '{$vote_title}', '{$frage}', '{$vote_body}', 0, '{$allow_m_vote}', '')" );
				}

				$member_id['name'] = $db->safesql($member_id['name']);

				$db->query( "UPDATE " . PREFIX . "_images set news_id='{$row['id']}' where author = '{$member_id['name']}' AND news_id = '0'" );
				$db->query( "UPDATE " . PREFIX . "_files set news_id='{$row['id']}' where author = '{$member_id['name']}' AND news_id = '0'" );
				$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );

				if( $user_group[$member_id['user_group']]['flood_news'] ) {
					$db->query( "INSERT INTO " . PREFIX . "_flood (id, ip, flag) values ('$_TIME', '{$member_id['name']}', '1')" );
				}
				
				if( $_POST['tags'] AND $approve ) {
					
					$tags = array ();
					
					$_POST['tags'] = explode( ",", $_POST['tags'] );
					
					foreach ( $_POST['tags'] as $value ) {
						
						$tags[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
					}
					
					$tags = implode( ", ", $tags );
					$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
				
				}
				
				if( $category_list AND $approve ) {
					
					$cat_ids = array ();
					
					$cat_ids_arr = explode( ",", $category_list );
					
					foreach ( $cat_ids_arr as $value ) {
						
						$cat_ids[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
					}
					
					$cat_ids = implode( ", ", $cat_ids );
					$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );
				
				}
	
				if ( count($xf_search_words) AND $approve ) {
					
					$temp_array = array();
					
					foreach ( $xf_search_words as $value ) {
						
						$temp_array[] = "('" . $row['id'] . "', '" . $value[0] . "', '" . $value[1] . "')";
					}
					
					$xf_search_words = implode( ", ", $temp_array );
					$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
				}
				
				if( !$approve and $config['mail_news'] ) {
					
					$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='new_news' LIMIT 0,1" );
					$mail = new dle_mail( $config, $row['use_html'] );
					
					$row['template'] = stripslashes( $row['template'] );
					$row['template'] = str_replace( "{%username%}", $member_id['name'], $row['template'] );
					$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $added_time, true ), $row['template'] );
					$row['template'] = str_replace( "{%title%}", stripslashes( stripslashes( $title ) ), $row['template'] );
					
					$category_list = explode( ",", $category_list );
					$my_cat = array ();
					
					foreach ( $category_list as $element ) {
						if( isset($cat_info[$element]['name']) ) {
							$my_cat[] = $cat_info[$element]['name'];
						}
					}
					
					if( count($my_cat) ) {
						$my_cat = stripslashes( implode( ', ', $my_cat ) );	
					} else $my_cat = '';
					
					$row['template'] = str_replace( "{%category%}", $my_cat, $row['template'] );
					
					$mail->send( $config['admin_mail'], $lang['mail_news'], $row['template'] );
				
				}
			
			}

			$_SESSION['dp']['dp_'.md5($_POST['duplicateprotection'])] = true;
			
			if( $approve ) {

				clear_cache( array('news_', 'related_', 'tagscloud_', 'archives_', 'calendar_', 'topnews_', 'rss', 'stats') );

				if( $config['news_indexnow'] AND $insert_id ) {

					$row = $db->super_query("SELECT id, date, category, alt_name FROM " . PREFIX . "_post WHERE id='{$insert_id}'");

					if ($config['allow_alt_url']) {
						if ($config['seo_type'] == 1 or $config['seo_type'] == 2) {
							if (intval($row['category']) and $config['seo_type'] == 2) {
								$full_link = $config['http_home_url'] . get_url(intval($row['category'])) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
							} else {
								$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
							}
						} else {
							$full_link = $config['http_home_url'] . date('Y/m/d/', strtotime($row['date'])) . $row['alt_name'] . ".html";
						}
					} else {
						$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
					}

					DLESEO::IndexNow( $full_link );
			
				}
	
			}
			
	
			msgbox( $lang['add_ok'], "{$msg} {$add_url} {$lang['add_or']} <a href=\"{$config['http_home_url']}\">{$lang['all_prev']}</a>" );

		
		}
	
	} elseif( $is_logged AND $user_group[$member_id['user_group']]['allow_adds'] ) {

		$duplicateprotection = md5(SECURE_AUTH_KEY.time().random_int( 0, 100 ));

		$js_array[] = "engine/classes/js/sortable.js";
		$js_array[] = "engine/classes/uploads/html5/plupload/plupload.full.min.js";
		$js_array[] = "engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js";
		$js_array[] = "engine/classes/calendar/calendar.js";
		
		$css_array[] = "engine/classes/calendar/calendar.css";
		
		if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

		$css_array[] = "engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css";

		$tpl->load_template( 'addnews.tpl' );
		
		$addtype = "addnews";
		$categories_default = "";
		
		if( $found ) {
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '{$id}'{$approve_find}" );

			if( isset($row['id']) AND $id == $row['id'] AND ($member_id['name'] == $row['autor'] OR $user_group[$member_id['user_group']]['allow_all_edit']) ) $found = true;
			else $found = false;

		} else { $row = array(); }
		
		if( $found ) {
			
			if ($config['alert_edit_now']) {

				if (isset($row['edited_now']) and $row['edited_now']) $row['edited_now'] = json_decode($row['edited_now'], true);
				else $row['edited_now'] = array('name' => '', 'time' => '');

				if ($row['edited_now']['name'] and $row['edited_now']['name'] != $member_id['name']  and time() < $row['edited_now']['time'] + 60) {

					$lang['edit_news_alert'] = str_replace('{name}', $row['edited_now']['name'], $lang['edit_news_alert']);

					$onload_scripts[] = <<<HTML
DLEPush.warning('{$lang['edit_news_alert']}', '', 20000);
HTML;
				} else {

					$db->query("UPDATE " . PREFIX . "_post_extras SET edited_now='" . $db->safesql(json_encode(array('name' => $member_id['name'], 'time' => time()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . "' WHERE news_id='{$row['id']}'");

					$onload_scripts[] = <<<HTML
function save_edit_alert() {

	$.post( dle_root + "engine/ajax/controller.php?mod=adminfunction", { 'id': '{$row['id']}', action: 'saveeditnews', user_hash: '{$dle_login_hash}' }, function(data){

		if (data.success) {
		
			setTimeout(function() {
				save_edit_alert();
			}, 20000);
			
		}
		
	}, "json");

};
setTimeout(function() {
	save_edit_alert();
}, 20000);
HTML;
				}
			}

			$cat_list = explode( ',', $row['category'] );
			$categories_list = CategoryNewsSelection( $cat_list, 0 );
			$tpl->set('{header-title}', $lang['title_editnews']);
			$tpl->set( '{title}', $parse->decodeBBCodes( $row['title'], false ) );
			$tpl->set( '{alt-name}', $row['alt_name'] );
			
			$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, true );
			$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, true );
			
			$tpl->set( '{short-story}', $row['short_story'] );
			$tpl->set( '{full-story}', $row['full_story'] );
			$tpl->set( '{tags}', $row['tags'] );

			if( $row['votes'] ) {
				$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll where news_id = '{$row['id']}'" );
				$poll['title'] = $parse->decodeBBCodes( $poll['title'], false );
				$poll['frage'] = $parse->decodeBBCodes( $poll['frage'], false );
				$poll['body'] = $parse->decodeBBCodes( $poll['body'], false );
				$poll['multiple'] = $poll['multiple'] ? "checked" : "";

				$tpl->set( '{votetitle}', $poll['title'] );
				$tpl->set( '{frage}', $poll['frage'] );
				$tpl->set( '{votebody}', $poll['body'] );
				$tpl->set( '{allowmvote}', $poll['multiple'] );

			} else {
				$tpl->set( '{votetitle}', '' );
				$tpl->set( '{frage}', '' );
				$tpl->set( '{votebody}', '' );
				$tpl->set( '{allowmvote}', '' );
			}
		
		} else {
			
			if( isset($_GET['category']) ) {
				
				$categories_list = CategoryNewsSelection( explode( ',', $_GET['category'] ), 0 );
				$temp_array = explode( ',', $_GET['category'] );
				$categories_default = array();
				
				foreach ( $temp_array as $element ) {
					$element = intval(trim($element));
					
					if( $element > 0 ) {
						$categories_default[] = $element;
					}
				}
				
				if( count($categories_default) ) $categories_default = htmlspecialchars(implode(',', $categories_default), ENT_QUOTES, 'UTF-8' );
				else $categories_default = "";
				
			} else $categories_list = CategoryNewsSelection( 0, 0 );

			$tpl->set( '{header-title}', $lang['title_addnews']);
			$tpl->set( '{title}', '' );
			$tpl->set( '{alt-name}', '' );
			$tpl->set( '{short-story}', '' );
			$tpl->set( '{full-story}', '' );
			$tpl->set( '{tags}', '' );
			$tpl->set( '{votetitle}', '' );
			$tpl->set( '{frage}', '' );
			$tpl->set( '{votebody}', '' );
			$tpl->set( '{allowmvote}', '' );
		
		}
		
			
		include_once (DLEPlugins::Check(ENGINE_DIR . '/editor/shortsite.php'));
		include_once (DLEPlugins::Check(ENGINE_DIR . '/editor/fullsite.php'));

		$tpl->set_block( "'\\[not-wysywyg\\].*?\\[/not-wysywyg\\]'si", '' );
			
		$tpl->set( '{shortarea}', $shortarea );
		$tpl->set( '{fullarea}', $fullarea );

		if ( !$config['disable_short'] ) {
			$tpl->set('[allow-shortstory]', '');
			$tpl->set('[/allow-shortstory]', '');
		} else {
			$tpl->set_block("'\\[allow-shortstory\\].*?\\[/allow-shortstory\\]'si", '');
		}

		if (!$config['disable_full']) {
			$tpl->set('[allow-fullstory]', '');
			$tpl->set('[/allow-fullstory]', '');
		} else {
			$tpl->set_block("'\\[allow-fullstory\\].*?\\[/allow-fullstory\\]'si", '');
		}

		$xfieldsaction = "categoryfilter";
		include_once (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
		
		if( $config['allow_multi_category'] ) {
			
			$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"category\" onchange=\"onCategoryChange(this)\" style=\"width:350px;height:140px;\" multiple=\"multiple\">";
		
		} else {
			
			$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"category\" onchange=\"onCategoryChange(this)\" style=\"width:350px;\">";
		}
		
		$cats .= $categories_list;
		$cats .= "</select>";
		
		$tpl->set( '{bbcode}', '' );
		$tpl->set( '{category}', $cats );
		
		if( $user_group[$member_id['user_group']]['moderation'] ) {
			$cheked = array();

			if ($found) {
				if( $row['approve'] ) $cheked['approve'] = ' checked="checked"'; else $cheked['approve'] = '';
				if( $row['allow_comm'] ) $cheked['allow_comm'] = ' checked="checked"'; else $cheked['allow_comm'] = '';
				if( $row['allow_main'] ) $cheked['allow_main'] = ' checked="checked"'; else $cheked['allow_main'] = '';
				if( $row['fixed'] ) $cheked['fixed'] = ' checked="checked"'; else $cheked['fixed'] = '';
				if( $row['allow_rate'] ) $cheked['allow_rate'] = ' checked="checked"'; else $cheked['allow_rate'] = '';

			} else {

				$cheked['approve'] = ' checked="checked"';
				$cheked['allow_comm'] = ' checked="checked"';
				$cheked['allow_main'] = ' checked="checked"';
				$cheked['fixed'] = '';
				$cheked['allow_rate'] = ' checked="checked"';

			}

			$admintag = "<div class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"approve\" id=\"approve\" value=\"1\"{$cheked['approve']}><span>{$lang['add_al_ap']}</span></label></div>";

			$admintag .= "<div id=\"opt_holder_comments\" class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"allow_comm\" value=\"1\"{$cheked['allow_comm']}><span>" . $lang['add_al_com'] . "</span></label></div>";
			
			if( $user_group[$member_id['user_group']]['allow_main'] ) $admintag .= "<div id=\"opt_holder_main\" class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"allow_main\" id=\"allow_main\" value=\"1\"{$cheked['allow_main']}><span>" . $lang['add_al_m'] . "</span></label></div>";
			
			$admintag .= "<div id=\"opt_holder_rating\" class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"allow_rating\" id=\"allow_rating\" value=\"1\"{$cheked['allow_rate']}><span>{$lang['addnews_allow_rate']}</span></label></div>";
			
			if( $user_group[$member_id['user_group']]['allow_fixed'] ) $admintag .= "<div class=\"checkbox\"><label class=\"form-check-label\"><input class=\"form-check-input\" type=\"checkbox\" name=\"news_fixed\" id=\"news_fixed\" value=\"1\"{$cheked['fixed']}><span>{$lang['add_al_fix']}</span></label></div>";
			
			$tpl->set( '{admintag}', $admintag );
		
		} else $tpl->set( '{admintag}', '' );
		
		if( $is_logged and $member_id['user_group'] < 3 ) {
			
			$tpl->set( '[urltag]', '' );
			$tpl->set( '[/urltag]', '' );
		
		} else
			$tpl->set_block( "'\\[urltag\\].*?\\[/urltag\\]'si", "" );
		
		if( $found ) {
			
			$xfieldsaction = "list";
			$xfieldmode = "site";
			$xfieldsid = $row['xfields'];
			$xfieldscat = $row['category'];
			$author = urlencode($row['autor']);
			$news_id = $row['id'];
			include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
		
		} else {
			
			$xfieldsaction = "list";
			$xfieldmode = "site";
			$xfieldsadd = true;
			$news_id = 0;
	        $author = urlencode($member_id['name']);
			include (DLEPlugins::Check(ENGINE_DIR . '/inc/xfields.php'));
		
		}
		
		$tpl->set( '{xfields}', $output );
		
		if ( count( $xfieldinput ) ) {
			foreach ( $xfieldinput as $key => $value ) {
				$tpl->copy_template = str_replace( "[xfinput_{$key}]", $value, $tpl->copy_template );
			}		
		}

		if ( $user_group[$member_id['user_group']]['disable_news_captcha'] AND $member_id['news_num'] >= $user_group[$member_id['user_group']]['disable_news_captcha'] ) {

			$user_group[$member_id['user_group']]['news_question'] = false;
			$user_group[$member_id['user_group']]['news_sec_code'] = false;

		}

		if( $user_group[$member_id['user_group']]['news_question'] ) {

			$tpl->set( '[question]', "" );
			$tpl->set( '[/question]', "" );

			$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
			$tpl->set( '{question}', htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, 'UTF-8' ) );

			$_SESSION['question'] = $question['id'];

		} else {

			$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
			$tpl->set( '{question}', "" );

		}
		
		if( $user_group[$member_id['user_group']]['news_sec_code'] ) {

			if ( $config['allow_recaptcha'] ) {

				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );
				
				$captcha_name = "g-recaptcha";
				$captcha_url = "https://www.google.com/recaptcha/api.js?hl={$lang['language_code']}";
				
				if( $config['allow_recaptcha'] == 3) {
					
					$captcha_name = "h-captcha";
					$captcha_url = "https://js.hcaptcha.com/1/api.js?hl={$lang['language_code']}";
				
				}

				if ($config['allow_recaptcha'] == 4) {

					$captcha_name = "cf-turnstile";
					$captcha_url = "https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha";
				}

				if( $config['allow_recaptcha'] == 2) {
					
					$tpl->set( '{recaptcha}', "");
					$tpl->copy_template .= "<script src=\"https://www.google.com/recaptcha/api.js?render={$config['recaptcha_public_key']}\" async defer></script>";
					
				} else {
					
					$tpl->set( '{recaptcha}', "<div class=\"{$captcha_name}\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\" data-language=\"{$lang['language_code']}\"></div><script src=\"{$captcha_url}\" async defer></script>" );	
				
				}

				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{sec_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$tpl->set( '{sec_code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

		} else {

			$tpl->set( '{sec_code}', "" );
			$tpl->set( '{recaptcha}', "" );
			$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
			$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );

		}

		$script = "
<script>
<!--
function preview(){
	if(document.entryform.title.value == ''){ DLEPush.error('$lang[add_err_7]'); return false;}
    else{
		var width  = 800;
		var height = 500;
		var left   = (screen.width  - width)/2;
		var top    = (screen.height - height)/2;

		dd=window.open('','prv','width='+width+', height='+height+', top='+top+', left='+left+', directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no');
        document.entryform.mod.value='preview';document.entryform.action='{$PHP_SELF}?do=preview';document.entryform.target='prv';
        document.entryform.submit();dd.focus();
        setTimeout(\"document.entryform.mod.value='addnews';document.entryform.action='';document.entryform.target='_self'\",500);
    }
}";
		
		$script .= <<<HTML
	function split( val ) {
		return val.split( /,\s*/ );
	}
	
	function extractLast( term ) {
		return split( term ).pop();
	}

	function find_relates ( )
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post(dle_root + 'engine/ajax/controller.php?mod=find_relates', { title: title, mode: 1, user_hash: '{$dle_login_hash}' }, function(data){
	
			HideLoading('');
	
			$('#related_news').html(data);
	
		});

		return false;

	};
	
	function xfimagedelete( xfname, xfvalue )
	{
		
		DLEconfirmDelete( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post(dle_root + 'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$news_id}', author: '{$author}', 'images[]' : xfvalue }, function(data){
	
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
			
			$.post(dle_root + 'engine/ajax/controller.php?mod=upload', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$news_id}', author: '{$author}', 'files[]' : xfvalue }, function(data){
	
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

	function StripHTML(html) {
		var tmp = document.createElement("DIV");
		tmp.innerHTML = html;
		return tmp.textContent || tmp.innerText || "";
	};

	function checkxf() {

		var status = '';
		var alert_text = '{$lang['addnews_xf_alert_1']}';
		var alert_all_text = [];
		
		tinyMCE.triggerSave();

		$('[uid="essential"]:visible').each(function(indx) {
			
			var tempval = StripHTML($(this).find('[rel="essential"]').val());
			tempval = tempval.trim();

			if(tempval.length < 1) {
			
				if( $(this).find('[rel=\"essential\"]').data('alert') ) {
				
					alert_all_text.push( alert_text.replace(/{field}/g, $(this).find('[rel=\"essential\"]').data('alert') ) );
					
				}

				status = 'fail';
			
			}

		});

		$('[data-blockminlen]:visible').each(function(indx) {
			var tempval = StripHTML($(this).find('[data-minlen]').val());
			tempval = tempval.trim();

			if( tempval.length && tempval.length < $(this).find('[data-minlen]').data('minlen')) {
				
				var alert_text = '{$lang['addnews_xf_alert_2']}';
				
				alert_text = alert_text.replace(/{field}/g, $(this).find('[data-minlen]').data('alert') );
				alert_text = alert_text.replace(/{count}/g, $(this).find('[data-minlen]').data('minlen') );
				alert_all_text.push(alert_text);
			
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
				alert_all_text.push(alert_text);

				status = 'fail';
			}

		});

		if (status == 'fail' ) {
			DLEPush.error(alert_all_text.join('<br><br>'));
		}
		
		if(document.entryform.title.value == ''){

			DLEPush.error('{$lang['add_err_7']}'); 

			status = 'fail';

		}

		return status;

	};

	var text_upload = "{$lang['bb_t_up']}";

//-->
</script>
HTML;

			$onload_scripts[] = <<<HTML
$('[data-rel=links]').autocomplete({
	source: function( request, response ) {
		$.getJSON( dle_root + 'engine/ajax/controller.php?mod=find_tags&user_hash={$dle_login_hash}&mode=xfield', {
			term: extractLast( request.term )
		}, response );
	},
	search: function() {
		var term = extractLast( this.value );
		if ( term.length < 3 ) {
			return false;
		}
	},
	focus: function() {
		return false;
	},
	select: function( event, ui ) {
		var terms = split( this.value );
		terms.pop();
		terms.push( ui.item.value );
		terms.push( '' );
		this.value = terms.join( ', ' );
		return false;
	}
});
HTML;

		if( $config['allow_add_tags'] ) {

			$onload_scripts[] = <<<HTML
$( '#tags' ).autocomplete({
	source: function( request, response ) {
		$.getJSON( dle_root + 'engine/ajax/controller.php?mod=find_tags&user_hash={$dle_login_hash}', {
			term: extractLast( request.term )
		}, response );
	},
	search: function() {
		var term = extractLast( this.value );
		if ( term.length < 3 ) {
			return false;
		}
	},
	focus: function() {
		return false;
	},
	select: function( event, ui ) {
		var terms = split( this.value );
		terms.pop();
		terms.push( ui.item.value );
		terms.push( '' );
		this.value = terms.join( ', ' );
		return false;
	}
});
HTML;
		}
		
		$script .= "<form method=\"post\" name=\"entryform\" id=\"entryform\" action=\"\">";
		
		$onload_scripts[] = <<<HTML
$('#entryform').submit(function() {

	if(checkxf()=='fail') {
		return false;
	}
	
	if( dle_captcha_type == 2 && typeof grecaptcha != "undefined" ) {
	
		event.preventDefault();
		
		grecaptcha.execute('{$config['recaptcha_public_key']}', {action: 'addnews'}).then(function(token) {
			$('#g-recaptcha-response').remove();
			$('#entryform').append('<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="' + token + '">');
			$('#entryform').off('submit');
			HTMLFormElement.prototype.submit.call(document.getElementById('entryform'));
		});

		return false;
	}
	
	return true;
});
$(".xfields textarea").each(function () {
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
HTML;

		if( $categories_default ) {
			
			$categories_default = "<input type=\"hidden\" name=\"categories_default\" value=\"{$categories_default}\">";
			
		} else $categories_default = "";
		
		$tpl->copy_template = $categoryfilter . $script . $tpl->copy_template . $categories_default."<input type=\"hidden\" name=\"mod\" value=\"addnews\"><input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\"><input type=\"hidden\" name=\"duplicateprotection\" value=\"{$duplicateprotection}\"></form>";

		$tpl->compile( 'content' );
		$tpl->clear();
	
	} else msgbox( $lang['all_info'], "{$lang['add_err_8']}<br><a href=\"javascript:history.go(-1)\">{$lang['all_prev']}</a>" );

}
?>