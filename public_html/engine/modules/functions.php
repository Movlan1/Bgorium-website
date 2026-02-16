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
 File: functions.php
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if ( isset($config['auth_domain']) AND $config['auth_domain'] ) {

	$domain_cookie = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
	$domain_cookie_count = count($domain_cookie);
	$domain_allow_count = -2;
	
	if ( $domain_cookie_count > 2 ) {
	
		if ( in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) $domain_allow_count = -3;
		if ( $domain_cookie[$domain_cookie_count-1] == 'ua' ) $domain_allow_count = -3;
		
		$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
	}
	
	$domain_cookie = "." . implode (".", $domain_cookie);
	
	if( ip2long($_SERVER['HTTP_HOST']) == -1 OR ip2long($_SERVER['HTTP_HOST']) === false ) define( 'DOMAIN', $domain_cookie );
	else define( 'DOMAIN', '' );

} else define( 'DOMAIN', '' );

function dle_session( $sid = false ) {
	global $config;
	
	$params = session_get_cookie_params();

	if ( DOMAIN ) $params['domain'] = DOMAIN;
	
	if (isset($config['only_ssl']) AND $config['only_ssl']) $params['secure'] = true;

	session_set_cookie_params($params['lifetime'], "/", $params['domain'], $params['secure'], true);

	if ( $sid ) session_id( $sid );

	session_start();

}

function set_cookie($name, $value, $expires) {
	global $config;
	
	if( $expires ) {
		
		$expires = time() + ($expires * 86400);
	
	} else {
		
		$expires = FALSE;
	
	}
	
	if ($config['only_ssl']) setcookie( $name, $value, $expires, "/", DOMAIN, TRUE, TRUE );
	else setcookie( $name, $value, $expires, "/", DOMAIN, FALSE, TRUE );

}

function formatsize($file_size) {
	
	if( !$file_size OR $file_size < 1) return '0 b';
	
    $prefix = array("b", "Kb", "Mb", "Gb", "Tb");
    $exp = floor(log($file_size, 1024)) | 0;

    $file_size = round($file_size / (pow(1024, $exp)), 2).' '.$prefix[$exp];
	$file_size = str_replace(",", ".", $file_size);

    return $file_size;

}

class microTimer {
	private $time;

	function __construct() {
		$this->time = $this->get_real_time();
	}
	function get() {
		return round( ($this->get_real_time() - $this->time), 5 );
	}

	function get_real_time() {
		list ( $seconds, $microSeconds ) = explode( ' ', microtime() );
		return (( float ) $seconds + ( float ) $microSeconds);
	}
}

function flooder($ip, $news_time = false) {
	global $db, $user_group, $member_id, $_TIME;
	
	$ip = $db->safesql($ip);
	
	if ( $news_time ) {

		$this_time = $_TIME - intval($news_time);
		$db->query( "DELETE FROM " . PREFIX . "_flood WHERE id < '$this_time' AND flag='1' " );
		
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_flood WHERE ip = '{$ip}' AND flag='1'");
		
		if( $row['count'] ) return true;
		else return false;

	} else {

		$this_time = $_TIME - intval($user_group[$member_id['user_group']]['flood_time']);
		$db->query( "DELETE FROM " . PREFIX . "_flood WHERE id < '{$this_time}' AND ip = '{$ip}' AND flag='0'" );
		
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_flood WHERE ip = '{$ip}' AND flag='0'");
		
		if( $row['count'] ) return true;
		else return false;

	}

}

function totranslit($var, $lower = true, $punkt = true, $translit = true ) {
	global $langtranslit;
	
	if ( !is_string($var) ) return "";

	$bads = array( '!', '*', '\'', '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '#', '[', ']', '%', '\\', '"', '<', '>', '^', '{', '}', '|', '`', '.php' );
	
	$var = strip_tags( $var );
	$var = str_replace(chr(0), '', $var);

	$var = html_entity_decode($var, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	
	if ( $lower ) {
		$var = dle_strtolower($var);	
	}
	
	$var = str_replace( array( "\r\n", "\r", "\n" ), ' ', $var );
	$var = preg_replace( "/\s+/u", "-", $var );

	if ( !$punkt ) {
		$bads[] = '.';
	}
	
	$var = str_ireplace( $bads, '', $var );
	
	if( $translit ) {
		
		if (is_array($langtranslit) AND count($langtranslit) ) {
			$var = strtr($var, $langtranslit);
		}
		
		if ( $punkt ) {
			
			$var = preg_replace( "/[^a-z0-9\_\-.]+/mi", '', $var );
			$var = preg_replace( '#[.]+#i', '.', $var );
			
		} else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", '', $var );
	
	}
	
	$var = str_ireplace( ".php", ".ppp", $var );
	$var = preg_replace( '/\-+/', '-', $var );
	
	if( dle_strlen( $var ) > 250 ) {
		
		$var = dle_substr( $var, 0, 250 );
		
		if( ($temp_max = dle_strrpos( $var, '-' )) ) $var = dle_substr( $var, 0, $temp_max );
	
	}
    
	$var = trim( $var, '-' );
    $var = trim( $var );
	
	return $var;
}

function timezone_list(){
	static $timezones = null;

	if ($timezones === null) {
		$timezones = array();
		$offsets = array();
		$now = new DateTime('now', new DateTimeZone('UTC'));

		foreach (DateTimeZone::listIdentifiers() as $timezone) {
			try {

				$now->setTimezone(new DateTimeZone($timezone));
				$offsets[] = $offset = $now->getOffset();
				$timezones[$timezone] = '(' . format_GMT_offset($offset) . ') ' . format_timezone_name($timezone);
				
			} catch (Throwable $e) {}
		}

		array_multisort($offsets, $timezones);
	}

	return $timezones;
}

function format_GMT_offset($offset) {
	$hours = intval($offset / 3600);
	$minutes = abs(intval($offset % 3600 / 60));
	return 'GMT' . ($offset !== false ? sprintf('%+03d:%02d', $hours, $minutes) : '');
}

function format_timezone_name($name) {
	$name = str_replace('/', ', ', $name);
	$name = str_replace('_', ' ', $name);
	$name = str_replace('St ', 'St. ', $name);
	return $name;
}

function compare_days_date( $news_date,  $servertime = false,  $comparehours = false ) {
	global $_TIME, $member_id;

	if (!$news_date) {
		$news_date = time();
	}

	$newsdate = new DateTime('@' . $news_date);
	$nowdate   = new DateTime('@' . $_TIME);
	$yesterdaydate = new DateTime('-1 day');

	if (isset($member_id['timezone']) and $member_id['timezone'] and !$servertime) {
		$localzone = $member_id['timezone'];
	} else {
		$localzone = date_default_timezone_get();
	}

	if ( !in_array( $localzone, DateTimeZone::listIdentifiers() ) ) $localzone = 'Europe/Moscow';

	$newsdate->setTimeZone(new DateTimeZone($localzone));
	$nowdate->setTimeZone(new DateTimeZone($localzone));
	$yesterdaydate->setTimeZone(new DateTimeZone($localzone));

	$diff = $newsdate->diff($nowdate);

	if( $comparehours ) {
		
		$hours = $diff->h;
		$hours = $hours + ($diff->days * 24);

		return $hours;
		
	} else {

		$days = intval($diff->format('%a'));
	
		if ($newsdate->format('Ymd') == $nowdate->format('Ymd')) {
			return 0;
		}

		if ($newsdate->format('Ymd') == $yesterdaydate->format('Ymd')) {
			return 1;
		}

		if($days == 1 ) {
			return $days + 1;
		} else return $days;

	}

}

function langdate($format, $stamp, $servertime = false, $custom = false ) {
	global $langdate, $member_id, $customlangdate;

	if( is_array($custom) ) $locallangdate = $customlangdate; else $locallangdate = $langdate;

	if( !is_array($locallangdate) ) {
		$locallangdate = array();
	}	
	
	if (!$stamp) { $stamp = time(); }
	
	$local = new DateTime('@'.$stamp);

	if (isset($member_id['timezone']) AND $member_id['timezone'] AND !$servertime) {
		$localzone = $member_id['timezone'];

	} else {

		$localzone = date_default_timezone_get();
	}

	if ( !in_array( $localzone, DateTimeZone::listIdentifiers() ) ) $localzone = 'Europe/Moscow';

	$local->setTimeZone(new DateTimeZone($localzone));

	return strtr( $local->format($format), $locallangdate );

}

function difflangdate($format, $stamp) {
	global $_TIME, $langdate, $member_id, $lang, $langcommentsweekdays;

	if (!is_array($langdate)) {
		$langdate = array();
	}

	if (!is_array($langcommentsweekdays)) {
		$langcommentsweekdays = array();
	}

	if (!$stamp) {
		$stamp = $_TIME;
	}

	$olddate = new DateTime('@' . $stamp);
	$nowdate = new DateTime('@' . $_TIME);
	$yesterdaydate = new DateTime('-1 day');

	if (isset($member_id['timezone']) and $member_id['timezone']) {
		$localzone = $member_id['timezone'];
	} else {

		$localzone = date_default_timezone_get();
	}

	if ( !in_array( $localzone, DateTimeZone::listIdentifiers() ) ) $localzone = 'Europe/Moscow';

	$olddate->setTimeZone(new DateTimeZone($localzone));
	$nowdate->setTimeZone(new DateTimeZone($localzone));
	$yesterdaydate->setTimeZone(new DateTimeZone($localzone));

	$diff = $olddate->diff($nowdate);

	$days    = intval($diff->format('%a') );
	$hours   = intval($diff->format('%h') );
	$minutes = intval($diff->format('%i') );

	if( $olddate->format('Ymd') == $yesterdaydate->format('Ymd') ) {

		$lang_format = str_replace('{date}', $lang['time_gestern'], $lang['diffs_format']);
		$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

		return $lang_format;

	} elseif( $days < 1 ) {

		if ($hours < 1) {

			if( $minutes < 1 ) {

				return $lang['now_diffs'];

			} else {

				return $minutes . ' ' . declination(array('', $minutes, $lang['minutes_diffs'])) . ' ' . $lang['time_diffs'];

			}

		} elseif ($hours <= 12) {

			return $hours . ' ' . declination(array('', $hours, $lang['hours_diffs'])) . ' ' . $lang['time_diffs'];

		} else {

			$lang_format = str_replace('{date}', $lang['time_heute'], $lang['diffs_format']);
			$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

			return $lang_format;

		}

	} else {

		if ($days < 6) {

			$lang_format = str_replace('{date}', $olddate->format('l'), $lang['diffs_format']);
			$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

			return strtr($lang_format, $langcommentsweekdays);

		} else return strtr($olddate->format($format), $langdate);

	}
}

function declination($matches = array())
{

	$matches[1] = strip_tags($matches[1]);
	$matches[1] = str_replace(' ', '', $matches[1]);

	$matches[1] = intval($matches[1]);
	$words = explode('|', trim($matches[2]));
	$parts_word = array();

	switch (count($words)) {
		case 1:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0];
			$parts_word[2] = $words[0];
			break;
		case 2:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0] . $words[1];
			$parts_word[2] = $words[0] . $words[1];
			break;
		case 3:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0] . $words[1];
			$parts_word[2] = $words[0] . $words[2];
			break;
		case 4:
			$parts_word[0] = $words[0] . $words[1];
			$parts_word[1] = $words[0] . $words[2];
			$parts_word[2] = $words[0] . $words[3];
			break;
	}

	$word = $matches[1] % 10 == 1 && $matches[1] % 100 != 11 ? $parts_word[0] : ($matches[1] % 10 >= 2 && $matches[1] % 10 <= 4 && ($matches[1] % 100 < 10 || $matches[1] % 100 >= 20) ? $parts_word[1] : $parts_word[2]);

	return $word;
}

function formdate( $matches=array() ) {
	global $news_date, $customlangdate, $config;
	
	if($config['decline_date']) return langdate($matches[1], $news_date);
	else return langdate($matches[1], $news_date, false, $customlangdate);

}

function check_newscount( $matches=array() ) {
	global $global_news_count;

	$block = $matches[3];

	$counts = explode( ',', trim($matches[2]) );
	
    if( $matches[1] == "newscount" ) {

        if( !in_array($global_news_count, $counts) ) return "";

    } else {

        if( in_array($global_news_count, $counts) ) return "";

    }

	return $block;
	
}

function msgbox($title, $text) {
	global $tpl;

	if (!class_exists('dle_template')) {
	    return;
	}
	
	$tpl_2 = new dle_template( );
	$tpl_2->dir = TEMPLATE_DIR;
	
	$tpl_2->load_template( 'info.tpl' );
	
	$tpl_2->set( '{error}', $text );
	$tpl_2->set( '{title}', $title );
	
	$tpl_2->compile( 'info' );
	$tpl_2->clear();
	
	$tpl->result['info'] .= $tpl_2->result['info'];
}

function ShowRating($id, $rating, $vote_num, $allow = true) {
	global $lang, $config, $row, $dle_module;

	if( !$config['rating_type'] ) {
		
		if( $rating AND $vote_num ) {
			
			$rating = round( ($rating / $vote_num), 0 );
			
		} else {
			$rating = 0;
		}
		
		if ($rating < 0 ) $rating = 0;
		
		$rating = $rating * 20;
	
		if( !$allow ) {
		
			$rated = <<<HTML
<div class="rating">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		</ul>
</div>
HTML;
		
			return $rated;
		}
	
		$rated = <<<HTML
<div data-ratig-layer-id='{$id}'>
	<div class="rating">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		<li><a href="#" title="{$lang['useless']}" class="r1-unit" onclick="doRate('1', '{$id}'); return false;">1</a></li>
		<li><a href="#" title="{$lang['poor']}" class="r2-unit" onclick="doRate('2', '{$id}'); return false;">2</a></li>
		<li><a href="#" title="{$lang['fair']}" class="r3-unit" onclick="doRate('3', '{$id}'); return false;">3</a></li>
		<li><a href="#" title="{$lang['good']}" class="r4-unit" onclick="doRate('4', '{$id}'); return false;">4</a></li>
		<li><a href="#" title="{$lang['excellent']}" class="r5-unit" onclick="doRate('5', '{$id}'); return false;">5</a></li>
		</ul>
	</div>
</div>
HTML;
	
		return $rated;

	} elseif ($config['rating_type'] == "1") {
		
		if( $rating < 0 ) $rating = 0;
		
		if( $allow ) $rated = "<span data-ratig-layer-id=\"{$id}\"><span class=\"ratingtypeplus\" >{$rating}</span></span>";
		else $rated = "<span class=\"ratingtypeplus\" >{$rating}</span>";
		
		return $rated;
	
	} elseif ($config['rating_type'] == "2" OR $config['rating_type'] == "3") {
		
		$extraclass = "ratingzero";
		
		if( $rating < 0 ) {
			$extraclass = "ratingminus";
		}
		
		if( $rating > 0 ) {
			$extraclass = "ratingplus";
			$rating = "+".$rating;
		}
		
		if( $allow ) $rated = "<span data-ratig-layer-id=\"{$id}\"><span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span></span>";
		else $rated = "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>";
		
		return $rated;
		
	}
	
}

