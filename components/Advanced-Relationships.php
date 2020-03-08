<?php
/**
 * Name: Advanced Relationships
 *
 * Description: Add advanced relationship objects for relating to including Database Tables, Multisite Networks, Multisite Sites, Themes, Page Templates, Sidebars, Post Type Objects, and Taxonomy Objects
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Tableless Mode: No
 *
 * @package    Pods\Components
 * @subpackage Advanced Relationships
 */

if ( class_exists( 'Pods_Advanced_Relationships' ) ) {
	return;
}

/**
 * Class Pods_Advanced_Relationships
 */
class Pods_Advanced_Relationships extends PodsComponent {

	/**
	 * {@inheritdoc}
	 */
	public function init() {

		add_action( 'pods_form_ui_field_pick_related_objects_other', array( $this, 'add_related_objects' ) );
	}

	/**
	 * Add Advanced Related Objects
	 *
	 * @since 2.3.0
	 */
	public function add_related_objects() {

		PodsField_Pick::$related_objects['table'] = array(
			'label' => __( 'Database Tables', 'pods' ),
			'group' => __( 'Advanced Objects', 'pods' ),
		);

		if ( is_multisite() ) {
			PodsField_Pick::$related_objects['site'] = array(
				'label' => __( 'Multisite Sites', 'pods' ),
				'group' => __( 'Advanced Objects', 'pods' ),
			);

			PodsField_Pick::$related_objects['network'] = array(
				'label' => __( 'Multisite Networks', 'pods' ),
				'group' => __( 'Advanced Objects', 'pods' ),
			);
		}

		PodsField_Pick::$related_objects['theme'] = array(
			'label'         => __( 'Themes', 'pods' ),
			'group'         => __( 'Advanced Objects', 'pods' ),
			'simple'        => true,
			'data_callback' => array( $this, 'data_themes' ),
		);

		PodsField_Pick::$related_objects['page-template'] = array(
			'label'         => __( 'Page Templates', 'pods' ),
			'group'         => __( 'Advanced Objects', 'pods' ),
			'simple'        => true,
			'data_callback' => array( $this, 'data_page_templates' ),
		);

		PodsField_Pick::$related_objects['sidebar'] = array(
			'label'         => __( 'Sidebars', 'pods' ),
			'group'         => __( 'Advanced Objects', 'pods' ),
			'simple'        => true,
			'data_callback' => array( $this, 'data_sidebars' ),
		);

		PodsField_Pick::$related_objects['post-types'] = array(
			'label'         => __( 'Post Type Objects', 'pods' ),
			'group'         => __( 'Advanced Objects', 'pods' ),
			'simple'        => true,
			'data_callback' => array( $this, 'data_post_types' ),
		);

		PodsField_Pick::$related_objects['taxonomies'] = array(
			'label'         => __( 'Taxonomy Objects', 'pods' ),
			'group'         => __( 'Advanced Objects', 'pods' ),
			'simple'        => true,
			'data_callback' => array( $this, 'data_taxonomies' ),
		);
	}

	/**
	 * Data callback for Themes
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_themes( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$themes = wp_get_themes( array( 'allowed' => true ) );

		foreach ( $themes as $theme ) {
			$data[ $theme->Template ] = $theme->Name;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_themes', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Page Templates
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_page_templates( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		if ( ! function_exists( 'get_page_templates' ) ) {
			include_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		$page_templates = apply_filters( 'pods_page_templates', get_page_templates() );

		if ( ! in_array( 'page.php', $page_templates, true ) && locate_template( array( 'page.php', false ) ) ) {
			$page_templates['Page (WP Default)'] = 'page.php';
		}

		if ( ! in_array( 'index.php', $page_templates, true ) && locate_template( array( 'index.php', false ) ) ) {
			$page_templates['Index (WP Fallback)'] = 'index.php';
		}

		ksort( $page_templates );

		$page_templates = array_flip( $page_templates );

		foreach ( $page_templates as $page_template_file => $page_template ) {
			$data[ $page_template_file ] = $page_template;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_page_templates', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Sidebars
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_sidebars( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		global $wp_registered_sidebars;

		if ( ! empty( $wp_registered_sidebars ) ) {
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$data[ $sidebar['id'] ] = $sidebar['name'];
			}
		}

		return apply_filters( 'pods_form_ui_field_pick_data_sidebars', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Post Types
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_post_types( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$post_types = get_post_types( array(), 'objects' );

		$ignore = array( 'revision', 'nav_menu_item' );

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type->name, $ignore, true ) || 0 === strpos( $post_type->name, '_pods_' ) ) {
				continue;
			}

			$data[ $post_type->name ] = $post_type->label;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_post_types', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Taxonomies
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_taxonomies( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$taxonomies = get_taxonomies( array(), 'objects' );

		$ignore = array( 'nav_menu', 'post_format' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, $ignore, true ) ) {
				continue;
			}

			$data[ $taxonomy->name ] = $taxonomy->label;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_taxonomies', $data, $name, $value, $options, $pod, $id );
	}

}
