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

function createNewArticle(categoryId) {
    var $newArticleModal = $("#newArticleInCategory" + categoryId + "_modal");
    var $modalBody       = $newArticleModal.find('.modal-body');
    var $modalFooter     = $newArticleModal.find('.modal-footer');
    var $articleForm     = $modalBody.find("form");
    var $brand           = $articleForm.find("input.articleBrand");
    var $articleLabel    = $articleForm.find("input.articleLabel");
    var $categoryBloc    = $articleForm.find(".categoryBloc");
    var $errorsBloc      = $modalBody.find(".errorsBloc");
    var $messagesBloc    = $modalBody.find(".messagesBloc");
    var $cancelButton    = $modalFooter.find('a.cancel');
    var $createButton    = $modalFooter.find('a.create');
    var $loader          = $modalFooter.find('img.loader');
    var $message         = $modalFooter.find('.message');
    var $type            = $articleForm.find('.customSelectEquipmentType'); //equipment type
    var $category        = $articleForm.find('.categoryTag'); //category de l'article

    $categoryBloc.hide();
    $brand.val("");
    $articleLabel.val("");
    $articleForm.show();
    $errorsBloc.html("");
    $errorsBloc.hide();
    $messagesBloc.html("");
    $messagesBloc.hide();
    $createButton.show();
    $cancelButton.html("Annuler");
    $loader.hide();
    $message.hide();
    $type.hide();

    var closeModal = function() {

        $confirmModal.modal('hide'); 
    }
    
    var createArticle = function() {
        if ( !$createButton.hasClass( "disabled" ) ) {
            articleLabel = $articleLabel.val();
            brand = $brand.val();

            $("#selectedTypeFromMenu").val($customSelectType_fromMenu.select2("val"));
            $("#selectedBrand_fromMenu").val(brand);

            if( articleLabel == "" ) {
                /*le fait d'afficher le div error met la modal derriere le layout principal...
                $errorsBloc.html("Le titre de l\'article ne doit pas être vide !");
                */
                $articleLabel.val("Le titre de l\'article ne doit pas être vide !");
            } else if (articleLabel == "Choisir un titre") {
                $articleLabel.val("Le titre de l\'article ne doit pas être Choisi un titre !");
            } else if (articleLabel !== "Le titre de l\'article ne doit pas être vide !" && articleLabel !== "Le titre de l\'article ne doit pas être \'Choisir un titre\' !") {
                $(".error_list").hide();
                $createButton.addClass( "disabled" );
                $loader.show();
                $message.show();
                $.post(
                    //form.attr('action'), 
                    Routing.generate('ksArticle_create', { "categoryId" : categoryId }),
                    $articleForm.serialize(), 
                    function(response) {
                    if ( response.response == 1 ) {
                        $articleForm.hide();

                        paragraph = $("<p>").html("Pas de doublon existant, l'article '" + response.article.label + "' a été créé avec succès, vous pouvez compléter les données !");
                        $messagesBloc.append( paragraph );

//                            articleLink = $("<a>", { "class": "btn btn-wikisport", href : Routing.generate('ksWikisport_edit', { 'id': response.article.id }) }).html( "Editer l'article" );
//                            $messagesBloc.append( articleLink );
                        $messagesBloc.show();

                        $createButton.hide();
                        $cancelButton.html("Fermer");
                        window.open(Routing.generate('ksWikisport_edit', {'id' : response.article.id}),'_blank');

                    } else {
                        processingErrorsForm( $errorsBloc, response.errors );
                    }
                    $createButton.removeClass( "disabled" );
                    $loader.hide();
                    $message.hide();
                }).error(function(xqr, error) {
                    console.log("error " + error);
                });
            }
        }
    };

    $createButton.unbind();
    $createButton.click(function() {
        createArticle();
    });

    $articleLabel.keypress(function(e) {
        if(e.which == 13) {
            createArticle();
            e.preventDefault();
        }
    });

    $newArticleModal.on('shown', function() {
        $articleLabel.focus();
    });

    $newArticleModal.modal('show');
    $newArticleModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
        e.stopPropagation();
    });
    
    //$articleLabel.val("Choisir un titre");
    $articleLabel.attr('placeholder','Choisir un titre');
    $brand.attr('placeholder','Choisir une marque');
    
    if (categoryId == -1) {
        $modalFooter.hide();
        $articleLabel.hide();
        $brand.hide();
        $type.hide();
        $createButton.addClass( "disabled" );
    }
    else if (categoryId == 5) {
        $type.show();
        $brand.show();
        $category.hide();
        $createButton.removeClass( "disabled" );
    }
    else {
        $category.hide();
        $createButton.removeClass( "disabled" );
    }
    
    $category.change(function() {
        $articleLabel.show();
        $modalFooter.show();
        
        categoryId = $('#ks_activitybundle_articletype_categoryTag').val();
        
        if (categoryId == 5) {
            //Article de catégorie "matériel" on affiche le type qui est obligatoire
            $type.show();
            $brand.show();
            $createButton.removeClass( "disabled" );
        }
        else if (categoryId == '') {
            $createButton.addClass( "disabled" );
        }
        else {
            $type.hide();
            $brand.hide();
            $createButton.removeClass( "disabled" );
        }
    });
}