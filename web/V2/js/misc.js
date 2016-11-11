Array.prototype.remove=function(s){
    var i = this.indexOf(s);
    if(i != -1) this.splice(i, 1);
}
    
String.prototype.addSlashes = function()
{return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');};
 
String.prototype.stripSlashes = function()
{return this.replace(/\\(.?)/g, function (s, n1){switch (n1){case '\\':return '\\';case '0':return '\u0000';case '':return '';default:return n1;}});};

function attachPublishSportSessionEvent(elt, sportId, clubId, eventId ) {
    
        if( typeof( sportId ) == 'undefined' ) sportId = null;
        if( typeof( clubId ) == 'undefined' ) clubId = null;
        if( typeof( eventId ) == 'undefined' ) eventId = null;
        
        var publishSportSessionModal = $("#publishSportSessionModal");
        var messagesBloc = publishSportSessionModal.find('.messages');
        var sportChoiceFormBloc = publishSportSessionModal.find('.sportChoiceForm-bloc');
        var loader = publishSportSessionModal.find('.loader');
        var publishButton = publishSportSessionModal.find('a.publish');
        var cancelButton = publishSportSessionModal.find('a.cancel');
        
        elt.click(function(e) {
            
          if(elt.attr('eventId')){
              eventId = elt.attr('eventId');
          }else{
              var eventId = "NC";
          }    
          
        $("div.popover.in").removeClass("in");

        //console.log(eventId);
            
            //On désactive le boutton
            publishButton.addClass("disabled");
            cancelButton.html("Annuler");
            publishButton.show();
            sportChoiceFormBloc.hide();
            loader.show();
            messagesBloc.hide();
            
            if( $("li.dropdown").hasClass("open")) {
                $("li.dropdown").removeClass("open");
            }

            $.get(
                Routing.generate('ksActivity_loadSportChoiceForm'),
                function(response) {
                    if( response.activitySportChoiceForm_html ) {
                        sportChoiceFormBloc.html(response.activitySportChoiceForm_html);
                        sportChoiceFormBloc.show();
                        loader.hide();
                        
                        var sportChoiceForm = sportChoiceFormBloc.find(".sportChoiceForm");
                        var selectSportChoice = sportChoiceForm.find("select.sportChoice");
                        //var eventId = $("#eventId").val();
                       
        
                        sportChoiceForm.change(function() {
                            var sportIdSelect = selectSportChoice.val();
                            loader.show();
                            console.log(clubId)
                            if( clubId != null ) {
                                path = Routing.generate('ksActivity_getSportSessionForm', {'sportId' : sportIdSelect, 'clubId' : clubId} )
                            } else {
                                path = Routing.generate('ksActivity_getSportSessionForm', {'sportId' : sportIdSelect} )
                            }
                            $.get(
                                path,
                                function(response) {
                                    $('#sportSessionFormContainer').html(response);

                                    //On active le boutton
                                    publishButton.removeClass("disabled");
                                    loader.hide();
                                }
                            ); 
                        }); 
                        
                        if(sportId) {
                            sportChoiceForm.hide();
                            loader.show();
                            selectSportChoice.val(sportId);
                            sportChoiceForm.change();
                        }
                    }
                }
            );

            var publishActivity = function() {
                if( !publishButton.hasClass("disabled")) {
                
                    $('div.error_list').remove();

                    var $sportSessionForm = sportChoiceFormBloc.find(".sportSessionForm");
                    var $fileUploadForm = publishSportSessionModal.find("form.fileUploadForm");
                    //Création de champs contenant les photos téléchargées
                    $("input.uploaded_photo").remove();
                    $.each($fileUploadForm.find("tr.template-download"), function(key, templateDownload) {
                        $sportSessionForm.append(
                            $("<input>", { type:"hidden", name : "photosToAdd[]"})
                                .addClass("uploaded_photo")
                                .val( $( this ).attr("imgName") )
                        );
                    });                

                    publishButton.addClass("disabled")
                    $.post($sportSessionForm.attr('action'), $sportSessionForm.serialize(), function(response) {
                        if ( response.publishResponse == 1 ) {
                            $("#sportSessionFormContainer").html("");

                            if ( $('#activitiesBlockList').size() > 0 ) {
                                $('#activitiesBlockList').prepend(response.html);


                                activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                                $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1)
                            }

                            sportChoiceFormBloc.hide();
                            messagesBloc.html("L'activité sportive a bien été publiée");
                            messagesBloc.show();
                            publishButton.hide();
                            cancelButton.html("Fermer");
                            if(eventId != "NC"){
                                cancelButton.click(function() {
                                    //reloadPage();
                                });
                            }    
                            setTimeout(closeModal,2000);
                            //reloadPage();

                        } else {
                            $.each(response.errors, function (fieldName, errors) {
                                var labelValue = $("label[for*='" + fieldName + "']").html();
                                var div = $("<div>").addClass("error_list");
                                var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs sur le champ " + labelValue + ":");
                                var ul = $("<ul>");
                                $.each(errors, function (key, error) {
                                    var li = $("<li>").html(error);
                                    ul.append(li);
                                });
                                var br = $("<br>", {clear:"all"});

                                div.append(p);
                                div.append(ul);
                                div.append(br);
                                $('[name*="' + fieldName + '"]').parent().append(div);
                            });

                        }
                        publishButton.removeClass("disabled");
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                }
            };      

            publishButton.unbind();
            publishButton.click(publishActivity);
            
            var closeModal = function() {
                publishSportSessionModal.modal('hide');
                sportChoiceFormBloc.html("");
            }
            
            function reloadPage() {
                window.location.reload();
            }
            
            

            publishSportSessionModal.on('shown', function() {

            });

            publishSportSessionModal.on('hide', function() {
                closeModal();
            });

            publishSportSessionModal.modal('show');
            publishSportSessionModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
                e.stopPropagation();
            });
            return false;
        });
    }


function blurImages(thumbs) {
    thumbs.each(function(){
        var $this = $(this);
        var blur_handler = "";
        for(i = 0;i < 9;i++){
            blur_handler += '<img class="blur-clone" src="'
                +$this.find("img").attr("src")+'"'
                +' alt="'+$this.find("img").attr("alt")+'"'
                +' style="top:'+(Math.floor(Math.random()*15)-7)+'px'+';left:'+(Math.floor(Math.random()*15)-7)+'px;"'
                +' />';
            /*
            pour ne pas trop solliciter le DOM,
            on créé le div.blur-handler dans une
            chaîne de caractère
            */
        }

        $this
            .find("a")
                .append('<div class="blur-handler">'+blur_handler+'</div>')
                /*
                on insère ensuite dans le lien,
                le div.blur-handler qui a été
                généré précédemment
                */
            .find(".blur-handler")
                .hide()
                .append($this.find("span"))
                /*
                le span du titre est déplacé dans le div handler
                */
                .find("span")
                    .wrap('<div class="blur-meta"></div>')
                /*
                Le span qui contient le titre de
                l'image est ensuite "encerclé" par un div
                auquel on attribue la classe .blur-meta.
                Ce nouveau div va servir de masque blanc
                et nous permet de positionner correctement
                notre span dans l'image.
                */
                .end()
            .end()
            .toggle(
            function(){
                /*
                au survol, on affiche le blur-handler contenant
                le masque blanc et le titre de l'image
                */
                $this.find(".blur-handler")
                    .stop()
                    .fadeTo(300,1);

                key = $this.attr("key");
                $( "#cb_delete_photo_" + key ).attr('checked','checked')
            },
            function(){
                /*
                en sortant de l'image, on remasque blur-handler
                */
                $this.find(".blur-handler")
                    .stop()
                    .fadeOut(300);

                key = $this.attr("key");
                $( "#cb_delete_photo_" + key ).removeAttr('checked')
            });
        $(".blur-clone").css("opacity",0.15);
        $(".blur-meta").css("opacity",0.6);

    })
}

function modalInfo(message) {
    $('#modalInfo .modal-body').html(message);
    $('#modalInfo').modal('show');
}

function linkAndEditEvent(action, eventId, notificationId) {

    var linkAndEditEventModal = $("#linkAndEditEventModal");
    var modalHeader = linkAndEditEventModal.find(".modal-header");
    var modalTitle = modalHeader.find("h3");
    var sportSessionFormContainer = linkAndEditEventModal.find('.sportSessionFormContainer');
    var loader = linkAndEditEventModal.find('.loader');
    var messagesBloc = linkAndEditEventModal.find('.messages');
    var validateButton = linkAndEditEventModal.find('a.validate');
    //console.log(validateButton);
    var cancelButton = linkAndEditEventModal.find('a.cancel');
    var initialValue = "";

    if( action == "linkAndEdit" ) {
        modalTitle.html("Sélectionnez l'activité à lier");
        validateButton.html("Valider");
    }

    if( $("li.dropdown").hasClass("open")) {
        $("li.dropdown").removeClass("open");
    }

    if( $("div.btn-group").hasClass("open")) {
        $("div.btn-group").removeClass("open");
    }
    
     var closeModal = function() {
        sportSessionFormContainer.html("");
        sportSessionFormContainer.hide();
        sportSessionFormContainer.modal('hide');
    }

    loader.show();
    $.get(
        Routing.generate('ksActivity_getActivitySessionList'),
        function(response) {
            if(response.activitySessionChoiceForm_html){

                var pressedEnter = false;

                sportSessionFormContainer.on('shown', function() {
                    sportSessionFormContainer.html("");
                    sportSessionFormContainer.hide();

                    //On désactive le boutton
                    validateButton.addClass("disabled");
                    cancelButton.html("Annuler");

                    messagesBloc.hide();
                    //descriptionTextarea.val("");
                    //descriptionTextarea.html("")
                    //descriptionTextarea.show();

                    sportSessionFormContainer.hide();
                    loader.show();

                    //descriptionTextarea.focus();
                });

                linkAndEditEventModal.on('hide', function() {
                    closeModal();
                });

                linkAndEditEventModal.modal('show');
                linkAndEditEventModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
                    e.stopPropagation();
                });


                sportSessionFormContainer.html(response.activitySessionChoiceForm_html);
                sportSessionFormContainer.show();

                loader.hide();

                return false;
                validateButton.removeClass("disabled");


                var pressedEnter = false;
            }
        } 
    ); 

    $("#validate-link-activity").click(function() {

        activityId = $("#ks_activitybundle_ActivityChoice_activitySession").val();
        //console.log(eventId);

        $.get(
        Routing.generate('ksActivity_getActivitySessionForm', {'activityId' : activityId , 'eventId' : eventId }),
            function(response) {
                sportSessionFormContainer.html(response);
                sportSessionFormContainer.show();
                //On active le boutton
                validateButton.removeClass("disabled");
                loader.hide();

                $("#validate-link-activity").click(function() {

                    $('div.error_list').remove();
                    var sportSessionForm = sportSessionFormContainer.find(".sportSessionForm");
                    $.post(sportSessionForm.attr('action'), sportSessionForm.serialize(), function(response) {
                        if ( response.publishResponse == 1 ) {                            
                            if( notificationId ) {
                                validateActivity(activityId, notificationId);
                                var successMessage = "L'activité sportive a bien été validée";
                            } else {
                                $("#sportSessionFormContainer").html("");

                                //On remplace l'activité dans le flux
                                if ( $('#activityBloc-' + activityId).size() > 0 ) {
                                    contentBloc = $('#activityBloc-' + activityId).find("div.contentBloc");
                                    if( contentBloc.size() > 0 ) {
                                        contentBloc.html(response.contentHtml);
                                    } else{
                                        contentDetailsBloc = $('#activityBloc-' + activityId).find("div.contentDetailsBloc");
                                        if( contentDetailsBloc.size() > 0 ) {
                                            contentDetailsBloc.html(response.contentDetailsHtml);
                                        }
                                    }
                                }

                                var successMessage = "L'activité sportive a bien été modifiée";
                            }

                            sportSessionFormContainer.hide();
                            messagesBloc.html(successMessage);
                            messagesBloc.show();
                            validateButton.hide();
                            cancelButton.html("Fermer");
                            if(eventId != "NC"){
                                cancelButton.click(function() {
                                    //reloadPage();
                                });
                            }    
                            setTimeout(closeModal,2000);
                            //reloadPage();
                        } else {
                            $.each(response.errors, function (fieldName, errors) {
                                var labelValue = $("label[for*='" + fieldName + "']").html();
                                var div = $("<div>").addClass("error_list");
                                var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs sur le champ " + labelValue + ":");
                                var ul = $("<ul>");
                                $.each(errors, function (key, error) {
                                    var li = $("<li>").html(error);
                                    ul.append(li);
                                });
                                var br = $("<br>", {clear:"all"});

                                div.append(p);
                                div.append(ul);
                                div.append(br);
                                $('[name*="' + fieldName + '"]').parent().append(div);
                            });

                        }
                    }).error(function(xqr, error) {
                        console.log("error " + error);
                    });
                })
            }
        ); 
    });
}    

