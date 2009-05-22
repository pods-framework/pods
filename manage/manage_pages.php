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
    jQuery("#pageArea .tab").click(function() {
        jQuery("#pageArea .tab").removeClass("active");
        page_id = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery("#pageArea .idle").show();
        loadPage();
    });

    jQuery("#pageArea .editme").click(function() {
        page_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#page_form").html();
        jQuery("#pageArea .pform").html("");
        jQuery("#pageArea .ptr").hide();
        jQuery("#pageArea #ptr"+page_id).show();
        jQuery("#pageArea #pform"+page_id).html(theform);
        loadPage();
    });

    jQuery("#pageArea .dropme").click(function() {
        page_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#pform"+page_id).html();
        dropPage();
    });

    jQuery("#pageBox").jqm();
});

function loadPage() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&page_id="+page_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var page_data = eval("("+msg+")");
                var uri = (null == page_data.uri) ? "" : page_data.uri;
                var title = (null == page_data.title) ? "" : page_data.title;
                var phpcode = (null == page_data.phpcode) ? "" : page_data.phpcode;
                var template = (null == page_data.page_template) ? "" : page_data.page_template;
                jQuery("#page_name").html(uri);
                jQuery("#page_title").val(title);
                jQuery("#page_content").val(phpcode);
                jQuery("#page_template").val(template);
            }
        }
    });
}

function addPage() {
    var uri = jQuery("#new_page").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=page&uri="+uri,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var rand = Math.round(Math.random()*9999);
                window.location="?page=pods&"+rand+"#page";
            }
        }
    });
}

function editPage() {
    var title = jQuery("#pageArea #pform"+page_id+" #page_title").val();
    var content = jQuery("#pageArea #pform"+page_id+" #page_content").val();
    var template = jQuery("#pageArea #pform"+page_id+" #page_template").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=editpage&page_id="+page_id+"&page_title="+encodeURIComponent(title)+"&page_template="+encodeURIComponent(template)+"&phpcode="+encodeURIComponent(content),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                colorFade('page', page_id);
            }
        }
    });
}

function dropPage() {
    if (confirm("Do you really want to drop this page?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&page="+page_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#pageArea #ptr"+page_id).remove();
                    jQuery("#pageArea tr#row"+page_id).css("background", "red");
                    jQuery("#pageArea tr#row"+page_id).fadeOut("slow");
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
    <div>Ex: <strong>/resources/latest/</strong> or <strong>/events/*</strong></div>
</div>

<!--
==================================================
Page HTML
==================================================
-->
<div id="pageArea" class="area hidden">
    <input type="button" class="button" onclick="jQuery('#pageBox').jqmShow()" value="Add new page" />
    <table id="browseTable" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th>URI</th>
            <th></th>
        </tr>
<?php
if (isset($pages))
{
    foreach ($pages as $id => $uri)
    {
        $zebra = ('' == $zebra) ? ' class="zebra"' : '';
?>
        <tr id="row<?php echo $id; ?>"<?php echo $zebra; ?>>
            <td width="20">
                <div class="btn editme"></div>
            </td>
            <td><?php echo $uri; ?></td>
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

    <div id="page_form" class="hidden">
        <input type="text" id="page_title" /> Page Title<br />
        <textarea id="page_content"></textarea><br />
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
        </select>
        <input type="button" class="button" onclick="editPage()" value="Save changes" /> or <a href="javascript:;" onclick="jQuery('.ptr').hide()">close</a>
    </div>
</div>

