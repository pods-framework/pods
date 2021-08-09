<?php
global $pods_i;

$api = pods_api();

$pod = $api->load_pod( array( 'id' => $obj->id ) );

$pod_type = pods_v( 'type', $pod );

if ( 'taxonomy' == $pod[ 'type' ] && 'none' == $pod[ 'storage' ] && 1 == pods_v( 'enable_extra_fields' ) ) {
    $api->save_pod( array( 'id' => $obj->id, 'storage' => 'table' ) );

    $pod = $api->load_pod( array( 'id' => $obj->id ) );

    unset( $_GET[ 'enable_extra_fields' ] );

    pods_message( __( 'Extra fields were successfully enabled for this Custom Taxonomy.', 'pods' ) );
}

$field_types = PodsForm::field_types();

$field_types_select = array();

foreach ( $field_types as $type => $field_type_data ) {
    /**
     * @var $field_type PodsField
     */
    $field_type = PodsForm::field_loader( $type, $field_type_data[ 'file' ] );

    $field_type_vars = get_class_vars( get_class( $field_type ) );

    if ( !isset( $field_type_vars[ 'pod_types' ] ) )
        $field_type_vars[ 'pod_types' ] = true;

    // Only show supported field types
    if ( true !== $field_type_vars[ 'pod_types' ] ) {
        if ( empty( $field_type_vars[ 'pod_types' ] ) )
            continue;
        elseif ( is_array( $field_type_vars[ 'pod_types' ] ) && !in_array( $pod_type, $field_type_vars[ 'pod_types' ], true ) )
            continue;
        elseif ( !is_array( $field_type_vars[ 'pod_types' ] ) && $pod_type != $field_type_vars[ 'pod_types' ] )
            continue;
    }

    if ( !empty( PodsForm::$field_group ) ) {
        if ( !isset( $field_types_select[ PodsForm::$field_group ] ) )
            $field_types_select[ PodsForm::$field_group ] = array();

        $field_types_select[ PodsForm::$field_group ][ $type ] = $field_type_data[ 'label' ];
    }
    else {
        if ( !isset( $field_types_select[ __( 'Other', 'pods' ) ] ) )
            $field_types_select[ __( 'Other', 'pods' ) ] = array();

        $field_types_select[ __( 'Other', 'pods' ) ][ $type ] = $field_type_data[ 'label' ];
    }
}

$field_defaults = array(
    'name' => 'new_field',
    'label' => 'New Field',
    'description' => '',
    'type' => 'text',
    'pick_object' => '',
    'sister_id' => '',
    'required' => 0,
    'unique' => 0,
);

$pick_object = PodsForm::field_method( 'pick', 'related_objects', true );

$tableless_field_types = PodsForm::tableless_field_types();
$simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );
$bidirectional_objects = PodsForm::field_method( 'pick', 'bidirectional_objects' );

foreach ( $pod[ 'options' ] as $_option => $_value ) {
    $pod[ $_option ] = $_value;
}

foreach ( $pod[ 'fields' ] as $_field => $_data ) {
    $_data[ 'options' ] = (array) $_data[ 'options' ];

    foreach ( $_data[ 'options' ] as $_option => $_value ) {
        $pod[ 'fields' ][ $_field ][ $_option ] = $_value;
    }
}

$pod_name = $pod[ 'name' ];

$field_defaults = apply_filters( 'pods_field_defaults', apply_filters( "pods_field_defaults_{$pod_name}", $field_defaults, $pod ) );

$pick_table = pods_transient_get( 'pods_tables' );

if ( empty( $pick_table ) ) {
    $pick_table = array(
        '' => __( '-- Select Table --', 'pods' )
    );

    global $wpdb;

    $tables = $wpdb->get_results( "SHOW TABLES", ARRAY_N );

    if ( !empty( $tables ) ) {
        foreach ( $tables as $table ) {
            $pick_table[ $table[ 0 ] ] = $table[ 0 ];
        }
    }

    pods_transient_set( 'pods_tables', $pick_table );
}

