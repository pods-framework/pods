<?php
$upload_dir = wp_upload_dir();
$upload_dir = str_replace(get_option('siteurl'), '', $upload_dir['baseurl']);

$result = pod_query("SELECT id, name FROM {$table_prefix}pod_types ORDER BY name ASC");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}

$add_or_edit = 'edit';
$page = explode('-', $_GET['page']);
if ('pod' == $page[0])
{
    $dtname = strtolower($page[1]);
    $datatype = array_search($dtname, $datatypes);
    $add_or_edit = 'add';
}
else
{
    $dtname = isset($page[2]) ? $page[2] : $_GET['pod'];
}
?>

<!--
==================================================
Begin javascript code
==================================================
-->
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/date_input.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqFileTree.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/nicEdit.js"></script>
<script type="text/javascript">
var datatype;
var active_file;
var add_or_edit = '<?php echo $add_or_edit; ?>';
var auth = '<?php echo md5(AUTH_KEY); ?>';

jQuery(function() {
    jQuery(".navTab").click(function() {
        jQuery(".navTab").removeClass("active");
        jQuery(this).addClass("active");
        var activeArea = jQuery(this).attr("rel");
        jQuery(".area").hide();
        jQuery("#"+activeArea).show();
    });
    if ('add' == add_or_edit) {
        jQuery(".navTab").click();
        jQuery("#editTitle").html("Add new <?php echo $dtname; ?>");
    }
    jQuery(".filebox").fileTree({
        root: "<?php echo $upload_dir; ?>/",
        script: "<?php echo $pods_url; ?>/ajax/filetree.php",
        multiFolder: false
    },
    function(file) {
        jQuery("."+active_file).val(file);
        jQuery("#dialog").jqmHide();
    });
    jQuery("#dialog").jqm();
});

function editItem(datatype, post_id) {
    jQuery(".navTab").click();
    jQuery("#editTitle").html("Edit "+datatype);
    showform(datatype, post_id);
}

function dropItem(post_id) {
    if (confirm("Do you really want to drop this item?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo $pods_url; ?>/ajax/drop.php",
            data: "auth="+auth+"&post_id="+post_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#browseTable tr#row"+post_id).css("background", "red");
                    jQuery("#browseTable tr#row"+post_id).fadeOut("slow");
                }
            }
        });
    }
}

function saveForm() {
    for (i = 0; i < elements.length; i++) {
        nicEditors.findEditor(elements[i].id).saveContent();
    }

    var data = new Array();

    var i = 0;
    jQuery(".form").each(function() {
        var theval = "";
        var classname = jQuery(this).attr("class").split(" ");
        if ("pick" == classname[1]) {
            jQuery("." + classname[2] + " .active").each(function() {
                theval += jQuery(this).attr("value") + ",";
            });
            theval = theval.substr(0, theval.length - 1);
        }
        else if ("bool" == classname[1]) {
            theval = (true == jQuery(this).is(":checked")) ? 1 : 0;
        }
        else {
            theval = jQuery(this).val();
        }
        data[i] = classname[2] + "=" + encodeURIComponent(theval);
        i++;
    });

    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/showform.php",
        data: "datatype="+datatype+"&save=1&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                window.location="";
            }
        }
    });
    return false;
}

function showform(dt, post_id) {
    if ("" == dt) {
        return false;
    }
    datatype = dt;
    jQuery(".option").unbind("click");
    jQuery.ajax({
        type: "post",
        url: "<?php echo $pods_url; ?>/ajax/showform.php",
        data: "post_id="+post_id+"&datatype="+datatype,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#module_form").hide();
                jQuery("#module_form").html(msg);
                jQuery("#module_form").show();
                jQuery(".option").click(function() {
                    jQuery(this).toggleClass("active");
                });

                elements = jQuery(".desc");
                var config = {
                    iconsPath : "../wp-content/plugins/pods/images/nicEditorIcons.gif",
                    buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
                };

                for (i = 0; i < elements.length; i++) {
                    new nicEditor(config).panelInstance(elements[i].id);
                }
                jQuery("input.date").date_input();
            }
        }
    });
}
</script>

<div class="jqmWindow" id="dialog">
    <h2 style="margin-top:0">Pick a File:</h2>
    <div class="filebox"></div>
</div>

<!--
==================================================
Begin tabbed navigation
==================================================
-->
<div id="nav">
    <div class="navTab active" rel="browseArea">Browse</div>
    <div class="navTab" rel="editArea">Edit</div>
    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin browse area
