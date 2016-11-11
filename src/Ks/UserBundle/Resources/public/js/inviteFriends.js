var isConnectedToFB = false;
var isConnectedToGoogle = false;
var accessTokenFacebook = '';
var accessTokenGoogle = '';
var ajaxRequests = new Array();

var abortOtherAjaxRequests = function(ajaxRequest) {
    for ( var i = 0; i < ajaxRequests.length; i++) {
        ajaxRequests[i].abort();
    }
    ajaxRequests.push(ajaxRequest);
};

function addUserAction(action, type, result, error) {
    var ajaxRequest = $.post(Routing.generate('ksActivity_addUserAction', {'action' : action, 'type' : type, 'result' : result, 'error' : error})
        , 
        function(response) {
        }
    ).complete(function() {
    });
    
    return ajaxRequest;
}

//Envoi requete facebook aux utilisateurs selectionnés
function sendRequestToRecipients(userFbIds) {
    console.log(userFbIds);
    console.log(userFbIds.join(','));
    FB.ui({
        method: 'apprequests',
        title: 'Rejoins Keepinsport',
        message: 'Rejoins notre belle communauté de sportifs !',
        to: userFbIds.join(',')
    }, requestCallback);
}


//Secteur multiple d'amis facebook
function sendRequestViaMultiFriendSelector() {
    if( !$("#inviteFacebookFriendsButton").hasClass("disabled") ) {
        FB.ui({
            method: 'apprequests',
            message: 'Rejoins notre belle communauté de sportifs !'
        }, requestCallback);
    }
}

function requestCallback(response) {
    if( response.error != null ) {
        //console.log(response);
        addUserAction(response.to.length+' invit envoyées', 'invit FB', 'KO', response.error.message);
    } else {
        addUserAction(response.to.length+' invit envoyées', 'invit FB', 'OK');
        showInformation(response.to.length + " invitation(s) envoyée(s) sur facebook !");
        return 0;
    }
    return 0;
}

var sendFriendRequests = function(userKsIds) {
    //Envoi des Ids Ks au script qui permet de mettre en relation plusieurs personnes
    $.post(
        Routing.generate('ksFriends_sendFriendRequests'), 
        {
            "userIds" : userKsIds
        },
        function(response) {
            showInformation(response.nbSendedRequests + " demande(s) d'ami envoyée(s)");
            loadFacebookFriendsAndGoogleContacts();
        }
    );
};

var sendMailInvitations = function(emailAdresses) {
    //Envoi des Ids Ks au script qui permet de mettre en relation plusieurs personnes
    $.post(
        Routing.generate('ksFriends_sendMailInvitations'), 
        {
            "emailAdresses" : emailAdresses
        },
        function(response) {
            showInformation(response.nbSendedMails + " invitation(s) envoyée(s) par mail");
        }
    );
};

var finalizeContactsOnKsLoad = function(contactsOnKsHtml) {

    $("#contactsOnKsLoader").hide();
    $("#contactsOnKsContainer").html(contactsOnKsHtml);
    $("#sendFriendRequestButton").show();

    //On affiche les tooltip au survol
    $("img[rel=tooltip]").tooltip();

    //On clic sur la checkbox "tout selectionner", on coche les cases visibles du tableau
    $("#contactsOnKsContainer table").find("input[type=checkbox].selectAllCb").click(function() {
        var $table = $( this ).parent().parent().parent().parent();
        if( $( this ).is(':checked') ) {
            $table.find("tbody").find("input[type=checkbox]").attr('checked', true);
        } else {
            $table.find("tbody").find("input[type=checkbox]").attr('checked', false);
        }
    });

    //Lorsqu'on décoche une case dans le tableau, on décoche "tout selectionner"
    //FIXME CD: TO DO

    //Au clic sur le bouton "+" sur chaque ligne du tableau "Ils sont déjà inscrit sur facebook"
    $("#contactsOnKsContainer table").find(".plusButton").click(function() {
        if( !$( this ).hasClass("disabled") ) {
            var userKsIds = new Array();
            var $tr = $( this ).parent().parent();
            var userKsId = $tr.attr("ksId");

            userKsIds.push(userKsId);

            var nbSendedRequests = sendFriendRequests(userKsIds);
            showInformation( nbSendedRequests );
        }
    });

    //On transforme les tableaux renvoyés en datatablme
    $("#contactsOnKsContainer table").dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            null,
            { "bSortable": false },
            { "bSortable": false },
        ]
    }); 
}

