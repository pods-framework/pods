<?php
$datatype = $this->datatype;
$upload_dir = wp_upload_dir();
$upload_dir = str_replace(get_option('siteurl'), '', $upload_dir['baseurl']);
?>
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css" />
<script type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqmodal.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/date_input.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/jqFileTree.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/nicEdit.js"></script>
<script type="text/javascript">
var active_file;

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });
    jQuery(".filebox").fileTree({
        root: "<?php echo $upload_dir; ?>/",
        script: "<?php echo PODS_URL; ?>/ajax/filetree.php",
        multiFolder: false
    },
    function(file) {
        jQuery("."+active_file).val(file);
        jQuery("#dialog").jqmHide();
    });

    elements = jQuery(".desc");
    var config = {
        iconsPath : "<?php echo PODS_URL; ?>/images/nicEditorIcons.gif",
        buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
    };

    for (i = 0; i < elements.length; i++) {
        new nicEditor(config).panelInstance(elements[i].id);
    }

    jQuery("#module_form input.date").date_input();
    jQuery("#dialog").jqm();
});

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
        data: "datatype=<?php echo $datatype; ?>&"+data.join("&"),
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
$this->showForm($this->get_pod_id(), $public_columns);
?>
</div>