$field_settings = array(
    'field_types_select' => $field_types_select,
    'field_defaults' => $field_defaults,
    'pick_object' => $pick_object,
    'pick_table' => $pick_table,
    'sister_id' => array( '' => __( 'No Related Fields Found', 'pods' ) )
);

$field_settings = apply_filters( 'pods_field_settings', apply_filters( "pods_field_settings_{$pod_name}", $field_settings, $pod ) );

$pod[ 'fields' ] = apply_filters( 'pods_fields_edit', apply_filters( "pods_fields_edit_{$pod_name}", $pod[ 'fields' ], $pod ) );

global $wpdb;
$max_length_name = 64;
$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
$max_length_name -= strlen( $wpdb->prefix . 'pods_' );

$tabs = PodsInit::$admin->admin_setup_edit_tabs( $pod );
$tab_options = PodsInit::$admin->admin_setup_edit_options( $pod );

$field_tabs = PodsInit::$admin->admin_setup_edit_field_tabs( $pod );
$field_tab_options = PodsInit::$admin->admin_setup_edit_field_options( $pod );

$no_additional = array();

foreach ( $field_tab_options[ 'additional-field' ] as $field_type => $field_type_fields ) {
    if ( empty( $field_type_fields ) )
        $no_additional[] = $field_type;
}

/**
 * Make use of the WP core meta box functionality
 *
 * Currently only context 'side' is available
 *
 * @since 2.7.0
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
 * @param array $pod The Pod object as an array
 */
do_action( 'pods_add_meta_boxes', '_pods_pod', $pod );

$pod_post = get_post( $pod['id'] );

/** This action is documented in wp-admin/edit-form-advanced.php */
do_action( 'add_meta_boxes', $pod_post->post_type, $pod_post );
?>
<div class="wrap pods-admin">
<div id="icon-pods" class="icon32"><br /></div>
<form action="" method="post" class="pods-submittable pods-nav-tabbed">
<div class="pods-submittable-fields">
    <input type="hidden" name="action" value="pods_admin" />
    <input type="hidden" name="method" value="save_pod" />
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'pods-save_pod' ) ); ?>" />
    <input type="hidden" name="id" value="<?php echo esc_attr( (int) $pod[ 'id' ] ); ?>" />
    <input type="hidden" name="old_name" value="<?php echo esc_attr( $pod[ 'name' ] ); ?>" />

    <h2>
        <?php esc_html_e( 'Edit Pod', 'pods' ); ?>:
        <?php
            if ( ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ), true ) && !empty( $pod[ 'object' ] ) ) || in_array( $pod[ 'type' ], array( 'media', 'user', 'comment' ), true ) ) {
        ?>
            <em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
        <?php
        }
            else {
        ?>
            <span class="pods-sluggable">
                <span class="pods-slug">
                    <em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
                    <input type="button" class="edit-slug-button button" value="<?php esc_attr_e( 'Edit', 'pods' ); ?>" />
                </span>
                <span class="pods-slug-edit">
                    <?php echo PodsForm::field( 'name', pods_v( 'name', $pod ), 'db', array(
                    'attributes' => array(
                        'maxlength' => $max_length_name,
                        'size' => 25
                    ),
                    'class' => 'pods-validate pods-validate-required'
                ) ); ?>
                    <input type="button" class="save-button button" value="<?php esc_attr_e( 'OK', 'pods' ); ?>" /> <a class="cancel" href="#cancel-edit"><?php esc_html_e( 'Cancel', 'pods' ); ?></a>
                </span>
            </span>
        <?php
            }
        ?>
    </h2>

    <?php
        if ( !empty( $tabs ) ) {
    ?>

        <h2 class="nav-tab-wrapper pods-nav-tabs">
            <?php
                $default = sanitize_title( pods_v( 'tab', 'get', 'manage-fields', null, true ) );

                if ( !isset( $tabs[ $default ] ) ) {
                    $tab_keys = array_keys( $tabs );

                    $default = current( $tab_keys );
                }

                foreach ( $tabs as $tab => $label ) {
                    if ( !in_array( $tab, array( 'manage-fields', 'labels', 'extra-fields' ), true ) && ( !isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) ) )
                        continue;

                    $class = '';

                    $tab = sanitize_title( $tab );

                    if ( $tab == $default )
                        $class = ' nav-tab-active';
            ?>
                <a href="#pods-<?php echo esc_attr( $tab ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?> pods-nav-tab-link">
                    <?php echo $label; ?>
                </a>
            <?php
                }
            ?>
        </h2>
    <?php
        }
    ?>
