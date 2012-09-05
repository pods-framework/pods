<?php
/**
 *
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
    public $join = '';

    /**
     * @var array
     */
    public $where = array();

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
    public $search_where = '';

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
     * Data Abstraction Class for Pods
     *
     * @param string $pod Pod name
     * @param integer $id Pod Item ID
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct ( $pod = null, $id = 0 ) {
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

            if ( false === $this->pod_data )
                return pods_error( 'Pod not found', $this );

            $this->pod_id = $this->pod_data[ 'id' ];
            $this->pod = $this->pod_data[ 'name' ];
            $this->fields = $this->pod_data[ 'fields' ];

            if ( isset( $this->pod_data[ 'options' ][ 'detail_page' ] ) )
                $this->detail_page = $this->pod_data[ 'options' ][ 'detail_page' ];

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

            if ( isset( $this->pod_data[ 'where' ] ) )
                $this->where = $this->pod_data[ 'where' ];

            if ( isset( $this->pod_data[ 'orderby' ] ) )
                $this->orderby = $this->pod_data[ 'orderby' ];

            if ( null !== $id && !is_array( $id ) && !is_object( $id ) ) {
                $this->id = pods_absint( $id );

                $this->fetch( $this->id );
            }
        }
    }

    /**
     * Insert an item, eventually mapping to WPDB::insert
     *
     * @param string $table
     * @param array $data
     * @param array $format
     *
     * @since 2.0.0
     */
    public function insert ( $table, $data, $format = null ) {
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
     * Update an item, eventually mapping to WPDB::update
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $format
     * @param array $where_format
     *
     * @since 2.0.0
     */
    public function update ( $table, $data, $where, $format = null, $where_format = null ) {
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
     * @param string $table
     * @param array $where
     * @param array $where_format
     *
     * @since 2.0.0
     */
    public function delete ( $table, $where, $where_format = null ) {
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

        return $this->query( $this->prepare( $sql, $where ) );
    }

    /**
     * Select items, eventually building dynamic query
     *
     * @param array $params
     *
     * @since 2.0.0
     */
    public function select ( $params ) {
        global $wpdb;

        // Build
        $this->sql = $this->build( $params );

        // Get Data
        $results = pods_query( $this->sql, $this );
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

        // Set totals
        $total = @current( $wpdb->get_col( "SELECT FOUND_ROWS()" ) );
        $total = $this->do_hook( 'select_total', $total );

        $this->total_found = 0;

        if ( is_numeric( $total ) )
            $this->total_found = $total;

        $this->total = count( (array) $this->data );

        return $this->data;
    }

    /**
     * Build/Rewrite dynamic SQL and handle search/filter/sort
     *
     * @param array $params
     * @since 2.0.0
     */
    public function build ( &$params ) {
        $defaults = array(
            'select' => '*',
            'table' => null,
            'join' => null,
            'where' => null,
            'groupby' => null,
            'having' => null,
            'orderby' => null,
            'limit' => -1,

            'identifier' => 'id',
            'index' => 'name',

            'page' => 1,
            'search' => null,
            'search_query' => null,
            'filters' => array(),

            'fields' => array(),
            'traverse' => array(),

            'sql' => null,

            'strict' => false
        );

        $params = (object) array_merge( $defaults, (array) $params );

        // Validate
        $params->page = pods_absint( $params->page );

        if ( 0 == $params->page )
            $params->page = 1;

        $params->limit = (int) $params->limit;

        if ( 0 == $params->limit )
            $params->limit = -1;

        if ( ( empty( $params->fields ) || !is_array( $params->fields ) ) && is_object( $this->pod_data ) && isset( $this->fields ) && !empty( $this->fields ) )
            $params->fields = $this->fields;

        if ( empty( $params->table ) && is_object( $this->pod_data ) && isset( $this->table ) && !empty( $this->table ) )
            $params->table = $this->table;

        if ( false === strpos( $params->table, '(' ) && false === strpos( $params->table, '`' ) )
            $params->table = '`' . $params->table . '`';

        if ( !empty( $params->join ) )
            $params->join = array_merge( (array) $this->join, (array) $params->join );
        elseif ( false === $params->strict )
            $params->join = $this->join;

        $params->where = (array) $params->where;

        if ( false === $params->strict && !empty( $this->where ) )
            $params->where = array_merge( $params->where, (array) $this->where );

        if ( empty( $params->where ) )
            $params->where = array();

        $params->having = (array) $params->having;

        if ( empty( $params->having ) )
            $params->having = array();

        $params->orderby = (array) $params->orderby;

        if ( false === $params->strict && !empty( $this->orderby ) )
            $params->orderby = array_merge( $params->orderby, (array) $this->orderby );

        if ( empty( $params->orderby ) )
            $params->orderby = array();

        if ( !empty( $params->traverse ) )
            $this->traverse = $params->traverse;

        // Get Aliases for future reference
        $selectsfound = '';
        if (!empty($params->select)) {
            if (is_array($params->select))
                $selectsfound = implode(', ', $params->select);
            else
                $selectsfound = $params->select;
        }

        // Pull Aliases from SQL query too
        if (null !== $params->sql) {
            $temp_sql = ' ' . trim(str_replace(array("\n", "\r"), ' ', $params->sql));
            $temp_sql = preg_replace(array('/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
                                      '/\sSELECT\s/i'),
                                array(' SELECT ',
                                      ' SELECT SQL_CALC_FOUND_ROWS '),
                                $temp_sql);
            preg_match('/\sSELECT SQL_CALC_FOUND_ROWS\s(.*)\sFROM/i', $temp_sql, $selectmatches);
            if (isset($selectmatches[1]) && !empty($selectmatches[1]) && false !== stripos($selectmatches[1], ' AS '))
                $selectsfound .= (!empty($selectsfound) ? ', ' : '') . $selectmatches[1];
        }

        // Build Alias list
        $this->aliases = array();

        if (!empty($selectsfound) && false !== stripos($selectsfound, ' AS ')) {
            $theselects = array_filter(explode(', ', $selectsfound));

            if (empty($theselects))
                $theselects = array_filter(explode(',', $selectsfound));

            foreach ($theselects as $selected) {
                $selected = trim($selected);

                if (strlen($selected) < 1)
                    continue;

                $selectfield = explode(' AS ', str_replace(' as ', ' AS ', $selected));

                if (2 == count($selectfield)) {
                    $field = trim(trim($selectfield[1]), '`');
                    $real_field = trim(trim($selectfield[0]), '`');
                    $this->aliases[$field] = $real_field;
                }
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
            $haystack = str_replace( array( '(', ')', '  ' ), ' ', $haystack );

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

                if ( in_array( $value[ 0 ], array( 't', 'p' ) ) ) {
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
                    $joins = $this->traverse( $found );
                elseif ( false !== $this->search )
                    $joins = $this->traverse();
            }
        }

        if ( !empty( $params->join ) && !empty( $joins ) )
            $params->join = array_merge( (array) $params->join, $joins );

        if (null !== $params->search && !empty($params->fields)) {
            // Search
            if (false !== $params->search_query && 0 < strlen($params->search_query)) {
                $where = $having = array();

                foreach ($params->fields as $key => $field) {
                    $attributes = $field;

                    if (!is_array($attributes))
                        $attributes = array();

                    if (false === $attributes['search'])
                        continue;

                    if (in_array($attributes['type'], array('date', 'time', 'datetime')))
                        continue;

                    if (is_array($field))
                        $field = pods_var( 'name', $attributes, $key );

                    if (isset($params->filters[$field]))
                        continue;

                    $fieldfield = '`' . $field . '`';

                    if (isset($this->aliases[$field]))
                        $fieldfield = '`' . $this->aliases[$field] . '`';

                    if (false !== $attributes['real_name'] && !empty( $attributes[ 'real_name' ] ) )
                        $fieldfield = $attributes['real_name'];

                    if (false !== $attributes['group_related'])
                        $having[] = "{$fieldfield} LIKE '%" . pods_sanitize($params->search_query) . "%'";
                    else
                        $where[] = "{$fieldfield} LIKE '%" . pods_sanitize($params->search_query) . "%'";
                }

                if (!empty($where))
                    $params->where[] = '(' . implode(' OR ', $where) . ')';

                if (!empty($having))
                    $params->having[] = '(' . implode(' OR ', $having) . ')';
            }

            // Traversal Search
            if ( 0 < strlen( $this->search_where ) )
                $params->where[] = $this->search_where;

            // Filter
            foreach ($params->filters as $filter) {
                $where = $having = array();

                if (!isset($params->fields[$filter]))
                    continue;

                $filterfield = '`' . $filter . '`';

                if (isset($this->aliases[$filter]))
                    $filterfield = '`' . $this->aliases[$filter] . '`';

                if (false !== $params->fields[$filter]['real_name'])
                    $filterfield = $params->fields[$filter]['real_name'];

                if (in_array($params->fields[$filter]['type'], array('date', 'datetime'))) {
                    $start = date('Y-m-d') . ('datetime' == $params->fields[$filter]['type']) ? ' 00:00:00' : '';
                    $end = date('Y-m-d') . ('datetime' == $params->fields[$filter]['type']) ? ' 23:59:59' : '';

                    if (strlen(pods_var('filter_' . $filter . '_start', 'get', false)) < 1 && strlen(pods_var('filter_' . $filter . '_end', 'get', false)) < 1)
                        continue;

                    if (0 < strlen(pods_var('filter_' . $filter . '_start', 'get', false)))
                        $start = date('Y-m-d', strtotime(pods_var('filter_' . $filter . '_start', 'get', false))) . ('datetime' == $params->fields[$filter]['type']) ? ' 00:00:00' : '';

                    if (0 < strlen(pods_var('filter_' . $filter . '_end', 'get', false)))
                        $end = date('Y-m-d', strtotime(pods_var('filter_' . $filter . '_end', 'get', false))) . ('datetime' == $params->fields[$filter]['type']) ? ' 23:59:59' : '';
                    if (false !== $params->fields[$filter]['date_ongoing']) {
                        $date_ongoing = '`' . $params->fields[$filter]['date_ongoing'] . '`';

                        if (isset($this->aliases[$date_ongoing]))
                            $date_ongoing = '`' . $this->aliases[$date_ongoing] . '`';

                        if (false !== $params->fields[$filter]['group_related'])
                            $having[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
                        else
                            $where[] = "(({$filterfield} <= '$start' OR ({$filterfield} >= '$start' AND {$filterfield} <= '$end')) AND ({$date_ongoing} >= '$start' OR ({$date_ongoing} >= '$start' AND {$date_ongoing} <= '$end')))";
                    }
                    else {
                        if (false !== $params->fields[$filter]['group_related'])
                            $having[] = "({$filterfield} BETWEEN '$start' AND '$end')";
                        else
                            $where[] = "({$filterfield} BETWEEN '$start' AND '$end')";
                    }
                }
                elseif (0 < strlen(pods_var('filter_' . $filter, 'get', false))) {
                    if (false !== $params->fields[$filter]['group_related'])
                        $having[] = "{$filterfield} LIKE '%" . pods_sanitize(pods_var('filter_' . $filter, 'get', false)) . "%'";
                    else
                        $where[] = "{$filterfield} LIKE '%" . pods_sanitize(pods_var('filter_' . $filter, 'get', false)) . "%'";
                }

                if (!empty($where))
                    $params->where[] = '(' . implode(' AND ', $where) . ')';

                if (!empty($having))
                    $params->having[] = '(' . implode(' AND ', $having) . ')';
            }
        }

        // Build
        if (null === $params->sql) {
            $sql = "
                SELECT SQL_CALC_FOUND_ROWS
                " . (!empty($params->select) ? (is_array($params->select) ? implode(', ', $params->select) : $params->select) : '*') . "
                FROM {$params->table} AS `t`
                " . (!empty($params->join) ? (is_array($params->join) ? implode("\n                ", $params->join) : $params->join) : '') . "
                " . (!empty($params->where) ? 'WHERE ' . (is_array($params->where) ? implode(' AND ', $params->where) : $params->where) : '') . "
                " . (!empty($params->groupby) ? 'GROUP BY ' . (is_array($params->groupby) ? implode(', ', $params->groupby) : $params->groupby) : '') . "
                " . (!empty($params->having) ? 'HAVING ' . (is_array($params->having) ? implode(' AND ', $params->having) : $params->having) : '') . "
                " . (!empty($params->orderby) ? 'ORDER BY ' . (is_array($params->orderby) ? implode(', ', $params->orderby) : $params->orderby) : '') . "
                " . ((0 < $params->page && 0 < $params->limit) ? 'LIMIT ' . (($params->page - 1) * $params->limit) . ', ' . ($params->limit) : '') . "
            ";
        }
        // Rewrite
        else {
            $sql = ' ' . trim(str_replace(array("\n", "\r"), ' ', $this->sql));
            $sql = preg_replace(array('/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i',
                                      '/\sSELECT\s/i'),
                                array(' SELECT ',
                                      ' SELECT SQL_CALC_FOUND_ROWS '),
                                $sql);

            // Insert variables based on existing statements
            if (false === stripos($sql, '%%SELECT%%'))
                $sql = preg_replace('/\sSELECT\sSQL_CALC_FOUND_ROWS\s/i', ' SELECT SQL_CALC_FOUND_ROWS %%SELECT%% ', $sql);
            if (false === stripos($sql, '%%WHERE%%'))
                $sql = preg_replace('/\sWHERE\s(?!.*\sWHERE\s)/gi', ' WHERE %%WHERE%% ', $sql);
            if (false === stripos($sql, '%%GROUPBY%%'))
                $sql = preg_replace('/\sGROUP BY\s(?!.*\sGROUP BY\s)/gi', ' GROUP BY %%GROUPBY%% ', $sql);
            if (false === stripos($sql, '%%HAVING%%'))
                $sql = preg_replace('/\sHAVING\s(?!.*\sHAVING\s)/gi', ' HAVING %%HAVING%% ', $sql);
            if (false === stripos($sql, '%%ORDERBY%%'))
                $sql = preg_replace('/\sORDER BY\s(?!.*\sORDER BY\s)/gi', ' ORDER BY %%ORDERBY%% ', $sql);

            // Insert variables based on other existing statements
            if (false === stripos($sql, '%%JOIN%%')) {
                if (false !== stripos($sql, ' WHERE '))
                    $sql = preg_replace('/\sWHERE\s(?!.*\sWHERE\s)/gi', ' %%JOIN%% WHERE ', $sql);
                elseif (false !== stripos($sql, ' GROUP BY '))
                    $sql = preg_replace('/\sGROUP BY\s(?!.*\sGROUP BY\s)/gi', ' %%WHERE%% GROUP BY ', $sql);
                elseif (false !== stripos($sql, ' ORDER BY '))
                    $sql = preg_replace('/\ORDER BY\s(?!.*\ORDER BY\s)/gi', ' %%WHERE%% ORDER BY ', $sql);
                else
                    $sql .= ' %%JOIN%% ';
            }
            if (false === stripos($sql, '%%WHERE%%')) {
                if (false !== stripos($sql, ' GROUP BY '))
                    $sql = preg_replace('/\sGROUP BY\s(?!.*\sGROUP BY\s)/gi', ' %%WHERE%% GROUP BY ', $sql);
                elseif (false !== stripos($sql, ' ORDER BY '))
                    $sql = preg_replace('/\ORDER BY\s(?!.*\ORDER BY\s)/gi', ' %%WHERE%% ORDER BY ', $sql);
                else
                    $sql .= ' %%WHERE%% ';
            }
            if (false === stripos($sql, '%%GROUPBY%%')) {
                if (false !== stripos($sql, ' HAVING '))
                    $sql = preg_replace('/\sHAVING\s(?!.*\sHAVING\s)/gi', ' %%GROUPBY%% HAVING ', $sql);
                elseif (false !== stripos($sql, ' ORDER BY '))
                    $sql = preg_replace('/\ORDER BY\s(?!.*\ORDER BY\s)/gi', ' %%GROUPBY%% ORDER BY ', $sql);
                else
                    $sql .= ' %%GROUPBY%% ';
            }
            if (false === stripos($sql, '%%HAVING%%')) {
                if (false !== stripos($sql, ' ORDER BY '))
                    $sql = preg_replace('/\ORDER BY\s(?!.*\ORDER BY\s)/gi', ' %%HAVING%% ORDER BY ', $sql);
                else
                    $sql .= ' %%HAVING%% ';
            }
            if (false === stripos($sql, '%%ORDERBY%%'))
                $sql .= ' %%ORDERBY%% ';
            if (false === stripos($sql, '%%LIMIT%%'))
                $sql .= ' %%LIMIT%% ';

            // Replace variables
            if (0 < strlen($params->select)) {
                if (false === stripos($sql, '%%SELECT%% FROM '))
                    $sql = str_ireplace('%%SELECT%%', $params->select . ', ', $sql);
                else
                    $sql = str_ireplace('%%SELECT%%', $params->select, $sql);
            }
            if (0 < strlen($params->join))
                $sql = str_ireplace('%%JOIN%%', $params->join, $sql);
            if (0 < strlen($params->where)) {
                if (false !== stripos($sql, ' WHERE ')) {
                    if (false !== stripos($sql, ' WHERE %%WHERE%% '))
                        $sql = str_ireplace('%%WHERE%%', $params->where . ' AND ', $sql);
                    else
                        $sql = str_ireplace('%%WHERE%%', ' AND ' . $params->where, $sql);
                }
                else
                    $sql = str_ireplace('%%WHERE%%', ' WHERE ' . $params->where, $sql);
            }
            if (0 < strlen($params->groupby)) {
                if (false !== stripos($sql, ' GROUP BY ')) {
                    if (false !== stripos($sql, ' GROUP BY %%GROUPBY%% '))
                        $sql = str_ireplace('%%GROUPBY%%', $params->groupby . ', ', $sql);
                    else
                        $sql = str_ireplace('%%GROUPBY%%', ', ' . $params->groupby, $sql);
                }
                else
                    $sql = str_ireplace('%%GROUPBY%%', ' GROUP BY ' . $params->groupby, $sql);
            }
            if (0 < strlen($params->having) && false !== stripos($sql, ' GROUP BY ')) {
                if (false !== stripos($sql, ' HAVING ')) {
                    if (false !== stripos($sql, ' HAVING %%HAVING%% '))
                        $sql = str_ireplace('%%HAVING%%', $params->having . ' AND ', $sql);
                    else
                        $sql = str_ireplace('%%HAVING%%', ' AND ' . $params->having, $sql);
                }
                else
                    $sql = str_ireplace('%%HAVING%%', ' HAVING ' . $params->having, $sql);
            }
            if (0 < strlen($params->orderby)) {
                if (false !== stripos($sql, ' ORDER BY ')) {
                    if (false !== stripos($sql, ' ORDER BY %%ORDERBY%% '))
                        $sql = str_ireplace('%%ORDERBY%%', $params->having . ', ', $sql);
                    else
                        $sql = str_ireplace('%%ORDERBY%%', ', ' . $params->having, $sql);
                }
                else
                    $sql = str_ireplace('%%ORDERBY%%', ' ORDER BY ' . $params->groupby, $sql);
            }
            if (0 < $params->page && 0 < $params->limit) {
                $start = ($params->page - 1) * $params->limit;
                $end = $start + $params->limit;
                $sql .= 'LIMIT ' . (int) $start . ', ' . (int) $end;
            }

            // Clear any unused variables
            $sql = str_ireplace(array('%%SELECT%%',
                                      '%%JOIN%%',
                                      '%%WHERE%%',
                                      '%%GROUPBY%%',
                                      '%%HAVING%%',
                                      '%%ORDERBY%%',
                                      '%%LIMIT%%'), '', $sql);
            $sql = str_replace(array('``', '`'), array('  ', ' '), $sql);
        }

        // Debug purposes
        if (1 == pods_var('debug_sql', 'get', 0) && is_user_logged_in() && is_super_admin())
            echo "<textarea cols='130' rows='30'>{$sql}</textarea>";

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
        if (0 < ($this->row_number % 2)) // Odd numbers
            $zebra = false;
        return $zebra;
    }

    /**
     * Create a Table
     *
     * @param string $table
     * @param string $fields
     * @param boolean $if_not_exists
     *
     * @since 2.0.0
     */
    public static function table_create ( $table, $fields, $if_not_exists = false ) {
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
     * @param string $table
     * @param string $changes
     *
     * @since 2.0.0
     */
    public static function table_alter ( $table, $changes ) {
        global $wpdb;

        $sql = "ALTER TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}` {$changes}";

        return self::query( $sql );
    }

    /**
     * Truncate a Table
     *
     * @param string $table
     *
     * @since 2.0.0
     */
    public static function table_truncate ( $table ) {
        global $wpdb;

        $sql = "TRUNCATE TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}`";

        return self::query( $sql );
    }

    /**
     * Drop a Table
     *
     * @param string $table
     *
     * @since 2.0.0
     */
    public static function table_drop ( $table ) {
        global $wpdb;

        $sql = "DROP TABLE `{$wpdb->prefix}" . self::$prefix . "{$table}`";

        return self::query( $sql );
    }

    /**
     * Reorder Items
     *
     * @param string $table
     * @param string $weight_field
     * @param string $id_field
     * @param array $ids
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
     * @param null $row
     *
     * @return mixed
     */
    public function fetch ( $row = null ) {
        global $wpdb;

        if ( null === $row ) {
            $this->row_number++;

            $this->row = false;

            if ( isset( $this->data[ $this->row_number ] ) )
                $this->row = get_object_vars( $this->data[ $this->row_number ] );
        }
        else {
            $this->row_number = -1;

            $id = pods_absint( $row );

            $row = false;

            if ( !empty($this->pod ) )
                $row = wp_cache_get( $id, 'pods_items_' . $this->pod );

            $get_table_data = false;

            if ( false !== $row && is_array( $row ) )
                $this->row = $row;
            elseif ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) {
                $this->row = get_post( $id, ARRAY_A );

                if ( empty( $this->row ) )
                    $this->row = false;

                $get_table_data = true;
            }
            elseif ( 'taxonomy' == $this->pod_data[ 'type' ] ) {
                $taxonomy = $this->pod_data[ 'object' ];

                if ( empty( $taxonomy ) )
                    $taxonomy = $this->pod_data[ 'name' ];

                $this->row = get_term( $id, $taxonomy, ARRAY_A );

                if ( empty( $this->row ) )
                    $this->row = false;

                $get_table_data = true;
            }
            elseif ( 'user' == $this->pod_data[ 'type' ] ) {
                $this->row = get_userdata( $id );

                if ( empty( $this->row ) )
                    $this->row = false;
                else
                    $this->row = get_object_vars( $this->row );

                $get_table_data = true;
            }
            elseif ( 'comment' == $this->pod_data[ 'type' ] ) {
                $this->row = get_comment( $id, ARRAY_A );

                if ( empty( $this->row ) )
                    $this->row = false;

                $get_table_data = true;
            }
            else {
                $params = array(
                    'table' => $this->table,
                    'where' => "`t`.`{$this->field_id}` = {$id}",
                    'orderby' => "`t`.`{$this->field_id}` DESC",
                    'page' => 1,
                    'limit' => 1,
                    'search' => false
                );

                $this->row = $this->select( $params );

                if ( empty( $this->row ) )
                    $this->row = false;
                else
                    $this->row = get_object_vars( (object) @current( (array) $this->row ) );
            }

            if ( 'table' == $this->pod_data[ 'storage' ] && false !== $get_table_data ) {
                $params = array(
                    'table' => $wpdb->prefix . "pods_tbl_",
                    'where' => "`t`.`id` = {$id}",
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
                wp_cache_set( $id, $this->row, 'pods_items_' . $this->pod );
        }

        return $this->row;
    }

    /**
     * @param null $row
     * @return mixed
     */
    public function reset ( $row = null ) {
        $row = pods_absint( $row );

        if ( empty( $row ) )
            $this->row_number = 0;

        $this->row = false;

        if ( isset( $this->data[ $this->row_number ] ) )
            $this->row = get_object_vars( $this->data[ $this->row_number ] );

        return $this->row;
    }

    /**
     * @static
     * @param $sql
     * @param string $error
     * @param null $results_error
     * @param null $no_results_error
     * @return array|bool|mixed|null|void
     */
    public static function query ($sql, $error = 'Database Error', $results_error = null, $no_results_error = null) {
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
        }

        $params->sql = trim( $params->sql );

        // Run Query
        $params->sql = self::do_hook( 'query', $params->sql, $params );

        $result = $wpdb->query( $params->sql );

        $result = self::do_hook( 'query_result', $result, $params );

        if ( false === $result && !empty( $params->error ) && !empty( $wpdb->last_error ) )
            return pods_error( "{$params->error}; SQL: {$params->sql}; Response: {$wpdb->last_error}", $params->display_errors );

        if ( 'INSERT' == substr( $params->sql, 0, 6 ) || 'REPLACE' == substr( $params->sql, 0, 7 ) )
            $result = $wpdb->insert_id;
        elseif ( 'SELECT' == substr( $params->sql, 0, 6 ) ) {
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
     */
    public static function get_tables ($wp_core = true, $pods_tables = true) {
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

        $showTables = mysql_list_tables(DB_NAME);

        $finalTables = array();

        while ($table = mysql_fetch_row($showTables)) {
            if (!$pods_tables && 0 === (strpos($table[0], $wpdb->prefix . rtrim(self::$prefix, '_')))) // don't include pods tables
                continue;
            elseif (!$wp_core && in_array($table[0], $core_wp_tables))
                continue;
            else
                $finalTables[] = $table[0];
        }

        return $finalTables;
    }

    /**
     * Gets column information from a table
     *
     * @param string $table
     */
    public static function get_table_columns ($table) {
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
     * @param string $table
     */
    public static function get_column_data ($column_name, $table) {
        $describe_data = mysql_query('DESCRIBE ' . $table);

        $column_data = array();

        while ($column_row = mysql_fetch_assoc($describe_data)) {
            $column_data[] = $column_row;
        }

        foreach ($column_data as $single_column) {
            if ($column_name == $single_column['Field'])
                return $single_column;
        }

        return $column_data;
    }

    /**
     * Prepare values for the DB
     *
     * @param string $sql
     * @param array $data
     */
    public static function prepare ($sql, $data) {
        global $wpdb;
        list($sql, $data) = self::do_hook('prepare', array($sql, $data));
        return $wpdb->prepare($sql, $data);
    }

    /**
     * Setup fields for traversal
     */
    function traverse_build ( $fields ) {
        $feed = array();

        foreach ( $fields as $field => $data ) {
            if ( !is_array( $data ) )
                $field = $data;

            if ( isset( $_GET[ $field ] ) || isset( $_GET[ 'filter_' . $field ] ) )
                $feed[ 'traverse_' . $field ] = array( $field );
        }

        return $feed;
    }

    /**
     * Recursively join tables based on fields
     */
    function traverse_recurse ( $pod, $fields, $joined = 't', $depth = 0 ) {
        global $wpdb;

        if ( empty( $pod ) )
            return array();

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        if ( !isset( $this->traversal[ $pod ] ) )
            $this->traversal[ $pod ] = array();
        $pod_data = $this->api->load_pod( array( 'name' => $pod ) );

        if ( empty( $pod_data ) )
            return array();

        $pod_id = (int) $pod_data[ 'id' ];

        foreach ( $pod_data[ 'fields' ] as $field ) {
            if ( !in_array( $field[ 'type' ], $tableless_field_types ) && !isset( $this->traversal[ $pod ][ $field[ 'name' ] ] ) )
                continue;

            $the_pod = null;
            $table = $field[ 'pick_object' ];
            $v = $field[ 'pick_val' ];
            $on = 'id';
            $name = 'name';
            $recurse = true;

            if ( 'file' == $field[ 'type' ] || 'post_type' == $table || 'media' == $table ) {
                $table = $wpdb->posts;
                $on = 'ID';
                $name = 'post_title';
                $recurse = false;
            }
            elseif ( 'taxonomy' == $table ) {
                $table = $wpdb->terms;
                $on = 'term_id';
                $recurse = false;
            }
            elseif ( 'user' == $table ) {
                $table = $wpdb->users;
                $on = 'ID';
                $name = 'display_name';
                $recurse = false;
            }
            elseif ( 'comment' == $table ) {
                $table = $wpdb->comments;
                $on = 'comment_ID';
                $name = 'comment_date';
                $recurse = false;
            }
            elseif ( !empty( $table ) ) {
                $the_pod = $table;
                $table = '@wp_pods_tbl_' . $table;
            }

            $traverse = array_merge(
                $field,
                array(
                    'table' => $table,
                    'pod' => $the_pod,
                    'on' => $on,
                    'name' => $name,
                    'recurse' => $recurse
                )
            );

            if ( isset( $this->traversal[ $pod ][ $field[ 'name' ] ] ) )
                $traverse = array_merge( $traverse, (array) $this->traversal[ $pod ][ $field[ 'name' ] ] );

            $this->traversal[ $pod ][ $field[ 'name' ] ] = $this->do_hook( 'traverse', $traverse, $pod, $fields, $joined, $depth );
        }

        $joins = array();

        if ( !isset( $fields[ $depth ] ) || empty( $fields[ $depth ] ) )
            return $joins;

        $field = $fields[ $depth ];

        if ( !isset( $this->traversal[ $pod ][ $field ] ) )
            return $joins;

        $this->traversal[ $pod ][ $field ] = array_merge(
            array(
                'table' => null,
                'pod' => null,
                'on' => 'id',
                'name' => 'name',
                'recurse' => false,
                'id' => 0,
                'type' => null
            ),
            $this->traversal[ $pod ][ $field ]
        );

        $this->traversal[ $pod ][ $field ][ 'id' ] = (int) $this->traversal[ $pod ][ $field ][ 'id' ];
        $field_joined = $field;

        if ( 0 < $depth && 't' != $joined )
            $field_joined = $joined . '_' . $field;

        if ( false !== $this->search ) {
            if ( 0 < strlen( pods_var( $field_joined, 'get' ) ) ) {
                $val = absint( pods_var( $field_joined, 'get' ) );
                $on = $this->traversal[ $pod ][ $field ][ 'on' ];
                $search = "`{$field_joined}`.`{$on}` = {$val}";

                if ( 'text' == $this->search_mode ) {
                    $val = pods_var( $field_joined, 'get' );
                    $on = $this->traversal[ $pod ][ $field ][ 'name' ];
                    $search = "`{$field_joined}`.`{$on}` = '{$val}'";
                }
                elseif ( 'text_like' == $this->search_mode ) {
                    $val = pods_sanitize( like_escape( $_GET[ $field_joined ] ) );
                    $on = $this->traversal[ $pod ][ $field ][ 'name' ];
                    $search = "`{$field_joined}`.`{$on}` LIKE '%{$val}%'";
                }

                $this->search_where .= " AND {$search} ";
            }
        }

        $rel_alias = 'rel_' . $field_joined;
        $the_join = "
            LEFT JOIN `@wp_pods_rel` AS `{$rel_alias}` ON `{$rel_alias}`.`field_id` = {$this->traversal[$pod][$field]['id']} AND `{$rel_alias}`.`item_id` = `{$joined}`.`id`
            LEFT JOIN `{$this->traversal[$pod][$field]['table']}` AS `{$field_joined}` ON `{$field_joined}`.`{$this->traversal[$pod][$field]['on']}` = `{$rel_alias}`.`related_item_id`
        ";

        if ( !in_array( $this->traversal[ $pod ][ $field ][ 'type' ], array( 'pick', 'file' ) ) ) {
            $the_join = "
            LEFT JOIN `{$this->traversal[$pod][$field]['table']}` AS `{$field_joined}` ON `{$field_joined}`.`{$this->traversal[$pod][$field]['on']}` = CONVERT(`{$joined}`.`{$field_joined}`, SIGNED)
            ";
        }

        $joins[ $pod . '_' . $depth . '_' . $this->traversal[ $pod ][ $field ][ 'id' ] ] = apply_filters( 'pods_traverse_the_join', $the_join, $pod, $fields, $joined, $depth, $this );

        if ( ( $depth + 1 ) < count( $fields ) && null !== $this->traversal[ $pod ][ $field ][ 'pod' ] && false !== $this->traversal[ $pod ][ $field ][ 'recurse' ] )
            $joins = array_merge( $joins, $this->traverse_recurse( $this->traversal[ $pod ][ $field ][ 'pod' ], $fields, $field_joined, ( $depth + 1 ) ) );

        return $joins;
    }

    /**
     * Recursively join tables based on fields
     */
    function traverse ( $fields = null ) {
        $joins = array();

        if ( null === $fields )
            $fields = $this->traverse_build( $this->fields );

        foreach ( (array) $fields as $field_group ) {
            if ( is_array( $field_group ) )
                $joins = array_merge( $joins, $this->traverse_recurse( $this->pod, $field_group ) );
            else {
                $joins = array_merge( $joins, $this->traverse_recurse( $this->pod, $fields ) );
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
}
