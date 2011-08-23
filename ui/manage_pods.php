<!-- Begin pod area -->
<script type="text/javascript">
jQuery(function() {
    jQuery("#columnBox").hide();
    jQuery(".select-pod").change(function() {
        dt = jQuery(this).val();
        dtname = jQuery(".select-pod > option:selected").html();
        if ("" == dt) {
            jQuery("#columnBox").hide();
            jQuery("#podContent").hide();
            jQuery("#podArea #column_list").html("");
            jQuery("#podArea .stickynote").show();
        }
        else {
            jQuery("#podContent").show();
            jQuery("#podArea .stickynote").hide();
            loadPod();
        }
    });
    jQuery(".select-pod").change();
    jQuery("#podBox").jqm();
});

function resetPodForm() {
    jQuery("#is_toplevel").removeAttr("checked");
    jQuery("#pod_label").val("");
    jQuery("#detail_page").val("");
    jQuery("#pre_save_helpers").val("");
    jQuery("#pre_drop_helpers").val("");
    jQuery("#post_save_helpers").val("");
    jQuery("#post_drop_helpers").val("");
    jQuery("#list_pre_save_helpers").html("");
    jQuery("#list_pre_drop_helpers").html("");
    jQuery("#list_post_save_helpers").html("");
    jQuery("#list_post_drop_helpers").html("");
}

function resetForm(fade) {
    if (false !== fade)
        jQuery("#columnBox").hide();
    jQuery("#column_name").val("");
    jQuery("#column_name").removeAttr("disabled");
    jQuery("#column_label").val("");
    jQuery("#column_comment").val("");
    jQuery("#column_type").val("date");
    jQuery("#column_type").removeAttr("disabled");
    jQuery("#column_pickval").val("");
    jQuery("#column_pick_filter").val("");
    jQuery("#column_pick_orderby").val("");
    //jQuery("#column_display_helper").val("");
    jQuery("#column_input_helper").val("");
    jQuery("#column_required").removeAttr("checked");
    jQuery("#column_required").removeAttr("disabled");
    jQuery("#column_unique").removeAttr("checked");
    jQuery("#column_unique").removeAttr("disabled");
    jQuery("#column_multiple").removeAttr("checked");
    jQuery("#column_multiple").removeAttr("disabled");
    jQuery("#column_sister_field_id").html("");
    doDropdown('nopick');
    jQuery(".column-header").html("Add Column");
    add_or_edit = "add";
    if (false !== fade)
        jQuery("#columnBox").fadeIn('fast');
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
        jQuery(".coltype-nopick").hide();
    }
    else {
        jQuery(".coltype-pick-bi").hide();
        jQuery(".coltype-pick").hide();
        jQuery(".coltype-nopick").show();
    }
}

function sisterFields(sister_field_id) {
    var pickval = jQuery("#column_pickval").val();
    jQuery(".coltype-pick-bi").hide();
    jQuery("#column_sister_field_id").html('<option value="">-- Related to --</option>');
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_sister_fields&_wpnonce=<?php echo wp_create_nonce('pods-load_sister_fields'); ?>&datatype="+dt+"&pickval="+pickval,
        success: function(msg) {
            if (!is_error(msg) && "" != msg) {
                var html = '<option value="">-- Related to --</option>';
                var json = eval('('+msg+')');
                var found = false;
                if(json != null) {
                    for (var i = 0; i < json.length; i++) {
                        found = true;
                        var id = json[i].id;
                        var name = json[i].name;
                        html += '<option value="'+id+'">'+name+'</option>';
                    }
                }
                jQuery("#column_sister_field_id").html(html);
                if (!found)
                    return;
                if (0 < sister_field_id)
                    jQuery("#column_sister_field_id option[value='"+sister_field_id+"']").attr("selected", "selected");
                jQuery(".coltype-pick-bi").show();
            }
        }
    });
}

