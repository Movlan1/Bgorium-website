<!DOCTYPE html>
<html lang="ru">
<head>
	{headers}
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width"> 
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">

	<link rel="shortcut icon" href="{THEME}/images/favicon.ico">
	<link rel="apple-touch-icon" href="{THEME}/images/touch-icon-iphone.png">
	<link rel="apple-touch-icon" sizes="76x76" href="{THEME}/images/touch-icon-ipad.png">
	<link rel="apple-touch-icon" sizes="120x120" href="{THEME}/images/touch-icon-iphone-retina.png">
	<link rel="apple-touch-icon" sizes="152x152" href="{THEME}/images/touch-icon-ipad-retina.png">

	<link href="{THEME}/css/engine.css" type="text/css" rel="stylesheet">
	<link href="{THEME}/css/styles.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div class="page">
		<div class="wrp">
			<div class="body">
				<!-- Левая колонка -->
				<div id="body_left">
					<div class="body_left_in">
					<!-- Шапка -->
					<header id="header">
						<!-- Логотип -->
						<a class="logotype" href="/" title="DataLife Engine">
							<svg class="icon icon-logo"><use xlink:href="#icon-logo"></use></svg>
						</a>
						<!-- / Логотип -->
						{login}
						<!-- Кнопка вызова меню -->
						<button class="h_btn" id="menubtn">
							<span class="menu_toggle">
								<i class="mt_1"></i><i class="mt_2"></i><i class="mt_3"></i>
							</span>
						</button>
						<!-- / Кнопка вызова меню -->
						[sort]
						<div id="sort">
							<button class="h_btn" id="sortbtn">
								<svg class="icon n_ico icon-sort"><use xlink:href="#icon-sort"></use></svg>
								<svg class="icon m_ico icon-sort_m"><use xlink:href="#icon-sort_m"></use></svg>
								<span class="icon_close">
									<i class="mt_1"></i><i class="mt_2"></i>
								</span>
							</button>
							<div id="sort_drop" class="head_drop">
								<div class="hd_pad">
									<b class="sort_label">Сортировать по</b>
									{sort}
								</div>
							</div>
						</div>
						[/sort]
					</header>
					<!-- / Шапка -->
					</div>
					<!-- Кнопка "Наверх" -->
					<a href="/" id="upper"><svg class="icon icon-up"><use xlink:href="#icon-up"></use></svg></a>
					<!-- / Кнопка "Наверх" -->
					<div id="menu">
						{include file="modules/menu.tpl"}
					</div>
				</div>
				<!-- / Левая колонка -->
				<div class="body_right">
					[available=main]
					<!-- Карусель и новости -->
					<aside id="topside" class="structure">
						<div id="topside_left" class="str_left">
							{include file="modules/carousel.tpl"}
						</div>
						<div id="topside_right" class="str_right">
							<!-- Новые новости в рэндоме -->
							<div class="bignews">
								{custom days="120" template="story_big" limit="2" order="rand" cache="yes"}
							</div>
							<!-- / Новые новости в рэндоме -->
						</div>
					</aside>
					<!-- / Карусель и новости -->
					[/available]
					[not-available=main]
					<div id="breadcrumbs">
						{speedbar}
					</div>
					[/not-available]
					<div id="mainside" class="structure">
						<div class="str_left">
							{info}
							[available=lastcomments]
							<div class="block story">
								<h1 class="title h2">Последние комментарии</h1>
								<div class="com_list">
									{content}
								</div>
							</div>
							[/available]
							[not-available=lastcomments]
							<section id="content">
							{content}
							</section>
							[/not-available]
						</div>
						{include file="modules/rightside.tpl"}
						<div class="rightside_bg"></div>
					</div>
					<footer id="footer">
						{include file="modules/footmenu.tpl"}
						{include file="modules/copyright.tpl"}
					</footer>
				</div>
			</div>
			{include file="modules/foot.tpl"}
		</div>
	</div>
	{AJAX}
	<script src="{THEME}/js/lib.js"></script>
	<script src="{THEME}/js/svgxuse.min.js"></script>
	<script>
		jQuery(function($){
			$.get("{THEME}/images/sprite.svg", function(data) {
			  var div = document.createElement("div");
			  div.innerHTML = new XMLSerializer().serializeToString(data.documentElement);
			  document.body.insertBefore(div, document.body.childNodes[0]);
			});
		});
	</script>
</body>
</html>