function editAndValidateActivity(action, activityId, notificationId) {
    var editSportSessionModal = $("#editSportSessionModal");
    var modalHeader = editSportSessionModal.find(".modal-header");
    var modalTitle = modalHeader.find("h3");
    var sportSessionFormContainer = editSportSessionModal.find('.sportSessionFormContainer');
    var loader = editSportSessionModal.find('.loader');
    var messagesBloc = editSportSessionModal.find('.messages');
    var editButton = editSportSessionModal.find('a.edit');
    var cancelButton = editSportSessionModal.find('a.cancel');
    var initialValue = "";

    if( action == "validate" ) {
        modalTitle.html("Valider une activité sportive");
        editButton.html("Valider");
    } else {
        modalTitle.html("Editer une activité sportive");
        editButton.html("Editer");
    }

        if( $("li.dropdown").hasClass("open")) {
            $("li.dropdown").removeClass("open");
        }

        if( $("div.btn-group").hasClass("open")) {
            $("div.btn-group").removeClass("open");
        }

        loader.show();
        $.get(
            Routing.generate('ksActivity_getActivitySessionForm', {'activityId' : activityId}),
            function(response) {
                sportSessionFormContainer.html(response);
                sportSessionFormContainer.show();
                //On active le boutton
                editButton.removeClass("disabled");
                loader.hide();
            }
        ); 

        var closeModal = function() {
            sportSessionFormContainer.html("");
            sportSessionFormContainer.hide();
            editSportSessionModal.modal('hide');
        }

        var editActivity = function() {
            $('div.error_list').remove();
            var $sportSessionForm = sportSessionFormContainer.find(".sportSessionForm");
                var $fileUploadForm = editSportSessionModal.find("form.fileUploadForm");
                //Création de champs contenant les photos téléchargées
                $.each($fileUploadForm.find("tr.template-download"), function(key, templateDownload) {
                    $sportSessionForm.append(
                        $("<input>", { type:"hidden", name : "photosToAdd[]"})
                            .addClass("uploaded_photo")
                            .val( $( this ).attr("imgName") )
                    );
                });
                
                //Création des champs contenant les photos à supprimer
                //on récupère les photos à supprimer
                $("input.photoToDelete").remove();
                $.each($("input.photos_to_delete:checked"), function(key, photo_to_delete) {
                    $sportSessionForm.append(
                        $("<input>", { type:"hidden", name : "photosToDelete[]"})
                            .addClass("photoToDelete")
                            .val( $( this ).val() )
                    );
                });
                
            $.post($sportSessionForm.attr('action'), $sportSessionForm.serialize(), function(response) {
                if (response.publishResponse == 1) {
                    if (notificationId) {
                        validateActivity(activityId, notificationId);
                        var successMessage = "L'activité sportive a bien été validée";
                    } else {
                        $("#sportSessionFormContainer").html("");

                        //On remplace l'activité dans le flux
                        if ( $('#activityBloc-' + activityId).size() > 0 ) {
                            $('#activityBloc-' + activityId).html(response.contentHtml);
                        }

                        var successMessage = "L'activité sportive a bien été modifiée";
                    }

                    sportSessionFormContainer.hide();
                    messagesBloc.html(successMessage);
                    messagesBloc.show();
                    editButton.hide();
                    cancelButton.html("Fermer");
                    setTimeout(closeModal,2000);
                } else {
                    $.each(response.errors, function (fieldName, errors) {
                        var labelValue = $("label[for*='" + fieldName + "']").html();
                        var div = $("<div>").addClass("error_list");
                        var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs sur le champ " + labelValue + ":");
                        var ul = $("<ul>");
                        $.each(errors, function (key, error) {
                            var li = $("<li>").html(error);
                            ul.append(li);
                        });
                        var br = $("<br>", {clear:"all"});

                        div.append(p);
                        div.append(ul);
                        div.append(br);
                        $('[name*="' + fieldName + '"]').parent().append(div);
                    });

                }
            }).error(function(xqr, error) {
                console.log("error " + error);
            });
        };    

        var pressedEnter = false;

        editButton.unbind();
        editButton.click(editActivity);

        editSportSessionModal.on('shown', function() {
            sportSessionFormContainer.html("");
            sportSessionFormContainer.hide();

            //On désactive le boutton
            editButton.addClass("disabled");
            cancelButton.html("Annuler");

            messagesBloc.hide();
            //descriptionTextarea.val("");
            //descriptionTextarea.html("")
            //descriptionTextarea.show();
            editButton.show();

            sportSessionFormContainer.hide();
            loader.show();

            //descriptionTextarea.focus();
        });

        editSportSessionModal.on('hide', function() {
            closeModal();
        });

        editSportSessionModal.modal('show');
        editSportSessionModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
        //return false;
}


function validateActivity(activityId, notificationId, context) {
    $.post(
        Routing.generate('ksNotification_validateActivity', { 'notificationId': notificationId, 'activityId': activityId }), 
        {}, 
        function(response) {
            if( response.validateResponse == 1 ) {

                $("#sportSessionFormContainer").html("");

                //On ajoute l'activité au flux
                if ( $('#activitiesBlockList').size() > 0 ) {
                    $('#activitiesBlockList').prepend(response.html);

                    //On incrémente l'offset des activités, vu qu'on vient d'en ajouter une au flux 
                    activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                    $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1)
                }

                $("button[activityId=" + activityId + "].validateActivity").parent().remove();

                if( $("#newNotifsNumber_"+context).html() != "" ) {
                    var oldNotifsNumber = parseInt($("#newNotifsNumber").html());
                    var newNotifsNumber = newNotifsNumber - 1;
                    if( newNotifsNumber > 0 ) {
                        $("#newNotifsNumber_"+context).html(newNotifsNumber - 1);
                    } else {
                        $("#newNotifsNumber_"+context).html("0");
                    }
                }
            }
        }
    );
}

function resetAutocompleteGmap($localisationBloc) {
    var $infosToHideBloc    = $localisationBloc.find("div.infosToHideBloc");
    var $fullAdressBloc     = $localisationBloc.find(".fullAdressBloc");
    var $fullAdress         = $fullAdressBloc.find("textarea.full_adress");
    var $longitude          = $infosToHideBloc.find("input.longitude");
    var $latitude           = $infosToHideBloc.find("input.latitude");
    //Pays
    var $countryCode        = $infosToHideBloc.find("input.countryCode");
    var $countryLabel        = $infosToHideBloc.find("input.countryLabel");
    //Région
    var $regionCode        = $infosToHideBloc.find("input.regionCode");
    var $regionLabel        = $infosToHideBloc.find("input.regionLabel");
    //Département
    var $countyCode        = $infosToHideBloc.find("input.countyCode");
    var $countyLabel        = $infosToHideBloc.find("input.countyLabel");
    //Ville
    var $townCode        = $infosToHideBloc.find("input.townCode");
    var $townLabel        = $infosToHideBloc.find("input.townLabel");

    $fullAdress.val("");
    $fullAdress.html("");
    $longitude.val("");
    $latitude.val("");
    $countryCode.val("");
    $countryLabel.val("");
    $regionCode.val("");
    $regionLabel.val("");
    $countyCode.val("");
    $countyLabel.val("");
    $townCode.val("");
    $townLabel.val("");
}

