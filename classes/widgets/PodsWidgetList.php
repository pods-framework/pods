<?php
class PodsWidgetList extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsWidgetList() {
        $this->WP_Widget(
            'pods_widget_list',
            'Pods List',
            array('classname' => 'pods_widget_list', 'description' => 'Display multiple Pod items'),
            array('width' => 200)
        );
    }

    /**
     * Output of widget
     */
    public function widget($args, $instance) {
        extract($args);

        // Get widget fields
        $title     = apply_filters('widget_title', $instance['title']);
        $pod_type  = $instance['pod_type'];
        $template  = $instance['template'];
        $limit     = $instance['limit'];
        $orderby   = $instance['orderby'];
        $direction = $instance['direction'];
        $where     = $instance['where'];
        $helper    = $instance['helper'];

        if (!empty($pod_type) && !empty($template)) {
            $shortcode = '[pods ';
            $shortcode .= "name=\"{$pod_type}\" ";
            $shortcode .= "template=\"{$template}\" ";

            if (!empty($limit))
                $shortcode .= "limit=\"{$limit}\" ";
            if (!empty($orderby) && !empty($direction))
                $shortcode .= "orderby=\"{$orderby} {$direction}\" ";
            if (!empty($where))
                $shortcode .= "where=\"{$where}\" ";
            if (!empty($helper))
                $shortcode .= "helper=\"{$helper}\" ";

            $shortcode .= "]";

            require PODS_DIR . 'ui/front/widgets/pods_widget_output.php';
        }
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
        require_once PODS_DIR . 'ui/admin/widgets/pods_widget_list_form.php'; 
    }
}

// vim: set ts=4 sw=4 expandtab :
?>
