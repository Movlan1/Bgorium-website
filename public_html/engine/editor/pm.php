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
 File: pm.php
-----------------------------------------------------
 Use: WYSIWYG for personal messages
=====================================================
*/

if (!defined('DATALIFEENGINE')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../');
	die("Hacking attempt!");
}

$box_class = "wseditor dlepm-editor";
$e_plugins = '';

if ($user_group[$member_id['user_group']]['allow_url']) {
	$link_icon = "link unlink dleleech ";
	$e_plugins = 'link autolink ';
} else $link_icon = "";

$mobile_link_icon = $link_icon;

if ($user_group[$member_id['user_group']]['allow_image']) {
	if ($config['bbimages_in_wysiwyg']) $link_icon .= "| dleimage "; else $link_icon .= "| image ";
	$e_plugins .= 'image ';
}

if (@file_exists(ROOT_DIR . '/templates/' . $config['skin'] . '/editor.css')) {
	$editor_css = "templates/{$config['skin']}/editor.css?v={$config['cache_id']}";
} else $editor_css = "engine/editor/css/content.css?v={$config['cache_id']}";

$dark_theme = "";

if (defined('TEMPLATE_DIR')) {
	$template_dir = TEMPLATE_DIR;
} else $template_dir = ROOT_DIR . "/templates/" . $config['skin'];

if (!isset($tpl)) $tpl = new dle_template();

if ($tpl->smartphone or $tpl->tablet) $comments_mobile_editor = true;

if (is_file($template_dir . "/info.json")) {

	$data = json_decode(trim(file_get_contents($template_dir . "/info.json")), true);

	if (isset($data['type']) and $data['type'] == "dark") {
		$dark_theme = " dle_theme_dark";
	}
}

if (!$comments_mobile_editor AND isset($is_pm_ajax_edit_mode) ) $code_icon = " code"; else $code_icon = "";

if ( isset($is_pm_ajax_mode) ) $ed_selector = 'textarea.ajaxwysiwygeditor'; else $ed_selector = 'textarea#comments';

$editor_scrips = <<<HTML
	tinymce.remove('{$ed_selector}');

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

	if ( $('html').attr('class') ) {
		dle_theme = dle_theme + ' ' + $('html').attr('class');
	}

	var height = 260;

	tinymce.init({
		selector: '{$ed_selector}',
		license_key: 'gpl',
		language : "{$lang['language_code']}",
		directionality: '{$lang['direction']}',
		element_format : 'html',
		body_class: dle_theme,
		skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',
		width : "100%",
		deprecation_warnings: false,
		promotion: false,
		cache_suffix: '?v={$config['cache_id']}',
		draggable_modal: true,
		toolbar_mode: 'floating',
		contextmenu: false,
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		browser_spellcheck: true,
		extended_valid_elements : "div[align|style|class|data-commenttime|data-commentuser|contenteditable],span[id|data-username|data-userurl|align|style|class|contenteditable],b/strong,i/em,u,s,p[align|style|class|contenteditable],pre[class],code",

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
		paste_data_images: false,
		elementpath: false,
		branding: false,
		text_patterns: [],
		menubar: false,
		statusbar: false,
		link_default_target: '_blank',
		editable_class: 'contenteditable',
		noneditable_class: 'noncontenteditable',
		image_dimensions: true,
		
		dle_root: dle_root,
		content_css : dle_root + "{$editor_css}",
		
HTML;

if ($comments_mobile_editor) {

	$box_class = "mobilewseditor dlepm-editor";

	$editor_scrips .= <<<HTML
		min_height : 40,
		max_height : 250,
		autoresize_overflow_padding: 10,
		autoresize_bottom_margin: 1,
		plugins: "{$e_plugins}autoresize lists dlebutton codesample",
		
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
				$(".dlepm-editor.mobilewseditor").addClass("focused");
			});

			editor.on("blur", () => {
				$(".dlepm-editor.mobilewseditor").removeClass("focused");
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

	$editor_scrips .= <<<HTML
		height : height,

		plugins: "{$e_plugins}lists quickbars dlebutton codesample codemirror",
		quickbars_insert_toolbar: '',
		quickbars_selection_toolbar: 'bold italic underline | dlequote dlespoiler dlehide',
		
		toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | dleemo {$link_icon} | dlequote codesample dlespoiler dlehide{$code_icon}",
		
		mobile: {
			toolbar_mode: "sliding",
			toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | {$mobile_link_icon} dlequote dlespoiler dlehide{$code_icon}",
			
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

$editor_scrips .= <<<HTML
	});
HTML;

if ($comments_mobile_editor) $area_height = 40; else $area_height = 260;

if( !isset($is_pm_ajax_mode) ) {
	$onload_scripts[] = $editor_scrips;
	unset($editor_scrips);

	$wysiwyg = <<<HTML
<div class="{$box_class}{$dark_theme}">
	<textarea id="comments" name="comments" style="width:100%;height:{$area_height}px;" class="ajaxwysiwygeditor"></textarea>
</div>
HTML;

}
