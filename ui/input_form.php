<?php
$cache = PodCache::instance();
$cache->form_count++;
$form_count = $cache->form_count;

if (1 == $form_count)
{
    do_action('pods_form_init',&$this);
?>
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>/ui/style.css" />
<script type="text/javascript" src="<?php echo PODS_URL; ?>/ui/js/jqmodal.js"></script>
<script type="text/javascript">
var active_file;

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });

    jQuery(".file .btn.dropme").live("click", function() {
        jQuery(this).parent().remove();
    });

    jQuery(".file_match").live("click", function() {
        var file_id = jQuery(this).attr("rel");
        var file_name = jQuery(this).html();
        jQuery(".rightside." + active_file + " .form").append('<div id="' + file_id + '" class="success"><div class="btn dropme"></div>' + file_name + '</div>');
        jQuery("#dialog").jqmHide();
    });

    if ('undefined' != typeof(nicPaneOptions)) {
        elements = jQuery(".desc");
        var config = {
            iconsPath : "<?php echo PODS_URL; ?>/ui/images/nicEditorIcons.gif",
            buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
        };

        for (i = 0; i < elements.length; i++) {
            new nicEditor(config).panelInstance(elements[i].id);
        }
    }
    jQuery("#dialog").jqm();
});

function saveForm(form_count) {
    jQuery(".btn_save").attr("disabled", "disabled");

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
                theval += jQuery(this).data("value") + ",";
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
        url: "<?php echo PODS_URL; ?>/ui/ajax/api.php",
        data: "action=save_pod_item&"+data.join("&"),
        success: function(msg) {
            if (!is_error(msg)) {
                window.location = "<?php echo (!empty($thankyou_url)) ? $thankyou_url : $_SERVER['REQUEST_URI']; ?>";
            }
            jQuery(".btn_save").removeAttr("disabled");
        }
    });
    return false;
}

function fileBrowser() {
    jQuery("#dialog").jqmShow();
    jQuery(".filebox").html("Loading...");
    var search = jQuery("#file_search").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ui/ajax/misc.php",
        data: "action=browse_files&search="+encodeURIComponent(search),
        success: function(msg) {
            jQuery(".filebox").html(msg);
        }
    });
}
</script>

<?php
}

//pre-form hooks
do_action('pods_pre_form',$form_count,&$this);
do_action("pods_pre_form_$this->datatype",$form_count,&$this);
?>

<div class="pods_form form_<?php echo $this->datatype; ?> form_<?php echo $form_count; ?>">
<?php
if (1 == $form_count)
{
?>
<div class="jqmWindow" id="dialog">
    <input type="text" id="file_search" value="" />
    <input type="button" class="button" value="Narrow results" onclick="fileBrowser()" />
    <div class="filebox"></div>
</div>
<?php
}
$this->showform($this->get_pod_id(), $public_columns, $label);
?>
</div>
<?php 
//post-form hooks
do_action('pods_post_form',$form_count,&$this);
do_action("pods_post_form_$this->datatype",$form_count,&$this);