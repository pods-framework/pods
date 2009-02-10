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

// Get all widgets
$result = pod_query("SELECT id, name FROM {$table_prefix}pod_widgets ORDER BY name");
while ($row = mysql_fetch_assoc($result))
{
    $widgets[$row['id']] = $row['name'];
}

// Get all available WP roles
$user_roles = get_option('wp_user_roles');
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
var widget_id;
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
    
    jQuery("#widgetArea .tab").click(function() {
        jQuery("#widgetArea .tab").removeClass("active");
        widget_id = jQuery(this).attr("class").split(" ")[1].substr(1);
        jQuery(this).addClass("active");
        jQuery("#widgetArea .idle").show();
        loadWidget();
    });

    jQuery("#podBox").jqm();
    jQuery("#columnBox").jqm();
    jQuery("#pageBox").jqm();
    jQuery("#widgetBox").jqm();
});

function resetForm() {
    jQuery("#column_name").val("");
    jQuery("#column_name").attr("disabled", false);
    jQuery("#column_label").val("");
    jQuery("#column_type").val("");
    jQuery("#column_type").attr("disabled", false);
    jQuery("#column_pickval").val("");
    jQuery("#column_required").attr("checked", 0);
    jQuery("#column_required").attr("disabled", false);
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
        //jQuery("#column_options").show();
    }
    else {
        jQuery("#column_pickval").val("");
        jQuery("#column_pickval").hide();
        //jQuery("#column_options").val("");
        //jQuery("#column_options").hide();
    }
    jQuery("#column_sister_field_id").val("");
    jQuery("#column_sister_field_id").hide();
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
                jQuery("#pod_name").html(name);
                jQuery("#pod_label").val(label);
                jQuery("#is_toplevel").attr("checked", is_toplevel);
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
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&datatype="+datatype+"&label="+label+"&is_toplevel="+is_toplevel+"&list_filters="+encodeURIComponent(list_filters)+"&tpl_detail="+encodeURIComponent(tpl_detail)+"&tpl_list="+encodeURIComponent(tpl_list),
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
            var comment = (null == col_data.comment) ? "" : col_data.comment;
            var coltype = (null == col_data.coltype) ? "" : col_data.coltype;
            var pickval = (null == col_data.pickval) ? "" : col_data.pickval;
            var sister_field_id = (null == col_data.sister_field_id) ? "" : col_data.sister_field_id;
            var required = parseInt(col_data.required);
            jQuery("#column_name").val(name);
            jQuery("#column_label").val(label);
            jQuery("#column_comment").val(comment);
            jQuery("#column_type").val(coltype);
            jQuery("#column_pickval").val(pickval);
            jQuery("#column_sister_field_id").hide();
            jQuery("#column_required").attr("checked", required);
            jQuery("#column_pickval").hide();
            if ("name" == name) {
                jQuery("#column_name").attr("disabled", true);
                jQuery("#column_type").attr("disabled", true);
                jQuery("#column_required").attr("disabled", true);
            }
            if ("pick" == coltype) {
                jQuery("#column_pickval").show();
                //jQuery("#column_options").show();
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
    var comment = jQuery("#column_comment").val();
    var dtname = jQuery("#pod_name").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&datatype="+datatype+"&dtname="+dtname+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required,
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
    var comment = jQuery("#column_comment").val();
    var dtname = jQuery(".tab.active").html();
    var coltype = jQuery("#column_type").val();
    var pickval = jQuery("#column_pickval").val();
    var sister_field_id = jQuery("#column_sister_field_id").val();
    var required = (true == jQuery("#column_required").is(":checked")) ? 1 : 0;
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=edit&datatype="+datatype+"&dtname="+dtname+"&field_id="+col+"&name="+name+"&label="+label+"&comment="+comment+"&coltype="+coltype+"&pickval="+pickval+"&sister_field_id="+sister_field_id+"&required="+required,
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
                jQuery("#page_name").html(uri);
                jQuery("#page_title").val(title);
                jQuery("#page_content").val(phpcode);
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
                var id = msg;
                var html = '<div class="tab t'+id+'">'+uri+'</div>';
                jQuery("#pageArea .tabs").append(html);
                jQuery("#pageBox").jqmHide();
                jQuery("#pageArea .t"+id).click(function() {
                    jQuery("#pageArea .tab").removeClass("active");
                    page_id = jQuery(this).attr("class").split(" ")[1].substr(1);
                    jQuery(this).addClass("active");
                    jQuery("#pageArea .idle").show();
                    loadPage();
                });
                jQuery("#pageArea .t"+id).click();
            }
        }
    });
}

