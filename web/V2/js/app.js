/******************************************

			   KEEP IN SPORT
		2014 - @Spookev for 14bis

******************************************/
$(function(){
	console.log(' -- App Loaded ! --');

	//Init vars
	$input=$('.block-choice input');
	actualChoice='elite1';
	actualBox='elite';
	$coachBox=$('.block-choice.coach');
	$watchBox=$('.block-choice.montre');
	btnFinal='<a id="finalize" class="btn btn-success btn-sm">Voir les tarifs</a>';
	noCoach=false;
	mobile=false;
	
	//Default Status
	$('#'+actualChoice).iCheck('check');
	$coachBox.addClass('disabled');
	$('div.first-block .block-choice.'+actualBox).addClass('select');
	
	triMontre();
	
	if(detectmob()) {
		mobile=true;
	}else{
		mobile=false;
	}

	
/******************************************
		CHECK/UNCHECK  FUNCTIONS
******************************************/
        //Radio buttons
        watchOfferChoice = 'default';
        packOfferChoice = 'default';
        $(".modal input").on('ifChecked', function(event){
            if ($(this).attr('name') == 'optionsRadiosPremiumWatch' || $(this).attr('name') == 'optionsRadiosEliteWatch') watchOfferChoice = $("#"+ $(this).attr('id') + "Label").text();
            if ($(this).attr('name') == 'optionsRadiosPremium' || $(this).attr('name') == 'optionsRadiosCoach') packOfferChoice = $("#"+ $(this).attr('id') + "Label").text();
        });


	//Checked Functions	
	$input.on('ifChecked', function(event){
				
		//Define actual box
		actualBox=$(this).closest('div.block-choice').attr('data-pos');
		//Define actual choice
		actualChoice=$(this).attr('id');

		console.log('> Check an input | Box : '+actualBox+' | Choice : '+actualChoice);

                if (actualChoice != 'advanced3' && actualBox == 'montre') watchSelected = actualChoice;
                else if (actualBox != 'coach') watchSelected = 'no';
                
                console.log("watchSelected="+watchSelected);

                if (actualChoice == 'elite1') modeSelected = 'elite1';
                else if (actualChoice == 'elite2') modeSelected = 'elite2';
                else if (actualChoice == 'elite3') modeSelected = 'elite3';
                else if (actualChoice == 'advanced1') modeSelected = 'advanced1';
                else if (actualChoice == 'advanced2') modeSelected = 'advanced2';
                else if (actualChoice == 'advanced3') modeSelected = 'advanced3';
                
                /*
                if (actualChoice == 'Pascal_Blanc') {
                    if (modeSelected == 'elite1') $("#cost").html("200€ à la commande + 120€/ mois pendant 1 an");
                    else if (modeSelected == 'elite2') $("#cost").html("140€ à la commande + 105€/ mois pendant 1 an");
                    else if (modeSelected == 'elite3') $("#cost").html("90€/ mois");
                }
                else if (actualChoice == 'Terre_de_sport') {
                    if (modeSelected == 'elite1') $("#cost").html("200€ à la commande + 80€/ mois pendant 1 an");
                    else if (modeSelected == 'elite2') $("#cost").html("140€ à la commande + 65€/ mois pendant 1 an");
                    else if (modeSelected == 'elite3') $("#cost").html("50€/ mois");
                }
                else if (actualChoice == 'advanced1') {
                    $("#cost").html("200€ à la commande + 35€/ mois pendant 1 an");
                }
                else if (actualChoice == 'advanced2') {
                    $("#cost").html("140€ à la commande + 25€/ mois pendant 1 an");
                }
                else if (actualChoice == 'advanced3') {
                    $("#cost").html("5€/ mois");
                }*/
                
		//Uncheck all to select only one
		unchecked(actualChoice);
                
                //Disabled other box
		$('.block-choice').each(function(e){
			//console.log("$(this)="+$(this).attr('data-pos'));
                        if($(this).attr('data-pos') != actualBox) $(this).addClass('disabled');
		});
		
		//Confort plan > Redirect user
		if(actualChoice=='confort1' || actualChoice=='confort2'){
			$watchBox.addClass('disabled');
			alert('user will be redirect');
			//window.location.replace("http://www.google.com");
			return 0;
		}else if(actualBox=='montre'){
			//Display button confirm watche
			$('#okWatch').removeClass('hide');
		}else if(actualBox=='advanced'){
			noCoach=true;
		}else if(actualBox=='elite'){
			noCoach=false;
		}
		
		//Tri des montres
		if(actualBox!='montre' && actualBox!='coach')	triMontre();
		
		//Define bloc choosed
		if($(this).closest('div.first-block').length!=0)	$(this).closest('div.first-block').find('.'+actualBox).addClass('select');
		
		//Display Valid Btn
		if(actualBox=='coach' && $coachBox.find('#finalize').length==0){
			//$('#modifPackCoach').after(btnFinal);
		}
		
		$watchBox.removeClass('disabled');
		
		//Mobile animations
		if(mobile){
                    if(actualBox=='elite' || actualBox=='advanced'){
                            scrollZ('step2');
                    }
		}
                /*Affiche les tarifs directement sur le choix d'un coach pour le pack Elite
                if (planSelected == 'advanced1' || planSelected == 'advanced2' || planSelected == 'advanced3') {
                    if (planSelected != 'advanced3') $("#plusWatchPremium").html("+ " + $("#"+ actualChoice + "Label").text());
                    else $("#plusWatchPremium").html("");
                    $('#premiumPricesModal').modal('show') ;
                }*/
                $("#blocWatchPremium").hide();
                if (watchSelected != 'no' && (planSelected == 'advanced1' || planSelected == 'advanced2' || planSelected == 'advanced3')) {
                    if (planSelected != 'advanced3') {
                        if (watchSelected == 'no') {
                            $("#blocWatchPremium").hide();
                        }
                        else {
                            $("#blocWatchPremium").show();
                            $("#plusWatchPremium").html("+ " + $("#"+ actualChoice + "Label").text());
                            $("#labelWatchPremium").html("+ " + $("#"+ actualChoice + "Label").text());
                            if (watchSelected == 'V800') $price = 354;
                            else if (watchSelected == '920xt') $price = 399;
                            else if (watchSelected == 'ambit3Peak') $price = 399;
                            else if (watchSelected == 'ambit3Sport') $price = 250;
                            else if (watchSelected == 'M400') $price = 170;
                            else if (watchSelected == '620') $price = 330;
                            
                            $("#calculatorPremium").attr("href", "https://secure.kwixo.com/credit/calculator.htm?merchantId=97718&amount="+$price);
                            $("#premiumWatchChoice1Label").html($price + "€");
                            watchOfferChoice = $price + "€";
//                            $("#premiumWatchChoice1Labe2").html("En 3 fois !");
//                            $("#premiumWatchChoice1Labe3").html("En 5 fois !");
//                            $("#premiumWatchChoice1Labe4").html("En 10 fois !");
//                            $("#premiumWatchChoice1Labe5").html("En 20 fois !");
                            /*$("#labelWatchPremiumChoice2").html("3x " + Math.trunc($price/3) + "€");
                            $("#labelWatchPremiumChoice3").html("5x " + Math.trunc($price/5) + "€");
                            $("#labelWatchPremiumChoice4").html("10x "  + Math.trunc($price/10) + "€");
                            $("#labelWatchPremiumChoice5").html("20x " + Math.trunc($price/20) + "€");*/
                        }
                    }
                    else {
                        $("#plusWatchPremium").html("");
                        $("#blocWatchPremium").hide();
                    }
                    $('#premiumPricesModal').modal('show') ;
                }
                if (planSelected == 'elite1' || planSelected == 'elite2' || planSelected == 'elite3') {
                    if (watchSelected != 'no') {
                        console.log('Confirm watch > ' +actualChoice);
		
                        $('ul.montres #'+actualChoice).closest('li.watch').addClass('select');
                        $('ul.montres li.watch').each(function(e){
                                //if(!$(this).hasClass('select'))	$(this).addClass('hide');
                        });

                        //Disabled other box
                        $('.block-choice').each(function(e){
                                if(!$(this).hasClass('select'))
                                $(this).addClass('disabled');
                        });

                        //Disabled checkbox of plan choice
                        $('.first-block .block-choice.select').iCheck('disable');

                        //Enabled Box coach & watches
                        if(!noCoach)	$coachBox.removeClass('disabled');
                        $watchBox.removeClass('disabled');


                        //remove confirm button
                        $(this).addClass('hide');

                        if(noCoach) //$('#modifPackWatch').after(btnFinal);

                        //Mobile animations
                        if(mobile){
                                scrollZ('step3');
                        }
                    }
                    
                    if (actualChoice == "Pascal_Blanc" || actualChoice == "Terre_de_sport") {
                        if (watchSelected == 'no') {
                            $("#blocWatchElite").hide();
                        }
                        else {
                            $("#blocWatchElite").show();
                            $("#labelWatchElite").html("+ " + $("#"+ watchSelected + "Label").text());
                            if (watchSelected == 'V800') $price = 449;
                            else if (watchSelected == '920xt') $price = 499;
                            else if (watchSelected == 'M400') $price = 199;
                            else if (watchSelected == '620') $price = 399;
                            else if (watchSelected == 'ambit3Peak') $price = 499;
                            else if (watchSelected == 'ambit3Sport') $price = 250;
                            $("#calculatorElite").attr("href", "https://secure.kwixo.com/credit/calculator.htm?merchantId=97718&amount="+$price);
                            $("#eliteWatchChoice1Label").html($price + "€");
//                            $("#eliteWatchChoice2Label").html("En 3 fois !");
//                            $("#eliteWatchChoice3Label").html("En 5 fois !");
//                            $("#eliteWatchChoice4Label").html("En 10 fois !");
//                            $("#eliteWatchChoice5Label").html("En 20 fois !");
                            watchOfferChoice = $price + "€";
                        }
                        
                        if (actualChoice == "Pascal_Blanc") {
                            packOfferChoice = '110€ /mois';
                            $("#optionsRadiosCoach1Label").html("110€ /mois");
                            $("#optionsRadiosCoach1Label2").html(" pour 3 mois");
                            $("#optionsRadiosCoach2Label").html("100€ /mois");
                            $("#optionsRadiosCoach2Label2").html(" pour 6 mois");
                            $("#optionsRadiosCoach3Label").html("90€ /mois");
                            $("#optionsRadiosCoach3Label2").html(" pour 1 an");
                            $("#optionsRadiosCoach4Label").html("");
                            $("#optionsRadiosCoach4Label2").html("");
                            $("#radio4").hide();
                            $("#coachDiversLabel1").html("");
                            $("#coachDiversLabel2").html("");
                            $("#coachDiversLabel3").html("");
                            $("#coachDiversLabel4").html("");
                            
                            $('#coachPricesModal').modal('show') ;
                            $coachLabel = $("#coach1Label").text();
                        }
                        else if (actualChoice == "Terre_de_sport") {
                            //Terre_de_sport
                            $("#optionsRadiosCoach1Label").html("Formule Elite Team Terre de Sport 100€/mois");
                            $("#optionsRadiosCoach1Label2").html("");
                            $("#optionsRadiosCoach2Label").html("Formule Optimum à partir de 56 €/mois");
                            $("#optionsRadiosCoach2Label2").html("");
                            $("#optionsRadiosCoach3Label").html("Formule Performance à partir de 50 €/mois");
                            $("#optionsRadiosCoach3Label2").html("");
                            $("#radio4").show();
                            $("#optionsRadiosCoach4Label").html("Formule Groupe à partir de 100€/mois pour le groupe");
                            $("#optionsRadiosCoach4Label2").html("");
                            
                            $("#coachDiversLabel1").html("Plus d'infos par mail : ");
                            $("#coachDiversLabel2").html("bregegiere.laurent@hotmail.fr");
                            $("#coachDiversLabel3").html(" ou ici : ");
                            $("#coachDiversLabel4").html(" <a href='http://www.keepinsport.com/agenda/753' target='_blank'> vitrine du club</a>");
                            
                            $('#coachPricesModal').modal('show') ;
                            $coachLabel = $("#coach2Label").text();
                        }
                        
                        if (watchSelected != 'no') $coachLabel = $coachLabel + " + " + $("#"+ watchSelected + "Label").text();
                        $("#coachLabel").html($coachLabel);
                    }
                }
	});
	
	//Uncheck functions
	$input.on('ifUnchecked', function(event){
	
		//Unset selected box choosed
		$('.first-block .block-choice').each(function(e){
			$(this).removeClass('select');
		});

	});

	
	
/******************************************
		COMPORTMENTS FUNCTIONS
******************************************/

        //Confirm Watche choice
	$('#okWatch').click(function(e){
		
		console.log('Confirm watche > ' +actualChoice);
		
		$('ul.montres #'+actualChoice).closest('li.watch').addClass('select');
		$('ul.montres li.watch').each(function(e){
			if(!$(this).hasClass('select'))	$(this).addClass('hide');
		});
		
		//Disabled other box
		$('.block-choice').each(function(e){
			if(!$(this).hasClass('select'))
			$(this).addClass('disabled');
		});
		
		//Disabled checkbox of plan choice
		$('.first-block .block-choice.select').iCheck('disable');
		
		//Enabled Box coach & watches
		if(!noCoach)	$coachBox.removeClass('disabled');
		$watchBox.removeClass('disabled');
		
		
		//remove confirm button
		$(this).addClass('hide');
		
		if(noCoach) //$('#modifPackWatch').after(btnFinal);
                
		//Mobile animations
		if(mobile){
			scrollZ('step3');
		}	
	});
	
	//Modif Pack Watch
	$('#modifPackWatch, #modifPackCoach, #modifPack').click(function(e){
		
                $("#cost").html("");
                
		//Uncheck
		$input.iCheck('update');
		$input.iCheck('uncheck');
		$input.iCheck('enable');
		
		//Tri montres
		triMontre('all');
		
		//Disabled boxes
		$coachBox.addClass('disabled');
		$watchBox.addClass('disabled');
		
		//Enabled plan boxes
		$('.first-block .block-choice').each(function(){
			$(this).removeClass('disabled select');
		});
		
		//Remove finalize btn
		$('#finalize').remove();
		
		//Mobile animations
		if(mobile){
			scrollZ('step1');
		}	
		
	});	
	
	//Finalize pack compose
	$(document).on('click','#finalize', function(e){
	
		var data = $('#packComposeForm').serialize();
		var data = planSelected+'=on&'+data;
		//console.log(planSelected);
                //console.log(data);
                //showInformation('Bientôt disponible !');
	});	
});//End $(function()

