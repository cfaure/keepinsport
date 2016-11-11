function hidePopover(elem) {
    $(elem).popover('hide');
}

function initializePopover(elem, timer) {
    elem.popover({
        placement: 'top',
        offset: 15,
        trigger: 'manual',
        delay: { show: 350, hide: 100 },
        html: true
    });

    elem.hover(
        function() {
            var self = this;
            clearTimeout(timer);
            $('.popover').hide();
            popover_parent = self
            $(self).popover('show');            
        }, 
        function() {
            var self = this;
            timer = setTimeout(function(){hidePopover(self)},300);    
        }
    );
}

function initializePopovers(bloc) {
    var timer, popover_parent;
    
    $.each( bloc.find(".userAvatarBloc .imageLink"), function(key, value) {
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
                $('.popover').hide();
                popover_parent = self
                $(self).popover('show');            
            }, 
            function() {
                var self = this;
                timer = setTimeout(function(){hidePopover(self)},300);    
            }
        );

        $( this ).hover(
            function() {
                var self = this;
                clearTimeout(timer);
                $('.popover').hide(); //Hide any open popovers on other elements.
                popover_parent = self
                $(self).popover('show');            
            }, 
            function() {
                var self = this;
                timer = setTimeout(function(){hidePopover(self)},300);    
            }
        );
    });
    
    $('.popover').live({
        mouseover: function() {
            clearTimeout(timer);
        },
        mouseleave: function() {
            var self = this;
            timer = setTimeout(function(){hidePopover(popover_parent)},300); 
        }
    });
}
    
$(document).ready(function() {

    //initializePopovers( $('body'))
    
     var timer, popover_parent;
     
     $.each( $(".userAvatarBloc .imageLink[rel=bubble]"), function(key, value) {
        $( this ).popover({
            placement: $( this ).data("placement") != "" ? $( this ).data("placement") : "down",
            offset: 15,
            trigger: 'manual',
            delay: { show: 350, hide: 100 },
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
                timer = setTimeout(function(){hidePopover(self)},300);    
            }
        );
    });
    
    $('.popover').live({
        mouseover: function() {
            clearTimeout(timer);
        },
        mouseleave: function() {
            var self = this;
            timer = setTimeout(function(){hidePopover(popover_parent)},300); 
        }
    });
});