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
 File: show.custom.php
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$global_custom_news_count = 0;
$i = 0;

if( isset( $cstart ) ) $i = $cstart;

$news_found = false;

$xfields = xfieldsload();

if(count($xfields)) {
	$xfound = true;
} else $xfound = false;

if( $use_banners AND $config['allow_banner'] AND is_array( $banners ) AND count( $banners ) AND isset( $ban_short ) ) {
	
	$news_c = 1;
	$banners_topz = $banners_cenz = $banners_downz = '';
	
	if ( isset($ban_short['top']) AND is_array($ban_short['top']) AND count($ban_short['top']) ) {
		for($indx = 0, $max = sizeof( $ban_short['top'] ), $banners_topz = ''; $indx < $max; $indx ++) {
			if( isset($ban_short['top'][$indx]['zakr']) AND $ban_short['top'][$indx]['zakr'] ) {
				$banners_topz .= $ban_short['top'][$indx]['text'];
				unset( $ban_short['top'][$indx] );
			}
		}
	}

	if ( isset($ban_short['cen']) AND is_array($ban_short['cen']) AND count($ban_short['cen']) ) {		
		for($indx = 0, $max = sizeof( $ban_short['cen'] ), $banners_cenz = ''; $indx < $max; $indx ++) {
			if( isset($ban_short['cen'][$indx]['zakr']) AND $ban_short['cen'][$indx]['zakr'] ) {
				$banners_cenz .= $ban_short['cen'][$indx]['text'];
				unset( $ban_short['cen'][$indx] );
			}
		}
	}
	
	if ( isset($ban_short['down']) AND is_array($ban_short['down']) AND count($ban_short['down']) ) {		
		for($indx = 0, $max = sizeof( $ban_short['down'] ), $banners_downz = ''; $indx < $max; $indx ++) {
			if( isset($ban_short['down'][$indx]['zakr']) AND $ban_short['down'][$indx]['zakr'] ) {
				$banners_downz .= $ban_short['down'][$indx]['text'];
				unset( $ban_short['down'][$indx] );
			}
		}
	}
	
	$middle = floor( $custom_limit / 2 ) + 1;
	
	if($middle < 2 ) $middle = 2;

	$middle_s = round( $middle / 2 );

	if($middle_s < 2 ) $middle_s = 2;
	
	if($middle_s == $middle ) {
		if( (is_array($ban_short['cen']) AND count($ban_short['cen'])) OR  $banners_cenz )  $middle_s = 0;
	}
	
	$middle_e = floor( $middle + (($custom_limit - $middle) / 2) + 1 );
	
	if($middle AND  $middle_e == $middle ) {
		if( (is_array($ban_short['cen']) AND count($ban_short['cen'])) OR  $banners_cenz )  $middle_e = 0;
	}
	
	if($middle_s AND $middle_e == $middle_s ) {
		if( (is_array($ban_short['top']) AND count($ban_short['top'])) OR  $banners_topz )  $middle_e = 0;
	}
	
}

