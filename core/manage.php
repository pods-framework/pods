<?php
// Get all helpers
$result = pod_query("SELECT id, name, helper_type FROM @wp_pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $helpers[$row['id']] = $row;
    $helper_types[$row['helper_type']][] = $row['name'];
}

// Get all datatypes
$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}
?>

<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqmodal.js"></script>
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
    if ("" != val) {
        var html = '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
        jQuery("#"+div_id).append(html);
    }
}

function changelog() {
    jQuery.ajax({
        type: "get",
        url: "<?php echo PODS_URL; ?>/ajax/changelog.php",
        data: "v=<?php echo PODS_VERSION; ?>&ref=<?php echo $_SERVER['HTTP_HOST']; ?>",
        success: function(msg) {
            jQuery("#changelog").html(msg);
        }
    });
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
    <div class="navTab" rel="pageArea"><a href="#page">Pages</a></div>
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
    <div class="stickynote">
        <div id="logo">
            <div id="version"><?php echo implode('.', str_split(PODS_VERSION)); ?></div>
        </div>
        <div id="info">
            <h3>Your server is running...</h3>
            <ul>
                <li>PHP <?php echo phpversion(); ?></li>
                <li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                <li>MySQL <?php echo mysql_result(pod_query("SELECT VERSION()"), 0); ?></li>
            </ul>
            <h3>What's new in this version?</h3>
            <div  id="changelog">
                <input type="button" class="button" value="Get changelog" onclick="changelog()" />
            </div>
        </div>
    </div>
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