</div>

<?php
if ( isset( $_GET[ 'do' ] ) ) {
    $action = __( 'saved', 'pods' );

	$do = pods_v( 'do', 'get', 'save' );

    if ( 'create' == $do )
        $action = __( 'created', 'pods' );
    elseif ( 'duplicate' ==$do )
        $action = __( 'duplicated', 'pods' );

    $message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), $obj->item, $action );

    echo $obj->message( $message );
}
?>

<div id="poststuff" class="poststuff">
<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
<!-- /inner-sidebar -->
<div id="post-body" class="meta-box-holder columns-2">
<div id="post-body-content" class="pods-nav-tab-group">

<?php
    if ( isset( $tabs[ 'manage-fields' ] ) ) {
?>
<div id="pods-manage-fields" class="pods-nav-tab">
    <p class="pods-manage-row-add pods-float-right">
        <a href="#add-field" class="button-primary"><?php _e( 'Add Field', 'pods' ); ?></a>
    </p>

    <?php
        if ( !empty( $tabs ) )
            echo '<h2>' . __( 'Manage Fields', 'pods' ) . '</h2>';

		do_action( 'pods_admin_ui_setup_edit_fields', $pod, $obj );
    ?>

    <!-- pods table -->
    <table class="widefat fixed pages" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column field-cb check-column">
                    <span>&nbsp;</span>
                </th>
                <th scope="col" id="label" class="manage-column field-label">
                    <span><?php esc_html_e( 'Label', 'pods' ); pods_help( "<h6>" . __( 'Label', 'pods' ) . "</h6>" . __( 'The label is the descriptive name to identify the Pod field.', 'pods' ) ); ?></span>
                </th>
                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                    <span><?php esc_html_e( 'Name', 'pods' ); pods_help( "<h6>" . __( 'Name', 'pods' ) . "</h6>" . __( 'The name attribute is what is used to identify and access the Pod field programatically.', 'pods' ) ); ?></span>
                </th>
                <th scope="col" id="field-type" class="manage-column field-field-type">
                    <span><?php esc_html_e( 'Field Type', 'pods' ); pods_help( "<h6>" . __( 'Field Type', 'pods' ) . "</h6>" . __( 'Field types are used to determine what kind of data will be stored in the Pod. They can range from, dates, text, files, etc.', 'pods' ) ); ?></span>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column field-cb check-column">
                    <span>&nbsp;</span>
                </th>
                <th scope="col" class="manage-column field-label">
                    <span><?php esc_html_e( 'Label', 'pods' ); pods_help( "<h6>" . __( 'Label', 'pods' ) . "</h6>" . __( 'The label is the descriptive name to identify the Pod field.', 'pods' ) ); ?></span>
                </th>
                <th scope="col" class="manage-column field-machine-name">
                    <span><?php esc_html_e( 'Name', 'pods' ); pods_help( "<h6>" . __( 'Name', 'pods' ) . "</h6>" . __( 'The name attribute is what is used to identify and access the Pod field programatically.', 'pods' ) ); ?></span>
                </th>
                <th scope="col" class="manage-column field-field-type">
                    <span><?php esc_html_e( 'Field Type', 'pods' ); pods_help( "<h6>" . __( 'Field Type', 'pods' ) . "</h6>" . __( 'Field types are used to determine what kind of data will be stored in the Pod. They can range from, dates, text, files, etc.', 'pods' ) ); ?></span>
                </th>
            </tr>
        </tfoot>
        <tbody class="pods-manage-list">
            <?php
                // Empty Row for Flexible functionality
                $pods_i = '--1';

                $field = array(
                    'id' => '__1',
                    'name' => '',
                    'label' => '',
                    'type' => 'text'
                );

                include PODS_DIR . 'ui/admin/setup-edit-field-fluid.php';

                $pods_i = 1;

                foreach ( $pod[ 'fields' ] as $field ) {
                    include PODS_DIR . 'ui/admin/setup-edit-field.php';

                    $pods_i++;
                }
            ?>
            <tr class="no-items<?php echo esc_attr( 1 < $pods_i ? ' hidden' : '' ); ?>">
                <td class="colspanchange" colspan="4"><?php esc_html_e( 'No fields have been added yet', 'pods' ); ?></td>
            </tr>
        </tbody>
    </table>
    <!-- /pods table -->
    <p class="pods-manage-row-add">
        <a href="#add-field" class="button-primary"><?php _e( 'Add Field', 'pods' ); ?></a>
    </p>
</div>
<?php
    }

    $pods_tab_form = true;

    if ( isset( $tabs[ 'labels' ] ) ) {
?>
<div id="pods-labels" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
<?php

    $fields = $tab_options[ 'labels' ];
    $field_options = PodsForm::fields_setup( $fields );
    $field = $pod;

    include PODS_DIR . 'ui/admin/field-option.php';

?>
</div>
<?php
}

