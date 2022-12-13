<?php

/**
 * @package Pods\Widgets
 */
class PodsWidgetList extends WP_Widget {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {

		parent::__construct( 'pods_widget_list', __( 'Pods - List Items', 'pods' ), array(
			'classname'   => 'pods_widget_list',
			'description' => __( 'Display multiple Pod items', 'pods' ),
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
			'name'       => trim( (string) pods_v( 'pod_type', $instance, '' ) ),
			'template'   => trim( (string) pods_v( 'template', $instance, '' ) ),
			'limit'      => (int) pods_v( 'limit', $instance, 15, true ),
			'orderby'    => trim( (string) pods_v( 'orderby', $instance, '' ) ),
			'where'      => trim( (string) pods_v( 'where', $instance, '' ) ),
			'expires'    => (int) trim( (string) pods_v( 'expires', $instance, ( 60 * 5 ) ) ),
			'cache_mode' => trim( (string) pods_v( 'cache_mode', $instance, 'none', true ) ),
		);

		$content = trim( (string) pods_v( 'template_custom', $instance, '' ) );

		if ( 0 < strlen( $args['name'] ) && ( 0 < strlen( $args['template'] ) || 0 < strlen( $content ) ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']           = pods_v( 'title', $new_instance, '' );
		$instance['pod_type']        = pods_v( 'pod_type', $new_instance, '' );
		$instance['template']        = pods_v( 'template', $new_instance, '' );
		$instance['template_custom'] = pods_v( 'template_custom', $new_instance, '' );
		$instance['limit']           = (int) pods_v( 'limit', $new_instance, 15, true );
		$instance['orderby']         = pods_v( 'orderby', $new_instance, '' );
		$instance['where']           = pods_v( 'where', $new_instance, '' );
		$instance['expires']         = (int) pods_v( 'expires', $new_instance, ( 60 * 5 ) );
		$instance['cache_mode']      = pods_v( 'cache_mode', $new_instance, 'none' );
		$instance['before_content']  = pods_v( 'before_content', $new_instance, '' );
		$instance['after_content']   = pods_v( 'after_content', $new_instance, '' );

		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function form( $instance ) {

		$title           = pods_v( 'title', $instance, '' );
		$pod_type        = pods_v( 'pod_type', $instance, '' );
		$template        = pods_v( 'template', $instance, '' );
		$template_custom = pods_v( 'template_custom', $instance, '' );
		$limit           = (int) pods_v( 'limit', $instance, 15, true );
		$orderby         = pods_v( 'orderby', $instance, '' );
		$where           = pods_v( 'where', $instance, '' );
		$expires         = (int) pods_v( 'expires', $instance, ( 60 * 5 ) );
		$cache_mode      = pods_v( 'cache_mode', $instance, 'none' );
		$before_content  = pods_v( 'before_content', $instance, '' );
		$after_content   = pods_v( 'after_content', $instance, '' );

		require PODS_DIR . 'ui/admin/widgets/list.php';
	}
}
