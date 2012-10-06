<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetForm extends WP_Widget {

    public function PodsWidgetForm () {
        $this->WP_Widget(
            'pods_widget_form',
            'Pods Form',
            array( 'classname' => 'pods_widget_form', 'description' => 'Display a form for creating and editing Pod items' ),
            array( 'width' => 200 )
        );
    }

    public function widget ( $args, $instance ) {
        extract( $args );

        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        $pod_type = pods_var( 'pod_type', $instance, '' );
        $slug = pods_var( 'slug', $instance, '' );
        $fields = pods_var( 'fields', $instance, '' );
        $label = pods_var( 'label', $instance, __( 'Submit', 'pods' ), null, true );
        $thank_you = pods_var( 'thank_you', $instance, '' );

        if ( !empty( $pod_type ) ) {
            $shortcode = '[pods name="' . $pod_type . '" form="1" fields="' . $fields . '" slug="' . $slug . '" label="' . $label . '" thank_you="' . $thank_you . '"]';

            require PODS_DIR . 'ui/front/widgets/pods_widget_output.php';
        }
    }

    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_var( 'title', $new_instance, '' );
        $instance[ 'pod_type' ] = pods_var( 'pod_type', $new_instance, '' );
        $instance[ 'slug' ] = pods_var( 'slug', $new_instance, '' );
        $instance[ 'fields' ] = pods_var( 'fields', $new_instance, '' );
        $instance[ 'label' ] = pods_var( 'label', $new_instance, __( 'Submit', 'pods' ), null, true );
        $instance[ 'thank_you' ] = pods_var( 'thank_you', $new_instance, '' );

        return $instance;
    }

    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $slug = pods_var_raw( 'slug', $instance, '' );
        $fields = pods_var_raw( 'fields', $instance, '' );
        $label = pods_var_raw( 'label', $instance, __( 'Submit', 'pods' ), null, true );
        $thank_you = pods_var_raw( 'thank_you', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/form.php';
    }
}
