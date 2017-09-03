<?php

include_once 'Game.php';

class Display {
	
	private $game;

	function __construct($game){
		$this->game = $game;
	}

	function draw($mode = 0, $cards = array()){
		$tags = $this->makeTags();
		if($mode == 1){
			
			//wypisuje karty do wyboru dla gracza
			$uniqueNums = $this->game->getPlayers()[Game::HUMAN_ID]->getUniqueCardNumbers();
			$tags['{human_panel}'] = "<div class='pick-card-box'>";
			foreach ($uniqueNums as $num) {
				$tags['{human_panel}'] .= "<button class='picked_num btn btn-primary' value='$num'>$num</button>";
			}
			$tags['{human_panel}'] .= '</div>';
			$tags['{opponent_msg}'] = "";
			$tags['{player_label_class}'] = " active_player";

		} elseif ($mode == 2) {
			
			//maszyna oddaje karty
			$tags['{cards_given_by_opponent}'] = $this->drawGivenCardsByOpponent($cards);
			if(count($cards) == 1){
				$tags['{human_panel}'] = "Pobierz kartę.";
			} else {
				$tags['{human_panel}'] = "Pobierz karty.";
			}
			$tags['{player_label_class}'] = " active_player";
			
		} elseif ($mode == 3) {
			
			//HUMAN_GOES TO FISH
			$tags['{opponent_msg_container}'] = $this->generateOpponentMsgContainer("Go fish!");
			$tags['{pick_card_disable}'] = "";
			$tags['{player_label_class}'] = " active_player";
			$tags['{human_panel}'] = "Pobierz kartę.";

		} elseif ($mode == 4) {
			//MASHINES MOVES
			$wantedCard = $this->game->getPlayers()[Game::MASHINE_ID]->getWantedCard();
			
			$tags['{opponent_label_class}'] = " active_player";
			$tags['{opponent_msg_container}'] = $this->generateOpponentMsgContainer($wantedCard."?");
			$tags['{go_fish_disable}'] = "";
			$tags['{human_cards}'] = $this->drawHumanCardTable(4); 
			$tags['{wanted_card}'] = $wantedCard;

		} else {
			$tags['{game_content}'] = "<a class='btn btn-priamry new_game_button'>Nowa gra</a>";
		}
		
		$htmlTemplate = str_replace("{game_content}",file_get_contents('game_container.html'),file_get_contents('view.html'));
		echo $this->replaceTags($htmlTemplate, $tags);
	}

	function makeTags($mode = false){
		$tags['{opponent_cards_num}'] 		= "Ilość kart: <span>" . $this->game->getPlayers()[1]->getNumOfCards() . "</span>";
		$tags['{opponent_books_num}'] 		= $this->game->getPlayers()[1]->getBooks();
		$tags['{human_cards_num}'] 			= "Ilość kart: <span>" . $this->game->getPlayers()[0]->getNumOfCards() . "</span>";
		$tags['{human_books_num}'] 			= $this->game->getPlayers()[0]->getBooks();
		$tags['{human_cards}'] 				= $this->drawHumanCardTable();
		$tags['{opponent_cards}'] 			= $this->drawMashineCards();
		$tags['{deck_cards}'] 				= $this->drawDeck();
		$tags['{human_panel}'] 				= '';
		$tags['{pick_card_disable}'] 		= " disabled";
		$tags['{go_fish_disable}'] 			= " disabled";
		$tags['{opponent_msg}'] 			= "";
		$tags['{player_msg}'] 				= "";
		$tags['{wanted_card}'] 				= "";
		$tags['{end_game_btn_hidden}'] 		= "inline";
		$tags['{logo}'] 					= "";
		$tags['{opponent_msg_container}'] 	= "";
		$tags['{player_msg_container}'] 	= "";
		$tags['{cards_given_by_opponent}'] 	= "";
		$tags['{opponent_label_class}'] 	= "";
		$tags['{player_label_class}'] 		= "";

		return $tags;
	}

	function drawHumanCardTable($mode = false){
		$card_table = '<div class="human_cards_table">';
		foreach($this->game->getPlayers()[Game::HUMAN_ID]->getCards() as $card){
			$red_symbol = (in_array($card->getSuit(), [1,3]) ? true : false);
			$prepared_card = $this->prepareCardDisplay($card);
			if($mode == 4){
				$card_table .= "<a data-card='".$prepared_card[0]." ".$card->getSuit()."' data-number='".$prepared_card[0]."' class='human_card active".($red_symbol ? " red_card" : '')."'>";
				$card_table .= "<div>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
				$card_table .= "<div class='rotated_symbol'>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
				$card_table .= "</a>";
			} else {
				$card_table .= "<a data-card='".$prepared_card[0]." ".$card->getSuit()."' data-number='".$prepared_card[0]."' class='human_card".($red_symbol ? " red_card" : '')."'>";
				$card_table .= "<div>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
				$card_table .= "<div class='rotated_symbol'>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
				$card_table .= "</a>";
			}
		}
		$card_table .= "</div>";
		return $card_table;
	}

