<?php
/**
 * @package Pods
 */
class PodsData {

    // base
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
     * Data Abstraction Class for Pods
     *
     * @param string $pod Pod name
     * @param integer $id Pod Item ID
     * @param bool $strict If true throws an error if a pod is not found.
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct ( $pod = null, $id = 0, $strict = true ) {
        global $wpdb;

        if ( is_object( $pod ) && 'PodsAPI' == get_class( $pod ) ) {
            $this->api = $pod;
            $pod = $this->api->pod;
        }
        else
            $this->api =& pods_api( $pod );

        $this->api->display_errors =& self::$display_errors;

        if ( null !== $pod ) {
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

            if ( null !== $id && !is_array( $id ) && !is_object( $id ) ) {
                $this->id = $id;

                $this->fetch( $this->id );
            }
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function select ( $params ) {
        $cache_key = $results = false;

        // Debug purposes
        if ( 1 == pods_var( 'pods_debug_params', 'get', 0 ) && is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) ) )
            pods_debug( $params );

        // Get from cache if enabled
        if ( null !== pods_var( 'expires', $params, null, null, true ) ) {
            $cache_key = md5( serialize( get_object_vars( $params ) ) );

            $results = pods_view_get( $cache_key, pods_var( 'cache_mode', $params, 'cache', null, true ), 'pods_data_select' );

            if ( empty( $results ) )
                $results = false;
        }

        if ( empty( $results ) ) {
            // Build
            $this->sql = $this->build( $params );

            // Debug purposes
            if ( ( 1 == pods_var( 'pods_debug_sql', 'get', 0 ) || 1 == pods_var( 'pods_debug_sql_all', 'get', 0 ) ) && is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) ) )
                echo "<textarea cols='100' rows='24'>{$this->sql}</textarea>";

            if ( empty( $this->sql ) )
                return array();

            // Get Data
            $results = pods_query( $this->sql, $this );

