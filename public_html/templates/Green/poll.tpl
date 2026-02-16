<div class="poll_block">
	<div class="poll_block_in">
	<h4 class="title">{question}</h4>
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
		<button title="Голосовать" class="btn" type="submit" onclick="doPoll('vote', '{news-id}'); return false;" ><b>Голосовать</b></button>
		<button title="Результаты" class="btn" type="button" onclick="doPoll('results', '{news-id}'); return false;" ><b>Результаты</b></button>
	[/not-voted]
	</div>
</div>