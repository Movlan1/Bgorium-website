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
 File: comments.php
-----------------------------------------------------
 Use: WYSIWYG for comments
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$p_id = isset($p_id) ? intval($p_id) : 0;
$p_name= isset($p_name) ? $p_name : '';
$comments_image_uploader_loaded = isset($comments_image_uploader_loaded) ? $comments_image_uploader_loaded : false;
$comments_mobile_editor = isset($comments_mobile_editor) ? $comments_mobile_editor : false;

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

$e_plugins = '';

if ($user_group[$member_id['user_group']]['allow_url']) { $link_icon = "link unlink dleleech "; $e_plugins = 'link autolink '; } else $link_icon = "";

$mobile_link_icon = $link_icon;

if ($user_group[$member_id['user_group']]['allow_image']) {
	
	if($config['bbimages_in_wysiwyg']) {
		
		$link_icon .= "| dleimage ";
		
	} else {
		$link_icon .= "| image ";
	}

	$e_plugins .= 'image ';
}

$image_upload = array();

if ( $user_group[$member_id['user_group']]['allow_up_image'] ) {

	if(!$comments_image_uploader_loaded) {
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

	$tiny_mce_conf = <<<HTML

	{$image_upload[1]}
	
	tinyMCE.baseURL = dle_root + 'engine/editor/jscripts/tiny_mce';
	tinyMCE.suffix = '.min';
	var dle_theme = '{$dark_theme}';
	dle_theme = dle_theme.trim();

	if(dle_theme != '') {
		$('body').addClass( dle_theme );
	} else {
		if ( $("body").hasClass('dle_theme_dark') ) {
			dle_theme = 'dle_theme_dark';
		}
	}
HTML;

	if( $comments_mobile_editor ) {

		$tiny_mce_conf .= <<<HTML
tinymce.init({
	selector: "textarea#comments",

	language : "{$lang['language_code']}",
	directionality: '{$lang['direction']}',
	body_class: dle_theme,
	skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',
	content_css : dle_root + "{$editor_css}",
	element_format : 'html',
		
	width : "100%",
	min_height : 40,
	max_height : 250,

	deprecation_warnings: false,
	promotion: false,
	cache_suffix: '?v={$config['cache_id']}',
	license_key: 'gpl',
	plugins: "{$e_plugins}autoresize lists dlebutton codesample",
	
	draggable_modal: true,
	toolbar_mode: 'floating',
	contextmenu: false,
	relative_urls : false,
	convert_urls : false,
	remove_script_host : false,
	browser_spellcheck: true,
	extended_valid_elements : "div[align|style|class|data-commenttime|data-commentuser|data-commentid|data-commentpostid|data-commentgast|contenteditable],span[id|data-username|data-userurl|align|style|class|contenteditable],b/strong,i/em,u,s,p[align|style|class|contenteditable],pre[class],code",
	
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
	elementpath: false,
	branding: false,
	text_patterns: [],
	dle_root : dle_root,
	dle_upload_area : "comments",
	dle_upload_user : "{$p_name}",
	dle_upload_news : "{$p_id}",

	link_default_target: '_blank',
	editable_class: 'contenteditable',
	noneditable_class: 'noncontenteditable',
	image_dimensions: true,
	{$image_upload[2]}

	toolbar: "formatgroup paragraphgroup insertgroup",
	placeholder: "{$lang['comm_placeholder']}",
	menubar: false,
	statusbar: false,
	toolbar_location: "bottom",
	object_resizing: false,
	contextmenu: false,
	autoresize_overflow_padding: 10,
	autoresize_bottom_margin: 1,

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
			$("#mobilewseditor").addClass("focused");
		});

		editor.on("blur", () => {
			$("#mobilewseditor").removeClass("focused");
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
});

HTML;

	} else {

		$tiny_mce_conf .= <<<HTML

	var additionalplugins = '';
	var maxheight = $(window).height() - 50;
	
	if (typeof getBaseSize === "function") {
		var height = 260 * getBaseSize();
	} else {
		var height = 260;
	}

	if($('body').hasClass('editor-autoheight')) {
       additionalplugins += ' autoresize';
    }

	tinymce.init({
		selector: 'textarea#comments',
		language : "{$lang['language_code']}",
		directionality: '{$lang['direction']}',
		body_class: dle_theme,
		skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',
		element_format : 'html',
		width : "100%",
		height : height,
		min_height : 40,
		max_height: maxheight,
		autoresize_bottom_margin: 1,
		statusbar: false,
		deprecation_warnings: false,
		promotion: false,
		cache_suffix: '?v={$config['cache_id']}',
		license_key: 'gpl',
		plugins: "{$e_plugins}lists quickbars dlebutton codesample"+additionalplugins,
		
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
		paste_postprocess: (editor, args) => {
			args = DLEPasteSafeText(args, {$user_group[$member_id['user_group']]['allow_url']});
		},
		paste_as_text: true,

	    formats: {
	      bold: {inline: 'b'},
	      italic: {inline: 'i'},
	      underline: {inline: 'u', exact : true},
	      strikethrough: {inline: 's', exact : true}
	    },

		elementpath: false,
		branding: false,
		text_patterns: [],
		dle_root : dle_root,
		dle_upload_area : "comments",
		dle_upload_user : "{$p_name}",
		dle_upload_news : "{$p_id}",
		
		menubar: false,
		link_default_target: '_blank',
		editable_class: 'contenteditable',
		noneditable_class: 'noncontenteditable',
		image_dimensions: true,
		{$image_upload[2]}
		
		toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | dleemo {$link_icon} | dlequote codesample dlespoiler dlehide",
		
		mobile: {
			toolbar_mode: "sliding",
			toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | dleemo {$mobile_link_icon} dlequote dlespoiler dlehide",
			
		},
		
		content_css : dle_root + "{$editor_css}",
		
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

	});
HTML;

	}

	$onload_scripts[] = $tiny_mce_conf;
	unset($tiny_mce_conf);

	$wysiwyg = <<<HTML
<script>
	var text_upload = "{$lang['bb_t_up']}";
	var dle_quote_title  = "{$lang['i_quote']}";
</script>
HTML;

	if ($comments_mobile_editor) {

$wysiwyg .= <<<HTML
<div class="dleaddcomments-editor mobilewseditor dlecomments-editor{$dark_theme}" id="mobilewseditor">
  <textarea id="comments" name="comments" style="width:100%;height:40px;"></textarea>
</div>
HTML;

	} else {
		$wysiwyg .= <<<HTML
<div class="dleaddcomments-editor wseditor dlecomments-editor{$dark_theme}">
	<textarea id="comments" name="comments" style="width:100%;height:260px;"></textarea>
</div>
HTML;
	}

