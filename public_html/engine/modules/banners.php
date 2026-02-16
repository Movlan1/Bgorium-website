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
File: banners.php
-----------------------------------------------------
Use: banners
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if ( !defined('BANNERS') ) {

	define( 'BANNERS', 1 );
	$first_include = true;

} else $first_include = false;

$banners = get_vars( "banners" );

if( ! is_array( $banners ) ) {
	$banners = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_banners ORDER BY id ASC" );
	
	while ( $row_b = $db->get_row() ) {
		
		$banners[$row_b['id']] = array ();
		
		foreach ( $row_b as $key => $value ) {
			$banners[$row_b['id']][$key] = $value;
		}
	
	}
	set_vars( "banners", $banners );
	$db->free();
}

$ban = array ();
$banner_in_news = array ();

if( count( $banners ) > 0 ) {
	foreach ( $banners as $name => $value ) {
		if( $value['approve'] ) { 
			
			$params_banner = "";

			if( $value['category'] ) {
				$value['category'] = explode( ',', $value['category'] );

				if( $dle_module != "showfull" ) {

					if (!in_array($category_id, $value['category'])) $value['code'] = "";

				} else {

					if( !isset($row['cats']) OR !is_array($row['cats']) ) $row['cats'] = array();

					$allow_this_banner = false;
					
					foreach ($row['cats'] as $n_cat) {
						if( in_array($n_cat, $value['category']) ) $allow_this_banner = true;
					}

					if ( !$allow_this_banner ) $value['code'] = "";

				}

			}
			
			if( $value['main'] ) {
				if( $_SERVER['QUERY_STRING'] != "" ) $value['code'] = "";
			}

			if( $value['fpage'] AND intval($_GET['cstart']) > 1 ) $value['code'] = "";
			if ($value['start'] AND $_TIME < $value['start'] ) $value['code'] = "";
			if ($value['end'] AND $_TIME > $value['end'] ) $value['code'] = "";
			
			$value['grouplevel'] = explode( ',', $value['grouplevel'] );
			
			if( $value['grouplevel'][0] != "all" and !in_array( $member_id['user_group'], $value['grouplevel'] ) ) {
				$value['code'] = "";
			}
			
			$value['devicelevel'] = explode( ',', $value['devicelevel'] );
			
			if( $value['devicelevel'][0] AND $value['devicelevel'][0] != "all" ) {
				$tmp_show = false;
				
				foreach ($value['devicelevel'] as $device) {
					if( $device == 1 AND $tpl->desktop ) $tmp_show = true;
					if( $device == 2 AND $tpl->tablet ) $tmp_show = true;
					if( $device == 3 AND $tpl->smartphone ) $tmp_show = true;
				}
				
				if(! $tmp_show ) $value['code'] = "";
			}
			
			if ($value['max_views'] AND $value['views'] >= $value['max_views'] ) {
				$value['code'] = "";
			}
			
			if ($value['max_counts'] AND $value['clicks'] >= $value['max_counts'] ) {
				$value['code'] = "";
			}

			if( trim($value['allowed_country']) AND !DLECountry::Check($value['allowed_country']) ) {
				$value['code'] = "";
			}

			if (trim($value['not_allowed_country']) AND DLECountry::Check($value['not_allowed_country'])) {
				$value['code'] = "";
			}

			if( $value['allow_views'] ) {
				$params_banner .="data-dlebviews=\"yes\" ";
			}
			
			if( $value['allow_counts'] ) {
				$params_banner .="data-dlebclicks=\"yes\" ";
			}
			
			if($params_banner AND $value['code']) {
				$value['code'] = "<div class=\"dle_b_{$value['banner_tag']}\" data-dlebid=\"{$value['id']}\" {$params_banner}>".$value['code']."</div>";
			}
			
			$value['code'] = str_ireplace( "{include", "&#123;include", $value['code'] );
			$value['code'] = str_ireplace( "{content", "&#123;content", $value['code'] );
			$value['code'] = str_ireplace( "{custom", "&#123;custom", $value['code'] );
		
			if( $value['code'] AND $value['short_place'] ) {
				
				if( !isset($ban_short) ) {
					
					$ban_short = array('top' => array(),
									   'cen' => array(),
									   'down' => array()
									   );
					
				}
				
				switch ($value['short_place']) 
				{
					case 1 :
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 2 :
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 3 :
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 4 :
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 5 :
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 6 :
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 7 :
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
				}
			
			}

			if ($value['code'] and $value['comments_place']) {

				if (!isset($banners_in_comments)) {

					$banners_in_comments = array(
						'top' => array(),
						'cen' => array(),
						'down' => array()
					);
				}

				switch ($value['comments_place']) {
					case 1:
						$banners_in_comments['top'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 2:
						$banners_in_comments['cen'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 3:
						$banners_in_comments['down'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 4:
						$banners_in_comments['top'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						$banners_in_comments['down'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 5:
						$banners_in_comments['cen'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						$banners_in_comments['down'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 6:
						$banners_in_comments['cen'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						$banners_in_comments['top'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;

					case 7:
						$banners_in_comments['cen'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						$banners_in_comments['top'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						$banners_in_comments['down'][] = array("text" => $value['code'], "zakr" => $value['bstick']);
						break;
				}
			}

			if( $value['innews'] ) $banner_in_news[] = $value['banner_tag'];
			
			$ban[$value['banner_tag']][] = $value['code'];
		
		}
	}
}


foreach ( $ban as $key => $value ) {
	
	if( ($r_key = count( $value )) > 1 ) {
		
		for($i = 0; $i < $r_key; $i ++) {
			
			if( $ban[$key][$i] == '' ) unset( $ban[$key][$i] );
		
		}
	}

	sort($ban[$key]);

	if ( isset( $_SESSION['banners'][$key] ) AND count($ban[$key]) > 1 ){
	
		$_SESSION['banners'][$key] = intval( $_SESSION['banners'][$key] );

		if( ($_SESSION['banners'][$key] < (count($ban[$key])-1)) OR ( $_SESSION['banners'][$key] == (count($ban[$key])-1) AND !$first_include) ) {

			if($first_include) {
				$r_key = $_SESSION['banners'][$key]+1;
			} else {
				$r_key = $_SESSION['banners'][$key];
			}

		} else $r_key = 0;

	} else {

		if( is_array($ban[$key]) AND count($ban[$key]) > 1 ) {
			$r_key = array_rand( $ban[$key] );
		} else $r_key = 0;

	}

	$_SESSION['banners'][$key] = $r_key;
	$ban[$key] = isset($ban[$key][$r_key]) ? $ban[$key][$r_key] : '';

}

$banners = $ban;
$ban = array ();
unset( $ban );

?>