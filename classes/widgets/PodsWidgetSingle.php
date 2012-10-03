<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetSingle extends WP_Widget {

    /**
     * Register the widget
     */
    public function PodsWidgetSingle () {
        $this->WP_Widget(
            'pods_widget_single',
            'Pods Single Item',
            array( 'classname' => 'pods_widget_single', 'description' => 'Display a Single Pod Item' ),
            array( 'width' => 200 )
        );
    }

    /**
     * Output of widget
     */
    public function widget ( $args, $instance ) {
        extract( $args );

        // Get widget field values
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        $pod_type = pods_var( 'pod_type', $instance, '' );
        $template = pods_var( 'template', $instance, '' );
        $template_custom = pods_var( 'template_custom', $instance, '' );
        $slug = pods_var( 'slug', $instance, '' );

        if ( !empty( $pod_type ) && !empty( $template ) && !empty( $slug ) ) {
            $shortcode = '[pods ';
            $shortcode .= "name=\"{$pod_type}\" ";
            $shortcode .= "template=\"{$template}\" ";
            $shortcode .= "slug=\"{$slug}\" ";
            $shortcode .= "]";

            if ( !empty ( $template_custom ) )
                $shortcode .= $template_custom . '[/pods]';

            require PODS_DIR . 'ui/front/widgets/pods_widget_output.php';
        }

    }

    /**
     * Updates the new instance of widget arguments
     *
     * @returns array $instance Updated instance
     */
    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance[ 'title' ] = pods_var( 'title', $new_instance, '' );
        $instance[ 'pod_type' ] = pods_var( 'pod_type', $new_instance, '' );
        $instance[ 'template' ] = pods_var( 'template', $new_instance, '' );
        $instance[ 'template_custom' ] = pods_var( 'template_custom', $new_instance, '' );
        $instance[ 'slug' ] = pods_var( 'slug', $new_instance, '' );

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $template = pods_var_raw( 'template', $instance, '' );
        $template_custom = pods_var_raw( 'template_custom', $instance, '' );
        $slug = pods_var_raw( 'slug', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/single.php';
    }
}
