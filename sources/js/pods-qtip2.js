jQuery(document).ready(function($){
    $('.pods-qtip').qtip({
        content: {
            attr: 'alt'
        },
        style: {
            classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
        },
        show: {
            effect: function(offset) {
                $(this).fadeIn('fast');
            }
        },
        hide: {
            fixed: true,
            delay: 300
        },
        position: {
            my: 'bottom left',
            adjust: {
                y: -14
            }
        }
    });
});