<?php
global $i;

$field_types = array(
    'date' => 'Date / Time',
    'number' => 'Number',
    'boolean' => 'Yes / No',
    'text' => 'Text',
    'paragraph' => 'Paragraph Text',
    'file' => 'File Upload',
    'slug' => 'Permalink (url-friendly)',
    'pick' => 'Relationship'
);

$advanced_fields = array(
    __( 'Visual', 'pods' ) => array(
        'css_class_name' => array(
            'label' => __( 'CSS Class Name', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => ''
        ),
        'input_helper' => array(
            'label' => __( 'Input Helper', 'pods' ),
            'help' => __( 'help', 'pods' ),
            'type' => 'text',
            'default' => '',
            'data' => array( '' => '-- Select --' )
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
                'restrict_capability' => array(
                    'label' => __( 'Restrict access by Capability?', 'pods' ),
                    'default' => 0,
                    'type' => 'boolean',
                    'dependency' => true
                )
            )
        ),
        'capability_allowed' => array(
            'label' => __( 'Capability Allowed', 'pods' ),
            'help' => __( 'Comma separated list of cababilities, for example add_podname_item , please see the Roles and Capabilities component for the complete list and a way to add your own', 'pods' ),
            'type' => 'text',
            'default' => '',
            'depends-on' => array( 'restrict_capability' => true )
        )
    ),
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
    )
);

$field_defaults = array(
    'name' => 'new_field',
    'label' => 'New Field',
    'description' => '',
    'type' => 'text',
    'pick_object' => '',
    'sister_field_id' => '',
    'required' => 0,
    'unique' => 0,
    'css_class_name' => '',
    'input_helper' => '',
    'default_value' => '',
    'default_value_parameter' => '',
    'admin_only' => 0,
    'restrict_capability' => 0,
    'capability_allowed' => '',
    'regex_validation' => '',
    'message_regex' => '',
    'message_required' => '',
    'message_unique' => ''
);

$pick_object = array(
    '' => '-- Select --',
    'Custom' => array( 'custom-simple' => 'Simple (custom defined list)' ),
    'Pods' => array(),
    'Post Types' => array(),
    'Taxonomies' => array(),
    'Other WP Objects' => array(
        'user' => 'Users',
        'comment' => 'Comments'
    )
);

$_pods = (array) $this->data->select( array(
    'table' => '@wp_pods',
    'where' => '`type` = "pod"',
    'orderby' => '`name`',
    'limit' => -1,
    'search' => false,
    'pagination' => false
) );
foreach ( $_pods as $pod ) {
    if ( !empty( $pod->options ) )
        $pod->options = (object) json_decode( $pod->options );
    $label = $pod->name;
    if ( '' != pods_var( 'label', $pod->options, '' ) )
        $label = pods_var( 'label', $pod->options, '' );
    $pick_object[ 'Pods' ][ 'pod-' . $pod->name ] = $label;
}

$post_types = get_post_types();
$ignore = array( 'attachment', 'revision', 'nav_menu_item' );
foreach ( $post_types as $post_type => $label ) {
    if ( in_array( $post_type, $ignore ) || empty( $post_type ) )
        continue;
    $post_type = get_post_type_object( $post_type );
    $pick_object[ 'Post Types' ][ 'post-type-' . $post_type->name ] = $post_type->label;
}

$taxonomies = get_taxonomies();
$ignore = array( 'nav_menu', 'link_category', 'post_format' );
foreach ( $taxonomies as $taxonomy => $label ) {
    if ( in_array( $taxonomy, $ignore ) || empty( $taxonomy ) )
        continue;
    $taxonomy = get_taxonomy( $taxonomy );
    $pick_object[ 'Taxonomies' ][ 'taxonomy-' . $taxonomy->name ] = $taxonomy->label;
}

$field_settings = array(
    'field_types' => $field_types,
    'field_defaults' => $field_defaults,
    'advanced_fields' => $advanced_fields,
    'pick_object' => $pick_object,
    'sister_field_id' => array( '' => '-- Select --' ),
    'input_helper' => array( '' => '-- Select --' )
);

