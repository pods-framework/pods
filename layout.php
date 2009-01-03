<?php
// Get all layouts
/*$result = pod_query("SELECT * FROM {$table_prefix}pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}*/
?>

<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
jQuery(function() {
    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        var activeArea = jQuery(this).attr("rel");
        jQuery(".area").hide();
        jQuery("#"+activeArea).show();
    });

    jQuery(".tab").click(function() {
        jQuery(".tab").removeClass("active");
        datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery(".idle").show();
        loadPod();
    });

    jQuery("#layoutBox").jqm();
});

function loadLayout() {

}

function addLayout() {

}

function editLayout() {

}

function dropLayout() {

}
</script>

<!--
==================================================
Begin popups
==================================================
-->
<div id="layoutBox" class="jqmWindow">
    <input type="text" id="new_page" style="width:280px" />
    <input type="button" class="button" onclick="addPage()" value="Add Layout" />
    <p>Ex: <b>homepage_2_column</b></p>
</div>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
    <div class="navTab active" rel="layoutArea">Layout Editor</div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin pod area
==================================================
-->
<div id="layoutArea" class="area">
    <div class="tabs">
        <input type="button" class="button" onclick="jQuery('#layoutBox').jqmShow()" value="Add new layout" />
<?php
/*
==================================================
Build the left tabs
==================================================
*/
if (isset($layouts))
{
    foreach ($layouts as $key => $val)
    {
?>
        <div class="tab t<?php echo $key; ?>"><?php echo $val; ?></div>
<?php
    }
}
?>
    </div>
    <div class="rightside">
        <h2 class="title" id="pod_name">Choose a Layout</h2>
        <div style="width:500px">
            <div style="float:left; width:400px; height:300px; border:1px solid #000; background:#f9f9f9">
                <div style="float:left; width:97%; margin:5px; height:96%; border:1px dashed #ccc">
                    This is a work in progress!!!
                </div>
            </div>
            <div style="float:left">
                <div onclick="alert('ok')">Split up/down</div>
                <div onclick="alert('ok')">Split left/right</div>
            </div>
        </div>
    </div>
    <div class="clear"><!--clear--></div>
</div>