function attachAutocompleteGmap($localisationBloc){

    var $infosToHideBloc    = $localisationBloc.find("div.infosToHideBloc");
    var $fullAdressBloc     = $localisationBloc.find(".fullAdressBloc");
    var $fullAdress         = $fullAdressBloc.find("textarea.full_adress");
    var $longitude          = $infosToHideBloc.find("input.longitude");
    var $latitude           = $infosToHideBloc.find("input.latitude");
    //Pays
    var $countryCode        = $infosToHideBloc.find("input.countryCode");
    var $countryLabel        = $infosToHideBloc.find("input.countryLabel");
    //Région
    var $regionCode        = $infosToHideBloc.find("input.regionCode");
    var $regionLabel        = $infosToHideBloc.find("input.regionLabel");
    //Département
    var $countyCode        = $infosToHideBloc.find("input.countyCode");
    var $countyLabel        = $infosToHideBloc.find("input.countyLabel");
    //Ville
    var $townCode        = $infosToHideBloc.find("input.townCode");
    var $townLabel        = $infosToHideBloc.find("input.townLabel");


    //infosToHideBloc.hide();
    if( typeof( google.maps ) != 'undefined' ) {
        geocoder = new google.maps.Geocoder(); 

        $fullAdress.autocomplete({
            //This bit uses the geocoder to fetch address values
            source: function(request, response) {
                geocoder.geocode( {'address': request.term}, function(results, status) {
                    response($.map(results, function(item) {
                        //Récupération des informations liées à l'adresse  
                        arrayAdressComponents = item.address_components;
                        
                        
                        var adressInfos = {
                            //Pour l'affichage de la liste de recherche
                            label:  item.formatted_address,
                            value: item.formatted_address,
                            //Latitude et longitude
                            latitude: item.geometry.location.lat(),
                            longitude: item.geometry.location.lng(),
                            //ville
                            townCode: "",
                            townLabel: "",
                            //département
                            countyCode: "",
                            countyLabel:"",
                            //région
                            regionCode: "",
                            regionLabel: "",
                            //pays
                            countryCode: "",
                            countryLabel: ""
                        };

                        $.each(arrayAdressComponents, function(key, value) {

                            //Récupération de la ville
                            if( value.types[0] == "locality" ){

                                if( value.long_name != "" ){
                                    adressInfos.townLabel = value.long_name;
                                }
                                
                                if( value.short_name != "" ){
                                    adressInfos.townCode = value.short_name;
                                }
                            }

                            //Récupération de la région
                            if( value.types[0] == "administrative_area_level_1" ){
                                if( value.long_name != "" ){
                                    adressInfos.regionLabel = value.long_name;
                                }
                                if( value.short_name != "" ){
                                    adressInfos.regionCode = value.short_name;
                                }
                            }
                            
                            //Récupération du département
                            if( value.types[0] == "administrative_area_level_2" ){
                                if( value.long_name != "" ){
                                    adressInfos.countyLabel = value.long_name;
                                }
                                if( value.short_name != "" ){
                                    adressInfos.countyCode = value.short_name;
                                }
                            }

                            if( value.types[0] == "country" ){ 
                                if( value.long_name != "" ){
                                    adressInfos.countryLabel = value.long_name;
                                }
                                
                                if( value.short_name != "" ){
                                    adressInfos.countryCode = value.short_name;
                                }
                            }
                        });

                        //console.log(adressInfos);
                        return adressInfos;
                    }));
                })
            },
            //This bit is executed upon selection of an address
            select: function(event, ui) {  
                //console.log(ui.item);

                //Pays
                $countryCode.val(ui.item.countryCode);
                $countryLabel.val(ui.item.countryLabel);
                //Région
                $regionCode.val(ui.item.regionCode);
                $regionLabel.val(ui.item.regionLabel);
                //Département
                $countyCode.val(ui.item.countyCode);
                $countyLabel.val(ui.item.countyLabel);
                //Ville
                $townCode.val(ui.item.townCode);
                $townLabel.val(ui.item.townLabel);
                
                $latitude.val(ui.item.latitude);
                $longitude.val(ui.item.longitude);
            }
        });
    }
}

function secondsToTime(secs, context)
{
    var hours = Math.floor(secs / (60 * 60));
    hours = hours < 10 ? "0"+hours : String(hours);

    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
    minutes = minutes < 10 ? "0"+minutes : String(minutes);

    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
    seconds = seconds < 10 ? "0"+seconds : String(seconds);

    var obj = {
        "h": hours,
        "m": minutes,
        "s": seconds
    };
    
    if (context == 'noHours') return obj["m"]+"'"+obj["s"];
    else return obj["h"]+":"+obj["m"]+":"+obj["s"];
}

/*function attachEditStatusEvent(activityId) {

    elt = $('a[rel="editActivityTrigger-' + activityId + '"]');

    var $publishStatusModal = $("#publishStatusModal");
    var $descriptionTextarea = $publishStatusModal.find(".status");
    var messagesBloc = $publishStatusModal.find('.messages');
    var errorsBloc = $publishStatusModal.find('.errors');
    var loader = $publishStatusModal.find('.loader');
    var statusForm = $("#statusForm");
    var publishButton = $publishStatusModal.find('a.publish');
    var cancelButton = $publishStatusModal.find('a.cancel');
    var initialValue = "";

    elt.click(function(e) {
        var publishStatus = function() {
            errorsBloc.hide();
            errorsBloc.html("");
            $.post(
                statusForm.attr('action'), 
                statusForm.serialize(),
                function(response) {
                    if (response.publishResponse == 1) {
                        if ( $('#activitiesBlockList').size() > 0 ) {
                            $('#activitiesBlockList').prepend(response.html);

                            {# On incrémente l'offset des activités, vu qu'on vient d'en ajouter une au flux #}
                            activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                            $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1);
                        }

                        $descriptionTextarea.html(initialValue);
                        $descriptionTextarea.val(initialValue);

                        statusForm.hide();

                        messagesBloc.html("Le statut a bien été publié");
                        messagesBloc.show();
                        publishButton.hide();
                        cancelButton.html("Fermer");

                        setTimeout(closeModal,2000);
                    } else {
                        $.each(response.errors, function (fieldName, errors) {
                            var ul = $("<ul>");
                            $.each(errors, function (key, error) {
                                var li = $("<li>").html(error);
                                ul.append(li);
                            });

                            errorsBloc.append(ul);
                            errorsBloc.show();
                        });
                    }
                }).fail(function(jqXHR, textStatus) {
                    console.log("error " + textStatus);
                });
            };

        var pressedEnter = false;

        publishButton.unbind();
        publishButton.click(publishStatus);

        var closeModal = function() {
            $publishStatusModal.modal('hide');
            $descriptionTextarea.val(initialValue);
            $descriptionTextarea.html(initialValue);
            statusForm.show();
            messagesBloc.hide();
        }

        $publishStatusModal.on('shown', function() {
            errorsBloc.hide();
            loader.hide();
            messagesBloc.hide();
            statusForm.show();                
            loader.hide();
            cancelButton.html("Annuler");
            publishButton.show();
            $descriptionTextarea.val(initialValue);
            $descriptionTextarea.html(initialValue);
            $descriptionTextarea.focus();

        });


        $publishStatusModal.modal('show');
        $publishStatusModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
        return false;
    });
}*/

function attachPublishStatusEvent(elt) {
    var $publishStatusModal = $("#publishStatusModal");

    //Le corp de la fenêtre
    var $modalBody              = $publishStatusModal.find("div.modal-body");
    var $messagesBloc           = $modalBody.find('.messages');
    var $loader                 = $modalBody.find('.loader');
    var $statusForm             = $modalBody.find("form");
    var $descriptionTextarea    = $publishStatusModal.find(".status");
    var $checkboxIsImportant    = $statusForm.find("input[type=checkbox][name=isImportant]");
    var $buttonIsImportantYes   = $statusForm.find("button.isImportantYes");
    var $buttonIsImportantNo    = $statusForm.find("button.isImportantNo");

    //Le pied de la fenêtre
    var $modalFooter            = $publishStatusModal.find("div.modal-footer");
    var $publishButton          = $modalFooter.find('a.publish');
    var $cancelButton           = $modalFooter.find('a.cancel');
    var $loader                 = $modalFooter.find('img.loader');

    //Autres variables
    var initialValue = "";

    var closeModal = function() {
        $publishStatusModal.modal('hide');
        $descriptionTextarea.val(initialValue);
        $descriptionTextarea.html(initialValue);
        $statusForm.show();
        $messagesBloc.hide();
    };


    var publishStatus = function() {
        if( ! $publishButton.hasClass("disabled") ) {
            $publishButton.addClass("disabled");
            $messagesBloc.hide().removeClass("alert").removeClass("alert-error").removeClass("alert-info");
            $messagesBloc.html("");
            $loader.show();
            $.post(
                $statusForm.attr('action'), 
                $statusForm.serialize(),
                function(response) {
                    if (response.publishResponse == 1) {
                        if ( $('#activitiesBlockList').size() > 0 ) {
                            $('#activitiesBlockList').prepend(response.html);

                            /* On incrémente l'offset des activités, vu qu'on vient d'en ajouter une au flux */
                            activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                            $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1);
                        }

                        $descriptionTextarea.html(initialValue);
                        $descriptionTextarea.val(initialValue);

                        $statusForm.hide();

                        $messagesBloc.addClass("alert alert-info").html("Le statut a bien été publié");
                        $messagesBloc.show();
                        $publishButton.hide();
                        $cancelButton.html("Fermer");

                        setTimeout(closeModal,2000);
                    } else {
                        $messagesBloc.addClass("alert alert-error");
                        $messagesBloc.html(response.errorMessage);

                        $.each(response.errors, function (fieldName, errors) {
                            $messagesBloc.append("<br/><u>" + fieldName +" :</u>");
                            var ul = "<ul>";
                            $.each(errors, function (key, error) {
                                ul = ul + "<li>" + error + "</li>";
                            });
                            ul = ul + "</ul>";
                            $messagesBloc.append(ul);

                        });
                        $messagesBloc.show();
                    }
                    $publishButton.removeClass("disabled");
                    $loader.hide();
                }
            ).fail(function(jqXHR, textStatus) {
                console.log("error " + textStatus);
            });
        }
    };

    $publishButton.on('click', publishStatus);

    /*$descriptionTextarea.keypress(function(e) {
        if(e.which == 13) {
            publishStatus();
        }
    });*/

    elt.click(function(e) {

        $("div.popover.in").removeClass("in");

        $buttonIsImportantYes.click(function() {
            $( this ).addClass("active");
            $buttonIsImportantNo.removeClass("active");
            $checkboxIsImportant.attr("checked", "checked");
       });

       $buttonIsImportantNo.click(function() {
            $( this ).addClass("active");
            $buttonIsImportantYes.removeClass("active");
            $checkboxIsImportant.removeAttr("checked");
       });

        var pressedEnter = false;

        $publishStatusModal.on('shown', function() {
            $loader.hide();
            $messagesBloc.hide();
            $statusForm.show();                
            $loader.hide();
            $cancelButton.html("Annuler");
            $publishButton.show();
            $descriptionTextarea.val(initialValue);
            $descriptionTextarea.html(initialValue);
            $descriptionTextarea.focus();

        });

        /*$publishStatusModal.on('hide', function() {
            closeModal();
        });*/

        $publishStatusModal.modal('show');
        $publishStatusModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
        return false;
    });
}

