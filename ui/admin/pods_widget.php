<?php
/**
 * Pods Widget Form and Implementation
 */

add_action('widgets_init', 'pods_register_widgets');

if (!function_exists('pods_register_widgets')) {
function pods_register_widgets() {
	register_widget('Pods_Custom_Widget');
}
}

if (!class_exists('Pods_Custom_Widget')) {
class Pods_Custom_Widget extends WP_Widget {

	/**
	 * Register Pods Widget
	 */
	public function Pods_Custom_Widget() {
		$this->WP_Widget(
			'pods_custom_widget',
			'Custom Pod',
			array('classname' => 'pods_custom_widget', 'description' => 'Pods Custom Widget'),
			array('width' => 200, 'height' => 400)
		);
	}

	/**
	 * Output of widget
	 */
	public function widget($args, $instance) {
		extract($args);
	}

	/**
	 * Updates the new instance of widget arguments
	 * @return array $instance Updated instance
	 */
	public function update($new_instance, $old_instance) {
	
	}

	/**
	 * Widget Form
	 */
	public function form($instance) {
		$widget = true;
		require_once(PODS_DIR . 'ui/admin/pods_shortcode_form.php');
	}
}
}

?>
