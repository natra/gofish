<?php

	function save_game_to_session(Game $game) {
		$_SESSION['game'] = serialize($game);
	}

?>