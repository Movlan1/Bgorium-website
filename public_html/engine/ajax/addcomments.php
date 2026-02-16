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
 File: addcomments.php
-----------------------------------------------------
 Use: AJAX for comments
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	die( "Hacking attempt!" );
}

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

$ajax_adds = true;

require_once (DLEPlugins::Check(ENGINE_DIR . '/modules/addcomments.php'));

if( !$CN_HALT ) {

	if ( !defined('BANNERS') ) {
		if ($config['allow_banner']) include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/banners.php'));
	}

	$comments = new DLE_Comments( $db, 1, 1 );
	$comments->intern_count = -1;
	if($parent) $comments->indent = $indent+1;
	
	$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, " . PREFIX . "_comments.rating, " . PREFIX . "_comments.vote_num, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, banned, signature, foto, fullname, land, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.id = '{$added_comments_id}'";
	$comments->build_comments('comments.tpl', 'ajax' );

}

if( $_POST['editor_mode'] == "wysiwyg" ) {

	$clear_value = "tinyMCE.activeEditor.setContent('');";

} else {
	
	$clear_value = "form.comments.value = '';";

}

$clear_value .= <<<HTML
	
if( $('#comments-image-uploader').data('files') == 'uploaded' ) {
	$('#comments-image-uploader').plupload('clearQueue');
	$('.plupload_container').removeClass('plupload_files_selected');
}

HTML;

if( $user_group[$member_id['user_group']]['comments_question'] ) {
	$qs = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
	$qs['question'] = htmlspecialchars( stripslashes( $qs['question'] ), ENT_QUOTES, 'UTF-8' );
	$_SESSION['question'] = $qs['id'];
} else $qs = array('question' => '');

