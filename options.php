<?php
// Get all datatypes
$result = pod_query("SELECT id, name FROM {$table_prefix}pod_types ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}

// Get all pages
$result = pod_query("SELECT id, uri FROM {$table_prefix}pod_pages ORDER BY uri");
while ($row = mysql_fetch_assoc($result))
{
    $pages[$row['id']] = $row['uri'];
}

// Get all helpers
$result = pod_query("SELECT id, name, helper_type FROM {$table_prefix}pod_helpers ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $helpers[$row['id']] = $row;
    $helper_types[$row['helper_type']][] = $row['name'];
}

// Get all available WP roles
$user_roles = get_option($table_prefix . 'user_roles');
?>

<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
var datatype;
var column_id;
var add_or_edit;
var helper_id;
var page_id;
var auth = '<?php echo md5(AUTH_KEY); ?>';

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });

    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        var activeArea = jQuery(this).attr("rel");
        jQuery(".area").hide();
        jQuery("#"+activeArea).show();
    });

    jQuery("#podArea .tab").click(function() {
        jQuery("#podArea .tab").removeClass("active");
        datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery("#podArea .idle").show();
        loadPod();
    });

    jQuery("#pageArea .tab").click(function() {
        jQuery("#pageArea .tab").removeClass("active");
        page_id = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery("#pageArea .idle").show();
        loadPage();
    });

    jQuery("#helperArea .editme").click(function() {
        helper_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#helper_form").html();
        jQuery("#helperArea .hform").html("");
        jQuery("#helperArea .htr").hide();
        jQuery("#helperArea #htr"+helper_id).show();
        jQuery("#hform"+helper_id).html(theform);
        loadHelper();
    });

    jQuery("#helperArea .dropme").click(function() {
        helper_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#hform"+helper_id).html();
        dropHelper();
    });

    jQuery("#pageArea .editme").click(function() {
        page_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#page_form").html();
        jQuery("#pageArea .pform").html("");
        jQuery("#pageArea .ptr").hide();
        jQuery("#pageArea #ptr"+page_id).show();
        jQuery("#pform"+page_id).html(theform);
        loadPage();
    });

    jQuery("#pageArea .dropme").click(function() {
        page_id = jQuery(this).parent("td").parent("tr").attr("id").substr(3);
        var theform = jQuery("#pform"+page_id).html();
        dropPage();
    });

    // Remember the tab selection
    var thetab = window.location.href.split("#")[1];
    thetab = ("undefined" == typeof(thetab)) ? "welcome" : thetab;
    jQuery(".navTab[@rel="+thetab+"Area]").click();

    jQuery("#podBox").jqm();
    jQuery("#columnBox").jqm();
    jQuery("#pageBox").jqm();
    jQuery("#helperBox").jqm();
});

function colorFade(area, id) {
    var bgcolor = jQuery("#"+area+"Area #row"+id).css("background-color");
    jQuery("#"+area+"Area #row"+id).css("background-color", "#88FFC0");
    jQuery("#"+area+"Area #row"+id).animate({backgroundColor:bgcolor}, 1000);
}

