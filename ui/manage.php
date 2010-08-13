<?php
// Get all helpers
$result = pod_query("SELECT id, name, helper_type FROM @wp_pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
    $helpers[$row['id']] = $row;
    $helper_types[$row['helper_type']][] = $row['name'];
}

// Get all datatypes
$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
    $datatypes[$row['id']] = $row['name'];
}
?>

<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/ui/style.css" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/ui/js/jqmodal.js"></script>
<script type="text/javascript">
var api_url = "<?php echo PODS_URL; ?>/ui/ajax/api.php";
var datatype;
var column_id;
var add_or_edit;
var helper_id;
var page_id;

jQuery(function() {
    jQuery(".navTab:first").click();

    // Remember the tab selection
    var thetab = window.location.href.split("#")[1];
    if ("undefined" != typeof(thetab)) {
        jQuery(".navTab[rel="+thetab+"Area]").click();
    }
});

function addPodHelper(div_id, select_id) {
    var val = jQuery("#"+select_id).val();
    if ("" != val) {
        var html = '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
        jQuery("#"+div_id).append(html);
    }
}
</script>

<div class="wrap pods_admin">
    <div id="icon-edit-pages" class="icon32"><br /></div>
    <h2>Pods Setup</h2>
    <div id="nav">
<?php
if (pods_access('manage_pods')) {
?>
        <div class="navTab" rel="podArea"><a href="#pod">Pods</a></div>
<?php
}
if (pods_access('manage_templates')) {
?>
        <div class="navTab" rel="templateArea"><a href="#template">Templates</a></div>
<?php
}
if (pods_access('manage_pages')) {
?>
        <div class="navTab" rel="pageArea"><a href="#page">Pages</a></div>
<?php
}
if (pods_access('manage_helpers')) {
?>
        <div class="navTab" rel="helperArea"><a href="#helper">Helpers</a></div>
<?php
}
if (pods_access('manage_roles')) {
?>
        <div class="navTab" rel="roleArea"><a href="#role">Roles</a></div>
<?php
}
if (pods_access('manage_settings')) {
?>
        <div class="navTab" rel="settingsArea"><a href="#settings">Settings</a></div>
<?php
}
?>
        <div class="clear"><!--clear--></div>
    </div>

    <div id="podArea" class="area">
<?php
if (pods_access('manage_pods') && apply_filters('pods_manage_pods',true)) {
    include(PODS_DIR . '/ui/manage_pods.php');
}
?>
    </div>

    <div id="templateArea" class="area">
<?php
if (pods_access('manage_templates') && apply_filters('pods_manage_templates',true)) {
    include(PODS_DIR . '/ui/manage_templates.php');
}
?>
    </div>

    <div id="pageArea" class="area">
<?php
if (pods_access('manage_pod_pages') && apply_filters('pods_manage_pod_pages',true)) {
    include(PODS_DIR . '/ui/manage_pages.php');
}
?>
    </div>

    <div id="helperArea" class="area">
<?php
if (pods_access('manage_helpers') && apply_filters('pods_manage_helpers',true)) {
    include(PODS_DIR . '/ui/manage_helpers.php');
}
?>
    </div>

    <div id="roleArea" class="area">
<?php
if (pods_access('manage_roles') && apply_filters('pods_manage_roles',true)) {
    include(PODS_DIR . '/ui/manage_roles.php');
}
?>
    </div>

    <div id="settingsArea" class="area">
<?php
if (pods_access('manage_settings') && apply_filters('pods_manage_settings',true)) {
    include(PODS_DIR . '/ui/manage_settings.php');
}
?>
    </div>
</div>
