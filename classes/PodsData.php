<?php

use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;

/**
 * The Pods DB related functionality needed for building queries and traversal.
 *
 * @package Pods
 *
 * @property null|string       $pod           The Pod name.
 * @property null|string       $pod_id        The Pod id.
 * @property null|array        $fields        The list of Pod fields.
 * @property null|array        $object_fields The list of Pod object fields.
 * @property null|string       $detail_page   The detail_url (if set), alias of detail_url.
 * @property null|string       $detail_url    The detail_url (if set).
 * @property null|string       $select        The select data.
 * @property null|string       $table         The table data.
 * @property null|string       $field_id      The field_id data.
 * @property null|string       $field_index   The field_index data.
 * @property null|string       $field_slug    The field_slug data.
 * @property null|string       $join          The join data.
 * @property null|array|string $where         The where data.
 * @property null|array|string $where_default The where_default data.
 * @property null|string       $orderby       The orderby data.
 * @property null|string       $type          The type data.
 * @property null|string       $storage       The storage data.
 */
class PodsData {

	/**
	 * @var PodsData
	 */
	public static $instance = null;

	/**
	 * @var string
	 */
	protected static $prefix = 'pods_';

	/**
	 * @var array
	 */
	protected static $field_types = array();

	/**
	 * @var bool
	 */
	public static $display_errors = true;

	/**
	 * @var bool
	 */
	public $fetch_full = true;

	/**
	 * @var PodsAPI
	 */
	public $api = null;

	/**
	 * @var Pod
	 */
	public $pod_data = null;

	/**
	 * The table information to fallback on when a Pod is not defined in $this->pod_data.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	public $table_info = null;

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var array
	 */
	public $aliases = array();

	/**
	 * @var int
	 */
	public $row_number = - 1;

	/**
	 * @var
	 */
	public $rows;

	/**
	 * @var
	 */
	public $row;

	/**
	 * @var
	 */
	public $insert_id;

	/**
	 * @var
	 */
	public $total;

	/**
	 * @var
	 */
	public $total_found;

	/**
	 * @var bool
	 */
	public $total_found_calculated;

	/**
	 * @var string
	 */
	public $page_var = 'pg';

	/**
	 * @var int
	 */
	public $page = 1;

	/**
	 * @var int
	 */
	public $limit = 15;

	/**
	 * @var int
	 */
	public $offset = 0;

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
	 * int | text | text_like
	 *
	 * @var string
	 */
	public $search_mode = 'int';

	/**
	 * @var string
	 */
	public $search_query = '';

	/**
	 * @var array
	 */
	public $search_fields = array();

	/**
	 * @var string
	 */
	public $search_where = array();

	/**
	 * @var array
	 */
	public $filters = array();

	/**
	 * @var array
	 */
	public $params = array();

	/**
	 * Holds Traversal information about Pods
	 *
	 * @var array
	 */
	public $traversal = array();

	/**
	 * Holds custom Traversals to be included
	 *
	 * @var array
	 */
	public $traverse = array();

	/**
	 * Last select() query SQL
	 *
	 * @var string
	 */
	public $sql = false;

	/**
	 * Last total sql
	 *
	 * @var string
	 */
	public $total_sql = false;

	/**
	 * Singleton handling for a basic pods_data() request
	 *
	 * @param string|null $pod    Pod name.
	 * @param int|string  $id     Pod Item ID.
	 * @param bool        $strict If true throws an error if a pod is not found.
	 *
	 * @return PodsData|false
	 *
	 * @throws Exception
	 *
	 * @since 2.3.5
	 */
	public static function init( $pod = null, $id = null, $strict = true ) {

		if ( ! in_array( $pod, array( null, false ), true ) || ! in_array( $id, array( null, 0 ), true ) ) {
			$object = new PodsData( $pod, $id, $strict );

			if ( empty( $object->pod_data ) && true === $strict ) {
				return pods_error( 'Pod not found', $object );
			}

			return $object;
		}

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsData();
		} else {
			$vars = get_class_vars( __CLASS__ );

			foreach ( $vars as $var => $default ) {
				if ( 'api' === $var ) {
					continue;
				}

				self::$instance->{$var} = $default;
			}
		}

