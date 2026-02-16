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
 File: main.php
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$home_url = clean_url($config['http_home_url']);

if ($home_url AND clean_url( $_SERVER['HTTP_HOST'] ) != $home_url ) {

	$replace_url = array ();
	$replace_url[0] = $home_url;
	$replace_url[1] = clean_url ( $_SERVER['HTTP_HOST'] );

} else $replace_url = false;

$tpl->load_template ( 'main.tpl' );

$tpl->set ( '{calendar}', isset($tpl->result['calendar']) ? $tpl->result['calendar'] : '' );
$tpl->set ( '{archives}', isset($tpl->result['archive']) ? $tpl->result['archive'] : '' );
$tpl->set ( '{tags}', isset($tpl->result['tags_cloud']) ? $tpl->result['tags_cloud'] : '' );
$tpl->set ( '{vote}', isset($tpl->result['vote']) ? $tpl->result['vote'] : '' );
$tpl->set ( '{login}', isset($tpl->result['login_panel']) ? $tpl->result['login_panel'] : '' );
$tpl->set ( '{speedbar}', isset($tpl->result['speedbar']) ? $tpl->result['speedbar'] : '' );

if ( $dle_module == "showfull" AND $news_found ) {

	if( $config['related_news'] AND RELATED_IDS AND strpos( $tpl->copy_template, "related-news" ) !== false ) {
		
		if( !is_string($related_buffer) ) {
			include_once(DLEPlugins::Check(ENGINE_DIR . '/modules/show.related.php'));
		}
		
		if ($related_buffer) {
			$tpl->set('[related-news]', "");
			$tpl->set('[/related-news]', "");
		} else $tpl->set_block("'\\[related-news\\](.*?)\\[/related-news\\]'si", "");

		$tpl->set('{related-news}', $related_buffer);

	} else {

		$tpl->set('{related-news}', '');
		$tpl->set_block("'\\[related-news\\](.*?)\\[/related-news\\]'si", "");
	}

	if( strpos( $tpl->copy_template, "[xf" ) !== false OR strpos( $tpl->copy_template, "[ifxf" ) !== false ) {

		$xfieldsdata = xfieldsdataload( $xfieldsdata );

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
			
			$xfieldsdata[$value[0]] = isset( $xfieldsdata[$value[0]] ) ? stripslashes( $xfieldsdata[$value[0]] ) : '';
			
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
				$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
			} else {
				$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[xfgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[/xfgiven_{$value[0]}]", "", $tpl->copy_template );
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
			
			if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links') AND isset($replace_links['news']) AND isset($xfieldsdata[$value[0]]) ) $xfieldsdata[$value[0]] = replace_links ( $xfieldsdata[$value[0]], $replace_links['news'] );

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

			if (($value[3] == "video" or $value[3] == "audio") and $xfieldsdata[$value[0]] and stripos($tpl->copy_template, "_{$value[0]}") !== false) {

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
					foreach ($playlist_single as $temp_key => $temp_value) $tpl->set($temp_key, $temp_value);
				}

				$xfieldsdata[$value[0]] = "<div class=\"dleplyrplayer\" {$playlist_width} theme=\"{$video_config['theme']}\">" . implode($playlist) . "</div>";
			}

			if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] AND stripos ( $tpl->copy_template, "_{$value[0]}" ) !== false) {
				
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
						
						$gallery_image[] = "<li><a href=\"{$path_parts->url}\" data-highslide=\"xf_". NEWS_ID . "_{$value[0]}\" target=\"_blank\"><img src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a></li>";
						$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<a href=\"{$path_parts->url}\" data-highslide=\"single\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->thumb}\" alt=\"{$temp_alt}\"></a>";
						
					} else {
						$gallery_image[] = "<li><img src=\"{$path_parts->url}\" alt=\"{$temp_alt}\"></li>";
						$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$path_parts->url}\" alt=\"{$temp_alt}\">";
					}
				
				}
				
				if( !$path_parts->thumb ) $path_parts->thumb = $path_parts->url;
				
				$gallery_single_image['[xfvalue_'.$value[0].' image-description="'.$xf_image_count.'"]'] = $temp_alt;
				$gallery_single_image['[xfvalue_'.$value[0].' image-thumb-url="'.$xf_image_count.'"]'] = $path_parts->thumb;
				$gallery_single_image['[xfvalue_'.$value[0].' image-url="'.$xf_image_count.'"]'] = $path_parts->url;
				
				$tpl->copy_template = str_ireplace( '[xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl->copy_template );
				$tpl->copy_template = str_ireplace( '[/xfgiven_'.$value[0].' image="'.$xf_image_count.'"]', "", $tpl->copy_template );
				$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\](.*?)\\[/xfnotgiven_{$preg_safe_name} image=\"{$xf_image_count}\"\\]'is", "", $tpl->copy_template );
		
				if(count($gallery_single_image) ) {
					foreach($gallery_single_image as $temp_key => $temp_value) $tpl->set( $temp_key, $temp_value);
				}
				
				$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">".implode($gallery_image)."</ul>";
				
			}
			
			$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\](.*?)\\[/xfgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[/xfnotgiven_{$preg_safe_name} (image|video|audio)=\"(\d+)\"\\]'i", "", $tpl->copy_template );
			
			if ($value[30]) $xfieldsdata[$value[0]] = preg_replace_callback ( "#<(img|iframe)(.+?)>#i", "enable_lazyload", $xfieldsdata[$value[0]] );
			
			$tpl->copy_template = str_ireplace( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $tpl->copy_template);

			if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches ) ) {
				$tpl->copy_template = str_ireplace( $matches[0], clear_content($xfieldsdata[$value[0]], $matches[1]), $tpl->copy_template );
			}
			
			if (stripos ( $tpl->copy_template, "[hide" ) !== false ) {
				
				$tpl->copy_template = preg_replace_callback ( "#\[hide(.*?)\](.+?)\[/hide\]#is", 
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
		
				}, $tpl->copy_template );
			}


			if( $config['files_allow'] ) if( strpos( $tpl->copy_template, "[attachment=" ) !== false ) {
				$tpl->copy_template = show_attach( $tpl->copy_template, NEWS_ID );
			}
		}
	}
		
} else {
	
	if( strpos( $tpl->copy_template, "related-news" ) !== false ) {
		$tpl->set( '{related-news}', "" );
		$tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );
	}
	
	if( strpos( $tpl->copy_template, "[xf" ) !== false ) {
		$tpl->copy_template = preg_replace( "'\\[xfnotgiven_(.*?)\\](.*?)\\[/xfnotgiven_(.*?)\\]'is", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "'\\[xfgiven_(.*?)\\](.*?)\\[/xfgiven_(.*?)\\]'is", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "'\\[xfvalue_(.*?)\\]'i", "", $tpl->copy_template );
	}
	
	if( strpos( $tpl->copy_template, "[ifxfvalue" ) !== false ) {
		$tpl->copy_template = preg_replace( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "", $tpl->copy_template );
	}

}

