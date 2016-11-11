/******************************************

			   KEEP IN SPORT
		2014 - @Spookev for 14bis

******************************************/

$(function(){
	console.log('App Loaded !');
	
	//Init vars
	$input=$('.block-choice input');
	$blockCoach=$('.block-choice.coach');
	$blockWatch=$('.block-choice.montre');
	btnFinal='<a href="#" id="finalize" class="btn btn-success btn-sm">Valider</a>';
	
	//Plan default
	actualChoice='elite1';
	planChoice='elite';
	actualBox=planChoice;
	
	
	//Init Functions	
	$input.iCheck({
		checkboxClass: 'icheckbox_flat-blue',
		radioClass: 'iradio_flat-blue'
	});
	
	//Launch choice function
	goChoice('first');

	//Check default choice
	$('#'+actualChoice).iCheck('check');
	
	
	//Checked Functions	
	$('input').on('ifChecked', function(event){
	
		//Uncheck ALL
		$('input').iCheck('uncheck');
	
		var container = $(this).closest('div.block-choice').attr('data-pos');	
		console.log('On Box > '+container);
		
		//Elite
		if(container=='elite'){
			planChoice='elite';
			actualBox='elite';
			if($(this).attr('id')=='elite1'){
				$('#elite2').iCheck('uncheck');
				actualChoice='elite1';
			}else{
				$('#elite1').iCheck('uncheck');
				actualChoice='elite2';
			}
		}
		//Advanced
		if(container=='advanced'){
			planChoice='advanced';
			actualBox='advanced';
			if($(this).attr('id')=='advanced1'){
				$('#advanced2').iCheck('uncheck');
				actualChoice='advanced1';
			}else{
				$('#advanced1').iCheck('uncheck');
				actualChoice='advanced2';
			}
		}
		//Confort
		if(container=='confort'){
			planChoice='confort';
			actualBox='confort';
			if($(this).attr('id')=='confort1'){
				$('#confort2').iCheck('uncheck');
				actualChoice='confort1';
			}else{
				$('#confort1').iCheck('uncheck');
				actualChoice='confort2';
			}
		}
		
		//Montre
		if(container=='montre'){
			actualBox='montre';
			if($(this).attr('id')=='montre1'){
				$('#montre2, #montre3, montre4').iCheck('uncheck');
				actualChoice='montre1';
			}else if($(this).attr('id')=='montre2'){
				$('#montre1, #montre3, #montre4').iCheck('uncheck');
				actualChoice='montre2';
			}else if($(this).attr('id')=='montre3'){
				$('#montre1, #montre2, #montre4').iCheck('uncheck');
				actualChoice='montre3';
			}else if($(this).attr('id')=='montre4'){
				$('#montre1, #montre2, #montre3').iCheck('uncheck');
				actualChoice='montre4';
			}
			
			$('#okWatch').removeClass('hide');
			
			$(this).closest('li.watch').addClass('check');

		}
		
		
		//Coach
		if(container=='coach'){
			actualBox='coach';
			if($(this).attr('id')=='coach1'){
				$('#coach2, #coach3, #coach3').iCheck('uncheck');
				actualChoice='coach1';
			}else if($(this).attr('id')=='coach2'){
				$('#coach1, #coach3, #coach4').iCheck('uncheck');
				actualChoice='coach2';
			}else if($(this).attr('id')=='coach3'){
				$('#coach1, #coach2, #coach4').iCheck('uncheck');
				actualChoice='coach3';
			}else if($(this).attr('id')=='coach4'){
				$('#coach1, #coach2, #coach3').iCheck('uncheck');
				actualChoice='coach4';
			}
			choiceDone('coach');
		}
		
		goChoice();
		
	});	
	
	//Finalize pack compose
	$(document).on('click','#finalize', function(e){
		var data = $('#packComposeForm').serialize();
		alert('Formulaire OK > '+data);
	});
	
	//UnChecked Functions	
	$('input').on('ifUnchecked', function(event){
		var $box = $(this).closest('.block-choice');
		if($box.hasClass('montre') || $box.hasClass('coach'))	return 0;
		console.log('Is unChecked');
		goEnabled();
		$('.block-choice.montre, .block-choice.coach').addClass('disabled');
		$('.block-choice.montre input, .block-choice.coach input').iCheck('uncheck');
		watchDefined('all');
		choiceDone();
		coachReset();
	});	
	
	
	//Function to confirm choice
	$('#okWatch').click(function(){
		if(planChoice=='elite')	$blockCoach.removeClass('disabled');
		$('ul.montres li.watch').each(function(e){
		    if($(this).hasClass(planChoice+'1')){
		    	if(!$(this).hasClass('check'))	$(this).addClass('hide plan');
		    }else if($(this).hasClass(planChoice+'2')){
		    	if(!$(this).hasClass('check'))	$(this).addClass('hide plan');
		    }

		});
	});
	
	//Function modif pack
	/*$('#modifPackCoach').click(function(){
		$('ul.montres li.watch').each(function(e){
		    if($(this).hasClass('hide plan')){
		    	$(this).removeClass('hide plan');
		    }else{
			    $(this).removeClass('check');
		    }
		});
		$blockCoach.addClass('disabled').find('input').iCheck('uncheck');
	});
	*/
	$('#modifPackWatch, #modifPackCoach').click(function(){
	
		$('.first-block input').iCheck('uncheck');
		
		$('ul.montres li.watch').each(function(e){
			$(this).removeClass('hide plan check');
		});
		$blockWatch.addClass('disabled').find('input').iCheck('uncheck');
		$blockWatch.find('#okWatch').addClass('hide');
				
		$('.first-block .block-choice').each(function(){
			$(this).removeClass('disabled');
		});
	});
	
});