while ( $row = $db->get_row( $sql_result ) ) {
	
	$news_found = true;
	$custom_news = true;
	$attachments[] = $row['id'];
	$row['date'] = strtotime( $row['date'] );
	
	if( isset($showed_news_ids) AND is_array($showed_news_ids)) {
		$showed_news_ids[] = $row['id'];
	}

	if( $row['editdate'] AND $row['editdate'] > $_DOCUMENT_DATE ) $_DOCUMENT_DATE = $row['editdate'];
	elseif( $row['date'] > $_DOCUMENT_DATE ) $_DOCUMENT_DATE = $row['date'];

	if( $config['allow_banner'] AND isset($banners) AND is_array($banners) AND count( $banners ) ) {
		
		foreach ( $banners as $name => $value ) {
			$tpl->copy_template = str_replace( "{banner_" . $name . "}", $value, $tpl->copy_template );

			if ( $value ) {
				$tpl->copy_template = str_replace ( "[banner_" . $name . "]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace ( "[/banner_" . $name . "]", "", $tpl->copy_template );
			}
		}
	}
	
	$tpl->set_block( "'{banner_(.*?)}'si", "" );
	$tpl->set_block ( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "" );
	
	if( isset( $middle ) ) {

		if( $news_c == $middle_s ) {
			$tpl->copy_template = bannermass( $banners_topz, $ban_short['top'] ).$tpl->copy_template;
		} else if( $news_c == $middle ) {
			$tpl->copy_template = bannermass( $banners_cenz, $ban_short['cen'] ).$tpl->copy_template;
		} else if( $news_c == $middle_e ) {
			$tpl->copy_template = bannermass( $banners_downz, $ban_short['down'] ).$tpl->copy_template;
		}
		
		$news_c ++;
	}
	
	$i ++;
	
	$allow_comments_in_cat = true;

	if( !$row['category'] ) {
		
		$my_cat = "---";
		$my_cat_link = "---";
		
		$tpl->set( '[not-has-category]', "" );
		$tpl->set( '[/not-has-category]', "" );
		$tpl->set_block( "'\\[has-category\\](.*?)\\[/has-category\\]'si", "" );
			
	} else {
		
		$my_cat = array ();
		$my_cat_link = array ();
		$cat_list = $row['cats'] = explode( ',', $row['category'] );
		
		$tpl->set( '[has-category]', "" );
		$tpl->set( '[/has-category]', "" );
		$tpl->set_block( "'\\[not-has-category\\](.*?)\\[/not-has-category\\]'si", "" );
			
		if( count( $cat_list ) == 1 ) {
			
			if( $cat_info[$cat_list[0]]['id'] ) {
				
				$my_cat[] = $cat_info[$cat_list[0]]['name'];
				$my_cat_link = get_categories( $cat_list[0], $config['category_separator']);

				if ( $cat_info[$cat_list[0]]['disable_comments'] ) $allow_comments_in_cat = false;

			} else $my_cat_link = "---";
		
		} else {
			
			foreach ( $cat_list as $element ) {
				if( $element AND $cat_info[$element]['id']) {

					$my_cat[] = $cat_info[$element]['name'];
					
					if( $config['allow_alt_url']) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
					else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category=". get_url( $element ) . "\">{$cat_info[$element]['name']}</a>";

					if ($cat_info[$element]['disable_comments']) $allow_comments_in_cat = false;
				}
			}
			
			if( count( $my_cat_link ) ) {
				$my_cat_link = implode( $config['category_separator'], $my_cat_link );
			} else $my_cat_link = "---";
		}
		
		if( count( $my_cat ) ) {
			$my_cat = implode( $config['category_separator'], $my_cat );
		} else $my_cat = "---";
			
	}

	$url_cat = $category_id;

	if (stripos ( $tpl->copy_template, "[category=" ) !== false) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(category)=(.+?)\\](.*?)\\[/category\\]#is", "check_category", $tpl->copy_template );
	}
	
	if (stripos ( $tpl->copy_template, "[not-category=" ) !== false) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(not-category)=(.+?)\\](.*?)\\[/not-category\\]#is", "check_category", $tpl->copy_template );
	}

	$category_id = $row['category'];

	if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
	}
							
	if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
	}
	
	$temp_rating = $config['rating_type'];
	$config['rating_type'] = if_category_rating( $row['category'] );
	
	if ( $config['rating_type'] === false ) {
		$config['rating_type'] = $temp_rating;
	}
		
	$category_id = $url_cat;
	
	if( $config['allow_alt_url'] ) {
			
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2  ) {
			
			if( $row['category'] and $config['seo_type'] == 2 ) {

				$cats_url = get_url( $row['category'] );
				
				if($cats_url) {
					
					$full_link = $config['http_home_url'] . $cats_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
					
				} else $full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
			
			} else {
				
				$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
		}
	
	} else {
		
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
	
	}
	
		if ( $row['category'] ) {
			
			if( $config['allow_alt_url'] ) {
				
				$cats_url = get_url( $row['category'] );
				
				if( $cats_url ) $cats_url .= "/";
			
				$tpl->set( '{category-url}', $config['http_home_url'] . $cats_url );
				
			} else {
				
				$cats_url = intval($row['category']);
				$tpl->set( '{category-url}', "{$PHP_SELF}?do=cat&category=".get_url($cats_url) );
				
			}
			
		} else $tpl->set( '{category-url}', "#" );	
		
	$row['category'] = intval( $row['category'] );

	if( !$allow_comments_in_cat ) $row['comm_num'] = 0;
	
	$news_find = array ('{comments-num}' => number_format($row['comm_num'], 0, ',', ' '), '{views}' => number_format($row['news_read'], 0, ',', ' '), '{category}' => $my_cat, '{link-category}' => $my_cat_link, '{news-id}' => $row['id'], '{rssdate}' => date( "r", $row['date'] ), '{rssauthor}' => $row['autor'], '{approve}' => '' );
	
	$tpl->set( '', $news_find );

	$compare_date = compare_days_date($row['date'],  $short_news_cache);

	if( !$compare_date ) {
		
		$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'], $short_news_cache ) );
	
	} elseif( $compare_date == 1 ) {
		
		$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'], $short_news_cache ) );
	
	} else {
		
		$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'], $short_news_cache ) );
	
	}
	
	$news_date = $row['date'];
	$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

	if (strpos($tpl->copy_template, "[new]") !== false OR strpos($tpl->copy_template, "[not-new]") !== false ) {

		if( $config['post_new'] AND compare_days_date($row['date'],  $short_news_cache, true) < $config['post_new'] ) {
			$tpl->set('[new]', "");
			$tpl->set('[/new]', "");
			$tpl->set_block("'\\[not-new\\](.*?)\\[/not-new\\]'si", "");
		} else {
			$tpl->set('[not-new]', "");
			$tpl->set('[/not-new]', "");
			$tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si", "");
		}

	}

	if (strpos($tpl->copy_template, "[updated]") !== false or strpos($tpl->copy_template, "[not-updated]") !== false) {

		if ($config['post_updated'] AND $row['editdate'] AND $row['view_edit'] AND compare_days_date($row['date'],  $short_news_cache, true) > $config['post_new'] AND compare_days_date($row['editdate'],  $short_news_cache, true) < $config['post_updated'] ) {
			$tpl->set('[updated]', "");
			$tpl->set('[/updated]', "");
			$tpl->set_block("'\\[not-updated\\](.*?)\\[/not-updated\\]'si", "");
		} else {
			$tpl->set('[not-updated]', "");
			$tpl->set('[/not-updated]', "");
			$tpl->set_block("'\\[updated\\](.*?)\\[/updated\\]'si", "");
		}
	}

	$global_custom_news_count ++;

	if (strpos ( $tpl->copy_template, "[newscount=" ) !== false) {
		
		$tpl->copy_template = preg_replace_callback ( "#\\[newscount=(.+?)\\](.*?)\\[/newscount\\]#is", 
			function ($matches) use ($global_custom_news_count) {
				
				$block = $matches[2];
			
				$counts = explode( ',', trim($matches[1]) );
				
				if( !in_array($global_custom_news_count, $counts) ) return "";
				
				return $block;
				 
			}, $tpl->copy_template );
			
	}

	if (strpos ( $tpl->copy_template, "[not-newscount=" ) !== false) {
		
		$tpl->copy_template = preg_replace_callback ( "#\\[not-newscount=(.+?)\\](.*?)\\[/not-newscount\\]#is", 
			function ($matches) use ($global_custom_news_count) {
				
				$block = $matches[2];
			
				$counts = explode( ',', trim($matches[1]) );
				
				 if( in_array($global_custom_news_count, $counts) ) return "";
				
				return $block;
				 
			}, $tpl->copy_template );
			
	}
	
	$tpl->set_block( "'\\[not-news\\](.*?)\\[/not-news\\]'si", "" );

	if ( $row['fixed'] ) {

		$tpl->set( '[fixed]', "" );
		$tpl->set( '[/fixed]', "" );
		$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

	} else {

		$tpl->set( '[not-fixed]', "" );
		$tpl->set( '[/not-fixed]', "" );
		$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
	}		

	if ( $row['comm_num'] ) {

		$tpl->set( '[comments]', "" );
		$tpl->set( '[/comments]', "" );
		$tpl->set_block( "'\\[not-comments\\](.*?)\\[/not-comments\\]'si", "" );

	} else {
			
			
		$tpl->set( '[not-comments]', "" );
		$tpl->set( '[/not-comments]', "" );
		$tpl->set_block( "'\\[comments\\](.*?)\\[/comments\\]'si", "" );
	}

	if ( $row['votes'] ) {

		$tpl->set( '[poll]', "" );
		$tpl->set( '[/poll]', "" );
		$tpl->set_block( "'\\[not-poll\\](.*?)\\[/not-poll\\]'si", "" );

	} else {

		$tpl->set( '[not-poll]', "" );
		$tpl->set( '[/not-poll]', "" );
		$tpl->set_block( "'\\[poll\\](.*?)\\[/poll\\]'si", "" );
	}

	if( strpos( $tpl->copy_template, "{poll}" ) !== false) {
	
		if( $row['votes'] ) {
	
			include (DLEPlugins::Check(ENGINE_DIR . '/modules/poll.php'));
	
			$tpl->set( '{poll}', $tpl->result['poll'] );
	
		} else {
	
			$tpl->set( '{poll}', '' );
	
		}
	}

	if( $row['view_edit'] and $row['editdate'] ) {
		
		$compare_date = compare_days_date($row['editdate'],  $short_news_cache);

		if( !$compare_date ) {
			
			$tpl->set( '{edit-date}', $lang['time_heute'] . langdate( ", H:i", $row['editdate'], $short_news_cache ) );
		
		} elseif( $compare_date == 1 ) {
			
			$tpl->set( '{edit-date}', $lang['time_gestern'] . langdate( ", H:i", $row['editdate'], $short_news_cache ) );
		
		} else {
			
			$tpl->set( '{edit-date}', langdate( $config['timestamp_active'], $row['editdate'], $short_news_cache ) );
		
		}
		
		$news_date = $row['editdate'];
		$tpl->copy_template = preg_replace_callback("#\{edit-date=(.+?)\}#i", "formdate", $tpl->copy_template);

		$tpl->set( '{editor}', $row['editor'] );
		$tpl->set( '{edit-reason}', $row['reason'] );
		
		if( $row['reason'] ) {
			
			$tpl->set( '[edit-reason]', "" );
			$tpl->set( '[/edit-reason]', "" );
		
		} else
			$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
		
		$tpl->set( '[edit-date]', "" );
		$tpl->set( '[/edit-date]', "" );
	
	} else {
		
		$tpl->set( '{edit-date}', "" );
		$tpl->set( '{editor}', "" );
		$tpl->set( '{edit-reason}', "" );
		$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
		$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
	}
	
	if( $config['allow_tags'] and $row['tags'] ) {
		
		$tpl->set( '[tags]', "" );
		$tpl->set( '[/tags]', "" );
		
		$tags = array ();
		
		$row['tags'] = explode( ",", $row['tags'] );
		
		foreach ( $row['tags'] as $value ) {
			
			$value = trim( $value );
			$url_tag = str_replace(array("&#039;", "&quot;", "&amp;", "/"), array("'", '"', "&", "&frasl;"), $value);
			
			if( $config['allow_alt_url'] ) $tags[] = "<a href=\"" . $config['http_home_url'] . "tags/" . rawurlencode( dle_strtolower($url_tag) ) . "/\">" . $value . "</a>";
			else $tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=" . rawurlencode( dle_strtolower($url_tag) ) . "\">" . $value . "</a>";
		
		}
		
		$tpl->set( '{tags}', implode( $config['tags_separator'], $tags ) );
	
	} else {
		
		$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
		$tpl->set( '{tags}', "" );
	
	}
	
	if( isset($cat_info[$row['category']]['icon']) AND $cat_info[$row['category']]['icon'] ) {
		
		$tpl->set( '{category-icon}', $cat_info[$row['category']]['icon'] );
		$tpl->set( '[category-icon]', "" );
		$tpl->set( '[/category-icon]', "" );
		$tpl->set_block( "'\\[not-category-icon\\](.*?)\\[/not-category-icon\\]'si", "" );
	
	} else {
		
		$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
		$tpl->set( '[not-category-icon]', "" );
		$tpl->set( '[/not-category-icon]', "" );
		$tpl->set_block( "'\\[category-icon\\](.*?)\\[/category-icon\\]'si", "" );
	
	}
	
	if ( $config['rating_type'] == "1" ) {
			$tpl->set( '[rating-type-2]', "" );
			$tpl->set( '[/rating-type-2]', "" );
			$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-4\\](.*?)\\[/rating-type-4\\]'si", "" );
	} elseif ( $config['rating_type'] == "2" ) {
			$tpl->set( '[rating-type-3]', "" );
			$tpl->set( '[/rating-type-3]', "" );
			$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-4\\](.*?)\\[/rating-type-4\\]'si", "" );
	} elseif ( $config['rating_type'] == "3" ) {
			$tpl->set( '[rating-type-4]', "" );
			$tpl->set( '[/rating-type-4]', "" );
			$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
	} else {
			$tpl->set( '[rating-type-1]', "" );
			$tpl->set( '[/rating-type-1]', "" );
			$tpl->set_block( "'\\[rating-type-4\\](.*?)\\[/rating-type-4\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );	
	}
		
	if( $row['allow_rate'] ) {
			
		if( $config['short_rating'] AND $user_group[$member_id['user_group']]['allow_rating'] ) {
				
			$tpl->set( '{rating}', ShowRating( $row['id'], $row['rating'], $row['vote_num'], 1 ) );
				
			if ( $config['rating_type'] ) {
					
				$tpl->set( '[rating-plus]', "<a href=\"#\" onclick=\"doRate('plus', '{$row['id']}'); return false;\" >" );
				$tpl->set( '[/rating-plus]', '</a>' );
				
				if ( $config['rating_type'] == "2" OR $config['rating_type'] == "3") {
					
					$tpl->set( '[rating-minus]', "<a href=\"#\" onclick=\"doRate('minus', '{$row['id']}'); return false;\" >" );
					$tpl->set( '[/rating-minus]', '</a>' );
					
				} else {
					$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
				}
				
			} else {
				$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
				$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
			}
			
		} else {
			
			$tpl->set( '{rating}', ShowRating( $row['id'], $row['rating'], $row['vote_num'], 0 ) );
			$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
			$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
		}
		
		if( $row['vote_num'] ) $ratingscore = str_replace( ',', '.', round( ($row['rating'] / $row['vote_num']), 1 ) );
		else $ratingscore = 0;

		$tpl->set( '{ratingscore}', $ratingscore );
			
		$dislikes = ($row['vote_num'] - $row['rating'])/2;
		$likes = $row['vote_num'] - $dislikes;
		
		$tpl->set( '{likes}', "<span data-likes-id=\"" . $row['id'] . "\">".$likes."</span>" );
		$tpl->set( '{dislikes}', "<span data-dislikes-id=\"" . $row['id'] . "\">".$dislikes."</span>" );
		$tpl->set( '{vote-num}', "<span data-vote-num-id=\"" . $row['id'] . "\">".$row['vote_num']."</span>" );
		$tpl->set( '[rating]', "" );
		$tpl->set( '[/rating]', "" );
		
	} else {
		
		$tpl->set( '{rating}', "" );
		$tpl->set( '{ratingscore}', "" );
		$tpl->set( '{vote-num}', "" );
		$tpl->set( '{likes}', "" );
		$tpl->set( '{dislikes}', "" );
		$tpl->set_block( "'\\[rating\\](.*?)\\[/rating\\]'si", "" );
		$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
		$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
	}
	
	$config['rating_type'] = $temp_rating;
	
	if( $config['allow_alt_url']) {
				
		$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
		$tpl->set( '[day-news]', "<a href=\"".$config['http_home_url'] . date( 'Y/m/d/', $row['date'])."\" >" );
	
	} else {
		
		$go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] );
		$tpl->set( '[day-news]', "<a href=\"$PHP_SELF?year=".date( 'Y', $row['date'])."&amp;month=".date( 'm', $row['date'])."&amp;day=".date( 'd', $row['date'])."\" >" );
	
	}

	$tpl->set( '[/day-news]', "</a>" );
	$tpl->set( '[profile]', "<a href=\"" . $go_page . "\">" );
	$tpl->set( '[/profile]', "</a>" );

	$tpl->set( '{login}', $row['autor'] );
	
	$tpl->set( '{author}', "<a onclick=\"ShowProfile('" . urlencode( $row['autor'] ) . "', '" . $go_page . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $go_page . "\">" . $row['autor'] . "</a>" );
	
	$_SESSION['referrer'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
	
	if( $is_logged AND (($member_id['name'] == $row['autor'] AND $user_group[$member_id['user_group']]['allow_edit']) OR $user_group[$member_id['user_group']]['allow_all_edit']) ) {

		if( $member_id['name'] == $row['autor'] AND $user_group[$member_id['user_group']]['allow_edit'] AND $user_group[$member_id['user_group']]['moderation'] ) {
			$allow_only_this_delete = true;
		} else { $allow_only_this_delete = false; }

		$tpl->set( '[edit]', "<a onclick=\"return dropdownmenu(this, event, MenuNewsBuild('" . $row['id'] . "', 'short', " . $allow_only_this_delete . "), '170px')\" href=\"#\">" );
		$tpl->set( '[/edit]', "</a>" );
		$allow_comments_ajax = true;

	} else $tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );

	if( $is_logged AND $user_group[$member_id['user_group']]['moderation'] AND (($member_id['name'] == $row['autor'] AND $user_group[$member_id['user_group']]['allow_edit']) OR $user_group[$member_id['user_group']]['allow_all_edit']) ) {
		$tpl->set('[del]', "<a onclick=\"dle_news_delete ('" . $row['id'] . "'); return false;\" href=\"#\">");
		$tpl->set('[/del]', "</a>");
	} else {
		$tpl->set_block("'\\[del\\](.*?)\\[/del\\]'si", "");
	}

	if( $row['full_story'] < 13 AND $config['hide_full_link'] ){
		$tpl->set_block("'\\[full-link\\](.*?)\\[/full-link\\]'si", "");
	} else {
		$tpl->set( '[full-link]', "<a href=\"" . $full_link . "\">" );
		$tpl->set( '[/full-link]', "</a>" );
	}
	
	$tpl->set( '{full-link}', $full_link );
	
	if( $row['allow_comm'] OR (!$row['allow_comm'] AND $row['comm_num']) ) {
		
		$tpl->set( '[com-link]', "<a href=\"" . $full_link . "#comment\">" );
		$tpl->set( '[/com-link]', "</a>" );
	
	} else $tpl->set_block( "'\\[com-link\\](.*?)\\[/com-link\\]'si", "" );
	
	if( $is_logged ) {
		
			$tpl->set( '{favorites}', "{-favorites-{$row['id']}}" );
			$tpl->set( '[add-favorites]', "[add-favorites-{$row['id']}]" );
			$tpl->set( '[/add-favorites]', "[/add-favorites-{$row['id']}]" );
			$tpl->set( '[del-favorites]', "[del-favorites-{$row['id']}]" );
			$tpl->set( '[/del-favorites]', "[/del-favorites-{$row['id']}]" );
	
	} else {
		
		$tpl->set( '{favorites}', "" );
		$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
		$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
		
	}

	if ($user_group[$member_id['user_group']]['allow_complaint_news']) {
		$tpl->set('[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'news')\">");
		$tpl->set('[/complaint]', "</a>");
	} else {
		$tpl->set_block("'\\[complaint\\](.*?)\\[/complaint\\]'si", "");
	}
		
	$row['xfields'] = stripslashes( $row['xfields'] );

	if( $xfound AND count($xfields) ) {
		$row['xfields_array'] = xfieldsdataload( $row['xfields'] );
	}
	
	if( $xfound AND count($xfields) ) {
		$xfieldsdata = $row['xfields_array'];

		$tpl->copy_template = preg_replace_callback( "#\\[ifxf(set|notset) fields=['\"](.+?)['\"]\\](.+?)\[/ifxf\\1\]#is",
			function ($matches) use ($xfieldsdata) {

				if(!isset($matches[1]) OR !isset($matches[2]) OR !isset($matches[3]) OR !$matches[1] OR !$matches[2] OR !$matches[3]) {
					return $matches[0];
				}

				$matches[2] = trim($matches[2]);
				$fields_arr = explode(',', $matches[2]);
				$found = 0;

				foreach ( $fields_arr as $field ) {
					$field  = trim($field);
					
					if ($matches[1] == 'set') {
						
						if( isset($xfieldsdata[$field]) AND strlen( trim( (string)$xfieldsdata[$field] ) ) > 0 AND trim((string)$xfieldsdata[$field]) != '<p><br></p>' ) $found++;

					} elseif ($matches[1] == 'notset') {

						if (!isset($xfieldsdata[$field]) OR strlen( trim( (string)$xfieldsdata[$field] ) ) < 1 OR trim((string)$xfieldsdata[$field]) == '<p><br></p>' ) $found++;

					}


				}

				if ($found == count($fields_arr) ) return $matches[3];
				else return '';

			},
		$tpl->copy_template);

		foreach ( $xfields as $value ) {
			$preg_safe_name = preg_quote( $value[0], "'" );
			
			if( $value[20] ) {
			  
			  $value[20] = explode( ',', $value[20] );
			  
			  if( $value[20][0] AND !in_array( $member_id['user_group'], $value[20] ) ) {
				  $xfieldsdata[$value[0]] = "";
			  }
			  
			}
		
			if ( $value[3] == "yesorno" ) {
				
				if( isset($xfieldsdata[$value[0]]) AND intval($xfieldsdata[$value[0]]) ) {
					$xfgiven = true;
					$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
				} else {
					$xfgiven = false;
					$xfieldsdata[$value[0]] = $lang['xfield_xno'];
				}
				
			} else {
				
				if( isset($xfieldsdata[$value[0]]) AND $xfieldsdata[$value[0]] ) $xfgiven = true; else $xfgiven = false;
				
			}
			
			if( !$xfgiven ) {
				$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_ireplace( "[xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_ireplace( "[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
			} else {
				$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_ireplace( "[xfgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_ireplace( "[/xfgiven_{$value[0]}]", "", $tpl->copy_template );
			}

			if(strpos( $tpl->copy_template, "[ifxfvalue {$value[0]}" ) !== false ) {
				$tpl->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", 
					function ($matches) use ($xfieldsdata, $preg_safe_name, $value) {

						$matches[1] = trim($matches[1]);
						$check_values = array();

						if (($value[3] == "select" or $value[6]) and isset($xfieldsdata[$value[0]]) and $xfieldsdata[$value[0]]) {
							$field_value = explode(",", $xfieldsdata[$value[0]]);
							$field_value = array_map('trim', $field_value);
						} elseif (isset($xfieldsdata[$value[0]])) {
							$field_value = $xfieldsdata[$value[0]];
						} else $field_value = '';

						if (preg_match("#^{$preg_safe_name}\s*\!\=\s*['\"](.+?)['\"]#i", $matches[1], $match)) {

							$check_values = array_map('trim', explode(",", trim($match[1])));

							if (is_array($field_value)) {

								$found = false;

								foreach ($field_value as $tenp_value) {
									if (in_array($tenp_value, $check_values)) {
										$found = true;
									}
								}

								if ($found) return "";
								else return $matches[2];
							} else {

								if (!in_array($field_value, $check_values)) {
									return $matches[2];
								} else return "";
							}
						}

						if (preg_match("#^{$preg_safe_name}\s*\=\s*['\"](.+?)['\"]#i", $matches[1], $match)) {

							$check_values = array_map('trim', explode(",", trim($match[1])));

							if (is_array($field_value)) {

								$found = false;

								foreach ($field_value as $tenp_value) {
									if (in_array($tenp_value, $check_values)) {
										$found = true;
									}
								}

								if ($found) {
									return $matches[2];
								} else return "";
							} else {

								if (in_array($field_value, $check_values)) {
									return $matches[2];
								} else return "";
							}
						}
						
						return $matches[0];
					}, 
				$tpl->copy_template );
			}

			if ($value[3] == "select" and isset($xfieldsdata[$value[0]]) and $xfieldsdata[$value[0]] and !$value[6]) {
				$xfieldsdata[$value[0]] = explode(',', $xfieldsdata[$value[0]]);
				$xfieldsdata[$value[0]] = implode($value[35], $xfieldsdata[$value[0]]);
			}

			if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
				$temp_array = explode( ",", $xfieldsdata[$value[0]] );
				$value3 = array();

				foreach ($temp_array as $value2) {

					$value2 = trim($value2);
					$value2 = str_replace('&amp;#x2C;', ',', $value2);
					
					if($value2) {
						
						$value4 = str_replace(array("&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;", "/"), array("'", '"', "&", "{", "[", ":", "&frasl;"), $value2);
						
						if( $value[3] == "datetime" ) {
						
							$value2 = strtotime( $value4 );
						
							if( !trim($value[24]) ) $value[24] = $config['timestamp_active'];

							if (strpos($tpl->copy_template, "[xfvalue_{$value[0]} format=") !== false) {

								$tpl->copy_template = preg_replace_callback("#\\[xfvalue_{$preg_safe_name} format=['\"](.*?)['\"]\\]#i",
									function ($matches) use ($value, $value2, $value4, $customlangdate, $config, $PHP_SELF) {

										$matches[1] = trim($matches[1]);

										if ($value[25]) {

											if ($value[26]) $value2 = langdate($matches[1], $value2);
											else return $value2 = langdate($matches[1], $value2, false, $customlangdate);
										} else $value2 = date($matches[1], $value2);

										if ($config['allow_alt_url']) return "<a href=\"" . $config['http_home_url'] . "xfsearch/" . $value[0] . "/" . rawurlencode(dle_strtolower($value4)) . "/\">" . $value2 . "</a>";
										else return "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=" . $value[0] . "&amp;xf=" . rawurlencode(dle_strtolower($value4)) . "\">" . $value2 . "</a>";

									}, $tpl->copy_template);
							}

							if( $value[25] ) {
								
								if($value[26]) $value2 = langdate($value[24], $value2);
								else $value2 = langdate($value[24], $value2, false, $customlangdate);
								
							} else $value2 = date( $value[24], $value2 );

						}

						if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". rawurlencode( dle_strtolower($value4) ) . "/\">" . $value2 . "</a>";
						else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . rawurlencode( dle_strtolower($value4) ) . "\">" . $value2 . "</a>";
						
					}

				}
				
				if ($value[3] == "select" and $value[35]) {
					$value[21] = $value[35];
				}

				if( empty($value[21]) ) $value[21] = ", ";
				
				$xfieldsdata[$value[0]] = implode($value[21], $value3);

				unset($temp_array);
				unset($value2);
				unset($value3);
				unset($value4);

			} elseif ( $value[3] == "datetime" AND !empty($xfieldsdata[$value[0]]) ) {

				$xfieldsdata[$value[0]] = strtotime( str_replace("&#58;", ":", $xfieldsdata[$value[0]]) );

				if( !trim($value[24]) ) $value[24] = $config['timestamp_active'];

				if (strpos ( $tpl->copy_template, "[xfvalue_{$value[0]} format=" ) !== false) {
					
					$tpl->copy_template = preg_replace_callback ( "#\\[xfvalue_{$preg_safe_name} format=['\"](.*?)['\"]\\]#i", 
						function ($matches) use ($value, $xfieldsdata, $customlangdate) {
							
							$matches[1] = trim($matches[1]);
							
							if ($value[25]) {

								if ($value[26]) return langdate($matches[1], $xfieldsdata[$value[0]]);
								else return langdate($matches[1], $xfieldsdata[$value[0]], false, $customlangdate);

							} else return date($matches[1], $xfieldsdata[$value[0]]);

							
						}, $tpl->copy_template );
						
				}

				if( $value[25] ) {
					
					if($value[26]) $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]]);
					else $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]], false, $customlangdate);
								
				} else $xfieldsdata[$value[0]] = date( $value[24], $xfieldsdata[$value[0]] );
				
				
			}
			
			if ($value[3] == "select" and isset($xfieldsdata[$value[0]]) and $xfieldsdata[$value[0]]) {
				$xfieldsdata[$value[0]] = str_replace('&amp;#x2C;', ',', $xfieldsdata[$value[0]]);
			}

			if($value[3] == "image" AND isset($xfieldsdata[$value[0]]) AND $xfieldsdata[$value[0]] ) {
				
				$temp_array = explode('|', $xfieldsdata[$value[0]]);
					
				if (count($temp_array) == 1 OR count($temp_array) == 5 ){
						
					$temp_alt = '';
					$temp_value = implode('|', $temp_array );
						
				} else {
						
					$temp_alt = $temp_array[0];
					$temp_alt = str_replace( "&amp;#44;", "&#44;", $temp_alt );
					$temp_alt = str_replace( "&amp;#124;", "&#124;", $temp_alt );
					
					unset($temp_array[0]);
					$temp_value =  implode('|', $temp_array );
						
				}

				$path_parts = get_uploaded_image_info($temp_value);
				
				if( $value[12] AND $path_parts->thumb ) {
					
					$tpl->set( "[xfvalue_thumb_url_{$value[0]}]", $path_parts->thumb);
					$xfieldsdata[$value[0]] = "<a href=\"{$path_parts->url}\" data-highslide=\"single\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a>";

				} else {
					
					$tpl->set( "[xfvalue_thumb_url_{$value[0]}]", $path_parts->url);
					$xfieldsdata[$value[0]] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->url}\" alt=\"{$temp_alt}\">";

				}
				
				$tpl->set( "[xfvalue_image_url_{$value[0]}]", $path_parts->url);
				$tpl->set( "[xfvalue_image_description_{$value[0]}]", $temp_alt);
				
				if( $value[28] ) {
					if( !$path_parts->thumb ) $path_parts->thumb = $path_parts->url;
						
					$xfields_in_news['[xfvalue_image_url_'.$value[0].']'] = $path_parts->url;
					$xfields_in_news['[xfvalue_image_description_'.$value[0].']'] = $temp_alt;
					$xfields_in_news['[xfvalue_thumb_url_'.$value[0].']'] = $path_parts->thumb;
				}
				
			}
			
			$xfieldsdata[$value[0]] = isset($xfieldsdata[$value[0]]) ? $xfieldsdata[$value[0]] : '';
			
			if($value[3] == "image" AND !$xfieldsdata[$value[0]]) {
				$tpl->set( "[xfvalue_thumb_url_{$value[0]}]", "");
				$tpl->set( "[xfvalue_image_url_{$value[0]}]", "");
				$tpl->set( "[xfvalue_image_description_{$value[0]}]", "");
			}

			if (($value[3] == "video" or $value[3] == "audio") and $xfieldsdata[$value[0]]) {

				$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
				$playlist = array();
				$playlist_single = array();
				$xf_playlist_count = 0;

				if ($value[3] == "audio") {
					$xftag = "audio";
					$xftype = "audio/mp3";
				} else {
					$xftag = "video";
					$xftype = "video/mp4";
				}

				if (!isset($video_config)) {
					include(ENGINE_DIR . '/data/videoconfig.php');
				}
				
				if ($video_config['preload']) $preload = "metadata";
				else $preload = "none";

				$playlist_width = $video_config['width'];

				if (substr($playlist_width, -1, 1) != '%') $playlist_width = $playlist_width . "px";

				$playlist_width = "style=\"width:100%;max-width:{$playlist_width};\"";

				foreach ($fieldvalue_arr as $temp_value) {

					$xf_playlist_count++;

					$temp_value = trim($temp_value);

					if (!$temp_value) continue;

					$temp_array = explode('|', $temp_value);

					if (count($temp_array) < 4) {

						$temp_alt = '';
						$temp_url = $temp_array[0];
					} else {

						$temp_alt = $temp_array[0];
						$temp_url = $temp_array[1];
					}

					$filename = pathinfo($temp_url, PATHINFO_FILENAME);
					$filename = explode("_", $filename);
					if (count($filename) > 1 and intval($filename[0])) unset($filename[0]);
					$filename = implode("_", $filename);

					if (!$temp_alt) $temp_alt = $filename;

					$playlist[] = "<{$xftag} title=\"{$temp_alt}\" preload=\"{$preload}\" controls><source type=\"{$xftype}\" src=\"{$temp_url}\"></{$xftag}>";
					$playlist_single['[xfvalue_' . $value[0] . ' ' . $xftag . '="' . $xf_playlist_count . '"]'] = "<div class=\"dleplyrplayer\" {$playlist_width} theme=\"{$video_config['theme']}\"><{$xftag} title=\"{$temp_alt}\" preload=\"{$preload}\" controls><source type=\"{$xftype}\" src=\"{$temp_url}\"></{$xftag}></div>";

					$playlist_single['[xfvalue_' . $value[0] . ' ' . $xftag . '-description="' . $xf_playlist_count . '"]'] = $temp_alt;
					$playlist_single['[xfvalue_' . $value[0] . ' ' . $xftag . '-url="' . $xf_playlist_count . '"]'] = $temp_url;

					$tpl->copy_template = str_ireplace('[xfgiven_' . $value[0] . ' ' . $xftag . '="' . $xf_playlist_count . '"]', "", $tpl->copy_template);
					$tpl->copy_template = str_ireplace('[/xfgiven_' . $value[0] . ' ' . $xftag . '="' . $xf_playlist_count . '"]', "", $tpl->copy_template);
					$tpl->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name} {$xftag}=\"{$xf_playlist_count}\"\\](.*?)\\[/xfnotgiven_{$preg_safe_name} {$xftag}=\"{$xf_playlist_count}\"\\]'is", "", $tpl->copy_template);
				}

				if (count($playlist_single)) {

					foreach ($playlist_single as $temp_key => $temp_value) {

						$tpl->set($temp_key, $temp_value);

						if ($value[28]) {
							$xfields_in_news[$temp_key] = $temp_value;
						}
					}
				}

				$xfieldsdata[$value[0]] = "<div class=\"dleplyrplayer\" {$playlist_width} theme=\"{$video_config['theme']}\">" . implode($playlist) . "</div>";
			}
			
			if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] ) {
				
				$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
				$gallery_image = array();
				$gallery_single_image = array();
				$xf_image_count = 0;
				
				foreach ($fieldvalue_arr as $temp_value) {
					
					$xf_image_count ++;
					
					$temp_value = trim($temp_value);
			
					if($temp_value == "") continue;
					
					$temp_array = explode('|', $temp_value);
					
					if (count($temp_array) == 1 OR count($temp_array) == 5 ){
							
						$temp_alt = '';
						$temp_value = implode('|', $temp_array );
							
					} else {
							
						$temp_alt = $temp_array[0];
						$temp_alt = str_replace( "&amp;#44;", "&#44;", $temp_alt );
						$temp_alt = str_replace( "&amp;#124;", "&#124;", $temp_alt );
						
						unset($temp_array[0]);
						$temp_value =  implode('|', $temp_array );
							
					}

					$path_parts = get_uploaded_image_info($temp_value);
				
					if($value[12] AND $path_parts->thumb) {
						
						$gallery_image[] = "<li><a href=\"{$path_parts->url}\" data-highslide=\"xf_{$row['id']}_{$value[0]}\" target=\"_blank\"><img src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a></li>";
						$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<a href=\"{$path_parts->url}\" data-highslide=\"single\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a>";
							
					} else {
						
						$gallery_image[] = "<li><img src=\"{$path_parts->url}\" alt=\"{$temp_alt}\"></li>";
						$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->url}\" alt=\"{$temp_alt}\">";

					}
					
					if( !$path_parts->thumb ) $path_parts->thumb = $path_parts->url;
					
					$gallery_single_image['[xfvalue_'.$value[0].' image-description="'.$xf_image_count.'"]'] = $temp_alt;
					$gallery_single_image['[xfvalue_'.$value[0].' image-thumb-url="'.$xf_image_count.'"]'] = $path_parts->thumb;
					$gallery_single_image['[xfvalue_'.$value[0].' image-url="'.$xf_image_count.'"]'] = $path_parts->url;

					$tpl->copy_template = str_ireplace( '[xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl->copy_template );
					$tpl->copy_template = str_ireplace( '[/xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl->copy_template );
					$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\](.*?)\\[/xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\]'is", "", $tpl->copy_template );

				}
				
				foreach($gallery_single_image as $temp_key => $temp_value) {
					
					$tpl->set( $temp_key, $temp_value);
					
					if( $value[28] ) {
						$xfields_in_news[$temp_key] = $temp_value;
					}
					
				}
				
				$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">".implode($gallery_image)."</ul>";
				
			}
			
			$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\](.*?)\\[/xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[/xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl->copy_template );	

			if ( $value[30] ) $xfieldsdata[$value[0]] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $xfieldsdata[$value[0]] );

			$tpl->set( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]]);
			
			if( $value[28] ) {
				$xfields_in_news['[xfvalue_'.$value[0].']'] = $xfieldsdata[$value[0]];
			}
			
			if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches ) ) {
				$tpl->set( $matches[0], clear_content($xfieldsdata[$value[0]], $matches[1]) );
			} 

		}
	}
	

	$row['title'] = stripslashes( $row['title'] );
	$tpl->set( '{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['title'], ENT_QUOTES, 'UTF-8' ) ) );

	if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
		$tpl->set( $matches[0], clear_content($row['title'], $matches[1]) );
	}

	if ( isset($smartphone_detected) AND $smartphone_detected) {

		if (!$config['allow_smart_format']) {

				$row['short_story'] = strip_tags( $row['short_story'], '<p><div><br><a>' );

		} else {


			if ( !$config['allow_smart_images'] ) {
	
				$row['short_story'] = preg_replace( "#<!--TBegin(.+?)<!--TEnd-->#is", "", $row['short_story'] );
				$row['short_story'] = preg_replace( "#<!--MBegin(.+?)<!--MEnd-->#is", "", $row['short_story'] );
				$row['short_story'] = preg_replace( "#<img(.+?)>#is", "", $row['short_story'] );
	
			}
	
			if ( !$config['allow_smart_video'] ) {
	
				$row['short_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['short_story'] );
				$row['short_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['short_story'] );
				$row['short_story'] = preg_replace( "#<!--dle_media_begin(.+?)<!--dle_media_end-->#is", "", $row['short_story'] );
	
			}

		}

	}

	$row['short_story'] = stripslashes( $row['short_story'] );

	if (stripos ( $tpl->copy_template, "image-" ) !== false) {

		$images = array();
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'].$row['xfields'], $media);
		$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
		$img_arr = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp', 'avif', 'svg');

		foreach($data as $url) {
			$info = pathinfo($url);
			if (isset($info['extension'])) {
				if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" OR strpos($info['dirname'], 'engine/data/emoticons') !== false) continue;
				$info['extension'] = strtolower($info['extension']);
				if ( in_array($info['extension'], $img_arr) ) array_push($images, $url);
			}
		}

		if ( count($images) ) {
			$i_count=0;
			foreach($images as $url) {
				$i_count++;
				$tpl->copy_template = str_replace( '{image-'.$i_count.'}', $url, $tpl->copy_template );
				$tpl->copy_template = str_replace( '[image-'.$i_count.']', "", $tpl->copy_template );
				$tpl->copy_template = str_replace( '[/image-'.$i_count.']', "", $tpl->copy_template );
				$tpl->copy_template = preg_replace( "#\[not-image-{$i_count}\](.+?)\[/not-image-{$i_count}\]#is", "", $tpl->copy_template );
			}

		}

		$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "#\[not-image-(.+?)\]#i", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "#\[/not-image-(.+?)\]#i", "", $tpl->copy_template );

	}

	if ($config['image_lazy']) $row['short_story'] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $row['short_story'] );
	
	$tpl->set( '{short-story}', $row['short_story'] );

	if ( preg_match( "#\\{short-story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
		$tpl->set( $matches[0], clear_content($row['short_story'], $matches[1]) );
	}
	
	if( $config['user_in_news'] ) {
		include (DLEPlugins::Check(ENGINE_DIR . '/modules/profile_innews.php'));
	}
		
	$tpl->compile( 'content', true, false );

	if(isset($xfields_in_news) AND is_array($xfields_in_news) AND count($xfields_in_news) ) {
		
		if (stripos ( $tpl->result['content'], "[xf" ) !== false ) {
			
			foreach ( $xfields_in_news as $key => $value) {
				$tpl->result['content'] = str_replace ( $key, $value, $tpl->result['content'] );
			}
			
		}
		
		$xfields_in_news = array();
	}
}

