<?php
/**
 * @package Pods
 */
class PodsMeta {

    /**
     * @var PodsAPI
     */
    private $api;

    /**
     * @var array
     */
    public static $post_types = array();

    /**
     * @var array
     */
    public static $taxonomies = array();

    /**
     * @var array
     */
    public static $media = array();

    /**
     * @var array
     */
    public static $user = array();

    /**
     * @var array
     */
    public static $comment = array();

    /**
     * @var array
     */
    public static $groups = array();

    /**
     *
     */
    function __construct () {
        $this->api =& pods_api();
    }

    /**
     * @return PodsMeta
     */
    public function init () {
        self::$post_types = $this->api->load_pods( array( 'type' => 'post_type' ) );
        self::$taxonomies = $this->api->load_pods( array( 'type' => 'taxonomy' ) );
        self::$media = $this->api->load_pods( array( 'type' => 'media' ) );
        self::$user = $this->api->load_pods( array( 'type' => 'user' ) );
        self::$comment = $this->api->load_pods( array( 'type' => 'comment' ) );

        // Handle Post Type Editor (needed for Pods core)

        // Loop through and add meta boxes for individual types (can't use this, Tabify doesn't pick it up)
        /*
        foreach ( self::$post_types as $post_type ) {
            $post_type_name = $post_type[ 'name' ];

            if ( !empty( $post_type[ 'object' ] ) )
                $post_type_name = $post_type[ 'object' ];

            add_action( 'add_meta_boxes_' . $post_type_name, array( $this, 'meta_post_add' ) );
        }
        */

        add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

        // Handle *_post_meta
        add_filter( 'get_post_metadata', array( $this, 'get_post_meta' ), 10, 4 );
        add_filter( 'add_post_metadata', array( $this, 'add_post_meta' ), 10, 5 );
        add_filter( 'update_post_metadata', array( $this, 'update_post_meta' ), 10, 5 );
        add_filter( 'delete_post_metadata', array( $this, 'delete_post_meta' ), 10, 5 );

        if ( !empty( self::$taxonomies ) ) {
            // Handle Taxonomy Editor
            foreach ( self::$taxonomies as $taxonomy ) {
                $taxonomy_name = $taxonomy[ 'name' ];
                if ( !empty( $taxonomy[ 'object' ] ) )
                    $taxonomy_name = $taxonomy[ 'object' ];

                add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 );
                add_action( $taxonomy_name . '_add_form_fields', array( $this, 'meta_taxonomy' ), 10, 1 );
            }

            add_action( 'edit_term', array( $this, 'save_taxonomy' ), 10, 3 );
            add_action( 'create_term', array( $this, 'save_taxonomy' ), 10, 3 );

            // Handle *_term_meta, LOL just kidding
            /*
            add_filter( 'get_term_metadata', array( $this, 'get_term_meta' ), 10, 4 );
            add_filter( 'add_term_metadata', array( $this, 'add_term_meta' ), 10, 5 );
            add_filter( 'update_term_metadata', array( $this, 'update_term_meta' ), 10, 5 );
            add_filter( 'delete_term_metadata', array( $this, 'delete_term_meta' ), 10, 5 );
            */
        }

        if ( !empty( self::$media ) ) {
            // Handle Media Editor
            add_filter( 'attachment_fields_to_edit', array( $this, 'meta_media' ), 10, 2 );
            add_filter( 'attachment_fields_to_save', array( $this, 'save_media' ), 10, 2 );
            add_filter( 'wp_update_attachment_metadata', array( $this, 'save_media' ), 10, 2 );
        }

        if ( !empty( self::$user ) ) {
            // Handle User Editor
            add_action( 'show_user_profile', array( $this, 'meta_user' ) );
            add_action( 'edit_user_profile', array( $this, 'meta_user' ) );
            add_action( 'personal_options_update', array( $this, 'save_user' ) );
            add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );

