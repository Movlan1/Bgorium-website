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
 File: rebuild.php
-----------------------------------------------------
 Use: News rebuild
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if(($member_id['user_group'] != 1)) {die ("error");}

if (!isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash) {

	  die ("error");

}

$_POST['area'] = isset($_POST['area']) ? $_POST['area'] : '';

if ($_POST['area'] == "related" ) {
	$db->query( "UPDATE " . PREFIX . "_post_extras SET related_ids=''" );
	clear_cache(array("full_", 'related_'));
    echo "{\"status\": \"ok\"}";
	die();
}

$startfrom = isset($_POST['startfrom']) ? intval($_POST['startfrom']) : 0;
$buffer = "";
$step = 0;
$count_per_step = 100;

if ($_POST['area'] == "comments" ) {
	$count_per_step = 500;
}

if( isset($_POST['count_per_step']) AND intval($_POST['count_per_step']) > 0 ) {
	$count_per_step = intval($_POST['count_per_step']);
}

if ($_POST['area'] == "static" ) {

	$parse = new ParseFilter();
	$parse->edit_mode = false;
	$parse->allow_code = false;

	$result = $db->query("SELECT id, template, allow_br FROM " . PREFIX . "_static WHERE allow_br !='2' LIMIT ".$startfrom.", ".$count_per_step);

	while($row = $db->get_row($result))
	{
			
		$row['template'] = $parse->decodeBBCodes( $row['template'], true, true );

		$template = $parse->process( $row['template'] );

		$template = $db->safesql($parse->BB_Parse( $template ));

		$db->query( "UPDATE " . PREFIX . "_static SET template='$template' WHERE id='{$row['id']}'" );

		$step++;
	}

	$rebuildcount = $startfrom + $step;
	$buffer = "{\"status\": \"ok\",\"rebuildcount\": {$rebuildcount}}";
	echo $buffer;
	
} elseif ($_POST['area'] == "comments" ) {
	
	if( $config['allow_comments_wysiwyg'] ) {

		$allowed_tags = array('div[align|style|class|data-commenttime|data-commentuser|data-commentid|data-commentgast|data-commentgast|contenteditable]', 'span[style|class|data-userurl|data-username|contenteditable]', 'p[align|style|class]', 'pre[class]', 'code', 'br', 'strong', 'em', 'ul', 'li', 'ol', 'b', 'u', 'i', 's', 'hr');
		
		if( $user_group[$member_id['user_group']]['allow_url'] ) $allowed_tags[] = 'a[href|target|style|class|title]';
		if( $user_group[$member_id['user_group']]['allow_image'] ) $allowed_tags[] = 'img[style|class|src|alt|width|height]';
		
		$parse = new ParseFilter( $allowed_tags );
		$parse->wysiwyg = true;
		$parse->allow_code = false;
		$use_html = true;
	
	} else {
		
		$parse = new ParseFilter();
		$use_html = false;
		$parse->allowbbcodes = false;
		
	}
	
	$parse->safe_mode = true;
	$parse->remove_html = false;
	$parse->edit_mode = false;
	$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
	$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

	$result = $db->query("SELECT id, text FROM " . PREFIX . "_comments LIMIT ".$startfrom.", ".$count_per_step);
	
	while($row = $db->get_row($result)) {
		
		if( !$config['allow_comments_wysiwyg'] ) {
			
			$row['text'] = $parse->decodeBBCodes( $row['text'], false );
			
		} else {
			$row['text'] = $parse->decodeBBCodes( $row['text'], true, true );
		}

		$row['text'] = $db->safesql( $parse->BB_Parse($parse->process( $row['text'] ), $use_html) );
		
		$db->query( "UPDATE " . PREFIX . "_comments SET text='{$row['text']}' WHERE id='{$row['id']}'" );
		
		$step++;
	}
	
	clear_cache();
	$rebuildcount = $startfrom + $step;
	$buffer = "{\"status\": \"ok\",\"rebuildcount\": {$rebuildcount}}";
	echo $buffer;
	
} else {


	$parse = new ParseFilter();
	$parse->edit_mode = false;
	$parse->allow_code = false;
	
	$parsexf = new ParseFilter();
	$parsexf->edit_mode = false;
	$parsexf->allow_code = false;
	
	$result = $db->query("SELECT p.id, p.short_story, p.full_story, p.xfields, p.title, p.category, p.approve, p.allow_br, p.tags, e.news_id FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) LIMIT ".$startfrom.", ".$count_per_step);
	
	while($row = $db->get_row($result))
	{
	
		$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, true );
		$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, true );
	
		$short_story = $parse->process( $row['short_story'] );
		$full_story = $parse->process( $row['full_story'] );
		$_POST['title'] = $row['title'];
			
		$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
		
		$xf_search_words = array ();
		$tags_cloud = array();
		
		if( $row['tags'] ) {

			$row['tags'] = html_entity_decode($row['tags'], ENT_QUOTES | ENT_XML1, 'UTF-8');

			if (@preg_match("/[\||\<|\>]/", $row['tags'])) $row['tags'] = "";
			else $row['tags'] = htmlspecialchars(strip_tags(stripslashes(trim($row['tags']))), ENT_COMPAT, 'UTF-8');

			if ($row['tags'] ) {

				$temp_array = explode(',', $row['tags'] );

				if (count($temp_array)) {

					foreach ($temp_array as $value) {
						if ( trim($value) ) $tags_cloud[] = $db->safesql( trim($value) );
					}
				}

				if ( count($tags_cloud) ) {
					
					$tags_cloud = array_unique($tags_cloud);
					$row['tags'] = implode(", ", $tags_cloud);

				} else $row['tags'] = "";

			}

		}

		if ($row['xfields']) {
	
			$xfields = xfieldsload();
			$postedxfields = xfieldsdataload($row['xfields']);
			$filecontents = array ();
			$newpostedxfields = array ();
	
			if( !empty( $postedxfields ) ) {
	
				foreach ($xfields as $name => $value) {

					if ($value[3] == "datetime" AND $postedxfields[$value[0]]) {

						$postedxfields[$value[0]] = @strtotime($postedxfields[$value[0]]);

						if ($postedxfields[$value[0]] !== -1 and $postedxfields[$value[0]]) {

							if ($value[23] == 1) $postedxfields[$value[0]] = date("Y-m-d", $postedxfields[$value[0]]);
							elseif ($value[23] == 2) $postedxfields[$value[0]] = date("H:i", $postedxfields[$value[0]]);
							else $postedxfields[$value[0]] = date("Y-m-d H:i", $postedxfields[$value[0]]);

						} else $postedxfields[$value[0]] = "";

					}

					if( $value[3] == "yesorno" AND isset( $postedxfields[$value[0]] ) ) {
						
						$postedxfields[$value[0]] = intval($postedxfields[$value[0]]);
						
					}

					if ($value[3] != "select" AND $value[3] != "image" AND $value[3] != "imagegalery" AND $value[3] != "video" AND $value[3] != "audio" AND $value[3] != "file" AND $value[3] != "htmljs" AND $value[3] != "datetime" AND $value[8] == 0 AND $value[6] == 0 AND isset($postedxfields[$value[0]]) AND $postedxfields[$value[0]] ) {

						$postedxfields[$value[0]] = $parsexf->decodeBBCodes($postedxfields[$value[0]], true, true);					
						$newpostedxfields[$value[0]] = $parsexf->BB_Parse($parsexf->process($postedxfields[$value[0]]));
				
					} elseif ( isset($postedxfields[$value[0]]) ) {
						
						if($value[3] == "htmljs") {
							
							$newpostedxfields[$value[0]] = $postedxfields[$value[0]];
							
						} else {

							$postedxfields[$value[0]] = str_replace("&#44;", "&amp;#44;", $postedxfields[$value[0]]);
							$postedxfields[$value[0]] = str_replace("&#124;", "&amp;#124;", $postedxfields[$value[0]]);

							$postedxfields[$value[0]] = html_entity_decode($postedxfields[$value[0]], ENT_QUOTES, 'UTF-8');
							$newpostedxfields[$value[0]] = trim( htmlspecialchars(strip_tags( stripslashes($postedxfields[$value[0]]) ), ENT_QUOTES, 'UTF-8' ));

							if ($value[3] == "image" or $value[3] == "imagegalery" or $value[3] == "video" or $value[3] == "audio") {

								$f_arr = explode(',', $newpostedxfields[$value[0]]);

								foreach ($f_arr as $t_val) {

									$t_a = explode('|', $t_val);

									if (count($t_a) == 1 or count($t_a) == 5) {

										$t_v = implode('|', $t_a);
									} else {

										unset($t_a[0]);
										$t_v = implode('|', $t_a);
									}

									if (preg_match("/[?&;<]/", $t_v) or stripos($t_v, ".php") !== false) $newpostedxfields[$value[0]] = "";
								}
							}

							$newpostedxfields[$value[0]] = str_replace(array("{", "["), array("&#123;", "&#91;"), $newpostedxfields[$value[0]]);
							$newpostedxfields[$value[0]] = preg_replace(array('/data:/i', '/about:/i', '/vbscript:/i', '/javascript:/i'), array("d&#1072;ta&#58;", "&#1072;bout&#58;", "vbscript&#58;", "j&#1072;vascript&#58;"), $newpostedxfields[$value[0]]);

							if($value[3] == "file") {
								
								$newpostedxfields[$value[0]] = str_replace( array("&#91;"), array("["), $newpostedxfields[$value[0]] );
								
								if( !$value[27] ) {
									if (strpos ( $newpostedxfields[$value[0]], "[attachment=" ) === false) $newpostedxfields[$value[0]] = "";
								}
								
							}

						}
				
					}
					
					if ( $value[6] AND !empty( $newpostedxfields[$value[0]] ) ) {
						$temp_array = explode( ",", $newpostedxfields[$value[0]] );
						
						foreach ($temp_array as $value2) {
							$value2 = trim($value2);
							if($value2) $xf_search_words[] = array( $db->safesql($value[0]), $db->safesql($value2) );
						}
					
					}
				
				}
	
				if (count ($newpostedxfields) ) {
		
					foreach ( $newpostedxfields as $xfielddataname => $xfielddatavalue ) {
						if( $xfielddatavalue === "" ) {
							continue;
						}
		
						$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
						$filecontents[] = $db->safesql("{$xfielddataname}|{$xfielddatavalue}");
					}
					
					$filecontents = implode( "||", $filecontents );
		
				} else	$filecontents = '';
			
			} else	$filecontents = '';
	
		} else	$filecontents = '';
	
		$db->query( "UPDATE " . PREFIX . "_post SET short_story='{$short_story}', full_story='{$full_story}', xfields='{$filecontents}', tags='{$row['tags']}' WHERE id='{$row['id']}'" );

		if ( !$row['news_id'] ) $db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate) VALUES('{$row['id']}', '1')" );

		$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id = '{$row['id']}'" );

		if( $row['category'] AND $row['approve'] ) {

			$cat_ids = array ();

			$cat_ids_arr = explode( ",", $row['category'] );

			foreach ( $cat_ids_arr as $value ) {

				$cat_ids[] = "('" . $row['id'] . "', '" . intval( $value ) . "')";
				
			}

			$cat_ids = implode( ", ", $cat_ids );
			$db->query( "INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES " . $cat_ids );

		}
		
		$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$row['id']}'" );

		if ( count($xf_search_words) AND $row['approve'] ) {
			
			$temp_array = array();
			
			foreach ( $xf_search_words as $value ) {
				
				$temp_array[] = "('" . $row['id'] . "', '" . $value[0] . "', '" . $value[1] . "')";
			}
			
			$xf_search_words = implode( ", ", $temp_array );
			$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
		}

		$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['id']}'" );
		
		if (count($tags_cloud) AND $row['approve']) {

			$temp_array = array();

			foreach ($tags_cloud as $value) {

				$temp_array[] = "('" . $row['id'] . "', '" . $value . "')";
			}

			$tags_cloud = implode(", ", $temp_array);
			$db->query("INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags_cloud);
		}

		$step++;
	}
	
	clear_cache();
	$rebuildcount = $startfrom + $step;
	$buffer = "{\"status\": \"ok\",\"rebuildcount\": {$rebuildcount}}";
	echo $buffer;
}
?>