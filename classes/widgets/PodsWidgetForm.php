<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetForm extends WP_Widget {

    public function PodsWidgetForm () {
        $this->WP_Widget(
            'pods_widget_form',
            'Pods Form',
            array( 'classname' => 'pods_widget_form', 'description' => 'Display a form for creating Pod items' ),
            array( 'width' => 200 )
        );
    }

    public function widget ( $args, $instance ) {
        extract( $args );

        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        $pod_type = pods_var( 'pod_type', $instance, '' );

        if ( !empty( $pod_type ) ) {
            $shortcode = '[pods name="' . $pod_type . '" form="true"]';
            require PODS_DIR . 'ui/front/widgets/pods_widget_output.php';
        }
    }

    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_var( 'title', $new_instance, '' );
        $instance[ 'pod_type' ] = pods_var( 'pod_type', $new_instance, '' );

        return $instance;
    }

    public function form ( $instance ) {
        $title = pods_var( 'title', $instance, '' );
        $pod_type = pods_var( 'pod_type', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/form.php';
    }
}
