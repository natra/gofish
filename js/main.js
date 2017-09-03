$(document).ready(function(){
	$(document).off('click.new_game_button');
	$(document).on('click.new_game_button', '.new_game_button', function(e){
		e.preventDefault();
		startNewGame();
	});

	$(document).off('click.games_rules_button');
	$(document).on('click.games_rules_button', '.games_rules_button', function(){
		$('.games_rules').modal('show');
	});

	var clicked_btns_num = 0;

	$(document).off('click.card');	
	$(document).on('click.card', "a.human_card.active", function(e){
		e.preventDefault();
		var wanted_number = $("game_table").data('wantedCard');
		var number = $(this).data('number');
		$(".select_done_btn").css('visibility', 'visible');
		if($(this).hasClass('clicked_btn')){
			$(this).css('position', 'relative').css('bottom', '0px');
			clicked_btns_num++;
		} else {
			$(this).css('position', 'relative').css('bottom', '10px');
			clicked_btns_num--;
		}
		$(this).toggleClass('clicked_btn');
		if(clicked_btns_num == 0){
			$(".select_done_btn").css('visibility', 'hidden');
		}
	})

	$(document).off('click.select_done_btn');
	$(document).on('click.select_done_btn', '.select_done_btn', function(){
		var wanted_number = $(".game_table").data('wantedCard');
		var arr1 = [];
		var arr2 = [];
		$('.human_cards_table').find("a").each(function(){
			var number = $(this).data('number');
			if(number == wanted_number){
				arr1.push($(this).data("card"));
			}
		});
		$('.human_cards_table').find(".clicked_btn").each(function(){
			arr2.push($(this).data('card'));
		});
		if(arr1.toString() == arr2.toString()){
			var books_before = $(".opponent_books_num").text();
			$.ajax({
				url: '',
				data: {
					given_cards_by_human: arr2
				},
				type: 'POST',
				dataType: 'json',
				success: function(tags){
					moveAnimate($(".human_cards_table").find(".clicked_btn"), $(".cards_given_by_opponent"), function(){
						increaseOpponentCardAmount(arr2.length);
						if(books_before != tags['opponent_books_num']){
							increaseBookNum(1, tags['opponent_books_num']);
							decreaseOpponentCardAmount(4);
						}

						$(".human_cards_container").html(tags['card_table']);
						$(".select_done_btn").css('visibility', 'hidden');
						
						if(typeof tags['winner'] !== 'undefined'){
							setTimeout(function(){
								showEndGamePanel(tags['winner']);
							}, 2000);
						} else {
							if(typeof tags['opponent_cards'] !== 'undefined'){
								setTimeout(function(){
									$(".opponent_cards").html(tags['opponent_cards']);
								}, 700);
							}

							if(typeof tags['human_cards_table'] !== 'undefined'){
								$(".human_cards_container").html(tags['human_cards_table']);
							}

							$(".opponent_msg").html(tags['wanted_card'] + "?");
							$(".game_table").data('wantedCard', tags['wanted_card']);
							$(".deck").html(tags['deck']);
							clicked_btns_num = 0;
						}

					}, true, true, true);
					
				}
			});
			$(".human_panel").text("");
		} else {
			$(".human_panel").text("Niewłaściwy dobór kart!");
		}
	});

	$(document).off('click.go_fish_btn');
	$(document).on('click.go_fish_btn','.go_fish_btn', function(e){
		
		var wanted_number = $(".game_table").data('wantedCard');
		var arr1 = [];
		
		$('.human_cards_table').find("a").each(function(){
			var number = $(this).data('number');
			if(number == wanted_number){
				arr1.push($(this).val());
			}
		});

		if(arr1.length == 0){
			$(".go_fish_btn").attr("disabled", "disabled");
			var books_before = $(".opponent_books_num").text();
			$.ajax({
				url: '',
				data: {
					go_fish: true
				},
				dataType: 'json',
				type: 'POST',
				success: function(tags){
					$(".opponent_msg_container").hide();
					moveAnimate($(".deck").find(".deck_card").last(), $(".opponent_cards"), function(){
						if(books_before == tags['opponent_books_num']){
							$(".opponent_cards").find(".deck_card").last().css("margin-bottom", "0").removeClass("deck_card").addClass("opponent_card");
						} else {
							$(".opponent_cards").find(".deck_card").last().css("margin-bottom", "0").removeClass("deck_card").addClass("opponent_card");
							decreaseOpponentCardAmount(4);
							increaseBookNum(1, tags['opponent_books_num']);
						}

						if(typeof tags['opponent_cards'] !== 'undefined'){
							setTimeout(function(){
								$(".opponent_cards").html(tags['opponent_cards']);
							}, 700);
						}

						$(".opponent_msg").text("");
						$(".playername.player").css("border-bottom", "3px solid #337ab7");
						$(".playername.opponent").css("border-bottom", "none");
						
						$(".human_cards_container").html(tags['card_table']);
						
						$(".select_done_btn").css('visibility', 'hidden');
						$(".deck").html(tags['deck']);

						setTimeout(function(){
							$(".human_panel").html(tags['human_panel']);
						}, 200);
					});
				}
			});
		} else {
			$(".human_panel").text("Masz karty, o które pyta przeciwnik!");
		}
	});

	$(document).off('click.picked_num');
	$(document).on('click.picked_num', '.picked_num', function(e){
		$.ajax({
			url: '',
			type: 'POST',
			dataType: 'json',
			data: {
				picked_num: $(this).val()
			},
			success: function(tags){
				
				if(tags['num_of_cards_returned'] > 0){
					$(".human_panel").html(tags['game_status']);

					if(tags['has_mashine_empty_hand'] !== "true"){
						decreaseOpponentCardAmount(tags['num_of_cards_returned']);
					}

					$(".cards_given_by_opponent").hide().html(tags['given_cards_by_opponent']).fadeIn();
					
				} else {
					$(".pick_card_btn").attr("disabled", false);
					drawOpponentMsgContainer("Go fish!");
					$(".human_panel").text("Pobierz kartę.");
				}
				
			}
		});
	});

	$(document).off('click.given_card_by_mashine');
	$(document).on('click.given_card_by_mashine', '.given_card_by_mashine', function(){
		
		if($(this).attr("blocked") == "blocked"){
			return;
		}

		var clicked_button = $(this);

		$(".cards_given_by_opponent").find(".given_card_by_mashine").each(function(){
			if($(this) != clicked_button){
				$(this).attr("blocked", "blocked");
			}
		});

		$(".cards_given_by_opponent").find(".given_card_by_mashine").attr("blocked", "blocked");

		var is_anything_else = false;
		if($(".cards_given_by_opponent").find(".given_card_by_mashine").length > 1){
			is_anything_else = true;
		}
		var books_before = $(".human_books_num").find("div").text();
		element = $(this);
		$.ajax({
				url: 		''
			,	data: {
							given_card_by_mashine: $(this).data('card')
						,	is_anything_else: is_anything_else
				}
			,	dataType: 	'json'
			,	type: 		'post'
			,	success: function(tags){
				
					moveAnimate(element, $(".human_cards_table"), function(){

						$(".human_cards_table").find(".given_card_by_mashine").last().removeClass("given_card_by_mashine").addClass("human_card").find("span").removeClass("given_card_by_mashine_suit").addClass("human_card_suit");
						$(".human_cards_table").find(".human_card").last().find("span").removeClass("given_card_by_mashine_suit").addClass("human_card_suit");
						
						addCardToPlayer(element);

						if(!is_anything_else){
							if(books_before != tags['human_books_num']){
								var number = element.data('card').split(" ")[0];
								setTimeout(function(){
									var is_human_empty_hand_handled = false;

									$(".human_cards_table").find(".human_card").each(function(i){
										if($(this).data("card").split(" ")[0] == number){
											$(this).fadeOut('slow', function(){
												$(this).remove();

												if(!is_human_empty_hand_handled && typeof tags['card_table'] !== 'undefined'){
													$(".human_cards_container").html(tags['card_table']);
													$(".deck").html(tags['deck']);
													is_human_empty_hand_handled = true;
												}
											});
										}
									});

									if(typeof tags['human_books_num'] !== 'undefined' && books_before != tags['human_books_num']){
										increaseBookNum(0, tags['human_books_num']);
									}

									if(typeof tags['opponent_cards'] !== 'undefined'){
										$(".opponent_cards").html(tags['opponent_cards']);
										decreaseDeckCardAmount($(".opponent_cards").find(".small_card").length);
									} else {
										decreaseOpponentCardAmount(tags['num_of_cards_returned']);
									}

								}, 700);
							}

							if(typeof tags['winner'] !== 'undefined'){
								setTimeout(function(){
									showEndGamePanel(tags['winner']);
								}, 2000);
							} else {
								$(".playername.player").css("border-bottom", "3px solid #337ab7");
								$(".playername.opponent").css("border-bottom", "none");
							}
						}

						setTimeout(function(){
							$(".human_panel").html(tags['human_panel']);
						}, (books_before != tags['human_books_num'] ? 1000 : 200));

						$(".cards_given_by_opponent").find(".given_card_by_mashine").attr("blocked", null);
						
					}, true, true, false);
				}
		});
	});

	$(document).off('click.pick_card_btn');
	$(document).on('click.pick_card_btn', '.pick_card_btn', function(){
		var books_before = $(".human_books_num").find("div").text();
		$(".pick_card_btn").attr("disabled", true);
		$.ajax({
				url: ''
			,	type: 'post'
			,	data: 	{
							pick_card: true
						}
			,	dataType: 'json'
			,	success: function(tags){
					moveAnimate($(".deck").find(".deck_card").last(), $(".player_msg_container").parent(), function(){
						
						$(".human_cards_table").append(tags['picked_card']);

						if(books_before != tags['human_books_num']){
							setTimeout(function(){
								increaseBookNum(0, tags['human_books_num']);
								var is_human_empty_hand_handled = false;

								$(".human_cards_table").find(".human_card[data-number='"+tags['picked_card_number']+"']").each(function(){
									$(this).fadeOut('slow', function(){
										$(this).remove();

										if(!is_human_empty_hand_handled){
											$(".human_cards_container").html(tags['card_table']);
											is_human_empty_hand_handled = true;
										}
									});
								});
							}, 400);
							
						} else {
							$(".human_cards_container").html(tags['card_table']);
						}

						$(".go_fish_btn").attr("disabled", false);
						$(".playername.opponent").css("border-bottom", "3px solid #337ab7");
						$(".playername.player").css("border-bottom", "none");
						
						$(".opponent_msg").html(tags['wanted_card'] + "?");
						$(".opponent_msg_container").show();
						$(".game_table").data('wantedCard', tags['wanted_card']);
						$(".human_panel").text("");
						$(".deck").html(tags['deck']);
					}, false, true, true);
				}
		});
	});

	function showEndGamePanel(winner){
		$("body").fadeOut('slow', function(){
			$(".game_container").load("endGamePanel.html", function(){
				if (winner == 'human'){
					var endGameHeading = "Brawo! Wygrałeś!";
				} else {
					var endGameHeading = "Niestety! Tym razem przegrałeś!";
				}

				$(".end_game").text(endGameHeading);
				$(".end_game_button").hide();
				$("body").fadeIn('slow');
			});
		});
	}

	function startNewGame(){
		$.ajax({
				url: ''
			,	type: 'POST'
			,	data: {
					new_game: true
				}
			,	dataType: 'json',
			success: function(tags){
				$(".game_container").hide();
				$(".new_game_button").fadeOut();

				function printNewGame(){
					$(".game_container").load("game_container.html", function(){
						$(".cards_given_by_opponent").text("");
						$(".player_msg_container").text("");
						$(".opponent_cards").html(tags['opponent_cards']);
						$(".deck").html(tags['deck']);
						
						if(tags['opponent_starts']){
							drawOpponentMsgContainer(tags['opponent_msg']);
							$(".human_panel").text("");
							$(".playername.opponent").css("border-bottom", "3px solid #337ab7");
						} else {
							$(".opponent_msg_container").text("");
							$(".go_fish_btn").attr("disabled", "disabled");
							$(".human_panel").html(tags['human_panel']);
							$(".playername.player").css("border-bottom", "3px solid #337ab7");
						}

						$(".pick_card_btn").attr("disabled", "disabled");
						$(".human_cards_container").html(tags['card_table']);
						$(".game_table").data('wantedCard', tags['wanted_card']);
						$(".opponent_books_num").find("div").text("0");
						$(".human_books_num").find("div").text("0");
						$(".game_container").fadeIn('normal');
						$(".end_game_button").fadeIn();
					});
				}

				if($(".main_img").length == 0 || $(".main_img").css("display") == "none"){
					printNewGame();
				} else {
					$(".main_img").fadeOut(function(){
						printNewGame();
					});
				}
			}
		});
	}

	function drawOpponentMsgContainer(message){
		$(".opponent_msg_container").show();
		if(message == 'Go fish!'){
			$(".opponent_msg_container").html('<div class="opponent_msg msg_container"><img src="img/fish-blue.png" title="Idź na ryby!" style="margin-top: 20px; vertical-align: top;"></div>');
		} else {
			$(".opponent_msg_container").html('<div class="opponent_msg msg_container">'+message+'</div>');
		}
	}

	function drawPlayerMsgContainer(message){
		$(".player_msg_container").html('<div style="top: 20px; right: 100%; display: block; height: 100px; width: 100px; position: absolute; border: 1px solid rgba(0,0,0,.2); border-radius: 6px; box-shadow: 0 5px 10px rgba(0,0,0,.2); margin-right: 20px;"><div class="player_msg">'+message+'</div></div>');
	}

	function increaseOpponentCardAmount(amount){
		for (var i = 0; i < amount; i++) {
			$(".opponent_cards").append("<div class='small_card opponent_card'></div>");
		}
	}

	function decreaseOpponentCardAmount(amount){
		var deck_before 			= $(".deck").find(".deck_card").length;
		var opponent_cards_amount 	= $(".opponent_cards").find(".opponent_card").length;
		 
		$(".opponent_cards").find(".opponent_card").each(function(i){
			if(i >= opponent_cards_amount - amount){
				$(this).addClass("card_to_remove");
			}
		});

		$(".card_to_remove").fadeOut('slow', function(){
			$(this).remove();
		});

		if($(".opponent_cards").find("div").length == 0){
			increaseOpponentCardAmount(deck_before - $(".deck").find(".deck_card").length);
		}
	}

	function decreaseDeckCardAmount(amount){
		amount =  amount || 1;

		for(var i=0; i < amount; i++){
			$(".deck").find("div.visible").last().remove();
			if($(".deck").find(".small_card").length > 9){
				$(".deck").find("div.non-visible").last().removeClass("non-visible").addClass("visible").css("margin-bottom", "10px").appendTo(".deck");
			} 
		}
	}

	function addCardToPlayer(card){
		card.removeClass("given_card_by_mashine").addClass("human_card");
		card.find("span").removeClass("given_card_by_mashine_suit").addClass("human_card_suit");
		$(".human_cards_table").append(card);
	}

	function increaseBookNum(player, new_value){
		if(player == 0){
			var book_container = $(".human_books_num").find("div");
		} else {
			var book_container = $(".opponent_books_num").find("div")
		}

		book_container.animate(
					{
									top: '25px'
								,	opacity: '0',
					}
			, function(){

				$(this).css('top',  '-25px');
				$(this).text(new_value);
				$(this).animate({
												top: '0'
											,	opacity: '1'
								});
			}
		);
	}

	function moveAnimate(elements, newParent, callback = false, deleteSource = false, animationOnly = false, fadeOut = false){

		elements.each(function(i){
			var element = $(this);
			var oldOffset = element.offset();

		    element1 = element.clone().appendTo(newParent);
		    var newOffset = element1.offset();

		    var temp = element1.clone().appendTo('body');
		    temp.css({
						        	'position': 	'absolute'
						        ,	'left': 		oldOffset.left + parseInt(element1.css('width'))
						        ,	'top': 			oldOffset.top
						        ,	'z-index': 		1
		    });

		    element1.hide();
		    if(deleteSource){
	    		element.remove();
			} 

			if(newParent.find(".human_card").length > 0){
				newWidth 		= newParent.find(".human_card").css('width');
				newHeight 		= newParent.find(".human_card").css('height');
				newMarginLeft 	= newParent.find(".human_card").css('marginLeft');
			} else if(newParent.find(".opponent_card").length > 0){
				newWidth 		= newParent.find(".opponent_card").css('width');
				newHeight 		= newParent.find(".opponent_card").css('height');
				newMarginLeft 	= newParent.find(".opponent_card").css('marginLeft');
			} else {
				newWidth 		= element.css("width");
				newHeight 		= element.css("height");	
				newMarginLeft 	= element.css('marginLeft');
			}

		    temp.animate(
			    	{	
					    			'top': 			newOffset.top
					    		, 	'left': 		newOffset.left + parseInt(element1.css('width'))
					    		,	'width':  		newWidth
					    		,	'height': 		newHeight
					    		,	'marginLeft': 	newMarginLeft
			    	}
		    	, 	'slow'
		    	, 	function(){
				
				    	var func = function(){
				    		
				    		temp.remove();
							if(animationOnly){
								element1.remove();
							} else {
								element1.show();
							}
							if(callback && i == 0){
								callback();
							}
			    		}

			    		if(fadeOut){
				    		console.log("dafeOut");
				    		temp.fadeOut('slow', function(){
				    			func();
				    		});
				    	} else {
				    		func();
				    	}
				    }
			);
		}); 
	}
});