if( !$news_found) {
	
	if( preg_match( "'\\[not-news\\](.*?)\\[/not-news\\]'si", $tpl->copy_template, $match ) ) {
		$tpl->result['content'] = $match[1];
	}

}

if (stripos ( $tpl->result['content'], "[hide" ) !== false ) {
		
	$tpl->result['content'] = preg_replace_callback ( "#\[hide(.*?)\](.+?)\[/hide\]#is", 
		function ($matches) use ($member_id, $user_group, $lang) {
			
			$matches[1] = str_replace(array("=", " "), "", $matches[1]);
			$matches[2] = $matches[2];

			if( $matches[1] ) {
				
				$groups = explode( ',', $matches[1] );

				if( in_array( $member_id['user_group'], $groups ) OR $member_id['user_group'] == "1") {
					return $matches[2];
				} else return "<div class=\"quote dlehidden\">" . $lang['news_regus'] . "</div>";
				
			} else {
				
				if( $user_group[$member_id['user_group']]['allow_hide'] ) return $matches[2]; else return "<div class=\"quote dlehidden\">" . $lang['news_regus'] . "</div>";
				
			}

	}, $tpl->result['content'] );
}

$tpl->result['content'] = str_ireplace( "{PAGEBREAK}", '', $tpl->result['content'] );
	
