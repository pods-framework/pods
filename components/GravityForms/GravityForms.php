<?php
/**
 * Name: Gravity Forms
 *
 * Description: Integration with Gravity Forms (http://www.gravityforms.com/); Provides a UI for mapping a Form's submissions into a Pod
 *
 * Version: 1.0
 *
 * Developer Mode: on
 *
 * Plugin Dependency: Gravity Forms|gravityforms/gravityforms.php|http://www.gravityforms.com/
 *
 * @package Pods\Components
 * @subpackage gravityforms
 */
class Pods_GravityForms extends PodsComponent {

    /**
     * Gravity Forms Validation array, containing message/error and thank you handling (message/redirect/page)
     *
     * @var array
     */
    static $validation;

    /**
     * New ID of Pod item created by GF Map
     *
     * @var int
     */
    static $new_id;

    /**
     * Length of recurring subscription
     *
     * @var int
     */
    static $paypal_switch_length;

    /**
     * Type of recurring subscription
     *
     * @var string
     */
    static $paypal_switch_type;

    /**
     * To keep or delete files when deleting GF entries
     *
     * @var bool
     */
    static $keep_files;

    /**
     * Array of options for Dynamic Select
     *
     * @var array
     */
    static $dynamic_select;

    /**
     * Setup filters and actions
     */
    public function __construct () {
        // Handle custom success/error message
        add_filter( 'gform_validation_message', array( 'Pods_GravityForms', 'validation_message' ) );
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function admin ( $options ) {
        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        $id = 0;
        $row = array();

        if ( isset( $_GET[ 'id' ] ) )
            $id = (int) $_GET[ 'id' ];

        $pods_forms = array(
            1 => array(
                'id' => 1,
                'pod' => 'book',
                'fields' => array(
                    1 => 'foreword',
                    2 => 'author'
                )
            )
        );

        foreach ( $pods_forms as &$form ) {
            $gf = RGFormsModel::get_form( $form[ 'id' ] );
            if ( empty( $gf ) || !isset( $gf->title ) )
                $gf_title = '<em>N/A</em>';
            else
                $gf_title = $gf->title;

            $form[ 'form' ] = '<a href="/wp-admin/admin.php?page=gf_edit_forms&id=' . $form[ 'id' ] . '">' . $gf_title . '</a> (Form ID: ' . $form[ 'id' ] . ')';

            $pod = pods_api()->load_pod( array( 'name' => $form[ 'pod' ] ) );

            $form[ 'name' ] = $pod[ 'name' ];

            if ( 0 < $id && $id == $form[ 'id' ] )
                $row = $form;
        }

        $ui = array(
            'data' => $pods_forms,
            'total' => count( $pods_forms ),
            'total_found' => count( $pods_forms ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Gravity Forms Mapping',
            'item' => 'Gravity Form Mapping',
            'fields' => array( 'manage' => array( 'name', 'form' ) ),
            'actions_disabled' => array( 'duplicate', 'view', 'export' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_add' ),
                'edit' => array( $this, 'admin_edit' ),
                'delete' => array( $this, 'admin_delete' )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => false,
            'pagination' => false
        );

        if ( 0 < $id )
            $ui[ 'row' ] = $row;

        pods_ui( $ui );
    }

    function admin_add ( $ui ) {
        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        $gravity_forms = RGFormsModel::get_forms( null, 'title' );

        $forms = array();

        foreach ( $gravity_forms as $form ) {
            if ( isset( $pods_forms[ $form->id ] ) )
                continue;

            $forms[ $form->id ] = $form->title . ' (Form ID: ' . $form->id . ')';
        }

        if ( empty( $forms ) )
            $forms = __( 'No Gravity Forms available to map', 'pods' );

        $_pods = pods_api()->load_pods();

        $types = array(
            'pod' => 'Pods',
            'post_type' => __( 'Post Types', 'pods' ),
            'taxonomy' => __( 'Taxonomies', 'pods' ),
            'user' => __( 'Users', 'pods' ),
            'comment' => __( 'Comments', 'pods' )
        );

        $pods = array(
            'Pods' => array(),
            __( 'Post Types', 'pods' ) => array(),
            __( 'Taxonomies', 'pods' ) => array(),
            __( 'Users', 'pods' ) => array(),
            __( 'Comments', 'pods' ) => array()
        );

        foreach ( $_pods as $pod ) {
            $pods[ $types[ $pod[ 'type' ] ] ][ $pod[ 'id' ] ] = $pod[ 'name' ];
        }

        foreach ( $pods as $k => $v ) {
            if ( empty( $v ) )
                unset( $pods[ $k ] );
        }

        pods_view( dirname( __FILE__ ) . '/ui/add.php', get_defined_vars() );
    }

    function admin_edit ( $dup, $ui ) {
        $id = $_GET[ 'id' ];

        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        $gravity_form = RGFormsModel::get_form( $id );

        pods_view( dirname( __FILE__ ) . '/ui/edit.php', get_defined_vars() );
    }

    function admin_delete ( $id, &$ui ) {
        $id = $_GET[ 'id' ];

        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        if ( !isset( $pods_forms[ $id ] ) )
            return $ui->error( __( 'Error: Form mapping not found.', 'pods' ) );

        $gravity_form = RGFormsModel::get_form( $id );

        $name = $pods_forms[ $id ][ 'pod' ];

        if ( !empty( $gravity_form->title ) )
            $name = $gravity_form->title;

        unset( $pods_forms[ $id ] );

        $ui->data = $pods_forms;
        $ui->total = count( $pods_forms );
        $ui->total_found = count( $pods_forms );

        $ui->message( '<strong>' . $name . '</strong> ' . __( 'mapping removed from site.', 'pods' ) );
    }

    function ajax_add ( $params ) {
        if ( !isset( $params->form ) || empty( $params->form ) || !isset( $params->pod ) || empty( $params->pod ) )
            pods_error( __( 'Error: You must select both a Gravity Form and a Pod to map to.', 'pods' ) );

        $params->form = (int) $params->form;

        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        if ( isset( $pods_forms[ $params->form ] ) )
            pods_error( __( 'Error: Form mapping for this Form already exists. Only one is currently allowed.', 'pods' ) );

        $pods_forms[ $params->form ] = array(
            'id' => $params->form,
            'pod' => $params->pod,
            'fields' => array()
        );

        update_option( 'pods_gravity_forms', $pods_forms );

        return $params->form;
    }

    function ajax_edit ( $params ) {
        if ( !isset( $params->form ) || empty( $params->form ) || !isset( $params->pod ) || empty( $params->pod ) )
            pods_error( __( 'Error: You must select both a Gravity Form and a Pod to map to.', 'pods' ) );

        $params->form = (int) $params->form;

        $pods_forms = (array) get_option( 'pods_gravity_forms', array() );

        if ( !isset( $pods_forms[ $params->form ] ) )
            pods_error( __( 'Error: Form mapping not found.', 'pods' ) );

        $fields = array();

        $pods_forms[ $params->form ] = array(
            'id' => $params->form,
            'pod' => $params->pod,
            'fields' => $fields
        );

        update_option( 'pods_gravity_forms', $pods_forms );

        return $params->form;
    }

    /**
     * Intercept validation message and return custom error/message
     *
     * @static
     *
     * @param $message
     * @param null $form
     *
     * @return string
     */
    public static function validation_message ( $message, $form = null ) {
        if ( !is_array( self::$validation ) || !isset( self::$validation[ 'msg' ] ) ) {
            return $message;
        }
        elseif ( false === self::$validation[ 'is_valid' ] ) {
            return '<div class="validation_error gfield_error">' . self::$validation[ 'msg' ] . '</div>';
        }
        return $message;
    }

    /**
     * Detect if there's a valid form already, if there are no errors, return false
     *
     * @static
     *
     * @param array $validation
     *
     * @return bool|array $validation array if error
     */
    public static function validation_error ( &$validation = null ) {
        if ( !empty( $validation ) ) {
            self::$validation =& $validation;
        }
        if ( true !== self::$validation[ 'is_valid' ] ) {
            return self::$validation;
        }
        foreach ( self::$validation[ 'form' ][ 'fields' ] as $field ) {
            if ( isset( $field[ 'failed_validation' ] ) && !empty( $field[ 'failed_validation' ] ) ) {
                return self::$validation;
            }
            if ( isset( $field[ 'validation_message' ] ) && !empty( $field[ 'validation_message' ] ) ) {
                return self::$validation;
            }
        }
        return false;
    }

    /**
     * Update Gravity Forms $validation array to redirect upon success
     *
     * @static
     *
     * @param string $url URL to redirect to
     * @param array $validation GF Validation array
     * @param string $queryString Optional queryString to add to redirect
     *
     * @return array GF Validation array
     */
    public static function redirect ( $url, &$validation = null, $queryString = null ) {
        if ( !empty( $validation ) ) {
            self::$validation =& $validation;
        }
        self::$validation[ 'confirmation' ][ 'type' ] = 'redirect';
        self::$validation[ 'confirmation' ][ 'url' ] = $url;
        if ( !empty( $queryString ) ) {
            self::$validation[ 'confirmation' ][ 'queryString' ] = $queryString;
        }
        return self::$validation;
    }

    /**
     * Update Gravity Forms $validation array to show message upon success
     *
     * @static
     *
     * @param string $msg Message to show
     * @param array $validation GF Validation array
     *
     * @return array GF Validation array
     */
    public static function message ( $msg, &$validation = null ) {
        if ( !empty( $validation ) ) {
            self::$validation =& $validation;
        }
        self::$validation[ 'confirmation' ][ 'type' ] = 'message';
        self::$validation[ 'confirmation' ][ 'message' ] = $msg;
        return self::$validation;
    }

    /**
     * Update Gravity Forms $validation array to show error message
     *
     * @static
     *
     * @param string $msg Message to show
     * @param array $validation GF Validation array
     *
     * @return array GF Validation array
     */
    public static function error ( $msg, &$validation = null ) {
        if ( !empty( $validation ) ) {
            self::$validation =& $validation;
        }
        self::$validation[ 'is_valid' ] = false;
        self::$validation[ 'msg' ] = $msg;
        return self::$validation;
    }

    /**
     * Setup a faux $entry field value array like you would get during post-processing,
     * making those values available during pre-processing
     *
     * @static
     *
     * @param array $validation GF Validation array
     *
     * @return array|bool false if failed to detect necessary info
     */
    public static function setup_entry ( $validation = null ) {
        if ( !empty( $validation ) ) {
            self::$validation =& $validation;
        }
        if ( !is_array( self::$validation ) ) {
            return false;
        }
        if ( !isset( $validation[ 'form' ] ) ) {
            if ( isset( $validation[ 'fields' ] ) ) {
                $fields = $validation[ 'fields' ];
            }
            else {
                return false;
            }
        }
        else {
            $fields = $validation[ 'form' ][ 'fields' ];
        }
        $entry = array();

        foreach ( $fields as &$field ) {
            $input_name = 'input_' . $field[ 'id' ];
            $is_hidden = false;
            $form_id = 0;
            if ( isset( $validation[ 'form' ] ) ) {
                $is_hidden = RGFormsModel::is_field_hidden( $validation[ 'form' ], $field, array() );
                $form_id = $validation[ 'form' ][ 'id' ];
            }
            $value = '';
            if ( !$is_hidden ) {
                if ( 'fileupload' == $field[ 'type' ] ) {
                    global $_gf_uploaded_files;
                    $value = rgget( $input_name, $_gf_uploaded_files );
                    if ( empty( $value ) && empty( $_gf_uploaded_files ) && isset( $_POST[ 'gform_uploaded_files' ] ) && !empty( $_POST[ 'gform_uploaded_files' ] ) ) {
                        $files = GFCommon::json_decode( stripslashes( RGForms::post( "gform_uploaded_files" ) ) );
                        if ( isset( $files[ $input_name ] ) ) {
                            $value = $files[ $input_name ];
                        }
                    }
                }
                else {
                    $value = array();

                    if ( isset( $_POST[ $input_name ] ) ) {
                        $v = rgpost( $input_name );
                        if ( is_array( $v ) ) {
                            $v = implode( ',', $v );
                        }
                        $value[] = $v;
                    }
                    $max = 10;
                    for ( $x = 1; $x <= 10; $x++ ) {
                        if ( isset( $_POST[ $input_name . '_' . $x ] ) ) {
                            $v = rgpost( $input_name . '_' . $x );
                            if ( is_array( $v ) ) {
                                $v = implode( ',', $v );
                            }
                            $value[] = $v;
                        }
                    }
                }
                if ( is_array( $value ) ) {
                    $value = implode( '|', $value );
                }
            }
            $entry[ $field[ 'id' ] ] = $value;
        }
        return $entry;
    }

    /**
     * Setup GF to auto-delete the entry it's about to create
     *
     * @static
     *
     * @param int $form_id GF Form ID
     * @param bool $keep_files To keep or delete files when deleting GF entries
     */
    public static function auto_delete ( $form_id = null, $keep_files = null ) {
        if ( empty( $form_id ) && is_array( self::$validation ) )
            $form_id = self::$validation[ 'form_id' ];

        if ( null !== $keep_files )
            self::$keep_files = (boolean) $keep_files;

        add_action( 'gform_post_submission' . ( empty( $form_id ) ? '' : '_' . (int) $form_id ), array( get_class(), 'delete_entry' ), 20, 1 );
    }

    /**
     * Delete GF entry that's just been created
     *
     * @static
     *
     * @param array|int $entry GF entry array or ID
     * @param bool $keep_files To keep or delete files when deleting GF entries, falls back to self::$keep_files
     *
     * @return bool Returns false if $entry is invalid
     */
    public static function delete_entry ( $entry, $keep_files = false ) {
        global $wpdb;

        if ( !is_array( $entry ) && 0 < (int) $entry )
            $lead_id = (int) $entry;
        elseif ( is_array( $entry ) && isset( $entry[ 'id' ] ) && 0 < (int) $entry[ 'id' ] )
            $lead_id = (int) $entry[ 'id' ];
        else
            return false;

        if ( null !== self::$keep_files )
            $keep_files = (boolean) self::$keep_files;

        do_action( "gform_delete_lead", $lead_id );

        $lead_table = RGFormsModel::get_lead_table_name();
        $lead_notes_table = RGFormsModel::get_lead_notes_table_name();
        $lead_detail_table = RGFormsModel::get_lead_details_table_name();
        $lead_detail_long_table = RGFormsModel::get_lead_details_long_table_name();

        if ( !$keep_files ) {
            RGFormsModel::delete_files( $lead_id );
        }

        //Delete from detail long
        $sql = $wpdb->prepare( "DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE lead_id=%d
                                )", $lead_id );
        $wpdb->query( $sql );

        //Delete from lead details
        $sql = $wpdb->prepare( "DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id );
        $wpdb->query( $sql );

        //Delete from lead notes
        $sql = $wpdb->prepare( "DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id );
        $wpdb->query( $sql );

        //Delete from lead meta
        gform_delete_meta( $lead_id );

        //Delete from lead
        $sql = $wpdb->prepare( "DELETE FROM $lead_table WHERE id=%d", $lead_id );
        $wpdb->query( $sql );
    }

    /**
     * Switch the length or type out for another value during processing before user is sent to PayPal
     *
     * @static
     *
     * @param $form_id Form ID to switch out on
     * @param int $length Length of recurring subscription
     * @param string $type Type of recurring subscription
     */
    public static function paypal_switch_sub ( $form_id = null, $length = null, $type = null ) {
        if ( empty( $form_id ) && is_array( self::$validation ) )
            $form_id = self::$validation[ 'form_id' ];

        if ( null !== $length ) {
            self::$paypal_switch_length = max( 1, (int) $length );
            add_filter( 'gform_paypal_query_' . $form_id, array( get_class(), 'paypal_switch_length' ), 10, 1 );
        }

        if ( null !== $type ) {
            self::$paypal_switch_type = strtoupper( (string) $type );
            if ( !in_array( self::$paypal_switch_type, array( 'D', 'W', 'M', 'Y' ) ) ) {
                self::$paypal_switch_type = 'M';
            }
            add_filter( 'gform_paypal_query_' . $form_id, array( get_class(), 'paypal_switch_type' ), 10, 1 );
        }
    }

    /**
     * Switch the length out for another value during processing before user is sent to PayPal
     *
     * @static
     *
     * @param string $query PayPal Query from Gravity Forms
     *
     * @return string
     */
    public static function paypal_switch_length ( $query ) {
        self::$paypal_switch_length = max( 1, (int) self::$paypal_switch_length );
        return preg_replace( '/\&p3\=(\d*)\&/i', '&p3=' . self::$paypal_switch_length . '&', $query );
    }

    /**
     * Switch the type out for another value during processing before user is sent to PayPal
     *
     * @static
     *
     * @param string $query PayPal Query from Gravity Forms
     *
     * @return string
     */
    public static function paypal_switch_type ( $query ) {
        self::$paypal_switch_type = strtoupper( (string) self::$paypal_switch_type );
        if ( !in_array( self::$paypal_switch_type, array( 'D', 'W', 'M', 'Y' ) ) )
            self::$paypal_switch_type = 'M';
        return preg_replace( '/\&t3\=(\w*)\&/i', '&t3=' . self::$paypal_switch_type . '&', $query );
    }

    /**
     * Auto-login a user upon registration (using GF User Registration add-on)
     *
     * @static
     */
    public static function auto_login () {
        add_action( 'gform_user_registered', array( get_class(), 'user_auto_login' ), 10, 4 );
    }

    /**
     * Auto-login a user upon registration (using GF User Registration add-on)
     *
     * @static
     *
     * @param int $user_id User ID
     * @param string $user_config
     * @param array $entry
     * @param string $password User password
     *
     * @return object
     */
    public static function user_auto_login ( $user_id, $user_config, $entry, $password ) {
        $user = get_userdata( $user_id );
        return wp_signon( array(
            'user_login' => $user->user_login,
            'user_password' => $password,
            'remember' => false
        ) );
    }

    /**
     * Set a field's values to be dynamically pulled from a Pod
     *
     * @static
     *
     * @param int $form_id GF Form ID
     * @param int $field_id GF Field ID
     * @param string $pod_name Pod name
     * @param string $label_column_name Column name to use for label text
     * @param array $params Additional parameters for findRecords
     */
    public static function dynamic_select ( $form_id, $field_id, $pod_name, $label_column_name = 'name', $params = null ) {
        self::$dynamic_select = array(
            'form' => $form_id,
            'field' => $field_id,
            'pod' => $pod_name,
            'label' => $label_column_name,
            'params' => $params
        );

        add_filter( 'gform_pre_render', array( get_class(), 'add_dynamic_select' ), 10, 1 );
    }

    /**
     * Set a field's values to be dynamically pulled from a Pod
     *
     * @static
     *
     * @param $form
     * @param int $form_id GF Form ID
     * @param int $field_id GF Field ID
     * @param string $pod_name Pod name
     * @param string $label_column_name Column name to use for label text
     * @param array $params Additional parameters for findRecords
     *
     * @return array
     */
    public static function add_dynamic_select ( $form, $form_id = null, $field_id = null, $pod_name = null, $label_column_name = null, $params = null ) {
        $defaults = array(
            'orderby' => 't.name',
            'limit' => -1,
            'search' => false,
            'pagination' => false
        );

        if ( null === $field_id && empty( self::$dynamic_select ) )
            return $form;

        if ( null === $form_id )
            $form_id = self::$dynamic_select[ 'form' ];

        if ( 0 < $form_id && $form[ 'id' ] != (int) $form_id )
            return $form;

        if ( null === $field_id )
            $field_id = self::$dynamic_select[ 'field' ];

        if ( null === $pod_name )
            $pod_name = self::$dynamic_select[ 'pod' ];

        if ( null === $label_column_name )
            $label_column_name = self::$dynamic_select[ 'label' ];

        if ( null === $params )
            $params = self::$dynamic_select[ 'params' ];

        if ( is_array( $params ) )
            $params = array_merge( $defaults, $params );
        else
            $params = $defaults;

        $pod = new Pod( $pod_name, $params );

        foreach ( $form[ 'fields' ] as &$field ) {
            if ( $field_id == $field[ 'id' ] ) {
                $field[ 'enableChoiceValue' ] = true; // allow value=>label

                if ( isset( $params[ 'defaultValue' ] ) )
                    $field[ 'defaultValue' ] = $params[ 'defaultValue' ];

                $choices = array();
                while ( $pod->fetchRecord() ) {
                    $choices[] = array(
                        'text' => $pod->get_field( $label_column_name ),
                        'value' => $pod->get_field( 'id' )
                    );
                }
                $field[ 'choices' ] = apply_filters( 'pods_gf_dynamic_select_choices', $choices, $field, $form, $pod, $label_column_name, $params );
            }
        }

        return $form;
    }

    /**
     * Map a GF entry to a new Pod item
     *
     * @static
     *
     * @param array $entry Gravity Forms entry array
     * @param string $pod Pod name
     * @param array $mapping Custom mapping of Pod columns
     * @param int $id ID of pod item (if already exists)
     * @param bool $related
     * @param bool $keep
     * @param bool $keep_files
     * @param bool $bypass_helpers
     *
     * @return bool|int|null|string|void
     */
    public static function map ( $entry, $pod = null, $mapping = null, $id = null, $related = false, $keep = true, $keep_files = true, $bypass_helpers = false ) {
        if ( null !== $pod && !empty( $mapping ) ) {
            global $pods_cache;
            $pods_cache->cache_enabled = false;
            $related_strict = false;
            if ( empty( $id ) ) {
                // create new record but only relate if $pod != $the_pod
                $api = new PodAPI( $pod );
                $api->snap = true;
                $related_strict = true;
                try {
                    $id = $api->save_pod_item( array(
                        'datatype' => $pod,
                        'bypass_helpers' => $bypass_helpers,
                        'columns' => array( 'name' => 1 )
                    ) );
                }
                catch ( Exception $e ) {
                    return $e->getMessage();
                }
            }
            $fields = array();
            foreach ( $mapping as $field_id => $map ) {
                if ( !isset( $entry[ $field_id ] ) && !isset( $entry[ $field_id . '.1' ] ) && false === strpos( $field_id, 'empty' ) ) {
                    continue;
                }
                $value = '';
                if ( false === strpos( $field_id, 'empty' ) && isset( $entry[ $field_id ] ) ) {
                    $value = $entry[ $field_id ];
                }
                $etc = $map;
                if ( is_array( $etc ) && !isset( $etc[ 'field' ] ) ) {
                    continue;
                }
                elseif ( is_array( $etc ) && isset( $etc[ 'field' ] ) ) {
                    $map = $etc[ 'field' ];
                }
                $map = trim( $map );
                $the_pod = $pod;
                if ( is_array( $etc ) && isset( $etc[ 'pod' ] ) ) {
                    $the_pod = $etc[ 'pod' ];
                }
                if ( empty( $value ) && isset( $etc[ 'default' ] ) ) {
                    $value = $etc[ 'default' ];
                }
                if ( empty( $value ) && isset( $etc[ 'default_copy' ] ) ) {
                    if ( is_array( $etc[ 'default_copy' ] ) ) {
                        $copy_pod = $pod;
                        if ( isset( $etc[ 'default_copy' ][ 'pod' ] ) ) {
                            $copy_pod = $etc[ 'default_copy' ][ 'pod' ];
                        }
                        $copy_field = $map;
                        if ( isset( $etc[ 'default_copy' ][ 'field' ] ) ) {
                            $copy_field = $etc[ 'default_copy' ][ 'field' ];
                        }
                        if ( isset( $fields[ $copy_pod ] ) && isset( $fields[ $copy_pod ][ $copy_field ] ) ) {
                            $value = $fields[ $copy_pod ][ $copy_field ];
                        }
                    }
                    elseif ( isset( $fields[ $the_pod ] ) && isset( $fields[ $the_pod ][ $etc[ 'default_copy' ] ] ) ) {
                        $value = $fields[ $the_pod ][ $etc[ 'default_copy' ] ];
                    }
                    elseif ( isset( $fields[ $pod ] ) && isset( $fields[ $pod ][ $etc[ 'default_copy' ] ] ) ) {
                        $value = $fields[ $pod ][ $etc[ 'default_copy' ] ];
                    }
                }
                $bool = false;
                if ( is_array( $etc ) && isset( $etc[ 'bool' ] ) ) {
                    $bool = $etc[ 'bool' ];
                }
                if ( false !== $bool && ( !isset( $etc[ 'bypass_value_set' ] ) || false === $etc[ 'bypass_value_set' ] ) ) {
                    if ( empty( $value ) && isset( $entry[ $field_id . '.1' ] ) ) {
                        $value = $entry[ $field_id . '.1' ];
                    }
                    if ( is_bool( $bool ) && true === $bool ) {
                        if ( 0 < strlen( $value ) ) {
                            $value = 1;
                        }
                        else {
                            $value = 0;
                        }
                    }
                    elseif ( $value == $bool ) {
                        $value = 1;
                    }
                    else {
                        $value = 0;
                    }
                }
                $api = new PodAPI( $the_pod );
                $api->snap = true;
                if ( !isset( $api->fields[ $map ] ) ) {
                    continue;
                }
                if ( 'pick' == $api->fields[ $map ][ 'coltype' ] && ( 'wp_user' != $api->fields[ $map ][ 'pickval' ] || !is_numeric( $value ) ) && ( !isset( $etc[ 'bypass_value_set' ] ) || false === $etc[ 'bypass_value_set' ] ) ) {
                    $values = explode( '|', $value );
                    $field_counter = 1;
                    while ( isset( $entry[ $field_id . '.' . $field_counter ] ) ) {
                        $entry[ $field_id . '.' . $field_counter ] = trim( $entry[ $field_id . '.' . $field_counter ] );
                        if ( 0 < strlen( $entry[ $field_id . '.' . $field_counter ] ) ) {
                            $values[] = $entry[ $field_id . '.' . $field_counter ];
                        }
                        $field_counter++;
                    }
                    $values = array_filter( array_unique( $values ) );
                    foreach ( $values as $k => $v ) {
                        unset( $values[ $k ] );
                        if ( 'wp_user' == $api->fields[ $map ][ 'pickval' ] ) {
                            $user = get_user_by( 'email', $v );
                            if ( !( is_object( $user ) && isset( $user->ID ) && 0 < $user->ID ) ) {
                                $user = get_user_by( 'login', $v );
                            }
                            if ( is_object( $user ) && isset( $user->ID ) && 0 < $user->ID ) {
                                $values[ $k ] = $user->ID;
                            }
                        }
                        else {
                            $lookup = new Pod( $api->fields[ $map ][ 'pickval' ] );
                            $lookup->findRecords( array(
                                'orderby' => '(t.name = "' . pods_sanitize( $v ) . '") DESC,(t.name = "' . pods_sanitize( trim( $v, '.' ) ) . '") DESC,t.id',
                                'limit' => 1,
                                'where' => 't.name = "' . pods_sanitize( $v ) . '" OR t.name = "' . pods_sanitize( trim( $v, '.' ) ) . '" OR t.name LIKE "%' . pods_sanitize( $v ) . '%" OR t.name LIKE "%' . pods_sanitize( trim( $v, '.' ) ) . '%" OR t.id = ' . intval( $v ),
                                'search' => false,
                                'page' => 1
                            ) );
                            if ( $lookup->fetchRecord() ) {
                                $values[ $k ] = $lookup->get_field( 'id' );
                            }
                        }
                    }
                    try {
                        $api->fields[ $map ] = $api->load_column( array( 'id' => $api->fields[ $map ][ 'id' ] ) );
                    }
                    catch ( Exception $e ) {
                        return $e->getMessage();
                    }
                    if ( 1 != $api->fields[ $map ][ 'multiple' ] && !empty( $values ) ) {
                        $value = current( $values );
                    }
                    else {
                        $value = implode( ',', $values );
                    }
                }
                elseif ( 'num' == $api->fields[ $map ][ 'coltype' ] ) {
                    $value = (int) $value;
                }
                else {
                    $value = trim( $value );
                }
                if ( !isset( $fields[ $the_pod ] ) ) {
                    $fields[ $the_pod ] = array();
                }
                if ( !isset( $fields[ $the_pod ][ $map ] ) || empty( $fields[ $the_pod ][ $map ] ) ) {
                    $fields[ $the_pod ][ $map ] = $value;
                }
            }
            $ids = array();
            foreach ( $fields as $the_pod => $columns ) {
                $api = new PodAPI( $the_pod );
                $api->snap = true;
                $params = array(
                    'datatype' => $the_pod,
                    'bypass_helpers' => $bypass_helpers,
                    'columns' => $columns
                );
                if ( !isset( $ids[ $the_pod ] ) ) {
                    $ids[ $the_pod ] = 0;
                }
                if ( !empty( $id ) && ( ( false === $related && false === $related_strict ) || $the_pod == $pod ) ) {
                    $params[ 'tbl_row_id' ] = $id;
                }
                if ( !empty( $id ) && false !== $related && !is_array( $related ) && ( false === $related_strict || $the_pod != $pod ) ) {
                    $params[ 'columns' ][ $related ] = $id;
                }
                try {
                    $save_id = $api->save_pod_item( pods_sanitize( $params ) );
                }
                catch ( Exception $e ) {
                    return $e->getMessage();
                }
                if ( 0 < $save_id ) {
                    $ids[ $the_pod ] = $save_id;
                }
            }
            if ( false !== $related && is_array( $related ) ) {
                $api = new PodAPI( $pod );
                $api->snap = true;
                $params = array(
                    'datatype' => $pod,
                    'bypass_helpers' => $bypass_helpers,
                    'columns' => array()
                );
                if ( !empty( $id ) && false === $related_strict ) {
                    $params[ 'tbl_row_id' ] = $id;
                }
                foreach ( $ids as $the_pod => $the_id ) {
                    if ( !isset( $related[ $the_pod ] ) ) {
                        continue;
                    }
                    $related_field = $related[ $the_pod ];
                    $params[ 'columns' ][ $related_field ] = $the_id;
                }
                try {
                    $id = $api->save_pod_item( pods_sanitize( $params ) );
                }
                catch ( Exception $e ) {
                    return $e->getMessage();
                }
            }
            if ( true !== $keep ) {
                self::delete_entry( $entry, $keep_files );
            }
            self::$new_id = $id;
            return $id;
        }
        return false;
    }
}
