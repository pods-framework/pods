<?php
// Get all datatypes
$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}
?>

<!--
==================================================
Begin pod area
==================================================
-->
<script type="text/javascript">
jQuery(function() {
    jQuery(".select-pod").change(function() {
        dt = jQuery(this).val();
        dtname = jQuery(".select-pod > option:selected").html();
        if ("" == dt) {
            jQuery("#columnBox").hide();
            jQuery("#podContent").hide();
            jQuery("#podArea #column_list").html(jQuery(".pod-welcome").html());
        }
        else {
            jQuery("#columnBox").show();
            jQuery("#podContent").show();
            resetForm();
            loadPod();
        }
    });
    jQuery(".select-pod").change();
    jQuery("#podBox").jqm();
});

function resetForm() {
    jQuery("#column_name").val("");
    jQuery("#column_name").attr("disabled", 0);
    jQuery("#column_label").val("");
    jQuery("#column_comment").val("");
    jQuery("#column_type").val("date");
    jQuery("#column_type").attr("disabled", 0);
    jQuery("#column_pickval").val("");
    jQuery("#column_pick_filter").val("");
    jQuery("#column_pick_orderby").val("");
    jQuery("#column_display_helper").val("");
    jQuery("#column_input_helper").val("");
    jQuery("#column_required").attr("checked", 0);
    jQuery("#column_required").attr("disabled", 0);
    jQuery("#column_unique").attr("checked", 0);
    jQuery("#column_unique").attr("disabled", 0);
    jQuery("#column_multiple").attr("checked", 0);
    jQuery("#column_multiple").attr("disabled", 0);
    jQuery("#column_sister_field_id").val("");
    jQuery(".coltype-pick").hide();
    jQuery(".column-header").html("Add Column");
    add_or_edit = "add";
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
    if ("pick" == val) {
        jQuery(".coltype-pick").show();
    }
    else {
        jQuery(".coltype-pick").hide();
    }
}

function sisterFields(sister_field_id) {
    var pickval = jQuery("#column_pickval").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/sister_fields.php",
        data: "auth="+auth+"&datatype="+dt+"&pickval="+pickval,
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
                jQuery("#column_sister_field_id option[value="+sister_field_id+"]").attr("selected", "selected");
                jQuery("#column_sister_field_id").show();
            }
        }
    });
}

function loadPod() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/load.php",
        data: "auth="+auth+"&id="+dt,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var json = eval("("+msg+")");
                var label = (null == json.label) ? "" : json.label;
                var is_toplevel = parseInt(json.is_toplevel);
                var list_filters = (null == json.list_filters) ? "" : json.list_filters;
                var before_helpers = (null == json.before_helpers) ? "" : json.before_helpers;
                var after_helpers = (null == json.after_helpers) ? "" : json.after_helpers;
                jQuery("#pod_label").val(label);
                jQuery("#is_toplevel").attr("checked", is_toplevel);
                jQuery("#list_filters").val(list_filters);
                jQuery("#list_before_helpers").html("");
                jQuery("#list_after_helpers").html("");

                // Build the column list
                var html = "";
                var fields = json.fields;
                for (var i = 0; i < fields.length; i++) {
                    var id = fields[i].id;
                    var name = fields[i].name;
                    var coltype = fields[i].coltype;
                    var pickval = fields[i].pickval;
                    if ("" != pickval && null != pickval && "NULL" != pickval) {
                        coltype += " "+pickval;
                    }
                    html += '<div class="col'+id+'">';
                    html += '<div class="btn moveup"></div> ';
                    html += '<div class="btn movedown"></div> ';
                    html += '<div class="btn editme"></div> ';

                    // Mark required fields
                    var required = parseInt(fields[i].required);
                    required = (1 == required) ? ' <span class="red">*</span>' : "";

                    // Default columns
                    if ("name" != name) {
                        html += '<div class="btn dropme"></div> ';
                    }
                    html += name+" ("+coltype+")"+required+"</div>";
                    jQuery("#podArea #column_list").html(html);
                }

                jQuery("#podArea #column_list .btn").click(function() {
                    var field_id = jQuery(this).parent().attr("class").substr(3);
                    var classname = jQuery(this).attr("class").substr(4);
                    if ("moveup" == classname) {
                        moveColumn(field_id, "up");
                    }
                    else if ("movedown" == classname) {
                        moveColumn(field_id, "down");
                    }
                    else if ("dropme" == classname) {
                        dropColumn(field_id);
                    }
                    else if ("editme" == classname) {
                        loadColumn(field_id);
                    }
                });

                var html = "";
                if ("" != before_helpers) {
                    before_helpers = before_helpers.split(",");
                    for (var i = 0; i < before_helpers.length; i++) {
                        var val = before_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
                    }
                    jQuery("#list_before_helpers").html(html);
                }

                var html = "";
                if ("" != after_helpers) {
                    after_helpers = after_helpers.split(",");
                    for (var i = 0; i < after_helpers.length; i++) {
                        var val = after_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
                    }
                    jQuery("#list_after_helpers").html(html);
                }
            }
        }
    });
}

