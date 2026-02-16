[searchposts]
	[fullresult]
		{include file="shortstory.tpl"}
	[/fullresult]
	[shortresult]
	<div class="search_head_line block story">
		[not-group=5]
		<ul class="story_icons">
			<li class="edit_btn">
				[edit]<i title="Редактировать">Редактировать</i>[/edit]
			</li>
		</ul>
		[/not-group]
		<h5 class="title">[full-link]{title}[/full-link]</h5>
		<div class="story_top"><time class="date grey" datetime="{date=Y-m-d}">[day-news]{date=d/m/Y H:i}[/day-news]</time></div>
		<div class="text">
			{short-story limit="120"}...
		</div>
	</div>
	[/shortresult]
[/searchposts]
[searchcomments]
	[fullresult]
	<div class="block">
		{include file="comments.tpl"}
	</div>
	[/fullresult]
	[shortresult]
	<div class="search_head_line block story">
		<h5 class="title">{news_title}</h5>
		<div class="story_top"><time class="date grey" datetime="{date=Y-m-d}">{date=d/m/Y H:i}</time></div>
		<div class="text">
			{comment limit="200"}
		</div>
	</div>
	[/shortresult]
[/searchcomments]