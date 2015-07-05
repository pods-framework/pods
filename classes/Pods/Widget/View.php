<?php
/**
 * @package Pods
 * @category Widgets
 */
class Pods_Widget_View extends WP_Widget {
	
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
	public function __construct( $id_base = 'pods_widget_view', $name = 'Pods - View', $widget_options = array(), $control_options = array() ) {
		parent::__construct(
			'pods_widget_view',
			'Pods - View',
			array( 'classname' => 'pods_widget_view', 'description' => "Include a file from a theme, with caching options" ),
			array( 'width' => 200 )
		);

	}

	/**
	 * {@inheritdoc}
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		// Get widget fields
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );

		$args = array(
			'view'       => trim( pods_v( 'view', $instance, '' ) ),
			'expires'    => (int) pods_var_raw( 'expires', $instance, ( 60 * 5 ) ),
			'cache_mode' => trim( pods_v( 'cache_mode', $instance, 'none', true ) ),
			'before'     => trim( pods_v( 'before', $instance, '' ) ),
			'after'      => trim( pods_v( 'after', $instance, '' ) ),
			'shortcodes' => (int) pods_v( 'shortcodes', $instance, 0 )
		);

		if ( 0 < strlen( $args[ 'view' ] ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}

	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
