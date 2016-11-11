var ajax = null;
var nbEquipmentsToLoadLimit = 42;
var timer, popover_parent;

$('.popover').live({
    mouseover: function() {
        clearTimeout(timer);
    },
    mouseleave: function() {
        var self = this;
        timer = setTimeout(function(){hidePopover(popover_parent)},300); 
    }
});

var searchEquipments = function( offset, limit ) {

    searchTerms = $("#searchInput").val();

    allEquipments  = $("input[type=checkbox][name=cdAllEquipments]").is(":checked");

    if( ajax != null ) ajax.abort();

    $("#searchOverBloc").hide();
    $("#0searchOverBloc").hide();
    //$("#searchLoader").show();
    $("#moreResultsLoader").show();
    $("#scrollForMoreResultsBloc").hide();

    ajax = $.post(
        Routing.generate('ksEquipment_search'), 
        {
            'terms'           : searchTerms,
            'allEquipments'   : allEquipments,
            'offset'          : offset,
            'limit'           : limit
        },
        function(response) {
            if( response.code == 1 ) {
                $("#equipmentBloc").append(response.html);
                $("#scrollForMoreResultsBloc").find('.nb').html(response.equipments_number_not_loaded);
                $("#scrollForMoreResultsBloc").show();

                //Pour réinitialiser les bulles sur les avatars


                $.each( $("#equipmentBloc").find(".equipmentAvatarBloc .imageLink"), function(key, value) {
                    $( this ).popover({
                        placement: 'top',
                        offset: 15,
                        trigger: 'manual',
                        delay: { show: 350, hide: 350 },
                        html: true
                    });

                    $( this ).hover(
                        function() {
                            var self = this;
                            clearTimeout(timer);
                            $('.popover').hide();
                            popover_parent = self
                            $(self).popover('show');            
                        }, 
                        function() {
                            var self = this;
                            timer = setTimeout(function(){hidePopover(self)},350);    
                        }
                    );
                });

                $(window).off("scroll");

                if (response.equipments_number == 0) {
                    $("#0searchOverBloc").show();
                    $("#scrollForMoreResultsBloc").hide();
                }
                else if( response.equipments_number < limit ) {
                    $("#searchOverBloc").show();
                    $("#scrollForMoreResultsBloc").hide();
                } else {
                    $(window).scroll(function()
                    {
                        if($(window).scrollTop() == $(document).height() - $(window).height())
                        {
                            searchEquipments( offset + limit, limit );
                        }
                    });
                }
            }
            //$("#searchLoader").hide();
            $("#moreResultsLoader").hide();
        }
    );
};

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

