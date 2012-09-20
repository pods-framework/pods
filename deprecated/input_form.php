<?php
/*$pods_cache = PodCache::instance();
$pods_cache->form_count++;*/
$form_count = (int) 1;

if (1 == $form_count)
{
    do_action('pods_form_init', $this);
    wp_register_script( 'pods-ui-deprecated', PODS_URL . 'deprecated/js/pods.ui.js', array(), PODS_VERSION );
    if (!wp_script_is('pods-ui-deprecated', 'queue') && !wp_script_is('pods-ui-deprecated', 'to_do') && !wp_script_is('pods-ui-deprecated', 'done'))
        wp_print_scripts('pods-ui-deprecated');
?>
<link rel="stylesheet" type="text/css" href="<?php echo PODS_URL; ?>deprecated/style.css" />
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
    });
<?php
    }
?>
    if ('undefined' != typeof(nicPaneOptions)) {
        var nicEditElements = jQuery(".form.desc");
        var config = {
            iconsPath : "<?php echo PODS_URL; ?>deprecated/images/nicEditorIcons.gif",
            buttonList : ['bold','italic','underline','fontFormat','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','xhtml']
        };

        for (i = 0; i < nicEditElements.length; i++) {
            new nicEditor(config).panelInstance(nicEditElements[i].id);
        }
    }
});

function saveForm (form_count) {
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
        url: "<?php echo PODS_URL; ?>deprecated/ajax/api.php",
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
</script>
<?php
}

//pre-form hooks
do_action('pods_pre_form', $form_count, $this);
do_action("pods_pre_form_{$this->pod}", $form_count, $this);
?>

<div class="pods_form form_<?php echo esc_attr($this->pod); ?> form_<?php echo $form_count; ?>">
<?php
$this->showform($this->get_field( $this->data->field_id ), $public_columns, $label);
?>
</div>
<?php
//post-form hooks
do_action('pods_post_form', $form_count, $this);
do_action("pods_post_form_{$this->pod}", $form_count, $this);