function resetForm() {
    jQuery("#column_name").val("");
    jQuery("#column_name").attr("disabled", false);
    jQuery("#column_label").val("");
    jQuery("#column_type").val("");
    jQuery("#column_type").attr("disabled", false);
    jQuery("#column_pickval").val("");
    jQuery("#column_required").attr("checked", 0);
    jQuery("#column_required").attr("disabled", false);
    jQuery("#column_unique").attr("checked", 0);
    jQuery("#column_unique").attr("disabled", false);
    jQuery("#column_multiple").attr("checked", 0);
    jQuery("#column_multiple").attr("disabled", false);
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

function addPodHelper(div_id, select_id) {
    var val = jQuery("#"+select_id).val();
    var html = '<div class="helper" id="'+val+'">'+val+' (<a onclick="jQuery(this).parent().remove()">drop</a>)</div>';
    jQuery("#"+div_id).append(html);
}

function sisterFields(sister_field_id) {
    var pickval = jQuery("#column_pickval").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/sister_fields.php",
        data: "auth="+auth+"&datatype="+datatype+"&pickval="+pickval,
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
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&id="+datatype,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var pod_type = eval("("+msg+")");
                var name = (null == pod_type.name) ? "" : pod_type.name;
                var label = (null == pod_type.label) ? "" : pod_type.label;
                var is_toplevel = parseInt(pod_type.is_toplevel);
                var list_filters = (null == pod_type.list_filters) ? "" : pod_type.list_filters;
                var tpl_detail = (null == pod_type.tpl_detail) ? "" : pod_type.tpl_detail;
                var tpl_list = (null == pod_type.tpl_list) ? "" : pod_type.tpl_list;
                var before_helpers = (null == pod_type.before_helpers) ? "" : pod_type.before_helpers;
                var after_helpers = (null == pod_type.after_helpers) ? "" : pod_type.after_helpers;
                jQuery("#pod_name").html(name);
                jQuery("#pod_label").val(label);
                jQuery("#is_toplevel").attr("checked", is_toplevel);
                jQuery("#list_filters").val(list_filters);
                jQuery("#tpl_detail").val(tpl_detail);
                jQuery("#tpl_list").val(tpl_list);
                jQuery("#list_before_helpers").html("");
                jQuery("#list_after_helpers").html("");

                // Build the column list
                var html = "";
                var fields = pod_type.fields;
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
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=pod&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<div class="tab t'+id+'">'+name+'</div>';
                jQuery("#podArea .tabs").append(html);
                jQuery("#podBox").jqmHide();
                jQuery("#podArea .t"+id).click(function() {
                    jQuery("#podArea .tab").removeClass("active");
                    datatype = jQuery(this).attr("class").split(" ")[1].substr(1);
                    jQuery(this).addClass("active");
                    jQuery("#podArea .idle").show();
                    jQuery("#podBox #new_pod").val("");
                    loadPod();
                });
                jQuery("#podArea .t"+id).click();
            }
        }
    });
}

function editPod() {
    var label = jQuery("#pod_label").val();
    var is_toplevel = (true == jQuery("#is_toplevel").is(":checked")) ? 1 : 0;
    var list_filters = jQuery("#list_filters").val();
    var tpl_detail = jQuery("#tpl_detail").val();
    var tpl_list = jQuery("#tpl_list").val();
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
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&datatype="+datatype+"&label="+label+"&is_toplevel="+is_toplevel+"&list_filters="+encodeURIComponent(list_filters)+"&tpl_detail="+encodeURIComponent(tpl_detail)+"&tpl_list="+encodeURIComponent(tpl_list)+"&before_helpers="+before_helpers+"&after_helpers="+after_helpers,
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
        var dtname = jQuery("#pod_name").html();
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&pod="+datatype+"&dtname="+dtname,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#pod_name").html("Choose a Pod");
                    jQuery("#podArea #column_list").html('Need some help? Check out the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> to get started.');
                    jQuery("#podArea .t"+datatype).remove();
                    jQuery("#podArea .idle").hide();
                }
            }
        });
    }
}