function ShowCommentsRating($id, $rating, $vote_num, $allow = true) {
	global $lang, $config;

	if( !$config['comments_rating_type'] ) {
		
		if( $rating AND $vote_num ) $rating = round( ($rating / $vote_num), 0 );
		else $rating = 0;
		
		if ($rating < 0 ) $rating = 0;

		$rating = $rating * 20;
	
		if( !$allow ) {
		
			$rated = <<<HTML
<div class="rating">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		</ul>
</div>
HTML;
		
			return $rated;
		}
	
		$rated = <<<HTML
<div data-comments-ratig-layer-id='{$id}'><div class="rating">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		<li><a href="#" title="{$lang['useless']}" class="r1-unit" onclick="doCommentsRate('1', '{$id}'); return false;">1</a></li>
		<li><a href="#" title="{$lang['poor']}" class="r2-unit" onclick="doCommentsRate('2', '{$id}'); return false;">2</a></li>
		<li><a href="#" title="{$lang['fair']}" class="r3-unit" onclick="doCommentsRate('3', '{$id}'); return false;">3</a></li>
		<li><a href="#" title="{$lang['good']}" class="r4-unit" onclick="doCommentsRate('4', '{$id}'); return false;">4</a></li>
		<li><a href="#" title="{$lang['excellent']}" class="r5-unit" onclick="doCommentsRate('5', '{$id}'); return false;">5</a></li>
		</ul>
</div></div>
HTML;
	
		return $rated;

	} elseif ($config['comments_rating_type'] == "1") {
		
		if( $rating < 0 ) $rating = 0;
		
		if( $allow ) $rated = "<span data-comments-ratig-layer-id=\"{$id}\"><span class=\"ratingtypeplus\" >{$rating}</span></span>";
		else $rated = "<span class=\"ratingtypeplus\" >{$rating}</span>";
		
		return $rated;
	
	} elseif ($config['comments_rating_type'] == "2" OR $config['comments_rating_type'] == "3") {
		
		$extraclass = "ratingzero";
		
		if( $rating < 0 ) {
			$extraclass = "ratingminus";
		}
		
		if( $rating > 0 ) {
			$extraclass = "ratingplus";
			$rating = "+".$rating;
		}
		
		if( $allow ) $rated = "<span data-comments-ratig-layer-id=\"{$id}\"><span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span></span>";
		else $rated = "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>";
		
		return $rated;
		
	}
	
}

function userrating($id) {
	global $db, $config, $lang, $global_news_user_ratingscore;

	$id = intval($id);
	$global_news_user_ratingscore = 0;
		
	$row = $db->super_query( "SELECT SUM(rating) as rating, SUM(vote_num) as num FROM " . PREFIX . "_post_extras WHERE user_id ='{$id}'" );

	if ($row['num']) $global_news_user_ratingscore = str_replace(',', '.', round(($row['rating'] / $row['num']), 1));

	if( !$config['rating_type'] ) {	
	
		if( $row['num'] ) $rating = round( ($row['rating'] / $row['num']), 0 );
		else $rating = 0;

		if ($rating < 0 ) $rating = 0;
		
		$rating = $rating * 20;
	
		$rated = <<<HTML
<div class="rating" style="display:inline;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		</ul>
		</div>
HTML;
	
		return $rated;
	
	} elseif ($config['rating_type'] == "1") {
		
		if( $row['num'] ) $rating = number_format($row['rating'], 0, ',', ' '); else $rating = 0;
		
		if( $row['num'] < 0 ) $rating = 0;
		
		return "<span class=\"ratingtypeplus\" >{$rating}</span>";
		
	} elseif ($config['rating_type'] == "2" OR $config['rating_type'] == "3" ) {

		if( $row['num'] ) $rating = number_format($row['rating'], 0, ',', ' '); else $rating = 0;

		$extraclass = "ratingzero";
		
		if( $row['rating'] < 0 ) {
			$extraclass = "ratingminus";
		}
		
		if( $row['rating'] > 0 ) {
			$extraclass = "ratingplus";
			$rating = "+".$rating;
		}
		
		if($config['rating_type'] == "2") {
			
			return "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>";
		
		} else {
			$dislikes = ($row['num'] - $row['rating'])/2;
			$likes = $row['num'] - $dislikes;
			
			return str_replace(array('{likes}', '{dislikes}', '{rating}'), array("<span class=\"ratingtypeplusminus ratingplus\" >{$likes}</span>", "<span class=\"ratingtypeplusminus ratingminus\" >{$dislikes}</span>", "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>"), $lang['like_dislike_sum']);
		}

		
	}
}

function commentsuserrating($id) {
	global $db, $config, $lang, $global_comments_user_ratingscore;

	$id = intval($id);
	$global_comments_user_ratingscore = 0;

	$row = $db->super_query( "SELECT SUM(rating) as rating, SUM(vote_num) as num FROM " . PREFIX . "_comments WHERE user_id ='{$id}'" );

	if ($row['num']) $global_comments_user_ratingscore = str_replace(',', '.', round(($row['rating'] / $row['num']), 1));

	if( !$config['comments_rating_type'] ) {	
	
		if( $row['num'] ) $rating = round( ($row['rating'] / $row['num']), 0 );
		else $rating = 0;

		if ($rating < 0 ) $rating = 0;
		
		$rating = $rating * 20;
	
		$rated = <<<HTML
<div class="rating" style="display:inline;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		</ul>
		</div>
HTML;
	
		return $rated;
	
	} elseif ($config['comments_rating_type'] == "1") {
		
		if( $row['num'] ) $rating = number_format($row['rating'], 0, ',', ' '); else $rating = 0;
		
		if( $rating < 0 ) $rating = 0;
		
		return "<span class=\"ratingtypeplus\" >{$rating}</span>";
		
	} elseif ($config['comments_rating_type'] == "2" OR $config['comments_rating_type'] == "3") {
		
		if( $row['num'] ) $rating = number_format($row['rating'], 0, ',', ' '); else $rating = 0;

		$extraclass = "ratingzero";
		
		if( $row['rating'] < 0 ) {
			$extraclass = "ratingminus";
		}
		
		if( $row['rating'] > 0 ) {
			$extraclass = "ratingplus";
			$rating = "+".$rating;
		}
		
		if($config['comments_rating_type'] == "2") {
			
			return "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>";
		
		} else {
			
			$dislikes = ($row['num'] - $row['rating'])/2;
			$likes = $row['num'] - $dislikes;
			
			return str_replace(array('{likes}', '{dislikes}', '{rating}'), array("<span class=\"ratingtypeplusminus ratingplus\" >{$likes}</span>", "<span class=\"ratingtypeplusminus ratingminus\" >{$dislikes}</span>", "<span class=\"ratingtypeplusminus {$extraclass}\" >{$rating}</span>"), $lang['like_dislike_sum']);
		}
		
	}
}

function CategoryNewsSelection($selectedId = null, $deprecated = null, $nocat = true) {
	global $cat_info, $user_group, $member_id, $dle_module;

	if ($dle_module == 'addnews') {

		if ($member_id['cat_allow_addnews']) $allow_list = explode(',', $member_id['cat_allow_addnews']);
		else $allow_list = explode(',', $user_group[$member_id['user_group']]['cat_allow_addnews']);

	} else $allow_list = explode(',', $user_group[$member_id['user_group']]['allow_cats']);

	$not_allow_list = explode(',', $user_group[$member_id['user_group']]['not_allow_cats']);

	if ($dle_module == 'search') {
		if (count($cat_info)) {
			foreach ($cat_info as $cats) {
				if ($cats['disable_search']) $not_allow_list[] = $cats['id'];
			}
		}
	}

	if (isset($member_id['cat_add']) AND $member_id['cat_add']) $spec_list = explode(',', $member_id['cat_add']);
	else $spec_list = explode(',', $user_group[$member_id['user_group']]['cat_add']);

	$html = '';
	$groupedCategories = array();

	if ($nocat AND $allow_list[0] == "all") $html .= '<option value="0"></option>';

	if (count($cat_info)) {

		foreach ($cat_info as $category) {
			$groupedCategories[$category['parentid']][] = $category;
		}

		$stack = isset($groupedCategories[0]) ? array_reverse($groupedCategories[0]) : array();
		$levels = array();
		foreach ($stack as $rootCategory) {
			$levels[$rootCategory['id']] = 0;
		}

		while (!empty($stack)) {
			
			$skip = false;
			$current = array_pop($stack);
			$currentLevel = $levels[$current['id']];

			if ($allow_list[0] != "all" AND !in_array($current['id'], $allow_list)) $skip = true;
			if (in_array($current['id'], $not_allow_list)) $skip = true;

			$prefix = str_repeat('&nbsp;', $currentLevel * 4);

			if(is_array($selectedId)) {
				$selected = in_array($current['id'], $selectedId) ? ' selected' : '';

			} else {
				$selected = ($current['id'] == $selectedId) ? ' selected' : '';
			}
			
			if( $dle_module == 'addnews' OR (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'edit') ) {
				
				if ($spec_list[0] == "all" OR in_array($current['id'], $spec_list)) $color = '';
				else $color = ' style="color: red"';

			} else $color = '';

			if ( !$skip ) {
				$html .= sprintf(
					'<option value="%d"%s%s>%s%s</option>',
					$current['id'],
					$selected,
					$color,
					$prefix,
					$current['name']
				);
			}

			if (isset($groupedCategories[$current['id']])) {
				foreach (array_reverse($groupedCategories[$current['id']]) as $childCategory) {
					$stack[] = $childCategory;
					$levels[$childCategory['id']] = $currentLevel + 1;
				}
			}
		}

	}

	return $html;
}

function get_ID($category) {
	global $cat_info;

	$paths = buildCategoryPaths($cat_info);

	$id = array_search($category, $paths);
	
	if($id === false) {
		$id = null;
	}

    if($id) {
		return $id;
	}
	
	$category = explode('/', $category);
	$category = end($category);

	foreach ( $cat_info as $cats ) {
		if( $cats['alt_name'] == $category ) return $cats['id'];
	}

	return false;
}

function buildCategoryPaths($categories) {
	static $result = null;

	if ($result === null) {

		$result = array();
		$temp = array();

		foreach ($categories as $category) {
			$temp[$category['id']] = [
				'alt_name' => $category['alt_name'],
				'parentid' => $category['parentid']
			];
		}

		foreach ($categories as $category) {
			$id = $category['id'];
			$path = array();
			$current = $id;

			while ($current != 0) {
				array_unshift($path, $temp[$current]['alt_name']);
				$current = $temp[$current]['parentid'];
			}

			$result[$id] = implode('/', $path);
		}

	}

    return $result;
}

function set_vars($file, $data) {
	
	$file = totranslit($file, true, false);
	
	if ( is_array($data) OR is_int($data) OR is_string($data) ) {
		
		file_put_contents (ENGINE_DIR . '/cache/system/' . $file . '.json', json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), LOCK_EX);
		@chmod( ENGINE_DIR . '/cache/system/' . $file . '.json', 0666 );
		
	}
}

function get_vars($file) {
	$file = totranslit($file, true, false);

	$data = @file_get_contents( ENGINE_DIR . '/cache/system/' . $file . '.json' );

	if ( $data !== false ) {

		$data = json_decode( $data, true );
		
		if( json_last_error() !== JSON_ERROR_NONE ) {
			return false;
		}

		if ( is_array($data) OR is_int($data) OR is_string($data) ) return $data;

	} 

	return false;	
}

function get_count_from_cache( $hash ) {
	
	$hash = md5($hash);

	$all_counts = dle_cache("news_cache_count");
	
	if( $all_counts ) {
		
		$all_counts = json_decode($all_counts, true);
		
		if( json_last_error() !== JSON_ERROR_NONE ) {
			return 0;
		}

		if( isset( $all_counts[$hash] ) ) {
			return intval($all_counts[$hash]);
		}
		
	}
	
	return 0;

}

function set_count_to_cache( $hash, $count ) {
	
	global $config;
	
	if( !$config['allow_cache'] ) return false;
	
	$hash = md5($hash);
	
	$all_counts = dle_cache("news_cache_count");
	
	if( $all_counts ) {
		
		$all_counts = json_decode($all_counts, true);
		
	}
	
	if( !is_array($all_counts) ) $all_counts = array();
	
	$all_counts[$hash] = intval($count);
	
	create_cache ( "news_cache_count", json_encode( $all_counts , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	
	return true;
	
}

function dle_cache($prefix, $cache_id = false, $member_prefix = false, $customCacheDate = false) {
	global $config, $is_logged, $member_id, $dlefastcache, $_TIME;

	if( !$config['allow_cache'] ) return false;

	$config['clear_cache'] = (intval($config['clear_cache']) > 1) ? intval($config['clear_cache']) : 0;

	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		
		$key = $prefix;
	
	} else {
		
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}
	
	if( $config['cache_type'] ) {
		if( $dlefastcache->connection > 0 ) {
			return $dlefastcache->get($key);
		}
	}

	if(file_exists( ENGINE_DIR . "/cache/" . $key . '.tmp' )) {

		if( $config['clear_cache'] OR $customCacheDate) {

			$file_date = @filemtime( ENGINE_DIR . "/cache/" . $key . '.tmp' );

			if($customCacheDate) {

				$amount = intval($customCacheDate);
				$timeUnit = substr($customCacheDate, -1);

				if( $amount AND in_array($timeUnit, array('d', 'h', 'm') ) ) {

					if ($timeUnit === "d") {
						$file_date += $amount * 24 * 60 * 60;
					} elseif ($timeUnit === "h") {
						$file_date += $amount * 60 * 60;
					} elseif ($timeUnit === "m") {
						$file_date += $amount * 60;
					}

					if( $_TIME > $file_date) {

						@unlink(ENGINE_DIR . "/cache/" . $key . ".tmp");
						return false;

					}

				}

			} elseif ( ( $_TIME - $file_date ) > ($config['clear_cache'] * 60) ) {

				@unlink(ENGINE_DIR . "/cache/" . $key . ".tmp");
				return false;

			}

		}

		return file_get_contents(ENGINE_DIR . "/cache/" . $key . ".tmp");

	}

	return false;

}

function create_cache($prefix, $cache_text, $cache_id = false, $member_prefix = false, $maxAge = false) {
	global $config, $is_logged, $member_id, $dlefastcache;
	
	if( !$config['allow_cache'] ) return false;
	
	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		
		$key = $prefix;
		
	} else {
		
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}
	
	if($cache_text === false) $cache_text = '';

	if( $config['cache_type'] ) {

		if( $dlefastcache->connection > 0 ) {

			if ( $maxAge ) {

				$amount = intval($maxAge);
				$timeUnit = substr($maxAge, -1);

				if ($amount and in_array($timeUnit, array('d', 'h', 'm'))) {

					if ($timeUnit === "d") {
						$maxAge = $amount * 24 * 60 * 60; // добавляем дни
					} elseif ($timeUnit === "h") {
						$maxAge = $amount * 60 * 60; // добавляем часы
					} elseif ($timeUnit === "m") {
						$maxAge = $amount * 60; // добавляем минуты
					}

				} else $maxAge = false;

			}

			if ( $maxAge ) $dlefastcache->set($key, $cache_text, $maxAge);
			else $dlefastcache->set($key, $cache_text);
			
			return true;
		}
	}

	file_put_contents (ENGINE_DIR . "/cache/" . $key . ".tmp", $cache_text, LOCK_EX);
	@chmod( ENGINE_DIR . "/cache/" . $key . ".tmp", 0666 );
	
	return true;
	
}

