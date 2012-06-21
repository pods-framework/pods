<?php
class PodsWidgetSingle extends WP_Widget {
    /**
     * Register the widget
     */
    public function PodsWidgetSingle() {
        $this->WP_Widget(
            'pods_widget_single',
            'Pods Single Item',
            array('classname' => 'pods_widget_single', 'description' => 'Display a Single Pod Item'),
            array('width' => 200)
        );
    }

    /**
     * Output of widget
     */
    public function widget($args, $instance) {
        extract($args);

        // Get widget field values
        $title    = apply_filters('widget_title', $instance['title']);
        $pod_type = $instance['pod_type'];
        $template = $instance['template'];
        $slug     = $instance['slug'];
        $helper   = $instance['helper'];

        if (!empty($pod_type) && !empty($template) && !empty($slug)) {
            $shortcode = '[pods ';
            $shortcode .= "name=\"{$pod_type}\" ";
            $shortcode .= "template=\"{$template}\" ";
            $shortcode .= "slug=\"{$slug}\" ";

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
        require PODS_DIR . 'ui/admin/widgets/single.php';
    }
}