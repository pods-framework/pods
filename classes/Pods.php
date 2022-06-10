<?php

use Pods\Data\Map_Field_Values;
use Pods\Whatsit\Object_Field;
use Pods\Whatsit\Field;
use Pods\Whatsit\Pod;
use Pod as Deprecated_Pod;

/**
 * Pods class.
 *
 * @package Pods
 *
 * @property null|string $pod         The Pod name.
 * @property null|string $pod_id      The Pod ID.
 * @property null|string $id          The current item ID (singular mode).
 * @property null|string $rows        The list of rows.
 * @property null|string $row         The current row.
 * @property null|string $pagination  Whether pagination is enabled.
 * @property null|string $page        The current page number.
 * @property null|string $page_var    The query variable used for pagination.
 * @property null|string $search      Whether search is enabled.
 * @property null|string $search_var  The query variable used for search.
 * @property null|string $search_mode The search mode to use.
 * @property null|string $params      The last find() params.
 * @property null|string $sql         The last find() SQL query.
 */
class Pods implements Iterator {

	/**
	 * Whether the Pods object is in a PHP Iterator call.
	 *
	 * @var bool
	 */
	private $iterator = false;

	/**
	 * PodsData object.
	 *
	 * @var PodsData
	 */
	public $data;

	/**
	 * PodsData object for additional calls.
	 *
	 * @var PodsData
	 */
	public $alt_data;

	/**
	 * Override pod item array.
	 *
	 * @var array
	 */
	public $row_override = array();

	/**
	 * Whether to display errors on the screen.
	 *
	 * @var bool
	 */
	public $display_errors = true;

	/**
	 * Current pod information.
	 *
	 * @var \Pods\Whatsit\Pod|array|bool|mixed|null|void
	 */
	public $pod_data;

	/**
	 * Last used ui options for ui() call.
	 *
	 * @var array
	 */
	public $ui = array();

	/**
	 * Page template to use for SEO feature in Pods Pages.
	 *
	 * @var string
	 */
	public $page_template;

	/**
	 * Body classes to use for SEO feature in Pods Pages.
	 *
	 * @var array
	 */
	public $body_classes;

	/**
	 * Meta tags to use for SEO feature in Pods Pages.
	 *
	 * @var array
	 */
	public $meta = array();

	/**
	 * Meta properties to use for SEO feature in Pods Pages.
	 *
	 * @var array
	 */
	public $meta_properties = array();

	/**
	 * Meta custom HTML to use for SEO feature in Pods Pages.
	 *
	 * @var string
	 */
	public $meta_extra = '';

	/**
	 * Pods_Deprecated object.
	 *
	 * @var Pods_Deprecated
	 */
	public $deprecated;

	/**
	 * Constructor - Pods Framework core.
	 *
	 * @param string $pod The pod name.
	 * @param mixed  $id  (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'.
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since   1.0.0
	 * @link    https://docs.pods.io/code/pods/
	 */
	public function __construct( $pod = null, $id = null ) {
		if ( null === $pod ) {
			$pod = get_queried_object();
		}

		if ( $pod && ! is_string( $pod ) && ! $pod instanceof Pod ) {
			if ( $pod instanceof WP_Post ) {
				// Post Type Singular.
				$pod       = $pod->post_type;
				$id_lookup = true;
			} elseif ( $pod instanceof WP_Term ) {
				// Term Archive.
				$pod       = $pod->taxonomy;
				$id_lookup = true;
			} elseif ( $pod instanceof WP_User ) {
				// Author Archive.
				$pod       = 'user';
				$id_lookup = true;
			} elseif ( $pod instanceof WP_Post_Type ) {
				// Post Type Archive.
				$pod       = $pod->name;
				$id_lookup = false;
			} else {
				// Unsupported pod object.
				$pod       = null;
				$id_lookup = false;
			}

			if ( null === $id && $id_lookup ) {
				$id = get_queried_object_id();
			}
		}//end if

		$maybe_id = null;

		if ( ! is_array( $id ) && ! is_object( $id ) ) {
			$maybe_id = $id;
		}

		$this->data = pods_data( $pod, $maybe_id, false );

		PodsData::$display_errors =& $this->display_errors;

		if ( ! $pod ) {
			return;
		}

		$this->pod_data =& $this->data->pod_data;

		if ( is_array( $id ) || is_object( $id ) ) {
			$this->find( $id );
		}
	}

	/**
	 * Determine whether this Pod object was defined or was built adhoc.
	 *
	 * @since 2.8.18
	 *
	 * @return bool Whether this Pod object was defined or was built adhoc.
	 */
	public function is_defined() {
		return $this->pod_data && empty( $this->pod_data['adhoc'] );
	}

	/**
	 * Determine whether this Pod object is valid or not.
	 *
	 * @since 2.8.18
	 *
	 * @return bool Whether this Pod object is valid or not.
	 */
	public function is_valid() {
		if ( empty( $this->pod_data ) ) {
			return false;
		}

		if ( $this->iterator ) {
			return isset( $this->data->rows[ $this->data->row_number ] );
		}

		return true;
	}

	/**
	 * Whether this Pod object is valid or not
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 *
	 * @see Pods::is_valid()
	 */
	public function valid() {
		return $this->is_valid();
	}

	/**
	 * Check if in Iterator mode
	 *
	 * @return bool
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function is_iterator() {
		return $this->iterator;
	}

	/**
	 * Turn off Iterator mode to off
	 *
	 * @return void
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function stop_iterator() {

		$this->iterator = false;

	}

	/**
	 * Rewind Iterator
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function rewind() {

		if ( ! $this->iterator ) {
			$this->iterator = true;

			$this->data->row_number = 0;
		}
	}

	/**
	 * Get current Iterator row
	 *
	 * @return mixed|boolean
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function current() {

		if ( $this->iterator && $this->fetch() ) {
			return $this;
		}

		return false;
	}

	/**
	 * Get current Iterator key
	 *
	 * @return int
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function key() {

		return $this->data->row_number;
	}

	/**
	 * Move onto the next Iterator row
	 *
	 * @return void
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function next() {

		$this->data->row_number ++;
	}

	/**
	 * Whether a Pod item exists or not when using fetch() or construct with an ID or slug
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public function exists() {

		if ( empty( $this->data->row ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return an array of all rows returned from a find() call.
	 *
	 * Most of the time, you will want to loop through data using fetch()
	 * instead of using this function.
	 *
	 * @return array|bool An array of all rows returned from a find() call, or false if no items returned
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/data/
	 */
	public function data() {

		do_action( 'pods_pods_data', $this );

		if ( empty( $this->data->rows ) ) {
			return false;
		}

		return (array) $this->data->rows;
	}

	/**
	 * Return a field input for a specific field
	 *
	 * @param string|array      $field      Field name or Field data array.
	 * @param string|array|null $input_name Input field name to use (overrides default name).
	 * @param mixed             $value      Current value to use.
	 *
	 * @return string Field Input HTML
	 *
	 * @since 2.3.10
	 */
	public function input( $field, $input_name = null, $value = '__null' ) {
		$is_field_object = $field instanceof Field;

		if ( is_array( $field ) || $is_field_object ) {
			// Field data override.
			$field_data = $field;

			$field = pods_v( 'name', $field );
		} else {
			// Get field data from field name.
			$field_data = $this->fields( $field );
		}

		if ( ! empty( $field_data ) ) {
			$field_type = pods_v( 'type', $field_data );

			if ( empty( $input_name ) ) {
				$input_name = $field;
			}

			if ( '__null' === $value ) {
				$value = $this->field( array(
					'name'    => $field,
					'in_form' => true,
				) );
			}

			return PodsForm::field( $input_name, $value, $field_type, $field_data, $this, $this->id() );
		}

		return '';

	}

	/**
	 * Return field array from a Pod, a field's data, or a field option
	 *
	 * @param null $field  Field name.
	 * @param null $option Option name.
	 *
	 * @return array|\Pods\Whatsit\Field|mixed|null
	 *
	 * @since 2.0.0
	 */
	public function fields( $field_name = null, $option = null ) {

		$field_data = null;

		if ( empty( $this->pod_data ) ) {
			return null;
		}

		if ( empty( $field_name ) ) {
			// Return all fields.
			$field_data = pods_config_get_all_fields( $this->pod_data );
		} else {
			$tableless_field_types = PodsForm::tableless_field_types();

			$field = pods_config_get_field_from_all_fields( $field_name, $this->pod_data );

			if ( empty( $field ) || empty( $option ) ) {
				$field_data = $field;
			} elseif ( 'data' === $option && in_array( $field['type'], $tableless_field_types, true ) ) {
				// Get a list of available items from a relationship field.
				$field_data = PodsForm::field_method( 'pick', 'get_field_data', $field );
			} elseif ( isset( $field[ $option ] ) ) {
				// Return option.
				$field_data = $field[ $option ];
			}//end if
		}//end if

		/**
		 * Modify the field data before returning
		 *
		 * @since unknown
		 *
		 * @param array|\Pods\Whatsit\Field|mixed $field_data The data to be returned for the field / option.
		 * @param array|\Pods\Whatsit\Field       $field      The field information.
		 * @param string|null                     $field_name The specific field that data is being return for, if set when method is called or null.
		 * @param string|null                     $option     Value of option param when method was called. Can be used to get a list of available items from a relationship field.
		 * @param Pods|object                     $this       The current Pods class instance.
		 */
		return apply_filters( 'pods_pods_fields', $field_data, $field_name, $option, $this );

	}

	/**
	 * Return row array for an item
	 *
	 * @return array|false
	 *
	 * @since 2.0.0
	 */
	public function row() {

		do_action( 'pods_pods_row', $this );

		if ( ! is_array( $this->data->row ) ) {
			return false;
		}

		return (array) $this->data->row;
	}

	/**
	 * Return the output for a field. If you want the raw value for use in PHP for custom manipulation,
	 * you will want to use field() instead. This function will automatically convert arrays into a
	 * list of text such as "Rick, John, and Gary"
	 *
	 * @param string|array|object  $name   The field name, or an associative array of parameters.
	 * @param boolean|array|object $single (optional) For tableless fields, to return an array or the first.
	 *
	 * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned
	 *                           for tableless fields
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/display/
	 */
	public function display( $name, $single = null ) {

		$defaults = array(
			'name'          => $name,
			'single'        => $single,
			'display'       => true,
			'serial_params' => null,
		);

		if ( is_array( $name ) || is_object( $name ) ) {
			$defaults['name'] = null;

			$params = (object) array_merge( $defaults, (array) $name );
		} elseif ( is_array( $single ) || is_object( $single ) ) {
			$defaults['single'] = null;

			$params = (object) array_merge( $defaults, (array) $single );
		} else {
			$params = $defaults;
		}

		$params = (object) $params;

		$value = $this->field( $params );

		if ( is_array( $value ) ) {
			$fields = pods_config_get_all_fields( $this->pod_data );

			$serial_params = array(
				'field'  => $params->name,
				'fields' => $fields,
			);

			if ( ! empty( $params->serial_params ) && is_array( $params->serial_params ) ) {
				$serial_params = array_merge( $serial_params, $params->serial_params );
			}

			$value = pods_serial_comma( $value, $serial_params );
		}

		return $value;
	}

	/**
	 * Return the raw output for a field If you want the raw value for use in PHP for custom manipulation,
	 * you will want to use field() instead. This function will automatically convert arrays into a
	 * list of text such as "Rick, John, and Gary"
	 *
	 * @param string|array|object  $name   The field name, or an associative array of parameters.
	 * @param boolean|array|object $single (optional) For tableless fields, to return an array or the first.
	 *
	 * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned
	 *                           for tableless fields
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/display/
	 */
	public function raw( $name, $single = null ) {

		$defaults = array(
			'name'   => $name,
			'single' => $single,
			'raw'    => true,
		);

		if ( is_array( $name ) || is_object( $name ) ) {
			$defaults['name'] = null;

			$params = (object) array_merge( $defaults, (array) $name );
		} elseif ( is_array( $single ) || is_object( $single ) ) {
			$defaults['single'] = null;

			$params = (object) array_merge( $defaults, (array) $single );
		} else {
			$params = (object) $defaults;
		}

		return $this->field( $params );
	}