if ( isset( $tabs[ 'advanced' ] ) ) {
?>
<div id="pods-advanced" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
<?php
if ( 'post_type' == $pod_type && strlen( pods_v( 'object', $pod ) ) < 1 ) {
    $fields = $tab_options[ 'advanced' ];
    $field_options = PodsForm::fields_setup( $fields );
    $field = $pod;

    include PODS_DIR . 'ui/admin/field-option.php';
    ?>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Supports', 'pods' ); ?>
        </p>

        <div class="pods-pick-values pods-pick-checkbox">
            <ul>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_title', pods_v( 'supports_title', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Title', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_editor', pods_v( 'supports_editor', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Editor', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_author', pods_v( 'supports_author', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Author', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_thumbnail', pods_v( 'supports_thumbnail', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Featured Image', 'pods' ), 'dependency' => true ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_excerpt', pods_v( 'supports_excerpt', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Excerpt', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_trackbacks', pods_v( 'supports_trackbacks', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Trackbacks', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_custom_fields', pods_v( 'supports_custom_fields', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Custom Fields', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_comments', pods_v( 'supports_comments', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Comments', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_revisions', pods_v( 'supports_revisions', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Revisions', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_page_attributes', pods_v( 'supports_page_attributes', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Page Attributes', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_post_formats', pods_v( 'supports_post_formats', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Post Formats', 'pods' ) ) ); ?>
                    </div>
                </li>

                <?php if ( function_exists( 'genesis' ) ) { ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_seo', pods_v( 'supports_genesis_seo', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: SEO', 'pods' ) ) ); ?>
                        </div>
                    </li>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_layouts', pods_v( 'supports_genesis_layouts', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Layouts', 'pods' ) ) ); ?>
                        </div>
                    </li>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_simple_sidebars', pods_v( 'supports_genesis_simple_sidebars', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Simple Sidebars', 'pods' ) ) ); ?>
                        </div>
                    </li>
                <?php } ?>

				<?php if ( defined( 'YARPP_VERSION' ) ) { ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_yarpp_support', pods_v( 'supports_yarpp_support', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'YARPP Support', 'pods' ) ) ); ?>
                        </div>
                    </li>
				<?php } ?>

				<?php if ( class_exists( 'Jetpack' ) ) { ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_jetpack_publicize', pods_v( 'supports_jetpack_publicize', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Jetpack Publicize Support', 'pods' ) ) ); ?>
                        </div>
                    </li>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_jetpack_markdown', pods_v( 'supports_jetpack_markdown', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Jetpack Markdown Support', 'pods' ) ) ); ?>
                        </div>
                    </li>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'supports_custom', __( 'Advanced Supports', 'pods' ), __( 'Comma-separated list of custom "supports" values to pass to register_post_type.', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'supports_custom', pods_v( 'supports_custom', $pod, '' ), 'text' ); ?>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Built-in Taxonomies', 'pods' ); ?>
        </p>

        <div class="pods-pick-values pods-pick-checkbox">
            <ul>
                <?php
                foreach ( (array) $field_settings[ 'pick_object' ][ __( 'Taxonomies', 'pods' ) ] as $taxonomy => $label ) {
                    $taxonomy = pods_str_replace( 'taxonomy-', '', $taxonomy, 1 );
                    ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'built_in_taxonomies_' . $taxonomy, pods_v( 'built_in_taxonomies_' . $taxonomy, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label . ' <small>(' . $taxonomy . ')</small>' ) ); ?>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
    <?php
}
elseif ( 'taxonomy' == $pod_type && strlen( pods_v( 'object', $pod ) ) < 1 ) {
    $fields = $tab_options[ 'advanced' ];
    $field_options = PodsForm::fields_setup( $fields );
    $field = $pod;

    include PODS_DIR . 'ui/admin/field-option.php';
    ?>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Associated Post Types', 'pods' ); ?>
        </p>

        <div class="pods-pick-values pods-pick-checkbox">
            <ul>
                <?php
                    foreach ( (array) $field_settings[ 'pick_object' ][ __( 'Post Types', 'pods' ) ] as $post_type => $label ) {
                        $post_type = pods_str_replace( 'post_type-', '', $post_type, 1 );
                        $label = str_replace( array( '(', ')' ), array( '<small>(', ')</small>' ), $label );
                ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'built_in_post_types_' . $post_type, pods_v( 'built_in_post_types_' . $post_type, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label ) ); ?>
                        </div>
                    </li>
                <?php
                    }
                ?>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'built_in_post_types_attachment', pods_v( 'built_in_post_types_attachment', $pod, false ), 'boolean', array( 'boolean_yes_label' => 'Media <small>(attachment)</small>' ) ); ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <?php
}
elseif ( 'pod' == $pod_type ) {
?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'detail_url', __( 'Detail Page URL', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'detail_url', pods_v( 'detail_url', $pod ), 'text' ); ?>
    </div>

    <?php
        $index_fields = array( 'id' => 'ID' );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( !in_array( $field[ 'type' ], $tableless_field_types, true ) )
                $index_fields[ $field[ 'name' ] ] = $field[ 'label' ];
        }
    ?>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'pod_index', __( 'Title Field', 'pods' ), __( 'If you delete the "name" field, we need to specify the field to use as your primary title field. This field will serve as an index of your content. Most commonly this field represents the name of a person, place, thing, or a summary field.', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pod_index', pods_v( 'pod_index', $pod, 'name' ), 'pick', array( 'data' => $index_fields ) ); ?>
    </div>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'hierarchical', (int) pods_v( 'hierarchical', $pod, 0 ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>

    <?php
        $hierarchical_fields = array();

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( 'pick' == $field[ 'type' ] && 'pod' == pods_v( 'pick_object', $field ) && $pod[ 'name' ] == pods_v( 'pick_val', $field ) && 'single' == pods_v( 'pick_format_type', $field[ 'options' ] ) )
                $hierarchical_fields[ $field[ 'name' ] ] = $field[ 'label' ];
        }

        if ( empty( $hierarchical_fields ) )
            $hierarchical_fields = array( '' => __( 'No Hierarchical Fields found', 'pods' ) );
    ?>

    <div class="pods-field-option pods-depends-on pods-depends-on-hierarchical">
        <?php echo PodsForm::label( 'pod_parent', __( 'Hierarchical Field', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pod_parent', pods_v( 'pod_parent', $pod, 'name' ), 'pick', array( 'data' => $hierarchical_fields ) ); ?>
    </div>

    <?php
    if ( class_exists( 'Pods_Helpers' ) ) {
    ?>

    <div class="pods-field-option">
        <?php
            $pre_save_helpers = array( '' => '-- Select --' );

            $helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'pre_save' ) ) );

            foreach ( $helpers as $helper ) {
                $pre_save_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
            }

            echo PodsForm::label( 'pre_save_helpers', __( 'Pre-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) );
            echo PodsForm::field( 'pre_save_helpers', pods_v( 'pre_save_helpers', $pod ), 'pick', array( 'data' => $pre_save_helpers ) );
        ?>
    </div>
    <div class="pods-field-option">
        <?php
            $post_save_helpers = array( '' => '-- Select --' );

            $helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'post_save' ) ) );

            foreach ( $helpers as $helper ) {
                $post_save_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
            }

            echo PodsForm::label( 'post_save_helpers', __( 'Post-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) );
            echo PodsForm::field( 'post_save_helpers', pods_v( 'post_save_helpers', $pod ), 'pick', array( 'data' => $post_save_helpers ) );
        ?>
    </div>
    <div class="pods-field-option">
        <?php
            $pre_delete_helpers = array( '' => '-- Select --' );

            $helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'pre_delete' ) ) );

            foreach ( $helpers as $helper ) {
                $pre_delete_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
            }

            echo PodsForm::label( 'pre_delete_helpers', __( 'Pre-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) );
            echo PodsForm::field( 'pre_delete_helpers', pods_v( 'pre_delete_helpers', $pod ), 'pick', array( 'data' => $pre_delete_helpers ) );
        ?>
    </div>
    <div class="pods-field-option">
        <?php
            $post_delete_helpers = array( '' => '-- Select --' );

            $helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'post_delete' ) ) );

            foreach ( $helpers as $helper ) {
                $post_delete_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
            }

            echo PodsForm::label( 'post_delete_helpers', __( 'Post-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) );
            echo PodsForm::field( 'post_delete_helpers', pods_v( 'post_delete_helpers', $pod ), 'pick', array( 'data' => $post_delete_helpers ) );
        ?>
    </div>
    <?php
    }
}
?>
</div>
<?php
}