            // Cache if enabled
            if ( false !== $cache_key )
                pods_view_set( $cache_key, $results, pods_var( 'expires', $params, 0, null, true ), pods_var( 'cache_mode', $params, 'cache', null, true ), 'pods_data_select' );
        }

        $results = $this->do_hook( 'select', $results );

        $this->data = $results;

        $this->row_number = -1;

        // Fill in empty field data (if none provided)
        if ( ( !isset( $this->fields ) || empty( $this->fields ) ) && !empty( $this->data ) ) {
            $this->fields = array();
            $data = (array) @current( $this->data );

            foreach ( $data as $data_key => $data_value ) {
                $this->fields[ $data_key ] = array( 'label' => ucwords( str_replace( '-', ' ', str_replace( '_', ' ', $data_key ) ) ) );
            }

            $this->fields = PodsForm::fields_setup( $this->fields );
        }
        $this->total_found_calculated = false;

        $this->total = count( (array) $this->data );

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
     * @since 2.0.0
     */
    public function build ( $params ) {
        $defaults = array(
            'select' => '*',
            'distinct' => true,
            'table' => null,
            'join' => null,
            'where' => null,
            'groupby' => null,
            'having' => null,
            'orderby' => null,
            'limit' => -1,
            'offset' => null,

            'id' => null,
            'index' => null,

            'page' => 1,
            'search' => null,
            'search_query' => null,
            'search_mode' => null,
            'search_across' => false,
            'search_across_picks' => false,
            'search_across_files' => false,
            'filters' => array(),

            'fields' => array(),
            'traverse' => array(),

            'sql' => null,

            'strict' => false
        );

        $params = (object) array_merge( $defaults, (array) $params );

        if ( 0 < strlen( $params->sql ) )
            return $params->sql;

        // Validate
        $params->page = pods_absint( $params->page );

        if ( 0 == $params->page )
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

        if ( ( empty( $params->fields ) || !is_array( $params->fields ) ) && is_object( $this->pod_data ) && isset( $this->fields ) && !empty( $this->fields ) )
            $params->fields = $this->fields;

        if ( empty( $params->index ) )
            $params->index = $this->field_index;

        if ( empty( $params->id ) )
            $params->id = $this->field_id;

        if ( empty( $params->table ) && is_object( $this->pod_data ) && isset( $this->table ) && !empty( $this->table ) )
            $params->table = $this->table;

        if ( empty( $params->table ) )
            return false;

        if ( false === strpos( $params->table, '(' ) && false === strpos( $params->table, '`' ) )
            $params->table = '`' . $params->table . '`';

        if ( !empty( $params->join ) )
            $params->join = array_merge( (array) $this->join, (array) $params->join );
        elseif ( false === $params->strict )
            $params->join = $this->join;

        if ( !empty( $params->where ) )
            $params->where = (array) $params->where;
        else
            $params->where = array();

        if ( false === $params->strict && !empty( $this->where ) ) {
            if ( empty( $params->where ) && !empty( $this->where_default ) )
                $params->where = array_merge( $params->where, (array) $this->where_default );

            $params->where = array_merge( $params->where, (array) $this->where );
        }

        if ( !empty( $params->having ) )
            $params->having = (array) $params->having;
        else
            $params->having = array();

        if ( !empty( $params->orderby ) )
            $params->orderby = (array) $params->orderby;
        else
            $params->orderby = array();

        if ( false === $params->strict && !empty( $this->orderby ) )
            $params->orderby = array_merge( $params->orderby, (array) $this->orderby );

        if ( !empty( $params->traverse ) )
            $this->traverse = $params->traverse;

        $allowed_search_modes = array( 'int', 'text', 'text_like' );

        if ( !empty( $params->search_mode ) && in_array( $params->search_mode, $allowed_search_modes ) )
            $this->search_mode = $params->search_mode;

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
                            $field = pods_var( 'name', $field, $key, null, true );
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

                        if ( in_array( $field, $params->filters ) )
                            continue;

                        $fieldfield = '`' . $field . '`';

                        if ( 'pick' == $attributes[ 'type' ] && 'custom-simple' != pods_var( 'pick_object', $attributes ) ) {
                            if ( false === $params->search_across_picks )
                                continue;
                            else {
                                if ( !isset( $attributes[ 'table_info' ] ) || empty( $attributes[ 'table_info' ] ) )
                                    $attributes[ 'table_info' ] = $this->api->get_table_info( pods_var( 'pick_object', $attributes ), pods_var( 'pick_val', $attributes ) );

                                if ( empty( $attributes[ 'table_info' ][ 'field_index' ] ) )
                                    continue;

                                $fieldfield = $fieldfield . '.`' . $attributes[ 'table_info' ][ 'field_index' ] . '`';
                            }
                        }
                        elseif ( in_array( $attributes[ 'type' ], apply_filters( 'pods_file_field_types', array( 'file', 'avatar' ) ) ) ) {
                            if ( false === $params->search_across_files )
                                continue;
                            else
                                $fieldfield = $fieldfield . '.`post_title`';
                        }
                        else
                            $fieldfield = '`t`.' . $fieldfield;

                        if ( isset( $this->aliases[ $field ] ) )
                            $fieldfield = '`' . $this->aliases[ $field ] . '`';

                        if ( isset( $attributes[ 'real_name' ] ) && !empty( $attributes[ 'real_name' ] ) )
                            $fieldfield = $attributes[ 'real_name' ];

                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                            $having[] = "{$fieldfield} LIKE '%" . pods_sanitize( $params->search_query ) . "%'";
                        else
                            $where[] = "{$fieldfield} LIKE '%" . pods_sanitize( $params->search_query ) . "%'";
                    }
                }
                elseif ( !empty( $params->index ) ) {
                    $attributes = array();

                    if ( isset( $params->fields[ $params->index ] ) )
                        $attributes = $params->fields[ $params->index ];

                    $fieldfield = '`t`.`' . $params->index . '`';

                    if ( isset( $attributes[ 'real_name' ] ) && false !== $attributes[ 'real_name' ] && !empty( $attributes[ 'real_name' ] ) )
                        $fieldfield = $attributes[ 'real_name' ];

                    if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                        $having[] = "{$fieldfield} LIKE '%" . pods_sanitize( $params->search_query ) . "%'";
                    else
                        $where[] = "{$fieldfield} LIKE '%" . pods_sanitize( $params->search_query ) . "%'";
                }

                if ( !empty( $where ) )
                    $params->where[] = '(' . implode( ' OR ', $where ) . ')';

                if ( !empty( $having ) )
                    $params->having[] = '(' . implode( ' OR ', $having ) . ')';
            }

            // Filter
            foreach ( $params->filters as $filter ) {
                $where = $having = array();

                if ( !isset( $params->fields[ $filter ] ) )
                    continue;

                $attributes = $params->fields[ $filter ];
                $field = pods_var( 'name', $attributes, $filter, null, true );

                $filterfield = '`' . $field . '`';

                if ( 'pick' == $attributes[ 'type' ] && 'custom-simple' != pods_var( 'pick_object', $attributes ) ) {
                    if ( !isset( $attributes[ 'table_info' ] ) || empty( $attributes[ 'table_info' ] ) )
                        $attributes[ 'table_info' ] = $this->api->get_table_info( pods_var( 'pick_object', $attributes ), pods_var( 'pick_val', $attributes ) );

                    if ( empty( $attributes[ 'table_info' ][ 'field_index' ] ) )
                        continue;

                    $filterfield = $filterfield . '.`' . $attributes[ 'table_info' ][ 'field_index' ] . '`';
                }
                elseif ( in_array( $attributes[ 'type' ], apply_filters( 'pods_file_field_types', array( 'file', 'avatar' ) ) ) )
                    $filterfield = $filterfield . '.`post_title`';
                else
                    $filterfield = '`t`.' . $filterfield;

                if ( isset( $this->aliases[ $field ] ) )
                    $filterfield = '`' . $this->aliases[ $field ] . '`';

                if ( isset( $attributes[ 'real_name' ] ) && false !== $attributes[ 'real_name' ] && !empty( $attributes[ 'real_name' ] ) )
                    $filterfield = $attributes[ 'real_name' ];

                if ( 'pick' == $attributes[ 'type' ] ) {
                    $filter_value = pods_var( 'filter_' . $field, 'get', false, null, true );

                    if ( 'custom-simple' == pods_var( 'pick_object', $attributes ) ) {
                        if ( strlen( $filter_value ) < 1 )
                            continue;

                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] ) {
                            $having[] = "( {$filterfield} = '" . $filter_value . "'"
                                         . " OR {$filterfield} LIKE '%\"" . $filter_value . "\"%' )";
                        }
                        else {
                            $where[] = "( {$filterfield} = '" . $filter_value . "'"
                                        . " OR {$filterfield} LIKE '%\"" . $filter_value . "\"%' )";
                        }
                    }
                    else {
                        $filter_value = (int) $filter_value;

                        if ( empty( $filter_value ) || empty( $attributes[ 'table_info' ][ 'field_id' ] ) )
                            continue;

                        $filterfield = '`' . $field . '`.`' . $attributes[ 'table_info' ][ 'field_id' ] . '`';

                        if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                            $having[] = "{$filterfield} = " . $filter_value;
                        else
                            $where[] = "{$filterfield} = " . $filter_value;
                    }
                }
                elseif ( in_array( $attributes[ 'type' ], array( 'date', 'datetime' ) ) ) {
                    $start = date_i18n( 'Y-m-d' ) . ( 'datetime' == $attributes[ 'type' ] ) ? ' 00:00:00' : '';
                    $end = date_i18n( 'Y-m-d' ) . ( 'datetime' == $attributes[ 'type' ] ) ? ' 23:59:59' : '';

                    $start_value = pods_var( 'filter_' . $field . '_start', 'get', false );
                    $end_value = pods_var( 'filter_' . $field . '_end', 'get', false );

                    if ( empty( $start_value ) && empty( $end_value ) )
                        continue;

                    if ( !empty( $start_value ) )
                        $start = date_i18n( 'Y-m-d', strtotime( $start_value ) ) . ( 'datetime' == $attributes[ 'type' ] ? ' 00:00:00' : '' );

                    if ( !empty( $end_value ) )
                        $end = date_i18n( 'Y-m-d', strtotime( $end_value ) ) . ( 'datetime' == $attributes[ 'type' ] ? ' 23:59:59' : '' );

                    if ( true === $attributes[ 'date_ongoing' ] ) {
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
                    $filter_value = pods_var( 'filter_' . $field, 'get', '' );

                    if ( strlen( $filter_value ) < 1 )
                        continue;

                    if ( isset( $attributes[ 'group_related' ] ) && false !== $attributes[ 'group_related' ] )
                        $having[] = "{$filterfield} LIKE '%" . $filter_value . "%'";
                    else
                        $where[] = "{$filterfield} LIKE '%" . $filter_value . "%'";
                }

                if ( !empty( $where ) )
                    $params->where[] = '(' . implode( ' AND ', $where ) . ')';

                if ( !empty( $having ) )
                    $params->having[] = '(' . implode( ' AND ', $having ) . ')';
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

            preg_match_all( '/`?[\w]+`?(?:\\.`?[\w]+`?)+(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $haystack, $found, PREG_PATTERN_ORDER );

            $found = (array) @current( $found );
            $find = $replace = array();

            foreach ( $found as $key => $value ) {
                $value = str_replace( '`', '', $value );
                $value = explode( '.', $value );
                $dot = array_pop( $value );

                if ( in_array( '/\b' . trim( $found[ $key ], '`' ) . '\b(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/', $find ) ) {
                    unset( $found[ $key ] );

                    continue;
                }

                $find[ $key ] = '/\b' . trim( $found[ $key ], '`' ) . '\b(?=[^"\']*(?:"[^"]*"[^"]*|\'[^\']*\'[^\']*)*$)/';
                $esc_start = $esc_end = '`';

                if ( strlen( ltrim( $found[ $key ], '`' ) ) < strlen( $found[ $key ] ) )
                    $esc_start = '';

                if ( strlen( rtrim( $found[ $key ], '`' ) ) < strlen( $found[ $key ] ) )
                    $esc_end = '';

                if ( '*' != $dot )
                    $dot = '`' . $dot . $esc_end;

                $replace[ $key ] = $esc_start . implode( '_', $value ) . '`.' . $dot;

                if ( 't' == $value[ 0 ] ) {
                    unset( $found[ $key ] );

                    continue;
                }

                unset( $found[ $key ] );

                if ( !in_array( $value, $found ) )
                    $found[ $key ] = $value;
            }

            if ( !empty( $this->traverse ) ) {
                foreach ( (array) $this->traverse as $key => $traverse ) {
                    $traverse = str_replace( '`', '', $traverse );
                    $already_found = false;

                    foreach ( $found as $traversal ) {
                        if ( is_array( $traversal ) )
                            $traversal = implode( '.', $traversal );

                        if ( $traversal == $traverse ) {
                            $already_found = true;
                            break;
                        }
                    }

                    if ( !$already_found )
                        $found[ 'traverse_' . $key ] = explode( '.', $traverse );
                }
            }

            $joins = array();

            if ( !empty( $find ) ) {
                $params->select = preg_replace( $find, $replace, $params->select );
                $params->where = preg_replace( $find, $replace, $params->where );
                $params->groupby = preg_replace( $find, $replace, $params->groupby );
                $params->having = preg_replace( $find, $replace, $params->having );
                $params->orderby = preg_replace( $find, $replace, $params->orderby );

                if ( !empty( $found ) )
                    $joins = $this->traverse( $found, $params->fields, $params );
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
                " . ( $params->distinct ? 'DISTINCT' : '' ) . "
                " . ( !empty( $params->select ) ? ( is_array( $params->select ) ? implode( ', ', $params->select ) : $params->select ) : '*' ) . "
                FROM {$params->table} AS `t`
                " . ( !empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . "
                " . ( !empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND ', $params->where ) : $params->where ) : '' ) . "
                " . ( !empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . "
                " . ( !empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND ', $params->having ) : $params->having ) : '' ) . "
                " . ( !empty( $params->orderby ) ? 'ORDER BY ' . ( is_array( $params->orderby ) ? implode( ', ', $params->orderby ) : $params->orderby ) : '' ) . "
                " . ( ( 0 < $params->page && 0 < $params->limit ) ? 'LIMIT ' . $params->offset . ', ' . ( $params->limit ) : '' ) . "
            ";
            $this->total_sql = "
                SELECT
                " . ( $params->distinct ? 'DISTINCT' : '' ) . "
                COUNT(*)
                FROM {$params->table} AS `t`
                " . ( !empty( $params->join ) ? ( is_array( $params->join ) ? implode( "\n                ", $params->join ) : $params->join ) : '' ) . "
                " . ( !empty( $params->where ) ? 'WHERE ' . ( is_array( $params->where ) ? implode( ' AND ', $params->where ) : $params->where ) : '' ) . "
                " . ( !empty( $params->groupby ) ? 'GROUP BY ' . ( is_array( $params->groupby ) ? implode( ', ', $params->groupby ) : $params->groupby ) : '' ) . "
                " . ( !empty( $params->having ) ? 'HAVING ' . ( is_array( $params->having ) ? implode( ' AND ', $params->having ) : $params->having ) : '' ) . "
            ";
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
     * @since 2.0.0
     */
    public function total () {
        return (int) $this->total;
    }

    /**
     * Fetch the total row count total
     *
     * @return int Number of rows found by select()
     * @since 2.0.0
     */
    public function total_found () {
        if(false === $this->total_found_calculated)
            $this->calculate_totals();

        return (int) $this->total_found;
    }

    /**
     * Fetch the zebra switch
     *
     * @return bool Zebra state
     * @since 1.12
     */
    public function zebra () {
        $zebra = true;
        if ( 0 < ( $this->row_number % 2 ) ) // Odd numbers
            $zebra = false;
        return $zebra;
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     *
     * @return mixed
     *
     * @since 2.0.0
     */
    public function fetch ( $row = null ) {
        global $wpdb;

        $id = $row;

        if ( null === $row ) {
            $this->row_number++;

            $this->row = false;

            if ( isset( $this->data[ $this->row_number ] ) )
                $this->row = get_object_vars( $this->data[ $this->row_number ] );
        }
        else {
            $this->row_number = -1;

            $mode = 'id';
            $id = pods_absint( $row );

            if ( !is_numeric( $row ) || 0 === strpos( $row, '0' ) || $row != preg_replace( '/[^0-9]/', '', $row ) ) {
                $mode = 'slug';
                $id = $row;
            }

            $row = false;

            if ( !empty( $this->pod ) )
                $row = pods_cache_get( $id, 'pods_items_' . $this->pod );

            $get_table_data = false;
            $current_row_id = false;

            if ( false !== $row && is_array( $row ) )
                $this->row = $row;
            elseif ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) {
                if ( 'post_type' == $this->pod_data[ 'type' ] ) {
                    $post_type = $this->pod_data[ 'object' ];

                    if ( empty( $post_type ) )
                        $post_type = $this->pod_data[ 'name' ];
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

                if ( empty( $this->row ) )
                    $this->row = false;

                $current_row_id = $this->row['ID'];
                $get_table_data = true;
            }
            elseif ( 'taxonomy' == $this->pod_data[ 'type' ] ) {
                $taxonomy = $this->pod_data[ 'object' ];

                if ( empty( $taxonomy ) )
                    $taxonomy = $this->pod_data[ 'name' ];

                if ( 'id' == $mode )
                    $this->row = get_term( $id, $taxonomy, ARRAY_A );
                else
                    $this->row = get_term_by( 'slug', $id, $taxonomy, ARRAY_A );

                if ( empty( $this->row ) )
                    $this->row = false;

                $current_row_id = $this->row['term_id'];

                $get_table_data = true;
            }
            elseif ( 'user' == $this->pod_data[ 'type' ] ) {
                if ( 'id' == $mode )
                    $this->row = get_userdata( $id );
                else
                    $this->row = get_user_by( 'slug', $id );

                if ( empty( $this->row ) )
                    $this->row = false;
                else
                    $this->row = get_object_vars( $this->row );

                $current_row_id = $this->row['ID'];

                $get_table_data = true;
            }
            elseif ( 'comment' == $this->pod_data[ 'type' ] ) {
                $this->row = get_comment( $id, ARRAY_A );

                // No slug handling here

                if ( empty( $this->row ) )
                    $this->row = false;

                $current_row_id = $this->row['comment_ID'];

                $get_table_data = true;
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
                    $id = esc_sql( $id );
                    $params[ 'where' ] = "`t`.`{$this->field_slug}` = '{$id}'";
                }

                $this->row = $this->select( $params );

                if ( empty( $this->row ) )
                    $this->row = false;
                else
                    $this->row = get_object_vars( (object) @current( (array) $this->row ) );
            }

            if ( 'table' == $this->pod_data[ 'storage' ] && false !== $get_table_data && is_numeric($current_row_id)) {
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

                $row = $this->select( $params );

                if ( !empty( $row ) ) {
                    $row = get_object_vars( (object) @current( (array) $row ) );

                    if ( is_array( $this->row ) && !empty( $this->row ) )
                        $this->row = array_merge( $row, $this->row );
                    else
                        $this->row = $row;
                }
            }

            if ( !empty( $this->pod ) )
                pods_cache_set( $id, $this->row, 0, 'pods_items_' . $this->pod );
        }

        $this->row = $this->do_hook( 'fetch', $this->row, $id, $this->row_number );

        return $this->row;
    }

    /**
     * Reset the current data
     *
     * @param int $row Row number to reset to
     *
     * @return mixed
     *
     * @since 2.0.0
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
     * @since 2.0.0
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

            if ( 1 == pods_var( 'pods_debug_sql_all', 'get', 0 ) && is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) ) )
                echo '<textarea cols="100" rows="24">' . $params->sql . '</textarea>';
        }

        $params->sql = trim( $params->sql );

        // Run Query
        $params->sql = self::do_hook( 'query', $params->sql, $params );

        $result = $wpdb->query( $params->sql );

        $result = self::do_hook( 'query_result', $result, $params );

        if ( false === $result && !empty( $params->error ) && !empty( $wpdb->last_error ) )
            return pods_error( "{$params->error}; SQL: {$params->sql}; Response: {$wpdb->last_error}", $params->display_errors );

        if ( 'INSERT' == strtoupper( substr( $params->sql, 0, 6 ) ) || 'REPLACE' == strtoupper( substr( $params->sql, 0, 7 ) ) )
            $result = $wpdb->insert_id;
        elseif ( 'SELECT' == strtoupper( substr( $params->sql, 0, 6 ) ) ) {
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
     * @since 2.0.0
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

        $showTables = mysql_list_tables( DB_NAME );

        $finalTables = array();

        while ( $table = mysql_fetch_row( $showTables ) ) {
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public static function get_column_data ( $column_name, $table ) {
        $describe_data = mysql_query( 'DESCRIBE ' . $table );

        $column_data = array();

        while ( $column_row = mysql_fetch_assoc( $describe_data ) ) {
            $column_data[] = $column_row;
        }

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
     * @since 2.0.0
     */
    public static function prepare ( $sql, $data ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;
        list( $sql, $data ) = self::do_hook( 'prepare', array( $sql, $data ) );
        return $wpdb->prepare( $sql, $data );
    }

    /**
     * Setup fields for traversal
     *
     * @param array $fields Associative array of fields data
     *
     * @return array Traverse feed
     * @param object $params (optional) Parameters from build()
     *
     * @since 2.0.0
     */
    function traverse_build ( $fields = null, $params = null ) {
        if ( null === $fields )
            $fields = $this->fields;

        $feed = array();

        foreach ( $fields as $field => $data ) {
            if ( !is_array( $data ) )
                $field = $data;

            if ( 0 < strlen( pods_var( 'filter_' . $field ) ) )
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
     * @since 2.0.0
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
            'params' => new stdClass()
        );

        $traverse_recurse = array_merge( $defaults, $traverse_recurse );

        $table_mode = false;
        $joins = array();

        if ( empty( $traverse_recurse[ 'pod' ] ) ) {
            if ( !empty( $traverse_recurse[ 'params' ] ) && !empty( $traverse_recurse[ 'params' ]->table ) && 0 === strpos( $traverse_recurse[ 'params' ]->table, $wpdb->prefix ) ) {
                if ( $wpdb->posts == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'post_type';
                elseif ( $wpdb->users == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'user';
                elseif ( $wpdb->comments == $traverse_recurse[ 'params' ]->table )
                    $traverse_recurse[ 'pod' ] = 'comment';
                else
                    return $joins;

                $pod_data = array();
                $table_mode = true;

                if ( in_array( $traverse_recurse[ 'pod' ], array( 'user', 'comment' ) ) ) {
                    $pod = $this->api->load_pod( array( 'name' => $traverse_recurse[ 'pod' ] ) );

                    if ( !empty( $pod ) && $pod[ 'type' ] == $pod )
                        $pod_data = $pod;
                }

                if ( empty( $pod_data ) ) {
                    $pod_data = array(
                        'id' => 0,
                        'name' => '_table_' . $traverse_recurse[ 'pod' ],
                        'type' => $traverse_recurse[ 'pod' ],
                        'storage' => 'meta',
                        'fields' => $this->api->get_wp_object_fields( $traverse_recurse[ 'pod' ] )
                    );

                    $traverse_recurse[ 'pod' ] = $pod_data[ 'name' ];
                }
            }
            else
                return $joins;
        }

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file', 'avatar' ) );

        if ( !isset( $this->traversal[ $traverse_recurse[ 'pod' ] ] ) )
            $this->traversal[ $traverse_recurse[ 'pod' ] ] = array();

        if ( !$table_mode ) {
            $pod_data = $this->api->load_pod( array( 'name' => $traverse_recurse[ 'pod' ] ), false );

            if ( empty( $pod_data ) )
                return $joins;
        }

        if ( empty( $traverse_recurse[ 'fields' ] ) || !isset( $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] ] ) || empty( $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] ] ) )
            return $joins;

        $field = $traverse_recurse[ 'fields' ][ $traverse_recurse[ 'depth' ] ];

        if ( 'wpml_languages' == $field )
            return $joins;

        // Fallback to meta table if the pod type supports it
        if ( !isset( $pod_data[ 'fields' ][ $field ] ) ) {
            if ( in_array( $pod_data[ 'type' ], array( 'post_type', 'media', 'user', 'comment' ) ) )
                $pod_data[ 'fields' ][ $field ] = PodsForm::field_setup( array( 'name' => $field ) );
            else
                return $joins;
        }

        $traverse = $pod_data[ 'fields' ][ $field ];

        if ( !in_array( $traverse[ 'type' ], $tableless_field_types ) )
            $traverse[ 'table_info' ] = $this->api->get_table_info( $pod_data[ 'type' ], $pod_data[ 'name' ], $pod_data[ 'name' ], $pod_data );
        elseif ( empty( $traverse[ 'table_info' ] ) )
            $traverse[ 'table_info' ] = $this->api->get_table_info( $traverse[ 'pick_object' ], $traverse[ 'pick_val' ] );

        if ( isset( $this->traversal[ $traverse_recurse[ 'pod' ] ][ $traverse[ 'name' ] ] ) )
            $traverse = array_merge( $traverse, (array) $this->traversal[ $traverse_recurse[ 'pod' ] ][ $traverse[ 'name' ] ] );

        $traverse = $this->do_hook( 'traverse', $traverse, compact( 'pod', 'fields', 'joined', 'depth', 'joined_id', 'params' ) );

        if ( empty( $traverse ) )
            return $joins;

        $traverse = pods_sanitize( $traverse );

        $traverse[ 'id' ] = (int) $traverse[ 'id' ];
        $table_info = $traverse[ 'table_info' ];

        $this->traversal[ $traverse_recurse[ 'pod' ] ][ $field ] = $traverse;

        $field_joined = $field;

        if ( 0 < $traverse_recurse[ 'depth' ] && 't' != $traverse_recurse[ 'joined' ] )
            $field_joined = $traverse_recurse[ 'joined' ] . '_' . $field;

        /*if ( !empty( $this->search ) && 1 == 0 ) {
            if ( 0 < strlen( pods_var( 'filter_' . $field_joined, 'get' ) ) ) {
                $val = absint( pods_var( 'filter_' . $field_joined, 'get' ) );

                $search = "`{$field_joined}`.`{$table_info[ 'field_id' ]}` = {$val}";

                if ( 'text' == $this->search_mode ) {
                    $val = pods_var( 'filter_' . $field_joined, 'get' );

                    $search = "`{$field_joined}`.`{$traverse[ 'name' ]}` = '{$val}'";
                }
                elseif ( 'text_like' == $this->search_mode ) {
                    $val = pods_sanitize( like_escape( pods_var_raw( 'filter_' . $field_joined ) ) );

                    $search = "`{$field_joined}`.`{$traverse[ 'name' ]}` LIKE '%{$val}%'";
                }

                $this->search_where[] = " {$search} ";
            }
        }*/

        $rel_alias = 'rel_' . $field_joined;

        $the_join = null;

        $joined_id = $table_info[ 'field_id' ];
        $joined_index = $table_info[ 'field_index' ];

        if ( in_array( $traverse[ 'type' ], $tableless_field_types ) && ( 'pick' != $traverse[ 'type' ] || 'custom-simple' != pods_var( 'pick_object', $traverse ) ) ) {
            if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS ) {
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
            else {
                $the_join = "
                    LEFT JOIN `@wp_podsrel` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`field_id` = {$traverse[ 'id' ]}
                        AND `{$rel_alias}`.`item_id` = `{$traverse_recurse[ 'joined' ]}`.`id`

                    LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$rel_alias}`.`related_item_id`
                ";
            }
        }
        elseif ( 'meta' == $pod_data[ 'storage' ] ) {
            if ( ( $traverse_recurse[ 'depth' ] + 2 ) == count( $traverse_recurse[ 'fields' ] ) ) {
                $the_join = "
                    LEFT JOIN `{$table_info[ 'table' ]}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info[ 'field_index' ]}` = '{$traverse[ 'name' ]}'
                        AND `{$field_joined}`.`{$table_info[ 'field_id' ]}` = `{$traverse_recurse[ 'joined' ]}`.`{$traverse_recurse[ 'joined_id' ]}`
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
            'pod' => $table_info[ 'pod' ][ 'name' ],
            'fields' => $traverse_recurse[ 'fields' ],
            'joined' => $field_joined,
            'depth' => ( $traverse_recurse[ 'depth' ] + 1 ),
            'joined_id' => $joined_id,
            'joined_index' => $joined_index,
            'params' => $traverse_recurse[ 'params' ]
        );

        $the_join = $this->do_hook( 'traverse_the_join', $the_join, $traverse_recurse, $traverse_recursive );

        if ( empty( $the_join ) )
            return $joins;

        $joins[ $traverse_recurse[ 'pod' ] . '_' . $traverse_recurse[ 'depth' ] . '_' . $traverse[ 'id' ] ] = $the_join;

        if ( ( $traverse_recurse[ 'depth' ] + 1 ) < count( $traverse_recurse[ 'fields' ] ) && null !== $table_info[ 'pod' ] && false !== $table_info[ 'recurse' ] )
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
                'params' => $params
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
     * @since 2.0.0
     */
    private function do_hook () {
        $args = func_get_args();

        if ( empty( $args ) )
            return false;

        $name = array_shift( $args );

        return pods_do_hook( 'data', $name, $args, $this );
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

        $sql = str_replace( '@wp_users', $wpdb->users, $sql );
        $sql = str_replace( '@wp_', $wpdb->prefix, $sql );
        $sql = str_replace( '{prefix}', '@wp_', $sql );
        $sql = str_replace( '{/prefix/}', '{prefix}', $sql );
        return $sql;
    }
}
