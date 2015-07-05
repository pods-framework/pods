<?php
/**
 * @package Pods
 * @category Widgets
 */
class Pods_Widget_Form extends WP_Widget {

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
	public function __construct( $id_base = 'pods_widget_form', $name = 'Pods - Form', $widget_options = array(), $control_options = array() ) {
		parent::__construct(
			'pods_widget_form',
			'Pods - Form',
			array( 'classname' => 'pods_widget_form', 'description' => 'Display a form for creating and editing Pod items' ),
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
			'name'       => trim( pods_v( 'pod_type', $instance, '' ) ),
			'slug'       => trim( pods_v( 'slug', $instance, '' ) ),
			'fields'     => trim( pods_v( 'fields', $instance, '' ) ),
			'label'      => trim( pods_var_raw( 'label', $instance, __( 'Submit', 'pods' ), null, true ) ),
			'thank_you'  => trim( pods_v( 'thank_you', $instance, '' ) ),
			'shortcodes' => (int) pods_v( 'shortcodes', $instance, 0 ),
			'before'     => trim( pods_v( 'before', $instance, '' ) ),
			'after'      => trim( pods_v( 'after', $instance, '' ) ),
			'form'       => 1
		);

		if ( 0 < strlen( $args[ 'name' ] ) ) {
			require PODS_DIR . 'ui/front/widgets.php';
		}

	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance[ 'title' ] = pods_v( 'title', $new_instance, '' );
		$instance[ 'pod_type' ] = pods_v( 'pod_type', $new_instance, '' );
		$instance[ 'slug' ] = pods_v( 'slug', $new_instance, '' );
		$instance[ 'fields' ] = pods_v( 'fields', $new_instance, '' );
		$instance[ 'label' ] = pods_var_raw( 'label', $new_instance, __( 'Submit', 'pods' ), null, true );
		$instance[ 'thank_you' ] = pods_v( 'thank_you', $new_instance, '' );
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
		$pod_type = pods_v( 'pod_type', $instance, '' );
		$slug = pods_v( 'slug', $instance, '' );
		$fields = pods_v( 'fields', $instance, '' );
		$label = pods_var_raw( 'label', $instance, __( 'Submit', 'pods' ), null, true );
		$thank_you = pods_v( 'thank_you', $instance, '' );
		$before = pods_v( 'before', $instance, '' );
		$after = pods_v( 'after', $instance, '' );
		$shortcodes = (int) pods_v( 'shortcodes', $instance, 0 );

		require PODS_DIR . 'ui/admin/widgets/form.php';

	}

}
