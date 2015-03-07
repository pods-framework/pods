<?php

/**
 * @package Pods\Widget
 */
class Pods_Widget_Field extends
	WP_Widget {

	/**
	 * Register the widget
	 */
	public function Pods_Widget_Field() {
		$this->WP_Widget( 'pods_widget_field',
			'Pods - Field Value',
			array( 'classname' => 'pods_widget_field', 'description' => "Display a single Pod item's field value" ),
			array( 'width' => 200 ) );
	}

	/**
	 * Output of widget
	 *
	 * @param array $args Pod args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		// Get widget fields
		$title = apply_filters( 'widget_title', $instance['title'] );

		$args = array(
			'name'       => trim( pods_v( 'pod_type', $instance, '' ) ),
			'slug'       => trim( pods_v( 'slug', $instance, '' ) ),
			'field'      => trim( pods_v( 'field', $instance, '' ) ),
			'before'     => trim( pods_v( 'before', $instance, '' ) ),
			'after'      => trim( pods_v( 'after', $instance, '' ) ),
			'shortcodes' => (int) pods_v( 'shortcodes', $instance, 0 )
		);

		if ( 0 < strlen( $args['name'] ) && 0 < strlen( $args['slug'] ) && 0 < strlen( $args['field'] ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}
	}

	/**
	 * {@inheritDocs}
	 */
	public function update( $new_instance, $old_instance ) {
		$instance               = $old_instance;
		$instance['title']      = pods_v( 'title', $new_instance, '' );
		$instance['pod_type']   = pods_v( 'pod_type', $new_instance, '' );
		$instance['slug']       = pods_v( 'slug', $new_instance, '' );
		$instance['field']      = pods_v( 'field', $new_instance, '' );
		$instance['before']     = pods_v( 'before', $new_instance, '' );
		$instance['after']      = pods_v( 'after', $new_instance, '' );
		$instance['shortcodes'] = (int) pods_v( 'shortcodes', $new_instance, 0 );

		return $instance;
	}

	/**
	 * {@inheritDocs}
	 */
	public function form( $instance ) {
		$title      = pods_v( 'title', $instance, '' );
		$pod_type   = pods_v( 'pod_type', $instance, '' );
		$slug       = pods_v( 'slug', $instance, '' );
		$field      = pods_v( 'field', $instance, '' );
		$before     = pods_v( 'before', $instance, '' );
		$after      = pods_v( 'after', $instance, '' );
		$shortcodes = (int) pods_v( 'shortcodes', $instance, 0 );

		require PODS_DIR . 'ui/admin/widgets/field.php';
	}
}
