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
    public $ui = array();

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
     * @link http://podsframework.org/docs/pods/
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
        $this->data->limit =& $this->limit;
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
     * Whether a Pod item exists or not when using fetch() or construct with an ID or slug
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function exists () {
        if ( empty( $this->row ) )
            return false;

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
     * @link http://podsframework.org/docs/data/
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
     * Return the output for a field. If you want the raw value for use in PHP for custom manipulation,
     * you will want to use field() instead. This function will automatically convert arrays into a
     * list of text such as "Rick, John, and Gary"
     *
     * @param string|array $name The field name, or an associative array of parameters
     * @param boolean $single (optional) For tableless fields, to return an array or the first
     *
     * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned for tableless fields
     * @since 2.0.0
     * @link http://podsframework.org/docs/display/
     */
    public function display ( $name, $single = null ) {
        $defaults = array(
            'name' => $name,
            'orderby' => null,
            'single' => $single,
            'in_form' => false
        );

        if ( is_array( $name ) || is_object( $name ) )
            $params = (object) array_merge( $defaults, (array) $name );
        else
            $params = (object) $defaults;

        $value = $this->field( $params );

        if ( false === $params->in_form && isset( $this->fields[ $params->name ] ) ) {
            if ( 'pick' == $this->fields[ $params->name ][ 'type' ] && 'custom-simple' == $this->fields[ $params->name ][ 'pick_object' ] )
                $value = PodsForm::field_method( 'pick', 'simple_value', $value, $this->fields[ $params->name ] );

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
     * Return the raw output for a field If you want the raw value for use in PHP for custom manipulation,
     * you will want to use field() instead. This function will automatically convert arrays into a
     * list of text such as "Rick, John, and Gary"
     *
     * @param string|array $name The field name, or an associative array of parameters
     * @param boolean $single (optional) For tableless fields, to return an array or the first
     *
     * @return string|null|false The output from the field, null if the field doesn't exist, false if no value returned for tableless fields
     * @since 2.0.0
     * @link http://podsframework.org/docs/display/
     */
    public function raw ( $name, $single = null ) {
        $defaults = array(
            'name' => $name,
            'orderby' => null,
            'single' => $single,
            'in_form' => false,
            'raw' => true
        );

        if ( is_array( $name ) || is_object( $name ) )
            $params = (object) array_merge( $defaults, (array) $name );
        else
            $params = (object) $defaults;

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
     * @param string|array $name The field name, or an associative array of parameters
     * @param boolean $single (optional) For tableless fields, to return the whole array or the just the first item
     * @param boolean $raw (optional) Whether to return the raw value, or to run through the field type's display method
     *
     * @return mixed|null Value returned depends on the field type, null if the field doesn't exist, false if no value returned for tableless fields
     * @since 2.0.0
     * @link http://podsframework.org/docs/field/
     */
    public function field ( $name, $single = null, $raw = false ) {
        $defaults = array(
            'name' => $name,
            'orderby' => null,
            'single' => $single,
            'in_form' => false,
            'raw' => $raw,
            'deprecated' => false
        );

        if ( is_array( $name ) || is_object( $name ) )
            $params = (object) array_merge( $defaults, (array) $name );
        else
            $params = (object) $defaults;

        // Support old $orderby variable
        if ( null !== $params->single && !is_bool( $params->single ) && empty( $params->orderby ) ) {
            pods_deprecated( 'Pods::field', '2.0.0', 'Use $params[ \'orderby\' ] instead' );

            $params->orderby = $params->single;
            $params->single = false;
        }

        if ( null !== $params->single )
            $params->single = (boolean) $params->single;

        $single = $params->single;

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
        elseif ( isset( $this->row[ $params->name ] ) ) {
            if ( !isset( $this->fields[ $params->name ] ) || in_array( $this->fields[ $params->name ][ 'type' ], array( 'boolean', 'number', 'currency' ) ) || in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) )
                $params->raw = true;

            $value = $this->row[ $params->name ];

            if ( !is_array( $value ) && isset( $this->fields[ $params->name ] ) && 'pick' == $this->fields[ $params->name ][ 'type' ] && 'custom-simple' == $this->fields[ $params->name ][ 'pick_object' ] )
                $value = PodsForm::field_method( 'pick', 'simple_value', $value, $this->fields[ $params->name ], true );
        }
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
                $simple_data = array();

                if ( isset( $this->fields[ $params->name ] ) ) {
                    if ( 'meta' == $this->pod_data[ 'storage' ] ) {
                        if ( !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) )
                            $simple = true;
                    }

                    if ( in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) ) {
                        $params->raw = true;

                        if ( 'custom-simple' == $this->fields[ $params->name ][ 'pick_object' ] ) {
                            $simple = true;
                            $params->single = true;
                        }
                    }
                    elseif ( in_array( $this->fields[ $params->name ][ 'type' ], array( 'boolean', 'number', 'currency' ) ) )
                        $params->raw = true;
                }

                if ( !isset( $this->fields[ $params->name ] ) || !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) || $simple ) {
                    if ( null === $params->single ) {
                        if ( isset( $this->fields[ $params->name ] ) && !in_array( $this->fields[ $params->name ][ 'type' ], $tableless_field_types ) )
                            $params->single = true;
                        else
                            $params->single = false;
                    }

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

                    // Handle Simple Relationships
                    if ( $simple ) {
                        if ( null === $single )
                            $params->single = false;

                        $value = PodsForm::field_method( 'pick', 'simple_value', $value, $this->fields[ $params->name ], true );
                    }

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

                        $simple = false;
                        $simple_options = array();

                        if ( $field_exists && 'pick' == $all_fields[ $pod ][ $field ][ 'type' ] && 'custom-simple' == $all_fields[ $pod ][ $field ][ 'pick_object' ] ) {
                            $simple = true;
                            $simple_options = $all_fields[ $pod ][ $field ];
                        }

                        // Tableless handler
                        if ( $field_exists && ( 'pick' != $all_fields[ $pod ][ $field ][ 'type' ] || !$simple ) ) {
                            $type = $all_fields[ $pod ][ $field ][ 'type' ];
                            $pick_object = $all_fields[ $pod ][ $field ][ 'pick_object' ];
                            $pick_val = $all_fields[ $pod ][ $field ][ 'pick_val' ];

                            $last_limit = 0;

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
                            $last_options = $all_fields[ $pod ][ $field ];

                            // Get related IDs
                            $ids = $this->api->lookup_related_items(
                                $all_fields[ $pod ][ $field ][ 'id' ],
                                $all_fields[ $pod ][ $field ][ 'pod_id' ],
                                $ids,
                                $all_fields[ $pod ][ $field ]
                            );

                            // No items found
                            if ( empty( $ids ) )
                                return false;
                            elseif ( 0 < $last_limit )
                                $ids = array_slice( $ids, 0, $last_limit );

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

                            $data = array();

                            $table = $this->api->get_table_info( $object_type, $object );

                            $join = $where = '';

                            if ( !empty( $table[ 'join' ] ) ) {
                                $join = (array) $table[ 'join' ];

                                $join = implode( ' ', $join );
                            }

                            if ( !empty( $table[ 'where' ] ) || !empty( $ids ) ) {
                                $where = array();

                                foreach ( $ids as $id ) {
                                    $where[ $id ] = '`t`.`' . $table[ 'field_id' ] . '` = ' . (int) $id;
                                }

                                if ( !empty( $where ) )
                                    $where = array( '( ' . implode( ' OR ', $where ) . ' )' );

                                if ( !empty( $table[ 'where' ] ) )
                                    $where = array_merge( $where, (array) $table[ 'where' ] );

                                $where = trim( implode( ' AND ', $where ) );

                                if ( !empty( $where ) )
                                    $where = "WHERE {$where}";
                            }

                            if ( !empty( $table[ 'table' ] ) ) {
                                $sql = "
                                    SELECT *, `t`.`" . $table[ 'field_id' ] . "` AS `pod_item_id`
                                    FROM `" . $table[ 'table' ] . "` AS `t`
                                    {$join}
                                    {$where}
                                ";

                                $item_data = pods_query( $sql );
                                $items = array();

                                foreach ( $item_data as $item ) {
                                    if ( empty( $item->pod_item_id ) )
                                        continue;

                                    // Get Item ID
                                    $item_id = $item->pod_item_id;

                                    // Cleanup
                                    unset( $item->pod_item_id );

                                    // Pass item data into $data
                                    $items[ $item_id ] = $item;
                                }

                                // Cleanup
                                unset( $item_data );

                                // Return all of the data in the order expected
                                foreach ( $ids as $id ) {
                                    if ( isset( $items[ $id ] ) )
                                        $data[] = $items[ $id ];
                                }
                            }

                            if ( in_array( $last_type, $tableless_field_types ) || in_array( $last_type, array( 'boolean', 'number', 'currency' ) ) )
                                $params->raw = true;

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

                                // Handle Simple Relationships
                                if ( $simple ) {
                                    if ( null === $single )
                                        $params->single = false;

                                    $value = PodsForm::field_method( 'pick', 'simple_value', $value, $simple_options, true );
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
        else
            $this->row[ $params->name ] = $value;

        if ( true === $params->single && is_array( $value ) && isset( $value[ 0 ] ) )
            $value = $value[ 0 ];

        // @todo Expand this into traversed fields too
        if ( false === $params->raw && false === $params->in_form && isset( $this->fields[ $params->name ] ) ) {
            $value = PodsForm::display(
                $this->fields[ $params->name ][ 'type' ],
                $value,
                $params->name,
                array_merge( $this->fields[ $params->name ][ 'options' ], $this->fields[ $params->name ] ),
                $this->pod_data,
                $this->id()
            );
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

        $id = (int) $id;

        $params = array(
            'select' => "`t`.{$this->data->field_id}`",
            'where' => "{$id} < `t`.{$this->data->field_id}`",
            'orderby' => "`t`.{$this->data->field_id}` ASC",
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

        $id = (int) $id;

        $params = array(
            'select' => "`t`.{$this->data->field_id}`",
            'where' => "`t`.{$this->data->field_id}` < {$id}",
            'orderby' => "`t`.{$this->data->field_id}` DESC",
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
     * Find items of a pod, much like WP_Query, but with advanced table handling.
     *
     * @param array $params An associative array of parameters
     * @param int $limit (optional) (deprecated) Limit the number of items to find, use -1 to return all items with no limit
     * @param string $where (optional) (deprecated) SQL WHERE declaration to use
     * @param string $sql (optional) (deprecated) For advanced use, a custom SQL query to run
     *
     * @return \Pods The pod object
     * @since 2.0.0
     * @link http://podsframework.org/docs/find/
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
            'page_var' => $this->page_var,
            'pagination' => (boolean) $this->pagination,

            'search' => (boolean) $this->search,
            'search_var' => $this->search_var,
            'search_query' => null,
            'search_mode' => $this->search_mode,
            'search_across' => false,
            'search_across_picks' => false,
            'search_across_files' => false,

            'fields' => $this->fields,
            'sql' => $sql,

            'expires' => null,
            'cache_mode' => 'cache'
        );

        if ( is_array( $params ) )
            $params = (object) array_merge( $defaults, $params );
        if ( is_object( $params ) )
            $params = (object) array_merge( $defaults, get_object_vars( $params ) );
        else {
            $defaults[ 'orderby' ] = $params;
            $params = (object) $defaults;
        }

        $params = $this->do_hook( 'find', $params );

        $params->limit = (int) $params->limit;

        if ( 0 == $params->limit )
            $params->limit = -1;

        $this->limit = $params->limit;
        $this->page = (int) $params->page;
        $this->page_var = $params->page_var;
        $this->pagination = (boolean) $params->pagination;
        $this->search = (boolean) $params->search;
        $this->search_var = $params->search_var;
        $params->join = (array) $params->join;

        if ( empty( $params->search_query ) )
            $params->search_query = pods_var( $this->search_var, 'get', '' );

        // Allow where array ( 'field' => 'value' )
        if ( !empty( $params->where ) && is_array( $params->where ) ) {
            $params->where = pods_sanitize( $params->where );

            foreach ( $params->where as $k => $where ) {
                if ( empty( $where ) ) {
                    unset( $params->where[ $k ] );

                    continue;
                }

                // @todo Implement meta_query like arguments for $where
                if ( !is_numeric( $k ) ) {
                    $where_args = array(
                        'key' => $k,
                        'value' => '',
                        'compare' => '=',
                        'type' => 'CHAR'
                    );

                    if ( !is_array( $where ) ) {
                        $where_args[ 'value' ] = $where;

                        $where = $where_args;
                    }
                    else
                        $where = array_merge( $where_args, $where );

                    $where[ 'key' ] = trim( $where[ 'key' ] );
                    $where[ 'compare' ] = trim( $where[ 'compare' ] );
                    $where[ 'type' ] = trim( $where[ 'type' ] );

                    if ( strlen( $where[ 'key' ] ) < 1 ) {
                        unset( $params->where[ $k ] );

                        continue;
                    }

                    $where[ 'compare' ] = strtotime( $where[ 'compare' ] );

                    if ( !in_array( $where[ 'compare' ], array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) )
                        $where[ 'compare' ] = '=';

                    $where[ 'type' ] = strtotime( $where[ 'type' ] );

                    if ( !in_array( $where[ 'type' ], array( 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ) ) )
                        $where[ 'type' ] = 'CHAR';

                    if ( is_array( $where[ 'value' ] ) && !in_array( $where[ 'compare' ], array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
                        if ( in_array( $where[ 'compare' ], array( '!=', 'NOT LIKE' ) ) )
                            $where[ 'compare' ] = 'NOT IN';
                        else
                            $where[ 'compare' ] = 'IN';
                    }

                    $key = '';

                    if ( false === strpos( $k, '`' ) && false === strpos( $k, ' ' ) ) {
                        if ( isset( $this->fields[ $k ] ) && in_array( $this->fields[ $k ][ 'type' ], $tableless_field_types ) ) {
                            if ( 'custom-simple' == $this->fields[ $k ][ 'pick_object' ] ) {
                                if ( 'table' == $this->pod_data[ 'storage' ] )
                                    $key = "`t`.`{$k}`";
                                else
                                    $key = "`{$k}`.`meta_value`";
                            }
                            else {
                                $table = $this->api->get_table_info( $this->fields[ $k ][ 'pick_object' ], $this->fields[ $k ][ 'pick_val' ] );

                                if ( !empty( $table ) )
                                    $key = "`{$k}`.`" . $table[ 'field_index' ] . '`';
                            }
                        }

                        if ( empty( $key ) ) {
                            if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) ) {
                                if ( isset( $this->pod_data[ 'object_fields' ][ $k ] ) )
                                    $key = "`t`.`{$k}`";
                                elseif ( isset( $this->fields[ $k ] ) ) {
                                    if ( 'table' == $this->pod_data[ 'storage' ] )
                                        $key = "`d`.`{$k}`";
                                    else
                                        $key = "`{$k}`.`meta_value`";
                                }
                                else {
                                    foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                                        if ( $object_field == $k || in_array( $k, $object_field_opt[ 'alias' ] ) )
                                            $key = "`t`.`{$object_field}`";
                                    }
                                }
                            }
                            elseif ( isset( $this->fields[ $k ] ) ) {
                                if ( 'table' == $this->pod_data[ 'storage' ] )
                                    $key = "`t`.`{$k}`";
                                else
                                    $key = "`{$k}`.`meta_value`";
                            }

                            if ( empty( $key ) )
                                $key = "`{$k}`";
                        }
                    }

                    if ( !empty( $key ) )
                        $where[ 'key' ] = $key;

                    $where_args = $where;

                    if ( is_array( $where[ 'value' ] ) )
                        $where = $where[ 'key' ] . ' ' . $where[ 'compare' ] . ' ( "' . implode( '", "', $where[ 'value' ] ) . '" )';
                    else
                        $where = $where[ 'key' ] . ' "' . (string) $where[ 'value' ] . '"';

                    $params->where[ $k ] = apply_filters( 'pods_find_where_query', $where, $where_args );
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
                        if ( 'custom-simple' == $this->fields[ $k ][ 'pick_object' ] ) {
                            if ( 'table' == $this->pod_data[ 'storage' ] )
                                $key = "`t`.`{$k}`";
                            else
                                $key = "`{$k}`.`meta_value`";
                        }
                        else {
                            $table = $this->api->get_table_info( $this->fields[ $k ][ 'pick_object' ], $this->fields[ $k ][ 'pick_val' ] );

                            if ( !empty( $table ) )
                                $key = "`{$k}`.`" . $table[ 'field_index' ] . '`';
                        }
                    }

                    if ( empty( $key ) ) {
                        if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) ) {
                            if ( isset( $this->pod_data[ 'object_fields' ][ $k ] ) )
                                $key = "`t`.`{$k}`";
                            elseif ( isset( $this->fields[ $k ] ) ) {
                                if ( 'table' == $this->pod_data[ 'storage' ] )
                                    $key = "`d`.`{$k}`";
                                else
                                    $key = "`{$k}`.`meta_value`";
                            }
                            else {
                                foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                                    if ( $object_field == $k || in_array( $k, $object_field_opt[ 'alias' ] ) )
                                        $key = "`t`.`{$object_field}`";
                                }
                            }
                        }
                        elseif ( isset( $this->fields[ $k ] ) ) {
                            if ( 'table' == $this->pod_data[ 'storage' ] )
                                $key = "`t`.`{$k}`";
                            else
                                $key = "`{$k}`.`meta_value`";
                        }

                        if ( empty( $key ) ) {
                            $key = $k;

                            if ( false === strpos( $key, ' ' ) && false === strpos( $key, '`' ) )
                                $key = '`' . str_replace( '.', '`.`', $key ) . '`';
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
            if ( !is_array( $params->orderby ) )
                $params->orderby = array( $params->orderby );

            foreach ( $params->orderby as &$prefix_orderby ) {
                if ( false === strpos( $prefix_orderby, ',' ) && false === strpos( $prefix_orderby, '(' ) && false === stripos( $prefix_orderby, ' AS ' ) && false === strpos( $prefix_orderby, '`' ) && false === strpos( $prefix_orderby, '.' ) ) {
                    if ( false !== stripos( $prefix_orderby, ' ASC' ) ) {
                        $k = trim( str_ireplace( array( '`', ' ASC' ), '', $prefix_orderby ) );
                        $dir = 'ASC';
                    }
                    else {
                        $k = trim( str_ireplace( array( '`', ' DESC' ), '', $prefix_orderby ) );
                        $dir = 'DESC';
                    }

                    $key = $k;

                    if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) ) {
                        if ( isset( $this->pod_data[ 'object_fields' ][ $k ] ) )
                            $key = "`t`.`{$k}`";
                        elseif ( isset( $this->fields[ $k ] ) ) {
                            if ( 'table' == $this->pod_data[ 'storage' ] )
                                $key = "`d`.`{$k}`";
                            else
                                $key = "`{$k}`.`meta_value`";
                        }
                        else {
                            foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                                if ( $object_field == $k || in_array( $k, $object_field_opt[ 'alias' ] ) )
                                    $key = "`t`.`{$object_field}`";
                            }
                        }
                    }
                    elseif ( isset( $this->fields[ $k ] ) ) {
                        if ( 'table' == $this->pod_data[ 'storage' ] )
                            $key = "`t`.`{$k}`";
                        else
                            $key = "`{$k}`.`meta_value`";
                    }

                    $prefix_orderby = "{$key} {$dir}";
                }
            }
        }

        $this->data->select( $params );

        $this->sql = $this->data->sql;

        return $this;
    }

    /**
     * Fetch an item from a Pod. If $id is null, it will return the next item in the list after running find().
     * You can rewind the list back to the start by using reset().
     *
     * Providing an $id will fetch a specific item from a Pod, much like a call to pods(), and can handle either an id or slug.
     *
     * @see PodsData::fetch
     *
     * @param int $id ID or slug of the item to fetch
     *
     * @return array An array of fields from the row
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/fetch/
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
     * @return \Pods The pod object
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/reset/
     */
    public function reset ( $row = null ) {
        $this->do_hook( 'reset', $row );

        $this->data->reset( $row );

        $this->sql = $this->data->sql;

        return $this;
    }

    /**
     * Fetch the total row count returned by the last call to find(), based on the 'limit' parameter set.
     *
     * This is different than the total number of rows found in the database, which you can get with total_found().
     *
     * @see PodsData::total
     *
     * @return int Number of rows returned by find(), based on the 'limit' parameter set
     * @since 2.0.0
     * @link http://podsframework.org/docs/total/
     */
    public function total () {
        $this->do_hook( 'total' );

        $this->total =& $this->data->total();

        return $this->total;
    }

    /**
     * Fetch the total amount of rows found by the last call to find(), regardless of the 'limit' parameter set.
     *
     * This is different than the total number of rows limited by the current call, which you can get with total().
     *
     * @see PodsData::total_found
     *
     * @return int Number of rows returned by find(), regardless of the 'limit' parameter
     * @since 2.0.0
     * @link http://podsframework.org/docs/total-found/
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
     * Add an item to a Pod by giving an array of field data or set a specific field to
     * a specific value if you're just wanting to add a new item but only set one field.
     *
     * You may be looking for save() in most cases where you're setting a specific field.
     *
     * @see PodsAPI::save_pod_item
     *
     * @param array|string $data Either an associative array of field information or a field name
     * @param mixed $value (optional) Value of the field, if $data is a field name
     *
     * @return int The item ID
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/add/
     */
    public function add ( $data = null, $value = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        $data = (array) $this->do_hook( 'add', $data );

        if ( empty( $data ) )
            return false;

        $params = array( 'pod' => $this->pod, 'data' => $data, 'allow_custom_fields' => true );

        return $this->api->save_pod_item( $params );
    }

    /**
     * Save an item by giving an array of field data or set a specific field to a specific value.
     *
     * Though this function has the capacity to add new items, best practice should direct you
     * to use add() for that instead.
     *
     * @see PodsAPI::save_pod_item
     *
     * @param array|string $data Either an associative array of field information or a field name
     * @param mixed $value (optional) Value of the field, if $data is a field name
     * @param int $id (optional) ID of the pod item to update
     *
     * @return int The item ID
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/save/
     */
    public function save ( $data = null, $value = null, $id = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        if ( null === $id )
            $id = $this->id();

        $data = (array) $this->do_hook( 'save', $data, $id );

        if ( empty( $data ) )
            return false;

        $params = array( 'pod' => $this->pod, 'id' => $id, 'data' => $data, 'allow_custom_fields' => true );

        return $this->api->save_pod_item( $params );
    }

    /**
     * Delete an item
     *
     * @see PodsAPI::delete_pod_item
     *
     * @param int $id ID of the Pod item to delete
     *
     * @return bool Whether the item was successfully deleted
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/delete/
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
     * @link http://podsframework.org/docs/duplicate/
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
     * @link http://podsframework.org/docs/export/
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
     * @var array $params Associative array of parameters
     *
     * @return string Pagination HTML
     * @since 2.0.0
     * @link http://podsframework.org/docs/pagination/
     */
    public function pagination ( $params = null ) {
        if ( empty( $params ) )
            $params = array();
        elseif ( !is_array( $params ) )
            $params = array( 'label' => $params );

        $this->page_var = pods_var_raw( 'page_var', $params, $this->page_var );

        $url = pods_var_update( null, null, $this->page_var );

        $append = '?';

        if ( false !== strpos( $url, '?' ) )
            $append = '&';

        $defaults = array(
            'type' => 'advanced',
            'label' => __( 'Go to page:', 'pods' ),
            'show_label' => true,
            'first_text' => __( '&laquo; First', 'pods' ),
            'prev_text' => __( '&lsaquo; Previous', 'pods' ),
            'next_text' => __( 'Next &rsaquo;', 'pods' ),
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

        $params = (object) array_merge( $defaults, $params );

        $params->total = ceil( $params->total_found / $params->limit );

        if ( $params->limit < 1 || $params->total_found < 1 || 1 == $params->total )
            return $this->do_hook( 'pagination', $this->do_hook( 'pagination_' . $params->type, '', $params ), $params );

        $pagination = $params->type;

        if ( !in_array( $params->type, array( 'simple', 'advanced', 'paginate' ) ) )
            $pagination = 'advanced';

        ob_start();

        pods_view( PODS_DIR . 'ui/front/pagination/' . $pagination . '.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return $this->do_hook( 'pagination', $this->do_hook( 'pagination_' . $params->type, $output, $params ), $params );
    }

    /**
     * Output a filter form for searching a Pod
     *
     * @var array|string $params Comma-separated list of fields or array of parameters
     *
     * @since 2.0.0
     * @link http://podsframework.org/docs/filters/
     */
    public function filters ( $params = null ) {
        $defaults = array(
            'fields' => $params,
            'label' => '',
            'action' => '',
            'search' => ''
        );

        if ( is_array( $params ) )
            $params = array_merge( $defaults, $params );
        else
            $params = $defaults;

        $pod =& $this;

        $params = apply_filters( 'pods_filters_params', $params, $pod );

        $fields = $params[ 'fields' ];

        if ( null !== $fields && !is_array( $fields ) && 0 < strlen( $fields ) )
            $fields = explode( ',', $fields );

        $object_fields = (array) pods_var_raw( 'object_fields', $this->pod_data, array(), null, true );

        // Force array
        if ( empty( $fields ) )
            $fields = array();
        else {
            $filter_fields = $fields; // Temporary

            $fields = array();

            foreach ( $filter_fields as $k => $field ) {
                $name = $k;

                $defaults = array(
                    'name' => $name
                );

                if ( !is_array( $field ) ) {
                    $name = $field;

                    $field = array(
                        'name' => $name
                    );
                }

                $field = array_merge( $defaults, $field );

                $field[ 'name' ] = trim( $field[ 'name' ] );

                if ( pods_var_raw( 'hidden', $field, false, null, true ) )
                    continue;
                elseif ( isset( $object_fields[ $field[ 'name' ] ] ) )
                    $fields[ $field[ 'name' ] ] = array_merge( $object_fields[ $field[ 'name' ] ], $field );
                elseif ( isset( $this->fields[ $field[ 'name' ] ] ) )
                    $fields[ $field[ 'name' ] ] = array_merge( $this->fields[ $field[ 'name' ] ], $field );
            }

            unset( $filter_fields ); // Cleanup
        }

        $label = $params[ 'label' ];

        if ( strlen( $label ) < 1 )
            $label = __( 'Search', 'pods' );

        $action = $params[ 'action' ];

        $search = trim( $params[ 'search' ] );

        if ( strlen( $search ) < 1 )
            $search = pods_var_raw( $pod->search_var, 'get', '' );

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
     * @link http://podsframework.org/docs/template/
     */
    public function template ( $template, $code = null, $deprecated = false ) {
        if ( !empty( $code ) ) {
            $code = apply_filters( 'pods_templates_pre_template', $code, $template, $this );
            $code = apply_filters( "pods_templates_pre_template_{$template}", $code, $template, $this );

            ob_start();

            if ( !empty( $code ) ) {
                // Only detail templates need $this->id
                if ( empty( $this->id ) ) {
                    while ( $this->fetch() ) {
                        echo $this->do_magic_tags( $code );
                    }
                }
                else
                    echo $this->do_magic_tags( $code, $this );
            }

            $out = ob_get_clean();

            $out = apply_filters( 'pods_templates_post_template', $out, $code, $template, $this );
            $out = apply_filters( "pods_templates_post_template_{$template}", $out, $code, $template, $this );

            return $out;
        }
        elseif ( class_exists( 'Pods_Templates' ) )
            return Pods_Templates::template( $template, $code, $this, $deprecated );
    }

    /**
     * Embed a form to add / edit a pod item from within your theme. Provide an array of $fields to include
     * and override options where needed. For WP object based Pods, you can pass through the WP object
     * field names too, such as "post_title" or "post_content" for example.
     *
     * @param array $params (optional) Fields to show on the form, defaults to all fields
     * @param string $label (optional) Save button label, defaults to "Save Changes"
     * @param string $thank_you (optional) Thank you URL to send to upon success
     *
     * @return bool|mixed
     * @since 2.0.0
     * @link http://podsframework.org/docs/form/
     */
    public function form ( $params = null, $label = null, $thank_you = null ) {
        $defaults = array(
            'fields' => $params,
            'label' => $label,
            'thank_you' => $thank_you
        );

        if ( is_array( $params ) )
            $params = array_merge( $defaults, $params );
        else
            $params = $defaults;

        $pod =& $this;

        $params = $this->do_hook( 'form_params', $params );

        $fields = $params[ 'fields' ];

        if ( null !== $fields && !is_array( $fields ) && 0 < strlen( $fields ) )
            $fields = explode( ',', $fields );

        $object_fields = (array) pods_var_raw( 'object_fields', $this->pod_data, array(), null, true );

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

            if ( !is_array( $field ) ) {
                $name = $field;

                $field = array(
                    'name' => $name
                );
            }

            $field = array_merge( $defaults, $field );

            $field[ 'name' ] = trim( $field[ 'name' ] );

            if ( empty( $field[ 'name' ] ) )
                $field[ 'name' ] = trim( $name );

            if ( pods_var_raw( 'hidden', $field, false, null, true ) )
                continue;
            elseif ( isset( $object_fields[ $field[ 'name' ] ] ) )
                $fields[ $field[ 'name' ] ] = array_merge( $object_fields[ $field[ 'name' ] ], $field );
            elseif ( isset( $this->fields[ $field[ 'name' ] ] ) )
                $fields[ $field[ 'name' ] ] = array_merge( $this->fields[ $field[ 'name' ] ], $field );
        }

        unset( $form_fields ); // Cleanup

        $fields = $this->do_hook( 'form_fields', $fields, $params );

        $label = $params[ 'label' ];

        if ( empty( $label ) )
            $label = __( 'Save Changes', 'pods' );

        $thank_you = $params[ 'thank_you' ];

        PodsForm::$form_counter++;

        ob_start();

        if ( empty( $thank_you ) ) {
            $success = 'success';

            if ( 1 < PodsForm::$form_counter )
                $success .= PodsForm::$form_counter;

            $thank_you = pods_var_update( array( 'success*' => null, $success => 1 ) );

            if ( 1 == pods_var( $success, 'get', 0 ) ) {
                echo '<div id="message" class="pods-form-front-success">'
                     . __( 'Form submitted successfully', 'pods' ) . '</div>';
            }
        }

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
    public function do_magic_tags ( $code ) {
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
    private function process_magic_tags ( $tag ) {
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

        $helper_name = $before = $after = '';

        if ( isset( $tag[ 1 ] ) && !empty( $tag[ 1 ] ) && class_exists( 'Pods_Helpers' ) ) {
            $value = $this->field( $field_name );

            $helper_name = $tag[ 1 ];

            $params = array(
                'helper' => $helper_name,
                'value' => $value,
                'name' => $field_name,
                'deprecated' => false
            );

            if ( class_exists( 'Pods_Templates' ) )
                $params[ 'deprecated' ] = Pods_Templates::$deprecated;

            $value = Pods_Helpers::helper( $params, $this );
        }
        else
            $value = $this->display( $field_name );

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
