<?php
/**
 * Pods Media Button
 */

function pods_media_button($context) {
	$button = '<a href="javascript:void(0)"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Add Pod" /></a>';
	$context .= $button;
	return $context;
}
add_filter('media_buttons_context', 'pods_media_button');

?>
