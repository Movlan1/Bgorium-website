<div id="votes" class="block">
	<h4 class="vote_title">{title}</h4>
	<div class="vote_more"><a href="#" onclick="ShowAllVotes(); return false;">Другие опросы...</a></div>
	[votelist]
	<form method="post" name="vote">
	[/votelist]
		<div class="vote_list">
			{list}
		</div>
	[voteresult]
		<div class="vote_votes grey">Проголосовало: {votes}</div>
	[/voteresult]
	[votelist]
		<input type="hidden" name="vote_action" value="vote">
		<input type="hidden" name="vote_id" id="vote_id" value="{vote_id}">
		<button title="Голосовать" class="btn" type="submit" onclick="doVote('vote'); return false;" ><b>Голосовать</b></button>
		<button title="Результаты опроса" class="vote_result_btn" type="button" onclick="doVote('results'); return false;" >
			<span>
				<i class="vr_1"></i>
				<i class="vr_2"></i>
				<i class="vr_3"></i>
			</span>
		</button>
	</form>
	[/votelist]
</div>
<div class="block_sep"></div>