function loadPod() {
    resetForm();
    resetPodForm();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_pod&_wpnonce=<?php echo wp_create_nonce('pods-load_pod'); ?>&id="+dt,
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval('('+msg+')');
                var label = (null == json.label) ? "" : json.label;
                var is_toplevel = parseInt(json.is_toplevel);
                var detail_page = (null == json.detail_page) ? "" : json.detail_page;
                var pre_save_helpers = (null == json.pre_save_helpers) ? "" : json.pre_save_helpers;
                var pre_drop_helpers = (null == json.pre_drop_helpers) ? "" : json.pre_drop_helpers;
                var post_save_helpers = (null == json.post_save_helpers) ? "" : json.post_save_helpers;
                var post_drop_helpers = (null == json.post_drop_helpers) ? "" : json.post_drop_helpers;
                jQuery("#pod_label").val(label);
                if (is_toplevel)
                    jQuery("#is_toplevel").attr("checked", "checked");
                else
                    jQuery("#is_toplevel").removeAttr("checked");
                jQuery("#detail_page").val(detail_page);
                jQuery("#list_pre_save_helpers").html("");
                jQuery("#list_pre_drop_helpers").html("");
                jQuery("#list_post_save_helpers").html("");
                jQuery("#list_post_drop_helpers").html("");

                // Build the column list
                var html = "";
                var fields = json.fields;
                for (var field in fields) {
                    var id = fields[field].id;
                    var name = fields[field].name;
                    var coltype = fields[field].coltype;
                    var pickval = fields[field].pickval;
                    if ("" != pickval && null != pickval && "NULL" != pickval) {
                        coltype += " "+pickval;
                    }
                    html += '<li class="col'+id+'">';
                    html += '<div class="btn dragme"></div> ';
                    html += '<div class="btn editme"></div> ';

                    // Mark required fields
                    var required = parseInt(fields[field].required);
                    required = (1 == required) ? ' <span class="red">*</span>' : "";

                    // Default columns
                    if ("name" != name) {
                        html += '<div class="btn dropme"></div> ';
                    }
                    html += name+" ("+coltype+")"+required+"</li>";
                }
                jQuery("#podArea #column_list").html('<ul class="sortable">'+html+'</ul>');
                jQuery(".sortable").sortable("destroy");
                jQuery(".sortable").sortable({axis: "y", handle: ".dragme"});

                jQuery("#podArea #column_list .btn").click(function() {
                    var field_id = jQuery(this).parent().attr("class").substr(3);
                    var classname = jQuery(this).attr("class").substr(4);
                    if ("dropme" == classname) {
                        dropColumn(field_id);
                    }
                    else if ("editme" == classname) {
                        loadColumn(field_id);
                    }
                });

                var html = "";
                if ("" != pre_save_helpers) {
                    pre_save_helpers = pre_save_helpers.split(",");
                    for (var i = 0; i < pre_save_helpers.length; i++) {
                        var val = pre_save_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a href="#" onclick="jQuery(this).parent().remove();return false;">drop</a>)</div>';
                    }
                    jQuery("#list_pre_save_helpers").html(html);
                }

                var html = "";
                if ("" != pre_drop_helpers) {
                    pre_drop_helpers = pre_drop_helpers.split(",");
                    for (var i in pre_drop_helpers) {
                        var val = pre_drop_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a href="#" onclick="jQuery(this).parent().remove();return false;">drop</a>)</div>';
                    }
                    jQuery("#list_pre_drop_helpers").html(html);
                }

                var html = "";
                if ("" != post_save_helpers) {
                    post_save_helpers = post_save_helpers.split(",");
                    for (var i in post_save_helpers) {
                        var val = post_save_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a href="#" onclick="jQuery(this).parent().remove();return false;">drop</a>)</div>';
                    }
                    jQuery("#list_post_save_helpers").html(html);
                }

                var html = "";
                if ("" != post_drop_helpers) {
                    post_drop_helpers = post_drop_helpers.split(",");
                    for (var i in post_drop_helpers) {
                        var val = post_drop_helpers[i];
                        html += '<div class="helper" id="'+val+'">'+val+' (<a href="#" onclick="jQuery(this).parent().remove();return false;">drop</a>)</div>';
                    }
                    jQuery("#list_post_drop_helpers").html(html);
                }
            }
        }
    });
}

function addPod() {
    var name = jQuery("#new_pod").val();
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_pod&_wpnonce=<?php echo wp_create_nonce('pods-save_pod'); ?>&name="+name+"&return_pod=1",
        success: function(msg) {
            if (!is_error(msg)) {
                var json = eval('('+msg+')');
                var id = json.id;
                var html = '<option value="'+json.id+'">'+json.name+'</option>';
                jQuery(".select-pod").append(html);
                jQuery(".select-pod > option[value='"+json.id+"']").attr("selected", "selected");
                jQuery("#podBox #new_pod").val("");
                jQuery(".select-pod").change();
                jQuery("#podBox").jqmHide();
                var pod_selection_html = '<option value="'+json.name+'" class="pod-selection">'+json.name+'</option>';
                jQuery("#column_pickval .pod-selection:last").after(pod_selection_html);
            }
        }
    });
}

