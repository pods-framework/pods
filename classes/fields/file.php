<?php
class PodsField_File extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'file';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'File Upload';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @param array $options
     *
     * @since 2.0.0
     */
    public function options () {
        $options = array(
            'file_format_type' => array(
                'label' => __( 'File Type', 'pods' ),
                'default' => 'single',
                'type' => 'pick',
                'data' => array(
                    'single' => __( 'Single File Upload', 'pods' ),
                    'multi-limited' => __( 'Multiple File Upload (limited uploads)', 'pods' ),
                    'multi-unlimited' => __( 'Multiple File Upload (no limit)', 'pods' )
                )
            ),
            'file_uploader' => array(
                'label' => __( 'File Uploader', 'pods' ),
                'default' => 'plupload',
                'type' => 'pick',
                'data' =>
                    apply_filters(
                        'pods_form_ui_field_file_uploader_options',
                        array(
                            'plupload' => __( 'Plupload', 'pods' ),
                            'attachment' => __( 'Attachments (WP Media Library)', 'pods' )
                        )
                    )
            ),
            'file_limit' => array(
                'label' => __( 'File Limit', 'pods' ),
                'depends-on' => array( 'file_format_type' => 'multi-limited' ),
                'default' => 5,
                'type' => 'number'
            ),
            'file_restrict_filesize' => array(
                'label' => __( 'Restrict File Size', 'pods' ),
                'default' => '10MB',
                'type' => 'text'
            ),
            'file_type' => array(
                'label' => __( 'Restrict File Types', 'pods' ),
                'default' => 'images',
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_file_type_options',
                    array(
                        'images' => __( 'Images (jpg, png, gif)', 'pods' ),
                        'video' => __( 'Video (mpg, mov, flv, mp4)', 'pods' ),
                        'other' => __( 'Other (customize allowed extensions)', 'pods' )
                    )
                )
            ),
            'file_allowed_extensions' => array(
                'label' => __( 'Allowed File Extensions', 'pods' ),
                'description' => __( 'Separate file extensions with a comma (ex. jpg,png,mp4,mov)', 'pods' ),
                'depends-on' => array( 'file_type' => 'other' ),
                'default' => '',
                'type' => 'text'
            ),
            'file_image_size' => array(
                'label' => __( 'Image Sizes', 'pods' ),
                'default' => 'images',
                'type' => 'pick',
                'options' => array(
                    // @todo multiple select checkbox
                ),
                'data' => apply_filters(
                    'pods_form_ui_field_file_image_size_options',
                    get_intermediate_image_sizes()
                )
            )
        );
        return $options;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function display ( &$value, $name, $options, $fields, &$pod, $id ) {

    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

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

        if ( 'plupload' == $options[ 'file_format_type' ] )
            $field_type = 'plupload';
        elseif ( 'attachment' == $options[ 'file_format_type' ] )
            $field_type = 'attachment';
        else {
            // Support custom File Uploader integration
            do_action( 'pods_form_ui_field_file_uploader_' . $options[ 'file_format_type' ], $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_file_uploader', $options[ 'file_format_type' ], $name, $value, $options, $pod, $id );
            return;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Validate a value before it's saved
     *
     * @param string $value
     * @param string $name
     * @param array $options
     * @param array $data
     * @param object $api
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function validate ( &$value, $name, $options, $data, &$api, &$pod, $id = false ) {
        // check file size
        // check file extensions
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     * @param string $name
     * @param array $options
     * @param array $data
     * @param object $api
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value, $name, $options, $data, &$api, &$pod, $id = false ) {

    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function ui ( $value, $name, $options, $fields, &$pod, $id ) {
        // link to file in new target
        // show thumbnail
    }
}