            // Handle *_user_meta
            add_filter( 'get_user_metadata', array( $this, 'get_user_meta' ), 10, 4 );
            add_filter( 'add_user_metadata', array( $this, 'add_user_meta' ), 10, 5 );
            add_filter( 'update_user_metadata', array( $this, 'update_user_meta' ), 10, 5 );
            add_filter( 'delete_user_metadata', array( $this, 'delete_user_meta' ), 10, 5 );
        }

        if ( !empty( self::$comment ) ) {
            // Handle Comment Editor
            add_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 );
            add_filter( 'comment_form_default_fields', array( $this, 'meta_comment_new' ) );
            add_action( 'add_meta_boxes_comment', array( $this, 'meta_comment_add' ) );
            add_action( 'wp_insert_comment', array( $this, 'save_comment' ) );
            add_action( 'edit_comment', array( $this, 'save_comment' ) );

            // Handle *_comment_meta
            add_filter( 'get_comment_metadata', array( $this, 'get_comment_meta' ), 10, 4 );
            add_filter( 'add_comment_metadata', array( $this, 'add_comment_meta' ), 10, 5 );
            add_filter( 'update_comment_metadata', array( $this, 'update_comment_meta' ), 10, 5 );
            add_filter( 'delete_comment_metadata', array( $this, 'delete_comment_meta' ), 10, 5 );
        }

        do_action( 'pods_meta_init' );

        return $this;
    }

    /**
     * Add a meta group of fields to add/edit forms
     *
     * @param string|array $pod The pod or type of element to attach the group to.
     * @param string $label Title of the edit screen section, visible to user.
     * @param string|array $fields Either a comma separated list of text fields or an associative array containing field infomration.
     * @param string $context (optional) The part of the page where the edit screen section should be shown ('normal', 'advanced', or 'side').
     * @param string $priority (optional) The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
     *
     * @since 2.0.0
     *
     * @return mixed|void
     */
    public function group_add ( $pod, $label, $fields, $context = 'normal', $priority = 'default' ) {
        if ( !is_array( $pod ) ) {
            $_pod = pods_api()->load_pod( array( 'name' => $pod ), false );

            if ( !empty( $_pod ) )
                $pod = $_pod;
            else {
                $type = 'post_type';

                if ( in_array( $pod, array( 'media', 'user', 'comment' ) ) )
                    $type = $pod;

                $pod = array(
                    'name' => $pod,
                    'type' => $type
                );
            }
        }

        if ( is_array( $pod ) && !isset( $pod[ 'id' ] ) ) {
            $defaults = array(
                'name' => '',
                'type' => 'post_type'
            );

            $pod = array_merge( $defaults, $pod );
        }

        if ( 'post' == $pod[ 'type' ] )
            $pod[ 'type' ] = 'post_type';

        if ( empty( $pod[ 'name' ] ) && isset( $pod[ 'object' ] ) && !empty( $pod[ 'object' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];
        elseif ( !isset( $pod[ 'object' ] ) || empty( $pod[ 'object' ] ) )
            $pod[ 'object' ] = $pod[ 'name' ];

        if ( empty( $pod[ 'object' ] ) )
            return pods_error( __( 'Object required to add a Pods meta group', 'pods' ) );

        $object_name = $pod[ 'object' ];

        if ( 'pod' == $pod[ 'type' ] )
            $object_name = $pod[ 'name' ];

        if ( !isset( self::$groups[ $pod[ 'type' ] ] ) )
            self::$groups[ $pod[ 'type' ] ] = array();

        if ( !isset( self::$groups[ $pod[ 'type' ] ][ $object_name ] ) )
            self::$groups[ $pod[ 'type' ] ][ $object_name ] = array();

        $_fields = array();

        if ( !is_array( $fields ) )
            $fields = explode( ',', $fields );

        foreach ( $fields as $k => $field ) {
            if ( !is_array( $field ) ) {
                if ( is_numeric( $k ) )
                    $k = $field;

                if ( !is_array( $k ) && isset( $pod[ 'fields' ] ) && isset( $pod[ 'fields' ][ $k ] ) ) {
                    if ( is_array( $field ) )
                        $field = array_merge( $pod[ 'fields' ][ $k ], $field );
                    else
                        $field = $pod[ 'fields' ][ $k ];
                }
                elseif ( is_array( $field ) ) {
                    $defaults = array(
                        'name' => '',
                        'label' => '',
                        'type' => 'text'
                    );

                    $field = array_merge( $defaults, $field );
                }
                else {
                    $field = array(
                        'name' => $k,
                        'label' => $field,
                        'type' => 'text'
                    );
                }
            }

            $_fields[ $k ] = $field;
        }

        // Setup field options
        $fields = PodsForm::fields_setup( $_fields );

        $group = array(
            'pod' => $pod,
            'label' => $label,
            'fields' => $fields,
            'context' => $context,
            'priority' => $priority
        );

        // Filter group data, pass vars separately for reference down the line (in case array changed by other filter)
        $group = apply_filters( 'pods_meta_group_add_' . $pod[ 'type' ] . '_' . $object_name, $group, $pod, $label, $fields );
        $group = apply_filters( 'pods_meta_group_add_' . $pod[ 'type' ], $group, $pod, $label, $fields );
        $group = apply_filters( 'pods_meta_group_add', $group, $pod, $label, $fields );

        self::$groups[ $pod[ 'type' ] ][ $object_name ][] = $group;

        // Hook it up!
        if ( 'post_type' == $pod[ 'type' ] ) {
            if ( !has_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) ) )
                add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );

            /*if ( !has_action( 'save_post', array( $this, 'save_post' ), 10, 2 ) )
                add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );*/
        }
        elseif ( 'taxonomy' == $pod[ 'type' ] ) {
            if ( !has_action( $pod[ 'object' ] . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 ) ) {
                add_action( $pod[ 'object' ] . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 );
                add_action( $pod[ 'object' ] . '_add_form_fields', array( $this, 'meta_taxonomy' ), 10, 1 );
            }

            if ( !has_action( 'edit_term', array( $this, 'save_taxonomy' ), 10, 3 ) ) {
                add_action( 'edit_term', array( $this, 'save_taxonomy' ), 10, 3 );
                add_action( 'create_term', array( $this, 'save_taxonomy' ), 10, 3 );
            }
        }
        elseif ( 'media' == $pod[ 'type' ] ) {
            if ( !has_filter( 'attachment_fields_to_edit', array( $this, 'meta_media' ), 10, 2 ) ) {
                add_filter( 'attachment_fields_to_edit', array( $this, 'meta_media' ), 10, 2 );
                add_filter( 'attachment_fields_to_save', array( $this, 'save_media' ), 10, 2 );
                add_filter( 'wp_update_attachment_metadata', array( $this, 'save_media' ), 10, 2 );
            }
        }
        elseif ( 'user' == $pod[ 'type' ] ) {
            if ( !has_action( 'show_user_profile', array( $this, 'meta_user' ) ) ) {
                add_action( 'show_user_profile', array( $this, 'meta_user' ) );
                add_action( 'edit_user_profile', array( $this, 'meta_user' ) );
                add_action( 'personal_options_update', array( $this, 'save_user' ) );
                add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
            }
        }
        elseif ( 'comment' == $pod[ 'type' ] ) {
            if ( !has_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 ) ) {
                add_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 );
                add_filter( 'comment_form_default_fields', array( $this, 'meta_comment_new' ) );
                add_action( 'add_meta_boxes_comment', array( $this, 'meta_comment_add' ) );
                add_action( 'wp_insert_comment', array( $this, 'save_comment' ) );
                add_action( 'edit_comment', array( $this, 'save_comment' ) );
            }
        }
    }

    /**
     * @param $type
     * @param $name
     *
     * @return array
     */
    public function groups_get ( $type, $name ) {
        do_action( 'pods_meta_groups', $type, $name );

        $pod = array();
        $fields = array();

        $object = self::$post_types;

        if ( 'taxonomy' == $type )
            $object = self::$taxonomies;
        elseif ( 'media' == $type )
            $object = self::$media;
        elseif ( 'user' == $type )
            $object = self::$user;
        elseif ( 'comment' == $type )
            $object = self::$comment;

        if ( 'pod' != $type && !empty( $object ) && is_array( $object ) && isset( $object[ $name ] ) )
            $fields = $object[ $name ][ 'fields' ];
        else {
            $pod = $this->api->load_pod( array( 'name' => $name ), false );

            if ( !empty( $pod ) )
                $fields = $pod[ 'fields' ];
        }

        $defaults = array(
            'name' => 'post',
            'object' => 'post',
            'type' => 'post_type'
        );

        $pod = array_merge( $defaults, (array) $pod );

        if ( empty( $pod[ 'name' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];
        elseif ( empty( $pod[ 'object' ] ) )
            $pod[ 'object' ] = $pod[ 'name' ];

        if ( $pod[ 'type' ] != $type )
            return array();

        $groups = array(
            array(
                'pod' => $pod,
                'label' => __( 'More Fields', 'pods' ),
                'fields' => $fields,
                'context' => 'normal',
                'priority' => 'default'
            )
        );

        if ( isset( self::$groups[ $type ] ) && isset( self::$groups[ $type ][ $name ] ) )
            $groups = self::$groups[ $type ][ $name ];

        return $groups;
    }

    /**
     * @param $post_type
     * @param null $post
     */
    public function meta_post_add ( $post_type, $post = null ) {
        if ( 'comment' == $post_type )
            return;

        if ( is_object( $post ) )
            $post_type = $post->post_type;

        $groups = $this->groups_get( 'post_type', $post_type );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( empty( $group[ 'label' ] ) )
                $group[ 'label' ] = get_post_type_object( $post_type )->labels->label;

            add_meta_box(
                $post_type . '-pods-meta-' . sanitize_title( $group[ 'label' ] ),
                $group[ 'label' ],
                array( $this, 'meta_post' ),
                $post_type,
                $group[ 'context' ],
                $group[ 'priority' ],
                array( 'group' => $group )
            );
        }
    }

    /**
     * @param $post
     * @param $metabox
     */
    public function meta_post ( $post, $metabox ) {
        wp_enqueue_style( 'pods-form' );
        ?>
    <table class="form-table pods-metabox">
        <?php
        $id = null;

        if ( is_object( $post ) )
            $id = $post->ID;

        $pod = pods( $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ], $id, true );

        foreach ( $metabox[ 'args' ][ 'group' ][ 'fields' ] as $field ) {
            if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $metabox[ 'args' ][ 'group' ][ 'fields' ], $pod, $id ) )
                continue;

            $value = '';

            if ( !empty( $pod ) ) {
                pods_no_conflict_on( 'post' );

                $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

                pods_no_conflict_off( 'post' );
            }
            elseif ( !empty( $id ) )
                $value = get_post_meta( $id, $field[ 'name' ], true );
            ?>
            <tr class="form-field pods-field">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                <td>
                    <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                    <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    }

    /**
     * @param $post_id
     * @param $post
     *
     * @return mixed
     */
    public function save_post ( $post_id, $post ) {
        $blacklisted_types = array(
            'revision',
            '_pods_pod',
            '_pods_field'
        );

        $blacklisted_types = apply_filters( 'pods_meta_save_post_blacklist_types', $blacklisted_types, $post_id, $post );

        // @todo Figure out how to hook into autosave for saving meta
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_type, $blacklisted_types ) )
            return $post_id;

        $blacklisted_status = array(
            'auto-draft'
        );

        $blacklisted_status = apply_filters( 'pods_meta_save_post_blacklist_status', $blacklisted_status, $post_id, $post );

        if ( in_array( $post->post_status, $blacklisted_status ) )
            return $post_id;

        $groups = $this->groups_get( 'post_type', $post->post_type );

        if ( empty( $groups ) )
            return $post_id;

        // Infinite loop fix
        remove_action( current_filter(), array( $this, __FUNCTION__ ), 10, 2 );

        $data = array();

        $id = $post_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        // Fix for Pods doing it's own sanitization
        $data = stripslashes_deep( $data );

        do_action( 'pods_meta_save_pre_post', $data, $pod, $id, $groups, $post, $post->post_type );
        do_action( "pods_meta_save_pre_post_{$post->post_type}", $data, $pod, $id, $groups, $post );

        if ( !empty( $pod ) )
            $pod->save( $data );
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'post' );

            foreach ( $data as $field => $value ) {
                update_post_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'post' );
        }

        do_action( 'pods_meta_save_post', $data, $pod, $id, $groups, $post, $post->post_type );
        do_action( "pods_meta_save_post_{$post->post_type}", $data, $pod, $id, $groups, $post );

        return $post_id;
    }

    /**
     * @param $form_fields
     * @param $post
     *
     * @return array
     */
    public function meta_media ( $form_fields, $post ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) )
            return $form_fields;

        wp_enqueue_style( 'pods-form' );

        $id = null;

        if ( is_object( $post ) )
            $id = $post->ID;

        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'post' );

                    $value = get_post_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'post' );
                }

                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = array(
                    'label' => $field[ 'label' ],
                    'input' => 'html',
                    'html' => PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ),
                    'helps' => PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field )
                );
            }
        }

        return $form_fields;
    }

    /**
     * @param $post
     * @param $attachment
     *
     * @return mixed
     */
    public function save_media ( $post, $attachment ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) )
            return $post;

        $post_id = $attachment;

        if ( is_array( $post ) && !empty( $post ) && isset( $post[ 'ID' ] ) )
            $post_id = $post[ 'ID' ];

        $data = array();

        $id = $post_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        // Fix for Pods doing it's own sanitization
        $data = stripslashes_deep( $data );

        do_action( 'pods_meta_save_pre_media', $data, $pod, $id, $groups, $post, $attachment );

        if ( !empty( $pod ) )
            $pod->save( $data );
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'post' );

            foreach ( $data as $field => $value ) {
                update_post_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'post' );
        }

        do_action( 'pods_meta_save_media', $data, $pod, $id, $groups, $post, $attachment );

        return $post;
    }

    /**
     * @param $tag
     * @param null $taxonomy
     */
    public function meta_taxonomy ( $tag, $taxonomy = null ) {
        wp_enqueue_style( 'pods-form' );

        $taxonomy_name = $taxonomy;

        if ( !is_object( $tag ) )
            $taxonomy_name = $tag;

        $groups = $this->groups_get( 'taxonomy', $taxonomy_name );

        $id = null;

        if ( is_object( $tag ) )
            $id = $tag->term_id;

        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

                if ( !is_object( $tag ) ) {
                    ?>
                <div class="form-field pods-field">
                    <?php
                    echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                    echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                    ?>
                </div>
                <?php
                }
                else {
                    ?>
                <tr class="form-field pods-field">
                    <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                    <td>
                        <?php
                        echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                        echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                        ?>
                    </td>
                </tr>
                <?php
                }
            }
        }
    }

    /**
     * @param $term_id
     * @param $term_taxonomy_id
     * @param $taxonomy
     */
    public function save_taxonomy ( $term_id, $term_taxonomy_id, $taxonomy ) {
        $groups = $this->groups_get( 'taxonomy', $taxonomy );

        if ( empty( $groups ) )
            return;

        $term = get_term( $term_id, $taxonomy );

        $data = array(
            'name' => $term->name
        );

        $id = $term_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        // Fix for Pods doing it's own sanitization
        $data = stripslashes_deep( $data );

        do_action( 'pods_meta_save_pre_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
        do_action( "pods_meta_save_pre_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );

        if ( !empty( $pod ) )
            $pod->save( $data );

        do_action( 'pods_meta_save_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
        do_action( "pods_meta_save_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
    }

    /**
     * @param $user_id
     */
    public function meta_user ( $user_id ) {
        wp_enqueue_style( 'pods-form' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $groups = $this->groups_get( 'user', 'user' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $id = $user_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
            ?>
        <h3><?php echo $group[ 'label' ]; ?></h3>

        <table class="form-table pods-meta">
            <tbody>
                <?php
                foreach ( $group[ 'fields' ] as $field ) {
                    if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                        continue;

                    $value = '';

                    if ( !empty( $pod ) )
                        $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                    elseif ( !empty( $id ) ) {
                        pods_no_conflict_on( 'user' );

                        $value = get_user_meta( $id, $field[ 'name' ], true );

                        pods_no_conflict_off( 'user' );
                    }
                    ?>
                    <tr class="form-field pods-field">
                        <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                        <td>
                            <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                            <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
        }
    }

    /**
     * @param $user_id
     */
    public function save_user ( $user_id ) {
        $groups = $this->groups_get( 'user', 'user' );

        if ( empty( $groups ) )
            return;

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $data = array();

        $id = $user_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        // Fix for Pods doing it's own sanitization
        $data = stripslashes_deep( $data );

        if ( !empty( $pod ) )
            $pod->save( $data );
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'user' );

            foreach ( $data as $field => $value ) {
                update_user_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'user' );
        }

        do_action( 'pods_meta_save_user', $data, $pod, $id, $groups );
    }

    /**
     * @param $commenter
     * @param $user_identity
     */
    public function meta_comment_new_logged_in ( $commenter, $user_identity ) {
        wp_enqueue_style( 'pods-form' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'comment' );

                    $value = get_comment_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'comment' );
                }
                ?>
            <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?>  pods-field">
                <?php
                echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                ?>
            </p>
            <?php
            }
        }
    }

    /**
     * @param $form_fields
     *
     * @return array
     */
    public function meta_comment_new ( $form_fields ) {
        wp_enqueue_style( 'pods-form' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'comment' );

                    $value = get_comment_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'comment' );
                }

                ob_start();
                ?>
            <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?> pods-field">
                <?php
                echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                ?>
            </p>
            <?php
                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = ob_get_clean();
            }
        }

        return $form_fields;
    }

    /**
     * @param $comment_type
     * @param null $comment
     */
    public function meta_comment_add ( $comment_type, $comment = null ) {
        if ( is_object( $comment ) && isset( $comment_type->comment_type ) )
            $comment_type = $comment->comment_type;

        if ( is_object( $comment_type ) && isset( $comment_type->comment_type ) ) {
            $comment = $comment_type;
            $comment_type = $comment_type->comment_type;
        }

        if ( is_object( $comment_type ) || 'comment' != $comment_type )
            return;

        $groups = $this->groups_get( 'comment', $comment_type );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            add_meta_box(
                $comment_type . '-pods-meta-' . sanitize_title( $group[ 'label' ] ),
                $group[ 'label' ],
                array( $this, 'meta_comment' ),
                $comment_type,
                $group[ 'context' ],
                $group[ 'priority' ],
                array( 'group' => $group )
            );
        }
    }

    /**
     * @param $comment
     * @param $metabox
     */
    public function meta_comment ( $comment, $metabox ) {
        wp_enqueue_style( 'pods-form' );
        ?>
    <table class="form-table editcomment pods-metabox">
        <?php
        $id = null;

        if ( is_object( $comment ) )
            $id = $comment->comment_ID;

        $pod = pods( $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ], $id, true );

        foreach ( $metabox[ 'args' ][ 'group' ][ 'fields' ] as $field ) {
            if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $metabox[ 'args' ][ 'group' ][ 'fields' ], $pod, $id ) )
                continue;

            $value = '';

            if ( !empty( $pod ) )
                $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
            ?>
            <tr class="form-field pods-field">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                <td>
                    <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                    <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    }

    /**
     * @param $comment_id
     */
    public function save_comment ( $comment_id ) {
        $groups = $this->groups_get( 'comment', 'comment' );

        if ( empty( $groups ) )
            return;

        $data = array();

        $id = $comment_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            if ( null === $pod )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id, true );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) )
                    continue;

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        // Fix for Pods doing it's own sanitization
        $data = stripslashes_deep( $data );

        do_action( 'pods_meta_save_pre_comment', $data, $pod, $id, $groups );

        if ( !empty( $pod ) )
            $pod->save( $data );
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'comment' );

            foreach ( $data as $field => $value ) {
                update_comment_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'comment' );
        }

        do_action( 'pods_meta_save_comment', $data, $pod, $id, $groups );
    }

    /**
     * All *_*_meta filter handler aliases
     *
     * @return mixed
     */
    public function get_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_get_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function get_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_get_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function get_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_get_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_add_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_add_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_add_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_update_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_update_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_update_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_delete_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_delete_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_delete_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /*
     * The real meta functions
     */
    /**
     * @param $object_type
     * @param $object_id
     * @param string $aux
     *
     * @return bool|mixed
     */
    public function get_object ( $object_type, $object_id, $aux = '' ) {
        if ( 'post_type' == $object_type )
            $objects = self::$post_types;
        elseif ( 'taxonomy' == $object_type )
            $objects = self::$taxonomies;
        elseif ( 'media' == $object_type )
            $objects = self::$media;
        elseif ( 'user' == $object_type )
            $objects = self::$user;
        elseif ( 'comment' == $object_type )
            $objects = self::$comment;
        else
            return false;

        if ( empty( $objects ) )
            return false;

        if ( 'media' == $object_type )
            return @current( self::$media );
        elseif ( 'user' == $object_type )
            return @current( self::$user );
        elseif ( 'comment' == $object_type )
            return @current( self::$comment );
        elseif ( 'post_type' == $object_type ) {
            $object = get_post( $object_id );

            if ( !is_object( $object ) || !isset( $object->post_type ) )
                return false;

            $object_name = $object->post_type;
        }
        elseif ( 'taxonomy' == $object_type )
            $object_name = $aux;
        else
            return false;

        $reserved_post_types = array(
            '_pods_pod',
            '_pods_field'
        );

        $reserved_post_types = apply_filters( 'pods_meta_reserved_post_types', $reserved_post_types, $object_type, $object_id, $object_name, $objects );

        if ( empty( $object_name ) || ( 'post_type' == $object_type && in_array( $object_name, $reserved_post_types ) ) )
            return false;

        $recheck = array();

        // Return first created by Pods, save extended for later
        foreach ( $objects as $pod ) {
            if ( $object_name == $pod[ 'object' ] )
                $recheck[] = $pod;

            if ( '' == $pod[ 'object' ] && $object_name == $pod[ 'name' ] )
                return $pod;
        }

        // If no objects created by Pods, return first extended
        foreach ( $recheck as $pod ) {
            return $pod;
        }

        return false;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param bool $single
     *
     * @return array|bool|int|mixed|null|string|void
     */
    public function get_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $single = false ) {
        $meta_type = $object_type;

        if ( 'post_type' == $meta_type )
            $meta_type = 'post';

        if ( empty( $meta_key ) )
            return $_null;

        $object = $this->get_object( $object_type, $object_id );

        $field = $meta_key;

        if ( false !== strpos( '.', $field ) )
            $field = @current( explode( '.', $field ) );

        if ( empty( $object_id ) || empty( $field ) || empty( $object ) || ( !isset( $object[ 'fields' ][ $field ] ) && !isset( $object[ 'object_fields' ][ $field ] ) ) )
            return $_null;

        pods_no_conflict_on( $meta_type );

        $meta_cache = array();

        if ( !$single )
            $meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

        if ( !$single || empty( $meta_cache ) || !is_array( $meta_cache ) )
            $meta_cache = array();

        if ( !empty( $meta_cache ) && isset( $meta_cache[ $meta_key ][ $field ] ) )
            $value = $meta_cache[ $meta_key ][ $field ];
        else {
            $pod = pods( $object[ 'name' ], $object_id );

            if ( !empty( $pod ) )
                $meta_cache[ $meta_key ] = $value = $pod->field( $meta_key, $single );
            else {
                pods_no_conflict_off( $meta_type );

                return null;
            }
        }

        if ( !$single )
            wp_cache_add( $object_id, $meta_cache, $meta_type . '_meta' );

        pods_no_conflict_off( $meta_type );

        if ( !is_numeric( $value ) && empty( $value ) ) {
            if ( $single )
                $value = '';
            else
                $value = array();
        }
        elseif ( !is_array( $value ) || !isset( $value[ 0 ] ) )
            $value = array( $value );

        return $value;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $unique
     *
     * @return bool|int|null
     */
    public function add_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
        $meta_type = $object_type;

        if ( 'post_type' == $meta_type )
            $meta_type = 'post';

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        if ( 'meta' == $object[ 'storage' ] )
            return $_null;

        $id = pods( $object[ 'name' ], $object_id )->save( $meta_key, $meta_value );

        return $id;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     *
     * @return bool|int|null
     */
    public function update_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
        $meta_type = $object_type;

        if ( 'post_type' == $meta_type )
            $meta_type = 'post';

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        if ( 'meta' == $object[ 'storage' ] )
            return $_null;

        $id = pods( $object[ 'name' ], $object_id )->save( $meta_key, $meta_value );

        return $id;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $delete_all
     *
     * @return null
     */
    public function delete_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {
        $meta_type = $object_type;

        if ( 'post_type' == $meta_type )
            $meta_type = 'post';

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        if ( 'meta' == $object[ 'storage' ] )
            return $_null;

        $fields = array(
            $meta_key => null
        );

        $id = pods( $object[ 'name' ], $object_id )->save( $fields );

        return $_null;
    }
}
