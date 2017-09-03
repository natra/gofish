<?php

class Card {
	private $suit;
	private $number;

	function __construct($suit, $number){
		$this->suit 	= $suit;
		$this->number 	= $number;
	}

	static function convertStringToNumber($string){
		if(is_int($string)) return $string;

		if ($string == "J"){ $num = 11; }
		elseif ($string == "Q"){ $num = 12; }
		elseif ($string == "K"){ $num = 13; }
		elseif ($string == "A"){ $num = 14; }
		return $num;
	}

	static function convertNumberToString($number){
		$string = strval($number);
		if ($number == 11){ $string = "J"; }
		elseif ($number == 12){ $string = "Q"; }
		elseif ($number == 13){ $string = "K"; }
		elseif ($number == 14){ $string = "A"; }
		return $string;
	}

	function getSuit(){
		return $this->suit;
	}

	function getNumber(){
		return $this->number;
	}

	function displayNumber(){
		$numberDisplay = '';
		if($this->number == 11){
			$numberDisplay = "J";
		} elseif ($this->number == 12){
			$numberDisplay = "Q";
		} elseif ($this->number == 13){
			$numberDisplay = "K";
		} elseif ($this->number == 14){
			$numberDisplay = "A";
		} else {
			$numberDisplay = $this->number;
		}
		return $numberDisplay;
	}

	function displaySuit(){
		$suitDisplay = '';
		if($this->suit == 0){
			$suitDisplay = "&clubs;";
		} elseif ($this->suit == 1){
			$suitDisplay = "&diams;";
		} elseif ($this->suit == 2){
			$suitDisplay = "&spades;";
		} elseif ($this->suit == 3){
			$suitDisplay = "&hearts;";
		}
		return $suitDisplay;
	}

}

?>