	/**
	 * Return the value for a field.
	 *
	 * If you are getting a field for output in a theme, most of the time you will want to use display() instead.
	 *
	 * This function will return arrays for relationship and file fields.
	 *
	 * @param string|array|object  $name   The field name, or an associative array of parameters.
	 * @param boolean|array|object $single For tableless fields, to return the whole array or the just the first item,
	 *                                     or an associative array of parameters.
	 * @param boolean|array|object $raw    Whether to return the raw value, or to run through the field type's display
	 *                                     method, or an associative array of parameters.
	 *
	 * @return mixed|null Value returned depends on the field type, null if the field doesn't exist, false if no value
	 *                    returned for tableless fields.
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/field/
	 */
	public function field( $name, $single = null, $raw = false ) {
		$defaults = [
			'name'                         => $name,
			'orderby'                      => null,
			'single'                       => $single,
			'params'                       => null,
			'in_form'                      => false,
			'raw'                          => $raw,
			'raw_display'                  => false,
			'display'                      => false,
			'display_process_individually' => false,
			'get_meta'                     => false,
			'output'                       => null,
			'pods_callback'                => 'pods',
			'deprecated'                   => false,
			'keyed'                        => false,
			// extra data to send to field handlers.
			'args'                         => [],
		];

		if ( is_object( $name ) ) {
			$name = get_object_vars( $name );
		}

		if ( is_object( $single ) ) {
			$single = get_object_vars( $single );
		}

		if ( is_object( $raw ) ) {
			$raw = get_object_vars( $raw );
		}

		if ( is_array( $name ) ) {
			$defaults['name'] = null;

			$params = (object) array_merge( $defaults, (array) $name );
		} elseif ( is_array( $single ) ) {
			$defaults['single'] = null;

			$params = (object) array_merge( $defaults, (array) $single );
		} elseif ( is_array( $raw ) ) {
			$defaults['raw'] = false;

			$params = (object) array_merge( $defaults, (array) $raw );
		} else {
			$params = (object) $defaults;
		}//end if

		if ( $params->in_form ) {
			$params->output = 'ids';
		} elseif ( null === $params->output ) {
			/**
			 * Override the way related fields are output
			 *
			 * @param string       $output How to output related fields. Default is 'arrays'. Options: ids|names|objects|arrays|pods|find
			 * @param array|object $row    Current row being outputted.
			 * @param array        $params Params array passed to field().
			 * @param Pods         $this   Current Pods object.
			 */
			$params->output = apply_filters( 'pods_pods_field_related_output_type', 'arrays', $this->data->row, $params, $this );
		}

		if ( in_array( $params->output, array( 'id', 'name', 'object', 'array', 'pod' ), true ) ) {
			$params->output .= 's';
		}

		if ( empty( $params->pods_callback ) || ! is_callable( $params->pods_callback ) ) {
			$params->pods_callback = 'pods';
		}

		// Support old $orderby variable.
		if ( null !== $params->single && is_string( $params->single ) && empty( $params->orderby ) ) {
			if ( ! class_exists( 'Deprecated_Pod' ) || Deprecated_Pod::$deprecated_notice ) {
				pods_deprecated( 'Pods::field', '2.0', 'Use $params[ \'orderby\' ] instead' );
			}

			$params->orderby = $params->single;
			$params->single  = false;
		}

		if ( null !== $params->single ) {
			$params->single = (boolean) $params->single;
		}

		$params->name = trim( $params->name );
		if ( is_array( $params->name ) || '' === $params->name ) {
			return null;
		}

		$params->full_name = $params->name;

		$value = null;

		if ( isset( $this->row_override[ $params->name ] ) ) {
			$value = $this->row_override[ $params->name ];
		}

		if ( false === $this->row() ) {
			if ( false !== $this->data() ) {
				$this->fetch();
			} else {
				return $value;
			}
		}

		if ( $this->data->field_id === $params->name ) {
			if ( isset( $this->data->row[ $params->name ] ) ) {
				return $this->data->row[ $params->name ];
				// @codingStandardsIgnoreLine.
			} elseif ( null !== $value ) {
				return $value;
			}

			return 0;
		}

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$params->traverse = array();

		$permalink_fields = array(
			'_link',
			'detail_url',
			'permalink',
			'the_permalink',
		);

		$wp_object_types = array(
			'post_type',
			'taxonomy',
			'media',
			'user',
			'comment',
		);

		/** @var string $pod_type The pod object type. */
		$pod_type = pods_v( 'type', $this->pod_data, '' );

		$use_meta_fallback = in_array( $pod_type, $wp_object_types, true );

		/**
		 * Allow hooking in to support getting meta using the meta fallback.
		 *
		 * @since 2.8.0
		 *
		 * @param bool   $use_meta_fallback Whether to support getting meta using the meta fallback.
		 * @param string $object_type       The object type.
		 */
		$use_meta_fallback = apply_filters( 'pods_field_wp_object_use_meta_fallback', $use_meta_fallback, $pod_type );

		if ( in_array( $params->name, $permalink_fields, true ) ) {
			if ( 0 < strlen( $this->detail_page ) && false === strpos( $params->name, 'permalink' ) ) {
				// ACT Pods. Prevent tag loop by not parsing `permalink`.
				$value = get_home_url() . '/' . $this->do_magic_tags( $this->detail_page );
			} else {
				switch ( $pod_type ) {
					case 'post_type':
					case 'media':
						$value = get_permalink( $this->id() );
						break;
					case 'taxonomy':
						$value = get_term_link( $this->id(), $this->pod_data['name'] );
						break;
					case 'user':
						$value = get_author_posts_url( $this->id() );
						break;
					case 'comment':
						$value = get_comment_link( $this->id() );
						break;
				}
			}
		}

		/**
		 * @var bool   $is_field_set       Is the field found.
		 * @var bool   $is_tableless_field Is it a tableless field.
		 * @var string $field_source       Regular field or object field.
		 * @var array  $field_data         The field data.
		 * @var string $field_type         The field type.
		 * @var array  $field_options      The field options.
		 * @var array  $traverse_fields    All the traversal field names.
		 * @var bool   $is_traversal       Is it a traversal field request.
		 * @var string $first_field        The name of the fieds without the traversal names from $params->name.
		 * @var array  $last_field_data    The field data used in traversal loop.
		 */

		$is_field_set       = false;
		$is_tableless_field = false;
		$field_source       = '';
		$field_data         = array();
		$field_type         = '';
		$field_options      = array();
		$traverse_fields    = explode( '.', $params->name );
		$is_traversal       = 1 < count( $traverse_fields );
		$first_field        = $traverse_fields[0];
		$last_field_data    = null;

		// Get the first field name data.
		$field_data = $this->fields( $first_field );

		// Ensure the field name is using the correct name and not the alias.
		if ( $field_data ) {
			$first_field = $field_data['name'];

			if ( ! $is_traversal ) {
				$params->name = $first_field;
			}
		}

		if ( ! $field_data && ! $is_traversal ) {
			// Get the full field name data.
			$field_data = $this->fields( $params->name );
		}

		$override_object_field = false;

		if ( $field_data instanceof \Pods\Whatsit\Object_Field ) {
			$field_source = 'object_field';
			$is_field_set = true;
		} elseif ( $field_data instanceof \Pods\Whatsit\Field ) {
			$field_source = 'field';
			$is_field_set = true;

			$override_object_field = (bool) $field_data->get_arg( 'override_object_field', false );
		}

		// Store field info.
		$field_type         = pods_v( 'type', $field_data, '' );
		$field_options      = $field_data;
		$is_tableless_field = in_array( $field_type, $tableless_field_types, true );

		// Simple fields have no other output options.
		if ( 'pick' === $field_type && in_array( $field_data['pick_object'], $simple_tableless_objects, true ) ) {
			$params->output = 'arrays';
		}

		$is_tableless_field_and_not_simple = (
			! $is_traversal
			&& $field_data
			&& ! $field_data instanceof Object_Field
			&& $is_tableless_field
			&& ! $field_data->is_simple_relationship()
		);

		// If a relationship is returned from the table as an ID but the parameter is not traversal, we need to run traversal logic.
		if ( $is_tableless_field_and_not_simple && isset( $this->data->row[ $params->name ] ) && ! is_array( $this->data->row[ $params->name ] ) ) {
			unset( $this->data->row[ $params->name ] );
		}

		// Enforce output type for tableless fields in forms.
		if ( empty( $value ) && $is_tableless_field ) {
			$params->raw = true;

			$value = false;

			$row_key = '_' . $params->output . '_' . $params->name;

			if ( 'arrays' !== $params->output && isset( $this->data->row[ $row_key ] ) ) {
				$value = $this->data->row[ $row_key ];
			} elseif ( 'arrays' === $params->output && isset( $this->data->row[ $params->name ] ) ) {
				$value = $this->data->row[ $params->name ];
			}

			if (
				false !== $value &&
				! is_array( $value ) &&
				'pick' === $field_type &&
				in_array( $field_data['pick_object'], $simple_tableless_objects, true )
			) {
				$value = PodsForm::field_method( 'pick', 'simple_value', $params->name, $value, $field_data, $this->pod_data, $this->id(), true );
			}
		}

		if (
			! $override_object_field
			&& empty( $value )
			&& isset( $this->data->row[ $params->name ] )
			&& ( ! $is_tableless_field || 'arrays' === $params->output )
		) {
			if ( empty( $field_data ) || in_array( $field_type, [
					'boolean',
					'number',
					'currency',
				], true ) ) {
				$params->raw = true;
			}

			if ( null === $params->single ) {
				if ( ! $is_tableless_field ) {
					$params->single = true;
				} else {
					$params->single = false;
				}
			}

			$value = $this->data->row[ $params->name ];
		} elseif ( empty( $value ) ) {
			$object_field_found = false;

			if ( 'object_field' === $field_source && ! $is_traversal ) {
				$object_field_found = true;

				if ( isset( $this->data->row[ $first_field ] ) ) {
					$value = $this->data->row[ $first_field ];
				} elseif ( $is_tableless_field ) {
					$object_field_found = false;
				} else {
					return null;
				}
			} else {
				// Handle custom/supported value mappings.
				$map_field_values = pods_container( Map_Field_Values::class );

				$value = $map_field_values->map_value( $first_field, $traverse_fields, $is_field_set ? $field_data : null, $this );

				$object_field_found = null !== $value;
			}

			// Continue regular field parsing.
			if ( false === $object_field_found ) {
				$params->traverse = array( $params->name );

				if ( false !== strpos( $params->name, '.' ) ) {
					$params->traverse = explode( '.', $params->name );

					$params->name = $params->traverse[0];
				}

				$simple = false;

				if ( $field_data ) {
					/**
					 * Modify value returned by field() after its retrieved, but before its validated or formatted
					 *
					 * Filter name is set dynamically with name of field: "pods_pods_field_{field_type}"
					 *
					 * @since unknown
					 *
					 * @param array|string|null $value      Value retrieved.
					 * @param array             $field_data Current field object.
					 * @param array|object      $row        Current row being outputted.
					 * @param array             $params     Params array passed to field().
					 * @param object|Pods       $this       Current Pods object.
					 */
					$v = apply_filters( "pods_pods_field_{$field_type}", null, $field_data, $this->row(), $params, $this );

					if ( null !== $v ) {
						return $v;
					}

					if ( 'meta' === $this->pod_data['storage'] && ! $is_tableless_field ) {
						$simple = true;
					}

					if ( $is_tableless_field ) {
						$params->raw = true;

						if ( 'pick' === $field_type && in_array( $field_data['pick_object'], $simple_tableless_objects, true ) ) {
							$simple         = true;
							$params->single = true;
						}
					} elseif ( in_array( $field_type, [
						'boolean',
						'number',
						'currency',
					], true ) ) {
						$params->raw = true;
					}
				}

				if ( ! $is_traversal && ( $simple || ! $is_field_set || ! $is_tableless_field ) ) {
					if ( null === $params->single ) {
						if ( $is_field_set && ! $is_tableless_field ) {
							$params->single = true;
						} else {
							$params->single = false;
						}
					}

					$no_conflict = pods_no_conflict_check( $pod_type );

					if ( ! $no_conflict ) {
						// Temporarily enable no conflict.
						pods_no_conflict_on( $pod_type );
					}

					if ( $use_meta_fallback ) {
						$metadata_type = $pod_type;

						if ( in_array( $metadata_type, array( 'post_type', 'media' ), true ) ) {
							$metadata_type = 'post';
						} elseif ( 'taxonomy' === $metadata_type ) {
							$metadata_type = 'term';
						}

						/**
						 * Modify the object ID for getting metadata.
						 * Added for i18n integration classes.
						 *
						 * @since 2.8.0
						 *
						 * @param int    $id            The object ID.
						 * @param string $metadata_type The object metadata type.
						 * @param array  $params        Field params
						 * @param \Pods  $pod           Pods object.
						 */
						$id = apply_filters( 'pods_pods_field_get_metadata_object_id', $this->id(), $metadata_type, $params, $this );

						$value = get_metadata( $metadata_type, $id, $params->name, $params->single );

						$single_multi = 'single';

						if ( $is_field_set ) {
							$single_multi = pods_v( $field_type . '_format_type', $field_data, $single_multi );
						}

						if ( $simple && ! is_array( $value ) && 'single' !== $single_multi ) {
							$value = get_metadata( $metadata_type, $id, $params->name );
						}
					} elseif ( 'settings' === $pod_type ) {
						$value = get_option( $this->pod_data['name'] . '_' . $params->name, null );
					}//end if

					// Handle Simple Relationships.
					if ( $simple ) {
						if ( null === $params->single ) {
							$params->single = false;
						}

						$value = PodsForm::field_method( 'pick', 'simple_value', $params->name, $value, $field_data, $this->pod_data, $this->id(), true );
					}

					if ( ! $no_conflict ) {
						// Revert temporarily no conflict mode.
						pods_no_conflict_off( $pod_type );
					}
				} else {
					// Dot-traversal.
					$pod        = $this->pod_data['name'];
					$ids        = array( $this->id() );
					$all_fields = array();

					$lookup = $params->traverse;

					// Get fields matching traversal names.
					if ( ! empty( $lookup ) ) {
						if ( 1 === count( $params->traverse ) && $params->name === $params->traverse[0] && $field_data && $field_data['name'] === $params->traverse[0] ) {
							$fields = array(
								$field_data,
							);
						} else {
							$fields = $this->data->api->traverse_fields( array(
								'pod'    => $pod,
								'expand' => $lookup,
								'type'   => $tableless_field_types,
							) );
						}

						if ( ! empty( $fields ) ) {
							foreach ( $fields as $field ) {
								if ( ! empty( $field ) ) {
									if ( ! isset( $all_fields[ $field['pod'] ] ) ) {
										$all_fields[ $field['pod'] ] = array();
									}

									$all_fields[ $field['pod'] ][ $field['name'] ] = $field;
								}
							}
						}
					}//end if

					$last_type           = '';
					$last_object         = '';
					$last_pick_val       = '';
					$last_options        = [];
					$last_object_options = [];

					$single_multi = pods_v( $field_type . '_format_type', $field_data, 'single' );

					if ( 'multi' === $single_multi ) {
						$limit = (int) pods_v( $field_type . '_limit', $field_data, 0 );
					} else {
						$limit = 1;
					}

					// Loop through each traversal level.
					foreach ( $params->traverse as $key => $field ) {
						$last_loop = false;

						if ( count( $params->traverse ) <= ( $key + 1 ) ) {
							$last_loop = true;
						}

						$field_exists = isset( $all_fields[ $pod ][ $field ] );

						$current_field = null;
						$simple        = false;

						if ( $field_exists ) {
							/** @var \Pods\Whatsit\Field $current_field */
							$current_field = $all_fields[ $pod ][ $field ];

							if ( in_array( $current_field['type'], $tableless_field_types, true ) ) {
								$last_options = $current_field;

								if ( 'pick' === $current_field['type'] && in_array( $current_field->get_related_object_type(), $simple_tableless_objects, true ) ) {
									$simple = true;
								}
							}
						}

						// Tableless handler.
						if ( $field_exists && ( ! $simple || ! in_array( $current_field['type'], array(
							'pick',
							'taxonomy',
							'comment',
						), true ) ) ) {
							$type        = $current_field['type'];
							$pick_object = $current_field->get_related_object_type();
							$pick_val    = $current_field->get_related_object_name();

							$last_limit = 0;

							if ( in_array( $type, $tableless_field_types, true ) ) {
								$single_multi = $current_field->get_arg( "{$type}_format_type", 'single' );

								if ( 'multi' === $single_multi ) {
									$last_limit = (int) $current_field->get_arg( "{$type}_limit", 0 );
								} else {
									$last_limit = 1;
								}
							}

							$last_type           = $type;
							$last_object         = $pick_object;
							$last_pick_val       = $pick_val;
							$last_options        = $current_field;
							$last_object_options = $current_field;

							// Temporary hack until there's some better handling here.
							$last_limit *= count( $ids );

							// Override the $limit in case $limit was a single select, there are multiple values to return now.
							$limit = $last_limit;

							// Get related IDs.
							if ( isset( $current_field['id'] ) ) {
								$ids = $this->data->api->lookup_related_items( $current_field['id'], $current_field->get_parent_id(), $ids, $current_field );
							}

							// No items found.
							if ( empty( $ids ) ) {
								// pods_debug( 'No related IDs found! ' . var_export( array( 'id' => $current_field['id'], 'pod_id' => $current_field->get_parent_id(), 'ids' => $ids, 'current_field' => empty( $current_field['id'] ) ? $current_field : 'has field id tho' ), true ) );

								return false;
							}

							// pods_debug( 'Related IDs found! ' . var_export( array( 'id' => $current_field['id'], 'pod_id' => $current_field->get_parent_id(), 'ids' => $ids ), true ) );

							if ( 0 < $last_limit ) {
								// @todo This should return array() if not $params->single.
								$ids = array_slice( $ids, 0, $last_limit );
							}

							// Get $pod if related to a Pod.
							if ( ! empty( $pick_object ) && ( ! empty( $pick_val ) || in_array( $pick_object, array(
								'user',
								'media',
								'comment',
							), true ) ) ) {
								if ( 'pod' === $pick_object ) {
									$pod = $pick_val;
								} else {
									$check = $current_field->get_table_info();

									if ( $check && ! empty( $check['pod'] ) ) {
										$pod = $check['pod']['name'];
									}
								}
							}
						} else {
							// Assume last iteration.
							$last_loop = true;

							if ( 0 === $key ) {
								// This is also the first loop. Assume metadata or options to traverse into.
								$last_object   = $this->pod_data['object_type'];
								$last_pick_val = $this->pod_data['name'];
							}
						}//end if

						if ( $last_loop ) {
							$object_type = $last_object;
							$object      = $last_pick_val;

							if ( in_array( $last_type, PodsForm::file_field_types(), true ) ) {
								$object_type = 'media';
								$object      = 'media';
							}

							$data  = array();
							$table = array();

							if ( $last_options ) {
								if ( $simple ) {
									$table = $last_object_options->get_table_info();
								} else {
									$table = $last_options->get_table_info();
								}
							}

							$join  = array();
							$where = array();

							if ( ! empty( $table['join'] ) ) {
								$join = (array) $table['join'];
							}

							// pods_debug( 'IDs found: ' . var_export( $ids, true ) );

							if ( $table && ( ! empty( $ids ) || ! empty( $table['where'] ) ) ) {
								foreach ( $ids as $id ) {
									$where[ $id ] = '`t`.`' . $table['field_id'] . '` = ' . (int) $id;
								}

								if ( ! empty( $where ) ) {
									$where = array( implode( ' OR ', $where ) );
								}

								if ( ! empty( $table['where'] ) ) {
									// @codingStandardsIgnoreLine.
									$where = array_merge( $where, array_values( (array) $table['where'] ) );
								}
							}

							/**
							 * Related object.
							 *
							 * @var $related_obj Pods|false
							 */
							$related_obj = false;

							// Check if we can return the full object/array or if we need to traverse into it.
							$is_field_output_full = false;

							if ( false !== $field_exists && ( in_array( $last_type, $tableless_field_types, true ) && ! $simple ) ) {
								$is_field_output_full = true;
							}

							/** @var Pods $related_obj */
							if ( 'pod' === $object_type ) {
								$related_obj = call_user_func( $params->pods_callback, $object, null, false );
							} elseif ( ! empty( $table['pod'] ) ) {
								$related_obj = call_user_func( $params->pods_callback, $table['pod']['name'], null, false );
							}

							if ( $table && ( $related_obj || ! empty( $table['table'] ) ) ) {
								$sql = array(
									'select'     => '*, `t`.`' . $table['field_id'] . '` AS `pod_item_id`',
									'table'      => $table['table'],
									'join'       => $join,
									'where'      => $where,
									'orderby'    => $params->orderby,
									'pagination' => false,
									'search'     => false,
									'limit'      => - 1,
									'expires'    => 180,
									// @todo This could potentially cause issues if someone changes the data within this time and persistent storage is used.
								);

								if ( ! empty( $table['where_default'] ) ) {
									$sql['where_default'] = $table['where_default'];
								}

								// Output types.
								if ( in_array( $params->output, array( 'ids', 'objects', 'pods' ), true ) ) {
									$sql['select']  = '`t`.`' . $table['field_id'] . '` AS `pod_item_id`';
									$sql['orderby'] = [];
								} elseif ( 'names' === $params->output && ! empty( $table['field_index'] ) ) {
									$sql['select']  = '`t`.`' . $table['field_index'] . '` AS `pod_item_index`, `t`.`' . $table['field_id'] . '` AS `pod_item_id`';
									$sql['orderby'] = [];
								}

								if ( ! empty( $params->params ) && is_array( $params->params ) ) {
									$where = $sql['where'];

									// @codingStandardsIgnoreLine.
									$sql = array_merge( $sql, $params->params );

									if ( isset( $params->params['where'] ) ) {
										// @codingStandardsIgnoreLine.
										$sql['where'] = array_merge( (array) $where, (array) $params->params['where'] );
									}
								}

								$item_data = array();

								if ( ! $related_obj || ! $related_obj->valid() ) {
									if ( ! is_object( $this->alt_data ) ) {
										$this->alt_data = pods_data();
									}

									$item_data = $this->alt_data->select( $sql );
								} else {
									// Support 'find' output ordering.
									if ( $ids && 'find' === $params->output && $is_field_output_full && empty( $sql['orderby'] ) ) {
										// Handle default orderby for ordering by the IDs.
										$order_ids = implode( ', ', array_map( 'absint', $ids ) );

										$sql['orderby'] = 'FIELD( `t`.`' . $table['field_id'] . '`, ' . $order_ids . ' )';
									}

									$related_obj->find( $sql );

									// Support 'find' output.
									if ( 'find' === $params->output && $is_field_output_full ) {
										$data = $related_obj;

										$is_field_output_full = true;
									} else {
										$item_data = $related_obj->data();
									}
								}//end if

								$items = array();

								if ( ! empty( $item_data ) ) {
									foreach ( $item_data as $item ) {
										if ( is_array( $item ) ) {
											$item = (object) $item;
										}

										if ( empty( $item->pod_item_id ) ) {
											continue;
										}

										// Bypass pass field.
										if ( isset( $item->user_pass ) ) {
											unset( $item->user_pass );
										}

										// Get Item ID.
										$item_id = $item->pod_item_id;

										// Output types.
										if ( 'ids' === $params->output ) {
											$item = (int) $item_id;
										} elseif ( 'names' === $params->output && ! empty( $table['field_index'] ) ) {
											$item = $item->pod_item_index;
										} elseif ( 'objects' === $params->output ) {
											if ( in_array( $object_type, array( 'post_type', 'media' ), true ) ) {
												$item = get_post( $item_id );
											} elseif ( 'taxonomy' === $object_type ) {
												$item = get_term( $item_id, $object );
											} elseif ( 'user' === $object_type ) {
												$item = get_userdata( $item_id );

												if ( ! empty( $item ) ) {
													// Get other vars.
													$roles   = $item->roles;
													$caps    = $item->caps;
													$allcaps = $item->allcaps;

													$item = $item->data;

													// Set other vars.
													$item->roles   = $roles;
													$item->caps    = $caps;
													$item->allcaps = $allcaps;

													unset( $item->user_pass );
												}
											} elseif ( 'comment' === $object_type ) {
												$item = get_comment( $item_id );
											} else {
												$item = (object) $item;
											}//end if
										} elseif ( 'pods' === $params->output ) {
											/** @var Pods $item */
											if ( in_array( $object_type, array( 'user', 'media' ), true ) ) {
												$item = call_user_func( $params->pods_callback, $object_type, (int) $item_id );
											} else {
												$item = call_user_func( $params->pods_callback, $object, (int) $item_id );
											}

											if ( ! $item || ! $item->valid() ) {
												// Related pod does not exist.
												$item = false;
											}
										} else {
											// arrays.
											$item = get_object_vars( (object) $item );
										}//end if

										// Pass item data into $data.
										$items[ $item_id ] = $item;
									}//end foreach

									// Cleanup.
									unset( $item_data );

									// Return all of the data in the order expected.
									if ( empty( $params->orderby ) ) {
										foreach ( $ids as $id ) {
											if ( isset( $items[ $id ] ) ) {
												$data[ $id ] = $items[ $id ];
											}
										}
									} else {
										// Use order set by orderby.
										foreach ( $items as $id => $v ) {
											// @codingStandardsIgnoreLine.
											if ( in_array( $id, $ids ) ) {
												$data[ $id ] = $v;
											}
										}
									}
								}//end if
							}//end if

							if ( in_array( $last_type, $tableless_field_types, true ) || in_array( $last_type, array(
								'boolean',
								'number',
								'currency',
							), true ) ) {
								$params->raw = true;
							}

							if ( empty( $data ) ) {
								$value = false;
							} else {
								$object_type = $table['type'];

								if ( in_array( $table['type'], array( 'post_type', 'attachment', 'media' ), true ) ) {
									$object_type = 'post';
								}

								$object_no_conflict = in_array( $object_type, array( 'post', 'taxonomy', 'user', 'comment', 'settings' ), true );

								$no_conflict = pods_no_conflict_check( $object_type );

								if ( $object_no_conflict && ! $no_conflict ) {
									// Temporarily enable no conflict.
									pods_no_conflict_on( $object_type );
								}

								if ( $is_field_output_full ) {
									// Return entire array.
									$value = $data;
								} else {
									// Return an array of single column values.
									$value = array();

									// $field is 123x123, needs to be _src.123x123
									$traverse_fields = array_splice( $params->traverse, $key );
									$full_field      = implode( '.', $traverse_fields );
									array_shift( $traverse_fields );

									foreach ( $data as $item_id => $item ) {
										if ( is_array( $item ) && isset( $item[ $field ] ) ) {
											if ( $table['field_id'] === $field ) {
												$value[] = (int) $item[ $field ];
											} else {
												$value[] = $item[ $field ];
											}
										} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
											if ( $table['field_id'] === $field ) {
												$value[] = (int) $item->{$field};
											} else {
												$value[] = $item->{$field};
											}
										} elseif ( ! empty( $related_obj ) && 0 === strpos( $full_field, 'post_thumbnail' ) ) {
											// We want to catch post_thumbnail and post_thumbnail_url here
											$value[] = $related_obj->field( $full_field );
										} elseif (
											(
												( false !== strpos( $full_field, '_src' ) || 'guid' === $field )
												&& (
													in_array( $table['type'], [ 'attachment', 'media' ], true )
													|| in_array( $last_type, PodsForm::file_field_types(), true )
												)
											)
											|| ( in_array( $field, $permalink_fields, true ) && in_array( $last_type, PodsForm::file_field_types(), true ) )
										) {
											// @todo Refactor the above condition statement.
											$size = 'full';

											if ( ! wp_attachment_is_image( $item_id ) ) {
												// No default sizes for non-images.
												// When a size is defined this will be overwritten.
												$size = null;
											}

											if ( false !== strpos( $full_field, '_src.' ) && 5 < strlen( $full_field ) ) {
												$size = substr( $full_field, 5 );
											} elseif ( false !== strpos( $full_field, '_src_relative.' ) && 14 < strlen( $full_field ) ) {
												$size = substr( $full_field, 14 );
											} elseif ( false !== strpos( $full_field, '_src_schemeless.' ) && 16 < strlen( $full_field ) ) {
												$size = substr( $full_field, 16 );
											}

											if ( $size ) {
												$value_url = pods_image_url( $item_id, $size, 0, true );
											} else {
												$value_url = wp_get_attachment_url( $item_id );
											}

											if ( ! empty( $value_url ) ) {
												if ( false !== strpos( $full_field, '_src_relative' ) ) {
													$value_url_parsed = wp_parse_url( $value_url );
													$value_url        = $value_url_parsed['path'];
												} elseif ( false !== strpos( $full_field, '_src_schemeless' ) ) {
													$value_url = str_replace( array(
														'http://',
														'https://',
													), '//', $value_url );
												}
											}

											if ( ! empty( $value_url ) ) {
												$value[] = $value_url;
											}

											$params->raw_display = true;
										} elseif (
											false !== strpos( $full_field, '_img' ) &&
											(
												in_array( $table['type'], array( 'attachment', 'media', ), true ) ||
												in_array( $last_type, PodsForm::file_field_types(), true )
											)
										) {
											$size = 'full';

											if ( false !== strpos( $full_field, '_img.' ) && 5 < strlen( $full_field ) ) {
												$size = substr( $full_field, 5 );
											}

											$value[] = pods_image( $item_id, $size, 0, array(), true );

											$params->raw_display = true;
										} elseif ( in_array( $field, $permalink_fields, true ) ) {
											if ( 'pod' === $object_type ) {
												if ( is_object( $related_obj ) ) {
													$related_obj->fetch( $item_id );

													$value[] = $related_obj->field( 'detail_url' );
												} else {
													$value[] = '';
												}
											} elseif ( 'post' === $object_type ) {
												$value[] = get_permalink( $item_id );
											} elseif ( 'taxonomy' === $object_type ) {
												$value[] = get_term_link( $item_id, $object );
											} elseif ( 'user' === $object_type ) {
												$value[] = get_author_posts_url( $item_id );
											} elseif ( 'comment' === $object_type ) {
												$value[] = get_comment_link( $item_id );
											} else {
												$value[] = '';
											}

											$params->raw_display = true;
										} elseif ( in_array( $object_type, array(
											'post',
											'taxonomy',
											'user',
											'comment',
										), true ) ) {
											$metadata_object_id = $item_id;

											$metadata_type = $object_type;

											if ( 'taxonomy' === $object_type ) {
												$metadata_type = 'term';
											}

											/** This filter is documented earlier in this method */
											$metadata_object_id = apply_filters( 'pods_pods_field_get_metadata_object_id', $metadata_object_id, $metadata_type, $params, $this );

											$meta_value = get_metadata( $metadata_type, $metadata_object_id, $field, true );

											$value[] = pods_traverse( $traverse_fields, $meta_value );
										} elseif ( 'settings' === $object_type ) {
											$option_value = get_option( $object . '_' . $field );

											$value[] = pods_traverse( $traverse_fields, $option_value );
										}//end if
									}//end foreach
								}//end if

								if ( $object_no_conflict && ! $no_conflict ) {
									// Revert temporarily no conflict mode.
									pods_no_conflict_off( $object_type );
								}

								// Handle Simple Relationships.
								if ( $simple ) {
									if ( null === $params->single ) {
										$params->single = false;
									}

									$value = PodsForm::field_method( 'pick', 'simple_value', $field, $value, $last_options, $all_fields[ $pod ], 0, true );
								} elseif ( false === $params->in_form && ! empty( $value ) && is_array( $value ) && false === $params->keyed ) {
									$value = array_values( $value );
								}

								// Return a single column value.
								if ( false === $params->in_form && 1 === $limit && ! empty( $value ) && is_array( $value ) && 1 === count( $value ) ) {
									$value = current( $value );
								}
							}//end if

							// pods_debug( 'value' );
							// pods_debug( compact( 'value', 'data' ) );

							if ( $last_options ) {
								$last_field_data = $last_options;
							} elseif ( isset( $related_obj, $related_obj->fields, $related_obj->fields[ $field ] ) ) {
								// Save related field data for later to be used for display formatting
								$last_field_data = $related_obj->fields[ $field ];
							}

							break;
						}//end if
					}//end foreach
				}//end if
			}//end if
		}//end if