function loadColumn(col) {
    resetForm();

    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&col="+col,
        success: function(msg) {
            var col_data = eval("("+msg+")");
            var name = (null == col_data.name) ? "" : col_data.name;
            var label = (null == col_data.label) ? "" : col_data.label;
            var helper = (null == col_data.helper) ? "" : col_data.helper;
            var comment = (null == col_data.comment) ? "" : col_data.comment;
            var coltype = (null == col_data.coltype) ? "" : col_data.coltype;
            var pickval = (null == col_data.pickval) ? "" : col_data.pickval;
            var sister_field_id = (null == col_data.sister_field_id) ? "" : col_data.sister_field_id;
            var required = parseInt(col_data.required);
            var unique = parseInt(col_data.unique);
            var multiple = parseInt(col_data.multiple);
            jQuery("#column_name").val(name);
            jQuery("#column_label").val(label);
            jQuery("#column_helper").val(helper);
            jQuery("#column_comment").val(comment);
            jQuery("#column_type").val(coltype);
            jQuery("#column_pickval").val(pickval);
            jQuery("#column_sister_field_id").hide();
            jQuery("#column_required").attr("checked", required);
            jQuery("#column_unique").attr("checked", unique);
            jQuery("#column_multiple").attr("checked", multiple);
            jQuery("#column_pickval").hide();
            if ("name" == name) {
                jQuery("#column_name").attr("disabled", true);
                jQuery("#column_type").attr("disabled", true);
                jQuery("#column_required").attr("disabled", true);
            }
            if ("pick" == coltype) {
                jQuery("#column_pickval").show();
            }
            if ("0" != sister_field_id) {
                sisterFields(sister_field_id);
            }
            column_id = col;
            add_or_edit = "edit";
            jQuery("#columnBox").jqmShow();
        }
    });
}

function addColumn() {
    var name = jQuery("#column_name").val();
    var label = jQuery("#column_label").val();
    var helper = jQuery("#column_helper").val();
    var comment = jQuery("#column_comment").val();
    var dtname = jQuery("#pod_name").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    var unique = (true == jQuery("#column_unique").is(":checked")) ? 1 : 0;
    var multiple = (true == jQuery("#column_multiple").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&datatype="+datatype+"&dtname="+dtname+"&name="+name+"&label="+label+"&helper="+helper+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required+"&unique="+unique+"&multiple="+multiple,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#columnBox").jqmHide();
                loadPod();
            }
        }
    });
}

function moveColumn(col, dir) {
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=move&datatype="+datatype+"&col="+col+"&dir="+dir,
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
    var helper = jQuery("#column_helper").val();
    var comment = jQuery("#column_comment").val();
    var dtname = jQuery(".tab.active").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    var unique = (true == jQuery("#column_unique").is(":checked")) ? 1 : 0;
    var multiple = (true == jQuery("#column_multiple").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edit&datatype="+datatype+"&dtname="+dtname+"&field_id="+col+"&name="+name+"&label="+label+"&helper="+helper+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required+"&unique="+unique+"&multiple="+multiple,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#columnBox").jqmHide();
                loadPod();
            }
        }
    });
}

function dropColumn(col) {
    if (confirm("Do you really want to drop this column?")) {
        var dtname = jQuery("#pod_name").html();
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&col="+col+"&dtname="+dtname,
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
    var title = jQuery("#pform"+page_id+" #page_title").val();
    var content = jQuery("#pform"+page_id+" #page_content").val();
    var template = jQuery("#pform"+page_id+" #page_template").val();
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

function loadHelper() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&helper_id="+helper_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var helper_data = eval("("+msg+")");
                var phpcode = (null == helper_data.phpcode) ? "" : helper_data.phpcode;
                var helper_type = (null == helper_data.helper_type) ? "display" : helper_data.helper_type;
                jQuery("#helper_content").val(phpcode);
                jQuery("#helper_type").val(helper_type);
            }
        }
    });
}

function addHelper() {
    var name = jQuery("#new_helper").val();
    var helper_type = jQuery("#helper_type").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=helper&name="+name+"&helper_type="+helper_type,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var rand = Math.round(Math.random()*9999);
                window.location="?page=pods&"+rand+"#helper";
            }
        }
    });
}

function editHelper() {
    var content = jQuery("#hform"+helper_id+" #helper_content").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edithelper&helper_id="+helper_id+"&phpcode="+encodeURIComponent(content),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                colorFade('helper', helper_id);
            }
        }
    });
}

