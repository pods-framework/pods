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
        $instance['template'] = $new_instance['template'];
        $instance['slug']     = $new_instance['slug'];
        $instance['helper']   = $new_instance['helper'];

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title    = esc_attr($instance['title']);
        $pod_type = esc_attr($instance['pod_type']);
        $template = esc_attr($instance['template']);
        $slug     = esc_attr($instance['slug']);
        $helper   = esc_attr($instance['helper']);
        require PODS_DIR . 'ui/admin/widgets/pods_single_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