		if ( ! empty( $params->traverse ) && 1 < count( $params->traverse ) ) {
			$field_names = implode( '.', $params->traverse );

			$this->data->row[ $field_names ] = $value;
		} elseif ( 'arrays' !== $params->output && $field_data && in_array( $field_data['type'], $tableless_field_types, true ) ) {
			$this->data->row[ '_' . $params->output . '_' . $params->full_name ] = $value;
		} elseif ( 'arrays' === $params->output || ! $field_data || ! in_array( $field_data['type'], $tableless_field_types, true ) ) {
			$this->data->row[ $params->full_name ] = $value;
		}

		if ( true === $params->single && is_array( $value ) && 1 === count( $value ) ) {
			$value = current( $value );
		}

		if ( ! empty( $last_field_data ) ) {
			$field_data = $last_field_data;
		}

		if ( ! empty( $field_data ) && ( $params->display || ! $params->raw ) && ! $params->in_form && ! $params->raw_display ) {
			if ( $params->display || ( ( $params->get_meta || $params->deprecated ) && ! in_array( $field_data['type'], $tableless_field_types, true ) ) ) {
				$post_temp   = false;
				$old_post    = null;
				$old_post_id = null;
				$post_ID     = null;

				if ( empty( $GLOBALS['post'] ) && 'post_type' === pods_v( 'type', $this->pod_data ) && 0 < $this->id() ) {
					global $post_ID, $post;

					$post_temp = true;

					$old_post    = $post;
					$old_post_id = $post_ID;

					// @codingStandardsIgnoreLine.
					$post    = get_post( $this->id() );
					// @codingStandardsIgnoreLine.
					$post_ID = $this->id();
				}

				$filter = pods_v( 'display_filter', $field_data );

				if ( 0 < strlen( $filter ) ) {
					$value_reset = false;

					if ( $params->single || ! is_array( $value ) ) {
						$value = array( $value );

						$value_reset = true;
					}

					foreach ( $value as $key => $val ) {
						$args = array(
							$filter,
							$val,
						);

						$filter_args = pods_v( 'display_filter_args', $field_data );

						if ( ! empty( $filter_args ) ) {
							$args = array_merge( $args, compact( $filter_args ) );
						}

						$val = call_user_func_array( 'apply_filters', array_values( $args ) );

						$value[ $key ] = $val;

					}

					if ( $value_reset ) {
						$value = reset( $value );
					}
				} elseif ( 1 === (int) pods_v( 'display_process', $field_data, 1 ) ) {
					if ( ! is_array( $value ) || ! $params->display_process_individually ) {
						// Do the normal display handling.
						$value = PodsForm::display( $field_data['type'], $value, $params->name, $field_data, $this->pod_data, $this->id() );
					} else {
						// Attempt to process each value independently.
						foreach ( $value as $k => $val ) {
							$value[ $k ] = PodsForm::display( $field_data['type'], $val, $params->name, $field_data, $this->pod_data, $this->id() );
						}
					}
				}

				if ( $post_temp ) {
					// @codingStandardsIgnoreLine.
					$post    = $old_post;
					// @codingStandardsIgnoreLine.
					$post_ID = $old_post_id;
				}
			} else {
				$value = PodsForm::value( $field_data['type'], $value, $params->name, $field_data, $this->pod_data, $this->id() );
			}//end if
		}//end if