function clear_cache($cache_areas = false) {
	global $dlefastcache, $config;

	if( $config['cache_type'] ) {
		if( $dlefastcache->connection > 0 ) {
			$dlefastcache->clear( $cache_areas );
			return true;
		}
	}

	if ( $cache_areas ) {
		if(!is_array($cache_areas)) {
			$cache_areas = array($cache_areas);
		}
	}
		
	$fdir = opendir( ENGINE_DIR . '/cache' );
		
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.htaccess' AND !is_dir(ENGINE_DIR . '/cache/' . $file) ) {
			
			if( $cache_areas ) {
				
				foreach($cache_areas as $cache_area) if( stripos( $file, $cache_area ) === 0 ) @unlink( ENGINE_DIR . '/cache/' . $file );
			
			} else {
				
				@unlink( ENGINE_DIR . '/cache/' . $file );
			
			}
		}
	}
	
	return true;

}

function ChangeSkin( $skin ) {
	
	$templates_list = get_folder_list( 'templates' );
	unset($templates_list['smartphone']);
	
	$skin_list = "<form method=\"post\"><select onchange=\"submit()\" name=\"skin_name\">";
	
	foreach ( $templates_list as $key => $value ) {
		
		if( $key == $skin ) $selected = " selected=\"selected\"";
		else $selected = "";
		
		$skin_list .= "<option value=\"{$key}\"" . $selected . ">{$value['name']}</option>";
	}
	
	$skin_list .= '</select><input type="hidden" name="action_skin_change" value="yes"></form>';
	
	return $skin_list;
}

function get_folder_list( $folder = 'language' ) {
	global $lang;
	$allowed_folder = array( 'language', 'templates' );
	
	$list = array ();
	
	if( !in_array($folder, $allowed_folder) ) {
		return $list;
	}
	
	if( !$handle = opendir( ROOT_DIR . "/". $folder ) ) {
		$list[]['name'] = $lang['opt_errfo']." ".$folder;
		return $list;
	}
	
	while ( false !== ($file = readdir( $handle )) ) {
		
		if( is_dir( ROOT_DIR . "/".$folder."/".$file ) AND ($file != "." and $file != "..") ) {
			
			if( is_file( ROOT_DIR . "/".$folder."/".$file."/info.json" ) ) {
				
				$data = json_decode( trim(file_get_contents( ROOT_DIR . "/".$folder."/".$file."/info.json" ) ), true );
				
				if( isset($data['name']) AND $data['name'] ) {
					$list[$file] = $data;
					continue;
				}
			}
			
			$list[$file]['name'] = $file;
		}
		
	}

	closedir( $handle );
	ksort($list);

	return $list;

	
}

function get_mass_cats($id) {
	global $cat_info;

	$id = explode ('-', $id);
	$temp_array = array();

	foreach ( $cat_info as $cats ) {

		if ($cats['id'] >= $id[0] AND $cats['id'] <= $id[1] ) $temp_array[] = intval($cats['id']);

	}

	if ( count($temp_array) ) { sort($temp_array); return implode(',', $temp_array); }
	else return 0;

}

