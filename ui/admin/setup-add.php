<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo esc_js( PODS_URL ); ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin" />
            <input type="hidden" name="method" value="add_pod" />
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'pods-add_pod' ) ); ?>" />
            <input type="hidden" name="create_extend" id="pods_create_extend" value="create" />

            <h2 class="italicized">
                <?php
                    _e( 'Add New Pod', 'pods' );

                    $all_pods = pods_api()->load_pods( array( 'key_names' => true ) );

                    if ( !empty( $all_pods ) ) {
                        $link = pods_query_arg( array( 'page' => 'pods', 'action' . $obj->num => 'manage' ) );
                ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="add-new-h2">&laquo; <?php _e( 'Back to Manage', 'pods' ); ?></a>
                <?php
                    }
                ?>
            </h2>

            <img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
                <div id="pods-wizard-heading">
                    <ul>
                        <li class="pods-wizard-menu-current" data-step="1">
                            <i></i> <span>1</span> <?php _e( 'Create or Extend', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="2">
                            <i></i> <span>2</span> <?php _e( 'Configure', 'pods' ); ?>
                            <em></em>
                        </li>
                    </ul>
                </div>
                <div id="pods-wizard-main">
                    <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p>
                                <?php _e( 'Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or Custom Settings Pages for site-specific data. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments.', 'pods' ); ?>
                                <br /><br />
                                <?php _e( 'Not sure what content type you should use? Check out our <a href="http://pods.io/docs/comparisons/compare-content-types/" target="_blank">Content Type Comparison</a> to help you decide.', 'pods' ); ?>
                            </p>

                        </div>
                        <div id="pods-wizard-options">
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-create" data-opt="create">
                                    <h2><?php _e( 'Create New', 'pods' ); ?></h2>

                                    <p><?php _e( 'Create entirely new content types using <strong>Post Types</strong>, <strong>Taxonomies</strong>, or <strong>Custom Settings Pages</strong>.', 'pods' ); ?></p>
                                </a>

                            </div>
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-extend" data-opt="extend">
                                    <h2><?php _e( 'Extend Existing', 'pods' ); ?></h2>

                                    <p><?php _e( 'Extend any existing content type within WordPress, including <strong>Post Types</strong> (Posts, Pages, etc), <strong>Taxonomies</strong> (Categories, Tags, etc), <strong>Media</strong>, <strong>Users</strong>, or <strong>Comments</strong>.', 'pods' ); ?></p>
                                </a>

                            </div>
                        </div>
                    </div>
                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-option-content" id="pods-wizard-create">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Creating a new Content Type allows you to control exactly what that content type does, how it acts like, the fields it has, and the way you manage it.', 'pods' ); ?></p>
                            </div>
                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Create a New Content Type', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">
                                    <div class="pods-field-option">
                                        <?php
                                            echo PodsForm::label( 'create_pod_type', __( 'Content Type', 'pods' ), array( __( '<h6>Content Types</h6> There are many content types to choose from, we have put together a comparison between them all to help you decide what fits your needs best.', 'pods' ), 'http://pods.io/docs/comparisons/compare-content-types/' ) );

                                            $data = array(
                                                'post_type' => __( 'Custom Post Type (like Posts or Pages)', 'pods' ),
                                                'taxonomy' => __( 'Custom Taxonomy (like Categories or Tags)', 'pods' ),
                                                'settings' => __( 'Custom Settings Page', 'pods' ),
                                                'pod' => '' // component will fill this in if it's enabled (this exists for placement)
                                            );

                                            $data = apply_filters( 'pods_admin_setup_add_create_pod_type', $data );

                                            if ( empty( $data[ 'pod' ] ) )
                                                unset( $data[ 'pod' ] );

                                            echo PodsForm::field( 'create_pod_type', pods_var_raw( 'create_pod_type', 'post' ), 'pick', array( 'data' => $data, 'class' => 'pods-dependent-toggle' ) );
                                        ?>
                                    </div>

                                    <?php
                                        if ( ( !pods_tableless() ) && apply_filters( 'pods_admin_setup_add_create_taxonomy_storage', false ) ) {
                                    ?>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-taxonomy">
                                            <?php
                                                echo PodsForm::label( 'create_storage_taxonomy', __( 'Enable Extra Fields?', 'pods' ), array( __( '<h6>Storage Types</h6> Taxonomies do not support extra fields natively, but Pods can add this feature for you easily. Table based storage will operate in a way where each field you create for your content type becomes a field in a table.', 'pods' ), 'http://pods.io/docs/comparisons/compare-storage-types/' ) );

                                                $data = array(
                                                    'none' => __( 'Do not enable extra fields to be added', 'pods' ),
                                                    'table' => __( 'Enable extra fields for this Taxonomy (Table Based)', 'pods' )
                                                );

                                                $default = 'none';

                                                if ( function_exists( 'get_term_meta' ) ) {
                                                    $data = array(
                                                        'meta' => __( 'Meta Based (WP Default)', 'pods' ),
                                                        'table' => $data['table'],
                                                    );

                                                    $default = 'meta';
                                                }

                                                echo PodsForm::field( 'create_storage_taxonomy', pods_var_raw( 'create_storage_taxonomy', 'post', $default, null, true ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                    <?php
                                        }
                                    ?>

                                    <div class="pods-excludes-on pods-excludes-on-create-pod-type pods-excludes-on-create-pod-type-settings">
                                        <div class="pods-field-option">
                                            <?php
                                                echo PodsForm::label( 'create_label_singular', __( 'Singular Label', 'pods' ), __( '<h6>Singular Label</h6> This is the label for 1 item (Singular) that will appear throughout the WordPress admin area for managing the content.', 'pods' ) );
                                                echo PodsForm::field( 'create_label_singular', pods_var_raw( 'create_label_singular', 'post' ), 'text', array( 'class' => 'pods-validate pods-validate-required', 'text_max_length' => 30 ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php
                                                echo PodsForm::label( 'create_label_plural', __( 'Plural Label', 'pods' ), __( '<h6>Plural Label</h6> This is the label for more than 1 item (Plural) that will appear throughout the WordPress admin area for managing the content.', 'pods' ) );
                                                echo PodsForm::field( 'create_label_plural', pods_var_raw( 'create_label_plural', 'post' ), 'text', array( 'text_max_length' => 30 ) );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-settings">
                                        <div class="pods-field-option">
                                            <?php
                                                echo PodsForm::label( 'create_label_title', __( 'Page Title', 'pods' ), __( '<h6>Page Title</h6> This is the text that will appear at the top of your settings page.', 'pods' ) );
                                                echo PodsForm::field( 'create_label_title', pods_var_raw( 'create_label_title', 'post' ), 'text', array( 'class' => 'pods-validate pods-validate-required', 'text_max_length' => 30 ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php
                                                echo PodsForm::label( 'create_label_menu', __( 'Menu Label', 'pods' ), __( '<h6>Menu Label</h6> This is the label that will appear throughout the WordPress admin area for your settings.', 'pods' ) );
                                                echo PodsForm::field( 'create_label_menu', pods_var_raw( 'create_label_menu', 'post' ), 'text', array( 'text_max_length' => 30 ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php
                                                echo PodsForm::label( 'create_menu_location', __( 'Menu Location', 'pods' ), __( '<h6>Menu Location</h6> This is the location where the new settings page will be added in the WordPress Dashboard menu.', 'pods' ) );

                                                $data = array(
                                                    'settings' => 'Add to Settings menu',
                                                    'appearances' => 'Add to Appearances menu',
                                                    'top' => 'Make a new menu item below Settings'
                                                );

                                                echo PodsForm::field( 'create_menu_location', pods_var_raw( 'create_menu_location', 'post' ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                    </div>

                                    <p>
                                        <a href="#pods-advanced" class="pods-advanced-toggle"><?php _e( 'Advanced', 'pods' ); ?> +</a>
                                    </p>

                                    <div class="pods-advanced">
                                        <div class="pods-field-option pods-excludes-on pods-excludes-on-create-pod-type pods-excludes-on-create-pod-type-settings">
                                            <?php
                                                global $wpdb;
                                                $max_length_name = 64;
                                                $max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
                                                $max_length_name -= strlen( $wpdb->prefix . 'pods_' );

                                                echo PodsForm::label( 'create_name', __( 'Pod Name', 'pods' ), __( '<h6>Pod Indentifier</h6> This is different than the labels users will see in the WordPress admin areas, it is the name you will use to programatically reference this object throughout your theme, WordPress, and other PHP.', 'pods' ) );
                                                echo PodsForm::field( 'create_name', pods_var_raw( 'create_name', 'post' ), 'db', array( 'attributes' => array( 'maxlength' => $max_length_name, 'size' => 25 ) ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-settings">
                                            <?php
                                                global $wpdb;
                                                $max_length_name = 64;
                                                $max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
                                                $max_length_name -= strlen( $wpdb->prefix . 'pods_' );

                                                echo PodsForm::label( 'create_setting_name', __( 'Pod Name', 'pods' ), __( '<h6>Pod Indentifier</h6> This is different than the labels users will see in the WordPress admin areas, it is the name you will use to programatically reference this object throughout your theme, WordPress, and other PHP.', 'pods' ) );
                                                echo PodsForm::field( 'create_setting_name', pods_var_raw( 'create_setting_name', 'post' ), 'db', array( 'attributes' => array( 'maxlength' => $max_length_name, 'size' => 25 ) ) );
                                            ?>
                                        </div>

                                        <?php
                                            if ( ( !pods_tableless() ) && apply_filters( 'pods_admin_setup_add_create_storage', false ) ) {
                                        ?>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-post-type">
                                                <?php
                                                    echo PodsForm::label( 'create_storage', __( 'Storage Type', 'pods' ), array( __( '<h6>Storage Types</h6> Table based storage will operate in a way where each field you create for your content type becomes a field in a table. Meta based storage relies upon the WordPress meta storage table for all field data.', 'pods' ), 'http://pods.io/docs/comparisons/compare-storage-types/' ) );

                                                    $data = array(
                                                        'meta' => __( 'Meta Based (WP Default)', 'pods' ),
                                                        'table' => __( 'Table Based', 'pods' )
                                                    );

                                                    echo PodsForm::field( 'create_storage', pods_var_raw( 'create_storage', 'post' ), 'pick', array( 'data' => $data ) );
                                                ?>
                                            </div>
                                        <?php
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pods-wizard-option-content" id="pods-wizard-extend">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Extending an existing Content Type allows you to add fields to it and take advantage of the Pods architecture for management and optionally for theming.', 'pods' ); ?></p>
                            </div>
                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Extend an Existing Content Type', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">

                                    <div class="pods-field-option">
                                        <?php
                                            echo PodsForm::label( 'extend_pod_type', __( 'Content Type', 'pods' ), array( __( '<h6>Content Types</h6> There are many content types to choose from, we have put together a comparison between them all to help you decide what fits your needs best.', 'pods' ), 'http://pods.io/docs/comparisons/compare-content-types/' ) );

                                            $data = array(
                                                'post_type' => __( 'Post Types (Posts, Pages, etc..)', 'pods' ),
                                                'taxonomy' => '', // component will fill this in if it's enabled (this exists for placement)
                                                'media' => __( 'Media', 'pods' ),
                                                'user' => __( 'Users', 'pods' ),
                                                'comment' => __( 'Comments', 'pods' )
                                            );

                                            if ( function_exists( 'get_term_meta' ) ) {
                                                $data[ 'taxonomy' ] = __( 'Taxonomies (Categories, Tags, etc..)', 'pods' );
                                            }

                                            if ( isset( $all_pods[ 'media' ] ) && 'media' == $all_pods[ 'media' ][ 'type' ] )
                                                unset( $data[ 'media' ] );

                                            if ( isset( $all_pods[ 'user' ] ) && 'user' == $all_pods[ 'user' ][ 'type' ] )
                                                unset( $data[ 'user' ] );

                                            if ( isset( $all_pods[ 'comment' ] ) && 'comment' == $all_pods[ 'comment' ][ 'type' ] )
                                                unset( $data[ 'comment' ] );

                                            $data = apply_filters( 'pods_admin_setup_add_extend_pod_type', $data );

                                            if ( empty( $data[ 'taxonomy' ] ) )
                                                unset( $data[ 'taxonomy' ] );

                                            echo PodsForm::field( 'extend_pod_type', pods_var_raw( 'extend_pod_type', 'post' ), 'pick', array( 'data' => $data, 'class' => 'pods-dependent-toggle' ) );
                                        ?>
                                    </div>
                                    <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post-type">
                                        <?php
                                            $post_types = get_post_types();
                                            $ignore = array( 'attachment', 'revision', 'nav_menu_item' );

                                            foreach ( $post_types as $post_type => $label ) {
                                                if ( in_array( $post_type, $ignore ) || empty( $post_type ) || 0 === strpos( $post_type, '_pods_' ) ) {
                                                    unset( $post_types[ $post_type ] );
                                                    continue;
                                                }
                                                elseif ( isset( $all_pods[ $post_type ] ) && 'post_type' == $all_pods[ $post_type ][ 'type' ] ) {
                                                    unset( $post_types[ $post_type ] );
                                                    continue;
                                                }

                                                $post_type = get_post_type_object( $post_type );
                                                $post_types[ $post_type->name ] = $post_type->label;
                                            }

                                            echo PodsForm::label( 'extend_post_type', __( 'Post Type', 'pods' ), array( __( '<h6>Post Types</h6> WordPress can hold and display many different types of content. Internally, these are all stored in the same place, in the wp_posts table. These are differentiated by a column called post_type.', 'pods' ), 'http://codex.wordpress.org/Post_Types' ) );
                                            echo PodsForm::field( 'extend_post_type', pods_var_raw( 'extend_post_type', 'post', 'table', null, true ), 'pick', array( 'data' => $post_types ) );
                                        ?>
                                    </div>
                                    <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-taxonomy">
                                        <?php
                                            $taxonomies = get_taxonomies();

                                            //Add Support for built-in taxonomy "link_category"
                                            //if links are in use.
                                            $bookmarkcount = count(get_bookmarks());
                                            if ($bookmarkcount < 1){
                                                $ignore = array( 'link_category' );
                                            }

                                            foreach ( $taxonomies as $taxonomy => $label ) {
                                                if ( in_array( $taxonomy, $ignore ) ) {
                                                    unset( $taxonomies[ $taxonomy ] );
                                                    continue;
                                                }
                                                elseif ( isset( $all_pods[ $taxonomy ] ) && 'taxonomy' == $all_pods[ $taxonomy ][ 'type' ] ) {
                                                    unset( $taxonomies[ $taxonomy ] );
                                                    continue;
                                                }

                                                $taxonomy = get_taxonomy( $taxonomy );
                                                $taxonomies[ $taxonomy->name ] = $taxonomy->label;
                                            }

                                            echo PodsForm::label( 'extend_taxonomy', __( 'Taxonomy', 'pods' ), array( __( '<h6>Taxonomies</h6> A taxonomy is a way to group Post Types.', 'pods' ), 'http://codex.wordpress.org/Taxonomies' ) );
                                            echo PodsForm::field( 'extend_taxonomy', pods_var_raw( 'extend_taxonomy', 'post' ), 'pick', array( 'data' => $taxonomies ) );
                                        ?>
                                    </div>

                                    <?php
                                        if ( ( !pods_tableless() ) && apply_filters( 'pods_admin_setup_add_extend_taxonomy_storage', false ) ) {
                                    ?>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-taxonomy">
                                            <?php
                                                echo PodsForm::label( 'extend_storage_taxonomy', __( 'Enable Extra Fields?', 'pods' ), array( __( '<h6>Storage Types</h6> Taxonomies do not support extra fields natively, but Pods can add this feature for you easily. Table based storage will operate in a way where each field you create for your content type becomes a field in a table.', 'pods' ), 'http://pods.io/docs/comparisons/compare-storage-types/' ) );

                                                $data = array(
                                                    'none' => __( 'Do not enable extra fields to be added', 'pods' ),
                                                    'table' => __( 'Enable extra fields for this Taxonomy (Table Based)', 'pods' )
                                                );

                                                $default = 'none';

                                                if ( function_exists( 'get_term_meta' ) ) {
                                                    $data = array(
                                                        'meta' => __( 'Meta Based (WP Default)', 'pods' ),
                                                        'table' => $data['table'],
                                                    );

                                                    $default = 'meta';
                                                }

                                                echo PodsForm::field( 'extend_storage_taxonomy', pods_var_raw( 'extend_storage_taxonomy', 'post', $default, null, true ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                    <?php
                                        }
                                    ?>

                                    <?php
                                        if ( ( !pods_tableless() ) && apply_filters( 'pods_admin_setup_add_extend_storage', false ) ) {
                                    ?>
                                        <div class="pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post-type pods-depends-on-extend-pod-type-media pods-depends-on-extend-pod-type-user pods-depends-on-extend-pod-type-comment">
                                            <p><a href="#pods-advanced" class="pods-advanced-toggle"><?php _e( 'Advanced', 'pods' ); ?> +</a></p>

                                            <div class="pods-advanced">
                                                <div class="pods-field-option">
                                                    <?php
                                                        echo PodsForm::label( 'extend_storage', __( 'Storage Type', 'pods' ), array( __( '<h6>Storage Types</h6> Table based storage will operate in a way where each field you create for your content type becomes a field in a table. Meta based storage relies upon the WordPress meta storage table for all field data.', 'pods' ), 'http://pods.io/docs/comparisons/compare-storage-types/' ) );

                                                        $data = array(
                                                            'meta' => __( 'Meta Based (WP Default)', 'pods' ),
                                                            'table' => __( 'Table Based', 'pods' )
                                                        );

                                                        echo PodsForm::field( 'extend_storage', pods_var_raw( 'extend_storage', 'post' ), 'pick', array( 'data' => $data ) );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-actions">
                        <div id="pods-wizard-toolbar">
                            <a href="#start" id="pods-wizard-start" class="button button-secondary"><?php _e( 'Start Over', 'pods' ); ?></a> <a href="#next" id="pods-wizard-next" class="button button-primary" data-next="<?php esc_attr_e( 'Next Step', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php _e( 'Next Step', 'pods' ); ?></a>
                        </div>
                        <div id="pods-wizard-finished">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    var pods_admin_submit_callback = function ( id ) {
        id = parseInt( id );

        if ( !isNaN( id ) ) {
            document.location = 'admin.php?page=pods&action=edit&id=' + id + '&do=create';
        }
        else {
            document.location = 'admin.php?page=pods&do=create';
        }
    }

    var pods_admin_option_select_callback = function ( $opt ) {
        jQuery( '#pods_create_extend' ).val( $opt.data( 'opt' ) );
    }

    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'sluggable' );

        $( document ).find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
            $( this ).trigger( 'change' );
        } );
    } );
</script>
