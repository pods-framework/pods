<?php
// Get all helpers
$result = pod_query("SELECT id, name, helper_type FROM @wp_pod_helpers ORDER BY name");
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
    jQuery(".navTab[rel="+thetab+"Area]").click();
});

function addPodHelper(div_id, select_id) {
    var val = jQuery("#"+select_id).val();
    var html = '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
    jQuery("#"+div_id).append(html);
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
if (pods_access('manage_templates'))
{
?>
    <div class="navTab" rel="templateArea"><a href="#template">Templates</a></div>
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
    <div id="logo">
        <div id="version">
            <?php echo implode('.', str_split($pods_latest)); ?>
        </div>
    </div>
    <div id="overview">
        <h3>This server is running...</h3>
        &nbsp; &#149; &nbsp; PHP <?php echo phpversion(); ?><br />
        &nbsp; &#149; &nbsp; <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br />
        &nbsp; &#149; &nbsp; MySQL <?php echo mysql_result(pod_query("SELECT VERSION()"), 0); ?>
    </div>
    <div class="clear"><!--clear--></div>
</div>

<?php
$pods_manage_dir = WP_PLUGIN_DIR . '/pods/core/';

if (pods_access('manage_pods'))
{
    include $pods_manage_dir . 'manage_pods.php';
}
if (pods_access('manage_templates'))
{
    include $pods_manage_dir . 'manage_templates.php';
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