function custom_comments( $matches=array() ) {
	global $db, $is_logged, $member_id, $cat_info, $config, $user_group, $category_id, $_TIME, $lang, $smartphone_detected, $dle_module, $allow_comments_ajax, $PHP_SELF, $dle_login_hash, $replace_links;

	if ( !count($matches) ) return "";
	
	$temp_category_id = $category_id;
	$param_str = trim($matches[1]);
	$custom_cache_id = "customcomments".$param_str.$config['skin'];

	$aviable = array("global");
	$comm_sort = "id";
	$comm_msort = "DESC";
	$where = array();
	$thisdate = date( "Y-m-d H:i:s", $_TIME );
	$sql_select = "SELECT cm.id, cm.post_id, cm.user_id, cm.date, cm.autor as gast_name, cm.email as gast_email, text, ip, is_register, cm.rating, cm.vote_num, name, u.email, news_num, u.comm_num, user_group, lastdate, reg_date, banned, signature, foto, fullname, land, u.xfields, p.title, p.date as newsdate, p.alt_name, p.category, p.allow_comm FROM " . PREFIX . "_comments cm LEFT JOIN " . PREFIX . "_post p ON cm.post_id=p.id {cat_join}LEFT JOIN " . USERPREFIX . "_users u ON cm.user_id=u.user_id ";

	$allow_cache = $config['allow_cache'];
	$cats_select = false;
	$ids_for_sort = false;
	
	if( preg_match( "#available=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$aviable = explode( '|', $match[1] );
	}

	$do = $dle_module ? $dle_module : "main";

	if( !in_array( $do, $aviable ) AND ($aviable[0] != "global") ) return "";

	if( preg_match( "#newsid=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$param_str = str_replace($match[0], '', $param_str);
		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "p.id >= '" . intval($value[0]) . "' AND p.id <= '".intval($value[1])."'";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "p.id IN ('" . implode("','", $temp_array) . "')";
			$ids_for_sort = "FIND_IN_SET(p.id, '".implode(",", $temp_array)."') ";
		}

		if ( count($where_id) ) { 
			$custom_id = implode(' OR ', $where_id);
			$where[] = $custom_id;

		}
	}
	
	if( preg_match( "#newsidexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$param_str = str_replace($match[0], '', $param_str);
		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "(p.id < '" . intval($value[0]) . "' OR p.id > '".intval($value[1])."')";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "p.id NOT IN ('" . implode("','", $temp_array) . "')";
		}

		if ( count($where_id) ) { 
			$custom_id = implode(' AND ', $where_id);
			$where[] = $custom_id;

		}
	}
	
	if( preg_match( "#id=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "cm.id >= '" . intval($value[0]) . "' AND cm.id <= '".intval($value[1])."'";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "cm.id IN ('" . implode("','", $temp_array) . "')";
			$ids_for_sort = "FIND_IN_SET(cm.id, '".implode(",", $temp_array)."') ";
		}

		if ( count($where_id) ) { 
			$custom_id = implode(' OR ', $where_id);
			$where[] = $custom_id;

		}
	}
	
	if( preg_match( "#idexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "(cm.id < '" . intval($value[0]) . "' OR cm.id > '".intval($value[1])."')";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "cm.id NOT IN ('" . implode("','", $temp_array) . "')";
		}

		if ( count($where_id) ) { 
			$custom_id = implode(' AND ', $where_id);
			$where[] = $custom_id;

		}
	}

	$cat_join = "";
	
	if( preg_match( "#category=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$cats_select = true;

		$temp_array = array();

		$match[1] = explode (',', $match[1]);

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
			else $temp_array[] = intval($value);

		}

		$temp_array = implode(',', $temp_array);

		$custom_category = $db->safesql( trim($temp_array) );
		$custom_category = str_replace( ",", "','", $custom_category );

		if( $config['allow_multi_category'] ) {
			
			$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $custom_category . "')) c ON (p.id=c.news_id) ";
		
		} else {

			$where[] = "p.category IN ('" . $custom_category . "')";
		
		}
	}
	
	if( preg_match( "#categoryexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$cats_select = true;
		
		$temp_array = array();

		$match[1] = explode (',', $match[1]);

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
			else $temp_array[] = intval($value);

		}

		$temp_array = implode(',', $temp_array);

		$custom_category = $db->safesql( trim($temp_array) );
		$custom_category = str_replace( ",", "','", $custom_category );

		if( $config['allow_multi_category'] ) {
			
			$where[] = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $custom_category . "') )";
		
		} else {
			
			$where[] = "p.category NOT IN ('" . $custom_category . "')";
		
		}
	}
	
	if (!$cats_select) {
		
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		
		if( $allow_list[0] != "all" ) {
	
			if( $config['allow_multi_category'] ) {
					
				$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode( "','", $allow_list ) . "')) c ON (p.id=c.news_id) ";
				
			} else {
					
				$where[] = "p.category IN ('" . implode( "','", $allow_list ) . "')";
				
			}
		
		}
	
		$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
			
		if( $not_allow_cats[0] != "" ) {
			
			if ($config['allow_multi_category']) {
				
				$where[] = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode ( ',', $not_allow_cats ) . ") )";
			
			} else {
				
				$where[] = "p.category NOT IN ('" . implode ( "','", $not_allow_cats ) . "')";
			
			}
			
		}
	}
	
	$sql_select = str_replace( "{cat_join}", $cat_join, $sql_select );
	
	if( preg_match( "#days=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$days = intval(trim($match[1]));
		$where[] = "cm.date >= '{$thisdate}' - INTERVAL {$days} DAY AND cm.date < '{$thisdate}'";
	}

	if( preg_match( "#author=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "cm.autor = '{$value}'";

		}		
		
		$where[] = implode(' OR ', $temp_array);
		
		
	}

	if( preg_match( "#authorexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "cm.autor != '{$value}'";

		}		
		
		$where[] = implode(' AND ', $temp_array);
		
		
	}
	
	if( $config['allow_cmod'] ) {
		
		$where[] = "cm.approve=1";
	
	}

	if( preg_match( "#template=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_template = trim($match[1]);
	} else $custom_template = "comments";
	
	
	if( preg_match( "#sort=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_sort = array ('asc' => 'ASC', 'desc' => 'DESC' );

		$match[1] = strtolower($match[1]);

		if ( $allowed_sort[$match[1]] ) $comm_msort = $allowed_sort[$match[1]];

	}
	
	if( preg_match( "#order=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_sort = array ('date' => 'id', 'rating' => 'rating', 'rand' => 'RAND()' );

		$match[1] = strtolower($match[1]);

		if ( $allowed_sort[$match[1]] ) $comm_sort = $allowed_sort[$match[1]];
		
		if ($match[1] == "rand" ) { $comm_msort = ""; }
		
		if($match[1] == "id_as_list" AND $ids_for_sort){
			$comm_sort = $ids_for_sort;
			$comm_msort = "";
		}

	}
	
	if( preg_match( "#from=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_from = intval($match[1]);
	} else { $custom_from = 0; }

	if( preg_match( "#limit=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_limit = intval($match[1]);
	} else $custom_limit = intval($config['comm_nummers']);

	$customCacheFile = false;
	$cacheTimestamp = false;

	if (preg_match("#cache=['\"](.+?)['\"]#i", $param_str, $match)) {

		$timeUnit = substr($match[1], -1);

		if (in_array($timeUnit, array('d', 'h', 'm'))) {

			$amount = intval($match[1]);

			if ($amount) {
				$cacheTimestamp = $amount . $timeUnit;
				$customCacheFile = true;
				$config['allow_cache'] = 1;
			}

		} else {

			if ($match[1] == "yes") $config['allow_cache'] = 1;
			else $config['allow_cache'] = false;
		}
	}

	if ($customCacheFile) $cacheFile = "customcommblock"; else $cacheFile = "news";

	if( count( $where ) ) {
		
		$where = implode( " AND ", $where );
		$where = "WHERE " . $where;
	
	} else $where = "";

	$sql_select .=  $where." ORDER BY " . $comm_sort . " " . $comm_msort . " LIMIT " . $custom_from . "," . $custom_limit;

	$content = dle_cache($cacheFile, $custom_cache_id, true, $cacheTimestamp );

	if( $content !== false ) {

		$config['allow_cache'] = $allow_cache;
		return $content;
	
	} else {

		$tpl = new dle_template();
		$tpl->dir = TEMPLATE_DIR;
			
		$comments = new DLE_Comments( $db, $custom_limit, $custom_limit );
		$comments->query = $sql_select;
		$content = $comments->build_customcomments( $tpl, $custom_template.'.tpl' );

		if ( $config['allow_cache'] ) create_cache( $cacheFile, $content, $custom_cache_id, true, $cacheTimestamp );

		$config['allow_cache'] = $allow_cache;
		$category_id = $temp_category_id;
		
		return $content;
	
	}
	

}

function custom_print( $matches=array() ) {
	global $db, $is_logged, $member_id, $xf_inited, $cat_info, $config, $user_group, $category_id, $_TIME, $lang, $smartphone_detected, $dle_module, $allow_comments_ajax, $PHP_SELF, $news_date, $banners, $banner_in_news, $ban_short, $url_page, $user_query, $custom_news, $global_news_count, $remove_canonical, $custom_navigation, $row, $_DOCUMENT_DATE, $custom_blocks_names, $showed_news_ids;

	if ( !count($matches) ) return "";
	$save_row = $row;
	
	$param_str = trim($matches[1]);
	$custom_cache_id = "customnews".$param_str.$config['skin'];


	if( $config['user_in_news'] ) {
		
		$user_select = ", u.email, u.name, u.user_id, u.news_num, u.comm_num as user_comm_num, u.user_group, u.lastdate, u.reg_date, u.banned, u.allow_mail, u.info, u.signature, u.foto, u.fullname, u.land, u.favorites, u.pm_all, u.pm_unread, u.time_limit, u.xfields as user_xfields ";
		$user_join = "LEFT JOIN " . USERPREFIX . "_users u ON (e.user_id=u.user_id) ";
		
	} else { $user_select = ""; $user_join = ""; }
		
	$aviable = array("global");
	$thisdate = date( "Y-m-d H:i:s", $_TIME );
	$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.descr, p.keywords, p.category, p.alt_name, p.comm_num, p.allow_comm, p.allow_main, p.approve, p.fixed, p.symbol, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.disable_index, e.editdate, e.editor, e.reason {$user_select}FROM " . PREFIX . "_post p {cat_join}LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) {$user_join}";

	$where = array();
	$allow_cache = $config['allow_cache'];
	$cats_select = false;
	$ids_for_sort = false;
	$cat_join_count = "";
	$xfields_in_news = array();
	
	if (preg_match("#name=['\"](.+?)['\"]#i", $param_str, $match)) {
		$custom_blocks_name = trim($match[1]);
		$custom_blocks_names[$custom_blocks_name] = false;
	} else $custom_blocks_name = false;

	if( preg_match( "#aviable=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$aviable = explode( '|', $match[1] );
	}
	
	if( preg_match( "#available=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$aviable = explode( '|', $match[1] );
	}
	
	$do = $dle_module ? $dle_module : "main";

	if( !in_array( $do, $aviable ) AND ($aviable[0] != "global") ) return "";

	if( preg_match( "#id=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "id >= '" . intval($value[0]) . "' AND id <= '".intval($value[1])."'";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "id IN ('" . implode("','", $temp_array) . "')";
			$ids_for_sort = "FIND_IN_SET(id, '".implode(",", $temp_array)."') ";
		}

		if ( count($where_id) ) { 
			$custom_id = "(".implode(' OR ', $where_id).")";
			$where[] = $custom_id;

		}
	}
	
	if( preg_match( "#idexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$temp_array = array();
		$where_id = array();
		$match[1] = explode (',', trim($match[1]));

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) {
				$value = explode('-', $value);
				$where_id[] = "(id < '" . intval($value[0]) . "' OR id > '".intval($value[1])."')";

			} else $temp_array[] = intval($value);

		}

		if ( count($temp_array) ) {

			$where_id[] = "id NOT IN ('" . implode("','", $temp_array) . "')";
		}

		if ( count($where_id) ) {
			$custom_id = implode(' AND ', $where_id);
			$where[] = $custom_id;

		}
	}
	
	if( preg_match( "#unique=['\"]yes['\"]#i", $param_str, $match ) ){
			
		if( count( $showed_news_ids ) ) {
			$showed_news_ids = array_unique($showed_news_ids);
			$where[] = "id NOT IN ('" . implode("','", $showed_news_ids) . "')";
		}
	}

	$cat_join = "";

	if( preg_match( "#category=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$cats_select = true;
		
		$temp_array = array();

		$match[1] = explode (',', $match[1]);

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
			else $temp_array[] = intval($value);

		}

		$temp_array = trim(implode(',', $temp_array));

		if( $temp_array AND preg_match( "#subcat=['\"](yes|only)['\"]#i", $param_str, $subcat_match ) ) {
			
			$subcat_array = array();
			$c_arr = explode (',', $temp_array);
			
			foreach ($c_arr as $value) {
				$subcat_array[] = get_sub_cats ( $value, '', false );
			}
			
			$subcat_array = trim(str_replace( "|", ",", implode(',', $subcat_array)));
			
			if( $subcat_match[1] == "yes" ) {
				$temp_array .= ','.$subcat_array;
			}
			
			if( $subcat_match[1] == "only" ) {
				$temp_array = $subcat_array;
			}
			
		}

		$custom_category = $db->safesql( $temp_array );
		$custom_category = str_replace( ",", "','", $custom_category );

		if( $config['allow_multi_category'] ) {
			
			$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $custom_category . "')) c ON (p.id=c.news_id) ";
		
		} else {

			$where[] = "p.category IN ('" . $custom_category . "')";
		
		}
	}
	
	if( preg_match( "#categoryexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$cats_select = true;
		
		$temp_array = array();

		$match[1] = explode (',', $match[1]);

		foreach ($match[1] as $value) {

			if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
			else $temp_array[] = intval($value);

		}

		$temp_array = trim(implode(',', $temp_array));

		if( $temp_array AND preg_match( "#subcat=['\"](yes|only)['\"]#i", $param_str, $subcat_match ) ) {
			
			$subcat_array = array();
			$c_arr = explode (',', $temp_array);
			
			foreach ($c_arr as $value) {
				$subcat_array[] = get_sub_cats ( $value, '', false );
			}
			
			$subcat_array = trim(str_replace( "|", ",", implode(',', $subcat_array)));
			
			if( $subcat_match[1] == "yes" ) {
				$temp_array .= ','.$subcat_array;
			}
			
			if( $subcat_match[1] == "only" ) {
				$temp_array = $subcat_array;
			}
			
		}

		$custom_category = $db->safesql( $temp_array );
		$custom_category = str_replace( ",", "','", $custom_category );

		if( $config['allow_multi_category'] ) {
			
			$where[] = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $custom_category . "') )";
		
		} else {
			
			$where[] = "category NOT IN ('" . $custom_category . "')";
		
		}
	}
	
	if( !$cats_select ) {
		
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		
		if( !$user_group[$member_id['user_group']]['allow_short'] AND $allow_list[0] != "all" ) {
	
			if( $config['allow_multi_category'] ) {
					
				$cat_join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode( "','", $allow_list ) . "')) c ON (p.id=c.news_id) ";
				
			} else {
					
				$where[] = "category IN ('" . implode( "','", $allow_list ) . "')";
				
			}
		
		}
	
		$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
			
		if( !$user_group[$member_id['user_group']]['allow_short'] AND $not_allow_cats[0] != "" ) {
			
			if ($config['allow_multi_category']) {
				
				$where[] = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode ( "','", $not_allow_cats ) . "') )";
			
			} else {
				
				$where[] = "category NOT IN ('" . implode ( "','", $not_allow_cats ) . "')";
			
			}
			
		}
		
	}
	
	$sql_select = str_replace( "{cat_join}", $cat_join, $sql_select );
	
	if( preg_match( "#futureannounce=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		if( $match[1] == "yes" ) $fromfuture = true;
		else $fromfuture = false;
		
	} else $fromfuture = false;
	
	if( preg_match( "#days=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$days = intval(trim($match[1]));
		
		if($fromfuture) {
			
			$startdate = date("Y-m-d 00:00:00", strtotime("+1 day"));
			$enddate = date("Y-m-d 00:00:00", strtotime("+".($days+1)." day"));
			$where[] = "p.date >= '{$startdate}' AND p.date < '{$enddate}'";
			
		} else {
			
			$where[] = "p.date >= '{$thisdate}' - INTERVAL {$days} DAY AND p.date < '{$thisdate}'";
			
		}
		
	} else $days = 0;

	if( preg_match( "#author=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.autor = '{$value}'";

		}		
		
		$where[] = "(".implode(' OR ', $temp_array).")";
		
		
	}

	if( preg_match( "#authorexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.autor != '{$value}'";

		}		
		
		$where[] = implode(' AND ', $temp_array);
		
		
	}

	if( preg_match( "#catalog=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.symbol = '{$value}'";

		}		
		
		$where[] = "(".implode(' OR ', $temp_array).")";
		
		
	}

	if( preg_match( "#catalogexclude=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.symbol != '{$value}'";

		}		
		
		$where[] = implode(' AND ', $temp_array);
		
		
	}
	
	if( preg_match( "#xfields=[\"](.+?)[\"]#i", $param_str, $match ) ) {

		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.xfields LIKE '%{$value}%'";

		}		
		
		$where[] = "(".implode(' OR ', $temp_array).")";
		
	}

	
	if( preg_match( "#xfieldsexclude=[\"](.+?)[\"]#i", $param_str, $match ) ) {
		
		$match[1] = explode (',', $match[1]);

		$temp_array = array();

		foreach ($match[1] as $value) {

			$value = $db->safesql(trim($value));
			$temp_array[] = "p.xfields NOT LIKE '%{$value}%'";

		}		
		
		$where[] = implode(' AND ', $temp_array);
		
		
	}
	
	$force_stop_cache = false;
	
	if (preg_match("#favorites=['\"](.+?)['\"]#i", $param_str, $match)) {

		if ($match[1] == "yes") {
			$fav_list = array();
			$list = explode(",", $member_id['favorites']);
			$list = array_reverse($list);
			$order_list = array();

			foreach ($list as $val) {
				$fav_list[] = "'" . intval($val) . "'";
				$order_list[] = intval($val);
			}

			if( count( $fav_list ) ) {

				$ids_for_sort = "FIND_IN_SET(id, '" . implode(",", $order_list) . "') ";
				$where[] = "id in (". implode(",", $fav_list) . ")";
			} else {
				$where[] = "id = 0";
			}

			$config['allow_cache'] = false;
			$force_stop_cache = true;

		}

	}

	if( preg_match( "#template=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_template = trim($match[1]);
	} else $custom_template = "shortstory";

	if( preg_match( "#from=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_from = intval($match[1]);
		$custom_all = $custom_from;
	} else { $custom_from = 0; $custom_all = 0;}

	if( preg_match( "#limit=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$custom_limit = intval($match[1]);
	} else $custom_limit = intval($config['news_number']);

	$customCacheFile = false;
	$cacheTimestamp = false;

	if( preg_match( "#cache=['\"](.+?)['\"]#i", $param_str, $match ) AND !$force_stop_cache) {

		$timeUnit = substr($match[1], -1);

		if( in_array($timeUnit, array('d', 'h', 'm') ) ) {

			$amount = intval($match[1]);

			if( $amount ) {
				$cacheTimestamp = $amount . $timeUnit;
				$customCacheFile = true;
				$config['allow_cache'] = 1;	
			}

		} else {

			if ($match[1] == "yes") $config['allow_cache'] = 1;
			else $config['allow_cache'] = false;

		}

	}

	if ($customCacheFile) $cacheFile = "customblock"; else $cacheFile = "news";

	if( $config['allow_cache'] ) $short_news_cache = true; else $short_news_cache = false;
	
	if( preg_match( "#fixed=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		$fixed = "";

		if( $match[1] == "yes" ) $fixed = "fixed DESC, ";
		elseif( $match[1] == "only" ) { $where[] = "fixed='1'"; }
		elseif( $match[1] == "without" ) { $where[] = "fixed='0'"; }

	} else $fixed = "";
	
	
	if( preg_match( "#banners=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		if( $match[1] == "yes" ) $use_banners = true;
		else $use_banners = false;
		
	} else $use_banners = false;

	if( $is_logged and ($user_group[$member_id['user_group']]['allow_edit'] and ! $user_group[$member_id['user_group']]['allow_all_edit']) ) $config['allow_cache'] = false;

	if (isset($custom_category) AND $custom_category) {
		
		if( isset($cat_info[$custom_category]['news_sort']) AND $cat_info[$custom_category]['news_sort'] ) $news_sort = $cat_info[$custom_category]['news_sort']; else $news_sort = $config['news_sort'];
		if( isset($cat_info[$custom_category]['news_msort']) AND $cat_info[$custom_category]['news_msort'] ) $news_msort = $cat_info[$custom_category]['news_msort']; else $news_msort = $config['news_msort'];
		
	} else {
		
		$news_sort = $config['news_sort'];
		$news_msort = $config['news_msort'];
	}

	if( preg_match( "#sort=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_sort = array ('asc' => 'ASC', 'desc' => 'DESC' );

		$match[1] = strtolower($match[1]);

		if ( $allowed_sort[$match[1]] ) $news_msort = $allowed_sort[$match[1]];

	}

	if( preg_match( "#order=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_sort = array ('date' => 'date', 'editdate' => 'editdate', 'rating' => 'rating', 'reads' => 'news_read', 'comments' => 'p.comm_num','title' => 'title', 'rand' => 'RAND()' );

		$match[1] = strtolower($match[1]);

		if ( $allowed_sort[$match[1]] ) $news_sort = $allowed_sort[$match[1]];

		if ($match[1] == "rand" ) { $fixed = ""; $news_msort = ""; }
		
		if($match[1] == "id_as_list" AND $ids_for_sort){
			$news_sort = $ids_for_sort;
			$news_msort = "";
		}

		if($match[1] == "lastviewed") {

			if(!$config['last_viewed']) return $lang['enable_lastviewed'];
		
			if( !$_COOKIE['viewed_ids'] ) return '';
			
			$viewed_ids = explode(',', trim($_COOKIE['viewed_ids']));
			$temp_array = array();
			
			if($news_msort == "ASC") $viewed_ids = array_reverse($viewed_ids);
			
			foreach ($viewed_ids as $value) {
				$value = intval(trim($value));
				
				if ($value > 0) $temp_array[] = $db->safesql($value);
				
			}
			
			if( count($temp_array) ) {
				$fixed = "";
				$where[] = "id IN ('" . implode("','", $temp_array) . "')";
				$news_sort = "FIND_IN_SET(id, '".implode(",", $temp_array)."') ";
				$news_msort = "";
				$config['allow_cache'] = false;
			}
		
		}
		
	}
	
	if( preg_match( "#sortbyuser=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		
		if( $match[1] == "yes" ) {
			
			if (isset ( $_SESSION['dle_sort_global'] )) $news_sort = $_SESSION['dle_sort_global'];
			if (isset ( $_SESSION['dle_direction_global'] )) $news_msort = $_SESSION['dle_direction_global'];
			
			if ( !defined('CUSTOMSORT') ) {
				define('CUSTOMSORT', true);
			}
	
		}

	}
	
	if( preg_match( "#navigation=['\"](.+?)['\"]#i", $param_str, $match ) ) {

		if( $match[1] == "yes" AND $url_page !== false ) {

			$build_navigation = true;
			if (isset ( $_GET['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 0;

			if ($cstart > $config['max_cache_pages']) $config['allow_cache'] = false;

			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = ($cstart * $custom_limit) + $custom_from;
				$custom_from = $cstart;
				$remove_canonical = true;
			}
			
			$custom_cache_id = $custom_cache_id.$cstart;
			
		} else $build_navigation = false;

	} else $build_navigation = false;

	$content = dle_cache($cacheFile, $custom_cache_id, true, $cacheTimestamp);
	
	if( $content ) {

		$content = json_decode($content, true);
	
		if( json_last_error() === JSON_ERROR_NONE AND is_array( $content ) ) {
			
			if( isset($content['navigation']) AND $content['navigation'] ) {
				if ( !defined('CUSTOMNAVIGATION') ) {
					define('CUSTOMNAVIGATION', true);
					$custom_navigation = $content['navigation'];
				}
			}
			
			if( isset( $active['last-modified'] ) AND $active['last-modified'] ) {
				
				if( $active['last-modified'] > $_DOCUMENT_DATE ) {
					$_DOCUMENT_DATE = $active['last-modified'];
				}
				
			}
				
			$content = $content['content'];
			
		}
	}
				
	if( $content !== false ) {

		$config['allow_cache'] = $allow_cache;
		$custom_news = true;
		$row = $save_row;
		
		if ( $user_group[$member_id['user_group']]['allow_edit'] OR $user_group[$member_id['user_group']]['allow_all_edit'] ) $allow_comments_ajax = true;

		if( $custom_blocks_name AND $content) $custom_blocks_names[$custom_blocks_name] = true;

		return $content;
	
	} else {

		if( preg_match( "#tags=['\"](.+?)['\"]#i", $param_str, $match ) ) {

			$temp_array = array();
			
			$match[1] = explode (',', trim($match[1]));
			
			foreach ($match[1] as $value) {
				$value = $db->safesql(trim($value));
				if( $value ) $temp_array[] = "tag='{$value}'";
			}
			
			if ( count($temp_array) ) {
	
				$temp_array = implode(" OR ", $temp_array);
				
				$db->query ( "SELECT news_id FROM " . PREFIX . "_tags WHERE {$temp_array}" );

				$temp_array = array ();
				
				while ( $row = $db->get_row () ) {
					
					if (!in_array($row['news_id'], $temp_array)) $temp_array[] = $row['news_id'];
				
				}
				
				if (count ( $temp_array )) {
					
					$where[] = "id IN ('" . implode("','", $temp_array) . "')";
				
				} else $where[] = "id IN ('0')";
				
			}
			
		}
		
		$where[] = "approve=1";

		if( $config['no_date'] AND !$config['news_future'] AND !$days) $where[] = "date < '" . $thisdate . "'";
		
		if ( $build_navigation ) {
			
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post p {$cat_join}WHERE ".implode(' AND ', $where);

		} else $sql_count = "";

		$tpl = new dle_template();
		$tpl->dir = TEMPLATE_DIR;				
		$tpl->is_custom = true;

		$tpl->load_template( $custom_template . '.tpl' );
	
		$sql_select .= " WHERE ".implode(' AND ', $where)." ORDER BY " . $fixed . $news_sort . " " . $news_msort . " LIMIT " . $custom_from . "," . $custom_limit;

		$sql_result = $db->query( $sql_select );

		include (DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));

		if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
			$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
		}
		
		create_cache($cacheFile, json_encode( array('content' => $tpl->result['content'], 'navigation' => $tpl->result['navigation'], 'last-modified' => $_DOCUMENT_DATE ) , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $custom_cache_id, true, $cacheTimestamp );
		
		$config['allow_cache'] = $allow_cache;
		$tpl->is_custom = false;
		$row = $save_row;
		
		if ($custom_blocks_name AND $tpl->result['content']) $custom_blocks_names[$custom_blocks_name] = true;

		return $tpl->result['content'];
	
	}

}

function check_ip($ips) {
	
	$_IP = get_ip();

	$blockip = false;
	
	if( is_array( $ips ) ) {
		
		if( strpos($_IP, ":") === false ) {
			$delimiter = ".";
		} else $delimiter = ":";
		
		$this_ip_split = explode( $delimiter, $_IP );
		$ip_lenght = count($this_ip_split);
		
		foreach ( $ips as $ip_line ) {

			$ip_arr = trim( $ip_line['ip'] );
			
			if( $ip_arr == $_IP ) {
				
				$blockip = $_IP;
				break;
			
			} elseif ( count(explode ('/', $ip_arr)) == 2 ) {
				
				if( maskmatch($_IP, $ip_arr) ) {
					$blockip = $ip_line['ip'];
					break;
				}
				
			} else {
				
				$ip_check_matches = 0;
				$db_ip_split = explode( $delimiter, $ip_arr );

				for($i_i = 0; $i_i < $ip_lenght; $i_i ++) {
					if( $this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*' ) {
						$ip_check_matches += 1;
					}
				
				}
			
				if( $ip_check_matches == $ip_lenght ) {
					$blockip = $ip_line['ip'];
					break;
				}
			}		
		}
	}
	
	return $blockip;
}

function allowed_ip($ip_array) {
	
	$ip_array = trim( $ip_array );

	$_IP = get_ip();

	if( !$ip_array ) {
		return true;
	}
	
	if( strpos($_IP, ":") === false ) {
		$delimiter = ".";
	} else $delimiter = ":";
	
	$db_ip_split = explode( $delimiter, $_IP );
	$ip_lenght = count($db_ip_split);
	
	$ip_array = explode( "|", $ip_array );
	
	foreach ( $ip_array as $ip ) {
		
		$ip = trim( $ip );
		
		if( $ip == $_IP ) {
			
			return true;
		
		} elseif( count(explode ('/', $ip)) == 2 ) {
				
			if( maskmatch($_IP, $ip) ) return true;
				
		} else {
			
			$ip_check_matches = 0;
			$this_ip_split = explode( $delimiter, $ip );
			
			for($i_i = 0; $i_i < $ip_lenght; $i_i ++) {
				if( $this_ip_split[$i_i] == $db_ip_split[$i_i] OR $this_ip_split[$i_i] == '*' ) {
					$ip_check_matches += 1;
				}
			
			}
			
			if( $ip_check_matches == $ip_lenght ) return true;
		}
	
	}
	
	return false;
}

function maskmatch($IP, $CIDR) {
	
    list ($address, $netmask) = explode('/', $CIDR, 2);

	if( strpos($IP, ".") !== false AND strpos($CIDR, ".") !== false ) {
		
		return ( ip2long($IP) & ~((1 << (32 - $netmask)) - 1) ) == ip2long ($address);
	
	} elseif( strpos($IP, ":") !== false AND strpos($CIDR, ":") !== false ) {
		
        if (!((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
          return false;
        }
		
        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($IP));

        if (!$bytesAddr || !$bytesTest) {
            return false;
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return false;
            }
        }
		
		return true;
		
	}
	
	return false;

}

function check_netz($ip1, $ip2) {
	
	if( strpos($ip1, ":") === false ) {
		$delimiter = ".";
	} else $delimiter = ":";
	
	$ip1 = explode( $delimiter, $ip1 );
	$ip2 = explode( $delimiter, $ip2 );
	
	if( $ip1[0] != $ip2[0] ) return false;
	if( $ip1[1] != $ip2[1] ) return false;
	
	if($delimiter == ":") {
		if( $ip1[2] != $ip2[2] ) return false;
		if( $ip1[3] != $ip2[3] ) return false;
	}
	
	return true;

}

function show_attach($story, $id, $static = false) {
	global $db, $config, $lang, $user_group, $member_id, $_TIME, $news_date;

	$find_1 = array();
	$find_2 = array();
	$replace_1 = array();
	$replace_2 = array();

	$tpl = new dle_template();
	$tpl->dir = TEMPLATE_DIR;
	$root = $config['http_home_url'];
	
	if (strpos($root, "//") === 0) {
		$root = isSSL() ? $root = "https:".$root : $root = "http:".$root;
	} elseif (strpos($root, "/") === 0) {
		$root = isSSL() ? $root = "https://".$_SERVER['HTTP_HOST'].$root : "http://".$_SERVER['HTTP_HOST'].$root;
	}

	if (!file_exists($tpl->dir . "/attachment.tpl")) {

		$tpl->template = <<<HTML
[allow-download]<span class="attachment"><a href="{link}" >{name}</a> [count] [{size}] ({$lang['att_dcount']} {count})[/count]</span>[/allow-download]
[not-allow-download]<span class="attachment">{$lang['att_denied']}</span>[/not-allow-download]
HTML;

		$tpl->copy_template = $tpl->template;
	} else {

		$tpl->load_template('attachment.tpl');
	}

	if( $static ) {
		
		if( is_array( $id ) and count( $id ) ) {
			$list = array();
			
			foreach ( $id as $value ) {
				$list[] = intval($value);
			}
			
			$id = implode( ',', $list );
			
			$where = "static_id IN ({$id})";
			
		} else $where = "static_id = '".intval($id)."'";

		$sql_result =  $db->query( "SELECT * FROM " . PREFIX . "_static_files WHERE $where" );
		
		$area = "&area=static";
	
	} else {
		
		if( is_array( $id ) and count( $id ) ) {
			
			$list = array();
			
			foreach ( $id as $value ) {
				$list[] = intval($value);
			}
			
			$id = implode( ',', $list );
			
			$where = "news_id IN ({$id})";
			
		} else $where = "news_id = '".intval($id)."'";
		
		$sql_result = $db->query( "SELECT * FROM " . PREFIX . "_files WHERE $where" );
		
		$area = "";
	
	}
	
	while ( $row = $db->get_row($sql_result) ) {

		$row['name'] = explode( "/", $row['name'] );
		$row['name'] = end( $row['name'] );
		
		$filename_arr = explode( ".", $row['onserver'] );
		$type = strtolower(end( $filename_arr ));

		$find_1[] = '[attachment=' . $row['id'] . ']';
		$find_2[] = "#\[attachment={$row['id']}:(.+?)\]#i";
		
		if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';

		if (stripos ( $tpl->copy_template, "{md5}" ) !== false) {
			
			if($row['checksum']) $tpl->set( '{md5}', $row['checksum'] );
			else $tpl->set( '{md5}', @md5_file( ROOT_DIR . '/uploads/' . $uploaded_path.$row['onserver'] ) );
			
		}

		if (stripos ( $tpl->copy_template, "{size}" ) !== false) {
			
			if($row['size']) $tpl->set( '{size}', formatsize($row['size']) );
			else $tpl->set( '{size}', formatsize( @filesize( ROOT_DIR . '/uploads/' . $uploaded_path.$row['onserver'] ) ) );
			
		}
		
		$microsoft_ext = array("doc", "docx", "docm", "dotm", "dotx", "xlsx", "xlsb", "xls", "xlsm", "pptx", "ppsx", "ppt", "pps", "pptm", "potm", "ppam", "potx", "ppsm", "odt", "odx");
		$google_ext = array("pdf");

		if ( in_array($type, $microsoft_ext) OR in_array($type, $google_ext) ) {

			$tpl->set( '[allow-online]', "" );
			$tpl->set( '[/allow-online]', "" );

			if(in_array($type, $microsoft_ext)) {
				$tpl->set('{online-view-link}', "https://view.officeapps.live.com/op/view.aspx?src=" . urlencode($root . "index.php?do=download&id=" . $row['id'] . $area . "&viewonline=1"));
			} else {
				$tpl->set('{online-view-link}', "https://docs.google.com/viewer?url=" . urlencode($root . "index.php?do=download&id=" . $row['id'] . $area . "&viewonline=1"));
			}
			

		} else {
			
			$tpl->set( '{online-view-link}', "" );
			$tpl->set_block( "'\\[allow-online\\](.*?)\\[/allow-online\\]'si", "" );
			
		}
		
		if ( $user_group[$member_id['user_group']]['allow_files'] ) {
			
			$tpl->set( '[allow-download]', "" );
			$tpl->set( '[/allow-download]', "" );
			$tpl->set_block( "'\\[not-allow-download\\](.*?)\\[/not-allow-download\\]'si", "" );
					
		} else {
			
			$tpl->set( '[not-allow-download]', "" );
			$tpl->set( '[/not-allow-download]', "" );
			$tpl->set_block( "'\\[allow-download\\](.*?)\\[/allow-download\\]'si", "" );
			
		}
		
		if ( $config['files_count'] ) {
			$tpl->set( '{count}', number_format($row['dcount'], 0, ',', ' ') );
			$tpl->set( '[count]', "" );
			$tpl->set( '[/count]', "" );
			$tpl->set_block( "'\\[not-allow-count\\](.*?)\\[/not-allow-count\\]'si", "" );
					
		} else {
			$tpl->set( '{count}', "" );			
			$tpl->set( '[not-allow-count]', "" );
			$tpl->set( '[/not-allow-count]', "" );
			$tpl->set_block( "'\\[count\\](.*?)\\[/count\\]'si", "" );
			
		}
		
		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
		
		}

		$news_date = $row['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
		
		$tpl->set( '{name}', $row['name'] );
		$tpl->set( '{extension}', $type );
		$tpl->set( '{link}', $config['http_home_url']."index.php?do=download&id=".$row['id'].$area );
		$tpl->set( '{id}', $row['id'] );

		$tpl->compile( 'attachment' );
		
		$replace_1[] = $tpl->result['attachment'];
		
		$tpl->result['attachment'] = str_replace( $row['name'], "\\1", $tpl->result['attachment'] );
		
		$replace_2[] = $tpl->result['attachment'];
		
		$tpl->result['attachment'] = '';

	}
	
	$tpl->clear();
	$db->free();

	$story = str_replace ( $find_1, $replace_1, $story );
	$story = preg_replace( $find_2, $replace_2, $story );
	
	return $story;

}

function xfieldsload($profile = false) {
	global $lang, $config;
	
	if( $profile ) $path = ENGINE_DIR . '/data/xprofile.txt';
	else $path = ENGINE_DIR . '/data/xfields.txt';
	
	$filecontents = file( $path );
	$fields = array();
	$tmp_arr = array();
	
	if( !is_array( $filecontents ) ) {
		
		return array();
	
	} elseif( count($filecontents) ) {
		
		foreach ( $filecontents as $name => $value ) {
			if( trim($value) ) {
				$tmp_arr = explode( "|", trim($value, "\t\n\r\0\x0B") );
				foreach ( $tmp_arr as $name2 => $value2 ) {
					$value2 = str_replace( "&#124;", "|", $value2 );
					$value2 = str_replace( "__NEWL__", "\r\n", $value2 );
					$value2 = html_entity_decode($value2, ENT_QUOTES, 'UTF-8');
					$fields[$name][$name2] = $value2;
				}
			}
		}
		
		return $fields;
	}
	
	return array();
}

function xfieldsdataload($id) {
	
	if( !is_string($id) OR !$id ) return array();
	
	$xfieldsdata = explode( "||", $id );
	
	foreach ( $xfieldsdata as $xfielddata ) {
		list ( $xfielddataname, $xfielddatavalue ) = explode( "|", $xfielddata );
		$xfielddataname = str_replace( "&#124;", "|", $xfielddataname );
		$xfielddataname = str_replace( "__NEWL__", "\r\n", $xfielddataname );
		$xfielddatavalue = str_replace( "&#124;", "|", $xfielddatavalue );
		$xfielddatavalue = str_replace( "__NEWL__", "\r\n", $xfielddatavalue );
		$data[$xfielddataname] = trim($xfielddatavalue);
	}
	
	return $data;
}

function clear_rss_content ( $content, $rssmode ) {
	
	$content = preg_replace_callback( "#<div class=['\"]quote_body contenteditable['\"]>(.+?)</div>#is", function ($matches) {
            return trim($matches[1]);
        }, $content );
	
	$content = preg_replace_callback( "#<div class=['\"]quote['\"]>(.+?)</div>#is", function ($matches) {
            return trim($matches[1]);
        }, $content );
		
	$content = preg_replace_callback( "#<div class=['\"]quote_block noncontenteditable['\"]>(.+?)</div>#is", function ($matches) {
            return "<blockquote>".trim($matches[1])."</blockquote>";
        }, $content );
	
	$content = preg_replace( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $content );
	$content = preg_replace( "'\[attachment=(.*?)\]'si", "", $content );	
	$content = preg_replace( "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $content );
	$content = preg_replace( "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $content );
	$content = preg_replace( "'{banner_(.*?)}'si", "", $content );
	$content = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "", $content );
	$content = preg_replace('#<script[^>]*>.*?</script>#is', '', $content );
	$content = preg_replace( "#<!--(.+?)-->#is", "", $content );
	$content = preg_replace('/\s+/u', ' ', $content);
	
	return trim($content);

}

function clear_content($content, $len = 300, $replace_single_quote = true) {

	if (!$content or !is_string($content)) {
		return '';
	}

	$remove = array("\x60", "\t", "\n", "\r", '\t', '\n', '\r', "{PAGEBREAK}", "&nbsp;", "<br />", "<br>", " ,");
	$len = intval($len);

	$content = stripslashes($content);

	$content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

	$content = preg_replace("#\[hide(.*?)\](.+?)\[/hide\]#is", "", $content);
	$content = preg_replace("'\[attachment=(.*?)\]'si", "", $content);
	$content = preg_replace("'\[page=(.*?)\](.*?)\[/page\]'si", "", $content);
	$content = preg_replace("#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $content);
	$content = preg_replace("#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $content);
	$content = preg_replace("'{banner_(.*?)}'si", "", $content);
	$content = preg_replace("'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "", $content);
	$content = preg_replace("#<pre(.*?)>(.+?)</pre>#is", "", $content);
	$content = str_replace("&#1072;", "a", $content);
	$content = str_replace("&#111;", "o", $content);
	$content = str_replace("><", "> <", $content);

	$content = str_replace($remove, ' ', $content);
	$content = strip_tags($content);

	$content = preg_replace("#(^|\s|>)((http|https)://\w+[^\s\[\]\<]+)#i", '', $content);

	if ($replace_single_quote) {
		$content = str_replace("&amp;amp;", "&amp;", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
	} else {
		$content = str_replace("&amp;amp;", "&amp;", htmlspecialchars($content, ENT_COMPAT, 'UTF-8'));
	}
	
	$content = str_replace(array("{", "}", "[", "]"), array("&#123;", "&#125;", "&#91;", "&#93;"), $content);

	$content = preg_replace('/\s+/u', ' ', $content);

	if ($len and $len > 1) {

		if (dle_strlen($content) > $len) {

			$content = dle_substr($content, 0, $len);

			if (($temp_dmax = dle_strrpos($content, ' '))) $content = dle_substr($content, 0, $temp_dmax);
		}
	}

	return trim($content);
}

function remove_quotes_from_text( $Html ) {

	$wrappedHtml = '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"></head><body>' . $Html . '</body></html>';

	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	libxml_clear_errors();

	$xpath = new DOMXPath($dom);
	$nodes = $xpath->query('//div[contains(@class, "quote_block")]');

	foreach ($nodes as $node) {
		$node->parentNode->removeChild($node);
	}

	$body = $dom->getElementsByTagName('body')->item(0);

	$newHtml = '';
	foreach ($body->childNodes as $child) {
		$newHtml .= $dom->saveHTML($child);
	}

	return $newHtml;
}

function get_keywords_from_clear_content($story) {

	$newarr = array ();
	$bad_keywords_symbol = array(",", ".", "/", "#", ":", "@", "~", "=", "-", "+", "*", "^", "%", "$", "?", "!", '&quot;');
	
	$story = str_replace($bad_keywords_symbol, '', $story);

	$arr = explode( " ", $story );
	
	foreach ( $arr as $word ) {
		$word = str_replace("&amp;", "&", $word);		
		if( dle_strlen( $word ) > 4 ) $newarr[] = $word;
	}
	
	$arr = array_count_values( $newarr );
	arsort( $arr );
	
	$arr = array_keys( $arr );

	$arr = array_slice( $arr, 0, 20 );
	
	return implode( ", ", $arr );
	
}

function create_keywords($story) {
	global $metatags;
	
	$story = clear_content($story, 0, false);
	
	$metatags['description'] = $story;

	if( dle_strlen( $metatags['description'] ) > 300 ) {
		
		$metatags['description'] = dle_substr( $story, 0, 300 );
	
		if( ($temp_dmax = dle_strrpos( $metatags['description'], ' ' )) ) $metatags['description'] = dle_substr( $metatags['description'], 0, $temp_dmax );

	}
	
	$metatags['keywords'] = get_keywords_from_clear_content($story);
	
}

function news_permission($id) {
	
	if( $id == "" ) return;
	
	$data = array ();
	$groups = explode( "||", $id );
	foreach ( $groups as $group ) {
		list ( $groupid, $groupvalue ) = explode( ":", $group );
		$data[$groupid] = $groupvalue;
	}
	return $data;
}

function bannermass($fest, $massiv) {
	
	if( is_array($massiv) AND count($massiv) ) {
		return $fest . $massiv[array_rand( $massiv )]['text'];
	} else return $fest;

}

function get_sub_cats($id, $subcategory = '', $with_id = true) {
	
	global $cat_info;
	$subfound = array ();
	
	if( !$subcategory AND $with_id) $subcategory = $id;
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		
		if( $subcategory ) $subcategory .= "|";
		
		$subcategory .= $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
		
	}
	
	return $subcategory;

}

function check_xss() {

	$url = html_entity_decode( urldecode( $_SERVER['QUERY_STRING'] ), ENT_QUOTES, 'ISO-8859-1' );
	$url = str_replace( "\\", "/", $url );

	if (isset($_GET['do']) AND $_GET['do'] == "xfsearch") {

		$f = html_entity_decode( urldecode( $_GET['xf'] ), ENT_QUOTES, 'ISO-8859-1' );

		$count1 = substr_count ($f, "'");
		$count2 = substr_count ($url, "'");

		if ( $count1 == $count2 AND (strpos( $url, '<' ) === false) AND (strpos( $url, '>' ) === false) AND (strpos( $url, '.php' ) === false) ) return;

	}

	if (isset($_GET['do']) AND $_GET['do'] == "tags") {

		$f = html_entity_decode( urldecode( $_GET['tag'] ), ENT_QUOTES, 'ISO-8859-1' );

		$count1 = substr_count ($f, "'");
		$count2 = substr_count ($url, "'");

		if ( $count1 == $count2 AND (strpos( $url, '<' ) === false) AND (strpos( $url, '>' ) === false) AND (strpos( $url, './' ) === false) AND (strpos( $url, '../' ) === false) AND (strpos( $url, '.php' ) === false) ) return;

	}
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false) ) {
			if( $_GET['do'] != "search" OR $_GET['subaction'] != "search" ) {
				header( "HTTP/1.1 403 Forbidden" );
				die( "Hacking attempt!" );
			}
		}
	
	}
	
	$url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ), ENT_QUOTES, 'ISO-8859-1' );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '\'' ) !== false) ) {
			if( $_GET['do'] != "search" OR $_GET['subaction'] != "search" ) {
				header( "HTTP/1.1 403 Forbidden" );
				die( "Hacking attempt!" );
			}
		
		}
	
	}

}

function check_same_domain($url) {
	global $config;
	
	$url = dle_strtolower( (string)parse_url( (string)$url, PHP_URL_HOST));
	$value = dle_strtolower( (string)parse_url($config['http_home_url'], PHP_URL_HOST));
	
	if( !$value ) $value = $_SERVER['HTTP_HOST'];

	if( !$url OR $url == $value ) return true;
	
	return false;
}
	
function if_category_rating( $category ) {
	global $cat_info;
	
	$category = explode( ',', $category );
	
	$found = false;
	
	foreach ( $category as $element ) {
			
		if( isset( $cat_info[$element]['rating_type'] ) AND $cat_info[$element]['rating_type'] > -1 ) {
			return $cat_info[$element]['rating_type'];
		}
	
	}
	
	return $found;
}

function check_category( $matches=array() ) {
	global $category_id;

	$block = $matches[3];
	$category = $category_id;

	$temp_array = array();

	$matches[2] = str_replace(" ", "", $matches[2] );
	$matches[2] = explode (',', $matches[2]);

	foreach ($matches[2] as $value) {

		if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
		else $temp_array[] = intval($value);

	}

	$temp_array = implode(',', $temp_array);

	if ($matches[1] == "category" OR $matches[1] == "catlist") $action = true; else $action = false;
	
	$cats = explode( ',', $temp_array );
	$category = explode( ',', $category );
	$found = false;
	
	foreach ( $category as $element ) {
		
		if( $action ) {
			
			if( in_array( $element, $cats ) ) {
				
				return $block;
			}
		
		} else {
			
			if( in_array( $element, $cats ) ) {
				$found = true;
			}
		
		}
	
	}

	if ( !$action AND !$found ) {	

		return $block;
	}

	return "";

}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", strtolower( $url ) );
	$url = str_replace( "https://", "", $url );
	if( substr( $url, 0, 2 ) == '//' ) $url = str_replace( "//", "", $url );
	if( substr( $url, 0, 4 ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

function get_url($id) {
	global $cat_info;

	if( !$id ) return '';

	$id = explode (",", $id);
	$id = intval($id[0]);

	$paths = buildCategoryPaths($cat_info);

	if (isset($paths[$id]) AND $paths[$id]) {

		return $paths[$id];
	}

	return '';
}

function get_categories($id, $separator=" &raquo;") {
	
	global $cat_info, $config, $PHP_SELF;
	
	if( ! $id ) return;
	
	$parent_id = $cat_info[$id]['parentid'];
	
	if( $config['allow_alt_url'] ) $list = "<a href=\"" . $config['http_home_url'] . get_url( $id ) . "/\">{$cat_info[$id]['name']}</a>";
	else $list = "<a href=\"$PHP_SELF?do=cat&amp;category=". get_url( $id ) . "\">{$cat_info[$id]['name']}</a>";
	
	while ( $parent_id ) {
		
		if( $config['allow_alt_url'] ) $list = "<a href=\"" . $config['http_home_url'] . get_url( $parent_id ) . "/\">{$cat_info[$parent_id]['name']}</a>" . $separator . $list;
		else $list = "<a href=\"$PHP_SELF?do=cat&amp;category=" . get_url( $parent_id ) . "\">{$cat_info[$parent_id]['name']}</a>" . $separator . $list;
		
		$parent_id = $cat_info[$parent_id]['parentid'];

		if( !isset($cat_info[$parent_id]['id']) OR ( isset($cat_info[$parent_id]['id']) AND !$cat_info[$parent_id]['id']) ) {
			break;
		}
		
		if($parent_id) {		
			if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
		}

	}

	return $list;
}

function get_breadcrumbcategories($id, $separator="&raquo;", $last_link = true) {
	
	global $cat_info, $config, $PHP_SELF, $elements, $position;
	
	if( !$id ) return;
	
	$parent_id = $cat_info[$id]['parentid'];
	$list = $temp = array();
	$i = 0;

	if( $config['allow_alt_url'] ) $list[0]['link'] = $config['http_home_url'] . get_url($id) . "/";
	else $list[0]['link'] = "{$PHP_SELF}?do=cat&amp;category=" . get_url($id);
		
	if (!$last_link)	{
		
		$list[0]['uri'] = $list[0]['link'];
		unset($list[0]['link']);
		
	}
	
	$list[0]['name'] = $cat_info[$id]['name'];
	
	while ( $parent_id ) {
		$i++;
		
		if( $config['allow_alt_url'] ) $list[$i]['link'] = $config['http_home_url'] . get_url($parent_id) . "/";
		else $list[$i]['link'] = "{$PHP_SELF}?do=cat&amp;category=" . get_url($parent_id);
		
		$list[$i]['name'] = $cat_info[$parent_id]['name'];
		$parent_id = $cat_info[$parent_id]['parentid'];

		if( !isset($cat_info[$parent_id]['id']) OR ( isset($cat_info[$parent_id]['id']) AND !$cat_info[$parent_id]['id']) ) {
			break;
		}
		
		if($parent_id) {		
			if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
		}
		
	}

	if(count($list)) {
		
		$list = array_reverse($list);
		
		foreach($list as $value) {
			
			if( isset($value['link']) AND $value['link']) {
				$temp[] = "<a href=\"{$value['link']}\">{$value['name']}</a>";;
			} else {
				$temp[] = $value['name'];
				$value['link'] = $value['uri'];
			}
			
			$elements[] = array(
				'@type'		=> "ListItem",
				'position'	=> $position,
				'item'		=> array(
					'@id'	=> $value['link'],
					'name'	=> $value['name'],
				)
			);
			$position++;
		
		}
		
		$list = $temp;
	}	
	
	return implode($separator, $list);
}

function news_sort($do) {
	
	global $config, $lang, $category_id, $cat_info;

	if( ! $do ) $do = "main";
	
	$find_sort = "dle_sort_" . $do;
	$direction_sort = "dle_direction_" . $do;

	if($do == "cat" AND $category_id) {
		$find_sort .= "_".$category_id;
		$direction_sort .= "_".$category_id;
	}
	
	$find_sort = str_replace( ".", "", $find_sort );
	$direction_sort = str_replace( ".", "", $direction_sort );
	
	$sort = array ();
	$allowed_sort = array ('date', 'rating', 'news_read', 'comm_num','title', 'editdate' );
	
	$soft_by_array = array (

		'date' => array ( 'name' => $lang['sort_by_date'], 'value' => "date", 'direction' => "desc", 'image' => "class=\"sort_by_date\"" ),
		'editdate' => array( 'name' => $lang['sort_by_edate'], 'value' => "editdate", 'direction' => "desc", 'image' => "class=\"sort_by_editdate\""),
		'rating' => array ( 'name' => $lang['sort_by_rating'], 'value' => "rating", 'direction' => "desc", 'image' => "class=\"sort_by_rating\"" ), 
		'news_read' => array ( 'name' => $lang['sort_by_read'], 'value' => "news_read", 'direction' => "desc", 'image' => "class=\"sort_by_news_read\"" ), 
		'comm_num' => array ( 'name' => $lang['sort_by_comm'], 'value' => "comm_num", 'direction' => "desc", 'image' => "class=\"sort_by_comm_num\"" ), 
		'title' => array ( 'name' => $lang['sort_by_title'], 'value' => "title", 'direction' => "desc", 'image' => "class=\"sort_by_title\"" )

	 );

	if( !$config['allow_comments'] ) { unset($allowed_sort[3]); unset($soft_by_array['comm_num']); }
		
	if( isset( $_SESSION[$direction_sort] ) AND ($_SESSION[$direction_sort] == "desc" OR $_SESSION[$direction_sort] == "asc") ) $direction = $_SESSION[$direction_sort];
	else $direction = $config['news_msort'];

	if( isset( $_SESSION[$find_sort] ) AND $_SESSION[$find_sort] AND in_array( $_SESSION[$find_sort], $allowed_sort ) ) $soft_by = $_SESSION[$find_sort];
	elseif( $do == "cat" AND isset($cat_info[$category_id]['news_sort']) AND $cat_info[$category_id]['news_sort'] ) $soft_by = $cat_info[$category_id]['news_sort'];
	else $soft_by = $config['news_sort'];
	
	if( strtolower( $direction ) == "asc" ) {
		
		$soft_by_array[$soft_by]['image'] = "class=\"desc sort_by_{$soft_by}\"";
		$soft_by_array[$soft_by]['direction'] = "desc";
	
	} else {
		
		$soft_by_array[$soft_by]['image'] = "class=\"asc sort_by_{$soft_by}\"";
		$soft_by_array[$soft_by]['direction'] = "asc";
	}
	
	foreach ( $soft_by_array as $value ) {
		
		$sort[] = "<li " . $value['image'] . "><a href=\"#\" onclick=\"dle_change_sort('{$value['value']}','{$value['direction']}'); return false;\">" . $value['name'] . "</a></li>";
	}
	
	$sort = implode($sort);
	
	$sort = <<<HTML
<form name="news_set_sort" id="news_set_sort" method="post">
<ul class="sort">{$sort}</ul>
<input type="hidden" name="dlenewssortby" id="dlenewssortby" value="{$config['news_sort']}">
<input type="hidden" name="dledirection" id="dledirection" value="{$config['news_msort']}">
<input type="hidden" name="set_new_sort" id="set_new_sort" value="{$find_sort}">
<input type="hidden" name="set_direction_sort" id="set_direction_sort" value="{$direction_sort}">
</form>
HTML;
	
	return $sort;
}

function compare_tags($a, $b) {
	
	if( $a['tag'] == $b['tag'] ) return 0;
	
	return strcasecmp( $a['tag'], $b['tag'] );

}

function build_js($js, $config) {
	global $tpl;
	
	$js_array = array();
	$extra_js_array = array();
	$return_js = '';
	
	$i=0;
	$defer = "";
	
	$config['jquery_version'] = intval($config['jquery_version']);
	
	$ver = $config['jquery_version'] ? $config['jquery_version'] : "";

	if( is_array($tpl->js_array) AND count($tpl->js_array) ) {
		
		foreach ( $tpl->js_array as $js_file) {
			
			$js_file = str_ireplace( '{THEME}', 'templates/' . $config['skin'], $js_file );
			
			if( $js_file[0] == '/' ) {
				$js_file = substr($js_file, 1);
			}
			
			if( stripos($js_file, 'http://') === 0 OR stripos($js_file, 'https://') === 0 ) {
				$extra_js_array[] = $js_file;
			} else $js[] = $js_file;
			
		}
	
	}	

	if ($config['js_min']) {

		$js_array[] = "<script src=\"{$config['http_home_url']}engine/classes/min/index.php?g=general{$ver}&amp;v={$config['cache_id']}\"></script>";

		$default_array = array (
			"engine/classes/js/jqueryui{$ver}.js",
			'engine/classes/js/dle_js.js',
		);

		if ( count($js) ) $js = array_merge($default_array, $js); else $js = $default_array;
		
		$js_array[] = "<script src=\"{$config['http_home_url']}engine/classes/min/index.php?f=".implode(",", $js)."&amp;v={$config['cache_id']}\" defer></script>";

		$return_js = implode("\n", $js_array);

	} else {

		$default_array = array (
			"engine/classes/js/jquery{$ver}.js",
			"engine/classes/js/jqueryui{$ver}.js",
			'engine/classes/js/dle_js.js',
		);

		if ( count($js) ) $js = array_merge($default_array, $js); else $js = $default_array;

		foreach ($js as $value) {
			if($i > 0) $defer =" defer";
			$js_array[] = "<script src=\"{$config['http_home_url']}{$value}?v={$config['cache_id']}\"{$defer}></script>";
			$i++;
		}

		$return_js = implode("\n", $js_array);
	}
	
	if( count($extra_js_array) ) {
		foreach ($extra_js_array as $value) {
			$return_js .= "\n<script src=\"{$value}\" defer></script>";
		}
	}
		
	
	return $return_js;

}

function build_css($css, $config) {
	global $tpl;
	
	$css_array = array();
	$tempate_css_array = array();
	$extra_css_array = array();
	$return_css = '';

	if( is_array($tpl->css_array) AND count($tpl->css_array) ) {
		
		foreach ( $tpl->css_array as $css_file) {
			
			$css_file = str_ireplace( '{THEME}', 'templates/' . $config['skin'], $css_file );
			
			if( $css_file[0] == '/' ) {
				$css_file = substr($css_file, 1);
			}
			
			if( stripos($css_file, 'http://') === 0 OR stripos($css_file, 'https://') === 0 ) {
				
				$extra_css_array[] = $css_file;
				
			} else $tempate_css_array[] = $css_file;
			
		}
	
	}
	
	if( count($tempate_css_array) ) {	
		$tempate_css_array = array_reverse($tempate_css_array);
		
		foreach ( $tempate_css_array as $css_file) {
			array_unshift($css, $css_file);
		}
	}

	if ($config['js_min'] AND count($css) ) {

		$return_css = "<link href=\"{$config['http_home_url']}engine/classes/min/index.php?f=".implode(",", $css)."&amp;v={$config['cache_id']}\" rel=\"stylesheet\" type=\"text/css\">";


	} elseif( count($css) ) {

		foreach ($css as $value) {
			$css_array[] = "<link href=\"{$config['http_home_url']}{$value}?v={$config['cache_id']}\" rel=\"stylesheet\" type=\"text/css\">";
		}
	
		$return_css = implode("\n", $css_array);
	}
	
	if( count($extra_css_array) ) {
		foreach ($extra_css_array as $value) {
			$return_css .= "\n<link href=\"{$value}\" rel=\"stylesheet\" type=\"text/css\">";
		}
	}
		
	
	return $return_css;

}

function check_static($matches=array()) {
	global $dle_module;

	$names = $matches[2];
	$block = $matches[3];

	if ($matches[1] == "static") $action = true; else $action = false;

	$names = str_replace(" ", "", $names );
	$names = explode( ',', $names );

	if ( isset($_GET['page']) ) $page = trim($_GET['page']); else $page = "";
	
	if( $action ) {
			
		if( in_array( $page, $names ) AND $dle_module == "static" ) {
				
			return $block;
		}
		
	} else {
			
		if( !in_array( $page, $names ) OR $dle_module != "static") {
				
			return $block;
		}
		
	}
	
	return "";
}


function dle_strlen($value, $charset = "utf-8" ) {

	if( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $value, 'UTF-8' );
	} elseif( function_exists( 'iconv_strlen' ) ) {
		return iconv_strlen($value, 'UTF-8');
	}

	return strlen($value);
}

function dle_substr($str, $start, $length, $charset = "utf-8" ) {

	if( function_exists( 'mb_substr' ) ) {
		return mb_substr( $str, $start, $length, 'UTF-8' );
	
	} elseif( function_exists( 'iconv_substr' ) ) {
		return iconv_substr($str, $start, $length, 'UTF-8');
	}

	return substr($str, $start, $length);

}

function dle_strrpos($str, $needle, $charset = "utf-8" ) {

	if( function_exists( 'mb_strrpos' ) ) {
		return mb_strrpos( $str, $needle, 0, 'UTF-8' );
	
	} elseif( function_exists( 'iconv_strrpos' ) ) {
		return iconv_strrpos($str, $needle, 'UTF-8');
	}

	return strrpos($str, $needle);

}

function dle_strpos($str, $needle, $charset = "utf-8" ) {

	if( function_exists( 'mb_strpos' ) ) {
		return mb_strpos( $str, $needle, 0, 'UTF-8' );
	} elseif( function_exists( 'iconv_strrpos' ) ) {
		return iconv_strpos($str, $needle, 0, 'UTF-8');
	}

	return strpos($str, $needle);

}

function dle_strtolower($str, $charset = "utf-8" ) {

	if( function_exists( 'mb_strtolower' ) ) {
		return mb_strtolower( $str, 'UTF-8' );
	}

	return strtolower($str);

}

function check_allow_login($ip, $max ) {
	global $db, $config;

	$config['login_ban_timeout'] = intval($config['login_ban_timeout']);
	
	$max = intval($max);
	
	if( $max < 2 ) $max = 2;
	
	$block_date = time()-($config['login_ban_timeout'] * 60);

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_login_log WHERE ip='{$ip}'" );

	if ( isset( $row['count'] ) AND $row['count'] AND $row['date'] < $block_date ) {
		$db->query( "DELETE FROM " . PREFIX . "_login_log WHERE ip = '{$ip}'" );
		return true;
	}

	if ( isset( $row['count'] ) AND $row['count'] >= $max AND $row['date'] > $block_date ) return false;
	else return true;

}

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
   
  foreach ($list as $item) {

	if( function_exists( 'mb_convert_encoding' ) ) {

		$sample = mb_convert_encoding( $string, $item, $item );

	} elseif( function_exists( 'iconv' ) ) {
	
		$sample = iconv($item, $item, $string);
	
	}

	if (md5($sample) == md5($string)) return $item;

   }

   return null;
}
 
function get_ip() {
	global $config;
	
	$config['own_ip'] = trim($config['own_ip']);

	if (isset($config['own_ip']) AND $config['own_ip'] AND isset($_SERVER[$config['own_ip']]) ) $ip = $_SERVER[$config['own_ip']]; else $ip = $_SERVER['REMOTE_ADDR'];

	$temp_ip = explode(",", $ip);

	if(count($temp_ip) > 1) $ip = trim($temp_ip[0]);

	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
		return filter_var( $ip , FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	return 'not detected';
}

function get_votes($all) {
	
	$data = array ();
	
	if( $all != "" ) {
		$all = explode( "|", $all );
		
		foreach ( $all as $vote ) {
			list ( $answerid, $answervalue ) = explode( ":", $vote );
			$data[$answerid] = intval( $answervalue );
		}
	}
	
	return $data;
}

function http_get_contents( $file, $post_params = false ) {
		
	$data = false;

	if (stripos($file, "http://") !== 0 AND stripos($file, "https://") !== 0) {
		return false;
	}
		
	if( function_exists( 'curl_init' ) ) {
			
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $file );

		if( is_array($post_params) ) {

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));

		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			
		$data = curl_exec( $ch );
		curl_close( $ch );

		if( $data !== false ) return $data;
		
	} 

	if( preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen')) ) {

		if( is_array($post_params) ) {

			$file .= '?'.http_build_query($post_params);
		}

		$data = @file_get_contents( $file );
			
		if( $data !== false ) return $data;

	}

	return false;	
}

function CheckGzip(){ 

	if (headers_sent() || connection_aborted() || ini_get('zlib.output_compression')) return false; 

	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'br' ) !== false AND function_exists( 'brotli_compress' ) ) return "br";
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false AND function_exists( 'gzencode' ) ) return "gzip";
	
	return false; 
}


function GzipOut($debug=false, $mysql=false){
	global $config, $Timer, $db, $tpl, $_DOCUMENT_DATE;
	
	$s = "";

	@header("Content-type: text/html; charset=utf-8");
	
	if ($debug) $s = "\n<!-- The script execution time ".$Timer->get()." seconds -->\n<!-- The time compilation of templates ".round($tpl->template_parse_time, 5)." seconds -->\n<!-- Time executing MySQL query: ".round($db->MySQL_time_taken, 5)." seconds -->\n<!-- The total number of MySQL queries ".$db->query_num." -->";
	
	if( $debug AND function_exists( "memory_get_peak_usage" ) ) $s .="\n<!-- RAM uses ".round(memory_get_peak_usage()/(1024*1024),2)." MB -->";

	if($debug AND $mysql AND count($db->query_list) ) {
		
		$temp_list = array();
		
		foreach ($db->query_list as $value) {
			$temp_list[] = "[query] => ".$value['query']."\n[time] => ".$value['time']."\n[num] => ".$value['num'];
		}
		
		$s .="\n<!-- MySQL queries list:\n\n".implode("\n\n", $temp_list)."\n\n-->";
		
	}

	if($_DOCUMENT_DATE) {
		
		$IfModifiedSince = false;
	
		if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ) $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
		elseif( isset($_ENV['HTTP_IF_MODIFIED_SINCE']) ) $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
	
		if ($IfModifiedSince && $IfModifiedSince >= $_DOCUMENT_DATE) {
			ob_end_clean();
			header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
			die();
		}
	
		@header ("Last-Modified: " . date('r', $_DOCUMENT_DATE) ." GMT");
	}
	
	if($config['disable_frame']) {
		
		if( !preg_match('%^(http:|https:)?//(www.)?(webvisor.com)%', $_SERVER['HTTP_REFERER']) ) {
			@header ("X-Frame-Options: SAMEORIGIN");
		}
	
	}
	
	if ( !$config['allow_gzip'] ) {if ($debug) echo $s; ob_end_flush(); return;}

    $ENCODING = CheckGzip();

    if ($ENCODING) {
        $s .= "\n<!-- For compression was used $ENCODING -->\n"; 
        $Contents = ob_get_clean(); 

        if ($debug){
			
			if ($ENCODING == "br") {
				$after_len = strlen(brotli_compress($Contents));
			} else {
				$after_len = strlen(gzencode($Contents));
			}
			
            $s .= "<!-- The total size of the page: ".strlen($Contents)." bytes "; 
            $s .= "After compression: ".$after_len." bytes -->"; 
            $Contents .= $s; 
        }

        header("Content-Encoding: {$ENCODING}");
		
		if ($ENCODING == "br") {
			$Contents = brotli_compress($Contents);
		} else {
			$Contents = gzencode($Contents);
		}	
		
		echo $Contents;
		
		ob_end_flush();
        die();

    } else {
		
	   ob_end_flush(); 
       die();

    }
}

function check_xfvalue( $matches=array() ) {
	global $xfieldsdata, $preg_safe_name, $value;
	
	$matches[1] = trim($matches[1]);
	$check_values = array();

	if( ($value[3] == "select" OR $value[6]) AND isset($xfieldsdata[$value[0]]) AND $xfieldsdata[$value[0]] ){
		
		$field_value = explode( ",", $xfieldsdata[$value[0]] );
		$field_value = array_map('trim', $field_value);

	} elseif( isset($xfieldsdata[$value[0]]) ) {
		$field_value = $xfieldsdata[$value[0]];
	} else $field_value = '';

	if( preg_match( "#^{$preg_safe_name}\s*\!\=\s*['\"](.+?)['\"]#i", $matches[1], $match ) ) {

		$check_values = array_map('trim', explode(",", trim($match[1])));
		
		if( is_array($field_value) ) {

			$found = false;

			foreach ($field_value as $tenp_value) {
				if (in_array($tenp_value, $check_values)) {
					$found = true;
				}
			}

			if ($found) return ""; else return $matches[2];

		} else {

			if (!in_array($field_value, $check_values)) {
				return $matches[2];
			} else return "";

		}

	}
	
	if( preg_match( "#^{$preg_safe_name}\s*\=\s*['\"](.+?)['\"]#i", $matches[1], $match ) ) {

		$check_values = array_map('trim', explode(",", trim($match[1])));

		if ( is_array($field_value) ) {
			
			$found = false;

			foreach($field_value as $tenp_value) {
				if (in_array($tenp_value, $check_values)) {
					$found = true;
				}
			}

			if( $found ) {
				return $matches[2];
			} else return "";

		} else {

			if( in_array($field_value, $check_values) ) {
				return $matches[2];
			} else return "";
		}
	}
	
	return $matches[0];
}

function enable_lazyload( $matches=array() ) {
	global $config;

	if($config['image_lazy'] == "1") {
		$matches[0] = str_replace('src="', 'data-src="', $matches[0]);
	} else {
		$matches[0] = str_replace('src="', 'loading="lazy" src="', $matches[0]);
	}
	
	return $matches[0];
}

function deletenewsbyid( $id ) {
	global $config, $db;

	$id = intval($id);
	DLEFiles::init();
	
	$row = $db->super_query( "SELECT user_id FROM " . PREFIX . "_post_extras WHERE news_id = '{$id}'" );
	
	$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 WHERE user_id='{$row['user_id']}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_pass WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE news_id='{$id}'");

	deletecommentsbynewsid( $id );

	$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$id}'" );

	if( isset($row['images']) AND $row['images']) {
		
		$listimages = explode( "|||", $row['images'] );
	
		foreach ( $listimages as $dataimage ) {
			
			$dataimage = get_uploaded_image_info($dataimage);
			
			$query = $db->safesql( $dataimage->path );
			$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE short_story LIKE '%{$query}%' OR full_story LIKE '%{$query}%' OR xfields LIKE '%{$query}%'");

			if( isset($row['count']) AND $row['count'] ) {
				continue;
			}
			
			if( $dataimage->remote ) $disk = DLEFiles::FindDriver($dataimage->url);
			else $disk = 0;
	
			DLEFiles::Delete( "posts/" . $dataimage->path, $disk );

			if ($dataimage->hidpi) {
				DLEFiles::Delete("posts/{$dataimage->folder}/{$dataimage->hidpi}", $disk);
			}

			if( $dataimage->thumb ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $disk );

				if ($dataimage->hidpi) {
					DLEFiles::Delete("posts/{$dataimage->folder}/thumbs/{$dataimage->hidpi}", $disk);
				}
			}
			
			if( $dataimage->medium ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/medium/{$dataimage->name}", $disk );
				
				if ($dataimage->hidpi) {
					DLEFiles::Delete("posts/{$dataimage->folder}/medium/{$dataimage->hidpi}", $disk);
				}
				
			}
						
		}
	
		$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$id}'" );
	
	}

	$db->query( "SELECT * FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

	while ( $row = $db->get_row() ) {
		
		if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
		
		if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';

		DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

	}

	$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

	$sql_result = $db->query( "SELECT user_id, favorites FROM " . USERPREFIX . "_users WHERE favorites LIKE '%{$id}%'" );
	
	while ( $row = $db->get_row($sql_result) ) {
		
		$temp_fav = explode( ",", $row['favorites'] );
		$new_fav = array();
		
		foreach ( $temp_fav as $value ) {
			$value = intval($value);
			if($value != $id ) $new_fav[] = $value;
		}
		
		if(count($new_fav)) $new_fav = $db->safesql(implode(",", $new_fav));
		else $new_fav = "";
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$new_fav}' WHERE user_id='{$row['user_id']}'" );

	}
}

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);
	DLEFiles::init();
	
	$row = $db->super_query( "SELECT id, post_id, user_id, is_register, approve FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	

	if( $row['is_register'] ) {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
	}
	
	if($row['approve']) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	$db->query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE c_id = '{$id}'" );
	
	while ( $row = $db->get_row() ) {
		
		$dataimage = get_uploaded_image_info( $row['name'] );
		
		DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );
		
		if( $dataimage->thumb ) {
			
			DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );
			
		}
			
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE c_id = '{$id}'" );
	
	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}

function deletecommentsbynewsid( $id ) {
	global $config, $db;
	
	$id = intval($id);
	DLEFiles::init();
	
	$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE post_id='{$id}'" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$row['id']}'" );

		$sub_result = $db->query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE c_id = '{$row['id']}'" );
		
		while ( $file = $db->get_row( $sub_result ) ) {
			
			$dataimage = get_uploaded_image_info( $file['name'] );
			
			DLEFiles::Delete( "posts/" . $dataimage->path, $file['driver'] );
			
			if( $dataimage->thumb ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $file['driver'] );
				
			}

		}
		
		$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE c_id = '{$row['id']}'" );
	
	}
	
	$result = $db->query( "SELECT COUNT(*) as count, user_id FROM " . PREFIX . "_comments WHERE post_id='{$id}' AND is_register='1' GROUP BY user_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-{$row['count']} WHERE user_id='{$row['user_id']}'" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$id}'" );


}
function deleteuserbyid($id) {
	global $config, $db;

	$id = intval($id);

	$row = $db->super_query("SELECT user_id, name, foto FROM " . USERPREFIX . "_users WHERE user_id='{$id}'");

	if (isset($row['user_id']) and $row['user_id']) {

		if ($row['foto'] and count(explode("@", $row['foto'])) != 2) {

			$url = @parse_url($row['foto']);
			$row['foto'] = basename($url['path']);

			$driver = DLEFiles::getDefaultStorage();
			$config['avatar_remote'] = intval($config['avatar_remote']);
			if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];

			DLEFiles::init($driver);
			DLEFiles::Delete("fotos/" . totranslit($row['foto']));
		}

		$db->query("DELETE FROM " . USERPREFIX . "_social_login WHERE uid='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_banned WHERE users_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' OR user_from='{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_notice WHERE user_id = '{$row['user_id']}'");
		$db->query("DELETE FROM " . PREFIX . "_subscribe WHERE user_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . PREFIX . "_logs WHERE `member` = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_comment_rating_log WHERE `member` = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_vote_result WHERE name = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_poll_log WHERE `member` = '{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_users WHERE user_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_users_delete WHERE user_id='{$row['user_id']}'");
		deletepmuserbyid($row['user_id']);
	}
}

