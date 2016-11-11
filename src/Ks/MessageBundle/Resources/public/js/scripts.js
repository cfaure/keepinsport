function attachDeleteMessageEvent(messageId) {
    
    var callback = function() {
        document.location.href = Routing.generate('ksMessage_delete', { "id" : messageId });
    };
    
    var message = "Tu es sur le point de supprimer ce message (action irréversible)<br/>Es-tu sûr de vouloir continuer ?";

    askConfirmation(message, 'sportif', callback, null);
    return false;
}