<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/ui/style.css" />
<script type="text/javascript">
var api_url = "<?php echo PODS_URL; ?>/ui/ajax/api.php";

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });
});

function podsExport() {
    var data = new Array();

    var i = 0;
    jQuery(".form").each(function() {
        var theval = "";
        var classname = jQuery(this).attr("class").split(" ");
        jQuery("." + classname[2] + " .active").each(function() {
            theval += jQuery(this).attr("value") + ",";
        });
        theval = theval.substr(0, theval.length - 1);
        data[i] = classname[2] + "=" + encodeURIComponent(theval);
        i++;
    });

    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=export_package&"+data.join("&"),
        success: function(msg) {
            if (!is_error(msg)) {
                jQuery("#export_code").html(msg);
            }
        }
    });
}

function podsImport(action) {
    var action = (false == action) ? 'validate_package' : 'import_package';
    var data = ('validate_package' == action) ? '&data='+encodeURIComponent(jQuery("#import_code").val()) : '';
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action="+action+data,
        success: function(msg) {
            if (!is_error(msg)) {
                jQuery("#import_finalize").html(msg);
            }
        }
    });
}
</script>

<div class="wrap pods_admin">
    <h2>Package Manager</h2>

    <div id="nav">
        <div class="navTab active" rel="exportArea"><a href="#export">Export</a></div>
        <div class="navTab" rel="importArea"><a href="#import">Import</a></div>
        <div class="clear"><!--clear--></div>
    </div>

    <div id="exportArea" class="area active pods_form">
        <table style="width:100%">
            <tr>
                <td>
                    <h3>Pods</h3>
                    <div class="form pick pod" style="height:100px; margin-bottom:15px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
                    </div>
                </td>
                <td style="width:20px"></td>
                <td>
                    <h3>Templates</h3>
                    <div class="form pick template" style="height:100px; margin-bottom:15px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_templates ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Pod Pages</h3>
                    <div class="form pick podpage" style="height:100px">
<?php
$result = pod_query("SELECT id, uri FROM @wp_pod_pages ORDER BY uri");
while ($row = mysql_fetch_assoc($result)) {
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['uri']; ?></div>
<?php
}
?>
                    </div>
                </td>
                <td style="width:20px"></td>
                <td>
                    <h3>Helpers</h3>
                    <div class="form pick helper" style="height:100px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result)) {
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
                    </div>
                </td>
            </tr>
        </table>

        <p><input type="button" class="button-primary" onclick="podsExport()" value="Export!" /></p>
        <p><textarea id="export_code">The export code will appear here.</textarea></p>
    </div>

    <div id="importArea" class="area hidden">
        <p>Paste the package code into the form. You will be taken to a confirmation screen.</p>
        <p><textarea id="import_code"></textarea></p>
        <input type="button" class="button-primary" onclick="podsImport(false)" value="Proceed to Confirmation" />
        <div id="import_finalize"></div>
    </div>
</div>
