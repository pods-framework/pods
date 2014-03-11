<?php
/**
 * @package Pods\Widgets
 */
class Pods_Widget_View extends WP_Widget {

    /**
     * Register the widget
     */
    public function Pods_Widget_View() {
        $this->WP_Widget(
            'pods_widget_view',
            'Pods - View',
            array( 'classname' => 'pods_widget_view', 'description' => "Include a file from a theme, with caching options" ),
            array( 'width' => 200 )
        );
    }

    /**
     * {@inheritDocs}
     */
    public function widget( $args, $instance ) {
        extract( $args );

        // Get widget fields
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        $args = array(
            'view' => trim( pods_v( 'view', $instance, '' ) ),
            'expires' => (int) pods_var_raw( 'expires', $instance, ( 60 * 5 ) ),
            'cache_mode' => trim( pods_v( 'cache_mode', $instance, 'none', true ) ),
            'before' => trim( pods_v( 'before', $instance, '' ) ),
            'after' => trim( pods_v( 'after', $instance, '' ) ),
            'shortcodes' => (int) pods_v( 'shortcodes', $instance, 0 )
        );

        if ( 0 < strlen( $args[ 'view' ] ) )
            require PODS_DIR . 'ui/front/widgets.php';
    }

    /**
     * {@inheritDocs}
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = pods_v( 'title', $new_instance, '' );
        $instance[ 'view' ] = pods_v( 'view', $new_instance, '' );
        $instance[ 'expires' ] = (int) pods_var_raw( 'expires', $new_instance, ( 60 * 5 ) );
        $instance[ 'cache_mode' ] = pods_v( 'cache_mode', $new_instance, 'none', true );
        $instance[ 'before' ] = pods_v( 'before', $new_instance, '' );
        $instance[ 'after' ] = pods_v( 'after', $new_instance, '' );
        $instance[ 'shortcodes' ] = (int) pods_v( 'shortcodes', $new_instance, 0 );
        return $instance;
    }

    /**
     * {@inheritDocs}
     */
    public function form( $instance ) {
        $title = pods_v( 'title', $instance, '' );
        $view = pods_v( 'view', $instance, '' );
        $expires = (int) pods_var_raw( 'expires', $instance, ( 60 * 5 ) );
        $cache_mode = pods_v( 'cache_mode', $instance, 'none', true );
        $before = pods_v( 'before', $instance, '' );
        $after = pods_v( 'after', $instance, '' );
        $shortcodes = (int) pods_v( 'shortcodes', $instance, 0 );

        require PODS_DIR . 'ui/admin/widgets/view.php';
    }
}
