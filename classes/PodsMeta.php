<?php
class PodsMeta {

    private $api;

    public static $post_types = array();

    public static $taxonomies = array();

    public static $media = array();

    public static $user = array();

    public static $comment = array();

    public static $groups = array();

    function __construct () {
        $this->api =& pods_api();
    }

    public function init () {
        self::$post_types = $this->api->load_pods( array( 'type' => 'post_type' ) );
        self::$taxonomies = $this->api->load_pods( array( 'type' => 'taxonomy' ) );
        self::$media = $this->api->load_pods( array( 'type' => 'media' ) );
        self::$user = $this->api->load_pods( array( 'type' => 'user' ) );
        self::$comment = $this->api->load_pods( array( 'type' => 'comment' ) );

        if ( !empty( self::$post_types ) ) {
            // Handle Post Type Editor
            foreach ( self::$post_types as $post_type ) {
                $post_type_name = $post_type[ 'name' ];

                if ( !empty( $post_type[ 'object' ] ) )
                    $post_type_name = $post_type[ 'object' ];

                add_action( 'add_meta_boxes_' . $post_type_name, array( $this, 'meta_post_add' ) );
            }

            add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
        }

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
        }

        if ( !empty( self::$comment ) ) {
            // Handle Comment Editor
            add_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 );
            add_filter( 'comment_form_default_fields', array( $this, 'meta_comment_new' ) );
            add_action( 'add_meta_boxes_comment', array( $this, 'meta_comment_add' ) );
            add_action( 'wp_insert_comment', array( $this, 'save_comment' ) );
            add_action( 'edit_comment', array( $this, 'save_comment' ) );
        }

        do_action( 'pods_meta_init' );
    }

    public function group_add ( $pod, $label, $fields, $context = 'normal', $priority = 'default' ) {
        $defaults = array(
            'name' => '',
            'object' => 'post',
            'type' => 'post_type'
        );

        if ( !is_array( $pod ) )
            $pod = pods_api()->load_pod( array( 'name' => $pod ) );
        else
            $pod = array_merge( $defaults, $pod );

        if ( empty( $pod[ 'name' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];

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

        foreach ( $fields as $k => $field ) {
            if ( !is_array( $field ) ) {
                if ( is_numeric( $k ) )
                    $k = $field;

                if ( isset( $pod[ 'fields' ] ) && isset( $pod[ 'fields' ][ $k ] ) )
                    $field = $pod[ 'fields' ][ $k ];
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
        $fields = PodsForm::option_setup( $_fields );

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

        self::$groups[ $object_name ][] = $group;

        // Hook it up!
        if ( 'post' == $pod[ 'type' ] ) {
            if ( !has_action( 'add_meta_boxes_' . $pod[ 'object' ], array( $this, 'meta_post_add' ) ) )
                add_action( 'add_meta_boxes_' . $pod[ 'object' ], array( $this, 'meta_post_add' ) );

            if ( !has_action( 'save_post', array( $this, 'save_post' ), 10, 2 ) )
                add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
        elseif( 'media' == $pod[ 'type' ] ) {
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

    public function groups_get ( $type, $name ) {
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

        if ( 'pod' != $type && isset( $object[ $name ] ) )
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

    public function meta_post_add ( $post ) {
        $groups = $this->groups_get( 'post_type', $post->post_type );

        foreach ( $groups as $k => $group ) {
            if ( empty( $group[ 'label' ] ) )
                $group[ 'label' ] = get_post_type_object( $post->post_type )->labels->label;

            add_meta_box(
                $post->post_type . '-pods-meta-' . sanitize_title( $group[ 'label' ] ),
                $group[ 'label' ],
                array( $this, 'meta_post' ),
                $post->post_type,
                $group[ 'context' ],
                $group[ 'priority' ],
                array( 'group' => $group )
            );
        }
    }

    public function meta_post ( $post, $metabox ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );
?>
    <table class="form-table pods-metabox">
        <?php
            $id = null;

            if ( is_object( $post ) )
                $id = $post->ID;

            $pod = pods( $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ], $id );

            foreach ( $metabox[ 'args' ][ 'group' ][ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );
        ?>
            <tr class="form-field">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                <td><?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] ); ?></td>
            </tr>
        <?php
            }
        ?>
    </table>
<?php
    }

    public function save_post ( $post_id, $post ) {
        $blacklisted_types = array(
            'revision',
            'auto-draft',
            '_pods_pod',
            '_pods_field',
            '_pods_object_template',
            '_pods_object_page',
            '_pods_object_helper'
        );

        $blacklisted_types = apply_filters( 'pods_meta_save_post_blacklist', $blacklisted_types, $post_id, $post );

        // @todo Figure out how to hook into autosave for saving meta
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_type, $blacklisted_types ) )
            return $post_id;

        $groups = $this->groups_get( 'post_type', $post->post_type );

        if ( empty( $groups ) )
            return $post_id;

        $data = array();

        $id = $post_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( !empty( $pod ) )
            $pod->save( $data );

        return $post_id;
    }

    public function meta_media ( $form_fields, $post ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) )
            return $form_fields;

        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $id = null;

        if ( is_object( $post ) )
            $id = $post->ID;

        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );

                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = array(
                    'label' => $field[ 'label' ],
                    'input' => 'html',
                    'html' => PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] ),
                    'helps' => $field[ 'options' ][ 'description' ]
                );
            }
        }

        return $form_fields;
    }

    public function save_media ( $post, $attachment ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) )
            return $post;

        $post_id = $attachment;

        if ( is_array( $post ) )
            $post_id = $post[ 'ID' ];

        $data = array();

        $id = $post_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( !empty( $pod ) )
            $pod->save( $data );

        return $post;
    }

    public function meta_taxonomy ( $tag, $taxonomy = null ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $taxonomy_name = $taxonomy;

        if ( !is_object( $tag ) )
            $taxonomy_name = $tag;

        $groups = $this->groups_get( 'taxonomy', $taxonomy );

        $id = null;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );

                if ( !is_object( $tag ) ) {
?>
    <div class="form-field">
        <?php
                    echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] );

                    if ( isset( $fields[ 'options' ][ 'description' ] ) )
                        echo wpautop( $field[ 'options' ][ 'description' ] );
        ?>
    </div>
    <?php
                }
                else {
    ?>
        <tr class="form-field">
            <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
            <td>
                <?php
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] );

                    if ( isset( $fields[ 'options' ][ 'description' ] ) )
                        echo '<span class="description">' . $field[ 'options' ][ 'description' ] . '</span>';
                ?>
            </td>
        </tr>
    <?php
                }
            }
        }
    }

    public function save_taxonomy ( $term_id, $term_taxonomy_id, $taxonomy ) {
        $groups = $this->groups_get( 'taxonomy', $taxonomy );

        if ( empty( $groups ) )
            return;

        $data = array();

        $id = $term_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( !empty( $pod ) )
            $pod->save( $data );
    }

    public function meta_user ( $user_id ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $groups = $this->groups_get( 'user', 'user' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $id = $user_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );
?>
    <h3><?php echo $group[ 'label' ]; ?></h3>

    <table class="form-table pods-meta">
        <tbody>
            <?php
                foreach ( $group[ 'fields' ] as $field ) {
                    $value = '';

                    if ( !empty( $pod ) )
                        $value = $pod->field( $field[ 'name' ] );
            ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                    <td><?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] ); ?></td>
                </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
