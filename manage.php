<?php
// Get all helpers
$result = pod_query("SELECT id, name, helper_type FROM {$table_prefix}pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $helpers[$row['id']] = $row;
    $helper_types[$row['helper_type']][] = $row['name'];
}
?>

<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
var auth = '<?php echo md5(AUTH_KEY); ?>';
var datatype;
var column_id;
var add_or_edit;
var helper_id;
var page_id;

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
    thetab = ("undefined" == typeof(thetab)) ? "welcome" : thetab;
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
    <div class="navTab active" rel="welcomeArea"><a href="#welcome">Welcome</a></div>
<?php
if (pods_access('manage_pods'))
{
?>
    <div class="navTab" rel="podArea"><a href="#pod">Pods</a></div>
<?php
}
if (pods_access('manage_podpages'))
{
?>
    <div class="navTab" rel="pageArea"><a href="#page">Pod Pages</a></div>
<?php
}
if (pods_access('manage_helpers'))
{
?>
    <div class="navTab" rel="helperArea"><a href="#helper">Helpers</a></div>
<?php
}
if (pods_access('manage_roles'))
{
?>
    <div class="navTab" rel="roleArea"><a href="#role">Roles</a></div>
<?php
}
if (pods_access('manage_settings'))
{
?>
    <div class="navTab" rel="settingsArea"><a href="#settings">Settings</a></div>
<?php
}
?>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin welcome area
==================================================
-->
<div id="welcomeArea" class="area hidden">
    <h2 align="center">Thanks for using the Pods CMS plugin.</h2>
    <p align="center">See the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> and <a href="http://pods.uproot.us/forum" target="_blank">Forum</a> to get started.</p>
</div>

<?php
$pods_manage_dir = WP_PLUGIN_DIR . '/pods/manage/';

if (pods_access('manage_pods'))
{
    include $pods_manage_dir . 'manage_pods.php';
}
if (pods_access('manage_podpages'))
{
    include $pods_manage_dir . 'manage_pages.php';
}
if (pods_access('manage_helpers'))
{
    include $pods_manage_dir . 'manage_helpers.php';
}
if (pods_access('manage_roles'))
{
    include $pods_manage_dir . 'manage_roles.php';
}
if (pods_access('manage_settings'))
{
    include $pods_manage_dir . 'manage_settings.php';
}

