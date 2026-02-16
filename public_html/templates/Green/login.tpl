<div class="loginpane">
	[not-group=5]
	<a id="loginbtn" class="h_btn login" href="{profile-link}">
		<div class="avatar pmnum_{new-pm}">
			<span class="cover" style="background-image: url({foto});">{login}</span>
			<span class="num">{new-pm}</span>
		</div>
		<span class="icon_close">
			<i class="mt_1"></i><i class="mt_2"></i>
		</span>
	</a>
	<div id="login_drop" class="head_drop">
		<div class="hd_pad">
			<div class="login_pane__info">
				<div class="avatar">
					<a href="{profile-link}"><span class="cover" style="background-image: url({foto});">{login}</span></a>
				</div>
				<b class="name"><a href="{profile-link}">{login}</a></b>
				[admin-link]<div class="login_pane__admin"><a href="{admin-link}" target="_blank">Админпанель</a></div>[/admin-link]
			</div>
			<ul class="login_pane__menu">
				<li><a href="{pm-link}">Сообщения <span class="right"><b>{new-pm}</b> из {all-pm}</span></a></li>
				<li><a href="{favorites-link}">Закладки <span class="right"><b>{favorite-count}</b></span></a></li>
				<li><a href="{newposts-link}">Непрочитанные новости</a></li>
			</ul>
			<div class="login_pane__foot">
				<a class="right" href="{logout-link}">Выход</a>
				<a href="{addnews-link}"><span class="plus_icon"><span>+</span></span>Новость</a>
			</div>
		</div>
	</div>
	[/not-group]
	[group=5]
	<a href="#" id="loginbtn" class="h_btn login">
		<svg class="icon n_ico icon-login"><use xlink:href="#icon-login"></use></svg>
		<svg class="icon m_ico icon-login_m"><use xlink:href="#icon-login_m"></use></svg>
		<span class="title_hide">Войти</span>
		<span class="icon_close">
			<i class="mt_1"></i><i class="mt_2"></i>
		</span>
	</a>
	<div id="login_drop" class="head_drop">
		<div class="hd_pad">
			<form class="login_form" method="post">
				<div class="soc_links">
					[vk]<a href="{vk_url}" target="_blank" class="soc_vk">
						<svg class="icon icon-vk"><use xlink:href="#icon-vk"/></svg>
					</a>[/vk]
					[yandex]<a href="{yandex_url}" target="_blank" class="soc_ya">
						<svg class="icon icon-ya"><use xlink:href="#icon-ya"/></svg>
					</a>[/yandex]
					[facebook]<a href="{facebook_url}" target="_blank" class="soc_fb">
						<svg class="icon icon-fb"><use xlink:href="#icon-fb"/></svg>
					</a>[/facebook]
					[google]<a href="{google_url}" target="_blank" class="soc_gp">
						<svg class="icon icon-gp"><use xlink:href="#icon-gp"/></svg>
					</a>[/google]
					[odnoklassniki]<a href="{odnoklassniki_url}" target="_blank" class="soc_od">
						<svg class="icon icon-od"><use xlink:href="#icon-od"/></svg>
					</a>[/odnoklassniki]
					[mailru]<a href="{mailru_url}" target="_blank" class="soc_mail">
						<svg class="icon icon-mail"><use xlink:href="#icon-mail"/></svg>
					</a>[/mailru]
				</div>
				<ul>
					<li class="form-group">
						<label for="login_name">{login-method}</label>
						<input placeholder="{login-method}" type="text" name="login_name" id="login_name" class="wide">
					</li>
					<li class="form-group">
						<label for="login_password">Пароль</label>
						<input placeholder="Пароль" type="password" name="login_password" id="login_password" class="wide">
					</li>
					<li>
						<button class="btn" onclick="submit();" type="submit" title="Войти">
							<b>Войти</b>
						</button>
					</li>
				</ul>
				<input name="login" type="hidden" id="login" value="submit">
				<div class="login_form__foot">
					<a class="right" href="{registration-link}"><b>Регистрация</b></a>
					<a href="{lostpassword-link}">Забыли пароль?</a>
				</div>
			</form>
		</div>
	</div>
	[/group]
</div>