function attachPublishLinkEvent(elt) {
    
    var publishLinkModal = $("#publishLinkModal");
    var descriptionTextarea = $('#ks_activitybundle_activitylinktype_description');
    var externalLinkBloc = publishLinkModal.find('.externalLink-bloc');
    var messagesBloc = publishLinkModal.find('.messages');
    var errorsBloc = publishLinkModal.find('.errors');
    var bodyLoader = publishLinkModal.find('.bodyLoader');
    var loader = publishLinkModal.find('.loader');
    var linkForm = $("#linkForm");
    var publishButton = publishLinkModal.find('a.publish');
    var cancelButton = publishLinkModal.find('a.cancel');
    var initialValue = descriptionTextarea.val();

    loader.hide();
    bodyLoader.hide();
    messagesBloc.hide();
    externalLinkBloc.show();
    linkForm.show();

    var publishLink = function() {
        if( ! publishButton.hasClass("disabled") ) {
            publishButton.addClass("disabled");
            messagesBloc.hide().removeClass("alert-error").removeClass("alert-info").removeClass("alert");
            messagesBloc.html("");
            loader.show();
            $("#ks_activitybundle_activitylinktype_label").val($('#atc_title').html());
            $("#ks_activitybundle_activitylinktype_linkDescription").val($('#atc_desc').html());
            $("#ks_activitybundle_activitylinktype_link").val($('#atc_title').attr('href'));
            $("#ks_activitybundle_activitylinktype_viewLink").val($('#attach_content').find(".viewLink").html());
            $("#ks_activitybundle_activitylinktype_photo").val($('#atc_images > img[id='+$('#cur_image').val()+']').attr('src'));

            $.post(linkForm.attr('action'), linkForm.serialize(), function(response) {
                if (response.publishResponse == 1) {
                    if ( $('#activitiesBlockList').size() > 0 ) {
                        $('#activitiesBlockList').prepend(response.html);

                        /* On incrémente l'offset des activités, vu qu'on vient d'en ajouter une au flux */
                        activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                        $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1)
                    }

                    $('#attach_content').hide();
                    $('#ks_activitybundle_activitylinktype_description').html(initialValue);
                    $('#ks_activitybundle_activitylinktype_description').val(initialValue);

                    linkForm.hide();
                    externalLinkBloc.hide();
                    messagesBloc.html("Le lien a bien été publié");
                    messagesBloc.show();
                    publishButton.hide();
                    cancelButton.html("Fermer");

                    setTimeout(closeModal,2000);
                } else {
                    messagesBloc.addClass("alert alert-error");
                    messagesBloc.html(response.errorMessage);

                    $.each(response.errors, function (fieldName, errors) {
                        messagesBloc.append("<br/><u>" + fieldName +" :</u>");
                        var ul = "<ul>";
                        $.each(errors, function (key, error) {
                            ul = ul + "<li>" + error + "</li>";
                        });
                        ul = ul + "</ul>";
                        messagesBloc.append(ul);

                    });
                    messagesBloc.show();
                }
                publishButton.removeClass("disabled");
                loader.hide();
            }).fail(function(jqXHR, textStatus) {
                console.log("error " + textStatus);

            });
        }
    };

    var closeModal = function() {
        publishLinkModal.modal('hide');
        descriptionTextarea.val(initialValue);
        descriptionTextarea.html(initialValue);
        externalLinkBloc.hide();
    }

    publishButton.click(publishLink);

    elt.click(function(e) {

        $("div.popover.in").removeClass("in");
        //On désactive le boutton
        publishButton.addClass("disabled");
        cancelButton.html("Annuler");
        //descriptionTextarea.show();
        publishButton.show();

        publishLinkModal.on('shown', function() {
            errorsBloc.hide();
            linkForm.show();
            loader.hide();
            messagesBloc.hide();
            descriptionTextarea.focus();
        });


        publishLinkModal.modal('show');
        publishLinkModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
        return false;
    });
}

function attachPublishPhotoEvent(elt) {
    
    var publishPhotoModal = $("#publishPhotoModal");
    var descriptionTextarea = publishPhotoModal.find(".description");

    //var publishPhotoDropbox = publishPhotoModal.find('.dropbox');
    var $jqueryFileUpload_photoForm = publishPhotoModal.find("#jqueryFileUpload_photoForm");
    var $fileUploadForm = $jqueryFileUpload_photoForm.find("form.fileUploadForm");
    var messagesBloc = publishPhotoModal.find('.messages');
    var errorsBloc = publishPhotoModal.find('.errors');
    var loader = publishPhotoModal.find('.loader');
    var photoForm = $("#photoForm");
    var publishButton = publishPhotoModal.find('a.publish');
    var cancelButton = publishPhotoModal.find('a.cancel');
    var initialValue = descriptionTextarea.val();

    var publishPhoto = function() {
        if( ! publishButton.hasClass("disabled") ) {
            publishButton.addClass("disabled");
            messagesBloc.hide().removeClass("alert-error").removeClass("alert-info").removeClass("alert");
            messagesBloc.html("");
            loader.show();

            //var uploaded_photos = publishPhotoDropbox.find(".uploaded_photos");

            //on récupère les photos téléchargées
            var uploadedPhotos = new Array();
            /*$.each($("input.uploaded_photo"), function(key, uploaded_photo) {
                uploadedPhotos.push( $( this ).val() );
                alert($( this ).val());
            });*/

            $.each($("tr.template-download"), function(key, templateDownload) {
                uploadedPhotos.push( $( this ).attr("imgName") );
                /*photoForm.append(
                    $("<input>", {})
                        .addClass("uploaded_photo")
                        .val( $( this ).attr("imgName") )
                );*/
            });

            if( uploadedPhotos.length > 0 ) {
                $.post(photoForm.attr('action'), {
                        //photoForm.serialize()
                        "description"       : descriptionTextarea.val(),
                        /*"localisation"      : {
                            "fullAdress"    : $fullAdress.val(),
                            "countryArea"   : $countryArea.val(),
                            "countryCode"   : $countryCode.val(),
                            "town"          : $town.val(),
                            "latitude"      : $latitude.val(),
                            "longitude"     : $longitude.val()  
                        },*/
                        "uploadedPhotos"    : uploadedPhotos
                    },
                    function(response) {
                    if (response.publishResponse == 1) {
                        if ( $('#activitiesBlockList').size() > 0 ) {
                            $('#activitiesBlockList').prepend(response.html);

                            /* On incrémente l'offset des activités, vu qu'on vient d'en ajouter une au flux */
                            activitiesOffset = $('#activitiesBlockList').attr('data-offset');
                            $('#activitiesBlockList').attr('data-offset', parseInt(activitiesOffset) + 1);
                        }

                        $("#fileuploadTable > tbody").html("");

                        descriptionTextarea.html(initialValue);
                        descriptionTextarea.val(initialValue);

                        photoForm.hide();
                        $jqueryFileUpload_photoForm.hide();

                        messagesBloc.addClass("alert alert-info").html("La photo a bien été publiée");
                        messagesBloc.show();
                        publishButton.hide();
                        cancelButton.html("Fermer");

                        setTimeout(closeModal,2000);
                    } else {
                        messagesBloc.addClass("alert alert-error");
                        messagesBloc.html(response.errorMessage);

                        $.each(response.errors, function (fieldName, errors) {
                            messagesBloc.append("<br/><u>" + fieldName +" :</u>");
                            var ul = "<ul>";
                            $.each(errors, function (key, error) {
                                ul = ul + "<li>" + error + "</li>";
                            });
                            ul = ul + "</ul>";
                            messagesBloc.append(ul);

                        });
                        messagesBloc.show();
                    }
                    publishButton.removeClass("disabled");
                    loader.hide();
                }).fail(function(jqXHR, textStatus) {
                    console.log("error " + textStatus);
                });

            } else {
                messagesBloc.addClass("alert").html("Merci de télécharger d'abord une photo.");
                messagesBloc.show();
                publishButton.removeClass("disabled");
                loader.hide();
            }
        }
    };

    var closeModal = function() {
        publishPhotoModal.modal('hide');
        descriptionTextarea.val(initialValue);
        descriptionTextarea.html(initialValue);

        $.each($("tbody.files").find("td.cancel"), function(key, templateDownload) {
            $( this ).find("button").click();
        });

        $.each($("tbody.files").find("td.delete"), function(key, templateDownload) {
            $( this ).find("button").click();
        });
        /*$("tbody.files").html("");
        $("span.fileinput-button").removeClass("disabled");*/
        /*$.each($("tr.template-download"), function(key, templateDownload) {
            uploadedPhotos.push( $( this ).attr("imgName") );
        });*/
        $fileUploadForm.fileupload('destroy');
    }

    loader.hide();
    messagesBloc.hide();
    photoForm.show();

    elt.click(function(e) {

        $("div.popover.in").removeClass("in");

        //On désactive le boutton
        //publishButton.addClass("disabled");
        cancelButton.html("Annuler");
        //descriptionTextarea.show();
        publishButton.show();

        publishButton.removeClass("disabled");


        publishButton.unbind();
        publishButton.click(publishPhoto);



        publishPhotoModal.on('shown', function() {

            //$fileUploadForm.fileupload('destroy');
            $fileUploadForm.fileupload({
                acceptFileTypes : /(\.|\/)(gif|jpe?g|png)$/i,
                //maxNumberOfFiles : 1
            });

            $("span.fileinput-button").removeClass("disabled");
            $("input.filesInputDl").removeAttr("disabled");
            $jqueryFileUpload_photoForm.show();
            messagesBloc.hide();
            photoForm.show();
            loader.hide();
            errorsBloc.hide();
            descriptionTextarea.focus();


        });

        /*publishPhotoModal.on('hide', function() {
            closeModal();
        });*/

        publishPhotoModal.modal('show');
        publishPhotoModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
            e.stopPropagation();
        });
        return false;
    });
}

