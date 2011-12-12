<!-- Begin settings area -->
<script type="text/javascript">
function pods_resetDB() {
    if (confirm("This will completely remove Pods from the database. Are you sure?")) {
        if (confirm("Did you already make a database backup?")) {
            if (confirm("There's no undo. Is that your final answer?")) {
                jQuery.ajax({
                    type: "post",
                    url: "<?php echo PODS_URL; ?>/uninstall.php",
                    data: "_wpnonce=<?php echo wp_create_nonce('pods-uninstall'); ?>",
                    success: function(msg) {
                        if (!is_error(msg)) {
                            window.location="";
                        }
                    }
                });
            }
        }
    }
}
function pods_fixDB() {
    if (confirm("This will completely resync your wp_pod and wp_pod_tbl_* data in the database. Are you sure?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ui/ajax/api.php",
            data: "action=fix_wp_pod&_wpnonce=<?php echo wp_create_nonce('pods-fix_wp_pod'); ?>",
            success: function(msg) {
                if ("admin.php?page=pods&wp_pod_fixed=1#settings" == window.location)
                    window.location = "";
                else
                    window.location = "admin.php?page=pods&wp_pod_fixed=1#settings";
            }
        });
    }
}
function pods_security_settings() {
    var data = new Array();
    var i = 0;
    jQuery('#pods_security_settings_group .pods-security-setting').each(function() {
        if ('checkbox' != jQuery(this).attr('type') || jQuery(this).is(':checked')) {
            data[i] = jQuery(this).attr('id') + "=" + encodeURIComponent(jQuery(this).val());
        }
        i++;
    });
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ui/ajax/api.php",
        data: "action=security_settings&_wpnonce=<?php echo wp_create_nonce('pods-security_settings'); ?>&"+data.join("&"),
        success: function(msg) {
            if ("admin.php?page=pods&security_settings_updated=1#settings" == window.location)
                window.location = "";
            else
                window.location = "admin.php?page=pods&security_settings_updated=1#settings";
        }
    });
}
function pods_page_settings() {
    var data = new Array();
    var i = 0;
    jQuery('#pods_page_settings_group .pods-page-setting').each(function() {
        if ('checkbox' != jQuery(this).attr('type') || jQuery(this).is(':checked')) {
            data[i] = jQuery(this).attr('id') + "=" + encodeURIComponent(jQuery(this).val());
        }
        i++;
    });
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ui/ajax/api.php",
        data: "action=pod_page_settings&_wpnonce=<?php echo wp_create_nonce('pods-pod_page_settings'); ?>&"+data.join("&"),
        success: function(msg) {
            if ("admin.php?page=pods&page_settings_updated=1#settings" == window.location)
                window.location = "";
            else
                window.location = "admin.php?page=pods&page_settings_updated=1#settings";
        }
    });
}
</script>

<!-- Settings HTML -->

