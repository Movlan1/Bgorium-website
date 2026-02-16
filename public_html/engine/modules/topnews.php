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
 File: topnews.php
-----------------------------------------------------
 Use: view of the rating of articles
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$tpl->result['topnews'] = dle_cache( "topnews", $config['skin'], true );

if( $tpl->result['topnews'] === false ) {
	
	$this_month = date( 'Y-m-d H:i:s', $_TIME );
	$tpl->result['topnews'] = '';
	$tpl->load_template( 'topnews.tpl' );

	if( stripos( $tpl->copy_template, "[xf" ) !== false OR stripos( $tpl->copy_template, "[ifxf" ) !== false ) {

		$xfound = true;
		$xfields = xfieldsload();

		if(count($xfields)) {
			$temp_xf = $xfields;
			foreach ($temp_xf as $k => $v) {
				if (stripos($tpl->copy_template, $v[0]) === false) {
					unset($xfields[$k]);
				}
			}
			unset($temp_xf);
		}
		
	} else $xfound = false;

	$config['top_number'] = intval($config['top_number']);
	if ($config['top_number'] < 1 ) $config['top_number'] = 10;
	
	$db->query( "SELECT p.id, p.date, p.short_story, p.xfields, p.title, p.category, p.alt_name FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE p.approve=1 AND p.date >= '$this_month' - INTERVAL 1 MONTH AND p.date < '$this_month' ORDER BY rating DESC, comm_num DESC, news_read DESC, date DESC LIMIT 0,{$config['top_number']}" );
	
	while ( $row = $db->get_row() ) {
		
		$row['date'] = strtotime( $row['date'] );

		if( ! $row['category'] ) {
			$my_cat = "---";
			$my_cat_link = "---";
		} else {
			
			$my_cat = array ();
			$my_cat_link = array ();
			$cat_list = explode( ',', $row['category'] );
		 
			if( count( $cat_list ) == 1 ) {
				
				if( $cat_info[$cat_list[0]]['id'] ) {
					$my_cat[] = $cat_info[$cat_list[0]]['name'];
					$my_cat_link = get_categories( $cat_list[0], $config['category_separator']);
				} else {
					$my_cat_link = "---";
				}
			
			} else {
				
				foreach ( $cat_list as $element ) {
					if( $element AND $cat_info[$element]['id'] ) {
						$my_cat[] = $cat_info[$element]['name'];
						if( $config['allow_alt_url'] ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
						else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category=". get_url( $element ) . "\">{$cat_info[$element]['name']}</a>";
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
		
		if( $config['allow_alt_url'] ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( $row['category'] and $config['seo_type'] == 2 ) {
					
					$cats_url = get_url( $row['category'] );
					
					if( $cats_url ) $cats_url .= "/";
			
					$full_link = $config['http_home_url'] . $cats_url . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		
		}
		
		$row['category'] = intval( $row['category'] );
		
		$compare_date = compare_days_date( $row['date'], $config['allow_cache'] );

		if( !$compare_date ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( $compare_date == 1 ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
		
		}

		$news_date = $row['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

		$tpl->set( '{category}', $my_cat );
		$tpl->set( '{link-category}', $my_cat_link );

		$row['title'] = stripslashes( $row['title'] );

		$row['title'] = str_replace( "{", "&#123;", $row['title'] );

		$tpl->set( '{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['title'], ENT_QUOTES, 'UTF-8' ) ) );
		
		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$tpl->set( $matches[0], clear_content($row['title'], $matches[1]) );
		}


		$tpl->set( '{link}', $full_link );

		$row['short_story'] = stripslashes( $row['short_story'] );
		$row['xfields'] = stripslashes( $row['xfields'] );
		
		if (stripos ( $row['short_story'], "[hide" ) !== false ) {
			
			$row['short_story'] = preg_replace_callback ( "#\[hide(.*?)\](.+?)\[/hide\]#is", 
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
	
			}, $row['short_story'] );
		}

		if (stripos ( $tpl->copy_template, "{image-" ) !== false) {

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
				$i=0;
				foreach($images as $url) {
					$i++;
					$tpl->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl->copy_template );
					$tpl->copy_template = str_replace( '[image-'.$i.']', "", $tpl->copy_template );
					$tpl->copy_template = str_replace( '[/image-'.$i.']', "", $tpl->copy_template );
					$tpl->copy_template = preg_replace( "#\[not-image-{$i}\](.+?)\[/not-image-{$i}\]#is", "", $tpl->copy_template );
				}

			}

			$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\[not-image-(.+?)\]#i", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\[/not-image-(.+?)\]#i", "", $tpl->copy_template );
			
		}

		if ($config['image_lazy']) $row['short_story'] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $row['short_story'] );
		
		$tpl->set( '{text}', $row['short_story'] );

		if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$tpl->set( $matches[0], clear_content($row['short_story'], $matches[1]) );
		}

		if( $xfound AND count($xfields) ) {
			$xfieldsdata = xfieldsdataload( $row['xfields'] );
			
			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );
				
				if( $value[20] ) {
				  
				  $value[20] = explode( ',', $value[20] );
				  
				  if( $value[20][0] AND !in_array( $member_id['user_group'], $value[20] ) ) {
					  $xfieldsdata[$value[0]] = "";
				  }
				  
				}
			
				if ( $value[3] == "yesorno" ) {
					
					if( intval($xfieldsdata[$value[0]]) ) {
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
					$tpl->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl->copy_template );
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
								
								if( $value[25] ) {
									
									if($value[26]) $value2 = langdate($value[24], $value2);
									else $value2 = langdate($value[24], $value2, false, $customlangdate);
									
								} else $value2 = date( $value[24], $value2 );
	
							}
	
							if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". rawurlencode( $value4 ) . "/\">" . $value2 . "</a>";
							else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . rawurlencode( $value4 ) . "\">" . $value2 . "</a>";
							
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
	
				}
				
				$xfieldsdata[$value[0]] = isset($xfieldsdata[$value[0]]) ? $xfieldsdata[$value[0]] : '';
				
				if($value[3] == "image" AND !$xfieldsdata[$value[0]]) {
					$tpl->set( "[xfvalue_thumb_url_{$value[0]}]", "");
					$tpl->set( "[xfvalue_image_url_{$value[0]}]", "");
					$tpl->set( "[xfvalue_image_description_{$value[0]}]", "");
				}
				
				if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] AND stripos ( $tpl->copy_template, "[xfvalue_{$value[0]}" ) !== false) {
					
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
					
					if(count($gallery_single_image) ) {
						foreach($gallery_single_image as $temp_key => $temp_value) $tpl->set( $temp_key, $temp_value);
					}
					
					$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">".implode($gallery_image)."</ul>";
					
				}
				
				$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name} image=\"(\d+)\"\\](.*?)\\[/xfgiven_{$preg_safe_name} image=\"(\d+)\"\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} image=\"(\d+)\"\\]'i", "", $tpl->copy_template );
				$tpl->copy_template = preg_replace( "'\\[/xfnotgiven_{$preg_safe_name} image=\"(\d+)\"\\]'i", "", $tpl->copy_template );
			
				if ($value[30]) $xfieldsdata[$value[0]] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $xfieldsdata[$value[0]] );
	
				$tpl->set( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]]);
				
				if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches ) ) {
					$tpl->set( $matches[0], clear_content($xfieldsdata[$value[0]], $matches[1]) );
				} 
	
			}
		}


		$tpl->compile( 'topnews' );
	}

	$tpl->clear();	
	$db->free();

	if ($config['allow_links'] AND function_exists('replace_links') AND isset($replace_links['news'])){
		$tpl->result['topnews'] = replace_links($tpl->result['topnews'], $replace_links['news']);
	}

	create_cache( "topnews", $tpl->result['topnews'], $config['skin'], true );
}
?>