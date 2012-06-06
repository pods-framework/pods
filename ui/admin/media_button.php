<?php
/**
 * Pods Media Button
 */

/**
 * Add a button to the media buttons context
 */
function pods_media_button($context) {
    $button = '<a href="#TB_inline?inlineId=pods_shortcode_form&width=640" class="thickbox" id="add_pod_button"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Add Pod" /></a>';
    $context .= $button;
    return $context;
}
add_filter('media_buttons_context', 'pods_media_button');

/**
 * Display the shortcode form
 */
function add_pods_mce_popup() {
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        $('#pods_insert_shortcode').click(function(evt) {
            var form = $('#pods_shortcode_form_element'),
                use_case = $('#use-case-selector').val(),
                pod_select = $('#pod_select').val(),
                slug = $('#pod_slug').val(),
                orderby = $('#pod_orderby').val(),
                sort_direction = $('#pod_sort_direction').val(),
                template = $('#pod_template').val(),
                limit = $('#pod_limit').val(),
                column = $('#pod_column').val(),
                helper = $('#pod_helper').val(),
                where = $('#pod_where').val(),
                shortcode = '[pods ';

            // Validate the form
            var errors = [];
            switch (use_case) {
                case 'single':
                    if (!pod_select || !pod_select.length) {
                        errors.push("Pod");
                    }
                    if (!slug || !slug.length) {
                        errors.push("Slug or ID");
                    }
                    if (!template || !template.length) {
                        errors.push("Template");
                    }
                    break;
                case 'list':
                    if (!pod_select || !pod_select.length) {
                        errors.push("Pod");
                    }
                    if (!template || !template.length) {
                        errors.push("Template");
                    }
                    break;
                case 'column':
                    if (!pod_select || !pod_select.length) {
                        errors.push("Pod");
                    }
                    if (!slug || !slug.length) {
                        errors.push("ID or Slug");
                    }
                    if (!column || !column.length) {
                        errors.push("Column");
                    }
                    break;
            }

            if (errors.length) {
                var error_msg = "The following fields are required:\n";
                error_msg += errors.join("\n");
                alert(error_msg);
                return false;
            }

            shortcode += 'name="' + pod_select + '" ';
            if (slug && slug.length)
                shortcode += 'slug="' + slug + '" ';
            if (orderby && orderby.length) {
                if (sort_direction.length) {
                    shortcode += 'orderby="' + orderby + ' ' + sort_direction + '" ';
                } else {
                    shortcode += 'orderby="' + orderby + ' ASC" ';
                }
            }
            if (template && template.length)
                shortcode += 'template="' + template + '" ';
            if (limit && limit.length)
                shortcode += 'limit="' + limit + '" ';
            if (column && column.length)
                shortcode += 'col="' + column + '" ';
            if (helper && helper.length)
                shortcode += 'helper="' + helper + '" ';
            if (where && where.length)
                shortcode += 'where="' + where + '" ';

            shortcode += ']';

            if ((use_case == 'single' && window.pods_template_count == 0) || (use_case == 'list' && window.pods_template_count == 0)) {
                alert("No templates found!");
                return false;
            }

            window.send_to_editor(shortcode);
                
        });
    });
    </script>
    <?php
    require_once PODS_DIR . 'ui/admin/pods_shortcode_form.php';
}
add_action('admin_footer', 'add_pods_mce_popup');

?>