function editEquipment(equipmentId) {
    var $newEquipmentModal = $("#newEquipment_modal");
    var $modalBody         = $newEquipmentModal.find('.modal-body');
    var $modalFooter       = $newEquipmentModal.find('.modal-footer');
    var $equipmentForm     = $modalBody.find("form");
    
    var $equipmentId       = $("#equipmentId");
    
    var $brand             = $equipmentForm.find("input.brand");
    var $name              = $equipmentForm.find("input.name");
    var $type              = $equipmentForm.find("input.type");
    var $weight            = $equipmentForm.find("input.weight");
    var $primaryColor      = $equipmentForm.find("input.primaryColor");
    var $secondaryColor    = $equipmentForm.find("input.secondaryColor");
    var $isByDefault       = $equipmentForm.find("input.isByDefault");
    $isByDefault.iCheck({
        checkboxClass: 'icheckbox_minimal-grey'
    });
    
    $isByDefault.iCheck('check');
    
    var $errorsBloc        = $modalBody.find(".errorsBloc");
    var $messagesBloc      = $modalBody.find(".messagesBloc");

    var $cancelButton      = $modalFooter.find('a.cancel');
    var $proposeEquipment  = $modalFooter.find('a.create');
    
    $paramsSpectrum = {
        preferredFormat: "hex6"
    };
    
    $("#changeAvatarLoader").hide();
    
    if (equipmentId == null) {
        //$isPrimaryColorEnabled = stringToBoolean(response.isPrimaryColorEnabled);
        //$isSecondaryColorEnabled = stringToBoolean(response.isSecondaryColorEnabled);
        $isPrimaryColorEnabled = stringToBoolean("1");
        $isSecondaryColorEnabled = stringToBoolean("1");

        $primaryColor.spectrum($paramsSpectrum);
        $secondaryColor.spectrum($paramsSpectrum);

        if($isPrimaryColorEnabled) {
            $primaryColor.spectrum("enable");
        } else {
            $primaryColor.spectrum("disable");
            $primaryColor.spectrum("disable");
        }

        if($isSecondaryColorEnabled) {
            $secondaryColor.spectrum("enable");
        } else {
            $secondaryColor.spectrum("disable");
            $secondaryColor.spectrum("disable");
        }
    }
    else {
        //Récupération des données du magasin
        $.get(
            Routing.generate('ksEquipment_getDetails', {'equipmentId' : equipmentId}),
            function(response) {
                if (response.responseUpdate == -1) modalInfo(data.errorMessage);
                else {
                    $equipmentId.val(equipmentId);
                    
                    $brand.val(response.brand);
                    $name.val(response.name);
                    
                    $customSelectType.select2("val", response.type);
                    $("#selectedType").val($customSelectType.select2("val"));
                    
                    $weight.val(response.weight);
                    
                    $customSelect.select2("val", response.sports);
                    $("#selectedSports").val($customSelect.select2("val"));
                    
                    $isPrimaryColorEnabled = response.isPrimaryColorEnabled;
                    $isSecondaryColorEnabled = response.isSecondaryColorEnabled;
                    
                    if($isPrimaryColorEnabled) {
                        $("#primaryColor").show();
                        $primaryColor.val(response.primaryColor);
                        $primaryColor.spectrum($paramsSpectrum);
                        $primaryColor.spectrum("enable");
                    } else {
                        $("#primaryColor").hide();
                    }

                    if($isSecondaryColorEnabled) {
                        $("#secondaryColor").show();
                        $secondaryColor.val(response.secondaryColor);
                        $secondaryColor.spectrum($paramsSpectrum);
                        $secondaryColor.spectrum("enable");
                    } else {
                        $("#secondaryColor").hide();
                    }
                    
                    if (response.isByDefault) $isByDefault.iCheck('check');
                    else $isByDefault.iCheck('uncheck');
                }
            }
        )
        $brand.focus();
    }
    
    $equipmentForm.show();
    $errorsBloc.html("");
    $errorsBloc.hide();
    $messagesBloc.html("");
    $messagesBloc.hide();
    $proposeEquipment.removeClass( "disabled" );
    $proposeEquipment.show();
    $cancelButton.html("Annuler");

    var proposeEquipment = function() {
        if ( !$proposeEquipment.hasClass( "disabled" ) ) {
            var customSelectSports = $customSelect.select2("val");
            //alert('ici+' + customSelectSports);
            
            if($brand.val() == "" ) {
               //$name.focus(); FIXME : mis en commentaire sinon sous CHROME j'ai un bug d'affichage, la popup s'efface partiellement !!
               processingErrorsForm( $errorsBloc, [["La marque ne doit pas être vide !"]]);
            } 
            if($name.val() == "" ) {
               //$name.focus(); FIXME : mis en commentaire sinon sous CHROME j'ai un bug d'affichage, la popup s'efface partiellement !!
               processingErrorsForm( $errorsBloc, [["Le nom ne doit pas être vide !"]]);
            } 
            else if (customSelectSports == "" ) {
                $customSelect.focus();
                processingErrorsForm( $errorsBloc, [["Un sport doit être sélectionné !"]]);
            }
            else if ($name.val() != "" && $brand.val() != "" &&
                     customSelectSports != "") {
                if (equipmentId == null) {
                    $("#changeAvatarLoader").show();
                    $("#saveButton").addClass("disabled");
                    $("#selectedType").val($customSelectType.select2("val"));
                    $("#selectedSports").val($customSelect.select2("val"));
                    
                    $creationOrEdition = "edition";
                    
                    //Création du nouveau matériel en base
                    $.post(
                        Routing.generate('ksEquipment_createNewEquipment'),
                        $equipmentForm.serialize(),
                        function(response) {
                        if ( response.publishResponse == 1 ) {
                            $newEquipmentModal.modal('hide');
                            //on récupère l'avatar s'il a été uploadé pour l'enregistrer en base
                            var uploadedPhotos = new Array();

                            $.each($("tr.template-download"), function(key, templateDownload) {
                                uploadedPhotos.push( $( this ).attr("imgName") );
                            });
                            
                            $brand.val("");
                            $name.val("");
                            $weight.val("");
                            showInformation("Ton équipement a bien été créé !<br> Rafraichissement en cours...", "sportif");
                            
                            $.post(
                                Routing.generate('ksEquipment_changeAvatar'), 
                                { 
                                    "uploadedPhotos" : uploadedPhotos,
                                    "equipmentId" : response.equipmentId
                                },
                                function(response2) { //si response pble avec le 1er => bug :(
                                    if( response2.response == 1 ) {
                                        $(".addFileButton").removeClass("disabled");
                                        $(".filesInputDl").removeClass("disabled");
                                    }
                                    $(".avatarBloc").html(response.html);
                                    $("tr.template-download").html("");

                                    $("#bchangeAvatar").removeClass("disabled");
                                    $("#changeAvatarLoader").hide();
                                    
                                    document.location.href = Routing.generate('ksProfile_V2');
                                    //$("#equipmentBloc").html(''); searchEquipments( 0, nbEquipmentsToLoadLimit ); FIXME FMO : ce rafraichissement donne un bug si on crée 2 équipements à la suite au niveau de l'image à importer :(
                                }
                            );

                        } else {
                            processingErrorsForm( $errorsBloc, response.errors );
                        }
                        $proposeEquipment.removeClass( "disabled" );
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
                else {
                    $("#changeAvatarLoader").show();
                    $("#saveButton").addClass("disabled");
                    
                    $("#selectedType").val($customSelectType.select2("val"));
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
                            Routing.generate('ksEquipment_changeAvatar'), 
                            { 
                                "uploadedPhotos" : uploadedPhotos,
                                "equipmentId" : $("#equipmentId").val()
                            },
                            function(response) {
                                if( response.response == 1 ) {
                                    $newEquipmentModal.modal('hide'); 
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
                    //Sauvegarde des autres données (name, type, colors)
                    
                    //alert($equipmentForm.serialize());
                    
                    $creationOrEdition = "edition";
                    
                    $.post(
                        Routing.generate('ksEquipment_updateEquipment', {'equipmentId' : equipmentId}),
                        $equipmentForm.serialize(), 
                        function(response) {
                        if ( response.publishResponse == 1 ) {
                            $newEquipmentModal.modal('hide'); 
                            showInformation("Ton équipement a bien été modifié !<br> Rafraichissement en cours...", "sportif");
                            document.location.href = Routing.generate('ksProfile_V2');
                            //$("#equipmentBloc").html(''); searchEquipments( 0, nbEquipmentsToLoadLimit ); FIXME FMO : ce rafraichissement donne un bug si on crée 2 équipements à la suite au niveau de l'image à importer :(
                        } else {
                            processingErrorsForm( $errorsBloc, response.errors );
                        }
                        $proposeEquipment.removeClass( "disabled" );
                        $brand.val("");
                        $name.val("");
                        $weight.val("");
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
                
            }
            else {
                $paragraph = $("<b>").html("Merci de renseigner tous les champs correctement !");
                $brand.focus();
                $messagesBloc.html($paragraph);
                $errorsBloc.show();
            }
        }
    };

    $proposeEquipment.unbind();
    $proposeEquipment.click(function() {
        proposeEquipment();
    });

    $newEquipmentModal.on('shown', function() {
        $brand.focus();
    });
    
    $newEquipmentModal.on('hide', function() {
        $brand.val("");
        $name.val("");
        $weight.val("");
        $("#selectedType").val("");
        $("#selectedSports").val("");
        $("#primaryColor").hide();
        $("#secondaryColor").hide();
    });

    $newEquipmentModal.modal('show');
    $newEquipmentModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
        $brand.val("");
        $name.val("");
        $weight.val("");
        e.stopPropagation();
    });
}
