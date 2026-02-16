<div class="box">
    <div id="addcomment" class="addcomment">
        <div class="plus_icon"><span>Добавить комментарий</span></div>
        <div class="box_in">
            <h3>Оставить комментарий</h3>
            <ul class="ui-form">
                [not-logged]
                <li class="form-group combo">
                    <div class="combo_field"><input placeholder="Ваше имя" type="text" name="name" id="name" class="wide" value="{guest-name}" required></div>
                    <div class="combo_field"><input placeholder="Ваш e-mail" type="email" name="mail" id="mail" value="{guest-mail}" class="wide"></div>
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
                <div class="c-captcha">
                    {sec_code}
                    <input placeholder="Повторите код" title="Введите код указанный на картинке" type="text" name="sec_code" id="sec_code" required>
                </div>
                [/sec_code]
                <button class="btn btn-big" type="submit" name="submit" title="Отправить комментарий"><b>Отправить комментарий</b></button>
            </div>
        </div>
    </div>
</div>