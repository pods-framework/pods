<?php
// Plugin hook
do_action('pods_manage_content');

if (false === apply_filters('pods_manage_content', true))
{
    return;
}

// Get all pod types
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
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css?r=<?php echo rand(1000, 9999); ?>" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqmodal.js"></script>
<script type="text/javascript">
var datatype;
var active_file;
var add_or_edit = '<?php echo $add_or_edit; ?>';

jQuery(function() {
    if ('add' == add_or_edit) {
        jQuery(".area").hide();
        jQuery("#editArea").show();
    }

    jQuery(".file .btn.dropme").live("click", function() {
        jQuery(this).parent().remove();
    });

    jQuery(".file_match").live("click", function() {
        var file_id = jQuery(this).attr("rel");
        var file_name = jQuery(this).html();
        jQuery(".rightside." + active_file + " .form").append('<div id="' + file_id + '" class="success"><div class="btn dropme"></div>' + file_name + '</div>');
        jQuery("#dialog").jqmHide();
    });

    jQuery("#browseArea tr:even").addClass("alternate");
    jQuery("#dialog").jqm();
});

function editItem(datatype, pod_id) {
    jQuery(".area").hide();
    jQuery("#editArea").show();
    showform(datatype, pod_id);
}

function dropItem(pod_id) {
    if (confirm("Do you really want to drop this item?")) {
        jQuery.ajax({
            type: "post",
            url: "<?php echo PODS_URL; ?>/ajax/api.php",
            data: "action=drop_pod_item&pod_id="+pod_id,
            success: function(msg) {
                if ("Error" == msg.substr(0, 5)) {
                    alert(msg);
                }
                else {
                    jQuery("#browseArea tr#row"+pod_id).css("background", "red");
                    jQuery("#browseArea tr#row"+pod_id).fadeOut("slow");
                }
            }
        });
    }
}

function saveForm() {
    jQuery(".btn_save").attr("disabled", "disabled");

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
            theval = theval.slice(0, -1);
        }
        else if ("file" == classname[1]) {
            jQuery("." + classname[2] + " > div.success").each(function() {
                theval += jQuery(this).attr("id") + ",";
            });
            theval = theval.slice(0, -1);
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
        url: "<?php echo PODS_URL; ?>/ajax/api.php",
        data: "action=save_pod_item&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                window.location="<?php echo $_SERVER['REQUEST_URI']; ?>";
            }
            jQuery(".btn_save").attr("disabled", "");
        }
    });
    return false;
}

function showform(dt, pod_id) {
    datatype = dt;
    jQuery(".pods_form").hide();
    jQuery(".option").unbind("click");
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/api.php",
        data: "action=load_pod_item&pod_id="+pod_id+"&datatype="+datatype,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery(".pods_form").html(msg);
                jQuery(".pods_form").toggle();
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
            }
        }
    });
}

function fileBrowser() {
    jQuery("#dialog").jqmShow();
    jQuery(".filebox").html("Loading...");
    var search = jQuery("#file_search").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ajax/misc.php",
        data: "action=browse_files&search="+encodeURIComponent(search),
        success: function(msg) {
            jQuery(".filebox").html(msg);
        }
    });
}
</script>

<div class="jqmWindow" id="dialog">
    <input type="text" id="file_search" value="" />
    <input type="button" class="button" value="Narrow results" onclick="fileBrowser()" />
    <div class="filebox"></div>
</div>

<!--
==================================================
Begin browse area
==================================================
-->
<?php
$Record = new Pod();
$Record->page = empty($_GET['pg']) ? 1 : $_GET['pg'];
$limit = (15 * ($Record->page - 1)) . ',15';
$Record->type = '';

$where[] = 1;

if (!empty($dtname))
{
    $where[] = "t.name = '" . pods_sanitize($dtname) . "'";
}

if (!empty($_GET['keywords']))
{
    $where[] = "p.name LIKE '%" . pods_url_variable('keywords', 'get') . "%'";
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
        $orderby = pods_url_variable($key, 'get');
    }
}

$where = implode(' AND ', $where);

$sql = "
SELECT
    SQL_CALC_FOUND_ROWS
    p.id, p.name, p.datatype, t.name AS dtname, p.created, p.modified
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
<div class="wrap">
    <div id="icon-edit" class="icon32"><br /></div>
    <h2>Browse Content</h2>

    <div id="browseArea" class="area">
        <div class="tablenav">
            <form method="get">
                <select class="pick_module" name="pod">
                    <option value="">-- All Pods --</option>
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
                <input type="text" name="keywords" value="<?php echo pods_url_variable('keywords', 'get'); ?>" />
                <input type="hidden" name="page" value="<?php echo pods_url_variable('page', 'get'); ?>" />
                <input type="submit" class="button" value="Filter" />
            </form>
        </div>

        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Pod</th>
                    <th width="100">Date</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Pod</th>
                    <th>Date</th>
                </tr>
            </tfoot>
            <tbody>
<?php
while ($row = mysql_fetch_assoc($result))
{
    $date_desc = ($row['created'] != $row['modified']) ? 'Updated' : 'Added';
?>
                <tr id="row<?php echo $row['id']; ?>">
                    <td>
                        <a class="row-title" href="#" onclick="editItem('<?php echo $row['dtname']; ?>',<?php echo $row['id']; ?>)"><?php echo htmlspecialchars($row['name']); ?></a>
                        <div class="row-actions">
                            <span><a href="#" onclick="editItem('<?php echo $row['dtname']; ?>',<?php echo $row['id']; ?>)">Quick Edit</a></span> |
                            <span><a href="javascript:;" onclick="dropItem(<?php echo $row['id']; ?>)">Delete</a></span>
                        </div>
                    </td>
                    <td><?php echo $datatypes[$row['datatype']]; ?></td>
                    <td><?php echo date("Y/m/d", strtotime($row['modified'])); ?><div><?php echo $date_desc; ?></div></td>
                </tr>
<?php
}
?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php echo $Record->getPagination(); ?>
            </div>
        </div>
    </div>

    <!--
    ==================================================
    Begin edit area
    ==================================================
    -->
    <div id="editArea" class="area hidden">
        <div class="tablenav">
            <input type="button" class="button" value="Back to List" onclick="jQuery('.area').hide(); jQuery('#browseArea').show()" />
        </div>
        <table id="editTable" class="widefat">
            <thead><tr><th></th></tr></thead>
            <tfoot><tr><th></th></tr></tfoot>
            <tbody>
                <tr>
                    <td>
                        <div class="pods_form">
<?php
if ('add' == $add_or_edit)
{
?>
                            <script type="text/javascript">showform('<?php echo $dtname; ?>')</script>
<?php
}
?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="tablenav">
            <input type="button" onclick="saveForm()" value="Save changes" class="button" />
        </div>
    </div>
</div>
