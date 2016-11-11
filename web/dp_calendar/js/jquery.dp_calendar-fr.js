jQuery(function($){
	$.fn.dp_calendar.regional[''] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Ant',
		nextText: 'Sig&#x3e;',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		'Juillet','Août','Septembre','Octobre','Novembre','Decembre'],
		monthNamesShort: ['Jan','Fev','Mar','Avr','Mai','Juin',
		'Juil','Août','Sept','Oct','Nov','Dec'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		DP_LBL_NO_ROWS: 'Aucun événement à cette date',
		DP_LBL_SORT_BY: 'TRIER PAR:',
		DP_LBL_TIME: 'HEURE',
		DP_LBL_TITLE: 'TITRE',
		DP_LBL_PRIORITY: 'PRIORITE'};
	$.datepicker.regional[''] = $.fn.dp_calendar.regional[''];
});