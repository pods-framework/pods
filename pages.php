<?php
// Get all pages
$result = mysql_query("SELECT * FROM wp_pod_pages ORDER BY uri");
while ($row = mysql_fetch_assoc($result))
{
    $pages[$row['id']] = array('uri' => $row['uri'], 'phpcode' => $row['phpcode']);
}
?>

<!--
==================================================
Begin Pods Javascript code
==================================================
-->

<link rel="stylesheet" type="text/css" href="/wp-content/plugins/pods/style.css" />
<script type="text/javascript" src="/wp-content/plugins/pods/js/jqmodal.js"></script>
<script type="text/javascript">
jQuery(function() {
    jQuery(".uri").click(function() {
        jQuery(this).parent(".extras").toggleClass("open");
        jQuery(this).siblings(".box").toggleClass("hidden");
    });
    jQuery("#dialog").jqm();
});

function addPage() {
    var uri = jQuery("#new_uri").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/add.php",
        data: "type=page&uri="+uri,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var html = '<div class="extras" id="'+msg+'"><span class="uri">'+uri+'</span>';
                html += '<div class="box hidden">';
                html += '<textarea style="width:80%; height:140px"></textarea>';
                html += '<input type="submit" value="save" onclick="editPage('+msg+')" /> or <a href="javascript:;" onclick="dropPage('+msg+')">drop page</a>';
                html += '</div>'
                jQuery(".wrap").append(html);

                jQuery("#"+msg+" > .uri").click(function() {
                    jQuery(this).parent(".extras").toggleClass("open");
                    jQuery(this).siblings(".box").toggleClass("hidden");
                });
                jQuery("#"+msg).click();
                jQuery("#dialog").jqmHide();
            }
        }
    });
}

function editPage(page) {
    var phpcode = jQuery("#"+page+" > .box > textarea").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/edit.php",
        data: "action=editpage&page_id="+page+"&phpcode="+encodeURIComponent(phpcode),
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

function dropPage(page) {
    if (confirm("Do you really want to drop this page?")) {
        jQuery.ajax({
            url: "/wp-content/plugins/pods/ajax/drop.php",
            data: "page="+page,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#"+page).remove();
                }
            }
        });
    }
}
</script>

<!--
==================================================
Begin HTML code
==================================================
-->

<div class="jqmWindow" id="dialog">
    Add New Page<br />
    URL: <input type="text" id="new_uri" style="width:280px" /> <input type="button" value="Save" onclick="addPage()" /><br />
    Ex: <b>/resources/events/latest/</b>
</div>

<div class="wrap">
    <h3>Manage Pages (<a href="javascript:;" onclick="jQuery('#dialog').jqmShow()">add new</a>)</h3>
<?php
if (isset($pages))
{
    foreach ($pages as $id => $val)
    {
?>
    <div class="extras" id="<?php echo $id; ?>">
        <span class="uri"><?php echo $val['uri']; ?></span>
        <div class="box hidden">
            <textarea style="width:80%; height:140px"><?php echo $val['phpcode']; ?></textarea>
            <input type="submit" value="Save" onclick="editPage(<?php echo $id; ?>)" />
<?php
        if (!in_array($val['uri'], array('/list/', '/detail/')))
        {
?>
            or <a href="javascript:;" onclick="dropPage(<?php echo $id; ?>)">drop page</a>
<?php
        }
?>
        </div>
    </div>
<?php
    }
}
?>
</div>

