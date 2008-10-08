<?php
$post_id = $_GET['post'];

if (empty($post_id))
{
    echo 'Please save the post before using this feature.';
    return;
}

$result = mysql_query("SELECT id, name FROM wp_pod_types");
while ($row = mysql_fetch_assoc($result))
{
    $datatypes[$row['id']] = $row['name'];
}

$result = mysql_query("SELECT datatype FROM wp_pod WHERE post_id = $post_id LIMIT 1");
if (0 < mysql_num_rows($result))
{
    $row = mysql_fetch_assoc($result);
    $datatype = $datatypes[$row['datatype']];
}
?>

<link rel="stylesheet" type="text/css" href="/wp-content/plugins/pods/style.css" />
<script type="text/javascript" src="/wp-content/plugins/pods/js/ui.datepicker.js"></script>
<script type="text/javascript">
var datatype;
var post_id = <?php echo $post_id; ?>;

function showform(dt) {
    datatype = dt;
    jQuery(".option").unbind("click");
    jQuery.ajax({
        type: "post",
        url: "/wp-content/plugins/pods/ajax/showform.php",
        data: "post_id="+post_id+"&datatype="+datatype,
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#module_form").hide();
                jQuery("#module_form").html(msg);
                jQuery("#module_form").show();
                jQuery(".date").datepicker({dateFormat: "yy-mm-dd 12:00:00"});
                jQuery(".option").click(function() {
                    jQuery(this).toggleClass("active");
                });
            }
        }
    });
}

function savePost() {
    var data = new Array();
    data[0] = "name=" + encodeURIComponent(jQuery("#title").val());
    data[1] = "body=" + encodeURIComponent(tinyMCE.activeEditor.getContent());

    var i = 2;
    var theval = "";
    jQuery(".form").each(function() {
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
        url: "/wp-content/plugins/pods/ajax/showform.php",
        data: "datatype="+datatype+"&post_id="+post_id+"&save=1&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                alert("Saved!");
            }
        }
    });
}

jQuery("#save-post").click(function() {
    savePost();
});
</script>
<table class="form-table">
    <tr valign="top">
        <th scope="row">Select One:</th>
        <td>
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
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span id="module_form">
<?php
if (!empty($datatype))
{
?>
                <script type="text/javascript">showform('<?php echo $datatype; ?>')</script>
<?php
}
?>
            </span>
        </td>
    </tr>
</table>
<p class="submit">
    <input type="button" value="Save" onclick="savePost()" />
</p>
