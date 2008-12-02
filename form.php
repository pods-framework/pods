<?php
$datatype = $this->datatype;
$pods_url = WP_PLUGIN_URL . '/pods';
?>
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/ui.datepicker.js"></script>
<script type="text/javascript">
jQuery(function() {
    jQuery("#module_form").html(msg);
    jQuery(".date").datepicker({dateFormat: "yy-mm-dd 12:00:00"});
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });
});

function savePost() {
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
        data: "datatype=<?php echo $datatype; ?>&save=1&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                jQuery("#module_form").empty().html("Thanks for your support!");
            }
        }
    });
    return false;
}
</script>

<p id="module_form">
<?php
$_POST['datatype'] = $datatype;
$_POST['columns'] = $columns;
include realpath(dirname(__FILE__) . '/ajax/showform.php');
?>
    <input type="button" onclick="savePost()" value="Submit" />
</p>

