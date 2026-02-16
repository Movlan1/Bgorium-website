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
 File: parse.class.php
-----------------------------------------------------
 Use: Text Parser
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

class ParseFilter {

	public $video_config = array ();
	public $code_text = array ();
	public $code_count = 0;
	public $image_count = 0;
	public $codes_param = array ();
	public $wysiwyg = false;
	public $safe_mode = false;
	public $allow_code = true;
	public $leech_mode = false;
	public $disable_leech = false;
	public $filter_mode = true;
	public $allow_url = true;
	public $allow_image = true;
	public $allow_video = true;
	public $allow_media = true;
	public $edit_mode = true;
	public $allowbbcodes = true;
	public $not_allowed_tags = false;
	public $not_allowed_text = false;
	public $remove_html = true;
	public $is_comments = false;
	public $found_media_content = false;
	public $allowed_domains = array();

	public $font_sizes = array (1 => '8', 2 => '10', 3 => '12', 4 => '14', 5 => '18', 6 => '24', 7 => '36' );
	public $allowed_fonts = array ("Arial", "Arial Black", "Century Gothic", "Courier New", "Georgia", "Impact", "System", "Tahoma", "Times New Roman", "Verdana");

	public $htmlparser = null;
	
	protected $media_providers = null;
	
	function __construct($tagsArray = array()) {
		global $config;

		if( $config['allow_iframe'] ) {

			$this->allowed_domains = explode(',', $config['iframe_domains']);

			$this->allowed_domains = array_map(
				function ($item) {
					if ( is_string($item) ) {
						return trim(preg_quote($item, '%'));
					}
				},
				$this->allowed_domains
			);

		}

		$parse_config = HTMLPurifier_Config::createDefault();
		$parse_config->set('Core.Encoding', 'UTF-8');
		$parse_config->set('Core.AllowParseManyTags', true);
		$parse_config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$parse_config->set('CSS.MaxImgLength', null);

		$parse_config->set('Cache.SerializerPath', ENGINE_DIR.'/cache/system');

		$parse_config->set('AutoFormat.RemoveEmpty', true);

		$parse_config->set('HTML.DefinitionID', 'html5-definitions');
		$parse_config->set('HTML.DefinitionRev', 1);

		$parse_config->set('Attr.DefaultImageAlt', '' );
		$parse_config->set('Attr.AllowedFrameTargets', array("_blank") );
		$parse_config->set('Attr.AllowedRel', array("highslide", "external" , "noopener" , "noreferrer", "nofollow", "sponsored", "ugc") );
		$parse_config->set('URI.AllowedSchemes', array('http' => true, 'https' => true, 'mailto' => true, 'ftp' => true, 'nntp' => true, 'news' => true, 'tel' => true,'magnet' => true,'viber' => true,'tg' => true,'skype' => true, 'market' => true) );
		$parse_config->set('Attr.EnableID', true);
		$parse_config->set('Attr.ID.HTML5', true);
		$parse_config->set('HTML.FlashAllowFullScreen', true);
		$parse_config->set('HTML.MaxImgLength', null);
		$parse_config->set('HTML.TargetNoreferrer', false);
		$parse_config->set('AutoFormat.RemoveEmpty.Predicate', array ('colgroup' => array(),'th' => array(),'td' => array(),'div' => array(),'p' => array(), 'i' => array(), 'video' => array(), 'audio' => array(), 'iframe' => array('src') ));

		if ( count($tagsArray) ) {
			
			for($i = 0; $i < count( $tagsArray ); $i ++) {
				$tagsArray[$i] = strtolower( $tagsArray[$i] );
			}

			$parse_config->set('HTML.Allowed', implode(",",$tagsArray) );
			$parse_config->set('Attr.AllowedClasses', array("quote", "title_quote", "highslide", "fr-dib", "fr-dii", "fr-fir", "fr-draggable", "fr-fil", "fr-rounded", "fr-padded", "fr-bordered", "fr-shadows", "fr-strong", "fr-text-red", "fr-text-blue", "fr-text-green", "native-emoji", "contenteditable", "noncontenteditable", "quote_block", "quote_body", "language-markup", "language-javascript", "language-css", "language-php", "language-ruby", "language-python", "language-java", "language-c", "language-csharp","language-cpp", "comments-user-profile", "fr-deletable") );
			$parse_config->set('CSS.AllowTricky', true);
			$parse_config->set('CSS.AllowedProperties', array("text-align", "width", "height", "margin-right", "margin-left", "display", "float") );
			$this->is_comments = true;
			
		} else {

			$parse_config->set('CSS.Trusted', true);
			$parse_config->set('CSS.AllowTricky', true);
			$parse_config->set('CSS.Proprietary', true);
			$parse_config->set('HTML.SafeEmbed', true);
			$parse_config->set('HTML.SafeObject', true);
			$parse_config->set('Output.FlashCompat', true);
			
			if ($config['allow_iframe'] AND count($this->allowed_domains) ) {
				$parse_config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.)?(' . implode("/|", $this->allowed_domains) . ')%');
				$parse_config->set('HTML.SafeIframe', true);
			}
			
		}
		
