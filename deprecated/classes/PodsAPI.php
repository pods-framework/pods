<?php
require_once PODS_DIR . 'deprecated/deprecated.php';

/**
 * @package Pods\Deprecated
 */
class PodsAPI_Deprecated {

	private $obj;

	public $snap = false;

	public $dt = 0;

	public $dtname = '';

	public $fields = array();

	public $use_pod_id = false;

	/**
	 * Constructor - PodsAPI Deprecated functionality (pre 2.0)
	 *
	 * @param object $obj The PodsAPI object
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $obj ) {

		// backwards-compatibility with references to $this->var_name
		$vars = get_object_vars( $obj );

		foreach ( (array) $vars as $key => $val ) {
			$this->{$key} = $val;
		}

		// keeping references pointing back to the source
		$this->obj =& $obj;
	}

	/**
	 * Add or edit a column within a Pod
	 *
	 * $params['id'] int The field ID
	 * $params['pod_id'] int The Pod ID
	 * $params['pod'] string The Pod name
	 * $params['name'] string The field name
	 * $params['label'] string The field label
	 * $params['type'] string The column type ("txt", "desc", "pick", etc)
	 * $params['pick_object'] string The related PICK object name
	 * $params['pick_val'] string The related PICK object value
	 * $params['sister_id'] int (optional) The related field ID
	 * $params['weight'] int The field weight
	 * $params['options'] array The field options
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function save_column( $params ) {

		pods_deprecated( 'PodsAPI::save_field', '2.0' );

		return $this->obj->save_field( $params );
	}

	/**
	 * Save the entire role structure
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 * @return bool
	 */
	public function save_roles( $params ) {

		pods_deprecated( '[use WP roles and capabilities instead]', '2.0' );

		return false;
	}

	/**
	 * Drop a Pod and all its content
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_pod( $params ) {

		pods_deprecated( 'PodsAPI::delete_pod', '2.0' );

		return $this->obj->delete_pod( $params );
	}

	/**
	 * Drop a column within a Pod
	 *
	 * $params['id'] int The column ID
	 * $params['name'] int The column name
	 * $params['pod'] string The Pod name
	 * $params['pod_id'] string The Pod name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_column( $params ) {

		pods_deprecated( 'PodsAPI::delete_field', '2.0' );

		return $this->obj->delete_field( $params );
	}

	/**
	 * Drop a Pod Template
	 *
	 * $params['id'] int The template ID
	 * $params['name'] string The template name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_template( $params ) {

		pods_deprecated( 'PodsAPI::delete_template', '2.0' );

		return $this->obj->delete_template( $params );
	}

	/**
	 * Drop a Pod Page
	 *
	 * $params['id'] int The page ID
	 * $params['uri'] string The page URI
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_page( $params ) {

		pods_deprecated( 'PodsAPI::delete_page', '2.0' );

		return $this->obj->delete_page( $params );
	}

	/**
	 * Drop a Pod Helper
	 *
	 * $params['id'] int The helper ID
	 * $params['name'] string The helper name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_helper( $params ) {

		pods_deprecated( 'PodsAPI::delete_helper', '2.0' );

		return $this->obj->delete_helper( $params );
	}

	/**
	 * Drop a single pod item
	 *
	 * $params['id'] int (optional) The item's ID from the wp_pod_* table (used with datatype parameter)
	 * $params['pod'] string (optional) The datatype name (used with id parameter)
	 * $params['pod_id'] int (optional) The datatype ID (used with id parameter)
	 * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function drop_pod_item( $params ) {

		pods_deprecated( 'PodsAPI::delete_pod_item', '2.0' );

		return $this->obj->delete_pod_item( $params );
	}

	/**
	 * Load a column
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['id'] int The field ID
	 * $params['name'] string The field name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @since 1.7.9
	 */
	public function load_column( $params ) {

		pods_deprecated( 'PodsAPI::load_column', '2.0', 'PodsAPI::load_field' );

		return $this->obj->load_field( $params );
	}
}
