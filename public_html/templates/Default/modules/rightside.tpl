[not-available=showfull]
<aside class="rightside">
	<!-- Популярное -->
	<div class="block top_block">
		<h4 class="title"><b>Популярное</b></h4>
		<ol class="topnews">
			{topnews}
		</ol>
	</div>
	<!-- / Популярное -->
	<!-- Баннер 300X250 -->
	<div class="banner banner_300">
		<img src="{THEME}/images/tmp/banner_300x250.png" alt="">
	</div>
	<!-- / Баннер 300X250 -->
	<!-- Баннер 240X400 -->
	<div class="banner banner_240">
		<img src="{THEME}/images/tmp/banner_240x400.png" alt="">
	</div>
	<!-- / Баннер 240X400 -->
	{vote}
	[available=main|cat]
	<!-- Архив -->
	<div class="block arch_block">
		<div class="title h4 title_tabs">
			<h4>Архив</h4>
			<ul>
				<li class="active">
					<a title="В виде календаря" href="#arch_calendar" aria-controls="arch_calendar" data-toggle="tab">
						<svg class="icon icon-calendar"><use xlink:href="#icon-calendar"></use></svg><span class="title_hide">В виде календаря</span>
					</a>
				</li>
				<li>
					<a title="В виде списка" href="#arch_list" aria-controls="arch_list" data-toggle="tab">
						<svg class="icon icon-archive"><use xlink:href="#icon-archive"></use></svg><span class="title_hide">В виде списка</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="tab-content">
			<div class="tab-pane active" id="arch_calendar">{calendar}</div>
			<div class="tab-pane" id="arch_list">{archives}</div>
		</div>
	</div>
	<!-- / Архив -->
	<!-- Изменить оформление -->
	[changeskin]
	<div class="block_bg change_skin">
		<h4 class="title">Изменить оформление</h4>
		<div class="styled_select">
			{changeskin}
			<svg class="icon icon-down"><use xlink:href="#icon-down"></use></svg>
		</div>
	</div>
	[/changeskin]
	<!-- / Изменить оформление -->
	[/available]
	<!-- Теги -->
	<div class="block tags_block">
		<h4 class="title"><b>Теги</b></h4>
		<div class="tag_list">
			{tags}
		</div>
	</div>
	<!-- / Теги -->
	<!-- Последние комментарии -->
	[available=main]
	<div class="block top_block">
		<h4 class="title"><b>Комментарии</b></h4>
		<ul class="lastcomm">
			{customcomments template="modules/lastcomments" available="global" from="0" limit="5" order="date" sort="desc" cache="yes"}
		</ul>
	</div>
	[/available]
	<!-- / Последние комментарии -->
</aside>
[/not-available]