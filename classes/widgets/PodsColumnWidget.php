<?php
class PodsColumnWidget extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsColumnWidget() {
        $this->WP_Widget(
            'pods_column_widget',
            'Pod Column',
            array('classname' => 'pods_single_widget', 'description' => "Display a single Pod item's column value"),
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
        require_once PODS_DIR . 'ui/admin/widgets/pods_column_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