function deletepmuserbyid($id) {
	global $db;

	$sql_result = $db->query("SELECT c.id AS conversation_id, c.sender_id, c.recipient_id FROM " . USERPREFIX . "_conversations c WHERE c.sender_id = '{$id}' OR c.recipient_id = '{$id}'");

	while ($row = $db->get_row($sql_result)) {
		
		if ($id == $row['sender_id']) {
			$sync_user_id = $row['recipient_id'];
		} else {
			$sync_user_id = $row['sender_id'];
		}

		if($sync_user_id == $id ) $sync_user_id = 0;

		$db->query("DELETE FROM " . USERPREFIX . "_conversations WHERE id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversation_reads WHERE conversation_id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversation_users WHERE conversation_id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversations_messages WHERE conversation_id='{$row['conversation_id']}'");

		if( $sync_user_id ) {
			$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$sync_user_id}'");
			$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$sync_user_id}'");
		}
	}
}

function normalize_name($var, $punkt = true) {
	
	if ( !is_string($var) ) return;

	$var = str_replace(chr(0), '', $var);
	
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/u", "-", $var );
	$var = str_replace( "/", "-", $var );
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );
	$var = preg_replace( '#[.]+#i', '.', $var );
	
	return $var;
}

function clearfilepath( $file, $ext=array() ) {

	$file = trim(str_replace(chr(0), '', (string)$file));
	$file = str_replace(array('/', '\\'), '/', $file);
	
	$path_parts = pathinfo( $file );

	if( count($ext) ) {
		if ( !in_array( $path_parts['extension'], $ext ) ) return '';
	}
	
	$filename = normalize_name($path_parts['basename'], true);
	
	if( !$filename) return '';
	
	$parts = array_filter(explode('/', $path_parts['dirname']), 'strlen');
	
	$absolutes = array();
	
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = normalize_name($part, false);
		}
	}

	$path = implode('/', $absolutes);
	
	if ( $path ) return implode('/', $absolutes).'/'.$filename;
	else return '';

}

