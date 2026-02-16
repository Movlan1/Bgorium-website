<article class="block story">
	<h1 class="title h2">{header-title}</h1>
	<ul class="ui-form">
		<li class="form-group imp">
			<label for="title">Заголовок</label>
			<input type="text" name="title" id="title" value="{title}" class="wide" required>
		</li>
		[urltag]
		<li class="form-group imp">
			<label for="alt_name">URL новости</label>
			<input type="text" name="alt_name" id="alt_name" value="{alt-name}" class="wide">
		</li>
		[/urltag]
		<li class="form-group imp">
			<label for="category">Категория</label>
			{category}
		</li>
		<li class="form-group">
			<label><a href="#" onclick="$('.addvote').toggle();return false;"><span class="plus_icon circle"><span>+</span></span> Добавить Опрос</a></label>
		</li>
		<li class="form-group addvote" style="display:none;">
			<label for="vote_title" >Заголовок опроса</label>
			<input type="text" name="vote_title" value="{votetitle}" class="wide" />
		</li>
		<li class="form-group addvote" style="display:none;">
			<label for="frage" >Вопрос</label>
			<input type="text" name="frage" value="{frage}" class="wide" />
		</li>
		<li class="form-group addvote" style="display:none;">
			<label for="vote_body" >Список ответов</label>
			<textarea name="vote_body" rows="5" class="wide" placeholder="Каждая новая строка является новым вариантом ответа">{votebody}</textarea><label class="form-check-label"><input class="form-check-input" type="checkbox" name="allow_m_vote" value="1" {allowmvote}><span>Разрешить выбор нескольких вариантов</span></label>
		</li>
		[allow-shortstory]
		<li class="form-group imp">
			<label for="short_story">Краткое описание</label>
			{shortarea}
		</li>
		[/allow-shortstory]
		[allow-fullstory]
		<li class="form-group">
			<label for="full_story">Полное описание</label>
			{fullarea}
		</li>
		[/allow-fullstory]
		<li class="form-group">
			<table style="width:100%">
				{xfields}
			</table>
		</li>
		<li class="form-group">
			<label for="alt_name">Ключевые слова</label>
			<input placeholder="Вводите через запятую" type="text" name="tags" id="tags" value="{tags}" maxlength="150" autocomplete="off" class="wide">
		</li>
		<li class="form-group">
			<div class="admin_checkboxs">{admintag}</div>
		</li>
	[recaptcha]
		<li class="form-group">{recaptcha}</li>
	[/recaptcha]
	[question]
		<li class="form-group">
			<label for="question_answer">{question}</label>
			<input placeholder="Введите ответ" type="text" name="question_answer" id="question_answer" class="wide" required>
		</li>
	[/question]
	</ul>
	<p style="margin: 20px 0 0 0;" class="grey"><span style="color: #e85319">*</span> — поля отмеченные звездочкой обязательны для заполнения.</p>
	<div class="form_submit">
		[sec_code]
			<div class="c-capcha">
				{sec_code}
				<input placeholder="Повторите код" title="Введите код указанный на картинке" type="text" name="sec_code" id="sec_code" required>
			</div>
		[/sec_code]
		<button class="btn" type="submit" name="add"><b>Отправить</b></button>
		<button id="add_news_preview" class="btn" onclick="preview()" type="submit" name="nview"><b>Предпросмотр</b></button>
	</div>
</article>