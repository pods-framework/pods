<?php
/**
 * @package Pods\Fields
 */
class PodsField_Avatar extends PodsField {

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
    public static $type = 'avatar';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Avatar';

    /**
     * Pod Types supported on (true for all, false for none, or give array of specific types supported)
     *
     * @var array|bool
     * @since 2.1
     */
    public static $pod_types = array( 'user' );

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {

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
            self::$type . '_uploader' => array(
                'label' => __( 'Avatar Uploader', 'pods' ),
                'default' => 'attachment',
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_avatar_uploader_options',
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
            self::$type . '_restrict_filesize' => array(
                'label' => __( 'Restrict File Size', 'pods' ),
                'depends-on' => array( self::$type . '_uploader' => 'plupload' ),
                'default' => '10MB',
                'type' => 'text'
            ),
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

        if ( 'plupload' == pods_v( self::$type . '_uploader', $options ) )
            $field_type = 'plupload';
        elseif ( 'attachment' == pods_v( self::$type . '_uploader', $options ) ) {
            if ( !pods_version_check( 'wp', '3.5' ) || !is_admin() ) // @todo test frontend media modal
                $field_type = 'attachment';
            else
                $field_type = 'media';
        }
        else {
            // Support custom File Uploader integration
            do_action( 'pods_form_ui_field_avatar_uploader_' . pods_v( self::$type . '_uploader', $options ), $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_avatar_uploader', pods_v( self::$type . '_uploader', $options ), $name, $value, $options, $pod, $id );
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

        foreach ( $value as $v ) {
            echo pods_image( $v, 'thumbnail' );
        }
    }

    /**
     * Take over the avatar served from WordPress
     *
     * @param string $avatar Default Avatar Image output from WordPress
     * @param int|string|object $id_or_email A user ID, email address, or comment object
     * @param int $size Size of the avatar image
     * @param string $default URL to a default image to use if no avatar is available
     * @param string $alt Alternate text to use in image tag. Defaults to blank
     * @return string <img> tag for the user's avatar
     */
    public function get_avatar ( $avatar, $id_or_email, $size, $default = '', $alt ='' ) {
        // Don't replace for the Avatars section of the Discussion settings page
        if ( is_admin() ) {
            $current_screen = get_current_screen();
            if ( ! is_null( $current_screen ) && 'options-discussion' == $current_screen->id && 32 == $size ) {
                return $avatar;
            }
        }

        $_user_ID = 0;

        if ( is_numeric( $id_or_email ) && 0 < $id_or_email )
            $_user_ID = (int) $id_or_email;
        elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) && 0 < $id_or_email->user_id )
            $_user_ID = (int) $id_or_email->user_id;
        elseif ( is_object( $id_or_email ) && isset( $id_or_email->ID ) && isset( $id_or_email->user_login ) && 0 < $id_or_email->ID )
            $_user_ID = (int) $id_or_email->ID;
        elseif ( !is_object( $id_or_email ) && false !== strpos( $id_or_email, '@' ) ) {
            $_user = get_user_by( 'email', $id_or_email );

            if ( !empty( $_user ) )
                $_user_ID = (int) $_user->ID;
        }

		// Include PodsMeta if not already included
		pods_meta();

        if ( 0 < $_user_ID && !empty( PodsMeta::$user ) ) {
            $avatar_cached = pods_cache_get( $_user_ID . '-' . $size, 'pods_avatars' );

            if ( !empty( $avatar_cached ) )
                $avatar = $avatar_cached;
            else {
                $avatar_field = pods_transient_get( 'pods_avatar_field' );

                $user = current( PodsMeta::$user );

                if ( empty( $avatar_field ) ) {
                    foreach ( $user[ 'fields' ] as $field ) {
                        if ( 'avatar' == $field[ 'type' ] ) {
                            $avatar_field = $field[ 'name' ];

                            pods_transient_set( 'pods_avatar_field', $avatar_field );

                            break;
                        }
                    }
                }
                elseif ( !isset( $user[ 'fields' ][ $avatar_field ] ) )
                    $avatar_field = false;

                if ( !empty( $avatar_field ) ) {
                    $user_avatar = get_user_meta( $_user_ID, $avatar_field . '.ID', true );

                    if ( !empty( $user_avatar ) ) {
                        $attributes = array(
                            'alt' => '',
                            'class' => 'avatar avatar-' . $size . ' photo'
                        );

                        if ( !empty( $alt ) )
                            $attributes[ 'alt' ] = $alt;

                        $user_avatar = pods_image( $user_avatar, array( $size, $size ), 0, $attributes );

                        if ( !empty( $user_avatar ) ) {
                            $avatar = $user_avatar;

                            pods_cache_set( $_user_ID . '-' . $size, $avatar, 'pods_avatars' );
                        }
                    }
                }
            }
        }

        return $avatar;
    }
}