		if ($def = $parse_config->maybeGetRawHTMLDefinition()) {

			$def->addElement('section', 'Block', 'Flow', 'Common');
			$def->addElement('noindex', 'Block', 'Flow', 'Common');
			$def->addElement('nav',     'Block', 'Flow', 'Common');
			$def->addElement('article', 'Block', 'Flow', 'Common');
			$def->addElement('aside',   'Block', 'Flow', 'Common');
			$def->addElement('header',  'Block', 'Flow', 'Common');
			$def->addElement('footer',  'Block', 'Flow', 'Common');
			$def->addElement('summary',  'Block', 'Flow', 'Common');
			$def->addElement('datalist', 'Block', 'Flow', 'Common' );
			$def->addElement('rp', 'Block', 'Flow', 'Common' );
			$def->addElement('rt', 'Block', 'Flow', 'Common' );
			$def->addElement('ruby', 'Block', 'Flow', 'Common' );
			$def->addElement('address', 'Block', 'Flow', 'Common');
			$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

			$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement('figcaption', 'Inline', 'Flow', 'Common');

			$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
			  'src' => 'URI',
			  'type' => 'Text',
			  'id' => 'Text',
			  'width' => 'Length',
			  'height' => 'Length',
			  'poster' => 'URI',
			  'preload' => 'Enum#auto,metadata,none',
			  'controls' => 'Bool',
			  'autoplay' => 'Bool',
			  'loop' => 'Bool',
			  'muted' => 'Bool',
			  'playsinline' => 'Bool',
			));
			
			$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
			  'src' => 'URI',
			  'type' => 'Text',
			  'id' => 'Text',
			  'width' => 'Length',
			  'height' => 'Length',
			  'preload' => 'Enum#auto,metadata,none',
			  'controls' => 'Bool',
			  'autoplay' => 'Bool',
			  'loop' => 'Bool',
			  'muted' => 'Bool',
			));
			
			$def->addElement( 'track', 'Inline', 'Empty', 'Common', array(
			  'kind' => 'Enum#captions,chapters,descriptions,metadata,subtitle',
			  'src' => 'URI',
			  'srclang' => 'Text',
			  'label' => 'Text',
			  'default' => 'Bool',
			) );

			$def->addElement('source', 'Inline', 'Empty', 'Common', array(
			  'src' => 'URI',
			  'type' => 'Text',
			  'srcset' => 'Text',
			  'sizes' => 'Text',
			  'media' => 'Text',
			));
			
			$def->addElement('canvas', 'Block', 'Flow', 'Common', array(
			  'width' => 'Length',
			  'label' => 'Text',
			) );
			
			$def->addElement('details', 'Block', 'Flow', 'Common', array() );
			
			$def->addElement('picture', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
			  'srcset' => 'Text',
			  'sizes' => 'Text',
			  'media' => 'Text',
			  'type' => 'Text',
			) );
			
			$def->addElement('map', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
			  'id' => 'Text',
			  'name' => 'Text',
			) );
			
			$def->addElement('area', 'Inline', 'Empty', 'Common', array(
			  'alt' => 'Text',
			  'coords' => 'Text',
			  'shape' => 'Enum#default,rect,circle,poly',
			  'href' => 'URI',
			  'target' => 'Enum#_self,_blank,_top,_parent',
			));

			$def->addElement('svg', 'Block', 'Flow', 'Common', array(
			  'xmlns' => 'URI',
			  'width' => 'Length',
			  'height' => 'Length',
			  'fill' => 'Text',
			  'viewbox' => 'Text',
			) );

			$def->addElement('symbol', 'Block', 'Flow', 'Common', array(
			  'id' => 'Text',
			  'width' => 'Length',
			  'height' => 'Length',
			  'fill' => 'Text',
			  'viewbox' => 'Text',
			) );

			$def->addElement('path', 'Inline', 'Empty', 'Common', array('d' => 'Text'));
			$def->addElement('use', 'Inline', 'Empty', 'Common', array(
				'href' => 'URI',
				'xlink:href' => 'URI',
			  	'width' => 'Length',
			  	'height' => 'Length',
			  	'x' => 'Length',
			  	'y' => 'Length',
			));

	        $time = $def->addElement('time', 'Inline', 'Inline', 'Common', array('datetime' => 'Text', 'pubdate' => 'Bool'));
	        $time->excludes = array('time' => true);
		
			$def->addElement('s',    'Inline', 'Inline', 'Common');
			$def->addElement('var',  'Inline', 'Inline', 'Common');
			$def->addElement('sub',  'Inline', 'Inline', 'Common');
			$def->addElement('sup',  'Inline', 'Inline', 'Common');
			$def->addElement('mark', 'Inline', 'Inline', 'Common');
			$def->addElement('wbr',  'Inline', 'Empty', 'Core');
			$def->addElement('a', 'Flow', 'Flow', 'Common', array('href' => 'URI', 'download' => 'Bool','rel' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rel'),'rev' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rev')));

			$def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			$def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			$def->addElement('progress', 'Inline', 'Flow', 'Common', array('max' => 'Number', 'value' => 'CDATA'));


			$def->addAttribute('img', 'data-maxwidth', 'Number');
			$def->addAttribute('img', 'contenteditable', 'Enum#true,false');
			$def->addAttribute('img', 'usemap', 'Text');
			$def->addAttribute('img', 'srcset', 'Text');
			$def->addAttribute('img', 'sizes', 'Text');
			$def->addAttribute('img', 'loading', 'Enum#eager,lazy');

			$def->addAttribute('a', 'data-srcset', 'Text');
			$def->addAttribute('a', 'data-sizes', 'Text');

			$def->addAttribute('iframe', 'allow', 'Text');
			$def->addAttribute('iframe', 'loading', 'Enum#eager,lazy');
			$def->addAttribute('table', 'height', 'Text');
			$def->addAttribute('td', 'border', 'Text');
			$def->addAttribute('th', 'border', 'Text');
			$def->addAttribute('tr', 'width', 'Text');
			$def->addAttribute('tr', 'height', 'Text');
			$def->addAttribute('tr', 'border', 'Text');

			$def->addAttribute('span', 'data-username', 'Text');
			$def->addAttribute('span', 'data-userurl', 'URI');
			$def->addAttribute('span', 'contenteditable', 'Enum#true,false');

			$def->addAttribute('div', 'data-commenttime', 'Number');
			$def->addAttribute('div', 'data-commentuser', 'Text');
			$def->addAttribute('div', 'data-commentid', 'Number');
			$def->addAttribute('div', 'data-commentpostid', 'Number');
			$def->addAttribute('div', 'data-commentgast', 'Number');
			$def->addAttribute('div', 'contenteditable', 'Enum#true,false');

		}
		
 		$this->htmlparser = new HTMLPurifier($parse_config);
		$this->media_providers = new OEmbed();
		
	}
	
	function process($source) {

		$source = $this->decode( $source );
		
		$source = preg_replace( "/javascript:/i", "j&#1072;vascript:", $source );
		$source = preg_replace( "/data:/i", "d&#1072;ta:", $source );
		$source = str_replace( "__CODEAMP__", "&", $source );

		$source = $this->htmlparser->purify($source);
	
		$source = str_ireplace( "{include", "&#123;include", $source );
		$source = str_ireplace( "{content", "&#123;content", $source );
		$source = str_ireplace( "{custom", "&#123;custom", $source );
		$source = str_ireplace( "[custom", "&#91;custom", $source );
		$source = str_ireplace( "[not-custom", "&#91;not-custom", $source );
		$source = str_ireplace( "[/custom", "&#91;/custom", $source );
		$source = str_ireplace( "[/not-custom", "&#91;/not-custom", $source );
		$source = str_ireplace( "{THEME}", "&#123;THEME}", $source );
		$source = str_ireplace( "{newsnavigation", "&#123;newsnavigation", $source );
		$source = str_replace(array("_&#123;_", "_&#91;_"), array("_{_", "_[_"), $source);

		if ( $this->safe_mode AND !$this->wysiwyg AND $this->edit_mode ) {
			$source = str_replace( '"', '&quot;', $source );
			$source = str_replace( "'", '&#039;', $source );
		}
		
		$source = preg_replace_callback( "#<a(.+?)>(.*?)</a>#is", array( &$this, 'remove_bad_url'), $source );
		
		if ($this->safe_mode) {
			$source = str_ireplace("<p></p>", "", $source);
		} else {
			$source = str_ireplace("<p></p>", "<p><br></p>", $source);
		}
		
		if( $this->code_count ) {
			foreach ( $this->code_text as $key_find => $key_replace ) {
				$find[] = $key_find;
				$replace[] = $key_replace;
			}

			$source = str_replace( $find, $replace, $source );
		}

		$this->code_count = 0;
		$this->code_text = array ();

		$source = str_replace( "<?", "&lt;?", $source );
		$source = str_replace( "?>", "?&gt;", $source );

		$source = addslashes( $source );
		
		return $source;

	}
	
	function decode($source) {
		
		if ($source == '<p><br></p>' OR $source == '<p></p>') {
			return '';
		}
		
		$source = str_replace("&#8203;", '', $source);

		if ( $this->safe_mode ) {
			
			if( $this->remove_html ) {
				
				$source = htmlspecialchars( strip_tags($source), ENT_QUOTES, 'UTF-8' );
				
			} elseif( !$this->wysiwyg AND $this->edit_mode ) {
				
				$source = htmlspecialchars( $source, ENT_QUOTES, 'UTF-8' );
				
			}
			
		}

		return $source;
	}


	function BB_Parse($source, $use_html = true) {
		global $config, $lang;
		
		$source = preg_replace_callback("#<code>(.+?)</code>#is",  array(&$this, 'hide_code_tag'), $source);
			
		$find = array ('/data:/i','/about:/i','/vbscript:/i','/onclick/i','/onload/i','/onunload/i','/onabort/i','/onerror/i','/onblur/i','/onchange/i','/onfocus/i','/onreset/i','/onsubmit/i','/ondblclick/i','/onkeydown/i','/onkeypress/i','/onkeyup/i','/onmousedown/i','/onmouseup/i','/onmouseover/i','/onmouseout/i','/onselect/i','/javascript/i','/onmouseenter/i','/onwheel/i','/onshow/i','/onafterprint/i','/onbeforeprint/i','/onbeforeunload/i','/onhashchange/i','/onmessage/i','/ononline/i','/onoffline/i','/onpagehide/i','/onpageshow/i','/onpopstate/i','/onresize/i','/onstorage/i','/oncontextmenu/i','/oninvalid/i','/oninput/i','/onsearch/i','/ondrag/i','/ondragend/i','/ondragenter/i','/ondragleave/i','/ondragover/i','/ondragstart/i','/ondrop/i','/onmousemove/i','/onmousewheel/i','/onscroll/i','/oncopy/i','/oncut/i','/onpaste/i','/oncanplay/i','/oncanplaythrough/i','/oncuechange/i','/ondurationchange/i','/onemptied/i','/onended/i','/onloadeddata/i','/onloadedmetadata/i','/onloadstart/i','/onpause/i','/onprogress/i',	'/onratechange/i','/onseeked/i','/onseeking/i','/onstalled/i','/onsuspend/i','/ontimeupdate/i','/onvolumechange/i','/onwaiting/i','/ontoggle/i', '/language-j&#1072;vascript/i');
		$replace = array ("d&#1072;ta:", "&#1072;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#1072;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow', '&#111;nafterprint','&#111;nbeforeprint','&#111;nbeforeunload','&#111;nhashchange','&#111;nmessage','&#111;nonline','&#111;noffline','&#111;npagehide','&#111;npageshow','&#111;npopstate','&#111;nresize','&#111;nstorage','&#111;ncontextmenu','&#111;ninvalid','&#111;ninput','&#111;nsearch','&#111;ndrag','&#111;ndragend','&#111;ndragenter','&#111;ndragleave','&#111;ndragover','&#111;ndragstart','&#111;ndrop','&#111;nmousemove','&#111;nmousewheel','&#111;nscroll','&#111;ncopy','&#111;ncut','&#111;npaste','&#111;ncanplay','&#111;ncanplaythrough','&#111;ncuechange','&#111;ndurationchange','&#111;nemptied','&#111;nended','&#111;nloadeddata','&#111;nloadedmetadata','&#111;nloadstart','&#111;npause','&#111;nprogress',	'&#111;nratechange','&#111;nseeked','&#111;nseeking','&#111;nstalled','&#111;nsuspend','&#111;ntimeupdate','&#111;nvolumechange','&#111;nwaiting','&#111;ntoggle', 'language-javascript');

		if( !$use_html ) {

			$find[] = "'\r'";
			$replace[] = "";
			$find[] = "'\n'";
			$replace[] = "<br>";

		} else {

			$source = str_replace( "\r\n\r\n", "\n", $source );

		}

		if(!$config['emoji'] ) {
			$smilies_arr = explode(",", $config['smilies']);

			foreach ($smilies_arr as $smile) {

				$smile = trim($smile);
				$sm_image = "";

				if (file_exists(ROOT_DIR . "/engine/data/emoticons/" . $smile . ".png")) {
					if (file_exists(ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.png")) {
						$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.png 2x\">";
					} else {
						$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\">";
					}
				} elseif (file_exists(ROOT_DIR . "/engine/data/emoticons/" . $smile . ".gif")) {
					if (file_exists(ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.gif")) {
						$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.gif 2x\">";
					} else {
						$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\">";
					}
				}

				if ($sm_image) {

					$find[] = "':$smile:'";
					$replace[] = "<!--smile:{$smile}-->{$sm_image}<!--/smile-->";
				}
			}
		}

		if( $this->filter_mode ) $source = $this->word_filter( $source );

		$source = preg_replace( $find, $replace, $source );

		$source = str_replace( "`", "&#96;", $source );
		$source = str_ireplace( "{comments}", "&#123;comments}", $source );
		$source = str_ireplace( "{addcomments}", "&#123;addcomments}", $source );
		$source = str_ireplace( "{newsnavigation}", "&#123;newsnavigation}", $source );
		$source = str_ireplace( "[declination", "&#91;declination", $source );

		$source = str_replace( "<?", "&lt;?", $source );
		$source = str_replace( "?>", "?&gt;", $source );

		$count_start = substr_count ($source, "[quote");
		$count_end = substr_count ($source, "[/quote]");

		if ($count_start AND $count_start == $count_end) {
			$source = str_ireplace( "[quote=]", "[quote]", $source );

			if ( !$this->allow_code ) {
				$source = preg_replace_callback( "#\[(quote)\](.+?)\[/quote\]#is", array( &$this, 'clear_div_tag'), $source );
				$source = preg_replace_callback( "#\[(quote)=(.+?)\](.+?)\[/quote\]#is", array( &$this, 'clear_div_tag'), $source );
			}

			while( preg_match( "#\[quote\](.+?)\[/quote\]#is", $source ) ) {
				$source = preg_replace( "#\[quote\](.+?)\[/quote\]#is", "<!--QuoteBegin--><div class=\"quote\"><!--QuoteEBegin-->\\1<!--QuoteEnd--></div><!--QuoteEEnd-->", $source );
			}
			
			while( preg_match( "#\[quote=([^\]|\[|<]+)\](.+?)\[/quote\]#is", $source ) ) {
				$source = preg_replace( "#\[quote=(.+?)\](.+?)\[/quote\]#is", "<!--QuoteBegin \\1 --><div class=\"title_quote\">{$lang['i_quote']} \\1</div><div class=\"quote\"><!--QuoteEBegin-->\\2<!--QuoteEnd--></div><!--QuoteEEnd-->", $source );
			}
		}
	
		if ( $this->allowbbcodes ) {
			
			$count_start = substr_count ($source, "[spoiler");
			$count_end = substr_count ($source, "[/spoiler]");
	
			if ($count_start AND $count_start == $count_end) {
				$source = str_ireplace( "[spoiler=]", "[spoiler]", $source );
	
				if ( !$this->allow_code ) {
					$source = preg_replace_callback( "#\[(spoiler)\](.+?)\[/spoiler\]#is", array( &$this, 'clear_div_tag'), $source );
					$source = preg_replace_callback( "#\[(spoiler)=(.+?)\](.+?)\[/spoiler\]#is", array( &$this, 'clear_div_tag'), $source );
				}
				while( preg_match( "#\[spoiler\](.+?)\[/spoiler\]#is", $source ) ) {
					$source = preg_replace_callback( "#\[spoiler\](.+?)\[/spoiler\]#is", array( &$this, 'build_spoiler'), $source );
				}
				
				while( preg_match( "#\[spoiler=([^\]|\[|<]+)\](.+?)\[/spoiler\]#is", $source ) ) {
					$source = preg_replace_callback( "#\[spoiler=([^\]|\[|<]+)\](.+?)\[/spoiler\]#is", array( &$this, 'build_spoiler'), $source);
				}
	
			}
			
			if( $this->allow_url ) {
		
				$source = preg_replace_callback( "#\[(leech)\](\S.+?)\[/leech\]#i", array( &$this, 'build_url'), $source );
				$source = preg_replace_callback( "#\[(leech)\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/leech\]#i", array( &$this, 'build_url'), $source );
				$source = preg_replace_callback( "#\[(leech)\s*=\s*(\S.+?)\s*\](.*?)\[\/leech\]#i", array( &$this, 'build_url'), $source );
	
			} else {
	
				if( stristr( $source, "[url" ) !== false ) $this->not_allowed_tags = true;
				if( stristr( $source, "[leech" ) !== false ) $this->not_allowed_tags = true;
				if( stristr( $source, "&lt;a" ) !== false ) $this->not_allowed_tags = true;
	
			}
	
			if( $this->allow_image ) {
	
				$source = preg_replace_callback( "#\[img\](.+?)\[/img\]#i", array( &$this, 'build_image'), $source );
				$source = preg_replace_callback( "#\[img=(.+?)\](.+?)\[/img\]#i", array( &$this, 'build_image'), $source );
				$source = preg_replace_callback( "'\[thumb\](.+?)\[/thumb\]'i", array( &$this, 'build_thumb'), $source );
				$source = preg_replace_callback( "'\[thumb=(.+?)\](.+?)\[/thumb\]'i", array( &$this, 'build_thumb'), $source );
	
			} else {
	
				if( stristr( $source, "[img" ) !== false OR stristr( $source, "[thumb" ) !== false ) $this->not_allowed_tags = true;
				if( stristr( $source, "&lt;img" ) !== false ) $this->not_allowed_tags = true;
	
			}
	
			if( !$this->safe_mode ) {
	
				$source = preg_replace_callback( "'\[medium\](.+?)\[/medium\]'i", array( &$this, 'build_medium'), $source );
				$source = preg_replace_callback( "'\[medium=(.+?)\](.+?)\[/medium\]'i", array( &$this, 'build_medium'), $source );
			
			}
			
			if( $this->allow_media ) {
				
				$source = preg_replace_callback( "#\[media=([^\]]+)\]#i", array( &$this, 'build_media'), $source );
				
			} else {
	
				if( stristr( $source, "[media" ) !== false ) $this->not_allowed_tags = true;
	
			}
		
			if( $this->allow_video ) {
				
				$source = preg_replace_callback( "#\[video\s*=\s*(\S.+?)\s*\]#i", array( &$this, 'build_video'), $source );
				$source = preg_replace_callback( "#\[audio\s*=\s*(\S.+?)\s*\]#i", array( &$this, 'build_audio'), $source );
				
			} else {
	
				if( stristr( $source, "[video" ) !== false ) $this->not_allowed_tags = true;
				if( stristr( $source, "[audio" ) !== false ) $this->not_allowed_tags = true;
	
			}
					
			if ($this->is_comments) {
				
				if( intval( $config['auto_wrap'] ) ) {
					
					$source = preg_split( '((>)|(<))', $source, - 1, PREG_SPLIT_DELIM_CAPTURE );
					$n = count( $source );
					
					for($i = 0; $i < $n; $i ++) {
						if( $source[$i] == "<" ) {
							$i ++;
							continue;
						}
						
						if( preg_match( "#([^\s\n\r]{" . intval( $config['auto_wrap'] ) . "})#ui", $source[$i] ) ) {
			
							$source[$i] = preg_replace( "#([^\s\n\r]{" . intval( $config['auto_wrap']-1 ) . "})#ui", "\\1<br>", $source[$i] );
			
						}
			
					}
					
					$source = join( "", $source );
				
				}
				
			}
			
			$source = preg_replace_callback("#<a\s+([^>]+?)>#i", array( &$this, 'add_rel'), $source );
			$source = preg_replace_callback( "#<img(.+?)>#i", array( &$this, 'clear_img'), $source );

			if( $this->found_media_content ) {
				$source = preg_replace_callback( "#<p([^>]*?)>(.*?)</p>#is", array( &$this, 'fix_p_in_div'), $source );
			}

			if( $this->code_count ) {
				
				$find=array();$replace=array();
				foreach ( $this->code_text as $key_find => $key_replace ) {
					$find[] = $key_find;
					$replace[] = $key_replace;
				}
	
				$source = str_replace( $find, $replace, $source );

				$this->code_count = 0;
				$this->code_text = array ();
		
				if ( !$this->allow_code AND $this->edit_mode) {
					$source = preg_replace_callback( "#<pre><code>(.+?)</code></pre>#is", array( &$this, 'clear_p_tag'), $source );
				}
				
				$source = str_replace( "__CODENR__", "\r", $source );
				$source = str_replace( "__CODENN__", "\n", $source );

			}
			
			$this->image_count = 0;
		}
		return trim( $source );

	}

	function decodeBBCodes($txt, $use_html = true, $wysiwig = false) {
		global $config;

		$txt = (string)$txt;
		
		$txt = stripslashes( $txt );
		if( $this->filter_mode ) $txt = $this->word_filter( $txt, false );

		if (stripos($txt, "title_quote") !== false and $this->edit_mode) {
			$txt = preg_replace_callback("#<div class=['\"]title_quote['\"](.*?)>(.+?)</div>#i",  array(&$this, 'fix_quote_title'), $txt);
		}
		
		$txt = str_ireplace( "&#123;THEME}", "{THEME}", $txt );
		$txt = str_ireplace( "&#123;comments}", "{comments}", $txt );
		$txt = str_ireplace( "&#123;addcomments}", "{addcomments}", $txt );
		$txt = str_ireplace( "&#123;newsnavigation}", "{newsnavigation}", $txt );
		$txt = str_ireplace( "&#91;declination", "[declination", $txt );
		$txt = str_ireplace( "&#123;include", "{include", $txt );
		$txt = str_ireplace( "&#123;content", "{content", $txt );
		$txt = str_ireplace( "&#123;custom", "{custom", $txt );
		$txt = str_ireplace( "&#91;custom", "[custom", $txt );
		$txt = str_ireplace( "&#91;not-custom", "[not-custom", $txt );
		$txt = str_ireplace( "&#91;/custom", "[/custom", $txt );
		$txt = str_ireplace( "&#91;/not-custom", "[/not-custom", $txt );

		$txt = preg_replace_callback( "#<!--(TBegin|MBegin):(.+?)-->(.+?)<!--(TEnd|MEnd)-->#i", array( &$this, 'decode_thumb'), $txt );
		$txt = preg_replace_callback( "#<!--TBegin-->(.+?)<!--TEnd-->#i", array( &$this, 'decode_oldthumb'), $txt );
		$txt = preg_replace( "#<!--QuoteBegin-->(.+?)<!--QuoteEBegin-->#", '[quote]', $txt );
		$txt = preg_replace( "#<!--QuoteBegin ([^>]+?) -->(.+?)<!--QuoteEBegin-->#", "[quote=\\1]", $txt );
		$txt = preg_replace( "#<!--QuoteEnd-->(.+?)<!--QuoteEEnd-->#", '[/quote]', $txt );
		$txt = preg_replace_callback( "#<!--dle_leech_begin--><a href=\"(.+?)\"(.*?)>(.+?)</a><!--dle_leech_end-->#i", array( &$this, 'decode_leech'), $txt );
		$txt = preg_replace( "#<!--dle_video_begin-->(.+?)src=\"(.+?)\"(.+?)<!--dle_video_end-->#is", '[video=\\2]', $txt );
		$txt = preg_replace_callback( "#<!--dle_video_begin:(.+?)-->(.+?)<!--dle_video_end-->#is", array( &$this, 'decode_video'), $txt );
		$txt = preg_replace_callback( "#<!--dle_audio_begin:(.+?)-->(.+?)<!--dle_audio_end-->#is", array( &$this, 'decode_audio'), $txt );
		$txt = preg_replace_callback( "#<!--dle_image_begin:(.+?)-->(.+?)<!--dle_image_end-->#is", array( &$this, 'decode_dle_img'), $txt );
		$txt = preg_replace( "#<!--dle_youtube_begin:(.+?)-->(.+?)<!--dle_youtube_end-->#is", '[media=\\1]', $txt );
		$txt = preg_replace( "#<!--dle_media_begin:(.+?)-->(.+?)<!--dle_media_end-->#is", '[media=\\1]', $txt );
		$txt = preg_replace( "#<!--dle_spoiler-->(.+?)<!--spoiler_text-->#is", '[spoiler]', $txt );
		$txt = preg_replace_callback( "#<!--dle_spoiler (.+?) -->(.+?)<!--spoiler_text-->#is", array( &$this, 'decode_spoiler'), $txt );
		$txt = str_replace( "<!--spoiler_text_end--></div><!--/dle_spoiler-->", '[/spoiler]', $txt );
		
		if (!$config['emoji']) {
			$txt = preg_replace( "#<!--smile:(.+?)-->(.+?)<!--/smile-->#is", ':\\1:', $txt );
			$txt = preg_replace_callback( "#<a(.+?)>(.*?)</a>#is", array( &$this, 'remove_rel'), $txt );
		}

		if( !$use_html ) {
			$txt = str_ireplace( "<br>", "\n", $txt );
			$txt = str_ireplace( "<br />", "\n", $txt );
		}

		if ($this->edit_mode) {
			$txt = preg_replace('#<!--.*?-->#', '', $txt);
			$txt = str_replace('<pre><code>', '<pre class="language-markup"><code>', $txt);
		}

		if ((!$this->safe_mode OR $wysiwig) AND $this->edit_mode) {
			$txt = htmlspecialchars( $txt, ENT_QUOTES, 'UTF-8' );
		}
		
		$this->codes_param['html'] = $use_html;
		$this->codes_param['wysiwig'] = $wysiwig;

		if(!$this->safe_mode AND $this->edit_mode AND !$this->codes_param['wysiwig'] ) {
			$txt = str_replace( "&amp;amp;", "&amp;", $txt );
			$txt = str_replace( "__CODEAMP__", "&", $txt );
		}
		
		if ($this->safe_mode AND $this->edit_mode AND !$this->codes_param['wysiwig'] )	{
			$txt = str_replace( "__CODEAMP__", "&amp;", $txt );
		}

		return trim( $txt );

	}

	function build_media( $matches=array() ) {
		global $config;

		$url = $matches[1];

		$get_size = explode( ",", trim( $url ) );
		$sizes = array();
		$params = array();
		
		if (!count($this->video_config)) {

			include (ENGINE_DIR . '/data/videoconfig.php');
			$this->video_config = $video_config;

		}
		
		if (count($get_size) == 2)  {

			$url = $get_size[1];
			$sizes = explode( "x", trim( $get_size[0] ) );
			
			if( intval($sizes[0]) > 0 ) {
				$params['width'] = intval($sizes[0]);
			}
			
			if( intval($sizes[1]) > 0 ) {
				$params['height'] = intval($sizes[1]);
			}

		}

		$url = $this->clear_url( urldecode( $url ) );
		$url = str_replace("&amp;","&", $url );

		if( !$url ) {
			
			return $matches[0];
		
		}
		
		$decode_url = "";
		
		if( isset($params['width']) AND $params['width'] ) {
			$decode_url = $params['width'];
			
			if ( $params['height'] ) $decode_url .= "x".$params['height'];
			
			$decode_url .= ",".$url;
			
		} else {
			
			if (substr( trim($this->video_config['width']), - 1, 1 ) != '%') {
				$params['width'] = intval($this->video_config['width']);
			}
			
			$decode_url = $url;
		}
		$parsed_url = parse_url($url);

		if($parsed_url['host'] == 't.me') {
			$html = '<script async src="https://telegram.org/js/telegram-widget.js" data-telegram-post="'.$parsed_url['path'].'" data-width="'.$params['width'].'"></script>';
		} else {
			$html = $this->media_providers->getHTML($url, $params);	
		}

		if(!$html) {
			return $matches[0];
		}

		$this->found_media_content = true;

		return '<!--dle_media_begin:'.$decode_url.'-->'.$html.'<!--dle_media_end-->';

	}

	function build_url( $matches=array() ) {
		global $config, $member_id, $user_group;

		$url = array();

		if ($matches[1] == "leech" ) $url['leech'] = 1;

		$option=explode("|", $matches[2]);
		
		$url['html'] = isset($option[0]) ? $option[0] : '';
		$url['tooltip'] = isset($option[1]) ? $option[1] : '';
		$url['show'] = isset($matches[3]) ? $matches[3] : '';
		
		if ( !$url['show'] ) $url['show'] = $url['html'];

		if( preg_match( "/([\.,\?]|&#33;)$/", $url['show'], $match ) ) {
			$url['end'] = $match[1];
			$url['show'] = preg_replace( "/([\.,\?]|&#33;)$/", "", $url['show'] );
		} else $url['end'] = '';

		$url['html'] = $this->clear_url( $url['html'] );
		$url['show'] = stripslashes( $url['show'] );

		if( $this->safe_mode ) {

			$url['show'] = str_replace( "&nbsp;", " ", $url['show'] );

			if (strlen(trim($url['show'])) < 3 )
				return $matches[0];

		}

		if( stripos( $url['html'], $config['admin_path'] ) !== false ) {

			return $matches[0];

		}
		
		if (empty($url['html'])) {
			return $matches[0];
		}

		if( stripos( $url['html'], "engine/go.php" ) !== false OR ($this->check_home( $url['html'] ) AND stripos( $url['html'], "do=go" ) !== false) ) {
			return $matches[0];
		}

		if( !preg_match( "#^(http|https|ftp|nntp|news|viber|tg|skype|market)://#", $url['html'] ) AND !preg_match( "#^(tel):#", $url['html'] )  AND !preg_match( "#^(magnet):#", $url['html'] ) AND $url['html'][0] != "/" AND $url['html'][0] != "#") {
			$url['html'] = 'https://' . $url['html'];
		}

		if ( $url['html'] == 'http://' OR $url['html'] == 'https://' ) {
			return $matches[0];
		}

		$url['show'] = str_replace( "&amp;amp;", "&amp;", $url['show'] );

		if( $this->check_home( $url['html'] ) OR $url['html'][0] == "/" OR $url['html'][0] == "#") $target = "";
		else $target = " target=\"_blank\"";

		if( $url['tooltip'] ) {
			$url['tooltip'] = htmlspecialchars( strip_tags( stripslashes( $url['tooltip'] ) ), ENT_QUOTES, 'UTF-8' );
			$url['tooltip'] = str_replace( "&amp;amp;", "&amp;", $url['tooltip'] );
			$target = "title=\"".$url['tooltip']."\"".$target;
		}
		
		if( isset($url['leech'] ) AND $url['leech'] AND !$this->disable_leech) {

			$area = '';

			if( $this->safe_mode ) $area = '&area=comments';
			
			$url['html'] = $config['http_home_url'] . "index.php?do=go{$area}&url=" . rawurlencode( base64_encode( $url['html'] ) );

			return "<!--dle_leech_begin-->".$this->htmlparser->purify("<a href=\"" . $url['html'] . "\" " . $target . ">" . $url['show'] . "</a>")."<!--dle_leech_end-->" . $url['end'];

		} else {

			return $this->htmlparser->purify("<a href=\"" . $url['html'] . "\" " . $target . ">" . $url['show'] . "</a>") . $url['end'];

		}

	}
	
	function hide_code_tag( $matches=array() ) {
		$txt = $matches[1];

		if( !$txt ) {
			return $matches[0];
		}

		$this->code_count ++;

		$p = "<code>{" . $this->code_count . "}</code>";

		$this->code_text[$p] = "<code>{$txt}</code>";

		return $p;
	}


	function build_video( $matches=array() ) {

		$url = $matches[1];
		
		if (!count($this->video_config)) {

			include (ENGINE_DIR . '/data/videoconfig.php');
			$this->video_config = $video_config;

		}
		
		$get_videos = array();
		$sizes = array();
		$decode_url = array();
		$video_url = array();
		$video_option = array();
		$i = 0;
		
		$width = $this->video_config['width'];
		
		if( $this->video_config['preload'] ) $preload = "metadata"; else $preload = "none";

		$get_videos = explode( ",", trim( $url ) );

		foreach ($get_videos as $video) {
			$i++;
			
			if( $i == 1 AND count($get_videos) > 1 AND stripos ( $video, "http" ) === false AND intval($video) ) {
				
				$sizes = explode( "x", trim( $video ) );
				$width = intval($sizes[0]) > 0 ? intval($sizes[0]) : $this->video_config['width'];
				
				if (substr( $sizes[0], - 1, 1 ) == '%') $width = $width."%";
				
				$decode_url[] = $width;
				continue;
			
			}
		
			$video = str_replace( "%20", " ", trim( $video ) );
			
			$video_option = explode( "|", trim( $video ) );
		
			$video_option[0] = $this->clear_url( trim($video_option[0]) );
			
			if( !$video_option[0] ) continue;
			
			if(isset($video_option[1]) AND $video_option[1]) {
				$video_option[1] = $this->clear_url( trim($video_option[1]) );
				$preview = " poster=\"{$video_option[1]}\" ";
			} else { $preview = ""; }
			
			if(isset($video_option[2]) AND $video_option[2]) {
				$video_option[2] = htmlspecialchars( strip_tags( stripslashes( trim($video_option[2]) ) ), ENT_QUOTES, 'UTF-8' );
				$video_option[2] = str_replace("&amp;amp;","&amp;", $video_option[2]);
			}
			
			
			$decode_url[] = implode("|", $video_option);
			
			if( empty($video_option[2]) ) $video_option[2] = str_replace( "%20", " ", pathinfo( $video_option[0], PATHINFO_FILENAME ) );
			
			$type="type=\"video/mp4\"";
			
			if (strpos ( $video_option[0], "youtube.com" ) !== false) { $type="provider=\"youtube\""; $preload = "metadata"; }

			$video_url[] = "<video title=\"{$video_option[2]}\" preload=\"{$preload}\" controls{$preview}><source {$type} src=\"{$video_option[0]}\"></video>";
			
		}
		
		if( count($video_url) ){
			$video_url = implode($video_url);
			$decode_url = implode(",",$decode_url);
		} else {
			return $matches[0];
		}
		
		if (substr( $width, - 1, 1 ) != '%') $width = $width."px";

		$width = "style=\"width:100%;max-width:{$width};\"";
		
		$this->found_media_content = true;
		
		return "<!--dle_video_begin:{$decode_url}--><div class=\"dleplyrplayer\" {$width} theme=\"{$this->video_config['theme']}\">{$video_url}</div><!--dle_video_end-->";

	}
	
	function build_audio( $matches=array() ) {

		$url = $matches[1];

		if( $url == "" ) return;

		if (!count($this->video_config)) {

			include (ENGINE_DIR . '/data/videoconfig.php');
			$this->video_config = $video_config;

		}

		$get_audios = array();
		$sizes = array();
		$decode_url = array();
		$audio_url = array();
		$audio_option = array();
		$i = 0;
		
		$width = $this->video_config['audio_width'];
		
		if( $this->video_config['preload'] ) $preload = "metadata"; else $preload = "none";

		$get_audios = explode( ",", trim( $url ) );

		foreach ($get_audios as $audio) {
			$i++;
			
			if( $i == 1 AND count($get_audios) > 1 AND stripos ( $audio, "http" ) === false AND intval($audio)) {
				
				$sizes = explode( "x", trim( $audio ) );
				$width = intval($sizes[0]) > 0 ? intval($sizes[0]) : $this->video_config['audio_width'];
				
				if (substr( $sizes[0], - 1, 1 ) == '%') $width = $width."%";
				
				$decode_url[] = $width;
				continue;
			
			}
			
			$audio = str_replace( "%20", " ", trim( $audio ) );
			
			$audio_option = explode( "|", trim( $audio ) );
			
			$audio_option[0] = $this->clear_url( trim($audio_option[0]) );

			$file_type = explode(".", $audio_option[0]);
			$file_type = totranslit(end($file_type));
			
			if( !$audio_option[0] ) continue;
			
			if(isset($audio_option[1]) AND $audio_option[1]) $audio_option[1] = htmlspecialchars( strip_tags( stripslashes( trim($audio_option[1]) ) ), ENT_QUOTES, 'UTF-8' );
			
			$decode_url[] = implode("|", $audio_option);
			
			if( !isset($audio_option[1]) OR (isset($audio_option[1]) AND !$audio_option[1]) ) $audio_option[1] = str_replace( "%20", " ", pathinfo( $audio_option[0], PATHINFO_FILENAME ));
			
			$audio_url[] = "<audio title=\"{$audio_option[1]}\" preload=\"{$preload}\" controls><source type=\"audio/{$file_type}\" src=\"{$audio_option[0]}\"></audio>";
			
		}
		
		if( count($audio_url) ){
			$audio_url = implode($audio_url);
			$decode_url = implode(",",$decode_url);
		} else {
			return $matches[0];
		}
		
		if (substr( $width, - 1, 1 ) != '%') $width = $width."px";

		if( $width ) $width = "style=\"width:100%;max-width:{$width};\""; 

		$this->found_media_content = true;

		return "<!--dle_audio_begin:{$decode_url}--><div class=\"dleplyrplayer\" {$width} theme=\"{$this->video_config['theme']}\">{$audio_url}</div><!--dle_audio_end-->";	


	}
	
	function decode_video( $matches=array() ) {
		$url = 	$matches[1];
		
		$url = str_replace("&amp;","&", $url );
		$url = str_replace("&quot;",'"', $url );
		$url = str_replace("&#039;","'", $url );
		
		return '[video='.$url.']';
	}
	

	function decode_audio( $matches=array() ) {
		$url = 	$matches[1];
		
		$url = str_replace("&amp;","&", $url );
		$url = str_replace("&quot;",'"', $url );
		$url = str_replace("&#039;","'", $url );
		
		return '[audio='.$url.']';
	}
	
	function build_image( $matches=array() ) {
		global $config;

		if(count($matches) == 2 ) {

			$align = "";
			$url = $matches[1];

		} else {
			$align = $matches[1];
			$url = $matches[2];
		}

		$url = trim( $url );
		$option = explode( "|", trim( $align ) );
		$align = $option[0];

		if( $align != "left" and $align != "right" ) $align = '';

		$url = $this->clear_url( urldecode( $url ) );
		
		if( preg_match( "/[?&;<\[\]]/", $url ) ) {

			return $matches[0];

		}

		$info = $url;

		$info = $info."|".$align;

		if( $url == "" ) return $matches[0];

		$this->image_count ++;

		if( isset($option[1]) AND $option[1] ) {

			$alt = htmlspecialchars( strip_tags( stripslashes( $option[1] ) ), ENT_QUOTES, 'UTF-8' );
			$alt = str_replace("&amp;amp;","&amp;",$alt);
			
			$info = $info."|".$alt;
			$alt = "alt=\"" . $alt . "\"";

		} else {
			
			if($this->image_count == 1) {
				
				$alt = htmlspecialchars( strip_tags( stripslashes( $_POST['title'] ) ), ENT_QUOTES, 'UTF-8' );
				$alt = str_replace("&amp;amp;","&amp;",$alt);
				
			} else { $alt = ""; }
			
			$alt = "alt=\"" . $alt . "\"";

		}

		if ( $align ) {
			
			$style="style=\"float:{$align};max-width:100%;\"";
			
		} else $style="style=\"max-width:100%;\"";
		
		if( intval( $config['tag_img_width'] ) ) {

			if (clean_url( $config['http_home_url'] ) != clean_url ( $url ) ) {
				
				$style .= " data-maxwidth=\"".intval($config['tag_img_width'])."\"";
				
			}
			
		}

		return "<!--dle_image_begin:{$info}-->".$this->htmlparser->purify("<img src=\"{$url}\" {$style} {$alt}>")."<!--dle_image_end-->";

	}

	function decode_dle_img( $matches=array() ) {

		$txt = $matches[1];
		$txt = explode("|", $txt );
		$url = isset($txt[0]) ? $txt[0] : '';
		$align = isset($txt[1]) ? $txt[1] : '';
		$alt = isset($txt[2]) ? $txt[2] : '';
		$extra = "";

		if( !$align and !$alt ) return "[img]" . $url . "[/img]";

		if( $align ) $extra = $align;

		if( $alt ) {

			$alt = str_replace("&#039;", "'", $alt);
			$alt = str_replace("&quot;", '"', $alt);
			$alt = str_replace("&amp;", '&', $alt);
			$extra .= "|" . $alt;

		}

		return "[img=" . $extra . "]" . $url . "[/img]";

	}

	function clear_p_tag( $matches=array() ) {

		$txt = $matches[1];

		$txt = str_replace("\r", "", $txt);
		$txt = str_replace("\n", "", $txt);

		$txt = preg_replace('/<p[^>]*>/', '', $txt);
		$txt = str_replace("</p>", "\n", $txt);
		$txt = preg_replace('/<div[^>]*>/', '', $txt);
		$txt = str_replace("</div>", "\n", $txt);
		$txt = preg_replace('/<br[^>]*>/', "\n", $txt);

		return "<pre><code>".$txt."</code></pre>";

	}

	function clear_div_tag( $matches=array() ) {

		$spoiler = array();

		if ( count($matches) == 3 ) {
			$spoiler['title'] = '';
			$spoiler['txt'] = $matches[2];
		} else {
			$spoiler['title'] = $matches[2];
			$spoiler['txt'] = $matches[3];
		}

		$tag = $matches[1];

		$spoiler['txt'] = preg_replace('/<div[^>]*>/', '', $spoiler['txt']);
		$spoiler['txt'] = str_replace("</div>", "<br>", $spoiler['txt']);

		if ($spoiler['title'])
			return "[{$tag}={$spoiler['title']}]".$spoiler['txt']."[/{$tag}]";
		else
			return "[{$tag}]".$spoiler['txt']."[/{$tag}]";

	}

	function build_thumb( $matches=array() ) {

		if (count($matches) == 2 ) {
			$align = "";
			$gurl = $matches[1];
		} else {
			$align = $matches[1];
			$gurl = $matches[2];
		}

		$gurl = $this->clear_url( urldecode( $gurl ) );
		
		if( preg_match( "/[?&;%<\[\]]/", $gurl ) ) {

			return $matches[0];

		}
		
		$url = preg_replace( "'([^\[]*)([/\\\\])(.*?)'i", "\\1\\2thumbs\\2\\3", $gurl );

		$url = trim( $url );
		$gurl = trim( $gurl );
		$option = explode( "|", trim( $align ) );

		$align = $option[0];

		if( $align != "left" and $align != "right" ) $align = '';

		$url = $this->clear_url( urldecode( $url ) );

		$info = $gurl;
		$info = $info."|".$align;

		if( $gurl == "" or $url == "" ) return $matches[0];

		if( isset($option[1]) AND $option[1] ) {

			$alt = htmlspecialchars( strip_tags( stripslashes( $option[1] ) ), ENT_QUOTES, 'UTF-8' );

			$alt = str_replace("&amp;amp;","&amp;",$alt);

			$info = $info."|".$alt;
			$alt = "alt=\"" . $alt . "\"";

		} else {

			$alt = "alt=''";

		}

		if( $align == '' ) return "<!--TBegin:{$info}-->".$this->htmlparser->purify("<a href=\"$gurl\" class=\"highslide\" target=\"_blank\"><img src=\"$url\" style=\"max-width:100%;\" {$alt}></a>")."<!--TEnd-->";
		else return "<!--TBegin:{$info}-->".$this->htmlparser->purify("<a href=\"$gurl\" class=\"highslide\" target=\"_blank\"><img src=\"$url\" style=\"float:{$align};max-width:100%;\" {$alt}></a>")."<!--TEnd-->";

	}


	function build_medium( $matches=array() ) {

		if (count($matches) == 2 ) {
			$align = "";
			$gurl = $matches[1];
		} else {
			$align = $matches[1];
			$gurl = $matches[2];
		}

		$gurl = $this->clear_url( urldecode( $gurl ) );
		
		if( preg_match( "/[?&;%<\[\]]/", $gurl ) ) {

			return $matches[0];

		}
		
		$url = preg_replace( "'([^\[]*)([/\\\\])(.*?)'i", "\\1\\2medium\\2\\3", $gurl );

		$url = trim( $url );
		$gurl = trim( $gurl );
		$option = explode( "|", trim( $align ) );

		$align = $option[0];

		if( $align != "left" and $align != "right" ) $align = '';

		$url = $this->clear_url( urldecode( $url ) );

		$info = $gurl;
		$info = $info."|".$align;

		if( $gurl == "" or $url == "" ) return $matches[0];

		if( isset($option[1]) AND $option[1] ) {

			$alt = htmlspecialchars( strip_tags( stripslashes( $option[1] ) ), ENT_QUOTES, 'UTF-8' );

			$alt = str_replace("&amp;amp;","&amp;",$alt);

			$info = $info."|".$alt;
			$alt = "alt=\"" . $alt . "\"";

		} else {

			$alt = "alt=''";

		}

		if( $align == '' ) return "<!--MBegin:{$info}-->".$this->htmlparser->purify("<a href=\"$gurl\" class=\"highslide\"><img src=\"$url\" style=\"max-width:100%;\" {$alt}></a>")."<!--MEnd-->";
		else return "<!--MBegin:{$info}-->".$this->htmlparser->purify("<a href=\"$gurl\" class=\"highslide\"><img src=\"$url\" style=\"float:{$align};max-width:100%;\" {$alt}></a>")."<!--MEnd-->";
		
	}

	function build_spoiler( $matches=array() ) {
		global $lang;

		
		if (count($matches) == 3 ) {
			
			$title = $matches[1];

			$title = htmlspecialchars( strip_tags( stripslashes( trim($title) ) ), ENT_QUOTES, 'UTF-8' );
	
			$title = str_replace( "&amp;amp;", "&amp;", $title );
			$title = preg_replace( "/javascript:/i", "j&#1072;vascript&#58; ", $title );
			
		} else $title = false;
		
		$id_spoiler = "sp".md5( microtime().uniqid( mt_rand(), TRUE ) );
		
		if( !$title ) {

			return "<!--dle_spoiler--><div class=\"title_spoiler\"><a href=\"javascript:ShowOrHide('" . $id_spoiler . "')\"><svg width=\"18\" height=\"18\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path id=\"svg-" . $id_spoiler . "\" d=\"M17.418 6.109c0.272-0.268 0.709-0.268 0.979 0s0.271 0.701 0 0.969l-7.908 7.83c-0.27 0.268-0.707 0.268-0.979 0l-7.908-7.83c-0.27-0.268-0.27-0.701 0-0.969s0.709-0.268 0.979 0l7.419 7.141 7.418-7.141z\"></path></svg></a><a href=\"javascript:ShowOrHide('" . $id_spoiler . "')\"><!--spoiler_title-->" . $lang['spoiler_title'] . "<!--spoiler_title_end--></a></div><div id=\"" . $id_spoiler . "\" class=\"text_spoiler\" style=\"display:none;\"><!--spoiler_text-->{$matches[1]}<!--spoiler_text_end--></div><!--/dle_spoiler-->";

		} else {

			return "<!--dle_spoiler $title --><div class=\"title_spoiler\"><a href=\"javascript:ShowOrHide('" . $id_spoiler . "')\"><svg width=\"18\" height=\"18\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path id=\"svg-" . $id_spoiler . "\" d=\"M17.418 6.109c0.272-0.268 0.709-0.268 0.979 0s0.271 0.701 0 0.969l-7.908 7.83c-0.27 0.268-0.707 0.268-0.979 0l-7.908-7.83c-0.27-0.268-0.27-0.701 0-0.969s0.709-0.268 0.979 0l7.419 7.141 7.418-7.141z\"></path></svg></a><a href=\"javascript:ShowOrHide('" . $id_spoiler . "')\"><!--spoiler_title-->" . $title . "<!--spoiler_title_end--></a></div><div id=\"" . $id_spoiler . "\" class=\"text_spoiler\" style=\"display:none;\"><!--spoiler_text-->{$matches[2]}<!--spoiler_text_end--></div><!--/dle_spoiler-->";

		}

	}
	
	function decode_spoiler( $matches=array() ) {
		$url = 	$matches[1];
		
		$url = str_replace("&amp;","&", $url );
		$url = str_replace("&quot;",'"', $url );
		$url = str_replace("&#039;","'", $url );
		
		return '[spoiler='.$url.']';
	}
	
	function clear_url($url) {

		$url = strip_tags( trim( stripslashes( html_entity_decode($url, ENT_QUOTES, 'UTF-8') ) ) );

		$url = str_replace( '\"', '"', $url );
		$url = str_replace( "'", "", $url );
		$url = str_replace( '"', "", $url );
		$url = str_replace( "&#111;", "o", $url );
		$url = preg_replace( "/j&#1072;vascript(.*?):/i", "javascript:", $url );
		$url = preg_replace( "/d&#1072;ta(.*?):/i", "data:", $url );
		$url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		
		$url_array = parse_url($url);

		if ( isset($url_array['scheme']) AND $url_array['scheme'] AND !in_array( $url_array['scheme'], array("http","https","mailto","ftp","nntp","news","tel","magnet","viber","tg","skype","market")) ) {

			return '';
		}
		
		if( stripos( $url, "engine/go.php" ) !== false OR ($this->check_home( $url ) AND stripos( $url, "do=go" ) !== false) ) {
			return '';
		}
		
		$url = str_replace( "&amp;amp;", "&amp;", $url );
		
		$url = str_ireplace( "document.cookie", "d&#111;cument.cookie", $url );
		$url = str_replace( " ", "%20", $url );
		$url = str_replace( "<", "&#60;", $url );
		$url = str_replace( ">", "&#62;", $url );
		$url = str_replace(array("{", "}", "[", "]"),array("%7B", "%7D", "%5B", "%5D"), $url);
		$url = preg_replace( "/javascript:/i", "j&#1072;vascript:", $url );
		$url = preg_replace( "/data:/i", "d&#1072;ta:", $url );
		
		return $url;

	}

	function decode_leech( $matches=array() ) {
		
		$url = 	$matches[1];
		$show = $matches[3];

		if( $this->leech_mode ) return "[url=" . $url . "]" . $show . "[/url]";
		
		$url = str_replace("&amp;","&", $url );
		$url = parse_url($url, PHP_URL_QUERY);
		parse_str($url, $url_parts);
		$url = $url_parts['url'];
		
		$url = rawurldecode( $url );
		$url = base64_decode( $url );

		$charset = $this->detect_encoding($url);

		if($charset AND $charset != 'utf-8' ) {
			
			if( function_exists( 'mb_convert_encoding' ) ) {
		
				$url = mb_convert_encoding( $url, 'UTF-8', $charset );
		
			} elseif( function_exists( 'iconv' ) ) {
			
				$url = iconv($charset, 'UTF-8', $url);
			
			}
			
		}

		$url = strip_tags( $url );
		$url = str_replace("&amp;","&", $url );

		if( preg_match( "#title=['\"](.+?)['\"]#i", $matches[2], $match ) ) {
			$match[1] = str_replace("&quot;", '"', $match[1]);
			$match[1] = str_replace("&#039;", "'", $match[1]);
			$match[1] = str_replace("&amp;", "&", $match[1]);
			$match[1] = strip_tags( $match[1] );
			$url = $url."|".$match[1];
		}
		
		return "[leech=" . $url . "]" . $show . "[/leech]";
	}
	
	function detect_encoding($string) {  
	  static $list = array('utf-8', 'windows-1251');
	   
	  foreach ($list as $item) {
	
		if( function_exists( 'mb_convert_encoding' ) ) {
	
			$sample = mb_convert_encoding( $string, $item, $item );
	
		} elseif( function_exists( 'iconv' ) ) {
		
			$sample = iconv($item, $item, $string);
		
		}
	
		if (md5($sample) == md5($string)) return $item;
	
	   }
	
	   return null;
	}
	
	function decode_url( $matches=array() ) {

		$show =  $matches[3];
		$url = $matches[1];
		$params = trim($matches[2]);

		if( preg_match( "#title=[\"](.+?)[\"]#i", $params, $match ) ) {
			$match[1] = str_replace("&quot;", '"', $match[1]);
			$match[1] = str_replace("&#039;", "'", $match[1]);
			$match[1] = str_replace("&amp;", "&", $match[1]);
			$url = $url."|".$match[1];
			$params = trim(str_replace($match[0], "", $params));
		}
		
		if( preg_match( "#rel=[\"](.+?)[\"]#i", $params, $match ) ) {
			$params = trim(str_replace($match[0], "", $params));
		}

		if (!$params OR $params == 'target="_blank"') {

			$url = str_replace("&amp;","&", $url );

			return "[url=" . $url . "]" . $show . "[/url]";

 		} else {

			return $matches[0];

		}
	}
	
	function clear_img( $matches=array() ) {
		
		$params = trim( stripslashes($matches[1]) );
		
		if( preg_match( "#src=['\"](.+?)['\"]#i", $params, $match ) ) {
			if( preg_match( "/[?&;<]/", $match[1]) ) return "";
		}
		
		return $matches[0];
	}
	
	function remove_bad_url( $matches=array() ) {
		global $config;
		
		$params = trim( stripslashes($matches[1]) );
		
		if( preg_match( "#href=['\"](.+?)['\"]#i", $params, $match ) ) {
			
			if( stripos( $match[1], "engine/go.php" ) !== false OR ($this->check_home( $match[1] ) AND stripos( $match[1], "do=go" ) !== false) ) {
				return '';
			}
			
			if( stripos( $match[1], $config['admin_path'] ) !== false ) {
	
				return '';
	
			}
			
		}
		
		return $matches[0];
	}
	
	function fix_p_in_div ( $matches=array() ) {

		if( stripos( $matches[2], "<div" ) !== false AND ( stripos( $matches[2], "dle_media_begin" ) !== false OR stripos( $matches[2], "dle_video_begin" ) !== false OR stripos( $matches[2], "dle_audio_begin" ) !== false ) ) {
			return "<div{$matches[1]}>{$matches[2]}</div>";
		} else return $matches[0];

	}
	
	function add_rel( $matches=array() ) {
		global $config;
		
		$matches = array_map('stripslashes', $matches);

		$params = trim( $matches[1] );

		if( preg_match( "#href=['\"](.+?)['\"]#i", $params, $match ) ) {
			
			if( $this->check_home($match[1]) ) {

				if( preg_match( "#rel=['\"](.+?)['\"]#i", $params, $match ) ) {
					
					$remove_params = array("external", "noopener", "noreferrer");

					$new_params = array();
					
					$exist_params = explode(" ", trim($match[1]) );
					
					foreach ($exist_params as $value) {
						if(!in_array( $value, $remove_params ) ) $new_params[] = $value;
					}
					
					if( count($new_params) ) {
						
						$new_params = implode(" ", $new_params);
						$params = str_ireplace($match[0], "rel=\"{$new_params}\"", $params);
						
					} else $params = str_ireplace($match[0], "", $params);
					
					$params = trim($params);
					
					return "<a {$params}>";
				
				} else {
					
					return $matches[0];
					
				}

			}
			
		} else return $matches[0];
		
		$new_params = array("external", "noopener");
			
		if ( $this->safe_mode AND !$config['allow_search_link'] ) {
			
			$new_params[] = "nofollow";
			
		}
		
		if( ($this->safe_mode AND $config['comm_noreferrer']) OR (!$this->safe_mode AND $config['news_noreferrer']) ) {
			
			$new_params[] = "noreferrer";
			
		}
		
		if( preg_match( "#rel=['\"](.+?)['\"]#i", $params, $match ) ) {
			
			$exist_params = trim(preg_replace('/\s+/', ' ', $match[1]));
			
			$exist_params = explode(" ", $exist_params);
			
			foreach ($new_params as $value) {
				if(!in_array( $value, $exist_params ) ) $exist_params[] = $value;
			}
			
			$exist_params = implode(" ", $exist_params);

			$params = str_ireplace($match[0], "rel=\"{$exist_params}\"", $params);

		} else {
			
			$params .= " rel=\"".implode(" ", $new_params)."\"";
			
		}
		
		$params = addslashes( $params );

		return "<a {$params}>";
		
	}
	
	function remove_rel( $matches=array() ) {
		
		global $config;
		
		$params = trim( $matches[1] );
		
		if( preg_match( "#rel=['\"](.+?)['\"]#i", $params, $match ) ) {
			
			$remove_params = array("external", "noopener", "noreferrer");
			
			if ( $this->safe_mode AND !$config['allow_search_link'] ) {
				
				$remove_params[] = "nofollow";
				
			}
		
			$new_params = array();
			
			$exist_params = explode(" ", trim($match[1]) );
			
			foreach ($exist_params as $value) {
				if(!in_array( $value, $remove_params ) ) $new_params[] = $value;
			}
			
			if( count($new_params) ) {
				
				$new_params = implode(" ", $new_params);
				$params = str_ireplace($match[0], "rel=\"{$new_params}\"", $params);
				
			} else $params = str_ireplace($match[0], "", $params);
			
			$params = trim($params);
			
			return "<a {$params}>{$matches[2]}</a>";
		
		} else {
			
			return $matches[0];
			
		}
		
	}
	
	function decode_thumb( $matches=array() ) {

		if ($matches[1] == "TBegin") $tag="thumb"; else $tag="medium";
		$txt = $matches[2];

		$txt = stripslashes( $txt );
		$txt = explode("|", $txt );
		$url = $txt[0];
		
		$align = isset($txt[1]) ? $txt[1] : '';
		$alt   = isset($txt[2]) ? $txt[2] : '';
		
		$extra = "";

		if( !$align and !$alt ) return "[{$tag}]{$url}[/{$tag}]";

		if( $align ) $extra = $align;
		if( $alt ) {

			$alt = str_replace("&#039;", "'", $alt);
			$alt = str_replace("&quot;", '"', $alt);
			$alt = str_replace("&amp;", '&', $alt);
			$extra .= "|" . $alt;

		}

		return "[{$tag}={$extra}]{$url}[/{$tag}]";

	}

	function decode_oldthumb( $matches=array() ) {

		$txt = $matches[1];

		$align = false;
		$alt = false;
		$extra = "";
		$txt = stripslashes( $txt );

		$url = str_replace( "<a href=\"", "", $txt );
		$url = explode( "\"", $url );
		$url = reset( $url );

		if( strpos( $txt, "align=\"" ) !== false ) {

			$align = preg_replace( "#(.+?)align=\"(.+?)\"(.*)#is", "\\2", $txt );
		}

		if( strpos( $txt, "alt=\"" ) !== false ) {

			$alt = preg_replace( "#(.+?)alt=\"(.+?)\"(.*)#is", "\\2", $txt );
		}

		if( $align != "left" and $align != "right" ) $align = false;

		if( ! $align and ! $alt ) return "[thumb]" . $url . "[/thumb]";

		if( $align ) $extra = $align;
		if( $alt ) {
			$alt = str_replace("&#039;", "'", $alt);
			$alt = str_replace("&quot;", '"', $alt);
			$alt = str_replace("&amp;", '&', $alt);
			$extra .= "|" . $alt;

		}

		return "[thumb=" . $extra . "]" . $url . "[/thumb]";

	}

	function decode_img( $matches=array() ) {

		$img = $matches[1];
		$txt = $matches[2];
		$align = false;
		$alt = false;
		$extra = "";

		if( strpos( $txt, "align=\"" ) !== false ) {

			$align = preg_replace( "#(.+?)align=\"(.+?)\"(.*)#is", "\\2", $txt );
		}

		if( strpos( $txt, "alt=\"\"" ) !== false ) {

			$alt = false;

		} elseif( strpos( $txt, "alt=\"" ) !== false ) {

			$alt = preg_replace( "#(.+?)alt=\"(.+?)\"(.*)#is", "\\2", $txt );
		}

		if( $align != "left" and $align != "right" ) $align = false;

		if( ! $align and ! $alt ) return "[img]" . $img . "[/img]";

		if( $align ) $extra = $align;
		if( $alt ) $extra .= "|" . $alt;

		return "[img=" . $extra . "]" . $img . "[/img]";

	}

	function check_home($url) {
		global $config;

		$url = strtolower( (string)parse_url( (string)$url, PHP_URL_HOST));
		$value = strtolower( (string)parse_url($config['http_home_url'], PHP_URL_HOST));

		if( !$value ) $value = $_SERVER['HTTP_HOST'];

		if( !$url ) return true;
		
		if( $url != $value ) return false;
		else return true;
	}

	function word_filter($source, $encode = true) {
		global $config;

		if( $encode ) {

			$all_words = @file( ENGINE_DIR . '/data/wordfilter.db.php' );
			$find = array ();
			$replace = array ();

			if( ! $all_words or ! count( $all_words ) ) return $source;

			foreach ( $all_words as $word_line ) {
				$word_arr = explode( "|", $word_line );
				
				$word_arr[1] = str_replace( "&#036;", "$", $word_arr[1] );
				$word_arr[1] = str_replace( "&#123;", "{", $word_arr[1] );
				$word_arr[1] = str_replace( "&#125;", "}", $word_arr[1] );
			
				if( $word_arr[4] ) {

					$register ="";

				} else $register ="i";

				$register .= "u";

				$allow_find = true;

				if ( $word_arr[5] == 1 AND $this->safe_mode ) $allow_find = false;
				if ( $word_arr[5] == 2 AND !$this->safe_mode ) $allow_find = false;

				if ( $allow_find ) {

					if( $word_arr[3] ) {

						$find_text = "#(^|\b|\s|\<br\>|\<br \/\>)" . preg_quote( addslashes( str_replace("&", "&amp;", $word_arr[1])), "#" ) . "(\b|\s|!|\?|\.|,|$)#".$register;

						if( $word_arr[2] == "" ) $replace_text = "\\1";
						else $replace_text = "\\1<!--filter:" . $word_arr[1] . "-->" . $word_arr[2] . "<!--/filter-->\\2";

					} else {

						$find_text = "#(" . preg_quote( addslashes( str_replace("&", "&amp;",$word_arr[1])), "#" ) . ")#".$register;

						if( $word_arr[2] == "" ) $replace_text = "";
						else $replace_text = "<!--filter:" . $word_arr[1] . "-->" . $word_arr[2] . "<!--/filter-->";

					}

					if ( $word_arr[6] ) {

						if ( preg_match($find_text, $source) ) {

							$this->not_allowed_text = true;
							return $source;

						}

					} else {

						$find[] = $find_text;
						$replace[] = $replace_text;
					}

				}

			}

			if( !count( $find ) ) return $source;

			$source = preg_split( '((>)|(<))', $source, - 1, PREG_SPLIT_DELIM_CAPTURE );
			$count = count( $source );

			for($i = 0; $i < $count; $i ++) {
				if( $source[$i] == "<" or $source[$i] == "[" ) {
					$i ++;
					continue;
				}

				if( $source[$i] != "" ) $source[$i] = preg_replace( $find, $replace, $source[$i] );
			}

			$source = join( "", $source );

		} else {

			$source = preg_replace( "#<!--filter:(.+?)-->(.+?)<!--/filter-->#", "\\1", $source );

		}

		return $source;
	}
	function isTimestamp($string)
	{
		try {
			new DateTime('@' . $string);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	function fix_quote_title($matches = array()) {
		global $config, $lang;

		$return_string = '<div class="title_quote"';
		$title_text = '';

		if (preg_match("#data-commenttime=['\"](.+?)['\"]#i", $matches[1], $match)) {

			$time = intval($match[1]);

			if ($this->isTimestamp($time)) {
				$return_string .= " data-commenttime=\"{$time}\"";
				$title_text .= difflangdate($config['timestamp_comment'], $time) . ', ';
			}
		}

		if (preg_match("#data-commentuser=['\"](.+?)['\"]#i", $matches[1], $match)) {

			$author = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$author = htmlspecialchars($author, ENT_COMPAT | ENT_HTML5, 'UTF-8');

			if ($author) {
				$return_string .= " data-commentuser=\"{$author}\"";
				$title_text .= $author . ' ' . $lang['user_says'];
			}
		}

		if (preg_match("#data-commentid=['\"](.+?)['\"]#i", $matches[1], $match)) {

			$commentid = intval($match[1]);

			$return_string .= " data-commentid=\"{$commentid}\"";
		}

		if (preg_match("#data-commentpostid=['\"](.+?)['\"]#i", $matches[1], $match)) {

			$commentpostid = intval($match[1]);

			$return_string .= " data-commentpostid=\"{$commentpostid}\"";
		}

		if (preg_match("#data-commentgast=['\"](.+?)['\"]#i", $matches[1], $match)) {

			$commentgast = intval($match[1]);

			$return_string .= " data-commentgast=\"{$commentgast}\"";
		}

		$return_string .= '>';

		if ($title_text) $return_string .= $title_text;
		else $return_string .= $matches[2];

		$return_string .= '</div>';

		return $return_string;
	}	
}

class OEmbed {

	protected $providers = array();
	private $facebook_app_id = "";
	private $facebook_app_secret = "";

	function __construct(){

		$this->providers['%^http:\/\/www.23hq.com\/.*\/photo\/.*%i'] = "http://www.23hq.com/23/oembed"; 
		$this->providers['%^https:\/\/store.abraia.me\/.*%i'] = "https://api.abraia.me/oembed"; 
		$this->providers['%^http:\/\/play.adpaths.com\/experience\/.*%i'] = "http://play.adpaths.com/oembed/*"; 
		$this->providers['%^https:\/\/alpha.app.net\/.*\/post\/.*%i'] = "https://alpha-api.app.net/oembed"; 
		$this->providers['%^https:\/\/photos.app.net\/.*\/.*%i'] = "https://alpha-api.app.net/oembed"; 
		$this->providers['%^https:\/\/app.altrulabs.com\/.*\/.*?answer_id=.*%i'] = "https://api.altrulabs.com/api/v1/social/oembed"; 
		$this->providers['%^https:\/\/app.altrulabs.com\/player\/.*%i'] = "https://api.altrulabs.com/api/v1/social/oembed"; 
		$this->providers['%^http:\/\/live.amcharts.com\/.*%i'] = "https://live.amcharts.com/oembed"; 
		$this->providers['%^https:\/\/live.amcharts.com\/.*%i'] = "https://live.amcharts.com/oembed"; 
		$this->providers['%^https:\/\/www.animatron.com\/project\/.*%i'] = "https://animatron.com/oembed/json"; 
		$this->providers['%^https:\/\/animatron.com\/project\/.*%i'] = "https://animatron.com/oembed/json"; 
		$this->providers['%^http:\/\/animoto.com\/play\/.*%i'] = "http://animoto.com/oembeds/create"; 
		$this->providers['%^https:\/\/renderer.apester.com\/v2\/.*?preview=true&iframe_preview=true%i'] = "https://display.apester.com/oembed"; 
		$this->providers['%^https:\/\/storymaps.arcgis.com\/stories\/.*%i'] = "https://storymaps.arcgis.com/oembed"; 
		$this->providers['%^https:\/\/app.archivos.digital\/app\/view\/.*%i'] = "https://app.archivos.digital/oembed/"; 
		$this->providers['%^https:\/\/audioboom.com\/channels\/.*%i'] = "https://audioboom.com/publishing/oembed/v4.json"; 
		$this->providers['%^https:\/\/audioboom.com\/channel\/.*%i'] = "https://audioboom.com/publishing/oembed/v4.json"; 
		$this->providers['%^https:\/\/audioboom.com\/posts\/.*%i'] = "https://audioboom.com/publishing/oembed/v4.json"; 
		$this->providers['%^https:\/\/audioclip.naver.com\/channels\/.*\/clips\/.*%i'] = "https://audioclip.naver.com/oembed"; 
		$this->providers['%^https:\/\/audioclip.naver.com\/audiobooks\/.*%i'] = "https://audioclip.naver.com/oembed"; 
		$this->providers['%^https:\/\/audiomack.com\/.*\/song\/.*%i'] = "https://audiomack.com/oembed"; 
		$this->providers['%^https:\/\/audiomack.com\/.*\/album\/.*%i'] = "https://audiomack.com/oembed"; 
		$this->providers['%^https:\/\/audiomack.com\/.*\/playlist\/.*%i'] = "https://audiomack.com/oembed"; 
		$this->providers['%^https:\/\/app.avocode.com\/view\/.*%i'] = "https://stage-embed.avocode.com/api/oembed"; 
		$this->providers['%^http:\/\/axiom.ninja\/.*%i'] = "http://axiom.ninja/oembed/"; 
		$this->providers['%^https:\/\/backtracks.fm\/.*\/.*\/e\/.*%i'] = "https://backtracks.fm/oembed"; 
		$this->providers['%^https:\/\/backtracks.fm\/.*\/s\/.*\/.*%i'] = "https://backtracks.fm/oembed"; 
		$this->providers['%^https:\/\/backtracks.fm\/.*\/.*\/.*\/.*\/e\/.*\/.*%i'] = "https://backtracks.fm/oembed"; 
		$this->providers['%^https:\/\/backtracks.fm\/.*%i'] = "https://backtracks.fm/oembed"; 
		$this->providers['%^http:\/\/backtracks.fm\/.*%i'] = "https://backtracks.fm/oembed"; 
		$this->providers['%^https:\/\/blackfire.io\/profiles\/.*\/graph%i'] = "https://blackfire.io/oembed"; 
		$this->providers['%^https:\/\/blackfire.io\/profiles\/compare\/.*\/graph%i'] = "https://blackfire.io/oembed"; 
		$this->providers['%^https:\/\/blogcast.host\/embed\/.*%i'] = "https://blogcast.host/oembed"; 
		$this->providers['%^https:\/\/blogcast.host\/embedly\/.*%i'] = "https://blogcast.host/oembed"; 
		$this->providers['%^https:\/\/view.briovr.com\/api\/v1\/worlds\/oembed\/.*%i'] = "https://view.briovr.com/api/v1/worlds/oembed/"; 
		$this->providers['%^https:\/\/buttondown.email\/.*%i'] = "https://buttondown.email/embed"; 
		$this->providers['%^https:\/\/cmc.byzart.eu\/files\/.*%i'] = "https://cmc.byzart.eu/oembed/"; 
		$this->providers['%^https:\/\/cacoo.com\/diagrams\/.*%i'] = "http://cacoo.com/oembed.json"; 
		$this->providers['%^https:\/\/carbonhealth.com\/practice\/.*%i'] = "http://carbonhealth.com/oembed"; 
		$this->providers['%^http:\/\/img.catbo.at\/.*%i'] = "http://img.catbo.at/oembed.json"; 
		$this->providers['%^http:\/\/view.ceros.com\/.*%i'] = "http://view.ceros.com/oembed"; 
		$this->providers['%^https:\/\/chainflix.net\/video\/.*%i'] = "https://beta.chainflix.net/video/oembed"; 
		$this->providers['%^https:\/\/chainflix.net\/video\/embed\/.*%i'] = "https://beta.chainflix.net/video/oembed"; 
		$this->providers['%^https:\/\/.*.chainflix.net\/video\/.*%i'] = "https://beta.chainflix.net/video/oembed"; 
		$this->providers['%^https:\/\/.*.chainflix.net\/video\/embed\/.*%i'] = "https://beta.chainflix.net/video/oembed"; 
		$this->providers['%^http:\/\/public.chartblocks.com\/c\/.*%i'] = "http://embed.chartblocks.com/1.0/oembed"; 
		$this->providers['%^http:\/\/chirb.it\/.*%i'] = "http://chirb.it/oembed.json"; 
		$this->providers['%^https:\/\/www.circuitlab.com\/circuit\/.*%i'] = "https://www.circuitlab.com/circuit/oembed/"; 
		$this->providers['%^http:\/\/www.clipland.com\/v\/.*%i'] = "https://www.clipland.com/api/oembed"; 
		$this->providers['%^https:\/\/www.clipland.com\/v\/.*%i'] = "https://www.clipland.com/api/oembed"; 
		$this->providers['%^http:\/\/clyp.it\/.*%i'] = "http://api.clyp.it/oembed/"; 
		$this->providers['%^http:\/\/clyp.it\/playlist\/.*%i'] = "http://api.clyp.it/oembed/"; 
		$this->providers['%^https:\/\/app.ilovecoco.video\/.*\/embed%i'] = "https://app.ilovecoco.video/api/oembed.json"; 
		$this->providers['%^https:\/\/codehs.com\/editor\/share_abacus\/.*%i'] = "https://codehs.com/api/sharedprogram/*/oembed/"; 
		$this->providers['%^http:\/\/codepen.io\/.*%i'] = "https://codepen.io/api/oembed"; 
		$this->providers['%^https:\/\/codepen.io\/.*%i'] = "https://codepen.io/api/oembed"; 
		$this->providers['%^http:\/\/codepoints.net\/.*%i'] = "https://codepoints.net/api/v1/oembed"; 
		$this->providers['%^https:\/\/codepoints.net\/.*%i'] = "https://codepoints.net/api/v1/oembed"; 
		$this->providers['%^http:\/\/www.codepoints.net\/.*%i'] = "https://codepoints.net/api/v1/oembed"; 
		$this->providers['%^https:\/\/www.codepoints.net\/.*%i'] = "https://codepoints.net/api/v1/oembed"; 
		$this->providers['%^https:\/\/codesandbox.io\/s\/.*%i'] = "https://codesandbox.io/oembed"; 
		$this->providers['%^https:\/\/codesandbox.io\/embed\/.*%i'] = "https://codesandbox.io/oembed"; 
		$this->providers['%^http:\/\/www.collegehumor.com\/video\/.*%i'] = "http://www.collegehumor.com/oembed.json"; 
		$this->providers['%^https:\/\/commaful.com\/play\/.*%i'] = "https://commaful.com/api/oembed/"; 
		$this->providers['%^https:\/\/coub.com\/view\/.*%i'] = "https://coub.com/api/oembed.json"; 
		$this->providers['%^https:\/\/coub.com\/embed\/.*%i'] = "https://coub.com/api/oembed.json"; 
		$this->providers['%^http:\/\/crowdranking.com\/.*\/.*%i'] = "http://crowdranking.com/api/oembed.json"; 
		$this->providers['%^https:\/\/staging.cyranosystems.com\/msg\/.*%i'] = "https://staging.cyranosystems.com/oembed"; 
		$this->providers['%^https:\/\/app.cyranosystems.com\/msg\/.*%i'] = "https://staging.cyranosystems.com/oembed"; 
		$this->providers['%^https:\/\/www.dailymotion.com\/video\/.*%i'] = "https://www.dailymotion.com/services/oembed"; 
		$this->providers['%^https:\/\/datawrapper.dwcdn.net\/.*%i'] = "https://api.datawrapper.de/v3/oembed/"; 
		$this->providers['%^https:\/\/.*.deseret.com\/.*%i'] = "https://embed.deseret.com/"; 
		$this->providers['%^http:\/\/.*.deviantart.com\/art\/.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^http:\/\/.*.deviantart.com\/.*#\/d.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^http:\/\/fav.me\/.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^http:\/\/sta.sh\/.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^https:\/\/.*.deviantart.com\/art\/.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^https:\/\/.*.deviantart.com\/.*\/art\/.*%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^https:\/\/sta.sh\/.*",%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^https:\/\/.*.deviantart.com\/.*#\/d.*"%i'] = "https://backend.deviantart.com/oembed"; 
		$this->providers['%^https:\/\/.*.didacte.com\/a\/course\/.*%i'] = "https://*.didacte.com/cards/oembed'"; 
		$this->providers['%^https:\/\/www.ultimedia.com\/central\/video\/edit\/id\/.*\/topic_id\/.*\/%i'] = "https://www.ultimedia.com/api/search/oembed"; 
		$this->providers['%^https:\/\/www.ultimedia.com\/default\/index\/videogeneric\/id\/.*\/showtitle\/1\/viewnc\/1%i'] = "https://www.ultimedia.com/api/search/oembed"; 
		$this->providers['%^https:\/\/www.ultimedia.com\/default\/index\/videogeneric\/id\/.*%i'] = "https://www.ultimedia.com/api/search/oembed"; 
		$this->providers['%^http:\/\/www.dipity.com\/.*\/.*\/%i'] = "http://www.dipity.com/oembed/timeline/"; 
		$this->providers['%^https:\/\/.*.docdroid.net\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^http:\/\/.*.docdroid.net\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^https:\/\/docdro.id\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^http:\/\/docdro.id\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^https:\/\/.*.docdroid.com\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^http:\/\/.*.docdroid.com\/.*%i'] = "https://www.docdroid.net/api/oembed"; 
		$this->providers['%^http:\/\/dotsub.com\/view\/.*%i'] = "http://dotsub.com/services/oembed"; 
		$this->providers['%^https:\/\/d.tube\/v\/.*%i'] = "https://api.d.tube/oembed"; 
		$this->providers['%^http:\/\/edocr.com\/docs\/.*%i'] = "http://edocr.com/api/oembed"; 
		$this->providers['%^http:\/\/egliseinfo.catholique.fr\/.*%i'] = "http://egliseinfo.catholique.fr/api/oembed"; 
		$this->providers['%^http:\/\/embedarticles.com\/.*%i'] = "http://embedarticles.com/oembed/"; 
		$this->providers['%^https:\/\/embedery.com\/widget\/.*%i'] = "https://embedery.com/api/oembed"; 
		$this->providers['%^https:\/\/ethfiddle.com\/.*%i'] = "https://ethfiddle.com/services/oembed/"; 
		$this->providers['%^https:\/\/eyrie.io\/board\/.*%i'] = "https://eyrie.io/v1/oembed"; 
		$this->providers['%^https:\/\/eyrie.io\/sparkfun\/.*%i'] = "https://eyrie.io/v1/oembed"; 
		$this->providers['%^https:\/\/www.facebook.com\/.*\/posts\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/photos\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/.*\/photos\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/photo.php.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/photo.php%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/.*\/activity\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/permalink.php%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/media\/set?set=.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/questions\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/notes\/.*\/.*\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_post"; 
		$this->providers['%^https:\/\/www.facebook.com\/.*\/videos\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_video";
		$this->providers['%^https:\/\/www.facebook.com\/watch\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_video"; 
		$this->providers['%^https:\/\/www.facebook.com\/video.php%i'] = "https://graph.facebook.com/v16.0/oembed_video";
		$this->providers['%^https:\/\/fb.watch\/.*%i'] = "https://graph.facebook.com/v16.0/oembed_video"; 
		$this->providers['%^https:\/\/app.getfader.com\/projects\/.*\/publish%i'] = "https://app.getfader.com/api/oembed"; 
		$this->providers['%^https:\/\/faithlifetv.com\/items\/.*%i'] = "https://faithlifetv.com/api/oembed"; 
		$this->providers['%^https:\/\/faithlifetv.com\/items\/resource\/.*\/.*%i'] = "https://faithlifetv.com/api/oembed"; 
		$this->providers['%^https:\/\/faithlifetv.com\/media\/.*%i'] = "https://faithlifetv.com/api/oembed"; 
		$this->providers['%^https:\/\/faithlifetv.com\/media\/assets\/.*%i'] = "https://faithlifetv.com/api/oembed"; 
		$this->providers['%^https:\/\/faithlifetv.com\/media\/resource\/.*\/.*%i'] = "https://faithlifetv.com/api/oembed"; 
		$this->providers['%^https:\/\/.*.fireworktv.com\/.*%i'] = "https://www.fireworktv.com/oembed"; 
		$this->providers['%^https:\/\/.*.fireworktv.com\/embed\/.*\/v\/.*%i'] = "https://www.fireworktv.com/oembed"; 
		$this->providers['%^https:\/\/www.fite.tv\/watch\/.*%i'] = "https://www.fite.tv/oembed"; 
		$this->providers['%^https:\/\/flat.io\/score\/.*%i'] = "https://flat.io/services/oembed"; 
		$this->providers['%^https:\/\/.*.flat.io\/score\/.*%i'] = "https://flat.io/services/oembed"; 
		$this->providers['%^http:\/\/.*.flickr.com\/photos\/.*%i'] = "https://www.flickr.com/services/oembed/"; 
		$this->providers['%^http:\/\/flic.kr\/p\/.*%i'] = "https://www.flickr.com/services/oembed/"; 
		$this->providers['%^https:\/\/.*.flickr.com\/photos\/.*%i'] = "https://www.flickr.com/services/oembed/"; 
		$this->providers['%^https:\/\/flic.kr\/p\/.*%i'] = "https://www.flickr.com/services/oembed/"; 
		$this->providers['%^https:\/\/public.flourish.studio\/visualisation\/.*%i'] = "https://app.flourish.studio/api/v1/oembed"; 
		$this->providers['%^https:\/\/public.flourish.studio\/story\/.*%i'] = "https://app.flourish.studio/api/v1/oembed"; 
		$this->providers['%^https:\/\/catapult.fontself.com\/.*%i'] = "https://oembed.fontself.com/"; 
		$this->providers['%^http:\/\/fiso.foxsports.com.au\/isomorphic-widget\/.*%i'] = "https://fiso.foxsports.com.au/oembed"; 
		$this->providers['%^https:\/\/fiso.foxsports.com.au\/isomorphic-widget\/.*%i'] = "https://fiso.foxsports.com.au/oembed"; 
		$this->providers['%^http:\/\/framebuzz.com\/v\/.*%i'] = "https://framebuzz.com/oembed/"; 
		$this->providers['%^https:\/\/framebuzz.com\/v\/.*%i'] = "https://framebuzz.com/oembed/"; 
		$this->providers['%^http:\/\/www.funnyordie.com\/videos\/.*%i'] = "http://www.funnyordie.com/oembed.json"; 
		$this->providers['%^http:\/\/.*.geograph.org.uk\/.*%i'] = "http://api.geograph.org.uk/api/oembed"; 
		$this->providers['%^http:\/\/.*.geograph.co.uk\/.*%i'] = "http://api.geograph.org.uk/api/oembed"; 
		$this->providers['%^http:\/\/.*.geograph.ie\/.*%i'] = "http://api.geograph.org.uk/api/oembed"; 
		$this->providers['%^http:\/\/.*.wikimedia.org\/.*_geograph.org.uk_.*%i'] = "http://api.geograph.org.uk/api/oembed"; 
		$this->providers['%^http:\/\/.*.geograph.org.gg\/.*%i'] = "http://www.geograph.org.gg/api/oembed"; 
		$this->providers['%^http:\/\/.*.geograph.org.je\/.*%i'] = "http://www.geograph.org.gg/api/oembed"; 
		$this->providers['%^http:\/\/channel-islands.geograph.org\/.*%i'] = "http://www.geograph.org.gg/api/oembed"; 
		$this->providers['%^http:\/\/channel-islands.geographs.org\/.*%i'] = "http://www.geograph.org.gg/api/oembed"; 
		$this->providers['%^http:\/\/.*.channel.geographs.org\/.*%i'] = "http://www.geograph.org.gg/api/oembed"; 
		$this->providers['%^http:\/\/geo-en.hlipp.de\/.*%i'] = "http://geo.hlipp.de/restapi.php/api/oembed"; 
		$this->providers['%^http:\/\/geo.hlipp.de\/.*%i'] = "http://geo.hlipp.de/restapi.php/api/oembed"; 
		$this->providers['%^http:\/\/germany.geograph.org\/.*%i'] = "http://geo.hlipp.de/restapi.php/api/oembed"; 
		$this->providers['%^http:\/\/gty.im\/.*%i'] = "http://embed.gettyimages.com/oembed"; 
		$this->providers['%^http:\/\/gfycat.com\/.*%i'] = "https://api.gfycat.com/v1/oembed"; 
		$this->providers['%^http:\/\/www.gfycat.com\/.*%i'] = "https://api.gfycat.com/v1/oembed"; 
		$this->providers['%^https:\/\/gfycat.com\/.*%i'] = "https://api.gfycat.com/v1/oembed"; 
		$this->providers['%^https:\/\/www.gfycat.com\/.*%i'] = "https://api.gfycat.com/v1/oembed"; 
		$this->providers['%^https:\/\/www.gifnote.com\/play\/.*%i'] = "https://www.gifnote.com/services/oembed"; 
		$this->providers['%^https:\/\/giphy.com\/gifs\/.*%i'] = "https://giphy.com/services/oembed"; 
		$this->providers['%^http:\/\/gph.is\/.*%i'] = "https://giphy.com/services/oembed"; 
		$this->providers['%^https:\/\/media.giphy.com\/media\/.*\/giphy.gif%i'] = "https://giphy.com/services/oembed"; 
		$this->providers['%^https:\/\/gtchannel.com\/watch\/.*%i'] = "https://api.luminery.com/oembed"; 
		$this->providers['%^https:\/\/gyazo.com\/.*%i'] = "https://api.gyazo.com/api/oembed"; 
		$this->providers['%^https:\/\/hearthis.at\/.*\/.*\/%i'] = "https://hearthis.at/oembed/?format=json"; 
		$this->providers['%^https:\/\/hearthis.at\/.*\/set\/.*\/%i'] = "https://hearthis.at/oembed/?format=json"; 
		$this->providers['%^https:\/\/player.hihaho.com\/.*%i'] = "https://player.hihaho.com/services/oembed/*"; 
		$this->providers['%^https:\/\/homey.app\/f\/.*%i'] = "https://homey.app/api/oembed/flow"; 
		$this->providers['%^https:\/\/homey.app\/.*\/flow\/.*%i'] = "https://homey.app/api/oembed/flow"; 
		$this->providers['%^http:\/\/huffduffer.com\/.*\/.*%i'] = "http://huffduffer.com/oembed"; 
		$this->providers['%^http:\/\/www.hulu.com\/watch\/.*%i'] = "http://www.hulu.com/api/oembed.json"; 
		$this->providers['%^http:\/\/www.ifixit.com\/Guide\/View\/.*%i'] = "http://www.ifixit.com/Embed"; 
		$this->providers['%^http:\/\/ifttt.com\/recipes\/.*%i'] = "http://www.ifttt.com/oembed/"; 
		$this->providers['%^https:\/\/www.iheart.com\/podcast\/.*\/.*%i'] = "https://www.iheart.com/oembed"; 
		$this->providers['%^https:\/\/player.indacolive.com\/player\/jwp\/clients\/.*%i'] = "https://player.indacolive.com/services/oembed"; 
		$this->providers['%^https:\/\/infogram.com\/.*%i'] = "https://infogram.com/oembed"; 
		$this->providers['%^https:\/\/.*.infoveave.net\/E\/.*%i'] = "https://infoveave.net/services/oembed/"; 
		$this->providers['%^https:\/\/.*.infoveave.net\/P\/.*%i'] = "https://infoveave.net/services/oembed/"; 
		$this->providers['%^https:\/\/www.injurymap.com\/exercises\/.*%i'] = "https://www.injurymap.com/services/oembed"; 
		$this->providers['%^https:\/\/www.inoreader.com\/oembed\/%i'] = "https://www.inoreader.com/oembed/api/"; 
		$this->providers['%^http:\/\/.*.inphood.com\/.*%i'] = "http://api.inphood.com/oembed"; 
		$this->providers['%^http:\/\/instagram.com\/.*\/p\/.*,%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/www.instagram.com\/.*\/p\/.*,%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/instagram.com\/.*\/p\/.*,%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/www.instagram.com\/.*\/p\/.*,%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/instagram.com\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/instagr.am\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/www.instagram.com\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/www.instagr.am\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/instagram.com\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/instagr.am\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/www.instagram.com\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/www.instagr.am\/p\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/instagram.com\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/instagr.am\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/www.instagram.com\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^http:\/\/www.instagr.am\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/instagram.com\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/instagr.am\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/www.instagram.com\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/www.instagr.am\/tv\/.*%i'] = "https://graph.facebook.com/v16.0/instagram_oembed"; 
		$this->providers['%^https:\/\/issuu.com\/.*\/docs\/.*%i'] = "https://issuu.com/oembed"; 
		$this->providers['%^https:\/\/jovian.ml\/.*%i'] = "https://api.jovian.ai/oembed.json"; 
		$this->providers['%^https:\/\/jovian.ml\/viewer.*%i'] = "https://api.jovian.ai/oembed.json"; 
		$this->providers['%^https:\/\/.*.jovian.ml\/.*%i'] = "https://api.jovian.ai/oembed.json"; 
		$this->providers['%^https:\/\/tv.kakao.com\/channel\/.*\/cliplink\/.*%i'] = "https://tv.kakao.com/oembed"; 
		$this->providers['%^https:\/\/tv.kakao.com\/channel\/v\/.*%i'] = "https://tv.kakao.com/oembed"; 
		$this->providers['%^https:\/\/tv.kakao.com\/channel\/.*\/livelink\/.*%i'] = "https://tv.kakao.com/oembed"; 
		$this->providers['%^https:\/\/tv.kakao.com\/channel\/l\/.*%i'] = "https://tv.kakao.com/oembed"; 
		$this->providers['%^http:\/\/www.kickstarter.com\/projects\/.*%i'] = "http://www.kickstarter.com/services/oembed"; 
		$this->providers['%^https:\/\/www.kidoju.com\/en\/x\/.*\/.*%i'] = "https://www.kidoju.com/api/oembed"; 
		$this->providers['%^https:\/\/www.kidoju.com\/fr\/x\/.*\/.*%i'] = "https://www.kidoju.com/api/oembed"; 
		$this->providers['%^https:\/\/halaman.email\/form\/.*%i'] = "https://halaman.email/service/oembed"; 
		$this->providers['%^https:\/\/aplikasi.kirim.email\/form\/.*%i'] = "https://halaman.email/service/oembed"; 
		$this->providers['%^http:\/\/kit.co\/.*\/.*%i'] = "https://embed.kit.co/oembed"; 
		$this->providers['%^https:\/\/kit.co\/.*\/.*%i'] = "https://embed.kit.co/oembed"; 
		$this->providers['%^http:\/\/www.kitchenbowl.com\/recipe\/.*%i'] = "http://www.kitchenbowl.com/oembed"; 
		$this->providers['%^http:\/\/jdr.knacki.info\/meuh\/.*%i'] = "https://jdr.knacki.info/oembed"; 
		$this->providers['%^https:\/\/jdr.knacki.info\/meuh\/.*%i'] = "https://jdr.knacki.info/oembed"; 
		$this->providers['%^https:\/\/knowledgepad.co\/#\/knowledge\/.*%i'] = "https://api.spoonacular.com/knowledge/oembed"; 
		$this->providers['%^http:\/\/learningapps.org\/.*%i'] = "http://learningapps.org/oembed.php"; 
		$this->providers['%^https:\/\/umotion-test.univ-lemans.fr\/video\/.*%i'] = "https://umotion-test.univ-lemans.fr/oembed"; 
		$this->providers['%^https:\/\/pod.univ-lille.fr\/video\/.*%i'] = "https://pod.univ-lille.fr/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/accounts\/.*\/events\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/accounts\/.*\/events\/.*\/videos\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/.*\/events\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/.*\/events\/.*\/videos\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/.*\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/livestream.com\/.*\/.*\/videos\/.*%i'] = "https://livestream.com/oembed"; 
		$this->providers['%^https:\/\/app.ludus.one\/.*%i'] = "https://app.ludus.one/oembed"; 
		$this->providers['%^http:\/\/mathembed.com\/latex?inputText=.*%i'] = "http://mathembed.com/oembed"; 
		$this->providers['%^https:\/\/me.me\/i\/.*%i'] = "https://me.me/oembed"; 
		$this->providers['%^https:\/\/.*.medialab.app\/share\/watch\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/.*.medialab.co\/share\/watch\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/.*.medialab.app\/share\/social\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/.*.medialab.co\/share\/social\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/.*.medialab.app\/share\/embed\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/.*.medialab.co\/share\/embed\/.*%i'] = "https://*.medialab.(co|app)/api/oembed/"; 
		$this->providers['%^https:\/\/medienarchiv.zhdk.ch\/entries\/.*%i'] = "https://medienarchiv.zhdk.ch/oembed.json"; 
		$this->providers['%^http:\/\/meetup.com\/.*%i'] = "https://api.meetup.com/oembed"; 
		$this->providers['%^https:\/\/www.meetup.com\/.*%i'] = "https://api.meetup.com/oembed"; 
		$this->providers['%^https:\/\/meetup.com\/.*%i'] = "https://api.meetup.com/oembed"; 
		$this->providers['%^http:\/\/meetu.ps\/.*%i'] = "https://api.meetup.com/oembed"; 
		$this->providers['%^https:\/\/mermaid.ink\/img\/.*%i'] = "https://mermaid.ink/services/oembed"; 
		$this->providers['%^https:\/\/mermaid.ink\/svg\/.*%i'] = "https://mermaid.ink/services/oembed"; 
		$this->providers['%^https:\/\/.*.microsoftstream.com\/video\/.*%i'] = "https://web.microsoftstream.com/oembed"; 
		$this->providers['%^https:\/\/.*.microsoftstream.com\/channel\/.*%i'] = "https://web.microsoftstream.com/oembed"; 
		$this->providers['%^http:\/\/www.mixcloud.com\/.*\/.*\/%i'] = "https://www.mixcloud.com/oembed/"; 
		$this->providers['%^https:\/\/www.mixcloud.com\/.*\/.*\/%i'] = "https://www.mixcloud.com/oembed/"; 
		$this->providers['%^http:\/\/www.mobypicture.com\/user\/.*\/view\/.*%i'] = "http://api.mobypicture.com/oEmbed"; 
		$this->providers['%^http:\/\/moby.to\/.*%i'] = "http://api.mobypicture.com/oEmbed"; 
		$this->providers['%^https:\/\/beta.modelo.io\/embedded\/.*%i'] = "https://portal.modelo.io/oembed"; 
		$this->providers['%^https:\/\/m-roll.morphcast.com\/mroll\/.*%i'] = "https://m-roll.morphcast.com/service/oembed"; 
		$this->providers['%^https:\/\/musicboxmaniacs.com\/explore\/melody\/.*%i'] = "https://musicboxmaniacs.com/embed/"; 
		$this->providers['%^https:\/\/mybeweeg.com\/w\/.*%i'] = "https://mybeweeg.com/services/oembed"; 
		$this->providers['%^https:\/\/namchey.com\/embeds\/.*%i'] = "https://namchey.com/api/oembed"; 
		$this->providers['%^http:\/\/.*.nanoo.tv\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^http:\/\/nanoo.tv\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^http:\/\/.*.nanoo.pro\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^http:\/\/nanoo.pro\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/.*.nanoo.tv\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/nanoo.tv\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/.*.nanoo.pro\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/nanoo.pro\/link\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^http:\/\/media.zhdk.ch\/signatur\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^http:\/\/new.media.zhdk.ch\/signatur\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/media.zhdk.ch\/signatur\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/new.media.zhdk.ch\/signatur\/.*%i'] = "https://www.nanoo.tv/services/oembed"; 
		$this->providers['%^https:\/\/www.nb.no\/items\/.*%i'] = "https://api.nb.no/catalog/v1/oembed"; 
		$this->providers['%^https:\/\/naturalatlas.com\/.*%i'] = "https://naturalatlas.com/oembed.json"; 
		$this->providers['%^https:\/\/naturalatlas.com\/.*\/.*%i'] = "https://naturalatlas.com/oembed.json"; 
		$this->providers['%^https:\/\/naturalatlas.com\/.*\/.*\/.*%i'] = "https://naturalatlas.com/oembed.json"; 
		$this->providers['%^https:\/\/naturalatlas.com\/.*\/.*\/.*\/.*%i'] = "https://naturalatlas.com/oembed.json"; 
		$this->providers['%^http:\/\/.*.nfb.ca\/film\/.*%i'] = "http://www.nfb.ca/remote/services/oembed/"; 
		$this->providers['%^https:\/\/www.odds.com.au\/.*%i'] = "https://www.odds.com.au/api/oembed/"; 
		$this->providers['%^https:\/\/odds.com.au\/.*%i'] = "https://www.odds.com.au/api/oembed/"; 
		$this->providers['%^https:\/\/song.link\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/album.link\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/artist.link\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/playlist.link\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/pods.link\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/mylink.page\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^https:\/\/odesli.co\/.*%i'] = "https://song.link/oembed"; 
		$this->providers['%^http:\/\/official.fm\/tracks\/.*%i'] = "http://official.fm/services/oembed.json"; 
		$this->providers['%^http:\/\/official.fm\/playlists\/.*%i'] = "http://official.fm/services/oembed.json"; 
		$this->providers['%^https:\/\/omniscope.me\/.*%i'] = "https://omniscope.me/_global_/oembed/json"; 
		$this->providers['%^http:\/\/on.aol.com\/video\/.*%i'] = "http://on.aol.com/api"; 
		$this->providers['%^https:\/\/orbitvu.co\/001\/.*\/ov3601\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^https:\/\/orbitvu.co\/001\/.*\/ov3601\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^https:\/\/orbitvu.co\/001\/.*\/ov3602\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^https:\/\/orbitvu.co\/001\/.*\/2\/orbittour\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^https:\/\/orbitvu.co\/001\/.*\/1\/2\/orbittour\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^http:\/\/orbitvu.co\/001\/.*\/ov3601\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^http:\/\/orbitvu.co\/001\/.*\/ov3601\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^http:\/\/orbitvu.co\/001\/.*\/ov3602\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^http:\/\/orbitvu.co\/001\/.*\/2\/orbittour\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^http:\/\/orbitvu.co\/001\/.*\/1\/2\/orbittour\/.*\/view%i'] = "http://orbitvu.co/service/oembed"; 
		$this->providers['%^https:\/\/www.oumy.com\/v\/.*%i'] = "https://www.oumy.com/oembed"; 
		$this->providers['%^https:\/\/outplayed.tv\/media\/.*%i'] = "https://outplayed.tv/oembed"; 
		$this->providers['%^https:\/\/overflow.io\/s\/.*%i'] = "https://overflow.io/services/oembed"; 
		$this->providers['%^https:\/\/overflow.io\/embed\/.*%i'] = "https://overflow.io/services/oembed"; 
		$this->providers['%^https:\/\/www.oz.com\/.*\/video\/.*%i'] = "https://core.oz.com/oembed"; 
		$this->providers['%^https:\/\/padlet.com\/.*%i'] = "https://padlet.com/oembed/"; 
		$this->providers['%^http:\/\/pastery.net\/.*%i'] = "https://www.pastery.net/oembed"; 
		$this->providers['%^https:\/\/pastery.net\/.*%i'] = "https://www.pastery.net/oembed"; 
		$this->providers['%^http:\/\/www.pastery.net\/.*%i'] = "https://www.pastery.net/oembed"; 
		$this->providers['%^https:\/\/www.pastery.net\/.*%i'] = "https://www.pastery.net/oembed"; 
		$this->providers['%^https:\/\/tools.pinpoll.com\/embed\/.*%i'] = "https://tools.pinpoll.com/oembed"; 
		$this->providers['%^https:\/\/store.pixdor.com\/place-marker-widget\/.*\/show%i'] = "https://store.pixdor.com/oembed"; 
		$this->providers['%^https:\/\/store.pixdor.com\/map\/.*\/show%i'] = "https://store.pixdor.com/oembed"; 
		$this->providers['%^https:\/\/.*.podbean.com\/e\/.*%i'] = "https://api.podbean.com/v1/oembed"; 
		$this->providers['%^http:\/\/.*.podbean.com\/e\/.*%i'] = "https://api.podbean.com/v1/oembed"; 
		$this->providers['%^https:\/\/www.polarishare.com\/.*\/.*%i'] = "https://api.polarishare.com/rest/api/oembed"; 
		$this->providers['%^http:\/\/.*.polldaddy.com\/s\/.*%i'] = "http://polldaddy.com/oembed/"; 
		$this->providers['%^http:\/\/.*.polldaddy.com\/poll\/.*%i'] = "http://polldaddy.com/oembed/"; 
		$this->providers['%^http:\/\/.*.polldaddy.com\/ratings\/.*%i'] = "http://polldaddy.com/oembed/"; 
		$this->providers['%^https:\/\/app.sellwithport.com\/#\/buyer\/.*%i'] = "https://api.sellwithport.com/v1.0/buyer/oembed"; 
		$this->providers['%^https:\/\/portfolium.com\/entry\/.*%i'] = "https://api.portfolium.com/oembed"; 
		$this->providers['%^https:\/\/posixion.com\/question\/.*%i'] = "http://posixion.com/services/oembed/"; 
		$this->providers['%^https:\/\/posixion.com\/.*\/question\/.*%i'] = "http://posixion.com/services/oembed/"; 
		$this->providers['%^http:\/\/www.quiz.biz\/quizz-.*.html%i'] = "http://www.quiz.biz/api/oembed"; 
		$this->providers['%^http:\/\/www.quizz.biz\/quizz-.*.html%i'] = "http://www.quizz.biz/api/oembed"; 
		$this->providers['%^https:\/\/play.radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^https:\/\/radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^https:\/\/www.radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^http:\/\/play.radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^http:\/\/radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^http:\/\/www.radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^https:\/\/.*.radiopublic.com\/.*%i'] = "https://oembed.radiopublic.com/oembed"; 
		$this->providers['%^https:\/\/reddit.com\/r\/.*\/comments\/.*\/.*%i'] = "https://www.reddit.com/oembed"; 
		$this->providers['%^https:\/\/www.reddit.com\/r\/.*\/comments\/.*\/.*%i'] = "https://www.reddit.com/oembed"; 
		$this->providers['%^http:\/\/rwire.com\/.*%i'] = "http://publisher.releasewire.com/oembed/"; 
		$this->providers['%^https:\/\/repl.it\/@.*\/.*%i'] = "https://repl.it/data/oembed"; 
		$this->providers['%^http:\/\/repubhub.icopyright.net\/freePost.act?.*%i'] = "http://repubhub.icopyright.net/oembed.act"; 
		$this->providers['%^https:\/\/www.reverbnation.com\/.*%i'] = "https://www.reverbnation.com/oembed"; 
		$this->providers['%^https:\/\/www.reverbnation.com\/.*\/songs\/.*%i'] = "https://www.reverbnation.com/oembed"; 
		$this->providers['%^http:\/\/roomshare.jp\/post\/.*%i'] = "http://roomshare.jp/en/oembed.json"; 
		$this->providers['%^http:\/\/roomshare.jp\/en\/post\/.*%i'] = "http://roomshare.jp/en/oembed.json"; 
		$this->providers['%^https:\/\/roosterteeth.com\/.*%i'] = "https://roosterteeth.com/oembed"; 
		$this->providers['%^http:\/\/embed.runkit.com\/.*,%i'] = "https://embed.runkit.com/oembed"; 
		$this->providers['%^https:\/\/embed.runkit.com\/.*,%i'] = "https://embed.runkit.com/oembed"; 
		$this->providers['%^http:\/\/videos.sapo.pt\/.*%i'] = "http://videos.sapo.pt/oembed"; 
		$this->providers['%^https:\/\/console.screen9.com\/.*%i'] = "https://api.screen9.com/oembed"; 
		$this->providers['%^https:\/\/.*.screen9.tv\/.*%i'] = "https://api.screen9.com/oembed"; 
		$this->providers['%^http:\/\/www.screenr.com\/.*\/%i'] = "http://www.screenr.com/api/oembed.json"; 
		$this->providers['%^http:\/\/www.scribblemaps.com\/maps\/view\/.*%i'] = "https://scribblemaps.com/api/services/oembed.json"; 
		$this->providers['%^https:\/\/www.scribblemaps.com\/maps\/view\/.*%i'] = "https://scribblemaps.com/api/services/oembed.json"; 
		$this->providers['%^http:\/\/scribblemaps.com\/maps\/view\/.*%i'] = "https://scribblemaps.com/api/services/oembed.json"; 
		$this->providers['%^https:\/\/scribblemaps.com\/maps\/view\/.*%i'] = "https://scribblemaps.com/api/services/oembed.json"; 
		$this->providers['%^http:\/\/www.scribd.com\/doc\/.*%i'] = "http://www.scribd.com/services/oembed/"; 
		$this->providers['%^https:\/\/embed.sendtonews.com\/oembed\/.*%i'] = "https://embed.sendtonews.com/services/oembed"; 
		$this->providers['%^https:\/\/www.shortnote.jp\/view\/notes\/.*%i'] = "https://www.shortnote.jp/oembed/"; 
		$this->providers['%^http:\/\/shoudio.com\/.*%i'] = "http://shoudio.com/api/oembed"; 
		$this->providers['%^http:\/\/shoud.io\/.*%i'] = "http://shoudio.com/api/oembed"; 
		$this->providers['%^https:\/\/showtheway.io\/to\/.*%i'] = "https://showtheway.io/oembed"; 
		$this->providers['%^https:\/\/simplecast.com\/s\/.*%i'] = "https://simplecast.com/oembed"; 
		$this->providers['%^https:\/\/onsizzle.com\/i\/.*%i'] = "https://onsizzle.com/oembed"; 
		$this->providers['%^http:\/\/sketchfab.com\/models\/.*%i'] = "http://sketchfab.com/oembed"; 
		$this->providers['%^https:\/\/sketchfab.com\/models\/.*%i'] = "http://sketchfab.com/oembed"; 
		$this->providers['%^https:\/\/sketchfab.com\/.*\/folders\/.*%i'] = "http://sketchfab.com/oembed"; 
		$this->providers['%^https:\/\/www.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^http:\/\/www.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^https:\/\/fr.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^http:\/\/fr.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^https:\/\/de.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^http:\/\/de.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^https:\/\/es.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^http:\/\/es.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^https:\/\/pt.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^http:\/\/pt.slideshare.net\/.*\/.*%i'] = "https://www.slideshare.net/api/oembed/2"; 
		$this->providers['%^https:\/\/smashnotes.com\/p\/.*%i'] = "https://smashnotes.com/services/oembed"; 
		$this->providers['%^https:\/\/smashnotes.com\/p\/.*\/e\/.* - https:\/\/smashnotes.com\/p\/.*\/e\/.*\/s\/.*%i'] = "https://smashnotes.com/services/oembed"; 
		$this->providers['%^http:\/\/.*.smugmug.com\/.*%i'] = "https://api.smugmug.com/services/oembed/"; 
		$this->providers['%^https:\/\/.*.smugmug.com\/.*%i'] = "https://api.smugmug.com/services/oembed/"; 
		$this->providers['%^https:\/\/www.socialexplorer.com\/.*\/explore%i'] = "https://www.socialexplorer.com/services/oembed/"; 
		$this->providers['%^https:\/\/www.socialexplorer.com\/.*\/view%i'] = "https://www.socialexplorer.com/services/oembed/"; 
		$this->providers['%^https:\/\/www.socialexplorer.com\/.*\/edit%i'] = "https://www.socialexplorer.com/services/oembed/"; 
		$this->providers['%^https:\/\/www.socialexplorer.com\/.*\/embed%i'] = "https://www.socialexplorer.com/services/oembed/"; 
		$this->providers['%^http:\/\/soundcloud.com\/.*%i'] = "https://soundcloud.com/oembed"; 
		$this->providers['%^https:\/\/soundcloud.com\/.*%i'] = "https://soundcloud.com/oembed"; 
		$this->providers['%^https:\/\/soundcloud.app.goog.gl\/.*%i'] = "https://soundcloud.com/oembed"; 
		$this->providers['%^http:\/\/speakerdeck.com\/.*\/.*%i'] = "https://speakerdeck.com/oembed.json"; 
		$this->providers['%^https:\/\/speakerdeck.com\/.*\/.*%i'] = "https://speakerdeck.com/oembed.json"; 
		$this->providers['%^http:\/\/play.bespotful.com\/.*%i'] = "https://api.bespotful.com/oembed"; 
		$this->providers['%^https:\/\/.*.spotify.com\/.*%i'] = "https://embed.spotify.com/oembed/"; 
		$this->providers['%^spotify:.*%i'] = "https://embed.spotify.com/oembed/"; 
		$this->providers['%^http:\/\/.*.spreaker.com\/.*%i'] = "https://api.spreaker.com/oembed"; 
		$this->providers['%^https:\/\/.*.spreaker.com\/.*%i'] = "https://api.spreaker.com/oembed"; 
		$this->providers['%^https:\/\/purl.stanford.edu\/.*%i'] = "https://purl.stanford.edu/embed.json"; 
		$this->providers['%^http:\/\/streamable.com\/.*%i'] = "https://api.streamable.com/oembed.json"; 
		$this->providers['%^https:\/\/streamable.com\/.*%i'] = "https://api.streamable.com/oembed.json"; 
		$this->providers['%^https:\/\/content.streamonecloud.net\/embed\/.*%i'] = "https://content.streamonecloud.net/oembed"; 
		$this->providers['%^https:\/\/www.sutori.com\/story\/.*%i'] = "https://www.sutori.com/api/oembed"; 
		$this->providers['%^https:\/\/sway.com\/.*%i'] = "https://sway.com/api/v1.0/oembed"; 
		$this->providers['%^https:\/\/www.sway.com\/.*%i'] = "https://sway.com/api/v1.0/oembed"; 
		$this->providers['%^http:\/\/ted.com\/talks\/.*%i'] = "https://www.ted.com/services/v1/oembed.json"; 
		$this->providers['%^https:\/\/ted.com\/talks\/.*%i'] = "https://www.ted.com/services/v1/oembed.json"; 
		$this->providers['%^https:\/\/www.ted.com\/talks\/.*%i'] = "https://www.ted.com/services/v1/oembed.json"; 
		$this->providers['%^https:\/\/www.nytimes.com\/svc\/oembed%i'] = "https://www.nytimes.com/svc/oembed/json/"; 
		$this->providers['%^https:\/\/nytimes.com\/.*%i'] = "https://www.nytimes.com/svc/oembed/json/"; 
		$this->providers['%^https:\/\/.*.nytimes.com\/.*%i'] = "https://www.nytimes.com/svc/oembed/json/"; 
		$this->providers['%^https:\/\/theysaidso.com\/image\/.*%i'] = "https://theysaidso.com/extensions/oembed/"; 
		$this->providers['%^http:\/\/www.tickcounter.com\/countdown\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^http:\/\/www.tickcounter.com\/countup\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^http:\/\/www.tickcounter.com\/ticker\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^http:\/\/www.tickcounter.com\/worldclock\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^https:\/\/www.tickcounter.com\/countdown\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^https:\/\/www.tickcounter.com\/countup\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^https:\/\/www.tickcounter.com\/ticker\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^https:\/\/www.tickcounter.com\/worldclock\/.*%i'] = "https://www.tickcounter.com/oembed"; 
		$this->providers['%^https:\/\/www.tiktok.com\/.*\/video\/.*%i'] = "https://www.tiktok.com/oembed"; 
		$this->providers['%^https:\/\/www.toornament.com\/tournaments\/.*\/information%i'] = "https://widget.toornament.com/oembed"; 
		$this->providers['%^https:\/\/www.toornament.com\/tournaments\/.*\/registration\/%i'] = "https://widget.toornament.com/oembed"; 
		$this->providers['%^https:\/\/www.toornament.com\/tournaments\/.*\/matches\/schedule%i'] = "https://widget.toornament.com/oembed"; 
		$this->providers['%^https:\/\/www.toornament.com\/tournaments\/.*\/stages\/.*\/%i'] = "https://widget.toornament.com/oembed"; 
		$this->providers['%^http:\/\/www.topy.se\/image\/.*%i'] = "http://www.topy.se/oembed/"; 
		$this->providers['%^https:\/\/www.tuxx.be\/.*%i'] = "https://www.tuxx.be/services/oembed"; 
		$this->providers['%^https:\/\/play.tvcf.co.kr\/.*%i'] = "https://play.tvcf.co.kr/rest/oembed"; 
		$this->providers['%^https:\/\/.*.tvcf.co.kr\/.*%i'] = "https://play.tvcf.co.kr/rest/oembed"; 
		$this->providers['%^http:\/\/clips.twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^https:\/\/clips.twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^http:\/\/www.twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^https:\/\/www.twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^http:\/\/twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^https:\/\/twitch.tv\/.*%i'] = "https://api.twitch.tv/v5/oembed"; 
		$this->providers['%^https:\/\/x.com\/.*%i'] = "https://publish.x.com/oembed"; 
		$this->providers['%^https:\/\/x.com\/.*\/status\/.*%i'] = "https://publish.x.com/oembed"; 
		$this->providers['%^https:\/\/.*.x.com\/.*\/status\/.*%i'] = "https://publish.x.com/oembed"; 
		$this->providers['%^https:\/\/play.typecast.ai\/s\/.*%i'] = "https://play.typecast.ai/oembed"; 
		$this->providers['%^https:\/\/play.typecast.ai\/e\/.*%i'] = "https://play.typecast.ai/oembed"; 
		$this->providers['%^https:\/\/play.typecast.ai\/.*%i'] = "https://play.typecast.ai/oembed"; 
		$this->providers['%^https:\/\/player.ubideo.com\/.*%i'] = "https://player.ubideo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/map.cam.ac.uk\/.*%i'] = "https://map.cam.ac.uk/oembed/"; 
		$this->providers['%^https:\/\/mediatheque.univ-paris1.fr\/video\/.*%i'] = "https://mediatheque.univ-paris1.fr/oembed"; 
		$this->providers['%^https:\/\/.*.uol.com.br\/view\/.*%i'] = "https://mais.uol.com.br/apiuol/v3/oembed/view"; 
		$this->providers['%^https:\/\/.*.uol.com.br\/video\/.*%i'] = "https://mais.uol.com.br/apiuol/v3/oembed/view"; 
		$this->providers['%^http:\/\/.*.ustream.tv\/.*%i'] = "http://www.ustream.tv/oembed"; 
		$this->providers['%^http:\/\/.*.ustream.com\/.*%i'] = "http://www.ustream.tv/oembed"; 
		$this->providers['%^https:\/\/.*.ustudio.com\/embed\/.*%i'] = "https://app.ustudio.com/api/v2/oembed"; 
		$this->providers['%^https:\/\/.*.ustudio.com\/embed\/.*\/.*%i'] = "https://app.ustudio.com/api/v2/oembed"; 
		$this->providers['%^https:\/\/www.utposts.com\/products\/.*%i'] = "https://www.utposts.com/api/oembed"; 
		$this->providers['%^http:\/\/www.utposts.com\/products\/.*%i'] = "https://www.utposts.com/api/oembed"; 
		$this->providers['%^https:\/\/utposts.com\/products\/.*%i'] = "https://www.utposts.com/api/oembed"; 
		$this->providers['%^http:\/\/utposts.com\/products\/.*%i'] = "https://www.utposts.com/api/oembed"; 
		$this->providers['%^http:\/\/uttles.com\/uttle\/.*%i'] = "http://uttles.com/api/reply/oembed"; 
		$this->providers['%^http:\/\/veer.tv\/videos\/.*%i'] = "https://api.veer.tv/oembed"; 
		$this->providers['%^http:\/\/veervr.tv\/videos\/.*%i'] = "https://api.veervr.tv/oembed"; 
		$this->providers['%^http:\/\/www.vevo.com\/.*%i'] = "https://www.vevo.com/oembed"; 
		$this->providers['%^https:\/\/www.vevo.com\/.*%i'] = "https://www.vevo.com/oembed"; 
		$this->providers['%^http:\/\/www.videojug.com\/film\/.*%i'] = "http://www.videojug.com/oembed.json"; 
		$this->providers['%^http:\/\/www.videojug.com\/interview\/.*%i'] = "http://www.videojug.com/oembed.json"; 
		$this->providers['%^https:\/\/vidl.it\/.*%i'] = "https://api.vidl.it/oembed"; 
		$this->providers['%^https:\/\/players-cdn-v2.vidmizer.com\/.*%i'] = "https://app-v2.vidmizer.com/api/oembed"; 
		$this->providers['%^http:\/\/.*.vidyard.com\/.*%i'] = "https://api.vidyard.com/dashboard/v1.1/oembed"; 
		$this->providers['%^https:\/\/.*.vidyard.com\/.*%i'] = "https://api.vidyard.com/dashboard/v1.1/oembed"; 
		$this->providers['%^http:\/\/.*.hubs.vidyard.com\/.*%i'] = "https://api.vidyard.com/dashboard/v1.1/oembed"; 
		$this->providers['%^https:\/\/.*.hubs.vidyard.com\/.*%i'] = "https://api.vidyard.com/dashboard/v1.1/oembed"; 
		$this->providers['%^https:\/\/vimeo.com\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/vimeo.com\/album\/.*\/video\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/vimeo.com\/channels\/.*\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/vimeo.com\/groups\/.*\/videos\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/vimeo.com\/ondemand\/.*\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/player.vimeo.com\/video\/.*%i'] = "https://vimeo.com/api/oembed.json"; 
		$this->providers['%^https:\/\/www.viously.com\/.*\/.*%i'] = "https://www.viously.com/oembed"; 
		$this->providers['%^http:\/\/viziosphere.com\/3dphoto.*%i'] = "http://viziosphere.com/services/oembed/"; 
		$this->providers['%^https:\/\/vizydrop.com\/shared\/.*%i'] = "https://vizydrop.com/oembed"; 
		$this->providers['%^https:\/\/vlipsy.com\/.*%i'] = "https://vlipsy.com/oembed"; 
		$this->providers['%^https:\/\/www.vlive.tv\/video\/.*%i'] = "https://www.vlive.tv/oembed"; 
		$this->providers['%^http:\/\/vlurb.co\/video\/.*%i'] = "https://vlurb.co/oembed.json"; 
		$this->providers['%^https:\/\/vlurb.co\/video\/.*%i'] = "https://vlurb.co/oembed.json"; 
		$this->providers['%^https:\/\/article.voxsnap.com\/.*\/.*%i'] = "https://data.voxsnap.com/oembed"; 
		$this->providers['%^https:\/\/watch.wave.video\/.*%i'] = "https://embed.wave.video/oembed"; 
		$this->providers['%^https:\/\/embed.wave.video\/.*%i'] = "https://embed.wave.video/oembed"; 
		$this->providers['%^https:\/\/.*.wiredrive.com\/.*%i'] = "http://*.wiredrive.com/present-oembed/"; 
		$this->providers['%^https:\/\/fast.wistia.com\/embed\/iframe\/.*%i'] = "https://fast.wistia.com/oembed.json"; 
		$this->providers['%^https:\/\/fast.wistia.com\/embed\/playlists\/.*%i'] = "https://fast.wistia.com/oembed.json"; 
		$this->providers['%^https:\/\/.*.wistia.com\/medias\/.*%i'] = "https://fast.wistia.com/oembed.json"; 
		$this->providers['%^http:\/\/.*.wizer.me\/learn\/.*%i'] = "http://app.wizer.me/api/oembed.json"; 
		$this->providers['%^https:\/\/.*.wizer.me\/learn\/.*%i'] = "http://app.wizer.me/api/oembed.json"; 
		$this->providers['%^http:\/\/.*.wizer.me\/preview\/.*%i'] = "http://app.wizer.me/api/oembed.json"; 
		$this->providers['%^https:\/\/.*.wizer.me\/preview\/.*%i'] = "http://app.wizer.me/api/oembed.json"; 
		$this->providers['%^https:\/\/wokwi.com\/share\/.*%i'] = "https://wokwi.com/api/oembed"; 
		$this->providers['%^https:\/\/web.xpression.jp\/video\/.*%i'] = "https://web.xpression.jp/api/oembed"; 
		$this->providers['%^http:\/\/yesik.it\/.*%i'] = "http://yesik.it/s/oembed"; 
		$this->providers['%^http:\/\/www.yesik.it\/.*%i'] = "http://yesik.it/s/oembed"; 
		$this->providers['%^http:\/\/.*.yfrog.com\/.*%i'] = "http://www.yfrog.com/api/oembed"; 
		$this->providers['%^http:\/\/yfrog.us\/.*%i'] = "http://www.yfrog.com/api/oembed"; 
		$this->providers['%^https:\/\/.*.youtube.com\/watch.*%i'] = "https://www.youtube.com/oembed"; 
		$this->providers['%^https:\/\/.*.youtube.com\/v\/.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/youtube.com\/watch.*%i'] = "https://www.youtube.com/oembed"; 
		$this->providers['%^https:\/\/youtube.com\/v\/.*%i'] = "https://www.youtube.com/oembed"; 
		$this->providers['%^https:\/\/youtu.be\/.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/youtube.com\/shorts.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/.*.youtube.com\/shorts.*%i'] = "https://www.youtube.com/oembed"; 
		$this->providers['%^https:\/\/youtube.com\/playlist\?list=.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/.*.youtube.com\/playlist\?list=.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/youtube.com\/live.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/.*.youtube.com\/live.*%i'] = "https://www.youtube.com/oembed";
		$this->providers['%^https:\/\/app.zeplin.io\/project\/.*\/screen\/.*%i'] = "https://app.zeplin.io/embed"; 
		$this->providers['%^https:\/\/app.zeplin.io\/project\/.*\/screen\/.*\/version\/.*%i'] = "https://app.zeplin.io/embed"; 
		$this->providers['%^https:\/\/app.zeplin.io\/project\/.*\/styleguide\/components?coid=.*%i'] = "https://app.zeplin.io/embed"; 
		$this->providers['%^https:\/\/app.zeplin.io\/styleguide\/.*\/components?coid=.*%i'] = "https://app.zeplin.io/embed"; 
		$this->providers['%^https:\/\/app.zingsoft.com\/embed\/.*%i'] = "https://app.zingsoft.com/oembed"; 
		$this->providers['%^https:\/\/app.zingsoft.com\/view\/.*%i'] = "https://app.zingsoft.com/oembed"; 
		$this->providers['%^https:\/\/.*.znipe.tv\/.*%i'] = "https://api.znipe.tv/v3/oembed/";
		$this->providers['%^https:\/\/rutube.ru\/.*%i'] = "https://rutube.ru/api/oembed/"; 
		
    }
    
	
	function fetch($provider_url, $content_url, $args = array() ){
	
		$args['width'] = isset($args['width']) ? $args['width'] : '';
		$args['height'] = isset($args['height']) ? $args['height'] : '';
		
		$params = array('url' => $content_url,'maxwidth' => $args['width'],'maxheight' => $args['height'],'format' => 'json');

		if( stripos ( $provider_url, "https://graph.facebook.com/" ) !== false ) {

			if (!$this->facebook_app_id) {

				include (ENGINE_DIR . '/data/socialconfig.php');
				$this->facebook_app_id = $social_config['fcid'];
				$this->facebook_app_secret = $social_config['fcsecret'];
				
			}

			$params['access_token'] = $this->facebook_app_id . '|' . $this->facebook_app_secret;
		
		}
	
		if( stripos ( $provider_url, "https://www.youtube.com/" ) !== false ) {
			if( !$params['maxwidth'] ) $params['maxwidth'] = 560;
			if( !$params['maxheight'] ) $params['maxheight'] = ceil($params['maxwidth'] / 1.78);
		}

		if( !$params['maxwidth'] ) {
			unset($params['maxwidth']);
		}
		
		if( !$params['maxheight'] ) {
			unset($params['maxheight']);
		}
		
		$query_string = http_build_query($params);	

		$result_json = $this->queryProvider($provider_url."?".$query_string);

		if($result_json['success']){
		
			$result = json_decode(trim($result_json['data']), false);
			
			if(is_object($result)){
					
				return $result;
				
			}else{
			
				return false;
			}
				
			
		}
		
		return false;
	
	}
	
	function getHtml($url, $args){
	
		$url = trim($url);
		
		foreach ($this->providers as $regex => $provider_url) {
			if(preg_match($regex,$url)){
		    	$provider = $provider_url;
		    	break;
		    }
		}
		
		if( isset($provider) AND $provider ){
		
			if($data = $this->fetch($provider, $url, $args)){

				return $this->toHtml($data, $args);
				
			}else{
			
				return false;
				
			}
		
		}else{
		
			return false;
		}
	
	}
	
	function toHtml($data, $args){
		global $config;
		
 		if(is_object($data) || !empty($data->type)){
 			
			switch($data->type){
				case 'photo':
					
					if( empty($data->url) ){
						
						return false;
					
					} else {
					
						$title = (!empty($data->title)) ? $data->title : '';
						
						$style = "";
						
						if( $args['width'] ) {
							$style = "style=\"width:100%;max-width:".intval($args['width'])."px;";
							
							if($args['height']) {
								$style .= "height:auto;max-height:".intval($args['height'])."px;";
							}
							
							$style .= "\"";
							
						}
					
						$html = '<img src="' . $this->escapeHTML($this->safeUrl($data->url)) . '" alt="' . $this->escapeHTML($title) . '" ' . $style . ' />';
					}
					
					break;
					
				case 'video':
				case 'rich':
					$html = ( !empty($data->html) ) ? $data->html : false;
					break;
					
				case 'link':
					$html = ( !empty($data->title) ) ? '<a href="' . $this->safeUrl($data->url) . '">' . $this->escapeHTML($data->title) . '</a>' : false;
					break;
				
				default:
					return false;
			}
			
			return $html;
		
		}else{
		
			return false;	
		
		}

	
	}
	
	function queryProvider($url){

		$result = array();
		
		if (stripos($url, "http://") !== 0 AND stripos($url, "https://") !== 0) {
			return false;
		}
	
		$ch = curl_init($url);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		
		if($data = curl_exec($ch)){

			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if($http_code >= 200 && $http_code < 300){
				$result['success'] = true;
				$result['data'] = $data;
				$result['http_code'] = $http_code;
			}else{
				$result['success'] = false;
				$result['http_code'] = $http_code;
				$result['url'] = $url;
			}
			
		}else{
			$result['success'] = false;
			$result['curl_error_code'] = curl_errno($ch);
		};
		
		curl_close($ch);

		return $result;
	}
	
	function safeUrl($url){
		return (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) ? $url : "";
	}
	
	function escapeHTML($html){
		
		return htmlspecialchars( strip_tags($html), ENT_QUOTES, 'UTF-8' );
	}

}
