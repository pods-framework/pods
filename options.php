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
var column_id;
var add_or_edit;

jQuery(function() {
    jQuery(".tab").click(function() {
        jQuery(".tab").removeClass("active");
        datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery(".idle").show();
        loadPod();
    });
    jQuery("#dialog").jqm();
    jQuery("#change").jqm();
});

function reset() {
    jQuery("#column_type").val("");
    jQuery("#column_pickval").val("");
    jQuery("#column_sister_field_id").val("");
    jQuery("#column_sister_field_id").hide();
    jQuery("#column_pickval").hide();
}

function addOrEditColumn() {
    if ("add" == add_or_edit) {
        addColumn();
    }
    else {
        editColumn(column_id);
    }
}

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

function sisterFields(sister_field_id) {
    var pickval = jQuery("#column_pickval").val();
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/sister_fields.php",
        data: "datatype="+datatype+"&pickval="+pickval,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else if ("" != msg) {
                var html = '<option value="">-- Related to --</option>';
                jQuery("#column_sister_field_id").html("");
                var items = eval("("+msg+")");
                for (var i = 0; i < items.length; i++) {
                    var id = items[i].id;
                    var name = items[i].name;
                    html += '<option value="'+id+'">'+name+'</option>';
                }
                jQuery("#column_sister_field_id").html(html);
                jQuery("#column_sister_field_id option[@value="+sister_field_id+"]").attr("selected", "selected");
                jQuery("#column_sister_field_id").show();
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
                    if ("" != pickval && null != pickval) {
                        coltype += " "+pickval;
                    }
                    html += '<div class="col'+id+'">';
                    html += '<div class="btn moveup"></div> ';
                    html += '<div class="btn movedown"></div> ';

                    // Default columns
                    if ("name" != name && "body" != name) {
                        html += '<div class="btn dropme"></div> ';
                        html += '<div class="btn editme"></div> ';
                    }
                    html += name+" ("+coltype+")</div>";
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
                        loadColumn(field_id);
                    }
                });
            }
            reset();
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
                    jQuery(".idle").show();
                    loadPod();
                });
                jQuery(".t"+id).click();
            }
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
    if (confirm("Do you really want to drop this pod?")) {
        var dtname = jQuery(".tab.active").html();
        jQuery.ajax({
            url: "/wp-content/plugins/pods/ajax/drop.php",
            data: "pod="+datatype+"&dtname="+dtname,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#pod_name").html("Choose a Pod");
                    jQuery("#column_list").html('Need some help? Check out the <a href="http://pods.uproot.us/" target="blank">User Guide</a> to get started.');
                    jQuery(".t"+datatype).remove();
                    jQuery(".idle").hide();
                }
            }
        });
    }
}

function loadColumn(col) {
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/load.php",
        data: "col="+col,
        success: function(msg) {
            var col_data = eval("("+msg+")");
            var name = (null == col_data.name) ? "" : col_data.name;
            var label = (null == col_data.label) ? "" : col_data.label;
            var coltype = (null == col_data.coltype) ? "" : col_data.coltype;
            var pickval = (null == col_data.pickval) ? "" : col_data.pickval;
            var sister_field_id = (null == col_data.sister_field_id) ? "" : col_data.sister_field_id;
            var required = parseInt(col_data.required);
            jQuery("#column_name").val(name);
            jQuery("#column_label").val(label);
            jQuery("#column_type").val(coltype);
            jQuery("#column_pickval").val(pickval);
            jQuery("#column_sister_field_id").hide();
            jQuery("#column_required").attr("checked", required);
            jQuery("#column_pickval").hide();
            if ("" != pickval) {
                jQuery("#column_pickval").show();
            }
            if ("" != sister_field_id) {
                sisterFields(sister_field_id);
            }
            column_id = col;
            add_or_edit = "edit";
            jQuery('#change').jqmShow();
        }
    });
}