function editPage() {
    var title = jQuery("#page_title").val();
    var content = jQuery("#page_content").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=editpage&page_id="+page_id+"&page_title="+encodeURIComponent(title)+"&phpcode="+encodeURIComponent(content),
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
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&page="+page_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#page_name").html("Choose a PodPage");
                    jQuery("#pageArea #column_list").html('Need some help? Check out the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> to get started.');
                    jQuery("#pageArea .t"+page_id).remove();
                    jQuery("#pageArea .idle").hide();
                }
            }
        });
    }
}

function loadWidget() {
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/load.php",
        data: "auth="+auth+"&widget_id="+widget_id,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var widget_data = eval("("+msg+")");
                var name = (null == widget_data.name) ? "" : widget_data.name;
                var phpcode = (null == widget_data.phpcode) ? "" : widget_data.phpcode;
                jQuery("#widget_name").html(name);
                jQuery("#widget_content").val(phpcode);
            }
        }
    });
}

function addWidget() {
    var name = jQuery("#new_widget").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/add.php",
        data: "auth="+auth+"&type=widget&name="+name,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                var id = msg;
                var html = '<div class="tab t'+id+'">'+name+'</div>';
                jQuery("#widgetArea .tabs").append(html);
                jQuery("#widgetBox").jqmHide();
                jQuery("#widgetArea .t"+id).click(function() {
                    jQuery("#widgetArea .tab").removeClass("active");
                    widget_id = jQuery(this).attr("class").split(" ")[1].substr(1);
                    jQuery(this).addClass("active");
                    jQuery("#widgetArea .idle").show();
                    loadWidget();
                });
                jQuery("#widgetArea .t"+id).click();
            }
        }
    });
}

