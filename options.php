<?php
// Get all datatypes
$result = mysql_query("SELECT * FROM wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
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
var datatype;
var column_val;

jQuery(function() {
    jQuery(".tab").click(function() {
        jQuery(".tab").removeClass("active");
        datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery(".idle").attr("disabled", false);
        loadPod();
    });
    jQuery("#dialog").jqm();
    jQuery(".idle").attr("disabled", true);
});

function doDropdown(val) {
    if ('pick' == val) {
        jQuery("#column_pickval").show();
    }
    else {
        jQuery("#column_pickval").val("");
        jQuery("#column_pickval").hide();
    }
    jQuery("#column_sister_field_id").val("");
    jQuery("#column_sister_field_id").hide();
}

function reset() {
    jQuery("#column_type").val("");
    jQuery("#column_pickval").val("");
    jQuery("#column_sister_field_id").val("");
    jQuery("#column_sister_field_id").hide();
    jQuery("#column_pickval").hide();
    
    
}

function sisterFields() {
    var pickval = jQuery("#column_pickval").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/sister_fields.php",
        data: "datatype="+datatype+"&pickval="+pickval,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else if ("" != msg) {
                var html = '<option value="">-- Rel Field --</option>';
                jQuery("#column_sister_field_id").html("");
                var items = eval("("+msg+")");
                for (var i = 0; i < items.length; i++) {
                    var id = items[i].id;
                    var name = items[i].name;
                    html += '<option value="'+id+'">'+name+'</option>';
                }
                jQuery("#column_sister_field_id").html(html);
                jQuery("#column_sister_field_id").show();
            }
        }
    });
}

function addPod() {
    var name = jQuery("#new_pod").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/add.php",
        data: "type=pod&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<div class="tab t'+id+'">'+name+'</div>';
                jQuery(".tabs").append(html);
                jQuery("#dialog").jqmHide();
                jQuery(".t"+id).click(function() {
                    jQuery(".tab").removeClass("active");
                    datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
                    jQuery(this).addClass("active");
                    loadPod();
                });
            }
        }
    });
}

function loadPod() {
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/load.php",
        data: "id="+datatype,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var pod_type = eval("("+msg+")");
                var description = (null == pod_type.description) ? "" : pod_type.description;
                var list_filters = (null == pod_type.list_filters) ? "" : pod_type.list_filters;
                var tpl_detail = (null == pod_type.tpl_detail) ? "" : pod_type.tpl_detail;
                var tpl_list = (null == pod_type.tpl_list) ? "" : pod_type.tpl_list;
                jQuery("#pod_name").html(pod_type.name);
                jQuery("#pod_description").val(description);
                jQuery("#list_filters").val(list_filters);
                jQuery("#tpl_detail").val(tpl_detail);
                jQuery("#tpl_list").val(tpl_list);

                // Build the column list
                var html = "";
                var fields = pod_type.fields;
                for (var i = 0; i < fields.length; i++) {
                    var id = fields[i].id;
                    var name = fields[i].name;
                    var coltype = fields[i].coltype;
                    var pickval = fields[i].pickval;
                    coltype = (null == pickval) ? coltype : coltype+" "+pickval;
                    html += '<div class="col'+id+'">';
                    html += '<div class="btn moveup"></div> ';
                    html += '<div class="btn movedown"></div> ';
                    html += '<div class="btn dropme"></div> ';
                    html += '<span class="btn editme">'+name+"</span> ("+coltype+")</div>";
                    jQuery("#column_list").html(html);
                }

                jQuery("#column_list .btn").click(function() {
                    var field_id = jQuery(this).parent().attr("class").substr(3);
                    var classname = jQuery(this).attr("class").substr(4);
                    if ('moveup' == classname) {
                        moveColumn(field_id, 'up');
                    }
                    else if ('movedown' == classname) {
                        moveColumn(field_id, 'down');
                    }
                    else if ('dropme' == classname) {
                        dropColumn(field_id);
                    }
                    else if ('editme' == classname) {
                        editColumn(field_id);
                    }
                });
            }
            reset();
        }
    });
}

function editPod() {
    var desc = jQuery("#pod_description").val();
    var list_filters = jQuery("#list_filters").val();
    var tpl_detail = jQuery("#tpl_detail").val();
    var tpl_list = jQuery("#tpl_list").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/edit.php",
        data: "datatype="+datatype+"&desc="+desc+"&list_filters="+encodeURIComponent(list_filters)+"&tpl_detail="+encodeURIComponent(tpl_detail)+"&tpl_list="+encodeURIComponent(tpl_list),
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

function dropPod() {
    if (confirm("Are you really sure?")) {
        jQuery.ajax({
            url: "/wp-content/plugins/pods/ajax/drop.php",
            data: "pod="+datatype,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".t"+datatype).remove();
                }
            }
        });
    }
}

