<?php
class PodsWidget extends WP_Widget {

	/**
	 * Register Pods Widget
	 */
	public function PodsWidget() {
		$this->WP_Widget(
			'pods_widget',
			'Custom Pod',
			array('classname' => 'pods_widget', 'description' => 'Pods Custom Widget'),
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
?>