function addColumn() {
    var name = jQuery("#column_name").val();
    var label = jQuery("#column_label").val();
    var dtname = jQuery(".tab.active").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/add.php",
        data: "datatype="+datatype+"&dtname="+dtname+"&name="+name+"&label="+label+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#change").jqmHide();
                loadPod();
                reset();
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

function editColumn(col) {
    var name = jQuery("#column_name").val();
    var label = jQuery("#column_label").val();
    var dtname = jQuery(".tab.active").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        url: "/wp-content/plugins/pods/ajax/edit.php",
        data: "action=edit&datatype="+datatype+"&dtname="+dtname+"&field_id="+col+"&name="+name+"&label="+label+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#change").jqmHide();
                loadPod();
                reset();
            }
        }
    });
}

function dropColumn(col) {
    if (confirm("Do you really want to drop this column?")) {
        var dtname = jQuery(".tab.active").html();
        jQuery.ajax({
            url: "/wp-content/plugins/pods/ajax/drop.php",
            data: "col="+col+"&dtname="+dtname,
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
    Add New Pod: <input type="text" id="new_pod" />
    <input type="button" class="button" onclick="addPod()" value="Save" />
    <p>Please use lowercase letters, dashes or underscores only.</p>
</div>
<div class="jqmWindow" id="change">
    <input type="hidden" id="add_or_edit" value="" />
    <div class="leftside">Name</div>
    <div class="rightside">
        <input type="text" id="column_name" value="" />
        <input type="checkbox" id="column_required" /> required?
    </div>

    <div class="leftside">Label</div>
    <div class="rightside">
        <input type="text" id="column_label" value="" />
        <input type="button" class="button" onclick="addOrEditColumn()" value="Save column" />
    </div>

    <div class="leftside">Column Type</div>
    <div class="rightside">
        <select id="column_type" onchange="doDropdown(this.value)">
            <option value="date">date</option>
            <option value="num">number</option>
            <option value="bool">boolean (true, false)</option>
            <option value="txt">text (title, caption, email, phone, url)</option>
            <option value="desc">desc (body, summary, long text)</option>
            <option value="file">file (document, media)</option>
            <option value="pick">pick</option>
        </select>
        <select id="column_pickval" style="display:none" onchange="sisterFields()">
            <option value="" style="font-weight:bold; font-style:italic">-- Category --</option>
<?php
/*
==================================================
Get the category dropdown list
==================================================
*/
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
/*
==================================================
Get all Pods, including country & state
==================================================
*/
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
        <select id="column_sister_field_id" class="hidden"></select>
    </div>
    <div class="clear"><!--clear--></div>
    <p><b>*CAUTION*</b> changing column types can result in data loss!</p>
</div>

<div class="wrap">
    <h2>Manage Pods (<a href="javascript:;" onclick="jQuery('#dialog').jqmShow()">add new</a>)</h2>

    <table style="width:700px" cellpadding="0" cellspacing="0">
        <tr>
            <td valign="top" class="tabs">
<?php
/*
==================================================
Build the left tabs
==================================================
*/
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
                <p><input class="popup hidden" type="text" id="pod_description" /></p>
                <p id="column_list">Need some help? Check out the <a href="http://pods.uproot.us/" target="blank">User Guide</a> to get started.</p>
                <div class="idle hidden">
                    <p>
                        <input type="button" class="button" onclick="add_or_edit='add'; jQuery('#change').jqmShow()" value="Add a column" />
                    </p>

                    <p class="extras" onclick="jQuery('#tpl_detail').toggle(); jQuery(this).toggleClass('open')">Detail Template</p>
                    <textarea id="tpl_detail" class="hidden"></textarea>

                    <p class="extras" onclick="jQuery('#tpl_list').toggle(); jQuery(this).toggleClass('open')">List Template</p>
                    <textarea id="tpl_list" class="hidden"></textarea>

                    <p class="extras" onclick="jQuery('#list_filters').toggle(); jQuery(this).toggleClass('open')">List Filters</p>
                    <input type="text" id="list_filters" class="hidden" />

                    <p>
                        <input type="button" class="button-primary" onclick="editPod()" value="Save changes" /> or
                        <a href="javascript:;" onclick="dropPod()">drop table</a>
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>

