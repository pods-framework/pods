<?php
$upload_dir = wp_upload_dir();
$upload_dir = str_replace(get_option('siteurl'), '', $upload_dir['baseurl']);
$post_id = $_GET['post'];

if (empty($post_id))
{
    echo 'Please save the post before using this feature.';
    return;
}

$result = mysql_query("SELECT id, name FROM {$table_prefix}pod_types");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}

$result = mysql_query("SELECT datatype FROM {$table_prefix}pod WHERE post_id = $post_id LIMIT 1");
if (0 < mysql_num_rows($result))
{
    $row = mysql_fetch_assoc($result);
    $datatype = $datatypes[$row['datatype']];
}
?>

<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqFileTree.js"></script>
<script type="text/javascript">
var datatype;
var post_id = <?php echo $post_id; ?>;
var active_file;

jQuery(function() {
    jQuery("#publish").click(function() {
        return savePost();
    });
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

function showform(dt) {
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
                /*jQuery(".datepicker").each(function() {
                    alert(jQuery(this).attr("rel"));
                });*/
            }
        }
    });
    // Add the new tag
    var tag_name = "pod-" + datatype.replace("_", "-");
    jQuery("#newtag").val(tag_name);
    jQuery("#tagadd").click();
}

function savePost() {
    var data = new Array();
    var active_editor = jQuery(".active").attr("id");
    var content = ("edButtonHTML" == active_editor) ? jQuery("#content").val() : tinyMCE.activeEditor.getContent();
    data[0] = "name=" + encodeURIComponent(jQuery("#title").val());
    data[1] = "body=" + encodeURIComponent(content);

    var i = 2;
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
        data: "datatype="+datatype+"&post_id="+post_id+"&save=1&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#post").submit();
            }
        }
    });
    return false;
}
</script>

<div class="jqmWindow" id="dialog">
    <h2 style="margin-top:0">Pick a File:</h2>
    <div class="filebox" style="height:160px; overflow-x:hidden; overflow-y:auto"></div>
</div>

<p>
    Select One:
    <select class="pick_module" onchange="showform(this.value)">
        <option value="">-- Pick One --</option>
<?php
foreach ($datatypes as $key => $name)
{
    $selected = ($name == $datatype) ? ' selected' : '';
?>
        <option value="<?php echo $name; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
<?php
}
?>
    </select>
</p>

<div id="module_form">
<?php
if (!empty($datatype))
{
?>
    <script type="text/javascript">showform('<?php echo $datatype; ?>')</script>
<?php
}
?>
</div>