function cleanpath($path) {
	$path = trim(str_replace(chr(0), '', (string)$path));
	$path = str_replace(array('/', '\\'), '/', $path);
	$parts = array_filter(explode('/', $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = totranslit($part, false, false);
		}
	}

	return implode('/', $absolutes);
}

function get_uploaded_image_info( $file, $root_folder = 'posts', $force_size = false ) {
	global $config;
	
	$info = array();
	$file = explode("|", $file);
	$path = $file[0];
	$path = str_replace('&#58;',':', $path);

	if( stripos($path, "https://" ) === 0 OR stripos($path, "http://" ) === 0 OR stripos($path, "//" ) === 0 ) {
		
		$info['remote'] = true;
		$info['local'] 	= false;
		$info['exists'] = true;
		$info['url'] 	= $path;
		
		$path = explode("/{$root_folder}/", $path);
		
		$info['path'] = $path[1];
		$info['root'] = $path[0] . "/{$root_folder}/";
		
	} else {
		
		$info['remote'] = false;
		$info['exists'] = true;
		$info['path'] 	= $path;
		$info['root']   = $config['http_home_url'] . "uploads/{$root_folder}/";
		$info['url'] 	= $info['root'] . $info['path'];
		
		if( !file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) ) {
			
			$info['url'] = 	$config['http_home_url'] . "engine/skins/images/noimage.jpg";
			$file[1] = 0;
			$file[2] = 0;
			$file[3] = "0x0";
			$file[4] = "0 b";
			$info['exists'] = false;
	
		}

	}

	if( count($file) == 1) {

		$info['local_check'] = true;
		$file[1] = 0;
		$file[2] = 0;

		$files_array = explode('/', $file[0]);

		if( count($files_array) == 2 ) {
			$folder_prefix = $files_array[0].'/';
			$file_name =  $files_array[1];
		} else {
			$folder_prefix = '';
			$file_name =  $files_array[0];
		}

		if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $folder_prefix . "thumbs/" . $file_name ) ) $file[1] = 1;
		if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $folder_prefix . "medium/" . $file_name ) ) $file[2] = 1;
		
		if( $force_size ) {
			
			if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) ) {
				
				$img_info =  @getimagesize( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] );
				$file[3] = "{$img_info[0]}x{$img_info[1]}";
				$file[4] = formatsize( filesize( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) );
	
			} else {
				
				$file[3] = "0x0";
				$file[4] = "0 b";
				
			}
				
		}
		
		
	} else $info['local_check'] = false;

	$parts = pathinfo($info['path']);
	$info['folder'] = $parts['dirname'];
	$info['name'] = $parts['basename'];

	if (isset($file[5]) and $file[5]) {
		$info['hidpi'] = pathinfo($info['name'], PATHINFO_FILENAME) . '@x2.' . pathinfo($info['name'], PATHINFO_EXTENSION);
	} else {
		$info['hidpi'] = false;
	}

	if (isset($file[1]) and $file[1]) {
		$info['thumb'] = $info['root'] . $info['folder'] . "/thumbs/" . $info['name'];

		if ($info['hidpi']) $info['hidpi_thumb'] = $info['root'] . $info['folder'] . "/thumbs/" . $info['hidpi'];
	} else {
		$info['thumb'] = false;
	}

	if (isset($file[2]) and $file[2]) {
		$info['medium'] = $info['root'] . $info['folder'] . "/medium/" . $info['name'];

		if ($info['hidpi']) $info['hidpi_medium'] = $info['root'] . $info['folder'] . "/medium/" . $info['hidpi'];
	} else {
		$info['medium'] = false;
	}

	if (isset($file[3]) and $file[3]) $info['dimension'] = $file[3]; else $info['dimension'] = false;
	if (isset($file[4]) and $file[4]) $info['size'] = $file[4]; else $info['size'] = false;

	return (object)$info;
}