function processingErrorsForm($errorsBloc, errors) {
    $errorsBloc.html("");
    $errorsBloc.addClass("error_list");
    
    $.each(errors, function (fieldName, errorsForField) {
        var labelValue = $("label[for*='" + fieldName + "']").html();
        
        var p = $("<p>", {css: {"text-decoration":"underline"}}).html("Erreurs sur le champ " + labelValue + ":");
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
function attachCommentEvent(elt) {
    var $parent = elt.parent();
    var $loader = $parent.find("img.commentFormLoader");
    
    elt.click(function(e) {
        if( ! elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $loader.show();
            $('form.commentForm').remove(); 
            newCommentFormContainer = $parent.parent().parent().parent().find('.newCommentFormContainer');
            /*$(newCommentFormContainer).load(
                $(this).attr('href'),
                ''//,
                //refreshTimeline()
            );*/
            $.get(
                elt.attr('href'),
                function(data) {
                    newCommentFormContainer.html(data);
                    textarea = newCommentFormContainer.find(".commentTextarea");
                    textarea.focus();
                    elt.removeClass("disabled");
                    $loader.hide();
                    $parent.parent().parent().parent().find("img.publishCommentLoader").hide();
                    //$parent.parent().parent().find('.commentsBloc').show();
                }
            )
        }
        
        return false;
    });
}
function attachVoteOnActivityEvent(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            votesNumberContainer = $(this).next('.votesNumberContainer');

            $.get(
                $(this).attr('href'),
                function(data) {
                    if (data.responseVote == -1) showInformation(data.errorMessage);
                    $( elt ).parent().html(data.voteLink);
                }
            )
        }
        e.preventDefault();
        return false;
    });
}
function attachRemoveVoteOnActivityEvent(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            votesNumberContainer = $(this).next('.votesNumberContainer');
            //$(newCommentFormContainer).load();
            $.get(
                $(this).attr('href'),
                function(data) {
                    if (data.responseVote == -1) showInformation(data.errorMessage);
                    $( elt ).parent().html(data.voteLink);
                }
            )
        }
        e.preventDefault();
        return false;
    });
}
function attachSubscribeOnActivityEvent(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");

            $.get(
                $(this).attr('href'),
                function(data) {
                    if (data.responseSubscribe == -1) showInformation(data.errorMessage);
                    $( elt ).parent().html(data.subscriptionLink);
                }
            )
        }
        e.preventDefault();
        return false;
    });
}
function attachUnsubscribeOnActivityEvent(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $.get(
                $(this).attr('href'),
                function(data) {
                    if (data.responseUnsubscribe == -1) showInformation(data.errorMessage);
                    $( elt ).parent().html(data.subscriptionLink);
                }
            )
        }
        e.preventDefault();
        return false;
    });
}
function attachDeleteActivityEvent(activityId) {
    elt = $('a[rel="deleteActivityTrigger-' + activityId + '"]');
    
    var callback = function() {
        //On essaye de délier l'activité si lié à une séance d'abord
        $.get(
            Routing.generate('ksCoaching_unlinkSession', {'activityId' : activityId, 'eventId' : 0 }), 
            null,
            function(response) {
                if (response.code == 1) {
                    $.get(
                        Routing.generate('ksActivity_deleteActivity', {'activityId' : activityId}),
                        function(data) {
                            if (data.responseDelete == -1) modalInfo(data.errorMessage);
                            else {
                                $('div[id="activityBloc-' + activityId + '"]').remove();
                                $('div[id="avatarBloc-' + activityId + '"]').remove();
                                $('br[id="br1Bloc-' + activityId + '"]').remove();
                            }
                        }
                    )
                }
            }
        ).fail(function(jqXHR, textStatus) {
            console.log("error " + textStatus);
        });
    };
    
    var message = "Tu es sur le point de supprimer cette actualité (action irréversible)<br/>Es-tu sûr de vouloir continuer ?";

    askConfirmation(message, 'sportif', callback, null);
    return false;
}

function attachDisableActivityEvent(activityId) {
    elt = $('a[rel="disableActivityTrigger-' + activityId + '"]');

    $.get(
        Routing.generate('ksActivity_disableActivity', {'activityId' : activityId}),
        function(data) {
            if (data.responseDisable == -1) modalInfo(data.errorMessage);
            else {
                $('div[id="activityBloc-' + activityId + '"]').remove();
                $('div[id="avatarBloc-' + activityId + '"]').remove();
                $('br[id="br1Bloc-' + activityId + '"]').remove();
            }
        }
    )
    return false;
}

function attachHideActivityEvent(activityId) {
    $.get(
        Routing.generate('ksActivity_hideActivity', {'activityId' : activityId}),
        function(data) {
            if (data.responseHide == -1) modalInfo(data.errorMessage);
            else {
                $('div[id="activityBloc-' + activityId + '"]').remove();
                $('div[id="avatarBloc-' + activityId + '"]').remove();
                $('br[id="br1Bloc-' + activityId + '"]').remove();
                //$('div[id="avatarBloc-' + activityId + '"]').remove();
                //refreshTimeline();
            }
        }
    )
}

function attachDownloadEvent(elt) {
    var $parent = elt.parent();
    var $loader = $parent.find("img.commentFormLoader");
    
    elt.click(function(e) {
        if( ! elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $loader.show();
            
            $.get(
                elt.attr('href'),
                function(response) {
                    elt.removeClass("disabled");
                    $loader.hide();
                    download(response.tcx, response.idFromService + '.tcx', "text/plain");
                }
            );
        }
        
        return false;
    });
}

function shareActivityEventOBSOLETE(context, activityId, type ) {
    console.log("shareActivity" + activityId + "OnFb('" + context + "','" + type + "')");
    eval("shareActivity" + activityId + "OnFb('" + context + "','" + type + "')");
    console.log("ici2");
}

function shareActivityEvent( userId, context, activityId, shareFbEnabled, type ) {

    var $shareActivityModal     = $("#shareActivityModal");
    
    //L'entête de la fenêtre
    var $shareTitle             = $("#shareTitle");
    var $shareInfo              = $("#shareInfo");

    //Le corp de la fenêtre
    var $modalBody              = $shareActivityModal.find("div.modal-body");
    var $shareButtonsBloc       = $modalBody.find("div.shareButtonsBloc");
    var $shareOnKs              = $modalBody.find("input[type=radio][name=shareOn][value=ks]");
    var $shareOnMail            = $modalBody.find("input[type=radio][name=shareOn][value=mail]");
    var $shareOnFb              = $modalBody.find("input[type=radio][name=shareOn][value=fb]");
    var $emailsBloc             = $modalBody.find("div.emailsBloc");
    var $inputEmail             = $modalBody.find("input.emails");
    var $descriptionBloc        = $("#descriptionBloc");
    var $descriptionTextarea    = $modalBody.find('textarea.description');
    var $activityToBeSharedBloc = $modalBody.find('.activityToBeShared-bloc');
    var $loader                 = $modalBody.find('.loader');

    //Le pied de la fenêtre
    var $modalFooter            = $shareActivityModal.find("div.modal-footer");
    var $shareButton            = $modalFooter.find('span.share');
    var $cancelButton           = $modalFooter.find('span.cancel');
    var $loaderFooter           = $modalFooter.find('.loader');


    $shareButton.addClass("disabled");
    $cancelButton.html("Annuler");
    $descriptionTextarea.val("");
    $descriptionTextarea.html("");
    $activityToBeSharedBloc.html("");
    $descriptionBloc.hide();
    $loaderFooter.hide();
    $inputEmail.val("");
    $emailsBloc.hide();
    $shareOnMail.iCheck('uncheck');
    $shareOnKs.iCheck('uncheck');
    $shareOnFb.iCheck('check');
    $shareOnFb.iCheck('update');
    
    if( ! shareFbEnabled ) {
        $shareOnFb.iCheck('disable');
    } else {
        $shareOnFb.iCheck('enable');
    }

    $loader.show();
    
    if (context != 'newsFeed') {
        //Cas du partage sur FB à partir d'une création d'activité manuelle par écran
        $shareInfo.css('visibility', 'visible');
        $shareInfo.show();
        $messagesBloc.addClass("alert alert-info");
        $messagesBloc.html("L'activité a bien été créée avec succès !");
        $messagesBloc.show();
        $cancelButton.html("Suite");
        $cancelButton.click(function() {
            activityRedirect(userId, context, activityId);
        });
    }
    else {
        $shareTitle.css('visibility', 'visible');
        $shareTitle.show();
        $messagesBloc.hide().removeClass("alert").removeClass("alert-error").removeClass("alert-info").html("");
        $shareButtonsBloc.show();
    }
    
    $shareActivityModal.modal('show');

    $.get(
        Routing.generate('ksActivity_loadActivityToBeShared', {'activityId' : activityId}),
        function(response) {
            $loader.hide();
            $activityToBeSharedBloc.html(response.activityToBeShared_html);  
            $activityToBeSharedBloc.show();
            
            //On active le boutton
            $shareButton.removeClass("disabled");
        }
    );
        
    $shareButton.click(function() {
        $shareButton.addClass( "disabled" );
        shareActivity(context, activityId, type);
    });
}

function attachWarninActivityIsDisturbingEvent(activityId) {
    $.get(
        Routing.generate('ksActivity_warningActivityIsDisturbing', {'activityId' : activityId}),
        function(data) {
            if (data.responseHide == -1) modalInfo(data.errorMessage);
            else {
                /*$('div[id="activityBloc-' + activityId + '"]').remove();
                $('div[id="avatarBloc-' + activityId + '"]').remove();
                $('br[id="br1Bloc-' + activityId + '"]').remove();*/
                //$('div[id="avatarBloc-' + activityId + '"]').remove();
                //refreshTimeline();
            }
        }
    )
}

function attachWarnActivitylikeDisturbingEvent(elt) {
    elt.click(function(e) {
        $.get(
            $(this).attr('href'),
            function(data) {
                if (data.responseWarn == -1) modalInfo(data.errorMessage);
                else $( elt ).parent().html(data.warnLink);
            }
        )
        return false;
    });
}
function attachRemoveWarnActivitylikeDisturbingEvent(elt) {
    elt.click(function(e) {
        $.get(
            $(this).attr('href'),
            function(data) {
                if (data.responseWarn == -1) modalInfo(data.errorMessage);
                else $( elt ).parent().html(data.warnLink);
            }
        )
        return false;
    });
}

function attachParticipateInArticleSportingEvent(elt) {
    elt.click(function(e) {
         $.get(
            $(this).attr('href'),
            function(data) {
                if (data.participateResponse == -1) modalInfo(data.errorMessage);
                else $( elt ).parent().html(data.subscriptionOnArticleSportingEventLink);
            }
        )
        return false;
    });
}
function attachNotParticipateAnymoreInArticleSportingEvent(elt) {
    elt.click(function(e) {
        $.get(
            $(this).attr('href'),
            function(data) {
                if (data.participateResponse == -1) modalInfo(data.errorMessage);
                else $( elt ).parent().html(data.subscriptionOnArticleSportingEventLink);
            }
        )
        return false;
    });
}


function Arrow_Points()
{ 
    var s = $('#timelineBloc').find('.activityBloc');
    $.each(s,function(i,obj){
        var posLeft = $(obj).css("left");
        $(obj).addClass('borderclass');

        if(posLeft == "0px")
        {
            html = "<span class='rightCorner'></span>";
            $(obj).prepend(html);
            $(obj).addClass('blocTimelineLeft');
        }
        else
        {
            html = "<span class='leftCorner'></span>";
            $(obj).prepend(html);
            $(obj).addClass('blocTimelineRight');
        }
    });
}

