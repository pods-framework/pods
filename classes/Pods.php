<?php
/**
 * @package Pods
 */
class Pods {

    /**
     * @var PodsAPI
     */
    public $api;

    /**
     * @var PodsData
     */
    public $data;

    /**
     * @var Array of pod item arrays
     */
    public $rows;

    /**
     * @var Current pod item array
     */
    public $row;

    /**
     * @var bool
     */
    public $display_errors = false;

    /**
     * @var array|bool|mixed|null|void
     */
    public $pod_data;

    /**
     * @var
     */
    public $pod;

    /**
     * @var
     */
    public $pod_id;

    /**
     * @var
     */
    public $fields;

    /**
     * @var
     */
    public $detail_page;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $limit = 15;

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
     * @var
     */
    public $ui;

    /**
     * @var
     */
    public $deprecated;

    public $datatype;

    public $datatype_id;

    public $page_template;

    public $body_classes;

    public $meta = array();

    public $meta_properties = array();

    public $meta_extra = '';

    public $sql;

    /**
     * Constructor - Pods Framework core
     *
     * @param string $pod The pod name
     * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.0.0
     */
    public function __construct ( $pod = null, $id = null ) {
        $this->api = pods_api( $pod );
        $this->api->display_errors =& $this->display_errors;

        $this->data = pods_data( $this->api, $id, false );
        PodsData::$display_errors =& $this->display_errors;

        // Set up page variable
        if ( defined( 'PODS_STRICT_MODE' ) && PODS_STRICT_MODE ) {
            $this->page = 1;
            $this->pagination = false;
            $this->search = false;
        }
        else {
            // Get the page variable
            $this->page = pods_var( $this->page_var, 'get' );
            $this->page = ( empty( $this->page ) ? 1 : max( pods_absint( $this->page ), 1 ) );
        }

        // Set default pagination handling to on/off
        if ( defined( 'PODS_GLOBAL_POD_PAGINATION' ) ) {
            if ( !PODS_GLOBAL_POD_PAGINATION ) {
                $this->page = 1;
                $this->pagination = false;
            }
            else
                $this->pagination = true;
        }

        // Set default search to on/off
        if ( defined( 'PODS_GLOBAL_POD_SEARCH' ) ) {
            if ( PODS_GLOBAL_POD_SEARCH )
                $this->search = true;
            else
                $this->search = false;
        }

        // Set default search mode
        $allowed_search_modes = array( 'int', 'text', 'text_like' );

        if ( defined( 'PODS_GLOBAL_POD_SEARCH_MODE' ) && in_array( PODS_GLOBAL_POD_SEARCH_MODE, $allowed_search_modes ) )
            $this->search_mode = PODS_GLOBAL_POD_SEARCH_MODE;

        // Sync Settings
        $this->data->page =& $this->page;
        $this->data->pagination =& $this->pagination;
        $this->data->search =& $this->search;
        $this->data->search_mode =& $this->search_mode;

        // Sync Pod Data
        $this->api->pod_data =& $this->data->pod_data;
        $this->pod_data =& $this->api->pod_data;
        $this->api->pod_id =& $this->data->pod_id;
        $this->pod_id =& $this->api->pod_id;
        $this->datatype_id =& $this->pod_id;
        $this->api->pod =& $this->data->pod;
        $this->pod =& $this->api->pod;
        $this->datatype =& $this->pod;
        $this->api->fields =& $this->data->fields;
        $this->fields =& $this->api->fields;
        $this->detail_page =& $this->data->detail_page;
        $this->id =& $this->data->id;
        $this->row =& $this->data->row;
        $this->rows =& $this->data->data;

        if ( is_array( $id ) || is_object( $id ) )
            $this->find( $id );
    }

    /**
     * Whether this Pod object is valid or not
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function valid () {
        if ( empty( $this->pod_id ) )
            return false;

        return true;
    }

    /**
     * Return data array from a find
     *
     * @return array
     *
     * @since 2.0.0
     */
    public function data () {
        $this->do_hook( 'data' );

        if ( empty( $this->rows ) )
            return false;

        return (array) $this->rows;
    }

    /**
     * Return field array from a Pod
     *
     * @return array
     *
     * @since 2.0.0
     */
    public function fields () {
        $this->do_hook( 'fields' );

        if ( empty( $this->fields ) )
            return false;

        return (array) $this->fields;
    }

    /**
     * Return row array for an item
     *
     * @return array
     *
     * @since 2.0.0
     */
    public function row () {
        $this->do_hook( 'row' );

        if ( empty( $this->row ) )
            return false;

        return (array) $this->row;
    }

