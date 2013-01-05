<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetForm extends WP_Widget {

    public function PodsWidgetForm () {
        $this->WP_Widget(
            'pods_widget_form',
            'Pods - Form',
            array( 'classname' => 'pods_widget_form', 'description' => 'Display a form for creating and editing Pod items' ),
            array( 'width' => 200 )
        );
    }

    public function widget ( $args, $instance ) {
        extract( $args );

        // Get widget fields
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'fields' => trim( pods_var_raw( 'fields', $instance, '' ) ),
            'label' => trim( pods_var_raw( 'label', $instance, __( 'Submit', 'pods' ), null, true ) ),
            'thank_you' => trim( pods_var_raw( 'thank_you', $instance, '' ) ),
            'form' => 1
        );

        if ( 0 < strlen( $args[ 'name' ] ) ) {
            require PODS_DIR . 'ui/front/widgets.php';
        }
    }

    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_var_raw( 'title', $new_instance, '' );
        $instance[ 'pod_type' ] = pods_var_raw( 'pod_type', $new_instance, '' );
        $instance[ 'slug' ] = pods_var_raw( 'slug', $new_instance, '' );
        $instance[ 'fields' ] = pods_var_raw( 'fields', $new_instance, '' );
        $instance[ 'label' ] = pods_var_raw( 'label', $new_instance, __( 'Submit', 'pods' ), null, true );
        $instance[ 'thank_you' ] = pods_var_raw( 'thank_you', $new_instance, '' );

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
