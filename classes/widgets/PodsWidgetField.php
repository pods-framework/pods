<?php

/**
 * @package Pods\Widgets
 */
class PodsWidgetField extends WP_Widget {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = [], $control_options = [] ) {
		parent::__construct( 'pods_widget_field', __( 'Pods - Field Value', 'pods' ), [
			'classname'   => 'pods_widget_field',
			'description' => __( "Display a single Pod item's field value", 'pods' ),
		], [ 'width' => 200 ] );
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

		$args = [
			'name'        => trim( (string) pods_v( 'pod_type', $instance, '' ) ),
			'slug'        => trim( (string) pods_v( 'slug', $instance, '' ) ),
			'use_current' => trim( (string) pods_v( 'use_current', $instance, '' ) ),
			'field'       => trim( (string) pods_v( 'field', $instance, '' ) ),
		];

		if (
			(
				(
					0 < strlen( $args['name'] )
					&& 0 < strlen( $args['slug'] )
				)
				|| 0 < strlen( $args['use_current'] )
			)
			&& 0 < strlen( $args['field'] )
		) {
			require PODS_DIR . 'ui/front/widgets.php';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']       = pods_v( 'title', $new_instance, '' );
		$instance['pod_type']    = pods_v( 'pod_type', $new_instance, '' );
		$instance['slug']        = pods_v( 'slug', $new_instance, '' );
		$instance['use_current'] = pods_v( 'use_current', $new_instance, '' );
		$instance['field']       = pods_v( 'field', $new_instance, '' );

		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function form( $instance ) {
		$title       = pods_v( 'title', $instance, '' );
		$pod_type    = pods_v( 'pod_type', $instance, '' );
		$slug        = pods_v( 'slug', $instance, '' );
		$use_current = pods_v( 'use_current', $instance, '' );
		$field       = pods_v( 'field', $instance, '' );

		require PODS_DIR . 'ui/admin/widgets/field.php';
	}
}
