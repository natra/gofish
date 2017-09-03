<?php

class Player {
	private $cards;
	private $books;
	private $wantedCard;
	private $id;
	private $hasEmptyHand;

	function __construct($id, $game){
		$this->emptyCardList();
		$this->books 		= 0;
		$this->game 		= $game;
		$this->id 			= $id;
	}


	function getCards(){
		return $this->cards;
	}

	function addCard($card){
		$this->cards[] = $card;
		$this->checkForBook();
	}

	function addCards($cards){
		foreach($cards as $card){
			$this->cards[] = $card;
		}
		$this->checkForBook();
	}

	function hasEmptyHand(){
		return $this->hasEmptyHand;
	}

	function toogleEmptyHand(){
		if($this->hasEmptyHand){
			$this->hasEmptyHand = false;
		} else {
			$this->hasEmptyHand = true;
		}
	}

	function removeCards($cards){
		foreach ($cards as $card) {
			if(($key = array_search($card, $this->cards)) !== false){
				unset($this->cards[$key]);
				$this->cards = array_values($this->cards);
			}
		}

		if(count($this->cards) == 0){
			$game = $this->game;

			for ($i=0; $i < $game::CARDS_IN_FIRST_HAND ; $i++) { 
				if(count($game->getDeck()) > 0){
					$game->pickCard($this->id);
				} else {
					break;
				}
			}
			$this->hasEmptyHand = true;
		} else {
			$this->hasEmptyHand = false;
		}
	}

	function hasNumber($number){
		$matchedCards = array();
		foreach ($this->cards as $card) {
			if($card->getNumber() == $number){
				$matchedCards[] = $card;
			}
		}
		if(count($matchedCards) > 0){
			return $matchedCards;
		} else {
			return false;
		}
	}

	function pickRandomNumber(){
		if(count($this->cards) > 0){
			$randCard = $this->cards[rand(0, count($this->cards)-1)];
			return $randCard->getNumber();
		}
		return 0;
	}

	function emptyCardList(){
		$this->cards = [];
	}

	function hasEmptyCardList(){
		if (count($this->cards) > 0){
			return false;
		}
		return true;
	}

	function getBooks(){
		return $this->books;
	}

	function addBook(){
		$this->books++;
	}

	function resetBooks(){
		$this->books = 0;
	}

	function getNumOfCards(){
		return count($this->cards);
	}

	function getUniqueCardNumbers(){
		$uniqueNums = array();
		foreach ($this->cards as $card) {
			$num = $card->getNumber();
			if (!in_array($num, $uniqueNums)) {
				$uniqueNums[] = $num;
			}
		}
		return $uniqueNums;
	}

	function convertStringToCard($string){
		$number		= explode(" ", $string)[0];
		$suit 		= explode(" ", $string)[1];
		foreach ($this->cards as $card) {
			if(strval($card->getNumber()) == $number AND $card->getSuit() == intval($suit)){
				return $card;
			}
		}
	}

	function checkForBook(){
		$cardsByNumber = array();
		foreach ($this->cards as $card) {
			if(!isset($cardsByNumber[$card->getNumber()])){
				$cardsByNumber[$card->getNumber()] = array();
			}
			$cardsByNumber[$card->getNumber()][] = $card;
		}
		foreach ($cardsByNumber as $key => $value) {
			if(count($value) == 4){
				$this->books++;
				$this->removeCards($value);
			} 
		}
	}

	function setWantedCard($card){
		$this->wantedCard = $card;
	}

	function getWantedCard(){
		return $this->wantedCard;
	}
}

?>