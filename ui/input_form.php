<?php
$cache = PodCache::instance();
$cache->form_count++;
$form_count = $cache->form_count;

if (1 == $form_count)
{
    do_action('pods_form_init',$this);
    if (!wp_script_is('pods-ui', 'queue') && !wp_script_is('pods-ui', 'to_do') && !wp_script_is('pods-ui', 'done'))
        wp_print_scripts('pods-ui');
?>
<link rel="stylesheet" type="text/css" href="<?php echo apply_filters('pods_form_stylesheet_url', PODS_URL.'/ui/style.css'); ?>" />
<script type="text/javascript">
var active_file;

jQuery(function() {
    jQuery(".option").click(function() {
        jQuery(this).toggleClass("active");
    });

    jQuery(".file .btn.dropme").live("click", function() {
        jQuery(this).parent().remove();
    });
<?php
    if (!(defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER) && !(defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in()) && !(defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))) {
?>
    jQuery(".file_match").live("click", function() {
        var file_id = jQuery(this).attr("rel");
        var file_name = jQuery(this).html();
        jQuery(".rightside." + active_file + " .form").append('<div id="' + file_id + '" class="success"><div class="btn dropme"></div>' + file_name + '</div>');
        jQuery("#dialog").jqmHide();
    });
<?php
    }
?>
    if ('undefined' != typeof(nicPaneOptions)) {
        var nicEditElements = jQuery(".form.desc");
        var config = {
            iconsPath : "<?php echo PODS_URL; ?>/ui/images/nicEditorIcons.gif",
            buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
        };

        for (i = 0; i < nicEditElements.length; i++) {
            new nicEditor(config).panelInstance(nicEditElements[i].id);
        }
    }
<?php
    if (!(defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER) && !(defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in()) && !(defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))) {
?>
    jQuery("#dialog").jqm();
<?php
    }
?>
});

function saveForm(form_count) {
    jQuery(".btn_save").attr("disabled", "disabled");

    if ('undefined' != typeof(nicPaneOptions)) {
        var nicEditElements = jQuery(".form.desc");
        for (i = 0; i < nicEditElements.length; i++) {
            nicEditors.findEditor(nicEditElements[i].id).saveContent();
        }
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
        else if (typeof(tinyMCE) == 'object' && "desc_tinymce" == classname[1]) {
            var ed = tinyMCE.get(jQuery(this).attr('id'));
            if (typeof(ed) == 'object')
                jQuery(this).val(ed.getContent());
            theval = jQuery(this).val();
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
        data: "action=save_pod_item&_wpnonce=<?php echo wp_create_nonce('pods-save_pod_item'); ?>&"+data.join("&"),
        success: function(msg) {
            if (!is_error(msg)) {
                window.location = "<?php echo (!empty($thankyou_url)) ? $thankyou_url : $_SERVER['REQUEST_URI']; ?>";
            }
            jQuery(".btn_save").removeAttr("disabled");
        }
    });
    return false;
}
<?php
    if (!(defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER) && !(defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in()) && !(defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))) {
?>
function fileBrowser() {
    jQuery("#dialog").jqmShow();
    jQuery(".filebox").html("Loading...");
    var search = jQuery("#file_search").val();
    jQuery.ajax({
        type: "post",
        url: "<?php echo PODS_URL; ?>/ui/ajax/misc.php",
        data: "action=browse_files&_wpnonce=<?php echo wp_create_nonce('pods-browse_files'); ?>&search="+encodeURIComponent(search),
        success: function(msg) {
            jQuery(".filebox").html(msg);
        }
    });
}
<?php
    }
?>
</script>
<?php
}

//pre-form hooks
do_action('pods_pre_form',$form_count,$this);
do_action("pods_pre_form_{$this->datatype}",$form_count,$this);
?>

<div class="pods_form form_<?php echo esc_attr($this->datatype); ?> form_<?php echo $form_count; ?>">
<?php
if (1 == $form_count && !(defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER) && !(defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in()) && !(defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))) {
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
do_action('pods_post_form',$form_count,$this);
do_action("pods_post_form_{$this->datatype}",$form_count,$this);