function addColumn() {
    var name = jQuery("#column_name").val();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/add.php",
        data: "datatype="+datatype+"&name="+name+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                loadPod();
                reset();
            }
        }
    });
}

function editColumn(col) {
    column_val = jQuery(".col"+col+" .editme").html();
    var html = '<input type="text" class="editable" value="'+column_val+'" /> <input type="button" value="OK" onclick="renameColumn('+col+')" /><input type="button" value="Cancel" onclick="loadPod()" />';
    jQuery(".col"+col+" .editme").html(html);
    jQuery(".col"+col+" .editme").unbind("click");
}

function renameColumn(col) {
    var name = jQuery(".editable").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/edit.php",
        data: "action=rename&field_id="+col+"&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                loadPod();
            }
        }
    });
}

function moveColumn(col, dir) {
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/edit.php",
        data: "action=move&datatype="+datatype+"&col="+col+"&dir="+dir,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                loadPod();
            }
        }
    });
}

function dropColumn(col) {
    if (confirm("Are you really sure?")) {
        jQuery.ajax({
            url: "/wp-content/plugins/pods/ajax/drop.php",
            data: "col="+col,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".col"+col).remove();
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
    Add New Pod: <input type="text" id="new_pod" /> <input type="button" value="Save" onclick="addPod()" />
    <p>Please use lowercase letters, dashes or underscores only.</p>
</div>

<div class="wrap">
    <h3>Manage Pods (<a href="javascript:;" onclick="jQuery('#dialog').jqmShow()">add new</a>)</h3>

    <table style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <td valign="top" class="tabs">
<?php
if (isset($datatypes))
{
    foreach ($datatypes as $key => $val)
    {
?>
                <div class="tab t<?php echo $key; ?>"><?php echo $val; ?></div>
<?php
    }
}
?>
            </td>
            <td valign="top" class="data-form">
                <h2 id="pod_name">Choose a Pod</h2>
                <p><input class="popup" type="text" id="pod_description" style="display:none" /></p>
                <p id="column_list"><p>
                <p>
                    <input type="text" id="column_name" value="column_name" />
                    <select id="column_type" class="idle" onchange="doDropdown(this.value)">
                        <option value="date">date</option>
                        <option value="num">number</option>
                        <option value="bool">boolean (true, false)</option>
                        <option value="txt">text (title, caption, email, phone, url)</option>
                        <option value="desc">desc (body, summary, long text)</option>
                        <option value="file">file (document, photo, media)</option>
                        <option value="pick">pick</option>
                    </select>
                    <select id="column_pickval" style="display:none" onchange="sisterFields()">
                        <option value="" style="font-weight:bold; font-style:italic">-- Category --</option>
<?php
// Get the category dropdown list
$sql = "
SELECT DISTINCT
    t.term_id AS id, t.name
FROM
    wp_term_taxonomy tx
INNER JOIN
    wp_terms t ON t.term_id = tx.parent
";
$result = mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);
while ($row = mysql_fetch_assoc($result))
{
?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
}
?>
                        <option value="" style="font-weight:bold; font-style:italic">-- Table --</option>
<?php
$result = mysql_query("SHOW TABLES LIKE 'tbl_%'");
while ($row = mysql_fetch_array($result))
{
    $table_name = substr($row[0], 4);
?>
                        <option value="<?php echo $table_name; ?>"><?php echo $table_name; ?></option>
<?php
}
?>
                    </select>
                    <select id="column_sister_field_id" style="display:none"></select>
                    <input type="button" class="idle" value="Add column" onclick="addColumn()" />
                </p>

                <p class="extras" onclick="jQuery('#tpl_detail').toggle(); jQuery(this).toggleClass('open')">Detail Template</p>
                <textarea id="tpl_detail" class="hidden"></textarea>

                <p class="extras" onclick="jQuery('#tpl_list').toggle(); jQuery(this).toggleClass('open')">List Template</p>
                <textarea id="tpl_list" class="hidden"></textarea>

                <p class="extras" onclick="jQuery('#list_filters').toggle(); jQuery(this).toggleClass('open')">List Filters</p>
                <input type="text" id="list_filters" class="hidden" />

                <p>
                    <input type="button" class="idle" value="SAVE CHANGES" onclick="editPod()" />
                    <input type="button" class="idle" value="DROP TABLE" onclick="dropPod()" />
                </p>
            </td>
        </tr>
    </table>
</div>