function is_md5hash( $md5 = '' ) {
  return strlen($md5) == 32 && ctype_xdigit($md5);
}

function generate_pin(){
	
	$pin = "";
	
	for($i = 0; $i < 5; $i ++) {
		$pin .= random_int(0, 9);
	}
	
    return $pin;
}

function isSSL() {
    if( (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
		|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')
		|| (!empty($_SERVER['HTTP_X_FORWARDED_SCHEME']) && strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']) == 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
		|| (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on')
        || (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
		|| (!empty($_SERVER['CF_VISITOR']) && $_SERVER['CF_VISITOR'] == '{"scheme":"https"}')
		|| (!empty($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR'] == '{"scheme":"https"}')
    ) return true; else return false;
}

function preg_quote_replacement($str) {
    return str_replace(array('\\', '$'), array('\\\\', '\\$'), $str);
}

function UniqIDReal($lenght = 10) {
	if (function_exists("random_bytes")) {
		$bytes = random_bytes(ceil($lenght / 2));
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
	} else {
		throw new Exception("no cryptographically secure random function available");
	}
	return substr(bin2hex($bytes), 0, $lenght);
}

function isBotDetected()
{

	if (preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|yandex|YaTurkiyeBot|YaDirect|yeti|Zeus/i', $_SERVER['HTTP_USER_AGENT'])) {
		return true;
	}

	return false;
}

function isTimestamp($string) {
	try {
		new DateTime('@' . $string);
	} catch (Exception $e) {
		return false;
	}
	return true;
}

function fix_quote_title($matches = array()) {
	global $config, $lang, $PHP_SELF, $user_group, $member_id;

	$return_string = '<div class="title_quote"';
	$title_text = '';

	if (preg_match("#data-commenttime=['\"](.+?)['\"]#i", $matches[1], $match)) {

		$time = intval($match[1]);

		if (isTimestamp($time)) {
			$return_string .= " data-commenttime=\"{$time}\"";
			$title_text .= difflangdate($config['timestamp_comment'], $time) . ', ';
		}
	}

	if (preg_match("#data-commentuser=['\"](.+?)['\"]#i", $matches[1], $match)) {

		$match[1] = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$author = htmlspecialchars($match[1], ENT_COMPAT | ENT_HTML5, 'UTF-8');
		$encoded_author = urlencode($match[1]);
		$encoded_author = str_replace('%C2%A0', '+', $encoded_author);

		if ($config['allow_alt_url']) {
			$go_page = $config['http_home_url'] . "user/" . $encoded_author . "/";
		} else {
			$go_page = "$PHP_SELF?subaction=userinfo&user=" . $encoded_author;
		}

		$go_page = "onclick=\"ShowProfile('" . $encoded_author . "', '" . htmlspecialchars($go_page, ENT_QUOTES, 'UTF-8') . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";

		if ($config['allow_alt_url']) $link_author = "<a {$go_page} href=\"" . $config['http_home_url'] . "user/" . $encoded_author . "/\">" . $author . "</a>";
		else $link_author = "<a {$go_page} href=\"$PHP_SELF?subaction=userinfo&amp;user=" . $encoded_author . "\">" . $author . "</a>";


		if ($author) {
			$return_string .= " data-commentuser=\"{$author}\"";
			$title_text .= $link_author . ' ' . $lang['user_says'];
		}
	}

	$return_string .= '>';

	if ($title_text) $return_string .= $title_text;
	else $return_string .= $matches[2];

	$return_string .= '</div>';

	return $return_string;
}

function generateCodeVerifier($length = 50) {
	return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
}

function generateCodeChallenge($codeVerifier) {
	return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
}

function get_related_ids($body, $id = false, $categories = false) {
	global $config, $db, $user_group, $cat_info;
	
	$id = intval($id) ? intval($id) : 0;

	if ($config['no_date'] and !$config['news_future']) $where_date = " AND date < '" . date("Y-m-d H:i:s", time()) . "'";
	else $where_date = "";

	$body = stripslashes(stripslashes($body));
	$body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$body = strip_tags($body);
	$body = trim(str_replace(array('+', '-', '*', '\\', '\'', '"','.', ',', '!', '%',  '(', ')', '[', ']', '{', '}', "\n", "\r", "\x60", "\t"), ' ', $body));
	$body = preg_replace('/\s+/u', ' ', $body);

	if (dle_strlen($body) > 2000) {

		$body = dle_substr($body, 0, 2000);

		if (($temp_dmax = dle_strrpos($body, ' '))) $body = dle_substr($body, 0, $temp_dmax);
	}

	$body = $db->safesql($body);

	$config['related_number'] = intval($config['related_number']);
	if ($config['related_number'] < 1) $config['related_number'] = 5;

	$allowed_cats = array();
	$not_allowed_cats = array();

	foreach ($user_group as $value) {
		
		if ($value['allow_cats'] != "all" AND !$value['allow_short']) $allowed_cats[] = $db->safesql($value['allow_cats']);
		if ($value['not_allow_cats'] != "" AND !$value['allow_short']) $not_allowed_cats[] = $db->safesql($value['not_allow_cats']);

	}

	$join_category = "";

	if (count($allowed_cats)) {

		$allowed_cats = implode(",", $allowed_cats);
		$allowed_cats = explode(",", $allowed_cats);
		$allowed_cats = array_unique($allowed_cats);
		sort($allowed_cats);

		if ($config['allow_multi_category']) {

			$join_category = "p INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode(',', $allowed_cats) . "')) c ON (p.id=c.news_id) ";
			$allowed_cats = "";
		} else {

			$allowed_cats = "category IN ('" . implode("','", $allowed_cats) . "') AND ";
		}
	} else $allowed_cats = "";

	if (count($not_allowed_cats)) {

		$not_allowed_cats = implode(",", $not_allowed_cats);
		$not_allowed_cats = explode(",", $not_allowed_cats);
		$not_allowed_cats = array_unique($not_allowed_cats);
		sort($not_allowed_cats);

		if ($config['allow_multi_category']) {

			$not_allowed_cats = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode(',', $not_allowed_cats) . ") ) AND ";
			$join_category = "p ";
		} else {

			$not_allowed_cats = "category NOT IN ('" . implode("','", $not_allowed_cats) . "') AND ";
		}
	} else $not_allowed_cats = "";

	if ($config['related_only_cats'] AND $categories) {

		$allowed_cats = "";
		$not_allowed_cats = "";
		$allow_sub_cats = true;
		$all_cats = explode(",", $categories);
		$get_cats = array();

		foreach ($all_cats as $value) {

			if ($cat_info[$value]['show_sub']) {

				if ($cat_info[$value]['show_sub'] == 1) $get_cats[] = get_sub_cats($value);
				else {
					$get_cats[] = $value;
				}
			} else {

				if ($config['show_sub_cats']) $get_cats[] = get_sub_cats($value);
				else {
					$get_cats[] = $value;
				}
			}
		}

		$get_cats = implode("|", $get_cats);
		$get_cats = explode("|", $get_cats);

		if (count($get_cats) < 2) $allow_sub_cats = false;

		$get_cats = implode("|", $get_cats);

		if ($config['allow_multi_category']) {

			$get_cats = str_replace("|", "','", $get_cats);
			$join_category = "p INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $get_cats . "')) c ON (p.id=c.news_id) ";
			$where_category = "";

		} else {

			if ($allow_sub_cats) {

				$get_cats = str_replace("|", "','", $get_cats);
				$where_category = "category IN ('" . $get_cats . "') AND ";
			} else {

				$where_category = "category = '{$get_cats}' AND ";
			}
		}

	} else $where_category = "";

	if ($id) {
		$id = " AND id != {$id}";
	} else $id = '';
	
	$related_ids = array();

	$db->query("SELECT id, MATCH (title, short_story, full_story, xfields) AGAINST ('{$body}') as score FROM " . PREFIX . "_post {$join_category}WHERE {$where_category}{$allowed_cats}{$not_allowed_cats}MATCH (title, short_story, full_story, xfields) AGAINST ('{$body}'){$id} AND approve=1" . $where_date . " ORDER BY score DESC LIMIT " . $config['related_number']);

	while ($related = $db->get_row()) {
		$related_ids[] = $related['id'];
	}

	if (count($related_ids)) {
		$related_ids = implode(",", $related_ids);
	} else $related_ids = '0';

	return $related_ids;
}	