/******************************************
			FUNCTIONS
******************************************/

//Tri des montres
function triMontre(x){
	console.log('> Tri montres (actualChoice='+actualChoice+')');
	$('.second-block .montres .watch').each(function(e){
	
		//Display all watches
		if(x=='all'){
			$(this).removeClass('hide');
			return 0;
		}

		if($(this).hasClass(actualChoice)){
			$(this).removeClass('hide');
		}else{
			$(this).addClass('hide');
		}
                if (actualChoice == 'elite3') $coachBox.removeClass('disabled');
	});
}


//Uncheck iCheck
function unchecked(choice){
	console.log('UncheckChoice > '+actualBox);
	
	//Plan choice
	if(actualBox=='elite' || actualBox=='confort' || actualBox=='advanced'){
	
		$('.first-block input').iCheck('uncheck');
		$input.iCheck('update');
		
		//Defined selected 
		planSelected=choice;
	}
		
	//Montres
	if(choice=='V800'){
	    $('#920xt, #ambit3Peak, #ambit3Sport, #M400, 620').iCheck('uncheck');
	}else if(choice=='920xt'){
	    $('#V800, #ambit3Peak, #ambit3Sport, #M400, #620').iCheck('uncheck');
	}else if(choice=='M400'){
	    $('#V800, #ambit3Peak, #ambit3Sport, #920xt, #620').iCheck('uncheck');
	}else if(choice=='620'){
	    $('#V800, #ambit3Peak, #ambit3Sport, #920xt, #M400').iCheck('uncheck');
        }else if(choice=='ambit3Peak'){
	    $('#V800, #ambit3Sport, #920xt, #620, #M400').iCheck('uncheck');
        }else if(choice=='ambit3Sport'){
	    $('#V800, #ambit3Peak, #920xt, #620, #M400').iCheck('uncheck');
	}
	
	//Coach
	if(choice=='Pascal_Blanc'){
	    $('#Terre_de_sport, #coach3, #coach4').iCheck('uncheck');
	}else if(choice=='Terre_de_sport'){
	    $('#Pascal_Blanc, #coach3, #coach4').iCheck('uncheck');
	}else if(choice=='coach3'){
	    $('#Pascal_Blanc, #Terre_de_sport, #coach4').iCheck('uncheck');
	}else if(choice=='coach4'){
	    $('#Pascal_Blanc, #Terre_de_sport, #coach3').iCheck('uncheck');
	}
        
        if (choice == 'advanced3') {
            $('#okWatch').hide();
            //$('#modifPackWatch').after(btnFinal);
            if (choice == 'advanced1' || choice == 'advanced2' || choice == 'advanced3') {
                if (planSelected != 'advanced3') $("#plusWatchPremium").html("+ " + $("#"+ actualChoice + "Label").text());
                else $("#plusWatchPremium").html("");
                $("#paymentPremiumBtn").html("Tester 1 mois gratuit !");
                $('#premiumPricesModal').modal('show') ;
            }
        }
        else if (choice == 'elite3') {
            $('#okWatch').hide();
        }
        else if (choice == 'advanced1' || choice == 'advanced2') {
            //$('#okWatch').hide();
        }
        else if (choice == 'elite1' || choice == 'elite2') {
            $('#okWatch').hide();
            $('#finalize').remove();
        }
        else {
            //$('#okWatch').show();
            $('#finalize').remove();
        }
            
}

//Function to scroll for mobile
function scrollZ(a){
    $('html, body').animate({  
        scrollTop:$('#'+a).offset().top
    }, 'slow');  
    return false;  
}

//Functions Mobile
function detectmob() { 
 if( navigator.userAgent.match(/Android/i)
 || navigator.userAgent.match(/webOS/i)
 || navigator.userAgent.match(/iPhone/i)
 || navigator.userAgent.match(/iPad/i)
 || navigator.userAgent.match(/iPod/i)
 || navigator.userAgent.match(/BlackBerry/i)
 || navigator.userAgent.match(/Windows Phone/i)
 ){
    return true;
  }
 else {
    return false;
  }
}
