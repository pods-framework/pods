<?php
/**
 * @package Pods\Widgets
 */
class Pods_Widget_Single extends WP_Widget {

    /**
     * Register the widget
     */
    public function Pods_Widget_Single () {
        $this->WP_Widget(
            'pods_widget_single',
            'Pods - Single Item',
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

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'template' => trim( pods_var_raw( 'template', $instance, '' ) ),
            'before' => trim( pods_var_raw( 'before', $instance, '' ) ),
            'after' => trim( pods_var_raw( 'after', $instance, '' ) ),
            'shortcodes' => (int) pods_var_raw( 'shortcodes', $instance, 0 )
        );

        $content = trim( pods_var_raw( 'template_custom', $instance, '' ) );

        if ( 0 < strlen( $args[ 'name' ] ) && 0 < strlen( $args[ 'slug' ] ) && ( 0 < strlen( $args[ 'template' ] ) || 0 < strlen( $content ) ) ) {
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
        $instance[ 'template' ] = pods_var_raw( 'template', $new_instance, '' );
        $instance[ 'template_custom' ] = pods_var_raw( 'template_custom', $new_instance, '' );
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
        $slug = pods_var_raw( 'slug', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $template = pods_var_raw( 'template', $instance, '' );
        $template_custom = pods_var_raw( 'template_custom', $instance, '' );
        $before = pods_var_raw( 'before', $instance, '' );
        $after = pods_var_raw( 'after', $instance, '' );
        $shortcodes = (int) pods_var_raw( 'shortcodes', $instance, 0 );

        require PODS_DIR . 'ui/admin/widgets/single.php';
    }
}
