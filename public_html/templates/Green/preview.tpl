[short-preview]
<article class="block story shortstory">
	<h2 class="title">[full-link]{title}[/full-link]</h2>
	<div class="story_top">
		<time class="date grey" datetime="{date=Y-m-d}">[day-news]{date=d/m/Y H:i}[/day-news]</time>
	</div>
	<div class="text">
		{short-story}
	</div>
	<div class="category grey">
		<a href="{category-url}">{category}</a>
	</div>
	<div class="story_tools">
		<div class="story_tools_in">
			<a href="{full-link}" title="Читать подробнее: {title}" class="btn"><span class="more_icon"><i></i><i></i><i></i></span></a>
		</div>
	</div>
</article>
[/short-preview]
[full-preview]
<article class="block story fullstory">
	<h1 class="title р2">{title}</h1>
	<div class="story_top">
		<time class="date grey" datetime="{date=Y-m-d}">[day-news]{date=d/m/Y H:i}[/day-news]</time>
	</div>
	<div class="text">
		{full-story}
		{pages}
	</div>
	<div class="category grey">
		<a href="{category-url}">{category}</a>
	</div>
</article>
[/full-preview]
[static-preview]
<article class="block story">
	<h1 class="h2 title">{description}</h1>
	<div class="text">{static}</div>
	<div class="static_pages">{pages}</div>
</article>
{pages}
[/static-preview]