function choiceDone(w){
	$('#finalize').remove();
	if(w=='coach'){
		$('#modifPackCoach').after(btnFinal);
	}else if(w=='watch'){
		$('#modifPackWatch').after(btnFinal);
	}else{
		$('#finalize').remove();
	}
	
}


//Function disabled choice box
function goDisabled(){
	console.log('Go Disabled !');
	$('.block-choice').each(function(e){
		if($(this).hasClass('montre')) return 0;
		$(this).addClass('disabled');
	});
}

//Function Make choice 
function goChoice(first){
	console.log('goChoice > Actual= '+actualChoice);
	//If choice is watches
	if(actualChoice=='montre1' || actualChoice=='montre2' || actualChoice=='montre3' || actualChoice=='montre4'){
		console.log('montre');
		//if(planChoice=='elite')		$('.block-choice.coach').removeClass('disabled');
		watchDefined('selectedWatch');
		return 0;
	//Else if choise is coach
	}else if(actualBox=='coach'){
		return 0;
	}
	
	var $box=$('#'+actualChoice).closest('div.block-choice');
	$box.removeClass('disabled');
	$blockWatch.removeClass('disabled');
	
	watchDefined();
	if(first=='first'){
		$blockCoach.addClass('disabled');
		return 0;
	}
	goDisabled();

}

//Function Coach Reset
function coachReset(){
	console.log('coach reset');
	$('.block-choice.coach .coachbox').each(function(){
		$(this).removeClass('hide');
	});
}


//Function enabled choice box of packs
function goEnabled(){
	$('.container .block-choice').each(function(e){
		var pos=$(this).attr('data-pos');
		if(pos!='montre' && pos!='coach'){
			$(this).removeClass('disabled');
		}
	});
}


//Function watches defined
function watchDefined(x){
	console.log('Watch Defined >> x> '+x);
	
	if(actualChoice=='confort1' || actualChoice=='confort2'){
		$blockWatch.addClass('disabled');
		window.location.replace("http://www.google.com");
		return 0;
	}
	$('.second-block .montres .watch').each(function(e){
		if(x=='all'){
			$(this).removeClass('hide');
			return 0;
		}else if(x=='selectedWatch'){
			return 0;
		}
		console.log(actualChoice);
		if($(this).hasClass(actualChoice)){
			$(this).removeClass('hide');
		}else{
			$(this).addClass('hide');
		}
		
	});
}

//Functions
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};