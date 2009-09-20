<?php
$upload_dir = wp_upload_dir();
$upload_dir = str_replace(get_option('siteurl'), '', $upload_dir['baseurl']);

$result = pod_query("SELECT id, name FROM @wp_pod_types ORDER BY name ASC");
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
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqmodal.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/date_input.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqFileTree.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/nicEdit.js"></script>
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
        script: "<?php echo PODS_URL; ?>/ajax/filetree.php",
        multiFolder: false
    },
    function(file) {
        jQuery("."+active_file).val(file);
        jQuery("#dialog").jqmHide();
    });

    jQuery("#browseTable tr:odd").addClass("zebra");
    jQuery("#dialog").jqm();
});

function editItem(datatype, pod_id) {
    jQuery(".navTab").click();
    jQuery("#editTitle").html("Edit "+datatype);
    showform(datatype, pod_id);
}

function dropItem(pod_id) {
    if (confirm("Do you really want to drop this item?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/drop.php",
            data: "auth="+auth+"&pod_id="+pod_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#browseTable tr#row"+pod_id).css("background", "red");
                    jQuery("#browseTable tr#row"+pod_id).fadeOut("slow");
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
        url: "<?php echo PODS_URL; ?>/ajax/showform.php",
        data: "datatype="+datatype+"&"+data.join("&"),
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

function showform(dt, pod_id) {
    if ("" == dt) {
        return false;
    }
    datatype = dt;
    jQuery(".option").unbind("click");
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/showform.php",
        data: "pod_id="+pod_id+"&datatype="+datatype,
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
                    iconsPath : "<?php echo PODS_URL; ?>/images/nicEditorIcons.gif",
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
    <div class="navTab active" rel="browseArea"><a href="javascript:;">Browse</a></div>
    <div class="navTab" rel="editArea"><a href="javascript:;">Edit</a></div>
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

$where[] = 1;

if (!empty($dtname))
{
    $where[] = "t.name = '" . mysql_real_escape_string(trim($dtname)) . "'";
}

if (!empty($_GET['keywords']))
{
    $where[] = "p.name LIKE '%" . mysql_real_escape_string(trim($_GET['keywords'])) . "%'";
}

$orderby = 'modified desc';
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
    p.id, p.name, p.datatype, t.name AS dtname, p.modified
FROM
    @wp_pod p
INNER JOIN
    @wp_pod_types t ON t.id = p.datatype
WHERE
    $where
ORDER BY
    $orderby, name
LIMIT
    $limit
";
$result = pod_query($sql);

$Record->total_rows = pod_query("SELECT FOUND_ROWS()");
?>
    <div id="filterForm">
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
Listing
==================================================
*/
$get_vals = implode('&', $get_vals);
$order = array('name' => 'name', 'dtname' => 'dtname', 'modified' => 'modified');
if (!empty($orderby))
{
    if ('desc' != substr($orderby, -4))
    {
        $order[$orderby] = "$orderby+desc";
    }
}
?>
    <table id="browseTable" cellpadding="0" cellspacing="0">
        <tr>
            <th></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['name']; ?>">Name</a></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['dtname']; ?>">Pod</a></th>
            <th><a href="?<?php echo $get_vals . '&orderby=' . $order['modified']; ?>">Modified</a></th>
            <th></th>
        </tr>
<?php
while ($row = mysql_fetch_assoc($result))
{
?>
        <tr id="row<?php echo $row['id']; ?>">
            <td width="20">
                <div class="btn editme" onclick="editItem('<?php echo $row['dtname']; ?>', <?php echo $row['id']; ?>)"></div>
            </td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo $datatypes[$row['datatype']]; ?></td>
            <td><?php echo date("m/d/Y g:i A", strtotime($row['modified'])); ?></td>
            <td width="20"><div class="btn dropme" onclick="dropItem(<?php echo $row['id']; ?>)"></div></td>
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
    <div id="icon-plugins" class="icon32" style="margin:0 6px 0 0"><br /></div>
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