$pod = $this->api->load_pod( ( isset( $obj->row[ 'name' ] ) ? $obj->row[ 'name' ] : false ) );
$pod[ 'options' ] = (array) $pod[ 'options' ];
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
$field_settings = apply_filters( 'pods_field_settings', apply_filters( 'pods_field_settings_' . $pod[ 'name' ], $field_settings, $pod ) );
$pod[ 'fields' ] = apply_filters( 'pods_fields_edit', apply_filters( 'pods_fields_edit_' . $pod[ 'name' ], $pod[ 'fields' ], $pod ) );

global $wpdb;
$max_length_name = 64;
$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
$max_length_name -= strlen( $wpdb->prefix . 'pods_' );
?>
<div class="wrap pods-admin">
<div id="icon-pods" class="icon32"><br /></div>
<form action="" method="post" class="pods-submittable">
<div class="pods-submittable-fields">
    <input type="hidden" name="action" value="pods_admin" />
    <input type="hidden" name="method" value="save_pod" />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'pods-save_pod' ); ?>" />
    <input type="hidden" name="id" value="<?php echo (int) $pod[ 'id' ]; ?>" />

    <h2>
        Edit Pod:
                <span class="pods-sluggable">
                    <span class="pods-slug">
                        <em><?php echo esc_html( $pod[ 'name' ] ); ?></em>
                        <input type="button" class="edit-slug-button button" value="Edit" />
                    </span>
                    <span class="pods-slug-edit">
                        <?php echo PodsForm::field( 'name', pods_var( 'name', $pod ), 'db', array( 'attributes' => array( 'maxlength' => $max_length_name, 'size' => 25 ), 'class' => 'pods-validate pods-validate-required' ) ); ?>
                        <input type="button" class="save-button button" value="OK" /> <a class="cancel" href="#cancel-edit">Cancel</a>
                    </span>
                </span>
    </h2>
</div>

<div id="poststuff">
<img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
<!-- /inner-sidebar -->
<div id="post-body" class="meta-box-holder columns-2">
<div id="post-body-content">
<h2>Manage Fields</h2>
<!-- pods table -->
<table class="widefat fixed pages" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" id="cb" class="manage-column field-cb check-column">
                <span>&nbsp;</span>
            </th>
            <th scope="col" id="label" class="manage-column field-label">
                <span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field." ) ); ?></span>
            </th>
            <th scope="col" id="machine-name" class="manage-column field-machine-name">
                <span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically." ) ); ?></span>
            </th>
            <th scope="col" id="field-type" class="manage-column field-field-type">
                <span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc." ) ); ?></span>
            </th>
            <!--
            <th scope="col" id="comment" class="manage-column field-comment">
                <span>Comment</span>
            </th>-->
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th scope="col" id="cb" class="manage-column field-cb check-column">
                <span>&nbsp;</span>
            </th>
            <th scope="col" id="label" class="manage-column field-label">
                <span>Label<?php pods_help( __( "<h6>Label</h6>The label is the descriptive name to identify the Pod field." ) ); ?></span>
            </th>
            <th scope="col" id="machine-name" class="manage-column field-machine-name">
                <span>Name<?php pods_help( __( "<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically." ) ); ?></span>
            </th>
            <th scope="col" id="field-type" class="manage-column field-field-type">
                <span>Field Type<?php pods_help( __( "<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc." ) ); ?></span>
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
        $i = '--1';
        $field = array(
            'id' => '__1',
            'name' => 'new__1',
            'label' => 'New Field __1',
            'type' => 'text'
        );
        include PODS_DIR . 'ui/admin/setup_edit_field.php';

        $i = 1;
        foreach ( $pod[ 'fields' ] as $field ) {
            if ( '_pods_empty' == $field[ 'name' ] )
                continue;
            include PODS_DIR . 'ui/admin/setup_edit_field.php';
            $i++;
        }
        ?>
        <tr class="no-items<?php echo ( 1 < $i ? ' hidden' : '' ); ?>">
            <td class="colspanchange" colspan="4">No fields have been added yet</td>
        </tr>
    </tbody>
</table>
<!-- /pods table -->
<p class="pods-manage-row-add">
    <a href="#add-field" class="button-primary"><?php _e( 'Add Field' ); ?></a>
</p>