==================================================
-->
<div id="browseArea" class="area">
<?php
$Record = new Pod();
$Record->page = empty($_GET['pg']) ? 1 : $_GET['pg'];
$limit = (15 * ($Record->page - 1)) . ',15';
$Record->type = '';

$where[] = "p.post_type NOT IN ('post', 'page', 'revision', 'attachment')";

if (!empty($dtname))
{
    $where[] = "p.post_type = '" . mysql_real_escape_string(trim($dtname)) . "'";
}

if (!empty($_GET['keywords']))
{
    $where[] = "p.post_title LIKE '%" . mysql_real_escape_string(trim($_GET['keywords'])) . "%'";
}

$orderby = 'post_modified DESC';
foreach ($_GET as $key => $val)
{
    if ('orderby' != $key)
    {
        $get_vals[$key] = "$key=$val";
    }
    else
    {
        $orderby = $_GET[$key];
    }
}

$where = implode(' AND ', $where);

$sql = "
SELECT
    SQL_CALC_FOUND_ROWS
    p.ID AS post_id, r.row_id, p.post_title, p.post_type, p.post_modified
FROM
    {$table_prefix}posts p
INNER JOIN
    {$table_prefix}pod r ON r.post_id = p.ID
WHERE
    $where
ORDER BY
    p.$orderby, p.post_title
LIMIT
    $limit
";
$result = pod_query($sql);

$Record->total_rows = pod_query("SELECT FOUND_ROWS()");
?>
    <div style="float:left; width:50%">
        <?php echo $Record->getPagination(); ?>
    </div>
    <div id="filterForm" style="float:left; width:50%; text-align:right">
        <form method="get">
            Narrow results:
<?php
if ('pods-browse' == $_GET['page'])
{
?>
            <select class="pick_module" name="pod">
                <option value="">-- All pods --</option>
<?php
    foreach ($datatypes as $key => $name)
    {
        $selected = ($name == $dtname) ? ' selected' : '';
?>
                <option value="<?php echo $name; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
<?php
    }
?>
            </select>
<?php
}
?>
            <input type="text" id="column_name" name="keywords" />
            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
            <input type="button" class="button" value="Go" onclick="this.form.submit()" />
        </form>
    </div>
    <div class="clear"><!--clear--></div>
<?php
/*
==================================================

==================================================
*/
$get_vals = implode('&', $get_vals);
$order = array('post_title' => 'post_title', 'post_type' => 'post_type', 'post_modified' => 'post_modified');
if (!empty($orderby))
{
    if ('DESC' != substr($orderby, -4))
    {
        $order[$orderby] = "$orderby DESC";
    }
}
?>
    <table id="browseTable" style="width:100%" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['post_title']; ?>">Name</a></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['post_type']; ?>">Pod</a></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['post_modified']; ?>">Modified</a></th>
            <th></th>
        </tr>
<?php
while ($row = mysql_fetch_assoc($result))
{
    $zebra = ('' == $zebra) ? ' class="zebra"' : '';
?>
        <tr id="row<?php echo $row['post_id']; ?>"<?php echo $zebra; ?>>
            <td width="20">
                <div class="btn editme" onclick="editItem('<?php echo $row['post_type']; ?>', <?php echo $row['post_id']; ?>)"></div>
            </td>
            <td>
                <?php echo $row['post_title']; ?>
            </td>
            <td><?php echo $row['post_type']; ?></td>
            <td><?php echo date("m/d/Y g:i A", strtotime($row['post_modified'])); ?></td>
            <td width="20"><div class="btn dropme" onclick="dropItem(<?php echo $row['post_id']; ?>)"></div></td>
        </tr>
<?php
}
?>
    </table>

    <?php echo $Record->getPagination(); ?>

    <div class="clear"><!--clear--></div>
</div>

<!--
==================================================
Begin edit area
==================================================
-->
<div id="editArea" class="area hidden">
    <div id="icon-plugins" class="icon32" style="margin:0; margin-right:6px"><br /></div>
    <h2 class="title" id="editTitle">Please select an item</h2>
    <div class="clear"><!--clear--></div>

    <div id="module_form">
<?php
if ('add' == $add_or_edit)
{
?>
        <script type="text/javascript">showform('<?php echo $dtname; ?>')</script>
<?php
}
?>
    </div>
</div>

