<article class="box story">
	<div class="box_in">
		<h1 class="title h1">Личные сообщения</h1>
		<div class="pm-box">
			<nav id="pm-menu">
				[inbox]<span>Список сообщений</span>[/inbox]
				[new_pm]<span>Написать новое сообщение</span>[/new_pm]
			</nav>
			<div class="pm_status">
				{pm-progress-bar}
				{proc-pm-limit} % / ({pm-limit} сообщений)
			</div>
		</div>
[pmlist]
<div class="pmlist">
	{pmlist}
</div>
[/pmlist]

[newpm]
<h4 class="heading">Создать сообщение</h4>
<div class="addform addpm">
	<ul class="ui-form">
		<li class="form-group combo">
			<div class="combo_field">
				<input placeholder="Имя адресата" type="text" name="name" value="{author}" class="wide" required>
			</div>
			<div class="combo_field">
				<input placeholder="Тема сообщения" type="text" name="subj" value="{subj}" class="wide" required>
			</div>
		</li>
		<li id="comment-editor">{editor}</li> 
	[recaptcha]
		<li>{recaptcha}</li>
	[/recaptcha]
	[question]
		<li class="form-group">
			<label for="question_answer">Вопрос: {question}</label>
			<input placeholder="Ответ" type="text" name="question_answer" id="question_answer" class="wide" required>
		</li>
	[/question]
	</ul>
	<div class="form_submit">
		[sec_code]
			<div class="c-captcha">
				{sec_code}
				<input placeholder="Повторите код" title="Введите код указанный на картинке" type="text" name="sec_code" id="sec_code" required>
			</div>
		[/sec_code]
		<button class="btn btn-big" type="submit" name="add"><b>Отправить</b></button>
		<button class="btn-border btn-big" type="button" onclick="dlePMPreview()">Предпросмотр</button>
	</div>
</div>
[/newpm]

	</div>
</article>

[readpm]
<div class="comments">
	<div class="box">
		<h4 class="heading">{subj}</h4>
		<div class="com_list">
[messages]
<div class="comment">
	<div class="com_info">
		<div class="avatar">
			<span class="cover" style="background-image: url({foto});">{login}</span>
			[online]<span class="com_online" title="{login} - онлайн">Онлайн</span>[/online]
		</div>
		<div class="com_user">
			<b class="name">{author}</b>
			<span class="grey">
				написал: {date}
			</span>
		</div>
		<div class="meta">
			<ul class="left">
				<li class="edit_btn" title="Редактировать">
					[pm-edit]<i title="Редактировать">Редактировать</i>[/pm-edit]
				</li>
				<li class="reply grey" title="Цитировать">
					[reply]<svg class="icon icon-reply"><use xlink:href="#icon-coms"></use></svg><span>Цитировать</span>[/reply]
				</li>
				<li class="reply grey" title="Игнорировать">
					[ignore]<svg class="icon icon-reply"><use xlink:href="#icon-dislike"></use></svg><span>Игнорировать</span>[/ignore]
				</li>
				<li class="complaint" title="Жалоба">
					[complaint]<svg class="icon icon-bad"><use xlink:href="#icon-bad"></use></svg><span class="title_hide">Жалоба</span>[/complaint]
				</li>
				<li class="del" title="Удалить">
					[del]<svg class="icon icon-cross"><use xlink:href="#icon-cross"></use></svg><span class="title_hide">Удалить</span>[/del]
				</li>
			</ul>
		</div>
	</div>
	<div class="com_content">
		<div class="text">{text}</div>
		[signature]<div class="signature">--------------------<br>{signature}</div>[/signature]
	</div>
</div>
[/messages]
		</div>
<div class="box_in form-sep">
	<h3>Ответить на сообщение</h3>
	{editor}
	<br><button class="btn btn-big" type="submit" name="submit" title="Ответить"><b>Ответить</b></button>
</div>
	</div>
</div>
[/readpm]