function addPod() {
    var name = jQuery("#new_pod").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/add.php",
        data: "auth="+auth+"&type=pod&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<option value="'+id+'">'+name+'</option>';
                jQuery(".select-pod").append(html);
                jQuery("#podBox #new_pod").val("");
                jQuery(".select-pod > option[value="+id+"]").attr("selected", "selected");
                jQuery(".select-pod").change();
                jQuery("#podBox").jqmHide();
            }
        }
    });
}

function editPod() {
    var label = jQuery("#pod_label").val();
    var is_toplevel = jQuery("#is_toplevel").is(":checked") ? 1 : 0;
    var list_filters = jQuery("#list_filters").val();
    var before_helpers = "";
    jQuery("#list_before_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        before_helpers += ("" == before_helpers) ? new_helper : "," + new_helper;
    });
    var after_helpers = "";
    jQuery("#list_after_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        after_helpers += ("" == after_helpers) ? new_helper : "," + new_helper;
    });
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/edit.php",
        data: "auth="+auth+"&datatype="+dt+"&label="+label+"&is_toplevel="+is_toplevel+"&list_filters="+encodeURIComponent(list_filters)+"&before_helpers="+before_helpers+"&after_helpers="+after_helpers,
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
    if (confirm("Do you really want to drop this pod and its contents?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/drop.php",
            data: "auth="+auth+"&pod="+dt+"&dtname="+dtname,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".select-pod > option[value="+dt+"]").remove();
                    jQuery(".select-pod").change();
                }
            }
        });
    }
}

function loadColumn(col) {
    resetForm();
    column_id = col;
    add_or_edit = "edit";
    jQuery(".column-header").html("Edit Column");

    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/load.php",
        data: "auth="+auth+"&col="+col,
        success: function(msg) {
            var json = eval("("+msg+")");
            var name = (null == json.name) ? "" : json.name;
            var label = (null == json.label) ? "" : json.label;
            var comment = (null == json.comment) ? "" : json.comment;
            var coltype = (null == json.coltype) ? "" : json.coltype;
            var pickval = (null == json.pickval) ? "" : json.pickval;
            var sister_field_id = (null == json.sister_field_id) ? "" : json.sister_field_id;
            var pick_filter = (null == json.pick_filter) ? "" : json.pick_filter;
            var pick_orderby = (null == json.pick_orderby) ? "" : json.pick_orderby;
            var display_helper = (null == json.display_helper) ? "" : json.display_helper;
            var input_helper = (null == json.input_helper) ? "" : json.input_helper;
            var required = parseInt(json.required);
            var unique = parseInt(json.unique);
            var multiple = parseInt(json.multiple);
            jQuery("#column_name").val(name);
            jQuery("#column_label").val(label);
            jQuery("#column_comment").val(comment);
            jQuery("#column_type").val(coltype);
            jQuery("#column_pickval").val(pickval);
            jQuery("#column_pick_filter").val(pick_filter);
            jQuery("#column_pick_orderby").val(pick_orderby);
            jQuery("#column_display_helper").val(display_helper);
            jQuery("#column_input_helper").val(input_helper);
            jQuery("#column_required").attr("checked", required);
            jQuery("#column_unique").attr("checked", unique);
            jQuery("#column_multiple").attr("checked", multiple);

            if ("name" == name) {
                jQuery("#column_name").attr("disabled", 1);
                jQuery("#column_type").attr("disabled", 1);
                jQuery("#column_required").attr("disabled", 1);
            }
            if ("pick" == coltype) {
                jQuery(".coltype-pick").show();
            }
            if (0 != parseInt(sister_field_id)) {
                sisterFields(sister_field_id);
            }
            jQuery("#columnBox").animate({marginTop:"20px"},100).animate({marginTop:"0"},100).animate({marginTop:"20px"},100).animate({marginTop:"0"},100);
        }
    });
}