<h3 style="padding-top:15px" id="pods_security_settings">Security Settings</h3>
<?php
if (isset($_GET['security_settings_updated']) && 1 == $_GET['security_settings_updated']) {
?>
        <div id="message" class="updated fade"><p>Security Settings Updated</p></div>
<?php
}
?>
<div class="tips">Restrict access to uploads and browsing existing uploads</div>
<div id="pods_security_settings_group">
    <p><label for="disable_file_browser"><input type="checkbox" name="disable_file_browser" id="disable_file_browser" class="pods-security-setting" value="1"<?php echo (defined('PODS_DISABLE_FILE_BROWSER') && false !== PODS_DISABLE_FILE_BROWSER) ? ' CHECKED' : ''; ?> /> Disable File Browser Completely</label></p>
    <p><label for="files_require_login"><input type="checkbox" name="files_require_login" id="files_require_login" class="pods-security-setting" value="1"<?php echo (defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && false !== PODS_FILES_REQUIRE_LOGIN) ? ' CHECKED' : ''; ?> /> Require user to be logged in to use File Browser</label></p>
    <p><label for="files_require_login_cap"><input type="text" name="files_require_login_cap" id="files_require_login_cap" class="pods-security-setting" value="<?php echo (defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && 0 < strlen(PODS_FILES_REQUIRE_LOGIN)) ? PODS_FILES_REQUIRE_LOGIN : ''; ?>" /> Require role or capability to use File Browser</label></p>
    <p><label for="disable_file_upload"><input type="checkbox" name="disable_file_upload" id="disable_file_upload" class="pods-security-setting" value="1"<?php echo (defined('PODS_DISABLE_FILE_UPLOAD') && false !== PODS_DISABLE_FILE_UPLOAD) ? ' CHECKED' : ''; ?> /> Disable File Uploader Completely</label></p>
    <p><label for="upload_require_login"><input type="checkbox" name="upload_require_login" id="upload_require_login" class="pods-security-setting" value="1"<?php echo (defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && false !== PODS_UPLOAD_REQUIRE_LOGIN) ? ' CHECKED' : ''; ?> /> Require user to be logged in to use File Uploader</label></p>
    <p><label for="upload_require_login_cap"><input type="text" name="upload_require_login_cap" id="upload_require_login_cap" class="pods-security-setting" value="<?php echo (defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && 0 < strlen(PODS_UPLOAD_REQUIRE_LOGIN)) ? PODS_UPLOAD_REQUIRE_LOGIN : ''; ?>" /> Require role or capability to use File Uploader</label></p>
    <input type="button" class="button-primary" onclick="pods_security_settings()" value=" Save Security Settings " />
</div>

<hr />

<h3 style="padding-top:15px" id="pods_page_settings">Pod Page Settings</h3>
<?php
if (isset($_GET['page_settings_updated']) && 1 == $_GET['page_settings_updated']) {
?>
        <div id="message" class="updated fade"><p>Pod Page Settings Updated</p></div>
<?php
}
?>
<div class="tips">Additional tweaks to Pod Pages</div>
<div id="pods_page_settings_group">
    <p><label for="pods_page_precode_timing"><input type="checkbox" name="pods_page_precode_timing" id="pods_page_precode_timing" class="pods-page-setting" value="1"<?php echo (defined('PODS_PAGE_PRECODE_TIMING') && false !== PODS_PAGE_PRECODE_TIMING) ? ' CHECKED' : ''; ?> /> Run Pod Page Precode after Theme functions.php runs</label></p>
    <input type="button" class="button-primary" onclick="pods_page_settings()" value=" Save Pod Page Settings " />
</div>

<hr />
<h3 style="padding-top:15px" id="pods_fix_wp_pod">Fix wp_pod table</h3>
<?php
if (isset($_GET['wp_pod_fixed']) && 1 == $_GET['wp_pod_fixed']) {
?>
        <div id="message" class="updated fade"><p>wp_pod / wp_pod_tbl_* Data Fixed</p></div>
<?php
}
?>
<div class="tips">Reset wp_pod_tbl_podname and wp_pod records to match if they've somehow gotten out of sync. There is no undo!</div>
<input type="button" class="button-primary" style="background:#f39400;border-color:#d56500;" onclick="pods_fixDB()" value=" Fix wp_pod table " />

<hr />
<h3 style="padding-top:15px">Reset Pods</h3>
<div class="tips">Remove all settings and delete all Pod data. There is no undo!</div>
<input type="button" class="button-primary" style="background:#f39400;border-color:#d56500;" onclick="pods_resetDB()" value=" Reset Pods " />

<hr />
<h3 style="padding-top:15px">Debug Information</h3>
<textarea>
WordPress <?php global $wp_version; echo $wp_version; ?>

PHP Version: <?php echo phpversion(); ?>

MySQL Version: <?php echo mysql_result(pod_query("SELECT VERSION()"), 0); ?>

Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?>

Your User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?>


-- Currently Active Plugins --
<?php
$all_plugins = get_plugins();
foreach ($all_plugins as $plugin_file => $plugin_data) {
    if (is_plugin_active($plugin_file)) {
        echo $plugin_data['Name'] . ' ' . $plugin_data['Version'] . " \n";
    }
}
?>
</textarea>