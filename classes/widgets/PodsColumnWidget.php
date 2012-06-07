<?php
class PodsColumnWidget extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsColumnWidget() {
        $this->WP_Widget(
            'pods_column_widget',
            'Pod Column',
            array('classname' => 'pods_column_widget', 'description' => "Display a single Pod item's column value"),
            array('width' => 200)
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
        $instance = $old_instance;
        $instance['title']    = $new_instance['title'];
        $instance['pod_type'] = $new_instance['pod_type'];
        $instance['slug']     = $new_instance['slug'];
        $instance['column']   = $new_instance['column'];
        $instance['helper']   = $new_instance['helper'];

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title    = $instance['title'];
        $pod_type = $instance['pod_type'];
        $slug     = $instance['slug'];
        $column   = $instance['column'];
        $helper   = $instance['helper'];
        require_once PODS_DIR . 'ui/admin/widgets/pods_column_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