	function drawHumanCard($card){
		$prepared_card = $this->prepareCardDisplay($card);
		$red_symbol = (in_array($card->getSuit(), [1,3]) ? true : false);

		$printed_card = "<a data-card='".$prepared_card[0]." ".$card->getSuit()."' data-number='".$prepared_card[0]."' class='human_card".($red_symbol ? " red_card" : '')."'>";
		$printed_card .= "<div>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
		$printed_card .= "<div class='rotated_symbol'>".$prepared_card[0]."<span class='human_card_suit'>".$prepared_card[1]."</span></div>";
		$printed_card .= "</a>";

		return $printed_card;
	}

	function drawMashineCards(){
		$str = "";
		foreach($this->game->getPlayers()[Game::MASHINE_ID]->getCards() as $card){
			$str .= "<div class='small_card opponent_card'></div>";
		}
		return $str;
	}

	function drawGivenCardsByOpponent($cards){
		$str = "";
		foreach ($cards as $card){
			$red_symbol = (in_array($card->getSuit(), [1,3]) ? true : false);
			$str .= "<a class='given_card_by_mashine".($red_symbol ? " red_card" : '')."' data-card='{$card->getNumber()} {$card->getSuit()}'>";
			$str .= "<div>".$card->getNumber()."<span class='given_card_by_mashine_suit'>".$card->displaySuit()."</span></div>";
			$str .= "<div class='rotated_symbol'>".$card->getNumber()."<span class='given_card_by_mashine_suit'>".$card->displaySuit()."</span></div>";
			$str .= "</a>";
		}
		return $str;
	}

	function replaceTags($htmlTemplate, $tags){
		foreach ($tags as $key => $value) {
			$htmlTemplate = str_replace($key, $value, $htmlTemplate);
		}
		return $htmlTemplate;
	}

	function prepareCardDisplay($card){
		$suitDisplay = '';
		if($card->getSuit() == 0){
			$suitDisplay = "&clubs;";
		} elseif ($card->getSuit() == 1){
			$suitDisplay = "&diams;";
		} elseif ($card->getSuit() == 2){
			$suitDisplay = "&spades;";
		} elseif ($card->getSuit() == 3){
			$suitDisplay = "&hearts;";
		}
		return [$card->getNumber(), $suitDisplay];
	}

	function generateOpponentMsgContainer($message){
		if($message == "Go fish!"){
			return '<div class="opponent_msg msg_container"><img src="img/fish-blue.png" title="Idź na ryby!" style="margin-top: 20px; vertical-align: top;"></div>';
		} else {
			return '<div class="opponent_msg msg_container">' . $message . '</div>';
		}
	}

	function generatePlayerMsgContainer($message){
		$str = '<div class="player_msg_container msg_container">';
		$str .= '<div class="player_msg">'.$message.'</div>';
		$str .= "</div>";
		return $str;
	}

	function generateUniqueNums(){
		$uniqueNums = $this->game->getPlayers()[Game::HUMAN_ID]->getUniqueCardNumbers();
		$str = "<div class='pick-card-box'>";
		foreach ($uniqueNums as $num) {
			$str .= "<button class='picked_num btn btn-primary' value='$num'>$num</button>";
		}
		$str .= "</div>";
		return $str;
	}

	function drawDeck(){
		$str = "";
		$cards_in_deck = count($this->game->getDeck());
		foreach($this->game->getDeck() as $key => $card){
			if($key < ($cards_in_deck - 10) && $cards_in_deck > 10){
				$str .= "<div class='small_card deck_card non-visible'></div>";
			} else {
				$str .= "<div class='small_card deck_card visible' style='margin-bottom: ".($key - ($cards_in_deck - 11))."px'></div>";
			}
		}
		return $str;
	}

	function drawEndGameView(){

		$msg = ($this->game->getWinner() == 'human' ? "Brawo! Wygrałeś!" : "Niestety! Tym razem przegrałeś!");
		$tags = array(
											'{end_game_heading}' 	=> $msg
										,	'{logo}' 				=> ''
										,	'{end_game_btn_hidden}' => 'none'
					);

		$htmlTemplate = str_replace("{game_content}",file_get_contents('endGamePanel.html'),file_get_contents('view.html'));
		echo $this->replaceTags($htmlTemplate, $tags);
	}

	static function drawInitialPage(){
		$htmlTemplate = file_get_contents('view.html');
		echo str_replace(["{game_content}", "{end_game_btn_hidden}", "{logo}"], ["<a class='btn btn-primary btn-lg new_game_button'>Nowa gra</a>", "none", '<img src="img/main.gif" width="600" style="margin: 20px 0 50px;" class="main_img">'], $htmlTemplate);
	}
}
?>