foreach ( $tabs as $tab => $tab_label ) {
    $tab = sanitize_title( $tab );

    if ( in_array( $tab, array( 'manage-fields', 'labels', 'advanced', 'extra-fields' ), true ) || !isset( $tab_options[ $tab ] ) || empty( $tab_options[ $tab ] ) )
        continue;
?>
    <div id="pods-<?php echo esc_attr( $tab ); ?>" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
        <?php
            $fields = $tab_options[ $tab ];
            $field_options = PodsForm::fields_setup( $fields );
            $field = $pod;

            include PODS_DIR . 'ui/admin/field-option.php';
        ?>
    </div>
<?php
}

if ( isset( $tabs[ 'extra-fields' ] ) ) {
?>
<div id="pods-extra-fields" class="pods-nav-tab">
    <p><?php _e( 'Taxonomies do not support extra fields natively, but Pods can add this feature for you easily. Table based storage will operate in a way where each field you create for your content type becomes a field in a table.', 'pods' ); ?></p>

    <p><?php echo sprintf( __( 'Enabling extra fields for this taxonomy will add a custom table into your database as <em>%s</em>.', 'pods' ), $wpdb->prefix . 'pods_' . pods_v( 'name', $pod ) ); ?></p>

    <p><a href="https://pods.io/docs/comparisons/compare-storage-types/" target="_blank" rel="noopener noreferrer"><?php _e( 'Find out more', 'pods' ); ?> &raquo;</a></p>

    <p class="submit">
        <a href="<?php echo esc_url( pods_query_arg( array( 'enable_extra_fields' => 1 ) ) ); ?>" class="button-primary"><?php _e( 'Enable Extra Fields', 'pods' ); ?></a>
    </p>
</div>
<?php
}
?>
</div>
<!-- /#post-body-content -->