function editPod() {
    var label = jQuery("#pod_label").val();
    var is_toplevel = jQuery("#is_toplevel").is(":checked") ? 1 : 0;
    var detail_page = jQuery("#detail_page").val();
    var pre_save_helpers = "";
    jQuery("#list_pre_save_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        pre_save_helpers += ("" == pre_save_helpers) ? new_helper : "," + new_helper;
    });
    var pre_drop_helpers = "";
    jQuery("#list_pre_drop_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        pre_drop_helpers += ("" == pre_drop_helpers) ? new_helper : "," + new_helper;
    });
    var post_save_helpers = "";
    jQuery("#list_post_save_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        post_save_helpers += ("" == post_save_helpers) ? new_helper : "," + new_helper;
    });
    var post_drop_helpers = "";
    jQuery("#list_post_drop_helpers .helper").each(function() {
        var new_helper = jQuery(this).attr("id");
        post_drop_helpers += ("" == post_drop_helpers) ? new_helper : "," + new_helper;
    });

    var order = "";
    jQuery("ul.sortable li").each(function() {
        order += jQuery(this).attr("class").substr(3) + ",";
    });
    order = order.slice(0, -1);

    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=save_pod&_wpnonce=<?php echo wp_create_nonce('pods-save_pod'); ?>&id="+dt+"&label="+label+"&is_toplevel="+is_toplevel+"&detail_page="+detail_page+"&pre_save_helpers="+pre_save_helpers+"&pre_drop_helpers="+pre_drop_helpers+"&post_save_helpers="+post_save_helpers+"&post_drop_helpers="+post_drop_helpers+"&order="+order,
        success: function(msg) {
            if (!is_error(msg)) {
                alert("Success!");
            }
        }
    });
}

function dropPod() {
    if (confirm("Do you really want to drop this pod and its contents?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_pod&_wpnonce=<?php echo wp_create_nonce('pods-drop_pod'); ?>&id="+dt+"&name="+dtname,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery(".select-pod > option[value='"+dt+"']").remove();
                    jQuery(".select-pod").change();
                    jQuery("#column_pickval > option[value='"+dtname+"']").remove();
                }
            }
        });
    }
}

function loadColumn(id) {
    jQuery("#columnBox").hide();
    resetForm(false);
    column_id = id;
    add_or_edit = "edit";
    jQuery(".column-header").html("Edit Column");
    jQuery.ajax({
        type: "post",
        url: api_url,
        data: "action=load_column&_wpnonce=<?php echo wp_create_nonce('pods-load_column'); ?>&id="+column_id,
        success: function(msg) {
            var json = eval('('+msg+')');
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
            //jQuery("#column_display_helper").val(display_helper);
            jQuery("#column_input_helper").val(input_helper);
            if (required)
                jQuery("#column_required").attr("checked", "checked");
            else
                jQuery("#column_required").removeAttr("checked");
            if (unique)
                jQuery("#column_unique").attr("checked", "checked");
            else
                jQuery("#column_unique").removeAttr("checked");
            if (multiple)
                jQuery("#column_multiple").attr("checked", "checked");
            else
                jQuery("#column_multiple").removeAttr("checked");

            if ("name" == name) {
                jQuery("#column_name").attr("disabled", "disabled");
                jQuery("#column_type").attr("disabled", "disabled");
                jQuery("#column_required").attr("disabled", "disabled");
            }
            var found = null;
            if (0 != parseInt(sister_field_id))
                sisterFields(sister_field_id);
            if ("pick" == coltype)
                doDropdown(coltype);
            jQuery("#columnBox").fadeIn('fast');
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
    //var display_helper = jQuery("#column_display_helper").val();
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
        url: api_url,
        data: "action=save_column&_wpnonce=<?php echo wp_create_nonce('pods-save_column'); ?>&datatype="+dt+"&dtname="+dtname+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&pick_filter="+pick_filter+"&pick_orderby="+pick_orderby+"&sister_field_id="+sister_field_id+"&input_helper="+input_helper+"&required="+required+"&unique="+unique+"&multiple="+multiple,//&display_helper="+display_helper+"
        success: function(msg) {
            if (!is_error(msg)) {
                loadPod();
            }
        }
    });
}

function editColumn(id) {
    var name = jQuery("#column_name").val();
    var label = jQuery("#column_label").val();
    var comment = jQuery("#column_comment").val();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var pick_filter = jQuery("#column_pick_filter").val();
    var pick_orderby = jQuery("#column_pick_orderby").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    //var display_helper = jQuery("#column_display_helper").val();
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
        url: api_url,
        data: "action=save_column&_wpnonce=<?php echo wp_create_nonce('pods-save_column'); ?>&id="+id+"&datatype="+dt+"&dtname="+dtname+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&pick_filter="+pick_filter+"&pick_orderby="+pick_orderby+"&sister_field_id="+sister_field_id+"&input_helper="+input_helper+"&required="+required+"&unique="+unique+"&multiple="+multiple,//&display_helper="+display_helper+"
        success: function(msg) {
            if (!is_error(msg)) {
                loadPod();
            }
        }
    });
}

