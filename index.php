<?php
error_reporting(E_ALL);
session_start();

require 'Game.php';
ob_start();
require 'Display.php';
ob_end_clean();
require 'functions.php';


if (isset($_GET['stop_game'])) {
	$_SESSION['game'] = null;
	session_destroy();
	header("Location: /gofish");
}

if (isset($_POST['new_game'])){
	$game = new Game;	
} elseif (isset($_SESSION['game'])){
	$game = unserialize($_SESSION['game']);
	if (!($game instanceof Game)) {
		session_destroy();
		throw new Exception('Could not load the game! Please refresh.');
	}
} else {
	Display::drawInitialPage();
	exit;
}

if(isset($_POST['picked_num'])){
	$game->setState(Game::MASHINE_IS_ASKED);
} elseif(isset($_POST['go_fish'])){
	$game->setState(Game::MASHINE_GOES_TO_FISH);
} elseif(isset($_POST['pick_card'])){
	$game->setState(Game::HUMAN_GOES_TO_FISH);
} elseif(isset($_POST['given_cards_by_human'])){
	$game->setState(Game::MASHINE_RECEIVES_CARDS);
} elseif(isset($_POST['given_card_by_mashine'])){
	$game->setState(Game::HUMAN_RECEIVES_CARDS);
}  

$display 	= new Display($game);
$state 		= $game->getState();