if ($config['allow_skin_change']) {
	$tpl->set('{changeskin}', ChangeSkin($config['skin']));
	$tpl->set('[changeskin]', '');
	$tpl->set('[/changeskin]', '');
} else {
	$tpl->set('{changeskin}', '');
	$tpl->set_block("'\\[changeskin\\](.*?)\\[/changeskin\\]'si", "");
}

if (count ( $banners ) and $config['allow_banner']) {

	foreach ( $banners as $name => $value ) {
		$tpl->copy_template = str_replace ( "{banner_" . $name . "}", $value, $tpl->copy_template );
		if ( $value ) {
			$tpl->copy_template = str_replace ( "[banner_" . $name . "]", "", $tpl->copy_template );
			$tpl->copy_template = str_replace ( "[/banner_" . $name . "]", "", $tpl->copy_template );
		}
	}

}

$tpl->set_block ( "'{banner_(.*?)}'si", "" );
$tpl->set_block ( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "" );

if ($config['rss_informer'] AND count ($informers) ) {
	foreach ( $informers as $name => $value ) {
		$tpl->copy_template = str_replace ( "{inform_" . $name . "}", $value, $tpl->copy_template );
	}
}

if (stripos ( $tpl->copy_template, "[category=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(category)=(.+?)\\](.*?)\\[/category\\]#is", "check_category", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[not-category=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-category)=(.+?)\\](.*?)\\[/not-category\\]#is", "check_category", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[static=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(static)=(.+?)\\](.*?)\\[/static\\]#is", "check_static", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[not-static=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-static)=(.+?)\\](.*?)\\[/not-static\\]#is", "check_static", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "{customcomments" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\{customcomments(.+?)\\}#i", "custom_comments", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "{custom" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\{custom(.+?)\\}#i", "custom_print", $tpl->copy_template );
}

