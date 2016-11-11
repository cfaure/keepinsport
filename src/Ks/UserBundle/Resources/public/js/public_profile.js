$(document).ready(function() { 
    $("#sportsButtons > input").click(function(e) {            
            e.preventDefault(); 
            $( "#activitiesBlockList" ).html("");
            var sportId = $( this ).attr('sportId');
            loadSessionActivities(0, sportId);

            return false;
    });

    $("#blocInfoFinish").hide(); 
    
    loadSessionActivities(0);
    $().timelinr({
        containerDiv : "#historic"
    });          
});

function loadSessionActivities(offset, sportId) {

    //paramètre facultatif
    if(!sportId){sportId="all"}
    
    var alreadyLoaded = false;
    
    //prepareRefreshScrollbar();
    
    $('#newActivitiesLoading').show();
    
    $.get(
        Routing.generate('ksActivity_loadSportSessions', {'offset' : offset, 'sportId' : sportId}),
        function(response) {
            $('#activitiesBlockList').append(response.html);
            //$('#bLoadNextActivities').attr("data-offset", response.offset)
            $("#activitiesBlockList").data("data-offset", response.offset);
            //refreshTimeline();
        }
    ).complete(function() {
        $('#newActivitiesLoading').hide();    
        
        //refreshScrollbar(); 

        //2venement sur le scroll qui regarde si on est en bas pour charger les autres activités
        /*$('#content_scroll').mousewheel(function(event, delta) {
            if (!alreadyLoaded) {
                if( $("#bouton").size() > 0 && $("#scrollbar").size() > 0 ) {
                    bouttonTop = $("#bouton").css('top');
                    bouttonTop = bouttonTop.substring(0,bouttonTop.length-2);

                    scrollHeight = $("#scrollbar").css('height');
                    scrollHeight = scrollHeight.substring(0,scrollHeight.length-2);

                    boutonHeight = $("#bouton").css('height');
                    boutonHeight = boutonHeight.substring(0,boutonHeight.length-2);

                    if(delta < 0 && bouttonTop == (scrollHeight - boutonHeight)) {
                        newOffset = $("#activitiesBlockList").data("data-offset");//$('#bLoadNextActivities').attr('data-offset');
                        
                        alreadyLoaded = true;
                        if( offset == newOffset ) $("#blocInfoFinish").show(); 
                        else {
                            loadSessionActivities(newOffset, sportId);      
                        }
                    }
                } else {
                    newOffset = $("#activitiesBlockList").data("data-offset");
                    
                    alreadyLoaded = true;
                    if( offset == newOffset ) $("#blocInfoFinish").show(); 
                    else {    
                        loadSessionActivities(newOffset, sportId);      
                    }
                }
            } else {
                //return false;
            }
        });*/
    })
}

