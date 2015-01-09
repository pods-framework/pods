// change the menu position based on the scroll positon
window.onscroll = function () {
    if ( window.XMLHttpRequest ) {
        if ( document.documentElement.scrollTop > 33 || self.pageYOffset > 33 ) {
            jQuery( '.pods_floatmenu' ).css( 'position', 'fixed' );
            jQuery( '.pods_floatmenu' ).css( 'top', '52px' );
            jQuery( '.pods_floatmenu' ).css( 'right', '315px' );
        }
        else if ( document.documentElement.scrollTop < 33 || self.pageYOffset < 33 ) {
            jQuery( '.pods_floatmenu' ).css( 'position', 'relative' );
            jQuery( '.pods_floatmenu' ).css( 'top', 'auto' );
            jQuery( '.pods_floatmenu' ).css( 'right', 'auto' );
            //jQuery('.pods_floatmenu').css('top','112px');
            //jQuery('.pods_floatmenu').css('right','15px');
        }
    }
}