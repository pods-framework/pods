<?php

/**
 * @package Pods\Widgets
 */
class PodsWidgetForm extends WP_Widget {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {

		parent::__construct( 'pods_widget_form', __( 'Pods - Form', 'pods' ), array(
			'classname'   => 'pods_widget_form',
			'description' => __( 'Display a form for creating and editing Pod items', 'pods' ),
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
			'name'      => trim( (string) pods_v( 'pod_type', $instance, '' ) ),
			'slug'      => trim( (string) pods_v( 'slug', $instance, '' ) ),
			'fields'    => trim( (string) pods_v( 'fields', $instance, '' ) ),
			'label'     => trim( (string) pods_v( 'label', $instance, __( 'Submit', 'pods' ), true ) ),
			'thank_you' => trim( (string) pods_v( 'thank_you', $instance, '' ) ),
			'form'      => 1,
		);

		if ( 0 < strlen( $args['name'] ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']     = pods_v( 'title', $new_instance, '' );
		$instance['pod_type']  = pods_v( 'pod_type', $new_instance, '' );
		$instance['slug']      = pods_v( 'slug', $new_instance, '' );
		$instance['fields']    = pods_v( 'fields', $new_instance, '' );
		$instance['label']     = pods_v( 'label', $new_instance, __( 'Submit', 'pods' ), true );
		$instance['thank_you'] = pods_v( 'thank_you', $new_instance, '' );

		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function form( $instance ) {

		$title     = pods_v( 'title', $instance, '' );
		$pod_type  = pods_v( 'pod_type', $instance, '' );
		$slug      = pods_v( 'slug', $instance, '' );
		$fields    = pods_v( 'fields', $instance, '' );
		$label     = pods_v( 'label', $instance, __( 'Submit', 'pods' ), true );
		$thank_you = pods_v( 'thank_you', $instance, '' );

		require PODS_DIR . 'ui/admin/widgets/form.php';
	}
}
