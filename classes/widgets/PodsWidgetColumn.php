<?php
class PodsWidgetColumn extends WP_Widget {

    /**
     * Register the widget
     */
    public function PodsWidgetColumn () {
        $this->WP_Widget(
            'pods_widget_column',
            'Pods Column Value',
            array( 'classname' => 'pods_widget_column', 'description' => "Display a single Pod item's column value" ),
            array( 'width' => 200 )
        );
    }

    /**
     * Output of widget
     */
    public function widget ( $args, $instance ) {
        extract( $args );

        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        $pod_type = pods_var( 'pod_type', $instance, '' );
        $slug = pods_var( 'slug', $instance, '' );
        $column = pods_var( 'column', $instance, '' );
        $helper = pods_var( 'helper', $instance, '' );

        if ( !empty( $pod_type ) && !empty( $slug ) && !empty( $column ) ) {
            $shortcode = '[pods ';
            $shortcode .= "name=\"{$pod_type}\" ";
            $shortcode .= "slug=\"{$slug}\" ";
            $shortcode .= "column=\"{$column}\" ";

            if ( !empty( $helper ) )
                $shortcode .= "helper=\"{$helper}\" ";

            $shortcode .= ']';

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
        $instance[ 'slug' ] = pods_var( 'slug', $new_instance, '' );
        $instance[ 'column' ] = pods_var( 'column', $new_instance, '' );
        $instance[ 'helper' ] = pods_var( 'helper', $new_instance, '' );

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
        $title = pods_var( 'title', $instance, '' );
        $pod_type = pods_var( 'pod_type', $instance, '' );
        $slug = pods_var( 'slug', $instance, '' );
        $column = pods_var( 'column', $instance, '' );
        $helper = pods_var( 'helper', $instance, '' );
        require PODS_DIR . 'ui/admin/widgets/column.php';
    }
}