var finalizeContactsNotOnKsLoad = function(contactsNotOnKsHtml) {
    $("#contactsNotOnKsLoader").hide();
    $("#contactsNotOnKsContainer").html(contactsNotOnKsHtml);
    $("#sendInvitationsButton").show();

    //On affiche les tooltip au survol
    $("img[rel=tooltip]").tooltip();

    //On clic sur la checkbox "tout selectionner", on coche les cases visibles du tableau
    $("#contactsNotOnKsContainer table").find("input[type=checkbox].selectAllCb").click(function() {
        var $table = $( this ).parent().parent().parent().parent();
        if( $( this ).is(':checked') ) {
            $table.find("tbody").find("input[type=checkbox]").attr('checked', true);
        } else {
            $table.find("tbody").find("input[type=checkbox]").attr('checked', false);
        }
    });

    //Lorsqu'on décoche une case dans le tableau, on décoche "tout selectionner"
    //FIXME CD: TO DO

    //Au clic sur le bouton "+" sur chaque ligne du tableau "Invite tes amis à te rejoindre"
    $("#contactsNotOnKsContainer table").find(".plusButton").click(function() {
        if( !$( this ).hasClass("disabled") ) {
            var emailAdresses = new Array();
            var userFbIds = new Array();
            var $tr = $( this ).parent().parent();

            if( $tr.attr("fbId") !== undefined )
                userFbIds.push( $tr.attr("fbId") );

            if( $tr.attr("email") !== undefined )
                emailAdresses.push( $tr.attr("email") );

            var nbSendedMails = nbSendedRequests = 0;

            if( emailAdresses.length > 0 ) {
                sendMailInvitations(emailAdresses);
            }

            //console.log(userFbIds);
            if( userFbIds.length > 0 ) {
                sendRequestToRecipients( userFbIds );
            }
        }
    });

    //On transforme les tableaux renvoyés en datatablme
    $("#contactsNotOnKsContainer table").dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            null,
            { "bSortable": false },
            { "bSortable": false },
        ]
    }); 
}       

//Information pour l'application google
var clientId = '774996425691.apps.googleusercontent.com';
var apiKey = 'AIzaSyAL3oC2JfcvDNkxhb0rx0VeemXMJaSLWzA';
//var scopes = 'https://www.googleapis.com/auth/plus.me';
var scopes = 'https://www.google.com/m8/feeds';

function handleClientLoad() {
    if ( gapi.client ) {
        gapi.client.setApiKey(apiKey);
        window.setTimeout(checkAuthGoogle, 1);
    } else {
        console.log("la librairie google s'est mal chargée");
    }
}

function checkAuthGoogle() {
    gapi.auth.authorize({client_id: clientId, scope: scopes, immediate: true}, handleAuthGoogleResult);
}

function handleAuthGoogleResult(authResult) {
    var $googleButton = $("#connectGoogleButton");
    if (authResult && !authResult.error) {
        console.log("google : auth ok");
        isConnectedToGoogle = true;

        $googleButton.addClass("disabled");
        $("#inviteGoogleContactsButton").removeClass("disabled");

        var accessTokenInfos = gapi.auth.getToken();
        accessTokenGoogle = accessTokenInfos.access_token;

        loadFacebookFriendsAndGoogleContacts();
    } else {
        console.log(authResult);
        console.log("google : auth not ok");

        $googleButton.removeClass("disabled");
        $("#inviteGoogleContactsButton").addClass("disabled");
        $googleButton.click(handleAuthGoogleClick);
    }
}

function handleAuthGoogleClick(event) {
    if( !$("#connectGoogleButton").hasClass("disabled") ) {
        gapi.auth.authorize({client_id: clientId, scope: scopes, immediate: false}, handleAuthGoogleResult);
        return false;
    }
}

var loadFbFriends = function() {    
    if( isConnectedToFB ) {
        $("#contactsOnKsLoader").show();
        $("#contactsNotOnKsLoader").show();
        $("#contactsOnKsContainer").html("");
        $("#contactsNotOnKsContainer").html("");

        var ajaxRequest = $.get(
            Routing.generate('ksFriends_loadFbFriends'), 
            {},
            function(response) {
                finalizeContactsOnKsLoad(response.contactsOnKsHtml);
            }
        );

        abortOtherAjaxRequests(ajaxRequest);
    }
};

var loadGoogleContacts = function() { 
    if( ! $("#inviteGoogleContactsButton").hasClass("disabled") ) {
        $("#contactsNotOnKsLoader").show();
        $("#contactsNotOnKsContainer").html("");
        $("#sendInvitationsButton").hide();

        params = {};           
        params.isConnectedToGoogle = isConnectedToGoogle;
        params.accessTokenGoogle = accessTokenGoogle;
        //console.log(accessTokenGoogle);
        var ajaxRequest = $.post(
            Routing.generate('ksFriends_loadGoogleContacts'), 
            params,
            function(response) {
                finalizeContactsNotOnKsLoad(response.contactsNotOnKsHtml);
            }
        );

        abortOtherAjaxRequests(ajaxRequest);
    }
};

