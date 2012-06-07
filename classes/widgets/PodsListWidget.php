<?php
class PodsListWidget extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsListWidget() {
        $this->WP_Widget(
            'pods_list_widget',
            'Pods List',
            array('classname' => 'pods_list_widget', 'description' => 'Display multiple Pod items'),
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

        $instance['title']     = $new_instance['title'];
        $instance['pod_type']  = $new_instance['pod_type'];
        $instance['template']  = $new_instance['template'];
        $instance['limit']     = $new_instance['limit'];
        $instance['orderby']   = $new_instance['orderby'];
        $instance['direction'] = $new_instance['direction'];
        $instance['where']     = $new_instance['where'];
        $instance['helper']    = $new_instance['helper'];

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title     = $instance['title'];
        $pod_type  = $instance['pod_type'];
        $template  = $instance['template'];
        $limit     = $instance['limit'];
        $orderby   = $instance['orderby'];
        $direction = $instance['direction'];
        $where     = $instance['where'];
        $helper    = $instance['helper'];
        require_once PODS_DIR . 'ui/admin/widgets/pods_list_widget_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