if ( ($allow_active_news AND $news_found AND $config['allow_change_sort'] AND $dle_module != "userinfo") OR defined('CUSTOMSORT')) {

	$tpl->set ( '[sort]', "" );
	$tpl->set ( '{sort}', news_sort ( $do ) );
	$tpl->set ( '[/sort]', "" );

} else {

	$tpl->set_block ( "'\\[sort\\](.*?)\\[/sort\\]'si", "" );

}

$tpl->copy_template = str_replace ( "{topnews}", isset($tpl->result['topnews']) ? $tpl->result['topnews'] : '', $tpl->copy_template );

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

$config['http_home_url'] = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
$config['http_home_url'] = reset ( $config['http_home_url'] );

if ( !$user_group[$member_id['user_group']]['allow_admin'] ) $config['admin_path'] = "";
if ($config['thumb_gallery'] and ($dle_module == "showfull" or $dle_module == "static")) $config['thumb_gallery'] = 1; else $config['thumb_gallery'] = 0;

$ajax .= <<<HTML
{$twofactor_alert}<script>
<!--
var dle_root       = '{$config['http_home_url']}';
var dle_admin      = '{$config['admin_path']}';
var dle_login_hash = '{$dle_login_hash}';
var dle_group      = {$member_id['user_group']};
var dle_link_type  = {$config['allow_alt_url']};
var dle_skin       = '{$config['skin']}';
var dle_wysiwyg    = {$config['allow_comments_wysiwyg']};
var dle_min_search = '{$config['search_length_min']}';
var dle_act_lang   = ["{$lang['p_yes']}", "{$lang['p_no']}", "{$lang['p_enter']}", "{$lang['p_cancel']}", "{$lang['p_save']}", "{$lang['p_del']}", "{$lang['ajax_info']}"];
var menu_short     = '{$lang['menu_short']}';
var menu_full      = '{$lang['menu_full']}';
var menu_profile   = '{$lang['menu_profile']}';
var menu_send      = '{$lang['menu_send']}';
var menu_uedit     = '{$lang['menu_uedit']}';
var dle_info       = '{$lang['p_info']}';
var dle_confirm    = '{$lang['p_confirm']}';
var dle_prompt     = '{$lang['p_prompt']}';
var dle_req_field  = ["{$lang['req_field_1']}", "{$lang['req_field_2']}", "{$lang['req_field_3']}"];
var dle_del_agree  = '{$lang['news_delcom']}';
var dle_spam_agree = '{$lang['mark_spam']}';
var dle_c_title    = '{$lang['complaint_title']}';
var dle_complaint  = '{$lang['add_to_complaint']}';
var dle_mail       = '{$lang['reply_mail']}';
var dle_big_text   = '{$lang['big_text']}';
var dle_orfo_title = '{$lang['orfo_title']}';
var dle_p_send     = '{$lang['p_send']}';
var dle_p_send_ok  = '{$lang['p_send_ok']}';
var dle_save_ok    = '{$lang['n_save_ok']}';
var dle_reply_title= '{$lang['reply_comments']}';
var dle_tree_comm  = '{$dle_tree_comments}';
var dle_del_news   = '{$lang['news_delnews']}';
var dle_sub_agree  = '{$lang['subscribe_info_3']}';
var dle_unsub_agree  = '{$lang['subscribe_info_4']}';
var dle_captcha_type  = '{$config['allow_recaptcha']}';
var dle_share_interesting  = ["{$lang['share_i_1']}", "{$lang['share_i_2']}", "{$lang['share_i_3']}", "{$lang['share_i_4']}", "{$lang['share_i_5']}", "{$lang['share_i_6']}"];
var DLEPlayerLang     = {prev: '{$lang['player_prev']}',next: '{$lang['player_next']}',play: '{$lang['player_play']}',pause: '{$lang['player_pause']}',mute: '{$lang['player_mute']}', unmute: '{$lang['player_unmute']}', settings: '{$lang['player_settings']}', enterFullscreen: '{$lang['player_fullscreen']}', exitFullscreen: '{$lang['player_efullscreen']}', speed: '{$lang['player_speed']}', normal: '{$lang['player_normal']}', quality: '{$lang['player_quality']}', pip: '{$lang['player_pip']}'};
var DLEGalleryLang    = {CLOSE: '{$lang['thumb_closetitle']}', NEXT: '{$lang['thumb_nexttitle']}', PREV: '{$lang['thumb_previoustitle']}', ERROR: '{$lang['all_err_1']}', IMAGE_ERROR: '{$lang['thumb_imageerror']}', TOGGLE_SLIDESHOW: '{$lang['thumb_playtitle']}',TOGGLE_FULLSCREEN: '{$lang['thumb_fullscreen']}', TOGGLE_THUMBS: '{$lang['thumb_thtoggle']}', ITERATEZOOM: '{$lang['thumb_thzoom']}', DOWNLOAD: '{$lang['thumb_thdownload']}' };
var DLEGalleryMode    = {$config['thumb_gallery']};
var DLELazyMode       = {$config['image_lazy']};\n
HTML;

