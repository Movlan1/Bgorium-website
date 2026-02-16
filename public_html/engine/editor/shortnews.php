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
 File: shortnews.php
-----------------------------------------------------
 Use: WYSIWYG for adminpanel
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if ($mod != "editnews") {
	$row['id'] = 0;
	$row['autor'] = $member_id['name'];
}

$row['id'] = isset($row['id']) ? intval($row['id']) : 0;
$mod = isset($mod) ? $mod : '';

$lang['language_code'] = totranslit( $lang['language_code'], false, false );
$p_name = urlencode($row['autor']);
$editor_js_code = '';


if($config['bbimages_in_wysiwyg']) {
	$implugin = 'dleimage';
} else $implugin = 'image';

$image_upload = array();
	
if ( $user_group[$member_id['user_group']]['allow_image_upload'] ) {

		$image_upload[0] = "dleupload ";

		$image_upload[1] = <<<HTML
var dle_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
  var xhr, formData;

  xhr = new XMLHttpRequest();
  xhr.withCredentials = false;
  xhr.open('POST', 'engine/ajax/controller.php?mod=upload');
  
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
		editor.selection.setContent('<a href="'+json.flink+'" class="highslide">'+object.prop('outerHTML')+'</a><br>');
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
  formData.append("news_id", "{$row['id']}");
  formData.append("area", "short_story");
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

        /* call the callback and populate the Title field with the file name */
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
		$image_upload[2] = "paste_data_images: false,";
		
	}	
	
	if( $user_group[$member_id['user_group']]['allow_file_upload'] ) {
		$image_upload[0] = "dleupload ";
	}

$chat_gpt = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '');

