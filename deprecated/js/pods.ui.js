function is_error(msg) {
    if ("<e>" == msg.substr(0, 3)) {
        alert(msg.substr(3));
        return true;
    }
    return false;
}

jQuery(function() {
    /* Navigation tabs */
    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        jQuery(".area").hide();
        jQuery("#" + jQuery(this).attr("rel")).show();
    });

    var active_tab = window.location.href.split("#")[1];
    if ("undefined" != typeof active_tab) {
        jQuery(".navTab[rel=" + active_tab + "Area]").click();
    }    
});