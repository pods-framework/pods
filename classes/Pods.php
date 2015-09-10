<?php
/**
 * @package Pods
 * @category Display
 */
class Pods implements Iterator {

	/**
	 * @var bool
	 */
	private $iterator = false;

	/**
	 * @var Pods_Data
	 */
	public $data;

	/**
	 * @var Pods_Data
	 */
	public $alt_data;

	/**
	 * @var array Array of pod item arrays
	 */
	public $rows = array();

	/**
	 * @var array Current pod item array
	 */
	public $row = array();

	/**
	 * @var int
	 */
	private $row_number = -1;

	/**
	 * @var array Override pod item array
	 */
	public $row_override = array();

	/**
	 * @var bool
	 */
	public $display_errors = true;

	/**
	 * @var array|bool|mixed|null|void
	 */
	public $pod_data;

	/**
	 * @var array
	 */
	public $params = array();

	/**
	 * @var string
	 */
	public $pod = '';

	/**
	 * @var int
	 */
	public $pod_id = 0;

	/**
	 * @var array
	 */
	public $fields = array();

	/**
	 * @var array
	 */
	public $filters = array();

	/**
	 * @var string
	 */
	public $detail_page;

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $limit = 15;

	/**
	 * @var int
	 */
	public $offset = 0;

	/**
	 * @var string
	 */
	public $page_var = 'pg';

	/**
	 * @var int|mixed
	 */
	public $page = 1;

	/**
	 * @var bool
	 */
	public $pagination = true;

	/**
	 * @var bool
	 */
	public $search = true;

	/**
	 * @var string
	 */
	public $search_var = 'search';

	/**
	 * @var string
	 */
	public $search_mode = 'int'; // int | text | text_like

	/**
	 * @var int
	 */
	public $total = 0;

	/**
	 * @var int
	 */
	public $total_found = 0;

	/**
	 * @var array
	 */
	public $ui = array();

	/**
	 * @var string|null SEO related vars for Pod Pages
	 */
	public $page_template;

	/**
	 * @var array|null Body classes to use for Pod Pages
	 */
	public $body_classes;

	/**
	 * @var array Array of meta name=>content for <head>
	 */
	public $meta = array();

	/**
	 * @var array Array of meta property name=>content for <head>
	 */
	public $meta_properties = array();

	/**
	 * @var string Extra meta string to add to <head>
	 */
	public $meta_extra = '';

	/**
	 * @var string Last SQL query used by a find()
	 */
	public $sql;

	/**
	 * @var bool Whether in deprecated mode or not
	 */
	public $deprecated;

	/**
	 * @deprecated
	 * @var null|string Pod name
	 */
	public $datatype;

	/**
	 * @deprecated
	 * @var null|int Pod ID
	 */
	public $datatype_id;

	/**
	 * Constructor - Pods Framework core
	 *
	 * @param string $pod The pod name
	 * @param mixed  $id  (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
	 *
	 * @return \Pods
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since   1.0.0
	 * @link    http://pods.io/docs/pods/
	 */
	public function __construct( $pod = null, $id = null ) {

		if ( null === $pod ) {
			$queried_object = get_queried_object();

			// WP Query on singular pages
			if ( $queried_object ) {
				$id_lookup = true;

				// Post Type Singular
				if ( isset( $queried_object->post_type ) ) {
					$pod = $queried_object->post_type;
				}
				// Term Archive
				elseif ( isset( $queried_object->taxonomy ) ) {
					$pod = $queried_object->taxonomy;
				}
				// Author Archive
				elseif ( isset( $queried_object->user_login ) ) {
					$pod = 'user';
				}
				// Post Type Archive
				elseif ( isset( $queried_object->public ) && isset( $queried_object->name ) ) {
					$pod = $queried_object->name;

					$id_lookup = false;
				}
				// Pods global
				elseif ( ! empty( $GLOBALS['pods'] ) ) {
					$pod = $GLOBALS['pods']->pod;

					if ( null === $id ) {
						$id = $GLOBALS['pods']->id;
					}
				}

				// Get queried object ID
				if ( null !== $pod && null === $id && $id_lookup ) {
					$id = get_queried_object_id();
				}
			}
			// Pods global
			elseif ( ! empty( $GLOBALS['pods'] ) ) {
				$pod = $GLOBALS['pods']->pod;

				if ( null === $id ) {
					$id = $GLOBALS['pods']->id;
				}
			}
		}

		$this->data = pods_data( $pod, $id, false );

		Pods_Data::$display_errors =& $this->display_errors;

		// Set up page variable
		if ( pods_strict( false ) || 0 === strpos( $pod, '_pods_' ) ) {
			$this->page       = 1;
			$this->pagination = false;
			$this->search     = false;
		} else {
			// Get the page variable
			$this->page = pods_v( $this->page_var );
			$this->page = ( empty( $this->page ) ? 1 : max( pods_absint( $this->page ), 1 ) );

			// Set default pagination handling to on/off
			if ( defined( 'PODS_GLOBAL_POD_PAGINATION' ) ) {
				if ( ! PODS_GLOBAL_POD_PAGINATION ) {
					$this->page       = 1;
					$this->pagination = false;
				} else {
					$this->pagination = true;
				}
			}

			// Set default search to on/off
			if ( defined( 'PODS_GLOBAL_POD_SEARCH' ) ) {
				if ( PODS_GLOBAL_POD_SEARCH ) {
					$this->search = true;
				} else {
					$this->search = false;
				}
			}
		}

		// Set default search mode
		$allowed_search_modes = array( 'int', 'text', 'text_like' );

		if ( defined( 'PODS_GLOBAL_POD_SEARCH_MODE' ) && in_array( PODS_GLOBAL_POD_SEARCH_MODE, $allowed_search_modes ) ) {
			$this->search_mode = PODS_GLOBAL_POD_SEARCH_MODE;
		}

		// Sync Settings
		$this->data->page        =& $this->page;
		$this->data->limit       =& $this->limit;
		$this->data->pagination  =& $this->pagination;
		$this->data->search      =& $this->search;
		$this->data->search_mode =& $this->search_mode;

		// Sync Pod Data
		$this->pod_data      =& $this->data->pod_data;
		$this->pod           =& $this->data->pod;
		$this->pod_id        =& $this->data->pod_id;
		$this->fields        =& $this->data->fields;
		$this->detail_page   =& $this->data->detail_page;
		$this->id            =& $this->data->id;
		$this->row           =& $this->data->row;
		$this->rows          =& $this->data->data;
		$this->row_number    =& $this->data->row_number;
		$this->sql           =& $this->data->sql;

		// Backwards compatibility
		$this->datatype_id   =& $this->pod_id;
		$this->datatype      =& $this->pod;

		// pods( 'pod_name', array( ... ) ) for find() shorthand syntax
		if ( ! empty( $this->pod_data ) && ( is_array( $id ) || is_object( $id ) ) ) {
			$this->find( $id );
		}

	}

	/**
	 * Whether this Pod object is valid or not
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function valid() {

		if ( empty( $this->pod_id ) && empty( $this->pod_data ) ) {
			return false;
		}

		if ( $this->iterator ) {
			return isset( $this->rows[ $this->row_number ] );
		}

		return true;

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

		return;

	}

	/**
	 * Rewind Iterator
	 *
	 * @return void|boolean
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function rewind() {

		if ( $this->iterator ) {
			$this->row_number = 0;

			return null;
		}

		return false;

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
	 * @return int|boolean
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function key() {

		if ( $this->iterator ) {
			return $this->row_number;
		}

		return false;

	}

	/**
	 * Move onto the next Iterator row
	 *
	 * @return void|boolean
	 *
	 * @since 2.3.4
	 *
	 * @link  http://www.php.net/manual/en/class.iterator.php
	 */
	public function next() {

		if ( $this->iterator ) {
			$this->row_number++;

			return null;
		}

		return false;

	}

	/**
	 * Whether a Pod item exists or not when using fetch() or construct with an ID or slug
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function exists() {

		if ( empty( $this->row ) ) {
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
	 * @since 2.0
	 * @link  http://pods.io/docs/data/
	 */
	public function data() {

		// @todo Needs hook doc
		do_action( 'pods_pods_data', $this );

		if ( empty( $this->rows ) ) {
			return false;
		}

		return (array) $this->rows;

	}

	/**
	 * Return a field input for a specific field
	 *
	 * @param string|array $field      Field name or Field data array
	 * @param string       $input_name Input field name to use (overrides default name)
	 * @param mixed        $value      Current value to use
	 *
	 * @return string Field Input HTML
	 *
	 * @since 2.3.10
	 */
	public function input( $field, $input_name = null, $value = '__null' ) {

		// Field data override
		if ( is_array( $field ) || is_object( $field ) ) {
			$field_data = $field;
			$field      = pods_v( 'name', $field );
		} // Get field data from field name
		else {
			$field_data = $this->fields( $field );
		}

		if ( ! empty( $field_data ) ) {
			$field_type = $field_data['type'];

			if ( empty( $input_name ) ) {
				$input_name = $field;
			}

			if ( '__null' == $value ) {
				$value = $this->field( array( 'name' => $field, 'in_form' => true ) );
			}

			return Pods_Form::field( $input_name, $value, $field_type, $field_data, $this, $this->id() );
		}

		return '';

	}

	/**
	 * Return field array from a Pod, a field's data, or a field option
	 *
	 * @param null $field
	 * @param null $option
	 *
	 * @return bool|mixed
	 *
	 * @since 2.0
	 */
	public function fields( $field = null, $option = null ) {

		// No fields found
		if ( empty( $this->fields ) ) {
			$field_data = array();
		} // Return all fields
		elseif ( empty( $field ) ) {
			$field_data = (array) $this->fields;
		} // Field not found
		elseif ( ! isset( $this->fields[ $field ] ) ) {
			$field_data = array();
		} // Return all field data
		elseif ( empty( $option ) ) {
			$field_data = $this->fields[ $field ];
		} else {
			$field_data = null;

			// Get a list of available items from a relationship field
			if ( 'data' == $option && in_array( $this->fields[ $field ]['type'], Pods_Form::tableless_field_types() ) ) {
				$field_data = Pods_Form::field_method( 'pick', 'get_field_data', $this->fields[ $field ] );
			} // Return option
			elseif ( isset( $this->fields[ $field ][ $option ] ) ) {
				$field_data = $this->fields[ $field ][ $option ];
			}
		}

		/**
		 * Modify the field data before returning
		 *
		 * @since unknown
		 *
		 * @param array $field_data The data for the field.
		 * @param string|null $field The specific field that data is being return for, if set when method is called or null.
		 * @param string|null $option Value of option param when method was called. Can be used to get a list of available items from a relationship field.
		 * @param Pods|object $this The current Pods class instance.
		 */
		return apply_filters( 'pods_pods_fields', $field_data, $field, $option, $this );

	}

