<?php
/**
 * @package Pods
 */
class PodsAPI {

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
     * @deprecated 2.0.0
     */
    public $format = null;

    /**
     * @var
     */
    private $deprecated;

    /**
     * Store and retrieve data programatically
     *
     * @param string $pod (optional) The pod name
     * @param string $format (deprecated) Format for import/export, "php" or "csv"
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.7.1
     */
    public function __construct ( $pod = null, $format = null ) {
        if ( null !== $pod && 0 < strlen( (string) $pod ) ) {
            if ( null !== $format ) {
                $this->format = $format;

                pods_deprecated( 'pods_api( $pod, $format )', '2.0.0', 'pods_api( $pod )' );
            }

            $pod = pods_clean_name( $pod );

            $pod = $this->load_pod( array( 'name' => $pod ), false );

            if ( !empty( $pod ) ) {
                $this->pod_data = $pod;
                $this->pod = $pod[ 'name' ];
                $this->pod_id = $pod[ 'id' ];
                $this->fields = $pod[ 'fields' ];
            }
        }
    }

    /**
     * Save a WP object and it's meta
     *
     * @param string $object_type Object type: post|user|comment
     * @param array $data All post data to be saved
     * @param array $meta (optional) Associative array of meta keys and values
     * @param bool $strict (optional) Decides whether the previous saved meta should be deleted or not
     * @param bool $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
     *
     * @return bool|mixed
     *
     * @since 2.0.0
     */
    public function save_wp_object ( $object_type, $data, $meta = array(), $strict = false, $sanitized = false ) {
        if ( in_array( $object_type, array( 'post_type', 'media' ) ) )
            $object_type = 'post';

        if ( $sanitized ) {
            $data = pods_unsanitize( $data );
            $meta = pods_unsanitize( $meta );
        }

        if ( in_array( $object_type, array( 'post', 'user', 'comment' ) ) )
            return call_user_func( array( $this, 'save_' . $object_type ), $data, $meta, $strict, false );

        return false;
    }

    /**
     * Delete a WP object
     *
     * @param string $object_type Object type: post|user|comment
     * @param int $id Object ID
     * @param bool $force_delete (optional) Force deletion instead of trashing (post types only)
     *
     * @return bool|mixed
     *
     * @since 2.0.0
     */
    public function delete_wp_object ( $object_type, $id, $force_delete = true ) {
        if ( in_array( $object_type, array( 'post_type', 'media' ) ) )
            $object_type = 'post';

        if ( empty( $id ) )
            return false;

        if ( in_array( $object_type, array( 'post' ) ) )
            return wp_delete_post( $id, $force_delete );

        if ( function_exists( 'wp_delete_' . $object_type ) )
            return call_user_func( 'wp_delete_' . $object_type, $id );

        return false;
    }

    /**
     * Save a post and it's meta
     *
     * @param array $post_data All post data to be saved (using wp_insert_post / wp_update_post)
     * @param array $post_meta (optional) All meta to be saved (set value to null to delete)
     * @param bool $strict (optional) Whether to delete previously saved meta not in $post_meta
     * @param bool $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
     * @return mixed|void
     */
    public function save_post ( $post_data, $post_meta = null, $strict = false, $sanitized = false ) {
        pods_no_conflict_on( 'post' );

        if ( !is_array( $post_data ) || empty( $post_data ) )
            $post_data = array( 'post_title' => '' );

        if ( !is_array( $post_meta ) )
            $post_meta = array();

        if ( $sanitized ) {
            $post_data = pods_unsanitize( $post_data );
            $post_meta = pods_unsanitize( $post_meta );
        }

        if ( !isset( $post_data[ 'ID' ] ) || empty( $post_data[ 'ID' ] ) )
            $post_data[ 'ID' ] = wp_insert_post( $post_data, true );
        elseif ( 2 < count( $post_data ) || !isset( $post_data[ 'post_type' ] ) )
            wp_update_post( $post_data );

        if ( is_wp_error( $post_data[ 'ID' ] ) ) {
            pods_no_conflict_off( 'post' );

            /**
             * @var $post_error WP_Error
             */
            $post_error = $post_data[ 'ID' ];

            return pods_error( $post_error->get_error_message(), $this );
        }

        $this->save_post_meta( $post_data[ 'ID' ], $post_meta, $strict );

        pods_no_conflict_off( 'post' );

        return $post_data[ 'ID' ];
    }