		/**
		 * Modify value returned by field() directly before output.
		 *
		 * Will not run if value was null
		 *
		 * @since unknown
		 *
		 * @param array|string|null $value  Value to be returned.
		 * @param array|object      $row    Current row being outputted.
		 * @param array             $params Params array passed to field().
		 * @param object|Pods       $this   Current Pods object.
		 */
		$value = apply_filters( 'pods_pods_field', $value, $this->row(), $params, $this );

		return $value;
	}

	/**
	 * Check if an item field has a specific value in it
	 *
	 * @param string $field Field name.
	 * @param mixed  $value Value to check.
	 * @param int    $id    (optional) ID of the pod item to check.
	 *
	 * @return bool Whether the value was found
	 *
	 * @since 2.3.3
	 */
	public function has( $field, $value, $id = null ) {

		$pod =& $this;

		if ( null === $id ) {
			$id = $this->id();
			// @codingStandardsIgnoreLine.
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		$this->do_hook( 'has', $field, $value, $id );

		if ( ! isset( $this->fields[ $field ] ) ) {
			return false;
		}

		// Tableless fields.
		if ( in_array( $this->fields[ $field ]['type'], PodsForm::tableless_field_types(), true ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			if ( 'pick' === $this->fields[ $field ]['type'] && in_array( $this->fields[ $field ]['pick_object'], PodsForm::simple_tableless_objects(), true ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) ) {
					$current_value = (array) $current_value;
				}

				foreach ( $current_value as $v ) {
					// @codingStandardsIgnoreLine.
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			} else {
				$related_ids = $this->data->api->lookup_related_items( $this->fields[ $field ]['id'], $this->pod_data['id'], $id, $this->fields[ $field ], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^\D]/', $v ) ) {
						$value[ $k ] = (int) $v;
					}

					// @todo Convert slugs into IDs.
				}

				foreach ( $related_ids as $v ) {
					// @codingStandardsIgnoreLine.
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			}//end if
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::text_field_types(), true ) ) {
			// Text fields.
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				return stripos( $current_value, $value );
			}
		} else {
			// All other fields.
			return $this->is( $field, $value, $id );
		}//end if

		return false;
	}

	/**
	 * Check if an item field is a specific value
	 *
	 * @param string $field Field name.
	 * @param mixed  $value Value to check.
	 * @param int    $id    (optional) ID of the pod item to check.
	 *
	 * @return bool Whether the value was found
	 *
	 * @since 2.3.3
	 */
	public function is( $field, $value, $id = null ) {

		$pod =& $this;

		if ( null === $id ) {
			$id = $this->id();
			// @codingStandardsIgnoreLine.
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		$this->do_hook( 'is', $field, $value, $id );

		if ( ! isset( $this->fields[ $field ] ) ) {
			return false;
		}

		// Tableless fields.
		if ( in_array( $this->fields[ $field ]['type'], PodsForm::tableless_field_types(), true ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			$current_value = array();

			if ( 'pick' === $this->fields[ $field ]['type'] && in_array( $this->fields[ $field ]['pick_object'], PodsForm::simple_tableless_objects(), true ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) ) {
					$current_value = (array) $current_value;
				}

				foreach ( $current_value as $v ) {
					// @codingStandardsIgnoreLine.
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			} else {
				$related_ids = $this->data->api->lookup_related_items( $this->fields[ $field ]['id'], $this->pod_data['id'], $id, $this->fields[ $field ], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^\D]/', $v ) ) {
						$value[ $k ] = (int) $v;
					}

					// @todo Convert slugs into IDs.
				}

				foreach ( $related_ids as $v ) {
					// @codingStandardsIgnoreLine.
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			}//end if

			if ( ! empty( $current_value ) ) {
				$current_value = array_filter( array_unique( $current_value ) );
			} else {
				$current_value = array();
			}

			if ( ! empty( $value ) ) {
				$value = array_filter( array_unique( $value ) );
			} else {
				$value = array();
			}

			sort( $current_value );
			sort( $value );

			if ( $value === $current_value ) {
				return true;
			}
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::number_field_types(), true ) ) {
			// Number fields.
			$current_value = $pod->raw( $field );

			if ( (float) $current_value === (float) $value ) {
				return true;
			}
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::date_field_types(), true ) ) {
			// Date fields.
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				if ( strtotime( $current_value ) === strtotime( $value ) ) {
					return true;
				}
			} elseif ( empty( $value ) ) {
				return true;
			}
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::text_field_types(), true ) ) {
			// Text fields.
			$current_value = $pod->raw( $field );

			if ( (string) $current_value === (string) $value ) {
				return true;
			}
		} else {
			// All other fields.
			$current_value = $pod->raw( $field );

			if ( $current_value === $value ) {
				return true;
			}
		}//end if

		return false;
	}

	/**
	 * Return the item ID
	 *
	 * @return int
	 * @since 2.0.0
	 */
	public function id() {

		if ( isset( $this->data->row['id'] ) ) {
			// If we already have data loaded return that ID.
			return $this->data->row['id'];
		}

		return $this->field( $this->data->field_id );
	}

	/**
	 * Return the previous item ID, loops at the last id to return the first
	 *
	 * @param int|null          $id              ID to start from.
	 * @param array|object|null $params_override Override the find() parameters.
	 *
	 * @return int
	 * @since 2.0.0
	 */
	public function prev_id( $id = null, $params_override = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		$id = (int) $id;

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'where'   => "`t`.`{$this->data->field_id}` < {$id}",
			'orderby' => "`t`.`{$this->data->field_id}` DESC",
			'limit'   => 1,
		);

		if ( ! empty( $params_override ) || ! empty( $this->params ) ) {
			if ( ! empty( $params_override ) ) {
				$params = $params_override;
			} elseif ( ! empty( $this->params ) ) {
				$params = $this->params;
			}

			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			if ( 0 < $id ) {
				if ( isset( $params['where'] ) && ! empty( $params['where'] ) ) {
					$params['where']   = (array) $params['where'];
					$params['where'][] = "`t`.`{$this->data->field_id}` < {$id}";
				} else {
					$params['where'] = "`t`.`{$this->data->field_id}` < {$id}";
				}
			} elseif ( isset( $params['offset'] ) && 0 < $params['offset'] ) {
				$params['offset'] --;
			} elseif ( 0 < $this->data->row_number && ! isset( $params['offset'] ) && ! empty( $this->params ) ) {
				$params['offset'] = $this->data->row_number - 1;
			} else {
				return 0;
			}

			if ( isset( $params['orderby'] ) && ! empty( $params['orderby'] ) ) {
				if ( is_array( $params['orderby'] ) ) {
					foreach ( $params['orderby'] as $orderby => $dir ) {
						$dir = strtoupper( $dir );

						if ( ! in_array( $dir, array( 'ASC', 'DESC' ), true ) ) {
							continue;
						}

						if ( 'ASC' === $dir ) {
							$params['orderby'][ $orderby ] = 'DESC';
						} else {
							$params['orderby'][ $orderby ] = 'ASC';
						}
					}

					$params['orderby'][ $this->data->field_id ] = 'DESC';
				} elseif ( "`t`.`{$this->data->field_id}` DESC" !== $params['orderby'] ) {
					$params['orderby'] .= ", `t`.`{$this->data->field_id}` DESC";
				}
			}//end if

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}//end if

		$pod = pods( $this->pod, $params );

		$new_id = 0;

		if ( $pod->fetch() ) {
			$new_id = $pod->id();
		}

		$new_id = $this->do_hook( 'prev_id', $new_id, $id, $pod, $params_override );

		return $new_id;
	}

	/**
	 * Return the next item ID, loops at the first id to return the last
	 *
	 * @param int|null          $id              ID to start from.
	 * @param array|object|null $params_override Override the find() parameters.
	 *
	 * @return int
	 * @since 2.0.0
	 */
	public function next_id( $id = null, $params_override = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		$id = (int) $id;

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'where'   => "{$id} < `t`.`{$this->data->field_id}`",
			'orderby' => "`t`.`{$this->data->field_id}` ASC",
			'limit'   => 1,
		);

		if ( ! empty( $params_override ) || ! empty( $this->params ) ) {
			if ( ! empty( $params_override ) ) {
				$params = $params_override;
			} elseif ( ! empty( $this->params ) ) {
				$params = $this->params;
			}

			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			if ( 0 < $id ) {
				if ( isset( $params['where'] ) && ! empty( $params['where'] ) ) {
					$params['where']   = (array) $params['where'];
					$params['where'][] = "{$id} < `t`.`{$this->data->field_id}`";
				} else {
					$params['where'] = "{$id} < `t`.`{$this->data->field_id}`";
				}
			} elseif ( ! isset( $params['offset'] ) ) {
				if ( ! empty( $this->params ) && - 1 < $this->data->row_number ) {
					$params['offset'] = $this->data->row_number + 1;
				} else {
					$params['offset'] = 1;
				}
			} else {
				$params['offset'] ++;
			}

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}//end if

		$pod = pods( $this->pod, $params );

		$new_id = 0;

		if ( $pod->fetch() ) {
			$new_id = $pod->id();
		}

		$new_id = $this->do_hook( 'next_id', $new_id, $id, $pod, $params_override );

		return $new_id;
	}

	/**
	 * Return the first item ID
	 *
	 * @param array|object|null $params_override Override the find() parameters.
	 *
	 * @return int
	 * @since 2.3.0
	 */
	public function first_id( $params_override = null ) {

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'orderby' => "`t`.`{$this->data->field_id}` ASC",
			'limit'   => 1,
		);

		if ( ! empty( $params_override ) || ! empty( $this->params ) ) {
			if ( ! empty( $params_override ) ) {
				$params = $params_override;
			} elseif ( ! empty( $this->params ) ) {
				$params = $this->params;
			}

			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['offset'] = 0;
			$params['limit']  = 1;
		}

		$pod = pods( $this->pod, $params );

		$new_id = 0;

		if ( $pod->fetch() ) {
			$new_id = $pod->id();
		}

		$new_id = $this->do_hook( 'first_id', $new_id, $pod, $params_override );

		return $new_id;
	}

	/**
	 * Return the last item ID
	 *
	 * @param array|object|null $params_override Override the find() parameters.
	 *
	 * @return int
	 * @since 2.3.0
	 */
	public function last_id( $params_override = null ) {

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'orderby' => "`t`.`{$this->data->field_id}` DESC",
			'limit'   => 1,
		);

		if ( ! empty( $params_override ) || ! empty( $this->params ) ) {
			if ( ! empty( $params_override ) ) {
				$params = $params_override;
			} elseif ( ! empty( $this->params ) ) {
				$params = $this->params;
			}

			if ( is_object( $params ) ) {
				$params = get_object_vars( $params );
			}

			if ( isset( $params['total_found'] ) ) {
				$params['offset'] = $params['total_found'] - 1;
			} else {
				$params['offset'] = $this->total_found() - 1;
			}

			if ( isset( $params['orderby'] ) && ! empty( $params['orderby'] ) ) {
				if ( is_array( $params['orderby'] ) ) {
					foreach ( $params['orderby'] as $orderby => $dir ) {
						$dir = strtoupper( $dir );

						if ( ! in_array( $dir, array( 'ASC', 'DESC' ), true ) ) {
							continue;
						}

						if ( 'ASC' === $dir ) {
							$params['orderby'][ $orderby ] = 'DESC';
						} else {
							$params['orderby'][ $orderby ] = 'ASC';
						}
					}

					$params['orderby'][ $this->data->field_id ] = 'DESC';
				} elseif ( "`t`.`{$this->data->field_id}` DESC" !== $params['orderby'] ) {
					$params['orderby'] .= ", `t`.`{$this->data->field_id}` DESC";
				}
			}//end if

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}//end if

		$pod = pods( $this->pod, $params );

		$new_id = 0;

		if ( $pod->fetch() ) {
			$new_id = $pod->id();
		}

		$new_id = $this->do_hook( 'last_id', $new_id, $pod, $params_override );

		return $new_id;
	}

	/**
	 * Return the item name
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function index() {

		return $this->field( $this->data->field_index );
	}

	/**
	 * Find items of a pod, much like WP_Query, but with advanced table handling.
	 *
	 * @param array|object $params An associative array of parameters.
	 * @param int          $limit  (deprecated) Limit the number of items to find, -1 to return all items with no limit.
	 * @param string       $where  (deprecated) SQL WHERE declaration to use.
	 * @param string       $sql    (deprecated) For advanced use, a custom SQL query to run.
	 *
	 * @return \Pods The pod object
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/find/
	 */
	public function find( $params = null, $limit = 15, $where = null, $sql = null ) {

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$this->params = $params;

		$select = '`t`.*';

		if ( ! $this->pod_data instanceof Pods\Whatsit\Pod ) {
			return $this;
		}

		if ( 'table' === $this->pod_data['storage'] && ! in_array( $this->pod_data['type'], array(
			'pod',
			'table',
		), true ) ) {
			$select .= ', `d`.*';
		}

		$table = $this->pod_data['table'];

		if ( empty( $table ) ) {
			return $this;
		}

		$defaults = array(
			// Optimization parameters.
			'table'               => $table,
			'select'              => $select,
			'join'                => null,
			// Main query parameters.
			'where'               => $where,
			'groupby'             => null,
			'having'              => null,
			'orderby'             => null,
			// Pagination parameters.
			'limit'               => (int) $limit,
			'offset'              => null,
			'page'                => (int) $this->page,
			'page_var'            => $this->page_var,
			'pagination'          => (boolean) $this->pagination,
			// Search parameters.
			'search'              => (boolean) $this->search,
			'search_var'          => $this->search_var,
			'search_query'        => null,
			'search_mode'         => $this->search_mode,
			'search_across'       => false,
			'search_across_picks' => false,
			'search_across_files' => false,
			// Advanced parameters.
			'filters'             => $this->filters,
			'sql'                 => $sql,
			// Caching parameters.
			'expires'             => null,
			'cache_mode'          => 'cache',
		);

		if ( is_array( $params ) ) {
			$params = (object) array_merge( $defaults, $params );
		} elseif ( is_object( $params ) ) {
			$params = (object) array_merge( $defaults, get_object_vars( $params ) );
		} else {
			$defaults['orderby'] = $params;

			$params = (object) $defaults;
		}

		/**
		 * Filter the Pods::find() parameters.
		 *
		 * @param object $params Parameters to make lookup with.
		 */
		$params = apply_filters( 'pods_pods_find', $params );

		$params->limit = (int) $params->limit;

		if ( 0 === $params->limit ) {
			$params->limit = - 1;
		}

		$this->limit      = (int) $params->limit;
		$this->offset     = (int) $params->offset;
		$this->page       = (int) $params->page;
		$this->page_var   = $params->page_var;
		$this->pagination = (boolean) $params->pagination;
		$this->search     = (boolean) $params->search;
		$this->search_var = $params->search_var;
		$params->join     = (array) $params->join;

		if ( empty( $params->search_query ) ) {
			$params->search_query = pods_v_sanitized( $this->search_var, 'get', '' );
		}

		$pod_type     = $this->pod_data->get_type();
		$storage_type = $this->pod_data->get_storage();

		// Add prefix to $params->orderby if needed and handle field => ASC|DESC formats.
		if ( ! empty( $params->orderby ) ) {
			if ( ! is_array( $params->orderby ) ) {
				$params->orderby = array( $params->orderby );
			}

			foreach ( $params->orderby as $key => $prefix_orderby ) {
				if (
					false !== strpos( $prefix_orderby, ',' )
					|| false !== strpos( $prefix_orderby, '(' )
					|| false !== stripos( $prefix_orderby, ' AS ' )
					|| false !== strpos( $prefix_orderby, '`' )
					|| false !== strpos( $prefix_orderby, '.' )
				) {
					continue;
				}

				if ( in_array( strtoupper( $prefix_orderby ), [ 'ASC', 'DESC' ], true ) ) {
					// Handle field => ASC|DESC.
					$field_name = $key;
					$dir        = $prefix_orderby;
				} elseif ( false !== stripos( $prefix_orderby, ' ASC' ) ) {
					// Handle field ASC.
					$field_name = trim( str_ireplace( [ '`', ' ASC' ], '', $prefix_orderby ) );
					$dir        = 'ASC';
				} elseif ( false !== stripos( $prefix_orderby, ' DESC' ) ) {
					// Handle field DESC.
					$field_name = trim( str_ireplace( [ '`', ' DESC' ], '', $prefix_orderby ) );
					$dir        = 'DESC';
				} else {
					// Default assumes no order was set but the field was given.
					$field_name = trim( str_ireplace( '`', '', $prefix_orderby ) );
					$dir        = 'ASC';
				}

				// Invalid orderby provided.
				if ( empty( $field_name ) ) {
					unset( $params->orderby[ $key ] );

					continue;
				}

				$order_field = $this->fields( $field_name );

				if ( $order_field instanceof Field ) {
					$field_name = $order_field->get_name();

					$is_object_field      = $order_field instanceof Object_Field;
					$is_pod_or_table_type = in_array( $pod_type, [ 'pod', 'table' ], true );

					if ( in_array( $order_field['type'], $tableless_field_types, true ) ) {
						$order_object_type = $order_field->get_related_object_type();

						if ( in_array( $order_object_type, $simple_tableless_objects, true ) ) {
							if ( $is_object_field || 'table' === $storage_type ) {
								if ( ! $is_object_field && ! $is_pod_or_table_type ) {
									$field_name = "`d`.`{$field_name}`";
								} else {
									$field_name = "`t`.`{$field_name}`";
								}
							} else {
								$field_name = "`{$field_name}`.`meta_value`";
							}
						} else {
							$table = $order_field->get_table_info();

							if ( ! empty( $table ) ) {
								$field_name = "`{$field_name}`.`" . $table['field_index'] . '`';
							}
						}
					} elseif ( $is_object_field || 'table' === $storage_type ) {
						if ( ! $is_object_field && ! $is_pod_or_table_type ) {
							$field_name = "`d`.`{$field_name}`";
						} else {
							$field_name = "`t`.`{$field_name}`";
						}
					} elseif ( 'meta' === $storage_type ) {
						$field_name = "`{$field_name}`.`meta_value`";
					}
				}

				$params->orderby[ $key ] = "{$field_name} {$dir}";
			}//end foreach
		}//end if

		$this->data->select( $params );

		return $this;
	}

	/**
	 * Fetch an item from a Pod. If $id is null, it will return the next item in the list after running find().
	 * You can rewind the list back to the start by using reset().
	 *
	 * Providing an $id will fetch a specific item from a Pod, much like a call to pods(), and can handle either an id
	 * or slug.
	 *
	 * @see   PodsData::fetch
	 *
	 * @param int  $id           ID or slug of the item to fetch.
	 * @param bool $explicit_set Whether to set explicitly (use false when in loop).
	 *
	 * @return array An array of fields from the row
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/fetch/
	 */
	public function fetch( $id = null, $explicit_set = true ) {

		/**
		 * Runs directly before an item is fetched by fetch().
		 *
		 * @since unknown
		 *
		 * @param int|string|null $id   Item ID being fetched or null.
		 * @param object|Pods     $this Current Pods object.
		 */
		do_action( 'pods_pods_fetch', $id, $this );

		if ( ! empty( $id ) ) {
			$this->params = array();
		}

		$this->data->fetch( $id, $explicit_set );

		return $this->row();
	}

	/**
	 * (Re)set the MySQL result pointer
	 *
	 * @see   PodsData::reset
	 *
	 * @param int $row ID of the row to reset to.
	 *
	 * @return \Pods The pod object
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/reset/
	 */
	public function reset( $row = null ) {

		/**
		 * Runs directly before the Pods object is reset by reset()
		 *
		 * @since unknown
		 *
		 * @param int|string|null The ID of the row being reset to or null if being reset to the beginning.
		 * @param object|Pods $this Current Pods object.
		 */
		do_action( 'pods_pods_reset', $row, $this );

		$this->data->reset( $row );

		return $this;
	}

	/**
	 * Fetch the total row count returned by the last call to find(), based on the 'limit' parameter set.
	 *
	 * This is different than the total number of rows found in the database, which you can get with total_found().
	 *
	 * @see   PodsData::total
	 *
	 * @return int Number of rows returned by find(), based on the 'limit' parameter set
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/total/
	 */
	public function total() {

		do_action( 'pods_pods_total', $this );

		$this->data->total();

		return $this->data->total;
	}

	/**
	 * Fetch the total amount of rows found by the last call to find(), regardless of the 'limit' parameter set.
	 *
	 * This is different than the total number of rows limited by the current call, which you can get with total().
	 *
	 * @see   PodsData::total_found
	 *
	 * @params null|array $params The list of Pods::find() parameters to use, otherwise use current dataset to calculate total found.
	 *
	 * @return int Number of rows returned by find(), regardless of the 'limit' parameter
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/total-found/
	 */
	public function total_found( $params = null ) {
		// Support find() shorthand to get total_found() for a specific dataset.
		if ( is_array( $params ) ) {
			$this->find( $params );

			return $this->total_found();
		}

		/**
		 * Runs directly before the value of total_found() is determined and returned.
		 *
		 * @since unknown
		 *
		 * @param object|Pods $this Current Pods object.
		 */
		do_action( 'pods_pods_total_found', $this );

		$this->data->total_found();

		return (int) $this->data->total_found;
	}

	/**
	 * Get the total count of all rows in a simple find() across the whole Pod. This will perform a new find() request
	 * and then return the total number of rows found.
	 *
	 * This method is non-destructive and it will not alter the current Pod object.
	 *
	 * @since 2.8.9
	 *
	 * @return int The total count of all rows in a simple find() across the whole Pod.
	 */
	public function total_all_rows() {
		$field_id = pods_v( 'field_id', $this->pod_data );

		if ( empty( $field_id ) ) {
			return 0;
		}

		$pod = clone $this;

		// Make a simple request so we can perform a total_found() SQL request.
		$params = [
			'distinct' => false,
			'select'   => 't.' . $this->pod_data['field_id'],
			'limit'    => 1,
		];

		$pod->find( $params );

		return $pod->total_found();
	}

	/**
	 * Fetch the total number of pages, based on total rows found and the last find() limit
	 *
	 * @param null|int $limit  Rows per page.
	 * @param null|int $offset Offset of rows.
	 * @param null|int $total  Total rows.
	 *
	 * @return int Number of pages.
	 * @since 2.3.10
	 */
	public function total_pages( $limit = null, $offset = null, $total = null ) {

		$this->do_hook( 'total_pages' );

		if ( null === $limit ) {
			$limit = $this->limit;
		}

		if ( null === $offset ) {
			$offset = $this->offset;
		}

		if ( null === $total ) {
			$total = $this->total_found();
		}

		// No limit means one page.
		if ( $limit < 1 ) {
			return 1;
		}

		return (int) ceil( ( $total - $offset ) / $limit );

	}

	/**
	 * Fetch the zebra switch
	 *
	 * @see   PodsData::zebra
	 *
	 * @return bool Zebra state
	 * @since 1.12
	 */
	public function zebra() {

		$this->do_hook( 'zebra' );

		return $this->data->zebra();
	}

	/**
	 * Fetch the nth state
	 *
	 * @see   PodsData::nth
	 *
	 * @param int|string $nth The $nth to match on the PodsData::row_number.
	 *
	 * @return bool Whether $nth matches
	 * @since 2.3.0
	 */
	public function nth( $nth = null ) {

		$this->do_hook( 'nth', $nth );

		return $this->data->nth( $nth );
	}

	/**
	 * Fetch the current position in the loop (starting at 1)
	 *
	 * @see   PodsData::position
	 *
	 * @return int Current row number (+1)
	 * @since 2.3.0
	 */
	public function position() {

		$this->do_hook( 'position' );

		return $this->data->position();
	}

	/**
	 * Add an item to a Pod by giving an array of field data or set a specific field to
	 * a specific value if you're just wanting to add a new item but only set one field.
	 *
	 * You may be looking for save() in most cases where you're setting a specific field.
	 *
	 * @see   PodsAPI::save_pod_item
	 *
	 * @param array|string $data  Either an associative array of field information or a field name.
	 * @param mixed        $value (optional) Value of the field, if $data is a field name.
	 *
	 * @return int The item ID
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/add/
	 */
	public function add( $data = null, $value = null ) {

		if ( null !== $value ) {
			$data = array( $data => $value );
		}

		$data = (array) $this->do_hook( 'add', $data );

		if ( empty( $data ) ) {
			return 0;
		}

		$params = array(
			'pod'                 => $this->pod,
			'data'                => $data,
			'allow_custom_fields' => true,
		);

		return $this->data->api->save_pod_item( $params );
	}

	/**
	 * Add an item to the values of a relationship field, add a value to a number field (field+1), add time to a date
	 * field, or add text to a text field
	 *
	 * @see   PodsAPI::save_pod_item
	 *
	 * @param string $field Field name.
	 * @param mixed  $value IDs to add, int|float to add to number field, string for dates (+1 day), or string for text.
	 * @param int    $id    (optional) ID of the pod item to update.
	 *
	 * @return int The item ID
	 *
	 * @since 2.3.0
	 */
	public function add_to( $field, $value, $id = null ) {

		$pod =& $this;

		$fetch = false;

		if ( null === $id ) {
			$fetch = true;

			$id = $pod->id();
			// @codingStandardsIgnoreLine
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		$this->do_hook( 'add_to', $field, $value, $id );

		if ( ! isset( $this->fields[ $field ] ) ) {
			return $id;
		}

		// Tableless fields.
		if ( in_array( $this->fields[ $field ]['type'], PodsForm::tableless_field_types(), true ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			if ( 'pick' === $this->fields[ $field ]['type'] && in_array( $this->fields[ $field ]['pick_object'], PodsForm::simple_tableless_objects(), true ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) || ( ! is_array( $current_value ) && 0 < strlen( $current_value ) ) ) {
					$current_value = (array) $current_value;
				} else {
					$current_value = array();
				}

				$value = array_merge( $current_value, $value );
			} else {
				$related_ids = $this->data->api->lookup_related_items( $this->fields[ $field ]['id'], $this->pod_data['id'], $id, $this->fields[ $field ], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^\D]/', $v ) ) {
						$value[ $k ] = (int) $v;
					}
				}

				$value = array_merge( $related_ids, $value );
			}//end if

			if ( ! empty( $value ) ) {
				$value = array_filter( array_unique( $value ) );
			} else {
				$value = array();
			}

			if ( empty( $value ) ) {
				return $id;
			}
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::number_field_types(), true ) ) {
			// Number fields.
			$current_value = (float) $pod->raw( $field );

			$value = ( $current_value + (float) $value );
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::date_field_types(), true ) ) {
			// Date fields.
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				$value = strtotime( $value, strtotime( $current_value ) );
			} else {
				$value = strtotime( $value );
			}
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::text_field_types(), true ) ) {
			// Text fields.
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				$value = $current_value . $value;
			}
		}//end if

		// @todo handle object fields and taxonomies.
		$params = array(
			'pod'  => $this->pod,
			'id'   => $id,
			'data' => array(
				$field => $value,
			),
		);

		$id = $this->data->api->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
			// Clear local var cache of field values.
			$pod->data->row = array();

			$pod->fetch( $id, false );
		}

		return $id;
	}

	/**
	 * Remove an item from the values of a relationship field, remove a value from a number field (field-1), remove
	 * time to a date field
	 *
	 * @see   PodsAPI::save_pod_item
	 *
	 * @param string $field Field name.
	 * @param mixed  $value IDs to add, int|float to add to number field, string for dates (-1 day), or string for text.
	 * @param int    $id    (optional) ID of the pod item to update.
	 *
	 * @return int The item ID
	 *
	 * @since 2.3.3
	 */
	public function remove_from( $field, $value = null, $id = null ) {

		$pod =& $this;

		$fetch = false;

		if ( null === $id ) {
			$fetch = true;

			$id = $this->id();
			// @codingStandardsIgnoreLine
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		$this->do_hook( 'remove_from', $field, $value, $id );

		if ( ! isset( $this->fields[ $field ] ) ) {
			return $id;
		}

		// Tableless fields.
		if ( in_array( $this->fields[ $field ]['type'], PodsForm::tableless_field_types(), true ) ) {
			if ( empty( $value ) ) {
				$value = array();
			}

			if ( ! empty( $value ) ) {
				if ( ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}

				if ( 'pick' === $this->fields[ $field ]['type'] && in_array( $this->fields[ $field ]['pick_object'], PodsForm::simple_tableless_objects(), true ) ) {
					$current_value = $pod->raw( $field );

					if ( ! empty( $current_value ) ) {
						$current_value = (array) $current_value;
					}

					foreach ( $current_value as $k => $v ) {
						// @codingStandardsIgnoreLine.
						if ( in_array( $v, $value ) ) {
							unset( $current_value[ $k ] );
						}
					}

					$value = $current_value;
				} else {
					$related_ids = $this->data->api->lookup_related_items( $this->fields[ $field ]['id'], $this->pod_data['id'], $id, $this->fields[ $field ], $this->pod_data );

					foreach ( $value as $k => $v ) {
						if ( ! preg_match( '/[^\D]/', $v ) ) {
							$value[ $k ] = (int) $v;
						}

						// @todo Convert slugs into IDs.
					}

					foreach ( $related_ids as $k => $v ) {
						// @codingStandardsIgnoreLine.
						if ( in_array( $v, $value ) ) {
							unset( $related_ids[ $k ] );
						}
					}

					$value = $related_ids;
				}//end if

				if ( ! empty( $value ) ) {
					$value = array_filter( array_unique( $value ) );
				} else {
					$value = array();
				}
			}//end if
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::number_field_types(), true ) ) {
			// Number fields.
			// Date fields don't support empty for removing.
			if ( empty( $value ) ) {
				return $id;
			}

			$current_value = (float) $pod->raw( $field );

			$value = ( $current_value - (float) $value );
		} elseif ( in_array( $this->fields[ $field ]['type'], PodsForm::date_field_types(), true ) ) {
			// Date fields.
			// Date fields don't support empty for removing.
			if ( empty( $value ) ) {
				return $id;
			}

			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				$value = strtotime( $value, strtotime( $current_value ) );
			} else {
				$value = strtotime( $value );
			}

			$value = date_i18n( 'Y-m-d h:i:s', $value );
		}//end if

		// @todo handle object fields and taxonomies.
		$params = array(
			'pod'  => $this->pod,
			'id'   => $id,
			'data' => array(
				$field => $value,
			),
		);

		$id = $this->data->api->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
			// Clear local var cache of field values.
			$pod->data->row = array();

			$pod->fetch( $id, false );
		}

		return $id;

	}

	/**
	 * Save an item by giving an array of field data or set a specific field to a specific value.
	 *
	 * Though this function has the capacity to add new items, best practice should direct you
	 * to use add() for that instead.
	 *
	 * @see   PodsAPI::save_pod_item
	 *
	 * @param array|string $data   Either an associative array of field information or a field name.
	 * @param mixed        $value  (optional) Value of the field, if $data is a field name.
	 * @param int          $id     (optional) ID of the pod item to update.
	 * @param array        $params (optional) Additional params to send to save_pod_item.
	 *
	 * @return int The item ID
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/save/
	 */
	public function save( $data = null, $value = null, $id = null, $params = null ) {

		if ( null !== $data && ! is_array( $data ) ) {
			$data = array( $data => $value );
		}

		$fetch = false;

		// @codingStandardsIgnoreLine
		if ( null === $id || ( $this->row() && $id == $this->id() ) ) {
			$fetch = true;

			if ( null === $id ) {
				$id = $this->id();
			}
		}

		$data = (array) $this->do_hook( 'save', $data, $id );

		if ( empty( $data ) && empty( $params['is_new_item'] ) && empty( $params['podsmeta'] ) ) {
			return $id;
		}

		$default = array();

		if ( ! empty( $params ) && is_array( $params ) ) {
			$default = $params;
		}

		// If ID is sent as part of data, use it and then unset it from the data.
		if ( isset( $data[ $this->pod_data['field_id'] ] ) ) {
			$id = $data[ $this->pod_data['field_id'] ];

			unset( $data[ $this->pod_data['field_id'] ] );
		}

		$params = array(
			'pod'                 => $this->pod,
			'id'                  => $id,
			'data'                => $data,
			'allow_custom_fields' => true,
			'clear_slug_cache'    => false,
		);

		if ( ! empty( $default ) ) {
			$params = array_merge( $params, $default );
		}

		$id = $this->data->api->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
			// Clear local var cache of field values.
			$this->data->row = array();

			$this->fetch( $id, false );
		}

		if ( ! empty( $this->pod_data['field_slug'] ) ) {
			if ( 0 < $id && $fetch ) {
				$slug = $this->field( $this->pod_data['field_slug'] );
			} else {
				$slug = pods( $this->pod, $id )->field( $this->pod_data['field_slug'] );
			}

			if ( 0 < strlen( $slug ) ) {
				pods_cache_clear( $slug, 'pods_items_' . $this->pod );
			}
		}

		return $id;
	}

	/**
	 * Delete an item
	 *
	 * @see   PodsAPI::delete_pod_item
	 *
	 * @param int $id ID of the Pod item to delete.
	 *
	 * @return bool Whether the item was successfully deleted
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/delete/
	 */
	public function delete( $id = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		$id = (int) $this->do_hook( 'delete', $id );

		if ( empty( $id ) ) {
			return false;
		}

		$params = array(
			'pod' => $this->pod,
			'id'  => $id,
		);

		return $this->data->api->delete_pod_item( $params );
	}

	/**
	 * Reset Pod
	 *
	 * @see   PodsAPI::reset_pod
	 *
	 * @return bool Whether the Pod was successfully reset
	 *
	 * @since 2.1.1
	 */
	public function reset_pod() {
		$params = [
			'id'   => $this->pod_id,
			'name' => $this->pod,
		];

		$this->data->id   = null;
		$this->data->row  = [];
		$this->data->data = [];

		$this->data->total       = 0;
		$this->data->total_found = 0;

		return $this->data->api->reset_pod( $params );
	}

	/**
	 * Duplicate an item
	 *
	 * @see   PodsAPI::duplicate_pod_item
	 *
	 * @param int $id ID of the pod item to duplicate.
	 *
	 * @return int|bool ID of the new pod item
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/duplicate/
	 */
	public function duplicate( $id = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		$id = (int) $this->do_hook( 'duplicate', $id );

		if ( empty( $id ) ) {
			return false;
		}

		$params = array(
			'pod' => $this->pod,
			'id'  => $id,
		);

		return $this->data->api->duplicate_pod_item( $params );
	}

	/**
	 * Import data / Save multiple rows of data at once
	 *
	 * @see   PodsAPI::import
	 *
	 * @param mixed  $import_data  PHP associative array or CSV input.
	 * @param bool   $numeric_mode Use IDs instead of the name field when matching.
	 * @param string $format       Format of import data, options are php or csv.
	 *
	 * @return array IDs of imported items
	 *
	 * @since 2.3.0
	 */
	public function import( $import_data, $numeric_mode = false, $format = null ) {
		return $this->data->api->import( $import_data, $numeric_mode, $format );
	}

	/**
	 * Export an item's data
	 *
	 * @see   PodsAPI::export_pod_item
	 *
	 * @param array       $fields (optional) Fields to export.
	 * @param int         $id     (optional) ID of the pod item to export.
	 * @param null|string $format (optional) The format of the export (php | json).
	 *
	 * @return array|bool Data array of the exported pod item
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/export/
	 */
	public function export( $fields = null, $id = null, $format = null ) {
		$params = array(
			'pod'     => $this->pod,
			'id'      => $id,
			'fields'  => null,
			'depth'   => 2,
			'flatten' => false,
			'context' => null,
			'format'  => $format,
		);

		if ( is_array( $fields ) && ( isset( $fields['fields'] ) || isset( $fields['depth'] ) ) ) {
			$params = array_merge( $params, $fields );
		} else {
			$params['fields'] = $fields;
		}

		if ( isset( $params['fields'] ) && is_array( $params['fields'] ) && ! in_array( $this->pod_data['field_id'], $params['fields'], true ) ) {
			$params['fields'] = array_merge( array( $this->pod_data['field_id'] ), $params['fields'] );
		}

		if ( null === $params['id'] ) {
			$params['id'] = $this->id();
		}

		$params = (array) $this->do_hook( 'export', $params );

		if ( empty( $params['id'] ) ) {
			return false;
		}

		$data = $this->data->api->export_pod_item( $params );

		if ( ! empty( $params['format'] ) && 'json' === $params['format'] ) {
			$data = wp_json_encode( (array) $data );
		}

		return $data;
	}

	/**
	 * Export data from all items
	 *
	 * @see   PodsAPI::export
	 *
	 * @param array $params An associative array of parameters.
	 *
	 * @return array Data arrays of all exported pod items
	 *
	 * @since 2.3.0
	 */
	public function export_data( $params = null ) {
		$defaults = array(
			'fields' => null,
			'depth'  => 2,
			'params' => null,
		);

		if ( empty( $params ) ) {
			$params = $defaults;
		} else {
			$params = array_merge( $defaults, (array) $params );
		}

		return $this->data->api->export( $this, $params );
	}

	/**
	 * Display the pagination controls, types supported by default
	 * are simple, paginate and advanced. The base and format parameters
	 * are used only for the paginate view.
	 *
	 * @param array|object $params Associative array of parameters.
	 *
	 * @return string Pagination HTML
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/pagination/
	 */
	public function pagination( $params = null ) {

		if ( empty( $params ) ) {
			$params = array();
		} elseif ( ! is_array( $params ) ) {
			$params = array( 'label' => $params );
		}

		$this->page_var = pods_v( 'page_var', $params, $this->page_var );

		$url = pods_query_arg( null, null, $this->page_var );

		$append = '?';

		if ( false !== strpos( $url, '?' ) ) {
			$append = '&';
		}

		$defaults = array(
			'type'        => 'advanced',
			'label'       => __( 'Go to page:', 'pods' ),
			'show_label'  => true,
			'first_text'  => __( '&laquo; First', 'pods' ),
			'prev_text'   => __( '&lsaquo; Previous', 'pods' ),
			'next_text'   => __( 'Next &rsaquo;', 'pods' ),
			'last_text'   => __( 'Last &raquo;', 'pods' ),
			'prev_next'   => true,
			'first_last'  => true,
			'limit'       => (int) $this->limit,
			'offset'      => (int) $this->offset,
			'page'        => max( 1, (int) $this->page ),
			'mid_size'    => 2,
			'end_size'    => 1,
			'total_found' => $this->total_found(),
			'page_var'    => $this->page_var,
			'base'        => "{$url}{$append}%_%",
			'format'      => "{$this->page_var}=%#%",
			'class'       => '',
			'link_class'  => '',
		);

		if ( is_object( $params ) ) {
			$params = get_object_vars( $params );
		}

		$params = (object) array_merge( $defaults, (array) $params );

		$params->total = $this->total_pages( $params->limit, $params->offset, $params->total_found );

		if ( $params->limit < 1 || $params->total_found < 1 || 1 === $params->total || $params->total_found <= $params->offset ) {
			return $this->do_hook( 'pagination', $this->do_hook( 'pagination_' . $params->type, '', $params ), $params );
		}

		$pagination = $params->type;

		if ( ! in_array( $params->type, array( 'simple', 'advanced', 'paginate', 'list' ), true ) ) {
			$pagination = 'advanced';
		}

		ob_start();

		pods_view( PODS_DIR . 'ui/front/pagination/' . $pagination . '.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return $this->do_hook( 'pagination', $this->do_hook( 'pagination_' . $params->type, $output, $params ), $params );

	}

	/**
	 * Return a filter form for searching a Pod
	 *
	 * @param array|string $params Comma-separated list of fields or array of parameters.
	 *
	 * @return string Filters HTML
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/filters/
	 */
	public function filters( $params = null ) {
		// Only show placeholder text if in REST API block preview.
		if ( wp_is_json_request() && did_action( 'rest_api_init' ) ) {
			return '<em>[' . esc_html__( 'This is a placeholder. Filters and form fields are not included in block previews.', 'pods' ) . ']</em>';
		}

		$defaults = array(
			'fields' => $params,
			'label'  => '',
			'action' => '',
			'search' => '',
		);

		if ( is_array( $params ) ) {
			$params = array_merge( $defaults, $params );
		} else {
			$params = $defaults;
		}

		$pod =& $this;

		/**
		 * Filter the Pods::filters() parameters.
		 *
		 * @param array $params Parameters to filter with.
		 * @param Pods  $pod    Pods object.
		 */
		$params = apply_filters( 'pods_filters_params', $params, $pod );

		$fields = $params['fields'];

		if ( null !== $fields && ! is_array( $fields ) && 0 < strlen( $fields ) ) {
			$fields = explode( ',', $fields );
		}

		$object_fields = (array) pods_v( 'object_fields', $this->pod_data, array(), true );

		// Force array.
		if ( empty( $fields ) ) {
			$fields = array();
		} else {
			// Temporary.
			$filter_fields = $fields;

			$fields = array();

			foreach ( $filter_fields as $k => $field ) {
				$name = $k;

				$defaults = array(
					'name' => $name,
				);

				$is_field_object = $field instanceof Field;

				if ( ! is_array( $field ) && ! $is_field_object ) {
					$name = $field;

					$field = array(
						'name' => $name,
					);
				}

				// @codingStandardsIgnoreLine.
				$field = pods_config_merge_data( $defaults, $field );

				$field['name'] = trim( $field['name'] );

				if ( pods_v( 'hidden', $field, false, true ) ) {
					$field['type'] = 'hidden';
				}

				if ( isset( $object_fields[ $field['name'] ] ) ) {
					// @codingStandardsIgnoreLine.
					$fields[ $field['name'] ] = pods_config_merge_data( $object_fields[ $field['name'] ], $field );
				} elseif ( isset( $this->fields[ $field['name'] ] ) ) {
					// @codingStandardsIgnoreLine.
					$fields[ $field['name'] ] = pods_config_merge_data( $this->fields[ $field['name'] ], $field );
				}
			}//end foreach

			// Cleanup before get_defined_vars().
			unset( $filter_fields );
		}//end if

		$this->filters = array_keys( $fields );

		$label = $params['label'];

		if ( '' === $label ) {
			$label = __( 'Search', 'pods' );
		}

		$action = $params['action'];

		$search = trim( $params['search'] );

		if ( '' === $search ) {
			$search = pods_v_sanitized( $pod->search_var, 'get', '' );
		}

		ob_start();

		pods_view( PODS_DIR . 'ui/front/filters.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		/**
		 * Filter the HTML output of filters()
		 *
		 * @since unknown
		 *
		 * @param string      $output Filter output.
		 * @param array       $params Params array passed to filters().
		 * @param object|Pods $this   Current Pods object.
		 */
		return apply_filters( 'pods_pods_filters', $output, $params, $this );
	}

	/**
	 * Run a helper within a Pod Page or WP Template.
	 *
	 * @param string|array $helper Helper name.
	 * @param string       $value  Value to run the helper on.
	 * @param string       $name   Field name.
	 *
	 * @return mixed Anything returned by the helper
	 * @since 2.0.0
	 */
	public function helper( $helper, $value = null, $name = null ) {

		$params = array(
			'helper'     => $helper,
			'value'      => $value,
			'name'       => $name,
			'deprecated' => false,
		);

		if ( class_exists( 'Pods_Templates' ) ) {
			$params['deprecated'] = Pods_Templates::$deprecated;
		}

		if ( is_array( $helper ) ) {
			$params = array_merge( $params, $helper );
		}

		/**
		 * Allows changing whether callbacks are allowed to run.
		 *
		 * @param bool  $allow_callbacks Whether callbacks are allowed to run.
		 * @param array $params          Parameters used by Pods::helper() method.
		 *
		 * @since 2.8.0
		 */
		$allow_callbacks = (boolean) apply_filters( 'pods_helper_allow_callbacks', true, $params );

		if ( ! $allow_callbacks ) {
			return $value;
		}

		/**
		 * Allows changing whether to include the Pods object as the second value to the callback.
		 *
		 * @param bool  $include_obj Whether to include the Pods object as the second value to the callback.
		 * @param array $params      Parameters used by Pods::helper() method.
		 *
		 * @since 2.8.0
		 */
		$include_obj = (boolean) apply_filters( 'pods_helper_include_obj', false, $params );

		if ( ! is_callable( $params['helper'] ) ) {
			if ( $include_obj ) {
				return apply_filters( $params['helper'], $value, $this );
			} else {
				return apply_filters( $params['helper'], $value );
			}
		}

		$disallowed = array(
			'system',
			'exec',
			'popen',
			'eval',
			'preg_replace',
			'preg_replace_array',
			'preg_replace_callback',
			'preg_replace_callback_array',
			'preg_match',
			'preg_match_all',
			'create_function',
			'include',
			'include_once',
			'require',
			'require_once',
		);

		$allowed = array();

		/**
		 * Allows adjusting the disallowed callbacks as needed.
		 *
		 * @param array $disallowed List of callbacks not allowed.
		 * @param array $params     Parameters used by Pods::helper() method.
		 *
		 * @since 2.7.0
		 */
		$disallowed = apply_filters( 'pods_helper_disallowed_callbacks', $disallowed, $params );

		/**
		 * Allows adjusting the allowed callbacks as needed.
		 *
		 * @param array $allowed List of callbacks explicitly allowed.
		 * @param array $params  Parameters used by Pods::helper() method.
		 *
		 * @since 2.7.0
		 */
		$allowed = apply_filters( 'pods_helper_allowed_callbacks', $allowed, $params );

		// Clean up helper callback (if string).
		if ( is_string( $params['helper'] ) ) {
			$params['helper'] = strip_tags( str_replace( array( '`', chr( 96 ) ), "'", $params['helper'] ) );
		}

		$is_allowed = false;

		if ( ! empty( $allowed ) ) {
			if ( in_array( $params['helper'], $allowed, true ) ) {
				$is_allowed = true;
			}
		} elseif ( ! in_array( $params['helper'], $disallowed, true ) ) {
			$is_allowed = true;
		}

		if ( ! $is_allowed ) {
			return $value;
		}

		if ( $include_obj ) {
			return call_user_func( $params['helper'], $value, $this );
		}

		return call_user_func( $params['helper'], $value );
	}

	/**
	 * Display the pod template (singular) in the context from within a fetch() loop.
	 *
	 * @see   Pods_Templates::template
	 *
	 * @param string      $template_name The template name.
	 * @param string|null $code          Custom template code to use instead.
	 * @param bool        $deprecated    Whether to use deprecated functionality based on old function usage.
	 *
	 * @return mixed Template output
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/template/
	 */
	public function template_singular( $template_name, $code = null, $deprecated = false ) {
		$old_id = $this->id;

		$this->id = $this->id();

		$out = $this->template( $template_name, $code, $deprecated );

		if ( ! empty( $old_id ) ) {
			$this->id = $old_id;
		}

		return $out;
	}

	/**
	 * Display the pod template.
	 *
	 * @see   Pods_Templates::template
	 *
	 * @param string      $template_name The template name.
	 * @param string|null $code          Custom template code to use instead.
	 * @param bool        $deprecated    Whether to use deprecated functionality based on old function usage.
	 *
	 * @return mixed Template output
	 *
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/template/
	 */
	public function template( $template_name, $code = null, $deprecated = false ) {

		$out = null;

		$obj =& $this;

		if ( class_exists( 'Pods_Templates' ) ) {
			$out = Pods_Templates::template( $template_name, $code, $this, $deprecated );
		} elseif ( ! empty( $code ) ) {
			// backwards compatibility.
			$code = str_replace( '$this->', '$obj->', $code );

			/**
			 * Filter the template code before running it.
			 *
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods  $pod            Pods object.
			 */
			$code = apply_filters( 'pods_templates_pre_template', $code, $template_name, $this );

			/**
			 * Filter the template code before running it.
			 *
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods  $pod            Pods object.
			 */
			$code = apply_filters( "pods_templates_pre_template_{$template_name}", $code, $template_name, $this );

			ob_start();

			if ( ! empty( $code ) ) {
				// Only detail templates need $this->id.
				if ( empty( $this->id ) ) {
					while ( $this->fetch() ) {
						// @codingStandardsIgnoreLine
						echo $this->do_magic_tags( $code );
					}
				} else {
					// @codingStandardsIgnoreLine
					echo $this->do_magic_tags( $code );
				}
			}

			$out = ob_get_clean();

			/**
			 * Filter the template output.
			 *
			 * @param string $out           Template output.
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods   $pod           Pods object.
			 */
			$out = apply_filters( 'pods_templates_post_template', $out, $code, $template_name, $this );

			/**
			 * Filter the template output.
			 *
			 * @param string $out           Template output.
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods   $pod           Pods object.
			 */
			$out = apply_filters( "pods_templates_post_template_{$template_name}", $out, $code, $template_name, $this );

			/**
			 * Filter the final template output.
			 *
			 * @param string $out  Template output.
			 * @param string $code Template code.
			 * @param Pods   $pod  Pods object.
			 */
			$out = apply_filters( 'pods_templates_do_template', $out, $code, $this );
		} elseif ( trim( preg_replace( '/[^a-zA-Z0-9_\-\/]/', '', $template_name ), ' /-' ) === $template_name ) {
			ob_start();

			$default_templates = array(
				'pods/' . $template_name,
				'pods-' . $template_name,
				$template_name,
			);

			/**
			 * Filter the default Pods Template files.
			 *
			 * @param array $default_templates Default Pods Template files.
			 */
			$default_templates = apply_filters( 'pods_template_default_templates', $default_templates );

			// Only detail templates need $this->id.
			if ( empty( $this->id ) ) {
				while ( $this->fetch() ) {
					pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );
				}
			} else {
				pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );
			}

			$out = ob_get_clean();

			/**
			 * Filter the template output.
			 *
			 * @param string $out           Template output.
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods   $pod           Pods object.
			 */
			$out = apply_filters( 'pods_templates_post_template', $out, $code, $template_name, $this );

			/**
			 * Filter the template output.
			 *
			 * @param string $out           Template output.
			 * @param string $code          Template code.
			 * @param string $template_name Template name.
			 * @param Pods   $pod           Pods object.
			 */
			$out = apply_filters( "pods_templates_post_template_{$template_name}", $out, $code, $template_name, $this );
		}//end if

		/**
		 * Filter the final content.
		 *
		 * @since 2.8.0
		 *
		 * @param string $out  The template content.
		 * @param string $code The original template code.
		 * @param Pods   $pod  The current Pods object.
		 */
		return apply_filters( 'pods_template_content', $out, $code, $this );
	}

	/**
	 * Embed a form to add / edit a pod item from within your theme. Provide an array of $fields to include
	 * and override options where needed. For WP object based Pods, you can pass through the WP object
	 * field names too, such as "post_title" or "post_content" for example.
	 *
	 * @param array  $params    (optional) Fields to show on the form, defaults to all fields.
	 * @param string $label     (optional) Save button label, defaults to "Save Changes".
	 * @param string $thank_you (optional) Thank you URL to send to upon success.
	 *
	 * @return bool|mixed
	 * @since 2.0.0
	 * @link  https://docs.pods.io/code/pods/form/
	 */
	public function form( $params = null, $label = null, $thank_you = null ) {
		// Only show placeholder text if in REST API block preview.
		if ( wp_is_json_request() && did_action( 'rest_api_init' ) ) {
			return '<em>[' . esc_html__( 'This is a placeholder. Filters and form fields are not included in block previews.', 'pods' ) . ']</em>';
		}

		// Check for anonymous submissions.
		if ( ! is_user_logged_in() ) {
			$session_auto_start = pods_session_auto_start();

			// Check if Pods sessions are disabled.
			if ( false === $session_auto_start ) {
				return sprintf(
					'<p><strong>%1$s:</strong> %2$s, <a href="%3$s">%4$s</a> %5$s.</p>',
					esc_html__( 'Error', 'pods' ),
					esc_html__( 'Anonymous form submissions are not enabled for this site', 'pods' ),
					esc_url( wp_login_url( pods_current_url() ) ),
					esc_html__( 'try logging in first', 'pods' ),
					esc_html__( 'contacting your site administrator', 'pods' )
				);
			}

			// Check if we need to turn on sessions and have visitor refresh page.
			if ( 'auto' === $session_auto_start ) {
				pods_update_setting( 'pods_session_auto_start', '1' );

				return sprintf(
					'<p><strong>%1$s:</strong> %2$s</p>',
					esc_html__( 'Error', 'pods' ),
					esc_html__( 'Please refresh the page to access this form.', 'pods' )
				);
			}

			// Check if the session started properly.
			if ( '' === pods_session_id() ) {
				return sprintf(
					'<p><strong>%1$s:</strong> %2$s</p>',
					esc_html__( 'Error', 'pods' ),
					esc_html__( 'Anonymous form submissions are not compatible with sessions on this site.', 'pods' )
				);
			}
		}

		$defaults = array(
			'fields'      => $params,
			'label'       => $label,
			'thank_you'   => $thank_you,
			'fields_only' => false,
			'output_type' => 'div',
		);

		if ( is_array( $params ) ) {
			$params = array_merge( $defaults, $params );
		} else {
			$params = $defaults;
		}

		$pod =& $this;

		$params = $this->do_hook( 'form_params', $params );

		$form_fields = $params['fields'];

		if ( null !== $form_fields && ! is_array( $form_fields ) && 0 < strlen( $form_fields ) ) {
			$form_fields = explode( ',', $form_fields );
		}

		$all_fields = pods_config_get_all_fields( $this->pod_data );

		$default_form = false;

		// Get all fields if form fields are empty.
		if ( empty( $form_fields ) ) {
			$form_fields = $all_fields;

			$default_form = true;
		}

		$fields = [];

		foreach ( $form_fields as $k => $field ) {
			$name = $k;

			$defaults = [
				'name' => $name,
			];

			if ( is_string( $field ) ) {
				$name = $field;

				$field = [
					'name' => $name,
				];
			}

			$field = pods_config_merge_data( $defaults, $field );

			$field['name'] = trim( $field['name'] );

			$default_value = pods_v( 'default', $field );
			$value         = pods_v( 'value', $field );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			$to_merge = $this->pod_data->get_field( $field['name'] );

			if ( $to_merge ) {
				$field = pods_config_merge_data( $to_merge, $field );

				// Override the name field as the alias should not be used.
				$field['name'] = $to_merge['name'];
			}

			// Never show the ID field.
			if ( $this->pod_data['field_id'] === $field['name'] ) {
				continue;
			}

			// Hide fields from default form.
			if ( 1 === (int) pods_v( 'hide_in_default_form', $field ) ) {
				continue;
			}

			if ( pods_v( 'hidden', $field, false, true ) ) {
				$field['type'] = 'hidden';
			}

			$fields[ $field['name'] ] = $field;

			if ( empty( $this->id ) && null !== $default_value ) {
				$this->row_override[ $field['name'] ] = $default_value;
			} elseif ( ! empty( $this->id ) && null !== $value ) {
				$this->data->row[ $field['name'] ] = $value;
			}
		}//end foreach

		// Cleanup before get_defined_vars().
		unset( $form_fields );

		$fields = $this->do_hook( 'form_fields', $fields, $params );

		$label = $params['label'];

		if ( empty( $label ) ) {
			$label = __( 'Save Changes', 'pods' );
		}

		$thank_you   = $params['thank_you'];
		$fields_only = $params['fields_only'];
		$output_type = $params['output_type'];

		if ( empty( $output_type ) ) {
			$output_type = 'div';
		}

		PodsForm::$form_counter ++;

		ob_start();

		if ( empty( $thank_you ) ) {
			$success = 'success';

			if ( 1 < PodsForm::$form_counter ) {
				$success .= PodsForm::$form_counter;
			}

			$thank_you = pods_query_arg( array(
				'success*' => null,
				$success   => 1,
			) );

			if ( 1 === (int) pods_v( $success, 'get', 0 ) ) {
				$message = __( 'Form submitted successfully', 'pods' );

				/**
				 * Change the text of the message that appears on successful form submission.
				 *
				 * @param string $message Success message.
				 *
				 * @since 2.7.0
				 */
				$message = apply_filters( 'pods_pod_form_success_message', $message );

				echo '<div id="message" class="pods-form-front-success">' . wp_kses_post( $message ) . '</div>';
			}
		}//end if

		$id = $this->id();

		pods_view( PODS_DIR . 'ui/front/form.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		if ( empty( $this->id ) ) {
			$this->row_override = array();
		}

		return $this->do_hook( 'form', $output, $fields, $label, $thank_you, $this, $this->id() );
	}

	/**
	 * Render a singular view for the Pod item content.
	 *
	 * @param array|string|null $view_fields (optional) Fields to show in the view, defaults to all fields.
	 *
	 * @return mixed
	 * @since 2.3.10
	 */
	public function view( $view_fields = null ) {

		$pod =& $this;

		// Convert comma separated list of fields to an array.
		if ( null !== $view_fields && ! is_array( $view_fields ) && 0 < strlen( $view_fields ) ) {
			$view_fields = explode( ',', $view_fields );
		}

		$all_fields = pods_config_get_all_fields( $this->pod_data );

		// Get all fields if fields are empty.
		if ( empty( $view_fields ) ) {
			$view_fields = $all_fields;
		}

		$fields = [];

		foreach ( $view_fields as $name => $field ) {
			$defaults = [
				'name' => $name,
			];

			$is_field_object = $field instanceof Field;

			if ( ! is_array( $field ) && ! $is_field_object ) {
				$name = $field;

				$field = [
					'name' => $name,
				];
			}

			$field = pods_config_merge_data( $defaults, $fields );

			$field['name'] = trim( $field['name'] );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			$to_merge = $this->pod_data->get_field( $field['name'] );

			if ( $to_merge ) {
				$field = pods_config_merge_data( $to_merge, $field );

				// Override the name field as the alias should not be used.
				$field['name'] = $to_merge['name'];
			}

			if ( pods_v( 'hidden', $field, false, true ) || 'hidden' === $field['type'] ) {
				continue;
			} elseif ( ! pods_permission( $field ) ) {
				continue;
			}

			$fields[ $field['name'] ] = $field;
		}//end foreach

		// Cleanup before get_defined_vars().
		unset( $view_fields );

		$output = pods_view( PODS_DIR . 'ui/front/view.php', compact( array_keys( get_defined_vars() ) ), false, 'cache', true );

		return $this->do_hook( 'view', $output, $fields, $this->id() );

	}

	/**
	 * Replace magic tags with their values
	 *
	 * @param string $code The content to evaluate.
	 *
	 * @return string Code with Magic Tags evaluated
	 *
	 * @since 2.0.0
	 */
	public function do_magic_tags( $code ) {

		/**
		 * Filters the Pods magic tags content before the default function.
		 * Allows complete replacement of the Pods magic tag engine.
		 *
		 * @param null   $pre  Default is null which processes magic tags normally. Return any other value to override.
		 * @param string $code The content to evaluate.
		 * @param Pods   $pods The Pods Object.
		 *
		 * @since 2.7.0
		 */
		$pre = apply_filters( 'pods_pre_do_magic_tags', null, $code, $this );
		if ( null !== $pre ) {
			return $pre;
		}

		return preg_replace_callback( '/({@(.*?)})/m', array( $this, 'process_magic_tags' ), $code );
	}

	/**
	 * Replace magic tags with their values
	 *
	 * @param string|array $tag The magic tag to process.
	 *
	 * @return string Code with Magic Tags evaluated
	 *
	 * @since 2.0.2
	 */
	private function process_magic_tags( $tag ) {

		if ( is_array( $tag ) ) {
			if ( ! isset( $tag[2] ) && '' === trim( $tag[2] ) ) {
				return '';
			}

			$tag = $tag[2];
		}

		$tag = trim( $tag, ' {@}' );
		$tag = explode( ',', $tag );

		if ( empty( $tag ) || ! isset( $tag[0] ) || '' === trim( $tag[0] ) ) {
			return '';
		}

		$tag = pods_trim( $tag );

		$field_name = $tag[0];

		$helper_name = '';
		$before      = '';
		$after       = '';

		if ( ! empty( $tag[1] ) ) {
			$value = $this->field( $field_name );

			$helper_name = $tag[1];

			$value = $this->helper( $helper_name, $value, $field_name );
		} else {
			$value = $this->display( $field_name );
		}

		// Process special magic tags but allow "empty" values for numbers.
		if (
			! $value
			&& ! is_numeric( $value )
			&& pods_shortcode_allow_evaluate_tags()
			&& ! $this->fields( $field_name )
		) {
			// Do not pass before and after tags (key 2 and 3) or these get processed twice.
			$value = pods_evaluate_tag( implode( ',', array_slice( $tag, 0, 2 ) ) );
		}

		if ( ! empty( $tag[2] ) ) {
			$before = $tag[2];
		}

		if ( ! empty( $tag[3] ) ) {
			$after = $tag[3];
		}

		/**
		 * Filter the magic tag output for a value.
		 *
		 * @param string $value      Magic tag output for value.
		 * @param string $field_name Magic tag field name.
		 * @param string $before     Before content.
		 * @param string $after      After content.
		 */
		$value = apply_filters( 'pods_do_magic_tags', $value, $field_name, $helper_name, $before, $after );

		if ( is_array( $value ) ) {
			$value = pods_serial_comma( $value, array(
				'field'  => $field_name,
				'fields' => $this->fields,
			) );
		}

		if ( null !== $value && false !== $value ) {
			return $before . $value . $after;
		}

		return '';
	}

	/**
	 *
	 * Generate UI for Data Management
	 *
	 * @param mixed $options Array or String containing Pod or Options to be used.
	 * @param bool  $amend   Whether to amend the default UI options or replace entirely.
	 *
	 * @return PodsUI|null UI object or null if custom UI used
	 *
	 * @since 2.3.10
	 */
	public function ui( $options = null, $amend = false ) {

		$num = '';

		if ( empty( $options ) ) {
			$options = array();
		} else {
			$num = pods_v_sanitized( 'num', $options, '' );

			if ( empty( $num ) ) {
				$num = '';
			}
		}

		$check_id = pods_v( 'id' . $num, 'get', null, true );

		// @codingStandardsIgnoreLine
		if ( $this->id() != $check_id ) {
			$this->fetch( $check_id );
		}

		if ( ! empty( $options ) && ! $amend ) {
			$this->ui = $options;

			return pods_ui( $this );
		} elseif ( ! empty( $options ) || 'custom' !== pods_v( 'ui_style', $this->pod_data, 'post_type', true ) ) {
			$actions_enabled = pods_v( 'ui_actions_enabled', $this->pod_data );

			if ( ! empty( $actions_enabled ) ) {
				$actions_enabled = (array) $actions_enabled;
			} else {
				$actions_enabled = array();
			}

			$available_actions = array(
				'add',
				'edit',
				'duplicate',
				'delete',
				'reorder',
				'export',
			);

			if ( ! empty( $actions_enabled ) ) {
				$actions_disabled = array(
					'view' => 'view',
				);

				foreach ( $available_actions as $action ) {
					if ( ! in_array( $action, $actions_enabled, true ) ) {
						$actions_disabled[ $action ] = $action;
					}
				}
			} else {
				$actions_disabled = array(
					'duplicate' => 'duplicate',
					'view'      => 'view',
					'export'    => 'export',
				);

				if ( 1 === pods_v( 'ui_export', $this->pod_data, 0 ) ) {
					unset( $actions_disabled['export'] );
				}
			}//end if

			if ( empty( $options ) ) {
				$author_restrict = false;

				if ( isset( $this->fields['author'] ) && 'pick' === $this->fields['author']['type'] && 'user' === $this->fields['author']['pick_object'] ) {
					$author_restrict = 'author.ID';
				}

				if ( ! pods_is_admin( array( 'pods', 'pods_content' ) ) ) {
					if ( ! current_user_can( 'pods_add_' . $this->pod ) ) {
						$actions_disabled['add'] = 'add';

						if ( 'add' === pods_v( 'action' . $num ) ) {
							$_GET[ 'action' . $num ] = 'manage';
						}
					}

					if ( ! $author_restrict && ! current_user_can( 'pods_edit_' . $this->pod ) && ! current_user_can( 'pods_edit_others_' . $this->pod ) ) {
						$actions_disabled['edit'] = 'edit';
					}

					if ( ! $author_restrict && ! current_user_can( 'pods_delete_' . $this->pod ) && ! current_user_can( 'pods_delete_others_' . $this->pod ) ) {
						$actions_disabled['delete'] = 'delete';
					}

					if ( ! current_user_can( 'pods_reorder_' . $this->pod ) ) {
						$actions_disabled['reorder'] = 'reorder';
					}

					if ( ! current_user_can( 'pods_export_' . $this->pod ) ) {
						$actions_disabled['export'] = 'export';
					}
				}//end if
			}//end if

			$_GET[ 'action' . $num ] = pods_v_sanitized( 'action' . $num, 'get', pods_v( 'action', $options, 'manage' ), true );

			$index = $this->pod_data['field_id'];
			$label = __( 'ID', 'pods' );

			if ( isset( $this->pod_data['fields'][ $this->pod_data['field_index'] ] ) ) {
				$index = $this->pod_data['field_index'];
				$label = $this->pod_data['fields'][ $this->pod_data['field_index'] ];
			}

			$manage = array(
				$index => $label,
			);

			if ( isset( $this->pod_data['fields']['modified'] ) ) {
				$manage['modified'] = $this->pod_data['fields']['modified']['label'];
			}

			$manage_fields = (array) pods_v( 'ui_fields_manage', $this->pod_data );

			if ( ! empty( $manage_fields ) ) {
				$manage_new = array();

				foreach ( $manage_fields as $manage_field ) {
					if ( isset( $this->pod_data['fields'][ $manage_field ] ) ) {
						$manage_new[ $manage_field ] = $this->pod_data['fields'][ $manage_field ];
					} elseif ( isset( $this->pod_data['object_fields'][ $manage_field ] ) ) {
						$manage_new[ $manage_field ] = $this->pod_data['object_fields'][ $manage_field ];
					} elseif ( $manage_field === $this->pod_data['field_id'] ) {
						$field = array(
							'name'  => $manage_field,
							'label' => 'ID',
							'type'  => 'number',
							'width' => '8%',
						);

						$manage_new[ $manage_field ] = PodsForm::field_setup( $field, null, $field['type'] );
					}
				}

				if ( ! empty( $manage_new ) ) {
					$manage = $manage_new;
				}
			}//end if

			$pod_name = $this->pod;
			$manage   = apply_filters( "pods_admin_ui_fields_{$pod_name}", apply_filters( 'pods_admin_ui_fields', $manage, $this->pod, $this ), $this->pod, $this );

			$icon = pods_v( 'ui_icon', $this->pod_data );

			if ( ! empty( $icon ) ) {
				$icon = pods_image_url( $icon, '32x32' );
			}

			$filters = pods_v( 'ui_filters', $this->pod_data );

			if ( ! empty( $filters ) ) {
				$filters_new = array();

				$filters = (array) $filters;

				foreach ( $filters as $filter_field ) {
					if ( isset( $this->pod_data['fields'][ $filter_field ] ) ) {
						$filters_new[ $filter_field ] = $this->pod_data['fields'][ $filter_field ];
					} elseif ( isset( $this->pod_data['object_fields'][ $filter_field ] ) ) {
						$filters_new[ $filter_field ] = $this->pod_data['object_fields'][ $filter_field ];
					}
				}

				$filters = $filters_new;
			}

			$ui = array(
				'fields'           => array(
					'manage'    => $manage,
					'add'       => $this->pod_data['fields'],
					'edit'      => $this->pod_data['fields'],
					'duplicate' => $this->pod_data['fields'],
				),
				'icon'             => $icon,
				'actions_disabled' => $actions_disabled,
				'actions_bulk'     => array(),
			);

			if ( ! empty( $filters ) ) {
				$ui['fields']['search'] = $filters;
				$ui['filters']          = array_keys( $filters );
				$ui['filters_enhanced'] = true;
			}

			$reorder_field = pods_v( 'ui_reorder_field', $this->pod_data );

			if ( ! empty( $reorder_field ) && in_array( 'reorder', $actions_enabled, true ) && ! in_array( 'reorder', $actions_disabled, true ) && ( ( ! empty( $this->pod_data['object_fields'] ) && isset( $this->pod_data['object_fields'][ $reorder_field ] ) ) || isset( $this->pod_data['fields'][ $reorder_field ] ) ) ) {
				$ui['reorder']     = array( 'on' => $reorder_field );
				$ui['orderby']     = $reorder_field;
				$ui['orderby_dir'] = 'ASC';
			}

			if ( ! empty( $author_restrict ) ) {
				$ui['restrict'] = array( 'author_restrict' => $author_restrict );
			}

			if ( ! in_array( 'export', $ui['actions_disabled'], true ) ) {
				$ui['actions_bulk']['export'] = array(
					'label' => __( 'Export', 'pods' ),
					// callback not needed, Pods has this built-in for export.
				);
			}

			if ( ! in_array( 'delete', $ui['actions_disabled'], true ) ) {
				$ui['actions_bulk']['delete'] = array(
					'label' => __( 'Delete', 'pods' ),
					// callback not needed, Pods has this built-in for delete.
				);
			}

			$detail_url = pods_v( 'detail_url', $this->pod_data );

			if ( 0 < strlen( $detail_url ) ) {
				$ui['actions_custom'] = array(
					'view_url' => array(
						'label' => 'View',
						'link'  => get_site_url() . '/' . $detail_url,
					),
				);
			}

			// @todo Customize the Add New / Manage links to point to their correct menu items.
			$pod_name = $this->pod;
			$ui       = apply_filters( "pods_admin_ui_{$pod_name}", apply_filters( 'pods_admin_ui', $ui, $this->pod, $this ), $this->pod, $this );

			// Override UI options.
			foreach ( $options as $option => $value ) {
				$ui[ $option ] = $value;
			}

			$this->ui = $ui;

			return pods_ui( $this );
		}//end if

		$pod_name = $this->pod;
		do_action( 'pods_admin_ui_custom', $this );
		do_action( "pods_admin_ui_custom_{$pod_name}", $this );

		return null;
	}

	/**
	 * Handle filters / actions for the class
	 *
	 * @see   pods_do_hook
	 *
	 * @param string $name Hook name.
	 *
	 * @return mixed Value filtered
	 *
	 * @since 2.0.0
	 */
	private function do_hook( $name ) {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		// Remove first argument.
		array_shift( $args );

		return pods_do_hook( 'pods', $name, $args, $this );
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function __get( $name ) {
		$name = (string) $name;

		// Handle PodsData properties.
		$supported_pods_data = array(
			'rows',
			'row',
			'pagination',
			'page',
			'page_var',
			'search',
			'search_var',
			'search_mode',
			'api',
			'row_number',
			'params',
			'filters',
			'id',
			'limit',
			'offset',
			'total',
			'total_found',
			'sql',
		);

		if ( in_array( $name, $supported_pods_data, true ) ) {
			return $this->data->{$name};
		}

		// Handle alias Pods\Whatsit\Pod properties.
		$supported_pods_object = array(
			'pod'         => 'name',
			'pod_id'      => 'id',
			'fields'      => 'fields',
			'detail_page' => 'detail_url',
			'detail_url'  => 'detail_url',
		);

		if ( isset( $supported_pods_object[ $name ] ) ) {
			if ( ! is_object( $this->pod_data ) ) {
				return null;
			}

			return pods_v( $supported_pods_object[ $name ], $this->pod_data );
		}

		// Handle sending previously mapped PodsData properties directly to their correct place.
		if ( 0 === strpos( $name, 'field_' ) ) {
			if ( ! is_object( $this->pod_data ) ) {
				return null;
			}

			return pods_v( $name, $this->pod_data );
		}

		return null;
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 *
	 * @since 2.8.0
	 */
	public function __set( $name, $value ) {
		$name = (string) $name;

		$supported_pods_data = array(
			'rows'        => 'array',
			'row'         => 'array',
			'pagination'  => 'boolean',
			'page'        => 'int',
			'page_var'    => 'string',
			'search'      => 'boolean',
			'search_var'  => 'string',
			'search_mode' => 'string',
			'id'          => 'int',
		);

		if ( isset( $supported_pods_data[ $name ] ) ) {
			// Cast the value.
			settype( $value, $supported_pods_data[ $name ] );

			$this->data->{$name} = $value;
		}
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name Property name.
	 *
	 * @return bool Whether the variable is set or not.
	 *
	 * @since 2.8.0
	 */
	public function __isset( $name ) {
		$value = $this->__get( $name );

		return ( null !== $value );
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name Property name.
	 *
	 * @since 2.8.0
	 */
	public function __unset( $name ) {
		$this->__set( $name, null );
	}

	/**
	 * Handle methods that have been deprecated and any aliasing.
	 *
	 * @param string $name Function name.
	 * @param array  $args Arguments passed to function.
	 *
	 * @return mixed|null
	 *
	 * @since 2.0.0
	 */
	public function __call( $name, $args ) {

		$name = (string) $name;

		// select > find alias.
		if ( 'select' === $name ) {
			return call_user_func_array( array( $this, 'find' ), $args );
		}

		if ( ! $this->deprecated ) {
			require_once PODS_DIR . 'deprecated/classes/Pods.php';

			$this->deprecated = new Pods_Deprecated( $this );
		}

		$pod_class_exists = class_exists( 'Deprecated_Pod' );

		if ( method_exists( $this->deprecated, $name ) ) {
			return call_user_func_array( array( $this->deprecated, $name ), $args );
			// @codingStandardsIgnoreLine
		} elseif ( ! $pod_class_exists || Deprecated_Pod::$deprecated_notice ) {
			pods_deprecated( "Pods::{$name}", '2.0' );
		}

		return null;
	}

	/**
	 * Handle casting a Pods() object to string
	 *
	 * @return string Pod type and name in CURIE notation
	 */
	public function __toString() {

		$string = '';

		if ( ! empty( $this->pod_data ) ) {
			$string = sprintf( '%s:%s', $this->pod_data['type'], $this->pod_data['name'] );
		}

		return $string;

	}
}
