function createNewSport(creationOrEdition) {
        var $newSportModal = $("#newSport_modal");
        var $modalBody       = $newSportModal.find('.modal-body');
        var $modalFooter     = $newSportModal.find('.modal-footer');
        var $sportForm       = $modalBody.find("form");
        var $sportLabel      = $sportForm.find("input.sportLabel");
        var $categoryBloc    = $sportForm.find(".categoryBloc");
        var $errorsBloc      = $modalBody.find(".errorsBloc");
        var $messagesBloc    = $modalBody.find(".messagesBloc");
        
        var $cancelButton    = $modalFooter.find('a.cancel');
        var $createButton    = $modalFooter.find('a.create');
        
        $sportLabel.val("");
        $sportForm.show();
        $errorsBloc.html("");
        $errorsBloc.hide();
        $messagesBloc.html("");
        $messagesBloc.hide();
        $createButton.removeClass( "disabled" );
        $createButton.show();
        $cancelButton.html("Annuler");
        
        var closeModal = function() {
            $confirmModal.modal('hide'); 
        }
        
        var createSport = function() {
            if ( !$createButton.hasClass( "disabled" ) ) {
                sportLabel = $sportLabel.val();
                if( sportLabel == "" ) {
                    /*le fait d'afficher le div error met la modal derriere le layout principal...
                    $errorsBloc.html("Le titre du sport ne doit pas être vide !");
                    */
                   $sportLabel.val("Le titre du sport ne doit pas être vide !");
                } else if (sportLabel !== "Le titre du sport ne doit pas être vide !") {
//                    $(".error_list").hide();
//                    $createButton.addClass( "disabled" );
//                    $sportForm.hide();
//                    paragraph = $("<p>").html("Ton nouveau sport '" + sportLabel + "' a été soumis à l'équipe Keepinsport pour validation !");
//                    $messagesBloc.append( paragraph );
//                    $messagesBloc.show();
//                    $createButton.hide();
//                    $cancelButton.html("Fermer");
                    $.post(
                        Routing.generate('ksProfile_sports', { "creationOrEdition" : creationOrEdition }),
                        $sportForm.serialize(), 
                        function(response) {
                        /*if ( response.response == 1 ) {*/
                            $sportForm.hide();
                            paragraph = $("<p>").html("Ton nouveau sport '" + sportLabel + "' a été soumis à l'équipe Keepinsport pour validation !");
                            $messagesBloc.append( paragraph );
                            $messagesBloc.show();
                            $createButton.hide();
                            $cancelButton.html("Fermer");
                        /*} else {
                            processingErrorsForm( $errorsBloc, response.errors );
                        }
                        */$createButton.removeClass( "disabled" );
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
            }
        };

        $createButton.unbind();
        $createButton.click(function() {
            createSport();
        });
        
        $newSportModal.on('shown', function() {
            $sportLabel.focus();
        });

        $newSportModal.modal('show');
        $newSportModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
    }