if ($config['allow_links'] AND function_exists('replace_links') AND isset($replace_links['news'])){
	$tpl->result['content'] = replace_links($tpl->result['content'], $replace_links['news']);
}

if ( $config['allow_banner'] AND isset($banner_in_news) AND is_array($banner_in_news) AND count($banner_in_news) ){
	
	foreach ( $banner_in_news as $name) {
		$tpl->result['content'] = str_replace( "{banner_" . $name . "}", $banners[$name], $tpl->result['content'] );
	
		if( $banners[$name] ) {
			$tpl->result['content'] = str_replace ( "[banner_" . $name . "]", "", $tpl->result['content'] );
			$tpl->result['content'] = str_replace ( "[/banner_" . $name . "]", "", $tpl->result['content'] );
		}
	}

	$tpl->result['content'] = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", '', $tpl->result['content'] );

}

$tpl->clear();
$db->free( $sql_result );

if ( $build_navigation AND $sql_count) {

	$count_all = $db->super_query( $sql_count );
	
	if($news_found AND !$count_all['count']) {
		$db->query("ANALYZE TABLE `" . PREFIX . "_post`, `" . PREFIX . "_post_extras`");
		$count_all = $db->super_query( $sql_count );
	}
		
	$count_all = $count_all['count'] - $custom_all;

}

