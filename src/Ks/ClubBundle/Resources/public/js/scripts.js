function loadClubActivities( clubId, offset, activitiesTypes, byLastModified) {

    //paramètre facultatif
    if(typeof(activitiesTypes) == 'undefined'){activitiesTypes = new Array("all")}
    if(typeof(fromUsers) == 'undefined'){fromUsers = "all" };
    if(typeof(byLastModified) == 'undefined'){byLastModified = true}
    
    
    var alreadyLoaded = false;
    
    //prepareRefreshScrollbar();
    $("#blocInfoFinish").hide();
    $('#newActivitiesLoading').show();
    $("#blocMoreActivitiesToLoad").hide();

    var ajaxRequest = $.post(Routing.generate('ksClub_loadClubActivities', {'clubId' : clubId, 'offset' : offset}), { 
            "activitiesTypes"   : activitiesTypes,
            "byLastModified"      : byLastModified
        }, 
        function(response) {
            
            $('#activitiesBlockList').append(response.html);
            $("#blocMoreActivitiesToLoad").show();
            //$('#bLoadNextActivities').attr("data-offset", response.offset)
            if( offset == response.offset ) {
                $("#blocInfoFinish").show(); 
                $("#blocMoreActivitiesToLoad").hide();
            } else {
                $("#activitiesBlockList").data("data-offset", response.offset);
            }
            
        }
    ).complete(function() {
        // FMO : retour au mode avec "bouton" pour permettre d'accéder à la boite de feedback
        $('#newActivitiesLoading').hide();  
        
        $("#bLoadMoreActivities").show();
        $("#bLoadMoreActivities").unbind();
        $("#bLoadMoreActivities").click(function(e) {
            newOffset = $("#activitiesBlockList").data("data-offset");//$('#bLoadNextActivities').attr('data-offset');

            //alreadyLoaded = true;
            if( offset == newOffset ) {
                $("#blocInfoFinish").show(); 
                $("#bLoadMoreActivities").remove();
            }
            else {
                loadClubActivities(clubId, newOffset, activitiesTypes, byLastModified);     
            }
        });
        
        /* Ancienne version avec utilisation du scroll de la souris
        $('#newActivitiesLoading').hide();  
        
        $(window).off("scroll");
        
        newOffset = $("#activitiesBlockList").data("data-offset");
        
        if( offset < newOffset ) {
            $("#blocMoreActivitiesToLoad").show();
            
            $(window).scroll(function()
            {
                if($(window).scrollTop() == $(document).height() - $(window).height())
                {  
                    loadClubActivities(clubId, newOffset, activitiesTypes, byLastModified);     
                    $(window).off("scroll");
                }
            });
        }
        */
    });
    
    return ajaxRequest;
}

function attachAskForMembershipEvent(elt) {
    elt.click(function(e) {
        $.get(
            $(this).attr('href'),
            function(data) {
                if (data.response == -1) modalInfo(data.errorMessage);
                else {
                    $( elt ).parent().html(data.askForMembershipLink);
                    showInformation(data.message);
                }
            }
        )
        return false;
    });
}

function attachRemoveAskForMembershipEvent(elt) {
    elt.click(function(e) {

        $.get(
            $(this).attr('href'),
            function(data) {
                if (data.response == -1) showInformation(data.errorMessage);
                else $( elt ).parent().html(data.askForMembershipLink);
            }
        )
        return false;
    });
}

function attachDeleteUserFromClubEvent(clubId, userId) {
    
    var callback = function() {
        document.location.href = Routing.generate('ksClubAdmin_deleteClubUser', { "clubId" : clubId, "userId": userId });
    };
    
    var message = "Tu es sur le point de supprimer ce membre (action irréversible)<br/>Es-tu sûr de vouloir continuer ?";

    askConfirmation(message, 'clubs', callback, null);
    return false;
}