if ($state === Game::HAND_START){
	
	$playerToStart = $game->startNewHand();
	if($playerToStart == Game::HUMAN_ID){		
		$arr = array(
								'card_table' => $display->drawHumanCardTable()
							,	'human_panel' => $display->generateUniqueNums()
					);
		$game->setState(Game::HUMAN_MOVE);

	} elseif ($playerToStart == Game::MASHINE_ID){
		$randomNumber = $game->getPlayers()[Game::MASHINE_ID]->pickRandomNumber();
		$game->getPlayers()[Game::MASHINE_ID]->setWantedCard($randomNumber);

		$arr = array(
										'wanted_card' => $randomNumber
									,	'opponent_msg' => $randomNumber.'?'
									,	'card_table' => $display->drawHumanCardTable(4)
									,	'opponent_starts' => true
					);
		$game->setState(Game::MASHINE_MOVE);
		
	}

	$arr['opponent_cards'] 	= $display->drawMashineCards();
	$arr['deck']			= $display->drawDeck();
	echo json_encode($arr);
	save_game_to_session($game);

} elseif ($state == Game::MASHINE_IS_ASKED){
	
	$arr = array();
	$game->setAskedNumber($_POST['picked_num']);
	$returnedCards = $game->askMashine();

	if($returnedCards){
		$game->setReturnedCards($returnedCards);
		$game->getPlayers()[Game::MASHINE_ID]->removeCards($returnedCards);	

		if($game->getPlayers()[Game::MASHINE_ID]->hasEmptyHand()){
			$arr['has_mashine_empty_hand'] = true;
		} else {
			$arr['has_mashine_empty_hand'] = false;
		}
		$arr['num_of_cards_returned'] = count($returnedCards);
		$arr['given_cards_by_opponent'] = $display->drawGivenCardsByOpponent($returnedCards);
		if(count($returnedCards) == 1){
			$arr['game_status'] = "Odbierz kartę";
		} else {
			$arr['game_status'] = "Odbierz karty";
		}

		$game->setState(Game::HUMAN_RECEIVES_CARDS);
		
	} else {
		$game->setState(Game::HUMAN_GOES_TO_FISH);
	}

	save_game_to_session($game);
	echo json_encode($arr);

} elseif ($state == Game::HUMAN_RECEIVES_CARDS){
	
	if(isset($_POST['given_card_by_mashine'])){
		$arr = array();
		$card = $game->getCardFromString($_POST['given_card_by_mashine']);
		$game->getPlayers()[Game::HUMAN_ID]->addCard($card);
		
		if($_POST['is_anything_else'] == 'false'){
			$arr = array(
										'human_panel' 		=> $display->generateUniqueNums()
									,	'human_books_num' 	=> $game->getPlayers()[Game::HUMAN_ID]->getBooks()
						);

			if($game->getPlayers()[Game::MASHINE_ID]->hasEmptyHand()){
				$arr['opponent_cards'] = $display->drawMashineCards();
				$game->getPlayers()[Game::MASHINE_ID]->toogleEmptyHand();
			}
			$game->setState(Game::HUMAN_MOVE);
		} else {
			$game->removeFromReturnedCards($card);
		}

		if($game->checkIfGameEnds()){
			$game->setWinner($arr['winner'] = $game->checkIfGameEnds());
			$game->setState(Game::END_GAME);
		} else {
			if($game->getPlayers()[Game::HUMAN_ID]->hasEmptyHand()){
				$arr['card_table'] = $display->drawHumanCardTable(4);
				$arr['deck'] = $display->drawDeck();
				$game->getPlayers()[Game::HUMAN_ID]->toogleEmptyHand();
			}
		}

		save_game_to_session($game);
		echo json_encode($arr);

	} else {
		$display->draw(2, $game->getReturnedCards());
	}

} elseif ($state == Game::HUMAN_GOES_TO_FISH) {
	
	if(isset($_POST['pick_card'])){
		$picked_card = $game->pickCard(Game::HUMAN_ID);
		$randomNumber = $game->getPlayers()[Game::MASHINE_ID]->pickRandomNumber();
		$game->getPlayers()[Game::MASHINE_ID]->setWantedCard($randomNumber);
		$arr = array(
										'wanted_card' 			=> $randomNumber
									,	'card_table' 			=> $display->drawHumanCardTable(4)
									,	'human_books_num' 		=> $game->getPlayers()[Game::HUMAN_ID]->getBooks()
									,	'deck' 					=> $display->drawDeck()
									,	'picked_card_number' 	=> $picked_card->getNumber()
									,	'picked_card' 			=> $display->drawHumanCard($picked_card)
					);
		$game->setState(Game::MASHINE_MOVE);
		save_game_to_session($game);
		echo json_encode($arr);
	} else {
		$display->draw(3);
	}
	
} elseif ($state == Game::MASHINE_RECEIVES_CARDS) {
	
	if(isset($_POST['given_cards_by_human'])){
		foreach ($_POST['given_cards_by_human'] as $given_card) {
			$given_cards[] = $game->getPlayers()[Game::HUMAN_ID]->convertStringToCard($given_card);	
		}
		$game->getPlayers()[Game::HUMAN_ID]->removeCards($given_cards);
		$game->getPlayers()[Game::MASHINE_ID]->addCards($given_cards);
		
		$arr['opponent_books_num'] = $game->getPlayers()[Game::MASHINE_ID]->getBooks();

		if($game->checkIfGameEnds()){
			$game->setWinner($arr['winner'] = $game->checkIfGameEnds());
			$game->setState(Game::END_GAME);
		} else {
			if($game->getPlayers()[Game::MASHINE_ID]->hasEmptyHand()){
				$arr['opponent_cards'] = $display->drawMashineCards();
				$game->getPlayers()[Game::MASHINE_ID]->toogleEmptyHand();
			}
			if($game->getPlayers()[Game::HUMAN_ID]->hasEmptyHand()){
				$arr['human_cards_table'] = $display->drawHumanCardTable();
				$game->getPlayers()[Game::HUMAN_ID]->toogleEmptyHand();
			}
			$randomNumber = $game->getPlayers()[Game::MASHINE_ID]->pickRandomNumber();
			$game->getPlayers()[Game::MASHINE_ID]->setWantedCard($randomNumber);
			$arr['wanted_card'] 	= $randomNumber;
			$arr['card_table'] 		= $display->drawHumanCardTable(4);
			$arr['deck'] 			= $display->drawDeck();
		}

		save_game_to_session($game);
		echo json_encode($arr);

	} else {
		$display->draw(4);
	}
} elseif($state == Game::MASHINE_GOES_TO_FISH){
	
	if(isset($_POST['go_fish'])){
		$game->pickCard(Game::MASHINE_ID);
		$arr = array(
										'card_table' 			=> $display->drawHumanCardTable()
									,	'human_panel' 			=> $display->generateUniqueNums()
									,	'opponent_books_num' 	=> $game->getPlayers()[Game::MASHINE_ID]->getBooks()
									,	'deck' 					=> $display->drawDeck()
					);

		if($game->getPlayers()[Game::MASHINE_ID]->hasEmptyHand()){
			$arr['opponent_cards'] = $display->drawMashineCards();
			$game->getPlayers()[Game::MASHINE_ID]->toogleEmptyHand();
		}
		save_game_to_session($game);
		echo json_encode($arr);
		
	} else {
		$display->draw(1);
	}

} elseif ($state == Game::HUMAN_MOVE) {
	
	$display->draw(1);

} elseif($state == Game::MASHINE_MOVE){
	
	$display->draw(4);

} elseif($state == Game::END_GAME){
	
	$display->drawEndGameView();

}

?>
