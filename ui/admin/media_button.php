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
	require_once PODS_DIR . 'ui/admin/pods_shortcode_form.php';
}
add_action('admin_footer', 'add_pods_mce_popup');

?>
