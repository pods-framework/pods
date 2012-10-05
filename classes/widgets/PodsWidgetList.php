<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetList extends WP_Widget {

    /**
     * Register the widget
     */
    public function PodsWidgetList () {
        $this->WP_Widget(
            'pods_widget_list',
            'Pods List',
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
        $pod_type = pods_var( 'pod_type', $instance, '' );
        $template = pods_var( 'template', $instance, '' );
        $template_custom = pods_var( 'template_custom', $instance, '' );
        $limit = pods_var( 'limit', $instance, '' );
        $orderby = pods_var( 'orderby', $instance, '' );
        $direction = pods_var( 'direction', $instance, '' );
        $where = pods_var( 'where', $instance, '' );

        if ( !empty( $pod_type ) && !empty( $template ) ) {
            $shortcode = '[pods ';
            $shortcode .= "name=\"{$pod_type}\" ";
            $shortcode .= "template=\"{$template}\" ";

            if ( !empty( $limit ) )
                $shortcode .= "limit=\"{$limit}\" ";
            if ( !empty( $orderby ) && !empty( $direction ) )
                $shortcode .= "orderby=\"{$orderby} {$direction}\" ";
            if ( !empty( $where ) )
                $shortcode .= "where=\"{$where}\" ";

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
        $instance[ 'limit' ] = pods_var( 'limit', $new_instance, '' );
        $instance[ 'orderby' ] = pods_var( 'orderby', $new_instance, '' );
        $instance[ 'direction' ] = pods_var( 'direction', $new_instance, '' );
        $instance[ 'where' ] = pods_var( 'where', $new_instance, '' );

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
        $limit = pods_var_raw( 'limit', $instance, '' );
        $orderby = pods_var_raw( 'orderby', $instance, '' );
        $direction = pods_var_raw( 'direction', $instance, '' );
        $where = pods_var_raw( 'where', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/list.php';
    }
}