function refreshTimeline() {
    if( $(".masonry").size() > 0 ) {
        $('#timelineBloc').masonry('destroy');
    }

    $(".leftCorner, .rightCorner").remove();
    $(".activityBloc").removeClass('borderclass blocTimelineLeft blocTimelineRight masonry-brick');
    $('#timelineBloc').masonry({itemSelector : '.activityBloc'});
    $('.timeline').show();
    Arrow_Points();
}

function loadActivities(offset, activitiesTypes, activitiesFrom, sports, lastModified, fromPublicProfile) {

    //paramètre facultatif
    if(typeof(activitiesTypes) === 'undefined') activitiesTypes = new Array("all");
    if(typeof(fromUsers) === 'undefined') fromUsers = new Array("public");
    if(typeof(lastModified) === 'undefined') lastModified = true;
    if(typeof(fromPublicProfile) === 'undefined') fromPublicProfile = false;
    
    var alreadyLoaded = false;
    
    //prepareRefreshScrollbar();
    $("#blocInfoFinish").hide();
    $("#newActivitiesLoading").show();
    $("#blocMoreActivitiesToLoad").hide();
    
    $("div.popover.in").removeClass("in");
    
    /*if( offset == 0 ) {
        //On supprime tous les timers des canvas en cours;
        $.each(canvasTimers, function(activityId, timer) {
            clearInterval( canvasTimers[activityId.toString()] );
            delete canvasTimers[activityId.toString()];
        });
    }*/
    
    //console.log(activitiesTypes);
    
    
    //var ajaxRequest = $.post(

    var ajaxQueue = new AjaxQueue();
    $max = 10;
    
    $ajaxTreated = 0;
    
    for ($i=0;$i<$max;$i++) {
        //console.log("AddAfter i:" + $i);
        ajaxQueue.AddAfter(
            {
                type : "POST",
                url : Routing.generate('ksActivity_loadActivities', {'offset' : offset + $i}),
                data : { 
                    "activitiesTypes"   : activitiesTypes,
                    "activitiesFrom"    : activitiesFrom,
                    "sports"            : sports,
                    "lastModified"      : lastModified,
                    "fromPublicProfile" : fromPublicProfile
                },
                success: function(response){
                    //On cré des liens là où il en faut 
                    var rgx = new RegExp('((https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|])','ig');

                    $html = $(response.html);
                    $.each( $html.find(".description"), function(key, description) {
                        var desc = $( this ).html();
                        var descWithLinks = desc.replace(rgx,"<a href='$1' target='_BLANK'>$1</a>");
                        //On remplace le lien dans la description adéquate
                        $html.find(".description").eq(key).html(descWithLinks);
                    });

                    //Dans les commentaires
                    $.each( $html.find(".commentBloc"), function(key, commentBloc) {
                        var comment = $( this ).html();
                        var commentWithLinks = comment.replace(rgx,"<a href='$1' target='_BLANK'>$1</a>");

                        $html.find(".commentBloc").eq(key).html(commentWithLinks);
                    });


                    $("#activitiesBlockList").append($html);
                    var timer, popover_parent;

                    //Activation de la bulle
                    $.each( $('#activitiesBlockList').find(".userAvatarBloc .imageLink[rel=bubble]"), function(key, value) {
                        $( this ).popover({
                            placement: 'top',
                            offset: 15,
                            trigger: 'manual',
                            delay: { show: 350, hide: 100 },
                            html: true
                        });

                        $( this ).hover(
                            function() {
                                var self = this;
                                clearTimeout(timer);
                                //$('.popover').hide();
                                popover_parent = self
                                $(self).popover('show');            
                            }, 
                            function() {
                                var self = this;
                                //timer = setTimeout(function(){hidePopover(self)},100);    
                            }
                        );
                    });

                    $('.popover').live({
                        mouseover: function() {
                            clearTimeout(timer);
                        },
                        mouseleave: function() {
                            var self = this;
                            //timer = setTimeout(function(){hidePopover(popover_parent)},100); 
                        }
                    });

                    $("a[rel=tooltip], img[rel=tooltip], span[rel=tooltip], button[rel=tooltip]").tooltip();
                    //$("a.userImage").ksUserBubble("test");

                    $("#blocMoreActivitiesToLoad").show();
                    //$('#bLoadNextActivities').attr("data-offset", response.offset)



                    if( offset == response.offset ) {
                        $("#blocInfoFinish").show(); 
                        $("#blocMoreActivitiesToLoad").hide();
                    } else {
                        $("#activitiesBlockList").data("data-offset", response.offset);
                    }

                    //Changement de taille du canvas en cas de resize (sur mobile)
                    window.addEventListener('resize', function() {
                        $.each($("canvas"), function() {
                            activityId = $( this ).attr("activityId");
                            resizeCanvas(activityId); 
                         });
                    }, false);

                    $("#bLoadMoreActivities").unbind();
                    $("#bLoadMoreActivities").hide();
                    $("#bLoadMoreActivities").click(function(e) {
                        newOffset = $("#activitiesBlockList").data("data-offset");//$('#bLoadNextActivities').attr('data-offset');

                        //alreadyLoaded = true;
                        if( offset == newOffset ) {
                            $("#blocInfoFinish").show(); 
                            $("#bLoadMoreActivities").remove();
                        }
                        else {
                            //console.log("relance:"+ newOffset);
                            loadActivities(newOffset, activitiesTypes, fromUsers, sports, lastModified, fromPublicProfile);     
                        }
                    });
                    
                    $ajaxTreated++;
                    //console.log("ajaxTreated="+$ajaxTreated);
                    if ($ajaxTreated === $max) {
                        $("#newActivitiesLoading").hide();
                        $("#bLoadMoreActivities").show();
                    }
                    else {
                        $("#newActivitiesLoading").show();
                        $("#bLoadMoreActivities").hide();
                    }
                }
            }
        );
    }
}

function savePreferenceNewsFeedFilters(activitiesTypes, activitiesFrom, sports, lastModified) {
    if(typeof(activitiesTypes) == 'undefined'){activitiesTypes = new Array("all")}
    if(typeof(fromUsers) == 'undefined'){fromUsers = new Array("public") }
    if(typeof(lastModified) == 'undefined'){lastModified = true}
    
    var ajaxRequest = $.post(Routing.generate('ksUser_savePreferenceNewsFeedFiltres'), { 
            "activitiesTypes"   : activitiesTypes,
            "activitiesFrom"    : activitiesFrom,
            "sports"            : sports,
            "lastModified"      : lastModified
        }, 
        function(response) {
            if (!response.response) showInformation('Erreur lors de la sauvegarde des préférences merci de nous indiquer par mail ou feedback ce message !');
            else document.location.href = Routing.generate('ksActivity_activitiesList', {});
        }
    ).complete(function() {
        
    });
    
    return ajaxRequest;
}

function publishOnFacebook(activityId, issetGPX, picture, url, title, description, caption, type) {
//    console.log('2');
//    console.log('activityId='+activityId);
//    console.log('issetGPX='+issetGPX);
//    console.log('url='+url);
//    console.log('name='+name);
//    console.log('type='+type);
    FB.ui({
        method: 'feed',
        picture: picture,
        link: url,
        title: title,
        description: description,
        caption: caption
        }, function(response) {
            addUserAction('activityId='+activityId, 'post FB '+ type, 'OK');
        });
}

function publishOnFacebookOLD(activityId, issetGPX, url, name, type) {
    /*console.log('2');
    console.log('activityId='+activityId);
    console.log('issetGPX='+issetGPX);
    console.log('url='+url);
    console.log('name='+name);
    console.log('type='+type);*/
    FB.api(
        "/me/photos",
        "POST",
        {
            "url": url,
            "name": name
        }, 
        function(response) {
            console.log('3');
            if (response.error !=null) {
                //console.log('4');
                //Cas du partage sur FB d'une activité déjà créée (pas très propre coté code mais plus efficace coté rendu écran, les API sont longues et donc tout mettre à la suite donne 3/4 sec d'attente de trop...
                addUserAction('activityId='+activityId, 'post FB '+ type, 'KO', response.error.message);
                if (type == "manuel") showInformation("Ton paramétrage sur Facebook ne permet pas de poster sur ton mur ! <br>Merci d'autoriser Keepinsport à publier sur ton mur. <br><br>Pour se faire, nous t'invitons à te rendre sur cette page : <a target='_blank' href='https://www.facebook.com/settings?tab=applications'> Paramétrage FB </a> de supprimer l'application Keepinsport et de relancer le partage d'activité depuis Keepinsport.");
                //else activityRedirect(issetGPX, activityId);
            }
            else {
                //console.log('5');
                //Cas du partage sur FB d'une activité déjà créée
                addUserAction('activityId='+activityId, 'post FB '+ type, 'OK');
                if (type == "manuel") showInformation("L'activité a été postée sur Facebook !");
                //else activityRedirect(issetGPX, activityId);
            }
        }
    );
}

function activityRedirect(userId, issetGPX, activityId) {
    //console.log(error);
    //Cas de la création OU modification d'une activité avec case à cocher Facebook OK
    if (issetGPX || issetGPX == 'true') {
        //Avec un GPX utilisé
        showInformation("Redirection vers le détail de l'activité...");
        document.location.href = Routing.generate('ksActivity_showActivity', {'activityId' : activityId})
    }
    else {
        //Sans GPX
        showInformation("Redirection vers ton tableau de bord...");
        //document.location.href = Routing.generate('ksActivity_activitiesList', {})
        document.location.href = Routing.generate('ksAgenda_index', {"id" : userId });
    }
}

function prepareRefreshScrollbar() {
    if($("#englobe").size() > 0) {
        englobeHeightBefore = $("#englobe").css('height');
        englobeHeightBefore = englobeHeightBefore.substring(0,englobeHeightBefore.length-2);
        englobeTopBefore = $("#englobe").css('top');
        englobeTopBefore = englobeTopBefore.substring(0,englobeTopBefore.length-2);
    } else {
        englobeHeightBefore = 0;
        englobeTopBefore = 0;
    }
}