if ($user_group[$member_id['user_group']]['allow_all_edit']) {

	$ajax .= <<<HTML
var dle_notice     = '{$lang['btn_notice']}';
var dle_p_text     = '{$lang['p_text']}';
var dle_del_msg    = '{$lang['p_message']}';
var allow_dle_delete_news   = true;\n
HTML;

} else {

	$ajax .= <<<HTML
var allow_dle_delete_news   = false;\n
HTML;

}

if ($config['fast_search'] AND $user_group[$member_id['user_group']]['allow_search']) {

	$ajax .= <<<HTML
var dle_search_delay   = false;
var dle_search_value   = '';
HTML;

	$onload_scripts[] = "FastSearch();";

}

if ( defined('DLE_PHP_MIN') AND !DLE_PHP_MIN ) {
	$lang['stat_phperror'] = str_replace('{minversion}', DLE_PHP_MIN_VERSION, $lang['stat_phperror']);
	$lang['stat_phperror'] = str_replace('{version}', PHP_VERSION, $lang['stat_phperror']);

	$onload_scripts[] = "DLEPush.error('{$lang['stat_phperror']}');";
}

if (strpos ( $tpl->result['content'], "<pre" ) !== false OR strpos ( $tpl->copy_template, "<pre" ) !== false) {

	$js_array[] = "engine/classes/highlight/highlight.code.js";

}

if ( (strpos ( $tpl->result['content'], "highslide" ) !== false OR strpos ( $tpl->copy_template, "highslide" ) !== false) AND $dle_module != "addnews") {

	$js_array[] = "engine/classes/fancybox/fancybox.js";

}

if ( strpos ( $tpl->result['content'], "data-src=" ) !== false OR strpos ( $tpl->copy_template, "data-src=" ) !== false ) {
	$js_array[] = "engine/classes/js/lazyload.js";
}

if ( strpos ( $tpl->result['content'], "share-content" ) !== false OR strpos ( $tpl->copy_template, "share-content" ) !== false ) {
	
	$js_array[] = "engine/classes/masha/masha.js";
	
}

if (strpos ( $tpl->result['content'], "dleplyrplayer" ) !== false OR strpos ( $tpl->copy_template, "dleplyrplayer" ) !== false) {
  if ( strpos ( $tpl->result['content'], ".m3u8" ) !== false OR strpos ( $tpl->copy_template, ".m3u8" ) !== false ) {
	 $js_array[] = "engine/classes/html5player/hls.js";
  }
  $css_array[] = "engine/classes/html5player/plyr.css";
  $js_array[] = "engine/classes/html5player/plyr.js";
  
} elseif (strpos ( $tpl->result['content'], "dleaudioplayer" ) !== false OR strpos ( $tpl->result['content'], "dlevideoplayer" ) !== false OR strpos ( $tpl->copy_template, "dlevideoplayer" ) !== false OR strpos ( $tpl->copy_template, "dleaudioplayer" ) !== false) {
	
  $css_array[] = "engine/classes/html5player/player.css";
  $js_array[] = "engine/classes/html5player/player.js";
  
}

if( $user_group[$member_id['user_group']]['allow_pm'] ) {
	$allow_comments_ajax = true;
}

if ($allow_comments_ajax AND $dle_module != "addnews") {

    $js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";

}


