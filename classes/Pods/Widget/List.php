<?php
/**
 * @package Pods\Widgets
 */
class Pods_Widget_List extends WP_Widget {

    /**
     * Register the widget
     */
    public function Pods_Widget_List () {
        $this->WP_Widget(
            'pods_widget_list',
            'Pods - List Items',
            array( 'classname' => 'pods_widget_list', 'description' => 'Display multiple Pod items' ),
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
            'template' => trim( pods_var_raw( 'template', $instance, '' ) ),
            'limit' => (int) pods_var_raw( 'limit', $instance, 15, null, true ),
            'orderby' => trim( pods_var_raw( 'orderby', $instance, '' ) ),
            'where' => trim( pods_var_raw( 'where', $instance, '' ) ),
            'expires' => (int) trim( pods_var_raw( 'expires', $instance, ( 60 * 5 ) ) ),
            'cache_mode' => trim( pods_var_raw( 'cache_mode', $instance, 'none', null, true ) ),
        	'filters' => trim( pods_var_raw( 'filters', $instance, '' ) ),
        	'filters_label' => trim( pods_var_raw( 'filters_label', $instance, '' ) ),
        	'filters_location' => pods_var_raw( 'filters_location', $instance, 'before', null, true ),
        	'pagination' => (int) pods_var_raw( 'pagination', $instance, 0 ),
        	'pagination_label' => trim( pods_var_raw( 'pagination_label', $instance, '' ) ),
			'pagination_location' => pods_var_raw( 'pagination_location', $instance, 'after', null, true ),
			'pagination_type' => pods_var_raw( 'pagination_type', $instance, 'advanced', null, true ),
            'before' => trim( pods_var_raw( 'before', $instance, '' ) ),
            'after' => trim( pods_var_raw( 'after', $instance, '' ) ),
            'shortcodes' => (int) pods_var_raw( 'shortcodes', $instance, 0 )
        );

        $content = trim( pods_var_raw( 'template_custom', $instance, '' ) );

        if ( 0 < strlen( $args[ 'name' ] ) && ( 0 < strlen( $args[ 'template' ] ) || 0 < strlen( $content ) ) ) {
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
        $instance[ 'template' ] = pods_var_raw( 'template', $new_instance, '' );
        $instance[ 'template_custom' ] = pods_var_raw( 'template_custom', $new_instance, '' );
        $instance[ 'limit' ] = (int) pods_var_raw( 'limit', $new_instance, 15, null, true );
        $instance[ 'orderby' ] = pods_var_raw( 'orderby', $new_instance, '' );
        $instance[ 'where' ] = pods_var_raw( 'where', $new_instance, '' );
        $instance[ 'expires' ] = (int) pods_var_raw( 'expires', $new_instance, ( 60 * 5 ) );
        $instance[ 'cache_mode' ] = pods_var_raw( 'cache_mode', $new_instance, 'none' );
        $instance[ 'filters' ] = pods_var_raw( 'filters', $new_instance, '' );
        $instance[ 'filters_label' ] = pods_var_raw( 'filters_label', $new_instance, '' );
        $instance[ 'filters_location' ] = pods_var_raw( 'filters_location', $new_instance, 'before', null, true );
        $instance[ 'pagination' ] = (int) pods_var_raw( 'pagination', $new_instance, 0 );
        $instance[ 'pagination_label' ] = pods_var_raw( 'pagination_label', $new_instance, '' );
        $instance[ 'pagination_location' ] = pods_var_raw( 'pagination_location', $new_instance, 'after', null, true );
        $instance[ 'pagination_type' ] = pods_var_raw( 'pagination_type', $new_instance, 'advanced', null, true );
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
        $template = pods_var_raw( 'template', $instance, '' );
        $template_custom = pods_var_raw( 'template_custom', $instance, '' );
        $limit = (int) pods_var_raw( 'limit', $instance, 15, null, true );
        $orderby = pods_var_raw( 'orderby', $instance, '' );
        $where = pods_var_raw( 'where', $instance, '' );
        $expires = (int) pods_var_raw( 'expires', $instance, ( 60 * 5 ) );
        $cache_mode = pods_var_raw( 'cache_mode', $instance, 'none' );
        $filters = pods_var_raw( 'filters', $instance, '' );
        $filters_label = pods_var_raw( 'filters_label', $instance, '' );
        $filters_location = pods_var_raw( 'filters_location', $instance, 'before', null, true );
        $pagination = (int) pods_var_raw( 'pagination', $instance, 0 );
        $pagination_label = pods_var_raw( 'pagination_label', $instance, '' );
        $pagination_location = pods_var_raw( 'pagination_location', $instance, 'after', null, true );
        $pagination_type = pods_var_raw( 'pagination_type', $instance, 'advanced', null, true );
        $before = pods_var_raw( 'before', $instance, '' );
        $after = pods_var_raw( 'after', $instance, '' );
        $shortcodes = (int) pods_var_raw( 'shortcodes', $instance, 0 );

        require PODS_DIR . 'ui/admin/widgets/list.php';
    }
}
