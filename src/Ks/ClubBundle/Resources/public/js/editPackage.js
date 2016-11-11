function editPackage(clubId, userId) {
    var $packageModal      = $("#package_modal");
    var $modalBody         = $packageModal.find('.modal-body');
    var $modalFooter       = $packageModal.find('.modal-footer');
    var $packageForm       = $modalBody.find("form");
    
    var $errorsBloc        = $modalBody.find(".errorsBloc");
    var $messagesBloc      = $modalBody.find(".messagesBloc");

    var $saveButton        = $("#saveButton");
    var $cancelButton      = $modalFooter.find('a.cancel');
    var $mode;
    
    
    $("#changeAvatarLoader").hide();
    
    //Récupération des forfaits de l'utilisateur pour le club donnée
    $("#username").addClass("disabled");
    
    $.get(
        Routing.generate('ksClub_getPackageDetails', {'clubId' : clubId, 'userId' : userId}),
        function(response) {
            if (response.responseUpdate === -1) modalInfo(data.errorMessage);
            else {
                $("#username").val(response.username);
                if (response.remainingSessions !== false) {
                    $mode = 'edition';
                    $("#packageType_remainingSessions").val(response.remainingSessions);
                }
                else {
                    $mode = 'creation';
                }
                $("#clubId").val(clubId);
                $("#userId").val(userId);
                $("#sportId").val(-1);
            };
    });
    
    $saveButton.removeClass( "disabled" );
    $saveButton.show();
    
    var savePackage = function() {
        
        if ( !$saveButton.hasClass( "disabled" ) && $mode === 'creation') {
            //Création du forfait pour l'utilisateur du club
            $("#changeAvatarLoader").show();
            $("#saveButton").addClass("disabled");
            
            $.post(
                Routing.generate('ksClub_createNewPackage'),
                $packageForm.serialize(),
                function(response) {
                if ( response.publishResponse === 1 ) {
                    $packageModal.modal('hide');
                    $("#username").val("");
                    $("#packageType_remainingSessions").val("");
                    showInformation("Le forfait a bien été créé !<br> Rafraichissement en cours...", "clubs");
                    document.location.href = Routing.generate('KsClub_members', {'clubId' : response.clubId});
                } else {
                    processingErrorsForm( $errorsBloc, response.errors );
                }
                $saveButton.removeClass( "disabled" );
            }).error(function(xqr, error) {
                console.log("error " + error);
            });
        }
        else {
            //Mise à jour des forfaits de l'utilisateur du club
            $("#changeAvatarLoader").show();
            $("#saveButton").addClass("disabled");
            $.post(
                Routing.generate('ksClub_updatePackage'),
                $packageForm.serialize(), 
                function(response) {
                if ( response.publishResponse === 1 ) {
                    $packageModal.modal('hide');
                    $("#username").val("");
                    $("#packageType_remainingSessions").val("");
                    showInformation("Le forfait a bien été modifié !<br> Rafraichissement en cours...", "clubs");
                    document.location.href = Routing.generate('KsClub_members', {'clubId' : response.clubId});
                } else {
                    processingErrorsForm( $errorsBloc, response.errors );
                }
                $saveButton.removeClass( "disabled" );
                $("#packageType_remainingSessions").val("");
            }).error(function(xqr, error) {
                console.log("error " + error);
            });
        }
    };

    $saveButton.unbind();
    $saveButton.click(function() {
        savePackage();
    });
    
    $("#packageType_remainingSessions").focus();
    
    $packageForm.show();
    $errorsBloc.html("");
    $errorsBloc.hide();
    $messagesBloc.html("");
    $messagesBloc.hide();
    $cancelButton.html("Annuler");

    $packageModal.on('shown', function() {
        $("#packageType_remainingSessions").focus();
    });
    
    $packageModal.on('hide', function() {
        $("#username").val("");
        $("#packageType_remainingSessions").val("");
    });

    $packageModal.modal('show');
    $packageModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
        $("#username").val("");
        $("#packageType_remainingSessions").val("");
        e.stopPropagation();
    });
}