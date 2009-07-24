<?php
// Get all pages
$result = pod_query("SELECT id, uri FROM @wp_pod_pages ORDER BY uri");
while ($row = mysql_fetch_assoc($result))
{
    $pages[$row['id']] = $row['uri'];
}
?>

<!--
==================================================
Begin page area
==================================================
-->
<script type="text/javascript">
jQuery(function() {
    jQuery(".select-page").change(function() {
        page_id = jQuery(this).val();
        if ("" == page_id) {
            jQuery("#pageContent").hide();
            jQuery("#page_code").val("");
            jQuery("#page_precode").val("");
        }
        else {
            jQuery("#pageContent").show();
            loadPage();
        }
    });
    jQuery(".select-page").change();
    jQuery("#pageBox").jqm();
});

function loadPage() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/load.php",
        data: "auth="+auth+"&page_id="+page_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var json = eval("("+msg+")");
                var title = (null == json.title) ? "" : json.title;
                var code = (null == json.phpcode) ? "" : json.phpcode;
                var precode = (null == json.precode) ? "" : json.precode;
                var template = (null == json.page_template) ? "" : json.page_template;
                jQuery("#page_code").val(code);
                jQuery("#page_precode").val(precode);
                jQuery("#page_title").val(title);
                jQuery("#page_template").val(template);
            }
        }
    });
}

function addPage() {
    var uri = jQuery("#new_page").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/add.php",
        data: "auth="+auth+"&type=page&uri="+uri,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<option value="'+id+'">'+uri+'</option>';
                jQuery(".select-page").append(html);
                jQuery("#pageBox #new_page").val("");
                jQuery(".select-page > option[value="+id+"]").attr("selected", "selected");
                jQuery(".select-page").change();
                jQuery("#pageBox").jqmHide();
            }
        }
    });
}

function editPage() {
    var code = jQuery("#page_code").val();
    var precode = jQuery("#page_precode").val();
    var title = jQuery("#page_title").val();
    var template = jQuery("#page_template").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=editpage&page_id="+page_id+"&page_title="+encodeURIComponent(title)+"&page_template="+encodeURIComponent(template)+"&phpcode="+encodeURIComponent(code)+"&precode="+encodeURIComponent(precode),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Success!");
            }
        }
    });
}

function dropPage() {
    if (confirm("Do you really want to drop this page?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/drop.php",
            data: "auth="+auth+"&page="+page_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".select-page > option[value="+page_id+"]").remove();
                    jQuery(".select-page").change();
                }
            }
        });
    }
}
</script>

<!--
==================================================
Page popups
==================================================
-->
<div id="pageBox" class="jqmWindow">
    <input type="text" id="new_page" style="width:280px" />
    <input type="button" class="button" onclick="addPage()" value="Add Page" />
    <div>Ex: <strong>events</strong> or <strong>events/latest/*</strong></div>
</div>

<!--
==================================================
Page HTML
==================================================
-->
<div id="pageArea" class="area hidden">
    <select class="area-select select-page">
        <option value="">Choose a Page</option>
<?php
if (isset($pages))
{
    foreach ($pages as $key => $val)
    {
?>
        <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
<?php
    }
}
?>
    </select>
    <input type="button" class="button-primary" onclick="jQuery('#pageBox').jqmShow()" value="Add new page" />
    <div id="pageContent">
        <textarea id="page_code"></textarea><br />
        Precode (optional):
        <textarea id="page_precode"></textarea><br />
        Page Title (optional):<br />
        <input id="page_title" type="text" />
        <select id="page_template">
            <option value="">-- Page Template --</option>
<?php
$page_templates = get_page_templates();
foreach ($page_templates as $template => $file)
{
?>
            <option value="<?php echo $file; ?>"><?php echo $template; ?></option>
<?php
}
?>
        </select><br />
        <input type="button" class="button" onclick="editPage()" value="Save changes" /> or
        <a href="javascript:;" onclick="dropPage()">drop page</a>
    </div>
</div>
