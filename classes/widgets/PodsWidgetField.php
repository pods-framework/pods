<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetField extends WP_Widget {

    /**
     * Register the widget
     */
    public function PodsWidgetField () {
        $this->WP_Widget(
            'pods_widget_field',
            'Pods - Field Value',
            array( 'classname' => 'pods_widget_field', 'description' => "Display a single Pod item's field value" ),
            array( 'width' => 200 )
        );
    }

    /**
     * Output of widget
     */
    public function widget ( $args, $instance ) {
        extract( $args );

        // Get widget fields
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'field' => trim( pods_var_raw( 'field', $instance, '' ) ),
            'before' => trim( pods_var_raw( 'before', $instance, '' ) ),
            'after' => trim( pods_var_raw( 'after', $instance, '' ) ),
            'shortcodes' => (int) pods_var_raw( 'shortcodes', $instance, 0 )
        );

        if ( 0 < strlen( $args[ 'name' ] ) && 0 < strlen( $args[ 'slug' ] ) && 0 < strlen( $args[ 'field' ] ) ) {
            require PODS_DIR . 'ui/front/widgets.php';
        }
    }

    /**
     * Updates the new instance of widget arguments
     *
     * @returns array $instance Updated instance
     */
    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_var_raw( 'title', $new_instance, '' );
        $instance[ 'pod_type' ] = pods_var_raw( 'pod_type', $new_instance, '' );
        $instance[ 'slug' ] = pods_var_raw( 'slug', $new_instance, '' );
        $instance[ 'field' ] = pods_var_raw( 'field', $new_instance, '' );
        $instance[ 'before' ] = pods_var_raw( 'before', $new_instance, '' );
        $instance[ 'after' ] = pods_var_raw( 'after', $new_instance, '' );
        $instance[ 'shortcodes' ] = (int) pods_var_raw( 'shortcodes', $new_instance, 0 );

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $slug = pods_var_raw( 'slug', $instance, '' );
        $field = pods_var_raw( 'field', $instance, '' );
        $before = pods_var_raw( 'before', $instance, '' );
        $after = pods_var_raw( 'after', $instance, '' );
        $shortcodes = (int) pods_var_raw( 'shortcodes', $instance, 0 );

        require PODS_DIR . 'ui/admin/widgets/field.php';
    }
}
