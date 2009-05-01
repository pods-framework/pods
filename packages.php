<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript">
var auth = '<?php echo md5(AUTH_KEY); ?>';

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
        url: "<?php echo $pods_url; ?>/ajax/import_export.php",
        data: "action=export&auth="+auth+"&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#export_code").html(msg);
            }
        }
    });
}

function podsImport(action) {
    var action = (false == action) ? 'import' : 'finalize';
    var data = jQuery("#import_code").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/import_export.php",
        data: "action="+action+"&auth="+auth+"&data="+encodeURIComponent(data),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#import_finalize").html(msg);
            }
        }
    });
}
</script>

<h2>Export Package</h2>

<p>Select which items to include in your new package.</p>

<div class="form pick pod" style="width:250px; height:100px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_types");
while ($row = mysql_fetch_assoc($result))
{
?>
    <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
</div>

<div class="form pick podpage" style="width:250px; height:100px">
<?php
$result = pod_query("SELECT id, uri FROM @wp_pod_pages");
while ($row = mysql_fetch_assoc($result))
{
?>
    <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['uri']; ?></div>
<?php
}
?>
</div>

<div class="form pick helper" style="width:250px; height:100px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_helpers");
while ($row = mysql_fetch_assoc($result))
{
?>
    <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
</div>

<p>
    <input type="button" class="button" onclick="podsExport()" value="Export!" /> (once you hit this button, your export code will appear below)
</p>

<p>
    <textarea id="export_code" style="width:500px; height:100px; border:2px dashed #000"></textarea>
</p>

<h2>Import Package</h2>

<p>Paste the package code into the form. You will be taken to a confirmation screen.</p>

<p>
    <textarea id="import_code" style="width:500px; height:100px; border:2px dashed #000"></textarea>
</p>

<input type="button" class="button" onclick="podsImport(false)" value="Proceed to Import Confirmation" />

<div id="import_finalize"></div>

