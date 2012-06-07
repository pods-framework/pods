<?php
class PodsSingleWidget extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsSingleWidget() {
        $this->WP_Widget(
            'pods_single_widget',
            'Single Pod',
            array('classname' => 'pods_single_widget', 'description' => 'Single Pod Item'),
            array('width' => 400)
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
     * @returns array $instance Updated instance
     */
    public function update($new_instance, $old_instance) {
    
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        require_once PODS_DIR . 'ui/admin/widgets/pods_single_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