function refreshScrollbar () {
    //$('#englobe').unbind();
    //$("#scrollbar").unbind();
    $("#content_body").unbind();
    $("#content_scroll").unbind();

    if($("#englobe").size() > 0) {            
        $(".clear").remove();
        $("#content_body").css({width: '', height: '', overflow: '', position: ''});
        $("#scrollbar").remove()
        $("#content_scroll").unwrap();
        $("#debug").remove();                
    } 

    taille_contenu = $("#content_body").height()+40;

    $("#content_body").scrollbar({debug:false, pas:30});
        
    //On remet le bouton de scroll et le contenu 
    //là où ils devraient être pour faire en sorte que la vision ne change pas
    if( $("#bouton").size() > 0 && $("#scrollbar").size() > 0 && englobeHeightBefore != 0) {

        scrollHeight = $("#scrollbar").css('height');
        scrollHeight = scrollHeight.substring(0,scrollHeight.length-2);

        englobeHeight = $("#englobe").css('height');
        englobeHeight = englobeHeight.substring(0,englobeHeight.length-2);

        englobeTop = $("#englobe").css('top');
        englobeTop = englobeTop.substring(0,englobeTop.length-2);

        boutonHeight = $("#bouton").css('height');
        boutonHeight = boutonHeight.substring(0,boutonHeight.length-2);

        pourcentBouton = englobeHeightBefore / englobeHeight;
        boutonTop = scrollHeight * pourcentBouton - boutonHeight;

        padTop = $("#navbar").height();
        padBot = $(window).height() - $("#navbar").height();
        taille_englobe = padBot - padTop;

        diffHeight = englobeHeight - englobeHeightBefore;
        boutonTopMax = englobeHeight - taille_contenu;
        contenuTopMax = taille_englobe - taille_contenu;

        if( diffHeight == 0) {
            $("#englobe").css('top', (contenuTopMax) + "px");
            $("#bouton").css('top', (boutonTop) + "px");
        } else {
            contenuTop = contenuTopMax * pourcentBouton;
            $("#englobe").css('top', (contenuTop) + "px");
            $("#bouton").css('top', (boutonTop) + "px");
        }       
    } else {
        //pas de scrollbar,
    }
}

function hundred( value ) {
    value = parseInt( value, 10 );
    if ( value >= 0 && value <= 99 ) {
        return ( value < 10 ? "0" : "" ) + value;
    } else {
        return null;
    }
}

function sixty( value ) {
    value = parseInt( value, 10 );
    if ( value >= 0 && value <= 59 ) {
        return ( value < 10 ? "0" : "" ) + value;
    } else {
        return null;
    }
}
   
function addUserAction(action, type, result, error) {
    var ajaxRequest = $.post(Routing.generate('ksActivity_addUserAction', {'action' : action, 'type' : type, 'result' : result, 'error' : error})
        , 
        function(response) {
        }
    ).complete(function() {
    });
    
    return ajaxRequest;
}

function doNothing() {
    
}

var woodPanelIsOK =false;

function reDrawCanvas(character, activityId, activityType, activityCodeSport, scoreCount) {
        var selectedEquipmentsIds = $("select.equipments").select2("val");
        
        var duration;
        
        if ($("#ksActivity_activitySessionType_duration").val() == null || $("#ksActivity_activitySessionType_duration").val() == '') duration = '';
        else if ($("#ksActivity_activitySessionType_duration").val().length < 7) {
            duration = $("#ksActivity_activitySessionType_duration").val() + ':00';
        }
        
        //pour le cas du mode édit
        if (activityCodeSport == '' ) $("#customSelectSports option:selected").attr("codeSport");
        
        var activity = {
            "id"                    : activityId,
            "activityType"          : activityType,
            "sport_codeSport"       : activityCodeSport,
            "sportGround_code"      : $("#customSelectSportsGrounds option:selected").attr("codeSportGround"),
            "issuedAt"              : $("#ksActivity_activitySessionType_issuedAt_time").val(),
            "stateOfHealth_code"    : $("#ksActivity_activitySessionType_stateOfHealth option:selected").text(),
            "description"           : $("#ksActivity_activitySessionType_description").val(),
            "distance"              : $("#ksActivity_activitySessionType_distance").val(),
            "denPos"                : $("#ksActivity_activitySessionType_elevationGain").val(),
            "duration"              : duration
        };
        
        //console.log(activity);
        
        //Récupération des données équipments liées aux IDs
        selectedEquipments= new Array();
        for ($i=0;$i<selectedEquipmentsIds.length;$i++) {
            selectedEquipments[$i] = new Array();
            //console.log(selectedEquipmentsIds[$i]);
            for($j=0;$j<allEquipments.length;$j++) {
                if (selectedEquipmentsIds[$i] == allEquipments[$j]['id']) {
                    selectedEquipments[$i] = allEquipments[$j];
                }
            }
        }
    
        var activityId = activity.id;
        
        var cBg = document.getElementById('canvas_background');
        var ctxBG = cBg.getContext('2d');
        ctxBG.save();
        ctxBG.setTransform(1, 0, 0, 1, 0, 0);
        ctxBG.clearRect(0, 0, cBg.width, cBg.height);
        ctxBG.restore();
        
        var c = document.getElementById('canvas_activity');
        var ctx = c.getContext('2d');
        ctx.save();
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, c.width, c.height);
        ctx.restore();
        
        var sky = null;
        var perso = null;
        var clothing = null;
        var accessories = null;
        var landscape = null;
        var equipments = null;
        var timerCanvas = null;
    
        var persoX = 0;
        var persoY = 0;
        
        var paramsLandscape = {};
        var paramsBoard = {};
        var paramsEquipments = {};
        var paramsClothing = {};
        var paramsSky = {};
        var params = {};
        var bubbleText = "";
        var xBubble = persoX + 130;
        params.persoX = persoX;
        params.persoY = persoY;
        
        paramsSky.drawDay = true;
        paramsSky.drawClouds = true;
        
        //Initialisation des éléments
        sky = new Sky(cBg, imagesPath);
        sky.initClouds(5);
        landscape = new Landscape(cBg, imagesPath);
        perso = new Perso(c);
        clothing = new Clothing(c, imagesPath);
        equipments = new Equipments(c, imagesPath);
        accessories = new Accessory(c, imagesPath);
        
        //console.log(activity);
        
        if (activity != null) {
            if (activity.activityType != null) params.activityType = activity.activityType;
            if (activity.sport_codeSport != null) paramsLandscape.codeSport = activity.sport_codeSport;
            if (activity.sportGround_code != null) paramsLandscape.groundCode = activity.sportGround_code;
            if (activity.description != null) bubbleText = activity.description;
            if (activity.distance !=null) paramsBoard.distance = activity.distance;
            if (activity.denPos != null) paramsBoard.denPos = activity.denPos;
            if (activity.duration != null) paramsBoard.duration = activity.duration;
        }
        
        //console.log(scoreCount);
        if (scoreCount != null) {
            paramsBoard.scores = new Array();
            for ($i=0;$i<scoreCount;$i++) {
               paramsBoard.scores.push({
                   'me'  : $('#ksActivity_activitySessionType_scores_' + $i + '_score1').val(),
                   'opponent' : $('#ksActivity_activitySessionType_scores_' + $i + '_score2').val()
               });
            }
            //console.log(paramsBoard.scores);
        }
        params.paramsBoard = paramsBoard;
        
        if (activity.issuedAt != null) {
            if ( activity.issuedAt.substr(0,2) > "20" || activity.issuedAt.substr(0,2) < "08") {
                    paramsSky.drawDay = false;
                    paramsSky.drawNight = true;
                    paramsSky.drawClouds = true;
            }
            else {
                    paramsSky.drawDay = true;
                    paramsSky.drawNight = false;
                    paramsSky.drawClouds = true;
            }
        }
        
        if (character != null) {
            if ( character.sexeCode != null ) perso.sexeCode = character.sexeCode;  
            if ( character.skinColor != null ) perso.skinColor = character.skinColor;  
            if ( character.hairColor != null ) perso.hairColor = character.hairColor;  
            if ( character.eyesColor != null ) perso.eyesColor = character.eyesColor;  
            if ( character.shirtColor != null ) perso.shirtColor = character.shirtColor;  
            if ( character.shortColor != null ) perso.shortColor = character.shortColor;  
            if ( character.shoesPrimaryColor != null ) perso.shoesPrimaryColor = character.shoesPrimaryColor;  
            if ( character.shoesSecondaryColor != null ) perso.shoesSecondaryColor = character.shoesSecondaryColor;
        }
        
        if ( activity.stateOfHealth_code != null) perso.face = activity.stateOfHealth_code;
        
        //console.log(selectedEquipments);
        if (selectedEquipments != null) {
            for ($i=0;$i<selectedEquipments.length;$i++) {
                //console.log(selectedEquipments[$i]);
                if (selectedEquipments[$i].type_code == "armband") paramsEquipments.drawArmband = true;
                if (selectedEquipments[$i].type_code == "watch") paramsEquipments.drawWatch = true;
                if (selectedEquipments[$i].type_code == "road_bike") {
                    paramsEquipments.drawRoadBike = true;
                    if (selectedEquipments[$i].primaryColor != null) {
                        equipments.bikeColor = selectedEquipments[$i].primaryColor;
                    }
                }
                if (selectedEquipments[$i].type_code == "mountain_bike") {
                    paramsEquipments.drawMountainBike = true;
                    if (selectedEquipments[$i].primaryColor != null) {
                        equipments.bikeColor = selectedEquipments[$i].primaryColor;
                    }
                }
                if (selectedEquipments[$i].type_code == "tennis_racquet") paramsEquipments.drawTennisRacquet = true;
                if (selectedEquipments[$i].type_code == "shirt" && selectedEquipments[$i].primaryColor != null) perso.shirtColor = selectedEquipments[$i].primaryColor;
                if (selectedEquipments[$i].type_code == "short" && selectedEquipments[$i].primaryColor != null) perso.short = selectedEquipments[$i].primaryColor;
                if (selectedEquipments[$i].type_code == "shoes") {
                    if (selectedEquipments[$i].primaryColor != null) perso.shoesPrimaryColor = selectedEquipments[$i].primaryColor;
                    if (selectedEquipments[$i].secondaryColor != null) perso.shoesSecondaryColor = selectedEquipments[$i].secondaryColor;
                }
                if (selectedEquipments[$i].type_code == "jacket") {
                    if (selectedEquipments[$i].primaryColor != null) clothing.jacketColor = selectedEquipments[$i].primaryColor;
                    paramsClothing.drawJacket = true;
                }
                if (selectedEquipments[$i].type_code == "gloves") {
                    if (selectedEquipments[$i].primaryColor != null) clothing.glovesColor = selectedEquipments[$i].primaryColor;
                    paramsClothing.drawGloves = true;
                }
                if (selectedEquipments[$i].type_code == "pants") {
                    if (selectedEquipments[$i].primaryColor != null) clothing.pantsColor = selectedEquipments[$i].primaryColor;
                    paramsClothing.drawPants = true;
                }
                if (selectedEquipments[$i].type_code == "footballSocks") {
                    if (selectedEquipments[$i].primaryColor != null) clothing.footballSocksColor = selectedEquipments[$i].primaryColor;
                    paramsClothing.drawFootballSocks = true;
                }
                if (selectedEquipments[$i].type_code == "bonnet") {
                    if (selectedEquipments[$i].primaryColor != null) clothing.bonnetPrimaryColor = selectedEquipments[$i].primaryColor;
                    if (selectedEquipments[$i].secondaryColor != null) clothing.bonnetSecondaryColor = selectedEquipments[$i].secondaryColor;
                    paramsClothing.drawBonnet = true;
                }
            }
        }
        
        //Affichage du dessin
        sky.draw( paramsSky );
        landscape.draw(paramsLandscape);
        perso.draw(persoX, persoY);
        clothing.draw(paramsClothing);
        equipments.draw( paramsEquipments );
        accessories.draw( params );
        
        if( bubbleText != "" ) {
            //On ne dessine pas la bulle si l'écran est trop petit
            if( document.body.clientWidth >= 963 ) {
                drawTextBubble(c, xBubble, 20, c.width - xBubble - 10, bubbleText);
            }
        }
        
        //Redessin de certaines parties
        if( paramsEquipments.drawTennisRacquet ) {
            perso.drawRightHand();
        }
        
        
    }
    
