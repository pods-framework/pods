<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin" />
            <input type="hidden" name="method" value="add_pod" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'pods-add_pod' ); ?>" />
            <input type="hidden" name="create_extend" id="pods_create_extend" value="create" />

            <h2 class="italicized"><?php _e( 'Add New Pod', 'pods' ); ?></h2>

            <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

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
                            <p><?php _e( 'Pods are content types that you can customize and define fields for based on your needs. You can choose to create a Custom Post Type, Custom Taxonomy, or a Custom Pod which operate completely seperate from normal WordPress Objects. You can also extend existing content types like WP Objects such as Post Types, Taxonomies, Users, or Comments', 'pods' ); ?></p>
                        </div>
                        <div id="pods-wizard-options">
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-create" data-opt="create">
                                    <h2><?php _e( 'Create New', 'pods' ); ?></h2>

                                    <p><?php _e( 'Create entirely new content types using <strong>Post Types</strong>, <strong>Taxonomies</strong>, or <strong>Advanced Content Types</strong> with their own tables.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-extend" data-opt="extend">
                                    <h2><?php _e( 'Extend Existing', 'pods' ); ?></h2>

                                    <p><?php _e( 'Extend any existing content type within WordPress, including <strong>Post Types</strong> (Posts, Pages, etc), <strong>Taxonomies</strong> (Categories, Tags, etc), <strong>Media</strong>, <strong>Users</strong>, or <strong>Comments</strong>.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                        </div>
                    </div>
                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-option-content" id="pods-wizard-create">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Creating a new Content Type allows you to control exactly what that content type does, acts like, the field it has, and the way you manage it.', 'pods' ); ?></p>
                            </div>
                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Create a Content Type', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">
                                    <div class="pods-field-option">
                                        <?php
                                        echo PodsForm::label( 'create_pod_type', __( 'Content Type', 'pods' ), __( 'help', 'pods' ) );

                                        $data = array(
                                            'post_type' => __( 'Custom Post Type (like Posts or Pages)', 'pods' ),
                                            'taxonomy' => __( 'Custom Taxonomy (like Categories or Tags)', 'pods' ),
                                            'pod' => __( 'Advanced Content Type (separate from WP, blank slate, in its own table)', 'pods' )
                                        );

                                        echo PodsForm::field( 'create_pod_type', pods_var_raw( 'create_pod_type', 'post' ), 'pick', array( 'data' => $data, 'class' => 'pods-dependent-toggle' ) );
                                        ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php
                                        global $wpdb;
                                        $max_length_name = 64;
                                        $max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
                                        $max_length_name -= strlen( $wpdb->prefix . 'pods_' );

                                        echo PodsForm::label( 'create_label_plural', __( 'Plural Label', 'pods' ), __( 'help', 'pods' ) );
                                        echo PodsForm::field( 'create_label_plural', pods_var_raw( 'create_label_plural', 'post' ), 'text', array( 'class' => 'pods-validate pods-validate-required' ) );
                                        ?>
                                    </div>
                                    <p>
                                        <a href="#pods-advanced" class="pods-advanced-toggle"><?php _e( 'Advanced', 'pods' ); ?> +</a>
                                    </p>

                                    <div class="pods-advanced">
                                        <div class="pods-field-option">
                                            <?php
                                            echo PodsForm::label( 'create_name', __( 'Identifier', 'pods' ), __( 'You will use this name to programatically reference this object throughout WordPress', 'pods' ) );
                                            echo PodsForm::field( 'create_name', pods_var_raw( 'create_name', 'post' ), 'db', array( 'attributes' => array( 'maxlength' => $max_length_name, 'size' => 25, 'data-sluggable' => 'create_label_plural' ), 'class' => 'pods-validate pods-validate-required pods-slugged-lower' ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option">
                                            <?php
                                            echo PodsForm::label( 'create_label_singular', __( 'Singular Label', 'pods' ), __( 'help', 'pods' ) );
                                            echo PodsForm::field( 'create_label_singular', pods_var_raw( 'create_label_singular', 'post' ), 'text' );
                                            ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-post_type">
                                            <?php
                                            echo PodsForm::label( 'create_storage', __( 'Storage Type', 'pods' ), __( 'Table based storage will operate in a way where each field you create for your content type becomes a field in a table, where as Meta based relies upon WordPress\' meta storage table for all field data.', 'pods' ) );

                                            $data = array(
                                                'meta' => 'Meta Based (WP Default)',
                                                'table' => 'Table Based'
                                            );

                                            echo PodsForm::field( 'create_storage', pods_var_raw( 'create_storage', 'post' ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-taxonomy">
                                            <?php
                                            echo PodsForm::label( 'create_storage_taxonomy', __( 'Storage Type', 'pods' ), __( 'Table based storage will operate in a way where each field you create for your content type becomes a field in a table. No other storage types are available in addition to our table-based storage because this WordPress object does not support additional fields natively.', 'pods' ) );

                                            $data = array(
                                                'none' => 'Do not add additional fields',
                                                'table' => 'Table Based'
                                            );

                                            echo PodsForm::field( 'create_storage_taxonomy', pods_var_raw( 'create_storage_taxonomy', 'post', 'none', null, true ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pods-wizard-option-content" id="pods-wizard-extend">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Extending an existing Content Type allows you to add fields to it and take advantage of the Pods architecture for management and optionally for theming.', 'pods' ); ?></p>
                            </div>
                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Extend a Content Type', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">

                                    <div class="pods-field-option">
                                        <?php
                                        echo PodsForm::label( 'extend_pod_type', __( 'Content Type', 'pods' ), __( 'help', 'pods' ) );

                                        $data = array(
                                            'post_type' => __( 'Post Types (Posts, Pages, etc..)', 'pods' ),
                                            'taxonomy' => __( 'Taxonomies (Categories, Tags, etc..)', 'pods' ),
                                            'media' => __( 'Media', 'pods' ),
                                            'user' => __( 'Users', 'pods' ),
                                            'comment' => __( 'Comments', 'pods' )
                                        );

                                        echo PodsForm::field( 'extend_pod_type', pods_var_raw( 'extend_pod_type', 'post' ), 'pick', array( 'data' => $data, 'class' => 'pods-dependent-toggle' ) );
                                        ?>
                                    </div>
                                    <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type">
                                        <?php
                                        $post_types = get_post_types();
                                        $ignore = array( 'attachment', 'revision', 'nav_menu_item' );

                                        foreach ( $post_types as $post_type => $label ) {
                                            if ( in_array( $post_type, $ignore ) || empty( $post_type ) || 0 === strpos( $post_type, '_pods_' ) ) {
                                                unset( $post_types[ $post_type ] );
                                                continue;
                                            }

                                            $post_type = get_post_type_object( $post_type );
                                            $post_types[ $post_type->name ] = $post_type->label;
                                        }

                                        echo PodsForm::label( 'extend_post_type', __( 'Post Type', 'pods' ), __( 'help', 'pods' ) );
                                        echo PodsForm::field( 'extend_post_type', pods_var_raw( 'extend_post_type', 'post', 'table', null, true ), 'pick', array( 'data' => $post_types ) );
                                        ?>
                                    </div>
                                    <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-taxonomy">
                                        <?php
                                        $taxonomies = get_taxonomies();
                                        $ignore = array( 'nav_menu', 'link_category', 'post_format' );

                                        foreach ( $taxonomies as $taxonomy => $label ) {
                                            if ( in_array( $taxonomy, $ignore ) ) {
                                                unset( $taxonomies[ $taxonomy ] );
                                                continue;
                                            }

                                            $taxonomy = get_taxonomy( $taxonomy );
                                            $taxonomies[ $taxonomy->name ] = $taxonomy->label;
                                        }

                                        echo PodsForm::label( 'extend_taxonomy', __( 'Taxonomy', 'pods' ), __( 'help', 'pods' ) );
                                        echo PodsForm::field( 'extend_taxonomy', pods_var_raw( 'extend_taxonomy', 'post' ), 'pick', array( 'data' => $taxonomies ) );
                                        ?>
                                    </div>

                                    <p><a href="#pods-advanced" class="pods-advanced-toggle"><?php _e( 'Advanced', 'pods' ); ?> +</a></p>

                                    <div class="pods-advanced">
                                        <div class="pods-field-option pods-depends-on pods-depends-on-extend-pod-type pods-depends-on-extend-pod-type-post_type pods-depends-on-extend-pod-type-media pods-depends-on-extend-pod-type-user pods-depends-on-extend-pod-type-comment">
                                            <?php
                                            echo PodsForm::label( 'extend_storage', __( 'Storage Type', 'pods' ), __( 'Table based storage will operate in a way where each field you create for your content type becomes a field in a table, where as Meta based relies upon WordPress\' meta storage table for all field data.', 'pods' ) );

                                            $data = array(
                                                'meta' => __( 'Meta Based (WP Default)', 'pods' ),
                                                'table' => __( 'Table Based', 'pods' )
                                            );

                                            echo PodsForm::field( 'extend_storage', pods_var_raw( 'extend_storage', 'post' ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-create-pod-type pods-depends-on-create-pod-type-taxonomy">
                                            <?php
                                            echo PodsForm::label( 'extend_storage_taxonomy', __( 'Storage Type', 'pods' ), __( 'Table based storage will operate in a way where each field you create for your content type becomes a field in a table. No other storage types are available in addition to our table-based storage because this WordPress object does not support additional fields natively.', 'pods' ) );

                                            $data = array(
                                                'none' => 'Do not add additional fields',
                                                'table' => 'Table Based'
                                            );

                                            echo PodsForm::field( 'extend_storage_taxonomy', pods_var_raw( 'extend_storage_taxonomy', 'post' ), 'pick', array( 'data' => $data ) );
                                            ?>
                                        </div>
                                    </div>
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
