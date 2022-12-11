<?php

/**
 * @package Pods\Widgets
 */
class PodsWidgetView extends WP_Widget {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {

		parent::__construct( 'pods_widget_view', __( 'Pods - View', 'pods' ), array(
			'classname'   => 'pods_widget_view',
			'description' => __( 'Include a file from a theme, with caching options', 'pods' ),
		), array( 'width' => 200 ) );

	}

	/**
	 * {@inheritdoc}
	 */
	public function widget( $args, $instance ) {

		// Setup basic widget parameters.
		$before_widget  = pods_v( 'before_widget', $args );
		$after_widget   = pods_v( 'after_widget', $args );
		$before_title   = pods_v( 'before_title', $args );
		$title          = apply_filters( 'widget_title', pods_v( 'title', $instance ) );
		$after_title    = pods_v( 'after_title', $args );
		$before_content = pods_v( 'before_content', $instance );
		$after_content  = pods_v( 'after_content', $instance );

		$args = array(
			'view'       => trim( (string) pods_v( 'view', $instance, '' ) ),
			'expires'    => (int) pods_v( 'expires', $instance, ( 60 * 5 ) ),
			'cache_mode' => trim( (string) pods_v( 'cache_mode', $instance, 'none', true ) ),
		);

		if ( 0 < strlen( $args['view'] ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']      = pods_v( 'title', $new_instance, '' );
		$instance['view']       = pods_v( 'view', $new_instance, '' );
		$instance['expires']    = (int) pods_v( 'expires', $new_instance, ( 60 * 5 ) );
		$instance['cache_mode'] = pods_v( 'cache_mode', $new_instance, 'none', true );

		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function form( $instance ) {

		$title      = pods_v( 'title', $instance, '' );
		$view       = pods_v( 'view', $instance, '' );
		$expires    = (int) pods_v( 'expires', $instance, ( 60 * 5 ) );
		$cache_mode = pods_v( 'cache_mode', $instance, 'none', true );

		require PODS_DIR . 'ui/admin/widgets/view.php';
	}
}
