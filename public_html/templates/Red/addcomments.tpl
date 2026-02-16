<div class="block addcomments_form">
	<div class="wrp">
		<div class="grid_1_4 grid_last right">
			<h4 class="ultrabold">Добавить комментарий</h4>
			<p class="grey">Автору будет очень приятно узнать обратную связь о своей новости.</p>
		</div>
		<div class="grid_3_4">
			<ul class="ui-form">
			[not-logged]
				<li class="form-group combo">
					<div class="combo_field"><input placeholder="Ваше имя" type="text" name="name" id="name" class="wide" value="{guest-name}" required></div>
					<div class="combo_field"><input placeholder="Ваш e-mail" type="email" name="mail" id="mail" class="wide" value="{guest-mail}"></div>
				</li>
			[/not-logged]
				<li id="comment-editor">
					{editor}
					[image-upload]
					<div class="comments-image-uploader-area">
						<a onclick="ShowCommentsUploader(); return false" href="#">Прикрепить изображения</a>
						<div id="hidden-image-uploader" style="display: none">{image-upload}</div>
					</div>
					[/image-upload]
				</li>
			[allow-comments-subscribe]
				<li>{comments-subscribe}</li>
			[/allow-comments-subscribe] 
			[recaptcha]
				<li>{recaptcha}</li>
			[/recaptcha]
			[question]
				<li class="form-group">
					<label for="question_answer">{question}</label>
					<input placeholder="Ответ" type="text" name="question_answer" id="question_answer" class="wide" required>
				</li>
			[/question]
			</ul>
			<div class="form_submit">
			[sec_code]
				<div class="c-capcha">
					{sec_code}
					<input placeholder="Повторите код" title="Введите код указанный на картинке" type="text" name="sec_code" id="sec_code" required>
				</div>
			[/sec_code]
				<button class="btn" type="submit" name="submit" title="Отправить комментарий"><b class="ultrabold">Отправить комментарий</b></button>
			</div>
		</div>
		<div class="clr"></div>
	</div>
</div>