function addColumn() {
    var name = jQuery("#column_name").val();
    var label = jQuery("#column_label").val();
    var comment = jQuery("#column_comment").val();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var pick_filter = jQuery("#column_pick_filter").val();
    var pick_orderby = jQuery("#column_pick_orderby").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var display_helper = jQuery("#column_display_helper").val();
    var input_helper = jQuery("#column_input_helper").val();
    var required = jQuery("#column_required").is(":checked") ? 1 : 0;
    var unique = jQuery("#column_unique").is(":checked") ? 1 : 0;
    var multiple = jQuery("#column_multiple").is(":checked") ? 1 : 0;

    if ("pick" == coltype && "" == pickval) {
        alert("Error: Invalid pick selection");
        return false;
    }
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/add.php",
        data: "auth="+auth+"&datatype="+dt+"&dtname="+dtname+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&pick_filter="+pick_filter+"&pick_orderby="+pick_orderby+"&sister_field_id="+sister_field_id+"&display_helper="+display_helper+"&input_helper="+input_helper+"&required="+required+"&unique="+unique+"&multiple="+multiple,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                resetForm();
                loadPod();
            }
        }
    });
}

function moveColumn(col, dir) {
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=move&datatype="+dt+"&col="+col+"&dir="+dir,
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
    var comment = jQuery("#column_comment").val();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var pick_filter = jQuery("#column_pick_filter").val();
    var pick_orderby = jQuery("#column_pick_orderby").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var display_helper = jQuery("#column_display_helper").val();
    var input_helper = jQuery("#column_input_helper").val();
    var required = jQuery("#column_required").is(":checked") ? 1 : 0;
    var unique = jQuery("#column_unique").is(":checked") ? 1 : 0;
    var multiple = jQuery("#column_multiple").is(":checked") ? 1 : 0;

    if ("pick" == coltype && "" == pickval) {
        alert("Error: Invalid pick selection");
        return false;
    }
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edit&field_id="+col+"&datatype="+dt+"&dtname="+dtname+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&pick_filter="+pick_filter+"&pick_orderby="+pick_orderby+"&sister_field_id="+sister_field_id+"&display_helper="+display_helper+"&input_helper="+input_helper+"&required="+required+"&unique="+unique+"&multiple="+multiple,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Success!");
                resetForm();
                loadPod();
            }
        }
    });
}

function dropColumn(col) {
    if (confirm("Do you really want to drop this column?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/drop.php",
            data: "auth="+auth+"&col="+col+"&dtname="+dtname,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery(".col"+col).remove();
                    resetForm();
                }
            }
        });
    }
}
</script>

<!--
==================================================
Pod popups
==================================================
-->
<div id="podBox" class="jqmWindow">
    <input type="text" id="new_pod" />
    <input type="button" class="button" onclick="addPod()" value="Add Pod" />
    <p>Please use lowercase letters, dashes or underscores only.</p>
</div>

<!--
==================================================
Pod HTML
==================================================
-->
<div id="podArea" class="area hidden">
    <div>
        <select class="area-select select-pod">
            <option value="">Choose a Pod</option>
<?php
if (isset($datatypes))
{
    foreach ($datatypes as $key => $val)
    {
?>
            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
<?php
    }
}
?>
        </select>
        <input type="button" class="button-primary" onclick="jQuery('#podBox').jqmShow()" value="Add new pod" />
    </div>

    <div class="pod-welcome hidden">
        Need some help? Check out the <a href="http://codex.uproot.us" target="_blank">Codex</a> to get started.
    </div>

    <div id="pod-area-left">
        <p id="column_list"></p>
        <table id="podContent" style="width:100%" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <h2 class="title" style="margin:10px 0">Pod Settings</h2>
                </td>
            </tr>
            <tr>
                <td>Top Level Menu?</td>
                <td><input type="checkbox" id="is_toplevel" /></td>
            </tr>
            <tr>
                <td>Menu Label</td>
                <td><input type="text" id="pod_label" value="" /></td>
            </tr>
            <tr>
                <td>List Filters</td>
                <td><input type="text" id="list_filters" /></td>
            </tr>
            <tr>
                <td>Pre-save Helpers</td>
                <td>
                    <select id="before_helpers">
                        <option value="">-- Select --</option>
