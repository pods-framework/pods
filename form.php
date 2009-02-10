<?php
$datatype = $this->datatype;
$pods_url = WP_PLUGIN_URL . '/pods';
$upload_dir = wp_upload_dir();
$upload_dir = str_replace(get_option('siteurl'), '', $upload_dir['baseurl']);
?>
<link rel="stylesheet" type="text/css" href="<?php echo $pods_url; ?>/style.css" />
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqmodal.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/date_input.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/jqFileTree.js"></script>
<script type="text/javascript" src="<?php echo $pods_url; ?>/js/nicEdit.js"></script>
<script type="text/javascript">
var active_file;

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
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

function saveForm() {
    var data = new Array();
    var columns = '<?php echo serialize($public_columns); ?>';
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
        data: "datatype=<?php echo $datatype; ?>&save=1&public=1&columns="+columns+"&"+data.join("&"),
        success: function(msg) {
            if ("Error" == msg.substr(0, 5)) {
                alert(msg);
            }
            else {
                window.location = "";
            }
        }
    });
    return false;
}
</script>

<div class="jqmWindow" id="dialog">
    <h2 style="margin-top:0">Pick a File:</h2>
    <div class="filebox"></div>
</div>

<div id="module_form" class="form_<?php echo $datatype; ?>">
<?php
$_POST['public'] = true;
$_POST['datatype'] = $datatype;
include realpath(dirname(__FILE__) . '/ajax/showform.php');
?>
</div>

