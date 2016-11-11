(function($) {
    $.fn.ksUserBubble = function()
    {
        this.each(function(content) {
            
            var $userBubble = $(this).wrap('<span class="userBubble"><span class="bubble"></span></span>');
            $userBubble.append('<span class="bubble">' + content +'</span>')
        });
        return this;
    };
})(jQuery);