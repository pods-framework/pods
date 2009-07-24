<!--
==================================================
Begin settings area
==================================================
-->
<script type="text/javascript">
function resetDB() {
    if (confirm("This will completely remove Pods from the database. Are you sure?")) {
        if (confirm("Did you already make a database backup?")) {
            if (confirm("There's no undo. Is that your final answer?")) {
                jQuery.ajax({
                    type: "post",
                    url: "<?php echo PODS_URL; ?>/uninstall.php",
                    data: "auth="+auth,
                    success: function(msg) {
                        if ("Error" == msg.substr(0, 5)) {
                            alert(msg);
                        }
                        else {
                            window.location="";
                        }
                    }
                });
            }
        }
    }
}
</script>

<!--
==================================================
Settings HTML
==================================================
-->
<div id="settingsArea" class="area hidden">
    <div class="tips">*WARNING* This cannot be undone. Please backup your database!</div>
    <input type="button" class="button" onclick="resetDB()" value="Reset Pods data" />
</div>

