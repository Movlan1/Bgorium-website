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
 File: replycomments.php
-----------------------------------------------------
 Use: comments reply
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	echo $lang['sess_error'];
	die();
}

if( !$user_group[$member_id['user_group']]['allow_addc'] OR !$config['allow_comments'] OR !$config['tree_comments']) {
	echo $lang['reply_error_1'];
	die();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0 ;
$indent = isset($_GET['indent']) ? intval($_GET['indent']) : 0 ;
$needwrap = isset($_GET['needwrap']) ? intval($_GET['needwrap']) : 0 ;
$comments_mobile_editor = false;

if( $id < 1 ) {
	echo $lang['reply_error_2'];
	die();
}

$row = $db->super_query("SELECT id, post_id, autor FROM " . PREFIX . "_comments WHERE id = '{$id}'");

if (!$row['id']) {
	echo $lang['reply_error_2'];
	die();
}

$dark_theme = "";

if (defined('TEMPLATE_DIR')) {
	$template_dir = TEMPLATE_DIR;
} else $template_dir = ROOT_DIR . "/templates/" . $config['skin'];

if (is_file($template_dir . "/info.json")) {

	$data = json_decode(trim(file_get_contents($template_dir . "/info.json")), true);

	if (isset($data['type']) and $data['type'] == "dark") {
		$dark_theme = " dle_theme_dark";
	}
}

$tpl = new dle_template();
if ($tpl->smartphone OR $tpl->tablet) $comments_mobile_editor = true;

if ( $is_logged AND $user_group[$member_id['user_group']]['disable_comments_captcha'] AND $member_id['comm_num'] >= $user_group[$member_id['user_group']]['disable_comments_captcha'] ) {
		
		$user_group[$member_id['user_group']]['comments_question'] = false;
		$user_group[$member_id['user_group']]['captcha'] = false;
		
}
if ($user_group[$member_id['user_group']]['allow_image'] and  $user_group[$member_id['user_group']]['allow_up_image'] and strpos(file_get_contents(ROOT_DIR . '/templates/' . $config['skin'] . '/addcomments.tpl'), "{image-upload}") !== false) {
	$comments_image_uploader_loaded = true;
} else $comments_image_uploader_loaded = false;

echo $lang['reply_descr']." <b>".$row['autor']."</b><br>";

echo "<form  method=\"post\" name=\"dle-comments-form-{$id}\" id=\"dle-comments-form-{$id}\">";

if( $is_logged ) echo "<input type=\"hidden\" name=\"name{$id}\" id=\"name{$id}\" value=\"{$member_id['name']}\" /><input type=\"hidden\" name=\"mail{$id}\" id=\"mail{$id}\" value=\"\" />";
else {
	
	$cookie_guest_name = isset($_COOKIE['dle_guest_name']) ? htmlspecialchars(strip_tags(stripslashes($_COOKIE['dle_guest_name'])), ENT_QUOTES, 'UTF-8') : '';
	$cookie_guest_mail = isset($_COOKIE['dle_guest_mail']) ? htmlspecialchars(strip_tags(stripslashes($_COOKIE['dle_guest_mail'])), ENT_QUOTES, 'UTF-8') : '';

	echo <<<HTML
<div class="commentsreplyname" style="float:left;width:50%;padding-right: 10px;box-sizing: border-box;"><input type="text" name="name{$id}" id="name{$id}" style="width:100%;" placeholder="{$lang['reply_name']}" value="{$cookie_guest_name}" required></div>
<div class="commentsreplymail" style="float:left;width:50%;padding-left: 10px;box-sizing: border-box;"><input type="text" name="mail{$id}" id="mail{$id}" style="width:100%;" placeholder="{$lang['reply_mail']}" value="{$cookie_guest_mail}"></div>
<div style="clear:both;padding-bottom:5px;"></div>
HTML;

}

	$p_name = isset($member_id['name']) ? urlencode($member_id['name']) : '';
	$p_id = 0;

	if( !$config['allow_comments_wysiwyg'] ) {
		
		$params = "";
		$box_class = "bb-editor";
		$bb_code = "";

	} else {
		
		$params = "class=\"ajaxwysiwygeditor\"";
		$box_class = "wseditor dlecomments-editor";

		if ($user_group[$member_id['user_group']]['allow_url']) $link_icon = "link unlink dleleech "; else $link_icon = "";
		
		$mobile_link_icon = $link_icon;
		
		if ($user_group[$member_id['user_group']]['allow_image']) {
			if($config['bbimages_in_wysiwyg']) $link_icon .= "| dleimage "; else $link_icon .= "| image ";
		}

		$image_upload = array();
		
		if ( $user_group[$member_id['user_group']]['allow_image'] AND  $user_group[$member_id['user_group']]['allow_up_image'] ) {

			if (!$comments_image_uploader_loaded) {
				$link_icon .= "dleupload ";
				$mobile_link_icon .= "dleupload ";
			}

			$image_upload[1] = <<<HTML
var dle_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
  var xhr, formData;

  xhr = new XMLHttpRequest();
  xhr.withCredentials = false;
  xhr.open('POST', dle_root + 'engine/ajax/controller.php?mod=upload');
  
  xhr.upload.onprogress = (e) => {
    progress(e.loaded / e.total * 100);
  };

  xhr.onload = function() {
    var json;

    if (xhr.status === 403) {
      reject('HTTP Error: ' + xhr.status, { remove: true });
      return;
    }

    if (xhr.status < 200 || xhr.status >= 300) {
      reject('HTTP Error: ' + xhr.status);
      return;
    }

    json = JSON.parse(xhr.responseText);

    if (!json || typeof json.link != 'string') {

		if(typeof json.error == 'string') {
			reject(json.error);
		} else {
			reject('Invalid JSON: ' + xhr.responseText);	
		}
		
		var editor = tinymce.activeEditor;
		var node = editor.selection.getEnd();
		editor.selection.select(node);
		editor.selection.setContent('');
		
      return;
    }

	if( json.flink ) {
		
		var editor = tinymce.activeEditor;
		var node = editor.selection.getEnd();

		var object = $(node);
		object.removeAttr('width');
		object.removeAttr('height');
		object.attr('src', json.link );
		object.attr('style', 'display: block; margin-left: auto; margin-right: auto;' );
		
		editor.selection.select(node);
		editor.selection.setContent('<a href="'+json.flink+'" class="highslide">'+object.prop('outerHTML')+'</a>&nbsp;');
		editor.notificationManager.close();
		$('#mediaupload').remove();

	} else {
		resolve(json.link);
		$('#mediaupload').remove();
	}
	
  };

  xhr.onerror = function () {
    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
  };

  formData = new FormData();
  formData.append('qqfile', blobInfo.blob(), blobInfo.filename());
  formData.append("subaction", "upload");
  formData.append("news_id", "{$p_id}");
  formData.append("area", "comments");
  formData.append("author", "{$p_name}");
  formData.append("mode", "quickload");
  formData.append("editor_mode", "tinymce");
  formData.append("user_hash", "{$dle_login_hash}");
  
  xhr.send(formData);
});
HTML;

		$image_upload[2] = <<<HTML
paste_data_images: true,
automatic_uploads: true,
images_upload_handler: dle_image_upload_handler,
images_reuse_filename: true,
image_uploadtab: false,
images_file_types: 'gif,jpg,png,jpeg,bmp,webp,avif',
file_picker_types: 'image',

file_picker_callback: function (cb, value, meta) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');

    input.addEventListener('change', (e) => {
      const file = e.target.files[0];

	  var filename = file.name;
	  filename = filename.split('.').slice(0, -1).join('.');
	
      const reader = new FileReader();
      reader.addEventListener('load', () => {

        const id = filename;
        const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        const base64 = reader.result.split(',')[1];
        const blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        cb(blobInfo.blobUri());

      });
      reader.readAsDataURL(file);
    });

    input.click();
},
HTML;
		
	} else {
		
		$image_upload[0] = "";
		$image_upload[1] = "";
		$image_upload[2] = "paste_data_images: false,\n";
		
	}

	if ($user_group[$member_id['user_group']]['video_comments'] AND !$comments_mobile_editor) $link_icon .= "dlemp dlaudio ";

	if ($user_group[$member_id['user_group']]['media_comments'] AND !$comments_mobile_editor) $link_icon .= "dletube ";

	if( @file_exists( ROOT_DIR . '/templates/'. $config['skin'].'/editor.css' ) ) {
		
		$editor_css = "templates/{$config['skin']}/editor.css?v={$config['cache_id']}";
			
	} else $editor_css = "engine/editor/css/content.css?v={$config['cache_id']}";
	
		$bb_code = <<<HTML

