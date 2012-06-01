<?php
/**
 * Pods Media Button
 */

/**
 * Add a button to the media buttons context
 */
function pods_media_button($context) {
	$button = '<a href="#TB_inline?inlineId=pods_shortcode_form&width=640&height=480" class="thickbox" id="add_pod"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Add Pod" /></a>';
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
			var form = $('#pods_shortcode_form'),
				pod_select = $('#pod_select').val(),
				slug = $('#pod_slug').val(),
				orderby = $('#pod_orderby').val(),
				sort_direction = $('#pod_sort_direction').val(),
				template = $('#pod_template').val(),
				limit = $('#pod_limit').val(),
				column = $('#pod_column').val(),
				helper = $('#pod_helper').val();
				
		});
	});
	</script>
	<?php
	require_once PODS_DIR . 'ui/admin/pods_shortcode_form.php';
}
add_action('admin_footer', 'add_pods_mce_popup');

?>
