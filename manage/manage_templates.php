<?php
// Get all pages
$result = pod_query("SELECT id, name FROM @wp_pod_templates ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $templates[$row['id']] = $row['name'];
}
?>

<!--
==================================================
Begin template area
==================================================
-->
<script type="text/javascript">
jQuery(function() {
    jQuery("#templateArea .tab").click(function() {
        jQuery("#templateArea .tab").removeClass("active");
        template_id = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery("#templateArea .idle").show();
        loadTemplate();
    });

    jQuery("#templateArea .editme").click(function() {
        template_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#template_form").html();
        jQuery("#templateArea .pform").html("");
        jQuery("#templateArea .ptr").hide();
        jQuery("#templateArea #ptr"+template_id).show();
        jQuery("#templateArea #pform"+template_id).html(theform);
        loadTemplate();
    });

    jQuery("#templateArea .dropme").click(function() {
        template_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#templateArea #pform"+template_id).html();
        dropPage();
    });

    jQuery("#templateBox").jqm();
});

function loadTemplate() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&template_id="+template_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var template_data = eval("("+msg+")");
                var name = (null == template_data.name) ? "" : template_data.name;
                var code = (null == template_data.code) ? "" : template_data.code;
                jQuery("#template_name").html(name);
                jQuery("#template_code").val(code);
            }
        }
    });
}

function addTemplate() {
    var name = jQuery("#new_template").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=template&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var rand = Math.round(Math.random()*9999);
                window.location="?page=pods&"+rand+"#template";
            }
        }
    });
}

function editTemplate() {
    var name = jQuery("#templateArea #pform"+template_id+" #template_name").val();
    var code = jQuery("#templateArea #pform"+template_id+" #template_code").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edittemplate&template_id="+template_id+"&code="+encodeURIComponent(code),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                colorFade('template', template_id);
            }
        }
    });
}

function dropTemplate() {
    if (confirm("Do you really want to drop this template?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&template="+template_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#templateArea #ptr"+template_id).remove();
                    jQuery("#templateArea tr#row"+template_id).css("background", "red");
                    jQuery("#templateArea tr#row"+template_id).fadeOut("slow");
                }
            }
        });
    }
}
</script>

<!--
==================================================
Template popups
==================================================
-->
<div id="templateBox" class="jqmWindow">
    <input type="text" id="new_template" style="width:280px" />
    <input type="button" class="button" onclick="addTemplate()" value="Add Template" />
    <div>Ex: <strong>event_list</strong> or <strong>gallery_photo_detail</strong></div>
</div>

<!--
==================================================
Template HTML
==================================================
-->
<div id="templateArea" class="area hidden">
    <input type="button" class="button" onclick="jQuery('#templateBox').jqmShow()" value="Add new template" />
    <table id="browseTable" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th>Name</th>
            <th></th>
        </tr>
<?php
if (isset($templates))
{
    foreach ($templates as $id => $name)
    {
        $zebra = ('' == $zebra) ? ' class="zebra"' : '';
?>
        <tr id="row<?php echo $id; ?>"<?php echo $zebra; ?>>
            <td width="20">
                <div class="btn editme"></div>
            </td>
            <td><?php echo $name; ?></td>
            <td width="20"><div class="btn dropme"></div></td>
        </tr>
        <tr id="ptr<?php echo $id; ?>" class="ptr hidden">
            <td id="pform<?php echo $id; ?>" class="pform" colspan="3"></td>
        </tr>
<?php
    }
}
?>
    </table>

    <div id="template_form" class="hidden">
        <textarea id="template_code"></textarea><br />
        <input type="button" class="button" onclick="editTemplate()" value="Save changes" /> or <a href="javascript:;" onclick="jQuery('.ptr').hide()">close</a>
    </div>
</div>