<div id="pods-advanced" class="pods-toggled postbox closed pods-submittable-fields">
<div class="handlediv" title="Click to toggle">
    <br />
</div>
<h3><span>Advanced Options</span></h3>

<div class="inside pods-form">
<div class="pods-manage-field pods-dependency">
<div class="pods-tabbed">
<ul class="pods-tabs">
    <?php
    if ( 'post_type' == pods_var( 'type', $pod ) ) {
        ?>
        <li class="pods-tab"><a href="#pods-advanced-post-type-labels">Post Type Labels</a></li>
        <li class="pods-tab"><a href="#pods-advanced-post-type-options">Post Type Options</a></li>
        <?php
    }
    elseif ( 'taxonomy' == pods_var( 'type', $pod ) ) {
        ?>
        <li class="pods-tab"><a href="#pods-advanced-taxonomy-labels">Taxonomy Labels</a></li>
        <li class="pods-tab"><a href="#pods-advanced-taxonomy-options">Taxonomy Options</a></li>
        <?php
    }
    elseif ( 'pod' == pods_var( 'type', $pod ) ) {
        ?>
        <li class="pods-tab"><a href="#pods-advanced-labels">Pod Labels</a></li>
        <?php
    }
    ?>
    <li class="pods-tab"><a href="#pods-advanced-options">Pod Options</a></li>
</ul>

