<?php
/**
 * Compatibility functions for integration with other plugins.
 *
 * @package Pods
 */

/**
 * Enable backwards compatibility with ACF functions.
 *
 * @param bool $acf_backwards_compatibility Whether to enable backwards compatibility for ACF functions.
 *
 * @since 2.7.17
 */
$acf_backwards_compatibility = apply_filters( 'pods_acf_backwards_compatibility', true );

if ( $acf_backwards_compatibility ) {
	if ( ! function_exists( 'the_field' ) ) {
		/**
		 * Backwards compatibility function for the_field() from ACF.
		 *
		 * @param string|array $field The field name, or an associative array of parameters.
		 * @param mixed|false  $id    The ID or slug to load a single item, array of $params to run find.
		 *
		 * @since 2.7.17
		 */
		function the_field( $field, $id = false ) {
			// @codingStandardsIgnoreLine
			echo pods_field_display( null, $id, $field, true );
		}
	}

	if ( ! function_exists( 'get_field' ) ) {
		/**
		 * Backwards compatibility function for get_field() from ACF.
		 *
		 * @param string|array $field The field name, or an associative array of parameters.
		 * @param mixed|false  $id    The ID or slug to load a single item, array of $params to run find.
		 *
		 * @return mixed Field value.
		 *
		 * @since 2.7.17
		 */
		function get_field( $field, $id = false ) {
			return pods_field( null, $id, $field, true );
		}
	}

	if ( ! function_exists( 'update_field' ) ) {
		/**
		 * Backwards compatibility function for update_field() from ACF.
		 *
		 * @param string|array $field The field name, or an associative array of parameters.
		 * @param mixed        $value The value to save.
		 * @param mixed|false  $id    The ID or slug to load a single item, array of $params to run find.
		 *
		 * @return int|false The item ID or false if not saved.
		 *
		 * @since 2.7.17
		 */
		function update_field( $field, $value, $id = false ) {
			return pods_field_update( null, $id, $field, $value );
		}
	}

	if ( ! function_exists( 'delete_field' ) ) {
		/**
		 * Backwards compatibility function for delete_field() from ACF.
		 *
		 * @param string|array $field The field name, or an associative array of parameters.
		 * @param mixed|false  $id    The ID or slug to load a single item, array of $params to run find.
		 *
		 * @return int|false The item ID or false if not saved.
		 *
		 * @since 2.7.17
		 */
		function delete_field( $field, $id = false ) {
			return pods_field_update( null, $id, $field, null );
		}
	}

	if ( ! shortcode_exists( 'acf' ) ) {
		/**
		 * Backwards compatibility function for [acf] shortcode from ACF.
		 *
		 * @param array  $tags    An associative array of shortcode properties.
		 * @param string $content A string that represents a template override.
		 *
		 * @return string
		 *
		 * @since 2.7.17
		 */
		function pods_acf_shortcode( $tags, $content ) {

			$post_id = null;

			if ( ! empty( $tags['post_id'] ) ) {
				$post_id = $tags['post_id'];
			}

			$tags = array(
				'field' => $tags['field'],
				'id'    => $post_id,
			);

			return pods_shortcode( $tags, $content );
		}

		add_shortcode( 'acf', 'pods_acf_shortcode' );
	}//end if

	/**
	 * These functions below will do nothing for now. We might add some sort of further compatibility later.
	 */

	if ( ! function_exists( 'get_field_object' ) ) {
		/**
		 * Backwards compatibility function for get_field_object() from ACF.
		 *
		 * @return array
		 *
		 * @since 2.7.17
		 */
		function get_field_object() {
			return array();
		}
	}

	if ( ! function_exists( 'get_fields' ) ) {
		/**
		 * Backwards compatibility function for get_fields() from ACF.
		 *
		 * @return array
		 *
		 * @since 2.7.17
		 */
		function get_fields() {
			return array();
		}
	}

	if ( ! function_exists( 'get_field_objects' ) ) {
		/**
		 * Backwards compatibility function for get_field_objects() from ACF.
		 *
		 * @return array
		 *
		 * @since 2.7.17
		 */
		function get_field_objects() {
			return array();
		}
	}

	if ( ! function_exists( 'have_rows' ) ) {
		/**
		 * Backwards compatibility function for have_rows() from ACF.
		 *
		 * @return false
		 *
		 * @since 2.7.17
		 */
		function have_rows() {
			return false;
		}
	}

	if ( ! function_exists( 'get_sub_field' ) ) {
		/**
		 * Backwards compatibility function for get_sub_field() from ACF.
		 *
		 * @return false
		 *
		 * @since 2.7.17
		 */
		function get_sub_field() {
			return false;
		}
	}

	if ( ! function_exists( 'the_sub_field' ) ) {
		/**
		 * Backwards compatibility function for the_sub_field() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function the_sub_field() {
			return null;
		}
	}

	if ( ! function_exists( 'get_sub_field_object' ) ) {
		/**
		 * Backwards compatibility function for get_sub_field_object() from ACF.
		 *
		 * @return array
		 *
		 * @since 2.7.17
		 */
		function get_sub_field_object() {
			return array();
		}
	}

	if ( ! function_exists( 'get_row' ) ) {
		/**
		 * Backwards compatibility function for get_row() from ACF.
		 *
		 * @return array
		 *
		 * @since 2.7.17
		 */
		function get_row() {
			return array();
		}
	}

	if ( ! function_exists( 'get_row_index' ) ) {
		/**
		 * Backwards compatibility function for get_row_index() from ACF.
		 *
		 * @return int
		 *
		 * @since 2.7.17
		 */
		function get_row_index() {
			return 0;
		}
	}

	if ( ! function_exists( 'get_row_layout' ) ) {
		/**
		 * Backwards compatibility function for get_row_layout() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function get_row_layout() {
			return null;
		}
	}

	if ( ! function_exists( 'delete_sub_field' ) ) {
		/**
		 * Backwards compatibility function for delete_sub_field() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function delete_sub_field() {
			return null;
		}
	}

	if ( ! function_exists( 'update_sub_field' ) ) {
		/**
		 * Backwards compatibility function for update_sub_field() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function update_sub_field() {
			return null;
		}
	}

	if ( ! function_exists( 'add_row' ) ) {
		/**
		 * Backwards compatibility function for add_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function add_row() {
			return null;
		}
	}

	if ( ! function_exists( 'update_row' ) ) {
		/**
		 * Backwards compatibility function for update_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function update_row() {
			return null;
		}
	}

	if ( ! function_exists( 'delete_row' ) ) {
		/**
		 * Backwards compatibility function for delete_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function delete_row() {
			return null;
		}
	}

	if ( ! function_exists( 'add_sub_row' ) ) {
		/**
		 * Backwards compatibility function for add_sub_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function add_sub_row() {
			return null;
		}
	}

	if ( ! function_exists( 'update_sub_row' ) ) {
		/**
		 * Backwards compatibility function for update_sub_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function update_sub_row() {
			return null;
		}
	}

	if ( ! function_exists( 'delete_sub_row' ) ) {
		/**
		 * Backwards compatibility function for delete_sub_row() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function delete_sub_row() {
			return null;
		}
	}

	if ( ! function_exists( 'acf_add_options_page' ) ) {
		/**
		 * Backwards compatibility function for acf_add_options_page() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function acf_add_options_page() {
			return null;
		}
	}

	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		/**
		 * Backwards compatibility function for acf_add_options_sub_page() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function acf_add_options_sub_page() {
			return null;
		}
	}

	if ( ! function_exists( 'acf_form_head' ) ) {
		/**
		 * Backwards compatibility function for acf_form_head() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function acf_form_head() {
			return null;
		}
	}

	if ( ! function_exists( 'acf_form' ) ) {
		/**
		 * Backwards compatibility function for acf_form() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function acf_form() {
			return null;
		}
	}

	if ( ! function_exists( 'acf_register_form' ) ) {
		/**
		 * Backwards compatibility function for acf_register_form() from ACF.
		 *
		 * @return null
		 *
		 * @since 2.7.17
		 */
		function acf_register_form() {
			return null;
		}
	}
}//end if
