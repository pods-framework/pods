<?php
/**
 * @package Pods
 */
class PodsData {

    /**
     * @var PodsData
     */
    static $instance = null;

    /**
     * @var string
     */
    static protected $prefix = 'pods_';

    /**
     * @var array
     */
    static protected $field_types = array();

    /**
     * @var bool
     */
    public static $display_errors = true;

    /**
     * @var PodsAPI
     */
    public $api = null;

    /**
     * @var null
     */
    public $select = null;

    /**
     * @var null
     */
    public $table = null;

    /**
     * @var null
     */
    public $pod = null;

    /**
     * @var array|bool|mixed|null|void
     */
    public $pod_data = null;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $field_id = 'id';

    /**
     * @var string
     */
    public $field_index = 'name';

    /**
     * @var string
     */
    public $field_slug = '';

    /**
     * @var string
     */
    public $join = '';

    /**
     * @var array
     */
    public $where = array();

    /**
     * @var array
     */
    public $where_default = array();

    /**
     * @var string
     */
    public $orderby = '';

    /**
     * @var array
     */
    public $fields = array();

    /**
     * @var array
     */
    public $aliases = array();

    /**
     * @var
     */
    public $detail_page;

    // data
    /**
     * @var int
     */
    public $row_number = -1;

    /**
     * @var
     */
    public $data;

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

    // pagination
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
     * @var bool
     */
    public $pagination = true;

    // search
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
     * @param string $pod Pod name
     * @param integer $id Pod Item ID
     * @param bool $strict If true throws an error if a pod is not found.
     *
     * @return \PodsData
     *
     * @since 2.3.5
     */
    public static function init ( $pod = null, $id = 0, $strict = true ) {
        if ( ( true !== $pod && null !== $pod ) || 0 != $id )
            return new PodsData( $pod, $id, $strict );
        elseif ( !is_object( self::$instance ) )
            self::$instance = new PodsData();
        else {
            $vars = get_class_vars( __CLASS__ );

            foreach ( $vars as $var => $default ) {
                if ( 'api' == $var )
                    continue;

                self::$instance->{$var} = $default;
            }
        }

        return self::$instance;
    }

    /**
     * Data Abstraction Class for Pods
     *
     * @param string $pod Pod name
     * @param integer $id Pod Item ID
     * @param bool $strict If true throws an error if a pod is not found.
     *
     * @return \PodsData
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct ( $pod = null, $id = 0, $strict = true ) {
        global $wpdb;

        if ( is_object( $pod ) && 'PodsAPI' == get_class( $pod ) ) {
            $this->api = $pod;
            $pod = $this->api->pod;
        }
        else
            $this->api = pods_api( $pod );

        $this->api->display_errors =& self::$display_errors;

        if ( !empty( $pod ) ) {
            $this->pod_data =& $this->api->pod_data;

            if ( false === $this->pod_data ) {
                if ( true === $strict )
                    return pods_error( 'Pod not found', $this );
                else
                    return $this;
            }

            $this->pod_id = $this->pod_data[ 'id' ];
            $this->pod = $this->pod_data[ 'name' ];
            $this->fields = $this->pod_data[ 'fields' ];

            if ( isset( $this->pod_data[ 'options' ][ 'detail_url' ] ) )
                $this->detail_page = $this->pod_data[ 'options' ][ 'detail_url' ];

            if ( isset( $this->pod_data[ 'select' ] ) )
                $this->select = $this->pod_data[ 'select' ];

            if ( isset( $this->pod_data[ 'table' ] ) )
                $this->table = $this->pod_data[ 'table' ];

            if ( isset( $this->pod_data[ 'join' ] ) )
                $this->join = $this->pod_data[ 'join' ];

            if ( isset( $this->pod_data[ 'field_id' ] ) )
                $this->field_id = $this->pod_data[ 'field_id' ];

            if ( isset( $this->pod_data[ 'field_index' ] ) )
                $this->field_index = $this->pod_data[ 'field_index' ];

            if ( isset( $this->pod_data[ 'field_slug' ] ) )
                $this->field_slug = $this->pod_data[ 'field_slug' ];

            if ( isset( $this->pod_data[ 'where' ] ) )
                $this->where = $this->pod_data[ 'where' ];

            if ( isset( $this->pod_data[ 'where_default' ] ) )
                $this->where_default = $this->pod_data[ 'where_default' ];

            if ( isset( $this->pod_data[ 'orderby' ] ) )
                $this->orderby = $this->pod_data[ 'orderby' ];

            if ( 'settings' == $this->pod_data[ 'type' ] ) {
                $this->id = $this->pod_data[ 'id' ];

                $this->fetch( $this->id );
            }
            elseif ( null !== $id && !is_array( $id ) && !is_object( $id ) ) {
                $this->id = $id;

                $this->fetch( $this->id );
            }
        }
    }

    /**
     * Handle tables like they are Pods (for traversal in select/build)
     *
     * @param array|string $table
     * @param string $object
     */
    public function table ( $table, $object = '' ) {
        global $wpdb;

        if ( !is_array( $table ) ) {
            $object_type = '';

            if ( $wpdb->users == $table )
                $object_type = 'user';
            elseif ( $wpdb->posts == $table )
                $object_type = 'post_type';
            elseif ( $wpdb->terms == $table )
                $object_type = 'taxonomy';
            elseif ( $wpdb->options == $table )
                $object_type = 'settings';
        }

        if ( !empty( $object_type ) )
            $table = $this->api->get_table_info( $object_type, $object );

        if ( !empty( $table ) && is_array( $table ) ) {
            $table[ 'id' ] = pods_var( 'id', $table[ 'pod' ], 0, null, true );
            $table[ 'name' ] = pods_var( 'name', $table[ 'pod' ], $table[ 'object_type' ], null, true );
            $table[ 'type' ] = pods_var_raw( 'type', $table[ 'pod' ], $table[ 'object_type' ], null, true );

            $default_storage = 'meta';

            if ( 'taxonomy' == $table[ 'type' ] && ! function_exists( 'get_term_meta' ) ) {
                $default_storage = 'none';
            }

            $table[ 'storage' ] = pods_var_raw( 'storage', $table[ 'pod' ], $default_storage, null, true );
            $table[ 'fields' ] = pods_var_raw( 'fields', $table[ 'pod' ], array() );
            $table[ 'object_fields' ] = pods_var_raw( 'object_fields', $table[ 'pod' ], $this->api->get_wp_object_fields( $table[ 'object_type' ] ), null, true );

            $this->pod_data = $table;
            $this->pod_id = $this->pod_data[ 'id' ];
            $this->pod = $this->pod_data[ 'name' ];
            $this->fields = $this->pod_data[ 'fields' ];

            if ( isset( $this->pod_data[ 'select' ] ) )
                $this->select = $this->pod_data[ 'select' ];

            if ( isset( $this->pod_data[ 'table' ] ) )
                $this->table = $this->pod_data[ 'table' ];

            if ( isset( $this->pod_data[ 'join' ] ) )
                $this->join = $this->pod_data[ 'join' ];

            if ( isset( $this->pod_data[ 'field_id' ] ) )
                $this->field_id = $this->pod_data[ 'field_id' ];

            if ( isset( $this->pod_data[ 'field_index' ] ) )
                $this->field_index = $this->pod_data[ 'field_index' ];

            if ( isset( $this->pod_data[ 'field_slug' ] ) )
                $this->field_slug = $this->pod_data[ 'field_slug' ];

            if ( isset( $this->pod_data[ 'where' ] ) )
                $this->where = $this->pod_data[ 'where' ];

            if ( isset( $this->pod_data[ 'where_default' ] ) )
                $this->where_default = $this->pod_data[ 'where_default' ];

            if ( isset( $this->pod_data[ 'orderby' ] ) )
                $this->orderby = $this->pod_data[ 'orderby' ];
        }
    }

