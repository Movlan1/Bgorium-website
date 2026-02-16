<div class="poll_block">
	<div class="poll_title">
		<b>{question}</b>
	</div>
	<div class="vote_list">
		{list}
	</div>
[voted]
	<div class="vote_votes grey">Проголосовало: {votes}</div>
[/voted]
[closed]
	<div class="vote_votes grey"><br>Данный опрос был закрыт {close-date}</div>
[/closed]
[not-voted]
	<button title="Голосовать" class="btn" type="submit" onclick="doPoll('vote', '{news-id}'); return false;" >
		<b class="ultrabold">Голосовать</b>
	</button>
[/not-voted]
</div>