<?php
        }
    }

    public function save_user ( $user_id ) {
        $groups = $this->groups_get( 'user', 'user' );

        if ( empty( $groups ) )
            return;

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $data = array();

        $id = $user_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( !empty( $pod ) )
            $pod->save( $data );
    }

    public function meta_comment_new_logged_in ( $commenter, $user_identity ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );
?>
    <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?>">
        <?php
                echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] );
        ?>
    </p>
<?php
            }
        }
    }

    public function meta_comment_new ( $form_fields ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );

                ob_start();
?>
    <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?>">
        <?php
                echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
                echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] );
        ?>
    </p>
<?php
                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = ob_get_clean();
            }
        }
        return $form_fields;
    }

    public function meta_comment_add ( $comment ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;

        if ( is_object( $comment ) )
            $id = $comment->comment_ID;

        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );
?>
    <table class="form-table pods-metabox">
        <?php
            foreach ( $pod[ 'fields' ] as $field ) {
                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( $field[ 'name' ] );
        ?>
            <tr class="form-field">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                <td><?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] ); ?></td>
            </tr>
        <?php
            }
        ?>
    </table>
<?php
        }
    }

    public function meta_comment ( $comment ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;

        if ( is_object( $comment ) )
            $id = $comment->comment_ID;

        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );
?>
    <table class="form-table pods-metabox">
        <?php
                foreach ( $group[ 'fields' ] as $field ) {
        ?>
            <tr class="form-field">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                <td><?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], ( is_object( $comment ) ? get_comment_meta( $comment->comment_ID, $field[ 'name' ] ) : '' ), $field[ 'type' ], $field[ 'options' ] ); ?></td>
            </tr>
        <?php
                }
        ?>
    </table>
<?php
        }
    }

    public function save_comment ( $comment_id ) {
        $groups = $this->groups_get( 'comment', 'comment' );

        if ( empty( $groups ) )
            return;

        $data = array();

        $id = $comment_id;
        $pod = false;

        foreach ( $groups as $group ) {
            if ( empty( $pod ) )
                $pod = pods( $group[ 'pod' ][ 'name' ], $id );

            foreach ( $group[ 'fields' ] as $field ) {
                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( !empty( $pod ) )
            $pod->save( $data );
    }
}
