<article class="post static">
  <h1 class="title">Персональные сообщения</h1>
  [inbox]Входящие сообщения[/inbox] <br> [new_pm]Отправить сообщение[/new_pm]
</article>
[pmlist]
<div class="ux-form">
  <h3>Список сообщений</h3>
  {pmlist}
</div>
[/pmlist]
[newpm]
<div class="ux-form">
  <h3>Отправка сообщения</h3>
  <ul class="ui-form">
    <li><input placeholder="Получатель" type="text" name="name" value="{author}" class="f_input f_wide"></li>
    <li><input placeholder="Тема" type="text" name="subj" value="{subj}" class="f_input f_wide"></li>
    <li>{editor}</li>
    [sec_code]
    <li>
      <div class="c-captcha-box">
        <label for="sec_code">Повторите код:</label>
        <div class="c-captcha">
          {sec_code}
          <input title="Введите код указанный на картинке" type="text" name="sec_code" id="sec_code" class="f_input" >
        </div>
      </div>
    </li>
    [/sec_code]
    [recaptcha]
    <li>
      {recaptcha}
    </li>
    [/recaptcha]
  </ul>
  <div class="submitline">
    <button class="btn f_wide" name="add" type="submit" name="submit">Отправить сообщение</button>
  </div>
</div>
[/newpm]
[readpm]
<div class="box commentbox">
  <h3>{subj}</h3>
[messages]
<div class="comment vcard">
  <div class="com-cont clrfix">
    {text}
  </div>
  <div class="com-inf">
    <span class="arg">Сообщение от <b class="fn">{author}</b></span>
    <span class="fast">[reply]<b class="thd">Цитировать</b>[/reply]</span>
    <span class="del">[del]<b class="thd">Удалить</b>[/del]</span>
  </div>
</div>
[/messages]
<div class="ux-form">
  {editor}
  <div class="submitline" style="margin-top: 10px;">
    <button class="btn f_wide" type="submit" name="submit">Ответить</button>
  </div>
</div>
</div>
[/readpm]