<div id="postbox-container-1" class="postbox-container pods_floatmenu">
    <div id="side-info-field" class="inner-sidebar">
        <div id="side-sortables">
            <div id="submitdiv" class="postbox pods-no-toggle">
                <h3><span><?php esc_html_e( 'Manage', 'pods' ); ?> <small>(<a href="<?php echo esc_url( pods_query_arg( array( 'action' . $obj->num => 'manage', 'id' . $obj->num => '' ) ) ); ?>">&laquo; <?php _e( 'Back to Manage', 'pods' ); ?></a>)
                </small></span></h3>
                <div class="inside">
                    <div class="submitbox" id="submitpost">
                        <div id="major-publishing-actions">
                            <div id="delete-action">
                                <a href="<?php echo esc_url( pods_query_arg( array( 'action' . $obj->num => 'delete', '_wpnonce' => wp_create_nonce( 'pods-ui-action-delete' ) ) ) ); ?>" class="submitdelete deletion pods-confirm" data-confirm="<?php _e( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' ); ?>"> <?php _e( 'Delete', 'pods' ); ?> Pod </a>
                            </div>
                            <div id="publishing-action">
                                <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                                <button class="button-primary" type="submit"><?php esc_html_e( 'Save Pod', 'pods' ); ?></button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /#submitdiv -->
            <div class="pods-submittable-fields">
            <?php do_meta_boxes( '_pods_pod', 'side', $pod_post ); ?>
            </div>
        </div>
    </div>
