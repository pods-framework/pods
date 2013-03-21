<?php
global $pods_i;

$api = pods_api();

$pod = $api->load_pod( array( 'id' => $obj->id ) );

if ( 'taxonomy' == $pod[ 'type' ] && 'none' == $pod[ 'storage' ] && 1 == pods_var( 'enable_extra_fields', 'get' ) ) {
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
        elseif ( is_array( $field_type_vars[ 'pod_types' ] ) && !in_array( pods_var( 'type', $pod ), $field_type_vars[ 'pod_types' ] ) )
            continue;
        elseif ( !is_array( $field_type_vars[ 'pod_types' ] ) && pods_var( 'type', $pod ) != $field_type_vars[ 'pod_types' ] )
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

$input_helpers = array( '' => '-- Select --' );

$helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'input' ) ) );

foreach ( $helpers as $helper ) {
    $input_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
}

$advanced_fields = array(
    __( 'Visual', 'pods' ) => array(
        'class' => array(
            'label' => __( 'Additional CSS Classes', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'input_helper' => array(
            'label' => __( 'Input Helper', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => '',
            'data' => $input_helpers
        )
    ),
    __( 'Values', 'pods' ) => array(
        'default_value' => array(
            'label' => __( 'Default Value', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'default_value_parameter' => array(
            'label' => __( 'Set Default Value via Parameter', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        )
    ),
    __( 'Visibility', 'pods' ) => array(
        'restrict_access' => array(
            'label' => __( 'Restrict Access', 'pods' ),
            'group' => array(
                'admin_only' => array(
                    'label' => __( 'Show to Admins Only?', 'pods' ),
                    'default' => 0,
                    'type' => 'boolean',
                    'dependency' => true
                ),
                'restrict_role' => array(
                    'label' => __( 'Restrict access by Role?', 'pods' ),
                    'default' => 0,
                    'type' => 'boolean',
                    'dependency' => true,
                    'depends-on' => array( 'admin_only' => false )
                ),
                'restrict_capability' => array(
                    'label' => __( 'Restrict access by Capability?', 'pods' ),
                    'default' => 0,
                    'type' => 'boolean',
                    'dependency' => true,
                    'depends-on' => array( 'admin_only' => false )
                )
            )
        ),
        'roles_allowed' => array(
            'label' => __( 'Role(s) Allowed', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'pick_object' => 'role',
            'pick_format_type' => 'multi',
            'default' => '',
            'depends-on' => array(
                'admin_only' => false,
                'restrict_role' => true
            )
        ),
        'capability_allowed' => array(
            'label' => __( 'Capability Allowed', 'pods' ),
            'help' => __( 'Comma-separated list of cababilities, for example add_podname_item, please see the Roles and Capabilities component for the complete list and a way to add your own.', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array(
                'admin_only' => false,
                'restrict_capability' => true
            )
        )/*,
        'search' => array(
            'label' => __( 'Include in searches', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'default' => 1,
            'type' => 'boolean',
        )*/
    )/*,
    __( 'Validation', 'pods' ) => array(
        'regex_validation' => array(
            'label' => __( 'RegEx Validation', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'message_regex' => array(
            'label' => __( 'Message if field does not pass RegEx', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'message_required' => array(
            'label' => __( 'Message if field is blank', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'required' => true )
        ),
        'message_unique' => array(
            'label' => __( 'Message if field is not unique', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'unique' => true )
        )
    )*/
);

$field_defaults = array(
    'name' => 'new_field',
    'label' => 'New Field',
    'description' => '',
    'type' => 'text',
    'pick_object' => '',
    'sister_id' => '',
    'required' => 0,
    'unique' => 0,
    'css_class_name' => '',
    'input_helper' => '',
    'default_value' => '',
    'default_value_parameter' => '',
    //'search' => 1,
    'admin_only' => 0,
    'restrict_roles' => 0,
    'restrict_capability' => 0,
    'roles_allowed' => 'administrator',
    'capability_allowed' => ''/*,
    'regex_validation' => '',
    'message_regex' => '',
    'message_required' => '',
    'message_unique' => ''*/
);

$pick_object = PodsForm::field_method( 'pick', 'related_objects', true );

$tableless_field_types = PodsForm::tableless_field_types();
$simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

foreach ( $pod[ 'options' ] as $_option => $_value ) {
    $pod[ $_option ] = $_value;
}

foreach ( $pod[ 'fields' ] as $_field => $_data ) {
    $_data[ 'options' ] = (array) $_data[ 'options' ];

    foreach ( $_data[ 'options' ] as $_option => $_value ) {
        $pod[ 'fields' ][ $_field ][ $_option ] = $_value;
    }
}

$field_defaults = apply_filters( 'pods_field_defaults', apply_filters( 'pods_field_defaults_' . $pod[ 'name' ], $field_defaults, $pod ) );

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
    'field_types' => $field_types,
    'field_types_select' => $field_types_select,
    'field_defaults' => $field_defaults,
    'advanced_fields' => $advanced_fields,
    'pick_object' => $pick_object,
    'pick_table' => $pick_table,
    'sister_id' => array( '' => __( 'No Related Fields Found', 'pods' ) )
);

$field_settings = apply_filters( 'pods_field_settings', apply_filters( 'pods_field_settings_' . $pod[ 'name' ], $field_settings, $pod ) );

$pod[ 'fields' ] = apply_filters( 'pods_fields_edit', apply_filters( 'pods_fields_edit_' . $pod[ 'name' ], $pod[ 'fields' ], $pod ) );

global $wpdb;
$max_length_name = 64;
$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
$max_length_name -= strlen( $wpdb->prefix . 'pods_' );

$manage_fields = true;
$advanced = false;
$labels = false;
$pods_ui = false;

if ( 'post_type' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
    $advanced = true;
    $labels = true;
}
elseif ( 'taxonomy' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
    $advanced = true;
    $labels = true;
}
elseif ( 'pod' == pods_var( 'type', $pod ) ) {
    $advanced = true;
    $labels = true;
    $pods_ui = true;
}
elseif ( 'settings' == pods_var( 'type', $pod ) ) {
    $advanced = true;
    $labels = true;
}

if ( 'none' == pods_var( 'storage', $pod, 'none', null, true ) && 'settings' != pods_var( 'type', $pod ) )
    $manage_fields = false;
?>
<div class="wrap pods-admin">
<div id="icon-pods" class="icon32"><br /></div>
<form action="" method="post" class="pods-submittable pods-nav-tabbed">
<div class="pods-submittable-fields">
    <input type="hidden" name="action" value="pods_admin" />
    <input type="hidden" name="method" value="save_pod" />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'pods-save_pod' ); ?>" />
    <input type="hidden" name="id" value="<?php echo (int) $pod[ 'id' ]; ?>" />
    <input type="hidden" name="old_name" value="<?php echo esc_attr( $pod[ 'name' ] ); ?>" />

    <h2>
        Edit Pod:
        <span class="pods-sluggable">
            <span class="pods-slug">
                <em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
                <input type="button" class="edit-slug-button button" value="Edit" />
            </span>
            <span class="pods-slug-edit">
                <?php echo PodsForm::field( 'name', pods_var_raw( 'name', $pod ), 'db', array(
                'attributes' => array(
                    'maxlength' => $max_length_name,
                    'size' => 25
                ),
                'class' => 'pods-validate pods-validate-required'
            ) ); ?>
                <input type="button" class="save-button button" value="OK" /> <a class="cancel" href="#cancel-edit">Cancel</a>
            </span>
        </span>
    </h2>

    <?php
        if ( $labels || $pods_ui || $advanced ) {
            $default = 'fields';

            $tabs = array();

            if ( $manage_fields )
                $tabs[ 'pods-manage-fields' ] = __( 'Manage Fields', 'pods' );

            if ( $labels )
                $tabs[ 'pods-labels' ] = __( 'Labels', 'pods' );

            if ( $pods_ui )
                $tabs[ 'pods-ui' ] = __( 'Admin UI', 'pods' );

            if ( $advanced )
                $tabs[ 'pods-advanced' ] = __( 'Advanced Options', 'pods' );

            if ( 'taxonomy' == pods_var( 'type', $pod ) && !$manage_fields )
                $tabs[ 'pods-extra-fields' ] = __( 'Extra Fields', 'pods' );
    ?>

        <h2 class="nav-tab-wrapper pods-nav-tabs">
            <?php
                foreach ( $tabs as $tab => $label ) {
                    $class = '';

                    if ( !$manage_fields && 'fields' == $default )
                        $default = $tab;

                    if ( $tab == $default )
                        $class = ' nav-tab-active';

                    $url = pods_var_update( array( 'tab' => $tab ), array( 'page', 'id', 'action' ) );
            ?>
                <a href="#<?php echo $tab; ?>" class="nav-tab<?php echo $class; ?> pods-nav-tab-link">
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

    if ( 'create' == pods_var( 'do', 'get', 'save' ) )
        $action = __( 'created', 'pods' );
    elseif ( 'duplicate' == pods_var( 'do', 'get', 'save' ) )
        $action = __( 'duplicated', 'pods' );

    $message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

    echo $obj->message( $message );
}
?>

<div id="poststuff">
<img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
<!-- /inner-sidebar -->
<div id="post-body" class="meta-box-holder columns-2">
<div id="post-body-content" class="pods-nav-tab-group">
<?php
if ( 'none' != pods_var( 'storage', $pod, 'none', null, true ) || 'settings' == $pod[ 'type' ] ) {
    ?>
<div id="pods-manage-fields" class="pods-nav-tab">
    <p class="pods-manage-row-add pods-float-right">
        <a href="#add-field" class="button-primary"><?php _e( 'Add Field', 'pods' ); ?></a>
    </p>

    <?php
        if ( !( $labels || $pods_ui || $advanced ) )
            echo '<h2>' . __( 'Manage Fields', 'pods' ) . '</h2>';
    ?>

    <!-- pods table -->
    <table class="widefat fixed pages" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column field-cb check-column">
                    <span>&nbsp;</span>
                </th>
                <th scope="col" id="label" class="manage-column field-label">
                    <span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field.", 'pods' ) ); ?></span>
                </th>
                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                    <span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.", 'pods' ) ); ?></span>
                </th>
                <th scope="col" id="field-type" class="manage-column field-field-type">
                    <span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.", 'pods' ) ); ?></span>
                </th>
                <!--
                <th scope="col" id="comment" class="manage-column field-comment">
                    <span>Comment</span>
                </th>-->
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column field-cb check-column">
                    <span>&nbsp;</span>
                </th>
                <th scope="col" class="manage-column field-label">
                    <span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field.", 'pods' ) ); ?></span>
                </th>
                <th scope="col" class="manage-column field-machine-name">
                    <span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.", 'pods' ) ); ?></span>
                </th>
                <th scope="col" class="manage-column field-field-type">
                    <span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.", 'pods' ) ); ?></span>
                </th>
                <!--
                <th scope="col" id="comment" class="manage-column field-comment">
                    <span>Comment</span>
                </th>-->
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
            <tr class="no-items<?php echo ( 1 < $pods_i ? ' hidden' : '' ); ?>">
                <td class="colspanchange" colspan="4">No fields have been added yet</td>
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

if ( $labels ) {
?>
<div id="pods-labels" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
<?php
if ( strlen( pods_var( 'object', $pod ) ) < 1 && 'settings' != pods_var( 'type', $pod ) ) {
    ?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label', __( 'Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label', pods_var_raw( 'label', $pod ), 'text', array( 'text_max_length' => 30 ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_singular', __( 'Singular Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_singular', pods_var_raw( 'label_singular', $pod, pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $pod ) ) ) ) ), 'text', array( 'text_max_length' => 30 ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_add_new', __( 'Add New', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_add_new', pods_var_raw( 'label_add_new', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_add_new_item', __( 'Add New <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_add_new_item', pods_var_raw( 'label_add_new_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_new_item', __( 'New <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_new_item', pods_var_raw( 'label_new_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_edit', __( 'Edit', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_edit', pods_var_raw( 'label_edit', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_edit_item', __( 'Edit <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_edit_item', pods_var_raw( 'label_edit_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_update_item', __( 'Update <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_update_item', pods_var_raw( 'label_update_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_view', __( 'View', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_view', pods_var_raw( 'label_view', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_view_item', __( 'View <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_view_item', pods_var_raw( 'label_view_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_all_items', __( 'All <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_all_items', pods_var_raw( 'label_all_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_search_items', __( 'Search <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_search_items', pods_var_raw( 'label_search_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_not_found', __( 'Not Found', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_not_found', pods_var_raw( 'label_not_found', $pod ), 'text' ); ?>
    </div>

    <?php
    if ( in_array( pods_var( 'type', $pod ), array( 'post_type', 'pod' ) ) ) {
        ?>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'label_not_found_in_trash', __( 'Not Found in Trash', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'label_not_found_in_trash', pods_var_raw( 'label_not_found_in_trash', $pod ), 'text' ); ?>
        </div>
        <?php
    }
    ?>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_popular_items', __( 'Popular <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_popular_items', pods_var_raw( 'label_popular_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_separate_items_with_commas', __( 'Separate <span class="pods-slugged-lower" data-sluggable="label">items</span> with commas', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_separate_items_with_commas', pods_var_raw( 'label_separate_items_with_commas', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_add_or_remove_items', __( 'Add or remove <span class="pods-slugged-lower" data-sluggable="label">items</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_add_or_remove_items', pods_var_raw( 'label_add_or_remove_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label_choose_from_the_most_used', __( 'Choose from the most used', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label_choose_from_the_most_used', pods_var_raw( 'label_choose_from_the_most_used', $pod ), 'text' ); ?>
    </div>
    <?php
}
elseif ( 'settings' == pods_var( 'type', $pod ) ) {
    ?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label', __( 'Page Title', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label', pods_var_raw( 'label', $pod ), 'text', array( 'text_max_length' => 30 ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'menu_name', __( 'Menu Name', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'menu_name', pods_var_raw( 'menu_name', $pod, pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $pod ) ) ) ) ), 'text', array( 'text_max_length' => 30 ) ); ?>
    </div>
    <?php
}
?>
</div>
<?php
}

if ( $advanced ) {
?>
<div id="pods-advanced" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
<?php
$advanced_options = array(
    'cpt_options' => array(
        'description' => array(
            'label' => __( 'Post Type Description', 'pods' ),
            'help' => __( 'A short descriptive summary of what the post type is.', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'public' => array(
            'label' => __( 'Public', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'boolean_yes_label' => ''
        ),
        'publicly_queryable' => array(
            'label' => __( 'Publicly Queryable', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => pods_var_raw( 'public', $pod, true ),
            'boolean_yes_label' => ''
        ),
        'exclude_from_search' => array(
            'label' => __( 'Exclude from Search', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => !pods_var_raw( 'public', $pod, true ),
            'boolean_yes_label' => ''
        ),
        'show_ui' => array(
            'label' => __( 'Show Admin UI', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => pods_var_raw( 'public', $pod, true ),
            'boolean_yes_label' => ''
        ),
        'show_in_menu' => array(
            'label' => __( 'Show Admin Menu in Dashboard', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => pods_var_raw( 'public', $pod, true ),
            'dependency' => true,
            'boolean_yes_label' => ''
        ),
        'menu_name' => array(
            'label' => __( 'Menu Name', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'menu_position' => array(
            'label' => __( 'Menu Position', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'number',
            'default' => 20,
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'menu_icon' => array(
            'label' => __( 'Menu Icon URL', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'menu_string' => array(
            'label' => __( '<strong>Label: </strong> Parent Page', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'show_in_nav_menus' => array(
            'label' => __( 'Show in Navigation Menus', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'boolean_yes_label' => ''
        ),
        'show_in_admin_bar' => array(
            'label' => __( 'Show in Admin Bar "New" Menu', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'boolean_yes_label' => ''
        ),
        'capability_type' => array(
            'label' => __( 'Capability Type', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => 'post',
            'data' => array(
                'post' => __( 'Post', 'pods' ),
                'page' => __( 'Page', 'pods' ),
                'custom' => __( 'Custom', 'pods' )
            ),
            'dependency' => true
        ),
        'capability_type_custom' => array(
            'label' => __( 'Custom Capability Type', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => pods_var_raw( 'name', $pod ),
            'depends-on' => array( 'capability_type' => 'custom' )
        ),
        'has_archive' => array(
            'label' => __( 'Has Archive', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => false,
            'boolean_yes_label' => ''
        ),
        'hierarchical' => array(
            'label' => __( 'Hierarchical', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => false,
            'dependency' => true,
            'boolean_yes_label' => ''
        ),
        'label_parent_item_colon' => array(
            'label' => __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'hierarchical' => true )
        ),
        'label_parent' => array(
            'label' => __( '<strong>Label: </strong> Parent', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'hierarchical' => true )
        ),
        'rewrite' => array(
            'label' => __( 'Rewrite', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'dependency' => true,
            'boolean_yes_label' => ''
        ),
        'rewrite_custom_slug' => array(
            'label' => __( 'Custom Rewrite Slug', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'rewrite' => true )
        ),
        'rewrite_with_front' => array(
            'label' => __( 'Rewrite with Front', 'pods' ),
            'help' => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'depends-on' => array( 'rewrite' => true ),
            'boolean_yes_label' => ''
        ),
        'rewrite_feeds' => array(
            'label' => __( 'Rewrite Feeds', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => false,
            'depends-on' => array( 'rewrite' => true ),
            'boolean_yes_label' => ''
        ),
        'rewrite_pages' => array(
            'label' => __( 'Rewrite Pages', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'depends-on' => array( 'rewrite' => true ),
            'boolean_yes_label' => ''
        ),
        'query_var' => array(
            'label' => __( 'Query Var', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'boolean_yes_label' => ''
        ),
        'can_export' => array(
            'label' => __( 'Exportable', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => true,
            'boolean_yes_label' => ''
        )
    ),
    // @todo Finish converting hardcoded fields into field array format (see how simple/powerful it is?)
    'ct_options' => array(),
    'pod_options' => array(),
    'setting_options' => array(
        'ui_style' => array(
            'label' => __( 'Admin UI Style', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => 'settings',
            'data' => array(
                'settings' => 'Normal Settings Form',
                'post_type' => 'Post Type UI',
                'custom' => 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)'
            ),
            'dependency' => true
        ),
        'menu_location' => array(
            'label' => __( 'Menu Location', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => 'settings',
            'data' => array(
                'settings' => 'Add to Settings menu',
                'appearances' => 'Add to Appearances menu',
                'top' => 'Make a new menu item below Settings',
                'submenu' => 'Add a submenu item to another menu'
            ),
            'dependency' => true
        ),
        'menu_location_custom' => array(
            'label' => __( 'Custom Menu Location', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'depends-on' => array( 'menu_location' => 'submenu' )
        ),
        'menu_position' => array(
            'label' => __( 'Menu Position', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'number',
            'default' => 0,
            'depends-on' => array( 'menu_location' => 'top' )
        ),
        'menu_icon' => array(
            'label' => __( 'Menu Icon URL', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'menu_location' => 'top' )
        )
    ),
    'ui_options' => array(
        'show_in_menu' => array(
            'label' => __( 'Show Admin Menu in Dashboard', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'boolean',
            'default' => false,
            'boolean_yes_label' => '',
            'dependency' => true
        ),
        'menu_location_custom' => array(
            'label' => __( 'Parent Menu ID (optional)', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'menu_position' => array(
            'label' => __( 'Menu Position', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'number',
            'default' => 0,
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'menu_icon' => array(
            'label' => __( 'Menu Icon URL', 'pods' ),
            'help' => __( 'This is the icon shown to the left of the menu text for this content type.', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'ui_icon' => array(
            'label' => __( 'Header Icon', 'pods' ),
            'help' => __( 'This is the icon shown to the left of the heading text at the top of the manage pages for this content type.', 'pods' ),
            'type' => 'file',
            'default' => '',
            'file_edit_title' => 0,
            'depends-on' => array( 'show_in_menu' => true )
        ),
        'ui_actions_enabled' => array(
            'label' => __( 'Actions Available', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => ( 1 == pods_var( 'ui_export', $pod ) ? array( 'add', 'edit', 'duplicate', 'delete', 'export' ) : array( 'add', 'edit', 'duplicate', 'delete' ) ),
            'data' => array(
                'add' => __( 'Add New', 'pods' ),
                'edit' => __( 'Edit', 'pods' ),
                'duplicate' => __( 'Duplicate', 'pods' ),
                'delete' => __( 'Delete', 'pods' ),
                'reorder' => __( 'Reorder', 'pods' ),
                'export' => __( 'Export', 'pods' )
            ),
            'pick_format_type' => 'multi',
            'dependency' => true
        ),
        'ui_reorder_field' => array(
            'label' => __( 'Reorder Field', 'pods' ),
            'help' => __( 'This is the field that will be reordered on, it should be numeric.', 'pods' ),
            'type' => 'text',
            'default' => 'menu_order',
            'depends-on' => array( 'ui_actions_enabled' => 'reorder' )
        ),
        'ui_fields_manage' => array(
            'label' => __( 'Admin Table Columns', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => array(),
            'data' => array(),
            'pick_format_type' => 'multi'
        ),
        'ui_filters' => array(
            'label' => __( 'Search Filters', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'pick',
            'default' => array(),
            'data' => array(),
            'pick_format_type' => 'multi'
        )
    )
);

if ( $pods_ui ) {
    if ( isset( $pod[ 'fields' ][ pods_var_raw( 'pod_index', $pod, 'name' ) ] ) )
        $advanced_options[ 'ui_options' ][ 'ui_fields_manage' ][ 'default' ][] = pods_var_raw( 'pod_index', $pod, 'name' );

    if ( isset( $pod[ 'fields' ][ 'modified' ] ) )
        $advanced_options[ 'ui_options' ][ 'ui_fields_manage' ][ 'default' ][] = 'modified';

    foreach ( $pod[ 'fields' ] as $field ) {
        $type = '';

        if ( isset( $field_types[ $field[ 'type' ] ] ) )
            $type = ' <small>(' . $field_types[ $field[ 'type' ] ][ 'label' ] . ')</small>';

        $advanced_options[ 'ui_options' ][ 'ui_fields_manage' ][ 'data' ][ $field[ 'name' ] ] = $field[ 'label' ] . $type;
        $advanced_options[ 'ui_options' ][ 'ui_filters' ][ 'data' ][ $field[ 'name' ] ] = $field[ 'label' ] . $type;
    }
}

if ( 'post_type' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
    $fields = $advanced_options[ 'cpt_options' ];
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
                        <?php echo PodsForm::field( 'supports_title', pods_var_raw( 'supports_title', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Title', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_editor', pods_var_raw( 'supports_editor', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Editor', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_author', pods_var_raw( 'supports_author', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Author', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_thumbnail', pods_var_raw( 'supports_thumbnail', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Featured Image', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_excerpt', pods_var_raw( 'supports_excerpt', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Excerpt', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_trackbacks', pods_var_raw( 'supports_trackbacks', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Trackbacks', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_custom_fields', pods_var_raw( 'supports_custom_fields', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Custom Fields', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_comments', pods_var_raw( 'supports_comments', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Comments', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_revisions', pods_var_raw( 'supports_revisions', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Revisions', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_page_attributes', pods_var_raw( 'supports_page_attributes', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Page Attributes', 'pods' ) ) ); ?>
                    </div>
                </li>
                <li>
                    <div class="pods-field pods-boolean">
                        <?php echo PodsForm::field( 'supports_post_formats', pods_var_raw( 'supports_post_formats', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Post Formats', 'pods' ) ) ); ?>
                    </div>
                </li>

                <?php if ( function_exists( 'genesis' ) ) { ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_seo', pods_var_raw( 'supports_genesis_seo', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: SEO', 'pods' ) ) ); ?>
                        </div>
                    </li>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_layouts', pods_var_raw( 'supports_genesis_layouts', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Layouts', 'pods' ) ) ); ?>
                        </div>
                    </li>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'supports_genesis_simple_sidebars', pods_var_raw( 'supports_genesis_simple_sidebars', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Genesis: Simple Sidebars', 'pods' ) ) ); ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'supports_custom', __( 'Advanced Supports', 'pods' ), __( 'Comma-separated list of custom "supports" values to pass to register_post_type.', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'supports_custom', pods_var_raw( 'supports_custom', $pod, '' ), 'text' ); ?>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Built-in Taxonomies', 'pods' ); ?>
        </p>

        <div class="pods-pick-values pods-pick-checkbox">
            <ul>
                <?php
                foreach ( (array) $field_settings[ 'pick_object' ][ 'Taxonomies' ] as $taxonomy => $label ) {
                    $taxonomy = pods_str_replace( 'taxonomy-', '', $taxonomy, 1 );
                    ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'built_in_taxonomies_' . $taxonomy, pods_var_raw( 'built_in_taxonomies_' . $taxonomy, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label . ' <small>(' . $taxonomy . ')</small>' ) ); ?>
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
elseif ( 'taxonomy' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
    ?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'public', __( 'Public', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'public', pods_var_raw( 'public', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'show_ui', __( 'Show UI', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'show_ui', pods_var_raw( 'show_ui', $pod, pods_var_raw( 'public', $pod, true ) ), 'boolean', array( 'boolean_yes_label' => '', 'dependency' => true ) ); ?>
    </div>
    <div class="pods-depends-on pods-depends-on-show-ui">
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'menu_name', __( 'Menu Name', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'menu_name', pods_var_raw( 'menu_name', $pod ), 'text' ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'menu_icon', __( 'Menu Icon', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'menu_icon', pods_var_raw( 'menu_icon', $pod ), 'text' ); ?>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'show_in_nav_menus', __( 'Show in Nav Menus', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'show_in_nav_menus', pods_var_raw( 'show_in_nav_menus', $pod, pods_var_raw( 'public', $pod, true ) ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'show_tagcloud', __( 'Allow in Tagcloud Widget', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'show_tagcloud', pods_var_raw( 'show_tagcloud', $pod, pods_var_raw( 'show_ui', $pod, pods_var_raw( 'public', $pod, true ) ) ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'hierarchical', pods_var_raw( 'hierarchical', $pod, false ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-container pods-depends-on pods-depends-on-hierarchical">
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'label_parent_item_colon', __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'label_parent_item_colon', pods_var_raw( 'label_parent_item_colon', $pod ), 'text' ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'label_parent', __( '<strong>Label: </strong> Parent', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'label_parent', pods_var_raw( 'label_parent', $pod ), 'text' ); ?>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'rewrite', __( 'Rewrite', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'rewrite', pods_var_raw( 'rewrite', $pod, true ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-container pods-depends-on pods-depends-on-rewrite">
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'rewrite_custom_slug', __( 'Custom Rewrite Slug', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'rewrite_custom_slug', pods_var_raw( 'rewrite_custom_slug', $pod ), 'text' ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'rewrite_with_front', __( 'Allow Front Prepend', 'pods' ), __( 'Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'rewrite_with_front', pods_var_raw( 'rewrite_with_front', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'rewrite_hierarchical', __( 'Hierarchical Permalinks', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'rewrite_hierarchical', pods_var_raw( 'rewrite_hierarchical', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'query_var', __( 'Query Var', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'query_var', pods_var_raw( 'query_var', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'sort', __( 'Remember order saved on Post Types', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'sort', pods_var_raw( 'sort', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>

    <?php
        if ( pods_wp_version( '3.5' ) ) {
    ?>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'show_admin_column', __( 'Show Taxonomy column on Post Types', 'pods' ), __( 'Whether to allow automatic creation of taxonomy columns on associated post-types', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'show_admin_column', pods_var_raw( 'show_admin_column', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
        </div>
    <?php
        }
    ?>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'update_count_callback', __( 'Function to call when updating counts', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'update_count_callback', pods_var_raw( 'update_count_callback', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Associated Post Types', 'pods' ); ?>
        </p>

        <div class="pods-pick-values pods-pick-checkbox">
            <ul>
                <?php
                    foreach ( (array) $field_settings[ 'pick_object' ][ 'Post Types' ] as $post_type => $label ) {
                        $post_type = pods_str_replace( 'post_type-', '', $post_type, 1 );
                ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'built_in_post_types_' . $post_type, pods_var_raw( 'built_in_post_types_' . $post_type, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label . ' <small>(' . $post_type . ')</small>' ) ); ?>
                        </div>
                    </li>
                <?php
                    }
                ?>

                <?php
                    if ( pods_wp_version( '3.5' ) ) {
                ?>
                    <li>
                        <div class="pods-field pods-boolean">
                            <?php echo PodsForm::field( 'built_in_post_types_attachment', pods_var_raw( 'built_in_post_types_attachment', $pod, false ), 'boolean', array( 'boolean_yes_label' => 'Media <small>(attachment)</small>' ) ); ?>
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
elseif ( 'pod' == pods_var( 'type', $pod ) ) {
?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'detail_url', __( 'Detail Page URL', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'detail_url', pods_var_raw( 'detail_url', $pod ), 'text' ); ?>
    </div>

    <?php
        $index_fields = array( 'id' => 'ID' );

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( !in_array( $field[ 'type' ], $tableless_field_types ) )
                $index_fields[ $field[ 'name' ] ] = $field[ 'label' ];
        }
    ?>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'pod_index', __( 'Title Field', 'pods' ), __( 'If you delete the "name" field, we need to specify the field to use as your primary title field. This field will serve as an index of your content. Most commonly this field represents the name of a person, place, thing, or a summary field.', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pod_index', pods_var_raw( 'pod_index', $pod, 'name' ), 'pick', array( 'data' => $index_fields ) ); ?>
    </div>

    <div class="pods-field-option">
        <?php echo PodsForm::label( 'hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'hierarchical', (int) pods_var_raw( 'hierarchical', $pod, 0 ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>

    <?php
        $hierarchical_fields = array();

        foreach ( $pod[ 'fields' ] as $field ) {
            if ( 'pick' == $field[ 'type' ] && 'pod' == pods_var( 'pick_object', $field ) && $pod[ 'name' ] == pods_var( 'pick_val', $field ) && 'single' == pods_var( 'pick_format_type', $field[ 'options' ] ) )
                $hierarchical_fields[ $field[ 'name' ] ] = $field[ 'label' ];
        }

        if ( empty( $hierarchical_fields ) )
            $hierarchical_fields = array( '' => __( 'No Hierarchical Fields found', 'pods' ) );
    ?>

    <div class="pods-field-option pods-depends-on pods-depends-on-hierarchical">
        <?php echo PodsForm::label( 'pod_parent', __( 'Hierarchical Field', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pod_parent', pods_var_raw( 'pod_parent', $pod, 'name' ), 'pick', array( 'data' => $hierarchical_fields ) ); ?>
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
            echo PodsForm::field( 'pre_save_helpers', pods_var_raw( 'pre_save_helpers', $pod ), 'pick', array( 'data' => $pre_save_helpers ) );
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
            echo PodsForm::field( 'post_save_helpers', pods_var_raw( 'post_save_helpers', $pod ), 'pick', array( 'data' => $post_save_helpers ) );
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
            echo PodsForm::field( 'pre_delete_helpers', pods_var_raw( 'pre_delete_helpers', $pod ), 'pick', array( 'data' => $pre_delete_helpers ) );
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
            echo PodsForm::field( 'post_delete_helpers', pods_var_raw( 'post_delete_helpers', $pod ), 'pick', array( 'data' => $post_delete_helpers ) );
        ?>
    </div>
    <?php
    }
}
elseif ( 'settings' == pods_var( 'type', $pod ) ) {
    $fields = $advanced_options[ 'setting_options' ];
    $field_options = PodsForm::fields_setup( $fields );
    $field = $pod;

    include PODS_DIR . 'ui/admin/field-option.php';
}
?>
</div>
<?php
}

if ( $pods_ui ) {
?>
<div id="pods-ui" class="pods-nav-tab pods-manage-field pods-dependency pods-submittable-fields">
<?php
    $fields = $advanced_options[ 'ui_options' ];
    $field_options = PodsForm::fields_setup( $fields );
    $field = $pod;

    include PODS_DIR . 'ui/admin/field-option.php';
?>
</div>
<?php
}

if ( 'taxonomy' == pods_var( 'type', $pod ) && !$manage_fields ) {
?>
<div id="pods-extra-fields" class="pods-nav-tab">
    <p><?php _e( 'Taxonomies do not support extra fields natively, but Pods can add this feature for you easily. Table based storage will operate in a way where each field you create for your content type becomes a field in a table.', 'pods' ); ?></p>

    <p><?php echo sprintf( __( 'Enabling extra fields for this taxonomy will add a custom table into your database as <em>%s</em>.', 'pods' ), $wpdb->prefix . 'pods_' . pods_var( 'name', $pod ) ); ?></p>

    <p><a href="http://pods.io/docs/comparisons/compare-storage-types/" target="_blank"><?php _e( 'Find out more', 'pods' ); ?> &raquo;</a></p>

    <p class="submit">
        <a href="<?php echo pods_var_update( array( 'enable_extra_fields' => 1 ) ); ?>" class="button-primary"><?php _e( 'Enable Extra Fields', 'pods' ); ?></a>
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
                <h3><span>Manage <small>(<a href="<?php echo pods_var_update( array( 'action' . $obj->num => 'manage', 'id' . $obj->num => '' ) ); ?>">&laquo; <?php _e( 'Back to Manage', 'pods' ); ?></a>)
                </small></span></h3>
                <div class="inside">
                    <div class="submitbox" id="submitpost">
                        <div id="major-publishing-actions">
                            <div id="delete-action">
                                <a href="<?php echo pods_var_update( array( 'action' . $obj->num => 'delete' ) ); ?>" class="submitdelete deletion pods-confirm" data-confirm="<?php _e( 'Are you sure you want to delete this Pod? All fields and data will be removed.', 'pods' ); ?>"> Delete Pod </a>
                            </div>
                            <div id="publishing-action">
                                <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                                <button class="button-primary" type="submit">Save Pod</button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /#submitdiv -->
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

    foreach ( $field_settings[ 'field_types' ] as $field_type => $field_type_data ) {
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
                $sub_object_label = preg_replace( '/(\s\([\w\d\s]*\))/', '', $sub_object_label );
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
        var thank_you = '<?php echo addslashes( pods_var_update( array( 'do' => 'save' ) ) ); ?>';

        document.location = thank_you.replace( 'X_ID_X', id );
    }

    var pods_sister_field_going = {

    };

    var pods_sister_field = function ( $el ) {
        if ( 'undefined' != typeof pods_sister_field_going[ $el.prop( 'id' ) ] )
            return;

        pods_sister_field_going[ $el.prop( 'id' ) ] = true;

        var id = $el.closest( 'tr.pods-manage-row' ).data( 'row' );

        var default_select = '<?php echo addslashes( str_replace( array( "\n", "\r" ), ' ', PodsForm::field( 'field_data[--1][sister_id]', '', 'pick', array( 'data' => pods_var_raw( 'sister_id', $field_settings ) ) ) ) ); ?>';
        default_select = default_select.replace( /\-\-1/g, id );

        var related_pod_name = jQuery( '#pods-form-ui-field-data-' + id + '-pick-object' ).val();

        if ( 'custom-simple' == related_pod_name || 'post-status' == related_pod_name || 'role' == related_pod_name || 'post-types' == related_pod_name || 'taxonomies' == related_pod_name || '' == related_pod_name ) {
            pods_sister_field_going[ $el.prop( 'id' ) ] = false;

            return;
        }

        var selected_value = jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val();

        var select_container = default_select.match( /<select[^<]*>/g );

        $el.find( '.pods-sister-field' ).html( select_container + '<option value=""><?php esc_attr_e( 'Loading available fields..', 'pods' ); ?></option></select>' );

        postdata = {
            action : 'pods_admin',
            method : 'load_sister_fields',
            _wpnonce : '<?php echo wp_create_nonce( 'pods-load_sister_fields' ); ?>',
            pod : '<?php echo pods_var( 'name', $pod ); ?>',
            related_pod : related_pod_name
        };

        jQuery.ajax( {
            type : 'POST',
            dataType : 'html',
            url : ajaxurl + '?pods_ajax=1',
            cache : false,
            data : postdata,
            success : function ( d ) {
                if ( -1 == d.indexOf( '<e>' ) && -1 == d.indexOf('</e>') && -1 != d && '[]' != d ) {
                    var json = d.match( /{.*}$/ );

                    if ( 0 < json.length )
                        json = jQuery.parseJSON( json[ 0 ] );
                    else
                        json = {};

                    var select_container = default_select.match( /<select[^<]*>/g );

                    if ( 'object' != typeof json || jQuery.isEmptyObject( json ) ) {
                        if ( window.console ) console.log( d );
                        if ( window.console ) console.log( json );

                        select_container += '<option value=""><?php esc_attr_e( 'There was a server error with your AJAX request.', 'pods' ); ?></option>';
                    }
                    else {
                        select_container += '<option value=""><?php esc_attr_e( '-- Select Related Field --', 'pods' ); ?></option>';

                        for ( var field_id in d ) {
                            var field_name = d[ field_id ];

                            select_container += '<option value="' + field_id + '">' + field_name + '</option>';
                        }
                    }

                    select_container += '</select>';

                    $el.find( '.pods-sister-field' ).html( select_container );

                    jQuery( '#pods-form-ui-field-data-' + id + '-sister-id' ).val( selected_value );

                    pods_sister_field_going[ $el.prop( 'id' ) ] = false;
                }
                else {
                    // None found
                    $el.find( '.pods-sister-field' ).html( default_select );

                    pods_sister_field_going[ $el.prop( 'id' ) ] = false;
                }
            },
            error : function () {
                // None found
                $el.find( '.pods-sister-field' ).html( default_select );

                pods_sister_field_going[ $el.prop( 'id' ) ] = false;
            }
        } );
    }
</script>
