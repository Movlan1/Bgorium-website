<div class="comment[online] online[/online]" id="{comment-id}">
	[online]<span class="status online">Онлайн</span>[/online]
	<span class="status offline">Офлайн</span>
	<div class="com_info">
		<div class="avatar">
			[profile]<span class="cover" style="background-image: url({foto});">{login}</span>[/profile]
		</div>
		<div class="com_user">
			<b class="name">{author}</b>
			<span class="grey date">{date}</span>
		</div>
		[rating]
		<div class="rate">
			[rating-type-1]<div class="rate_stars">{rating}</div>[/rating-type-1]
			[rating-type-2]
			<div class="rate_like">
			[rating-plus]
				<svg class="icon icon-like"><use xlink:href="#icon-like"></use></svg>
				{rating}
			[/rating-plus]
			</div>
			[/rating-type-2]
			[rating-type-3]
			<div class="rate_like-dislike">
				[rating-plus]<span class="plus_icon" title="Нравится"><span>+</span></span>[/rating-plus]
				{rating}
				[rating-minus]<span class="plus_icon minus" title="Не нравится"><span>-</span></span>[/rating-minus]
			</div>
			[/rating-type-3]
			[rating-type-4]
			<div class="rate_like-dislike">
				<span class="ratingtypeplusminus ratingplus">{likes}</span>
				[rating-plus]<span class="plus_icon" title="Нравится"><span>+</span></span>[/rating-plus]
				<span class="ratingtypeplusminus ratingminus">{dislikes}</span>
				[rating-minus]<span class="plus_icon minus" title="Не нравится"><span>-</span></span>[/rating-minus]
			</div>
			[/rating-type-4]
		</div>
		[/rating]
	</div>
	<div class="com_content">
		[available=lastcomments|search]<h4 class="title">{news_title}</h4>[/available]
		<div class="text">{comment}</div>
		[images]<div class="signature">--------------------</div><div class="clrfix">{images}</div>[/images]
		[signature]<div class="signature">--------------------<br>{signature}</div>[/signature]
	</div>
	<div class="com_tools">
	[not-group=5]
		<div class="mass">{mass-action}</div>
		<span class="edit_btn">
		[com-edit]
			<i title="Редактировать">Редактировать</i>
		[/com-edit]
		</span>
	[/not-group]
		<div class="com_tools_links grey">
			[fast]<svg class="icon icon-reply"><use xlink:href="#icon-reply"></use></svg><span>Цитировать</span>[/fast]
			[treecomments] 
			[reply]<svg class="icon icon-reply"><use xlink:href="#icon-reply"></use></svg><span>Ответить</span>[/reply]
			[/treecomments]
			[complaint]<svg class="icon icon-compl"><use xlink:href="#icon-compl"></use></svg><span>Жалоба</span>[/complaint]
			[com-del]<svg class="icon icon-del"><use xlink:href="#icon-del"></use></svg><span>Удалить</span>[/com-del]
		</div>
	</div>
</div>