function dropHelper() {
    if (confirm("Do you really want to drop this helper?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&helper="+helper_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#helperArea #htr"+helper_id).remove();
                    jQuery("#helperArea tr#row"+helper_id).css("background", "red");
                    jQuery("#helperArea tr#row"+helper_id).fadeOut("slow");
                }
            }
        });
    }
}

function editRoles() {
    var data = new Array();

    var i = 0;
    jQuery("#roleArea .form").each(function() {
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
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "action=editroles&auth="+auth+"&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Success!");
            }
        }
    });
    return false;
}

function resetDB() {
    if (confirm("This will completely remove Pods from the database. Are you sure?")) {
        if (confirm("Did you already make a database backup?")) {
            if (confirm("There's no undo. Is that your final answer?")) {
                jQuery.ajax({
                    type: "post",
                    url: "<?php echo $pods_url; ?>/uninstall.php",
                    data: "auth="+auth,
                    success: function(msg) {
                        if ("Error" == msg.substr(0, 5)) {
                            alert(msg);
                        }
                        else {
                            window.location="";
                        }
                    }
                });
            }
        }
    }
}
</script>

<!--
==================================================
Begin popups
==================================================
-->
<div id="podBox" class="jqmWindow">
    <input type="text" id="new_pod" />
    <input type="button" class="button" onclick="addPod()" value="Add Pod" />
    <p>Please use lowercase letters, dashes or underscores only.</p>
</div>

<div id="columnBox" class="jqmWindow">
    <input type="hidden" id="add_or_edit" value="" />
    <div class="leftside">Name</div>
    <div class="rightside">
        <input type="text" id="column_name" value="" />
        <input type="button" class="button" onclick="addOrEditColumn()" value="Save column" />
    </div>

    <div class="leftside">Label</div>
    <div class="rightside">
        <input type="text" id="column_label" value="" />
        <select id="column_helper">
            <option value="">-- Helper --</option>
<?php
// Get all display helpers
$result = pod_query("SELECT name FROM {$table_prefix}pod_helpers WHERE helper_type = 'display'");
while ($row = mysql_fetch_assoc($result))
{
?>
            <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
<?php
}
?>
        </select>
    </div>

    <div class="leftside">Column Type</div>
    <div class="rightside">
        <select id="column_type" onchange="doDropdown(this.value)">
            <option value="date">date</option>
            <option value="num">number</option>
            <option value="bool">boolean (true, false)</option>
            <option value="txt">text (title, caption, email, phone, url)</option>
            <option value="desc">desc (summary, fulltext)</option>
            <option value="code">code (no WYSIWYG editor)</option>
            <option value="file">file (document, media)</option>
            <option value="slug">slug (permalink)</option>
            <option value="pick">pick</option>
        </select>
        <select id="column_pickval" class="hidden" onchange="sisterFields()">
            <option value="" style="font-weight:bold; font-style:italic">-- Pods --</option>
<?php
// Get pods, including country and state
$result = pod_query("SHOW TABLES LIKE '{$table_prefix}pod_tbl_%'");
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
    {$table_prefix}term_taxonomy tx
INNER JOIN
    {$table_prefix}terms t ON t.term_id = tx.parent
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
        <select id="column_sister_field_id" class="hidden"></select>
    </div>

    <div class="leftside">Options</div>
    <div class="rightside">
        <input type="checkbox" id="column_required" /> required &nbsp;
        <input type="checkbox" id="column_unique" /> unique &nbsp;
        <input type="checkbox" id="column_multiple" /> multiple
    </div>

    <div class="leftside">Comment</div>
    <div class="rightside">
        <input type="text" id="column_comment" value="" />
    </div>

    <div class="clear">
        <span class="red">*CAUTION*</span> changing column types can result in data loss!
    </div>
</div>