<?php
if (isset($helper_types['before']))
{
    foreach ($helper_types['before'] as $key => $helper_name)
    {
?>
                        <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                    </select>
                    <input type="button" class="button" value="Add" onclick="addPodHelper('list_before_helpers', 'before_helpers')" />
                    <div id="list_before_helpers"></div>
                </td>
            </tr>
            <tr>
                <td>Post-save Helpers</td>
                <td>
                    <select id="after_helpers">
                        <option value="">-- Select --</option>
<?php
if (isset($helper_types['after']))
{
    foreach ($helper_types['after'] as $key => $helper_name)
    {
?>
                        <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                    </select>
                    <input type="button" class="button" value="Add" onclick="addPodHelper('list_after_helpers', 'after_helpers')" />
                    <div id="list_after_helpers"></div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="button" class="button-primary" onclick="editPod()" value="Save settings" /> or
                    <a href="javascript:;" onclick="dropPod()">drop pod</a>
                </td>
            </tr>
        </table>
    </div>

    <div id="pod-area-right">
        <table id="columnBox" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <h2 class="title column-header" style="margin-bottom:10px">New Column</h2>
                </td>
            </tr>
            <tr>
                <td>Name</td>
                <td><input type="text" id="column_name" value="" /></td>
            </tr>
            <tr>
                <td>Label</td>
                <td><input type="text" id="column_label" value="" /></td>
            </tr>
            <tr>
                <td>Column Type</td>
                <td>
                    <select id="column_type" onchange="doDropdown(this.value)">
                        <option value="date">date</option>
                        <option value="num">number</option>
                        <option value="bool">boolean (true, false)</option>
                        <option value="txt">text (title, email, phone, url)</option>
                        <option value="desc">desc (summary, body)</option>
                        <option value="code">code (no visual editor)</option>
                        <option value="file">file (upload)</option>
                        <option value="slug">slug (permalink)</option>
                        <option value="pick">pick</option>
                    </select>
                </td>
            </tr>
            <tr class="coltype-pick">
                <td>Related to</td>
                <td>
                    <select id="column_pickval" onchange="sisterFields()">
                        <option value="" style="font-weight:bold; font-style:italic">-- Pods --</option>
<?php
// Get pods, including country and state
$result = pod_query("SHOW TABLES LIKE '@wp_pod_tbl_%'");
while ($row = mysql_fetch_array($result))
{
    $table_name = explode('tbl_', $row[0]);
    $table_name = $table_name[1];
?>
                        <option value="<?php echo $table_name; ?>"><?php echo $table_name; ?></option>
<?php
}
?>
                        <option value="" style="font-weight:bold; font-style:italic">-- Category --</option>
<?php
// Category dropdown list
$sql = "
SELECT DISTINCT
    t.term_id AS id, t.name
FROM
    @wp_term_taxonomy tx
INNER JOIN
    @wp_terms t ON t.term_id = tx.parent
";
$result = pod_query($sql);
while ($row = mysql_fetch_assoc($result))
{
?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
}
?>
                        <option value="" style="font-weight:bold; font-style:italic">-- WordPress --</option>
                        <option value="wp_page">WP Page</option>
                        <option value="wp_post">WP Post</option>
                        <option value="wp_user">WP User</option>
                    </select>
                </td>
            </tr>
            <tr class="coltype-pick">
                <td>Bi-directional?</td>
                <td>
                    <select id="column_sister_field_id"></select>
                </td>
            </tr>
            <tr class="coltype-pick">
                <td>PICK Filter</td>
                <td>
                    <input type="text" id="column_pick_filter" value="" />
                </td>
            </tr>
            <tr class="coltype-pick">
                <td>PICK Orderby</td>
                <td>
                    <input type="text" id="column_pick_orderby" value="" />
                </td>
            </tr>
            <tr>
                <td>Attributes</td>
                <td>
                    <input type="checkbox" id="column_required" /> required<br />
                    <input type="checkbox" id="column_unique" /> unique<br />
                    <input type="checkbox" id="column_multiple" /> multiple
                </td>
            </tr>
            <tr>
                <td>Display Helper</td>
                <td>
                    <select id="column_display_helper">
                        <option value="">-- Select --</option>
<?php
// Get all display helpers
if (isset($helper_types['display']))
{
    foreach ($helper_types['display'] as $key => $name)
    {
?>
                        <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
<?php
    }
}
?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Input Helper</td>
                <td>
                    <select id="column_input_helper">
                        <option value="">-- Select --</option>
<?php
// Get all display helpers
if (isset($helper_types['input']))
{
    foreach ($helper_types['input'] as $key => $name)
    {
?>
                        <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
<?php
    }
}
?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Comment</td>
                <td>
                    <input type="text" id="column_comment" value="" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="button" class="button-primary" onclick="addOrEditColumn()" value="Save column" />
                    or <a href="javascript:;" onclick="resetForm()">cancel</a>
                </td>
            </tr>
        </table>
    </div>
</div>