$js_array = build_css($css_array, $config)."\n".build_js($js_array, $config);

$schema = DLESEO::CompileSchema();

if($schema) {
	$js_array .= "\n<script type=\"application/ld+json\">".DLESEO::CompileSchema()."</script>";	
}

$show_error_info = false;

if( $_SERVER['QUERY_STRING'] AND !$tpl->result['content'] AND !$tpl->result['info'] AND stripos ( $tpl->copy_template, "{content}" ) !== false ) {
	$show_error_info = true;
}

if($dle_module == "main" AND $config['start_site'] == 2 ) {
	$show_error_info = false;
}

if( $show_error_info ) {

	header( "HTTP/1.0 404 Not Found" );
	$need_404 = false;
	
	if( $config['own_404'] AND file_exists(ROOT_DIR . '/404.html') ) {
		header("Content-type: text/html; charset=utf-8");
		echo file_get_contents( ROOT_DIR . '/404.html' );
		die();
		
	} else msgbox( $lang['all_err_1'], $lang['news_err_27'] );

}

if($need_404) {
	@header( "HTTP/1.0 404 Not Found" );
}

if( is_array($tpl->onload_scripts) AND count($tpl->onload_scripts) ) {
	$onload_scripts = array_merge($onload_scripts, $tpl->onload_scripts);
}

if ( count($onload_scripts) ) {
	
	$onload_scripts =implode("\n", $onload_scripts);

	$ajax .= <<<HTML

jQuery(function($){
{$onload_scripts}
});
HTML;

} else $onload_scripts="";

$ajax .= <<<HTML

//-->
</script>
HTML;

if( ($tpl->result['content'] AND isset($tpl->result['navigation']) AND $tpl->result['navigation']) OR defined('CUSTOMNAVIGATION') ) {

	$tpl->set( '[navigation]', "" );
	$tpl->set( '[/navigation]', "" );
	$tpl->set_block( "'\\[not-navigation\\](.*?)\\[/not-navigation\\]'si", "" );
		
	if( stripos ( $tpl->copy_template, "{navigation}" ) !== false )	{

		$tpl->result['content'] = str_replace ( '{newsnavigation}', '', $tpl->result['content'] );
		$tpl->copy_template = str_replace ( '{newsnavigation}', '', $tpl->copy_template );
			
		if( $tpl->result['navigation'] AND stripos ( $tpl->copy_template, "{content}" ) !== false ) {
			
			$tpl->set( '{navigation}', $tpl->result['navigation'] );
			
		} else {
			
			$tpl->set( '{navigation}', $custom_navigation );
			
		}

	} else {
		
		$tpl->result['content'] = str_replace ( '{newsnavigation}', $tpl->result['navigation'], $tpl->result['content'] );
		$tpl->copy_template = str_replace ( '{newsnavigation}', $custom_navigation, $tpl->copy_template );

	}

} else {
	
	$tpl->set( '{navigation}', "" );
	$tpl->set( '[not-navigation]', "" );
	$tpl->set( '[/not-navigation]', "" );
	$tpl->set_block( "'\\[navigation\\](.*?)\\[/navigation\\]'si", "" );
	
}


if (stripos ( $tpl->copy_template, "{jsfiles}" ) !== false) {
	$tpl->set ( '{headers}', $metatags );
	$tpl->set ( '{jsfiles}', $js_array );
} else {
	$tpl->set ( '{headers}', $metatags."\n".$js_array );
}

$tpl->set ( '{AJAX}', $ajax );
$tpl->set ( '{info}',  $tpl->result['info'] );

$tpl->set ( '{content}', $tpl->result['content'] );

$tpl->compile ( 'main' );

