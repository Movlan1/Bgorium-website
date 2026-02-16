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
 File: show.related.php
-----------------------------------------------------
 Use:  view related news
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if ( $allow_full_cache ) $related_buffer = dle_cache( "related", NEWS_ID.$config['skin'], true ); else $related_buffer = false;

if( $related_buffer === false ) {

	$db->query( "SELECT id, date, short_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE id IN(". RELATED_IDS .") AND approve=1 ORDER BY FIND_IN_SET(id, '". RELATED_IDS ."') LIMIT " . $config['related_number'] );

	$tpl2 = new dle_template();
	$tpl2->dir = TEMPLATE_DIR;
	$tpl2->load_template( 'relatednews.tpl' );
					
	while ( $related = $db->get_row() ) {

		if (isset($showed_news_ids) AND is_array($showed_news_ids)) {
			$showed_news_ids[] = $related['id'];
		}

		$related['date'] = strtotime( $related['date'] );

		if( ! $related['category'] ) {
			$my_cat = "---";
			$my_cat_link = "---";
		} else {
			
			$my_cat = array ();
			$my_cat_link = array ();
			$rel_cat_list = explode( ',', $related['category'] );
			
			if( count( $rel_cat_list ) == 1 ) {
				
				if( $cat_info[$rel_cat_list[0]]['id'] ) {
					$my_cat[] = $cat_info[$rel_cat_list[0]]['name'];
					$my_cat_link = get_categories( $rel_cat_list[0], $config['category_separator'] );
				} else {
					$my_cat_link = "---";
				}
	
			} else {
				
				foreach ( $rel_cat_list as $element ) {
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
				
				if( $related['category'] and $config['seo_type'] == 2 ) {
					
					$cats_url = get_url( $related['category'] );
					
					if( $cats_url ) $cats_url .= "/";

					$rel_full_link = $config['http_home_url'] . $cats_url . $related['id'] . "-" . $related['alt_name'] . ".html";
				
				} else {
					
					$rel_full_link = $config['http_home_url'] . $related['id'] . "-" . $related['alt_name'] . ".html";
				
				}
			
			} else {
				
				$rel_full_link = $config['http_home_url'] . date( 'Y/m/d/', $related['date'] ) . $related['alt_name'] . ".html";
			}
		
		} else {
			
			$rel_full_link = $config['http_home_url'] . "index.php?newsid=" . $related['id'];
		
		}
		
		$related['category'] = intval( $related['category'] );
		
		$related['title'] = strip_tags( stripslashes( $related['title'] ) );

		$tpl2->set( '{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars( $related['title'], ENT_QUOTES, 'UTF-8' ) ) );
		$tpl2->set( '{link}', $rel_full_link );
		$tpl2->set( '{category}', $my_cat );
		$tpl2->set( '{link-category}', $my_cat_link );
	
		$compare_date = compare_days_date($related['date']);

		if( !$compare_date ) {
			
			$tpl2->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $related['date'] ) );
		
		} elseif( $compare_date == 1 ) {
			
			$tpl2->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $related['date'] ) );
		
		} else {
			
			$tpl2->set( '{date}', langdate( $config['timestamp_active'], $related['date'] ) );
		
		}
		$news_date = $related['date'];
		$tpl2->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl2->copy_template );

		$related['short_story'] = stripslashes( $related['short_story'] );
		
		if (stripos ( $related['short_story'], "[hide" ) !== false ) {
			
			$related['short_story'] = preg_replace_callback ( "#\[hide(.*?)\](.+?)\[/hide\]#is", 
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
	
			}, $related['short_story'] );
		}

		if (stripos ( $tpl2->copy_template, "image-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $related['short_story'], $media);
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
					$tpl2->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl2->copy_template );
					$tpl2->copy_template = str_replace( '[image-'.$i.']', "", $tpl2->copy_template );
					$tpl2->copy_template = str_replace( '[/image-'.$i.']', "", $tpl2->copy_template );
					$tpl2->copy_template = preg_replace( "#\[not-image-{$i}\](.+?)\[/not-image-{$i}\]#is", "", $tpl2->copy_template );
				}

			}

			$tpl2->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl2->copy_template );			
			$tpl2->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl2->copy_template );
			$tpl2->copy_template = preg_replace( "#\[not-image-(.+?)\]#i", "", $tpl2->copy_template );
			$tpl2->copy_template = preg_replace( "#\[/not-image-(.+?)\]#i", "", $tpl2->copy_template );

		}

		if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl2->copy_template, $matches ) ) {
			$tpl2->set( $matches[0], clear_content($related['short_story'], $matches[1]) );
		} else $tpl2->set( '{text}', $related['short_story'] );

		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl2->copy_template, $matches ) ) {
			$tpl2->set( $matches[0], clear_content($related['title'], $matches[1]) );
		}

		if( count($xfields) ) {
			$xfieldsdata = xfieldsdataload( $related['xfields'] );

			$tpl2->copy_template = preg_replace_callback( "#\\[ifxf(set|notset) fields=['\"](.+?)['\"]\\](.+?)\[/ifxf\\1\]#is",
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
			$tpl2->copy_template);

			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );

				if( !isset($xfieldsdata[$value[0]]) ) $xfieldsdata[$value[0]] = '';
				
				$xfieldsdata[$value[0]] = stripslashes( $xfieldsdata[$value[0]] );
				
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
					if($xfieldsdata[$value[0]] == "") $xfgiven = false; else $xfgiven = true;
				}
				
				if( !$xfgiven ) {
					$tpl2->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
					$tpl2->copy_template = str_ireplace( "[xfnotgiven_{$value[0]}]", "", $tpl2->copy_template );
					$tpl2->copy_template = str_ireplace( "[/xfnotgiven_{$value[0]}]", "", $tpl2->copy_template );
				} else {
					$tpl2->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
					$tpl2->copy_template = str_ireplace( "[xfgiven_{$value[0]}]", "", $tpl2->copy_template );
					$tpl2->copy_template = str_ireplace( "[/xfgiven_{$value[0]}]", "", $tpl2->copy_template );
				}
				
				if(strpos( $tpl2->copy_template, "[ifxfvalue {$value[0]}" ) !== false ) {
					$tpl2->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl2->copy_template );
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

								if (strpos($tpl2->copy_template, "[xfvalue_{$value[0]} format=") !== false) {

									$tpl2->copy_template = preg_replace_callback("#\\[xfvalue_{$preg_safe_name} format=['\"](.*?)['\"]\\]#i",
										function ($matches) use ($value, $value2, $value4, $customlangdate, $config, $PHP_SELF) {

											$matches[1] = trim($matches[1]);

											if ($value[25]) {

												if ($value[26]) $value2 = langdate($matches[1], $value2);
												else return $value2 = langdate($matches[1], $value2, false, $customlangdate);
											} else $value2 = date($matches[1], $value2);

											if ($config['allow_alt_url']) return "<a href=\"" . $config['http_home_url'] . "xfsearch/" . $value[0] . "/" . rawurlencode(dle_strtolower($value4)) . "/\">" . $value2 . "</a>";
											else return "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=" . $value[0] . "&amp;xf=" . rawurlencode(dle_strtolower($value4)) . "\">" . $value2 . "</a>";

										}, $tpl2->copy_template);
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

					if (strpos ( $tpl2->copy_template, "[xfvalue_{$value[0]} format=" ) !== false) {
						
						$tpl2->copy_template = preg_replace_callback ( "#\\[xfvalue_{$preg_safe_name} format=['\"](.*?)['\"]\\]#i", 
							function ($matches) use ($value, $xfieldsdata, $customlangdate) {
								
								$matches[1] = trim($matches[1]);
								
								if ($value[25]) {

									if ($value[26]) return langdate($matches[1], $xfieldsdata[$value[0]]);
									else return langdate($matches[1], $xfieldsdata[$value[0]], false, $customlangdate);

								} else return date($matches[1], $xfieldsdata[$value[0]]);

								
							}, $tpl2->copy_template );
							
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
						
						$tpl2->set( "[xfvalue_thumb_url_{$value[0]}]", $path_parts->thumb);
						$xfieldsdata[$value[0]] = "<a href=\"{$path_parts->url}\" data-highslide=\"single\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a>";

					} else {
						
						$tpl2->set( "[xfvalue_thumb_url_{$value[0]}]", $path_parts->url);
						$xfieldsdata[$value[0]] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->url}\" alt=\"{$temp_alt}\">";

					}
					
					$tpl2->set( "[xfvalue_image_url_{$value[0]}]", $path_parts->url);
					$tpl2->set( "[xfvalue_image_description_{$value[0]}]", $temp_alt);

				}
				
				if($value[3] == "image" AND !$xfieldsdata[$value[0]]) {

					$tpl2->set( "[xfvalue_thumb_url_{$value[0]}]", "");
					$tpl2->set( "[xfvalue_image_url_{$value[0]}]", "");
					$tpl2->set( "[xfvalue_image_description_{$value[0]}]", "");
					
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

						$tpl2->copy_template = str_ireplace('[xfgiven_' . $value[0] . ' ' . $xftag . '="' . $xf_playlist_count . '"]', "", $tpl2->copy_template);
						$tpl2->copy_template = str_ireplace('[/xfgiven_' . $value[0] . ' ' . $xftag . '="' . $xf_playlist_count . '"]', "", $tpl2->copy_template);
						$tpl2->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name} {$xftag}=\"{$xf_playlist_count}\"\\](.*?)\\[/xfnotgiven_{$preg_safe_name} {$xftag}=\"{$xf_playlist_count}\"\\]'is", "", $tpl2->copy_template);
					}

					if (count($playlist_single)) {

						foreach ($playlist_single as $temp_key => $temp_value) {
							$tpl2->set($temp_key, $temp_value);
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
							
							$gallery_image[] = "<li><a href=\"{$path_parts->url}\" data-highslide=\"xf_". NEWS_ID ."_{$value[0]} \" target=\"_blank\"><img src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a></li>";
							$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<a href=\"{$path_parts->url}\" data-highslide=\"single\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a>";
							
						} else {
							
							$gallery_image[] = "<li><img src=\"{$path_parts->url}\" alt=\"{$temp_alt}\"></li>";
							$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->url}\" alt=\"{$temp_alt}\">";
							
						}
						
							$gallery_single_image['[xfvalue_'.$value[0].' image-description="'.$xf_image_count.'"]'] = $temp_alt;
							$gallery_single_image['[xfvalue_'.$value[0].' image-thumb-url="'.$xf_image_count.'"]'] = $path_parts->thumb;
							$gallery_single_image['[xfvalue_'.$value[0].' image-url="'.$xf_image_count.'"]'] = $path_parts->url;
							
							$tpl2->copy_template = str_ireplace( '[xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl2->copy_template );
							$tpl2->copy_template = str_ireplace( '[/xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl2->copy_template );
							$tpl2->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\](.*?)\\[/xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\]'is", "", $tpl2->copy_template );

					}
					
					if(count($gallery_single_image) ) {
						foreach($gallery_single_image as $temp_key => $temp_value) $tpl2->set( $temp_key, $temp_value);
					}
					
					$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">".implode($gallery_image)."</ul>";
					
				}
				
				$tpl2->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\](.*?)\\[/xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'is", "", $tpl2->copy_template );
				$tpl2->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl2->copy_template );
				$tpl2->copy_template = preg_replace( "'\\[/xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl2->copy_template );
	
				if ($value[30] AND $view_template != "print" ) $xfieldsdata[$value[0]] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $xfieldsdata[$value[0]] );

				$tpl2->set( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]] );

				if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl2->copy_template, $matches ) ) {
					$tpl2->set( $matches[0], clear_content($xfieldsdata[$value[0]], $matches[1]) );
				}

			}
		}

		$tpl2->compile( 'content' );
	
	}

	$related_buffer = $tpl2->result['content'];
	unset($tpl2);
	$db->free();

	if ( $allow_full_cache ) create_cache( "related", $related_buffer, NEWS_ID.$config['skin'], true );
}