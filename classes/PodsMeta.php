<?php
class PodsMeta {

    private $api;

    public static $taxonomies;

    public static $post_types;

    public static $media;

    public static $user;

    public static $comment;

    function __construct () {

        $this->api =& pods_api();

        self::$taxonomies = $this->api->load_pods( array( 'orderby' => '`weight`, `name`', 'type' => 'taxonomy' ) );
        self::$post_types = $this->api->load_pods( array( 'orderby' => '`weight`, `name`', 'type' => 'post_type' ) );
        self::$media = $this->api->load_pods( array( 'orderby' => '`weight`, `name`', 'type' => 'media' ) );
        self::$user = $this->api->load_pods( array( 'orderby' => '`weight`, `name`', 'type' => 'user' ) );
        self::$comment = $this->api->load_pods( array( 'orderby' => '`weight`, `name`', 'type' => 'comment' ) );

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
    }

    public function meta_post_add ( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        add_meta_box( $post->post_type . '-pods-meta', $post_type->labels->name, array( $this, 'meta_post' ), $post->post_type, 'normal', 'high' );
    }

    public function meta_post ( $post ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );
?>
    <table class="form-table pods-metabox">
        <?php
            $pod = $this->api->load_pod( array( 'name' => self::$post_types[ $post->post_type ][ 'name' ] ) );
            foreach ( $pod[ 'fields' ] as $field ) {
                $value = '';

                if ( is_object( $post ) ) {
                    if ( in_array( $field[ 'type' ], array( 'pick', 'file' ) ) )
                        $value = get_post_meta( $post->ID, $field[ 'name' ] );
                    else
                        $value = get_post_meta( $post->ID, $field[ 'name' ], true );
                }
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
        // @todo Figure out how to hook into autosave for saving meta
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'revision' == $post->post_type )
            return $post_id;

        if ( !isset( self::$post_types[ $post->post_type ] ) )
            return;

        $pod = $this->api->load_pod( array( 'name' => self::$post_types[ $post->post_type ][ 'name' ] ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                update_post_meta( $post_id, $field[ 'name' ], $_POST[ 'pods_meta_' . $field[ 'name' ] ] );
        }

        return $post_id;
    }

    public function meta_media ( $form_fields, $post ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $pod = $this->api->load_pod( array( 'name' => 'media' ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( in_array( $field[ 'type' ], array( 'pick', 'file' ) ) )
                $value = get_post_meta( $post->ID, $field[ 'name' ] );
            else
                $value = get_post_meta( $post->ID, $field[ 'name' ], true );

            $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = array(
                'label' => $field[ 'label' ],
                'input' => 'html',
                'html' => PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field[ 'options' ] ),
                'helps' => $field[ 'options' ][ 'description' ]
            );
        }

        return $form_fields;
    }

    public function save_media ( $post, $attachment ) {
        $post_id = $attachment;

        if ( is_array( $post ) )
            $post_id = $post[ 'ID' ];

        $pod = $this->api->load_pod( array( 'name' => 'media' ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                update_post_meta( $post_id, $field[ 'name' ], $_POST[ 'pods_meta_' . $field[ 'name' ] ] );
        }

        return $post;
    }

    public function meta_taxonomy ( $tag, $taxonomy = null ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $taxonomy_name = $taxonomy;

        if ( !is_object( $tag ) )
            $taxonomy_name = $tag;

        $pod = $this->api->load_pod( array( 'name' => self::$taxonomies[ $taxonomy_name ][ 'name' ] ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( !is_object( $tag ) ) {
?>
    <div class="form-field">
        <?php
            echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
            echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], '', $field[ 'type' ], $field[ 'options' ] );

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
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], '', $field[ 'type' ], $field[ 'options' ] );

                    if ( isset( $fields[ 'options' ][ 'description' ] ) )
                        echo '<span class="description">' . $field[ 'options' ][ 'description' ] . '</span>';
                ?>
            </td>
        </tr>
    <?php
            }
        }
    }

    public function save_taxonomy ( $term_id, $term_taxonomy_id, $taxonomy ) {
        if ( !isset( self::$taxonomies[ $taxonomy ][ 'name' ] ) )
            return false;

        $pod = $this->api->load_pod( array( 'name' => self::$taxonomies[ $taxonomy ][ 'name' ] ) );

        $item = pods( $pod[ 'name' ], $term_id );
        $data = array();

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
        }

        $item->save( $data );
    }

    public function meta_user ( $user_id ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $pod_name = current( self::$user );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );
?>
    <h3><?php echo $pod[ 'options' ][ 'label' ]; ?></h3>

    <table class="form-table pods-meta">
        <tbody>
            <?php
                foreach ( $pod[ 'fields' ] as $field ) {
            ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] ); ?></th>
                    <td><?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], get_user_meta( $user_id, $field[ 'name' ] ), $field[ 'type' ], $field[ 'options' ] ); ?></td>
                </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
<?php
    }

    public function save_user ( $user_id ) {
        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $pod_name = current( self::$user );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                update_user_meta( $user_id, $field[ 'name' ], $_POST[ 'pods_meta_' . $field[ 'name' ] ] );
        }
    }

    public function meta_comment_new_logged_in ( $commenter, $user_identity ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $pod_name = current( self::$comment );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );
        foreach ( $pod[ 'fields' ] as $field ) {
?>
    <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?>">
        <?php
            echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
            echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], '', $field[ 'type' ], $field[ 'options' ] );
        ?>
    </p>
<?php
        }
    }

    public function meta_comment_new ( $form_fields ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $pod_name = current( self::$comment );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );
        foreach ( $pod[ 'fields' ] as $field ) {
            ob_start();
?>
    <p class="comment-form-author comment-form-pods-meta-<?php echo $field[ 'name' ]; ?>">
        <?php
            echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ] );
            echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], '', $field[ 'type' ], $field[ 'options' ] );
        ?>
    </p>
<?php
            $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = ob_get_clean();
        }
        return $form_fields;
    }

    public function meta_comment_add ( $comment ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $pod_name = current( self::$comment );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );
?>
    <table class="form-table pods-metabox">
        <?php
        foreach ( $pod[ 'fields' ] as $field ) {
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

    public function meta_comment ( $comment ) {
        wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css' );

        $pod_name = current( self::$comment );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );
?>
    <table class="form-table pods-metabox">
        <?php
            foreach ( $pod[ 'fields' ] as $field ) {
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

    public function save_comment ( $comment_id ) {
        $pod_name = current( self::$comment );
        $pod_name = $pod_name[ 'name' ];

        $pod = $this->api->load_pod( array( 'name' => $pod_name ) );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                update_comment_meta( $comment_id, $field[ 'name' ], $_POST[ 'pods_meta_' . $field[ 'name' ] ] );
        }
    }
}
