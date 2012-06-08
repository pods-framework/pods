<?php
class PodsWidgetColumn extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsWidgetColumn() {
        $this->WP_Widget(
            'pods__widget_column',
            'Pods Column Value',
            array('classname' => 'pods_widget_column', 'description' => "Display a single Pod item's column value"),
            array('width' => 200)
        );
    }

    /**
     * Output of widget
     */
    public function widget($args, $instance) {
        extract($args);
        require_once PODS_DIR . 'ui/front/widgets/pods_widget_output.php';
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
        require_once PODS_DIR . 'ui/admin/widgets/pods_widget_column_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