    /**
     * Insert an item, eventually mapping to WPDB::insert
     *
     * @param string $table Table name
     * @param array $data Data to insert (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     * @param array $format (optional) An array of formats to be mapped to each of the value in $data.
     *
     * @return int|bool The ID of the item
     *
     * @uses wpdb::insert
     *
     * @since 2.0
     */
    public function insert ( $table, $data, $format = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( strlen( $table ) < 1 || empty( $data ) || !is_array( $data ) )
            return false;

        if ( empty( $format ) ) {
            $format = array();

            foreach ( $data as $field ) {
                if ( isset( self::$field_types[ $field ] ) )
                    $format[] = self::$field_types[ $field ];
                elseif ( isset( $wpdb->field_types[ $field ] ) )
                    $format[] = $wpdb->field_types[ $field ];
                else
                    break;
            }
        }

        list( $table, $data, $format ) = $this->do_hook( 'insert', array( $table, $data, $format ) );

        $result = $wpdb->insert( $table, $data, $format );
        $this->insert_id = $wpdb->insert_id;

        if ( false !== $result )
            return $this->insert_id;

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
     * @param string $table Name of the table to update
     * @param array $data column => value pairs
     * @param array $formats For $wpdb->prepare, uses sprintf formatting
     *
     * @return mixed Sanitized query string
     *
     * @uses wpdb::prepare
     *
     * @since 2.0
     */
    public static function insert_on_duplicate ( $table, $data, $formats = array() ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $columns = array_keys( $data );

        $update = array();
        $values = array();

        foreach ( $columns as $column ) {
            $update[] = "`{$column}` = VALUES( `{$column}` )";
            $values[] = "%s";
        }

        if ( empty( $formats ) )
            $formats = $values;

        $columns_data = implode( '`, `', $columns );
        $formats = implode( ", ", $formats );
        $update = implode( ', ', $update );

        $sql = "INSERT INTO `{$table}` ( `{$columns_data}` ) VALUES ( {$formats} ) ON DUPLICATE KEY UPDATE {$update}";

        return $wpdb->prepare( $sql, $data );
    }

    /**
     * Update an item, eventually mapping to WPDB::update
     *
     * @param string $table Table name
     * @param array $data Data to update (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     * @param array $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be joined with ANDs. Both $where columns and $where values should be "raw".
     * @param array $format (optional) An array of formats to be mapped to each of the values in $data.
     * @param array $where_format (optional) An array of formats to be mapped to each of the values in $where.
     *
     * @return bool
     * @since 2.0
     */
    public function update ( $table, $data, $where, $format = null, $where_format = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( strlen( $table ) < 1 || empty( $data ) || !is_array( $data ) )
            return false;

        if ( empty( $format ) ) {
            $format = array();

            foreach ( $data as $field ) {
                if ( isset( self::$field_types[ $field ] ) )
                    $form = self::$field_types[ $field ];
                elseif ( isset( $wpdb->field_types[ $field ] ) )
                    $form = $wpdb->field_types[ $field ];
                else
                    $form = '%s';

                $format[] = $form;
            }
        }

        if ( empty( $where_format ) ) {
            $where_format = array();

            foreach ( (array) array_keys( $where ) as $field ) {
                if ( isset( self::$field_types[ $field ] ) )
                    $form = self::$field_types[ $field ];
                elseif ( isset( $wpdb->field_types[ $field ] ) )
                    $form = $wpdb->field_types[ $field ];
                else
                    $form = '%s';

                $where_format[] = $form;
            }
        }

        list( $table, $data, $where, $format, $where_format ) = $this->do_hook( 'update', array(
            $table,
            $data,
            $where,
            $format,
            $where_format
        ) );

        $result = $wpdb->update( $table, $data, $where, $format, $where_format );

        if ( false !== $result )
            return true;

        return false;
    }

    /**
     * Delete an item
     *
     * @param string $table Table name
     * @param array $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be joined with ANDs. Both $where columns and $where values should be "raw".
     * @param array $where_format (optional) An array of formats to be mapped to each of the values in $where.
     *
     * @return array|bool|mixed|null|void
     *
     * @uses PodsData::query
     * @uses PodsData::prepare
     *
     * @since 2.0
     */
    public function delete ( $table, $where, $where_format = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( strlen( $table ) < 1 || empty( $where ) || !is_array( $where ) )
            return false;

        $wheres = array();
        $where_formats = $where_format = (array) $where_format;

        foreach ( (array) array_keys( $where ) as $field ) {
            if ( !empty( $where_format ) )
                $form = ( $form = array_shift( $where_formats ) ) ? $form : $where_format[ 0 ];
            elseif ( isset( self::$field_types[ $field ] ) )
                $form = self::$field_types[ $field ];
            elseif ( isset( $wpdb->field_types[ $field ] ) )
                $form = $wpdb->field_types[ $field ];
            else
                $form = '%s';

            $wheres[] = "`{$field}` = {$form}";
        }

        $sql = "DELETE FROM `$table` WHERE " . implode( ' AND ', $wheres );

        list( $sql, $where ) = $this->do_hook( 'delete', array(
            $sql,
            array_values( $where )
        ), $table, $where, $where_format, $wheres );

        return $this->query( self::prepare( $sql, $where ) );
    }

    /**
     * Select items, eventually building dynamic query
     *
     * @param array $params
     *
     * @return array|bool|mixed
     * @since 2.0
     */
    public function select ( $params ) {
        global $wpdb;

        $cache_key = $results = false;

        /**
         * Filter select parameters before the query
         *
         * @param array|object $params
         * @param PodsData|object $this The current PodsData class instance.
         *
         * @since unknown
         */
        $params = apply_filters( 'pods_data_pre_select_params', $params, $this );

        // Debug purposes
        if ( 1 == pods_v( 'pods_debug_params', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
            pods_debug( $params );

        // Get from cache if enabled
        if ( null !== pods_v( 'expires', $params, null, false ) ) {
            $cache_key = md5( (string) $this->pod . serialize( $params ) );

            $results = pods_view_get( $cache_key, pods_v( 'cache_mode', $params, 'cache', true ), 'pods_data_select' );

            if ( empty( $results ) )
                $results = false;
        }

        if ( empty( $results ) ) {
            // Build
            $this->sql = $this->build( $params );

            // Debug purposes
            if ( ( 1 == pods_v( 'pods_debug_sql', 'get', 0 ) || 1 == pods_v( 'pods_debug_sql_all', 'get', 0 ) ) && pods_is_admin( array( 'pods' ) ) )
                echo '<textarea cols="100" rows="24">' . esc_textarea( str_replace( array( '@wp_users', '@wp_' ), array( $wpdb->users, $wpdb->prefix ), $this->sql ) ) . '</textarea>';

            if ( empty( $this->sql ) )
                return array();

            // Get Data
            $results = pods_query( $this->sql, $this );

            // Cache if enabled
            if ( false !== $cache_key )
                pods_view_set( $cache_key, $results, pods_v( 'expires', $params, 0, false ), pods_v( 'cache_mode', $params, 'cache', true ), 'pods_data_select' );
        }

        /**
         * Filter results of Pods Query
         *
         * @param array $results
         * @param array|object $params
         * @param PodsData|object $this The current PodsData class instance.
         *
         * @since unknown
         */
        $results = apply_filters( 'pods_data_select', $results, $params, $this );

        $this->data = $results;

        $this->row_number = -1;
        $this->row = null;

        // Fill in empty field data (if none provided)
        if ( ( !isset( $this->fields ) || empty( $this->fields ) ) && !empty( $this->data ) ) {
            $this->fields = array();
            $data = (array) @current( $this->data );

            foreach ( $data as $data_key => $data_value ) {
                $this->fields[ $data_key ] = array( 'label' => ucwords( str_replace( '-', ' ', str_replace( '_', ' ', $data_key ) ) ) );
                if ( isset( $this->pod_data[ 'object_fields' ][ $data_key ] ) ) {
                    $this->fields[ $data_key ] = $this->pod_data[ 'object_fields' ][ $data_key ];
                }
            }

            $this->fields = PodsForm::fields_setup( $this->fields );
        }

        $this->total_found_calculated = false;

	    $this->total = 0;

	    if ( ! empty( $this->data ) ) {
		    $this->total = count( (array) $this->data );
	    }

        return $this->data;
    }

    public function calculate_totals () {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        // Set totals
        if ( false !== $this->total_sql )
            $total = @current( $wpdb->get_col( $this->get_sql( $this->total_sql ) ) );
        else
            $total = @current( $wpdb->get_col( "SELECT FOUND_ROWS()" ) );

        $total = $this->do_hook( 'select_total', $total );
        $this->total_found = 0;
        $this->total_found_calculated = true;

        if ( is_numeric( $total ) )
            $this->total_found = $total;
    }

    /**
     * Build/Rewrite dynamic SQL and handle search/filter/sort
     *
     * @param array $params
     *
     * @return bool|mixed|string
     * @since 2.0
     */
    public function build ( $params ) {
		$simple_tableless_objects = PodsForm::simple_tableless_objects();
	    $file_field_types = PodsForm::file_field_types();

        $defaults = array(
            'select' => '*',
			'calc_rows' => false,
            'distinct' => true,
            'table' => null,
            'join' => null,
            'where' => null,
            'where_default' => null,
            'groupby' => null,
            'having' => null,
            'orderby' => null,
            'limit' => -1,
            'offset' => null,

            'id' => null,
            'index' => null,

            'page' => 1,
            'pagination' => $this->pagination,
            'search' => $this->search,
            'search_query' => null,
            'search_mode' => null,
            'search_across' => false,
            'search_across_picks' => false,
            'search_across_files' => false,
            'filters' => array(),

            'fields' => array(),
            'object_fields' => array(),
            'pod_table_prefix' => null,

            'traverse' => array(),

            'sql' => null,

            'strict' => false
        );

        $params = (object) array_merge( $defaults, (array) $params );

        if ( 0 < strlen( $params->sql ) )
            return $params->sql;

		$pod = false;

		if ( is_array( $this->pod_data ) ) {
			$pod = $this->pod_data;
		}

        // Validate
        $params->page = pods_absint( $params->page );

        $params->pagination = (boolean) $params->pagination;

        if ( 0 == $params->page || !$params->pagination )
            $params->page = 1;

        $params->limit = (int) $params->limit;

        if ( 0 == $params->limit )
            $params->limit = -1;

        $this->limit = $params->limit;

        $offset = ( $params->limit * ( $params->page - 1 ) );

        if ( 0 < (int) $params->offset )
            $params->offset += $offset;
        else
            $params->offset = $offset;

        if ( !$params->pagination || -1 == $params->limit ) {
            $params->page = 1;
            $params->offset = 0;
        }

        if ( ( empty( $params->fields ) || !is_array( $params->fields ) ) && !empty( $pod ) && isset( $this->fields ) && !empty( $this->fields ) )
            $params->fields = $this->fields;

        if ( ( empty( $params->object_fields ) || !is_array( $params->object_fields ) ) && !empty( $pod ) && isset( $pod[ 'object_fields' ] ) && !empty( $pod[ 'object_fields' ] ) )
            $params->object_fields = $pod[ 'object_fields' ];

        if ( empty( $params->filters ) && $params->search )
            $params->filters = array_keys( $params->fields );
        elseif ( empty( $params->filters ) )
            $params->filters = array();

        if ( empty( $params->index ) )
            $params->index = $this->field_index;

        if ( empty( $params->id ) )
            $params->id = $this->field_id;

        if ( empty( $params->table ) && !empty( $pod ) && isset( $this->table ) && !empty( $this->table ) )
            $params->table = $this->table;

        if ( empty( $params->pod_table_prefix ) )
            $params->pod_table_prefix = 't';

        if ( !empty( $pod ) && !in_array( $pod[ 'type' ], array( 'pod', 'table' ) ) && 'table' == $pod[ 'storage' ] )
            $params->pod_table_prefix = 'd';

        $params->meta_fields = false;

        if ( !empty( $pod ) && !in_array( $pod[ 'type' ], array( 'pod', 'table' ) ) && ( 'meta' == $pod[ 'storage' ] || ( 'none' == $pod[ 'storage' ] && function_exists( 'get_term_meta' ) ) ) )
            $params->meta_fields = true;

        if ( empty( $params->table ) )
            return false;

        if ( false === strpos( $params->table, '(' ) && false === strpos( $params->table, '`' ) )
            $params->table = '`' . $params->table . '`';

        if ( !empty( $params->join ) )
            $params->join = array_merge( (array) $this->join, (array) $params->join );
        elseif ( false === $params->strict )
            $params->join = $this->join;

		$params->where_defaulted = false;

		if ( empty( $params->where_default ) && false !== $params->where_default ) {
			$params->where_default = $this->where_default;
		}

		if ( false === $params->strict ) {
			// Set default where
            if ( !empty( $params->where_default ) && empty( $params->where ) ) {
				$params->where = array_values( (array) $params->where_default );

				$params->where_defaulted = true;
			}

			if ( !empty( $this->where ) ) {
				if ( is_array( $params->where ) && isset( $params->where[ 'relation' ] ) && 'OR' == strtoupper( $params->where[ 'relation' ] ) ) {
					$params->where = array_merge( array( $params->where ), array_values( (array) $this->where ) );
				}
				else {
					$params->where = array_merge( (array) $params->where, array_values( (array) $this->where ) );
				}
			}
		}

	    // Allow where array ( 'field' => 'value' ) and WP_Query meta_query syntax
	    if ( ! empty( $params->where ) ) {
		    $params->where = $this->query_fields( (array) $params->where, $pod, $params );
	    }

	    if ( empty( $params->where ) ) {
		    $params->where = array();
	    } else {
		    $params->where = (array) $params->where;
	    }

	    // Allow having array ( 'field' => 'value' ) and WP_Query meta_query syntax
	    if ( ! empty( $params->having ) ) {
		    $params->having = $this->query_fields( (array) $params->having, $pod, $params );
	    }

	    if ( empty( $params->having ) ) {
		    $params->having = array();
	    } else {
		    $params->having = (array) $params->having;
	    }

        if ( !empty( $params->orderby ) ) {
	        if ( 'post_type' == $pod[ 'type' ] && 'meta' == $pod[ 'storage' ] && is_array( $params->orderby ) ) {

		        foreach ( $params->orderby as $i => $orderby ) {
			        if ( strpos( $orderby, '.meta_value_num' ) ) {
				        $params->orderby[ $i ] = 'CAST(' . str_replace( '.meta_value_num', '.meta_value', $orderby ) . ' AS DECIMAL)';
			        } elseif ( strpos( $orderby, '.meta_value_date' ) ) {
				        $params->orderby[ $i ] = 'CAST(' . str_replace( '.meta_value_date', '.meta_value', $orderby  ) . ' AS DATE)';
			        }

		        }

	        }

	        $params->orderby = (array) $params->orderby;
        } else {
	        $params->orderby = array();
        }


        if ( false === $params->strict && !empty( $this->orderby ) )
            $params->orderby = array_merge( $params->orderby, (array) $this->orderby );

        if ( !empty( $params->traverse ) )
            $this->traverse = $params->traverse;

        $allowed_search_modes = array( 'int', 'text', 'text_like' );

        if ( !empty( $params->search_mode ) && in_array( $params->search_mode, $allowed_search_modes ) )
            $this->search_mode = $params->search_mode;

        $params->search = (boolean) $params->search;

        if ( 1 == pods_v( 'pods_debug_params_all', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
            pods_debug( $params );

        $params->field_table_alias = 't';

        // Get Aliases for future reference
        $selectsfound = '';

        if ( !empty( $params->select ) ) {
            if ( is_array( $params->select ) )
                $selectsfound = implode( ', ', $params->select );
            else
                $selectsfound = $params->select;
        }

        // Pull Aliases from SQL query too
        if ( null !== $params->sql ) {
            $temp_sql = ' ' . trim( str_replace( array( "\n", "\r" ), ' ', $params->sql ) );
            $temp_sql = preg_replace( array(
                    '/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
                    '/\sSELECT\s/i'
                ),
                array(
                    ' SELECT ',
                    ' SELECT SQL_CALC_FOUND_ROWS '
                ),
                $temp_sql );
            preg_match( '/\sSELECT SQL_CALC_FOUND_ROWS\s(.*)\sFROM/i', $temp_sql, $selectmatches );
            if ( isset( $selectmatches[ 1 ] ) && !empty( $selectmatches[ 1 ] ) && false !== stripos( $selectmatches[ 1 ], ' AS ' ) )
                $selectsfound .= ( !empty( $selectsfound ) ? ', ' : '' ) . $selectmatches[ 1 ];
        }

        // Build Alias list
        $this->aliases = array();

        if ( !empty( $selectsfound ) && false !== stripos( $selectsfound, ' AS ' ) ) {
            $theselects = array_filter( explode( ', ', $selectsfound ) );

            if ( empty( $theselects ) )
                $theselects = array_filter( explode( ',', $selectsfound ) );

            foreach ( $theselects as $selected ) {
                $selected = trim( $selected );

                if ( strlen( $selected ) < 1 )
                    continue;

                $selectfield = explode( ' AS ', str_replace( ' as ', ' AS ', $selected ) );

                if ( 2 == count( $selectfield ) ) {
                    $field = trim( trim( $selectfield[ 1 ] ), '`' );
                    $real_field = trim( trim( $selectfield[ 0 ] ), '`' );
                    $this->aliases[ $field ] = $real_field;
                }
            }
        }

        // Search
        if ( !empty( $params->search ) && !empty( $params->fields ) ) {
            if ( false !== $params->search_query && 0 < strlen( $params->search_query ) ) {
                $where = $having = array();

                if ( false !== $params->search_across ) {
                    foreach ( $params->fields as $key => $field ) {
                        if ( is_array( $field ) ) {
                            $attributes = $field;
                            $field = pods_v( 'name', $field, $key, true );
                        }
                        else {
                            $attributes = array(
                                'type' => '',
                                'options' => array()
                            );
                        }

                        if ( isset( $attributes[ 'options' ][ 'search' ] ) && !$attributes[ 'options' ][ 'search' ] )
                            continue;

                        if ( in_array( $attributes[ 'type' ], array( 'date', 'time', 'datetime', 'number', 'decimal', 'currency', 'phone', 'password', 'boolean' ) ) )
                            continue;

                        $fieldfield = '`' . $field . '`';

                        if ( 'pick' == $attributes[ 'type' ] && !in_array( pods_v( 'pick_object', $attributes ), $simple_tableless_objects ) ) {
                            if ( false === $params->search_across_picks )
                                continue;
                            else {
                                if ( empty( $attributes[ 'table_info' ] ) )
                                    $attributes[ 'table_info' ] = $this->api->get_table_info( pods_v( 'pick_object', $attributes ), pods_v( 'pick_val', $attributes ) );

                                if ( empty( $attributes[ 'table_info' ][ 'field_index' ] ) )
                                    continue;

                                $fieldfield = $fieldfield . '.`' . $attributes[ 'table_info' ][ 'field_index' ] . '`';
                            }
                        }
                        elseif ( in_array( $attributes[ 'type' ], $file_field_types ) ) {
                            if ( false === $params->search_across_files )
                                continue;
                            else
                                $fieldfield = $fieldfield . '.`post_title`';
                        }
                        elseif ( isset( $params->fields[ $field ] ) ) {
                            if ( $params->meta_fields )
                                $fieldfield = $fieldfield . '.`meta_value`';
                            else
                                $fieldfield = '`' . $params->pod_table_prefix . '`.' . $fieldfield;
                        }
                        elseif ( !empty( $params->object_fields ) && !isset( $params->object_fields[ $field ] ) && 'meta' == $pod['storage'] )
	                        $fieldfield = $fieldfield . '.`meta_value`';
                        else
	                        $fieldfield = '`t`.' . $fieldfield;

                        if ( isset( $this->aliases[ $field ] ) )
                            $fieldfield = '`' . $this->aliases[ $field ] . '`';

                        if ( !empty( $attributes[ 'real_name' ] ) )
                            $fieldfield = $attributes[ 'real_name' ];

                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                            $having[] = "{$fieldfield} LIKE '%" . pods_sanitize_like( $params->search_query ) . "%'";
                        else
                            $where[] = "{$fieldfield} LIKE '%" . pods_sanitize_like( $params->search_query ) . "%'";
                    }
                }
                elseif ( !empty( $params->index ) ) {
                    $attributes = array();

                    $fieldfield = '`t`.`' . $params->index . '`';

                    if ( isset( $params->fields[ $params->index ] ) ) {
                        if ( $params->meta_fields )
                            $fieldfield = '`' . $params->index . '`.`' . $params->pod_table_prefix . '`';
                        else
                            $fieldfield = '`' . $params->pod_table_prefix . '`.`' . $params->index . '`';
                    }
                    elseif ( !empty( $params->object_fields ) && !isset( $params->object_fields[ $params->index ] ) && 'meta' == $pod['storage'] )
                        $fieldfield = '`' . $params->index . '`.`meta_value`';

                    if ( isset( $attributes[ 'real_name' ] ) && false !== $attributes[ 'real_name' ] && !empty( $attributes[ 'real_name' ] ) )
                        $fieldfield = $attributes[ 'real_name' ];

                    if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                        $having[] = "{$fieldfield} LIKE '%" . pods_sanitize_like( $params->search_query ) . "%'";
                    else
                        $where[] = "{$fieldfield} LIKE '%" . pods_sanitize_like( $params->search_query ) . "%'";
                }

                if ( !empty( $where ) )
                    $params->where[] = '( ' . implode( ' OR ', $where ) . ' )';

                if ( !empty( $having ) )
                    $params->having[] = '( ' . implode( ' OR ', $having ) . ' )';
            }

            // Filter
            foreach ( $params->filters as $filter ) {
                $where = $having = array();

                if ( !isset( $params->fields[ $filter ] ) )
                    continue;

                $attributes = $params->fields[ $filter ];
                $field = pods_v( 'name', $attributes, $filter, true );

                $filterfield = '`' . $field . '`';

                if ( 'pick' == $attributes[ 'type' ] && !in_array( pods_v( 'pick_object', $attributes ), $simple_tableless_objects ) ) {
                    if ( empty( $attributes[ 'table_info' ] ) )
                        $attributes[ 'table_info' ] = $this->api->get_table_info( pods_v( 'pick_object', $attributes ), pods_v( 'pick_val', $attributes ) );

                    if ( empty( $attributes[ 'table_info' ][ 'field_index' ] ) )
                        continue;

                    $filterfield = $filterfield . '.`' . $attributes[ 'table_info' ][ 'field_index' ] . '`';
                }
                elseif ( in_array( $attributes[ 'type' ], $file_field_types ) )
                    $filterfield = $filterfield . '.`post_title`';
                elseif ( isset( $params->fields[ $field ] ) ) {
                    if ( $params->meta_fields && 'meta' == $pod['storage'] )
                        $filterfield = $filterfield . '.`meta_value`';
                    else
                        $filterfield = '`' . $params->pod_table_prefix . '`.' . $filterfield;
                }
                elseif ( !empty( $params->object_fields ) && !isset( $params->object_fields[ $field ] ) && 'meta' == $pod['storage'] )
                    $filterfield = $filterfield . '.`meta_value`';
                else
                    $filterfield = '`t`.' . $filterfield;

                if ( isset( $this->aliases[ $field ] ) )
                    $filterfield = '`' . $this->aliases[ $field ] . '`';

                if ( !empty( $attributes[ 'real_name' ] ) )
                    $filterfield = $attributes[ 'real_name' ];

                if ( 'pick' == $attributes[ 'type' ] ) {
                    $filter_value = pods_v( 'filter_' . $field, 'get' );

                    if ( !is_array( $filter_value ) )
                        $filter_value = (array) $filter_value;

                    foreach ( $filter_value as $filter_v ) {
                        if ( in_array( pods_v( 'pick_object', $attributes ), $simple_tableless_objects ) ) {
                            if ( strlen( $filter_v ) < 1 )
                                continue;

                            if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] ) {
                                $having[] = "( {$filterfield} = '" . pods_sanitize( $filter_v ) . "'"
                                             . " OR {$filterfield} LIKE '%\"" . pods_sanitize_like( $filter_v ) . "\"%' )";
                            }
                            else {
                                $where[] = "( {$filterfield} = '" . pods_sanitize( $filter_v ) . "'"
                                            . " OR {$filterfield} LIKE '%\"" . pods_sanitize_like( $filter_v ) . "\"%' )";
                            }
                        }
                        else {
                            $filter_v = (int) $filter_v;

                            if ( empty( $filter_v ) || empty( $attributes[ 'table_info' ] ) || empty( $attributes[ 'table_info' ][ 'field_id' ] ) )
                                continue;

                            $filterfield = '`' . $field . '`.`' . $attributes[ 'table_info' ][ 'field_id' ] . '`';

                            if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                                $having[] = "{$filterfield} = " . $filter_v;
                            else
                                $where[] = "{$filterfield} = " . $filter_v;
                        }
                    }
                }
                elseif ( in_array( $attributes[ 'type' ], array( 'date', 'datetime' ) ) ) {
                    $start_value = pods_v( 'filter_' . $field . '_start', 'get', false );
                    $end_value = pods_v( 'filter_' . $field . '_end', 'get', false );

                    if ( empty( $start_value ) && empty( $end_value ) )
                        continue;

	                if ( !empty( $start_value ) )
                        $start = date_i18n( 'Y-m-d', strtotime( $start_value ) ) . ( 'datetime' == $attributes[ 'type' ] ? ' 00:00:00' : '' );
	                else
		                $start = date_i18n( 'Y-m-d' ) . ( 'datetime' == $attributes[ 'type' ] ) ? ' 00:00:00' : '';

                    if ( !empty( $end_value ) )
                        $end = date_i18n( 'Y-m-d', strtotime( $end_value ) ) . ( 'datetime' == $attributes[ 'type' ] ? ' 23:59:59' : '' );
	                else
		                $end = date_i18n( 'Y-m-d' ) . ( 'datetime' == $attributes[ 'type' ] ) ? ' 23:59:59' : '';

                    if ( isset( $attributes[ 'date_ongoing' ] ) && true === $attributes[ 'date_ongoing' ] ) {
                        $date_ongoing = '`' . $attributes[ 'date_ongoing' ] . '`';

                        if ( isset( $this->aliases[ $date_ongoing ] ) )
                            $date_ongoing = '`' . $this->aliases[ $date_ongoing ] . '`';

                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                            $having[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
                        else
                            $where[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
                    }
                    else {
                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                            $having[] = "({$filterfield} BETWEEN '$start' AND '$end')";
                        else
                            $where[] = "({$filterfield} BETWEEN '$start' AND '$end')";
                    }
                }
                else {
                    $filter_value = pods_v( 'filter_' . $field, 'get', '' );

                    if ( strlen( $filter_value ) < 1 )
                        continue;

                    if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                        $having[] = "{$filterfield} LIKE '%" . pods_sanitize_like( $filter_value ) . "%'";
                    else
                        $where[] = "{$filterfield} LIKE '%" . pods_sanitize_like( $filter_value ) . "%'";
                }

                if ( !empty( $where ) )
                    $params->where[] = implode( ' AND ', $where );

                if ( !empty( $having ) )
                    $params->having[] = implode( ' AND ', $having );
            }
        }

        // Traverse the Rabbit Hole
        if ( !empty( $this->pod ) ) {
            $haystack = implode( ' ', (array) $params->select )
                        . ' ' . implode( ' ', (array) $params->where )
                        . ' ' . implode( ' ', (array) $params->groupby )
                        . ' ' . implode( ' ', (array) $params->having )
                        . ' ' . implode( ' ', (array) $params->orderby );
            $haystack = preg_replace( '/\s/', ' ', $haystack );
            $haystack = preg_replace( '/\w\(/', ' ', $haystack );
            $haystack = str_replace( array( '(', ')', '  ', '\\\'', "\\\"" ), ' ', $haystack );

            preg_match_all( '/`?[\w\-]+`?(?:\\.`?[\w\-]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER );

            $found = (array) @current( $found );
            $find = $replace = $traverse = array();

            foreach ( $found as $key => $value ) {
                $value = str_replace( '`', '', $value );
                $value = explode( '.', $value );
                $dot = $last_value = array_pop( $value );

                if ( 't' == $value[ 0 ] )
                    continue;
                elseif ( array_key_exists( $value[ 0 ], $params->join ) )
	                // Don't traverse for tables we are already going to join
	                continue;
                elseif ( 1 == count( $value ) && '' == preg_replace( '/[0-9]*/', '', $value[ 0 ] ) && '' == preg_replace( '/[0-9]*/', '', $last_value ) )
                    continue;

	            $found_value = str_replace( '`', '', $found[ $key ] );
	            $found_value = '([`]{1}|\b)' . str_replace( '.', '[`]*\.[`]*', $found_value ) . '([`]{1}|\b)';
	            $found_value = '/' . $found_value . '(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/';

	            if ( in_array( $found_value, $find ) ) {
		            continue;
	            }

                $find[ $key ] = $found_value;

                if ( '*' != $dot )
                    $dot = '`' . $dot . '`';

                $replace[ $key ] = '`' . implode( '_', $value ) . '`.' . $dot;

                $value[] = $last_value;

                if ( !in_array( $value, $traverse ) )
                    $traverse[ $key ] = $value;
            }

            if ( !empty( $this->traverse ) ) {
                foreach ( (array) $this->traverse as $key => $traverse ) {
                    $traverse = str_replace( '`', '', $traverse );
                    $already_found = false;

                    foreach ( $traverse as $traversal ) {
                        if ( is_array( $traversal ) )
                            $traversal = implode( '.', $traversal );

                        if ( $traversal == $traverse ) {
                            $already_found = true;
                            break;
                        }
                    }

                    if ( !$already_found )
                        $traverse[ 'traverse_' . $key ] = explode( '.', $traverse );
                }
            }

            $joins = array();

            if ( !empty( $find ) ) {
                // See: "#3294 OrderBy Failing on PHP7"  Non-zero array keys
                // here in PHP 7 cause odd behavior so just strip the keys
                $find = array_values( $find );
                $replace = array_values( $replace );

                $params->select = preg_replace( $find, $replace, $params->select );
                $params->where = preg_replace( $find, $replace, $params->where );
                $params->groupby = preg_replace( $find, $replace, $params->groupby );
                $params->having = preg_replace( $find, $replace, $params->having );
                $params->orderby = preg_replace( $find, $replace, $params->orderby );

                if ( !empty( $traverse ) )
                    $joins = $this->traverse( $traverse, $params->fields, $params );
                elseif ( false !== $params->search )
                    $joins = $this->traverse( null, $params->fields, $params );
            }
        }

        // Traversal Search
        if ( !empty( $params->search ) && !empty( $this->search_where ) )
            $params->where = array_merge( (array) $this->search_where, $params->where );

        if ( !empty( $params->join ) && !empty( $joins ) )
            $params->join = array_merge( $joins, (array) $params->join );
        elseif ( !empty( $joins ) )
            $params->join = $joins;

        // Build
        if ( null === $params->sql ) {
            $sql = "
                SELECT
                " . ( $params->calc_rows ? 'SQL_CALC_FOUND_ROWS' : '' ) . "
                " . ( $params->distinct ? 'DISTINCT' : '' ) . "
                " . ( !empty( $params->select ) ? ( is_array( $params->select ) ? implode( ', ', $params->select ) : $params->select ) : '*' ) . "
                FROM {$params->table} AS `t`
                " . ( !empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . "
                " . ( !empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND ', $params->where ) : $params->where ) : '' ) . "
                " . ( !empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . "
                " . ( !empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND  ', $params->having ) : $params->having ) : '' ) . "
                " . ( !empty( $params->orderby ) ? 'ORDER BY ' . ( is_array( $params->orderby ) ? implode( ', ', $params->orderby ) : $params->orderby ) : '' ) . "
                " . ( ( 0 < $params->page && 0 < $params->limit ) ? 'LIMIT ' . $params->offset . ', ' . ( $params->limit ) : '' ) . "
            ";

			if ( !$params->calc_rows ) {
				// Handle COUNT() SELECT
				$total_sql_select = "COUNT( " . ( $params->distinct ? 'DISTINCT `t`.`' . $params->id . '`' : '*' ) . " )";

				// If 'having' is set, we have to select all so it has access to anything it needs
				if ( ! empty( $params->having ) ) {
					$total_sql_select .= ', ' . ( !empty( $params->select ) ? ( is_array( $params->select ) ? implode( ', ', $params->select ) : $params->select ) : '*' );
				}

				$this->total_sql = "
					SELECT {$total_sql_select}
					FROM {$params->table} AS `t`
					" . ( !empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . "
					" . ( !empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND  ', $params->where ) : $params->where ) : '' ) . "
					" . ( !empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . "
					" . ( !empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND  ', $params->having ) : $params->having ) : '' ) . "
				";
			}
        }
        // Rewrite
        else {
            $sql = ' ' . trim( str_replace( array( "\n", "\r" ), ' ', $params->sql ) );
            $sql = preg_replace( array(
                    '/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
                    '/\sSELECT\s/i'
                ),
                array(
                    ' SELECT ',
                    ' SELECT SQL_CALC_FOUND_ROWS '
                ),
                $sql );

            // Insert variables based on existing statements
            if ( false === stripos( $sql, '%%SELECT%%' ) )
                $sql = preg_replace( '/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i', ' SELECT SQL_CALC_FOUND_ROWS %%SELECT%% ', $sql );
            if ( false === stripos( $sql, '%%WHERE%%' ) )
                $sql = preg_replace( '/\sWHERE\s(?!.*\sWHERE\s)/i', ' WHERE %%WHERE%% ', $sql );
            if ( false === stripos( $sql, '%%GROUPBY%%' ) )
                $sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' GROUP BY %%GROUPBY%% ', $sql );
            if ( false === stripos( $sql, '%%HAVING%%' ) )
                $sql = preg_replace( '/\sHAVING\s(?!.*\sHAVING\s)/i', ' HAVING %%HAVING%% ', $sql );
            if ( false === stripos( $sql, '%%ORDERBY%%' ) )
                $sql = preg_replace( '/\sORDER BY\s(?!.*\sORDER BY\s)/i', ' ORDER BY %%ORDERBY%% ', $sql );

            // Insert variables based on other existing statements
            if ( false === stripos( $sql, '%%JOIN%%' ) ) {
                if ( false !== stripos( $sql, ' WHERE ' ) )
                    $sql = preg_replace( '/\sWHERE\s(?!.*\sWHERE\s)/i', ' %%JOIN%% WHERE ', $sql );
                elseif ( false !== stripos( $sql, ' GROUP BY ' ) )
                    $sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' %%WHERE%% GROUP BY ', $sql );
                elseif ( false !== stripos( $sql, ' ORDER BY ' ) )
                    $sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%WHERE%% ORDER BY ', $sql );
                else
                    $sql .= ' %%JOIN%% ';
            }
            if ( false === stripos( $sql, '%%WHERE%%' ) ) {
                if ( false !== stripos( $sql, ' GROUP BY ' ) )
                    $sql = preg_replace( '/\sGROUP BY\s(?!.*\sGROUP BY\s)/i', ' %%WHERE%% GROUP BY ', $sql );
                elseif ( false !== stripos( $sql, ' ORDER BY ' ) )
                    $sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%WHERE%% ORDER BY ', $sql );
                else
                    $sql .= ' %%WHERE%% ';
            }
            if ( false === stripos( $sql, '%%GROUPBY%%' ) ) {
                if ( false !== stripos( $sql, ' HAVING ' ) )
                    $sql = preg_replace( '/\sHAVING\s(?!.*\sHAVING\s)/i', ' %%GROUPBY%% HAVING ', $sql );
                elseif ( false !== stripos( $sql, ' ORDER BY ' ) )
                    $sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%GROUPBY%% ORDER BY ', $sql );
                else
                    $sql .= ' %%GROUPBY%% ';
            }
            if ( false === stripos( $sql, '%%HAVING%%' ) ) {
                if ( false !== stripos( $sql, ' ORDER BY ' ) )
                    $sql = preg_replace( '/\ORDER BY\s(?!.*\ORDER BY\s)/i', ' %%HAVING%% ORDER BY ', $sql );
                else
                    $sql .= ' %%HAVING%% ';
            }
            if ( false === stripos( $sql, '%%ORDERBY%%' ) )
                $sql .= ' %%ORDERBY%% ';
            if ( false === stripos( $sql, '%%LIMIT%%' ) )
                $sql .= ' %%LIMIT%% ';

            // Replace variables
            if ( 0 < strlen( $params->select ) ) {
                if ( false === stripos( $sql, '%%SELECT%% FROM ' ) )
                    $sql = str_ireplace( '%%SELECT%%', $params->select . ', ', $sql );
                else
                    $sql = str_ireplace( '%%SELECT%%', $params->select, $sql );
            }
            if ( 0 < strlen( $params->join ) )
                $sql = str_ireplace( '%%JOIN%%', $params->join, $sql );
            if ( 0 < strlen( $params->where ) ) {
                if ( false !== stripos( $sql, ' WHERE ' ) ) {
                    if ( false !== stripos( $sql, ' WHERE %%WHERE%% ' ) )
                        $sql = str_ireplace( '%%WHERE%%', $params->where . ' AND ', $sql );
                    else
                        $sql = str_ireplace( '%%WHERE%%', ' AND ' . $params->where, $sql );
                }
                else
                    $sql = str_ireplace( '%%WHERE%%', ' WHERE ' . $params->where, $sql );
            }
            if ( 0 < strlen( $params->groupby ) ) {
                if ( false !== stripos( $sql, ' GROUP BY ' ) ) {
                    if ( false !== stripos( $sql, ' GROUP BY %%GROUPBY%% ' ) )
                        $sql = str_ireplace( '%%GROUPBY%%', $params->groupby . ', ', $sql );
                    else
                        $sql = str_ireplace( '%%GROUPBY%%', ', ' . $params->groupby, $sql );
                }
                else
                    $sql = str_ireplace( '%%GROUPBY%%', ' GROUP BY ' . $params->groupby, $sql );
            }
            if ( 0 < strlen( $params->having ) && false !== stripos( $sql, ' GROUP BY ' ) ) {
                if ( false !== stripos( $sql, ' HAVING ' ) ) {
                    if ( false !== stripos( $sql, ' HAVING %%HAVING%% ' ) )
                        $sql = str_ireplace( '%%HAVING%%', $params->having . ' AND ', $sql );
                    else
                        $sql = str_ireplace( '%%HAVING%%', ' AND ' . $params->having, $sql );
                }
                else
                    $sql = str_ireplace( '%%HAVING%%', ' HAVING ' . $params->having, $sql );
            }
            if ( 0 < strlen( $params->orderby ) ) {
                if ( false !== stripos( $sql, ' ORDER BY ' ) ) {
                    if ( false !== stripos( $sql, ' ORDER BY %%ORDERBY%% ' ) )
                        $sql = str_ireplace( '%%ORDERBY%%', $params->groupby . ', ', $sql );
                    else
                        $sql = str_ireplace( '%%ORDERBY%%', ', ' . $params->groupby, $sql );
                }
                else
                    $sql = str_ireplace( '%%ORDERBY%%', ' ORDER BY ' . $params->groupby, $sql );
            }
            if ( 0 < $params->page && 0 < $params->limit ) {
                $start = ( $params->page - 1 ) * $params->limit;
                $end = $start + $params->limit;
                $sql .= 'LIMIT ' . (int) $start . ', ' . (int) $end;
            }

            // Clear any unused variables
            $sql = str_ireplace( array(
                '%%SELECT%%',
                '%%JOIN%%',
                '%%WHERE%%',
                '%%GROUPBY%%',
                '%%HAVING%%',
                '%%ORDERBY%%',
                '%%LIMIT%%'
            ), '', $sql );
            $sql = str_replace( array( '``', '`' ), array( '  ', ' ' ), $sql );
        }

        return $sql;
    }

    /**
     * Fetch the total row count returned
     *
     * @return int Number of rows returned by select()
     * @since 2.0
     */
    public function total () {
        return (int) $this->total;
    }

    /**
     * Fetch the total row count total
     *
     * @return int Number of rows found by select()
     * @since 2.0
     */
    public function total_found () {
        if(false === $this->total_found_calculated)
            $this->calculate_totals();

        return (int) $this->total_found;
    }

    /**
     * Fetch the zebra state
     *
     * @return bool Zebra state
     * @since 1.12
     * @see PodsData::nth
     */
    public function zebra () {
        return $this->nth( 'odd' ); // Odd numbers
    }

    /**
     * Fetch the nth state
     *
     * @param int|string $nth The $nth to match on the PodsData::row_number
     *
     * @return bool Whether $nth matches
     * @since 2.3
     */
    public function nth ( $nth ) {
        if ( empty( $nth ) )
            $nth = 2;

        $offset = 0;
        $negative = false;

        if ( 'even' == $nth )
            $nth = 2;
        elseif ( 'odd' == $nth ) {
            $negative = true;
            $nth = 2;
        }
        elseif ( false !== strpos( $nth, '+' ) ) {
            $nth = explode( '+', $nth );

            if ( isset( $nth[ 1 ] ) )
                $offset += (int) trim( $nth[ 1 ] );

            $nth = (int) trim( $nth[ 0 ], ' n' );
        }
        elseif ( false !== strpos( $nth, '-' ) ) {
            $nth = explode( '-', $nth );

            if ( isset( $nth[ 1 ] ) )
                $offset -= (int) trim( $nth[ 1 ] );

            $nth = (int) trim( $nth[ 0 ], ' n' );
        }

        $nth = (int) $nth;
        $offset = (int) $offset;

        if ( 0 == ( ( $this->row_number % $nth ) + $offset ) )
            return ( $negative ? false: true );

        return ( $negative ? true : false );
    }

    /**
     * Fetch the current position in the loop (starting at 1)
     *
     * @return int Current row number (+1)
     * @since 2.3
     */
    public function position () {
        return $this->row_number + 1;
    }

    /**
     * Create a Table
     *
     * @param string $table Table name
     * @param string $fields
     * @param boolean $if_not_exists Check if the table exists.
     *
     * @return array|bool|mixed|null|void
     *
     * @uses PodsData::query
     *
     * @since 2.0
     */
    public static function table_create ( $table, $fields, $if_not_exists = false ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $sql = "CREATE TABLE";

        if ( true === $if_not_exists )
            $sql .= " IF NOT EXISTS";

        $sql .= " `{$wpdb->prefix}" . self::$prefix . "{$table}` ({$fields})";

        if ( !empty( $wpdb->charset ) )
            $sql .= " DEFAULT CHARACTER SET {$wpdb->charset}";

        if ( !empty( $wpdb->collate ) )
            $sql .= " COLLATE {$wpdb->collate}";

        return self::query( $sql );
    }

    /**
     * Alter a Table
     *
     * @param string $table Table name
     * @param string $changes
     *
     * @return array|bool|mixed|null|void
     *
     * @uses PodsData::query
     *
     * @since 2.0
     */
    public static function table_alter ( $table, $changes ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $sql = "ALTER TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}` {$changes}";

        return self::query( $sql );
    }

    /**
     * Truncate a Table
     *
     * @param string $table Table name
     *
     * @return array|bool|mixed|null|void
     *
     * @uses PodsData::query
     *
     * @since 2.0
     */
    public static function table_truncate ( $table ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $sql = "TRUNCATE TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}`";

        return self::query( $sql );
    }

    /**
     * Drop a Table
     *
     * @param string $table Table name
     *
     * @uses PodsData::query
     *
     * @return array|bool|mixed|null|void
     *
     * @uses PodsData::query
     *
     * @since 2.0
     */
    public static function table_drop ( $table ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $sql = "DROP TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}`";

        return self::query( $sql );
    }

    /**
     * Reorder Items
     *
     * @param string $table Table name
     * @param string $weight_field
     * @param string $id_field
     * @param array $ids
     *
     * @return bool
     *
     * @uses PodsData::update
     *
     * @since 2.0
     */
    public function reorder ( $table, $weight_field, $id_field, $ids ) {
        $success = false;
        $ids = (array) $ids;

        list( $table, $weight_field, $id_field, $ids ) = $this->do_hook( 'reorder', array(
            $table,
            $weight_field,
            $id_field,
            $ids
        ) );

        if ( !empty( $ids ) ) {
            $success = true;

            foreach ( $ids as $weight => $id ) {
                $updated = $this->update( $table, array( $weight_field => $weight ), array( $id_field => $id ), array( '%d' ), array( '%d' ) );

                if ( false === $updated )
                    $success = false;
            }
        }

        return $success;
    }

    /**
     * Fetch a new row for the current pod_data
     *
     * @param int $row Row number to fetch
     * @param bool $explicit_set Whether to set explicitly (use false when in loop)
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function fetch ( $row = null, $explicit_set = true ) {
        global $wpdb;

        if ( null === $row )
            $explicit_set = false;

	    $already_cached = false;
        $id = $row;

	    $tableless_field_types = PodsForm::tableless_field_types();

        if ( null === $row ) {
            $this->row_number++;

            $this->row = false;

            if ( isset( $this->data[ $this->row_number ] ) ) {
                $this->row = get_object_vars( $this->data[ $this->row_number ] );

                $current_row_id = false;

                if ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) )
                    $current_row_id = pods_var_raw( 'ID', $this->row );
                elseif ( 'taxonomy' == $this->pod_data[ 'type' ] )
                    $current_row_id = pods_var_raw( 'term_id', $this->row );
                elseif ( 'user' == $this->pod_data[ 'type' ] )
                    $current_row_id = pods_var_raw( 'ID', $this->row );
                elseif ( 'comment' == $this->pod_data[ 'type' ] )
                    $current_row_id = pods_var_raw( 'comment_ID', $this->row );
                elseif ( 'settings' == $this->pod_data[ 'type' ] )
                    $current_row_id = $this->pod_data[ 'id' ];

                if ( 0 < $current_row_id )
                    $row = $current_row_id;
            }
        }

        if ( null !== $row || 'settings' == $this->pod_data[ 'type' ] ) {
            if ( $explicit_set )
                $this->row_number = -1;

            $mode = 'id';
            $id = pods_absint( $row );

            if ( !is_numeric( $row ) || 0 === strpos( $row, '0' ) || $row != preg_replace( '/[^0-9]/', '', $row ) ) {
                $mode = 'slug';
                $id = $row;
            }

            $row = false;

            if ( !empty( $this->pod ) ) {
                $row = pods_cache_get( $id, 'pods_items_' . $this->pod );
	            if ( false !== $row ) {
		            $already_cached = true;
	            }
			}

            $current_row_id = false;
            $get_table_data = false;

			$old_row = $this->row;

            if ( false !== $row && is_array( $row ) )
                $this->row = $row;
            elseif ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) {
                if ( 'post_type' == $this->pod_data[ 'type' ] ) {
                    if ( empty( $this->pod_data[ 'object' ] ) ) {
                        $post_type = $this->pod_data[ 'name' ];
                    }
                    else {
                        $post_type = $this->pod_data[ 'object' ];
                    }
                }
                else
                    $post_type = 'attachment';

                if ( 'id' == $mode ) {
                    $this->row = get_post( $id, ARRAY_A );

                    if ( is_array( $this->row ) && $this->row[ 'post_type' ] != $post_type )
                        $this->row = false;
                }
                else {
                    $args = array(
                        'post_type' => $post_type,
                        'name' => $id,
                        'numberposts' => 5
                    );

                    $find = get_posts( $args );

                    if ( !empty( $find ) )
                        $this->row = get_object_vars( $find[ 0 ] );
                }

                if ( is_wp_error( $this->row ) || empty( $this->row ) )
                    $this->row = false;
                else
                    $current_row_id = $this->row[ 'ID' ];

                $get_table_data = true;
            }
            elseif ( 'taxonomy' == $this->pod_data[ 'type' ] ) {
                $taxonomy = $this->pod_data[ 'object' ];

                if ( empty( $taxonomy ) )
                    $taxonomy = $this->pod_data[ 'name' ];

                // Taxonomies are registered during init, so they aren't available before then
                if ( !did_action( 'init' ) ) {
                    // hackaround :(
                    if ( 'id' == $mode )
                        $term_where = 't.term_id = %d';
                    else
                        $term_where = 't.slug = %s';

                    $filter = 'raw';
                    $term = $id;

                    if ( 'id' != $mode || !$_term = wp_cache_get( $term, $taxonomy ) ) {
                        $_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND {$term_where} LIMIT 1", $taxonomy, $term ) );

                        if ( $_term )
                            wp_cache_add( $term, $_term, $taxonomy );
                    }

                    $_term = apply_filters( 'get_term', $_term, $taxonomy );
                    $_term = apply_filters( "get_$taxonomy", $_term, $taxonomy );
                    $_term = sanitize_term( $_term, $taxonomy, $filter );

                    if ( is_object( $_term ) )
                        $this->row = get_object_vars( $_term );
                }
                elseif ( 'id' == $mode )
                    $this->row = get_term( $id, $taxonomy, ARRAY_A );
                else
                    $this->row = get_term_by( 'slug', $id, $taxonomy, ARRAY_A );

                if ( is_wp_error( $this->row ) || empty( $this->row ) )
                    $this->row = false;
                else
                    $current_row_id = $this->row[ 'term_id' ];

                $get_table_data = true;
            }
            elseif ( 'user' == $this->pod_data[ 'type' ] ) {
                if ( 'id' == $mode )
                    $this->row = get_userdata( $id );
                else
                    $this->row = get_user_by( 'slug', $id );

                if ( is_wp_error( $this->row ) || empty( $this->row ) )
                    $this->row = false;
                else {
                    // Get other vars
                    $roles = $this->row->roles;
                    $caps = $this->row->caps;
                    $allcaps = $this->row->allcaps;

                    $this->row = get_object_vars( $this->row->data );

                    // Set other vars
                    $this->row[ 'roles' ] = $roles;
                    $this->row[ 'caps' ] = $caps;
                    $this->row[ 'allcaps' ] = $allcaps;

                    unset( $this->row[ 'user_pass' ] );

                    $current_row_id = $this->row[ 'ID' ];
                }

                $get_table_data = true;
            }
            elseif ( 'comment' == $this->pod_data[ 'type' ] ) {
                $this->row = get_comment( $id, ARRAY_A );

                // No slug handling here

                if ( is_wp_error( $this->row ) || empty( $this->row ) )
                    $this->row = false;
                else
                    $current_row_id = $this->row[ 'comment_ID' ];

                $get_table_data = true;
            }
            elseif ( 'settings' == $this->pod_data[ 'type' ] ) {
                $this->row = array();

                if ( empty( $this->fields ) )
                    $this->row = false;
                else {
                    foreach ( $this->fields as $field ) {
                        if ( !in_array( $field[ 'type' ], $tableless_field_types ) )
                            $this->row[ $field[ 'name' ] ] = get_option( $this->pod_data[ 'name' ] . '_' . $field[ 'name' ], null );
                    }

                    // Force ID
                    $this->id = $this->pod_data[ 'id' ];
                    $this->row[ 'option_id' ] = $this->id;
                }
            }
            else {
                $params = array(
                    'table' => $this->table,
                    'where' => "`t`.`{$this->field_id}` = " . (int) $id,
                    'orderby' => "`t`.`{$this->field_id}` DESC",
                    'page' => 1,
                    'limit' => 1,
                    'search' => false
                );

                if ( 'slug' == $mode && !empty( $this->field_slug ) ) {
                    $id = pods_sanitize( $id );
                    $params[ 'where' ] = "`t`.`{$this->field_slug}` = '{$id}'";
                }

                $this->row = pods_data()->select( $params );

                if ( empty( $this->row ) )
                    $this->row = false;
                else {
                    $current_row = (array) $this->row;
                    $this->row = get_object_vars( (object) @current( $current_row ) );
                }
            }

			if ( !$explicit_set && !empty( $this->row ) && is_array( $this->row ) && !empty( $old_row ) ) {
				$this->row = array_merge( $old_row, $this->row );
			}

            if ( 'table' == $this->pod_data[ 'storage' ] && false !== $get_table_data && is_numeric( $current_row_id ) ) {
                $params = array(
                    'table' => $wpdb->prefix . "pods_",
                    'where' => "`t`.`id` = {$current_row_id}",
                    'orderby' => "`t`.`id` DESC",
                    'page' => 1,
                    'limit' => 1,
                    'search' => false,
                    'strict' => true
                );

                if ( empty( $this->pod_data[ 'object' ] ) )
                    $params[ 'table' ] .= $this->pod_data[ 'name' ];
                else
                    $params[ 'table' ] .= $this->pod_data[ 'object' ];

                $row = pods_data()->select( $params );

                if ( !empty( $row ) ) {
                    $current_row = (array) $row;
                    $row = get_object_vars( (object) @current( $current_row ) );

                    if ( is_array( $this->row ) && !empty( $this->row ) )
                        $this->row = array_merge( $this->row, $row );
                    else
                        $this->row = $row;
                }
            }

            if ( !empty( $this->pod ) && ! $already_cached ) {
                pods_cache_set( $id, $this->row, 'pods_items_' . $this->pod, 0 );
			}
        }

        $this->row = apply_filters( 'pods_data_fetch', $this->row, $id, $this->row_number, $this );

        return $this->row;
    }

    /**
     * Reset the current data
     *
     * @param int $row Row number to reset to
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function reset ( $row = null ) {
        $row = pods_absint( $row );

        $this->row = false;

        if ( isset( $this->data[ $row ] ) )
            $this->row = get_object_vars( $this->data[ $row ] );

        if ( empty( $row ) )
            $this->row_number = -1;
        else
            $this->row_number = $row - 1;

        return $this->row;
    }

    /**
     * @static
     *
     * Do a query on the database
     *
     * @param string|array $sql The SQL to execute
     * @param string $error Error to throw on problems
     * @param null $results_error (optional)
     * @param null $no_results_error (optional)
     *
     * @return array|bool|mixed|null|void Result of the query
     *
     * @since 2.0
     */
    public static function query ( $sql, $error = 'Database Error', $results_error = null, $no_results_error = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( $wpdb->show_errors )
            self::$display_errors = true;

        $display_errors = self::$display_errors;

        if ( is_object( $error ) ) {
            if ( isset( $error->display_errors ) && false === $error->display_errors )
                $display_errors = false;

            $error = 'Database Error';
        }
        elseif ( is_bool( $error ) ) {
            $display_errors = $error;

            if ( false !== $error )
                $error = 'Database Error';
        }

        $params = (object) array(
            'sql' => $sql,
            'error' => $error,
            'results_error' => $results_error,
            'no_results_error' => $no_results_error,
            'display_errors' => $display_errors
        );

        // Handle Preparations of Values (sprintf format)
        if ( is_array( $sql ) ) {
            if ( isset( $sql[ 0 ] ) && 1 < count( $sql ) ) {
                if ( 2 == count( $sql ) ) {
                    if ( !is_array( $sql[ 1 ] ) )
                        $sql[ 1 ] = array( $sql[ 1 ] );

                    $params->sql = self::prepare( $sql[ 0 ], $sql[ 1 ] );
                }
                elseif ( 3 == count( $sql ) )
                    $params->sql = self::prepare( $sql[ 0 ], array( $sql[ 1 ], $sql[ 2 ] ) );
                else
                    $params->sql = self::prepare( $sql[ 0 ], array( $sql[ 1 ], $sql[ 2 ], $sql[ 3 ] ) );
            }
            else
                $params = array_merge( $params, $sql );

            if ( 1 == pods_var( 'pods_debug_sql_all', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
                echo '<textarea cols="100" rows="24">' . esc_textarea( str_replace( array( '@wp_users', '@wp_' ), array( $wpdb->users, $wpdb->prefix ), $params->sql ) ) . '</textarea>';
        }

        $params->sql = trim( $params->sql );

        // Run Query
        $params->sql = apply_filters( 'pods_data_query', $params->sql, $params );

        $result = $wpdb->query( $params->sql );

        $result = apply_filters( 'pods_data_query_result', $result, $params );

        if ( false === $result && !empty( $params->error ) && !empty( $wpdb->last_error ) )
            return pods_error( "{$params->error}; SQL: {$params->sql}; Response: {$wpdb->last_error}", $params->display_errors );

        if ( 'INSERT' == strtoupper( substr( $params->sql, 0, 6 ) ) || 'REPLACE' == strtoupper( substr( $params->sql, 0, 7 ) ) )
            $result = $wpdb->insert_id;
        elseif ( preg_match( '/^[\s\r\n\(]*SELECT/', strtoupper( $params->sql ) ) ) {
            $result = (array) $wpdb->last_result;

            if ( !empty( $result ) && !empty( $params->results_error ) )
                return pods_error( $params->results_error, $params->display_errors );
            elseif ( empty( $result ) && !empty( $params->no_results_error ) )
                return pods_error( $params->no_results_error, $params->display_errors );
        }

        return $result;
    }

    /**
     * Gets all tables in the WP database, optionally exclude WP core
     * tables, and/or Pods table by settings the parameters to false.
     *
     * @param boolean $wp_core
     * @param boolean $pods_tables restrict Pods 2.x tables
     *
     * @return array
     *
     * @since 2.0
     */
    public static function get_tables ( $wp_core = true, $pods_tables = true ) {
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
            $wpdb->term_relationships
        );

        $showTables = $wpdb->get_results( 'SHOW TABLES in ' . DB_NAME, ARRAY_A );

        $finalTables = array();

        foreach ( $showTables as $table ) {
            if ( !$pods_tables && 0 === ( strpos( $table[ 0 ], $wpdb->prefix . rtrim( self::$prefix, '_' ) ) ) ) // don't include pods tables
                continue;
            elseif ( !$wp_core && in_array( $table[ 0 ], $core_wp_tables ) )
                continue;
            else
                $finalTables[] = $table[ 0 ];
        }

        return $finalTables;
    }

    /**
     * Gets column information from a table
     *
     * @param string $table Table Name
     *
     * @return array
     *
     * @since 2.0
     */
    public static function get_table_columns ( $table ) {
        global $wpdb;

        self::query( "SHOW COLUMNS FROM `{$table}` " );

        $table_columns = $wpdb->last_result;

        $table_cols_and_types = array();

        foreach ( $table_columns as $table_col ) {
            // Get only the type, not the attributes
            if ( false === strpos( $table_col->Type, '(' ) )
                $modified_type = $table_col->Type;
            else
                $modified_type = substr( $table_col->Type, 0, ( strpos( $table_col->Type, '(' ) ) );

            $table_cols_and_types[ $table_col->Field ] = $modified_type;
        }

        return $table_cols_and_types;
    }

    /**
     * Gets column data information from a table
     *
     * @param string $column_name Column name
     * @param string $table Table name
     *
     * @return array
     *
     * @since 2.0
     */
    public static function get_column_data ( $column_name, $table ) {
	    global $wpdb;

        $column_data = $wpdb->get_results( 'DESCRIBE ' . $table, ARRAY_A );

        foreach ( $column_data as $single_column ) {
            if ( $column_name == $single_column[ 'Field' ] )
                return $single_column;
        }

        return $column_data;
    }

    /**
     * Prepare values for the DB
     *
     * @param string $sql SQL to prepare
     * @param array $data Data to add to the sql prepare statement
     *
     * @return bool|null|string
     *
     * @since 2.0
     */
    public static function prepare ( $sql, $data ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;
        list( $sql, $data ) = apply_filters( 'pods_data_prepare', array( $sql, $data ) );
        return $wpdb->prepare( $sql, $data );
    }

    /**
     * Get the string to use in a query for WHERE/HAVING, uses WP_Query meta_query arguments
     *
     * @param array $fields Array of field matches for querying
     * @param array $pod Related Pod
	 * @param object $params Parameters passed from select()
     *
     * @return string|null Query string for WHERE/HAVING
     *
     * @static
     * @since 2.3
     */
    public static function query_fields ( $fields, $pod = null, &$params = null ) {
        $query_fields = array();

		if ( !is_object( $params ) ) {
			$params = new stdClass();
		}

		if ( !isset( $params->query_field_level ) || 0 == $params->query_field_level ) {
			$params->query_fields = array();
			$params->query_field_syntax = false;
			$params->query_field_level = 1;

			if ( !isset( $params->where_default ) ) {
				$params->where_default = array();
			}

			if ( !isset( $params->where_defaulted ) ) {
				$params->where_defaulted = false;
			}
		}

		$current_level = $params->query_field_level;

        $relation = 'AND';

        if ( isset( $fields[ 'relation' ] ) ) {
            $relation = strtoupper( trim( pods_var( 'relation', $fields, 'AND', null, true ) ) );

            if ( 'AND' != $relation )
                $relation = 'OR';

            unset( $fields[ 'relation' ] );
        }

        foreach ( $fields as $field => $match ) {
            if ( is_array( $match ) && isset( $match[ 'relation' ] ) ) {
				$params->query_field_level = $current_level + 1;

                $query_field = self::query_fields( $match, $pod, $params );

				$params->query_field_level = $current_level;

                if ( !empty( $query_field ) ) {
                    $query_fields[] = $query_field;
				}
            }
            else {
                $query_field = self::query_field( $field, $match, $pod, $params );

                if ( !empty( $query_field ) ) {
                    $query_fields[] = $query_field;
				}
            }
        }

        if ( !empty( $query_fields ) ) {
			// If post_status not sent, detect it
			if ( !empty( $pod ) && 'post_type' == $pod[ 'type' ] && 1 == $current_level && !$params->where_defaulted && !empty( $params->where_default ) ) {
				$post_status_found = false;

				if ( !$params->query_field_syntax ) {
					$haystack = implode( ' ', (array) $query_fields );
					$haystack = preg_replace( '/\s/', ' ', $haystack );
					$haystack = preg_replace( '/\w\(/', ' ', $haystack );
					$haystack = str_replace( array( '(', ')', '  ', '\\\'', "\\\"" ), ' ', $haystack );

					preg_match_all( '/`?[\w\-]+`?(?:\\.`?[\w\-]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER );

					$found = (array) @current( $found );

					foreach ( $found as $value ) {
						$value = str_replace( '`', '', $value );
						$value = explode( '.', $value );

						if ( ( 'post_status' == $value[ 0 ] && 1 == count( $value ) ) || ( 2 == count( $value ) && 't' == $value[ 0 ] && 'post_status' == $value[ 1 ] ) ) {
							$post_status_found = true;

							break;
						}
					}
				}
				elseif ( !empty( $params->query_fields ) && in_array( 'post_status', $params->query_fields ) ) {
					$post_status_found = true;
				}

				if ( !$post_status_found ) {
					$query_fields[] = $params->where_default;
				}
			}

            if ( 1 < count( $query_fields ) )
                $query_fields = '( ( ' . implode( ' ) ' . $relation . ' ( ', $query_fields ) . ' ) )';
            else
                $query_fields = '( ' . implode( ' ' . $relation . ' ', $query_fields ) . ' )';
        }
        else
            $query_fields = null;

		// query_fields level complete
		if ( 1 == $params->query_field_level ) {
			$params->query_field_level = 0;
		}

        return $query_fields;
    }

    /**
     * Get the string to use in a query for matching, uses WP_Query meta_query arguments
     *
     * @param string|int $field Field name or array index
     * @param array|string $q Query array (meta_query) or string for matching
     * @param array $pod Related Pod
	 * @param object $params Parameters passed from select()
     *
     * @return string|null Query field string
     *
     * @see PodsData::query_fields
     * @static
     * @since 2.3
     */
    public static function query_field ( $field, $q, $pod = null, &$params = null ) {
        global $wpdb;

	    $simple_tableless_objects = PodsForm::simple_tableless_objects();

        $field_query = null;

        // Plain queries
        if ( is_numeric( $field ) && !is_array( $q ) ) {
            return $q;
		}
        // key => value queries (backwards compatibility)
        elseif ( !is_numeric( $field ) && ( !is_array( $q ) || ( !isset( $q[ 'key' ] ) && !isset( $q[ 'field' ] ) ) ) ) {
            $new_q = array(
                'field' => $field,
                'compare' => pods_var_raw( 'compare', $q, '=', null, true ),
                'value' => pods_var_raw( 'value', $q, $q, null, true ),
                'sanitize' => pods_var_raw( 'sanitize', $q, true ),
                'sanitize_format' => pods_var_raw( 'sanitize_format', $q ),
                'cast' => pods_var_raw( 'cast', $q )
            );

            if ( is_array( $new_q[ 'value' ] ) ) {
                if ( '=' == $new_q[ 'compare' ] )
                    $new_q[ 'compare' ] = 'IN';

                if ( isset( $new_q[ 'value' ][ 'compare' ] ) )
                    unset( $new_q[ 'value' ][ 'compare' ] );
            }

            $q = $new_q;
        }

        $field_name = trim( pods_var_raw( 'field', $q, pods_var_raw( 'key', $q, $field, null, true ), null, true ) );
        $field_type = strtoupper( trim( pods_var_raw( 'type', $q, 'CHAR', null, true ) ) );
        $field_value = pods_var_raw( 'value', $q );
        $field_compare = strtoupper( trim( pods_var_raw( 'compare', $q, ( is_array( $field_value ? 'IN' : '=' ) ), null, true ) ) );
		$field_sanitize = (boolean) pods_var( 'sanitize', $q, true );
        $field_sanitize_format = pods_var_raw( 'sanitize_format', $q, null, null, true );
		$field_cast = pods_var_raw( 'cast', $q, null, null, true );

		if ( is_object( $params ) ) {
			$params->meta_query_syntax = true;
			$params->query_fields[] = $field_name;
		}

        // Deprecated WP type
        if ( 'NUMERIC' == $field_type )
            $field_type = 'SIGNED';
        // Restrict to supported types
        elseif ( !in_array( $field_type, array( 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ) ) )
            $field_type = 'CHAR';

		// Alias / Casting
		if ( empty( $field_cast ) ) {
			// Setup field casting from field name
			if ( false === strpos( $field_name, '`' ) && false === strpos( $field_name, '(' ) && false === strpos( $field_name, ' ' ) ) {
				// Handle field naming if Pod-based
				if ( !empty( $pod ) && false === strpos( $field_name, '.' ) ) {
					$field_cast = '';

					$tableless_field_types = PodsForm::tableless_field_types();

					if ( isset( $pod[ 'fields' ][ $field_name ] ) && in_array( $pod[ 'fields' ][ $field_name ][ 'type' ], $tableless_field_types ) ) {
						if ( in_array( $pod[ 'fields' ][ $field_name ][ 'pick_object' ], $simple_tableless_objects ) ) {
							if ( 'meta' == $pod[ 'storage' ] )
								$field_cast = "`{$field_name}`.`meta_value`";
							else
								$field_cast = "`t`.`{$field_name}`";
						}
						else {
							$table = pods_api()->get_table_info( $pod[ 'fields' ][ $field_name ][ 'pick_object' ], $pod[ 'fields' ][ $field_name ][ 'pick_val' ] );

							if ( !empty( $table ) )
								$field_cast = "`{$field_name}`.`" . $table[ 'field_index' ] . '`';
						}
					}

					if ( empty( $field_cast ) ) {
						if ( !in_array( $pod[ 'type' ], array( 'pod', 'table' ) ) ) {
							if ( isset( $pod[ 'object_fields' ][ $field_name ] ) )
								$field_cast = "`t`.`{$field_name}`";
							elseif ( isset( $pod[ 'fields' ][ $field_name ] ) ) {
								if ( 'meta' == $pod['storage'] )
									$field_cast = "`{$field_name}`.`meta_value`";
								else
									$field_cast = "`d`.`{$field_name}`";
							}
							else {
								foreach ( $pod[ 'object_fields' ] as $object_field => $object_field_opt ) {
									if ( $object_field == $field_name || in_array( $field_name, $object_field_opt[ 'alias' ] ) ) {
										$field_cast = "`t`.`{$object_field}`";

										break;
									}
								}
							}
						}
						elseif ( isset( $pod[ 'fields' ][ $field_name ] ) ) {
							if ( 'meta' == $pod['storage'] )
								$field_cast = "`{$field_name}`.`meta_value`";
							else
								$field_cast = "`t`.`{$field_name}`";
						}

						if ( empty( $field_cast ) ) {
							if ( 'meta' == $pod['storage'] ) {
								$field_cast = "`{$field_name}`.`meta_value`";
							}
							else
								$field_cast = "`t`.`{$field_name}`";
						}
					}
				}
				else {
					$field_cast = '`' . str_replace( '.', '`.`', $field_name ) . '`';
				}
			}
			else {
				$field_cast = $field_name;
			}

			// Cast field if needed
			if ( 'CHAR' != $field_type ) {
				$field_cast = 'CAST( ' . $field_cast . ' AS ' . $field_type .' )';
			}
		}

		// Setup string sanitizing for $wpdb->prepare()
		if ( empty( $field_sanitize_format ) ) {
			// Sanitize as string
			$field_sanitize_format = '%s';

			// Sanitize as integer if needed
			if ( in_array( $field_type, array( 'UNSIGNED', 'SIGNED' ) ) )
				$field_sanitize_format = '%d';
		}

        // Restrict to supported comparisons
        if ( !in_array( $field_compare, array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'ALL', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' ) ) )
            $field_compare = '=';

        // Restrict to supported array comparisons
        if ( is_array( $field_value ) && !in_array( $field_compare, array( 'IN', 'NOT IN', 'ALL', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
            if ( in_array( $field_compare, array( '!=', 'NOT LIKE' ) ) )
                $field_compare = 'NOT IN';
            else
                $field_compare = 'IN';
        }
        // Restrict to supported string comparisons
        elseif ( !is_array( $field_value ) && in_array( $field_compare, array( 'IN', 'NOT IN', 'ALL', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
            $check_value = preg_split( '/[,\s]+/', $field_value );

            if ( 1 < count( $check_value ) )
                $field_value = $check_value;
            elseif ( in_array( $field_compare, array( 'NOT IN', 'NOT BETWEEN' ) ) )
                $field_compare = '!=';
            else
                $field_compare = '=';
        }

        // Restrict to two values, force = and != if only one value provided
        if ( in_array( $field_compare, array( 'BETWEEN', 'NOT BETWEEN' ) ) ) {
            $field_value = array_values( array_slice( $field_value, 0, 2 ) );

            if ( 1 == count( $field_value ) ) {
                if ( 'NOT IN' == $field_compare )
                    $field_compare = '!=';
                else
                    $field_compare = '=';
            }
        }

		// Single array handling
		if ( 1 == count( (array) $field_value ) && $field_compare == 'ALL' ) {
			$field_compare = '=';
		}
		// Empty array handling
		elseif ( empty( $field_value ) && in_array( $field_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) )  ) {
			$field_compare = 'EXISTS';
		}

		// Rebuild $q
		$q = array(
			'field' => $field_name,
			'type' => $field_type,
			'value' => $field_value,
			'compare' => $field_compare,
			'sanitize' => $field_sanitize,
			'sanitize_format' => $field_sanitize_format,
			'cast' => $field_cast
		);

        // Make the query
        if ( in_array( $field_compare, array( '=', '!=', '>', '>=', '<', '<=', 'REGEXP', 'NOT REGEXP', 'RLIKE' ) ) ) {
			if ( $field_sanitize ) {
            	$field_query = $wpdb->prepare( $field_cast . ' ' . $field_compare . ' ' . $field_sanitize_format, $field_value );
			}
			else {
            	$field_query = $field_cast . ' ' . $field_compare . ' "' . $field_value . '"';
			}
		}
        elseif ( in_array( $field_compare, array( 'LIKE', 'NOT LIKE' ) ) ) {
			if ( $field_sanitize ) {
            	$field_query = $field_cast . ' ' . $field_compare . ' "%' . pods_sanitize_like( $field_value ) . '%"';
			}
			else {
            	$field_query = $field_cast . ' ' . $field_compare . ' "' . $field_value . '"';
			}
		}
        elseif ( in_array( $field_compare, array( 'IN', 'NOT IN', 'ALL' ) ) ) {
	        if ( $field_compare == 'ALL' ) {
		        $field_compare = 'IN';

		        if ( ! empty( $pod ) ) {
			        $params->having[] = 'COUNT( DISTINCT ' . $field_cast . ' ) = ' . count( $field_value );

			        if ( empty( $params->groupby ) || ( ! in_array( '`t`.`' . $pod['field_id'] . '`', $params->groupby ) && ! in_array( 't.' . $pod['field_id'] . '', $params->groupby ) ) ) {
				        $params->groupby[] = '`t`.`' . $pod['field_id'] . '`';
			        }
		        }
	        }

			if ( $field_sanitize ) {
            	$field_query = $wpdb->prepare( $field_cast . ' ' . $field_compare . ' ( ' . substr( str_repeat( ', ' . $field_sanitize_format, count( $field_value ) ), 1 ) . ' )', $field_value );
			}
			else {
            	$field_query = $field_cast . ' ' . $field_compare . ' ( "' . implode( '", "', $field_value ) . '" )';
			}
		}
        elseif ( in_array( $field_compare, array( 'BETWEEN', 'NOT BETWEEN' ) ) ) {
			if ( $field_sanitize ) {
            	$field_query = $wpdb->prepare( $field_cast . ' ' . $field_compare . ' ' . $field_sanitize_format . ' AND ' . $field_sanitize_format, $field_value );
			}
			else {
            	$field_query = $field_cast . ' ' . $field_compare . ' "' . $field_value[ 0 ] . '" AND "' . $field_value[ 1 ] . '"';
			}
		}
        elseif ( 'EXISTS' == $field_compare )
            $field_query = $field_cast . ' IS NOT NULL';
        elseif ( 'NOT EXISTS' == $field_compare )
            $field_query = $field_cast . ' IS NULL';

        $field_query = apply_filters( 'pods_data_field_query', $field_query, $q );

        return $field_query;
    }

    /**
     * Setup fields for traversal
     *
     * @param array $fields Associative array of fields data
     *
     * @return array Traverse feed
     * @param object $params (optional) Parameters from build()
     *
     * @since 2.0
     */
    function traverse_build ( $fields = null, $params = null ) {
        if ( null === $fields )
            $fields = $this->fields;

        $feed = array();

        foreach ( $fields as $field => $data ) {
            if ( !is_array( $data ) )
                $field = $data;

            if ( !isset( $_GET[ 'filter_' . $field ] ) )
                continue;

            $field_value = pods_var( 'filter_' . $field, 'get', false, null, true );

            if ( !empty( $field_value ) || 0 < strlen( $field_value ) )
                $feed[ 'traverse_' . $field ] = array( $field );
        }

        return $feed;
    }

    /**
     * Recursively join tables based on fields
     *
     * @param array $traverse_recurse Array of traversal options
     *
     * @return array Array of table joins
     *
     * @since 2.0
     */
    function traverse_recurse ( $traverse_recurse ) {
        global $wpdb;

        $defaults = array(
            'pod' => null,
            'fields' => array(),
            'joined' => 't',
            'depth' => 0,
            'joined_id' => 'id',
            'joined_index' => 'id',
            'params' => new stdClass(),
            'last_table_info' => array()
        );

        $traverse_recurse = array_merge( $defaults, $traverse_recurse );

        $joins = array();

        if ( 0 == $traverse_recurse[ 'depth' ] && !empty( $traverse_recurse[ 'pod' ] ) && !empty( $traverse_recurse [ 'last_table_info' ] ) && isset( $traverse_recurse [ 'last_table_info' ][ 'id' ] ) )
            $pod_data = $traverse_recurse [ 'last_table_info' ];
        elseif ( empty( $traverse_recurse[ 'pod' ] ) ) {
            if ( !empty( $traverse_recurse[ 'params' ] ) && !empty( $traverse_recurse[ 'params' ]->table ) && 0 === strpos( $traverse_recurse[ 'params' ]->table, $wpdb->prefix ) ) {
                if ( $wpdb->posts == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'post_type';
                elseif ( $wpdb->terms == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'taxonomy';
                elseif ( $wpdb->users == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'user';
                elseif ( $wpdb->comments == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'comment';
                else
                    return $joins;

                $pod_data = array();

                if ( in_array( $traverse_recurse[ 'pod' ], array( 'user', 'comment' ) ) ) {
                    $pod = $this->api->load_pod( array( 'name' => $traverse_recurse[ 'pod' ], 'table_info' => true ) );

                    if ( !empty( $pod ) && $pod[ 'type' ] == $pod )
                        $pod_data = $pod;
                }

                if ( empty( $pod_data ) ) {
                    $default_storage = 'meta';

                    if ( 'taxonomy' == $traverse_recurse['pod'] && ! function_exists( 'get_term_meta' ) ) {
                        $default_storage = 'none';
                    }

                    $pod_data = array(
                        'id' => 0,
                        'name' => '_table_' . $traverse_recurse[ 'pod' ],
                        'type' => $traverse_recurse[ 'pod' ],
                        'storage' => $default_storage,
                        'fields' => array(),
                        'object_fields' => $this->api->get_wp_object_fields( $traverse_recurse[ 'pod' ] )
                    );

                    $pod_data = array_merge( $this->api->get_table_info( $traverse_recurse[ 'pod' ], '' ), $pod_data );
                } elseif ( 'taxonomy' == $pod_data['type'] && 'none' == $pod_data['storage'] && function_exists( 'get_term_meta' ) ) {
                    $pod_data['storage'] = 'meta';
                }

                $traverse_recurse[ 'pod' ] = $pod_data[ 'name' ];
            }
            else
                return $joins;
        }
        else {
            $pod_data = $this->api->load_pod( array( 'name' => $traverse_recurse[ 'pod' ], 'table_info' => true ), false );

            if ( empty( $pod_data ) )
                return $joins;
        }

		if ( isset( $pod_data[ 'object_fields' ] ) ) {
			$pod_data[ 'fields' ] = array_merge( $pod_data[ 'fields' ], $pod_data[ 'object_fields' ] );
		}

	    $tableless_field_types = PodsForm::tableless_field_types();
	    $simple_tableless_objects = PodsForm::simple_tableless_objects();
	    $file_field_types = PodsForm::file_field_types();

        if ( !isset( $this->traversal[ $traverse_recurse[ 'pod' ] ] ) )
            $this->traversal[ $traverse_recurse[ 'pod' ] ] = array();

        if ( ( empty( $pod_data[ 'meta_table' ] ) || $pod_data[ 'meta_table' ] == $pod_data[ 'table' ] ) && ( empty( $traverse_recurse[ 'fields' ] ) || empty( $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] ] ) ) )
            return $joins;

        $field = $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] ];

        $ignore_aliases = array(
            'wpml_languages',
            'polylang_languages'
        );

        $ignore_aliases = apply_filters( 'pods_data_traverse_recurse_ignore_aliases', $ignore_aliases, $field, $traverse_recurse, $this );

        if ( in_array( $field, $ignore_aliases ) )
            return $joins;

        $meta_data_table = false;

        if ( !isset( $pod_data[ 'fields' ][ $field ] ) && 'd' == $field && isset( $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] - 1 ] ) ) {
            $field = $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] - 1 ];

			$field_type = 'pick';

			if ( isset( $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'fields' ][ $field ] ) ) {
				$field_type = $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'fields' ][ $field ][ 'type' ];
			}
			elseif ( isset( $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'object_fields' ][ $field ] ) ) {
				$field_type = $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'object_fields' ][ $field ][ 'type' ];
			}

            $pod_data[ 'fields' ][ $field ] = array(
                'id' => 0,
                'name' => $field,
                'type' => $field_type,
                'pick_object' => $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'type' ],
                'pick_val' => $traverse_recurse[ 'last_table_info' ][ 'pod' ][ 'name' ]
            );

            $meta_data_table = true;
        }

        // Fallback to meta table if the pod type supports it
        if ( !isset( $pod_data[ 'fields' ][ $field ] ) ) {
            $last = end( $traverse_recurse[ 'fields' ] );

            if ( 'post_type' == $pod_data[ 'type' ] && !isset( $pod_data[ 'object_fields' ] ) )
                $pod_data[ 'object_fields' ] = $this->api->get_wp_object_fields( 'post_type', $pod_data );

            if ( 'post_type' == $pod_data[ 'type' ] && isset( $pod_data[ 'object_fields'][ $field ] ) && in_array( $pod_data[ 'object_fields' ][ $field ][ 'type' ], $tableless_field_types ) )
                $pod_data[ 'fields' ][ $field ] = $pod_data[ 'object_fields' ][ $field ];
            elseif ( 'meta_value' === $last && in_array( $pod_data[ 'type' ], array( 'post_type', 'media', 'taxonomy', 'user', 'comment' ) ) )
                $pod_data[ 'fields' ][ $field ] = PodsForm::field_setup( array( 'name' => $field ) );
            else {
                if ( 'post_type' == $pod_data[ 'type' ] ) {
                    $pod_data[ 'object_fields' ] = $this->api->get_wp_object_fields( 'post_type', $pod_data, true );

                    if ( 'post_type' == $pod_data[ 'type' ] && isset( $pod_data[ 'object_fields' ][ $field ] ) && in_array( $pod_data[ 'object_fields' ][ $field ][ 'type' ], $tableless_field_types ) )
                        $pod_data[ 'fields' ][ $field ] = $pod_data[ 'object_fields' ][ $field ];
                    else
                        return $joins;
                }
                else
                    return $joins;
            }
        } elseif ( isset( $pod_data[ 'object_fields' ] ) && isset( $pod_data[ 'object_fields' ][ $field ] ) && ! in_array( $pod_data[ 'object_fields' ][ $field ][ 'type' ], $tableless_field_types ) ) {
            return $joins;
	    }

        $traverse = $pod_data[ 'fields' ][ $field ];

        if ( in_array( $traverse[ 'type' ], $file_field_types ) )
            $traverse[ 'table_info' ] = $this->api->get_table_info( 'post_type', 'attachment' );
        elseif ( !in_array( $traverse[ 'type' ], $tableless_field_types ) )
            $traverse[ 'table_info' ] = $this->api->get_table_info( $pod_data[ 'type' ], $pod_data[ 'name' ], $pod_data[ 'name' ], $pod_data );
        elseif ( empty( $traverse[ 'table_info' ] ) || ( in_array( $traverse[ 'pick_object' ], $simple_tableless_objects ) && !empty( $traverse_recurse[ 'last_table_info' ] ) ) ) {
            if ( in_array( $traverse[ 'pick_object' ], $simple_tableless_objects ) && !empty( $traverse_recurse[ 'last_table_info' ] ) ) {
                $traverse[ 'table_info' ] = $traverse_recurse[ 'last_table_info' ];

                if ( !empty( $traverse[ 'table_info' ][ 'meta_table' ] ) )
                    $meta_data_table = true;
            }
            elseif ( !in_array( $traverse[ 'type' ], $tableless_field_types ) && !empty( $traverse_recurse[ 'last_table_info' ] )  && 0 == $traverse_recurse[ 'depth' ] )
                $traverse[ 'table_info' ] = $traverse_recurse[ 'last_table_info' ];
            else {
	            if ( ! isset( $traverse[ 'pod' ] ) ) {
		            $traverse[ 'pod' ] = null;
	            }

	            $traverse[ 'table_info' ] = $this->api->get_table_info( $traverse[ 'pick_object' ], $traverse[ 'pick_val' ], null, $traverse[ 'pod' ], $traverse );
            }
        }

        if ( isset( $this->traversal[ $traverse_recurse[ 'pod' ] ][ $traverse[ 'name' ] ] ) )
            $traverse = array_merge( $traverse, (array) $this->traversal[ $traverse_recurse[ 'pod' ] ][ $traverse[ 'name' ] ] );

        $traverse = apply_filters( 'pods_data_traverse', $traverse, compact( 'pod', 'fields', 'joined', 'depth', 'joined_id', 'params' ), $this );

        if ( empty( $traverse ) )
            return $joins;

        $traverse[ 'id' ] = (int) $traverse[ 'id' ];

        if ( empty( $traverse[ 'id' ] ) )
            $traverse[ 'id' ] = $field;

        $table_info = $traverse[ 'table_info' ];

        $this->traversal[ $traverse_recurse[ 'pod' ] ][ $field ] = $traverse;

        $field_joined = $field;

        if ( 0 < $traverse_recurse[ 'depth' ] && 't' != $traverse_recurse[ 'joined' ] ) {
            if ( $meta_data_table && ( 'pick' != $traverse[ 'type' ] || !in_array( pods_var( 'pick_object', $traverse ), $simple_tableless_objects ) ) )
                $field_joined = $traverse_recurse[ 'joined' ] . '_d';
            else
                $field_joined = $traverse_recurse[ 'joined' ] . '_' . $field;
        }

        $rel_alias = 'rel_' . $field_joined;

        if ( pods_v( 'search', $traverse_recurse[ 'params' ], false ) && empty( $traverse_recurse[ 'params' ]->filters ) ) {
            if ( 0 < strlen( pods_var( 'filter_' . $field_joined, 'get' ) ) ) {
                $val = absint( pods_var( 'filter_' . $field_joined, 'get' ) );

                $search = "`{$field_joined}`.`{$table_info[ 'field_id' ]}` = {$val}";

                if ( 'text' == $this->search_mode ) {
                    $val = pods_var( 'filter_' . $field_joined, 'get' );

                    $search = "`{$field_joined}`.`{$traverse[ 'name' ]}` = '{$val}'";
                }
                elseif ( 'text_like' == $this->search_mode ) {
                    $val = pods_sanitize( pods_sanitize_like( pods_var_raw( 'filter_' . $field_joined ) ) );

                    $search = "`{$field_joined}`.`{$traverse[ 'name' ]}` LIKE '%{$val}%'";
                }

                $this->search_where[] = " {$search} ";
            }
        }

        $the_join = null;

        $joined_id = $table_info[ 'field_id' ];
        $joined_index = $table_info[ 'field_index' ];

        if ( 'taxonomy' == $traverse[ 'type' ] ) {
            $rel_tt_alias = 'rel_tt_' . $field_joined;

            if ( pods_tableless() && function_exists( 'get_term_meta' ) ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
                ";

                $joined_id = $table_info[ 'meta_field_id' ];
                $joined_index = $table_info[ 'meta_field_index' ];
            } elseif ( $meta_data_table ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'pod_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'pod_field_id' ]}` = `{$traverse_recurse[ 'rel_alias' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
                ";
            }
            else {
                $the_join = "
                    LEFT JOIN `{$wpdb->term_relationships}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`object_id` = `{$traverse_recurse[ 'joined' ]}`.`ID`

                    LEFT JOIN `{$wpdb->term_taxonomy}` AS `{$rel_tt_alias}` ON
                        `{$rel_tt_alias}`.`taxonomy` = '{$traverse[ 'name' ]}'
                        AND `{$rel_tt_alias}`.`term_taxonomy_id` = `{$rel_alias}`.`term_taxonomy_id`

                    LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$rel_tt_alias}`.`{$table_info[ 'field_id' ]}`
                ";

				// Override $rel_alias
				$rel_alias = $field_joined;

                $joined_id = $table_info[ 'field_id' ];
                $joined_index = $table_info[ 'field_index' ];
            }
        } elseif ( 'comment' == $traverse[ 'type' ] ) {
            if ( pods_tableless() ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
                ";

                $joined_id = $table_info[ 'meta_field_id' ];
                $joined_index = $table_info[ 'meta_field_index' ];
            } elseif ( $meta_data_table ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'pod_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'pod_field_id' ]}` = `{$traverse_recurse[ 'rel_alias' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
                ";
            }
            else {
                $the_join = "
                    LEFT JOIN `{$wpdb->comments}` AS `{$field_joined}` ON
                        `{$field_joined}`.`comment_post_ID` = `{$traverse_recurse[ 'joined' ]}`.`ID`
                ";

				// Override $rel_alias
				$rel_alias = $field_joined;

                $joined_id = $table_info[ 'field_id' ];
                $joined_index = $table_info[ 'field_index' ];
            }
        } elseif ( in_array( $traverse[ 'type' ], $tableless_field_types ) && ( 'pick' != $traverse[ 'type' ] || !in_array( pods_v( 'pick_object', $traverse ), $simple_tableless_objects ) ) ) {
            if ( pods_tableless() ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$rel_alias}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = CONVERT( `{$rel_alias}`.`{$table_info[ 'meta_field_value' ]}`, SIGNED )
                ";

                $joined_id = $table_info[ 'meta_field_id' ];
                $joined_index = $table_info[ 'meta_field_index' ];
            }
            elseif ( $meta_data_table ) {
                if ( $traverse[ 'id' ] !== $traverse[ 'pick_val' ] ) {
                    // This must be a relationship
                    $joined_id = 'related_item_id';
                } else {
                    $joined_id = $traverse_recurse[ 'joined_id' ];
                }

                $the_join = "
                     LEFT JOIN `{$table_info['pod_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['pod_field_id']}` = `{$traverse_recurse['rel_alias']}`.`{$joined_id}`
                 ";
            }
            else {
                if (
                    ( $traverse_recurse[ 'depth' ] + 2 ) == count( $traverse_recurse[ 'fields' ] )
                    && ( 'pick' != $traverse[ 'type' ] || !in_array( pods_var( 'pick_object', $traverse ), $simple_tableless_objects ) )
                    && 'post_author' == $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] + 1 ] ) {
                    $table_info[ 'recurse' ] = false;
                }

                $the_join = "
                    LEFT JOIN `@wp_podsrel` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`field_id` = {$traverse[ 'id' ]}
                        AND `{$rel_alias}`.`item_id` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`

                    LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$rel_alias}`.`related_item_id`
                ";
            }
        } elseif ( 'meta' == $pod_data[ 'storage' ] ) {
            if (
                ( $traverse_recurse[ 'depth' ] + 2 ) == count( $traverse_recurse[ 'fields' ] )
                && ( 'pick' != $traverse[ 'type' ] || !in_array( pods_var( 'pick_object', $traverse ), $simple_tableless_objects ) )
                && $table_info[ 'meta_field_value' ] == $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] + 1 ] ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
                ";