if( $is_logged AND stripos ( $tpl->result['main'], "-favorites-" ) !== false) {
	
	$fav_arr = explode(',', $member_id['favorites'] );
	
	foreach( $fav_arr as $fav_id ) {
		$tpl->result['main'] = str_replace ( "{-favorites-{$fav_id}}", "<a data-fav-id=\"{$fav_id}\" class=\"favorite-link del-favorite\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=del&amp;id={$fav_id}\"><img src=\"{$config['http_home_url']}templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('{$fav_id}', 'minus', 0); return false;\" title=\"{$lang['news_minfav']}\" alt=\"\"></a>", $tpl->result['main'] );
		$tpl->result['main'] = str_replace ( "[del-favorites-{$fav_id}]", "<span data-favorites-del=\"{$fav_id}\" style=\"display:none\"></span><a onclick=\"doFavorites('{$fav_id}', 'minus', 1, 'short'); return false;\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=del&amp;id={$fav_id}\">", $tpl->result['main'] );
		$tpl->result['main'] = str_replace ( "[/del-favorites-{$fav_id}]", "</a>", $tpl->result['main'] );
		$tpl->result['main'] = preg_replace( "'\\[add-favorites-{$fav_id}\\](.*?)\\[/add-favorites-{$fav_id}\\]'is", "<span data-favorites-add=\"{$fav_id}\" style=\"display:none\"></span>", $tpl->result['main'] );
	}
	
	$tpl->result['main'] = preg_replace( "'\\{-favorites-(\d+)\\}'i", "<a data-fav-id=\"\\1\" class=\"favorite-link add-favorite\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=add&amp;id=\\1\"><img src=\"{$config['http_home_url']}templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('\\1', 'plus', 0); return false;\" title=\"{$lang['news_addfav']}\" alt=\"\"></a>", $tpl->result['main'] );
	$tpl->result['main'] = preg_replace( "'\\[add-favorites-(\d+)\\]'i", "<span data-favorites-add=\"\\1\" style=\"display:none\"></span><a onclick=\"doFavorites('\\1', 'plus', 1, 'short'); return false;\" href=\"{$PHP_SELF}?do=favorites&amp;doaction=add&amp;id=\\1\">", $tpl->result['main'] );
	$tpl->result['main'] = preg_replace( "'\\[/add-favorites-(\d+)\\]'i", "</a>", $tpl->result['main'] );
	$tpl->result['main'] = preg_replace( "'\\[del-favorites-(\d+)\\](.*?)\\[/del-favorites-(\d+)\\]'si", "<span data-favorites-del=\"\\1\" style=\"display:none\"></span>", $tpl->result['main'] );

}

if( stripos ( $tpl->result['main'], '[custom' ) !== false OR stripos ( $tpl->result['main'], '[not-custom' ) !== false) {
	$tpl->result['main'] = preg_replace_callback("#\\[(custom|not-custom)=(.+?)\\](.*?)\[/\\1\]#is",
		function ($matches) use ($custom_blocks_names) {

			$matches[1] = trim($matches[1]);
			$matches[2] = trim($matches[2]);

			if ( $matches[1] == 'custom' ) {
				if( isset($custom_blocks_names[$matches[2]]) AND $custom_blocks_names[$matches[2]] ) return $matches[3];
				else return '';
			}

			if ($matches[1] == 'not-custom') {
				if (isset($custom_blocks_names[$matches[2]]) AND $custom_blocks_names[$matches[2]]) return '';
				else return $matches[3];
			}

			return $matches[0];

		}, $tpl->result['main']);
}

if ($config['allow_links'] and isset($replace_links['all']) ) $tpl->result['main'] = replace_links ( $tpl->result['main'], $replace_links['all'] );

$tpl->result['main'] = str_ireplace( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['main'] );

if ( is_array($replace_url) ) $tpl->result['main'] = str_replace ( $replace_url[0]."/", $replace_url[1]."/", $tpl->result['main'] );

if($remove_canonical) {
	$tpl->result['main'] = preg_replace( "#<link rel=['\"]canonical['\"](.+?)>#i", "", $tpl->result['main'] );
}

$tpl->result['main'] = str_replace ( 'src="http://'.$_SERVER['HTTP_HOST'].'/', 'src="/', $tpl->result['main'] );
$tpl->result['main'] = str_replace ( 'srcset="http://'.$_SERVER['HTTP_HOST'].'/', 'srcset="/', $tpl->result['main'] );
$tpl->result['main'] = str_replace ( 'src="https://'.$_SERVER['HTTP_HOST'].'/', 'src="/', $tpl->result['main'] );
$tpl->result['main'] = str_replace ( 'srcset="https://'.$_SERVER['HTTP_HOST'].'/', 'srcset="/', $tpl->result['main'] );

echo $tpl->result['main'];

$tpl->global_clear();

$db->close();

echo "\n<!-- DataLife Engine Copyright SoftNews Media Group (https://dle-news.ru) -->\r\n";

GzipOut();

?>