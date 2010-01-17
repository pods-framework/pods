<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css?r=<?php echo rand(1000, 9999); ?>" />
<script type="text/javascript">
jQuery(function() {
    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        var activeArea = jQuery(this).attr("rel");
        jQuery(".area").hide();
        jQuery("#"+activeArea).show();
    });

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
        url: "<?php echo PODS_URL; ?>/ajax/package.php",
        data: "action=export&"+data.join("&"),
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
        url: "<?php echo PODS_URL; ?>/ajax/package.php",
        data: "action="+action+"&data="+encodeURIComponent(data),
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

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
    <div class="navTab active" rel="exportArea"><a href="javascript:;">Export</a></div>
    <div class="navTab" rel="importArea"><a href="javascript:;">Import</a></div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="exportArea" class="area">
    <h2 class="title">Export Package</h2>
    <p>Select which items to include in your new package.</p>

    <div style="float:left; width:45%; margin-right:20px">
        <h3>Pods</h3>
        <div class="form pick pod" style="height:100px; margin-bottom:10px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
        </div>
    </div>

    <div style="float:left; width:45%; margin-right:20px">
        <h3>Templates</h3>
        <div class="form pick template" style="height:100px; margin-bottom:10px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_templates ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
        </div>
    </div>

    <div style="float:left; width:45%; margin-right:20px">
        <h3>Pod Pages</h3>
        <div class="form pick podpage" style="height:100px; margin-bottom:10px">
<?php
$result = pod_query("SELECT id, uri FROM @wp_pod_pages ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['uri']; ?></div>
<?php
}
?>
        </div>
    </div>

    <div style="float:left; width:45%; margin-right:20px">
        <h3>Helpers</h3>
        <div class="form pick helper" style="height:100px">
<?php
$result = pod_query("SELECT id, name FROM @wp_pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
?>
            <div class="option" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></div>
<?php
}
?>
        </div>
    </div>

    <div class="clear"><!--clear--></div>
    <p><input type="button" class="button-primary" onclick="podsExport()" value="Export!" /></p>
    <p><textarea id="export_code" style="height:180px; border:2px dashed #e3e3e3">The export code will appear here.</textarea></p>
</div>

<div id="importArea" class="area hidden">
    <h2 class="title">Import Package</h2>
    <p>Paste the package code into the form. You will be taken to a confirmation screen.</p>
    <p><textarea id="import_code" style="height:180px; border:2px dashed #e3e3e3"></textarea></p>
    <input type="button" class="button-primary" onclick="podsImport(false)" value="Proceed to Confirmation" />
    <div id="import_finalize"></div>
</div>
