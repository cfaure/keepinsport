function formatResult(result) {
    var returnStr = ""
    if (result.type && result.type != '' ) {
        if( result.type == "user" ) {
            returnStr += "<span class='icon-user' style='color:#12A24A'></span> ";
        }
        if( result.type == "club" ) {
            returnStr += "<span class='icon-group' style='color:#F8C70B'></span> ";
        }
        if( result.type == "article" ) {
            returnStr += "<span class='icon-edit' style='color:#DB2033'></span> ";
        }
        if( result.type == "event" ) {
            if( result.club_id != null ) {
                returnStr += "<span class='icon-calendar' style='color:#F8C70B'></span> ";
            }
            else if( result.user_id != null ) {
                returnStr += "<span class='icon-calendar' style='color:#1787C7'></span> ";
            }
            else {
                returnStr += "<span class='icon-calendar' style='color:#DB2033'></span> ";
            }
        }
    }
    returnStr += result.text;

    return returnStr;
}

function initSelect2ForResearch( select, translations ) {
    select.select2({
        placeholder: translations.placeholder,
        formatNoMatches: function () { return translations["no-matches-found"] },
        formatSearching: function () { return translations.searching; },
        width : 400,
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: Routing.generate('ksSearch'),
            dataType: 'json',
            data: function (term, page) {
                return {
                    term: term,
                    page_limit: 10
                };
            },
            results: function (data, page) { 
                return {results: data.results};
            }
        },
        formatResult: formatResult
    }).on("change", function(e) { 
        var selectElement = select.select2("data");

        if( selectElement.type == "user" ) {
            window.location.href = Routing.generate('ksUser_show', {'id' : selectElement.id});
        }
        if( selectElement.type == "club" ) {
            window.location.href = Routing.generate('ksClub_show', {'id' : selectElement.id});
        }
        if( selectElement.type == "article" ) {
            window.location.href = Routing.generate('ksWikisport_show', {'id' : selectElement.id});
        }
        if( selectElement.type == "event" ) {
            window.location.href = Routing.generate('ksEvent_show', {'id' : selectElement.id});
        }
    });
}