if ( $config['enable_ai'] AND in_array($member_id['user_group'], explode(',', trim($config['ai_groups'])) ) ) {

	$chat_gpt[0] = 'ai ';
	$chat_gpt[1] = 'aidialog ';
	$chat_gpt[2] = 'aishortcuts ';
	$chat_gpt[3] = "ai_request,
	ai_shortcuts: [
		{ title: 'Summarize content', prompt: '{$lang['ai_command_1']}', selection: true },
		{ title: 'Improve writing', prompt: '{$lang['ai_command_2']}', selection: true },
		{ title: 'Simplify language', prompt: '{$lang['ai_command_3']}', selection: true },
		{ title: 'Expand upon', prompt: '{$lang['ai_command_4']}', selection: true },
		{ title: 'Trim content', prompt: '{$lang['ai_command_5']}', selection: true },
		{
			title: 'Change tone', subprompts: [
			{ title: 'Professional', prompt: '{$lang['ai_command_6']}', selection: true },
			{ title: 'Casual', prompt: '{$lang['ai_command_7']}', selection: true },
			{ title: 'Direct', prompt: '{$lang['ai_command_8']}', selection: true },
			{ title: 'Confident', prompt: '{$lang['ai_command_9']}', selection: true },
			{ title: 'Friendly', prompt: '{$lang['ai_command_10']}', selection: true },
			]
		},
		{
			title: 'Change style', subprompts: [
			{ title: 'Business', prompt: '{$lang['ai_command_11']}', selection: true },
			{ title: 'Legal', prompt: '{$lang['ai_command_12']}', selection: true },
			{ title: 'Journalism', prompt: '{$lang['ai_command_13']}', selection: true },
			{ title: 'Medical', prompt: '{$lang['ai_command_14']}', selection: true },
			{ title: 'Poetic', prompt: '{$lang['ai_command_15']}', selection: true },
			]
		},
		{
			title: 'Translate', subprompts: [
			{ title: 'Translate to English', prompt: 'Translate this content to English language.', selection: true },
			{ title: 'Translate to Russian', prompt: 'Translate this content to Russian language.', selection: true },
			{ title: 'Translate to German', prompt: 'Translate this content to German language.', selection: true },
			{ title: 'Translate to Spanish', prompt: 'Translate this content to Spanish language.', selection: true },
			{ title: 'Translate to Portuguese', prompt: 'Translate this content to Portuguese language.', selection: true },
			{ title: 'Translate to French', prompt: 'Translate this content to French language.', selection: true },
			{ title: 'Translate to Norwegian', prompt: 'Translate this content to Norwegian language.', selection: true },
			{ title: 'Translate to Ukrainian', prompt: 'Translate this content to Ukrainian language.', selection: true },
			{ title: 'Translate to Japanese', prompt: 'Translate this content to Japanese language.', selection: true },
			{ title: 'Translate to Korean', prompt: 'Translate this content to Korean language.', selection: true },
			{ title: 'Translate to Simplified Chinese', prompt: 'Translate this content to Simplified Chinese language.', selection: true },
			{ title: 'Translate to Hebrew', prompt: 'Translate this content to Hebrew language.', selection: true },
			{ title: 'Translate to Hindi', prompt: 'Translate this content to Hindi language.', selection: true },
			{ title: 'Translate to Arabic', prompt: 'Translate this content to Arabic language.', selection: true },
			]
		},
	],
";

	if($config['ai_tokens'] == '') $config['ai_tokens'] = 'null';
	if($config['ai_completion_tokens'] == '') $config['ai_completion_tokens'] = 'null';
	if($config['ai_temperature'] == '') $config['ai_temperature'] = 'null';

	$chat_gpt[4] = <<<HTML
const gptFetchApi = import("./engine/editor/jscripts/tiny_mce/plugins/ai/fetch/index.js").then(module => module.fetchEventSource);

const gpt_api_key = '{$config['ai_key']}';
const gpt_api_endurl = '{$config['ai_endpoint']}';
const gpt_api_mode = '{$config['ai_mode']}';
const gpt_api_tokens = {$config['ai_tokens']};
const gpt_api_completion_tokens = {$config['ai_completion_tokens']};
const gpt_api_temperature = {$config['ai_temperature']};

const ai_request = (request, respondWith) => {
  respondWith.stream((signal, streamMessage) => {
    const conversation = request.thread.flatMap((event) => {
      if (event.response) {
        return [
          { role: 'user', content: event.request.query },
          { role: 'assistant', content: event.response.data }
        ];
      } else {
        return [];
      }
    });

    const pluginSystemMessages = request.system.map((content) => ({
      role: 'system',
      content
    }));

    const systemMessages = [
      ...pluginSystemMessages,
      { role: 'system', content: 'Remove lines with ``` from the response start and response end.' },
	  { role: 'system', content: 'Write PHP, CSS, Javascript, SQL code examples in the response inside <pre class="language-markup"><code></code></pre> tags formatted and converted special characters to HTML entities.' }
    ]

    const content = request.context.length === 0 || conversation.length > 0
      ? request.query
      : `Question: \${request.query} Context: """\${request.context}"""`;

    const messages = [
      ...conversation,
      ...systemMessages,
      { role: 'user', content }
    ];

	let hasHead = false;
	let markdownHead = '';

	const hasMarkdown = (message) => {
		if (message.includes('`') && markdownHead !== '```') {
			const numBackticks = message.split('`').length - 1;
			markdownHead += '`'.repeat(numBackticks);
			if (hasHead && markdownHead === '```') {
				markdownHead = '';
				hasHead = false;
			}
			return true;
		} else if (message.includes('html') && markdownHead === '```') {
			markdownHead = '';
			hasHead = true;
			return true;
		}
		return false;
	};

    var requestBody = {
      model: gpt_api_mode,
      messages,
      stream: true
    };

	if(gpt_api_temperature !== null ) {
		requestBody = { ...requestBody, temperature: gpt_api_temperature };
	}

	if(gpt_api_tokens !== null ) {
		requestBody = { ...requestBody, max_tokens: gpt_api_tokens };
	}

	if(gpt_api_completion_tokens !== null ) {
		requestBody = { ...requestBody, max_completion_tokens: gpt_api_completion_tokens };
	}

    const openAiOptions = {
      signal,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer \${gpt_api_key}`
      },
      body: JSON.stringify(requestBody)
    };

    const onopen = async (response) => {
      if (response) {
        const contentType = response.headers.get('content-type');
        if (response.ok && contentType?.includes('text/event-stream')) {
          return;
        } else if (contentType?.includes('application/json')) {
          const data = await response.json();
          if (data.error) {
            throw new Error(`\${data.error.type}: \${data.error.message}`);
          } else if(data.detail){
			 throw new Error(`\${data.detail}`);
		  }
        }
      } else {
        throw new Error('Failed to communicate with the ChatGPT API');
      }
    };

    const onmessage = (ev) => {
      const data = ev.data;
      if (data !== '[DONE]') {
        const parsedData = JSON.parse(data);
        const firstChoice = parsedData?.choices[0];
        const message = firstChoice?.delta?.content;
        if (message) {
			if (!hasMarkdown(message)) {
				streamMessage(message);
			}
        }
      }
    };

    const onerror = (error) => {
      throw error;
    };

    return gptFetchApi
    .then(fetchEventSource =>
      fetchEventSource(gpt_api_endurl, {
        ...openAiOptions,
        openWhenHidden: true,
        onopen,
        onmessage,
        onerror
      })
    )
    .then(async (response) => {
      if (response && !response.ok) {
        const data = await response.json();
        if (data.error) {
          throw new Error(`\${data.error.type}: \${data.error.message}`);
        }
      }
    })
    .catch(onerror);
  });
};
HTML;

}

if( @file_exists( ROOT_DIR . '/templates/'. $config['skin'].'/editor.css' ) ) {
	
	$editor_css = "templates/{$config['skin']}/editor.css?v={$config['cache_id']}";
		
} else $editor_css = "engine/editor/css/content.css?v={$config['cache_id']}";

	$editor_js_code = <<<HTML
<script>
function init_dle_editor ( filelds ) {

	tinymce.remove(filelds);

	tinyMCE.baseURL = 'engine/editor/jscripts/tiny_mce';
	tinyMCE.suffix = '.min';

	{$image_upload[1]}
	{$chat_gpt[4]}

	if(dle_theme === null) dle_theme = '';

	var body_class = dle_theme;
	var height = 400 * getBaseSize();
	if( height > 600 ) height = 600;

	if ( $('html').attr('class') ) {
		body_class = body_class + ' ' + $('html').attr('class');
	}

	if($('body').hasClass('style-smoothing')) {
        body_class = body_class + ' style-smoothing';
    }

	var statusbar = true;
	var additionalplugins = '';
	var maxheight = $(window).height() - 50;

	if($('body').hasClass('editor-style-light') || $('body').hasClass('editor-autoheight')) {
       statusbar = false;
    } else  additionalplugins += ' wordcount';
	
	if($('body').hasClass('editor-autoheight')) {
       additionalplugins += ' autoresize';
    }

	tinymce.init({
		selector: filelds,
		language : '{$lang['language_code']}',
		directionality: '{$lang['direction']}',
		element_format : 'html',
		body_class: body_class,
		skin: dle_theme == 'dle_theme_dark' ? 'oxide-dark' : 'oxide',
		width : "100%",
		height : height,
		min_height: 50,
		max_height: maxheight,
		autoresize_bottom_margin: 1,
		statusbar: statusbar,
		deprecation_warnings: false,
		promotion: false,
		cache_suffix: '?v={$config['cache_id']}',
		license_key: 'gpl',
		sandbox_iframes: false,
		plugins: "{$chat_gpt[0]}accordion fullscreen advlist autolink lists link image charmap anchor searchreplace visualblocks visualchars nonbreaking table codemirror dlebutton codesample quickbars autosave pagebreak toc" + additionalplugins,

		setup: function(editor) {
			editor.on('PreInit', function() {
				var shortEndedElements = editor.schema.getVoidElements();
				shortEndedElements['path'] = {};
				shortEndedElements['source'] = {};
				shortEndedElements['use'] = {};
			});
		},
		paste_postprocess: (editor, args) => {
			args.node.innerHTML = DLEclearPasteText(args.node.innerHTML, editor);
		},

		indentation : '20px',
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		verify_html: false,
		nonbreaking_force_tab: true,
		branding: false,
		link_default_target: '_blank',
		browser_spellcheck: true,
		pagebreak_separator: '{PAGEBREAK}',
		pagebreak_split_block: true,
		editable_class: 'contenteditable',
		noneditable_class: 'noncontenteditable',
		contextmenu: 'image table lists',

		image_advtab: true,
		image_caption: true,
		image_dimensions: true,
		image_uploadtab: false,
		{$image_upload[2]}
		{$chat_gpt[3]}

		draggable_modal: true,
		
		menubar: false,

		toolbar: [
			'{$chat_gpt[1]}bold italic underline strikethrough align bullist numlist link unlink dleleech table {$image_upload[0]} {$implugin} dlemp dlaudio dletube dleemo dlequote dlehide dlespoiler codesample code dlemore',
			'fontformatting forecolor backcolor pastetext | outdent indent subscript superscript anchor accordion pagebreak dlepage hr charmap searchreplace toc dletypo visualblocks | restoredraft undo redo removeformat fullscreen'
		],
  
		mobile: {
			plugins: '{$chat_gpt[0]}autoresize link image dlebutton codemirror',
			toolbar: '{$chat_gpt[1]}bold italic underline alignleft aligncenter alignright link dleleech {$image_upload[0]} {$implugin} dlemp dlaudio dletube dlequote dlespoiler dlehide code',
			max_height: 400,
			autoresize_bottom_margin: 1,
			statusbar: false,
		},

		toolbar_groups: {
		  
			fontformatting: {
			  icon: 'change-case',
			  tooltip: 'Formatting',
			  items: 'blocks styles fontfamily fontsizeinput lineheight'
			},
			  
			align: {
			  icon: 'align-center',
			  tooltip: 'Formatting',
			  items: 'alignleft aligncenter alignright alignjustify'
			}
		},

		block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6;Tag (p)=p;Tag (div)=div;',
		style_formats: [
			{ title: 'Information Block', block: 'div', wrapper: true, styles: { 'color': '#333333', 'border': 'solid 1px #00897B', 'padding': '0.7rem', 'background-color': '#E0F2F1', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Warning Block', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #FF9800', 'padding': '0.7rem', 'background-color': '#FFF3E0', 'color': '#aa3510', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Error Block', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #FF5722', 'padding': '0.7rem', 'background-color': '#FBE9E7', 'color': '#9c1f1f', 'box-shadow': 'rgb(0 0 0 / 24%) 0px 1px 2px' } },
			{ title: 'Borders', block: 'div', wrapper: true, styles: { 'border': 'solid 1px #ccc', 'padding': '0.7rem' } },
			{ title: 'Borders top and bottom', block: 'div', wrapper: true, styles: { 'border-top': 'solid 1px #ccc', 'border-bottom': 'solid 1px #ccc', 'padding': '10px 0' } },
			{ title: 'Use a shadow', block: 'div', wrapper: true, styles: { 'box-shadow': '0 5px 12px rgba(126,142,177,0.2)' } },
			{ title: 'Increased letter spacing', inline: 'span', styles: { 'letter-spacing': '1px' } },
			{ title: 'Сapital letters', inline: 'span', styles: { 'text-transform': 'uppercase' } },
			{ title: 'Gray background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#607D8B', 'padding': '0.7rem' } },
			{ title: 'Brown background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#795548', 'padding': '0.7rem' } },
			{ title: 'Blue background', block: 'div', wrapper: true, styles: { 'color': '#104d92', 'background-color': '#E3F2FD', 'padding': '0.7rem' } },
			{ title: 'Green background', block: 'div', wrapper: true, styles: { 'color': '#fff', 'background-color': '#009688', 'padding': '0.7rem' } },
		],

		image_class_list: [
			{ title: 'None', value: '' },
			{ title: 'Image Border', value: 'image-bordered' },
			{ title: 'Image Shadow', value: 'image-shadows' },
			{ title: 'Image Padding', value: 'image-padded' },
			{ title: 'Borders Padding', value: 'image-bordered image-padded' },
			{ title: 'Shadow Padding', value: 'image-shadows image-padded' },
		],
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
		
		text_patterns: [],
		quickbars_insert_toolbar: false, // 'quickimage dlemp quicktable bullist numlist | dlequote dlespoiler dlehide codesample | hr blocks'
		quickbars_selection_toolbar: 'bold italic underline | quicklink | dlequote dlespoiler dlehide | forecolor backcolor | styles | blocks fontsizeinput',
		quickbars_image_toolbar: 'alignleft aligncenter alignright | image link',

		autosave_ask_before_unload: true,
		autosave_interval: '10s',
		autosave_prefix: 'dle-editor-{path}{query}-{id}-',
		autosave_restore_when_empty: false,
		autosave_retention: '10m',
  
		formats: {
		  bold: {inline: 'b'},  
		  italic: {inline: 'i'},
		  underline: {inline: 'u', exact : true},  
		  strikethrough: {inline: 's', exact : true}
		},

		toc_depth : 4,
		
		dle_root : "",
		dle_upload_area : "short_story",
		dle_upload_user : "{$p_name}",
		dle_upload_news : "{$row['id']}",

		content_css : "{$editor_css}"

	});

}

jQuery(function($){
	init_dle_editor ( 'textarea.wysiwygeditor' );
});
</script>
HTML;

if ($mod != "rss") {
	
	echo $editor_js_code . "\n";

	if (!$config['disable_short']) {
		
		if (!isset($row['short_story'])) $row['short_story'] = "";

		echo <<<HTML
		<div class="editor-panel"><textarea id="short_story" name="short_story" class="wysiwygeditor" style="width:98%;height:400px;">{$row['short_story']}</textarea></div>
HTML;

	}
}

?>