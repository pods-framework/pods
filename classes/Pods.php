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
     * @var array Array of pod item arrays
     */
    public $rows = array();

    /**
     * @var array Current pod item array
     */
    public $row = array();

    /**
     * @var array Override pod item array
     */
    public $row_override = array();

    /**
     * @var bool
     */
    public $display_errors = false;

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
     * @var mixed SEO related vars for Pod Pages
     */
    public $page_template;
    public $body_classes;
    public $meta = array();
    public $meta_properties = array();
    public $meta_extra = '';

    /**
     * @var
     */
    public $deprecated;

    public $datatype;

    public $datatype_id;

    public $sql;

    /**
     * Constructor - Pods Framework core
     *
     * @param string $pod The pod name
     * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
     *
     * @return \Pods
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.0.0
     * @link http://pods.io/docs/pods/
     */
    public function __construct ( $pod = null, $id = null ) {
        if ( null === $pod  ) {
            $pod = get_post_type();

            if ( null === $id )
                $id = get_the_ID();
        }

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
     * @since 2.0
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
     * @since 2.0
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
     * @since 2.0
     * @link http://pods.io/docs/data/
     */
    public function data () {
        $this->do_hook( 'data' );

        if ( empty( $this->rows ) )
            return false;

        return (array) $this->rows;
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
    public function fields ( $field = null, $option = null ) {
        // No fields found
        if ( empty( $this->fields ) )
            $field_data = array();
        // Return all fields
        elseif ( empty( $field ) )
            $field_data = (array) $this->fields;
        // Field not found
        elseif ( !isset( $this->fields[ $field ] ) )
            $field_data = array();
        // Return all field data
        elseif ( empty( $option ) )
            $field_data = $this->fields[ $field ];
        else {
            // Merge options
            $options = array_merge( $this->fields[ $field ], $this->fields[ $field ][ 'options' ] );

            $field_data = null;

            // Return option
            if ( isset( $options[ $option ] ) )
                $field_data = $options[ $option ];
        }

        return $this->do_hook( 'fields', $field_data, $field, $option );
    }

    /**
     * Return row array for an item
     *
     * @return array
     *
     * @since 2.0
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
     * @since 2.0
     * @link http://pods.io/docs/display/
     */
    public function display ( $name, $single = null ) {
        $defaults = array(
            'name' => $name,
            'orderby' => null,
            'single' => $single,
            'args' => array(),
            'in_form' => false,
            'display' => true
        );

        if ( is_array( $name ) || is_object( $name ) )
            $params = (object) array_merge( $defaults, (array) $name );
        else
            $params = (object) $defaults;

        if ( is_array( $params->single ) ) {
            $params->args = $params->single;
            $params->single = null;
        }

        $value = $this->field( $params );

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
     * @since 2.0
     * @link http://pods.io/docs/display/
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
     * @since 2.0
     * @link http://pods.io/docs/field/
     */
    public function field ( $name, $single = null, $raw = false ) {
        global $sitepress;

        $defaults = array(
            'name' => $name,
            'orderby' => null,
            'single' => $single,
            'in_form' => false,
            'raw' => $raw,
            'display' => false,
            'output' => null,
            'deprecated' => false
        );

        if ( is_array( $name ) || is_object( $name ) )
            $params = (object) array_merge( $defaults, (array) $name );
        else
            $params = (object) $defaults;

        if ( null === $params->output )
            $params->output = $this->do_hook( 'field_related_output_type', 'arrays', $this->row, $params );

        // Support old $orderby variable
        if ( null !== $params->single && !is_bool( $params->single ) && empty( $params->orderby ) ) {
            pods_deprecated( 'Pods::field', '2.0', 'Use $params[ \'orderby\' ] instead' );

            $params->orderby = $params->single;
            $params->single = false;
        }

        if ( null !== $params->single )
            $params->single = (boolean) $params->single;

        if ( is_array( $params->name ) || strlen( $params->name ) < 1 )
            return null;

        $value = null;

        if ( isset( $this->row_override[ $params->name ] ) )
            $value = $this->row_override[ $params->name ];

        if ( false === $this->row() ) {
            if ( false !== $this->data() )
                $this->fetch();
            else
                return $value;
        }

        if ( $this->data->field_id == $params->name ) {
            if ( isset( $this->row[ $params->name ] ) )
                return $this->row[ $params->name ];
            elseif ( null !== $value )
                return $value;

            return 0;
        }

        $tableless_field_types = PodsForm::tableless_field_types();
        $simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

        $params->traverse = array();

        if ( 'detail_url' == $params->name || ( in_array( $params->name, array( 'permalink', 'the_permalink' ) ) && in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) ) {
            if ( 0 < strlen( $this->detail_page ) )
                $value = get_home_url() . '/' . $this->do_magic_tags( $this->detail_page );
            elseif ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) )
                $value = get_permalink( $this->id() );
            elseif ( 'taxonomy' == $this->pod_data[ 'type' ] )
                $value = get_term_link( $this->id(), $this->pod_data[ 'name' ] );
            elseif ( 'user' == $this->pod_data[ 'type' ] )
                $value = get_author_posts_url( $this->id() );
            elseif ( 'comment' == $this->pod_data[ 'type' ] )
                $value = get_comment_link( $this->id() );
        }

        $field_data = false;
        $field_type = false;

        $first_field = explode( '.', $params->name );
        $first_field = $first_field[ 0 ];

        if ( isset( $this->fields[ $first_field ] ) ) {
            $field_data = $this->fields[ $first_field ];
            $field_type = 'field';
        }
        elseif ( isset( $this->pod_data[ 'object_fields' ] ) && !empty( $this->pod_data[ 'object_fields' ] ) ) {
            if ( isset( $this->pod_data[ 'object_fields' ][ $first_field ] ) ) {
                $field_data = $this->pod_data[ 'object_fields' ][ $first_field ];
                $field_type = 'object_field';
            }
            else {
                foreach ( $this->pod_data[ 'object_fields' ] as $object_field => $object_field_opt ) {
                    if ( in_array( $first_field, $object_field_opt[ 'alias' ] ) ) {
                        if ( $first_field == $params->name )
                            $params->name = $object_field;

                        $first_field = $object_field;
                        $field_data = $object_field_opt;
                        $field_type = 'object_field';

                        break;
                    }
                }
            }
        }

        if ( empty( $value ) && isset( $this->row[ $params->name ] ) ) {
            if ( empty( $field_data ) || in_array( $field_data[ 'type' ], array( 'boolean', 'number', 'currency' ) ) || in_array( $field_data[ 'type' ], $tableless_field_types ) )
                $params->raw = true;

            $value = $this->row[ $params->name ];

            if ( !is_array( $value ) && 'pick' == $field_data[ 'type' ] && in_array( $field_data[ 'pick_object' ], $simple_tableless_objects ) )
                $value = PodsForm::field_method( 'pick', 'simple_value', $params->name, $value, $field_data, $this->pod_data, $this->id(), true );
        }
        elseif ( empty( $value ) ) {
            $object_field_found = false;

            if ( 'object_field' == $field_type ) {
                $object_field_found = true;

                if ( isset( $this->row[ $first_field ] ) )
                    $value = $this->row[ $first_field ];
                elseif ( in_array( $field_data[ 'type' ], $tableless_field_types ) ) {
                    $this->fields[ $first_field ] = $field_data;
                    $object_field_found = false;
                }
                else
                    return null;
            }

            if ( 'post_type' == $this->pod_data[ 'type' ] && !isset( $this->fields[ $params->name ] ) ) {
                if ( !isset( $this->fields[ 'post_thumbnail' ] ) && ( 'post_thumbnail' == $params->name || 0 === strpos( $params->name, 'post_thumbnail.' ) ) ) {
                    $size = 'thumbnail';

                    if ( 0 === strpos( $params->name, 'post_thumbnail.' ) ) {
                        $field_names = explode( '.', $params->name );

                        if ( isset( $field_names[ 1 ] ) )
                            $size = $field_names[ 1 ];
                    }

                    // Pods will auto-get the thumbnail ID if this isn't an attachment
                    $value = pods_image( $this->id(), $size );

                    $object_field_found = true;
                }
                elseif ( !isset( $this->fields[ 'post_thumbnail_url' ] ) && ( 'post_thumbnail_url' == $params->name || 0 === strpos( $params->name, 'post_thumbnail_url.' ) ) ) {
                    $size = 'thumbnail';

                    if ( 0 === strpos( $params->name, 'post_thumbnail_url.' ) ) {
                        $field_names = explode( '.', $params->name );

                        if ( isset( $field_names[ 1 ] ) )
                            $size = $field_names[ 1 ];
                    }

                    // Pods will auto-get the thumbnail ID if this isn't an attachment
                    $value = pods_image_url( $this->id(), $size );

                    $object_field_found = true;
                }
            }
            elseif ( 'user' == $this->pod_data[ 'type' ] && !isset( $this->fields[ $params->name ] ) ) {
                if ( !isset( $this->fields[ 'avatar' ] ) && ( 'avatar' == $params->name || 0 === strpos( $params->name, 'avatar.' ) ) ) {
                    $size = null;

                    if ( 0 === strpos( $params->name, 'avatar.' ) ) {
                        $field_names = explode( '.', $params->name );

                        if ( isset( $field_names[ 1 ] ) )
                            $size = (int) $field_names[ 1 ];
                    }

                    if ( !empty( $size ) )
                        $value = get_avatar( $this->id(), $size );
                    else
                        $value = get_avatar( $this->id() );

                    $object_field_found = true;
                }
            }
            elseif ( 0 === strpos( $params->name, 'image_attachment.' ) ) {
                $size = 'thumbnail';

                $image_id = 0;

                $field_names = explode( '.', $params->name );

                if ( isset( $field_names[ 1 ] ) )
                    $image_id = $field_names[ 1 ];

                if ( isset( $field_names[ 2 ] ) )
                    $size = $field_names[ 2 ];

                if ( !empty( $image_id ) ) {
                    $value = pods_image( $image_id, $size );

                    if ( !empty( $value ) )
                        $object_field_found = true;
                }
            }
            elseif ( 0 === strpos( $params->name, 'image_attachment_url.' ) ) {
                $size = 'thumbnail';

                $image_id = 0;

                $field_names = explode( '.', $params->name );

                if ( isset( $field_names[ 1 ] ) )
                    $image_id = $field_names[ 1 ];

                if ( isset( $field_names[ 2 ] ) )
                    $size = $field_names[ 2 ];

                if ( !empty( $image_id ) ) {
                    $value = pods_image_url( $image_id, $size );

                    if ( !empty( $value ) )
                        $object_field_found = true;
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

                        if ( 'pick' == $this->fields[ $params->name ][ 'type' ] && in_array( $this->fields[ $params->name ][ 'pick_object' ], $simple_tableless_objects ) ) {
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

                    $no_conflict = pods_no_conflict_check( $this->pod_data[ 'type' ] );

                    if ( !$no_conflict )
                        pods_no_conflict_on( $this->pod_data[ 'type' ] );

                    if ( in_array( $this->pod_data[ 'type' ], array( 'post_type', 'media' ) ) ) {
                        $id = $this->id();

                        // Support for WPML 'duplicated' translation handling
                        if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $this->pod_data[ 'name' ] ) ) {
                            $master_post_id = (int) get_post_meta( $id, '_icl_lang_duplicate_of', true );

                            if ( 0 < $master_post_id )
                                $id = $master_post_id;
                        }

                        $value = get_post_meta( $id, $params->name, $params->single );
                    }
                    elseif ( in_array( $this->pod_data[ 'type' ], array( 'user', 'comment' ) ) )
                        $value = get_metadata( $this->pod_data[ 'type' ], $this->id(), $params->name, $params->single );
                    elseif ( 'settings' == $this->pod_data[ 'type' ] )
                        $value = get_option( $this->pod_data[ 'name' ] . '_' . $params->name );

                    // Handle Simple Relationships
                    if ( $simple ) {
                        if ( null === $params->single )
                            $params->single = false;

                        $value = PodsForm::field_method( 'pick', 'simple_value', $params->name, $value, $this->fields[ $params->name ], $this->pod_data, $this->id(), true );
                    }

                    if ( !$no_conflict )
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
                    $last_options = array();

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
                        $last_options = array();

                        if ( $field_exists && 'pick' == $all_fields[ $pod ][ $field ][ 'type' ] && in_array( $all_fields[ $pod ][ $field ][ 'pick_object' ], $simple_tableless_objects ) ) {
                            $simple = true;
                            $last_options = $all_fields[ $pod ][ $field ];
                        }

                        // Tableless handler
                        if ( $field_exists && ( 'pick' != $all_fields[ $pod ][ $field ][ 'type' ] || !$simple ) ) {
                            $type = $all_fields[ $pod ][ $field ][ 'type' ];
                            $pick_object = $all_fields[ $pod ][ $field ][ 'pick_object' ];
                            $pick_val = $all_fields[ $pod ][ $field ][ 'pick_val' ];

                            if ( 'table' == $pick_object )
                                $pick_val = pods_var( 'pick_table', $all_fields[ $pod ][ $field ][ 'options' ], $pick_val, null, true );

                            if ( '__current__' == $pick_val )
                                $pick_val = $pod;

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

                            // Temporary hack until there's some better handling here
                            $last_limit = $last_limit * count( $ids );

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
                            if ( !empty( $pick_object ) && !empty( $pick_val ) ) {
                                if ( 'pod' == $pick_object )
                                    $pod = $pick_val;
                                else {
                                    $check = $this->api->get_table_info( $pick_object, $pick_val );

                                    if ( !empty( $check ) && !empty( $check[ 'pod' ] ) )
                                        $pod = $check[ 'pod' ][ 'name' ];
                                }
                            }
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

                            if ( in_array( $last_type, PodsForm::file_field_types() ) ) {
                                $object_type = 'media';
                                $object = 'attachment';
                            }

                            $data = array();

                            $table = $this->api->get_table_info( $object_type, $object, null, null, $last_options );

                            $join = $where = array();

                            if ( !empty( $table[ 'join' ] ) )
                                $join = (array) $table[ 'join' ];

                            if ( !empty( $table[ 'where' ] ) || !empty( $ids ) ) {
                                foreach ( $ids as $id ) {
                                    $where[ $id ] = '`t`.`' . $table[ 'field_id' ] . '` = ' . (int) $id;
                                }

                                if ( !empty( $where ) )
                                    $where = array( '( ' . implode( ' OR ', $where ) . ' )' );

                                if ( !empty( $table[ 'where' ] ) )
                                    $where = array_merge( $where, array_values( (array) $table[ 'where' ] ) );
                            }

                            if ( !empty( $table[ 'table' ] ) ) {
                                $sql = array(
                                    'select' => '*, `t`.`' . $table[ 'field_id' ] . '` AS `pod_item_id`',
                                    'table' => $table[ 'table' ],
                                    'join' => $join,
                                    'where' => $where,
                                    'orderby' => $params->orderby
                                );

                                // Output types
                                if ( in_array( $params->output, array( 'ids', 'objects' ) ) )
                                    $sql[ 'select' ] = '`t`.`' . $table[ 'field_id' ] . '` AS `pod_item_id`';
                                elseif ( 'names' == $params->output && !empty( $table[ 'field_index' ] ) )
                                    $sql[ 'select' ] = '`t`.`' . $table[ 'field_index' ] . '` AS `pod_item_index`, `t`.`' . $table[ 'field_id' ] . '` AS `pod_item_id`';

                                $item_data = pods_data()->select( $sql );

                                $items = array();

                                foreach ( $item_data as $item ) {
                                    if ( empty( $item->pod_item_id ) )
                                        continue;

                                    // Get Item ID
                                    $item_id = $item->pod_item_id;

                                    // Cleanup
                                    unset( $item->pod_item_id );

                                    // Output types
                                    if ( 'ids' == $params->output )
                                        $item = (int) $item_id;
                                    elseif ( 'objects' == $params->output ) {
                                        if ( in_array( $object_type, array( 'post_type', 'media' ) ) )
                                            $item = get_post( $item_id );
                                        elseif ( 'taxonomy' == $object_type )
                                            $item = get_term( $item_id, $object );
                                        elseif ( 'user' == $object_type )
                                            $item = get_userdata( $item_id );
                                        elseif ( 'comment' == $object_type )
                                            $item = get_comment( $item_id );
                                        else
                                            $item = (object) $item;
                                    }
                                    elseif ( 'names' == $params->output && !empty( $table[ 'field_index' ] ) )
                                        $item = $item->pod_item_index;
                                    else // arrays
                                        $item = get_object_vars( (object) $item );

                                    // Pass item data into $data
                                    $items[ $item_id ] = $item;
                                }

                                // Cleanup
                                unset( $item_data );

                                // Return all of the data in the order expected
                                if ( empty( $params->orderby ) ) {
                                    foreach ( $ids as $id ) {
                                        if ( isset( $items[ $id ] ) )
                                            $data[ $id ] = $items[ $id ];
                                    }
                                }
                            }

                            if ( in_array( $last_type, $tableless_field_types ) || in_array( $last_type, array( 'boolean', 'number', 'currency' ) ) )
                                $params->raw = true;

                            if ( empty( $data ) )
                                $value = false;
                            else {
                                $object_type = $table[ 'type' ];

                                if ( in_array( $table[ 'type' ], array( 'post_type', 'attachment', 'media' ) ) )
                                    $object_type = 'post';

                                $no_conflict = true;

                                if ( in_array( $object_type, array( 'post', 'user', 'comment', 'settings' ) ) ) {
                                    $no_conflict = pods_no_conflict_check( $object_type );

                                    if ( !$no_conflict )
                                        pods_no_conflict_on( $object_type );
                                }

                                // Return entire array
                                if ( false === $params->in_form && false !== $field_exists && in_array( $last_type, $tableless_field_types ) )
                                    $value = $data;
                                // Return an array of single column values
                                else {
                                    $value = array();

                                    if ( $params->in_form )
                                        $field = $table[ 'field_id' ];

                                    $related_obj = false;

                                    foreach ( $data as $item_id => $item ) {
                                        if ( 'detail_url' == $field || ( in_array( $field, array( 'permalink', 'the_permalink' ) ) && 'post' == $object_type ) ) {
                                            if ( 'pod' == $object_type ) {
                                                if ( empty( $related_obj ) )
                                                    $related_obj = pods( $object );

                                                if ( is_object( $related_obj ) ) {
                                                    $related_obj->fetch( $item_id );

                                                    $value[] = $related_obj->field( 'detail_url' );
                                                }
                                                else
                                                    $value[] = '';
                                            }
                                            if ( 'post' == $object_type )
                                                $value[] = get_permalink( $item_id );
                                            elseif ( 'taxonomy' == $object_type )
                                                $value[] = get_term_link( $item_id, $object );
                                            elseif ( 'user' == $object_type )
                                                $value[] = get_author_posts_url( $item_id );
                                            elseif ( 'comment' == $object_type )
                                                $value[] = get_comment_link( $item_id );
                                            else
                                                $value[] = '';
                                        }
                                        elseif ( is_array( $item ) && isset( $item[ $field ] ) ) {
                                            if ( $table[ 'field_id' ] == $field )
                                                $value[] = (int) $item[ $field ];
                                            else
                                                $value[] = $item[ $field ];
                                        }
                                        elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
                                            if ( $table[ 'field_id' ] == $field )
                                                $value[] = (int) $item->{$field};
                                            else
                                                $value[] = $item->{$field};
                                        }
                                        elseif ( 'post' == $object_type ) {
                                            // Support for WPML 'duplicated' translation handling
                                            if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $object ) ) {
                                                $master_post_id = (int) get_post_meta( $item_id, '_icl_lang_duplicate_of', true );

                                                if ( 0 < $master_post_id )
                                                    $item_id = $master_post_id;
                                            }

                                            $value[] = get_post_meta( $item_id, $field, true );
                                        }
                                        elseif ( in_array( $object_type, array( 'post', 'user', 'comment' ) ) )
                                            $value[] = get_metadata( $object_type, $item_id, $field, true );
                                        elseif ( 'settings' == $object_type )
                                            $value[] = get_option( $object . '_' . $field );
                                    }
                                }

                                if ( in_array( $object_type, array( 'post', 'user', 'comment', 'settings' ) ) && !$no_conflict )
                                    pods_no_conflict_off( $object_type );

                                // Handle Simple Relationships
                                if ( $simple ) {
                                    if ( null === $params->single )
                                        $params->single = false;

                                    $value = PodsForm::field_method( 'pick', 'simple_value', $field, $value, $last_options, $all_fields[ $pod ], 0, true );
                                }
                                elseif ( false === $params->in_form && !empty( $value ) )
                                    $value = array_values( $value );

                                // Return a single column value
                                if ( false === $params->in_form && 1 == $limit && !empty( $value ) && is_array( $value ) && 1 == count( $value ) )
                                    $value = current( $value );
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

        if ( $params->single && is_array( $value ) && 1 == count( $value ) )
            $value = current( $value );

        // @todo Expand this into traversed fields too
        if ( !empty( $field_data ) ) {
            if ( false === $params->raw && false === $params->in_form ) {
                $field_data[ 'options' ] = pods_var_raw( 'options', $field_data, array(), null, true );

                $post_temp = false;

                if ( 'post_type' == pods_var( 'type', $this->pod_data ) && 0 < $this->id() && ( !isset( $GLOBALS[ 'post' ] ) || empty( $GLOBALS[ 'post' ] ) ) ) {
                    $post_temp = true;

                    $GLOBALS[ 'post' ] = get_post( $this->id() );
                }

                if ( 0 < strlen( pods_var( 'display_filter', $field_data[ 'options' ] ) ) )
                    $value = apply_filters( pods_var( 'display_filter', $field_data[ 'options' ] ), $value );
                elseif ( 1 == pods_var( 'display_process', $field_data[ 'options' ], 1 ) || $params->display ) {
                    $value = PodsForm::display(
                        $field_data[ 'type' ],
                        $value,
                        $params->name,
                        array_merge( $field_data, $field_data[ 'options' ] ),
                        $this->pod_data,
                        $this->id()
                    );
                }

                if ( $post_temp )
                    $GLOBALS[ 'post' ] = null;
            }
            else {
                $value = PodsForm::value(
                    $field_data[ 'type' ],
                    $value,
                    $params->name,
                    array_merge( $field_data, $field_data[ 'options' ] ),
                    $this->pod_data,
                    $this->id()
                );
            }
        }

        $value = $this->do_hook( 'field', $value, $this->row, $params );

        return $value;
    }

    /**
     * Check if an item field has a specific value in it
     *
     * @param string $field Field name
     * @param mixed $value Value to check
     * @param int $id (optional) ID of the pod item to check
     *
     * @return bool Whether the value was found
     *
     * @since 2.3.3
     */
    public function has ( $field, $value, $id = null ) {
        $pod =& $this;

        if ( null === $id )
            $id = $this->id();
        elseif ( $id != $this->id() )
            $pod = pods( $this->pod, $id );

        $this->do_hook( 'has', $field, $value, $id );

        if ( !isset( $this->fields[ $field ] ) )
            return false;

        // Tableless fields
        if ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_array( $value ) )
                $value = explode( ',', $value );

            if ( 'pick' == $this->fields[ $field ][ 'type' ] && in_array( $this->fields[ $field ][ 'pick_object' ], PodsForm::field_method( 'pick', 'simple_objects' ) ) ) {
                $current_value = $pod->raw( $field );

                if ( !empty( $current_value ) )
                    $current_value = (array) $current_value;

                foreach ( $current_value as $v ) {
                    if ( in_array( $v, $value ) )
                        return true;
                }
            }
            else {
                $related_ids = $this->api->lookup_related_items( $this->fields[ $field ][ 'id' ], $this->pod_data[ 'id' ], $id, $this->fields[ $field ], $this->pod_data );

                foreach ( $value as $k => $v ) {
                    $value[ $k ] = (int) $v;
                }

                foreach ( $related_ids as $v ) {
                    if ( in_array( $v, $value ) )
                        return true;
                }
            }
        }
        // Text fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::text_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( 0 < strlen( $current_value ) )
                return stripos( $current_value, $value );
        }
        // All other fields
        else
            return $this->is( $field, $value, $id );

        return false;
    }

    /**
     * Check if an item field is a specific value
     *
     * @param string $field Field name
     * @param mixed $value Value to check
     * @param int $id (optional) ID of the pod item to check
     *
     * @return bool Whether the value was found
     *
     * @since 2.3.3
     */
    public function is ( $field, $value, $id = null ) {
        $pod =& $this;

        if ( null === $id )
            $id = $this->id();
        elseif ( $id != $this->id() )
            $pod = pods( $this->pod, $id );

        $this->do_hook( 'is', $field, $value, $id );

        if ( !isset( $this->fields[ $field ] ) )
            return false;

        // Tableless fields
        if ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_array( $value ) )
                $value = explode( ',', $value );

            $current_value = array();

            if ( 'pick' == $this->fields[ $field ][ 'type' ] && in_array( $this->fields[ $field ][ 'pick_object' ], PodsForm::field_method( 'pick', 'simple_objects' ) ) ) {
                $current_value = $pod->raw( $field );

                if ( !empty( $current_value ) )
                    $current_value = (array) $current_value;

                foreach ( $current_value as $v ) {
                    if ( in_array( $v, $value ) )
                        return true;
                }
            }
            else {
                $related_ids = $this->api->lookup_related_items( $this->fields[ $field ][ 'id' ], $this->pod_data[ 'id' ], $id, $this->fields[ $field ], $this->pod_data );

                foreach ( $value as $k => $v ) {
                    $value[ $k ] = (int) $v;
                }

                foreach ( $related_ids as $v ) {
                    if ( in_array( $v, $value ) )
                        return true;
                }
            }

            $current_value = array_filter( array_unique( $current_value ) );
            $value = array_filter( array_unique( $value ) );

            sort( $current_value );
            sort( $value );

            if ( $value === $current_value )
                return true;
        }
        // Number fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::number_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( (float) $current_value === (float) $value )
                return true;
        }
        // Date fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::date_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( 0 < strlen( $current_value ) ) {
                if ( strtotime( $current_value ) == strtotime( $value ) )
                    return true;
            }
            elseif ( empty( $value ) )
                return true;
        }
        // Text fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::text_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( (string) $current_value === (string) $value )
                return true;
        }
        // All other fields
        else {
            $current_value = $pod->raw( $field );

            if ( $current_value === $value )
                return true;
        }

        return false;
    }

    /**
     * Return the item ID
     *
     * @return int
     * @since 2.0
     */
    public function id () {
        return $this->field( $this->data->field_id );
    }

    /**
     * Return the previous item ID, loops at the last id to return the first
     *
     * @param int $id
     * @param array $params_override
     *
     * @return int
     * @since 2.0
     */
    public function prev_id ( $id = null, $params_override = null ) {
        if ( null === $id )
            $id = $this->field( 'id' );

        $id = (int) $id;

        $params = array(
            'select' => "`t`.`{$this->data->field_id}`",
            'where' => "`t`.`{$this->data->field_id}` < {$id}",
            'orderby' => "`t`.`{$this->data->field_id}` DESC",
            'limit' => 1
        );

        if ( !empty( $params_override ) || !empty( $this->params ) ) {
            if ( !empty( $params_override ) )
                $params = $params_override;
            elseif ( !empty( $this->params ) )
                $params = $this->params;

            if ( 0 < $id )
                $params[ 'where' ] = "`t`.`{$this->data->field_id}` < {$id}";
            elseif ( isset( $params[ 'offset' ] ) && 0 < $params[ 'offset' ] )
                $params[ 'offset' ] -= 1;
            elseif ( !isset( $params[ 'offset' ] ) && !empty( $this->params ) && 0 < $this->row )
                $params[ 'offset' ] = $this->row - 1;
            else
                return 0;

            $params[ 'select' ] = "`t`.`{$this->data->field_id}`";
            $params[ 'limit' ] = 1;
        }

        $pod = pods( $this->pod, $params );

        $new_id = 0;

        if ( $pod->fetch() )
            $new_id = $pod->id();

        $new_id = $this->do_hook( 'prev_id', $new_id, $id, $pod, $params_override );

        return $new_id;
    }

    /**
     * Return the next item ID, loops at the first id to return the last
     *
     * @param int $id
     * @param array $find_params
     *
     * @return int
     * @since 2.0
     */
    public function next_id ( $id = null, $params_override = null ) {
        if ( null === $id )
            $id = $this->field( 'id' );

        $id = (int) $id;

        $params = array(
            'select' => "`t`.`{$this->data->field_id}`",
            'where' => "{$id} < `t`.`{$this->data->field_id}`",
            'orderby' => "`t`.`{$this->data->field_id}` ASC",
            'limit' => 1
        );

        if ( !empty( $params_override ) || !empty( $this->params ) ) {
            if ( !empty( $params_override ) )
                $params = $params_override;
            elseif ( !empty( $this->params ) )
                $params = $this->params;

            if ( 0 < $id )
                $params[ 'where' ] = "{$id} < `t`.`{$this->data->field_id}`";
            elseif ( !isset( $params[ 'offset' ] ) ) {
                if ( !empty( $this->params ) && -1 < $this->row )
                    $params[ 'offset' ] = $this->row + 1;
                else
                    $params[ 'offset' ] = 1;
            }
            else
                $params[ 'offset' ] += 1;

            $params[ 'select' ] = "`t`.`{$this->data->field_id}`";
            $params[ 'limit' ] = 1;
        }

        $pod = pods( $this->pod, $params );

        $new_id = 0;

        if ( $pod->fetch() )
            $new_id = $pod->id();

        $new_id = $this->do_hook( 'next_id', $new_id, $id, $pod, $params_override );

        return $new_id;
    }

    /**
     * Return the first item ID
     *
     * @param array $params_override
     *
     * @return int
     * @since 2.3
     */
    public function first_id ( $params_override = null ) {
        $params = array(
            'select' => "`t`.`{$this->data->field_id}`",
            'orderby' => "`t`.`{$this->data->field_id}` ASC",
            'limit' => 1
        );

        if ( !empty( $params_override ) || !empty( $this->params ) ) {
            if ( !empty( $params_override ) )
                $params = $params_override;
            elseif ( !empty( $this->params ) )
                $params = $this->params;

            $params[ 'select' ] = "`t`.`{$this->data->field_id}`";
            $params[ 'offset' ] = 0;
            $params[ 'limit' ] = 1;
        }

        $pod = pods( $this->pod, $params );

        $new_id = 0;

        if ( $pod->fetch() )
            $new_id = $pod->id();

        $new_id = $this->do_hook( 'first_id', $new_id, $pod, $params_override );

        return $new_id;
    }

    /**
     * Return the last item ID
     *
     * @param array $params_override
     *
     * @return int
     * @since 2.3
     */
    public function last_id ( $params_override = null ) {
        $params = array(
            'select' => "`t`.`{$this->data->field_id}`",
            'orderby' => "`t`.`{$this->data->field_id}` DESC",
            'limit' => 1
        );

        if ( !empty( $params_override ) || !empty( $this->params ) ) {
            if ( !empty( $params_override ) )
                $params = $params_override;
            elseif ( !empty( $this->params ) )
                $params = $this->params;

            if ( isset( $params[ 'total_found' ] ) )
                $params[ 'offset' ] = $params[ 'total_found' ] - 1;
            else
                $params[ 'offset' ] = $this->total_found() - 1;

            $params[ 'select' ] = "`t`.`{$this->data->field_id}`";
            $params[ 'limit' ] = 1;
        }

        $pod = pods( $this->pod, $params );

        $new_id = 0;

        if ( $pod->fetch() )
            $new_id = $pod->id();

        $new_id = $this->do_hook( 'last_id', $new_id, $pod, $params_override );
    }

    /**
     * Return the item name
     *
     * @return string
     * @since 2.0
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
     * @since 2.0
     * @link http://pods.io/docs/find/
     */
    public function find ( $params = null, $limit = 15, $where = null, $sql = null ) {
        $tableless_field_types = PodsForm::tableless_field_types();
        $simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

        $select = '`t`.*';

        if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) && 'table' == $this->pod_data[ 'storage' ] )
            $select .= ', `d`.*';

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

            'filters' => $this->filters,
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

        // Allow orderby array ( 'field' => 'asc|desc' )
        if ( !empty( $params->orderby ) && is_array( $params->orderby ) ) {
            foreach ( $params->orderby as $k => &$orderby ) {
                if ( !is_numeric( $k ) ) {
                    $key = '';

                    $order = 'ASC';

                    if ( 'DESC' == strtoupper( $orderby ) )
                        $order = 'DESC';

                    if ( isset( $this->fields[ $k ] ) && in_array( $this->fields[ $k ][ 'type' ], $tableless_field_types ) ) {
                        if ( in_array( $this->fields[ $k ][ 'pick_object' ], $simple_tableless_objects ) ) {
                            if ( 'table' == $this->pod_data[ 'storage' ] ) {
                                if ( !in_array( $this->pod_data[ 'type' ], array( 'pod', 'table' ) ) )
                                    $key = "`d`.`{$k}`";
                                else
                                    $key = "`t`.`{$k}`";
                            }
                            else
                                $key = "`{$k}`.`meta_value`";
                        }
                        else {
                            $pick_val = $this->fields[ $k ][ 'pick_val' ];

                            if ( '__current__' == $pick_val )
                                $pick_val = $this->pod;

                            $table = $this->api->get_table_info( $this->fields[ $k ][ 'pick_object' ], $pick_val );

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

        $this->params = $params;

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
     * @since 2.0
     * @link http://pods.io/docs/fetch/
     */
    public function fetch ( $id = null ) {
        $this->do_hook( 'fetch', $id );

        if ( !empty( $id ) )
            $this->params = array();

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
     * @since 2.0
     * @link http://pods.io/docs/reset/
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
     * @since 2.0
     * @link http://pods.io/docs/total/
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
     * @since 2.0
     * @link http://pods.io/docs/total-found/
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
     * Fetch the nth state
     *
     * @see PodsData::nth
     *
     * @param int|string $nth The $nth to match on the PodsData::row_number
     *
     * @return bool Whether $nth matches
     * @since 2.3
     */
    public function nth ( $nth = null ) {
        $this->do_hook( 'nth', $nth );

        return $this->data->nth( $nth );
    }

    /**
     * Fetch the current position in the loop (starting at 1)
     *
     * @see PodsData::position
     *
     * @return int Current row number (+1)
     * @since 2.3
     */
    public function position () {
        $this->do_hook( 'position' );

        return $this->data->position();
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
     * @since 2.0
     * @link http://pods.io/docs/add/
     */
    public function add ( $data = null, $value = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        $data = (array) $this->do_hook( 'add', $data );

        if ( empty( $data ) )
            return false;

        $params = array(
            'pod' => $this->pod,
            'data' => $data,
            'allow_custom_fields' => true
        );

        return $this->api->save_pod_item( $params );
    }

    /**
     * Add an item to the values of a relationship field, add a value to a number field (field+1), add time to a date field, or add text to a text field
     *
     * @see PodsAPI::save_pod_item
     *
     * @param string $field Field name
     * @param mixed $value ID(s) to add, int|float to add to number field, string for dates (+1 week), or string for text
     * @param int $id (optional) ID of the pod item to update
     *
     * @return int The item ID
     *
     * @since 2.3
     */
    public function add_to ( $field, $value, $id = null ) {
        $pod =& $this;

        if ( null === $id )
            $id = $this->id();
        elseif ( $id != $this->id() )
            $pod = pods( $this->pod, $id );

        $this->do_hook( 'add_to', $field, $value, $id );

        if ( !isset( $this->fields[ $field ] ) )
            return $id;

        // Tableless fields
        if ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_array( $value ) )
                $value = explode( ',', $value );

            if ( 'pick' == $this->fields[ $field ][ 'type' ] && in_array( $this->fields[ $field ][ 'pick_object' ], PodsForm::field_method( 'pick', 'simple_objects' ) ) ) {
                $current_value = $pod->raw( $field );

                if ( !empty( $current_value ) )
                    $current_value = (array) $current_value;

                $value = array_merge( $current_value, $value );
            }
            else {
                $related_ids = $this->api->lookup_related_items( $this->fields[ $field ][ 'id' ], $this->pod_data[ 'id' ], $id, $this->fields[ $field ], $this->pod_data );

                foreach ( $value as $k => $v ) {
                    $value[ $k ] = (int) $v;
                }

                $value = array_merge( $related_ids, $value );
            }

            $value = array_filter( array_unique( $value ) );

            if ( empty( $value ) )
                return $id;
        }
        // Number fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::number_field_types() ) ) {
            $current_value = (float) $pod->raw( $field );

            $value = ( $current_value + (float) $value );
        }
        // Date fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::date_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( 0 < strlen( $current_value ) )
                $value = strtotime( $value, strtotime( $current_value ) );
            else
                $value = strtotime( $value );
        }
        // Text fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::text_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( 0 < strlen( $current_value ) )
                $value = $current_value . $value;
        }

        $params = array(
            'pod' => $this->pod,
            'id' => $id,
            'data' => array(
                $field => $value
            )
        );

        $id = $this->api->save_pod_item( $params );

        if ( 0 < $id )
            $pod->fetch( $id );

        return $id;
    }

    /**
     * Remove an item from the values of a relationship field, remove a value from a number field (field-1), remove time to a date field
     *
     * @see PodsAPI::save_pod_item
     *
     * @param string $field Field name
     * @param mixed $value ID(s) to add, int|float to add to number field, string for dates (-1 week), or string for text
     * @param int $id (optional) ID of the pod item to update
     *
     * @return int The item ID
     *
     * @since 2.3.3
     */
    public function remove_from ( $field, $value, $id = null ) {
        $pod =& $this;

        if ( null === $id )
            $id = $this->id();
        elseif ( $id != $this->id() )
            $pod = pods( $this->pod, $id );

        $this->do_hook( 'remove_from', $field, $value, $id );

        if ( !isset( $this->fields[ $field ] ) )
            return $id;

        // Tableless fields
        if ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_array( $value ) )
                $value = explode( ',', $value );

            if ( 'pick' == $this->fields[ $field ][ 'type' ] && in_array( $this->fields[ $field ][ 'pick_object' ], PodsForm::field_method( 'pick', 'simple_objects' ) ) ) {
                $current_value = $pod->raw( $field );

                if ( !empty( $current_value ) )
                    $current_value = (array) $current_value;

                foreach ( $current_value as $k => $v ) {
                    if ( in_array( $v, $value ) )
                        unset( $current_value[ $k ] );
                }

                $value = $current_value;
            }
            else {
                $related_ids = $this->api->lookup_related_items( $this->fields[ $field ][ 'id' ], $this->pod_data[ 'id' ], $id, $this->fields[ $field ], $this->pod_data );

                foreach ( $value as $k => $v ) {
                    $value[ $k ] = (int) $v;
                }

                foreach ( $related_ids as $k => $v ) {
                    if ( in_array( $v, $value ) )
                        unset( $related_ids[ $k ] );
                }

                $value = $related_ids;
            }

            $value = array_filter( array_unique( $value ) );
        }
        // Number fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::number_field_types() ) ) {
            $current_value = (float) $pod->raw( $field );

            $value = ( $current_value - (float) $value );
        }
        // Date fields
        elseif ( in_array( $this->fields[ $field ][ 'type' ], PodsForm::date_field_types() ) ) {
            $current_value = $pod->raw( $field );

            if ( 0 < strlen( $current_value ) )
                $value = strtotime( $value, strtotime( $current_value ) );
            else
                $value = strtotime( $value );
        }

        $params = array(
            'pod' => $this->pod,
            'id' => $id,
            'data' => array(
                $field => $value
            )
        );

        $id = $this->api->save_pod_item( $params );

        if ( 0 < $id )
            $pod->fetch( $id );

        return $id;
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
     * @since 2.0
     * @link http://pods.io/docs/save/
     */
    public function save ( $data = null, $value = null, $id = null ) {
        if ( null !== $value )
            $data = array( $data => $value );

        if ( null === $id )
            $id = $this->id();

        $data = (array) $this->do_hook( 'save', $data, $id );

        if ( empty( $data ) )
            return false;

        $params = array(
            'pod' => $this->pod,
            'id' => $id,
            'data' => $data,
            'allow_custom_fields' => true
        );

        $id = $this->api->save_pod_item( $params );

        if ( 0 < $id )
            $this->fetch( $id );

        return $id;
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
     * @since 2.0
     * @link http://pods.io/docs/delete/
     */
    public function delete ( $id = null ) {
        if ( null === $id )
            $id = $this->id();

        $id = (int) $this->do_hook( 'delete', $id );

        if ( empty( $id ) )
            return false;

        $params = array(
            'pod' => $this->pod,
            'id' => $id
        );

        return $this->api->delete_pod_item( $params );
    }

    /**
     * Reset Pod
     *
     * @see PodsAPI::reset_pod
     *
     * @return bool Whether the Pod was successfully reset
     *
     * @since 2.1.1
     */
    public function reset_pod () {
        $params = array( 'id' => $this->pod_id );

        $this->data->id = null;
        $this->data->row = array();
        $this->data->data = array();

        $this->data->total = 0;
        $this->data->total_found = 0;

        return $this->api->reset_pod( $params );
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
     * @since 2.0
     * @link http://pods.io/docs/duplicate/
     */
    public function duplicate ( $id = null ) {
        if ( null === $id )
            $id = $this->id();

        $id = (int) $this->do_hook( 'duplicate', $id );

        if ( empty( $id ) )
            return false;

        $params = array(
            'pod' => $this->pod,
            'id' => $id
        );

        return $this->api->duplicate_pod_item( $params );
    }

    /**
     * Import data / Save multiple rows of data at once
     *
     * @see PodsAPI::import
     *
     * @param mixed $import_data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     * @param string $format Format of import data, options are php or csv
     *
     * @return array IDs of imported items
     *
     * @since 2.3
     */
    public function import ( $import_data, $numeric_mode = false, $format = null ) {
        return $this->api->import( $import_data, $numeric_mode, $format );
    }

    /**
     * Export an item's data
     *
     * @see PodsAPI::export_pod_item
     *
     * @param array $fields (optional) Fields to export
     * @param int $id (optional) ID of the pod item to export
     *
     * @return array|bool Data array of the exported pod item
     *
     * @since 2.0
     * @link http://pods.io/docs/export/
     */
    public function export ( $fields = null, $id = null, $format = null ) {
        $params = array(
            'pod' => $this->pod,
            'id' => $id,
            'fields' => null,
            'depth' => 2,
            'flatten' => false
        );

        if ( is_array( $fields ) && ( isset( $fields[ 'fields' ] ) || isset( $fields[ 'depth' ] ) ) )
            $params = array_merge( $params, $fields );
        else
            $params[ 'fields' ] = $fields;

        if ( !in_array( $this->pod_data[ 'field_id' ], $params[ 'fields' ] ) )
            $params[ 'fields' ] = array_merge( array( $this->pod_data[ 'field_id' ] ), $params[ 'fields' ] );

        if ( null === $params[ 'id' ] )
            $params[ 'id' ] = $this->id();

        $params = (array) $this->do_hook( 'export', $params );

        if ( empty( $params[ 'id' ] ) )
            return false;

        $data = $this->api->export_pod_item( $params );

        if ( !empty( $format ) ) {
            if ( 'json' == $format )
                $data = json_encode( (array) $data );
        }

        return $data;
    }

    /**
     * Export data from all items
     *
     * @see PodsAPI::export
     *
     * @param array $params An associative array of parameters
     *
     * @return array Data arrays of all exported pod items
     *
     * @since 2.3
     */
    public function export_data ( $params = null ) {
        $defaults = array(
            'fields' => null,
            'depth' => 2,
            'params' => null
        );

        if ( empty( $params ) )
            $params = $defaults;
        else
            $params = array_merge( $defaults, (array) $params );

        return $this->api->export( $this, $params );
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
     * @link http://pods.io/docs/pagination/
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
            'format' => "{$this->page_var}=%#%",
            'class' => "",
            'link_class'
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
     * Return a filter form for searching a Pod
     *
     * @var array|string $params Comma-separated list of fields or array of parameters
     *
     * @return string Filters HTML
     *
     * @since 2.0
     * @link http://pods.io/docs/filters/
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
                    $field[ 'type' ] = 'hidden';

                if ( isset( $object_fields[ $field[ 'name' ] ] ) )
                    $fields[ $field[ 'name' ] ] = array_merge( $object_fields[ $field[ 'name' ] ], $field );
                elseif ( isset( $this->fields[ $field[ 'name' ] ] ) )
                    $fields[ $field[ 'name' ] ] = array_merge( $this->fields[ $field[ 'name' ] ], $field );
            }

            unset( $filter_fields ); // Cleanup
        }

        $this->filters = array_keys( $fields );

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
     * @since 2.0
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
     * @since 2.0
     * @link http://pods.io/docs/template/
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
     * @since 2.0
     * @link http://pods.io/docs/form/
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

            $value = pods_var_raw( 'default', $field );

            if ( empty( $field[ 'name' ] ) )
                $field[ 'name' ] = trim( $name );

            if ( pods_var_raw( 'hidden', $field, false, null, true ) )
                continue;
            elseif ( isset( $object_fields[ $field[ 'name' ] ] ) )
                $fields[ $field[ 'name' ] ] = array_merge( $object_fields[ $field[ 'name' ] ], $field );
            elseif ( isset( $this->fields[ $field[ 'name' ] ] ) )
                $fields[ $field[ 'name' ] ] = array_merge( $this->fields[ $field[ 'name' ] ], $field );

            if ( empty( $this->id ) && null !== $value )
                $this->row_override[ $field[ 'name' ] ] = $value;
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

        if ( empty( $this->id ) )
            $this->row_override = array();

        return $this->do_hook( 'form', $output, $fields, $label, $thank_you, $this, $this->id() );
    }

    /**
     * Replace magic tags with their values
     *
     * @param string $code The content to evaluate
     * @param object $obj The Pods object
     *
     * @return string Code with Magic Tags evaluated
     *
     * @since 2.0
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
     * @return string Code with Magic Tags evaluated
     *
     * @since 2.0.2
     */
    private function process_magic_tags ( $tag ) {
        if ( is_array( $tag ) ) {
            if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 )
                return '';

            $tag = $tag[ 2 ];
        }

        $tag = trim( $tag, ' {@}' );
        $tag = explode( ',', $tag );

        if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 )
            return '';

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

        return '';
    }

    /**
     * Handle filters / actions for the class
     *
     * @see pods_do_hook
     *
     * @return mixed Value filtered
     *
     * @since 2.0
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
     * @since 2.0
     */
    public function __get ( $name ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        $var = null;

        if ( isset( $this->deprecated->{$name} ) ) {
            pods_deprecated( "Pods->{$name}", '2.0' );

            $var = $this->deprecated->{$name};
        }
        else
            pods_deprecated( "Pods->{$name}", '2.0' );

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
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) )
            return call_user_func_array( array( $this->deprecated, $name ), $args );
        else
            pods_deprecated( "Pods::{$name}", '2.0' );
    }
}
