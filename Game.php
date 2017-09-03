<?php 

include_once 'Card.php';
include_once 'Player.php';

class Game {
	const CLUBS 		= 0;
	const DIAMONDS 		= 1;
	const SPADES 		= 2;
	const HEARTS 		= 3;

	const N_OF_PLAYERS 			= 2;
	const CARDS_IN_FIRST_HAND 	= 7;
	const HUMAN_ID 				= 0;
	const MASHINE_ID 			= 1;

	const HAND_START 				= 0;
	const MASHINE_MOVE 				= 1;
	const HUMAN_MOVE 				= 2;
	const HUMAN_RECEIVES_CARDS 		= 3;
	const MASHINE_RECEIVES_CARDS 	= 4;
	const HUMAN_GOES_TO_FISH 		= 5;
	const MASHINE_GOES_TO_FISH 		= 6;
	const MASHINE_IS_ASKED 			= 7;
	const END_GAME 					= 8;

	private $players;
	private $points;
	private $state;
	private $askedNumber;
	private $returnedCards;
	private $deck;
	private $winner;

	function __construct(){
		$this->points = array();
		$this->players = array();
		for ($i = 0; $i<self::N_OF_PLAYERS; $i++){
			$this->players[] = new Player($i, $this);
		}
		$this->state = self::HAND_START;
		$this->deck = $this->initializeDeck();
	}

	function startNewHand(){
		$this->distributeCards();
		return $this->getRandomPlayer();
	}

	function distributeCards(){
		for($i = 0; $i < self::N_OF_PLAYERS; $i++){
			for($j = 0; $j < self::CARDS_IN_FIRST_HAND; $j++){
				$this->players[$i]->addCard(array_shift($this->deck));
			}
		}
	}

	function initializeDeck(){
		$deck = array();
		for($i = self::CLUBS; $i <= self::HEARTS; $i++){
			for($j = 2; $j <= 10; $j++){
				$deck[] = new Card($i, $j);
			}
			$deck[] = new Card($i, "J");
			$deck[] = new Card($i, "D");
			$deck[] = new Card($i, "K");
			$deck[] = new Card($i, "A");
		}
		
		shuffle($deck);

		return $deck;
	}

	function askMashine(){
		$mashine = $this->players[self::MASHINE_ID];
		$wantedCards = $mashine->hasNumber($_POST['picked_num']);
		if($wantedCards){
			return $wantedCards;
		} else {
			return false;
		}
	}

	function pickCard($playerId){
		if(count($this->deck) > 0){
			$picked_card = array_shift($this->deck);
			$this->players[$playerId]->addCard($picked_card);
		}
		return $picked_card;
	}

	function checkIfGameEnds(){
		if((count($this->players[self::MASHINE_ID]->getCards()) == 0 && count($this->players[self::HUMAN_ID]->getCards()) == 0)){
			if($this->players[self::MASHINE_ID]->getBooks() > $this->players[self::HUMAN_ID]->getBooks()){
				return 'mashine';
			} else {
				return 'human';
			}
			$this->state = self::END_GAME;
		}
		return false;
	}

	function getCardFromString($string){
		$number = explode(" ", $string)[0];
		$suit = explode(" ", $string)[1];
		foreach ($this->returnedCards as $card) {
			if(strval($card->getNumber()) == $number && $card->getSuit() == intval($suit)){
				return $card;
			}
		}
	}

	function getRandomPlayer(){
		return rand(0, count($this->players)-1);
	}

	function getPlayers(){
		return $this->players;
	}

	function getPoints(){
		return $this->points;
	}

	function getState(){
		return $this->state;
	}

	function getAskedNumber(){
		return $this->askedCard;
	}

	function setAskedNumber($num){
		$this->askedNumber = $num;
	}

	function setState($state){
		$this->state = $state;
	}

	function setReturnedCards($cards){
		$this->returnedCards = $cards;
	}

	function getReturnedCards(){
		return $this->returnedCards;
	}

	function getDeck(){
		return $this->deck;
	}

	function removeFromReturnedCards($card){
		$index = array_search($card, $this->returnedCards);
		if ( $index !== false ) {
			unset( $this->returnedCards[$index] );
		}
	}

	function getWinner(){
		return $this->winner;
	}

	function setWinner($winner){
		$this->winner = $winner;
	}
}

?>