<script>
var text_upload = "{$lang['bb_t_up']}";

setTimeout(function() {

	tinymce.remove('textarea.ajaxwysiwygeditor');

	tinyMCE.baseURL = dle_root + 'engine/editor/jscripts/tiny_mce';
	tinyMCE.suffix = '.min';

	var dle_theme = '{$dark_theme}';

	if(dle_theme != '') {
		$('body').addClass( dle_theme );
	}

	{$image_upload[1]}
	
	tinymce.init({
		selector: 'textarea.ajaxwysiwygeditor',
		language : '{$lang['language_code']}',
		directionality: '{$lang['direction']}',
		body_class: dle_theme,
		skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',
		element_format : 'html',
		width : "100%",
		deprecation_warnings: false,
		promotion: false,
		cache_suffix: '?v={$config['cache_id']}',
		license_key: 'gpl',
		draggable_modal: true,
		toolbar_mode: 'floating',
		contextmenu: false,
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		browser_spellcheck: true,
		extended_valid_elements : "div[align|style|class|data-commenttime|data-commentuser|data-commentid|data-commentpostid|data-commentgast|contenteditable],span[id|data-username|data-userurl|align|style|class|contenteditable],b/strong,i/em,u,s,p[align|style|class|contenteditable],pre[class],code",
		quickbars_insert_toolbar: '',
		quickbars_selection_toolbar: 'bold italic underline | dlequote dlespoiler dlehide',
		
	    formats: {
	      bold: {inline: 'b'},
	      italic: {inline: 'i'},
	      underline: {inline: 'u', exact : true},
	      strikethrough: {inline: 's', exact : true}
	    },
		
		paste_postprocess: (editor, args) => {
			args = DLEPasteSafeText(args, {$user_group[$member_id['user_group']]['allow_url']});
		},
		paste_as_text: true,
		statusbar : false,
		branding: false,
		browser_spellcheck: true,
		text_patterns: [],
		menubar: false,
		link_default_target: '_blank',
		editable_class: 'contenteditable',
		noneditable_class: 'noncontenteditable',
		image_dimensions: true,
		{$image_upload[2]}
		
		dle_root: dle_root,
		dle_upload_area : "comments",
		dle_upload_user : "{$p_name}",
		dle_upload_news : "{$p_id}",
		content_css : dle_root + "{$editor_css}",
HTML;

		if ($comments_mobile_editor) {
			
			$box_class = "mobilewseditor dlecomments-editor";

			$bb_code .= <<<HTML
		min_height : 40,
		max_height : 250,
		autoresize_overflow_padding: 10,
		autoresize_bottom_margin: 1,
		plugins: "autoresize link image lists dlebutton codesample",
		
		placeholder: "{$lang['comm_placeholder']}",
		toolbar: "formatgroup paragraphgroup insertgroup",
		toolbar_location: "bottom",

		toolbar_groups: {
			formatgroup: {
			icon: "format",
			tooltip: "Formatting",
			items:
				"bold italic underline strikethrough | removeformat"
			},
			paragraphgroup: {
			icon: "paragraph",
			tooltip: "Paragraph format",
			items:
				"bullist numlist | alignleft aligncenter alignright"
			},
			insertgroup: {
				icon: "plus",
				tooltip: "Insert",
				items: "dleemo {$link_icon} | dlequote dlespoiler dlehide"
			}
		},

		mobile: {
			toolbar_mode: "floating"
		},

		setup: (editor) => {

			const onCompeteAction = (autocompleteApi, rng, value) => {
				editor.selection.setRng(rng);
				editor.insertContent(value);
				autocompleteApi.hide();
			};

			editor.ui.registry.addAutocompleter('getusers', {
			trigger: '@',
			minChars: 1,
			columns: 1,
			onAction: onCompeteAction,
			fetch: (pattern) => {

				return new Promise((resolve) => {

					$.get(dle_root + "engine/ajax/controller.php?mod=find_tags", { mode: 'users', term: pattern, skin: dle_skin, user_hash: dle_login_hash }, function(data){
						if ( data.found ) {
							resolve(data.items);
						}
					}, "json");

				});
			}
			});

			editor.on("focus", () => {
				$(".comments-edit-area .mobilewseditor").addClass("focused");
			});

			editor.on("blur", () => {
				$(".comments-edit-area .mobilewseditor").removeClass("focused");
			});

			editor.ui.registry.addContextToolbar("editimage", {
				predicate: (node) => {
					return node.nodeName.toLowerCase() === "img";
				},
				items: "editimage removeimage",
				position: "node",
				scope: "node"
			});

			editor.ui.registry.addButton("editimage", {
				icon: "edit-block",
				onAction: () => {
					editor.execCommand("mceImage");
				}
			});

			editor.ui.registry.addButton("removeimage", {
				icon: "remove",
				onAction: () => {
					const node = tinymce.activeEditor.selection.getNode();
					node.remove();
				}
			});

		}
HTML;

		} else {
			$bb_code .= <<<HTML
		height: 220,

		plugins: "link image lists quickbars dlebutton codemirror codesample",
		quickbars_insert_toolbar: '',
		quickbars_selection_toolbar: 'bold italic underline | dlequote dlespoiler dlehide',
		
		toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | dleemo {$link_icon} | dlequote codesample dlespoiler dlehide",
		
		mobile: {
			toolbar_mode: "sliding",
			toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | {$mobile_link_icon} dlequote dlespoiler dlehide",
			
		},
		
		codesample_languages: [
			{ text: 'HTML/XML', value: 'markup' },
			{ text: 'JavaScript', value: 'javascript' },
			{ text: 'CSS', value: 'css' },
			{ text: 'PHP', value: 'php' },
			{ text: 'SQL', value: 'sql' },
			{ text: 'Ruby', value: 'ruby' },
			{ text: 'Python', value: 'python' },
			{ text: 'Java', value: 'java' },
			{ text: 'C', value: 'c' },
			{ text: 'C#', value: 'csharp' },
			{ text: 'C++', value: 'cpp' }
		],

		setup: (editor) => {

			const onCompeteAction = (autocompleteApi, rng, value) => {
				editor.selection.setRng(rng);
				editor.insertContent(value);
				autocompleteApi.hide();
			};

			editor.ui.registry.addAutocompleter('getusers', {
			trigger: '@',
			minChars: 1,
			columns: 1,
			onAction: onCompeteAction,
			fetch: (pattern) => {

				return new Promise((resolve) => {

					$.get(dle_root + "engine/ajax/controller.php?mod=find_tags", { mode: 'users', term: pattern, skin: dle_skin, user_hash: dle_login_hash }, function(data){
						if ( data.found ) {
							resolve(data.items);
						}
					}, "json");

				});
			}
		});
	}
HTML;

		}

		$bb_code .= <<<HTML
	});

}, 100);

