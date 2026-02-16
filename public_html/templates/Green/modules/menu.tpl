<div class="greenmenu">
	<!-- Поиск -->
	<form id="q_search" method="post">
		<div class="q_search">
			<input id="story" name="story" placeholder="Поиск по сайту..." type="search">
			<button class="q_search_btn" type="submit" title="Найти"><svg class="icon icon-search"><use xlink:href="#icon-search"></use></svg><span class="title_hide">Найти</span></button>
		</div>
		<input type="hidden" name="do" value="search">
		<input type="hidden" name="subaction" value="search">
	</form>
	<!-- / Поиск -->
	<nav class="menu">
		<a[available=main] class="active"[/available] href="/" title="Главная">Главная</a>
		<a[available=feedback] class="active"[/available] href="/index.php?do=feedback" title="Контакты">Контакты</a>
		<a[available=rules] class="active"[/available] href="/rules.html" title="Правила">Правила</a>
		{catmenu}
	</nav>
</div>