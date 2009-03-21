<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
var auth = '<?php echo md5(AUTH_KEY); ?>';

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });

    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        var activeArea = jQuery(this).attr("rel");
        jQuery(".area").hide();
        jQuery("#"+activeArea).show();
    });

    // Remember the tab selection
    var thetab = window.location.href.split("#")[1];
    thetab = ("undefined" == typeof(thetab)) ? "overview" : thetab;
    jQuery(".navTab[@rel="+thetab+"Area]").click();
});

function colorFade(area, id) {
    var bgcolor = jQuery("#"+area+"Area #row"+id).css("background-color");
    jQuery("#"+area+"Area #row"+id).css("background-color", "#88FFC0");
    jQuery("#"+area+"Area #row"+id).animate({backgroundColor:bgcolor}, 1000);
}
</script>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
<?php
if (pods_access('manage_packages'))
{
?>
    <div class="navTab" rel="overviewArea"><a href="#overview">Overview</a></div>
<?php
}
?>
    <div class="clear"><!--clear--></div>
</div>

Package manager coming soon!

