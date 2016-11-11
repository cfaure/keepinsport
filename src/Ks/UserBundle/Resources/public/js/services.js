function attachActiveUserService(elt, serviceId, userId) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $.get(
                Routing.generate('ksUser_activeService', { 'userId' : userId, 'serviceId' : serviceId}),
                function(response) {
                    if (response.code == -1) showInformation(response.errorMessage);
                    if( response.html ) $( elt ).parent().html(response.html);
                    
                    if (serviceId == 2) document.location.href = Routing.generate('ksAgenda_index', {"id" : userId });
                    else document.location.href = Routing.generate('ksActivity_syncFromList');
//                    $("#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop").show();
//                    
//                    if (serviceId == 6) {
//                        $.post(Routing.generate('ksyncStrava_createJob'), {}, function(response) {
//                            if (response.syncResponse == 1) {
//                                //showInformation(response.successMessage);
//                                checkServicesSynchronization();
//                            } 
//                            else if (response.syncResponse == -1) {
//                                $('#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop').removeClass('icon-spin');
//                                showInformation(response.errorMessage);
//                            }
//                        });
//                    }
//                    
//                    if (serviceId == 4)
//                        $.post(Routing.generate('ksyncEndomondo_createJob'), {}, function(response) {
//                            if (response.syncResponse == 1) {
//                                //showInformation(response.successMessage);
//                                checkServicesSynchronization();
//                            } 
//                            else if (response.syncResponse == -1) {
//                                $('#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop').removeClass('icon-spin');
//                                showInformation(response.errorMessage);
//                            }
//                        });
//
//                    if (serviceId == 3)
//                        $.post( Routing.generate('ksActivity_createNikePlusJob' ), {}, function( response ) {
//                            if ( response.syncResponse == 1 ) {
//                                //showInformation(response.successMessage );
//                                checkServicesSynchronization();
//                            } 
//                            else if (response.syncResponse == -1) {
//                                $('#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop').removeClass('icon-spin');
//                                showInformation( response.errorMessage );
//                            }
//                        });
//
//                    if (serviceId == 1)
//                        $.post( Routing.generate('ksyncRunkeeper_createJob' ), {}, function( response ) {
//                            if ( response.syncResponse == 1 ) {
//                                //showInformation(response.successMessage );
//                                checkServicesSynchronization();
//                            } 
//                            else if (response.syncResponse == -1) {
//                                $('#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop').removeClass('icon-spin');
//                                showInformation( response.errorMessage );
//                            }
//                        });
//                        
//                    if (serviceId == 5)
//                        $.post( Routing.generate('ksyncSuunto_createJob' ), {}, function( response ) {
//                            if ( response.syncResponse == 1 ) {
//                                //showInformation(response.successMessage );
//                                //checkServicesSynchronization();
//                                $("#servicesSynchroBtnDesktop").click();
//                                document.location.href = Routing.generate('ksActivity_activitiesList', { "firstSync" : "firstSync" });
//                            } 
//                            else if (response.syncResponse == -1) {
//                                $('#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop').removeClass('icon-spin');
//                                showInformation( response.errorMessage );
//                            }
//                        });
                    
                }
            )
        }
    });
}

function attachDeactiveUserService(elt, serviceId, userId) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");

            $.get(
                Routing.generate('ksUser_deactiveService', { 'userId' : userId, 'serviceId' : serviceId}),
                function(response) {
                    if (response.code == -1) showInformation(response.errorMessage);
                    if( response.html ) $( elt ).parent().html(response.html);
                    
                }
            )
        }
    });
}

var checkServicesSynchronization = function() {
    $.post(
        Routing.generate('ksActivity_checkSynchronisationInProgress'), 
        {},
        function(response) {
            if( response.servicesAreBeingSynchronized ) {
                $("#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop").addClass('icon-spin');

                //On re vérifiera dans 30 secondes
                window.setTimeout(checkServicesSynchronization, 30000);
            } else {
                $("#servicesSynchroBtnMobile, #servicesSynchroBtnTablet, #servicesSynchroBtnDesktop").removeClass('icon-spin');

                if( response.activitiesSynchronizedNb > 0 ) {
                    $("#showSynchronizedActivitiesBloc").show();
                }
            }
        }
    );
};