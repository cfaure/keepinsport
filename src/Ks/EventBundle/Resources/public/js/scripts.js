function attachUserParticipatesEvent_Event(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $.get(
               $(this).attr('href'),
               function(data) {
                   if (data.participateResponse == -1) modalInfo(data.errorMessage);
                   else $( elt ).parent().html(data.userParticipatesEventLink);
                   elt.removeClass("disabled");
               }
           )
        }
        e.preventDefault();
        return false;
    });
}

function attachUserNotParticipatesAnymoreEvent_Event(elt) {
    elt.click(function(e) {
        if( !elt.hasClass("disabled") ) {
            elt.addClass("disabled");
            $.get(
                $(this).attr('href'),
                function(data) {
                    if (data.participateResponse == -1) modalInfo(data.errorMessage);
                    else $( elt ).parent().html(data.userParticipatesEventLink);
                    elt.removeClass("disabled");
                }
            )
        }
        e.preventDefault();
        return false;
    });
}