function dropColumn(id) {
    if (confirm("Do you really want to drop this column?")) {
        jQuery.ajax({
            type: "post",
            url: api_url,
            data: "action=drop_column&_wpnonce=<?php echo wp_create_nonce('pods-drop_column'); ?>&id="+id+"&dtname="+dtname,
            success: function(msg) {
                if (!is_error(msg)) {
                    jQuery(".col"+id).remove();
                    resetForm();
                }
            }
        });
    }
}

function addPodHelper(div_id, select_id) {
    var val = jQuery("#"+select_id).val();
    if ("" != val) {
        var valexists = jQuery("div.helper#"+val).html();
        if (null == valexists) {
            var html = '<div class="helper" id="'+val+'">'+val+' (<a href="#" onclick="jQuery(this).parent().remove();return false;">drop</a>)</div>';
            jQuery("#"+div_id).append(html);
            jQuery("#"+select_id).val('');
        }
    }
}
</script>

<!-- Pod popups -->

<div id="podBox" class="jqmWindow">
    <input type="text" id="new_pod" maxlength="32" />
    <input type="button" class="button" onclick="addPod()" value="Add Pod" />
    <p>Please only use lowercase letters and underscores.</p>
</div>

<!-- Pod HTML -->

<div>
    <select class="area-select select-pod">
        <option value="">-- Choose a Pod --</option>
<?php
if (isset($datatypes)) {
    foreach ($datatypes as $key => $val) {
?>
        <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
<?php
    }
}
?>
    </select>
    <input type="button" class="button-primary" onclick="jQuery('#podBox').jqmShow()" value="Add new pod" />
</div>

<div id="pod-area-left">
    <table id="podContent" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="2">
                <div id="column_list"></div>
            </td>
        </tr>
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
            <td>Pod Label</td>
            <td><input type="text" id="pod_label" value="" maxlength="128" /></td>
        </tr>
        <tr>
            <td>Detail Page</td>
            <td><input type="text" id="detail_page" maxlength="128" /></td>
        </tr>
        <tr>
            <td>Pre-save Helpers</td>
            <td>
                <select id="pre_save_helpers">
                    <option value="">-- Select --</option>