var loadFacebookFriendsAndGoogleContacts = function() {
    params = {};

    params.isConnectedToFB = isConnectedToFB;
    params.isConnectedToGoogle = isConnectedToGoogle;

    params.accessTokenFacebook = accessTokenFacebook;
    params.accessTokenGoogle = accessTokenGoogle;

    $("#contactsOnKsLoader").show();
    //$("#contactsNotOnKsLoader").show();
    $("#contactsOnKsContainer").html("");
    //$("#contactsNotOnKsContainer").html("");
    $("#sendFriendRequestButton").hide();
    var ajaxRequest = $.post(
        Routing.generate('ksFriends_loadFbAnbGoogleContacts'), 
        params,
        function(response) {
            finalizeContactsOnKsLoad(response.contactsOnKsHtml);
            //finalizeContactsNotOnKsLoad(response.contactsNotOnKsHtml);
        }
    );

    abortOtherAjaxRequests(ajaxRequest);
};

$(document).ready(function() {

    
    /*FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            isConnectedToFB = true;
            //console.log('FB: auth ok');
            $("#connectFacebookButton").addClass("disabled");
            $("#inviteFacebookFriendsButton").removeClass("disabled");

            //accessTokenFacebook = response.authResponse.accessToken;
            loadFacebookFriendsAndGoogleContacts();
        } else if (response.status === 'not_authorized') {            
            console.log('FB: not auth');
        } else {
            console.log('FB: not logged in');
        }
    });*/
    
    
    //handleClientLoad();

    $("#inviteFacebookFriendsButton").click(sendRequestViaMultiFriendSelector);
    $("#inviteGoogleContactsButton").click(loadGoogleContacts);

    //Au clic sur le bouton "Demande de mise en relation", on récupère les id et on envoi des demande d'ami
    $("#sendFriendRequestButton").click(function() {
        if( ! $( this ).hasClass("disabled") ) {
            //Récupération des ids Ks
            var userKsIds = new Array();
            $.each($("#contactsOnKsContainer").find("table").find("tbody").find("tr"), function(key, tr) {
                var $checkbox = $( tr ).find("td").first().find("input[type=checkbox]");

                if( $checkbox.is(':checked') ) {
                    userKsIds.push( $( tr ).attr("ksId") );
                }
            });

            if( userKsIds.length > 0 ) {
                sendFriendRequests(userKsIds);
            } else {
                showInformation("Sélectionnes au moins un utilisateur");
            }
        }

        //showInformation( nbSendedRequests );
    });

    //Au clic sur le bouton "Envoyer une invitation", on récupère les adresses mails et on envoi des emails
    $("#sendInvitationsButton").click(function() {
        console.log("click on sendInvitationsButton");
        if( ! $( this ).hasClass("disabled") ) {
            //Récupération des adresses et id fb
            var emailAdresses = new Array();
            var userFbIds = new Array();
            $.each($("#contactsNotOnKsContainer").find("table").find("tbody").find("tr"), function(key, tr) {
                var $checkbox = $( tr ).find("td").first().find("input[type=checkbox]");

                if( $checkbox.is(':checked') ) {
                    if( $( tr ).attr("fbId") !== undefined )
                        userFbIds.push( $( tr ).attr("fbId") );

                    if( $( tr ).attr("email") !== undefined )
                        emailAdresses.push( $( tr ).attr("email") );
                }
            });


            if( emailAdresses.length > 0 ) {
                sendMailInvitations(emailAdresses);
            }

            console.log(userFbIds);
            //sendMailInvitations(emailAdresses);
            if ( userFbIds.length > 0 && userFbIds.length <= 50 ) {
                sendRequestToRecipients( userFbIds );
            } else if( userFbIds.length == 0 ){
                showInformation("Sélectionne au moins un de tes contacts");
            } else {    
                showInformation("Tu ne peux pas inviter plus de 50 utilisateurs à la fois");
            }
        }
    });

    $("#connectFacebookButton").click(function() {
        if( ! $( this ).hasClass("disabled") ) {
            if(! isConnectedToFB ) {
                FB.login(function(response) {
                    if (response.status === 'connected') {
                        isConnectedToFB = true;
                        console.log('FB: auth ok');
                        $("#connectFacebookButton").addClass("disabled");
                        $("#inviteFacebookFriendsButton").removeClass("disabled");

                        //accessTokenFacebook = response.authResponse.accessToken
                        loadFacebookFriendsAndGoogleContacts();
                    } else if (response.status === 'not_authorized') {            
                        console.log('FB: not auth');
                    } else {
                        console.log('FB: not logged in');
                    }
                }, {scope: 'publish_actions,user_photos'});
            } else {
                $("#connectFacebookButton").addClass("disabled");
                $("#inviteFacebookFriendsButton").removeClass("disabled");
                loadFacebookFriendsAndGoogleContacts();
            }
        }
    });
});