    /**
     * Return a field's value(s), through display hook of field type
     *
     * @param array $params An associative array of parameters (OR the Field name)
     * @param boolean $single (optional) For tableless fields, to return an array or the first
     *
     * @return bool|int|mixed|null|string
     * @since 2.0.0
     */
    public function display ( $params, $single = false ) {
        $defaults = array(
            'name' => $params,
            'orderby' => null,
            'single' => $single,
            'in_form' => false
        );

        if ( is_array( $params ) || is_object( $params ) )
            $params = (object) array_merge( $defaults, (array) $params );
        else
            $params = (object) $defaults;

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $value = $this->field( $params, $single );

        if ( false === $params->in_form && isset( $this->fields[ $params->name ] ) ) {
            $value = PodsForm::display(
                $this->fields[ $params->name ][ 'type' ],
                $value,
                $params->name,
                array_merge( $this->fields[ $params->name ][ 'options' ], $this->fields[ $params->name ] ),
                $this->pod_data,
                $this->id()
            );
        }

        if ( is_array( $value ) )
            $value = pods_serial_comma( $value, $params->name, $this->fields );

        return $value;
    }

    /**
     * Return a field's value(s)
     *
     * @param array $params An associative array of parameters (OR the Field name)
     * @param boolean $single (optional) For tableless fields, to return an array or the first
     *
     * @return bool|int|mixed|null
     * @since 2.0.0
     */
    public function field ( $params, $single = false ) {
        $defaults = array(
            'name' => $params,
            'orderby' => null,
            'single' => $single,
            'in_form' => false
        );

        if ( is_array( $params ) || is_object( $params ) )
            $params = (object) array_merge( $defaults, (array) $params );
        else
            $params = (object) $defaults;

        // Support old $orderby variable
        if ( !is_bool( $params->single ) && empty( $params->orderby ) ) {
            pods_deprecated( 'Pods::field', '2.0.0', 'Use $params[ \'orderby\' ] instead' );

            $params->orderby = $params->single;
            $params->single = false;
        }

        $params->single = (boolean) $params->single;

        if ( is_array( $params->name ) || strlen( $params->name ) < 1 )
            return null;

        if ( false === $this->row() ) {
            if ( false !== $this->data() )
                $this->fetch();
            else
                return null;
        }

        if ( $this->data->field_id == $params->name ) {
            if ( isset( $this->row[ $params->name ] ) )
                return $this->row[ $params->name ];
            else
                return 0;
        }

        $value = null;

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $params->traverse = array();

        if ( 'detail_url' == $params->name ) {
            if ( 0 < strlen( $this->detail_page ) )
                $value = get_bloginfo( 'url' ) . '/' . $this->do_magic_tags( $this->detail_page );
            elseif ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) )
                $value = get_permalink( $this->id() );
        }
        elseif ( isset( $this->row[ $params->name ] ) )
            $value = $this->row[ $params->name ];
        else {
            $object_field_found = false;

            // @todo Handle Author WP object fields like they are pick fields
            foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                if ( $object_field == $params->name || in_array( $params->name, $object_field_opt[ 'alias' ] ) ) {
                    if ( isset( $this->row[ $object_field ] ) ) {
                        $value = $this->row[ $object_field ];
                        $object_field_found = true;
                    }
                    else
                        return null;
                }
            }

            if ( false === $object_field_found ) {
                $params->traverse = array( $params->name );

                if ( false !== strpos( $params->name, '.' ) ) {
                    $params->traverse = explode( '.', $params->name );

                    $params->name = $params->traverse[ 0 ];
                }

                if ( isset( $this->fields[ $params->name ] ) && isset( $this->fields[ $params->name ][ 'type' ] ) ) {
                    $v = $this->do_hook( 'field_' . $this->fields[ $params->name ][ 'type' ], null, $this->fields[ $params->name ], $this->row, $params );

                    if ( null !== $v )
                        return $v;
                }

                $simple = false;

                if ( isset( $this->fields[ $params->name ] ) ) {
                    if ( 'meta' == $this->pod_data[ 'storage' ] ) {
                        if ( !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) )
                            $simple = true;

                        $params->single = true;
                    }

                    if ( in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) ) {
                        if ( 'custom-simple' == $this->fields[ $params->name ][ 'pick_object' ] )
                            $simple = true;
                    }
                }

                if ( !isset( $this->fields[ $params->name ] ) || !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) || $simple ) {
                    pods_no_conflict_on( $this->pod_data[ 'type' ] );

                    if ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) {
                        $id = $this->id();

                        if ( function_exists( 'icl_get_languages' ) ) {
                            $master_post_id = (int) get_post_meta( $id, '_icl_lang_duplicate_of', true );

                            if ( 0 < $master_post_id )
                                $id = $master_post_id;
                        }

                        $value = get_post_meta( $id, $params->name, $params->single );
                    }
                    elseif ( 'user' == $this->pod_data[ 'type' ] )
                        $value = get_user_meta( $this->id(), $params->name, $params->single );
                    elseif ( 'comment' == $this->pod_data[ 'type' ] )
                        $value = get_comment_meta( $this->id(), $params->name, $params->single );

                    pods_no_conflict_off( $this->pod_data[ 'type' ] );
                }
                else {
                    // Dot-traversal
                    $pod = $this->pod;
                    $ids = array( $this->id() );
                    $all_fields = array(
                        $this->pod => $this->fields
                    );

                    $lookup = $params->traverse;

                    if ( !empty( $lookup ) )
                        unset( $lookup[ 0 ] );

                    // Get fields matching traversal names
                    if ( !empty( $lookup ) ) {
                        $fields = $this->api->load_fields( array(
                            'name' => $lookup,
                            'type' => $tableless_field_types
                        ) );

                        if ( !empty( $fields ) ) {
                            foreach ( $fields as $field ) {
                                if ( !empty( $field ) ) {
                                    if ( !isset( $all_fields[ $field[ 'pod' ] ] ) )
                                        $all_fields[ $field[ 'pod' ] ] = array();

                                    $all_fields[ $field[ 'pod' ] ][ $field[ 'name' ] ] = $field;
                                }
                            }
                        }
                    }

                    $last_type = $last_object = $last_pick_val = '';

                    $single_multi = pods_var( $this->fields[ $params->name ][ 'type' ] . '_format_type', $this->fields[ $params->name ][ 'options' ], 'single' );

                    if ( 'multi' == $single_multi )
                        $limit = (int) pods_var( $this->fields[ $params->name ][ 'type' ] . '_limit', $this->fields[ $params->name ][ 'options' ], 0 );
                    else
                        $limit = 1;

                    $last_limit = 0;

                    // Loop through each traversal level
                    foreach ( $params->traverse as $key => $field ) {
                        $last_loop = false;

                        if ( count( $params->traverse ) <= ( $key + 1 ) )
                            $last_loop = true;

                        $field_exists = isset( $all_fields[ $pod ][ $field ] );

                        // Tableless handler
                        if ( $field_exists ) {
                            $type = $all_fields[ $pod ][ $field ][ 'type' ];
                            $pick_object = $all_fields[ $pod ][ $field ][ 'pick_object' ];
                            $pick_val = $all_fields[ $pod ][ $field ][ 'pick_val' ];

                            if ( in_array( $type, $tableless_field_types ) ) {
                                $single_multi = pods_var( "{$type}_format_type", $all_fields[ $pod ][ $field ][ 'options' ], 'single' );

                                if ( 'multi' == $single_multi )
                                    $last_limit = (int) pods_var( "{$type}_limit", $all_fields[ $pod ][ $field ][ 'options' ], 0 );
                                else
                                    $last_limit = 1;
                            }

                            $last_type = $type;
                            $last_object = $pick_object;
                            $last_pick_val = $pick_val;

                            // Get related IDs
                            $ids = $this->api->lookup_related_items(
                                $all_fields[ $pod ][ $field ][ 'id' ],
                                $all_fields[ $pod ][ $field ][ 'pod_id' ],
                                $ids
                            );

                            // No items found
                            if ( empty( $ids ) )
                                return false;

                            // Get $pod if related to a Pod
                            if ( !empty( $pick_object ) && 'pod' == $pick_object && !empty( $pick_val ) )
                                $pod = $pick_val;
                        }
                        // Assume last iteration
                        else {
                            // Invalid field
                            if ( 0 == $key )
                                return false;

                            $last_loop = true;
                        }

                        if ( $last_loop ) {
                            $object_type = $last_object;
                            $object = $last_pick_val;

                            if ( 'file' == $last_type ) {
                                $object_type = 'media';
                                $object = 'attachment';
                            }

                            $table = $this->api->get_table_info( $object_type, $object );

                            $join = $where = '';

                            if ( !empty( $table[ 'join' ] ) ) {
                                $join = (array) $table[ 'join' ];

                                $join = implode( ' ', $join );
                            }

                            if ( !empty( $table[ 'where' ] ) || !empty( $ids ) ) {
                                $where = array();

                                foreach ( $ids as $id ) {
                                    $where[] = '`t`.`' . $table[ 'field_id' ] . '` = ' . (int) $id;
                                }

                                if ( !empty( $where ) )
                                    $where = array( '( ' . implode( ' OR ', $where ) . ' )' );

                                if ( !isset( $table[ 'where' ] ) )
                                    $where = array_merge( $where, (array) $table[ 'where' ] );

                                $where = implode( ' AND ', $where );

                                $where = "WHERE {$where}";
                            }

                            $sql = "
                                SELECT *
                                FROM `" . $table[ 'table' ] . "` AS `t`
                                {$join}
                                {$where}
                            ";

                            $data = pods_query( $sql );

                            if ( empty( $data ) )
                                $value = false;
                            else {
                                foreach ( $data as &$item_value ) {
                                    $item_value = get_object_vars( (object) $item_value );
                                }

                                // Return entire array
                                if ( false === $params->in_form && false !== $field_exists && in_array( $last_type, $tableless_field_types ) )
                                    $value = $data;
                                // Return an array of single column values
                                else {
                                    $value = array();

                                    if ( false !== $params->in_form )
                                        $field = $table[ 'field_id' ];

                                    foreach ( $data as $item ) {
                                        $value[] = $item[ $field ];
                                    }
                                }

                                // Return a single column value
                                if ( false === $params->in_form && 1 == $limit && !empty( $value ) && is_array( $value ) && isset( $value[ 0 ] ) )
                                    $value = $value[ 0 ];
                            }

                            break;
                        }
                    }
                }
            }
        }

        if ( !empty( $params->traverse ) && 1 < count( $params->traverse ) ) {
            $field_names = implode( '.', $params->traverse );

            $this->row[ $field_names ] = $value;
        }
        else {
            if ( isset( $this->fields[ $params->name ] ) && in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) ) {
                if ( 'custom-simple' == $this->fields[ $params->name ][ 'pick_object' ] ) {
                    if ( empty( $value ) )
                        $value = array();
                    else
                        $value = @json_decode( $value, true );

                    $single_multi = pods_var( $this->fields[ $params->name ][ 'type' ] . '_format_type', $this->fields[ $params->name ][ 'options' ], 'single' );

                    if ( 'single' == $single_multi ) {
                        if ( empty( $value ) )
                            $value = '';
                        else
                            $value = current( $value );
                    }
                }
            }

            $this->row[ $params->name ] = $value;
        }

        $value = $this->do_hook( 'field', $value, $this->row, $params );

        return $value;
    }

    /**
     * Return the item ID
     *
     * @return int
     * @since 2.0.0
     */
    public function id () {
        return $this->field( $this->data->field_id );
    }

    /**
     * Return the next item ID, loops at the first id to return the last
     *
     * @param int $id
     *
     * @return int
     * @since 2.0.0
     */
    public function next_id ( $id = null ) {
        if ( null === $id )
            $id = $this->field( 'id' );

        $params = array(
            'select' => $this->data->field_id,
            'where' => $id . ' < ' . $this->data->field_id,
            'orderby' => 't.' . $this->data->field_id . ' ASC',
            'limit' => 1
        );

        $pod = pods( $this->pod, $params );

        if ( $pod->fetch() )
            return $pod->id();

        return 0;
    }

    /**
     * Return the previous item ID, loops at the last id to return the first
     *
     * @param int $id
     *
     * @return int
     * @since 2.0.0
     */
    public function prev_id ( $id = null ) {
        if ( null === $id )
            $id = $this->field( 'id' );

        $params = array(
            'select' => $this->data->field_id,
            'where' => $this->data->field_id . ' < ' . $id,
            'orderby' => 't.' . $this->data->field_id . ' DESC',
            'limit' => 1
        );

        $pod = pods( $this->pod, $params );

        if ( $pod->fetch() )
            return $pod->id();

        return 0;
    }

    /**
     * Return the item name
     *
     * @return string
     * @since 2.0.0
     */
    public function index () {
        return $this->field( $this->data->field_index );
    }

    /**
     * Search and filter items
     *
     * @param array $params An associative array of parameters
     * @param int $limit (optional) Limit the number of items to find
     * @param string $where (optional) SQL WHERE declaration to use
     * @param string $sql (optional)
     *
     * @return \Pods The pod object
     * @since 2.0.0
     */
    public function find ( $params = null, $limit = 15, $where = null, $sql = null ) {
        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $select = '`t`.*';
        $pod_table_prefix = 't';

        if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) && 'table' == $this->pod_data[ 'storage' ] ) {
            $select .= ', `d`.*';
            $pod_table_prefix = 'd';
        }

        if ( empty( $this->data->table ) )
            return $this;

        $defaults = array(
            'table' => $this->data->table,
            'select' => $select,
            'join' => null,
            'where' => $where,
            'groupby' => null,
            'having' => null,
            'orderby' => null,
            'limit' => (int) $limit,
            'offset' => null,
            'page' => (int) $this->page,
            'search' => (boolean) $this->search,
            'search_query' => pods_var( $this->search_var, 'get', '' ),
            'search_mode' => $this->search_mode,
            'search_across' => true,
            'search_across_picks' => false,
            'fields' => $this->fields,
            'sql' => $sql
        );

        if ( is_array( $params ) )
            $params = (object) array_merge( $defaults, $params );
        if ( is_object( $params ) )
            $params = (object) array_merge( $defaults, get_object_vars( $params ) );
        else
            $params = (object) $defaults;

        $params = $this->do_hook( 'find', $params );

        $this->limit = (int) $params->limit;
        $this->page = (int) $params->page;
        $this->search = (boolean) $params->search;
        $params->join = (array) $params->join;

        // Allow where array ( 'field' => 'value' )
        if ( !empty( $params->where ) && is_array( $params->where ) ) {
            $params->where = pods_sanitize( $params->where );

            foreach ( $params->where as $k => &$where ) {
                if ( empty( $where ) ) {
                    unset( $params->where[ $k ] );

                    continue;
                }

                if ( !is_numeric( $k ) ) {
                    $key = '';

                    if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) ) {
                        if ( isset( $this->pod_data[ 'object_fields' ][ $k ] ) )
                            $key = "`t`.`{$k}`";
                        elseif ( 'table' == $this->pod_data[ 'storage' ] && isset( $this->fields[ $k ] ) )
                            $key = "`d`.`{$k}`";
                        else {
                            foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                                if ( $object_field == $k || in_array( $k, $object_field_opt[ 'alias' ] ) )
                                    $key = "`t`.`{$object_field}`";
                            }
                        }
                    }
                    elseif ( 'table' == $this->pod_data[ 'storage' ] && isset( $this->fields[ $k ] ) )
                        $key = "`t`.`{$k}`";

                    if ( empty( $key ) )
                        $key = "`{$k}`";

                    if ( is_array( $where ) )
                        $where = "$key IN ( '" . implode( "', '", $where ) . "' )";
                    else
                        $where = "$key = '" . (string) $where . "'";
                }
            }
        }

        // Allow orderby array ( 'field' => 'asc|desc' )
        if ( !empty( $params->orderby ) && is_array( $params->orderby ) ) {
            foreach ( $params->orderby as $k => &$orderby ) {
                if ( !is_numeric( $k ) ) {
                    $key = '';

                    $order = 'ASC';

                    if ( 'DESC' == strtoupper( $orderby ) )
                        $order = 'DESC';

                    if ( isset( $this->fields[ $k ] ) && in_array( $this->fields[ $k ][ 'type' ], $tableless_field_types ) ) {
                        $table = $this->api->get_table_info( $this->fields[ $k ][ 'pick_object' ], $this->fields[ $k ][ 'pick_val' ] );

                        if ( !empty( $table ) )
                            $key = "`{$k}`.`" . $table[ 'field_index' ] . '`';
                    }

                    if ( empty( $key ) ) {
                        if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) ) {
                            if ( isset( $this->pod_data[ 'object_fields' ][ $k ] ) )
                                $key = "`t`.`{$k}`";
                            elseif ( 'table' == $this->pod_data[ 'storage' ] && isset( $this->fields[ $k ] ) )
                                $key = "`d`.`{$k}`";
                            else {
                                foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                                    if ( $object_field == $k || in_array( $k, $object_field_opt[ 'alias' ] ) )
                                        $key = "`t`.`{$object_field}`";
                                }
                            }
                        }
                        elseif ( 'table' == $this->pod_data[ 'storage' ] && isset( $this->fields[ $k ] ) )
                            $key = "`t`.`{$k}`";

                        if ( empty( $key ) ) {
                            $key = $k;

                            if ( false === strpos( $key, ' ' ) )
                                $key = "`{$key}`";
                        }
                    }

                    $orderby = $key;

                    if ( false === strpos( $orderby, ' ' ) )
                        $orderby .= ' ' . $order;
                }
            }
        }

        // Add prefix to $params->orderby if needed
        if ( !empty( $params->orderby ) ) {
            if ( is_array( $params->orderby ) ) {
                foreach ( $params->orderby as &$prefix_orderby ) {
                    if ( false === strpos( $prefix_orderby, ',' ) && false === strpos( $prefix_orderby, '(' ) && false === strpos( $prefix_orderby, '.' ) ) {
                        if ( false !== stripos( $prefix_orderby, ' ASC' ) ) {
                            $o = trim( str_ireplace( array( '`', ' ASC' ), '', $prefix_orderby ) );
                            $prefix_orderby = "`{$pod_table_prefix}`.`" . $o . '` ASC';
                        }
                        else {
                            $o = trim( str_ireplace( array( '`', ' DESC' ), '', $prefix_orderby ) );
                            $prefix_orderby = "`{$pod_table_prefix}`.`" . $o . '` DESC';
                        }
                    }
                }
            }
            elseif ( false === strpos( $params->orderby, ',' ) && false === strpos( $params->orderby, '(' ) && false === strpos( $params->orderby, '.' ) ) {
                if ( false !== stripos( $params->orderby, ' ASC' ) ) {
                    $o = trim( str_ireplace( array( '`', ' ASC' ), '', $params->orderby ) );
                    $params->orderby = "`{$pod_table_prefix}`.`" . $o . '` ASC';
                }
                else {
                    $o = trim( str_ireplace( array( '`', ' DESC' ), '', $params->orderby ) );
                    $params->orderby = "`{$pod_table_prefix}`.`" . $o . '` DESC';
                }
            }
        }

        $this->data->select( $params );

        $this->sql = $this->data->sql;

        return $this;
    }

    /**
     * Fetch a row
     *
     * @see PodsData::fetch
     *
     * @param int $id ID of the row to fetch
     *
     * @return array The row array
     *
     * @since 2.0.0
     */
    public function fetch ( $id = null ) {
        $this->do_hook( 'fetch', $id );

        $this->data->fetch( $id );

        $this->sql = $this->data->sql;

        return $this->row;
    }

    /**
     * (Re)set the MySQL result pointer
     *
     * @see PodsData::reset
     *
     * @param int $row ID of the row to reset to
     *
     * @return array The row array
     *
     * @since 2.0.0
     */
    public function reset ( $row = null ) {
        $this->do_hook( 'reset', $row );

        $this->data->reset( $row );

        $this->sql = $this->data->sql;

        return $this->row;
    }

    /**
     * Fetch the total row count returned
     *
     * @see PodsData::total
     *
     * @return int Number of rows returned by find()
     * @since 2.0.0
     */
    public function total () {
        $this->do_hook( 'total' );

        $this->total =& $this->data->total();

        return $this->total;
    }

    /**
     * Fetch the total row count total
     *
     * @see PodsData::total_found
     *
     * @return int Number of rows found by find()
     * @since 2.0.0
     */
    public function total_found () {
        $this->do_hook( 'total_found' );

        $this->total_found =& $this->data->total_found();

        return $this->total_found;
    }

    /**
     * Fetch the zebra switch
     *
     * @see PodsData::zebra
     *
     * @return bool Zebra state
     * @since 1.12
     */
    public function zebra () {
        $this->do_hook( 'zebra' );

        return $this->data->zebra();
    }

    /**
     * Add an item
     *
     * @see PodsAPI::save_pod_item
     *
     * @param array|string $data Either an associative array of field information or a field name
     * @param string $value (optional) Value of the data to add, if data is string
     *
     * @return int The item ID
     *
     * @since 2.0.0
     */
    public function add ( $data = null, $value = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        $data = (array) $this->do_hook( 'add', $data );

        if ( empty( $data ) )
            return false;

        $params = array( 'pod' => $this->pod, 'data' => $data );

        return $this->api->save_pod_item( $params );
    }

    /**
     * Save an item
     *
     * @see PodsAPI::save_pod_item
     *
     * @param null $data Either an associative array of field information or a field name
     * @param null $value (optional) Value of the data to add, if data is string
     * @param null $id (optional) Id of the pod item to update
     *
     * @return int The item ID
     *
     * @since 2.0.0
     */
    public function save ( $data = null, $value = null, $id = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        if ( null === $id )
            $id = $this->id();

        $data = (array) $this->do_hook( 'save', $data, $id );

        if ( empty( $data ) )
            return false;

        $params = array( 'pod' => $this->pod, 'id' => $id, 'data' => $data );

        return $this->api->save_pod_item( $params );
    }

    /**
     * Delete an item
     *
     * @see PodsAPI::delete_pod_item
     *
     * @param int $id Id of the pod item to delete
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function delete ( $id = null ) {
        if ( null === $id )
            $id = $this->id();

        $id = (int) $this->do_hook( 'delete', $id );

        if ( empty( $id ) )
            return false;

        $params = array( 'pod' => $this->pod, 'id' => $id );

        return $this->api->delete_pod_item( $params );
    }

    /**
     * Duplicate an item
     *
     * @see PodsAPI::duplicate_pod_item
     *
     * @param int $id ID of the pod item to duplicate
     *
     * @return int|bool ID of the new pod item
     *
     * @since 2.0.0
     */
    public function duplicate ( $id = null ) {
        if ( null === $id )
            $id = $this->id();

        $id = (int) $this->do_hook( 'duplicate', $id );

        if ( empty( $id ) )
            return false;

        $params = array( 'pod' => $this->pod, 'id' => $id );

        return $this->api->duplicate_pod_item( $params );
    }

    /**
     * Export an item's data
     *
     * @see PodsApi::export_pod_item
     *
     * @param array $fields (optional) Fields to export
     * @param int $id (optional) ID of the pod item to export
     *
     * @return array|bool Data array of the exported pod item
     *
     * @since 2.0.0
     */
    public function export ( $fields = null, $id = null ) {
        if ( null === $id )
            $id = $this->id();

        $fields = (array) $this->do_hook( 'export', $fields, $id );

        if ( empty( $id ) )
            return false;

        $params = array( 'pod' => $this->pod, 'id' => $id, 'fields' => $fields );

        return $this->api->export_pod_item( $params );
    }

    /**
     * Display the pagination controls, types supported by default
     * are simple, paginate and advanced. The base and format parameters
     * are used only for the paginate view.
     *
     * @var array $params Associative array of parameteres
     *
     * @return bool|mixed
     * @since 2.0.0
     */
    public function pagination ( $params = null ) {
        $url = pods_var_update( null, null, $this->page_var );

        $append = '?';

        if ( false !== strpos( $url, '?' ) )
            $append = '&';

        $defaults = array(
            'type' => 'advanced',
            'label' => __( 'Go to page:', 'pods' ),
            'show_label' => true,
            'next_text' => __( 'Next &rsaquo;', 'pods' ),
            'prev_text' => __( '&lsaquo; Previous', 'pods' ),
            'first_text' => __( '&laquo; First', 'pods' ),
            'last_text' => __( 'Last &raquo;', 'pods' ),
            'prev_next' => true,
            'first_last' => true,
            'limit' => (int) $this->limit,
            'page' => max( 1, (int) $this->page ),
            'mid_size' => 2,
            'end_size' => 1,
            'total_found' => $this->total_found(),
            'page_var' => $this->page_var,
            'base' => "{$url}{$append}%_%",
            'format' => "{$this->page_var}=%#%"
        );

        if ( empty( $params ) )
            $params = array();
        elseif ( !is_array( $params ) )
            $params = array( 'label' => $params );

        $params = (object) array_merge( $defaults, $params );

        $params->total = ceil( $params->total_found / $params->limit );

        if ( $params->limit < 1 || $params->total_found < 1 || 1 == $params->total )
            return $this->do_hook( 'pagination', '', $params );

        $pagination = $params->type;

        if ( !in_array( $params->type, array( 'simple', 'advanced', 'paginate' ) ) )
            $pagination = 'advanced';

        ob_start();

        pods_view( PODS_DIR . 'ui/front/pagination/' . $pagination . '.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return $this->do_hook( 'pagination', $output, $params );
    }

    /**
     * Display the list filters
     *
     * @since 2.0.0
     */
    public function filters ( $params = null ) {
        // handle $params deprecated

        ob_start();

        pods_view( PODS_DIR . 'ui/front/filters.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return $this->do_hook( 'filters', $output, $params );
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * @see Pods_Helpers::helper
     *
     * @param string $helper Helper name
     * @param string $value Value to run the helper on
     * @param string $name Field name
     * @internal param array $params An associative array of parameters
     *
     * @return mixed Anything returned by the helper
     * @since 2.0.0
     */
    public function helper ( $helper, $value = null, $name = null ) {
        $params = array(
            'helper' => $helper,
            'value' => $value,
            'name' => $name,
            'deprecated' => false
        );

        if ( is_array( $helper ) )
            $params = array_merge( $params, $helper );

        if ( class_exists( 'Pods_Helpers' ) )
            return Pods_Helpers::helper( $params, $this );
    }

    /**
     * Display the page template
     *
     * @see Pods_Templates::template
     *
     * @param string $template The template name
     * @param string $code Custom template code to use instead
     * @param bool $deprecated Whether to use deprecated functionality based on old function usage
     *
     * @return mixed Template output
     *
     * @since 2.0.0
     */
    public function template ( $template, $code = null, $deprecated = false ) {
        if ( class_exists( 'Pods_Templates' ) )
            return Pods_Templates::template( $template, $code, $this, $deprecated );
    }

    /**
     * Build form for handling add / edit
     *
     * @param array $params Fields to show on the form
     * @param string $label
     * @param string $thank_you Thank you message
     *
     * @return bool|mixed
     * @since 2.0.0
     */
    public function form ( $params = null, $label = null, $thank_you = null ) {
        $defaults = array(
            'fields' => $params,
            'label' => $label,
            'thank_you' => $thank_you
        );

        if ( isset( $params[ 'fields' ] ) )
            $params = array_merge( $defaults, $params );
        else
            $params = $defaults;

        $pod =& $this;

        $params = apply_filters( 'pods_form_params', $params, $pod );

        $fields = $params[ 'fields' ];

        if ( empty( $fields ) )
            $fields = $this->fields;
        else {
            foreach ( $fields as $k => $field ) {
                $name = $k;

                if ( !is_array( $field ) ) {
                    $name = $field;
                    $field = array();
                }
                elseif ( isset( $field[ 'name' ] ) )
                    $name = $field[ 'name' ];

                if ( !isset( $this->fields[ $name ] ) || pods_var_raw( 'hidden', $field, false, null, true ) )
                    unset( $fields[ $k ] );
                else
                    $fields[ $k ] = array_merge( $this->fields[ $name ], $field );
            }
        }

        $label = $params[ 'label' ];

        if ( empty( $label ) )
            $label = __( 'Save Changes', 'pods' );

        $thank_you = $params[ 'thank_you' ];

        ob_start();

        pods_view( PODS_DIR . 'ui/front/form.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return $this->do_hook( 'form', $output, $fields, $label, $thank_you, $this, $this->id() );
    }

    /**
     * Replace magic tags with their values
     *
     * @param string $code The content to evaluate
     * @param object $obj The Pods object
     *
     * @since 2.0.0
     */
    public function do_magic_tags( $code ) {
        return preg_replace_callback( '/({@(.*?)})/m', array( $this, 'process_magic_tags' ), $code );
    }

    /**
     * Replace magic tags with their values
     *
     * @param string $tag The magic tag to process
     * @param object $obj The Pods object
     *
     * @since 2.0.2
     */
    public function process_magic_tags ( $tag ) {
        if ( is_array( $tag ) ) {
            if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 )
                return;

            $tag = $tag[ 2 ];
        }

        $tag = trim( $tag, ' {@}' );
        $tag = explode( ',', $tag );

        if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 )
            return;

        foreach ( $tag as $k => $v ) {
            $tag[ $k ] = trim( $v );
        }

        $field_name = $tag[ 0 ];

        if ( 'type' == $field_name )
            $value = $this->pod;
        else
            $value = $this->field( $field_name );

        $helper_name = $before = $after = '';

        if ( isset( $tag[ 1 ] ) && !empty( $tag[ 1 ] ) ) {
            $helper_name = $tag[ 1 ];

            $params = array(
                'helper' => $helper_name,
                'value' => $value,
                'name' => $field_name
            );

            if ( class_exists( 'Pods_Templates' ) )
                $params[ 'deprecated' ] = Pods_Templates::$deprecated;

            if ( class_exists( 'Pods_Helpers' ) )
                $value = Pods_Helpers::helper( $params, $this );
        }

        if ( isset( $tag[ 2 ] ) && !empty( $tag[ 2 ] ) )
            $before = $tag[ 2 ];

        if ( isset( $tag[ 3 ] ) && !empty( $tag[ 3 ] ) )
            $after = $tag[ 3 ];

        $value = apply_filters( 'pods_do_magic_tags', $value, $field_name, $helper_name, $before, $after );

        if ( is_array( $value ) )
            $value = pods_serial_comma( $value, $field_name, $this->fields );

        if ( null !== $value && false !== $value )
            return $before . $value . $after;

        return;
    }

    /**
     * Handle filters / actions for the class
     *
     * @see pods_do_hook
     *
     * @since 2.0.0
     */
    private function do_hook () {
        $args = func_get_args();

        if ( empty( $args ) )
            return false;

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
     * @since 2.0.0
     */
    public function __get ( $name ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        $var = null;

        if ( isset( $this->deprecated->{$name} ) ) {
            pods_deprecated( "Pods->{$name}", '2.0.0' );

            $var = $this->deprecated->{$name};
        }
        else
            pods_deprecated( "Pods->{$name}", '2.0.0' );

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
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) )
            return call_user_func_array( array( $this->deprecated, $name ), $args );
        else
            pods_deprecated( "Pods::{$name}", '2.0.0' );
    }
}