<div id="pageBox" class="jqmWindow">
    <input type="text" id="new_page" style="width:280px" />
    <input type="button" class="button" onclick="addPage()" value="Add Page" />
    <div>Ex: <strong>/resources/latest/</strong> or <strong>/events/*</strong></div>
</div>

<div id="helperBox" class="jqmWindow">
    <input type="text" id="new_helper" style="width:280px" />
    <input type="button" class="button" onclick="addHelper()" value="Add Helper" />
    <select id="helper_type">
        <option value="display">Display (pre-output hook)</option>
        <option value="before">Before (pre-save hook)</option>
        <option value="after">After (post-save hook)</option>
    </select>
    <div>Ex: <strong>format_date</strong> or <strong>mp3_player</strong></div>
</div>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
    <div class="navTab active" rel="welcomeArea"><a href="#welcome">Welcome</a></div>
<?php
if (pods_access('manage_pods'))
{
?>
    <div class="navTab" rel="podArea"><a href="#pod">Pods</a></div>
<?php
}
if (pods_access('manage_podpages'))
{
?>
    <div class="navTab" rel="pageArea"><a href="#page">PodPages</a></div>
<?php
}
if (pods_access('manage_helpers'))
{
?>
    <div class="navTab" rel="helperArea"><a href="#helper">Helpers</a></div>
<?php
}
if (pods_access('manage_roles'))
{
?>
    <div class="navTab" rel="roleArea"><a href="#role">Roles</a></div>
<?php
}
if (pods_access('manage_settings'))
{
?>
    <div class="navTab" rel="settingsArea"><a href="#settings">Settings</a></div>
<?php
}
?>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin welcome area
==================================================
-->
<div id="welcomeArea" class="area hidden">
    <div id="logo">
        <img src="<?php echo $pods_url; ?>/images/header-logo.png" alt="Pods" />
    </div>
    <h2 align="center">Thanks for using the Pods CMS plugin.</h2>
    <p align="center">See the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> and <a href="http://pods.uproot.us/forum" target="_blank">Forum</a> to get started.</p>
</div>

<!--
==================================================
Begin pod area
==================================================
-->
<div id="podArea" class="area hidden">
    <div class="tabs">
        <input type="button" class="button" onclick="jQuery('#podBox').jqmShow()" value="Add new pod" />
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
    </div>
    <div class="rightside">
        <h2 class="title" id="pod_name">Choose a Pod</h2>
        <p id="column_list">Need some help? Check out the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> to get started.</p>
        <div class="idle hidden">
            <p>
                <input type="button" class="button" onclick="add_or_edit='add'; resetForm(); jQuery('#columnBox').jqmShow()" value="Add a column" />
                <input type="button" class="button" onclick="editPod()" value="Save changes" /> or
                <a href="javascript:;" onclick="dropPod()">drop pod</a>
            </p>

            <p class="extras" onclick="jQuery('#tpl_detail').toggle(); jQuery(this).toggleClass('open')">Detail Template</p>
            <textarea id="tpl_detail" class="hidden"></textarea>

            <p class="extras" onclick="jQuery('#tpl_list').toggle(); jQuery(this).toggleClass('open')">List Template</p>
            <textarea id="tpl_list" class="hidden"></textarea>

            <p class="extras" onclick="jQuery('#pod_settings').toggle(); jQuery(this).toggleClass('open')">Pod Settings</p>
            <div id="pod_settings" class="hidden">
                <input type="text" id="pod_label" value="" />
                <input type="checkbox" id="is_toplevel" /> Top-level menu? (if so, add a label)
                <p>List Filters:<br /><input type="text" id="list_filters" /></p>
                <p>
                    Pre-save Helpers:<br />
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
                </p>
                <p>
                    Post-save Helpers:<br />
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
                </p>
            </div>
        </div>
    </div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin page area
==================================================
-->
<div id="pageArea" class="area hidden">
    <div style="float:left; width:50%">
        <input type="button" class="button" onclick="jQuery('#pageBox').jqmShow()" value="Add new page" />
    </div>
    <div id="filterForm" style="float:left; width:50%; text-align:right">
        Filters coming soon!
    </div>
    <div class="clear"><!--clear--></div>

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

<!--
==================================================
Begin helper area
==================================================
-->
<div id="helperArea" class="area hidden">
    <div style="float:left; width:50%">
        <input type="button" class="button" onclick="jQuery('#helperBox').jqmShow()" value="Add new helper" />
    </div>
    <div id="filterForm" style="float:left; width:50%; text-align:right">
        Filters coming soon!
    </div>
    <div class="clear"><!--clear--></div>

    <table id="browseTable" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th>Name</th>
            <th>Helper Type</th>
            <th></th>
        </tr>
<?php
if (isset($helpers))
{
    $zebra = '';
    foreach ($helpers as $id => $val)
    {
        $id = $val['id'];
        $name = $val['name'];
        $helper_type = $val['helper_type'];
        $zebra = ('' == $zebra) ? ' class="zebra"' : '';
?>
        <tr id="row<?php echo $id; ?>"<?php echo $zebra; ?>>
            <td width="20">
                <div class="btn editme"></div>
            </td>
            <td><?php echo $name; ?></td>
            <td><?php echo $helper_type; ?></td>
            <td width="20"><div class="btn dropme"></div></td>
        </tr>
        <tr id="htr<?php echo $id; ?>" class="htr hidden">
            <td id="hform<?php echo $id; ?>" class="hform" colspan="4"></td>
        </tr>
<?php
    }
}
?>
    </table>

    <div id="helper_form" class="hidden">
        <textarea id="helper_content"></textarea>
        <input type="button" class="button" onclick="editHelper()" value="Save changes" /> or <a href="javascript:;" onclick="jQuery('.htr').hide()">close</a>
    </div>
</div>

<!--
==================================================
Begin role area
==================================================
-->
<div id="roleArea" class="area hidden">
    <div class="tips">Use the Role Manager plugin to add new roles. Admins have total access.</div>
<?php
$all_privs = array(
    array('name' => 'manage_pods', 'label' => 'Manage Pods'),
    array('name' => 'manage_podpages', 'label' => 'Manage PodPages'),
    array('name' => 'manage_helpers', 'label' => 'Manage Helpers'),
    array('name' => 'manage_settings', 'label' => 'Manage Settings'),
    array('name' => 'manage_content', 'label' => 'Manage All Content'),
    array('name' => 'manage_roles', 'label' => 'Manage Roles'),
    array('name' => 'manage_menu', 'label' => 'Manage Menu')
);

foreach ($datatypes as $id => $dtname)
{
    $all_privs[] = array('name' => "pod_$dtname", 'label' => "Access: $dtname");
}

if (isset($user_roles))
{
    foreach ($user_roles as $role => $junk)
    {
        if ('administrator' != $role)
        {
?>
    <div style="float:left; width:32%; margin:0 5px 5px 0">
        <h4><?php echo $role; ?></h4>
        <div class="form pick <?php echo $role; ?>">
<?php
            foreach ($all_privs as $priv)
            {
                $active = '';
                $pods_role = $pods_roles[$role];
                if (isset($pods_role) && false !== array_search($priv['name'], $pods_role))
                {
                    $active = ' active';
                }
?>
            <div class="option<?php echo $active; ?>" value="<?php echo $priv['name']; ?>"><?php echo $priv['label']; ?></div>
<?php
            }
?>
        </div>
    </div>
<?php
        }
    }
}
?>
    <div class="clear"><!--clear--></div>
    <input type="button" class="button" onclick="editRoles()" value="Save roles" />
</div>

<!--
==================================================
Begin settings area
==================================================
-->
<div id="settingsArea" class="area hidden">
    <div class="tips">*WARNING* This cannot be undone. Please backup your database!</div>
    <input type="button" class="button" onclick="resetDB()" value="Reset database" />
</div>