</script>
HTML;


	}

	if ($comments_mobile_editor) {
		echo <<<HTML
		<div class="comments-edit-area ignore-select">
			<div class="{$box_class}{$dark_theme}">
			{$bb_code}
			<textarea name="comments{$id}" id="comments{$id}" style="width:100%;height:40px;" {$params}></textarea>
			</div>
	</div>
HTML;

	} else {

		echo <<<HTML
		<div class="{$box_class}{$dark_theme}">
		{$bb_code}
		<textarea name="comments{$id}" id="comments{$id}" style="width:100%;height:245px;" {$params}></textarea>
		</div>
HTML;

	}

if ( $comments_image_uploader_loaded ) {

	$user_group[$member_id['user_group']]['up_count_image'] = intval($user_group[$member_id['user_group']]['up_count_image']);
	$max_file_size = intval($user_group[$member_id['user_group']]['up_image_size']) * 1024;
	$config['file_chunk_size'] =  number_format(floatval($config['file_chunk_size']), 1, '.', '');
	
	if ($config['file_chunk_size'] < 1) $config['file_chunk_size'] = '1.5';

	if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

	echo <<<HTML
<div class="comments-image-uploader-area">
	<a onclick="ShowOrHideUploader(); return false" href="#">{$lang['attach_images']}</a>
	<div id="hidden-comments-image-uploader-reply"" style="display: none"><div id="comments-image-uploader-reply" class="comments-image-uploader"></div></div>
</div>
<script>

function LoadDLEFont() {
    const elem = document.createElement('i');
    elem.className = 'mediaupload-icon';
	elem.style.position = 'absolute';
	elem.style.left = '-9999px';
	document.body.appendChild(elem);

	if ($( elem ).css('font-family') !== 'mediauploadicons') {
		$('head').append('<link rel="stylesheet" type="text/css" href="' + dle_root + 'engine/classes/uploads/html5/fileuploader{$rtl_prefix}.css">');
	}
  
    document.body.removeChild(elem);
};
function ShowOrHideUploader() {

	var item = $("#hidden-comments-image-uploader-reply");

	var scrolltime = (item.height() / 500) * 1000;

	if (scrolltime > 2000 ) { scrolltime = 2000; }

	if (scrolltime < 250 ) { scrolltime = 250; }

	if (item.css("display") == "none") { 

		item.show('blind',{}, scrolltime, function() {
   			$('#comments-image-uploader-reply').plupload('refresh');
  		});

	} else {

		item.hide('blind',{}, scrolltime, function() {
   			$('#comments-image-uploader-reply').plupload('refresh');
  		});


	}

};

function comments_media_uploader() {

	LoadDLEFont();

	$('#comments-image-uploader-reply').plupload({

		runtimes: 'html5',
		url: dle_root + "engine/ajax/controller.php?mod=upload",
		file_data_name: "qqfile",

		max_file_size: '{$max_file_size}',

		chunk_size: '{$config['file_chunk_size']}mb',

		filters: [
			{title : "Image files", extensions : "gif,jpg,png,jpeg,bmp,webp"}
		],
		
		rename: true,
		sortable: true,
		dragdrop: true,

		views: {
			list: false,
			thumbs: true,
			active: 'thumbs',
			remember: false
		},
		
		multipart_params: {"subaction" : "upload", "news_id" : 0, "area" : 'comments', "author" : "{$member_id['name']}", "user_hash" : "{$dle_login_hash}"},
		
		init: function(event, args) {
			$('#comments-image-uploader-reply .plupload_droptext').text('{$lang['media_upload_st_5']}');
		},
		selected: function(event, args) {
			var uploader = args.up;
			var commentsfiles_each_count = 0;
			var commentsfiles_count_errors = false;
			var comments_max_allow_files = {$user_group[$member_id['user_group']]['up_count_image']};

			plupload.each(uploader.files, function(file) {
				commentsfiles_each_count ++

				if(comments_max_allow_files && commentsfiles_each_count > comments_max_allow_files ) {
					commentsfiles_count_errors = true;

					setTimeout(function() {
						uploader.removeFile( file );
					}, 100);

				}

			});

			if(commentsfiles_count_errors) {
				$('#comments-image-uploader-reply').plupload('notify', 'error', "{$lang['error_max_queue']}");
			}

			$('#comments-image-uploader-reply').data('files', 'selected');
			$('.plupload_container').addClass('plupload_files_selected');

		},
		removed: function(event, args) {
			if(args.up.files.length) {
				$('.plupload_container').addClass('plupload_files_selected');
			} else {
				$('.plupload_container').removeClass('plupload_files_selected');
			}
		},
		started: function(event, args) {
			ShowLoading('');
		},
		
	});

}

if (typeof $.fn.plupload !== "function" ) {

	$.getCachedScript(dle_root + 'engine/classes/uploads/html5/plupload/plupload.full.min.js?v={$config['cache_id']}').done(function() {
		$.getCachedScript(dle_root +'engine/classes/uploads/html5/plupload/plupload.ui.min.js?v={$config['cache_id']}').done(function() {
			$.getCachedScript(dle_root + 'engine/classes/uploads/html5/plupload/i18n/{$lang['language_code']}.js?v={$config['cache_id']}').done(function() {
				comments_media_uploader();
			});
		});
	});
	
} else {
	comments_media_uploader();
}
</script>
HTML;

}

