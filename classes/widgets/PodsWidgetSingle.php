<?php
/**
 * @package Pods\Widgets
 */
class PodsWidgetSingle extends WP_Widget {

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
	public function __construct( $id_base = 'pods_widget_single', $name = 'Pods - Single Item', $widget_options = array(), $control_options = array() ) {
	    parent::__construct(
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
        $title = apply_filters( 'widget_title', pods_v( 'title', $instance ) );

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'use_current' => trim( pods_var_raw( 'use_current', $instance, '' ) ),
            'template' => trim( pods_var_raw( 'template', $instance, '' ) )
        );

        $content = trim( pods_var_raw( 'template_custom', $instance, '' ) );

        if ( ( ( 0 < strlen( $args[ 'name' ] ) && 0 < strlen( $args[ 'slug' ] ) ) || 0 < strlen( $args[ 'use_current' ] ) ) && ( 0 < strlen( $args[ 'template' ] ) || 0 < strlen( $content ) ) ) {
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
        $instance[ 'use_current' ] = pods_var_raw( 'use_current', $new_instance, '' );
        $instance[ 'template' ] = pods_var_raw( 'template', $new_instance, '' );
        $instance[ 'template_custom' ] = pods_var_raw( 'template_custom', $new_instance, '' );

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $slug = pods_var_raw( 'slug', $instance, '' );
        $use_current = pods_var_raw( 'use_current', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $template = pods_var_raw( 'template', $instance, '' );
        $template_custom = pods_var_raw( 'template_custom', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/single.php';
    }
}
