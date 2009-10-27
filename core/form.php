<?php
global $form_count;
$form_count = empty($form_count) ? 1 : $form_count + 1;
$datatype = $this->datatype;

if (1 == $form_count)
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/style.css" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/date_input.js"></script>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/nicEdit.js"></script>
<script type="text/javascript">
var active_file;

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });

    jQuery(".file .btn.dropme").live("click", function() {
        jQuery(this).parent().remove();
    });

    elements = jQuery(".desc");
    var config = {
        iconsPath : "<?php echo PODS_URL; ?>/images/nicEditorIcons.gif",
        buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
    };

    for (i = 0; i < elements.length; i++) {
        new nicEditor(config).panelInstance(elements[i].id);
    }

    jQuery("#pod_form input.date").date_input();
});

function saveForm(form_count) {
    for (i = 0; i < elements.length; i++) {
        nicEditors.findEditor(elements[i].id).saveContent();
    }

    var data = new Array();
    var i = 0;
    jQuery(".form_" + form_count + " .form").each(function() {
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
        url: "<?php echo PODS_URL; ?>/ajax/showform.php",
        data: data.join("&"),
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

<?php
}
?>

<div class="pod_form form_<?php echo $datatype; ?> form_<?php echo $form_count; ?>">
<?php
$this->showForm($this->get_pod_id(), $public_columns, $label, $form_count);
?>
</div>