	/**
	 * Return row array for an item
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function row() {

		// @todo Needs hook doc
		do_action( 'pods_pods_row', $this );

		if ( ! is_array( $this->row ) ) {
			return false;
		}

		return (array) $this->row;

	}

	/**
	 * Return the output for a field. If you want the raw value for use in PHP for custom manipulation,
	 * you will want to use field() instead. This function will automatically convert arrays into a
	 * list of text such as "Rick, John, and Gary"
	 *
	 * @param string|array $name   The field name, or an associative array of parameters
	 * @param boolean      $single (optional) For tableless fields, to return an array or the first
	 *
	 * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned for tableless fields
	 * @since 2.0
	 * @link  http://pods.io/docs/display/
	 */
	public function display( $name, $single = null ) {

		$defaults = array(
			'name'          => $name,
			'single'        => $single,
			'display'       => true,
			'serial_params' => null
		);

		if ( is_array( $name ) || is_object( $name ) ) {
			$defaults['name'] = null;
			$params           = (object) array_merge( $defaults, (array) $name );
		} elseif ( is_array( $single ) || is_object( $single ) ) {
			$defaults['single'] = null;
			$params             = (object) array_merge( $defaults, (array) $single );
		} else {
			$params = $defaults;
		}

		$params = (object) $params;

		$value = $this->field( $params );

		if ( is_array( $value ) ) {
			$fields = $this->fields;

			if ( isset( $this->pod_data[ 'object_fields' ] ) ) {
				$fields = array_merge( $fields, $this->pod_data[ 'object_fields' ] );
			}

			$serial_params = array(
				'field' => $params->name,
				'fields' => $fields
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
	 * @param string|array $name   The field name, or an associative array of parameters
	 * @param boolean      $single (optional) For tableless fields, to return an array or the first
	 *
	 * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned for tableless fields
	 * @since 2.0
	 * @link  http://pods.io/docs/display/
	 */
	public function raw( $name, $single = null ) {

		$defaults = array(
			'name'   => $name,
			'single' => $single,
			'raw'    => true
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

		$value = $this->field( $params );

		return $value;

	}

	/**
	 * Return the value for a field.
	 *
	 * If you are getting a field for output in a theme, most of the time you will want to use display() instead.
	 *
	 * This function will return arrays for relationship and file fields.
	 *
	 * @param string|array $name   The field name, or an associative array of parameters
	 * @param boolean      $single (optional) For tableless fields, to return the whole array or the just the first item, or an associative array of parameters
	 * @param boolean      $raw    (optional) Whether to return the raw value, or to run through the field type's display method, or an associative array of parameters
	 *
	 * @return mixed|null Value returned depends on the field type, null if the field doesn't exist, false if no value returned for tableless fields
	 * @since 2.0
	 * @link  http://pods.io/docs/field/
	 */
	public function field( $name, $single = null, $raw = false ) {

		global $sitepress;

		static $api = null;

		$defaults = array(
			'name'        => $name,
			'orderby'     => null,
			'single'      => $single,
			'params'      => null,
			'in_form'     => false,
			'raw'         => $raw,
			'raw_display' => false,
			'display'     => false,
			'get_meta'    => false,
			'output'      => null,
			'deprecated'  => false,
			'args'        => array() // extra data to send to field handlers
		);

		if ( is_array( $name ) || is_object( $name ) ) {
			$defaults['name'] = null;

			$params = (object) array_merge( $defaults, (array) $name );
		} elseif ( is_array( $single ) || is_object( $single ) ) {
			$defaults['single'] = null;

			$params = (object) array_merge( $defaults, (array) $single );
		} elseif ( is_array( $raw ) || is_object( $raw ) ) {
			$defaults['raw'] = false;

			$params = (object) array_merge( $defaults, (array) $raw );
		} else {
			$params = (object) $defaults;
		}

		if ( $params->in_form ) {
			$params->output = 'ids';
		} elseif ( null === $params->output ) {
			/**
			 * Override the way realted fields are output
			 *
			 * @param string $output How to output related fields. Default is 'arrays'. Options: id|name|object|array|pod
			 * @param array|object $row Current row being outputted.
			 * @param array $params Params array passed to field().
			 * @param object|Pods   $this Current Pods object.
			 */
			$params->output = apply_filters( 'pods_pods_field_related_output_type', 'arrays', $this->row, $params, $this );
		}

		if ( in_array( $params->output, array( 'id', 'name', 'object', 'array', 'pod' ) ) ) {
			$params->output .= 's';
		}

		// Support old $orderby variable
		if ( null !== $params->single && is_string( $params->single ) && empty( $params->orderby ) ) {
			if ( ! class_exists( 'Pod' ) || Pod::$deprecated_notice ) {
				pods_deprecated( 'Pods::field', '2.0', 'Use $params[ \'orderby\' ] instead' );
			}

			$params->orderby = $params->single;
			$params->single  = false;
		}

		if ( null !== $params->single ) {
			$params->single = (boolean) $params->single;
		}

		if ( is_array( $params->name ) ) {
			return null;
		}

		$params->name = trim( $params->name );

		if ( strlen( $params->name ) < 1 ) {
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

		if ( $this->data->field_id == $params->name ) {
			if ( isset( $this->row[ $params->name ] ) ) {
				return $this->row[ $params->name ];
			} elseif ( null !== $value ) {
				return $value;
			}

			return 0;
		}

		$id = $this->id();

		$tableless_field_types    = Pods_Form::tableless_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		$params->traverse = array();

		if ( in_array( $params->name, array( '_link', 'detail_url' ) ) || ( in_array( $params->name, array( 'permalink', 'the_permalink' ) ) && in_array( $this->pod_data['type'], array( 'post_type', 'media' ) ) ) ) {
			if ( 0 < strlen( $this->detail_page ) ) {
				$value = get_home_url() . '/' . $this->do_magic_tags( $this->detail_page );
			} elseif ( in_array( $this->pod_data['type'], array( 'post_type', 'media' ) ) ) {
				$value = get_permalink( $id );
			} elseif ( 'taxonomy' == $this->pod_data['type'] ) {
				$value = get_term_link( $id, $this->pod_data['name'] );
			} elseif ( 'user' == $this->pod_data['type'] ) {
				$value = get_author_posts_url( $id );
			} elseif ( 'comment' == $this->pod_data['type'] ) {
				$value = get_comment_link( $id );
			}
		}

		$field_data = false;
		$last_field_data = false;
		$field_type = false;

		$first_field = explode( '.', $params->name );
		$first_field = $first_field[0];

		if ( isset( $this->fields[ $first_field ] ) ) {
			$field_data = $this->fields[ $first_field ];
			$field_type = 'field';
		} elseif ( ! empty( $this->pod_data['object_fields'] ) ) {
			if ( ! empty( $this->pod_data['object_fields'][ $first_field ] ) ) {
				$field_data = $this->pod_data['object_fields'][ $first_field ];
				$field_type = 'object_field';
			} else {
				foreach ( $this->pod_data['object_fields'] as $object_field => $object_field_opt ) {
					if ( in_array( $first_field, $object_field_opt['alias'] ) ) {
						if ( $first_field == $params->name ) {
							$params->name = $object_field;
						}

						$first_field = $object_field;
						$field_data  = $object_field_opt;
						$field_type  = 'object_field';

						break;
					}
				}
			}
		}

		// Simple fields have no other output options
		if ( 'pick' == $field_data['type'] && ( in_array( $field_data['pick_object'], $simple_tableless_objects ) || 1 == $field_data['pick_simple'] ) ) {
			$params->output = 'arrays';
		}

		if ( empty( $value ) && in_array( $field_data['type'], $tableless_field_types ) ) {
			$params->raw = true;

			$value = false;

			if ( 'arrays' != $params->output && isset( $this->row[ '_' . $params->output . '_' . $params->name ] ) ) {
				$value = $this->row[ '_' . $params->output . '_' . $params->name ];
			} elseif ( 'arrays' == $params->output && isset( $this->row[ $params->name ] ) ) {
				$value = $this->row[ $params->name ];
			}

			if ( false !== $value && ! is_array( $value ) && 'pick' == $field_data['type'] && ( in_array( $field_data['pick_object'], $simple_tableless_objects ) || 1 == $field_data['pick_simple'] ) ) {
				$value = Pods_Form::field_method( 'pick', 'simple_value', $params->name, $value, $field_data, $this->pod_data, $id, true );
			}
		}

		if ( empty( $value ) && isset( $this->row[ $params->name ] ) && ( ! in_array( $field_data['type'], $tableless_field_types ) || 'arrays' == $params->output ) ) {
			if ( empty( $field_data ) || in_array( $field_data['type'], array( 'boolean', 'number', 'currency' ) ) ) {
				$params->raw = true;
			}

			if ( null === $params->single ) {
				if ( ! empty( $this->fields[ $params->name ] ) && !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) ) {
					$params->single = true;
				}
				else {
					$params->single = false;
				}
			}

			$value = $this->row[ $params->name ];
		} elseif ( empty( $value ) ) {
			$object_field_found = false;

			if ( 'object_field' == $field_type ) {
				$object_field_found = true;

				if ( isset( $this->row[ $first_field ] ) ) {
					$value = $this->row[ $first_field ];
				} elseif ( in_array( $field_data['type'], $tableless_field_types ) ) {
					$this->fields[ $first_field ] = $field_data;

					$object_field_found = false;
				} else {
					return null;
				}
			}

			if ( 'post_type' == $this->pod_data['type'] && ! isset( $this->fields[ $params->name ] ) ) {
				if ( ! isset( $this->fields['post_thumbnail'] ) && ( 'post_thumbnail' == $params->name || 0 === strpos( $params->name, 'post_thumbnail.' ) ) ) {
					$size = 'thumbnail';

					if ( 0 === strpos( $params->name, 'post_thumbnail.' ) ) {
						$field_names = explode( '.', $params->name );

						if ( isset( $field_names[1] ) ) {
							$size = $field_names[1];
						}
					}

					// Pods will auto-get the thumbnail ID if this isn't an attachment
					// @todo Verify we need to force generation here
					$value = pods_image( $id, $size, 0, null, true );

					$object_field_found = true;
				} elseif ( empty( $this->fields['post_thumbnail_url'] ) && ( 'post_thumbnail_url' == $params->name || 0 === strpos( $params->name, 'post_thumbnail_url.' ) ) ) {
					$size = 'thumbnail';

					if ( 0 === strpos( $params->name, 'post_thumbnail_url.' ) ) {
						$field_names = explode( '.', $params->name );

						if ( isset( $field_names[1] ) ) {
							$size = $field_names[1];
						}
					}

					// Pods will auto-get the thumbnail ID if this isn't an attachment
					// @todo Verify we need to force generation here
					$value = pods_image_url( $id, $size, 0, true );

					$object_field_found = true;
				} elseif ( 0 === strpos( $params->name, 'image_attachment.' ) ) {
					$size = 'thumbnail';

					$image_id = 0;

					$field_names = explode( '.', $params->name );

					if ( isset( $field_names[ 1 ] ) ) {
						$image_id = $field_names[ 1 ];
					}

					if ( isset( $field_names[ 2 ] ) ) {
						$size = $field_names[ 2 ];
					}

					if ( ! empty( $image_id ) ) {
						$value = pods_image( $image_id, $size, 0, null, true );

						if ( ! empty( $value ) ) {
							$object_field_found = true;
						}
					}
				} elseif ( 0 === strpos( $params->name, 'image_attachment_url.' ) ) {
					$size = 'thumbnail';

					$image_id = 0;

					$field_names = explode( '.', $params->name );

					if ( isset( $field_names[ 1 ] ) ) {
						$image_id = $field_names[ 1 ];
					}

					if ( isset( $field_names[ 2 ] ) ) {
						$size = $field_names[ 2 ];
					}

					if ( ! empty( $image_id ) ) {
						$value = pods_image_url( $image_id, $size, 0, true );

						if ( ! empty( $value ) ) {
							$object_field_found = true;
						}
					}
				}
			} elseif ( 'user' == $this->pod_data[ 'type' ] && ! isset( $this->fields[ $params->name ] ) ) {
				if ( ! isset( $this->fields[ 'avatar' ] ) && ( 'avatar' == $params->name || 0 === strpos( $params->name, 'avatar.' ) ) ) {
					$size = null;

					if ( 0 === strpos( $params->name, 'avatar.' ) ) {
						$field_names = explode( '.', $params->name );

						if ( isset( $field_names[1] ) ) {
							$size = (int) $field_names[1];
						}
					}

					if ( ! empty( $size ) ) {
						$value = get_avatar( $id, $size );
					} else {
						$value = get_avatar( $id );
					}

					$object_field_found = true;
				}
			} elseif ( 0 === strpos( $params->name, 'image_attachment.' ) ) {
				$size = 'thumbnail';

				$image_id = 0;

				$field_names = explode( '.', $params->name );

				if ( isset( $field_names[1] ) ) {
					$image_id = $field_names[1];
				}

				if ( isset( $field_names[2] ) ) {
					$size = $field_names[2];
				}

				if ( ! empty( $image_id ) ) {
					// @todo Verify we need to force generation here
					$value = pods_image( $image_id, $size, 0, null, true );

					if ( ! empty( $value ) ) {
						$object_field_found = true;
					}
				}
			} elseif ( 0 === strpos( $params->name, 'image_attachment_url.' ) ) {
				$size = 'thumbnail';

				$image_id = 0;

				$field_names = explode( '.', $params->name );

				if ( isset( $field_names[1] ) ) {
					$image_id = $field_names[1];
				}

				if ( isset( $field_names[2] ) ) {
					$size = $field_names[2];
				}

				if ( ! empty( $image_id ) ) {
					// @todo Verify we need to force generation here
					$value = pods_image_url( $image_id, $size, 0, true );

					if ( ! empty( $value ) ) {
						$object_field_found = true;
					}
				}
			}

			if ( false === $object_field_found ) {
				$params->traverse = array( $params->name );

				if ( false !== strpos( $params->name, '.' ) ) {
					$params->traverse = explode( '.', $params->name );

					$params->name = $params->traverse[0];
				}

				if ( isset( $this->fields[ $params->name ] ) && isset( $this->fields[ $params->name ][ 'type' ] ) ) {
					/**
					 * Modify value returned by field() after its retrieved, but before its validated or formatted
					 *
					 * Filter name is set dynamically with name of field: "pods_pods_field_{field_name}"
					 *
					 * @since unknown
					 *
					 * @param array|string|null $value Value retrieved.
					 * @param array|object $row Current row being outputted.
					 * @param array $params Params array passed to field().
					 * @param object|Pods $this Current Pods object.
					 *
					 */
					$v = apply_filters( 'pods_pods_field_' . $this->fields[ $params->name ][ 'type' ], null, $this->fields[ $params->name ], $this->row, $params, $this );

					if ( null !== $v ) {
						return $v;
					}
				}

				$simple      = false;
				$simple_data = array();

				if ( isset( $this->fields[$params->name] ) ) {
					if ( 'meta' == $this->pod_data['storage'] ) {
						if ( ! in_array( $this->fields[$params->name]['type'], $tableless_field_types ) ) {
							$simple = true;
						}
					}

					if ( in_array( $this->fields[$params->name]['type'], $tableless_field_types ) ) {
						$params->raw = true;

						if ( 'pick' == $this->fields[$params->name]['type'] && ( in_array( $this->fields[$params->name]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$params->name]['pick_simple'] ) ) {
							$simple         = true;
							$params->single = true;
						}
					} elseif ( in_array( $this->fields[$params->name]['type'], array( 'boolean', 'number', 'currency' ) ) ) {
						$params->raw = true;
					}
				}

				if ( ! isset( $this->fields[$params->name] ) || ! in_array( $this->fields[$params->name]['type'], $tableless_field_types ) || $simple ) {
					if ( null === $params->single ) {
						if ( isset( $this->fields[$params->name] ) && ! in_array( $this->fields[$params->name]['type'], $tableless_field_types ) ) {
							$params->single = true;
						} else {
							$params->single = false;
						}
					}

					$no_conflict = pods_no_conflict_check( $this->pod_data['type'] );

					if ( ! $no_conflict ) {
						pods_no_conflict_on( $this->pod_data['type'] );
					}

					if ( in_array( $this->pod_data['type'], array( 'post_type', 'media' ) ) ) {
						// Support for WPML 'duplicated' translation handling
						if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $this->pod_data['name'] ) ) {
							$master_post_id = (int) get_post_meta( $id, '_icl_lang_duplicate_of', true );

							if ( 0 < $master_post_id ) {
								$id = $master_post_id;
							}
						}

						$value = get_post_meta( $id, $params->name, $params->single );

						$single_multi = 'single';

						if ( isset( $this->fields[ $params->name ] ) ) {
							$single_multi = pods_v( $this->fields[ $params->name ][ 'type' ] . '_format_type', $this->fields[ $params->name ], 'single' );
						}

						if ( $simple && !is_array( $value ) && 'single' != $single_multi ) {
							$value = get_post_meta( $id, $params->name );
						}
					} elseif ( in_array( $this->pod_data['type'], array( 'user', 'comment' ) ) ) {
						$value = get_metadata( $this->pod_data['type'], $id, $params->name, $params->single );

						$single_multi = 'single';

						if ( isset( $this->fields[ $params->name ] ) ) {
							$single_multi = pods_v( $this->fields[ $params->name ][ 'type' ] . '_format_type', $this->fields[ $params->name ], 'single' );
						}

						if ( $simple && ! is_array( $value ) && 'single' != $single_multi ) {
							$value = get_metadata( $this->pod_data['type'], $id, $params->name );
						}
					} elseif ( 'settings' == $this->pod_data['type'] ) {
						$value = get_option( $this->pod_data['name'] . '_' . $params->name, null );
					}

					// Handle Simple Relationships
					if ( $simple ) {
						if ( null === $params->single ) {
							$params->single = false;
						}

						$value = Pods_Form::field_method( 'pick', 'simple_value', $params->name, $value, $this->fields[$params->name], $this->pod_data, $id, true );
					}

					if ( ! $no_conflict ) {
						pods_no_conflict_off( $this->pod_data['type'] );
					}
				} else {
					// Dot-traversal
					$pod        = $this->pod;
					$ids        = array( $id );
					$all_fields = array();

					$lookup = $params->traverse;

					// Get fields matching traversal names
					if ( ! empty( $lookup ) ) {
						if ( ! $api ) {
							$api = pods_api();

							$api->display_errors =& $this->display_errors;
						}

						$fields = $api->load_fields( array(
							'name'          => $lookup,
							'type'          => $tableless_field_types,
							'object_fields' => true,
						    'output'        => OBJECT,
							// @todo support object fields too
						) );

						if ( ! empty( $fields ) ) {
							foreach ( $fields as $field ) {
								if ( ! empty( $field ) ) {
									if ( ! isset( $all_fields[$field['pod']] ) ) {
										$all_fields[$field['pod']] = array();
									}

									$all_fields[$field['pod']][$field['name']] = $field;
								}
							}
						}

						if ( ! empty( $this->pod_data['object_fields'] ) ) {
							foreach ( $this->pod_data['object_fields'] as $object_field => $object_field_opt ) {
								if ( in_array( $object_field_opt['type'], $tableless_field_types ) ) {
									$all_fields[$this->pod][$object_field] = $object_field_opt;
								}
							}
						}
					}

					$last_type    = $last_object = $last_pick_val = '';
					$last_options = array();

					$single_multi = 'single';

					if ( isset( $this->fields[$params->name] ) ) {
						$single_multi = pods_v( $this->fields[ $params->name ][ 'type' ] . '_format_type', $this->fields[ $params->name ], 'single' );
					}

					if ( 'single' != $this->fields[$params->name][$this->fields[$params->name]['type'] . '_format_type'] ) {
						$limit = (int) pods_v( $this->fields[$params->name]['type'] . '_limit', $this->fields[$params->name], 0 );
					} else {
						$limit = 1;
					}

					$last_limit = 0;

					// Loop through each traversal level
					foreach ( $params->traverse as $key => $field ) {
						$last_loop = false;

						if ( count( $params->traverse ) <= ( $key + 1 ) ) {
							$last_loop = true;
						}

						$field_exists = isset( $all_fields[$pod][$field] );

						$simple       = false;
						$last_options = array();

						if ( $field_exists && 'pick' == $all_fields[$pod][$field]['type'] && ( in_array( $all_fields[$pod][$field]['pick_object'], $simple_tableless_objects ) || 1 == $all_fields[$pod][$field]['pick_simple'] ) ) {
							$simple       = true;
							$last_options = $all_fields[$pod][$field];
						}

						// Tableless handler
						if ( $field_exists && ( ! in_array( $all_fields[$pod][$field]['type'], array( 'pick', 'taxonomy' ) ) || ! $simple ) ) {
							$type        = $all_fields[$pod][$field]['type'];
							$pick_object = $all_fields[$pod][$field]['pick_object'];
							$pick_val    = $all_fields[$pod][$field]['pick_val'];

							if ( 'table' == $pick_object ) {
								$pick_val = pods_v_sanitized( 'pick_table', $all_fields[$pod][$field], $pick_val, null, true );
							} elseif ( '__current__' == $pick_val ) {
								$pick_val = $pod;
							}

							$last_limit = 0;

							if ( in_array( $type, $tableless_field_types ) ) {
								if ( ! empty( $all_fields[$pod][$field][$type . '_format_type'] ) && 'single' != $all_fields[$pod][$field][$type . '_format_type'] ) {
									$last_limit = (int) pods_v( $type . '_limit', $all_fields[$pod][$field], 0 );
								} else {
									$last_limit = 1;
								}
							}

							$last_type     = $type;
							$last_object   = $pick_object;
							$last_pick_val = $pick_val;
							$last_options  = $all_fields[$pod][$field];

							// Temporary hack until there's some better handling here
							$last_limit = $last_limit * count( $ids );

							// Get related IDs
							if ( !isset( $all_fields[ $pod ][ $field ][ 'pod_id' ] ) ) {
								$all_fields[ $pod ][ $field ][ 'pod_id' ] = 0;
							}

							if ( isset( $all_fields[$pod][$field]['id'] ) && isset( $all_fields[$pod][$field]['pod_id'] ) ) {
								if ( ! $api ) {
									$api = pods_api();

									$api->display_errors =& $this->display_errors;
								}

								$ids = $api->lookup_related_items(
									$all_fields[$pod][$field]['id'],
									$all_fields[$pod][$field]['pod_id'],
									$ids,
									$all_fields[$pod][$field]
								);
							}

							// No items found
							if ( empty( $ids ) ) {
								return false;
							} // @todo This should return array() if not $params->single
							elseif ( 0 < $last_limit ) {
								$ids = array_slice( $ids, 0, $last_limit );
							}

							// Get $pod if related to a Pod
							if ( ! empty( $pick_object ) && ( ! empty( $pick_val ) || in_array( $pick_object, array( 'user', 'media', 'comment' ) ) ) ) {
								if ( 'pod' == $pick_object ) {
									$pod = $pick_val;
								} else {
									if ( ! $api ) {
										$api = pods_api();

										$api->display_errors =& $this->display_errors;
									}

									$check = $api->get_table_info( $pick_object, $pick_val );

									if ( ! empty( $check ) && ! empty( $check['pod'] ) ) {
										$pod = $check['pod']['name'];
									}
								}
							}
						} // Assume last iteration
						else {
							// Invalid field
							if ( 0 == $key ) {
								return false;
							}

							$last_loop = true;
						}

						if ( $last_loop ) {
							$object_type = $last_object;
							$object      = $last_pick_val;

							if ( in_array( $last_type, Pods_Form::file_field_types() ) ) {
								$object_type = 'media';
								$object      = 'attachment';
							}

							$data = array();

							if ( ! $api ) {
								$api = pods_api();

								$api->display_errors =& $this->display_errors;
							}

							$table = $api->get_table_info( $object_type, $object, null, null, $last_options );

							$join = $where = array();

							if ( ! empty( $table['join'] ) ) {
								$join = (array) $table['join'];
							}

							if ( ! empty( $table['where'] ) || ! empty( $ids ) ) {
								foreach ( $ids as $id ) {
									$where[$id] = '`t`.`' . $table['field_id'] . '` = ' . (int) $id;
								}

								if ( ! empty( $where ) ) {
									$where = array( implode( ' OR ', $where ) );
								}

								if ( ! empty( $table['where'] ) ) {
									$where = array_merge( $where, array_values( (array) $table['where'] ) );
								}
							}

							/**
							 * @var $related_obj Pods
							 */
							$related_obj = false;

							if ( 'pod' == $object_type ) {
								$related_obj = pods( $object, null, false );
							} elseif ( ! empty( $table[ 'pod' ] ) ) {
								$related_obj = pods( $table['pod']['name'], null, false );
							}

							if ( ! empty( $table['table'] ) || ! empty( $related_obj ) ) {
								$sql = array(
									'select'     => '*, `t`.`' . $table['field_id'] . '` AS `pod_item_id`',
									'table'      => $table['table'],
									'join'       => $join,
									'where'      => $where,
									'orderby'    => $params->orderby,
									'pagination' => false,
									'search'     => false,
									'limit'      => -1, // @todo Need a better way than no limit
									'expires'    => 180 // @todo This could potentially cause issues if someone changes the data within this time and persistent storage is used
								);

								// Output types
								if ( in_array( $params->output, array( 'ids', 'objects', 'pods' ) ) ) {
									$sql['select'] = '`t`.`' . $table['field_id'] . '` AS `pod_item_id`';
								} elseif ( 'names' == $params->output && ! empty( $table['field_index'] ) ) {
									$sql['select'] = '`t`.`' . $table['field_index'] . '` AS `pod_item_index`, `t`.`' . $table['field_id'] . '` AS `pod_item_id`';
								}

								if ( is_array( $params->params ) && ! empty( $params->params ) ) {
									$where = $sql['where'];

									$sql = array_merge( $sql, $params->params );

									if ( isset( $params->params['where'] ) ) {
										$sql['where'] = array_merge( (array) $where, (array) $params->params['where'] );
									}
								}

								if ( empty( $related_obj ) ) {
									if ( ! is_object( $this->alt_data ) ) {
										$this->alt_data = pods_data( null, 0, true, true );
									}

									$item_data = $this->alt_data->select( $sql );
								} else {
									$item_data = $related_obj->find( $sql )->data();
								}

								$items = array();

								if ( ! empty( $item_data ) ) {
									foreach ( $item_data as $item ) {
										if ( is_array( $item ) ) {
											$item = (object) $item;
										}

										if ( empty( $item->pod_item_id ) ) {
											continue;
										}

										// Bypass pass field
										if ( isset( $item->user_pass ) ) {
											unset( $item->user_pass );
										}

										// Get Item ID
										$item_id = $item->pod_item_id;

										// Output types
										if ( 'ids' == $params->output ) {
											$item = (int) $item_id;
										} elseif ( 'names' == $params->output && ! empty( $table['field_index'] ) ) {
											$item = $item->pod_item_index;
										} elseif ( 'objects' == $params->output ) {
											if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
												$item = get_post( $item_id );
											} elseif ( 'taxonomy' == $object_type ) {
												$item = get_term( $item_id, $object );
											} elseif ( 'user' == $object_type ) {
												$item = get_userdata( $item_id );

												if ( ! empty( $item ) ) {
													// Get other vars
													$roles   = $item->roles;
													$caps    = $item->caps;
													$allcaps = $item->allcaps;

													$item = $item->data;

													// Set other vars
													$item->roles   = $roles;
													$item->caps    = $caps;
													$item->allcaps = $allcaps;

													unset( $item->user_pass );
												}
											} elseif ( 'comment' == $object_type ) {
												$item = get_comment( $item_id );
											} else {
												$item = (object) $item;
											}
										} elseif ( 'pods' == $params->output ) {
											$item = pods( $object, (int) $item_id );
										} else { // arrays
											$item = get_object_vars( (object) $item );
										}

										// Pass item data into $data
										$items[$item_id] = $item;
									}

									// Cleanup
									unset( $item_data );

									// Return all of the data in the order expected
									if ( empty( $params->orderby ) ) {
										foreach ( $ids as $id ) {
											if ( isset( $items[ $id ] ) ) {
												$data[ $id ] = $items[ $id ];
											}
										}
									} else {
										// Use order set by orderby
										foreach ( $items as $id => $v ) {
											if ( in_array( $id, $ids ) ) {
												$data[ $id ] = $v;
											}
										}
									}
								}
							}

							if ( in_array( $last_type, $tableless_field_types ) || in_array( $last_type, array( 'boolean', 'number', 'currency' ) ) ) {
								$params->raw = true;
							}

							if ( empty( $data ) ) {
								$value = false;
							} else {
								$object_type = $table['type'];

								if ( in_array( $table['type'], array( 'post_type', 'attachment', 'media' ) ) ) {
									$object_type = 'post';
								}

								$no_conflict = true;

								if ( in_array( $object_type, array( 'post', 'user', 'comment', 'settings' ) ) ) {
									$no_conflict = pods_no_conflict_check( $object_type );

									if ( ! $no_conflict ) {
										pods_no_conflict_on( $object_type );
									}
								}

								// Return entire array
								if ( false !== $field_exists && ( in_array( $last_type, $tableless_field_types ) && ! $simple ) ) {
									$value = $data;
								} // Return an array of single column values
								else {
									$value = array();

									$file_field_types = Pods_Form::file_field_types();

									foreach ( $data as $item_id => $item ) {
										// $field is 123x123, needs to be _src.123x123
										$full_field = implode( '.', array_splice( $params->traverse, $key ) );

										if ( ( ( false !== strpos( $full_field, '_src' ) || 'guid' == $field ) && ( in_array( $table['type'], array( 'attachment', 'media' ) ) || in_array( $last_type, $file_field_types ) ) ) || ( in_array( $field, array( '_link', 'detail_url' ) ) || in_array( $field, array( 'permalink', 'the_permalink' ) ) && in_array( $last_type, $file_field_types ) ) ) {
											$size = 'full';

											if ( false !== strpos( $full_field, '_src.' ) && 5 < strlen( $full_field ) ) {
												$size = substr( $full_field, 5 );
											} elseif ( false !== strpos( $full_field, '_src_relative.' ) && 14 < strlen( $full_field ) ) {
												$size = substr( $full_field, 14 );
											} elseif ( false !== strpos( $full_field, '_src_schemeless.' ) && 16 < strlen( $full_field ) ) {
												$size = substr( $full_field, 16 );
											}

											$value_url = pods_image_url( $item_id, $size );

											if ( false !== strpos( $full_field, '_src_relative' ) && ! empty( $value_url ) ) {
												$value_url_parsed = parse_url( $value_url );
												$value_url        = $value_url_parsed['path'];
											} elseif ( false !== strpos( $full_field, '_src_schemeless' ) && ! empty( $value_url ) ) {
												$value_url = str_replace( array( 'http://', 'https://' ), '//', $value_url );
											}

											if ( ! empty( $value_url ) ) {
												$value[] = $value_url;
											}

											$params->raw_display = true;
										} elseif ( false !== strpos( $full_field, '_img' ) && ( in_array( $table['type'], array( 'attachment', 'media' ) ) || in_array( $last_type, Pods_Form::file_field_types() ) ) ) {
											$size = 'full';

											if ( false !== strpos( $full_field, '_img.' ) && 5 < strlen( $full_field ) ) {
												$size = substr( $full_field, 5 );
											}

											$value[] = pods_image( $item_id, $size );

											$params->raw_display = true;
										} elseif ( in_array( $field, array( '_link', 'detail_url' ) ) || in_array( $field, array( 'permalink', 'the_permalink' ) ) ) {
											if ( 'pod' == $object_type ) {
												if ( is_object( $related_obj ) ) {
													$related_obj->fetch( $item_id );

													$value[] = $related_obj->field( 'detail_url' );
												} else {
													$value[] = '';
												}
											} elseif ( 'post' == $object_type ) {
												$value[] = get_permalink( $item_id );
											} elseif ( 'taxonomy' == $object_type ) {
												$value[] = get_term_link( $item_id, $object );
											} elseif ( 'user' == $object_type ) {
												$value[] = get_author_posts_url( $item_id );
											} elseif ( 'comment' == $object_type ) {
												$value[] = get_comment_link( $item_id );
											} else {
												$value[] = '';
											}

											$params->raw_display = true;
										} elseif ( is_array( $item ) && isset( $item[$field] ) ) {
											if ( $table['field_id'] == $field ) {
												$value[] = (int) $item[$field];
											} else {
												$value[] = $item[$field];
											}
										} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
											if ( $table['field_id'] == $field ) {
												$value[] = (int) $item->{$field};
											} else {
												$value[] = $item->{$field};
											}
										} elseif ( 'post' == $object_type ) {
											// Support for WPML 'duplicated' translation handling
											if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $object ) ) {
												$master_post_id = (int) get_post_meta( $item_id, '_icl_lang_duplicate_of', true );

												if ( 0 < $master_post_id ) {
													$item_id = $master_post_id;
												}
											}

											$value[] = get_post_meta( $item_id, $field, true );
										} elseif ( in_array( $object_type, array( 'post', 'user', 'comment' ) ) ) {
											$value[] = get_metadata( $object_type, $item_id, $field, true );
										} elseif ( 'settings' == $object_type ) {
											$value[] = get_option( $object . '_' . $field );
										}
									}
								}

								if ( in_array( $object_type, array( 'post', 'user', 'comment', 'settings' ) ) && ! $no_conflict ) {
									pods_no_conflict_off( $object_type );
								}

								// Handle Simple Relationships
								if ( $simple ) {
									if ( null === $params->single ) {
										$params->single = false;
									}

									$value = Pods_Form::field_method( 'pick', 'simple_value', $field, $value, $last_options, $all_fields[$pod], 0, true );
								} elseif ( false === $params->in_form && ! empty( $value ) ) {
									$value = array_values( $value );
								}

								// Return a single column value
								if ( false === $params->in_form && 1 == $limit && ! empty( $value ) && is_array( $value ) && 1 == count( $value ) ) {
									$value = current( $value );
								}
							}

							if ( $last_options ) {
								$last_field_data = $last_options;
							}

							break;
						}
					}
				}
			}
		}

		if ( ! empty( $params->traverse ) && 1 < count( $params->traverse ) ) {
			$field_names = implode( '.', $params->traverse );

			$this->row[$field_names] = $value;
		} elseif ( 'arrays' != $params->output && in_array( $field_data['type'], $tableless_field_types ) ) {
			$this->row['_' . $params->output . '_' . $params->full_name] = $value;
		} elseif ( 'arrays' == $params->output || ! in_array( $field_data['type'], $tableless_field_types ) ) {
			$this->row[$params->full_name] = $value;
		}

		if ( $params->single && is_array( $value ) && 1 == count( $value ) ) {
			$value = current( $value );
		}

		if ( ! empty( $last_field_data ) ) {
			$field_data = $last_field_data;
		}

		// @todo Expand this into traversed fields too
		if ( ! empty( $field_data ) && ( $params->display || ! $params->raw ) && ! $params->in_form && ! $params->raw_display ) {
			if ( $params->display || ( ( $params->get_meta || $params->deprecated ) && ! in_array( $field_data['type'], $tableless_field_types ) ) ) {
				$post_temp = false;

				if ( 'post_type' == $this->pod_data['type'] && 0 < $id && ( ! isset( $GLOBALS['post'] ) || empty( $GLOBALS['post'] ) ) ) {
					global $post_ID, $post;

					$post_temp = true;

					$old_post = $post;
					$old_ID   = $post_ID;

					$post    = get_post( $id );
					$post_ID = $id;
				}

				$filter = $field_data['display_filter'];

				if ( 0 < strlen( $filter ) ) {
					$args = array(
						$filter,
						$value
					);

					$filter_args = $field_data['display_filter_args'];

					if ( ! empty( $filter_args ) ) {
						$args = array_merge( $args, compact( $filter_args ) );
					}

					$value = call_user_func_array( 'apply_filters', $args );
				} elseif ( 1 == pods_v( 'display_process', $field_data, 1 ) ) {
					$value = Pods_Form::display(
						$field_data[ 'type' ],
						$value,
						$params->name,
						$field_data,
						$this->pod_data,
						$this->id()
					);
				}

				// Reset globals
				// @todo Should we use wp_reset_postdata() here, will it affect other things people are doing?
				if ( $post_temp ) {
					$post    = $old_post;
					$post_ID = $old_ID;
				}
			} else {
				$value = Pods_Form::value(
					$field_data[ 'type' ],
					$value,
					$params->name,
					$field_data,
					$this->pod_data,
					$this->id()
				);
			}
		}

		/**
		 * Modify value returned by field() directly before output.
		 *
		 * Will not run if value was null
		 *
		 * @since unknown
		 *
		 * @param array|string|null $value Value to be returned.
		 * @param array|object $row Current row being outputted.
		 * @param array $params Params array passed to field().
		 * @param object|Pods $this Current Pods object.
		 *
		 */
		$value = apply_filters( 'pods_pods_field', $value, $this->row, $params, $this );

		return $value;

	}

	/**
	 * Check if an item field has a specific value in it
	 *
	 * @param string $field Field name
	 * @param mixed  $value Value to check
	 * @param int    $id    (optional) ID of the pod item to check
	 *
	 * @return bool Whether the value was found
	 *
	 * @since 2.3.3
	 */
	public function has( $field, $value, $id = null ) {

		$pod =& $this;

		if ( null === $id ) {
			$id = $this->id();
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		// @todo Needs hook doc
		do_action( 'pods_pods_has', $field, $value, $id, $this );

		// @todo Support traversal
		if ( ! isset( $this->fields[$field] ) ) {
			return false;
		}

		$tableless_field_types = Pods_Form::tableless_field_types();

		// Tableless fields
		if ( in_array( $this->fields[$field]['type'], $tableless_field_types ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			$simple_tableless_objects = Pods_Form::simple_tableless_objects();

			if ( 'pick' == $this->fields[$field]['type'] && ( in_array( $this->fields[$field]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$field]['pick_simple'] ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) ) {
					$current_value = (array) $current_value;
				}

				foreach ( $current_value as $v ) {
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			} else {
				$related_ids = pods_api()->lookup_related_items( $this->fields[$field]['id'], $this->pod_data['id'], $id, $this->fields[$field], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^0-9]/', $v ) ) {
						$value[$k] = (int) $v;
					}
					else {
						// @todo Convert slugs into IDs
					}
				}

				foreach ( $related_ids as $v ) {
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			}
		} // Text fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::text_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				return stripos( $current_value, $value );
			}
		} // All other fields
		else {
			return $this->is( $field, $value, $id );
		}

		return false;

	}

	/**
	 * Check if an item field is a specific value
	 *
	 * @param string $field Field name
	 * @param mixed  $value Value to check
	 * @param int    $id    (optional) ID of the pod item to check
	 *
	 * @return bool Whether the value was found
	 *
	 * @since 2.3.3
	 */
	public function is( $field, $value, $id = null ) {

		$pod =& $this;

		if ( null === $id ) {
			$id = $this->id();
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		// @todo Needs hook doc
		do_action( 'pods_pods_is', $field, $value, $id, $this );

		// @todo Support traversal
		if ( ! isset( $this->fields[$field] ) ) {
			return false;
		}

		$tableless_field_types = Pods_Form::tableless_field_types();

		// Tableless fields
		if ( in_array( $this->fields[$field]['type'], $tableless_field_types ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			$simple_tableless_objects = Pods_Form::simple_tableless_objects();

			$current_value = array();

			if ( 'pick' == $this->fields[$field]['type'] && ( in_array( $this->fields[$field]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$field]['pick_simple'] ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) ) {
					$current_value = (array) $current_value;
				}

				foreach ( $current_value as $v ) {
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			} else {
				$related_ids = pods_api()->lookup_related_items( $this->fields[$field]['id'], $this->pod_data['id'], $id, $this->fields[$field], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^0-9]/', $v ) ) {
						$value[$k] = (int) $v;
					}
					else {
						// @todo Convert slugs into IDs
					}
				}

				foreach ( $related_ids as $v ) {
					if ( in_array( $v, $value ) ) {
						return true;
					}
				}
			}

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
		} // Number fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::number_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( (float) $current_value === (float) $value ) {
				return true;
			}
		} // Date fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::date_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				if ( strtotime( $current_value ) == strtotime( $value ) ) {
					return true;
				}
			} elseif ( empty( $value ) ) {
				return true;
			}
		} // Text fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::text_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( (string) $current_value === (string) $value ) {
				return true;
			}
		} // All other fields
		else {
			$current_value = $pod->raw( $field );

			if ( $current_value === $value ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Return the item ID
	 *
	 * @return int
	 * @since 2.0
	 */
	public function id() {

		if ( isset( $this->data->row ) && isset( $this->data->row[ 'id' ] ) ) {
			// If we already have data loaded return that ID
			return $this->data->row[ 'id' ];
		}

		return $this->field( $this->data->field_id );

	}

	/**
	 * Return the previous item ID, loops at the last id to return the first
	 *
	 * @param int   $id
	 * @param array $params_override
	 *
	 * @return int
	 * @since 2.0
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
			'limit'   => 1
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
				if ( ! empty( $params['where'] ) ) {
					$params['where']   = (array) $params['where'];
					$params['where'][] = "`t`.`{$this->data->field_id}` < {$id}";
				} else {
					$params['where'] = "`t`.`{$this->data->field_id}` < {$id}";
				}
			} elseif ( ! empty( $params['offset'] ) && 0 < $params['offset'] ) {
				$params['offset'] -= 1;
			} elseif ( empty( $params['offset'] ) && ! empty( $this->params ) && 0 < $this->row_number ) {
				$params['offset'] = $this->row_number - 1;
			} else {
				return 0;
			}

			if ( ! empty( $params['orderby'] ) ) {
				if ( is_array( $params['orderby'] ) ) {
					foreach ( $params['orderby'] as $orderby => $dir ) {
						$dir = strtoupper( $dir );

						if ( ! in_array( $dir, array( 'ASC', 'DESC' ) ) ) {
							continue;
						}

						if ( 'ASC' == $dir ) {
							$params['orderby'][$orderby] = 'DESC';
						} else {
							$params['orderby'][$orderby] = 'ASC';
						}
					}

					$params['orderby'][$this->data->field_id] = 'DESC';
				} elseif ( "`t`.`{$this->data->field_id}` DESC" != $params['orderby'] ) {
					$params['orderby'] .= ", `t`.`{$this->data->field_id}` DESC";
				}
			}

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}

		$pod = pods( $this->pod, $params );

		$prev_id = 0;

		if ( $pod->fetch() ) {
			$prev_id = $pod->id();
		}

		// @todo Needs hook doc
		$prev_id = apply_filters( 'pods_pods_prev_id', $prev_id, $id, $pod, $params_override, $this );

		return $prev_id;

	}

	/**
	 * Return the next item ID, loops at the first id to return the last
	 *
	 * @param int  $id
	 * @param null $params_override
	 *
	 * @return int
	 * @since    2.0
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
			'limit'   => 1
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
				if ( ! empty( $params['where'] ) ) {
					$params['where']   = (array) $params['where'];
					$params['where'][] = "{$id} < `t`.`{$this->data->field_id}`";
				} else {
					$params['where'] = "{$id} < `t`.`{$this->data->field_id}`";
				}
			} elseif ( empty( $params['offset'] ) ) {
				if ( ! empty( $this->params ) && -1 < $this->row_number ) {
					$params['offset'] = $this->row_number + 1;
				} else {
					$params['offset'] = 1;
				}
			} else {
				$params['offset'] += 1;
			}

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}

		$pod = pods( $this->pod, $params );

		$next_id = 0;

		if ( $pod->fetch() ) {
			$next_id = $pod->id();
		}

		// @todo Needs hook doc
		$next_id = apply_filters( 'pods_pods_next_id', $next_id, $id, $pod, $params_override, $this );

		return $next_id;

	}

	/**
	 * Return the first item ID
	 *
	 * @param array $params_override
	 *
	 * @return int
	 * @since 2.3
	 */
	public function first_id( $params_override = null ) {

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'orderby' => "`t`.`{$this->data->field_id}` ASC",
			'limit'   => 1
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

		$first_id = 0;

		if ( $pod->fetch() ) {
			$first_id = $pod->id();
		}

		// @todo Needs hook doc
		$first_id = apply_filters( 'pods_pods_first_id', $first_id, $pod, $params_override, $this );

		return $first_id;

	}

	/**
	 * Return the last item ID
	 *
	 * @param array $params_override
	 *
	 * @return int
	 * @since 2.3
	 */
	public function last_id( $params_override = null ) {

		$params = array(
			'select'  => "`t`.`{$this->data->field_id}`",
			'orderby' => "`t`.`{$this->data->field_id}` DESC",
			'limit'   => 1
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

			if ( ! empty( $params['orderby'] ) ) {
				if ( is_array( $params['orderby'] ) ) {
					foreach ( $params['orderby'] as $orderby => $dir ) {
						$dir = strtoupper( $dir );

						if ( ! in_array( $dir, array( 'ASC', 'DESC' ) ) ) {
							continue;
						}

						if ( 'ASC' == $dir ) {
							$params['orderby'][$orderby] = 'DESC';
						} else {
							$params['orderby'][$orderby] = 'ASC';
						}
					}

					$params['orderby'][$this->data->field_id] = 'DESC';
				} elseif ( "`t`.`{$this->data->field_id}` DESC" != $params['orderby'] ) {
					$params['orderby'] .= ", `t`.`{$this->data->field_id}` DESC";
				}
			}

			$params['select'] = "`t`.`{$this->data->field_id}`";
			$params['limit']  = 1;
		}

		$pod = pods( $this->pod, $params );

		$last_id = 0;

		if ( $pod->fetch() ) {
			$last_id = $pod->id();
		}

		// @todo Needs hook doc
		$last_id = apply_filters( 'pods_pods_last_id', $last_id, $pod, $params_override, $this );

		return $last_id;

	}

	/**
	 * Return the item name
	 *
	 * @return string
	 * @since 2.0
	 */
	public function index() {

		return $this->field( $this->data->field_index );

	}

	/**
	 * Find items of a pod, much like WP_Query, but with advanced table handling.
	 *
	 * @param array  $params An associative array of parameters
	 * @param int    $limit  (optional) (deprecated) Limit the number of items to find, use -1 to return all items with no limit
	 * @param string $where  (optional) (deprecated) SQL WHERE declaration to use
	 * @param string $sql    (optional) (deprecated) For advanced use, a custom SQL query to run
	 *
	 * @return \Pods The pod object
	 * @since 2.0
	 * @link  http://pods.io/docs/find/
	 */
	public function find( $params = null, $limit = 15, $where = null, $sql = null ) {

		static $tableless_field_types = null;
		static $simple_tableless_objects = null;
		static $api = null;

		if ( ! $tableless_field_types ) {
			$tableless_field_types = Pods_Form::tableless_field_types();
		}

		if ( ! $simple_tableless_objects ) {
			$simple_tableless_objects = Pods_Form::simple_tableless_objects();
		}

		$this->params = $params;

		$select = '`t`.*';

		if ( ! in_array( $this->pod_data['type'], array( 'pod', 'table' ) ) && 'table' == $this->pod_data['storage'] ) {
			$select .= ', `d`.*';
		}

		if ( empty( $this->data->table ) ) {
			return $this;
		}

		$defaults = array(
			'table'               => $this->data->table,
			'select'              => $select,
			'join'                => null,

			'where'               => $where,
			'groupby'             => null,
			'having'              => null,
			'orderby'             => null,

			'limit'               => (int) $limit,
			'offset'              => null,
			'page'                => (int) $this->page,
			'page_var'            => $this->page_var,
			'pagination'          => (boolean) $this->pagination,

			'search'              => (boolean) $this->search,
			'search_var'          => $this->search_var,
			'search_query'        => null,
			'search_mode'         => $this->search_mode,
			'search_across'       => false,
			'search_across_picks' => false,
			'search_across_files' => false,

			'filters'             => $this->filters,
			'sql'                 => $sql,

			'expires'             => null,
			'cache_mode'          => 'cache'
		);

		if ( is_array( $params ) ) {
			$params = (object) array_merge( $defaults, $params );
		}
		if ( is_object( $params ) ) {
			$params = (object) array_merge( $defaults, get_object_vars( $params ) );
		} else {
			$defaults['orderby'] = $params;
			$params              = (object) $defaults;
		}

		// @todo Needs hook doc
		$params = apply_filters( 'pods_pods_find', $params, $this );

		$params->limit = (int) $params->limit;

		if ( 0 == $params->limit ) {
			$params->limit = -1;
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
			$params->search_query = trim( pods_v_sanitized( $this->search_var, 'get', '' ) );
		}

		// Allow orderby array ( 'field' => 'asc|desc' )
		if ( ! empty( $params->orderby ) && is_array( $params->orderby ) ) {
			foreach ( $params->orderby as $k => &$orderby ) {
				if ( ! is_numeric( $k ) ) {
					$key = '';

					$order = 'ASC';

					if ( 'DESC' == strtoupper( $orderby ) ) {
						$order = 'DESC';
					}

					if ( isset( $this->fields[$k] ) && in_array( $this->fields[$k]['type'], $tableless_field_types ) ) {
						if ( in_array( $this->fields[$k]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$k]['pick_simple'] ) {
							if ( 'table' == $this->pod_data['storage'] ) {
								if ( ! in_array( $this->pod_data['type'], array( 'pod', 'table' ) ) ) {
									$key = "`d`.`{$k}`";
								} else {
									$key = "`t`.`{$k}`";
								}
							} else {
								$key = "`{$k}`.`meta_value`";
							}
						} else {
							$pick_val = $this->fields[$k]['pick_val'];

							if ( '__current__' == $pick_val ) {
								$pick_val = $this->pod;
							}

							if ( ! $api ) {
								$api = pods_api();

								$api->display_errors =& $this->display_errors;
							}

							$table = $api->get_table_info( $this->fields[$k]['pick_object'], $pick_val );

							if ( ! empty( $table ) ) {
								$key = "`{$k}`.`" . $table['field_index'] . '`';
							}
						}
					}

					if ( empty( $key ) ) {
						if ( ! in_array( $this->pod_data['type'], array( 'pod', 'table' ) ) ) {
							if ( isset( $this->pod_data['object_fields'][$k] ) ) {
								$key = "`t`.`{$k}`";
							} elseif ( isset( $this->fields[$k] ) ) {
								if ( 'table' == $this->pod_data['storage'] ) {
									$key = "`d`.`{$k}`";
								} else {
									$key = "`{$k}`.`meta_value`";
								}
							} else {
								foreach ( $this->pod_data['object_fields'] as $object_field => $object_field_opt ) {
									if ( $object_field == $k || in_array( $k, $object_field_opt['alias'] ) ) {
										$key = "`t`.`{$object_field}`";
									}
								}
							}
						} elseif ( isset( $this->fields[$k] ) ) {
							if ( 'table' == $this->pod_data['storage'] ) {
								$key = "`t`.`{$k}`";
							} else {
								$key = "`{$k}`.`meta_value`";
							}
						}

						if ( empty( $key ) ) {
							$key = $k;

							if ( false === strpos( $key, ' ' ) && false === strpos( $key, '`' ) ) {
								$key = '`' . str_replace( '.', '`.`', $key ) . '`';
							}
						}
					}

					$orderby = $key;

					if ( false === strpos( $orderby, ' ' ) ) {
						$orderby .= ' ' . $order;
					}
				}
			}
		}

		// Add prefix to $params->orderby if needed
		if ( ! empty( $params->orderby ) ) {
			if ( ! is_array( $params->orderby ) ) {
				$params->orderby = array( $params->orderby );
			}

			foreach ( $params->orderby as &$prefix_orderby ) {
				if ( false === strpos( $prefix_orderby, ',' ) && false === strpos( $prefix_orderby, '(' ) && false === stripos( $prefix_orderby, ' AS ' ) && false === strpos( $prefix_orderby, '`' ) && false === strpos( $prefix_orderby, '.' ) ) {
					if ( false !== stripos( $prefix_orderby, ' DESC' ) ) {
						$k   = trim( str_ireplace( array( '`', ' DESC' ), '', $prefix_orderby ) );
						$dir = 'DESC';
					} else {
						$k   = trim( str_ireplace( array( '`', ' ASC' ), '', $prefix_orderby ) );
						$dir = 'ASC';
					}

					$key = $k;

					if ( ! in_array( $this->pod_data['type'], array( 'pod', 'table' ) ) ) {
						if ( isset( $this->pod_data['object_fields'][$k] ) ) {
							$key = "`t`.`{$k}`";
						} elseif ( isset( $this->fields[$k] ) ) {
							if ( 'table' == $this->pod_data['storage'] ) {
								$key = "`d`.`{$k}`";
							} else {
								$key = "`{$k}`.`meta_value`";
							}
						} else {
							foreach ( $this->pod_data['object_fields'] as $object_field => $object_field_opt ) {
								if ( $object_field == $k || in_array( $k, $object_field_opt['alias'] ) ) {
									$key = "`t`.`{$object_field}`";
								}
							}
						}
					} elseif ( isset( $this->fields[$k] ) ) {
						if ( 'table' == $this->pod_data['storage'] ) {
							$key = "`t`.`{$k}`";
						} else {
							$key = "`{$k}`.`meta_value`";
						}
					}

					$prefix_orderby = "{$key} {$dir}";
				}
			}
		}

		$this->data->select( $params );

		return $this;

	}

	/**
	 * Fetch an item from a Pod. If $id is null, it will return the next item in the list after running find().
	 * You can rewind the list back to the start by using reset().
	 *
	 * Providing an $id will fetch a specific item from a Pod, much like a call to pods(), and can handle either an id or slug.
	 *
	 * @see   Pods_Data::fetch
	 *
	 * @param int       $id           ID or slug of the item to fetch
	 * @param null|bool $explicit_set Whether to set explicitly (use false when in loop)
	 *
	 * @return array An array of fields from the row
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/fetch/
	 */
	public function fetch( $id = null, $explicit_set = null ) {

		/**
		 * Runs directly before an item is fetched by fetch()
		 *
		 * @since unknown
		 *
		 * @param int|string|null $id Item ID being fetched or null.
		 * @param object|Pods $this Current Pods object.
		 */
		do_action( 'pods_pods_fetch', $id, $this );

		if ( ! empty( $id ) ) {
			$this->params = array();
		}

		$this->data->fetch( $id, $explicit_set );

		return $this->row;

	}

	/**
	 * (Re)set the MySQL result pointer
	 *
	 * @see   Pods_Data::reset
	 *
	 * @param int $row ID of the row to reset to
	 *
	 * @return \Pods The pod object
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/reset/
	 */
	public function reset( $row = null ) {

		/**
		 * Runs directly before the Pods object is reset by reset()
		 *
		 * @since unknown
		 *
		 * @param int|string|null The ID of the row being reset to or null if being reset to the beginningg.
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
	 * @see   Pods_Data::total
	 *
	 * @return int Number of rows returned by find(), based on the 'limit' parameter set
	 * @since 2.0
	 * @link  http://pods.io/docs/total/
	 */
	public function total() {

		// @todo Needs hook doc
		do_action( 'pods_pods_total', $this );

		$this->data->total();

		$this->total =& $this->data->total;

		return $this->total;

	}

	/**
	 * Fetch the total amount of rows found by the last call to find(), regardless of the 'limit' parameter set.
	 *
	 * This is different than the total number of rows limited by the current call, which you can get with total().
	 *
	 * @see   Pods_Data::total_found
	 *
	 * @return int Number of rows returned by find(), regardless of the 'limit' parameter
	 * @since 2.0
	 * @link  http://pods.io/docs/total-found/
	 */
	public function total_found() {

		/**
		 * Runs directly before the value of total_found() is determined and returned.
		 *
		 * @since unknown
		 *
		 * @param object|Pods $this Current Pods object.
		 *
		 */
		do_action( 'pods_pods_total_found', $this );

		$this->data->total_found();

		$this->total_found =& $this->data->total_found;

		return $this->total_found;

	}

	/**
	 * Fetch the total number of pages, based on total rows found and the last find() limit
	 *
	 * @param null|int $limit  Rows per page
	 * @param null|int $offset Offset of rows
	 * @param null|int $total  Total rows
	 *
	 * @return int Number of pages
	 * @since 2.3.10
	 */
	public function total_pages( $limit = null, $offset = null, $total = null ) {

		// @todo Needs hook doc
		do_action( 'pods_pods_total_pages', $this );

		if ( null === $limit ) {
			$limit = $this->limit;
		}

		if ( null === $offset ) {
			$offset = $this->offset;
		}

		if ( null === $total ) {
			$total = $this->total_found();
		}

		$total_pages = ceil( ( $total - $offset ) / $limit );

		return $total_pages;

	}

	/**
	 * Fetch the zebra switch
	 *
	 * @see   Pods_Data::zebra
	 *
	 * @return bool Zebra state
	 * @since 1.12
	 */
	public function zebra() {

		// @todo Needs hook doc
		do_action( 'pods_pods_zebra', $this );

		return $this->data->zebra();

	}

	/**
	 * Fetch the nth state
	 *
	 * @see   Pods_Data::nth
	 *
	 * @param int|string $nth The $nth to match on the Pods_Data::row_number
	 *
	 * @return bool Whether $nth matches
	 * @since 2.3
	 */
	public function nth( $nth = null ) {

		// @todo Needs hook doc
		do_action( 'pods_pods_nth', $nth, $this );

		return $this->data->nth( $nth );

	}

	/**
	 * Fetch the current position in the loop (starting at 1)
	 *
	 * @see   Pods_Data::position
	 *
	 * @return int Current row number (+1)
	 * @since 2.3
	 */
	public function position() {

		// @todo Needs hook doc
		do_action( 'pods_pods_position', $this );

		return $this->data->position();

	}

	/**
	 * Add an item to a Pod by giving an array of field data or set a specific field to
	 * a specific value if you're just wanting to add a new item but only set one field.
	 *
	 * You may be looking for save() in most cases where you're setting a specific field.
	 *
	 * @see   Pods_API::save_pod_item
	 *
	 * @param array|string $data  Either an associative array of field information or a field name
	 * @param mixed        $value (optional) Value of the field, if $data is a field name
	 *
	 * @return int The item ID
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/add/
	 */
	public function add( $data = null, $value = null ) {

		if ( null !== $value ) {
			$data = array( $data => $value );
		}

		// @todo Needs hook doc
		$data = (array) $this->do_hook( 'add', $data );

		if ( empty( $data ) ) {
			return 0;
		}

		$params = array(
			'pod'                 => $this->pod,
			'data'                => $data,
			'allow_custom_fields' => true
		);

		return pods_api()->save_pod_item( $params );

	}

	/**
	 * Add an item to the values of a relationship field, add a value to a number field (field+1), add time to a date field, or add text to a text field
	 *
	 * @see   Pods_API::save_pod_item
	 *
	 * @param string $field Field name
	 * @param mixed  $value ID(s) to add, int|float to add to number field, string for dates (+1 week), or string for text
	 * @param int    $id    (optional) ID of the pod item to update
	 *
	 * @return int The item ID
	 *
	 * @since 2.3
	 */
	public function add_to( $field, $value, $id = null ) {

		$api = pods_api();

		$api->display_errors =& $this->display_errors;

		$pod =& $this;

		$fetch = false;

		if ( null === $id ) {
			$fetch = true;

			$id = $this->id();
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		// @todo Needs hook doc
		do_action( 'pods_pods_add_to', $field, $value, $id, $this );

		if ( ! isset( $this->fields[$field] ) ) {
			return $id;
		}

		$tableless_field_types = Pods_Form::tableless_field_types();

		// Tableless fields
		if ( in_array( $this->fields[$field]['type'], $tableless_field_types ) ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			$simple_tableless_objects = Pods_Form::simple_tableless_objects();

			if ( 'pick' == $this->fields[$field]['type'] && ( in_array( $this->fields[$field]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$field]['pick_simple'] ) ) {
				$current_value = $pod->raw( $field );

				if ( ! empty( $current_value ) || ( ! is_array( $current_value ) && 0 < strlen( $current_value ) ) ) {
					$current_value = (array) $current_value;
				} else {
					$current_value = array();
				}

				$value = array_merge( $current_value, $value );
			} else {
				$related_ids = $api->lookup_related_items( $this->fields[$field]['id'], $this->pod_data['id'], $id, $this->fields[$field], $this->pod_data );

				foreach ( $value as $k => $v ) {
					if ( ! preg_match( '/[^0-9]/', $v ) ) {
						$value[$k] = (int) $v;
					}
				}

				$value = array_merge( $related_ids, $value );
			}

			if ( ! empty( $value ) ) {
				$value = array_filter( array_unique( $value ) );
			} else {
				$value = array();
			}

			if ( empty( $value ) ) {
				return $id;
			}
		} // Number fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::number_field_types() ) ) {
			$current_value = (float) $pod->raw( $field );

			$value = ( $current_value + (float) $value );
		} // Date fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::date_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				$value = strtotime( $value, strtotime( $current_value ) );
			} else {
				$value = strtotime( $value );
			}
		} // Text fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::text_field_types() ) ) {
			$current_value = $pod->raw( $field );

			if ( 0 < strlen( $current_value ) ) {
				$value = $current_value . $value;
			}
		}

		// @todo handle object fields and taxonomies

		$params = array(
			'pod'  => $this->pod,
			'id'   => $id,
			'data' => array(
				$field => $value
			)
		);

		$id = $api->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
			$pod->fetch( $id, false );
		}

		return $id;

	}

	/**
	 * Remove an item from the values of a relationship field, remove a value from a number field (field-1), remove time to a date field
	 *
	 * @see   Pods_API::save_pod_item
	 *
	 * @param string $field Field name
	 * @param mixed  $value ID(s) to add, int|float to add to number field, string for dates (-1 week), or string for text
	 * @param int    $id    (optional) ID of the pod item to update
	 *
	 * @return int The item ID
	 *
	 * @since 2.3.3
	 */
	public function remove_from( $field, $value = null, $id = null ) {

		$api = pods_api();

		$api->display_errors =& $this->display_errors;

		$pod =& $this;

		$fetch = false;

		if ( null === $id ) {
			$fetch = true;

			$id = $this->id();
		} elseif ( $id != $this->id() ) {
			$pod = pods( $this->pod, $id );
		}

		// @todo Needs hook doc
		do_action( 'pods_pods_remove_from', $field, $value, $id, $this );

		if ( ! isset( $this->fields[$field] ) ) {
			return $id;
		}

		$tableless_field_types = Pods_Form::tableless_field_types();

		// Tableless fields
		if ( in_array( $this->fields[$field]['type'], $tableless_field_types ) ) {
			if ( empty( $value ) ) {
				$value = array();
			}

			if ( ! empty( $value ) ) {
				if ( ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}

				$simple_tableless_objects = Pods_Form::simple_tableless_objects();

				if ( 'pick' == $this->fields[$field]['type'] && ( in_array( $this->fields[$field]['pick_object'], $simple_tableless_objects ) || 1 == $this->fields[$field]['pick_simple'] ) ) {
					$current_value = $pod->raw( $field );

					if ( ! empty( $current_value ) ) {
						$current_value = (array) $current_value;
					}

					foreach ( $current_value as $k => $v ) {
						if ( in_array( $v, $value ) ) {
							unset( $current_value[$k] );
						}
					}

					$value = $current_value;
				} else {
					$related_ids = $api->lookup_related_items( $this->fields[$field]['id'], $this->pod_data['id'], $id, $this->fields[$field], $this->pod_data );

					foreach ( $value as $k => $v ) {
						if ( ! preg_match( '/[^0-9]/', $v ) ) {
							$value[$k] = (int) $v;
						} else {
							// @todo Convert slugs into IDs
						}
					}

					foreach ( $related_ids as $k => $v ) {
						if ( in_array( $v, $value ) ) {
							unset( $related_ids[$k] );
						}
					}

					$value = $related_ids;
				}

				if ( ! empty( $value ) ) {
					$value = array_filter( array_unique( $value ) );
				} else {
					$value = array();
				}
			}
		} // Number fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::number_field_types() ) ) {
			// Date fields don't support empty for removing
			if ( empty( $value ) ) {
				return $id;
			}

			$current_value = (float) $pod->raw( $field );

			$value = ( $current_value - (float) $value );
		} // Date fields
		elseif ( in_array( $this->fields[$field]['type'], Pods_Form::date_field_types() ) ) {
			// Date fields don't support empty for removing
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
		}

		// @todo handle object fields and taxonomies

		$params = array(
			'pod'  => $this->pod,
			'id'   => $id,
			'data' => array(
				$field => $value
			)
		);

		$id = $api->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
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
	 * @see   Pods_API::save_pod_item
	 *
	 * @param array|string $data   Either an associative array of field information or a field name
	 * @param mixed        $value  (optional) Value of the field, if $data is a field name
	 * @param int          $id     (optional) ID of the pod item to update
	 * @param array        $params (optional) Additional params to send to save_pod_item
	 *
	 * @return int The item ID
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/save/
	 */
	public function save( $data = null, $value = null, $id = null, $params = null ) {

		if ( null !== $value ) {
			$data = array( $data => $value );
		}

		$fetch = false;

		if ( null !== $id && $this->row && $id == $this->id() ) {
			$fetch = true;
		}

		if ( null === $id ) {
			$fetch = true;

			$id = $this->id();
		}

		// @todo Needs hook doc
		$data = (array) apply_filters( 'pods_pods_save', $data, $id, $this );

		if ( empty( $data ) ) {
			return $id;
		}

		$default = array();

		if ( ! empty( $params ) && is_array( $params ) ) {
			$default = $params;
		}

		$params = array(
			'pod'                 => $this->pod,
			'id'                  => $id,
			'data'                => $data,
			'allow_custom_fields' => true,
			'clear_slug_cache'    => false
		);

		if ( ! empty( $default ) ) {
			$params = array_merge( $params, $default );
		}

		$id = pods_api()->save_pod_item( $params );

		if ( 0 < $id && $fetch ) {
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
	 * @see   Pods_API::delete_pod_item
	 *
	 * @param int $id ID of the Pod item to delete
	 *
	 * @return bool Whether the item was successfully deleted
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/delete/
	 */
	public function delete( $id = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		// @todo Needs hook doc
		$id = (int) apply_filters( 'pods_pods_delete', $id, $this );

		if ( empty( $id ) ) {
			return false;
		}

		$params = array(
			'pod' => $this->pod,
			'id'  => $id
		);

		return pods_api()->delete_pod_item( $params );

	}

	/**
	 * Reset Pod
	 *
	 * @see   Pods_API::reset_pod
	 *
	 * @return bool Whether the Pod was successfully reset
	 *
	 * @since 2.1.1
	 */
	public function reset_pod() {

		$params = array( 'id' => $this->pod_id );

		$this->data->id   = null;
		$this->data->row  = array();
		$this->data->data = array();

		$this->data->total       = 0;
		$this->data->total_found = 0;

		return pods_api()->reset_pod( $params );

	}

	/**
	 * Duplicate an item
	 *
	 * @see   Pods_API::duplicate_pod_item
	 *
	 * @param int $id ID of the pod item to duplicate
	 *
	 * @return int|bool ID of the new pod item
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/duplicate/
	 */
	public function duplicate( $id = null ) {

		if ( null === $id ) {
			$id = $this->id();
		}

		$id = (int) apply_filters( 'pods_pods_duplicate', $id, $this );

		if ( empty( $id ) ) {
			return false;
		}

		$params = array(
			'pod' => $this->pod,
			'id'  => $id
		);

		return pods_api()->duplicate_pod_item( $params );

	}

	/**
	 * Import data / Save multiple rows of data at once
	 *
	 * @see   Pods_API::import
	 *
	 * @param mixed  $import_data  PHP associative array or CSV input
	 * @param bool   $numeric_mode Use IDs instead of the name field when matching
	 * @param string $format       Format of import data, options are php or csv
	 *
	 * @return array IDs of imported items
	 *
	 * @since 2.3
	 */
	public function import( $import_data, $numeric_mode = false, $format = null ) {

		return pods_api()->import( $import_data, $numeric_mode, $format );

	}

	/**
	 * Export an item's data
	 *
	 * @see   Pods_API::export_pod_item
	 *
	 * @param array $fields (optional) Fields to export
	 * @param int   $id     (optional) ID of the pod item to export
	 * @param null  $format
	 *
	 * @return array|bool Data array of the exported pod item
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/export/
	 */
	public function export( $fields = null, $id = null, $format = null ) {

		$params = array(
			'pod'     => $this->pod,
			'id'      => $id,
			'fields'  => null,
			'depth'   => 2,
			'flatten' => false
		);

		if ( is_array( $fields ) && ( isset( $fields['fields'] ) || isset( $fields['depth'] ) ) ) {
			$params = array_merge( $params, $fields );
		} else {
			$params['fields'] = $fields;
		}

		if ( ! empty( $params['fields'] ) && is_array( $params['fields'] ) && ! in_array( $this->pod_data['field_id'], $params['fields'] ) ) {
			$params['fields'] = array_merge( array( $this->pod_data['field_id'] ), $params['fields'] );
		}

		if ( null === $params['id'] ) {
			$params['id'] = $this->id();
		}

		// @todo Needs hook doc
		$params = (array) apply_filters( 'pods_pods_export', $params, $this );

		if ( empty( $params['id'] ) ) {
			return false;
		}

		$data = pods_api()->export_pod_item( $params );

		if ( ! empty( $format ) ) {
			if ( 'json' == $format ) {
				$data = json_encode( (array) $data );
			}

			// @todo more formats
		}

		return $data;

	}

	/**
	 * Export data from all items
	 *
	 * @see   Pods_API::export
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array Data arrays of all exported pod items
	 *
	 * @since 2.3
	 */
	public function export_data( $params = null ) {

		$defaults = array(
			'fields' => null,
			'depth'  => 2,
			'params' => null
		);

		if ( empty( $params ) ) {
			$params = $defaults;
		} else {
			$params = array_merge( $defaults, (array) $params );
		}

		return pods_api()->export( $this, $params );

	}

	/**
	 * Display the pagination controls, types supported by default
	 * are simple, paginate and advanced. The base and format parameters
	 * are used only for the paginate view.
	 *
	 * @var array $params Associative array of parameters
	 *
	 * @return string Pagination HTML
	 * @since 2.0
	 * @link  http://pods.io/docs/pagination/
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
			'link_class'  => ''
		);

		$params = (object) array_merge( $defaults, $params );

		$params->total = $this->total_pages( $params->limit, $params->offset, $params->total_found );

		if ( $params->limit < 1 || $params->total_found < 1 || 1 == $params->total || $params->total_found <= $params->offset ) {
			return $this->do_hook( 'pagination', $this->do_hook( 'pagination_' . $params->type, '', $params ), $params );
		}

		$pagination = $params->type;

		if ( ! in_array( $params->type, array( 'simple', 'advanced', 'paginate', 'list' ) ) ) {
			$pagination = 'advanced';
		}

		ob_start();

		pods_view( PODS_DIR . 'ui/front/pagination/' . $pagination . '.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		$output = apply_filters( 'pods_pods_pagination_' . $params->type, $output, $params, $this );
		$output = apply_filters( 'pods_pods_pagination', $output, $params, $this );

		return $output;

	}

	/**
	 * Return a filter form for searching a Pod
	 *
	 * @var array|string $params Comma-separated list of fields or array of parameters
	 *
	 * @return string Filters HTML
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/filters/
	 */
	public function filters( $params = null ) {

		$defaults = array(
			'fields'      => $params,
			'label'       => '',
			'action'      => '',
			'search'      => '',
			'placeholder' => ''
		);

		if ( is_array( $params ) ) {
			$params = array_merge( $defaults, $params );
		} else {
			$params = $defaults;
		}

		$pod =& $this;

		$params = apply_filters( 'pods_filters_params', $params, $pod );

		$fields = $params['fields'];

		if ( null !== $fields && ! is_array( $fields ) && 0 < strlen( $fields ) ) {
			$fields = explode( ',', $fields );
		}

		$object_fields = $this->pod_data['object_fields'];

		// Force array
		if ( empty( $fields ) ) {
			$fields = array();
		} else {
			$filter_fields = $fields; // Temporary

			$fields = array();

			foreach ( $filter_fields as $k => $field ) {
				$name = $k;

				$defaults = array(
					'name' => $name
				);

				if ( ! is_array( $field ) && ! is_object( $field ) ) {
					$name = $field;

					$field = array(
						'name' => $name
					);

					$field = pods_object_field( $field );
				} elseif ( is_array( $field ) ) {
					$field = pods_object_field( $field );
				}

				$field->defaults( $defaults );

				$field['name'] = trim( $field['name'] );

				if ( pods_v( 'hidden', $field, false, null, true ) ) {
					$field['type'] = 'hidden';
				}

				if ( isset( $object_fields[$field['name']] ) ) {
					$field->defaults( $object_fields[$field['name']] );

					$fields[$field['name']] = $field;
				} elseif ( isset( $this->fields[$field['name']] ) ) {
					$field->defaults( $this->fields[$field['name']] );

					$fields[$field['name']] = $field;
				}
			}

			unset( $filter_fields ); // Cleanup
		}

		$this->filters = array_keys( $fields );

		$label = $params['label'];

		if ( strlen( $label ) < 1 ) {
			$label = __( 'Search', 'pods' );
		}

		$action = $params['action'];

		$search = trim( $params['search'] );

		if ( strlen( $search ) < 1 ) {
			$search = pods_v( $pod->search_var, 'get', '' );
		}

		$placeholder = $params['placeholder'];

		ob_start();

		pods_view( PODS_DIR . 'ui/front/filters.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		// ToDo: convert and document
		return $this->do_hook( 'filters', $output, $params );

		/**
		 * Filter the HTML output of filters()
		 *
		 * @since unknown
		 *
		 * @param string $output
		 * @param array $params Params array passed to filters().
		 * @param object|Pods $this Current Pods object.
		 */
		return apply_filters( 'pods_pods_filters', $output, $params, $this );
	}

	/**
	 * Run a helper within a Pod Page or WP Template
	 *
	 * @see      Pods_Helpers::helper
	 *
	 * @param string $helper Helper name
	 * @param string $value  Value to run the helper on
	 * @param string $name   Field name
	 *
	 * @return mixed Anything returned by the helper
	 * @since    2.0
	 */
	public function helper( $helper, $value = null, $name = null ) {

		$params = array(
			'helper'     => $helper,
			'value'      => $value,
			'name'       => $name,
			'deprecated' => false
		);

		if ( class_exists( 'Pods_Templates' ) ) {
			$params['deprecated'] = Pods_Templates::$deprecated;
		}

		if ( is_array( $helper ) ) {
			$params = array_merge( $params, $helper );
		}

		if ( class_exists( 'Pods_Helpers' ) ) {
			$value = Pods_Helpers::helper( $params, $this );
		} elseif ( function_exists( $params['helper'] ) ) {
			$value = call_user_func( $params['helper'], $value );
		} else {
			$value = apply_filters( $params['helper'], $value );
		}

		return $value;
	}

	/**
	 * Display the page template
	 *
	 * @see   Pods_Templates::template
	 *
	 * @param string $template_name The template name
	 * @param string $code          Custom template code to use instead
	 * @param bool   $deprecated    Whether to use deprecated functionality based on old function usage
	 *
	 * @return mixed Template output
	 *
	 * @since 2.0
	 * @link  http://pods.io/docs/template/
	 */
	public function template( $template_name, $code = null, $deprecated = false ) {

		$out = null;

		$obj =& $this;

		if ( ! empty( $code ) ) {
			$code = str_replace( '$this->', '$obj->', $code ); // backwards compatibility

			$code = apply_filters( 'pods_templates_pre_template', $code, $template_name, $this );
			$code = apply_filters( "pods_templates_pre_template_{$template_name}", $code, $template_name, $this );

			ob_start();

			if ( ! empty( $code ) ) {
				// Only detail templates need $this->id
				if ( empty( $this->id ) ) {
					while ( $this->fetch() ) {
						echo $this->do_magic_tags( $code );
					}
				} else {
					echo $this->do_magic_tags( $code );
				}
			}

			$out = ob_get_clean();

			$out = apply_filters( 'pods_templates_post_template', $out, $code, $template_name, $this );
			$out = apply_filters( "pods_templates_post_template_{$template_name}", $out, $code, $template_name, $this );
		} elseif ( class_exists( 'Pods_Templates' ) ) {
			$out = Pods_Templates::template( $template_name, $code, $this, $deprecated );
		} elseif ( $template_name == trim( preg_replace( '/[^a-zA-Z0-9_\-\/]/', '', $template_name ), ' /-' ) ) {
			ob_start();

			$default_templates = array(
				'pods/' . $template_name,
				'pods-' . $template_name,
				$template_name
			);

			$default_templates = apply_filters( 'pods_template_default_templates', $default_templates );

			// Only detail templates need $this->id
			if ( empty( $this->id ) ) {
				while ( $this->fetch() ) {
					pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );
				}
			} else {
				pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );
			}

			$out = ob_get_clean();

			$out = apply_filters( 'pods_templates_post_template', $out, $code, $template_name, $this );
			$out = apply_filters( "pods_templates_post_template_{$template_name}", $out, $code, $template_name, $this );
		}

		return $out;
	}

	/**
	 * Embed a form to add / edit a pod item from within your theme. Provide an array of $fields to include
	 * and override options where needed. For WP object based Pods, you can pass through the WP object
	 * field names too, such as "post_title" or "post_content" for example.
	 *
	 * @param array  $params    (optional) Fields to show on the form, defaults to all fields
	 * @param string $label     (optional) Save button label, defaults to "Save Changes"
	 * @param string $thank_you (optional) Thank you URL to send to upon success
	 *
	 * @return bool|mixed
	 * @since 2.0
	 * @link  http://pods.io/docs/form/
	 */
	public function form( $params = null, $label = null, $thank_you = null ) {

		$defaults = array(
			'fields'    => $params,
			'label'     => $label,
			'thank_you'   => $thank_you,
			'fields_only' => false
		);

		if ( is_array( $params ) ) {
			$params = array_merge( $defaults, $params );
		} else {
			$params = $defaults;
		}

		$pod =& $this;

		$params = $this->do_hook( 'form_params', $params );

		$fields = $params['fields'];

		if ( null !== $fields && ! is_array( $fields ) && 0 < strlen( $fields ) ) {
			$fields = explode( ',', $fields );
		}

		$object_fields = $this->pod_data['object_fields'];

		if ( empty( $fields ) ) {
			// Add core object fields if $fields is empty
			$fields = array_merge( $object_fields, $this->fields );
		}

		$form_fields = $fields; // Temporary

		$fields = array();

		foreach ( $form_fields as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name' => $name
			);

			if ( ! is_array( $field ) && ! is_object( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name
				);

				$field = pods_object_field( $field );
			} elseif ( is_array( $field ) ) {
				$field = pods_object_field( $field );
			}

			$field->defaults( $defaults );

			$field['name'] = trim( $field['name'] );

			$default_value = pods_v( 'default', $field );
			$value         = pods_v( 'value', $field );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			if ( isset( $object_fields[$field['name']] ) ) {
				$field->defaults( $object_fields[$field['name']] );
			} elseif ( isset( $this->fields[$field['name']] ) ) {
				$field->defaults( $this->fields[$field['name']] );
			}

			if ( pods_v( 'hidden', $field, false, null, true ) ) {
				$field['type'] = 'hidden';
			}

			$fields[$field['name']] = $field;

			if ( empty( $this->id ) && null !== $default_value ) {
				$this->row_override[$field['name']] = $default_value;
			} elseif ( ! empty( $this->id ) && null !== $value ) {
				$this->row[$field['name']] = $value;
			}
		}

		unset( $form_fields ); // Cleanup

		$fields = $this->do_hook( 'form_fields', $fields, $params );

		$label = $params['label'];

		if ( empty( $label ) ) {
			$label = __( 'Save Changes', 'pods' );
		}

		$thank_you = $params['thank_you'];
		$fields_only = $params['fields_only'];

		Pods_Form::$form_counter++;

		ob_start();

		if ( empty( $thank_you ) ) {
			$success = 'success';

			if ( 1 < Pods_Form::$form_counter ) {
				$success .= Pods_Form::$form_counter;
			}

			$thank_you = pods_query_arg( array( 'success*' => null, $success => 1 ) );

            if ( 1 == pods_v_sanitized( $success, 'get', 0 ) ) {
				$message = __( 'Form submitted successfully', 'pods' );
				/**
				 * Change the text of the message that appears on succesful form submission.
				 *
				 * @param string $message
				 *
				 * @returns string the message
				 *
				 * @since 3.0.0
				 */
				$message = apply_filters( 'pods_pod_form_success_message', $message );

				echo '<div id="message" class="pods-form-front-success">' . $message . '</div>';
			}
		}

		pods_view( PODS_DIR . 'ui/front/form.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		if ( empty( $this->id ) ) {
			$this->row_override = array();
		}

		return apply_filters( 'pods_pods_form', $output, $fields, $label, $thank_you, $this, $this->id(), $this );

	}

	/**
	 * @param array $fields (optional) Fields to show in the view, defaults to all fields
	 *
	 * @return mixed
	 * @since 2.3.10
	 */
	public function view( $fields = null ) {

		$pod =& $this;

		// Convert comma separated list of fields to an array
		if ( null !== $fields && ! is_array( $fields ) && 0 < strlen( $fields ) ) {
			$fields = explode( ',', $fields );
		}

		$object_fields = $this->pod_data['object_fields'];

		if ( empty( $fields ) ) {
			// Add core object fields if $fields is empty
			$fields = array_merge( $object_fields, $this->fields );
		}

		$view_fields = $fields; // Temporary

		$fields = array();

		foreach ( $view_fields as $name => $field ) {

			$defaults = array(
				'name' => $name
			);

			if ( ! is_array( $field ) && ! is_object( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name
				);

				$field = pods_object_field( $field );
			} elseif ( is_array( $field ) ) {
				$field = pods_object_field( $field );
			}

			$field->defaults( $defaults );

			$field['name'] = trim( $field['name'] );

			if ( empty( $field['name'] ) ) {
				$field['name'] = trim( $name );
			}

			if ( isset( $object_fields[$field['name']] ) ) {
				$field = $object_fields[$field['name']]->override( $field );
			} elseif ( isset( $this->fields[$field['name']] ) ) {
				$field = $this->fields[$field['name']]->override( $field );
			}

			if ( pods_v( 'hidden', $field, false, null, true ) || 'hidden' == $field['type'] ) {
				continue;
			} elseif ( ! Pods_Form::permission( $field['type'], $field['name'], $field, $fields, $pod, $pod->id() ) ) {
				continue;
			}

			$fields[$field['name']] = $field;
		}

		unset( $view_fields ); // Cleanup

		$output = pods_view( PODS_DIR . 'ui/front/view.php', compact( array_keys( get_defined_vars() ) ), false, 'cache', true );

		return apply_filters( 'pods_pods_view', $output, $fields, $this->id(), $this );

	}

	/**
	 * Replace magic tags with their values
	 *
	 * @param string $code The content to evaluate
	 *
	 * @return string Code with Magic Tags evaluated
	 *
	 * @since 2.0
	 */
	public function do_magic_tags( $code ) {

		return preg_replace_callback( '/({@(.*?)})/m', array( $this, 'process_magic_tags' ), $code );

	}

	/**
	 * Replace magic tags with their values
	 *
	 * @param string $tag The magic tag to process
	 *
	 * @return string Code with Magic Tags evaluated
	 *
	 * @since 2.0.2
	 */
	private function process_magic_tags( $tag ) {

		if ( is_array( $tag ) ) {
			if ( ! isset( $tag[2] ) && strlen( trim( $tag[2] ) ) < 1 ) {
				return '';
			}

			$tag = $tag[2];
		}

		$tag = trim( $tag, ' {@}' );
		$tag = explode( ',', $tag );

		if ( empty( $tag ) || ! isset( $tag[0] ) || strlen( trim( $tag[0] ) ) < 1 ) {
			return '';
		}

		foreach ( $tag as $k => $v ) {
			$tag[$k] = trim( $v );
		}

		$field_name = $tag[0];

		$helper_name = $before = $after = '';

		if ( isset( $tag[1] ) && ! empty( $tag[1] ) ) {
			$value = $this->field( $field_name );

			$helper_name = $tag[1];

			$value = $this->helper( $helper_name, $value, $field_name );
		} else {
			$value = $this->display( $field_name );
		}

		if ( isset( $tag[2] ) && ! empty( $tag[2] ) ) {
			$before = $tag[2];
		}

		if ( isset( $tag[3] ) && ! empty( $tag[3] ) ) {
			$after = $tag[3];
		}

		// @todo Needs hook docs
		$value = apply_filters( 'pods_do_magic_tags', $value, $field_name, $helper_name, $before, $after );

		if ( is_array( $value ) ) {
			$value = pods_serial_comma( $value, array( 'field' => $field_name, 'fields' => $this->fields ) );
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
	 * @param mixed $options Array or String containing Pod or Options to be used
	 * @param bool  $amend   Whether to amend the default UI options or replace entirely
	 *
	 * @return Pods_UI|null UI object or null if custom UI used
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

		if ( $this->id() != pods_v_sanitized( 'id' . $num, 'get', null, null, true ) ) {
			$this->fetch( pods_v_sanitized( 'id' . $num, 'get', null, null, true ) );
		}

		if ( ! empty( $options ) && ! $amend ) {
			$this->ui = $options;

			if ( pods_is_admin() && 1 == pods_v( 'pods_debug_ui' ) ) {
				pods_debug( $options );
			}

			return pods_ui( $this );
		} elseif ( ! empty( $options ) || 'custom' != $this->pod_data['ui_style'] ) {
			$actions_enabled = $this->pod_data['ui_actions_enabled'];

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
				'export'
			);

			if ( ! empty( $actions_enabled ) ) {
				$actions_disabled = array(
					'view' => 'view'
				);

				foreach ( $available_actions as $action ) {
					if ( ! in_array( $action, $actions_enabled ) ) {
						$actions_disabled[$action] = $action;
					}
				}
			} else {
				$actions_disabled = array(
					'duplicate' => 'duplicate',
					'view'      => 'view',
					'export'    => 'export'
				);

				if ( 1 == (int) $this->pod_data['ui_export'] ) {
					unset( $actions_disabled['export'] );
				}
			}

			if ( empty( $options ) ) {
				$author_restrict = false;

				if ( isset( $this->fields['author'] ) && 'pick' == $this->fields['author']['type'] && 'user' == $this->fields['author']['pick_object'] ) {
					$author_restrict = 'author.ID';
				}

				if ( ! pods_is_admin( array( 'pods', 'pods_content' ) ) ) {
					if ( ! current_user_can( 'pods_add_' . $this->pod ) ) {
						$actions_disabled['add'] = 'add';

						if ( 'add' == pods_v( 'action' . $num ) ) {
							$_GET['action' . $num] = 'manage';
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
				}
			}

			$_GET['action' . $num] = pods_v_sanitized( 'action' . $num, 'get', pods_v_sanitized( 'action', $options, 'manage' ) );

			$index = $this->pod_data['field_id'];
			$label = __( 'ID', 'pods' );

			if ( isset( $this->pod_data['fields'][$this->pod_data['field_index']] ) ) {
				$index = $this->pod_data['field_index'];
				$label = $this->pod_data['fields'][$this->pod_data['field_index']];
			}

			$manage = array(
				$index => $label
			);

			if ( isset( $this->pod_data['fields']['modified'] ) ) {
				$manage['modified'] = $this->pod_data['fields']['modified']['label'];
			}

			$manage_fields = $this->pod_data['ui_fields_manage'];

			if ( ! empty( $manage_fields ) ) {
				$manage_new = array();

				foreach ( $manage_fields as $manage_field ) {
					if ( isset( $this->pod_data['fields'][$manage_field] ) ) {
						$manage_new[$manage_field] = $this->pod_data['fields'][$manage_field];
					} elseif ( isset( $this->pod_data['object_fields'][$manage_field] ) ) {
						$manage_new[$manage_field] = $this->pod_data['object_fields'][$manage_field];
					} elseif ( $manage_field == $this->pod_data['field_id'] ) {
						$field = array(
							'name'  => $manage_field,
							'label' => 'ID',
							'type'  => 'number',
							'width' => '8%'
						);

						$manage_new[$manage_field] = Pods_Form::field_setup( $field, null, $field['type'] );
					}
				}

				if ( ! empty( $manage_new ) ) {
					$manage = $manage_new;
				}
			}

			// @todo Needs hook doc
			$manage = apply_filters( 'pods_admin_ui_fields_' . $this->pod, apply_filters( 'pods_admin_ui_fields', $manage, $this->pod, $this ), $this->pod, $this );

			$icon = $this->pod_data['ui_icon'];

			if ( ! empty( $icon ) ) {
				$icon = pods_image_url( $icon, '32x32' );
			}

			$filters = $this->pod_data['ui_filters'];

			if ( ! empty( $filters ) ) {
				$filters_new = array();

				foreach ( $filters as $filter_field ) {
					if ( isset( $this->pod_data['fields'][$filter_field] ) ) {
						$filters_new[$filter_field] = $this->pod_data['fields'][$filter_field];
					} elseif ( isset( $this->pod_data['object_fields'][$filter_field] ) ) {
						$filters_new[$filter_field] = $this->pod_data['object_fields'][$filter_field];
					}
				}

				$filters = $filters_new;
			}

			$ui = array(
				'fields'           => array(
					'manage'    => $manage,
					'add'       => $this->pod_data['fields'],
					'edit'      => $this->pod_data['fields'],
					'duplicate' => $this->pod_data['fields']
				),
				'icon'             => $icon,
				'actions_disabled' => $actions_disabled
			);

			if ( ! empty( $filters ) ) {
				$ui['fields']['search'] = $filters;
				$ui['filters']          = array_keys( $filters );
				$ui['filters_enhanced'] = true;
			}

			$reorder_field = $this->pod_data['ui_reorder_field'];

			if ( in_array( 'reorder', $actions_enabled ) && ! in_array( 'reorder', $actions_disabled ) && ! empty( $reorder_field ) && ( ( ! empty( $this->pod_data['object_fields'] ) && isset( $this->pod_data['object_fields'][$reorder_field] ) ) || isset( $this->pod_data['fields'][$reorder_field] ) ) ) {
				$ui['reorder']     = array( 'on' => $reorder_field );
				$ui['orderby']     = $reorder_field;
				$ui['orderby_dir'] = 'ASC';
			}

			if ( ! empty( $author_restrict ) ) {
				$ui['restrict'] = array( 'author_restrict' => $author_restrict );
			}

			if ( ! in_array( 'delete', $ui['actions_disabled'] ) ) {
				$ui['actions_bulk'] = array(
					'delete' => array(
						'label' => __( 'Delete', 'pods' )
						// callback not needed, Pods has this built-in for delete
					)
				);
			}

			$detail_url = $this->pod_data['detail_url'];

			if ( 0 < strlen( $detail_url ) ) {
				$ui['actions_custom'] = array(
					'view_url' => array(
						'label' => 'View',
						'link' => get_site_url() . '/' . $detail_url
					)
				);
			}

			// @todo Customize the Add New / Manage links to point to their correct menu items

			// @todo Needs hook doc
			$ui = apply_filters( 'pods_admin_ui_' . $this->pod, apply_filters( 'pods_admin_ui', $ui, $this->pod, $this ), $this->pod, $this );

			// Override UI options
			foreach ( $options as $option => $value ) {
				$ui[$option] = $value;
			}

			$this->ui = $ui;

			if ( pods_is_admin() && 1 == pods_v( 'pods_debug_ui' ) ) {
				pods_debug( $ui );
			}

			return pods_ui( $this );
		}

		// @todo Needs hook doc
		do_action( 'pods_admin_ui_custom', $this );
		do_action( 'pods_admin_ui_custom_' . $this->pod, $this );

		return null;

	}

	/**
	 * Handle filters / actions for the class
	 *
	 * @see   pods_do_hook
	 *
	 * @return mixed Value filtered
	 *
	 * @since 2.0
	 * @deprecated
	 */
	private function do_hook() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$name = array_shift( $args );

		return pods_do_hook( 'pods', $name, $args, $this );

	}

	/**
	 * Handle variables that have been deprecated
	 *
	 * @var $name
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function __get( $name ) {

		$name = (string) $name;

		// PodsData vars
		if ( 0 === strpos( $name, 'field_' ) && isset( $this->data->{$name} ) ) {
			return $this->data->{$name};
		}

		if ( ! isset( $this->deprecated ) ) {
			$this->deprecated = new Pods_Deprecated( $this );
		}

		$var = null;

		if ( isset( $this->deprecated->{$name} ) ) {
			if ( ! class_exists( 'Pod' ) || Pod::$deprecated_notice ) {
				pods_deprecated( "Pods->{$name}", '2.0' );
			}

			$var = $this->deprecated->{$name};
		} elseif ( ! class_exists( 'Pod' ) || Pod::$deprecated_notice ) {
			pods_deprecated( "Pods->{$name}", '2.0' );
		}

		return $var;

	}

	/**
	 * Handle methods that have been deprecated
	 *
	 * @var $name
	 * @var $args
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function __call( $name, $args ) {
		$name = (string) $name;

		// select > find alias
		if ( 'select' == $name ) {
			return call_user_func_array( array( $this, 'find' ), $args );
		}

		if ( ! isset( $this->deprecated ) ) {
			$this->deprecated = new Pods_Deprecated( $this );
		}

		if ( method_exists( $this->deprecated, $name ) ) {
			return call_user_func_array( array( $this->deprecated, $name ), $args );
		} elseif ( ! class_exists( 'Pod' ) || Pod::$deprecated_notice ) {
			pods_deprecated( "Pods::{$name}", '2.0' );
		}

		return null;

	}
}