if ($config['allow_subscribe'] AND $user_group[$member_id['user_group']]['allow_subscribe']) {
echo <<<HTML
<div style="padding-top:5px;">
	<label class="form-check-label"><input class="form-check-input" type="checkbox" name="subscribe{$id}" id="subscribe{$id}" value="1"><span>{$lang['c_subscribe']}</span></label>
</div>
HTML;
}

if( $user_group[$member_id['user_group']]['comments_question'] ) {
	$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");

	$_SESSION['question'] = $question['id'];

	$question = htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, 'UTF-8' );
	
	echo <<<HTML
<div id="dle-question{$id}" style="padding-top:5px;">{$question}</div>
<div><input type="text" name="question_answer{$id}" id="question_answer{$id}" placeholder="{$lang['question_hint']}" class="quick-edit-text" required></div>
HTML;

}

if( $user_group[$member_id['user_group']]['captcha'] ) {

	if ( $config['allow_recaptcha'] ) {
		
		if( $config['allow_recaptcha'] == 2) {
			
			echo <<<HTML
	<input type="hidden" name="comments-recaptcha-response{$id}" id="comments-recaptcha-response{$id}" data-key="{$config['recaptcha_public_key']}" value="">
	<script>
	if ( typeof grecaptcha === "undefined"  ) {
	
		$.getScript( "https://www.google.com/recaptcha/api.js?render={$config['recaptcha_public_key']}");

    }
	</script>
HTML;
		} elseif($config['allow_recaptcha'] == 3 )  {

			echo <<<HTML
<div id="dle_recaptcha{$id}" style="padding-top:5px;height:78px;"></div><input type="hidden" name="recaptcha{$id}" id="recaptcha{$id}" value="1" />
<script>
<!--
	var recaptcha_widget;
	
	if ( typeof hcaptcha === "undefined"  ) {
	
		$.getScript( "https://js.hcaptcha.com/1/api.js?hl={$lang['language_code']}&render=explicit").done(function () {
		
			var setIntervalID = setInterval(function () {
				if (window.hcaptcha) {
					clearInterval(setIntervalID);
					recaptcha_widget = hcaptcha.render('dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
				};
			}, 300);
		});

    } else {
		recaptcha_widget = hcaptcha.render('dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
	}
//-->
</script>
HTML;
		} elseif ($config['allow_recaptcha'] == 4) {

			echo <<<HTML
<div id="dle_recaptcha{$id}" style="padding-top:5px;height:78px;"></div><input type="hidden" name="recaptcha{$id}" id="recaptcha{$id}" value="1">
<script>
<!--
	var recaptcha_widget = false;
	
	if ( typeof turnstile === "undefined"  ) {
	
		$.getScript( "https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha&render=explicit").done(function () {
		
			var setIntervalID = setInterval(function () {
				if (window.turnstile) {
					clearInterval(setIntervalID);
					recaptcha_widget = turnstile.render('#dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}', 'language':'{$lang['language_code']}'});
				};
			}, 1500);
		});

    } else {

			var setIntervalID = setInterval(function () {
				if (window.turnstile && recaptcha_widget === false) {
					clearInterval(setIntervalID);
					recaptcha_widget = turnstile.render('#dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}', 'language':'{$lang['language_code']}'});
				};
			}, 1500);
	}
//-->
</script>
HTML;
		} else {
			
			echo <<<HTML
<div id="dle_recaptcha{$id}" style="padding-top:5px;height:78px;"></div><input type="hidden" name="recaptcha{$id}" id="recaptcha{$id}" value="1">
<script>
<!--
	var recaptcha_widget;
	
	if ( typeof grecaptcha === "undefined"  ) {
	
		$.getScript( "https://www.google.com/recaptcha/api.js?hl={$lang['language_code']}&render=explicit").done(function () {
		
			var setIntervalID = setInterval(function () {
				if (window.grecaptcha) {
					clearInterval(setIntervalID);
					recaptcha_widget = grecaptcha.render('dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
				};
			}, 300);
		});

    } else {
		recaptcha_widget = grecaptcha.render('dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
	}
//-->
</script>
HTML;
		}
		
	} else {

		echo <<<HTML
<div style="padding-top:5px;" class="dle-captcha"><a onclick="reload{$id}(); return false;" title="{$lang['reload_code']}" href="#"><span id="dle-captcha{$id}"><img src="{$_ROOT_DLE_URL}engine/modules/antibot/antibot.php" alt="{$lang['reload_code']}" width="160" height="80" /></span></a>
<input class="sec-code" type="text" name="sec_code{$id}" id="sec_code{$id}" placeholder="{$lang['captcha_hint']}" required>
</div>
<script>
<!--
function reload{$id} () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha{$id}').innerHTML = '<img src="{$_ROOT_DLE_URL}engine/modules/antibot/antibot.php?rndval=' + rndval + '" width="160" height="80" alt="" />';
	document.getElementById('sec_code{$id}').value = '';
};
//-->
</script>
HTML;

	}
}
	
echo "<input type=\"hidden\" name=\"postid{$id}\" id=\"postid{$id}\" value=\"{$row['post_id']}\" /></form>";

if( $config['simple_reply'] ) {

	echo  <<<HTML
<div class="save-buttons" style="text-align: right;">
	<input class="bbcodes cancelchanges" title="{$lang['bb_t_cancel']}" type="button" onclick="ajax_cancel_reply(); return false;" value="{$lang['bb_b_cancel']}">
	<input class="bbcodes applychanges" title="{$lang['reply_comments']}" type="button" onclick="ajax_fast_reply('{$id}', '{$indent}', '{$needwrap}'); return false;" value="{$lang['reply_comments_1']}">
</div>
HTML;

	
}

?>