if( $CN_HALT ) {
	
	$stop = implode( '<br><br>', $stop );

	if($parent) {

		$replyclear="";
		
		if($user_group[$member_id['user_group']]['comments_question']) {
			
				$replyclear .= <<<HTML
	
		jQuery('#dle-question{$parent}').text('{$qs['question']}');
		jQuery('#question_answer{$parent}').val('');

HTML;
	
		}
	
		if( $user_group[$member_id['user_group']]['captcha'] AND $config['allow_recaptcha'] ) {

				$replyclear .= <<<HTML
			if ( dle_captcha_type == "1" ) {
				if ( typeof grecaptcha != "undefined"  ) {
				grecaptcha.reset(recaptcha_widget);
				}
			} else if (dle_captcha_type == "2") {;
				$('#comments-recaptcha-response{$parent}').val('');
			} else if (dle_captcha_type == "3") {
				if ( typeof hcaptcha != "undefined"  ) {
				hcaptcha.reset(recaptcha_widget);
				}
			} else if (dle_captcha_type == "4") {
				if ( typeof turnstile != "undefined"  ) {
				turnstile.reset(recaptcha_widget);
				}
			}
HTML;
			
		}

		if( $user_group[$member_id['user_group']]['captcha'] AND !$config['allow_recaptcha'] ) {

				$replyclear .= <<<HTML
	
			reload{$parent} ();
		
HTML;
			
		}
		
	} else  {

		$replyclear = <<<HTML
	
		if ( dle_captcha_type == "1" ) {
			if ( typeof grecaptcha != "undefined"  ) {
			grecaptcha.reset();
			}
		} else if (dle_captcha_type == "3") {
			if ( typeof hcaptcha != "undefined"  ) {
			hcaptcha.reset();
			}
		} else if (dle_captcha_type == "4") {
			if ( typeof turnstile != "undefined"  ) {
			turnstile.reset();
			}
		}

		if ( form.question_answer ) {

		form.question_answer.value ='';
		jQuery('#dle-question').text('{$qs['question']}');
		}

		if ( document.getElementById('dle-captcha') ) {
			form.sec_code.value = '';
			document.getElementById('dle-captcha').innerHTML = '<img src="' + dle_root + 'engine/modules/antibot/antibot.php?rand=' + timeval + '" width="160" height="80" alt="">';
		}
		
HTML;
		
	} 
	
	$tpl->result['content'] = "<script>\nvar form = document.getElementById('dle-comments-form');\n";
	
	if( !$where_approve ) {
		$tpl->result['content'] .= "\n{$clear_value}\n";
		
		if($parent) $tpl->result['content'] .= "\n jQuery('#dlereplypopup').remove(); jQuery('#dlefastreplycomments').remove(); \n";

		$tpl->result['content'] .= "\n DLEPush.warning('" . $stop . "');\n";

	} else $tpl->result['content'] .= "\n DLEPush.error('" . $stop . "');\n";
	
	$tpl->result['content'] .= "var timeval = new Date().getTime();\n

	{$replyclear}\n </script>";

} else {
	
	$js_script = <<<HTML
	var timeval = new Date().getTime();
	
	if( document.getElementById('dle-comments-form') ) {
	
		var form = document.getElementById('dle-comments-form');
		
		if ( form.question_answer ) {
	
		   form.question_answer.value ='';
		   jQuery('#dle-question').text('{$qs['question']}');
	
		}
		
		{$clear_value}
	}
HTML;

	if( strpos ( $tpl->result['content'], "dleplyrplayer" ) !== false ) {
		
		if( strpos ( $tpl->result['content'], ".m3u8" ) !== false ) {
			$load_more = "\$.getCachedScript( dle_root + 'engine/classes/html5player/plyr.js?v={$config['cache_id']}');";
			$js_name = "hls.js"; 
		} else {
			$load_more = "";
			$js_name = "plyr.js"; 
		}
		
		$js_script .= <<<HTML
		
			if (typeof DLEPlayer == "undefined") {

				var headlink =  $("head").find("link[rel='stylesheet']");
				var linkElement = "<link href='" + dle_root +"engine/classes/html5player/plyr.css' rel='stylesheet' type='text/css'>";

				if (headlink.length){
					headlink.first().before(linkElement)
				}
				else {
					$("head").append(linkElement);
				}
				  
				$.getCachedScript( dle_root + 'engine/classes/html5player/{$js_name}?v={$config['cache_id']}').done(function() {
				  {$load_more} 
				});
				
			} else {
			
				var containers = document.querySelectorAll("#comm-id-{$added_comments_id} .dleplyrplayer");Array.from(containers).forEach(function (container) {new DLEPlayer(container);});
				
			}
HTML;

	}

	if( strpos ( $tpl->result['content'], 'highslide' ) !== false ) {
		
		$js_script .= <<<HTML
		
			if (typeof Fancybox == "undefined" ) {
				$.getCachedScript( dle_root + 'engine/classes/fancybox/fancybox.js?v={$config['cache_id']}');
			}
		
HTML;
		
	}

	$needwrap = isset($_POST['needwrap']) ? intval($_POST['needwrap']) : 0 ;
	
	if ( $config['tree_comments'] ) {
		
		if (!$parent) $class = ' blind-animation';
		else $class = '';
		
		if ($config['tree_comments_level'] AND $indent >= $config['tree_comments_level'] ) {
			
			$tpl->result['content'] = "<div id=\"comments-tree-item-{$added_comments_id}\" style=\"display:none\" class=\"comments-tree-item{$class}\" >".$tpl->result['content']."</div>";
			
		} else {
			
			$tpl->result['content'] = "<li id=\"comments-tree-item-{$added_comments_id}\" style=\"display:none\" class=\"comments-tree-item{$class}\" >".$tpl->result['content']."</li>";
			
			if($needwrap OR !$parent) {
				$tpl->result['content'] = "<ol class=\"comments-tree-list\">".$tpl->result['content']."</ol>";
			}
			
		}

	} else {

		$tpl->result['content'] = "<div id=\"blind-animation-{$added_comments_id}\" style=\"display:none\">".$tpl->result['content']."<div>";
	}
	
}

$tpl->result['content'] = str_replace( '{THEME}', $_ROOT_DLE_URL . 'templates/' . $config['skin'], $tpl->result['content'] );

if ($CN_HALT) {

	echo json_encode(array("error" => true, "content" => $tpl->result['content'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

} else {
	
	echo json_encode(array("success" => true, "id" => $added_comments_id, "content" => $tpl->result['content'], "scripts" => $js_script), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	
}

?>