                $table_info[ 'recurse' ] = false;
            }
            else {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'meta_table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'meta_field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'meta_field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
                ";

                $joined_id = $table_info[ 'meta_field_id' ];
                $joined_index = $table_info[ 'meta_field_index' ];
            }
        }

        $traverse_recursive = array(
            'pod' => pods_var_raw( 'name', pods_var_raw( 'pod', $table_info ) ),
            'fields' => $traverse_recurse[ 'fields' ],
            'joined' => $field_joined,
            'depth' => ( $traverse_recurse[ 'depth' ] + 1 ),
            'joined_id' => $joined_id,
            'joined_index' => $joined_index,
            'params' => $traverse_recurse[ 'params' ],
            'rel_alias' => $rel_alias,
            'last_table_info' => $table_info
        );

        $the_join = apply_filters( 'pods_data_traverse_the_join', $the_join, $traverse_recurse, $traverse_recursive, $this );

        if ( empty( $the_join ) )
            return $joins;

        $joins[ $traverse_recurse[ 'pod' ] . '_' . $traverse_recurse[ 'depth' ] . '_' . $traverse[ 'id' ] ] = $the_join;

        if ( ( $traverse_recurse[ 'depth' ] + 1 ) < count( $traverse_recurse[ 'fields' ] ) && !empty( $traverse_recurse[ 'pod' ] ) && false !== $table_info[ 'recurse' ] )
            $joins = array_merge( $joins, $this->traverse_recurse( $traverse_recursive ) );

        return $joins;
    }

    /**
     * Recursively join tables based on fields
     *
     * @param array $fields Fields to recurse
     * @param null $all_fields (optional) If $fields is empty then traverse all fields, argument does not need to be passed
     * @param object $params (optional) Parameters from build()
     *
     * @return array Array of joins
     */
    function traverse ( $fields = null, $all_fields = null, $params = null ) {
        $joins = array();

        if ( null === $fields )
            $fields = $this->traverse_build( $all_fields, $params );

        foreach ( (array) $fields as $field_group ) {
            $traverse_recurse = array(
                'pod' => $this->pod,
                'fields' => $fields,
                'params' => $params,
                'last_table_info' => $this->pod_data,
                'joined_id' => $this->pod_data[ 'field_id' ],
                'joined_index' => $this->pod_data[ 'field_index' ]
            );

            if ( is_array( $field_group ) ) {
                $traverse_recurse[ 'fields' ] = $field_group;

                $joins = array_merge( $joins, $this->traverse_recurse( $traverse_recurse ) );
            }
            else {
                $joins = array_merge( $joins, $this->traverse_recurse( $traverse_recurse ) );
                $joins = array_filter( $joins );

                return $joins;
            }
        }

        $joins = array_filter( $joins );

        return $joins;
    }

    /**
     * Handle filters / actions for the class
     *
     * @since 2.0
     */
    private static function do_hook () {
        $args = func_get_args();

        if ( empty( $args ) )
            return false;

        $name = array_shift( $args );

        return pods_do_hook( 'data', $name, $args );
    }

    /**
     * Get the complete sql
     *
     * @since 2.0.5
     */
    public function get_sql ( $sql ) {
        global $wpdb;

        if ( empty( $sql ) )
            $sql = $this->sql;

	/**
	 * Allow SQL query to be manipulated.
	 *
	 * @param string   $sql       SQL Query string
	 * @param PodsData $pods_data PodsData object
	 *
	 * @since 2.7
	 */
	$sql = apply_filters( 'pods_data_get_sql', $sql, $this );

        $sql = str_replace( array( '@wp_users', '@wp_' ), array( $wpdb->users, $wpdb->prefix ), $sql );

        $sql = str_replace( '{prefix}', '@wp_', $sql );
        $sql = str_replace( '{/prefix/}', '{prefix}', $sql );

        return $sql;
    }
}