<?php
if (isset($helper_types['pre_save'])) {
    foreach ($helper_types['pre_save'] as $key => $helper_name) {
?>
                    <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                </select>
                <input type="button" class="button" value="Add" onclick="addPodHelper('list_pre_save_helpers', 'pre_save_helpers')" />
                <div id="list_pre_save_helpers"></div>
            </td>
        </tr>
        <tr>
            <td>Pre-drop Helpers</td>
            <td>
                <select id="pre_drop_helpers">
                    <option value="">-- Select --</option>
<?php
if (isset($helper_types['pre_drop'])) {
    foreach ($helper_types['pre_drop'] as $key => $helper_name) {
?>
                    <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                </select>
                <input type="button" class="button" value="Add" onclick="addPodHelper('list_pre_drop_helpers', 'pre_drop_helpers')" />
                <div id="list_pre_drop_helpers"></div>
            </td>
        </tr>
        <tr>
            <td>Post-save Helpers</td>
            <td>
                <select id="post_save_helpers">
                    <option value="">-- Select --</option>
<?php
if (isset($helper_types['post_save'])) {
    foreach ($helper_types['post_save'] as $key => $helper_name) {
?>
                    <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                </select>
                <input type="button" class="button" value="Add" onclick="addPodHelper('list_post_save_helpers', 'post_save_helpers')" />
                <div id="list_post_save_helpers"></div>
            </td>
        </tr>
        <tr>
            <td>Post-drop Helpers</td>
            <td>
                <select id="post_drop_helpers">
                    <option value="">-- Select --</option>
<?php
if (isset($helper_types['post_drop'])) {
    foreach ($helper_types['post_drop'] as $key => $helper_name) {
?>
                    <option value="<?php echo $helper_name; ?>"><?php echo $helper_name; ?></option>
<?php
    }
}
?>
                </select>
                <input type="button" class="button" value="Add" onclick="addPodHelper('list_post_drop_helpers', 'post_drop_helpers')" />
                <div id="list_post_drop_helpers"></div>
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
            <td>Machine Name</td>
            <td><input type="text" id="column_name" value="" maxlength="32" /></td>
        </tr>
        <tr>
            <td>Label</td>
            <td><input type="text" id="column_label" value="" maxlength="128" /></td>
        </tr>
        <tr>
            <td>Column Type</td>
            <td>
                <select id="column_type" onchange="doDropdown(this.value)">
<?php
$column_types = array(
    'date' => 'Date',
    'num' => 'Number',
    'bool' => 'Boolean',
    'txt' => 'Single line Text',
    'desc' => 'Paragraph Text',
    'code' => 'Code',
    'file' => 'File Upload',
    'slug' => 'Permalink',
    'pick' => 'Relationship (pick)'
);
$column_types = apply_filters('pods_column_types', $column_types);
foreach ($column_types as $type => $label)
{
?>
                    <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
<?php
}
?>
                </select>
            </td>
        </tr>
        <tr class="coltype-pick">
            <td>Related to</td>
            <td>
                <select id="column_pickval" onchange="sisterFields()">
                    <option value="" style="font-weight:bold; font-style:italic" class="pod-selection">-- Pod --</option>
<?php
// Get all pod names
$result = pod_query("SELECT name FROM @wp_pod_types ORDER BY name");
while ($row = mysql_fetch_array($result)) {
?>
                    <option value="<?php echo $row['name']; ?>" class="pod-selection"><?php echo $row['name']; ?></option>
<?php
}
?>
                    <option value="" style="font-weight:bold; font-style:italic">-- WordPress --</option>
                    <option value="wp_taxonomy">WP Taxonomy</option>
                    <option value="wp_page">WP Page</option>
                    <option value="wp_post">WP Post</option>
                    <option value="wp_user">WP User</option>
                </select>
            </td>
        </tr>
        <tr class="coltype-pick-bi">
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
                <div><label for="column_required"><input type="checkbox" id="column_required" /> required</label><br /></div>
                <div class="coltype-nopick"><label for="column_unique"><input type="checkbox" id="column_unique" /> unique</label><br /></div>
                <div class="coltype-pick"><label for="column_multiple"><input type="checkbox" id="column_multiple" /> multi-select pick</label></div>
            </td>
        </tr><!-- NOT BEING USED RIGHT NOW, LET'S HIDE IT
        <tr>
            <td>Display Helper</td>
            <td>
                <select id="column_display_helper">
                    <option value="">-- Select --</option>
<?php
// Get all display helpers
if (isset($helper_types['display'])) {
    foreach ($helper_types['display'] as $key => $name) {
?>
                    <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
<?php
    }
}
?>
                </select>
            </td>
        </tr>-->
        <tr>
            <td>Input Helper</td>
            <td>
                <select id="column_input_helper">
                    <option value="">-- Select --</option>
<?php
// Get all display helpers
if (isset($helper_types['input'])) {
    foreach ($helper_types['input'] as $key => $name) {
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
                <input type="text" id="column_comment" value="" maxlength="255" />
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
<div class="clear"></div>

<div class="stickynote">
    <div><strong>A pod is a named group of input fields.</strong> This area will allow you to add new pods and edit existing ones.</div>
    <div style="margin-top:10px">To get started, select an existing pod from the dropdown or click the blue button to add a new one.</div>
</div>