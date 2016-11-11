function processingErrorsForm($errorsBloc, errors) {
    $errorsBloc.html("");
    $errorsBloc.addClass("error_list");
    
    $.each(errors, function (fieldName, errorsForField) {
        var labelValue = $("label[for*='" + fieldName + "']").html();
        
        if (labelValue != undefined ) var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs sur le champ " + labelValue + ":");
        else var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs :");
        var ul = $("<ul>");
        
        $.each(errorsForField, function (key, error) {
            var li = $("<li>").html(error);
            ul.append(li);
        });
            var br = $("<br>", {clear:"all"});

        $errorsBloc.append(p);
        $errorsBloc.append(ul);
        $errorsBloc.append(br);
    });
    
    $errorsBloc.show();
}

function editShop(shopId) {
    var $newShopModal    = $("#newShop_modal");
    var $modalBody       = $newShopModal.find('.modal-body');
    var $modalFooter     = $newShopModal.find('.modal-footer');
    var $shopForm        = $modalBody.find("form");
    var $sportsForm      = $modalBody.find("sportChoiceForm");

    var $shopId          = $("#shopId");
    
    var $name            = $shopForm.find("input.name");
    var $address         = $shopForm.find("input.address");
    var $town            = $shopForm.find("input.town");
    var $email           = $shopForm.find("input.email");
    var $telNumber       = $shopForm.find("input.telNumber");
    
    var $webShop         = $shopForm.find("input.webShop");
    $webShop.iCheck({
        checkboxClass: 'icheckbox_minimal-grey',
    });
    
    var $conditions      = $("#ks_shopbundle_newshoptype_conditions");
    
    var $errorsBloc      = $modalBody.find(".errorsBloc");
    var $messagesBloc    = $modalBody.find(".messagesBloc");

    var $cancelButton    = $modalFooter.find('a.cancel');
    var $proposeShop     = $modalFooter.find('a.create');
    
    $("#changeAvatarLoader").hide();
    
    if (shopId == null) {
        $name.val("");
        $address.val("");
        $town.val("");
        $email.val("");
        $telNumber.val("");
        $conditions.val("");
    }
    else {
        //Récupération des données du magasin
        $.get(
            Routing.generate('ksShop_getDetails', {'shopId' : shopId}),
            function(response) {
                if (response.responseUpdate == -1) modalInfo(data.errorMessage);
                else {
                    $shopId.val(shopId);
                    $name.val(response.name);
                    $address.val(response.address);
                    $town.val(response.town);
                    $email.val(response.email);
                    $telNumber.val(response.telNumber);
                    if (response.webShop) $webShop.iCheck('check');
                    //console.log(response.sports);
                    $customSelect.select2("val", response.sports);
                    $("#selectedSports").val($customSelect.select2("val"));
                    console.log(response.userId);
                    if (response.userId === 1) {
                        $conditions.val(response.conditions);
                        $("#conditions").show();
                    }
                    else {
                        $("#conditions").hide();
                    }
                }
            }
        );
    }
    
    $shopForm.show();
    $errorsBloc.html("");
    $errorsBloc.hide();
    $messagesBloc.html("");
    $messagesBloc.hide();
    $proposeShop.removeClass( "disabled" );
    $proposeShop.show();
    $cancelButton.html("Annuler");

    var proposeShop = function() {
        if ( !$proposeShop.hasClass( "disabled" ) ) {
            var customSelectSports = $customSelect.select2("val");
            //alert('ici+' + customSelectSports);
            
            if($name.val() == "" ) {
               processingErrorsForm( $errorsBloc, [["Le nom ne doit pas être vide !"]]);
               $name.focus();
            } 
            else if($address.val() == "" ) {
               processingErrorsForm( $errorsBloc, [["L'adresse ne doit pas être vide !"]]);
               $address.focus();
            }
            else if($town.val() == "" ) {
               processingErrorsForm( $errorsBloc, [["La ville ne doit pas être vide !"]]);
               $town.focus();
            }
            else if($telNumber.val() == "" && $email.val() == "") {
                processingErrorsForm( $errorsBloc, [["L'email et le numéro de téléphone ne peuvent pas être tous les deux vides !"]]);
                $email.focus();
            }
            else if (customSelectSports == "" ) {
                processingErrorsForm( $errorsBloc, [["Un sport est obligatoire !"]]);
                $customSelect.focus();
            }
            else if ($name.val() != "") {
                if (shopId == null) {
                    $("#changeAvatarLoader").show();
                    $("#saveButton").addClass("disabled");
                    $("#selectedSports").val($customSelect.select2("val"));
                  
                    //Création du nouveau magasin en base
                    $.post(
                        Routing.generate('ksShop_createNewShop'),
                        $shopForm.serialize(),
                        function(response) {
                        if ( response.publishResponse == 1 ) {
                            $newShopModal.modal('hide');
                            //on récupère l'avatar s'il a été uploadé pour l'enregistrer en base
                            var uploadedPhotos = new Array();

                            $.each($("tr.template-download"), function(key, templateDownload) {
                                uploadedPhotos.push( $( this ).attr("imgName") );
                            });
                            
                            $.post(
                                Routing.generate('ksShop_changeAvatar'), 
                                { 
                                    "uploadedPhotos" : uploadedPhotos,
                                    "shopId" : response.shopId
                                },
                                function(response) {
                                    if( response.response == 1 ) {
                                        $(".addFileButton").removeClass("disabled");
                                        $(".filesInputDl").removeClass("disabled");
                                    }
                                    $(".avatarBloc").html(response.html);
                                    $("tr.template-download").html("");

                                    $("#bchangeAvatar").removeClass("disabled");
                                    $("#changeAvatarLoader").hide();
                                }
                            );
                            showInformation("Ton magasin a bien été créé !<br> Redirection en cours...", "shops");
                            document.location.href = Routing.generate('ksShop_list', {})

                        } else {
                            processingErrorsForm( $errorsBloc, response.errors );
                        }
                        $proposeShop.removeClass( "disabled" );
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
                else {
                    $("#changeAvatarLoader").show();
                    $("#saveButton").addClass("disabled");
                    $("#selectedSports").val($customSelect.select2("val"));
                    //Sauvegarde de l'avatar
                    if( !$("#bchangeAvatar").hasClass("disabled") ) {
                        $("#bchangeAvatar").addClass("disabled");
                        $("#changeAvatarLoader").show();

                        //on récupère les photos téléchargées
                        var uploadedPhotos = new Array();

                        $.each($("tr.template-download"), function(key, templateDownload) {
                            uploadedPhotos.push( $( this ).attr("imgName") );
                        });

                        $.post(
                            Routing.generate('ksShop_changeAvatar'), 
                            { 
                                "uploadedPhotos" : uploadedPhotos,
                                "shopId" : $("#shopId").val()
                            },
                            function(response) {
                                if( response.response == 1 ) {
                                    $newShopModal.modal('hide'); 
                                    $(".addFileButton").removeClass("disabled");
                                    $(".filesInputDl").removeClass("disabled");
                                }
                                $(".avatarBloc").html(response.html);
                                $("tr.template-download").html("");

                                $("#bchangeAvatar").removeClass("disabled");
                                $("#changeAvatarLoader").hide();
                            }
                        );
                    }
                    //Sauvegarde des données
                    $.post(
                        Routing.generate('ksShop_updateShop', {'shopId' : shopId}),
                        $shopForm.serialize(), 
                        function(response) {
                        if ( response.publishResponse == 1 ) {
                            $newShopModal.modal('hide'); 
                            showInformation("Ton magasin a bien été modifié !<br> Redirection en cours...", "shops");
                            document.location.href = Routing.generate('ksShop_list', {})
                        } else {
                            processingErrorsForm( $errorsBloc, response.errors );
                        }
                        $proposeShop.removeClass( "disabled" );
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
                
            }
            else {
                $paragraph = $("<b>").html("Merci de renseigner tous les champs correctement !");
                $name.focus();
                $messagesBloc.html($paragraph);
                $errorsBloc.show();
            }
        }
    };

    $proposeShop.unbind();
    $proposeShop.click(function() {
        proposeShop();
    });

    $newShopModal.on('shown', function() {
        $name.focus();
    });

    $newShopModal.modal('show');
    $newShopModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
        e.stopPropagation();
    });
}