<div class="pods-tab-group">
<?php
if ( 'post_type' == pods_var( 'type', $pod ) ) {
    $advanced_options = array(
        'cpt_labels' => array(
            'cpt_label' => array(
                'label' => __( 'Label', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ucwords( str_replace( '_', ' ', pods_var( 'name', $pod ) ) )
            ),
            'cpt_singular_label' => array(
                'label' => __( 'Singular Label', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => pods_var( 'cpt_label', $pod, ucwords( str_replace( '_', ' ', pods_var( 'name', $pod ) ) ) )
            ),
            'cpt_add_new' => array(
                'label' => __( 'Add New', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_add_new_item' => array(
                'label' => __( 'Add New <span class="pods-slugged">Item</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_new_item' => array(
                'label' => __( 'New <span class="pods-slugged">Item</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_edit' => array(
                'label' => __( 'Edit', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_edit_item' => array(
                'label' => __( 'Edit <span class="pods-slugged">Item</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_view' => array(
                'label' => __( 'View', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_view_item' => array(
                'label' => __( 'View <span class="pods-slugged">Item</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_all_items' => array(
                'label' => __( 'All <span class="pods-slugged">Items</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_search_items' => array(
                'label' => __( 'Search <span class="pods-slugged">Items</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_not_found' => array(
                'label' => __( 'Not Found', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_not_found_in_trash' => array(
                'label' => __( 'Not Found in Trash', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => ''
            )
        ),
        'cpt_options' => array(
            'cpt_description' => array(
                'label' => __( 'Post Type Description', 'pods' ),
                'help' => __( 'A short descriptive summary of what the post type is.', 'pods' ),
                'type' => 'text',
                'default' => ''
            ),
            'cpt_public' => array(
                'label' => __( 'Public', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => true,
                'boolean_yes_label' => ''
            ),
            'cpt_publicly_queryable' => array(
                'label' => __( 'Publicly Queryable', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => pods_var( 'cpt_public', $pod, true ),
                'boolean_yes_label' => ''
            ),
            'cpt_exclude_from_search' => array(
                'label' => __( 'Exclude from Search', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => !pods_var( 'cpt_public', $pod, true ),
                'boolean_yes_label' => ''
            ),
            'cpt_show_ui' => array(
                'label' => __( 'Show UI', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => pods_var( 'cpt_public', $pod, true ),
                'boolean_yes_label' => ''
            ),
            'cpt_show_in_menu' => array(
                'label' => __( 'Show in Menu', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => pods_var( 'cpt_public', $pod, true ),
                'dependency' => true,
                'boolean_yes_label' => ''
            ),
            'cpt_menu_name' => array(
                'label' => __( 'Menu Name', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_show_in_menu' => true )
            ),
            'cpt_menu_position' => array(
                'label' => __( 'Menu Position', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'number',
                'default' => '',
                'depends-on' => array( 'cpt_show_in_menu' => true )
            ),
            'cpt_menu_icon' => array(
                'label' => __( 'Menu Icon URL', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_show_in_menu' => true )
            ),
            'cpt_menu_string' => array(
                'label' => __( '<strong>Label: </strong> Parent Page', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_show_in_menu' => true )
            ),
            'cpt_show_in_nav_menus' => array(
                'label' => __( 'Show in Navigation Menu', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => true,
                'boolean_yes_label' => ''
            ),
            'cpt_capability_type' => array(
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
            'cpt_capability_type_custom' => array(
                'label' => __( 'Custom Capability Type', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => pods_var( 'name', $pod ),
                'depends-on' => array( 'cpt_capability_type' => 'custom' )
            ),
            'cpt_has_archive' => array(
                'label' => __( 'Has Archive', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => false,
                'boolean_yes_label' => ''
            ),
            'cpt_hierarchical' => array(
                'label' => __( 'Hierarchical', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => false,
                'dependency' => true,
                'boolean_yes_label' => ''
            ),
            'cpt_parent_item_colon' => array(
                'label' => __( '<strong>Label: </strong> Parent <span class="pods-slugged">Item</span>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_hierarchical' => true )
            ),
            'cpt_parent' => array(
                'label' => __( '<strong>Label: </strong> Parent', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_hierarchical' => true )
            ),
            'cpt_rewrite' => array(
                'label' => __( 'Rewrite', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => true,
                'dependency' => true,
                'boolean_yes_label' => ''
            ),
            'cpt_rewrite_custom_slug' => array(
                'label' => __( 'Custom Rewrite Slug', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'text',
                'default' => '',
                'depends-on' => array( 'cpt_rewrite' => true )
            ),
            'cpt_rewrite_with_front' => array(
                'label' => __( 'Rewrite with Front', 'pods' ),
                'help' => __( 'Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)', 'pods' ),
                'type' => 'boolean',
                'default' => true,
                'depends-on' => array( 'cpt_rewrite' => true ),
                'boolean_yes_label' => ''
            ),
            'cpt_rewrite_feeds' => array(
                'label' => __( 'Rewrite Feeds', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => false,
                'depends-on' => array( 'cpt_rewrite' => true ),
                'boolean_yes_label' => ''
            ),
            'cpt_rewrite_pages' => array(
                'label' => __( 'Rewrite Pages', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'type' => 'boolean',
                'default' => true,
                'depends-on' => array( 'cpt_rewrite' => true ),
                'boolean_yes_label' => ''
            )
        ),
        // @todo Finish converting hardcoded fields into field array format (see how simple/powerful it is?)
        'ct_labels' => array(),
        'ct_options' => array(),
        'pod_labels' => array(),
        'pod_options' => array()
    );
    ?>
<div id="pods-advanced-post-type-labels" class="pods-tab">
    <?php
    $fields = $advanced_options[ 'cpt_labels' ];
    $field_options = PodsForm::fields_setup( $fields );

    include PODS_DIR . 'ui/admin/field_option.php';
    ?>
</div>
<div id="pods-advanced-post-type-options" class="pods-tab">
    <?php
    $fields = $advanced_options[ 'cpt_options' ];
    $field_options = PodsForm::fields_setup( $fields );

    include PODS_DIR . 'ui/admin/field_option.php';
    ?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'cpt_query_var', __( 'Query Var', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'cpt_query_var', pods_var( 'cpt_query_var', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'cpt_can_export', __( 'Exportable', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'cpt_can_export', pods_var( 'cpt_can_export', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Supports' ); ?>
        </p>

        <div class="pods-field-option-group-values">
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_title', pods_var( 'cpt_supports_title', $pod, true ), 'boolean', array( 'boolean_yes_label' => __( 'Title', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_editor', pods_var( 'cpt_supports_editor', $pod, true ), 'boolean', array( 'boolean_yes_label' => __( 'Editor', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_author', pods_var( 'cpt_supports_author', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Author', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_thumbnail', pods_var( 'cpt_supports_thumbnail', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Featured Image', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_excerpt', pods_var( 'cpt_supports_excerpt', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Excerpt', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_trackbacks', pods_var( 'cpt_supports_trackbacks', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Trackbacks', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_custom_fields', pods_var( 'cpt_supports_custom_fields', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Custom Fields', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_comments', pods_var( 'cpt_supports_comments', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Comments', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_revisions', pods_var( 'cpt_supports_revisions', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Revisions', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_page_attributes', pods_var( 'cpt_supports_page_attributes', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Page Attributes', 'pods' ) ) ); ?>
            </div>
            <div class="pods-field-option-group-value">
                <?php echo PodsForm::field( 'cpt_supports_post_formats', pods_var( 'cpt_supports_post_formats', $pod, false ), 'boolean', array( 'boolean_yes_label' => __( 'Post Formats', 'pods' ) ) ); ?>
            </div>
        </div>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Built-in Taxonomies' ); ?>
        </p>

        <div class="pods-field-option-group-values">
            <?php
            foreach ( (array) $field_settings[ 'pick_object' ][ 'Taxonomies' ] as $taxonomy => $label ) {
                $taxonomy = str_replace( 'taxonomy-', '', $taxonomy );
                ?>
                <div class="pods-field-option-group-value">
                    <?php echo PodsForm::field( 'cpt_built_in_taxonomies_' . $taxonomy, pods_var( 'cpt_built_in_taxonomies_' . $taxonomy, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label ) ); ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
    <?php
}
elseif ( 'taxonomy' == pods_var( 'type', $pod ) ) {
    ?>
<div id="pods-advanced-taxonomy-labels" class="pods-tab">
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_label', __( 'Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_label', pods_var( 'ct_label', $pod, ucwords( str_replace( '_', ' ', pods_var( 'name', $pod ) ) ) ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_singular_label', __( 'Singular Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_singular_label', pods_var( 'ct_singular_label', $pod, pods_var( 'ct_label', $pod, ucwords( str_replace( '_', ' ', pods_var( 'name', $pod ) ) ) ) ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_add_new_item', __( 'Add New <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_add_new_item', pods_var( 'ct_add_new_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_edit_item', __( 'Edit <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_edit_item', pods_var( 'ct_edit_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_update_item', __( 'Update <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_update_item', pods_var( 'ct_update_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_new_item_name', __( 'New <span class="pods-slugged">Item</span> Name' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_new_item_name', pods_var( 'ct_new_item_name', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_all_items', __( 'All <span class="pods-slugged">Items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_all_items', pods_var( 'ct_all_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_search_items', __( 'Search <span class="pods-slugged">Items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_search_items', pods_var( 'ct_search_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_popular_items', __( 'Popular <span class="pods-slugged">Items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_popular_items', pods_var( 'ct_popular_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_separate_items_with_commas', __( 'Separate <span class="pods-slugged-lower">items</span> with commas' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_separate_items_with_commas', pods_var( 'ct_separate_items_with_commas', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_add_or_remove_items', __( 'Add or remove <span class="pods-slugged-lower">items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_add_or_remove_items', pods_var( 'ct_add_or_remove_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_choose_from_the_most_used', __( 'Choose from the most used', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_choose_from_the_most_used', pods_var( 'ct_choose_from_the_most_used', $pod ), 'text' ); ?>
    </div>
</div>
<div id="pods-advanced-taxonomy-options" class="pods-tab">
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_public', __( 'Public', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_public', pods_var( 'ct_public', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_show_ui', __( 'Show UI', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_show_ui', pods_var( 'ct_show_ui', $pod, pods_var( 'ct_public', $pod, true ) ), 'boolean', array( 'boolean_yes_label' => '', 'dependency' => true ) ); ?>
    </div>
    <div class="pods-field-option pods-depends-on pods-depends-on-ct-show-ui">
        <?php echo PodsForm::label( 'ct_menu_name', __( 'Menu Name', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_menu_name', pods_var( 'ct_menu_name', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_show_in_nav_menus', __( 'Show in Nav Menus', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_show_in_nav_menus', pods_var( 'ct_show_in_nav_menus', $pod, pods_var( 'ct_public', $pod, true ) ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_show_tagcloud', __( 'Allow in Tagcloud Widget', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_show_tagcloud', pods_var( 'ct_show_tagcloud', $pod, pods_var( 'ct_show_ui', $pod, pods_var( 'ct_public', $pod, true ) ) ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_hierarchical', __( 'Hierarchical', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_hierarchical', pods_var( 'ct_hierarchical', $pod, false ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-container pods-depends-on pods-depends-on-ct-hierarchical">
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'ct_parent_item_colon', __( '<strong>Label: </strong> Parent <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'ct_parent_item_colon', pods_var( 'ct_parent_item_colon', $pod ), 'text' ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'ct_parent', __( '<strong>Label: </strong> Parent', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'ct_parent', pods_var( 'ct_parent', $pod ), 'text' ); ?>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_rewrite', __( 'Rewrite', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_rewrite', pods_var( 'ct_rewrite', $pod, true ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-container pods-depends-on pods-depends-on-ct-rewrite">
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'ct_rewrite_custom_slug', __( 'Custom Rewrite Slug', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'ct_rewrite_custom_slug', pods_var( 'ct_rewrite_custom_slug', $pod ), 'text' ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'ct_rewrite_with_front', __( 'Allow Front Prepend', 'pods' ), __( 'Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)' ) ); ?>
            <?php echo PodsForm::field( 'ct_rewrite_with_front', pods_var( 'ct_rewrite_with_front', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
        </div>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'ct_rewrite_hierarchical', __( 'Hierarchical Permalinks', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'ct_rewrite_hierarchical', pods_var( 'ct_rewrite_hierarchical', $pod, true ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
        </div>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'ct_query_var', __( 'Query Var', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'ct_query_var', pods_var( 'ct_query_var', $pod ), 'boolean', array( 'boolean_yes_label' => '' ) ); ?>
    </div>
    <div class="pods-field-option-group">
        <p class="pods-field-option-group-label">
            <?php _e( 'Associated Post Types' ); ?>
        </p>

        <div class="pods-field-option-group-values">
            <?php
            foreach ( (array) $field_settings[ 'pick_object' ][ 'Post Types' ] as $post_type => $label ) {
                $post_type = str_replace( 'post-type-', '', $post_type );
                ?>
                <div class="pods-field-option-group-value">
                    <?php echo PodsForm::field( 'ct_built_in_post_types_' . $post_type, pods_var( 'ct_built_in_post_types_' . $post_type, $pod, false ), 'boolean', array( 'boolean_yes_label' => $label ) ); ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
    <?php
}
elseif ( 'pod' == pods_var( 'type', $pod ) ) {
    ?>
<div id="pods-advanced-labels" class="pods-tab">
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'label', __( 'Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'label', pods_var( 'label', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'singular_label', __( 'Singular Label', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'singular_label', pods_var( 'singular_label', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'add_new', __( 'Add New', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'add_new', pods_var( 'add_new', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'add_new_item', __( 'Add New <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'add_new_item', pods_var( 'add_new_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'new_item', __( 'New <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'new_item', pods_var( 'new_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'edit', __( 'Edit', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'edit', pods_var( 'edit', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'edit_item', __( 'Edit <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'edit_item', pods_var( 'edit_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'update_item', __( 'Update <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'update_item', pods_var( 'update_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'view', __( 'View', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'view', pods_var( 'view', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'view_item', __( 'View <span class="pods-slugged">Item</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'view_item', pods_var( 'view_item', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'all_items', __( 'All <span class="pods-slugged">Items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'all_items', pods_var( 'all_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'search_items', __( 'Search <span class="pods-slugged">Items</span>' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'search_items', pods_var( 'search_items', $pod ), 'text' ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'not_found', __( 'Not Found', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'not_found', pods_var( 'not_found', $pod ), 'text' ); ?>
    </div>
</div>
    <?php
}
?>
<div id="pods-advanced-options" class="pods-tab">
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'detail_url', __( 'Detail Page URL', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'detail_url', pods_var( 'detail_url', $pod ), 'text' ); ?>
    </div>
    <?php
    if ( 'pod' == pods_var( 'type', $pod ) ) {
        ?>
        <div class="pods-field-option">
            <?php echo PodsForm::label( 'show_in_menu', __( 'Show in Menu', 'pods' ), __( 'help', 'pods' ) ); ?>
            <?php echo PodsForm::field( 'show_in_menu', pods_var( 'show_in_menu', $pod ), 'boolean', array( 'dependency' => true, 'boolean_yes_label' => '' ) ); ?>
        </div>
        <div class="pods-field-option-container pods-depends-on pods-depends-on-show-in-menu">
            <div class="pods-field-option">
                <?php echo PodsForm::label( 'menu_name', __( 'Menu Name', 'pods' ), __( 'help', 'pods' ) ); ?>
                <?php echo PodsForm::field( 'menu_name', pods_var( 'menu_name', $pod ), 'text' ); ?>
            </div>
            <div class="pods-field-option">
                <?php echo PodsForm::label( 'menu_icon', __( 'Menu Icon', 'pods' ), __( 'help', 'pods' ) ); ?>
                <?php echo PodsForm::field( 'menu_icon', pods_var( 'menu_icon', $pod ), 'text' ); ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'pre_save_helpers', __( 'Pre-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pre_save_helpers', pods_var( 'pre_save_helpers', $pod ), 'pick', array( 'data' => array( '' => '-- Select --' ) ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'post_save_helpers', __( 'Post-Save Helper(s)', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'post_save_helpers', pods_var( 'post_save_helpers', $pod ), 'pick', array( 'data' => array( '' => '-- Select --' ) ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'pre_delete_helpers', __( 'Pre-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'pre_delete_helpers', pods_var( 'pre_delete_helpers', $pod ), 'pick', array( 'data' => array( '' => '-- Select --' ) ) ); ?>
    </div>
    <div class="pods-field-option">
        <?php echo PodsForm::label( 'post_delete_helpers', __( 'Post-Delete Helper(s)', 'pods' ), __( 'help', 'pods' ) ); ?>
        <?php echo PodsForm::field( 'post_delete_helpers', pods_var( 'post_delete_helpers', $pod ), 'pick', array( 'data' => array( '' => '-- Select --' ) ) ); ?>
    </div>
</div>
</div>
</div>
</div>
</div>
<!-- /inside -->
</div>
<!-- /pods-pod-advanced-settings -->
</div>
<!-- /#post-body-content -->

<div id="postbox-container-1" class="postbox-container pods_floatmenu">
    <div id="side-info-field" class="inner-sidebar">
        <div id="side-sortables">
            <div id="submitdiv" class="postbox pods-no-toggle">
                <h3><span>Manage <small>(<a href="<?php echo pods_var_update( array( 'action' . $obj->num => 'manage', 'id' . $obj->num => '' ) ); ?>">&laquo; <?php _e( 'Back to Manage' ); ?></a>)
                </small></span></h3>
                <div class="inside">
                    <div class="submitbox" id="submitpost">
                        <div id="major-publishing-actions">
                            <div id="delete-action">
                                <a href="#delete-pod" class="submitdelete deletion pods-submit" data-action="pods_admin" data-method="drop_pod" data-_wpnonce="<?php echo wp_create_nonce( 'pods-drop_pod' ); ?>" data-name="<?php echo esc_attr( pods_var( 'name', $pod ) ); ?>"> Delete Pod </a>
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
    foreach ( $field_settings[ 'field_types' ] as $field_type => $field_label ) {
        $pods_field_types[] = "'" . esc_js( $field_type ) . "' : '" . esc_js( $field_label ) . "'";
    }
    $pods_pick_objects = array();
    foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
        if ( '-- Select --' == $object_label )
            continue;
        if ( is_array( $object_label ) ) {
            $object = rtrim( $object, 's' );
            if ( false !== strpos( $object, 'ies' ) )
                $object = str_replace( 'ies', 'y', $object );
            foreach ( $object_label as $sub_object => $sub_object_label ) {
                $sub_object_label = preg_replace( '/(\s\([\w\d\s]*\))/', '', $sub_object_label );
                $pods_pick_objects[] = "'" . esc_js( $sub_object ) . "' : '" . esc_js( $sub_object_label ) . " <small>(" . esc_js( $object ) . ")</small>'";
            }
        }
        else
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
        $( document ).Pods( 'collapsible' );
        $( document ).Pods( 'toggled' );
        $( document ).Pods( 'tabbed' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'flexible', $( 'tbody.pods-manage-list tr.flexible-row' ) );
        $( document ).Pods( 'confirm' );
    } );
</script>
