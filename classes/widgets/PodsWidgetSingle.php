<?php

/**
 * @package Pods\Widgets
 */
class PodsWidgetSingle extends WP_Widget {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {

		parent::__construct( 'pods_widget_single', __( 'Pods - Single Item', 'pods' ), array(
			'classname'   => 'pods_widget_single',
			'description' => __( 'Display a Single Pod Item', 'pods' ),
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
			'name'        => trim( pods_v( 'pod_type', $instance, '' ) ),
			'slug'        => trim( pods_v( 'slug', $instance, '' ) ),
			'use_current' => trim( pods_v( 'use_current', $instance, '' ) ),
			'template'    => trim( pods_v( 'template', $instance, '' ) ),
		);

		$content = trim( pods_v( 'template_custom', $instance, '' ) );

		if ( ( ( 0 < strlen( $args['name'] ) && 0 < strlen( $args['slug'] ) ) || 0 < strlen( $args['use_current'] ) ) && ( 0 < strlen( $args['template'] ) || 0 < strlen( $content ) ) ) {
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
		$instance['slug']            = pods_v( 'slug', $new_instance, '' );
		$instance['use_current']     = pods_v( 'use_current', $new_instance, '' );
		$instance['template']        = pods_v( 'template', $new_instance, '' );
		$instance['template_custom'] = pods_v( 'template_custom', $new_instance, '' );

		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function form( $instance ) {

		$title           = pods_v( 'title', $instance, '' );
		$slug            = pods_v( 'slug', $instance, '' );
		$use_current     = pods_v( 'use_current', $instance, '' );
		$pod_type        = pods_v( 'pod_type', $instance, '' );
		$template        = pods_v( 'template', $instance, '' );
		$template_custom = pods_v( 'template_custom', $instance, '' );

		require PODS_DIR . 'ui/admin/widgets/single.php';
	}
}
