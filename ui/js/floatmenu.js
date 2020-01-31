// change the menu position based on the scroll positon

jQuery(document).ready(function($) {
    $( '.pods_floatmenu' ).each(function() {
        var floatmenu = $( this );
        var margin = 20;
        var offset = floatmenu.offset();
        var top = margin;
        if ( $('html').hasClass( 'wp-toolbar' ) ) {
            offset.top -= parseInt( $('html').css('padding-top') );
            top += parseInt( $('html').css('padding-top') );
        }
        offset.left -= margin;
        offset.top -= margin;
        var right = $(window).width() - offset.left;
        window.onscroll = function () {
            if ( window.XMLHttpRequest ) {
                // Make sure the window height is larger than the floatmenu height
                if ( $(window).height() < ( floatmenu.height() + ( 2 * margin ) ) ) {
                    return;
                }
                if ( document.documentElement.scrollTop > offset.top || self.pageYOffset > offset.top ) {
                    floatmenu.css( {
                        'position': 'fixed',
                        'top': top + 'px',
                        'right': right + 'px'
                    } );
                }
                else if ( document.documentElement.scrollTop < offset.top || self.pageYOffset < offset.top ) {
                    floatmenu.css( {
                        'position': 'relative',
                        'top': 'auto',
                        'right': 'auto'
                    } );
                }
            }
        };
    });
});