if( $build_navigation AND $count_all AND $news_found) {

		$tpl->load_template( 'navigation.tpl' );
		
		//----------------------------------
		// Previous link
		//----------------------------------
		
		$no_prev = false;
		$no_next = false;
		if (isset ( $_GET['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 1;
		
		if( isset( $cstart ) and $cstart != "" and $cstart > 1 ) {
			$prev = $cstart - 1;

			if( $config['allow_alt_url'] ) {

				if ($prev == 1)
					$prev_page = $url_page . "/";
				else
					$prev_page = $url_page . "/page/" . $prev . "/";

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );

			} else {
				
				if ($prev == 1) {
					
					if ($user_query) $prev_page = $PHP_SELF . "?" . $user_query;
					else $prev_page = $config['http_home_url'];
					
				} else {
					
					if ($user_query) $prev_page = $PHP_SELF . "?cstart=" . $prev . "&amp;" . $user_query;
					else $prev_page = $PHP_SELF . "?cstart=" . $prev;
				}

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}
		
		//----------------------------------
		// Pages
		//----------------------------------
		if( $custom_limit ) {

			$pages = "";
			
			if( $count_all > $custom_limit ) {
				
				$enpages_count = @ceil( $count_all / $custom_limit );

				if ($tpl->smartphone) {
					$max_pages = 5;
				} else {
					$max_pages = 10;
				}

				if( $enpages_count <= $max_pages ) {
					
					for($j = 1; $j <= $enpages_count; $j ++) {
						
						if( $j != $cstart ) {
							
							if( $config['allow_alt_url'] ) {

								if ($j == 1)
									$pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
								else
									$pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";

							} else {

								if ($j == 1) {
									
									if ($user_query) {
										$pages .= "<a href=\"{$PHP_SELF}?{$user_query}\">$j</a> ";
									} else $pages .= "<a href=\"{$config['http_home_url']}\">$j</a> ";
									
								} else {
									
									if ($user_query) {
										$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
									} else $pages .= "<a href=\"$PHP_SELF?cstart=$j\">$j</a> ";
									
								}

							}
						
						} else {
							
							$pages .= "<span>$j</span> ";
						}
					
					}
				
				} else {

					$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

					if ($tpl->smartphone) {

						$start = 1;
						$end = 3;

						if ($cstart > 0) {

							if ($cstart > 2) {

								$start = $cstart - 1;
								$end = $start + 2;

								if ($end >= $enpages_count) {
									$start = $enpages_count - 2;
									$end = $enpages_count - 1;
								}
							}
						}
						
					} else {

						$start = 1;
						$end = 10;

						if ($cstart > 0) {

							if ($cstart > 6) {

								$start = $cstart - 4;
								$end = $start + 8;

								if ($end >= $enpages_count - 1) {
									$start = $enpages_count - 9;
									$end = $enpages_count - 1;
								}
							}
						}
					}
					
					if( $end >= $enpages_count-1 ) $nav_prefix = ""; else $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $start >= 2 ) {
						
						if( $start >= 3 ) $before_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> "; else $before_prefix = "";

						if( $config['allow_alt_url'] ) $pages .= "<a href=\"" . $url_page . "/\">1</a> ".$before_prefix;
						else {
							if($user_query) $pages .= "<a href=\"$PHP_SELF?{$user_query}\">1</a> ".$before_prefix;
							else $pages .= "<a href=\"{$config['http_home_url']}\">1</a> ".$before_prefix;
						}
					
					}
					
					for($j = $start; $j <= $end; $j ++) {
						
						if( $j != $cstart ) {

							if( $config['allow_alt_url'] ) {

								if ($j == 1)
									$pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
								else
									$pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";

							} else {

								if ($j == 1) {
									
									if ($user_query) {
										$pages .= "<a href=\"{$PHP_SELF}?{$user_query}\">$j</a> ";
									} else $pages .= "<a href=\"{$config['http_home_url']}\">$j</a> ";
									
								} else {
									
									if ($user_query) {
										$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
									} else $pages .= "<a href=\"$PHP_SELF?cstart=$j\">$j</a> ";
									
								}

							}
						
						} else {
							
							$pages .= "<span>$j</span> ";
						}
					
					}
					
					if( $cstart != $enpages_count ) {
						if( $config['allow_alt_url'] ) {
							
							$pages .= $nav_prefix . "<a href=\"" . $url_page . "/page/{$enpages_count}/\">{$enpages_count}</a>";
							
						} else {
							
							if ($user_query) $pages .= $nav_prefix . "<a href=\"$PHP_SELF?cstart={$enpages_count}&amp;$user_query\">{$enpages_count}</a>";
							else $pages .= $nav_prefix . "<a href=\"$PHP_SELF?cstart={$enpages_count}\">{$enpages_count}</a>";
							
						}
					
					} else
						$pages .= "<span>{$enpages_count}</span> ";
				
				}
			
			}
			$tpl->set( '{pages}', $pages );
		}
		
		//----------------------------------
		// Next link
		//----------------------------------

		if( $custom_limit AND $custom_limit < $count_all AND $cstart < $enpages_count ) {
			$next_page = $cstart + 1;
			
			if( $config['allow_alt_url'] ) {
				$next = $url_page . '/page/' . $next_page . '/';
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
			} else {
				
				if ($user_query) $next = $PHP_SELF . "?cstart=" . $next_page . "&amp;" . $user_query;
				else $next = $PHP_SELF . "?cstart=" . $next_page;
				
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}
		
		if( !$no_prev OR !$no_next ) {
			
			$tpl->compile( 'navigation' );

			switch ( $config['news_navigation'] ) {

				case "2" :
					
					$tpl->result['content'] = '{newsnavigation}'.$tpl->result['content'];
					break;

				case "3" :
					
					$tpl->result['content'] = '{newsnavigation}'.$tpl->result['content'].'{newsnavigation}';
					break;

				default :
					$tpl->result['content'] .= '{newsnavigation}';
					break;
			
			}
			
			if ( !defined('CUSTOMNAVIGATION') ) {
				define('CUSTOMNAVIGATION', true);
				$custom_navigation = $tpl->result['navigation'];
			}
		
		} else $tpl->result['navigation'] = "";
	
		$tpl->clear();

} else $tpl->result['navigation'] = "";

?>