function editWidget() {
    var content = jQuery("#widget_content").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/edit.php",
        data: "auth="+auth+"&action=editwidget&widget_id="+widget_id+"&phpcode="+encodeURIComponent(content),
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

function dropWidget() {
    if (confirm("Do you really want to drop this widget?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&widget="+widget_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#widget_name").html("Choose a Widget");
                    jQuery("#widgetArea #column_list").html('Need some help? Check out the <a href="http://pods.uproot.us/user_guide" target="_blank">User Guide</a> to get started.');
                    jQuery("#widgetArea .t"+widget_id).remove();
                    jQuery("#widgetArea .idle").hide();
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
            <option value="code">code (no WYSIWYG editor)</option>
            <option value="file">file (document, media)</option>
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
            <!--<option value="" style="font-weight:bold; font-style:italic">-- WordPress --</option>
            <option value="wp_page">WP Page</option>
            <option value="wp_post">WP Post</option>
            <option value="wp_user">WP User</option>-->
        </select>
        <select id="column_sister_field_id" class="hidden"></select>
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
    <p>Ex: <strong>/resources/events/latest/</strong></p>
</div>

<div id="widgetBox" class="jqmWindow">
    <input type="text" id="new_widget" style="width:280px" />
    <input type="button" class="button" onclick="addWidget()" value="Add Widget" />
    <p>Ex: <strong>format_date</strong> or <strong>mp3_player</strong></p>
</div>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
    <div class="navTab active" rel="podArea">Pods</div>
<?php
if (pods_access('manage_podpages'))
{
?>
    <div class="navTab" rel="pageArea">PodPages</div>
<?php
}
if (pods_access('manage_widgets'))
{
?>
    <div class="navTab" rel="widgetArea">Widgets</div>
<?php
}
if (pods_access('manage_roles'))
{
?>
    <div class="navTab" rel="roleArea">Roles</div>
<?php
}
if (pods_access('manage_settings'))
{
?>
    <div class="navTab" rel="settingsArea">Settings</div>
<?php
}
?>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin pod area
==================================================
-->
<div id="podArea" class="area">
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

            <p class="extras" onclick="jQuery('#pod_settings').toggle(); jQuery(this).toggleClass('open')">Pod Settings</p>
            <div id="pod_settings" class="hidden">
                <p><input type="checkbox" id="is_toplevel" /> Add to Top Level menu?</p>
                <p><input type="text" id="pod_label" value="" /> Label (for Top Level menu)</p>
            </div>

            <p class="extras" onclick="jQuery('#tpl_detail').toggle(); jQuery(this).toggleClass('open')">Detail Template</p>
            <textarea id="tpl_detail" class="hidden"></textarea>

            <p class="extras" onclick="jQuery('#tpl_list').toggle(); jQuery(this).toggleClass('open')">List Template</p>
            <textarea id="tpl_list" class="hidden"></textarea>

            <p class="extras" onclick="jQuery('#list_filters').toggle(); jQuery(this).toggleClass('open')">List Filters</p>
            <input type="text" id="list_filters" class="hidden" />
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
    <div class="tabs">
        <input type="button" class="button" onclick="jQuery('#pageBox').jqmShow()" value="Add new page" />
<?php
if (isset($pages))
{
    foreach ($pages as $key => $val)
    {
?>
        <div class="tab t<?php echo $key; ?>"><?php echo $val; ?></div>
<?php
    }
}
?>
    </div>
    <div class="rightside">
        <h2 class="title" id="page_name">Choose a PodPage</h2>
        <textarea id="page_content"></textarea><br />
        <input type="text" id="page_title" /> Page Title
        <div class="idle hidden">
            <p>
                <input type="button" class="button" onclick="editPage()" value="Save changes" /> or
                <a href="javascript:;" onclick="dropPage()">drop page</a>
            </p>
        </div>
    </div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin widget area
==================================================
-->
<div id="widgetArea" class="area hidden">
    <div class="tabs">
        <input type="button" class="button" onclick="jQuery('#widgetBox').jqmShow()" value="Add new widget" />
<?php
if (isset($widgets))
{
    foreach ($widgets as $key => $val)
    {
?>
        <div class="tab t<?php echo $key; ?>"><?php echo $val; ?></div>
<?php
    }
}
?>
    </div>
    <div class="rightside">
        <h2 class="title" id="widget_name">Choose a Widget</h2>
        <textarea id="widget_content"></textarea>
        <div class="idle hidden">
            <p>
                <input type="button" class="button" onclick="editWidget()" value="Save changes" /> or
                <a href="javascript:;" onclick="dropWidget()">drop widget</a>
            </p>
        </div>
    </div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin role area
==================================================
-->
<div id="roleArea" class="area hidden">
    <div class="helper">Use the Role Manager plugin to add new roles. Admins have total access.</div>
<?php
$all_privs = array(
    array('name' => 'manage_pods', 'label' => 'Manage Pods'),
    array('name' => 'manage_podpages', 'label' => 'Manage PodPages'),
    array('name' => 'manage_widgets', 'label' => 'Manage Widgets'),
    array('name' => 'manage_settings', 'label' => 'Manage Settings'),
    array('name' => 'manage_content', 'label' => 'Manage All Content'),
    array('name' => 'manage_roles', 'label' => 'Manage Roles'),
    array('name' => 'manage_menu', 'label' => 'Manage Menu')
);

foreach ($datatypes as $id => $dtname)
{
    $all_privs[] = array('name' => "pod_$dtname", 'label' => "Access: $dtname");
}

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
            $active = (false !== array_search($priv['name'], $pods_roles[$role])) ? ' active' : '';
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
    <div class="helper">*WARNING* This cannot be undone. Please backup your database!</div>
    <input type="button" class="button" onclick="resetDB()" value="Reset database" />
</div>

