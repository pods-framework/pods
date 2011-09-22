// change the menu position based on the scroll positon
window.onscroll = function()
{
    if( window.XMLHttpRequest ) {
        if (document.documentElement.scrollTop > 112 || self.pageYOffset > 112) {
            jQuery('.pods_floatmenu').css('position','fixed');
            jQuery('.pods_floatmenu').css('top','52px');
            jQuery('.pods_floatmenu').css('right','15px');
        } else if (document.documentElement.scrollTop < 112 || self.pageYOffset < 112) {
            jQuery('.pods_floatmenu').css('position','absolute');
            jQuery('.pods_floatmenu').css('top','112px');
            jQuery('.pods_floatmenu').css('right','15px');
        }
    }
}