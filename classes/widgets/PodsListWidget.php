<?php
class PodsListWidget extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsListWidget() {
        $this->WP_Widget(
            'pods_list_widget',
            'Pod List',
            array('classname' => 'pods_single_widget', 'description' => 'Display multiple Pod items'),
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
        require_once PODS_DIR . 'ui/admin/widgets/pods_list_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