function getActivityTypesSelect() {
    activitiesTypes = $("#activityTypeSelect").val();

    if( ! $.isArray(activitiesTypes)) {
        activitiesTypes = new Array();
    }

    return activitiesTypes;
}

function getFromUsersSelect() {
    fromUsers = $("#fromUsersSelect").val();

    if( ! $.isArray(fromUsers)) {
        fromUsers = new Array();
    }

    return fromUsers;
}

function getSportsSelect() {
    sports = $("#ksSportTypeMultiSimple_sport").val();

    if( ! $.isArray(sports)) {
        sports = new Array();
    }

    return sports;
}

function traceEvolutionBySaisonGraph(id, userId, clicActive) {
    $.get(
        Routing.generate('ksDashboard_getDataGraphPointsBySportByMonth', {'id' : userId} ), 
        function(response) {
            $("#"+ id + "Loader").hide();

            if( Object.keys(response.chart.points).length > 0) {
                var highchartsOptions = {};
                highchartsOptions.chart = {
                    renderTo: id + 'Container',
                    type: 'column'
                };

                highchartsOptions.title = {
                    text: ''
                };
                
                highchartsOptions.series = [];
                highchartsOptions.xAxis = xAxisOptions;
                highchartsOptions.yAxis = yAxisOptions;
                highchartsOptions.yAxis[0].stackLabels.useHTML = true; 
                //On monte le label pour pouvoir rentrer le spoints et les étoiles
                        highchartsOptions.yAxis[0].stackLabels.y = -20;
                highchartsOptions.yAxis[0].stackLabels.formatter = function() {
                    var str = '';
                    //console.log();
                    var leagueId = response.chart.leaguesIds[this.x];
                    var league = getLeague( response.leagues, leagueId);

                    if( league != null ) {
                        
                        
                        //On écrit les étoiles
                        str += '<span class="' + league.categoryCode + '">';
                        
                        switch( parseInt( league.starNumber ) ) {
                            case 3:
                                str += '<i class="icon-star"></i><i class="icon-star"></i><i class="icon-star"></i>';
                                break;
                            case 2:
                                str += '<i class="icon-star"></i><i class="icon-star"></i><i class="icon-star-empty"></i>';
                                break;
                            case 1:
                                str += '<i class="icon-star"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>';
                                break;
                            default:
                                str += '<i class="icon-star-empty"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>';
                                break;
                        }
                        
                        str += '</span>';
                    }
                    str += '<br/>';
                    str += this.total;
                    return str; 
                };
                highchartsOptions.tooltip = tooltipStackingGraphOptions;

                /*highchartsOptions.labels = {
                    items: [{
                        html: 'Cumul des points: ' +response.cumulPoints,
                        style: {
                            left: '40px',
                            top: '8px',
                            color: 'black'
                        }
                    }]
                };*/
                highchartsOptions.plotOptions = plotOptionsGraphOptions;
                highchartsOptions.plotOptions.column.stacking = "normal";
                //highchartsOptions.plotOptions.column.dataLabels.zIndex = 10;
                //highchartsOptions.plotOptions.bubble.zIndex = 0;


                //FMO : volontairement désactiver car pas trop de sens d'afficher le détail par mois
                /*
                if( clicActive ) {
                    highchartsOptions.plotOptions.series.point.events.click = function(e) {
                        var nbMonths = this.series.xData.length;
                        indexPreviousMonthSelected = ( nbMonths - 1 ) - this.x;
                        sportIdSelected = this.series.options.id ? this.series.options.id : this.options.id;
                        var parameters = {
                            "userId"                : userId,
                            "sportId"               : sportIdSelected,
                            "indexPreviousMonth"    : indexPreviousMonthSelected
                        };
                        
                        getDataGraphDependingOnSport( parameters );
                        loadActivitiesByParameters( parameters );
                    }
                }
                */
                
                //console.log(response.chart.leaguesIds);
                
                /*highchartsOptions.plotOptions.line = {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }*/
                
                /*highchartsOptions.plotOptions.column = {
                    stacking: "normal",
                    stackLabels: {
                      enabled: true,
                      useHTML: true,
                      formatter: function() {
                        return '<i class="icon-star"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>'; 
                      }
                      //y: 0
                    }
                };*/

                highchartsOptions.credits = creditsOptions;

                $.each( response.sports, function(sportId, sportLabel) {

                    highchartsOptions.series.push({
                        id:   sportId,
                        name: sportLabel,
                        data: response.chart.points[sportId]
                    });

                });
                
                /*highchartsOptions.series.push({
                    name: 'Ligues',
                    color: '#89A54E',
                    type: 'scatter',
                    data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                    yAxis: 1,
                    tooltip: {
                        valueSuffix: '°C'
                    }
                });*/
                

                //Camembert pour cumul des sports
                /*highchartsOptions.series.push({
                    type:   'pie',
                    name: "cumul",
                    data: response.chart.cumulForPie,
                    center: [100, 80],
                    size: 100,
                    showInLegend: false,
                    dataLabels: {
                        enabled: false
                    }
                });*/

                highchartsOptions.xAxis.categories = response.periods;
                new Highcharts.Chart(highchartsOptions);
            } else {
                $("#"+ id + 'Container').html("Tu n'as encore réalisé aucune activité.")
            }
        }
    );
}

function getLeague( leagues, leagueId ) {
    var returnLeague = null;
    $.each( leagues, function( key, league ) {
        //console.log( league.id+ " - " + leagueId)
        if( league.id == leagueId) {
            returnLeague = league;
            return false;
        }
    });
    return returnLeague;
}

 var colors = Highcharts.getOptions().colors;
  
 xAxisOptions = {
    categories: []
};

yAxisOptions = [{
    min: 0,
    title: {
        text: 'Points'
    },
    //Label des valeurs cumulées en haut des barres
    stackLabels: {
        enabled: true,
        style: {
            fontWeight: 'bold',
            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
        },
        useHTML: false,
        formatter: function() {
            return this.total; 
        },
        y: 0
    },
    labels: {
        formatter: function() {
            //Pour ne pas que ça transforme "1000" en "1k"
            return this.value;
        }
    }
}/*,{ min: 0,
    title: {
        text: 'Ligue'
    },
    //Label des valeurs cumulées en haut des barres
    stackLabels: {
        style: {
            fontWeight: 'bold',
            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
        }
    },
    labels: {
        formatter: function() {

            return this.value;
        }
    },
    opposite: true
}*/];

tooltipStackingGraphOptions = {
    formatter: function() {
        var s;
        if (this.point.name) { // the pie chart
            s = this.point.name +': '+ this.y +' points';
        } else {
            s = '<b>'+ this.x +'</b><br/>'+
            this.series.name +': '+ this.y +'<br/>'+
            'Total: '+ this.point.stackTotal;
        }
        return s;
    }
};

tooltipGraphOptions = {
    formatter: function() {
        return '<b>'+ this.x +'</b><br/>'+
        this.series.name +': '+ this.y;
    }
};

plotOptionsGraphOptions = {
    column: {
        stacking: 'normal',
        dataLabels: {
            enabled: false,
            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
        }
    },
    series: {
        cursor: 'pointer',
        point: {
            events: {}
        }
    },
    pie: {
        colors: {0: 'rgb(67, 67, 72)', 1: 'rgb(124, 181, 236)'}
    }
};

plotOptionsPieOptions = {
    pie: {
        //allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
            enabled: true,
            //distance: -30,
            //color: 'white',
            formatter: function() {
                return this.y;
            }
        },
        showInLegend: true
    },
    series: {
        cursor: 'pointer',
        point: {
            events: {}
        }
    }
};

creditsOptions = {enabled:false};

function createEncodings(coords) {
	var i = 0;
 
	var plat = 0;
	var plng = 0;
 
	var encoded_points = "";
 
	for(i = 0; i < coords.length; ++i) {
	    var lat = coords[i][0];				
		var lng = coords[i][1];		
 
		encoded_points += encodePoint(plat, plng, lat, lng);
 
	    plat = lat;
	    plng = lng;
	}
 
	// close polyline
	//encoded_points += encodePoint(plat, plng, coords[0][0], coords[0][1]);
 
	return encoded_points;
    }
 
    function encodePoint(plat, plng, lat, lng) {
        var late5 = Math.round(lat * 1e5);
        var plate5 = Math.round(plat * 1e5)    

        var lnge5 = Math.round(lng * 1e5);
        var plnge5 = Math.round(plng * 1e5)

        dlng = lnge5 - plnge5;
        dlat = late5 - plate5;

        return encodeSignedNumber(dlat) + encodeSignedNumber(dlng);
    }

    function encodeSignedNumber(num) {
        var sgn_num = num << 1;

        if (num < 0) {
            sgn_num = ~(sgn_num);
        }
        return(encodeNumber(sgn_num));
    }

    function encodeNumber(num) {
        var encodeString = "";

        while (num >= 0x20) {
            encodeString += (String.fromCharCode((0x20 | (num & 0x1f)) + 63));
            num >>= 5;
        }

        encodeString += (String.fromCharCode(num + 63));
        return encodeString;
    }