</div>
</div>
<!-- /#post-body -->
</div>
<!-- /poststuff -->
</form>
</div>
<script type="text/javascript">
    <?php
    $pods_field_types = array();

    foreach ( $field_types as $field_type => $field_type_data ) {
        $pods_field_types[] = "'" . esc_js( $field_type ) . "' : '" . esc_js( $field_type_data[ 'label' ] ) . "'";
    }

    $pods_pick_objects = array();

    $pick_object_singular = array(
        __( 'Pods', 'pods' ) => __( 'Pod', 'pods' ),
        __( 'Post Types', 'pods' ) => __( 'Post Type', 'pods' ),
        __( 'Taxonomies', 'pods' ) => __( 'Taxonomy', 'pods' )
    );

    foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
        if ( is_array( $object_label ) ) {
            if ( isset( $pick_object_singular[ $object ] ) )
                $object = ' <small>(' . esc_js( $pick_object_singular[ $object ] ) . ')</small>';
            else
                $object = '';

            foreach ( $object_label as $sub_object => $sub_object_label ) {
                $pods_pick_objects[] = "'" . esc_js( $sub_object ) . "' : '" . esc_js( $sub_object_label ) . $object . "'";
            }
        }
        elseif ( '-- Select --' != $object_label )
            $pods_pick_objects[] = "'" . esc_js( $object ) . "' : '" . esc_js( $object_label ) . "'";
    }
    ?>
    var pods_field_types = {
    <?php echo implode( ",\n        ", $pods_field_types ); ?>
    };
    var pods_pick_objects = {
    <?php echo implode( ",\n        ", $pods_pick_objects ); ?>
    };

    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'sluggable' );
        $( document ).Pods( 'sortable' );
        $( document ).Pods( 'collapsible', $( 'tbody.pods-manage-list tr.flexible-row div.pods-manage-field' ) );
        $( document ).Pods( 'toggled' );
        $( document ).Pods( 'tabbed' );
        $( document ).Pods( 'nav_tabbed' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'flexible', $( 'tbody.pods-manage-list tr.flexible-row' ) );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'exit_confirm' );
    } );

    var pods_admin_submit_callback = function ( id ) {
        id = parseInt( id );
        var thank_you = '<?php echo pods_slash( pods_query_arg( array( 'do' => 'save' ) ) ); ?>';

        document.location = thank_you.replace( 'X_ID_X', id );
    };

    var sisterFieldsProcessing = {};

    /*global PodsDFV */
    var pods_sister_field = function ( $el ) {
        var postData, relatedPodName, selectedValue, $container;
        var fieldModel, collection, selectField;

        var id = $el.closest( 'tr.pods-manage-row' ).data( 'row' );
        var fieldKey = id + '_' + $el.prop( 'id' );

        if ( 'undefined' != typeof sisterFieldsProcessing[ fieldKey ] && true == sisterFieldsProcessing[ fieldKey ] ) {
            return;
        }

        relatedPodName = jQuery( '#pods-form-ui-field-data-' + id + '-pick-object' ).val() + '';
        selectedValue = jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val() + '';
        $container = $el.find( '.pods-sister-field' );

        if ( 0 != relatedPodName.indexOf( 'pod-' )
            && 0 != relatedPodName.indexOf( 'post_type-' )
            && 0 != relatedPodName.indexOf( 'taxonomy-' )
            && 0 != relatedPodName.indexOf( 'user' )
            && 0 != relatedPodName.indexOf( 'media' )
            && 0 != relatedPodName.indexOf( 'comment' ) ) {

            sisterFieldsProcessing[ fieldKey ] = false;
            return;
        }

        sisterFieldsProcessing[ fieldKey ] = true;

        fieldModel = new PodsDFV.models.PodsDFVFieldModel( {
            htmlAttr: {
                id        : "pods-form-ui-field-data-" + id + "-sister-id",
                "class"   : "pods-form-ui-field pods-form-ui-field-type-pick pods-form-ui-field-name-field-data-sister-id",
                name      : "field_data[" + id + "][sister_id]",
                name_clean: "field-data-sister-id"
            }
        } );

        collection = new PodsDFV.models.RelationshipCollection( {
            id  : "",
            name: <?php echo json_encode( __( 'Loading available fields..', 'pods' ) ); ?>
        } );

        selectField = new PodsDFV.fields.Pick( {
            el        : $container,
            model     : fieldModel,
            collection: collection
        } );

        selectField.render();

        postData = {
            action     : 'pods_admin',
            method     : 'load_sister_fields',
            _wpnonce   : '<?php echo esc_js( wp_create_nonce( 'pods-load_sister_fields' ) ); ?>',
            pod        : '<?php echo esc_js( pods_v( 'name', $pod ) ); ?>',
            related_pod: relatedPodName
        };

        jQuery.ajax( {
            type    : 'POST',
            dataType: 'html',
            url     : ajaxurl + '?pods_ajax=1',
            cache   : false,
            data    : postData,
            success : function ( d ) {
                var json, newItems, fieldID, fieldName;

                newItems = [];

                if ( -1 == d.indexOf( '<e>' ) && -1 == d.indexOf('</e>') && -1 != d && '[]' != d ) {

                    json = d.match( /{.*}$/ );

                    if ( null !== json && 0 < json.length ) {
                        json = jQuery.parseJSON( json[ 0 ] );
                    } else {
                        json = {};
                    }

                    if ( 'object' != typeof json || ! jQuery.isEmptyObject( json ) ) {

                        if ( 'object' != typeof json ) {
                            if ( window.console ) console.log( d );
                            if ( window.console ) console.log( json );

                            newItems.push( {
                                id: "",
                                name: <?php echo json_encode( __( 'There was a server error with your AJAX request.', 'pods' ) ); ?>
                            } );

                        } else {
                            newItems.push( {
                                id  : "",
                                name: <?php echo json_encode( __( '-- Select Related Field --', 'pods' ) ); ?>
                            } );

                            for ( fieldID in json ) {
                                if ( json.hasOwnProperty( fieldID ) ) {
                                    fieldName = json[ fieldID ];

                                    newItems.push( {
                                        id      : fieldID,
                                        name    : fieldName,
                                        selected: (selectedValue == fieldID)
                                    } );
                                }
                            }
                        }
                        collection.reset( newItems );
                        jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val( selectedValue );

                    } else {
                        // None found
                        collection.reset( {
                            id  : "",
                            name: <?php echo json_encode( __( 'No Related Fields Found', 'pods' ) ); ?>
                        } );
                    }

                } else {
                    // None found
                    collection.reset( {
                        id  : "",
                        name: <?php echo json_encode( __( 'No Related Fields Found', 'pods' ) ); ?>
                    } );
                }

                sisterFieldsProcessing[ fieldKey ] = false;
            },
            error : function () {
                // None found
                collection.reset( {
                    id  : "",
                    name: <?php echo json_encode( __( 'No Related Fields Found', 'pods' ) ); ?>
                } );

                sisterFieldsProcessing[ fieldKey ] = false;
            }
        } );
    }
</script>
