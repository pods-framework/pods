<?php
/**
 * @package Pods
 * @category Widgets
 */
class Pods_Widget_List extends WP_Widget {

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
	public function __construct( $id_base = 'pods_widget_list', $name = 'Pods - List Items', $widget_options = array(), $control_options = array() ) {
		parent::__construct(
			'pods_widget_list',
			'Pods - List Items',
			array( 'classname' => 'pods_widget_list', 'description' => 'Display multiple Pod items' ),
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
			'name'                => trim( pods_v( 'pod_type', $instance, '' ) ),
			'template'            => trim( pods_v( 'template', $instance, '' ) ),
			'limit'               => (int) pods_v( 'limit', $instance, 15, true ),
			'orderby'             => trim( pods_v( 'orderby', $instance, '' ) ),
			'where'               => trim( pods_v( 'where', $instance, '' ) ),
			'expires'             => (int) trim( pods_var_raw( 'expires', $instance, ( 60 * 5 ) ) ),
			'cache_mode'          => trim( pods_v( 'cache_mode', $instance, 'none', true ) ),
			'filters'             => trim( pods_v( 'filters', $instance, '' ) ),
			'filters_label'       => trim( pods_v( 'filters_label', $instance, '' ) ),
			'filters_location'    => pods_v( 'filters_location', $instance, 'before', true ),
			'pagination'          => (int) pods_v( 'pagination', $instance, 0 ),
			'pagination_label'    => trim( pods_v( 'pagination_label', $instance, '' ) ),
			'pagination_location' => pods_v( 'pagination_location', $instance, 'after', true ),
			'pagination_type'     => pods_v( 'pagination_type', $instance, 'advanced', true ),
			'before'              => trim( pods_v( 'before', $instance, '' ) ),
			'after'               => trim( pods_v( 'after', $instance, '' ) ),
			'shortcodes'          => (int) pods_v( 'shortcodes', $instance, 0 )
		);

		$content = trim( pods_v( 'template_custom', $instance, '' ) );

		if ( 0 < strlen( $args[ 'name' ] ) && ( 0 < strlen( $args[ 'template' ] ) || 0 < strlen( $content ) ) ) {
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
		$instance[ 'template' ] = pods_v( 'template', $new_instance, '' );
		$instance[ 'template_custom' ] = pods_v( 'template_custom', $new_instance, '' );
		$instance[ 'limit' ] = (int) pods_v( 'limit', $new_instance, 15, true );
		$instance[ 'orderby' ] = pods_v( 'orderby', $new_instance, '' );
		$instance[ 'where' ] = pods_v( 'where', $new_instance, '' );
		$instance[ 'expires' ] = (int) pods_var_raw( 'expires', $new_instance, ( 60 * 5 ) );
		$instance[ 'cache_mode' ] = pods_v( 'cache_mode', $new_instance, 'none' );
		$instance[ 'filters' ] = pods_v( 'filters', $new_instance, '' );
		$instance[ 'filters_label' ] = pods_v( 'filters_label', $new_instance, '' );
		$instance[ 'filters_location' ] = pods_v( 'filters_location', $new_instance, 'before', true );
		$instance[ 'pagination' ] = (int) pods_v( 'pagination', $new_instance, 0 );
		$instance[ 'pagination_label' ] = pods_v( 'pagination_label', $new_instance, '' );
		$instance[ 'pagination_location' ] = pods_v( 'pagination_location', $new_instance, 'after', true );
		$instance[ 'pagination_type' ] = pods_v( 'pagination_type', $new_instance, 'advanced', true );
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
		$template = pods_v( 'template', $instance, '' );
		$template_custom = pods_v( 'template_custom', $instance, '' );
		$limit = (int) pods_v( 'limit', $instance, 15, true );
		$orderby = pods_v( 'orderby', $instance, '' );
		$where = pods_v( 'where', $instance, '' );
		$expires = (int) pods_var_raw( 'expires', $instance, ( 60 * 5 ) );
		$cache_mode = pods_v( 'cache_mode', $instance, 'none' );
		$filters = pods_v( 'filters', $instance, '' );
		$filters_label = pods_v( 'filters_label', $instance, '' );
		$filters_location = pods_v( 'filters_location', $instance, 'before', true );
		$pagination = (int) pods_v( 'pagination', $instance, 0 );
		$pagination_label = pods_v( 'pagination_label', $instance, '' );
		$pagination_location = pods_v( 'pagination_location', $instance, 'after', true );
		$pagination_type = pods_v( 'pagination_type', $instance, 'advanced', true );
		$before = pods_v( 'before', $instance, '' );
		$after = pods_v( 'after', $instance, '' );
		$shortcodes = (int) pods_v( 'shortcodes', $instance, 0 );

		require PODS_DIR . 'ui/admin/widgets/list.php';

	}

}