		return self::$instance;
	}

	/**
	 * Data Abstraction Class for Pods
	 *
	 * @param string|null $pod    Pod name.
	 * @param int|string  $id     Pod Item ID.
	 * @param bool        $strict (optional) If set to true, we will not attempt to auto-setup the pod based on the object.
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $pod = null, $id = 0, $strict = false ) {

		global $wpdb;

		$this->api = pods_api();
		$this->api->display_errors =& self::$display_errors;

		if ( empty( $pod ) ) {
			return;
		}

		if ( $pod instanceof Pod ) {
			$this->pod_data = clone $pod;
		} elseif ( $pod instanceof Pods ) {
			$this->pod_data = clone $pod->pod_data;
		} else {
			$this->pod_data = $this->api->load_pod( [
				'name'       => $pod,
				// Auto-setup only if not in strict mode.
				'auto_setup' => ! $strict,
			], false );
		}

		// Set up page variable.
		if ( pods_strict( false ) ) {
			$this->page       = 1;
			$this->pagination = false;
			$this->search     = false;
		} else {
			// Get the page variable.
			$this->page = pods_v( $this->page_var, 'get', 1, true );

			if ( ! empty( $this->page ) ) {
				$this->page = max( 1, pods_absint( $this->page ) );
			}
		}

		// Set default pagination handling to on/off.
		if ( defined( 'PODS_GLOBAL_POD_PAGINATION' ) ) {
			if ( ! PODS_GLOBAL_POD_PAGINATION ) {
				$this->page       = 1;
				$this->pagination = false;
			} else {
				$this->pagination = true;
			}
		}

		// Set default search to on/off.
		if ( defined( 'PODS_GLOBAL_POD_SEARCH' ) ) {
			if ( PODS_GLOBAL_POD_SEARCH ) {
				$this->search = true;
			} else {
				$this->search = false;
			}
		}

		// Set default search mode.
		$allowed_search_modes = array( 'int', 'text', 'text_like' );

		if ( defined( 'PODS_GLOBAL_POD_SEARCH_MODE' ) && in_array( PODS_GLOBAL_POD_SEARCH_MODE, $allowed_search_modes, true ) ) {
			$this->search_mode = PODS_GLOBAL_POD_SEARCH_MODE;
		}

		if ( $this->pod_data && 'settings' === $this->pod_data['type'] ) {
			$this->id = $this->pod_data['name'];

			$this->fetch( $this->id );
		} elseif ( null !== $id && ! is_array( $id ) && ! is_object( $id ) ) {
			$this->id = $id;

			$this->fetch( $this->id );
		}
	}

	/**
	 * Handle tables like they are Pods (for traversal in select/build)
	 *
	 * @param array|string $table
	 * @param string       $object
	 */
	public function table( $table, $object = '' ) {
		global $wpdb;

		if ( is_string( $table ) ) {
			$object_type = '';

			if ( $wpdb->posts === $table ) {
				$object_type = 'post_type';
			} elseif ( $wpdb->terms === $table ) {
				$object_type = 'taxonomy';
			} elseif ( $wpdb->users === $table ) {
				$object_type = 'user';
			} elseif ( $wpdb->comments === $table ) {
				$object_type = 'comment';
			} elseif ( $wpdb->options === $table ) {
				$object_type = 'settings';
			}

			if ( ! empty( $object_type ) ) {
				$table = $this->api->get_table_info( $object_type, $object );
			}
		}

		// Check if we received a pod object itself.
		if ( $table instanceof Pod ) {
			$table = [
				'pod' => $table,
			];
		} elseif ( $table instanceof Pods ) {
			$table = [
				'pod' => $table->pod_data,
			];
		}

		// No pod set.
		if ( empty( $table['pod'] )  ) {
			// No pod name to try to use.
			if ( ! empty( $table['name'] ) ) {
				$pod_data = $this->api->load_pod( [
					'name'       => $table['name'],
					'auto_setup' => true,
				] );

				// No pod data found.
				if ( $pod_data ) {
					$table['pod'] = $pod_data;
				}
			}
		}

		// Check for pod object.
		if ( ! empty( $table['pod'] ) && $table['pod'] instanceof Pod ) {
			$this->pod_data = $table['pod'];
		} else {
			$this->table_info = $table;
		}
	}

	/**
	 * Insert an item, eventually mapping to WPDB::insert
	 *
	 * @param string $table  Table name.
	 * @param array  $data   Data to insert (in column => value pairs) Both $data columns and $data values should be
	 *                       "raw" (neither should be SQL escaped).
	 * @param array  $format (optional) An array of formats to be mapped to each of the value in $data.
	 *
	 * @return int|bool The ID of the item
	 *
	 * @uses  wpdb::insert
	 *
	 * @since 2.0.0
	 */
	public function insert( $table, $data, $format = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( '' === $table || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		if ( empty( $format ) ) {
			$format = array();

			foreach ( $data as $field ) {
				if ( isset( self::$field_types[ $field ] ) ) {
					$format[] = self::$field_types[ $field ];
				} elseif ( isset( $wpdb->field_types[ $field ] ) ) {
					$format[] = $wpdb->field_types[ $field ];
				} else {
					break;
				}
			}
		}

		[ $table, $data, $format ] = self::do_hook( 'insert', array( $table, $data, $format ), $this );

		$result          = $wpdb->insert( $table, $data, $format );
		$this->insert_id = $wpdb->insert_id;

		if ( false !== $result ) {
			return $this->insert_id;
		}

		return false;
	}

	/**
	 * @static
	 *
	 * Insert into a table, if unique key exists just update values.
	 *
	 * Data must be a key value pair array, keys act as table rows.
	 *
	 * Returns the prepared query from wpdb or false for errors
	 *
	 * @param string $table   Name of the table to update.
	 * @param array  $data    column => value pairs.
	 * @param array  $formats For $wpdb->prepare, uses sprintf formatting.
	 *
	 * @return mixed Sanitized query string
	 *
	 * @uses  wpdb::prepare
	 *
	 * @since 2.0.0
	 */
	public static function insert_on_duplicate( $table, $data, $formats = array() ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$columns = array_keys( $data );

		$update = array();
		$values = array();

		foreach ( $columns as $column ) {
			$update[] = "`{$column}` = VALUES( `{$column}` )";
			$values[] = '%s';
		}

		if ( empty( $formats ) ) {
			$formats = $values;
		}

		$columns_data = implode( '`, `', $columns );
		$formats      = implode( ', ', $formats );
		$update       = implode( ', ', $update );

		$sql = "INSERT INTO `{$table}` ( `{$columns_data}` ) VALUES ( {$formats} ) ON DUPLICATE KEY UPDATE {$update}";

		return $wpdb->prepare( $sql, $data );
	}

	/**
	 * Update an item, eventually mapping to WPDB::update
	 *
	 * @param string $table        Table name.
	 * @param array  $data         Data to update (in column => value pairs) Both $data columns and $data values.
	 *                             should be "raw" (neither should be SQL escaped).
	 * @param array  $where        A named array of WHERE clauses (in column => value pairs) Multiple clauses will be.
	 *                             joined with ANDs. Both $where columns and $where values should be "raw".
	 * @param array  $format       (optional) An array of formats to be mapped to each of the values in $data.
	 * @param array  $where_format (optional) An array of formats to be mapped to each of the values in $where.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public function update( $table, $data, $where, $format = null, $where_format = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( '' === $table || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		if ( empty( $format ) ) {
			$format = array();

			foreach ( $data as $field ) {
				if ( isset( self::$field_types[ $field ] ) ) {
					$form = self::$field_types[ $field ];
				} elseif ( isset( $wpdb->field_types[ $field ] ) ) {
					$form = $wpdb->field_types[ $field ];
				} else {
					$form = '%s';
				}

				$format[] = $form;
			}
		}

		if ( empty( $where_format ) ) {
			$where_format = array();

			foreach ( (array) array_keys( $where ) as $field ) {
				if ( isset( self::$field_types[ $field ] ) ) {
					$form = self::$field_types[ $field ];
				} elseif ( isset( $wpdb->field_types[ $field ] ) ) {
					$form = $wpdb->field_types[ $field ];
				} else {
					$form = '%s';
				}

				$where_format[] = $form;
			}
		}

		[ $table, $data, $where, $format, $where_format ] = self::do_hook(
			'update', array(
				$table,
				$data,
				$where,
				$format,
				$where_format,
			), $this
		);

		$result = $wpdb->update( $table, $data, $where, $format, $where_format );

		if ( false !== $result ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete an item
	 *
	 * @param string $table        Table name.
	 * @param array  $where        A named array of WHERE clauses (in column => value pairs) Multiple clauses will be.
	 *                             joined with ANDs. Both $where columns and $where values should be "raw".
	 * @param array  $where_format (optional) An array of formats to be mapped to each of the values in $where.
	 *
	 * @return array|bool|mixed|null|void
	 *
	 * @uses  PodsData::query
	 * @uses  PodsData::prepare
	 *
	 * @since 2.0.0
	 */
	public function delete( $table, $where, $where_format = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( '' === $table || empty( $where ) || ! is_array( $where ) ) {
			return false;
		}

		$wheres        = array();
		$where_format  = (array) $where_format;
		$where_formats = $where_format;

		foreach ( (array) array_keys( $where ) as $field ) {
			if ( ! empty( $where_format ) ) {
				$form = array_shift( $where_formats );
				$form = $form ? $form : $where_format[0];
			} elseif ( isset( self::$field_types[ $field ] ) ) {
				$form = self::$field_types[ $field ];
			} elseif ( isset( $wpdb->field_types[ $field ] ) ) {
				$form = $wpdb->field_types[ $field ];
			} else {
				$form = '%s';
			}

			$wheres[] = "`{$field}` = {$form}";
		}

		$sql = "DELETE FROM `$table` WHERE " . implode( ' AND ', $wheres );

		[ $sql, $where ] = self::do_hook(
			'delete', array(
				$sql,
				array_values( $where ),
			), $table, $where, $where_format, $wheres, $this
		);

		return $this->query( self::prepare( $sql, $where ) );
	}

	/**
	 * Select items, eventually building dynamic query
	 *
	 * @param array $params
	 *
	 * @return array|bool|mixed
	 * @since 2.0.0
	 */
	public function select( $params ) {

		global $wpdb;

		$cache_key = false;
		$results   = false;

		/**
		 * Filter select parameters before the query
		 *
		 * @param array|object    $params
		 * @param PodsData|object $this The current PodsData class instance.
		 *
		 * @since unknown
		 */
		$params = apply_filters( 'pods_data_pre_select_params', $params, $this );

		// Debug purposes.
		if ( 1 === (int) pods_v( 'pods_debug_params', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
			pods_debug( $params );
		}

		// Get from cache if enabled.
		if ( null !== pods_v( 'expires', $params, null, true ) ) {
			$cache_key = md5( (string) $this->pod . serialize( $params ) );

			$results = pods_view_get( $cache_key, pods_v( 'cache_mode', $params, 'cache', true ), 'pods_data_select' );

			if ( empty( $results ) ) {
				$results = false;
			}
		}

		if ( empty( $results ) ) {
			// Build.
			$this->sql = $this->build( $params );

			// Debug purposes.
			if ( ( 1 === (int) pods_v( 'pods_debug_sql', 'get', 0 ) || 1 === (int) pods_v( 'pods_debug_sql_all', 'get', 0 ) ) && pods_is_admin( array( 'pods' ) ) ) {
				if ( function_exists( 'codecept_debug' ) ) {
					pods_debug( $this->get_sql() );
				} else {
					echo '<textarea cols="100" rows="24">' . esc_textarea( $this->get_sql() ) . '</textarea>';
				}
			}

			if ( empty( $this->sql ) ) {
				return array();
			}

			// Get Data.
			$results = pods_query( $this->sql, $this );

			// Cache if enabled.
			if ( false !== $cache_key ) {
				pods_view_set( $cache_key, $results, pods_v( 'expires', $params, 0, false ), pods_v( 'cache_mode', $params, 'cache', true ), 'pods_data_select' );
			}
		}//end if

		/**
		 * Filter results of Pods Query
		 *
		 * @param array           $results
		 * @param array|object    $params
		 * @param PodsData|object $this The current PodsData class instance.
		 *
		 * @since unknown
		 */
		$results = apply_filters( 'pods_data_select', $results, $params, $this );

		$this->rows = $results;

		$this->row_number = - 1;
		$this->row        = null;

		$this->total_found_calculated = false;

		$this->total = 0;

		if ( ! empty( $this->rows ) ) {
			$this->total = count( (array) $this->rows );
		}

		/**
		 * Filters whether the total_found should be calculated right away or not.
		 *
		 * @param boolean  $auto_calculate_total_found Whether to auto calculate total_found.
		 * @param array    $params                     Select parameters.
		 * @param PodsData $this                       The current PodsData instance.
		 *
		 * @since 2.7.11
		 */
		if ( apply_filters( 'pods_data_auto_calculate_total_found', false, $params, $this ) ) {
			// Run the calculation logic.
			$this->calculate_totals();
		}

		return $this->rows;
	}

	/**
	 * Calculate total found.
	 */
	public function calculate_totals() {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		// Set totals.
		if ( false !== $this->total_sql ) {
			$total = @current( $wpdb->get_col( $this->get_sql( $this->total_sql ) ) );
		} else {
			$total = @current( $wpdb->get_col( 'SELECT FOUND_ROWS()' ) );
		}

		$total                        = self::do_hook( 'select_total', $total, $this );
		$this->total_found            = 0;
		$this->total_found_calculated = true;

		if ( is_numeric( $total ) ) {
			$this->total_found = $total;
		}
	}

	/**
	 * Build/Rewrite dynamic SQL and handle search/filter/sort
	 *
	 * @param array $params
	 *
	 * @return bool|mixed|string
	 * @since 2.0.0
	 */
	public function build( $params ) {

		$simple_tableless_objects = PodsForm::simple_tableless_objects();
		$file_field_types         = PodsForm::file_field_types();
		$pick_field_types         = [
			'pick',
			'comment',
			'taxonomy',
		];

		$defaults = array(
			'select'              => '*',
			'calc_rows'           => false,
			'distinct'            => true,
			'table'               => null,
			'join'                => null,
			'where'               => null,
			'where_default'       => null,
			'groupby'             => null,
			'having'              => null,
			'orderby'             => null,
			'limit'               => - 1,
			'offset'              => null,

			'id'                  => null,
			'index'               => null,

			'page'                => 1,
			'pagination'          => $this->pagination,
			'search'              => $this->search,
			'search_query'        => null,
			'search_mode'         => null,
			'search_across'       => false,
			'search_across_picks' => false,
			'search_across_files' => false,
			'filters'             => array(),

			'fields'              => array(),
			'object_fields'       => array(),
			'pod_table_prefix'    => null,

			'traverse'            => array(),

			'sql'                 => null,

			'strict'              => false,
		);

		$params = (object) array_merge( $defaults, (array) $params );

		if ( is_string( $params->sql ) && 0 < strlen( $params->sql ) ) {
			return $params->sql;
		}

		$pod = false;

		if ( $this->pod_data instanceof Pod ) {
			$pod = $this->pod_data;
		} elseif ( $this->table_info ) {
			$pod = $this->table_info;
		}

		// Validate.
		$params->page = pods_absint( $params->page );

		$params->pagination = (boolean) $params->pagination;

		if ( 0 === $params->page || ! $params->pagination ) {
			$params->page = 1;
		}

		$params->limit = (int) $params->limit;

		if ( 0 === $params->limit ) {
			$params->limit = - 1;
		}

		$this->limit = $params->limit;

		$offset = ( $params->limit * ( $params->page - 1 ) );

		if ( 0 < (int) $params->offset ) {
			$params->offset += $offset;
		} else {
			$params->offset = $offset;
		}

		if ( ! $params->pagination || - 1 === $params->limit ) {
			$params->page   = 1;
			$params->offset = 0;
		}

		$merge_object_fields = false;

		if (
			$pod
			&& (
				empty( $params->fields )
				|| ! is_array( $params->fields )
			)
		) {
			$params->fields = $this->fields;

			if ( null === $params->fields ) {
				$params->fields = [];
			}

			$merge_object_fields = true;
		}

		if (
			$pod
			&& (
				empty( $params->object_fields )
				|| ! is_array( $params->object_fields )
			)
		) {
			$params->object_fields = $this->object_fields;

			if ( null === $params->object_fields ) {
				$params->object_fields = [];
			}

			if ( $merge_object_fields ) {
				if ( $pod instanceof Pod ) {
					$params->fields = $pod->get_all_fields();
				} elseif ( $params->object_fields ) {
					if ( $params->fields ) {
						$params->fields = pods_config_merge_fields( $params->fields, $params->object_fields );
					} else {
						$params->fields = $params->object_fields;
					}
				}
			}
		}

		if ( empty( $params->filters ) && $params->search ) {
			$params->filters = array_keys( $params->fields );
		} elseif ( empty( $params->filters ) ) {
			$params->filters = array();
		}

		if ( empty( $params->index ) ) {
			$params->index = $this->field_index;
		}

		if ( empty( $params->id ) ) {
			$params->id = $this->field_id;
		}

		if ( $pod && empty( $params->table ) ) {
			$params->table = $this->table;
		}

		if ( empty( $params->pod_table_prefix ) ) {
			$params->pod_table_prefix = 't';
		}

		if (
			'table' === $this->storage
			&& ! in_array( $this->type, [
					'pod',
					'table',
				], true )
		) {
			$params->pod_table_prefix = 'd';
		}

		$params->meta_fields = false;

		$is_pod_meta_storage = 'meta' === $this->storage;

		if (
			(
				$is_pod_meta_storage
				|| 'none' === $this->storage
			)
			&& ! in_array( $this->type, [
				'pod',
				'table',
			], true )
		) {
			$params->meta_fields = true;
		}

		if ( empty( $params->table ) ) {
			return false;
		}

		if ( false === strpos( $params->table, '(' ) && false === strpos( $params->table, '`' ) ) {
			$params->table = '`' . $params->table . '`';
		}

		if ( ! empty( $params->join ) ) {
			$params->join = array_merge( (array) $this->join, (array) $params->join );
		} elseif ( false === $params->strict ) {
			$params->join = $this->join;
		}

		$params->where_defaulted = false;

		if ( empty( $params->where_default ) && false !== $params->where_default ) {
			$params->where_default = $this->where_default;
		}

		if ( false === $params->strict ) {
			// Set default where.
			if ( ! empty( $params->where_default ) && empty( $params->where ) ) {
				$params->where = array_values( (array) $params->where_default );

				$params->where_defaulted = true;
			}

			if ( ! empty( $this->where ) ) {
				if ( is_array( $params->where ) && isset( $params->where['relation'] ) && 'OR' === strtoupper( $params->where['relation'] ) ) {
					$params->where = array_merge( array( $params->where ), array_values( (array) $this->where ) );
				} else {
					$params->where = array_merge( (array) $params->where, array_values( (array) $this->where ) );
				}
			}
		}

		// Allow where array ( 'field' => 'value' ) and WP_Query meta_query syntax.
		if ( ! empty( $params->where ) ) {
			$params->where = $this->query_fields( (array) $params->where, $pod, $params );
		}

		if ( empty( $params->where ) ) {
			$params->where = array();
		} else {
			$params->where = (array) $params->where;
		}

		// Allow having array ( 'field' => 'value' ) and WP_Query meta_query syntax.
		if ( ! empty( $params->having ) ) {
			$params->having = $this->query_fields( (array) $params->having, $pod, $params );
		}

		if ( empty( $params->having ) ) {
			$params->having = array();
		} else {
			$params->having = (array) $params->having;
		}

		// If orderby is passed exactly as an empty array or strict mode, let's assume we don't want to use it at all.
		$strict_orderby = [] === $params->orderby || $params->strict;

		if ( ! empty( $params->orderby ) ) {
			if ( $is_pod_meta_storage && is_array( $params->orderby ) ) {
				foreach ( $params->orderby as $i => $orderby ) {
					if ( strpos( $orderby, '.meta_value_num' ) ) {
						$params->orderby[ $i ] = 'CAST(' . str_replace( '.meta_value_num', '.meta_value', $orderby ) . ' AS DECIMAL)';
					} elseif ( strpos( $orderby, '.meta_value_date' ) ) {
						$params->orderby[ $i ] = 'CAST(' . str_replace( '.meta_value_date', '.meta_value', $orderby ) . ' AS DATE)';
					}
				}
			}

			$params->orderby = (array) $params->orderby;
		} else {
			$params->orderby = array();
		}

		if ( ! $strict_orderby && ! empty( $this->orderby ) ) {
			$params->orderby = array_merge( $params->orderby, (array) $this->orderby );
		}

		if ( ! empty( $params->traverse ) ) {
			$this->traverse = $params->traverse;
		}

		$allowed_search_modes = array( 'int', 'text', 'text_like' );

		if ( ! empty( $params->search_mode ) && in_array( $params->search_mode, $allowed_search_modes, true ) ) {
			$this->search_mode = $params->search_mode;
		}

		$params->search = (boolean) $params->search;

		if ( 1 === (int) pods_v( 'pods_debug_params_all', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
			pods_debug( $params );
		}

		$params->field_table_alias = 't';

		// Get Aliases for future reference.
		$selectsfound = '';

		if ( ! empty( $params->select ) ) {
			if ( is_array( $params->select ) ) {
				$selectsfound = implode( ', ', $params->select );
			} else {
				$selectsfound = $params->select;
			}
		}

		// Pull Aliases from SQL query too.
		if ( null !== $params->sql ) {
			$temp_sql = ' ' . trim( str_replace( array( "\n", "\r" ), ' ', $params->sql ) );
			$temp_sql = preg_replace(
				array(
					'/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
					'/\sSELECT\s/i',
				), array(
					' SELECT ',
					' SELECT SQL_CALC_FOUND_ROWS ',
				), $temp_sql
			);
			preg_match( '/\sSELECT SQL_CALC_FOUND_ROWS\s(.*)\sFROM/i', $temp_sql, $selectmatches );
			if ( isset( $selectmatches[1] ) && ! empty( $selectmatches[1] ) && false !== stripos( $selectmatches[1], ' AS ' ) ) {
				$selectsfound .= ( ! empty( $selectsfound ) ? ', ' : '' ) . $selectmatches[1];
			}
		}

		// Build Alias list.
		$this->aliases = array();

		if ( ! empty( $selectsfound ) && false !== stripos( $selectsfound, ' AS ' ) ) {
			$theselects = array_filter( explode( ', ', $selectsfound ) );

			if ( empty( $theselects ) ) {
				$theselects = array_filter( explode( ',', $selectsfound ) );
			}

			foreach ( $theselects as $selected ) {
				$selected = trim( $selected );

				if ( '' === $selected ) {
					continue;
				}

				$selectfield = explode( ' AS ', str_replace( ' as ', ' AS ', $selected ) );

				if ( 2 === count( $selectfield ) ) {
					$field                   = trim( trim( $selectfield[1] ), '`' );
					$real_field              = trim( trim( $selectfield[0] ), '`' );
					$this->aliases[ $field ] = $real_field;
				}
			}
		}//end if

		// Search.
		if ( ! empty( $params->search ) && ! empty( $params->fields ) ) {
			if ( is_string( $params->search_query ) && 0 < strlen( $params->search_query ) ) {
				$where            = [];
				$having           = [];
				$fields_to_search = [];

				$excluded_field_types_from_search = [
					'date',
					'time',
					'datetime',
					'number',
					'decimal',
					'currency',
					'phone',
					'password',
					'boolean',
					'comment',
					'taxonomy',
				];

				if ( false !== $params->search_across ) {
					// Search all fields.
					$fields_to_search = $params->fields;
				} elseif ( ! empty( $params->index ) ) {
					$index_field = null;

					// Search just the index field if we can find it.
					if ( isset( $params->fields[ $params->index ] ) ) {
						$index_field = $params->fields[ $params->index ];
					} elseif ( isset( $params->object_fields[ $params->index ] ) ) {
						$index_field = $params->object_fields[ $params->index ];
					}

					if ( $index_field ) {
						$fields_to_search = [
							$params->index => $index_field,
						];
					}
				}

				foreach ( $fields_to_search as $key => $field ) {
					$is_field_object = $field instanceof Field;

					if ( is_array( $field ) || $is_field_object ) {
						$attributes = $field;
						$field      = pods_v( 'name', $field, $key, true );
					} else {
						$attributes = [
							'type'    => '',
							'options' => [],
						];
					}

					if ( isset( $attributes['search'] ) && ! $attributes['search'] ) {
						continue;
					}

					// Exclude certain field types from search.
					if ( in_array( $attributes['type'], $excluded_field_types_from_search, true ) ) {
						continue;
					}

					$db_field_name = '`' . $field . '`';

					$is_object_field = (
						isset( $params->object_fields[ $field ] )
						|| $attributes instanceof Object_Field
					);
					$is_custom_field = (
						! $is_object_field
						&& ! isset( $params->fields[ $field ] )
					);

					$pick_object = pods_v( $attributes['type'] . '_object', $attributes );
					$pick_val    = pods_v( $attributes['type'] . '_val', $attributes );

					if ( in_array( $attributes['type'], $pick_field_types, true ) && ! in_array( $pick_object, $simple_tableless_objects, true ) ) {
						// Search relationship fields.
						if ( false !== $params->search_across && false === $params->search_across_picks ) {
							// Skip if we are searching but not searching across relationship fields.
							continue;
						} elseif ( empty( $attributes['table_info'] ) ) {
							$attributes['table_info'] = $this->api->get_table_info( $pick_object, $pick_val );
						}

						// Check if we have index column information about the related table.
						if ( empty( $attributes['table_info']['field_index'] ) ) {
							continue;
						}

						$db_field_name .= '.`' . $attributes['table_info']['field_index'] . '`';
					} elseif ( in_array( $attributes['type'], $file_field_types, true ) ) {
						// Search file fields.
						if ( false !== $params->search_across && false === $params->search_across_files ) {
							// Skip if we are searching but not searching across file fields.
							continue;
						}

						$db_field_name .= '.`post_title`';
					} elseif ( $is_custom_field ) {
						// Search custom fields (they are not a defined field or an object field).
						if ( $params->meta_fields ) {
							// If meta is enabled, this must be a meta field.
							$db_field_name .= '.`meta_value`';
						} else {
							// Maybe this is just a field we don't know about on the table.
							$db_field_name = '`' . $params->pod_table_prefix . '`.' . $db_field_name;
						}
					} elseif ( ! $is_object_field && $is_pod_meta_storage ) {
						// Search meta fields.
						$db_field_name .= '.`meta_value`';
					} else {
						// Search object fields.
						$db_field_name = '`t`.' . $db_field_name;
					}//end if

					if ( isset( $this->aliases[ $field ] ) ) {
						$db_field_name = '`' . $this->aliases[ $field ] . '`';
					}

					if ( ! empty( $attributes['real_name'] ) ) {
						$db_field_name = $attributes['real_name'];
					}

					$filter_clause = "{$db_field_name} LIKE '%" . pods_sanitize_like( $params->search_query ) . "%'";

					if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
						$having[] = $filter_clause;
					} else {
						$where[] = $filter_clause;
					}
				}//end foreach

				if ( ! empty( $where ) ) {
					$params->where[] = '( ' . implode( ' OR ', $where ) . ' )';
				}

				if ( ! empty( $having ) ) {
					$params->having[] = '( ' . implode( ' OR ', $having ) . ' )';
				}
			}//end if

			// Filter.
			foreach ( $params->filters as $filter ) {
				$where  = array();
				$having = array();

				// Check if we have a matching field.
				$attributes = pods_v( $filter, $params->fields, null );

				// If we do not have a matching field, check for a matching object field.
				if ( ! $attributes ) {
					$attributes = pods_v( $filter, $params->object_fields, null );
				}

				if ( null === $attributes ) {
					// Field not found.
					continue;
				}

				$field = pods_v( 'name', $attributes, $filter, true );

				$db_field_params = array_merge( [
					'field_name'               => $field,
					'field'                    => $attributes,
					'is_pod_meta_storage'      => $is_pod_meta_storage,
					'simple_tableless_objects' => $simple_tableless_objects,
					'file_field_types'         => $file_field_types,
					'aliases'                  => $this->aliases,
				], (array) $params );

				$filterfield = self::get_db_field( $db_field_params );

				if ( 'pick' === $attributes['type'] ) {
					$filter_value = pods_v( 'filter_' . $field );

					if ( ! is_array( $filter_value ) ) {
						$filter_value = (array) $filter_value;
					}

					foreach ( $filter_value as $filter_v ) {
						if ( in_array( pods_v( 'pick_object', $attributes ), $simple_tableless_objects, true ) ) {
							if ( '' === $filter_v ) {
								continue;
							}

							if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
								$having[] = "( {$filterfield} = '" . pods_sanitize( $filter_v ) . "'" . " OR {$filterfield} LIKE '%\"" . pods_sanitize_like( $filter_v ) . "\"%' )";
							} else {
								$where[] = "( {$filterfield} = '" . pods_sanitize( $filter_v ) . "'" . " OR {$filterfield} LIKE '%\"" . pods_sanitize_like( $filter_v ) . "\"%' )";
							}
						} else {
							$filter_v = (int) $filter_v;

							if ( empty( $filter_v ) ) {
								continue;
							}

							$db_field_params['use_field_id'] = true;

							$filterfield_id = $this->get_db_field( $db_field_params );

							if ( empty( $filterfield_id ) ) {
								continue;
							}

							if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
								$having[] = "{$filterfield_id} = {$filter_v}";
							} else {
								$where[] = "{$filterfield_id} = {$filter_v}";
							}
						}//end if
					}//end foreach
				} elseif ( in_array(
					$attributes['type'], array(
						'date',
						'datetime',
					), true
				) ) {
					$start_value = pods_v( 'filter_' . $field . '_start', 'get', false );
					$end_value   = pods_v( 'filter_' . $field . '_end', 'get', false );

					if ( empty( $start_value ) && empty( $end_value ) ) {
						continue;
					}

					if ( ! empty( $start_value ) ) {
						$start = date_i18n( 'Y-m-d', strtotime( $start_value ) ) . ( 'datetime' === $attributes['type'] ? ' 00:00:00' : '' );
					} else {
						$start = date_i18n( 'Y-m-d' ) . ( 'datetime' === $attributes['type'] ) ? ' 00:00:00' : '';
					}

					if ( ! empty( $end_value ) ) {
						$end = date_i18n( 'Y-m-d', strtotime( $end_value ) ) . ( 'datetime' === $attributes['type'] ? ' 23:59:59' : '' );
					} else {
						$end = date_i18n( 'Y-m-d' ) . ( 'datetime' === $attributes['type'] ) ? ' 23:59:59' : '';
					}

					if ( isset( $attributes['date_ongoing'] ) && true === $attributes['date_ongoing'] ) {
						$date_ongoing = '`' . $attributes['date_ongoing'] . '`';

						if ( isset( $this->aliases[ $date_ongoing ] ) ) {
							$date_ongoing = '`' . $this->aliases[ $date_ongoing ] . '`';
						}

						if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
							$having[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
						} else {
							$where[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
						}
					} else {
						if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
							$having[] = "({$filterfield} BETWEEN '$start' AND '$end')";
						} else {
							$where[] = "({$filterfield} BETWEEN '$start' AND '$end')";
						}
					}
				} else {
					$filter_value = (string) pods_v( 'filter_' . $field, 'get', '' );

					if ( '' === $filter_value ) {
						continue;
					}

					if ( isset( $attributes['group_related'] ) && false !== $attributes['group_related'] ) {
						$having[] = "{$filterfield} LIKE '%" . pods_sanitize_like( $filter_value ) . "%'";
					} else {
						$where[] = "{$filterfield} LIKE '%" . pods_sanitize_like( $filter_value ) . "%'";
					}
				}//end if

				if ( ! empty( $where ) ) {
					$params->where[] = implode( ' AND ', $where );
				}

				if ( ! empty( $having ) ) {
					$params->having[] = implode( ' AND ', $having );
				}
			}//end foreach
		}//end if

		// Traverse the Rabbit Hole.
		if ( $this->pod_data || $this->table_info ) {
			$haystack = implode( ' ', (array) $params->select ) . ' ' . implode( ' ', (array) $params->where ) . ' ' . implode( ' ', (array) $params->groupby ) . ' ' . implode( ' ', (array) $params->having ) . ' ' . implode( ' ', (array) $params->orderby );
			$haystack = preg_replace( '/\s/', ' ', $haystack );
			$haystack = preg_replace( '/\w\(/', ' ', $haystack );
			$haystack = str_replace( array( '(', ')', '  ', '\\\'', '\\"' ), ' ', $haystack );

			preg_match_all( '/`?[\w\-]+`?(?:\\.`?[\w\-]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER );

			$found    = (array) @current( $found );
			$find     = array();
			$replace  = array();
			$traverse = array();

			foreach ( $found as $key => $value ) {
				$value = str_replace( '`', '', $value );
				$value = explode( '.', $value );

				$last_value = array_pop( $value );
				$dot        = $last_value;

				if ( 't' === $value[0] ) {
					continue;
				} elseif ( array_key_exists( $value[0], $params->join ) ) {
					// Don't traverse for tables we are already going to join.
					continue;
				} elseif ( 1 === count( $value ) && '' === preg_replace( '/[0-9]*/', '', $value[0] ) && '' === preg_replace( '/[0-9]*/', '', $last_value ) ) {
					continue;
				}

				$found_value = str_replace( '`', '', $found[ $key ] );
				$found_value = '([`]{1}|\b)' . str_replace( '.', '[`]*\.[`]*', $found_value ) . '([`]{1}|\b)';
				$found_value = '/' . $found_value . '(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/';

				if ( in_array( $found_value, $find, true ) ) {
					continue;
				}

				$find[ $key ] = $found_value;

				if ( '*' !== $dot ) {
					$dot = '`' . $dot . '`';
				}

				$replace[ $key ] = '`' . implode( '_', $value ) . '`.' . $dot;

				$value[] = $last_value;

				if ( ! in_array( $value, $traverse, true ) ) {
					$traverse[ $key ] = $value;
				}
			}//end foreach

			if ( ! empty( $this->traverse ) ) {
				foreach ( (array) $this->traverse as $key => $traverse_field ) {
					$traverse_field      = str_replace( '`', '', $traverse_field );
					$already_found = false;

					foreach ( $traverse_field as $traversal ) {
						if ( is_array( $traversal ) ) {
							$traversal = implode( '.', $traversal );
						}

						if ( $traversal === $traverse_field ) {
							$already_found = true;
							break;
						}
					}

					if ( ! $already_found ) {
						$traverse[ 'traverse_' . $key ] = explode( '.', $traverse_field );
					}
				}
			}//end if

			$joins = array();

			$pre_traverse_args = [
				'find'     => $find,
				'replace'  => $replace,
				'traverse' => $traverse,
				'params'   => $params,
			];

			/**
			 * Allow filtering the pre-traverse arguments that will be used to build the query.
			 *
			 * @since 2.8.14
			 *
			 * @param array    $pre_traverse_args The pre-traverse arguments.
			 * @param PodsData $pods_data         The PodsData object.
			 */
			$pre_traverse_args = (array) apply_filters( 'pods_data_build_pre_traverse_args', $pre_traverse_args, $this );

			if ( isset( $pre_traverse_args['find'] ) ) {
				$find = $pre_traverse_args['find'];
			}

			if ( isset( $pre_traverse_args['replace'] ) ) {
				$replace = $pre_traverse_args['replace'];
			}

			if ( isset( $pre_traverse_args['traverse'] ) ) {
				$traverse = $pre_traverse_args['traverse'];
			}

			if ( isset( $pre_traverse_args['params'] ) ) {
				$params = $pre_traverse_args['params'];
			}

			if ( ! empty( $find ) ) {
				// See: "#3294 OrderBy Failing on PHP7"  Non-zero array keys.
				// here in PHP 7 cause odd behavior so just strip the keys.
				$find    = array_values( $find );
				$replace = array_values( $replace );

				if ( $params->select ) {
					$params->select = preg_replace( $find, $replace, $params->select );
				}

				if ( $params->where ) {
					$params->where = preg_replace( $find, $replace, $params->where );
				}

				if ( $params->groupby ) {
					$params->groupby = preg_replace( $find, $replace, $params->groupby );
				}

				if ( $params->having ) {
					$params->having = preg_replace( $find, $replace, $params->having );
				}

				if ( $params->orderby ) {
					$params->orderby = preg_replace( $find, $replace, $params->orderby );
				}

				if ( ! empty( $traverse ) ) {
					$joins = $this->traverse( $traverse, $params->fields, $params );
				} elseif ( false !== $params->search ) {
					$joins = $this->traverse( null, $params->fields, $params );
				}
			}
		}//end if

		// Traversal Search.
		if ( ! empty( $params->search ) && ! empty( $this->search_where ) ) {
			$params->where = array_merge( (array) $this->search_where, $params->where );
		}

		if ( ! empty( $params->join ) && ! empty( $joins ) ) {
			$params->join = array_merge( $joins, (array) $params->join );
		} elseif ( ! empty( $joins ) ) {
			$params->join = $joins;
		}

		// Build.
		if ( null === $params->sql ) {
			$sql = '
                SELECT
                ' . ( $params->calc_rows ? 'SQL_CALC_FOUND_ROWS' : '' ) . '
                ' . ( $params->distinct ? 'DISTINCT' : '' ) . '
                ' . ( ! empty( $params->select ) ? ( is_array( $params->select ) ? implode( ', ', $params->select ) : $params->select ) : '*' ) . "
                FROM {$params->table} AS `t`
                " . ( ! empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . '
                ' . ( ! empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND ', $params->where ) : $params->where ) : '' ) . '
                ' . ( ! empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . '
                ' . ( ! empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND  ', $params->having ) : $params->having ) : '' ) . '
                ' . ( ! empty( $params->orderby ) ? 'ORDER BY ' . ( is_array( $params->orderby ) ? implode( ', ', $params->orderby ) : $params->orderby ) : '' ) . '
                ' . ( ( 0 < $params->page && 0 < $params->limit ) ? 'LIMIT ' . $params->offset . ', ' . ( $params->limit ) : '' ) . '
            ';

			if ( ! $params->calc_rows ) {
				// Handle COUNT() SELECT.
				$total_sql_select = 'COUNT( ' . ( $params->distinct ? 'DISTINCT `t`.`' . $params->id . '`' : '*' ) . ' )';

				// If 'having' is set, we have to select all so it has access to anything it needs.
				if ( ! empty( $params->having ) ) {
					$total_sql_select .= ', ' . ( ! empty( $params->select ) ? ( is_array( $params->select ) ? implode( ', ', $params->select ) : $params->select ) : '*' );
				}

				$this->total_sql = "
					SELECT {$total_sql_select}
					FROM {$params->table} AS `t`
					" . ( ! empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . '
                    ' . ( ! empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND  ', $params->where ) : $params->where ) : '' ) . '
                    ' . ( ! empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . '
                    ' . ( ! empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND  ', $params->having ) : $params->having ) : '' ) . '
                ';
			}
		} else {
			$sql = ' ' . trim( str_replace( array( "\n", "\r" ), ' ', $params->sql ) );
			$sql = preg_replace(
				array(
					'/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
					'/\sSELECT\s/i',
				), array(
					' SELECT ',
					' SELECT SQL_CALC_FOUND_ROWS ',
				), $sql
			);

			// Insert variables based on existing statements.
			if ( false === stripos( $sql, '%%SELECT%%' ) ) {
				$sql = preg_replace( '/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i', ' SELECT SQL_CALC_FOUND_ROWS %%SELECT%% ', $sql );
			}
			if ( false === stripos( $sql, '%%WHERE%%' ) ) {
				$sql = preg_replace( '/\sWHERE\s(?!.*\sWHERE\s)/i', ' WHERE %%WHERE%% ', $sql );
			}
			if ( false === stripos( $sql, '%%GROUPBY%%' ) ) {
				$sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' GROUP BY %%GROUPBY%% ', $sql );
			}
			if ( false === stripos( $sql, '%%HAVING%%' ) ) {
				$sql = preg_replace( '/\sHAVING\s(?!.*\sHAVING\s)/i', ' HAVING %%HAVING%% ', $sql );
			}
			if ( false === stripos( $sql, '%%ORDERBY%%' ) ) {
				$sql = preg_replace( '/\sORDER BY\s(?!.*\sORDER BY\s)/i', ' ORDER BY %%ORDERBY%% ', $sql );
			}

			// Insert variables based on other existing statements.
			if ( false === stripos( $sql, '%%JOIN%%' ) ) {
				if ( false !== stripos( $sql, ' WHERE ' ) ) {
					$sql = preg_replace( '/\sWHERE\s(?!.*\sWHERE\s)/i', ' %%JOIN%% WHERE ', $sql );
				} elseif ( false !== stripos( $sql, ' GROUP BY ' ) ) {
					$sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' %%WHERE%% GROUP BY ', $sql );
				} elseif ( false !== stripos( $sql, ' ORDER BY ' ) ) {
					$sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%WHERE%% ORDER BY ', $sql );
				} else {
					$sql .= ' %%JOIN%% ';
				}
			}
			if ( false === stripos( $sql, '%%WHERE%%' ) ) {
				if ( false !== stripos( $sql, ' GROUP BY ' ) ) {
					$sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' %%WHERE%% GROUP BY ', $sql );
				} elseif ( false !== stripos( $sql, ' ORDER BY ' ) ) {
					$sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%WHERE%% ORDER BY ', $sql );
				} else {
					$sql .= ' %%WHERE%% ';
				}
			}
			if ( false === stripos( $sql, '%%GROUPBY%%' ) ) {
				if ( false !== stripos( $sql, ' HAVING ' ) ) {
					$sql = preg_replace( '/\sHAVING\s(?!.*\sHAVING\s)/i', ' %%GROUPBY%% HAVING ', $sql );
				} elseif ( false !== stripos( $sql, ' ORDER BY ' ) ) {
					$sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%GROUPBY%% ORDER BY ', $sql );
				} else {
					$sql .= ' %%GROUPBY%% ';
				}
			}
			if ( false === stripos( $sql, '%%HAVING%%' ) ) {
				if ( false !== stripos( $sql, ' ORDER BY ' ) ) {
					$sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%HAVING%% ORDER BY ', $sql );
				} else {
					$sql .= ' %%HAVING%% ';
				}
			}
			if ( false === stripos( $sql, '%%ORDERBY%%' ) ) {
				$sql .= ' %%ORDERBY%% ';
			}
			if ( false === stripos( $sql, '%%LIMIT%%' ) ) {
				$sql .= ' %%LIMIT%% ';
			}

			// Replace variables.
			if ( 0 < strlen( (string) $params->select ) ) {
				if ( false === stripos( $sql, '%%SELECT%% FROM ' ) ) {
					$sql = str_ireplace( '%%SELECT%%', $params->select . ', ', $sql );
				} else {
					$sql = str_ireplace( '%%SELECT%%', $params->select, $sql );
				}
			}
			if ( 0 < strlen( (string) $params->join ) ) {
				$sql = str_ireplace( '%%JOIN%%', $params->join, $sql );
			}
			if ( 0 < strlen( (string) $params->where ) ) {
				if ( false !== stripos( $sql, ' WHERE ' ) ) {
					if ( false !== stripos( $sql, ' WHERE %%WHERE%% ' ) ) {
						$sql = str_ireplace( '%%WHERE%%', $params->where . ' AND ', $sql );
					} else {
						$sql = str_ireplace( '%%WHERE%%', ' AND ' . $params->where, $sql );
					}
				} else {
					$sql = str_ireplace( '%%WHERE%%', ' WHERE ' . $params->where, $sql );
				}
			}
			if ( 0 < strlen( (string) $params->groupby ) ) {
				if ( false !== stripos( $sql, ' GROUP BY ' ) ) {
					if ( false !== stripos( $sql, ' GROUP BY %%GROUPBY%% ' ) ) {
						$sql = str_ireplace( '%%GROUPBY%%', $params->groupby . ', ', $sql );
					} else {
						$sql = str_ireplace( '%%GROUPBY%%', ', ' . $params->groupby, $sql );
					}
				} else {
					$sql = str_ireplace( '%%GROUPBY%%', ' GROUP BY ' . $params->groupby, $sql );
				}
			}
			if ( 0 < strlen( (string) $params->having ) && false !== stripos( $sql, ' GROUP BY ' ) ) {
				if ( false !== stripos( $sql, ' HAVING ' ) ) {
					if ( false !== stripos( $sql, ' HAVING %%HAVING%% ' ) ) {
						$sql = str_ireplace( '%%HAVING%%', $params->having . ' AND ', $sql );
					} else {
						$sql = str_ireplace( '%%HAVING%%', ' AND ' . $params->having, $sql );
					}
				} else {
					$sql = str_ireplace( '%%HAVING%%', ' HAVING ' . $params->having, $sql );
				}
			}
			if ( 0 < strlen( (string) $params->orderby ) ) {
				if ( false !== stripos( $sql, ' ORDER BY ' ) ) {
					if ( false !== stripos( $sql, ' ORDER BY %%ORDERBY%% ' ) ) {
						$sql = str_ireplace( '%%ORDERBY%%', $params->groupby . ', ', $sql );
					} else {
						$sql = str_ireplace( '%%ORDERBY%%', ', ' . $params->groupby, $sql );
					}
				} else {
					$sql = str_ireplace( '%%ORDERBY%%', ' ORDER BY ' . $params->groupby, $sql );
				}
			}
			if ( 0 < $params->page && 0 < $params->limit ) {
				$start = ( $params->page - 1 ) * $params->limit;
				$end   = $start + $params->limit;
				$sql  .= 'LIMIT ' . (int) $start . ', ' . (int) $end;
			}

			// Clear any unused variables.
			$sql = str_ireplace(
				array(
					'%%SELECT%%',
					'%%JOIN%%',
					'%%WHERE%%',
					'%%GROUPBY%%',
					'%%HAVING%%',
					'%%ORDERBY%%',
					'%%LIMIT%%',
				), '', $sql
			);
			$sql = str_replace( array( '``', '`' ), array( '  ', ' ' ), $sql );
		}//end if

		return $sql;
	}

	/**
	 * Fetch the total row count returned
	 *
	 * @return int Number of rows returned by select()
	 * @since 2.0.0
	 */
	public function total() {

		return (int) $this->total;
	}

	/**
	 * Fetch the total row count total
	 *
	 * @return int Number of rows found by select()
	 * @since 2.0.0
	 */
	public function total_found() {

		if ( false === $this->total_found_calculated ) {
			$this->calculate_totals();
		}

		return (int) $this->total_found;
	}

	/**
	 * Fetch the zebra state
	 *
	 * @return bool Zebra state
	 * @since 1.12
	 * @see   PodsData::nth
	 */
	public function zebra() {

		return $this->nth( 'odd' );
		// Odd numbers.
	}

	/**
	 * Fetch the nth state
	 *
	 * @param int|string $nth The $nth to match on the PodsData::row_number.
	 *
	 * @return bool Whether $nth matches
	 * @since 2.3.0
	 */
	public function nth( $nth ) {

		if ( empty( $nth ) ) {
			$nth = 2;
		}

		$offset   = 0;
		$negative = false;

		if ( 'even' === $nth ) {
			$nth = 2;
		} elseif ( 'odd' === $nth ) {
			$negative = true;
			$nth      = 2;
		} elseif ( false !== strpos( $nth, '+' ) ) {
			$nth = explode( '+', $nth );

			if ( isset( $nth[1] ) ) {
				$offset += (int) trim( $nth[1] );
			}

			$nth = (int) trim( $nth[0], ' n' );
		} elseif ( false !== strpos( $nth, '-' ) ) {
			$nth = explode( '-', $nth );

			if ( isset( $nth[1] ) ) {
				$offset -= (int) trim( $nth[1] );
			}

			$nth = (int) trim( $nth[0], ' n' );
		}//end if

		$nth    = (int) $nth;
		$offset = (int) $offset;

		if ( 0 === ( ( $this->row_number % $nth ) + $offset ) ) {
			return ( $negative ? false : true );
		}

		return ( $negative ? true : false );
	}

	/**
	 * Fetch the current position in the loop (starting at 1)
	 *
	 * @return int Current row number (+1)
	 * @since 2.3.0
	 */
	public function position() {

		return $this->row_number + 1;
	}

	/**
	 * Create a Table
	 *
	 * @param string  $table         Table name.
	 * @param string  $fields
	 * @param boolean $if_not_exists Check if the table exists.
	 *
	 * @return array|bool|mixed|null|void
	 *
	 * @uses  PodsData::query
	 *
	 * @since 2.0.0
	 */
	public static function table_create( $table, $fields, $if_not_exists = false ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$sql = 'CREATE TABLE';

		if ( true === $if_not_exists ) {
			$sql .= ' IF NOT EXISTS';
		}

		$pods_prefix = self::get_pods_prefix();

		$sql .= " `{$pods_prefix}{$table}` ({$fields})";

		if ( ! empty( $wpdb->charset ) ) {
			$sql .= " DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$sql .= " COLLATE {$wpdb->collate}";
		}

		return self::query( $sql );
	}

	/**
	 * Alter a Table
	 *
	 * @param string $table Table name.
	 * @param string $changes
	 *
	 * @return array|bool|mixed|null|void
	 *
	 * @uses  PodsData::query
	 *
	 * @since 2.0.0
	 */
	public static function table_alter( $table, $changes ) {

		$pods_prefix = self::get_pods_prefix();

		$sql = "ALTER TABLE `{$pods_prefix}{$table}` {$changes}";

		return self::query( $sql );
	}

	/**
	 * Truncate a Table
	 *
	 * @param string $table Table name.
	 *
	 * @return array|bool|mixed|null|void
	 *
	 * @uses  PodsData::query
	 *
	 * @since 2.0.0
	 */
	public static function table_truncate( $table ) {

		$pods_prefix = self::get_pods_prefix();

		$sql = "TRUNCATE TABLE `{$pods_prefix}{$table}`";

		return self::query( $sql );
	}

	/**
	 * Drop a Table
	 *
	 * @param string $table Table name.
	 *
	 * @uses  PodsData::query
	 *
	 * @return array|bool|mixed|null|void
	 *
	 * @uses  PodsData::query
	 *
	 * @since 2.0.0
	 */
	public static function table_drop( $table ) {

		$pods_prefix = self::get_pods_prefix();

		$sql = "DROP TABLE `{$pods_prefix}{$table}`";

		return self::query( $sql );
	}

	/**
	 * Reorder Items
	 *
	 * @param string $table Table name.
	 * @param string $weight_field
	 * @param string $id_field
	 * @param array  $ids
	 *
	 * @return bool
	 *
	 * @uses  PodsData::update
	 *
	 * @since 2.0.0
	 */
	public function reorder( $table, $weight_field, $id_field, $ids ) {

		$success = false;
		$ids     = (array) $ids;

		[ $table, $weight_field, $id_field, $ids ] = self::do_hook(
			'reorder', array(
				$table,
				$weight_field,
				$id_field,
				$ids,
			), $this
		);

		if ( ! empty( $ids ) ) {
			$success = true;

			foreach ( $ids as $weight => $id ) {
				$updated = $this->update( $table, array( $weight_field => $weight ), array( $id_field => $id ), array( '%d' ), array( '%d' ) );

				if ( false === $updated ) {
					$success = false;
				}
			}
		}

		return $success;
	}

	/**
	 * Fetch a new row for the current pod_data
	 *
	 * @param int  $row          Row number to fetch.
	 * @param bool $explicit_set Whether to set explicitly (use false when in loop).
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function fetch( $row = null, $explicit_set = true ) {

		global $wpdb;

		if ( null === $row ) {
			$explicit_set = false;
		}

		$already_cached = false;
		$id             = $row;

		$tableless_field_types = PodsForm::tableless_field_types();

		$is_settings_pod = null;

		if ( null === $row ) {
			$this->row_number ++;

			$this->row = false;

			if ( isset( $this->rows[ $this->row_number ] ) ) {
				$this->row = $this->rows[ $this->row_number ];

				if ( is_object( $this->row ) ) {
					$this->row = get_object_vars( $this->row );
				}

				$current_row_id = false;

				if ( $this->pod_data && 'settings' === $this->pod_data->get_type() ) {
					$current_row_id = $this->pod_data->get_name();

					$is_settings_pod = true;
				} else {
					$is_settings_pod = false;

					$current_row_id = pods_v( $this->field_id, $this->row );
				}

				if ( ! in_array( $current_row_id, [ '', '0', 0, null, false ], true ) ) {
					$row = $current_row_id;
				}
			}//end if
		}//end if

		/**
		 * Allow filtering whether to fetch the full row.
		 *
		 * @since 2.8.9
		 *
		 * @param bool     $fetch_full Whether to fetch the full row.
		 * @param PodsData $pods_data  The PodsData object.
		 */
		$fetch_full = (bool) apply_filters( 'pods_data_fetch_full', $this->fetch_full, $this );

		if ( $fetch_full && null === $is_settings_pod ) {
			$is_settings_pod = $this->pod_data && 'settings' === $this->pod_data['type'];
		}

		if ( $fetch_full && ( null !== $row || $is_settings_pod ) ) {
			if ( $explicit_set ) {
				$this->row_number = - 1;
			}

			$mode = 'id';
			$id   = pods_absint( $row );

			if ( $is_settings_pod ) {
				$mode = 'slug';
				$id   = $this->pod_data->get_name();
			}

			if (
				! $is_settings_pod
				&& null !== $row
				&& (
					! is_numeric( $row )
					|| 0 === strpos( $row, '0' )
					|| (string) $row !== (string) preg_replace( '/[^0-9]/', '', $row )
				)
			) {
				$mode = 'slug';
				$id   = $row;
			}

			$row = false;

			if ( ! empty( $this->pod ) ) {
				$row = pods_cache_get( $id, 'pods_items_' . $this->pod );

				if ( is_array( $row ) ) {
					$already_cached = true;
				} else {
					$row = false;
				}
			}

			$current_row_id = false;
			$get_table_data = false;

			$old_row = $this->row;

			$pod_type = $this->type;

			if ( $already_cached ) {
				$this->row = $row;
			} elseif ( in_array(
				$pod_type, array(
					'post_type',
					'media',
				), true
			) ) {
				if ( 'post_type' === $pod_type ) {
					$post_type = $this->pod;

					if ( $this->pod_data && ! empty( $this->pod_data['object'] ) ) {
						$post_type = $this->pod_data['object'];
					}
				} else {
					$post_type = 'attachment';
				}

				$this->row = [];

				if ( 'id' === $mode ) {
					$this->row = get_post( $id, ARRAY_A );

					if ( is_array( $this->row ) && $this->row['post_type'] !== $post_type ) {
						$this->row = false;
					}
				} else {
					$args = [
						'post_type'      => $post_type,
						'name'           => $id,
						'posts_per_page' => 5,
					];

					$find = get_posts( $args );

					if ( ! empty( $find ) ) {
						$this->row = get_object_vars( reset( $find ) );
					}
				}

				if ( empty( $this->row ) || is_wp_error( $this->row ) ) {
					$this->row = false;
				} else {
					$current_row_id = (int) $this->row['ID'];
				}

				$get_table_data = true;
			} elseif ( 'taxonomy' === $pod_type ) {
				$taxonomy = $this->pod;

				if ( $this->pod_data && ! empty( $this->pod_data['object'] ) ) {
					$taxonomy = $this->pod_data['object'];
				}

				// Taxonomies are registered during init, so they aren't available before then.
				if ( ! did_action( 'init' ) ) {
					// hackaround :(
					if ( 'id' === $mode ) {
						$term_where = 't.term_id = %d';
					} else {
						$term_where = 't.slug = %s';
					}

					$filter = 'raw';
					$term   = $id;

					$_term = wp_cache_get( $term, $taxonomy );

					if ( 'id' !== $mode || ! $_term ) {
						$_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND {$term_where} LIMIT 1", $taxonomy, $term ) );

						if ( $_term ) {
							wp_cache_add( $term, $_term, $taxonomy );
						}
					}

					$_term = apply_filters( 'get_term', $_term, $taxonomy );
					$_term = apply_filters( "get_$taxonomy", $_term, $taxonomy );
					$_term = sanitize_term( $_term, $taxonomy, $filter );

					$this->row = [];

					if ( is_object( $_term ) ) {
						$this->row = get_object_vars( $_term );
					}
				} elseif ( 'id' === $mode ) {
					$this->row = get_term( $id, $taxonomy, ARRAY_A );
				} else {
					$this->row = get_term_by( 'slug', $id, $taxonomy, ARRAY_A );
				}//end if

				if ( empty( $this->row ) || is_wp_error( $this->row ) ) {
					$this->row = false;
				} else {
					$current_row_id = (int) $this->row['term_id'];
				}

				$get_table_data = true;
			} elseif ( 'user' === $pod_type ) {
				if ( 'id' === $mode ) {
					$this->row = get_userdata( $id );
				} else {
					$this->row = get_user_by( 'slug', $id );
				}

				if ( empty( $this->row ) || is_wp_error( $this->row ) ) {
					$this->row = false;
				} else {
					// Get other vars.
					$roles   = $this->row->roles;
					$caps    = $this->row->caps;
					$allcaps = $this->row->allcaps;

					$this->row = get_object_vars( $this->row->data );

					// Set other vars.
					$this->row['roles']   = $roles;
					$this->row['caps']    = $caps;
					$this->row['allcaps'] = $allcaps;

					unset( $this->row['user_pass'] );

					$current_row_id = (int) $this->row['ID'];
				}

				$get_table_data = true;
			} elseif ( 'comment' === $pod_type ) {
				$this->row = get_comment( $id, ARRAY_A );

				// No slug handling here.
				if ( empty( $this->row ) || is_wp_error( $this->row ) ) {
					$this->row = false;
				} else {
					$current_row_id = (int) $this->row['comment_ID'];
				}

				$get_table_data = true;
			} elseif ( 'settings' === $pod_type ) {
				$this->row = [];

				if ( empty( $this->fields ) || ! $this->pod_data ) {
					$this->row = false;
				} else {
					/** @var Field $field */
					foreach ( $this->fields as $field ) {
						if (
							! in_array( $field['type'], $tableless_field_types, true )
							|| $field->is_simple_relationship()
						) {
							$this->row[ $field['name'] ] = get_option( $this->pod_data->get_name() . '_' . $field['name'], null );
						}
					}

					// Force the pod name as the ID.
					$this->id               = $this->pod_data->get_name();
					$this->row['option_id'] = $this->id;
				}
			} else {
				$params = array(
					'table'   => $this->table,
					'where'   => "`t`.`{$this->field_id}` = " . (int) $id,
					'orderby' => "`t`.`{$this->field_id}` DESC",
					'page'    => 1,
					'limit'   => 1,
					'search'  => false,
				);

				if ( 'slug' === $mode && ! empty( $this->field_slug ) ) {
					$id              = pods_sanitize( $id );
					$params['where'] = "`t`.`{$this->field_slug}` = '{$id}'";
				}

				$new_data = new PodsData();

				$this->row = $new_data->select( $params );

				if ( empty( $this->row ) ) {
					$this->row = false;
				} else {
					$current_row = (array) $this->row;
					$this->row   = get_object_vars( (object) @current( $current_row ) );
				}
			}//end if

			if ( ! $explicit_set && ! empty( $this->row ) && is_array( $this->row ) && ! empty( $old_row ) ) {
				$this->row = array_merge( $old_row, $this->row );
			}

			if ( false !== $get_table_data && is_numeric( $current_row_id ) && $this->pod_data && 'table' === $this->pod_data['storage'] ) {
				$params = array(
					'table'   => self::get_pods_prefix(),
					'where'   => "`t`.`id` = {$current_row_id}",
					'orderby' => '`t`.`id` DESC',
					'page'    => 1,
					'limit'   => 1,
					'search'  => false,
					'strict'  => true,
				);

				$table_name = $this->pod;

				if ( $this->pod_data && ! empty( $this->pod_data['object'] ) ) {
					$table_name = $this->pod_data['object'];
				}

				$params['table'] .= $table_name;

				$new_data = new PodsData();

				$row = $new_data->select( $params );

				if ( ! empty( $row ) ) {
					$current_row = (array) $row;
					$row         = get_object_vars( (object) @current( $current_row ) );

					if ( is_array( $this->row ) && ! empty( $this->row ) ) {
						$this->row = array_merge( $this->row, $row );
					} else {
						$this->row = $row;
					}
				}
			}//end if

			if ( ! empty( $this->pod ) && ! $already_cached ) {
				pods_cache_set( $id, $this->row, 'pods_items_' . $this->pod, WEEK_IN_SECONDS );
			}
		}//end if

		$this->row = apply_filters( 'pods_data_fetch', $this->row, $id, $this->row_number, $this );

		// Set the ID if the row was found.
		if ( $explicit_set && $this->row ) {
			$this->id = $id;
		}

		return $this->row;
	}

	/**
	 * Reset the current data
	 *
	 * @param int $row Row number to reset to.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function reset( $row = null ) {

		$row = pods_absint( $row );

		$this->row = false;

		if ( isset( $this->rows[ $row ] ) ) {
			$this->row = $this->rows[ $row ];

			if ( is_object( $this->row ) ) {
				$this->row = get_object_vars( $this->row );
			}
		}

		if ( empty( $row ) ) {
			$this->row_number = - 1;
		} else {
			$this->row_number = $row - 1;
		}

		return $this->row;
	}

	/**
	 * @static
	 *
	 * Do a query on the database
	 *
	 * @param string|array $sql              The SQL to execute.
	 * @param string       $error            Error to throw on problems.
	 * @param null         $results_error    (optional).
	 * @param null         $no_results_error (optional).
	 *
	 * @return array|bool|mixed|null|void Result of the query
	 *
	 * @since 2.0.0
	 */
	public static function query( $sql, $error = 'Database Error', $results_error = null, $no_results_error = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( isset( $wpdb->show_errors ) ) {
			self::$display_errors = (bool) $wpdb->show_errors;
		}

		$display_errors = self::$display_errors;

		if ( is_object( $error ) ) {
			if ( isset( $error->display_errors ) && false === $error->display_errors ) {
				$display_errors = false;
			}

			$error = 'Database Error';
		} elseif ( is_bool( $error ) ) {
			$display_errors = $error;

			if ( false !== $error ) {
				$error = 'Database Error';
			}
		}

		if ( pods_is_admin() && 1 === (int) pods_v( 'pods_debug_backtrace' ) ) {
			ob_start();
			echo '<pre>';
			var_dump( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 11 ) );
			echo '</pre>';
			$error = ob_get_clean() . $error;
		}

		$params = (object) array(
			'sql'              => $sql,
			'error'            => $error,
			'results_error'    => $results_error,
			'no_results_error' => $no_results_error,
			'display_errors'   => $display_errors,
		);

		// Handle Preparations of Values (sprintf format).
		if ( is_array( $sql ) ) {
			if ( isset( $sql[0] ) && 1 < count( $sql ) ) {
				$sql = array_values( $sql );

				if ( 2 === count( $sql ) ) {
					if ( ! is_array( $sql[1] ) ) {
						$sql[1] = [
							$sql[1],
						];
					}

					$params->sql = self::prepare( $sql[0], $sql[1] );
				} else {
					$sql_query = array_shift( $sql );
					$prepare   = $sql;

					$params->sql = self::prepare( $sql_query, $prepare );
				}
			} else {
				$params = (object) array_merge( get_object_vars( $params ), $sql );
			}

			if ( 1 === (int) pods_v( 'pods_debug_sql_all', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
				echo '<textarea cols="100" rows="24">' . esc_textarea( pods_data()->get_sql( $params->sql ) ) . '</textarea>';
			}

		}//end if

		$params->sql = trim( $params->sql );

		// Run Query.
		$params->sql = apply_filters( 'pods_data_query', $params->sql, $params );

		$wpdb_show_errors = null;

		// Maybe disable wpdb errors.
		if ( false === $params->error && ! empty( $wpdb->show_errors ) ) {
			$wpdb_show_errors = false;

			$wpdb->show_errors( false );
		}

		$result = $wpdb->query( $params->sql );

		// Maybe show wpdb errors.
		if ( $wpdb_show_errors ) {
			$wpdb->show_errors( true );
		}

		$result = apply_filters( 'pods_data_query_result', $result, $params );

		if ( false === $result && ! empty( $params->error ) && ! empty( $wpdb->last_error ) ) {
			return pods_error( "{$params->error}; SQL: {$params->sql}; Response: {$wpdb->last_error}", $params->display_errors );
		}

		if ( 'INSERT' === strtoupper( substr( $params->sql, 0, 6 ) ) || 'REPLACE' === strtoupper( substr( $params->sql, 0, 7 ) ) ) {
			$result = $wpdb->insert_id;
		} elseif ( preg_match( '/^[\s\r\n\(]*SELECT/', strtoupper( $params->sql ) ) ) {
			$result = (array) $wpdb->last_result;

			if ( ! empty( $result ) && ! empty( $params->results_error ) ) {
				return pods_error( $params->results_error, $params->display_errors );
			} elseif ( empty( $result ) && ! empty( $params->no_results_error ) ) {
				return pods_error( $params->no_results_error, $params->display_errors );
			}
		}

		return $result;
	}

	/**
	 * Gets all tables in the WP database, optionally exclude WP core
	 * tables, and/or Pods table by settings the parameters to false.
	 *
	 * @param boolean $wp_core
	 * @param boolean $pods_tables restrict Pods 2x tables.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_tables( $wp_core = true, $pods_tables = true ) {

		global $wpdb;

		$core_wp_tables = array(
			$wpdb->options,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->links,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
		);

		$showTables = $wpdb->get_results( 'SHOW TABLES in ' . DB_NAME, ARRAY_A );

		$finalTables = array();

		foreach ( $showTables as $table ) {
			if ( ! $pods_tables && 0 === ( strpos( $table[0], rtrim( self::get_pods_prefix(), '_' ) ) ) ) {
				// don't include pods tables.
				continue;
			} elseif ( ! $wp_core && in_array( $table[0], $core_wp_tables, true ) ) {
				continue;
			} else {
				$finalTables[] = $table[0];
			}
		}

		return $finalTables;
	}

	/**
	 * Gets column information from a table
	 *
	 * @param string $table Table Name.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_table_columns( $table ) {

		global $wpdb;

		self::query( "SHOW COLUMNS FROM `{$table}` " );

		$table_columns = $wpdb->last_result;

		$table_cols_and_types = array();

		foreach ( $table_columns as $table_col ) {
			// Get only the type, not the attributes.
			if ( false === strpos( $table_col->Type, '(' ) ) {
				$modified_type = $table_col->Type;
			} else {
				$modified_type = substr( $table_col->Type, 0, ( strpos( $table_col->Type, '(' ) ) );
			}

			$table_cols_and_types[ $table_col->Field ] = $modified_type;
		}

		return $table_cols_and_types;
	}

	/**
	 * Gets column data information from a table
	 *
	 * @param string $column_name Column name.
	 * @param string $table       Table name.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function get_column_data( $column_name, $table ) {

		global $wpdb;

		$column_data = $wpdb->get_results( 'DESCRIBE ' . $table, ARRAY_A );

		foreach ( $column_data as $single_column ) {
			if ( $column_name === $single_column['Field'] ) {
				return $single_column;
			}
		}

		return $column_data;
	}

	/**
	 * Prepare values for the DB
	 *
	 * @param string $sql  SQL to prepare.
	 * @param array  $data Data to add to the sql prepare statement.
	 *
	 * @return bool|null|string
	 *
	 * @since 2.0.0
	 */
	public static function prepare( $sql, $data ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;
		[ $sql, $data ] = apply_filters( 'pods_data_prepare', array( $sql, $data ) );

		return $wpdb->prepare( $sql, $data );
	}

	/**
	 * Get the string to use in a query for WHERE/HAVING, uses WP_Query meta_query arguments
	 *
	 * @param array     $fields Array of field matches for querying.
	 * @param array|Pod $pod    Related Pod.
	 * @param object    $params Parameters passed from select().
	 *
	 * @return string|null Query string for WHERE/HAVING
	 *
	 * @static
	 * @since 2.3.0
	 */
	public static function query_fields( $fields, $pod = null, &$params = null ) {

		$query_fields = array();

		if ( ! is_object( $params ) ) {
			$params = new stdClass();
		}

		if ( ! isset( $params->query_field_level ) || 0 === $params->query_field_level ) {
			$params->query_fields       = array();
			$params->query_field_syntax = false;
			$params->query_field_level  = 1;

			if ( ! isset( $params->where_default ) ) {
				$params->where_default = array();
			}

			if ( ! isset( $params->where_defaulted ) ) {
				$params->where_defaulted = false;
			}
		}

		$current_level = $params->query_field_level;

		$relation = 'AND';

		if ( isset( $fields['relation'] ) ) {
			$relation = strtoupper( trim( (string) pods_v( 'relation', $fields, 'AND', true ) ) );

			if ( 'AND' !== $relation ) {
				$relation = 'OR';
			}

			unset( $fields['relation'] );
		}

		foreach ( $fields as $field => $match ) {
			if ( is_array( $match ) && isset( $match['relation'] ) ) {
				$params->query_field_level = $current_level + 1;

				$query_field = self::query_fields( $match, $pod, $params );

				$params->query_field_level = $current_level;

				if ( ! empty( $query_field ) ) {
					$query_fields[] = $query_field;
				}
			} else {
				$query_field = self::query_field( $field, $match, $pod, $params );

				if ( ! empty( $query_field ) ) {
					$query_fields[] = $query_field;
				}
			}
		}

		if ( ! empty( $query_fields ) ) {
			// If post_status not sent, detect it.
			if ( $pod && 'post_type' === $pod['type'] && 1 === $current_level && ! $params->where_defaulted && ! empty( $params->where_default ) ) {
				$post_status_found = false;

				if ( ! $params->query_field_syntax ) {
					$haystack = implode( ' ', (array) $query_fields );
					$haystack = preg_replace( '/\s/', ' ', $haystack );
					$haystack = preg_replace( '/\w\(/', ' ', $haystack );
					$haystack = str_replace( array( '(', ')', '  ', '\\\'', '\\"' ), ' ', $haystack );

					// Find xyz.some_field and `xyz`.`some_field` variations.
					preg_match_all( '/`?[\w\-]+`?(?:\\.`?[\w\-]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER );

					// Find `some_field` variations but leave out some_field without backticks.
					preg_match_all( '/(?:`?[\w\-]+`?)+(?!\.)(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found2, PREG_PATTERN_ORDER );

					$found = (array) @current( $found );
					$found = array_merge( $found, (array) @current( $found2 ) );
					$found = array_unique( $found );

					$post_status_patterns = [
						'`t`.`post_status`',
						't.post_status',
						'`post_status`',
						'post_status',
					];

					$post_status_found = 0 < count( array_intersect( $found, $post_status_patterns ) );
				} elseif ( ! empty( $params->query_fields ) && in_array( 'post_status', $params->query_fields, true ) ) {
					$post_status_found = true;
				}//end if

				if ( ! $post_status_found ) {
					$query_fields[] = $params->where_default;
				}
			}//end if

			if ( 1 < count( $query_fields ) ) {
				$query_fields = '( ( ' . implode( ' ) ' . $relation . ' ( ', $query_fields ) . ' ) )';
			} else {
				$query_fields = '( ' . implode( ' ' . $relation . ' ', $query_fields ) . ' )';
			}
		} else {
			$query_fields = null;
		}//end if

		// query_fields level complete.
		if ( 1 === $params->query_field_level ) {
			$params->query_field_level = 0;
		}

		return $query_fields;
	}

	/**
	 * Get the string to use in a query for matching, uses WP_Query meta_query arguments
	 *
	 * @param string|int   $field  Field name or array index.
	 * @param array|string $q      Query array (meta_query) or string for matching.
	 * @param array|Pod    $pod    Related Pod.
	 * @param object       $params Parameters passed from select().
	 *
	 * @return string|null Query field string
	 *
	 * @see   PodsData::query_fields
	 * @static
	 * @since 2.3.0
	 */
	public static function query_field( $field, $q, $pod = null, &$params = null ) {

		global $wpdb;

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$field_query = null;

		// Plain queries.
		if ( is_numeric( $field ) && ! is_array( $q ) ) {
			return $q;
		} elseif ( ! is_numeric( $field ) && ( ! is_array( $q ) || ! isset( $q['key'], $q['field'] ) ) ) {
			$new_q = array(
				'field'           => $field,
				'compare'         => pods_v( 'compare', $q, '=', true ),
				'value'           => pods_v( 'value', $q, $q, true ),
				'sanitize'        => pods_v( 'sanitize', $q, true ),
				'sanitize_format' => pods_v( 'sanitize_format', $q ),
				'cast'            => pods_v( 'cast', $q ),
			);

			if ( is_array( $new_q['value'] ) ) {
				if ( '=' === $new_q['compare'] ) {
					$new_q['compare'] = 'IN';
				}

				if ( isset( $new_q['value']['compare'] ) ) {
					unset( $new_q['value']['compare'] );
				}
			}

			$q = $new_q;
		}//end if

		$field_name  = trim( (string) pods_v( 'field', $q, pods_v( 'key', $q, $field, true ), true ) );
		$field_type  = strtoupper( trim( (string) pods_v( 'type', $q, 'CHAR', true ) ) );
		$field_value = pods_v( 'value', $q );

		$field_compare = '=';

		if ( is_array( $field_value ) ) {
			$field_compare = 'IN';
		}

		$field_compare         = strtoupper( trim( (string) pods_v( 'compare', $q, $field_compare, true ) ) );
		$field_sanitize        = (boolean) pods_v( 'sanitize', $q, true );
		$field_sanitize_format = pods_v( 'sanitize_format', $q, null, true );
		$field_cast            = pods_v( 'cast', $q, null, true );

		if ( is_object( $params ) ) {
			$params->meta_query_syntax = true;
			$params->query_fields[]    = $field_name;
		}

		// Deprecated WP type.
		if ( 'NUMERIC' === $field_type ) {
			$field_type = 'SIGNED';
		} elseif ( ! in_array(
			$field_type, array(
				'BINARY',
				'CHAR',
				'DATE',
				'DATETIME',
				'DECIMAL',
				'SIGNED',
				'TIME',
				'UNSIGNED',
			), true
		) ) {
			$field_type = 'CHAR';
		}

		$is_pod_meta_storage = 'meta' === $pod['storage'];

		// Alias / Casting.
		if ( empty( $field_cast ) ) {
			// Setup field casting from field name.
			if ( false === strpos( $field_name, '`' ) && false === strpos( $field_name, '(' ) && false === strpos( $field_name, ' ' ) ) {
				// Handle field naming if Pod-based.
				if ( $pod && false === strpos( $field_name, '.' ) ) {
					$field_cast = '';

					$the_field = null;

					if ( $pod instanceof Pod ) {
						$the_field = $pod->get_field( $field_name );
					} elseif ( isset( $pod[ $field_name ] ) ) {
						$the_field = $pod[ $field_name ];
					}

					$tableless_field_types = PodsForm::tableless_field_types();

					if ( $the_field ) {
						$field_name = $the_field->get_name();

						// @todo Implement get_db_field here in the future.

						if ( $the_field instanceof Object_Field ) {
							$field_cast = "`t`.`{$field_name}`";
						} elseif ( in_array( $the_field['type'], $tableless_field_types, true ) ) {
							$related_object_type = $the_field->get_related_object_type();

							if ( in_array( $related_object_type, $simple_tableless_objects, true ) ) {
								if ( $is_pod_meta_storage ) {
									$field_cast = "`{$field_name}`.`meta_value`";
								} else {
									$field_cast = "`t`.`{$field_name}`";
								}
							} else {
								$table = $the_field->get_table_info();

								if ( ! empty( $table ) ) {
									if ( is_int( $field_value ) ) {
										$field_cast = "`{$field_name}`.`" . $table['field_id'] . '`';
									} else {
										// Prior to 2.8 this was the default query, retain backwards compatibility
										$field_cast = "`{$field_name}`.`" . $table['field_index'] . '`';
									}
								}
							}
						} elseif ( ! in_array( $pod['type'], [ 'pod', 'table' ], true ) ) {
							if ( $is_pod_meta_storage ) {
								$field_cast = "`{$field_name}`.`meta_value`";
							} else {
								$field_cast = "`d`.`{$field_name}`";
							}
						} elseif ( $is_pod_meta_storage ) {
							$field_cast = "`{$field_name}`.`meta_value`";
						} else {
							$field_cast = "`t`.`{$field_name}`";
						}
					}

					// Fallback to support meta or object field that's not registered.
					if ( empty( $field_cast ) ) {
						if ( $is_pod_meta_storage ) {
							$field_cast = "`{$field_name}`.`meta_value`";
						} else {
							$field_cast = "`t`.`{$field_name}`";
						}
					}
				} else {
					$field_cast = '`' . str_replace( '.', '`.`', $field_name ) . '`';
				}//end if
			} else {
				$field_cast = $field_name;
			}//end if

			// Cast field if needed.
			if ( 'CHAR' !== $field_type ) {
				$field_cast = 'CAST( ' . $field_cast . ' AS ' . $field_type . ' )';
			}
		}//end if

		// Setup string sanitizing for $wpdb->prepare().
		if ( empty( $field_sanitize_format ) ) {
			// Sanitize as string.
			$field_sanitize_format = '%s';

			// Sanitize as integer if needed.
			if ( in_array( $field_type, array( 'UNSIGNED', 'SIGNED' ), true ) ) {
				$field_sanitize_format = '%d';
			}
		}

		// Restrict to supported comparisons.
		if ( ! in_array(
			$field_compare, array(
				'=',
				'!=',
				'>',
				'>=',
				'<',
				'<=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				'ALL',
				'BETWEEN',
				'NOT BETWEEN',
				'EXISTS',
				'NOT EXISTS',
				'REGEXP',
				'NOT REGEXP',
				'RLIKE',
			), true
		) ) {
			$field_compare = '=';
		}

		// Restrict to supported array comparisons.
		if ( is_array( $field_value ) && ! in_array(
			$field_compare, array(
				'IN',
				'NOT IN',
				'ALL',
				'BETWEEN',
				'NOT BETWEEN',
			), true
		) ) {
			if ( in_array(
				$field_compare, array(
					'!=',
					'NOT LIKE',
				), true
			) ) {
				$field_compare = 'NOT IN';
			} else {
				$field_compare = 'IN';
			}
		} elseif ( ! is_array( $field_value ) && in_array(
			$field_compare, array(
				'IN',
				'NOT IN',
				'ALL',
				'BETWEEN',
				'NOT BETWEEN',
			), true
		) ) {
			$check_value = preg_split( '/[,\s]+/', $field_value );

			if ( 1 < count( $check_value ) ) {
				$field_value = $check_value;
			} elseif ( in_array(
				$field_compare, array(
					'NOT IN',
					'NOT BETWEEN',
				), true
			) ) {
				$field_compare = '!=';
			} else {
				$field_compare = '=';
			}
		}//end if

		// Restrict to two values, force = and != if only one value provided.
		if ( in_array(
			$field_compare, array(
				'BETWEEN',
				'NOT BETWEEN',
			), true
		) ) {
			$field_value = array_values( array_slice( $field_value, 0, 2 ) );

			if ( 1 === count( $field_value ) ) {
				if ( 'NOT IN' === $field_compare ) {
					$field_compare = '!=';
				} else {
					$field_compare = '=';
				}
			}
		}

		// Single array handling.
		if ( 1 === count( (array) $field_value ) && 'ALL' === $field_compare ) {
			$field_compare = '=';
		} elseif ( empty( $field_value ) && in_array(
			$field_compare, array(
				'IN',
				'NOT IN',
				'BETWEEN',
				'NOT BETWEEN',
			), true
		) ) {
			$field_compare = 'EXISTS';
		}

		// Rebuild $q.
		$q = array(
			'field'           => $field_name,
			'type'            => $field_type,
			'value'           => $field_value,
			'compare'         => $field_compare,
			'sanitize'        => $field_sanitize,
			'sanitize_format' => $field_sanitize_format,
			'cast'            => $field_cast,
		);

		// Make the query.
		if ( in_array( $field_compare, [
			'=',
			'!=',
			'>',
			'>=',
			'<',
			'<=',
			'REGEXP',
			'NOT REGEXP',
			'RLIKE',
		], true ) ) {
			if ( $field_sanitize ) {
				$field_query = "{$field_cast} {$field_compare} {$field_sanitize_format}";
				$field_query = $wpdb->prepare( $field_query, $field_value );
			} else {
				$field_query = "{$field_cast} {$field_compare} '{$field_value}'";
			}
		} elseif ( in_array( $field_compare, [
			'LIKE',
			'NOT LIKE',
		], true ) ) {
			if ( $field_sanitize ) {
				$field_query = "{$field_cast} {$field_compare} '%" . pods_sanitize_like( $field_value ) . "%'";
			} else {
				$field_query = "{$field_cast} {$field_compare} '{$field_value}'";
			}
		} elseif ( in_array( $field_compare, [
			'IN',
			'NOT IN',
			'ALL',
		], true ) ) {
			$field_value = (array) $field_value;

			if ( 'ALL' === $field_compare ) {
				$field_compare = 'IN';

				if ( $pod ) {
					$params->having[] = 'COUNT( DISTINCT ' . $field_cast . ' ) = ' . count( $field_value );

					if (
						empty( $params->groupby )
						|| (
							! in_array( "`t`.`{$pod['field_id']}`", $params->groupby, true )
							&& ! in_array( "t.{$pod['field_id']}", $params->groupby, true )
						)
					) {
						$params->groupby[] = "`t`.`{$pod['field_id']}`";
					}
				}
			}

			if ( $field_sanitize ) {
				$field_query = "{$field_cast} {$field_compare} ( " . substr( str_repeat( ', ' . $field_sanitize_format, count( $field_value ) ), 1 ) . " )";
				$field_query = $wpdb->prepare( $field_query, $field_value );
			} else {
				$field_query = "{$field_cast} {$field_compare} ( '" . implode( "', '", $field_value ) . "' )";
			}
		} elseif ( in_array( $field_compare, [
			'BETWEEN',
			'NOT BETWEEN',
		], true ) ) {
			if ( $field_sanitize ) {
				$field_query = "{$field_cast} {$field_compare} {$field_sanitize_format} AND {$field_sanitize_format}";
				$field_query = $wpdb->prepare( $field_query, $field_value );
			} else {
				$field_query = "{$field_cast} {$field_compare} '{$field_value[0]}' AND '{$field_value[1]}'";
			}
		} elseif ( 'EXISTS' === $field_compare ) {
			$field_query = "{$field_cast} IS NOT NULL";
		} elseif ( 'NOT EXISTS' === $field_compare ) {
			$field_query = "{$field_cast} IS NULL";
		}//end if

		$field_query = apply_filters( 'pods_data_field_query', $field_query, $q );

		return $field_query;
	}

	/**
	 * Setup fields for traversal
	 *
	 * @param array  $fields Associative array of fields data.
	 *
	 * @return array Traverse feed
	 *
	 * @param object $params (optional) Parameters from build().
	 *
	 * @since 2.0.0
	 */
	public function traverse_build( $fields = null, $params = null ) {

		if ( null === $fields ) {
			$fields = $this->fields;
		}

		$feed = array();

		foreach ( $fields as $field => $data ) {
			if ( ! is_array( $data ) ) {
				$field = $data;
			}

			if ( ! isset( $_GET[ 'filter_' . $field ] ) ) {
				continue;
			}

			$field_value = pods_v( 'filter_' . $field, 'get', false, true );

			if ( ! empty( $field_value ) || ( is_string( $field_value ) && 0 < strlen( $field_value ) ) ) {
				$feed[ 'traverse_' . $field ] = array( $field );
			}
		}

		return $feed;
	}

	/**
	 * Recursively join tables based on fields
	 *
	 * @param array $traverse_recurse Array of traversal options.
	 *
	 * @return array Array of table joins
	 *
	 * @since 2.0.0
	 */
	public function traverse_recurse( $traverse_recurse ) {
		global $wpdb;

		$defaults = [
			'pod'             => null,
			'fields'          => [],
			'joined'          => 't',
			'depth'           => 0,
			'joined_id'       => 'id',
			'joined_index'    => 'id',
			'params'          => new stdClass(),
			'last_table_info' => [],
			'last_field'      => [],
		];

		$traverse_recurse = array_merge( $defaults, $traverse_recurse );

		$joins = [];

		if ( 0 === $traverse_recurse['depth'] && ! empty( $traverse_recurse['pod'] ) && ! empty( $traverse_recurse ['last_table_info'] ) && isset( $traverse_recurse ['last_table_info']['id'] ) ) {
			$pod_data = $traverse_recurse['last_table_info'];
		} elseif ( empty( $traverse_recurse['pod'] ) ) {
			if ( ! empty( $traverse_recurse['params'] ) && ! empty( $traverse_recurse['params']->table ) && ( ! $wpdb->prefix // Make sure there is a prefix.
			                                                                                                  || 0 === strpos( $traverse_recurse['params']->table, $wpdb->prefix ) ) ) {
				if ( $wpdb->posts === $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'post_type';
				} elseif ( $wpdb->terms === $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'taxonomy';
				} elseif ( $wpdb->users === $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'user';
				} elseif ( $wpdb->comments === $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'comment';
				} else {
					return $joins;
				}

				$pod_data = [];

				if ( in_array( $traverse_recurse['pod'], [
					'user',
					'comment',
				], true ) ) {
					$new_pod = $this->api->load_pod( [
						'name'       => $traverse_recurse['pod'],
						'auto_setup' => true,
					] );

					if ( $new_pod && $new_pod['type'] === $traverse_recurse['pod'] ) {
						$pod_data = $new_pod;
					}
				}

				if ( empty( $pod_data ) ) {
					// @todo This logic is problematic with the new object based Pod configs.
					$default_storage = 'meta';

					$pod_data = [
						'id'            => 0,
						'name'          => '_table_' . $traverse_recurse['pod'],
						'type'          => $traverse_recurse['pod'],
						'storage'       => $default_storage,
						'fields'        => $this->api->get_wp_object_fields( $traverse_recurse['pod'] ),
						'object_fields' => [],
					];

					$pod_data['object_fields'] = $pod_data['fields'];

					$pod_data = pods_config_merge_data( $this->api->get_table_info( $traverse_recurse['pod'], '' ), $pod_data );
				} elseif ( 'taxonomy' === $pod_data['type'] && 'none' === $pod_data['storage'] ) {
					$pod_data['storage'] = 'meta';
				}

				$traverse_recurse['pod'] = $pod_data['name'];
			} else {
				return $joins;
			}//end if
		} else {
			$pod_data = $this->api->load_pod( [
				'name'       => $traverse_recurse['pod'],
				'auto_setup' => true,
			] );

			if ( empty( $pod_data ) ) {
				return $joins;
			}
		}//end if

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();
		$file_field_types         = PodsForm::file_field_types();

		if ( ! isset( $this->traversal[ $traverse_recurse['pod'] ] ) ) {
			$this->traversal[ $traverse_recurse['pod'] ] = [];
		}

		if ( ( empty( $pod_data['meta_table'] ) || $pod_data['meta_table'] === $pod_data['table'] ) && ( empty( $traverse_recurse['fields'] ) || empty( $traverse_recurse['fields'][ $traverse_recurse['depth'] ] ) ) ) {
			return $joins;
		}

		$field = $traverse_recurse['fields'][ $traverse_recurse['depth'] ];

		/**
		 * Prevent aliases from being used in traversals.
		 *
		 * @since 2.3.0
		 *
		 * @param array     $ignore_aliases   Aliases to be ignored.
		 * @param array     $field            Field data.
		 * @param array     $traverse_recurse Traverse params.
		 * @param \PodsData $pods_data        PodsData instance.
		 */
		$ignore_aliases = apply_filters( 'pods_data_traverse_recurse_ignore_aliases', [], $field, $traverse_recurse, $this );

		if ( in_array( $field, $ignore_aliases, true ) ) {
			return $joins;
		}

		$meta_data_table = false;

		$the_field = null;

		if ( $pod_data instanceof Pod ) {
			// Maybe get the field / object field from the pod.
			$the_field = $pod_data->get_field( $field );
		} elseif ( isset( $pod_data['fields'][ $field ] ) ) {
			$the_field = $pod_data['fields'][ $field ];
		}

		if ( null === $the_field && 'd' === $field && isset( $traverse_recurse['fields'][ $traverse_recurse['depth'] - 1 ] ) ) {
			$field = $traverse_recurse['fields'][ $traverse_recurse['depth'] - 1 ];

			if ( ! empty( $traverse_recurse['last_field'] ) ) {
				$the_field = $traverse_recurse['last_field'];
			} elseif ( $pod_data instanceof Pod ) {
				// Maybe get the field / object field from the pod.
				$the_field = $pod_data->get_field( $field );
			} elseif ( isset( $pod_data['fields'][ $field ] ) ) {
				$the_field = $pod_data['fields'][ $field ];
			}

			if ( null === $the_field ) {
				$field_type = 'pick';

				if ( isset( $traverse_recurse['last_table_info']['pod']['fields'][ $field ] ) ) {
					$field_type = $traverse_recurse['last_table_info']['pod']['fields'][ $field ]['type'];
				} elseif ( isset( $traverse_recurse['last_table_info']['pod']['object_fields'][ $field ] ) ) {
					$field_type = $traverse_recurse['last_table_info']['pod']['object_fields'][ $field ]['type'];
				}

				$the_field = [
					'id'          => 0,
					'name'        => $field,
					'type'        => $field_type,
					'pick_object' => $traverse_recurse['last_table_info']['pod']['type'],
					'pick_val'    => $traverse_recurse['last_table_info']['pod']['name'],
				];
			}

			$meta_data_table = true;
		}//end if

		// Fallback to meta table if the pod type supports it.
		$last = end( $traverse_recurse['fields'] );

		if ( ! $the_field ) {
			if ( 'meta_value' === $last && in_array( $pod_data['type'], [
					'post_type',
					'media',
					'taxonomy',
					'user',
					'comment',
				], true ) ) {
				// Set up a faux-field and use meta table fallback.
				$the_field = PodsForm::field_setup( [ 'name' => $field ] );
			} elseif ( ! $pod_data instanceof Pod && 'post_type' === $pod_data['type'] ) {
				// Maybe fallback to object field if it is tableless.
				$pod_data['object_fields'] = $this->api->get_wp_object_fields( 'post_type', $pod_data, true );

				if ( isset( $pod_data['object_fields'][ $field ] ) && in_array( $pod_data['object_fields'][ $field ]['type'], $tableless_field_types, true ) ) {
					$the_field = $pod_data['object_fields'][ $field ];
				}
			}
		}

		if ( null === $the_field ) {
			return $joins;
		}

		if ( $the_field instanceof Object_Field && ! in_array( $the_field['type'], $tableless_field_types, true ) ) {
			return $joins;
		}

		$traverse = $the_field;

		$table_info = array();

		if ( $the_field instanceof Field && $the_field->get_table_info() ) {
			$table_info = $the_field->get_table_info();
		} elseif (
			in_array( $traverse['type'], $file_field_types, true )
			|| (
				'pick' === $traverse['type']
				&& in_array( $traverse['pick_object'], [ 'media', 'attachment' ], true )
			)
		) {
			$table_info = $this->api->get_table_info( 'media', 'media' );
		} elseif ( ! in_array( $traverse['type'], $tableless_field_types, true ) ) {
			if ( $pod_data instanceof Pod ) {
				$table_info = $pod_data->get_table_info();
			} else {
				$table_info = $this->api->get_table_info( $pod_data['type'], $pod_data['name'], $pod_data['name'], $pod_data );
			}
		} else {
			$pick_object = pods_v( $traverse['type'] . '_object', $traverse );

			if ( in_array( $pick_object, $simple_tableless_objects, true ) && ! empty( $traverse_recurse['last_table_info'] ) ) {
				$has_last_table_info = ! empty( $traverse_recurse['last_table_info'] );

				if ( $has_last_table_info ) {
					$table_info = $traverse_recurse['last_table_info'];

					if ( ! empty( $table_info['meta_table'] ) ) {
						$meta_data_table = true;
					}
				} else {
					if ( ! isset( $traverse['pod'] ) ) {
						$traverse['pod'] = null;
					}

					if ( ! isset( $traverse['pick_val'] ) ) {
						$traverse['pick_val'] = null;
					}

					$table_info = $this->api->get_table_info( $traverse['pick_object'], $traverse['pick_val'], null, $traverse['pod'], $traverse );

					$traverse['table_info'] = $table_info;
				}
			}
		}

		if ( isset( $this->traversal[ $traverse_recurse['pod'] ][ $traverse['name'] ] ) ) {
			$traverse = $this->traversal[ $traverse_recurse['pod'] ][ $traverse['name'] ];
		}

		$traverse = apply_filters( 'pods_data_traverse', $traverse, $traverse_recurse, $this );

		if ( empty( $traverse ) || empty( $table_info ) ) {
			return $joins;
		}

		$traverse['id'] = (int) $traverse['id'];

		if ( empty( $traverse['id'] ) ) {
			$traverse['id'] = $field;
		}

		$this->traversal[ $traverse_recurse['pod'] ][ $field ] = $traverse;

		$field_joined = $field;

		$is_pickable = in_array( $traverse['type'], [ 'pick', 'taxonomy' ], true );
		$pick_object = pods_v( $traverse['type'] . '_object', $traverse );

		if ( 0 < $traverse_recurse['depth'] && 't' !== $traverse_recurse['joined'] ) {
			if (
				$meta_data_table
				&& (
					! $is_pickable
					|| ! in_array( $pick_object, $simple_tableless_objects, true )
				)
			) {
				$field_joined = $traverse_recurse['joined'] . '_d';
			} else {
				$field_joined = $traverse_recurse['joined'] . '_' . $field;
			}
		}

		$rel_alias = 'rel_' . $field_joined;

		if ( pods_v( 'search', $traverse_recurse['params'], false ) && empty( $traverse_recurse['params']->filters ) ) {
			if ( 0 < strlen( (string) pods_v( 'filter_' . $field_joined ) ) ) {
				$val = absint( pods_v( 'filter_' . $field_joined ) );

				$search = "`{$field_joined}`.`{$table_info[ 'field_id' ]}` = {$val}";

				if ( 'text' === $this->search_mode ) {
					$val = pods_v_sanitized( 'filter_' . $field_joined );

					$search = "`{$field_joined}`.`{$traverse[ 'name' ]}` = '{$val}'";
				} elseif ( 'text_like' === $this->search_mode ) {
					$val = pods_sanitize( pods_sanitize_like( pods_v( 'filter_' . $field_joined ) ) );

					$search = "`{$field_joined}`.`{$traverse[ 'name' ]}` LIKE '%{$val}%'";
				}

				$this->search_where[] = " {$search} ";
			}
		}

		$the_join = null;

		$joined_id    = $table_info['field_id'];
		$joined_index = $table_info['field_index'];

		if ( 'taxonomy' === $traverse['type'] ) {
			$rel_tt_alias = 'rel_tt_' . $field_joined;

			if ( pods_tableless() ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
						`{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
				";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			} elseif ( $meta_data_table ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'pod_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'pod_field_id' ]}` = `{$traverse_recurse[ 'rel_alias' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
				";
			} else {
				$the_join = "
					LEFT JOIN `{$wpdb->term_relationships}` AS `{$rel_alias}` ON
						`{$rel_alias}`.`object_id` = `{$traverse_recurse[ 'joined' ]}`.`ID`

					LEFT JOIN `{$wpdb->term_taxonomy}` AS `{$rel_tt_alias}` ON
						`{$rel_tt_alias}`.`taxonomy` = '{$traverse[ 'name' ]}'
						AND `{$rel_tt_alias}`.`term_taxonomy_id` = `{$rel_alias}`.`term_taxonomy_id`

					LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$rel_tt_alias}`.`{$table_info[ 'field_id' ]}`
				";

				// Override $rel_alias.
				$rel_alias = $field_joined;

				$joined_id    = $table_info['field_id'];
				$joined_index = $table_info['field_index'];
			}//end if
		} elseif ( 'comment' === $traverse['type'] ) {
			if ( pods_tableless() ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
						`{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
				";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			} elseif ( $meta_data_table ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'pod_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'pod_field_id' ]}` = `{$traverse_recurse[ 'rel_alias' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
				";
			} else {
				$the_join = "
					LEFT JOIN `{$wpdb->comments}` AS `{$field_joined}` ON
						`{$field_joined}`.`comment_post_ID` = `{$traverse_recurse[ 'joined' ]}`.`ID`
				";

				// Override $rel_alias.
				$rel_alias = $field_joined;

				$joined_id    = $table_info['field_id'];
				$joined_index = $table_info['field_index'];
			}//end if
		} elseif ( in_array( $traverse['type'], $tableless_field_types, true ) && ( ! $is_pickable || ! in_array( $pick_object, $simple_tableless_objects, true ) ) ) {
			if ( pods_tableless() ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
						`{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
				";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			} elseif ( $meta_data_table ) {
				if ( $traverse['id'] !== $traverse['pick_val'] ) {
					// This must be a relationship.
					$joined_id = 'related_item_id';
				} else {
					$joined_id = $traverse_recurse['joined_id'];
				}

				$the_join = "
					LEFT JOIN `{$table_info['pod_table']}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info['pod_field_id']}` = `{$traverse_recurse['rel_alias']}`.`{$joined_id}`
				";
			} elseif ( pods_podsrel_enabled( $the_field, 'lookup' ) ) {
				if ( ( $traverse_recurse['depth'] + 2 ) === count( $traverse_recurse['fields'] ) && ( ! $is_pickable || ! in_array( $pick_object, $simple_tableless_objects, true ) ) && 'post_author' === $traverse_recurse['fields'][ $traverse_recurse['depth'] + 1 ] ) {
					$table_info['recurse'] = false;
				}

				if ( ! is_numeric( $traverse['id'] ) ) {
					$the_join = "
						LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
							`{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse['id']}`
					";
				} else {
					$the_join = "
						LEFT JOIN `@wp_podsrel` AS `{$rel_alias}` ON
							`{$rel_alias}`.`field_id` = {$traverse[ 'id' ]}
							AND `{$rel_alias}`.`item_id` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

						LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
							`{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$rel_alias}`.`related_item_id`
					";
				}
			} else {
				$handle_join = [
					'recurse'  => $table_info['recurse'],
					'the_join' => null,
				];

				/**
				 * Allow filtering the join parameters to be used for custom traversal logic.
				 *
				 * @since 2.8.14
				 *
				 * @param array    $handle_join The join parameters to set.
				 * @param array    $args        The additional traverse recurse arguments.
				 * @param PodsData $pods_data   The PodsData object.
				 */
				$handle_join = apply_filters( 'pods_data_traverse_recurse_handle_join', $handle_join, [
					'traverse'         => $traverse,
					'traverse_recurse' => $traverse_recurse,
					'the_field'        => $the_field,
					'table_info'       => $table_info,
					'field_joined'     => $field_joined,
					'rel_alias'        => $rel_alias,
					'is_pickable'      => $is_pickable,
					'pick_object'      => $pick_object,
				], $this );

				if ( null !== $handle_join['recurse'] ) {
					$table_info['recurse'] = $handle_join['recurse'];
				}

				if ( null !== $handle_join['the_join'] ) {
					$the_join = $handle_join['the_join'];
				}
			}//end if
		} elseif ( 'meta' === $pod_data['storage'] || 'meta_value' === end( $traverse_recurse['fields'] ) ) {
			if ( ( $traverse_recurse['depth'] + 2 ) === count( $traverse_recurse['fields'] ) && ( ! $is_pickable || ! in_array( $pick_object, $simple_tableless_objects, true ) ) && $table_info['meta_field_value'] === $traverse_recurse['fields'][ $traverse_recurse['depth'] + 1 ] ) {
				$the_join = "
					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
				";

				$table_info['recurse'] = false;
			} else {
				$the_join = "
					LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
						`{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
						AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
				";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			}
		}

		$traverse_recursive = array(
			'pod'             => pods_v( 'name', pods_v( 'pod', $table_info ) ),
			'fields'          => $traverse_recurse['fields'],
			'joined'          => $field_joined,
			'depth'           => ( $traverse_recurse['depth'] + 1 ),
			'joined_id'       => $joined_id,
			'joined_index'    => $joined_index,
			'params'          => $traverse_recurse['params'],
			'rel_alias'       => $rel_alias,
			'last_table_info' => $table_info,
			'last_field'      => $the_field,
		);

		$the_join = apply_filters( 'pods_data_traverse_the_join', $the_join, $traverse_recurse, $traverse_recursive, $this );

		if ( empty( $the_join ) ) {
			return $joins;
		}

		$joins[ $traverse_recurse['pod'] . '_' . $traverse_recurse['depth'] . '_' . $traverse['id'] ] = $the_join;

		if ( ( $traverse_recurse['depth'] + 1 ) < count( $traverse_recurse['fields'] ) && ! empty( $traverse_recurse['pod'] ) && false !== $table_info['recurse'] ) {
			$joins = array_merge( $joins, $this->traverse_recurse( $traverse_recursive ) );
		}

		return $joins;
	}

	/**
	 * Recursively join tables based on fields
	 *
	 * @param array  $fields     Fields to recurse.
	 * @param null   $all_fields (optional) If $fields is empty then traverse all fields, argument does not need to be.
	 *                           passed
	 * @param object $params     (optional) Parameters from build().
	 *
	 * @return array Array of joins
	 */
	public function traverse( $fields = null, $all_fields = null, $params = null ) {
		if ( null === $fields ) {
			$fields = $this->traverse_build( $all_fields, $params );
		}

		$fields = (array) $fields;

		$recurse_joins = [];

		foreach ( $fields as $field ) {
			/**
			 * @var string[]|string $field The field(s) to recurse (related_field.name would be [related_field, name]).
			 */

			$traverse_recurse = [
				'pod'             => $this->pod,
				'fields'          => (array) $field,
				'params'          => $params,
				'last_table_info' => isset( $this->pod_data ) ? $this->pod_data : $this->table_info,
				'joined_id'       => $this->field_id,
				'joined_index'    => $this->field_index,
			];

			$recurse_joins[] = $this->traverse_recurse( $traverse_recurse );
		}

		$joins = array_merge( ...$recurse_joins );

		return array_filter( $joins );
	}

	/**
	 * Handle filters / actions for the class
	 *
	 * @since 2.0.0
	 */
	private static function do_hook() {

		$args = func_get_args();

		if ( empty( $args ) ) {
			return false;
		}

		$name = array_shift( $args );

		return pods_do_hook( 'data', $name, $args );
	}

	/**
	 * Get full prefix for Pods tables.
	 *
	 * @since 2.7.23
	 *
	 * @return string
	 */
	public static function get_pods_prefix() {

		global $wpdb;

		return $wpdb->prefix . self::$prefix;
	}

	/**
	 * Get the complete sql
	 *
	 * @since 2.0.5
	 *
	 * @param string|array $sql The SQL query, optionally an array with the query first and the variables to prepare after that.
	 */
	public function get_sql( $sql = '' ) {

		global $wpdb;

		if ( empty( $sql ) ) {
			$sql = $this->sql;
		}

		/**
		 * Allow SQL query to be manipulated.
		 *
		 * @param string   $sql       SQL Query string.
		 * @param PodsData $pods_data PodsData object.
		 *
		 * @since 2.7.0
		 */
		$sql = apply_filters( 'pods_data_get_sql', $sql, $this );

		$sql = str_replace( array( '@wp_users', '@wp_' ), array( $wpdb->users, $wpdb->prefix ), $sql );

		$sql = str_replace( '{prefix}', '@wp_', $sql );
		$sql = str_replace( '{/prefix/}', '{prefix}', $sql );

		return $sql;
	}

	/**
	 * Get the db field to use in SQL queries.
	 *
	 * @since 2.8.9
	 *
	 * @param array|object $params The parameters.
	 *
	 * @return string|false The db field to use in SQL queries, or false if not found/invalid.
	 */
	public static function get_db_field( $params ) {
		if ( is_object( $params ) ) {
			$params = get_object_vars( $params );
		}

		$params = (object) array_merge( [
			'field_name'          => null,
			'field'               => [],
			'fields'              => [],
			'aliases'             => [],
			'use_field_id'        => false,
			'is_pod_meta_storage' => false,
			'meta_fields'         => false,
			'pod_table_prefix'    => 't',
		], $params );

		if ( empty( $params->field_name ) ) {
			$params->field_name = pods_v( 'name', $params->field );
		}

		if ( empty( $params->field_name ) || empty( $params->field ) ) {
			return false;
		}

		$real_name = pods_v( 'real_name', $params->field );

		if ( ! empty( $real_name ) ) {
			return $real_name;
		}

		$db_field = '`' . $params->field_name . '`';

		$use_pod_meta_storage_for_field = $params->is_pod_meta_storage;

		/**
		 * Allow filtering whether to use meta storage for this field.
		 *
		 * @since 2.8.9
		 *
		 * @param bool        $use_pod_meta_storage_for_field Whether to use meta storage for this field.
		 * @param string      $field_name The field name.
		 * @param array|Field $field      The field options.
		 * @param object      $params     The list of additional parameters.
		 */
		$use_pod_meta_storage_for_field = apply_filters( 'pods_data_get_db_field_use_pod_meta_storage_for_field', $use_pod_meta_storage_for_field, $params->field_name, $params->field, $params );

		$simple_tableless_objects = PodsForm::simple_tableless_objects();
		$file_field_types         = PodsForm::file_field_types();

		if ( 'pick' === $params->field['type'] && ! in_array( pods_v( 'pick_object', $params->field ), $simple_tableless_objects, true ) ) {
			if ( $params->field instanceof Field ) {
				$table_info_field_id    = $params->field->get_arg( 'field_id' );
				$table_info_field_index = $params->field->get_arg( 'field_index' );
			} else {
				$table_info = pods_v( 'table_info', $params->field );

				if ( empty( $table_info ) ) {
					$table_info = pods_api()->get_table_info( pods_v( 'pick_object', $params->field ), pods_v( 'pick_val', $params->field ) );
				}

				if ( empty( $table_info['field_id'] ) || empty( $table_info['field_index'] ) ) {
					return false;
				}

				$table_info_field_id    = $table_info['field_id'];
				$table_info_field_index = $table_info['field_index'];
			}

			if ( $params->use_field_id ) {
				$db_field = $db_field . '.`' . $table_info_field_id . '`';
			} else {
				$db_field = $db_field . '.`' . $table_info_field_index . '`';
			}
		} elseif ( 'taxonomy' === $params->field['type'] ) {
			$db_field = $db_field . '.`term_id`';
		} elseif ( in_array( $params->field['type'], $file_field_types, true ) ) {
			if ( $params->use_field_id ) {
				$db_field = $db_field . '.`ID`';
			} else {
				$db_field = $db_field . '.`post_title`';
			}
		} elseif ( isset( $params->fields[ $params->field_name ] ) && $params->fields[ $params->field_name ] instanceof Object_Field ) {
			if ( $params->meta_fields && $use_pod_meta_storage_for_field ) {
				$db_field = $db_field . '.`meta_value`';
			} else {
				$db_field = '`' . $params->pod_table_prefix . '`.' . $db_field;
			}
		} elseif ( ! isset( $params->fields[ $params->field_name ] ) || $use_pod_meta_storage_for_field ) {
			$db_field = $db_field . '.`meta_value`';
		} else {
			$db_field = '`t`.' . $db_field;
		}//end if

		if ( isset( $params->aliases[ $params->field_name ] ) ) {
			$db_field = '`' . $params->aliases[ $params->field_name ] . '`';
		}

		/**
		 * Allow filtering what to use when referencing the field from the database.
		 *
		 * @since 2.8.9
		 *
		 * @param string      $db_field   What to use when referencing the field from the database.
		 * @param string      $field_name The field name.
		 * @param array|Field $field      The field options.
		 * @param object      $params     The list of additional parameters.
		 */
		return apply_filters( 'pods_data_get_db_field', $db_field, $params->field_name, $params->field, $params );
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 *
	 * @since 2.8.0
	 */
	#[\ReturnTypeWillChange]
	public function __get( $name ) {
		$name = (string) $name;

		// Map deprecated properties.
		$mapped = array(
			'data' => 'rows',
		);

		if ( isset( $mapped[ $name ] ) ) {
			return $this->{$mapped[$name]};
		}

		// Handle alias Pod properties.
		$supported_pods_object = array(
			'pod'           => 'name',
			'pod_id'        => 'id',
			'fields'        => 'fields',
			'object_fields' => 'object_fields',
			'detail_page'   => 'detail_url',
			'detail_url'    => 'detail_url',
			'select'        => 'select',
			'table'         => 'table',
			'field_id'      => 'field_id',
			'field_index'   => 'field_index',
			'field_slug'    => 'field_slug',
			'join'          => 'join',
			'where'         => 'where',
			'where_default' => 'where_default',
			'orderby'       => 'orderby',
			'type'          => 'type',
			'storage'       => 'storage',
		);

		if ( isset( $supported_pods_object[ $name ] ) ) {
			if ( ! $this->pod_data instanceof Pod ) {
				// Check if table info is set.
				if ( ! is_array( $this->table_info ) ) {
					return null;
				}

				return pods_v( $supported_pods_object[ $name ], $this->table_info );
			}

			return $this->pod_data->get_arg( $supported_pods_object[ $name ] );
		}

		return null;
	}

	/**
	 * Handle variables that have been deprecated.
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 *
	 * @since 2.8.0
	 */
	public function __set( $name, $value ): void {
		$supported_overrides = array(
			'select'        => 'select',
			'table'         => 'table',
			'field_id'      => 'field_id',
			'field_index'   => 'field_index',
			'field_slug'    => 'field_slug',
			'join'          => 'join',
			'where'         => 'where',
			'where_default' => 'where_default',
			'orderby'       => 'orderby',
		);

		// Allow overrides for certain values.
		if ( isset( $supported_overrides[ $name ] ) ) {
			$this->{$name} = $value;
		}
	}

	/**
	 * Handle variables that have been deprecated.
	 *
	 * @param string $name Property name.
	 *
	 * @return bool Whether the variable is set or not.
	 *
	 * @since 2.8.0
	 */
	public function __isset( $name ): bool {
		// Handle alias Pod properties.
		$supported_pods_object = array(
			'pod'           => 'name',
			'pod_id'        => 'id',
			'fields'        => 'fields',
			'detail_page'   => 'detail_url',
			'detail_url'    => 'detail_url',
			'select'        => 'select',
			'table'         => 'table',
			'field_id'      => 'field_id',
			'field_index'   => 'field_index',
			'field_slug'    => 'field_slug',
			'join'          => 'join',
			'where'         => 'where',
			'where_default' => 'where_default',
			'orderby'       => 'orderby',
		);

		if ( isset( $supported_pods_object[ $name ] ) ) {
			if ( ! $this->pod_data instanceof Pod ) {
				// Check if table info is set.
				if ( ! is_array( $this->table_info ) ) {
					return false;
				}

				return null !== pods_v( $supported_pods_object[ $name ], $this->table_info );
			}

			return null !== $this->pod_data->get_arg( $supported_pods_object[ $name ] );
		}

		return false;
	}

	/**
	 * Handle variables that have been deprecated.
	 *
	 * @param string $name Property name.
	 *
	 * @since 2.8.0
	 */
	public function __unset( $name ): void {
		// Don't do anything.
		return;
	}
}
