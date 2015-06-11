<?php
/**
 * @package Pods\Fields
 */
class PodsField_File extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Relationships / Media';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'file';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'File / Image / Video';

    /**
     * API caching for fields that need it during validate/save
     *
     * @var \PodsAPI
     * @since 2.3
     */
    protected static $api = false;

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {

    }

    /**
     * Add admin_init actions
     *
     * @since 2.3
     */
    public function admin_init() {
        // AJAX for Uploads
        add_action( 'wp_ajax_pods_upload', array( $this, 'admin_ajax_upload' ) );
        add_action( 'wp_ajax_nopriv_pods_upload', array( $this, 'admin_ajax_upload' ) );
    }

    /**
     * Add options and set defaults to
     *
     * @param array $options
     *
     * @since 2.0
     */
    public function options () {
        $sizes = get_intermediate_image_sizes();

        $image_sizes = array();

        foreach ( $sizes as $size ) {
            $image_sizes[ $size ] = ucwords( str_replace( '-', ' ', $size ) );
        }

        $options = array(
            self::$type . '_format_type' => array(
                'label' => __( 'Upload Limit', 'pods' ),
                'default' => 'single',
                'type' => 'pick',
                'data' => array(
                    'single' => __( 'Single File', 'pods' ),
                    'multi' => __( 'Multiple Files', 'pods' )
                ),
                'dependency' => true
            ),
            self::$type . '_uploader' => array(
                'label' => __( 'File Uploader', 'pods' ),
                'default' => 'attachment',
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_file_uploader_options',
                    array(
                        'attachment' => __( 'Attachments (WP Media Library)', 'pods' ),
                        'plupload' => __( 'Plupload', 'pods' )
                    )
                ),
                'dependency' => true
            ),
            self::$type . '_attachment_tab' => array(
                'label' => __( 'Attachments Default Tab', 'pods' ),
                'depends-on' => array( self::$type . '_uploader' => 'attachment' ),
                'default' => 'upload',
                'type' => 'pick',
                'data' => array(
                    // keys MUST match WP's router names
                    'upload' => __( 'Upload File', 'pods' ),
                    'browse' => __( 'Media Library', 'pods' )
                )
            ),
            self::$type . '_edit_title' => array(
                'label' => __( 'Editable Title', 'pods' ),
                'default' => 1,
                'type' => 'boolean'
            ),
            self::$type . '_linked' => array(
                'label' => __( 'Link to File in editor', 'pods' ),
                'default' => 0,
                'type' => 'boolean'
            ),
            self::$type . '_limit' => array(
                'label' => __( 'Max Number of Files', 'pods' ),
                'depends-on' => array( self::$type . '_format_type' => 'multi' ),
                'default' => 0,
                'type' => 'number'
            ),
            self::$type . '_restrict_filesize' => array(
                'label' => __( 'Restrict File Size', 'pods' ),
                'depends-on' => array( self::$type . '_uploader' => 'plupload' ),
                'default' => '10MB',
                'type' => 'text'
            ),
            self::$type . '_type' => array(
                'label' => __( 'Restrict File Types', 'pods' ),
                'default' => apply_filters( 'pods_form_ui_field_file_type_default', 'images' ),
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_file_type_options',
                    array(
                        'images' => __( 'Images (jpg, jpeg, png, gif)', 'pods' ),
                        'video' => __( 'Video (mpg, mov, flv, mp4, etc..)', 'pods' ),
                        'audio' => __( 'Audio (mp3, m4a, wav, wma, etc..)', 'pods' ),
                        'text' => __( 'Text (txt, csv, tsv, rtx, etc..)', 'pods' ),
                        'any' => __( 'Any Type (no restriction)', 'pods' ),
                        'other' => __( 'Other (customize allowed extensions)', 'pods' )
                    )
                ),
                'dependency' => true
            ),
            self::$type . '_allowed_extensions' => array(
                'label' => __( 'Allowed File Extensions', 'pods' ),
                'description' => __( 'Separate file extensions with a comma (ex. jpg,png,mp4,mov)', 'pods' ),
                'depends-on' => array( self::$type . '_type' => 'other' ),
                'default' => apply_filters( 'pods_form_ui_field_file_extensions_default', '' ),
                'type' => 'text'
            ),/*
            self::$type . '_image_size' => array(
                'label' => __( 'Excluded Image Sizes', 'pods' ),
                'description' => __( 'Image sizes not to generate when processing the image', 'pods' ),
                'depends-on' => array( self::$type . '_type' => 'images' ),
                'default' => 'images',
                'type' => 'pick',
                'pick_format_type' => 'multi',
                'pick_format_multi' => 'checkbox',
                'data' => apply_filters(
                    'pods_form_ui_field_file_image_size_options',
                    $image_sizes
                )
            ),*/
            self::$type . '_add_button' => array(
                'label' => __( 'Add Button Text', 'pods' ),
                'default' => __( 'Add File', 'pods' ),
                'type' => 'text'
            ),
            self::$type . '_modal_title' => array(
                'label' => __( 'Modal Title', 'pods' ),
                'depends-on' => array( self::$type . '_uploader' => 'attachment' ),
                'default' => __( 'Attach a file', 'pods' ),
                'type' => 'text'
            ),
            self::$type . '_modal_add_button' => array(
                'label' => __( 'Modal Add Button Text', 'pods' ),
                'depends-on' => array( self::$type . '_uploader' => 'attachment' ),
                'default' => __( 'Add File', 'pods' ),
                'type' => 'text'
            )
        );

        if ( !pods_version_check( 'wp', '3.5' ) ) {
            unset( $options[ self::$type . '_linked' ] );
            unset( $options[ self::$type . '_modal_title' ] );
            unset( $options[ self::$type . '_modal_add_button' ] );

            $options[ self::$type . '_attachment_tab' ][ 'default' ] = 'type';
            $options[ self::$type . '_attachment_tab' ][ 'data' ] = array(
                'type' => __( 'Upload File', 'pods' ),
                'library' => __( 'Media Library', 'pods' )
            );
        }

        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $schema = false;

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        if ( is_array( $value ) && !empty( $value ) ) {
            if ( isset( $value[ 'ID' ] ) )
                $value = wp_get_attachment_url( $value[ 'ID' ] );
            else {
                $attachments = $value;
                $value = array();

                foreach ( $attachments as $v ) {
                    if ( !is_array( $v ) )
                        $value[] = $v;
                    elseif ( isset( $v[ 'ID' ] ) )
                        $value[] = wp_get_attachment_url( $v[ 'ID' ] );
                }

                $value = implode( ' ', $value );
            }
        }

        return $value;
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        if ( !is_admin() ) {
            include_once( ABSPATH . '/wp-admin/includes/template.php' );

            if ( is_multisite() )
                include_once( ABSPATH . '/wp-admin/includes/ms.php' );
        }

        if ( ( ( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD )
               || ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in() )
               || ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && !is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && ( !is_user_logged_in() || !current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) ) )
             && ( ( defined( 'PODS_DISABLE_FILE_BROWSER' ) && true === PODS_DISABLE_FILE_BROWSER )
                  || ( defined( 'PODS_FILES_REQUIRE_LOGIN' ) && is_bool( PODS_FILES_REQUIRE_LOGIN ) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in() )
                  || ( defined( 'PODS_FILES_REQUIRE_LOGIN' ) && !is_bool( PODS_FILES_REQUIRE_LOGIN ) && ( !is_user_logged_in() || !current_user_can( PODS_FILES_REQUIRE_LOGIN ) ) ) )
        ) {
            ?>
        <p>You do not have access to upload / browse files. Contact your website admin to resolve.</p>
        <?php
            return;
        }

        // Use plupload if attachment isn't available
        if ( 'attachment' == pods_var( self::$type . '_uploader', $options ) && ( !is_user_logged_in() || ( !current_user_can( 'upload_files' ) && !current_user_can( 'edit_files' ) ) ) )
            $field_type = 'plupload';
        elseif ( 'plupload' == pods_var( self::$type . '_uploader', $options ) )
            $field_type = 'plupload';
        elseif ( 'attachment' == pods_var( self::$type . '_uploader', $options ) ) {
            if ( !pods_version_check( 'wp', '3.5' ) || !is_admin() ) // @todo test frontend media modal
                $field_type = 'attachment';
            else
                $field_type = 'media';
        }
        else {
            // Support custom File Uploader integration
            do_action( 'pods_form_ui_field_file_uploader_' . pods_var( self::$type . '_uploader', $options ), $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_file_uploader', pods_var( self::$type . '_uploader', $options ), $name, $value, $options, $pod, $id );
            return;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Build regex necessary for JS validation
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @return bool
     * @since 2.0
     */
    public function regex ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        return false;
    }

    /**
     * Validate a value before it's saved
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     * @param null $params
     *
     * @return bool
     * @since 2.0
     */
    public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        // check file size
        // check file extensions
        return true;
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @return mixed
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        return $value;
    }

    /**
     * Save the value to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.3
     */
    public function save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        if ( empty( self::$api ) )
            self::$api = pods_api();

        // File title / field handling
        foreach ( $value as $id ) {
            $title = false;

            if ( is_array( $id ) ) {
                if ( isset( $id[ 'title' ] ) && 0 < strlen( trim( $id[ 'title' ] ) ) )
                    $title = trim( $id[ 'title' ] );

                if ( isset( $id[ 'id' ] ) )
                    $id = (int) $id[ 'id' ];
                else
                    $id = 0;
            }

            if ( empty( $id ) )
                continue;

            // Update the title if set
            if ( false !== $title && 1 == pods_var( self::$type . '_edit_title', $options, 0 ) ) {
                $attachment_data = array(
                    'ID' => $id,
                    'post_title' => $title
                );

                self::$api->save_wp_object( 'media', $attachment_data );
            }
        }
    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @return mixed|void
     * @since 2.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        if ( empty( $value ) )
            return;

        if ( !empty( $value ) && isset( $value[ 'ID' ] ) )
            $value = array( $value );

        $image_size = apply_filters( 'pods_form_ui_field_file_ui_image_size', 'thumbnail', $id, $value, $name, $options, $pod );

        return $this->images( $id, $value, $name, $options, $pod, $image_size );
    }

    /**
     * Return image(s) markup
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param string $image_size
     *
     * @return string
     * @since 2.3
     */
    public function images ( $id, $value, $name = null, $options = null, $pod = null, $image_size = null ) {
        $images = '';

        if ( empty( $value ) || !is_array( $value ) )
            return $images;

        foreach ( $value as $v ) {
            $images .= pods_image( $v, $image_size );
        }

        return $images;
    }

    /**
     * Handle file row output for uploaders
     *
     * @param array $attributes
     * @param int $limit
     * @param bool $editable
     * @param int $id
     * @param string $icon
     * @param string $name
     *
     * @return string
     * @since 2.0
     */
    public function markup ( $attributes, $limit = 1, $editable = true, $id = null, $icon = null, $name = null, $linked = false, $link = null ) {
        // Preserve current file type
        $field_type = PodsForm::$field_type;

        ob_start();

        if ( empty( $id ) )
            $id = '{{id}}';

        if ( empty( $icon ) ) {
	        $icon = '{{icon}}';
        }else{
	        $icon = esc_url( $icon );
        }


        if ( empty( $name ) )
            $name = '{{name}}';

        if ( empty( $link ) )
            $link = '{{link}}';

        $editable = (boolean) $editable;
        $linked = (boolean) $linked;
        ?>
    <li class="pods-file hidden" id="pods-file-<?php echo esc_attr( $id ); ?>">
        <?php echo PodsForm::field( $attributes[ 'name' ] . '[' . $id . '][id]', $id, 'hidden' ); ?>

        <ul class="pods-file-meta media-item">
            <?php if ( 1 != $limit ) { ?>
                <li class="pods-file-col pods-file-handle">Handle</li>
            <?php } ?>

            <li class="pods-file-col pods-file-icon">
                <img class="pinkynail" src="<?php echo $icon; ?>" alt="Icon" />
            </li>

            <li class="pods-file-col pods-file-name">
                <?php
                if ( $editable )
                    echo PodsForm::field( $attributes[ 'name' ] . '[' . $id . '][title]', $name, 'text' );
                else
                    echo ( empty( $name ) ? '{{name}}' : $name );
                ?>
            </li>

            <li class="pods-file-col pods-file-delete"><a href="#delete">Delete</a></li>

			<?php
				if ( $linked ) {
			?>
            	<li class="pods-file-col pods-file-download"><a href="<?php echo esc_url( $link ); ?>" target="_blank">Download</a></li>
			<?php
				}
			?>
        </ul>
    </li>
    <?php
        PodsForm::$field_type = $field_type;

        return ob_get_clean();
    }

    /**
     * Handle plupload AJAX
     *
     * @since 2.3
     */
    public function admin_ajax_upload () {
		pods_session_start();

        // Sanitize input
        $params = pods_unslash( (array) $_POST );

        foreach ( $params as $key => $value ) {
            if ( 'action' == $key )
                continue;

            unset( $params[ $key ] );

            $params[ str_replace( '_podsfix_', '', $key ) ] = $value;
        }

        $params = (object) $params;

        $methods = array(
            'upload',
        );

        if ( !isset( $params->method ) || !in_array( $params->method, $methods ) || !isset( $params->pod ) || !isset( $params->field ) || !isset( $params->uri ) || empty( $params->uri ) )
            pods_error( 'Invalid AJAX request', PodsInit::$admin );
        elseif ( !empty( $params->pod ) && empty( $params->field ) )
            pods_error( 'Invalid AJAX request', PodsInit::$admin );
        elseif ( empty( $params->pod ) && !current_user_can( 'upload_files' ) )
            pods_error( 'Invalid AJAX request', PodsInit::$admin );

        // Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
        if ( is_ssl() && empty( $_COOKIE[ SECURE_AUTH_COOKIE ] ) && !empty( $_REQUEST[ 'auth_cookie' ] ) )
            $_COOKIE[ SECURE_AUTH_COOKIE ] = $_REQUEST[ 'auth_cookie' ];
        elseif ( empty( $_COOKIE[ AUTH_COOKIE ] ) && !empty( $_REQUEST[ 'auth_cookie' ] ) )
            $_COOKIE[ AUTH_COOKIE ] = $_REQUEST[ 'auth_cookie' ];

        if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) && !empty( $_REQUEST[ 'logged_in_cookie' ] ) )
            $_COOKIE[ LOGGED_IN_COOKIE ] = $_REQUEST[ 'logged_in_cookie' ];

        global $current_user;
        unset( $current_user );

        /**
         * Access Checking
         */
        $upload_disabled = false;

        if ( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD )
            $upload_disabled = true;
        elseif ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in() )
            $upload_disabled = true;
        elseif ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && !is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && ( !is_user_logged_in() || !current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) )
            $upload_disabled = true;

        $uid = @session_id();

        if ( is_user_logged_in() )
            $uid = 'user_' . get_current_user_id();

        $nonce_check = 'pods_upload_' . (int) $params->pod . '_' . $uid . '_' . $params->uri . '_' . (int) $params->field;

        if ( true === $upload_disabled || !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, $nonce_check ) )
            pods_error( __( 'Unauthorized request', 'pods' ), PodsInit::$admin );

        $pod = array();
        $field = array(
            'type' => 'file',
            'options' => array()
        );

        $api = pods_api();

	    $api->display_errors = false;

        if ( !empty( $params->pod ) ) {
            $pod = $api->load_pod( array( 'id' => (int) $params->pod ) );
            $field = $api->load_field( array( 'id' => (int) $params->field ) );

            if ( empty( $pod ) || empty( $field ) || $pod[ 'id' ] != $field[ 'pod_id' ] || !isset( $pod[ 'fields' ][ $field[ 'name' ] ] ) )
                pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );

            if ( !in_array( $field[ 'type' ], PodsForm::file_field_types() ) )
                pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
        }

        $method = $params->method;

        // Cleaning up $params
        unset( $params->action );
        unset( $params->method );
        unset( $params->_wpnonce );

        $params->post_id = pods_var( 'post_id', $params, 0, null, true );

        /**
         * Upload a new file (advanced - returns URL and ID)
         */
        if ( 'upload' == $method ) {
            $file = $_FILES[ 'Filedata' ];

            $limit_size = pods_var( $field[ 'type' ] . '_restrict_filesize', $field[ 'options' ] );

            if ( !empty( $limit_size ) ) {
                if ( false !== stripos( $limit_size, 'MB' ) ) {
                    $limit_size = (float) trim( str_ireplace( 'MB', '', $limit_size ) );
                    $limit_size = $limit_size * 1025 * 1025; // convert to KB to B
                }
                elseif ( false !== stripos( $limit_size, 'KB' ) ) {
                    $limit_size = (float) trim( str_ireplace( 'KB', '', $limit_size ) );
                    $limit_size = $limit_size * 1025 * 1025; // convert to B
                }
                elseif ( false !== stripos( $limit_size, 'GB' ) ) {
                    $limit_size = (float) trim( str_ireplace( 'GB', '', $limit_size ) );
                    $limit_size = $limit_size * 1025 * 1025 * 1025; // convert to MB to KB to B
                }
                elseif ( false !== stripos( $limit_size, 'B' ) )
                    $limit_size = (float) trim( str_ireplace( 'B', '', $limit_size ) );
                else
                    $limit_size = wp_max_upload_size();

                if ( 0 < $limit_size && $limit_size < $file[ 'size' ] ) {
                    $error = __( 'File size too large, max size is %s', 'pods' );
                    $error = sprintf( $error, pods_var( $field[ 'type' ] . '_restrict_filesize', $field[ 'options' ] ) );

                    pods_error( '<div style="color:#FF0000">Error: ' . $error . '</div>' );
                }
            }

            $limit_file_type = pods_var( $field[ 'type' ] . '_type', $field[ 'options' ], 'images' );

            if ( 'images' == $limit_file_type )
                $limit_types = 'jpg,jpeg,png,gif';
            elseif ( 'video' == $limit_file_type )
                $limit_types = 'mpg,mov,flv,mp4';
            elseif ( 'audio' == $limit_file_type )
                $limit_types = 'mp3,m4a,wav,wma';
            elseif ( 'text' == $limit_file_type )
                $limit_types = 'txt,rtx,csv,tsv';
            elseif ( 'any' == $limit_file_type )
                $limit_types = '';
            else
                $limit_types = pods_var( $field[ 'type' ] . '_allowed_extensions', $field[ 'options' ], '', null, true );

            $limit_types = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_types ), ',' );

            if ( pods_version_check( 'wp', '3.5' ) ) {
                $mime_types = wp_get_mime_types();

                if ( in_array( $limit_file_type, array( 'images', 'audio', 'video' ) ) ) {
                    $new_limit_types = array();

                    foreach ( $mime_types as $type => $mime ) {
                        if ( 0 === strpos( $mime, $limit_file_type ) ) {
                            $type = explode( '|', $type );

                            $new_limit_types = array_merge( $new_limit_types, $type );
                        }
                    }

                    if ( !empty( $new_limit_types ) )
                        $limit_types = implode( ',', $new_limit_types );
                }
                elseif ( 'any' != $limit_file_type ) {
                    $new_limit_types = array();

                    $limit_types = explode( ',', $limit_types );

                    foreach ( $limit_types as $k => $limit_type ) {
                        $found = false;

                        foreach ( $mime_types as $type => $mime ) {
                            if ( 0 === strpos( $mime, $limit_type ) ) {
                                $type = explode( '|', $type );

                                foreach ( $type as $t ) {
                                    if ( !in_array( $t, $new_limit_types ) )
                                        $new_limit_types[] = $t;
                                }

                                $found = true;
                            }
                        }

                        if ( !$found )
                            $new_limit_types[] = $limit_type;
                    }

                    if ( !empty( $new_limit_types ) )
                        $limit_types = implode( ',', $new_limit_types );
                }
            }

            $limit_types = explode( ',', $limit_types );

            $limit_types = array_filter( array_unique( $limit_types ) );

            if ( !empty( $limit_types ) ) {
                $ok = false;

                foreach ( $limit_types as $limit_type ) {
                    $limit_type = '.' . trim( $limit_type, ' .' );

                    $pos = ( strlen( $file[ 'name' ] ) - strlen( $limit_type ) );

                    if ( $pos === stripos( $file[ 'name' ], $limit_type ) ) {
                        $ok = true;

                        break;
                    }
                }

                if ( false === $ok ) {
                    $error = __( 'File type not allowed, please use one of the following: %s', 'pods' );
                    $error = sprintf( $error, '.' . implode( ', .', $limit_types ) );

                    pods_error( '<div style="color:#FF0000">Error: ' . $error . '</div>' );
                }
            }

            $custom_handler = apply_filters( 'pods_upload_handle', null, 'Filedata', $params->post_id, $params, $field );

            if ( null === $custom_handler ) {
				$linked = pods_var( $field[ 'type' ] . '_linked', $field[ 'options' ], 0 );

                $attachment_id = media_handle_upload( 'Filedata', $params->post_id );

                if ( is_object( $attachment_id ) ) {
                    $errors = array();

                    foreach ( $attachment_id->errors[ 'upload_error' ] as $error_code => $error_message ) {
                        $errors[] = '[' . $error_code . '] ' . $error_message;
                    }

                    pods_error( '<div style="color:#FF0000">Error: ' . implode( '</div><div>', $errors ) . '</div>' );
                }
                else {
                    $attachment = get_post( $attachment_id, ARRAY_A );

                    $attachment[ 'filename' ] = basename( $attachment[ 'guid' ] );

                    $thumb = wp_get_attachment_image_src( $attachment[ 'ID' ], 'thumbnail', true );
                    $attachment[ 'thumbnail' ] = $thumb[ 0 ];

					$attachment[ 'link' ] = '';

					if ( $linked ) {
                    	$attachment[ 'link' ] = wp_get_attachment_url( $attachment[ 'ID' ] );
					}

                    $attachment = apply_filters( 'pods_upload_attachment', $attachment, $params->post_id );

                    wp_send_json( $attachment );
                }
            }
        }

        die(); // KBAI!
    }
}
