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
            'pods_widget_view',
            'Pods - View',
            array( 'classname' => 'pods_widget_view', 'description' => "Include a file from a theme or another site, with caching options" ),
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
            'view' => trim( pods_var_raw( 'view', $instance, '' ) ),
            'expires' => (int) pods_var_raw( 'expires', $instance, 0 ),
            'cache_mode' => trim( pods_var_raw( 'cache_mode', $instance, 'none', null, true ) )
        );

        if ( 0 < strlen( $args[ 'view' ] ) )
            require PODS_DIR . 'ui/front/widgets.php';
    }

    /**
     * Updates the new instance of widget arguments
     *
     * @returns array $instance Updated instance
     */
    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_var_raw( 'title', $new_instance, '' );
        $instance[ 'view' ] = pods_var_raw( 'view', $new_instance, '' );
        $instance[ 'expires' ] = (int) pods_var_raw( 'expires', $new_instance, 0 );
        $instance[ 'cache_mode' ] = pods_var_raw( 'cache_mode', $new_instance, 'none', null, true );

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $view = pods_var_raw( 'view', $instance, '' );
        $expires = (int) pods_var_raw( 'expires', $instance, 0 );
        $cache_mode = pods_var_raw( 'cache_mode', $instance, 'none', null, true );

        require PODS_DIR . 'ui/admin/widgets/view.php';
    }
}
