<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetField extends WP_Widget {

	/**
	 * Register the widget
	 *
	 * @since 2.5.4
	 *
	 * Note: params are totally ignored. Included for the sake of strict standards.
	 *
	 *
	 * @param string $id_base         Optional Base ID for the widget, lowercase and unique. If left empty,
	 *                                a portion of the widget's class name will be used Has to be unique.
	 * @param string $name            Name for the widget displayed on the configuration page.
	 * @param array  $widget_options  Optional. Widget options. See {@see wp_register_sidebar_widget()} for
	 *                                information on accepted arguments. Default empty array.
	 * @param array  $control_options Optional. Widget control options. See {@see wp_register_widget_control()}
	 *                                for information on accepted arguments. Default empty array.
	 */
	public function __construct( $id_base = 'pods_widget_field', $name = 'Pods - Field Value', $widget_options = array(), $control_options = array() ) {
	    parent::__construct(
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
        $title = apply_filters( 'widget_title', pods_v( 'title', $instance ) );

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'field' => trim( pods_var_raw( 'field', $instance, '' ) )
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

        require PODS_DIR . 'ui/admin/widgets/field.php';
    }
}
