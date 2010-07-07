<!-- Begin settings area -->

<script type="text/javascript">
function resetDB() {
    if (confirm("This will completely remove Pods from the database. Are you sure?")) {
        if (confirm("Did you already make a database backup?")) {
            if (confirm("There's no undo. Is that your final answer?")) {
                jQuery.ajax({
                    type: "post",
                    url: "<?php echo PODS_URL; ?>/uninstall.php",
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
</script>

<!-- Settings HTML -->

<h3>Debug Information</h3>
<ul>
    <li>PHP <?php echo phpversion(); ?></li>
    <li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
    <li>MySQL <?php echo mysql_result(pod_query("SELECT VERSION()"), 0); ?></li>
    <li><?php echo $_SERVER['HTTP_USER_AGENT']; ?></li>
    <li>WordPress <?php global $wp_version; echo $wp_version; ?></li>
<?php
$all_plugins = get_plugins();
foreach ($all_plugins as $plugin_file => $plugin_data) {
    if (is_plugin_active($plugin_file)) {
?>
    <li><?php echo $plugin_data['Name'] . ' ' . $plugin_data['Version']; ?></li>
<?php
    }
}
?>
</ul>

<h3>Reset Pods</h3>
<div class="tips">There is no undo. Please backup your database before proceeding!</div>
<input type="button" class="button" onclick="resetDB()" value="Reset everything" />