    /**
     * Save a post's meta
     *
     * @param int $id Post ID
     * @param array $post_meta All meta to be saved (set value to null to delete)
     * @param bool $strict Whether to delete previously saved meta not in $post_meta
     *
     * @return int Id of the post with the meta
     *
     * @since 2.0.0
     */
    public function save_post_meta ( $id, $post_meta = null, $strict = false ) {
        pods_no_conflict_on( 'post' );

        if ( !is_array( $post_meta ) )
            $post_meta = array();

        $id = (int) $id;

        $meta = get_post_meta( $id );

        foreach ( $meta as &$value ) {
            if ( is_array( $value ) && 1 == count( $value ) && isset( $value[ 0 ] ) )
                $value = $value[ 0 ];
        }

        foreach ( $post_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_post_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_post_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $post_meta[ $meta_key ] ) )
                    delete_post_meta( $id, $meta_key, $meta_value );
            }
        }
        pods_no_conflict_off( 'post' );

        return $id;
    }

    /**
     * Save a user and it's meta
     *
     * @param array $user_data All user data to be saved (using wp_insert_user / wp_update_user)
     * @param array $user_meta (optional) All meta to be saved (set value to null to delete)
     * @param bool $strict (optional) Whether to delete previously saved meta not in $user_meta
     * @param bool $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
     *
     * @return int Returns user id on success
     *
     * @since 2.0.0
     */
    public function save_user ( $user_data, $user_meta = null, $strict = false, $sanitized = false ) {
        if ( !is_array( $user_data ) || empty( $user_data ) )
            return pods_error( __( 'User data is required but is either invalid or empty', 'pods' ), $this );

        pods_no_conflict_on( 'user' );

        if ( !is_array( $user_meta ) )
            $user_meta = array();

        if ( $sanitized ) {
            $user_data = pods_unsanitize( $user_data );
            $user_meta = pods_unsanitize( $user_meta );
        }

        if ( !isset( $user_data[ 'ID' ] ) || empty( $user_data[ 'ID' ] ) )
            $user_data[ 'ID' ] = wp_insert_user( $user_data );
        else
            wp_update_user( $user_data );

        if ( is_wp_error( $user_data[ 'ID' ] ) ) {
            pods_no_conflict_off( 'user' );

            /**
             * @var $user_error WP_Error
             */
            $user_error = $user_data[ 'ID' ];

            return pods_error( $user_error->get_error_message(), $this );
        }

        $this->save_user_meta( $user_data[ 'ID' ], $user_meta, $strict );

        pods_no_conflict_off( 'user' );

        return $user_data[ 'ID' ];
    }

    /**
     * Save a user meta
     *
     * @param int $id User ID
     * @param array $user_meta (optional) All meta to be saved (set value to null to delete)
     * @param bool $strict (optional) Whether to delete previously saved meta not in $user_meta
     *
     * @return int User ID
     *
     * @since 2.0.0
     *
     */
    public function save_user_meta ( $id, $user_meta = null, $strict = false ) {
        pods_no_conflict_on( 'user' );

        if ( !is_array( $user_meta ) )
            $user_meta = array();

        $id = (int) $id;

        $meta = get_user_meta( $id );

        foreach ( $user_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_user_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_user_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $user_meta[ $meta_key ] ) )
                    delete_user_meta( $id, $meta_key, $user_meta[ $meta_key ] );
            }
        }

        pods_no_conflict_off( 'user' );

        return $id;
    }

    /**
     * Save a comment and it's meta
     *
     * @param array $comment_data All comment data to be saved (using wp_insert_comment / wp_update_comment)
     * @param array $comment_meta (optional) All meta to be saved (set value to null to delete)
     * @param bool $strict (optional) Whether to delete previously saved meta not in $comment_meta
     * @param bool $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
     *
     * @return int Comment ID
     *
     * @since 2.0.0
     */
    public function save_comment ( $comment_data, $comment_meta = null, $strict = false, $sanitized = false ) {
        if ( !is_array( $comment_data ) || empty( $comment_data ) )
            return pods_error( __( 'Comment data is required but is either invalid or empty', 'pods' ), $this );

        pods_no_conflict_on( 'comment' );

        if ( !is_array( $comment_meta ) )
            $comment_meta = array();

        if ( $sanitized ) {
            $comment_data = pods_unsanitize( $comment_data );
            $comment_meta = pods_unsanitize( $comment_meta );
        }

        if ( !isset( $comment_data[ 'comment_ID' ] ) || empty( $comment_data[ 'comment_ID' ] ) )
            $comment_data[ 'comment_ID' ] = wp_insert_comment( $comment_data );
        else
            wp_update_comment( $comment_data );

        if ( is_wp_error( $comment_data[ 'comment_ID' ] ) ) {
            pods_no_conflict_off( 'comment' );

            /**
             * @var $comment_error WP_Error
             */
            $comment_error = $comment_data[ 'comment_ID' ];

            return pods_error( $comment_error->get_error_message(), $this );
        }

        $this->save_comment_meta( $comment_data[ 'comment_ID' ], $comment_meta, $strict );

        pods_no_conflict_off( 'comment' );

        return $comment_data[ 'comment_ID' ];
    }

    /**
     * Save a comment meta
     *
     * @param int $id Comment ID
     * @param array $comment_meta (optional) All meta to be saved (set value to null to delete)
     * @param bool $strict (optional) Whether to delete previously saved meta not in $comment_meta
     *
     * @return int Comment ID
     *
     * @since 2.0.0
     */
    public function save_comment_meta ( $id, $comment_meta = null, $strict = false ) {
        pods_no_conflict_on( 'comment' );

        if ( !is_array( $comment_meta ) )
            $comment_meta = array();

        $id = (int) $id;

        $meta = get_comment_meta( $id );

        foreach ( $comment_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_comment_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_comment_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $comment_meta[ $meta_key ] ) )
                    delete_comment_meta( (int) $id, $meta_key, $comment_meta[ $meta_key ] );
            }
        }

        pods_no_conflict_off( 'comment' );

        return $id;
    }

    /**
     * Save a taxonomy's term
     *
     * @param int $term_ID Term ID, leave empty to add
     * @param string $term Term name
     * @param string $taxonomy Taxonomy name
     * @param array $term_data All term data to be saved (using wp_insert_term / wp_update_term)
     * @param bool $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
     *
     * @return int Term ID
     *
     * @since 2.0.0
     */
    public function save_term ( $term_ID, $term, $taxonomy, $term_data, $sanitized = false ) {
        pods_no_conflict_on( 'taxonomy' );

        if ( !is_array( $term_data ) )
            $term_data = array();

        $term_ID = (int) $term_ID;

        if ( $sanitized ) {
            $term = pods_unsanitize( $term );
            $taxonomy = pods_unsanitize( $taxonomy );
            $term_data = pods_unsanitize( $term_data );
        }

        if ( empty( $term_ID ) )
            $term_ID = wp_insert_term( $term, $taxonomy, $term_data );
        else {
            if ( 0 < strlen( $term ) )
                $term_data[ 'term' ] = $term;

            if ( empty( $term_data ) ) {
                pods_no_conflict_off( 'taxonomy' );

                return pods_error( __( 'Taxonomy term data is required but is either invalid or empty', 'pods' ), $this );
            }

            wp_update_term( $term_ID, $taxonomy, $term_data );
        }

        if ( is_wp_error( $term_ID ) ) {
            pods_no_conflict_off( 'taxonomy' );

            return pods_error( $term_ID->get_error_message(), $this );
        }
        elseif ( is_array( $term_ID ) )
            $term_ID = $term_ID[ 'term_id' ];

        pods_no_conflict_off( 'taxonomy' );

        return $term_ID;
    }

    /**
     * Rename a WP object's type
     *
     * @param string $object_type Object type: post|taxonomy|comment
     * @param string $old_name The old name
     * @param string $new_name The new name
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function rename_wp_object_type ( $object_type, $old_name, $new_name ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( 'post_type' == $object_type )
            $object_type = 'post';

        if ( 'post' == $object_type ) {
            pods_query( "UPDATE `{$wpdb->posts}` SET `post_type` = %s WHERE `post_type` = %s", array(
                $new_name,
                $old_name
            ) );
        }
        elseif ( 'taxonomy' == $object_type ) {
            pods_query( "UPDATE `{$wpdb->term_taxonomy}` SET `taxonomy` = %s WHERE `taxonomy` = %s", array(
                $new_name,
                $old_name
            ) );
        }
        elseif ( 'comment' == $object_type ) {
            pods_query( "UPDATE `{$wpdb->comments}` SET `comment_type` = %s WHERE `comment_type` = %s", array(
                $new_name,
                $old_name
            ) );
        }

        return true;
    }

    /**
     * Get a list of core WP object fields for a specific object
     *
     * @param string $object The post type to look for, possible values: post_type, user, comment, taxonomy
     * @param array $pod Array of Pod data
     *
     * @return array Array of fields
     */
    public function get_wp_object_fields ( $object = 'post_type', $pod = null ) {
        $fields = pods_transient_get( 'pods_api_object_fields_' . $object );

        if ( false !== $fields )
            return apply_filters( 'pods_api_get_wp_object_fields', $fields, $object, $pod );

        $fields = array();

        if ( 'post_type' == $object ) {
            $post_stati = get_post_stati();

            $fields = array(
                'post_title' => array(
                    'name' => 'post_title',
                    'label' => 'Title',
                    'type' => 'text',
                    'alias' => array( 'title', 'name' )
                ),
                'post_content' => array(
                    'name' => 'post_content',
                    'label' => 'Content',
                    'type' => 'wysiwyg',
                    'alias' => array( 'content' ),
                    'options' => array(
                        'wysiwyg_oembed' => 1,
                        'wysiwyg_wptexturize' => 1,
                        'wysiwyg_convert_chars' => 1,
                        'wysiwyg_wpautop' => 1,
                        'wysiwyg_allow_shortcode' => 1,
                        'wysiwyg_allowed_html_tags' => ''
                    )
                ),
                'post_excerpt' => array(
                    'name' => 'post_excerpt',
                    'label' => 'Excerpt',
                    'type' => 'paragraph',
                    'alias' => array( 'excerpt' ),
                    'options' => array(
                        'paragraph_allow_html' => 1,
                        'paragraph_oembed' => 1,
                        'paragraph_wptexturize' => 1,
                        'paragraph_convert_chars' => 1,
                        'paragraph_wpautop' => 1,
                        'paragraph_allow_shortcode' => 1,
                        'paragraph_allowed_html_tags' => ''
                    )
                ),
                'post_author' => array(
                    'name' => 'post_author',
                    'label' => 'Author',
                    'type' => 'pick',
                    'alias' => array( 'author' ),
                    'pick_object' => 'user',
                    'options' => array(
                        'pick_format_type' => 'single',
                        'pick_format_single' => 'autocomplete',
                        'default_value' => '{@user.ID}'
                    )
                ),
                'post_date' => array(
                    'name' => 'post_date',
                    'label' => 'Publish Date',
                    'type' => 'datetime',
                    'alias' => array( 'created', 'date' )
                ),
                'post_date_gmt' => array(
                    'name' => 'post_date',
                    'label' => 'Publish Date (GMT)',
                    'type' => 'datetime',
                    'alias' => array(),
                    'hidden' => true
                ),
                'post_status' => array(
                    'name' => 'post_status',
                    'label' => 'Status',
                    'type' => 'pick',
                    'default' => 'draft',
                    'alias' => array( 'status' ),
                    'data' => $post_stati
                ),
                'comment_status' => array(
                    'name' => 'comment_status',
                    'label' => 'Comment Status',
                    'type' => 'text',
                    'default' => get_option( 'default_comment_status', 'open' ),
                    'alias' => array(),
                    'data' => array(
                        'open' => __( 'Open', 'pods' ),
                        'closed' => __( 'Closed', 'pods' )
                    )
                ),
                'ping_status' => array(
                    'name' => 'ping_status',
                    'label' => 'Ping Status',
                    'default' => get_option( 'default_ping_status', 'open' ),
                    'type' => 'text',
                    'alias' => array(),
                    'data' => array(
                        'open' => __( 'Open', 'pods' ),
                        'closed' => __( 'Closed', 'pods' )
                    )
                ),
                'post_password' => array(
                    'name' => 'post_password',
                    'label' => 'Password',
                    'type' => 'text',
                    'alias' => array()
                ),
                'post_name' => array(
                    'name' => 'post_name',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'alias' => array( 'slug', 'permalink' )
                ),
                'to_ping' => array(
                    'name' => 'to_ping',
                    'label' => 'To Ping',
                    'type' => 'text',
                    'alias' => array(),
                    'hidden' => true
                ),
                'pinged' => array(
                    'name' => 'pinged',
                    'label' => 'Pinged',
                    'type' => 'text',
                    'alias' => array(),
                    'hidden' => true
                ),
                'post_modified' => array(
                    'name' => 'post_modified',
                    'label' => 'Last Modified Date',
                    'type' => 'datetime',
                    'alias' => array( 'modified' ),
                    'hidden' => true
                ),
                'post_modified_gmt' => array(
                    'name' => 'post_modified',
                    'label' => 'Last Modified Date (GMT)',
                    'type' => 'datetime',
                    'alias' => array(),
                    'hidden' => true
                ),
                'post_content_filtered' => array(
                    'name' => 'post_content_filtered',
                    'label' => 'Content (filtered)',
                    'type' => 'paragraph',
                    'alias' => array(),
                    'hidden' => true,
                    'options' => array(
                        'paragraph_allow_html' => 1,
                        'paragraph_oembed' => 1,
                        'paragraph_wptexturize' => 1,
                        'paragraph_convert_chars' => 1,
                        'paragraph_wpautop' => 1,
                        'paragraph_allow_shortcode' => 1,
                        'paragraph_allowed_html_tags' => ''
                    )
                ),
                'post_parent' => array(
                    'name' => 'post_parent',
                    'label' => 'Parent',
                    'type' => 'pick',
                    'alias' => array( 'parent' ),
                    'data' => array(),
                    'hidden' => true
                ),
                'guid' => array(
                    'name' => 'guid',
                    'label' => 'GUID',
                    'type' => 'text',
                    'alias' => array(),
                    'hidden' => true
                ),
                'menu_order' => array(
                    'name' => 'menu_order',
                    'label' => 'Menu Order',
                    'type' => 'number',
                    'alias' => array()
                ),
                'post_type' => array(
                    'name' => 'post_type',
                    'label' => 'Type',
                    'type' => 'text',
                    'alias' => array( 'type' ),
                    'hidden' => true
                ),
                'post_mime_type' => array(
                    'name' => 'post_mime_type',
                    'label' => 'Mime Type',
                    'type' => 'text',
                    'alias' => array(),
                    'hidden' => true
                ),
                'comment_count' => array(
                    'name' => 'comment_count',
                    'label' => 'Comment Count',
                    'type' => 'number',
                    'alias' => array(),
                    'hidden' => true
                )
            );
        }
        elseif ( 'user' == $object ) {
            $fields = array(
                'user_login' => array(
                    'name' => 'user_login',
                    'label' => 'Title',
                    'type' => 'text',
                    'alias' => array( 'login' ),
                    'options' => array(
                        'required' => 1
                    )
                ),
                'user_nicename' => array(
                    'name' => 'user_nicename',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'alias' => array( 'nicename', 'slug', 'permalink' )
                ),
                'display_name' => array(
                    'name' => 'display_name',
                    'label' => 'Display Name',
                    'type' => 'text',
                    'alias' => array( 'title', 'name' )
                ),
                'user_pass' => array(
                    'name' => 'user_pass',
                    'label' => 'Password',
                    'type' => 'text',
                    'alias' => array( 'password', 'pass' ),
                    'options' => array(
                        'required' => 1,
                        'text_format_type' => 'password'
                    )
                ),
                'user_email' => array(
                    'name' => 'user_email',
                    'label' => 'E-mail',
                    'type' => 'text',
                    'alias' => array( 'email' ),
                    'options' => array(
                        'required' => 1,
                        'text_format_type' => 'email'
                    )
                ),
                'user_url' => array(
                    'name' => 'user_url',
                    'label' => 'URL',
                    'type' => 'text',
                    'alias' => array( 'url', 'website' ),
                    'options' => array(
                        'required' => 1,
                        'text_format_type' => 'website',
                        'text_format_website' => 'normal'
                    )
                ),
                'user_registered' => array(
                    'name' => 'user_registered',
                    'label' => 'Registration Date',
                    'type' => 'date',
                    'alias' => array( 'created', 'date', 'registered' ),
                    'options' => array(
                        'date_format_type' => 'datetime'
                    )
                )
            );
        }
        elseif ( 'comment' == $object ) {
            $fields = array(
                'comment_content' => array(
                    'name' => 'comment_content',
                    'label' => 'Content',
                    'type' => 'wysiwyg',
                    'alias' => array( 'content' )
                ),
                'comment_approved' => array(
                    'name' => 'comment_approved',
                    'label' => 'Approved',
                    'type' => 'number',
                    'alias' => array( 'approved' )
                ),
                'comment_post_ID' => array(
                    'name' => 'comment_post_ID',
                    'label' => 'Post',
                    'type' => 'pick',
                    'alias' => array( 'post', 'post_id' ),
                    'data' => array()
                ),
                'user_id' => array(
                    'name' => 'user_id',
                    'label' => 'Author',
                    'type' => 'pick',
                    'alias' => array( 'author' ),
                    'pick_object' => 'user',
                    'data' => array()
                ),
                'comment_date' => array(
                    'name' => 'comment_date',
                    'label' => 'Date',
                    'type' => 'date',
                    'alias' => array( 'created', 'date' ),
                    'options' => array(
                        'date_format_type' => 'datetime'
                    )
                )
            );
        }
        elseif ( 'taxonomy' == $object ) {
            $fields = array(
                'name' => array(
                    'name' => 'name',
                    'label' => 'Title',
                    'type' => 'text',
                    'alias' => array( 'title' )
                ),
                'slug' => array(
                    'name' => 'slug',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'alias' => array( 'permalink' )
                ),
                'description' => array(
                    'name' => 'description',
                    'label' => 'Description',
                    'type' => 'wysiwyg',
                    'alias' => array( 'content' )
                ),
                'taxonomy' => array(
                    'name' => 'taxonomy',
                    'label' => 'Taxonomy',
                    'type' => 'pick',
                    'alias' => array()
                )
            );
        }

        $fields = apply_filters( 'pods_api_get_wp_object_fields', $fields, $object, $pod );

        foreach ( $fields as $field => &$options ) {
            if ( !isset( $options[ 'alias' ] ) )
                $options[ 'alias' ] = array();
            else
                $options[ 'alias' ] = (array) $options[ 'alias' ];

            if ( !isset( $options[ 'name' ] ) )
                $options[ 'name' ] = $field;
        }

        $fields = PodsForm::fields_setup( $fields );

        pods_transient_set( 'pods_api_object_fields_' . $object, $fields );

        return $fields;
    }

    /**
     *
     * @see PodsAPI::save_pod
     *
     * Add a Pod via the Wizard
     *
     * $params['create_extend'] string Create or Extend a Content Type
     * $params['create_pod_type'] string Pod Type (for Creating)
     * $params['create_name'] string Pod Name (for Creating)
     * $params['create_label_plural'] string Plural Label (for Creating)
     * $params['create_label_singular'] string Singular Label (for Creating)
     * $params['create_storage'] string Storage Type (for Creating Post Types)
     * $params['create_storage_taxonomy'] string Storage Type (for Creating Taxonomies)
     * $params['extend_pod_type'] string Pod Type (for Extending)
     * $params['extend_post_type'] string Post Type (for Extending Post Types)
     * $params['extend_taxonomy'] string Taxonomy (for Extending Taxonomies)
     * $params['extend_storage'] string Storage Type (for Extending Post Types / Users / Comments)
     *
     * @param array $params An associative array of parameters
     *
     * @return bool|int Pod ID
     * @since 2.0.0
     */
    public function add_pod ( $params ) {
        $defaults = array(
            'create_extend' => 'create',
            'create_pod_type' => 'post_type',
            'create_name' => '',
            'create_label_plural' => '',
            'create_label_singular' => '',
            'create_storage' => 'meta',
            'create_storage_taxonomy' => 'none',
            'extend_pod_type' => 'post_type',
            'extend_post_type' => 'post',
            'extend_taxonomy' => 'category',
            'extend_storage_taxonomy' => 'table',
            'extend_storage' => 'meta'
        );

        $params = (object) array_merge( $defaults, (array) $params );

        if ( empty( $params->create_extend ) || !in_array( $params->create_extend, array( 'create', 'extend' ) ) )
            return pods_error( __( 'Please choose whether to Create or Extend a Content Type', $this ) );

        $pod_params = array(
            'name' => '',
            'label' => '',
            'type' => '',
            'storage' => 'table',
            'object' => '',
            'options' => array()
        );

        if ( 'create' == $params->create_extend ) {
            if ( empty( $params->create_name ) )
                return pods_error( 'Please enter a Name for this Pod', $this );

            $pod_params[ 'name' ] = $params->create_name;
            $pod_params[ 'label' ] = ( !empty( $params->create_label_plural ) ? $params->create_label_plural : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
            $pod_params[ 'type' ] = $params->create_pod_type;
            $pod_params[ 'options' ] = array(
                'label_singular' => ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) ),
                'public' => 1,
                'show_ui' => 1
            );

            if ( 'post_type' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->create_storage;

                if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
                    $pod_params[ 'storage' ] = 'meta';
            }
            elseif ( 'taxonomy' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->create_storage_taxonomy;

                if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
                    $pod_params[ 'storage' ] = 'none';
            }
            elseif ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS ) {
                $pod_params[ 'type' ] = 'post_type';
                $pod_params[ 'storage' ] = 'meta';
            }
        }
        elseif ( 'extend' == $params->create_extend ) {
            $pod_params[ 'type' ] = $params->extend_pod_type;

            if ( 'post_type' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->extend_storage;

                if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
                    $pod_params[ 'storage' ] = 'meta';

                $pod_params[ 'name' ] = $params->extend_post_type;
            }
            elseif ( 'taxonomy' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->extend_storage_taxonomy;

                if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
                    $pod_params[ 'storage' ] = 'none';

                $pod_params[ 'name' ] = $params->extend_taxonomy;
            }
            else {
                $pod_params[ 'storage' ] = $params->extend_storage;

                if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
                    $pod_params[ 'storage' ] = 'meta';

                $pod_params[ 'name' ] = $params->extend_pod_type;
            }

            $pod_params[ 'label' ] = ucwords( str_replace( '_', ' ', $pod_params[ 'name' ] ) );
            $pod_params[ 'object' ] = $pod_params[ 'name' ];
        }

        if ( empty( $pod_params[ 'object' ] ) ) {
            if ( 'post_type' == $pod_params[ 'type' ] ) {
                $check = get_post_type_object( $pod_params[ 'name' ] );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Post Type %s already exists, try extending it instead', 'pods' ), $pod_params[ 'name' ] ), $this );

                $pod_params[ 'options' ][ 'supports_title' ] = 1;
                $pod_params[ 'options' ][ 'supports_editor' ] = 1;
            }
            elseif ( 'taxonomy' == $pod_params[ 'type' ] ) {
                $check = get_taxonomy( $pod_params[ 'name' ] );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Taxonomy %s already exists, try extending it instead', 'pods' ), $pod_params[ 'name' ] ), $this );
            }
        }

        if ( !empty( $pod_params ) )
            return $this->save_pod( $pod_params );

        return false;
    }

    /**
     * Add or edit a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     * $params['label'] string The Pod label
     * $params['type'] string The Pod type
     * $params['storage'] string The Pod storage
     * $params['options'] array Options
     *
     * @param array $params An associative array of parameters
     * @param bool $sanitized (optional) Decides whether the params have been sanitized before being passed, will sanitize them if false.
     * @param bool|int $db (optional) Whether to save into the DB or just return Pod array.
     *
     * @return int Pod ID
     * @since 1.7.9
     */
    public function save_pod ( $params, $sanitized = false, $db = true ) {
        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $load_params = (object) $params;

        if ( isset( $load_params->id ) && isset( $load_params->name ) )
            unset( $load_params->name );

        if ( isset( $load_params->old_name ) )
            $load_params->name = $load_params->old_name;

        $pod = $this->load_pod( $load_params, false );

        $params = (object) $params;

        if ( false === $sanitized )
            $params = pods_sanitize( $params );

        $old_id = $old_name = $old_storage = null;

        $old_fields = $old_options = array();

        if ( isset( $params->name ) )
            $params->name = pods_clean_name( $params->name );

        if ( !empty( $pod ) ) {
            if ( isset( $params->id ) && 0 < $params->id )
                $old_id = $params->id;

            $params->id = $pod[ 'id' ];

            $old_name = $pod[ 'name' ];
            $old_storage = $pod[ 'storage' ];
            $old_fields = $pod[ 'fields' ];
            $old_options = $pod[ 'options' ];

            if ( !isset( $params->name ) && empty( $params->name ) )
                $params->name = $pod[ 'name' ];

            if ( $old_name != $params->name && false !== $this->pod_exists( array( 'name' => $params->name ) ) )
                return pods_error( sprintf( __( 'Pod %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ), $this );

            if ( $old_name != $params->name && in_array( $pod[ 'type' ], array( 'user', 'comment', 'media' ) ) && in_array( $pod[ 'object' ], array( 'user', 'comment', 'media' ) ) )
                return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );

            if ( $old_name != $params->name && in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) && !empty( $pod[ 'object' ] ) && $pod[ 'object' ] != $old_name )
                return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );

            if ( $old_id != $params->id ) {
                if ( $params->type == $pod[ 'type' ] && isset( $params->object ) && $params->object == $pod[ 'object' ] )
                    return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ), $this );
                else
                    return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ), $this );
            }
        }
        else {
            $pod = array(
                'id' => 0,
                'name' => $params->name,
                'label' => $params->name,
                'description' => '',
                'type' => 'pod',
                'storage' => 'table',
                'object' => '',
                'alias' => '',
                'options' => array(),
                'fields' => array()
            );
        }

        // Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $pod[ 'fields' ] = array();
            $pod[ 'options' ] = array();
        }

        // Setup options
        $options = get_object_vars( $params );

        if ( isset( $options[ 'method' ] ) )
            unset( $options[ 'method' ] );

        $exclude = array(
            'id',
            'name',
            'label',
            'description',
            'type',
            'storage',
            'object',
            'alias',
            'options',
            'fields'
        );

        foreach ( $exclude as $k => $exclude_field ) {
            $aliases = array( $exclude_field );

            if ( is_array( $exclude_field ) ) {
                $aliases = array_merge( array( $k ), $exclude_field );
                $exclude_field = $k;
            }

            foreach ( $aliases as $alias ) {
                if ( isset( $options[ $alias ] ) ) {
                    $pod[ $exclude_field ] = pods_trim( $options[ $alias ] );

                    unset( $options[ $alias ] );
                }
            }
        }

        if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS ) {
            if ( 'pod' == $pod[ 'type' ] )
                $pod[ 'type' ] = 'post_type';

            if ( 'table' == $pod[ 'storage' ] ) {
                if ( 'taxonomy' == $pod[ 'type' ] )
                    $pod[ 'storage' ] = 'none';
                else
                    $pod[ 'storage' ] = 'meta';
            }
        }

        $pod[ 'options' ][ 'type' ] = $pod[ 'type' ];
        $pod[ 'options' ][ 'storage' ] = $pod[ 'storage' ];
        $pod[ 'options' ][ 'object' ] = $pod[ 'object' ];
        $pod[ 'options' ][ 'alias' ] = $pod[ 'alias' ];

        $pod[ 'options' ] = array_merge( $pod[ 'options' ], $options );

        if ( strlen( $pod[ 'label' ] ) < 1 )
            $pod[ 'label' ] = $pod[ 'name' ];

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        if ( null !== $old_name && $old_name != $params->name && empty( $pod[ 'object' ] ) ) {
            if ( 'post_type' == $pod[ 'type' ] ) {
                $check = get_post_type_object( $params->name );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Post Type %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ), $this );
            }
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                $check = get_taxonomy( $params->name );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Taxonomy %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ), $this );
            }
        }

        $field_table_operation = true;

        // Add new pod
        if ( empty( $params->id ) ) {
            if ( strlen( $params->name ) < 1 )
                return pods_error( __( 'Pod name cannot be empty', 'pods' ), $this );

            $post_data = array(
                'post_name' => $pod[ 'name' ],
                'post_title' => $pod[ 'label' ],
                'post_content' => $pod[ 'description' ],
                'post_type' => '_pods_pod',
                'post_status' => 'publish'
            );

            if ( 'pod' == $pod[ 'type' ] && ( !is_array( $pod[ 'fields' ] ) || empty( $pod[ 'fields' ] ) ) ) {
                $pod[ 'fields' ] = array();

                $pod[ 'fields' ][] = array(
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'options' => array(
                        'required' => '1'
                    )
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'created',
                    'label' => 'Date Created',
                    'type' => 'datetime',
                    'options' => array(
                        'datetime_format' => 'ymd_slash',
                        'datetime_time_type' => '12',
                        'datetime_time_format' => 'h_mm_ss_A'
                    )
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'modified',
                    'label' => 'Date Modified',
                    'type' => 'datetime',
                    'options' => array(
                        'datetime_format' => 'ymd_slash',
                        'datetime_time_type' => '12',
                        'datetime_time_format' => 'h_mm_ss_A'
                    )
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'author',
                    'label' => 'Author',
                    'type' => 'pick',
                    'pick_object' => 'user',
                    'options' => array(
                        'pick_format_type' => 'single',
                        'pick_format_single' => 'autocomplete',
                        'default_value' => '{@user.ID}'
                    )
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'permalink',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'description' => 'Leave blank to auto-generate from Name'
                );

                if ( !isset( $pod[ 'options' ][ 'pod_index' ] ) )
                    $pod[ 'options' ][ 'pod_index' ] = 'name';
            }

            $field_table_operation = false;
        }
        else {
            $post_data = array(
                'ID' => $pod[ 'id' ],
                'post_name' => $pod[ 'name' ],
                'post_title' => $pod[ 'label' ],
                'post_content' => $pod[ 'description' ],
                'post_status' => 'publish'
            );
        }

        if ( true === $db ) {
            $params->id = $this->save_post( $post_data, $pod[ 'options' ], true, true );

            if ( false === $params->id )
                return pods_error( __( 'Cannot save Pod', 'pods' ), $this );
        }
        elseif ( empty( $params->id ) )
            $params->id = (int) $db;

        $pod[ 'id' ] = $params->id;

        // Setup / update tables
        if ( 'table' == $pod[ 'storage' ] && $old_storage != $pod[ 'storage' ] && $db ) {
            $definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );

            foreach ( $pod[ 'fields' ] as $field ) {
                if ( !in_array( $field[ 'type' ], $tableless_field_types ) )
                    $definitions[] = "`{$field['name']}` " . $this->get_field_definition( $field[ 'type' ], $field[ 'options' ] );
            }

            pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`" );

            $result = pods_query( "CREATE TABLE `@wp_pods_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8", $this );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ), $this );

        }
        elseif ( 'table' == $pod[ 'storage' ] && $pod[ 'storage' ] == $old_storage && null !== $old_name && $old_name != $params->name && $db ) {
            $result = pods_query( "ALTER TABLE `@wp_pods_{$old_name}` RENAME `@wp_pods_{$params->name}`", $this );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
        }

        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( 'post_type' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) && null !== $old_name && $old_name != $params->name && $db )
            $this->rename_wp_object_type( 'post', $old_name, $params->name );
        elseif ( 'taxonomy' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) && null !== $old_name && $old_name != $params->name && $db )
            $this->rename_wp_object_type( 'taxonomy', $old_name, $params->name );
        elseif ( 'comment' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) && null !== $old_name && $old_name != $params->name && $db )
            $this->rename_wp_object_type( 'comment', $old_name, $params->name );

        // Sync any related fields if the name has changed
        if ( null !== $old_name && $old_name != $params->name && $db ) {
            $fields = pods_query( "
                SELECT `p`.`ID`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm2` ON `pm2`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` = '_pods_field'
                    AND `pm`.`meta_key` = 'pick_object'
                    AND `pm`.`meta_value` = 'pod'
                    AND `pm2`.`meta_key` = 'pick_val'
                    AND `pm2`.`meta_value` = '{$old_name}'
            " );

            if ( !empty( $fields ) ) {
                foreach ( $fields as $field ) {
                    update_post_meta( $field->ID, 'pick_val', $params->name );
                }
            }

            $fields = pods_query( "
                SELECT `p`.`ID`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` = '_pods_field'
                    AND `pm`.`meta_key` = 'pick_object'
                    AND `pm`.`meta_value` = 'pod-{$old_name}'
            " );

            if ( !empty( $fields ) ) {
                foreach ( $fields as $field ) {
                    update_post_meta( $field->ID, 'pick_object', 'pod' );
                    update_post_meta( $field->ID, 'pick_val', $params->name );
                }
            }
        }

        // Sync built-in options for post types and taxonomies
        if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) && empty( $pod[ 'object' ] ) && $db ) {
            // Build list of 'built_in' for later
            $built_in = array();

            foreach ( $pod[ 'options' ] as $key => $val ) {
                if ( false === strpos( $key, 'built_in_' ) )
                    continue;
                elseif ( false !== strpos( $key, 'built_in_post_types_' ) )
                    $built_in_type = 'post_type';
                elseif ( false !== strpos( $key, 'built_in_taxonomies_' ) )
                    $built_in_type = 'taxonomy';
                else
                    continue;

                if ( $built_in_type == $pod[ 'type' ] )
                    continue;

                if ( !isset( $built_in[ $built_in_type ] ) )
                    $built_in[ $built_in_type ] = array();

                $built_in_object = str_replace( array( 'built_in_post_types_', 'built_in_taxonomies_' ), '', $key );

                $built_in[ $built_in_type ][ $built_in_object ] = (int) $val;
            }

            $lookup_option = $lookup_built_in = false;

            $lookup_name = $pod[ 'name' ];

            if ( 'post_type' == $pod[ 'type' ] ) {
                $lookup_option = 'built_in_post_types_' . $lookup_name;
                $lookup_built_in = 'taxonomy';
            }
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                $lookup_option = 'built_in_taxonomies_' . $lookup_name;
                $lookup_built_in = 'post_type';
            }

            if ( !empty( $lookup_option ) && !empty( $lookup_built_in ) && isset( $built_in[ $lookup_built_in ] ) ) {
                foreach ( $built_in[ $lookup_built_in ] as $built_in_object => $val ) {
                    $search_val = 1;

                    if ( 1 == $val )
                        $search_val = 0;

                    $query = "SELECT p.ID FROM {$wpdb->posts} AS p
                                LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID AND pm.meta_key = '{$lookup_option}'
                                LEFT JOIN {$wpdb->postmeta} AS pm2 ON pm2.post_id = p.ID AND pm2.meta_key = 'type' AND pm2.meta_value = '{$lookup_built_in}'
                                LEFT JOIN {$wpdb->postmeta} AS pm3 ON pm3.post_id = p.ID AND pm3.meta_key = 'object' AND pm3.meta_value = ''
                                WHERE p.post_type = '_pods_pod' AND p.post_name = '{$built_in_object}'
                                    AND pm2.meta_id IS NOT NULL
                                    AND ( pm.meta_id IS NULL OR pm.meta_value = {$search_val} )";

                    $results = pods_query( $query );

                    if ( !empty( $results ) ) {
                        foreach ( $results as $the_pod ) {
                            delete_post_meta( $the_pod->ID, $lookup_option );

                            add_post_meta( $the_pod->ID, $lookup_option, $val );
                        }
                    }
                }
            }
        }

        $saved = array();
        $errors = array();

        $field_index_change = false;
        $field_index_id = 0;

        $id_required = false;

        $field_index = pods_var( 'pod_index', $pod[ 'options' ], 'id', null, true );

        if ( 'pod' == $pod[ 'type' ] && !empty( $pod[ 'fields' ] ) && isset( $pod[ 'fields' ][ $field_index ] ) )
            $field_index_id = $pod[ 'fields' ][ $field_index ];

        if ( isset( $params->fields ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $fields = array();

            if ( isset( $params->fields ) ) {
                $params->fields = (array) $params->fields;

                $weight = 0;

                foreach ( $params->fields as $field ) {
                    if ( !isset( $field[ 'name' ] ) )
                        continue;

                    if ( !isset( $field[ 'weight' ] ) ) {
                        $field[ 'weight' ] = $weight;

                        $weight++;
                    }

                    $fields[ $field[ 'name' ] ] = $field;
                }
            }

            $weight = 0;

            foreach ( $pod[ 'fields' ] as $k => $field ) {
                if ( !empty( $old_id ) && ( !is_array( $field ) || !isset( $field[ 'name' ] ) || !isset( $fields[ $field[ 'name' ] ] ) ) )
                    continue;

                if ( !empty( $old_id ) )
                    $field = array_merge( $field, $fields[ $field[ 'name' ] ] );

                $field[ 'pod' ] = $pod;

                if ( !isset( $field[ 'weight' ] ) ) {
                    $field[ 'weight' ] = $weight;

                    $weight++;
                }

                if ( 0 < $field_index_id && pods_var( 'id', $field ) == $field_index_id )
                    $field_index_change = $field[ 'name' ];

                if ( 0 < pods_var( 'id', $field ) )
                    $id_required = true;

                if ( $id_required )
                    $field[ 'id_required' ] = true;

                $field = $this->save_field( $field, $field_table_operation, $sanitized, $db );


                if ( true !== $db )
                    $pod[ 'fields' ][ $k ] = $field;
                else {
                    if ( !empty( $field ) && 0 < $field )
                        $saved[ $field ] = true;
                    else
                        $errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field[ 'name' ] );
                }
            }

            if ( true === $db ) {
                foreach ( $old_fields as $field ) {
                    if ( !isset( $saved[ $field[ 'id' ] ] ) ) {
                        if ( $field[ 'id' ] == $field_index_id )
                            $field_index_change = 'id';
                        elseif ( $field[ 'name' ] == $field_index )
                            $field_index_change = 'id';

                        $this->delete_field( array(
                            'id' => (int) $field[ 'id' ],
                            'name' => $field[ 'name' ],
                            'pod' => $pod
                        ), $field_table_operation );
                    }
                }
            }

            // Update field index if the name has changed or the field has been removed
            if ( false !== $field_index_change && true === $db )
                update_post_meta( $pod[ 'id' ], 'pod_index', $field_index_change );
        }

        $this->cache_flush_pods( $pod );

        if ( !empty( $errors ) )
            return pods_error( $errors, $this );

        if ( true === $db )
            return $params->id;
        else
            return $pod;
    }

    /**
     * Add or edit a field within a Pod
     *
     * $params['id'] int The field ID
     * $params['pod_id'] int The Pod ID
     * $params['pod'] string The Pod name
     * $params['name'] string The field name
     * $params['label'] string The field label
     * $params['type'] string The field type ("txt", "desc", "pick", etc)
     * $params['pick_object'] string The related PICK object name
     * $params['pick_val'] string The related PICK object value
     * $params['sister_id'] int (optional) The related field ID
     * $params['weight'] int The field weight
     * $params['options'] array The field options
     *
     * @param array $params An associative array of parameters
     * @param bool $table_operation (optional) Whether or not to handle table operations
     * @param bool $sanitized (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
     * @param bool|int $db (optional) Whether to save into the DB or just return field array.
     *
     * @return int The field ID
     * @since 1.7.9
     */
    public function save_field ( $params, $table_operation = true, $sanitized = false, $db = true ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( true !== $db )
            $table_operation = false;

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $params = (object) $params;

        if ( false === $sanitized )
            $params = pods_sanitize( $params );

        if ( isset( $params->pod_id ) )
            $params->pod_id = pods_absint( $params->pod_id );

        if ( true !== $db )
            $params->pod_id = (int) $db;

        $pod = null;
        $save_pod = false;
        $id_required = false;

        if ( isset( $params->id_required ) ) {
            unset( $params->id_required );

            $id_required = true;
        }

        if ( ( !isset( $params->pod ) || empty( $params->pod ) ) && ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) )
            return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );

        if ( isset( $params->pod ) && is_array( $params->pod ) ) {
            $pod = $params->pod;

            $save_pod = true;
        }
        elseif ( ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $db || 0 < $db ) )
            $pod = $this->load_pod( array( 'name' => $params->pod ) );
        elseif ( !isset( $params->pod ) && ( true === $db || 0 < $db ) )
            $pod = $this->load_pod( array( 'id' => $params->pod_id ) );
        elseif ( true === $db || 0 < $db )
            $pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ) );

        if ( empty( $pod ) && true === $db )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];
        $params->pod_data = $pod;

        $params->name = pods_clean_name( $params->name );

        if ( !isset( $params->id ) )
            $params->id = 0;

        if ( empty( $params->name ) )
            return pods_error( 'Pod field name is required', $this );

        $field = $this->load_field( $params );

        unset( $params->pod_data );

        $old_id = $old_name = $old_type = $old_definition = $old_simple = $old_options = $old_sister_id = null;

        if ( !empty( $field ) ) {
            $old_id = pods_var( 'id', $field );
            $old_name = pods_clean_name( $field[ 'name' ] );
            $old_type = $field[ 'type' ];
            $old_options = $field[ 'options' ];
            $old_sister_id = (int) pods_var( 'sister_id', $old_options, 0 );

            $old_simple = ( 'pick' == $old_type && 'custom-simple' == pods_var( 'pick_object', $field ) );
            $old_simple = (boolean) $this->do_hook( 'tableless_custom', $old_simple, $field, $pod, $params );

            if ( isset( $params->name ) && !empty( $params->name ) )
                $field[ 'name' ] = $params->name;

            if ( $old_name != $field[ 'name' ] && false !== $this->field_exists( $params ) )
                return pods_error( sprintf( __( 'Field %s already exists, you cannot rename %s to that', 'pods' ), $field[ 'name' ], $old_name ), $this );

            if ( ( $id_required || !empty( $params->id ) ) && ( empty( $old_id ) || $old_id != $params->id ) )
                return pods_error( sprintf( __( 'Field %s already exists', 'pods' ), $field[ 'name' ] ), $this );

            if ( empty( $params->id ) )
                $params->id = $old_id;

            if ( !in_array( $old_type, $tableless_field_types ) || $old_simple )
                $old_definition = '`' . $old_name . '` ' . $this->get_field_definition( $old_type, $old_options );
        }
        else {
            $field = array(
                'id' => 0,
                'pod_id' => $params->pod_id,
                'name' => $params->name,
                'label' => $params->name,
                'description' => '',
                'type' => 'text',
                'pick_object' => '',
                'pick_val' => '',
                'sister_id' => '',
                'weight' => null,
                'options' => array()
            );
        }

        // Setup options
        $options = get_object_vars( $params );

        if ( isset( $options[ 'method' ] ) )
            unset( $options[ 'method' ] );

        $exclude = array(
            'id',
            'pod_id',
            'pod',
            'name',
            'label',
            'description',
            'type',
            'pick_object',
            'pick_val',
            'sister_id',
            'weight',
            'options'
        );

        foreach ( $exclude as $k => $exclude_field ) {
            $aliases = array( $exclude_field );

            if ( is_array( $exclude_field ) ) {
                $aliases = array_merge( array( $k ), $exclude_field );
                $exclude_field = $k;
            }

            foreach ( $aliases as $alias ) {
                if ( isset( $options[ $alias ] ) ) {
                    $field[ $exclude_field ] = pods_trim( $options[ $alias ] );

                    unset( $options[ $alias ] );
                }
            }
        }

        if ( strlen( $field[ 'label' ] ) < 1 )
            $field[ 'label' ] = $field[ 'name' ];

        $field[ 'options' ][ 'type' ] = $field[ 'type' ];

        if ( in_array( $field[ 'options' ][ 'type' ], $tableless_field_types ) ) {
            // Clean up special drop-down in field editor and save out pick_val
            $field[ 'pick_object' ] = pods_var( 'pick_object', $field, '', null, true );

            if ( 0 === strpos( $field[ 'pick_object' ], 'pod-' ) ) {
                $field[ 'pick_val' ] = pods_str_replace( 'pod-', '', $field[ 'pick_object' ], 1 );
                $field[ 'pick_object' ] = 'pod';
            }
            elseif ( 0 === strpos( $field[ 'pick_object' ], 'post_type-' ) ) {
                $field[ 'pick_val' ] = pods_str_replace( 'post_type-', '', $field[ 'pick_object' ], 1 );
                $field[ 'pick_object' ] = 'post_type';
            }
            elseif ( 0 === strpos( $field[ 'pick_object' ], 'taxonomy-' ) ) {
                $field[ 'pick_val' ] = pods_str_replace( 'taxonomy-', '', $field[ 'pick_object' ], 1 );
                $field[ 'pick_object' ] = 'taxonomy';
            }
            elseif ( false === strpos( $field[ 'pick_object' ], '-' ) && !in_array( $field[ 'pick_object' ], array( 'pod', 'post_type', 'taxonomy' ) ) )
                $field[ 'pick_val' ] = '';

            $field[ 'options' ][ 'pick_object' ] = $field[ 'pick_object' ];
            $field[ 'options' ][ 'pick_val' ] = $field[ 'pick_val' ];
            $field[ 'options' ][ 'sister_id' ] = pods_var( 'sister_id', $field );

            unset( $field[ 'pick_object' ] );
            unset( $field[ 'pick_val' ] );

            if ( isset( $field[ 'sister_id' ] ) )
                unset( $field[ 'sister_id' ] );
        }

        $field[ 'options' ] = array_merge( $field[ 'options' ], $options );

        $object_fields = (array) pods_var_raw( 'object_fields', $pod, array(), null, true );

        if ( 0 < $old_id && defined( 'PODS_FIELD_STRICT' ) && !PODS_FIELD_STRICT )
            $params->id = $field[ 'id' ] = $old_id;

        // Add new field
        if ( !isset( $params->id ) || empty( $params->id ) || empty( $field ) ) {
            if ( $table_operation && in_array( $field[ 'name' ], array( 'created', 'modified', 'author' ) ) && ( !defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) )
                return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );

            if ( in_array( $field[ 'name' ], array( 'id', 'ID' ) ) )
                return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );

            foreach ( $object_fields as $object_field => $object_field_opt ) {
                if ( $object_field == $field[ 'name' ] || in_array( $field[ 'name' ], $object_field_opt[ 'alias' ] ) )
                    return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name. Also consider what WordPress and Pods provide you built-in.', 'pods' ), $field[ 'name' ] ), $this );
            }

            if ( in_array( $field[ 'name' ], array( 'rss' ) ) ) // Reserved post_name values that can't be used as field names
                $field[ 'name' ] .= '2';

            if ( 'slug' == $field[ 'type' ] && true === $db ) {
                if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy', 'user' ) ) )
                    return pods_error( __( 'This pod already has an internal WordPress permalink field', 'pods' ), $this );

                $slug_field = get_posts( array(
                    'post_type' => '_pods_field',
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'posts_per_page' => 1,
                    'post_parent' => $field[ 'pod_id' ],
                    'meta_query' => array(
                        array(
                            'key' => 'type',
                            'value' => 'slug'
                        )
                    )
                ) );

                if ( !empty( $slug_field ) )
                    return pods_error( __( 'This pod already has a permalink field', 'pods' ), $this );
            }

            // Sink the new field to the bottom of the list
            if ( null === $field[ 'weight' ] ) {
                $field[ 'weight' ] = 0;

                $bottom_most_field = get_posts( array(
                    'post_type' => '_pods_field',
                    'orderby' => 'menu_order',
                    'order' => 'DESC',
                    'posts_per_page' => 1,
                    'post_parent' => $field[ 'pod_id' ]
                ) );

                if ( !empty( $bottom_most_field ) )
                    $field[ 'weight' ] = pods_absint( $bottom_most_field[ 0 ]->menu_order ) + 1;
            }

            $field[ 'weight' ] = pods_absint( $field[ 'weight' ] );

            $post_data = array(
                'post_name' => $field[ 'name' ],
                'post_title' => $field[ 'label' ],
                'post_content' => $field[ 'description' ],
                'post_type' => '_pods_field',
                'post_parent' => $field[ 'pod_id' ],
                'post_status' => 'publish',
                'menu_order' => $field[ 'weight' ]
            );
        }
        else {
            if ( in_array( $field[ 'name' ], array( 'id', 'ID' ) ) )
                return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field[ 'name' ] ), $this );

            foreach ( $object_fields as $object_field => $object_field_opt ) {
                if ( $object_field == $field[ 'name' ] || in_array( $field[ 'name' ], $object_field_opt[ 'alias' ] ) )
                    return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field[ 'name' ] ), $this );
            }

            if ( null !== $old_name && $field[ 'name' ] != $old_name && in_array( $field[ 'name' ], array( 'id', 'ID', 'created', 'modified', 'author' ) ) )
                return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );

            foreach ( $object_fields as $object_field => $object_field_opt ) {
                if ( $object_field == $field[ 'name' ] || in_array( $field[ 'name' ], $object_field_opt[ 'alias' ] ) )
                    return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
            }

            $post_data = array(
                'ID' => $field[ 'id' ],
                'post_name' => $field[ 'name' ],
                'post_title' => $field[ 'label' ],
                'post_content' => $field[ 'description' ]
            );

            if ( null !== $field[ 'weight' ] ) {
                $field[ 'weight' ] = pods_absint( $field[ 'weight' ] );

                $post_data[ 'menu_order' ] = $field[ 'weight' ];
            }
        }

        if ( true === $db ) {
            if ( !has_filter( 'wp_unique_post_slug', array( $this, 'save_field_slug_fix' ) ) )
                add_filter( 'wp_unique_post_slug', array( $this, 'save_field_slug_fix' ), 100, 6 );

            $params->id = $this->save_wp_object( 'post', $post_data, $field[ 'options' ], true, true );

            if ( false === $params->id )
                return pods_error( __( 'Cannot save Field', 'pods' ), $this );
        }
        else
            $params->id = $field[ 'name' ];

        $field[ 'id' ] = $params->id;

        $definition = false;

        $simple = ( 'pick' == $field[ 'type' ] && 'custom-simple' == pods_var( 'pick_object', $field[ 'options' ] ) );
        $simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field, $pod, $params );

        if ( !in_array( $field[ 'type' ], $tableless_field_types ) || $simple )
            $definition = '`' . $field[ 'name' ] . '` ' . $this->get_field_definition( $field[ 'type' ], $field[ 'options' ] );

        $sister_id = (int) pods_var( 'sister_id', $field[ 'options' ], 0 );

        if ( $table_operation && 'table' == $pod[ 'storage' ] ) {
            if ( !empty( $old_id ) ) {
                if ( $field[ 'type' ] != $old_type ) {
                    if ( in_array( $field[ 'type' ], $tableless_field_types ) && !$simple && ( !in_array( $old_type, $tableless_field_types ) || $old_simple ) )
                        pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$old_name}`", false );
                    elseif ( ( in_array( $old_type, $tableless_field_types ) && !$old_simple ) || ( in_array( $old_type, $tableless_field_types ) && $simple ) )
                        pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
                    elseif ( false !== $definition )
                        pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}" );
                }
                elseif ( $old_name != $field[ 'name' ] && false !== $definition ) {
                    $test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );

                    // If the old field doesn't exist, continue to add a new field
                    if ( false === $test )
                        pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
                }
                elseif ( null !== $old_definition && false !== $definition && $definition != $old_definition ) {
                    $test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `" . $field[ 'name' ] . "` {$definition}", false );

                    // If the old field doesn't exist, continue to add a new field
                    if ( false === $test )
                        pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
                }
            }
            elseif ( false !== $definition )
                $test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
        }

        if ( $field[ 'type' ] != $old_type && in_array( $old_type, $tableless_field_types ) ) {
            delete_post_meta( $old_sister_id, 'sister_id' );

            if ( true === $db ) {
                $wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
                    LEFT JOIN {$wpdb->posts} AS p
                        ON p.post_type = '_pods_field' AND p.ID = pm.post_id
                    WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d", $params->id ) );

                if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
                    pods_query( "DELETE FROM @wp_podsrel WHERE `field_id` = {$params->id}", false );

                    pods_query( "
                            UPDATE `@wp_podsrel`
                            SET `related_field_id` = 0
                            WHERE `field_id` = %d
                        ", array(
                            $old_sister_id
                        )
                    );
                }
            }
        }
        elseif ( 0 < $sister_id ) {
            update_post_meta( $sister_id, 'sister_id', $params->id );

            if ( true === $db && ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) ) {
                pods_query( "
                        UPDATE `@wp_podsrel`
                        SET `related_field_id` = %d
                        WHERE `field_id` = %d
                    ",
                    array(
                        $params->id,
                        $sister_id
                    )
                );
            }
        }
        elseif ( 0 < $old_sister_id ) {
            delete_post_meta( $old_sister_id, 'sister_id' );

            if ( true === $db && ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) ) {
                pods_query( "
                        UPDATE `@wp_podsrel`
                        SET `related_field_id` = 0
                        WHERE `field_id` = %d
                    ", array(
                        $old_sister_id
                    )
                );
            }
        }

        if ( !empty( $old_id ) && $old_name != $field[ 'name' ] && true === $db ) {
            pods_query( "
                    UPDATE `@wp_postmeta`
                    SET `meta_value` = %s
                    WHERE
                        `post_id` = %d
                        AND `meta_key` = 'pod_index'
                        AND `meta_value` = %s
                ", array(
                    $field[' name' ],
                    $pod[ 'id' ],
                    $old_name
                )
            );
        }

        if ( !$save_pod )
            $this->cache_flush_pods( $pod );

        if ( true === $db )
            return $params->id;
        else
            return $field;
    }

    public function save_field_slug_fix ( $slug, $post_ID, $post_status, $post_type, $post_parent = 0, $original_slug = null ) {
        if ( in_array( $post_type, array( '_pods_field', '_pods_pod' ) ) && false !== strpos( $slug, '-' ) ) {
            $slug = explode( '-', $slug );
            $slug = $slug[ 0 ];
        }

        return $slug;
    }

    /**
     * Add or Edit a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     * $params['options'] Associative array of Object options
     *
     * @param array|object $params An associative array of parameters
     * @param bool $sanitized (optional) Decides whether the params have been sanitized before being passed, will sanitize them if false.
     *
     * @return int The Object ID
     * @since 2.0.0
     */
    public function save_object ( $params, $sanitized = false ) {
        $params = (object) $params;

        if ( false === $sanitized )
            $params = pods_sanitize( $params );

        if ( !isset( $params->name ) || empty( $params->name ) )
            return pods_error( __( 'Name must be given to save an Object', 'pods' ), $this );

        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( __( 'Type must be given to save an Object', 'pods' ), $this );

        $object = array(
            'id' => 0,
            'name' => $params->name,
            'type' => $params->type,
            'code' => '',
            'options' => array()
        );

        // Setup options
        $options = get_object_vars( $params );

        if ( isset( $options[ 'method' ] ) )
            unset( $options[ 'method' ] );

        $exclude = array(
            'id',
            'name',
            'helper_type',
            'code',
            'options',
            'status'
        );

        foreach ( $exclude as $k => $exclude_field ) {
            $aliases = array( $exclude_field );

            if ( is_array( $exclude_field ) ) {
                $aliases = array_merge( array( $k ), $exclude_field );
                $exclude_field = $k;
            }

            foreach ( $aliases as $alias ) {
                if ( isset( $options[ $alias ] ) ) {
                    $object[ $exclude_field ] = pods_trim( $options[ $alias ] );

                    unset( $options[ $alias ] );
                }
            }
        }

        if ( 'helper' == $object[ 'type' ] )
            $object[ 'options' ][ 'helper_type' ] = $object[ 'helper_type' ];

        if ( isset( $object[ 'options' ][ 'code' ] ) )
            unset( $object[ 'options' ][ 'code' ] );

        $object[ 'options' ] = array_merge( $object[ 'options' ], $options );

        $post_data = array(
            'post_name' => pods_clean_name( $object[ 'name' ], true),
            'post_title' => $object[ 'name' ],
            'post_content' => $object[ 'code' ],
            'post_type' => '_pods_' . $object[ 'type' ],
            'post_status' => 'publish'
        );

        if ( !empty( $object[ 'id' ] ) )
            $post_data[ 'ID' ] = $object[ 'id' ];

        if ( null !== pods_var( 'status', $object, null, null, true ) )
            $post_data[ 'post_status' ] = pods_var( 'status', $object, null, null, true );

        remove_filter( 'content_save_pre', 'balanceTags', 50 );

        $post_data = pods_sanitize( $post_data );

        $params->id = $this->save_post( $post_data, $object[ 'options' ], true, true );

        pods_transient_clear( 'pods_object_' . $params->type );
        pods_transient_clear( 'pods_object_' . $params->type . '_' . pods_clean_name( $params->name, true ) );

        return $params->id;
    }

    /**
     * @see PodsAPI::save_object
     *
     * Add or edit a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     * $params['code'] string The template code
     *
     * @param array|object $params An associative array of parameters
     * @param bool $sanitized  (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
     *
     * @return int The Template ID
     *
     * @since 1.7.9
     */
    public function save_template ( $params, $sanitized = false ) {
        $params = (object) $params;

        $params->type = 'template';

        return $this->save_object( $params, $sanitized );
    }

    /**
     * @see PodsAPI::save_object
     *
     * Add or edit a Pod Page
     *
     * $params['id'] int The page ID
     * $params['name'] string The page URI
     * $params['code'] string The page code
     *
     * @param array|object $params An associative array of parameters
     * @param bool $sanitized  (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
     *
     * @return int The page ID
     * @since 1.7.9
     */
    public function save_page ( $params, $sanitized = false ) {
        $params = (object) $params;

        if ( !isset( $params->name ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }

        if ( isset( $params->phpcode ) ) {
            $params->code = $params->phpcode;
            unset( $params->phpcode );
        }

        $params->name = trim( $params->name, '/' );
        $params->type = 'page';

        return $this->save_object( $params, $sanitized );
    }

    /**
     * @see PodsAPI::save_object
     *
     * Add or edit a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     * $params['helper_type'] string The helper type ("pre_save", "display", etc)
     * $params['code'] string The helper code
     *
     * @param array $params An associative array of parameters
     * @param bool $sanitized  (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
     *
     * @return int The helper ID
     * @since 1.7.9
     */
    public function save_helper ( $params, $sanitized = false ) {
        $params = (object) $params;

        if ( isset( $params->phpcode ) ) {
            $params->code = $params->phpcode;
            unset( $params->phpcode );
        }

        if ( isset( $params->type ) ) {
            $params->helper_type = $params->type;
            unset( $params->type );
        }

        $params->type = 'helper';

        return $this->save_object( $params, $sanitized );
    }

    /**
     * Add or edit a single pod item
     *
     * $params['pod'] string The Pod name (pod or pod_id is required)
     * $params['pod_id'] string The Pod ID (pod or pod_id is required)
     * $params['id'] int The item ID
     * $params['data'] array (optional) Associative array of field names + values
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array|object $params An associative array of parameters
     *
     * @return int The item ID
     *
     * @since 1.7.9
     */
    public function save_pod_item ( $params ) {
        $params = (object) str_replace( '@wp_', '{prefix}', $params );

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );

            $params->pod = $params->datatype;

            unset( $params->datatype );

            if ( isset( $params->pod_id ) ) {
                pods_deprecated( '$params->id instead of $params->pod_id', '2.0.0' );

                $params->id = $params->pod_id;

                unset( $params->pod_id );
            }

            if ( isset( $params->data ) && !empty( $params->data ) && is_array( $params->data ) ) {
                $check = current( $params->data );

                if ( is_array( $check ) ) {
                    pods_deprecated( 'PodsAPI::save_pod_items', '2.0.0' );

                    return $this->save_pod_items( $params, $params->data );
                }
            }
        }

        // @deprecated 2.0.0
        if ( isset( $params->tbl_row_id ) ) {
            pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0.0' );

            $params->id = $params->tbl_row_id;

            unset( $params->tbl_row_id );
        }

        // @deprecated 2.0.0
        if ( isset( $params->columns ) ) {
            pods_deprecated( '$params->data instead of $params->columns', '2.0.0' );

            $params->data = $params->columns;

            unset( $params->columns );
        }

        if ( !isset( $params->pod ) )
            $params->pod = false;
        if ( isset( $params->pod_id ) )
            $params->pod_id = pods_absint( $params->pod_id );
        else
            $params->pod_id = 0;

        if ( isset( $params->id ) )
            $params->id = pods_absint( $params->id );
        else
            $params->id = 0;

        // Support for bulk edit
        if ( isset( $params->id ) && !empty( $params->id ) && is_array( $params->id ) ) {
            $ids = array();
            $new_params = $params;

            foreach ( $params->id as $id ) {
                $new_params->id = $id;

                $ids[] = $this->save_pod_item( $new_params );
            }

            return $ids;
        }

        // Allow Helpers to know what's going on, are we adding or saving?
        $is_new_item = false;

        if ( empty( $params->id ) )
            $is_new_item = true;

        // Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
        $bypass_helpers = false;

        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers )
            $bypass_helpers = true;

        // Allow Custom Fields not defined by Pods to be saved
        $allow_custom_fields = false;

        if ( isset( $params->allow_custom_fields ) && false !== $params->allow_custom_fields )
            $allow_custom_fields = true;

        // Get array of Pods
        $pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ) );

        if ( false === $pod )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $params->pod = $pod[ 'name' ];
        $params->pod_id = $pod[ 'id' ];

        $fields = $pod[ 'fields' ];

        $object_fields = (array) pods_var_raw( 'object_fields', $pod, array(), null, true );

        $fields_active = array();
        $custom_data = array();

        // Find the active fields (loop through $params->data to retain order)
        if ( !empty( $params->data ) && is_array( $params->data ) ) {
            $custom_fields = array();

            foreach ( $params->data as $field => $value ) {
                if ( isset( $object_fields[ $field ] ) ) {
                    $object_fields[ $field ][ 'value' ] = $value;
                    $fields_active[] = $field;
                }
                elseif ( isset( $fields[ $field ] ) ) {
                    if ( true === PodsForm::permission( $fields[ $field ][ 'type' ], $field, $fields[ $field ], $fields, $pod, $params->id, $params ) ) {
                        $fields[ $field ][ 'value' ] = $value;
                        $fields_active[] = $field;
                    }
                }
                else {
                    $found = false;

                    foreach ( $object_fields as $object_field => $object_field_opt ) {
                        if ( in_array( $field, $object_field_opt[ 'alias' ] ) ) {
                            $object_fields[ $object_field ][ 'value' ] = $value;
                            $fields_active[] = $object_field;

                            $found = true;

                            break;
                        }
                    }

                    if ( $allow_custom_fields && !$found )
                        $custom_fields[] = $field;
                }
            }

            if ( $allow_custom_fields && !empty( $custom_fields ) ) {
                foreach ( $custom_fields as $field ) {
                    $custom_data[ $field ] = $params->data[ $field ];
                }
            }

            unset( $params->data );
        }

        if ( 'pod' == $pod[ 'type' ] ) {
            if ( empty( $params->id ) && !in_array( 'created', $fields_active ) && isset( $fields[ 'created' ] ) ) {
                $fields[ 'created' ][ 'value' ] = date_i18n( 'Y-m-d H:i:s' );
                $fields_active[] = 'created';
            }

            if ( !in_array( 'modified', $fields_active ) && isset( $fields[ 'modified' ] ) ) {
                $fields[ 'modified' ][ 'value' ] = date_i18n( 'Y-m-d H:i:s' );
                $fields_active[] = 'modified';
            }
        }

        // Handle hidden fields
        if ( empty( $params->id ) ) {
            foreach ( $fields as $field => $field_data ) {
                if ( in_array( $field, $fields_active ) )
                    continue;

                if ( true === PodsForm::permission( $fields[ $field ][ 'type' ], $field, $fields[ $field ], $fields, $pod, $params->id, $params ) ) {
                    $value = PodsForm::default_value( pods_var_raw( $field, 'post' ), $field_data[ 'type' ], $field, pods_var_raw( 'options', $field_data, $field_data, null, true ), $pod, $params->id );

                    if ( null !== $value && '' !== $value && false !== $value ) {
                        $fields[ $field ][ 'value' ] = $value;
                        $fields_active[] = $field;
                    }
                }
            }
        }

        $columns =& $fields; // @deprecated 2.0.0
        $active_columns =& $fields_active; // @deprecated 2.0.0
        $params->tbl_row_id =& $params->id;

        $pre_save_helpers = $post_save_helpers = array();

        if ( false === $bypass_helpers ) {
            $pieces = array( 'fields', 'params', 'pod', 'fields_active' );

            // Plugin hooks
            $hooked = $this->do_hook( 'pre_save_pod_item', compact( $pieces ), $is_new_item );

            if ( is_array( $hooked ) && !empty( $hooked ) )
                extract( $hooked );

            $hooked = $this->do_hook( "pre_save_pod_item_{$params->pod}", compact( $pieces ), $is_new_item );

            if ( is_array( $hooked ) && !empty( $hooked ) )
                extract( $hooked );

            if ( false !== $is_new_item ) {
                $hooked = $this->do_hook( 'pre_create_pod_item', compact( $pieces ) );

                if ( is_array( $hooked ) && !empty( $hooked ) )
                    extract( $hooked );

                $hooked = $this->do_hook( "pre_create_pod_item_{$params->pod}", compact( $pieces ) );

                if ( is_array( $hooked ) && !empty( $hooked ) )
                    extract( $hooked );
            }
            else {
                $hooked = $this->do_hook( 'pre_edit_pod_item', compact( $pieces ) );

                if ( is_array( $hooked ) && !empty( $hooked ) )
                    extract( $hooked );

                $hooked = $this->do_hook( "pre_edit_pod_item_{$params->pod}", compact( $pieces ) );

                if ( is_array( $hooked ) && !empty( $hooked ) )
                    extract( $hooked );
            }

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) {
                if ( !empty( $pod[ 'options' ] ) && is_array( $pod[ 'options' ] ) ) {
                    $helpers = array( 'pre_save_helpers', 'post_save_helpers' );

                    foreach ( $helpers as $helper ) {
                        if ( isset( $pod[ 'options' ][ $helper ] ) && !empty( $pod[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $pod[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_save_helpers ) ) {
                    pods_deprecated( sprintf( __( 'Pre-save helpers are deprecated, use the action pods_pre_save_pod_item_%s instead', 'pods' ), $params->pod ), '2.0.0' );

                    foreach ( $pre_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );

                        if ( false !== $helper )
                            eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        $table_data = $table_formats = $update_values = $rel_fields = $rel_field_ids = array();

        $object_type = $pod[ 'type' ];

        $object_ID = 'ID';

        if ( 'comment' == $object_type )
            $object_ID = 'comment_ID';

        $object_data = $object_meta = array();

        if ( !empty( $params->id ) )
            $object_data[ $object_ID ] = $params->id;

        $fields_active = array_unique( $fields_active );

        // Loop through each active field, validating and preparing the table data
        foreach ( $fields_active as $field ) {
            if ( isset( $object_fields[ $field ] ) )
                $field_data = $object_fields[ $field ];
            elseif ( isset( $fields[ $field ] ) )
                $field_data = $fields[ $field ];
            else
                continue;

            $value = $field_data[ 'value' ];
            $type = $field_data[ 'type' ];
            $options = pods_var( 'options', $field_data, array() );

            // WPML AJAX compatibility
            if ( is_admin() && isset( $_GET[ 'page' ] ) && false !== strpos( $_GET[ 'page' ], '/menu/languages.php' ) && isset( $_POST[ 'icl_ajx_action' ] ) && isset( $_POST[ '_icl_nonce' ] ) && wp_verify_nonce( $_POST[ '_icl_nonce' ], $_POST[ 'icl_ajx_action' ] . '_nonce' ) )
                $options[ 'unique' ] = $fields[ $field ][ 'options' ][ 'unique' ] = $options[ 'required' ] = $fields[ $field ][ 'options' ][ 'required' ] = 0;
            else {
                // Validate value
                $validate = $this->handle_field_validation( $value, $field, $object_fields, $fields, $pod, $params );

                if ( false === $validate )
                    $validate = sprintf( __( 'There was an issue validating the field %s', 'pods' ), $field_data[ 'label' ] );

                if ( !is_bool( $validate ) && !empty( $validate ) )
                    return pods_error( $validate, $this );
            }

            $value = PodsForm::pre_save( $field_data[ 'type' ], $value, $params->id, $field, array_merge( $options, $field_data ), array_merge( $fields, $object_fields ), $pod, $params );

            $field_data[ 'value' ] = $value;

            if ( isset( $object_fields[ $field ] ) )
                $object_data[ $field ] = $value;
            else {
                $simple = ( 'pick' == $type && 'custom-simple' == pods_var( 'pick_object', $field_data ) );
                $simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field_data, $field, $fields, $pod, $params );

                // Handle Simple Relationships
                if ( $simple ) {
                    $value = (array) $value;

                    $custom = pods_var_raw( 'pick_custom', $options, '' );

                    $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $field_data[ 'name' ], $value, array_merge( $options, $field_data ), $pod, $params->id );

                    $pick_limit = (int) pods_var_raw( 'pick_limit', $options, 0 );

                    if ( 'single' == pods_var_raw( 'pick_format_type', $options ) )
                        $pick_limit = 1;

                    if ( empty( $value ) || empty( $custom ) )
                        $value = '';
                    elseif ( !empty( $custom ) ) {
                        if ( !is_array( $custom ) ) {
                            $custom = explode( "\n", $custom );

                            $custom_values = array();

                            foreach ( $custom as $c => $cv ) {
                                if ( 0 < strlen( $cv ) ) {
                                    $custom_label = explode( '|', $cv );

                                    if ( !isset( $custom_label[ 1 ] ) )
                                        $custom_label[ 1 ] = $custom_label[ 0 ];

                                    $custom_values[ $custom_label[ 0 ] ] = $custom_label[ 1 ];
                                }
                            }
                        }
                        else
                            $custom_values = $custom;

                        $values = array();

                        foreach ( $value as $k => $v ) {
                            $v = pods_unsanitize( $v );

                            if ( isset( $custom_values[ $v ] ) )
                                $values[ $k ] = $v;
                        }

                        $value = $values;

                        if ( 0 < $pick_limit && !empty( $value ) )
                            $value = array_slice( $value, 0, $pick_limit );
                    }

                    // Don't save an empty array, just make it an empty string
                    if ( empty( $value ) )
                        $value = '';
                    elseif ( is_array( $value ) ) {
                        // If there's just one item, don't save as an array, save the string
                        if ( 1 == $pick_limit || 1 == count( $value ) )
                            $value = implode( '', $value );
                        // If storage is set to table, json encode, otherwise WP will serialize automatically
                        elseif ( 'table' == pods_var( 'storage', $pod ) )
                            $value = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $value, JSON_UNESCAPED_UNICODE ) : json_encode( $value );
                    }
                }

                // Prepare all table (non-relational) data
                if ( !in_array( $type, $tableless_field_types ) || $simple ) {
                    $table_data[ $field ] = str_replace( array( '{prefix}', '@wp_' ), array( '{/prefix/}', '{prefix}' ), $value ); // Fix for pods_query
                    $table_formats[] = PodsForm::prepare( $type, $options );

                    $object_meta[ $field ] = $value;
                }
                // Store relational field data to be looped through later
                else {
                    $rel_fields[ $type ][ $field ] = $value;
                    $rel_field_ids[] = $field_data[ 'id' ];
                }
            }
        }

        if ( 'post_type' == $pod[ 'type' ] ) {
            $post_type = $pod[ 'name' ];

            if ( !empty( $pod[ 'object' ] ) )
                $post_type = $pod[ 'object' ];

            $object_data[ 'post_type' ] = $post_type;
        }

        if ( 'meta' == $pod[ 'storage' ] && !in_array( $pod[ 'type' ], array( 'taxonomy', 'pod', 'table', '' ) ) ) {
            if ( $allow_custom_fields && !empty( $custom_data ) )
                $object_meta = array_merge( $custom_data, $object_meta );

            $params->id = $this->save_wp_object( $object_type, $object_data, $object_meta, false, true );
        }
        else {
            if ( !in_array( $pod[ 'type' ], array( 'taxonomy', 'pod', 'table', '' ) ) )
                $params->id = $this->save_wp_object( $object_type, $object_data, array(), false, true );
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                $term = pods_var( $object_fields[ 'name' ][ 'name' ], $object_data, '', null, true );
                $term_data = array();

                if ( empty( $params->id ) || !empty( $term_data ) ) {
                    $taxonomy = $pod[ 'name' ];

                    if ( !empty( $pod[ 'object' ] ) )
                        $taxonomy = $pod[ 'object' ];

                    $params->id = $this->save_term( $params->id, $term, $taxonomy, $term_data, true );
                }
            }

            if ( 'table' == $pod[ 'storage' ] ) {
                // Every row should have an id set here, otherwise Pods with nothing
                // but relationship fields won't get properly ID'd
                if ( empty( $params->id ) )
                    $params->id = 0;

                $table_data = array( 'id' => $params->id ) + $table_data;
                array_unshift( $table_formats, '%d' );

                if ( !empty( $table_data ) ) {
                    $sql = pods_data()->insert_on_duplicate( "@wp_pods_{$params->pod}", $table_data, $table_formats );

                    $id = pods_query( $sql, 'Cannot add/save table row' );

                    if ( empty( $params->id ) )
                        $params->id = $id;
                }
            }
        }

        pods_no_conflict_on( 'post' );

        // Save relational field data
        if ( !empty( $rel_fields ) ) {
            // E.g. $rel_fields['pick']['related_events'] = '3,15';
            foreach ( $rel_fields as $type => $data ) {
                // Only handle tableless fields
                if ( !in_array( $type, $tableless_field_types ) )
                    continue;

                if ( !is_array( $data ) )
                    $data = explode( ',', $data );

                foreach ( $data as $field => $values ) {
                    $field_id = pods_absint( $fields[ $field ][ 'id' ] );

                    // Convert values from a comma-separated string into an array
                    if ( !is_array( $values ) )
                        $values = explode( ',', $values );

                    // Enforce integers / unique values for IDs
                    if ( 'pick' != $type || 'custom-simple' != $fields[ $field ][ 'pick_object' ] ) {
                        $new_values = array();

                        foreach ( $values as $k => $v ) {
                            if ( !empty( $v ) ) {
                                if ( !is_array( $v ) ) {
                                    $v = (int) $v;

                                    if ( !empty( $v ) && !in_array( $v, $new_values ) )
                                        $new_values[] = $v;
                                }
                                elseif ( 'file' == $type )
                                    $new_values[] = $v;
                            }
                        }

                        $values = $new_values;
                    }

                    // Save relationships to meta if meta-based
                    if ( in_array( $pod[ 'type' ], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
                        $object_type = $pod[ 'type' ];

                        if ( 'post_type' == $object_type || 'media' == $object_type )
                            $object_type = 'post';

                        if ( 'pick' != $type || 'custom-simple' != $fields[ $field ][ 'pick_object' ] ) {
                            delete_metadata( $object_type, $params->id, $field, '', true );

                            if ( !empty( $values ) ) {
                                update_metadata( $object_type, $params->id, '_pods_' . $field, $values );

                                foreach ( $values as $v ) {
                                    add_metadata( $object_type, $params->id, $field, $v );
                                }
                            }
                            else
                                delete_metadata( $object_type, $params->id, '_pods_' . $field, '', true );
                        }
                        elseif ( !empty( $values ) )
                            update_metadata( $object_type, $params->id, $field, $values );
                        else
                            delete_metadata( $object_type, $params->id, $field, '', true );
                    }

                    $related_pod_id = $related_field_id = 0;

                    $related_pod = $related_field = false;

                    if ( 'pick' == $type && 'custom-simple' != $fields[ $field ][ 'pick_object' ] ) {
                        $pick_object = pods_var( 'pick_object', $fields[ $field ], '' ); // pod, post_type, taxonomy, etc..
                        $pick_val = pods_var( 'pick_val', $fields[ $field ], '' ); // pod name, post type name, taxonomy name, etc..
                        $pick_sister_id = (int) pods_var( 'sister_id', $fields[ $field ], 0 );

                        if ( 'pod' == $pick_object ) {
                            $related_pod = $this->load_pod( array( 'name' => $pick_val ) );

                            if ( false !== $related_pod )
                                $related_pod_id = $related_pod[ 'id' ];

                            if ( 0 < $pick_sister_id ) {
                                foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
                                    if ( 'pick' == $related_pod_field[ 'type' ] && $pick_sister_id == $related_pod_field[ 'id' ] ) {
                                        $related_field_id = $related_pod_field[ 'id' ];
                                        $related_field = $related_pod_field[ 'name' ];

                                        break;
                                    }
                                }
                            }

                            // @todo Delete sister relationships in meta

                            foreach ( $values as $id ) {
                                if ( !empty( $related_pod_id ) && !empty( $related_field_id ) ) {
                                    if ( in_array( $related_pod[ 'type' ], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
                                        $object_type = $related_pod[ 'type' ];

                                        if ( 'post_type' == $object_type || 'media' == $object_type )
                                            $object_type = 'post';

                                        $ids = get_metadata( $object_type, $id, '_pods_' . $related_field, true );

                                        if ( empty( $ids ) )
                                            $ids = get_metadata( $object_type, $id, $related_field, true );

                                        if ( empty( $ids ) )
                                            $ids = array( $params->id );
                                        else {
                                            if ( !is_array( $ids ) )
                                                $ids = array( $ids );

                                            if ( !in_array( $params->id, $ids ) )
                                                $ids[] = $params->id;
                                        }

                                        delete_metadata( $object_type, $id, $related_field, '', true );

                                        if ( !empty( $ids ) ) {
                                            update_metadata( $object_type, $id, '_pods_' . $related_field, $values );

                                            foreach ( $ids as $rel_id ) {
                                                add_metadata( $object_type, $id, $related_field, $rel_id );
                                            }
                                        }
                                        else
                                            delete_metadata( $object_type, $id, '_pods_' . $related_field, '', true );
                                    }
                                }
                            }
                        }
                    }

                    if ( 'pick' != $type || 'custom-simple' != $fields[ $field ][ 'pick_object' ] ) {
                        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
                            if ( !empty( $values ) ) {
                                $values_to_impode = array();
                                foreach ( $values as $id ) {
                                    if ( is_array( $id ) )
                                        $values_to_impode[] = $id[ 'id' ];
                                    else
                                        $values_to_impode[] = $id;
                                }
                                $value_ids = implode( ',', $values_to_impode );
                            }
                            else {
                                $value_ids = '0';
                            }

                            // Remove relationships
                            $sql = "
                                DELETE FROM `@wp_podsrel`
                                WHERE
                                    (
                                        `pod_id` = {$params->pod_id}
                                        AND `field_id` = {$field_id}
                                        AND `item_id` = {$params->id}
                                    ) OR (
                                        `related_pod_id` = {$params->pod_id}
                                        AND `related_field_id` = {$field_id}
                                        AND `related_item_id` = {$params->id}
                                        AND `item_id` NOT IN ( {$value_ids} )
                                    )
                            ";

                            pods_query( $sql, false );
                        }

                        if ( empty( $values ) )
                            continue;

                        // File relationships
                        if ( 'file' == $type ) {
                            $weight = 0;

                            foreach ( $values as $id ) {
                                $title = false;

                                if ( is_array( $id ) ) {
                                    if ( isset( $id[ 'title' ] ) && 0 < strlen( trim( $id[ 'title' ] ) ) )
                                        $title = trim( $id[ 'title' ] );

                                    if ( isset( $id[ 'id' ] ) )
                                        $id = $id[ 'id' ];
                                }

                                if ( empty( $id ) )
                                    continue;

                                // Update the title if set
                                if ( false !== $title && 1 == pods_var( $fields[ $field ][ 'type' ] . '_edit_title', $fields[ $field ][ 'options' ], 0 ) ) {
                                    $attachment_data = array(
                                        'ID' => $id,
                                        'post_title' => $title
                                    );

                                    $this->save_wp_object( 'media', $attachment_data );
                                }

                                if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
                                    pods_query( "INSERT INTO `@wp_podsrel` (`pod_id`, `field_id`, `item_id`, `related_item_id`, `weight`) VALUES (%d, %d, %d, %d, %d)", array(
                                        $params->pod_id,
                                        $field_id,
                                        $params->id,
                                        $id,
                                        $weight
                                    ) );

                                    $weight++;
                                }
                            }
                        }
                        // Pick relationships
                        elseif ( 'pick' == $type && ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) ) {
                            // Add relationship values
                            $weight = 0;

                            foreach ( $values as $id ) {
                                if ( !empty( $related_pod_id ) && !empty( $related_field_id ) ) {
                                    $related_weight = 0;

                                    $related_ids = pods_query( "SELECT `related_item_id`, `weight` FROM `@wp_podsrel` WHERE `pod_id` = %d AND `field_id` = %d AND `item_id` = %d ORDER BY `weight` DESC", array(
                                        $related_pod_id,
                                        $related_field_id,
                                        $id
                                    ) );

                                    $save_related = true;

                                    foreach ( $related_ids as $related_id_data ) {
                                        if ( $params->id == $related_id_data->related_item_id ) {
                                            $save_related = false;

                                            break;
                                        }
                                        elseif ( empty( $related_weight ) )
                                            $related_weight = ( (int) $related_id_data->weight ) + 1;
                                    }

                                    if ( $save_related ) {
                                        pods_query( "INSERT INTO `@wp_podsrel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES (%d, %d, %d, %d, %d, %d, %d)", array(
                                            $related_pod_id,
                                            $related_field_id,
                                            $id,
                                            $params->pod_id,
                                            $field_id,
                                            $params->id,
                                            $related_weight
                                        ) );
                                    }
                                }

                                pods_query( "INSERT INTO `@wp_podsrel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES (%d, %d, %d, %d, %d, %d, %d)", array(
                                    $params->pod_id,
                                    $field_id,
                                    $params->id,
                                    $related_pod_id,
                                    $related_field_id,
                                    $id,
                                    $weight
                                ) );

                                $weight++;
                            }
                        }
                    }
                }
            }
        }

        pods_no_conflict_off( 'post' );

        if ( false === $bypass_helpers ) {
            $pieces = array( 'fields', 'params', 'pod', 'fields_active' );

            $pieces = compact( $pieces );

            // Plugin hooks
            $this->do_hook( 'post_save_pod_item', $pieces, $is_new_item );
            $this->do_hook( "post_save_pod_item_{$params->pod}", $pieces, $is_new_item );

            if ( false !== $is_new_item ) {
                $this->do_hook( 'post_create_pod_item', $pieces );
                $this->do_hook( "post_create_pod_item_{$params->pod}", $pieces );
            }
            else {
                $this->do_hook( 'post_edit_pod_item', $pieces );
                $this->do_hook( "post_edit_pod_item_{$params->pod}", $pieces );
            }

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) {
                if ( !empty( $post_save_helpers ) ) {
                    pods_deprecated( sprintf( __( 'Post-save helpers are deprecated, use the action pods_post_save_pod_item_%s instead', 'pods' ), $params->pod ), '2.0.0' );

                    foreach ( $post_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );

                        if ( false !== $helper && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) )
                            eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        // Clear cache
        pods_cache_clear( $params->id, 'pods_items_' . $params->pod );

        // Success! Return the id
        return $params->id;
    }

    /**
     * @see PodsAPI::save_pod_item
     * Add multiple pod items
     *
     * $params['pod'] string The Pod name (pod or pod_id is required)
     * $params['pod_id'] string The Pod ID (pod or pod_id is required)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * $data['id'] int The item ID (optional)
     * $data['data'] array An associative array of field names + values
     *
     * @param array|object $params An associative array of parameters, data excluded
     * @param array $data An associative array of pod ids and field names + values (arrays of field data)
     *
     * @return int The item ID
     * @since 2.0.0
     */
    public function save_pod_items ( $params, $data ) {
        $params = (object) $params;

        $ids = array();

        foreach ( $data as $fields ) {
            $params->data = $fields;

            if ( isset( $fields[ 'id' ] ) && isset( $fields[ 'data' ] ) ) {
                $params->id = $fields[ 'id' ];
                $params->data = $fields[ 'data' ];
            }

            $ids[] = $this->save_pod_item( $params );
        }

        return $ids;
    }

    /**
     * @see PodsAPI::save_pod_item
     *
     * Duplicate a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_* table
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function duplicate_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( array( 'name' => $params->pod ) );

        if ( false === $pod )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $fields = $pod[ 'fields' ];
        $params->pod = $pod[ 'name' ];
        $params->pod_id = $pod[ 'id' ];

        $pod = pods( $params->pod, $params->id );

        $params = array(
            'pod' => $params->pod,
            'data' => array()
        );

        foreach ( $fields as $field ) {
            $field = $field[ 'name' ];

            if ( 'pick' == $field[ 'type' ] ) {
                $field = $field . '.id';

                if ( 'taxonomy' == $field[ 'pick_object' ] )
                    $field = $field . '.term_id';
            }

            if ( 'file' == $field[ 'type' ] )
                $field = $field . '.ID';

            $value = $pod->field( $field );

            if ( 0 < strlen( $value ) )
                $params[ 'data' ][ $field[ 'name' ] ] = $value;
        }

        $params = $this->do_hook( 'duplicate_pod_item', $params, $pod->pod, $pod->field( 'id' ) );
        $id = $this->save_pod_item( $params );

        return $id;
    }

    /**
     * @see pods()
     *
     * Export a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_* table
     * $params['fields'] array The fields to export
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function export_pod_item ( $params ) {
        $fields = array();

        if ( is_object( $params ) && 'Pods' == get_class( $params ) ) {
            $pod = $params;
        }
        else {
            $params = (object) pods_sanitize( $params );

            $pod = pods( $params->pod, $params->id, false );

            if ( empty( $pod ) )
                return false;

            $fields = (array) pods_var_raw( 'fields', $params, array(), null, true );
        }

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $object_fields = (array) pods_var_raw( 'object_fields', $pod->pod_data, array(), null, true );

        if ( empty( $fields ) ) {
            $fields = $pod->fields;
            $fields = array_merge( $object_fields, $fields );
        }

        foreach ( $fields as $k => $field ) {
            if ( !is_array( $field ) )
                $field = array( 'name' => $field );

            if ( isset( $pod->fields[ $field[ 'name' ] ] ) ) {
                $field = $pod->fields[ $field[ 'name' ] ];
                $field[ 'lookup_name' ] = $field[ 'name' ];

                if ( in_array( $field[ 'type' ], $tableless_field_types ) ) {
                    if ( 'pick' == $field[ 'type' ] ) {
                        if ( empty( $field[ 'table_info' ] ) )
                            $field[ 'table_info' ] = $this->get_table_info( pods_var_raw( 'pick_object', $field ), pods_var_raw( 'pick_val', $field ) );

                        if ( !empty( $field[ 'table_info' ] ) )
                            $field[ 'lookup_name' ] .= '.' . $field[ 'table_info' ][ 'field_id' ];
                    }
                    else
                        $field[ 'lookup_name' ] .= '.guid';
                }
            }
            elseif ( isset( $object_fields[ $field[ 'name' ] ] ) ) {
                $field = $object_fields[ $field[ 'name' ] ];
                $field[ 'lookup_name' ] = $field[ 'name' ];
            }
            else
                unset( $fields[ $k ] );
        }

        $data = array();

        foreach ( $fields as $field ) {
            $data[ $field[ 'name' ] ] = $pod->field( $field[ 'name' ] );
        }

        $data = $this->do_hook( 'export_pod_item', $data, $pod->pod, $pod->id(), $pod, $fields, $object_fields );

        return $data;
    }

    /**
     * Reorder a Pod
     *
     * $params['pod'] string The Pod name
     * $params['field'] string The field name of the field to reorder
     * $params['order'] array The key => value array of items to reorder (key should be an integer)
     *
     * @param array $params An associative array of parameters
     *
     * @return bool
     *
     * @since 1.9.0
     */
    public function reorder_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( __( '$params->pod instead of $params->datatype', 'pods' ), '2.0.0' );

            $params->pod = $params->datatype;

            unset( $params->datatype );
        }

        if ( null === pods_var_raw( 'pod', $params, null, null, true ) )
            return pods_error( __( '$params->pod is required', 'pods' ), $this );

        if ( !is_array( $params->order ) )
            $params->order = explode( ',', $params->order );

        $pod = $this->load_pod( array( 'name' => $params->pod ) );

        $params->name = $pod[ 'name' ];

        if ( false === $pod )
            return pods_error( __( 'Pod is required', 'pods' ), $this );

        foreach ( $params->order as $order => $id ) {
            if ( isset( $pod[ 'fields' ][ $params->field ] ) || isset( $pod[ 'object_fields' ][ $params->field ] ) ) {
                if ( 'table' == $pod[ 'storage' ] && ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) ) {
                    if ( isset( $pod[ 'fields' ][ $params->field ] ) )
                        pods_query( "UPDATE `@wp_pods_{$params->name}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `id` = " . pods_absint( $id ) . " LIMIT 1" );
                    else
                        pods_query( "UPDATE `{$pod['table']}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `{$pod['field_id']}` = " . pods_absint( $id ) . " LIMIT 1" );
                }
                else
                    $this->save_pod_item( array( 'pod' => $params->pod, 'pod_id' => $params->pod_id, 'id' => $id, 'data' => array( $params->field => pods_absint( $order ) ) ) );
            }
        }

        return true;
    }

    /**
     *
     * Delete all content for a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @param array $pod Pod data
     *
     * @return bool
     *
     * @uses pods_query
     * @uses pods_cache_clear
     *
     * @since 1.9.0
     */
    public function reset_pod ( $params, $pod = false ) {
        $params = (object) pods_sanitize( $params );

        if ( empty( $pod ) )
            $pod = $this->load_pod( $params );

        if ( false === $pod )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            if ( 'table' == $pod[ 'storage' ] ) {
                try {
                    pods_query( "TRUNCATE `@wp_pods_{$params->name}`", false );
                }
                catch ( Exception $e ) {
                    // Allow pod to be reset if the table doesn't exist
                    if ( false === strpos( $e->getMessage(), 'Unknown table' ) )
                        return pods_error( $e->getMessage(), $this );
                }
            }

            pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
        }

        // @todo Delete relationships from tableless relationships

        // Delete all posts/revisions from this post type
        if ( in_array( $pod[ 'type' ], array( 'post_type', 'media' ) ) ) {
            $type = pods_var( 'object', $pod, $pod[ 'name' ], null, true );

            $sql = "
                DELETE `t`, `r`, `m`
                FROM `{$pod['table']}` AS `t`
                LEFT JOIN `{$pod['meta_table']}` AS `m`
                    ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
                LEFT JOIN `{$pod['table']}` AS `r`
                    ON `r`.`post_parent` = `t`.`{$pod['field_id']}` AND `r`.`post_status` = 'inherit'
                WHERE `t`.`{$pod['field_type']}` = '{$type}'
            ";

            pods_query( $sql, false );
        }
        // Delete all terms from this taxonomy
        elseif ( 'taxonomy' == $pod[ 'type' ] ) {
            $type = pods_var( 'object', $pod, $pod[ 'name' ], null, true );

            $sql = "
                DELETE FROM `{$pod['table']}` AS `t`
                " . $pod['join']['tt'] . "
                WHERE " . implode( ' AND ', $pod['where'] ) . "
            ";

            pods_query( $sql, false );
        }
        // Delete all users except the current one
        elseif ( 'user' == $pod[ 'type' ] ) {
            $sql = "
                DELETE `t`, `m`
                FROM `{$pod['table']}` AS `t`
                LEFT JOIN `{$pod['meta_table']}` AS `m`
                    ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
                WHERE `t`.`{$pod['field_id']}` != " . (int) get_current_user_id() . "
            ";

            pods_query( $sql, false );
        }
        // Delete all comments
        elseif ( 'comment' == $pod[ 'type' ] ) {
            $type = pods_var( 'object', $pod, $pod[ 'name' ], null, true );

            $sql = "
                DELETE `t`, `m`
                FROM `{$pod['table']}` AS `t`
                LEFT JOIN `{$pod['meta_table']}` AS `m`
                    ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
                WHERE `t`.`{$pod['field_type']}` = '{$type}'
            ";

            pods_query( $sql, false );
        }

        pods_cache_clear( true ); // only way to reliably clear out cached data across an entire group

        return true;
    }

    /**
     * Delete a Pod and all its content
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @param bool $strict (optional) Makes sure a pod exists, if it doesn't throws an error
     * @param bool $delete_all (optional) Whether to delete all content from a WP object
     *
     * @uses PodsAPI::load_pod
     * @uses wp_delete_post
     * @uses pods_query
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_pod ( $params, $strict = false, $delete_all = false ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( !is_object( $params ) && !is_array( $params ) ) {
            if ( is_numeric( $params ) )
                $params = array( 'id' => $params );
            else
                $params = array( 'name' => $params );

            $params = pods_sanitize( $params );
        }
        else
            $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( $params, $strict );

        if ( empty( $pod ) ) {
            if ( false !== $strict )
                return pods_error( __( 'Pod not found', 'pods' ), $this );

            return false;
        }

        $params->id = (int) $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        foreach ( $pod[ 'fields' ] as $field ) {
            $field[ 'pod' ] = $pod;

            $this->delete_field( $field, false );
        }

        // Only delete the post once the fields are taken care of, it's not required anymore
        $success = wp_delete_post( $params->id );

        if ( !$success )
            return pods_error( __( 'Pod unable to be deleted', 'pods' ), $this );

        // Reset content
        if ( $delete_all )
            $this->reset_pod( $params, $pod );

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            if ( 'table' == $pod[ 'storage' ] ) {
                try {
                    pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`", false );
                }
                catch ( Exception $e ) {
                    // Allow pod to be deleted if the table doesn't exist
                    if ( false === strpos( $e->getMessage(), 'Unknown table' ) )
                        return pods_error( $e->getMessage(), $this );
                }
            }

            pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
        }

        // @todo Delete relationships from tableless relationships

        // Delete any relationship references
        $sql = "
            DELETE `pm`
            FROM `{$wpdb->postmeta}` AS `pm`
            LEFT JOIN `{$wpdb->posts}` AS `p`
                ON `p`.`post_type` = '_pods_field'
                    AND `p`.`ID` = `pm`.`post_id`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm2`
                ON `pm2`.`meta_key` = 'pick_object'
                    AND `pm2`.`meta_value` = 'pod'
                    AND `pm2`.`post_id` = `pm`.`post_id`
            WHERE
                `p`.`ID` IS NOT NULL
                AND `pm2`.`meta_id` IS NOT NULL
                AND `pm`.`meta_key` = 'pick_val'
                AND `pm`.`meta_value` = '{$params->name}'
        ";

        pods_query( $sql );

        $this->cache_flush_pods( $pod );

        return true;
    }

    /**
     * Drop a field within a Pod
     *
     * $params['id'] int The field ID
     * $params['name'] int The field name
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     *
     * @param array $params An associative array of parameters
     * @param bool $table_operation Whether or not to handle table operations
     *
     * @uses PodsAPI::load_field
     * @uses wp_delete_post
     * @uses pods_query
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_field ( $params, $table_operation = true ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) )
            $params->pod = '';

        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;

        $pod = $params->pod;

        $save_pod = false;

        if ( !is_array( $pod ) )
            $pod = $this->load_pod( array( 'name' => $pod, 'id' => $params->pod_id ) );
        else
            $save_pod = true;

        if ( empty( $pod ) )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        if ( !isset( $params->name ) )
            $params->name = '';

        if ( !isset( $params->id ) )
            $params->id = 0;

        $field = $this->load_field( array( 'name' => $params->name, 'id' => $params->id ) );

        if ( false === $field )
            return pods_error( __( 'Field not found', 'pods' ), $this );

        $params->id = $field[ 'id' ];
        $params->name = $field[ 'name' ];

        $simple = ( 'pick' == $field[ 'type' ] && 'custom-simple' == pods_var( 'pick_object', $field ) );
        $simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field, $pod, $params );

        if ( $table_operation && 'table' == $pod[ 'storage' ] && ( !in_array( $field[ 'type' ], $tableless_field_types ) || $simple ) )
            pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$params->name}`", false );

        $success = wp_delete_post( $params->id );

        if ( !$success )
            return pods_error( __( 'Field unable to be deleted', 'pods' ), $this );

        $wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p
                ON p.post_type = '_pods_field' AND p.ID = pm.post_id
            WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d", $params->id ) );

        if ( ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) && $table_operation ) {
            pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})", false );
        }

        // @todo Delete tableless relationship meta

        if ( true === $save_pod )
            $this->cache_flush_pods( $pod );

        return true;
    }

    /**
     * Drop a Pod Object
     *
     * $params['id'] int The object ID
     * $params['name'] string The object name
     * $params['type'] string The object type
     *
     * @param array|object $params An associative array of parameters
     *
     * @uses wp_delete_post
     *
     * @return bool
     * @since 2.0.0
     */
    public function delete_object ( $params ) {
        $params = (object) $params;
        $object = $this->load_object( $params );

        if ( empty( $object ) )
            return pods_error( sprintf( __( "%s Object not found", 'pods' ), ucwords( $params->type ) ), $this );

        $success = wp_delete_post( $params->id );

        if ( !$success )
            return pods_error( sprintf( __( "%s Object not deleted", 'pods' ), ucwords( $params->type ) ), $this );

        pods_transient_clear( 'pods_object_' . $params->type );

        if ( isset( $params->name ) )
            pods_transient_clear( 'pods_object_' . $params->type . '_' . $object[ 'slug' ] );

        return true;
    }

    /**
     * @see PodsAPI::delete_object
     *
     * Drop a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->delete_object( $params );
    }

    /**
     * @see PodsAPI::delete_object
     *
     * Drop a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_page ( $params ) {
        $params = (object) $params;
        if ( isset( $params->uri ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        if ( isset( $params->name ) )
            $params->name = trim( $params->name, '/' );
        $params->type = 'page';
        return $this->delete_object( $params );
    }

    /**
     * @see PodsAPI::delete_object
     *
     * Drop a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->delete_object( $params );
    }

    /**
     * Drop a single pod item
     *
     * $params['id'] int (optional) The item's ID from the wp_pod_* table (used with datatype parameter)
     * $params['pod'] string (optional) The Pod name (used with id parameter)
     * $params['pod_id'] int (optional) The Pod ID (used with id parameter)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     * @param bool $wp Whether to run WP object delete action
     *
     * @return bool
     * @since 1.7.9
     */
    public function delete_pod_item ( $params, $wp = true ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype_id ) || isset( $params->datatype ) || isset( $params->tbl_row_id ) ) {
            if ( isset( $params->tbl_row_id ) ) {
                pods_deprecated( __( '$params->id instead of $params->tbl_row_id', 'pods' ), '2.0.0' );
                $params->id = $params->tbl_row_id;
                unset( $params->tbl_row_id );
            }

            if ( isset( $params->pod_id ) ) {
                pods_deprecated( __( '$params->id instead of $params->pod_id', 'pods' ), '2.0.0' );
                $params->id = $params->pod_id;
                unset( $params->pod_id );
            }

            if ( isset( $params->dataype_id ) ) {
                pods_deprecated( __( '$params->pod_id instead of $params->datatype_id', 'pods' ), '2.0.0' );
                $params->pod_id = $params->dataype_id;
                unset( $params->dataype_id );
            }

            if ( isset( $params->datatype ) ) {
                pods_deprecated( __( '$params->pod instead of $params->datatype', 'pods' ), '2.0.0' );
                $params->pod = $params->datatype;
                unset( $params->datatype );
            }
        }

        if ( !isset( $params->id ) )
            return pods_error( __( 'Pod Item not found', 'pods' ), $this );

        $params->id = pods_absint( $params->id );

        if ( !isset( $params->pod ) )
            $params->pod = '';

        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;

        $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );

        if ( false === $pod )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        // Allow Helpers to bypass subsequent helpers in recursive delete_pod_item calls
        $bypass_helpers = false;

        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers )
            $bypass_helpers = true;

        $pre_delete_helpers = $post_delete_helpers = array();

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'pre_delete_pod_item', $params, $pod );
            $this->do_hook( "pre_delete_pod_item_{$params->pod}", $params, $pod );

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) {
                if ( !empty( $pod[ 'options' ] ) && is_array( $pod[ 'options' ] ) ) {
                    $helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );

                    foreach ( $helpers as $helper ) {
                        if ( isset( $pod[ 'options' ][ $helper ] ) && !empty( $pod[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $pod[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_delete_helpers ) ) {
                    pods_deprecated( sprintf( __( 'Pre-delete helpers are deprecated, use the action pods_pre_delete_pod_item_%s instead', 'pods' ), $params->pod ), '2.0.0' );

                    foreach ( $pre_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );

                        if ( false !== $helper )
                            eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        if ( 'table' == $pod[ 'storage' ] )
            pods_query( "DELETE FROM `@wp_pods_{$params->pod}` WHERE `id` = {$params->id} LIMIT 1" );

        if ( $wp && 'taxonomy' == $pod[ 'type' ] ) {
            $taxonomy = $pod[ 'name' ];

            if ( !empty( $pod[ 'object' ] ) )
                $taxonomy = $pod[ 'object' ];

            wp_delete_term( $params->id, $taxonomy );
        }
        elseif ( $wp && !in_array( $pod[ 'type' ], array( 'pod', 'table', '', 'taxonomy' ) ) )
            $this->delete_wp_object( $pod[ 'type' ], $params->id );

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS )
            pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `item_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_item_id` = {$params->id})", false );

        // @todo Delete tableless relationship meta where related

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'post_delete_pod_item', $params, $pod );
            $this->do_hook( "post_delete_pod_item_{$params->pod}", $params, $pod );

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) {
                if ( !empty( $post_delete_helpers ) ) {
                    pods_deprecated( sprintf( __( 'Post-delete helpers are deprecated, use the action pods_post_delete_pod_item_%s instead', 'pods' ), $params->pod ), '2.0.0' );

                    foreach ( $post_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );

                        if ( false !== $helper )
                            eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        pods_cache_clear( $params->id, 'pods_items_' . $params->pod );

        return true;
    }

    /**
     * Check if a Pod exists
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     *
     * @return bool True if exists
     *
     * @since 1.12
     */
    public function pod_exists ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !empty( $params->id ) || !empty( $params->name ) ) {
            if ( !isset( $params->name ) )
                $pod = get_post( $dummy = (int) $params->id );
            else {
                $pod = get_posts( array(
                    'name' => $params->name,
                    'post_type' => '_pods_pod',
                    'posts_per_page' => 1
                ) );
            }

            if ( !empty( $pod ) )
                return true;
        }

        return false;
    }

    /**
     * Load a Pod and all of its fields
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array|object $params An associative array of parameters or pod name as a string
     * @param bool $strict Makes sure the pod exists, throws an error if it doesn't work
     *
     * @return array|bool|mixed|void
     * @since 1.7.9
     */
    public function load_pod ( $params, $strict = true ) {
        if ( !is_array( $params ) && !is_object( $params ) )
            $params = array( 'name' => $params );

        if ( is_object( $params ) && isset( $params->post_name ) ) {
            $pod = pods_transient_get( 'pods_pod_' . $params->post_name );

            if ( false !== $pod )
                return $pod;

            $_pod = get_object_vars( $params );
        }
        else {
            $params = (object) pods_sanitize( $params );

            if ( ( !isset( $params->id ) || empty( $params->id ) ) && ( !isset( $params->name ) || empty( $params->name ) ) )
                return pods_error( 'Either Pod ID or Name are required', $this );

            if ( isset( $params->name ) ) {
                $pod = pods_transient_get( 'pods_pod_' . $params->name );

                if ( false !== $pod )
                    return $pod;
            }

            if ( !isset( $params->name ) )
                $pod = get_post( $dummy = (int) $params->id );
            else {
                $pod = get_posts( array(
                    'name' => $params->name,
                    'post_type' => '_pods_pod',
                    'posts_per_page' => 1
                ) );
            }

            if ( empty( $pod ) ) {
                if ( $strict )
                    return pods_error( __( 'Pod not found', 'pods' ), $this );

                return false;
            }

            if ( is_array( $pod ) )
                $pod = $pod[ 0 ];

            $_pod = get_object_vars( $pod );
        }

        $pod = pods_transient_get( 'pods_pod_' . $_pod[ 'post_name' ] );

        if ( false !== $pod )
            return $pod;

        $pod = array(
            'id' => $_pod[ 'ID' ],
            'name' => $_pod[ 'post_name' ],
            'label' => $_pod[ 'post_title' ],
            'description' => $_pod[ 'post_content' ]
        );

        if ( strlen( $pod[ 'label' ] ) < 1 )
            $pod[ 'label' ] = $pod[ 'name' ];

        // @todo update with a method to put all options in
        $defaults = array(
            'show_in_menu' => 1,
            'type' => 'post_type',
            'storage' => 'meta',
            'object' => '',
            'alias' => ''
        );

        $pod[ 'options' ] = get_post_meta( $pod[ 'id' ] );

        foreach ( $pod[ 'options' ] as $option => &$value ) {
            if ( is_array( $value ) && 1 == count( $value ) && isset( $value[ 0 ] ) )
                $value = $value[ 0 ];
        }

        $pod[ 'options' ] = array_merge( $defaults, $pod[ 'options' ] );

        $pod[ 'type' ] = $pod[ 'options' ][ 'type' ];
        $pod[ 'storage' ] = $pod[ 'options' ][ 'storage' ];
        $pod[ 'object' ] = $pod[ 'options' ][ 'object' ];
        $pod[ 'alias' ] = $pod[ 'options' ][ 'alias' ];

        unset( $pod[ 'options' ][ 'type' ] );
        unset( $pod[ 'options' ][ 'storage' ] );
        unset( $pod[ 'options' ][ 'object' ] );
        unset( $pod[ 'options' ][ 'alias' ] );

        $pod = array_merge( $this->get_table_info( $pod[ 'type' ], $pod[ 'object' ], $pod[ 'name' ], $pod ), $pod );

        $pod[ 'fields' ] = array();

        $pod[ 'object_fields' ] = array();

        if ( 'pod' != $pod[ 'type' ] )
            $pod[ 'object_fields' ] = $this->get_wp_object_fields( $pod[ 'type' ], $pod );

        $fields = get_posts( array(
            'post_type' => '_pods_field',
            'posts_per_page' => -1,
            'nopaging' => true,
            'post_parent' => $pod[ 'id' ],
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ) );

        if ( !empty( $fields ) ) {
            foreach ( $fields as $field ) {
                $field->pod = $pod[ 'name' ];

                $field = $this->load_field( $field );

                $field = PodsForm::field_setup( $field, null, $field[ 'type' ] );

                $pod[ 'fields' ][ $field[ 'name' ] ] = $field;
            }
        }

        pods_transient_set( 'pods_pod_' . $pod[ 'name' ], $pod );

        return $pod;
    }

    /**
     * Load a list of Pods based on filters specified.
     *
     * $params['type'] string/array Pod Type(s) to filter by
     * $params['object'] string/array Pod Object(s) to filter by
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of Pods to return
     * $params['where'] string WHERE clause of query
     * $params['ids'] string|array IDs of Objects
     *
     * @param array $params An associative array of parameters
     *
     * @return array|mixed
     *
     * @uses PodsAPI::load_pod
     *
     * @since 2.0.0
     */
    public function load_pods ( $params = null ) {
        $params = (object) pods_sanitize( $params );

        $order = 'ASC';
        $orderby = 'menu_order title';
        $limit = -1;
        $ids = false;

        $meta_query = array();
        $cache_key = '';

        if ( isset( $params->type ) && !empty( $params->type ) ) {
            if ( !is_array( $params->type ) )
                $params->type = array( trim( $params->type ) );

            sort( $params->type );

            $meta_query[] = array(
                'key' => 'type',
                'value' => $params->type,
                'compare' => 'IN'
            );

            if ( 1 == count( $params->type ) )
                $cache_key .= '_type_' . trim( implode( '', $params->type ) );
        }

        if ( isset( $params->object ) && !empty( $params->object ) ) {
            if ( !is_array( $params->object ) )
                $params->object = array( $params->object );

            $params->object = pods_trim( $params->object );

            sort( $params->object );

            $meta_query[] = array(
                'key' => 'object',
                'value' => $params->object,
                'compare' => 'IN'
            );

            if ( 1 == count( $params->object ) )
                $cache_key .= '_object_' . trim( implode( '', $params->object ) );
        }

        if ( isset( $params->options ) && !empty( $params->options ) && is_array( $params->options ) ) {
            foreach ( $params->options as $option => $value ) {
                if ( !is_array( $value ) )
                    $value = array( $value );

                $value = pods_trim( $value );

                sort( $value );

                $meta_query[] = array(
                    'key' => $option,
                    'value' => pods_sanitize( $value ),
                    'compare' => 'IN'
                );
            }

            $cache_key = '';
        }

        if ( isset( $params->where ) && is_array( $params->where ) )
            $meta_query = array_combine( $meta_query, (array) $params->where );

        if ( isset( $params->order ) && !empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) )
            $order = strtoupper( $params->order );

        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = strtoupper( $params->orderby );

        if ( isset( $params->limit ) && !empty( $params->limit ) )
            $limit = pods_absint( $params->limit );

        if ( isset( $params->ids ) && !empty( $params->ids ) ) {
            $ids = $params->ids;

            if ( !is_array( $ids ) )
                $ids = explode( ',', $ids );
        }

        if ( empty( $ids ) )
            $ids = false;

        if ( empty( $cache_key ) )
            $cache_key = 'pods';
        else
            $cache_key = 'pods_get' . $cache_key;

        if ( !empty( $cache_key ) && ( 'pods' != $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) ) {
            $the_pods = pods_transient_get( $cache_key );

            if ( false !== $the_pods )
                return $the_pods;
        }

        $the_pods = array();

        $pods = get_posts( array(
            'post_type' => '_pods_pod',
            'nopaging' => true,
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'meta_query' => $meta_query,
            'post__in' => $ids
        ) );

        foreach ( $pods as $pod ) {
            $pod = $this->load_pod( $pod );

            $the_pods[ $pod[ 'id' ] ] = $pod;
        }

        if ( !empty( $cache_key ) && ( 'pods' != $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) )
            pods_transient_set( $cache_key, $the_pods );

        return $the_pods;
    }

    /**
     * Check if a Pod's field exists
     *
     * $params['pod_id'] int The Pod ID
     * $params['id'] int The field ID
     * $params['name'] string The field name
     *
     * @param array $params An associative array of parameters
     *
     * @return bool
     *
     * @since 1.12
     */
    public function field_exists ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( ( !empty( $params->id ) || !empty( $params->name ) ) && isset( $params->pod_id ) && !empty( $params->pod_id ) ) {
            if ( !isset( $params->name ) )
                $field = get_post( $dummy = (int) $params->id );
            else {
                $field = get_posts( array(
                    'name' => $params->name,
                    'post_type' => '_pods_field',
                    'posts_per_page' => 1,
                    'post_parent' => $params->pod_id
                ) );
            }

            if ( !empty( $field ) )
                return true;
        }

        return false;
    }

    /**
     * Load a field
     *
     * $params['pod_id'] int The Pod ID
     * $params['pod'] string The Pod name
     * $params['id'] int The field ID
     * $params['name'] string The field name
     * $params['table_info'] boolean Whether to lookup a pick field's table info
     *
     * @param array $params An associative array of parameters
     * @param boolean $strict Whether to require a field exist or not when loading the info
     *
     * @return array|bool Array with field data, false if field not found
     * @since 1.7.9
     */
    public function load_field ( $params, $strict = false ) {
        $params = (object) $params;

        if ( !isset( $params->table_info ) )
            $params->table_info = false;

        $pod = array();

        if ( isset( $params->post_title ) )
            $_field = $params;
        elseif ( isset( $params->id ) && !empty( $params->id ) )
            $_field = get_post( $dumb = (int) $params->id );
        else {
            if ( !isset( $params->pod ) )
                $params->pod = '';

            if ( !isset( $params->pod_id ) )
                $params->pod_id = 0;

            if ( isset( $params->pod_data ) )
                $pod = $params->pod_data;
            else {
                $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );

                if ( false === $pod )
                    return pods_error( __( 'Pod not found', 'pods' ), $this );
            }

            $params->pod_id = $pod[ 'id' ];
            $params->pod = $pod[ 'name' ];

            if ( empty( $params->name ) && empty( $params->pod ) && empty( $params->pod_id ) )
                return pods_error( __( 'Either Field Name or Field ID / Pod ID are required', 'pods' ), $this );

            $params->name = pods_clean_name( $params->name );

            if ( isset( $pod[ 'fields' ][ $params->name ] ) && isset( $pod[ 'fields' ][ $params->name ][ 'id' ] ) )
                return $pod[ 'fields' ][ $params->name ];

            $field = get_posts( array(
                'name' => $params->name,
                'post_type' => '_pods_field',
                'posts_per_page' => 1,
                'post_parent' => $params->pod_id
            ) );

            if ( empty( $field ) ) {
                if ( $strict )
                    return pods_error( __( 'Field not found', 'pods' ), $this );

                return false;
            }

            $_field = $field[ 0 ];
        }

        if ( empty( $_field ) ) {
            if ( $strict )
                return pods_error( __( 'Field not found', 'pods' ), $this );

            return false;
        }

        $_field = get_object_vars( $_field );

        $defaults = array(
            'type' => 'text'
        );

        $field = array(
            'id' => $_field[ 'ID' ],
            'name' => $_field[ 'post_name' ],
            'label' => $_field[ 'post_title' ],
            'description' => $_field[ 'post_content' ],
            'weight' => $_field[ 'menu_order' ],
            'pod_id' => $_field[ 'post_parent' ],
            'pick_object' => '',
            'pick_val' => '',
            'sister_id' => '',
            'table_info' => array()
        );

        if ( isset( $pod[ 'name' ] ) )
            $field[ 'pod' ] = $pod[ 'name' ];
        elseif ( isset( $_field[ 'pod' ] ) )
            $field[ 'pod' ] = $_field[ 'pod' ];
        else {
            $pod = $this->load_pod( array( 'id' => $field[ 'pod_id' ] ) );

            $field[ 'pod' ] = $pod[ 'name' ];
        }

        $field[ 'options' ] = get_post_meta( $field[ 'id' ] );

        foreach ( $field[ 'options' ] as $option => &$value ) {
            if ( is_array( $value ) && 1 == count( $value ) && isset( $value[ 0 ] ) )
                $value = $value[ 0 ];
        }

        $field[ 'options' ] = array_merge( $defaults, $field[ 'options' ] );

        $field[ 'type' ] = $field[ 'options' ][ 'type' ];

        unset( $field[ 'options' ][ 'type' ] );

        if ( isset( $field[ 'options' ][ 'pick_object' ] ) ) {
            $field[ 'pick_object' ] = $field[ 'options' ][ 'pick_object' ];

            unset( $field[ 'options' ][ 'pick_object' ] );
        }

        if ( isset( $field[ 'options' ][ 'pick_val' ] ) ) {
            $field[ 'pick_val' ] = $field[ 'options' ][ 'pick_val' ];

            unset( $field[ 'options' ][ 'pick_val' ] );
        }

        if ( isset( $field[ 'options' ][ 'sister_id' ] ) ) {
            $field[ 'sister_id' ] = $field[ 'options' ][ 'sister_id' ];

            unset( $field[ 'options' ][ 'sister_id' ] );
        }

        if ( isset( $field[ 'options' ][ 'sister_field_id' ] ) )
            unset( $field[ 'options' ][ 'sister_field_id' ] );

        $field[ 'table_info' ] = array();

        if ( 'pick' == $field[ 'type' ] && true === $params->table_info )
            $field[ 'table_info' ] = $this->get_table_info( $field[ 'pick_object' ], $field[ 'pick_val' ] );

        return $field;
    }

    /**
     * Load fields by Pod, ID, Name, and/or Type
     *
     * $params['pod_id'] int The Pod ID
     * $params['pod'] string The Pod name
     * $params['id'] array The field IDs
     * $params['name'] array The field names
     * $params['type'] array The field types
     *
     * @param array $params An associative array of parameters
     * @param bool $strict  Whether to require a field exist or not when loading the info
     *
     * @return array Array of field data.
     *
     * @since 1.7.9
     */
    public function load_fields ( $params, $strict = false ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) || empty( $params->pod ) )
            $params->pod = '';

        if ( !isset( $params->pod_id ) || empty( $params->pod_id ) )
            $params->pod_id = 0;

        if ( !isset( $params->name ) || empty( $params->name ) )
            $params->name = array();
        else
            $params->name = (array) $params->name;

        if ( !isset( $params->id ) || empty( $params->id ) )
            $params->id = array();
        else {
            $params->id = (array) $params->id;

            foreach ( $params->id as &$id ) {
                $id = pods_absint( $id );
            }
        }

        if ( !isset( $params->type ) || empty( $params->type ) )
            $params->type = array();
        else
            $params->type = (array) $params->type;

        if ( !empty( $params->pod ) || !empty( $params->pod_id ) ) {
            $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );

            if ( false === $pod )
                return pods_error( __( 'Pod not found', 'pods' ), $this );

            $fields = array();

            foreach ( $pod[ 'fields' ] as $field ) {
                if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) )
                    $fields[ $field[ 'name' ] ] = $field;

                if ( in_array( $fields[ 'name' ], $params->name ) || in_array( $fields[ 'id' ], $params->id ) || in_array( $fields[ 'type' ], $params->type ) )
                    $fields[ $field[ 'name' ] ] = $field;
            }
        }
        else {
            if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) )
                return pods_error( __( 'Either Field Name / Field ID / Field Type, or Pod Name / Pod ID are required', 'pods' ), $this );

            $lookup = array();

            if ( !empty( $params->name ) ) {
                $fields = implode( "', '", $params->name );

                $lookup[] = "`post_name` IN ( '{$fields}' )";
            }

            if ( !empty( $params->id ) ) {
                $fields = implode( ", ", $params->id );

                $lookup[] = "`ID` IN ( {$fields} )";
            }

            $lookup = implode( ' AND ', $lookup );

            $fields = pods_cache_get( md5( $lookup ), 'pods_load_fields' );

            if ( false !== $fields )
                return $fields;

            $result = pods_query( "SELECT `ID`, `post_name`, `post_parent` FROM `@wp_posts` WHERE `post_type` = '_pods_field' AND ( {$lookup} )" );

            $fields = array();

            if ( !empty( $result ) ) {
                foreach ( $result as $field ) {
                    $field = $this->load_field( array(
                        'id' => $field->ID,
                        'name' => $field->post_name,
                        'pod_id' => $field->post_parent
                    ) );

                    if ( empty( $params->type ) || in_array( $field[ 'type' ], $params->type ) )
                        $fields[] = $field;
                }
            }

            pods_cache_set( md5( $lookup ), $fields, 'pods_load_fields' );
        }

        return $fields;
    }

    /**
     * Load a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     *
     * @param array|object $params An associative array of parameters
     * @param bool $strict
     *
     * @return array|bool
     * @since 2.0.0
     */
    public function load_object ( $params, $strict = false ) {
        if ( is_object( $params ) && isset( $params->post_name ) ) {
            $type = str_replace( '_pods_', '', $params->post_type );

            $object = pods_transient_get( 'pods_object_' . $type . '_' . $params->post_name );

            if ( false !== $object )
                return $object;

            $_object = get_object_vars( $params );
        }
        else {
            $params = (object) pods_sanitize( $params );

            if ( !isset( $params->type ) || empty( $params->type ) )
                return pods_error( __( 'Object type is required', 'pods' ), $this );

            if ( ( !isset( $params->id ) || empty( $params->id ) ) && ( !isset( $params->name ) || empty( $params->name ) ) )
                return pods_error( __( 'Either Object ID or Name are required', 'pods' ), $this );

            /**
             * @var $wpdb wpdb
             */
            global $wpdb;

            if ( isset( $params->name ) ) {
                $object = pods_transient_get( 'pods_object_' . $params->type . '_' . pods_clean_name( $params->name, true ) );

                if ( false !== $object )
                    return $object;

                $sql = "
                        SELECT `ID`
                        FROM `{$wpdb->posts}`
                        WHERE
                            `post_type` = %s
                            AND `post_status` = 'publish'
                            AND `post_title` = %s
                        LIMIT 1
                    ";

                $object = $wpdb->get_var( $wpdb->prepare( $sql, '_pods_' . $params->type, $params->name) );

                if ( empty( $object ) ) {
                    if ( $strict )
                        return pods_error( __( 'Object not found', 'pods' ), $this );

                    return false;
                }
            }
            else
                $object = $params->id;

            $_object = get_post( $object );

            if ( empty( $_object ) ) {
                if ( $strict )
                    return pods_error( __( 'Object not found', 'pods' ), $this );

                return false;
            }

            $_object = get_object_vars( $_object );
        }

        $object = array(
            'id' => $_object[ 'ID' ],
            'name' => $_object[ 'post_title' ],
            'code' => $_object[ 'post_content' ],
            'type' => str_replace( '_pods_', '', $_object[ 'post_type' ] ),
            'slug' => $_object[ 'post_name' ]
        );

        $object[ 'options' ] = get_post_meta( $object[ 'id' ] );

        foreach ( $object[ 'options' ] as $option => &$value ) {
            if ( is_array( $value ) && 1 == count( $value ) && isset( $value[ 0 ] ) )
                $value = $value[ 0 ];
        }

        pods_transient_set( 'pods_object_' . $object[ 'type' ] . '_' . $object[ 'slug' ], $object );

        return $object;
    }

    /**
     * Load Multiple Pods Objects
     *
     * $params['type'] string The Object type
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of objects to return
     * $params['where'] string WHERE clause of query
     * $params['ids'] string|array IDs of Objects
     *
     * @param array|object $params An associative array of parameters
     *
     * @return array
     * @since 2.0.0
     */
    public function load_objects ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( __( 'Pods Object type is required', 'pods' ), $this );

        $order = 'ASC';
        $orderby = 'menu_order';
        $limit = -1;
        $ids = false;

        $meta_query = array();
        $cache_key = '';

        if ( isset( $params->options ) && !empty( $params->options ) && is_array( $params->options ) ) {
            foreach ( $params->options as $option => $value ) {
                if ( !is_array( $value ) )
                    $value = array( $value );

                $value = pods_trim( $value );

                sort( $value );

                $meta_query[] = array(
                    'key' => $option,
                    'value' => pods_sanitize( $value ),
                    'compare' => 'IN'
                );
            }

            $cache_key = '';
        }

        if ( isset( $params->where ) && is_array( $params->where ) )
            $meta_query = array_combine( $meta_query, (array) $params->where );

        if ( isset( $params->order ) && !empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) )
            $order = strtoupper( $params->order );

        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = strtoupper( $params->orderby );

        if ( isset( $params->limit ) && !empty( $params->limit ) )
            $limit = pods_absint( $params->limit );

        if ( isset( $params->ids ) && !empty( $params->ids ) ) {
            $ids = $params->ids;

            if ( !is_array( $ids ) )
                $ids = explode( ',', $ids );
        }

        if ( empty( $ids ) )
            $ids = false;

        if ( empty( $cache_key ) )
            $cache_key = 'pods_objects_' . $params->type;
        else
            $cache_key = 'pods_objects_' . $params->type . '_get' . $cache_key;

        if ( ( 'pods_objects_' . $params->type != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty( $orderby ) || 'menu_order' == $orderby ) && empty( $ids ) ) {
            $the_objects = pods_transient_get( $cache_key );

            if ( false !== $the_objects )
                return $the_objects;
        }

        $the_objects = array();

        $objects = get_posts( array(
            'post_type' => '_pods_' . $params->type,
            'nopaging' => true,
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'meta_query' => $meta_query,
            'post__in' => $ids
        ) );

        foreach ( $objects as $object ) {
            $object = $this->load_object( $object );

            $the_objects[ $object[ 'name' ] ] = $object;
        }

        if ( ( 'pods_objects_' . $params->type != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty( $orderby ) || 'menu_order' == $orderby ) && empty( $ids ) )
            pods_transient_set( $cache_key, $the_objects );

        return $the_objects;
    }

    /**
     * @see PodsAPI::load_object
     *
     * Load a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @return array|bool
     * @since 1.7.9
     */
    public function load_template ( $params ) {
        if ( !class_exists( 'Pods_Templates' ) )
            return false;

        $params = (object) $params;
        $params->type = 'template';
        return $this->load_object( $params );
    }

    /**
     * @see PodsAPI::load_objects
     *
     * Load Multiple Pod Templates
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of templates to return
     *
     * @param array $params (optional) An associative array of parameters
     * @return array
     */
    public function load_templates ( $params = null ) {
        if ( !class_exists( 'Pods_Templates' ) )
            return array();

        $params = (object) $params;
        $params->type = 'template';
        return $this->load_objects( $params );
    }

    /**
     * @see PodsAPI::load_object
     *
     * Load a Pod Page
     *
     * $params['id'] int The page ID
     * $params['name'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @return array|bool
     * @since 1.7.9
     */
    public function load_page ( $params ) {
        if ( !class_exists( 'Pods_Pages' ) )
            return false;

        $params = (object) $params;
        if ( !isset( $params->name ) && isset( $params->uri ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->type = 'page';
        return $this->load_object( $params );
    }

    /**
     * @see PodsAPI::load_objects
     *
     * Load Multiple Pod Pages
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params (optional) An associative array of parameters
     * @return array
     */
    public function load_pages ( $params = null ) {
        if ( !class_exists( 'Pods_Pages' ) )
            return array();

        $params = (object) $params;
        $params->type = 'page';
        return $this->load_objects( $params );
    }

    /**
     * @see PodsAPI::load_object
     *
     * Load a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @return array|bool
     * @since 1.7.9
     */
    public function load_helper ( $params ) {
        if ( !class_exists( 'Pods_Helpers' ) )
            return false;

        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_object( $params );
    }

    /**
     * @see PodsAPI::load_objects
     *
     * Load Multiple Pod Helpers
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params (optional) An associative array of parameters
     * @return array
     */
    public function load_helpers ( $params = null ) {
        if ( !class_exists( 'Pods_Helpers' ) )
            return array();

        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_objects( $params );
    }

    /**
     * Load the pod item object
     *
     * $params['pod'] string The datatype name
     * $params['id'] int (optional) The item's ID
     *
     * @param array $params An associative array of parameters
     *
     * @return bool|\Pods
     *
     * @uses pods()
     *
     * @since 2.0.0
     */
    public function load_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) || empty( $params->pod ) )
            return pods_error( __( 'Pod name required', 'pods' ), $this );
        if ( !isset( $params->id ) || empty( $params->id ) )
            return pods_error( __( 'Item ID required', 'pods' ), $this );

        $pod = pods_cache_get( $params->id, 'pods_items_' . $params->pod );

        if ( false !== $pod )
            return $pod;

        $pod = pods( $params->pod, $params->id );

        pods_cache_set( $params->id, $pod, 'pods_items_' . $params->pod );

        return $pod;
    }

    /**
     * Load potential sister fields for a specific field
     *
     * $params['pod'] int The Pod name
     * $params['related_pod'] string The related Pod name
     *
     * @param array $params An associative array of parameters
     * @param array $pod (optional) Array of Pod data to use (to avoid lookup)
     *
     * @return array|bool
     * @since 1.7.9
     *
     * @uses PodsAPI::load_pod
     */
    public function load_sister_fields ( $params, $pod = null ) {
        $params = (object) pods_sanitize( $params );

        if ( empty( $pod ) ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ), false );

            if ( false === $pod )
                return pods_error( __( 'Pod not found', 'pods' ), $this );
        }

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        $related_pod = $this->load_pod( array( 'name' => $params->related_pod ), false );

        if ( false === $pod )
            return pods_error( __( 'Related Pod not found', 'pods' ), $this );

        $params->related_pod_id = $related_pod[ 'id' ];
        $params->related_pod = $related_pod[ 'name' ];

        $sister_fields = array();

        foreach ( $related_pod[ 'fields' ] as $field ) {
            if ( 'pick' == $field[ 'type' ] && 'pod' == $field[ 'pick_object' ] && $params->pod == $field[ 'pick_val' ] )
                $sister_fields[ $field[ 'id' ] ] = esc_html( $field[ 'label' ] . ' (' . $field[ 'name' ] . ')' );
        }

        return $sister_fields;
    }

    /**
     * Takes a sql field such as tinyint and returns the pods field type, such as num.
     *
     * @param string $sql_field The SQL field to look for
     *
     * @return string The field type
     *
     * @since 2.0.0
     */
    public static function detect_pod_field_from_sql_data_type ( $sql_field ) {
        $sql_field = strtolower( $sql_field );

        $field_to_field_map = array(
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'int' => 'number',
            'bigint' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
            'time' => 'time',
            'year' => 'date',
            'varchar' => 'text',
            'text' => 'paragraph',
            'mediumtext' => 'paragraph',
            'longtext' => 'paragraph'
        );

        return ( array_key_exists( $sql_field, $field_to_field_map ) ) ? $field_to_field_map[ $sql_field ] : 'paragraph';
    }

    /**
     * Gets all field types
     *
     * @return array Array of field types
     *
     * @uses PodsForm::field_loader
     *
     * @since 2.0.0
     */
    public function get_field_types () {
        $types = array(
            'text',
            'website',
            'phone',
            'email',
            'password',
            'paragraph',
            'wysiwyg',
            'code',
            'datetime',
            'date',
            'time',
            'number',
            'currency',
            'file',
            'avatar',
            'pick',
            'boolean',
            'color',
            'slug'
        );

        if ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER )
            $types[] = 'loop';

        $types = $this->do_hook( 'field_types', $types );

        $field_types = pods_transient_get( 'pods_field_types' );

        if ( false === $field_types || count( $types ) != count( $field_types ) ) {
            $field_types = $types;

            foreach ( $field_types as $k => $field_type ) {
                unset( $field_types[ $k ] );

                PodsForm::field_loader( $field_type );

                $class_vars = get_class_vars( get_class( PodsForm::$loaded[ $field_type ] ) ); // PHP 5.2.x workaround

                $field_type = $class_vars[ 'type' ];
                $field_label = $class_vars[ 'label' ];

                $field_types[ $field_type ] = $field_label;
            }

            pods_transient_set( 'pods_field_types', $field_types );
        }

        return $field_types;
    }

    /**
     * Gets the schema definition of a field.
     *
     * @param string $type Field type to look for
     * @param array $options (optional) Options of the field to pass to the schema function.
     *
     * @return array|bool|mixed|null
     *
     * @since 2.0.0
     */
    private function get_field_definition ( $type, $options = null ) {
        $definition = PodsForm::field_method( $type, 'schema', $options );

        return $this->do_hook( 'field_definition', $definition, $type, $options );
    }

    /**
     * @see PodsForm:validate
     *
     * Validates the value of a field.
     *
     * @param mixed $value The value to validate
     * @param string $field Field to use for validation
     * @param array $object_fields Fields of the object we're validating
     * @param array $fields Array of all fields data
     * @param array $pod Array of pod data
     * @param array|object $params Extra parameters to pass to the validation function of the field.
     *
     * @return array|bool
     *
     * @uses PodsForm::validate
     *
     * @since 2.0.0
     */
    public function handle_field_validation ( &$value, $field, $object_fields, $fields, $pod, $params ) {
        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file' ) );

        $fields = array_merge( $fields, $object_fields );

        $options = $fields[ $field ];

        $id = ( is_object( $params ) ? $params->id : ( is_object( $pod ) ? $pod->id() : 0 ) );

        if ( is_object( $pod ) )
            $pod = $pod->pod_data;

        $type = $options[ 'type' ];
        $label = $options[ 'label' ];
        $label = empty( $label ) ? $field : $label;

        // Verify required fields
        if ( 1 == pods_var( 'required', $options[ 'options' ], 0 ) ) {
            if ( '' == $value || null === $value )
                return pods_error( sprintf( __( '%s is empty', 'pods' ), $label ), $this );

            if ( 'multi' == pods_var( 'pick_format_type', $options[ 'options' ] ) && 'autocomplete' != pods_var( 'pick_format_multi', $options[ 'options' ] ) ) {
                $has_value = false;

                $check_value = (array) $value;

                foreach ( $check_value as $val ) {
                    if ( '' != $val && null !== $val && 0 != $val )
                        $has_value = true;
                }

                if ( !$has_value )
                    return pods_error( sprintf( __( '%s is required', 'pods' ), $label ), $this );
            }

        }

        // Verify unique fields
        if ( 1 == pods_var( 'unique', $options[ 'options' ], 0 ) ) {
            if ( empty( $pod ) )
                return false;

            if ( !in_array( $type, $tableless_field_types ) ) {
                $exclude = '';

                if ( !empty( $id ) )
                    $exclude = "AND `id` != {$id}";

                $check = false;

                $check_value = pods_sanitize( $value );

                // Trigger an error if not unique
                if ( 'table' == $pod[ 'storage' ] )
                    $check = pods_query( "SELECT `id` FROM `@wp_pods_" . $pod[ 'name' ] . "` WHERE `{$field}` = '{$check_value}' {$exclude} LIMIT 1", $this );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( '%s needs to be unique', 'pods' ), $label ), $this );
            }
            else {
                // @todo handle tableless check
            }
        }

        $validate = PodsForm::validate( $options[ 'type' ], $value, $field, array_merge( pods_var( 'options', $options, array() ), $options ), $fields, $pod, $id, $params );

        $validate = $this->do_hook( 'field_validation', $validate, $value, $field, $object_fields, $fields, $pod, $params );

        return $validate;
    }

    /**
     * Find items related to a parent field
     *
     * @param int $field_id The Field ID
     * @param int $pod_id The Pod ID
     * @param mixed $ids A comma-separated string (or array) of item IDs
     * @param array $field Field data array
     * @param array $pod Pod data array
     *
     * @return array|bool
     *
     * @since 2.0.0
     *
     * @uses pods_query()
     */
    public function lookup_related_items ( $field_id, $pod_id, $ids, $field = null, $pod = null ) {
        if ( !is_array( $ids ) )
            $ids = explode( ',', $ids );

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file', 'avatar' ) );

        if ( empty( $ids ) || !in_array( pods_var( 'type', $field ), $tableless_field_types ) )
            return false;

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            foreach ( $ids as &$id ) {
                $id = (int) $id;
            }

            $ids = implode( ', ', $ids );

            $field_id = (int) $field_id;
            $sister_id = (int) pods_var_raw( 'sister_id', $field, 0 );

            $related_where = "
                `field_id` = {$field_id}
                AND `item_id` IN ( {$ids} )
            ";

            $sql = "
                SELECT *
                FROM `@wp_podsrel`
                WHERE
                    {$related_where}
                ORDER BY `weight`
            ";

            $relationships = pods_query( $sql );

            if ( !empty( $relationships ) ) {
                $related_ids = array();

                foreach ( $relationships as $relation ) {
                    if ( $field_id == $relation->field_id && !in_array( $relation->related_item_id, $related_ids ) )
                        $related_ids[] = (int) $relation->related_item_id;
                    elseif ( 0 < $sister_id && $field_id == $relation->related_field_id && !in_array( $relation->item_id, $related_ids ) )
                        $related_ids[] = (int) $relation->item_id;
                }

                $related_ids = array_unique( array_filter( $related_ids ) );

                return $related_ids;
            }
        }
        else {
            if ( !is_array( $pod ) )
                $pod = $this->load_pod( array( 'id' => $pod_id ), false );

            if ( !empty( $pod ) && in_array( $pod[ 'type' ], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
                $related_ids = array();

                $meta_type = $pod[ 'type' ];

                if ( in_array( $pod[ 'type' ], array( 'post_type', 'media' ) ) )
                    $meta_type = 'post';

                pods_no_conflict_on( $meta_type );

                foreach ( $ids as $id ) {
                    $related_id = get_metadata( $meta_type, $id, '_pods_' . $field[ 'name' ], true );

                    if ( empty( $related_id ) )
                        $related_id = get_metadata( $meta_type, $id, $field[ 'name' ], true );

                    if ( is_array( $related_id ) && !empty( $related_id ) ) {
                        foreach ( $related_id as $related ) {
                            if ( is_array( $related ) && !empty( $related ) ) {
                                foreach ( $related as $r ) {
                                    $related_ids[] = (int) $r;
                                }
                            }
                            else
                                $related_ids[] = (int) $related;
                        }
                    }
                }

                pods_no_conflict_off( $meta_type );

                $related_ids = array_unique( array_filter( $related_ids ) );

                return $related_ids;
            }
        }

        return false;
    }

    /**
     * Get information about an objects MySQL table
     *
     * @param string $object_type
     * @param string $object The object to look for
     * @param null $name (optional) Name of the pod to load
     * @param array $pod (optional) Array with pod information
     *
     * @return array|bool
     *
     * @since 2.0.0
     */
    public function get_table_info ( $object_type, $object, $name = null, $pod = null ) {
        /**
         * @var $wpdb wpdb
         * @var $sitepress SitePress
         * @var $icl_adjust_id_url_filter_off boolean
         */
        global $wpdb, $sitepress, $icl_adjust_id_url_filter_off;

        $info = array(
            //'select' => '`t`.*',
            'object_type' => null,
            'type' => null,

            'table' => $object,
            'meta_table' => $object,

            'field_id' => 'id',
            'field_index' => 'name',
            'field_slug' => null,
            'field_type' => null,

            'meta_field_id' => 'id',
            'meta_field_index' => 'name',
            'meta_field_value' => 'name',

            'join' => array(),

            'where' => null,
            'where_default' => null,

            'orderby' => null,

            'pod' => null,
            'recurse' => false
        );

        if ( empty( $object_type ) ) {
            $object_type = 'post_type';
            $object = 'post';
        }

        $pod_name = $pod;

        if ( is_array( $pod_name ) )
            $pod_name = pods_var_raw( 'name', $pod_name, ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $pod_name, JSON_UNESCAPED_UNICODE ) : json_encode( $pod_name ) ), null, true );

        $transient = 'pods_get_table_info_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name );

        if ( is_object( $sitepress ) && !$icl_adjust_id_url_filter_off ) {
            $current_language = pods_sanitize( ICL_LANGUAGE_CODE );

            $transient .= 'pods_get_table_info_' . $current_language . '_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name );
        }

        $_info = pods_transient_get( $transient );

        if ( false !== $_info )
            $info = $_info;
        else {
            if ( 'pod' == $object_type && null === $pod ) {
                if ( empty( $name ) ) {
                    $prefix = 'pod-';

                    // Make sure we actually have the prefix before trying anything with the name
                    if ( 0 === strpos( $object_type, $prefix ) )
                        $name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
                }
                if ( !empty( $object ) )
                    $name = $object;

                $pod = $this->load_pod( array( 'name' => $name ), false );

                if ( !empty( $pod ) ) {
                    $object_type = $pod[ 'type' ];
                    $name = $pod[ 'name' ];
                    $object = $pod[ 'object' ];

                    $info[ 'pod' ] = $pod;
                }
            }

            if ( 0 === strpos( $object_type, 'pod' ) ) {
                if ( empty( $name ) ) {
                    $prefix = 'pod-';

                    // Make sure we actually have the prefix before trying anything with the name
                    if ( 0 === strpos( $object_type, $prefix ) )
                        $name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
                }

                $object_type = 'pod';

                $info[ 'table' ] = $info[ 'meta_table' ] = $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object );

                if ( is_array( $pod ) ) {
                    if ( isset( $pod[ 'options' ] ) && 'pod' == pods_var( 'type', $pod ) )
                        $info[ 'field_index' ] = $info[ 'meta_field_index' ] = $info[ 'meta_field_value' ] = pods_var( 'pod_index', $pod[ 'options' ], 'id', null, true );

                    $slug_field = get_posts( array(
                        'post_type' => '_pods_field',
                        'posts_per_page' => 1,
                        'nopaging' => true,
                        'post_parent' => $pod[ 'id' ],
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                        'meta_query' => array(
                            array(
                                'key' => 'type',
                                'value' => 'slug',
                            )
                        )
                    ) );

                    if ( !empty( $slug_field ) ) {
                        $slug_field = $slug_field[ 0 ];

                        $info[ 'field_slug' ] = $slug_field->post_name;
                    }
                }
            }
            elseif ( 0 === strpos( $object_type, 'post_type' ) || 'media' == $object_type ) {
                $info[ 'table' ] = $wpdb->posts;
                $info[ 'meta_table' ] = $wpdb->postmeta;

                $info[ 'field_id' ] = 'ID';
                $info[ 'field_index' ] = 'post_title';
                $info[ 'field_slug' ] = 'post_name';
                $info[ 'field_type' ] = 'post_type';

                $info[ 'meta_field_id' ] = 'post_id';
                $info[ 'meta_field_index' ] = 'meta_key';
                $info[ 'meta_field_value' ] = 'meta_value';

                if ( 'media' == $object_type )
                    $object = 'attachment';

                if ( empty( $name ) ) {
                    $prefix = 'post_type-';

                    // Make sure we actually have the prefix before trying anything with the name
                    if ( 0 === strpos( $object_type, $prefix ) )
                        $name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
                }

                if ( 'media' != $object_type )
                    $object_type = 'post_type';

                $post_type = pods_sanitize( ( empty( $object ) ? $name : $object ) );

                $info[ 'where' ] = array(
                    //'post_status' => '`t`.`post_status` IN ( "inherit", "publish" )', // @todo Figure out what statuses Attachments can be
                    'post_type' => '`t`.`post_type` = "' . $post_type . '"'
                );

                if ( 'post_type' == $object_type )
                    $info[ 'where_default' ] = '`t`.`post_status` = "publish"';

                $info[ 'orderby' ] = '`t`.`menu_order`, `t`.`' . $info[ 'field_index' ] . '`, `t`.`post_date`';

                if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $post_type ) && !$icl_adjust_id_url_filter_off ) {
                    $info[ 'join' ][ 'wpml_translations' ] = "
                        LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
                            ON `wpml_translations`.`element_id` = `t`.`ID`
                                AND `wpml_translations`.`element_type` = 'post_{$post_type}'
                                AND `wpml_translations`.`language_code` = '{$current_language}'
                    ";

                    $info[ 'join' ][ 'wpml_languages' ] = "
                        LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
                            ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
                    ";

                    $info[ 'where' ][ 'wpml_language_code' ] = "`wpml_languages`.`code` IS NOT NULL";
                }

                $info[ 'object_fields' ] = $this->get_wp_object_fields( $object_type );
            }
            elseif ( 0 === strpos( $object_type, 'taxonomy' ) || in_array( $object_type, array( 'nav_menu', 'post_format' ) ) ) {
                $info[ 'table' ] = $info[ 'meta_table' ] = $wpdb->terms;

                $info[ 'join' ][ 'tt' ] = "LEFT JOIN `{$wpdb->term_taxonomy}` AS `tt` ON `tt`.`term_id` = `t`.`term_id`";
                $info[ 'field_id' ] = $info[ 'meta_field_id' ] = 'term_id';
                $info[ 'field_index' ] = $info[ 'meta_field_index' ] = $info[ 'meta_field_value' ] = 'name';
                $info[ 'field_slug' ] = 'slug';
                $info[ 'field_type' ] = 'taxonomy';

                if ( 'nav_menu' == $object_type )
                    $object = 'nav_menu';
                elseif ( 'post_format' == $object_type )
                    $object = 'post_format';

                if ( empty( $name ) ) {
                    $prefix = 'taxonomy-';

                    // Make sure we actually have the prefix before trying anything with the name
                    if ( 0 === strpos( $object_type, $prefix ) )
                        $name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
                }

                if ( !in_array( $object_type, array( 'nav_menu', 'post_format' ) ) )
                    $object_type = 'taxonomy';

                $taxonomy = ( empty( $object ) ? $name : $object );

                $info[ 'where' ] = array(
                    'tt.taxonomy' => '`tt`.`taxonomy` = "' . $taxonomy . '"'
                );

                if ( is_object( $sitepress ) && $sitepress->is_translated_taxonomy( $taxonomy ) && !$icl_adjust_id_url_filter_off ) {
                    $info[ 'join' ][ 'wpml_translations' ] = "
                        LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
                            ON `wpml_translations`.`element_id` = `tt`.`term_taxonomy_id`
                                AND `wpml_translations`.`element_type` = 'tax_{$taxonomy}'
                                AND `wpml_translations`.`language_code` = '{$current_language}'
                    ";

                    $info[ 'join' ][ 'wpml_languages' ] = "
                        LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
                            ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
                    ";

                    $info[ 'where' ][ 'wpml_language_code' ] = "`wpml_languages`.`code` IS NOT NULL";
                }

                $info[ 'object_fields' ] = $this->get_wp_object_fields( $object_type );
            }
            elseif ( 'user' == $object_type ) {
                $info[ 'table' ] = $wpdb->users;
                $info[ 'meta_table' ] = $wpdb->usermeta;

                $info[ 'field_id' ] = 'ID';
                $info[ 'field_index' ] = 'display_name';
                $info[ 'field_slug' ] = 'user_nicename';

                $info[ 'meta_field_id' ] = 'user_id';
                $info[ 'meta_field_index' ] = 'meta_key';
                $info[ 'meta_field_value' ] = 'meta_value';

                $info[ 'where' ] = array(
                    'user_status' => '`t`.`user_status` = 0'
                );

                $info[ 'object_fields' ] = $this->get_wp_object_fields( $object_type );
            }
            elseif ( 'comment' == $object_type ) {
                $info[ 'table' ] = $wpdb->comments;
                $info[ 'meta_table' ] = $wpdb->commentmeta;

                $info[ 'field_id' ] = 'comment_ID';
                $info[ 'field_index' ] = 'comment_date';
                $info[ 'field_type' ] = 'comment_type';

                $info[ 'meta_field_id' ] = 'comment_id';
                $info[ 'meta_field_index' ] = 'meta_key';
                $info[ 'meta_field_value' ] = 'meta_value';

                $object = 'comment';

                $info[ 'where' ] = array(
                    'comment_approved' => '`t`.`comment_approved` = 1',
                    'comment_type' => '`t`.`comment_type` = "' . ( empty( $object ) ? $name : $object ) . '"'
                );

                $info[ 'orderby' ] = '`t`.`' . $info[ 'field_index' ] . '` DESC, `t`.`' . $info[ 'field_id' ] . '`';
            }
            elseif ( 'table' == $object_type )
                $info[ 'table' ] = ( empty( $object ) ? $name : $object );

            $info[ 'table' ] = pods_clean_name( $info[ 'table' ], false, false );
            $info[ 'meta_table' ] = pods_clean_name( $info[ 'meta_table' ], false, false );

            $info[ 'field_id' ] = pods_clean_name( $info[ 'field_id' ], false, false );
            $info[ 'field_index' ] = pods_clean_name( $info[ 'field_index' ], false, false );
            $info[ 'field_slug' ] = pods_clean_name( $info[ 'field_slug' ], false, false );

            $info[ 'meta_field_id' ] = pods_clean_name( $info[ 'meta_field_id' ], false, false );
            $info[ 'meta_field_index' ] = pods_clean_name( $info[ 'meta_field_index' ], false, false );
            $info[ 'meta_field_value' ] = pods_clean_name( $info[ 'meta_field_value' ], false, false );

            if ( empty( $info[ 'orderby' ] ) )
                $info[ 'orderby' ] = '`t`.`' . $info[ 'field_index' ] . '`, `t`.`' . $info[ 'field_id' ] . '`';

            if ( !empty( $pod ) && 'table' == $pod[ 'storage' ] && !in_array( $object_type, array( 'pod', 'table' ) ) ) {
                $info[ 'join' ][ 'd' ] = "LEFT JOIN `{$wpdb->prefix}pods_{$name}` AS `d` ON `d`.`id` = `t`.`" . $info[ 'field_id' ] . '`';
                //$info[ 'select' ] .= ', `d`.*';
            }

            if ( !empty( $info[ 'pod' ] ) && is_array( $info[ 'pod' ] ) )
                $info[ 'recurse' ] = true;

            $info[ 'type' ] = $object_type;

            pods_transient_set( $transient, $info );
        }

        $info = $this->do_hook( 'get_table_info', $info, $object_type, $object );

        return $info;
    }

    /**
     * Export a package
     *
     * $params['pod'] string Pod Type IDs to export
     * $params['template'] string Template IDs to export
     * $params['podpage'] string Pod Page IDs to export
     * $params['helper'] string Helper IDs to export
     *
     * @param array $params An associative array of parameters
     *
     * @return array|bool
     *
     * @since 1.9.0
     * @deprecated 2.0.0
     */
    public function export_package ( $params ) {
        if ( class_exists( 'Pods_Migrate_Packages' ) )
            return Pods_Migrate_Packages::export( $params );

        return false;
    }

    /**
     * Replace an existing package
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     *
     * @return bool
     *
     * @since 1.9.8
     * @deprecated 2.0.0
     */
    public function replace_package ( $data = false ) {
        return $this->import_package( $data, true );
    }

    /**
     * Import a package
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @param bool $replace (optional) Replace existing items when found
     *
     * @return bool
     *
     * @since 1.9.0
     * @deprecated 2.0.0
     */
    public function import_package ( $data = false, $replace = false ) {
        if ( class_exists( 'Pods_Migrate_Packages' ) )
            return Pods_Migrate_Packages::import( $data, $replace );

        return false;
    }

    /**
     * Validate a package
     *
     * @param array|string $data (optional) An associative array containing a package, or the json encoded package
     * @param bool $output (optional)
     *
     * @return array|bool
     *
     * @since 1.9.0
     * @deprecated 2.0.0
     */
    public function validate_package ( $data = false, $output = false ) {
        return true;
    }

    /**
     * Import data from an array or a CSV file.
     *
     * @param mixed $import_data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     * @param string $format Format of import data, options are php or csv
     *
     * @return array
     * @since 1.7.1
     * @todo This needs some love and use of table_info etc for relationships
     */
    public function import ( $import_data, $numeric_mode = false, $format = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        if ( null === $format && null !== $this->format )
            $format = $this->format;

        if ( 'csv' == $format )
            $import_data = $this->csv_to_php( $import_data );

        pods_query( "SET NAMES utf8" );
        pods_query( "SET CHARACTER SET utf8" );

        // Loop through the array of items
        $ids = array();

        // Test to see if it's an array of arrays
        if ( !is_array( @current( $import_data ) ) )
            $import_data = array( $import_data );

        $pod = $this->load_pod( array( 'name' => $this->pod ) );

        if ( false === $pod )
            return pods_error( __( 'Pod not found', 'pods' ), $this );

        $fields = $pod[ 'fields' ];

        foreach ( $import_data as $key => $data_row ) {
            $data = array();

            // Loop through each field (use $fields so only valid fields get parsed)
            foreach ( $fields as $field_name => $field_data ) {
                $field_id = $field_data[ 'id' ];
                $type = $field_data[ 'type' ];
                $pick_object = $field_data[ 'pick_object' ];
                $pick_val = $field_data[ 'pick_val' ];
                $field_value = $data_row[ $field_name ];

                if ( null != $field_value && false !== $field_value ) {
                    if ( 'pick' == $type || 'file' == $type ) {
                        $field_values = is_array( $field_value ) ? $field_value : array( $field_value );
                        $pick_values = array();

                        foreach ( $field_values as $pick_value ) {
                            if ( 'file' == $type || 'media' == $pick_object ) {
                                $where = "`guid` = '" . pods_sanitize( $pick_value ) . "'";

                                if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                    $where = "`ID` = " . pods_absint( $pick_value );

                                $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this );

                                if ( !empty( $result ) )
                                    $pick_values[] = $result[ 0 ]->id;
                            }
                            elseif ( 'pick' == $type ) {
                                if ( 'taxonomy' == $pick_object ) {
                                    $where = "`t`.`name` = '" . pods_sanitize( $pick_value ) . "'";

                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`tt`.`term_id` = " . pods_absint( $pick_value );

                                    $result = pods_query( "SELECT `t`.`term_id` AS `id` FROM `{$wpdb->term_taxonomy}` AS `tt` LEFT JOIN `{$wpdb->terms}` AS `t` ON `t`.`term_id` = `tt`.`term_id` WHERE `taxonomy` = '{$pick_val}' AND {$where} ORDER BY `t`.`term_id`", $this );

                                    if ( !empty( $result ) )
                                        $pick_values[] = $result[ 0 ]->id;
                                }
                                elseif ( 'post_type' == $pick_object || 'media' == $pick_object ) {
                                    $where = "`post_title` = '" . pods_sanitize( $pick_value ) . "'";

                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );

                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '{$pick_val}' AND {$where} ORDER BY `ID`", $this );

                                    if ( !empty( $result ) )
                                        $pick_values[] = $result[ 0 ]->id;
                                }
                                elseif ( 'user' == $pick_object ) {
                                    $where = "`user_login` = '" . pods_sanitize( $pick_value ) . "'";

                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );

                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID`", $this );

                                    if ( !empty( $result ) )
                                        $pick_values[] = $result[ 0 ]->id;
                                }
                                elseif ( 'comment' == $pick_object ) {
                                    $where = "`comment_ID` = " . pods_absint( $pick_value );

                                    $result = pods_query( "SELECT `comment_ID` AS `id` FROM `{$wpdb->comments}` WHERE {$where} ORDER BY `ID`", $this );

                                    if ( !empty( $result ) )
                                        $pick_values[] = $result[ 0 ]->id;
                                }
                                elseif ( 'custom-simple' == $pick_object )
                                    $pick_values[] = $pick_value;
                                else {
                                    $related_pod = $this->load_pod( array( 'name' => $pick_val ), false );

                                    if ( empty( $related_pod ) )
                                        continue;

                                    $where = "`" . $related_pod[ 'field_index' ] . "` = '" . pods_sanitize( $pick_value ) . "'";

                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`" . $related_pod[ 'field_id' ] . "` = " . pods_absint( $pick_value );

                                    $result = pods_query( "SELECT `id` FROM `" . $related_pod[ 'table' ] . "` WHERE {$where} ORDER BY `" . $related_pod[ 'field_id' ] . "`", $this );

                                    if ( !empty( $result ) )
                                        $pick_values[] = $result[ 0 ]->id;
                                }
                            }
                        }

                        $field_value = implode( ',', $pick_values );
                    }

                    $data[ $field_name ] = pods_sanitize( $field_value );
                }
            }

            if ( !empty( $data ) ) {
                $params = array(
                    'pod' => $this->pod,
                    'data' => $data
                );

                $ids[] = $this->save_pod_item( $params );
            }
        }

        return $ids;
    }

    /**
     * Export data
     *
     * @since 1.7.1
     */
    public function export () {
        $pod = pods( $this->pod, array( 'limit' => -1, 'search' => false, 'pagination' => false ) );

        $data = array();

        while ( $pod->fetch() ) {
            $data[ $pod->id() ] = $this->export_pod_item( $pod );
        }

        $data = $this->do_hook( 'export', $data, $this->pod, $pod );

        return $data;
    }

    /**
     * Convert CSV to a PHP array
     *
     * @param string $data The CSV input
     *
     * @return array
     * @since 1.7.1
     */
    public function csv_to_php ( $data, $delimiter = ',' ) {
        $expr = "/{$delimiter}(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";

        $data = str_replace( "\r\n", "\n", $data );
        $data = str_replace( "\r", "\n", $data );

        $lines = explode( "\n", $data );

        $field_names = array_shift( $lines );

        if ( function_exists( 'str_getcsv' ) )
            $field_names = str_getcsv( $field_names, $delimiter );
        else {
            $field_names = explode( $delimiter, $field_names );
            $field_names = preg_replace( "/^\"(.*)\"$/s", "$1", $field_names );
        }

        $out = array();

        foreach ( $lines as $line ) {
            // Skip the empty line
            if ( strlen ( $line ) < 1 )
                continue;

            $row = array();

            if ( function_exists( 'str_getcsv' ) )
                $fields = str_getcsv( $line, $delimiter );
            else {
                $fields = preg_split( $expr, trim( $line ) );
                $fields = preg_replace( "/^\"(.*)\"$/s", "$1", $fields );
            }

            foreach ( $field_names as $key => $field ) {
                $row[ $field ] = $fields[ $key ];
            }

            $out[] = $row;
        }
        return $out;
    }

    /**
     * Clear Pod-related cache
     *
     * @param array $pod
     */
    public function cache_flush_pods ( $pod = null ) {
        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

        pods_transient_clear( 'pods' );
        pods_transient_clear( 'pods_components' );

        if ( null !== $pod && is_array( $pod ) ) {
            pods_transient_clear( 'pods_pod_' . $pod[ 'name' ] );

            if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) )
                pods_transient_clear( 'pods_wp_cpt_ct' );
        }
        else
            pods_transient_clear( 'pods_wp_cpt_ct' );

        $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_pods%'" );
        $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_timeout_pods%'" );

        pods_cache_clear( true );

        pods_transient_set( 'pods_flush_rewrites', 1 );
    }

    /**
     * Process a Pod-based form
     *
     * @param mixed $params
     * @param object $obj Pod object
     * @param array $fields Fields being submitted in form ( key => settings )
     * @param string $thank_you URL to send to upon success
     *
     * @return mixed
     */
    public function process_form ( $params, $obj = null, $fields = null, $thank_you = null ) {
        $this->display_errors = false;

        $form = null;

        $nonce = pods_var( '_pods_nonce', $params );
        $pod = pods_var( '_pods_pod', $params );
        $id = pods_var( '_pods_id', $params );
        $uri = pods_var( '_pods_uri', $params );
        $form = pods_var( '_pods_form', $params );

        if ( is_object( $obj ) ) {
            $pod = $obj->pod;
            $id = $obj->id();
        }

        if ( !empty( $fields ) ) {
            $fields = array_keys( $fields );
            $form = implode( ',', $fields );
        }
        else
            $fields = explode( ',', $form );

        if ( empty( $nonce ) || empty( $pod ) || empty( $uri ) || empty( $fields ) )
            return pods_error( __( 'Invalid submission', 'pods' ), $this );

        $uid = @session_id();

        if ( is_user_logged_in() )
            $uid = 'user_' . get_current_user_id();

        $action = 'pods_form_' . $pod . '_' . $uid . '_' . $id . '_' . $uri . '_' . wp_hash( $form );

        if ( empty( $uid ) )
            return pods_error( __( 'Access denied for your session, please refresh and try again.', 'pods' ), $this );

        if ( wp_verify_nonce( $nonce, $action ) )
            return pods_error( __( 'Access denied, please refresh and try again.', 'pods' ), $this );

        $data = array();

        foreach ( $fields as $field ) {
            $data[ $field ] = pods_var_raw( 'pods_field_' . $field, $params, '' );
        }

        $params = array(
            'pod' => $pod,
            'id' => $id,
            'data' => $data
        );

        $id = $this->save_pod_item( $params );

        if ( 0 < $id && !empty( $thank_you ) ) {
            $thank_you = str_replace( 'X_ID_X', $id, $thank_you );

            die( '<script type="text/javascript">document.location = \'' . addslashes( $thank_you ) . '\';</script>' );
        }

        return $id;
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
        return pods_do_hook( "api", $name, $args, $this );
    }

    /**
     * Handle variables that have been deprecated
     *
     * @since 2.0.0
     */
    public function __get ( $name ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        $var = null;

        if ( isset( $this->deprecated->{$name} ) ) {
            pods_deprecated( "PodsAPI->{$name}", '2.0.0' );

            $var = $this->deprecated->{$name};
        }
        else
            pods_deprecated( "PodsAPI->{$name}", '2.0.0' );

        return $var;
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) )
            return call_user_func_array( array( $this->deprecated, $name ), $args );
        else
            pods_deprecated( "PodsAPI::{$name}", '2.0.0' );
    }
}
